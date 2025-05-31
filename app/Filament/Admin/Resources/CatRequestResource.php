<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CatRequestResource\Pages;
use App\Models\CatSale;
use App\Models\CatDevice;
use App\Models\TpeDevice;
use App\Models\CatRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Notifications\Notification;

class CatRequestResource extends Resource
{
    protected static ?string $model = CatRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $modelLabel = 'Demande CAT';
    protected static ?string $pluralModelLabel = 'Demandes CAT';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la demande')
                    ->schema([
                        Forms\Components\TextInput::make('user.email')
                            ->label('Email demandeur')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('asso')
                            ->label('Association')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('event_name')
                            ->label("Nom de l'évènement")
                            ->disabled(),
                        
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date de début')
                            ->disabled(),
                        
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date de fin')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('cats_count')
                            ->label('Nombre de CATs')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('tpe_count')
                            ->label('Nombre de TPE')
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Traitement administratif')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'accepted' => 'Accepté',
                                'rejected' => 'Refusé',
                            ])
                            ->required()
                            ->reactive(),
                        
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notes administratives')
                            ->rows(3),
                    ])
                    ->columns(1),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(1)
                    ->schema([
                        TextEntry::make('asso')
                            ->label('')
                            ->formatStateUsing(function ($state, $record) {
                                return ucfirst($record->asso ?? '') . ' - ' . ucfirst($record->event_name ?? '');
                            })
                            ->size('4xl')
                            ->weight('bold')
                            ->alignCenter()
                            ->extraAttributes(['style' => 'font-size:3rem;']),
                    ])
                    ->columnSpanFull(),

                Section::make('Demandeur.euse')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Split::make([
                            TextEntry::make('user')
                                ->label('Prénom et Nom')
                                ->size(TextEntry\TextEntrySize::Large)
                                ->formatStateUsing(fn ($state) => ucfirst($state->firstName) . ' ' . ucfirst($state->lastName)),
                            
                            TextEntry::make('user.email')
                                ->label('Email')
                                ->size(TextEntry\TextEntrySize::Large)
                                ->formatStateUsing(fn ($state) => $state ?? '—'),
                        ])
                        ->columns(2),
                    ]),
                
                Section::make('Informations générales')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('cats_count')
                                ->size(TextEntry\TextEntrySize::Large)
                                ->label('Nombre de CATs'),
                            TextEntry::make('tpe_count')
                                ->size(TextEntry\TextEntrySize::Large)
                                ->label('Nombre de TPE'),
                        ]),
                        Grid::make(2)->schema([
                            TextEntry::make('start_date')
                                ->size(TextEntry\TextEntrySize::Large)
                                ->label('Date de début')
                                ->formatStateUsing(fn ($state) => ucfirst(\Carbon\Carbon::parse($state)->locale('fr')->translatedFormat('l d F Y'))),
                            TextEntry::make('end_date')
                                ->size(TextEntry\TextEntrySize::Large)
                                ->label('Date de fin')
                                ->formatStateUsing(fn ($state) => ucfirst(\Carbon\Carbon::parse($state)->locale('fr')->translatedFormat('l d F Y'))),
                        ]),
                    ]),

                Section::make('Responsables')
                    ->icon('heroicon-o-users')
                    ->description("Membres de l'asso capables de déverouiller et annuler des ventes sur les CATs")
                    ->schema([
                        RepeatableEntry::make('responsibles')
                            ->label('')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('')
                                    ->size('lg')
                                    ->alignCenter(),
                            ])
                            ->columns(1)
                            ->grid(4),
                    ])
                    ->columnSpanFull(),

                Section::make('Articles')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        RepeatableEntry::make('articles')
                            ->label('')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nom')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->formatStateUsing(fn ($state) => $state ?? '—'),

                                TextEntry::make('price')
                                    ->label('Prix (€)')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->formatStateUsing(fn ($state) => $state !== null ? number_format(floatval($state), 2, ',', ' ') . ' €' : '—'),

                                ImageEntry::make('image')
                                    ->label('Image')
                                    ->height(120)
                                    ->width(120)
                                    ->alignCenter()
                                    ->url(fn ($state) => $state ? asset('storage/' . ltrim($state, '/')) : null)
                                    ->visible(fn ($state) => !empty($state)),
                                    
                                TextEntry::make('consigne_type')
                                    ->label('Consigne')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->visible(fn ($state) => !empty($state))
                                    ->formatStateUsing(function ($state) {
                                        return match ($state ?? '') {
                                            'ecocup' => 'Écocup 1€',
                                            'assiette' => 'Assiette 1€',
                                            default => 'Oui',
                                        };
                                    }),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user')
                    ->formatStateUsing(fn($state) => $state->firstName . ' ' . $state->lastName)
                    ->label('Demandeur.euse')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('asso')
                    ->label('Asso')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('event_name')
                    ->label('Événement')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('lieu')
                    ->label('Lieu')
                    ->formatStateUsing(fn ($state, $record) => match($state) {
                        'bf' => 'BF',
                        'pic' => 'Pic',
                        'jmde' => 'Jardin de la MDE',
                        'parkingBf' => 'Parking de BF',
                        'autre' => $record->lieu_autre ?? 'Autre',
                        default => $state,
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('connexion')
                    ->label('Connexion')
                    ->formatStateUsing(fn ($state) => match($state) {
                        '4g' => '4G',
                        'rhizome' => 'Rhizome',
                        default => 'Réseau UTC',
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Début')
                    ->formatStateUsing(fn($state) => $state ? ucfirst(\Carbon\Carbon::parse($state)->locale('fr')->translatedFormat('l d F Y')) : '')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->formatStateUsing(fn($state) => $state ? ucfirst(\Carbon\Carbon::parse($state)->locale('fr')->translatedFormat('l d F Y')) : '')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('cats_count')
                    ->label('CATs')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('tpe_count')
                    ->label('TPE')
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Demandé le')
                    ->formatStateUsing(fn ($state) => ucfirst(\Carbon\Carbon::parse($state)->locale('fr')->translatedFormat('l d F Y H:i')))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'accepted' => 'Accepté',
                        'rejected' => 'Refusé',
                    ])
                    ->default('pending'),
                
                Tables\Filters\Filter::make('future_events')
                    ->label('Événements à venir')
                    ->query(fn ($query) => $query->whereDate('start_date', '>=', now()))
                    ->default(true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),                
                Tables\Actions\Action::make('accept')
                    ->label('Accepter')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'accepted',
                            'processed_at' => now(),
                        ]);
                        
                        CatSale::create([
                            'cat_request_id' => $record->id,
                            'status' => 'none',
                        ]);
                        
                        Notification::make()
                            ->title('Demande acceptée')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                Tables\Actions\Action::make('reject')
                    ->label('Refuser')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Raison du refus')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'admin_notes' => $data['admin_notes'],
                            'processed_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Demande refusée')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCatRequests::route('/'),
            'view' => Pages\ViewCatRequest::route('/{record}'),
            'edit' => Pages\EditCatRequest::route('/{record}/edit'),
        ];
    }
}