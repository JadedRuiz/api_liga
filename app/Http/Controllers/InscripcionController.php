<?php

namespace App\Http\Controllers;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InscripcionController extends Controller
{
    public function obtenerSolicitudesDeInscripciones()
    {
        $temporada_actual = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $inscripciones = Inscripcion::select("InscripcionID","ce.Equipo", DB::raw("CONCAT(APatRep, ' ', AMatRep, ' ', NomRep) as Representante"),"TelRep")
        ->leftJoin('ly_catequipos as ce', 'ce.EquipoID', '=', 'ly_encinscripciones.EquipoID')
        ->where("Inscripcion",0)
        ->where("TemporadaID",$temporada_actual->TemporadaID)
        ->get();
        if(count($inscripciones)>0){
            return $this->crearRespuesta(1,$inscripciones,200);
        }
        return $this->crearRespuesta(2,"Aún no han enviado solicitudes para esta temporada",200);
    }
    public function obtenerInscripciones()
    {
        $temporada_actual = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $inscripciones = Inscripcion::select("InscripcionID","Inscripcion","ce.Equipo", DB::raw("CONCAT(APatRep, ' ', AMatRep, ' ', NomRep) as Representante"),"TelRep","Editable","lcc.Categoria", DB::RAW("CONCAT(InscripcionID,'.jpg') as LogoEquipo"))
        ->leftJoin('ly_catcategorias as lcc',"lcc.CategoriaID","ly_encinscripciones.CategoriaID")
        ->leftJoin('ly_catequipos as ce', 'ce.EquipoID', '=', 'ly_encinscripciones.EquipoID')
        ->where("Inscripcion","<>",0)
        ->where("TemporadaID",$temporada_actual->TemporadaID)
        ->get();
        if(count($inscripciones)>0){
            foreach ($inscripciones as $inscripcion){
                if($inscripcion->LogoEquipo != ""){
                    $inscripcion->LogoEquipo = Storage::disk('equipos')->url($inscripcion->InscripcionID);
                }else{
                    $inscripcion->LogoEquipo = Storage::disk('equipos')->url("avatarequipo.jpg");
                }
            }
            return $this->crearRespuesta(1,$inscripciones,200);
        }
        return $this->crearRespuesta(2,"Aún no han enviado solicitudes para esta temporada",200);
    }
    public function obtenerReciboPorId($id_inscripcion)
    {
        $recibo = Inscripcion::select("FotoRecibo","ExtRecibo")
        ->where("InscripcionID",$id_inscripcion)
        ->first();
        if($recibo){
            return $this->crearRespuesta(1,$recibo,200);
        }
        return $this->crearRespuesta(2,"No se ha encontrado el recibo con ese ID",200);
    }
    public function validarSolicitudDeInscripcion($id_inscripcion){
        $temporada_actual = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $utlima_insc = Inscripcion::select("Inscripcion")
        ->where("Inscripcion", "<>",0)
        ->where("TemporadaID",$temporada_actual->TemporadaID)
        ->orderBy("Inscripcion","DESC")
        ->first();
        if($utlima_insc){
            $utlima_insc = intval($utlima_insc->Inscripcion)+1;
        }else{
            //Primera inscripcion
            $utlima_insc = 1;
        }
        try{
            $inscripcion = Inscripcion::find($id_inscripcion);
            $inscripcion->Inscripcion = $utlima_insc;
            $inscripcion->save();
            //Activar usuario
            $usuario = Inscripcion::select("UsuarioID","MailRep",DB::raw("CONCAT(APatRep, ' ', AMatRep, ' ', NomRep) as nombre"),"ce.Equipo")
            ->where("InscripcionID",$id_inscripcion)
            ->leftJoin('ly_catequipos as ce', 'ce.EquipoID', '=', 'ly_encinscripciones.EquipoID')
            ->first();
            if($usuario){
                DB::update('update ly_usaurios set activo = 1 where UsuarioID = ?', [$usuario->UsuarioID]);
                $this->enviarCorreo([
                    "rfc" => "RUPJ9512159Y3",
                    "tipo" => 1,
                    "dirigidos" => [
                        [
                            "correo" => $usuario->MailRep,
                            "nombre" => $usuario->nombre
                        ],
                    ],
                    "asunto" => "INSCRIPCIÓN EXITOSA",
                    "mensaje" => "Ya puede proceder al registro de sus jugadores, ingresando www.reydelosdeportes.com.mx con el correo y contraseña propocionada en su registro"
                ]);
            }
            return $this->crearRespuesta(1,"El equipo ha sido inscrito a la temporada",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),200);
        }
    }
    public function habilitarDeshabilitarEquipo($inscripcion_id,$tipo)
    {
        try{
            $inscripcion = Inscripcion::find($inscripcion_id);
            $inscripcion->Editable = $tipo;
            $inscripcion->save();
            return $this->crearRespuesta(1,"Tu acción se ha ejucutado con exito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),200);
        }
    }
    public function rechazarSolicitud($id_inscripcion)
    {
        
    }
}
