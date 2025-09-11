<?php

namespace App\Http\Controllers;

use App\Models\Control;
use Illuminate\Http\Request;

class ControlController extends Controller
{
    // Vista principal con selector de controles
    public function index()
    {
        $controls = Control::all();
        return view('control.index', compact('controls'));
    }

    // Vista de un control específico
    public function show($id)
    {
        $control = Control::withCount(['checks'])->findOrFail($id);
        return view('control.show', compact('control'));
    }
}
