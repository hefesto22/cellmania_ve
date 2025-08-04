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

class FinanzasOverview extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Resumen de Finanzas';
    protected static ?string $title = 'Resumen Financiero';
    protected static string $view = 'filament.pages.finanzas-overview';

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
        return ProductoFactura::whereIn('factura_id', $facturasIds)->sum('costo');
    }

    public function getBalance(): float
    {
        return $this->getTotalFacturado() - $this->getTotalGastos();
    }

    public function getTableQuery(): Builder
    {
        $query = Factura::query();

        if ($this->getUsuarioFiltrado()) {
            $query->where('user_id', $this->getUsuarioFiltrado());
        }

        return $query;
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

            // Filtro por Encargado
            SelectFilter::make('user_id')
                ->label('Encargado')
                ->options(
                    User::whereHas('roles', fn($q) => $q->where('name', 'Encargado'))->pluck('name', 'id')
                )
                ->searchable(),
        ];
    }
}
