<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Laravel\Lumen\Routing\Controller as BaseController;
use GuzzleHttp\Client;
use App\Models\Inscripcion;

class Controller extends BaseController
{
    public function enviarCorreo($datos)
    {
        $baseUrl = env('API_CORREO');
        $client = new Client();
        $response = $client->request('POST', $baseUrl, [
                'form_params' => $datos
        ]);
        return $response->getBody();
    }
    public function temporadaActual()
    {
        $temporada = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        if($temporada){
           return $this->crearRespuesta(1,$temporada->Temporada,200);
        }
        return $this->crearRespuesta(2,"Aun no se tiene una temporada actual",301);
    }
    public function obtenerEquipo($usuario_id)
    {
        $temporada_id = DB::table('ly_cattemporadas')->where("Actual",1)->first();
        $datos = Inscripcion::select("Equipo")
        ->leftJoin('ly_catequipos as ce', 'ly_encinscripciones.EquipoID', '=', 'ce.EquipoID')
        ->where("UsuarioID",$usuario_id)
        ->where("TemporadaID",$temporada_id->TemporadaID)
        ->first();
        if($datos){
                return $this->crearRespuesta(1,$datos,200);   
        }
        return $this->crearRespuesta(2,"No se encontro el nombre del equipo",301);
    }
    public function catalogoCategorias()
    {
        $categorias = DB::table('ly_catcategorias')
        ->get();
        if(count($categorias)>0){
            return $this->crearRespuesta(1,$categorias,200);
        }
        return $this->crearRespuesta(2,"Aun no se tienen categorias",301);
    }
    
    public function getSigId($nombre_tabla){
        $bol = true;
        $utlimo = DB::table($nombre_tabla)
        ->get();
        if(count($utlimo) > 0){
                $utlimo = $utlimo->last();
                $id = "";
                foreach($utlimo as $parametro){
                        if($bol){
                                $id = $parametro;
                                $bol = false;
                        }
                }
                return (intval($id)+1);
        }elseif(count($utlimo) == 0){
                return 1;
        }
    }

    public function crearRespuesta($tipo,$obj,$http_response){
        if($tipo == 1){ //Success
            return response()->json(['ok' => true, 'data' => $obj], $http_response);
        }
        if($tipo == 2) {    //Failed
            return response()->json(['ok' => false, 'message' => $obj], $http_response);
        }
    }

    public function getHoraFechaActual(){
        $mytime = Carbon::now();
        return $mytime;
    }

    public function decode_json($code){
        $ultimoCharacter = substr($code,-1);
        $restante = substr($code,0,-1);
        $digitos = substr($restante,-2);
        $longitud = $this->convert1toInvers($digitos[0]).$this->convert1toInvers($digitos[1]);
        $iteraciones = substr($code,0,1);
        $ite=  $this->convertAto1($iteraciones);
        $descrypt = substr($code,0,$longitud+1);
        $descrypt = substr($descrypt,1);
        $cola = substr($code,0,-3);
        $cola = substr($cola,$longitud+1);
        for($i=0; $i<$ite; $i++){
                $descrypt= base64_decode($descrypt);
        }
        $resu = $descrypt.$cola.$ultimoCharacter;
        return base64_decode($resu);
    }
    public function encode_json($code){
        $rand = rand(3,9);
        $base_64 = base64_encode($code);
        $cabecera = substr($base_64, 0, 3);
        $cola = substr($base_64,3);
        for($r =0; $r<$rand; $r++){
                $cabecera = base64_encode($cabecera);
        }
        $longitud = strlen($cabecera);
        $cabecera_cola = substr($cola, 0, -1);
        $cola_cola = substr($cola, -1);
        $longitud_1er = substr($longitud,0,1);
        $longitud_2do = substr($longitud, -1);
        $letras = $this->convert1toInvers($longitud_1er) . $this->convert1toInvers($longitud_2do);
        $letra_rand = $this->convertAto1($rand);
        return $letra_rand.$cabecera.$cabecera_cola.$letras.$cola_cola;
    }

    public function convertAto1($num){
        if(is_numeric($num)){
            switch($num) {
                case 3: return "e";
                        break;
                case 4: return "A";
                        break;
                case 5: return "r";
                        break;
                case 6: return "M";
                        break;
                case 7: return "z";
                        break;
                case 8: return "L";
                        break;
                case 9: return "S";
                        break; 
            }
        }else{
            switch($num) {
                case "e": return 3;
                        break;
                case "A": return 4;
                        break;
                case "r": return 5;
                        break;
                case "M": return 6;
                        break;
                case "z": return 7;
                        break;
                case "L": return 8;
                        break;
                case "S": return 9;
                        break;
                        
            }
        }
    
    }
    public function convert1toInvers($num){
        if(is_numeric($num)){
            switch($num) {
                case 0: return "z";
                        break;
                case 1: return "Y";
                        break;
                case 2: return "x";
                        break;
                case 3: return "W";
                        break;
                case 4: return "v";
                        break;
                case 5: return "U";
                        break;
                case 6: return "t";
                        break;
                case 7: return "S";
                        break;
                case 8: return "r";
                        break;
                case 9: return "Q";
                        break;
                        
            }
        }else{
            switch($num) {
                case "z": return 0;
                        break;
                case "Y": return 1;
                        break;
                case "x": return 2;
                        break;
                case "W": return 3;
                        break;
                case "v": return 4;
                        break;
                case "U": return 5;
                        break;
                case "t": return 6;
                        break;
                case "S": return 7;
                        break;
                case "r": return 8;
                        break;
                case "Q": return 9;
                        break;
            }
        }
    
    }
}
