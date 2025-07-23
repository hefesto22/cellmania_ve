<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SucursalResource\Pages;
use App\Models\Sucursal;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SucursalResource extends Resource
{
    protected static ?string $model = Sucursal::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $navigationLabel = 'Sucursales';
    protected static ?string $modelLabel = 'Sucursal';
    protected static ?string $pluralModelLabel = 'Sucursales';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Forms\Components\TextInput::make('direccion')
                ->label('Dirección')
                ->maxLength(255),

            Forms\Components\TextInput::make('telefono')
                ->label('Teléfono')
                ->maxLength(20),

            Forms\Components\Select::make('user_id')
                ->label('Encargado')
                ->required()
                ->relationship('user', 'name')
                ->options(function () {
                    return User::role('Encargado')->pluck('name', 'id');
                })
                ->searchable()
                ->preload(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->sortable()->label('ID'),
            Tables\Columns\TextColumn::make('nombre')->searchable(),
            Tables\Columns\TextColumn::make('direccion')->label('Dirección'),
            Tables\Columns\TextColumn::make('telefono')->label('Teléfono'),
            Tables\Columns\TextColumn::make('user.name')->label('Encargado')->searchable(),
            Tables\Columns\TextColumn::make('created_at')->label('Creado')->dateTime(),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSucursals::route('/'),
            'create' => Pages\CreateSucursal::route('/create'),
            'edit' => Pages\EditSucursal::route('/{record}/edit'),
        ];
    }
}
