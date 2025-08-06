<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarcaResource\Pages;
use App\Models\Marca;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;


class MarcaResource extends Resource
{
    protected static ?string $model = Marca::class;
    protected static ?string $navigationIcon = 'heroicon-o-bookmark';
    protected static ?string $navigationLabel = 'Marcas';
    protected static ?string $pluralModelLabel = 'Marcas';
    protected static ?string $modelLabel = 'Marca';
    protected static ?string $navigationGroup = 'Marcas y Categorias';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')
                ->required()
                ->maxLength(50),

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
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),

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
            'index' => Pages\ListMarcas::route('/'),
            'create' => Pages\CreateMarca::route('/create'),
            'edit' => Pages\EditMarca::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $auth = Auth::user();

        if (in_array($auth->role_id, [1, 2])) {
            $userIds = User::where('created_by', $auth->id)
                ->pluck('id')
                ->push($auth->id);

            return parent::getEloquentQuery()->whereIn('created_by', $userIds);
        }

        return parent::getEloquentQuery()->where('created_by', $auth->id);
    }
}
