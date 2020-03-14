<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Horario;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;
use Illuminate\Support\Facades\Auth;


class HorarioController extends Controller
{
    protected $folderview      = 'app.horario';
    protected $tituloAdmin     = 'Horario';
    protected $tituloRegistrar = 'Registrar horario';
    protected $tituloModificar = 'Modificar horario';
    protected $tituloEliminar  = 'Eliminar horario';
    protected $rutas           = array('create' => 'horario.create', 
            'edit'   => 'horario.edit', 
            'delete' => 'horario.eliminar',
            'search' => 'horario.buscar',
            'index'  => 'horario.index',
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
        $entidad          = 'Horario';
        $person_id             = Libreria::getParam($request->input('person_id'));
        $resultado        = Horario::where('person_id','=',$person_id)->orderBy('desde', 'DESC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Desde', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Hasta', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Horarios', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Creacion', 'numero' => '1');
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
    public function index(Request $request)
    {
        $entidad          = 'Horario';
        $person_id             = Libreria::getParam($request->input('person_id'));
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta','person_id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Horario';
        $horario = null;
        
        $person_id             = Libreria::getParam($request->input('person_id'));
        $formData = array('horario.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('horario', 'formData', 'entidad', 'boton', 'listar','person_id'));
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
        $reglas = array(
            'desde.required'   => 'Ingrese desde',
            'hasta.required'   => 'Ingrese hasta',
            'person_id.required' => 'Debe ingresar un trabajador'
            );
        $mensajes = array(
            'desde'   => 'required|date_format:d/m/Y',
            'hasta'   => 'required|date_format:d/m/Y',
            'person_id' => 'required|integer|exists:person,id,deleted_at,NULL'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $horario       = new Horario();
            $horario->desde   = Date::createFromFormat('d/m/Y', $request->input('desde'))->format('Y-m-d');
            $horario->hasta   = Date::createFromFormat('d/m/Y', $request->input('hasta'))->format('Y-m-d');
            $horario->observaciones = Libreria::obtenerParametro($request->input('observaciones'));
            $horario->horarios = Libreria::obtenerParametro($request->input('horarios'));
            $horario->person_id = $request->input('person_id');
            $horario->usuario_id = $user->person_id;
            $horario->save();
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
        $existe = Libreria::verificarExistencia($id, 'horario');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $horario = Horario::find($id);
        $person_id  = Libreria::getParam($request->input('person_id'));
        
        $entidad  = 'Horario';
        $formData = array('horario.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('horario', 'formData', 'entidad', 'boton', 'listar','person_id'));
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
        $existe = Libreria::verificarExistencia($id, 'horario');
        if ($existe !== true) {
            return $existe;
        }
        $reglas = array(
            'desde.required'   => 'Ingrese desde',
            'hasta.required'   => 'Ingrese hasta',
            'person_id.required' => 'Debe ingresar un trabajador'
            );
        $mensajes = array(
            'desde'   => 'required|date_format:d/m/Y',
            'hasta'   => 'required|date_format:d/m/Y',
            'person_id' => 'required|integer|exists:person,id,deleted_at,NULL'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $id, $user){
            $horario       = Horario::find($id);
            $horario->desde   = Date::createFromFormat('d/m/Y', $request->input('desde'))->format('Y-m-d');
            $horario->hasta   = Date::createFromFormat('d/m/Y', $request->input('hasta'))->format('Y-m-d');
            $horario->observaciones = Libreria::obtenerParametro($request->input('observaciones'));
            $horario->horarios = Libreria::obtenerParametro($request->input('horarios'));
            $horario->person_id = $request->input('person_id');
            $horario->usuario_id = $user->person_id;
            //$horario->updated_at = date("Y-m-d H:i:s");
            $horario->save();
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
        $existe = Libreria::verificarExistencia($id, 'horario');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $horario = Horario::find($id);
            $horario->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'horario');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Horario::find($id);
        $entidad  = 'Horario';
        $formData = array('route' => array('horario.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
