<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FicheFrais;
use Illuminate\Support\Facades\Auth;
use App\Models\FraisForfait;

class FicheFraisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = FicheFrais::with(['etat', 'lignesFraisForfait.fraisForfait', 'lignesFraisHorsForfait']);

        if ($user->type_utilisateur === 'responsable' || $user->type_utilisateur === 'admin') {
            // Responsable/Admin can see all fiches
        } else {
            // Visiteur only sees their own fiches
            $query->where('id_utilisateur', $user->id);
        }

        if ($request->has('mois')) {
            $query->where('mois', $request->input('mois'));
        }
        
        if ($request->has('etat')) {
            $query->where('id_etat', $request->input('etat'));
        }

        return $query->get();
    }

    /**
     * Get all forfait types
     */
    public function getFraisForfait()
    {
        return FraisForfait::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mois' => 'required|string|size:7',
            'lignesFraisForfait' => 'sometimes|array',
            'lignesFraisForfait.*.id_frais_forfait' => 'required|string|exists:frais_forfait,id',
            'lignesFraisForfait.*.quantite' => 'required|integer|min:0',
            'lignesFraisHorsForfait' => 'sometimes|array',
            'lignesFraisHorsForfait.*.libelle' => 'required|string|max:100',
            'lignesFraisHorsForfait.*.date' => 'required|date',
            'lignesFraisHorsForfait.*.montant' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();

        // Check if a fiche already exists for this user and month
        $existingFiche = FicheFrais::where('id_utilisateur', $user->id)
                                    ->where('mois', $request->mois)
                                    ->first();

        if ($existingFiche) {
            return response()->json(['message' => 'Une fiche de frais existe déjà pour ce mois.'], 409);
        }

        $ficheFrais = FicheFrais::create([
            'id_utilisateur' => $user->id,
            'mois' => $request->mois,
            'id_etat' => 'CR', // Créé
            'date_modif' => now(),
        ]);

        if ($request->has('lignesFraisForfait')) {
            foreach ($request->lignesFraisForfait as $ligne) {
                $ficheFrais->lignesFraisForfait()->create($ligne);
            }
        }

        if ($request->has('lignesFraisHorsForfait')) {
            foreach ($request->lignesFraisHorsForfait as $ligne) {
                $ficheFrais->lignesFraisHorsForfait()->create($ligne);
            }
        }

        return response()->json($ficheFrais->load(['lignesFraisForfait', 'lignesFraisHorsForfait']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $fiche = FicheFrais::with(['etat', 'lignesFraisForfait.fraisForfait', 'lignesFraisHorsForfait', 'utilisateur'])->findOrFail($id);

        if ($user->type_utilisateur !== 'responsable' && $user->type_utilisateur !== 'admin' && $fiche->id_utilisateur !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $fiche;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ficheFrais = FicheFrais::findOrFail($id);
        $user = Auth::user();

        if ($user->type_utilisateur !== 'responsable' && $user->type_utilisateur !== 'admin' && $ficheFrais->id_utilisateur !== $user->id) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Only allow update if the state is 'CR' (créé)
        if ($ficheFrais->id_etat !== 'CR') {
            return response()->json(['message' => 'La fiche ne peut plus être modifiée.'], 403);
        }

        $request->validate([
            'lignesFraisForfait' => 'sometimes|array',
            'lignesFraisForfait.*.id_frais_forfait' => 'required|string|exists:frais_forfait,id',
            'lignesFraisForfait.*.quantite' => 'required|integer|min:0',
            'lignesFraisHorsForfait' => 'sometimes|array',
            'lignesFraisHorsForfait.*.libelle' => 'required|string|max:100',
            'lignesFraisHorsForfait.*.date' => 'required|date',
            'lignesFraisHorsForfait.*.montant' => 'required|numeric|min:0',
        ]);

        // Update logic: delete old lines and create new ones
        $ficheFrais->lignesFraisForfait()->delete();
        $ficheFrais->lignesFraisHorsForfait()->delete();

        if ($request->has('lignesFraisForfait')) {
            foreach ($request->lignesFraisForfait as $ligne) {
                $ficheFrais->lignesFraisForfait()->create($ligne);
            }
        }

        if ($request->has('lignesFraisHorsForfait')) {
             foreach ($request->lignesFraisHorsForfait as $ligne) {
                $ficheFrais->lignesFraisHorsForfait()->create($ligne);
            }
        }
        
        $ficheFrais->date_modif = now();
        $ficheFrais->save();

        return $ficheFrais->load(['lignesFraisForfait', 'lignesFraisHorsForfait']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ficheFrais = FicheFrais::findOrFail($id);
        $user = Auth::user();

        if ($user->type_utilisateur !== 'responsable' && $user->type_utilisateur !== 'admin' && $ficheFrais->id_utilisateur !== $user->id) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($ficheFrais->id_etat !== 'CR') {
            return response()->json(['message' => 'La fiche ne peut pas être supprimée.'], 403);
        }
        
        $ficheFrais->lignesFraisForfait()->delete();
        $ficheFrais->lignesFraisHorsForfait()->delete();
        $ficheFrais->delete();

        return response()->json(null, 204);
    }
    
    /**
     * Validate the expense report.
     */
    public function valider(Request $request, string $id)
    {
        $this->authorizeAction(['responsable', 'admin']);

        $request->validate([
            'nb_justificatifs' => 'required|integer|min:0',
            'montant_valide' => 'required|numeric|min:0',
        ]);
        
        $ficheFrais = FicheFrais::findOrFail($id);

        if ($ficheFrais->id_etat !== 'CR') {
             return response()->json(['message' => 'La fiche a déjà été traitée.'], 403);
        }
        
        $ficheFrais->update([
            'id_etat' => 'VA', // Validée
            'nb_justificatifs' => $request->nb_justificatifs,
            'montant_valide' => $request->montant_valide,
            'date_modif' => now(),
        ]);

        return $ficheFrais;
    }

    /**
     * Set the expense report as refunded.
     */
    public function rembourser(string $id)
    {
        $this->authorizeAction(['responsable', 'admin']);
        
        $ficheFrais = FicheFrais::findOrFail($id);

        if ($ficheFrais->id_etat !== 'VA') {
             return response()->json(['message' => 'La fiche doit être validée avant d\'être remboursée.'], 403);
        }
        
        $ficheFrais->update([
            'id_etat' => 'RB', // Remboursée
            'date_modif' => now(),
        ]);

        return $ficheFrais;
    }

    private function authorizeAction(array $roles)
    {
        $user = Auth::user();
        if (!in_array($user->type_utilisateur, $roles)) {
            abort(403, 'Unauthorized action.');
        }
    }
} 