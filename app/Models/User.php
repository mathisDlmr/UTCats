<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use App\Enums\Roles;

class User extends Authenticatable implements HasName
{
    use HasFactory;

    protected $fillable = ['email', 'firstName', 'lastName', 'assos'];

    public function getFilamentName(): string
    {
        return ($this->firstName." ".$this->lastName);
    }
}
