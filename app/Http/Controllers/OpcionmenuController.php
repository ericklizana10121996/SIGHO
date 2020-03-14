<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Menuoption;
use App\Menuoptioncategory;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OpcionmenuController extends Controller
{
    protected $folderview      = 'app.opcionmenu';
    protected $tituloAdmin     = 'Opción menú';
    protected $tituloRegistrar = 'Registrar opción de menú';
    protected $tituloModificar = 'Modificar opción de menú';
    protected $tituloEliminar  = 'Eliminar opción de menú';
    protected $rutas           = array('create' => 'opcionmenu.create', 
            'edit'   => 'opcionmenu.edit', 
            'delete' => 'opcionmenu.eliminar',
            'search' => 'opcionmenu.buscar',
            'index'  => 'opcionmenu.index',
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
        $pagina                = $request->input('page');
        $filas                 = $request->input('filas');
        $entidad               = 'Opcionmenu';
        $name                  = Libreria::getParam($request->input('name'));
        $menuoptioncategory_id = Libreria::getParam($request->input('menuoptioncategory_id'));
        $resultado             = Menuoption::listar($name, $menuoptioncategory_id);
        $lista                 = $resultado->get();
        $cabecera              = array();
        $cabecera[]            = array('valor' => '#', 'numero' => '1');
        $cabecera[]            = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]            = array('valor' => 'Orden', 'numero' => '1');
        $cabecera[]            = array('valor' => 'Categoria', 'numero' => '1');
        $cabecera[]            = array('valor' => 'Operaciones', 'numero' => '2');
        
        $titulo_modificar      = $this->tituloModificar;
        $titulo_eliminar       = $this->tituloEliminar;
        $ruta                  = $this->rutas;
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
        $entidad          = 'Opcionmenu';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboCategoria     = [''=>'Todos'] + Menuoptioncategory::lists('name', 'id')->all();
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboCategoria'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar       = Libreria::getParam($request->input('listar'), 'NO');
        $entidad      = 'Opcionmenu';
        $cboCategoria = [''=>'Seleccione una categoría'] + Menuoptioncategory::lists('name', 'id')->all();
        $opcionmenu   = null;
        $formData     = array('opcionmenu.store');
        $formData     = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton        = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('opcionmenu', 'formData', 'entidad', 'boton', 'cboCategoria', 'listar'));
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
        $validacion = Validator::make($request->all(),
            array(
                    'menuoptioncategory_id' => 'required|integer|exists:menuoptioncategory,id,deleted_at,NULL',
                    'name'                  => 'required|max:60',
                    'menuoption_id'         => 'integer|exists:menuoption,id',
                    'order'                 => 'required|integer',
                    'icon'                  => 'required',
                    'link'                  => 'required'
                )
            );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $opcionmenu                        = new Menuoption();
            $opcionmenu->name                  = $request->input('name');
            $opcionmenu->order                 = $request->input('order');
            $opcionmenu->icon                  = $request->input('icon');
            $opcionmenu->link                  = $request->input('link');
            $opcionmenu->menuoptioncategory_id = $request->input('menuoptioncategory_id'); 
            $opcionmenu->save();
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
        $existe = Libreria::verificarExistencia($id, 'menuoption');
        if ($existe !== true) {
            return $existe;
        }
        $listar       = Libreria::getParam($request->input('listar'), 'NO');
        $opcionmenu   = Menuoption::find($id);
        $entidad      = 'Opcionmenu';
        $cboCategoria = [''=>'Seleccione una categoría'] + Menuoptioncategory::lists('name', 'id')->all();
        $formData     = array('opcionmenu.update', $id);
        $formData     = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton        = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('opcionmenu', 'formData', 'entidad', 'boton', 'cboCategoria', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'menuoption');
        if ($existe !== true) {
            return $existe;
        }
        $validacion = Validator::make($request->all(),
            array(
                    'menuoptioncategory_id' => 'required|integer|exists:menuoptioncategory,id,deleted_at,NULL',
                    'name'                  => 'required|max:60',
                    'menuoption_id'         => 'integer|exists:opcionmenu,id',
                    'order'                 => 'required|integer',
                    'icon'                  => 'required',
                    'link'                  => 'required'
                )
            );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $opcionmenu                        = Menuoption::find($id);
            $opcionmenu->name                  = $request->input('name');
            $opcionmenu->order                 = $request->input('order');
            $opcionmenu->icon                  = $request->input('icon');
            $opcionmenu->link                  = $request->input('link');
            $opcionmenu->menuoptioncategory_id = $request->input('menuoptioncategory_id'); 
            $opcionmenu->save();
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
        $existe = Libreria::verificarExistencia($id, 'menuoption');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $opcionmenu = Menuoption::find($id);
            $opcionmenu->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Función para confirmar la eliminación de un registrlo
     * @param  integer $id          id del registro a intentar eliminar
     * @param  string $listarLuego consultar si luego de eliminar se listará
     * @return html              se retorna html, con la ventana de confirmar eliminar
     */
    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'menuoption');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Menuoption::find($id);
        $entidad  = 'Opcionmenu';
        $formData = array('route' => array('opcionmenu.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
