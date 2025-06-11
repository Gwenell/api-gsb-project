<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = Utilisateur::with('region');

        // Filtrage par type d'utilisateur si spécifié
        if ($request->has('type_utilisateur')) {
            $query->where('type_utilisateur', $request->type_utilisateur);
        }

        // Vérifier si l'utilisateur est authentifié et est un directeur
        $user = $request->user();
        if ($user && $user->type_utilisateur === 'directeur') {
            $query->where('idRegion', $user->idRegion);
        }

        $users = $query->orderBy('nom')->get();

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|string|max:4|unique:utilisateur,id',
            'nom' => 'required|string|max:30',
            'prenom' => 'required|string|max:30',
            'username' => 'required|string|max:20|unique:utilisateur,username',
            'mdp' => 'required|string|min:6',
            'adresse' => 'nullable|string|max:30',
            'cp' => 'nullable|string|max:5',
            'ville' => 'nullable|string|max:30',
            'dateEmbauche' => 'nullable|date',
            'type_utilisateur' => 'required|in:visiteur,delegue,responsable,directeur,comptable',
            'idRegion' => 'nullable|string|exists:region,id'
        ]);

        $userData = $request->all();
        $userData['mdp'] = Hash::make($request->mdp);
        $userData['timespan'] = time();

        $user = Utilisateur::create($userData);

        return response()->json($user->load('region'), 201);
    }

    public function update(Request $request, $id)
    {
        $user = Utilisateur::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        $request->validate([
            'nom' => 'sometimes|string|max:30',
            'prenom' => 'sometimes|string|max:30',
            'username' => 'sometimes|string|max:20|unique:utilisateur,username,' . $id,
            'mdp' => 'sometimes|string|min:6',
            'adresse' => 'nullable|string|max:30',
            'cp' => 'nullable|string|max:5',
            'ville' => 'nullable|string|max:30',
            'dateEmbauche' => 'nullable|date',
            'type_utilisateur' => 'sometimes|in:visiteur,delegue,responsable,directeur,comptable',
            'idRegion' => 'nullable|string|exists:region,id'
        ]);

        $userData = $request->all();
        
        if ($request->has('mdp')) {
            $userData['mdp'] = Hash::make($request->mdp);
        }

        $user->update($userData);

        return response()->json($user->load('region'));
    }

    public function destroy($id)
    {
        $user = Utilisateur::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }

    public function visiteursInRegion(Request $request)
    {
        $user = $request->user();
        
        // Seuls les directeurs régionaux peuvent voir les visiteurs de leur région
        if (!$user || $user->type_utilisateur !== 'directeur') {
            return response()->json([
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $visiteurs = Utilisateur::with('region')
            ->where('idRegion', $user->idRegion)
            ->where('type_utilisateur', 'visiteur')
            ->orderBy('nom')
            ->get();

        return response()->json($visiteurs);
    }
}
