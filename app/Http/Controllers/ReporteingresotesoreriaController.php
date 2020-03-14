<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Tipodocumento;
use App\Movimiento;
use App\Detallemovimiento;
use App\Person;
use App\Servicio;
use App\Caja;
use App\Conceptopago;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Detallemovcaja;
use App\Librerias\EnLetras;
use Illuminate\Support\Facades\Auth;
use Excel;

class ReporteingresotesoreriaController extends Controller
{
    protected $folderview      = 'app.reporteingresotesoreria';
    protected $tituloAdmin     = 'Reporte de Ingreso Tesoreria';
    protected $tituloRegistrar = 'Registrar Venta';
    protected $tituloModificar = 'Modificar Movimiento de Caja';
    protected $tituloCobrar = 'Cobrar Venta';
    protected $tituloAnular  = 'Anular Venta';
    protected $rutas           = array('create' => 'reporteingresotesoreria.create', 
            'edit'   => 'movimientocaja.edit', 
            'anular' => 'movimientocaja.anular',
            'search' => 'reporteingresotesoreria.buscar',
            'index'  => 'reporteingresotesoreria.index',
            'detalle' => 'movimientocaja.detalle',
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
        $entidad          = 'Reporteegresotesoreria';
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago', 'conceptopago.id', '=', 'movimiento.conceptopago_id')
                            ->join('area','area.id','=','movimiento.area_id')
                            ->where('movimiento.situacion','<>','A')
                            ->where('conceptopago.id','<>','1')
                            ->where('conceptopago.id','<>','2')
                            ->where('conceptopago.tipo','like','E')
                            ->where('area.nombre','like','%'.strtoupper($request->input('area')).'%')
                            ->where('conceptopago.nombre','like','%'.strtoupper($request->input('concepto')).'%')
                            ->where('movimiento.caja_id','=',$request->input('caja_id'));
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
        }
        if($request->input('nombre')!=""){
            $resultado = $resultado->where(function($q) use($request){
                        $q->where(DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('nombre').'%')
                          ->orWhere('paciente.bussinesname','like','%'.$request->input('persona').'%');
                          });
        }

