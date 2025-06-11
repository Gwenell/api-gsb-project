<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FicheFrais extends Model
{
    use HasFactory;

    protected $table = 'fiche_frais';
    public $timestamps = false;

    protected $fillable = [
        'id_utilisateur',
        'mois',
        'nb_justificatifs',
        'montant_valide',
        'date_modif',
        'id_etat',
    ];

    protected $casts = [
        'date_modif' => 'date',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function etat()
    {
        return $this->belongsTo(Etat::class, 'id_etat');
    }

    public function lignesFraisForfait()
    {
        return $this->hasMany(LigneFraisForfait::class, 'id_fiche_frais');
    }

    public function lignesFraisHorsForfait()
    {
        return $this->hasMany(LigneFraisHorsForfait::class, 'id_fiche_frais');
    }
} 