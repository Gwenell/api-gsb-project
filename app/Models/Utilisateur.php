<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'utilisateur';
    public $timestamps = false;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nom',
        'prenom', 
        'username',
        'mdp',
        'mdp_clair',
        'adresse',
        'cp',
        'ville',
        'dateEmbauche',
        'timespan',
        'type_utilisateur',
        'idRegion',
        'salt'
    ];

    protected $hidden = [
        'mdp',
        'mdp_clair',
        'timespan'
    ];

    protected $casts = [
        'dateEmbauche' => 'date',
        'timespan' => 'integer'
    ];

    public function getAuthPassword()
    {
        return $this->mdp;
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'idRegion');
    }

    public function rapports()
    {
        return $this->hasMany(Rapport::class, 'idVisiteur');
    }

    public function directeurRegion()
    {
        return $this->hasOne(DirecteurRegion::class, 'id');
    }

    public function presenter()
    {
        return $this->hasMany(Presenter::class, 'idVisiteur');
    }

    // Scopes pour les différents types d'utilisateurs
    public function scopeVisiteurs($query)
    {
        return $query->where('type_utilisateur', 'visiteur');
    }

    public function scopeDelegues($query)
    {
        return $query->where('type_utilisateur', 'delegue');
    }

    public function scopeResponsables($query)
    {
        return $query->where('type_utilisateur', 'responsable');
    }

    public function scopeDirecteurs($query)
    {
        return $query->where('type_utilisateur', 'directeur');
    }

    public function scopeComptables($query)
    {
        return $query->where('type_utilisateur', 'comptable');
    }
}
