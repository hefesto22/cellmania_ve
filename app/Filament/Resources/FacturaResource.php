<?php

// app/Filament/Resources/FacturaResource.php

namespace App\Filament\Resources;

use App\Models\Factura;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\FacturaResource\Pages;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\{TextInput, DateTimePicker, Textarea};
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class FacturaResource extends Resource
{
    protected static ?string $model = Factura::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Facturas';
    protected static ?string $modelLabel = 'Factura';
    protected static ?string $pluralModelLabel = 'Facturas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('numero_factura')->required()->unique(ignoreRecord: true),
            TextInput::make('cai')->nullable(),
            DateTimePicker::make('fecha_emision')->default(now()),

            TextInput::make('cliente_rtn')->maxLength(50),
            TextInput::make('cliente_nombre'),
            Textarea::make('cliente_direccion')->rows(2),

            TextInput::make('subtotal_sin_isv')->numeric()->required(),
            TextInput::make('total_isv')->numeric()->required(),
            TextInput::make('bruto')->numeric()->required(),
            TextInput::make('descuento')->numeric()->default(0),
            TextInput::make('total_final')->numeric()->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_factura')->searchable(),
                TextColumn::make('cliente_nombre')->searchable(),
                TextColumn::make('total_final')->money('HNL'),
                TextColumn::make('fecha_emision')->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Action::make('verFactura')
                    ->label('Ver Factura')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('filament.admin.pages.ver-factura', ['record' => $record->id])),
            ])
            ->defaultSort('fecha_emision', 'desc');
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacturas::route('/'),
            'create' => Pages\CreateFactura::route('/create'),
            'edit' => Pages\EditFactura::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $auth = Auth::user();

        // Admin y Supervisor ven todo
        if (in_array($auth->role_id, [1, 2])) {
            return parent::getEloquentQuery();
        }

        // Encargado: ve lo suyo + lo de sus registrados
        if ($auth->role_id === 3) {
            $userIds = \App\Models\User::where('created_by', $auth->id)
                ->pluck('id')
                ->push($auth->id);

            return parent::getEloquentQuery()->whereIn('user_id', $userIds);
        }

        // Otros roles: solo lo suyo
        return parent::getEloquentQuery()->where('user_id', $auth->id);
    }
}
