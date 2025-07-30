<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DatosEmpresa extends Model
{
    use HasFactory;

    protected $table = 'datos_empresa';

    protected $fillable = [
        'nombre',
        'rtn',
        'cai',
        'telefono',
        'direccion',
        'email',
        'lema',
        'rango_desde',
        'rango_hasta',
        'numero_actual',
        'fecha_limite_emision',
        'logo', // ✅ Nuevo campo para almacenar la ruta del logo
        'user_id',
    ];

    /**
     * Relación con el usuario que configuró la empresa.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con las facturas emitidas por esta empresa.
     */
    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }
}
