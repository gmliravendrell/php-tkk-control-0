<?php
/**
 * @OA\Info(
 *     title="Race API",
 *     version="1.0",
 *     description="API for managing race controls and participants"
 * )
 */
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Participant;
use App\Models\Check;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Control;


class ParticipantController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/participants",
     *     tags={"Participants"},
     *     summary="Get all participants",
     *     @OA\Response(
     *         response=200,
     *         description="List of participants",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function index() { return Participant::all(); }
        /**
     * @OA\Get(
     *     path="/api/participants/{id}",
     *     tags={"Participants"},
     *     summary="Get a participant by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Participant ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Participant details with related checks",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Participant not found")
     * )
     */
    public function show($id) { return Participant::with('checks')->findOrFail($id); }
        /**
     * @OA\Post(
     *     path="/api/participants",
     *     tags={"Participants"},
     *     summary="Create a new participant",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="dni", type="string", example="12345678A"),
     *             @OA\Property(property="phone", type="string", example="+34123456789"),
     *             @OA\Property(property="emergency_phone", type="string", example="+34987654321"),
     *             @OA\Property(property="status", type="string", enum={"not_presented","presented","abandoned","finished"}, example="presented"),
     *             @OA\Property(property="lunch_sandwich", type="string", example=true),
     *             @OA\Property(property="dinner_sandwich", type="string", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Participant created",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function store(Request $request) { return Participant::create($request->all()); }

    /**
     * @OA\Put(
     *     path="/api/participants/{id}",
     *     tags={"Participants"},
     *     summary="Update a participant",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Participant ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="Jane"),
     *             @OA\Property(property="last_name", type="string", example="Smith"),
     *             @OA\Property(property="status", type="string", enum={"not_presented","presented","abandoned","finished"}, example="finished")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Participant updated",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Participant not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $participant = Participant::findOrFail($id);
        $participant->update($request->all());
        return response()->json($participant);
    }
        /**
     * @OA\Delete(
     *     path="/api/participants/{id}",
     *     tags={"Participants"},
     *     summary="Delete a participant",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Participant ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=204, description="Participant deleted"),
     *     @OA\Response(response=404, description="Participant not found")
     * )
     */
    public function destroy($id)
    {
        Participant::findOrFail($id)->delete();
        return response()->noContent();
    }
        /**
     * @OA\Post(
     *     path="/api/admin/participants/import",
     *     tags={"Participants"},
     *     summary="Import participants from CSV",
     *     description="Upload a CSV file to import multiple participants. Existing checks are truncated.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"csv"},
     *                 @OA\Property(property="csv", type="string", format="binary", description="CSV file with participants")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Import result with added count and errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="added", type="integer", example=20),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid file")
     * )
     */
public function import(Request $request)
{
    Log::info('Importing participants with and csv file');

    $request->validate([
        'csv' => 'required|file|mimes:csv,txt'
    ]);
    Log::warning('Disabling foreing keys for data truncation');
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    Check::truncate();
    Participant::truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    Log::warning('Data truncated, enabling foreign keys');

    $file = $request->file('csv');
    $rows = array_map('str_getcsv', file($file->getRealPath()));
    $header = array_shift($rows);

    $added = 0;
    $errors = [];

    foreach ($rows as $index => $row) {
        $data = array_combine($header, $row);

        $validator = Validator::make($data, [
            'first_name'      => 'required|string',
            'last_name'       => 'required|string',
            'dni'             => 'nullable|string',
            'phone'           => 'nullable|string',
            'emergency_phone' => 'nullable|string',
            'status'          => 'nullable|in:not_presented,presented,abandoned,finished',
            'lunch_sandwich'  => 'nullable|string',
            'dinner_sandwich' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            $errors[$index + 2] = $validator->errors()->all();
            continue;
        }

        try {
            Participant::create($data);
            $added++;
        } catch (\Exception $e) {
            Log::error('Error creating participant with data: ' . $data);
            Log::error('The provided error is' . $e->getMessage());
            $errors[$index + 2] = ["Error al guardar en la BD: " . $e->getMessage()];
        }
    }

    // 🔹 Actualizamos todos los controles con los nuevos contadores
    foreach (Control::all() as $control) {
        Log::info("Reseting counters for control " . $control);
        $control->resetCounters($added);
    }

    return response()->json([
        'added'  => $added,
        'errors' => $errors
    ]);
}

}
