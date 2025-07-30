<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Telefono extends Model
{
    use HasFactory;

    protected $table = 'telefonos';

    protected $fillable = [
        'marca_id',
        'categoria_id',
        'modelo',
        'almacenamiento',
        'ram',
        'color',
        'precio_compra',
        'precio_venta',
        'isv', // 👈 Campo agregado
        'stock',
        'codigo_barras',
        'imei',
        'accesorios',
        'estado',
        'usuario_id',
    ];


    /**
     * Relación con el usuario que registró el teléfono.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relación con la marca del teléfono.
     */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }

    /**
     * Relación con la categoría del teléfono.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function accesorios()
    {
        return $this->hasMany(\App\Models\AccesorioTelefono::class);
    }
}
