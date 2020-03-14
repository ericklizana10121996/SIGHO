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
class ReportepagoconvenioController extends Controller
{
    protected $folderview      = 'app.reportepagoconvenio';
    protected $tituloAdmin     = 'Reporte de Pago Medicos Convenio';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Modificar Concepto de Pago';
    protected $tituloEliminar  = 'Eliminar Concepto de Pago';
    protected $rutas           = array('create' => 'reporteconvenio.create', 
            'edit'   => 'reporteconsulta.edit', 
            'delete' => 'reporteconsulta.eliminar',
            'search' => 'reportepagoconvenio.buscar',
            'index'  => 'reportepagoconvenio.index',
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
        $entidad          = 'Reportepagoconvenio';
        $paciente         = Libreria::getParam($request->input('paciente'));
        $doctor     = Libreria::getParam($request->input('doctor'));
        $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','dmc.usuarioentrega_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->whereNull('historia.deleted_at')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->where('plan.id','<>','6')
                            ->where('dmc.situacionentrega','like','E');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        $resultado        = $resultado->orderBy('medico.apellidopaterno','asc')->orderBy('medico.apellidomaterno','asc')->orderBy('medico.nombres','asc')->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','dmc.fechaentrega','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'));
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago Doc', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Plan', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Pago', 'numero' => '1');
        
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
        $entidad          = 'Reportepagoconvenio';
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
        $entidad             = 'Reportetomografia';
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
        $paciente         = Libreria::getParam($request->input('paciente'));
        $doctor     = Libreria::getParam($request->input('doctor'));
        $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','dmc.usuarioentrega_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->whereNull('historia.deleted_at')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->where('plan.id','<>','6')
                            ->where('dmc.situacionentrega','like','E');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        $resultado        = $resultado->orderBy('medico.apellidopaterno','asc')->orderBy('medico.apellidomaterno','asc')->orderBy('medico.nombres','asc')->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total',DB::raw('plan.nombre as plan'),'mref.tipomovimiento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','dmc.precioconvenio','dmc.precio','dmc.pagohospital','dmc.fechaentrega','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'));
        $lista            = $resultado->get();

        Excel::create('ExcelReporte', function($excel) use($lista,$request) {
 
            $excel->sheet('Reporte', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Pago Doc.";
                $cabecera[] = "Plan";
                $cabecera[] = "Doctor";
                $cabecera[] = "Cant.";
                $cabecera[] = "Servicio";
                $cabecera[] = "Usuario";
                $cabecera[] = "Fecha Pago";
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
                    if($value->pagodoctor>0){
                        $detalle[] = number_format(($value->pagodoctor)*$value->cantidad,2,'.','');
                    }else{
                        $mov = Movimiento::where('listapago','like','%'.$value->iddetalle.'%')->where('situacion','<>','A')->first();
                        if(empty($mov)){
                            $detalle[] = 0.00;
                        }else{
                            $d=explode(",",$mov->listapago);
                            $detalle[] = number_format($mov->total/count($d),2,'.','');
                        }
                    }
                    $detalle[] = $value->plan;
                    $detalle[] = $value->medico;
                    $detalle[] = round($value->cantidad,0);
                    if($value->servicio_id>0)
                        $detalle[] = $value->servicio;
                    else
                        $detalle[] = $value->servicio2;
                    $detalle[] = $value->responsable;
                    $detalle[] = date("d/m/Y",strtotime($value->fechaentrega));
                    $array[] = $detalle;
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }

}
