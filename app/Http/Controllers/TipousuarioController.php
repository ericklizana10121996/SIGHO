<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Usertype;
use App\Permission;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TipousuarioController extends Controller
{
    protected $folderview      = 'app.tipousuario';
    protected $tituloAdmin     = 'Tipo Usuario';
    protected $tituloRegistrar = 'Registrar tipo usuario';
    protected $tituloModificar = 'Modificar tipo usuario';
    protected $tituloEliminar  = 'Eliminar tipo usuario';
    protected $rutas           = array('create' => 'tipousuario.create', 
            'edit'     => 'tipousuario.edit', 
            'delete'   => 'tipousuario.eliminar',
            'search'   => 'tipousuario.buscar',
            'index'    => 'tipousuario.index',
            'permisos' => 'tipousuario.obtenerpermisos',
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
        $entidad          = 'Tipousuario';
        $name             = Libreria::getParam($request->input('name'));
        $resultado        = Usertype::listar($name);
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
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
        $entidad          = 'Tipousuario';
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
        $listar       = Libreria::getParam($request->input('listar'), 'NO');
        $entidad      = 'Tipousuario';
        $cboCategoria = [''=>'Seleccione una categoría'] + Usertype::lists('name', 'id')->all();
        $tipousuario  = null;
        $formData     = array('tipousuario.store');
        $formData     = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton        = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('tipousuario', 'formData', 'entidad', 'boton', 'cboCategoria', 'listar'));
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
                'name' => 'required|max:60'
                )
            );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $tipousuario       = new Usertype();
            $tipousuario->name = $request->input('name');
            $tipousuario->save();
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
        $existe = Libreria::verificarExistencia($id, 'usertype');
        if ($existe !== true) {
            return $existe;
        }
        $listar       = Libreria::getParam($request->input('listar'), 'NO');
        $tipousuario  = Usertype::find($id);
        $entidad      = 'Tipousuario';
        $cboCategoria = [''=>'Seleccione una categoría'] + Usertype::where('id', '<>', $id)->lists('name', 'id')->all();
        $formData     = array('tipousuario.update', $id);
        $formData     = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton        = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('tipousuario', 'formData', 'entidad', 'boton', 'cboCategoria', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'usertype');
        if ($existe !== true) {
            return $existe;
        }
        $validacion = Validator::make($request->all(),
            array(
                'name' => 'required|max:60'
                )
            );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $tipousuario       = Usertype::find($id);
            $tipousuario->name = $request->input('name');
            $tipousuario->save();

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
        $existe = Libreria::verificarExistencia($id, 'usertype');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $tipousuario = Usertype::find($id);
            $tipousuario->delete();
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
        $existe = Libreria::verificarExistencia($id, 'usertype');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Usertype::find($id);
        $entidad  = 'Tipousuario';
        $formData = array('route' => array('tipousuario.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * método para cargar view para asignación de permisos
     * @param  [type] $listarParam [description]
     * @param  [type] $id          [description]
     * @return [type]              [description]
     */
    public function obtenerpermisos($listarParam, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'usertype');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        $entidad = 'Tipousuario';
        if (isset($listarParam)) {
            $listar = $listarParam;
        }
        $tipousuario = Usertype::find($id);
        return view($this->folderview.'.permisos')->with(compact('tipousuario', 'listar', 'entidad'));
    }

    /**
     * método para guardar asignación de permisos
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function guardarpermisos(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'usertype');
        if ($existe !== true) {
            return $existe;
        }
        $listar        = Libreria::getParam($request->input('listar'), 'NO');
        $estados       = $request->input('estado');
        $idopcionmenus = $request->input('idopcionmenu');
        $cantAux       = count($estados);
        $respuesta     = true;
        $error         = DB::transaction(function() use ($id, $idopcionmenus, $estados, $cantAux)
        {
            Permission::where('usertype_id', '=', $id)->delete();
            for ($i=0; $i < $cantAux; $i++) {
                $exito = true;
                if($estados[$i] === 'H'){
                    $permiso = new Permission();
                    $permiso->usertype_id = $id;
                    $permiso->menuoption_id = $idopcionmenus[$i];
                    $permiso->save();
                }
            }
        });
        return is_null($error) ? "OK" : $error;
            
    }
}
