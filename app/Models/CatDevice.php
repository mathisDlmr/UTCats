<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class CatDevice extends Model
{
    use HasFactory;

    protected $primaryKey = 'identifiant';
    public $incrementing = false;
    protected $keyType = 'string';

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
     * Accesseur pour le statut formaté
     */
    protected function statutFormate(): Attribute
    {
        return Attribute::make(
            get: fn () => self::ETATS[$this->etat] ?? $this->etat
        );
    }

    public function sales()
    {
        return $this->belongsToMany(CatSale::class, 'cat_sale_devices', 'cat_device_identifiant', 'cat_sale_id', 'identifiant', 'id');
    }

    public function isAvailable()
    {
        return ($this->etat === 'ok' || $this->etat === 'moyen') && !$this->sales()->whereIn('status', ['devices_assigned', 'retrieved'])->exists();
    }
}