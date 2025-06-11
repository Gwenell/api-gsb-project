<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offrir extends Model
{
    use HasFactory;

    protected $table = 'offrir';
    public $timestamps = false;

    protected $fillable = [
        'idRapport',
        'idMedicament',
        'quantite'
    ];

    protected $casts = [
        'quantite' => 'integer'
    ];

    public function rapport()
    {
        return $this->belongsTo(Rapport::class, 'idRapport');
    }

    public function medicament()
    {
        return $this->belongsTo(Medicament::class, 'idMedicament');
    }

    // Composite primary key
    protected $primaryKey = ['idRapport', 'idMedicament'];
    public $incrementing = false;
}
