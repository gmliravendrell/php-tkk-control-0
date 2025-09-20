<?php
/**
 * @OA\Info(
 *     title="Race API",
 *     version="1.0",
 *     description="API for managing race controls and participants"
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
    /**
     * @OA\Post(
     *     path="/api/checks",
     *     summary="Register a check or dropout at a control",
     *     description="Creates a record of passage (check) or dropout for a participant at a specific control.",
     *     tags={"Checks"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"control_id","participant_id","type"},
     *             @OA\Property(property="control_id", type="integer", example=1, description="ID of the control"),
     *             @OA\Property(property="participant_id", type="integer", example=10, description="ID of the participant"),
     *             @OA\Property(property="type", type="string", enum={"check","abandon"}, example="check", description="Type of record")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Record successfully created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Checked successfully"),
     *             @OA\Property(
     *                 property="check",
     *                 type="object",
     *                 description="Created record data"
     *             ),
     *             @OA\Property(
     *                 property="control",
     *                 type="object",
     *                 description="Updated control state"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or duplicate record",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="This participant already has a 'check' record at this control.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Control or participant not found"
     *     )
     * )
     */
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
