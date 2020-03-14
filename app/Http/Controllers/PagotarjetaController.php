<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Conceptopago;
use App\Movimiento;
use App\Detallemovcaja;
use App\Librerias\Libreria;
use App\Caja;
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
class PagotarjetaController extends Controller
{
    protected $folderview      = 'app.pagotarjeta';
    protected $tituloAdmin     = 'Pago Tarjeta y Boleteo Total';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Pago Tarjeta y Boleteo Total';
    protected $tituloEliminar  = 'Eliminar el pago de Tarjeta y Boleteo Total';
    protected $rutas           = array('create' => 'pagotarjeta.create', 
            'pagar'   => 'pagotarjeta.pago', 
            'delete' => 'pagotarjeta.eliminar',
            'search' => 'pagotarjeta.buscar',
            'index'  => 'pagotarjeta.index',
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
        $entidad          = 'Pagotarjeta';
        $nombre           = Libreria::getParam($request->input('nombre'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            // ->where('medico.id','=','240')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('dmc.pagotarjeta','>',0);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $resultado = $resultado->where('dmc.situaciontarjeta','LIKE',$situacion);
        }
        $resultado        = $resultado->orderBy('movimiento.fecha', 'ASC')
                            ->select('movimiento.fecha','dmc.id','dmc.recibo','dmc.situaciontarjeta','s.nombre as servicio','dmc.descripcion as servicio2','dmc.fechaentrega','dmc.servicio_id','dmc.recibo','dmc.pagotarjeta',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id','responsable.nombres as responsable2');
        
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Recibo', 'numero' => '1');
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
    public function index(Request $request)
    {
        $user = Auth::user();
        $rs        = Caja::orderBy('nombre','ASC')->get();
        $band=false;
        foreach ($rs as $key => $value) {
            if($request->ip()==$value->ip && $value->id==3){
                $band=true;
            }
        }
        if($band || $user->usertype_id==1 || $user->usertype_id==8 || $user->usertype_id==7 ){
            $entidad          = 'Pagotarjeta';
            $title            = $this->tituloAdmin;
            $titulo_registrar = $this->tituloRegistrar;
            $ruta             = $this->rutas;
            $cboSituacion          = array("" => "Todos", "E" => "Pagado", "P" => "Pendiente");
            return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboSituacion'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Pagodoctor';
        $conceptopago = null;
        $formData            = array('conceptopago.store');
        $cboTipo          = array("Ingreso" => "Ingreso", "Egreso" => "Egreso");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('conceptopago', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
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

    public function pago($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'detallemovcaja');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $detalle             = Detallemovcaja::find($id);
        $entidad             = 'Pagotarjeta';
        $formData            = array('pagotarjeta.pagar', $id);
        $formData            = array('route' => $formData, 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Pagar';
        return view($this->folderview.'.pago')->with(compact('detalle', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function pagar(Request $request)
    {
        $existe = Libreria::verificarExistencia($request->input('id'), 'detallemovcaja');
        if ($existe !== true) {
            return $existe;
        }

        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $detalle           = Detallemovcaja::find($request->input('id'));
            $detalle->fechaentrega = date("Y-m-d");
            $detalle->usuarioentrega_id = $user->person_id;
            $detalle->situaciontarjeta = 'E';
            $detalle->recibo = $request->input('recibo');
            $detalle->retencion = ($request->input('retencion')>0)?'S':'N';
            $detalle->save();

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
            $movimiento->conceptopago_id=26;//PAGO TARJETA
            $movimiento->comentario='Pago con RH '.$request->input('recibo');
            $movimiento->formapago='RH';
            $movimiento->voucher=$request->input('recibo');
            //$movimiento->caja_id=3;//CAJA CONVENIO
            $movimiento->totalpagado=$request->input('pago');
            $movimiento->situacion='N';
            $movimiento->listapago=$detalle->id;
            $movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function destroy($id)
    {
        $existe = Libreria::verificarExistencia($id, 'detallemovcaja');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id, $user){
            $detalle = Detallemovcaja::find($id);
            $detalle->fechaentrega = date("Y-m-d");
            $detalle->usuarioentrega_id = $user->person_id;
            $detalle->situaciontarjeta = 'A';
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
        $entidad  = 'Pagotarjeta';
        $formData = array('route' => array('pagotarjeta.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function pdfReporte(Request $request){
        $nombre           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('dmc.pagotarjeta','>',0);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $resultado = $resultado->where('dmc.situaciontarjeta','LIKE',$situacion);
        }
        $resultado        = $resultado->orderBy('movimiento.fecha', 'ASC')
                            ->select('movimiento.fecha','dmc.id','dmc.recibo','dmc.situaciontarjeta','s.nombre as servicio','dmc.descripcion as servicio2','dmc.fechaentrega','dmc.servicio_id','dmc.pagotarjeta',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id');

        $lista            = $resultado->get();
        $pdf = new TCPDF();
        $pdf::SetTitle('Reporte de Pagos de Transferencia Medico al '.($fechafinal));
        if (count($lista) > 0 || count($lista2) > 0) {            
            $pdf::AddPage();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Reporte de Pagos de Transferencia Medico al ".date("d/m/Y",strtotime($fechafinal))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(8,6,utf8_decode("Nro."),1,0,'C');
            $pdf::Cell(14,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(60,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(60,6,utf8_decode("CONCEPTO"),1,0,'C');
            $pdf::Cell(14,6,utf8_decode("TOTAL"),1,0,'C');
            $pdf::Cell(25,6,utf8_decode("USUARIO"),1,0,'C');
            $pdf::Cell(18,6,utf8_decode("FECHA ENT."),1,0,'C');
            $pdf::Ln();
            $doctor="";$total=0;$totalgeneral=0;$idmedico=0;$c=0;
            foreach ($lista as $key => $value){$c=$c+1;
                if($doctor!=($value->medico)){
                    $pdf::SetFont('helvetica','B',8);
                    if($doctor!=""){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(142,6,("TOTAL :"),1,0,'R');
                        $pdf::Cell(16,6,number_format($total,2,'.',''),1,0,'C');
                        $totalgeneral = $totalgeneral + $total;
                        $total=0;
                        $pdf::Ln();
                    }
                    $doctor=($value->medico);
                    $idmedico=$value->medico_id;
                    $pdf::SetFont('helvetica','B',8);
                    $pdf::Cell(199,6,($doctor),1,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',7);
                $pdf::Cell(8,6,$c,1,0,'R');
                $pdf::Cell(14,6,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                $pdf::Cell(60,6,($value->paciente),1,0,'L');
                if($value->servicio_id>0){
                    $pdf::Cell(60,6,$value->servicio,1,0,'L');
                }else{
                    $pdf::Cell(60,6,$value->servicio2,1,0,'L');
                }
                $pdf::Cell(14,6,number_format($value->pagotarjeta,2,'.',''),1,0,'C');
                $pdf::Cell(25,6,($value->responsable),1,0,'L');
                if($value->fechaentrega!="0000-00-00"){
                    $pdf::Cell(18,6,date("d/m/Y",strtotime($value->fechaentrega)),1,0,'C');
                }else{
                    $pdf::Cell(18,6,"",1,0,'C');
                }
                $total=$total + $value->pagotarjeta;
                $pdf::Ln();                
            }
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(142,6,("TOTAL :"),1,0,'R');
            $pdf::Cell(14,6,number_format($total,2,'.',''),1,0,'C');
            $totalgeneral = $totalgeneral + $total;
            $total=0;
            $pdf::Ln();
        }
        $pdf::Output('ReporteTarjeta.pdf');
    }

    public function excel(Request $request){
        $nombre           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('dmc.pagotarjeta','>',0);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $resultado = $resultado->where('dmc.situaciontarjeta','LIKE',$situacion);
        }
        $resultado        = $resultado->orderBy('movimiento.fecha', 'ASC')
                            ->select('movimiento.fecha','dmc.id','dmc.recibo','dmc.situaciontarjeta','s.nombre as servicio','dmc.descripcion as servicio2','dmc.fechaentrega','dmc.servicio_id','dmc.pagotarjeta',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id');

        $lista            = $resultado->get();
        
        Excel::create('ExcelPagoTarjeta', function($excel) use($lista,$request) {
 
            $excel->sheet('Boleteo Total', function($sheet) use($lista,$request) {
 
                $cabecera = array();
                $cabecera[] = "Nro";
                $cabecera[] = "Fecha";
                $cabecera[] = "Recbo";
                $cabecera[] = "Paciente";
                $cabecera[] = "Concepto";
                $cabecera[] = "Total";
                $cabecera[] = "Usuario";
                $cabecera[] = "Fecha Ent";
                $c=2;$d=3;$band=true;$nro=1;
                $sheet->row(1,$cabecera);

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = $nro;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->recibo;
                    $detalle[] = $value->paciente;
                    if($value->servicio_id>0){
                        $detalle[] = $value->servicio;
                    }else{
                        $detalle[] = $value->servicio2;
                    }
                    $detalle[] = number_format($value->pagotarjeta);
                    $detalle[] = $value->responsable;
                    if ($value->fechaentrega != '0000-00-00') {
                        $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                    }
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                    $nro++;
                }

            });
        })->export('xls');

    }
}
