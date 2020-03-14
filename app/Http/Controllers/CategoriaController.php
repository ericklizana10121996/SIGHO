<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Categoria;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{
    protected $folderview      = 'app.categoria';
    protected $tituloAdmin     = 'Clasificacion Producto';
    protected $tituloRegistrar = 'Registrar Clasificacion';
    protected $tituloModificar = 'Modificar Clasificacion';
    protected $tituloEliminar  = 'Eliminar Clasificacion';
    protected $rutas           = array('create' => 'categoria.create', 
            'edit'   => 'categoria.edit', 
            'delete' => 'categoria.eliminar',
            'search' => 'categoria.buscar',
            'index'  => 'categoria.index',
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
        $entidad          = 'Categoria';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Categoria::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('nombre', 'ASC');
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
        $entidad          = 'Categoria';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    public function crearsimple(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Categoria';
        $categoria = null;
        $formData = array('categoria.guardarsimple');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mantSimple')->with(compact('categoria', 'formData', 'entidad', 'boton', 'listar'));
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
            $categoria       = new Categoria();
            $categoria->nombre = strtoupper($request->input('nombre'));
            $categoria->save();
            $dat[0]=array("respuesta"=>"OK","id"=>$categoria->id,"nombre"=>$categoria->nombre); 
        });
        return is_null($error) ? json_encode($dat) : $error;
    }

    public function autocompletarcategoria($searching)
    {
        $entidad   = 'Categoria';
        $mdlCategoria = new Categoria();
        $resultado = Categoria::where('nombre', 'LIKE', '%'.strtoupper($searching).'%')->orderBy('nombre', 'ASC');
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Categoria';
        $cboCategoria        = [''=>'Seleccione una categoría'] + Categoria::lists('nombre', 'id')->all();
        $categoria = null;
        $formData            = array('categoria.store');
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('categoria', 'formData', 'entidad', 'boton', 'cboCategoria', 'listar'));
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
                'nombre'                  => 'required|max:100'
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $categoria       = new Categoria();
            $categoria->nombre = strtoupper($request->input('nombre'));
            //$categoria->nivel                 = $request->input('nivel');
            //$categoria->categoria_id = Libreria::obtenerParametro($request->input('categoria_id'));
            $categoria->save();
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
        $existe = Libreria::verificarExistencia($id, 'categoria');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $categoria = Categoria::find($id);
        $entidad             = 'Categoria';
        $cboCategoria        = [''=>'Seleccione una categoría'] + Categoria::where('id', '<>', $id)->lists('nombre', 'id')->all();
        $formData            = array('categoria.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('categoria', 'formData', 'entidad', 'boton', 'cboCategoria', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'categoria');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'nombre'                  => 'required|max:100'
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request, $id){
            $categoria                        = Categoria::find($id);
            $categoria->nombre = strtoupper($request->input('nombre'));
            //$categoria->nivel                 = $request->input('nivel');
            //$categoria->categoria_id = Libreria::obtenerParametro($request->input('categoria_id'));
            $categoria->save();
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
        $existe = Libreria::verificarExistencia($id, 'categoria');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $categoria = Categoria::find($id);
            $categoria->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'categoria');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Categoria::find($id);
        $entidad  = 'Categoria';
        $formData = array('route' => array('categoria.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
