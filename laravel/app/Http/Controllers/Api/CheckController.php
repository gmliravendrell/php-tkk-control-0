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
        Log::debug('Creating a check with request ', ['request' => $request->all()]);

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
    
        // 1️⃣ Validar que el participante está presented
        if ($request->type == 'check' && $participant->status !== 'presented') {
            return response()->json([
                'error' => "No se puede marcar al participante porque su estado actual es " . $participant->status
            ], 400);
        }
        if ($request->type == 'abandon' && $participant->status == 'finished') {
            return response()->json([
                'error' => "El participante no puede abandonar porque ya ha marcado en el control de Meta"
            ], 400);
        }

        // Evitamos duplicados exactos (control + participante + type)
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

        // Actualizamos totales en control y estado del participante
        if ($validated['type'] === 'check') {
            $control->passed++;
            $control->missing = max(0, $control->missing - 1);

            // 3️⃣ Si el control es finish, marcar participante como finished
            if ($control->type === 'finish') {
                $participant->status = 'finished';
                $participant->save();
            }

        } elseif ($validated['type'] === 'abandon') {
            $control->abandoned++;
            $control->missing = max(0, $control->missing - 1);

            // 2️⃣ Actualizar estado del participante a abandon
            $participant->status = 'abandoned';
            $participant->save();
        }

        $control->save();

        return response()->json([
            'message' => $validated['type'] === 'check'
                ? ($control->type === 'finish' ? 'Marcado en meta, participante finalizado' : 'Marcado correctamente')
                : 'Abandono registrado',
            'check' => $check,
            'control' => $control,
            'participant' => $participant,
        ]);
    }
       /**
     * @OA\Get(
     *     path="/api/controls/{control}/checks",
     *     summary="Get participants who checked at a control",
     *     description="Returns the list of participants who have passed a given control, ordered by timestamp descending.",
     *     tags={"Checks"},
     *     @OA\Parameter(
     *         name="control",
     *         in="path",
     *         required=true,
     *         description="ID of the control",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of participants who passed the control",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="dorsal", type="integer", example=23),
     *                 @OA\Property(property="name", type="string", example="Maria López"),
     *                 @OA\Property(property="checked_at", type="string", example="2025-09-21 09:30:00")
     *             )
     *         )
     *     )
     * )
     */
    public function getChecksByControl($controlId)
    {
        $control = Control::findOrFail($controlId);

        $checks = Check::with('participant')
            ->where('control_id', $control->id)
            ->where('type', 'check')
            ->orderBy('checked_at', 'desc')
            ->get()
            ->map(function ($check) {
                return [
                    'dorsal'    => $check->participant->id,
                    'name'      => $check->participant->first_name . ' ' . $check->participant->last_name,
                    'checked_at' => $check->checked_at,
                ];
            });

        return response()->json($checks);
    }

    /**
     * @OA\Get(
     *     path="/api/checks/abandons",
     *     summary="Get participants who abandoned",
     *     description="Returns the list of participants who have abandoned at any control, including the control name.",
     *     tags={"Checks"},
     *     @OA\Response(
     *         response=200,
     *         description="List of participants who abandoned",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="dorsal", type="integer", example=42),
     *                 @OA\Property(property="name", type="string", example="Carlos Martínez"),
     *                 @OA\Property(property="checked_at", type="string", example="2025-09-21 10:15:00"),
     *                 @OA\Property(property="control", type="string", example="Avituallamiento 2 - KM 15")
     *             )
     *         )
     *     )
     * )
     */
    public function getAbandons()
    {
        $abandons = Check::with(['participant', 'control'])
            ->where('type', 'abandon')
            ->orderBy('checked_at', 'desc')
            ->get()
            ->map(function ($check) {
                return [
                    'dorsal'    => $check->participant->id,
                    'name'      => $check->participant->first_name . ' ' . $check->participant->last_name,
                    'checked_at' => $check->checked_at,
                    'control'   => $check->control->name,
                ];
            });

        return response()->json($abandons);
    }
}
