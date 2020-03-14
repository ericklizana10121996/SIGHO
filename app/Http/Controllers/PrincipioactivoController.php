<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Principioactivo;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PrincipioactivoController extends Controller
{
    protected $folderview      = 'app.principioactivo';
    protected $tituloAdmin     = 'Principio Activo';
    protected $tituloRegistrar = 'Registrar Principio Activo';
    protected $tituloModificar = 'Modificar Principio Activo';
    protected $tituloEliminar  = 'Eliminar Principio Activo';
    protected $rutas           = array('create' => 'principioactivo.create', 
            'edit'   => 'principioactivo.edit', 
            'delete' => 'principioactivo.eliminar',
            'search' => 'principioactivo.buscar',
            'searchsimple' => 'principioactivo.buscarsimple',
            'index'  => 'principioactivo.index',
            'indexsimple'  => 'principioactivo.indexsimple',
            'createsimple' => 'principioactivo.crearsimple',
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexsimple()
    {
        $entidad          = 'Principioactivo';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.adminSimple')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    /**
     * Mostrar el resultado de búsquedas
     * 
     * @return Response 
     */
    public function buscarsimple(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Principioactivo';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Principioactivo::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('nombre', 'ASC');
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
            return view($this->folderview.'.listSimple')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta'));
        }
        return view($this->folderview.'.listSimple')->with(compact('lista', 'entidad'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function agregarprincipio(Request $request)
    {
        $cadena = '';
        $lista = array();
        if ($request->session()->get('carritoprincipio') !== null) {
            $lista          = $request->session()->get('carritoprincipio');
            $principioactivo_id       = Libreria::getParam($request->input('principioactivo_id'));
            $principioactivo   = Principioactivo::find($principioactivo_id);
            $estaPresente   = false;
            $indicepresente = '';
            for ($i=0; $i < count($lista); $i++) { 
                if ($lista[$i]['principioactivo_id'] == $principioactivo_id) {
                    $estaPresente   = true;
                    $indicepresente = $i;
                }
            }

            if ($estaPresente !== true) {
                $lista[]  = array('principioactivo_id' => $principioactivo_id, 'nombre' => $principioactivo->nombre);
            
                for ($i=0; $i < count($lista); $i++) {
                    if ($i == 0) {
                        $cadena = $cadena.$lista[$i]['nombre'];
                    }else{
                        $cadena = $cadena.'+'.$lista[$i]['nombre'];
                    }
                }
                $request->session()->put('carritoprincipio', $lista);
            }
         }else{
            $principioactivo_id       = Libreria::getParam($request->input('principioactivo_id'));
            $principioactivo   = Principioactivo::find($principioactivo_id);
            $cadena = $cadena.$principioactivo->nombre;
            $lista[]  = array('principioactivo_id' => $principioactivo_id, 'nombre' => $principioactivo->nombre);
            $request->session()->put('carritoprincipio', $lista);

         }
         return $cadena;
    }

    public function quitarprincipio(Request $request)
    {
        $id       = $request->input('valor');
        $cantidad = count($request->session()->get('carritoprincipio'));
        $lista2   = $request->session()->get('carritoprincipio');
        $lista    = array();
        $principioactivo_id = '';
        for ($i=0; $i < $cantidad; $i++) {
            if ($lista2[$i]['principioactivo_id'] != $id) {
                $lista[] = $lista2[$i];
            }else{
                $principioactivo_id = $lista2[$i]['principioactivo_id'];
            }
        }
        $cadena = '';
        for ($i=0; $i < count($lista); $i++) {
            if ($i == 0) {
                $cadena = $cadena.$lista[$i]['nombre'];
            }else{
                $cadena = $cadena.'+'.$lista[$i]['nombre'];
            }
        }
        $request->session()->put('carritoprincipio', $lista);

        return $cadena;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function crearsimple(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Principioactivo';
        $principioactivo = null;
        $formData = array('principioactivo.guardarsimple');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mantSimple')->with(compact('principioactivo', 'formData', 'entidad', 'boton', 'listar'));
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
        $error = DB::transaction(function() use($request){
            $principioactivo       = new Principioactivo();
            $principioactivo->nombre = strtoupper($request->input('nombre'));
            $principioactivo->save();
        });
        return is_null($error) ? "OK" : $error;
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
        $entidad          = 'Principioactivo';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Principioactivo::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('nombre', 'ASC');
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
        $entidad          = 'Principioactivo';
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
        $entidad  = 'Principioactivo';
        $principioactivo = null;
        $formData = array('principioactivo.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('principioactivo', 'formData', 'entidad', 'boton', 'listar'));
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
            $principioactivo       = new Principioactivo();
            $principioactivo->nombre = strtoupper($request->input('nombre'));
            $principioactivo->save();
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
        $existe = Libreria::verificarExistencia($id, 'principioactivo');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $principioactivo = Principioactivo::find($id);
        $entidad  = 'Principioactivo';
        $formData = array('principioactivo.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('principioactivo', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'principioactivo');
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
            $principioactivo       = Principioactivo::find($id);
            $principioactivo->nombre = strtoupper($request->input('nombre'));
            $principioactivo->save();
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
        $existe = Libreria::verificarExistencia($id, 'principioactivo');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $principioactivo = Principioactivo::find($id);
            $principioactivo->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'principioactivo');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Principioactivo::find($id);
        $entidad  = 'Principioactivo';
        $mensaje = '¿Desea eliminar el principio activo "'.$modelo->nombre.'" ? <br><br>';
        $formData = array('route' => array('principioactivo.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar','mensaje'));
    }
}
