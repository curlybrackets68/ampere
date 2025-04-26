<?php

namespace App\Http\Controllers;

use App\Models\AmcMaster;
use App\Models\AmcPackageMaster;
use App\Models\ServiceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ServiceController extends Controller
{
    //

    public function index(Request $request)
    {
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

        return redirect()->route('amc-master-service.index')->with('success', 'Service Update successfully!');
    }
}
