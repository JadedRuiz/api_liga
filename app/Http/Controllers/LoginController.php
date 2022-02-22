<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Equipo;
use App\Models\Usuario;
use App\Models\Inscripcion;

class LoginController extends Controller
{
    public function login(Request $res)
    {
        $contra = $res["contra"];
        $is_admin = DB::table('ly_usaurios')
        ->select("activo","contra", "perfil")
        ->where("usuario",$res["usuario"])
        ->get();
        if(count($is_admin)>0){
            if( $is_admin[0]->perfil == "admin"){
                if($this->decode_json($is_admin[0]->contra) == $contra){
                    if($is_admin[0]->activo != 0){
                        $is_admin[0]->tipo = "admin";
                        return $this->crearRespuesta(1,$is_admin,200);
                    }else{
                        return $this->crearRespuesta(2,"El usuario no se encuentra activo",200);
                    }
                }else{
                    return $this->crearRespuesta(2,"La contraseña no coincide con el usuario",200);
                }
            }
        }
        $temporada_id = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $validar = DB::table('ly_encinscripciones as lye')
        ->join("ly_usaurios as lyu","lyu.UsuarioID","=","lye.UsuarioID")
        ->select("activo","contra", "lye.InscripcionID","lyu.UsuarioID")
        ->where("lyu.usuario",$res["usuario"])
        ->where("lye.TemporadaID",$temporada_id->TemporadaID)
        ->get();
        if(count($validar)>0){
            if($this->decode_json($validar[0]->contra) == $contra){
                if($validar[0]->activo != 0){
                    $validar[0]->tipo = "representante";
                    return $this->crearRespuesta(1,$validar,200);
                }else{
                    return $this->crearRespuesta(2,"El usuario no se encuentra activo",200);
                }
            }else{
                return $this->crearRespuesta(2,"La contraseña no coincide con el usuario",200);
            }
        }else{  
            return $this->crearRespuesta(2,"El usaurio no existe",200);
        }
    }
    public function recuperarContra(Request $res){
        $correo = $res["correo"];
        $temporada_id = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $validar = DB::table('ly_encinscripciones as lye')
        ->select(DB::raw('CONCAT(NomRep, " ", APatRep, " ", AMatRep ) as nombre'), "TelRep", "MailRep","lyu.UsuarioID","lyc.Equipo")
        ->join("ly_catequipos as lyc","lyc.EquipoID","=","lye.EquipoID")
        ->join("ly_usaurios as lyu","lyu.UsuarioID","=","lye.UsuarioID")
        ->where(DB::raw('BINARY `usuario`'),$correo)
        ->where("lye.TemporadaID",$temporada_id->TemporadaID)
        ->get();
        if(count($validar)>0){
            return $this->crearRespuesta(1,[
                "tipo" => 1,
                "data" => $validar],200);
        }else{
            $validar_coincidencias = DB::table('ly_encinscripciones as lye')
            ->select(DB::raw('CONCAT(NomRep, " ", APatRep, " ", AMatRep ) as nombre'), "TelRep", "MailRep","lyu.UsuarioID","lyc.Equipo")
            ->join("ly_catequipos as lyc","lyc.EquipoID","=","lye.EquipoID")
            ->join("ly_usaurios as lyu","lyu.UsuarioID","=","lye.UsuarioID")
            ->where("lyu.usuario", "like", "%".$correo."%")
            ->where("lye.TemporadaID",$temporada_id->TemporadaID)
            ->get();
            if(count($validar_coincidencias)>0){
               return $this->crearRespuesta(1,[
                "tipo" => 2,
                "data" => $validar_coincidencias],200);
            }
        }
        return $this->crearRespuesta(2,"El correo no ha sido encontrado",301);
    }
    public function enviarContra($id_usuario){
        $usuario = DB::table("ly_usaurios as lyu")
        ->select(DB::raw('CONCAT(NomRep, " ", APatRep, " ", AMatRep ) as nombre'),"lyu.usuario","lyu.contra")
        ->join("ly_encinscripciones as lye","lyu.UsuarioID","=","lye.UsuarioID")
        ->where("lyu.UsuarioID",$id_usuario)
        ->first();
        if($usuario){
            $this->enviarCorreo([
                "rfc" => "RUPJ9512159Y3",
                "tipo" => 1,
                "dirigidos" => [
                    [
                        "correo" => "$usuario->coreo",
                        "nombre" => $usuario->nombre
                    ],
                ],
                "asunto" => "RECUPERACIÓN DE CONTRASEÑA",
                "mensaje" => "Su usuario ha sido recuperada con la siguiente información :
                    usuario -> '".$usuario->usuario."'
                    contraseña -> '".$this->decode_json($usuario->contra) . "'
                **IMPORTANTE** SI EL PROBLEMA PERSISTE FAVOR DE COMUNICARTE CON EL ADMINISTRADOR DEL SISTEMA."
            ]);
            return $this->crearRespuesta(1,"La contraseña ha sido enviada a su correo",200);
        }
    }
    
