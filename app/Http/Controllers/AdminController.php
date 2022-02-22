<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function obtenerUsuariosTemp(Request $res)
    {
        $busqueda = "%".$res["busqueda"]."%";
        $temporada_id = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $usuarios = DB::table('ly_encinscripciones as lye')
        ->select(DB::raw('CONCAT(NomRep, " ", APatRep, " ", AMatRep ) as nombre'), "TelRep", "lyu.usuario","lyu.UsuarioID","lyc.Equipo")
        ->join("ly_catequipos as lyc","lyc.EquipoID","=","lye.EquipoID")
        ->join("ly_usaurios as lyu","lyu.UsuarioID","=","lye.UsuarioID")
        ->where(function ($query) use ($busqueda){
            if($busqueda != ""){
                $query->orWhere(DB::raw('CONCAT(NomRep, " ", APatRep, " ", AMatRep )'), "like", $busqueda)
                ->orWhere("lyu.usuario", "like", $busqueda)
                ->orWhere("lyc.Equipo", "like", $busqueda);
            }
        })
        ->where("lye.TemporadaID",$temporada_id->TemporadaID)
        ->get();
        if(count($usuarios)>0){
            return $this->crearRespuesta(1,$usuarios,200);
        }
        return $this->crearRespuesta(2,"No se ha encontrado usuario",200);
    }
    public function getContra($id_usuario){
        $usuario = DB::table('ly_usaurios')
        ->select("contra","usuario")
        ->where("UsuarioID",$id_usuario)
        ->first();
        if($usuario){
            $usuario->contra = $this->decode_json($usuario->contra);
            return $this->crearRespuesta(1,$usuario,200);
        }
        return $this->crearRespuesta(2,"No se ha encontrado el usuario",200);
    }
    //
}
