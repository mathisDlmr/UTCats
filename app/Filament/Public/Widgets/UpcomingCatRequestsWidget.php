<?php

namespace App\Filament\Public\Widgets;

use App\Models\CatRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingCatRequestsWidget extends BaseWidget
{
    protected static ?string $heading = 'Mes prochaines demandes';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CatRequest::query()
                    ->where('user_id', auth()->id())
                    ->where('end_date', '>=', now())
                    ->orderBy('start_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('asso')
                    ->label('Asso')
                    ->formatStateUsing(fn($state) => ucfirst($state)),
                
                Tables\Columns\TextColumn::make('event_name')
                    ->label('Evenement'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Début')
                    ->formatStateUsing(fn($state) => $state ? ucfirst(\Carbon\Carbon::parse($state)->locale('fr')->translatedFormat('l d F Y')) : ''),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->formatStateUsing(fn($state) => $state ? ucfirst(\Carbon\Carbon::parse($state)->locale('fr')->translatedFormat('l d F Y')) : ''),
                
                Tables\Columns\TextColumn::make('cats_count')
                    ->label('Nb CATs')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('tpe_count')
                    ->label('Nb TPE')
                    ->alignCenter(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'En attente',
                        'accepted' => 'Accepté',
                        'rejected' => 'Refusé',
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                    ]),
            ]);
    }
}