<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Conceptopago;
use App\Movimiento;
use App\Person;
use App\Detallemovcaja;
use App\Caja;
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
class PagoparticularController extends Controller
{
    protected $folderview      = 'app.pagoparticular';
    protected $tituloAdmin     = 'Pago Particular';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Pago Particular';
    protected $tituloEliminar  = 'Eliminar el Pago Particular';
    protected $rutas           = array('create' => 'pagoparticular.create', 
            'pagar'   => 'pagoparticular.pago', 
            'regularizar' => 'pagoparticular.regularizar',
            'delete' => 'pagoparticular.eliminar',
            'delete2' => 'recibomedico.eliminar',
            'delete3' => 'garantia.eliminar',
            'search' => 'pagoparticular.buscar',
            'index'  => 'pagoparticular.index',
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
        $entidad          = 'Pagoparticular';
        $nombre           = Libreria::getParam($request->input('nombre'));
        $paciente           = Libreria::getParam($request->input('nombrepaciente'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $horainicial     = Libreria::getParam($request->input('horainicial'));
        $horafinal       = Libreria::getParam($request->input('horafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));
        $recibo        = Libreria::getParam($request->input('recibo'));
        $first        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                ->join('person as medico','medico.id','=','movimiento.doctor_id')
                ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','movimiento.usuarioentrega_id')
                //->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                //->where('movimiento.situacion', 'like', 'N')
                ->whereIn('movimiento.tipodocumento_id',['14','18','23'])
                ->where('movimiento.numero', 'like', '%'.$recibo.'%')
                ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%');
        if($fechainicial!=""){
            $first = $first->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first = $first->where('movimiento.fecha','<=',$fechafinal);
        }
        if($horainicial!=""){
            $first = $first->where(DB::raw('cast(movimiento.created_at as time)'),'>=',$horainicial);   
        }
        if($horafinal!=""){
            $first = $first->where(DB::raw('cast(movimiento.created_at as time)'),'<=',$horafinal);
        }
        if($situacion!=""){
            $first = $first->where('movimiento.situacion','LIKE',$situacion);
        }else{
            $first = $first->where('movimiento.situacion','not LIKE','A');
        }
        $first = $first->select('movimiento.fecha','movimiento.id','movimiento.comentario as servicio2','movimiento.numero as recibo','movimiento.situacion as situacionentrega','movimiento.comentario as servicio','movimiento.fechaentrega','movimiento.id as servicio_id',DB::raw('1 as cantidad'),'movimiento.total as pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('movimiento.nombrepaciente as paciente'),DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id',DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        //dd($first->get());

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','dmc.usuarioentrega_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            //->where('dmc.id','=',DB::raw('(select dmc.id from movimiento mcaja where mcaja.tipomovimiento_id=2 and mcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,mcaja.listapago)>0 and mcaja.fecha >= '.$fechainicial.' and deleted_at is null limit 1)'))
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            //->where(DB::raw('case when LENGTH(TRIM(dmc.recibo)) > 0 then concat(\'RH \',dmc.recibo) else case when (select movcaja.numero from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) is null then \' \' else (select concat(\'RE \',movcaja.numero) from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) end end'), 'like', '%'.$recibo.'%')
                            ->where('dmc.pagodoctor','>',0);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($horainicial!=""){
            $resultado = $resultado->where(DB::raw('cast(movimiento.created_at as time)'),'>=',$horainicial);   
        }
        if($horafinal!=""){
            $resultado = $resultado->where(DB::raw('cast(movimiento.created_at as time)'),'<=',$horafinal);
        }
        if($situacion!=""){
            if($situacion=="E"){
                $resultado = $resultado->where('dmc.situacionentrega','LIKE',$situacion);
            }else{
                $resultado = $resultado->where(function ($query) {
                                    $query->whereNull('dmc.situacionentrega')
                                          ->orwhere(function ($query1){
                                            $query1->whereNotIn('dmc.situacionentrega', ['E','A']);
                                          });//normal
                            });                
            }
        }else{
            $resultado = $resultado->where('dmc.situacionentrega','not LIKE','A');
        }
        $resultado        = $resultado->orderBy('movimiento.fecha', 'ASC')
                            ->select('movimiento.fecha','dmc.id','dmc.descripcion as servicio2',DB::raw('case when LENGTH(TRIM(dmc.recibo)) > 0 then concat(\'RH \',dmc.recibo) else case when (select movcaja.numero from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) is null then \' \' else (select concat(\'RE \',movcaja.numero) from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) end end as recibo'),'dmc.situacionentrega','s.nombre as servicio','dmc.fechaentrega','dmc.servicio_id','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id',DB::raw('responsable.nombres as responsable'))->unionAll($first);
        $querySql = $resultado->toSql();
        $binding  = $resultado->getBindings();
        $resultado = DB::table(DB::raw("($querySql) as a"))->addBinding($binding);
        $lista            = $resultado->get();
        //dd($lista);
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Recibo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '2');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_regularizar = 'Regularizar';
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'titulo_regularizar', 'ruta','user'));
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
        if($band || $user->usertype_id==1 || $user->usertype_id==7){
            $entidad          = 'Pagoparticular';
            $title            = $this->tituloAdmin;
            $titulo_registrar = $this->tituloRegistrar;
            $ruta             = $this->rutas;
            $cboSituacion          = array("" => "Todos", "E" => "Pagado", "N" => "Pendiente");
            return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboSituacion', 'user'));
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
        $entidad             = 'Pagoparticular';
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pago($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'detallemovcaja');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $detalle             = Detallemovcaja::find($id);
        $entidad             = 'Pagoparticular';
        $formData            = array('pagoparticular.pagar', $id);
        $formData            = array('route' => $formData, 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Pagar';
        return view($this->folderview.'.pago')->with(compact('detalle', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pagar(Request $request)
    {
        $existe = Libreria::verificarExistencia($request->input('id'), 'detallemovcaja');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'recibo'                  => 'required|max:200',
                );
        $mensajes = array(
            'recibo.required'         => 'Debe ingresar un recibo',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $detalle           = Detallemovcaja::find($request->input('id'));
            $detalle->recibo = strtoupper($request->input('recibo'));
            $detalle->fechaentrega = date("Y-m-d");
            $detalle->usuarioentrega_id = $user->person_id;
            $detalle->situacionentrega = 'E';
            $detalle->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function regulariza($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id,$user){
            $Venta = Movimiento::find($id);
            $Venta->situacion='E';
            $Venta->fechaentrega = date("Y-m-d");
            $Venta->usuarioentrega_id = $user->person_id;
            $Venta->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function regularizar($id, $listarLuego)
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
        $entidad  = 'Pagoparticular';
        $formData = array('route' => array('pagoparticular.regulariza', $id), 'method' => 'Regulariza', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Regularizar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }


    public function destroy(Request $request)
    {
        $id = $request->input("id");
        $comentarioa = $request->input("comentarioa");
        $existe = Libreria::verificarExistencia($id, 'detallemovcaja');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id, $comentarioa, $user){
            $detalle = Detallemovcaja::find($id);
            $detalle->fechaentrega = date("Y-m-d");
            $detalle->motivo_anul = $comentarioa;
            $detalle->usuarioentrega_id = $user->person_id;
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
        $entidad  = 'Pagoparticular';
        $formData = array('route' => array('pagoparticular.destroy'), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar2')->with(compact('id','modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    /*public function pdfReporte(Request $request){
        $nombre           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->where('dmc.pagodoctor','>',0);
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
                                            $query1->whereNotIn('dmc.situacionentrega', ['E','A']);
                                          });//normal
                            });                
            }
        }
        $resultado        = $resultado->orderBy('medico.apellidopaterno', 'ASC')
                            ->orderBy('movimiento.fecha', 'ASC')
                            ->orderBy(DB::raw('case when LENGTH(TRIM(dmc.recibo)) > 0 then concat(\'RH \',dmc.recibo) else case when (select movcaja.numero from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) is null then \' \' else (select concat(\'RE \',movcaja.numero) from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) end end'), 'ASC')
                            ->select('movimiento.fecha','dmc.id','dmc.descripcion as servicio2',DB::raw('case when LENGTH(TRIM(dmc.recibo)) > 0 then concat(\'RH \',dmc.recibo) else case when (select movcaja.numero from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) is null then \' \' else (select concat(\'RE \',movcaja.numero) from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) end end as recibo'),'dmc.situacionentrega','dmc.id as iddetalle','s.nombre as servicio','dmc.fechaentrega','dmc.servicio_id','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),'dmc.persona_id as medico_id',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'responsable.nombres as responsable');
        $lista            = $resultado->get();

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('person as medico','medico.id','=','movimiento.doctor_id')
                            ->where('movimiento.situacion', 'like', 'N')
                            ->where('movimiento.tipodocumento_id','=','18')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->select('movimiento.*',DB::raw('movimiento.nombrepaciente as paciente'),'responsable.nombres as responsable')->orderBy('movimiento.fecha', 'ASC')->orderBy('medico.apellidopaterno','ASC')->orderBy('medico.apellidomaterno','ASC')->orderBy('movimiento.numero','ASC');

        $lista2            = $resultado->get();

        $pdf = new TCPDF();
        $pdf::SetTitle('Reporte de Dinero en Convenios por Medico al '.($fechafinal));
        if (count($lista) > 0 || count($lista2) > 0) {            
            $pdf::AddPage();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Reporte de Dinero en Convenios por Medico al ".date("d/m/Y",strtotime($fechafinal))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',7.5);
            $pdf::Cell(15,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(55,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(55,6,utf8_decode("CONCEPTO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("RECIBO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("TOTAL"),1,0,'C');
            $pdf::Cell(25,6,utf8_decode("USUARIO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("EGRESO"),1,0,'C');
            $pdf::Ln();
            $doctor="";$total=0;$totalgeneral=0;$idmedico=0;$array=array();
            foreach ($lista as $key => $value){
                if($doctor!=($value->medico)){
                    $pdf::SetFont('helvetica','B',7);
                    if($doctor!=""){
                        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('person as medico','medico.id','=','movimiento.doctor_id')
                            ->where('movimiento.doctor_id', '=', $idmedico)
                            ->where('movimiento.situacion', 'like', 'N')
                            ->where('movimiento.tipodocumento_id','=','18')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->select('movimiento.*',DB::raw('movimiento.nombrepaciente as paciente'),'responsable.nombres as responsable')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');

                        $lista2            = $resultado->get();
                        if(count($lista2)>0){
                            $pdf::SetFont('helvetica','B',7);
                            $pdf::Cell(195,6,("RECIBOS"),1,0,'L');
                            $pdf::Ln();
                            $pdf::SetFont('helvetica','',7);
                            foreach ($lista2 as $key2 => $value2){
                                $pdf::Cell(15,6,date("d/m/Y",strtotime($value2->fecha)),1,0,'C');
                                $pdf::Cell(55,6,($value2->paciente),1,0,'L');
                                $pdf::Cell(55,6,($value2->comentario),1,0,'L');
                                $pdf::Cell(15,6,$value2->numero,1,0,'C');
                                $pdf::Cell(15,6,number_format($value2->total,2,'.',''),1,0,'C');
                                $pdf::Cell(25,6,($value2->responsable),1,0,'L');
                                $pdf::Cell(15,6,'',1,0,'C');
                                $pdf::Ln();
                                $total = $total + $value2->total;
                            }
                        }

                        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('person as medico','medico.id','=','movimiento.doctor_id')
                            ->where('movimiento.doctor_id', '=', $idmedico)
                            ->where('movimiento.situacion', 'like', 'N')
                            ->where('movimiento.tipodocumento_id','=','14')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->select('movimiento.*',DB::raw('movimiento.nombrepaciente as paciente'),'responsable.nombres as responsable')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');

                        $lista2            = $resultado->get();
                        $egreso=0;
                        if(count($lista2)>0){
                            $pdf::SetFont('helvetica','B',7);
                            $pdf::Cell(195,6,("EGRESOS"),1,0,'L');
                            $pdf::Ln();
                            $pdf::SetFont('helvetica','',7);
                            foreach ($lista2 as $key2 => $value2){
                                $pdf::Cell(15,6,date("d/m/Y",strtotime($value2->fecha)),1,0,'C');
                                $pdf::Cell(55,6,($value2->paciente),1,0,'L');
                                $pdf::Cell(55,6,($value2->comentario),1,0,'L');
                                $pdf::Cell(15,6,'',1,0,'C');
                                $pdf::Cell(15,6,'',1,0,'C');
                                $pdf::Cell(25,6,($value2->responsable),1,0,'L');
                                $pdf::Cell(15,6,number_format($value2->total,2,'.',''),1,0,'C');
                                $pdf::Ln();
                                $egreso = $egreso + $value2->total;
                            }
                        }
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(125,6,("EFECTIVO :"),1,0,'R');
                        $pdf::Cell(15,6,'',1,0,'L');
                        $pdf::Cell(15,6,number_format($total,2,'.',''),1,0,'C');
                        $pdf::Cell(25,6,number_format($egreso,2,'.',''),1,0,'C');
                        $pdf::Cell(15,6,number_format($total - $egreso,2,'.',''),1,0,'C');
                        $totalgeneral = $totalgeneral + $total - $egreso;
                        $total=0;
                        $pdf::Ln();
                    }
                    $doctor=($value->medico);
                    $idmedico=$value->medico_id;
                    $array[]=$idmedico;
                    $pdf::SetFont('helvetica','B',8);
                    $pdf::Cell(195,6,($doctor),1,0,'L');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(195,6,("HONORARIOS"),1,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',7);
                $pdf::Cell(15,6,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                $pdf::Cell(55,6,($value->paciente),1,0,'L');
                if($value->servicio_id>0){
                    $pdf::Cell(55,6,utf8_decode($value->servicio),1,0,'L');
                }else{
                    $pdf::Cell(55,6,utf8_decode($value->servicio2),1,0,'L');
                }
                $pdf::Cell(15,6,$value->recibo,1,0,'L');
                $pdf::Cell(15,6,number_format($value->pagodoctor*$value->cantidad,2,'.',''),1,0,'C');
                $pdf::Cell(25,6,($value->responsable),1,0,'L');
                $pdf::Cell(15,6,'',1,0,'L');
                $total=$total + $value->pagodoctor*$value->cantidad;
                $pdf::Ln();                
            }
            if($idmedico==0){
                $medico=Person::where(DB::raw('concat(apellidopaterno,\' \',apellidomaterno,\' \',nombres)'),'like','%'.$nombre.'%')->first();
                $idmedico=$medico->id;
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(195,6,($medico->apellidopaterno.' '.$medico->apellidomaterno.' '.$medico->nombres),1,0,'L');
                $pdf::Ln();
            }
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('person as medico','medico.id','=','movimiento.doctor_id')
                            ->where('movimiento.doctor_id', '=', $idmedico)
                            ->where('movimiento.situacion', 'like', 'N')
                            ->where('movimiento.tipodocumento_id','=','18')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->select('movimiento.*',DB::raw('movimiento.nombrepaciente as paciente'),'responsable.nombres as responsable')->orderBy('movimiento.fecha', 'ASC')->orderBy('medico.apellidopaterno','ASC')->orderBy('medico.apellidomaterno','ASC')->orderBy('movimiento.numero','ASC');

            $lista2            = $resultado->get();
            if(count($lista2)>0){
                $pdf::SetFont('helvetica','B',7);
                $pdf::Cell(195,6,("RECIBOS"),1,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',7);
                foreach ($lista2 as $key2 => $value2){
                    $array[]=$value2->doctor_id;
                    $pdf::Cell(15,6,date("d/m/Y",strtotime($value2->fecha)),1,0,'C');
                    $pdf::Cell(55,6,($value2->paciente),1,0,'L');
                    $pdf::Cell(55,6,($value2->comentario),1,0,'L');
                    $pdf::Cell(15,6,$value2->numero,1,0,'C');
                    $pdf::Cell(15,6,number_format($value2->total,2,'.',''),1,0,'C');
                    $pdf::Cell(25,6,($value2->responsable),1,0,'L');
                    $pdf::Cell(15,6,'',1,0,'C');
                    $pdf::Ln();
                    $total = $total + $value2->total;
                }
            }
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                ->join('person as medico','medico.id','=','movimiento.doctor_id')
                ->where('movimiento.doctor_id', '=', $idmedico)
                ->where('movimiento.situacion', 'like', 'N')
                ->where('movimiento.tipodocumento_id','=','14')
                ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                ->select('movimiento.*',DB::raw('movimiento.nombrepaciente as paciente'),'responsable.nombres as responsable')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');

            $lista2            = $resultado->get();
            $egreso=0;
            if(count($lista2)>0){
                $pdf::SetFont('helvetica','B',7);
                $pdf::Cell(195,6,("EGRESOS"),1,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',7);
                foreach ($lista2 as $key2 => $value2){
                    $pdf::Cell(15,6,date("d/m/Y",strtotime($value2->fecha)),1,0,'C');
                    $pdf::Cell(55,6,($value2->paciente),1,0,'L');
                    $pdf::Cell(55,6,($value2->comentario),1,0,'L');
                    $pdf::Cell(15,6,'',1,0,'C');
                    $pdf::Cell(15,6,'',1,0,'C');
                    $pdf::Cell(25,6,($value2->responsable),1,0,'L');
                    $pdf::Cell(15,6,number_format($value2->total,2,'.',''),1,0,'C');
                    $pdf::Ln();
                    $egreso = $egreso + $value2->total;
                }
            }

            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(125,6,("EFECTIVO :"),1,0,'R');
            $pdf::Cell(15,6,(""),1,0,'R');
            $pdf::Cell(15,6,number_format($total,2,'.',''),1,0,'C');
            $pdf::Cell(25,6,number_format($egreso,2,'.',''),1,0,'C');
            $pdf::Cell(15,6,number_format($total - $egreso,2,'.',''),1,0,'C');
            $totalgeneral = $totalgeneral + $total - $egreso;
            $total=0;
            $pdf::Ln();
            //PARA RECIBOS SIN HONORARIOS CUANDO MUESTRA TODOS
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('person as medico','medico.id','=','movimiento.doctor_id')
                            ->where('movimiento.situacion', 'like', 'N')
                            ->where('movimiento.tipodocumento_id','=','18')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->select('movimiento.*',DB::raw('movimiento.nombrepaciente as paciente'),'responsable.nombres as responsable',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');

            $lista2            = $resultado->get();
            if(count($lista2)>0){
                $doctor="";
                foreach ($lista2 as $key2 => $value2){
                    if(!in_array($value2->doctor_id,$array)){
                        if($doctor!=$value2->medico){
                            if($total>0){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(125,6,("EFECTIVO :"),1,0,'R');
                                $pdf::Cell(15,6,'',1,0,'L');
                                $pdf::Cell(15,6,number_format($total,2,'.',''),1,0,'C');
                                $pdf::Cell(25,6,number_format(0,2,'.',''),1,0,'C');
                                $pdf::Cell(15,6,number_format($total,2,'.',''),1,0,'C');
                                $totalgeneral = $totalgeneral + $total;
                                $total=0;
                                $pdf::Ln();
                            }
                            $doctor=$value2->medico;
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(195,6,($doctor),1,0,'L');
                            $pdf::Ln();
                            $pdf::SetFont('helvetica','B',7);
                            $pdf::Cell(195,6,("RECIBOS"),1,0,'L');
                            $pdf::Ln();
                            $pdf::SetFont('helvetica','',7);
                        }
                        $pdf::Cell(15,6,date("d/m/Y",strtotime($value2->fecha)),1,0,'C');
                        $pdf::Cell(55,6,($value2->paciente),1,0,'L');
                        $pdf::Cell(55,6,($value2->comentario),1,0,'L');
                        $pdf::Cell(15,6,$value2->numero,1,0,'C');
                        $pdf::Cell(15,6,number_format($value2->total,2,'.',''),1,0,'C');
                        $pdf::Cell(25,6,($value2->responsable),1,0,'L');
                        $pdf::Cell(15,6,'',1,0,'C');
                        $pdf::Ln();
                        $total = $total + $value2->total;
                    }
                }
                if($total>0){
                    $pdf::SetFont('helvetica','B',8);
                    $pdf::Cell(125,6,("EFECTIVO :"),1,0,'R');
                    $pdf::Cell(15,6,'',1,0,'L');
                    $pdf::Cell(15,6,number_format($total,2,'.',''),1,0,'C');
                    $pdf::Cell(25,6,number_format(0,2,'.',''),1,0,'C');
                    $pdf::Cell(15,6,number_format($total,2,'.',''),1,0,'C');
                    $pdf::Ln();
                    $totalgeneral = $totalgeneral + $total;
                    $total=0;
                }
            }


            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(180,6,("TOTAL GENERAL:"),1,0,'R');
            $pdf::Cell(15,6,number_format($totalgeneral,2,'.',''),1,0,'C');
            $pdf::Ln();
        }
        $pdf::Output('ReporteParticular.pdf');
    }*/

    public function pdfReporte(Request $request){
        $nombre           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $horainicial     = Libreria::getParam($request->input('horainicial'));
        $horafinal       = Libreria::getParam($request->input('horafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));

        $first        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                ->join('person as medico','medico.id','=','movimiento.doctor_id')
                ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','movimiento.usuarioentrega_id')
                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                ->where('movimiento.situacion', 'like', 'N')
                ->whereIn('movimiento.tipodocumento_id',['14','18','23'])
                ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%');
        if($fechainicial!=""){
            $first = $first->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first = $first->where('movimiento.fecha','<=',$fechafinal);
        }
        if($horainicial!=""){
            $first = $first->where(DB::raw('cast(movimiento.created_at as time)'),'>=',$horainicial);   
        }
        if($horafinal!=""){
            $first = $first->where(DB::raw('cast(movimiento.created_at as time)'),'<=',$horafinal);
        }
        $first = $first->select('movimiento.fecha','movimiento.id','movimiento.comentario as servicio2','movimiento.numero as recibo','movimiento.situacion as situacionentrega','movimiento.id as iddetalle','movimiento.comentario as servicio','movimiento.fechaentrega','movimiento.id as servicio_id',DB::raw('1 as cantidad'),'movimiento.total as pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('movimiento.nombrepaciente as paciente'),DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id',DB::raw('responsable.nombres as responsable'),DB::raw('case when movimiento.tipodocumento_id=14 then \'EGRESOS\' when movimiento.tipodocumento_id=23 then \'INGRESOS\' else \'RECIBOS\' end as tipo'),'movimiento.nombrepaciente')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->where('dmc.pagodoctor','>',0);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($horainicial!=""){
            $resultado = $resultado->where(DB::raw('cast(movimiento.created_at as time)'),'>=',$horainicial);   
        }
        if($horafinal!=""){
            $resultado = $resultado->where(DB::raw('cast(movimiento.created_at as time)'),'<=',$horafinal);
        }
        if($situacion!=""){
            if($situacion=="E"){
                $resultado = $resultado->where('dmc.situacionentrega','LIKE',$situacion);
            }else{
                $resultado = $resultado->where(function ($query) {
                                    $query->whereNull('dmc.situacionentrega')
                                          ->orwhere(function ($query1){
                                            $query1->whereNotIn('dmc.situacionentrega', ['E','A']);
                                          });//normal
                            });                
            }
        }
        $resultado        = $resultado->orderBy('medico.apellidopaterno', 'ASC')
                            ->orderBy('movimiento.fecha', 'ASC')
                            ->orderBy(DB::raw('case when LENGTH(TRIM(dmc.recibo)) > 0 then concat(\'RH \',dmc.recibo) else case when (select movcaja.numero from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) is null then \' \' else (select concat(\'RE \',movcaja.numero) from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) end end'), 'ASC')
                            ->select('movimiento.fecha','dmc.id','dmc.descripcion as servicio2',DB::raw('case when LENGTH(TRIM(dmc.recibo)) > 0 then concat(\'RH \',dmc.recibo) else case when (select movcaja.numero from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) is null then \' \' else (select concat(\'RE \',movcaja.numero) from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) end end as recibo'),'dmc.situacionentrega','dmc.id as iddetalle','s.nombre as servicio','dmc.fechaentrega','dmc.servicio_id','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),'dmc.persona_id as medico_id',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'responsable.nombres as responsable',DB::raw('case when LENGTH(TRIM(dmc.recibo)) > 0 then \'HONORARIOS\' else \'RECIBOS\'end as tipo'),'movimiento.nombrepaciente')->unionAll($first);
        $querySql = $resultado->toSql();
        $binding  = $resultado->getBindings();
        $resultado = DB::table(DB::raw("($querySql) as a order by medico asc,tipo asc,fecha asc,recibo asc"))->addBinding($binding);
        $lista            = $resultado->get();
        

        $pdf = new TCPDF();
        $pdf::SetTitle('Reporte de Dinero en Convenios por Medico al '.($fechafinal));
        if (count($lista) > 0) {            
            $pdf::AddPage();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Reporte de Dinero Particulares por Medico al ".date("d/m/Y",strtotime($fechafinal))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',7.5);
            $pdf::Cell(15,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(55,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(55,6,utf8_decode("CONCEPTO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("RECIBO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("TOTAL"),1,0,'C');
            $pdf::Cell(25,6,utf8_decode("USUARIO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("EGRESO"),1,0,'C');
            $pdf::Ln();
            $doctor="";$total=0;$totalgeneral=0;$idmedico=0;$array=array();$tipo='';$egreso=0;
            foreach ($lista as $key => $value){
                if($doctor!=($value->medico)){
                    $pdf::SetFont('helvetica','B',7);
                    if($doctor!=""){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(125,6,("EFECTIVO :"),1,0,'R');
                        $pdf::Cell(15,6,'',1,0,'L');
                        $pdf::Cell(15,6,number_format($total,2,'.',''),1,0,'C');
                        $pdf::Cell(25,6,number_format($egreso,2,'.',''),1,0,'C');
                        $pdf::Cell(15,6,number_format($total - $egreso,2,'.',''),1,0,'C');
                        $totalgeneral = $totalgeneral + $total - $egreso;
                        $total=0;
                        $pdf::Ln();
                    }
                    $doctor=($value->medico);
                    $idmedico=$value->medico_id;
                    $tipo="";$egreso=0;
                    $pdf::SetFont('helvetica','B',8);
                    $pdf::Cell(195,6,($doctor),1,0,'L');
                    $pdf::Ln();
                }
                if($tipo!=$value->tipo){
                    $pdf::SetFont('helvetica','B',8);
                    $pdf::Cell(195,6,$value->tipo,1,0,'L');
                    $pdf::Ln();
                    $tipo=$value->tipo;
                }
                $pdf::SetFont('helvetica','',7);
                if($tipo=="EGRESOS"){
                    $pdf::Cell(15,6,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    $pdf::Cell(55,6,($value->nombrepaciente),1,0,'L');
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();                    
                    $pdf::Multicell(55,3,($value->servicio),0,'L');
                    $pdf::SetXY($x,$y);
                    $pdf::Cell(55,6,"",1,0,'C');
                    //$pdf::Cell(55,6,($value->servicio),1,0,'L');
                    $pdf::Cell(15,6,'',1,0,'C');
                    $pdf::Cell(15,6,'',1,0,'C');
                    $pdf::Cell(25,6,($value->responsable),1,0,'L');
                    $pdf::Cell(15,6,number_format($value->pagodoctor,2,'.',''),1,0,'C');
                    $pdf::Ln();
                    $egreso = $egreso + $value->pagodoctor;
                }else{
                    $pdf::Cell(15,6,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    if($value->nombrepaciente!="")
                        $pdf::Cell(55,6,($value->nombrepaciente),1,0,'L');
                    else
                        $pdf::Cell(55,6,($value->paciente),1,0,'L');
                    if($value->servicio_id>0){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(55,3,($value->servicio),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(55,6,"",1,0,'C');
                        //$pdf::Cell(55,6,utf8_decode($value->servicio),1,0,'L');
                    }else{
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(55,3,($value->servicio2),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(55,6,"",1,0,'C');
                        //$pdf::Cell(55,6,utf8_decode($value->servicio2),1,0,'L');
                    }
                    $pdf::Cell(15,6,$value->recibo,1,0,'L');
                    $pdf::Cell(15,6,number_format($value->pagodoctor*$value->cantidad,2,'.',''),1,0,'C');
                    $pdf::Cell(25,6,($value->responsable),1,0,'L');
                    $pdf::Cell(15,6,'',1,0,'L');
                    $total=$total + $value->pagodoctor*$value->cantidad;
                    $pdf::Ln();                
                }
            }
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(125,6,("EFECTIVO :"),1,0,'R');
            $pdf::Cell(15,6,(""),1,0,'R');
            $pdf::Cell(15,6,number_format($total,2,'.',''),1,0,'C');
            $pdf::Cell(25,6,number_format($egreso,2,'.',''),1,0,'C');
            $pdf::Cell(15,6,number_format($total - $egreso,2,'.',''),1,0,'C');
            $totalgeneral = $totalgeneral + $total - $egreso;
            $total=0;
            $pdf::Ln();

            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(180,6,("TOTAL GENERAL:"),1,0,'R');
            $pdf::Cell(15,6,number_format($totalgeneral,2,'.',''),1,0,'C');
            $pdf::Ln();
        }
        $pdf::Output('ReporteParticular.pdf');
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        // $nombre           = Libreria::getParam($request->input('nombre'));
        // $paciente           = Libreria::getParam($request->input('nombrepaciente'));
        // $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        // $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        // $horainicial     = Libreria::getParam($request->input('horainicial'));
        // $horafinal       = Libreria::getParam($request->input('horafinal'));
        // $situacion        = Libreria::getParam($request->input('situacion'));
        // $recibo        = Libreria::getParam($request->input('recibo'));
        // $first        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
        //         ->join('person as medico','medico.id','=','movimiento.doctor_id')
        //         ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','movimiento.usuarioentrega_id')
        //         ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
        //         ->where('movimiento.situacion', 'like', 'N')
        //         ->whereIn('movimiento.tipodocumento_id',['14','18','23'])
        //         ->where('movimiento.numero', 'like', '%'.$recibo.'%')
        //         ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
        //         ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%');
        // if($fechainicial!=""){
        //     $first = $first->where('movimiento.fecha','>=',$fechainicial);
        // }
        // if($fechafinal!=""){
        //     $first = $first->where('movimiento.fecha','<=',$fechafinal);
        // }
        // if($horainicial!=""){
        //     $first = $first->where(DB::raw('cast(movimiento.created_at as time)'),'>=',$horainicial);   
        // }
        // if($horafinal!=""){
        //     $first = $first->where(DB::raw('cast(movimiento.created_at as time)'),'<=',$horafinal);
        // }
        // $first = $first->select('movimiento.fecha','movimiento.id','movimiento.comentario as servicio2','movimiento.numero as recibo','movimiento.situacion as situacionentrega','movimiento.comentario as servicio','movimiento.fechaentrega','movimiento.id as servicio_id',DB::raw('1 as cantidad'),'movimiento.total as pagodoctor','medico.id as medico_id',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('movimiento.nombrepaciente as paciente'),DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id',DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');


        // $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
        //                     ->join('person as medico','medico.id','=','dmc.persona_id')
        //                     ->join('person as paciente','paciente.id','=','movimiento.persona_id')
        //                     ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','dmc.usuarioentrega_id')
        //                     ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
        //                     ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
        //                     //->where('dmc.id','=',DB::raw('(select dmc.id from movimiento mcaja where mcaja.tipomovimiento_id=2 and mcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,mcaja.listapago)>0 and mcaja.fecha >= '.$fechainicial.' and deleted_at is null limit 1)'))
        //                     ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
        //                     ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
        //                     ->where('movimiento.tipomovimiento_id','=',1)
        //                     ->where('movimiento.situacion','<>','U')
        //                     //->where(DB::raw('case when LENGTH(TRIM(dmc.recibo)) > 0 then concat(\'RH \',dmc.recibo) else case when (select movcaja.numero from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) is null then \' \' else (select concat(\'RE \',movcaja.numero) from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) end end'), 'like', '%'.$recibo.'%')
        //                     ->where('dmc.pagodoctor','>',0);
        // if($fechainicial!=""){
        //     $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        // }
        // if($fechafinal!=""){
        //     $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        // }
        // if($horainicial!=""){
        //     $resultado = $resultado->where(DB::raw('cast(movimiento.created_at as time)'),'>=',$horainicial);   
        // }
        // if($horafinal!=""){
        //     $resultado = $resultado->where(DB::raw('cast(movimiento.created_at as time)'),'<=',$horafinal);
        // }
        // if($situacion!=""){
        //     if($situacion=="E"){
        //         $resultado = $resultado->where('dmc.situacionentrega','LIKE',$situacion);
        //     }else{
        //         $resultado = $resultado->where(function ($query) {
        //                             $query->whereNull('dmc.situacionentrega')
        //                                   ->orwhere(function ($query1){
        //                                     $query1->whereNotIn('dmc.situacionentrega', ['E','A']);
        //                                   });//normal
        //                     });                
        //     }
        // }else{
        //     $resultado = $resultado->where('dmc.situacionentrega','not LIKE','A');
        // }
        // $resultado        = $resultado->orderBy('movimiento.fecha', 'ASC')
        //                     ->select('movimiento.fecha','dmc.id','dmc.descripcion as servicio2',DB::raw('case when LENGTH(TRIM(dmc.recibo)) > 0 then concat(\'RH \',dmc.recibo) else case when (select movcaja.numero from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) is null then \' \' else (select concat(\'RE \',movcaja.numero) from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) end end as recibo'),'dmc.situacionentrega','s.nombre as servicio','dmc.fechaentrega','dmc.servicio_id','dmc.cantidad','dmc.pagodoctor','medico.id as medico_id',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id',DB::raw('responsable.nombres as responsable'))->unionAll($first);
        // $querySql = $resultado->toSql();
        // $binding  = $resultado->getBindings();
        // $resultado = DB::table(DB::raw("($querySql) as a"))->addBinding($binding);
        // $lista            = $resultado->get();

        // dd($lista);
//  ------------------------------------------------------------------------------

        $nombre           = Libreria::getParam($request->input('nombre'));
        $paciente           = Libreria::getParam($request->input('nombrepaciente'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $horainicial     = Libreria::getParam($request->input('horainicial'));
        $horafinal       = Libreria::getParam($request->input('horafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));
        $recibo        = Libreria::getParam($request->input('recibo'));
        $first        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                ->join('person as medico','medico.id','=','movimiento.doctor_id')
                ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','movimiento.usuarioentrega_id')
                //->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                //->where('movimiento.situacion', 'like', 'N')
                ->whereIn('movimiento.tipodocumento_id',['14','18','23'])
                ->where('movimiento.numero', 'like', '%'.$recibo.'%')
                ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%');
        if($fechainicial!=""){
            $first = $first->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first = $first->where('movimiento.fecha','<=',$fechafinal);
        }
        if($horainicial!=""){
            $first = $first->where(DB::raw('cast(movimiento.created_at as time)'),'>=',$horainicial);   
        }
        if($horafinal!=""){
            $first = $first->where(DB::raw('cast(movimiento.created_at as time)'),'<=',$horafinal);
        }
        if($situacion!=""){
            $first = $first->where('movimiento.situacion','LIKE',$situacion);
        }else{
            $first = $first->where('movimiento.situacion','not LIKE','A');
        }
        $first = $first->select('movimiento.fecha','movimiento.id','movimiento.comentario as servicio2','movimiento.numero as recibo','movimiento.situacion as situacionentrega','movimiento.comentario as servicio','movimiento.fechaentrega','movimiento.id as servicio_id',DB::raw('1 as cantidad'),'movimiento.total as pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('movimiento.nombrepaciente as paciente'),DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id',DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        //dd($first->get());

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','dmc.usuarioentrega_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            //->where('dmc.id','=',DB::raw('(select dmc.id from movimiento mcaja where mcaja.tipomovimiento_id=2 and mcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,mcaja.listapago)>0 and mcaja.fecha >= '.$fechainicial.' and deleted_at is null limit 1)'))
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            //->where(DB::raw('case when LENGTH(TRIM(dmc.recibo)) > 0 then concat(\'RH \',dmc.recibo) else case when (select movcaja.numero from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) is null then \' \' else (select concat(\'RE \',movcaja.numero) from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) end end'), 'like', '%'.$recibo.'%')
                            ->where('dmc.pagodoctor','>',0);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($horainicial!=""){
            $resultado = $resultado->where(DB::raw('cast(movimiento.created_at as time)'),'>=',$horainicial);   
        }
        if($horafinal!=""){
            $resultado = $resultado->where(DB::raw('cast(movimiento.created_at as time)'),'<=',$horafinal);
        }
        if($situacion!=""){
            if($situacion=="E"){
                $resultado = $resultado->where('dmc.situacionentrega','LIKE',$situacion);
            }else{
                $resultado = $resultado->where(function ($query) {
                                    $query->whereNull('dmc.situacionentrega')
                                          ->orwhere(function ($query1){
                                            $query1->whereNotIn('dmc.situacionentrega', ['E','A']);
                                          });//normal
                            });                
            }
        }else{
            $resultado = $resultado->where('dmc.situacionentrega','not LIKE','A');
        }
        $resultado        = $resultado->orderBy('movimiento.fecha', 'ASC')
                            ->select('movimiento.fecha','dmc.id','dmc.descripcion as servicio2',DB::raw('case when LENGTH(TRIM(dmc.recibo)) > 0 then concat(\'RH \',dmc.recibo) else case when (select movcaja.numero from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) is null then \' \' else (select concat(\'RE \',movcaja.numero) from movimiento as movcaja where movcaja.conceptopago_id=8 and FIND_IN_SET(dmc.id,movcaja.listapago)>0 and movcaja.deleted_at is null limit 1) end end as recibo'),'dmc.situacionentrega','s.nombre as servicio','dmc.fechaentrega','dmc.servicio_id','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id',DB::raw('responsable.nombres as responsable','medico.id as medico_id'))->unionAll($first);
        $querySql = $resultado->toSql();
        $binding  = $resultado->getBindings();
        $resultado = DB::table(DB::raw("($querySql) as a"))->addBinding($binding);
        $lista            = $resultado->get();


// -------------------------------------

        Excel::create('ExcelPagoParticular', function($excel) use($lista,$request) {
 
            $excel->sheet('PagoParticular', function($sheet) use($lista,$request) {
 
                $cabecera = array();
                $cabecera[] = "Tipo";
                $cabecera[] = "Fecha";
                $cabecera[] = "Doctor";
                $cabecera[] = "Paciente";
                $cabecera[] = "Servicio";
                $cabecera[] = "Pago";
                $cabecera[] = "Usuario";
                $cabecera[] = "Recibo";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;$doctor="";$idmedico=0;$nombre="";

                foreach ($lista as $key => $value){
                    if($doctor!=($value->medico)){
                        if($doctor!=""){
                            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                                ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('person as medico','medico.id','=','movimiento.doctor_id')
                                ->where('movimiento.doctor_id', '=', $idmedico)
                                ->where('movimiento.situacion', 'like', 'N')
                                ->where('movimiento.tipodocumento_id','=','18')
                                ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                                ->select('movimiento.*',DB::raw('movimiento.nombrepaciente as paciente'),'responsable.nombres as responsable')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');

                            $lista2            = $resultado->get();
                            if(count($lista2)>0){
                                foreach ($lista2 as $key2 => $value2){
                                    $detalle = array();    
                                    $detalle[] = "RECIBOS";
                                    $detalle[] = date("d/m/Y",strtotime($value2->fecha));
                                    $detalle[] = $value->medico;
                                    $detalle[] = $value2->paciente;
                                    $detalle[] = $value2->comentario;
                                    $detalle[] = $value2->total;
                                    $detalle[] = $value2->responsable;
                                    $sheet->row($c,$cabecera);
                                    $c=$c+1;
                                }
                            }

                            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                                ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('person as medico','medico.id','=','movimiento.doctor_id')
                                ->where('movimiento.doctor_id', '=', $idmedico)
                                ->where('movimiento.situacion', 'like', 'N')
                                ->where('movimiento.tipodocumento_id','=','14')
                                ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                                ->select('movimiento.*',DB::raw('movimiento.nombrepaciente as paciente'),'responsable.nombres as responsable')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');

                            $lista2            = $resultado->get();
                            $egreso=0;
                            if(count($lista2)>0){
                                foreach ($lista2 as $key2 => $value2){
                                    $detalle = array();    
                                    $detalle[] = "EGRESOS";
                                    $detalle[] = date("d/m/Y",strtotime($value2->fecha));
                                    $detalle[] = $value->medico;
                                    $detalle[] = $value2->paciente;
                                    $detalle[] = $value2->comentario;
                                    $detalle[] = $value2->total;
                                    $detalle[] = $value2->responsable;
                                    $sheet->row($c,$cabecera);
                                    $c=$c+1;
                                }
                            }
                        }
                        $doctor=($value->medico);
                        $idmedico=$value->medico_id;
                    }
                    $detalle = array();
                    $detalle[] = "HONORARIOS";
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->medico;
                    $detalle[] = $value->paciente;
                    if($value->servicio_id>0){
                        $detalle[] = $value->servicio;
                    }else{
                        $detalle[] = $value->servicio2;
                    }
                    $detalle[] = number_format($value->pagodoctor*$value->cantidad,2,'.','');
                    $detalle[] = $value->responsable;
                    $detalle[] = $value->recibo;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }

                $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('person as medico','medico.id','=','movimiento.doctor_id')
                            ->where('movimiento.doctor_id', '=', $idmedico)
                            ->where('movimiento.situacion', 'like', 'N')
                            ->where('movimiento.tipodocumento_id','=','18')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                            ->select('movimiento.*',DB::raw('movimiento.nombrepaciente as paciente'),'responsable.nombres as responsable')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');

                $lista2            = $resultado->get();
                if(count($lista2)>0){
                    foreach ($lista2 as $key2 => $value2){
                        $detalle = array();    
                        $detalle[] = "RECIBOS";
                        $detalle[] = date("d/m/Y",strtotime($value2->fecha));
                        $detalle[] = $value->medico;
                        $detalle[] = $value2->paciente;
                        $detalle[] = $value2->comentario;
                        $detalle[] = $value2->total;
                        $detalle[] = $value2->responsable;
                        $sheet->row($c,$detalle);
                        $c=$c+1;
                    }
                }

                $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                    ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                    ->join('person as medico','medico.id','=','movimiento.doctor_id')
                    ->where('movimiento.doctor_id', '=', $idmedico)
                    ->where('movimiento.situacion', 'like', 'N')
                    ->where('movimiento.tipodocumento_id','=','14')
                    ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                    ->select('movimiento.*',DB::raw('movimiento.nombrepaciente as paciente'),'responsable.nombres as responsable')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');

                $lista2            = $resultado->get();
                $egreso=0;
                if(count($lista2)>0){
                    foreach ($lista2 as $key2 => $value2){
                        $detalle = array();    
                        $detalle[] = "EGRESOS";
                        $detalle[] = date("d/m/Y",strtotime($value2->fecha));
                        $detalle[] = $value->medico;
                        $detalle[] = $value2->paciente;
                        $detalle[] = $value2->comentario;
                        $detalle[] = $value2->total;
                        $detalle[] = $value2->responsable;
                        $sheet->row($c,$detalle);
                        $c=$c+1;
                    }
                }
            });
        })->export('xls');
    }
}
