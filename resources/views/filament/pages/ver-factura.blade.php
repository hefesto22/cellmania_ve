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
            @else
                <div class="text-center mb-1 font-bold text-sm"
                    style="height: 60px; display: flex; align-items: center; justify-content: center;">
                    TU LOGO
                </div>
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
                <tr class="border-b border-black">
                    <th colspan="3" class="text-left">DESCRIPCIÓN</th>
                </tr>
                <tr>
                    <th class="text-left w-1/3">CANT</th>
                    <th class="text-right w-1/3">P/U</th>
                    <th class="text-right w-1/3">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($factura->productos as $prod)
                    {{-- Primera línea: nombre del producto --}}
                    <tr>
                        <td colspan="3" class="text-left font-semibold uppercase" style="white-space: normal;">
                            {{ $prod->nombre }}
                        </td>
                    </tr>
                    {{-- Segunda línea: cantidad, precio unitario, total --}}
                    <tr>
                        <td class="text-left">{{ $prod->cantidad }}</td>
                        <td class="text-right">L{{ number_format($prod->precio_unitario, 2) }}</td>
                        <td class="text-right">L{{ number_format($prod->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $grav15 = 0;
            $isv15 = 0;
            $grav18 = 0;
            $isv18 = 0;

            foreach ($factura->productos as $p) {
                $sub = $p->precio_unitario * $p->cantidad;
                if ($p->porcentaje_isv == 15) {
                    $grav15 += $sub;
                    $isv15 += $sub * 0.15;
                }
                if ($p->porcentaje_isv == 18) {
                    $grav18 += $sub;
                    $isv18 += $sub * 0.18;
                }
            }
        @endphp

        <div class="border-t border-black pt-1 text-xs font-mono leading-tight">
            <div class="flex justify-between">
                <span>Importe Exento</span>
                <span>L 0.00</span>
            </div>
            <div class="flex justify-between">
                <span>Importe grav 15%</span>
                <span>L {{ number_format($grav15, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Importe grav 18%</span>
                <span>L {{ number_format($grav18, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span>ISV 15%</span>
                <span>L {{ number_format($isv15, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span>ISV 18%</span>
                <span>L {{ number_format($isv18, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Dscto</span>
                <span>L {{ number_format($factura->descuento, 2) }}</span>
            </div>
            <div class="flex justify-between font-bold text-sm border-t mt-1 pt-1">
                <span>Total</span>
                <span>L {{ number_format($factura->total_final, 2) }}</span>
            </div>
        </div>
        <div class="mt-1 text-xs font-bold uppercase tracking-wide">
            {{ \App\Helpers\NumeroHelper::numeroALetras($factura->total_final) }} LEMPIRAS CON
            {{ str_pad(($factura->total_final - floor($factura->total_final)) * 100, 2, '0', STR_PAD_LEFT) }}/100
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
