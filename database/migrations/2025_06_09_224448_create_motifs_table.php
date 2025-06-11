<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('motifs', function (Blueprint $table) {
            $table->string('id', 10)->primary();
            $table->string('libelle', 100);
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
        });

        // Insertion des motifs standardisés selon les spécifications
        DB::table('motifs')->insert([
            [
                'id' => 'PERIO',
                'libelle' => 'Visite périodique',
                'description' => 'Visite de suivi tous les 6 à 8 mois',
                'actif' => true
            ],
            [
                'id' => 'NOUV',
                'libelle' => 'Nouveautés/Actualisations',
                'description' => 'Présentation nouveaux produits, conditionnements, législation',
                'actif' => true
            ],
            [
                'id' => 'REMON',
                'libelle' => 'Remontage',
                'description' => 'Chute de prescription, manque d\'enthousiasme constaté',
                'actif' => true
            ],
            [
                'id' => 'SOLIC',
                'libelle' => 'Sollicitation médecin',
                'description' => 'Demande du médecin pour information complémentaire',
                'actif' => true
            ],
            [
                'id' => 'AUTRE',
                'libelle' => 'Autre motif',
                'description' => 'Motif spécifique à préciser',
                'actif' => true
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motifs');
    }
};
