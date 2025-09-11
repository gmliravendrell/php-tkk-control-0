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

use App\Models\Control;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class ControlController extends Controller
{
    public function index()
    {
        return Control::all();
    }

    public function show($id)
    {
        return Control::with(['checks', 'events'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $control = Control::create($request->all());
        return response()->json($control, 201);
    }

    public function update(Request $request, $id)
    {
        Log::debug('Updating control to',['request' => $request->all()]);
        $control = Control::findOrFail($id);

        // Validamos que el campo 'status' venga y tenga un valor permitido
        $validated = $request->validate([
            'status' => 'required|in:preparing,open_requested,opened,close_requested,closed'
        ]);
        Log::debug('Input status validated');
        $newStatus = $validated['status'];
        $currentStatus = $control->status; 

        // Lógica de transiciones permitidas
        if ($currentStatus === 'preparing' && $newStatus === 'open_requested') {
            Log::debug("Open requested");
            $control->status = 'open_requested';
            $control->save();
            return response()->json(['message' => 'Solicitud de apertura enviada', 'control' => $control]);
        }

        if ($currentStatus === 'open_requested' && $newStatus === 'opened') {
            Log::debug("Open authorized");
            $control->status = 'opened';
            $control->save();
            return response()->json(['message' => 'Control abierto', 'control' => $control]);
        }

        if ($currentStatus === 'opened' && $newStatus === 'close_requested' && $control->missing > 0) {
            Log::debug("Close requested");
            $control->status = 'close_requested';
            $control->save();
            return response()->json(['message' => 'Solicitud de cierre enviada', 'control' => $control]);
        }
        if ($currentStatus === 'close_requested' && $newStatus === 'closed' && $control->missing > 0) {
            Log::debug("Close requested authorized");
            $control->status = 'closed';
            $control->save();
            return response()->json(['message' => 'Solicitud de cierre enviada', 'control' => $control]);
        }
        // Cualquier otra transición no permitida
        return response()->json([
            'error' => "Cambio de estado no permitido: no se puede pasar de '$currentStatus' a '$newStatus'"
        ], 400);

    }

    public function destroy($id)
    {
        $control = Control::findOrFail($id);
        $control->delete();
        return response()->noContent();
    }
}
