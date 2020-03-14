<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Provincia;
use App\Http\Controllers\Controller;

class DistritoController extends Controller
{
    /**
     * Método para generar combo distrito
     * @param  [type] $provincia_id [description]
     * @return [type]               [description]
     */
    public function cbodistrito($provincia_id = null)
    {
        $existe = Libreria::verificarExistencia($provincia_id, 'provincia');
        if ($existe !== true) {
            return $existe;
        }
        $provincia = Provincia::find($provincia_id);
        $distritos = $provincia->distritos;
        if (count($distritos) > 0) {
            $cadena = '';
            foreach ($distritos as $key => $value) {
                $cadena .= '<option value="'.$value->id.'">'.$value->nombre.'</option>';
            }
            return $cadena;
        } else {
            return '';
        }
    }
}
