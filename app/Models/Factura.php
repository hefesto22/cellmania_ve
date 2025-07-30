<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Factura extends Model
{
    use HasFactory;
    protected $table = 'facturas';
    protected $fillable = [
        'numero_factura',
        'cai',
        'fecha_emision',
        'cliente_nombre',
        'cliente_rtn',
        'cliente_direccion',
        'subtotal_sin_isv',
        'total_isv',
        'bruto',
        'descuento',
        'total_final',
        'datos_empresa_id',
        'user_id',

    ];

    // 🔗 Relación con empresa emisora
    public function empresa()
    {
        return $this->belongsTo(DatosEmpresa::class, 'datos_empresa_id');
    }

    // 🔗 Relación con el usuario que creó la factura
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔗 Relación con productos en la factura
    public function productos()
    {
        return $this->hasMany(ProductoFactura::class);
    }
}
