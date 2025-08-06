<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DatosEmpresaResource\Pages;
use App\Models\DatosEmpresa;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, DatePicker, FileUpload, Section, Grid, Select};
use Filament\Tables\Columns\{TextColumn, ImageColumn};
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\{ViewAction, EditAction, DeleteAction, ActionGroup};
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;



class DatosEmpresaResource extends Resource
{
    protected static ?string $model = DatosEmpresa::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Datos de la Empresa';
    protected static ?string $modelLabel = 'Datos de la Empresa';
    protected static ?string $pluralModelLabel = 'Datos de la Empresa';
    protected static ?string $navigationGroup = 'Empresa y Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General')
                    ->description('Datos básicos de la empresa')
                    ->schema([
                        TextInput::make('nombre')->required()->maxLength(255),
                        TextInput::make('lema')->maxLength(255),
                        FileUpload::make('logo')
                            ->directory('logos')
                            ->image()
                            ->imageEditor()
                            ->maxSize(1024)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Contacto')
                    ->schema([
                        TextInput::make('telefono')->tel(),
                        TextInput::make('email')->email(),
                        TextInput::make('direccion'),
                    ])->columns(2),

                Section::make('Datos Fiscales')
                    ->description('Información requerida por la SAR')
                    ->schema([
                        TextInput::make('rtn')->label('RTN')->maxLength(50),
                        TextInput::make('cai')->maxLength(50)->nullable(),
                        TextInput::make('rango_desde')->label('Rango Desde')->maxLength(25)->nullable(),
                        TextInput::make('rango_hasta')->label('Rango Hasta')->maxLength(25)->nullable(),
                        TextInput::make('numero_actual')->label('Número Actual')->maxLength(25)->nullable(),
                        DatePicker::make('fecha_limite_emision')->label('Fecha Límite de Emisión')->nullable(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')->label('Logo')->circular(),
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('rtn'),
                TextColumn::make('telefono'),
                TextColumn::make('email'),
                TextColumn::make('numero_actual')->label('Correlativo Actual'),
                TextColumn::make('fecha_limite_emision')->date('d/m/Y'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->button()
                    ->label('Actions'), // o 'Acciones' si prefieres en español
            ])
            ->defaultSort('id', 'desc');
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDatosEmpresas::route('/'),
            'create' => Pages\CreateDatosEmpresa::route('/crear'),
            'edit' => Pages\EditDatosEmpresa::route('/{record}/editar'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $auth = Auth::user();

        // Admin y Supervisor (roles 1 y 2) ven lo suyo y lo de los usuarios que registraron
        if (in_array($auth->role_id, [1, 2])) {
            $userIds = User::where('created_by', $auth->id)
                ->pluck('id')
                ->push($auth->id);

            return parent::getEloquentQuery()->whereIn('user_id', $userIds);
        }

        // Encargado (rol 3): lo suyo + usuarios que él registró
        if ($auth->role_id === 3) {
            $userIds = User::where('created_by', $auth->id)
                ->pluck('id')
                ->push($auth->id);

            return parent::getEloquentQuery()->whereIn('user_id', $userIds);
        }

        // Otros usuarios: solo lo suyo
        return parent::getEloquentQuery()->where('user_id', $auth->id);
    }
}
