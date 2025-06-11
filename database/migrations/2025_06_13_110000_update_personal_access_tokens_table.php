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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Add all required columns for Sanctum
            if (!Schema::hasColumn('personal_access_tokens', 'tokenable_type')) {
                $table->morphs('tokenable');
            }
            
            if (!Schema::hasColumn('personal_access_tokens', 'name')) {
                $table->string('name');
            }
            
            if (!Schema::hasColumn('personal_access_tokens', 'token')) {
                $table->string('token', 64)->unique();
            }
            
            if (!Schema::hasColumn('personal_access_tokens', 'abilities')) {
                $table->text('abilities')->nullable();
            }
            
            if (!Schema::hasColumn('personal_access_tokens', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable();
            }
            
            if (!Schema::hasColumn('personal_access_tokens', 'expires_at')) {
                $table->timestamp('expires_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $columns = [
                'tokenable_type', 'tokenable_id', 'name', 'token', 
                'abilities', 'last_used_at', 'expires_at'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('personal_access_tokens', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}; 