        $resultado        = $resultado->select('movimiento.*',DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as persona2'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Area', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Concepto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_anular  = $this->tituloAnular;
        $titulo_cobrar    = $this->tituloCobrar;
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_anular', 'titulo_cobrar', 'ruta', 'user'));
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
        $entidad          = 'Reporteingresotesoreria';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        $cboCaja = array('6'=>'Tesoreria','7'=>'Farmacia - Tesoreria');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user','cboCaja'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Ventaadmision';
        $Venta = null;
        $cboConvenio = array();
        $convenios = Convenio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($convenios as $key => $value) {
            $cboConvenio = $cboConvenio + array($value->id => $value->nombre);
        }
        $formData            = array('Venta.store');
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('Venta', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente', 'cboConvenio'));
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
                'doctor'                  => 'required',
                'especialidad'          => 'required',
                'paciente'          => 'required',
                'numero'          => 'required',
                );
        $mensajes = array(
            'doctor.required'         => 'Debe seleccionar un doctor',
            'especialidad.required'         => 'Debe seleccionar una especialidad',
            'paciente.required'         => 'Debe seleccionar un paciente',
            'numero.required'         => 'Debe seleccionar una historia',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       
        
        $error = DB::transaction(function() use($request){
            $Venta       = new Venta();
            $Venta->fecha = $request->input('fecha');
            $person_id = $request->input('person_id');
            if($person_id==""){
                $person_id = null;
            }
            $historia_id = $request->input('historia_id');
            if($historia_id==""){
                $historia_id = null;
            }
            $Venta->paciente_id = $person_id;
            $Venta->historia_id = $historia_id;
            $Venta->doctor_id = $request->input('doctor_id');
            $Venta->situacion='P';//Pendiente
            $Venta->horainicio = $request->input('horainicio');
            $Venta->horafin = $request->input('horafin');
            $Venta->comentario = $request->input('comentario');
            $Venta->telefono = $request->input('telefono');
            $Venta->paciente = $request->input('paciente');
            $Venta->historia = $request->input('numero');
            $Venta->tipopaciente = $request->input('tipopaciente');
            $Venta->save();
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
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $Movimientocaja = Movimiento::find($id);
        $entidad             = 'Movimientocaja';
        $formData            = array('movimientocaja.update', $id);
        $cboConcepto = array();
        $rs = Conceptopago::where(DB::raw('1'),'=','1')->where('tipo','LIKE','E')->where('id','<>','2')->where('id','<>',8)->where('id','<>',14)->where('id','<>',16)->where('id','<>',18)->where('id','<>',20)->where('id','<>',31)->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboConcepto = $cboConcepto + array($value->id => $value->nombre);
        }
        $cboTipo = array('RH' => 'RH', 'FT' => 'FT', 'BV' => 'BV', 'TK' => 'TK', 'VR' => 'VR');
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('Movimientocaja', 'formData', 'entidad', 'boton', 'listar', 'cboConcepto', 'cboTipo'));
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        /*$resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago', 'conceptopago.id', '=', 'movimiento.conceptopago_id')
                            ->where('movimiento.situacion','<>','A')
                            ->where('conceptopago.id','<>','1')
                            ->where('conceptopago.id','<>','2')
                            ->where('conceptopago.tipo','like','I')
                            ->where('movimiento.caja_id','=',6);
        if($request->input('fecha')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fecha').'-01');
            $fechafinal=date("Y-m-d",strtotime('-1 day',strtotime('+1 month',strtotime($request->input('fecha').'-01'))));
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as persona2'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();*/
        $resultado  = Movimiento::where('caja_id','=',6)
                        ->where('conceptopago_id','=',1)
                        ->where('movimiento.situacion','<>','A');
        if($request->input('fecha')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fecha').'-01');
            $fechafinal=date("Y-m-d",strtotime('-1 day',strtotime('+1 month',strtotime($request->input('fecha').'-01'))));
            //$fechafinal = '2018-12-01';
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
            //dd($fechafinal);
            //dd($fechafinal);
            //$resultado = $resultado->whereRaw('movimiento.fecha','<=',$fechafinal);
        }
        //dd(array($resultado->select('*')->toSql(),$request->input('fecha'),$fechafinal));
        $lista = $resultado->select('*')->get();
        //dd($lista);

        Excel::create('ExcelIngresosTesoreria', function($excel) use($lista,$request) {
 
            $excel->sheet('Ingresos', function($sheet) use($lista,$request) {
                $celdas      = 'A1:K1';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $title[] = "Ingresos de Efectivo a Tesoreria del ".date("d/Y",strtotime($request->input('fecha').'-01'));
                $sheet->row(1,$title);
                $cabecera=array();
                $cabecera[] = "Dia" ;               
                $cabecera[] = "Saldo Anterior";
                $cabecera[] = "Ingresos";
                $cabecera[] = "Otros Ingresos";
                $cabecera[] = "Total" ;  
                $cabecera[] = "" ;               
                $cabecera[] = "Dia";
                $cabecera[] = "Admision";
                $cabecera[] = "Emergencia" ;
                $cabecera[] = "Farmacia" ;
                $cabecera[] = "Total" ;                   
                $sheet->row(3,$cabecera);
                $sheet->cells("A3:K3", function($cells) {
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setValignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $c=4;$d=3;$band=true;$fecha="";$admision=0;$emergencia=0;$farmacia=0;$otros=0;
                foreach($lista as $k=>$v){
                    $cierre = Movimiento::where('id','>',$v->id)->where('conceptopago_id','=',2)->where('caja_id','=',6)->first();
                    if(!is_null($cierre)){
                        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                            ->join('conceptopago', 'conceptopago.id', '=', 'movimiento.conceptopago_id')
                                            ->where('movimiento.situacion','<>','A')
                                            ->where('conceptopago.id','<>','1')
                                            ->where('conceptopago.id','<>','2')
                                            ->where('conceptopago.tipo','like','I')
                                            ->where(function($sql) use ($v,$cierre){
                                                $sql->where(function($q) use($v,$cierre){
                                                    $q->whereNull('movimiento.cajaapertura_id')
                                                      ->where('movimiento.id','>',$v->id)
                                                      ->where('movimiento.id','<',$cierre->id);
                                                    })
                                                    ->orWhere('movimiento.cajaapertura_id','=',$v->id);
                                            })
                                            ->where('movimiento.caja_id','=',6);
                        $resultado        = $resultado->select('movimiento.*',DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as persona2'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
                        $lista            = $resultado->get();
                        //if($k>28){dd(array($v,$cierre,$lista));}
                        foreach ($lista as $key => $value){
                            //if($k>28){dd(array($v,$value));}
                            if($fecha!=$v->fecha){
                                if($fecha!=""){
                                    $detalle = array();
                                    $detalle[] = date("d/m/Y",strtotime($fecha));
                                    if($c==4){
                                        $apertura=Movimiento::where('fecha','=',$request->input('fecha').'-01')->where('caja_id','=',6)->where('conceptopago_id','=','1')->orderBy('fecha','asc')->first();
                                        $detalle[] = number_format($apertura->total,2,'.','');
                                    }else{
                                        $detalle[] = "";
                                    }
                                    $detalle[] = $dato["admision"] + $dato["emergencia"] + $dato["farmacia"];
                                    $detalle[] = $dato["otros"];
                                    if($c==4){
                                        $detalle[] = $dato["admision"] + $dato["emergencia"] + $dato["farmacia"] + $dato["otros"] + $apertura->total;
                                    }else{
                                        $detalle[] = $dato["admision"] + $dato["emergencia"] + $dato["farmacia"] + $dato["otros"];
                                    }
                                    $detalle[] = "";
                                    $detalle[] = date("d/m/Y",strtotime($fecha));
                                    $detalle[] = $dato["admision"];
                                    $detalle[] = $dato["emergencia"];
                                    $detalle[] = $dato["farmacia"];
                                    $detalle[] = $dato["admision"] + $dato["emergencia"] + $dato["farmacia"];
                                    $sheet->row($c,$detalle);
                                    $sheet->cells("A".$c.":K".$c, function($cells) {
                                        $cells->setBorder('thin','thin','thin','thin');
                                        $cells->setValignment('center');
                                        $cells->setFont(array(
                                            'family'     => 'Calibri',
                                            'size'       => '10',
                                            ));
                                    });
                                    $sheet->cells("F".$c, function($cells) {
                                        $cells->setBorder('thin','','thin','');
                                        $cells->setFont(array(
                                            'family'     => 'Calibri',
                                            'size'       => '10',
                                            'color' => array('rgb' => 'FF0000'),
                                            'fill' =>  array('color'=> array('rgb' => 'FF0000')),
                                            ));
                                    });
                                    $admision = $admision + $dato["admision"];
                                    $emergencia = $emergencia + $dato["emergencia"];
                                    $farmacia = $farmacia + $dato["farmacia"];
                                    $otros = $otros + $dato["otros"];
                                    $c++;
                                }
                                $fecha=$v->fecha;        
                                $dato = array("farmacia"=>'',"emergencia"=>'',"admision"=>'',"otros"=>'');
                            }
                            if($value->conceptopago_id==60 && $value->movimiento_id>0){
                                $caja=Movimiento::find($value->movimiento_id);
                                if($caja->caja_id==4){
                                    $dato["farmacia"]=$dato["farmacia"] + $value->total;
                                }elseif($caja->caja_id==5){
                                    $dato["emergencia"]=$dato["emergencia"] + $value->total;
                                }else{
                                    $dato["admision"]=$dato["admision"] + $value->total;
                                }
                            }else{
                                $dato["otros"]=$dato["otros"] + $value->total;
                            }
                        }
                    }else{
                        //dd($v);
                    }
                }
                $detalle = array();
                $detalle[] = date("d/m/Y",strtotime($fecha));
                if($c==4){
                    $apertura=Movimiento::where('fecha','=',$request->input('fecha').'-01')->where('caja_id','=',6)->where('conceptopago_id','=','1')->orderBy('fecha','asc')->first();
                    $detalle[] = number_format($apertura->total,2,'.','');
                }else{
                    $detalle[] = "";
                }
                $detalle[] = $dato["admision"] + $dato["emergencia"] + $dato["farmacia"];
                $detalle[] = $dato["otros"];
                if($c==4){
                    $detalle[] = $dato["admision"] + $dato["emergencia"] + $dato["farmacia"] + $dato["otros"] + $apertura->total;
                }else{
                    $detalle[] = $dato["admision"] + $dato["emergencia"] + $dato["farmacia"] + $dato["otros"];
                }
                $detalle[] = "";
                $detalle[] = date("d/m/Y",strtotime($fecha));
                $detalle[] = $dato["admision"];
                $detalle[] = $dato["emergencia"];
                $detalle[] = $dato["farmacia"];
                $detalle[] = $dato["admision"] + $dato["emergencia"] + $dato["farmacia"];
                $sheet->row($c,$detalle);
                $sheet->cells("A".$c.":K".$c, function($cells) {
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setValignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        ));
                });
                $sheet->cells("F".$c, function($cells) {
                    $cells->setBorder('thin','','thin','');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'color' => array('rgb' => 'FF0000'),
                        'fill' =>  array('color'=> array('rgb' => 'FF0000')),
                        ));
                });
                $admision = $admision + $dato["admision"];
                $emergencia = $emergencia + $dato["emergencia"];
                $farmacia = $farmacia + $dato["farmacia"];
                $otros = $otros + $dato["otros"];
                $c++;


                $detalle = array();
                $detalle[] = "";
                $detalle[] = number_format($apertura->total,2,'.','');
                $detalle[] = number_format($admision+$emergencia+$farmacia,2,'.','');
                $detalle[] = number_format($otros,2,'.','');
                $detalle[] = number_format($admision+$emergencia+$farmacia+$otros+$apertura->total,2,'.','');
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = number_format($admision,2,'.','');
                $detalle[] = number_format($emergencia,2,'.','');
                $detalle[] = number_format($farmacia,2,'.','');
                $detalle[] = number_format($admision+$emergencia+$farmacia,2,'.','');
                $sheet->row($c,$detalle);
                $sheet->cells("A".$c.":K".$c, function($cells) {
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setValignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
            });
        })->export('xls');
    }
    
}
