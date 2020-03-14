<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Tarifario;
use App\Servicio;
use App\Tiposervicio;
use App\Plan;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TarifarioController extends Controller
{

    protected $folderview      = 'app.tarifario';
    protected $tituloAdmin     = 'Tarifario';
    protected $tituloRegistrar = 'Registrar Tarifario';
    protected $tituloModificar = 'Modificar Tarifario';
    protected $tituloEliminar  = 'Eliminar Tarifario';
    protected $rutas           = array('create' => 'tarifario.create', 
            'edit'   => 'tarifario.edit', 
            'delete' => 'tarifario.eliminar',
            'search' => 'tarifario.buscar',
            'index'  => 'tarifario.index',
            'generar' => 'tarifario.generar',
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
        $entidad          = 'tarifario';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $codigo             = Libreria::getParam($request->input('codigo'));
        $resultado        = Tarifario::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->where('codigo', 'LIKE', '%'.strtoupper($codigo).'%')->orderBy('codigo', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Codigo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Unidad', 'numero' => '1');
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
        $entidad          = 'tarifario';
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
        $entidad  = 'tarifario';
        $tarifario = null;
        $formData = array('tarifario.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('tarifario', 'formData', 'entidad', 'boton', 'listar'));
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
        $reglas     = array('nombre' => 'required|max:100');
        $mensajes = array(
            'codigo.required'         => 'Debe ingresar un codigo',
            'nombre.required'         => 'Debe ingresar un nombre',
            'unidad.required'         => 'Debe ingresar una unidad',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $Tarifario       = new Tarifario();
            $Tarifario->codigo = strtoupper($request->input('codigo'));
            $Tarifario->nombre = strtoupper($request->input('nombre'));
            $Tarifario->unidad = strtoupper($request->input('unidad'));
            $Tarifario->save();
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
        $existe = Libreria::verificarExistencia($id, 'Tarifario');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $tarifario = Tarifario::find($id);
        $entidad  = 'tarifario';
        $formData = array('tarifario.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('tarifario', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'tarifario');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('nombre' => 'required|max:100');
        $mensajes = array(
            'codigo.required'         => 'Debe ingresar un codigo',
            'nombre.required'         => 'Debe ingresar un nombre',
            'unidad.required'         => 'Debe ingresar una unidad',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $Tarifario       = Tarifario::find($id);
            $Tarifario->codigo = strtoupper($request->input('codigo'));
            $Tarifario->nombre = strtoupper($request->input('nombre'));
            $Tarifario->unidad = strtoupper($request->input('unidad'));
            $Tarifario->save();
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
        $existe = Libreria::verificarExistencia($id, 'tarifario');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Tarifario = Tarifario::find($id);
            $Tarifario->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'tarifario');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Tarifario::find($id);
        $entidad  = 'tarifario';
        $formData = array('route' => array('tarifario.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function tarifarioautocompletar($searching)
    {
        $entidad    = 'tarifario';        
        $resultado = Tarifario::where(DB::raw('concat(codigo,\' \',nombre)'), 'LIKE', '%'.strtoupper($searching).'%');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array(
                            'label' => $value->codigo." ".$value->nombre,
                            'id'    => $value->id,
                            'value' => $value->codigo." ".$value->nombre,
                            'unidad'    => $value->unidad,
                        );
        }
        return json_encode($data);
    }


    public function guardar(Request $request)
    {
        $error = DB::transaction(function() use($request){
            $Tarifario = Tarifario::find($request->input('id'));
            $Tarifario->tiposervicio_id = $request->input('tiposervicio');
            $Tarifario->save();

            $plan = Plan::where('tipo','=','Aseguradora')->get();
            foreach ($plan as $key => $value) {
                $servicio2 = Servicio::where('plan_id','=',$value->id)
                                ->where('tarifario_id','=',$Tarifario->id)->first();
                if(count($servicio2)==0){
                    if(($request->input('txtPrecio'.$value->id)+0) > 0){
                        $servicio  = new Servicio();
                        $servicio->nombre = $Tarifario->codigo.' '.$Tarifario->nombre;
                        $servicio->tiposervicio_id = $request->input('tiposervicio');
                        $servicio->tipopago = 'Convenio';
                        $servicio->precio = $request->input('txtPrecio'.$value->id);
                        $servicio->modo = 'Porcentaje';
                        $servicio->pagohospital = 100;
                        $servicio->pagodoctor = 0;
                        $servicio->plan_id = $value->id;
                        $servicio->tarifario_id = $Tarifario->id;
                        $servicio->factor = $request->input('txtFactor'.$value->id);
                        $servicio->save();
                    }
                }else{
                    if(($request->input('txtPrecio'.$value->id)+0) > 0){
                        $servicio2->nombre = $Tarifario->codigo.' '.$Tarifario->nombre;
                        $servicio2->tiposervicio_id = $request->input('tiposervicio');
                        $servicio2->tipopago = 'Convenio';
                        $servicio2->precio = $request->input('txtPrecio'.$value->id);
                        $servicio2->modo = 'Porcentaje';
                        $servicio2->pagohospital = 100;
                        $servicio2->pagodoctor = 0;
                        $servicio2->plan_id = $value->id;
                        $servicio2->tarifario_id = $Tarifario->id;
                        $servicio2->factor = $request->input('txtFactor'.$value->id);
                        $servicio2->save();   
                    }
                }
            }            
        });
        return is_null($error) ? "OK" : $error;
    }

    public function generar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'tarifario');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $tarifario   = Tarifario::find($id);
        $cboTipoServicio = array();
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $plan = Plan::where('tipo','=','Aseguradora')->get();
        if(count($plan)>0){
            $servicio2 = Servicio::where('plan_id','=',$plan[0]->id)->where('tarifario_id','=',$tarifario->id)->first();
            if(empty($servicio2)){
                $tiposervicio_id = 0;
            }else{
                $tiposervicio_id = $servicio2->tiposervicio_id;
                //dd($servicio2);
            }
        }else{
            $tiposervicio_id = 0;
        }
        $entidad  = 'tarifario';
        $formData = array('route' => array('tarifario.guardar', $id), 'method' => 'SAVE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Generar';
        return view($this->folderview.'.generar')->with(compact('tarifario', 'formData', 'entidad', 'boton', 'listar','plan','cboTipoServicio','tiposervicio_id'));
    }
    
}
