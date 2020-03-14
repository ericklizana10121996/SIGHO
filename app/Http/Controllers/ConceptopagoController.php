<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Conceptopago;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ConceptopagoController extends Controller
{
    protected $folderview      = 'app.conceptopago';
    protected $tituloAdmin     = 'Concepto Pago';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Modificar Concepto de Pago';
    protected $tituloEliminar  = 'Eliminar Concepto de Pago';
    protected $rutas           = array('create' => 'conceptopago.create', 
            'edit'   => 'conceptopago.edit', 
            'delete' => 'conceptopago.eliminar',
            'search' => 'conceptopago.buscar',
            'index'  => 'conceptopago.index',
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar el resultado de búsquedas
     * 
     * @return Response 
     */
    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Conceptopago';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Conceptopago::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->where('tipo','LIKE','%'.$request->input('tipo').'%')->orderBy('nombre', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cuenta', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Admision', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tesoreria', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '2');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $ruta             = $this->rutas;
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entidad          = 'Conceptopago';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipo          = array("" => "Todos", "I" => "Ingreso", "E" => "Egreso");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Conceptopago';
        $conceptopago = null;
        $formData            = array('conceptopago.store');
        $cboTipo          = array("I" => "Ingreso", "E" => "Egreso");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('conceptopago', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                'nombre'                  => 'required|max:200',
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $Conceptopago       = new Conceptopago();
            $Conceptopago->nombre = strtoupper($request->input('nombre'));
            $Conceptopago->tipo = $request->input('tipo');
            $Conceptopago->cuenta = $request->input('cuenta');
            $Conceptopago->admision = $request->input('admision');
            $Conceptopago->tesoreria  = $request->input('tesoreria');
            $Conceptopago->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $cboTipo          = array("I" => "Ingreso", "E" => "Egreso");
        $conceptopago = Conceptopago::find($id);
        $entidad             = 'Conceptopago';
        $formData            = array('conceptopago.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('conceptopago', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'nombre'                  => 'required|max:200',
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request, $id){
            $conceptopago   = Conceptopago::find($id);
            $conceptopago->nombre = strtoupper($request->input('nombre'));
            $conceptopago->tipo = $request->input('tipo');
            $conceptopago->cuenta = $request->input('cuenta');
            $conceptopago->admision = $request->input('admision');
            $conceptopago->tesoreria  = $request->input('tesoreria');
            $conceptopago->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Conceptopago = Conceptopago::find($id);
            $Conceptopago->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Conceptopago::find($id);
        $entidad  = 'conceptopago';
        $formData = array('route' => array('conceptopago.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
