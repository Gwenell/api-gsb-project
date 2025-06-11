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
        // Ajouter seulement les champs GSB manquants à la table rapport
        Schema::table('rapport', function (Blueprint $table) {
            // Vérifier si les colonnes n'existent pas avant de les ajouter
            if (!Schema::hasColumn('rapport', 'evaluationImpact')) {
                $table->enum('evaluationImpact', ['faible', 'moyen', 'fort'])->nullable()->after('isRemplacant');
            }
            
            if (!Schema::hasColumn('rapport', 'observationsConcurrence')) {
                $table->text('observationsConcurrence')->nullable()->after('bilan');
            }
            
            if (!Schema::hasColumn('rapport', 'documentationDistribuee')) {
                $table->text('documentationDistribuee')->nullable()->after('observationsConcurrence');
            }
            
            if (!Schema::hasColumn('rapport', 'nbJustificatifs')) {
                $table->integer('nbJustificatifs')->nullable()->after('dateSaisie');
            }
            
            if (!Schema::hasColumn('rapport', 'totalValide')) {
                $table->decimal('totalValide', 10, 2)->nullable()->after('nbJustificatifs');
            }
            
            if (!Schema::hasColumn('rapport', 'derniereVisite')) {
                $table->timestamp('derniereVisite')->nullable()->after('totalValide');
            }
            
            if (!Schema::hasColumn('rapport', 'prochainVisite')) {
                $table->timestamp('prochainVisite')->nullable()->after('derniereVisite');
            }
        });

        // Vérifier si la table motifs existe, sinon la créer
        if (!Schema::hasTable('motifs')) {
            Schema::create('motifs', function (Blueprint $table) {
                $table->string('id', 10)->primary();
                $table->string('libelle', 100);
                $table->text('description');
                $table->boolean('actif')->default(true);
            });

            // Insérer les motifs standardisés GSB
            DB::table('motifs')->insert([
                [
                    'id' => 'PERIO',
                    'libelle' => 'Visite périodique',
                    'description' => 'Visite périodique tous les 6 à 8 mois selon le planning établi',
                    'actif' => 1
                ],
                [
                    'id' => 'NOUV',
                    'libelle' => 'Nouveautés',
                    'description' => 'Présentation de nouveaux produits, conditionnements ou actualisations (législation, remboursement)',
                    'actif' => 1
                ],
                [
                    'id' => 'REMON',
                    'libelle' => 'Remontage',
                    'description' => 'Suite à une baisse de prescription dans la zone d\'influence du spécialiste ou manque d\'enthousiasme constaté',
                    'actif' => 1
                ],
                [
                    'id' => 'SOLIC',
                    'libelle' => 'Sollicitation',
                    'description' => 'Demande du médecin pour information complémentaire sur un médicament ou effets constatés',
                    'actif' => 1
                ],
                [
                    'id' => 'AUTRE',
                    'libelle' => 'Autre motif',
                    'description' => 'Autre motif non standard (à préciser dans le champ motifAutre)',
                    'actif' => 1
                ]
            ]);
        }

        // Modifier la table personal_access_tokens pour supporter les IDs string
        if (Schema::hasTable('personal_access_tokens')) {
            try {
                Schema::table('personal_access_tokens', function (Blueprint $table) {
                    $table->string('tokenable_id')->change();
                });
            } catch (\Exception $e) {
                // Ignorer si déjà modifié
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer seulement les champs ajoutés dans cette migration
        Schema::table('rapport', function (Blueprint $table) {
            $table->dropColumn([
                'evaluationImpact',
                'observationsConcurrence',
                'documentationDistribuee',
                'nbJustificatifs',
                'totalValide',
                'derniereVisite',
                'prochainVisite'
            ]);
        });

        // Ne pas supprimer la table motifs car elle pourrait contenir des données
    }
};
