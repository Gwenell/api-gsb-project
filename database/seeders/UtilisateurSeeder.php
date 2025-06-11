<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Utilisateur;
use App\Models\Region;

class UtilisateurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer quelques régions d'abord
        $regions = [
            ['id' => 'A', 'nom' => 'Aquitaine'],
            ['id' => 'B', 'nom' => 'Bretagne'],
            ['id' => 'C', 'nom' => 'Centre'],
            ['id' => 'N', 'nom' => 'Normandie'],
        ];

        foreach ($regions as $region) {
            Region::updateOrCreate(['id' => $region['id']], $region);
        }

        // Créer des utilisateurs de test
        $utilisateurs = [
            [
                'id' => 'a17',
                'nom' => 'Andret',
                'prenom' => 'Louis',
                'username' => 'landret',
                'mdp' => Hash::make('secret'),
                'mdp_clair' => 'secret',
                'adresse' => '1 rue du Château',
                'cp' => '29200',
                'ville' => 'Brest',
                'dateEmbauche' => '2010-03-15',
                'timespan' => time(),
                'type_utilisateur' => 'visiteur',
                'idRegion' => 'B'
            ],
            [
                'id' => 'b25',
                'nom' => 'Durand',
                'prenom' => 'Marie',
                'username' => 'mdurand',
                'mdp' => Hash::make('secret'),
                'mdp_clair' => 'secret',
                'adresse' => '45 avenue de la République',
                'cp' => '33000',
                'ville' => 'Bordeaux',
                'dateEmbauche' => '2012-06-20',
                'timespan' => time(),
                'type_utilisateur' => 'visiteur',
                'idRegion' => 'A'
            ],
            [
                'id' => 'd13',
                'nom' => 'Dubois',
                'prenom' => 'Patrick',
                'username' => 'pdubois',
                'mdp' => Hash::make('secret'),
                'mdp_clair' => 'secret',
                'adresse' => '12 place de la Mairie',
                'cp' => '37000',
                'ville' => 'Tours',
                'dateEmbauche' => '2008-09-10',
                'timespan' => time(),
                'type_utilisateur' => 'delegue',
                'idRegion' => 'C'
            ],
            [
                'id' => 'r01',
                'nom' => 'Martin',
                'prenom' => 'Jean',
                'username' => 'jmartin',
                'mdp' => Hash::make('secret'),
                'mdp_clair' => 'secret',
                'adresse' => '78 rue de la Paix',
                'cp' => '14000',
                'ville' => 'Caen',
                'dateEmbauche' => '2005-01-15',
                'timespan' => time(),
                'type_utilisateur' => 'responsable',
                'idRegion' => 'N'
            ],
            [
                'id' => 'c01',
                'nom' => 'Dumoulin',
                'prenom' => 'Alphonse',
                'username' => 'adumoulin',
                'mdp' => Hash::make('secret'),
                'mdp_clair' => 'secret',
                'adresse' => '32 boulevard Haussmann',
                'cp' => '75008',
                'ville' => 'Paris',
                'dateEmbauche' => '2000-05-01',
                'timespan' => time(),
                'type_utilisateur' => 'comptable',
                'idRegion' => null
            ]
        ];

        foreach ($utilisateurs as $utilisateur) {
            Utilisateur::updateOrCreate(['id' => $utilisateur['id']], $utilisateur);
        }

        $this->command->info('Utilisateurs créés avec succès !');
        $this->command->info('Identifiants de connexion:');
        $this->command->info('- Visiteur: landret / secret');
        $this->command->info('- Visiteur: mdurand / secret');
        $this->command->info('- Délégué: pdubois / secret');
        $this->command->info('- Responsable: jmartin / secret');
        $this->command->info('- Comptable: adumoulin / secret');
    }
}
