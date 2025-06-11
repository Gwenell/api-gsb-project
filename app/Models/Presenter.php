<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presenter extends Model
{
    use HasFactory;

    protected $table = 'presenter';
    public $timestamps = false;

    protected $fillable = [
        'idVisiteur',
        'idMedicament',
        'annee_mois'
    ];

    public function visiteur()
    {
        return $this->belongsTo(Utilisateur::class, 'idVisiteur');
    }

    public function medicament()
    {
        return $this->belongsTo(Medicament::class, 'idMedicament');
    }

    // Composite primary key
    protected $primaryKey = ['idVisiteur', 'idMedicament', 'annee_mois'];
    public $incrementing = false;
}
