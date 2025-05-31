<?php

namespace App\Filament\Admin\Resources\SuiviDesAppareilsResource\Pages;

use App\Filament\Admin\Resources\SuiviDesAppareilsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSuiviDesAppareils extends CreateRecord
{
    protected static string $resource = SuiviDesAppareilsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}