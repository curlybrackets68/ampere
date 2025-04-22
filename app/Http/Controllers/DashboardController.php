<?php

namespace App\Http\Controllers;

use App\Exports\InquiryDetailsExport;
use App\Models\InquiryDetails;
use App\Models\Lead;
use App\Models\Order;
use App\Models\SystemLogs;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class DashboardController
{
    use CommonFunctions;

    public function dashboard()
    {
        $inquiryCounts = InquiryDetails::select('status_id', DB::raw('count(*) as count'))
            ->whereIn('status_id', [1, 2, 3, 4, 5])
            ->groupBy('status_id')
            ->pluck('count', 'status_id')
            ->toArray();

        $pendingInquiry = $inquiryCounts[1] ?? 0;
        $completeInquiry = $inquiryCounts[2] ?? 0;
        $rejectedInquiry = $inquiryCounts[3] ?? 0;
        $confirmInquiry = $inquiryCounts[4] ?? 0;
        $workshopInquiry = $inquiryCounts[5] ?? 0;

        $orders = Order::select('status_id', DB::raw('count(*) as count'))
            ->whereIn('status_id', [1, 6, 7, 8, 9])
            ->groupBy('status_id')
            ->pluck('count', 'status_id')
            ->toArray();

        $totalOrdersPending = $orders[1] ?? 0;
        $totalOrdersOrdered = $orders[6] ?? 0;
        $totalOrdersReceived = $orders[7] ?? 0;
        $totalOrdersCancelled = $orders[8] ?? 0;
        $totalOrdersFitment = $orders[9] ?? 0;

        $serviceType = $this->serviceTypeArray;
        $inquiryStatusArrayData = [
            "1" => 'Pending',
            "2" => 'Completed',
            "3" => 'Rejected',
            "4" => 'Confirmed',
            "5" => 'In Workshop',

        ];
        $orderStatusArrayData = [
            "1" => 'Pending',
            "6" => 'Ordered',
            "7" => 'Recieved',
            "8" => 'Cancelled',
            "9" => 'Fitment',
        ];
        $salesman = User::pluck('user_name', 'id');


        return view('dashboard')->with(compact('pendingInquiry', 'completeInquiry', 'rejectedInquiry', 'confirmInquiry', 'workshopInquiry', 'totalOrdersPending', 'totalOrdersOrdered', 'totalOrdersReceived', 'totalOrdersCancelled', 'totalOrdersFitment', 'serviceType', 'inquiryStatusArrayData','orderStatusArrayData','salesman'));
    }

    public function inquiryDetails(Request $request)
    {
        $branches = $this->branchArray;
        if ($request->ajax()) {
            $inquiry = InquiryDetails::query();
            if (isset($request->actionType) && $request->actionType == 'report') {
                if (!empty($request->startDate) && !empty($request->endDate)) {
                    $inquiry = $inquiry->whereBetween(DB::raw('DATE(created_at)'), [$request->startDate, $request->endDate]);
                }

                if (!empty($request->statusId)) {
                    $inquiry = $inquiry->where('status_id', $request->statusId);
                }

                if (!empty($request->searchBranchId)) {
                    $inquiry = $inquiry->where('branch_id', $request->searchBranchId);
                }
            } else {
                if (!empty($request->status)) {
                    $inquiry = $inquiry->where('status_id', $request->status);
                } else {
                    $inquiry = $inquiry->where('status_id', '1');
                }
            }


            //return dd($inquiry->toRawSql());
            return DataTables::of($inquiry)
                ->addIndexColumn()
                ->addColumn('display_status', function ($row) {
                    $class = 'warning';
                    if ($row->status_id == '2') {
                        $class = 'success';
                    } else if ($row->status_id == '3') {
                        $class = 'danger';
                    } else if ($row->status_id == '4') {
                        $class = 'info';
                    } else if ($row->status_id == '5') {
                        $class = 'primary';
                    }
                    $checkEditRights = '';
                    if(checkRights('USER_INQUIRY_ROLE_EDIT')){
                        $checkEditRights = ' change-status ';
                    }
                    // $html = '<span class="badge text-bg-' . $class . '">' . $this->getArrayNameById($this->statusArray, $row->status_id) . '</span>';
                    if ($row->status_id == '1' || $row->status_id == '4' || $row->status_id == '5') {
                        $html = '<button type="button" class="btn btn-' . $class . $checkEditRights . ' btn-sm " data-id="' . $row->id . '" data-status="' . $row->status_id . '">' . $this->getArrayNameById($this->statusArray, $row->status_id) . '</button>';
                    } else {
                        $html = '<button type="button" class="btn btn-' . $class . ' btn-sm">' . $this->getArrayNameById($this->statusArray, $row->status_id) . '</button>';
                    }
                    if ($row->status_id == '4') {
                        $html .= '<br>
                        <a class="link-primary '.$checkEditRights.'" data-id="' . $row->id . '" style="cursor: pointer;">' . $this->formatDateTime('d M, Y h:i A', $row->confirm_date) . '</a>';
                    }
                    return $html;
                })
                ->addColumn('display_inquiry_date', function ($row) {
                    return $this->formatDateTime('d M, Y h:i A', $row->created_at);
                })
                ->addColumn('branch_name', function ($row) {
                    return $this->getArrayNameById($this->branchArray, $row->branch_id);
                })
                ->addColumn('service_type', function ($row) {
                    return $this->getArrayNameById($this->serviceTypeArray, $row->service_type_id);
                })
                ->addColumn('action', function ($row) {
                    if ($row->status_id == '1' || $row->status_id == '4' || $row->status_id == '5') {
                        return '<div class="btn-group"> <button type="button" class="btn btn-light dropdown-toggle"
                                    style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                    data-bs-toggle="dropdown" aria-expanded="false"> Action </button>
                                <ul class="dropdown-menu" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                    <li> <a class="dropdown-item change-status" data-status= data-id="' . $row->id . '" data-status="' . $row->status_id . '" href="javascript:void(0);">Status</a></li>
                                </ul>
                            </div>';
                    } else {
                        return '';
                    }

                })
                ->rawColumns(['display_status', 'action', 'display_inquiry_date', 'branch_name', 'service_type'])
                ->make(true);
        }

        $status = $request->input('status');
        return view('inquiry', compact('status', 'branches'));
    }

    public function changeStatus(Request $request)
    {
        $statusId = $request->statusId;
        $remark = $request->statusRemark;

        $save = InquiryDetails::where('id', $request->id)->update(['status_id' => $statusId, 'status_remark' => $remark ?? '']);

        if ($request->statusId == '4') {
            InquiryDetails::where('id', $request->id)->update(['confirm_date' => $this->formatDateTime(mDateTime: $request->confirmDate)]);
        }

        if ($save) {
            $inquiryDetails = InquiryDetails::find($request->id);
            $message = '';
            if ($request->statusId == '2') { // Completed
                $message = 'Your service completed for #' . $inquiryDetails->inquiry_no . "\n";
                $message .= "Name: " . $inquiryDetails->name . "\n";
                $message .= "Mobile: " . $inquiryDetails->mobile . "\n";
                $message .= "Vehicle No: " . $inquiryDetails->vehicle_no . "\n";
                $message .= "Remark: " . $remark . "\n";
            } else if ($request->statusId == '4') { // Confirmed
                $message = 'Your booking confirmed as #' . $inquiryDetails->inquiry_no . "\n";
                $message .= "Date: " . $this->formatDateTime('d M, Y h:i A', $inquiryDetails->confirm_date) . "\n";
                $message .= "Name: " . $inquiryDetails->name . "\n";
                $message .= "Mobile: " . $inquiryDetails->mobile . "\n";
                $message .= "Vehicle No: " . $inquiryDetails->vehicle_no . "\n";
                $message .= "Service Type: " . $this->serviceTypeArray[$inquiryDetails->service_type_id] . "\n";
                $message .= "Location: " . $this->branchArray[$inquiryDetails->branch_id] . "\n";
            } else if ($request->statusId == '3') { // Rejected
                $message = 'Your service rejected for #' . $inquiryDetails->inquiry_no . "\n";
                $message .= "Name: " . $inquiryDetails->name . "\n";
                $message .= "Mobile: " . $inquiryDetails->mobile . "\n";
                $message .= "Vehicle No: " . $inquiryDetails->vehicle_no . "\n";
                $message .= "Remark: " . $remark . "\n";
                $message .= "To book appointment again, please send Hi on whatsapp";
            }

            $this->sendWhatsAppMessage($inquiryDetails->mobile, $message);
            SystemLogs::create([
                'inquiry_id' => $request->id,
                'type' => '1', // for Inq
                'type_id' => $request->id, // for Inq
                'remark' => 'Status changed to ' . $this->getArrayNameById($this->statusArray, $request->statusId),
                'action_id' => 3,
                'created_by' => auth()->id(),
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
            $exportStatusId = $request->input('exportStatusId', '');
            $exportBranchId = $request->input('exportBranchId', '');
            // dd($exportStartDate, $exportEndDate, $exportStatusId);
            return Excel::download(new InquiryDetailsExport($exportStartDate, $exportEndDate, $exportStatusId, $exportBranchId), 'inquiry.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $message = 'Please enter Hi for inquiry';
        $mobile = $request->input('mobile');
        $this->sendWhatsAppMessage($mobile, $message);
        SystemLogs::create([
            'inquiry_id' => 0,
            'remark' => 'WhatsApp message sent to ' . $mobile,
            'action_id' => 1,
            'created_by' => 1,
        ]);
        return response()->json(['code' => 1, 'message' => 'Message sent successfully']);

    }

    public function getInquiryChart(Request $request)
    {
        $serviceTypeId = $request->serviceTypeId;
        $statusId      = $request->statusId;
        $startDate     = $request->startDate;
        $endDate       = $request->endDate;

        $query = InquiryDetails::query();

        if (!empty($serviceTypeId)) {
            $query->where('service_type_id', $serviceTypeId);
        }

        if (!empty($statusId)) {
            $query->where('status_id', $statusId);
        }

        if ($startDate && $endDate) {
            $start = date('Y-m-d', strtotime(str_replace('-', '/', $startDate)));
            $end   = date('Y-m-d', strtotime(str_replace('-', '/', $endDate)));
            $query->whereBetween(DB::raw('DATE(created_at)'), [$start, $end]);
        }

        $data = $query->selectRaw('status_id, COUNT(*) as total')
            ->groupBy('status_id')
            ->get();

        $labels = [];
        $values = [];

        foreach ($data as $row) {
            $labels[] = $this->getArrayNameById($this->statusArray,$row->status_id); // Adjust accordingly
            $values[] = $row->total;
        }

        return response()->json([
            'labels' => $labels,
            'data'   => $values
        ]);
    }
    public function getOrderChart(Request $request)
    {
        $statusId      = $request->statusId;
        $startDate     = $request->startDate;
        $endDate       = $request->endDate;

        $query = Order::query();


        if (!empty($statusId)) {
            $query->where('status_id', $statusId);
        }

        if ($startDate && $endDate) {
            $start = date('Y-m-d', strtotime(str_replace('-', '/', $startDate)));
            $end   = date('Y-m-d', strtotime(str_replace('-', '/', $endDate)));
            $query->whereBetween(DB::raw('DATE(order_date)'), [$start, $end]);
        }


        $data = $query->selectRaw('status_id, COUNT(*) as total')
            ->groupBy('status_id')
            ->get();

        $labels = [];
        $values = [];

        foreach ($data as $row) {
            $labels[] = $this->getArrayNameById($this->statusArray,$row->status_id); // Adjust accordingly
            $values[] = $row->total;
        }

        return response()->json([
            'labels' => $labels,
            'data'   => $values
        ]);
    }

    public function getLeadChart(Request $request)
    {
        $salesPersonId      = $request->salesPersonId;
        $startDate     = $request->startDate;
        $endDate       = $request->endDate;

        $query = Lead::query();


        if (!empty($salesPersonId)) {
            $query->where('salesman', $salesPersonId);
        }

        if ($startDate && $endDate) {
            $start = date('Y-m-d', strtotime(str_replace('-', '/', $startDate)));
            $end   = date('Y-m-d', strtotime(str_replace('-', '/', $endDate)));

            $query->whereBetween(DB::raw('DATE(created_at)'), [$start, $end]);
        }

            $data = $query->selectRaw('salesman, COUNT(*) as total')
            ->groupBy('salesman')
            ->get();

        $labels = [];
        $values = [];

        foreach ($data as $row) {
            $salesPersonName = '';
            $nameQuery = User::find($row->salesman);
            if ($nameQuery) {
                $salesPersonName = $nameQuery->user_name;
            }
            $labels[] =$salesPersonName;
            $values[] = $row->total;
        }

        return response()->json([
            'labels' => $labels,
            'data'   => $values
        ]);
    }

}
