<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Conceptopago;
use App\Movimiento;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
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
class ReporteventaController extends Controller
{
    protected $folderview      = 'app.reporteventa';
    protected $tituloAdmin     = 'Reporte de Venta';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Modificar Concepto de Pago';
    protected $tituloEliminar  = 'Eliminar Concepto de Pago';
    protected $rutas           = array('create' => 'reporteventa.create', 
            'edit'   => 'conceptopago.edit', 
            'delete' => 'conceptopago.eliminar',
            'search' => 'conceptopago.buscar',
            'index'  => 'reporteventa.index',
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
        $entidad          = 'Reportefacturacion';
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where('plan.nombre','like','%'.$plan.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->where('movimiento.situacion','<>','U');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        $resultado        = $resultado->orderBy(DB::raw('movimiento.fecha'), 'desc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'));
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago Doc', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago Hosp.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Plan', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cant.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        
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
        $entidad          = 'Reporteventa';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoPaciente          = array("" => "Todos", "P" => "Particular", "C" => "Convenio");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoPaciente'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Reporteventa';
        $conceptopago = null;
        $formData            = array('reporteventa.store');
        $cboTipo          = array("Ingreso" => "Ingreso", "Egreso" => "Egreso");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('reportefacturacion', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $conceptopago = Conceptopago::find($id);
        $entidad             = 'conceptopago';
        $formData            = array('Conceptopago.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('conceptopago', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
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
        $error = DB::transaction(function() use($request, $id){
            $categoria                        = Categoria::find($id);
            $categoria->nombre = strtoupper($request->input('nombre'));
            $Conceptopago->tipo = $request->input('tipo');
            $categoria->save();
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
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Conceptopago = Conceptopago::find($id);
            $Conceptopago->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Conceptopago::find($id);
        $entidad  = 'conceptopago';
        $formData = array('route' => array('conceptopago.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $resultado        = Movimiento::join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->join('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->join('person as paciente','paciente.id','=','mref.persona_id')
                            ->where('mref.tipomovimiento_id','=',4)
                            ->where('plan.id','=',6)
                            ->where('movimiento.situacion','<>','U')
                            ->where('movimiento.situacion','<>','A')
                            ->where('mref.situacion','<>','A');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        $resultado        = $resultado->orderBy(DB::raw('mref.fecha'), 'asc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','mref.tipodocumento_id','mref.serie','mref.numero',DB::raw('mref.fecha'),'movimiento.tarjeta','movimiento.tipotarjeta','mref.situacion as situacion2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.ruc','paciente.bussinesname as empresa2','paciente.dni',DB::raw('responsable.nombres as responsable'),'movimiento.comentario','movimiento.soat');
        $lista            = $resultado->get();

        Excel::create('ExcelReporteVentas', function($excel) use($lista,$request) {
 

            $total=0;$tarjeta=0;$contado=0;$pendiente=0;
            $excel->sheet('VentaAdmisionPart', function($sheet) use($lista,$request,&$total,&$contado,&$pendiente,&$tarjeta) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Serie";
                $cabecera[] = "Numero";
                $cabecera[] = "Paciente/Cliente";
                $cabecera[] = "DNI / RUC";
                $cabecera[] = "Forma Pago";
                $cabecera[] = "Total";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = ($value->tipodocumento_id==5?'B':'F').$value->serie;
                    $detalle[] = $value->numero;                    
                    if($value->tipodocumento_id==5){
                        $detalle[] = $value->paciente2;
                        $detalle[] = $value->dni;
                    }else{
                        $detalle[] = $value->empresa2;
                        $detalle[] = $value->ruc;
                    }
                    if($value->situacion2=='P'){
                        $formapago='PENDIENTE';
                        $pendiente = $pendiente + number_format($value->total,2,'.','');
                    }else{
                        if($value->tarjeta!=""){
                            $formapago='TARJETA';
                            $tarjeta = $tarjeta + number_format($value->total,2,'.','');
                        }else{
                            $formapago='CONTADO';
                            $contado = $contado + number_format($value->total,2,'.','');
                        }
                    }
                    $detalle[] = $formapago;
                    $detalle[] = number_format($value->total,2,'.','');
                    $total = $total + number_format($value->total,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "TOTAL PARTICULAR";
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
            });


            $resultado        = Movimiento::join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->join('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->join('person as paciente','paciente.id','=','mref.persona_id')
                            ->where('mref.tipomovimiento_id','=',4)
                            ->where('plan.id','<>',6)
                            ->where('movimiento.situacion','<>','U')
                            ->where('movimiento.situacion','<>','A')
                            ->where('mref.situacion','<>','A');
            if($request->input('fechainicial')!=""){
                $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
            }
            if($request->input('fechafinal')!=""){
                $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
            }
            $resultado        = $resultado->orderBy(DB::raw('mref.fecha'), 'asc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                                ->select('mref.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','mref.tipodocumento_id','mref.serie','mref.numero',DB::raw('mref.fecha'),'movimiento.tarjeta','movimiento.tipotarjeta','mref.situacion as situacion2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.ruc','paciente.bussinesname as empresa2','paciente.dni',DB::raw('responsable.nombres as responsable'),'movimiento.comentario','movimiento.soat');
            $lista            = $resultado->get();

            $total0=0;$pendiente0=0;$tarjeta0=0;$contado0=0;
            $excel->sheet('VentaAdmisionConv', function($sheet) use($lista,$request,&$total0,&$contado0,&$pendiente0,&$tarjeta0) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Serie";
                $cabecera[] = "Numero";
                $cabecera[] = "Paciente/Cliente";
                $cabecera[] = "DNI / RUC";
                $cabecera[] = "Forma Pago";
                $cabecera[] = "Total";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = ($value->tipodocumento_id==5?'B':'F').$value->serie;
                    $detalle[] = $value->numero;                    
                    if($value->tipodocumento_id==5){
                        $detalle[] = $value->paciente2;
                        $detalle[] = $value->dni;
                    }else{
                        $detalle[] = $value->empresa2;
                        $detalle[] = $value->ruc;
                    }
                    if($value->situacion2=='P'){
                        $formapago='PENDIENTE';
                        $pendiente0 = $pendiente0 + number_format($value->total,2,'.','');
                    }else{
                        if($value->tarjeta!=""){
                            $formapago='TARJETA';
                            $tarjeta0 = $tarjeta0 + number_format($value->total,2,'.','');
                        }else{
                            $formapago='CONTADO';
                            $contado0 = $contado0 + number_format($value->total,2,'.','');
                        }
                    }
                    $detalle[] = $formapago;
                    $detalle[] = number_format($value->total,2,'.','');
                    $total0 = $total0 + number_format($value->total,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "TOTAL CONVENIO";
                $detalle[] = number_format($total0,2,'.','');
                $sheet->row($c,$detalle);
            });


            $resultado        = Movimiento::join('person as paciente','paciente.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->leftjoin('person as empresa','empresa.id','=','movimiento.empresa_id')
                                ->where('movimiento.tipomovimiento_id','=',4)
                                ->where('movimiento.situacion','<>','U')
                                ->where('movimiento.situacion','<>','A')
                                ->where('movimiento.tipodocumento_id','<>','15')
                                ->where('movimiento.ventafarmacia','=','S');
            if($request->input('fechainicial')!=""){
                $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
            }
            if($request->input('fechafinal')!=""){
                $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
            }
            $resultado        = $resultado->orderBy(DB::raw('movimiento.fecha'), 'asc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                                ->select('movimiento.total','movimiento.tipomovimiento_id','movimiento.tipodocumento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','movimiento.estadopago',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'empresa.ruc','empresa.bussinesname as empresa2','paciente.dni',DB::raw('responsable.nombres as responsable'),'movimiento.comentario','movimiento.soat');
            $lista            = $resultado->get();

            $total1=0;$contado1=0;$pendiente1=0;$tarjeta1=0;
            $excel->sheet('VentaFarmacia', function($sheet) use($lista,$request,&$total1,&$contado1,&$pendiente1,&$tarjeta1) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Serie";
                $cabecera[] = "Numero";
                $cabecera[] = "Paciente/Cliente";
                $cabecera[] = "DNI / RUC";
                $cabecera[] = "Forma Pago";
                $cabecera[] = "Total";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = ($value->tipodocumento_id==5?'B':'F').$value->serie;
                    $detalle[] = $value->numero;
                    if($value->tipodocumento_id==5){
                        $detalle[] = $value->paciente2;
                        $detalle[] = $value->dni;
                    }else{
                        $detalle[] = $value->empresa2;
                        $detalle[] = $value->ruc;
                    }
                    if($value->estadopago=='PP'){
                        $formapago='PENDIENTE';
                        $pendiente1 = $pendiente1 + number_format($value->total,2,'.','');
                    }else{
                        if($value->formapago=='T'){
                            $formapago='TARJETA';
                            $tarjeta1 = $tarjeta1 + number_format($value->total,2,'.','');
                        }else{
                            $formapago='CONTADO';
                            $contado1 = $contado1 + number_format($value->total,2,'.','');
                        }
                    }
                    $detalle[] = $formapago;
                    $detalle[] = number_format($value->total,2,'.','');
                    $total1 = $total1 + number_format($value->total,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "TOTAL FARMACIA";
                $detalle[] = number_format($total1,2,'.','');
                $sheet->row($c,$detalle);
            });

            $excel->sheet('ResumenVentas', function($sheet) use($lista,$request,$total,$total1,$total0,$pendiente,$pendiente1,$pendiente0,$contado0,$contado1,$contado,$tarjeta0,$tarjeta1,$tarjeta) {
                $cabecera[] = "";
                $cabecera[] = "CONTADO";
                $cabecera[] = "TARJETA";
                $cabecera[] = "PENDIENTE";
                $cabecera[] = "TOTAL";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;
                $detalle = array();
                $detalle[] = "VENTA ADMISION PART";
                $detalle[] = number_format($contado,2,'.','');
                $detalle[] = number_format($tarjeta,2,'.','');
                $detalle[] = number_format($pendiente,2,'.','');
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
                $c=$c+1;
                $detalle = array();
                $detalle[] = "VENTA ADMISION CONV";
                $detalle[] = number_format($contado0,2,'.','');
                $detalle[] = number_format($tarjeta0,2,'.','');
                $detalle[] = number_format($pendiente0,2,'.','');
                $detalle[] = number_format($total0,2,'.','');
                $sheet->row($c,$detalle);
                $c=$c+1;
                $detalle = array();
                $detalle[] = "VENTA FARMACIA";
                $detalle[] = number_format($contado1,2,'.','');
                $detalle[] = number_format($tarjeta1,2,'.','');
                $detalle[] = number_format($pendiente1,2,'.','');
                $detalle[] = number_format($total1,2,'.','');
                $sheet->row($c,$detalle);
                $c=$c+1;
                $detalle = array();
                $detalle[] = "TOTAL";
                $detalle[] = number_format($contado1+$contado0+$contado,2,'.','');
                $detalle[] = number_format($tarjeta+$tarjeta1+$tarjeta0,2,'.','');
                $detalle[] = number_format($pendiente1+$pendiente+$pendiente0,2,'.','');
                $detalle[] = number_format($total1+$total+$total0,2,'.','');
                $sheet->row($c,$detalle);
            });

            $resultado        = Movimiento::join('person as paciente','paciente.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->leftjoin('person as empresa','empresa.id','=','movimiento.empresa_id')
                                ->join('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                                ->where('movimiento.tipomovimiento_id','=',3)

                                ->where('movimiento.situacion','<>','U')
                                ->where('movimiento.situacion','<>','A');
            if($request->input('fechainicial')!=""){
                $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
            }
            if($request->input('fechafinal')!=""){
                $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
            }
            $resultado        = $resultado->orderBy(DB::raw('movimiento.fecha'), 'asc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                                ->select('movimiento.total','movimiento.tipomovimiento_id','movimiento.tipodocumento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','movimiento.estadopago',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.ruc','paciente.bussinesname as empresa2','paciente.dni',DB::raw('responsable.nombres as responsable'),'movimiento.comentario','movimiento.soat');
            $lista            = $resultado->get();

            $list=array();
            $excel->sheet('ComprasFarmacia', function($sheet) use($lista,$request,&$list) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Serie";
                $cabecera[] = "Numero";
                $cabecera[] = "Paciente/Cliente";
                $cabecera[] = "DNI / RUC";
                $cabecera[] = "Total";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;
                $total1=0;
                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = ($value->tipodocumento_id==6?'B':'F').$value->serie;
                    $detalle[] = $value->numero;
                    $detalle[] = $value->empresa2;
                    $detalle[] = $value->ruc;
                    if(!isset($list[$value->empresa2])){
                        $list[$value->empresa2]=array("empresa"=>$value->empresa2,"total"=>0);
                    }
                    $list[$value->empresa2]["total"]=$list[$value->empresa2]["total"] + $value->total;
                    $detalle[] = number_format($value->total,2,'.','');
                    $total1 = $total1 + number_format($value->total,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "TOTAL COMPRAS";
                $detalle[] = number_format($total1,2,'.','');
                $sheet->row($c,$detalle);
            });

            $excel->sheet('ResumenCompras', function($sheet) use($list) {
                $cabecera[] = "EMPRESA";
                $cabecera[] = "TOTAL";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;
                $total=0;
                foreach ($list as $key => $value) {
                    $detalle = array();
                    $detalle[] = $value["empresa"];
                    $detalle[] = number_format($value["total"],2,'.','');
                    $total = $total + $value["total"];
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
                $detalle = array();
                $detalle[] = "TOTAL";
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
            });

        })->export('xls');
    }

}
