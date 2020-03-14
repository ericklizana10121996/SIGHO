<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Guardia;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;

class GuardiaController extends Controller
{
    protected $folderview      = 'app.guardia';
    protected $tituloAdmin     = 'Guardias';
    protected $tituloRegistrar = 'Registrar guardia';
    protected $tituloModificar = 'Modificar guardia';
    protected $tituloEliminar  = 'Eliminar guardia';
    protected $rutas           = array('create' => 'guardia.create', 
            'edit'   => 'guardia.edit', 
            'delete' => 'guardia.eliminar',
            'search' => 'guardia.buscar',
            'index'  => 'guardia.index',
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
        $entidad          = 'Guardia';
        $person_id             = Libreria::getParam($request->input('person_id'));
        $resultado        = Guardia::where('person_id','=',$person_id)->orderBy('fecha', 'DESC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
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
        $entidad          = 'Guardia';
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
        $entidad  = 'Guardia';
        $guardia = null;
        $person_id             = Libreria::getParam($request->input('person_id'));
        $formData = array('guardia.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('guardia', 'formData', 'entidad', 'boton', 'listar','person_id'));
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
            'fecha.required'   => 'Ingrese fecha',
            'person_id.required' => 'Debe ingresar un trabajador'
            );
        $mensajes = array(
            'fecha'   => 'required|date_format:d/m/Y',
            'person_id' => 'required|integer|exists:person,id,deleted_at,NULL'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $guardia       = new Guardia();
            $guardia->fecha   = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
            $guardia->person_id = $request->input('person_id');
            $guardia->save();
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
        $existe = Libreria::verificarExistencia($id, 'guardia');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $guardia = Guardia::find($id);
        $person_id             = Libreria::getParam($request->input('person_id'));
        $entidad  = 'Guardia';
        $formData = array('guardia.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('guardia', 'formData', 'entidad', 'boton', 'listar','person_id'));
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
        $existe = Libreria::verificarExistencia($id, 'guardia');
        if ($existe !== true) {
            return $existe;
        }
        $reglas = array(
            'fecha.required'   => 'Ingrese fecha',
            'person_id.required' => 'Debe ingresar un trabajador'
            );
        $mensajes = array(
            'fecha'   => 'required|date_format:d/m/Y',
            'person_id' => 'required|integer|exists:person,id,deleted_at,NULL'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request, $id){
            $guardia       = Guardia::find($id);
            $guardia->fecha   = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
            $guardia->person_id = $request->input('person_id');
            $guardia->save();
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
        $existe = Libreria::verificarExistencia($id, 'guardia');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $guardia = Guardia::find($id);
            $guardia->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'guardia');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Guardia::find($id);
        $entidad  = 'Guardia';
        $formData = array('route' => array('guardia.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
