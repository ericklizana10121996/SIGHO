<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Aperturacierrecaja;
use App\Venta;
use App\Producto;
use App\Distribuidora;
use App\Tipodocumento;
use App\Detallemovimiento;
use App\Kardex;
use App\Movimiento;
use App\Detallemovcaja;
use App\Lote;
use App\Numeracion;
use App\Stock;
use App\Cuenta;
use App\Person;
use App\Historia;
use App\Productoprincipio;
use App\Conveniofarmacia;
use App\Empresa;
use App\Plan;
use App\Librerias\Libreria;
use App\Librerias\EnLetras;
use App\Librerias\phpJson;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;
use Excel;

ini_set('memory_limit', '512M'); //Raise to 512 MB
ini_set('max_execution_time', '60000'); //Raise to 512 MB 

class VentaController extends Controller
{

    protected $folderview      = 'app.venta';
    protected $tituloAdmin     = 'ventas';
    protected $tituloRegistrar = 'Registrar venta';
    protected $tituloModificar = 'Modificar venta';
    protected $tituloVer       = 'Ver venta';
    protected $tituloEliminar  = 'Eliminar venta';
    protected $rutas           = array('create' => 'venta.create',
            'create2'  => 'venta.create2', 
            'edit'   => 'venta.edit',
            'show'   => 'venta.show', 
            'delete' => 'venta.eliminar',
            'search' => 'venta.buscar',
            'search2' => 'venta.buscar2',
            'index'  => 'venta.index',
            'index2'  => 'venta.index2',
            'createpedido'  => 'venta.createpedido',
            'notacredito' => 'venta.createnotacredito',
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

        $entidad          = 'Venta';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        $cboTipoDoc = array(''=>'Todos...');
        $rs = Tipodocumento::where('tipomovimiento_id','=','4')->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboTipoDoc = $cboTipoDoc + array($value->id => $value->nombre);
        }
        //$user = Auth::user();
        //$responsable_id=$user->person_id;
        $cboSituacion = array(''=>'Todos...','N'=>'Pagado','P'=>'Pendiente','U'=>'Anulado');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user','cboTipoDoc','cboSituacion'));
    }

    public function index2()
    {

        $entidad          = 'Venta';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        $cboTipoDoc = array(''=>'Todos...');
        $rs = Tipodocumento::where('tipomovimiento_id','=','4')->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboTipoDoc = $cboTipoDoc + array($value->id => $value->nombre);
        }
        $cboSituacion = array(''=>'Todos...','U'=>'Anulado');
        return view($this->folderview.'.admin2')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user','cboTipoDoc','cboSituacion'));
    }

    public function buscarpedido()
    {

        $entidad          = 'Venta';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.adminPedido')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    public function buscarproducto(Request $request)
    {
        $entidad          = 'Producto';
        $title            = 'Agregar Productos';
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipo          = array("" => "Todos","P" => "Producto", "I" => "Insumo", "O" => "Otros"); 
        $tipoventa        = $request->input('tipoventa');
        $descuentokayros  = $request->input('descuentokayros');
        $copago           = $request->input('copago');
        return view($this->folderview.'.producto')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipo','tipoventa','descuentokayros','copago'));
    }

    public function buscarconvenio(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'BusquedaConvenio';
        $venta = null;
        $formData = array('venta.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.convenio')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function listarconvenio(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Convenio';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Conveniofarmacia::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Kayros', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Copago', 'numero' => '1');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_ver  = $this->tituloVer;
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
            return view($this->folderview.'.listconvenio')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'titulo_ver', 'ruta'));
        }
        return view($this->folderview.'.listconvenio')->with(compact('lista', 'entidad'));
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
        $entidad          = 'Venta';
        $fechainicio             = Libreria::getParam($request->input('fechainicio'));
        $fechafin             = Libreria::getParam($request->input('fechafin'));
        $numero             = Libreria::getParam($request->input('numero'));
        $paciente             = Libreria::getParam($request->input('paciente'));
        $resultado        = Venta::leftjoin('person','person.id','=','movimiento.persona_id')
                                ->where('tipomovimiento_id', '=', '4')
                                ->where('ventafarmacia','=','S')//where('serie','=','4')->
                                ->where(function($query) use ($numero){   
                                if (!is_null($numero) && $numero !== '') {
                                   
                                    $query->where('numero', '=', $numero);
                                }
                            })->where(function($query) use ($fechainicio,$fechafin){   
                                if (!is_null($fechainicio) && $fechainicio !== '') {
                                    $begindate   = Date::createFromFormat('d/m/Y', $fechainicio)->format('Y-m-d');
                                    $query->where('fecha', '>=', $begindate);
                                }
                                if (!is_null($fechafin) && $fechafin !== '') {
                                    $enddate   = Date::createFromFormat('d/m/Y', $fechafin)->format('Y-m-d');
                                    $query->where('fecha', '<=', $enddate);
                                }
                            });
        if($paciente!=""){
            $resultado = $resultado->where(DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }
        $resultado = $resultado->orderBy('movimiento.id','DESC')->select('movimiento.*');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Proveedor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Boleta', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '5');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_ver  = $this->tituloVer;
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'titulo_ver', 'ruta', 'user'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    public function excel(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Venta';
        $fechainicio             = Libreria::getParam($request->input('fechainicial'));
        $fechafin             = Libreria::getParam($request->input('fechafinal'));
        $numero             = Libreria::getParam($request->input('numero'));
        $situacion             = Libreria::getParam($request->input('situacion'));
        $paciente             = Libreria::getParam($request->input('paciente'));
        $resultado        = Venta::leftjoin('person','person.id','=','movimiento.persona_id')
                                ->where('tipomovimiento_id', '=', '4')
                                ->where('movimiento.situacion','not like','U');
        if($situacion!=""){
            $resultado = $resultado->where('movimiento.formapago','like',$situacion);
        }
        $resultado        = $resultado->where('ventafarmacia','=','S')//where('serie','=','4')->
                                ->where(function($query) use ($numero){   
                                if (!is_null($numero) && $numero !== '') {
                                   
                                    $query->where('numero', '=', $numero);
                                }
                            })->where(function($query) use ($fechainicio,$fechafin){   
                                if (!is_null($fechainicio) && $fechainicio !== '') {
                                    $begindate   = Date::createFromFormat('d/m/Y', $fechainicio)->format('Y-m-d');
                                    $query->where('fecha', '>=', $begindate);
                                }
                                if (!is_null($fechafin) && $fechafin !== '') {
                                    $enddate   = Date::createFromFormat('d/m/Y', $fechafin)->format('Y-m-d');
                                    $query->where('fecha', '<=', $enddate);
                                }
                            });
        if($paciente!=""){
            $resultado = $resultado->where(DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%');
        }
        $resultado = $resultado->orderBy('movimiento.id','DESC')->select('movimiento.*');
        $lista            = $resultado->get();
        Excel::create('PendientesFarmacia', function($excel) use($lista,$request) {
 
            $excel->sheet('Reporte', function($sheet) use($lista,$request) {

                $cabecera[] = "Fecha";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Plan";
                $cabecera[] = "Paciente";
                //$cabecera[] = "Total";
                // $cabecera[] = "Estado";
                $cabecera[] = "Total";
                $array[] = $cabecera;
                $c=2;$d=3;$band=true;$stotal=0;$final=3;

                foreach ($lista as $key => $value){

                    if($value->tipodocumento_id=="4"){
                        $abreviatura="F";
                    }elseif($value->tipodocumento_id=="5"){
                        $abreviatura="B";    
                    }else{
                        $abreviatura="G"; 
                    }
                    $nombrepaciente = '';
                    if ($value->persona_id !== NULL) {
                        //echo 'entro'.$value->id;break;
                        if (isset($value->person->apellidopaterno)) {
                            $nombrepaciente = trim($value->person->apellidopaterno." ".$value->person->apellidomaterno." ".$value->person->nombres);
                        }else{
                            if (isset($value->person->bussinesname)) {
                                $nombrepaciente = trim($value->person->bussinesname);
                            } else {
                                $nombrepaciente="ERROR";
                            }
                        }                    
                    }else{
                        $nombrepaciente = "";
                    }

                    if ($value->empresa_id!='') {
                        $plan=$value->empresa->bussinesname;
                    } else {
                        $plan="PARTICULAR";
                    }

                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT));
                    $detalle[] = $plan;
                    $detalle[] = $nombrepaciente;
                    $detalle[] = $value->total;
                    $array[] = $detalle;
                }
                $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function excel2(Request $request)
    {
        set_time_limit(0);
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Venta';
        $fechainicio             = Libreria::getParam($request->input('fechainicial'));
        $fechafin             = Libreria::getParam($request->input('fechafinal'));
        $numero             = Libreria::getParam($request->input('numero'));
        $situacion             = Libreria::getParam($request->input('situacion'));
        $paciente             = Libreria::getParam($request->input('paciente'));
        $tipoDocumento        = Libreria::getParam($request->input('tipodocumento'));
        // dd($fechainicio, $fechafin, $numero, $situacion, $paciente);


        $resultado        = Venta::join('detallemovimiento as dmv','movimiento.id','=','dmv.movimiento_id')
                                ->leftjoin('person','person.id','=','movimiento.persona_id')
                                ->leftjoin('producto','producto.id','=','dmv.producto_id')
                                ->leftjoin('origen','producto.origen_id','=','origen.id')
                                ->leftjoin('laboratorio','producto.laboratorio_id','=','laboratorio.id')
                                ->leftjoin('person as doctor','doctor.id','=','movimiento.doctor_id')
                                ->where('tipomovimiento_id', '=', '4')
                                ->whereNotIn('movimiento.situacion',array('U','A'));
        if($situacion!="" || !(is_null($situacion))){
            $resultado = $resultado->where('movimiento.formapago','like',$situacion);
        }
        
        // dd($paciente);
        if($paciente!=""){
            $resultado = $resultado->where(DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%');
        }
        $resultado        = $resultado->where('ventafarmacia','=','S')//where('serie','=','4')->
                                ->where(function($query) use ($numero){   
                                if (!is_null($numero) && $numero !== '') {
                                   
                                    $query->where('numero', '=', $numero);
                                }
                            })->where(function($query) use ($fechainicio,$fechafin){   
                                if (!is_null($fechainicio) && $fechainicio !== '') {
                                    $begindate   = Date::createFromFormat('d/m/Y', $fechainicio)->format('Y-m-d');
                                    $query->where('fecha', '>=', $begindate);
                                }
                                if (!is_null($fechafin) && $fechafin !== '') {
                                    $enddate   = Date::createFromFormat('d/m/Y', $fechafin)->format('Y-m-d');
                                    $query->where('fecha', '<=', $enddate);
                                }
                            });
        if($paciente!=""){
            $resultado = $resultado->where(DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%');
        }
        
        if ($tipoDocumento != "") {
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$tipoDocumento);     
        }

        $resultado = $resultado->orderBy('movimiento.id','DESC')->select('movimiento.*', DB::raw("CONCAT(doctor.apellidopaterno,' ', doctor.apellidomaterno,' ',doctor.nombres) as doctor"), 'dmv.*','producto.nombre as producto','origen.nombre as origen','laboratorio.nombre as laboratorio');
        $lista            = $resultado->get();
        

        // dd($lista);
        
        Excel::create('ExcelFarmacia', function($excel) use($lista,$request) {
 
            $excel->sheet('Reporte', function($sheet) use($lista,$request) {

                $cabecera[] = "Fecha";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Plan";
                $cabecera[] = "Tipo de Plan";
                $cabecera[] = "Doctor";
                $cabecera[] = "Paciente";
                $cabecera[] = "Producto";
                $cabecera[] = "Origen";
                $cabecera[] = "Laboratorio";
                $cabecera[] = "Cantidad";
                $cabecera[] = "Total";
                $cabecera[] = "Estado";
                $array[] = $cabecera;
                $c=2;$d=3;$band=true;$stotal=0;$final=3;

                foreach ($lista as $key => $value){

                    if($value->tipodocumento_id=="4"){
                        $abreviatura="F";
                    }elseif($value->tipodocumento_id=="5"){
                        $abreviatura="B";    
                    }else{
                        $abreviatura="G"; 
                    }
                    $nombrepaciente = '';
                    if ($value->persona_id !== NULL) {
                        //echo 'entro'.$value->id;break;
                        if (isset($value->person->apellidopaterno)) {
                            $nombrepaciente = trim($value->person->apellidopaterno." ".$value->person->apellidomaterno." ".$value->person->nombres);
                        }else{
                            if (isset($value->person->bussinesname)) {
                                $nombrepaciente = trim($value->person->bussinesname);
                            } else {
                                $nombrepaciente="ERROR";
                            }
                        }                    
                    }else{
                        $nombrepaciente = "";
                    }

                    /*if ($value->empresa_id!='') {
                        $plan=$value->empresa->bussinesname;
                    } else {
                        $plan="PARTICULAR";
                    }*/
                    if($value->conveniofarmacia_id!=null){
                        $plan="CONVENIO";
                        $tipoPlan = Conveniofarmacia::find($value->conveniofarmacia_id);
                        $nombrePlan = $tipoPlan->nombre;
                    }else{
                        $plan="PARTICULAR";
                        $nombrePlan = "-";
                    }

                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT));
                    $detalle[] = $plan;
                    $detalle[] = $nombrePlan;
                    $detalle[] = $value->doctor;
                    $detalle[] = $nombrepaciente;
                    $detalle[] = $value->producto;
                    $detalle[] = $value->origen;
                    $detalle[] = $value->laboratorio;
                    $dscto = 0;
                    $subtotal1 = 0;
                    if ($value->conveniofarmacia_id !== null && $value->descuentokayros>0) {
                        $valaux = round(($value->precio*$value->cantidad), 2);
                        $precioaux = $value->precio - ($value->precio*($value->descuentokayros/100));
                        $dscto = round(($precioaux*$value->cantidad),2);
                        $subtotal1 = round(($dscto*($value->copago/100)),2);
                        $value->precio=round($subtotal1 / $value->cantidad,2);
                    }else{
                        $subtotal1 = round(($value->precio*$value->cantidad), 2);
                    }
                    $detalle[] = $value->cantidad;
                    $detalle[] = $subtotal1;
                    $estado = "";
                    if($value->estadopago=="P"){
                        $estado = "Pagado";
                    }elseif($value->estadopago=="PP"){
                        $estado = "Pendiente";
                    }else{
                        $estado = $value->estadopago;
                    }
                    $detalle[] = $estado;
                    $array[] = $detalle;
                    //dd($array);
                }
                $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function buscar2(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Venta';
        $fechainicio             = Libreria::getParam($request->input('fechainicio'));
        $fechafin             = Libreria::getParam($request->input('fechafin'));
        $numero             = Libreria::getParam($request->input('numero'));
        $paciente             = Libreria::getParam($request->input('paciente'));
        $resultado        = Venta::leftjoin('person','person.id','=','movimiento.persona_id')
                                ->where('tipomovimiento_id', '=', '4')
                                ->where('ventafarmacia','=','S')//where('serie','=','4')->
                                ->where(function($query) use ($numero){   
                                if (!is_null($numero) && $numero !== '') {
                                   
                                    $query->where('numero', '=', $numero);
                                }
                            })->where(function($query) use ($fechainicio,$fechafin){   
                                if (!is_null($fechainicio) && $fechainicio !== '') {
                                    $begindate   = Date::createFromFormat('d/m/Y', $fechainicio)->format('Y-m-d');
                                    $query->where('fecha', '>=', $begindate);
                                }
                                if (!is_null($fechafin) && $fechafin !== '') {
                                    $enddate   = Date::createFromFormat('d/m/Y', $fechafin)->format('Y-m-d');
                                    $query->where('fecha', '<=', $enddate);
                                }
                            });
        if($paciente!=""){
            $resultado = $resultado->where(DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }
        $resultado = $resultado->orderBy('movimiento.id','DESC')->select('movimiento.*');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Proveedor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Boleta', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado Descargado', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Observaciones', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '5');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_ver  = $this->tituloVer;
        $ruta             = $this->rutas;
        $user = Auth::user();
        if (count($lista) > 0) {
            $totalfac = 0;
            foreach ($lista as $key => $value3) {
                $totalfac+=$value3->total;
            }
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list2')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'titulo_ver', 'ruta', 'user', 'totalfac'));
        }
        return view($this->folderview.'.list2')->with(compact('lista', 'entidad'));
    }

    /**
     * Mostrar el resultado de búsquedas
     * 
     * @return Response 
     */
    public function listarpedido(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Venta';
        $fechainicio             = Libreria::getParam($request->input('fechainicio'));
        $fechafin             = Libreria::getParam($request->input('fechafin'));
        $resultado        = Venta::where('tipomovimiento_id', '=', '8')->where('ventafarmacia','=','S')->where(function($query) use ($fechainicio,$fechafin){   
                                if (!is_null($fechainicio) && $fechainicio !== '') {
                                    $begindate   = Date::createFromFormat('d/m/Y', $fechainicio)->format('Y-m-d');
                                    $query->where('fecha', '>=', $begindate);
                                }
                                if (!is_null($fechafin) && $fechafin !== '') {
                                    $enddate   = Date::createFromFormat('d/m/Y', $fechafin)->format('Y-m-d');
                                    $query->where('fecha', '<=', $enddate);
                                }
                            });
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Proveedor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Comprobante', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_ver  = $this->tituloVer;
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
            return view($this->folderview.'.listpedido')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'titulo_ver', 'ruta'));
        }
        return view($this->folderview.'.listpedido')->with(compact('lista', 'entidad'));
    }

    public function listarproducto(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Producto';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Producto::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->where(function ($query) use($request){
                        if ($request->input('tipo') !== null && $request->input('tipo') !== '') {
                            $query->where('tipo', '=', $request->input('tipo'));
                        }
                    })->orderBy('nombre', 'ASC');
        $lista            = $resultado->get();
        //$cboDistribuidora        = Distribuidora::lists('nombre', 'id')->all();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Principio Activo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Clasificacion', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Forma', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Presentacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Stock', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Precio Kayros', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Precio Venta', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cantidad', 'numero' => '1');
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
            return view($this->folderview.'.listproducto')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta','cboDistribuidora'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    public function buscandoproducto(Request $request)
    {
        $nombre = $request->input("nombre");
        $idtiposervicio = $request->input("idtiposervicio");
        $tipopago = $request->input('tipopaciente');
        
        $resultado        = Producto::where('nombre', 'LIKE', ''.strtoupper($nombre).'%')
                            ->orWhere(function($query) use($nombre){
                                $query->WhereIn('id',function($q) use($nombre){
                                    $q->select('producto_id')
                                      ->from('productoprincipio')
                                      ->leftjoin('principioactivo','principioactivo.id','=','Productoprincipio.principioactivo_id')
                                      ->where('principioactivo.nombre','like','%'.strtoupper($nombre).'%');
                                });
                            })
                            ->orderBy('origen_id','DESC')->orderBy('nombre', 'ASC')
                            ->limit(20)->get();
                            //dd($resultado);
        if(count($resultado)>0){
            $c=0;
            foreach ($resultado as $key => $value){
                $listado = Productoprincipio::where('producto_id','=',$value->id)->get();
                $principio = ''; 
                foreach ($listado as $key2 => $value2) {
                    $i = 0;
                    if ($i == 0) {
                        if ($value2->principioactivo !== null) {
                            $principio = $principio.$value2->principioactivo->nombre;
                        }
                    }else{
                        if ($value2->principioactivo !== null) {
                            $principio = $principio.'+'.$value2->principioactivo->nombre;
                        }
                    }
                    $i++;
                }
                /*$currentstock = Kardex::leftjoin('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->leftjoin('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $value->id)->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();*/

                //$currentstock = Kardex::leftjoin('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->where('producto_id', '=', $value->id)->orderBy('kardex.id', 'DESC')->first();
                $currentstock = Kardex::leftjoin('lote', 'kardex.lote_id', '=', 'lote.id')->where('producto_id', '=', $value->id)->orderBy('kardex.id', 'DESC')->first();
                $stock = 0;
                /*$stock2 = Stock::where('almacen_id','=',1)->where('producto_id','=',$value->id)->first();
                if(!is_null($stock2)){
                    $stock = $stock2->cantidad;
                }*/

                if ($currentstock !== null) {
                    $stock=$currentstock->stockactual;
                }
                $nombrepresentacion = '';
                if ($value->presentacion != null) {
                    $nombrepresentacion=$value->presentacion->nombre;
                }

                $condicionAlmacenamiento = '';
                if ($value->condicionAlmacenamiento != null) {
                    $condicionAlmacenamiento = $value->condicionAlmacenamiento->nombre;
                }

                $concentracion = '';
                if ($value->concentracion != null) {
                    $concentracion = $value->concentracion;
                }

                $formaFarmaceutica = '';
                if ($value->formaFarmaceutica != null) {
                        $formaFarmaceutica = $value->formaFarmaceutica->nombre;
                }   

                // if ($producto->condicionAlmac_id !== null) {
                //     if ($producto->condicionAlmacenamiento !== null) {
                //         $condicionAlmacenamiento = $producto->condicionAlmacenamiento->nombre;
                //     }
                // }

                $data[$c] = array(
                    'nombre' => $value->nombre,
                    'principio' => $principio,
                    'presentacion' => $nombrepresentacion,
                    'idCondicion' => $value->condicionAlmac_id,
                    'condicionAlmacenamiento' => $condicionAlmacenamiento,
                    'idConcentracion' => $concentracion,
                    'concentracion' => $concentracion,
                    'idForma' => $value->formaFarmac_id,
                    'formaFarmaceutica' => $formaFarmaceutica,
                    'stock' => number_format($stock, 0, '.', ''),
                    'preciokayros' => number_format($value->preciokayros,2,'.',''),
                    'precioventa' => number_format($value->precioventa,2,'.',''),
                    'preciocompra' => number_format($value->preciocompra,2,'.',''),
                    'idproducto' => $value->id,
                    'origen' => $value->origen_id,
                    'idunspsc' => $value->id_unspsc,
                    'regSanitario' => $value->registro_sanitario
                );
                $c++;
            }            
        }else{
            $data = array();
        }
        return json_encode($data);
    }

    public function buscandoproducto2(Request $request)
    {
        //$data = array();
        $nombre = $request->input("nombre");
        $idtiposervicio = $request->input("idtiposervicio");
        $tipopago = $request->input('tipopaciente');
        
        $resultado        = Producto::leftjoin('productoprincipio','productoprincipio.producto_id','=','producto.id')->leftjoin('principioactivo','principioactivo.id','=','productoprincipio.principioactivo_id')
                            ->where('producto.nombre', 'LIKE', ''.strtoupper($nombre).'%')
                            ->select('producto.id','producto.nombre','principioactivo.nombre as principioactivo','producto.preciokayros','producto.precioventa','producto.presentacion_id','producto.presentacion_id')
                            ->orderBy('origen_id','DESC')
                            ->orderBy('producto.nombre', 'ASC')->get();      

        if(count($resultado)>0){
            $c=0;
            foreach ($resultado as $key => $value){

                $principio = '';
                
                if ($value->principioactivo !== null) {
                    $principio = $value->principioactivo;
                }

                $currentstock = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $value->id)->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                $stock = 0;
                if ($currentstock !== null) {
                    $stock=$currentstock->stockactual;
                }

                $nombrepresentacion = '';
                if ($value->presentacion != null) {
                    $nombrepresentacion=$value->presentacion->nombre;
                }
                
                $data[$c] = array(
                    'nombre' => $value->nombre,
                    'principio' => $principio,
                    'presentacion' => $nombrepresentacion,
                    'stock' => number_format($stock, 0, '.', ''),
                    'preciokayros' => $value->preciokayros,
                    'precioventa' => $value->precioventa,
                    'idproducto' => $value->id,
                );
                $c++;
            }            
        }else{
            $data = array();
        }
        return json_encode($data);
    }

    public function consultaproducto(Request $request)
    {
        $producto = Producto::find($request->input("idproducto"));
        $currentstock = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $producto->id)->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
        $stock = 0;
        if ($currentstock !== null) {
            $stock=$currentstock->stockactual;
        }

        return $producto->id.'@'.$producto->preciokayros.'@'.$producto->precioventa.'@'.$stock.'@'.$producto->preciocompra;
    }

    public function agregarcarritoventa(Request $request)
    {
        $lista = array();
        $cadena = '';
        if($request->input('conveniofarmacia_id')!="" && $request->input('conveniofarmacia_id')!="0"){
            $convenio = Conveniofarmacia::find($request->input('conveniofarmacia_id'));
            if(!is_null($convenio->plan)  && $convenio->plan->tipo=='Institucion'){
                $tipoventa ='N';
                $pro = Producto::find($request->input('producto_id'));
                if($pro->origen_id==1){//MARCA
                    $precio=round($request->input('precio')*(100-$convenio->plan->descuentomarca)/100,2);
                }elseif($pro->origen_id==6){//GENERICO
                    $precio=round($request->input('precio')*(100-$convenio->plan->descuentogenerico)/100,2);
                }else{
                    $precio = Libreria::getParam($request->input('precio'));
                }
            }else{
                $precio = Libreria::getParam($request->input('precio'));
                $tipoventa = Libreria::getParam($request->input('tipoventa'));
            }
        }else{
            $tipoventa = Libreria::getParam($request->input('tipoventa'));
            $precio = Libreria::getParam($request->input('precio'));
        }

        if($request->input('detalle')=='false'){
            $request->session()->put('carritoventa', $lista);
        }
        if ($request->session()->get('carritoventa') !== null) {
            $lista          = $request->session()->get('carritoventa');
            $cantidad       = Libreria::getParam($request->input('cantidad'));
            $producto_id       = Libreria::getParam($request->input('producto_id'));
            $preciokayros       = Libreria::getParam($request->input('preciokayros'));
            $producto   = Producto::find($producto_id);
            $descuentokayros       = Libreria::getParam($request->input('descuentokayros'));
            $copago       = Libreria::getParam($request->input('copago'));
            $estaPresente   = false;
            $indicepresente = '';
            for ($i=0; $i < count($lista); $i++) { 
                if ($lista[$i]['producto_id'] == $producto_id) {
                    $estaPresente   = true;
                    $indicepresente = $i;
                }
            }
            if ($tipoventa == 'C') {
                //$precio = $preciokayros - ($preciokayros*($descuentokayros/100));
                //$precio = $precio*($copago/100);
                $precio = $preciokayros;
            }

            if ($estaPresente === true) {
                $lista[$indicepresente]  = array('cantidad' => $cantidad, 'precio' => $precio, 'productonombre' => $producto->nombre,'producto_id' => $producto_id, 'codigobarra' => $producto->codigobarra, 'tipoventa' => $tipoventa, 'descuentokayros' => $descuentokayros, 'copago' => $copago);
            }else{
                $lista[]  = array('cantidad' => $cantidad, 'precio' => $precio, 'productonombre' => $producto->nombre,'producto_id' => $producto_id, 'codigobarra' => $producto->codigobarra, 'tipoventa' => $tipoventa, 'descuentokayros' => $descuentokayros, 'copago' => $copago);
            }
            
            $cadena   .= '<table style="width: 100%;" border="1">';
            $cadena   .= '<thead>
                                <tr>
                                    <th bgcolor="#E0ECF8" class="text-center">N°</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Producto</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Cantidad</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Precio Unit</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Dscto</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Subtotal</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Quitar</th>                            
                                </tr>
                            </thead>';
            
            $total = 0;
            $dscto = 0;
            $subtotal = 0;
            $w = 0;
            for ($i=0; $i < count($lista); $i++) { $w++;
                if ($lista[$i]['tipoventa'] == 'C') {
                    $precioaux = $lista[$i]['precio'] - ($lista[$i]['precio']*($lista[$i]['descuentokayros']/100));
                    $dscto = round(($precioaux*$lista[$i]['cantidad']),2);
                    $subtotal = round(($dscto*($lista[$i]['copago']/100)),2);
                }else{
                    $subtotal = round(($lista[$i]['cantidad']*$lista[$i]['precio']), 2);
                }
                
                $total    += $subtotal;
                $cadena   .= '<tr>';
                $cadena   .= '<td class="text-center"><span style="display: block; font-size:.7em">'.$w.'</span></td>';
                $cadena   .= '<td class="text-center" style="width:750px;"><span style="display: block; font-size:.7em">'.$lista[$i]['productonombre'].'</span></td>';
                $cadena   .= '<td class="text-center" style="width:100px;"><span style="display: block; font-size:.7em">'.$lista[$i]['cantidad'].'</span></td>';
                $cadena   .= '<td class="text-center" style="width:100px;"><span style="display: block; font-size:.7em">'.$lista[$i]['precio'].'</span></td>';
                $cadena   .= '<td class="text-center" style="width:90px;"><span style="display: block; font-size:.7em">'.$dscto.'</span></td>';
                $cadena   .= '<td class="text-center" style="width:90px;"><span style="display: block; font-size:.7em">'.$subtotal.'</span></td>';
                $cadena   .= '<td class="text-center"><span style="display: block; font-size:.7em"><a class="btn btn-xs btn-danger" onclick="quitar(\''.$i.'\');">Quitar</a></span></td></tr>';
            }
            $cadena  .= '<tr><th colspan="3" style="text-align: right;">TOTAL</th><td class="text-center">'.$total.'<input type ="hidden" id="totalventa" readonly=""  name="totalventa" value="'.$total.'"></td></tr></tr>';
            $cadena .= '</table>';
            $request->session()->put('carritoventa', $lista);

        }else{
            $cantidad       = Libreria::getParam($request->input('cantidad'));
            $producto_id       = Libreria::getParam($request->input('producto_id'));
            $precio       = Libreria::getParam($request->input('precio'));
            $preciokayros       = Libreria::getParam($request->input('preciokayros'));
            $producto   = Producto::find($producto_id);
            $tipoventa       = Libreria::getParam($request->input('tipoventa'));
            $descuentokayros       = Libreria::getParam($request->input('descuentokayros'));
            $copago       = Libreria::getParam($request->input('copago'));
            if ($tipoventa == 'C') {
                //$precio = $preciokayros - ($preciokayros*($descuentokayros/100));
                //$precio = $precio*($copago/100);
                $precio = $preciokayros;
            }

            $dscto = 0;
            $subtotal = 0;

            if ($tipoventa == 'C') {
                $precioaux = $precio - ($precio*($descuentokayros/100));
                $dscto = round(($precioaux*$cantidad),2);
                $subtotal = round(($dscto*($copago/100)),2);
            }else{
                $subtotal       = round(($cantidad*$precio), 2);
            }
            
            $cadena   .= '<table style="width: 100%;" border="1">';
            $cadena   .= '<thead>
                                <tr>
                                    <th bgcolor="#E0ECF8" class="text-center">Producto</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Cantidad</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Precio Unit</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Dscto</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Subtotal</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Quitar</th>                            
                                </tr>
                            </thead>';
            $cadena         .= '<tr><td class="text-center" style="width:550px;"><span style="display: block; font-size:.7em">'.$producto->nombre.'</span></td>';
            $cadena         .= '<td class="text-center" style="width:100px;"><span style="display: block; font-size:.7em">'.$cantidad.'</span></td>';
            $cadena         .= '<td class="text-center" style="width:100px;"><span style="display: block; font-size:.7em">'.$precio.'</span></td>';
            $cadena         .= '<td class="text-center" style="width:90px;"><span style="display: block; font-size:.7em">'.$dscto.'</span></td>';
            $cadena         .= '<td class="text-center" style="width:90px;"><span style="display: block; font-size:.7em">'.$subtotal.'</span></td>';
            $cadena         .= '<td class="text-center"><span style="display: block; font-size:.7em"><a class="btn btn-xs btn-danger" onclick="quitar(\'0\');">Quitar</a></span></td><tr>';
            $cadena         .= '<tr><th colspan="3" style="text-align: right;">TOTAL</th><td class="text-center">'.$subtotal.'<input type ="hidden" id="totalventa" readonly=""  name="totalventa" value="'.$subtotal.'"></td></tr></tr>';
            $cadena         .= '</table>';
            $lista[]  = array('cantidad' => $cantidad, 'precio' => $precio, 'productonombre' => $producto->nombre,'producto_id' => $producto_id, 'codigobarra' => $producto->codigobarra, 'tipoventa' => $tipoventa, 'descuentokayros' => $descuentokayros, 'copago' => $copago);
            $request->session()->put('carritoventa', $lista);
        }
        return $cadena; 


    }

    public function quitarcarritoventa(Request $request)
    {
        $id       = $request->input('valor');
        $cantidad = count($request->session()->get('carritoventa'));
        $lista2   = $request->session()->get('carritoventa');
        $lista    = array();
        $producto_id = '';
        for ($i=0; $i < $cantidad; $i++) {
            if ($i != $id) {
                $lista[] = $lista2[$i];
            }else{
                $producto_id = $lista2[$i]['producto_id'];
            }
        }
        $cadena   = '<table style="width: 100%;" border="1">';
        $cadena   .= '<thead>
                            <tr>
                                <th bgcolor="#E0ECF8" class="text-center">Producto</th>
                                <th bgcolor="#E0ECF8" class="text-center">Cantidad</th>
                                <th bgcolor="#E0ECF8" class="text-center">Precio Unit</th>
                                <th bgcolor="#E0ECF8" class="text-center">Dscto</th>
                                <th bgcolor="#E0ECF8" class="text-center">Subtotal</th>
                                <th bgcolor="#E0ECF8" class="text-center">Quitar</th>                            
                            </tr>
                        </thead>';
            
            $total = 0;
            $dscto = 0;
            $subtotal = 0;
            
            for ($i=0; $i < count($lista); $i++) {
                if ($lista[$i]['tipoventa'] == 'C') {
                    $precioaux = $lista[$i]['precio'] - ($lista[$i]['precio']*($lista[$i]['descuentokayros']/100));
                    $dscto = round(($precioaux*$lista[$i]['cantidad']),2);
                    $subtotal = round(($dscto*($lista[$i]['copago']/100)),2);
                }else{
                    $subtotal = round(($lista[$i]['cantidad']*$lista[$i]['precio']), 2);
                }
                $total    += $subtotal;
                $cadena   .= '<tr><td class="text-center" style="width:750px;"><span style="display: block; font-size:.7em">'.$lista[$i]['productonombre'].'</span></td>';
                $cadena   .= '<td class="text-center" style="width:100px;"><span style="display: block; font-size:.7em">'.$lista[$i]['cantidad'].'</span></td>';
                $cadena   .= '<td class="text-center" style="width:100px;"><span style="display: block; font-size:.7em">'.$lista[$i]['precio'].'</span></td>';
                $cadena         .= '<td class="text-center" style="width:90px;"><span style="display: block; font-size:.7em">'.$dscto.'</span></td>';
                $cadena   .= '<td class="text-center" style="width:90px;"><span style="display: block; font-size:.7em">'.$subtotal.'</span></td>';
                $cadena   .= '<td class="text-center"><span style="display: block; font-size:.7em"><a class="btn btn-xs btn-danger" onclick="quitar(\''.$i.'\');">Quitar</a></span></td></tr>';
            }
            $cadena  .= '<tr><th colspan="3" style="text-align: right;">TOTAL</th><td class="text-center">'.$total.'<input type ="hidden" id="totalventa" readonly=""  name="totalventa" value="'.$total.'"></td></tr></tr>';
            $cadena .= '</table>';
        $request->session()->put('carritoventa', $lista);
        return  $cadena;
    }

    public function quitarcarritonotacredito(Request $request)
    {
        $id       = $request->input('valor');
        $cantidad = count($request->session()->get('carritonotacredito'));
        $lista2   = $request->session()->get('carritonotacredito');
        $lista    = array();
        $producto_id = '';
        for ($i=0; $i < $cantidad; $i++) {
            if ($i != $id) {
                $lista[] = $lista2[$i];
            }else{
                $producto_id = $lista2[$i]['producto_id'];
            }
        }
        $cadena   = '<table style="width: 100%;" border="1">';
        $cadena   .= '<thead>
                            <tr>
                                <th bgcolor="#E0ECF8" class="text-center">Producto</th>
                                <th bgcolor="#E0ECF8" class="text-center">Cantidad</th>
                                <th bgcolor="#E0ECF8" class="text-center">Precio Unit</th>
                                <th bgcolor="#E0ECF8" class="text-center">Dscto</th>
                                <th bgcolor="#E0ECF8" class="text-center">Subtotal</th>
                                <th bgcolor="#E0ECF8" class="text-center">Quitar</th>                            
                            </tr>
                        </thead>';
            
            $total = 0;
            $dscto = 0;
            $subtotal = 0;
            
            for ($i=0; $i < count($lista); $i++) {
                if ($lista[$i]['tipoventa'] == 'C') {
                    $precioaux = $lista[$i]['precio'] - ($lista[$i]['precio']*($lista[$i]['descuentokayros']/100));
                    $dscto = round(($precioaux*$lista[$i]['cantidad']),2);
                    $subtotal = round(($dscto*($lista[$i]['copago']/100)),2);
                }else{
                    $subtotal = round(($lista[$i]['cantidad']*$lista[$i]['precio']), 2);
                }
                $total    += $subtotal;
                $cadena   .= '<tr><td class="text-center" style="width:750px;"><span style="display: block; font-size:.7em">'.$lista[$i]['productonombre'].'</span></td>';
                $cadena   .= '<td class="text-center" style="width:100px;"><span style="display: block; font-size:.7em">'.$lista[$i]['cantidad'].'</span></td>';
                $cadena   .= '<td class="text-center" style="width:100px;"><span style="display: block; font-size:.7em">'.$lista[$i]['precio'].'</span></td>';
                $cadena         .= '<td class="text-center" style="width:90px;"><span style="display: block; font-size:.7em">'.$dscto.'</span></td>';
                $cadena   .= '<td class="text-center" style="width:90px;"><span style="display: block; font-size:.7em">'.$subtotal.'</span></td>';
                $cadena   .= '<td class="text-center"><span style="display: block; font-size:.7em"><a class="btn btn-xs btn-danger" onclick="quitar(\''.$i.'\');">Quitar</a></span></td></tr>';
            }
            $cadena  .= '<tr><th colspan="3" style="text-align: right;">TOTAL</th><td class="text-center">'.$total.'<input type ="hidden" id="totalventa" readonly=""  name="totalventa" value="'.$total.'"></td></tr></tr>';
            $cadena .= '</table>';
        $request->session()->put('carritonotacredito', $lista);
        return  $cadena;
    }

    public function comprobarproducto(Request $request)
    {

        $lista   = $request->session()->get('carritoventa');
        $valor = Libreria::obtenerParametro($request->input('valor'));
        $resp = "NO";
        for ($i=0; $i < count($lista); $i++) {
            //echo $lista[$i]['codigobarra'].'-'.$valor;
            if ($valor == $lista[$i]['codigobarra']) {
                $resp = "SI";
                break;
            }
        }

        return $resp;

    }

    public function calculartotal(Request $request)
    {

        
        $lista   = $request->session()->get('carritoventa');
        
        $total  = 0;
        for ($i=0; $i < count($lista); $i++) {
                $subtotal = round(($lista[$i]['cantidad']*$lista[$i]['precio']), 2);
                $total    += $subtotal;
        }
       
        return  $total;
    }


    public function generarNumero(Request $request)
    {
        $tipodoc = $request->input("tipodocumento_id");
        //if($tipodoc!=15)
        $numeracion = Numeracion::where('tipomovimiento_id','=',4)->where('tipodocumento_id','=',$tipodoc)->where('serie','=',4)->first();
        if(is_null($numeracion)){
            $numero = Movimiento::NumeroSigue(4,$tipodoc,4,'N');
            $numeracion = new Numeracion();
            $numeracion->serie=4;
            $numeracion->numero=$numero-1;
            $numeracion->tipomovimiento_id=4;
            $numeracion->tipodocumento_id=$tipodoc;
            $numeracion->save();
        }else{
            $numero = str_pad($numeracion->numero + 1,8,'0',STR_PAD_LEFT);
        }
            //$numero  = Movimiento::NumeroSigue2(4,$tipodoc,4,'N');
        /*else
            $numero  = Movimiento::NumeroSigue(4,$tipodoc,4);*/
        return $numero;
    }

    public function agregarconvenio(Request $request)
    {
        $convenio_id = $request->input("convenio_id");
        $convenio = Conveniofarmacia::find($convenio_id);
        if(!is_null($convenio->plan)){
            return $convenio->nombre.'|'.$convenio->id.'|'.$convenio->plan->descuentomarca.'|'.$convenio->plan->descuentogenerico.'|'.$convenio->plan->tipo;
        }else{
            return $convenio->nombre.'|'.$convenio->id.'|0|0|';
        }
    }

    public function busquedacliente(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'BusquedaCliente';
        $venta = null;
        $formData = array('venta.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mantBusquedacliente')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function listarclientes()
    {
        /*$storage_path = storage_path();
        $url = $storage_path.'/clientes.json';
        $datos_clientes = file_get_contents($url);
        //$json_clientes = json_decode($datos_clientes, true);
        $json_clientes = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $datos_clientes), true );
        $table = "<table class='table table-bordered table-condensed table-hover' border='1' id='tablaClientes'><thead><tr><th class='text-center'>Codigo</th><th class='text-center'>Cliente</th></thead><tbody>";
        $id='';
        $cliente='';
        foreach ($json_clientes as $cliente) {
            
            //echo var_dump($cliente)."<br>";
            
                //echo var_dump($value2).'<br>';
                $j=0;
                foreach ($cliente as $value3) {
                    if ($j == 1) {
                        $id = $value3;
                    }elseif ($j == 0) {
                        $cliente = $value3;
                    }
                    $j++;
                }
                $table   .= "<tr id='".$id."'><td>".$id."</td><td>".$cliente."</td></tr>";
          
        }

        $table   .= "</tbody></table>";*/
        $storage_path = storage_path();
        $url = $storage_path.'/clientes.txt';
        $table = '';
        $file = fopen($url, "r");
        while(!feof($file)) {
            //$table = fgets($file); 
            return fgets($file);
        }
        fclose($file);

        return $table;
    }

    public function clienteid(Request $request)
    {
        $id = $request->input('id');
        $person = Person::find($id);
        return $person->id.'-'.$person->apellidopaterno.' '.$person->apellidomaterno.' '.$person->nombres;
    }

    public function buscandoclientes(Request $request)
    {
        $nombre = $request->input('nombre');
        $resultado = Historia::join('person', 'person.id', '=', 'historia.person_id')
                            ->leftjoin('convenio', 'convenio.id', '=', 'historia.convenio_id')
                            ->where(DB::raw('concat(apellidopaterno,\' \',apellidomaterno,\' \',nombres)'), 'LIKE', '%'.strtoupper($nombre).'%')
                            ->select('historia.*');
        $list      = $resultado->get();
        $data = array();
        if(count($list)>0){
            foreach ($list as $key => $value) {
                $convenio = '';
                if ($value->tipopaciente == 'Convenio') {
                    if ($value->empresa !== null) {
                        $convenio = $value->empresa;
                    }else{
                        $convenio = $value->soat;
                    }
                }else {
                    $convenio = 'Particular';
                }
                $personname = '';
                $id = '';
                if ($value->persona !== null) {
                    $personname = $value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                    $id = $value->persona->id;
                }else{
                    $persona = DB::connection('mysql')->table('person')->where('id','=',$value->person_id)->whereNotNull('deleted_at')->first();
                     $personname = $persona->apellidopaterno.' '.$persona->apellidomaterno.' '.$persona->nombres;
                    $id = $persona->id;
                }
                $data[] = array(
                                'value' => $personname,
                                'person_id' => $id,
                                'convenio' => $convenio,
                            );
            }

        }else{
            $data = array();
        }
        return json_encode($data);


    }

    public function buscandoconvenios(Request $request)
    {
        $nombre = $request->input('nombre');
        $resultado = Conveniofarmacia::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('nombre','ASC');
        $list      = $resultado->get();
        $data = array();
        if(count($list)>0){
            foreach ($list as $key => $value) {
                
                $data[] = array(
                                'value' => $value->nombre,
                                'convenio_id' => $value->id,
                                'kayros' => $value->kayros,
                                'copago' => $value->copago,
                            );
            }

        }else{
            $data = array();
        }
        return json_encode($data);


    }

    public function agregarempresa(Request $request)
    {
        $empresa_id = $request->input("empresa_id");
        $empresa = Person::find($empresa_id);
        /*$empresa->ruc = $request->input("ruc");
        $empresa->direccion = $request->input("direccion");
        $empresa->telefono = $request->input("telefono");
        $empresa->save();*/
        return $empresa->bussinesname.'*'.$empresa->id;
    }


    public function busquedaempresa(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'BusquedaEmpresa';
        $venta = null;
        $formData = array('venta.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mantBusquedaempresa')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar'));
    }


    public function buscandoempresas(Request $request)
    {
        /*$nombre = $request->input('nombre');
        $resultado = Empresa::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('nombre','ASC');
        $list      = $resultado->get();
        $data = array();
        if(count($list)>0){
            foreach ($list as $key => $value) {
                $data[] = array(
                                'value' => $value->nombre,
                                'empresa_id' => $value->id,
                                'ruc' => $value->ruc,
                                'direccion' => $value->direccion,
                                'telefono' => $value->telefono,
                            );
            }

        }else{
            $data = array();
        }*/
        $nombre = $request->input('nombre');
        $resultado        = Person::where('bussinesname', 'LIKE', '%'.strtoupper(str_replace("_","",$nombre)).'%')->orderBy('ruc', 'ASC');
        $list      = $resultado->get();
        $data = array();
        if(count($list)>0){
            foreach ($list as $key => $value) {
                $data[] = array(
                                'value' => $value->bussinesname,
                                'empresa_id' => $value->id,
                                'ruc' => $value->ruc,
                                'direccion' => $value->direccion,
                                'telefono' => $value->telefono,
                            );
            }

        }else{
            $data = array();
        }
        return json_encode($data);
    }


    public function indexempresa(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Empresa';
        $empresa = null;
        $formData = array('venta.guardarempresa');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mantEmpresa')->with(compact('empresa', 'formData', 'entidad', 'boton', 'listar'));
    }


    public function verificarruc(Request $request)
    {
        $ruc = strtoupper($request->input('ruc'));
        $nombre= ''; $direccion = ''; $telefono=''; $empresa_id=0; $resp = 'NO';
        $empresa = Empresa::where('ruc','=',$ruc)->first();
        if ($empresa !== NULL) {
            $resp = 'SI';
            $empresa_id = $empresa->id;
            $nombre = $empresa->nombre;
            $direccion = $empresa->direccion;
            $telefono = $empresa->telefono;
        }
        return $resp.'-'.$empresa_id.'-'.$nombre.'-'.$direccion.'-'.$telefono;
    }

    public function guardarempresa(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                'ruc'                  => 'required',
                'nombre'                  => 'required'
                );
        $mensajes = array(
            'ruc.required'         => 'Debe ingresar un numero de ruc',
            'nombre.required'         => 'Debe ingresar una razon social'
            );


        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $dat=array();
        $error = DB::transaction(function() use($request,&$dat){
            $empresa       = new Empresa();
            if ($request->input('empresa_id') > 0) {
                $empresa = Empresa::find($request->input('empresa_id'));
            }
            $empresa->ruc = strtoupper($request->input('ruc'));
            $empresa->nombre = strtoupper($request->input('nombre'));
            $empresa->direccion = Libreria::getParam($request->input('direccion'));
            $empresa->telefono = Libreria::getParam($request->input('telefono'));
            $empresa->save();

            $dat[0]=array("respuesta"=>"OK","empresa_id"=>$empresa->id,"nombre"=>$empresa->nombre);  
        });
        return is_null($error) ? json_encode($dat) : $error;
    }

    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Venta';
        $venta = null;
        //$cboDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $cboDocumento = array();
        $listdocument = Tipodocumento::where('tipomovimiento_id','=','4')->get();
        $cboDocumento = $cboDocumento + array('5' => 'BOLETA DE VENTA');
        foreach ($listdocument as $key => $value) {
            if ($value->id != 5) {
                $cboDocumento = $cboDocumento + array( $value->id => $value->nombre);
            }  
        }
        $cboCredito        = array("N" => 'NO', 'S' => 'SI');
        $cboCajafarmacia        = array("N" => 'NO', 'S' => 'SI');
        $cboTipoventa        = array("N" => 'Normal', 'C' => 'Convenio');
        $cboFormapago        = array("C" => 'Contado', 'P' => 'Pendiente', 'T' => 'Tarjeta');
        $cboTipoTarjeta    = array("VISA" => "VISA", "MASTER" => "MASTER");
        $cboTipoTarjeta2    = array("CREDITO" => "CREDITO", "DEBITO" => "DEBITO");
        $formData = array('venta.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        $numero              = Movimiento::NumeroSigue(4,5,4,'N');//movimiento caja y documento ingreso
        $request->session()->forget('carritoventa');
        $lista = array();
        $request->session()->put('carritoventa', $lista);
        return view($this->folderview.'.mant')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','cboDocumento','cboCredito','numero','cboTipoventa','cboFormapago','cboTipoTarjeta','cboTipoTarjeta2'));
    }

    public function create2(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Venta';
        $venta = null;
        //$cboDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $cboDocumento = array();
        $listdocument = Tipodocumento::where('tipomovimiento_id','=','4')->get();
        $cboDocumento = $cboDocumento + array('5' => 'BOLETA DE VENTA');
        foreach ($listdocument as $key => $value) {
            if ($value->id != 5) {
                $cboDocumento = $cboDocumento + array( $value->id => $value->nombre);
            }  
        }
        $cboCredito        = array("N" => 'NO', 'S' => 'SI');
        $cboCajafarmacia        = array("N" => 'NO', 'S' => 'SI');
        $cboTipoventa        = array("N" => 'Normal', 'C' => 'Convenio');
        $cboFormapago        = array("C" => 'Contado', 'P' => 'Pendiente', 'T' => 'Tarjeta');
        $cboTipoTarjeta    = array("VISA" => "VISA", "MASTER" => "MASTER");
        $cboTipoTarjeta2    = array("CREDITO" => "CREDITO", "DEBITO" => "DEBITO");
        $formData = array('venta.store2');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        $numeracion = Numeracion::where('tipomovimiento_id','=',4)->where('tipodocumento_id','=',5)->where('serie','=',4)->first();
        if(is_null($numeracion)){
            $numero = Movimiento::NumeroSigue(4,5,4,'N');
            $numeracion = new Numeracion();
            $numeracion->serie=4;
            $numeracion->numero=$numero-1;
            $numeracion->tipomovimiento_id=4;
            $numeracion->tipodocumento_id=5;
            $numeracion->save();
        }else{
            $numero = str_pad($numeracion->numero + 1,8,'0',STR_PAD_LEFT);
        }
        //$numero              = Movimiento::NumeroSigue(4,5,4,'N');//movimiento caja y documento ingreso
        return view($this->folderview.'.mant2')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','cboDocumento','cboCredito','numero','cboTipoventa','cboFormapago','cboTipoTarjeta','cboTipoTarjeta2'));
    }

    public function clienteautocompletar($nombre)
    {
        $resultado = Historia::join('person', 'person.id', '=', 'historia.person_id')
                            ->leftjoin('convenio', 'convenio.id', '=', 'historia.convenio_id')
                            ->where(DB::raw('concat(apellidopaterno,\' \',apellidomaterno,\' \',nombres)'), 'LIKE', '%'.strtoupper($nombre).'%')
                            ->selectRaw("person.id, CONCAT(person.apellidopaterno, ' ', person.apellidomaterno, ' ', person.nombres) AS persona, (CASE WHEN historia.tipopaciente = 'Convenio' THEN CASE WHEN historia.empresa IS NOT NULL THEN historia.empresa ELSE historia.soat END ELSE 'Particular' END ) AS convenio, historia.empresa, historia.soat");
        $list      = $resultado->limit(10)->get();
        return json_encode($list);
    }

    public function empresaautocompletar($nombre)
    {
        $resultado        = Person::where('bussinesname', 'LIKE', '%'.strtoupper(str_replace("_","",$nombre)).'%')->orderBy('ruc', 'ASC');
        $list      = $resultado->limit(10)->get();
        /*$data = array();
        if(count($list)>0){
            foreach ($list as $key => $value) {
                $data[] = array(
                                'value' => $value->bussinesname,
                                'empresa_id' => $value->id,
                                'ruc' => $value->ruc,
                                'direccion' => $value->direccion,
                                'telefono' => $value->telefono,
                            );
            }

        }else{
            $data = array();
        }*/
        return json_encode($list);
    }

    public function store(Request $request)
    {

        /*$comprobar = Venta::where('serie','=','4')->where('numero','=',$request->input('documento'))->where('tipodocumento_id','=',$request->input('documento'))->first();
        if ($comprobar !== null) {
            $error = array(
                'total' => array(
                    'Numero de documento ya esta usado'
                    ));
            return json_encode($error);
        }*/
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                //'person_id' => 'required|integer|exists:person,id,deleted_at,NULL',
                'numerodocumento'                  => 'required',
                'fecha'                 => 'required'
                );
        $mensajes = array(
            //'person_id.required'         => 'Debe ingresar un cliente',
            'numerodocumento.required'         => 'Debe ingresar un numero de documento',
            'fecha.required'         => 'Debe ingresar fecha'
            );
        

        if (is_null($request->session()->get('carritoventa')) || count($request->session()->get('carritoventa')) === 0) {
            $error = array(
                'total' => array(
                    'Debe agregar al menos un producto'
                    ));
            return json_encode($error);
        }


        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }

        $dat=array();
        //if($request->input('formapago')=='C' || $request->input('formapago')=='T'){
            $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',4)->orderBy('movimiento.id','DESC')->limit(1)->first();
            if(count($rst)==0){
                $conceptopago_id=2;
            }else{
                $conceptopago_id=$rst->conceptopago_id;
            }
            if($conceptopago_id==2){
                $dat[0]=array("respuesta"=>"ERROR","msg"=>"Caja cerrada");
                return json_encode($dat);
            }
        //}

        $error = DB::transaction(function() use($request,&$dat){
            $validar = Venta::where('serie','=','4')->where('manual','like','N')->where('tipodocumento_id','=',$request->input('documento'))->where('numero','=',$request->input('numerodocumento'))->first();
            if ($validar == null) {
                # code...
            
            $ind = 0;
            $montoafecto = 0;
            $montonoafecto = 0;
            $lista = $request->session()->get('carritoventa');
            for ($i=0; $i < count($lista); $i++) {
                $producto = Producto::find($lista[$i]['producto_id']);
                $cantidad  = str_replace(',', '',$lista[$i]['cantidad']);
                $precio    = str_replace(',', '',$lista[$i]['precio']);
                $subtotal  = round(($cantidad*$precio), 2);
                if($request->input('tipoventa')=='C'){
                    $descuentokayros=$request->input('descuentokayros');
                    $copago=$request->input('copago');
                    $precioaux = $precio - ($precio*($descuentokayros/100));
                    $dscto = round(($precioaux*$cantidad),2);
                    $subtotal = round(($dscto*($copago/100)),2);
                }
                if ($producto->afecto == 'NO') {
                    $ind = 1;
                    $montonoafecto = $montonoafecto+$subtotal;
                }else{
                    $montoafecto = $montoafecto+$subtotal;
                }
            }

            if ($ind == 0) {
                $total = str_replace(',', '', $request->input('totalventa'));
                $venta                 = new Venta();
                $venta->serie = '004';
                $venta->tipodocumento_id          = $request->input('documento');
                if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                    $venta->persona_id = $request->input('person_id');
                }else{
                    $venta->nombrepaciente = $request->input('nombrepersona');
                }
                if ($request->input('documento') == '5' || $request->input('documento') == '14' ) {
                    $codigo="03";
                    $abreviatura="B";
                    
                    
                }else{
                    $codigo="01";
                    $abreviatura="F";
                    $venta->empresa_id = $request->input('empresa_id');
                }
                $venta->tipomovimiento_id          = 4;
                $venta->almacen_id          = 1;
                
                $venta->numero = $request->input('numerodocumento');
                $venta->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                $venta->subtotal=number_format($total/1.18,2,'.','');
                $venta->igv=number_format($total - $venta->subtotal,2,'.','');
                $venta->total = $total;
                $venta->credito = $request->input('credito');
                $venta->tipoventa = $request->input('tipoventa');
                $venta->formapago = $request->input('formapago');
                if($request->input('formapago')=="T"){
                    $venta->tarjeta=$request->input('tipotarjeta');//VISA/MASTER
                    $venta->tipotarjeta=$request->input('tipotarjeta2');//DEBITO/CREDITO
                }
                if ($request->input('tipoventa') == 'C') {
                    $venta->conveniofarmacia_id = $request->input('conveniofarmacia_id');
                    $venta->descuentokayros = $request->input('descuentokayros');
                    $venta->copago = $request->input('copago');
                }
                       
                $venta->inicial = 'N';
                $venta->estadopago = 'P';
                $venta->ventafarmacia = 'S';
                $venta->manual='N';
                $venta->descuentoplanilla = $request->input('descuentoplanilla');
                if($request->input('descuentoplanilla')=="SI"){
                    $venta->personal_id=$request->input('personal_id');
                }
                if ($request->input('formapago')=="P") {
                    $venta->estadopago = 'PP';
                }
                
                $user = Auth::user();
                $venta->responsable_id = $user->person_id;
                $venta->doctor_id = Libreria::obtenerParametro($request->input('doctor_id'));
                //$venta->numeroficha = Libreria::obtenerParametro($request->input('numeroficha'));
                //$venta->cajaprueba = $request->input('cajafamarcia');
                $venta->save();
                $movimiento_id = $venta->id;
                $arr=$lista;
                for ($i=0; $i < count($lista); $i++) {
                    $cantidad  = str_replace(',', '',$lista[$i]['cantidad']);
                    $precio    = str_replace(',', '',$lista[$i]['precio']);
                    $subtotal  = round(($cantidad*$precio), 2);
                    $detalleVenta = new Detallemovimiento();
                    $detalleVenta->cantidad = $cantidad;
                    $detalleVenta->precio = $precio;
                    $detalleVenta->subtotal = $subtotal;
                    $detalleVenta->movimiento_id = $movimiento_id;
                    $detalleVenta->producto_id = $lista[$i]['producto_id'];
                    $detalleVenta->save();
                    $producto = Producto::find($lista[$i]['producto_id']);
                    if ($producto->afecto == 'NO') {
                        $ind = 1;
                        
                    }else{
                        
                    }
                    
                    
                    // consulta lotes
                    $lotes = Lote::where('producto_id','=',$lista[$i]['producto_id'])->where('queda','>','0')->orderBy('fechavencimiento','ASC')->get();
                    $aux = $lista[$i]['cantidad'];
                    foreach ($lotes as $key => $value) {
                        if ($value->queda >= $aux) {
                            $queda = $value->queda-$aux;
                            $value->queda = $queda;
                            $value->save();
                            $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $lista[$i]['producto_id'])->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                            $stockanterior = 0;
                            $stockactual = 0;
                            // ingresamos nuevo kardex
                            if ($ultimokardex === NULL) {
                                
                                
                            }else{
                                $stockanterior = $ultimokardex->stockactual;
                                $stockactual = $ultimokardex->stockactual-$aux;
                                $kardex = new Kardex();
                                $kardex->tipo = 'S';
                                $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                                $kardex->stockanterior = $stockanterior;
                                $kardex->stockactual = $stockactual;
                                $kardex->cantidad = $aux;
                                $kardex->precioventa = $precio;
                                //$kardex->almacen_id = 1;
                                $kardex->detallemovimiento_id = $detalleVenta->id;
                                $kardex->lote_id = $value->id;
                                $kardex->save();    

                            }
                            break;
                        }else{
                            $cantvendida = $value->queda;
                            $aux = $aux-$value->queda;
                            $value->queda = 0;
                            $value->save();
                            
                            $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $lista[$i]['producto_id'])->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                            $stockanterior = 0;
                            $stockactual = 0;
                            // ingresamos nuevo kardex
                            if ($ultimokardex === NULL) {
                                
                                
                            }else{
                                $stockanterior = $ultimokardex->stockactual;
                                $stockactual = $ultimokardex->stockactual-$cantvendida;
                                $kardex = new Kardex();
                                $kardex->tipo = 'S';
                                $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                                $kardex->stockanterior = $stockanterior;
                                $kardex->stockactual = $stockactual;
                                $kardex->cantidad = $cantvendida;
                                $kardex->precioventa = $precio;
                                //$kardex->almacen_id = 1;
                                $kardex->detallemovimiento_id = $detalleVenta->id;
                                $kardex->lote_id = $value->id;
                                $kardex->save();    

                            }
                        }
                    }

                   

                }

                # REGISTRO DE CREDITOS
                
                if ($request->input('formapago') == 'P') {
                    
                }else{

                    if ( ($request->input('documento') == 15 && $venta->copago > 0 ) || ($request->input('documento') != 15)) {
                        $total = str_replace(',', '', $request->input('totalventa'));
                        $movimiento                 = new Movimiento();
                        $movimiento->tipodocumento_id          = $request->input('documento');
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $movimiento->persona_id = $request->input('person_id');
                        }else{
                            $movimiento->nombrepaciente = $request->input('nombrepersona');
                        }
                        if ($request->input('documento') == '5') {
                            if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                                $movimiento->persona_id = $request->input('person_id');
                            }else{
                                $movimiento->nombrepaciente = $request->input('nombrepersona');
                            }
                            
                        }else{
                            $movimiento->empresa_id = $request->input('empresa_id');
                        }
                        $movimiento->tipomovimiento_id          = 2;
                        $movimiento->serie = '004';
                        $movimiento->numero = $request->input('numerodocumento');
                        $movimiento->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                        $movimiento->total = $total;
                        
                        $user = Auth::user();
                        $movimiento->responsable_id = $user->person_id;
                        $movimiento->conceptopago_id = 3;
                        $movimiento->caja_id = 4;
                        $movimiento->movimiento_id = $venta->id;
                        if($request->input('formapago')=="T"){
                            $movimiento->tipotarjeta=$request->input('tipotarjeta');
                            $movimiento->tarjeta=$request->input('tipotarjeta2');
                            $movimiento->voucher=$request->input('nroref');
                            $movimiento->totalpagado=0;
                        }else{
                            $movimiento->totalpagado=$total;
                        }
                        
                        $movimiento->save();

                        $venta->movimiento_id = $movimiento->id;
                        $venta->save();


                        $movimientocaja = new Detallemovcaja();
                        if ($request->input('documento') == '5') {
                            if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                                $movimientocaja->persona_id = $request->input('person_id');
                            }else{
                                $movimientocaja->nombrepaciente = $request->input('nombrepersona');
                            }
                            
                        }else{
                            $movimientocaja->empresa_id = $request->input('empresa_id');
                        }
                        //$movimientocaja->persona_id = $request->input('persona_id');
                        $movimientocaja->movimiento_id = $movimiento->id;
                        $aperturacierrecaja = Aperturacierrecaja::where('estado','=','A')->first();
                        $movimientocaja->aperturacierrecaja_id = $aperturacierrecaja->id;
                        $movimientocaja->descripcion = 'PAGO DE CLIENTE';
                        $movimientocaja->save();
                    }
                        
                }

            }else{
                // Para Monto Afecto
                if ($montoafecto > 0) {

                    $total = str_replace(',', '', $request->input('totalventa'));
                    $venta                 = new Venta();
                    $venta->serie = '004';
                    $venta->tipodocumento_id          = $request->input('documento');
                    if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                        $venta->persona_id = $request->input('person_id');
                    }else{
                        $venta->nombrepaciente = $request->input('nombrepersona');
                    }
                    if ($request->input('documento') == '5' || $request->input('documento') == '14' ) {
                        $codigo="03";
                        $abreviatura="B";
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $venta->persona_id = $request->input('person_id');
                        }else{
                            $venta->nombrepaciente = $request->input('nombrepersona');
                        }
                        
                    }else{
                        $codigo="01";
                        $abreviatura="F";
                        $venta->empresa_id = $request->input('empresa_id');
                    }
                    $venta->tipomovimiento_id          = 4;
                    $venta->almacen_id          = 1;
                    
                    $venta->numero = $request->input('numerodocumento');
                    $venta->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                    $venta->subtotal=number_format($montoafecto/1.18,2,'.','');
                    $venta->igv=number_format($montoafecto - $venta->subtotal,2,'.','');
                    $venta->total = $montoafecto;
                    $venta->credito = $request->input('credito');
                    $venta->tipoventa = $request->input('tipoventa');
                    $venta->formapago = $request->input('formapago');
                    if($request->input('formapago')=="Tarjeta"){
                        $venta->tarjeta=$request->input('tipotarjeta');//VISA/MASTER
                        $venta->tipotarjeta=$request->input('tipotarjeta2');//DEBITO/CREDITO
                        $venta->voucher=$request->input('nroref');
                    }
                    if ($request->input('tipoventa') == 'C') {
                        $venta->conveniofarmacia_id = $request->input('conveniofarmacia_id');
                        $venta->descuentokayros = $request->input('descuentokayros');
                        $venta->copago = $request->input('copago');
                    }
                           
                    $venta->inicial = 'N';
                    $venta->estadopago = 'P';
                    $venta->ventafarmacia = 'S';
                    $venta->manual='N';
                    $venta->descuentoplanilla = $request->input('descuentoplanilla');
                    if($request->input('descuentoplanilla')=="SI"){
                        $venta->personal_id=$request->input('personal_id');
                    }
                    if ($request->input('credito') == 'S') {
                        $venta->estadopago = 'PP';
                    }
                    
                    $user = Auth::user();
                    $venta->responsable_id = $user->person_id;
                    $venta->doctor_id = Libreria::obtenerParametro($request->input('doctor_id'));
                    //$venta->numeroficha = Libreria::obtenerParametro($request->input('numeroficha'));
                    //$venta->cajaprueba = $request->input('cajafamarcia');
                    $venta->save();
                    $movimiento_id = $venta->id;
                    $arr=$lista;
                    for ($i=0; $i < count($lista); $i++) {
                         $producto = Producto::find($lista[$i]['producto_id']);
                        if ($producto->afecto != 'NO') {
                            $cantidad  = str_replace(',', '',$lista[$i]['cantidad']);
                            $precio    = str_replace(',', '',$lista[$i]['precio']);
                            $subtotal  = round(($cantidad*$precio), 2);
                            $detalleVenta = new Detallemovimiento();
                            $detalleVenta->cantidad = $cantidad;
                            $detalleVenta->precio = $precio;
                            $detalleVenta->subtotal = $subtotal;
                            $detalleVenta->movimiento_id = $movimiento_id;
                            $detalleVenta->producto_id = $lista[$i]['producto_id'];
                            $detalleVenta->save();
                           
                            
                            
                            // consulta lotes
                            $lotes = Lote::where('producto_id','=',$lista[$i]['producto_id'])->where('queda','>','0')->orderBy('fechavencimiento','ASC')->get();
                            $aux = $lista[$i]['cantidad'];// 3
                            foreach ($lotes as $key => $value) { 
                                if ($value->queda >= $aux) {
                                    $queda = $value->queda-$aux;
                                    $value->queda = $queda;
                                    $value->save();

                                    $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $lista[$i]['producto_id'])->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                                    $stockanterior = 0;
                                    $stockactual = 0;
                                    // ingresamos nuevo kardex
                                    if ($ultimokardex === NULL) {
                                        
                                        
                                    }else{
                                        $stockanterior = $ultimokardex->stockactual;
                                        $stockactual = $ultimokardex->stockactual-$aux;
                                        $kardex = new Kardex();
                                        $kardex->tipo = 'S';
                                        $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                                        $kardex->stockanterior = $stockanterior;
                                        $kardex->stockactual = $stockactual;
                                        $kardex->cantidad = $aux;
                                        $kardex->precioventa = $precio;
                                        //$kardex->almacen_id = 1;
                                        $kardex->detallemovimiento_id = $detalleVenta->id;
                                        $kardex->lote_id = $value->id;
                                        $kardex->save();    

                                    }
                                    break;
                                }else{
                                    $cantvendida = $value->queda;
                                    $aux = $aux-$value->queda;
                                    $value->queda = 0;
                                    $value->save();
                                    
                                    $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $lista[$i]['producto_id'])->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                                    $stockanterior = 0;
                                    $stockactual = 0;
                                    // ingresamos nuevo kardex
                                    if ($ultimokardex === NULL) {
                                        
                                        
                                    }else{
                                        $stockanterior = $ultimokardex->stockactual;
                                        $stockactual = $ultimokardex->stockactual-$cantvendida;
                                        $kardex = new Kardex();
                                        $kardex->tipo = 'S';
                                        $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                                        $kardex->stockanterior = $stockanterior;
                                        $kardex->stockactual = $stockactual;
                                        $kardex->cantidad = $cantvendida;
                                        $kardex->precioventa = $precio;
                                        //$kardex->almacen_id = 1;
                                        $kardex->detallemovimiento_id = $detalleVenta->id;
                                        $kardex->lote_id = $value->id;
                                        $kardex->save();    

                                    }
                                }
                            }

                            
                            
                        }
                        

                    }

                    # REGISTRO DE CREDITOS
                    
                    if ($request->input('formapago') == 'P') {
                        
                    }else{

                        if ( ($request->input('documento') == 15 && $venta->copago > 0) || ($request->input('documento') != 15)) {
                            $total = str_replace(',', '', $request->input('totalventa'));
                            $movimiento                 = new Movimiento();
                            $movimiento->tipodocumento_id          = $request->input('documento');
                            if ($request->input('documento') == '5') {
                                if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                                    $movimiento->persona_id = $request->input('person_id');
                                }else{
                                    $movimiento->nombrepaciente = $request->input('nombrepersona');
                                }
                                
                            }else{
                                $movimiento->empresa_id = $request->input('empresa_id');
                            }
                            $movimiento->tipomovimiento_id          = 2;
                            
                            $movimiento->numero = $request->input('numerodocumento');
                            $movimiento->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                            $movimiento->total = $montoafecto;
                            
                            $user = Auth::user();
                            $movimiento->responsable_id = $user->person_id;
                            $movimiento->conceptopago_id = 3;
                            $movimiento->caja_id = 4;
                            $movimiento->movimiento_id = $venta->id;
                            if($request->input('formapago')=="T"){
                                $movimiento->tipotarjeta=$request->input('tipotarjeta');
                                $movimiento->tarjeta=$request->input('tipotarjeta2');
                                $movimiento->voucher=$request->input('nroref');
                                $movimiento->totalpagado=0;
                            }else{
                                $movimiento->totalpagado=$request->input('total',0);
                            }
                            
                            $movimiento->save();

                            $venta->movimiento_id = $movimiento->id;
                            $venta->save();

                            $movimientocaja = new Detallemovcaja();
                            if ($request->input('documento') == '5') {
                                if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                                    $movimientocaja->persona_id = $request->input('person_id');
                                }else{
                                    $movimientocaja->nombrepaciente = $request->input('nombrepersona');
                                }
                                
                            }else{
                                $movimientocaja->empresa_id = $request->input('empresa_id');
                            }
                            //$movimientocaja->persona_id = $request->input('persona_id');
                            $movimientocaja->movimiento_id = $movimiento->id;
                            $movimientocaja->descripcion = 'PAGO DE CLIENTE';
                            $movimientocaja->save();
                        }
                            
                    }

                }

                // Para Monto Inafecto

                $total = str_replace(',', '', $request->input('totalventa'));
                $venta2                 = new Venta();
                $venta2->serie = '004';
                $venta2->tipodocumento_id          = $request->input('documento');
                if ($request->input('documento') == '5' || $request->input('documento') == '14' ) {
                    $codigo="03";
                    $abreviatura="B";
                    if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                        $venta2->persona_id = $request->input('person_id');
                    }else{
                        $venta2->nombrepaciente = $request->input('nombrepersona');
                    }
                    
                }else{
                    $codigo="01";
                    $abreviatura="F";
                    $venta2->empresa_id = $request->input('empresa_id');
                    if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                        $venta2->persona_id = $request->input('person_id');
                    }else{
                        $venta2->nombrepaciente = $request->input('nombrepersona');
                    }
                }
                $venta2->tipomovimiento_id          = 4;
                $venta2->almacen_id          = 1;
                
                $venta2->numero = Movimiento::NumeroSigue(4,$request->input('documento'),4,'N');
                $venta2->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                $venta2->subtotal=number_format($montonoafecto,2,'.','');
                $venta2->igv=0;
                $venta2->total = $montonoafecto;
                $venta2->credito = $request->input('credito');
                $venta2->tipoventa = $request->input('tipoventa');
                $venta2->formapago = $request->input('formapago');
                if($request->input('formapago')=="T"){
                    $venta2->tarjeta=$request->input('tipotarjeta');//VISA/MASTER
                    $venta2->tipotarjeta=$request->input('tipotarjeta2');//DEBITO/CREDITO
                }
                if ($request->input('tipoventa') == 'C') {
                    $venta2->conveniofarmacia_id = $request->input('conveniofarmacia_id');
                    $venta2->descuentokayros = $request->input('descuentokayros');
                    $venta2->copago = $request->input('copago');
                }
                       
                $venta2->inicial = 'N';
                $venta2->manual='N';
                $venta2->estadopago = 'P';
                $venta2->ventafarmacia = 'S';
                if ($request->input('credito') == 'S') {
                    $venta2->estadopago = 'PP';
                }
                
                $user = Auth::user();
                $venta2->responsable_id = $user->person_id;
                $venta2->doctor_id = Libreria::obtenerParametro($request->input('doctor_id'));
                //$venta2->numeroficha = Libreria::obtenerParametro($request->input('numeroficha'));
                //$venta2->cajaprueba = $request->input('cajafamarcia');
                $venta2->save();
                $movimiento_id = $venta2->id;
                $arr=$lista;
                for ($i=0; $i < count($lista); $i++) {
                    $producto = Producto::find($lista[$i]['producto_id']);
                    if ($producto->afecto == 'NO') {
                        $cantidad  = str_replace(',', '',$lista[$i]['cantidad']);
                        $precio    = str_replace(',', '',$lista[$i]['precio']);
                        $subtotal  = round(($cantidad*$precio), 2);
                        $detalleVenta = new Detallemovimiento();
                        $detalleVenta->cantidad = $cantidad;
                        $detalleVenta->precio = $precio;
                        $detalleVenta->subtotal = $subtotal;
                        $detalleVenta->movimiento_id = $movimiento_id;
                        $detalleVenta->producto_id = $lista[$i]['producto_id'];
                        $detalleVenta->save();
                        
                        
                       
                        // consulta lotes
                        $lotes = Lote::where('producto_id','=',$lista[$i]['producto_id'])->where('queda','>','0')->orderBy('fechavencimiento','ASC')->get();
                        $aux = $lista[$i]['cantidad'];
                        foreach ($lotes as $key => $value) {
                            if ($value->queda >= $aux) {
                                $queda = $value->queda-$aux;
                                $value->queda = $queda;
                                $value->save();
                                $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $lista[$i]['producto_id'])->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                                $stockanterior = 0;
                                $stockactual = 0;
                                // ingresamos nuevo kardex
                                if ($ultimokardex === NULL) {
                                    
                                    
                                }else{
                                    $stockanterior = $ultimokardex->stockactual;
                                    $stockactual = $ultimokardex->stockactual-$aux;
                                    $kardex = new Kardex();
                                    $kardex->tipo = 'S';
                                    $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                                    $kardex->stockanterior = $stockanterior;
                                    $kardex->stockactual = $stockactual;
                                    $kardex->cantidad = $aux;
                                    $kardex->precioventa = $precio;
                                    //$kardex->almacen_id = 1;
                                    $kardex->detallemovimiento_id = $detalleVenta->id;
                                    $kardex->lote_id = $value->id;
                                    $kardex->save();    

                                }
                                break;
                            }else{
                                $cantvendida = $value->queda;
                                $aux = $aux-$value->queda;
                                $value->queda = 0;
                                $value->save();
                                
                                $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $lista[$i]['producto_id'])->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                                $stockanterior = 0;
                                $stockactual = 0;
                                // ingresamos nuevo kardex
                                if ($ultimokardex === NULL) {
                                    
                                    
                                }else{
                                    $stockanterior = $ultimokardex->stockactual;
                                    $stockactual = $ultimokardex->stockactual-$cantvendida;
                                    $kardex = new Kardex();
                                    $kardex->tipo = 'S';
                                    $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                                    $kardex->stockanterior = $stockanterior;
                                    $kardex->stockactual = $stockactual;
                                    $kardex->cantidad = $cantvendida;
                                    $kardex->precioventa = $precio;
                                    //$kardex->almacen_id = 1;
                                    $kardex->detallemovimiento_id = $detalleVenta->id;
                                    $kardex->lote_id = $value->id;
                                    $kardex->save();    

                                }
                            }
                        }

                        
                        
                    }
                    

                }

                # REGISTRO DE CREDITOS
                
                if ($request->input('formapago') == 'P') {
                    
                }else{

                        if ( ($request->input('documento') == 15 && $venta2->copago > 0 ) || ($request->input('documento') != 15)) {
                            $total = str_replace(',', '', $request->input('totalventa'));
                            $movimiento                 = new Movimiento();
                            $movimiento->tipodocumento_id          = $request->input('documento');
                            if ($request->input('documento') == '5') {
                                if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                                    $movimiento->persona_id = $request->input('person_id');
                                }else{
                                    $movimiento->nombrepaciente = $request->input('nombrepersona');
                                }
                                
                            }else{
                                $movimiento->empresa_id = $request->input('empresa_id');
                            }
                            $movimiento->tipomovimiento_id          = 2;
                            
                            $movimiento->numero = $request->input('numerodocumento');
                            $movimiento->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                            $movimiento->total = $montonoafecto;
                            
                            $user = Auth::user();
                            $movimiento->responsable_id = $user->person_id;
                            $movimiento->conceptopago_id = 3;
                            $movimiento->caja_id = 4;
                            $movimiento->movimiento_id = $venta2->id;
                            if($request->input('formapago')=="T"){
                                $movimiento->tipotarjeta=$request->input('tipotarjeta');
                                $movimiento->tarjeta=$request->input('tipotarjeta2');
                                $movimiento->voucher=$request->input('nroref');
                                $movimiento->totalpagado=0;
                            }else{
                                $movimiento->totalpagado=$request->input('total',0);
                            }
                            
                            $movimiento->save();

                            $venta2->movimiento_id = $movimiento->id;
                            $venta2->save();


                            $movimientocaja = new Detallemovcaja();
                            if ($request->input('documento') == '5') {
                                if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                                    $movimientocaja->persona_id = $request->input('person_id');
                                }else{
                                    $movimientocaja->nombrepaciente = $request->input('nombrepersona');
                                }
                                
                            }else{
                                $movimientocaja->empresa_id = $request->input('empresa_id');
                            }
                            //$movimientocaja->persona_id = $request->input('persona_id');
                            $movimientocaja->movimiento_id = $movimiento->id;
                            $movimientocaja->descripcion = 'PAGO DE CLIENTE';
                            $movimientocaja->save();
                        }
                        
                }

            }
            
            /*if ($request->input('documento') != '15') {
                if($ind == 1 && $montoafecto>0){
                    // Monto Afecto
                    if ($request->input('documento') == '5') {
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $person = Person::find($venta->persona_id);
                        }else{

                        }
                        
                    }else{
                        $empresa = Person::find($venta->empresa_id);
                    }
                    
                    $columna1=6;
                    $columna2="20480082673";//RUC HOSPITAL
                    $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                    $columna4=$codigo; // Revisar
                    $columna5=$abreviatura.str_pad($venta->serie,3,'0',STR_PAD_LEFT).'-'.$venta->numero;
                    $columna6=date('Y-m-d');
                    $columna7="sistemas@hospitaljuanpablo.pe";
                    if($codigo=="03"){//BOLETA
                        $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            if(strlen($person->dni)<>8){
                                $columna9='-';
                            }else{
                                $columna9=$person->dni;
                            }
                        }else{
                            $columna9='-';
                        }
                        
                    }else{
                        $columna8=6;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        $columna9=$empresa->ruc;
                    }
                    //$columna9='00000000';
                    
                    if ($request->input('documento') == '5') {
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                            $columna101=trim($person->direccion);
                        }else{
                           $columna10=trim($venta->nombrepaciente);//Razon social
                            $columna101=trim('-');
                        }
                        
                    }else{
                        $columna10=trim($empresa->bussinesname);//Razon social
                        $columna101=trim($empresa->direccion);
                    }
                    //if($person->email!=""){
                    //    $columna11=$person->email;
                    //}else{
                        $columna11="-";    
                    //}

                    $subtotal=number_format($montoafecto/1.18,2,'.','');
                    $igv=number_format($montoafecto - $subtotal,2,'.','');


                    $columna12="PEN";
                    $columna13=$subtotal;
                    $columna14='0.00';
                    $columna15='0.00';
                    $columna16="";
                    $columna17=$igv;
                    $columna18='0.00';
                    $columna19='0.00';
                    $columna20=$montoafecto;
                    $columna21=1000;
                    $letras = new EnLetras();
                    $columna22=$letras->ValorEnLetras($columna20, "SOLES" );//letras
                    DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER (
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        razonSocialEmisor,
                        tipoDocumento,
                        serieNumero,
                        fechaEmision,
                        correoEmisor,
                        tipoDocumentoAdquiriente,
                        numeroDocumentoAdquiriente,
                        razonSocialAdquiriente,
                        correoAdquiriente,
                        tipoMoneda,
                        totalValorVentaNetoOpGravadas,
                        totalValorVentaNetoOpNoGravada,
                        totalValorVentaNetoOpExonerada,
                        
                        totalIgv,
                        
                        
                        totalVenta,
                        codigoLeyenda_1,
                        textoLeyenda_1
                        ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                        [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22]);

                    if($abreviatura=="F"){
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            serieNumero,
                            tipoDocumento,
                            clave,
                            valor) 
                            values (?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);
                    }else{
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            serieNumero,
                            tipoDocumento,
                            clave,
                            valor) 
                            values (?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna5, $columna4, 'lugarDestino', $columna101]);
                    }
                      //---
                    
                    //Array Insert Detalle Facturacion

                    for($c=0;$c<count($arr);$c++){

                        $producto = Producto::find($arr[$c]['producto_id']);
                        if ($producto->afecto == 'SI') {
                            $columnad1=$c+1;
                            $columnad2=$producto->id;
                            $columnad3=$producto->nombre;   
                            
                            $columnad4=$arr[$c]['cantidad'];
                            $columnad5="NIU";
                            $columnad6=round($arr[$c]['precio']/1.18,2);
                            $columnad7=$arr[$c]['precio'];
                            $columnad8="01";
                            $columnad9=round($columnad4*$columnad6,2);
                            $columnad10="10";
                            $columnad11=round($columnad9*0.18,2);
                            $columnad12='0.00';
                            $columnad13='0.00';
                            DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            tipoDocumento,
                            serieNumero,
                            numeroOrdenItem,
                            codigoProducto,
                            descripcion,
                            cantidad,
                            unidadMedida,
                            importeUnitarioSinImpuesto,
                            importeUnitarioConImpuesto,
                            codigoImporteUnitarioConImpues,
                            importeTotalSinImpuesto,
                            codigoRazonExoneracion,
                            importeIgv
                            )
                            values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);
                        }
                        
                    }
                    DB::connection('sqlsrv')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?',
                        ['A',$columna5]);
                        
                    //--

                    // Monto Inafecto
                    if ($request->input('documento') == '5') {
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $person = Person::find($venta2->persona_id);
                        }else{

                        }
                        
                    }else{
                        $empresa = Person::find($venta2->empresa_id);
                    }
                    
                    $columna1=6;
                    $columna2="20480082673";//RUC HOSPITAL
                    $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                    $columna4=$codigo; // Revisar
                    $columna5=$abreviatura.str_pad($venta2->serie,3,'0',STR_PAD_LEFT).'-'.$venta2->numero;
                    $columna6=date('Y-m-d');
                    $columna7="sistemas@hospitaljuanpablo.pe";
                    if($codigo=="03"){//BOLETA
                        $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            if(strlen($person->dni)<>8){
                                $columna9='00000000';
                            }else{
                                $columna9=$person->dni;
                            }
                        }else{
                            $columna9='00000000';
                        }
                        
                    }else{
                        $columna8=6;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        $columna9=$empresa->ruc;
                    }
                    //$columna9='00000000';
                    
                    if ($request->input('documento') == '5') {
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                            $columna101=trim($person->direccion);
                        }else{
                           $columna10=trim($venta2->nombrepaciente);//Razon social
                            $columna101=trim('-');
                        }
                        
                    }else{
                        $columna10=trim($empresa->bussinesname);//Razon social
                        $columna101=trim($empresa->direccion);
                    }
                    //if($person->email!=""){
                    //    $columna11=$person->email;
                    //}else{
                        $columna11="-";    
                    //}

                    //$subtotal=number_format($montoafecto/1.18,2,'.','');
                    //$igv=number_format($montoafecto - $subtotal,2,'.','');


                    $columna12="PEN";
                    $columna13='0.00';
                    $columna14=$montonoafecto;
                    $columna15='0.00';
                    $columna16="";
                    $columna17='0.00';
                    $columna18='0.00';
                    $columna19='0.00';
                    $columna20=$montonoafecto;
                    $columna21=1000;
                    $letras = new EnLetras();
                    $columna22=$letras->ValorEnLetras($columna20, "SOLES" );//letras
                    DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER (
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        razonSocialEmisor,
                        tipoDocumento,
                        serieNumero,
                        fechaEmision,
                        correoEmisor,
                        tipoDocumentoAdquiriente,
                        numeroDocumentoAdquiriente,
                        razonSocialAdquiriente,
                        correoAdquiriente,
                        tipoMoneda,
                        totalValorVentaNetoOpGravadas,
                        totalValorVentaNetoOpNoGravada,
                        totalValorVentaNetoOpExonerada,
                        
                        totalIgv,
                        
                        
                        totalVenta,
                        codigoLeyenda_1,
                        textoLeyenda_1
                        ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                        [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22]);

                    if($abreviatura=="F"){
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            serieNumero,
                            tipoDocumento,
                            clave,
                            valor) 
                            values (?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);
                    }else{
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            serieNumero,
                            tipoDocumento,
                            clave,
                            valor) 
                            values (?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna5, $columna4, 'lugarDestino', $columna101]);
                    }
                    //---
                    
                    //Array Insert Detalle Facturacion

                    for($c=0;$c<count($arr);$c++){

                        $producto = Producto::find($arr[$c]['producto_id']);
                        if ($producto->afecto == 'NO') {
                            $columnad1=$c+1;
                            $columnad2=$producto->id;
                            $columnad3=$producto->nombre;   
                            
                            $columnad4=$arr[$c]['cantidad'];
                            $columnad5="NIU";
                            $columnad6=$arr[$c]['precio'];
                            $columnad7=$arr[$c]['precio'];
                            $columnad8="01";
                            $columnad9=round($columnad4*$columnad6,2);
                            $columnad10="30";
                            $columnad11='0.00';
                            $columnad12='0.00';
                            $columnad13='0.00';
                            DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            tipoDocumento,
                            serieNumero,
                            numeroOrdenItem,
                            codigoProducto,
                            descripcion,
                            cantidad,
                            unidadMedida,
                            importeUnitarioSinImpuesto,
                            importeUnitarioConImpuesto,
                            codigoImporteUnitarioConImpues,
                            importeTotalSinImpuesto,
                            codigoRazonExoneracion,
                            importeIgv
                            )
                            values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);
                        }
                        
                    }
                    DB::connection('sqlsrv')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?',
                        ['A',$columna5]);
                    
                }else{
                    //Array Insert facturacion
                    if($montonoafecto>0){
                        $venta=$venta2;
                    }
                    if ($request->input('documento') == '5') {
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $person = Person::find($venta->persona_id);
                        }else{

                        }
                        
                    }else{
                        $empresa = Person::find($venta->empresa_id);
                    }
                    
                    $columna1=6;
                    $columna2="20480082673";//RUC HOSPITAL
                    $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                    $columna4=$codigo; // Revisar
                    $columna5=$abreviatura.str_pad($venta->serie,3,'0',STR_PAD_LEFT).'-'.$venta->numero;
                    $columna6=date('Y-m-d');
                    $columna7="sistemas@hospitaljuanpablo.pe";
                    if($codigo=="03"){//BOLETA
                        $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            if(strlen($person->dni)<>8){
                                $columna9='00000000';
                            }else{
                                $columna9=$person->dni;
                            }
                        }else{
                            $columna9='00000000';
                        }
                        
                    }else{
                        $columna8=6;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        $columna9=$empresa->ruc;
                    }
                    //$columna9='00000000';
                    
                    if ($request->input('documento') == '5') {
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                            $columna101=trim($person->direccion);
                        }else{
                           $columna10=trim($venta->nombrepaciente);//Razon social
                            $columna101=trim('-');
                        }
                        
                    }else{
                        $columna10=trim($empresa->bussinesname);//Razon social
                        $columna101=trim($empresa->direccion);
                    }
                    //if($person->email!=""){
                    //    $columna11=$person->email;
                    //}else{
                        $columna11="-";    
                    //}
                    $columna12="PEN";
                    if($venta->igv>0){
                        $columna13=$venta->subtotal;
                        $columna14='0.00';
                        $columna15='0.00';
                    }else{
                        $columna13='0.00';
                        $columna14=$venta->subtotal;
                        $columna15='0.00';
                    }
                    $columna16="";
                    $columna17=$venta->igv;
                    $columna18='0.00';
                    $columna19='0.00';
                    $columna20=$venta->total;
                    $columna21=1000;
                    $letras = new EnLetras();
                    $columna22=$letras->ValorEnLetras($columna20, "SOLES" );//letras
                    DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER (
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        razonSocialEmisor,
                        tipoDocumento,
                        serieNumero,
                        fechaEmision,
                        correoEmisor,
                        tipoDocumentoAdquiriente,
                        numeroDocumentoAdquiriente,
                        razonSocialAdquiriente,
                        correoAdquiriente,
                        tipoMoneda,
                        totalValorVentaNetoOpGravadas,
                        totalValorVentaNetoOpNoGravada,
                        totalValorVentaNetoOpExonerada,
                        
                        totalIgv,
                        
                        
                        totalVenta,
                        codigoLeyenda_1,
                        textoLeyenda_1
                        ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                        [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22]);

                    if($abreviatura=="F"){
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            serieNumero,
                            tipoDocumento,
                            clave,
                            valor) 
                            values (?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);
                    }else{
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            serieNumero,
                            tipoDocumento,
                            clave,
                            valor) 
                            values (?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna5, $columna4, 'lugarDestino', $columna101]);
                    }
                    //---
                    
                    //Array Insert Detalle Facturacion

                    for($c=0;$c<count($arr);$c++){
                        $columnad1=$c+1;
                        $producto = Producto::find($arr[$c]['producto_id']);
                        $columnad2=$producto->id;
                        $columnad3=$producto->nombre;   
                        
                        $columnad4=$arr[$c]['cantidad'];
                        $columnad5="NIU";
                        if($venta->igv>0){
                            $columnad6=round($arr[$c]['precio']/1.18,2);
                            $columnad7=$arr[$c]['precio'];
                            $columnad8="01";
                            $columnad9=round($columnad4*$columnad6,2);
                            $columnad10="10";
                            $columnad11=round($columnad9*0.18,2);
                        }else{
                            $columnad6=$arr[$c]['precio'];
                            $columnad7=$arr[$c]['precio'];
                            $columnad8="01";
                            $columnad9=round($columnad4*$columnad6,2);
                            $columnad10="30";
                            $columnad11='0.00';
                        }
                        $columnad12='0.00';
                        $columnad13='0.00';
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        tipoDocumento,
                        serieNumero,
                        numeroOrdenItem,
                        codigoProducto,
                        descripcion,
                        cantidad,
                        unidadMedida,
                        importeUnitarioSinImpuesto,
                        importeUnitarioConImpuesto,
                        codigoImporteUnitarioConImpues,
                        importeTotalSinImpuesto,
                        codigoRazonExoneracion,
                        importeIgv
                        )
                        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);
                    }
                    DB::connection('sqlsrv')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?',
                        ['A',$columna5]);
                        
                    //--

                }
                
            }*/

                $guia = 'NO';
                
                if ($ind == 0) {
                    if ($venta->conveniofarmacia_id !== null) {
                        if ($venta->copago == 0) {
                            $guia = 'SI';
                        }
                    }
                    $dat[0]=array("respuesta"=>"OK","venta_id"=>$venta->id, "ind" => $ind, "second_id" => 0, "guia" => $guia);
                }else{
                    if ($montoafecto > 0) {
                        if ($venta->conveniofarmacia_id !== null) {
                            if ($venta->copago == 0) {
                                $guia = 'SI';
                            }
                        }
                        $dat[0]=array("respuesta"=>"OK","venta_id"=>$venta->id, "ind" => $ind, "second_id" => $venta2->id, "guia" => $guia);
                    }else{
                        $ind = 0;
                        if ($venta2->conveniofarmacia_id !== null) {
                            if ($venta2->copago == 0) {
                                $guia = 'SI';
                            }
                        }
                        $dat[0]=array("respuesta"=>"OK","venta_id"=>$venta2->id, "ind" => $ind, "second_id" => $venta2->id, "guia" => $guia);
                    }
                    
                }
            }
                


        });
        return is_null($error) ? json_encode($dat) : $error;

    }

    public function store2(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                //'person_id' => 'required|integer|exists:person,id,deleted_at,NULL',
                'numerodocumento'                  => 'required',
                'fecha'                 => 'required'
                );
        $mensajes = array(
            //'person_id.required'         => 'Debe ingresar un cliente',
            'numerodocumento.required'         => 'Debe ingresar un numero de documento',
            'fecha.required'         => 'Debe ingresar fecha'
            );

        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }

        $dat=array();
        $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',4)->orderBy('movimiento.id','DESC')->limit(1)->first();
        if(count($rst)==0){
            $conceptopago_id=2;
        }else{
            $conceptopago_id=$rst->conceptopago_id;
        }
        if($conceptopago_id==2){
            $dat[0]=array("respuesta"=>"ERROR","msg"=>"Caja cerrada");
            return json_encode($dat);
        }

        $error = DB::transaction(function() use($request,&$dat){
        $validar = Venta::where('serie','=','4')->where('manual','like','N')->where('tipodocumento_id','=',$request->input('documento'))->where('numero','=',$request->input('numerodocumento'))->first();
        if ($validar == null) {
            $ind = 0;
            $montoafecto = 0;
            $montonoafecto = 0;
            $arr=explode(",",$request->input('listProducto'));
            for($c=0;$c<count($arr);$c++){
                $producto = Producto::find($request->input('txtIdProducto'.$arr[$c]));
                $cantidad  = str_replace(',', '',$request->input('txtCantidad'.$arr[$c]));
                $precio    = str_replace(',', '',$request->input('txtPrecio'.$arr[$c]));
                $subtotal  = round(($cantidad*$precio), 2);
                if($request->input('tipoventa')=='C'){
                    $descuentokayros=$request->input('descuentokayros');
                    $copago=$request->input('copago');
                    $precioaux = $precio - ($precio*($descuentokayros/100));
                    $dscto = round(($precioaux*$cantidad),2);
                    $subtotal = round(($dscto*($copago/100)),2);
                }
                if ($producto->afecto == 'NO') {
                    $ind = 1;
                    $montonoafecto = $montonoafecto+$subtotal;
                }else{
                    $montoafecto = $montoafecto+$subtotal;
                }
            }

            if ($ind == 0) {
                $total = str_replace(',', '', $request->input('total'));
                $venta                 = new Venta();
                $venta->serie = '004';
                $venta->tipodocumento_id          = $request->input('documento');
                if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                    $venta->persona_id = $request->input('person_id');
                }else{
                    $venta->nombrepaciente = $request->input('nombrepersona');
                }
                if ($request->input('documento') == '5' || $request->input('documento') == '14' ) {
                    $codigo="03";
                    $abreviatura="B";
                    
                    
                }else{
                    $codigo="01";
                    $abreviatura="F";
                    $venta->empresa_id = $request->input('empresa_id');
                }
                $venta->tipomovimiento_id          = 4;
                $venta->almacen_id          = 1;
                
                //NUMERACION
                $numeracion = Numeracion::where('tipomovimiento_id','=',4)->where('tipodocumento_id','=',$request->input('documento'))->where('serie','=',4)->first();
                $numeracion->numero = $numeracion->numero + 1;
                $numeracion->save();
                //

                $venta->numero = $request->input('numerodocumento');
                $venta->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                $venta->subtotal=number_format($total/1.18,2,'.','');
                $venta->igv=number_format($total - $venta->subtotal,2,'.','');
                $venta->total = $total;
                $venta->credito = $request->input('credito');
                $venta->tipoventa = $request->input('tipoventa');
                $venta->formapago = $request->input('formapago');
                if($request->input('formapago')=="T"){
                    $venta->tarjeta=$request->input('tipotarjeta');//VISA/MASTER
                    $venta->tipotarjeta=$request->input('tipotarjeta2');//DEBITO/CREDITO
                }
                if ($request->input('tipoventa') == 'C') {
                    $venta->conveniofarmacia_id = $request->input('conveniofarmacia_id');
                    $venta->descuentokayros = $request->input('descuentokayros');
                    $venta->copago = $request->input('copago');
                }
                       
                $venta->inicial = 'N';
                $venta->estadopago = 'P';
                $venta->ventafarmacia = 'S';
                $venta->manual='N';
                $venta->descuentoplanilla = $request->input('descuentoplanilla');
                if($request->input('descuentoplanilla')=="SI"){
                    $venta->personal_id=$request->input('personal_id');
                }
                if ($request->input('formapago')=="P") {
                    $venta->estadopago = 'PP';
                }
                
                $user = Auth::user();
                $venta->responsable_id = $user->person_id;
                $venta->doctor_id = Libreria::obtenerParametro($request->input('doctor_id'));
                //$venta->numeroficha = Libreria::obtenerParametro($request->input('numeroficha'));
                //$venta->cajaprueba = $request->input('cajafamarcia');
                $venta->save();
                $movimiento_id = $venta->id;
                $arr=explode(",",$request->input('listProducto'));
                for($c=0;$c<count($arr);$c++){
                    $producto = Producto::find($request->input('txtIdProducto'.$arr[$c]));
                    $cantidad  = str_replace(',', '',$request->input('txtCantidad'.$arr[$c]));
                    $precio    = str_replace(',', '',$request->input('txtPrecio'.$arr[$c]));
                    $subtotal  = round(($cantidad*$precio), 2);
                    $detalleVenta = new Detallemovimiento();
                    $detalleVenta->cantidad = $cantidad;
                    $detalleVenta->precio = $precio;
                    $detalleVenta->subtotal = $subtotal;
                    $detalleVenta->movimiento_id = $movimiento_id;
                    $detalleVenta->producto_id = $request->input('txtIdProducto'.$arr[$c]);
                    if($request->input('txtIdUnspsc'.$arr[$c]) > 0){
                        $detalleVenta->idunspsc = $request->input('txtIdUnspsc'.$arr[$c]);
                    }else{
                        $detalleVenta->idunspsc =  $producto->id_unspsc;
                    }
                    $detalleVenta->save();
                    if ($producto->afecto == 'NO') {
                        $ind = 1;
                        
                    }else{
                        
                    }

                    //Stock
                    $stock = Stock::where('producto_id','=',$request->input('txtIdProducto'.$arr[$c]))->where('almacen_id','=',1)->first();
                    if(is_null($stock)){
                        $stock = new Stock();
                        $stock->producto_id = $request->input('txtIdProducto'.$arr[$c]);
                        $stock->cantidad = $cantidad*(-1);
                        $stock->almacen_id = 1;
                        $stock->save();
                    }else{
                        $stock->cantidad = $stock->cantidad - $cantidad;
                        $stock->save();
                    }
                    //
                    
                    
                    // consulta lotes
                    $lotes = Lote::where('producto_id','=',$request->input('txtIdProducto'.$arr[$c]))->where('queda','>','0')->orderBy('fechavencimiento','ASC')->get();
                    $aux = $request->input('txtCantidad'.$arr[$c]);
                    foreach ($lotes as $key => $value) {
                        if ($value->queda >= $aux) {
                            $queda = $value->queda-$aux;
                            $value->queda = $queda;
                            $value->save();
                            $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $request->input('txtIdProducto'.$arr[$c]))->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                            $stockanterior = 0;
                            $stockactual = 0;
                            // ingresamos nuevo kardex
                            if ($ultimokardex === NULL) {
                                
                                
                            }else{
                                $stockanterior = $ultimokardex->stockactual;
                                $stockactual = $ultimokardex->stockactual-$aux;
                                $kardex = new Kardex();
                                $kardex->tipo = 'S';
                                $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                                $kardex->stockanterior = $stockanterior;
                                $kardex->stockactual = $stockactual;
                                $kardex->cantidad = $aux;
                                $kardex->precioventa = $precio;
                                //$kardex->almacen_id = 1;
                                $kardex->detallemovimiento_id = $detalleVenta->id;
                                $kardex->lote_id = $value->id;
                                $kardex->save();    

                            }
                            break;
                        }else{
                            $cantvendida = $value->queda;
                            $aux = $aux-$value->queda;
                            $value->queda = 0;
                            $value->save();
                            
                            $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $request->input('txtIdProducto'.$arr[$c]))->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                            $stockanterior = 0;
                            $stockactual = 0;
                            // ingresamos nuevo kardex
                            if ($ultimokardex === NULL) {
                                
                                
                            }else{
                                $stockanterior = $ultimokardex->stockactual;
                                $stockactual = $ultimokardex->stockactual-$cantvendida;
                                $kardex = new Kardex();
                                $kardex->tipo = 'S';
                                $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                                $kardex->stockanterior = $stockanterior;
                                $kardex->stockactual = $stockactual;
                                $kardex->cantidad = $cantvendida;
                                $kardex->precioventa = $precio;
                                //$kardex->almacen_id = 1;
                                $kardex->detallemovimiento_id = $detalleVenta->id;
                                $kardex->lote_id = $value->id;
                                $kardex->save();    

                            }
                        }
                    }

                   

                }

                # REGISTRO DE CREDITOS
                
                if ($request->input('formapago') == 'P') {
                    
                }else{

                    if ( ($request->input('documento') == 15 && $venta->copago > 0 ) || ($request->input('documento') != 15)) {
                        $total = str_replace(',', '', $request->input('total'));
                        $movimiento                 = new Movimiento();
                        $movimiento->tipodocumento_id          = $request->input('documento');
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $movimiento->persona_id = $request->input('person_id');
                        }else{
                            $movimiento->nombrepaciente = $request->input('nombrepersona');
                        }
                        if ($request->input('documento') == '5') {
                            if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                                $movimiento->persona_id = $request->input('person_id');
                            }else{
                                $movimiento->nombrepaciente = $request->input('nombrepersona');
                            }
                            
                        }else{
                            $movimiento->empresa_id = $request->input('empresa_id');
                        }
                        $movimiento->tipomovimiento_id          = 2;
                        $movimiento->serie = '004';
                        $movimiento->numero = $request->input('numerodocumento');
                        $movimiento->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                        $movimiento->total = $total;
                        
                        $user = Auth::user();
                        $movimiento->responsable_id = $user->person_id;
                        $movimiento->conceptopago_id = 3;
                        $movimiento->caja_id = 4;
                        $movimiento->movimiento_id = $venta->id;
                        if($request->input('formapago')=="T"){
                            $movimiento->tipotarjeta=$request->input('tipotarjeta');
                            $movimiento->tarjeta=$request->input('tipotarjeta2');
                            $movimiento->voucher=$request->input('nroref');
                            $movimiento->totalpagado=0;
                        }else{
                            $movimiento->totalpagado=$total;
                        }
                        
                        $movimiento->save();

                        $venta->movimiento_id = $movimiento->id;
                        $venta->save();


                        $movimientocaja = new Detallemovcaja();
                        if ($request->input('documento') == '5') {
                            if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                                $movimientocaja->persona_id = $request->input('person_id');
                            }else{
                                $movimientocaja->nombrepaciente = $request->input('nombrepersona');
                            }
                            
                        }else{
                            $movimientocaja->empresa_id = $request->input('empresa_id');
                        }
                        //$movimientocaja->persona_id = $request->input('persona_id');
                        $movimientocaja->movimiento_id = $movimiento->id;
                        $aperturacierrecaja = Aperturacierrecaja::where('estado','=','A')->first();
                        $movimientocaja->aperturacierrecaja_id = $aperturacierrecaja->id;
                        $movimientocaja->descripcion = 'PAGO DE CLIENTE';
                        $movimientocaja->save();
                    }
                        
                }

            }else{
                // Para Monto Afecto
                if ($montoafecto > 0) {

                    $total = str_replace(',', '', $request->input('total'));
                    $venta                 = new Venta();
                    $venta->serie = '004';
                    $venta->tipodocumento_id          = $request->input('documento');
                    if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                        $venta->persona_id = $request->input('person_id');
                    }else{
                        $venta->nombrepaciente = $request->input('nombrepersona');
                    }
                    if ($request->input('documento') == '5' || $request->input('documento') == '14' ) {
                        $codigo="03";
                        $abreviatura="B";
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $venta->persona_id = $request->input('person_id');
                        }else{
                            $venta->nombrepaciente = $request->input('nombrepersona');
                        }
                        
                    }else{
                        $codigo="01";
                        $abreviatura="F";
                        $venta->empresa_id = $request->input('empresa_id');
                    }
                    $venta->tipomovimiento_id          = 4;
                    $venta->almacen_id          = 1;
                    
                    //NUMERACION
                    $numeracion = Numeracion::where('tipomovimiento_id','=',4)->where('tipodocumento_id','=',$request->input('documento'))->where('serie','=',4)->first();
                    $numeracion->numero = $numeracion->numero + 1;
                    $numeracion->save();
                    //
                    
                    $venta->numero = $request->input('numerodocumento');
                    $venta->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                    $venta->subtotal=number_format($montoafecto/1.18,2,'.','');
                    $venta->igv=number_format($montoafecto - $venta->subtotal,2,'.','');
                    $venta->total = $montoafecto;
                    $venta->credito = $request->input('credito');
                    $venta->tipoventa = $request->input('tipoventa');
                    $venta->formapago = $request->input('formapago');
                    if($request->input('formapago')=="Tarjeta"){
                        $venta->tarjeta=$request->input('tipotarjeta');//VISA/MASTER
                        $venta->tipotarjeta=$request->input('tipotarjeta2');//DEBITO/CREDITO
                        $venta->voucher=$request->input('nroref');
                    }
                    if ($request->input('tipoventa') == 'C') {
                        $venta->conveniofarmacia_id = $request->input('conveniofarmacia_id');
                        $venta->descuentokayros = $request->input('descuentokayros');
                        $venta->copago = $request->input('copago');
                    }
                           
                    $venta->inicial = 'N';
                    $venta->estadopago = 'P';
                    $venta->ventafarmacia = 'S';
                    $venta->manual='N';
                    $venta->descuentoplanilla = $request->input('descuentoplanilla');
                    if($request->input('descuentoplanilla')=="SI"){
                        $venta->personal_id=$request->input('personal_id');
                    }
                    if ($request->input('credito') == 'S') {
                        $venta->estadopago = 'PP';
                    }
                    
                    $user = Auth::user();
                    $venta->responsable_id = $user->person_id;
                    $venta->doctor_id = Libreria::obtenerParametro($request->input('doctor_id'));
                    //$venta->numeroficha = Libreria::obtenerParametro($request->input('numeroficha'));
                    //$venta->cajaprueba = $request->input('cajafamarcia');
                    $venta->save();
                    $movimiento_id = $venta->id;
                    $arr=explode(",",$request->input('listProducto'));
                    for($c=0;$c<count($arr);$c++){
                         $producto = Producto::find($request->input('txtIdProducto'.$arr[$c]));
                        if ($producto->afecto != 'NO') {
                            $cantidad  = str_replace(',', '',$request->input('txtCantidad'.$arr[$c]));
                            $precio    = str_replace(',', '',$request->input('txtPrecio'.$arr[$c]));
                            $subtotal  = round(($cantidad*$precio), 2);
                            $detalleVenta = new Detallemovimiento();
                            $detalleVenta->cantidad = $cantidad;
                            $detalleVenta->precio = $precio;
                            $detalleVenta->subtotal = $subtotal;
                            $detalleVenta->movimiento_id = $movimiento_id;
                            $detalleVenta->producto_id = $request->input('txtIdProducto'.$arr[$c]);
                            if($request->input('txtIdUnspsc'.$arr[$c]) > 0){
                                $detalleVenta->idunspsc = $request->input('txtIdUnspsc'.$arr[$c]);
                            }else{
                                $detalleVenta->idunspsc =  $producto->id_unspsc;
                            }
                            $detalleVenta->save();
                           

                            //Stock
                            $stock = Stock::where('producto_id','=',$request->input('txtIdProducto'.$arr[$c]))->where('almacen_id','=',1)->first();
                            if(is_null($stock)){
                                $stock = new Stock();
                                $stock->producto_id = $request->input('txtIdProducto'.$arr[$c]);
                                $stock->cantidad = $cantidad*(-1);
                                $stock->almacen_id = 1;
                                $stock->save();
                            }else{
                                $stock->cantidad = $stock->cantidad - $cantidad;
                                $stock->save();
                            }
                            //

                            // consulta lotes
                            $lotes = Lote::where('producto_id','=',$request->input('txtIdProducto'.$arr[$c]))->where('queda','>','0')->orderBy('fechavencimiento','ASC')->get();
                            $aux = $request->input('txtCantidad'.$arr[$c]);// 3
                            foreach ($lotes as $key => $value) { 
                                if ($value->queda >= $aux) {
                                    $queda = $value->queda-$aux;
                                    $value->queda = $queda;
                                    $value->save();

                                    $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $request->input('txtIdProducto'.$arr[$c]))->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                                    $stockanterior = 0;
                                    $stockactual = 0;
                                    // ingresamos nuevo kardex
                                    if ($ultimokardex === NULL) {
                                        
                                        
                                    }else{
                                        $stockanterior = $ultimokardex->stockactual;
                                        $stockactual = $ultimokardex->stockactual-$aux;
                                        $kardex = new Kardex();
                                        $kardex->tipo = 'S';
                                        $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                                        $kardex->stockanterior = $stockanterior;
                                        $kardex->stockactual = $stockactual;
                                        $kardex->cantidad = $aux;
                                        $kardex->precioventa = $precio;
                                        //$kardex->almacen_id = 1;
                                        $kardex->detallemovimiento_id = $detalleVenta->id;
                                        $kardex->lote_id = $value->id;
                                        $kardex->save();    

                                    }
                                    break;
                                }else{
                                    $cantvendida = $value->queda;
                                    $aux = $aux-$value->queda;
                                    $value->queda = 0;
                                    $value->save();
                                    
                                    $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $request->input('txtIdProducto'.$arr[$c]))->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                                    $stockanterior = 0;
                                    $stockactual = 0;
                                    // ingresamos nuevo kardex
                                    if ($ultimokardex === NULL) {
                                        
                                        
                                    }else{
                                        $stockanterior = $ultimokardex->stockactual;
                                        $stockactual = $ultimokardex->stockactual-$cantvendida;
                                        $kardex = new Kardex();
                                        $kardex->tipo = 'S';
                                        $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                                        $kardex->stockanterior = $stockanterior;
                                        $kardex->stockactual = $stockactual;
                                        $kardex->cantidad = $cantvendida;
                                        $kardex->precioventa = $precio;
                                        //$kardex->almacen_id = 1;
                                        $kardex->detallemovimiento_id = $detalleVenta->id;
                                        $kardex->lote_id = $value->id;
                                        $kardex->save();    

                                    }
                                }
                            }

                        }
                    }

                    # REGISTRO DE CREDITOS
                    
                    if ($request->input('formapago') == 'P') {
                        
                    }else{

                        if ( ($request->input('documento') == 15 && $venta->copago > 0) || ($request->input('documento') != 15)) {
                            $total = str_replace(',', '', $request->input('total'));
                            $movimiento                 = new Movimiento();
                            $movimiento->tipodocumento_id          = $request->input('documento');
                            if ($request->input('documento') == '5') {
                                if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                                    $movimiento->persona_id = $request->input('person_id');
                                }else{
                                    $movimiento->nombrepaciente = $request->input('nombrepersona');
                                }
                                
                            }else{
                                $movimiento->empresa_id = $request->input('empresa_id');
                            }
                            $movimiento->tipomovimiento_id          = 2;
                            
                            $movimiento->numero = $request->input('numerodocumento');
                            $movimiento->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                            $movimiento->total = $montoafecto;
                            
                            $user = Auth::user();
                            $movimiento->responsable_id = $user->person_id;
                            $movimiento->conceptopago_id = 3;
                            $movimiento->caja_id = 4;
                            $movimiento->movimiento_id = $venta->id;
                            if($request->input('formapago')=="T"){
                                $movimiento->tipotarjeta=$request->input('tipotarjeta');
                                $movimiento->tarjeta=$request->input('tipotarjeta2');
                                $movimiento->voucher=$request->input('nroref');
                                $movimiento->totalpagado=0;
                            }else{
                                $movimiento->totalpagado=$request->input('total',0);
                            }
                            
                            $movimiento->save();

                            $venta->movimiento_id = $movimiento->id;
                            $venta->save();

                            $movimientocaja = new Detallemovcaja();
                            if ($request->input('documento') == '5') {
                                if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                                    $movimientocaja->persona_id = $request->input('person_id');
                                }else{
                                    $movimientocaja->nombrepaciente = $request->input('nombrepersona');
                                }
                                
                            }else{
                                $movimientocaja->empresa_id = $request->input('empresa_id');
                            }
                            //$movimientocaja->persona_id = $request->input('persona_id');
                            $movimientocaja->movimiento_id = $movimiento->id;
                            $movimientocaja->descripcion = 'PAGO DE CLIENTE';
                            $movimientocaja->save();
                        }
                            
                    }

                }

                // Para Monto Inafecto

                $total = str_replace(',', '', $request->input('total'));
                $venta2                 = new Venta();
                $venta2->serie = '004';
                $venta2->tipodocumento_id          = $request->input('documento');
                if ($request->input('documento') == '5' || $request->input('documento') == '14' ) {
                    $codigo="03";
                    $abreviatura="B";
                    if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                        $venta2->persona_id = $request->input('person_id');
                    }else{
                        $venta2->nombrepaciente = $request->input('nombrepersona');
                    }
                    
                }else{
                    $codigo="01";
                    $abreviatura="F";
                    $venta2->empresa_id = $request->input('empresa_id');
                    if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                        $venta2->persona_id = $request->input('person_id');
                    }else{
                        $venta2->nombrepaciente = $request->input('nombrepersona');
                    }
                }
                $venta2->tipomovimiento_id          = 4;
                $venta2->almacen_id          = 1;
                
                //NUMERACION
                $numeracion = Numeracion::where('tipomovimiento_id','=',4)->where('tipodocumento_id','=',$request->input('documento'))->where('serie','=',4)->first();
                $numeracion->numero = $numeracion->numero + 1;
                $numeracion->save();
                //
                //$venta2->numero = Movimiento::NumeroSigue(4,$request->input('documento'),4,'N');
                $venta2->numero = $numeracion->numero;
                $venta2->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                $venta2->subtotal=number_format($montonoafecto,2,'.','');
                $venta2->igv=0;
                $venta2->total = $montonoafecto;
                $venta2->credito = $request->input('credito');
                $venta2->tipoventa = $request->input('tipoventa');
                $venta2->formapago = $request->input('formapago');
                if($request->input('formapago')=="T"){
                    $venta2->tarjeta=$request->input('tipotarjeta');//VISA/MASTER
                    $venta2->tipotarjeta=$request->input('tipotarjeta2');//DEBITO/CREDITO
                }
                if ($request->input('tipoventa') == 'C') {
                    $venta2->conveniofarmacia_id = $request->input('conveniofarmacia_id');
                    $venta2->descuentokayros = $request->input('descuentokayros');
                    $venta2->copago = $request->input('copago');
                }
                       
                $venta2->inicial = 'N';
                $venta2->manual='N';
                $venta2->estadopago = 'P';
                $venta2->ventafarmacia = 'S';
                if ($request->input('credito') == 'S') {
                    $venta2->estadopago = 'PP';
                }
                
                $user = Auth::user();
                $venta2->responsable_id = $user->person_id;
                $venta2->doctor_id = Libreria::obtenerParametro($request->input('doctor_id'));
                //$venta2->numeroficha = Libreria::obtenerParametro($request->input('numeroficha'));
                //$venta2->cajaprueba = $request->input('cajafamarcia');
                $venta2->save();
                $movimiento_id = $venta2->id;
                $arr=explode(",",$request->input('listProducto'));
                for($c=0;$c<count($arr);$c++){
                    $producto = Producto::find($request->input('txtIdProducto'.$arr[$c]));
                    if ($producto->afecto == 'NO') {
                        $cantidad  = str_replace(',', '',$request->input('txtCantidad'.$arr[$c]));
                        $precio    = str_replace(',', '',$request->input('txtPrecio'.$arr[$c]));
                        $subtotal  = round(($cantidad*$precio), 2);
                        $detalleVenta = new Detallemovimiento();
                        $detalleVenta->cantidad = $cantidad;
                        $detalleVenta->precio = $precio;
                        $detalleVenta->subtotal = $subtotal;
                        $detalleVenta->movimiento_id = $movimiento_id;
                        $detalleVenta->producto_id = $request->input('txtIdProducto'.$arr[$c]);
                        if($request->input('txtIdUnspsc'.$arr[$c]) > 0){
                            $detalleVenta->idunspsc = $request->input('txtIdUnspsc'.$arr[$c]);
                        }else{
                            $detalleVenta->idunspsc =  $producto->id_unspsc;
                        }
                        $detalleVenta->save();
                        
                        //Stock
                        $stock = Stock::where('producto_id','=',$request->input('txtIdProducto'.$arr[$c]))->where('almacen_id','=',1)->first();
                        if(is_null($stock)){
                            $stock = new Stock();
                            $stock->producto_id = $request->input('txtIdProducto'.$arr[$c]);
                            $stock->cantidad = $cantidad*(-1);
                            $stock->almacen_id = 1;
                            $stock->save();
                        }else{
                            $stock->cantidad = $stock->cantidad - $cantidad;
                            $stock->save();
                        }
                        //


                        // consulta lotes
                        $lotes = Lote::where('producto_id','=',$request->input('txtIdProducto'.$arr[$c]))->where('queda','>','0')->orderBy('fechavencimiento','ASC')->get();

                        //$aux = $lista[$i]['cantidad'];
                        $aux = $request->input('txtCantidad'.$arr[$c]);// 3
                        foreach ($lotes as $key => $value) {
                            if ($value->queda >= $aux) {
                                $queda = $value->queda-$aux;
                                $value->queda = $queda;
                                $value->save();
                                $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $request->input('txtIdProducto'.$arr[$c]))->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                                $stockanterior = 0;
                                $stockactual = 0;
                                // ingresamos nuevo kardex
                                if ($ultimokardex === NULL) {
                                    
                                    
                                }else{
                                    $stockanterior = $ultimokardex->stockactual;
                                    $stockactual = $ultimokardex->stockactual-$aux;
                                    $kardex = new Kardex();
                                    $kardex->tipo = 'S';
                                    $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                                    $kardex->stockanterior = $stockanterior;
                                    $kardex->stockactual = $stockactual;
                                    $kardex->cantidad = $aux;
                                    $kardex->precioventa = $precio;
                                    //$kardex->almacen_id = 1;
                                    $kardex->detallemovimiento_id = $detalleVenta->id;
                                    $kardex->lote_id = $value->id;
                                    $kardex->save();    

                                }
                                break;
                            }else{
                                $cantvendida = $value->queda;
                                $aux = $aux-$value->queda;
                                $value->queda = 0;
                                $value->save();
                                
                                $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $request->input('txtIdProducto'.$arr[$c]))->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                                $stockanterior = 0;
                                $stockactual = 0;
                                // ingresamos nuevo kardex
                                if ($ultimokardex === NULL) {
                                    
                                    
                                }else{
                                    $stockanterior = $ultimokardex->stockactual;
                                    $stockactual = $ultimokardex->stockactual-$cantvendida;
                                    $kardex = new Kardex();
                                    $kardex->tipo = 'S';
                                    $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                                    $kardex->stockanterior = $stockanterior;
                                    $kardex->stockactual = $stockactual;
                                    $kardex->cantidad = $cantvendida;
                                    $kardex->precioventa = $precio;
                                    //$kardex->almacen_id = 1;
                                    $kardex->detallemovimiento_id = $detalleVenta->id;
                                    $kardex->lote_id = $value->id;
                                    $kardex->save();    

                                }
                            }
                        }
                    }
                }

                # REGISTRO DE CREDITOS
                
                if ($request->input('formapago') == 'P') {
                    
                }else{

                    if ( ($request->input('documento') == 15 && $venta2->copago > 0 ) || ($request->input('documento') != 15)) {
                        $total = str_replace(',', '', $request->input('total'));
                        $movimiento                 = new Movimiento();
                        $movimiento->tipodocumento_id          = $request->input('documento');
                        if ($request->input('documento') == '5') {
                            if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                                $movimiento->persona_id = $request->input('person_id');
                            }else{
                                $movimiento->nombrepaciente = $request->input('nombrepersona');
                            }
                            
                        }else{
                            $movimiento->empresa_id = $request->input('empresa_id');
                        }
                        $movimiento->tipomovimiento_id          = 2;
                        
                        $movimiento->numero = $request->input('numerodocumento');
                        $movimiento->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                        $movimiento->total = $montonoafecto;
                        
                        $user = Auth::user();
                        $movimiento->responsable_id = $user->person_id;
                        $movimiento->conceptopago_id = 3;
                        $movimiento->caja_id = 4;
                        $movimiento->movimiento_id = $venta2->id;
                        if($request->input('formapago')=="T"){
                            $movimiento->tipotarjeta=$request->input('tipotarjeta');
                            $movimiento->tarjeta=$request->input('tipotarjeta2');
                            $movimiento->voucher=$request->input('nroref');
                            $movimiento->totalpagado=0;
                        }else{
                            $movimiento->totalpagado=$request->input('total',0);
                        }
                        
                        $movimiento->save();

                        $venta2->movimiento_id = $movimiento->id;
                        $venta2->save();


                        $movimientocaja = new Detallemovcaja();
                        if ($request->input('documento') == '5') {
                            if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                                $movimientocaja->persona_id = $request->input('person_id');
                            }else{
                                $movimientocaja->nombrepaciente = $request->input('nombrepersona');
                            }
                            
                        }else{
                            $movimientocaja->empresa_id = $request->input('empresa_id');
                        }
                        //$movimientocaja->persona_id = $request->input('persona_id');
                        $movimientocaja->movimiento_id = $movimiento->id;
                        $movimientocaja->descripcion = 'PAGO DE CLIENTE';
                        $movimientocaja->save();
                    }
                        
                }

            }
            
            /*if ($request->input('documento') != '15') {
                if($ind == 1 && $montoafecto>0){
                    // Monto Afecto
                    if ($request->input('documento') == '5') {
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $person = Person::find($venta->persona_id);
                        }else{

                        }
                        
                    }else{
                        $empresa = Person::find($venta->empresa_id);
                    }
                    
                    $columna1=6;
                    $columna2="20480082673";//RUC HOSPITAL
                    $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                    $columna4=$codigo; // Revisar
                    $columna5=$abreviatura.str_pad($venta->serie,3,'0',STR_PAD_LEFT).'-'.$venta->numero;
                    $columna6=date('Y-m-d');
                    $columna7="sistemas@hospitaljuanpablo.pe";
                    if($codigo=="03"){//BOLETA
                        $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            if(strlen($person->dni)<>8){
                                $columna9='-';
                            }else{
                                $columna9=$person->dni;
                            }
                        }else{
                            $columna9='-';
                        }
                        
                    }else{
                        $columna8=6;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        $columna9=$empresa->ruc;
                    }
                    //$columna9='00000000';
                    
                    if ($request->input('documento') == '5') {
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                            $columna101=trim($person->direccion);
                        }else{
                           $columna10=trim($venta->nombrepaciente);//Razon social
                            $columna101=trim('-');
                        }
                        
                    }else{
                        $columna10=trim($empresa->bussinesname);//Razon social
                        $columna101=trim($empresa->direccion);
                    }
                    //if($person->email!=""){
                    //    $columna11=$person->email;
                    //}else{
                        $columna11="-";    
                    //}

                    $subtotal=number_format($montoafecto/1.18,2,'.','');
                    $igv=number_format($montoafecto - $subtotal,2,'.','');


                    $columna12="PEN";
                    $columna13=$subtotal;
                    $columna14='0.00';
                    $columna15='0.00';
                    $columna16="";
                    $columna17=$igv;
                    $columna18='0.00';
                    $columna19='0.00';
                    $columna20=$montoafecto;
                    $columna21=1000;
                    $letras = new EnLetras();
                    $columna22=$letras->ValorEnLetras($columna20, "SOLES" );//letras
                    DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER (
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        razonSocialEmisor,
                        tipoDocumento,
                        serieNumero,
                        fechaEmision,
                        correoEmisor,
                        tipoDocumentoAdquiriente,
                        numeroDocumentoAdquiriente,
                        razonSocialAdquiriente,
                        correoAdquiriente,
                        tipoMoneda,
                        totalValorVentaNetoOpGravadas,
                        totalValorVentaNetoOpNoGravada,
                        totalValorVentaNetoOpExonerada,
                        
                        totalIgv,
                        
                        
                        totalVenta,
                        codigoLeyenda_1,
                        textoLeyenda_1
                        ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                        [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22]);

                    if($abreviatura=="F"){
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            serieNumero,
                            tipoDocumento,
                            clave,
                            valor) 
                            values (?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);
                    }else{
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            serieNumero,
                            tipoDocumento,
                            clave,
                            valor) 
                            values (?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna5, $columna4, 'lugarDestino', $columna101]);
                    }
                      //---
                    
                    //Array Insert Detalle Facturacion

                    for($c=0;$c<count($arr);$c++){

                        $producto = Producto::find($arr[$c]['producto_id']);
                        if ($producto->afecto == 'SI') {
                            $columnad1=$c+1;
                            $columnad2=$producto->id;
                            $columnad3=$producto->nombre;   
                            
                            $columnad4=$arr[$c]['cantidad'];
                            $columnad5="NIU";
                            $columnad6=round($arr[$c]['precio']/1.18,2);
                            $columnad7=$arr[$c]['precio'];
                            $columnad8="01";
                            $columnad9=round($columnad4*$columnad6,2);
                            $columnad10="10";
                            $columnad11=round($columnad9*0.18,2);
                            $columnad12='0.00';
                            $columnad13='0.00';
                            DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            tipoDocumento,
                            serieNumero,
                            numeroOrdenItem,
                            codigoProducto,
                            descripcion,
                            cantidad,
                            unidadMedida,
                            importeUnitarioSinImpuesto,
                            importeUnitarioConImpuesto,
                            codigoImporteUnitarioConImpues,
                            importeTotalSinImpuesto,
                            codigoRazonExoneracion,
                            importeIgv
                            )
                            values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);
                        }
                        
                    }
                    DB::connection('sqlsrv')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?',
                        ['A',$columna5]);
                        
                    //--

                    // Monto Inafecto
                    if ($request->input('documento') == '5') {
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $person = Person::find($venta2->persona_id);
                        }else{

                        }
                        
                    }else{
                        $empresa = Person::find($venta2->empresa_id);
                    }
                    
                    $columna1=6;
                    $columna2="20480082673";//RUC HOSPITAL
                    $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                    $columna4=$codigo; // Revisar
                    $columna5=$abreviatura.str_pad($venta2->serie,3,'0',STR_PAD_LEFT).'-'.$venta2->numero;
                    $columna6=date('Y-m-d');
                    $columna7="sistemas@hospitaljuanpablo.pe";
                    if($codigo=="03"){//BOLETA
                        $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            if(strlen($person->dni)<>8){
                                $columna9='00000000';
                            }else{
                                $columna9=$person->dni;
                            }
                        }else{
                            $columna9='00000000';
                        }
                        
                    }else{
                        $columna8=6;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        $columna9=$empresa->ruc;
                    }
                    //$columna9='00000000';
                    
                    if ($request->input('documento') == '5') {
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                            $columna101=trim($person->direccion);
                        }else{
                           $columna10=trim($venta2->nombrepaciente);//Razon social
                            $columna101=trim('-');
                        }
                        
                    }else{
                        $columna10=trim($empresa->bussinesname);//Razon social
                        $columna101=trim($empresa->direccion);
                    }
                    //if($person->email!=""){
                    //    $columna11=$person->email;
                    //}else{
                        $columna11="-";    
                    //}

                    //$subtotal=number_format($montoafecto/1.18,2,'.','');
                    //$igv=number_format($montoafecto - $subtotal,2,'.','');


                    $columna12="PEN";
                    $columna13='0.00';
                    $columna14=$montonoafecto;
                    $columna15='0.00';
                    $columna16="";
                    $columna17='0.00';
                    $columna18='0.00';
                    $columna19='0.00';
                    $columna20=$montonoafecto;
                    $columna21=1000;
                    $letras = new EnLetras();
                    $columna22=$letras->ValorEnLetras($columna20, "SOLES" );//letras
                    DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER (
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        razonSocialEmisor,
                        tipoDocumento,
                        serieNumero,
                        fechaEmision,
                        correoEmisor,
                        tipoDocumentoAdquiriente,
                        numeroDocumentoAdquiriente,
                        razonSocialAdquiriente,
                        correoAdquiriente,
                        tipoMoneda,
                        totalValorVentaNetoOpGravadas,
                        totalValorVentaNetoOpNoGravada,
                        totalValorVentaNetoOpExonerada,
                        
                        totalIgv,
                        
                        
                        totalVenta,
                        codigoLeyenda_1,
                        textoLeyenda_1
                        ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                        [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22]);

                    if($abreviatura=="F"){
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            serieNumero,
                            tipoDocumento,
                            clave,
                            valor) 
                            values (?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);
                    }else{
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            serieNumero,
                            tipoDocumento,
                            clave,
                            valor) 
                            values (?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna5, $columna4, 'lugarDestino', $columna101]);
                    }
                    //---
                    
                    //Array Insert Detalle Facturacion

                    for($c=0;$c<count($arr);$c++){

                        $producto = Producto::find($arr[$c]['producto_id']);
                        if ($producto->afecto == 'NO') {
                            $columnad1=$c+1;
                            $columnad2=$producto->id;
                            $columnad3=$producto->nombre;   
                            
                            $columnad4=$arr[$c]['cantidad'];
                            $columnad5="NIU";
                            $columnad6=$arr[$c]['precio'];
                            $columnad7=$arr[$c]['precio'];
                            $columnad8="01";
                            $columnad9=round($columnad4*$columnad6,2);
                            $columnad10="30";
                            $columnad11='0.00';
                            $columnad12='0.00';
                            $columnad13='0.00';
                            DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            tipoDocumento,
                            serieNumero,
                            numeroOrdenItem,
                            codigoProducto,
                            descripcion,
                            cantidad,
                            unidadMedida,
                            importeUnitarioSinImpuesto,
                            importeUnitarioConImpuesto,
                            codigoImporteUnitarioConImpues,
                            importeTotalSinImpuesto,
                            codigoRazonExoneracion,
                            importeIgv
                            )
                            values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);
                        }
                        
                    }
                    DB::connection('sqlsrv')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?',
                        ['A',$columna5]);
                    
                }else{
                    //Array Insert facturacion
                    if($montonoafecto>0){
                        $venta=$venta2;
                    }
                    if ($request->input('documento') == '5') {
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $person = Person::find($venta->persona_id);
                        }else{

                        }
                        
                    }else{
                        $empresa = Person::find($venta->empresa_id);
                    }
                    
                    $columna1=6;
                    $columna2="20480082673";//RUC HOSPITAL
                    $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                    $columna4=$codigo; // Revisar
                    $columna5=$abreviatura.str_pad($venta->serie,3,'0',STR_PAD_LEFT).'-'.$venta->numero;
                    $columna6=date('Y-m-d');
                    $columna7="sistemas@hospitaljuanpablo.pe";
                    if($codigo=="03"){//BOLETA
                        $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            if(strlen($person->dni)<>8){
                                $columna9='00000000';
                            }else{
                                $columna9=$person->dni;
                            }
                        }else{
                            $columna9='00000000';
                        }
                        
                    }else{
                        $columna8=6;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        $columna9=$empresa->ruc;
                    }
                    //$columna9='00000000';
                    
                    if ($request->input('documento') == '5') {
                        if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                            $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                            $columna101=trim($person->direccion);
                        }else{
                           $columna10=trim($venta->nombrepaciente);//Razon social
                            $columna101=trim('-');
                        }
                        
                    }else{
                        $columna10=trim($empresa->bussinesname);//Razon social
                        $columna101=trim($empresa->direccion);
                    }
                    //if($person->email!=""){
                    //    $columna11=$person->email;
                    //}else{
                        $columna11="-";    
                    //}
                    $columna12="PEN";
                    if($venta->igv>0){
                        $columna13=$venta->subtotal;
                        $columna14='0.00';
                        $columna15='0.00';
                    }else{
                        $columna13='0.00';
                        $columna14=$venta->subtotal;
                        $columna15='0.00';
                    }
                    $columna16="";
                    $columna17=$venta->igv;
                    $columna18='0.00';
                    $columna19='0.00';
                    $columna20=$venta->total;
                    $columna21=1000;
                    $letras = new EnLetras();
                    $columna22=$letras->ValorEnLetras($columna20, "SOLES" );//letras
                    DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER (
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        razonSocialEmisor,
                        tipoDocumento,
                        serieNumero,
                        fechaEmision,
                        correoEmisor,
                        tipoDocumentoAdquiriente,
                        numeroDocumentoAdquiriente,
                        razonSocialAdquiriente,
                        correoAdquiriente,
                        tipoMoneda,
                        totalValorVentaNetoOpGravadas,
                        totalValorVentaNetoOpNoGravada,
                        totalValorVentaNetoOpExonerada,
                        
                        totalIgv,
                        
                        
                        totalVenta,
                        codigoLeyenda_1,
                        textoLeyenda_1
                        ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                        [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22]);

                    if($abreviatura=="F"){
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            serieNumero,
                            tipoDocumento,
                            clave,
                            valor) 
                            values (?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);
                    }else{
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            serieNumero,
                            tipoDocumento,
                            clave,
                            valor) 
                            values (?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna5, $columna4, 'lugarDestino', $columna101]);
                    }
                    //---
                    
                    //Array Insert Detalle Facturacion

                    for($c=0;$c<count($arr);$c++){
                        $columnad1=$c+1;
                        $producto = Producto::find($arr[$c]['producto_id']);
                        $columnad2=$producto->id;
                        $columnad3=$producto->nombre;   
                        
                        $columnad4=$arr[$c]['cantidad'];
                        $columnad5="NIU";
                        if($venta->igv>0){
                            $columnad6=round($arr[$c]['precio']/1.18,2);
                            $columnad7=$arr[$c]['precio'];
                            $columnad8="01";
                            $columnad9=round($columnad4*$columnad6,2);
                            $columnad10="10";
                            $columnad11=round($columnad9*0.18,2);
                        }else{
                            $columnad6=$arr[$c]['precio'];
                            $columnad7=$arr[$c]['precio'];
                            $columnad8="01";
                            $columnad9=round($columnad4*$columnad6,2);
                            $columnad10="30";
                            $columnad11='0.00';
                        }
                        $columnad12='0.00';
                        $columnad13='0.00';
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        tipoDocumento,
                        serieNumero,
                        numeroOrdenItem,
                        codigoProducto,
                        descripcion,
                        cantidad,
                        unidadMedida,
                        importeUnitarioSinImpuesto,
                        importeUnitarioConImpuesto,
                        codigoImporteUnitarioConImpues,
                        importeTotalSinImpuesto,
                        codigoRazonExoneracion,
                        importeIgv
                        )
                        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);
                    }
                    DB::connection('sqlsrv')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?',
                        ['A',$columna5]);
                        
                    //--

                }
                
            }*/

                $guia = 'NO';
                
                if ($ind == 0) {
                    if ($venta->conveniofarmacia_id !== null) {
                        if ($venta->copago == 0) {
                            $guia = 'SI';
                        }
                    }
                    $dat[0]=array("respuesta"=>"OK","venta_id"=>$venta->id, "ind" => $ind, "second_id" => 0, "guia" => $guia);
                }else{
                    if ($montoafecto > 0) {
                        if ($venta->conveniofarmacia_id !== null) {
                            if ($venta->copago == 0) {
                                $guia = 'SI';
                            }
                        }
                        $dat[0]=array("respuesta"=>"OK","venta_id"=>$venta->id, "ind" => $ind, "second_id" => $venta2->id, "guia" => $guia);
                    }else{
                        $ind = 0;
                        if ($venta2->conveniofarmacia_id !== null) {
                            if ($venta2->copago == 0) {
                                $guia = 'SI';
                            }
                        }
                        $dat[0]=array("respuesta"=>"OK","venta_id"=>$venta2->id, "ind" => $ind, "second_id" => $venta2->id, "guia" => $guia);
                    }
                    
                }
            }
                


        });
        return is_null($error) ? json_encode($dat) : $error;

    }

    public function creatependientepasado(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Venta';
        $venta = null;
        //$cboDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $cboDocumento = array();
        $listdocument = Tipodocumento::where('tipomovimiento_id','=','4')->get();
        $cboDocumento = $cboDocumento + array('5' => 'BOLETA DE VENTA');
        foreach ($listdocument as $key => $value) {
            if ($value->id != 5) {
                $cboDocumento = $cboDocumento + array( $value->id => $value->nombre);
            }  
        }
        $cboCredito        = array( 'S' => 'SI');
        $cboCajafarmacia        = array("N" => 'NO');
        $cboTipoventa        = array("N" => 'Normal', 'C' => 'Convenio');
        $cboFormapago        = array('P' => 'Pendiente');
        $cboTipoTarjeta    = array("VISA" => "VISA", "MASTER" => "MASTER");
        $cboTipoTarjeta2    = array("CREDITO" => "CREDITO", "DEBITO" => "DEBITO");
        $formData = array('venta.storependientepasado');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        $numero              = Movimiento::NumeroSigue(4,5,4,'N');//movimiento caja y documento ingreso
        $request->session()->forget('carritoventa');
        return view($this->folderview.'.mantPendientepasado')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','cboDocumento','cboCredito','numero','cboTipoventa','cboFormapago','cboTipoTarjeta','cboTipoTarjeta2'));
    }

    public function storependientepasado(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                //'person_id' => 'required|integer|exists:person,id,deleted_at,NULL',
                'numerodocumento'                  => 'required',
                'fecha'                 => 'required'
                );
        $mensajes = array(
            //'person_id.required'         => 'Debe ingresar un cliente',
            'numerodocumento.required'         => 'Debe ingresar un numero de documento',
            'fecha.required'         => 'Debe ingresar fecha'
            );
        

        if (is_null($request->session()->get('carritoventa')) || count($request->session()->get('carritoventa')) === 0) {
            $error = array(
                'total' => array(
                    'Debe agregar al menos un producto'
                    ));
            return json_encode($error);
        }


        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }

        $dat=array();
        

        $error = DB::transaction(function() use($request,&$dat){
            $ind = 0;
            $montoafecto = 0;
            $montonoafecto = 0;
            $lista = $request->session()->get('carritoventa');
            for ($i=0; $i < count($lista); $i++) {
                $producto = Producto::find($lista[$i]['producto_id']);
                $cantidad  = str_replace(',', '',$lista[$i]['cantidad']);
                $precio    = str_replace(',', '',$lista[$i]['precio']);
                $subtotal  = round(($cantidad*$precio), 2);
                if ($producto->afecto == 'NO') {
                    //$ind = 1;
                    $montonoafecto = $montonoafecto+$subtotal;
                }else{
                    $montoafecto = $montoafecto+$subtotal;
                }
            }

            if ($ind == 0) {
                $total = str_replace(',', '', $request->input('totalventa'));
                $venta                 = new Venta();
                $venta->serie = '004';
                $venta->tipodocumento_id          = $request->input('documento');
                if ($request->input('person_id') !== '' && $request->input('person_id') !== NULL) {
                    $venta->persona_id = $request->input('person_id');
                }else{
                    $venta->nombrepaciente = $request->input('nombrepersona');
                }
                if ($request->input('documento') == '5' || $request->input('documento') == '14' ) {
                    $codigo="03";
                    $abreviatura="B";
                    
                    
                }else{
                    $codigo="01";
                    $abreviatura="F";
                    $venta->empresa_id = $request->input('empresa_id');
                }
                $venta->tipomovimiento_id          = 4;
                $venta->almacen_id          = 1;
                
                $venta->numero = $request->input('numerodocumento');
                $venta->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                $venta->subtotal=number_format($total/1.18,2,'.','');
                $venta->igv=number_format($total - $venta->subtotal,2,'.','');
                $venta->total = $total;
                $venta->credito = $request->input('credito');
                $venta->tipoventa = $request->input('tipoventa');
                $venta->formapago = $request->input('formapago');
                if($request->input('formapago')=="T"){
                    $venta->tarjeta=$request->input('tipotarjeta');//VISA/MASTER
                    $venta->tipotarjeta=$request->input('tipotarjeta2');//DEBITO/CREDITO
                }
                if ($request->input('tipoventa') == 'C') {
                    $venta->conveniofarmacia_id = $request->input('conveniofarmacia_id');
                    $venta->descuentokayros = $request->input('descuentokayros');
                    $venta->copago = $request->input('copago');
                }
                       
                $venta->inicial = 'N';
                $venta->estadopago = 'P';
                $venta->ventafarmacia = 'S';
                $venta->descuentoplanilla = $request->input('descuentoplanilla');
                if ($request->input('formapago')=="P") {
                    $venta->estadopago = 'PP';
                }
                
                $user = Auth::user();
                $venta->responsable_id = $user->person_id;
                $venta->doctor_id = Libreria::obtenerParametro($request->input('doctor_id'));
                //$venta->numeroficha = Libreria::obtenerParametro($request->input('numeroficha'));
                //$venta->cajaprueba = $request->input('cajafamarcia');
                $venta->save();
                $movimiento_id = $venta->id;
                $arr=$lista;
                for ($i=0; $i < count($lista); $i++) {
                    $cantidad  = str_replace(',', '',$lista[$i]['cantidad']);
                    $precio    = str_replace(',', '',$lista[$i]['precio']);
                    $subtotal  = round(($cantidad*$precio), 2);
                    $detalleVenta = new Detallemovimiento();
                    $detalleVenta->cantidad = $cantidad;
                    $detalleVenta->precio = $precio;
                    $detalleVenta->subtotal = $subtotal;
                    $detalleVenta->movimiento_id = $movimiento_id;
                    $detalleVenta->producto_id = $lista[$i]['producto_id'];
                    $detalleVenta->save();
                    $producto = Producto::find($lista[$i]['producto_id']);
                           

                }
            }
            
            

                $guia = 'NO';
                if ($venta->conveniofarmacia_id !== null) {
                    if ($venta->copago == 0) {
                        $guia = 'SI';
                    }
                }
                if ($ind == 0) {
                    $dat[0]=array("respuesta"=>"OK","venta_id"=>$venta->id, "ind" => $ind, "second_id" => 0, "guia" => $guia);
                }else{
                    $dat[0]=array("respuesta"=>"OK","venta_id"=>$venta->id, "ind" => $ind, "second_id" => $venta2->id, "guia" => $guia);
                }

        });
        return is_null($error) ? json_encode($dat) : $error;

    }

    public function createpedido(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Venta';
        $venta = null;
        //$cboDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $cboDocumento = array();
        $listdocument = Tipodocumento::where('tipomovimiento_id','=','4')->get();
        $cboDocumento = $cboDocumento + array('5' => 'BOLETA DE VENTA');
        foreach ($listdocument as $key => $value) {
            if ($value->id != 5) {
                $cboDocumento = $cboDocumento + array( $value->id => $value->nombre);
            }  
        }
        $cboCredito        = array("N" => 'NO', 'S' => 'SI');
        $cboCajafarmacia        = array("N" => 'NO', 'S' => 'SI');
        $cboTipoventa        = array("N" => 'Normal', 'C' => 'Convenio');
        $cboFormapago        = array("C" => 'Contado', 'P' => 'Pendiente', 'T' => 'Tarjeta');
        $formData = array('venta.storepedido');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        $numero              = Movimiento::NumeroSigue(4,5,4,'N');//movimiento caja y documento ingreso
        $request->session()->forget('carritoventa');
        return view($this->folderview.'.mantPedido')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','cboDocumento','cboCredito','numero','cboTipoventa','cboFormapago'));
    }

    public function storepedido(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                //'person_id' => 'required|integer|exists:person,id,deleted_at,NULL',
                'fecha'                 => 'required'
                );
        $mensajes = array(
            //'person_id.required'         => 'Debe ingresar un cliente',
            'fecha.required'         => 'Debe ingresar fecha'
            );
        

        if (is_null($request->session()->get('carritoventa')) || count($request->session()->get('carritoventa')) === 0) {
            $error = array(
                'total' => array(
                    'Debe agregar al menos un producto'
                    ));
            return json_encode($error);
        }


        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }

        $dat=array();

        $error = DB::transaction(function() use($request,&$dat){
            $lista = $request->session()->get('carritoventa');
            $total = str_replace(',', '', $request->input('totalventa'));
            $venta                 = new Venta();

            $venta->tipomovimiento_id          = 8;
            $venta->almacen_id          = 1;
            
            $venta->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
            $venta->subtotal=number_format($total/1.18,2,'.','');
            $venta->igv=number_format($total - $venta->subtotal,2,'.','');
            $venta->total = $total;
            $venta->credito = $request->input('credito');
            $venta->tipoventa = $request->input('tipoventa');
            $venta->formapago = $request->input('formapago');
            if ($request->input('tipoventa') == 'C') {
                $venta->conveniofarmacia_id = $request->input('conveniofarmacia_id');
                $venta->descuentokayros = $request->input('descuentokayros');
                $venta->copago = $request->input('copago');
            }
                   
            $venta->inicial = 'N';
            $venta->estadopago = 'P';
            $venta->ventafarmacia = 'S';
            if ($request->input('credito') == 'S') {
                $venta->estadopago = 'PP';
            }
            
            $user = Auth::user();
            $venta->responsable_id = $user->person_id;
            $venta->doctor_id = Libreria::obtenerParametro($request->input('doctor_id'));
            //$venta->numeroficha = Libreria::obtenerParametro($request->input('numeroficha'));
            //$venta->cajaprueba = $request->input('cajafamarcia');
            $venta->save();
            $movimiento_id = $venta->id;
            $arr=$lista;
            for ($i=0; $i < count($lista); $i++) {
                $cantidad  = str_replace(',', '',$lista[$i]['cantidad']);
                $precio    = str_replace(',', '',$lista[$i]['precio']);
                $subtotal  = round(($cantidad*$precio), 2);
                $detalleVenta = new Detallemovimiento();
                $detalleVenta->cantidad = $cantidad;
                $detalleVenta->precio = $precio;
                $detalleVenta->subtotal = $subtotal;
                $detalleVenta->movimiento_id = $movimiento_id;
                $detalleVenta->producto_id = $lista[$i]['producto_id'];
                $detalleVenta->save();
                $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $lista[$i]['producto_id'])->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                //$ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->where('promarlab_id', '=', $lista[$i]['promarlab_id'])->where('kardex.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();

                // Creamos el lote para el producto
                /*$lote = new Lote();
                $lote->nombre  = $lista[$i]['lote'];
                $lote->fechavencimiento  = Date::createFromFormat('d/m/Y', $lista[$i]['fechavencimiento'])->format('Y-m-d');
                $lote->cantidad = $cantidad;
                $lote->queda = $cantidad;
                $lote->producto_id = $lista[$i]['producto_id'];
                $lote->almacen_id = 1;
                $lote->save();*/
                $lotes = Lote::where('producto_id','=',$lista[$i]['producto_id'])->where('queda','>','0')->orderBy('fechavencimiento','ASC')->get();
                foreach ($lotes as $key => $value) {
                    $aux = $lista[$i]['cantidad'];
                    if ($value->queda >= $aux) {
                        $queda = $value->queda-$aux;
                        $value->queda = $queda;
                        $value->save();
                        break;
                    }else{
                        $aux = $value->queda-$aux;
                    }
                }

                $stockanterior = 0;
                $stockactual = 0;
                // ingresamos nuevo kardex
                if ($ultimokardex === NULL) {
                    
                    
                }else{
                    $stockanterior = $ultimokardex->stockactual;
                    $stockactual = $ultimokardex->stockactual-$cantidad;
                    $kardex = new Kardex();
                    $kardex->tipo = 'S';
                    $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                    $kardex->stockanterior = $stockanterior;
                    $kardex->stockactual = $stockactual;
                    $kardex->cantidad = $cantidad;
                    $kardex->precioventa = $precio;
                    //$kardex->almacen_id = 1;
                    $kardex->detallemovimiento_id = $detalleVenta->id;
                    //$kardex->lote_id = $lote->id;
                    $kardex->save();    

                }

            }
                    //echo 'entro';
                $dat[0]=array("respuesta"=>"OK","venta_id"=>$venta->id);

        });
        return is_null($error) ? json_encode($dat) : $error;

    }

    public function pagar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Venta::find($id);
        $entidad  = 'Venta';
        $cboFormaPago     = array("Efectivo" => "Efectivo", "Tarjeta" => "Tarjeta");
        $cboTipoTarjeta    = array("VISA" => "VISA", "MASTER" => "MASTER");
        $cboTipoTarjeta2    = array("CREDITO" => "CREDITO", "DEBITO" => "DEBITO");  

        $formData = array('route' => array('venta.guardarpago', $id), 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Pagar';
        return view('app.venta.confirmarpago')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar','cboFormaPago','cboTipoTarjeta2','cboTipoTarjeta'));
    }

    public function guardarpago(Request $request)
    {
        $id=$request->input('id');
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id,$request){
            $venta = Venta::find($id);
            $total = $venta->total;
            $movimiento = new Movimiento();
            $movimiento->tipodocumento_id          = $venta->tipodocumento_id;
            if ($venta->persona_id !== '' && $venta->persona_id !== NULL) {
                $movimiento->persona_id = $venta->persona_id;
            }else{
                $movimiento->nombrepaciente = $venta->nombrepaciente;
            }
            if ($venta->tipodocumento_id == '5') {
                if ($venta->persona_id !== '' && $venta->persona_id !== NULL) {
                    $movimiento->persona_id = $venta->persona_id;
                }else{
                    $movimiento->nombrepaciente = $venta->nombrepaciente;
                }
                
            }else{
                $movimiento->empresa_id = $venta->empresa_id;
            }
            $movimiento->tipomovimiento_id          = 2;
            $movimiento->serie = '008';
            $movimiento->numero = $venta->numero;
            $movimiento->fecha  = date("Y-m-d");
            $movimiento->total = $total;
            
            $user = Auth::user();
            $movimiento->responsable_id = $user->person_id;
            $movimiento->conceptopago_id = 23;
            $movimiento->caja_id = 4;
            $movimiento->movimiento_id = $venta->id;
            if($request->input('formapago')=="Tarjeta"){
                $movimiento->tipotarjeta=$request->input('tipotarjeta');
                $movimiento->tarjeta=$request->input('tipotarjeta2');
                $movimiento->voucher=$request->input('nroref');
                $movimiento->totalpagado=0;
            }else{
                $movimiento->totalpagado=$request->input('total',0);
            }
            $movimiento->save();

            $venta->movimiento_id = $movimiento->id;
            $venta->formapago = 'C';
            $venta->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function show(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $venta = Venta::find($id);
        $entidad             = 'Venta';
        $cboDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $cboCredito        = array("N" => 'NO', 'S' => 'SI');
        $cboCajafarmacia        = array("N" => 'NO', 'S' => 'SI');     
        $formData            = array('venta.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        $detalles = Detallemovimiento::where('movimiento_id','=',$venta->id)->get();

        return view($this->folderview.'.mantView')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','cboDocumento','cboCredito','cboCajafarmacia','detalles'));
    }

    public function createnotacredito($id, $listarLuego,Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $venta = Venta::find($id);
        $numero              = Movimiento::NumeroSigue(6,13,2,'N');
        $entidad             = 'Venta';
        $cboDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $cboCredito        = array("N" => 'NO', 'S' => 'SI');
        $cboCajafarmacia        = array("N" => 'NO', 'S' => 'SI');     
        $formData            = array('venta.savenotacredito', $id);
        $formData            = array('route' => $formData, 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Guardar';
        $detalles = Detallemovimiento::where('movimiento_id','=',$venta->id)->get();
        $lista = array();
        foreach ($detalles as $key => $value) {
            $lista[]  = array('cantidad' => $value->cantidad, 'precio' => $value->precio, 'productonombre' => $value->producto->nombre,'producto_id' => $value->producto_id, 'codigobarra' => $value->producto->codigobarra, 'tipoventa' => $venta->tipoventa, 'descuentokayros' => $venta->descuentokayros, 'copago' => $venta->copago);
        }

        $cboComentario = array('01@Anulacion de la operacion'=>'Anulación de la operación',
                                '02@Anulacion por error en el RUC'=>'Anulación por error en el RUC',
                                '03@Correccion por error en la descripcion'=>'Corrección por error en la descripción',
                                '04@Descuento global'=>'Descuento global',
                                '05@Descuento por item'=>'Descuento por ítem',
                                '06@Devolucion total'=>'Devolución total',
                                '07@Devolucion por ítem'=>'Devolución por ítem',
                                '08@Bonificacion'=>'Bonificación',
                                '09@Disminucion en el valor'=>'Disminución en el valor',
                                '10@Otros conceptos'=>'Otros conceptos');

        $request->session()->put('carritonotacredito', $lista);

        return view($this->folderview.'.mantNota')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','cboDocumento','cboCredito','cboCajafarmacia','detalles','numero','cboComentario'));
    }

    public function listarcarritonota(Request $request)
    {
            $lista          = $request->session()->get('carritonotacredito');
            $cadena   = '<table style="width: 100%;" border="1">';
            $cadena   .= '<thead>
                                <tr>
                                    <th bgcolor="#E0ECF8" class="text-center">Producto</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Cantidad</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Precio Unit</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Dscto</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Subtotal</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Quitar</th>                            
                                </tr>
                            </thead>';
            
            $total = 0;
            $dscto = 0;
            $subtotal = 0;
            for ($i=0; $i < count($lista); $i++) {
                if ($lista[$i]['tipoventa'] == 'C') {
                    $precioaux = $lista[$i]['precio'] - ($lista[$i]['precio']*($lista[$i]['descuentokayros']/100));
                    $dscto = round(($precioaux*$lista[$i]['cantidad']),2);
                    $subtotal = round(($dscto*($lista[$i]['copago']/100)),2);
                }else{
                    $subtotal = round(($lista[$i]['cantidad']*$lista[$i]['precio']), 2);
                }
                
                $total    += $subtotal;
                $cadena   .= '<tr><td class="text-center" style="width:750px;"><span style="display: block; font-size:.7em">'.$lista[$i]['productonombre'].'</span></td>';
                $cadena   .= '<td class="text-center" style="width:100px;"><span style="display: block; font-size:.7em">'.$lista[$i]['cantidad'].'</span></td>';
                $cadena   .= '<td class="text-center" style="width:100px;"><span style="display: block; font-size:.7em">'.$lista[$i]['precio'].'</span></td>';
                $cadena   .= '<td class="text-center" style="width:90px;"><span style="display: block; font-size:.7em">'.$dscto.'</span></td>';
                $cadena   .= '<td class="text-center" style="width:90px;"><span style="display: block; font-size:.7em">'.$subtotal.'</span></td>';
                $cadena   .= '<td class="text-center"><span style="display: block; font-size:.7em"><a class="btn btn-xs btn-danger" onclick="quitar(\''.$i.'\');">Quitar</a></span></td></tr>';
            }
            $cadena  .= '<tr><th colspan="3" style="text-align: right;">TOTAL</th><td class="text-center">'.$total.'<input type ="hidden" id="totalventa" readonly=""  name="totalventa" value="'.$total.'"></td></tr></tr>';
            $cadena .= '</table>';
            return $cadena;
    }

    public function savenotacredito(Request $request)
    {
        $dat=array();
        

        $error = DB::transaction(function() use($request,&$dat){
            $user = Auth::user();
            $ind = 0;
            $montoafecto = 0;
            $montonoafecto = 0;
            $lista = $request->session()->get('carritonotacredito');
            for ($i=0; $i < count($lista); $i++) {
                $producto = Producto::find($lista[$i]['producto_id']);
                /*$cantidad  = str_replace(',', '',$lista[$i]['cantidad']);
                $precio    = str_replace(',', '',$lista[$i]['precio']);
                $subtotal  = round(($cantidad*$precio), 2);*/
                $subtotal = 0;
                if ($lista[$i]['tipoventa'] == 'C') {
                    $precioaux = $lista[$i]['precio'] - ($lista[$i]['precio']*($lista[$i]['descuentokayros']/100));
                    $dscto = round(($precioaux*$lista[$i]['cantidad']),2);
                    $subtotal = round(($dscto*($lista[$i]['copago']/100)),2);
                }else{
                    $subtotal = round(($lista[$i]['cantidad']*$lista[$i]['precio']), 2);
                }

                if ($producto->afecto == 'NO') {
                    $ind = 1;
                    $montonoafecto = $montonoafecto+$subtotal;
                }else{
                    $montoafecto = $montoafecto+$subtotal;
                }
            }

            //if ($ind == 0) {
                //$movimientoref = Movimiento::find($request->input('movimiento_id'));
                $Venta = Movimiento::find($request->input('movimiento_id'));
                $total = str_replace(',', '', $request->input('totalventa'));
                $Movimiento = new Movimiento();
                $Movimiento->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                $Movimiento->serie = $request->input('serie');
                //$numero              = Movimiento::NumeroSigue(6,13,2,'N');
                if($Venta->tipodocumento_id=='5'){
                    $dat = Movimiento::join('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',6)->where('movimiento.manual','like','N')->whereIn('m2.tipodocumento_id',['5'])->select(DB::raw("max((CASE WHEN movimiento.numero IS NULL THEN 0 ELSE movimiento.numero END)*1) AS maximo"))->first();
                }else{
                    $dat = Movimiento::join('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',6)->where('movimiento.manual','like','N')->whereIn('m2.tipodocumento_id',['4','17'])->select(DB::raw("max((CASE WHEN movimiento.numero IS NULL THEN 0 ELSE movimiento.numero END)*1) AS maximo"))->first();
                }
                $numero = $dat->maximo + 1;
                $Movimiento->numero = $numero;
                if ($request->input('person_id') !== null && $request->input('person_id') !== '') {
                    $Movimiento->persona_id = $request->input('person_id');
                }else{
                    $Movimiento->nombrepaciente = $Venta->nombrepaciente;
                }
                $subtotal = number_format($Venta->subtotal,2,'.','');
                $igv = number_format($Venta->igv,2,'.','');
                $Movimiento->total = $total;
                $Movimiento->subtotal = $subtotal;
                $Movimiento->igv = $igv;
                $Movimiento->responsable_id=$user->person_id;
                $Movimiento->movimiento_id = $request->input('movimiento_id');
                $Movimiento->situacion='N';//Normal
                $Movimiento->tipomovimiento_id = 6;
                $Movimiento->tipodocumento_id = 13;
                $comentario = explode("@",$request->input('comentario'));
                $Movimiento->comentario = $comentario[1];
                $Movimiento->almacen_id=1;
                $Movimiento->ventafarmacia='S';
                $Movimiento->manual='N';
                $Movimiento->save();

                $movimiento_id = $Movimiento->id;
                $arr=$lista;
                for ($i=0; $i < count($lista); $i++) {
                    $cantidad  = str_replace(',', '',$lista[$i]['cantidad']);
                    $precio    = str_replace(',', '',$lista[$i]['precio']);
                    $subtotal  = round(($cantidad*$precio), 2);
                    $detalleVenta = new Detallemovimiento();
                    $detalleVenta->cantidad = $cantidad;
                    $detalleVenta->precio = $precio;
                    $detalleVenta->subtotal = $subtotal;
                    $detalleVenta->movimiento_id = $movimiento_id;
                    $detalleVenta->producto_id = $lista[$i]['producto_id'];
                    $detalleVenta->save();
                    $producto = Producto::find($lista[$i]['producto_id']);
                    if ($producto->afecto == 'NO') {
                        $ind = 1;
                        
                    }else{
                        
                    }

                    //Stock
                    $stock = Stock::where('producto_id','=',$lista[$i]['producto_id'])->where('almacen_id','=',1)->first();
                    if(is_null($stock)){
                        $stock = new Stock();
                        $stock->producto_id = $lista[$i]['producto_id'];
                        $stock->cantidad = $cantidad;
                        $stock->almacen_id = 1;
                        $stock->save();
                    }else{
                        $stock->cantidad = $stock->cantidad + $cantidad;
                        $stock->save();
                    }
                    //

                    $consultakardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('movimiento.id', '=',$request->input('movimiento_id'))->where('producto_id', '=', $lista[$i]['producto_id'])->select('kardex.*')->get();

                    //$ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->where('promarlab_id', '=', $lista[$i]['promarlab_id'])->where('kardex.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();

                    // Creamos el lote para el producto
                    /*$lote = new Lote();
                    $lote->nombre  = $lista[$i]['lote'];
                    $lote->fechavencimiento  = Date::createFromFormat('d/m/Y', $lista[$i]['fechavencimiento'])->format('Y-m-d');
                    $lote->cantidad = $cantidad;
                    $lote->queda = $cantidad;
                    $lote->producto_id = $lista[$i]['producto_id'];
                    $lote->almacen_id = 1;
                    $lote->save();*/


                    foreach ($consultakardex as $key => $value) {
                        if($value->lote_id>0){
                            $lote = Lote::find($value->lote_id);
                            $lote->queda = $lote->queda + $value->cantidad;
                            $lote->save();
                        }
                        $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $lista[$i]['producto_id'])->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();

                        $stockanterior = 0;
                        $stockactual = 0;
                        // ingresamos nuevo kardex
                        if ($ultimokardex === NULL) {
                            
                            
                        }else{
                            $stockanterior = $ultimokardex->stockactual;
                            $stockactual = $ultimokardex->stockactual+$value->cantidad;
                            $kardex = new Kardex();
                            $kardex->tipo = 'I';
                            $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                            $kardex->stockanterior = $stockanterior;
                            $kardex->stockactual = $stockactual;
                            $kardex->cantidad = $value->cantidad;
                            $kardex->precioventa = $precio;
                            //$kardex->almacen_id = 1;
                            $kardex->detallemovimiento_id = $detalleVenta->id;
                            $kardex->lote_id = $lote->id;
                            $kardex->save();    

                        }
                    }
                    
                } 


                //VENTA
                $Movimientoref = Movimiento::find($request->input('movimiento_id'));
                $Movimientoref->situacion='A';
                $Movimientoref->save();

                //CAJA
                if($request->input('pagar')=='S'){
                    $movimiento        = new Movimiento();
                    $movimiento->fecha = date("Y-m-d");
                    $movimiento->numero= $request->input('numerodocumento');
                    $movimiento->responsable_id=$user->person_id;
                    $movimiento->persona_id=$Venta->persona_id;
                    $movimiento->nombrepaciente=$Venta->nombrepaciente;
                    $movimiento->subtotal=0;
                    $movimiento->igv=0;
                    $movimiento->total=$request->input('total',0); 
                    $movimiento->tipomovimiento_id=2;
                    $movimiento->tipodocumento_id=2;
                    $movimiento->conceptopago_id=13;//DEVOLUCION
                    $movimiento->comentario='Anulacion de: '.$Venta->serie.'-'.$Venta->numero;
                    $movimiento->caja_id=4;
                    $movimiento->totalpagado=$request->input('total',0);
                    $movimiento->situacion='N';
                    $movimiento->movimiento_id=$movimiento_id;
                    $movimiento->save();
                }
                // MOVIMIENTO PAGO
                /*$movimientopago = Movimiento::find($Movimientoref->movimiento_id);
                if (!is_null($movimientopago)) {
                    $movimientopago->situacion = 'A';
                    $movimientopago->save();
                }*/
                /*
                //Array Insert facturacion
                if($Movimientoref->tipodocumento_id==5){//BOLETA
                    $codigo="03";
                    $abreviatura="BC";
                }else{
                    $codigo="01";
                    $abreviatura="FC";
                }
                if ($Movimientoref->persona_id !== null) {
                    $person = Person::find($Movimientoref->persona_id);
                }
                
                $columna1=6;
                $columna2="20480082673";//RUC HOSPITAL
                $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                $columna4="07";
                $columna5=$abreviatura.substr($request->input('serie'),1,2).'-'.$request->input('numerodocumento');
                $numero=$columna5;
                $columna6=date('Y-m-d');
                $columna7="sistemas@hospitaljuanpablo.pe";
                if($Movimientoref->tipodocumento_id==5){//BOLETA
                    $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                    if ($Movimientoref->persona_id !== NULL) {
                        
                        if(strlen($person->dni)<>8){
                            $columna9='00000000';
                        }else{
                            $columna9=$person->dni;
                        }
                    }else{
                        $columna9='00000000';
                    }
                    
                }else{
                    $columna8=6;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                    $columna9=$person->ruc;
                }
                if ($Movimientoref->persona_id !== NULL) {
                    $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                    if(trim($person->direccion!="")){
                        $columna101=trim($person->direccion);
                    }else{
                        $columna101="-";
                    }
                }else{
                    $columna10 = $Movimientoref->nombrepaciente;
                    $columna101=trim('-');
                }
                
                $columna11="-";    
                $columna12="PEN";
                if($Movimiento->igv>0){
                    $columna13=$Movimiento->subtotal;
                    $columna14='0.00';
                    $columna15='0.00';
                }else{
                    $columna13='0.00';
                    $columna14=$Movimiento->subtotal;
                    $columna15='0.00';
                }
                $columna16="";
                $columna17=$Movimiento->igv;
                $columna18='0.00';
                $columna19='0.00';
                $columna20=$Movimiento->total;
                $columna21=1000;
                $letras = new EnLetras();
                $columna22=$letras->ValorEnLetras($columna20, "SOLES" );//letras
                $columna23=$codigo;
                $columna24=($Movimientoref->tipodocumento_id==4?"F":"B").str_pad($Movimientoref->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($Movimientoref->numero,8,'0',STR_PAD_LEFT);
                $columna25=$Movimiento->comentario;
                $columna26=$comentario[0];
                DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER (
                    tipoDocumentoEmisor,
                    numeroDocumentoEmisor,
                    razonSocialEmisor,
                    tipoDocumento,
                    serieNumero,
                    fechaEmision,
                    correoEmisor,
                    tipoDocumentoAdquiriente,
                    numeroDocumentoAdquiriente,
                    razonSocialAdquiriente,
                    correoAdquiriente,
                    tipoMoneda,
                    totalValorVentaNetoOpGravadas,
                    totalValorVentaNetoOpNoGravada,
                    totalValorVentaNetoOpExonerada,                
                    totalIgv,
                    totalVenta,
                    codigoLeyenda_1,
                    textoLeyenda_1,
                    tipoDocumentoReferenciaPrincip,
                    numeroDocumentoReferenciaPrinc,
                    motivoDocumento,
                    codigoSerieNumeroAfectado,
                    serieNumeroAfectado  
                    ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22, $columna23, $columna24, $columna25, $columna26, $columna24]);

                if($abreviatura=="BC"){
                    DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        serieNumero,
                        tipoDocumento,
                        clave,
                        valor) 
                        values (?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna5, $columna4, 'LugarDestino', $columna101]);
                }else{
                    DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        serieNumero,
                        tipoDocumento,
                        clave,
                        valor) 
                        values (?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);
                }
                //---
                
                //Array Insert Detalle Facturacion
                //$arr=explode(",",$request->input('listServicio'));
                for($c=0;$c<count($arr);$c++){
                    $columnad1=$c+1;
                        $producto = Producto::find($arr[$c]['producto_id']);
                        $columnad2=$producto->id;
                        $columnad3=$producto->nombre;   
                        
                        $columnad4=$arr[$c]['cantidad'];
                        $columnad5="NIU";
                        if($Movimiento->igv>0){
                            $columnad6=round($arr[$c]['precio']/1.18,2);
                        }else{
                            $columnad6=round($arr[$c]['precio'],2);
                        }
                        $columnad7=$arr[$c]['precio'];
                        $columnad8="01";
                        $columnad9=round($columnad4*$columnad6,2);
                        if($Movimiento->igv>0){
                            $columnad10="10";
                            $columnad11=round($columnad9*0.18,2);
                        }else{
                            $columnad10="30";
                            $columnad11='0.00';
                        }
                        $columnad12='0.00';
                        $columnad13='0.00';
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        tipoDocumento,
                        serieNumero,
                        numeroOrdenItem,
                        codigoProducto,
                        descripcion,
                        cantidad,
                        unidadMedida,
                        importeUnitarioSinImpuesto,
                        importeUnitarioConImpuesto,
                        codigoImporteUnitarioConImpues,
                        importeTotalSinImpuesto,
                        codigoRazonExoneracion,
                        importeIgv
                        )
                        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);
                }
                DB::connection('sqlsrv')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?',
                    ['A',$columna5]);
                */
            
                
            /*}else{

            }*/



            if ($ind == 0) {
                $dat[0]=array("respuesta"=>"OK","venta_id"=>$Movimiento->id, "ind" => $ind, "second_id" => 0);
            }else{
                $dat[0]=array("respuesta"=>"OK","venta_id"=>$Movimiento->id, "ind" => $ind, "second_id" => 0);
                //$venta2->id
            }

        });
       /* if (!is_null($error)) {
                DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEHEADER where serieNumero="'.$numero.'"');
                DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEDETAIL where serieNumero="'.$numero.'"');
                DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEHEADER_ADD where serieNumero="'.$numero.'"');
            }*/
        return is_null($error) ? json_encode($dat) : $error;
    }

    public function anulacion($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Movimiento::find($id);
        $entidad  = 'Venta';
        $formData = array('route' => array('venta.anular', $id), 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view($this->folderview.'.anular')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function anular(Request $request, $id)
    {
        $reglas     = array(
            'motivo'                  => 'required'
        );

        $mensajes = array(
            'motivo.required'         => 'Debe ingresar un motivo'
        );

        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        
        // dd('njdehnde');
        $error = DB::transaction(function() use($request, $id){
            $user = Auth::user();
            $movimiento = Movimiento::find($id);

            $detalles = Detallemovimiento::where('movimiento_id','=',$movimiento->id)->get();
            foreach ($detalles as $key => $value) {
                $consultakardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('movimiento.id', '=',$movimiento->id)->where('producto_id', '=', $value->producto_id)->select('kardex.*')->get();

                foreach ($consultakardex as $key2 => $value2) {
                    $lote = Lote::find($value2->lote_id);
                    $lote->queda = $lote->queda + $value2->cantidad;
                    $lote->save();
                    $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $value->producto_id)->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();

                    //Stock
                    $stock = Stock::where('producto_id','=',$value->producto_id)->where('almacen_id','=',1)->first();
                    if(is_null($stock)){
                        $stock = new Stock();
                        $stock->producto_id = $value->producto_id;
                        $stock->cantidad = $value2->cantidad;
                        $stock->almacen_id = 1;
                        $stock->save();
                    }else{
                        $stock->cantidad = $stock->cantidad + $value2->cantidad;
                        $stock->save();
                    }
                    //

                    $stockanterior = 0;
                    $stockactual = 0;
                    // ingresamos nuevo kardex
                    if ($ultimokardex === NULL) {
                        
                        
                    }else{
                        $stockanterior = $ultimokardex->stockactual;
                        $stockactual = $ultimokardex->stockactual+$value2->cantidad;
                        $kardex = new Kardex();
                        $kardex->tipo = 'I';
                        $kardex->fecha = date('Y-m-d');
                        $kardex->stockanterior = $stockanterior;
                        $kardex->stockactual = $stockactual;
                        $kardex->cantidad = $value2->cantidad;
                        $kardex->precioventa = $value2->precio;
                        //$kardex->almacen_id = 1;
                        $kardex->detallemovimiento_id = $value->id;
                        $kardex->lote_id = $lote->id;
                        $kardex->save();    

                    }
                }
            }

            $movimiento->comentario = $request->get('motivo');
            $movimiento->situacion='U';
            $movimiento->usuarioEliminar_id = $user->person_id;;
            $movimiento->save();
            
            $movimientopago = Movimiento::find($movimiento->movimiento_id);
            if ($movimientopago != null) {
                $movimientopago->situacion = 'A';
                $movimientopago->usuarioEliminar_id = $user->person_id;;
                $movimientopago->save();
            }
        });
        return is_null($error) ? "OK" : $error;
    }
    
    public function pdfComprobante(Request $request){
        $entidad          = 'Venta';
        $id               = Libreria::getParam($request->input('venta_id'),'');
        $guia = $request->input('guia');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->where('movimiento.id', '=', $id);
        $resultado        = $resultado->select('movimiento.*','tipodocumento.nombre as tipodocumento');
        $lista            = $resultado->get();
        //dd($lista);
        //print_r(count($lista));
        if (count($lista) > 0) {     
            foreach($lista as $key => $value){
                if ( ($value->tipodocumento_id == 5) || ($value->tipodocumento_id == 4)) {
                    if ($guia == 'SI') {
                        $pdf = new TCPDF();
                        $abreviatura="B";
                        $pdf::SetTitle('Guia Interna de Salida de Medicamentos');
                        $pdf::AddPage();
                        $pdf::SetFont('helvetica','B',14);
                        $pdf::Cell(0,5.5,("GUIA INTERNA DE SALIDA DE MEDICAMENTOS"),0,0,'C');
                        $pdf::Ln();
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',10);
                        $pdf::Cell(50,5.5,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                        $pdf::Cell(70,5.5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                        $pdf::Cell(60,5.5,'Convenio: '.$value->conveniofarmacia->nombre,0,0,'R');
                        $pdf::Ln();
                        $pdf::Ln();
                        if ($value->persona_id !== NULL) {
                            $pdf::Cell(110,6,(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                            $pdf::Cell(110,6,($value->fecha),0,0,'L');
                        }else{
                            $pdf::Cell(110,6,(trim("Paciente: ".$value->nombrepaciente)),0,0,'L');
                        }
                        $pdf::Ln();
                        $pdf::Cell(240,6,(substr($value->created_at, 11)),0,0,'C');
                        $pdf::Ln();
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(15,5.5,("Cantidad"),1,0,'C');
                        $pdf::Cell(80,5.5,"Producto",1,0,'C');
                        $pdf::Cell(19,5.5,("Prec. Unit."),1,0,'C');
                        $pdf::Cell(22,5.5,("P.Total Dcto"),1,0,'C');
                        $pdf::Cell(23,5.5,("% Copago Pac."),1,0,'C');
                        $pdf::Cell(20,5.5,("Precio Total"),1,0,'C');
                        $pdf::Cell(20,5.5,("Sin IGV"),1,0,'C');
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Ln();
                        $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                        $lista2            = $resultado->get();
                        $totalpago=0;
                        $totaldescuento=0;
                        $totaligv=0;
                        foreach($lista2 as $key2 => $v){
                            $pdf::Cell(15,5.5,number_format($v->cantidad,2,'.',''),0,0,'C');
                            $pdf::Cell(80,5.5,utf8_encode($v->producto->nombre),0,0,'L');
                            $pdf::Cell(19,5.5,number_format($v->precio,2,'.',''),0,0,'C');
                            $valaux = round(($v->precio*$v->cantidad), 2);
                            $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                            $dscto = round(($precioaux*$v->cantidad),2);
                            $totalpago = $totalpago+$dscto;
                            $pdf::Cell(22,5.5,number_format($dscto,2,'.',''),0,0,'C');

                            if($value->copago == 100){
                                $value->copago = 0;
                            }
                            
                            $pdf::Cell(23,5.5,number_format($value->copago,2,'.',''),0,0,'C');
                            if($value->copago>0){
                                $subtotal = round(($dscto*(($value->copago)/100)),2);
                                $subigv = round(($subtotal/1.18),2);
                            }else{
                                $subigv = round(($dscto/1.18),2);
                                $subtotal = 0;
                            }
                            // $subtotal = round(($dscto*($value->copago/100)),2);
                            // $subigv = round(($subtotal/1.18),2);
                            $totaldescuento = $totaldescuento+$subtotal;
                            $totaligv = $totaligv+$subigv;
                            $pdf::Cell(20,5.5,number_format($subtotal,2,'.',''),0,0,'C');
                            $pdf::Cell(20,5.5,number_format($subigv,2,'.',''),0,0,'C');
                            $pdf::Ln();
                        }
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(114,5.5,'',0,0,'C');
                        $pdf::Cell(22,5.5,number_format($totalpago,2,'.',''),0,0,'C');
                        $pdf::Cell(23,5.5,'',0,0,'C');
                        $pdf::Cell(20,5.5,number_format($totaldescuento,2,'.',''),0,0,'C');
                        $pdf::Cell(20,5.5,number_format($totaligv,2,'.',''),0,0,'C');
                        $pdf::Ln();
                        $pdf::Output('Guia.pdf');
                    }else{
                        $pdf = new TCPDF();
                        $pdf::SetTitle('Comprobante');
                        $pdf::AddPage();
                        $pdf::SetFont('helvetica','B',12);
                        $pdf::Cell(130,5.5,"",0,0,'C');
                        $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 15, 5, 115, 30);
                        $pdf::Cell(60,5.5,"RUC N° 20480082673",'RTL',0,'C');
                        $pdf::Ln();
                        $pdf::Cell(130,5.5,"",0,0,'C');
                        $pdf::Cell(60,5.5,utf8_encode($value->tipodocumento_id=='4'?"FACTURA":"BOLETA"),'RL',0,'C');
                        $pdf::Ln();
                        $pdf::Cell(130,5.5,"",0,0,'C');
                        $pdf::Cell(60,5.5,"ELECTRÓNICA",'RL',0,'C');
                        $pdf::Ln();
                        $pdf::Cell(130,5.5,"",0,0,'C');
                        if($value->tipodocumento_id=="4"){
                            $abreviatura="F";
                            $dni=$value->empresa->ruc;
                            $subtotal=number_format($value->subtotal,2,'.','');
                            $igv=number_format($value->igv,2,'.','');
                        }else{
                            $abreviatura="B";
                            /*$subtotal='0.00';
                            $igv='0.00';*/
                            $subtotal=number_format($value->subtotal,2,'.','');
                            $igv=number_format($value->igv,2,'.','');
                            if ($value->persona_id !== NULL) {
                                if(strlen($value->persona->dni)<>8){
                                    $dni='00000000';
                                }else{
                                    $dni=$value->persona->dni;
                                }
                            }else{
                                $dni='00000000';
                            }
                            
                        }
                        $pdf::Cell(60,5.5,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),'RBL',0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(0,5.5,utf8_decode("HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA"),0,0,'L');
                        $pdf::Ln();
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(37,6,"Nombre / Razón Social: ",0,0,'L');
                        $pdf::SetFont('helvetica','',9);
                        if ($value->tipodocumento_id == 5) {
                            if ($value->persona_id !== NULL) {
                                $pdf::Cell(110,6,(trim($value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                            }else{
                                $pdf::Cell(110,6,(trim($value->nombrepaciente)),0,0,'L');
                            }
                            
                        }else{
                            $pdf::Cell(110,6,(trim($value->empresa->bussinesname)),0,0,'L');
                        }
                        
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(30,6,utf8_encode($abreviatura=="F"?"RUC":"DNI".": "),0,0,'L');
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Cell(37,6,utf8_encode($dni),0,0,'L');
                        $pdf::Ln();

                        if($value->tipodocumento_id=="4"){
                            $pdf::SetFont('helvetica','B',9);
                            $pdf::Cell(37,6,utf8_encode("Paciente: "),0,0,'L');
                            $pdf::SetFont('helvetica','',9);
                            if ($value->persona_id>0 && $value->persona_id !== NULL) {
                                $pdf::Cell(110,6,(trim($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                            }else{
                                $pdf::Cell(110,6,(trim($value->nombrepaciente)),0,0,'L');
                            }
                            
                        }else{
                            $pdf::Cell(37,6,utf8_encode(""),0,0,'L');
                            $pdf::Cell(110,6,"",0,0,'L');
                        }
                        $estadopago = '';
                        
                        if ($value->formapago == 'P') {
                            $estadopago = 'Pendiente';
                        }else {
                            if ($value->formapago == 'C') {
                                $estadopago = 'Pagado - Contado';
                            }else{
                            $movaux = Movimiento::find($value->movimiento_id);
                                $voucher = $movaux->voucher;
                                $estadopago = 'Pagado -'.$value->tipotarjeta.' - '.$value->tarjeta.' - '.$voucher;
                            }
                            
                        }
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(12,6,"Estado:",0,0,'L');
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(43,6,$estadopago,0,0,'L');
                        $pdf::Ln();

                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(37,6,"Dirección: ",0,0,'L');
                        $pdf::SetFont('helvetica','',9);
                        if ($value->tipodocumento_id == 5) {
                            if ($value->persona_id !== NULL) {
                                if(strlen($value->persona->direccion)>50){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();
                                    $pdf::Multicell(110,3,trim($value->persona->direccion),0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(110,6,"",0,0,'L');
                                }else{
                                    $pdf::Cell(110,6,(trim($value->persona->direccion)),0,0,'L');
                                }
                            }else{

                                $pdf::Cell(110,6,(trim('')),0,0,'L');
                            }
                            
                        }else{
                            if(strlen($value->empresa->direccion)>50){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();
                                $pdf::Multicell(150,3,trim($value->empresa->direccion),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(110,6,"",0,0,'L');
                            }else{
                                $pdf::Cell(110,6,(trim($value->empresa->direccion)),0,0,'L');
                            }
                            //$pdf::Cell(110,6,(trim($value->empresa->direccion)),0,0,'L');
                        }
                        
                        
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(37,6,utf8_encode("Moneda: "),0,0,'L');
                        $pdf::SetFont('helvetica','',9);
                        $pdf::Cell(40,6,(trim('PEN - Sol')),0,0,'L');
                        //$value2=Movimiento::find($id);
                        //$pdf::Cell(80,6,(trim($value2->plan->nombre)),0,0,'L');
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(100,6,"Fecha de emisión: ",0,0,'R');
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Cell(37,6,utf8_encode($value->fecha),0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(10,5.5,("Item"),1,0,'C');
                        $pdf::Cell(13,5.5,"Código",1,0,'C');
                        $pdf::Cell(68,5.5,"Descripción",1,0,'C');
                        $pdf::Cell(10,5.5,("Und."),1,0,'C');
                        $pdf::Cell(15,5.5,("Cantidad"),1,0,'C');
                        $pdf::Cell(20,5.5,("V. Unitario"),1,0,'C');
                        $pdf::Cell(20,5.5,("P. Unitario"),1,0,'C');
                        $pdf::Cell(20,5.5,("Descuento"),1,0,'C');
                        $pdf::Cell(20,5.5,("Sub Total"),1,0,'C');
                        $pdf::Ln();
                        
                        $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                        $lista2            = $resultado->get();
                        $c=0;
                        foreach($lista2 as $key2 => $v){$c=$c+1;
                            $dscto = 0;
                            $subtotal1 = 0;
                            if ($value->conveniofarmacia_id !== null) {
                                $valaux = round(($v->precio*$v->cantidad), 2);
                                $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                                $dscto = round(($precioaux*$v->cantidad),2);
                                $subtotal1 = round(($dscto*($value->copago/100)),2);
                            }else{
                                $subtotal1 = round(($v->precio*$v->cantidad), 2);
                            }
                            $pdf::SetFont('helvetica','',7.5);
                            $pdf::Cell(10,5.5,$c,1,0,'C');
                            
                            $pdf::Cell(13,5.5,$v->producto->id,1,0,'C');
                            $pdf::Cell(68,5.5,utf8_encode($v->producto->nombre),1,0,'L');
                            $pdf::Cell(10,5.5,("NIU."),1,0,'C');
                            $pdf::Cell(15,5.5,number_format($v->cantidad,2,'.',''),1,0,'R');
                            if($igv>0)
                                $pdf::Cell(20,5.5,number_format($v->precio/1.18,2,'.',''),1,0,'R');
                            else
                                $pdf::Cell(20,5.5,number_format($v->precio,2,'.',''),1,0,'R');
                            $pdf::Cell(20,5.5,number_format($v->precio,2,'.',''),1,0,'R');
                            $pdf::Cell(20,5.5,($dscto),1,0,'R');
                            $pdf::Cell(20,5.5,number_format($subtotal1,2,'.',''),1,0,'R');
                            $pdf::Ln();                    
                        }
                        $letras = new EnLetras();
                        $pdf::SetFont('helvetica','B',8);
                        $valor=$letras->ValorEnLetras($value->total, " SOLES" );//letras
                        
                        $pdf::Cell(116,5,utf8_decode($valor),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(40,5,utf8_decode('Op. Gravada'),0,0,'L');
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                        if($igv>0)
                            $pdf::Cell(20,5,$subtotal,0,0,'R'); 
                        else
                            $pdf::Cell(20,5,'0.00',0,0,'R'); 
                        $pdf::Ln();
                        $pdf::Cell(116,5,'',0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(40,5,utf8_decode('I.G.V'),0,0,'L');
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                        $pdf::Cell(20,5,$igv,0,0,'R');
                        $pdf::Ln();
                        $pdf::Cell(116,5,'',0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(40,5,utf8_decode('Op. Inafecta'),0,0,'L');
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                        if($igv>0)
                            $pdf::Cell(20,5,'0.00',0,0,'R');
                        else
                            $pdf::Cell(20,5,$subtotal,0,0,'R');
                        $pdf::Ln();
                        $pdf::Cell(116,5,'',0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(40,5,utf8_decode('Op. Exonerada'),0,0,'L');
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                        $pdf::Cell(20,5,'0.00',0,0,'R');
                        $pdf::Ln();
                        $pdf::Cell(116,5,'',0,0,'L');
                        $pdf::Cell(40,5,'',0,0,'L');
                        $pdf::Cell(20,5,'',0,0,'C');
                        $pdf::Cell(20,5,'----------------------',0,0,'R');
                        $pdf::Ln();
                        $pdf::Cell(116,5,'',0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(40,5,utf8_decode('Importe Total'),0,0,'L');
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                        $pdf::Cell(20,5,number_format($value->total,2,'.',''),0,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(195,5,'Observaciones de SUNAT:','LRT',0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Cell(195,5,'','LRB',0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Cell(0,5,'Autorizado a ser emisor electrónico mediante R.I. SUNAT Nº 0340050004781',0,0,'L');
                        $pdf::Ln();
                        $pdf::Cell(0,5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Cell(0,5,'Representación Impresa de la Factura Electrónica, consulte en https://sfe.bizlinks.com.pe',0,0,'L');
                        $pdf::Ln();
                        $pdf::Output('Comprobante.pdf');
                    }
                    
                }elseif ($value->tipodocumento_id == 15) {
                    $pdf = new TCPDF();
                    $abreviatura="G";
                    $pdf::SetTitle('Guia Interna');
                    $pdf::AddPage();
                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(50,5.5,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                    $pdf::Cell(70,5.5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                    $pdf::Cell(60,5.5,'Convenio: '.$value->conveniofarmacia->nombre,0,0,'R');
                    $pdf::Ln();
                    $pdf::Ln();
                    if ($value->persona_id !== NULL) {
                        $pdf::Cell(110,6,(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                        $pdf::Cell(110,6,date("d/m/Y",strtotime($value->fecha)).' '.substr($value->created_at, 11),0,0,'L');
                    }else{
                        $pdf::Cell(110,6,(trim("Paciente: ".$value->nombrepaciente)),0,0,'L');
                    }
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(15,5.5,("Cantidad"),1,0,'C');
                    $pdf::Cell(80,5.5,"Producto",1,0,'C');
                    $pdf::Cell(19,5.5,("Prec. Unit."),1,0,'C');
                    $pdf::Cell(22,5.5,("P.Total Dcto"),1,0,'C');
                    $pdf::Cell(23,5.5,("% Copago Pac."),1,0,'C');
                    $pdf::Cell(20,5.5,("Precio Total"),1,0,'C');
                    $pdf::Cell(20,5.5,("Sin IGV"),1,0,'C');
                    $pdf::SetFont('helvetica','',8);
                    $pdf::Ln();
                    $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                    $lista2            = $resultado->get();
                    $totalpago=0;
                    $totaldescuento=0;
                    $totaligv=0;
                    foreach($lista2 as $key2 => $v){
                        $pdf::Cell(15,5.5,number_format($v->cantidad,2,'.',''),0,0,'C');
                        $pdf::Cell(80,5.5,utf8_encode($v->producto->nombre),0,0,'L');
                        $pdf::Cell(19,5.5,number_format($v->precio,2,'.',''),0,0,'C');
                        $valaux = round(($v->precio*$v->cantidad), 2);
                        $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                        $dscto = round(($precioaux*$v->cantidad),2);
                        $totalpago = $totalpago+$dscto;
                        $pdf::Cell(22,5.5,number_format($dscto,2,'.',''),0,0,'C');
                        if($value->copago == 100){
                            $value->copago = 0;
                        }
                        $pdf::Cell(23,5.5,number_format($value->copago,2,'.',''),0,0,'C');
                        if($value->copago>0){
                            $subtotal = round(($dscto*(($value->copago)/100)),2);
                            $subigv = round(($subtotal/1.18),2);
                        }else{
                            $subigv = round(($dscto/1.18),2);
                            $subtotal = 0;
                        }
                        $totaldescuento = $totaldescuento+$subtotal;
                        $totaligv = $totaligv+$subigv;
                        $pdf::Cell(20,5.5,number_format($subtotal,2,'.',''),0,0,'C');
                        $pdf::Cell(20,5.5,number_format($subigv,2,'.',''),0,0,'C');
                        $pdf::Ln();
                    }
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(114,5.5,'',0,0,'C');
                    $pdf::Cell(22,5.5,number_format($totalpago,2,'.',''),0,0,'C');
                    $pdf::Cell(23,5.5,'',0,0,'C');
                    $pdf::Cell(20,5.5,number_format($totaldescuento,2,'.',''),0,0,'C');
                    $pdf::Cell(20,5.5,number_format($totaligv,2,'.',''),0,0,'C');
                    $pdf::Ln();
                    $pdf::Output('Guia.pdf');
                }
                    
            }
        }
    }
    
    public function pdfComprobante2(Request $request){
        $entidad          = 'Venta';
        $id               = Libreria::getParam($request->input('venta_id'),'');
        $guia = $request->input('guia');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->where('movimiento.id', '=', $id);
        $resultado        = $resultado->select('movimiento.*','tipodocumento.nombre as tipodocumento');
        $lista            = $resultado->get();
        //print_r(count($lista));
        if (count($lista) > 0) {     
            foreach($lista as $key => $value){
                if ( ($value->tipodocumento_id == 5) || ($value->tipodocumento_id == 4)) {
                    if ($guia == 'SI') {
                        $pdf = new TCPDF();
                        $abreviatura="B";
                        $pdf::SetTitle('Guia Interna de Salida de Medicamentos');
                        $pdf::AddPage();
                        $pdf::SetFont('helvetica','B',14);
                        $pdf::Cell(0,5.5,("GUIA INTERNA DE SALIDA DE MEDICAMENTOS"),0,0,'C');
                        $pdf::Ln();
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',10);
                        $pdf::Cell(50,5.5,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                        $pdf::Cell(70,5.5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                        $pdf::Cell(60,5.5,'Convenio: '.$value->conveniofarmacia->nombre,0,0,'R');
                        $pdf::Ln();
                        $pdf::Ln();
                        if ($value->persona_id !== NULL) {
                            $pdf::Cell(110,6,(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                            $pdf::Cell(110,6,($value->fecha),0,0,'L');
                        }else{
                            $pdf::Cell(110,6,(trim("Paciente: ".$value->nombrepaciente)),0,0,'L');
                        }
                        $pdf::Ln();
                        $pdf::Cell(240,6,(substr($value->created_at, 11)),0,0,'C');
                        $pdf::Ln();
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(15,5.5,("Cantidad"),1,0,'C');
                        $pdf::Cell(80,5.5,"Producto",1,0,'C');
                        $pdf::Cell(19,5.5,("Prec. Unit."),1,0,'C');
                        $pdf::Cell(22,5.5,("P.Total Dcto"),1,0,'C');
                        $pdf::Cell(23,5.5,("% Copago Pac."),1,0,'C');
                        $pdf::Cell(20,5.5,("Precio Total"),1,0,'C');
                        $pdf::Cell(20,5.5,("Sin IGV"),1,0,'C');
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Ln();
                        $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                        $lista2            = $resultado->get();
                        $totalpago=0;
                        $totaldescuento=0;
                        $totaligv=0;
                        foreach($lista2 as $key2 => $v){
                            $pdf::Cell(15,5.5,number_format($v->cantidad,2,'.',''),0,0,'C');
                            $pdf::Cell(80,5.5,utf8_encode($v->producto->nombre),0,0,'L');
                            $pdf::Cell(19,5.5,number_format($v->precio,2,'.',''),0,0,'C');
                            $valaux = round(($v->precio*$v->cantidad), 2);
                            $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                            $dscto = round(($precioaux*$v->cantidad),2);
                            $totalpago = $totalpago+$dscto;
                            $pdf::Cell(22,5.5,number_format($dscto,2,'.',''),0,0,'C');

                            if($value->copago == 100){
                                $value->copago = 0;
                            }
                            $pdf::Cell(23,5.5,number_format($value->copago,2,'.',''),0,0,'C');
                            
                            if($value->copago>0){
                                $subtotal = round(($dscto*(($value->copago)/100)),2);
                                $subigv = round(($subtotal/1.18),2);
                            }else{
                                $subigv = round(($dscto/1.18),2);
                                $subtotal = 0;
                            }
                            // $subtotal = round(($dscto*($value->copago/100)),2);
                            // $subigv = round(($subtotal/1.18),2);
                            $totaldescuento = $totaldescuento+$subtotal;
                            $totaligv = $totaligv+$subigv;
                            $pdf::Cell(20,5.5,number_format($subtotal,2,'.',''),0,0,'C');
                            $pdf::Cell(20,5.5,number_format($subigv,2,'.',''),0,0,'C');
                            $pdf::Ln();
                        }
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(114,5.5,'',0,0,'C');
                        $pdf::Cell(22,5.5,number_format($totalpago,2,'.',''),0,0,'C');
                        $pdf::Cell(23,5.5,'',0,0,'C');
                        $pdf::Cell(20,5.5,number_format($totaldescuento,2,'.',''),0,0,'C');
                        $pdf::Cell(20,5.5,number_format($totaligv,2,'.',''),0,0,'C');
                        $pdf::Ln();
                        $pdf::Output('Guia.pdf');
                    }else{
                        $pdf = new TCPDF();
                        $pdf::SetTitle('Comprobante');
                        $pdf::AddPage();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,utf8_decode("HOSPITAL PRIVADO JUAN PABLO II SAC"),0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,utf8_decode("RUC: 20480082673"),0,0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,utf8_decode("Tel.: 226070 - 226108"),0,0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,utf8_decode("Dir.: Av. Grau 1461 - Chiclayo"),0,0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,utf8_encode($value->tipodocumento_id=='4'?"FACTURA":"BOLETA").(" ELECTRÓNICA"),0,0,'C');
                        $pdf::Ln();
                        if($value->tipodocumento_id=="4"){
                            $abreviatura="F";
                            $dni=$value->empresa->ruc;
                            $subtotal=number_format($value->subtotal,2,'.','');
                            $igv=number_format($value->igv,2,'.','');
                        }else{
                            $abreviatura="B";
                            /*$subtotal='0.00';
                            $igv='0.00';*/
                            $subtotal=number_format($value->subtotal,2,'.','');
                            $igv=number_format($value->igv,2,'.','');
                            if ($value->persona_id !== NULL) {
                                if(strlen($value->persona->dni)<>8){
                                    $dni='00000000';
                                }else{
                                    $dni=$value->persona->dni;
                                }
                            }else{
                                $dni='00000000';
                            }
                            
                        }

                        $pdf::Cell(60,4,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                        $pdf::Ln();
                        $pdf::Cell(60,4,"====================================",0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(13,4,utf8_encode("Cliente: "),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        if ($value->tipodocumento_id == 5) {
                            if ($value->persona_id !== NULL) {
                                $pdf::MultiCell(47,6,(trim($value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,'L');
                            }else{
                                $pdf::MultiCell(47,6,(trim($value->nombrepaciente)),0,'L');
                            }
                            
                        }else{
                            $pdf::MultiCell(47,6,(trim($value->empresa->bussinesname)),0,'L');
                        }
                        
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(13,4,utf8_encode($abreviatura=="F"?"RUC":"DNI".": "),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(47,4,utf8_encode($dni),0,0,'L');
                        $pdf::Ln();
                        if($value->tipodocumento_id=="4"){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(13,4,utf8_encode("Paciente: "),0,0,'L');
                            $pdf::SetFont('helvetica','B',8);
                            if ($value->persona_id>0 && $value->persona_id !== NULL) {
                                $pdf::MultiCell(47,6,(trim($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,'L');
                            }else{
                                $pdf::MultiCell(47,6,(trim($value->nombrepaciente)),0,'L');
                            }
                            
                        }
                        $estadopago = '';
                        
                        if ($value->formapago == 'P' || $value->estadopago=='PP') {
                            $estadopago = 'Pendiente';
                        }else {
                            if ($value->formapago == 'C') {
                                $estadopago = 'Pagado - Contado';
                            }else{
                            $movaux = Movimiento::find($value->movimiento_id);
                                $voucher = $movaux->voucher;
                                $estadopago = 'Pagado -'.$value->tipotarjeta.' - '.$value->tarjeta.' - '.$voucher;
                            }
                            
                        }
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(14,4,"Dirección:",0,0,'L');
                        $pdf::SetFont('helvetica','B',9);
                        if ($value->tipodocumento_id == 5) {
                            if ($value->persona_id !== NULL) {
                                $pdf::Multicell(47,6,trim($value->persona->direccion),0,'L');
                            }else{
                                $pdf::Cell(47,4,(trim('')),0,0,'L');
                                $pdf::Ln();
                            }
                            
                        }else{
                            $pdf::Multicell(47,4,trim($value->empresa->direccion),0,'L');
                        }
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(13,4,"Fecha: ",0,0,'R');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(47,4,date("d/m/Y",strtotime($value->fecha)),0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(13,4,"Cond.:",0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(47,4,$estadopago,0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,"====================================",0,0,'L');
                        $pdf::Ln();
                        $pdf::Cell(20,4,("Descripción"),0,0,'C');
                        $pdf::Cell(10,4,("Cant"),0,0,'C');
                        $pdf::Cell(15,4,("P. Unit"),0,0,'C');
                        $pdf::Cell(15,4,("Total"),0,0,'C');
                        $pdf::Ln();
                        
                        $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                        $lista2            = $resultado->get();
                        $c=0;
                        foreach($lista2 as $key2 => $v){$c=$c+1;
                            $dscto = 0;
                            $subtotal1 = 0;
                            if ($value->conveniofarmacia_id !== null && $value->descuentokayros>0) {
                                $valaux = round(($v->precio*$v->cantidad), 2);
                                $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                                $dscto = round(($precioaux*$v->cantidad),2);
                                $subtotal1 = round(($dscto*($value->copago/100)),2);
                                $v->precio=round($subtotal1 / $v->cantidad,2);
                            }else{
                                $subtotal1 = round(($v->precio*$v->cantidad), 2);
                            }
                            $pdf::SetFont('helvetica','B',8);
                            $nombre=$v->producto->nombre;
                            if(strlen($nombre)<50){
                                $pdf::Cell(60,4,utf8_encode($nombre),0,0,'L');
                                $pdf::Ln();
                            }else{
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();
                                $pdf::Multicell(60,6,utf8_encode($nombre),0,'L');
                            }                      
                            $pdf::Cell(20,4,"",0,0,'R');      
                            $pdf::Cell(10,4,number_format($v->cantidad,2,'.',''),0,0,'R');
                            $pdf::Cell(15,4,number_format($v->precio,2,'.',''),0,0,'R');
                            $pdf::Cell(15,4,number_format($subtotal1,2,'.',''),0,0,'R');
                            $pdf::Ln();                    
                        }
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,"====================================",0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(30,4,utf8_decode('Op. Gravada'),0,0,'L');
                        $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        if($igv>0)
                            $pdf::Cell(20,4,$subtotal,0,0,'R');
                        else
                            $pdf::Cell(20,4,'0.00',0,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(30,4,utf8_decode('I.G.V'),0,0,'L');
                        $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(20,4,$igv,0,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(30,4,utf8_decode('Op. Inafecta'),0,0,'L');
                        $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        if($igv>0)
                            $pdf::Cell(20,5,'0.00',0,0,'R');
                        else
                            $pdf::Cell(20,5,$subtotal,0,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(30,4,utf8_decode('Op. Exonerada'),0,0,'L');
                        $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(20,4,'0.00',0,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(30,4,utf8_decode('Total'),0,0,'L');
                        $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(20,4,number_format($value->total,2,'.',''),0,0,'R');
                        $pdf::Ln();
                        
                        $letras = new EnLetras();
                        $pdf::SetFont('helvetica','B',8);
                        $valor="SON: ".$letras->ValorEnLetras($value->total, "SOLES" );//letras
                        if(strlen($valor)>40){
                            $pdf::MultiCell(60,6,utf8_decode($valor),0,'L');
                        }else{
                            $pdf::Cell(60,4,utf8_decode($valor),0,0,'L');
                            $pdf::Ln();
                        }

                        $pdf::Cell(60,5,'Usuario: '.$value->responsable->nombres,0,0,'L');
                        $pdf::Ln();
                        $pdf::Cell(60,5,$value->created_at,0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,"====================================",0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::MultiCell(60,6,('Autorizado a ser emisor electrónico mediante R.I. SUNAT Nº 0340050004781'),0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::MultiCell(60,6,('Representación Impresa de la Factura Electrónica, consulte en https://www.hospitaljuanpablo.pe'),0,'L');
                        $pdf::Output('Comprobante.pdf');
                    }
                    
                }elseif ($value->tipodocumento_id == 15) {
                    $pdf = new TCPDF();
                    $abreviatura="G";
                    $pdf::SetTitle('Guia Interna');
                    $pdf::AddPage();
                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(50,5.5,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                    $pdf::Cell(70,5.5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                    $pdf::Cell(60,5.5,'Convenio: '.$value->conveniofarmacia->nombre,0,0,'R');
                    $pdf::Ln();
                    $pdf::Ln();
                    if ($value->persona_id !== NULL) {
                        $pdf::Cell(110,6,(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                        $pdf::Cell(110,6,date("d/m/Y",strtotime($value->fecha)).' '.substr($value->created_at, 11),0,0,'L');
                    }else{
                        $pdf::Cell(110,6,(trim("Paciente: ".$value->nombrepaciente)),0,0,'L');
                    }
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',9);
  
                    $pdf::Cell(15,5.5,("Cantidad"),1,0,'C');
                    $pdf::Cell(80,5.5,"Producto",1,0,'C');
                    $pdf::Cell(19,5.5,("Prec. Unit."),1,0,'C');
                    $pdf::Cell(22,5.5,("P.Total Dcto"),1,0,'C');
                    $pdf::Cell(23,5.5,("% Copago Pac."),1,0,'C');
                    $pdf::Cell(20,5.5,("Precio Total"),1,0,'C');
                    $pdf::Cell(20,5.5,("Sin IGV"),1,0,'C');
                    $pdf::SetFont('helvetica','',8);
                    $pdf::Ln();
                    $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                    $lista2            = $resultado->get();
                    $totalpago=0;
                    $totaldescuento=0;
                    $totaligv=0;
                    foreach($lista2 as $key2 => $v){
                        $pdf::Cell(15,5.5,number_format($v->cantidad,2,'.',''),0,0,'C');
                        $pdf::Cell(80,5.5,utf8_encode($v->producto->nombre),0,0,'L');
                        $pdf::Cell(19,5.5,number_format($v->precio,2,'.',''),0,0,'C');
                        $valaux = round(($v->precio*$v->cantidad), 2);
                        $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                        $dscto = round(($precioaux*$v->cantidad),2);
                        $totalpago = $totalpago+$dscto;
                        $pdf::Cell(22,5.5,number_format($dscto,2,'.',''),0,0,'C');
                        if($value->copago == 100){
                            $value->copago = 0;
                        }
                        $pdf::Cell(23,5.5,number_format($value->copago,2,'.',''),0,0,'C');
                        
                        if($value->copago>0){
                            $subtotal = round(($dscto*(($value->copago)/100)),2);
                            $subigv = round(($subtotal/1.18),2);
                        }else{
                            $subigv = round(($dscto/1.18),2);
                            $subtotal = 0;
                        }
                        // $subtotal = round(($dscto*($value->copago/100)),2);
                        // $subigv = round(($subtotal/1.18),2);
                        $totaldescuento = $totaldescuento+$subtotal;
                        $totaligv = $totaligv+$subigv;
                        $pdf::Cell(20,5.5,number_format($subtotal,2,'.',''),0,0,'C');
                        $pdf::Cell(20,5.5,number_format($subigv,2,'.',''),0,0,'C');
                        $pdf::Ln();
                    }
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(114,5.5,'',0,0,'C');
                    $pdf::Cell(22,5.5,number_format($totalpago,2,'.',''),0,0,'C');
                    $pdf::Cell(23,5.5,'',0,0,'C');
                    $pdf::Cell(20,5.5,number_format($totaldescuento,2,'.',''),0,0,'C');
                    $pdf::Cell(20,5.5,number_format($totaligv,2,'.',''),0,0,'C');
                    $pdf::Ln();
                    $pdf::Output('Guia.pdf');
                }
                    
            }
        }
    }

    public function pdfComprobante4(Request $request){
        $entidad          = 'Venta';
        $id               = Libreria::getParam($request->input('venta_id'),'');
        $guia = $request->input('guia');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->where('movimiento.id', '=', $id);
        $resultado        = $resultado->select('movimiento.*','tipodocumento.nombre as tipodocumento');
        $lista            = $resultado->get();
        //print_r(count($lista));
        if (count($lista) > 0) {     
            foreach($lista as $key => $value){
                if ( ($value->tipodocumento_id == 5) || ($value->tipodocumento_id == 4)) {
                    if ($guia == 'SI') {
                        $pdf = new TCPDF();
                        $abreviatura="B";
                        $pdf::SetTitle('Guia Interna de Salida de Medicamentos');
                        $pdf::AddPage();
                        $pdf::SetFont('helvetica','B',14);
                        $pdf::Cell(0,5.5,("GUIA INTERNA DE SALIDA DE MEDICAMENTOS"),0,0,'C');
                        $pdf::Ln();
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',10);
                        $pdf::Cell(50,5.5,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                        $pdf::Cell(70,5.5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                        $pdf::Cell(60,5.5,'Convenio: '.$value->conveniofarmacia->nombre,0,0,'R');
                        $pdf::Ln();
                        $pdf::Ln();
                        if ($value->persona_id !== NULL) {
                            $pdf::Cell(110,6,(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                            $pdf::Cell(110,6,($value->fecha),0,0,'L');
                        }else{
                            $pdf::Cell(110,6,(trim("Paciente: ".$value->nombrepaciente)),0,0,'L');
                        }
                        $pdf::Ln();
                        $pdf::Cell(240,6,(substr($value->created_at, 11)),0,0,'C');
                        $pdf::Ln();
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(15,5.5,("Cantidad"),1,0,'C');
                        $pdf::Cell(80,5.5,"Producto",1,0,'C');
                        $pdf::Cell(19,5.5,("Prec. Unit."),1,0,'C');
                        $pdf::Cell(22,5.5,("P.Total Dcto"),1,0,'C');
                        $pdf::Cell(23,5.5,("% Copago Pac."),1,0,'C');
                        $pdf::Cell(20,5.5,("Precio Total"),1,0,'C');
                        $pdf::Cell(20,5.5,("Sin IGV"),1,0,'C');
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Ln();
                        $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                        $lista2            = $resultado->get();
                        $totalpago=0;
                        $totaldescuento=0;
                        $totaligv=0;
                        foreach($lista2 as $key2 => $v){
                            $pdf::Cell(15,5.5,number_format($v->cantidad,2,'.',''),0,0,'C');
                            $pdf::Cell(80,5.5,utf8_encode($v->producto->nombre),0,0,'L');
                            $pdf::Cell(19,5.5,number_format($v->precio,2,'.',''),0,0,'C');
                            $valaux = round(($v->precio*$v->cantidad), 2);
                            $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                            $dscto = round(($precioaux*$v->cantidad),2);
                            $totalpago = $totalpago+$dscto;
                            $pdf::Cell(22,5.5,number_format($dscto,2,'.',''),0,0,'C');
                            if($value->copago == 100){
                                $value->copago = 0;
                            }
                            $pdf::Cell(23,5.5,number_format($value->copago,2,'.',''),0,0,'C');
                            
                            if($value->copago>0){
                                $subtotal = round(($dscto*(($value->copago)/100)),2);
                                $subigv = round(($subtotal/1.18),2);
                            }else{
                                $subigv = round(($dscto/1.18),2);
                                $subtotal = 0;
                            }
                            // $subtotal = round(($dscto*($value->copago/100)),2);
                            // $subigv = round(($subtotal/1.18),2);
                            $totaldescuento = $totaldescuento+$subtotal;
                            $totaligv = $totaligv+$subigv;
                            $pdf::Cell(20,5.5,number_format($subtotal,2,'.',''),0,0,'C');
                            $pdf::Cell(20,5.5,number_format($subigv,2,'.',''),0,0,'C');
                            $pdf::Ln();
                        }
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(114,5.5,'',0,0,'C');
                        $pdf::Cell(22,5.5,number_format($totalpago,2,'.',''),0,0,'C');
                        $pdf::Cell(23,5.5,'',0,0,'C');
                        $pdf::Cell(20,5.5,number_format($totaldescuento,2,'.',''),0,0,'C');
                        $pdf::Cell(20,5.5,number_format($totaligv,2,'.',''),0,0,'C');
                        $pdf::Ln();
                        $pdf::Output('Guia.pdf');
                    }else{
                        $pdf = new TCPDF();
                        $pdf::SetTitle('Comprobante');
                        $pdf::AddPage();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,utf8_decode("HOSPITAL PRIVADO JUAN PABLO II SAC"),0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,utf8_decode("RUC: 20480082673"),0,0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,utf8_decode("Tel.: 226070 - 226108"),0,0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,utf8_decode("Dir.: Av. Grau 1461 - Chiclayo"),0,0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,utf8_encode($value->tipodocumento_id=='4'?"FACTURA":"BOLETA").(" ELECTRÓNICA"),0,0,'C');
                        $pdf::Ln();
                        if($value->tipodocumento_id=="4"){
                            $abreviatura="F";
                            $dni=$value->empresa->ruc;
                            $subtotal=number_format($value->subtotal,2,'.','');
                            $igv=number_format($value->igv,2,'.','');
                        }else{
                            $abreviatura="B";
                            /*$subtotal='0.00';
                            $igv='0.00';*/
                            $subtotal=number_format($value->subtotal,2,'.','');
                            $igv=number_format($value->igv,2,'.','');
                            if ($value->persona_id !== NULL) {
                                if(strlen($value->persona->ruc)<>11){
                                    $dni='12345678910';
                                }else{
                                    $dni=$value->persona->ruc;
                                }
                            }else{
                                $dni='12345678910';
                            }
                            
                        }

                        $pdf::Cell(60,4,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                        $pdf::Ln();
                        $pdf::Cell(60,4,"====================================",0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(13,4,utf8_encode("Cliente: "),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::MultiCell(47,6,"GLOBAL MEDICAL MANAGMENT",0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(13,4,utf8_encode($abreviatura=="F"?"RUC":"RUC".": "),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(47,4,utf8_encode($dni),0,0,'L');
                        $pdf::Ln();
                        $pdf::Cell(13,4,utf8_encode("Paciente: "),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        if ($value->tipodocumento_id == 5) {
                            if ($value->persona_id !== NULL) {
                                $pdf::MultiCell(47,6,(trim($value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,'L');
                            }else{
                                $pdf::MultiCell(47,6,(trim($value->nombrepaciente)),0,'L');
                            }
                            
                        }else{
                            $pdf::MultiCell(47,6,(trim($value->empresa->bussinesname)),0,'L');
                        }
                        if($value->tipodocumento_id=="4"){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(13,4,utf8_encode("Paciente: "),0,0,'L');
                            $pdf::SetFont('helvetica','B',8);
                            if ($value->persona_id>0 && $value->persona_id !== NULL) {
                                $pdf::MultiCell(47,6,(trim($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,'L');
                            }else{
                                $pdf::MultiCell(47,6,(trim($value->nombrepaciente)),0,'L');
                            }
                            
                        }
                        $estadopago = '';
                        
                        if ($value->formapago == 'P') {
                            $estadopago = 'Pendiente';
                        }else {
                            if ($value->formapago == 'C') {
                                $estadopago = 'Pagado - Contado';
                            }else{
                            $movaux = Movimiento::find($value->movimiento_id);
                                $voucher = $movaux->voucher;
                                $estadopago = 'Pagado -'.$value->tipotarjeta.' - '.$value->tarjeta.' - '.$voucher;
                            }
                            
                        }
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(16,4,"Dirección:",0,0,'L');
                        $pdf::SetFont('helvetica','B',9);
                        if ($value->tipodocumento_id == 5) {
                            if ($value->persona_id !== NULL) {
                                $pdf::Multicell(47,6,'-',0,'L');
                            }else{
                                $pdf::Cell(47,4,'-',0,0,'L');
                                $pdf::Ln();
                            }
                            
                        }else{
                            $pdf::Multicell(47,4,trim($value->empresa->direccion),0,'L');
                        }
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(13,4,"Fecha: ",0,0,'R');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(47,4,date("d/m/Y",strtotime($value->fecha)),0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(13,4,"Cond.:",0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(47,4,$estadopago,0,0,'L');
                        $pdf::Ln();
                        $pdf::Cell(14,4,utf8_encode("Convenio: "),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::MultiCell(47,6,"GMMI-GLOBAL MEDICAL MANAGEMENT",0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,"====================================",0,0,'L');
                        $pdf::Ln();
                        $pdf::Cell(20,4,("Descripción"),0,0,'C');
                        $pdf::Cell(10,4,("Cant"),0,0,'C');
                        $pdf::Cell(15,4,("P. Unit"),0,0,'C');
                        $pdf::Cell(15,4,("Total"),0,0,'C');
                        $pdf::Ln();
                        
                        $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                        $lista2            = $resultado->get();
                        $c=0;
                        foreach($lista2 as $key2 => $v){$c=$c+1;
                            $dscto = 0;
                            $subtotal1 = 0;
                            if ($value->conveniofarmacia_id !== null) {
                                $valaux = round(($v->precio*$v->cantidad), 2);
                                $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                                $dscto = round(($precioaux*$v->cantidad),2);
                                $subtotal1 = round(($dscto*($value->copago/100)),2);
                                $v->precio=round($subtotal1 / $v->cantidad,2);
                            }else{
                                $subtotal1 = round(($v->precio*$v->cantidad), 2);
                            }
                            $pdf::SetFont('helvetica','B',8);
                            $nombre=$v->producto->nombre;
                            if(strlen($nombre)<50){
                                $pdf::Cell(60,4,utf8_encode($nombre),0,0,'L');
                                $pdf::Ln();
                            }else{
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();
                                $pdf::Multicell(60,6,utf8_encode($nombre),0,'L');
                            }                      
                            $pdf::Cell(20,4,"",0,0,'R');      
                            $pdf::Cell(10,4,number_format($v->cantidad,2,'.',''),0,0,'R');
                            $pdf::Cell(15,4,number_format($v->precio,2,'.',''),0,0,'R');
                            $pdf::Cell(15,4,number_format($subtotal1,2,'.',''),0,0,'R');
                            $pdf::Ln();                    
                        }
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,"====================================",0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(30,4,utf8_decode('Op. Gravada'),0,0,'L');
                        $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        if($igv>0)
                            $pdf::Cell(20,4,$subtotal,0,0,'R');
                        else
                            $pdf::Cell(20,4,'0.00',0,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(30,4,utf8_decode('I.G.V'),0,0,'L');
                        $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(20,4,$igv,0,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(30,4,utf8_decode('Op. Inafecta'),0,0,'L');
                        $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        if($igv>0)
                            $pdf::Cell(20,5,'0.00',0,0,'R');
                        else
                            $pdf::Cell(20,5,$subtotal,0,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(30,4,utf8_decode('Op. Exonerada'),0,0,'L');
                        $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(20,4,'0.00',0,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(30,4,utf8_decode('Total'),0,0,'L');
                        $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(20,4,number_format($value->total,2,'.',''),0,0,'R');
                        $pdf::Ln();
                        
                        $letras = new EnLetras();
                        $pdf::SetFont('helvetica','B',8);
                        $valor="SON: ".$letras->ValorEnLetras($value->total, "SOLES" );//letras
                        if(strlen($valor)>40){
                            $pdf::MultiCell(60,6,utf8_decode($valor),0,'L');
                        }else{
                            $pdf::Cell(60,4,utf8_decode($valor),0,0,'L');
                            $pdf::Ln();
                        }

                        $pdf::Cell(60,5,'Usuario: '.$value->responsable->nombres,0,0,'L');
                        $pdf::Ln();
                        $pdf::Cell(60,5,$value->created_at,0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(60,4,"====================================",0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::MultiCell(60,6,('Autorizado a ser emisor electrónico mediante R.I. SUNAT Nº 0340050004781'),0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::MultiCell(60,6,('Representación Impresa de la Factura Electrónica, consulte en https://www.hospitaljuanpablo.pe'),0,'L');
                        $pdf::Output('Comprobante.pdf');
                    }
                    
                }elseif ($value->tipodocumento_id == 15) {
                    $pdf = new TCPDF();
                    $abreviatura="G";
                    $pdf::SetTitle('Guia Interna');
                    $pdf::AddPage();
                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(50,5.5,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                    $pdf::Cell(70,5.5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                    $pdf::Cell(60,5.5,'Convenio: '.$value->conveniofarmacia->nombre,0,0,'R');
                    $pdf::Ln();
                    $pdf::Ln();
                    if ($value->persona_id !== NULL) {
                        $pdf::Cell(110,6,(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                        $pdf::Cell(110,6,date("d/m/Y",strtotime($value->fecha)).' '.substr($value->created_at, 11),0,0,'L');
                    }else{
                        $pdf::Cell(110,6,(trim("Paciente: ".$value->nombrepaciente)),0,0,'L');
                    }
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(15,5.5,("Cantidad"),1,0,'C');
                    $pdf::Cell(80,5.5,"Producto",1,0,'C');
                    $pdf::Cell(19,5.5,("Prec. Unit."),1,0,'C');
                    $pdf::Cell(22,5.5,("P.Total Dcto"),1,0,'C');
                    $pdf::Cell(23,5.5,("% Copago Pac."),1,0,'C');
                    $pdf::Cell(20,5.5,("Precio Total"),1,0,'C');
                    $pdf::Cell(20,5.5,("Sin IGV"),1,0,'C');
                    $pdf::SetFont('helvetica','',8);
                    $pdf::Ln();
                    $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                    $lista2            = $resultado->get();
                    $totalpago=0;
                    $totaldescuento=0;
                    $totaligv=0;
                    foreach($lista2 as $key2 => $v){
                        $pdf::Cell(15,5.5,number_format($v->cantidad,2,'.',''),0,0,'C');
                        $pdf::Cell(80,5.5,utf8_encode($v->producto->nombre),0,0,'L');
                        $pdf::Cell(19,5.5,number_format($v->precio,2,'.',''),0,0,'C');
                        $valaux = round(($v->precio*$v->cantidad), 2);
                        $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                        $dscto = round(($precioaux*$v->cantidad),2);
                        $totalpago = $totalpago+$dscto;
                        $pdf::Cell(22,5.5,number_format($dscto,2,'.',''),0,0,'C');
                        if($value->copago == 100){
                            $value->copago = 0;
                        }
                        $pdf::Cell(23,5.5,number_format($value->copago,2,'.',''),0,0,'C');
                        
                        if($value->copago>0){
                            $subtotal = round(($dscto*(($value->copago)/100)),2);
                            $subigv = round(($subtotal/1.18),2);
                        }else{
                            $subigv = round(($dscto/1.18),2);
                            $subtotal = 0;
                        }
                        // $subtotal = round(($dscto*($value->copago/100)),2);
                        // $subigv = round(($subtotal/1.18),2);
                        $totaldescuento = $totaldescuento+$subtotal;
                        $totaligv = $totaligv+$subigv;
                        $pdf::Cell(20,5.5,number_format($subtotal,2,'.',''),0,0,'C');
                        $pdf::Cell(20,5.5,number_format($subigv,2,'.',''),0,0,'C');
                        $pdf::Ln();
                    }
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(114,5.5,'',0,0,'C');
                    $pdf::Cell(22,5.5,number_format($totalpago,2,'.',''),0,0,'C');
                    $pdf::Cell(23,5.5,'',0,0,'C');
                    $pdf::Cell(20,5.5,number_format($totaldescuento,2,'.',''),0,0,'C');
                    $pdf::Cell(20,5.5,number_format($totaligv,2,'.',''),0,0,'C');
                    $pdf::Ln();
                    $pdf::Output('Guia.pdf');
                }
                    
            }
        }
    }

    public function pdfComprobante3(Request $request){

        $user = Auth::user();
        $entidad          = 'Venta';
        $id               = Libreria::getParam($request->input('venta_id'),'');
        $guia = $request->input('guia');
        $tipousuario = $user->usertype_id; 
        if ($tipousuario == 11) {
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->where('movimiento.id', '=', $id);
            $resultado        = $resultado->select('movimiento.*','tipodocumento.nombre as tipodocumento');
            $lista            = $resultado->get();
            //print_r(count($lista));
            if (count($lista) > 0) {     
                foreach($lista as $key => $value){
                    if ( ($value->tipodocumento_id == 5) || ($value->tipodocumento_id == 4)) {
                        if ($guia == 'SI') {
                            $pdf = new TCPDF();
                            $abreviatura="B";
                            $pdf::SetTitle('Guia Interna de Salida de Medicamentos');
                            $pdf::AddPage("L");
                            $pdf::SetFont('helvetica','B',12);
                            $pdf::Cell(0,6,("GUIA INTERNA DE SALIDA DE MEDICAMENTOS"),0,0,'C');
                            $pdf::Ln();
                            $pdf::Ln();
                            $pdf::Cell(10);
                            $pdf::SetFont('helvetica','B',10);
                            $pdf::Cell(50,6,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                            $pdf::Cell(70,6,'Usuario: '.$value->responsable->nombres,0,0,'R');
                            $pdf::Cell(60,6,'Convenio: '.$value->conveniofarmacia->nombre,0,0,'R');
                            $pdf::Ln();
                            $pdf::Cell(10);
                            if ($value->persona_id !== NULL) {
                                $pdf::Cell(155,6,(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                                //$pdf::Cell(110,6,($value->fecha),0,0,'L');
                            }else{
                                $pdf::Cell(155,6,(trim("Paciente: ".$value->nombrepaciente)),0,0,'L');
                            }
                            $pdf::Cell(30,4,date("d/m/Y",strtotime($value->fecha)).' '.substr($value->created_at, 11),0,0,'L');
                            $pdf::Ln();
                            $pdf::Ln();
                            $pdf::SetFont('helvetica','B',11);
                            $pdf::Cell(10);
                            $pdf::Cell(20,6,("CANTIDAD"),0,0,'C');
                            $pdf::Cell(100,6,"PRODUCTO",0,0,'L');
                            $pdf::Cell(25,6,("PRECIO UNI"),0,0,'C');
                            $pdf::Cell(15,6,("P/DSCTO"),0,0,'C');
                            $pdf::Cell(20,6,("% COPAGO"),0,0,'C');
                            $pdf::Cell(20,6,("PRECIO TOTAL"),0,0,'C');
                            $pdf::Cell(20,6,("SIN IGV"),0,0,'C');
                            $pdf::SetFont('helvetica','B',11);
                            $pdf::Ln();
                            $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                            $lista2            = $resultado->get();
                            $totalpago=0;
                            $totalcopago=0;
                            $totaldescuento=0;
                            $totaligv=0;
                            foreach($lista2 as $key2 => $v){
                                $pdf::Cell(10);
                                $pdf::Cell(20,6,number_format($v->cantidad,2,'.',''),0,0,'C');
                                $pdf::Cell(100,6,utf8_encode($v->producto->nombre),0,0,'L');
                                $pdf::Cell(25,6,number_format($v->precio,2,'.',''),0,0,'C');
                                $valaux = round(($v->precio*$v->cantidad), 2);
                                $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                                $dscto = round(($precioaux*$v->cantidad),2);
                                $totalpago = $totalpago+$dscto;
                                $pdf::Cell(15,6,number_format($dscto,2,'.',''),0,0,'C');
                                if($value->copago == 100){
                                    $value->copago = 0;
                                }
                                $pdf::Cell(20,6,number_format($value->copago,2,'.',''),0,0,'C');
                                
                                if($value->copago>0){
                                    $subtotal = round(($dscto*(($value->copago)/100)),2);
                                    $subigv = round(($subtotal/1.18),2);
                                }else{
                                    $subigv = round(($dscto/1.18),2);
                                    $subtotal = 0;
                                }
                                // $subtotal = round(($dscto*($value->copago/100)),2);
                                // $subigv = round(($subtotal/1.18),2);
                                $totalcopago+=$value->copago;
                                $totaldescuento = $totaldescuento+$subtotal;
                                $totaligv = $totaligv+$subigv;
                                $pdf::Cell(20,6,number_format($subtotal,2,'.',''),0,0,'C');
                                $pdf::Cell(20,6,number_format($subigv,2,'.',''),0,0,'C');
                                $pdf::Ln();
                            }
                            $pdf::SetFont('helvetica','B',11);
                            $pdf::Cell(155,6,'',0,0,'C');
                            $pdf::Cell(15,6,number_format($totalpago,2,'.',''),0,0,'C');
                            //$pdf::Cell(20,6,number_format($totalcopago,2,'.',''),0,0,'C');
                            $pdf::Cell(20,6,"",0,0,'C');
                            $pdf::Cell(20,6,number_format($totaldescuento,2,'.',''),0,0,'C');
                            $pdf::Cell(20,6,number_format($totaligv,2,'.',''),0,0,'C');
                            $pdf::Ln();
                            $pdf::Output('Guia.pdf');
                        }else{
                            $pdf = new TCPDF();
                            $pdf::SetTitle('Comprobante');
                            //$pdf::AddPage();
                            $pdf::AddPage("L");
                            $pdf::SetFont('helvetica','B',13);
                            $pdf::Ln();
                            $pdf::Cell(180,6,"",0,0,'C');
                            if($value->tipodocumento_id=="4"){//Factura
                                $abreviatura="F";
                                $dni=$value->empresa->ruc;
                                $subtotal=number_format($value->total/1.18,2,'.','');
                                $igv=number_format($value->total - $subtotal,2,'.','');
                                $nombrepersona = '';
                                $direccion = $value->empresa->direccion;
                                $dnipaciente = '';
                                if ($value->persona_id !== NULL) {
                                    $nombrepersona = $value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres;
                                    $dnipaciente = $value->persona->dni;
                                }else{
                                    $nombrepersona = $value->nombrepaciente;
                                }
                                $pdf::setXY(150,20);
                                $pdf::Cell(10,4,utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                                $pdf::Ln();
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(35,4,"RAZON SOCIAL: ",0,0,'L');
                                
                                $pdf::Cell(180,4,(trim($value->empresa->bussinesname)),0,0,'L');
                                $pdf::setX(148);
                                $pdf::Cell(25,4,"RUC: ",0,0,'L');
                                $pdf::Cell(30,4,$dni,0,0,'L');
                                $pdf::Ln();
                                $pdf::Cell(35,4,"DIRECCION: ",0,0,'L');
                                $pdf::Cell(180,4,(trim($direccion)),0,0,'L');
                                $pdf::setX(148);
                                  $pdf::Cell(25,4,"FECHA: ",0,0,'L');
                                $pdf::Cell(30,4,date("d/m/Y",strtotime($value->fecha)).' '.substr($value->created_at, 11),0,0,'L');
                                $pdf::Ln();
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(35,6,utf8_encode("PACIENTE: "),0,0,'L');
                                
                                $pdf::Cell(180,6,($nombrepersona),0,0,'L');
                                $pdf::setX(148);
                                $pdf::Cell(25,4,"DNI: ",0,0,'L');
                                $pdf::Cell(30,4,utf8_encode($dnipaciente),0,0,'L');
                                $pdf::Ln();
                                $pdf::Cell(35,4,"CONVENIO: ",0,0,'L');
                                $pdf::Cell(180,4,trim(""),0,0,'L');
                                $pdf::setX(148);
                                $pdf::Cell(25,4,"HISTORIA: ",0,0,'L');
                                $pdf::Cell(30,4,utf8_encode(""),0,0,'L');
                                $pdf::Ln();
                                $movaux = Movimiento::find($value->movimiento_id);
                                if ($movaux != null) {
                                    $voucher = $movaux->voucher;
                                    $estadopago = 'Pagado -'.$value->tipotarjeta.' - '.$value->tarjeta.' - '.$voucher;
                                    if($movaux->tarjeta!="")
                                        $pdf::Cell(50,6,trim($movaux->tarjeta." - ".$movaux->tipotarjeta),0,0,'L');
                                }
                                    
                                $pdf::Cell(0,4,$value->responsable->nombres,0,0,'R');
                                $pdf::Ln(15);
                                $pdf::setX(0);
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(5,5.5,"",0,0,'C');
                                $pdf::Cell(15,5.5,("Cant."),0,0,'C');
                                $pdf::Cell(120,5.5,"Descripción",0,0,'C');
                                $pdf::Cell(20,5.5,("P. Unitario"),0,0,'C');
                                $pdf::Cell(20,5.5,("Dscto"),0,0,'C');
                                $pdf::Cell(20,5.5,("Sub Total"),0,0,'C');
                                $pdf::Ln();
                                $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                                $lista2            = $resultado->get();
                                $c=0;
                                foreach($lista2 as $key2 => $v){$c=$c+1;

                                    $dscto = 0;
                                    $subtotal2 = 0;
                                    if ($value->conveniofarmacia_id !== null) {
                                        $valaux = round(($v->precio*$v->cantidad), 2);
                                        $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                                        $dscto = round(($precioaux*$v->cantidad),2);
                                        $subtotal2 = round(($dscto*($value->copago/100)),2);
                                    }else{
                                        $subtotal2 = round(($v->precio*$v->cantidad), 2);
                                    }
                                    $pdf::setX(0);
                                    $pdf::SetFont('helvetica','B',11);
                                    $pdf::Cell(5,4,"",0,0,'C');
                                    $pdf::Cell(15,4,number_format($v->cantidad,2,'.',''),0,0,'C');
                                    $pdf::Cell(120,4,$v->producto->nombre,0,0,'L');
                                    $pdf::Cell(20,4,number_format($v->precio,2,'.',''),0,0,'C');
                                    $pdf::Cell(20,4,number_format($dscto,2,'.',''),0,0,'C');
                                    $pdf::Cell(20,4,number_format($subtotal2,2,'.',''),0,0,'C');
                                    $pdf::Ln('4');               
                                }

                                $pdf::Ln();
                                $letras = new EnLetras();
                                $pdf::SetFont('helvetica','B',11);
                                $valor=$letras->ValorEnLetras($value->total, " SOLES" );//letras
                                $pdf::Cell(15,5.5,"",0,0,'C');
                                $pdf::Cell(125,5,utf8_decode($valor),0,0,'L');
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(20,5.5,"SUBTOTAL: ",0,0,'L');
                                $pdf::Cell(30,5,"S/. ".number_format($subtotal,2,'.',''),0,0,'R');
                                $pdf::Ln();
                                $pdf::Cell(140,5,'',0,0,'L');
                                $pdf::Cell(20,5.5,"IGV: ",0,0,'L');
                                $pdf::Cell(30,5,"S/. ".$igv,0,0,'R');
                                $pdf::Ln();
                                $pdf::Cell(140,5,'',0,0,'L');
                                $pdf::Cell(20,5.5,"TOTAL: ",0,0,'L');
                                $pdf::Cell(30,5,"S/. ".number_format($value->total,2,'.',''),0,0,'R');
                                $pdf::Ln();

                                $pdf::Output('Comprobante.pdf');

                            }else{
                                $abreviatura="B";
                                $subtotal='0.00';
                                $igv='0.00';
                                $nombrepersona = '';
                                $dnipaciente = '-';
                                if ($value->persona_id !== NULL) {
                                    $nombrepersona = $value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres;
                                    $dnipaciente = $value->persona->dni;
                                }else{
                                    $nombrepersona = $value->nombrepaciente;
                                }
                                $pdf::setXY(0,20);
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(40,4,"",0,0,'C');
                                $pdf::Cell(145,4,($nombrepersona),0,0,'L');
                                $pdf::Cell(10,4,utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                                $pdf::Ln();

                                
                                $pdf::setX(40);
                                $pdf::Cell(100,4,utf8_encode($dnipaciente),0,0,'L');
                                $pdf::Cell(37,4,date("d/m/Y",strtotime($value->fecha)).' '.substr($value->created_at, 11),0,0,'L');
                                $pdf::Ln();
                                if($value->tipodocumento_id=="4"){
                                    $pdf::SetFont('helvetica','B',11);
                                    $pdf::Cell(37,6,utf8_encode("Paciente: "),0,0,'L');
                                    $pdf::SetFont('helvetica','',11);
                                    //$ticket = Movimiento::find($value->movimiento_id);
                                    $pdf::Cell(110,6,($nombrepersona),0,0,'L');
                                    $pdf::Ln();
                                }
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(40,4,"",0,0,'C');
                                $movaux = Movimiento::find($value->movimiento_id);
                                $pdf::Cell(50,4,(""),0,0,'L');
                                if ($movaux != null) {
                                    if($movaux->tarjeta!="")
                                    $pdf::Cell(50,6,trim($movaux->tarjeta." - ".$movaux->tipotarjeta),0,0,'L');
                                }
                                
                                $pdf::Cell(0,4,$value->responsable->nombres,0,0,'R');
                                $pdf::Ln();
                                $pdf::setX(10);
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(5,5.5,"",0,0,'C');
                                $pdf::Cell(15,5.5,("Cant."),0,0,'C');
                                $pdf::Cell(120,5.5,"Descripción",0,0,'C');
                                $pdf::Cell(20,5.5,("P. Unitario"),0,0,'C');
                                $pdf::Cell(20,5.5,("Dscto"),0,0,'C');
                                $pdf::Cell(20,5.5,("Sub Total"),0,0,'C');
                                $pdf::Ln();
                                $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                                $lista2            = $resultado->get();
                                $c = 0;
                                foreach($lista2 as $key2 => $v){$c=$c+1;
                                    $dscto = 0;
                                    $subtotal2 = 0;
                                    if ($value->conveniofarmacia_id !== null) {
                                        $valaux = round(($v->precio*$v->cantidad), 2);
                                        $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                                        $dscto = round(($precioaux*$v->cantidad),2);
                                        $subtotal2 = round(($dscto*($value->copago/100)),2);
                                    }else{
                                        $subtotal2 = round(($v->precio*$v->cantidad), 2);
                                    }
                                    $pdf::setX(10);
                                    $pdf::SetFont('helvetica','B',11);
                                    $pdf::Cell(5,4,"",0,0,'C');
                                    $pdf::Cell(15,4,number_format($v->cantidad,2,'.',''),0,0,'C');
                                    if(strlen($v->producto->nombre)<80){
                                        $pdf::Cell(120,4,$v->producto->nombre,0,0,'L');
                                    }else{
                                        $x=$pdf::GetX();
                                        $y=$pdf::GetY();
                                        $pdf::Multicell(120,2,$v->producto->nombre,0,'L');
                                        $pdf::SetXY($x,$y);
                                        $pdf::Cell(120,4,"",0,0,'L');
                                    }
                                    //$pdf::Cell(120,4,$v->producto->nombre,0,0,'L');
                                    $pdf::Cell(20,4,number_format($v->precio,2,'.',''),0,0,'C');
                                    $pdf::Cell(20,4,number_format($dscto,2,'.',''),0,0,'C');
                                    $pdf::Cell(20,4,number_format($subtotal2,2,'.',''),0,0,'C');
                                    $pdf::Ln('4');                     
                                }
                                $pdf::Ln();
                                $letras = new EnLetras();
                                $pdf::SetFont('helvetica','B',11);
                                $valor=$letras->ValorEnLetras($value->total, " SOLES" );//letras
                                $pdf::Cell(15,5.5,"",0,0,'C');
                                $pdf::Cell(140,5,utf8_decode($valor),0,0,'L');
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(30,5,"S/. ".number_format($value->total,2,'.',''),0,0,'R');
                                $pdf::Ln();
                                $pdf::Output('Comprobante.pdf');
                            }
                        }
                        
                    }elseif ($value->tipodocumento_id == 15) {
                        $pdf = new TCPDF();
                        $abreviatura="G";
                        $pdf::SetTitle('Guia Interna');
                        $pdf::AddPage("L");
                        $pdf::SetFont('helvetica','B',11);
                        $pdf::Cell(50,5.5,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                        $pdf::Cell(10);
                        $pdf::Cell(70,5.5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                        $pdf::SetFont('helvetica','B',11);
                        $pdf::Cell(60,5.5,'Convenio: '.$value->conveniofarmacia->nombre,0,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',11);
                        $pdf::Cell(10);
                        if ($value->persona_id !== NULL) {
                            $pdf::Cell(155,6,(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                        }else{
                            $pdf::Cell(155,6,(trim("Paciente: ".$value->nombrepaciente)),0,0,'L');
                        }
                        $pdf::Cell(30,4,date("d/m/Y",strtotime($value->fecha)).' '.substr($value->created_at, 11),0,0,'L');
                        $pdf::Ln();
                        $pdf::Ln(); 
                        $pdf::SetFont('helvetica','B',11);
                        $color='background:rgba(10,215,37,0.50)';
                        $pdf::Cell(10);
                        $pdf::Cell(20,6,("CANTIDAD"),0,0,'C');
                        $pdf::Cell(100,6,"PRODUCTO",0,0,'L');
                        $pdf::Cell(25,6,("PRECIO UNI"),0,0,'C');
                        $pdf::Cell(15,6,("P/DSCTO"),0,0,'C');
                        $pdf::Cell(18,6,("% COPAGO"),0,0,'C');
                        $pdf::Cell(18,6,("PRECIO TOTAL"),0,0,'C');
                        $pdf::Cell(18,6,("SIN IGV"),0,0,'C');
                        $pdf::SetFont('helvetica','B',11);
                        $pdf::Ln();
                        $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                        $lista2            = $resultado->get();
                        $totalpago=0;
                        $totalcopago=0;
                        $totaldescuento=0;
                        $totaligv=0;
                        foreach($lista2 as $key2 => $v){
                            $pdf::Cell(10);
                            $pdf::Cell(20,6,number_format($v->cantidad),0,0,'C');
                            if(strlen($v->producto->nombre)<35){
                                $pdf::Cell(100,6,$v->producto->nombre,0,0,'L');
                            }else{
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();
                                $pdf::Multicell(100,3,$v->producto->nombre,0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(100,6,"",0,0,'L');
                            }
                            //$pdf::Cell(80,12,$v->producto->nombre,0,0,'L');
                            $pdf::Cell(25,6,number_format($v->precio,2,'.',''),0,0,'C');
                            $valaux = round(($v->precio*$v->cantidad), 2);
                            $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                            $dscto = round(($precioaux*$v->cantidad),2);
                            $totalpago = $totalpago+$dscto;
                            $pdf::Cell(15,6,number_format($dscto,2,'.',''),0,0,'C');
                            if($value->copago == 100){
                                $value->copago = 0;
                            }
                            $pdf::Cell(18,6,number_format($value->copago,2,'.',''),0,0,'C');
                            
                            if($value->copago>0){
                                $subtotal = round(($dscto*(($value->copago)/100)),2);
                                $subigv = round(($subtotal/1.18),2);
                            }else{
                                $subigv = round(($dscto/1.18),2);
                                $subtotal = 0;
                            }
                            // $subtotal = round(($dscto*($value->copago/100)),2);
                            // $subigv = round(($subtotal/1.18),2);
                            $totaldescuento = $totaldescuento+$subtotal;
                            $totalcopago+=$value->copago;
                            $totaligv = $totaligv+$subigv;
                            $pdf::Cell(18,6,number_format($subtotal,2,'.',''),0,0,'C');
                            $pdf::Cell(18,6,number_format($subigv,2,'.',''),0,0,'C');
                            $pdf::Ln();
                        }
                        $pdf::SetFont('helvetica','B',11);
                        $pdf::Cell(155,6,'',0,0,'C');
                        $pdf::Cell(15,6,number_format($totalpago,2,'.',''),0,0,'C');
                        //$pdf::Cell(18,6,number_format($totalcopago,2,'.',''),0,0,'C');
                        $pdf::Cell(18,6,"",0,0,'C');
                        $pdf::Cell(18,6,number_format($totaldescuento,2,'.',''),0,0,'C');
                        $pdf::Cell(18,6,number_format($totaligv,2,'.',''),0,0,'C');
                        $pdf::Ln();
                        $pdf::Output('Guia.pdf');
                    }
                        
                }
            }  
        } else {
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->where('movimiento.id', '=', $id);
            $resultado        = $resultado->select('movimiento.*','tipodocumento.nombre as tipodocumento');
            $lista            = $resultado->get();
            //print_r(count($lista));
            if (count($lista) > 0) {     
                foreach($lista as $key => $value){
                    if ( ($value->tipodocumento_id == 5) || ($value->tipodocumento_id == 4)) {
                        if ($guia == 'SI') {
                            $pdf = new TCPDF();
                            $abreviatura="B";
                            $pdf::SetTitle('Guia Interna de Salida de Medicamentos');
                            $pdf::AddPage();
                            $pdf::SetFont('helvetica','B',12);
                            $pdf::Cell(0,6,("GUIA INTERNA DE SALIDA DE MEDICAMENTOS"),0,0,'C');
                            $pdf::Ln();
                            $pdf::Ln();
                            $pdf::SetFont('helvetica','',10);
                            $pdf::Cell(50,6,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                            $pdf::Cell(70,6,'Usuario: '.$value->responsable->nombres,0,0,'R');
                            $pdf::Cell(60,6,'Convenio: '.$value->conveniofarmacia->nombre,0,0,'R');
                            $pdf::Ln();
                            $pdf::Ln();
                            if ($value->persona_id !== NULL) {
                                $pdf::Cell(155,6,(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                                //$pdf::Cell(110,6,($value->fecha),0,0,'L');
                            }else{
                                $pdf::Cell(155,6,(trim("Paciente: ".$value->nombrepaciente)),0,0,'L');
                            }
                            $pdf::Cell(30,4,date("d/m/Y",strtotime($value->fecha)).' '.substr($value->created_at, 11),0,0,'L');
                            $pdf::Ln();
                            $pdf::Ln();
                            $pdf::SetFont('helvetica','B',9);
                            $pdf::Cell(15,5.5,("Cantidad"),1,0,'C');
                            $pdf::Cell(80,5.5,"Producto",1,0,'C');
                            $pdf::Cell(19,5.5,("Prec. Unit."),1,0,'C');
                            $pdf::Cell(22,5.5,("P.Total Dcto"),1,0,'C');
                            $pdf::Cell(23,5.5,("% Copago Pac."),1,0,'C');
                            $pdf::Cell(20,5.5,("Precio Total"),1,0,'C');
                            $pdf::Cell(20,5.5,("Sin IGV"),1,0,'C');
                            $pdf::SetFont('helvetica','',9);
                            $pdf::Ln();
                            $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                            $lista2            = $resultado->get();
                            $totalpago=0;
                            $totalcopago=0;
                            $totaldescuento=0;
                            $totaligv=0;
                            foreach($lista2 as $key2 => $v){
                                $pdf::Cell(15,6,number_format($v->cantidad,2,'.',''),0,0,'C');
                                $pdf::Cell(80,6,utf8_encode($v->producto->nombre),0,0,'L');
                                $pdf::Cell(19,6,number_format($v->precio,2,'.',''),0,0,'C');
                                $valaux = round(($v->precio*$v->cantidad), 2);
                                $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                                $dscto = round(($precioaux*$v->cantidad),2);
                                $totalpago = $totalpago+$dscto;
                                $pdf::Cell(22,6,number_format($dscto,2,'.',''),0,0,'C');
                                if($value->copago == 100){
                                    $value->copago = 0;
                                }
                                $pdf::Cell(23,6,number_format($value->copago,2,'.',''),0,0,'C');
                                
                                if($value->copago>0){
                                    $subtotal = round(($dscto*(($value->copago)/100)),2);
                                    $subigv = round(($subtotal/1.18),2);
                                }else{
                                    $subigv = round(($dscto/1.18),2);
                                    $subtotal = 0;
                                }
                                // $subtotal = round(($dscto*($value->copago/100)),2);
                                // $subigv = round(($subtotal/1.18),2);
                                $totalcopago+=$value->copago;
                                $totaldescuento = $totaldescuento+$subtotal;
                                $totaligv = $totaligv+$subigv;
                                $pdf::Cell(20,6,number_format($subtotal,2,'.',''),0,0,'C');
                                $pdf::Cell(20,6,number_format($subigv,2,'.',''),0,0,'C');
                                $pdf::Ln();
                            }
                            $pdf::SetFont('helvetica','B',9);
                            $pdf::Cell(114,6,'',0,0,'C');
                            $pdf::Cell(22,6,number_format($totalpago,2,'.',''),0,0,'C');
                            //$pdf::Cell(20,6,number_format($totalcopago,2,'.',''),0,0,'C');
                            $pdf::Cell(23,6,"",0,0,'C');
                            $pdf::Cell(20,6,number_format($totaldescuento,2,'.',''),0,0,'C');
                            $pdf::Cell(20,6,number_format($totaligv,2,'.',''),0,0,'C');
                            $pdf::Ln();
                            $pdf::Output('Guia.pdf');
                        }else{
                            $pdf = new TCPDF();
                            $pdf::SetTitle('Comprobante');
                            //$pdf::AddPage();
                            $pdf::AddPage();
                            $pdf::SetFont('helvetica','B',13);
                            $pdf::Ln();
                            $pdf::Cell(180,6,"",0,0,'C');
                            if($value->tipodocumento_id=="4"){//Factura
                                $abreviatura="F";
                                $dni=$value->empresa->ruc;
                                $subtotal=number_format($value->total/1.18,2,'.','');
                                $igv=number_format($value->total - $subtotal,2,'.','');
                                $nombrepersona = '';
                                $direccion = $value->empresa->direccion;
                                $dnipaciente = '';
                                if ($value->persona_id !== NULL) {
                                    $nombrepersona = $value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres;
                                    $dnipaciente = $value->persona->dni;
                                }else{
                                    $nombrepersona = $value->nombrepaciente;
                                }
                                $pdf::setXY(150,20);
                                $pdf::Cell(10,4,utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                                $pdf::Ln();
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(35,4,"RAZON SOCIAL: ",0,0,'L');
                                
                                $pdf::Cell(180,4,(trim($value->empresa->bussinesname)),0,0,'L');
                                $pdf::setX(148);
                                $pdf::Cell(25,4,"RUC: ",0,0,'L');
                                $pdf::Cell(30,4,$dni,0,0,'L');
                                $pdf::Ln();
                                $pdf::Cell(35,4,"DIRECCION: ",0,0,'L');
                                $pdf::Cell(180,4,(trim($direccion)),0,0,'L');
                                $pdf::setX(148);
                                  $pdf::Cell(25,4,"FECHA: ",0,0,'L');
                                $pdf::Cell(30,4,date("d/m/Y",strtotime($value->fecha)).' '.substr($value->created_at, 11),0,0,'L');
                                $pdf::Ln();
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(35,6,utf8_encode("PACIENTE: "),0,0,'L');
                                
                                $pdf::Cell(180,6,($nombrepersona),0,0,'L');
                                $pdf::setX(148);
                                $pdf::Cell(25,4,"DNI: ",0,0,'L');
                                $pdf::Cell(30,4,utf8_encode($dnipaciente),0,0,'L');
                                $pdf::Ln();
                                $pdf::Cell(35,4,"CONVENIO: ",0,0,'L');
                                $pdf::Cell(180,4,trim(""),0,0,'L');
                                $pdf::setX(148);
                                $pdf::Cell(25,4,"HISTORIA: ",0,0,'L');
                                $pdf::Cell(30,4,utf8_encode(""),0,0,'L');
                                $pdf::Ln();
                                $movaux = Movimiento::find($value->movimiento_id);
                                if ($movaux != null) {
                                    $voucher = $movaux->voucher;
                                    $estadopago = 'Pagado -'.$value->tipotarjeta.' - '.$value->tarjeta.' - '.$voucher;
                                    if($movaux->tarjeta!="")
                                        $pdf::Cell(50,6,trim($movaux->tarjeta." - ".$movaux->tipotarjeta),0,0,'L');
                                }
                                    
                                $pdf::Cell(0,4,$value->responsable->nombres,0,0,'R');
                                $pdf::Ln(15);
                                $pdf::setX(0);
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(5,5.5,"",0,0,'C');
                                $pdf::Cell(15,5.5,("Cant."),0,0,'C');
                                $pdf::Cell(120,5.5,"Descripción",0,0,'C');
                                $pdf::Cell(20,5.5,("P. Unitario"),0,0,'C');
                                $pdf::Cell(20,5.5,("Dscto"),0,0,'C');
                                $pdf::Cell(20,5.5,("Sub Total"),0,0,'C');
                                $pdf::Ln();
                                $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                                $lista2            = $resultado->get();
                                $c=0;
                                foreach($lista2 as $key2 => $v){$c=$c+1;

                                    $dscto = 0;
                                    $subtotal2 = 0;
                                    if ($value->conveniofarmacia_id !== null) {
                                        $valaux = round(($v->precio*$v->cantidad), 2);
                                        $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                                        $dscto = round(($precioaux*$v->cantidad),2);
                                        $subtotal2 = round(($dscto*($value->copago/100)),2);
                                    }else{
                                        $subtotal2 = round(($v->precio*$v->cantidad), 2);
                                    }
                                    $pdf::setX(0);
                                    $pdf::SetFont('helvetica','B',11);
                                    $pdf::Cell(5,4,"",0,0,'C');
                                    $pdf::Cell(15,4,number_format($v->cantidad,2,'.',''),0,0,'C');
                                    $pdf::Cell(120,4,$v->producto->nombre,0,0,'L');
                                    $pdf::Cell(20,4,number_format($v->precio,2,'.',''),0,0,'C');
                                    $pdf::Cell(20,4,number_format($dscto,2,'.',''),0,0,'C');
                                    $pdf::Cell(20,4,number_format($subtotal2,2,'.',''),0,0,'C');
                                    $pdf::Ln('4');               
                                }

                                $pdf::Ln();
                                $letras = new EnLetras();
                                $pdf::SetFont('helvetica','B',11);
                                $valor=$letras->ValorEnLetras($value->total, " SOLES" );//letras
                                $pdf::Cell(15,5.5,"",0,0,'C');
                                $pdf::Cell(125,5,utf8_decode($valor),0,0,'L');
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(20,5.5,"SUBTOTAL: ",0,0,'L');
                                $pdf::Cell(30,5,"S/. ".number_format($subtotal,2,'.',''),0,0,'R');
                                $pdf::Ln();
                                $pdf::Cell(140,5,'',0,0,'L');
                                $pdf::Cell(20,5.5,"IGV: ",0,0,'L');
                                $pdf::Cell(30,5,"S/. ".$igv,0,0,'R');
                                $pdf::Ln();
                                $pdf::Cell(140,5,'',0,0,'L');
                                $pdf::Cell(20,5.5,"TOTAL: ",0,0,'L');
                                $pdf::Cell(30,5,"S/. ".number_format($value->total,2,'.',''),0,0,'R');
                                $pdf::Ln();

                                $pdf::Output('Comprobante.pdf');

                            }else{
                                $abreviatura="B";
                                $subtotal='0.00';
                                $igv='0.00';
                                $nombrepersona = '';
                                $dnipaciente = '-';
                                if ($value->persona_id !== NULL) {
                                    $nombrepersona = $value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres;
                                    $dnipaciente = $value->persona->dni;
                                }else{
                                    $nombrepersona = $value->nombrepaciente;
                                }
                                $pdf::setXY(150,20);
                                $pdf::Cell(10,4,utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                                $pdf::Ln();

                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(35,4,"",0,0,'C');
                                $pdf::Cell(180,4,($nombrepersona),0,0,'L');
                                $pdf::setX(160);
                                $pdf::Cell(37,4,date("d/m/Y",strtotime($value->fecha)).' '.substr($value->created_at, 11),0,0,'L');
                                $pdf::Ln();
                                if($value->tipodocumento_id=="4"){
                                    $pdf::SetFont('helvetica','B',11);
                                    $pdf::Cell(37,6,utf8_encode("Paciente: "),0,0,'L');
                                    $pdf::SetFont('helvetica','',11);
                                    //$ticket = Movimiento::find($value->movimiento_id);
                                    $pdf::Cell(110,6,($nombrepersona),0,0,'L');
                                    $pdf::Ln();
                                }
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(35,4,"",0,0,'C');
                                $pdf::Cell(70,4,utf8_encode($dnipaciente),0,0,'L');
                                $movaux = Movimiento::find($value->movimiento_id);
                                $pdf::Cell(50,4,(""),0,0,'L');
                                if ($movaux != null) {
                                    if($movaux->tarjeta!="")
                                    $pdf::Cell(50,6,trim($movaux->tarjeta." - ".$movaux->tipotarjeta),0,0,'L');
                                }
                                
                                $pdf::Cell(0,4,$value->responsable->nombres,0,0,'R');
                                $pdf::Ln();
                                $pdf::setX(0);
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(5,5.5,"",0,0,'C');
                                $pdf::Cell(15,5.5,("Cant."),0,0,'C');
                                $pdf::Cell(120,5.5,"Descripción",0,0,'C');
                                $pdf::Cell(20,5.5,("P. Unitario"),0,0,'C');
                                $pdf::Cell(20,5.5,("Dscto"),0,0,'C');
                                $pdf::Cell(20,5.5,("Sub Total"),0,0,'C');
                                $pdf::Ln();
                                $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                                $lista2            = $resultado->get();
                                $c = 0;
                                foreach($lista2 as $key2 => $v){$c=$c+1;
                                    $dscto = 0;
                                    $subtotal2 = 0;
                                    if ($value->conveniofarmacia_id !== null) {
                                        $valaux = round(($v->precio*$v->cantidad), 2);
                                        $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                                        $dscto = round(($precioaux*$v->cantidad),2);
                                        $subtotal2 = round(($dscto*($value->copago/100)),2);
                                    }else{
                                        $subtotal2 = round(($v->precio*$v->cantidad), 2);
                                    }
                                    $pdf::setX(0);
                                    $pdf::SetFont('helvetica','B',11);
                                    $pdf::Cell(5,4,"",0,0,'C');
                                    $pdf::Cell(15,4,number_format($v->cantidad,2,'.',''),0,0,'C');
                                    if(strlen($v->producto->nombre)<80){
                                        $pdf::Cell(120,4,$v->producto->nombre,0,0,'L');
                                    }else{
                                        $x=$pdf::GetX();
                                        $y=$pdf::GetY();
                                        $pdf::Multicell(120,2,$v->producto->nombre,0,'L');
                                        $pdf::SetXY($x,$y);
                                        $pdf::Cell(120,4,"",0,0,'L');
                                    }
                                    //$pdf::Cell(120,4,$v->producto->nombre,0,0,'L');
                                    $pdf::Cell(20,4,number_format($v->precio,2,'.',''),0,0,'C');
                                    $pdf::Cell(20,4,number_format($dscto,2,'.',''),0,0,'C');
                                    $pdf::Cell(20,4,number_format($subtotal2,2,'.',''),0,0,'C');
                                    $pdf::Ln('4');                     
                                }
                                $pdf::Ln();
                                $letras = new EnLetras();
                                $pdf::SetFont('helvetica','B',11);
                                $valor=$letras->ValorEnLetras($value->total, " SOLES" );//letras
                                $pdf::Cell(15,5.5,"",0,0,'C');
                                $pdf::Cell(140,5,utf8_decode($valor),0,0,'L');
                                $pdf::SetFont('helvetica','B',11);
                                $pdf::Cell(30,5,"S/. ".number_format($value->total,2,'.',''),0,0,'R');
                                $pdf::Ln();
                                $pdf::Output('Comprobante.pdf');
                            }
                        }
                        
                    }elseif ($value->tipodocumento_id == 15) {
                        $pdf = new TCPDF();
                        $abreviatura="G";
                        $pdf::SetTitle('Guia Interna');
                        $pdf::AddPage();
                        $pdf::SetFont('helvetica','',10);
                        $pdf::Cell(50,5.5,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                        $pdf::Cell(70,5.5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                        $pdf::SetFont('helvetica','B',10);
                        $pdf::Cell(60,5.5,'Convenio: '.$value->conveniofarmacia->nombre,0,0,'R');
                        $pdf::Ln();
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','',9);
                        if ($value->persona_id !== NULL) {
                            $pdf::Cell(155,6,(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                        }else{
                            $pdf::Cell(155,6,(trim("Paciente: ".$value->nombrepaciente)),0,0,'L');
                        }
                        $pdf::Cell(30,4,date("d/m/Y",strtotime($value->fecha)).' '.substr($value->created_at, 11),0,0,'L');
                        $pdf::Ln();
                        $pdf::Ln(); 
                        $pdf::SetFont('helvetica','B',9);
                        $color='background:rgba(10,215,37,0.50)';
                        $pdf::Cell(15,5.5,("Cantidad"),1,0,'C');
                        $pdf::Cell(80,5.5,"Producto",1,0,'C');
                        $pdf::Cell(19,5.5,("Prec. Unit."),1,0,'C');
                        $pdf::Cell(22,5.5,("P.Total Dcto"),1,0,'C');
                        $pdf::Cell(23,5.5,("% Copago Pac."),1,0,'C');
                        $pdf::Cell(20,5.5,("Precio Total"),1,0,'C');
                        $pdf::Cell(20,5.5,("Sin IGV"),1,0,'C');
                        $pdf::SetFont('helvetica','',9);
                        $pdf::Ln();
                        $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                        $lista2            = $resultado->get();
                        $totalpago=0;
                        $totalcopago=0;
                        $totaldescuento=0;
                        $totaligv=0;
                        foreach($lista2 as $key2 => $v){
                            $pdf::Cell(15,6,number_format($v->cantidad),0,0,'C');
                            if(strlen($v->producto->nombre)<35){
                                $pdf::Cell(80,6,$v->producto->nombre,0,0,'L');
                            }else{
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();
                                $pdf::Multicell(80,3,$v->producto->nombre,0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(80,6,"",0,0,'L');
                            }
                            //$pdf::Cell(80,12,$v->producto->nombre,0,0,'L');
                            $pdf::Cell(19,6,number_format($v->precio,2,'.',''),0,0,'C');
                            $valaux = round(($v->precio*$v->cantidad), 2);
                            $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                            $dscto = round(($precioaux*$v->cantidad),2);
                            $totalpago = $totalpago+$dscto;
                            $pdf::Cell(22,6,number_format($dscto,2,'.',''),0,0,'C');
                            if($value->copago == 100){
                                $value->copago = 0;
                            }
                            $pdf::Cell(23,6,number_format($value->copago,2,'.',''),0,0,'C');
                            
                            if($value->copago>0){
                                $subtotal = round(($dscto*(($value->copago)/100)),2);
                                $subigv = round(($subtotal/1.18),2);
                            }else{
                                $subigv = round(($dscto/1.18),2);
                                $subtotal = 0;
                            }
                            // $subtotal = round(($dscto*($value->copago/100)),2);
                            // $subigv = round(($subtotal/1.18),2);
                            $totaldescuento = $totaldescuento+$subtotal;
                            $totalcopago+=$value->copago;
                            $totaligv = $totaligv+$subigv;
                            $pdf::Cell(20,6,number_format($subtotal,2,'.',''),0,0,'C');
                            $pdf::Cell(20,6,number_format($subigv,2,'.',''),0,0,'C');
                            $pdf::Ln();
                        }
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(114,6,'',0,0,'C');
                        $pdf::Cell(22,6,number_format($totalpago,2,'.',''),0,0,'C');
                        //$pdf::Cell(18,6,number_format($totalcopago,2,'.',''),0,0,'C');
                        $pdf::Cell(23,6,"",0,0,'C');
                        $pdf::Cell(20,6,number_format($totaldescuento,2,'.',''),0,0,'C');
                        $pdf::Cell(20,6,number_format($totaligv,2,'.',''),0,0,'C');
                        $pdf::Ln();
                        $pdf::Output('Guia.pdf');
                    }
                        
                }
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function procesar(Request $request)
    {
        $error = DB::transaction(function() use($request){
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','S')
                            ->where('movimiento.tipodocumento_id','<>',15);

            if($request->input('fechainicial')!=""){
                $resultado = $resultado->where('movimiento.fecha','>=',Date::createFromFormat('d/m/Y', $request->input('fechainicial'))->format('Y-m-d'));
            }
            if($request->input('fechafinal')!=""){
                $resultado = $resultado->where('movimiento.fecha','<=',Date::createFromFormat('d/m/Y', $request->input('fechafinal'))->format('Y-m-d'));
            }        
            if($request->input('tipodocumento')!=""){
                $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
            }        
            if($request->input('numero')!=""){
                $resultado = $resultado->where('movimiento.numero','LIKE','%'.$request->input('numero').'%');
            }        

            $resultado        = $resultado->select('movimiento.*')->orderBy('movimiento.fecha', 'ASC');
            $lista            = $resultado->get();
            foreach ($lista as $key => $value) {
                $numero=($value->tipodocumento_id==4?"F":"B").str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT);

                $dias_trascurridos = date_diff(date_create($value->fecha),date_create())->days;
                if(substr($numero, 0, 1) == "B" && $dias_trascurridos <= 7){
                    //dd($numero,$dias_trascurridos);
                    $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICE_RESPONSE')->where('serieNumero','like',$numero)->where("bl_estadoRegistro","=","L")->count("*");
                    // dd($rs);
                    if($rs>0){
                        DB::connection('sqlsrvtst21')->delete("delete from SPE_EINVOICE_RESPONSE where serieNumero in (?)",[$numero]); 
                        DB::connection('sqlsrvtst21')->update("update SPE_EINVOICEHEADER set bl_estadoRegistro='A',bl_reintento=0 where serieNumero in (?)",[$numero]); 
                    }
                }

                //if($value->situacionsunat!="E"){
                    $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICEHEADER')->where('serieNumero','like',$numero)->first();
                    // dd($rs);
                    if(count($rs)>0){
                        $value->situacionbz=$rs->bl_estadoRegistro;
                        if($rs->bl_estadoRegistro=='E'){
                            $value->situacionsunat='E';    
                        }
                    }
                    $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICE_RESPONSE')->where('serieNumero','like',$numero)->first();
                    if(count($rs)>0){
                        $value->situacionsunat=$rs->bl_estadoRegistro;
                        $value->mensajesunat=$rs->bl_mensajeSunat;
                    }
                    $value->save();
                //}
            }
        });
        return is_null($error) ? "OK" : $error;
    }

}