    public function registro(Request $res)
    { 
        //Validaciones
        if(isset($res["curp"]) && strlen($res["curp"]) == 0){
            return $this->crearRespuesta(2,"El CURP no puede estar vacio",200);
        }
        if(isset($res["nombres"]) && strlen($res["nombres"]) == 0){
            return $this->crearRespuesta(2,"El NOMBRE no puede estar vacio",200);
        }
        if(isset($res["apellido_p"]) && strlen($res["apellido_p"]) == 0){
            return $this->crearRespuesta(2,"El APELLIDO PATERNO no puede estar vacio",200);
        }
        if(isset($res["correo"]) && strlen($res["correo"]) == 0){
            return $this->crearRespuesta(2,"El CORREO no puede estar vacio",200);
        }
        if(isset($res["contra"]) && strlen($res["contra"]) == 0){
            return $this->crearRespuesta(2,"La CONTRASEÑA no puede estar vacio",200);
        }
        $temporada_id = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $validar_usuario = DB::table('ly_usaurios as lyu')
        ->join("ly_encinscripciones as lye","lyu.UsuarioID", "=", "lye.UsuarioID")
        ->where("lyu.usuario",$res["correo"])
        ->where("lye.TemporadaID",$temporada_id->TemporadaID)
        ->get();
        if(count($validar_usuario)>0){
            return $this->crearRespuesta(2,"El nombre de usuario que desea utilizar ya se encuentra en uso en está temporada",200);
        }
        $equipo_name = strtoupper($res["equipo"]);
        $id_categoria = $res["CategoriaID"];
        $curp = strtoupper($res["curp"]);
        $apellido_p = strtoupper($res["apellido_p"]);
        $apellido_m = strtoupper($res["apellido_m"]);
        $nombres = strtoupper($res["nombres"]);
        $telefono = $res["telefono"];
        $correo = $res["correo"];
        $usuario_name = $res["correo"];
        $contra = $this->encode_json($res["contra"]);
        $recibo = $res["recibo"];
        $ext = $res["extension"];
        $fecha = $this->getHoraFechaActual();
        try{
            $temporada_id = DB::table('ly_cattemporadas')->where("Actual",1)->first();
            $validar = DB::table('ly_encinscripciones')
            ->where("TemporadaID",$temporada_id->TemporadaID)
            ->where("CurpRep", $curp)
            ->get();
            if(count($validar)==0){
                $usuario = new Usuario();
                $usuario->usuario = $usuario_name;
                $usuario->contra = $contra;
                $usuario->perfil = "representante";
                $usuario->activo = 0;
                $usuario->save();
                $id_usuario = $usuario->UsuarioID;
                $equipo = new Equipo();
                $equipo->equipo = $equipo_name;
                $equipo->save();
                $equipo_id = $equipo->EquipoID;
                $inscripcion = new Inscripcion();
                $inscripcion->InscripcionID = $this->getSigId("ly_encinscripciones");
                $inscripcion->Inscripcion = 0;
                $inscripcion->FechaInscripcion = $fecha;
                $inscripcion->Fecha = $fecha;
                $inscripcion->APatEnt = "";
                $inscripcion->AMatEnt = "";
                $inscripcion->NomEnt = "";
                $inscripcion->CurpEnt = "";
                $inscripcion->MailEnt = "";
                $inscripcion->TelEnt = "";
                $inscripcion->Representante = "";
                $inscripcion->APatAy = "";
                $inscripcion->AMatAy = "";
                $inscripcion->NomAy = "";
                $inscripcion->CurpAy = "";
                $inscripcion->MailAy = "";
                $inscripcion->TelAy = "";
                $inscripcion->FotoEnt = "";
                $inscripcion->FotoRep = "";
                $inscripcion->FotoAy = "";
                $inscripcion->EquipoID = $equipo_id;
                $inscripcion->TemporadaID = $temporada_id->TemporadaID;
                $inscripcion->CategoriaID = $id_categoria;
                $inscripcion->Clasificacion = 'A';
                $inscripcion->Grupo = 'I';
                $inscripcion->APatRep = $apellido_p;
                $inscripcion->AMatRep = $apellido_m;
                $inscripcion->NomRep = $nombres;
                $inscripcion->CurpRep = $curp;
                $inscripcion->MailRep = $correo;
                $inscripcion->TelRep = $telefono;
                $inscripcion->PosicionTabla = 0;
                $inscripcion->UsuarioID = $id_usuario;
                $inscripcion->FotoRecibo = $recibo;
                $inscripcion->ExtRecibo = $ext;
                $categoria = DB::table('ly_catcategorias')->where("CategoriaID",$id_categoria)->first()->Categoria;
                $this->enviarCorreo([
                    "rfc" => "RUPJ9512159Y3",
                    "tipo" => 1,
                    "dirigidos" => [
                        [
                            "correo" => $temporada_id->Correo,
                            "nombre" => "ADMINISTRADOR"
                        ],
                    ],
                    "asunto" => "NUEVA INSCRIPCIÓN",
                    "mensaje" => "El representante con CURP '".$curp."', ha inscrito al equipo '".$equipo_name."' para la categoria '".$categoria."', ingresa a tu panel administrativo para validar su inscripción.",
                    "adjuntos" => [
                        [
                            "extension" => $ext,
                            "nombre" => "RECIBO_PAGO",
                            "data" => $recibo
                        ]
                    ]
                ]);
                $inscripcion->save();
                return $this->crearRespuesta(1,"Inscripción realizada",200);
            }
            return $this->crearRespuesta(2,"Este representante ya ha sido inscrito en esta temporada con otro equipo",200);

        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }

    //
}