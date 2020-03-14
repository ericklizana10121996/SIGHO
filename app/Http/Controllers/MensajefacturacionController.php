<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Mensajefacturacion;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Convenio;
use App\Historia;

class MensajefacturacionController extends Controller
{

    protected $folderview      = 'app.mensajefacturacion';
    protected $tituloAdmin     = 'Mensaje Facturacion';
    protected $tituloRegistrar = 'Registrar Mensaje';
    protected $tituloModificar = 'Modificar Mensaje';
    protected $tituloEliminar  = 'Eliminar Mensaje';
    protected $rutas           = array('create' => 'mensajefacturacion.create', 
            'edit'   => 'mensajefacturacion.edit', 
            'delete' => 'mensajefacturacion.eliminar',
            'search' => 'mensajefacturacion.buscar',
            'index'  => 'mensajefacturacion.index',
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
        $entidad          = 'Mensajefacturacion';
        $resultado        = Mensajefacturacion::join('historia','historia.id','=','mensajefacturacion.historia_id')
                            ->join('person as paciente','paciente.id','=','historia.person_id')
                            ->join('convenio as cv','cv.id','=','historia.convenio_id')
                            ->join('person as usuario','usuario.id','=','mensajefacturacion.usuario_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($request->input('paciente')).'%');
        $lista            = $resultado->orderBy('mensajefacturacion.fecha', 'DESC')->orderBy('hora','DESC')->select('mensajefacturacion.*','usuario.nombres as usuario','cv.nombre as convenio')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Hora', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Mensaje', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Convenio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '2');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'user'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad','user'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entidad          = 'Mensajefacturacion';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Mensajefacturacion';
        $mensaje = null;
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $planes = Convenio::whereNull("deleted_at")->get();
        $cboPlan = array("0"=>"Ninguna");
        foreach ($planes as $plan) {
            $cboPlan[$plan->id] = $plan->nombre;
        }
        //dd($cboPlan);
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $formData = array('mensajefacturacion.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('mensaje', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente','cboPlan'));
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
        $reglas     = array('mensaje' => 'required|max:1000');
        $mensajes = array(
            'mensaje.required'         => 'Debe ingresar un mensaje'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request,$user){
            $Mensaje       = new Mensajefacturacion();
            $Mensaje->historia_id = $request->input('historia_id');
            $Mensaje->mensaje = strtoupper($request->input('mensaje'));
            $Mensaje->fecha = date("Y-m-d");
            $Mensaje->hora = date('H:i:s');
            $Mensaje->usuario_id = $user->person_id;
            $Mensaje->save();
            $convenio_id = $request->input('convenio_id');
            if($convenio_id>0){
                $Historia = Historia::find($request->input('historia_id'));
                $Historia->convenio_id = $convenio_id;
                $Historia->save();
            }
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
        $existe = Libreria::verificarExistencia($id, 'Mensajefacturacion');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $mensaje = Mensajefacturacion::find($id);
        $entidad  = 'Mensajefacturacion';
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $planes = Convenio::whereNull("deleted_at")->get();
        $cboPlan = array("0"=>"Ninguna");
        foreach ($planes as $plan) {
            $cboPlan[$plan->id] = $plan->nombre;
        }
        $formData = array('mensajefacturacion.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('mensaje', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente','cboPlan'));
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
        $existe = Libreria::verificarExistencia($id, 'Mensajefacturacion');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('mensaje' => 'required|max:1000');
        $mensajes = array(
            'mensaje.required'         => 'Debe ingresar un mensaje'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $Mensaje       = Mensajefacturacion::find($id);
            $Mensaje->historia_id = $request->input('historia_id');
            $Mensaje->mensaje = strtoupper($request->input('mensaje'));
            $Mensaje->save();
            $convenio_id = $request->input('convenio_id');
            if($convenio_id>0){
                $Historia = Historia::find($request->input('historia_id'));
                $Historia->convenio_id = $convenio_id;
                $Historia->save();
            }
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
        $existe = Libreria::verificarExistencia($id, 'mensajefacturacion');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Mensaje = Mensajefacturacion::find($id);
            $Mensaje->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'mensajefacturacion');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Mensajefacturacion::find($id);
        $entidad  = 'Mensajefacturacion';
        $formData = array('route' => array('mensajefacturacion.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
