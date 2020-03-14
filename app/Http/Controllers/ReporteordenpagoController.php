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

class ReporteordenpagoController extends Controller
{
    protected $folderview      = 'app.reporteordenpago';
    protected $tituloAdmin     = 'Reporte de Ordenes de Pago';
    protected $tituloRegistrar = 'Registrar Venta';
    protected $tituloModificar = 'Modificar Movimiento de Caja';
    protected $tituloCobrar = 'Cobrar Venta';
    protected $tituloAnular  = 'Anular Venta';
    protected $rutas           = array('create' => 'reporteordenpago.create', 
            'edit'   => 'movimientocaja.edit', 
            'anular' => 'movimientocaja.anular',
            'search' => 'reporteordenpago.buscar',
            'index'  => 'reporteordenpago.index',
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
        $entidad          = 'Reporteordenpago';
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago', 'conceptopago.id', '=', 'movimiento.conceptopago_id')
                            ->where('movimiento.situacion','<>','A')
                            ->where('conceptopago.id','<>','1')
                            ->where('conceptopago.id','<>','2')
                            ->where('conceptopago.nombre','like','%'.strtoupper($request->input('concepto')).'%')
                            ->whereIn('movimiento.caja_id',[6,7]);
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
        }
        if($request->input('nombre')!=""){
            $resultado = $resultado->where(function($q) use($request){
                        $q->where(DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('nombre').'%')
                          ->orWhere('paciente.bussinesname','like','%'.$request->input('nombre').'%');
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
        $cabecera[]       = array('valor' => 'Concepto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
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
        $entidad          = 'Reporteordenpago';
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request)
    {
        $existe = Libreria::verificarExistencia($request->input('id'), 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $Movimiento = Movimiento::find($request->input('id'));
            $Movimiento->conceptopago_id=$request->input('concepto');
            $Movimiento->formapago=$request->input('tipo');
            $Movimiento->voucher=$request->input('numero');
            $Movimiento->persona_id=$request->input('persona_id');
            $Movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function cobrar($id, $listarLuego,Request $request)
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
        $entidad  = 'Ventaadmision';
        $cboCaja          = array();
        $resultado        = Caja::where('id','<>',6)->where('id','<>',4)->orderBy('nombre','ASC')->get();
        $caja=0;
        foreach ($resultado as $key => $value) {
            $cboCaja = $cboCaja + array($value->id => $value->nombre);
            if($request->ip()==$value->ip){
                $caja=$value->id;
                $serie=$value->serie;
            }
        }
        $cboFormaPago     = array("Efectivo" => "Efectivo", "Tarjeta" => "Tarjeta");
        $cboTipoTarjeta    = array("VISA" => "VISA", "MASTER" => "MASTER");
        $cboTipoTarjeta2    = array("CREDITO" => "CREDITO", "DEBITO" => "DEBITO");        
        $user = Auth::user();
        if($caja==0){//ADMISION 1
            $serie=3;
            $idcaja=1;
        }
        $formData = array('route' => array('ventaadmision.pagar', $id), 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Cobrar';
        return view($this->folderview.'.cobrar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar', 'cboCaja' , 'caja', 'cboFormaPago', 'cboTipoTarjeta2', 'cboTipoTarjeta'));
    }

    public function detalle($id, Request $request)
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
        $boton               = 'Detalle';
        return view($this->folderview.'.detalle')->with(compact('Movimientocaja', 'formData', 'entidad', 'boton', 'listar', 'cboConcepto', 'cboTipo'));
    }


 public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago', 'conceptopago.id', '=', 'movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',2)                            
                            ->where('movimiento.conceptopago_id','<>',1)
                            ->where('movimiento.conceptopago_id','<>',2)
                            ->where('Conceptopago.tipo','=','E')
                            ->where('movimiento.conceptopago_id','<>',8)
                            ->where('movimiento.conceptopago_id','<>',14)
                            ->where('movimiento.conceptopago_id','<>',16)
                            ->where('movimiento.conceptopago_id','<>',18)
                            ->where('movimiento.conceptopago_id','<>',20)
                            ->where('movimiento.conceptopago_id','<>',31)
                            ->where('movimiento.situacion','<>','A')
                            ->where('movimiento.caja_id','=',$request->input('caja'));
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
        }
        if($request->input('persona')!=""){
            $resultado = $resultado->where(DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('persona').'%')
                          ->orWhere(function($q) use($request){
                            $q->where('paciente.bussinesname','like','%'.$request->input('persona').'%');
                          });
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
        }        

        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2',DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as persona2'),'movimiento.nombrepaciente',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();

        Excel::create('ExcelMovimientoCaja', function($excel) use($lista,$request) {
 
            $excel->sheet('MovimientoCaja', function($sheet) use($lista,$request) {
                $cabecera[] = "Caja" ;               
                $cabecera[] = "Fecha";
                $cabecera[] = "Persona";
                $cabecera[] = "Tipo Doc";
                $cabecera[] = "Nro.";
                $cabecera[] = "Concepto" ;               
                $cabecera[] = "Total" ;               
                $cabecera[] = "Comentario" ;               
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;$referido_id="";$total=0;
                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = $value->caja->nombre;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    if($value->empresa_id>0){
                        $detalle[] = $value->empresa->bussinesname;
                    }else{
                        if($value->persona_id>0 && $value->persona->bussinesname!="")
                            $detalle[] = $value->persona->bussinesname;
                        else
                            $detalle[] = $value->persona2;
                    }
                    if($value->caja_id==4){
                        if($value->formapago!="")
                            $detalle[] = $value->formapago;
                        elseif($value->tipodocumento_id==7)
                            $detalle[] = 'BV';
                        elseif($value->tipodocumento_id==6)
                            $detalle[] = 'FY';
                        else
                            $detalle[] = '';
                    }else{
                        $detalle[] =  $value->formapago;
                    }
                    $detalle[] = $value->voucher;
                    $detalle[] = $value->conceptopago->nombre;
                    $detalle[] = $value->total;
                    $detalle[] = $value->comentario;
                    $sheet->row($c,$detalle);
                    $c++;
                }
                //$sheet->fromArray($array);
            });
        })->export('xls');
    }
}
