<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;
    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'user_id',
    ];

    /**
     * Relación: una sucursal tiene muchos usuarios.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
