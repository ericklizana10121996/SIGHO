<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Person;
use App\Rolpersona;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{

    protected $folderview      = 'app.proveedor';
    protected $tituloAdmin     = 'Proveedores';
    protected $tituloRegistrar = 'Registrar proveedor';
    protected $tituloModificar = 'Modificar proveedor';
    protected $tituloEliminar  = 'Eliminar proveedor';
    protected $rutas           = array('create' => 'proveedor.create', 
            'edit'   => 'proveedor.edit', 
            'delete' => 'proveedor.eliminar',
            'search' => 'proveedor.buscar',
            'index'  => 'proveedor.index',
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
    public function index()
    {
        $entidad          = 'Proveedor';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
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
        $entidad          = 'Proveedor';
        $nombre             = Libreria::getParam($request->input('name'));
        $resultado        = Rolpersona::join('person','rolpersona.person_id','=','person.id')
                            ->where('rol_id','=','2')->where('bussinesname', 'LIKE', '%'.strtoupper($nombre).'%')->whereNull('person.deleted_at')->orderBy('bussinesname', 'ASC')->select('rolpersona.*');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Razon social', 'numero' => '1');
        $cabecera[]       = array('valor' => 'RUC', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Direccion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Telefono', 'numero' => '1');
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Proveedor';
        $proveedor = null;
        $formData = array('proveedor.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('proveedor', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function crearsimple(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Proveedor';
        $proveedor = null;
        $formData = array('proveedor.guardarsimple');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off', 'method' => 'POST');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mantSimple')->with(compact('proveedor', 'formData', 'entidad', 'boton', 'listar'));
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
        $reglas     = array(
                            'bussinesname' => 'required',
                            'ruc' => 'required',
                            'direccion' => 'required',
                            'telefono' => 'required',
                        );
        $mensajes = array(
            'bussinesname.required'         => 'Debe ingresar un razon social',
            'ruc.required'         => 'Debe ingresar un ruc',
            'direccion.required'         => 'Debe ingresar una direccion',
            'telefono.required'         => 'Debe ingresar un telefono'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $dat=array();
        $error = DB::transaction(function() use($request,&$dat){
            $proveedor       = new Person();
            $proveedor->bussinesname = strtoupper($request->input('bussinesname'));
            $proveedor->ruc = strtoupper($request->input('ruc'));
            $proveedor->direccion = strtoupper($request->input('direccion'));
            $proveedor->telefono = strtoupper($request->input('telefono'));
            $proveedor->save();

            $rolpersona = new Rolpersona();
            $rolpersona->rol_id = 2;
            $rolpersona->person_id = $proveedor->id;
            $rolpersona->save();
            $dat[0]=array("respuesta"=>"OK","id"=>$proveedor->id,"nombre"=>$proveedor->bussinesname); 
        });
        return is_null($error) ? json_encode($dat) : $error;
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
                            'bussinesname' => 'required',
                            'ruc' => 'required',
                            'direccion' => 'required',
                            'telefono' => 'required',
                        );
        $mensajes = array(
            'bussinesname.required'         => 'Debe ingresar un razon social',
            'ruc.required'         => 'Debe ingresar un ruc',
            'direccion.required'         => 'Debe ingresar una direccion',
            'telefono.required'         => 'Debe ingresar un telefono'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $proveedor       = new Person();
            $proveedor->bussinesname = strtoupper($request->input('bussinesname'));
            $proveedor->ruc = strtoupper($request->input('ruc'));
            $proveedor->direccion = strtoupper($request->input('direccion'));
            $proveedor->telefono = strtoupper($request->input('telefono'));
            $proveedor->save();

            $rolpersona = new Rolpersona();
            $rolpersona->rol_id = 2;
            $rolpersona->person_id = $proveedor->id;
            $rolpersona->save();
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
    public function edit(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'person');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $proveedor = Person::find($id);
        $entidad  = 'Proveedor';
        $formData = array('proveedor.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('proveedor', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'person');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                            'bussinesname' => 'required',
                            'ruc' => 'required',
                            'direccion' => 'required',
                            'telefono' => 'required',
                        );
        $mensajes = array(
            'bussinesname.required'         => 'Debe ingresar un razon social',
            'ruc.required'         => 'Debe ingresar un ruc',
            'direccion.required'         => 'Debe ingresar una direccion',
            'telefono.required'         => 'Debe ingresar un telefono'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $proveedor       = Person::find($id);
            $proveedor->bussinesname = strtoupper($request->input('bussinesname'));
            $proveedor->ruc = strtoupper($request->input('ruc'));
            $proveedor->direccion = strtoupper($request->input('direccion'));
            $proveedor->telefono = strtoupper($request->input('telefono'));
            $proveedor->save();
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
        $existe = Libreria::verificarExistencia($id, 'person');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $proveedor = Person::find($id);
            $proveedor->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'person');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Person::find($id);
        $entidad  = 'Proveedor';
        $formData = array('route' => array('proveedor.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
