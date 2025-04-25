<?php

namespace App\Http\Controllers;

use App\Models\AmcMaster;
use App\Models\AmcPackageMaster;
use App\Models\ServiceDetail;
use Illuminate\Http\Request;
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
            $serveiceData = ServiceDetail::query()->where('amc_id', $amcMaster->id)->orderBy('service_date', 'DESC')->first();
            $data['data'] = array('serveiceData' => $serveiceData);
            return $this->successResponse($data);
        } else {
            return  $this->failResponse();
        }
    }


    public function addServiceHandel(Request $request)
    {

        $filePath = null;
        $fileData = request()->file;
        $amcMasterID = 0;
        if ($request->hasFile('filename')) {
            $extension = $fileData[0]->getClientOriginalExtension();
            $imageName = date('YmdHis') . '_' . time() . '_' . '.' . $extension;

            $directory = 'assets/attachment/amc-master/' . $amcMasterID . '/service/';
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            if ($fileData->move($directory, $imageName)) {
                $fileArray = [
                    'filePath' => asset($directory . $imageName),
                    'fileRelativePath' => $directory . $imageName,
                    'tefileName' => $imageName,
                ];
            }
        }
    }
}
