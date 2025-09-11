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

use App\Models\Participant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ParticipantController extends Controller
{
    public function index() { return Participant::all(); }
    public function show($id) { return Participant::with('checks')->findOrFail($id); }
    public function store(Request $request) { return Participant::create($request->all()); }
    public function update(Request $request, $id)
    {
        $participant = Participant::findOrFail($id);
        $participant->update($request->all());
        return response()->json($participant);
    }
    public function destroy($id)
    {
        Participant::findOrFail($id)->delete();
        return response()->noContent();
    }
}
