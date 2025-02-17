<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrdersController extends Controller
{
    //

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $inquiry = Order::query();

            return DataTables::of($inquiry)
                ->addIndexColumn()
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
                ->addColumn('display_order_date', function ($row) {
                    return $this->formatDateTime('d M, Y h:i A', $row->created_at);
                })
                ->rawColumns(['action','display_order_date'])
                ->make(true);

        }
        return view('orders-list');
    }
}
