<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Habitacion;
use App\Tipohabitacion;
use App\Piso;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HabitacionController extends Controller
{

    protected $folderview      = 'app.habitacion';
    protected $tituloAdmin     = 'Habitacion';
    protected $tituloRegistrar = 'Registrar Habitacion';
    protected $tituloModificar = 'Modificar Habitacion';
    protected $tituloEliminar  = 'Eliminar Habitacion';
    protected $rutas           = array('create' => 'habitacion.create', 
            'edit'   => 'habitacion.edit', 
            'delete' => 'habitacion.eliminar',
            'search' => 'habitacion.buscar',
            'index'  => 'habitacion.index',
        );


     /**
     * Create a new controller instance.
     *
     * @return void
     */
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
        $entidad          = 'Habitacion';
        $nombre           = Libreria::getParam($request->input('nombre'));
        $piso             = Libreria::getParam($request->input('piso'));
        $tipohabitacion   = Libreria::getParam($request->input('tipohabitacion'));
        $resultado        = Habitacion::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('nombre', 'ASC');
        if($piso!=""){
            $resultado=$resultado->where('piso_id','=',$piso);
        }
        if($tipohabitacion!=""){
            $resultado=$resultado->where('tipohabitacion_id','=',$tipohabitacion);
        }
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Hab.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Piso', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Sexo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
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
        $entidad          = 'Habitacion';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboPiso = array('' => 'Todos...');
        $piso = Piso::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($piso as $key => $value) {
            $cboPiso = $cboPiso + array($value->id => $value->nombre);
        }
        $cboTipoHabitacion = array('' => 'Todos...');
        $piso = Tipohabitacion::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($piso as $key => $value) {
            $cboTipoHabitacion = $cboTipoHabitacion + array($value->id => $value->nombre);
        }
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboPiso', 'cboTipoHabitacion'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Habitacion';
        $habitacion = null;
        $formData = array('habitacion.store');
        $cboPiso = array();
        $piso = Piso::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($piso as $key => $value) {
            $cboPiso = $cboPiso + array($value->id => $value->nombre);
        }
        $cboTipoHabitacion = array();
        $piso = Tipohabitacion::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($piso as $key => $value) {
            $cboTipoHabitacion = $cboTipoHabitacion + array($value->id => $value->nombre);
        }
        $cboSexo = array('N' => 'Ninguno','M'=>'Masculino','F'=>'Femenino');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('habitacion', 'formData', 'entidad', 'boton', 'listar', 'cboPiso', 'cboTipoHabitacion', 'cboSexo'));
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
        $reglas     = array('nombre' => 'required|max:100');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $Habitacion       = new Habitacion();
            $Habitacion->nombre = strtoupper($request->input('nombre'));
            $Habitacion->tipohabitacion_id = $request->input('tipohabitacion_id');
            $Habitacion->piso_id = $request->input('piso_id');
            $Habitacion->sexo = $request->input('sexo');
            $Habitacion->situacion = 'D';
            $Habitacion->save();
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
        $existe = Libreria::verificarExistencia($id, 'Habitacion');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $habitacion = Habitacion::find($id);
        $entidad  = 'Habitacion';
        $formData = array('habitacion.update', $id);
        $cboPiso = array();
        $piso = Piso::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($piso as $key => $value) {
            $cboPiso = $cboPiso + array($value->id => $value->nombre);
        }
        $cboTipoHabitacion = array();
        $piso = Tipohabitacion::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($piso as $key => $value) {
            $cboTipoHabitacion = $cboTipoHabitacion + array($value->id => $value->nombre);
        }
        $cboSexo = array('N' => 'Ninguno','M'=>'Masculino','F'=>'Femenino');
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('habitacion', 'formData', 'entidad', 'boton', 'listar', 'cboPiso', 'cboTipoHabitacion', 'cboSexo'));
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
        $existe = Libreria::verificarExistencia($id, 'Habitacion');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('nombre' => 'required|max:100');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $Habitacion       = Habitacion::find($id);
            $Habitacion->nombre = strtoupper($request->input('nombre'));
            $Habitacion->tipohabitacion_id = $request->input('tipohabitacion_id');
            $Habitacion->piso_id = $request->input('piso_id');
            $Habitacion->sexo = $request->input('sexo');
            $Habitacion->save();
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
        $existe = Libreria::verificarExistencia($id, 'Habitacion');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Habitacion = Habitacion::find($id);
            $Habitacion->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'Habitacion');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Habitacion::find($id);
        $entidad  = 'Habitacion';
        $formData = array('route' => array('habitacion.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
