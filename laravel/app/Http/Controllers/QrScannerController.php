<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QrScannerController extends Controller
{
    public function index($controlId)
    {
        $control = \App\Models\Control::findOrFail($controlId);

        return view('control.scan', compact('control'));
    }
}
