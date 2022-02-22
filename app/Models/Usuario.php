<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'ly_usaurios';
    protected $primaryKey = 'UsuarioID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'UsuarioID', 'usuario', 'contra', 'perfil', 'activo'
    ];
}