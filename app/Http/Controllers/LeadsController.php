<?php

namespace App\Http\Controllers;

use App\Exports\LeadsExport;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Salesman;
use App\Models\SystemLogs;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class LeadsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $salesman = User::pluck('user_name', 'id');
        $leadSource = LeadSource::pluck('name', 'id');

        if ($request->ajax()) {
            $inquiry = Lead::query()->select('leads.*', 'users.user_name AS salesmanName', 'lead_sources.name AS leadSourceName')
                ->leftJoin('lead_sources', 'lead_sources.id', '=', 'leads.lead_source')
                ->leftJoin('users', 'users.id', '=', 'leads.salesman');

            if (!empty($request->startDate) && !empty($request->endDate)) {
                $inquiry = $inquiry->whereBetween(DB::raw('DATE(leads.created_at)'), [$request->startDate, $request->endDate]);
            }

            if (!empty($request->salesmanId)) {
                $inquiry = $inquiry->where('users.id', $request->salesmanId);
            }

            if (!empty($request->leadSourceId)) {
                $inquiry = $inquiry->where('lead_sources.id', $request->leadSourceId);
            }

            if (!empty($request->mobileNumber)) {
                $inquiry = $inquiry->where('leads.mobile', 'like', '%' . $request->mobileNumber . '%');
            }

            if (!empty($request->customerName)) {
                $inquiry = $inquiry->where('leads.name', 'like', '%' . $request->customerName . '%');
            }


            if (checkRights('USER_LEAD_ROLE_VIEW') && !checkRights('USER_LEAD_ROLE_VIEW_ALL')) {
                $inquiry = $inquiry->where('created_by', Auth::id());
            }

            return DataTables::of($inquiry)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (checkRights('USER_LEAD_ROLE_EDIT')) {
                        $html .= '<a href="' . route('leads.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a>';
                    }
                    return $html;
                })
                ->make(true);
        }
        return view('leads-list')->with(compact('leadSource', 'salesman'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vehicle = Vehicle::pluck('name', 'id');
        $salesman = User::pluck('user_name', 'id');
        $leadSource = LeadSource::pluck('name', 'id');
        $authId = auth()->id();
        return view('add-update-leads')->with(compact('leadSource', 'vehicle', 'salesman', 'authId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $salesmanName = '';
        $salesmanMobile = '';

        $nameQuery = User::find($request->salesman);
        if ($nameQuery) {
            $salesmanName = $nameQuery->user_name;
            $salesmanMobile = $nameQuery->mobile;
        }

        $languageType = $request->language_type;
        $locationType = $request->location_type;

        $vehicleIds = array_map('intval', $request->input('vehicle', []));
        $data = $request->all();
        $data['vehicle'] = $vehicleIds;

        $lead = Lead::create($data);

        if ($lead && !empty($vehicleIds)) {
            if (count($vehicleIds) == 1) {
                $vehicleInfo = $this->getVehicleInfo($vehicleIds[0]);
                $vehicleName = $vehicleInfo['name'];

                // switch ($languageType) {
                //     case '1':
                //         $message = "Hi {$request->name}\n\n";
                //         $message .= "Thank you for showing your interest in *{$vehicleName}*.\n\n";
                //         $message .= "My name is {$salesmanName} and I will be your companion along this electrifying journey.\n\n";
                //         $message .= "Warm Regards\n";
                //         $message .= "{$salesmanName}\n";
                //         $message .= $salesmanMobile;
                //         break;

                //     case '2':
                //         $message = "નમસ્કાર {$request->name}\n\n";
                //         $message .= "તમારો *{$vehicleName}* માં રસ દર્શાવવા બદલ હૃદયપૂર્વક આભાર.\n\n";
                //         $message .= "મારું નામ {$salesmanName} છે અને આ ઉત્સાહભરેલી મુસાફરીમાં હું આપનો સહયોગી રહીશ.\n\n";
                //         $message .= "સ્નેહપૂર્વક,\n";
                //         $message .= "{$salesmanName}\n";
                //         $message .= $salesmanMobile;
                //         break;
                // }
                $message = $this->getLeadMessage(
                    $languageType,
                    $request->name,
                    $vehicleName,
                    $salesmanName,
                    $salesmanMobile,
                    false,
                    $locationType
                );
            } else {
                // $message = "Hi " . $request->name . "\n\n";
                // $message .= "Thank you for showing your interest in our electric vehicles.\n\n";
                // $message .= "My name is " . $salesmanName . " and I will be your companion along this electrifying journey.\n\n";
                // $message .= "Warm Regards\n";
                // $message .= $salesmanName . "\n";
                // $message .= $salesmanMobile;

                $message = $this->getLeadMessage(
                    $languageType,
                    $request->name,
                    null,
                    $salesmanName,
                    $salesmanMobile,
                    true,
                    $locationType
                );
            }

            $firstVehicleInfo = $this->getVehicleInfo($vehicleIds[0]);
            $firstPdfUrl = $firstVehicleInfo['pdf'];
            $firstVehicleName = $firstVehicleInfo['name'];
            $this->sendWhatsAppMessageWithFileForLead($request->mobile, '', $firstPdfUrl, $firstVehicleName);
            sleep(2);
            foreach ($vehicleIds as $index => $vehicleId) {
                $vehicleInfo = $this->getVehicleInfo($vehicleId);
                $vehicleName = $vehicleInfo['name'];
                $pdfUrl = $vehicleInfo['pdf'];

                if ($index != 0) {
                    $this->sendWhatsAppMessageWithFileForLead($request->mobile, '', $pdfUrl, $vehicleName);
                    sleep(2);
                }

                $this->sendVehicleMedia($vehicleId, $request->mobile, $vehicleName);
            }

            $this->sendWhatsAppMessageForLead($request->mobile, $message);

            // $locationMessage = $this->getLocationMessage($languageType, $locationType);
            // $this->sendWhatsAppMessageForLead($request->mobile, $locationMessage);
            SystemLogs::create([
                'inquiry_id' => 0,
                'type'       => '3',
                'type_id'    => $lead->id,
                'remark'     => 'Add Lead',
                'action_id'  => 1,
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('leads.index')->with('success', 'Lead added successfully!');
    }

    /**
     * Get vehicle name and PDF URL by ID
     */
    private function getVehicleInfo($vehicleId)
    {
        $vehicles = [
            1 => ['name' => 'Ampere Nexus', 'pdf' => 'https://chiragautomotive.com/amper/assets/pdf/Ampere_Nexus.pdf'],
            2 => ['name' => 'Ampere Magnus Neo', 'pdf' => 'https://chiragautomotive.com/amper/assets/pdf/Magnus_Neo_A4.pdf'],
            3 => ['name' => 'Ampere Reo', 'pdf' => 'https://chiragautomotive.com/amper/assets/pdf/REO_80_KV.pdf'],
            4 => ['name' => 'TVS King EV Max', 'pdf' => 'https://chiragautomotive.com/amper/assets/pdf/King_EV_MAX_English.pdf'],
            5 => ['name' => 'TVS King Duramax Plus', 'pdf' => 'https://chiragautomotive.com/amper/assets/pdf/King_Duramax_Plus_Petrol_English.pdf'],
            6 => ['name' => 'TVS King Deluxe', 'pdf' => 'https://chiragautomotive.com/amper/assets/pdf/King_Deluxe_Petrol_English.pdf'],
            7 => ['name' => 'TVS King Kargo HD EV', 'pdf' => 'https://chiragautomotive.com/amper/assets/pdf/King_Kargo_EV_HD_English.pdf'],
        ];
        return $vehicles[$vehicleId] ?? ['name' => '', 'pdf' => ''];
    }

    /**
     * Send vehicle images and videos by vehicle ID
     */
    private function sendVehicleMedia($vehicleId, $mobile, $vehicleName)
    {
        if ($vehicleId === 1) { // Nexus
            for ($i = 1; $i <= 4; $i++) {
                $imageUrl = "https://chiragautomotive.com/amper/assets/pdf/images/nexus/{$i}.jpg";
                $this->sendWhatsAppMessageWithFileForLead($mobile, '', $imageUrl, $vehicleName);
                sleep(1);
            }
            $videoUrl = 'https://chiragautomotive.com/amper/assets/pdf/images/nexus/nexus_video.mp4';
            $this->sendWhatsAppMessageWithFileForLead($mobile, '', $videoUrl, $vehicleName);
            sleep(2);
        } elseif ($vehicleId === 2) { // Magnus
            for ($i = 1; $i <= 5; $i++) {
                $imageUrl = "https://chiragautomotive.com/amper/assets/pdf/images/magnus/{$i}.jpg";
                $this->sendWhatsAppMessageWithFileForLead($mobile, '', $imageUrl, $vehicleName);
                sleep(1);
            }
        } elseif ($vehicleId === 4) { // TVS King EV Max
            for ($i = 1; $i <= 7; $i++) {
                $imageUrl = "https://chiragautomotive.com/amper/assets/pdf/images/tvs_king_ev_max/{$i}.jpg";
                $this->sendWhatsAppMessageWithFileForLead($mobile, '', $imageUrl, $vehicleName);
                sleep(1);
            }
        } elseif ($vehicleId === 5) { // TVS King Duramax Plus
            for ($i = 1; $i <= 4; $i++) {
                $imageUrl = "https://chiragautomotive.com/amper/assets/pdf/images/duramax/{$i}.jpg";
                $this->sendWhatsAppMessageWithFileForLead($mobile, '', $imageUrl, $vehicleName);
                sleep(1);
            }
        } elseif ($vehicleId === 6) { // TVS King Deluxe
            for ($i = 1; $i <= 4; $i++) {
                $imageUrl = "https://chiragautomotive.com/amper/assets/pdf/images/deluxe/{$i}.jpg";
                $this->sendWhatsAppMessageWithFileForLead($mobile, '', $imageUrl, $vehicleName);
                sleep(1);
            }
        } elseif ($vehicleId === 7) { // TVS King Kargo HD EV
            for ($i = 1; $i <= 7; $i++) {
                $imageUrl = "https://chiragautomotive.com/amper/assets/pdf/images/tvs_king_kargo_hd_ev/{$i}.jpg";
                $this->sendWhatsAppMessageWithFileForLead($mobile, '', $imageUrl, $vehicleName);
                sleep(1);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $lead = Lead::find($id);
        $leadSource = $this->leadSource;
        $vehicle = Vehicle::pluck('name', 'id');
        $salesman = User::pluck('user_name', 'id');
        $leadSource = LeadSource::pluck('name', 'id');
        $authId = auth()->id();
        return view('add-update-leads')->with(compact('lead', 'leadSource', 'vehicle', 'salesman', 'authId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lead = Lead::find($id);
        $data = $request->all();
        $data['vehicle'] = array_map('intval', $request->input('vehicle', []));
        $lead->update($data);
        SystemLogs::create([
            'inquiry_id' => 0,
            'type' => '3',
            'type_id' => $lead->id,
            'remark'     => 'Edit Lead ',
            'action_id'  => 2,
            'created_by' => auth()->id(),
        ]);
        return redirect()->route('leads.index')->with('success', 'Lead updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function addVehicle(Request $request)
    {
        $name = $request->name;
        Vehicle::create(['name' => $name]);
        return response()->json(['success' => 'Vehicle added successfully!']);
    }

    public function addSalesman(Request $request)
    {
        Salesman::updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->name,
                'mobile' => $request->mobile,
            ]
        );
        return response()->json(['success' => true, 'message' => 'Salesman saved successfully.']);
    }

    public function addLeadSource(Request $request)
    {
        $name = $request->name;
        LeadSource::create(['name' => $name]);
        return response()->json(['success' => 'Lead Source added successfully!']);
    }

    public function vehicleDetails()
    {
        $vehicles = Vehicle::all();
        return response()->json($vehicles);
    }

    public function salesmanDetails($id = null)
    {
        $salesmen = $id ? Salesman::find($id) : Salesman::all();
        return response()->json($salesmen);
    }

    public function leadSourceDetails()
    {
        $data = LeadSource::all();
        return response()->json($data);
    }

    public function export(Request $request)
    {
        try {
            $exportStartDate = $request->input('exportStartDate');
            $exportEndDate = $request->input('exportEndDate');
            $exportSalesmanId = $request->input('exportSalesmanId');
            $exportLeadSourceId = $request->input('exportLeadSourceId');
            $exportMobileNumber = $request->input('exportMobileNumber');
            $exportCustomerName = $request->input('exportCustomerName');

            return Excel::download(new LeadsExport($exportStartDate, $exportEndDate, $exportSalesmanId, $exportLeadSourceId, $exportMobileNumber, $exportCustomerName), 'leads.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function export(Request $request)
    // {
    //     try {
    //         ExportLeadsToExcel::dispatch([
    //             'startDate' => $request->input('exportStartDate'),
    //             'endDate' => $request->input('exportEndDate'),
    //             'salesmanId' => $request->input('exportSalesmanId'),
    //             'leadSourceId' => $request->input('exportLeadSourceId'),
    //             'mobileNumber' => $request->input('exportMobileNumber'),
    //             'customerName' => $request->input('exportCustomerName'),
    //             'userId' => auth()->id(),
    //         ]);

    //         return response()->json(['success' => 'Export started. You’ll be notified once it’s ready.']);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    public function salesmanIndex(Request $request)
    {
        if ($request->ajax()) {
            $data = Salesman::query();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-sm btn-primary edit-salesman" data-id="' . $row->id . '">Edit</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('salesman');
    }
}
