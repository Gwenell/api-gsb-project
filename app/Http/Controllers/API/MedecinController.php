<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Medecin;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MedecinController extends Controller
{
    public function index(Request $request)
    {
        $query = Medecin::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('adresse', 'like', "%{$search}%");
            });
        }

        $medecins = $query->orderBy('nom')->get();

        return response()->json($medecins);
    }

    public function show($id)
    {
        $medecin = Medecin::find($id);

        if (!$medecin) {
            return response()->json([
                'message' => 'Médecin non trouvé'
            ], 404);
        }

        return response()->json($medecin);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:30',
            'prenom' => 'required|string|max:30',
            'adresse' => 'required|string|max:80',
            'tel' => 'nullable|string|max:15',
            'specialitecomplementaire' => 'nullable|string|max:50',
            'departement' => 'required|integer'
        ]);

        $medecin = Medecin::create($request->all());

        return response()->json($medecin, 201);
    }

    public function update(Request $request, $id)
    {
        $medecin = Medecin::find($id);

        if (!$medecin) {
            return response()->json([
                'message' => 'Médecin non trouvé'
            ], 404);
        }

        $request->validate([
            'nom' => 'sometimes|string|max:30',
            'prenom' => 'sometimes|string|max:30',
            'adresse' => 'sometimes|string|max:80',
            'tel' => 'nullable|string|max:15',
            'specialitecomplementaire' => 'nullable|string|max:50',
            'departement' => 'sometimes|integer'
        ]);

        $medecin->update($request->all());

        return response()->json($medecin);
    }

    public function destroy($id)
    {
        $medecin = Medecin::find($id);

        if (!$medecin) {
            return response()->json([
                'message' => 'Médecin non trouvé'
            ], 404);
        }

        $medecin->delete();

        return response()->json([
            'message' => 'Médecin supprimé avec succès'
        ]);
    }
}
