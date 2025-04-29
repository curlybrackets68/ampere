<?php

namespace App\Http\Controllers;

use App\Exports\AmcExport;
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
use Maatwebsite\Excel\Facades\Excel;
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

            if ($request->action_type != 'report') {
                //default list 
                $amcMasterList = $amcMasterList->where('renew_status', $this->getArrayIdByName($this->statusArray, 'New'));
            }
            if (checkRights('USER_AMC_ROLE_VIEW') && !checkRights('USER_AMC_ROLE_VIEW_ALL')) {
                $amcMasterList = $amcMasterList->where('created_by', Auth::id());
            }

            if (!empty($request->chassis_number)) {
                $amcMasterList = $amcMasterList->where('chassis_number', $request->chassis_number);
            }
            if (!empty($request->vehicle_number)) {
                $amcMasterList = $amcMasterList->where('vehicle_number', $request->vehicle_number);
            }
            if (!empty($request->contact_number)) {
                $amcMasterList = $amcMasterList->where('contact_number', $request->contact_number);
            }
            if (!empty($request->vehicle_type)) {
                $amcMasterList = $amcMasterList->where('vehicle_type', $request->vehicle_type);
            }
            if (!empty($request->vehicle_master_id)) {
                $amcMasterList = $amcMasterList->where('vehicle_master_id', $request->vehicle_master_id);
            }

            return DataTables::of($amcMasterList)
                ->addIndexColumn()

                ->addColumn('customer_details', function ($row) {
                    return $row->customer_name . "<br>" . $row->contact_number;
                })
                ->addColumn('contact_date', function ($row) {
                    return $this->formatDateTime('d-m-Y', $row->amc_start_date) . "<br>" . $this->formatDateTime('d-m-Y', $row->amc_end_date);
                })
                ->addColumn('vehicle_model', function ($row) {
                    $vehicleName  = '';
                    $query = Vehicle::find($row->vehicle_master_id);
                    if ($query) {
                        $vehicleName =  $query->name ?? '';
                    }

                    return $this->getArrayNameById($this->vehicleTypeArray, $row->vehicle_type) . '<br>' . $vehicleName;
                })->addColumn('vehicle_data', function ($row) {
                    return $row->chassis_number . "<br>" . $row->vehicle_number;
                })
                ->addColumn('display_status', function ($row) {
                    $class = 'warning';
                    $html = '<button type="button" class="btn btn-' . $class . ' btn-sm " data-id="' . $row->id . '" data-status="' . $row->status_id . '">' . $this->getArrayNameById($this->statusArray, $row->status) . '</button>';
                    return $html;
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    $html .= '<div class="btn-group">';
                    $html .= '<button type="button" class="btn btn-tool dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">';
                    $html .= '<i class="bi bi-wrench"></i>';
                    $html .= '</button>';
                    $html .= '<div class="dropdown-menu dropdown-menu-end" role="menu" style="">';
                    if (checkRights('USER_AMC_ROLE_EDIT')) {
                        $notPendingServiceCount = ServiceDetail::query()->where('amc_id', $row->id)->where('status', '!=', $this->getArrayIdByName($this->statusArray, 'Pending'))->get();

                        if ($notPendingServiceCount->count() == 0) {
                            $html .= '<a href="' . route('amc-master.edit', $row->id) . '"  class="dropdown-item">Edit</a>';
                        }
                        $html .= '<a href="' . route('amc-master.renew', $row->id) . '" class="dropdown-item">Renew</a>';
                    }
                    $html .= '<a href="javascript:void(0);" class="dropdown-item amc-view" data-id="' . $row->id . '">View</a>';
                    $html .= '<a href="' . route('amc.download', $row->id) . '" class="dropdown-item" target="_blank">PDF</a>';
                    $html .= '<a href="#" class="dropdown-item amc-add-inquiry" data-id="' . $row->id . '" data-vehicle-number="'.$row->vehicle_number.'" data-customer-name="'.$row->customer_name.'" data-customer-number="'.$row->contact_number.'">Add Inquiry</a>';
                    if (Carbon::parse($row->amc_end_date)->isFuture()) {
                        $html .= '<a href="javascript:void(0);" class="dropdown-item change-status" data-id="' . $row->id . '" data-status="' . $row->status . '">Status</a>';
                    }
                    $html .= '</div>';
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['action', 'customer_details', 'contact_date', 'vehicle_data', 'display_status', 'vehicle_model'])
                ->make(true);
        }
        $vehicle = Vehicle::pluck('name', 'id');
        $vehicleTypeArray = $this->vehicleTypeArray;
        $serviceTypeArray = $this->serviceTypeArray;
        $branch = $this->branchArray;

        return view('amc-master-list')->with(compact('vehicle', 'vehicleTypeArray','branch','serviceTypeArray'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $vehicle = Vehicle::pluck('name', 'id');
        $vehicleTypeArray = $this->vehicleTypeArray;
        $paymentTypeArray = $this->paymentTypeArray;
        $amcDisplayNumber = AmcMaster::select('amc_display_number')->orderBy('amc_display_number', 'DESC')->first() ?? 0;
        $amcDisplayNumber = $amcDisplayNumber ? $amcDisplayNumber->amc_display_number + 1 : 1;
        return view('add-update-amc-master')->with(compact('vehicle', 'vehicleTypeArray', 'paymentTypeArray', 'amcDisplayNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data = $request->all();
        $data['amc_start_date'] = $this->formatDateTime('Y-m-d H:i:s', $request->amc_start_date);
        $data['amc_end_date'] = $this->formatDateTime('Y-m-d H:i:s', $request->amc_end_date);
        $data['renew_status'] = $this->getArrayIdByName($this->statusArray, 'New');
        $data['amc_type'] = '2'; // paid serive
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
                            'created_by' => Auth::id(),
                        ];

                        ServiceDetail::create($serviceData);
                    }
                    $vehicleTypeName = $this->getArrayNameById($this->vehicleTypeArray, $amcMaster->vehicle_type);
                    $startDate = $this->formatDateTime('d-M-Y', $amcMaster->amc_start_date);
                    $endDate = $this->formatDateTime('d-M-Y', $amcMaster->amc_end_date);
                    $vehicleName  = Vehicle::find($amcMaster->vehicle_master_id)->first()->name ?? '';
                    $packageString = $amcPackageMasterData->service_count . ' Sevices - ' . $amcPackageMasterData->duration . ' duration ' . $amcPackageMasterData->time_period . 'months';
                    $whatsAppMsg = "Hi $amcMaster->customer_name \n \n";

                    $whatsAppMsg .= "Your AMC contract has been successfully generated for your vehicle $amcMaster->vehicle_number \n";
                    $whatsAppMsg .= "Contract ID: *$amcMaster->amc_display_number* \n";
                    $whatsAppMsg .= "Vehicle Category: *$vehicleTypeName* \n";
                    $whatsAppMsg .= "Contract Start Date: *$startDate* \n";
                    $whatsAppMsg .= "Valid Till: *$endDate* \n";
                    $whatsAppMsg .= "Vehicle Model: *$vehicleName* \n";
                    $whatsAppMsg .= "Service Details: *$packageString* \n";
                    $whatsAppMsg .= "You can now enjoy hassle-free service and priority support under your AMC plan. \n \n";
                    $whatsAppMsg .= "Thank you for choosing Ampere! \n";
                    $whatsAppMsg .= "For queries, contact us at +91 90233 42463.";
                    $pdfUrl = $this->generateAndStorePdf('pdf.amc-pdf', ['amc' => $amcMaster], 'amc_pdfs');

                    //$this->sendWhatsAppMessage($amcMaster->contact_number, $whatsAppMsg);
                    // $this->sendWhatsAppMessageWithFile($amcMaster->contact_number, $whatsAppMsg, $pdfUrl);
                }
            }
            SystemLogs::create([
                'inquiry_id' => 0,
                'type' => '5', // AMC master Module ID
                'type_id' => $amcMasterId,
                'remark'     => 'Add AMC Master ',
                'action_id'  => 1,
                'created_by' => Auth::id(),
            ]);
        }


        return redirect()->route('amc-master.index')->with('success', 'AMC Master added successfully!');
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
        $vehicle = Vehicle::pluck('name', 'id');
        $action = 'update';
        $vehicleTypeArray = $this->vehicleTypeArray;
        $paymentTypeArray = $this->paymentTypeArray;
        $amcDisplayNumber = AmcMaster::select('amc_display_number')->orderBy('amc_display_number', 'DESC')->first() ?? 0;
        $amcDisplayNumber = $amcDisplayNumber ? $amcDisplayNumber->amc_display_number + 1 : 1;
        $amcMaster = AmcMaster::find($id);
        return view('add-update-amc-master')->with(compact('amcMaster',  'vehicle',  'vehicleTypeArray', 'paymentTypeArray', 'amcDisplayNumber'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $data['amc_start_date'] = $this->formatDateTime('Y-m-d H:i:s', $request->amc_start_date);
        $data['amc_end_date'] = $this->formatDateTime('Y-m-d H:i:s', $request->amc_end_date);
        $data['renew_status'] = $this->getArrayIdByName($this->statusArray, 'New');

        $amcMaster = AmcMaster::find($id);
        if ($amcMaster) {
            if ($amcMaster) {
                $amcMaster->update($data);
            }
            ServiceDetail::where('amc_id', $id)
                ->update(['deleted_by' => Auth::id()]);

            ServiceDetail::where('amc_id', $id)->delete();
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
                            'created_by' => Auth::id(),
                        ];

                        ServiceDetail::create($serviceData);
                    }
                }
            }
            SystemLogs::create([
                'inquiry_id' => 0,
                'type' => '5', // AMC master Module ID
                'type_id' => $amcMasterId,
                'remark'     => 'Update AMC Master ',
                'action_id'  => 2,
                'created_by' => Auth::id(),
            ]);
        }
        return redirect()->route('amc-master.index')->with('success', 'AMC Master Update successfully!');
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
    public function renew(Request $request, string $id)
    {

        $vehicle = Vehicle::pluck('name', 'id');
        $action = 'Renew AMC';
        $vehicleTypeArray = $this->vehicleTypeArray;
        $paymentTypeArray = $this->paymentTypeArray;
        $amcDisplayNumber = AmcMaster::select('amc_display_number')->orderBy('amc_display_number', 'DESC')->first()->amc_display_number + 1 ?? 1;
        $amcMaster = AmcMaster::find($id);
        return view('renew-amc-master')->with(compact('amcMaster',  'vehicle',  'vehicleTypeArray', 'paymentTypeArray', 'amcDisplayNumber'));
    }

    public function renewHandel(Request $request)
    {
        $data = $request->all();

        $data['amc_start_date'] = $this->formatDateTime('Y-m-d H:i:s', $request->amc_start_date);
        $data['amc_end_date'] = $this->formatDateTime('Y-m-d H:i:s', $request->amc_end_date);
        $data['renew_status'] = $this->getArrayIdByName($this->statusArray, 'New');
        $data['amc_type'] = '2'; // paid serive
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
                            'created_by' => Auth::id(),
                        ];

                        ServiceDetail::create($serviceData);
                    }
                }
            }
            AmcMaster::where('id', $request->amc_reference_id)->update(['renew_status' => $this->getArrayIdByName($this->statusArray, 'Renew')]);
            SystemLogs::create([
                'inquiry_id' => 0,
                'type' => '5', // AMC master Module ID
                'type_id' => $request->amc_reference_id,
                'remark'     => 'Renew AMC Master ',
                'action_id'  => $this->getArrayIdByName($this->actionLogsArray, 'Renew'),
                'created_by' => Auth::id(),
            ]);
            SystemLogs::create([
                'inquiry_id' => 0,
                'type' => '5', // AMC master Module ID
                'type_id' => $amcMasterId,
                'remark'     => 'Add New AMC Master ',
                'action_id'  => $this->getArrayIdByName($this->actionLogsArray, '1'),
                'created_by' => Auth::id(),
            ]);
        }
        return redirect()->route('amc-master.index')->with('success', 'AMC Master Renew successfully!');
    }

    public function amcPdf($id)
    {
        $query = AmcMaster::with('services')->find($id);
        if ($query) {
            $data = $query;
        }
        $pdf = Pdf::loadView('pdf.amc-pdf', ['amc' => $data]);

        return $pdf->stream('amc.pdf');
    }

    function changeStatus(Request $request)
    {
        $amcId = $request->amcId;
        $statusId = $request->statusId;
        $statusRemark = $request->statusRemark;

        $save = AmcMaster::where('id', $amcId)->update(['status' => $statusId, 'status_remark' => $statusRemark ?? '']);
        if ($save) {
            SystemLogs::create([
                'type' => '5',
                'type_id' => $amcId,
                'remark' => 'Status changed to ' . $this->getArrayNameById($this->statusArray, $statusId),
                'action_id' => 3,
                'created_by' => Auth::id(),
            ]);
            return response()->json(['code' => 1, 'message' => 'Status updated successfully']);
        } else {
            return response()->json(['code' => 0, 'message' => 'Failed to update status']);
        }
    }

    public function export(Request $request)
    {
        try {
            $exportStartDate = $request->input('exportStartDate');
            $exportEndDate = $request->input('exportEndDate');
            $exportChassisNumber = $request->input('exportChassisNumber');
            $exportVehicleNumber = $request->input('exportVehicleNumber');
            $exportContactNumber = $request->input('exportContactNumber');
            $exportVehicleType = $request->input('exportVehicleType');
            $exportvehicleMasterId = $request->input('exportvehicleMasterId');

            return Excel::download(new AmcExport($exportStartDate, $exportEndDate, $exportChassisNumber, $exportVehicleNumber, $exportContactNumber, $exportVehicleType, $exportvehicleMasterId), 'amc.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    function geAmcViewDetails(Request $request)
    {
        $amcId = $request->amcId;
        $data = [];

        $amcQuery = AmcMaster::find($amcId);
        if ($amcQuery) {
            $amcData = $amcQuery;

            $serviceQuery = ServiceDetail::where('amc_id', $amcId)->get();

            $amcDetailsQuery = AmcMaster::where('amc_reference_id', $amcId)->get();

            $historyData = SystemLogs::where('type', '5')->where('type_id', $amcId)->get();
            $data = [
                'amc' => $amcData,
                'amcDetail' => $amcDetailsQuery->isNotEmpty() ? $amcDetailsQuery : [],
                'serviceDetail' => $serviceQuery->isNotEmpty() ? $serviceQuery : [],
                'historyData' => $historyData->isNotEmpty() ? $historyData : [],
            ];

            return response()->json(['code' => '1', 'data' => $data]);
        } else {
            return response()->json(['code' => 0, 'message' => 'No Amc Details']);
        }
    }
}
