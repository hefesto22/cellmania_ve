<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Telefono extends Model
{
    use HasFactory;

    protected $table = 'telefonos';

    protected $fillable = [
        'marca',
        'modelo',
        'almacenamiento',
        'ram',
        'color',
        'precio_compra',
        'precio_venta',
        'stock',
        'codigo_barras',
        'estado',
        'usuario_id',
    ];

    /**
     * Relación con el usuario que registró el teléfono.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

}
