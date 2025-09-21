<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CentralReportController extends Controller
{
    public function index()
    {
        return view('central.report');
    }
}
