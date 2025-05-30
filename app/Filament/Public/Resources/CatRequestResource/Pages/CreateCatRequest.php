<?php

namespace App\Filament\Public\Resources\CatRequestResource\Pages;

use App\Filament\Public\Resources\CatRequestResource;
use App\Mail\CatRequestSubmitted;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateCatRequest extends CreateRecord
{
    protected static string $resource = CatRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        Mail::to('simde@assos.utc.fr')->send(new CatRequestSubmitted($this->record));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}