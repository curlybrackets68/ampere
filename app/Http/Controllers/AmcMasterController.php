<?php

namespace App\Http\Controllers;

use App\Models\AmcPackageMaster;
use App\Models\LeadSource;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class AmcMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $authId = auth()->id();
        return view('add-update-amc-master')->with(compact('leadSource', 'vehicle', 'branch','salesman','authId','vehicleTypeArray'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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

    public function getAmcPackageMaster(Request $request){
        $amcPackageMaster = AmcPackageMaster::query()->where('vehicle_type', $request->type_id)->get();
        if($amcPackageMaster->isNotEmpty()){
            $data['data'] = array('amcPackageMaster' => $amcPackageMaster);
            return $this->successResponse($data);
        }else{
            return  $this->failResponse();
        }
    }
}
