<?php

namespace App\Filament\Admin\Resources\CatSaleResource\Pages;

use App\Filament\Admin\Resources\CatSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCatSale extends EditRecord
{
    protected static string $resource = CatSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
