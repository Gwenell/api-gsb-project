<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, copy current mdp values to mdp_clair for all records
        DB::statement('UPDATE utilisateur SET mdp_clair = mdp WHERE mdp_clair IS NULL');

        // Get all users and hash their passwords
        $users = DB::table('utilisateur')->get();
        
        foreach ($users as $user) {
            DB::table('utilisateur')
                ->where('id', $user->id)
                ->update([
                    'mdp' => Hash::make($user->mdp_clair)
                ]);
        }
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This is not truly reversible since hashing is one-way,
     * but we can restore from mdp_clair if needed.
     */
    public function down(): void
    {
        // This is just to satisfy the migration interface.
        // Not actually restoring the original values.
    }
}; 