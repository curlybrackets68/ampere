<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SystemLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class OrdersController extends Controller
{
    //

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $order = Order::query();

            if (isset($request->actionType) && $request->actionType == 'report') {
                if (! empty($request->startDate) && ! empty($request->endDate)) {
                    $order = $order->whereBetween(DB::raw('DATE(created_at)'), [$request->startDate, $request->endDate]);
                }

                if (! empty($request->statusId)) {
                    $order = $order->where('status_id', $request->statusId);
                }
            } else {
                if (! empty($request->status)) {
                    $order = $order->where('status_id', $request->status);
                } else {
                    $order = $order->where('status_id', '1');
                }
            }
            // dd($order->toRawSql());
            return DataTables::of($order)
                ->addIndexColumn()
                ->addColumn('display_status', function ($row) {
                    $class = 'warning';
                    if ($row->status_id == '6' || $row->status_id == '7') {
                        $class = 'success';
                    } else if ($row->status_id == '8') {
                        $class = 'danger';
                    } else if ($row->status_id == '9') {
                        $class = 'info';
                    }

                    // $html = '<span class="badge text-bg-' . $class . '">' . $this->getArrayNameById($this->statusArray, $row->status_id) . '</span>';
                    if ($row->status_id == '1' || $row->status_id == '6' || $row->status_id == '7' || $row->status_id == '6') {
                        $html = '<button type="button" class="btn btn-' . $class . ' btn-sm change-status" data-id="' . $row->id . '" data-status="' . $row->status_id . '">' . $this->getArrayNameById($this->statusArray, $row->status_id) . '</button>';
                    } else {
                        $html = '<button type="button" class="btn btn-' . $class . ' btn-sm">' . $this->getArrayNameById($this->statusArray, $row->status_id) . '</button>';
                    }
                    if ($row->status_id == '9') {
                        $html .= '<br>
                        <a class="link-primary confirmDateChange" data-id="' . $row->id . '" style="cursor: pointer;">' . $this->formatDateTime('d M, Y h:i A', $row->confirm_date) . '</a>';
                    }
                    return $html;
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-info btn-sm open-history-modal" data-type="order" data-type-id="' . $row->id . '">Hisotry</button>';
                })
                ->addColumn('branch_name', function ($row) {
                    return $this->getArrayNameById($this->branchArray, $row->branch_id);
                })
                ->addColumn('display_order_date', function ($row) {
                    return $this->formatDateTime('d M, Y h:i A', $row->created_at);
                })
                ->rawColumns(['action', 'display_order_date', 'display_status', 'branch_name'])
                ->make(true);
        }
        $status = $request->input('status');
        return view('orders-list', compact('status'));
    }

    public function changeStatus(Request $request)
    {
        $statusId = $request->statusId;
        $remark   = $request->statusRemark;

        $save = Order::where('id', $request->id)->update(['status_id' => $statusId, 'status_remark' => $remark ?? '']);
        if ($request->statusId == '9') {
            Order::where('id', $request->id)->update(['confirm_date' => $this->formatDateTime(mDateTime: $request->confirmDate)]);
        }

        if ($save) {
            $orderDetails = Order::find($request->id);
            $message        = '';
            //  dd($request->statusId);
            if ($request->statusId == '6') { // Ordered
                $message = 'Your Ordered #' . $orderDetails->order_no . " successful.\n";
                $message .= "We will contact you once the part has arrived \n";

                $message .= "Remark: " . $remark . "\n";
            } else if ($request->statusId == '7') { // received
                $message = "Your part has been received. Our executive will reach out to you for the fitment of the same \n";
                $message .= "Remark: " . $remark . "\n";
            } else if ($request->statusId == '8') { // Cancelled
                $message = 'Your Ordered #' . $orderDetails->order_no . " is Cancelled.\n";
                $message .= "Remark: " . $remark . "\n";
            } else if ($request->statusId == '9') { // Fitment
                $message = 'Your Ordered #' . $orderDetails->order_no . " is Fitment.\n";
                $message .= "Name: " . $orderDetails->customer_name . "\n";
                $message .= "Mobile: " . $orderDetails->customer_mobile . "\n";
                $message .= "Vehicle No: " . $orderDetails->customer_vehicle_no . "\n";
                $message .= "Part Details: " . $orderDetails->order_name . "\n";
                $message .= "Remark: " . $remark . "\n";
            }

            $this->sendWhatsAppMessage($orderDetails->customer_mobile, $message);
            SystemLogs::create([
                'inquiry_id' => 0,
                'type' => '2', // for Order
                'type_id' => $request->id, // for Order
                'remark'     => 'Status changed to ' . $this->getArrayNameById($this->statusArray, $request->statusId) ." Status Remark: - ".$remark,
                'action_id'  => 3,
                'created_by' => auth()->id(),
            ]);
            return response()->json(['code' => 1, 'message' => 'Status updated successfully']);
        } else {
            return response()->json(['code' => 0, 'message' => 'Failed to update status']);
        }
    }

    public function getHistory(Request $request)
    {
        if ($request->ajax()) {

            $order = SystemLogs::query()->where('type', 2)->where('type_id', $request->type_id)->get();

            return DataTables::of($order)
                ->addIndexColumn()
                ->addColumn('display_action', function ($row) {
                    return $this->getArrayNameById($this->actionLogsArray,$row->action_id);
                })->addColumn('display_date', function ($row) {
                    return $this->formatDateTime('d M, Y h:i A', $row->created_at);
                })
                ->make(true);
        }
    }
}
