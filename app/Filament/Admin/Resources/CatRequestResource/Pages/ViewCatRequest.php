<?php


namespace App\Filament\Admin\Resources\CatRequestResource\Pages;

use App\Filament\Admin\Resources\CatRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCatRequest extends ViewRecord
{
    protected static string $resource = CatRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}