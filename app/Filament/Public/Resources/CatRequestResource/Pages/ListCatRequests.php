<?php

namespace App\Filament\Public\Resources\CatRequestResource\Pages;

use App\Filament\Public\Resources\CatRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCatRequests extends ListRecords
{
    protected static string $resource = CatRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
