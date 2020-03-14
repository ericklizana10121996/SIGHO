<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Tiposervicio;
use App\Servicio;
use App\Plan;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Excel;

class ServicioController extends Controller
{
    protected $folderview      = 'app.servicio';
    protected $tituloAdmin     = 'Servicio';
    protected $tituloRegistrar = 'Registrar servicio';
    protected $tituloModificar = 'Modificar servicio';
    protected $tituloEliminar  = 'Eliminar servicio';
    protected $rutas           = array('create' => 'servicio.create', 
            'edit'   => 'servicio.edit', 
            'delete' => 'servicio.eliminar',
            'search' => 'servicio.buscar',
            'index'  => 'servicio.index',
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
        $entidad          = 'Servicio';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Servicio::where('tipopago','LIKE','%'.$request->input('tipopago').'%');
        if($request->input('tiposervicio')!="0"){
            $resultado = $resultado->where('tiposervicio_id','=',$request->input('tiposervicio'));
        }
        if(trim($request->input('tipopago'))=='Convenio'){
            $resultado = $resultado->whereIn('tarifario_id', function($query) use ($request,$nombre)
                {
                $query->select('id')
                    ->from('tarifario')
                    ->whereRaw("concat(codigo,' ',nombre) like '%".strtoupper(trim($nombre))."%'");
                })->whereIn('plan_id', function($query) use ($request)
                {
                $query->select('id')
                    ->from('plan')
                    ->whereRaw("nombre like '%".strtoupper(trim($request->input('plan')))."%'");
                });  
        }else{
            $resultado = $resultado->where('nombre', 'LIKE', '%'.strtoupper($nombre).'%');
        }
        $lista            = $resultado->orderBy('nombre', 'ASC')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Plan', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Precio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Modo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago Medico', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago Hospital', 'numero' => '1');
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
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entidad          = 'Servicio';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoServicio = array();
        $cboTipoServicio = $cboTipoServicio + array(0 => '--Todos--');
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $user = Auth::user();
        $cboTipoPago     = array("Particular" => "Particular", "Convenio" => "Convenio", "ParticularDescuento" => "Particular con Descuento");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoServicio', 'cboTipoPago', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Servicio';
        $servicio = null;
        $cboTipoServicio = array();
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $cboPlan = array();
        $plan = Plan::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($plan as $key => $value) {
            $cboPlan = $cboPlan + array($value->id => $value->nombre);
        }
        $cboTipoPago     = array("Particular" => "Particular", "Convenio" => "Convenio", "ParticularDescuento" => "Particular con Descuento");
        $cboModo     = array("Porcentaje" => "Porcentaje", "Monto" => "Monto");
        $formData = array('servicio.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('servicio', 'formData', 'entidad', 'boton', 'listar', 'cboTipoServicio', 'cboTipoPago', 'cboModo', 'cboPlan'));
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
        $reglas     = array('precio' => 'required',
                            'pagodoctor' => 'required',
                            'pagohospital' => 'required');
        $mensajes = array(
            'precio.required'         => 'Debe ingresar el precio',
            'pagodoctor.required'         => 'Debe ingresar el pago al medico',
            'pagohospital.required'         => 'Debe ingresar el pago al hospital'
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $servicio       = new Servicio();
            $servicio->nombre = strtoupper($request->input('nombre'));
            $servicio->tiposervicio_id = $request->input('tiposervicio');
            $servicio->tipopago = $request->input('tipopago');
            $servicio->precio = str_replace(",","",$request->input('precio'));
            $servicio->modo = $request->input('modo');
            $servicio->pagohospital = str_replace(",","",$request->input('pagohospital'));
            $servicio->pagodoctor = str_replace(",","",$request->input('pagodoctor'));
            if($request->input('tipopago')=='Convenio'){
                $servicio->plan_id = $request->input('plan');
                $servicio->tarifario_id = $request->input('tarifario_id');
                $servicio->factor = $request->input('factor');
            }
            $servicio->save();
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
        $existe = Libreria::verificarExistencia($id, 'servicio');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $servicio = Servicio::find($id);
        $entidad  = 'Servicio';
        $cboTipoServicio = array();
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $cboPlan = array();
        $plan = Plan::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($plan as $key => $value) {
            $cboPlan = $cboPlan + array($value->id => $value->nombre);
        }
        $cboTipoPago     = array("Particular" => "Particular", "Convenio" => "Convenio", "ParticularDescuento" => "Particular con Descuento");
        $cboModo     = array("Porcentaje" => "Porcentaje", "Monto" => "Monto");
        $formData = array('servicio.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('servicio', 'formData', 'entidad', 'boton', 'listar', 'cboTipoServicio', 'cboTipoPago', 'cboModo', 'cboPlan'));
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
        $existe = Libreria::verificarExistencia($id, 'servicio');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('precio' => 'required',
                            'pagodoctor' => 'required',
                            'pagohospital' => 'required');
        $mensajes = array(
            'precio.required'         => 'Debe ingresar el precio',
            'pagodoctor.required'         => 'Debe ingresar el pago al medico',
            'pagohospital.required'         => 'Debe ingresar el pago al hospital'
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $servicio       = Servicio::find($id);
            $servicio->nombre = strtoupper($request->input('nombre'));
            $servicio->tiposervicio_id = $request->input('tiposervicio');
            $servicio->tipopago = $request->input('tipopago');
            $servicio->precio = str_replace(",","",$request->input('precio'));
            $servicio->modo = $request->input('modo');
            $servicio->pagohospital = str_replace(",","",$request->input('pagohospital'));
            $servicio->pagodoctor = str_replace(",","",$request->input('pagodoctor'));
            if($request->input('tipopago')=='Convenio'){
                $servicio->plan_id = $request->input('plan');
                $servicio->tarifario_id = $request->input('tarifario_id');
                $servicio->factor = $request->input('factor');
            }
            $servicio->save();
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
        $existe = Libreria::verificarExistencia($id, 'servicio');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $servicio = Servicio::find($id);
            $servicio->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'servicio');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Servicio::find($id);
        $entidad  = 'Servicio';
        $formData = array('route' => array('servicio.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Servicio::where('tipopago','LIKE','%'.$request->input('tipopago').'%');
        if($request->input('tiposervicio')!="0"){
            $resultado = $resultado->where('tiposervicio_id','=',$request->input('tiposervicio'));
        }
        if(trim($request->input('tipopago'))=='Convenio'){
            $resultado = $resultado->whereIn('tarifario_id', function($query) use ($request,$nombre)
                {
                $query->select('id')
                    ->from('tarifario')
                    ->whereRaw("concat(codigo,' ',nombre) like '%".strtoupper(trim($nombre))."%'");
                })->whereIn('plan_id', function($query) use ($request)
                {
                $query->select('id')
                    ->from('plan')
                    ->whereRaw("nombre like '%".strtoupper(trim($request->input('plan')))."%'");
                });  
        }else{
            $resultado = $resultado->where('nombre', 'LIKE', '%'.strtoupper($nombre).'%');
        }
        $lista            = $resultado->orderBy('nombre', 'ASC')->get();

        // dd($lista);

        Excel::create('ExcelTarifario', function($excel) use($lista,$request) {
 
            $excel->sheet('Tarifario', function($sheet) use($lista,$request) {
                $cabecera[] = "Tipo Pago";
                $cabecera[] = "Plan";
                $cabecera[] = "Nombre";
                $cabecera[] = "Tipo Servicio";
                $cabecera[] = "Precio";
                $cabecera[] = "Modo";
                $cabecera[] = "Pago Medico";
                $cabecera[] = "Pago Hospital";
                $sheet->row(1,$cabecera);
                $c=2;$d=1;$band=true;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = $value->tipopago;
                    // dd($value->plan_id);
                    $aux = "-";
                    if (!is_null($value->plan_id) && $value->plan_id>0) {
                        
                        // $aux = $value->plan->nombre;
                    }
                    $detalle[] = $aux;
                    $detalle[] = ($value->tipopago=='Convenio')?($value->tarifario->codigo." ".$value->tarifario->nombre):$value->nombre;
                    $detalle[] = $value->tiposervicio->nombre;
                    $detalle[] = $value->precio;
                    $detalle[] = $value->modo;
                    $detalle[] = $value->pagodoctor;
                    $detalle[] = $value->pagohospital;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
                
            });
        })->export('xls');
    }
}
