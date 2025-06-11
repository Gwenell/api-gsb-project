<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motif extends Model
{
    use HasFactory;

    protected $table = 'motifs';
    public $timestamps = false;

    // Motifs standardisés selon le cahier des charges GSB
    const PERIODICITE = 'PERIO';
    const NOUVEAUTES = 'NOUV';
    const REMONTAGE = 'REMON';
    const SOLLICITATION = 'SOLIC';
    const AUTRE = 'AUTRE';

    protected $fillable = [
        'id',
        'libelle',
        'description',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean'
    ];

    /**
     * Retourne tous les motifs standardisés
     */
    public static function getMotifsStandards()
    {
        return [
            self::PERIODICITE => 'Visite périodique (tous les 6-8 mois)',
            self::NOUVEAUTES => 'Nouveautés ou actualisations de produits',
            self::REMONTAGE => 'Remontage suite à baisse de prescription',
            self::SOLLICITATION => 'Sollicitation du médecin',
            self::AUTRE => 'Autre motif à préciser'
        ];
    }

    /**
     * Vérifie si un motif est valide
     */
    public static function isValidMotif($motif)
    {
        return in_array($motif, [
            self::PERIODICITE,
            self::NOUVEAUTES,
            self::REMONTAGE,
            self::SOLLICITATION,
            self::AUTRE
        ]);
    }

    public function rapports()
    {
        return $this->hasMany(Rapport::class, 'motif', 'id');
    }
}
