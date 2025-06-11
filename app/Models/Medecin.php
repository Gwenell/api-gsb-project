<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medecin extends Model
{
    use HasFactory;

    protected $table = 'medecin';
    public $timestamps = true;

    protected $fillable = [
        'nom',
        'prenom',
        'adresse',
        'cp',
        'ville',
        'telephone',
        'email',
        'specialite',
        'coef_notoriete',
        'coef_prescription',
    ];

    public function rapports()
    {
        return $this->hasMany(Rapport::class, 'idMedecin');
    }

    public function getNomCompletAttribute()
    {
        return "{$this->prenom} {$this->nom}";
    }
}
