<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CatSaleResource\Pages;
use App\Models\CatSale;
use App\Models\CatDevice;
use App\Models\TpeDevice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;

class CatSaleResource extends Resource
{
    protected static ?string $model = CatSale::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $modelLabel = 'Gestion demande CAT';
    protected static ?string $pluralModelLabel = 'Gestion demandes CAT';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la vente')
                    ->schema([
                        Forms\Components\Select::make('cat_request_id')
                            ->label('Demande associée')
                            ->relationship('catRequest', 'event_name')
                            ->searchable()
                            ->preload()
                            ->disabled(),
                        
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'configured' => 'Configurée',
                                'devices_assigned' => 'Appareils assignés',
                                'retrieved' => 'Matériel récupéré',
                                'returned' => 'Matériel rendu',
                            ])
                            ->required()
                            ->reactive(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Attribution des appareils')
                    ->schema([
                        Forms\Components\Select::make('cat_devices')
                            ->label('CATs assignés')
                            ->multiple()
                            ->relationship('catDevices', 'identifiant')
                            ->options(function () {
                                return CatDevice::where('etat', 'ok')
                                    ->whereDoesntHave('sales', function ($query) {
                                        $query->whereIn('status', ['devices_assigned', 'retrieved']);
                                    })
                                    ->pluck('identifiant', 'identifiant');
                            })
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('tpe_devices')
                            ->label('TPE assignés')
                            ->multiple()
                            ->relationship('tpeDevices', 'identifiant')
                            ->options(function () {
                                return TpeDevice::where('etat', 'ok')
                                    ->where('disponible', true)
                                    ->whereDoesntHave('sales', function ($query) {
                                        $query->whereIn('status', ['devices_assigned', 'retrieved']);
                                    })
                                    ->pluck('identifiant', 'identifiant');
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->visible(fn ($get) => in_array($get('status'), ['configured', 'devices_assigned', 'retrieved', 'returned'])),

                Forms\Components\Section::make('Retrait du matériel')
                    ->schema([
                        Forms\Components\TextInput::make('bde_member_pickup')
                            ->label('Membre BDE (remise)')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('receiver_pickup')
                            ->label('Personne qui récupère')
                            ->maxLength(255),
                        
                        Forms\Components\Toggle::make('caution_collected')
                            ->label('Caution récupérée')
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('caution_amount')
                            ->label('Montant de la caution (€)')
                            ->numeric()
                            ->visible(fn ($get) => $get('caution_collected')),
                        
                        Forms\Components\DateTimePicker::make('pickup_at')
                            ->label('Date/heure de retrait'),
                    ])
                    ->columns(2)
                    ->visible(fn ($get) => in_array($get('status'), ['retrieved', 'returned'])),

                Forms\Components\Section::make('Retour du matériel')
                    ->schema([
                        Forms\Components\TextInput::make('bde_member_return')
                            ->label('Membre BDE (récupération)')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('returner')
                            ->label('Personne qui rend')
                            ->maxLength(255),
                        
                        Forms\Components\Toggle::make('caution_returned')
                            ->label('Caution rendue'),
                        
                        Forms\Components\DateTimePicker::make('returned_at')
                            ->label('Date/heure de retour'),
                    ])
                    ->columns(2)
                    ->visible(fn ($get) => $get('status') === 'returned'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(1)
                    ->schema([
                        TextEntry::make('title_display')
                            ->label('')
                            ->formatStateUsing(function ($record) {
                                $status = match($record->status) {
                                    'configured' => 'CONFIGURÉE',
                                    'devices_assigned' => 'APPAREILS ASSIGNÉS',
                                    'retrieved' => 'MATÉRIEL RÉCUPÉRÉ',
                                    'returned' => 'MATÉRIEL RENDU',
                                };
                                return $record->catRequest->asso . ' - ' . $record->catRequest->event_name . ' (' . $status . ')';
                            })
                            ->size('4xl')
                            ->weight('bold')
                            ->alignCenter()
                            ->extraAttributes(['style' => 'font-size:2rem;']),
                    ])
                    ->columnSpanFull(),

                Grid::make(2)->schema([
                    Section::make('Informations de la demande')
                        ->schema([
                            TextEntry::make('catRequest.user.email')
                                ->label('Demandeur'),
                            TextEntry::make('catRequest.start_date')
                                ->label('Date de début')
                                ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('d/m/Y')),
                            TextEntry::make('catRequest.end_date')
                                ->label('Date de fin')
                                ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('d/m/Y')),
                            TextEntry::make('catRequest.cats_count')
                                ->label('CATs demandés'),
                            TextEntry::make('catRequest.tpe_count')
                                ->label('TPE demandés'),
                        ]),

                    Section::make('Caution')
                        ->schema([
                            TextEntry::make('total_caution')
                                ->label('Caution totale')
                                ->formatStateUsing(fn ($state) => $state . ' €'),
                            TextEntry::make('caution_collected')
                                ->label('Caution récupérée')
                                ->formatStateUsing(fn ($state) => $state ? 'Oui' : 'Non')
                                ->color(fn ($state) => $state ? 'success' : 'warning'),
                            TextEntry::make('caution_amount')
                                ->label('Montant récupéré')
                                ->formatStateUsing(fn ($state) => $state ? $state . ' €' : 'N/A'),
                            TextEntry::make('caution_returned')
                                ->label('Caution rendue')
                                ->formatStateUsing(fn ($state) => $state ? 'Oui' : 'Non')
                                ->color(fn ($state) => $state ? 'success' : 'warning'),
                        ]),
                ]),

                Section::make('Appareils assignés')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('catRequest')
                                ->label('CATs assignés')
                                ->formatStateUsing(function ($record) {
                                    return $record->catDevices->pluck('identifiant')->join(', ') ?: 'Aucun';
                                }),
                            TextEntry::make('catRequest')
                                ->label('TPE assignés')
                                ->formatStateUsing(function ($record) {
                                    return $record->tpeDevices->pluck('identifiant')->join(', ') ?: 'Aucun';
                                }),
                        ]),
                    ])
                    ->visible(fn ($record) => $record->catDevices->count() > 0 || $record->tpeDevices->count() > 0),

