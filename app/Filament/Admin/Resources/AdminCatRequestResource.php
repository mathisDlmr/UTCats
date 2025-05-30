<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AdminCatRequestResource\Pages;
use App\Models\CatRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class AdminCatRequestResource extends Resource
{
    protected static ?string $model = CatRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $modelLabel = 'Demande CAT';
    protected static ?string $pluralModelLabel = 'Demandes CAT';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asso.name')
                    ->formatStateUsing(fn ($state) => ucfirst($state) ?? 'Inconnu')
                    ->label('Asso')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user')
                    ->formatStateUsing(fn ($state) => $state->firstName.' '.$state->lastName ?? 'Inconnu')
                    ->label('Demandeur.euse')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('cats_count')
                    ->label('Nb CATs')
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'accepted' => 'Accepté',
                        'rejected' => 'Refusé',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('accept')
                    ->label('Accepter')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (CatRequest $record) {
                        $record->update(['status' => 'accepted']);
                        Notification::make()
                            ->title('Demande acceptée')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
                
                Tables\Actions\Action::make('reject')
                    ->label('Refuser')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(function (CatRequest $record) {
                        $record->update(['status' => 'rejected']);
                        Notification::make()
                            ->title('Demande refusée')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminCatRequests::route('/'),
        ];
    }
}