<?php

// app/Models/CatSale.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'cat_request_id',
        'status',
        'notes',
        'bde_member_pickup',
        'receiver_pickup',
        'caution_collected',
        'caution_amount',
        'pickup_at',
        'bde_member_return',
        'returner',
        'caution_returned',
        'returned_at',
    ];

    protected $casts = [
        'caution_collected' => 'boolean',
        'caution_returned' => 'boolean',
        'pickup_at' => 'datetime',
        'returned_at' => 'datetime',
        'caution_amount' => 'decimal:2',
    ];

    public function catRequest()
    {
        return $this->belongsTo(CatRequest::class);
    }

    public function catDevices()
    {
        return $this->belongsToMany(CatDevice::class, 'cat_sale_devices', 'cat_sale_id', 'cat_device_identifiant', 'id', 'identifiant');
    }

    public function tpeDevices()
    {
        return $this->belongsToMany(TpeDevice::class, 'tpe_sale_devices', 'cat_sale_id', 'tpe_device_identifiant', 'id', 'identifiant');
    }

    public function getTotalCautionAttribute()
    {
        $catCount = $this->catDevices()->count();
        $tpeCount = $this->tpeDevices()->count();
        $connexion = $this->catRequest->connexion === '4g' ? 150: 0;
        return ($catCount * 200) + ($tpeCount * 50) + $connexion;
    }
}