<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneFraisForfait extends Model
{
    use HasFactory;

    protected $table = 'ligne_frais_forfait';
    public $timestamps = false;
    protected $primaryKey = ['id_fiche_frais', 'id_frais_forfait'];
    public $incrementing = false;


    protected $fillable = [
        'id_fiche_frais',
        'id_frais_forfait',
        'quantite',
    ];

    public function ficheFrais()
    {
        return $this->belongsTo(FicheFrais::class, 'id_fiche_frais');
    }

    public function fraisForfait()
    {
        return $this->belongsTo(FraisForfait::class, 'id_frais_forfait');
    }
} 