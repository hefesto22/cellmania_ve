<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accesorio extends Model
{
    protected $fillable = [
        'nombre',
        'codigo_barras',
        'precio_compra',
        'precio_venta',
        'isv', // 👈 Campo agregado
        'stock',
        'estado',
        'marca_id',
        'categoria_id',
        'created_by',
    ];


    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function telefonos()
    {
        return $this->belongsToMany(\App\Models\Telefono::class, 'accesorio_telefono');
    }
}
