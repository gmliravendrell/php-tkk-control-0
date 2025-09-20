<?php
/**
 * @OA\Info(
 *     title="Race API",
 *     version="1.0",
 *     description="API for managing race controls and participants"
 * )
 */

namespace App\Http\Controllers\Api;

use App\Models\Control;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class ControlController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/controls",
     *     tags={"Controls"},
     *     summary="Get all controls",
     *     @OA\Response(
     *         response=200,
     *         description="List of controls",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function index()
    {
        return Control::all();
    }
   /**
     * @OA\Get(
     *     path="/api/controls/{id}",
     *     tags={"Controls"},
     *     summary="Get a control by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Control ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Control details with relations",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Control not found")
     * )
     */
    public function show($id)
    {
        return Control::with(['checks', 'events'])->findOrFail($id);
    }
    /**
     * @OA\Post(
     *     path="/api/controls",
     *     tags={"Controls"},
     *     summary="Create a new control",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","km_point"},
     *             @OA\Property(property="name", type="string", example="CP1"),
     *             @OA\Property(property="km_point", type="number", example=12.5),
     *             @OA\Property(property="responsible", type="string", example="John Doe"),
     *             @OA\Property(property="phone", type="string", example="+34123456789"),
     *             @OA\Property(property="status", type="string", enum={"preparing","open_requested","opened","close_requested","closed"}, example="preparing")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Control created",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $control = Control::create($request->all());
        return response()->json($control, 201);
    }
    /**
     * @OA\Patch(
     *     path="/api/controls/{id}",
     *     tags={"Controls"},
     *     summary="Update a control status",
     *     description="Update the status of a control with allowed state transitions.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Control ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"preparing","open_requested","opened","close_requested","closed"}, example="open_requested")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=400, description="Invalid state transition"),
     *     @OA\Response(response=404, description="Control not found")
     * )
     */
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
        return response()->json([
            'error' => "Cambio de estado no permitido: no se puede pasar de '$currentStatus' a '$newStatus'"
        ], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/controls/import",
     *     tags={"Controls"},
     *     summary="Import controls from CSV",
     *     description="Upload a CSV file to import multiple controls.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"csv"},
     *                 @OA\Property(property="csv", type="string", format="binary", description="CSV file with controls")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Import result with added count and errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="added", type="integer", example=5),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid file")
     * )
     */
    public function import(Request $request)
    {
        $request->validate(['csv' => 'required|file|mimes:csv,txt']);

        $file = $request->file('csv');
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($rows);

        $added = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $data = array_combine($header, $row);

            $validator = Validator::make($data, [
                'name' => 'required|string',
                'km_point' => 'required|numeric',
                'responsible' => 'nullable|string',
                'phone' => 'nullable|string',
                'status' => 'nullable|in:preparing,open_requested,opened,close_requested,closed'
            ]);

            if ($validator->fails()) {
                $errors[$index + 1] = $validator->errors()->all();
                continue;
            }

            Control::create($data);
            $added++;
        }

        return response()->json([
            'added' => $added,
            'errors' => $errors
        ]);
    }

    public function destroy($id)
    {
        $control = Control::findOrFail($id);
        $control->delete();
        return response()->noContent();
    }
}
