<?php

namespace App\Filament\Pages;

use App\Models\Factura;
use App\Models\ProductoFactura;
use Filament\Pages\Page;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class FinanzasOverview extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Resumen de Finanzas';
    protected static ?string $title = 'Resumen Financiero';
    protected static string $view = 'filament.pages.finanzas-overview';
    protected static ?string $navigationGroup = 'Finanzas';

    // Obtener usuario filtrado desde los filtros de tabla
    public function getUsuarioFiltrado(): ?int
    {
        return request()->input('tableFilters.user_id');
    }


    public function getTotalFacturado(): float
    {
        return $this->getTableRecords()->sum('total_final');
    }

    public function getTotalGastos(): float
    {
        $facturasIds = $this->getTableRecords()->pluck('id');

        // Sumar el costo de los productos facturados
        $gastoProductos = \App\Models\ProductoFactura::whereIn('factura_id', $facturasIds)->sum('costo');

        // Obtener los IDs de teléfonos vendidos
        $telefonosVendidos = \App\Models\ProductoFactura::whereIn('factura_id', $facturasIds)
            ->where('tipo', 'Telefono') // 👈 importante
            ->pluck('referencia_id');

        // Sumar el precio_compra de los accesorios vinculados a esos teléfonos
        $gastoAccesorios = DB::table('accesorio_telefono')
            ->whereIn('telefono_id', $telefonosVendidos)
            ->sum('precio_compra');

        return $gastoProductos + $gastoAccesorios;
    }



    public function getBalance(): float
    {
        return $this->getTotalFacturado() - $this->getTotalGastos();
    }

    public function getTableQuery(): Builder
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Validamos que el método hasRole exista por seguridad
        if (method_exists($user, 'hasRole')) {

            // Si es super_admin, ve todo
            if ($user->hasRole('super_admin')) {
                return Factura::query();
            }

            // Si es Jefe
            if ($user->hasRole('Jefe')) {
                $encargados = User::where('created_by', $user->id)->pluck('id')->toArray();
                $vendedores = User::whereIn('created_by', $encargados)->pluck('id')->toArray();

                $ids = array_merge([$user->id], $encargados, $vendedores);

                return Factura::whereIn('user_id', $ids);
            }

            // Si es Encargado
            if ($user->hasRole('Encargado')) {
                $vendedores = User::where('created_by', $user->id)->pluck('id')->toArray();

                $ids = array_merge([$user->id], $vendedores);

                return Factura::whereIn('user_id', $ids);
            }
        }

        // Cualquier otro rol solo ve sus propias facturas
        return Factura::where('user_id', $user->id);
    }




    public function getTableColumns(): array
    {
        return [
            TextColumn::make('numero_factura')->label('Factura')->searchable(),
            TextColumn::make('cliente_nombre')->label('Cliente')->searchable(),
            TextColumn::make('fecha_emision')->label('Fecha')->date('d/m/Y'),
            TextColumn::make('total_final')->label('Total')->money('HNL')->sortable(),
        ];
    }

    public function getTableFilters(): array
    {
        return [
            // Filtro por Rango de Fechas
            Filter::make('fecha_emision')
                ->form([
                    DatePicker::make('from')->label('Desde'),
                    DatePicker::make('until')->label('Hasta'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['from'], fn($q) => $q->whereDate('fecha_emision', '>=', $data['from']))
                        ->when($data['until'], fn($q) => $q->whereDate('fecha_emision', '<=', $data['until']));
                }),

            SelectFilter::make('user_id')
                ->label('Encargado')
                ->options(
                    User::whereHas('roles', fn($q) => $q->where('name', 'Encargado'))
                        ->pluck('name', 'id')
                )
                ->searchable()
                ->visible(function () {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();

                    return method_exists($user, 'hasRole') &&
                        ($user->hasRole('super_admin') || $user->hasRole('Jefe'));
                })

        ];
    }
    public function getInversionPorTipo(): array
    {
        return [
            'telefonos' => DB::table('telefonos')
                ->where('estado', 'Disponible')
                ->sum(DB::raw('precio_compra * stock')),

            'accesorios' => DB::table('accesorios')
                ->where('estado', 'Disponible')
                ->sum(DB::raw('precio_compra * stock')),

            'accesorios_telefono' => DB::table('accesorio_telefono')
                ->where('stock', '>', 0)
                ->sum('precio_compra'),
        ];
    }



    //aceso solo apara jefe 
    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->can('page_FinanzasOverview');
    }
}
