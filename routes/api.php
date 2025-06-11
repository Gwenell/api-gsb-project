<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MedecinController;
use App\Http\Controllers\API\MedicamentController;
use App\Http\Controllers\API\RapportController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\FicheFraisController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Routes d'authentification
Route::post('/login', [AuthController::class, 'login']);

// Routes publiques
Route::get('/medecins', [MedecinController::class, 'index']);
Route::get('/medecins/{id}', [MedecinController::class, 'show']);
Route::get('/medicaments', [MedicamentController::class, 'index']);
Route::get('/medicaments/{id}', [MedicamentController::class, 'show']);
Route::get('/medicaments/dangerosity', [MedicamentController::class, 'byDangerosity']);
Route::get('/medicaments/recent', [MedicamentController::class, 'recent']);
Route::get('/medicaments/current-month', [MedicamentController::class, 'currentMonth']);
Route::get('/familles', [MedicamentController::class, 'familles']);
Route::get('/dashboard-stats', [App\Http\Controllers\API\StatisticsController::class, 'getDashboardStats']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Médecins - opérations d'écriture
    Route::post('/medecins', [MedecinController::class, 'store']);
    Route::put('/medecins/{id}', [MedecinController::class, 'update']);
    Route::delete('/medecins/{id}', [MedecinController::class, 'destroy']);

    // Médicaments - opérations d'écriture
    Route::post('/medicaments', [MedicamentController::class, 'store']);
    Route::put('/medicaments/{id}', [MedicamentController::class, 'update']);
    Route::delete('/medicaments/{id}', [MedicamentController::class, 'destroy']);
    Route::get('/medicaments/by-visiteur', [MedicamentController::class, 'byVisiteurAndMonth']);
    Route::post('/medicaments/assign', [MedicamentController::class, 'assignToVisiteurs']);
    Route::get('/medicaments/pdf', [MedicamentController::class, 'generatePdf']);

    // Rapports
    Route::get('/visiteurs/{id}/rapports', [RapportController::class, 'byVisiteur']);
    Route::get('/rapports/{id}', [RapportController::class, 'show']);
    Route::post('/rapports', [RapportController::class, 'store']);
    Route::put('/rapports/{id}', [RapportController::class, 'update']);
    Route::delete('/rapports/{id}', [RapportController::class, 'destroy']);
    Route::get('/rapports/{id}/medicaments', [RapportController::class, 'medicaments']);
    Route::post('/rapports/{id}/medicaments', [RapportController::class, 'addMedicament']);
    
    // Nouvelles routes GSB pour rapports
    Route::get('/motifs', [RapportController::class, 'getMotifs']);
    Route::get('/rapports-validation', [RapportController::class, 'getRapportsToValidate']);
    Route::put('/rapports/{id}/valider', [RapportController::class, 'valider']);
    Route::put('/rapports/{id}/rembourser', [RapportController::class, 'rembourser']);
    Route::get('/statistiques', [RapportController::class, 'getStatistiques']);
    Route::get('/equipe/rapports', [RapportController::class, 'getRapportsEquipe']);
    Route::get('/admin/rapports', [RapportController::class, 'allRapports']);

    // Gestion des fiches de frais
    Route::get('/frais-forfait', [FicheFraisController::class, 'getFraisForfait']);
    Route::apiResource('/fiches-frais', FicheFraisController::class);
    Route::put('/fiches-frais/{id}/valider', [FicheFraisController::class, 'valider']);
    Route::put('/fiches-frais/{id}/rembourser', [FicheFraisController::class, 'rembourser']);

    // Région/Visiteurs
    Route::get('/region/visiteurs', [UserController::class, 'visiteursInRegion']);
    
    // Gestion des utilisateurs (pour les responsables/directeurs)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
}); 