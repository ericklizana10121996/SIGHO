<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Especialidadfarmacia;
use App\Rolpersona;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class EspecialidadfarmaciaController extends Controller
{
    protected $folderview      = 'app.especialidadfarmacia';
    protected $tituloAdmin     = 'Especialidad';
    protected $tituloRegistrar = 'Registrar especialidad';
    protected $tituloModificar = 'Modificar especialidad';
    protected $tituloEliminar  = 'Eliminar especialidad';
    protected $rutas           = array('create' => 'especialidadfarmacia.create', 
            'edit'   => 'especialidadfarmacia.edit', 
            'delete' => 'especialidadfarmacia.eliminar',
            'search' => 'especialidadfarmacia.buscar',
            'index'  => 'especialidadfarmacia.index',
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function crearsimple(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Especialidadfarmacia';
        $especialidadfarmacia = null;
        $formData = array('especialidadfarmacia.guardarsimple');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mantSimple')->with(compact('especialidadfarmacia', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function guardarsimple(Request $request)
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
        $dat=array();
        $error = DB::transaction(function() use($request,&$dat){
            $especialidadfarmacia       = new Especialidadfarmacia();
            $especialidadfarmacia->nombre = strtoupper($request->input('nombre'));
            $especialidadfarmacia->save();
            $dat[0]=array("respuesta"=>"OK","id"=>$especialidadfarmacia->id,"nombre"=>$especialidadfarmacia->nombre); 
        });
        return is_null($error) ? json_encode($dat) : $error;
    }

    public function autocompletarespecialidadfarmacia($searching)
    {
        $entidad   = 'Especialidadfarmacia';
        $mdlEspecialidadfarmacia = new Especialidadfarmacia();
        $resultado = Especialidadfarmacia::where('nombre', 'LIKE', '%'.strtoupper($searching).'%')->orderBy('nombre', 'ASC');
        $lista     = $resultado->get();
        $data      = array();
        foreach ($lista as $key => $value) {
            $data[] = array(
                            'label' => $value->nombre,
                            'id'    => $value->id,
                            'value' => $value->nombre,
                        );
            
        }
        return json_encode($data);
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
        $entidad          = 'Especialidadfarmacia';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Especialidadfarmacia::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('nombre', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
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
        $entidad          = 'Especialidadfarmacia';
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
        $entidad  = 'Especialidadfarmacia';
        $especialidadfarmacia = null;
        $formData = array('especialidadfarmacia.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('especialidadfarmacia', 'formData', 'entidad', 'boton', 'listar'));
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
            $especialidadfarmacia       = new Especialidadfarmacia();
            $especialidadfarmacia->nombre = strtoupper($request->input('nombre'));
            $especialidadfarmacia->save();
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
        $existe = Libreria::verificarExistencia($id, 'especialidadfarmacia');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $especialidadfarmacia = Especialidadfarmacia::find($id);
        $entidad  = 'Especialidadfarmacia';
        $formData = array('especialidadfarmacia.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('especialidadfarmacia', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'especialidadfarmacia');
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
            $especialidadfarmacia       = Especialidadfarmacia::find($id);
            $especialidadfarmacia->nombre = strtoupper($request->input('nombre'));
            $especialidadfarmacia->save();
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
        $existe = Libreria::verificarExistencia($id, 'especialidadfarmacia');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $especialidadfarmacia = Especialidadfarmacia::find($id);
            $especialidadfarmacia->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'especialidadfarmacia');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Especialidadfarmacia::find($id);
        $entidad  = 'Especialidadfarmacia';
        $mensaje = '¿Desea eliminar la especialidad "'.$modelo->nombre.'" ? <br><br>';
        $formData = array('route' => array('especialidadfarmacia.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar','mensaje'));
    }
}
