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

class MTCPDF extends TCPDF {

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(190, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

/**
 * PagodoctorController
 * 
 * @package 
 * @author DaYeR
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class ReportereferidoController extends Controller
{
    protected $folderview      = 'app.reportereferido';
    protected $tituloAdmin     = 'Reporte de Referido';
    protected $tituloRegistrar = 'Registrar Referido';
    protected $tituloModificar = 'Modificar Referido';
    protected $tituloEliminar  = 'Eliminar Concepto de Pago';
    protected $rutas           = array('create' => 'reportereferido.create', 
            'edit'   => 'reportereferido.edit', 
            'delete' => 'reporteconsulta.eliminar',
            'search' => 'reportereferido.buscar',
            'index'  => 'reportereferido.index',
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
        $entidad          = 'Reportereferido';
        $paciente         = Libreria::getParam($request->input('paciente'));
        $tiposervicio_id  = Libreria::getParam($request->input('tiposervicio_id'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->where('mref.situacion','<>','A');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tiposervicio_id!=""){
            $tiposervicio_id = explode(",",$tiposervicio_id);
            $resultado = $resultado->whereIn(DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else dmc.tiposervicio_id end'),$tiposervicio_id);   
        }
        $resultado = $resultado->where('movimiento.plan_id','=',6);   
        
        $resultado        = $resultado->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('movimiento.id','mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio',DB::raw('case when dmc.servicio_id>0 then  s.tiposervicio_id else dmc.tiposervicio_id end as tiposervicio_id'),'dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'));
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago Hosp.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Referido', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '1');
        
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
        $entidad          = 'Reportereferido';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoServicio          = array("5" => "Rayos", "4" => "Ecografias", "16" => "Tomografias");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoServicio'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Reporterayos';
        $conceptopago = null;
        $formData            = array('reporterayos.store');
        $cboTipo          = array("Ingreso" => "Ingreso", "Egreso" => "Egreso");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('reporterayos', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
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
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $movimiento = Movimiento::find($id);
        $entidad             = 'Reportereferido';
        $formData            = array('reportereferido.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($request, $id){
            $movimiento        = Movimiento::find($id);
            $movimiento->doctor_id = $request->input('referido_id');
            $movimiento->save();
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

    public function pdf(Request $request){
        $paciente         = Libreria::getParam($request->input('paciente'));
        $tipopaciente     = Libreria::getParam($request->input('tipopaciente'));
        $tiposervicio_id  = Libreria::getParam($request->input('tiposervicio_id'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->where('mref.situacion','<>','A');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tiposervicio_id!=""){
            $tiposervicio_id = explode(",",$tiposervicio_id);
            $resultado = $resultado->whereIn(DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else dmc.tiposervicio_id end'),$tiposervicio_id);   
        }
        $resultado = $resultado->where('movimiento.plan_id','=',6); 

        $resultado        = $resultado->orderBy('referido.apellidopaterno','asc')->orderBy('referido.apellidomaterno','asc')->orderBy('referido.nombres','asc')->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.descuento','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta',DB::raw('case when dmc.servicio_id>0 then  s.tiposervicio_id else dmc.tiposervicio_id end as tiposervicio_id'),'movimiento.tipotarjeta','movimiento.situacion as situacion2',DB::raw('historia.numero as historia'),'dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'));
        $lista            = $resultado->get();
        $pdf = new MTCPDF();
        $pdf::SetTitle('Reporte de Referido');
        $pdf::setFooterCallback(function($pdf) {
                $pdf->SetY(-15);
                // Set font
                $pdf->SetFont('helvetica', 'I', 8);
                // Page number
                $pdf->Cell(0, 10, 'Pag. '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        });
        if (count($lista) > 0) {            
            $pdf::AddPage();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Reporte de Referido del ".date("d/m/Y",strtotime($fechainicial))." al ".date("d/m/Y",strtotime($fechafinal))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(14,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(40,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("PAGADO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("MEDICO"),1,0,'C');            
            $pdf::Cell(30,6,utf8_decode("REAL. PROC-"),1,0,'C');
            //$pdf::Cell(10,6,utf8_decode("CANT."),1,0,'C');
            $pdf::Cell(45,6,utf8_decode("SERVICIO"),1,0,'C');
            $pdf::Ln();
            $fecha="";$total=0;$totalgeneral=0;$referido_id=0;$c=0;$d=0;
            foreach ($lista as $key => $value){
                if($referido_id!=$value->referido_id){
                    $pdf::SetFont('helvetica','B',7);
                    if($referido_id!=""){
                        $pdf::SetFont('helvetica','B',7);
                        $pdf::Cell(84,6,"",0,0,'R');
                        $pdf::Cell(15,6,number_format($total,2,'.',''),1,0,'C');
                        $totalgeneral = $totalgeneral + $total;
                        $total=0;
                        $pdf::Ln();
                    }
                    $referido_id=$value->referido_id;
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(174,6,($value->referido),1,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',6.5);
                $pdf::Cell(14,6,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                $pdf::Cell(40,6,substr($value->paciente2,0,25),1,0,'L');
                if($value->total>0){
                    $pdf::Cell(15,6,($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero,1,0,'L');
                }else{
                    $pdf::Cell(15,6,'PREF. '.$value->numero2,1,0,'L');
                }
                $pdf::Cell(15,6,number_format($value->pagohospital*$value->cantidad,2,'.',''),1,0,'R');
                if($value->tiposervicio_id==2){
                    if ($value->descuento== -30) {
                        $pdf::Cell(15,6,number_format((($value->pagohospital*$value->cantidad)/1.3)*0.12/1.18,2,'.',''),1,0,'R');
                        $total = $total + number_format((($value->pagohospital*$value->cantidad)/1.3)*0.12/1.18,2,'.','');
                    } else {
                        $pdf::Cell(15,6,number_format($value->pagohospital*$value->cantidad*0.12/1.18,2,'.',''),1,0,'R');
                        $total = $total + number_format($value->pagohospital*$value->cantidad*0.12/1.18,2,'.','');    
                    }
                }elseif($value->tiposervicio_id==4){
                    if ($value->descuento== -50) {
                        $pdf::Cell(15,6,number_format((($value->pagohospital*$value->cantidad)/1.5)*0.1/1.18,2,'.',''),1,0,'R');
                        $total = $total + number_format((($value->pagohospital*$value->cantidad)/1.5)*0.1/1.18,2,'.','');
                    } else {
                        $pdf::Cell(15,6,number_format($value->pagohospital*$value->cantidad*0.1/1.18,2,'.',''),1,0,'R');
                        $total = $total + number_format($value->pagohospital*$value->cantidad*0.1/1.18,2,'.','');
                    }
                }elseif($value->tiposervicio_id==5){
                    if ($value->descuento== -50) {
                        $pdf::Cell(15,6,number_format((($value->pagohospital*$value->cantidad)/1.5)*0.1/1.18,2,'.',''),1,0,'R');
                        $total = $total + number_format((($value->pagohospital*$value->cantidad)/1.5)*0.1/1.18,2,'.','');
                    } else {
                        $pdf::Cell(15,6,number_format($value->pagohospital*$value->cantidad*0.1/1.18,2,'.',''),1,0,'R');
                        $total = $total + number_format($value->pagohospital*$value->cantidad*0.1/1.18,2,'.','');
                    }
                }else{
                    $pdf::Cell(15,6,number_format(0,2,'.',''),1,0,'R');
                }
                $pdf::Cell(30,6,substr($value->medico,0,15),1,0,'L');
                //$pdf::Cell(10,6,round($value->cantidad,0),1,0,'R');
                if($value->servicio_id>0){
                    $pdf::Cell(45,6,substr($value->servicio,0,30),1,0,'L');
                }else{
                    $pdf::Cell(45,6,substr($value->servicio2,0,30),1,0,'L');
                }
                $pdf::Ln();         
                $c=$c+1;       
            }
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(84,6,"",0,0,'R');
            $pdf::Cell(15,6,number_format($total,2,'.',''),1,0,'C');
            $totalgeneral = $totalgeneral + $total;
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(84,6,"TOTAL A DISTRIBUIR:",0,0,'R');
            $pdf::Cell(15,6,"S/. ".number_format($totalgeneral,2,'.',''),0,0,'R');
            $pdf::Ln();
        }

        $pdf::Output('ReporteReferido.pdf');
    }


    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $tiposervicio_id  = Libreria::getParam($request->input('tiposervicio_id'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->where('mref.situacion','<>','A');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tiposervicio_id!=""){
            $tiposervicio_id = explode(",",$tiposervicio_id);
            $resultado = $resultado->whereIn(DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else dmc.tiposervicio_id end'),$tiposervicio_id);   
        }
        $resultado = $resultado->where('movimiento.plan_id','=',6); 

        $resultado        = $resultado->orderBy('referido.apellidopaterno','asc')->orderBy('referido.apellidomaterno','asc')->orderBy('referido.nombres','asc')->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.descuento','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta',DB::raw('case when dmc.servicio_id>0 then  s.tiposervicio_id else dmc.tiposervicio_id end as tiposervicio_id'),'movimiento.tipotarjeta','movimiento.situacion as situacion2',DB::raw('historia.numero as historia'),'dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'));
        $lista            = $resultado->get();

        Excel::create('ExcelReporte', function($excel) use($lista,$request) {
 
            $excel->sheet('Reporte', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Pago Hosp.";
                $cabecera[] = "Pago Doctor";
                $cabecera[] = "Doctor";
                $cabecera[] = "Servicio";
                $cabecera[] = "Referido" ;
                $cabecera[] = "Responsable" ;
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;$referido_id="";$total=0;$totalgeneral=0;

                foreach ($lista as $key => $value){
                    if($referido_id!=$value->referido_id){
                        if($referido_id!=""){
                            $detalle = array();
                            $detalle[] = '';
                            $detalle[] = '';
                            $detalle[] = '';
                            $detalle[] = 'TOTAL';
                            $detalle[] = number_format($total,2,'.','');
                            $totalgeneral = $totalgeneral + $total;
                            $sheet->row($c,$detalle);
                            $c=$c+1;    
                        }
                        $detalle = array();
                        $detalle[] = $value->referido;
                        $sheet->row($c,$detalle);
                        $total=0;
                        $c=$c+1;
                    }
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->paciente2;
                    if($value->total>0)
                        $detalle[] = ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero;
                    else
                        $detalle[] = 'PREF. '.$value->numero2;
                    $detalle[] = number_format($value->pagohospital*$value->cantidad,2,'.','');
                    if($value->tiposervicio_id==2){
                        if ($value->descuento == -30) {
                            $detalle[] = number_format((($value->pagohospital*$value->cantidad)/1.3)*0.12/1.18,2,'.','');
                            $total = $total + number_format((($value->pagohospital*$value->cantidad)/1.3)*0.12/1.18,2,'.','');
                        } else {
                            $detalle[] = number_format($value->pagohospital*$value->cantidad*0.12/1.18,2,'.','');
                            $total = $total + number_format($value->pagohospital*$value->cantidad*0.12/1.18,2,'.','');
                        }
                    }elseif($value->tiposervicio_id==4){
                        if ($value->descuento == -50) {
                           $detalle[] = number_format((($value->pagohospital*$value->cantidad)/1.5)*0.1/1.18,2,'.','');
                            $total = $total + number_format((($value->pagohospital*$value->cantidad)/1.5)*0.1/1.18,2,'.','');
                        } else {
                            $detalle[] = number_format($value->pagohospital*$value->cantidad*0.1/1.18,2,'.','');
                            $total = $total + number_format($value->pagohospital*$value->cantidad*0.1/1.18,2,'.','');
                        }
                    }elseif($value->tiposervicio_id==5){
                        if ($value->descuento == -50) {
                            $detalle[] = number_format((($value->pagohospital*$value->cantidad)/1.5)*0.1/1.18,2,'.','');
                            $total = $total + number_format((($value->pagohospital*$value->cantidad)/1.5)*0.1/1.18,2,'.','');
                        } else {
                            $detalle[] = number_format($value->pagohospital*$value->cantidad*0.1/1.18,2,'.','');
                            $total = $total + number_format($value->pagohospital*$value->cantidad*0.1/1.18,2,'.','');
                        }
                    }else{
                        $detalle[] = '';
                    }
                    $detalle[] = $value->medico;
                    if($value->servicio_id>0)
                        $detalle[] = $value->servicio;
                    else
                        $detalle[] = $value->servicio2;
                    if($value->referido_id>0)
                        $detalle[] = $value->referido;
                    else
                        $detalle[] = "NO REFERIDO";
                    $detalle[] = $value->responsable;
                    $sheet->row($c,$detalle);
                    $referido_id=$value->referido_id;
                    $c=$c+1;
                }
                $detalle = array();
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = 'TOTAL';
                $detalle[] = number_format($total,2,'.','');
                $totalgeneral = $totalgeneral + $total;
                $sheet->row($c,$detalle);
                $c=$c+1; 

                $detalle = array();
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = 'TOTAL A DISTRIBUIR';
                $detalle[] = number_format($totalgeneral,2,'.','');
                $sheet->row($c,$detalle);
                
            });
        })->export('xls');
    }

    public function excelresumen(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $tiposervicio_id  = Libreria::getParam($request->input('tiposervicio_id'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->where('mref.situacion','<>','A');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tiposervicio_id!=""){
            $tiposervicio_id = explode(",",$tiposervicio_id);
            $resultado = $resultado->whereIn(DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else dmc.tiposervicio_id end'),$tiposervicio_id);   
        }
        $resultado = $resultado->where('movimiento.plan_id','=',6); 

        $resultado        = $resultado->orderBy('referido.apellidopaterno','asc')->orderBy('referido.apellidomaterno','asc')->orderBy('referido.nombres','asc')->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.descuento','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta',DB::raw('case when dmc.servicio_id>0 then  s.tiposervicio_id else dmc.tiposervicio_id end as tiposervicio_id'),'movimiento.tipotarjeta','movimiento.situacion as situacion2',DB::raw('historia.numero as historia'),'dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'));
        $lista            = $resultado->get();

        Excel::create('ExcelReporte', function($excel) use($lista,$request) {
 
            $excel->sheet('Reporte', function($sheet) use($lista,$request) {
                $cabecera[] = "Doctor";
                $cabecera[] = "Pago Doctor";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;$referido_id="";$total=0;$totalgeneral=0;

                foreach ($lista as $key => $value){
                    if($referido_id!=$value->referido_id){
                        if($referido_id!=""){
                            $detalle[] = 'TOTAL';
                            $detalle[] = number_format($total,2,'.','');
                            $totalgeneral = $totalgeneral + $total;
                            $sheet->row($c,$detalle);
                            $c=$c+1;    
                        }
                        $detalle = array();
                        $detalle[] = $value->referido;
                        $total=0;
                    }
                    if($value->tiposervicio_id==2){
                        if ($value->descuento == -30) {
                            $total = $total + number_format((($value->pagohospital*$value->cantidad)/1.3)*0.12/1.18,2,'.','');
                        } else {
                            $total = $total + number_format($value->pagohospital*$value->cantidad*0.12/1.18,2,'.','');
                        }
                    }elseif($value->tiposervicio_id==4){
                        if ($value->descuento == -50) {
                            $total = $total + number_format((($value->pagohospital*$value->cantidad)/1.5)*0.1/1.18,2,'.','');
                        } else {
                            $total = $total + number_format($value->pagohospital*$value->cantidad*0.1/1.18,2,'.','');
                        }
                    }elseif($value->tiposervicio_id==5){
                        if ($value->descuento == -50) {
                            $total = $total + number_format((($value->pagohospital*$value->cantidad)/1.5)*0.1/1.18,2,'.','');
                        } else {
                            $total = $total + number_format($value->pagohospital*$value->cantidad*0.1/1.18,2,'.','');
                        }
                    }
                    $referido_id=$value->referido_id;
                    //$c=$c+1;
                }
                $detalle[] = 'TOTAL';
                $detalle[] = number_format($total,2,'.','');
                $totalgeneral = $totalgeneral + $total;
                $sheet->row($c,$detalle);
                $c=$c+1; 

                $detalle = array();
                $detalle[] = 'TOTAL A DISTRIBUIR';
                $detalle[] = '';
                $detalle[] = number_format($totalgeneral,2,'.','');
                $sheet->row($c,$detalle);
                
            });
        })->export('xls');
    }
}
