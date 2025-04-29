<?php

namespace App\Http\Controllers;

use App\Exports\ServiceDetailsExport;
use App\Models\AmcMaster;
use App\Models\ServiceDetail;
use App\Models\SystemLogs;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{
    //

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $serviceList = ServiceDetail::query()->where('status', 2);



            if ($request->action_type != 'report') {
                //default list 
                $serviceList = $serviceList->where('status', 2);
            } else {
                $amcIds = AmcMaster::query()
                    ->when(!empty($request->chassis_number), fn($q) => $q->where('chassis_number', $request->chassis_number))
                    ->when(!empty($request->vehicle_number), fn($q) => $q->where('vehicle_number', $request->vehicle_number))
                    ->when(!empty($request->contact_number), fn($q) => $q->where('contact_number', $request->contact_number))
                    ->when(!empty($request->vehicle_type), fn($q) => $q->where('vehicle_type', $request->vehicle_type))
                    ->when(!empty($request->vehicle_master_id), fn($q) => $q->where('vehicle_master_id', $request->vehicle_master_id))
                    ->pluck('id')
                    ->toArray();

                if(!empty($amcIds)){
                    $serviceList = $serviceList->whereIn('amc_id', $amcIds);
                }
            }


            return DataTables::of($serviceList)
                ->addIndexColumn()

                ->addColumn('customer_details', function ($row) {
                    return $row->amc_master_details->customer_name . "<br>" . $row->amc_master_details->contact_number;
                })
                ->addColumn('service_date', function ($row) {
                    return $this->formatDateTime('d-m-Y', $row->service_date);
                })
                ->addColumn('vehicle_model', function ($row) {
                    $vehicleName  = Vehicle::find($row->amc_master_details->vehicle_master_id)->first()->name ?? '';
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
                    // $html .= '<div class="btn-group">';
                    // $html .= '<button type="button" class="btn btn-tool dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">';
                    // $html .= '<i class="bi bi-wrench"></i>';
                    // $html .= '</button>';
                    // $html .= '<div class="dropdown-menu dropdown-menu-end" role="menu" style="">';
                    // if (checkRights('USER_AMC_ROLE_EDIT')) {
                    //     $notPendingServiceCount = ServiceDetail::query()->where('amc_id', $row->id)->where('status', '!=', $this->getArrayIdByName($this->statusArray, 'Pending'))->get();

                    //     if ($notPendingServiceCount->count() == 0) {
                    //         $html .= '<a href="' . route('amc-master.edit', $row->id) . '"  class="dropdown-item">Edit</a>';
                    //     }
                    //     $html .= '<a href="' . route('amc-master.renew', $row->id) . '" class="dropdown-item">Renew</a>';
                    // }
                    // $html .= '<a href="' . route('amc-master.edit', $row->id) . '" class="dropdown-item">View</a>';
                    // $html .= '<a href="' . route('amc.download', $row->id) . '" class="dropdown-item" target="_blank">PDF</a>';
                    // if (Carbon::parse($row->amc_end_date)->isFuture()) {
                    //     $html .= '<a href="javascript:void()" class="dropdown-item change-status" data-id="' . $row->id . '" data-status="' . $row->status . '">Status</a>';
                    // }
                    // $html .= '</div>';
                    // $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['action', 'customer_details', 'contact_date', 'vehicle_data', 'display_status', 'vehicle_model'])
                ->make(true);
        }
        $vehicle = Vehicle::pluck('name', 'id');
        $vehicleTypeArray = $this->vehicleTypeArray;
        $serviceStatus = [
            "1" => 'Pending',
            "2" => 'Completed',
        ];

        return view('service-list')->with(compact('vehicle', 'vehicleTypeArray', 'serviceStatus'));
    }

    public function addService(Request $request)
    {
        return view('service-status-change');
    }

    public function getServiceDetailsByChassisNumber(Request $request)
    {
        $amcMaster = AmcMaster::query()->where('chassis_number', $request->chassis_number)->where('renew_status', $this->getArrayIdByName($this->statusArray, 'New'))->first();
        if ($amcMaster) {
            $serveiceData = ServiceDetail::query()->where('amc_id', $amcMaster->id)->orderBy('service_date', 'ASC')->where('status', '1')->first();
            $serveiceDataList = ServiceDetail::query()->where('amc_id', $amcMaster->id)->orderBy('service_date', 'ASC')->get();
            $serviceFlag = false;
            if ($serveiceData) {
                $serviceFlag = true;
            }
            $data['data'] = array('serveiceData' => $serveiceData, 'serveiceDataList' => $serveiceDataList, 'serviceFlag' => $serviceFlag);
            return $this->successResponse($data);
        } else {
            return  $this->failResponse();
        }
    }


    public function addServiceHandel(Request $request)
    {

        $filePath = null;
        $fileData = request()->filename;
        $amc_id = $request->amc_id;
        $service_id = $request->service_id;
        $service_remark = $request->service_remark;
        $status_id = $request->status_id;

        $amcMaster = AmcMaster::find($amc_id);

        $updateData['service_remark'] = $service_remark;
        $updateData['status'] = $status_id;
        $updateData['modified_by'] = Auth::id();
        if ($request->hasFile('filename')) {
            $extension = $fileData->getClientOriginalExtension();
            $imageName = date('YmdHis') . '_' . time()  . '.' . $extension;

            $directory = 'assets/attachment/amc-master/' . $amc_id . '/service/';
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            if ($fileData->move($directory, $imageName)) {
                $updateData['attachment'] = $imageName;
            }
        }

        ServiceDetail::where('id', $service_id)->update($updateData);

        $serviceData = ServiceDetail::find($service_id)->first();

        SystemLogs::create([
            'inquiry_id' => 0,
            'type' => '6', // Service Module ID
            'type_id' => $service_id,
            'remark'     => 'Serive Status Update ',
            'action_id'  => 1,
            'created_by' => Auth::id(),
        ]);
        $whatsAppMsg = "Hi $amcMaster->customer_name \n \n";

        $whatsAppMsg .= "Your vehicle $amcMaster->vehicle_number has been successfully serviced under AMC Contract ID: amc_display_number-> \n";
        $whatsAppMsg .= "Service Date: *$serviceData->display_service_date* \n";
        $whatsAppMsg .= "Next Service Due: *$serviceData->display_service_date* \n";
        $whatsAppMsg .= "Next Service Due: Ampere Service Center, Ahmedabad \n";
        $whatsAppMsg .= "Our team has completed all required checks and maintenance as per AMC guidelines. Your vehicle is now ready for delivery. \n \n";
        $whatsAppMsg .= "For feedback or questions, feel free to reply to this message. \n";
        $whatsAppMsg .= "Thank you for choosing Ampere! \n\n ";
        $whatsAppMsg .= "Support: +91 90233 42463";
        return redirect()->route('amc-master-service.index')->with('success', 'Service Update successfully!');
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

            return Excel::download(new ServiceDetailsExport($exportStartDate, $exportEndDate, $exportChassisNumber, $exportVehicleNumber, $exportContactNumber, $exportVehicleType, $exportvehicleMasterId), 'service.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
