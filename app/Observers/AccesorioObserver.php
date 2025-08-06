<?php

namespace App\Observers;

use App\Models\Accesorio;
use Illuminate\Support\Facades\Log;

class AccesorioObserver
{
    public function saved(Accesorio $accesorio): void
    {
        if ($accesorio->stock <= 0 && $accesorio->estado === 'Disponible') {
            $accesorio->estado = 'Vendido';
            $accesorio->save();
            Log::info("📦 Accesorio ID {$accesorio->id} se marcó como 'Vendido' automáticamente.");
        }

        if ($accesorio->stock > 0 && $accesorio->estado === 'Vendido') {
            $accesorio->estado = 'Disponible';
            $accesorio->save();
            Log::info("📦 Accesorio ID {$accesorio->id} se volvió a marcar como 'Disponible' porque tiene stock.");
        }
    }
}
