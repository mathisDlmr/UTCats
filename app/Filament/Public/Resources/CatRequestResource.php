<?php

namespace App\Filament\Public\Resources;

use App\Filament\Public\Resources\CatRequestResource\Pages;
use App\Models\CatRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ImageEntry;

class CatRequestResource extends Resource
{
    protected static ?string $model = CatRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $modelLabel = 'Demande CAT';
    protected static ?string $pluralModelLabel = 'Mes demandes CAT';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\Select::make('asso')
                            ->label('Association')
                            ->options(fn () => 
                                collect(json_decode(Auth::user()->assos) ?? [])
                                    ->mapWithKeys(fn ($asso) => [$asso->login => $asso->name])
                                    ->toArray()
                            )
                            ->required()
                            ->searchable()
                            ->reactive(),

                        Forms\Components\TextInput::make('event_name')
                            ->label("Nom de l'évènement")
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date de début')
                            ->required()
                            ->minDate(now())
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => 
                                $set('end_date', null)),
                        
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date de fin')
                            ->required()
                            ->minDate(fn ($get) => $get('start_date') ?: now())
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('cats_count')
                            ->label('Nombre de CATs')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(8),
                        
                        Forms\Components\TextInput::make('tpe_count')
                            ->label('Nombre de TPE')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->maxValue(4),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Responsables')
                    ->schema([
                        Repeater::make('responsibles')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Prénom et nom')
                                    ->required()
                                    ->columnSpanFull()
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->addActionLabel('Ajouter un.e responsable')
                            ->collapsible(),
                    ]),

                Forms\Components\Section::make('Articles')
                    ->schema([
                        Repeater::make('articles')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom de l\'article')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('price')
                                    ->label('Prix')
                                    ->required()
                                    ->numeric()
                                    ->prefix('€'),
                                FileUpload::make('image')
                                    ->label('Image (optionnelle)')
                                    ->image()
                                    ->directory('cat-requests/articles')
                                    ->maxSize(2048),
                                Forms\Components\Toggle::make('consigne_enabled')
                                    ->label('Vendu avec une consigne ?')
                                    ->inline(false)
                                    ->reactive(),
                                Forms\Components\Select::make('consigne_type')
                                    ->label('Type de consigne')
                                    ->options([
                                        'ecocup' => 'Écocup 1€',
                                        'assiette' => 'Assiette 1€',
                                    ])
                                    ->visible(fn ($get) => $get('consigne_enabled'))
                                    ->required(fn ($get) => $get('consigne_enabled')),
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->addActionLabel('Ajouter un article')
                            ->collapsible(),
                    ]),
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

                Section::make('Responsables')
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
                    ->schema([
                        RepeatableEntry::make('articles')
                            ->label('')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nom')
                                    ->formatStateUsing(function ($state, $record) {
                                        $name = $record['name'] ?? '';
                                        $price = $record['price'] ?? '0';
                                        return "{$name} - {$price} €";
                                    })
                                    ->size('md')
                                    ->weight('medium')
                                    ->alignCenter(),

                                ImageEntry::make('image')
                                    ->label('Image')
                                    ->height(120)
                                    ->width(120)
                                    ->alignCenter()
                                    ->visible(fn($state) => !empty($state)),

                                TextEntry::make('consigne_enabled')
                                    ->label('Consigne')
                                    ->formatStateUsing(function ($state, $record) {
                                        if (!($record['consigne_enabled'] ?? false)) {
                                            return 'Non';
                                        }
                                        $type = $record['consigne_type'] ?? '';
                                        return match ($type) {
                                            'ecocup' => 'Oui (Écocup 1€)',
                                            'assiette' => 'Oui (Assiette 1€)',
                                            default => 'Oui',
                                        };
                                    })
                                    ->alignCenter(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asso')
                    ->label('Asso')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('event_name')
                    ->label('Evenement')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Début')
                    ->formatStateUsing(fn($state) => $state ? ucfirst(\Carbon\Carbon::parse($state)->locale('fr')->translatedFormat('l d F Y')) : '')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->formatStateUsing(fn($state) => $state ? ucfirst(\Carbon\Carbon::parse($state)->locale('fr')->translatedFormat('l d F Y')) : '')
                    ->sortable(),
                
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
                Tables\Filters\Filter::make('future_end_date')
                    ->label('Événements à venir')
                    ->query(fn (Builder $query) => $query->whereDate('end_date', '>=', now()))
                    ->default(true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === 'pending'),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => 
                $query->where('user_id', auth()->id()));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCatRequests::route('/'),
            'create' => Pages\CreateCatRequest::route('/create'),
            'edit' => Pages\EditCatRequest::route('/{record}/edit'),
            'view' => Pages\ViewCatRequest::route('/{record}'),
        ];
    }
}