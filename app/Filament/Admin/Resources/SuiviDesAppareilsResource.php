<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SuiviDesAppareilsResource\Pages;
use App\Models\CatDevice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Support\Enums\FontWeight;

class SuiviDesAppareilsResource extends Resource
{
    protected static ?string $model = CatDevice::class;
    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'Suivi des Appareils';
    protected static ?string $modelLabel = 'Suivi des Appareils';
    protected static ?string $pluralModelLabel = 'Suivi des Appareils';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du Terminal')
                    ->schema([
                        Forms\Components\TextInput::make('identifiant')
                            ->label('Identifiant')
                            ->minLength(4)
                            ->maxLength(4)
                            ->placeholder('Ex: 1210')
                            ->helperText('Identifiant lisible après 80405# en bas du CAT'),

                        Forms\Components\Select::make('etat')
                            ->label('État')
                            ->required()
                            ->options(CatDevice::ETATS)
                            ->default('ok')
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Localisation et Statut')
                    ->schema([
                        Forms\Components\Toggle::make('dans_malette')
                            ->label('Dans la malette ?')
                            ->helperText('Le terminal est-il présent dans la malette ?')
                            ->default(false),

                        Forms\Components\DatePicker::make('dernier_ping')
                            ->label('Dernier ping')
                            ->helperText('Date et heure du dernier ping reçu')
                            ->seconds(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Observations')
                    ->schema([
                        Forms\Components\Textarea::make('commentaires')
                            ->label('Commentaires')
                            ->rows(3)
                            ->placeholder('Observations, problèmes rencontrés, etc.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('identifiant')
                    ->label('Identifiant')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Non défini'),

                Tables\Columns\BadgeColumn::make('etat')
                    ->label('État')
                    ->colors([
                        'success' => 'ok',
                        'warning' => 'moyen',
                        'danger' => 'hs',
                    ])
                    ->formatStateUsing(fn (string $state): string => CatDevice::ETATS[$state] ?? $state),

                Tables\Columns\IconColumn::make('dans_malette')
                    ->label('Malette')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Dernière màj')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->placeholder('Jamais')
                    ->since(),

                Tables\Columns\TextColumn::make('dernier_ping')
                    ->label('Dernier ping')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->placeholder('Jamais')
                    ->since()
                    ->color(fn ($state) => $state && $state->isAfter(now()->subHour()) ? 'success' : 'warning'),
            ])
            ->filters([
                SelectFilter::make('etat')
                    ->label('État')
                    ->options(CatDevice::ETATS)
                    ->native(false),

                TernaryFilter::make('dans_malette')
                    ->label('Dans la malette'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('marquer_malette')
                        ->label('Marquer "Dans malette"')
                        ->icon('heroicon-o-briefcase')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['dans_malette' => true]))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('retirer_malette')
                        ->label('Retirer de la malette')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['dans_malette' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informations du Terminal')
                    ->schema([
                        TextEntry::make('identifiant')
                            ->label('Identifiant')
                            ->placeholder('Non défini'),
                        
                        TextEntry::make('etat')
                            ->label('État')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'actif' => 'success',
                                'maintenance' => 'warning',
                                'defaillant' => 'danger',
                                'inactif' => 'secondary',
                                default => 'primary',
                            })
                            ->formatStateUsing(fn (string $state): string => CatDevice::ETATS[$state] ?? $state),
                    ])
                    ->columns(3),

                Section::make('Localisation et Statut')
                    ->schema([
                        TextEntry::make('dans_malette')
                            ->label('Dans la malette')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Oui' : 'Non')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                        
                        TextEntry::make('updated_at')
                            ->label("Dernière màj de l'info")
                            ->since()
                            ->placeholder('Non spécifié'),
                        
                        TextEntry::make('dernier_ping')
                            ->label('Dernier ping')
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('Jamais')
                            ->since(),
                    ])
                    ->columns(3),

                Section::make('Observations')
                    ->schema([
                        TextEntry::make('commentaires')
                            ->label('Commentaires')
                            ->placeholder('Aucun commentaire')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuiviDesAppareils::route('/'),
            'create' => Pages\CreateSuiviDesAppareils::route('/create'),
            'view' => Pages\ViewSuiviDesAppareils::route('/{record}'),
            'edit' => Pages\EditSuiviDesAppareils::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('etat', 'ok')->orWhere('etat', 'moyen')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}