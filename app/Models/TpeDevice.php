<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TpeDevice extends Model
{
    use HasFactory;

    protected $primaryKey = 'identifiant';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'identifiant',
        'etat',
        'disponible',
        'lieu',
        'commentaires',
    ];

    protected $casts = [
        'disponible' => 'boolean',
    ];

    public function sales()
    {
        return $this->belongsToMany(CatSale::class, 'tpe_sale_devices', 'tpe_device_identifiant', 'cat_sale_id', 'identifiant', 'id');
    }

    public function isAvailable()
    {
        return $this->etat === 'ok' && $this->disponible && !$this->sales()->whereIn('status', ['devices_assigned', 'retrieved'])->exists();
    }
}