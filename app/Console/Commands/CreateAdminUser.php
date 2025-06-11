<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $adminId = 'a001';

        Utilisateur::updateOrCreate(
            ['username' => 'admin'],
            [
                'id' => $adminId,
                'nom' => 'Admin',
                'prenom' => 'Admin',
                'mdp' => Hash::make('admin'),
                'type_utilisateur' => 'admin',
                'salt' => 'dummy-salt', // Provide a dummy value
                'adresse' => 'Admin Address',
                'cp' => '00000',
                'ville' => 'Adminville',
                'dateEmbauche' => now(),
            ]
        );
        $this->info('Admin user created successfully.');
    }
} 