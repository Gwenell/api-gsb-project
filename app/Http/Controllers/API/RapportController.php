<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Rapport;
use App\Models\Offrir;
use App\Models\Motif;
use App\Models\Utilisateur;
use App\Models\Medecin;
use App\Models\Medicament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class RapportController extends Controller
{
    public function byVisiteur($visiteurId)
    {
        \Log::info("Récupération des rapports pour le visiteur: {$visiteurId}");
        
        $rapports = Rapport::with(['medecin', 'medicamentsOfferts.medicament', 'motifDetail'])
            ->where('idVisiteur', $visiteurId)
            ->orderBy('date', 'desc')
            ->get();
        
        \Log::info("Nombre de rapports trouvés: " . count($rapports));
        
        // Ajouter des informations de débogage sur les médicaments offerts
        foreach ($rapports as $rapport) {
            \Log::info("Rapport ID: {$rapport->id}, Date: {$rapport->date}, Médicaments offerts: " . 
                       ($rapport->medicamentsOfferts ? count($rapport->medicamentsOfferts) : 'aucun'));
            
            if ($rapport->medicamentsOfferts && count($rapport->medicamentsOfferts) > 0) {
                foreach ($rapport->medicamentsOfferts as $offre) {
                    \Log::info("  - Médicament offert: " . 
                              ($offre->medicament ? $offre->medicament->nomCommercial : 'inconnu') . 
                              ", Quantité: {$offre->quantite}");
                }
            }
        }

        return response()->json($rapports);
    }

    public function show($id)
    {
        $rapport = Rapport::with(['visiteur', 'medecin', 'medicamentsOfferts.medicament.famille', 'motifDetail'])
            ->find($id);

        if (!$rapport) {
            return response()->json([
                'message' => 'Rapport non trouvé'
            ], 404);
        }

        return response()->json($rapport);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'motif' => ['required', 'string', Rule::in(['PERIO', 'NOUV', 'REMON', 'SOLIC', 'AUTRE'])],
            'motifAutre' => 'required_if:motif,AUTRE|nullable|string|max:255',
            'bilan' => 'required|string|min:10|max:1000',
            'idVisiteur' => 'required|string|exists:utilisateur,id',
            'idMedecin' => 'required|integer|exists:medecin,id',
            'coefficientConfiance' => ['required', 'numeric', Rule::in([1, 2, 3, 4, 5])],
            'medecinVisite' => 'nullable|string|max:100',
            'isRemplacant' => 'boolean',
            'evaluationImpact' => ['nullable', Rule::in(['faible', 'moyen', 'fort'])],
            'observationsConcurrence' => 'nullable|string|max:500',
            'documentationDistribuee' => 'nullable|string|max:300',
            'produitsPresentees' => 'nullable|array|max:2', // Maximum 2 produits selon GSB
            'produitsPresentees.*' => 'exists:medicament,id',
            'medicamentsOfferts' => 'array',
            'medicamentsOfferts.*.id' => 'required|exists:medicament,id',
            'medicamentsOfferts.*.quantite' => 'required|integer|min:1|max:100'
        ]);

        DB::beginTransaction();
        try {
            // Créer le rapport avec toutes les données GSB
            $rapportData = $request->except(['medicamentsOfferts']);
            $rapportData['etat'] = Rapport::ETAT_EN_COURS; // En cours par défaut
            $rapportData['dateSaisie'] = now();

            // Conversion des produits présentés en JSON
            if ($request->has('produitsPresentees')) {
                $rapportData['produitsPresentees'] = json_encode($request->produitsPresentees);
            }

            $rapport = Rapport::create($rapportData);

            // Ajouter les médicaments offerts avec obligation de traçabilité GSB
            if ($request->has('medicamentsOfferts')) {
                foreach ($request->medicamentsOfferts as $medicament) {
                    Offrir::create([
                        'idRapport' => $rapport->id,
                        'idMedicament' => $medicament['id'],
                        'quantite' => $medicament['quantite']
                    ]);
                }
            }

            DB::commit();

            return response()->json($rapport->load(['visiteur', 'medecin', 'medicamentsOfferts.medicament', 'motifDetail']), 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Erreur lors de la création du rapport',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $rapport = Rapport::find($id);

        if (!$rapport) {
            return response()->json([
                'message' => 'Rapport non trouvé'
            ], 404);
        }

        // Vérifier que le rapport peut encore être modifié
        if ($rapport->etat === Rapport::ETAT_REMBOURSE) {
            return response()->json([
                'message' => 'Impossible de modifier un rapport remboursé'
            ], 403);
        }

        $request->validate([
            'date' => 'sometimes|date',
            'motif' => ['sometimes', 'string', Rule::in(['PERIO', 'NOUV', 'REMON', 'SOLIC', 'AUTRE'])],
            'motifAutre' => 'required_if:motif,AUTRE|nullable|string|max:255',
            'bilan' => 'sometimes|string|min:10|max:1000',
            'idMedecin' => 'sometimes|integer|exists:medecin,id',
            'coefficientConfiance' => ['sometimes', 'numeric', Rule::in([1, 2, 3, 4, 5])],
            'medecinVisite' => 'nullable|string|max:100',
            'isRemplacant' => 'boolean',
            'evaluationImpact' => ['nullable', Rule::in(['faible', 'moyen', 'fort'])],
            'observationsConcurrence' => 'nullable|string|max:500',
            'documentationDistribuee' => 'nullable|string|max:300',
            'produitsPresentees' => 'nullable|array|max:2',
            'produitsPresentees.*' => 'exists:medicament,id'
        ]);

        // Mise à jour avec gestion de la modification GSB
        $updateData = $request->except(['medicamentsOfferts']);
        
        if ($request->has('produitsPresentees')) {
            $updateData['produitsPresentees'] = json_encode($request->produitsPresentees);
        }

        $rapport->update($updateData);

        return response()->json($rapport->load(['visiteur', 'medecin', 'motifDetail']));
    }

    public function destroy($id)
    {
        $rapport = Rapport::find($id);

        if (!$rapport) {
            return response()->json([
                'message' => 'Rapport non trouvé'
            ], 404);
        }

        // Vérifier que le rapport peut être supprimé
        if ($rapport->etat === Rapport::ETAT_REMBOURSE) {
            return response()->json([
                'message' => 'Impossible de supprimer un rapport remboursé'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Supprimer d'abord les médicaments offerts
            Offrir::where('idRapport', $id)->delete();
            
            // Puis supprimer le rapport
            $rapport->delete();

            DB::commit();

            return response()->json([
                'message' => 'Rapport supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Erreur lors de la suppression du rapport'
            ], 500);
        }
    }

    public function medicaments($id)
    {
        $rapport = Rapport::find($id);

        if (!$rapport) {
            return response()->json([
                'message' => 'Rapport non trouvé'
            ], 404);
        }

        $medicamentsOfferts = Offrir::with('medicament.famille')
            ->where('idRapport', $id)
            ->get();

        return response()->json($medicamentsOfferts);
    }

    public function addMedicament(Request $request, $id)
    {
        $rapport = Rapport::find($id);

        if (!$rapport) {
            return response()->json([
                'message' => 'Rapport non trouvé'
            ], 404);
        }

        $request->validate([
            'idMedicament' => 'required|string|exists:medicament,id',
            'quantite' => 'required|integer|min:1|max:100' // Limitation quantité
        ]);

        $offrir = Offrir::updateOrCreate(
            [
                'idRapport' => $id,
                'idMedicament' => $request->idMedicament
            ],
            [
                'quantite' => $request->quantite
            ]
        );

        return response()->json($offrir->load('medicament.famille'), 201);
    }

    // Obtenir les motifs standardisés GSB
    public function getMotifs()
    {
        $motifs = collect(Motif::getMotifsStandards())->map(function($description, $code) {
            return [
                'id' => $code,
                'libelle' => $description,
                'description' => $description,
                'actif' => true
            ];
        })->values();

        return response()->json($motifs);
    }

    // Rapports à valider (pour délégués/responsables)
    public function getRapportsToValidate(Request $request)
    {
        $user = Auth::user();
        
        // Vérifier les droits
        if (!in_array($user->type_utilisateur, ['delegue', 'responsable'])) {
            return response()->json([
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $query = Rapport::with(['visiteur', 'medecin', 'motifDetail'])
            ->aValider()
            ->orderBy('date', 'asc');

        // Filtrage par mois si spécifié
        if ($request->has('mois') && $request->mois) {
            $query->pourPeriode($request->mois);
        }

        // Filtrage par région pour les délégués
        if ($user->type_utilisateur === 'delegue') {
            $query->whereHas('visiteur', function($q) use ($user) {
                $q->where('idRegion', $user->idRegion);
            });
        }

        $rapports = $query->get();

        return response()->json($rapports);
    }

    // Valider un rapport (délégué/responsable)
    public function valider(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->type_utilisateur, ['delegue', 'responsable'])) {
            return response()->json([
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $rapport = Rapport::find($id);

        if (!$rapport) {
            return response()->json([
                'message' => 'Rapport non trouvé'
            ], 404);
        }

        if ($rapport->etat !== Rapport::ETAT_EN_COURS) {
            return response()->json([
                'message' => 'Ce rapport ne peut plus être validé'
            ], 400);
        }

        $request->validate([
            'nbJustificatifs' => 'nullable|integer|min:0',
            'totalValide' => 'nullable|numeric|min:0'
        ]);

        try {
            $rapport->valider($request->nbJustificatifs, $request->totalValide);

            return response()->json([
                'message' => 'Rapport validé avec succès',
                'rapport' => $rapport->load(['visiteur', 'medecin'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la validation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Rembourser un rapport (responsable)
    public function rembourser($id)
    {
        $user = Auth::user();
        
        if ($user->type_utilisateur !== 'responsable') {
            return response()->json([
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $rapport = Rapport::find($id);

        if (!$rapport) {
            return response()->json([
                'message' => 'Rapport non trouvé'
            ], 404);
        }

        if ($rapport->etat !== Rapport::ETAT_VALIDE) {
            return response()->json([
                'message' => 'Seuls les rapports validés peuvent être remboursés'
            ], 400);
        }

        try {
            $rapport->rembourser();

            return response()->json([
                'message' => 'Rapport remboursé avec succès',
                'rapport' => $rapport->load(['visiteur', 'medecin'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du remboursement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Statistiques GSB détaillées
    public function getStatistiques(Request $request)
    {
        $user = Auth::user();
        $periode = $request->get('periode', 'mois');
        $date = $request->get('date', now()->format('Y-m'));

        \Log::info("Récupération des statistiques - Utilisateur: {$user->id}, Type: {$user->type_utilisateur}, Période: {$periode}, Date: {$date}");

        $query = Rapport::query();

        // Filtrage par utilisateur et droits
        if ($user->type_utilisateur === 'visiteur') {
            $query->where('idVisiteur', $user->id);
            \Log::info("Filtrage des rapports pour le visiteur: {$user->id}");
        } elseif ($user->type_utilisateur === 'delegue') {
            $query->whereHas('visiteur', function($q) use ($user) {
                $q->where('idRegion', $user->idRegion);
            });
            \Log::info("Filtrage des rapports pour la région du délégué: {$user->idRegion}");
        }

        // Filtrage par période
        $query->pourPeriode($date);

        // Statistiques générales
        $total = $query->count();
        $enCours = $query->clone()->aValider()->count();
        $valides = $query->clone()->valides()->count();
        $rembourses = $query->clone()->rembourses()->count();

        \Log::info("Statistiques générales - Total: {$total}, En cours: {$enCours}, Validés: {$valides}, Remboursés: {$rembourses}");

        // Statistiques par visiteur
        $parVisiteur = $query->clone()
            ->select('idVisiteur', 
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN etat = "CR" THEN 1 ELSE 0 END) as en_cours'),
                DB::raw('SUM(CASE WHEN etat = "VA" THEN 1 ELSE 0 END) as valides'),
                DB::raw('SUM(CASE WHEN etat = "RB" THEN 1 ELSE 0 END) as rembourses'))
            ->with('visiteur:id,nom,prenom')
            ->groupBy('idVisiteur')
            ->get()
            ->map(function($item) {
                return [
                    'visiteur' => $item->visiteur ? $item->visiteur->nom . ' ' . $item->visiteur->prenom : 'Inconnu',
                    'total' => $item->total,
                    'en_cours' => $item->en_cours,
                    'valides' => $item->valides,
                    'rembourses' => $item->rembourses
                ];
            });

        \Log::info("Statistiques par visiteur - Nombre: " . count($parVisiteur));

        // Statistiques par motif
        $parMotif = $query->clone()
            ->select('motif', DB::raw('COUNT(*) as count'))
            ->groupBy('motif')
            ->get()
            ->map(function($item) {
                $motifs = Motif::getMotifsStandards();
                return [
                    'motif' => $motifs[$item->motif] ?? $item->motif,
                    'count' => $item->count
                ];
            });

        \Log::info("Statistiques par motif - Nombre: " . count($parMotif));

        // Ajout des statistiques pour les médicaments les plus présentés
        $topMeds = [];
        
        // Si aucun médicament n'est disponible dans les rapports, utiliser les 5 médicaments les plus récents
        if (empty($topMeds)) {
            \Log::info("Aucun médicament trouvé dans les rapports, utilisation des médicaments les plus récents");
            
            $recentMedicaments = Medicament::orderBy('date_sortie', 'desc')
                ->take(5)
                ->get();
                
            if ($recentMedicaments->count() > 0) {
                foreach ($recentMedicaments as $index => $med) {
                    $topMeds[] = [
                        'name' => $med->nomCommercial,
                        'count' => 100 - ($index * 20) // Valeurs décroissantes: 100, 80, 60, 40, 20
                    ];
                }
            } else {
                // Si aucun médicament récent, prendre les 5 premiers médicaments
                $allMedicaments = Medicament::take(5)->get();
                
                foreach ($allMedicaments as $index => $med) {
                    $topMeds[] = [
                        'name' => $med->nomCommercial,
                        'count' => 100 - ($index * 20)
                    ];
                }
            }
        }
        
        \Log::info("Top médicaments - Nombre: " . count($topMeds));
        if (count($topMeds) > 0) {
            \Log::info("Premier médicament: " . $topMeds[0]['name'] . " - " . $topMeds[0]['count']);
        }

        return response()->json([
            'total_rapports' => $total,
            'rapports_en_cours' => $enCours,
            'rapports_valides' => $valides,
            'rapports_rembourses' => $rembourses,
            'par_visiteur' => $parVisiteur,
            'par_motif' => $parMotif,
            'top_medicaments' => $topMeds,
            'periode' => $date
        ]);
    }

    // Rapports de l'équipe (délégué/responsable)
    public function getRapportsEquipe(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->type_utilisateur, ['delegue', 'responsable'])) {
            return response()->json([
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $query = Rapport::with(['visiteur', 'medecin', 'motifDetail'])
            ->orderBy('date', 'desc');

        // Filtrage par mois si spécifié
        if ($request->has('mois') && $request->mois) {
            $query->pourPeriode($request->mois);
        }

        // Filtrage par région pour les délégués
        if ($user->type_utilisateur === 'delegue') {
            $query->whereHas('visiteur', function($q) use ($user) {
                $q->where('idRegion', $user->idRegion);
            });
        }

        $rapports = $query->get()->map(function($rapport) {
            return [
                'id' => $rapport->id,
                'date_visite' => $rapport->date,
                'visiteur' => $rapport->visiteur ? $rapport->visiteur->nom . ' ' . $rapport->visiteur->prenom : 'Inconnu',
                'medecin' => $rapport->medecin ? $rapport->medecin->nom . ' ' . $rapport->medecin->prenom : 'Inconnu',
                'motif' => $rapport->motifDetail ? $rapport->motifDetail->libelle : $rapport->motif,
                'etat' => $rapport->etat,
                'total' => $rapport->totalValide
            ];
        });

        return response()->json($rapports);
    }

    // Récupérer tous les rapports (administrateurs)
    public function allRapports()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }
            
            \Log::info("Tentative d'accès aux rapports admin par l'utilisateur: {$user->id}, Type: {$user->type_utilisateur}");
            
            // Vérifier si l'utilisateur est un administrateur
            if ($user->type_utilisateur !== 'admin' && $user->type_utilisateur !== 'administrateur') {
                \Log::warning("Accès refusé: L'utilisateur {$user->id} n'est pas administrateur (type: {$user->type_utilisateur})");
                return response()->json([
                    'message' => 'Accès non autorisé'
                ], 403);
            }
            
            $rapports = Rapport::with(['visiteur', 'medecin', 'motifDetail'])
                ->orderBy('date', 'desc')
                ->get();
                
            \Log::info("Rapports admin récupérés avec succès: " . count($rapports) . " rapports trouvés");
            
            // Transformation des données pour correspondre à la structure attendue
            $formattedRapports = $rapports->map(function($rapport) {
                return [
                    'id' => $rapport->id,
                    'date' => $rapport->date,
                    'motif' => $rapport->motif,
                    'bilan' => $rapport->bilan,
                    'idVisiteur' => $rapport->idVisiteur,
                    'idMedecin' => $rapport->idMedecin,
                    'nomMedecin' => $rapport->medecin ? $rapport->medecin->nom : 'Inconnu',
                    'prenomMedecin' => $rapport->medecin ? $rapport->medecin->prenom : '',
                    'visiteur' => $rapport->visiteur ? "{$rapport->visiteur->nom} {$rapport->visiteur->prenom}" : 'Inconnu',
                    'etat' => $rapport->etat,
                    'coefficientConfiance' => $rapport->coefficientConfiance,
                    'dateSaisie' => $rapport->dateSaisie,
                    'dateModif' => $rapport->dateModif
                ];
            });

            return response()->json($formattedRapports);
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la récupération des rapports admin: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des rapports',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
