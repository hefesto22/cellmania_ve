<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TelefonoResource\Pages;
use App\Models\Telefono;
use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\{TextInput, Select, Hidden};
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Accesorio;
use App\Filament\Resources\TelefonoResource\RelationManagers\AccesoriosRelationManager;
use App\Models\Marca;
use App\Models\Categoria;
use Filament\Tables\Filters\SelectFilter;


class TelefonoResource extends Resource
{
    protected static ?string $model = Telefono::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'Teléfonos';
    protected static ?string $pluralModelLabel = 'Teléfonos';
    protected static ?string $modelLabel = 'Teléfono';
    protected static ?string $navigationGroup = 'Productos';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Select::make('marca_id')
                ->label('Marca')
                ->options(function () {
                    $auth = Auth::user();
                    $ids = collect([$auth->id, $auth->created_by])->filter()->unique();
                    return Marca::whereIn('created_by', $ids)->pluck('nombre', 'id');
                })
                ->searchable()
                ->preload()
                ->required(),

            Select::make('categoria_id')
                ->label('Categoría')
                ->options(function () {
                    $auth = Auth::user();
                    $ids = collect([$auth->id, $auth->created_by])->filter()->unique();
                    return Categoria::whereIn('created_by', $ids)->pluck('nombre', 'id');
                })
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('modelo')->required()->maxLength(50),
            TextInput::make('almacenamiento')
                ->label('Almacenamiento (GB)')
                ->numeric()
                ->required()
                ->formatStateUsing(fn($state) => preg_replace('/\D/', '', $state)) // solo el número
                ->dehydrateStateUsing(fn($state) => $state . 'GB'), // se guarda con "GB"

            TextInput::make('ram')
                ->label('RAM (GB)')
                ->numeric()
                ->required()
                ->formatStateUsing(fn($state) => preg_replace('/\D/', '', $state))
                ->dehydrateStateUsing(fn($state) => $state . 'GB'),


            TextInput::make('color')->maxLength(30),
            TextInput::make('precio_compra')->required()->numeric(),
            TextInput::make('precio_venta')->required()->numeric(),
            TextInput::make('isv')
                ->label('ISV (%)')
                ->numeric()
                ->default(0) // ✅ ahora por defecto 0
                ->required(),
            //TextInput::make('stock')->required()->numeric()->minValue(0),
            TextInput::make('imei')->label('IMEI')->maxLength(30),
            TextInput::make('codigo_barras')->required()->unique(ignoreRecord: true),


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
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('marca.nombre')->label('Marca')->sortable()->searchable(),

                TextColumn::make('modelo')->searchable(),
                TextColumn::make('almacenamiento'),
                TextColumn::make('ram'),
                TextColumn::make('precio_venta')->money('USD'),
                TextColumn::make('isv')->label('ISV (%)'), // 👈 Campo nuevo
                TextColumn::make('stock')->sortable(),
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Disponible' => 'success',
                        'Reservado' => 'warning',
                        'Vendido' => 'danger',
                        'Inactivo' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('codigo_barras')->label('Código')->searchable(),
                TextColumn::make('imei')->label('IMEI')->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'Disponible' => 'Disponible',
                        'Vendido' => 'Vendido',
                        'Reservado' => 'Reservado',
                        'Inactivo' => 'Inactivo',
                    ]),

                // Filtro por encargado (solo para Jefe)
                SelectFilter::make('encargado')
                    ->label('Filtrar por Encargado')
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user->hasRole('Jefe');
                    })

                    ->options(function () {
                        $user = Auth::user();

                        return User::where('created_by', $user->id)
                            ->whereHas('roles', fn($q) => $q->where('name', 'Encargado'))
                            ->pluck('name', 'id');
                    })
                    ->query(function (Builder $query, $state) {
                        return $query->where('usuario_id', $state); // ✅ No se llama $value, sino $state
                    }),

            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTelefonos::route('/'),
            'create' => Pages\CreateTelefono::route('/create'),
            'edit' => Pages\EditTelefono::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $query = parent::getEloquentQuery()->with('usuario');
        $roleName = $user->roles->first()?->name;

        $filters = request()->input('tableFilters', []);

        // Detectar si el filtro de estado está activo
        $filtradoPorEstado = isset($filters['estado']) && !empty($filters['estado']['value']);

        if ($roleName === 'Jefe') {
            $subUserIds = \App\Models\User::where('created_by', $user->id)->pluck('id');
            $query->whereIn('usuario_id', $subUserIds->push($user->id));
        } elseif ($roleName === 'Encargado') {
            $vendedorIds = \App\Models\User::where('created_by', $user->id)->pluck('id');
            $query->where(function ($q) use ($user, $vendedorIds) {
                $q->where('usuario_id', $user->id)
                    ->orWhereIn('usuario_id', $vendedorIds);
            });
        } elseif ($roleName === 'Vendedor') {
            $query->where(function ($q) use ($user) {
                $q->where('usuario_id', $user->id)
                    ->orWhere('usuario_id', $user->created_by);
            });
        } else {
            return $query->whereNull('id');
        }

        // 👇 Aplicar condición para ocultar "Vendido" solo si no está filtrando explícitamente por estado
        if (! $filtradoPorEstado) {
            $query->where('estado', '!=', 'Vendido');
        }

        return $query->orderByRaw('stock = 0');
    }


    public static function getRelations(): array
    {
        return [
            AccesoriosRelationManager::class,
        ];
    }
}
