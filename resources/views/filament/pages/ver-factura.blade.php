<x-filament-panels::page>
    {{-- Botón de impresión superior derecho --}}
    <div class="flex justify-end mb-4 no-print">
        <x-filament::button color="primary" onclick="imprimirFactura()">
            🖨️ Imprimir Factura
        </x-filament::button>
    </div>

    <div id="factura-imprimible" class="text-xs font-mono leading-tight">
        {{-- Encabezado --}}
        <div class="text-center mb-2">
            @if ($factura->empresa->logo)
                <img src="{{ asset('storage/' . $factura->empresa->logo) }}" class="mx-auto mb-1"
                    style="max-height: 60px;">
            @endif
            <strong>{{ $factura->empresa->nombre }}</strong><br>
            RTN: {{ $factura->empresa->rtn }}<br>
            {{ $factura->empresa->direccion }}<br>
            Fecha: {{ $factura->created_at->format('d/m/Y H:i') }}<br>
            Factura #: {{ $factura->numero_factura }}<br>
            Cajero: {{ $factura->user->name }}<br>
        </div>

        {{-- Cliente --}}
        <div class="mb-2">
            <strong>Cliente:</strong> {{ $factura->cliente_nombre ?? '-' }}<br>
            <strong>RTN:</strong> {{ $factura->cliente_rtn ?? '-' }}<br>
            <strong>Dirección:</strong> {{ $factura->cliente_direccion ?? '-' }}
        </div>

        {{-- Tabla productos --}}
        <table class="w-full text-xs mb-2">
            <thead>
                <tr class="border-t border-b border-black">
                    <th class="text-left w-10">Cant</th>
                    <th class="text-left">Producto</th>
                    <th class="text-right w-16">P/U</th>
                    <th class="text-right w-16">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($factura->productos as $prod)
                    <tr>
                        <td>{{ $prod->cantidad }}</td>
                        <td style="white-space: normal;">
                            {{ ucfirst($prod->nombre) }}
                        </td>
                        <td class="text-right">L{{ number_format($prod->precio_unitario, 2) }}</td>
                        <td class="text-right">L{{ number_format($prod->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totales --}}
        <div class="border-t border-black pt-1 text-sm">
            <div class="flex justify-between">
                <span>Subtotal</span>
                <span>L {{ number_format($factura->subtotal_sin_isv, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span>ISV</span>
                <span>L {{ number_format($factura->total_isv, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Descuento</span>
                <span>L {{ number_format($factura->descuento, 2) }}</span>
            </div>
            <div class="flex justify-between font-bold text-base border-t mt-1 pt-1">
                <span>Total</span>
                <span>L {{ number_format($factura->total_final, 2) }}</span>
            </div>
        </div>

        <div class="text-center mt-2">
            ¡Gracias por su compra!
        </div>
    </div>


    {{-- Estilos impresión --}}
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            #factura-imprimible,
            #factura-imprimible * {
                visibility: visible;
            }

            #factura-imprimible {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 0;
                margin: 0;
                background: white !important;
                color: black !important;
                border-color: black !important;
            }

            #factura-imprimible img {
                display: block !important;
                max-height: 64px;
                margin: 0 auto 8px auto;
            }

            #factura-imprimible th,
            #factura-imprimible td,
            #factura-imprimible div,
            #factura-imprimible p,
            #factura-imprimible h1,
            #factura-imprimible h2,
            #factura-imprimible h3,
            #factura-imprimible h4,
            #factura-imprimible h5,
            #factura-imprimible h6 {
                background: white !important;
                color: black !important;
                border-color: black !important;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>

    {{-- Script impresión --}}
    <script>
        function imprimirFactura() {
            window.print();
        }
    </script>
</x-filament-panels::page>
