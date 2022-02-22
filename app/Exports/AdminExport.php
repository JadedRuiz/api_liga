<?php 

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Codedge\Fpdf\Fpdf\Fpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AdminExport {
    

    public function generarReporte($datos_vista, $nombre_reporte)
    {
        if($nombre_reporte == "Equipos"){
            return $this->reporteEquipos($datos_vista);
        }
        if($nombre_reporte == "MiEquipo"){
            return $this->reporteMiEquipo($datos_vista);
        }
        if($nombre_reporte == "Jugadores"){
            return $this->reporteJugadores($datos_vista);
        }
        if($nombre_reporte == "Jugador"){
            return $this->reporteJugador($datos_vista);
        }
        if($nombre_reporte == "ReporteCarpeta"){
            return $this->reporteCarpeta($datos_vista);
        }
    }
    public function reporteMiEquipo($datos_vista)
    {
        //OBTENCIÓN DE DATOS
        $temporada_actual = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $jugadores = DB::table('vw_jugadoresporequipo as je')
        ->join("vw_equiposinscritos as ji", "je.InscripcionID", "=", "ji.InscripcionID")
        ->where("je.Activo",1)
        ->where("je.InscripcionID",$datos_vista["InscripcionID"])
        ->where("je.TemporadaID",$temporada_actual->TemporadaID)
        ->get();
        //CREACION DE PDF
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        $pdf->Ln();
        $pdf->Image(env("APP_URL")."/storage/logo/logo.png",10,10,40,40,'PNG','');
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"LIGA INFANTIL Y JUVENIL DE BEISBOL",0,0,"C");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"YUCATAN A.C ",0,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,utf8_decode("Afiliada a la Asociación Yucateca de Beisbol Amateur"),0,0,"C");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,utf8_decode("Unidad Deportiva Periférico Oriente ExEjido de Sitpach, Mérida, Yuc., Méx."),0,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"FICHA DE INSCRIPCION DEL EQUIPO",0,0,"C");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"AL ".$temporada_actual->Temporada,0,0,"C");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"PERIODO DEL MES DE ".$this->getMes(date('m',strtotime($temporada_actual->FechaInauguracion)))." A ".$this->getMes(date('m',strtotime($temporada_actual->FechaFinalTemporada)))." DEL ".date('Y',strtotime($temporada_actual->FechaFinalTemporada)),0,0,"C");
        $pdf->Ln(9);
        $pdf->SetFont('Arial','B',12);
        $pdf->SetFillColor(0, 158, 5);
        $pdf->SetDrawColor(0, 158, 5);
        $pdf->Cell(190,.5,"",1,0,"C",true);
        $pdf->Ln(1);
        $i=0;
        foreach ($jugadores as $d) {
            $equiposelect=$d->EquipoSeleccion;
            $categoria=$d->Categoria;
            $equipo=$d->Equipo;
            $nomEntr=$d->NomEnt;
            $apellidosEntr=$d->APatEnt." ".$d->AMatEnt;
            $telEntr=$d->TelEnt;
            $telRep=$d->TelRep;
            $telAy=$d->TelAy;
            $mailEntr=$d->MailEnt;
            $mailRep=$d->MailRep;
            $mailAy=$d->MailAy;
            $nomAyu=$d->NomAy;
            $apellidosAyu=$d->APatAy." ".$d->AMatAy;
            $nomRep=$d->NomRep;
            $apellidosRep=$d->APatRep." ".$d->AMatRep;
            $numcaracteres=strlen(utf8_decode($d->Jugador));
            if($numcaracteres>32){
                $jugador[$i] = substr(utf8_decode($d->Jugador),0,-($numcaracteres-32));
            }else{
                $jugador[$i]=$d->Jugador;
            }
            $fchnac[$i]=date('d/m/Y',strtotime($d->FechaNacimiento));
            $jugadorid[$i]=$d->JugadorID;
            $edad[$i]=$d->Edad;
            $i++;
        }

        $equiposelect=str_replace($equipo, "", $equiposelect);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,5,"NOMBRE DEL EQUIPO: ",0,0,"L");
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150,5,utf8_decode($equipo),0,0,"L");
        $pdf->Ln(4);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,5,"CATEGORIA: ",0,0,"L");
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(100,5,utf8_decode($equiposelect),0,0,"L");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(50,5,"FECHA LIMITE ".date('d/m/Y',strtotime($temporada_actual->FechaLimite)),0,1,"R");
        $pdf->Ln(.3);
        $pdf->SetFont('Arial','B',12);
        $pdf->SetFillColor(0, 158, 5);
        $pdf->SetDrawColor(0, 158, 5);
        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(190,.5,"",1,0,"C",true);
        $pdf->Ln(6);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(10,10,"No.",1,0,"R",true);
        $pdf->Cell(60,10,"NOMBRE DEL JUGADOR.",1,0,"L",true);
        $pdf->Cell(20,10,"F.NAC.",1,0,"C",true);
        $pdf->Cell(20,10,"EDAD",1,0,"C",true);
        $pdf->Cell(20,10,"REGISTRO",1,0,"C",true);
        $pdf->Cell(60,10,"EQUIPO ANTERIOR",1,0,"C",true);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',8);
        for ($i=0; $i <16 ; $i++) { 
            $pdf->Ln();
            $pdf->Cell(10,5,$i+1,1,0,"R");
            $pdf->Cell(60,5,@utf8_decode($jugador[$i]),1,0,"L");
            $pdf->Cell(20,5,@$fchnac[$i],1,0,"C");
            $pdf->Cell(20,5,@$edad[$i],1,0,"C");
            $pdf->Cell(20,5,@$jugadorid[$i],1,0,"C");
            //CONSULTA AUX
            $anterior = DB::table('vw_jugadoresporequipo as je')
            ->join("vw_equiposinscritos as ji","je.InscripcionID", "=", "ji.InscripcionID")
            ->where('je.JugadorID',@$jugadorid[$i])
            ->where("je.TemporadaID", "<>", $temporada_actual->TemporadaID)
            ->orderBy("je.TemporadaID", "DESC")
            ->get();
            $equiant="";
            if(@$jugadorid[$i]>0){
                foreach ($anterior as $datos) {
                    $equiant=$datos->EquipoSeleccion;
                }
                $pdf->SetFont('Arial','',6);
                $equiant=str_replace("DIVISION ", "", $equiant);
                $equiant=str_replace("CATEGORIA ", "", $equiant);
                $equiant=str_replace("jUVENIL ", "", $equiant);
                $equiant=str_replace("INFANTIL ", "", $equiant);
                $equiant=str_replace("MENOR ", "", $equiant);
                $equiant=str_replace("MAYOR ", "", $equiant);
                $numcaracteres=strlen(utf8_decode($equiant));
                if($numcaracteres>45){
                        $equiant = substr(utf8_decode($equiant),0,-($numcaracteres-45));
                }

                $pdf->Cell(60,5,@utf8_decode($equiant),1,0,"L");
            }else{
                $pdf->Cell(60,5,"",1,0,"L");
            }
            $pdf->SetFont('Arial','',8);
        }
        $pdf->Ln(10);

        $pdf->SetTextColor(255 ,255,255);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(65,5,"MANEJADOR",1,0,"C",true);
        $pdf->Cell(65,5,"AYUDANTE",1,0,"C",true);
        $pdf->Cell(60,5,"REPRESENTANTE",1,0,"C",true);
        $pdf->Ln();
        $pdf->Cell(65,20,"",1,0,"C");
        $pdf->Cell(65,20,"",1,0,"C");
        $pdf->Cell(60,20,"",1,0,"C");
        $pdf->Ln();
        $pdf->SetFillColor(230,230,230);
        $pdf->SetDrawColor(0, 158, 5);
        $pdf->SetTextColor(0,158,5);
        $pdf->Cell(65,5,"FIRMA",1,0,"C",true);
        $pdf->Cell(65,5,"FIRMA",1,0,"C",true);
        $pdf->Cell(60,5,"FIRMA",1,0,"C",true);

        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(65,5,$nomEntr,'L',0,"C");
        $pdf->Cell(65,5,$nomAyu,'L',0,"C");
        $pdf->Cell(60,5,$nomRep,'LR',0,"C");
        $pdf->Ln();
        $pdf->Cell(65,5,$apellidosEntr,'L',0,"C");
        $pdf->Cell(65,5,$apellidosAyu,'L',0,"C");
        $pdf->Cell(60,5,$apellidosRep,'LR',0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',10);
        $pdf->SetTextColor(0,158,5);
        $pdf->Cell(65,5,"NOMBRE",1,0,"C",true);
        $pdf->Cell(65,5,"NOMBRE",1,0,"C",true);
        $pdf->Cell(60,5,"NOMBRE",1,0,"C",true);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(65,10,$telEntr,1,0,"C");
        $pdf->Cell(65,10,$telAy,1,0,"C");
        $pdf->Cell(60,10,$telRep,1,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',10);
        $pdf->SetTextColor(0,158,5);
        $pdf->Cell(65,5,"TELEFONO",1,0,"C",true);
        $pdf->Cell(65,5,"TELEFONO",1,0,"C",true);
        $pdf->Cell(60,5,"TELEFONO",1,0,"C",true);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(65,10,$mailEntr,1,0,"C");
        $pdf->Cell(65,10,$mailAy,1,0,"C");
        $pdf->Cell(60,10,$mailRep,1,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',10);
        $pdf->SetTextColor(0,158,5);
        $pdf->Cell(65,5,"CORREO ELECTRONICO",1,0,"C",true);
        $pdf->Cell(65,5,"CORREO ELECTRONICO",1,0,"C",true);
        $pdf->Cell(60,5,"CORREO ELECTRONICO",1,0,"C",true);
        $pdf->Ln(20);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(190,10,'FOMENTAR EL DEPORTE ES ENCAUZAR LA'.' '.utf8_decode('NIÑEZ').' '.'Y LA JUVENTUD',0,0,"C");
        $pdf->Ln(20);
        $pdf->SetFont('Arial','B',6);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(190,5,"REPORTE FICHA DE INSCRIPCION",0,0,"R");
        
        return base64_encode($pdf->Output("S","ReporteEquipo.pdf"));
    }
    public function reporteEquipos($datos_vista)
    {
        //OBTENER DATOS
        $opcion = $datos_vista["opcion"];
        $temporada_actual = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $resp = DB::table('ly_encinscripciones as e')
        ->select("e.InscripcionID","e.Inscripcion","e.EquipoID", "ce.Equipo", "e.CategoriaID", "cc.Categoria", "e.Clasificacion", "APatRep", "AMatRep", "NomRep", "TelRep", "MailRep",db::raw("COUNT(d.JugadorID) AS NumJugadores"))
        ->join("ly_detinscripciones as d", "e.InscripcionID", "=", "d.InscripcionID","left")
        ->join("ly_catequipos as ce", "e.EquipoID" , "=", "ce.EquipoID")
        ->join("ly_catcategorias as cc", "e.CategoriaID", "=", "cc.CategoriaID")
        ->where("TemporadaID",$temporada_actual->TemporadaID)
        ->where(function ($query) use ($opcion){
            if($opcion == 1){
                $query->where("Inscripcion",">",0);
            }
            if($opcion == 2){
                $query->where("Inscripcion","<=",0);
            }
        })
        ->groupBy("e.InscripcionID", "e.EquipoID", "ce.Equipo", "e.CategoriaID", "cc.Categoria", "e.Clasificacion", "APatRep", "AMatRep", "NomRep", "TelRep")
        ->orderBy("e.CategoriaID", "ASC")
        ->orderBy("cc.Categoria", "ASC")
        ->orderBy("e.Clasificacion","ASC")
        ->get();
        //PINTAR PDF
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        $pdf->Ln();
        $pdf->Image(env("APP_URL")."/storage/logo/logo.png",10,10,40,40,'PNG','');
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"LIGA INFANTIL Y JUVENIL DE BEISBOL",0,0,"C");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"YUCATAN A.C ",0,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,utf8_decode("Afiliada a la Asociación Yucateca de Beisbol Amateur"),0,0,"C");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,utf8_decode("Unidad Deportiva Periférico Oriente ExEjido de Sitpach, Mérida, Yuc., Méx."),0,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"FICHA DE INSCRIPCION DEL EQUIPO",0,0,"C");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"AL ".$temporada_actual->Temporada,0,0,"C");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"PERIODO DEL MES DE ".$this->getMes(date('m',strtotime($temporada_actual->FechaInauguracion)))." A ".$this->getMes(date('m',strtotime($temporada_actual->FechaFinalTemporada)))." DEL ".date('Y',strtotime($temporada_actual->FechaFinalTemporada)),0,0,"C");
        $pdf->Ln(9);
        $pdf->SetFont('Arial','B',12);
        $pdf->SetFillColor(0, 158, 5);
        $pdf->SetDrawColor(0, 158, 5);
        $pdf->Cell(190,.5,"",1,0,"C",true);
        $pdf->Ln(1);


        $pdf->SetFont('Arial','B',12);
        if ($opcion==3) {
            $pdf->Cell(190,5,"LISTA COMPLETA DE EQUIPOS",0,0,"C");
        }
        if ($opcion==1) {
            $pdf->Cell(190,5,"LISTA EQUIPOS INSCRITOS",0,0,"C");
        }
        if ($opcion==2) {
            $pdf->Cell(190,5,"LISTA EQUIPOS NO INSCRITOS",0,0,"C");
        }
        $pdf->Ln(5);
        $pdf->Cell(190,.5,"",1,0,"C",true);
        $pdf->Ln(1);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(190,5,"FECHA LIMITE ".date('d/m/Y',strtotime($temporada_actual->FechaLimite)),0,1,"R");
        $pdf->Ln(1);

        $numcategoria=0;
        $numclasificacion=0;
        $p=0;
        $contapag = 0;

        foreach ($resp as $datos) {
        /*titulos*/
            if(($numcategoria!=$datos->CategoriaID) || ($numclasificacion!=$datos->Clasificacion)){
                if($p==0 ){
                    $pdf->Ln(8);
                    $p++;
                }else{
                    $pdf->Ln(15);
                }
                $pdf->SetTextColor(255 ,255,255);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(130,10,$datos->Categoria,1,0,"L",true);
                $pdf->Cell(60,10,utf8_decode("CLASIFICACIÓN: ".$datos->Clasificacion),1,0,"L",true);
                $pdf->Ln(12);
                $pdf->Cell(68,5,"EQUIPO",1,0,"C",true);
                $pdf->Cell(40,5,"REPESENTANTE",1,0,"C",true);
                $pdf->Cell(18,5,"TEL.",1,0,"C",true);
                $pdf->Cell(56,5,"EMAIL",1,0,"C",true);
                $pdf->Cell(8,5,"N.J.",1,0,"C",true);

                $numcategoria=$datos->CategoriaID;
                $numclasificacion=$datos->Clasificacion;
                $contapag+=5;
            }
            $pdf->SetTextColor(0 ,0,0);
            $nomrep=$datos->NomRep." ".$datos->APatRep." ".$datos->AMatRep;
            $numcaracteres=strlen(utf8_decode($nomrep));
            if($numcaracteres>23){
                $nomrep = substr(utf8_decode($nomrep),0,-($numcaracteres-23));
            }
            $numcaracteres=strlen(utf8_decode($datos->MailRep));
            if($numcaracteres>35){
                $datos->MailRep = substr(utf8_decode($datos->MailRep),0,-($numcaracteres-35   ));
            }
            $numcaracteres=strlen(utf8_decode($datos->Equipo));
            if($numcaracteres>41){
                $datos->Equipo = substr(utf8_decode($datos->Equipo),0,-($numcaracteres-41));
            }
            $numcaracteres=strlen(utf8_decode($datos->TelRep));
            if($numcaracteres>10){
                $datos->TelRep = substr(utf8_decode($datos->TelRep),0,-($numcaracteres-10));
            }

            $pdf->Ln();
            /*datos*/
            $pdf->SetFont('Arial','',7);
            $pdf->Cell(68,10,$datos->Equipo,1,0,"L");
            $pdf->Cell(40,10,$nomrep,1,0,"L");
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(18,10,$datos->TelRep,1,0,"C");
            $pdf->SetFont('Arial','',9);
            $pdf->Cell(56,10,strtolower($datos->MailRep),1,0,"L");
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(8,10,$datos->NumJugadores,1,0,"C");

            $contapag=$contapag+10;
            if ($contapag>=160) {
                $contapag=0;
                $pdf->Ln(10);
                $pdf->SetFont('Arial','B',6);
                $pdf->SetTextColor(0,0,0);
                $pdf->Cell(190,5,"REPORTE FICHA ESTADOS DE LOS EQUIPOS",0,0,"R");
                $pdf->AddPage();
                $pdf->SetFont('Arial','B',12);
                $pdf->Image(env("APP_URL")."/storage/logo/logo.png",10,10,40,40,'PNG','');
                $pdf->Cell(35,5,"",0,0,"L");
                $pdf->Cell(130,5,"LIGA INFANTIL Y JUVENIL DE BEISBOL",0,0,"C");
                $pdf->Ln();
                $pdf->Cell(35,5,"",0,0,"L");
                $pdf->Cell(130,5,"YUCATAN A.C ",0,0,"C");
                $pdf->Ln();
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(35,5,"",0,0,"L");
                $pdf->Cell(130,5,utf8_decode("Afiliada a la Asociación Yucateca de Beisbol Amateur"),0,0,"C");
                $pdf->Ln();
                $pdf->Cell(35,5,"",0,0,"L");
                $pdf->Cell(130,5,utf8_decode("Unidad Deportiva Periférico Oriente ExEjido de Sitpach, Mérida, Yuc., Méx."),0,0,"C");
                $pdf->Ln();
                $pdf->SetFont('Arial','B',12);
                $pdf->Cell(35,5,"",0,0,"L");
                $pdf->Cell(130,5,"FICHA DE INSCRIPCION DEL EQUIPO",0,0,"C");
                $pdf->Ln();
                $pdf->Cell(35,5,"",0,0,"L");
                $pdf->Cell(130,5,"AL ".$temporada_actual->Temporada,0,0,"C");
                $pdf->Ln();
                $pdf->Cell(35,5,"",0,0,"L");
                $pdf->Cell(130,5,"PERIODO DEL MES DE ".$this->getMes(date('m',strtotime($temporada_actual->FechaInauguracion)))." A ".$this->getMes(date('m',strtotime($temporada_actual->FechaFinalTemporada)))." DEL ".date('Y',strtotime($temporada_actual->FechaFinalTemporada)),0,0,"C");
                $pdf->Ln(9);
                $pdf->SetFont('Arial','B',12);
                $pdf->SetFillColor(0, 158, 5);
                $pdf->SetDrawColor(0, 158, 5);
                $pdf->Cell(190,.5,"",1,0,"C",true);
                $pdf->Ln(1);
                $pdf->SetFont('Arial','B',12);
                if ($opcion==3) {
                    $pdf->Cell(190,5,"LISTA COMPLETA DE EQUIPOS",0,0,"C");
                }
                if ($opcion==1) {
                    $pdf->Cell(190,5,"LISTA EQUIPOS INSCRITOS",0,0,"C");
                }
                if ($opcion==2) {
                    $pdf->Cell(190,5,"LISTA EQUIPOS NO INSCRITOS",0,0,"C");
                }
                $pdf->Ln(5);
                $pdf->Cell(190,.5,"",1,0,"C",true);
                $pdf->Ln(8);
            }

        }
        $pdf->Ln();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(190,10,'FOMENTAR EL DEPORTE ES ENCAUZAR LA'.' '.utf8_decode('NIÑEZ').' '.'Y LA JUVENTUD',0,0,"C");
        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',6);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(190,5,"REPORTE FICHA ESTADOS DE LOS EQUIPOS",0,0,"R");
        return base64_encode($pdf->Output("S","ReporteEstado.pdf"));
    }
    public function reporteJugador($datos_vista)
    {
        $temporada_actual = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $resp = DB::table('vw_catjugadores')
        ->where("TemporadaID",$temporada_actual->TemporadaID)
        ->where("JugadorID",$datos_vista["JugadorID"])
        ->get();
        $equipoant = DB::table('vw_jugadoresporequipo as je')
        ->join("vw_equiposinscritos as ji","je.InscripcionID","=","ji.InscripcionID")
        ->where("je.JugadorID",$datos_vista["JugadorID"])
        ->where("je.TemporadaID",$temporada_actual->TemporadaID)
        ->orderBy("je.TemporadaID","DESC")
        ->get();
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        $pdf->Ln();
        $pdf->Image(env("APP_URL")."/storage/logo/logo.png",10,10,40,40,'PNG','');
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"LIGA INFANTIL Y JUVENIL DE BEISBOL",0,0,"C");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"YUCATAN A.C ",0,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,utf8_decode("Afiliada a la Asociación Yucateca de Beisbol Amateur"),0,0,"C");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,utf8_decode("Unidad Deportiva Periférico Oriente ExEjido de Sitpach, Mérida, Yuc., Méx."),0,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"FICHA DE INSCRIPCION DEL JUGADOR",0,0,"C");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"AL ".$temporada_actual->Temporada,0,0,"C");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"PERIODO DEL MES DE ".$this->getMes(date('m',strtotime($temporada_actual->FechaInauguracion)))." A ".$this->getMes(date('m',strtotime($temporada_actual->FechaFinalTemporada)))." DEL ".date('Y',strtotime($temporada_actual->FechaFinalTemporada)),0,0,"C");
        $pdf->Ln(9);
        $pdf->SetFont('Arial','B',12);
        $pdf->SetFillColor(0, 158, 5);
        $pdf->SetDrawColor(0, 158, 5);
        $pdf->Cell(190,.5,"",1,0,"C",true);
        $pdf->Ln(1);
        foreach ($resp as $d) {

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,5,"NOMBRE DEL EQUIPO: ",0,0,"L");
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150,5,utf8_decode($d->Equipo),0,0,"L");
        $pdf->Ln(4);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,5,"CATEGORIA: ",0,0,"L");
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(100,5,utf8_decode($d->Categoria." ".$d->Clasificacion." ".$d->Grupo),0,0,"L");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(50,5,"FECHA LIMITE ".date('d/m/Y',strtotime($temporada_actual->FechaLimite)),0,1,"R");
        $pdf->Ln(.3);
        $pdf->SetFont('Arial','B',12);
        $pdf->SetFillColor(0, 158, 5);
        $pdf->SetDrawColor(0, 158, 5);
        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(190,.5,"",1,0,"C",true);
        $pdf->Ln(6);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(60,4,"DATOS DEL JUGADOR",1,0,"C",true);
        $pdf->Cell(30,4,"",0,0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0 ,0,0);
        $fechains = DB::table('ly_detinscripciones')
        ->where("JugadorID",$datos_vista["JugadorID"])
        ->where("InscripcionID",$d->InscripcionID)
        ->first();
        $pdf->Cell(100,4,"FECHA INSCRIPCION: ".date('d/m/Y',strtotime($fechains->Fecha)),0,0,"R");
        $pdf->Ln();
        if ($d->Fotografia != "" && file_exists(storage_path("fotos")."/".$d->Fotografia)) {
            $extension = strtoupper(explode(".",$d->Fotografia)[1]);
            $pdf->Image(storage_path("fotos")."/".$d->Fotografia,150,69,50,60,$extension,'');
        }else{
            $pdf->Image(env("APP_URL")."/storage/logo/foto_default.jpg",150,69,50,60,'JPG','');	
        }
        $pdf->SetTextColor(0 ,0,0);
        $pdf->Cell(35,6,"NOMBRE: ",1,0,"L");
        $pdf->Cell(105,6,$d->Jugador,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"C.U.R.P: ",1,0,"L");
        $pdf->Cell(105,6,$d->Curp,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"FECHA DE NACIMIENTO ",1,0,"L");
        $pdf->Cell(105,6,date('d/m/Y',strtotime($d->FechaNacimiento)),1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"LUGAR DE NACIMIENTO",1,0,"L");
        $pdf->Cell(105,6,strtoupper(utf8_decode($d->Municipio)),1,0,"L");
        $pdf->Ln();

        $direc=explode("|", $d->Domicilio);
        $resultado = count($direc);
        if($resultado>3){

        $domicilio1	= "Calle ".$direc[0]. " # ".$direc[1];
        if($direc[2]!=0){
        $domicilio1 = $domicilio1." interior ".$direc[2];
        }
        $domicilio1 = $domicilio1." por ".$direc[3]. " y ".$direc[4];
        if($direc[1]==0){
        $domicilio1	=$direc[0];
        }
        }
        else{
        $domicilio1	= $d->Domicilio;
        }

        $numcaracteres=strlen(utf8_decode(@$direc[0]));
        if($numcaracteres>20){
                @$direc[0] = substr(utf8_decode(@$direc[0]),0,20);
        }
        for ($i=3; $i <5 ; $i++) { 
                $numcaracteres=strlen(utf8_decode(@$direc[$i]));
                if($numcaracteres>20){
                 @$direc[$i] = substr(utf8_decode(@$direc[$i]),0,20);
                } 
        }
        $pdf->Cell(35,6,utf8_decode("DIRECCIÓN"),1,0,"L");
        $pdf->Cell(12,6,"CALLE",1,0,"L");
        $pdf->Cell(50,6,@$direc[0],1,0,"L");
        $pdf->Cell(10,6,"No.",1,0,"L");
        $pdf->Cell(12,6,@$direc[1],1,0,"L");
        $pdf->Cell(10,6,"No.Int",1,0,"L");
        $pdf->Cell(11,6,@$direc[2],1,0,"L");

        $pdf->Ln();
        $pdf->Cell(35,6,"CRUZAMIENTOS",1,0,"L");
        $pdf->Cell(45,6,@$direc[3],1,0,"L");
        $pdf->Cell(15,6,"Y",1,0,"C");
        $pdf->Cell(45,6,@$direc[4],1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"COLONIA",1,0,"L");
        $pdf->Cell(105,6,$d->Colonia,1,0,"L");
        $pdf->Ln();
        foreach ($equipoant as $key) {
            $eqant=$key->EquipoSeleccion;
        }
        $pdf->Cell(35,6,"EQUIPO ANTERIOR: ",1,0,"L");
        $numcaracteres=strlen(utf8_decode($eqant));
        if($numcaracteres>75){
        $eqant = substr(utf8_decode($eqant),0,-($eqant-75));
        }
        $pdf->SetFont('Arial','B',6);
        $pdf->Cell(105,6,@$eqant,1,0,"L");/////////////////////////////////////////////////////////////
        $pdf->SetFont('Arial','B',8);


        $pdf->Ln();
        $pdf->Cell(70,6,utf8_decode("¿ESTA VACUNADO CONTRA EL TETANOS?"),1,0,"L");
        $pdf->Cell(15,6,"SI ","B",0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(20,6,"O","B",0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(15,6,"N0 ",'B',0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(20,6,"O",'BR',0,"C");
        $pdf->SetFont('Arial','B',8);
        $pdf->Ln();
        $pdf->Cell(140,6,'ESTOY ENTERADO Y HAGO CONSTAR QUE MI HIJO HA SIDO VACUNADO CONTRA EL TETANOS',1,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(70,6,utf8_decode("NÚMERO DE SEGURO SOCIAL"),1,0,"L");
        $pdf->Cell(70,6,"","B",0,"R");
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(30,6,"EDAD:",1,0,"L");
        $pdf->Cell(20,6,number_format($d->Edad, 2, '.', ''),1,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(40,6,utf8_decode("TIPO SEGURO SOCIAL"),1,0,"L");
        $pdf->Cell(15,6,"IMSS ","B",0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(10,6,"O","B",0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(15,6,"ISSTE ",'B',0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(10,6,"O",'B',0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(15,6,"POPULAR ",'B',0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(10,6,"O",'B',0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(15,6,"OTRO: ",'B',0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(600,6,"",'BR',0,"C");
        $pdf->SetFont('Arial','B',8);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','B',8);
        $pdf->SetFillColor(0, 158, 5);
        $pdf->SetDrawColor(0, 158, 5);
        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(60,4,"DATOS DE LOS PADRES",1,0,"C",true);
        $pdf->Ln();
        $pdf->SetTextColor(0 ,0,0);
        $pdf->Cell(40,7,"NOMBRE PADRE: ",1,0,"L");
        $pdf->Cell(150,7,$d->NPadre." ".$d->APPadre." ".$d->AMPadre,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,7,"TELEFONO:  ",1,0,"L");
        $pdf->Cell(150,7,$d->TelPadre,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,7,"NOMBRE MADRE: ",1,0,"L");
        $pdf->Cell(150,7,$d->NMadre." ".$d->APMadre." ".$d->AMMadre,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,7,"TELEFONO: ",1,0,"L");
        $pdf->Cell(150,7,$d->TelMadre,1,0,"L");
        $pdf->Ln(10);

        $ent=DB::table('ly_encinscripciones')->where("InscripcionID",$d->InscripcionID)
        ->get();
        foreach ($ent as $k) {
            $nomEntr=$k->NomEnt;
            $apellidosEntr=$k->APatEnt." ".$k->AMatEnt;
            $telEntr=$k->TelEnt;
            $mailEntr=$k->MailEnt;
        }

        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(95,5,"MANEJADOR",1,0,"C",true);
        $pdf->Cell(95,5,"TUTOR LEGAL",1,0,"C",true);
        $pdf->Ln();
        $pdf->Cell(95,20,"",1,0,"C");
        $pdf->Cell(95,20,"",1,0,"C");
        $pdf->Ln();
        $pdf->SetFillColor(230,230,230);
        $pdf->SetDrawColor(0, 158, 5);
        $pdf->SetTextColor(0,158,5);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(95,5,"FIRMA",1,0,"C",true);
        $pdf->Cell(95,5,"FIRMA",1,0,"C",true);

        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(95,5,$nomEntr,'L',0,"C");
        $pdf->Cell(95,5,$d->NRepresentante,'LR',0,"C");
        $pdf->Ln();
        $pdf->Cell(95,5,$apellidosEntr,'L',0,"C");
        $pdf->Cell(95,5,$d->APRepresentante." ".$d->AMRepresentante,'LR',0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',10);
        $pdf->SetTextColor(0,158,5);
        $pdf->Cell(95,5,"NOMBRE",1,0,"C",true);
        $pdf->Cell(95,5,"NOMBRE",1,0,"C",true);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(95,10,$telEntr,1,0,"C");
        $pdf->Cell(95,10,$d->TelRepresentante,1,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',10);
        $pdf->SetTextColor(0,158,5);
        $pdf->Cell(95,5,"TELEFONO",1,0,"C",true);
        $pdf->Cell(95,5,"TELEFONO",1,0,"C",true);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0,0,0);
        if($mailEntr == "NO DISPONIBLE"){
            $mailEntr = "";
        }
        $pdf->Cell(95,10,$mailEntr,1,0,"C");
        $pdf->Cell(95,10,$d->CorreoRepresentante,1,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',10);
        $pdf->SetTextColor(0,158,5);
        $pdf->Cell(95,5,"CORREO ELECTRONICO",1,0,"C",true);
        $pdf->Cell(95,5,"CORREO ELECTRONICO",1,0,"C",true);
        $pdf->Ln(8);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(190,10,'FOMENTAR EL DEPORTE ES ENCAUZAR LA'.' '.utf8_decode('NIÑEZ').' '.'Y LA JUVENTUD',0,0,"C");
        $pdf->Ln(12);
        $pdf->SetFont('Arial','B',6);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(190,5,"REPORTE FICHA DE JUGADOR",0,0,"R");
        return base64_encode($pdf->Output("S","ReporteJugador.pdf"));
        }
    }
    public function reporteJugadores($datos_vista)
    {
        //OBTENCIÓN DE DATOS
        $temporada_actual = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $res= DB::table('ly_catjugadores as j')
        ->select("j.*",DB::raw("CONCAT(ce.Equipo, ' ' , cc.Categoria ,' ' , e.Clasificacion) AS EquipoActual"))
        ->leftJoin('ly_detinscripciones as d', 'd.JugadorID', '=', 'j.JugadorID')
        ->leftJoin('ly_encinscripciones as e', 'd.InscripcionID', '=','e.InscripcionID')
        ->leftJoin('ly_catequipos as ce', 'e.EquipoID', '=', 'ce.EquipoID')
        ->leftJoin('ly_catcategorias as cc', 'e.CategoriaID', '=', 'cc.CategoriaID')
        ->where("e.TemporadaID",$temporada_actual->TemporadaID)
        ->get();
        //PINTAR EXCEL
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
        ->setCreator("Serteza")
        ->setLastModifiedBy("Serteza")
        ->setTitle("Exportar excel desde mysql")
        ->setSubject("Jugadores")
        ->setDescription("Documento generado por Serteza")
        ->setKeywords("Serteza")
        ->setCategory("Jugadores");
        $i=1;
        $objRichText = new RichText();
        $objBold = $objRichText->createTextRun('LIGA INFANTIL Y JUVENIL DE BEISBOL YUCATAN A.C. TEMPORADA 93');
        $objBold->getFont()->setBold(true);

        $spreadsheet->getActiveSheet()->getCell('F1')->setValue($objRichText);
        $i++;
        foreach(range('A','R') as $columnID) {
            $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('A'.$i, "JugadorID");
        $spreadsheet->getActiveSheet()->getStyle('A2:R2')
        ->getFill()->setFillType(Fill::FILL_SOLID);
        $spreadsheet->getActiveSheet()->getStyle('A2:R2')
        ->getFill()->getStartColor()->setRGB('A8DF16');
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('B'.$i, "Apellido Paterno");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('C'.$i, "Apellido Materno");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('D'.$i, "Nombres");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('E'.$i, "Fecha de Nacimiento");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('F'.$i, "Dirección");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('G'.$i, "Telefono");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('H'.$i, "Municipio");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('I'.$i, "Estado");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('J'.$i, "Sexo");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('K'.$i, "Curp");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('L'.$i, "Estado");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('M'.$i, "CodigoPostal");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('N'.$i, "RepresentanteLegal");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('O'.$i, "CorreoRepresentante");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('P'.$i, "TelRepresentante");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('Q'.$i, "EquipoActual");  
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('R'.$i, "EquipoAnterior");
        $i++;
        foreach($res as $datos){
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('A'.$i, $datos->JugadorID);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('B'.$i, $datos->ApellidoPaterno);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('C'.$i, $datos->ApellidoMaterno);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('D'.$i, $datos->Nombre);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('E'.$i, $datos->FechaNacimiento);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('F'.$i, $datos->Domicilio);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('G'.$i, $datos->Telefono);
            $spreadsheet->getActiveSheet()->getStyle('G'.$i)->getNumberFormat()->setFormatCode('0000');
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('H'.$i, $datos->Municipio);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('I'.$i, "YUCATÁN");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('J'.$i, $datos->Sexo);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('K'.$i, $datos->Curp);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('L'.$i, $datos->Estado);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('M'.$i, $datos->CodigoPostal);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('N'.$i, $datos->NRepresentante." ".$datos->APRepresentante. " ".$datos->AMRepresentante);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('O'.$i, $datos->CorreoRepresentante);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('P'.$i, $datos->TelRepresentante);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('Q'.$i, $datos->EquipoActual);
            //CONSULTA AUX
            // SELECT j.JugadorID, CONCAT(ce.Equipo, ' ' , cc.Categoria ,' ' , e.Clasificacion) AS EquipoAnterior 
            // FROM ly_catjugadores j 
            // LEFT OUTER JOIN ly_detinscripciones d ON d.JugadorID = j.JugadorID 
            // LEFT OUTER JOIN ly_encinscripciones e ON d.InscripcionID = e.InscripcionID AND e.TemporadaID <> '$TemporadaID' 
            // LEFT OUTER JOIN ly_catequipos ce ON e.EquipoID = ce.EquipoID 
            // LEFT OUTER JOIN ly_catcategorias cc ON e.CategoriaID = cc.CategoriaID 
            // WHERE e.TemporadaID <> '$TemporadaID' GROUP BY j.JugadorID ORDER BY j.JugadorID, e.TemporadaID DESC
            $anterior = DB::table('ly_catjugadores as j')
            ->select(DB::raw("CONCAT(ce.Equipo, ' ' , cc.Categoria ,' ' , e.Clasificacion) AS EquipoAnterior"))
            ->leftJoin('ly_detinscripciones as d', 'd.JugadorID', '=', 'j.JugadorID')
            ->leftJoin('ly_encinscripciones as e', 'd.InscripcionID', '=','e.InscripcionID')
            ->leftJoin('ly_catequipos as ce', 'e.EquipoID', '=', 'ce.EquipoID')
            ->leftJoin('ly_catcategorias as cc', 'e.CategoriaID', '=', 'cc.CategoriaID')
            ->where("e.TemporadaID","<>",$temporada_actual->TemporadaID)
            ->where("j.JugadorID",$datos->JugadorID)
            ->orderBy("j.JugadorID","DESC")
            ->orderBy("e.TemporadaID","DESC")
            ->first();
            if($anterior){
                $spreadsheet->setActiveSheetIndex(0)->setCellValue('R'.$i, $anterior->EquipoAnterior);
            }else{
                $spreadsheet->setActiveSheetIndex(0)->setCellValue('R'.$i, "");
            }
            $i++;
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('excel')."/temp_excel.xlsx");
        $content = base64_encode(file_get_contents(storage_path('excel')."/temp_excel.xlsx"));
        return $content;
    }
    public function reporteCarpeta($datos_vista)
    {
        $temporada_actual = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        @$TemporadaID = $temporada_actual->TemporadaID;
        @$InscripcionID = $datos_vista['InscripcionID'];
        $mes=$this->periodos_temporada($TemporadaID);
        
        
        $r = $this->get_vw_catjugador_tem($TemporadaID, $InscripcionID);
        $contador=0;
        foreach ($r as $d) {
            $equipo=$d->Equipo;
            $categoria=$d->Categoria;
            $clasi=$d->Clasificacion;
        }
        
        $resp = $this->imprimircarpeta($InscripcionID);
        $arreglo_id = [$contador => " "];
        foreach ($resp as $datos) {
            $arreglo_id[$contador]= $datos->JugadorID;
            /*$pdf->Cell(2,10,$arreglo_id[$contador],'L',0,'L');
            $pdf->Ln();*/
            $contador++;
            $resp1 = $this->get_jugador($datos->JugadorID);
        }
        $pdf=new FPDF('P','mm','A4');
        $pdf->AddPage();
        
        ///ENCABEZADO
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(200,4,strtoupper(utf8_decode($equipo))." ".$categoria." ".$clasi."      ".$temporada_actual->Temporada,0,0,"L"); 
        $pdf->Ln();
        
        $pdf->SetFont('Arial','',9);
        
        $cont = 17;//contador mueve la imagen en posiciones diferentes para la ++FOTO++
        
        $contador1=0;
        $cont_reg_tot = 0;
        
        
        
        $num_reg=count($resp);
        $meses = array("Mes","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic");
        
        for ($i=0; $i < round(($num_reg/2),2,PHP_ROUND_HALF_UP) ; $i++) {
        
            $resp1 = $this->get_jugador($arreglo_id[$contador1]);
            if((count($arreglo_id))>($contador1+1)){
                $resp2 = $this->get_jugador($arreglo_id[$contador1+1]);
            }
            
            if(count($resp1)>0){
                
                if($resp1[0]->Fotografia != "" && file_exists(storage_path("fotos")."/".$resp1[0]->Fotografia)) {
                    $extension_1 = strtoupper(explode(".",$resp1[0]->Fotografia)[1]);
                    $foto1	= storage_path("fotos")."/".$resp1[0]->Fotografia;
                }else{
                    $extension_1 = "JPG";
                    $foto1 = env("APP_URL")."/storage/logo/foto_default.jpg";	
                }
                $apellidos1 = $resp1[0]->ApellidoPaterno." ".$resp1[0]->ApellidoMaterno;
                $nombre1	= $resp1[0]->Nombre;
                $fec=explode("-",$resp1[0]->FechaNacimiento);
                $fecha=$fec[2]."/";
                $cadena=$fec[1];
                $cadena=(string)(int)$cadena; 
                $fecha=$fecha.$meses[$cadena]."/".$fec[0];
                $fechanacimiento1 = $fecha;
                
                $direc=explode("|", $resp1[0]->Domicilio);
                $resultado = count($direc);
                
                if($resultado>2){
                    $domicilio1	= "Calle ".$direc[0]. " # ".$direc[1];
                    if($direc[2]!=0){
                    $domicilio1 = $domicilio1." interior ".$direc[2];
                    }
                    $domicilio1 = $domicilio1." por ".$direc[3]. " y ".$direc[4];
                    if($direc[1]===""){
                    $domicilio1	=$direc[1];
                    }
                }else{
                    $domicilio1	= $resp1[0]->Domicilio;
                }
                $telefono1	= $resp1[0]->Telefono;
            }
            
            if((count($arreglo_id))>($contador1+1)){
                if(count($resp2)>0){
                    if($resp2[0]->Fotografia != "" && file_exists(storage_path("fotos")."/".$resp2[0]->Fotografia)) {
                        $extension_2 = strtoupper(explode(".",$resp2[0]->Fotografia)[1]);
                        $foto2	= storage_path("fotos")."/".$resp2[0]->Fotografia;
                    }else{
                        $extension_2 = "JPG";
                        $foto2 = env("APP_URL")."/storage/logo/foto_default.jpg";
                    }
            
                    $apellidos2 = $resp2[0]->ApellidoPaterno." ".$resp2[0]->ApellidoMaterno;
                    $nombre2	= $resp2[0]->Nombre;
            
                    $fec=explode("-",$resp2[0]->FechaNacimiento);
                    $fecha=$fec[2]."/";
                    $cadena=$fec[1];
                    $cadena=(string)(int)$cadena; 
                    $fecha=$fecha.$meses[$cadena]."/".$fec[0];
                    $fechanacimiento2 = $fecha;
            
                    //$fechanacimiento2 = $datos2['FechaNacimiento'];
                    
                    $direc=explode("|", $resp2[0]->Domicilio);
                    $resultado = count($direc);
                    if($resultado>2){
                        $domicilio2	= "Calle ".$direc[0]. " # ".$direc[1];
                        if($direc[2]!=0){
                            $domicilio2 = $domicilio2." interior ".$direc[2];
                        }
                        $domicilio2 = $domicilio2." por ".$direc[3]. " y ".$direc[4];
                        if($direc[1]===""){
                            $domicilio2	=$direc[0];
                        }
                    }else{
                        $domicilio2	= $resp2[0]->Domicilio;
                    }
                    $telefono2	= $resp2[0]->Telefono;
                }
            }else{
                $foto2 = env("APP_URL")."/storage/logo/foto_default.jpg";
                $apellidos2 = "";
                $nombre2	= "";
                $fechanacimiento2 = "";
                $domicilio2	= "";
                $telefono2	= "";
            }
            
            
            
            $pdf->Cell(97,2,"",'L,T,R',0,'L');///espacios en blancos
            
            $pdf->Cell(5,2,"",0,0,'L');
            
            $pdf->Cell(97,2,"",'L,T,R',1,'L');
            
            /////////////
            
            $pdf->Cell(2,7,"",'L',0,'L');
            $pdf->Cell(25,25,"",1,0,'L');
            $pdf->Image($foto1,13,$cont,23,23,$extension_1,'');///FOTO
            
            $pdf->Cell(15,7,"Nombre:",0,0,'L');
            $pdf->Cell(52,7,$apellidos1,'B',0,'L');///datos NOMBRE
            $pdf->Cell(3,7,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');///esapacio en blanco
            
            $pdf->Cell(2,7,"",'L',0,'L');
            $pdf->Cell(25,25,"",1,0,'L');
            $pdf->Image($foto2,115,$cont,23,23,$extension_2,'');///FOTO
            
            $pdf->Cell(15,7,"Nombre:",0,0,'L');
            $pdf->Cell(52,7,$apellidos2,'B',0,'L');///datos NOMBRE
            $pdf->Cell(3,7,"",'R',1,'L');
            
            ///////////
            
            $pdf->Cell(2,8,"",'L',0,'L');
            $pdf->Cell(28,8,"",0,0,'L');
            $pdf->Cell(64,8,$nombre1,'B',0,'L');///MAS DATOS NOMBRE
            $pdf->Cell(3,8,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');///espacio en blanco
            
            $pdf->Cell(2,8,"",'L',0,'L');
            $pdf->Cell(28,8,"",0,0,'L');
            $pdf->Cell(64,8,$nombre2,'B',0,'L');///MAS DATOS NOMBRE
            $pdf->Cell(3,8,"",'R',1,'L');
            
            ////////////////
            
            $pdf->Cell(2,9,"",'L',0,'L');
            $pdf->Cell(27,9,"",0,0,'L');
            $pdf->Cell(37,9,"Fecha de Nacimiento:",0,0,'L');
            $pdf->Cell(28,9,$fechanacimiento1,'B',0,'L');///DATOS FECHA DE NACIMIENTO
            $pdf->Cell(3,9,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');///espacio en blanco
            
            $pdf->Cell(2,9,"",'L',0,'L');
            $pdf->Cell(27,9,"",0,0,'L');
            $pdf->Cell(37,9,"Fecha de Nacimiento:",0,0,'L');
            $pdf->Cell(28,9,$fechanacimiento2,'B',0,'L');///DATOS FECHA DE NACIMIENTO
            $pdf->Cell(3,9,"",'R',1,'L');
            
            //////////////////////
            
            $pdf->Cell(2,7,"",'L',0,'L');
            $pdf->Cell(18,7,"Domicilio:",0,0,'L');
            $pdf->Cell(74,7,$domicilio1,'B',0,'L');///DATOS DOMICILIO
            $pdf->Cell(3,7,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');//espacio en blanco
            
            $pdf->Cell(2,7,"",'L',0,'L');
            $pdf->Cell(18,7,"Domicilio:",0,0,'L');
            $pdf->Cell(74,7,$domicilio2,'B',0,'L');///DATOS DOMICILIO
            $pdf->Cell(3,7,"",'R',1,'L');
            
            ///////////////////////
            
            $pdf->Cell(2,5,"",'L',0,'L');
            $pdf->Cell(18,5,"Telefono:",0,0,'L');
            $pdf->Cell(74,5,$telefono1,'B',0,'L');///DATOS TELEFONO
            $pdf->Cell(3,5,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');//espacio en blanco
            
            $pdf->Cell(2,5,"",'L',0,'L');
            $pdf->Cell(18,5,"Telefono:",0,0,'L');
            $pdf->Cell(74,5,$telefono2,'B',0,'L');///DATOS TELEFONO
            $pdf->Cell(3,5,"",'R',1,'L');
            
            ///////////////////////////
            
            $pdf->Cell(97,3,"",'R,B,L',0,'L');///espacios en blancos
            
            $pdf->Cell(5,2,"",0,0,'L');
            
            $pdf->Cell(97,3,"",'R,B,L',1,'L');
            
            ///////////////////////////
            
            $pdf->Cell(97,3,"",0,0,'L');///espacios en blancos
            
            $pdf->Cell(5,2,"",0,0,'L');
            
            $pdf->Cell(97,3,"",0,1,'L');
            
            //////////////////////
            
            $cont = $cont + 44;
            
            $contador1=$contador1+2;
            
            $cont_reg_tot++;
            
            if ($cont_reg_tot==6) {
                /// nuevo encabezado
                
                $pdf->SetFont('Arial','B',12);
                $pdf->Cell(200,4,$equipo." ".$categoria." ".$clasi."      ".$temporada_actual->Temporada,0,0,"L");
                $pdf->Ln();
                
                $pdf->SetFont('Arial','',10);
                
                $cont_reg_tot = 0;
                $cont =20;
            }
        
        }
        /*impresion de  representante y auyudante*/
        
        $resp3= $this->get_equipo_temp_inc($InscripcionID,$TemporadaID);
        foreach ($resp3 as $datos3) {
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(200,7,"REPRESENTANTE                                                       ENTRENADOR",0,0,"L"); 
            $pdf->Ln();
            
            $pdf->SetFont('Arial','',9);
            
            $pdf->Cell(97,2,"",'L,T,R',0,'L');///espacios en blancos
            
            $pdf->Cell(5,2,"",0,0,'L');
            
            $pdf->Cell(97,2,"",'L,T,R',1,'L');
            
            
            
            /////////////
            
            $pdf->Cell(2,7,"",'L',0,'L');
            $pdf->Cell(25,25,"",1,0,'L');
            if ($datos3->FotoRep != "" && file_exists(storage_path("fotos")."/".$datos3->FotoRep)) {
                $extension = strtoupper(explode(".",$datos3->FotoRep)[1]);
                $foto = storage_path("fotos")."/".$datos3->FotoRep;
                $pdf->Image($foto,13,$cont+7,23,23,$extension,'');///FOTO
            }else{
                $foto = env("APP_URL")."/storage/logo/foto_default.jpg";
                $pdf->Image($foto,13,$cont+7,23,23,'JPG','');///FOTO	
            }
            $pdf->Cell(15,7,"Nombre:",0,0,'L');
            $pdf->Cell(52,7,$datos3->APatRep.' '.$datos3->AMatRep,'B',0,'L');///datos NOMBRE
            $pdf->Cell(3,7,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');///esapacio en blanco
            
            $pdf->Cell(2,7,"",'L',0,'L');
            $pdf->Cell(25,25,"",1,0,'L');
            if ($datos3->FotoEnt != "" && file_exists(storage_path("fotos")."/".$datos3->FotoEnt)) {
                $extension = strtoupper(explode(".",$datos3->FotoEnt)[1]);
                $foto = storage_path("fotos")."/".$datos3->FotoEnt;
                $pdf->Image($foto,115,$cont+7,23,23,$extension,'');///FOTO
            }else{
                $foto = env("APP_URL")."/storage/logo/foto_default.jpg";
                $pdf->Image($foto,115,$cont+7,23,23,'JPG','');///FOTO	
            }
            
            
            $pdf->Cell(15,7,"Nombre:",0,0,'L');
            $pdf->Cell(52,7,$datos3->APatEnt.' '.$datos3->AMatEnt,'B',0,'L');;///datos NOMBRE
            $pdf->Cell(3,7,"",'R',1,'L');
            
            ///////////
            
            $pdf->Cell(2,8,"",'L',0,'L');
            $pdf->Cell(28,8,"",0,0,'L');
            $pdf->Cell(64,8,$datos3->NomRep,'B',0,'L');///MAS DATOS NOMBRE
            $pdf->Cell(3,8,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');///espacio en blanco
            
            $pdf->Cell(2,8,"",'L',0,'L');
            $pdf->Cell(28,8,"",0,0,'L');
            $pdf->Cell(64,8,$datos3->NomEnt,'B',0,'L');///MAS DATOS NOMBRE
            $pdf->Cell(3,8,"",'R',1,'L');
            
            ////////////////
            
            $pdf->Cell(2,9,"",'L',0,'L');
            $pdf->Cell(27,9,"",0,0,'L');
            $pdf->Cell(17,9,"Telefono:",0,0,'L');
            $pdf->Cell(48,9,$datos3->TelRep,'B',0,'L');///DATOS FECHA DE NACIMIENTO
            $pdf->Cell(3,9,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');///espacio en blanco
            
            $pdf->Cell(2,9,"",'L',0,'L');
            $pdf->Cell(27,9,"",0,0,'L');
            $pdf->Cell(17,9,"Telefono",0,0,'L');
            $pdf->Cell(48,9,$datos3->TelEnt,'B',0,'L');///DATOS FECHA DE NACIMIENTO
            $pdf->Cell(3,9,"",'R',1,'L');
            
            //////////////////////
            
            $pdf->Cell(2,7,"",'L',0,'L');
            $pdf->Cell(18,7,"Domicilio:",0,0,'L');
            $pdf->Cell(74,7,"",'B',0,'L');///DATOS DOMICILIO
            $pdf->Cell(3,7,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');//espacio en blanco
            
            $pdf->Cell(2,7,"",'L',0,'L');
            $pdf->Cell(18,7,"Domicilio:",0,0,'L');
            $pdf->Cell(74,7,"",'B',0,'L');///DATOS DOMICILIO
            $pdf->Cell(3,7,"",'R',1,'L');
            
            ///////////////////////
            
            $pdf->Cell(2,10,"",'L',0,'L');
            $pdf->Cell(18,10,"Firma:",0,0,'L');
            $pdf->Cell(74,10,"",'B',0,'L');///DATOS TELEFONO
            $pdf->Cell(3,10,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');//espacio en blanco
            
            $pdf->Cell(2,10,"",'L',0,'L');
            $pdf->Cell(18,10,"Firma:",0,0,'L');
            $pdf->Cell(74,10,"",'B',0,'L');///DATOS TELEFONO
            $pdf->Cell(3,10,"",'R',1,'L');
            
            ///////////////////////////
            
            $pdf->Cell(97,3,"",'R,B,L',0,'L');///espacios en blancos
            
            $pdf->Cell(5,2,"",0,0,'L');
            
            $pdf->Cell(97,3,"",'R,B,L',1,'L');
            
            ///////////////////////////
            
            $pdf->Cell(97,3,"",0,0,'L');///espacios en blancos
            
            $pdf->Cell(5,2,"",0,0,'L');
            
            $pdf->Cell(97,3,"",0,1,'L');
            
            //////////////////////
            
            $cont = $cont + 56;
            
            
            
            //////////////////////////////////////////
            
            $pdf->Cell(97,3,"",0,0,'L');///espacios en blancos
            
            $pdf->Cell(5,2,"",0,0,'L');
            
            $pdf->Cell(97,3,"",0,1,'L');
            
            
            $pdf->SetFont('Arial','',10);
            /*$pdf->Image('../img/imagenes/logo-liga3.jpg',13,13,25,25,'JPG','');*/
            $pdf->Cell(200,7,"AYUDANTE                                                       ",0,0,"L"); 
            $pdf->Ln();
            
            //////////////////////
            
            $pdf->Cell(97,2,"",'L,T,R',0,'L');///espacios en blancos
            
            $pdf->Cell(5,2,"",0,0,'L');
            
            $pdf->Cell(97,2,"",'L,T,R',1,'L');
            
            
            
            
            /////////////
            $pdf->SetFont('Arial','',9);
            
            $pdf->Cell(2,7,"",'L',0,'L');
            $pdf->Cell(25,25,"",1,0,'L');
            if ($datos3->FotoAy != "" && file_exists(storage_path("fotos")."/".$datos3->FotoAy)) {
                $extension = strtoupper(explode(".",$datos3->FotoAy)[1]);
                $foto = storage_path("fotos")."/".$datos3->FotoAy;
                $pdf->Image($foto,13,$cont+10,23,23,$extension,'');///FOTO
            }else{
                $foto = env("APP_URL")."/storage/logo/foto_default.jpg";
                $pdf->Image($foto,13,$cont+10,23,23,'JPG','');///FOTO	
            }
            $pdf->Cell(15,7,"Nombre:",0,0,'L');
            $pdf->Cell(52,7,$datos3->APatAy.' '.$datos3->AMatAy,'B',0,'L');///datos NOMBRE
            $pdf->Cell(3,7,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');///esapacio en blanco
            
            $pdf->Cell(2,7,"",'L',0,'L');
            $pdf->Cell(25,25,"",1,0,'L');
            if ($datos3->FotoAy != "" && file_exists(storage_path("fotos")."/".$datos3->FotoAy)) {
                $foto = env("APP_URL")."/storage/logo/foto_default.jpg";
                $pdf->Image($foto,115,$cont+10,23,23,'JPG','');///FOTO
            }else{
                $foto = env("APP_URL")."/storage/logo/foto_default.jpg";
                $pdf->Image($foto,115,$cont+10,23,23,'JPG','');///FOTO	
            }
            
            
            $pdf->Cell(15,7,"Nombre:",0,0,'L');
            $pdf->Cell(52,7,"",'B',0,'L');;///datos NOMBRE
            $pdf->Cell(3,7,"",'R',1,'L');
            
            ///////////
            
            $pdf->Cell(2,8,"",'L',0,'L');
            $pdf->Cell(28,8,"",0,0,'L');
            $pdf->Cell(64,8,$datos3->NomAy,'B',0,'L');///MAS DATOS NOMBRE
            $pdf->Cell(3,8,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');///espacio en blanco
            
            $pdf->Cell(2,8,"",'L',0,'L');
            $pdf->Cell(28,8,"",0,0,'L');
            $pdf->Cell(64,8,"",'B',0,'L');///MAS DATOS NOMBRE
            $pdf->Cell(3,8,"",'R',1,'L');
            
            ////////////////
            
            $pdf->Cell(2,9,"",'L',0,'L');
            $pdf->Cell(27,9,"",0,0,'L');
            $pdf->Cell(17,9,"Telefono",0,0,'L');
            $pdf->Cell(48,9,$datos3->TelAy,'B',0,'L');///DATOS FECHA DE NACIMIENTO
            $pdf->Cell(3,9,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');///espacio en blanco
            
            $pdf->Cell(2,9,"",'L',0,'L');
            $pdf->Cell(27,9,"",0,0,'L');
            $pdf->Cell(17,9,"Telefono:",0,0,'L');
            $pdf->Cell(48,9,"",'B',0,'L');///DATOS FECHA DE NACIMIENTO
            $pdf->Cell(3,9,"",'R',1,'L');
            
            //////////////////////
            
            $pdf->Cell(2,7,"",'L',0,'L');
            $pdf->Cell(18,7,"Domicilio:",0,0,'L');
            $pdf->Cell(74,7,"",'B',0,'L');///DATOS DOMICILIO
            $pdf->Cell(3,7,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');//espacio en blanco
            
            $pdf->Cell(2,7,"",'L',0,'L');
            $pdf->Cell(18,7,"Domicilio:",0,0,'L');
            $pdf->Cell(74,7,"",'B',0,'L');///DATOS DOMICILIO
            $pdf->Cell(3,7,"",'R',1,'L');
            
            ///////////////////////
            
            $pdf->Cell(2,10,"",'L',0,'L');
            $pdf->Cell(18,10,"Firma:",0,0,'L');
            $pdf->Cell(74,10,"",'B',0,'L');///DATOS TELEFONO
            $pdf->Cell(3,10,"",'R',0,'L');
            
            $pdf->Cell(5,2,"",0,0,'L');//espacio en blanco
            
            $pdf->Cell(2,10,"",'L',0,'L');
            $pdf->Cell(18,10,"Firma:",0,0,'L');
            $pdf->Cell(74,10,"",'B',0,'L');///DATOS TELEFONO
            $pdf->Cell(3,10,"",'R',1,'L');
            
            ///////////////////////////
            
            $pdf->Cell(97,3,"",'R,B,L',0,'L');///espacios en blancos
            
            $pdf->Cell(5,2,"",0,0,'L');
            
            $pdf->Cell(97,3,"",'R,B,L',1,'L');
            
            ///////////////////////////
            
            $pdf->Cell(97,3,"",0,0,'L');///espacios en blancos
            
            $pdf->Cell(5,2,"",0,0,'L');
            
            $pdf->Cell(97,3,"",0,1,'L');
            
            //////////////////////
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
            $pdf->SetFont('Arial','B',15);
            
            $pdf->Cell(10,1,"",'0,0,0',0,'L');
            $pdf->Cell(80,1,"",'0,B,0',0,'L');////Firmas
            $pdf->Cell(10,1,"",'0,0,0',0,'L');
            $pdf->Cell(80,1,"",'0,B,0',0,'L');
            
            $pdf->Ln();
            
            
            $pdf->Cell(80,10,"PRESIDENTE",0,0,'C');
            $pdf->Cell(20,10,"",0,0,'C');///DATOS TELEFONO
            $pdf->Cell(80,10,"SECRETARIO",0,0,'C');
        }
        return base64_encode($pdf->Output("S","ReporteJugador.pdf"));
    }
    public function getMes($mes){
        switch($mes){
          case '1' : return 'ENERO';
          case '2' : return 'FEBRERO';
          case '3' : return 'MARZO';
          case '4' : return 'ABRIL';
          case '5' : return 'MAYO';
          case '6' : return 'JUNIO';
          case '7' : return 'JULIO';
          case '8' : return 'AGOSTO';
          case '9' : return 'SEPTIEMBRE';
          case '10' : return 'OCTUBRE';
          case '11' : return 'NOVIEMBRE';
          case '12' : return 'DICIEMBRE';
        }
    }
    
    public function get_vw_catjugador_tem($TemporadaID,$InscripcionID){
        $resp = DB::table("vw_catjugadores")
        ->where("TemporadaID",$TemporadaID)
        ->where("InscripcionID",$InscripcionID)
        ->limit(1)
        ->get();
        return $resp;
    }
    public function imprimircarpeta($InscripcionID){
        $resp = DB::table("vw_jugadoresporequipo as je")
        ->select("je.JugadorID", 'je.Jugador', 'je.Edad', 'je.FechaNacimiento', 'je.Curp',  'je.Fotografia', 'ei.EquipoSeleccion')
        ->join('vw_equiposinscritos as ei','ei.InscripcionID',"=",'je.InscripcionID')
        ->where("je.InscripcionID",$InscripcionID)
        ->groupBy("je.JugadorID")
        ->get();
        return $resp;
    }
    public function get_jugador($JugadorID){
        $resp = DB::table("ly_catjugadores")
        ->where("JugadorID",$JugadorID)
        ->get();
        return $resp;
    }
    public function periodos_temporada($TemporadaID){
        $resp = DB::table("ly_cattemporadas")
        ->select("FechaInauguracion","FechaFinalTemporada")
        ->where("TemporadaID",$TemporadaID)
        ->get();
        foreach($resp as $k){
            $fechas=explode("-",$k->FechaInauguracion);
            $mes[0]=$fechas[1];
            $fechas=explode("-",$k->FechaFinalTemporada);
            $mes[1]=$fechas[1];
            $mes[2]=$fechas[0];
        }
        return $mes;
    }
    public function get_equipo_temp_inc($InscripcionID,$TemporadaID){
        $resp = DB::table("ly_encinscripciones")
        ->where("TemporadaID",$TemporadaID)
        ->where("InscripcionID",$InscripcionID)
        ->get();
        return $resp;
    }
}