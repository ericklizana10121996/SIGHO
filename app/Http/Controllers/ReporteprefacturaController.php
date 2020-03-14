<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Convenio;
use App\Movimiento;
use App\Detallemovcaja;
use App\Person;
use App\Tiposervicio;
use App\Servicio;
use App\Plan;
use App\Detalleplan;
use App\Librerias\Libreria;
use App\Librerias\EnLetras;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;
use Excel;

class ReporteprefacturaController extends Controller
{
    protected $folderview      = 'app.reporteprefactura';
    protected $tituloAdmin     = 'Reporte Descargado';
    protected $tituloRegistrar = 'Registrar Prefactura';
    protected $tituloModificar = 'Modificar Prefactura';
    protected $tituloEliminar  = 'Eliminar Prefactura';
    protected $rutas           = array('create' => 'reporteprefactura.create', 
            'edit'   => 'prefactura.edit', 
            'delete' => 'prefactura.eliminar',
            'search' => 'reporteprefactura.buscar',
            'index'  => 'reporteprefactura.index',
            'pdfListar'  => 'prefactura.pdfListar',
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
        $entidad          = 'Reporteprefactura';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $descargado       = Libreria::getParam($request->input('descargado'));
        $servicio       = Libreria::getParam($request->input('servicio'));
        $doctor       = Libreria::getParam($request->input('doctor'));
        $tiposervicio_id  = Libreria::getParam($request->input('tiposervicio'));
        $user = Auth::user();

        /*$first1            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('conveniofarmacia','conveniofarmacia.id','=','movimiento.conveniofarmacia_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->whereNotIn('movimiento.situacion',['U','A'])
                            ->where('movimiento.tipomovimiento_id', '=', '4')
                            ->where('movimiento.ventafarmacia', '=', 'S')
                            ->whereNotNull('movimiento.conveniofarmacia_id')
                            ->where('movimiento.numero','LIKE','%'.$numero.'%')
                            ->where(DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end'),'like','%'.$paciente.'%')
                            ->whereIn('movimiento.tipodocumento_id',['4','5','15']);
        if($descargado!=""){
            if($descargado=='N'){
                $first1 = $first1->where(function($sql) use($descargado){
                                $sql->where('movimiento.tipo', 'like', ''.$descargado.'')
                                    ->orWhereNull('movimiento.tipo');
                                });
            }else{
                $first1 = $first1->where('movimiento.tipo', 'like', ''.$descargado.'');
            }
        }
        if($fechainicial!=""){
            $first1 = $first1->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first1 = $first1->where('movimiento.fecha','<=',$fechafinal);
        }
        if($servicio!=""){
            $first1 = $first1->where(DB::raw('\'MEDICAMENTOS\''), 'like', '%'.$servicio.'%');   
        }

        $first1            = $first1->select('movimiento.id','movimiento.tipo','movimiento.listapago','movimiento.fecha','movimiento.numero',DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('0 as servicio_id'),DB::raw('\'MEDICAMENTOS\' as servicio2'),DB::raw('\'MEDICAMENTOS\' as servicio'),'movimiento.total','historia.numero as historia',DB::raw('cast(conveniofarmacia.nombre as char(100)) as plan2'),'movimiento.copago','movimiento.tipodocumento_id',DB::raw('1 as cantidad'))->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');
*/

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->leftjoin('detallemovcaja as dmc2','dmc2.id','=','dmc.movimientodescargo_id')
                            ->leftjoin('movimiento as m2','m2.id','=','dmc2.movimiento_id')
                            ->leftjoin('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('person as responsable','responsable.id','=','dmc.usuariodescargo_id')
                            ->leftjoin('person as doctor','doctor.id','=','dmc.persona_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'), 'LIKE', '%'.strtoupper($doctor).'%')
                            ->whereIn('movimiento.plan_id',function($query){
                                $query->select('id')->from('plan')->where('tipopago','LIKE','Convenio');
                                })
                            ->where('movimiento.numero','LIKE','%'.$numero.'%')
                            ->where('movimiento.tipodocumento_id','=','1')
                            ->where('dmc.anulado', '=', 'N')
                            ->whereNull('dmc.deleted_at')
                            ->whereNotIn('movimiento.situacion',['U','A']);
        if($descargado!=""){
            if($descargado=='N'){
                $resultado = $resultado->where(function($sql) use($descargado){
                                    $sql->where('dmc.descargado', 'like', ''.$descargado.'')
                                        ->orWhereNull('dmc.descargado');
                                });
            }else{
                $resultado = $resultado->where('dmc.descargado', 'like', ''.$descargado.'');
            }
        }
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fechainicial.'');
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fechafinal.'');
        }
        if($servicio!=""){
            $resultado = $resultado->where(DB::raw('case when dmc.servicio_id>0 then s.nombre else dmc.descripcion end'), 'like', '%'.$servicio.'%');   
        }
        if($tiposervicio_id!="0"){
            $resultado = $resultado->where(DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else dmc.tiposervicio_id end'),'=',$tiposervicio_id);
        }
        $resultado        = $resultado->select('dmc.id',DB::raw('dmc.descargado as tipo'),DB::raw('dmc.observacion as listapago'),'movimiento.fecha','movimiento.numero',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'responsable.nombres as responsable','dmc.servicio_id','dmc.descripcion as servicio2','s.nombre as servicio',DB::raw('dmc.cantidad*dmc.precio as total'),'plan.nombre as plan2',DB::raw('0 as copago'),'movimiento.tipodocumento_id','dmc.cantidad',DB::raw('case when dmc.movimientodescargo_id>0 and dmc.movimientodescargo_id is not null then (select concat(serie,\'-\',numero) from movimiento where id=dmc2.movimiento_id) else \' \' end as numero2'),DB::raw('case when dmc.movimientodescargo_id>0 and dmc.movimientodescargo_id is not null then (select fecha from movimiento where id=dmc2.movimiento_id) else \'\' end as fecha2'),DB::raw('case when dmc.movimientodescargo_id>0 and dmc.movimientodescargo_id is not null then (select fechaentrega from movimiento where id=dmc2.movimiento_id) else \'\' end as fechaentrega2'),DB::raw('case when dmc.movimientodescargo_id>0 and dmc.movimientodescargo_id is not null then (select voucher from movimiento where id=dmc2.movimiento_id) else \'\' end as voucher2'),'dmc2.id as id2',DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres) as doctor'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        /*$querySql = $resultado->unionAll($first1)->toSql();
        $binding  = $resultado->getBindings();
        $resultado = DB::table(DB::raw("($querySql) as a order by paciente2 asc,fecha desc"))->addBinding($binding);*/
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Plan', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cant.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio / Farmacia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Factura', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Factura', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Ope', 'numero' => '1');
        
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'conf'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad','conf'));
    }

    public function index()
    {
        $entidad          = 'Reporteprefactura';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboDescargado    = array("" => "Todos", "S" => "Si", "N" => "No");
        $cboTipobusqueda    = array(1 => "Fecha Descargo", 2 => "Fecha Atencion");
        $cboTipoServicio = array();
        $cboTipoServicio = $cboTipoServicio + array(0 => '--Todos--');
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboDescargado', 'cboTipobusqueda', 'cboTipoServicio'));
    }

    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Prefactura';
        $ticket = null;
        $cboConvenio = array();
        $convenios = Convenio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($convenios as $key => $value) {
            $cboConvenio = $cboConvenio + array($value->id => $value->nombre);
        }
        $cboTipoServicio = array(""=>"--Todos--");
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $formData            = array('ticket.store');
        $cboTipoPaciente     = array("Convenio" => "Convenio", "Particular" => "Particular", "Hospital" => "Hospital");
        $cboTipoDocumento     = array("Boleta" => "Boleta", "Factura" => "Factura");
        $cboFormaPago     = array("Efectivo" => "Efectivo", "Tarjeta" => "Tarjeta");
        $cboTipoTarjeta    = array("VISA" => "VISA", "MASTER" => "MASTER");
        $cboTipoTarjeta2    = array("CREDITO" => "CREDITO", "DEBITO" => "DEBITO");        
        $cboCaja = array();
        $rs = Caja::where('nombre','<>','FARMACIA')->orderBy('nombre','ASC')->get();
        $idcaja=0;
        foreach ($rs as $key => $value) {
            $cboCaja = $cboCaja + array($value->id => $value->nombre);
            if($request->ip()==$value->ip){
                $idcaja=$value->id;
                $serie=$value->serie;
            }
        }
        if($idcaja==0){//ADMISION 1
            $serie=3;
            $idcaja=1;
        }
        //print_r($request->ip());
        $numero = Movimiento::NumeroSigue(1);
        $user = Auth::user();

        $numeroventa = Movimiento::NumeroSigue(4,5,$serie);
        $serie='00'.$serie;
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('ticket', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente', 'cboConvenio', 'cboTipoDocumento', 'cboFormaPago', 'cboTipoTarjeta', 'cboTipoServicio', 'cboTipoTarjeta2', 'numero', 'cboCaja', 'numeroventa','serie','idcaja'));
    }

    public function show($id)
    {
        //
    }

    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'Ticket');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $Ticket = Ticket::find($id);
        $entidad             = 'Ticket';
        $formData            = array('Ticket.update', $id);
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio");
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('Ticket', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente'));
    }

    public function update(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'Ticket');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'nombre'                  => 'required|max:100',
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
            $categoria->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function destroy($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Ticket = Movimiento::find($id);
            $Ticket->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
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
        $entidad  = 'Ticket';
        $formData = array('route' => array('ticket.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $servicio       = Libreria::getParam($request->input('servicio'));
        $descargado       = Libreria::getParam($request->input('descargado'));
        $servicio       = Libreria::getParam($request->input('servicio'));
        $tiposervicio_id  = Libreria::getParam($request->input('tiposervicio'));
        $doctor       = Libreria::getParam($request->input('doctor'));
        $user = Auth::user();

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->leftjoin('detallemovcaja as dmc2','dmc2.id','=','dmc.movimientodescargo_id')
                            ->leftjoin('movimiento as m2','m2.id','=','dmc2.movimiento_id')
                            ->leftjoin('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('person as responsable','responsable.id','=','dmc.usuariodescargo_id')
                            ->leftjoin('person as doctor','doctor.id','=','dmc.persona_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'), 'LIKE', '%'.strtoupper($doctor).'%')
                            ->whereIn('movimiento.plan_id',function($query){
                                $query->select('id')->from('plan')->where('tipopago','LIKE','Convenio');
                                })
                            ->where('movimiento.numero','LIKE','%'.$numero.'%')
                            ->where('movimiento.tipodocumento_id','=','1')
                            ->where('dmc.anulado', '=', 'N')
                            ->whereNull('dmc.deleted_at')
                            ->whereNotIn('movimiento.situacion',['U','A']);
        if($descargado!=""){
            if($descargado=='N'){
                $resultado = $resultado->where(function($sql) use($descargado){
                                    $sql->where('dmc.descargado', 'like', ''.$descargado.'')
                                        ->orWhereNull('dmc.descargado');
                                });
            }else{
                $resultado = $resultado->where('dmc.descargado', 'like', ''.$descargado.'');
            }
        }
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fechainicial.'');
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fechafinal.'');
        }
        if($servicio!=""){
            $resultado = $resultado->where(DB::raw('case when dmc.servicio_id>0 then s.nombre else dmc.descripcion end'), 'like', '%'.$servicio.'%');   
        }
        if($tiposervicio_id!="0"){
            $resultado = $resultado->where(DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else dmc.tiposervicio_id end'),'=',$tiposervicio_id);
        }
        $resultado        = $resultado->select('dmc.id',DB::raw('dmc.descargado as tipo'),DB::raw('dmc.observacion as listapago'),'movimiento.fecha','movimiento.numero',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'responsable.nombres as responsable','dmc.servicio_id','dmc.descripcion as servicio2','s.nombre as servicio',DB::raw('dmc.cantidad*dmc.precio as total'),'plan.nombre as plan2',DB::raw('0 as copago'),'movimiento.tipodocumento_id','dmc.cantidad',DB::raw('case when dmc.movimientodescargo_id>0 and dmc.movimientodescargo_id is not null then (select concat(serie,\'-\',numero) from movimiento where id=dmc2.movimiento_id) else \' \' end as numero2'),DB::raw('case when dmc.movimientodescargo_id>0 and dmc.movimientodescargo_id is not null then (select fecha from movimiento where id=dmc2.movimiento_id) else \'\' end as fecha2'),DB::raw('case when dmc.movimientodescargo_id>0 and dmc.movimientodescargo_id is not null then (select fechaentrega from movimiento where id=dmc2.movimiento_id) else \'\' end as fechaentrega2'),DB::raw('case when dmc.movimientodescargo_id>0 and dmc.movimientodescargo_id is not null then (select voucher from movimiento where id=dmc2.movimiento_id) else \'\' end as voucher2'),'dmc2.id as id2',DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres) as doctor'),'dmc.descargado')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        
        
        $lista            = $resultado->get();

        Excel::create('ExcelDescargado', function($excel) use($lista,$request) {
 
            $excel->sheet('Descargado', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha Atencion";
                $cabecera[] = "Nro";
                $cabecera[] = "Paciente";
                $cabecera[] = "Plan";
                $cabecera[] = "Servicio";
                $cabecera[] = "Doctor";
                $cabecera[] = "Descargado";
                $cabecera[] = "Usuario";
                $cabecera[] = "Factura";
                $cabecera[] = "Fecha Factura";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Nro Ope";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;$mes="";

                foreach ($lista as $key => $value){
                    if($mes!=date('m/Y',strtotime($value->fecha))){
                        $detalle = array();
                        if(date('m',strtotime($value->fecha))=="01") $mes="ENERO";
                        if(date('m',strtotime($value->fecha))=="02") $mes="FEBRERO";
                        if(date("m",strtotime($value->fecha))=="03") $mes="MARZO";
                        if(date("m",strtotime($value->fecha))=="04") $mes="ABRIL";
                        if(date("m",strtotime($value->fecha))=="05") $mes="MAYO";
                        if(date("m",strtotime($value->fecha))=="06") $mes="JUNIO";
                        if(date("m",strtotime($value->fecha))=="07") $mes="JULIO";
                        if(date("m",strtotime($value->fecha))=="08") $mes="AGOSTO";
                        if(date("m",strtotime($value->fecha))=="09") $mes="SETIEMBRE";
                        if(date("m",strtotime($value->fecha))=="10") $mes="OCTUBRE";
                        if(date("m",strtotime($value->fecha))=="11") $mes="NOVIEMBRE";
                        if(date("m",strtotime($value->fecha))=="12") $mes="DICIEMBRE";
                        $detalle[] = $mes.' - '.date('Y',strtotime($value->fecha));
                        $sheet->row($c,$detalle);
                        $c++;
                        $mes=date('m/Y',strtotime($value->fecha));
                    }
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->numero;
                    $detalle[] = $value->paciente2;
                    $detalle[] = $value->plan2;
                    if($value->servicio_id>0)
                        $detalle[] = $value->servicio;
                    else
                        $detalle[] = $value->servicio2;
                    $detalle[] = $value->doctor;
                    $detalle[] = $value->descargado;
                    $detalle[] = $value->responsable;
                    $detalle[] = $value->numero2;
                    if($value->fecha2!="")
                        $detalle[] = date('d/m/Y',strtotime($value->fecha2));
                    else
                        $detalle[] = '';
                    if($value->fechaentrega2!="")
                        $detalle[] = date('d/m/Y',strtotime($value->fechaentrega2));
                    else
                        $detalle[] ='-';
                    $detalle[] = $value->voucher2;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
            });
        })->export('xls');
    }


}
