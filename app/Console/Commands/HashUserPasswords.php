<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class HashUserPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:hash-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hash existing plain-text passwords for all users and store them in the mdp field.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to hash user passwords...');

        // Find users who have a plain-text password.
        $users = Utilisateur::whereNotNull('mdp_clair')->where('mdp_clair', '!=', '')->get();

        if ($users->isEmpty()) {
            $this->info('No users with plain-text passwords found to hash.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        $updatedCount = 0;
        foreach ($users as $user) {
            try {
                // Hash the plain-text password and update the 'mdp' field.
                $hashedPassword = Hash::make($user->mdp_clair);
                
                DB::table('utilisateur')
                    ->where('id', $user->id)
                    ->update(['mdp' => $hashedPassword]);
                
                $updatedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to update password for user ID: {$user->id}. Error: " . $e->getMessage());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nPassword hashing complete. {$updatedCount} user(s) were updated.");

        return 0;
    }
} 