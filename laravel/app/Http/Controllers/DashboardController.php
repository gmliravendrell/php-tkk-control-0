<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Control;

class DashboardController extends Controller
{
    public function index()
    {
        // Recuperamos todos los controles
        $controls = Control::withCount([
            'checks as passed' => function ($query) {
                $query->where('type', 'check');
            },
            'checks as abandoned' => function ($query) {
                $query->where('type', 'abandon');
            }
        ])->get();

        // Calculamos los pendientes por cada control
        foreach ($controls as $control) {
            $control->missing = $control->total_participants - ($control->passed + $control->abandoned);
        }

        return view('dashboard.dashboard', compact('controls'));
    }
}
