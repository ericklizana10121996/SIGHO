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

class PrefacturaController extends Controller
{
    protected $folderview      = 'app.prefactura';
    protected $tituloAdmin     = 'Prefactura';
    protected $tituloRegistrar = 'Registrar Prefactura';
    protected $tituloModificar = 'Modificar Prefactura';
    protected $tituloEliminar  = 'Eliminar Prefactura';
    protected $rutas           = array('create' => 'ticket.create', 
            'edit'   => 'prefactura.edit', 
            'delete' => 'prefactura.eliminar',
            'search' => 'prefactura.buscar',
            'index'  => 'prefactura.index',
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
        $entidad          = 'Prefactura';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $descargado       = Libreria::getParam($request->input('descargado'));
        $anulado       = Libreria::getParam($request->input('anulado'));
        $servicio       = Libreria::getParam($request->input('servicio'));
        $tiposervicio_id  = Libreria::getParam($request->input('tiposervicio'));
        $user = Auth::user();

        $first1            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
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
        if($anulado!=""){
            $first1 = $first1->where('movimiento.anuladoprefac', 'like', ''.$anulado.'');
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
        if($tiposervicio_id!="0"){
            $first1 = $first1->where(DB::raw('\'-1\''),'=',$tiposervicio_id);
        }

        $first1            = $first1->select('movimiento.id','movimiento.tipo','movimiento.listapago','movimiento.fecha','movimiento.numero',DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('0 as servicio_id'),DB::raw('\'MEDICAMENTOS\' as servicio2'),DB::raw('\'MEDICAMENTOS\' as servicio'),'movimiento.total','historia.numero as historia',DB::raw('cast(conveniofarmacia.nombre as char(100)) as plan2'),'movimiento.copago','movimiento.tipodocumento_id',DB::raw('1 as cantidad'),DB::raw("movimiento.anuladoprefac as anulado"),DB::raw('-1 as tiposervicio_id'))->orderBy('movimiento.fecha', 'DESC')->orderBy('movimiento.numero','ASC');

        // dd($first1->get());

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->leftjoin('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('historia','historia.person_id','=','paciente.id')
                            /*->leftjoin('historia', function ($join) {
                                $join->on('historia.person_id','=','paciente.id')
                                     ->whereNotNull('historia.deleted_at');
                            })*/
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->whereIn('movimiento.plan_id',function($query){
                                $query->select('id')->from('plan')->where('tipopago','LIKE','Convenio');
                                })
                            ->whereNull('historia.deleted_at')
                            ->where('movimiento.numero','LIKE','%'.$numero.'%')
                            ->where('movimiento.tipodocumento_id','=','1')
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
        if($anulado!=""){
            $resultado = $resultado->where('dmc.anulado', 'like', ''.$anulado.'');
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
        $resultado        = $resultado->select('dmc.id',DB::raw('dmc.descargado as tipo'),DB::raw('dmc.observacion as listapago'),'movimiento.fecha','movimiento.numero',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'responsable.nombres as responsable','dmc.servicio_id','dmc.descripcion as servicio2','s.nombre as servicio',DB::raw('dmc.cantidad*dmc.precio as total'),'historia.numero as historia','plan.nombre as plan2',DB::raw('0 as copago'),'movimiento.tipodocumento_id','dmc.cantidad','dmc.anulado',DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else dmc.tiposervicio_id end as tiposervicio_id '))->orderBy('movimiento.fecha', 'DESC')->orderBy('movimiento.numero','ASC');
        $querySql = $resultado->unionAll($first1)->toSql();
        $binding  = $resultado->getBindings();
        // dd($querySql);
        
        $resultado = DB::table(DB::raw("($querySql) as a order by fecha desc, paciente2 asc"))->addBinding($binding);
        $lista            = $resultado->get();
        
        /*$listaSinNC = array();
        foreach ($lista as $key => $item) {
            if (strpos($item->listapago, "NC/") !== false) {

            }else{
                $listaSinNC[] = $item;
            }
        }
        $lista = $listaSinNC;*/
        // dd($lista);
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Plan', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cant.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio / Farmacia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado Descargado <input type="checkbox" id="chkDescargarTodos" onclick="cargarTodos(this.checked);">', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Observaciones', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Anular', 'numero' => '1');
        
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
        $entidad          = 'Prefactura';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboDescargado    = array("" => "Todos", "S" => "Si", "N" => "No");
        $cboAnulado    = array("" => "Todos", "S" => "Si", "N" => "No");
        $cboTipobusqueda    = array(1 => "Fecha Descargo", 2 => "Fecha Atencion");
        $cboTipoServicio = array();
        $cboTipoServicio = $cboTipoServicio + array(0 => '--Todos--');
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $cboTipoServicio = $cboTipoServicio + array(-1 => 'MEDICAMENTOS');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboDescargado', 'cboAnulado', 'cboTipobusqueda', 'cboTipoServicio'));
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
    
   	public function pdfComprobante(Request $request){
        $entidad          = 'Ticket';
        $id               = Libreria::getParam($request->input('ticket_id'),'');
        $resultado        = Movimiento::join('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->where('movimiento.movimiento_id', '=', $id);
        $resultado        = $resultado->select('movimiento.*','tipodocumento.nombre as tipodocumento');
        $lista            = $resultado->get();
        if (count($lista) > 0) {     
            foreach($lista as $key => $value){
                $pdf = new TCPDF();
                $pdf::SetTitle('Comprobante');
                $pdf::AddPage();
                $pdf::SetFont('helvetica','B',12);
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 15, 5, 115, 30);
                $pdf::Cell(60,7,utf8_encode("RUC N° 20480082673"),'RTL',0,'C');
                $pdf::Ln();
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Cell(60,7,utf8_encode($value->tipodocumento_id=='4'?"FACTURA":"BOLETA"),'RL',0,'C');
                $pdf::Ln();
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Cell(60,7,utf8_encode("ELECTRÓNICA"),'RL',0,'C');
                $pdf::Ln();
                $pdf::Cell(130,7,"",0,0,'C');
                if($value->tipodocumento_id=="4"){
                    $abreviatura="F";
                    $dni=$value->persona->ruc;
                    $subtotal=number_format($value->total/1.18,2,'.','');
                    $igv=number_format($value->total - $subtotal,2,'.','');
                }else{
                    $abreviatura="B";
                    $subtotal='0.00';
                    $igv='0.00';
                    if(strlen($value->persona->dni)<>8 || $value->total<750){
                        $dni='00000000';
                    }else{
                        $dni=$value->persona->dni;
                    }
                }
                $pdf::Cell(60,7,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),'RBL',0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(0,7,utf8_decode("HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA"),0,0,'L');
                $pdf::Ln();
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,utf8_encode("Nombre / Razón Social: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(110,6,(trim($value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,utf8_encode($abreviatura=="F"?"RUC :":"DNI".": "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,utf8_encode($dni),0,0,'L');
                $pdf::Ln();
                if($value->tipodocumento_id=="4"){
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(37,6,utf8_encode("Paciente: "),0,0,'L');
                    $pdf::SetFont('helvetica','',9);
                    $ticket = Movimiento::find($value->movimiento_id);
                    $pdf::Cell(110,6,(trim($ticket->persona->apellidopaterno." ".$ticket->persona->apellidomaterno." ".$ticket->persona->nombres)),0,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,utf8_encode("Dirección: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(110,6,(trim($value->persona->direccion)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,utf8_encode("Fecha de emisión: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,utf8_encode($value->fecha),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,utf8_encode("Moneda: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(40,6,(trim('PEN - Sol')),0,0,'L');
                $value2=Movimiento::find($id);
                $pdf::Cell(60,6,(trim($value2->plan->nombre)),0,0,'L');
                if($value2->tarjeta!="")
                    $pdf::Cell(60,6,trim($value2->tarjeta." - ".$value2->tipotarjeta),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(10,7,("Item"),1,0,'C');
                $pdf::Cell(13,7,utf8_encode("Código"),1,0,'C');
                $pdf::Cell(68,7,utf8_encode("Descripción"),1,0,'C');
                $pdf::Cell(10,7,("Und."),1,0,'C');
                $pdf::Cell(15,7,("Cantidad"),1,0,'C');
                $pdf::Cell(20,7,("V. Unitario"),1,0,'C');
                $pdf::Cell(20,7,("P. Unitario"),1,0,'C');
                $pdf::Cell(20,7,("Descuento"),1,0,'C');
                $pdf::Cell(20,7,("Sub Total"),1,0,'C');
                $pdf::Ln();
                $resultado        = Detallemovcaja::leftjoin('servicio', 'servicio.id', '=', 'detallemovcaja.servicio_id')
                            ->where('detallemovcaja.movimiento_id', '=', $id)
                            ->select('detallemovcaja.*');
                $lista2            = $resultado->get();
                $c=0;
                foreach($lista2 as $key2 => $v){$c=$c+1;
                    $pdf::SetFont('helvetica','',7.5);
                    $pdf::Cell(10,7,$c,1,0,'C');
                    if($v->servicio_id>"0"){
                        if($v->servicio->tipopago=="Convenio"){
                            $codigo=$v->servicio->tarifario->codigo;
                            $nombre=$v->servicio->tarifario->nombre;    
                        }else{
                            $codigo="-";
                            if($v->servicio_id>"0"){
                                $nombre=$v->servicio->nombre;
                            }else{
                                $nombre=trim($v->descripcion);
                            }
                        }
                    }else{
                        $codigo="-";
                        $nombre=trim($v->descripcion);
                    }
                    $nombre.=" - ".substr($v->persona->nombres,0,1)." ".$v->persona->apellidopaterno;
                    $pdf::Cell(13,7,$codigo,1,0,'C');
                    if(strlen($nombre)<50){
                        $pdf::Cell(68,7,utf8_encode($nombre),1,0,'L');
                    }else{
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(68,3.5,utf8_encode($nombre),1,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(68,7,"",1,0,'L');
                    }
                    $pdf::Cell(10,7,("ZZ."),1,0,'C');
                    $pdf::Cell(15,7,number_format($v->cantidad,2,'.',''),1,0,'R');
                    $pdf::Cell(20,7,number_format($v->pagohospital/1.18,2,'.',''),1,0,'R');
                    $pdf::Cell(20,7,number_format($v->pagohospital,2,'.',''),1,0,'R');
                    $pdf::Cell(20,7,("0.00"),1,0,'R');
                    $pdf::Cell(20,7,number_format($v->pagohospital*$v->cantidad,2,'.',''),1,0,'R');
                    $pdf::Ln();                    
                }
                $letras = new EnLetras();
                $pdf::SetFont('helvetica','B',8);
                $valor=$letras->ValorEnLetras($value->total, "NUEVOS SOLES" );//letras
                
                $pdf::Cell(116,5,utf8_decode($valor),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(40,5,utf8_decode('Op. Gravada'),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                $pdf::Cell(20,5,$subtotal,0,0,'R');
                $pdf::Ln();
                $pdf::Cell(116,5,'',0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(40,5,utf8_decode('I.G.V'),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                $pdf::Cell(20,5,$igv,0,0,'R');
                $pdf::Ln();
                $pdf::Cell(116,5,'',0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(40,5,utf8_decode('Op. Inafecta'),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                $pdf::Cell(20,5,'0.00',0,0,'R');
                $pdf::Ln();
                $pdf::Cell(116,5,'',0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(40,5,utf8_decode('Op. Exonerada'),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                $pdf::Cell(20,5,'0.00',0,0,'R');
                $pdf::Ln();
                $pdf::Cell(116,5,'',0,0,'L');
                $pdf::Cell(40,5,'',0,0,'L');
                $pdf::Cell(20,5,'',0,0,'C');
                $pdf::Cell(20,5,'----------------------',0,0,'R');
                $pdf::Ln();
                $pdf::Cell(116,5,'',0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(40,5,utf8_decode('Importe Total'),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                $pdf::Cell(20,5,number_format($value->total,2,'.',''),0,0,'R');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(195,5,'Observaciones de SUNAT:','LRT',0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(195,5,'','LRB',0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(0,5,utf8_encode('Autorizado a ser emisor electrónico mediante R.I. SUNAT Nº 101010101010'),0,0,'L');
                $pdf::Ln();
                $pdf::Cell(0,5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(0,5,utf8_encode('Representación Impresa de la Factura Electrónica, consulte en https://sfe.bizlinks.com.pe'),0,0,'L');
                $pdf::Ln();
                $pdf::Output('Comprobante.pdf');
            }
        }
    }

    public function pdfPrefactura(Request $request){
        $entidad          = 'Ticket';
        $id               = Libreria::getParam($request->input('ticket_id'),'');
        $resultado        = Movimiento::join('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->join('historia','historia.person_id','=','movimiento.persona_id')
                            ->where('movimiento.id', '=', $id);
        $resultado        = $resultado->select('movimiento.*','tipodocumento.nombre as tipodocumento','plan.aseguradora','plan.nombre as plan','historia.numero as historia');
        $lista            = $resultado->get();
        if (count($lista) > 0) {     
            foreach($lista as $key => $value){
                $pdf = new TCPDF();
                $pdf::SetTitle('Prefactura');
                $pdf::AddPage();
                $pdf::SetFont('helvetica','B',12);
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 45, 5, 115, 20);
                $pdf::Ln();
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(22,6,utf8_encode("Fecha: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(110,6,utf8_encode($value->fecha),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,6,utf8_encode("Nro. Ticket: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,utf8_encode($value->numero),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(22,6,utf8_encode("Paciente: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(110,6,(trim($value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,6,utf8_encode("Historia".": "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,utf8_encode($value->historia),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(22,6,utf8_encode("Plan: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                if($value->soat=="S"){
                    $pdf::Cell(110,6,(trim("SOAT")),0,0,'L');
                }else{
                    $pdf::Cell(110,6,(trim($value->plan)),0,0,'L');
                }
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,6,utf8_encode("Usuario: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,utf8_encode($value->responsable->nombres),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(22,6,utf8_encode("Aseguradora: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(110,6,(trim($value->aseguradora)),0,0,'L');
                $pdf::Ln();
                $val      = Detallemovcaja::leftjoin('servicio', 'servicio.id', '=', 'detallemovcaja.servicio_id')
                            ->where('detallemovcaja.movimiento_id', '=', $id)
                            ->select('detallemovcaja.*')->first();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(22,6,utf8_encode("Medico: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(110,6,(trim($val->persona->nombres." ".$val->persona->apellidopaterno." ".$val->persona->apellidomaterno)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(0,5,utf8_encode("ESTE REPORTE ES VALIDO PARA ATENCION DE PACIENTES DE CONVENIO,"),0,0,'C');
                $pdf::Ln();
                $pdf::Cell(0,5,utf8_encode("SE IMPRIME PARA PACIENTES EN ATENCIONES DE EMERGENCIA, Y PACIENTES QUE NO TIENEN COMPROB. DE PAGO"),0,0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(13,7,utf8_encode("Cant."),1,0,'C');
                $pdf::Cell(110,7,utf8_encode("Descripción"),1,0,'C');
                $pdf::Cell(35,7,utf8_encode("P. Unit"),1,0,'C');
                $pdf::Cell(35,7,utf8_encode("Empresa"),1,0,'C');
                $pdf::Ln();
                $resultado        = Detallemovcaja::leftjoin('servicio', 'servicio.id', '=', 'detallemovcaja.servicio_id')
                            ->where('detallemovcaja.movimiento_id', '=', $id)
                            ->select('detallemovcaja.*');
                $lista2            = $resultado->get();
                $c=0;
                foreach($lista2 as $key2 => $v){$c=$c+1;
                    $pdf::SetFont('helvetica','',7.5);
                    if($v->servicio_id>"0"){
                        if($v->servicio->tipopago=="Convenio"){
                            $codigo=$v->servicio->tarifario->codigo;
                            $nombre=$v->servicio->tarifario->nombre;    
                        }else{
                            $codigo="-";
                            if($v->servicio_id>"0"){
                                $nombre=$v->servicio->nombre;
                            }else{
                                $nombre=trim($v->descripcion);
                            }
                        }
                    }else{
                        $codigo="-";
                        $nombre=trim($v->descripcion);
                    }                    
                    $pdf::Cell(13,7,round($v->cantidad,0),1,0,'C');
                    if($codigo!="-")
                        $pdf::Cell(110,7,utf8_encode($codigo." - ".$nombre),1,0,'L');
                    else
                        $pdf::Cell(110,7,utf8_encode($nombre),1,0,'L');
                    if($v->servicio->tiposervicio_id==1){
                        $plan = Plan::find($value->plan_id);
                        $pdf::Cell(35,7,($plan->consulta),1,0,'C');
                        $precio=$plan->consulta;
                    }else{
                        $precio = $v->servicio->precio;
                        $pdf::Cell(35,7,($precio),1,0,'C');
                    }
                    $pdf::Cell(35,7,number_format($precio*$v->cantidad,2,'.',''),1,0,'C');
                    $pdf::Ln();                    
                }                
                $pdf::Ln();
                $pdf::Output('Prefactura.pdf');
            }
        }
    }

    public function cargado(Request $request)
    {
        // echo json_encode($request->get('array_mov'));
        // $arreglo = json_decode($request->get('array_mov'));
         
        $error = '';
        //  // dd($arreglo[0][1]);
         // echo count($arreglo);
        //  exit();
        
        $user = Auth::user();
        $arreglo = json_decode($request->get('array_mov'));
           
        // try{
            for($i=0; $i<count($arreglo);$i++) {           
                    if ($arreglo[$i][1] == "1") {
                        $movimiento = Movimiento::find($arreglo[$i][0]);
                        if(is_null($movimiento) == true){
                            $error.="Ocurrió un Error, No se Encontró Código ". $arreglo[$i][0].",";
                        }else{
                            $movimiento->tipo =  'S';//($request->input('check')=="true"?'S':'N');
                            // if($request->input('check')=="true"){
                            $movimiento->fechaentrega = date("Y-m-d");
                            $movimiento->usuarioentrega_id = $user->person_id;
                            // }
                            $movimiento->save();
                        }
                    }else{
                        if($arreglo[$i][1] == "0"){
                            $movimiento = Detallemovcaja::find($arreglo[$i][0]);
                            if (is_null($movimiento) == true) {
                                $error.="Ocurrió un Error, No se Encontró Código ". $arreglo[$i][0].",";
                            }else{
                             // $aux = $movimiento->descargado; 
                                $movimiento->descargado = 'S'; 
                                $movimiento->fechadescargo = date("Y-m-d");
                                $movimiento->usuariodescargo_id = $user->person_id;
                                $movimiento->save();
                            }
                     
                        }
                    }     
            }
        // }catch(ModelNotFoundException $e){
        //     $error = $e->getMessage();
        // }          


        // $error = DB::transaction(function() use($request,$user){
        //     $arreglo = json_decode($request->get('array_mov'));
             
        //     for($i=0; $i<count($arreglo);$i++) {           
        //         if ($arreglo[$i][1] == "1") {
        //             $movimiento = Movimiento::find($arreglo[$i][0]);
        //             $movimiento->tipo =  'S';//($request->input('check')=="true"?'S':'N');
        //             // if($request->input('check')=="true"){
        //             $movimiento->fechaentrega = date("Y-m-d");
        //             $movimiento->usuarioentrega_id = $user->person_id;
        //             // }
        //             $movimiento->save();
        //         }else{
        //             if($arreglo[$i][1] == "0"){
        //                 $movimiento = Detallemovcaja::find($arreglo[$i][0]);
        //                 $aux = $movimiento->descargado; 
        //                 // $movimiento->descargado = 'S'; 
        //                 // $movimiento->usuariodescargo_id = $user->person_id;
        //                 // $movimiento->save();
                 
        //             }
        //         }          
        //     }


        //     // if($request->input('tipo')=="1"){//farmacia
        //     //     $movimiento = Movimiento::find($request->input('id'));
        //     //     $movimiento->tipo = ($request->input('check')=="true"?'S':'N');
        //     //     if($request->input('check')=="true"){
        //     //         $movimiento->fechaentrega = date("Y-m-d");
        //     //         $movimiento->usuarioentrega_id = $user->person_id;
        //     //     }
        //     //     $movimiento->save();
        //     // }else{
        //     //     $movimiento = Detallemovcaja::find($request->input('id'));
        //     //     $movimiento->descargado = ($request->input('check')=="true"?'S':'N');
        //     //     if($request->input('check')=="true"){
        //     //         $movimiento->fechadescargo = date("Y-m-d");
        //     //         $movimiento->usuariodescargo_id = $user->person_id;
        //     //     }
        //     //     $movimiento->save();
        //     // }
        // });
        
         // return "Ok";
        return ($error=="") ? "OK" : $error;

    }
   

    public function anulado(Request $request)
    {
         
        $error = '';
        //  // dd($arreglo[0][1]);
         // echo count($arreglo);
        //  exit();
        
        $user = Auth::user();
        $arreglo = json_decode($request->get('array_mov'));
        // $aux = '';

        for($i=0; $i<count($arreglo);$i++) {           
            if ($arreglo[$i][1] == "1") {
                $movimiento = Movimiento::find($arreglo[$i][0]);
                if(is_null($movimiento) == true){
                    $error.="Ocurrió un Error, No se Encontró Código ". $arreglo[$i][0].",";
                }else{
                    $movimiento->anuladoprefac = 'S';
                    $movimiento->save();
                }
            }else{
                if($arreglo[$i][1] == "0"){
                    $movimiento = Detallemovcaja::find($arreglo[$i][0]);
                    if (is_null($movimiento) == true) {
                        $error.="Ocurrió un Error, No se Encontró Código ". $arreglo[$i][0].",";
                    }else{
                        // $aux = $movimiento->descargado; 
                        $movimiento->anulado = 'S';
                        $movimiento->save();
                    }
             
                }
            }     
        }



            // $user = Auth::user();
            // $error = DB::transaction(function() use($request,$user){
            //     if($request->input('tipo')=="1"){//farmacia
            //         $movimiento = Movimiento::find($request->input('id'));
            //         $movimiento->anuladoprefac = ($request->input('check')=="true"?'S':'N');
            //         $movimiento->save();
            //     }else{
            //         $movimiento = Detallemovcaja::find($request->input('id'));
            //         $movimiento->anulado = ($request->input('check')=="true"?'S':'N');
            //         $movimiento->save();
            //     }
            // });

        // return $aux;
        return ($error=="") ? "OK" : $error;
    }
    
    public function observacion(Request $request)
    {
        $error = DB::transaction(function() use($request){
            if($request->input('tipo')=="1"){//farmacia
                $movimiento = Movimiento::find($request->input('id'));
                $movimiento->listapago = $request->input('value');
                $movimiento->save();
            }else{
                $movimiento = Detallemovcaja::find($request->input('id'));
                $movimiento->observacion = $request->input('value');
                $movimiento->save();
            }
        });
        return is_null($error) ? "OK" : $error;
    }


    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $descargado       = Libreria::getParam($request->input('descargado'));
        $anulado       = Libreria::getParam($request->input('anulado'));
        $tipobusqueda       = Libreria::getParam($request->input('tipobusqueda'));
        $user = Auth::user();

        //->leftjoin('detallemovcaja','detallemovcaja.movimiento_id','=','movimiento.id')

        $first1            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('conveniofarmacia','conveniofarmacia.id','=','movimiento.conveniofarmacia_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->whereIn('movimiento.situacion',['N'/*,'A'*/])
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
        if($anulado!=""){
            $first1 = $first1->where('movimiento.anuladoprefac', 'like', ''.$anulado.'');
        }
        if($fechainicial!=""){
            $first1 = $first1->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first1 = $first1->where('movimiento.fecha','<=',$fechafinal);
        }

        $first1            = $first1->select('movimiento.id','movimiento.situacion','movimiento.tipo','movimiento.listapago','movimiento.fecha','movimiento.numero',DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('0 as observacion'),DB::raw('0 as servicio_id'),DB::raw('\'MEDICAMENTOS\' as servicio2'),DB::raw('\'MEDICAMENTOS\' as servicio'),'historia.numero as historia',DB::raw('cast(conveniofarmacia.nombre as char(100)) as plan2'),'movimiento.copago',DB::raw("movimiento.anuladoprefac as anulado"),'movimiento.tipodocumento_id')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');


        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('historia','historia.person_id','=','paciente.id')
                            ->whereIn('movimiento.plan_id',function($query){
                                $query->select('id')->from('plan')->where('tipopago','LIKE','Convenio');
                                })
                            ->where(DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipodocumento_id','=','1')
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
        if($anulado!=""){
            $resultado = $resultado->where('dmc.anulado', 'like', ''.$anulado.'');
        }
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fechainicial.'');
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fechafinal.'');
        }
        $resultado        = $resultado->select('movimiento.id','movimiento.situacion','dmc.descargado as tipo','movimiento.listapago','movimiento.fecha','movimiento.numero',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'responsable.nombres as responsable','dmc.observacion','dmc.servicio_id','dmc.descripcion as servicio2','s.nombre as servicio','historia.numero as historia','plan.nombre as plan2',DB::raw('0 as copago'),'dmc.anulado','movimiento.tipodocumento_id')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        $querySql = $resultado->unionAll($first1)->toSql();
        $binding  = $resultado->getBindings();
        $resultado = DB::table(DB::raw("($querySql) as a order by fecha asc,paciente2 asc"))->addBinding($binding);
        
        $lista            = $resultado->get();

        Excel::create('ExcelPrefactura', function($excel) use($lista,$request) {
 
            $excel->sheet('Prefactura', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha Atencion";
                $cabecera[] = "Nro";
                $cabecera[] = "Paciente";
                $cabecera[] = "Plan";
                $cabecera[] = "Servicio / Farmacia";
                $cabecera[] = "Usuario";
                $cabecera[] = "Historia";
                $cabecera[] = "Descargado";
                $cabecera[] = "Observacion";
                $cabecera[] = "Anulado";
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
                    $detalle[] = $value->responsable;
                    $detalle[] = $value->historia;
                    $detalle[] = $value->tipo=='S'?'SI':'NO';
                    if ($value->servicio2 == "MEDICAMENTOS") {
                        $detalle[] = $value->listapago;
                    } else {
                        $detalle[] = $value->observacion;
                    }
                    /*if ($value->situacion == "A") {
                        $detalle[] = $value->situacion;
                    }*/
                    $detalle[] = $value->anulado=='S'?'SI':'NO';
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
            });
        })->export('xls');
    }

    public function excelDiario(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $user = Auth::user();

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('person as responsable','responsable.id','=','dmc.usuariodescargo_id')
                            ->join('historia','historia.person_id','=','paciente.id')
                            ->whereIn('movimiento.plan_id',function($query){
                                $query->select('id')->from('plan')->where('tipopago','LIKE','Convenio');
                                })
                            ->where('movimiento.tipodocumento_id','=','1')
                            ->where('dmc.descargado', 'like', 'S')
                            ->whereNotIn('movimiento.situacion',['U','A']);
        if($fechainicial!=""){
            $resultado = $resultado->where('dmc.fechadescargo', '>=', ''.$fechainicial.'');
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('dmc.fechadescargo', '<=', ''.$fechafinal.'');
        }
        $resultado        = $resultado->select('movimiento.id','movimiento.tipo','movimiento.listapago','movimiento.fecha','movimiento.numero',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'responsable.nombres as responsable','dmc.servicio_id','dmc.descripcion as servicio2','s.nombre as servicio','historia.numero as historia','plan.nombre as plan2',DB::raw('0 as copago'),'movimiento.tipodocumento_id',DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else dmc.tiposervicio_id end as tiposervicio_id'))->orderBy(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'asc')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');
        
        $lista            = $resultado->get();

        Excel::create('ExcelReporteDiario', function($excel) use($lista,$request) {
 
            $excel->sheet('ReporteDiario', function($sheet) use($lista,$request) {
                $cabecera[] = "Nro.";
                $cabecera[] = "Paciente";
                $cabecera[] = "Convenio";
                $cabecera[] = "Fecha";
                $cabecera[] = "Servicio";
                $cabecera[] = "Usuario";
                $sheet->row(1,$cabecera);
                $c=2;$d=1;$band=true;

                foreach ($lista as $key => $value){
                    if($value->tiposervicio_id=="1" || $value->tiposervicio_id=="9"){
                        $detalle = array();
                        $detalle[] = $d;
                        $detalle[] = $value->paciente2;
                        $detalle[] = $value->plan2;
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        if($value->tiposervicio_id>0){
                            $tiposervicio = Tiposervicio::find($value->tiposervicio_id);
                            if($value->tiposervicio_id=="9"){
                                $detalle[] = $tiposervicio->nombre;
                            }else{
                                if($value->servicio_id>0)
                                    $detalle[] = $value->servicio;
                                else
                                    $detalle[] = $value->servicio2;
                            }
                        }else{
                            $detalle[] = $value->servicio;
                        }
                        $detalle[] = $value->responsable;
                        $sheet->row($c,$detalle);
                        $c=$c+1;
                        $d=$d+1;
                    }
                }
            });
        })->export('xls');
    }

    public function excelUsuario(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $tipobusqueda       = Libreria::getParam($request->input('tipobusqueda'));
        $user = Auth::user();

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('person as responsable','responsable.id','=','dmc.usuariodescargo_id')
                            ->join('historia','historia.person_id','=','paciente.id')
                            ->whereIn('movimiento.plan_id',function($query){
                                $query->select('id')->from('plan')->where('tipopago','LIKE','Convenio');
                                })
                            ->where('movimiento.tipodocumento_id','=','1')
                            ->where('dmc.descargado', 'like', 'S')
                            ->where('dmc.usuariodescargo_id', '=', $user->person_id)
                            ->whereNotIn('movimiento.situacion',['U','A']);
        if($tipobusqueda==1){
            $busqueda = 'dmc.fechadescargo';
        } else {
            $busqueda = 'movimiento.fecha';
        }
        if($fechainicial!=""){
            $resultado = $resultado->where($busqueda, '>=', ''.$fechainicial.'');
        }
        if($fechafinal!=""){
            $resultado = $resultado->where($busqueda, '<=', ''.$fechafinal.'');
        }
        $resultado        = $resultado->select('movimiento.id','movimiento.tipo','movimiento.listapago','movimiento.fecha','movimiento.numero',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'responsable.nombres as responsable','dmc.fechadescargo','dmc.servicio_id','dmc.descripcion as servicio2','s.nombre as servicio','historia.numero as historia','plan.nombre as plan2',DB::raw('0 as copago'),'movimiento.tipodocumento_id',DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else dmc.tiposervicio_id end as tiposervicio_id'))->orderBy(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'asc')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');
        
        $lista            = $resultado->get();

        Excel::create('ExcelReporteUsuario', function($excel) use($lista,$request) {
 
            $excel->sheet('ReporteUsuario', function($sheet) use($lista,$request) {
                $cabecera[] = "Nro.";
                $cabecera[] = "Paciente";
                $cabecera[] = "Convenio";
                $cabecera[] = "Fecha Atencion";
                $cabecera[] = "Fecha Descargo";
                $cabecera[] = "Servicio";
                $cabecera[] = "Usuario";
                $sheet->row(1,$cabecera);
                $c=2;$d=1;$band=true;

                foreach ($lista as $key => $value){
                    if($value->tiposervicio_id=="1" || $value->tiposervicio_id=="9"){
                        $detalle = array();
                        $detalle[] = $d;
                        $detalle[] = $value->paciente2;
                        $detalle[] = $value->plan2;
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        $detalle[] = date('d/m/Y',strtotime($value->fechadescargo));
                        if($value->tiposervicio_id>0){
                            $tiposervicio = Tiposervicio::find($value->tiposervicio_id);
                            if($value->tiposervicio_id=="9"){
                                //$detalle[] = $tiposervicio->nombre;
                                $detalle[] = $value->servicio;
                            }else{
                                if($value->servicio_id>0)
                                    $detalle[] = $value->servicio;
                                else
                                    $detalle[] = $value->servicio2;
                            }
                        }else{
                            $detalle[] = $value->servicio;
                        }
                        $detalle[] = $value->responsable;
                        $sheet->row($c,$detalle);
                        $c=$c+1;
                        $d=$d+1;
                    }
                }
            });
        })->export('xls');
    }
}
