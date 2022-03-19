<?php

namespace App\Http\Controllers;
use App\Models\Jornada;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RolJuegosController extends Controller
{
    
    public function obtenerJornadas()
    {
        
        $jornadas = Jornada::select("JornadaID", "ly_catjornadas.TemporadaID", "Jornada", "Fecha", "Activo", DB::RAW("CONCAT(right(concat('00',Jornada),2),'.-',date_format(fecha, '%d-%m-%Y')) as Jornada_Vista") )
        ->Join('ly_cattemporadas as t',"t.TemporadaID","ly_catjornadas.TemporadaID")
        ->where("Actual",1)
        ->get();
        if(count($jornadas)>0){
            
            return $this->crearRespuesta(1,$jornadas,200);
        }
        return $this->crearRespuesta(2,"Aún no hay Jornadas Capturadas",200);
    }
    
    public function obtenerJornadaActual()
    {
        
        $jornadas = DB::table('ly_catjornadas as j')
        ->select("j.JornadaID", "j.TemporadaID", "j.Jornada", "j.Fecha", "j.Activo", DB::RAW("CONCAT(right(concat('00',j.Jornada),2),'.-',date_format(j.fecha, '%d-%m-%Y')) as Jornada_Vista") )
        ->Join('ly_cattemporadas as t',"t.TemporadaID","j.TemporadaID")
        ->where("t.Actual",1)
        ->where("j.Activo",1)
        ->get();
        if(count($jornadas)>0){
            
            return $this->crearRespuesta(1,$jornadas,200);
        }
        return $this->crearRespuesta(2,"Aún no hay Jornada Activa",200);
    }

    public function obtenerResultados(Request $res)
    {
        $jornadaid = $res["JornadaID"];
        $categoriaid = $res["CategoriaID"];
        $roljuego = DB::table("vw_roljuegos")
        ->select("RolJuegoID", "TemporadaID", "JornadaID", "Jornada", "CampoID", "Turno",  "Categoria", "Clasificacion", "Grupo", "EquipoVisitante", "ResultadoVisitante", "EquipoLocal", "ResultadoLocal", "GanoVisitante","GanoLocal", "InscripcionIDV", "InscripcionIDL", "Notas", "ForFit", "HitVisitante", "HitLocal", "Mostrar", DB::RAW("CONCAT(InscripcionIDV,'.jpg') as LogoV"), DB::RAW("CONCAT(InscripcionIDL,'.jpg') as LogoL") )
        ->where("JornadaID",$jornadaid);
        if($categoriaid > 0){
            $roljuego = $roljuego ->where("CategoriaID",$categoriaid);
        }
        $roljuego = $roljuego ->get();
        if(count($roljuego)>0){
            $resultado = array();
            foreach ($roljuego as $rj){
                $rj->LogoV = Storage::disk('equipos')->url('t'.strval($rj->TemporadaID).'/L'.$rj->LogoV);
                $path = storage_path('equipos').'t'.strval($rj->TemporadaID).'/L'.$rj->LogoV;

                //if(file_exists($path) == false){
                //    $rj->LogoV = Storage::disk('equipos')->url("avatarequipo.jpg");    
                //}

                $rj->LogoL = Storage::disk('equipos')->url('t'.strval($rj->TemporadaID).'/L'.$rj->LogoL);
                $path = storage_path('equipos').'t'.strval($rj->TemporadaID).'/L'.$rj->LogoL;

                //if(file_exists($path) == false){
                //    $rj->LogoL = Storage::disk('equipos')->url("avatarequipo.jpg");    
                //}
                $GanoL = false;
                $GanoV = false;
                if($rj->GanoVisitante == 1){$GanoV = true;}
                if($rj->GanoLocal == 1){$GanoL = true;}

                $var = array(
                    "categoria" => $rj->Categoria,
                    "ubicacion" => $rj->Categoria.' '.$rj->Clasificacion.' - '.$rj->Grupo,
                    "temporada" => 'JORNADA '.$rj->Jornada,
                    "equipo_1" => array("equipo" => $rj->EquipoVisitante, "logo"=> $rj->LogoV, "carreras"=> $rj->ResultadoVisitante, "win"=>$GanoV),
                    "equipo_2" => array("equipo" => $rj->EquipoLocal, "logo"=> $rj->LogoL, "carreras"=> $rj->ResultadoLocal, "win"=>$GanoL)
                );
                $resultado[] = $var;

            }
            return $this->crearRespuesta(1,$resultado,200);
        }
        return $this->crearRespuesta(2,"Aún no hay Resultados del Rol de Juegos Seleccionados ".$jornadaid." ".$categoriaid,200);
    }

    public function obtenerStanding(Request $res)
    {
        
        
        $temporadaid = $res["TemporadaID"];
        $categoriaid = $res["CategoriaID"];

        $jornadadb = DB::table("ly_catjornadas")
        ->Select("JornadaID", "Jornada")
        ->WHERE("Activo",1)
        ->WHERE("TemporadaID", $temporadaid)
        ->get();
        if(count($jornadadb)>0){
            foreach ($jornadadb as $res){
                $jornada = $res->Jornada;
            }

            $resultadosl = DB::table("vw_roljuegos as r")
            ->select("r.TemporadaID", "r.CategoriaID", "r.Categoria", "r.Clasificacion", "r.Grupo", DB::RAW("r.InscripcionIDV AS EquipoID"), DB::RAW("r.EquipoVisitante AS Equipo"), DB::RAW("SUM(CASE WHEN r.GanoVisitante = 1 THEN 1 ELSE 0 END) AS Ganados"), DB::RAW("SUM(CASE WHEN r.GanoVisitante = 1 THEN 0 ELSE 1 END) AS Perdidos"), DB::RAW("SUM(CASE WHEN r.Empate = 1 THEN 1 ELSE 0 END) AS Empate"), DB::RAW("SUM(CASE WHEN r.EquipoLocal = 'DESCANSO' THEN 1 ELSE 0 END) AS Descanso"), DB::RAW("CONCAT(r.InscripcionIDV,'.jpg') as Logo"),"i.PosicionTabla",DB::RAW("0.000 as Porcentaje") )
            ->Join('ly_cattemporadas as t',"t.TemporadaID","r.TemporadaID")
            ->Join('ly_encinscripciones as i',"i.InscripcionID","r.InscripcionIDV")
            ->WHERE("r.Jornada",">=",1 )
            ->WHERE("r.Jornada","<=",$jornada)
            ->WHERE("r.TemporadaID",$temporadaid);
            if($categoriaid > 0){
                $resultadosl = $resultadosl ->where("r.CategoriaID",$categoriaid);
            }
            $resultadosl = $resultadosl
            ->groupBy("r.TemporadaID", "r.CategoriaID", "r.Categoria", "r.Clasificacion", "r.Grupo", "r.InscripcionIDV", "r.EquipoVisitante", "i.PosicionTabla");

            $resultadosv = DB::table("vw_roljuegos as r")
            ->select("r.TemporadaID", "r.CategoriaID", "r.Categoria", "r.Clasificacion", "r.Grupo", DB::RAW("r.InscripcionIDL AS EquipoID"), DB::RAW("r.EquipoLocal AS Equipo"), DB::RAW("SUM(CASE WHEN r.GanoLocal = 1 THEN 1 ELSE 0 END) AS Ganados"), DB::RAW("SUM(CASE WHEN r.GanoLocal = 1 THEN 0 ELSE 1 END) AS Perdidos"), DB::RAW("SUM(CASE WHEN r.Empate = 1 THEN 1 ELSE 0 END) AS Empate"), DB::RAW("SUM(CASE WHEN r.EquipoLocal = 'DESCANSO' THEN 1 ELSE 0 END) AS Descanso"), DB::RAW("CONCAT(r.InscripcionIDL,'.jpg') as Logo"),"i.PosicionTabla",DB::RAW("0.000 as Porcentaje") )
            ->Join('ly_cattemporadas as t',"t.TemporadaID","r.TemporadaID")
            ->Join('ly_encinscripciones as i',"i.InscripcionID","r.InscripcionIDL")
            ->WHERE("r.Jornada",">=",1)
            ->WHERE("r.Jornada","<=",$jornada)
            ->WHERE("r.TemporadaID",$temporadaid);
            if($categoriaid > 0){
                $resultadosv = $resultadosv ->where("r.CategoriaID",$categoriaid);
            }
            $resultadosv = $resultadosv
            ->groupBy("r.TemporadaID", "r.CategoriaID", "r.Categoria", "r.Clasificacion", "r.Grupo", "r.InscripcionIDL", "r.EquipoLocal", "i.PosicionTabla")
            ->OrderBy("r.TemporadaID")
            ->Orderby("r.CategoriaID")
            ->OrderBy("r.Clasificacion")
            ->OrderBy("r.Grupo")
            ->unionAll($resultadosl)
            ->get();

            
            //return $this->crearRespuesta(1,$jornada,200);
            if(count($resultadosv)>0){
                $resEquipo = array();
                foreach ($resultadosv as $res){
                    $var = array(
                        "TemporadaID" => $res->TemporadaID,
                        "CategoriaID" => $res->CategoriaID,
                        "Categoria" => $res->Categoria,
                        "Clasificacion" => $res->Clasificacion,
                        "Grupo"=>$res->Grupo,
                        "EquipoID"=>$res->EquipoID,
                        "Equipo"=>$res->Equipo,
                        "Ganados"=>$res->Ganados,
                        "Perdidos"=>$res->Perdidos,
                        "Empate"=>$res->Empate,
                        "Descanso"=>$res->Descanso,
                        "Logo"=>$res->Logo,
                        "Lugar"=>'',
                        "PosicionTabla"=>$res->PosicionTabla
                    );
                    $resEquipo[] = $var;
                }
                
                foreach ($resEquipo as $key=>$row){
                    $aux[$key] = $row['EquipoID'];
                }
                array_multisort($aux, SORT_ASC,$resEquipo);

                $resultado = array();
                $equipoid = 0;
                foreach ($resEquipo as $res){
                    
                    if($res["EquipoID"] <> $equipoid){
                        
                        if($equipoid > 0){
                            $porcentaje = 0.0000;
                            if($ganados > 0){
                                if(($ganados+$perdidos+$empate) > 0){
                                    $porcentaje = $ganados / ($perdidos + $ganados + $empate);
                                }
                            }
                            $var = array(
                                "TemporadaID" => $temporadaid,
                                "CategoriaID" => $categoriaid,
                                "Categoria" => $categoria,
                                "Clasificacion" => $clasificacion,
                                "Grupo"=>$grupo,
                                "EquipoID"=>$equipoid,
                                "Equipo"=>$equipo,
                                "Ganados"=>$ganados,
                                "Perdidos"=>$perdidos,
                                "Empate"=>$empate,
                                "Descanso"=>$descanso,
                                "Logo"=>$logo,
                                "Lugar"=>'',
                                "Posicion"=>$posicion,
                                "Porcentaje"=>$porcentaje,
                                "Orden"=>substr(strval('00'.$categoriaid),-2,2).$clasificacion.$grupo.strval($porcentaje)
                            );
                            $resultado[] = $var;
                        }
                        $equipoid = $res["EquipoID"];
                        $temporadaid = $res["TemporadaID"];
                        $categoriaid = $res["CategoriaID"];
                        $categoria = $res["Categoria"];
                        $clasificacion = $res["Clasificacion"];
                        $grupo = $res["Grupo"];
                        $equipo = $res["Equipo"];
                        $ganados = 0;
                        $perdidos = 0;
                        $empate = 0;
                        $descanso = 0;
                        $posicion = $res["PosicionTabla"];
                        $ruta = 't'.$res["TemporadaID"].'/L'.$res["Logo"];
                        
                        $logo = storage_path('equipos').'t'.strval($res["TemporadaID"]).'/L'.$res["Logo"];
                        $logo = Storage::disk('equipos')->url('t'.strval($res["TemporadaID"]).'/L'.$res["Logo"]);
                        //$logo = Storage::disk('equipos')->url($ruta);
                        //if(file_exists($logo)){
                        //    $logo = Storage::disk('equipos')->url($logo);    
                        //}else{
                        //    $logo = Storage::disk('equipos')->url("avatarequipo.jpg");   
                        //}
                        
                    }
                    $ganados = $ganados + $res["Ganados"];
                    $perdidos = $perdidos + $res["Perdidos"];
                    $empate = $empate + $res["Empate"];
                    $descanso = $descanso + $res["Descanso"];

                }
                $var = array(
                    "TemporadaID" => $temporadaid,
                    "CategoriaID" => $categoriaid,
                    "Categoria" => $categoria,
                    "Clasificacion" => $clasificacion,
                    "Grupo"=>$grupo,
                    "EquipoID"=>$equipoid,
                    "Equipo"=>$equipo,
                    "Ganados"=>$ganados,
                    "Perdidos"=>$perdidos,
                    "Empate"=>$empate,
                    "Descanso"=>$descanso,
                    "Logo"=>$logo,
                    "Lugar"=>'',
                    "Posicion"=>$posicion,
                    "Porcentaje"=>$porcentaje,
                    "Orden"=>substr(strval('00'.$categoriaid),-2,2).$clasificacion.$grupo.strval($porcentaje)
                );
                $resultado[] = $var;
                $aux = array();

                foreach ($resultado as $key=>$row){
                    $aux[$key] = $row['Orden'];
                }
                array_multisort($aux, SORT_DESC,$resultado);
                
                $lugar = 0;
                $categoriaid = 0;
                $grupo = '';
                $clasificacion = '';
                foreach ($resultado as $row){
                    if(($row["CategoriaID"] <> $categoriaid) || ($row["Grupo"] != $grupo) || ($row["Clasificacion"] != $clasificacion)){
                        $lugar = 0;
                    }
                    $lugar = $lugar + 1;
                    if($row["Posicion"] > 0){
                        $row["Orden"] = substr(strval($row["Posicion"])+'-0',-5,5);
                    }else{
                        $row["Orden"] = substr(strval($lugar)+'-1',-5,5);
                    }
                    
                }
                foreach ($resultado as $key=>$row){
                    $aux[$key] = $row['Orden'];
                }
                array_multisort($aux, SORT_DESC,$resultado);
                $lugar = 0;
                $arrposiciones = array();
                $grupo = 0;
                $catid = 0;
                $clas = "";
                $grup = "";
                $arrfinal = [];
                $primero = 0;
                foreach ($resultado as $row){
                    if(($row["CategoriaID"] != $catid) || ($row["Clasificacion"] != $clas) || ($row["Grupo"] != $grup)){
                        if($primero > 0){
                            $arrfinal[]= array("grupo"=>$grupo,"clasificacion"=>$clas.' '.$grup,"equipos"=>$var);
                        }
                        $primero = 1;
                        $grupo = $grupo + 1;
                        $var=[];
                        $lugar = 0;
                    }
                    $lugar = $lugar + 1;
                    $var[] = array(
                        
                        "categoriaID" => $row["CategoriaID"],
                        "categoria" => $row["Categoria"],
                        "position"=>$lugar,
                        "foto"=>$row["Logo"],
                        "equipo"=>$row["Equipo"],
                        "wins"=>$row["Ganados"],
                        "loses"=>$row["Perdidos"],
                        "points"=>number_format($row["Porcentaje"],3,".",",")
                    );
                    $catid = $row["CategoriaID"];
                    $clas = $row["Clasificacion"];
                    $grup = $row["Grupo"];
                    $arrposiciones[] = $var;
                    
                }
                $arrfinal[]= array("grupo"=>$grupo, "clasificacion"=>$clas.' '.$grup,"equipos"=>$var);

                return $this->crearRespuesta(1,$arrfinal,200);
            }
            return $this->crearRespuesta(2,"Aún no hay Resultados del Rol de Juegos Seleccionados ".$jornadaid." ".$categoriaid,200);
        }
        return $this->crearRespuesta(2,"NO encontro la Jornada Activa",200);
    }

    public function obtenerEquipos(Request $res)
    {
        $temporadaid = $res["TemporadaID"];
        $resultado = DB::table("vw_equiposinscritos")
        ->select("InscripcionID", "Inscripcion", "EquipoSeleccion as Equipo" )
        ->where("TemporadaID",$temporadaid)
        ->get();
        if(count($resultado)>0){
            
            return $this->crearRespuesta(1,$resultado,200);
        }
        return $this->crearRespuesta(2,"Aún no hay Equipos en la Temporada",200);
    }
}