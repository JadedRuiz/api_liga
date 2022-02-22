<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Exports\AdminExport;

class ReporteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function obtenerPDF(Request $res)
    {
        $adminExport = new AdminExport();
        $result = $adminExport->generarReporte($res["datos"],$res["nombre"]);
        return $this->crearRespuesta(1,$result,200);
        // return response()->file($pdf->Output("ReporteEstado.pdf",'I'));
    }

    //
}
