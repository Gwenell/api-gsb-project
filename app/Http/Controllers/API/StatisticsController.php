<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Rapport;
use App\Models\Medicament;
use App\Models\Medecin;
use App\Models\Motif;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class StatisticsController extends Controller
{
    /**
     * Get public dashboard statistics
     */
    public function getDashboardStats(Request $request)
    {
        try {
        $periode = $request->get('periode', 'mois');
        $date = $request->get('date', now()->format('Y-m'));

        \Log::info("Récupération des statistiques publiques - Période: {$periode}, Date: {$date}");

        // Count total reports, doctors and medications
        $totalRapports = Rapport::count();
        $totalMedecins = Medecin::count();
        $totalMedicaments = Medicament::count();
        
        // Recent reports (last 30 days)
        $recentRapports = Rapport::where('date', '>=', now()->subDays(30))->count();
        
        // Reports by month (last 7 months)
        $rapportsByMonth = [];
        for ($i = 6; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $startOfMonth = $monthDate->copy()->startOfMonth();
            $endOfMonth = $monthDate->copy()->endOfMonth();
            
            $monthName = $monthDate->format('M'); // Short month name
            $count = Rapport::whereBetween('date', [$startOfMonth, $endOfMonth])->count();
            
            $rapportsByMonth[] = [
                'name' => $monthName,
                'count' => $count
            ];
        }
        
        // Most visited doctors
        $topDoctors = DB::table('rapport')
            ->select('medecin.id', 'medecin.nom', 'medecin.prenom', DB::raw('COUNT(*) as count'))
            ->join('medecin', 'rapport.idMedecin', '=', 'medecin.id')
            ->groupBy('medecin.id', 'medecin.nom', 'medecin.prenom')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'name' => "Dr. {$item->nom} {$item->prenom}",
                    'count' => $item->count
                ];
            });

        return response()->json([
            'total_rapports' => $totalRapports,
            'total_medecins' => $totalMedecins,
            'total_medicaments' => $totalMedicaments,
            'recent_rapports' => $recentRapports,
            'rapports_by_month' => $rapportsByMonth,
            'top_doctors' => $topDoctors,
            'periode' => $date
        ]);
        } catch (Exception $e) {
            \Log::error("Error in dashboard stats: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'error' => 'An error occurred while fetching dashboard statistics',
                'message' => $e->getMessage(),
                'debug' => app()->environment('production') && !config('app.debug') ? null : [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            ], 500);
        }
    }
} 