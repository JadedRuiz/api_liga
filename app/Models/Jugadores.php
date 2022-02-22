<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jugadores extends Model
{
    protected $table = 'ly_catjugadores';
    protected $primaryKey = 'JugadorID';
    public $timestamps = false;
    protected $fillable = [
        'JugadorID', 'ApellidoPaterno', 'ApellidoMaterno', 'Nombre', 'FechaNacimiento', 'Domicilio', 'Colonia', 'Telefono', 'Municipio', 'EstadoID', 'Sexo', 'Curp', 'Estado', 'CodigoPostal', 'Fotografia', 'APRepresentante', 'AMRepresentante', 'NRepresentante', 'CorreoRepresentante', 'TelRepresentante', 'APMadre', 'AMMadre', 'NMadre', 'TelMadre', 'APPadre', 'AMPadre', 'NPadre', 'TelPadre', 'EstatusID', 'Notas'
    ];
}