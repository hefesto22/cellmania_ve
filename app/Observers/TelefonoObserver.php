<?php

namespace App\Observers;

use App\Models\Telefono;
use Illuminate\Support\Facades\Log;

class TelefonoObserver
{
    public function saved(Telefono $telefono): void
    {
        if ($telefono->stock <= 0 && $telefono->estado === 'Disponible') {
            $telefono->estado = 'Vendido';
            $telefono->save();
            Log::info("📱 Teléfono ID {$telefono->id} se marcó como 'Vendido' automáticamente.");
        }

        if ($telefono->stock > 0 && $telefono->estado === 'Vendido') {
            $telefono->estado = 'Disponible';
            $telefono->save();
            Log::info("📱 Teléfono ID {$telefono->id} se volvió a marcar como 'Disponible' porque tiene stock.");
        }
    }
}
