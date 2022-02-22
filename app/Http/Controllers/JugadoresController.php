<?php

namespace App\Http\Controllers;
use App\Models\Jugadores;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DateTime;

class JugadoresController extends Controller
{
    public function busquedaJugadores(Request $res){
        $palabra = "%".$res["busqueda"]."%";
        $jugadores = Jugadores::select("JugadorID",DB::raw("CONCAT(ApellidoPaterno,' ',ApellidoMaterno, ' ', Nombre) as nombre"),"FechaNacimiento","TelMadre","TelPadre")
        ->where(function($query) use ($palabra){
            $query->where(DB::raw("CONCAT(ApellidoPaterno,' ',ApellidoMaterno, ' ', Nombre)"),"like",$palabra)
            ->orWhere('Curp','like',$palabra);
        })
        ->limit(50)
        ->get();
        if(count($jugadores)>0){
            return $this->crearRespuesta(1,$jugadores,200);
        }
        return $this->crearRespuesta(2,"No se ha encontrado jugadores",200);
    }
    
    public function obtenerJugadoresAdmin()
    {
        $jugadores = Jugadores::select("JugadorID",DB::raw("CONCAT(ApellidoPaterno,' ',ApellidoMaterno, ' ', Nombre) as nombre"),"FechaNacimiento","TelMadre","TelPadre")
        ->limit(1000)
        ->get();
        if(count($jugadores)>0){
            return $this->crearRespuesta(1,$jugadores,200);
        }
        return $this->crearRespuesta(2,"No se ha encontrado jugadores",200);
    }
    public function obtenerJugadorPorId($id_jugador)
    {
        $temporada_actual = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $jugador = Jugadores::select("JugadorID","Nombre","ApellidoPaterno","ApellidoMaterno","Curp","Sexo","Telefono","NPadre", "APPadre","AMPadre","NMadre","APMadre","AMMadre","NRepresentante","APRepresentante","AMRepresentante","Fotografia","JugadorID as Equipo","Domicilio as Calle","Domicilio as Num_int","Domicilio as Num_ext","Domicilio as Cruzamiento_uno","Domicilio as Cruzamiento_dos","Colonia","Municipio","FechaNacimiento as Edad","APRepresentante as apellido_p_rep","AMRepresentante as apellido_m_rep","NRepresentante as nombre_rep","CorreoRepresentante as correo_rep","TelRepresentante as tel_rep")
        ->where("JugadorID",$id_jugador)
        ->first();
        if($jugador){
            $get_equipo_actual = DB::table('ly_detinscripciones as lyd')
            ->select("lyc.Equipo")
            ->leftJoin("ly_encinscripciones as lye","lye.InscripcionID","=","lyd.InscripcionID")
            ->leftJoin("ly_catequipos as lyc","lyc.EquipoID","=","lye.EquipoID")
            ->where("lyd.JugadorID",$id_jugador)
            ->where("lye.TemporadaID",$temporada_actual->TemporadaID)
            ->where("lyd.activo",1)
            ->first();
            $jugador->Equipo = "AUN NO CUENTA CON EQUIPO ESTA TEMPORADA";
            if($get_equipo_actual){
                $jugador->Equipo = $get_equipo_actual->Equipo;
            }
            if($jugador->Fotografia != ""){
                $jugador->Fotografia = Storage::disk('fotos')->url($jugador->Fotografia);
            }else{
                $jugador->Fotografia = "../assets/logos/avatar.jpg";
            }
            $dir = explode("|",$jugador->Calle);
            $fecha_nacimiento = new DateTime($jugador->Edad);
            $hoy = new DateTime();
            $jugador->Edad = $hoy->diff($fecha_nacimiento)->y ." Años";
            $jugador->Nombre = utf8_decode($jugador->Nombre);
            $jugador->ApellidoPaterno = utf8_decode($jugador->ApellidoPaterno);
            $jugador->ApellidoMaterno = utf8_decode($jugador->ApellidoMaterno);
            $jugador->NPadre = utf8_decode($jugador->NPadre);
            $jugador->APPadre = utf8_decode($jugador->APPadre);
            $jugador->AMPadre = utf8_decode($jugador->AMPadre);
            $jugador->NMadre = utf8_decode($jugador->NMadre);
            $jugador->APMadre = utf8_decode($jugador->APMadre);
            $jugador->AMMadre = utf8_decode($jugador->AMMadre);
            $jugador->NRepresentante = utf8_decode($jugador->NRepresentante);
            $jugador->APRepresentante = utf8_decode($jugador->APRepresentante);
            $jugador->AMRepresentante = utf8_decode($jugador->AMRepresentante);
            if(count($dir)>1){
                $jugador->Calle = $dir[0];
                $jugador->Num_int = $dir[1];
                $jugador->Num_ext = $dir[2];
                $jugador->Cruzamiento_uno = $dir[3];
                $jugador->Cruzamiento_dos = $dir[4]; 
            }else{
                $jugador->Calle = "";
                $jugador->Num_int = "";
                $jugador->Num_ext = "";
                $jugador->Cruzamiento_uno = "";
                $jugador->Cruzamiento_dos = ""; 
            }
            return $this->crearRespuesta(1,$jugador,200);
        }
        return $this->crearRespuesta(2,"No se ha encontrado el jugador",200);
    }
    public function validarCurp(Request $res)
    {
        $curp = strtoupper($res["curp"]);
        $validar = DB::table('ly_catjugadores as lyc')
        ->select('lyc.ApellidoPaterno','lyc.ApellidoMaterno','lyc.Nombre',"lyc.Curp","lyc.FechaNacimiento","lyc.Domicilio","lyc.Domicilio as Calle","lyc.Domicilio as Num_int","lyc.Domicilio as Nun_ext","lyc.Domicilio as Cruzamiento_uno","lyc.Domicilio as Cruzamiento_dos","lyc.Colonia","lyc.Telefono","lyc.Municipio","lyc.Sexo","lyc.CodigoPostal","lyc.APMadre","lyc.AMMadre","lyc.NMadre","lyc.APPadre","lyc.AMPadre","lyc.NPadre","lyc.TelMadre","lyc.TelPadre","lyc.JugadorID","lyc.Fotografia","APRepresentante as apellido_p_rep","AMRepresentante as apellido_m_rep","NRepresentante as nombre_rep","CorreoRepresentante as correo_rep","TelRepresentante as tel_rep")
        ->where("lyc.Curp",$curp)
        ->orderBy("lyc.JugadorID","DESC")
        ->get();
        if(count($validar)>0){
            if($validar[0]->Fotografia != ""){
                $validar[0]->Fotografia = Storage::disk('fotos')->url($validar[0]->Fotografia);
            }else{
                $validar[0]->Fotografia = "../assets/logos/avatar.jpg";
            }
            $dir = explode("|",$validar[0]->Domicilio);
            if(count($dir)>1){
                $validar[0]->Calle = $dir[0];
                $validar[0]->Num_int = $dir[1];
                $validar[0]->Num_ext = $dir[2];
                $validar[0]->Cruzamiento_uno = $dir[3];
                $validar[0]->Cruzamiento_dos = $dir[4]; 
            }else{
                $validar[0]->Calle = "";
                $validar[0]->Num_int = "";
                $validar[0]->Num_ext = "";
                $validar[0]->Cruzamiento_uno = "";
                $validar[0]->Cruzamiento_dos = ""; 
            }
            $validar[0]->Sexo = strtoupper($validar[0]->Sexo); 
            return $this->crearRespuesta(1,$validar,200);
        }
        return $this->crearRespuesta(2,"No se encontro curp",200);
    }
    public function altaJugadorAdmin(Request $res){
        try{
            if($res["JugadorId"] == 0){
                $jugador_id = $this->getSigId("ly_catjugadores");
                $jugador->JugadorID = $jugador_id;
            }else{
                $jugador_id = $res["JugadorId"];
                $jugador = Jugadores::find($res["JugadorId"]);
            }
            $temporada_actual = $temporada = DB::table('ly_cattemporadas')->where("Actual",1)->first();
            $file = base64_decode($res["foto_jugador"]);
            $path = "";
            if(strlen($res["extension"]) != 0 && strlen($res["foto_jugador"]) != 0){
                    $path = $temporada_actual->Temporada."/F".$jugador_id.".".$res["extension"];
                    Storage::disk('fotos')->put($path, $file);
                }
                $jugador->ApellidoPaterno = strtoupper($res["apellido_p"]); 
                $jugador->ApellidoMaterno = strtoupper($res["apellido_m"]); 
                $jugador->Nombre = strtoupper($res["nombre"]);
                $jugador->FechaNacimiento = date('Y-m-d',strtotime($res["fecha_nacimiento"])); 
                $jugador->Domicilio = strtoupper($res["calle"])."|".strtoupper($res["num_int"])."|".strtoupper($res["num_exterior"])."|".strtoupper($res["cruzamiento_uno"])."|".strtoupper($res["cruzamiento_dos"]); 
                $jugador->Colonia = strtoupper($res["colonia"]); 
                $jugador->Telefono = $res["telefono"]; 
                $jugador->Municipio = strtoupper($res["municipio"]); 
                $jugador->EstadoID = 31; 
                $jugador->Sexo = strtoupper($res["sexo"]); 
                $jugador->Curp = strtoupper($res["curp"]); 
                $jugador->Estado  = "A"; 
                $jugador->CodigoPostal  = strtoupper($res["cp"]); 
                $jugador->Fotografia = $path;
                $jugador->APRepresentante = strtoupper($res["apellido_p_rep"]);
                $jugador->AMRepresentante = strtoupper($res["apellido_m_rep"]); 
                $jugador->NRepresentante = strtoupper($res["nombre_rep"]);  
                $jugador->CorreoRepresentante = $res["correo_rep"]; 
                $jugador->TelRepresentante = $res["tel_rep"]; 
                $jugador->APMadre = strtoupper($res["apellido_pm"]); 
                $jugador->AMMadre = strtoupper($res["apellido_mm"]); 
                $jugador->NMadre = strtoupper($res["nombre_m"]); 
                $jugador->TelMadre = $res["telefono_m"]; 
                $jugador->APPadre = strtoupper($res["apellido_pp"]); 
                $jugador->AMPadre = strtoupper($res["apellido_mp"]); 
                $jugador->NPadre = strtoupper($res["nombre_p"]); 
                $jugador->TelPadre = $res["telefono_p"]; 
                $jugador->EstatusID = 1;
                $jugador->Notas = strtoupper($res["nota"]);
                $jugador->save();
            return $this->crearRespuesta(1,"El jugador se ha actualizado con éxito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function altaJugador(Request $res)
    {
        //Validaciones
        if(isset($res["apellid_p"]) && strlen($res["apellido_p"]) == 0){
            return $this->crearRespuesta(2,"El campo apellido paterno no puede estar vacio",200);
        }
        if(isset($res["InscripcionID"]) && strlen($res["InscripcionID"]) == 0){
            return $this->crearRespuesta(2,"El campo curp no puede estar vacio",200);
        }
        if(isset($res["curp"]) && strlen($res["curp"]) == 0){
            return $this->crearRespuesta(2,"El campo curp no puede estar vacio",200);
        }
        if(isset($res["nombre"]) && strlen($res["nombre"]) == 0){
            return $this->crearRespuesta(2,"El campo nombre no puede estar vacio",200);
        }
        try{
            $temporada_actual = $temporada = DB::table('ly_cattemporadas')->where("Actual",1)->first();
            $file = base64_decode($res["foto_jugador"]);
            $path = "";
            $fecha = $this->getHoraFechaActual();
            if($res["JugadorId"] == 0){
                $inscripcion = $res["InscripcionID"];
                $jugador_id = $this->getSigId("ly_catjugadores");
                if(strlen($res["extension"]) != 0 && strlen($res["foto_jugador"]) != 0){
                    $path = $temporada_actual->Temporada."/F".$jugador_id.".".$res["extension"];
                    Storage::disk('fotos')->put($path, $file);
                }
                $jugador = new Jugadores();
                $jugador->JugadorID = $jugador_id;
                $jugador->ApellidoPaterno = strtoupper($res["apellido_p"]); 
                $jugador->ApellidoMaterno = strtoupper($res["apellido_m"]); 
                $jugador->Nombre = strtoupper($res["nombre"]);
                $jugador->FechaNacimiento = date('Y-m-d',strtotime($res["fecha_nacimiento"])); 
                $jugador->Domicilio = strtoupper($res["calle"])."|".strtoupper($res["num_int"])."|".strtoupper($res["num_exterior"])."|".strtoupper($res["cruzamiento_uno"])."|".strtoupper($res["cruzamiento_dos"]); 
                $jugador->Colonia = strtoupper($res["colonia"]); 
                $jugador->Telefono = $res["telefono"]; 
                $jugador->Municipio = strtoupper($res["municipio"]); 
                $jugador->EstadoID = 31; 
                $jugador->Sexo = strtoupper($res["sexo"]); 
                $jugador->Curp = strtoupper($res["curp"]); 
                $jugador->Estado  = "A"; 
                $jugador->CodigoPostal  = strtoupper($res["cp"]); 
                $jugador->Fotografia = $path;
                $jugador->APRepresentante = strtoupper($res["apellido_p_rep"]);
                $jugador->AMRepresentante = strtoupper($res["apellido_m_rep"]); 
                $jugador->NRepresentante = strtoupper($res["nombre_rep"]);  
                $jugador->CorreoRepresentante = $res["correo_rep"]; 
                $jugador->TelRepresentante = $res["tel_rep"]; 
                $jugador->APMadre = strtoupper($res["apellido_pm"]); 
                $jugador->AMMadre = strtoupper($res["apellido_mm"]); 
                $jugador->NMadre = strtoupper($res["nombre_m"]); 
                $jugador->TelMadre = $res["telefono_m"]; 
                $jugador->APPadre = strtoupper($res["apellido_pp"]); 
                $jugador->AMPadre = strtoupper($res["apellido_mp"]); 
                $jugador->NPadre = strtoupper($res["nombre_p"]); 
                $jugador->TelPadre = $res["telefono_p"]; 
                $jugador->EstatusID = 1;
                $jugador->Notas = strtoupper($res["nota"]);
                $jugador->save();
                DB::insert('insert into ly_detinscripciones (InscripcionID, Uniforme, Baja, JugadorID, Notas, Fecha) values (?,?,?,?,?,?)', [$inscripcion,0,0,$jugador_id,"",$fecha]);
            }else{
                $band = true;
                $jugador = Jugadores::find($res["JugadorId"]);
                if(strlen($res["extension"]) != 0 && strlen($res["foto_jugador"]) != 0){
                    $path = $temporada_actual->Temporada."/F".$res["JugadorId"].".".$res["extension"];
                    Storage::disk('fotos')->put($path, $file);
                }
                $jugador->ApellidoPaterno = strtoupper($res["apellido_p"]); 
                $jugador->ApellidoMaterno = strtoupper($res["apellido_m"]); 
                $jugador->Nombre = strtoupper($res["nombre"]);
                $jugador->FechaNacimiento = date('Y-m-d',strtotime($res["fecha_nacimiento"])); 
                $jugador->Domicilio = strtoupper($res["calle"])."|".strtoupper($res["num_int"])."|".strtoupper($res["num_exterior"])."|".strtoupper($res["cruzamiento_uno"])."|".strtoupper($res["cruzamiento_dos"]); 
                $jugador->Colonia = strtoupper($res["colonia"]); 
                $jugador->Telefono = $res["telefono"]; 
                $jugador->Municipio = strtoupper($res["municipio"]); 
                $jugador->EstadoID = 31; 
                $jugador->Sexo = strtoupper($res["sexo"]); 
                $jugador->Curp = strtoupper($res["curp"]); 
                $jugador->Estado  = "A"; 
                $jugador->Fotografia = $path;
                $jugador->APRepresentante = strtoupper($res["apellido_p_rep"]);
                $jugador->AMRepresentante = strtoupper($res["apellido_m_rep"]); 
                $jugador->NRepresentante = strtoupper($res["nombre_rep"]);  
                $jugador->CorreoRepresentante = $res["correo_rep"]; 
                $jugador->TelRepresentante = $res["tel_rep"]; 
                $jugador->CodigoPostal  = strtoupper($res["cp"]); 
                $jugador->APMadre = strtoupper($res["apellido_pm"]); 
                $jugador->AMMadre = strtoupper($res["apellido_mm"]); 
                $jugador->NMadre = strtoupper($res["nombre_m"]); 
                $jugador->TelMadre = $res["telefono_m"]; 
                $jugador->APPadre = strtoupper($res["apellido_pp"]); 
                $jugador->AMPadre = strtoupper($res["apellido_mp"]); 
                $jugador->NPadre = strtoupper($res["nombre_p"]); 
                $jugador->TelPadre = $res["telefono_p"]; 
                $jugador->Notas = strtoupper($res["nota"]);
                $jugador->save();
                $validar_inscripcion = DB::table('ly_detinscripciones as lyd')
                ->join("ly_encinscripciones as lye","lye.InscripcionID","=","lyd.InscripcionID")
                ->where("JugadorID",$res["JugadorId"])
                ->where("TemporadaID",$temporada_actual->TemporadaID)
                ->where("lyd.activo",1)
                ->get();
                if(count($validar_inscripcion)==0){
                    $inscripcion = $res["InscripcionID"];
                    DB::insert('insert into ly_detinscripciones (InscripcionID, Uniforme, Baja, JugadorID, Notas, Fecha) values (?,?,?,?,?,?)', [$inscripcion,0,0,$res["JugadorId"],"",$fecha]);
                }else{
                    return $this->crearRespuesta(2,"Este jugador ya se encunentra inscrito en tu equipo o en otro, sin embargo su información ha sido actualizada",200);
                }
                return $this->crearRespuesta(1,"El jugador se ha actualizado con éxito",200);
            }
            return $this->crearRespuesta(1,"El jugador se ha dado de alta",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function bajaJugador($id_jugador)
    {
        try{
            DB::update('update ly_detinscripciones set activo = 0 where JugadorID = ?', [$id_jugador]);
            return $this->crearRespuesta(1,"El jugador ha sido eliminado de su equipo",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),200);
        }
    }
    public function obtenerEquipos(){
        $temporada_actual =  DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $equipos = DB::table("vw_equiposinscritos as ve")
        ->select("Equipo","InscripcionID")
        ->where("ve.TemporadaID","=",22)
        ->get();
        if(count($equipos)>0){
            return $this->crearRespuesta(1,$equipos,200);
        }
        return $this->crearRespuesta(2,"Sin equipos",301);
    }
    public function altaJugadorAEquipo(Request $res){
        try{
            $fecha = $this->getHoraFechaActual();
            DB::insert('insert into ly_detinscripciones (InscripcionID, Uniforme, Baja, JugadorID, Notas, Fecha) values (?,?,?,?,?,?)', [$res["InscripcionID"],0,0,$res["JugadorId"],"",$fecha]);
            return $this->crearRespuesta(1,"El jugador ha sido dado de alta al equipo",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),200);
        }
        
    }
}
