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
use App\Models\Factura;
use App\Models\DatosEmpresa;
use App\Models\ProductoFactura;

use Filament\Notifications\Notification;

use Illuminate\Support\Facades\DB;

class Dashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.dashboard';

    public function getTableQuery(): Builder
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

        // Teléfonos
        $telefonos = DB::table('telefonos')
            ->select([
                DB::raw("CONCAT('telefono-', id) as uid"),
                'id',
                DB::raw("'telefono' as tipo"),
                DB::raw("modelo as nombre"),

                'precio_venta',
                'isv',
                'stock',
                'codigo_barras',
                'usuario_id as user_id',
            ])
            ->whereIn('usuario_id', $idsPermitidos)
            ->where('stock', '>', 0);

        // Accesorios
        $accesorios = DB::table('accesorios')
            ->select([
                DB::raw("CONCAT('accesorio-', id) as uid"),
                'id',
                DB::raw("'accesorio' as tipo"),
                'nombre',
                'precio_venta',
                'isv',
                'stock',
                'codigo_barras',
                'created_by as user_id',
            ])
            ->whereIn('created_by', $idsPermitidos)
            ->where('stock', '>', 0);

        // Unión
        $union = $telefonos->unionAll($accesorios);

        return \App\Models\Item::query()->fromSub($union, 'items');
    }



    public function getTableColumns(): array
    {
        return [
            TextColumn::make('nombre')->label('Nombre')->searchable(),
            TextColumn::make('tipo')->label('Tipo'),
            TextColumn::make('precio_venta')->label('Precio')->money('HNL', true),
            TextColumn::make('isv')->label('ISV')->suffix('%'),
            TextColumn::make('stock')
                ->label('Stock disponible')
                ->getStateUsing(
                    fn($record) =>
                    max(0, $record->stock - ($this->carrito[$record->uid]['cantidad'] ?? 0))
                )
                ->color(
                    fn($record) => ($record->stock - ($this->carrito[$record->uid]['cantidad'] ?? 0)) > 0
                        ? 'gray'
                        : 'danger'
                ),

            TextColumn::make('codigo_barras')->label('Código de barras'),
        ];
    }

    //aca trabajaremos en el carrito 
    public array $carrito = [];
    public ?float $descuento = null;
    public bool $facturaSinCai = false;
    public ?string $nombre_cliente = null;
    public ?string $rtn_cliente = null;
    public ?string $direccion_cliente = null;

    public function mount(): void
    {
        $this->carrito = session()->get('carrito', []);
    }
    public function getSubtotalSinISVProperty(): float
    {
        return collect($this->carrito)
            ->sum(fn($item) => $item['precio'] * $item['cantidad']);
    }

    public function getTotalISVProperty(): float
    {
        return collect($this->carrito)
            ->sum(fn($item) => ($item['precio'] * $item['cantidad']) * ($item['isv'] / 100));
    }

    public function getTotalFinalProperty(): float
    {
        $bruto = $this->subtotalSinISV + $this->totalISV;
        return round($bruto - min($this->descuento ?? 0, $bruto), 2);
    }

    public function getIsvDesglosadoProperty(): array
    {
        $isv15 = 0;
        $isv18 = 0;

        foreach ($this->carrito as $item) {
            $sub  = $item['precio'] * $item['cantidad'];
            $porc = $item['isv'] ?? 0;

            if ($porc === 15) $isv15 += $sub * 0.15;
            if ($porc === 18) $isv18 += $sub * 0.18;
        }

        return [
            'isv15' => round($isv15, 2),
            'isv18' => round($isv18, 2),
        ];
    }
    #[\Livewire\Attributes\On('modificarCantidad')]
    public function modificarCantidad($uid, $delta): void
    {
        if (!isset($this->carrito[$uid])) return;

        $item = $this->carrito[$uid];
        $modelo = match ($item['tipo']) {
            'telefono' => \App\Models\Telefono::find($item['id']),
            'accesorio' => \App\Models\Accesorio::find($item['id']),
            default => null,
        };

        if (!$modelo) return;

        $stock = $modelo->stock ?? 0;
        $nuevaCantidad = $item['cantidad'] + $delta;

        if ($nuevaCantidad <= 0) {
            unset($this->carrito[$uid]);
        } elseif ($nuevaCantidad <= $stock) {
            $this->carrito[$uid]['cantidad'] = $nuevaCantidad;
        }

        session()->put('carrito', $this->carrito);
        $this->dispatch('$refresh');
    }

    #[\Livewire\Attributes\On('quitarDelCarrito')]
    public function quitarDelCarrito($uid): void
    {
        unset($this->carrito[$uid]);
        session()->put('carrito', $this->carrito);
        $this->dispatch('$refresh');
    }
    public function descartarVenta(): void
    {
        $this->carrito = [];
        session()->forget('carrito');
        $this->descuento = 0;
        $this->dispatch('closeModal', ['id' => 'carrito-modal']);

        \Filament\Notifications\Notification::make()
            ->title('Venta descartada')
            ->success()
            ->send();
    }

    public function getCantidadCarritoProperty(): int
    {
        return collect($this->carrito)->sum('cantidad');
    }


    public function getTableActions(): array
    {
        return [
            Action::make('agregar')
                ->label('Agregar')
                ->color('primary')
                ->icon('heroicon-m-plus')
                ->disabled(
                    fn($record) => ($this->carrito[$record->uid]['cantidad'] ?? 0) >= $record->stock
                )
                ->action(function ($record) {
                    $uid = $record->uid;
                    $actual = $this->carrito[$uid]['cantidad'] ?? 0;

                    if ($actual >= $record->stock) {
                        \Filament\Notifications\Notification::make()
                            ->title("No hay más stock disponible para {$record->nombre}")
                            ->danger()
                            ->send();
                        return;
                    }

                    // Agrega el teléfono al carrito
                    $this->carrito[$uid] = [
                        'uid'      => $uid,
                        'id'       => $record->id,
                        'nombre'   => $record->nombre,
                        'tipo'     => $record->tipo,
                        'cantidad' => $actual + 1,
                        'precio'   => $record->precio_venta,
                        'isv'      => $record->isv,
                    ];

                    // Si es un teléfono, agrega sus accesorios relacionados
                    if ($record->tipo === 'telefono') {
                        $telefono = \App\Models\Telefono::find($record->id);
                        if ($telefono) {
                            $accesorios = \Illuminate\Support\Facades\DB::table('accesorio_telefono')
                                ->where('telefono_id', $telefono->id)
                                ->get();

                            foreach ($accesorios as $accesorio) {
                                $accUid = "accesorio-{$accesorio->id}";
                                $accActual = $this->carrito[$accUid]['cantidad'] ?? 0;

                                if ($accActual < $accesorio->stock) {
                                    $this->carrito[$accUid] = [
                                        'uid'      => $accUid,
                                        'id'       => $accesorio->id,
                                        'nombre'   => $accesorio->nombre,
                                        'tipo'     => 'accesorio',
                                        'cantidad' => $accActual + 1,
                                        'precio'   => $accesorio->precio_venta,
                                        'isv'      => $accesorio->isv,
                                        'fijo'     => true,
                                    ];
                                }
                            }
                        }
                    }

                    session()->put('carrito', $this->carrito);
                    $this->dispatch('$refresh');

                    \Filament\Notifications\Notification::make()
                        ->title("{$record->nombre} agregado al carrito.")
                        ->success()
                        ->send();
                }),

        ];
    }
    //aca inica para generar la imprecion 


    public function imprimirFactura(): void
    {
        if (empty($this->carrito)) {
            Notification::make()->title('El carrito está vacío')->danger()->send();
            return;
        }

        try {
            $authUser = Auth::user();
            $empresaUserId = $authUser->role_id === 3 ? $authUser->created_by : $authUser->id;

            $empresa = \App\Models\DatosEmpresa::where('user_id', $empresaUserId)->first();
            if (! $empresa) {
                Notification::make()->title('No hay empresa registrada')->danger()->send();
                return;
            }

            $usarCai = ! $this->facturaSinCai;
            $numeroFactura = '---';
            $cai = '---';

            if ($usarCai) {
                if ($empresa->fecha_limite_emision && now()->gt($empresa->fecha_limite_emision)) {
                    Notification::make()->title('Fecha límite de emisión vencida')->danger()->send();
                    return;
                }

                $numeroActual = (int) substr($empresa->numero_actual, -8);
                $nuevoNumero = $numeroActual + 1;
                $numeroFactura = substr($empresa->numero_actual, 0, -8) . str_pad($nuevoNumero, 8, '0', STR_PAD_LEFT);
                $cai = $empresa->cai;

                $empresa->update(['numero_actual' => $numeroFactura]);
            }

            // Guardar la factura
            $factura = \App\Models\Factura::create([
                'numero_factura' => $numeroFactura,
                'cai' => $cai,
                'cliente_rtn' => $this->rtn_cliente,
                'cliente_nombre' => $this->nombre_cliente,
                'cliente_direccion' => $this->direccion_cliente,
                'subtotal_sin_isv' => $this->subtotalSinISV,
                'total_isv' => $this->totalISV,
                'bruto' => $this->subtotalSinISV + $this->totalISV,
                'descuento' => $this->descuento ?? 0,
                'total_final' => $this->totalFinal,
                'datos_empresa_id' => $empresa->id,
                'user_id' => $authUser->id,
            ]);

            // Guardar productos en la factura
            foreach ($this->carrito as $item) {
                if (!isset($item['id'], $item['tipo'], $item['precio'], $item['cantidad'], $item['isv'], $item['nombre'])) {
                    logger('Producto incompleto en carrito:', $item);
                    continue;
                }


                $sub = $item['precio'] * $item['cantidad'];
                $isv = $sub * ($item['isv'] / 100);
                $total = $sub + $isv;

                \App\Models\ProductoFactura::create([
                    'factura_id' => $factura->id,
                    'tipo' => $item['tipo'],
                    'referencia_id' => $item['id'],
                    'nombre' => $item['nombre'] ?? 'Producto',
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'porcentaje_isv' => $item['isv'],
                    'total_isv' => $isv,
                    'subtotal' => $sub,
                    'total' => $total,
                    'costo' => $item['costo'] ?? 0,
                ]);
            }

            // Limpiar carrito
            $this->carrito = [];
            session()->forget('carrito');

            Notification::make()->title('Factura generada correctamente')->success()->send();
            $this->redirectRoute('filament.admin.pages.ver-factura', ['record' => $factura->id]);
        } catch (\Throwable $th) {
            logger()->error('Error al generar factura: ' . $th->getMessage());
            Notification::make()->title('Error al generar la factura')->danger()->body('Verifica los datos o consulta al administrador.')->send();
        }
    }
}
