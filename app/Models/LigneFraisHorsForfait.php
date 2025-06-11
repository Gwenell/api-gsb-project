<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneFraisHorsForfait extends Model
{
    use HasFactory;

    protected $table = 'ligne_frais_hors_forfait';
    public $timestamps = false;

    protected $fillable = [
        'id_fiche_frais',
        'libelle',
        'date',
        'montant',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function ficheFrais()
    {
        return $this->belongsTo(FicheFrais::class, 'id_fiche_frais');
    }
} 