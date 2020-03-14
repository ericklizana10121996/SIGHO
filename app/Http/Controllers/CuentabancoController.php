<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Banco;
use App\Cuentabanco;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CuentabancoController extends Controller
{

    protected $folderview      = 'app.Cuentabanco';
    protected $tituloAdmin     = 'Cuenta Banco';
    protected $tituloRegistrar = 'Registrar Cuenta Banco';
    protected $tituloModificar = 'Modificar Cuenta Banco';
    protected $tituloEliminar  = 'Eliminar Cuenta Banco';
    protected $rutas           = array('create' => 'cuentabanco.create', 
            'edit'   => 'cuentabanco.edit', 
            'delete' => 'cuentabanco.eliminar',
            'search' => 'cuentabanco.buscar',
            'index'  => 'cuentabanco.index',
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
        $entidad          = 'Banco';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Cuentabanco::where('descripcion', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('descripcion', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro. Cta.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Banco', 'numero' => '1');
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
        $entidad          = 'Cuentabanco';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Cuentabanco';
        $cuentabanco = null;
        $formData = array('cuentabanco.store');
        $cboBanco = array();
        $rs        = Banco::orderBy('descripcion','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboBanco = $cboBanco + array($value->id => $value->descripcion);
        }
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('cuentabanco', 'formData', 'entidad', 'boton', 'listar', 'cboBanco'));
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
        $reglas     = array('nombre' => 'required|max:50');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $cuentabanco       = new Cuentabanco();
            $cuentabanco->descripcion = strtoupper($request->input('nombre'));
            $cuentabanco->banco_id = $request->input('banco_id');
            $cuentabanco->save();
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
    public function edit(Request $request,$id)
    {
        $existe = Libreria::verificarExistencia($id, 'cuentabanco');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $cuentabanco = Cuentabanco::find($id);
        $cboBanco = array();
        $rs        = Banco::orderBy('descripcion','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboBanco = $cboBanco + array($value->id => $value->descripcion);
        }
        $entidad  = 'Cuentabanco';
        $formData = array('cuentabanco.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('cuentabanco', 'formData', 'entidad', 'boton', 'listar', 'cboBanco'));
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
        $existe = Libreria::verificarExistencia($id, 'cuentabanco');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('nombre' => 'required|max:50');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $cuentabanco       = Cuentabanco::find($id);
            $cuentabanco->descripcion = strtoupper($request->input('nombre'));
            $cuentabanco->banco_id = $request->input('banco_id');
            $cuentabanco->save();
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
        $existe = Libreria::verificarExistencia($id, 'cuentabanco');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $cuentabanco = Cuentabanco::find($id);
            $cuentabanco->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'cuentabanco');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Cuentabanco::find($id);
        $entidad  = 'Cuentabanco';
        $mensaje = '¿Desea eliminar la cuenta del banco "'.$modelo->descripcion.'" ? <br><br>';
        $formData = array('route' => array('cuentabanco.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar','mensaje'));
    }
}
