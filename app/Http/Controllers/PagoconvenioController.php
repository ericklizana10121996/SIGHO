<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Conceptopago;
use App\Movimiento;
use App\Detallemovcaja;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Elibyy\TCPDF\Facades\TCPDF;
use Excel;

/**
 * PagodoctorController
 * 
 * @package 
 * @author DaYeR
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class PagoconvenioController extends Controller
{
    protected $folderview      = 'app.pagoconvenio';
    protected $tituloAdmin     = 'Pago Convenio';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Pago Convenio';
    protected $tituloEliminar  = 'Eliminar el Pago de Convenio';
    protected $rutas           = array('create' => 'pagoconvenio.create', 
            'pagar'   => 'pagoconvenio.pago', 
            'delete' => 'pagoconvenio.eliminar',
            'search' => 'pagoconvenio.buscar',
            'index'  => 'pagoconvenio.index',
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Pagoconvenio';
        $nombre           = Libreria::getParam($request->input('nombre'));
        $nombrep          = Libreria::getParam($request->input('nombrep'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));
        $solo        = Libreria::getParam($request->input('solo'));

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('plan as convenio','convenio.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$nombrep.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.plan_id','<>',6)
                            ->where('movimiento.situacion','<>','U')
                            ->whereNull('dmc.deleted_at')
                            ->where('dmc.persona_id','<>',294);//quito LAB JUAN PABLO;
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            if($situacion=="E"){
                $resultado = $resultado->where('dmc.situacionentrega','LIKE',$situacion);
            }else{
                $resultado = $resultado->where(function ($query) {
                                    $query->whereNull('dmc.situacionentrega')
                                          ->orwhere(function ($query1){
                                            $query1->whereNotIn('dmc.situacionentrega',['E','A']);
                                            });//normal
                            });                
            }
        }
        if($solo!=""){
            if($solo=="C"){//CONSULTA
                $resultado = $resultado->where(function ($query){
                                            $query->where('dmc.tiposervicio_id','=',1)
                                                  ->orwhere(function ($query1){
                                                    $query1->where('s.tiposervicio_id','=',1);
                                                });//normal
                                            });
            }else{
                $resultado = $resultado->where('S.tiposervicio_id','<>',1);                
            }
        }
        $resultado        = $resultado->orderBy('movimiento.fecha', 'ASC')->groupBy('movimiento.id')
                            ->select('movimiento.fecha','dmc.id','dmc.recibo','dmc.situacionentrega','s.nombre as servicio','dmc.descripcion as servicio2','dmc.fechaentrega','dmc.servicio_id','dmc.recibo','dmc.pagotarjeta',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'usuarioentrega.nombres as usuarioentrega','movimiento.tipomovimiento_id','responsable.nombres as responsable2','convenio.nombre as convenio','movimiento.condicionpaciente'
                                ,DB::raw("(SELECT CONCAT_WS('|',ct.fecha, ct.atendio, ct.ficha, ct.sctr, ct.soat) FROM cita ct WHERE (ct.movimiento_id = movimiento.id) OR (ct.fecha >= movimiento.fecha AND ct.paciente_id = movimiento.persona_id AND ct.doctor_id = dmc.persona_id) AND ct.situacion <> 'A' ORDER BY ct.fecha LIMIT 1) as feccita"),'movimiento.id as idticket'
                        );
        
        $lista            = $resultado->get();

        // dd($lista);
        // exit();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Condición de Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Convenio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Recibo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cita', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Atendio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Ficha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'SCTR', 'numero' => '1');
        $cabecera[]       = array('valor' => 'SOAT', 'numero' => '1');
        
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

            // dd($lista);
            // exit;
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    public function index()
    {
        $entidad          = 'Pagoconvenio';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboSituacion          = array("" => "Todos", "E" => "Pagado", "P" => "Pendiente");
        $cboSolo           = array("" => "Todos", "C" => "Consulta", "P" => "Procedimientos");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboSituacion', 'cboSolo'));
    }

    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Pagoconvenio';
        $conceptopago = null;
        $formData            = array('conceptopago.store');
        $cboTipo          = array("Ingreso" => "Ingreso", "Egreso" => "Egreso");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('conceptopago', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
    }

    public function store(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                'nombre'                  => 'required|max:200',
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $Conceptopago       = new Conceptopago();
            $Conceptopago->nombre = strtoupper($request->input('nombre'));
            $Conceptopago->tipo = $request->input('tipo');
            $Conceptopago->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function show($id)
    {
        //
    }

    public function pago(Request $request,$listar,$id)
    {
        $listar              = Libreria::getParam($listar, 'NO');
        $id2 = explode(',',$id);
        //print_r($id2);
        $detalle1             = Detallemovcaja::whereIn('id',$id2)->get();
        $detalle="";
        $entidad             = 'Pagoconvenio';
        $formData            = array('pagoconvenio.pagar', $id);
        $formData            = array('route' => $formData, 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Pagar';
        return view($this->folderview.'.pago')->with(compact('detalle1', 'formData', 'entidad', 'boton', 'listar','detalle','id'));
    }

    public function pagar(Request $request)
    {
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $arr=explode(",",$request->input('id'));
            for($c=0;$c<count($arr);$c++){
                $detalle           = Detallemovcaja::find($arr[$c]);
                $detalle->fechaentrega = date("Y-m-d");
                $detalle->usuarioentrega_id = $user->person_id;
                $detalle->situacionentrega = 'E';
                $detalle->recibo = $request->input('recibo');
                $detalle->retencion = ($request->input('retencion')>0)?'S':'N';
                $detalle->save();
            }

            //MOV CAJA
            $movimiento        = new Movimiento();
            $movimiento->fecha = date("Y-m-d");
            $movimiento->numero= Movimiento::NumeroSigue(2,2);
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$detalle->persona_id;
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=$request->input('pago'); 
            $movimiento->tipomovimiento_id=2;
            $movimiento->tipodocumento_id=2;
            $movimiento->conceptopago_id=24;//PAGO TARJETA
            $movimiento->comentario='Pago con RH '.$request->input('recibo');
            //$movimiento->caja_id=3;//CAJA CONVENIO
            $movimiento->totalpagado=$request->input('pago');
            $movimiento->situacion='N';
            $movimiento->listapago=$request->input('id');
            $movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function destroy(Request $request)
    {
        $id = $request->input("id");
        $existe = Libreria::verificarExistencia($id, 'detallemovcaja');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $comentarioa = $request->input("comentarioa");
        //dd($id);
        $error = DB::transaction(function() use($id, $user, $comentarioa){
            $detalle = Detallemovcaja::find($id);
            $detalle->fechaentrega = date("Y-m-d");
            $detalle->usuarioentrega_id = $user->person_id;
            $detalle->motivo_anul = $comentarioa;
            $detalle->situacionentrega = 'A';
            $detalle->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'detallemovcaja');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Detallemovcaja::find($id);
        $entidad  = 'Pagoconvenio';
        $formData = array('route' => array('pagoconvenio.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar2')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar', 'id'));
    }


    public function pdfReporte(Request $request){
        $nombre           = Libreria::getParam($request->input('doctor'));
        $nombrep          = Libreria::getParam($request->input('paciente'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));
        $solo          = Libreria::getParam($request->input('solo'));

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$nombrep.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.plan_id','<>',6)
                            ->where('movimiento.situacion','<>','U')
                            ->whereNull('dmc.deleted_at')
                            ->where('dmc.persona_id','<>',294);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
         if($situacion!=""){
            if($situacion=="E"){
                $resultado = $resultado->where('dmc.situacionentrega','LIKE',$situacion);
            }else{
                $resultado = $resultado->where(function ($query) {
                                    $query->whereNull('dmc.situacionentrega')
                                          ->orwhere(function ($query1){
                                            $query1->whereNotIn('dmc.situacionentrega',['E','A']);
                                            });//normal
                            });                
            }
        }else{
            $resultado = $resultado->whereNotIn('dmc.situacionentrega',['A']);
        }
        if($solo!=""){
            if($solo=="C"){//CONSULTA
                $resultado = $resultado->where(function ($query){
                                            $query->where('dmc.tiposervicio_id','=',1)
                                                  ->orwhere(function ($query1){
                                                    $query1->where('s.tiposervicio_id','=',1);
                                                });//normal
                                            });
            }else{
                $resultado = $resultado->where('S.tiposervicio_id','<>',1);                
            }
        }
        $resultado = $resultado->where('medico.id','!=',294)->where('medico.id','!=',58572)->where('medico.id','!=',314)->where('medico.id','!=',34846)->where('medico.id','!=',57325)->where('medico.id','!=',155)->where('medico.id','!=',201)->where('medico.id','!=',56595)->where('medico.id','!=',59313)->where('medico.id','!=',315)->where('medico.id','!=',56802)->where('medico.id','!=',59313)->where('medico.id','!=',56655)->where('medico.id','!=',62487)->where('medico.id','!=',282)->where('medico.id','!=',297)->orderBy('medico.apellidopaterno', 'ASC')->orderBy('movimiento.fecha', 'ASC')
                            ->select('movimiento.fecha','movimiento.total','dmc.id','dmc.recibo','dmc.situacionentrega','s.nombre as servicio','dmc.descripcion as servicio2','dmc.fechaentrega','dmc.servicio_id','dmc.recibo','dmc.precio','dmc.pagotarjeta',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'usuarioentrega.nombres as usuarioentrega','movimiento.tipomovimiento_id',DB::raw("DATE_FORMAT(movimiento.created_at,'%h:%i:%s %p') AS hora"));
        $lista            = $resultado->get();
        $pdf = new TCPDF();
        $pdf::SetTitle('Reporte de Pagos de Convenio por Medico al '.($fechafinal));
        if (count($lista) > 0) {            
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Reporte de Pagos de Convenio  Medico al ".date("d/m/Y",strtotime($fechafinal))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(8,6,utf8_decode("Nro"),1,0,'C');
            $pdf::Cell(16,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(60,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(122,6,utf8_decode("CONCEPTO"),1,0,'C');
            $pdf::Cell(13,6,utf8_decode("RECIBO"),1,0,'C');
            $pdf::Cell(13,6,utf8_decode("TOTAL"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("FECHA PAGO"),1,0,'C');
            $pdf::Cell(25,6,utf8_decode("USUARIO"),1,0,'C');
            $pdf::Ln();
            $doctor="";$total=0;$totalgeneral=0;$idmedico=0;$c=0;
            foreach ($lista as $key => $value){$c=$c+1;
                if($doctor!=($value->medico)){
                    $pdf::SetFont('helvetica','B',8);
                    /*if($doctor!=""){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(136,6,("TOTAL :"),1,0,'R');
                        $pdf::Cell(16,6,number_format($total,2,'.',''),1,0,'C');
                        $totalgeneral = $totalgeneral + $total;
                        $total=0;
                        $pdf::Ln();
                    }*/
                    $doctor=($value->medico);
                    $idmedico=$value->medico_id;
                    $pdf::SetFont('helvetica','B',8);
                    $pdf::Cell(0,6,($doctor),1,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',7);
                $pdf::Cell(8,6,$c,1,0,'R');
                $pdf::Cell(16,6,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                $pdf::Cell(60,6,substr($value->paciente,0,35),1,0,'L');
                $nombreservicio = "";
                if($value->servicio_id>0){
                    $nombreservicio = $value->servicio;
                }else{
                    $nombreservicio = $value->servicio2;
                }
                if(strpos($nombreservicio,'NOCHE') === false && strpos($nombreservicio,'NOCHE') === false) {
                    $monto_pago = "30.00";
                }else{
                    $monto_pago = "50.00";
                }

                if (date('N',strtotime($value->fecha)) === '7') {
                    $monto_pago = "50.00";
                }

                if($value->servicio_id>0){
                    $pdf::Cell(122,6,($value->servicio." - ".$value->hora." - ".$monto_pago),1,0,'L');
                }else{
                    $pdf::Cell(122,6,($value->servicio2." - ".$value->hora." - ".$monto_pago),1,0,'L');
                }
                $pdf::Cell(13,6,$value->recibo,1,0,'C');
                if ($value->fechaentrega!="0000-00-00") {
                    $pdf::Cell(13,6,$value->total,1,0,'C');
                } else {
                    $pdf::Cell(13,6,"",1,0,'C');
                }
                if($value->fechaentrega!="0000-00-00"){
                    $pdf::Cell(20,6,date("d/m/Y",strtotime($value->fechaentrega)),1,0,'C');
                }else{
                    $pdf::Cell(20,6,"",1,0,'C');
                }
                $pdf::Cell(25,6,($value->usuarioentrega),1,0,'L');
                $total=$total + $value->pagotarjeta;
                $pdf::Ln();                
            }
            
            $total=0;
            $pdf::Ln();
        }
        $pdf::Output('ReporteConvenio.pdf');
    }

    public function excel(Request $request){
        $nombre           = Libreria::getParam($request->input('doctor'));
        $nombrep          = Libreria::getParam($request->input('paciente'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));
        $solo             = Libreria::getParam($request->input('solo'));

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$nombrep.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.plan_id','<>',6)
                            ->where('movimiento.situacion','<>','U')
                            ->whereNull('dmc.deleted_at')
                            ->where('dmc.persona_id','<>',294);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
         if($situacion!=""){
            if($situacion=="E"){
                $resultado = $resultado->where('dmc.situacionentrega','LIKE',$situacion);
            }else{
                $resultado = $resultado->where(function ($query) {
                                    $query->whereNull('dmc.situacionentrega')
                                          ->orwhere(function ($query1){
                                            $query1->whereNotIn('dmc.situacionentrega',['E','A']);
                                            });//normal
                            });                
            }
        }else{
            $resultado = $resultado->whereNotIn('dmc.situacionentrega',['A']);
        }
        if($solo!=""){
            if($solo=="C"){//CONSULTA
                $resultado = $resultado->where(function ($query){
                                            $query->where('dmc.tiposervicio_id','=',1)
                                                  ->orwhere(function ($query1){
                                                    $query1->where('s.tiposervicio_id','=',1);
                                                });//normal
                                            });
            }else{
                $resultado = $resultado->where('S.tiposervicio_id','<>',1);                
            }
        }
        $resultado = $resultado->where('medico.id','!=',294)->where('medico.id','!=',58572)->where('medico.id','!=',314)->where('medico.id','!=',34846)->where('medico.id','!=',57325)->where('medico.id','!=',155)->where('medico.id','!=',201)->where('medico.id','!=',56595)->where('medico.id','!=',59313)->where('medico.id','!=',315)->where('medico.id','!=',56802)->where('medico.id','!=',59313)->where('medico.id','!=',56655)->where('medico.id','!=',62487)->where('medico.id','!=',282)->where('medico.id','!=',297)->orderBy('medico.apellidopaterno', 'ASC')->orderBy('movimiento.fecha', 'ASC')
                            ->select('movimiento.fecha','dmc.id','dmc.recibo','dmc.situacionentrega','s.nombre as servicio','dmc.descripcion as servicio2','dmc.fechaentrega','dmc.servicio_id','dmc.recibo','dmc.pagotarjeta',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'usuarioentrega.nombres as usuarioentrega','movimiento.tipomovimiento_id');
        $lista            = $resultado->get();
        
        Excel::create('ExcelPagoTarjeta', function($excel) use($lista,$request) {
 
            $excel->sheet('Boleteo Total', function($sheet) use($lista,$request) {
 
                $cabecera = array();
                $cabecera[] = "Nro";
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Concepto";
                $cabecera[] = "Recbo";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Usuario";
                $c=2;$d=3;$band=true;$nro=1;
                $sheet->row(1,$cabecera);

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = $nro;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->paciente;
                    if($value->servicio_id>0){
                        $detalle[] = $value->servicio;
                    }else{
                        $detalle[] = $value->servicio2;
                    }
                    $detalle[] = $value->recibo;
                    if ($value->fechaentrega != '0000-00-00') {
                        $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                    }
                    $detalle[] = $value->responsable;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                    $nro++;
                }

            });
        })->export('xls');
    }
}
