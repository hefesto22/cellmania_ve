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
            TextInput::make('marca')->required()->maxLength(50),
            TextInput::make('modelo')->required()->maxLength(50),
            TextInput::make('almacenamiento')->required(),
            TextInput::make('ram')->required(),
            TextInput::make('color')->maxLength(30),
            TextInput::make('precio_compra')->required()->numeric(),
            TextInput::make('precio_venta')->required()->numeric(),
            TextInput::make('stock')->required()->numeric()->minValue(0),
            TextInput::make('codigo_barras')->required()->unique(ignoreRecord: true),
            Select::make('estado')
                ->required()
                ->options([
                    'Disponible' => 'Disponible',
                    'Vendido' => 'Vendido',
                    'Reservado' => 'Reservado',
                    'Inactivo' => 'Inactivo',
                ])
                ->default('Disponible'),
            // ✅ usuario_id se asigna automáticamente en mutateFormData, no se necesita aquí
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('marca')->searchable()->sortable(),
                TextColumn::make('modelo')->searchable(),
                TextColumn::make('almacenamiento'),
                TextColumn::make('ram'),
                TextColumn::make('stock')->sortable(),
                TextColumn::make('precio_venta')->money('USD'),
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
                // ❌ Eliminado: TextColumn::make('sucursal.nombre')
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

        // Relación anticipada solo con usuario
        $query = parent::getEloquentQuery()->with('usuario');

        // Jefe ve todo
        if ($user->role === 'Jefe') {
            return $query;
        }

        // Encargado ve sus registros y los de sus vendedores
        if ($user->created_by === null) {
            $vendedorIds = User::where('created_by', $user->id)->pluck('id')->toArray();

            return $query->where(function ($q) use ($user, $vendedorIds) {
                $q->where('usuario_id', $user->id)
                    ->orWhereIn('usuario_id', $vendedorIds);
            });
        }

        // Vendedor ve sus registros y los del encargado
        $encargadoId = $user->created_by;

        return $query->where(function ($q) use ($user, $encargadoId) {
            $q->where('usuario_id', $user->id)
                ->orWhere('usuario_id', $encargadoId);
        });
    }
}
