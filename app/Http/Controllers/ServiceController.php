<?php

namespace App\Http\Controllers;

use App\Models\AmcMaster;
use App\Models\AmcPackageMaster;
use App\Models\ServiceDetail;
use App\Models\SystemLogs;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{
    //

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $amcMasterList = ServiceDetail::query()->where('status',2);

            return DataTables::of($amcMasterList)
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
        return view('service-list');
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

        SystemLogs::create([
            'inquiry_id' => 0,
            'type' => '6', // Service Module ID
            'type_id' => $service_id,
            'remark'     => 'Serive Status Update ',
            'action_id'  => 1,
            'created_by' => Auth::id(),
        ]);
        return redirect()->route('amc-master-service.index')->with('success', 'Service Update successfully!');
    }
}
