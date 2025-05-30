<?php

namespace App\Filament\Public\Resources\CatRequestResource\Pages;

use App\Filament\Public\Resources\CatRequestResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCatRequest extends ViewRecord
{
    protected static string $resource = CatRequestResource::class;

    public function getTitle(): string
    {
        return 'Détail de la demande';
    }
}