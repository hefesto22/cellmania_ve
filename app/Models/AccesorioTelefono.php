<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccesorioTelefono extends Model
{
    protected $table = 'accesorio_telefono';

    protected $fillable = [
        'telefono_id',
        'nombre',
        'codigo_barras',
        'precio_compra',
        'precio_venta',
        'isv',
        'stock',
    ];

    public function telefono()
    {
        return $this->belongsTo(Telefono::class);
    }
}
