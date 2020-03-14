<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Convenio;
use App\Caja;
use App\Especialidad;
use App\Person;
use App\Producto;
use App\Plan;
use App\Sala;
use App\Movimiento;
use App\Tipodocumento;
use App\Conceptopago;
use App\Detallemovcaja;
use App\Especialidadfarmacia;
use App\Presentacion;
use App\Principioactivo;
use App\Tiposervicio;
use App\Servicio;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Excel;

class repsController extends Controller
{

    protected $folderview      = 'app.rpts';
    protected $tituloAdmin     = 'Area';
    protected $tituloRegistrar = 'Registrar area';
    protected $tituloModificar = 'Modificar area';
    protected $tituloEliminar  = 'Eliminar area';
    protected $rutas           = array('create' => 'area.create', 
            'create2' => 'retramite.create',
            'edit'   => 'area.edit', 
            'delete' => 'area.eliminar',
            'search' => 'area.buscar',
            'index'  => 'area.index',
            'medicos' => 'reps.medicos',
            'tiposervicio' => 'reps.tiposervicio',
            'servicio' => 'reps.servicio',
            'bservicio' => 'reps.bservicio',
            'cajas' => 'reps.cajas',
            'movimientosConta' => 'reps.movimientosConta',
            'salas' => 'reps.salas',
            'historia' => 'reps.historia',
            'nombres' => 'reps.nombres',
            'pnombres' => 'reps.pnombres',
            'convenios' => 'reps.convenios',
            'medicinas' => 'reps.medicinas',
            'nmedicinas' => 'reps.nmedicinas',
            'nproveedor' => 'reps.nproveedor',
            'nespecialidad' => 'reps.nespecialidad',
            'nprincipio' => 'reps.nprincipio',
            'compras' => 'reps.compras',
            'ventas' => 'reps.ventas',
            'gconv' => 'reps.gconv',
            'costos' => 'reps.costos',
            'Vendm' => 'reps.Vendm',
            'notas' => 'reps.notas',
            'honorarios' => 'reps.honorarios',
            'ProAlmacen' => 'reps.ProAlmacen',
            'ProQuimica' => 'reps.ProQuimica',
            'Pedido' => 'reps.Pedido',
            'Proveedores' => 'reps.Proveedores',
            'presentaciones' => 'reps.presentaciones',
            'Productos' => 'reps.Productos',
            'FacCon' => 'reps.FacCon',
            'Retramites' => 'reps.Retramites',
            'InventarioVal' => 'reps.InventarioVal',
            'movAlma' => 'reps.movAlma',
            'fallecidos' => 'reps.fallecidos',
            'anuladas' => 'reps.anuladas',
            'ConsultaPago' => 'reps.ConsultaPago',
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
    public function medicos(){
    	$response = '<div class="form-group">
				  <label for="Medico">Médico:</label>
				  <select class="form-control input-xs" id="Medico">
				  <option value="0">TODOS</option>';
    	$resultado        = Person::join('especialidad','especialidad.id','=','person.especialidad_id')
                            ->where('workertype_id','=','1')->orderBy('apellidopaterno', 'ASC')
                            ->select('person.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
        	$response = $response.'<option value="'.$value->id.'">'.(trim($value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres)).'</option>';
        }
        
		$response = $response.'</select></div>';
		return $response;
	}
    public function pnombres($apep,$apem){
        $response = '<div class="form-group">
                  <label for="Nombre">Nombres:</label>
                  <select class="form-control input-xs" id="Nombre">';
        $resultado        = Person::where('apellidopaterno','=',$apep)->where('apellidomaterno','=',$apem)->orderBy('apellidopaterno', 'ASC')
                            ->select('person.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<option value="'.$value->dni.'">'.(trim($value->nombres)).'</option>';
        }
        
        $response = $response.'</select></div>';
        return $response;
    }
    public function tiposervicio($indicio){
        $response = '';
        $resultado        = Especialidad::where('nombre','LIKE',$indicio.'%')->where('deleted_at','=',NULL)->orderBy('nombre', 'ASC')
                            ->select('especialidad.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<div class="resul" onclick="selecciona('.$value->id.')">'.$value->nombre.'</div>';
        }

        return $response;
    }
    public function servicio($indicio){
        $response = '';
        $resultado        = Tiposervicio::where('nombre','LIKE',$indicio.'%')->where('deleted_at','=',NULL)->orderBy('nombre', 'ASC')
                            ->select('tiposervicio.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<div class="resul" onclick="selecciona('.$value->id.')">'.$value->nombre.'</div>';
        }

        return $response;
    }
    public function bservicio($indicio){
        $response = '';
        $resultado        = Servicio::where('nombre','LIKE',$indicio.'%')->where('deleted_at','=',NULL)->orderBy('nombre', 'ASC')
                            ->select('servicio.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<div class="resul" onclick="selecciona('.$value->id.')">'.$value->nombre.'</div>';
        }

        return $response;
    }
    public function nombres($ap){
        $long = strlen($ap);
        $indiciomaterno = "";
        for ($i=0; $i < $long; $i++) { 
            if (substr($ap, $i, 1) == "2") {
                $indiciopaterno = substr($ap, 0, $i);
                $dis = $long - $i - 1;
                $indiciomaterno = substr($ap, $i+1, $dis);
            }
        }
        //$indiciopaterno = $ap;
        //$indiciomaterno = $am;
        //$indiciomaterno = '';
        if ($indiciomaterno == " ") {
            $indiciomaterno = "";
        }
        $response = '';
        $resultado        = Person::where('apellidopaterno','LIKE',$indiciopaterno.'%')->where('apellidomaterno','LIKE',$indiciomaterno.'%')->orderBy('apellidopaterno', 'ASC')
                            ->select('person.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<div class="resul" onclick="selecciona('.$value->id.')">'.(trim($value->apellidopaterno.' '.$value->apellidomaterno.' '.$value->nombres)).'</div>';
        }

        return $response;
    }
    public function salas(){
        $response = '<div class="form-group">
                  <label for="Sala">Sala:</label>
                  <select class="form-control input-xs" id="Sala">
                  <option value="0">TODAS</option>';
        $resultado        = Sala::orderBy('nombre','ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<option value="'.$value->id.'">'.(trim($value->nombre)).'</option>';
        }
        
        $response = $response.'</select></div>';
        return $response;
    }
    public function cajas(){
        $response = '<div class="form-group">
                  <label for="Medico">Caja:</label>
                  <select class="form-control input-xs" id="Medico">';
        $resultado        = Caja::orderBy('nombre','ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<option value="'.$value->id.'">'.(trim($value->nombre)).'</option>';
        }
        
        $response = $response.'</select></div>';
        return $response;
    }

    public function convenios(){
        $response = '<div class="form-group">
                  <label for="Medico">Convenios:</label>
                  <select class="form-control input-xs" id="Medico">
                  <option value="0">TODOS</option>';
        $resultado        = Plan::orderBy('nombre','ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<option value="'.$value->id.'">'.(trim($value->nombre)).'</option>';
        }
        
        $response = $response.'</select></div>';
        return $response;
    }
    public function medicinas(){
        $response = '<div class="form-group">
                  <label for="medicinas">Medicinas:</label>
                  <select class="form-control input-xs" id="medicinas">';
        $resultado        = Producto::orderBy('nombre','ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<option value="'.$value->id.'">'.(trim($value->nombre)).'</option>';
        }
        
        $response = $response.'</select></div>';
        return $response;
    }

    public function nmedicinas($indicio){
        $response = '';
        
        // $resultado        = Producto::where('nombre','like',''.strtoupper($indicio).'%')->orderBy('nombre','ASC');
        

        $list = Producto::where('nombre', 'LIKE', ''.strtoupper($indicio).'%')
                            ->orWhere(function($query) use($indicio){
                                $query->WhereIn('id',function($q) use($indicio){
                                    $q->select('producto_id')
                                      ->from('productoprincipio')
                                      ->leftjoin('principioactivo','principioactivo.id','=','Productoprincipio.principioactivo_id')
                                      ->where('principioactivo.nombre','like','%'.strtoupper($indicio).'%');
                                });
                            })
                            ->orderBy('origen_id','DESC')->orderBy('nombre', 'ASC')->get();
        // $list      = $resultado->get();


        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<div class="resul" onclick="selecciona('.$value->id.')">'.(trim($value->nombre)).'</div>';
        }
        
        return $response;
    }

