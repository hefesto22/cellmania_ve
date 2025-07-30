<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $fillable = ['nombre', 'created_by'];

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function telefonos(): HasMany
    {
        return $this->hasMany(Telefono::class);
    }

    public function accesorios(): HasMany
    {
        return $this->hasMany(Accesorio::class);
    }
}
