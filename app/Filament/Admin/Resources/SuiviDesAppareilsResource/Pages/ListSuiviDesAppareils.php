<?php

namespace App\Filament\Admin\Resources\SuiviDesAppareilsResource\Pages;

use App\Filament\Admin\Resources\SuiviDesAppareilsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSuiviDesAppareils extends ListRecords
{
    protected static string $resource = SuiviDesAppareilsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouveau Terminal'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'tous' => Tab::make('Tous les terminaux'),
            
            'en_fonction' => Tab::make('En fonction')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('etat', 'ok')->orWhere('etat', 'moyen'))
                ->badge(fn () => static::getResource()::getModel()::where('etat', 'ok')->orWhere('etat', 'moyen')->count()),
            
            'hs' => Tab::make('Hors Service')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('etat', 'hs'))   /* Ajouter ceux pas dans la malette ? */
                ->badge(fn () => static::getResource()::getModel()::where('etat', 'hs')->count()),   
        ];
    }
}