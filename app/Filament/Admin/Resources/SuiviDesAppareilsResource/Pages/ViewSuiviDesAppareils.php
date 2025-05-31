<?php


namespace App\Filament\Admin\Resources\SuiviDesAppareilsResource\Pages;

use App\Filament\Admin\Resources\SuiviDesAppareilsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSuiviDesAppareils extends ViewRecord
{
    protected static string $resource = SuiviDesAppareilsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}