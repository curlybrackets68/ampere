<?php

namespace App\Http\Controllers;

use App\Models\AmcMaster;
use App\Models\AmcPackageMaster;
use App\Models\LeadSource;
use App\Models\ServiceDetail;
use App\Models\SystemLogs;
use App\Models\User;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class AmcMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $amcMasterList = AmcMaster::query()->select();

            if (checkRights('USER_AMC_ROLE_VIEW') && !checkRights('USER_AMC_ROLE_VIEW_ALL')) {
                $amcMasterList = $amcMasterList->where('created_by', Auth::id());
            }

            return DataTables::of($amcMasterList)
                ->addIndexColumn()
                ->addColumn('display_amc_start_date', function ($row) {
                    return $this->formatDateTime('d-m-Y', $row->amc_start_date);
                })
                ->addColumn('display_amc_end_date', function ($row) {
                    return $this->formatDateTime('d-m-Y', $row->amc_end_date);
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    $html .= '<div class="btn-group">';
                    $html .= '<button type="button" class="btn btn-tool dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">';
                    $html .= '<i class="bi bi-wrench"></i>';
                    $html .= '</button>';
                    $html .= '<div class="dropdown-menu dropdown-menu-end" role="menu" style="">';
                    if (checkRights('USER_AMC_ROLE_EDIT') ) {
                        $html .= '<a href="' . route('amc-master.edit', $row->id) . '"  class="dropdown-item">Edit</a>';
                        $html .= '<a href="' . route('amc-master.renew', $row->id) . '" class="dropdown-item">Renew</a>';
                    }
                    $html .= '<a href="' . route('amc-master.edit', $row->id) . '" class="dropdown-item">View</a>';
                    $html .= '<a href="' . route('amc.download', $row->id) . '" class="dropdown-item" target="_blank">PDF</a>';
                    $html .= '</div>';
                    $html .= '</div>';
                    return $html;
                })

                ->make(true);
        }
        return view('amc-master-list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $vehicle = Vehicle::pluck('name', 'id');
        $branch = $this->branchArray;
        $salesman = User::pluck('user_name', 'id');
        $leadSource = LeadSource::pluck('name', 'id');
        $vehicleTypeArray = $this->vehicleTypeArray;
        $paymentTypeArray = $this->paymentTypeArray;
        $amcDisplayQuery = AmcMaster::select('amc_display_number')->orderBy('amc_display_number', 'DESC')->first();
        $amcDisplayNumber = 1;
        if ($amcDisplayQuery) {
            $amcDisplayNumber = $amcDisplayQuery->amc_display_number + 1;
        }
        $authId = auth()->id();
        return view('add-update-amc-master')->with(compact('leadSource', 'vehicle', 'branch', 'salesman', 'authId', 'vehicleTypeArray', 'paymentTypeArray', 'amcDisplayNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data = $request->all();
        $data['amc_start_date'] = $this->formatDateTime('Y-m-d H:i:s', $request->amc_start_date);
        $data['amc_end_date'] = $this->formatDateTime('Y-m-d H:i:s', $request->amc_end_date);
        $data['renew_status'] = $this->getArrayIdByName($this->statusArray,'New');
        $amcMaster = AmcMaster::create($data);
        if ($amcMaster) {
            $amcMasterId = $amcMaster->id;
            $amcPackageTypeId = $amcMaster->amc_package_type_id;

            if ($amcPackageTypeId) {
                $amcPackageMasterData = AmcPackageMaster::find($amcPackageTypeId);
                if ($amcPackageMasterData) {
                    $contractStartDate = Carbon::parse($amcMaster->amc_start_date);
                    $serviceDates = [];

                    $firstServiceDays = $amcPackageMasterData->vehicle_type == '1' ? 30 : 120; // for new vehicale first serve after 30days
                    $firstServiceDate = $contractStartDate->copy()->addDays($firstServiceDays);

                    $serviceDates[] = $firstServiceDate;
                    $totalServices = $amcPackageMasterData->service_count;
                    $lastServiceDate = $firstServiceDate;
                    for ($i = 1; $i < $totalServices; $i++) {
                        $nextServiceDate = $lastServiceDate->copy()->addDays(120);
                        $serviceDates[] = $nextServiceDate;
                        $lastServiceDate = $nextServiceDate;
                    }

                    foreach ($serviceDates as $serviceDate) {
                        $serviceData = [
                            'amc_id' => $amcMasterId,
                            'service_date' => $serviceDate->format('Y-m-d H:i:s'),
                            'created_by' => auth()->id(),
                        ];

                        ServiceDetail::create($serviceData);
                    }
                }
            }
            SystemLogs::create([
                'inquiry_id' => 0,
                'type' => '5', // AMC master Module ID
                'type_id' => $amcMasterId,
                'remark'     => 'Add AMC Master ',
                'action_id'  => 1,
                'created_by' => auth()->id(),
            ]);
        }
        return redirect()->route('amc-master.index')->with('success', 'Lead added successfully!');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getAmcPackageMaster(Request $request)
    {
        $amcPackageMaster = AmcPackageMaster::query()->where('vehicle_type', $request->type_id)->get();
        if ($amcPackageMaster->isNotEmpty()) {
            $data['data'] = array('amcPackageMaster' => $amcPackageMaster);
            return $this->successResponse($data);
        } else {
            return  $this->failResponse();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function renew(string $id)
    {
        $vehicle = Vehicle::pluck('name', 'id');
        $branch = $this->branchArray;
        $action = 'Renew AMC';
        $salesman = User::pluck('user_name', 'id');
        $leadSource = LeadSource::pluck('name', 'id');
        $vehicleTypeArray = $this->vehicleTypeArray;
        $paymentTypeArray = $this->paymentTypeArray;
        $amcDisplayNumber = AmcMaster::select('amc_display_number')->orderBy('amc_display_number', 'DESC')->first()->amc_display_number + 1 ?? 1;
        $amcMaster = AmcMaster::find($id);
        $authId = auth()->id();
        return view('add-update-amc-master')->with(compact('amcMaster','leadSource', 'vehicle', 'branch', 'salesman', 'authId', 'vehicleTypeArray', 'paymentTypeArray', 'amcDisplayNumber'));
    }

    public function amcPdf($id)
    {
        $data = [
            'contract_start' => '21/04/2025',
            'contract_end' => '17/05/2026',
            'vehicle_model' => 'NEXUS',
            'chassis_number' => '225MC02502250',
            'customer_name' => 'Sachin Kumar Nayak',
            'customer_mobile' => '+91 96018 32207',
            'customer_address' => 'B-302 Rudra Enclave Dumad Rd, Amin Nagar Society, Chhani, Vadodara, Gujarat 391740',
            'package' => '4 services - 1700 duration 17 months',
            'services_count' => 4,
            'services' => [
                ['date' => '22-05-2025'],
                ['date' => '19-09-2025'],
                ['date' => '17-01-2026'],
                ['date' => '17-05-2026'],
            ],
        ];

        // $query = Lead::find($id);
        // if ($query) {
        //     $data = $query;
        // }

        $pdf = Pdf::loadView('pdf.amc-pdf', $data);

        return $pdf->stream('amc.pdf');
    }
}
