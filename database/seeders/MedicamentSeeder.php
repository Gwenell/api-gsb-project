<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Medicament;
use App\Models\Famille;

class MedicamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les familles de médicaments
        $familles = [
            ['id' => 'AA', 'libelle' => 'Anti-inflammatoires'],
            ['id' => 'AB', 'libelle' => 'Antibiotiques'],
            ['id' => 'AC', 'libelle' => 'Antalgiques'],
            ['id' => 'AD', 'libelle' => 'Antidépresseurs'],
            ['id' => 'AE', 'libelle' => 'Anti-hypertenseurs'],
            ['id' => 'AF', 'libelle' => 'Anticoagulants']
        ];

        foreach ($familles as $famille) {
            Famille::updateOrCreate(['id' => $famille['id']], $famille);
        }

        // Créer les médicaments
        $medicaments = [
            [
                'id' => 'Med001',
                'nomCommercial' => 'ADVIL',
                'idFamille' => 'AA',
                'composition' => 'Ibuprofène 400mg',
                'effets' => 'Anti-inflammatoire et antalgique',
                'contreIndications' => 'Allergie aux AINS, ulcère gastroduodénal',
                'niveau_dangerosité' => 2,
                'date_sortie' => '2020-01-15'
            ],
            [
                'id' => 'Med002',
                'nomCommercial' => 'DOLIPRANE',
                'idFamille' => 'AC',
                'composition' => 'Paracétamol 1000mg',
                'effets' => 'Antalgique et antipyrétique',
                'contreIndications' => 'Insuffisance hépatique sévère',
                'niveau_dangerosité' => 1,
                'date_sortie' => '2019-06-20'
            ],
            [
                'id' => 'Med003',
                'nomCommercial' => 'AMOXICILLINE',
                'idFamille' => 'AB',
                'composition' => 'Amoxicilline 1g',
                'effets' => 'Antibiotique à large spectre',
                'contreIndications' => 'Allergie aux pénicillines',
                'niveau_dangerosité' => 3,
                'date_sortie' => '2021-03-10'
            ],
            [
                'id' => 'Med004',
                'nomCommercial' => 'PROZAC',
                'idFamille' => 'AD',
                'composition' => 'Fluoxétine 20mg',
                'effets' => 'Antidépresseur inhibiteur sélectif de la recapture de la sérotonine',
                'contreIndications' => 'Association aux IMAO, grossesse',
                'niveau_dangerosité' => 4,
                'date_sortie' => '2022-09-05'
            ],
            [
                'id' => 'Med005',
                'nomCommercial' => 'TENORMINE',
                'idFamille' => 'AE',
                'composition' => 'Aténolol 100mg',
                'effets' => 'Bêta-bloquant cardiosélectif',
                'contreIndications' => 'Asthme, bloc AV, bradycardie',
                'niveau_dangerosité' => 3,
                'date_sortie' => '2020-11-30'
            ],
            [
                'id' => 'Med006',
                'nomCommercial' => 'SINTROM',
                'idFamille' => 'AF',
                'composition' => 'Acénocoumarol 4mg',
                'effets' => 'Anticoagulant antivitamine K',
                'contreIndications' => 'Hémorragie active, grossesse',
                'niveau_dangerosité' => 5,
                'date_sortie' => '2021-07-12'
            ],
            [
                'id' => 'Med007',
                'nomCommercial' => 'ASPIRINE',
                'idFamille' => 'AC',
                'composition' => 'Acide acétylsalicylique 500mg',
                'effets' => 'Antalgique, antipyrétique et anti-inflammatoire',
                'contreIndications' => 'Allergie aux salicylés, enfant de moins de 16 ans',
                'niveau_dangerosité' => 2,
                'date_sortie' => '2023-01-20'
            ],
            [
                'id' => 'Med008',
                'nomCommercial' => 'AUGMENTIN',
                'idFamille' => 'AB',
                'composition' => 'Amoxicilline 875mg + Acide clavulanique 125mg',
                'effets' => 'Antibiotique à spectre élargi',
                'contreIndications' => 'Allergie aux pénicillines, antécédent de jaunisse',
                'niveau_dangerosité' => 3,
                'date_sortie' => '2023-04-15'
            ]
        ];

        foreach ($medicaments as $medicament) {
            Medicament::updateOrCreate(['id' => $medicament['id']], $medicament);
        }

        $this->command->info('Familles et médicaments créés avec succès !');
    }
}
