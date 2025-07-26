<x-filament-panels::page>
    {{-- Botón de carrito --}}
    <div class="flex justify-end mb-4">
        <button class="relative inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-md shadow transition"
            style="background-color: #32E0C4;" onmouseover="this.style.backgroundColor='#29c4ab'"
            onmouseout="this.style.backgroundColor='#32E0C4'">
            🛒 Carrito

            @if ($cantidadCarrito > 0)
                <span
                    class="absolute -top-2 -right-2 bg-black text-xs font-bold px-2 py-0.5 rounded-full shadow-lg ring-2 ring-white"
                    style="color: #d80d40;">
                    {{ $cantidadCarrito }}
                </span>
            @endif
        </button>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
