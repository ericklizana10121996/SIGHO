<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'importHistoriaExcel','importHistoria','importTarifario','importCie','importApellidoExcel','producto/vistamedico','vistamedico2','seguimiento/alerta','importStock'
    ];
}
