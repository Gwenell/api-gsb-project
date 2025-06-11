<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Medicament;
use App\Models\Famille;
use App\Models\Presenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedicamentController extends Controller
{
    public function index(Request $request)
    {
        $query = Medicament::with('famille');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomCommercial', 'like', "%{$search}%")
                  ->orWhere('composition', 'like', "%{$search}%")
                  ->orWhere('effets', 'like', "%{$search}%");
            });
        }

        $medicaments = $query->orderBy('id')->get();

        return response()->json($medicaments);
    }

    public function show($id)
    {
        $medicament = Medicament::with('famille')->find($id);

        if (!$medicament) {
            return response()->json([
                'message' => 'Médicament non trouvé'
            ], 404);
        }

        return response()->json($medicament);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|string|max:30|unique:medicament,id',
            'nomCommercial' => 'required|string|max:80',
            'idFamille' => 'required|string|exists:famille,id',
            'composition' => 'required|string|max:100',
            'effets' => 'required|string|max:100',
            'contreIndications' => 'required|string|max:100',
            'image_url' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'niveau_dangerosité' => 'nullable|integer|min:1|max:5',
            'date_sortie' => 'nullable|date'
        ]);

        $medicament = Medicament::create($request->all());

        return response()->json($medicament->load('famille'), 201);
    }

    public function update(Request $request, $id)
    {
        $medicament = Medicament::find($id);

        if (!$medicament) {
            return response()->json([
                'message' => 'Médicament non trouvé'
            ], 404);
        }

        $request->validate([
            'nomCommercial' => 'sometimes|string|max:80',
            'idFamille' => 'sometimes|string|exists:famille,id',
            'composition' => 'sometimes|string|max:100',
            'effets' => 'sometimes|string|max:100',
            'contreIndications' => 'sometimes|string|max:100',
            'image_url' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'niveau_dangerosité' => 'nullable|integer|min:1|max:5',
            'date_sortie' => 'nullable|date'
        ]);

        $medicament->update($request->all());

        return response()->json($medicament->load('famille'));
    }

    public function destroy($id)
    {
        $medicament = Medicament::find($id);

        if (!$medicament) {
            return response()->json([
                'message' => 'Médicament non trouvé'
            ], 404);
        }

        $medicament->delete();

        return response()->json([
            'message' => 'Médicament supprimé avec succès'
        ]);
    }

    public function byDangerosity()
    {
        $medicaments = Medicament::with('famille')
            ->whereNotNull('niveau_dangerosité')
            ->orderBy('niveau_dangerosité', 'desc')
            ->get();

        return response()->json($medicaments);
    }

    public function recent()
    {
        $medicaments = Medicament::with('famille')
            ->whereNotNull('date_sortie')
            ->where('date_sortie', '>=', Carbon::now()->subMonths(6))
            ->orderBy('date_sortie', 'desc')
            ->get();

        return response()->json($medicaments);
    }

    public function currentMonth()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        
        $medicaments = Medicament::with('famille')
            ->whereHas('presenter', function($query) use ($currentMonth) {
                $query->where('annee_mois', $currentMonth);
            })
            ->get();

        return response()->json($medicaments);
    }

    public function byVisiteurAndMonth(Request $request)
    {
        $request->validate([
            'idVisiteur' => 'required|string',
            'annee_mois' => 'required|string'
        ]);

        $medicaments = Medicament::with('famille')
            ->whereHas('presenter', function($query) use ($request) {
                $query->where('idVisiteur', $request->idVisiteur)
                      ->where('annee_mois', $request->annee_mois);
            })
            ->get();

        return response()->json($medicaments);
    }

    public function assignToVisiteurs(Request $request)
    {
        $request->validate([
            'annee_mois' => 'required|string',
            'visiteurs' => 'required|array',
            'visiteurs.*' => 'string|exists:utilisateur,id',
            'medicaments' => 'required|array',
            'medicaments.*' => 'string|exists:medicament,id'
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->visiteurs as $visiteur) {
                foreach ($request->medicaments as $medicament) {
                    Presenter::updateOrCreate([
                        'idVisiteur' => $visiteur,
                        'idMedicament' => $medicament,
                        'annee_mois' => $request->annee_mois
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Médicaments assignés avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Erreur lors de l\'assignation des médicaments'
            ], 500);
        }
    }

    public function generatePdf(Request $request)
    {
        // Cette méthode nécessiterait une librairie PDF comme DomPDF
        // Pour l'instant, on renvoie juste les données
        $request->validate([
            'annee_mois' => 'required|string'
        ]);

        $medicaments = Medicament::with(['famille', 'presenter' => function($query) use ($request) {
            $query->where('annee_mois', $request->annee_mois);
        }])->get();

        return response()->json($medicaments);
    }

    public function familles()
    {
        $familles = Famille::orderBy('libelle')->get();
        return response()->json($familles);
    }
}
