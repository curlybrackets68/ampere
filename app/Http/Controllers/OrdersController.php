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
                    return '<a href="' . route('leads.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a>';
                })
                ->make(true);

        }
        return view('orders-list');
    }
}
