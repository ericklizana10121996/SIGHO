<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Librerias\Libreria;
use Validator;
use App\CondicionAlmacenamiento;
use Illuminate\Support\Facades\DB;

class CondicionAlmacenamientoController extends Controller
{
    protected $folderview      = 'app.condicionAlmacenamiento';
    protected $tituloAdmin     = 'Condición de Almacenamiento';
    protected $tituloRegistrar = 'Registrar Condición de Almacenamiento';
    protected $tituloModificar = 'Modificar Condición de Almacenamiento';
    protected $tituloEliminar  = 'Eliminar Condición de Almacenamiento';
    protected $rutas           = array('create' => 'condicionAlmacenamiento.create', 
            'edit'   => 'condicionAlmacenamiento.edit', 
            'delete' => 'condicionAlmacenamiento.eliminar',
            'search' => 'condicionAlmacenamiento.buscar',
            'index'  => 'condicionAlmacenamiento.index',
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

    public function index()
    {
        $entidad          = 'CondicionAlmacenamiento';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    public function crearsimple(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'CondicionAlmacenamiento';
        $condicionAlmacenamiento = null;
        $formData = array('condicionAlmacenamiento.guardarsimple');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mantSimple')->with(compact('condicionAlmacenamiento', 'formData', 'entidad', 'boton', 'listar'));
    }

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
            $condicionAlmacenamiento       = new CondicionAlmacenamiento();
            $condicionAlmacenamiento->nombre = strtoupper($request->input('nombre'));
            $condicionAlmacenamiento->save();
            $dat[0]=array("respuesta"=>"OK","id"=>$condicionAlmacenamiento->id,"nombre"=>$condicionAlmacenamiento->nombre); 
        });
        return is_null($error) ? json_encode($dat) : $error;
    }

    public function autocompletarcondicion($searching)
    {
        $entidad   = 'CondicionAlmacenamiento';
        $mdlPresentacion = new CondicionAlmacenamiento();
        $resultado = CondicionAlmacenamiento::where('nombre', 'LIKE', '%'.strtoupper($searching).'%')->orderBy('nombre', 'ASC');
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

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'CondicionAlmacenamiento';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = CondicionAlmacenamiento::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('nombre', 'ASC');
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'CondicionAlmacenamiento';
        $condicion = null;
        $formData = array('condicionAlmacenamiento.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('condicion', 'formData', 'entidad', 'boton', 'listar'));
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
        $reglas     = array('nombre' => 'required|max:250');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $forma       = new CondicionAlmacenamiento();
            $forma->nombre = strtoupper($request->input('nombre'));
            $forma->save();
        });
        return is_null($error) ? "OK" : $error;
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $existe = Libreria::verificarExistencia($id, 'condicionalmacenamiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $condicion = CondicionAlmacenamiento::find($id);
        $entidad  = 'CondicionAlmacenamiento';
        $formData = array('condicionAlmacenamiento.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('condicion', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'condicionalmacenamiento');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('nombre' => 'required|max:250');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $condicion         = CondicionAlmacenamiento::find($id);
            $condicion->nombre = strtoupper($request->input('nombre'));
            $condicion->save();
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
        $existe = Libreria::verificarExistencia($id, 'condicionalmacenamiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $forma = CondicionAlmacenamiento::find($id);
            // dd($forma);
            $forma->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'condicionalmacenamiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = CondicionAlmacenamiento::find($id);
        $entidad  = 'CondicionAlmacenamiento';
        $mensaje = '¿Desea eliminar la Condición de Almacenamiento "'.$modelo->nombre.'" ? <br><br>';
        $formData = array('route' => array('condicionAlmacenamiento.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar','mensaje'));
    }
}
