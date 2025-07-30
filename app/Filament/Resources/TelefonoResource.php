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

class TelefonoResource extends Resource
{
    protected static ?string $model = Telefono::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'Teléfonos';
    protected static ?string $pluralModelLabel = 'Teléfonos';
    protected static ?string $modelLabel = 'Teléfono';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Select::make('marca_id')
                ->label('Marca')
                ->relationship('marca', 'nombre')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('categoria_id')
                ->label('Categoría')
                ->relationship('categoria', 'nombre')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('modelo')->required()->maxLength(50),
            TextInput::make('almacenamiento')
                ->label('Almacenamiento (GB)')
                ->numeric()
                ->required(),

            TextInput::make('ram')
                ->label('RAM (GB)')
                ->numeric()
                ->required(),

            TextInput::make('color')->maxLength(30),
            TextInput::make('precio_compra')->required()->numeric(),
            TextInput::make('precio_venta')->required()->numeric(),
            TextInput::make('isv') // 👈 Campo nuevo
                ->label('ISV (%)')
                ->numeric()
                ->default(15.00)
                ->required(),
            TextInput::make('stock')->required()->numeric()->minValue(0),
            TextInput::make('codigo_barras')->required()->unique(ignoreRecord: true),
            TextInput::make('imei')->label('IMEI')->maxLength(30),

            Select::make('estado')
                ->required()
                ->options([
                    'Disponible' => 'Disponible',
                    'Vendido' => 'Vendido',
                    'Reservado' => 'Reservado',
                    'Inactivo' => 'Inactivo',
                ])
                ->default('Disponible'),
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

        // Jefe ve lo que él y los usuarios que él registró hayan hecho
        if ($roleName === 'Jefe') {
            $subUserIds = \App\Models\User::where('created_by', $user->id)->pluck('id');
            return $query->whereIn('usuario_id', $subUserIds->push($user->id));
        }

        // Encargado ve sus registros y los de sus vendedores
        if ($roleName === 'Encargado') {
            $vendedorIds = \App\Models\User::where('created_by', $user->id)->pluck('id');
            return $query->where(function ($q) use ($user, $vendedorIds) {
                $q->where('usuario_id', $user->id)
                    ->orWhereIn('usuario_id', $vendedorIds);
            });
        }

        // Vendedor ve lo suyo y lo del encargado que lo creó
        if ($roleName === 'Vendedor') {
            return $query->where(function ($q) use ($user) {
                $q->where('usuario_id', $user->id)
                    ->orWhere('usuario_id', $user->created_by);
            });
        }

        // Si no tiene rol definido, no ve nada
        return $query->whereNull('id');
    }
    public static function getRelations(): array
    {
        return [
            AccesoriosRelationManager::class,
        ];
    }
}
