<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asso',
        'event_name',
        'start_date',
        'end_date',
        'cats_count',
        'tpe_count',
        'lieu',
        'lieu_autre',
        'connexion',
        'status',
        'ready',
        'responsibles',
        'articles',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'responsibles' => 'array',
        'articles' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'accepted' => 'Accepté',
            'rejected' => 'Refusé',
        };
    }

    public function catSale()
    {
        return $this->hasOne(CatSale::class);
    }
}