<?php

namespace App\Http\Controllers;

use App\Models\AmcMaster;
use App\Models\AmcPackageMaster;
use App\Models\ServiceDetail;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    //

    public function index(Request $request)
    {
        return view('service-list');
    }

    public function addService(Request $request){
        return view('service-status-change');
    }

    public function getServiceDetailsByChassisNumber(Request $request){
        $amcMaster = AmcMaster::query()->where('chassis_number', $request->chassis_number)->where('renew_status',$this->getArrayIdByName($this->statusArray,'New'))->first();
        if ($amcMaster) {
            $serveiceData = ServiceDetail::query()->where('amc_id',$amcMaster->id)->orderBy('service_date','DESC')->first();
            $data['data'] = array('serveiceData' => $serveiceData);
            return $this->successResponse($data);
        } else {
            return  $this->failResponse();
        }
    }
}
