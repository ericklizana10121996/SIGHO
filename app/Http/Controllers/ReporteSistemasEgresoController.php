<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Conceptopago;
use App\Movimiento;
use App\Especialidad;

use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;

use Excel;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;

/**
 * PagodoctorController
 * 
 * @package 
 * @author DaYeR
 * @copyright 2017
 * @version $Id$
 * @access public
 */

class ReporteSistemasEgresoController extends Controller
{
   
    protected $folderview      = 'app.reportegreso';
    protected $tituloAdmin     = 'Reportes Gerenciales';
    protected $tituloRegistrar = 'Registrar Egreso de Pago';
    protected $tituloModificar = 'Modificar Egreso de Pago';
    protected $tituloEliminar  = 'Eliminar Egreso de Pago';
    protected $rutas           = array('create' => 'reportegreso.create', 
            'edit'   => 'reporteconsulta.edit', 
            'delete' => 'reporteconsulta.eliminar',
            'search' => 'reportegreso.buscar',
            'index'  => 'reportegreso.index',
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
        $entidad          = 'ReporteEgresos';
        $doctor           = Libreria::getParam($request->input('doctor'));
        // $tipopaciente     = Libreria::getParam($request->input('tipopaciente'));
        // $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));

        if(is_null($doctor)){
            $doctor = '';
        }

        $lista = DB::select('CALL sp_reporte_sistemas(?,?,?)',array($doctor, $fechainicial, $fechafinal));      
        // dd($lista);

        $resultado = Movimiento::join('conceptopago as concep','concep.id','=','movimiento.conceptopago_id')
                ->join('person as doc_ref','doc_ref.id','=','movimiento.persona_id')
                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                // ->leftjoin('detallemovcaja as det','det.movimiento_id','=', 'movimiento.id')
                // ->leftjoin('especialidad as esp','doc_ref.especialidad_id','=','esp.id')
                ->whereNotNull('doc_ref.especialidad_id')
                ->whereIn('conceptopago_id',[8,34,35])
                ->orderBy('movimiento.fecha', 'ASC')->orderBy('doctor_resp','ASC')
                ->where(DB::raw("CONCAT(doc_ref.apellidopaterno,' ', doc_ref.apellidomaterno,' ',doc_ref.nombres)"), 'LIKE',$doctor.'%')
                ->whereBetween('movimiento.fecha',[$fechainicial, $fechafinal])
                ->select('movimiento.id as id_mov','movimiento.serie as serie_mov','movimiento.numero as num_mov','movimiento.fecha as fecha_mov',DB::raw("CONCAT(doc_ref.apellidopaterno,' ', doc_ref.apellidomaterno,' ', doc_ref.nombres) as doctor_resp"),'movimiento.total as total_venta', 'concep.nombre as conceptopago', DB::raw("CONCAT(responsable.apellidopaterno,' ', responsable.apellidomaterno,' ',responsable.nombres) as responsable_op"/*,'det.*'*/),'movimiento.comentario'/*,'det.*'*/);


        // $lista  =  $resultado->get();
    
        $cabecera         = array();

        $cabecera[]       = array('valor' => 'ID Mov.', 'numero' => '1');                
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Numero', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Concepto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
      
        // $cabecera[]       = array('valor' => 'Cant.', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Referido', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Forma Pago', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $ruta             = $this->rutas;
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];

            // dd($paginacion);

            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = DB::select('CALL sp_reporte_sistemas_limit(?,?,?,?,?)',array($doctor, $fechainicial, $fechafinal, $pagina,$filas));

