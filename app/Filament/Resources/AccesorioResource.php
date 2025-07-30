<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccesorioResource\Pages;
use App\Models\Accesorio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use App\Models\Marca;
use App\Models\Categoria;


class AccesorioResource extends Resource
{
    protected static ?string $model = Accesorio::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Accesorios';
    protected static ?string $pluralModelLabel = 'Accesorios';
    protected static ?string $modelLabel = 'Accesorio';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')->required(),
            Forms\Components\TextInput::make('codigo_barras')->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('marca_id')
                ->label('Marca')
                ->options(fn() => Marca::limit(5)->pluck('nombre', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('categoria_id')
                ->label('Categoría')
                ->options(fn() => Categoria::limit(5)->pluck('nombre', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('precio_compra')->numeric()->required(),
            Forms\Components\TextInput::make('precio_venta')->numeric()->required(),
            Forms\Components\TextInput::make('isv')
                ->label('ISV (%)')
                ->numeric()
                ->default(0.00)
                ->required(),

            Forms\Components\TextInput::make('stock')->numeric()->default(0),
            Forms\Components\Select::make('estado')
                ->options([
                    'Disponible' => 'Disponible',
                    'Vendido' => 'Vendido',
                    'Inactivo' => 'Inactivo',
                ])
                ->default('Disponible')
                ->required(),

            Forms\Components\Hidden::make('created_by')
                ->default(fn() => Auth::user()?->id)
                ->required()
                ->visibleOn('create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('marca.nombre')->label('Marca')->sortable(),
                Tables\Columns\TextColumn::make('categoria.nombre')->label('Categoría')->sortable(),
                Tables\Columns\TextColumn::make('precio_venta')->money('HNL'),
                Tables\Columns\TextColumn::make('isv')
                    ->label('ISV (%)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock'),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Disponible' => 'success',
                        'Vendido' => 'danger',
                        'Inactivo' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('creador.name')
                    ->label('Creado por')
                    ->sortable(),
            ])
            ->defaultSort('nombre');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccesorios::route('/'),
            'create' => Pages\CreateAccesorio::route('/create'),
            'edit' => Pages\EditAccesorio::route('/{record}/edit'),
        ];
    }
}
