<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Departamento;
use App\Http\Controllers\Controller;

class ProvinciaController extends Controller
{
    /**
     * Método para generar combo provincia
     * @param  [type] $departamento_id [description]
     * @return [type]                  [description]
     */
    public function cboprovincia($departamento_id = null)
    {
        $existe = Libreria::verificarExistencia($departamento_id, 'departamento');
        if ($existe !== true) {
            return $existe;
        }
        $departamento = Departamento::find($departamento_id);
        $provincias = $departamento->provincias;
        if (count($provincias)>0) {
            $cadena = '';
            foreach ($provincias as $key => $value) {
                $cadena .= '<option value="'.$value->id.'">'.$value->nombre.'</option>';
            }
            return $cadena;
        } else {
            return '';
        }
    }
}
