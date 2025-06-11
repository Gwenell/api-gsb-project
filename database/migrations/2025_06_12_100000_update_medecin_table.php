<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('medecin', function (Blueprint $table) {
            // Renommer les colonnes
            if (Schema::hasColumn('medecin', 'tel')) {
                $table->renameColumn('tel', 'telephone');
            }
            if (Schema::hasColumn('medecin', 'specialitecomplementaire')) {
                $table->renameColumn('specialitecomplementaire', 'specialite');
            }

            // Ajouter les nouvelles colonnes
            if (!Schema::hasColumn('medecin', 'cp')) {
                $table->string('cp', 5)->nullable()->after('adresse');
            }
            if (!Schema::hasColumn('medecin', 'ville')) {
                $table->string('ville', 100)->nullable()->after('cp');
            }
            if (!Schema::hasColumn('medecin', 'email')) {
                $table->string('email')->unique()->nullable()->after(Schema::hasColumn('medecin', 'telephone') ? 'telephone' : 'tel');
            }
            if (!Schema::hasColumn('medecin', 'coef_notoriete')) {
                $table->decimal('coef_notoriete', 8, 2)->nullable()->after(Schema::hasColumn('medecin', 'specialite') ? 'specialite' : 'specialitecomplementaire');
            }
            if (!Schema::hasColumn('medecin', 'coef_prescription')) {
                $table->decimal('coef_prescription', 8, 2)->nullable()->after('coef_notoriete');
            }
            
            // Supprimer l'ancienne colonne departement
            if (Schema::hasColumn('medecin', 'departement')) {
                $table->dropColumn('departement');
            }

            // Ajouter les timestamps s'ils n'existent pas
            if (!Schema::hasColumn('medecin', 'created_at') && !Schema::hasColumn('medecin', 'updated_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medecin', function (Blueprint $table) {
            // Inverser le renommage
            if (Schema::hasColumn('medecin', 'telephone')) {
                $table->renameColumn('telephone', 'tel');
            }
            if (Schema::hasColumn('medecin', 'specialite')) {
                $table->renameColumn('specialite', 'specialitecomplementaire');
            }
            
            // Supprimer les nouvelles colonnes
            $columns = [];
            foreach(['cp', 'ville', 'email', 'coef_notoriete', 'coef_prescription'] as $column) {
                if (Schema::hasColumn('medecin', $column)) {
                    $columns[] = $column;
                }
            }
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
            
            // Rajouter la colonne departement s'il n'existe pas
            if (!Schema::hasColumn('medecin', 'departement')) {
                $table->integer('departement')->nullable();
            }

            // Supprimer les timestamps
            if (Schema::hasColumn('medecin', 'created_at') && Schema::hasColumn('medecin', 'updated_at')) {
                $table->dropTimestamps();
            }
        });
    }
}; 