<?php

namespace App\Http\Controllers;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InicioController extends Controller
{
    public function obtenerDatos($id_usuario)
    {
        $temporada_id = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $datos = Inscripcion::select(DB::RAW("CONCAT(APatEnt,' ',AMatEnt,' ',NomEnt) as Entrenador"), "CurpEnt","MailEnt","TelEnt",DB::RAW("CONCAT(APatRep,' ',AMatRep,' ',NomRep) as Representante"),"CurpRep","MailRep","TelRep",DB::RAW("CONCAT(APatAy,' ',AMatAy,' ',NomAy) as Ayudante"),"CurpAy","MailAy","TelAy","InscripcionID","Equipo","FotoRep","FotoEnt","FotoAy")
        ->leftJoin('ly_catequipos as ce', 'ly_encinscripciones.EquipoID', '=', 'ce.EquipoID')
        ->where("UsuarioID",$id_usuario)
        ->where("TemporadaID",$temporada_id->TemporadaID)
        ->first();
        if($datos){
            if($datos->FotoRep != ""){
                $datos->FotoRep = Storage::disk('fotos')->url($datos->FotoRep);
            }else{
                $datos->FotoRep = "../assets/logos/avatar.jpg";
            }
            if($datos->FotoEnt != ""){
                $datos->FotoEnt = Storage::disk('fotos')->url($datos->FotoEnt);
            }else{
                $datos->FotoEnt = "../assets/logos/avatar.jpg";
            }
            if($datos->FotoAy != ""){
                $datos->FotoAy = Storage::disk('fotos')->url($datos->FotoAy);
            }else{
                $datos->FotoAy = "../assets/logos/avatar.jpg";
            }
            if($datos->Entrenador == "  "){
                $datos->Entrenador = "NO DISPONIBLE";
            }
            if($datos->CurpEnt == ""){
                $datos->CurpEnt = "NO DISPONIBLE";
            }
            if($datos->MailEnt == ""){
                $datos->MailEnt = "NO DISPONIBLE";
            }
            if($datos->TelEnt == ""){
                $datos->TelEnt = "NO DISPONIBLE";
            }
            if($datos->Representante == "  "){
                $datos->Representante = "NO DISPONIBLE";
            }
            if($datos->CurpRep == ""){
                $datos->CurpRep = "NO DISPONIBLE";
            }
            if($datos->MailRep == ""){
                $datos->MailRep = "NO DISPONIBLE";
            }
            if($datos->TelRep == ""){
                $datos->TelRep = "NO DISPONIBLE";
            }
            if($datos->Ayudante == "  "){
                $datos->Ayudante = "NO DISPONIBLE";
            }
            if($datos->CurpAy == ""){
                $datos->CurpAy = "NO DISPONIBLE";
            }
            if($datos->MailAy == ""){
                $datos->MailAy = "NO DISPONIBLE";
            }
            if($datos->TelAy == ""){
                $datos->TelAy = "NO DISPONIBLE";
            }
            $mis_jugadores = DB::table('ly_detinscripciones as lyd')
            ->select(DB::raw("CONCAT(lyc.ApellidoPaterno,' ',lyc.ApellidoMaterno,' ',lyc.Nombre) as nombre"),"lyc.FechaNacimiento","lyc.TelPadre","lyc.TelMadre","lyc.JugadorID")
            ->join("ly_catjugadores as lyc","lyc.JugadorID","=","lyd.JugadorID")
            ->where("lyd.InscripcionID",$datos->InscripcionID)
            ->where("lyd.activo",1)
            ->get();
            $datos->jugadores = [];
            if(count($mis_jugadores)>0){
                $datos->jugadores = $mis_jugadores;
            }
            return $this->crearRespuesta(1,$datos,200);
        }
        return $this->crearRespuesta(2,"No se han encontrado los datos",200);
    }
    public function obtenerDatosPorId($inscripcion_id)
    {
        $datos = Inscripcion::select(DB::RAW("CONCAT(APatEnt,' ',AMatEnt,' ',NomEnt) as Entrenador"), "CurpEnt","MailEnt","TelEnt",DB::RAW("CONCAT(APatRep,' ',AMatRep,' ',NomRep) as Representante"),"CurpRep","MailRep","TelRep",DB::RAW("CONCAT(APatAy,' ',AMatAy,' ',NomAy) as Ayudante"),"CurpAy","MailAy","TelAy","InscripcionID","Equipo","Categoria")
        ->leftJoin('ly_catequipos as ce', 'ly_encinscripciones.EquipoID', '=', 'ce.EquipoID')
        ->leftJoin('ly_catcategorias as lcc',"lcc.CategoriaID","ly_encinscripciones.CategoriaID")
        ->where("InscripcionID",$inscripcion_id)
        ->first();
        if($datos){
            if($datos->Entrenador == "  "){
                $datos->Entrenador = "NO DISPONIBLE";
            }
            if($datos->CurpEnt == ""){
                $datos->CurpEnt = "NO DISPONIBLE";
            }
            if($datos->MailEnt == ""){
                $datos->MailEnt = "NO DISPONIBLE";
            }
            if($datos->TelEnt == ""){
                $datos->TelEnt = "NO DISPONIBLE";
            }
            if($datos->Representante == "  "){
                $datos->Representante = "NO DISPONIBLE";
            }
            if($datos->CurpRep == ""){
                $datos->CurpRep = "NO DISPONIBLE";
            }
            if($datos->MailRep == ""){
                $datos->MailRep = "NO DISPONIBLE";
            }
            if($datos->TelRep == ""){
                $datos->TelRep = "NO DISPONIBLE";
            }
            if($datos->Ayudante == "  "){
                $datos->Ayudante = "NO DISPONIBLE";
            }
            if($datos->CurpAy == ""){
                $datos->CurpAy = "NO DISPONIBLE";
            }
            if($datos->MailAy == ""){
                $datos->MailAy = "NO DISPONIBLE";
            }
            if($datos->TelAy == ""){
                $datos->TelAy = "NO DISPONIBLE";
            }
            $mis_jugadores = DB::table('ly_detinscripciones as lyd')
            ->select(DB::raw("CONCAT(lyc.ApellidoPaterno,' ',lyc.ApellidoMaterno,' ',lyc.Nombre) as nombre"),"lyc.FechaNacimiento","lyc.TelPadre","lyc.TelMadre","lyc.JugadorID")
            ->join("ly_catjugadores as lyc","lyc.JugadorID","=","lyd.JugadorID")
            ->where("lyd.InscripcionID",$datos->InscripcionID)
            ->where("lyd.activo",1)
            ->get();
            $datos->jugadores = [];
            if(count($mis_jugadores)>0){
                $datos->jugadores = $mis_jugadores;
            }
            return $this->crearRespuesta(1,$datos,200);
        }
        return $this->crearRespuesta(2,"No se han encontrado los datos",200);
    }
    public function EditarDatos(Request $res)
    {
        //Validaciones
        if(isset($res["nombre"]) && strlen($res["nombre"]) == 0){
            return $this->crearRespuesta(2,"El campo nombre es obligatorio",200);
        }
        if(isset($res["apellido_p"]) && strlen($res["apellido_p"]) == 0){
            return $this->crearRespuesta(2,"El campo apellido paterno es obligatorio",200);
        }
        if(isset($res["telefono"]) && strlen($res["telefono"]) == 0){
            return $this->crearRespuesta(2,"El campo telefono es obligatorio",200);
        }
        try{
            $temporada_actual = $temporada = DB::table('ly_cattemporadas')->where("Actual",1)->first();
            $inscripcion = Inscripcion::find($res["InscripcionID"]);
            if($res["tipo"] == 1){  //Representante
                $path = "";
                if($res["extension"] != "" && $res["foto"] != ""){
                    $file = base64_decode($res["foto"]);
                    $path = "Entrenadores/".$temporada_actual->Temporada."/F-".strtoupper($res["nombre"])."_".strtoupper($res["apellido_p"])."_".strtoupper($res["apellido_m"]).".".$res["extension"];
                    Storage::disk('fotos')->put($path, $file);
                }
                $inscripcion->APatRep = strtoupper($res["apellido_p"]);
                $inscripcion->AMatRep = strtoupper($res["apellido_m"]);
                $inscripcion->NomRep = strtoupper($res["nombre"]);
                $inscripcion->CurpRep = strtoupper($res["curp"]);
                $inscripcion->MailRep = $res["correo"];
                $inscripcion->TelRep = strtoupper($res["tel"]);
                $inscripcion->FotoRep = $path;
            }
            if($res["tipo"] == 2){  //Entrenado
                $path = "";
                if($res["extension"] != "" && $res["foto"] != ""){
                    $file = base64_decode($res["foto"]);
                    $path = "Entrenadores/".$temporada_actual->Temporada."/F-".strtoupper($res["nombre"])."_".strtoupper($res["apellido_p"])."_".strtoupper($res["apellido_m"]).".".$res["extension"];
                    Storage::disk('fotos')->put($path, $file);
                }
                $inscripcion->APatEnt = strtoupper($res["apellido_p"]);
                $inscripcion->AMatEnt = strtoupper($res["apellido_m"]);
                $inscripcion->NomEnt = strtoupper($res["nombre"]);
                $inscripcion->CurpEnt = strtoupper($res["curp"]);
                $inscripcion->MailEnt = $res["correo"];
                $inscripcion->TelEnt = strtoupper($res["tel"]);
                $inscripcion->FotoEnt = $path;
            }
            if($res["tipo"] == 3){  //Ayudante
                $path = "";
                if($res["extension"] != "" && $res["foto"] != ""){
                    $file = base64_decode($res["foto"]);
                    $path = "Entrenadores/".$temporada_actual->Temporada."/F-".strtoupper($res["nombre"])."_".strtoupper($res["apellido_p"])."_".strtoupper($res["apellido_m"]).".".$res["extension"];
                    Storage::disk('fotos')->put($path, $file);
                }
                $inscripcion->APatAy = strtoupper($res["apellido_p"]);
                $inscripcion->AMatAy = strtoupper($res["apellido_m"]);
                $inscripcion->NomAy = strtoupper($res["nombre"]);
                $inscripcion->CurpAy = strtoupper($res["curp"]);
                $inscripcion->MailAy = $res["correo"];
                $inscripcion->TelAy = strtoupper($res["tel"]);
                $inscripcion->FotoAy = $path;
            }
            $inscripcion->save();
            return $this->crearRespuesta(1,"Edicion completada",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),200);
        }
    }
}
