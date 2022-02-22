<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    protected $table = 'ly_encinscripciones';
    protected $primaryKey = 'InscripcionID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'InscripcionID', 'Inscripcion', 'FechaInscripcion', 'Fecha', 'EquipoID', 'TemporadaID', 'CategoriaID', 'Clasificacion', 'Grupo', 'Entrenador', 'APatEnt', 'AMatEnt', 'NomEnt', 'CurpEnt', 'MailEnt', 'TelEnt', 'Representante', 'APatRep', 'AMatRep', 'NomRep', 'CurpRep', 'MailRep', 'TelRep', 'PosicionTabla', 'UsuarioID', 'APatAy', 'AMatAy', 'NomAy', 'CurpAy', 'MailAy', 'TelAy', 'FotoEnt', 'FotoRep', 'FotoAy', 'FotoRecibo', 'ExtRecibo', 'Editable'
    ];
}