<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $table = 'region';
    public $timestamps = false;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nom'
    ];

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'idRegion');
    }

    public function directeurRegion()
    {
        return $this->hasOne(DirecteurRegion::class, 'idRegion');
    }
}
