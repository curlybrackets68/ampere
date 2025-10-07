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
use Illuminate\Support\Facades\DB;
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
            $amcMasterList = AmcMaster::query()->orderBy('amc_end_date');

            if ($request->action_type != 'report') {
                //default list
                $amcMasterList = $amcMasterList->where('renew_status', $this->getArrayIdByName($this->statusArray, 'New'));
            }
            if (checkRights('USER_AMC_ROLE_VIEW') && !checkRights('USER_AMC_ROLE_VIEW_ALL')) {
                $amcMasterList = $amcMasterList->where('created_by', Auth::id());
            }
            if (! empty($request->startDate) && ! empty($request->endDate)) {
                $amcMasterList = $amcMasterList->whereBetween(DB::raw('DATE(amc_end_date)'), [$request->startDate, $request->endDate]);
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
            if (!empty($request->amc_status_id)) {
                $amcMasterList = $amcMasterList->where('status', $request->amc_status_id);
            }

            return DataTables::of($amcMasterList)
                ->addIndexColumn()

                ->addColumn('customer_details', function ($row) {
                    return $row->customer_name . "<br>" . $row->contact_number;
                })
                ->addColumn('contact_date', function ($row) {
                    return $this->formatDateTime('d-m-Y', $row->amc_start_date) . "<br>" . $this->formatDateTime('d-m-Y', $row->amc_end_date);
                })
                ->addColumn('row_class', function ($row) {
                    $amcEndDate = Carbon::parse($row->amc_end_date);
                    $today = Carbon::today();

                    return $amcEndDate <= $today ? 'light-red' : '';
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
                    $serviceData = ServiceDetail::query()->where('status', 1)->where('inquiry_flag', 2)->where('inquiry_id', '!=', 0)->where('amc_id', $row->id)->orderBy('service_date', 'ASC')->first();
                    $htmlInfo = '';
                    if ($serviceData) {
                        if (checkRights('USER_INQUIRY_ROLE_VIEW') || checkRights('USER_INQUIRY_ROLE_VIEW_ALL')) {
                            $htmlInfo = '<a href="' . route('inquiry') . '" class=""><i class="bi bi-info-circle-fill"></i></a>';
                        } else {
                            $htmlInfo = '<a href="javascript:void(0);" class="btn btn-' . $class . ' btn-sm " data-id="' . $row->id . '" data-status="' . $row->status_id . '">' . $this->getArrayNameById($this->statusArray, $row->status) . '</a>';
                        }
                    }

                    $html = '<a href="javascript:void(0);" class="btn btn-' . $class . ' btn-sm " data-id="' . $row->id . '" data-status="' . $row->status_id . '">' . $this->getArrayNameById($this->statusArray, $row->status) . '</a> <br> ' . $htmlInfo;
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
                        if ($row->status == $this->getArrayIdByName($this->statusArray, 'Deactive') && $row->renew_status == $this->getArrayIdByName($this->statusArray, 'New')) {
                            $html .= '<a href="' . route('amc-master.renew', $row->id) . '" class="dropdown-item">Renew</a>';
                        }
                    }
                    $html .= '<a href="javascript:void(0);" class="dropdown-item amc-view" data-id="' . $row->id . '">View</a>';
                    $html .= '<a href="' . route('amc.download', $row->id) . '" class="dropdown-item" target="_blank">PDF</a>';

                    $html .= '<a href="javascript:void(0);" class="dropdown-item amc-add-inquiry" data-id="' . $row->id . '" data-vehicle-number="' . $row->vehicle_number . '" data-customer-name="' . $row->customer_name . '" data-customer-number="' . $row->contact_number . '">Add Inquiry</a>';
                    if (Carbon::parse($row->amc_end_date)->isFuture()) {
                        $html .= '<a href="javascript:void(0);" class="dropdown-item change-status" data-id="' . $row->id . '" data-status="' . $row->status . '">Status</a>';
                    }
                    $html .= '</div>';
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['action', 'customer_details', 'contact_date', 'vehicle_data', 'display_status', 'vehicle_model', 'row_class'])
                ->make(true);
        }
        $vehicle = Vehicle::pluck('name', 'id');
        $vehicleTypeArray = $this->vehicleTypeArray;
        $serviceTypeArray = $this->serviceTypeArray;
        $branch = $this->branchArray;
        $serviceStatus = [
            "10" => 'Active',
            "11" => 'Deactive',
        ];
        return view('amc-master-list')->with(compact('vehicle', 'vehicleTypeArray', 'branch', 'serviceTypeArray', 'serviceStatus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $vehicle = Vehicle::pluck('name', 'id');
        $vehicleTypeArray = $this->vehicleTypeArray;
        $paymentTypeArray = $this->paymentTypeArray;
        $amcDisplayNumber = AmcMaster::select('amc_display_number')->orderBy('id', 'DESC')->first() ?? 0;
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
                    $serviceKm = [];

                    $firstServiceDays = $amcPackageMasterData->vehicle_type == '1' ? 30 : 120; // for new vehicle first service after 30 days
                    $kmInterval = $amcPackageMasterData->vehicle_type == '1' ? 1000 : 4000; // you can change this based on requirement

                    if ($amcMaster->vehicle_master_id == 4) {
                        // TVS King EV Max
                        $firstServiceDays = $amcPackageMasterData->vehicle_type == '1' ? 40 : 40; // for new vehicle first service after 30 days
                        $kmInterval = $amcPackageMasterData->vehicle_type == '1' ? 1000 : 1000; // you can change this based on requirement
                    } else if ($amcMaster->vehicle_master_id == 6) {
                        // TVS King Deluxe
                        $firstServiceDays = $amcPackageMasterData->vehicle_type == '1' ? 25 : 25; // for new vehicle first service after 30 days
                        $kmInterval = $amcPackageMasterData->vehicle_type == '1' ? 1000 : 1000; // you can change this based on requirement
                    }
                    $firstServiceDate = $contractStartDate->copy()->addDays($firstServiceDays);
                    $firstServiceKm = $amcMaster->amc_start_km + $kmInterval;
                    $serviceDates[] = $firstServiceDate;
                    $serviceKm[] = $firstServiceKm; // Add initial KM


                    $totalServices = $amcPackageMasterData->service_count;
                    $lastServiceDate = $firstServiceDate;
                    $lastServiceKm = $firstServiceKm;

                    for ($i = 1; $i < $totalServices; $i++) {
                        $daysAdd = 120;
                        $additionKm = 4000;
                        if ($amcMaster->vehicle_master_id == 4) {
                            // TVS King EV Max
                            $additionKm = 10000;
                            if ($i <= 2) {
                                $daysAdd = 40;
                            } else if ($i <= 3 && $i >= 5) {
                                $daysAdd = 90;
                            } else if ($i <= 6 && $i >= 16) {
                                $daysAdd = 120;
                            }
                        } else  if ($amcMaster->vehicle_master_id == 6) {
                            // TVS King Deluxe
                            $additionKm = 10000;
                            if ($i == 1) {
                                $daysAdd = 25;
                            } else {
                                $daysAdd = 45;
                            }
                        }

                        $nextServiceDate = $lastServiceDate->copy()->addDays($daysAdd); // Next service after 120 days

                        $nextServiceKm = $lastServiceKm + $additionKm; // Add KM interval

                        $serviceDates[] = $nextServiceDate;
                        $serviceKm[] = $nextServiceKm;

                        $lastServiceDate = $nextServiceDate;
                        $lastServiceKm = $nextServiceKm;
                    }

                    for ($i = 0; $i < count($serviceDates); $i++) {
                        $serviceType = 2;
                        $reminderDays = 0;
                        if ($amcMaster->vehicle_master_id == 4) {
                            // TVS King EV Max
                            if ($i <= 2) {
                                $serviceType = 1;
                            }
                            $reminderDays = $this->reminderDays['4'][$i];
                        } elseif ($amcMaster->vehicle_master_id == 6) {
                            // TVS King Deluxe
                            if ($i <= 2) {
                                $serviceType = 1;
                            }
                            $reminderDays = $this->reminderDays['6'][$i];
                        }
                        $serviceData = [
                            'service_no' => $i + 1,
                            'service_km' => $serviceKm[$i],
                            'service_type' => $serviceType,
                            'reminder_days' => $reminderDays,
                            'amc_id' => $amcMasterId,
                            'service_date' => $serviceDates[$i]->format('Y-m-d H:i:s'),
                            'created_by' => Auth::id(),
                        ];

                        ServiceDetail::create($serviceData);
                    }

                    $vehicleTypeName = $this->getArrayNameById($this->vehicleTypeArray, $amcMaster->vehicle_type);
                    $startDate = $this->formatDateTime('d-M-Y', $amcMaster->amc_start_date);
                    $endDate = $this->formatDateTime('d-M-Y', $amcMaster->amc_end_date);
                    $vehicleName  = Vehicle::find($amcMaster->vehicle_master_id)->first()->name ?? '';
                    $packageString = $amcPackageMasterData->service_count . ' Sevices - ' . $amcPackageMasterData->price . ' duration ' . $amcPackageMasterData->time_period . 'months';
                    $whatsAppMsg = "Hi $amcMaster->customer_name \n \n";

                    $whatsAppMsg .= "Your AMC contract has been successfully generated for your vehicle *$amcMaster->vehicle_number* \n \n";
                    $whatsAppMsg .= "Contract ID: *$amcMaster->amc_display_number* \n";
                    $whatsAppMsg .= "Vehicle Category: *$vehicleTypeName* \n";
                    $whatsAppMsg .= "Contract Start Date: *$startDate* \n";
                    $whatsAppMsg .= "Valid Till: *$endDate* \n";
                    $whatsAppMsg .= "Vehicle Model: *$vehicleName* \n";
                    $whatsAppMsg .= "Service Details: *$packageString* \n \n";
                    $whatsAppMsg .= "You can now enjoy hassle-free service and priority support under your AMC plan. \n \n";
                    $whatsAppMsg .= "Thank you for choosing Ampere! \n";
                    $whatsAppMsg .= "For queries, contact us at +91 90233 42463.";
                    $pdfUrl = $this->generateAndStorePdf('pdf.amc-pdf', ['amc' => $amcMaster], 'amc_pdfs');

                    $data = $this->sendWhatsAppMessageWithFile($amcMaster->contact_number, $whatsAppMsg, $pdfUrl['public_url'], 'amc_pdf');
                }
            }
            SystemLogs::create([
                'inquiry_id' => 0,
                'type' => '5', // AMC master Module ID
                'type_id' => $amcMasterId,
                'remark'     => 'Add AMC # ' . $amcMaster->amc_display_number,
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
        $amcPackageMasterData = AmcPackageMaster::where('vehicle_type', $amcMaster->vehicle_type)->get();
        $amcPackageMaster = [];
        if ($amcPackageMasterData) {
            foreach ($amcPackageMasterData as $value) {
                $amcPackageMaster[$value->id] = $value->service_count . ' Services';
            }
        }
        return view('add-update-amc-master')->with(compact('amcMaster',  'vehicle',  'vehicleTypeArray', 'paymentTypeArray', 'amcDisplayNumber', 'amcPackageMaster'));
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
                    $serviceKm = [];
                    $firstServiceDays = $amcPackageMasterData->vehicle_type == '1' ? 30 : 120; // for new vehicle first service after 30 days
                    $kmInterval = $amcPackageMasterData->vehicle_type == '1' ? 1000 : 4000; // you can change this based on requirement


                    if ($amcMaster->vehicle_master_id == 4) {
                        // TVS King EV Max
                        $firstServiceDays = $amcPackageMasterData->vehicle_type == '1' ? 40 : 40; // for new vehicle first service after 30 days
                        $kmInterval = $amcPackageMasterData->vehicle_type == '1' ? 1000 : 1000; // you can change this based on requirement
                    } else if ($amcMaster->vehicle_master_id == 6) {
                        // TVS King Deluxe
                        $firstServiceDays = $amcPackageMasterData->vehicle_type == '1' ? 25 : 25; // for new vehicle first service after 30 days
                        $kmInterval = $amcPackageMasterData->vehicle_type == '1' ? 1000 : 1000; // you can change this based on requirement
                    }
                    $firstServiceDate = $contractStartDate->copy()->addDays($firstServiceDays);
                    $firstServiceKm = $amcMaster->amc_start_km + $kmInterval;
                    $serviceDates[] = $firstServiceDate;
                    $serviceKm[] = $firstServiceKm; // Add initial KM

                    $totalServices = $amcPackageMasterData->service_count;
                    $lastServiceDate = $firstServiceDate;
                    $lastServiceKm = $firstServiceKm;

                    for ($i = 1; $i < $totalServices; $i++) {
                        $daysAdd = 120;
                        $additionKm = 4000;
                        if ($amcMaster->vehicle_master_id == 4) {
                            $additionKm = 10000;
                            if ($i <= 2) {
                                $daysAdd = 40;
                            } else if ($i <= 3 && $i >= 5) {
                                $daysAdd = 90;
                            } else if ($i <= 6 && $i >= 16) {
                                $daysAdd = 120;
                            }
                        } else  if ($amcMaster->vehicle_master_id == 6) {
                            // TVS King Deluxe
                            $additionKm = 10000;
                            if ($i == 1) {
                                $daysAdd = 25;
                            } else {
                                $daysAdd = 45;
                            }
                        }
                        $nextServiceDate = $lastServiceDate->copy()->addDays($daysAdd); // Next service after 120 days
                        $nextServiceKm = $lastServiceKm + $additionKm; // Add KM interval

                        $serviceDates[] = $nextServiceDate;
                        $serviceKm[] = $nextServiceKm;

                        $lastServiceDate = $nextServiceDate;
                        $lastServiceKm = $nextServiceKm;
                    }

                    for ($i = 0; $i < count($serviceDates); $i++) {
                        $serviceType = 2;
                        $reminderDays = 0;
                        if ($amcMaster->vehicle_master_id == 4) {
                            // TVS King EV Max
                            if ($i <= 2) {
                                $serviceType = 1;
                            }
                            $reminderDays = $this->reminderDays['4'][$i];
                        } elseif ($amcMaster->vehicle_master_id == 6) {
                            // TVS King Deluxe
                            if ($i <= 2) {
                                $serviceType = 1;
                            }
                            $reminderDays = $this->reminderDays['6'][$i];
                        }
                        $serviceData = [
                            'service_no' => $i + 1,
                            'service_km' => $serviceKm[$i],
                            'service_type' => $serviceType,
                            'reminder_days' => $reminderDays,
                            'amc_id' => $amcMasterId,
                            'service_date' => $serviceDates[$i]->format('Y-m-d H:i:s'),
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
                'remark'     => 'Update AMC # ' . $amcMaster->amc_display_number,
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
                    $serviceKm = [];
                    $firstServiceDays = $amcPackageMasterData->vehicle_type == '1' ? 30 : 120; // for new vehicle first service after 30 days
                    $kmInterval = $amcPackageMasterData->vehicle_type == '1' ? 1000 : 4000; // you can change this based on requirement

                    if ($amcMaster->vehicle_master_id == 4) {
                        // TVS King EV Max
                        $firstServiceDays = $amcPackageMasterData->vehicle_type == '1' ? 40 : 40; // for new vehicle first service after 30 days
                        $kmInterval = $amcPackageMasterData->vehicle_type == '1' ? 1000 : 1000; // you can change this based on requirement
                    } else if ($amcMaster->vehicle_master_id == 6) {
                        // TVS King Deluxe
                        $firstServiceDays = $amcPackageMasterData->vehicle_type == '1' ? 25 : 25; // for new vehicle first service after 30 days
                        $kmInterval = $amcPackageMasterData->vehicle_type == '1' ? 1000 : 1000; // you can change this based on requirement
                    }

                    $firstServiceDate = $contractStartDate->copy()->addDays($firstServiceDays);
                    $firstServiceKm = $amcMaster->amc_start_km + $kmInterval;
                    $serviceDates[] = $firstServiceDate;
                    $serviceKm[] = $firstServiceKm; // Add initial KM

                    $totalServices = $amcPackageMasterData->service_count;
                    $lastServiceDate = $firstServiceDate;
                    $lastServiceKm = $firstServiceKm;

                    for ($i = 1; $i < $totalServices; $i++) {
                        $daysAdd = 120;
                        $additionKm = 4000;
                        if ($amcMaster->vehicle_master_id == 4) {
                            $additionKm = 10000;
                            if ($i <= 2) {
                                $daysAdd = 45;
                            } else if ($i <= 3 && $i >= 5) {
                                $daysAdd = 90;
                            } else if ($i <= 6 && $i >= 16) {
                                $daysAdd = 120;
                            }
                        } else  if ($amcMaster->vehicle_master_id == 6) {
                            // TVS King Deluxe
                            $additionKm = 10000;
                            if ($i == 1) {
                                $daysAdd = 25;
                            } else {
                                $daysAdd = 45;
                            }
                        }
                        $nextServiceDate = $lastServiceDate->copy()->addDays($daysAdd); // Next service after 120 days
                        $nextServiceKm = $lastServiceKm + $additionKm; // Add KM interval

                        $serviceDates[] = $nextServiceDate;
                        $serviceKm[] = $nextServiceKm;

                        $lastServiceDate = $nextServiceDate;
                        $lastServiceKm = $nextServiceKm;
                    }

                    for ($i = 0; $i < count($serviceDates); $i++) {
                        $serviceType = 2;
                        $reminderDays = 0;
                        if ($amcMaster->vehicle_master_id == 4) {
                            // TVS King EV Max
                            if ($i <= 2) {
                                $serviceType = 1;
                            }
                            $reminderDays = $this->reminderDays['4'][$i];
                        } elseif ($amcMaster->vehicle_master_id == 6) {
                            // TVS King Deluxe
                            if ($i <= 2) {
                                $serviceType = 1;
                            }
                            $reminderDays = $this->reminderDays['6'][$i];
                        }
                        $serviceData = [
                            'service_no' => $i + 1,
                            'service_km' => $serviceKm[$i],
                            'service_type' => $serviceType,
                            'reminder_days' => $reminderDays,
                            'amc_id' => $amcMasterId,
                            'service_date' => $serviceDates[$i]->format('Y-m-d H:i:s'),
                            'created_by' => Auth::id(),
                        ];

                        ServiceDetail::create($serviceData);
                    }
                }
            }
            $oldAmcData = AmcMaster::find($request->amc_reference_id)->first();
            AmcMaster::where('id', $request->amc_reference_id)->update(['renew_status' => $this->getArrayIdByName($this->statusArray, 'Renew'), 'status' => $this->getArrayIdByName($this->statusArray, 'Deactive')]);


            SystemLogs::create([
                'inquiry_id' => 0,
                'type' => '5', // AMC master Module ID
                'type_id' => $request->amc_reference_id,
                'remark'     => 'Renew AMC # ' . $oldAmcData->amc_display_number,
                'action_id'  => $this->getArrayIdByName($this->actionLogsArray, 'Renew'),
                'created_by' => Auth::id(),
            ]);
            SystemLogs::create([
                'inquiry_id' => 0,
                'type' => '5', // AMC master Module ID
                'type_id' => $amcMasterId,
                'remark'     => 'Add New AMC # ' . $amcMaster->amc_display_number,
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
            $exportVehicleMasterId = $request->input('exportVehicleMasterId');
            $exportAmcStatusId = $request->input('exportAmcStatusId');

            return Excel::download(new AmcExport($exportStartDate, $exportEndDate, $exportChassisNumber, $exportVehicleNumber, $exportContactNumber, $exportVehicleType, $exportVehicleMasterId, $exportAmcStatusId), 'amc.xlsx');
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
            $referenceId = $amcData->amc_reference_id;
            $renewStatus = $amcData->renew_status;
            $serviceQuery = ServiceDetail::where('amc_id', $amcId)->get();

            $fieldId = 'amc_reference_id';
            $fieldIdValue = $amcId;
            if ($renewStatus === 12) {
                $fieldId = 'id';
                $fieldIdValue = $referenceId;
            }

            $amcDetailsQuery = AmcMaster::where($fieldId, $fieldIdValue)->get();

            $historyData = SystemLogs::where('type', '5')->where('type_id', $fieldIdValue)->get();
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

    public function checkChassisNumber(Request $request)
    {
        $chassisNumber = $request->chassis_number;

        $amcMaster = AmcMaster::where('chassis_number', $chassisNumber)->where('status', $this->getArrayIdByName($this->statusArray, 'Active'))->first();
        if ($amcMaster) {
            return response()->json(['code' => 1, 'message' => 'Chassis number already exists']);
        } else {
            return response()->json(['code' => 0, 'message' => 'Chassis number is available']);
        }
    }

    public function dueList(Request $request)
    {

        if ($request->ajax()) {
            $amcMasterList = AmcMaster::query()->where('status', $this->getArrayIdByName($this->statusArray, 'Deactive'))->Where('renew_status', $this->getArrayIdByName($this->statusArray, 'New'))->orderBy('amc_end_date');

            // if ($request->action_type != 'report') {
            //     //default list
            //     $amcMasterList = $amcMasterList->where('renew_status', $this->getArrayIdByName($this->statusArray, 'New'));
            // }
            if (checkRights('USER_AMC_ROLE_VIEW') && !checkRights('USER_AMC_ROLE_VIEW_ALL')) {
                $amcMasterList = $amcMasterList->where('created_by', Auth::id());
            }
            // if (! empty($request->startDate) && ! empty($request->endDate)) {
            //     $amcMasterList = $amcMasterList->whereBetween(DB::raw('DATE(amc_end_date)'), [$request->startDate, $request->endDate]);
            // }
            // if (!empty($request->chassis_number)) {
            //     $amcMasterList = $amcMasterList->where('chassis_number', $request->chassis_number);
            // }
            // if (!empty($request->vehicle_number)) {
            //     $amcMasterList = $amcMasterList->where('vehicle_number', $request->vehicle_number);
            // }
            // if (!empty($request->contact_number)) {
            //     $amcMasterList = $amcMasterList->where('contact_number', $request->contact_number);
            // }
            // if (!empty($request->vehicle_type)) {
            //     $amcMasterList = $amcMasterList->where('vehicle_type', $request->vehicle_type);
            // }
            // if (!empty($request->vehicle_master_id)) {
            //     $amcMasterList = $amcMasterList->where('vehicle_master_id', $request->vehicle_master_id);
            // }
            // if (!empty($request->amc_status_id)) {
            //     $amcMasterList = $amcMasterList->where('status', $request->amc_status_id);
            // }

            return DataTables::of($amcMasterList)
                ->addIndexColumn()

                ->addColumn('customer_details', function ($row) {
                    return $row->customer_name . "<br>" . $row->contact_number;
                })
                ->addColumn('contact_date', function ($row) {
                    return $this->formatDateTime('d-m-Y', $row->amc_start_date) . "<br>" . $this->formatDateTime('d-m-Y', $row->amc_end_date);
                })
                ->addColumn('row_class', function ($row) {
                    return 'light-red';
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
                    $serviceData = ServiceDetail::query()->where('status', 1)->where('inquiry_flag', 2)->where('inquiry_id', '!=', 0)->where('amc_id', $row->id)->orderBy('service_date', 'ASC')->first();
                    $htmlInfo = '';
                    if ($serviceData) {
                        if (checkRights('USER_INQUIRY_ROLE_VIEW') || checkRights('USER_INQUIRY_ROLE_VIEW_ALL')) {
                            $htmlInfo = '<a href="' . route('inquiry') . '" class=""><i class="bi bi-info-circle-fill"></i></a>';
                        } else {
                            $htmlInfo = '<a href="#" class="btn btn-' . $class . ' btn-sm " data-id="' . $row->id . '" data-status="' . $row->status_id . '">' . $this->getArrayNameById($this->statusArray, $row->status) . '</a>';
                        }
                    }

                    $html = '<a href="" class="btn btn-' . $class . ' btn-sm " data-id="' . $row->id . '" data-status="' . $row->status_id . '">' . $this->getArrayNameById($this->statusArray, $row->status) . '</a> <br> ' . $htmlInfo;
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
                        if ($row->status == $this->getArrayIdByName($this->statusArray, 'Deactive') && $row->renew_status == $this->getArrayIdByName($this->statusArray, 'New')) {
                            $html .= '<a href="' . route('amc-master.renew', $row->id) . '" class="dropdown-item">Renew</a>';
                        }
                    }
                    $html .= '<a href="javascript:void(0);" class="dropdown-item amc-view" data-id="' . $row->id . '">View</a>';
                    $html .= '<a href="' . route('amc.download', $row->id) . '" class="dropdown-item" target="_blank">PDF</a>';

                    $html .= '<a href="#" class="dropdown-item amc-add-inquiry" data-id="' . $row->id . '" data-vehicle-number="' . $row->vehicle_number . '" data-customer-name="' . $row->customer_name . '" data-customer-number="' . $row->contact_number . '">Add Inquiry</a>';
                    if (Carbon::parse($row->amc_end_date)->isFuture()) {
                        $html .= '<a href="javascript:void(0);" class="dropdown-item change-status" data-id="' . $row->id . '" data-status="' . $row->status . '">Status</a>';
                    }
                    $html .= '</div>';
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['action', 'customer_details', 'contact_date', 'vehicle_data', 'display_status', 'vehicle_model', 'row_class'])
                ->make(true);
        }
        $vehicle = Vehicle::pluck('name', 'id');
        $vehicleTypeArray = $this->vehicleTypeArray;
        $serviceTypeArray = $this->serviceTypeArray;
        $branch = $this->branchArray;
        $serviceStatus = [
            "10" => 'Active',
            "11" => 'Deactive',
        ];
        return view('amc-due-list')->with(compact('vehicle', 'vehicleTypeArray', 'branch', 'serviceTypeArray', 'serviceStatus'));
    }
}
