<?php

namespace App\Filament\Resources\TelefonoResource\RelationManagers;

use App\Models\Accesorio;
use App\Models\AccesorioTelefono;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;

class AccesoriosRelationManager extends RelationManager
{
    protected static string $relationship = 'accesorios'; // este nombre se mantendrá solo para el contexto visual
    protected static ?string $title = 'Accesorios Incluidos';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre'),
                TextColumn::make('codigo_barras'),
                TextColumn::make('precio_venta')->money('HNL', true),
                TextColumn::make('precio_compra')->money('HNL', true),
                TextColumn::make('isv')->label('ISV (%)'),
                TextColumn::make('stock'),
            ])
            ->headerActions([
                Action::make('Agregar Accesorio')
                    ->form([
                        Select::make('accesorio_id')
                            ->label('Seleccionar accesorio')
                            ->options(function () {
                                $auth = Auth::user(); // ✅ así como lo estás trabajando tú

                                $ids = collect([$auth->id, $auth->created_by])->filter()->unique();

                                return Accesorio::where('stock', '>', 0)
                                    ->whereIn('created_by', $ids)
                                    ->pluck('nombre', 'id');
                            })
                            ->searchable()
                            ->required(),
                    ])


                    ->action(function (array $data) {
                        $accesorio = Accesorio::findOrFail($data['accesorio_id']);

                        if ($accesorio->stock <= 0) {
                            Notification::make()
                                ->title('Stock insuficiente')
                                ->body('Este accesorio no tiene unidades disponibles.')
                                ->danger()
                                ->send();

                            return;
                        }

                        // Crear entrada en accesorio_telefono
                        AccesorioTelefono::create([
                            'telefono_id'    => $this->ownerRecord->id,
                            'nombre'         => $accesorio->nombre,
                            'codigo_barras'  => $accesorio->codigo_barras,
                            'precio_compra'  => $accesorio->precio_compra,
                            'precio_venta'   => 0, // puedes cambiarlo si lo cobras aparte
                            'isv'            => $accesorio->isv,
                            'stock'          => 1,
                        ]);

                        $accesorio->stock -= 1;
                        $accesorio->save(); // 🔥 Esto sí ejecuta el observer y actualiza estado


                        Notification::make()
                            ->title('Accesorio asignado')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Action::make('Eliminar')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->delete();

                        Notification::make()
                            ->title('Accesorio eliminado del teléfono')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
