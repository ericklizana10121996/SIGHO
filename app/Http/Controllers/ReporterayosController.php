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
class ReporterayosController extends Controller
{
    protected $folderview      = 'app.reporterayos';
    protected $tituloAdmin     = 'Reporte de Rayos, Ecografias y Tomografias';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Modificar Concepto de Pago';
    protected $tituloEliminar  = 'Eliminar Concepto de Pago';
    protected $rutas           = array('create' => 'reporterayos.create', 
            'edit'   => 'reporteconsulta.edit', 
            'delete' => 'reporteconsulta.eliminar',
            'search' => 'reporterayos.buscar',
            'index'  => 'reporterayos.index',
            'pagar'   => 'reporterayos.pago', 
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Reporterayos';
        $paciente         = Libreria::getParam($request->input('paciente'));
        $tipopaciente     = Libreria::getParam($request->input('tipopaciente'));
        $tiposervicio_id  = Libreria::getParam($request->input('tiposervicio_id'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->whereNotIn('movimiento.situacion',['U','A']);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tiposervicio_id!=""){
            $tiposervicio_id = explode(",",$tiposervicio_id);
            $resultado = $resultado->whereIn('s.tiposervicio_id',$tiposervicio_id);   
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
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.marcado','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','movimiento.tipo_poliza','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'dmc.precioconvenio','dmc.descargado','dmc.fechadescargo');
        $lista            = $resultado->get();
        //dd($lista);
        $cabecera         = array();
        $cabecera[]       = array('valor' => '', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago Hosp.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Plan', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Poliza', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cant.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Referido', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Forma Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $ruta             = $this->rutas;
        $user             = Auth::user();
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'user', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    public function index()
    {
        $entidad          = 'Reporterayos';
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
        $entidad             = 'Reporterayos';
        $conceptopago = null;
        $formData            = array('reporterayos.store');
        $cboTipo          = array("Ingreso" => "Ingreso", "Egreso" => "Egreso");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('reporterayos', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
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

    public function pago(Request $request,$listar,$id)
    {
        $listar              = Libreria::getParam($listar, 'NO');
        $id2 = explode(',',$id);
        //print_r($id2);
        $detalle1             = Detallemovcaja::whereIn('id',$id2)->get();
        $detalle="";
        $entidad             = 'Reporterayos';
        $formData            = array('reporterayos.pagar', $id);
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
                $detalle->fechadescargo = date("Y-m-d");
                $detalle->usuariodescargo_id = $user->person_id;
                $detalle->descargado = 'E';
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
            $movimiento->conceptopago_id=35;//ECOGRAFIAS PARTICULARES
            $movimiento->comentario='Pago con '.$request->input('recibo');
            //$movimiento->caja_id=3;//CAJA CONVENIO
            $movimiento->totalpagado=$request->input('pago');
            $movimiento->situacion='N';
            $movimiento->listapago=$request->input('id');
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
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->whereNotIn('dmc.situacion',['U','A'])
                            ->whereNotIn('movimiento.situacion',['U','A']);
                            
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tiposervicio_id!=""){
            $tiposervicio_id = explode(",",$tiposervicio_id);
            $resultado = $resultado->whereIn('s.tiposervicio_id',$tiposervicio_id);   
        }
        if($tipopaciente!=""){
            if($tipopaciente=="P"){//SOLO PARTICULAR
                // $resultado = $resultado->where(function($query){
                //     $query->where('plan.tipopago','=','Particular')
                //           ->orWhere(function($q){
                //             $q->where('plan.tipo','=','Institucion');
                //           });
                // });
                $resultado = $resultado->where('plan.id','=',6);
            }else{
                //$resultado = $resultado->where('plan.tipo','=','Aseguradora');
                $resultado = $resultado->where('plan.id','!=',6);
            }
        }
        $resultado        = $resultado->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.tipo_poliza','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.precio as sprecio','s.nombre as servicio','dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'dmc.precioconvenio','dmc.descargado','dmc.fechadescargo','movimiento.plan_id');
        $lista            = $resultado->get();
        //dd($lista);

        Excel::create('ExcelReporte', function($excel) use($lista,$request) {
 
            $excel->sheet('Reporte', function($sheet) use($lista,$request) {
                $tiposervicio_id  = Libreria::getParam($request->input('tiposervicio_id'));
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Recibo Doc";
                $cabecera[] = "Pago Doc.";
                $cabecera[] = "Pago no IGV";
                $cabecera[] = "Total";
                $cabecera[] = "Plan";
                $cabecera[] = "Poliza";
                $cabecera[] = "Doctor";
                $cabecera[] = "Cant.";
                $cabecera[] = "Servicio";
                $cabecera[] = "Referido" ;               
                $cabecera[] = "Forma Pago";
                $cabecera[] = "Situacion";
                $cabecera[] = "Usuario";
                $cabecera[] = "Historia";
                $array[] = $cabecera;
                $c=2;$d=3;$band=true;$stotal=0;$final=3;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->paciente2;
                    if($value->total>0){
                        $detalle[] = ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero;
                        $detalle[] = $value->recibo;
                        if ($tiposervicio_id==4 && $value->plan_id != 6) { //ECO
                            $detalle[] = ($value->sprecio*$value->cantidad)/1.18*0.6;
                            $detalle[] = ($value->sprecio*$value->cantidad)/1.18;
                            $detalle[] = $value->sprecio*$value->cantidad;
                            $valorsuma = ($value->sprecio);
                        } else {
                            $detalle[] = $value->pagodoctor*$value->cantidad;
                            $detalle[] = ($value->precioconvenio*$value->cantidad)/1.18;
                            $detalle[] = $value->precioconvenio*$value->cantidad;
                            $valorsuma = ($value->precioconvenio*$value->cantidad)/1.18;
                        }
                        //$detalle[] = number_format($value->pagohospital*$value->cantidad,2,'.','');
                    }else{
                        $detalle[] = 'PREF. '.$value->numero2;
                        $detalle[] = $value->recibo;
                        $detalle[] = '0,00';
                        $detalle[] = ($value->precioconvenio*$value->cantidad)/1.18;
                        $detalle[] = $value->precioconvenio*$value->cantidad;
                        $valorsuma = ($value->precioconvenio*$value->cantidad)/1.18;
                    }
                    $stotal+=$valorsuma;
                    $final++;
                    $detalle[] = $value->plan;
                    $detalle[] = empty($value->tipo_poliza) === true?'No Especificado':$value->tipo_poliza;
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
                    if($value->total>0){
                        $detalle[] = $value->situacion=='P'?'-':($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta):'CONTADO');
                        $detalle[] = $value->situacion=='P'?'Pendiente':'Pagado';
                    }else{
                        $detalle[] = 'CREDITO';
                        $detalle[] = 'Pendiente';
                    }
                    $detalle[] = $value->responsable;
                    $detalle[] = $value->historia;
                    $array[] = $detalle;
                }

                $final--;

                $sheet->getStyle('F3:F'.$final)->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle('E3:E'.$final)->getNumberFormat()->setFormatCode('0.00');

                $detalle = array();
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = 'TOTAL';
                //$detalle[] = $stotal;
                $detalle[] = '=SUM(E3:E'.$final.')';
                $array[] = $detalle;
                $final++;
                $detalle = array();
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = '60%';
                //$detalle[] = $stotal*0.6;
                $detalle[] = '=(E'.$final.'*60)/100';
                $array[] = $detalle;

                $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function excel2(Request $request){
        $hoy = date("Y-m-d");
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
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            //->where('dmc.updated_at','like',$hoy."%")
                            ->where('dmc.marcado','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->whereNotIn('movimiento.situacion',['U','A']);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tiposervicio_id!=""){
            $tiposervicio_id = explode(",",$tiposervicio_id);
            $resultado = $resultado->whereIn('s.tiposervicio_id',$tiposervicio_id);   
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
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'dmc.precioconvenio','dmc.descargado','dmc.fechadescargo');
        $lista            = $resultado->get();

        Excel::create('RayosMarcados', function($excel) use($lista,$request) {
 
            $excel->sheet('Reporte', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Pago Doc.";
                $cabecera[] = "Pago Hosp.";
                $cabecera[] = "Plan";
                $cabecera[] = "Doctor";
                $cabecera[] = "Cant.";
                $cabecera[] = "Servicio";
                $cabecera[] = "Referido" ;               
                $cabecera[] = "Forma Pago";
                $cabecera[] = "Situacion";
                $cabecera[] = "Usuario";
                $cabecera[] = "Historia";
                $array[] = $cabecera;
                $c=2;$d=3;$band=true;$stotal=0;$final=3;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->paciente2;
                    if($value->total>0){
                        $detalle[] = ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero;
                        $detalle[] = $value->pagodoctor*$value->cantidad;
                        $detalle[] = $value->precioconvenio*$value->cantidad/1.18;
                        $valorsuma = $value->precioconvenio*$value->cantidad/1.18;
                        //$detalle[] = number_format($value->pagohospital*$value->cantidad,2,'.','');
                    }else{
                        $detalle[] = 'PREF. '.$value->numero2;
                        $detalle[] = '0,00';
                        $detalle[] = $value->precioconvenio*$value->cantidad/1.18;
                        $valorsuma = $value->precioconvenio*$value->cantidad/1.18;
                    }
                    $stotal+=$valorsuma;
                    $final++;
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
                    if($value->total>0){
                        $detalle[] = $value->situacion=='P'?'-':($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta):'CONTADO');
                        $detalle[] = $value->situacion=='P'?'Pendiente':'Pagado';
                    }else{
                        $detalle[] = 'CREDITO';
                        $detalle[] = 'Pendiente';
                    }
                    $detalle[] = $value->responsable;
                    $detalle[] = $value->historia;
                    $array[] = $detalle;
                }

                $final--;

                $sheet->getStyle('D3:D'.$final)->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle('E3:E'.$final)->getNumberFormat()->setFormatCode('0.00');

                $detalle = array();
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = 'TOTAL';
                //$detalle[] = $stotal;
                $detalle[] = '=SUM(E3:E'.$final.')';
                $array[] = $detalle;
                $detalle = array();
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = 'Pago Med';
                //$detalle[] = $stotal*0.6;
                $detalle[] = '=SUM(D3:D'.$final.')';
                $final++;
                $array[] = $detalle;

                $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function excel3(Request $request){
        $hoy = date("Y-m-d");
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
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('dmc.marcado','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->where('movimiento.situacion','<>','U');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($tiposervicio_id!=""){
            $tiposervicio_id = explode(",",$tiposervicio_id);
            $resultado = $resultado->whereIn('s.tiposervicio_id',$tiposervicio_id);   
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
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.tipo_poliza','movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'dmc.precioconvenio','dmc.descargado','dmc.fechadescargo');
        $lista            = $resultado->get();

        Excel::create('EcosMarcados', function($excel) use($lista,$request) {
 
            $excel->sheet('Reporte', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Pago Hosp.";
                $cabecera[] = "Precio sin IGV.";
                $cabecera[] = "Pago Doc. 60%";
                $cabecera[] = "Plan";
                $cabecera[] = "Poliza";
                $cabecera[] = "Doctor";
                $cabecera[] = "Cant.";
                $cabecera[] = "Servicio";
                $array[] = $cabecera;
                $c=2;$d=3;$band=true;$stotal=0;$final=3;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->paciente2;
                    if($value->total>0){
                        $detalle[] = ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero;
                        //$detalle[] = number_format($value->pagohospital*$value->cantidad,2,'.','');
                    }else{
                        $detalle[] = 'PREF. '.$value->numero2;
                    }

                    $detalle[] = $value->precioconvenio*$value->cantidad;
                    $detalle[] = $value->precioconvenio*$value->cantidad/1.18;
                    $detalle[] = ($value->precioconvenio*$value->cantidad/1.18)*0.6;
                    $valorsuma = ($value->precioconvenio*$value->cantidad/1.18)*0.6;

                    $stotal+=$valorsuma;
                    $final++;
                    $detalle[] = $value->plan;
                    $detalle[] = empty($value->tipo_poliza) === true?'No Especificado':$value->tipo_poliza;
                    $detalle[] = $value->medico;
                    $detalle[] = round($value->cantidad,0);
                    if($value->servicio_id>0)
                        $detalle[] = $value->servicio;
                    else
                        $detalle[] = $value->servicio2;
                    $array[] = $detalle;
                }

                $final--;

                $sheet->getStyle('D3:D'.$final)->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle('E3:E'.$final)->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle('F3:F'.$final)->getNumberFormat()->setFormatCode('0.00');

                $detalle = array();
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = '';
                $detalle[] = 'Pago Med';
                //$detalle[] = $stotal*0.6;
                $detalle[] = number_format($stotal,2,".","");
                $final++;
                $array[] = $detalle;

                $sheet->fromArray($array);
            });
        })->export('xls');
    }
}
