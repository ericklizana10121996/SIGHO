<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Conceptopago;
use App\Movimiento;
use App\Tiposervicio;
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
class ReportefacturacionController extends Controller
{
    protected $folderview      = 'app.reportefacturacion';
    protected $tituloAdmin     = 'Reporte de Pacientes de Facturacion';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Modificar Concepto de Pago';
    protected $tituloEliminar  = 'Eliminar Concepto de Pago';
    protected $rutas           = array('create' => 'reportefacturacion.create', 
            'edit'   => 'reporteconsulta.edit', 
            'delete' => 'reporteconsulta.eliminar',
            'search' => 'reportefacturacion.buscar',
            'index'  => 'reportefacturacionreportefacturacion.index',
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Reportefacturacion';
        $paciente         = Libreria::getParam($request->input('paciente'));
        $servicio     = Libreria::getParam($request->input('servicio'));
        $doctor     = Libreria::getParam($request->input('doctor'));
        $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial   = Libreria::getParam($request->input('fechainicial'));
        $fechafinal     = Libreria::getParam($request->input('fechafinal'));
        $usuario        = Libreria::getParam($request->input('usuario'));
        $tiposervicio   = Libreria::getParam($request->input('tiposervicio'));
        $resultado      = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where('plan.nombre','like','%'.$plan.'%')
                            ->whereNull('historia.deleted_at')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->whereNotIn('movimiento.situacion',['U','A']);
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($servicio!=""){
            //$resultado = $resultado->where(DB::raw('case when dmc.servicio_id>0 then s.nombre else dmc.descripcion end'),'like','%'.$servicio.'%');   
            $resultado = $resultado->where(function($q) use ($servicio){
                $q->where('s.nombre','like','%'.$servicio.'%')
                    ->orWhere('dmc.descripcion','like','%'.$servicio.'%');
            });
        }
        if($tiposervicio!="0"){
            $resultado = $resultado->where(DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else 0 end'),'=',$tiposervicio);
        }
        if($doctor!=""){
            $resultado = $resultado->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%');   
        }
        if($usuario!=""){
            $resultado = $resultado->where(DB::raw('concat(responsable.apellidopaterno,\' \',responsable.apellidomaterno,\' \',responsable.nombres)'),'like','%'.$usuario.'%');   
        }
        $resultado        = $resultado->orderBy(DB::raw('movimiento.fecha'), 'desc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.fechaentrega','movimiento.voucher');
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
        $cabecera[]       = array('valor' => 'Fecha Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Ope.', 'numero' => '1');
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
        $entidad          = 'Reportefacturacion';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoServicio = array();
        $cboTipoServicio = $cboTipoServicio + array(0 => '--Todos--');
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $cboTipoPaciente          = array("" => "Todos", "P" => "Particular", "C" => "Convenio");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoPaciente', 'cboTipoServicio'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Reportefacturacion';
        $conceptopago = null;
        $formData            = array('reportefacturacion.store');
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
        $paciente         = Libreria::getParam($request->input('paciente'));
        $usuario         = Libreria::getParam($request->input('usuario'));
        $servicio     = Libreria::getParam($request->input('servicio'));
        $doctor     = Libreria::getParam($request->input('doctor'));
        $tiposervicio_id  = Libreria::getParam($request->input('tiposervicio_id'));
        $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial   = Libreria::getParam($request->input('fechainicial'));
        $fechafinal     = Libreria::getParam($request->input('fechafinal'));
        $tiposervicio   = Libreria::getParam($request->input('tiposervicio'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('especialidad as es','es.id','=','medico.especialidad_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('tarifario as ta','ta.id','=','s.tarifario_id')
                            ->leftjoin('cie as cie','cie.id','=','movimiento.cie_id')
                            ->leftjoin('tiposervicio as ts','ts.id','=','s.tiposervicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as empresa','empresa.id','=','movimiento.empresa_id')
                            ->where('plan.nombre','like','%'.$plan.'%')
                            ->whereNull('historia.deleted_at')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->where('movimiento.situacion','<>','U')
                            ->where('movimiento.situacion','<>','A');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
       
        if($servicio!=""){
            //$resultado = $resultado->where(DB::raw('case when dmc.servicio_id>0 then s.nombre else dmc.descripcion end'),'like','%'.$servicio.'%');   
            $resultado = $resultado->where(function($q) use ($servicio){
                $q->where('s.nombre','like','%'.$servicio.'%')
                    ->orWhere('dmc.descripcion','like','%'.$servicio.'%');
            });
        }
        // if($servicio!=""){
        //     $resultado = $resultado->where(DB::raw('case when dmc.servicio_id>0 then s.nombre else dmc.descripcion end'),'like','%'.$servicio.'%');   
        // }
        if($doctor!=""){
            $resultado = $resultado->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%');   
        }
        if($tiposervicio!="0"){
            $resultado = $resultado->where(DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else 0 end'),'=',$tiposervicio);
        }
        if($usuario!=""){
            $resultado = $resultado->where(DB::raw('concat(responsable.apellidopaterno,\' \',responsable.apellidomaterno,\' \',responsable.nombres)'),'like','%'.$usuario.'%');   
        }
        $resultado        = $resultado->orderBy(DB::raw('movimiento.fechaingreso'), 'asc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','s.tarifario_id','ta.codigo','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.dni',DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'empresa.bussinesname','empresa.direccion','empresa.ruc','movimiento.comentario','movimiento.fechaingreso','movimiento.fechaalta','ts.nombre as tiposervicio','cie.descripcion as cie','cie.codigo as codigocie','movimiento.soat','es.nombre as especialidad','movimiento.fechaentrega','movimiento.voucher','movimiento.montoinicial','movimiento.igv','movimiento.copago','s.tiposervicio_id');
        $lista            = $resultado->get();

        Excel::create('ExcelReporte', function($excel) use($lista,$request) {
 
            $excel->sheet('Reporte', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Serie";
                $cabecera[] = "Numero";
                $cabecera[] = "Siniestro";
                $cabecera[] = "Historia";
                $cabecera[] = "Paciente";
                $cabecera[] = "DNI";
                $cabecera[] = "Cliente";
                $cabecera[] = "Direccion";
                $cabecera[] = "RUC";
                $cabecera[] = "Cod.";
                $cabecera[] = "Servicio";
                $cabecera[] = "Medico";
                $cabecera[] = "Precio";
                $cabecera[] = "Cant";
                $cabecera[] = "Total";
                $cabecera[] = "Fecha Ingreso";
                $cabecera[] = "Fecha Alta";
                $cabecera[] = "Fecha Entrega Medicina";
                $cabecera[] = "Fecha Atencion";
                $cabecera[] = "Tipo";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Nro Ope";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "F".$value->serie;
                    $detalle[] = $value->numero;
                    $detalle[] = $value->comentario;
                    $detalle[] = $value->historia;
                    $detalle[] = $value->paciente2;
                    $detalle[] = $value->dni;
                    $detalle[] = $value->bussinesname;
                    $detalle[] = $value->direccion;
                    $detalle[] = $value->ruc;
                    if($value->tarifario_id>0){
                        $detalle[] = $value->codigo;
                    }else{
                        $detalle[] = $value->codigo;
                    }
                    $detalle[] = $value->servicio2;
                    $detalle[] = $value->medico;
                    $nombre=$value->servicio2;
                    $tiposervicio_id=$value->tiposervicio_id;
                    if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $tiposervicio_id!="1") {
                        $precio=number_format($value->precio*100/(100-$value->montoinicial),2,'.','');
                    } else {
                        $precio=$value->precio;
                    }
                    if($value->igv>0){
                        if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $tiposervicio_id!="1") {
                            $precio=number_format($precio/1.18,2,'.','');
                        }else{
                            $precio=number_format($value->copago+round($precio/1.18,2),2,'.','');
                        }
                    }
                    $detalle[] = number_format($precio,2,'.','');
                    $detalle[] = round($value->cantidad,0);
                    $detalle[] = number_format($precio*$value->cantidad,2,'.','');
                    $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                    $detalle[] = date('d/m/Y',strtotime($value->fechaalta));
                    $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                    $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                    $detalle[] = $value->soat=='S'?'SOAT':'AMBULATORIO';
                    if($value->fechaentrega!=""){
                        $detalle[] = date("d/m/Y",strtotime($value->fechaentrega));
                    }else{
                        $detalle[] = "";
                    }
                    $detalle[] = $value->voucher;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
            });
        })->export('xls');
    }

    public function excelsincoaseguro(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $usuario         = Libreria::getParam($request->input('usuario'));
        $servicio     = Libreria::getParam($request->input('servicio'));
        $doctor     = Libreria::getParam($request->input('doctor'));
        $tiposervicio_id  = Libreria::getParam($request->input('tiposervicio_id'));
        $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial   = Libreria::getParam($request->input('fechainicial'));
        $fechafinal     = Libreria::getParam($request->input('fechafinal'));
        $tiposervicio   = Libreria::getParam($request->input('tiposervicio'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('especialidad as es','es.id','=','medico.especialidad_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('tarifario as ta','ta.id','=','s.tarifario_id')
                            ->leftjoin('cie as cie','cie.id','=','movimiento.cie_id')
                            ->leftjoin('tiposervicio as ts','ts.id','=','s.tiposervicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as empresa','empresa.id','=','movimiento.empresa_id')
                            ->where('plan.nombre','like','%'.$plan.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->where('movimiento.situacion','<>','U')
                            ->where('movimiento.situacion','<>','A');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($servicio!=""){
            $resultado = $resultado->where(DB::raw('case when dmc.servicio_id>0 then s.nombre else dmc.descripcion end'),'like','%'.$servicio.'%');   
        }
        if($doctor!=""){
            $resultado = $resultado->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%');   
        }
        if($tiposervicio!="0"){
            $resultado = $resultado->where(DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else 0 end'),'=',$tiposervicio);
        }
        if($usuario!=""){
            $resultado = $resultado->where(DB::raw('concat(responsable.apellidopaterno,\' \',responsable.apellidomaterno,\' \',responsable.nombres)'),'like','%'.$usuario.'%');   
        }
        $resultado        = $resultado->orderBy(DB::raw('movimiento.fecha'), 'desc')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','s.tarifario_id','ta.codigo','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.dni',DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'empresa.bussinesname','empresa.direccion','empresa.ruc','movimiento.comentario','movimiento.fechaingreso','movimiento.fechaalta','ts.nombre as tiposervicio','cie.descripcion as cie','cie.codigo as codigocie','movimiento.soat','es.nombre as especialidad','movimiento.fechaentrega','movimiento.voucher','movimiento.montoinicial','movimiento.igv','movimiento.copago','s.tiposervicio_id');
        $lista            = $resultado->get();

        Excel::create('ExcelReporte', function($excel) use($lista,$request) {
 
            $excel->sheet('Reporte', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Serie";
                $cabecera[] = "Numero";
                $cabecera[] = "Siniestro";
                $cabecera[] = "Historia";
                $cabecera[] = "Paciente";
                $cabecera[] = "DNI";
                $cabecera[] = "Cliente";
                $cabecera[] = "Direccion";
                $cabecera[] = "RUC";
                $cabecera[] = "Cod.";
                $cabecera[] = "Servicio";
                $cabecera[] = "Medico";
                $cabecera[] = "Precio";
                $cabecera[] = "Cant";
                $cabecera[] = "Total";
                $cabecera[] = "Fecha Ingreso";
                $cabecera[] = "Fecha Alta";
                $cabecera[] = "Fecha Entrega Medicina";
                $cabecera[] = "Fecha Atencion";
                $cabecera[] = "Tipo";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Nro Ope";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "F".$value->serie;
                    $detalle[] = $value->numero;
                    $detalle[] = $value->comentario;
                    $detalle[] = $value->historia;
                    $detalle[] = $value->paciente2;
                    $detalle[] = $value->dni;
                    $detalle[] = $value->bussinesname;
                    $detalle[] = $value->direccion;
                    $detalle[] = $value->ruc;
                    if($value->tarifario_id>0){
                        $detalle[] = $value->codigo;
                    }else{
                        $detalle[] = $value->codigo;
                    }
                    $detalle[] = $value->servicio2;
                    $detalle[] = $value->medico;
                    $nombre=$value->servicio2;
                    $tiposervicio_id=$value->tiposervicio_id;
                    if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $tiposervicio_id!="1") {
                        //$precio=number_format($value->precio*100/(100-$value->montoinicial),2,'.','');
                        $precio=number_format($value->precio*100/(100),2,'.','');
                    } else {
                        $precio=$value->precio;
                    }
                    if($value->igv>0){
                        if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $tiposervicio_id!="1") {
                            $precio=number_format($precio/1.18,2,'.','');
                        }else{
                            $precio=number_format($value->copago+round($precio/1.18,2),2,'.','');
                        }
                    }
                    $detalle[] = number_format($precio,2,'.','');
                    $detalle[] = round($value->cantidad,0);
                    $detalle[] = number_format($precio*$value->cantidad,2,'.','');
                    $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                    $detalle[] = date('d/m/Y',strtotime($value->fechaalta));
                    $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                    $detalle[] = date('d/m/Y',strtotime($value->fechaingreso));
                    $detalle[] = $value->soat=='S'?'SOAT':'AMBULATORIO';
                    if($value->fechaentrega!=""){
                        $detalle[] = date("d/m/Y",strtotime($value->fechaentrega));
                    }else{
                        $detalle[] = "";
                    }
                    $detalle[] = $value->voucher;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
            });
        })->export('xls');
    }

}
