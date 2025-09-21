<?php

namespace App\Http\Controllers;

class QrScannerController extends Controller
{
    public function index($controlId)
    {
        $control = \App\Models\Control::findOrFail($controlId);

        return view('control.scan', compact('control'));
    }
}
