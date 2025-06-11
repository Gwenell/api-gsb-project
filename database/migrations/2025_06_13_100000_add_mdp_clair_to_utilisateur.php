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
        Schema::table('utilisateur', function (Blueprint $table) {
            if (!Schema::hasColumn('utilisateur', 'mdp_clair')) {
                $table->string('mdp_clair')->nullable()->after('mdp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            if (Schema::hasColumn('utilisateur', 'mdp_clair')) {
                $table->dropColumn('mdp_clair');
            }
        });
    }
}; 