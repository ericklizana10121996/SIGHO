<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Convenio;
use App\Detalleconvenio;
use App\Tiposervicio;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ConvenioController extends Controller
{
    protected $folderview      = 'app.convenio';
    protected $tituloAdmin     = 'Convenio';
    protected $tituloRegistrar = 'Registrar convenio';
    protected $tituloModificar = 'Modificar convenio';
    protected $tituloEliminar  = 'Eliminar convenio';
    protected $rutas           = array('create' => 'convenio.create', 
            'edit'   => 'convenio.edit', 
            'delete' => 'convenio.eliminar',
            'search' => 'convenio.buscar',
            'index'  => 'convenio.index',
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
        $entidad          = 'Convenio';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Convenio::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('nombre', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Plan', 'numero' => '1');
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
        $entidad          = 'Convenio';
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
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Convenio';
        $convenio = null;
        $formData            = array('convenio.store');
        $cboTipoServicio = array();
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $detalle = null;
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('convenio', 'formData', 'entidad', 'boton', 'listar', 'cboTipoServicio', 'detalle'));
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
                'nombre'                  => 'required|max:100',
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $convenio       = new Convenio();
            $convenio->nombre = strtoupper($request->input('nombre'));
            $convenio->plan_id = $request->input('plan_id');
            $convenio->save();
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
        $existe = Libreria::verificarExistencia($id, 'convenio');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $convenio = Convenio::find($id);
        $cboTipoServicio = array();
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $entidad             = 'Convenio';
        $formData            = array('convenio.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('convenio', 'formData', 'entidad', 'boton', 'listar', 'cboTipoServicio'));
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
        $existe = Libreria::verificarExistencia($id, 'convenio');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'nombre'                  => 'required|max:100',
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request, $id){
            $convenio         = Convenio::find($id);
            $convenio->nombre = strtoupper($request->input('nombre'));
            $convenio->plan_id = $request->input('plan_id');
            $convenio->save();
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
        $existe = Libreria::verificarExistencia($id, 'convenio');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $convenio = Convenio::find($id);
            $convenio->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'convenio');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Convenio::find($id);
        $entidad  = 'Convenio';
        $formData = array('route' => array('convenio.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function convenioautocompletar($searching)
    {
        $resultado        = Convenio::where('nombre', 'LIKE', '%'.strtoupper($searching).'%')->orderBy('nombre', 'ASC')
                            ->select('convenio.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $dato = Horario::where('person_id','=',$value->id)->where('desde','<=',date("Y-m-d"))->where('hasta','>=',date("Y-m-d"))->first();
            if(count($dato)>0){
                $fecha='Desde '.$dato->desde.' al '.$dato->hasta;
                $observacion=$dato->observaciones;
                $horario=str_replace("\\r\\n","<br />",json_encode($dato->horarios));
            }else{
                $fecha="";
                $observacion="";
                $horario="";
            }
            if($value->apellidomaterno==""){
                $data[] = array(
                            'label' => trim($value->apellidopaterno." ".$value->nombres),
                            'id'    => $value->id,
                            'value' => trim($value->apellidopaterno." ".$value->nombres),
                            'especialidad' => $value->especialidad,
                            'fecha' => $fecha,
                            'observacion' => $observacion,
                            'horario' => $horario,
                        );
            }else{
                $data[] = array(
                            'label' => trim($value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres),
                            'id'    => $value->id,
                            'value' => trim($value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres),
                            'especialidad' => $value->especialidad,
                            'fecha' => $fecha,
                            'observacion' => $observacion,
                            'horario' => $horario,
                        );
            }
        }
        return json_encode($data);
    }
}