            //$resultado->paginate($filas);
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
        $entidad          = 'ReporteEgresos';
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
        $entidad             = 'ReporteEgresos';
        $conceptopago = null;
        $formData            = array('reportetomografia.store');
        $cboTipo          = array("Ingreso" => "Ingreso", "Egreso" => "Egreso");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('reportetomografia', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
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
        $doctor           = Libreria::getParam($request->input('doc'));
        $fechainicial     = Libreria::getParam($request->input('fi'));
        $fechafinal       = Libreria::getParam($request->input('ff'));
        if (is_null($doctor)) {
            $doctor = '';
        }
        $resultado = DB::select('CALL sp_reporte_sistemas(?,?,?)',array($doctor, $fechainicial, $fechafinal));

                     
        Excel::create('ExcelReporteEgresos', function($excel) use($resultado,$request) {
 
            $excel->sheet('ConsultaEgresos', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "ID Mov.";
                $cabecera[] = "Fecha";
                $cabecera[] = "Numero";
                $cabecera[] = "Doctor";
                $cabecera[] = "Concepto";
                $cabecera[] = "Comentario";
               
                $cabecera[] = "Total";
                $cabecera[] = "Usuario";
                $array[] = $cabecera;
                $c=3;$d=3;$band=true;


                foreach ($resultado as $key => $value){
                    $detalle = array();
                    $detalle[] = $value->id_mov;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha_mov));
                    $detalle[] = is_null($value->serie_mov)?$value->num_mov:$value->serie_mov.'-'.$value->num_mov;
                    $detalle[] = $value->doctor_resp;
                    $detalle[] = $value->conceptopago;
                    $detalle[] = $value->comentario;
                    $detalle[] = $value->total_venta;
                    $detalle[] = $value->responsable_op;
                    $array[] = $detalle;                    
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function excel02(Request $request){
        setlocale(LC_TIME, 'spanish');
        $fechainicial     = Libreria::getParam($request->input('fi'));
        $fechafinal       = Libreria::getParam($request->input('ff'));
       
      
        $resultado = Movimiento::leftJoin('detallemovcaja as det','det.movimiento_id','=','movimiento.movimiento_id')
                     ->leftJoin('person as med','med.id','=','det.persona_id')
                     ->whereNotNull('med.especialidad_id')
                     ->whereNotNull('movimiento.serie')
                     ->whereNotNull('movimiento.movimiento_id')
                     // ->whereNotNull('movimiento.deleted_at')
                     ->whereNull('movimiento.conceptopago_id')
                     ->where('movimiento.tipomovimiento_id','=',4)
                     ->where('det.situacion','<>','A')
                     ->where('movimiento.situacion','!=','A');

        if($fechainicial != '' && $fechafinal != ''){
            $resultado = $resultado->whereBetween('movimiento.fecha',[$fechainicial, $fechafinal]);
        }

        $resultado = $resultado->select('det.*','movimiento.serie','movimiento.numero','movimiento.fecha','movimiento.tipodocumento_id')->orderBy('movimiento.fecha','ASC')
                     ->get();

       
                     
        Excel::create('ExcelReporteHospital', function($excel) use($resultado,$request) {
 
            $excel->sheet('ReporteHospital', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
               
                $cabecera[] = "Fecha";
                $cabecera[] = "Documento";
               
                $cabecera[] = "Pago Medico";
                $cabecera[] = "Pago Medico Invitado";
                $cabecera[] = "Pago Hospital";
                $cabecera[] = "Total (Boleta)";
                $cabecera[] = "Total Neto (Hospital) ";
               
            
                $array[] = $cabecera;
                $c=3;$d=3;$band=true;


                foreach ($resultado as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = ($value->tipodocumento_id==5?'B':'F').$value->serie.'-'.$value->numero;
                    $detalle[] = number_format($value->pagodoctor,2,'.',' ');
                    $detalle[] = number_format($value->pagosocio,2,'.',' ');
                    $detalle[] = number_format($value->pagohospital,2,'.',' ');
                    $detalle[] = number_format(($value->precio*$value->cantidad),2,'.',' ');
                    $detalle[] = number_format((($value->precio*$value->cantidad)-$value->pagodoctor-$value->pagosocio),2,'.',' ');
  
                    $array[] = $detalle;                    
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }


    public function excel03(Request $request){
        setlocale(LC_TIME, 'spanish');
        $fechainicial     = Libreria::getParam($request->input('fi'));
        $fechafinal       = Libreria::getParam($request->input('ff'));
       
      
        $resultado = Movimiento::leftJoin('movimiento as prefactura','prefactura.id','=','movimiento.movimiento_id')
                     ->leftJoin('detallemovcaja as det','det.movimiento_id','=','prefactura.id')
                     ->join('person as medico','medico.id','=','det.persona_id')
                     ->join('especialidad as esp','esp.id','=','medico.especialidad_id')
                     ->leftJoin('servicio as s','s.id','=','det.servicio_id')
                     // ->leftJoin('tiposervicio as tps','tps.id','=','s.tiposervicio_id')
                     ->leftjoin('tiposervicio as tps',function($join){
                        $join->on('tps.id','=','s.tiposervicio_id')
                            ->orOn('tps.id','=','det.tiposervicio_id');
                     })
                     ->leftJoin('person as referido','referido.id','=','prefactura.doctor_id')
                     // ->join('plan','plan.id','=','movimiento.plan_id')
                     ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                     ->leftJoin('person as med','med.id','=','det.persona_id')
                     // ->leftJoin('person as pac','pac.id','=','movimient.persona_id')
                     // ->whereNotNull('med.especialidad_id')
                     ->whereNotNull('movimiento.serie')
                     ->whereIn('prefactura.plan_id',[6,20,25,26,30])
                     ->where('movimiento.tipomovimiento_id','=',4)
                     ->where('prefactura.situacion','<>','U')
                     ->where('det.situacion','<>','A')
                     ->whereNotIn('det.situacionentrega',['A'])
                     // ->where('movimiento.situacion','!=','A')
                     // ->where('prefactura.situacion','<>','A')
                     ->where(DB::raw('det.descripcion'),'NOT LIKE','%GARANTIA%')
                     ->where(DB::raw('det.descripcion'),'NOT LIKE','%PAGO DE ATENCION PARTICULAR%');
                     // ->where(DB::raw('plan.nombre'),'not like','%PLAN PARTICULAR%');

        if($fechainicial != '' && $fechafinal != ''){
            $resultado = $resultado->whereBetween('movimiento.fecha',[$fechainicial, $fechafinal]);
        }

        $resultado = $resultado->select('det.*','med.socio','prefactura.plan_id','esp.nombre as especialidad','det.nombremedico as medico','movimiento.serie','historia.numero as historia','historia.tipopaciente','movimiento.numero','movimiento.fecha','movimiento.tipodocumento_id','prefactura.nombrepaciente as paciente', 's.nombre as servicio2','tps.nombre as tiposervicio','prefactura.tarjeta','prefactura.tipotarjeta','prefactura.voucher','prefactura.situacion','prefactura.ventafarmacia','prefactura.estadopago','prefactura.formapago','prefactura.nombrepaciente','prefactura.soat','prefactura.condicionpaciente','prefactura.copago', 'prefactura.created_at','prefactura.nombreresponsable as responsable',
            DB::raw("(CASE WHEN (WEEK(movimiento.fecha, 1) - WEEK(DATE_SUB(movimiento.fecha, INTERVAL DAYOFMONTH(movimiento.fecha) - 1 DAY), 1)) = '0' THEN (WEEK(movimiento.fecha, 1) - WEEK(DATE_SUB(movimiento.fecha, INTERVAL DAYOFMONTH(movimiento.fecha) - 1 DAY), 1) + 1) ELSE (WEEK(movimiento.fecha, 1) - WEEK(DATE_SUB(movimiento.fecha, INTERVAL DAYOFMONTH(movimiento.fecha) - 1 DAY), 1)) END) as mes"))->orderByRaw('(mes - movimiento.fecha) DESC')
                     ->get();

        // dd($resultado);
       
                     
        Excel::create('Excel de Atención', function($excel) use($resultado,$request,$fechainicial,$fechafinal) {
            
            $excel->sheet('Detalle de Atención', function($sheet) use($resultado,$request,$fechainicial,$fechafinal) {
                
                $default_border = array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb'=>'000000')
                );

                $style_header = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 10,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );

                 $style_content = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'F6F8FC'),
                    ),
                    'font' => array(
                        'size' => 8,
                        'color' => array('rgb' => '000000'),
                    )
                );

                // $array = array();
                // $cabecera = array();
                $sheet->setCellValue("A1", "DETALLES DE CONSULTAS DEL ".date('d/m/Y',strtotime($fechainicial)) ." AL ".date('d/m/Y',strtotime($fechafinal)));
                
                $sheet->mergeCells("A1:AB1");
                $sheet->getStyle("A1:AB1")->applyFromArray($style_header);
               
                $sheet->setCellValue("A2", "MES");
                $sheet->setCellValue("B2", "SEMANA");
                $sheet->setCellValue("C2", "FECHA");
                $sheet->setCellValue("D2", "T. PACIENTE");
                $sheet->setCellValue("E2", "HISTORIA");
                $sheet->setCellValue("F2", "PACIENTE");
                $sheet->setCellValue("G2", "PLAN");
                $sheet->setCellValue("H2", "FECHA ENTREGA");
                $sheet->setCellValue("I2", "RR.HH DOCTOR");
                $sheet->setCellValue("J2", "DOCTOR");
                $sheet->setCellValue("K2", "TIPO");
                
                $sheet->setCellValue("L2", "CANTIDAD");
                $sheet->setCellValue("M2", "SERVICIO");
                $sheet->setCellValue("N2", "ESPECIALIDAD");
                $sheet->setCellValue("O2", "TIPO SERVICIO");
                $sheet->setCellValue("P2", "PAGO DOCTOR");
                $sheet->setCellValue("Q2", "PAGO SOCIO");
                $sheet->setCellValue("R2", "PAGO HOSPITAL");
                $sheet->setCellValue("S2", "PLAN 10");
                $sheet->setCellValue("T2", "TOTAL");
                $sheet->setCellValue("U2", "REFERIDO");
                $sheet->setCellValue("V2", "NRO DOCUMENTO");
                $sheet->setCellValue("W2", "FORMA PAGO");
                $sheet->setCellValue("X2", "SITUACIÓN");
                $sheet->setCellValue("Y2", "EMERGENCIA");
                $sheet->setCellValue("Z2", "USUARIO");
                $sheet->setCellValue("AA2", "CONDICIÓN");
                $sheet->setCellValue("AB2", "F. REGISTRO");
        
                   $sheet->getStyle("A2")->applyFromArray($style_header);
                   $sheet->getStyle("B2")->applyFromArray($style_header);
                   $sheet->getStyle("C2")->applyFromArray($style_header);
                   $sheet->getStyle("D2")->applyFromArray($style_header);
                   $sheet->getStyle("E2")->applyFromArray($style_header);
                   $sheet->getStyle("F2")->applyFromArray($style_header);
                   $sheet->getStyle("G2")->applyFromArray($style_header);
                   $sheet->getStyle("H2")->applyFromArray($style_header);
                   $sheet->getStyle("I2")->applyFromArray($style_header);
                   $sheet->getStyle("J2")->applyFromArray($style_header);
                   $sheet->getStyle("K2")->applyFromArray($style_header);
                   $sheet->getStyle("L2")->applyFromArray($style_header);
                   $sheet->getStyle("M2")->applyFromArray($style_header);
                   $sheet->getStyle("N2")->applyFromArray($style_header);
                   $sheet->getStyle("O2")->applyFromArray($style_header);
                   $sheet->getStyle("P2")->applyFromArray($style_header);
                   $sheet->getStyle("Q2")->applyFromArray($style_header);
                   $sheet->getStyle("R2")->applyFromArray($style_header);
                   $sheet->getStyle("S2")->applyFromArray($style_header);
                   $sheet->getStyle("T2")->applyFromArray($style_header);
                   $sheet->getStyle("U2")->applyFromArray($style_header);
                   $sheet->getStyle("V2")->applyFromArray($style_header);
                   $sheet->getStyle("W2")->applyFromArray($style_header);
                   $sheet->getStyle("X2")->applyFromArray($style_header);
                   $sheet->getStyle("Y2")->applyFromArray($style_header);
                   $sheet->getStyle("Z2")->applyFromArray($style_header);
                   $sheet->getStyle("AA2")->applyFromArray($style_header);
                   $sheet->getStyle("AB2")->applyFromArray($style_header);
                                               
                $cont_sem = 0;
                $cont = 3;
                foreach ($resultado as $key => $value){
                    $sheet->setCellValue("A".$cont, $this->aLetras(date('m',strtotime($value->fecha))));
            
                    $aux = $this->nroSemana($value->fecha);
                    if ((int)$aux > 0) {
                        $cont_sem = $aux;
                    }

                    // $detalle[] = $aux>0?$cont_sem:$cont_sem+1;
                    $sheet->setCellValue("B".$cont, $aux>0?$cont_sem:$cont_sem+1);
                    $sheet->setCellValue("C".$cont, date('d/m/Y',strtotime($value->fecha)));
                    $sheet->setCellValue("D".$cont, $value->tipopaciente);
                    $sheet->setCellValue("E".$cont, $value->historia);
                    $sheet->setCellValue("F".$cont, $value->paciente);
                    // $sheet->setCellValue("G".$cont, $value->tipopaciente);
                                                 
                    if ($value->plan_id == 6) {
                        $detalle = 'TARIFA PARTICULAR 1';
                    }else if($value->plan_id == 26){
                        $detalle = 'COLEGIO DE INGENIEROS DEL PERÚ';
                    }else if($value->plan_id == 20){
                        $detalle = 'CORIS';
                    }else if($value->plan_id == 30){
                        $detalle = 'MAPFRE -SEGUROS DE VIDA';
                    }else {
                        $detalle = 'RED SALUD';
                    }

                    $sheet->setCellValue("G".$cont, $detalle);
                 
                    // $detalle[] = $value->plan2;
                    if ($value->fechaentrega != "0000-00-00") {
                        $detalle2 = date('d/m/Y',strtotime($value->fechaentrega));
                    } else {
                        $detalle2 = "";
                    }
                    $sheet->setCellValue("H".$cont, $detalle2);
                    $sheet->setCellValue("I".$cont, $value->recibo);
                    $sheet->setCellValue("J".$cont, $value->medico);

                    $sheet->setCellValue("K".$cont, ($value->socio=='N'?'I':'S'));



                    $sheet->setCellValue("L".$cont, number_format($value->cantidad,0,'.',''));
                                      
                    if(!is_null($value->servicio_id)){
                        $detalle3 = $value->servicio2;$nombre = $value->servicio;
                    }
                    else{
                        $detalle3 = $value->descripcion;$nombre = $value->descripcion;
                    }
                    $sheet->setCellValue("M".$cont, $detalle3);
                    
                    $sheet->setCellValue("N".$cont, (trim($value->especialidad)==="PROVEEDOR" || trim($value->especialidad)==="MEDICINA GENERAL" || trim($value->especialidad)==="HOSPITAL")?"":$value->especialidad);

                    $sheet->setCellValue("O".$cont, $value->tiposervicio);
                    $sheet->setCellValue("P".$cont, number_format($value->pagodoctor*$value->cantidad,2,'.',''));
                    $sheet->setCellValue("Q".$cont, "");
                    $sheet->setCellValue("R".$cont, number_format($value->pagohospital*$value->cantidad,2,'.',''));
                   
                    
                    if(strpos($nombre,'CONSULTA') === false && $value->tiposervicio_id!=9) {
                        $detalle5 = "";
                    }else{
                        $detalle5 = "10.00";
                    }
                    if($value->referido_id>0)
                        $detalle6 = $value->referido;
                    else
                        $detalle6 = "NO REFERIDO";
                    // if($value->total>0)
                    $sheet->setCellValue("S".$cont, $detalle5);
                    $sheet->setCellValue("T".$cont, number_format(($value->pagohospital + $value->pagodoctor)*$value->cantidad,2,'.',''));
               
                    $sheet->setCellValue("U".$cont, $detalle6);
                   
                    $sheet->setCellValue("V".$cont, ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero);
                    // else
                    //     $detalle[] = 'PREF. '.$value->numero2;
                    $sheet->setCellValue("W".$cont,$value->situacion=='C'?($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta.'-'.$value->voucher):'CONTADO'):'-');

                    $sheet->setCellValue("X".$cont,($value->situacion=='C'?'Pagado':'Pendiente'));
                    $sheet->setCellValue("Y".$cont,$value->soat);
                    $sheet->setCellValue("Z".$cont,$value->responsable);
                    $sheet->setCellValue("AA".$cont,$value->condicionpaciente);
                    if (is_null($value->created_at) || $value->created_at == '') { 
                        $detalle6 = "-";      
                    }else{
                        $detalle6 = date('H:i:s', strtotime($value->created_at));
                    }
             
                    $sheet->setCellValue("AB".$cont,$detalle6);


                   $sheet->getStyle("A".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("B".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("C".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("D".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("E".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("F".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("G".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("H".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("I".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("J".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("K".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("L".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("M".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("N".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("O".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("P".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("Q".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("R".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("S".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("T".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("U".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("V".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("W".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("X".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("Y".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("Z".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("AA".$cont)->applyFromArray($style_content);
                   $sheet->getStyle("AB".$cont)->applyFromArray($style_content);
                                

                    $cont++;
                    // $array[] = $detalle;                    
                }

                // $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function nroSemana($date) 
    {
        // $date = '2019-12-10';
        // $fecha_act = $date;
        $anio_ini = date('Y',strtotime($date));
        $mes_ini = date('m',strtotime($date));
    
        if(date('d',strtotime($date)) == '01'){
            $dia_ini = $date;
            $ultimo_domingo = $date;
        }else{
            $dia_ini = $anio_ini.'-'.$mes_ini.'-01';
            $ultimo_domingo = date('Y-m-d',strtotime('last sunday',strtotime($date)));
        }

        if (date('N',strtotime($date)) != '1' /*&& date('d',strtotime($date)) != '31' && date('m',strtotime($date)) != '12'*/) {     
            $prox_lunes   = date('Y-m-d',strtotime('next monday',strtotime($date)));
            if (date("W",strtotime($prox_lunes)) == '01') {
                $prox_lunes = $date;
            }
        }else{
           $prox_lunes = $date;     
        }
       
        $mes_restante = date("W",strtotime($prox_lunes))- date("W",strtotime($dia_ini));


        if(date('N',strtotime($dia_ini)) != '7'){
            if (date('N',strtotime($date)) == '1' || date('N',strtotime($date)) == '7') {
                $mes_restante = $mes_restante+1;
            }        
        }else{
            // if(date('d',strtotime($ultimo_domingo)) != '01'){
            if (!(date('N',strtotime($date)) == '1' || date('N',strtotime($date)) == '7')) {
                // $mes_restante = $mes_restante+1;
                 $mes_restante = $mes_restante-1; 
            }
        }
       
        return $mes_restante; 

    }

    public function aLetras($mes){
        switch ($mes) {
            case '01':
                return 'Enero';
                break;
            case '02':
                return 'Febrero';
                break;
            case '03':
                return 'Marzo';
                break;
            case '04':
                return 'Abril';
                break;
            case '05':
                return 'Mayo';
                break;
            case '06':
                return 'Junio';
                break;
            case '07':
                return 'Julio';
                break;
            case '08':
                return 'Agosto';
                break;
            case '09':
                return 'Setiembre';
                break;
            case '10':
                return 'Octubre';
                break;
            case '11':
                return 'Noviembre';
                break;
            case '12':
                return 'Diciembre';
                break;
        }
    }

}
