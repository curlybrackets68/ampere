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
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group"> <button type="button" class="btn btn-light dropdown-toggle"
                      style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                        data-bs-toggle="dropdown" aria-expanded="false"> Action </button>
                        <ul class="dropdown-menu" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                            <li> <a class="dropdown-item change-status" data-id="' . $row->id . '" data-status="' . $row->status_id . '" href="javascript:void(0);">Status</a></li>
                        </ul>
                    </div>';
                })
                ->addColumn('display_order_date', function ($row) {
                    return $this->formatDateTime('d M, Y h:i A', $row->created_at);
                })
                ->rawColumns(['action', 'display_order_date'])
                ->make(true);
        }
        return view('orders-list');
    }

    public function changeStatus(Request $request)
    {
        $statusId = $request->statusId;
        $remark   = $request->statusRemark;

        $save = Order::where('id', $request->id)->update(['status_id' => $statusId, 'status_remark' => $remark ?? '']);

        // dd($save);
        // if ($request->statusId == '4') {
        //     Order::where('id', $request->id)->update(['confirm_date' => $this->formatDateTime(mDateTime: $request->confirmDate)]);
        // }

        if ($save) {
            $orderDetails = Order::find($request->id);
            $message        = '';
            if ($request->statusId == '6') { // Ordered
                $message = 'Your order Ordered for #' . $orderDetails->order_no . "\n";
                $message .= "Name: " . $orderDetails->customer_name . "\n";
                $message .= "Mobile: " . $orderDetails->customer_mobile . "\n";
                $message .= "Vehicle No: " . $orderDetails->customer_vehicle_no . "\n";
                $message .= "Order: " . $orderDetails->order_name . "\n";
                $message .= "Remark: " . $remark . "\n";
            } else if ($request->statusId == '7') { // Confirmed
                $message = 'Your booking Recieved as #' . $orderDetails->order_no . "\n";
                $message .= "Date: " . $this->formatDateTime('d M, Y h:i A', $orderDetails->confirm_date) . "\n";
                $message .= "Name: " . $orderDetails->customer_name . "\n";
                $message .= "Mobile: " . $orderDetails->customer_mobile . "\n";
                $message .= "Vehicle No: " . $orderDetails->customer_vehicle_no . "\n";
                $message .= "Order: " . $orderDetails->order_name . "\n";
            }

            $this->sendWhatsAppMessage($orderDetails->customer_mobile, $message);
            SystemLogs::create([
                'inquiry_id' => $request->id,
                'remark'     => 'Status changed to ' . $this->getArrayNameById($this->statusArray, $request->statusId),
                'action_id'  => 3,
                'created_by' => auth()->id(),
            ]);
            return response()->json(['code' => 1, 'message' => 'Status updated successfully']);
        } else {
            return response()->json(['code' => 0, 'message' => 'Failed to update status']);
        }
    }
}
