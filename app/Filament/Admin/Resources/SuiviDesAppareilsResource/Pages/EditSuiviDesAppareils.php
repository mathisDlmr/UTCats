<?php

namespace App\Filament\Admin\Resources\SuiviDesAppareilsResource\Pages;

use App\Filament\Admin\Resources\SuiviDesAppareilsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuiviDesAppareils extends EditRecord
{
    protected static string $resource = SuiviDesAppareilsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}