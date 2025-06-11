<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Motif;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('motifs', function (Blueprint $table) {
            $table->string('id', 5)->primary();
            $table->string('libelle', 100);
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
        });

        // Insert standard motifs
        $this->insertStandardMotifs();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motifs');
    }

    /**
     * Insert standard motifs
     */
    private function insertStandardMotifs(): void
    {
        $motifs = [
            [
                'id' => Motif::PERIODICITE,
                'libelle' => 'Visite périodique (tous les 6-8 mois)',
                'description' => 'Visite régulière selon le planning standard',
                'actif' => true
            ],
            [
                'id' => Motif::NOUVEAUTES,
                'libelle' => 'Nouveautés ou actualisations de produits',
                'description' => 'Présentation de nouveaux produits ou mise à jour',
                'actif' => true
            ],
            [
                'id' => Motif::REMONTAGE,
                'libelle' => 'Remontage suite à baisse de prescription',
                'description' => 'Relance suite à une baisse des prescriptions',
                'actif' => true
            ],
            [
                'id' => Motif::SOLLICITATION,
                'libelle' => 'Sollicitation du médecin',
                'description' => 'Visite à la demande du médecin',
                'actif' => true
            ],
            [
                'id' => Motif::AUTRE,
                'libelle' => 'Autre motif à préciser',
                'description' => 'Autre motif nécessitant une précision',
                'actif' => true
            ]
        ];

        foreach ($motifs as $motif) {
            DB::table('motifs')->insert($motif);
        }
    }
}; 