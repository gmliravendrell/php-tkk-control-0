<?php
/**
 * @OA\Get(
 *     path="/api/controls",
 *     summary="Get all controls",
 *     @OA\Response(
 *         response=200,
 *         description="List of controls"
 *     )
 * )
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Check;
use App\Models\Control;
use App\Models\Participant;
use Illuminate\Support\Facades\Log;

class CheckController extends Controller
{
    public function store(Request $request)
    {
        Log::debug('Creating a check with request ',['request' => $request->all()]);

        $validated = $request->validate([
            'control_id' => 'required|exists:controls,id',
            'participant_id' => 'required|exists:participants,id',
            'type' => 'required|in:check,abandon',
        ]);
        Log::debug('Validations passed');
        $control = Control::findOrFail($validated['control_id']);
        Log::debug('Control found');
        $participant = Participant::findOrFail($validated['participant_id']);
        Log::debug('Participant found');
        // Evitamos duplicados: check solo una vez, abandon solo una vez
        $existing = Check::where('control_id', $control->id)
                         ->where('participant_id', $participant->id)
                         ->where('type', $validated['type'])
                         ->first();
        if ($existing) {
            return response()->json([
                'error' => "Este participante ya tiene un registro de tipo '{$validated['type']}' en este control."
            ], 400);
        }
        Log::debug('Participant in valid state');
        $check = Check::create($validated);
        // Actualizamos totales en control
        if ($validated['type'] === 'check') {
            $control->passed++;
            $control->missing = max(0, $control->missing - 1);
        } elseif ($validated['type'] === 'abandon') {
            $control->abandoned++;
            $control->missing = max(0, $control->missing - 1);
        }

        $control->save();

        return response()->json([
            'message' => $validated['type'] === 'check' ? 'Marcado correctamente' : 'Abandono registrado',
            'check' => $check,
            'control' => $control,
        ]);
    }
}
