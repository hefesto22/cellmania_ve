<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductoFactura extends Model
{
    use HasFactory;

    protected $fillable = [
        'factura_id',
        'tipo',
        'referencia_id', 
        'nombre',// 👈 Campo nuevo
        'cantidad',
        'precio_unitario',
        'porcentaje_isv',
        'total_isv',
        'subtotal',
        'total',
        'costo',
    ];

    protected $table = 'productosfactura';

    // 🔗 Relación con la factura
    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    // 🔗 Relación dinámica con el producto original según tipo (opcional)
    public function referencia()
    {
        return $this->morphTo(__FUNCTION__, 'tipo', 'referencia_id');
    }
}
