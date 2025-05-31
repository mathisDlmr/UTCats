<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class CatDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifiant',
        'etat',
        'dans_malette',
        'lieu',
        'dernier_ping',
        'commentaires',
    ];

    protected $casts = [
        'dans_malette' => 'boolean',
        'dernier_ping' => 'date',
    ];

    /**
     * Les états possibles du terminal
     */
    public const ETATS = [
        'ok' => 'Ok',
        'moyen' => 'Moyen',
        'hs' => 'Hors Service',
    ];

    /**
     * Scope pour les terminaux actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('etat', 'ok')
                     ->orWhere('etat', 'moyen');
    }

    /**
     * Scope pour les terminaux dans la malette
     */
    public function scopeDansMalette($query)
    {
        return $query->where('dans_malette', true);
    }

    /**
     * Scope pour les terminaux avec ping récent (dernières 24h)
     */
    public function scopePingRecent($query)
    {
        return $query->where('dernier_ping', '>=', now()->subDay());
    }

    /**
     * Accesseur pour vérifier si le terminal est connecté récemment
     */
    protected function estConnecte(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->dernier_ping && $this->dernier_ping->isAfter(now()->subMinutes(30))
        );
    }

    /**
     * Accesseur pour le temps depuis dernier ping
     */
    protected function tempsDernierPing(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->dernier_ping ? $this->dernier_ping->diffForHumans() : 'Jamais'
        );
    }

    /**
     * Accesseur pour le statut formaté
     */
    protected function statutFormate(): Attribute
    {
        return Attribute::make(
            get: fn () => self::ETATS[$this->etat] ?? $this->etat
        );
    }
}