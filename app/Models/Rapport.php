<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    use HasFactory;

    protected $table = 'rapport';
    public $timestamps = false;

    // États des rapports selon les spécifications GSB
    const ETAT_EN_COURS = 'CR';      // Compte-rendu en cours
    const ETAT_VALIDE = 'VA';        // Validé par le délégué
    const ETAT_REMBOURSE = 'RB';     // Remboursé

    // Coefficients de confiance (1-5)
    const COEFFICIENT_MIN = 1;
    const COEFFICIENT_MAX = 5;

    protected $fillable = [
        'date',
        'motif',
        'bilan',
        'idVisiteur',
        'idMedecin',
        'etat',
        'dateModif',
        'coefficientConfiance',
        'medecinVisite',
        'isRemplacant',
        'dateSaisie',
        'motifAutre',
        'produitsPresentees',
        'nbJustificatifs',
        'totalValide'
    ];

    protected $casts = [
        'date' => 'date',
        'dateModif' => 'datetime',
        'dateSaisie' => 'datetime',
        'isRemplacant' => 'boolean',
        'coefficientConfiance' => 'decimal:1',
        'nbJustificatifs' => 'integer',
        'totalValide' => 'decimal:2'
    ];

    /**
     * Retourne tous les états possibles avec leurs libellés
     */
    public static function getEtats()
    {
        return [
            self::ETAT_EN_COURS => 'En cours',
            self::ETAT_VALIDE => 'Validé',
            self::ETAT_REMBOURSE => 'Remboursé'
        ];
    }

    /**
     * Vérifie si un état est valide
     */
    public static function isValidEtat($etat)
    {
        return in_array($etat, [
            self::ETAT_EN_COURS,
            self::ETAT_VALIDE,
            self::ETAT_REMBOURSE
        ]);
    }

    /**
     * Vérifie si le coefficient de confiance est valide
     */
    public static function isValidCoefficient($coefficient)
    {
        return $coefficient >= self::COEFFICIENT_MIN && $coefficient <= self::COEFFICIENT_MAX;
    }

    /**
     * Retourne le libellé de l'état
     */
    public function getEtatLibelleAttribute()
    {
        $etats = self::getEtats();
        return $etats[$this->etat] ?? 'Inconnu';
    }

    /**
     * Scope pour les rapports à valider
     */
    public function scopeAValider($query)
    {
        return $query->where('etat', self::ETAT_EN_COURS);
    }

    /**
     * Scope pour les rapports validés
     */
    public function scopeValides($query)
    {
        return $query->where('etat', self::ETAT_VALIDE);
    }

    /**
     * Scope pour les rapports remboursés
     */
    public function scopeRembourses($query)
    {
        return $query->where('etat', self::ETAT_REMBOURSE);
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopePourPeriode($query, $anneeOuMois)
    {
        if (strlen($anneeOuMois) === 4) {
            // Année complète (YYYY)
            return $query->whereYear('date', $anneeOuMois);
        } else {
            // Année-mois (YYYY-MM)
            [$annee, $mois] = explode('-', $anneeOuMois);
            return $query->whereYear('date', $annee)
                         ->whereMonth('date', $mois);
        }
    }

    /**
     * Valide le rapport
     */
    public function valider($nbJustificatifs = null, $totalValide = null)
    {
        $this->etat = self::ETAT_VALIDE;
        $this->dateModif = now();
        
        if ($nbJustificatifs !== null) {
            $this->nbJustificatifs = $nbJustificatifs;
        }
        
        if ($totalValide !== null) {
            $this->totalValide = $totalValide;
        }
        
        return $this->save();
    }

    /**
     * Rembourse le rapport
     */
    public function rembourser()
    {
        $this->etat = self::ETAT_REMBOURSE;
        $this->dateModif = now();
        return $this->save();
    }

    public function visiteur()
    {
        return $this->belongsTo(Utilisateur::class, 'idVisiteur');
    }

    public function medecin()
    {
        return $this->belongsTo(Medecin::class, 'idMedecin');
    }

    public function motifDetail()
    {
        return $this->belongsTo(Motif::class, 'motif', 'id');
    }

    public function medicamentsOfferts()
    {
        return $this->hasMany(Offrir::class, 'idRapport');
    }

    public function medicaments()
    {
        return $this->belongsToMany(Medicament::class, 'offrir', 'idRapport', 'idMedicament')
                    ->withPivot('quantite');
    }

    /**
     * Vérifie les contraintes métier avant sauvegarde
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($rapport) {
            // Vérification du coefficient de confiance
            if ($rapport->coefficientConfiance && !self::isValidCoefficient($rapport->coefficientConfiance)) {
                throw new \InvalidArgumentException("Le coefficient de confiance doit être entre " . self::COEFFICIENT_MIN . " et " . self::COEFFICIENT_MAX);
            }

            // Vérification de l'état
            if ($rapport->etat && !self::isValidEtat($rapport->etat)) {
                throw new \InvalidArgumentException("État invalide : " . $rapport->etat);
            }

            // Date de saisie automatique si pas définie
            if (!$rapport->dateSaisie && $rapport->isDirty()) {
                $rapport->dateSaisie = now();
            }

            // Date de modification automatique
            if ($rapport->exists && $rapport->isDirty()) {
                $rapport->dateModif = now();
            }
        });
    }
}