    public function nproveedor($indicio){
        $response = '';
        $resultado        = Person::where('bussinesname','like',$indicio.'%')->orderBy('bussinesname','ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<div class="resul" onclick="selecciona('.$value->id.')">'.(trim($value->bussinesname)).'</div>';
        }
        
        return $response;
    }

    public function presentaciones(){
        $response = '<label for="presentacion">Presentación:</label>
                                <select class="form-control" id="presentacion">
                                <option value="0">TODOS</option>';
        $resultado        = Presentacion::orderBy('nombre','ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<option value="'.$value->id.'">'.(trim($value->nombre)).'</option>';
        }
        $response = $response.'</select>';
        
        return $response;
    }

    public function nespecialidad($indicio){
        $response = '';
        $resultado        = Especialidadfarmacia::where('nombre','like',$indicio.'%')->orderBy('nombre','ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<div class="resul" onclick="selecciona('.$value->id.')">'.(trim($value->nombre)).'</div>';
        }
        
        return $response;
    }

    public function nprincipio($indicio){
        $response = '';
        $resultado        = Principioactivo::where('nombre','like',$indicio.'%')->orderBy('nombre','ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $response = $response.'<div class="resul" onclick="selecciona('.$value->id.')">'.(trim($value->nombre)).'</div>';
        }
        
        return $response;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function Pedido(){
        $entidad          = 'reporte';
        $title            = 'Farmacia - Cobros Cuaderno';
        
        return view($this->folderview.'.cuaderno')->with(compact('entidad', 'title'));
    }

    public function honorarios(){
        $entidad          = 'reporte';
        $title            = 'Honorarios Diarios';
        
        return view($this->folderview.'.honorarios')->with(compact('entidad', 'title'));
    }

    public function historia(){
        $entidad          = 'reporte';
        $title            = 'Reporte Historias';
        
        return view($this->folderview.'.historia')->with(compact('entidad', 'title'));
    }

    public function Productos(){
        $entidad          = 'reporte';
        $title            = 'Farmacia - Productos Saldos';
        
        return view($this->folderview.'.productos')->with(compact('entidad', 'title'));
    }

    public function FacCon(){
        $entidad          = 'reporte';
        $title            = 'Facturas Emitidas por Convenio';
        
        return view($this->folderview.'.faccon')->with(compact('entidad', 'title'));
    }

    public function Retramites(){
        $entidad          = 'reporte';
        $title            = 'Retramites de Convenio';
        $titulo_registrar = 'Registrar Retramite por Devolución';
        $ruta = $this->rutas;
        return view($this->folderview.'.retramites')->with(compact('entidad', 'title','titulo_registrar','ruta'));
    }

    public function createRetramite(){

    }
    public function Proveedores(){
        $entidad          = 'reporte';
        $title            = 'Proveedores';
        
        return view($this->folderview.'.proveedores')->with(compact('entidad', 'title'));
    }

    public function movimientosConta(){
        $entidad          = 'reporte';
        $title            = 'Movimientos (INGRESOS - EGRESOS)';
        
        return view($this->folderview.'.movConta')->with(compact('entidad', 'title'));
    }

    public function Vendm(){
        $entidad          = 'reporte';
        $title            = 'Farmacia - Más/Menos Vendidos';
        
        return view($this->folderview.'.mmvend')->with(compact('entidad', 'title'));
    }

    public function notas(){
        $entidad          = 'reporte';
        $title            = 'Farmacia - Notas de Crédito - Ventas';
        
        return view($this->folderview.'.notas')->with(compact('entidad', 'title'));
    }

    public function InventarioVal(){
        $entidad          = 'inventarioval';
        $title            = 'Farmacia - Inventario Valorizado';
        
        return view($this->folderview.'.inventarioval')->with(compact('entidad', 'title'));
    }

    public function movAlma(){
        $entidad          = 'movAlma';
        $title            = 'Farmacia - Movimientos de Almacén';
        
        return view($this->folderview.'.movAlma')->with(compact('entidad', 'title'));
    }

    public function compras(){
        $entidad          = 'reporte';
        $title            = 'Farmacia - Compras';
        
        return view($this->folderview.'.compras')->with(compact('entidad', 'title'));
    }

    public function ventas(){
        $entidad          = 'reporte';
        $title            = 'Farmacia - Ventas';
        
        return view($this->folderview.'.ventas')->with(compact('entidad', 'title'));
    }

     public function gconv(){
        $entidad          = 'reporte';
        $title            = 'Farmacia - Guías de Convenio';
        
        return view($this->folderview.'.guiaconve')->with(compact('entidad', 'title'));
    }

    public function costos(){
        $entidad          = 'reporte';
        $title            = 'Farmacia - Costos';
        
        return view($this->folderview.'.costos')->with(compact('entidad', 'title'));
    }

    public function kardex(){
        $entidad          = 'reporte';
        $title            = 'Movimientos de Productos';
        
        return view($this->folderview.'.kardex')->with(compact('entidad', 'title'));
    }

    public function kardexCo(){
        $entidad          = 'reporte';
        $title            = 'Kardex';
        
        return view($this->folderview.'.kardexco')->with(compact('entidad', 'title'));
    }

    public function ProQuimica(){
        $entidad          = 'reporte';
        $title            = 'Existencias Actuales de Productos';
        
        return view($this->folderview.'.stockFarmacia')->with(compact('entidad', 'title'));
    }

    public function atenC(){
        $entidad          = 'reporte';
        $title            = 'Pacientes Atendidos por Convenio';
        
        return view($this->folderview.'.atenC')->with(compact('entidad', 'title'));
    }

    public function pagMC(){
        $entidad          = 'reporte';
        $title            = 'Pagos a Médicos de Pacientes por Convenio';
        
        return view($this->folderview.'.pagMC')->with(compact('entidad', 'title'));
    }

    public function pagME(){
        $entidad          = 'reporte';
        $title            = 'Pagos a Médicos de Pacientes Externos';
        
        return view($this->folderview.'.pagME')->with(compact('entidad', 'title'));
    }

    public function pagC(){
        $entidad          = 'reporte';
        $title            = 'Pagos Pacientes por Convenio';
        
        return view($this->folderview.'.pagC')->with(compact('entidad', 'title'));
    }

    public function pagE(){
        $entidad          = 'reporte';
        $title            = 'Pagos Pacientes Externos';
        
        return view($this->folderview.'.pagE')->with(compact('entidad', 'title'));
    }

    public function caja(){
        $entidad          = 'reporte';
        $title            = 'Caja Diaria';
        $user = Auth::user();
        return view($this->folderview.'.caja')->with(compact('entidad', 'title','user'));
    }

	public function hosp(){
		$entidad          = 'reporte';
        $title            = 'Hospitalizados General';
        
        return view($this->folderview.'.hosp')->with(compact('entidad', 'title'));
	}

	public function pacM(){
		$entidad          = 'reporte';
        $title            = 'HOSPITALIZACION';
        
        return view($this->folderview.'.pacM')->with(compact('entidad', 'title'));
	}

	public function pacP(){
		$entidad          = 'reporte';
        $title            = 'Ingresos y altas por Paciente';
        
        return view($this->folderview.'.pacP')->with(compact('entidad', 'title'));
	}

    public function fallecidos(){
        $entidad          = 'reporte';
        $title            = 'Pacientes Fallecidos';
        
        return view($this->folderview.'.fallecidos')->with(compact('entidad', 'title'));
    }

    public function anuladas(){
        $entidad          = 'reporte';
        $title            = 'Movimientos Anulados';
        
        return view($this->folderview.'.fallecidos')->with(compact('entidad', 'title'));
    }

    public function creditop(){
        $entidad          = 'reporte';
        $title            = 'Crédito del Personal';
        
        return view($this->folderview.'.creditop')->with(compact('entidad', 'title'));
    }

    public function ConsultaPago(){
        $entidad          = 'reporte';
        $title            = 'Consulta de Pagos';
        
        return view($this->folderview.'.consulpago')->with(compact('entidad', 'title'));
    }

	public function salaope(){
		$entidad          = 'reporte';
        $title            = 'Sala de Operaciones';
        
        return view($this->folderview.'.salaope')->with(compact('entidad', 'title'));
	}

    public function index()
    {
        $entidad          = 'reporte';
        $title            = 'Mensajes';
        
        return view($this->folderview.'.mensajes')->with(compact('entidad', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Area';
        $area = null;
        $formData = array('area.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('area', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function create2(Request $request){

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
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $area       = new Area();
            $area->nombre = strtoupper($request->input('nombre'));
            $area->save();
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
        $existe = Libreria::verificarExistencia($id, 'area');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $area = Area::find($id);
        $entidad  = 'Area';
        $formData = array('area.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('area', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'area');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('nombre' => 'required|max:100');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $area       = Area::find($id);
            $area->nombre = strtoupper($request->input('nombre'));
            $area->save();
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
        $existe = Libreria::verificarExistencia($id, 'area');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $area = Area::find($id);
            $area->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'area');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Area::find($id);
        $entidad  = 'Area';
        $formData = array('route' => array('area.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
