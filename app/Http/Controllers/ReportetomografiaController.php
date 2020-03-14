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
class ReportetomografiaController extends Controller
{
    protected $folderview      = 'app.reportetomografia';
    protected $tituloAdmin     = 'Reporte de Tomografia';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Modificar Referido Tomografia';
    protected $tituloEliminar  = 'Eliminar Concepto de Pago';
    protected $rutas           = array('create' => 'reportetomografia.create', 
            'edit'   => 'reportetomografia.edit', 
            'delete' => 'reporteconsulta.eliminar',
            'search' => 'reportetomografia.buscar',
            'index'  => 'reportetomografia.index',
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Reportetomografia';
        $paciente         = Libreria::getParam($request->input('paciente'));
        $tipopaciente     = Libreria::getParam($request->input('tipopaciente'));
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
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->whereNull('historia.deleted_at')
                            ->whereNotIn(DB::raw('case when mref.id>0 then mref.situacion else \'N\' end'),['A','U'])
                            ->where(function($query){
                                $query->where('s.tiposervicio_id','=',16)
                                      ->orWhere(function($q){
                                        $q->where('dmc.tiposervicio_id','=',16);
                                      });
                            });
        if($doctor!=""){
            $resultado = $resultado->where(DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres)'),'like','%'.$doctor.'%');
        }
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tipopaciente!=""){
            if($tipopaciente=="P"){//SOLO PARTICULAR
                $resultado = $resultado->where(function($query){
                    $query->where('plan.tipopago','=','Particular')
                          ->orWhere(function($q){
                            $q->where('plan.tipo','=','Institucion');
                          });
                });   
            }else{
                $resultado = $resultado->where('plan.tipo','=','Aseguradora');
            }
        }
        $resultado        = $resultado->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'asc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('movimiento.id','mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.mensajesunat');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Plan', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cant.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Referido', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Forma Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
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

