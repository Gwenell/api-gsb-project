<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedecinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Début du seeder pour les médecins...');

        // Désactiver les contraintes de clé étrangère et vider la table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('medecin')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('Table medecin vidée.');

        // Récupérer les données à partir du tableau défini ci-dessous
        $data = $this->getData();
        $this->command->info('Traitement de ' . count($data) . ' médecins.');

        // Créer un tableau pour l'insertion en batch
        $medecins = [];
        $now = Carbon::now();

        foreach ($data as $medecin) {
            // Extraire les informations de l'adresse
            $adresse = $medecin['adresse'];
            $cp = '';
            $ville = '';

            // Analyser l'adresse pour extraire le code postal et la ville
            if (preg_match('/([A-Z\-\s]+)\s+(\d{5})$/', $adresse, $matches)) {
                $ville = trim($matches[1]);
                $cp = $matches[2];
                $adresse = trim(str_replace($matches[0], '', $adresse));
            }

            // Générer un email unique
            $email = strtolower($medecin['prenom'] . '.' . $medecin['nom'] . '@gsb.fr');
            $email = iconv('UTF-8', 'ASCII//TRANSLIT', $email); // Supprimer les accents
            $email = preg_replace('/[^a-zA-Z0-9@\.]/', '', $email); // Nettoyer les caractères spéciaux

            // Définir des coefficients aléatoires entre 1 et 5
            $coef_notoriete = rand(10, 50) / 10;
            $coef_prescription = rand(10, 50) / 10;

            // Préparer l'enregistrement pour l'insertion
            $medecins[] = [
                'id' => $medecin['id'],
                'nom' => $medecin['nom'],
                'prenom' => $medecin['prenom'],
                'adresse' => $adresse,
                'cp' => $cp,
                'ville' => $ville,
                'telephone' => $medecin['tel'],
                'specialite' => $medecin['specialitecomplementaire'],
                'email' => $email,
                'coef_notoriete' => $coef_notoriete,
                'coef_prescription' => $coef_prescription,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        // Insérer les données par lots de 100
        $chunks = array_chunk($medecins, 100);
        foreach ($chunks as $index => $chunk) {
            DB::table('medecin')->insert($chunk);
            $this->command->info('Lot ' . ($index + 1) . ' de ' . count($chunks) . ' inséré.');
        }

        $this->command->info('Seeding terminé avec succès.');
    }

    /**
     * Définit les données des médecins à insérer
     */
    private function getData(): array
    {
        return [
            ['id' => 1, 'nom' => 'MARTIN', 'prenom' => 'Prosper', 'adresse' => '25 rue Anatole France BRIANCON 05100', 'tel' => '0485244174', 'specialitecomplementaire' => null, 'departement' => 5],
            ['id' => 2, 'nom' => 'BLANC', 'prenom' => 'Anne-Lucie', 'adresse' => '39 rue des gatinnes BILLIAT 01200', 'tel' => '0356895400', 'specialitecomplementaire' => 'Pédiatre', 'departement' => 1],
            ['id' => 3, 'nom' => 'GARCIA', 'prenom' => 'Camille', 'adresse' => '58 rue du stade MESSINCOURT 08110', 'tel' => '0365489929', 'specialitecomplementaire' => null, 'departement' => 8],
            ['id' => 4, 'nom' => 'MARTINEZ', 'prenom' => 'Alice', 'adresse' => '12 rue des Pigeons JOIGNY-SUR-MEUSE 08700', 'tel' => '0319692016', 'specialitecomplementaire' => null, 'departement' => 8],
            ['id' => 5, 'nom' => 'MICHEL', 'prenom' => 'Vénus', 'adresse' => '55 rue du 14 juillet ORCIERES 05170', 'tel' => '0421670911', 'specialitecomplementaire' => null, 'departement' => 5],
            ['id' => 6, 'nom' => 'ROUX', 'prenom' => 'Anne-Lucie', 'adresse' => '49 rue des Ormes ATTILLY 02490', 'tel' => '0313817061', 'specialitecomplementaire' => null, 'departement' => 2],
            ['id' => 7, 'nom' => 'FABRE', 'prenom' => 'Marie', 'adresse' => '78 rue de Poligny YONCQ 08210', 'tel' => '0388716930', 'specialitecomplementaire' => 'HOMEOPATHIE', 'departement' => 8],
            ['id' => 8, 'nom' => 'ARNAUD', 'prenom' => 'Andrew', 'adresse' => '29 rue des Pigeons SAVINES-LE-LAC 05160', 'tel' => '0477740994', 'specialitecomplementaire' => null, 'departement' => 5],
            ['id' => 9, 'nom' => 'FERNANDEZ', 'prenom' => 'Julien', 'adresse' => '45 rue de du général Scott THIN-LE-MOUTIER 08460', 'tel' => '0321760709', 'specialitecomplementaire' => 'MEDECINE APPLIQUEE AUX SPORTS', 'departement' => 8],
            ['id' => 10, 'nom' => 'LOPEZ', 'prenom' => 'Aurèle', 'adresse' => '16 rue Alphonse Daudet ORCIERES 05170', 'tel' => '0485568083', 'specialitecomplementaire' => null, 'departement' => 5],
            ['id' => 11, 'nom' => 'BERNARD', 'prenom' => 'Johnny', 'adresse' => '74 rue de Paris SAVINES-LE-LAC 05160', 'tel' => '0417789322', 'specialitecomplementaire' => null, 'departement' => 5],
            ['id' => 12, 'nom' => 'GIRAUD', 'prenom' => 'Andrée', 'adresse' => 'ville', 'tel' => '1234567891', 'specialitecomplementaire' => 'psy', 'departement' => 5],
            ['id' => 13, 'nom' => 'BOYER', 'prenom' => 'Gilles', 'adresse' => '33 rue Commandant Hériot MONTIGNY-SUR-MEUSE 08170', 'tel' => '0334037052', 'specialitecomplementaire' => null, 'departement' => 8],
            ['id' => 14, 'nom' => 'SANCHEZ', 'prenom' => 'Vénus', 'adresse' => '14 rue de la poste BANCIGNY 02145', 'tel' => '0313832194', 'specialitecomplementaire' => 'Pédiatrie', 'departement' => 2],
            ['id' => 15, 'nom' => 'BRUN', 'prenom' => 'Cristophe', 'adresse' => '81 rue Bonaparte AUBENTON 02500', 'tel' => '0366612144', 'specialitecomplementaire' => null, 'departement' => 2],
            ['id' => 16, 'nom' => 'PEREZ', 'prenom' => 'Jules', 'adresse' => '85 rue Hector Berlioz BRIANCON 05100', 'tel' => '0459073011', 'specialitecomplementaire' => null, 'departement' => 5],
            ['id' => 17, 'nom' => 'SANTIAGO', 'prenom' => 'Julienne', 'adresse' => '17 rue Lampion AUGIREIN 09800', 'tel' => '0524077425', 'specialitecomplementaire' => null, 'departement' => 9],
            ['id' => 18, 'nom' => 'DURAND', 'prenom' => 'John', 'adresse' => '92 rue du général de Gaulle SORBIERS 05150', 'tel' => '0484328394', 'specialitecomplementaire' => 'HOMEOPATHIE', 'departement' => 5],
            ['id' => 19, 'nom' => 'RODRIGUEZ', 'prenom' => 'Cristophe', 'adresse' => '1 rue des Accacias BELLOC 09600', 'tel' => '0564847694', 'specialitecomplementaire' => null, 'departement' => 9],
            ['id' => 20, 'nom' => 'REYNAUD', 'prenom' => 'Catherine', 'adresse' => '8 rue des Accacias MONCEAU-SUR-OISE 02120', 'tel' => '0352679072', 'specialitecomplementaire' => null, 'departement' => 2],
            ['id' => 21, 'nom' => 'AUBERT', 'prenom' => 'Julien', 'adresse' => '51 rue Bonaparte CAMON 09500', 'tel' => '0552863581', 'specialitecomplementaire' => null, 'departement' => 9],
            ['id' => 22, 'nom' => 'ROSSI', 'prenom' => 'Nohan', 'adresse' => '69 rue Mallarmé AX-LES-THERMES 09110', 'tel' => '0596648531', 'specialitecomplementaire' => null, 'departement' => 9],
            ['id' => 23, 'nom' => 'BERTRAND', 'prenom' => 'Julienne', 'adresse' => '72 rue Pasteur SAVOURNON 05700', 'tel' => '0490527954', 'specialitecomplementaire' => null, 'departement' => 5],
            ['id' => 24, 'nom' => 'GAUTIER', 'prenom' => 'Irénée', 'adresse' => '28 rue Anatole France BELLEY 01300', 'tel' => '0486481045', 'specialitecomplementaire' => null, 'departement' => 1],
            ['id' => 25, 'nom' => 'BONNET', 'prenom' => 'François', 'adresse' => '78 rue des Epines AZY-SUR-MARNE 02400', 'tel' => '0318212781', 'specialitecomplementaire' => null, 'departement' => 2],
            ['id' => 26, 'nom' => 'REY', 'prenom' => 'Charles-Edouard', 'adresse' => '86 rue Louis Aragon BILLIAT 01200', 'tel' => '0446961025', 'specialitecomplementaire' => null, 'departement' => 1],
            ['id' => 27, 'nom' => 'GOMEZ', 'prenom' => 'Jérémy', 'adresse' => '89 rue de la pointe SAINT-FIRMIN 05800', 'tel' => '0438318333', 'specialitecomplementaire' => null, 'departement' => 5],
            ['id' => 28, 'nom' => 'GIRARD', 'prenom' => 'Johnny', 'adresse' => '19 rue de la Tour JOIGNY-SUR-MEUSE 08700', 'tel' => '0341883832', 'specialitecomplementaire' => 'pédiatrie', 'departement' => 8],
            ['id' => 29, 'nom' => 'JOURDAN', 'prenom' => 'Hector', 'adresse' => '29 rue de la pointe BOURG-EN-BRESSE 01000', 'tel' => '0431450970', 'specialitecomplementaire' => null, 'departement' => 1],
            ['id' => 30, 'nom' => 'ROCHE', 'prenom' => 'Mohammed', 'adresse' => '30 rue Pasteur BEZAC 09100', 'tel' => '0591677334', 'specialitecomplementaire' => 'MEDECINE APPLIQUEE AUX SPORTS', 'departement' => 9],
            ['id' => 31, 'nom' => 'FAURE', 'prenom' => 'Annie', 'adresse' => '82 rue Mallarmé SIGOYER 05130', 'tel' => '0487018886', 'specialitecomplementaire' => null, 'departement' => 5],
            ['id' => 32, 'nom' => 'ESPOSITO', 'prenom' => 'Caline', 'adresse' => '27 rue de Marigny BRIANCON 05100', 'tel' => '0444253472', 'specialitecomplementaire' => null, 'departement' => 5]
        ];
    }
}
