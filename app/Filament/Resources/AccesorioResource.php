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
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;


class AccesorioResource extends Resource
{
    protected static ?string $model = Accesorio::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Accesorios';
    protected static ?string $pluralModelLabel = 'Accesorios';
    protected static ?string $modelLabel = 'Accesorio';
    protected static ?string $navigationGroup = 'Productos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')->required(),

            Forms\Components\Select::make('marca_id')
                ->label('Marca')
                ->options(function () {
                    $auth = Auth::user();
                    $ids = collect([$auth->id, $auth->created_by])->filter()->unique();
                    return Marca::whereIn('created_by', $ids)->pluck('nombre', 'id');
                })
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('categoria_id')
                ->label('Categoría')
                ->options(function () {
                    $auth = Auth::user();
                    $ids = collect([$auth->id, $auth->created_by])->filter()->unique();
                    return Categoria::whereIn('created_by', $ids)->pluck('nombre', 'id');
                })
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

            Forms\Components\TextInput::make('stock')->numeric()->default(1),
            Select::make('estado')
                ->label('Estado')
                ->required()
                ->options([
                    'Disponible' => 'Disponible',
                    'Vendido' => 'Vendido',
                    'Reservado' => 'Reservado',
                    'Inactivo' => 'Inactivo',
                ])
                ->visible(fn() => request()->routeIs('filament.admin.resources.telefonos.edit')),

            Forms\Components\TextInput::make('codigo_barras')->required()->unique(ignoreRecord: true),

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
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'Disponible' => 'Disponible',
                        'Vendido' => 'Vendido',
                        'Reservado' => 'Reservado',
                        'Inactivo' => 'Inactivo',
                    ]),

                SelectFilter::make('encargado')
                    ->label('Filtrar por Encargado')
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user->hasRole('Jefe');
                    })
                    ->options(function () {
                        $user = Auth::user();
                        return \App\Models\User::where('created_by', $user->id)
                            ->whereHas('roles', fn($q) => $q->where('name', 'Encargado'))
                            ->pluck('name', 'id');
                    })
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, $state) {
                        if (blank($state)) {
                            return $query; // Mostrar todos si no hay filtro
                        }

                        return $query->where('created_by', $state);
                    }),

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
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $auth = Auth::user();

        // IDs válidos: el mismo usuario + los que él registró
        $userIds = \App\Models\User::where('created_by', $auth->id)
            ->pluck('id')
            ->push($auth->id);

        $query = parent::getEloquentQuery()->whereIn('created_by', $userIds);

        // ✅ Detectar si se está filtrando por estado
        $filters = request()->input('tableFilters', []);
        $filtradoPorEstado = isset($filters['estado']) && !empty($filters['estado']);


        // ✅ Ocultar los accesorios vendidos si no se está filtrando por estado
        if (! $filtradoPorEstado) {
            $query->where('estado', '!=', 'Vendido');
        }

        return $query;
    }
}
