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
        Schema::table('rapport', function (Blueprint $table) {
            // État du rapport : CR (en cours), VA (validé), RB (remboursé)
            $table->enum('etat', ['CR', 'VA', 'RB'])->default('CR')->after('bilan');
            
            // Date de modification (pour validation/remboursement)
            $table->timestamp('dateModif')->nullable()->after('etat');
            
            // Coefficient de confiance du médecin (0.0 à 5.0)
            $table->decimal('coefficientConfiance', 3, 1)->nullable()->after('dateModif');
            
            // Nom du médecin effectivement visité (si remplaçant)
            $table->string('medecinVisite', 100)->nullable()->after('coefficientConfiance');
            
            // Indique si c'est un remplaçant qui a été vu
            $table->boolean('isRemplacant')->default(false)->after('medecinVisite');
            
            // Date de saisie du rapport
            $table->timestamp('dateSaisie')->nullable()->after('isRemplacant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rapport', function (Blueprint $table) {
            $table->dropColumn([
                'etat',
                'dateModif', 
                'coefficientConfiance',
                'medecinVisite',
                'isRemplacant',
                'dateSaisie'
            ]);
        });
    }
};
