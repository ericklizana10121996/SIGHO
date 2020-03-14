<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Conceptopago;
use App\Movimiento;
use App\Detallemovcaja;
use App\Pagomedico;
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
class ReportepagofacturacionController extends Controller
{
    protected $folderview      = 'app.reportepagofacturacion';
    protected $tituloAdmin     = 'Reporte de Pago a Doctores';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Modificar Concepto de Pago';
    protected $tituloEliminar  = 'Eliminar Concepto de Pago';
    protected $rutas           = array('create' => 'reportepagofacturacion.create', 
            'edit'   => 'reporteconsulta.edit', 
            //'delete' => 'reporteconsulta.eliminar'
            'delete' => 'reportepagofacturacion.eliminar',
            'search' => 'reportepagofacturacion.buscar',
            'index'  => 'reportepagofacturacion.index',
            'nuevopago'  => 'reportepagofacturacion.nuevopago',
            'nuevoreporte'  => 'reportepagofacturacion.nuevoreporte',
            'generarreporteexcel'  => 'reportepagofacturacion.generarreporteexcel',
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
        $entidad          = 'Reportepagofacturacion';
        $paciente         = Libreria::getParam($request->input('paciente'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $tipopaciente     = Libreria::getParam($request->input('tipopaciente'));
        $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $nroope           = Libreria::getParam($request->input('nroope'));
        $nrodoc           = Libreria::getParam($request->input('nrodoc'));
        $situacion        = Libreria::getParam($request->input('situacion'));
        if($situacion!="H"){
            $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                                ->join('person as medico','medico.id','=','dmc.persona_id')
                                ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('plan','plan.id','=','movimiento.plan_id')
                                ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                                ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                                ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                                ->where('movimiento.tipomovimiento_id','=',9)
                                /*->whereNotIn('medico.id',[56595,69954,297,294,56655])
                                ->whereNotIn('s.tiposervicio_id',[1,2])
                                ->where('dmc.descripcion',"not like","%CONS%")
                                ->where(function($query){
                                    $query->whereNotIn('s.tiposervicio_id',[4])
                                            ->orWhere('movimiento.fecha','<',"2018-06-01");
                                })*/
                                ->where("dmc.descripcion","NOT LIKE","%CONS%")
                                ->where("dmc.descripcion","NOT LIKE","%FARMACIA%")
                                ->where("dmc.descripcion","NOT LIKE","%CONTROL PRENATAL%")
                                ->where("dmc.descripcion","NOT LIKE","%PAPANICOLAOU%")
                                ->where("dmc.descripcion","NOT LIKE","%CONTROL NI_O SANO%")
                                /*->where(function($q2){
                                    $q2->where("dmc.descripcion","NOT LIKE","%NIÑO SANO%")
                                        ->orWhere("s.nombre","NOT LIKE","%NIÑO SANO%");
                                })*/
                                ;
            if(strlen(trim($nrodoc))>0){//$value->serie.'-'.$value->numero
                $resultado->whereRaw("CONCAT(movimiento.serie,'-',movimiento.numero) = ?",[$nrodoc]);
            }else{
                $resultado->where('plan.nombre','like','%'.$plan.'%')
                                ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                                ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                                ->where('dmc.pagodoctor','>=',0)
                                ->where('movimiento.voucher','like',"%".$nroope."%")
                                /*->where(function($query){
                                    $query->where('dmc.pagodoctor','>',0)
                                        ->orWhere(function($query2){
                                            $query2->where('dmc.pagodoctor','=',0)
                                                ->whereNotIn('medico.id',[56595,56595]);
                                        });
                                })*/
                                ->where(function($qqq){
                                    $qqq->whereNull("dmc.servicio_id")
                                        ->orWhere(function($qqqq){
                                            $qqqq->whereNotIn("s.tiposervicio_id",[2,5])
                                                ->where(function($qq){
                                                    $qq->where("s.tiposervicio_id",'<>',"4")
                                                        ->orWhere(function($qqq){
                                                            $qqq->where("s.tiposervicio_id",'=',"4")
                                                                ->whereNotIn("medico.id",[69,313,118,121]);
                                                        });
                                                });
                                        });
                                })
                                ->where(function($q){
                                    $q->where("medico.socio","=","S")
                                        ->orWhereIn("medico.id",[296,177])
                                        //->orWhere('dmc.descripcion','like','%V.M%')
                                        ;
                                });
            }
            $resultado->whereIn('movimiento.situacion',['C'])
                                ->whereNotIn('movimiento.situacion',['U','A']);
            if($fechainicial!=""){
                $resultado = $resultado->where('movimiento.fechaentrega','>=',$fechainicial);
            }
            if($fechafinal!=""){
                $resultado = $resultado->where('movimiento.fechaentrega','<=',$fechafinal);
            }
            $resultado        = $resultado->orderBy(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'))->orderBy(DB::raw('movimiento.fecha'), 'desc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                                ->select('dmc.id as dmc_id','dmc.comentario','movimiento.id as mov_id','movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.fechaingreso','movimiento.fechaentrega','movimiento.voucher','dmc.fechapagodoctor','dmc.recibopagodoctor',DB::raw('(SELECT count(ptpm.id) FROM reportepagomedico ptpm WHERE ptpm.iddetallemovcaja = dmc.id) as reportados'));
            $lista            = $resultado->get();
            //dd($resultado->toSql());
        }else{
            $resultado        = Pagomedico::whereRaw("TRUE");
            if(strlen(trim($nrodoc))>0){
                $resultado->whereRaw("numero","like","%".$nrodoc."%");
            }else{
                $resultado->where('compania','like','%'.$plan.'%')
                                ->where('paciente','like','%'.$paciente.'%')
                                ->where('doctor','like','%'.$doctor.'%')
                                ->where('voucher','like',"%".$nroope."%");
            }
            if($fechainicial!=""){
                $resultado = $resultado->where('fecpag','>=',$fechainicial);
            }
            if($fechafinal!=""){
                $resultado = $resultado->where('fecpag','<=',$fechafinal);
            }
            $resultado        = $resultado->orderBy("doctor")->orderBy("fecfac")->orderBy("numero")
                                ->select(DB::raw("'' as dmc_id"),DB::raw("'' as comentario"),DB::raw("'' as mov_id"),'total','compania as plan',DB::raw("'' as serie"),'numero','fecfac as fecha','servicio as servicio2',DB::raw('1 as cantidad'),'total as pagodoctor','doctor as medico','paciente as paciente2',DB::raw("'' as responsable"),DB::raw('numero as numero2'),'fecate as fechaingreso','fecpag as fechaentrega','voucher',DB::raw("'' as fechapagodoctor"),DB::raw("'' as recibopagodoctor"),DB::raw('0 as reportados'));
            $lista            = $resultado->get();
        }
        
        //dd($lista);
        $cabecera         = array();
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Atencion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Ope.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Plan', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cant.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Pago Medico', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Recibo Pago Medico', 'numero' => '1');
        $cabecera[]       = array('valor' => '', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Excel Generado', 'numero' => '1');
        $cabecera[]       = array('valor' => '', 'numero' => '1');
        
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta','situacion'));
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
        $entidad          = 'Reportepagofacturacion';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoPaciente          = array("" => "Todos", "P" => "Particular", "C" => "Convenio");
        $cboSituacion          = array("N" => "Nuevos", "H" => "Historico");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoPaciente', 'cboSituacion'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Reportepagofacturacion';
        $conceptopago = null;
        $formData            = array('reportepagofacturacion.store');
        $cboTipo          = array("Ingreso" => "Ingreso", "Egreso" => "Egreso");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('reportepagofacturacion', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
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
        $existe = Libreria::verificarExistencia($id, 'detallemovcaja');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $detallemovcaja = Detallemovcaja::find($id);
            $detallemovcaja->fechapagodoctor = date("Y-m-d");
            $detallemovcaja->recibopagodoctor = "NO SE PAGA";
            $detallemovcaja->save();
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
        $entidad  = 'reportepagofacturacion';
        $formData = array('route' => array('reportepagofacturacion.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function comentario(Request $request)
    {
        $error = DB::transaction(function() use($request){
            $movimiento = Detallemovcaja::find($request->input('id'));
            $movimiento->comentario = $request->input('value');
            $movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $tipopaciente     = Libreria::getParam($request->input('tipopaciente'));
        $tiposervicio_id  = Libreria::getParam($request->input('tiposervicio_id'));
        $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $nroope           = Libreria::getParam($request->input('nroope'));
        $nrodoc           = Libreria::getParam($request->input('nrodoc'));
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
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            //->whereNotIn('medico.id',[56595,69954,297,294,56655])
                            //->whereNotIn('s.tiposervicio_id',[1,2])
                            ->where('dmc.descripcion',"not like","%CONS%")
                            /*->where(function($query){
                                $query->whereNotIn('s.tiposervicio_id',[4])
                                        ->orWhere('movimiento.fecha','<',"2018-06-01");
                            })*/
                            //->where('movimiento.tipomovimiento_id','=',9)
                            ->where('dmc.pagodoctor','>=',0)
                            //->whereIn('movimiento.situacion',['C'])
                            //->whereNotIn('movimiento.situacion',['U','A'])

                            ->where("dmc.descripcion","NOT LIKE","%FARMACIA%")
                            ->where("dmc.descripcion","NOT LIKE","%CONTROL PRENATAL%")
                            ->where("dmc.descripcion","NOT LIKE","%PAPANICOLAOU%")
                            ->where("dmc.descripcion","NOT LIKE","%NIÑO SANO%")
                            ->where(function($qqq){
                                $qqq->whereNull("dmc.servicio_id")
                                    ->orWhere(function($qqqq){
                                        $qqqq->whereNotIn("s.tiposervicio_id",[2,5])
                                            ->where(function($qq){
                                                $qq->where("s.tiposervicio_id",'<>',"4")
                                                    ->orWhere(function($qqq){
                                                        $qqq->where("s.tiposervicio_id",'=',"4")
                                                            ->whereNotIn("medico.id",[69,313,118,121]);
                                                    });
                                            });
                                    });
                            })
                            ->where(function($q){
                                $q->where("medico.socio","=","S")
                                    ->orWhereIn("medico.id",[296,177])
                                    ->orWhere('dmc.descripcion','like','%V.M%');
                            })
                            ;
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fechaentrega','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fechaentrega','<=',$fechafinal);
        }
        $resultado        = $resultado->orderBy(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'), 'desc')->orderBy(DB::raw('movimiento.fecha'), 'desc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.total','movimiento.tipo_poliza',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','s.tiposervicio_id','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.fechaingreso','movimiento.fechaentrega','movimiento.voucher','movimiento.montoinicial','movimiento.igv','movimiento.copago');
        $lista            = $resultado->get();
        //dd($lista);
        Excel::create('ExcelPagoDoctor', function($excel) use($lista,$request) {
 
            $excel->sheet('PagoDoctor', function($sheet) use($lista,$request) {
                $cabecera[] = "Paciente";
                $cabecera[] = "Fecha Atencion";
                $cabecera[] = "Fecha Doc.";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Pago Doctor";
                $cabecera[] = "Precio";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Nro Ope.";
                $cabecera[] = "Plan";
                $cabecera[] = "Poliza";
                $cabecera[] = "Servicio";
                $cabecera[] = "Usuario";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;$total=0;$doctor="";$totalg=0;

                foreach ($lista as $key => $value){
                    if($doctor!=$value->medico){
                        if($doctor!=""){
                            $detalle = array();
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "TOTAL";
                            $detalle[] = number_format($total,2,'.','');
                            $sheet->row($c,$detalle);
                            $totalg=$totalg+$total;
                            $c=$c+1;        
                        }
                        $detalle = array();
                        $detalle[] = $value->medico;
                        $sheet->row($c,$detalle);
                        $doctor=$value->medico;
                        $c=$c+1;    
                        $total=0;
                    }
                    if($value->servicio_id>0){
                        $tiposervicio_id=$value->tiposervicio_id;
                    }else{
                        $tiposervicio_id=0;
                    }
                    $nombre=$value->servicio2;
                    $detalle = array();
                    $detalle[] = $value->paciente2;
                    $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "F".$value->serie.'-'.$value->numero;
                    $detalle[] = number_format(($value->pagodoctor),2,'.','');
                    //$detalle[] = number_format($value->precio,2,'.','');
                    if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $tiposervicio_id!="1") {
                        $value->precio=number_format($value->precio*100/(100-$value->montoinicial),2,'.','');
                    }
                    if($value->igv>0){
                        if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $tiposervicio_id!="1") {
                            $detalle[] = number_format($value->precio/1.18,2,'.','');
                        }else{
                            $detalle[] = number_format($value->copago+round($value->precio/1.18,2),2,'.','');
                        }
                    }else{
                        $detalle[] = number_format($value->precio,2,'.','');
                    }
                    $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                    $detalle[] = $value->voucher;
                    $detalle[] = $value->plan;
                    $detalle[] = empty($value->tipo_poliza)===true?'No Especificado':$value->tipo_poliza;
                    $detalle[] = $value->servicio2;
                    $detalle[] = $value->responsable;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                    $total=$total+$value->pagodoctor;
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "TOTAL";
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
                $c=$c+1;        
                $totalg=$totalg+$total;
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "TOTAL GENERAL";
                $detalle[] = number_format($totalg,2,'.','');
                $sheet->row($c,$detalle);
                $c=$c+1;        
            });
        })->export('xls');
    }

    public function excelGeneralOriginal(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('especialidad','especialidad.id','=','medico.especialidad_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where('plan.nombre','like','%'.$plan.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->where('dmc.pagodoctor','>',0)
                            ->whereIn('movimiento.situacion',['C'])
                            ->whereNotIn('movimiento.situacion',['U','A']);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fechaentrega','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fechaentrega','<=',$fechafinal);
        }
        $resultado        = $resultado->orderBy(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'), 'desc')->orderBy(DB::raw('movimiento.fecha'), 'desc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','plan.ruc as ruc2','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','dmc.cantidad','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.dni as dni2',DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.fechaingreso','movimiento.fechaentrega','movimiento.voucher','especialidad.nombre as especialidad2');
        $lista            = $resultado->get();

        Excel::create('ExcelPagoGeneral', function($excel) use($lista,$request) {
 
            $excel->sheet('PagoGeneral', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Paciente";
                $cabecera[] = "DNI";
                $cabecera[] = "Cliente";
                $cabecera[] = "RUC";
                $cabecera[] = "Situacion";
                $cabecera[] = "Rubro";
                $cabecera[] = "Total";
                $cabecera[] = "Fecha Atencion";
                $cabecera[] = "Medico";
                $cabecera[] = "Especialidad";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Pago Medico";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;$total=0;$doctor="";$totalg=0;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "F".$value->serie.'-'.$value->numero;
                    $detalle[] = $value->paciente2;
                    $detalle[] = $value->dni2;
                    $detalle[] = $value->plan;
                    $detalle[] = $value->ruc2;
                    $detalle[] = 'PAGADO';
                    $detalle[] = $value->servicio2;
                    $detalle[] = number_format(($value->precio*$value->cantidad)/1.18,2,'.','');
                    $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                    $detalle[] = $value->medico;
                    $detalle[] = $value->especialidad2;
                    $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                    $detalle[] = number_format(($value->pagodoctor),2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                    $total=$total+$value->pagodoctor;
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "TOTAL GENERAL";
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
                $c=$c+1;        
            });
        })->export('xls');
    }

    public function excelGeneral(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $nroope           = Libreria::getParam($request->input('nroope'));
        $nrodoc           = Libreria::getParam($request->input('nrodoc'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('especialidad','especialidad.id','=','medico.especialidad_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            //->where('medico.socio',"=","S")
                            /*->where(function($qu){
                                $qu->where(function($que){
                                    $que->whereNotIn('medico.id',[56595,69954,297,294,56655,71089])
                                        ->whereNotIn('s.tiposervicio_id',[1,2])
                                        ->where('dmc.descripcion',"not like","%CONS%")
                                        ->where(function($query){
                                            $query->whereNotIn('s.tiposervicio_id',[4])
                                                    ->orWhere('movimiento.fecha','<',"2018-06-01");
                                        });
                                })->orWhere('dmc.descripcion','like','%V.M.%');
                            })*/
                            ->where("dmc.descripcion","NOT LIKE","%CONS%")
                            ->where("dmc.descripcion","NOT LIKE","%FARMACIA%")
                            ->where("dmc.descripcion","NOT LIKE","%NIÑO SANO%")
                            ->whereNotIn("s.tiposervicio_id",[2,5])
                            ->where(function($qq){
                                $qq->where("s.tiposervicio_id",'<>',"4")
                                    ->orWhere(function($qqq){
                                        $qqq->where("s.tiposervicio_id",'=',"4")
                                            ->whereNotIn("medico.id",[69,313,118,121]);
                                    });
                            })
                            ->where(function($q){
                                $q->where("medico.socio","=","S")
                                    ->orWhereIn("medico.id",[296,177])
                                    ->orWhere('dmc.descripcion','like','%V.M.%');
                            });
        if(strlen(trim($nrodoc))>0){//$value->serie.'-'.$value->numero
            $resultado->whereRaw("CONCAT(movimiento.serie,'-',movimiento.numero) = ?",[$nrodoc]);
        }else{
            $resultado->where('plan.nombre','like','%'.$plan.'%')
                    ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                    ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                    ->where('dmc.pagodoctor','>=',0)
                    ->where('movimiento.voucher','like',"%".$nroope."%")
                    ->where('movimiento.tipomovimiento_id','=',9)
                    ->whereIn('movimiento.situacion',['C'])
                    ->whereNotIn('movimiento.situacion',['U','A']);
        }
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fechaentrega','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fechaentrega','<=',$fechafinal);
        }
        $resultado        = $resultado->orderBy(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'), 'desc')->orderBy(DB::raw('movimiento.fecha'), 'desc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.tipo_poliza','movimiento.total','movimiento.voucher',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','plan.ruc as ruc2','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','dmc.cantidad','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.dni as dni2',DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.fechaingreso','movimiento.fechaentrega','movimiento.voucher','especialidad.nombre as especialidad2');
        $lista            = $resultado->get(); //dd($lista);

        Excel::create('ExcelPagoGeneral', function($excel) use($lista,$request) {
 
            $excel->sheet('PagoGeneral', function($sheet) use($lista,$request) {
                $cabecera[] = "Medico";
                $cabecera[] = "Paciente";
                $cabecera[] = "Fecha Atencion";
                $cabecera[] = "Fecha Doc.";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Precio";
                $cabecera[] = "Pago Doctor";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Nro Ope.";
                $cabecera[] = "Plan";
                $cabecera[] = "Poliza";
                $cabecera[] = "Servicio";
                $cabecera[] = "Usuario";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;$total=0;$doctor="";$totalg=0;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = $value->medico;
                    $detalle[] = $value->paciente2;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                    $detalle[] = "F".$value->serie.'-'.$value->numero;
                    $detalle[] = number_format(($value->precio*$value->cantidad)/1.18,2,'.','');
                    $detalle[] = number_format(($value->pagodoctor),2,'.','');
                    $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                    $detalle[] = $value->voucher;
                    $detalle[] = $value->plan;
                    $detalle[] = empty($value->tipo_poliza) == true?'No Especificado':$value->tipo_poliza;
                    $detalle[] = $value->servicio2;
                    $detalle[] = $value->responsable;
                    //$detalle[] = $value->ruc2;
                    //$detalle[] = 'PAGADO';
                    //$detalle[] = $value->especialidad2;
                    //$detalle[] = $value->dni2;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                    $total=$total+$value->pagodoctor;
                }
                //dd($detalle);
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "TOTAL GENERAL";
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
                $c=$c+1;        
            });
        })->export('xls');
    }

    public function excelDoctor(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('especialidad','especialidad.id','=','medico.especialidad_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where('plan.nombre','like','%'.$plan.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->where('dmc.pagodoctor','>',0)
                            ->whereIn('movimiento.situacion',['C'])
                            ->whereNotIn('movimiento.situacion',['U','A']);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fechaentrega','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fechaentrega','<=',$fechafinal);
        }
        $resultado        = $resultado->orderBy(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'), 'desc')->orderBy(DB::raw('movimiento.fecha'), 'desc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.total','movimiento.tipo_poliza',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','plan.ruc as ruc2','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','dmc.cantidad','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.dni as dni2',DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.fechaingreso','movimiento.fechaentrega','movimiento.voucher','especialidad.nombre as especialidad2');
        $lista            = $resultado->get();

        Excel::create('ExcelPagoDoctor', function($excel) use($lista,$request) {
 
            $excel->sheet('PagoDoctor', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Paciente";
                $cabecera[] = "DNI";
                $cabecera[] = "Cliente";
                $cabecera[] = "Poliza";
                $cabecera[] = "RUC";
                $cabecera[] = "Situacion";
                $cabecera[] = "Rubro";
                $cabecera[] = "Total";
                $cabecera[] = "Fecha Atencion";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Pago Medico";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;$total=0;$doctor="";$totalg=0;

                foreach ($lista as $key => $value){
                    if($doctor!=$value->medico){
                        if($doctor!=""){
                            $detalle = array();
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "TOTAL";
                            $detalle[] = number_format($total,2,'.','');
                            $sheet->row($c,$detalle);
                            $totalg=$totalg+$total;
                            $c=$c+1;        
                        }
                        $detalle = array();
                        $detalle[] = $value->medico;
                        $sheet->row($c,$detalle);
                        $doctor=$value->medico;
                        $c=$c+1;    
                        $total=0;
                    }
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "F".$value->serie.'-'.$value->numero;
                    $detalle[] = $value->paciente2;
                    $detalle[] = $value->dni2;
                    $detalle[] = $value->plan;
                    $detalle[] = empty($value->tipo_poliza) === true?'No Especificado':$value->tipo_poliza;
                    $detalle[] = $value->ruc2;
                    $detalle[] = 'PAGADO';
                    $detalle[] = $value->servicio2;
                    $detalle[] = number_format(($value->precio*$value->cantidad)/1.18,2,'.','');
                    $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                    $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                    $detalle[] = number_format(($value->pagodoctor),2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                    $total=$total+$value->pagodoctor;
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "TOTAL";
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
                $totalg=$totalg+$total;
                $c=$c+1;
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "TOTAL GENERAL";
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
                $c=$c+1;        
            });
        })->export('xls');
    }

    public function nuevopago(Request $request)
    {
        $idsSelec = array();
        if (!is_null(Libreria::obtenerParametro($request->input('idS')))) {
            $idsSelec = explode(",", $request->input('idS'));
        }
        $idsSelec = implode(",", $idsSelec);
        $numerosSelec = $request->input('numeroS');
        $modelo   = new Detallemovcaja();
        $entidad  = 'Reportepagofacturacion';
        $formData = array('route' => array('reportepagofacturacion.pagarmedico'), 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar';
        return view($this->folderview.'.nuevopago')->with(compact('modelo', 'formData', 'entidad', 'boton','idsSelec','numerosSelec'));
    }
    
    public function pagarmedico(Request $request)
    {
        $idsSelec = array();
        if (!is_null(Libreria::obtenerParametro($request->input('idS')))) {
            $idsSelec = explode(",", $request->input('idS'));
        }
        if(count($idsSelec)==0){
            throw new \Exception("No se ha marcado ningun detalle");
        }
        $fecha = $request->input("fecha");
        if(strlen($fecha)==0){
            throw new \Exception("No se ha ingresado la fecha");
        }
        $numero = $request->input("numero");
        if(strlen($numero)==0){
            throw new \Exception("No se ha ingresado el numero del recibo");
        }
        $idhonorario = $request->input("voucher");
        if(!($idhonorario>0)){
            throw new \Exception("No se ha seleccionado ningun voucher");
        }
        //dd($idsSelec,$fecha,$numero);
        foreach ($idsSelec as $key => $value) {
            $detalle = Detallemovcaja::find($value);
            $detalle->fechapagodoctor = $fecha;
            $detalle->recibopagodoctor = $numero;
            $detalle->idhonorario = $idhonorario;
            $detalle->save();
        }
        return "OK";
    }
    
    public function nuevoreporte(Request $request)
    {
        $idsSelec = array();
        if (!is_null(Libreria::obtenerParametro($request->input('idS')))) {
            $idsSelec = explode(",", $request->input('idS'));
        }
        $idsSelec = implode(",", $idsSelec);
        $numerosSelec = $request->input('numeroS');
        $mes = substr($request->input('fecha'),0,7);
        $modelo   = new Detallemovcaja();
        $entidad  = 'Reportepagofacturacion';
        $formData = array('route' => array('reportepagofacturacion.generarreporte'), 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar';
        return view($this->folderview.'.nuevoreporte')->with(compact('modelo', 'formData', 'entidad', 'boton','idsSelec','numerosSelec','mes'));
    }
    
    public function generarreporte(Request $request)
    {
        $idsSelec = array();
        if (!is_null(Libreria::obtenerParametro($request->input('idS')))) {
            $idsSelec = explode(",", $request->input('idS'));
        }
        if(count($idsSelec)==0){
            throw new \Exception("No se ha marcado ningun detalle");
        }
        $mes = $request->input("mes");
        if(strlen($mes)==0){
            throw new \Exception("No se ha ingresado el mes");
        }
        $medico_id = $request->input("medico_id");
        if(!($medico_id>0)){
            throw new \Exception("No ha seleccionado al medico");
        }

        $dat = array();
        $user = \Auth::user();
        $dat[0]=array("respuesta"=>"OK");
        $idmov = 0;
        $error = DB::transaction(function() use($request,$user,$mes,$medico_id,$idsSelec,&$idmov){
            $movimiento        = new Movimiento();
            $movimiento->fecha = date("Y-m-d");
            $mespago = explode("-", $mes);
            $codmes = $mes;
            if(intval($mespago[1])==1){
                $mes = "ENERO";
            }elseif(intval($mespago[1])==2){
                $mes = "FEBRERO";
            }elseif(intval($mespago[1])==3){
                $mes = "MARZO";
            }elseif(intval($mespago[1])==4){
                $mes = "ABRIL";
            }elseif(intval($mespago[1])==5){
                $mes = "MAYO";
            }elseif(intval($mespago[1])==6){
                $mes = "JUNIO";
            }elseif(intval($mespago[1])==7){
                $mes = "JULIO";
            }elseif(intval($mespago[1])==8){
                $mes = "AGOSTO";
            }elseif(intval($mespago[1])==9){
                $mes = "SEPTIEMBRE";
            }elseif(intval($mespago[1])==10){
                $mes = "OCTUBRE";
            }elseif(intval($mespago[1])==11){
                $mes = "NOVIEMBRE";
            }elseif(intval($mespago[1])==12){
                $mes = "DICIEMBRE";
            }
            $movimiento->voucher= $mespago[1]."-".$mespago[0];
            $movimiento->formapago="VR";
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$medico_id;    
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->tipomovimiento_id=13;
            $movimiento->tipodocumento_id=22;
            $movimiento->comentario="HONORARIOS MEDICOS CONVENIO REPORTE MES ".$mes." 2018";
            $movimiento->situacion='N';
            $movimiento->estadopago='PP';
            $movimiento->tarjeta=$codmes;
            $movimiento->save();
            foreach ($idsSelec as $key => $value) {
                DB::insert("INSERT INTO reportepagomedico (idmovimiento,iddetallemovcaja) VALUES (?,?);",[$movimiento->id,$value]);
            }
            $idmov = $movimiento->id;
            error_log($idmov);
        });
        return "OK|reportepagofacturacion/generarreporteexcel?movimiento_id=".$idmov;
    }
    
    public function actualizarpagocero(Request $request){
        $iddetalle = $request->input("dmc_id");
        $detalle = Detallemovcaja::find($iddetalle);
        $detalle->pagodoctor = $request->input("valor");
        $detalle->save();
        return "OK";
    }

    public function generarreporteexcel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $movimiento_id         = Libreria::getParam($request->input('movimiento_id'));
        $movimiento = Movimiento::find($movimiento_id);
        $iddetalles = DB::select("SELECT iddetallemovcaja FROM reportepagomedico WHERE idmovimiento = ?",[$movimiento_id]);
        $iddetalles2 = array();
        foreach ($iddetalles as $iddet) {
            $iddetalles2[] = $iddet->iddetallemovcaja;
        }
        $iddetalles = $iddetalles2;
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            //->where('movimiento.tipomovimiento_id','=',9)
                            //->where('dmc.pagodoctor','>',0)
                            //->whereIn('movimiento.situacion',['C'])
                            //->whereNotIn('movimiento.situacion',['U','A'])
                            ->whereIn('dmc.id',$iddetalles);
        $resultado        = $resultado->orderBy(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'), 'desc')->orderBy(DB::raw('movimiento.fecha'), 'desc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','s.tiposervicio_id','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.fechaingreso','movimiento.fechaentrega','movimiento.voucher','movimiento.montoinicial','movimiento.igv','movimiento.copago');
        $lista            = $resultado->get();

        if(file_exists('D:\xampp\htdocs\juanpablo\pagodoctor\ExcelPagoPendienteDoctor_'.$movimiento_id.'.xls')){
            $excelDoc = Excel::load('D:\xampp\htdocs\juanpablo\pagodoctor\ExcelPagoPendienteDoctor_'.$movimiento_id.'.xls');
        }else{
            $excelDoc = Excel::create('ExcelPagoPendienteDoctor_'.$movimiento_id, function($excel) use($lista,$request) {
     
                $excel->sheet('PagoPendienteDoctor', function($sheet) use($lista,$request) {
                    $cabecera[] = "Paciente";
                    $cabecera[] = "Fecha Atencion";
                    $cabecera[] = "Fecha Doc.";
                    $cabecera[] = "Nro Doc.";
                    $cabecera[] = "Pago Doctor";
                    //$cabecera[] = "Precio";
                    $cabecera[] = "Fecha Pago";
                    $cabecera[] = "Nro Ope.";
                    $cabecera[] = "Plan";
                    $cabecera[] = "Servicio";
                    //$cabecera[] = "Usuario";
                    $sheet->row(1,$cabecera);
                    $c=2;$d=3;$band=true;$total=0;$doctor="";$totalg=0;

                    foreach ($lista as $key => $value){
                        if($doctor!=$value->medico){
                            if($doctor!=""){
                                $detalle = array();
                                $detalle[] = "";
                                $detalle[] = "";
                                $detalle[] = "";
                                $detalle[] = "TOTAL";
                                $detalle[] = number_format($total,2,'.','');
                                $sheet->row($c,$detalle);
                                $totalg=$totalg+$total;
                                $c=$c+1;        
                            }
                            $detalle = array();
                            $detalle[] = $value->medico;
                            $sheet->row($c,$detalle);
                            $doctor=$value->medico;
                            $c=$c+1;    
                            $total=0;
                        }
                        if($value->servicio_id>0){
                            $tiposervicio_id=$value->tiposervicio_id;
                        }else{
                            $tiposervicio_id=0;
                        }
                        $nombre=$value->servicio2;
                        $detalle = array();
                        $detalle[] = $value->paciente2;
                        $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        $detalle[] = "F".$value->serie.'-'.$value->numero;
                        $detalle[] = number_format(($value->pagodoctor),2,'.','');
                        //$detalle[] = number_format($value->precio,2,'.','');
                        if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $tiposervicio_id!="1") {
                            $value->precio=number_format($value->precio*100/(100-$value->montoinicial),2,'.','');
                        }
                        if($value->igv>0){
                            if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $tiposervicio_id!="1") {
                                //$detalle[] = number_format($value->precio/1.18,2,'.','');
                            }else{
                                //$detalle[] = number_format($value->copago+round($value->precio/1.18,2),2,'.','');
                            }
                        }else{
                            //$detalle[] = number_format($value->precio,2,'.','');
                        }
                        $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                        $detalle[] = $value->voucher;
                        $detalle[] = $value->plan;
                        $detalle[] = $value->servicio2;
                        //$detalle[] = $value->responsable;
                        $sheet->row($c,$detalle);
                        $c=$c+1;
                        $total=$total+$value->pagodoctor;
                    }
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "TOTAL";
                    $detalle[] = number_format($total,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;        
                    $totalg=$totalg+$total;
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "TOTAL GENERAL";
                    $detalle[] = number_format($totalg,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;        
                });
            })->store('xls','D:\xampp\htdocs\juanpablo\pagodoctor');
        }
        $excelDoc->download();
    }

    public function generarreportepdf(Request $request){
        setlocale(LC_TIME, 'spanish');
        $movimiento_id         = Libreria::getParam($request->input('movimiento_id'));
        $movimiento = Movimiento::find($movimiento_id);
        $iddetalles = DB::select("SELECT iddetallemovcaja FROM reportepagomedico WHERE idmovimiento = ?",[$movimiento_id]);
        $iddetalles2 = array();
        foreach ($iddetalles as $iddet) {
            $iddetalles2[] = $iddet->iddetallemovcaja;
        }
        $iddetalles = $iddetalles2;
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            //->where('movimiento.tipomovimiento_id','=',9)
                            //->where('dmc.pagodoctor','>',0)
                            //->whereIn('movimiento.situacion',['C'])
                            //->whereNotIn('movimiento.situacion',['U','A'])
                            ->whereIn('dmc.id',$iddetalles);
        $resultado        = $resultado->orderBy(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'), 'desc')->orderBy(DB::raw('movimiento.fecha'), 'desc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','s.tiposervicio_id','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.fechaingreso','movimiento.fechaentrega','movimiento.voucher','movimiento.montoinicial','movimiento.igv','movimiento.copago');
        $lista            = $resultado->get();

        if(file_exists('D:\xampp\htdocs\juanpablo\pagodoctor\PdfPagoPendienteDoctor_'.$movimiento_id.'.pdf')){
            $excelDoc = Excel::load('D:\xampp\htdocs\juanpablo\pagodoctor\PdfPagoPendienteDoctor_'.$movimiento_id.'.pdf');
        }else{
            $pdf = new TCPDF();
            $pdf::SetTitle('Comprobante');
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(110,7,"",0,0,'C');
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 10, 5, 55, 18);
            $pdf::Cell(60,7,utf8_encode("REPORTE DE PAGO A MEDICO"),'',0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',10);
            $pdf::Cell(275,7,utf8_encode($movimiento->persona->apellidopaterno." ".$movimiento->persona->apellidomaterno." ".$movimiento->persona->nombres),'',0,'C');
            $pdf::Ln();


            $pdf::SetFont('helvetica','B',10);
            $pdf::Cell(10,7,"N",1,0,'C');
            $pdf::Cell(10,7,"N",1,0,'C');

            $pdf::Output('PdfPagoPendienteDoctor_'.$movimiento_id.'.pdf');

            $excelDoc = Excel::create('PdfPagoPendienteDoctor_'.$movimiento_id, function($excel) use($lista,$request) {
     
                $excel->sheet('PagoPendienteDoctor', function($sheet) use($lista,$request) {
                    $cabecera[] = "Paciente";
                    $cabecera[] = "Fecha Atencion";
                    $cabecera[] = "Fecha Doc.";
                    $cabecera[] = "Nro Doc.";
                    $cabecera[] = "Pago Doctor";
                    //$cabecera[] = "Precio";
                    $cabecera[] = "Fecha Pago";
                    $cabecera[] = "Nro Ope.";
                    $cabecera[] = "Plan";
                    $cabecera[] = "Servicio";
                    //$cabecera[] = "Usuario";
                    $sheet->row(1,$cabecera);
                    $c=2;$d=3;$band=true;$total=0;$doctor="";$totalg=0;

                    foreach ($lista as $key => $value){
                        if($doctor!=$value->medico){
                            if($doctor!=""){
                                $detalle = array();
                                $detalle[] = "";
                                $detalle[] = "";
                                $detalle[] = "";
                                $detalle[] = "TOTAL";
                                $detalle[] = number_format($total,2,'.','');
                                $sheet->row($c,$detalle);
                                $totalg=$totalg+$total;
                                $c=$c+1;        
                            }
                            $detalle = array();
                            $detalle[] = $value->medico;
                            $sheet->row($c,$detalle);
                            $doctor=$value->medico;
                            $c=$c+1;    
                            $total=0;
                        }
                        if($value->servicio_id>0){
                            $tiposervicio_id=$value->tiposervicio_id;
                        }else{
                            $tiposervicio_id=0;
                        }
                        $nombre=$value->servicio2;
                        $detalle = array();
                        $detalle[] = $value->paciente2;
                        $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        $detalle[] = "F".$value->serie.'-'.$value->numero;
                        $detalle[] = number_format(($value->pagodoctor),2,'.','');
                        //$detalle[] = number_format($value->precio,2,'.','');
                        if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $tiposervicio_id!="1") {
                            $value->precio=number_format($value->precio*100/(100-$value->montoinicial),2,'.','');
                        }
                        if($value->igv>0){
                            if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $tiposervicio_id!="1") {
                                //$detalle[] = number_format($value->precio/1.18,2,'.','');
                            }else{
                                //$detalle[] = number_format($value->copago+round($value->precio/1.18,2),2,'.','');
                            }
                        }else{
                            //$detalle[] = number_format($value->precio,2,'.','');
                        }
                        $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                        $detalle[] = $value->voucher;
                        $detalle[] = $value->plan;
                        $detalle[] = $value->servicio2;
                        //$detalle[] = $value->responsable;
                        $sheet->row($c,$detalle);
                        $c=$c+1;
                        $total=$total+$value->pagodoctor;
                    }
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "TOTAL";
                    $detalle[] = number_format($total,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;        
                    $totalg=$totalg+$total;
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "TOTAL GENERAL";
                    $detalle[] = number_format($totalg,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;        
                });
            })->store('xls','D:\xampp\htdocs\juanpablo\pagodoctor');
        }
        $excelDoc->download();
    }

    public function generarreporteconsolidadoexcel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $mes = $request->input("mes");
        if(strlen($mes)==0){
            throw new \Exception("No se ha ingresado el mes");
        }
        $mespago = explode("-", $mes);
        $movimientos = Movimiento::where('tarjeta','=',$mes)->where('tipomovimiento_id',"=",13)->where('tipodocumento_id',"=",22)->get();

        // foreach ($movimientos as $key => $value) {
        //     echo $value->id.',';
        // }

        // exit();

        // dd($movimientos);
        $iddetalles2 = array();
        foreach ($movimientos as $movimiento) {
            $movimiento_id         = $movimiento->id;
            $iddetalles = DB::select("SELECT iddetallemovcaja FROM reportepagomedico WHERE idmovimiento = ?",[$movimiento_id]);
            
            // if($movimiento_id == '241777'){
            //     dd($iddetalles);
            //     // dd($movimiento_id);
            // }


            foreach ($iddetalles as $iddet) {
                $iddetalles2[] = $iddet->iddetallemovcaja;
            }
        }

        
        $iddetalles = $iddetalles2;

        // echo json_encode($iddetalles);
        // exit();

        if(count($iddetalles) <= 0){
            throw new \Exception("No hay pagos generados");
        }

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            //->where('movimiento.tipomovimiento_id','=',9)
                            //->where('dmc.pagodoctor','>',0)
                            //->whereIn('movimiento.situacion',['C'])
                            //->whereNotIn('movimiento.situacion',['U','A'])
                            ->whereIn('dmc.id',$iddetalles);
        $resultado        = $resultado->orderBy(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'), 'desc')->orderBy(DB::raw('movimiento.fecha'), 'desc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','s.tiposervicio_id','dmc.cantidad','dmc.pagodoctor','dmc.comentario',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.fechaingreso','movimiento.fechaentrega','movimiento.voucher','movimiento.montoinicial','movimiento.igv','movimiento.copago');
        $lista            = $resultado->get();



        // dd($lista);

        // if(file_exists('D:\xampp\htdocs\juanpablo\pagodoctor\ExcelPagoPendienteDoctorConsolidado_'.$mes.'.xls')){
        //     $excelDoc = Excel::load('D:\xampp\htdocs\juanpablo\pagodoctor\ExcelPagoPendienteDoctorConsolidado_'.$mes.'.xls');
        // }else{
            $excelDoc = Excel::create('ExcelPagoPendienteDoctorConsolidado_'.$mes, function($excel) use($lista,$request) {
     
                $excel->sheet('PagoPendienteDoctor', function($sheet) use($lista,$request) {
                    $cabecera[] = "Paciente";
                    $cabecera[] = "Fecha Atencion";
                    $cabecera[] = "Fecha Doc.";
                    $cabecera[] = "Nro Doc.";
                    $cabecera[] = "Pago Doctor";
                    //$cabecera[] = "Precio";
                    $cabecera[] = "Fecha Pago";
                    $cabecera[] = "Nro Ope.";
                    $cabecera[] = "Plan";
                    $cabecera[] = "Servicio";
                    //$cabecera[] = "Observacion";
                    //$cabecera[] = "Usuario";
                    $sheet->row(1,$cabecera);
                    $c=2;$d=3;$band=true;$total=0;$doctor="";$totalg=0;

                    // dd($lista);
                    foreach ($lista as $key => $value){
                        if($doctor!=$value->medico){
                            if($doctor!=""){
                                $detalle = array();
                                $detalle[] = "";
                                $detalle[] = "";
                                $detalle[] = "";
                                $detalle[] = "TOTAL";
                                $detalle[] = number_format($total,2,'.','');
                                $sheet->row($c,$detalle);
                                $totalg=$totalg+$total;
                                $c=$c+1;        
                            }
                            $detalle = array();
                            $detalle[] = $value->medico;
                            $sheet->row($c,$detalle);
                            $doctor=$value->medico;
                            $c=$c+1;    
                            $total=0;
                        }
                        if($value->servicio_id>0){
                            $tiposervicio_id=$value->tiposervicio_id;
                        }else{
                            $tiposervicio_id=0;
                        }
                        $nombre=$value->servicio2;
                        $detalle = array();
                        $detalle[] = $value->paciente2;
                        $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        $detalle[] = "F".$value->serie.'-'.$value->numero;
                        $detalle[] = number_format(($value->pagodoctor),2,'.','');
                        //$detalle[] = number_format($value->precio,2,'.','');
                        if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $tiposervicio_id!="1") {
                            $value->precio=number_format($value->precio*100/(100-$value->montoinicial),2,'.','');
                        }
                        if($value->igv>0){
                            if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $tiposervicio_id!="1") {
                                //$detalle[] = number_format($value->precio/1.18,2,'.','');
                            }else{
                                //$detalle[] = number_format($value->copago+round($value->precio/1.18,2),2,'.','');
                            }
                        }else{
                            //$detalle[] = number_format($value->precio,2,'.','');
                        }
                        $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                        $detalle[] = $value->voucher;
                        $detalle[] = $value->plan;
                        $detalle[] = $value->servicio2;
                        //$detalle[] = $value->comentario;
                        //$detalle[] = $value->responsable;
                        $sheet->row($c,$detalle);
                        $c=$c+1;
                        $total=$total+$value->pagodoctor;
                    }
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "TOTAL";
                    $detalle[] = number_format($total,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;        
                    $totalg=$totalg+$total;
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "TOTAL GENERAL";
                    $detalle[] = number_format($totalg,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;        
                });
            })->store('xls','D:\xampp\htdocs\juanpablo\pagodoctor');
        // }
        $excelDoc->download();
    }

}
