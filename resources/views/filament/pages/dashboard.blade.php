<x-filament-panels::page>
    @php
        $cantidadCarrito = $this->cantidadCarrito;
        $carrito = $this->carrito;
    @endphp

    {{-- Botón "Ver carrito" con contador --}}
    <div class="flex justify-end mb-4">
        <button wire:click="$dispatch('open-modal', { id: 'carrito-modal' })"
            class="relative inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-md shadow transition"
            style="background-color: #32E0C4;" onmouseover="this.style.backgroundColor='#29c4ab'"
            onmouseout="this.style.backgroundColor='#32E0C4'">
            🛒 Ver carrito

            @if ($cantidadCarrito > 0)
                <span
                    class="absolute -top-2 -right-2 bg-black text-xs font-bold px-2 py-0.5 rounded-full shadow-lg ring-2 ring-white"
                    style="color: #d80d40;">
                    {{ $cantidadCarrito }}
                </span>
            @endif
        </button>
    </div>

    {{-- Tabla de productos --}}
    {{ $this->table }}

    {{-- Modal del carrito --}}
    <x-filament::modal id="carrito-modal" width="5xl" slide-over>
        <x-slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 lg:px-8 py-4 gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-3xl text-teal-500">🛒</span>
                    <h2 class="text-2xl font-semibold text-gray-900">Carrito de Compras</h2>
                </div>
                <div class="flex items-center ml-6">
                    <label class="inline-flex items-center space-x-2">
                        <input type="checkbox" wire:model="facturaSinCai"
                            class="rounded border-gray-300 text-teal-600 shadow-sm focus:ring-teal-500">
                        <span class="text-gray-700 text-sm">Factura sin CAI</span>
                    </label>
                </div>
            </div>
        </x-slot>

        <div class="px-4 sm:px-6 lg:px-8 py-4 space-y-6 overflow-y-auto">
            @if (count($carrito))
                <x-filament::card class="p-4 space-y-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-[600px] w-full table-auto text-sm text-gray-700">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="px-3 py-2 text-left">Producto</th>
                                    <th class="px-3 py-2 text-right">Precio</th>
                                    <th class="px-3 py-2 text-center">ISV</th>
                                    <th class="px-3 py-2 text-center">Cant.</th>
                                    <th class="px-3 py-2 text-right">Subtotal</th>
                                    <th class="px-3 py-2 text-right">ISV Total</th>
                                    <th class="px-3 py-2 text-right">Total</th>
                                    <th class="px-3 py-2 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($carrito as $item)
                                    @php
                                        $pct = floatval($item['isv'] ?? 0);
                                        $sub = $item['precio'] * $item['cantidad'];
                                        $tax = $sub * ($pct / 100);
                                        $tot = $sub + $tax;
                                    @endphp

                                    @if ($item['cantidad'] > 0)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-3 py-2">{{ $item['nombre'] }}</td>
                                            <td class="px-3 py-2 text-right">L {{ number_format($item['precio'], 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <span class="bg-gray-100 px-2 py-0.5 rounded-full text-xs">
                                                    {{ number_format($pct, 0) }}%
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <div class="inline-flex items-center space-x-1">
                                                    @if (!($item['fijo'] ?? false))
                                                        <x-filament::button size="sm" color="gray"
                                                            wire:click="modificarCantidad('{{ $item['uid'] }}', -1)">–</x-filament::button>
                                                    @endif

                                                    <span
                                                        class="w-5 text-center font-medium">{{ $item['cantidad'] }}</span>

                                                    @if (!($item['fijo'] ?? false))
                                                        <x-filament::button size="sm" color="gray"
                                                            wire:click="modificarCantidad('{{ $item['uid'] }}', 1)">+</x-filament::button>
                                                    @endif

                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-right">L {{ number_format($sub, 2) }}</td>
                                            <td class="px-3 py-2 text-right">L {{ number_format($tax, 2) }}</td>
                                            <td class="px-3 py-2 text-right font-semibold text-teal-600">L
                                                {{ number_format($tot, 2) }}</td>
                                            <td class="px-3 py-2 text-center">
                                                @if (!($item['fijo'] ?? false))
                                                    <x-filament::button size="sm" color="danger"
                                                        wire:click="quitarDelCarrito('{{ $item['uid'] }}')">✕</x-filament::button>
                                                @endif

                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-filament::card>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <x-filament::card class="p-6 space-y-4">
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal (sin ISV)</span>
                            <span class="font-semibold">L {{ number_format($this->subtotalSinISV, 2) }}</span>
                        </div>
                        @php $d = $this->isvDesglosado; @endphp
                        @if ($d['isv15'] > 0)
                            <div class="flex justify-between text-gray-700">
                                <span>ISV 15%</span>
                                <span class="font-semibold">L {{ number_format($d['isv15'], 2) }}</span>
                            </div>
                        @endif
                        @if ($d['isv18'] > 0)
                            <div class="flex justify-between text-gray-700">
                                <span>ISV 18%</span>
                                <span class="font-semibold">L {{ number_format($d['isv18'], 2) }}</span>
                            </div>
                        @endif
                        <div class="border-t pt-3 flex justify-between text-gray-700">
                            <span>Total ISV</span>
                            <span class="font-semibold">L {{ number_format($this->totalISV, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Bruto</span>
                            <span class="font-semibold">L
                                {{ number_format($this->subtotalSinISV + $this->totalISV, 2) }}</span>
                        </div>
                    </x-filament::card>

                    <x-filament::card class="p-6 space-y-4">
                        <label class="block text-gray-700 font-medium">Descuento (Lps)</label>
                        <input type="number" wire:model.defer="descuento" wire:change="$refresh"
                            class="w-full border-gray-300 rounded-lg px-3 py-2 focus:ring-teal-500 focus:border-teal-500" />
                        <div class="border-t pt-4 flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-800">Total Final</span>
                            <span class="text-2xl font-bold text-teal-600">
                                L {{ number_format($this->totalFinal, 2) }}
                            </span>
                        </div>
                    </x-filament::card>
                </div>

                <x-filament::card class="p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800">🧑‍💼 Datos del Cliente</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-700">Nombre</label>
                            <input type="text" wire:model.defer="nombre_cliente" placeholder="Juan Pérez"
                                class="w-full border-gray-300 rounded-lg px-3 py-2 focus:ring-teal-500 focus:border-teal-500" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700">RTN</label>
                            <input type="text" wire:model.defer="rtn_cliente" placeholder="0000-0000-00000"
                                class="w-full border-gray-300 rounded-lg px-3 py-2 focus:ring-teal-500 focus:border-teal-500" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm text-gray-700">Dirección</label>
                            <textarea wire:model.defer="direccion_cliente" rows="2" placeholder="Colonia, Ciudad, País"
                                class="w-full border-gray-300 rounded-lg px-3 py-2 resize-none focus:ring-teal-500 focus:border-teal-500"></textarea>
                        </div>
                    </div>
                </x-filament::card>
            @else
                <p class="text-center text-gray-500">El carrito está vacío.</p>
            @endif
        </div>

        <x-slot name="footer">
            <div class="flex flex-col sm:flex-row justify-end gap-3 px-4 sm:px-6 lg:px-8 py-4">
                <x-filament::button color="danger" wire:click="descartarVenta" class="w-full sm:w-auto">
                    🗑️ Descartar venta
                </x-filament::button>
                <x-filament::button color="primary" wire:click="imprimirFactura" wire:loading.attr="disabled"
                    wire:target="imprimirFactura" class="w-full sm:w-auto">
                    <span wire:loading.remove wire:target="imprimirFactura">🧾 Imprimir factura</span>
                    <span wire:loading wire:target="imprimirFactura">⌛ Generando...</span>
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