                Section::make('Retrait du matériel')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('bde_member_pickup')
                                ->label('Membre BDE (remise)')
                                ->formatStateUsing(fn ($state) => $state ?: 'Non renseigné'),
                            TextEntry::make('receiver_pickup')
                                ->label('Personne qui récupère')
                                ->formatStateUsing(fn ($state) => $state ?: 'Non renseigné'),
                        ]),
                        TextEntry::make('pickup_at')
                            ->label('Date/heure de retrait')
                            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i') : 'Non renseigné'),
                    ])
                    ->visible(fn ($record) => in_array($record->status, ['retrieved', 'returned'])),

                Section::make('Retour du matériel')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('bde_member_return')
                                ->label('Membre BDE (récupération)')
                                ->formatStateUsing(fn ($state) => $state ?: 'Non renseigné'),
                            TextEntry::make('returner')
                                ->label('Personne qui rend')
                                ->formatStateUsing(fn ($state) => $state ?: 'Non renseigné'),
                        ]),
                        TextEntry::make('returned_at')
                            ->label('Date/heure de retour')
                            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i') : 'Non renseigné'),
                    ])
                    ->visible(fn ($record) => $record->status === 'returned'),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('')
                            ->formatStateUsing(fn ($state) => $state ?: 'Aucune note')
                    ])
                    ->visible(fn ($record) => !empty($record->notes))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('catRequest.user')
                    ->formatStateUsing(fn($state) => $state->firstName . ' ' . $state->lastName)
                    ->label('Demandeur.euse')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('catRequest.asso')
                    ->label('Association')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('catRequest.event_name')
                    ->label('Événement')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('catRequest.start_date')
                    ->label('Début')
                    ->formatStateUsing(fn($state) => ucfirst(\Carbon\Carbon::parse($state)->locale('fr')->translatedFormat('l d F Y')))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('catRequest')
                    ->label('Appareils')
                    ->formatStateUsing(function ($record) {
                        $cats = $record->catDevices->count();
                        $tpes = $record->tpeDevices->count();
                        return "{$cats} CATs, {$tpes} TPE";
                    }),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'none' => 'À faire',
                        'configured' => 'Vente configurée',
                        'devices_assigned' => 'Appareils configurés',
                        'retrieved' => 'Matériel récupéré',
                        'returned' => 'Matériel rendu',
                    })
                    ->colors([
                        'gray' => 'none',
                        'danger' => 'configured',
                        'warning' => 'devices_assigned',
                        'primary' => 'retrieved',
                        'success' => 'returned',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'none' => 'À faire',
                        'configured' => 'Vente configurée',
                        'devices_assigned' => 'Appareils configurés',
                        'retrieved' => 'Matériel récupéré',
                        'returned' => 'Matériel rendu',
                    ]),
                
                Tables\Filters\Filter::make('current_events')
                    ->label('Événements en cours')
                    ->query(function ($query) {
                        return $query->whereHas('catRequest', function ($q) {
                            $q->whereDate('start_date', '<=', now())
                              ->whereDate('end_date', '>=', now());
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\Action::make('assign_devices')
                    ->label('Assigner appareils')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'configured')
                    ->form([
                        Forms\Components\Select::make('cat_devices')
                            ->label('CATs à assigner')
                            ->multiple()
                            //->maxItems(fn($state) => $record->catRequest->cats_count)
                            ->options(function ($record) {
                                return CatDevice::where('etat', 'ok')
                                    ->whereDoesntHave('sales', function ($query) {
                                        $query->whereIn('status', ['devices_assigned', 'retrieved']);
                                    })
                                    ->pluck('identifiant', 'identifiant');
                            })
                            ->required()
                            ->helperText(fn ($record) => "Il faut assigner {$record->catRequest->cats_count} CATs"),
                        
                        Forms\Components\Select::make('tpe_devices')
                            ->label('TPE à assigner')
                            ->multiple()
                            ->options(function () {
                                return TpeDevice::where('etat', 'ok')
                                    ->where('disponible', true)
                                    ->whereDoesntHave('sales', function ($query) {
                                        $query->whereIn('status', ['devices_assigned', 'retrieved']);
                                    })
                                    ->pluck('identifiant', 'identifiant');
                            })
                            ->helperText(fn ($record) => "Il faut assigner {$record->catRequest->tpe_count} TPE"),
                    ])
                    ->action(function ($record, array $data) {
                        if (!empty($data['cat_devices'])) {
                            $record->catDevices()->sync($data['cat_devices']);
                        }
                        
                        if (!empty($data['tpe_devices'])) {
                            $record->tpeDevices()->sync($data['tpe_devices']);
                        }
                        
                        $record->update(['status' => 'devices_assigned']);
                        
                        Notification::make()
                            ->title('Appareils assignés avec succès')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('mark_retrieved')
                    ->label('Marquer comme récupéré')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->visible(fn ($record) => $record->status === 'devices_assigned')
                    ->form([
                        Forms\Components\TextInput::make('bde_member_pickup')
                            ->label('Membre BDE qui remet le matériel')
                            ->required(),
                        
                        Forms\Components\TextInput::make('receiver_pickup')
                            ->label('Personne qui récupère')
                            ->required(),
                        
                        Forms\Components\Toggle::make('caution_collected')
                            ->label('Caution récupérée')
                            ->default(true)
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('caution_amount')
                            ->label('Montant de la caution (€)')
                            ->numeric()
                            ->default(fn ($record) => $record->total_caution)
                            ->visible(fn ($get) => $get('caution_collected')),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'retrieved',
                            'bde_member_pickup' => $data['bde_member_pickup'],
                            'receiver_pickup' => $data['receiver_pickup'],
                            'caution_collected' => $data['caution_collected'],
                            'caution_amount' => $data['caution_amount'] ?? null,
                            'pickup_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Matériel marqué comme récupéré')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('mark_returned')
                    ->label('Marquer comme rendu')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'retrieved')
                    ->form([
                        Forms\Components\TextInput::make('bde_member_return')
                            ->label('Membre BDE qui récupère')
                            ->required(),
                        
                        Forms\Components\TextInput::make('returner')
                            ->label('Personne qui rend')
                            ->required(),
                        
                        Forms\Components\Toggle::make('caution_returned')
                            ->label('Caution rendue')
                            ->default(true),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'returned',
                            'bde_member_return' => $data['bde_member_return'],
                            'returner' => $data['returner'],
                            'caution_returned' => $data['caution_returned'],
                            'returned_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Matériel marqué comme rendu')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCatSales::route('/'),
            'view' => Pages\ViewCatSale::route('/{record}'),
            'edit' => Pages\EditCatSale::route('/{record}/edit'),
        ];
    }
}