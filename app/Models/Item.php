<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public $timestamps = false;

    protected $table = 'items'; // Tabla virtual (subquery con unionAll)

    protected $fillable = [
        'id',
        'uid',
        'tipo',
        'nombre',
        'precio_venta',
        'stock',
        'isv',
        'codigo_barras',
        'user_id',
    ];

    // Opcional: deshabilita claves primarias automáticas
    protected $primaryKey = 'uid';
    public $incrementing = false;
    protected $keyType = 'string';
}