    public function index()
    {
        $entidad          = 'Reportetomografia';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoPaciente          = array("" => "Todos", "P" => "Particular", "C" => "Convenio");
        $cboTipoServicio          = array("5" => "Rayos", "4" => "Ecografias", "16" => "Tomografias");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoPaciente', 'cboTipoServicio'));
    }

    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Reportetomografia';
        $conceptopago = null;
        $formData            = array('reportetomografia.store');
        $cboTipo          = array("Ingreso" => "Ingreso", "Egreso" => "Egreso");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('reportetomografia', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
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

    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $movimiento = Movimiento::find($id);
        $entidad             = 'Reportetomografia';
        $formData            = array('reportetomografia.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function update(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($request, $id){
            $movimiento        = Movimiento::find($id);
            $movimiento->doctor_id = $request->input('referido_id');
            $movimiento->mensajesunat = $request->input('comentario');
            $movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

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
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->whereNull('historia.deleted_at')
                            ->whereNull('dmc.deleted_at')
                            ->whereNotIn(DB::raw('case when mref.id>0 then mref.situacion else \'N\' end'),['A','U'])
                            ->where(function($query){
                                $query->where('s.tiposervicio_id','=',16)
                                      ->orWhere(function($q){
                                        $q->where('dmc.tiposervicio_id','=',16);
                                      });
                            });
        if($doctor!=""){
            $resultado = $resultado->where(DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres)'),'like','%'.$doctor.'%');
        }
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tipopaciente!=""){
            if($tipopaciente=="P"){//SOLO PARTICULAR
                $resultado = $resultado->where(function($query){
                    $query->where('plan.tipopago','=','Particular')
                          ->orWhere(function($q){
                            $q->where('plan.tipo','=','Institucion');
                          });
                });   
            }else{
                $resultado = $resultado->where('plan.tipo','=','Aseguradora');
            }
        }
        $resultado        = $resultado->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'asc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2',DB::raw('historia.numero as historia'),'dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'));
        $lista            = $resultado->get();

        Excel::create('ExcelReporte', function($excel) use($lista,$request) {
 
            $excel->sheet('Reporte', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Pago";
                $cabecera[] = "Pago2";
                $cabecera[] = "Plan";
                $cabecera[] = "Doctor";
                $cabecera[] = "Cant.";
                $cabecera[] = "Servicio";
                $cabecera[] = "Referido";
                $cabecera[] = "Forma Pago";
                $cabecera[] = "Situacion";
                $cabecera[] = "Usuario";
                $cabecera[] = "Historia";
                $array[] = $cabecera;
                $c=2;$d=3;$band=true;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->paciente2;
                    if($value->total>0)
                        $detalle[] = ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero;
                    else
                        $detalle[] = 'PREF. '.$value->numero2;
                    if($value->precio>0)
                        $detalle[] = number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio)*$value->cantidad,2,'.','');
                    else
                        $detalle[] = number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio2)*$value->cantidad,2,'.','');

                    if($value->servicio_id>0){
                        $servicio=$value->servicio;
                    }else{
                        $servicio=$value->servicio2;
                    }
                    if(strpos(strtoupper($servicio), "CON CONTRASTE") !== FALSE || strpos(strtoupper($servicio), "C/C") !== FALSE || strpos(strtoupper($servicio), "C/CONTRASTE") !== FALSE){
                        $detalle[] = number_format(260*$value->cantidad,2,'.','');
                    }else{
                        if(strpos(strtoupper($servicio), "TACH") !== FALSE || strpos(strtoupper($servicio), "TAC") !== FALSE){
                            $detalle[] = number_format(180*$value->cantidad,2,'.','');
                        }else{
                            $detalle[] = number_format(185*$value->cantidad,2,'.','');
                        }
                    }
                    $detalle[] = $value->plan;
                    $detalle[] = $value->medico;
                    $detalle[] = round($value->cantidad,0);
                    if($value->servicio_id>0)
                        $detalle[] = $value->servicio;
                    else
                        $detalle[] = $value->servicio2;
                    if($value->referido_id>0)
                        $detalle[] = $value->referido;
                    else
                        $detalle[] = "NO REFERIDO";
                    $detalle[] = $value->situacion=='P'?'-':($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta):'CONTADO');
                    $detalle[] = $value->situacion=='P'?'Pendiente':'Pagado';
                    $detalle[] = $value->responsable;
                    $detalle[] = $value->historia;
                    $array[] = $detalle;
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function excelReferido(Request $request){
        setlocale(LC_TIME, 'spanish');
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
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->whereNull('historia.deleted_at')
                            ->whereNull('dmc.deleted_at')
                            ->whereNotIn(DB::raw('case when mref.id>0 then mref.situacion else \'N\' end'),['A','U'])
                            ->where(function($query){
                                $query->where('s.tiposervicio_id','=',16)
                                      ->orWhere(function($q){
                                        $q->where('dmc.tiposervicio_id','=',16);
                                      });
                            });
        if($doctor!=""){
            $resultado = $resultado->where(DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres)'),'like','%'.$doctor.'%');
        }
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tipopaciente!=""){
            if($tipopaciente=="P"){//SOLO PARTICULAR
                $resultado = $resultado->where(function($query){
                    $query->where('plan.tipopago','=','Particular')
                          ->orWhere(function($q){
                            $q->where('plan.tipo','=','Institucion');
                          });
                });   
            }else{
                $resultado = $resultado->where('plan.tipo','=','Aseguradora');
            }
        }
        $resultado        = $resultado->orderBy(DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres)'), 'ASC')->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),DB::raw('case when movimiento.doctor_id is null then 0 else movimiento.doctor_id end as referido_id'),'historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2',DB::raw('historia.numero as historia'),'dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'));
        $lista            = $resultado->get();

        Excel::create('ExcelReporteReferido', function($excel) use($lista,$request) {
 
            $excel->sheet('ReporteReferido', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Importe";
                $cabecera[] = "Servicio";
                $cabecera[] = "Firma" ;               
                $array[] = $cabecera;
                $c=2;$d=3;$band=true;$referido_id="";$total=0;
                foreach ($lista as $key => $value){
                    if($value->referido_id=="") $value->referido_id=0;
                    if($referido_id!=$value->referido_id){
                        if($referido_id!=""){
                            $detalle = array();
                            $detalle[] = "";
                            $detalle[] = "TOTAL";
                            $detalle[] = number_format($total,2,'.','');
                            $detalle[] = "";
                            $detalle[] = "";
                            $array[] = $detalle;
                            $total=0;
                        }
                        $detalle = array();
                        $detalle[] = $value->referido==''?'NO REFERIDO':$value->referido;
                        $detalle[] = "";
                        $detalle[] = "";
                        $detalle[] = "";
                        $detalle[] = "";
                        $referido_id=$value->referido_id;
                        $array[] = $detalle;
                    }
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->paciente2;
                    if($value->precio>0){
                        $detalle[] = number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio)*$value->cantidad*0.08,2,'.','');
                        $total = $total + number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio)*$value->cantidad*0.08,2,'.','');
                    }else{
                        $detalle[] = number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio2)*$value->cantidad*0.08,2,'.','');
                        $total = $total + number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio2)*$value->cantidad*0.08,2,'.','');
                    }
                    if($value->servicio_id>0)
                        $detalle[] = $value->servicio;
                    else
                        $detalle[] = $value->servicio2;
                    $detalle[] = "";
                    $array[] = $detalle;
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "TOTAL";
                $detalle[] = number_format($total,2,'.','');
                $detalle[] = "";
                $detalle[] = "";
                $array[] = $detalle;
                $sheet->fromArray($array);
            });
        })->export('xls');
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
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->where('mref.persona_id','<>',69801)
                            ->whereNull('historia.deleted_at')
                            ->whereNull('dmc.deleted_at')
                            ->whereNotIn(DB::raw('case when mref.id>0 then mref.situacion else \'N\' end'),['A','U'])
                            ->where(function($query){
                                $query->where('s.tiposervicio_id','=',16)
                                      ->orWhere(function($q){
                                        $q->where('dmc.tiposervicio_id','=',16);
                                      });
                            });
        if($doctor!=""){
            $resultado = $resultado->where(DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres)'),'like','%'.$doctor.'%');
        }
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tipopaciente!=""){
            if($tipopaciente=="P"){//SOLO PARTICULAR
                $resultado = $resultado->where(function($query){
                    $query->where('plan.tipopago','=','Particular')
                          ->orWhere(function($q){
                            $q->where('plan.tipo','=','Institucion');
                          });
                });   
            }else{
                $resultado = $resultado->where('plan.tipo','=','Aseguradora');
            }
        }

        // dd($resultado);

        $resultado        = $resultado->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'asc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2',DB::raw('historia.numero as historia'),'dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'mref.tipodocumento_id','movimiento.mensajesunat','movimiento.id as movimiento_id');
        $lista            = $resultado->orderBy('movimiento.fecha','asc')->get();

        // dd($lista);
        $pdf = new TCPDF();
        $pdf::SetTitle('Reporte de Tomografias ');
        if (count($lista) > 0) {            
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Reporte de Tomografias del ".date("d/m/Y",strtotime($fechainicial))." al ".date("d/m/Y",strtotime($fechafinal))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(14,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(55,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(10,6,utf8_decode("TOTAL"),1,0,'C');
            $pdf::Cell(30,6,utf8_decode("PLAN"),1,0,'C');
            $pdf::Cell(25,6,utf8_decode("MEDICO"),1,0,'C');
            //$pdf::Cell(10,6,utf8_decode("CANT."),1,0,'C');
            $pdf::Cell(55,6,utf8_decode("SERVICIO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("HISTORIA"),1,0,'C');
            $pdf::Cell(60,6,utf8_decode("COMENTARIO"),1,0,'C');
            $pdf::Ln();
            $fecha="";$total=0;$totalgeneral=0;$idmedico=0;$c=0;$d=0;
            foreach ($lista as $key => $value){
                // if($value->fecha == '2019-09-09'){
                //     echo json_encode($value);
                // }
               
                if($fecha!=date("d/m/Y",strtotime($value->fecha))){
                    $pdf::SetFont('helvetica','B',7);
                    if($fecha!=""){
                        $pdf::SetFont('helvetica','B',7);
                        $pdf::Cell(84,6,"",0,0,'R');
                        $pdf::Cell(10,6,$c,1,0,'C');
                        $totalgeneral = $totalgeneral + $total;
                        $total=0;
                        $d = $d + $c;
                        $c=0;
                        $pdf::Ln();
                    }
                    $fecha=date("d/m/Y",strtotime($value->fecha));
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(279,6,($fecha),1,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',6.5);
                $pdf::Cell(14,6,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                $pdf::Cell(55,6,substr($value->paciente2,0,32),1,0,'L');
                if($value->total>0){
                    $pdf::Cell(15,6,($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero,1,0,'L');
                }else{
                    $pdf::Cell(15,6,'PREF. '.$value->numero2,1,0,'L');
                }
                // dd($value);
                if($value->movimiento_id==288615){
                    //dd($value);
                    $value->precioconvenio = 140;
                }
//                 if($value->precio>0 /*&& $value->precio <= $value->total*/){
//                     // $pdf::Cell(10,6,number_format($value->total,2,'.',''),1,0,'R');
//                     // $total = $total + number_format($value->total,2,'.','');
//                     // number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio)*$value->cantidad,2,'.','');

//                     //if($value->movimiento_id==288615){dd(1);}
//                     $pdf::Cell(10,6,number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio)*$value->cantidad,2,'.',''),1,0,'R');
//                     $total = $total + number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio)*$value->cantidad,2,'.','');
//                 }else{
// /*                    if($value->precio > $value->total){
//                          $pdf::Cell(10,6,number_format($value->total,2,'.',''),1,0,'R');
//                          $total = $total + number_format($value->total,2,'.','');
                   
//                     }else{*/
//                     //if($value->movimiento_id==288615){dd(2);}
//                         $pdf::Cell(10,6,number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio2)*$value->cantidad,2,'.',''),1,0,'R');
//                         $total = $total + number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio2)*$value->cantidad,2,'.','');    
//                   //  }
                    
//                 }
                $pdf::Cell(10,6,number_format($value->pagohospital,2,'.',''),1,0,'R');
                         $total = $total + number_format($value->pagohospital,2,'.','');


                $pdf::Cell(30,6,substr($value->plan,0,20),1,0,'L');
                $pdf::Cell(25,6,substr($value->medico,0,15),1,0,'L');
                //$pdf::Cell(10,6,round($value->cantidad,0),1,0,'R');
                if($value->servicio_id>0){
                    $pdf::Cell(55,6,substr($value->servicio,0,38),1,0,'L');
                }else{
                    $pdf::Cell(55,6,substr($value->servicio2,0,38),1,0,'L');
                }
                $pdf::Cell(15,6,($value->historia),1,0,'R');
                $pdf::Cell(60,6,($value->mensajesunat),1,0,'R');
                $pdf::Ln();         
                $c=$c+1;       
            }
            // exit();

            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(84,6,"",0,0,'R');
            $pdf::Cell(10,6,$c,1,0,'C');
            $d = $d + $c;
            $pdf::Cell(84,6,"ITEMS :",0,0,'R');
            $pdf::Cell(10,6,$d,0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(100,6,("TOTAL :"),0,0,'R');
            $totalgeneral = $totalgeneral + $total;
            $pdf::Cell(20,6,"S/. ".number_format($totalgeneral,2,'.',''),0,0,'R');
            $pdf::Ln();
            $pdf::Cell(100,6,("HOSPITAL 15%:"),0,0,'R');
            $pdf::Cell(20,6,"S/. ".number_format($totalgeneral*0.15,2,'.',''),0,0,'R');
            $pdf::Cell(40,6,("MEDICOS 8%:"),0,0,'R');
            $pdf::Cell(20,6,"S/. ".number_format($totalgeneral*0.08,2,'.',''),0,0,'R');
            $pdf::Ln();
            $pdf::Cell(100,6,("RESOCENTRO 85%:"),0,0,'R');
            $pdf::Cell(20,6,"S/. ".number_format($totalgeneral*0.85,2,'.',''),0,0,'R');
        }
        $pdf::Output('ReporteSocio.pdf');
    }

    public function pdf2(Request $request){
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
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->whereNull('historia.deleted_at')
                            ->whereNull('dmc.deleted_at')
                            ->whereNotIn(DB::raw('case when mref.id>0 then mref.situacion else \'N\' end'),['A','U'])
                            ->where(function($query){
                                $query->where('s.tiposervicio_id','=',16)
                                      ->orWhere(function($q){
                                        $q->where('dmc.tiposervicio_id','=',16);
                                      });
                            });
        if($doctor!=""){
            $resultado = $resultado->where(DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres)'),'like','%'.$doctor.'%');
        }
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tipopaciente!=""){
            if($tipopaciente=="P"){//SOLO PARTICULAR
                $resultado = $resultado->where(function($query){
                    $query->where('plan.tipopago','=','Particular')
                          ->orWhere(function($q){
                            $q->where('plan.tipo','=','Institucion');
                          });
                });   
            }else{
                $resultado = $resultado->where('plan.tipo','=','Aseguradora');
            }
        }
        $resultado        = $resultado->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2',DB::raw('historia.numero as historia'),'dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.mensajesunat');
        $lista            = $resultado->orderBy('movimiento.fecha','asc')->get();
        $pdf = new TCPDF();
        $pdf::SetTitle('Reporte de Tomografias ');
        if (count($lista) > 0) {            
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Reporte de Tomografias de Convenio del ".date("d/m/Y",strtotime($fechainicial))." al ".date("d/m/Y",strtotime($fechafinal))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(14,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(50,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(10,6,utf8_decode("TOTAL"),1,0,'C');
            $pdf::Cell(40,6,utf8_decode("PLAN"),1,0,'C');
            $pdf::Cell(25,6,utf8_decode("MEDICO"),1,0,'C');
            //$pdf::Cell(10,6,utf8_decode("CANT."),1,0,'C');
            $pdf::Cell(50,6,utf8_decode("SERVICIO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("HISTORIA"),1,0,'C');
            $pdf::Cell(60,6,utf8_decode("COMENTARIO"),1,0,'C');
            $pdf::Ln();
            $fecha="";$total=0;$totalgeneral=0;$idmedico=0;$c=0;$d=0;
            foreach ($lista as $key => $value){
                if($fecha!=date("d/m/Y",strtotime($value->fecha))){
                    $pdf::SetFont('helvetica','B',7);
                    if($fecha!=""){
                        $pdf::SetFont('helvetica','B',7);
                        $pdf::Cell(79,6,"",0,0,'R');
                        $pdf::Cell(10,6,$c,1,0,'C');
                        $totalgeneral = $totalgeneral + $total;
                        $total=0;
                        $d = $d + $c;
                        $c=0;
                        $pdf::Ln();
                    }
                    $fecha=date("d/m/Y",strtotime($value->fecha));
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(279,6,($fecha),1,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',6.5);
                $pdf::Cell(14,6,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                $pdf::Cell(50,6,substr($value->paciente2,0,30),1,0,'L');
                if($value->total>0){
                    $pdf::Cell(15,6,($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero,1,0,'L');
                }else{
                    $pdf::Cell(15,6,'PREF. '.$value->numero2,1,0,'L');
                }
                if($value->servicio_id>0){
                    $servicio=$value->servicio;
                }else{
                    $servicio=$value->servicio2;
                }
                if(strpos(strtoupper($servicio), "CON CONTRASTE") !== FALSE || strpos(strtoupper($servicio), "C/C") !== FALSE || strpos(strtoupper($servicio), "C/CONTRASTE") !== FALSE){
                    $pdf::Cell(10,6,number_format(260*$value->cantidad,2,'.',''),1,0,'R');
                    $total = $total + number_format((260)*$value->cantidad,2,'.','');
                }else{
                    if(strpos(strtoupper($servicio), "TACH") !== FALSE || strpos(strtoupper($servicio), "TAC") !== FALSE){
                        $pdf::Cell(10,6,number_format(180*$value->cantidad,2,'.',''),1,0,'R');
                        $total = $total + number_format((180)*$value->cantidad,2,'.','');
                    }else{
                        $pdf::Cell(10,6,number_format(185*$value->cantidad,2,'.',''),1,0,'R');
                        $total = $total + number_format(185*$value->cantidad,2,'.','');
                    }
                }
                $pdf::Cell(40,6,substr($value->plan,0,25),1,0,'L');
                $pdf::Cell(25,6,substr($value->medico,0,15),1,0,'L');
                //$pdf::Cell(10,6,round($value->cantidad,0),1,0,'R');
                if($value->servicio_id>0){
                    $pdf::Cell(50,6,substr($value->servicio,0,35),1,0,'L');
                }else{
                    $pdf::Cell(50,6,substr($value->servicio2,0,35),1,0,'L');
                }
                $pdf::Cell(15,6,($value->historia),1,0,'R');
                $pdf::Cell(60,6,($value->mensajesunat),1,0,'R');
                $pdf::Ln();         
                $c=$c+1;       
            }
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(79,6,"",0,0,'R');
            $pdf::Cell(10,6,$c,1,0,'C');
            $d = $d + $c;
            $pdf::Cell(79,6,"ITEMS :",0,0,'R');
            $pdf::Cell(10,6,$d,0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(100,6,("TOTAL :"),0,0,'R');
            $totalgeneral = $totalgeneral + $total;
            $pdf::Cell(20,6,"S/. ".number_format($totalgeneral,2,'.',''),0,0,'R');
            $pdf::Ln();
            if($request->input('tipopaciente')=="P"){
                $pdf::Cell(100,6,("HOSPITAL 15%:"),0,0,'R');
                $pdf::Cell(20,6,"S/. ".number_format($totalgeneral*0.15,2,'.',''),0,0,'R');
                $pdf::Cell(40,6,("MEDICOS 8%:"),0,0,'R');
                $pdf::Cell(20,6,"S/. ".number_format($totalgeneral*0.08,2,'.',''),0,0,'R');
                $pdf::Ln();
                $pdf::Cell(100,6,("RESOCENTRO 85%:"),0,0,'R');
                $pdf::Cell(20,6,"S/. ".number_format($totalgeneral*0.85,2,'.',''),0,0,'R');
            }
        }
        $pdf::Output('ReporteTomografia2.pdf');
    }

    public function pdf3(Request $request){
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
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('mref.persona_id','!=',69801)
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->whereNull('historia.deleted_at')
                            ->whereNotIn(DB::raw('case when mref.id>0 then mref.situacion else \'N\' end'),['A','U'])
                            ->where(function($query){
                                $query->where('s.tiposervicio_id','=',16)
                                      ->orWhere(function($q){
                                        $q->where('dmc.tiposervicio_id','=',16);
                                      });
                            });
        if($doctor!=""){
            $resultado = $resultado->where(DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres)'),'like','%'.$doctor.'%');
        }
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tipopaciente!=""){
            if($tipopaciente=="P"){//SOLO PARTICULAR
                $resultado = $resultado->where(function($query){
                    $query->where('plan.tipopago','=','Particular')
                          ->orWhere(function($q){
                            $q->where('plan.tipo','=','Institucion');
                          });
                });   
            }else{
                $resultado = $resultado->where('plan.tipo','=','Aseguradora');
            }
        }
        $resultado        = $resultado->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2',DB::raw('historia.numero as historia'),'dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'));
        $lista            = $resultado->orderBy('referido.apellidopaterno','asc')->orderBy('referido.apellidomaterno')->orderBy('referido.nombres')->orderBy('movimiento.fecha','asc')->get();
        $pdf = new TCPDF();
        $pdf::SetTitle('Reporte de Tomografias ');
        if (count($lista) > 0) {            
            $pdf::AddPage();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Reporte de Tomografias del ".date("d/m/Y",strtotime($fechainicial))." al ".date("d/m/Y",strtotime($fechafinal))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(14,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(50,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(10,6,utf8_decode("TOTAL"),1,0,'C');
            //$pdf::Cell(10,6,utf8_decode("CANT."),1,0,'C');
            $pdf::Cell(50,6,utf8_decode("SERVICIO"),1,0,'C');
            $pdf::Cell(50,6,utf8_decode("FIRMA"),1,0,'C');
            $pdf::Ln();
            $fecha="";$total=0;$totalgeneral=0;$medico='';$c=0;$d=0;
            foreach ($lista as $key => $value){
                if($medico!=$value->referido){
                    $pdf::SetFont('helvetica','B',7);
                    if($medico!=""){
                        $pdf::SetFont('helvetica','B',7);
                        $pdf::Cell(64,6,'',0,0,'L');
                        $pdf::Cell(10,6,number_format($total,2,'.',''),1,0,'R');
                        $pdf::Cell(50,6,'',0,0,'L');
                        $pdf::Cell(50,6,'','BR',0,'L');
                        $totalgeneral = $totalgeneral + $total;
                        $total=0;
                        $pdf::Ln();
                    }
                    $medico=$value->referido;
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(174,6,$value->referido,'LR',0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',6.5);
                $pdf::Cell(14,6,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                $pdf::Cell(50,6,substr($value->paciente2,0,25),1,0,'L');
                if($value->precio>0){
                    $pdf::Cell(10,6,number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio)*$value->cantidad*0.08,2,'.',''),1,0,'R');
                    $total = $total + number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio)*$value->cantidad*0.08,2,'.','');
                }else{
                    $pdf::Cell(10,6,number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio2)*$value->cantidad*0.08,2,'.',''),1,0,'R');
                    $total = $total + number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio2)*$value->cantidad*0.08,2,'.','');
                }
                if($value->servicio_id>0){
                    $pdf::Cell(50,6,substr($value->servicio,0,30),1,0,'L');
                }else{
                    $pdf::Cell(50,6,substr($value->servicio2,0,30),1,0,'L');
                }
                $pdf::Cell(50,6,'','LR',0,'L');
                $pdf::Ln();         
            }
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(64,6,'',0,0,'L');
            $pdf::Cell(10,6,number_format($total,2,'.',''),1,0,'R');
            $pdf::Cell(50,6,'',0,0,'L');
            $pdf::Cell(50,6,'','BR',0,'L');
            $totalgeneral = $totalgeneral + $total;
            $pdf::Ln();         
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(100,6,("TOTAL :"),0,0,'R');
            $pdf::Cell(20,6,"S/. ".number_format($totalgeneral,2,'.',''),0,0,'R');
        }
        $pdf::Output('ReporteSocio.pdf');
    }

    public function pdfOncorad(Request $request){
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
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->where('mref.persona_id','=',69801)
                            ->whereNull('historia.deleted_at')
                            ->whereNull('dmc.deleted_at')
                            ->whereNotIn(DB::raw('case when mref.id>0 then mref.situacion else \'N\' end'),['A','U'])
                            ->where(function($query){
                                $query->where('s.tiposervicio_id','=',16)
                                      ->orWhere(function($q){
                                        $q->where('dmc.tiposervicio_id','=',16);
                                      });
                            });
        if($doctor!=""){
            $resultado = $resultado->where(DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres)'),'like','%'.$doctor.'%');
        }
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tipopaciente!=""){
            if($tipopaciente=="P"){//SOLO PARTICULAR
                $resultado = $resultado->where(function($query){
                    $query->where('plan.tipopago','=','Particular')
                          ->orWhere(function($q){
                            $q->where('plan.tipo','=','Institucion');
                          });
                });   
            }else{
                $resultado = $resultado->where('plan.tipo','=','Aseguradora');
            }
        }
        $resultado        = $resultado->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'asc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2',DB::raw('historia.numero as historia'),'dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'mref.tipodocumento_id','movimiento.mensajesunat');
        $lista            = $resultado->orderBy('movimiento.fecha','asc')->get();
        $pdf = new TCPDF();
        $pdf::SetTitle('Reporte de Tomografias Oncorad ');
        if (count($lista) > 0) {            
            $pdf::AddPage('P');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Reporte de Tomografias Oncorad del ".date("d/m/Y",strtotime($fechainicial))." al ".date("d/m/Y",strtotime($fechafinal))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(14,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(50,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(10,6,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(55,6,utf8_decode("SERVICIO"),1,0,'C');
            $pdf::Cell(13,6,utf8_decode("HISTORIA"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("ONCORAD"),1,0,'C');
            $pdf::Cell(13,6,utf8_decode("EMETAC"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("A PAGAR EMET"),1,0,'C');
            $pdf::Ln();
            $fecha="";$total=0;$total2=0;$totalgeneral=0;$totalemetac=0;$idmedico=0;$c=0;$d=0;
            foreach ($lista as $key => $value){
                if($fecha!=date("d/m/Y",strtotime($value->fecha))){
                    $pdf::SetFont('helvetica','B',7);
                    if($fecha!=""){
                        $pdf::SetFont('helvetica','B',7);
                        $pdf::Cell(64,6,"",0,0,'R');
                        $pdf::Cell(10,6,$c,1,0,'C');
                        $d = $d + $c;
                        $c=0;
                        $pdf::Ln();
                    }
                    $fecha=date("d/m/Y",strtotime($value->fecha));
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(0,6,($fecha),1,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',6.5);
                $pdf::Cell(14,6,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                $pdf::Cell(50,6,substr($value->paciente2,0,32),1,0,'L');
                if($value->total>0){
                    $pdf::Cell(10,6,($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero,1,0,'L');
                }else{
                    $pdf::Cell(10,6,'PREF. '.$value->numero2,1,0,'L');
                }
                
                $cc = 0;
                //$pdf::Cell(10,6,round($value->cantidad,0),1,0,'R');
                if($value->servicio_id>0){
                    $pdf::Cell(55,6,substr($value->servicio,0,38),1,0,'L');
                    if (strrpos($value->servicio, 'CON CONTRASTE') !== FALSE || strrpos($value->servicio, 'C/CONTRASTE') !== FALSE) {
                        $cc = 1;
                    }
                }else{
                    $pdf::Cell(65,6,substr($value->servicio2,0,38),1,0,'L');
                    if (strrpos($value->servicio2, 'CON CONTRASTE') !== FALSE || strrpos($value->servicio2, 'C/CONTRASTE') !== FALSE) {
                        $cc = 1;
                    }
                }
                $pdf::Cell(13,6,($value->historia),1,0,'R');
                
                //ONCORAD
                if ($cc==1) {
                    $pdf::Cell(15,6,"S/.346,00",1,0,'R');
                    $total=346;
                } else {
                    $pdf::Cell(15,6,"S/.276,00",1,0,'R');
                    $total=276;
                }
                
                //EMETAC
                if ($cc==1) {
                    $pdf::Cell(13,6,"S/.300,00",1,0,'R');
                } else {
                    $pdf::Cell(13,6,"S/.250,00",1,0,'R');
                }

                //A PAGAR
                if ($cc==1) {
                    $pdf::Cell(20,6,"S/.255,00",1,0,'R');
                    $total2=255;
                } else {
                    $pdf::Cell(20,6,"S/.212,50",1,0,'R');
                    $total2=212.5;
                }
                $totalgeneral += $total;
                $totalemetac += $total2;
                $total=0;$total2=0;
                $pdf::Ln();         
                $c=$c+1;       
            }
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(64,6,"",0,0,'R');
            $pdf::Cell(10,6,$c,1,0,'C');
            $d = $d + $c;
            $pdf::Cell(84,6,"ITEMS :",0,0,'R');
            $pdf::Cell(10,6,$d,0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(100,6,("TOTAL :"),0,0,'R');
            $totalgeneral = $totalgeneral + $total;
            $pdf::Cell(20,6,"S/. ".number_format($totalgeneral,2,'.',''),0,0,'R');
            
            $pdf::Ln();
            $pdf::Cell(100,6,("A PAGAR EMETAC 85%:"),0,0,'R');
            $pdf::Cell(20,6,"S/. ".number_format($totalemetac,2,'.',''),0,0,'R');

            $pdf::Ln();
            $pdf::Cell(100,6,("HOSPITAL:"),0,0,'R');
            $pdf::Cell(20,6,"S/. ".number_format($totalgeneral-$totalemetac,2,'.',''),0,0,'R');;
        }
        $pdf::Output('ReporteSocio.pdf');
    }
}
