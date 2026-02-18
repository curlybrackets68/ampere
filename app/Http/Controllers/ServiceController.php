<?php

namespace App\Http\Controllers;

use App\Exports\ServiceDetailsExport;
use App\Models\AmcMaster;
use App\Models\AmcPackageMaster;
use App\Models\InquiryDetails;
use App\Models\ServiceDetail;
use App\Models\SystemLogs;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $serviceList = ServiceDetail::query();
            if (isset($request->action_type) && !empty($request->action_type)) {
                $amcIds = AmcMaster::query()
                    ->when(!empty($request->chassis_number), fn($q) => $q->where('chassis_number', $request->chassis_number))
                    ->when(!empty($request->vehicle_number), fn($q) => $q->where('vehicle_number', $request->vehicle_number))
                    ->when(!empty($request->contact_number), fn($q) => $q->where('contact_number', $request->contact_number))
                    ->when(!empty($request->vehicle_type), fn($q) => $q->where('vehicle_type', $request->vehicle_type))
                    ->when(!empty($request->vehicle_master_id), fn($q) => $q->where('vehicle_master_id', $request->vehicle_master_id))
                    ->pluck('id')
                    ->toArray();
                if (!empty($amcIds)) {
                    $serviceList = $serviceList->whereIn('amc_id', $amcIds);
                }
                if (!empty($request->status_id)) {
                    $serviceList = $serviceList->where('status', $request->status_id);
                }
                if (! empty($request->startDate) && ! empty($request->endDate)) {
                    $serviceList = $serviceList->whereBetween(DB::raw('DATE(service_details.service_date)'), [$request->startDate, $request->endDate]);
                }
            } else {
                $serviceList = $serviceList->where('status', 1);
                if (! empty($request->startDate) && ! empty($request->endDate)) {
                    $serviceList = $serviceList->whereBetween(DB::raw('DATE(service_details.service_date)'), [$request->startDate, $request->endDate]);
                }
                $serviceList = $serviceList->orwhere(DB::raw('DATE(service_details.service_date)'), '<', $request->startDate);
            }

            // dd($serviceList->toRawSql());

            return DataTables::of($serviceList)
                ->addIndexColumn()

                ->addColumn('contract_details', function ($row) {
                    return $row->amc_master_details->amc_display_number . "<br>" . $this->formatDateTime('d-m-Y', $row->amc_start_date) . "<br>" . $this->formatDateTime('d-m-Y', $row->amc_end_date);
                })
                ->addColumn('customer_details', function ($row) {
                    return $row->amc_master_details->customer_name . "<br>" . $row->amc_master_details->contact_number;
                })
                ->addColumn('service_date', function ($row) {
                    return $this->formatDateTime('d-m-Y', $row->service_date);
                })
                ->addColumn('service_details', function ($row) {
                    return $row->service_no . "<br>" . $this->formatDateTime('d-m-Y', $row->service_date);
                })
                ->addColumn('service_by', function ($row) {
                    return $row->service_by;
                })
                ->addColumn('vehicle_details', function ($row) {
                    $amc = optional($row->amc_master_details);

                    $vehicleType = $this->getArrayNameById($this->vehicleTypeArray, $amc->vehicle_type ?? 1);
                    return $vehicleType . '<br>' . ($amc->vehicle_name ?? '') . '<br>' . ($amc->vehicle_number ?? '');
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
                    if ($row->inquiry_flag == 1) {
                        $html .= '<a href="javascript:void(0);" class="dropdown-item amc-add-inquiry" data-service-id="' . $row->id . '" data-id="' . $row->amc_master_details->id . '" data-vehicle-number="' . $row->amc_master_details->vehicle_number . '" data-customer-name="' . $row->amc_master_details->customer_name . '" data-customer-number="' . $row->amc_master_details->contact_number . '">Add Inquiry</a>';
                    }
                    $html .= '</div>';
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['action', 'contract_details', 'customer_details', 'service_date', 'service_details', 'vehicle_details', 'display_status', 'vehicle_model'])
                ->make(true);
        }
        $vehicle = Vehicle::pluck('name', 'id');
        $vehicleTypeArray = $this->vehicleTypeArray;
        $serviceTypeArray = $this->serviceTypeArray;
        $branch = $this->branchArray;
        $serviceStatus = [
            "1" => 'Pending',
            "2" => 'Completed',
        ];

        return view('service-list')->with(compact('vehicle', 'vehicleTypeArray', 'serviceStatus', 'serviceTypeArray', 'branch'));
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
            $previousService = ServiceDetail::where('amc_id', $amcMaster->id)->where('status', '2')->first();
            $serviceFlag = false;
            if ($serveiceData) {
                $serviceFlag = true;
            }
            $data['data'] = array('serveiceData' => $serveiceData, 'serveiceDataList' => $serveiceDataList, 'serviceFlag' => $serviceFlag, 'previousService' => $previousService);
            return $this->successResponse($data);
        } else {
            return  $this->failResponse([], 'Chassis Number Not Found');
        }
    }


    public function addServiceHandel(Request $request)
    {
        $fileData = request()->filename;
        $amc_id = $request->amc_id;
        $service_id = $request->service_id;
        $service_remark = $request->service_remark;
        $service_km = $request->service_km;
        $status_id = $request->status_id;

        $amcMaster = AmcMaster::find($amc_id);
        //  dd($amcMaster)->vehicle_master_id;

        // Prepare update data for completed service
        // IMPORTANT: Only update status, remark, KM, and attachment. DO NOT update service_type or service_no
        $updateData['service_remark'] = $service_remark;
        $updateData['status'] = $status_id;
        $updateData['service_km'] = $service_km;
        $updateData['modified_by'] = Auth::id();
        // NOTE: service_type and service_no are NOT included - they remain unchanged

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
        $isTVSVehicle = false;
        if (in_array($amcMaster->vehicle_master_id, [4, 5, 6])) {
            $isTVSVehicle = true;
        }
        $serviceData = ServiceDetail::query()->where('id', $service_id)->first();
        $serviceKmDiffernace =  $service_km - $serviceData->service_km;

        if (!Carbon::parse($serviceData->service_date)->isToday()) {
            $pendingServices = ServiceDetail::where('amc_id', $amc_id)
                ->where('status', '1')
                ->where('id', '!=', $service_id) // Exclude the service being updated
                ->orderBy('service_no', 'ASC')
                ->get();
            if ($isTVSVehicle) {
                if ($pendingServices->count() > 0) {
                    // Get completed service to know the service number
                    $completedService = ServiceDetail::where('id', $service_id)->first();
                    $completedServiceNo = $completedService->service_no ?? 0;
                    $completedServiceDate = Carbon::today();
                    $completedServiceKm = $service_km;

                    $serviceDates = [];
                    $serviceKm = [];
                    $lastServiceDate = $completedServiceDate;
                    $lastServiceKm = $completedServiceKm;

                    // Calculate dates and KM for each pending service
                    // In AmcMasterController, loop index $i corresponds to service_no - 1 (0-based index)
                    // So for service_no n, we use $i = n - 1
                    // Store calculations by service_no to ensure correct matching
                    $calculatedData = [];
                    foreach ($pendingServices as $index => $service) {
                        // $i is the loop index used in AmcMasterController (0-based, service_no - 1)
                        // Use the actual service_no of each pending service to get the correct index
                        $i = $service->service_no - 1;
                        $daysAdd = 120;
                        $additionKm = 4000;

                        if ($amcMaster->vehicle_master_id == 4) {
                            // TVS King EV Max
                            $additionKm = 10000;
                            if ($i <= 2) {
                                $daysAdd = 40;
                            } else if ($i >= 3 && $i <= 5) {
                                $daysAdd = 90;
                            } else if ($i >= 6 && $i <= 16) {
                                $daysAdd = 120;
                            }
                        } else if ($amcMaster->vehicle_master_id == 6) {
                            // TVS King Deluxe
                            if ($i == 1) {
                                $daysAdd = 25;
                            } else {
                                $daysAdd = 45;
                            }
                            // KM remains same as default (4000) for King Deluxe
                        } else if ($amcMaster->vehicle_master_id == 5) {
                            // TVS King Duramax Plus
                            $additionKm = 10000;
                            if ($i == 1) {
                                $daysAdd = 35;
                            } else if ($i == 7) {
                                $daysAdd = 70;
                            } else {
                                $daysAdd = 65;
                            }
                        }

                        $nextServiceDate = $lastServiceDate->copy()->addDays($daysAdd);
                        $nextServiceKm = $lastServiceKm + $additionKm;

                        // Store by service_no to ensure correct matching
                        $calculatedData[$service->service_no] = [
                            'date' => $nextServiceDate,
                            'km' => $nextServiceKm
                        ];

                        $lastServiceDate = $nextServiceDate;
                        $lastServiceKm = $nextServiceKm;
                    }

                    // Get AMC contract end date as Carbon instance
                    $amcEndDate = Carbon::parse($amcMaster->amc_end_date ?? null);
                    
                    // Update pending services with calculated dates and KM, matching by service_no
                    // IMPORTANT: Only update service_date and service_km. DO NOT update service_type or service_no
                    foreach ($pendingServices as $service) {
                        if (isset($calculatedData[$service->service_no])) {
                            $calculated = $calculatedData[$service->service_no];
                            
                            // Check if this is the last service and if date is within AMC end date
                            $isLastService = $service->service_no == $pendingServices->max('service_no');
                            
                            // Update all services, but check AMC end date for last service
                            // Only update service_date and service_km - preserve service_type and service_no
                            if (!$isLastService || ($isLastService && $calculated['date']->lte($amcEndDate))) {
                                ServiceDetail::where('id', $service->id)->update([
                                    'service_date' => $calculated['date']->toDateString(),
                                    'service_km' => $calculated['km']
                                    // NOTE: service_type and service_no are NOT updated - they remain as set at AMC creation
                                ]);
                            }
                        }
                    }
                }
            } else {
                if ($pendingServices->count() > 1) {
                    $firstServiceDate = Carbon::today();
                    $serviceDates = [];
                    $lastServiceDate = $firstServiceDate;

                    // Generate all service dates
                    for ($i = 0; $i < $pendingServices->count(); $i++) {
                        if ($i == 0) {
                            $nextDate = $firstServiceDate;
                        } elseif ($i == 1) {
                            $nextDate = $lastServiceDate->copy()->addDays(120);
                        } else {
                            $nextDate = $lastServiceDate->copy()->addDays(119);
                        }
                        $serviceDates[] = $nextDate;
                        $lastServiceDate = $nextDate;
                    }

                    // Get AMC contract end date as Carbon instance
                    $amcEndDate = Carbon::parse($amcMaster->amc_end_date ?? null);

                    foreach ($pendingServices as $index => $service) {
                        if ($index < $pendingServices->count() - 1) {
                            // Update all except the last
                            $service->service_date = $serviceDates[$index]->toDateString();
                            $service->save();
                        } elseif ($index == $pendingServices->count() - 1) {
                            // Only update last if service date is within AMC end date
                            if ($serviceDates[$index]->lte($amcEndDate)) {
                                $service->service_date = $serviceDates[$index]->toDateString();
                                $service->save();
                            }
                        }
                    }
                }
            }
        }
        if ($serviceKmDiffernace != 0) {
            $updateData['service_km'] = $serviceData->service_km + $serviceKmDiffernace;
        }
        // Save the selected service
        ServiceDetail::where('id', $service_id)->update($updateData);

        // Calculate and update dates and KM for TVS vehicles (when service date IS today)
        if ($isTVSVehicle && Carbon::parse($serviceData->service_date)->isToday()) {
            $pendingServices = ServiceDetail::where('amc_id', $amc_id)
                ->where('status', '1')
                ->where('id', '!=', $service_id) // Exclude the service being updated
                ->orderBy('service_no', 'ASC')
                ->get();

            if ($pendingServices->count() > 0) {
                // Get completed service to know the service number
                $completedService = ServiceDetail::where('id', $service_id)->first();
                $completedServiceNo = $completedService->service_no ?? 0;
                $completedServiceDate = Carbon::today();
                $completedServiceKm = $service_km;

                $lastServiceDate = $completedServiceDate;
                $lastServiceKm = $completedServiceKm;

                // Calculate dates and KM for each pending service
                $calculatedData = [];
                foreach ($pendingServices as $index => $service) {
                    // $i is the loop index used in AmcMasterController (0-based, service_no - 1)
                    $i = $service->service_no - 1;
                    $daysAdd = 120;
                    $additionKm = 4000;

                    if ($amcMaster->vehicle_master_id == 4) {
                        // TVS King EV Max
                        $additionKm = 10000;
                        if ($i <= 2) {
                            $daysAdd = 40;
                        } else if ($i >= 3 && $i <= 5) {
                            $daysAdd = 90;
                        } else if ($i >= 6 && $i <= 16) {
                            $daysAdd = 120;
                        }
                    } else if ($amcMaster->vehicle_master_id == 6) {
                        // TVS King Deluxe
                        if ($i == 1) {
                            $daysAdd = 25;
                        } else {
                            $daysAdd = 45;
                        }
                        // KM remains same as default (4000) for King Deluxe
                    } else if ($amcMaster->vehicle_master_id == 5) {
                        // TVS King Duramax Plus
                        $additionKm = 10000;
                        if ($i == 1) {
                            $daysAdd = 35;
                        } else if ($i == 7) {
                            $daysAdd = 70;
                        } else {
                            $daysAdd = 65;
                        }
                    }

                    $nextServiceDate = $lastServiceDate->copy()->addDays($daysAdd);
                    $nextServiceKm = $lastServiceKm + $additionKm;

                    // Store by service_no to ensure correct matching
                    $calculatedData[$service->service_no] = [
                        'date' => $nextServiceDate,
                        'km' => $nextServiceKm
                    ];

                    $lastServiceDate = $nextServiceDate;
                    $lastServiceKm = $nextServiceKm;
                }

                // Get AMC contract end date as Carbon instance
                $amcEndDate = Carbon::parse($amcMaster->amc_end_date ?? null);

                // Update pending services with calculated dates and KM
                // IMPORTANT: Only update service_date and service_km. DO NOT update service_type or service_no
                foreach ($pendingServices as $service) {
                    if (isset($calculatedData[$service->service_no])) {
                        $calculated = $calculatedData[$service->service_no];
                        
                        // Check if this is the last service and if date is within AMC end date
                        $isLastService = $service->service_no == $pendingServices->max('service_no');
                        
                        // Update all services, but check AMC end date for last service
                        // Only update service_date and service_km - preserve service_type and service_no
                        if (!$isLastService || ($isLastService && $calculated['date']->lte($amcEndDate))) {
                            ServiceDetail::where('id', $service->id)->update([
                                'service_date' => $calculated['date']->toDateString(),
                                'service_km' => $calculated['km']
                                // NOTE: service_type and service_no are NOT updated - they remain as set at AMC creation
                            ]);
                        }
                    }
                }
            }
        }

        // Update KM for pending services (only for non-TVS vehicles, TVS vehicles already updated above)
        if (!$isTVSVehicle) {
            $pendingServices = ServiceDetail::where('amc_id', $amc_id)
                ->where('status', '1')
                ->orderBy('service_no', 'ASC')
                ->get();

            if ($pendingServices) {
                foreach ($pendingServices as  $service) {
                    if ($serviceKmDiffernace != 0) {
                        $service->service_km = $service->service_km + $serviceKmDiffernace;
                        $service->save();
                    }
                }
            }
        }

        $serviceDataNewServiceData = ServiceDetail::query()
            ->Where('amc_id', $amc_id)
            ->orderBy('service_no', 'ASC')
            ->where('status', '1')
            ->first();

        $serviceData = ServiceDetail::query()->where('id', $service_id)->first();

        $nextserviceDate = $serviceDataNewServiceData->display_service_date ?? '';
        $nextServiceKm = $serviceDataNewServiceData->service_km ?? 0;
        $checkPendingServiceCount = ServiceDetail::where('amc_id', $amc_id)
            ->where('status', '1')
            ->count();

        // Log
        SystemLogs::create([
            'inquiry_id' => 0,
            'type' => '6', // Service Module ID
            'type_id' => $service_id,
            'remark'     => 'Serive Status Update ',
            'action_id'  => 1,
            'created_by' => Auth::id(),
        ]);

        $template = 'service_english';
        if ($amcMaster->vehicle_master_id == '4' || $amcMaster->vehicle_master_id == '5' || $amcMaster->vehicle_master_id == '6') {
            $template = 'service_gujarati';
        }

        $metaData = [
            $amcMaster->customer_name ?? 'N/A',
            $amcMaster->vehicle_number,
            $amcMaster->amc_display_number ?? 'N/A',
            $serviceData->display_service_date ?? 'N/A',
        ];

        if (!empty($checkPendingServiceCount)) {
            $template = 'next_service_english';
            if ($amcMaster->vehicle_master_id == '4' || $amcMaster->vehicle_master_id == '5' || $amcMaster->vehicle_master_id == '6') {
                $template = 'next_service_gujarati';
            }
            $metaData[] = $nextserviceDate ?? 'N/A';
            $metaData[] = $checkPendingServiceCount ?? 'N/A';
            $metaData[] = $nextServiceKm ?? 'N/A';
        }

        $pdfUrl = $this->generateAndStorePdf('pdf.amc-pdf', ['amc' => $amcMaster], 'amc_pdfs');
        // $data = $this->sendWhatsAppMessageWithFile($amcMaster->contact_number, $whatsAppMsg, $pdfUrl['public_url'], 'amc_pdf');

        // Meta Send
        $this->sendMetaWhatsappMessage($amcMaster->contact_number, $template, $metaData, $pdfUrl['public_url'], 'AMC_FILE');

        // Check if all services are completed
        $checkPendingServiceCount = ServiceDetail::where('amc_id', $amc_id)
            ->where('status', '1')
            ->count();

        if ($checkPendingServiceCount == 0) {
            $amcMaster->update([
                'status' => $this->getArrayIdByName($this->statusArray, 'Deactive')
            ]);

            // Meta Send
            $this->sendMetaWhatsappMessage($amcMaster->contact_number, 'service_completed');
        }

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
            $exportStatusId = $request->input('exportStatusId');
            $exportActionType = $request->input('exportActionType');

            return Excel::download(new ServiceDetailsExport($exportStartDate, $exportEndDate, $exportChassisNumber, $exportVehicleNumber, $exportContactNumber, $exportVehicleType, $exportvehicleMasterId, $exportStatusId, $exportActionType), 'service.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function addServiceInquiry(Request $request)
    {
        $data = [];


        $data['name'] = $request->name;
        $data['mobile'] = $request->mobile;
        $data['vehicle_no'] = strtoupper($request->vehicle_no);
        $data['created_by'] = Auth::id();
        $data['branch_id'] = $request->branch_id;
        $data['service_type_id'] = $request->service_type_id;


        $lastInquiryId = InquiryDetails::orderBy('id', 'desc')->first()->id ?? 0;
        $data['inquiry_no'] = 'INQ-' . ($lastInquiryId + 1);


        $inquirySave = InquiryDetails::create($data);
        $latestNumber = $inquirySave->inquiry_no;
        SystemLogs::create([
            'inquiry_id' => $inquirySave->id,
            'type' => '1', //
            'type_id' => $inquirySave->id,
            'remark'     => 'Inquiry Created # ' . $latestNumber,
            'action_id'  => 1,
            'created_by' => 1,
        ]);



        $this->sendWhatsAppMessage($request->mobile, "Inquiry No # $latestNumber added successfully. We will contact you soon.");

        if (isset($request->inquiry_service_id) && !empty($request->inquiry_service_id)) {
            $serviceData = ServiceDetail::query()->where('id', $request->inquiry_service_id)->first();
            $serviceData->update(['inquiry_id' => $inquirySave->id, 'inquiry_flag' => 2]);
        } else {
            $serviceData = ServiceDetail::query()->where('status', 1)->where('amc_id', $request->amc_id)->orderBy('service_date', 'ASC')->first();
            $serviceData->update(['inquiry_id' => $inquirySave->id, 'inquiry_flag' => 2]);
        }

        return $this->successResponse([], "Inquiry No # $latestNumber added successfully. We will contact you soon.");
    }
}
