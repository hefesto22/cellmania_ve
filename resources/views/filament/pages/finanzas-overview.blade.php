<x-filament-panels::page>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold">Resumen Financiero</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-green-100 border-l-4 border-green-500 rounded shadow">
                <h3 class="text-lg font-semibold text-green-700">Total Facturado</h3>
                <p class="text-2xl font-bold text-green-800">
                    L {{ number_format($this->getTotalFacturado(), 2) }}
                </p>
            </div>

            <div class="p-4 bg-red-100 border-l-4 border-red-500 rounded shadow">
                <h3 class="text-lg font-semibold text-red-700">Total Gastos</h3>
                <p class="text-2xl font-bold text-red-800">
                    L {{ number_format($this->getTotalGastos(), 2) }}
                </p>
            </div>

            <div class="p-4 bg-blue-100 border-l-4 border-blue-500 rounded shadow">
                <h3 class="text-lg font-semibold text-blue-700">Balance Neto</h3>
                <p class="text-2xl font-bold text-blue-800">
                    L {{ number_format($this->getBalance(), 2) }}
                </p>
            </div>
        </div>

        {{ $this->table }}
    </div>
    @php
        $user = auth()->user();
        $inversion = $this->getInversionPorTipo();
    @endphp

    @if (!method_exists($user, 'hasRole') || !$user->hasRole('Encargado'))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Inversión en Teléfonos --}}
            <div class="bg-white shadow rounded-lg p-4">
                <div class="text-sm text-gray-600">Inversión en Teléfonos</div>
                <div class="text-2xl font-bold">L {{ number_format($inversion['telefonos'], 2) }}</div>
            </div>

            {{-- Inversión en Accesorios Individuales --}}
            <div class="bg-white shadow rounded-lg p-4">
                <div class="text-sm text-gray-600">Inversión en Accesorios</div>
                <div class="text-2xl font-bold">L {{ number_format($inversion['accesorios'], 2) }}</div>
            </div>

            {{-- Inversión en Accesorios de Teléfonos --}}
            <div class="bg-white shadow rounded-lg p-4">
                <div class="text-sm text-gray-600">Inversión en Accesorios de Teléfonos</div>
                <div class="text-2xl font-bold">L {{ number_format($inversion['accesorios_telefono'], 2) }}</div>
            </div>
        </div>
    @endif



</x-filament-panels::page>
