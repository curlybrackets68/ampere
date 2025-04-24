<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceController extends Controller
{
    //

    public function index(Request $request)
    {
        return view('service-list');
    }
}
