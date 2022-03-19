<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jornada extends Model
{
    protected $table = 'ly_catjornadas';
    protected $primaryKey = 'JornadaID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'JornadaID', 'TemporadaID', 'Jornada', 'Fecha', 'Activo'
    ];
}