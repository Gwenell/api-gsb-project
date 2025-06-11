<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicament extends Model
{
    use HasFactory;

    protected $table = 'medicament';
    public $timestamps = false;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nomCommercial',
        'idFamille',
        'composition',
        'effets',
        'contreIndications',
        'image_url',
        'description',
        'niveau_dangerosité',
        'date_sortie'
    ];

    protected $casts = [
        'niveau_dangerosité' => 'integer',
        'date_sortie' => 'date'
    ];

    public function famille()
    {
        return $this->belongsTo(Famille::class, 'idFamille');
    }

    public function offrir()
    {
        return $this->hasMany(Offrir::class, 'idMedicament');
    }

    public function presenter()
    {
        return $this->hasMany(Presenter::class, 'idMedicament');
    }

    public function rapports()
    {
        return $this->belongsToMany(Rapport::class, 'offrir', 'idMedicament', 'idRapport')
                    ->withPivot('quantite');
    }

    public function visiteurs()
    {
        return $this->belongsToMany(Utilisateur::class, 'presenter', 'idMedicament', 'idVisiteur')
                    ->withPivot('annee_mois');
    }
}
