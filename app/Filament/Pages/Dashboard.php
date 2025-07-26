<?php

namespace App\Filament\Pages;

use App\Models\Telefono;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.dashboard';

    public function getTableQuery(): Builder
    {
        return $this->getTelefonosQuery();
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('marca')->label('Marca')->searchable(),
            TextColumn::make('modelo')->label('Modelo')->searchable(),
            TextColumn::make('almacenamiento')->label('Almacenamiento'),
            TextColumn::make('ram')->label('RAM'),
            TextColumn::make('color')->label('Color'),
        ];
    }
    public int $cantidadCarrito = 0;

    public function getTableActions(): array
    {
        return [
            Action::make('agregar')
                ->label('Agregar')
                ->color('primary')
                ->icon('heroicon-m-plus')
                ->action(function ($record) {
                    $this->cantidadCarrito++;
                    session()->put('cantidad_carrito', $this->cantidadCarrito);

                    \Filament\Notifications\Notification::make()
                        ->title("Teléfono {$record->marca} {$record->modelo} agregado al carrito.")
                        ->success()
                        ->send();
                }),
        ];
    }


    protected function getTelefonosQuery(): Builder
    {
        $auth = Auth::user();

        if ($auth->role_id === 1) {
            $idsPermitidos = User::pluck('id')->toArray();
        } elseif ($auth->role_id === 2) {
            $subIds = User::where('created_by', $auth->id)->pluck('id')->toArray();
            $idsPermitidos = array_merge([$auth->id], $subIds);
        } elseif ($auth->role_id === 3) {
            $propietarioId = $auth->created_by;
            $companerosIds = User::where('created_by', $propietarioId)
                ->where('id', '!=', $auth->id)
                ->pluck('id')
                ->toArray();

            $idsPermitidos = [$auth->id, $propietarioId, ...$companerosIds];
        } else {
            $idsPermitidos = [$auth->id];
        }

        return Telefono::query()->whereIn('usuario_id', $idsPermitidos);
    }
}
