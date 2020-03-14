<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Convenio;
use App\Caja;
use App\Person;
use App\Venta;
use App\Servicio;
use App\Movimiento;
use App\conveniofarmacia;
use App\Tipodocumento;
use App\Conceptopago;
use App\Detallemovcaja;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;

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

class CajaController extends Controller
{
    protected $folderview      = 'app.caja';
    protected $tituloAdmin     = 'Caja';
    protected $tituloRegistrar = 'Registrar Movimiento de Caja';
    protected $tituloModificar = 'Modificar Caja';
    protected $tituloEliminar  = 'Eliminar Caja';
    protected $rutas           = array('create' => 'caja.create', 
            'edit'   => 'caja.edit', 
            'delete' => 'caja.eliminar',
            'search' => 'caja.buscar',
            'buscarcontrol' => 'caja.buscarcontrol',
            'index'  => 'caja.index',
            'pdfListar'  => 'caja.pdfListar',
            'apertura' => 'caja.apertura',
            'cierre' => 'caja.cierre',
            'acept' => 'caja.acept',
            'reject' => 'caja.reject',
            'imprimir' => 'caja.imprimir',
            'descarga' => 'caja.descarga',
            'control' => 'caja.control'
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Caja';
        $caja_id          = Libreria::getParam($request->input('caja_id'),'1');
        $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->orderBy('movimiento.id','DESC')->limit(1)->first();
        if(count($rst)==0){
            $conceptopago_id=2;
        }else{
            $conceptopago_id=$rst->conceptopago_id;
        }

        // dd($rst);

        $titulo_registrar = $this->tituloRegistrar;
        $titulo_apertura  = 'Apertura';
        $titulo_cierre    = 'Cierre'; 
        
        $rst              = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',1)->orderBy('id','DESC')->limit(1)->first();
        if(count($rst)>0){
            $movimiento_mayor = $rst->id;    
        }else{
            $movimiento_mayor = 0;
        }
        
        // dd($movimiento_mayor);

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','movimiento.id','=','m2.movimiento_id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            ->whereNull('movimiento.cajaapertura_id')
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            //->whereNotIn('movimiento.id', [359022,359023])//TRANSFERENCIA POR CHEQUE MANUAL DIA 09/01/19, USUARIO NELLY MONTEZA
                            ->where(function($query){
                                $query
                                    ->whereNotIn('movimiento.conceptopago_id',[15, 17, 19, 21, 32])
                                    ->orWhere('movimiento.situacion','<>','R');
                            })
                            ;
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2',DB::raw('CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) as paciente'),DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.id', 'desc');
        $lista            = $resultado->get();

        // dd($lista);

        $listapendiente = array();

        if ($caja_id == 4) {
            $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','movimiento.id','=','m2.movimiento_id')
                            ->where('movimiento.serie', '=', $caja_id)
                            ->where('movimiento.estadopago', '=', 'PP')
                            ->where('movimiento.id', '>=', $movimiento_mayor);
            $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2',DB::raw('CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) as paciente'),DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.id', 'desc');
            $listapendiente            = $resultado2->get();
        }
        

        
        $cabecera         = array();
        //$cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Numero', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Tipo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Concepto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Ingreso', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Egreso', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tarjeta', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_anular    = "Anular";
        $ruta             = $this->rutas;
        $user = Auth::user();
        $ingreso=0;$egreso=0;$garantia=0;$efectivo=0;$master=0;$visa=0;
        //dd($lista);
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            foreach($lista as $k=>$v){
                if($v->conceptopago_id<>2 && $v->situacion<>'A'){
                    if($v->conceptopago->tipo=="I"){
                        if($v->conceptopago_id<>10 && $v->conceptopago_id <>150){//Garantias
                            if($v->conceptopago_id<>15 && $v->conceptopago_id<>17 && $v->conceptopago_id<>19 && $v->conceptopago_id<>21 && $v->conceptopago_id<>32){
                                $ingreso = $ingreso + $v->total;    
                            }elseif(($v->conceptopago_id==15 || $v->conceptopago_id==17 || $v->conceptopago_id==19 || $v->conceptopago_id==21 || $v->conceptopago_id==32) && $v->situacion=='C'){
                                $ingreso = $ingreso + $v->total;    
                            }
                            if($v->tipotarjeta=='VISA'){
                                $visa = $visa + $v->total;
                            }elseif($v->tipotarjeta==''){
                                $efectivo = $efectivo + $v->total;
                            }else{
                                $master = $master + $v->total;
                            }
                        }else{
                            $garantia = $garantia + $v->total;
                        }
                        if(in_array($v->id, [359022,359023])){
                            $ingreso = $ingreso - $v->total;
                        }
                    }else{
                        if($v->conceptopago_id<>14 && $v->conceptopago_id<>16 && $v->conceptopago_id<>18 && $v->conceptopago_id<>20 && $v->conceptopago_id<>31){
                            $egreso  = $egreso + $v->total;
                        }elseif(($v->conceptopago_id==14 || $v->conceptopago_id==16 || $v->conceptopago_id==18 || $v->conceptopago_id==20 || $v->conceptopago_id==31) && $v->situacion2=='C'){
                            $egreso  = $egreso + $v->total;
                        }
                        if(in_array($v->id, [359022,359023])){
                            $egreso  = $egreso - $v->total;
                        }
                    }
                }
            }
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'conceptopago_id', 'titulo_registrar', 'titulo_apertura', 'titulo_cierre', 'ingreso', 'egreso', 'titulo_anular', 'garantia', 'efectivo', 'visa', 'master', 'listapendiente', 'user' ));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad', 'conceptopago_id', 'titulo_registrar', 'titulo_apertura', 'titulo_cierre', 'ruta', 'ingreso', 'egreso','visa', 'master'));
    }

    public function index(Request $request)
    {
        $entidad          = 'Caja';
        $title            = $this->tituloAdmin;
        $ruta             = $this->rutas;
        $cboCaja          = array();
        $rs        = Caja::where('id','<>',6)->where('id','<>',7)->orderBy('nombre','ASC')->get();
        $caja=0;
        foreach ($rs as $key => $value) {
            $cboCaja = $cboCaja + array($value->id => $value->nombre);
            if($request->ip()==$value->ip){
                $caja=$value->id;
                $serie=$value->serie;
            }
        }
        if($caja==0){//ADMISION 1
            $serie=3;
            $caja=1;
        }
        $user = Auth::user();
        if($user->usertype_id==11){
            $serie=4;
            $caja=4;
        }
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'ruta', 'cboCaja', 'user', 'caja'));
    }

    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Caja';
        $caja = null;
        $cboTipoDoc = array();
        $rs = Tipodocumento::where(DB::raw('1'),'=','1')->where('tipomovimiento_id','=',2)->orderBy('nombre','DESC')->get();
        foreach ($rs as $key => $value) {
            $cboTipoDoc = $cboTipoDoc + array($value->id => $value->nombre);
        }
        $cboConcepto = array();
        $rs = Conceptopago::where(DB::raw('1'),'=','1')->where('tipo','LIKE','I')->where('id','<>','1')->where('id','<>',6)->where('id','<>',13)->where('id','<>',15)->where('id','<>',17)->where('id','<>',19)->where('id','<>',21)->where('id','<>',23)->where('id','<>',32)->where('id','<>',3)->where('admision','like','S')->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboConcepto = $cboConcepto + array($value->id => $value->nombre);
        }
        $formData            = array('caja.store');
        $caja2                = Caja::find($request->input('caja_id'));
        $cboCaja = array();
        $rs = Caja::where(DB::raw('1'),'=','1')->where('id','<>',6)->where('id','<>',7)->where('id','<>',$request->input('caja_id'))->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboCaja = $cboCaja + array($value->id => $value->nombre);
        }
        $cboTipo = array('RH' => 'RH', 'FT' => 'FT', 'BV' => 'BV', 'TK' => 'TK', 'VR' => 'VR');
        $numero              = Movimiento::NumeroSigue(2,2);//movimiento caja y documento ingreso
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');

        $boton               = 'Registrar '.$caja2->nombre; 
        return view($this->folderview.'.mant')->with(compact('caja', 'formData', 'entidad', 'boton', 'listar', 'cboTipoDoc', 'caja2', 'numero', 'cboConcepto', 'cboCaja', 'user', 'cboTipo'));
    }

    public function store(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                'total'          => 'required',
                );
        $mensajes = array(
            'total.required'         => 'Debe tener un monto',
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        
        // dd($request);

        $error = DB::transaction(function() use($request,$user){
            $movimiento        = new Movimiento();
            $movimiento->fecha = date("Y-m-d H:i:s");
            $movimiento->numero= $request->input('numero');
            $movimiento->responsable_id=$user->person_id;
            if($request->input('concepto')==7 || $request->input('concepto')==8 || $request->input('concepto')==14 || $request->input('concepto')==20 || $request->input('concepto')==45 || $request->input('concepto')==35){
                $movimiento->persona_id=$request->input('doctor_id');    
            }elseif($request->input('concepto')==16){//TRANSFERENCIA SOCIO
                $movimiento->persona_id=$request->input('socio_id');
            }else{
                $movimiento->persona_id=$request->input('person_id');    
            }
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->tipomovimiento_id=2;
            $movimiento->tipodocumento_id=$request->input('tipodocumento');
            $movimiento->conceptopago_id=$request->input('concepto');
            $movimiento->comentario=$request->input('comentario');
            $movimiento->caja_id=$request->input('caja_id');
            $movimiento->situacion='N';
            $movimiento->listapago=$request->input('lista');//Lista de pagos para transferencia y pago tambien
            if($request->input('concepto')==10 || $request->input('concepto')==16 ||  $request->input('concepto')==150){//GARANTIA Y TRANSFERENCIA SOCIO
                $movimiento->doctor_id=$request->input('doctor_id');
            }

            if($request->input('concepto')==150){
                $movimiento->responsableGarantia = $request->input('responsable');
            }
            //if($request->input('concepto')==11 || $request->input('concepto')==28 || $request->input('concepto')==9 || $request->input('concepto')==30 || $request->input('concepto')==5 || $request->input('concepto')==22 || $request->input('concepto')==27){
            //if($request->input('tipodocumento')=="3"){
                if($request->input('tipo')=='VR'){
                    $movimiento->voucher=$request->input('numero');
                }else{
                    $movimiento->voucher=$request->input('rh');
                }
                $movimiento->formapago=$request->input('tipo');
            //}
            $movimiento->save();
            $idref=$movimiento->id;
            if($request->input('concepto')==7 || $request->input('concepto')==16 || $request->input('concepto')==14 || $request->input('concepto')==18 || $request->input('concepto')==20 || $request->input('concepto')==31){//Transferencia de Caja y Socio y Tarjeta y atencion por convenio y boleteo y farmacia
                $caja = Caja::find($request->input('caja_id'));
                $movimiento        = new Movimiento();
                $movimiento->fecha = date("Y-m-d H:i:s");
                $numero              = Movimiento::NumeroSigue(2,2);
                $movimiento->numero= $numero;
                $movimiento->responsable_id=$user->person_id;
                if($request->input('concepto')==7 || $request->input('concepto')==14 || $request->input('concepto')==20){//caja y tarjeta y boleteo
                    $movimiento->persona_id=$request->input('doctor_id');
                }elseif($request->input('concepto')==16){//socio
                    $movimiento->persona_id=$request->input('socio_id');
                }elseif($request->input('concepto')==18){//atencion por convenio
                    $movimiento->persona_id=$request->input('person_id');
                }elseif($request->input('concepto')==31){//transferencia farmacia
                    $movimiento->persona_id=$request->input('person_id');
                }
                $movimiento->subtotal=0;
                $movimiento->igv=0;
                $movimiento->total=str_replace(",","",$request->input('total'));
                $movimiento->tipomovimiento_id=2;
                $movimiento->tipodocumento_id=2;//Ingreso
                if($request->input('concepto')==7){//caja
                    $movimiento->conceptopago_id=6;
                }elseif($request->input('concepto')==16){//socio
                    $movimiento->conceptopago_id=17;
                }elseif($request->input('concepto')==14){//tarjeta
                    $movimiento->conceptopago_id=15;
                }elseif($request->input('concepto')==18){//atencion por convenio
                    $movimiento->conceptopago_id=19;
                }elseif($request->input('concepto')==20){//boleteo total
                    $movimiento->conceptopago_id=21;
                }elseif($request->input('concepto')==31){//transferencia farmacia
                    $movimiento->conceptopago_id=32;
                }
                $movimiento->comentario="Envio de caja ".$caja->nombre." por ".$request->input('comentario');
                $movimiento->caja_id=$request->input('caja');
                $movimiento->situacion='P';//PENDIENTE
                $movimiento->movimiento_id=$idref;
                $movimiento->listapago=$request->input('lista');//Lista de pagos para transferencia y pago tambien
                $movimiento->save();  

                if ($request->input('concepto')==31) {
                    $nuevoCarro = true;
                    if ($request->session()->get('carritoventa') !== null) {
                        $lista = $request->session()->get('carritoventa');
                        for ($i=0; $i < count($lista) ; $i++) { 
                            $nuevoCarro = false;
                            $venta = Movimiento::find($lista[$i]['venta_id']);
                            $venta->formapago = 'C';
                            $venta->movimientodescarga_id = $movimiento->id;
                            $venta->save();
                        }
                    }
                    if($nuevoCarro){
                        $lista = $request->input('carritoJSON');
                        $lista = json_decode($lista,true);
                        for ($i=0; $i < count($lista) ; $i++) {
                            $venta = Movimiento::find($lista[$i]['venta_id']);
                            $venta->formapago = 'C';
                            $venta->movimientodescarga_id = $movimiento->id;
                            $venta->save();
                        }
                    }

                } 
                
                $arr=explode(",",$request->input('lista'));
                if($request->input('concepto')==7){//CAJA
                    for($c=0;$c<count($arr);$c++){
                        $Detalle = Detallemovcaja::find($arr[$c]);
                        $Detalle->situacion='T';//transferencia;
                        $Detalle->save();
                    }
                }elseif($request->input('concepto')==16){//SOCIO
                    for($c=0;$c<count($arr);$c++){
                        $Detalle = Detallemovcaja::find($arr[$c]);
                        $Detalle->medicosocio_id = $request->input('socio_id');
                        $Detalle->situacionsocio = 'P'; //PENDIENTE DE CONFIRMAR
                        $Detalle->pagosocio = $request->input('txtPago'.$arr[$c]);
                        $Detalle->save();
                    }
                }elseif($request->input('concepto')==14){//TARJETA
                    for($c=0;$c<count($arr);$c++){
                        $Detalle = Detallemovcaja::find($arr[$c]);
                        $Detalle->situaciontarjeta = 'P'; //PENDIENTE DE CONFIRMAR
                        $Detalle->pagotarjeta = $request->input('txtPago'.$arr[$c]);
                        $Detalle->save();
                    }
                }elseif($request->input('concepto')==20){//BOLETEO TOTAL
                    for($c=0;$c<count($arr);$c++){
                        $Detalle = Detallemovcaja::find($arr[$c]);
                        $Detalle->situaciontarjeta = 'P'; //PENDIENTE DE CONFIRMAR
                        $Detalle->pagotarjeta = $request->input('txtPago'.$arr[$c]);
                        $Detalle->save();
                    }
                }
            }
            if($request->input('concepto')==8 || $request->input('concepto')==45){//Pago a doctor
                $arr=explode(",",$request->input('lista'));
                for($c=0;$c<count($arr);$c++){
                    $Detalle = Detallemovcaja::find($arr[$c]);
                    $Detalle->situacion='P';//pagado;
                    $Detalle->recibo=$request->input('txtRecibo'.$arr[$c]);
                    $Detalle->save();
                }
            }else{
                if($request->input('concepto') == 35){
                    $arr=explode(",",$request->input('lista'));
            
                    for($c=0;$c<count($arr);$c++){
                        $Detalle = Detallemovcaja::find($arr[$c]);
                        $Detalle->situacion_ecografia='P';//pagado;
                        $Detalle->pago_ecografia=$request->input('txtPrecio'.$arr[$c]);
                        $Detalle->save();
                    }

                }

            }
        });
        return is_null($error) ? "OK" : $error;
    }

    public function show($id)
    {
        //
    }

    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'Caja');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $Caja = Caja::find($id);
        $entidad             = 'Caja';
        $formData            = array('Caja.update', $id);
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('Caja', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente'));
    }

    public function update(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'Caja');
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
            $Caja = Movimiento::find($id);
            $Caja->situacion="A";//Anulado
            $Caja->save();
            if($Caja->listapago!=""){
                $arr=explode(",",$Caja->listapago);
                for($c=0;$c<count($arr);$c++){
                    $Detalle = Detallemovcaja::find($arr[$c]);
                    $Detalle->situacion='N';
                    if($Caja->conceptopago_id==6){//CAJA
                        $Detalle->situacion='N';//normal;
                    }elseif($Caja->conceptopago_id==16){//SOCIO
                        $Detalle->situacionsocio=null;//null
                        $Detalle->situaciontarjeta=null;//null
                        $Detalle->medicosocio_id=null;//null
                    }elseif($Caja->conceptopago_id==14 || $Caja->conceptopago_id==20){//TARJETA Y BOLETEO TOTAL
                        $Detalle->situaciontarjeta=null;//null
                    }elseif($Caja->conceptopago_id==24){//CONVENIO
                        $Detalle->situacionentrega=null;//null
                    }
                    $Detalle->save();
                }
            }

            if($Caja->conceptopago_id==7 || $Caja->conceptopago_id==14 || $Caja->conceptopago_id==16 || $Caja->conceptopago_id==18 || $Caja->conceptopago_id==20){//TRANSFERENCIA DE CAJA
                $rs = Movimiento::where('movimiento_id','=',$id)->first();
                $Caja2 = Movimiento::find($rs->id);
                $Caja2->situacion="A";//Anulado
                $Caja2->save();                
            }
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
        $modelo   = Caja::find($id);
        $entidad  = 'Caja';
        $formData = array('route' => array('caja.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
   	public function pdfCierre(Request $request){
        $caja                = Caja::find($request->input('caja_id'));
        $caja_id          = Libreria::getParam($request->input('caja_id'),'1');
        $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->orderBy('movimiento.id','DESC')->limit(1)->first();
        if(count($rst)==0){
            $conceptopago_id=2;
        }else{
            $conceptopago_id=$rst->conceptopago_id;
        }
        $resp=Movimiento::find($rst->id);
        
        $rst              = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',1)->orderBy('id','DESC')->limit(1)->first();
        if(count($rst)>0){
            $movimiento_mayor = $rst->id;    
        }else{
            $movimiento_mayor = 0;
        }
        // $movimiento_mayor = '635386';

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            ->where('movimiento.id', '>=', $movimiento_mayor);
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2')->orderBy('movimiento.id', 'desc');
        $lista            = $resultado->get();
        if (count($lista) > 0) {            
            $pdf = new TCPDF();

            $pdf::setFooterCallback(function($pdf) {
                $pdf->SetY(-15);
                // Set font
                $pdf->SetFont('helvetica', 'I', 8);
                // Page number
                $pdf->Cell(0, 10, 'Pag. '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        });

            $pdf::SetTitle('Cierre de '.$caja->nombre);
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(0,10,utf8_decode("Cierre de ".$caja->nombre),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(18,7,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(13,7,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(16,7,utf8_decode("TIPO"),1,0,'C');
            $pdf::Cell(38,7,utf8_decode("CONCEPTO"),1,0,'C');
            $pdf::Cell(40,7,utf8_decode("PERSONA"),1,0,'C');
            $pdf::Cell(18,7,utf8_decode("INGRESO"),1,0,'C');
            $pdf::Cell(18,7,utf8_decode("EGRESO"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("TARJETA"),1,0,'C');
            $pdf::Cell(55,7,utf8_decode("COMENTARIO"),1,0,'C');
            $pdf::Cell(22,7,utf8_decode("USUARIO"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("SITUACION"),1,0,'C');
            $pdf::Ln();
            $ingreso=0;$egreso=0;$garantia=0;$efectivo=0;$visa=0;$master=0;
            foreach ($lista as $key => $value){
                    
                $pdf::SetFont('helvetica','',7.8);
                $pdf::Cell(18,7,utf8_decode($value->fecha),1,0,'C');
                $pdf::Cell(13,7,utf8_decode($value->numero),1,0,'C');
                $pdf::Cell(16,7,utf8_decode($value->conceptopago->tipo=="I"?"INGRESO":"EGRESO"),1,0,'C');
                if(strlen($value->conceptopago->nombre)>30){
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();
                    $pdf::Multicell(38,3,utf8_decode($value->conceptopago->nombre),0,'C');
                    $pdf::SetXY($x,$y);
                    $pdf::Cell(38,7,"",1,0,'C');
                }else{
                    $pdf::Cell(38,7,utf8_decode($value->conceptopago->nombre),1,0,'C');
                }
                if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>22){
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();                    
                    $pdf::Multicell(40,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                    $pdf::SetXY($x,$y);
                    $pdf::Cell(40,7,"",1,0,'C');
                }else{
                    $pdf::Cell(40,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'C');    
                }
                if($value->situacion<>'R' && $value->situacion2<>'R'){
                    if($value->conceptopago->tipo=="I"){
                        $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'C');
                        $pdf::Cell(18,7,utf8_decode("0.00"),1,0,'C');
                    }else{
                        $pdf::Cell(18,7,utf8_decode("0.00"),1,0,'C');
                        $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'C');
                    }
                }else{
                    $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                    $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                }
                if($value->conceptopago_id<>2 && $value->situacion<>'A'){
                    if($value->conceptopago->tipo=="I"){
                        if($value->conceptopago_id<>10 && $value->conceptopago_id <>150){//GARANTIA
                            if($value->conceptopago_id<>6){
                                $ingreso = $ingreso + $value->total;    
                            }elseif($value->conceptopago_id==6 && $value->situacion=='C'){
                                $ingreso = $ingreso + $value->total;    
                            }
                        }else{
                            $garantia = $garantia + $value->total;
                        }
                        if($value->tipotarjeta=='VISA'){
                                $visa = $visa + $value->total;
                        }elseif($value->tipotarjeta==''){
                            $efectivo = $efectivo + $value->total;
                        }else{
                            $master = $master + $value->total;
                        }
                    }else{
                        if($value->conceptopago_id<>7){
                            $egreso  = $egreso + $value->total;
                        }elseif($value->conceptopago_id==7 && $value->situacion2=='C'){
                            $egreso  = $egreso + $value->total;
                        }
                    }
                }
                
                if($value->tipotarjeta!=""){
                    $pdf::Cell(20,7,utf8_decode($value->tipotarjeta - $value->tarjeta),1,0,'C');
                }else{
                    $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                }
                 if(strlen($value->comentario)>27){
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();                    
                    $pdf::Multicell(55,3,utf8_decode($value->comentario),0,'L');
                    $pdf::SetXY($x,$y);
                    $pdf::Cell(55,7,"",1,0,'C');
                }else{
                    $pdf::Cell(55,7,utf8_decode($value->comentario),1,0,'L');    
                }
                if(strlen($value->responsable->nombres)>25){
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();                    
                    $pdf::Multicell(22,3,($value->responsable->nombres),0,'L');
                    $pdf::SetXY($x,$y);
                    $pdf::Cell(22,7,"",1,0,'C');
                }else{
                    $pdf::Cell(22,7,($value->responsable->nombres),1,0,'L');    
                }
                $color="";
                $titulo="Ok";
                if($value->conceptopago_id==7 || $value->conceptopago_id==6){
                    if($value->conceptopago_id==7){//TRANSFERENCIA EGRESO CAJA QUE ENVIA
                        if($value->situacion2=='P'){//PENDIENTE
                            $color='background:rgba(255,235,59,0.76)';
                            $titulo="Pendiente";
                        }elseif($value->situacion2=='R'){
                            $color='background:rgba(215,57,37,0.50)';
                            $titulo="Rechazado";
                        }elseif($value->situacion2=='C'){
                            $color='background:rgba(10,215,37,0.50)';
                            $titulo="Aceptado";
                        }elseif($value->situacion2=='A'){
                            $color='background:rgba(215,57,37,0.50)';
                            $titulo='Anulado'; 
                        }    
                    }else{
                        if($value->situacion=='P'){
                            $color='background:rgba(255,235,59,0.76)';
                            $titulo="Pendiente";
                        }elseif($value->situacion=='R'){
                            $color='background:rgba(215,57,37,0.50)';
                            $titulo="Rechazado";
                        }elseif($value->situacion=="C"){
                            $color='background:rgba(10,215,37,0.50)';
                            $titulo="Aceptado";
                        }elseif($value->situacion=='A'){
                            $color='background:rgba(215,57,37,0.50)';
                            $titulo='Anulado'; 
                        } 
                    }
                }else{
                    $color=($value->situacion=='A')?'background:rgba(215,57,37,0.50)':'';
                    $titulo=($value->situacion=='A')?'Anulado':'Ok';            
                }
                $pdf::Cell(20,7,utf8_decode($titulo),1,0,'C');
                $pdf::Ln();
            }
            $pdf::Ln();
            $pdf::Cell(120,7,('RESPONSABLE: '.$resp->responsable->nombres)." / Hora Cierre: ".date("d/m/Y H:i:s",strtotime($resp->created_at)),0,0,'L');

            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(50,7,utf8_decode("RESUMEN DE CAJA"),1,0,'C');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("INGRESOS :"),1,0,'L');
            $pdf::Cell(20,7,number_format($ingreso,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Efectivo :"),1,0,'L');
            $pdf::Cell(20,7,number_format($efectivo,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Master :"),1,0,'L');
            $pdf::Cell(20,7,number_format($master,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Visa :"),1,0,'L');
            $pdf::Cell(20,7,number_format($visa,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("EGRESOS :"),1,0,'L');
            $pdf::Cell(20,7,number_format($egreso,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("SALDO :"),1,0,'L');
            $pdf::Cell(20,7,number_format($ingreso - $egreso,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("GARANTIA :"),1,0,'L');
            $pdf::Cell(20,7,number_format($garantia,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Output('ListaCaja.pdf');
        }
    }

    public function pdfDetalleCierre(Request $request){
        $caja                = Caja::find($request->input('caja_id'));
        $caja_id          = Libreria::getParam($request->input('caja_id'),'1');
        $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->orderBy('movimiento.id','DESC')->limit(1)->first();
        if(count($rst)==0){
            $conceptopago_id=2;
        }else{
            $conceptopago_id=$rst->conceptopago_id;
        }
        
        $rst              = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',1)->orderBy('id','DESC')->limit(1)->first();
        if(count($rst)>0){
            $movimiento_mayor = $rst->id;    
        }else{
            $movimiento_mayor = 0;
        }
        
        // $movimiento_mayor = '635738';
        // dd($movimiento_mayor);
        

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            ->where('movimiento.id', '>', $movimiento_mayor)
                            ->whereNull('movimiento.cajaapertura_id')
                            ->where(function($query){
                                $query
                                    ->whereNotIn('movimiento.conceptopago_id',[31])
                                    ->orWhere('m2.situacion','<>','R');
                            })
                            ->where('movimiento.situacion', '<>', 'A')
                            ->where('movimiento.situacion', '<>', 'R');
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','responsable.nombres as responsable2')->orderBy('conceptopago.tipo', 'asc')->orderBy('conceptopago.orden', 'asc')->orderBy('conceptopago.id', 'asc')->orderBy('movimiento.tipotarjeta', 'asc')->orderBy('movimiento.numero', 'asc');
        $listConcepto     = array();
        $listConcepto2     = array();
        $listConcepto3     = array();
        $listConcepto4     = array();
        $listConcepto[]   = 6;//TRANSF CAJA INGRESO
        $listConcepto[]   = 7;//TRANSF CAJA EGRESO
        $listConcepto2[]   = 8;//HONORARIOS MEDICOS
        $listConcepto[]   = 14;//TRANSF TARJETA EGRESO
        $listConcepto[]   = 15;//TRANSF TARJETA INGRESO
        $listConcepto[]   = 16;//TRANSF SOCIO EGRESO
        $listConcepto[]   = 17;//TRANSF SOCIO INGRESO
        $listConcepto3[]   = 24;//PAGO DE CONVENIO
        $listConcepto3[]   = 25;//PAGO DE SOCIO
        $listConcepto[]   = 20;//TRANSF BOLETEO EGRESO
        $listConcepto[]   = 21;//TRANSF BOLETEO INGRESO
        $listConcepto4[]   = 31;//TRANSF FARMACIA EGRESO
        $listConcepto4[]   = 32;//TRANSF FARMACiA INGRESO
        $lista            = $resultado->get();
        $listapendiente = array();
        if ($caja_id != 6) {
            $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','movimiento.id','=','m2.movimiento_id')
                            ->where('movimiento.ventafarmacia', '=', 'S')
                            ->where('movimiento.estadopago', '=', 'PP')
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            ->where('movimiento.situacion', '<>', 'A')
                            ->where('movimiento.situacion', '<>', 'U')->where('movimiento.situacion', '<>', 'R')
                            ->whereNull('movimiento.cajaapertura_id');
            $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2',DB::raw('CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) as paciente'),DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.id', 'desc');
            $listapendiente            = $resultado2->get();
        }
        if (isset($lista)) {            
            $pdf = new TCPDF();
            //$pdf::SetImaº
            $pdf::SetTitle('Detalle Cierre de '.$caja->nombre);
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(0,10,utf8_decode("Detalle de Cierre de ".$caja->nombre),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(15,7,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(60,7,utf8_decode("PERSONA"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(40,7,utf8_decode("EMPRESA"),1,0,'C');
            $pdf::Cell(70,7,utf8_decode("CONCEPTO"),1,0,'C');
            $pdf::Cell(18,7,utf8_decode("EGRESO"),1,0,'C');
            $pdf::Cell(18,7,utf8_decode("INGRESO"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("TARJETA"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("DOCTOR"),1,0,'C');
            $pdf::Ln();
            if($caja_id==1){//ADMISION 1
                $serie=3;
            }elseif($caja_id==2){//ADMISION 2
                $serie=7;
            }elseif($caja_id==3){//CONVENIOS
                $serie=8;
            }elseif($caja_id==5){//EMERGENCIA
                $serie=9;
            }elseif($caja_id==4){//FARMACIA
                $serie=4;
            }elseif($caja_id==8){//PROCEDIMIENTOS
                $serie=5;
            }
            $resultado1       = Movimiento::join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','movimiento.movimiento_id','=','m2.id')
                            ->leftjoin('person as paciente', 'paciente.id', '=', 'm2.persona_id')
                            ->where('movimiento.serie', '=', $serie)
                            ->where('movimiento.tipomovimiento_id', '=', 4)
                            ->where('movimiento.situacion', '=', 'P')
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            ->where('movimiento.situacion', '<>', 'U')
                            ->where('movimiento.situacion', '<>', 'A')
                            ->where('movimiento.situacion', '<>', 'R');
            $resultado1       = $resultado1->select('movimiento.*','m2.situacion as situacion2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'responsable.nombres as responsable2')->orderBy('movimiento.numero', 'asc');
            
            $lista1           = $resultado1->get();
            if ($caja_id == 4) {
                $pendiente = 0;

                foreach ($listapendiente as $key => $value) { 
                    if($pendiente==0 && $value->tipodocumento_id != 15){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(281,7,utf8_decode("PENDIENTE"),1,0,'L');
                        $pdf::Ln();
                    }

                    if ($value->tipodocumento_id != 15) {
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');

                        $nombrepaciente = '';
                        $nombreempresa = '-';
                        if ($value->persona_id !== NULL) {
                            //echo 'entro'.$value->id;break;
                            $nombrepaciente =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                        }else{
                            $nombrepaciente = trim($value->nombrepaciente);
                        }
                        if ($value->tipodocumento_id == 5) {
                            
                            
                        }else{
                            if ($value->empresa_id != null) {
                                $nombreempresa = trim($value->empresa->bussinesname);
                            }
                        }
                        if(strlen($nombrepaciente)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($nombrepaciente),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($nombrepaciente),1,0,'L');    
                        }
                        //$venta= Movimiento::find($value->id);
                        $pdf::Cell(8,7,($value->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(12,7,$value->serie.'-'.$value->numero,1,0,'C');

                        if ($value->conveniofarmacia_id != null) {
                            $nombreempresa = $value->conveniofarmacia->nombre;
                        }

                        $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                        if($value->servicio_id>0){
                            if(strlen($value->servicio->nombre)>35){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(70,3,$value->servicio->nombre,0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(70,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(70,7,$value->servicio->nombre,1,0,'L');    
                            }
                        }else{
                            $pdf::Cell(70,7,'MEDICINA',1,0,'L');    
                        }
                        $pdf::Cell(18,7,'',1,0,'C');
                        $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                        $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                        if ($value->doctor_id != null) {
                            $pdf::Cell(20,7,substr($value->doctor->nombres,0,1).'. '.$value->doctor->apellidopaterno,1,0,'L');

                        }else{
                           $pdf::Cell(20,7," - ",1,0,'L'); 
                        }
                        
                        $pdf::Ln();
                        $pendiente=$pendiente + number_format($value->total,2,'.','');
                    }

                }
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($pendiente,2,'.',''),1,0,'R');
                $pdf::Ln();
            }
            

            if(count($lista1)>0){
                $pendiente=0;
                foreach($lista1 as $key1 => $value1){
                    $rs = Detallemovcaja::where("movimiento_id",'=',$value1->movimiento_id)->get();
                    foreach ($rs as $k => $v){
                        if($pendiente==0){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(281,7,utf8_decode("PENDIENTE"),1,0,'L');
                            $pdf::Ln();
                        }
                        $pdf::SetFont('helvetica','',7.5);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value1->fecha)),1,0,'C');
                        if($value1->tipodocumento_id==5){//BOLETA
                            $nombre=$value1->persona->apellidopaterno.' '.$value1->persona->apellidomaterno.' '.$value1->persona->nombres;
                        }else{
                            $nombre=$value1->paciente2;
                            $empresa=$value1->persona->bussinesname." / ";
                        }
                        if(strlen($nombre)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($nombre),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($nombre),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($value1->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(12,7,$value1->serie.'-'.$value1->numero,1,0,'C');
                        $ticket= Movimiento::find($value1->movimiento_id);
                        if($value1->tipodocumento_id==5){//BOLETA
                            $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');    
                        }else{
                            $pdf::Cell(40,7,substr($empresa,0,23),1,0,'L');
                        }
                        if($v->servicio_id>0){
                            if(strlen($v->servicio->nombre)>35){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(70,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                            }
                        }else{
                            $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                        }
                        $pdf::Cell(18,7,'',1,0,'C');
                        $pdf::Cell(18,7,number_format($v->cantidad*$v->pagohospital,2,'.',''),1,0,'R');
                        $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                        $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                        $pdf::Ln();
                        $pendiente=$pendiente + number_format($v->cantidad*$v->pagohospital,2,'.','');
                    }
                }
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($pendiente,2,'.',''),1,0,'R');
                $pdf::Ln();
            }

            $ingreso=0;$egreso=0;$transferenciai=0;$transferenciae=0;$garantia=0;$efectivo=0;$visa=0;$master=0;$pago=0;$tarjeta=0;$cobranza=0;$egreso1=0;$transferenciai=0;$cobranza=0;$ingresotarjeta=0;
            $bandpago=true;$bandegreso=true;$bandtransferenciae=true;$bandtarjeta=true;$bandtransferenciai=true;$bandcobranza=true;$bandingresotarjeta=true;
            foreach ($lista as $key => $value){
                if($ingreso==0){
                    $responsable=$value->responsable2;
                }
                
                if($value->conceptopago_id==3 && $value->tipotarjeta==''){


                    if ($caja_id == 4) {
                        $rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        //echo $value->movimiento_id."|".$value->id."@";
                        foreach ($rs as $k => $v) {
                            $pdf::SetTextColor(0,0,0);
                            if($egreso1>0 && $bandegreso){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(205,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                                $bandegreso=false;
                                $pdf::Ln(); 
                            }
                            if($pago==0 && $value->tipodocumento_id!=15){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                                $pdf::Ln();
                            }
                            if ($value->tipodocumento_id !== 15) {
                                $pdf::SetFont('helvetica','',7);
                                $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');

                                $nombrepaciente = '';
                                $nombreempresa = '-';
                                if ($value->persona_id !== NULL) {
                                    //echo 'entro'.$value->id;break;
                                    $nombrepaciente =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                                }else{
                                    $nombrepaciente = trim($value->nombrepaciente);
                                }
                                if ($value->tipodocumento_id == 5) {
                                    
                                    
                                }else{
                                    if ($value->empresa_id != null) {
                                        // dd($value);
                                        
                                        $nombreempresa = trim($value->empresa->bussinesname);
                                    }
                                    
                                }
                                

                                if(strlen($nombrepaciente)>30){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(60,3,($nombrepaciente),0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(60,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(60,7,($nombrepaciente),1,0,'L');    
                                }
                                $venta= Movimiento::find($value->movimiento_id);
                                $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                                $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                                if ($venta->conveniofarmacia_id != null) {
                                    $nombreempresa = $venta->conveniofarmacia->nombre;
                                }
                                $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                                if($v->servicio_id>0){
                                    if(strlen($v->servicio->nombre)>35){
                                        $x=$pdf::GetX();
                                        $y=$pdf::GetY();                    
                                        $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                        $pdf::SetXY($x,$y);
                                        $pdf::Cell(70,7,"",1,0,'C');
                                    }else{
                                        $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                    }
                                }else{
                                    $pdf::Cell(70,7,$v->descripcion.'- MEDICINA',1,0,'L');    
                                }
                                $pdf::Cell(18,7,'',1,0,'C');
                                $pdf::Cell(18,7,number_format($v->movimiento->total,2,'.',''),1,0,'R');
                                $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                                if ($venta->doctor_id != null) {
                                    $pdf::Cell(20,7,substr($venta->doctor->nombres,0,1).'. '.$venta->doctor->apellidopaterno,1,0,'L');

                                }else{
                                   $pdf::Cell(20,7," - ",1,0,'L'); 
                                }
                                
                                $pdf::Ln();
                                $pago=$pago + number_format($v->movimiento->total,2,'.','');
                            }
                            
                        }
                    }else{
                        //PARA PAGO DE CLIENTE, BUSCO TICKET
                        $rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        foreach ($rs as $k => $v){
                            $pdf::SetTextColor(0,0,0);
                            if($transferenciae>0 && $bandtransferenciae){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(205,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($transferenciae,2,'.',''),1,0,'R');
                                $bandtransferenciae=false;
                                $pdf::Ln(); 
                            }
                            if($egreso1>0 && $bandegreso){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(205,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                                $bandegreso=false;
                                $pdf::Ln(); 
                            }
                            if($pago==0){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                                $pdf::Ln();
                                if($caja_id==3){
                                    $pdf::SetFont('helvetica','B',7);
                                    $pdf::Cell(223,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                                    $apert = Movimiento::find($movimiento_mayor);
                                    $pdf::Cell(18,7,number_format($apert->total,2,'.',''),1,0,'R');
                                    $pago = $pago + $apert->total;
                                    $ingreso = $ingreso + $apert->total;
                                    $pdf::Ln();
                                }
                            }
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                            }
                            $venta= Movimiento::find($value->movimiento_id);
                            $ticket= Movimiento::find($v->movimiento_id);
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                            if($v->servicio_id>0){
                                if(strlen($v->servicio->nombre)>35){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(70,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                            $pdf::Cell(18,7,'',1,0,'C');
                            $pdf::Cell(18,7,number_format($v->cantidad*$v->pagohospital,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode('-'),1,0,'C');
                            $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                            $pdf::Ln();
                            $pago=$pago + number_format($v->cantidad*$v->pagohospital,2,'.','');
                        }
                    }
                }elseif($value->conceptopago_id==3 && $value->tipotarjeta!=''){//PARA PAGO DE CLIENTE, BUSCO TICKET CON TARJETA
                    if ($caja_id == 4) {
                        $rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        foreach ($rs as $k => $v){
                            $pdf::SetTextColor(0,0,0);
                            if($pago>0 && $bandpago){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                                $bandpago=false;
                                $pdf::Ln(); 
                            }

                            if($tarjeta==0){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(281,7,utf8_decode("TARJETA"),1,0,'L');
                                $pdf::Ln();
                            }
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            
                            $nombrepaciente = '';
                            $nombreempresa = '-';
                            if ($value->persona_id !== NULL) {
                                //echo 'entro'.$value->id;break;
                                $nombrepaciente =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                            }else{
                                $nombrepaciente = trim($value->nombrepaciente);
                            }
                            if ($value->tipodocumento_id == 5) {
                                
                                
                            }else{
                                $nombreempresa = trim($value->empresa->bussinesname);
                            }
                            if(strlen($nombrepaciente)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($nombrepaciente),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($nombrepaciente),1,0,'L');    
                            }
                            $venta= Movimiento::find($value->movimiento_id);
                            $ticket= Movimiento::find($v->movimiento_id);
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            if ($venta->conveniofarmacia_id != null) {
                                $nombreempresa = $venta->conveniofarmacia->nombre;
                            }
                            $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                            if($v->servicio_id>0){
                                if(strlen($v->servicio->nombre)>35){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(70,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                            $pdf::Cell(18,7,'',1,0,'C');
                            $tarjeta=$tarjeta + $v->movimiento->total;
                            $pdf::Cell(18,7,number_format($v->movimiento->total,2,'.',''),1,0,'R');
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();

                            // $pdf::SetFont('helvetica','',6);
                            $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(20,7,"",1,0,'C');
                            if ($venta->doctor_id != null) {
                                $pdf::Cell(20,7,substr($venta->doctor->nombres,0,1).'. '.$venta->doctor->apellidopaterno,1,0,'L');

                            }else{
                               $pdf::Cell(20,7," - ",1,0,'L'); 
                            }
                            $pdf::Ln();
                            //$pago=$pago + number_format($v->movimiento->total,2,'.','');
                            // $pdf::SetFont('helvetica','',7);
                            
                        }
                    }else{
                        $rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        foreach ($rs as $k => $v){
                            $pdf::SetTextColor(0,0,0);
                            if($pago>0 && $bandpago){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                                $bandpago=false;
                                $pdf::Ln(); 
                            }
                            if($tarjeta==0){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(281,7,utf8_decode("TARJETA"),1,0,'L');
                                $pdf::Ln();
                            }
                            if($value->situacion<>'A'){
                                $pdf::SetTextColor(0,0,0);
                            }else{
                                $pdf::SetTextColor(255,0,0);
                            }
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            $tarjeta=$tarjeta + number_format($v->cantidad*$v->pagohospital,2,'.','');
                            if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                            }
                            $venta= Movimiento::find($value->movimiento_id);
                            $ticket= Movimiento::find($v->movimiento_id);
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                            if($v->servicio_id>0){
                                if(strlen($v->servicio->nombre)>35){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(70,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                            $pdf::Cell(18,7,'',1,0,'C');
                            $pdf::Cell(18,7,number_format($v->cantidad*$v->pagohospital,2,'.',''),1,0,'R');
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();     
                            $pdf::SetFont('helvetica','',6);
                            $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(20,7,"",1,0,'C');
                            $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                            $pdf::Ln();
                            //$pago=$pago + number_format($v->cantidad*$v->pagohospital,2,'.','');
                            $pdf::SetFont('helvetica','',7);
                        }

                    }
                    
                }elseif(in_array($value->conceptopago_id, $listConcepto) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA EGRESOS
                    if($value->situacion2<>'R'){
                        $pdf::SetTextColor(0,0,0);
                        if($egreso1>0 && $bandegreso){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(205,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                            $bandegreso=false;
                            $pdf::Ln(); 
                        }
                        if($transferenciae==0){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(281,7,utf8_decode("TRANSFERENCIA"),1,0,'L');
                            $pdf::Ln();
                        }
                        if($value->situacion<>'A'){
                            $pdf::SetTextColor(0,0,0);
                        }else{
                            $pdf::SetTextColor(255,0,0);
                        }
                        $list=explode(",",$value->listapago);
                        // dd($list);
                        $transferenciae = $transferenciae + $value->total;
                        for($c=0;$c<count($list);$c++){
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            // dd($list[$c]);
                            $detalle = Detallemovcaja::find($list[$c]);
                            $ticket = Movimiento::where("id","=",$detalle->movimiento_id)->first();
                            $venta = Movimiento::where("movimiento_id","=",$detalle->movimiento_id)->first();
                            if(strlen($ticket->persona->movimiento.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            if($venta->tipodocumento_id==4){
                                $pdf::Cell(40,7,$venta->persona->bussinesname,1,0,'L');
                            }else{
                                $pdf::Cell(40,7,"",1,0,'L');
                            }
                            if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                                $descripcion=$value->conceptopago->nombre.' - RH: '.$detalle->recibo;
                            }else{
                                $descripcion=$value->conceptopago->nombre;
                            }
                            if(strlen($descripcion)>40){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();
                                $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(70,7,"",1,0,'L');
                            }else{
                                $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                            }
                            if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                                $pdf::Cell(18,7,number_format($detalle->pagodoctor,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                            }elseif($value->conceptopago_id==16){//TRANSFERENCIA SOCIO
                                $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                            }elseif($value->conceptopago_id==20){//BOLETEO TOTAL
                                $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                            }elseif($value->conceptopago_id==14){//TARJETA
                                $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                                if(in_array($value->id, [359022,359023])){
                                    $egreso = $egreso - $detalle->pagotarjeta;
                                }//TRANSFERENCIA POR CHEQUE MANUAL DIA 09/01/19, USUARIO NELLY MONTEZA
                            }
                            $pdf::Ln();
                        }
                    }
                }elseif(in_array($value->conceptopago_id, $listConcepto) && $value->conceptopago->tipo=='I'){//CONCEPTOS QUE TIENEN LISTA INGRESOS
                    $pdf::SetTextColor(0,0,0);
                    if($pago>0 && $bandpago){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                        $bandpago=false;
                        $pdf::Ln(); 
                    }
                    if($tarjeta>0 && $bandtarjeta){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                        $bandtarjeta=false;
                        $pdf::Ln(); 
                    }
                    if($transferenciai==0){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(281,7,utf8_decode("TRANSFERENCIA"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $transferenciai = $transferenciai + $value->total;
                    $list=explode(",",$value->listapago);
                    for($c=0;$c<count($list);$c++){
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        $detalle = Detallemovcaja::find($list[$c]);
                        $venta = Movimiento::where("movimiento_id","=",$detalle->movimiento_id)->first();
                        if(strlen($venta->persona->movimiento.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                        $pdf::Cell(40,7,"",1,0,'L');
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $descripcion=$value->conceptopago->nombre.' - RH: '.$detalle->recibo;
                        }else{
                            $descripcion=$value->conceptopago->nombre;
                        }
                        if(strlen($descripcion)>40){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(70,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                        }
                        if($value->conceptopago_id==21 ){//BOLETEO TOTAL
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }elseif($value->conceptopago_id==17){// TRANSFERENCIA SOCIO
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }elseif($value->conceptopago_id==15){//TARJETA
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                            if(in_array($value->id, [359022,359023])){
                                $ingreso = $ingreso - $detalle->pagotarjeta;
                            }//TRANSFERENCIA POR CHEQUE MANUAL DIA 09/01/19, USUARIO NELLY MONTEZA
                        }
                        $pdf::Ln();   
                    }
                }elseif(in_array($value->conceptopago_id, $listConcepto2) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA2
                    /*$pdf::SetTextColor(0,0,0);
                    if($egreso==0){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(279,7,utf8_decode("EGRESO"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $list=explode(",",$value->listapago);//print_r($value->listapago."-");
                    for($c=0;$c<count($list);$c++){
                        $detalle = Detallemovcaja::find($list[$c]);
                        $venta = Movimiento::where("movimiento_id","=",$detalle->movimiento_id)->first();
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        if(strlen($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(10,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                        $pdf::Cell(40,7,"",1,0,'L');
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $descripcion=$value->conceptopago->nombre.' - RH: '.$detalle->recibo;
                        }else{
                            $descripcion=$value->conceptopago->nombre;
                        }
                        if(strlen($descripcion)>40){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(70,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                        }
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $pdf::Cell(18,7,number_format($detalle->pagodoctor,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }else{//SOCIO
                            $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }
                        $pdf::Ln();   
                    }*/
                }elseif(in_array($value->conceptopago_id, $listConcepto3) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA3
                    $pdf::SetTextColor(0,0,0);
                    if($egreso1==0){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(281,7,utf8_decode("EGRESO"),1,0,'L');
                        $pdf::Ln();
                    }
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                    }   
                    $pdf::Cell(8,7,'RH',1,0,'C');
                    $list=explode(",",$value->listapago);
                    $detalle = Detallemovcaja::find($list[0]);
                    if($value->conceptopago_id==25)//pago de socio
                        $pdf::Cell(12,7,$detalle->recibo2,1,0,'C');
                    else
                        $pdf::Cell(12,7,$detalle->recibo,1,0,'C');
                    $pdf::Cell(40,7,"",1,0,'L');
                    $descripcion=$value->conceptopago->nombre;
                    if(strlen($descripcion)>40){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(70,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                    }
                    $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                    $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Ln();
                    $egreso1 = $egreso1 + $value->total;
                }elseif($value->conceptopago_id==23 || $value->conceptopago_id == 32){//COBRANZA
                    if ($caja_id == 4 && $value->conceptopago_id == 32) {
                        if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }
                        if($tarjeta>0 && $bandtarjeta){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                            $bandtarjeta=false;
                            $pdf::Ln(); 
                        }
                        $listventas = Movimiento::where('movimientodescarga_id','=',$value->id)->get();

                        if($cobranza==0 && $value->conceptopago->tipo=="I"){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(281,7,utf8_decode("COBRANZA"),1,0,'L');
                            $pdf::Ln();
                        }
                        foreach ($listventas as $key6 => $value6) {
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value6->fecha)),1,0,'C');
                            $nombrepersona = '-';
                            if ($value6->persona_id !== NULL) {
                                //echo 'entro'.$value6->id;break;
                                $nombrepersona =$value6->persona->apellidopaterno.' '.$value6->persona->apellidomaterno.' '.$value6->persona->nombres;

                            }else{
                                $nombrepersona = trim($value6->nombrepaciente);
                            }
                            if(strlen($nombrepersona)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($nombrepersona),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($value6->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$value6->serie.'-'.$value6->numero,1,0,'C');
                            $nombreempresa = '';
                            if ($value6->conveniofarmacia_id != null) {
                                $nombreempresa = $value6->conveniofarmacia->nombre;
                            }
                            $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                            $pdf::Cell(70,7,'MEDICINA',1,0,'L');
                            $pdf::Cell(18,7,'',1,0,'C');
                            $pdf::Cell(18,7,number_format($value6->total,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                            if ($value6->doctor_id != null) {
                                $pdf::Cell(20,7,substr($value6->doctor->nombres,0,1).'. '.$value6->doctor->apellidopaterno,1,0,'L');

                            }else{
                               $pdf::Cell(20,7," - ",1,0,'L'); 
                            }
                            if($value->situacion=='C')//CONFIRMADO FARMACIA
                                $cobranza=$cobranza + $value6->total;
                            $pdf::Ln();
                        }
                    }elseif($caja_id == 4 && $value->conceptopago_id == 23){
                        if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }
                        if($tarjeta>0 && $bandtarjeta){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                            $bandtarjeta=false;
                            $pdf::Ln(); 
                        }
                        $listventas = Movimiento::where('movimiento_id','=',$value->id)->get();
                        if($cobranza==0 && $value->conceptopago->tipo=="I"){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(281,7,utf8_decode("COBRANZA"),1,0,'L');
                            $pdf::Ln();
                        }
                        foreach ($listventas as $key6 => $value6) {
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value6->fecha)),1,0,'C');
                            $nombrepersona = '-';
                            if ($value6->persona_id !== NULL) {
                                //echo 'entro'.$value6->id;break;
                                $nombrepersona =$value6->persona->apellidopaterno.' '.$value6->persona->apellidomaterno.' '.$value6->persona->nombres;

                            }else{
                                $nombrepersona = trim($value6->nombrepaciente);
                            }
                            if(strlen($nombrepersona)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($nombrepersona),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($value6->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$value6->serie.'-'.$value6->numero,1,0,'C');
                            $nombreempresa = '';
                            if ($value6->conveniofarmacia_id != null) {
                                $nombreempresa = $value6->conveniofarmacia->nombre;
                            }
                            $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                            $pdf::Cell(70,7,'MEDICINA',1,0,'L');
                            $pdf::Cell(18,7,'',1,0,'C');
                            $pdf::Cell(18,7,number_format($value6->total,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                            if ($value6->doctor_id != null) {
                                $pdf::Cell(20,7,substr($value6->doctor->nombres,0,1).'. '.$value6->doctor->apellidopaterno,1,0,'L');

                            }else{
                               $pdf::Cell(20,7," - ",1,0,'L'); 
                            }
                            $cobranza=$cobranza + $value6->total;
                            $pdf::Ln();
                        }
                    }else{

                        $pdf::SetTextColor(0,0,0);
                        if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }
                        if($tarjeta>0 && $bandtarjeta){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                            $bandtarjeta=false;
                            $pdf::Ln(); 
                        }
                        if($ingresotarjeta>0 && $bandingresotarjeta){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($ingresotarjeta,2,'.',''),1,0,'R');
                            $bandingresotarjeta=false;
                            $pdf::Ln(); 
                        }
                        if($cobranza==0 && $value->conceptopago->tipo=="I"){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(281,7,utf8_decode("COBRANZA"),1,0,'L');
                            $pdf::Ln();
                        }
                        if($value->situacion<>'A'){
                            $pdf::SetTextColor(0,0,0);
                        }else{
                            $pdf::SetTextColor(255,0,0);
                        }
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        $nombrepersona = '-';
                        if ($value->persona_id !== NULL) {
                            //echo 'entro'.$value->id;break;
                            $nombrepersona =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                        }else{
                            $nombrepersona = trim($value->nombrepaciente);
                        }
                        if(strlen($nombrepersona)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($nombrepersona),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                        }
                        $venta = Movimiento::find($value->movimiento_id);
                        $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':"F"),1,0,'C');
                        $pdf::Cell(12,7,utf8_decode($venta->serie.'-'.$venta->numero),1,0,'C');
                        if($value->conceptopago_id==11){//PAGO A ENFERMERIA
                            $descripcion=$value->conceptopago->nombre.': '.$value->comentario.' - RH: '.$value->voucher;
                        }else{
                            $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                        }
                        if(strlen($descripcion)>70){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(110,3,($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(110,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(110,7,($descripcion),1,0,'L');
                        }
                        if($value->situacion<>'R' && $value->situacion2<>'R'){
                            if($value->conceptopago->tipo=="I"){
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'R');
                                $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            }else{
                                $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            }
                        }else{
                            $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                            $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                        }
                        $cobranza=$cobranza + $value->total;
                        $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                        $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                        $pdf::Ln();
                    }
                }elseif($value->conceptopago_id==33){//PAGO DE FARMACIA
                    $pdf::SetTextColor(0,0,0);
                    if($transferenciae>0 && $bandtransferenciae){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(205,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($transferenciae,2,'.',''),1,0,'R');
                        $transferenciae=false;
                        $pdf::Ln(); 
                    }
                    if($tarjeta>0 && $bandtarjeta){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                        $bandtarjeta=false;
                        $pdf::Ln(); 
                    }
                    if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }

                    if($ingresotarjeta==0){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(281,7,utf8_decode("INGRESOS POR TRANSFERENCIA"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    $nombrepersona = '-';
                    if ($value->persona_id != NULL && !is_null($value->persona)) {
                        //echo 'entro'.$value->id;break;
                        if ($value->persona->bussinesname != null) {
                            $nombrepersona = $value->persona->bussinesname;
                        }else{
                            $nombrepersona =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                        }
                        
                    }else{
                        $nombrepersona = trim($value->nombrepaciente);
                    }
                    if(strlen($nombrepersona)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($nombrepersona),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                    }
                    if($value->conceptopago_id==31){
                        $pdf::Cell(8,7,'T',1,0,'C');
                    }else{
                        $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C'); 
                        
                    }
                    $pdf::Cell(12,7,utf8_decode(trim($value->voucher)==''?$value->numero:$value->voucher),1,0,'C');
                
                    $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                    
                    if(strlen($descripcion)>70){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(110,3,SUBSTR($descripcion,0,150),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(110,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(110,7,($descripcion),1,0,'L');
                    }
                    if($value->situacion<>'R' && $value->situacion2<>'R'){
                        if($value->conceptopago->tipo=="I"){
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'R');
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $ingresotarjeta=$ingresotarjeta + $value->total;
                        }else{
                            $egreso1=$egreso1 + $value->total;
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                        }
                    }else{
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                    }
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Ln();
                }elseif($value->conceptopago_id!=1 && $value->conceptopago_id!=2 && $value->conceptopago_id!=23 && $value->conceptopago_id!=10 && $value->conceptopago_id!=150){
                    $pdf::SetTextColor(0,0,0);
                    if($transferenciae>0 && $bandtransferenciae){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(205,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($transferenciae,2,'.',''),1,0,'R');
                        $transferenciae=false;
                        $pdf::Ln(); 
                    }
                    if(($ingreso==0 || $pago==0) && $value->conceptopago->tipo=="I"){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                        $pdf::Ln();
                        if($pago==0){
                            if($caja_id==3){
                                $pdf::SetFont('helvetica','B',7);
                                $pdf::Cell(223,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                                $apert = Movimiento::find($movimiento_mayor);
                                $pdf::Cell(18,7,number_format($apert->total,2,'.',''),1,0,'R');
                                $pago = $pago + $apert->total;
                                $ingreso = $ingreso + $apert->total;
                                $pdf::Ln();
                            }
                        }
                    }elseif($egreso1==0){
                        //$pdf::SetFont('helvetica','B',8.5);
                        //$pdf::Cell(281,7,utf8_decode("EGRESOS"),1,0,'L');
                        //$pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    $nombrepersona = '-';
                    if ($value->persona_id != NULL && !is_null($value->persona)) {
                        //echo 'entro'.$value->id;break;
                        if ($value->persona->bussinesname != null) {
                            $nombrepersona = $value->persona->bussinesname;
                        }else{
                            $nombrepersona =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                        }
                        
                    }else{
                        $nombrepersona = trim($value->nombrepaciente);
                    }
                    if(strlen($nombrepersona)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($nombrepersona),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                    }
                    if($value->conceptopago_id!=13){
                        if($value->conceptopago_id==31){
                            $pdf::Cell(8,7,'T',1,0,'C');
                        }else{
                            if ($caja_id == 4) {
                                if ($value->tipodocumento_id == 7) {
                                    $pdf::Cell(8,7,'BV',1,0,'C');
                                }elseif($value->tipodocumento_id == 6){
                                    $pdf::Cell(8,7,'FT',1,0,'C');
                                }else{
                                    $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C'); 
                                }
                            }else{
                               $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C'); 
                            }
                            
                        }
                        $pdf::Cell(12,7,utf8_decode(trim($value->voucher)==''?$value->numero:$value->voucher),1,0,'C');
                    }else{//PARA ANULACION POR NOTA CREDITO
                        $pdf::Cell(8,7,'NA',1,0,'C');
                        $mov = Movimiento::find($value->movimiento_id);
                        $pdf::Cell(12,7,($mov->serie.'-'.$mov->numero),1,0,'C');
                    }

                    if($value->conceptopago_id==11){//PAGO A ENFERMERIA
                        $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                    }else{
                        $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                    }
                    if(strlen($descripcion)>70){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(110,3,SUBSTR($descripcion,0,150),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(110,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(110,7,($descripcion),1,0,'L');
                    }
                    if($value->situacion<>'R' && $value->situacion2<>'R'){
                        if($value->conceptopago->tipo=="I"){
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'R');
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pago=$pago + $value->total;
                        }else{
                            $egreso1=$egreso1 + $value->total;
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                        }
                    }else{
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                    }
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Ln();
                }
                
                if($value->conceptopago_id<>2 && $value->situacion<>'A'){
                    if($value->conceptopago->tipo=="I"){
                        if($value->conceptopago_id<>10 && $value->conceptopago_id<>150){//GARANTIA
                            if($value->conceptopago_id<>15 && $value->conceptopago_id<>17 && $value->conceptopago_id<>19 && $value->conceptopago_id<>21){
                                if ($value->tipodocumento_id != 15) {
                                    //echo $value->total."@";
                                    $ingreso = $ingreso + $value->total;
                                }
                                    
                            }elseif(($value->conceptopago_id==15 || $value->conceptopago_id==17 || $value->conceptopago_id==19 || $value->conceptopago_id==21) && $value->situacion=='C'){
                                $ingreso = $ingreso + $value->total;    
                            }
                        }else{
                            $garantia = $garantia + $value->total;
                        }
                        if($value->conceptopago_id<>10  && $value->conceptopago_id<>150){//GARANTIA
                            if($value->tipotarjeta=='VISA'){
                                    $visa = $visa + $value->total;
                            }elseif($value->tipotarjeta==''){
                                if ($value->tipodocumento_id != 15) {
                                    $efectivo = $efectivo + $value->total;
                                }
                            }else{
                                $master = $master + $value->total;
                            }
                        }
                    }else{
                        if($value->conceptopago_id<>14 && $value->conceptopago_id<>16 && $value->conceptopago_id<>18 && $value->conceptopago_id<>20){
                            if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                                $ingreso  = $ingreso - $value->total;
                                $efectivo = $efectivo - $value->total;
                            }else{
                                $egreso  = $egreso + $value->total;
                            }
                        }elseif(($value->conceptopago_id==14 || $value->conceptopago_id==16 || $value->conceptopago_id==18 || $value->conceptopago_id==20) && $value->situacion2=='C'){
                            $egreso  = $egreso + $value->total;
                        }
                    }
                }
                $res=$value->responsable2;
                if ($caja_id == 4) {
                    /*if($tarjeta>0 && $bandtarjeta){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                        $bandtarjeta=false;
                        $pdf::Ln(); 
                    }*/
                }
            }
            if($ingresotarjeta>0 && $bandingresotarjeta){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($ingresotarjeta,2,'.',''),1,0,'R');
                $bandingresotarjeta=false;
                $pdf::Ln(); 
            }
            if($cobranza>0 && $bandcobranza){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($cobranza,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($transferenciai>0 && $bandtransferenciai){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($transferenciai,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($pago==0){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                $pdf::Ln();
                if($caja_id==3){
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(223,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                    $apert = Movimiento::find($movimiento_mayor);
                    $pdf::Cell(18,7,number_format($apert->total,2,'.',''),1,0,'R');
                    $pago = $pago + $apert->total;
                    $ingreso = $ingreso + $apert->total;
                    $pdf::Ln();
                }
            }
            if($pago>0 && $bandpago){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($tarjeta>0 && $bandtarjeta){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                $bandtarjeta=false;
                $pdf::Ln(); 
            }

            $resultado1       = Movimiento::join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                ->leftjoin('movimiento as m2','movimiento.movimiento_id','=','m2.id')
                                ->leftjoin('person as paciente', 'paciente.id', '=', 'm2.persona_id')
                                ->where('movimiento.serie', '=', $serie)
                                ->where('movimiento.tipomovimiento_id', '=', 4)
                                ->where('movimiento.tipodocumento_id', '<>', 15)
                                ->where('movimiento.id', '>', $movimiento_mayor)
                                ->where('movimiento.situacion','like','U');
            $resultado1       = $resultado1->select('movimiento.*','m2.situacion as situacion2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'))->orderBy('movimiento.numero', 'asc');
            
            $lista1           = $resultado1->get();
            if(count($lista1)>0){
                //echo 'alert('.count($lista1).')';
                $anuladas=0;
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(281,7,utf8_decode("ANULADAS"),1,0,'L');
                $pdf::Ln();
                foreach($lista1 as $key1 => $value1){
                    $pdf::SetFont('helvetica','',7.5);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value1->fecha)),1,0,'C');
                    if($value1->tipodocumento_id==5){//BOLETA}
                        $nombre=$value1->paciente2;
                        //$value1->persona->apellidopaterno.' '.$value1->persona->apellidomaterno.' '.$value1->persona->nombres;
                    }else{
                        $nombre=$value1->paciente2;
                        $empresa=$value1->persona->bussinesname;
                    }
                    if(strlen($nombre)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($nombre),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($nombre),1,0,'L');    
                    }
                    $pdf::Cell(8,7,($value1->tipodocumento_id==5?'B':'F'),1,0,'C');
                    $pdf::Cell(12,7,$value1->serie.'-'.$value1->numero,1,0,'C');
                    if($caja_id==4){
                        $nombreempresa='-';
                        if ($value->tipodocumento_id != 5) {
                            if ($value->empresa != null) {
                                $nombreempresa = trim($value->empresa->bussinesname);
                            }
                        }
                        $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                    }else{
                        if($value1->tipodocumento_id==5){
                            $ticket= Movimiento::find($value1->movimiento_id);
                            if($ticket->plan_id>0)
                                $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                            else
                                $pdf::Cell(40,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(40,7,substr($empresa,0,23),1,0,'L');
                        }
                    }
                    if($caja_id==4){
                        $pdf::Cell(70,7,"MEDICINA",1,0,'L');    
                    }else{
                        // echo json_encode($value1);
                        if($value1->movimiento_id !== ''){
                           $detalles = Detallemovcaja::join('servicio','servicio.id','=','detallemovcaja.servicio_id')->where('detallemovcaja.movimiento_id','=',$value1->movimiento_id)->select('servicio.nombre')->get();

                           $detalles_serv_cadena = '';
                        
                           foreach($detalles as $key2 => $value2){
                                $detalles_serv_cadena.=$value2->nombre;
                                break;
                           }

                           if($detalles_serv_cadena !== ''){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(70,7,$detalles_serv_cadena,0,'L');
                                $pdf::SetXY($x,$y);
                           }else{
                               $detalles = Detallemovcaja::where('detallemovcaja.movimiento_id','=',$value1->movimiento_id)->select('descripcion')->get();

                               $detalles_serv_cadena = '';
                            
                               foreach($detalles as $key2 => $value2){
                                    $detalles_serv_cadena.=$value2->descripcion;
                                    break;
                               }

                                // $pdf::SetFont('helvetica','',6);
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(70,7,$detalles_serv_cadena,0,'L');
                                $pdf::SetXY($x,$y);   
                            
                            }
                            // $pdf::SetFont('helvetica','',6);
                                  
                           // $pdf::Cell(88,7,$detalles_serv_cadena,1,0,'L');    
                        }
                        // $pdf::Cell(70,7,"SERVICIOS",1,0,'L');    
                    }
                    $pdf::Cell(70,7,'',1,0,'C');
                   
                    $pdf::Cell(18,7,'',1,0,'C');
                    $pdf::Cell(18,7,number_format(0,2,'.',''),1,0,'R');
                    $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                    $pdf::Cell(20,7,'-',1,0,'L');
                    //substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno
                    $pdf::Ln();
                    $anuladas=$anuladas + number_format(0,2,'.','');
                    
                }

                // exit();
            }
            $pdf::Ln();
            if (!isset($responsable)) {
                $responsable="CAJA VACIA";
            }
            $resp=Movimiento::where('caja_id','=',$caja_id)->where('conceptopago_id','=','2')->select('movimiento.*')->orderBy('id','desc')->limit(1)->first();
            $pdf::Cell(120,7,('RESPONSABLE: '.$responsable." / HORA DE IMPRESION: ".date("d/m/Y H:i:s")),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(50,7,utf8_decode("RESUMEN DE CAJA"),1,0,'C');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("INGRESOS :"),1,0,'L');
            $pdf::Cell(20,7,number_format($ingreso,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Efectivo :"),1,0,'L');
            $pdf::Cell(20,7,number_format($efectivo,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Master :"),1,0,'L');
            $pdf::Cell(20,7,number_format($master,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Visa :"),1,0,'L');
            $pdf::Cell(20,7,number_format($visa,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("EGRESOS :"),1,0,'L');
            $pdf::Cell(20,7,number_format($egreso,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("SALDO :"),1,0,'L');
            $pdf::Cell(20,7,number_format($ingreso - $egreso - $visa - $master,2,'.',''),1,0,'R');
            $pdf::Ln();

            if ($caja_id == 4) {
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(50,7,utf8_decode("VENTAS O SALIDAS:"),0,0,'L');
                $pdf::Ln();
                $pdf::Cell(28,7,"FECHA",1,0,'C');
                $pdf::Cell(65,7,"PERSONA",1,0,'C');
                $pdf::Cell(22,7,"TIPO DOC.",1,0,'C');
                $pdf::Cell(18,7,"NRO DOC.",1,0,'C');
                $pdf::Cell(80,7,"PRODUCTO",1,0,'C');
                $pdf::Cell(15,7,"CANT.",1,0,'C');
                $pdf::Cell(25,7,"TARIFA",1,0,'C');
                $pdf::Cell(20,7,"IMPORTE",1,0,'C');
                $pdf::Ln();

                $resultado1       = Movimiento::join('detallemovimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')
                            ->leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('producto','producto.id','=','detallemovimiento.producto_id')
                            ->where('movimiento.serie', '=', $serie)
                            ->whereIn('movimiento.tipodocumento_id', [4,5,15,9,11])
                            ->where('movimiento.id', '>=', $movimiento_mayor);
                $resultado1       = $resultado1->select('movimiento.created_at','producto.nombre as producto','detallemovimiento.cantidad','detallemovimiento.precio','detallemovimiento.subtotal','movimiento.serie','movimiento.numero','movimiento.tipodocumento_id','movimiento.tipoventa','movimiento.conveniofarmacia_id',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'))->orderBy('movimiento.numero', 'asc');
                $lista = $resultado1->get();
                foreach ($lista as $key => $value) {
                    if($value->situacion=='A'){//NOTA DE CREDITO
                        $pdf::SetTextColor(215,57,37);
                    }elseif($value->situacion=='U'){//ANULADA
                        $pdf::SetTextColor(48,215,37);
                    }else{
                        $pdf::SetTextColor(0,0,0);
                    }

                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(28,7,date("d/m/Y H:i:s",strtotime($value->created_at)),1,0,'C');
                    $pdf::Cell(65,7,substr($value->paciente2,0,40),1,0,'L');
                    $pdf::Cell(22,7,($value->tipodocumento_id==5?'BOLETA':($value->tipodocumento_id==4?'FACTURA':($value->tipodocumento_id==15?'GUIA INTERNA':($value->tipodocumento_id==11?'NC COMPRA':'SALIDA INT.')))),1,0,'C');
                    $pdf::Cell(18,7,$value->serie.'-'.$value->numero,1,0,'L');
                    $pdf::Cell(80,7,$value->producto,1,0,'L');
                    $pdf::Cell(15,7,$value->cantidad,1,0,'C');
                    if($value->conveniofarmacia_id!=NULL && $value->conveniofarmacia_id>0){
                        $convenio = conveniofarmacia::find($value->conveniofarmacia_id)->nombre;
                    }else{
                        $convenio="PARTICULAR";
                    }
                    $pdf::Cell(25,7,$convenio,1,0,'C');
                    $pdf::Cell(20,7,$value->subtotal,1,0,'C');
                    $pdf::Ln();

                }

                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(50,7,utf8_decode("COMPRAS O INGRESOS:"),0,0,'L');
                $pdf::Ln();
                $pdf::Cell(28,7,"FECHA",1,0,'C');
                $pdf::Cell(65,7,"PROVEEDOR",1,0,'C');
                $pdf::Cell(22,7,"TIPO DOC.",1,0,'C');
                $pdf::Cell(18,7,"NRO DOC.",1,0,'C');
                $pdf::Cell(80,7,"PRODUCTO",1,0,'C');
                $pdf::Cell(15,7,"CANT.",1,0,'C');
                $pdf::Cell(20,7,"IMPORTE",1,0,'C');
                $pdf::Ln();

                $resultado1       = Movimiento::join('detallemovimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')
                            ->leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('producto','producto.id','=','detallemovimiento.producto_id')
                            ->whereIn('movimiento.tipodocumento_id', [6,7,10,12,13,8])
                            ->where('movimiento.id', '>=', $movimiento_mayor);
                $resultado1       = $resultado1->select('movimiento.created_at','producto.nombre as producto','detallemovimiento.cantidad','detallemovimiento.precio','detallemovimiento.subtotal','movimiento.serie','movimiento.numero','movimiento.tipodocumento_id','movimiento.tipoventa','movimiento.conveniofarmacia_id',DB::raw('concat(paciente.bussinesname) as paciente2'))->orderBy('movimiento.numero', 'asc');
                $lista = $resultado1->get();
                foreach ($lista as $key => $value) {
                    if($value->situacion=='A'){//NOTA DE CREDITO
                        $pdf::SetTextColor(215,57,37);
                    }elseif($value->situacion=='U'){//ANULADA
                        $pdf::SetTextColor(48,215,37);
                    }else{
                        $pdf::SetTextColor(0,0,0);
                    }
                    $pdf::SetFont('helvetica','',8);
                    $pdf::Cell(28,7,date("d/m/Y H:i:s",strtotime($value->created_at)),1,0,'C');
                    $pdf::Cell(65,7,substr($value->paciente2,0,40),1,0,'L');
                    $pdf::Cell(22,7,($value->tipodocumento_id==6?'FACTURA':($value->tipodocumento_id==7?'BOLETA':($value->tipodocumento_id==10?'GUIA INTERNA':($value->tipodocumento_id==12?'TICKET':($value->tipodocumento_id==13?'NC VENTA':'INGRESO INT.'))))),1,0,'C');
                    $pdf::Cell(18,7,$value->serie.'-'.$value->numero,1,0,'L');
                    $pdf::Cell(80,7,$value->producto,1,0,'L');
                    $pdf::Cell(15,7,$value->cantidad,1,0,'C');
                    $pdf::Cell(20,7,$value->subtotal,1,0,'C');
                    $pdf::Ln();

                }

            }
            /*$pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("GARANTIA :"),1,0,'L');
            $pdf::Cell(20,7,number_format($garantia,2,'.',''),1,0,'R');*/
            $pdf::Ln();
            $pdf::Output('ListaCaja.pdf');
        }
    }


    public function pdfDetalleCierreF(Request $request){
        $caja                = Caja::find($request->input('caja_id'));
        $f_inicial           = $request->input('fi');
        $f_final             = $request->input('ff');

        $aperturas = array();
        $cierres = array();
        $negativo = 0;
        $caja_id          = Libreria::getParam($request->input('caja_id'),'1');
        
        
        $rst        = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',1)->where('movimiento.fecha', '>=', $f_inicial)->where('movimiento.fecha', '<=', $f_final)->orderBy('id','ASC')->get();
        
        if(count($rst)>0){
            foreach ($rst as $key => $rvalue) {
                array_push($aperturas,$rvalue->id);
                $svalue       = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',2)->where('movimiento.fecha', '>=', $f_inicial)->where('movimiento.fecha', '<=', $f_final)
                ->where('movimiento.id', '>=', $rvalue->id)
                ->orderBy('id','ASC')->first();
                if(!is_null($svalue)){
                    array_push($cierres,$svalue->id);
                }else{
                    array_push($cierres,0);
                }
            }
        }else{
            $movimiento_mayor = 0;
        }


        // dd($aperturas,$cierres);
        
        $vmax = sizeof($aperturas);

        
        $pdf = new MTCPDF();
        $pdf::SetTitle('Detalle Cierre General');
        $pdf::setFooterCallback(function($pdf) {
                $pdf->SetY(-15);
                // Set font
                $pdf->SetFont('helvetica', 'I', 8);
                // Page number
                $pdf->Cell(0, 10, 'Pag. '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        });

        $caja = Caja::find($request->input('caja_id'));
        if($caja->estado=="A"){
            $vmax = $vmax - 1;
        }
        // dd($aperturas,$cierres); 
        //LA ULTIMA APERTURA DE CAJA HA SIDO OBVIADA AL PROGRAMAR EL REPORTE, ALGUN PROBLEMA DESCOMENTAR EL -1
        for ($valor=0; $valor < $vmax; $valor++) {
            // echo $aperturas[$valor].' - '.$cierres[$valor].' ';exit();
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                ->where('movimiento.caja_id', '=', $caja_id)
                ->where(function ($query) use($aperturas,$cierres,$valor) {
                    $query->where(function($q) use($aperturas,$cierres,$valor){
                            $q->where('movimiento.id', '>', $aperturas[$valor])
                            ->where('movimiento.id', '<', $cierres[$valor])
                            ->whereNull('movimiento.cajaapertura_id');
                    })->orwhere(function ($query1) use($aperturas,$cierres,$valor){
                        $query1->where('movimiento.cajaapertura_id','=',$aperturas[$valor]);
                        });//normal
                })
                // ->whereIn('movimiento.id', ['623686','623685','623671','623670'])
                ->where(function($query){
                    $query
                        ->whereNotIn('movimiento.conceptopago_id',[31])
                        ->orWhere('m2.situacion','<>','R');
                })
                ->where('movimiento.situacion', '<>', 'A')/*->where('movimiento.id','=','603873')*/->where('movimiento.situacion', '<>', 'R');
            $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','responsable.nombres as responsable2')->orderBy('conceptopago.tipo', 'asc')->orderBy('conceptopago.orden', 'asc')->orderBy('conceptopago.id', 'asc')->orderBy('movimiento.tipotarjeta', 'asc')->orderBy('movimiento.numero', 'asc');
            $listConcepto     = array();
            $listConcepto2     = array();
            $listConcepto3     = array();
            $listConcepto4     = array();
            $listtarjeta      = array();
            $listtarjeta      = array();
            $listConcepto[]   = 6;//TRANSF CAJA INGRESO
            $listConcepto[]   = 7;//TRANSF CAJA EGRESO
            $listConcepto2[]   = 8;//HONORARIOS MEDICOS
            $listConcepto[]   = 14;//TRANSF TARJETA EGRESO
            $listConcepto[]   = 15;//TRANSF TARJETA INGRESO
            $listConcepto[]   = 16;//TRANSF SOCIO EGRESO
            $listConcepto[]   = 17;//TRANSF SOCIO INGRESO
            $listConcepto3[]   = 24;//PAGO DE CONVENIO
            $listConcepto3[]   = 25;//PAGO DE SOCIO
            $listConcepto[]   = 20;//TRANSF BOLETEO EGRESO
            $listConcepto[]   = 21;//TRANSF BOLETEO INGRESO
            //$listConcepto[]   = 30;//DEVOLUCION GARANTÍA CONTROL REMOTO
            $listConcepto4[]   = 31;//TRANSF FARMACIA EGRESO
            $listConcepto4[]   = 32;//TRANSF FARMACiA INGRESO
            $lista            = $resultado->get();
            

            // dd($lista);

            //if($valor==1){dd($aperturas[$valor],$cierres[$valor],$lista);}
            
            // ->where('movimiento.id','=','542989')


            // dd(json_encode($lista));
            //if($aperturas[$valor]==290049){dd($lista);}

            if ($caja_id == 4) {
                $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                    ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                    ->leftjoin('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                    ->leftjoin('movimiento as m2','movimiento.id','=','m2.movimiento_id')
                    //->where('movimiento.serie', '=', $caja_id)
                    ->where('movimiento.estadopago', '=', 'PP')
                    ->where('movimiento.tipomovimiento_id', '=', '4')
                    ->where(function ($query) use($aperturas,$cierres,$valor) {
                        $query->where(function($q) use($aperturas,$cierres,$valor){
                                $q->where('movimiento.id', '>', $aperturas[$valor])
                                ->where('movimiento.id', '<', $cierres[$valor])
                                ->whereNull('movimiento.cajaapertura_id');
                        })
                              ->orwhere(function ($query1) use($aperturas,$cierres,$valor){
                                $query1->where('movimiento.cajaapertura_id','=',$aperturas[$valor]);
                                });//normal
                    })
                    ->whereNull('movimiento.tipo')
                    ->where('movimiento.situacion', '<>', 'U')
                    ->where('movimiento.situacion', '<>', 'R');
                                // ->where('movimiento.id','=','538910');
                $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2',DB::raw('CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) as paciente'),DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.id', 'desc');
                $listapendiente            = $resultado2->get();
            }

            
            if (isset($lista)) {
                $pdf::AddPage('L');
                $pdf::SetFont('helvetica','B',12);
                $pdf::Image('dist/img/logo.jpg',10,8,50,0);
                $pdf::Cell(0,15,utf8_decode("Detalle de Cierre de ".$caja->nombre." Desde ".$f_inicial. " hasta ".$f_final),0,0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(15,7,utf8_decode("FECHA"),1,0,'C');
                $pdf::Cell(60,7,utf8_decode("PERSONA"),1,0,'C');
                $pdf::Cell(20,7,utf8_decode("NRO"),1,0,'C');
                $pdf::Cell(40,7,utf8_decode("EMPRESA"),1,0,'C');
                $pdf::Cell(70,7,utf8_decode("CONCEPTO"),1,0,'C');
                $pdf::Cell(18,7,utf8_decode("EGRESO"),1,0,'C');
                $pdf::Cell(18,7,utf8_decode("INGRESO"),1,0,'C');
                $pdf::Cell(20,7,utf8_decode("TARJETA"),1,0,'C');
                $pdf::Cell(20,7,utf8_decode("DOCTOR"),1,0,'C');
                $pdf::Ln();
                if($caja_id==1){//ADMISION 1
                    $serie=3;
                }elseif($caja_id==2){//ADMISION 2
                    $serie=7;
                }elseif($caja_id==3){//CONVENIOS
                    $serie=8;
                }elseif($caja_id==5){//EMERGENCIA
                    $serie=9;
                }elseif($caja_id==4){//FARMACIA
                    $serie=4;
                }elseif($caja_id==8){//PROCEDIMIENTOS
                    $serie=5;
                }else{
                    $serie='';
                }
                $resultado1       = Movimiento::join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                ->leftjoin('movimiento as m2','movimiento.movimiento_id','=','m2.id')
                                ->leftjoin('person as paciente', 'paciente.id', '=', 'm2.persona_id')
                                ->where('movimiento.serie', '=', $serie)
                                ->where('movimiento.tipomovimiento_id', '=', 4)
                                //->where('movimiento.id', '>', $aperturas[$valor])
                                //->where('movimiento.id', '<', $cierres[$valor])
                                ->where(function ($query) use($aperturas,$cierres,$valor) {
                                    $query->where(function($q) use($aperturas,$cierres,$valor){
                                            $q->where('movimiento.id', '>', $aperturas[$valor])
                                            ->where('movimiento.id', '<', $cierres[$valor])
                                            ->whereNull('movimiento.cajaapertura_id');
                                    })
                                          ->orwhere(function ($query1) use($aperturas,$cierres,$valor){
                                            $query1->where('movimiento.cajaapertura_id','=',$aperturas[$valor]);
                                            });//normal
                                })
                                ->where('m2.situacion','like','B');
                                /*->whereNotIn('movimiento.id',function ($query) use ($aperturas,$valor,$cierres,$caja_id) {
                                    $query->select('movimiento_id')->from('movimiento')->where('id','>',$aperturas[$valor])->where('id','<',$cierres[$valor])->where('caja_id','=',$caja_id);
                                });*/
                $resultado1       = $resultado1->select('movimiento.*','m2.situacion as situacion2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'))->orderBy('movimiento.numero', 'asc');
                
                $lista1           = $resultado1->get();
                // dd($lista1);
                // dd($lista1,$serie);
                //if($valor==1){ dd($lista1);}
                //if($valor==0){ dd($resultado1->toSql(),$serie,$aperturas[$valor],$cierres[$valor]);}
                //ECHO $aperturas[$valor]."-".$cierres[$valor]."-";
            if ($caja_id == 4) {
                $pendiente = 0;
                foreach ($listapendiente as $key => $value) {
                    if($pendiente==0 && $value->tipodocumento_id != 15){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("PENDIENTE"),1,0,'L');
                        $pdf::Ln();
                    }
                    if ($value->tipodocumento_id != 15) {
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');

                        $nombrepaciente = '';
                        $nombreempresa = '-';
                        if ($value->persona_id !== NULL) {
                            //echo 'entro'.$value->id;break;
                            $nombrepaciente =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                        }else{
                            $nombrepaciente = trim($value->nombrepaciente);
                        }
                        if ($value->tipodocumento_id == 5) {
                            
                            
                        }else{
                            if ($value->empresa != null) {
                                $nombreempresa = trim($value->empresa->bussinesname);
                            }
                            
                        }
                        if(strlen($nombrepaciente)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($nombrepaciente),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($nombrepaciente),1,0,'L');    
                        }
                        //$venta= Movimiento::find($value->id);
                        $pdf::Cell(8,7,($value->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(12,7,$value->serie.'-'.$value->numero,1,0,'C');

                        $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                        if($value->servicio_id>0){
                            if(strlen($value->servicio->nombre)>35){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(70,3,$value->servicio->nombre,0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(70,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(70,7,$value->servicio->nombre,1,0,'L');    
                            }
                        }else{
                            $pdf::Cell(70,7,'MEDICINA',1,0,'L');    
                        }
                        $pdf::Cell(18,7,'',1,0,'C');
                        $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                        $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                        if ($value->doctor_id != null) {
                            $pdf::Cell(20,7,substr($value->doctor->nombres,0,1).'. '.$value->doctor->apellidopaterno,1,0,'L');

                        }else{
                           $pdf::Cell(20,7," - ",1,0,'L'); 
                        }
                        
                        $pdf::Ln();
                        //$pendiente=$pendiente + number_format($value->total,2,'.','');
                        $pendiente=$pendiente + round($value->total,2);
                    }
                    
                }
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($pendiente,2,'.',''),1,0,'R');
                $pdf::Ln();
            }
            

            if(count($lista1)>0){
                $pendiente=0;
                foreach($lista1 as $key1 => $value1){
                    /*$rs = Detallemovcaja::where("movimiento_id",'=',$value1->movimiento_id)->get();
                    foreach ($rs as $k => $v){*/
                        if($pendiente==0){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(281,7,utf8_decode("PENDIENTE"),1,0,'L');
                            $pdf::Ln();
                        }
                        $pdf::SetFont('helvetica','',7.5);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value1->fecha)),1,0,'C');
                        if($value1->tipodocumento_id==5){//BOLETA
                            $nombre=$value1->persona->apellidopaterno.' '.$value1->persona->apellidomaterno.' '.$value1->persona->nombres;
                        }else{
                            $nombre=$value1->paciente2;
                            $empresa=$value1->persona->bussinesname;
                        }
                        if(strlen($nombre)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($nombre),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($nombre),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($value1->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(12,7,$value1->serie.'-'.$value1->numero,1,0,'C');
                        if($value1->tipodocumento_id==5){
                            $ticket= Movimiento::find($value1->movimiento_id);
                            $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                        }else{
                            $pdf::Cell(40,7,substr($empresa,0,23),1,0,'L');
                        }
                        $v = Detallemovcaja::where("movimiento_id",'=',$value1->movimiento_id)->first();

                        if (isset($v->servicio_id)) {
                            if($v->servicio_id>0){
                                if(strlen($v->servicio->nombre)>35){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(70,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                        } else {
                            $pdf::Cell(70,7,"",1,0,'L');
                        }  
                        $pdf::Cell(18,7,'',1,0,'C');
                        //$pdf::Cell(18,7,number_format($v->cantidad*$v->pagohospital,2,'.',''),1,0,'R');
                        $pdf::Cell(18,7,number_format($value1->total,2,'.',''),1,0,'R');
                        $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                        
                        if (isset($v->persona->nombres)) {
                            $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                        }
                        $pdf::Ln();
                        //$pendiente=$pendiente + number_format($v->cantidad*$v->pagohospital,2,'.','');
                        //$pendiente=$pendiente + number_format($value1->total,2,'.','');
                        $pendiente=$pendiente + number_format($value1->total,2);
                    //}
                }
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($pendiente,2,'.',''),1,0,'R');
                $pdf::Ln();
            }
            //dd($lista);
            //if($aperturas[$valor]==301797){dd($lista);}
            $ingreso=0;$egreso=0;$transferenciai=0;$transferenciae=0;$garantia=0;$efectivo=0;$visa=0;$master=0;$pago=0;$tarjeta=0;$cobranza=0;$egreso1=0;$transferenciai=0;$cobranza=0;$ingresotarjeta=0;
            $bandpago=true;$bandegreso=true;$bandtransferenciae=true;$bandtarjeta=true;$bandtransferenciai=true;$bandcobranza=true;$bandingresotarjeta=true;

            // echo json_encode($listConcepto);
            // exit;
             // echo json_encode($lista);

            foreach ($lista as $key => $value){
                // echo json_encode();
                //print_r($value);
                // if($value->id = '603873')
                //     dd($value->conceptopago_id,$value->conceptopago->tipo, $listConcepto, $listConcepto2, $listConcepto3);
                if($ingreso==0){
                    $responsable=$value->responsable2;
                }
                if (!isset($responsable)) {
                    $responsable="CAJA VACIA";
                }
                if($value->conceptopago_id==3 && $value->tipotarjeta==''){
                    
                    if ($caja_id == 4) {
                        //echo $value->movimiento_id."<br />";
                        $rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))/*->whereNotNull('deleted_at')*/->get();
                        //echo $value->movimiento_id."|".$value->id."@";
                        // dd($rs);
                        foreach ($rs as $k => $v) {
                            $pdf::SetTextColor(0,0,0);
                            if($egreso1>0 && $bandegreso){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(205,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                                $bandegreso=false;
                                $pdf::Ln(); 
                            }
                            if($pago==0 && $value->tipodocumento_id!=15){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                                $pdf::Ln();
                            }
                            if ($value->tipodocumento_id !== 15) {
                                $pdf::SetFont('helvetica','',7);
                                $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');

                                $nombrepaciente = '';
                                $nombreempresa = '-';
                                if ($value->persona_id !== NULL) {
                                    //echo 'entro'.$value->id;break;
                                    $nombrepaciente =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                                }else{
                                    $nombrepaciente = trim($value->nombrepaciente);
                                }
                                if ($value->tipodocumento_id == 5) {
                                    
                                    
                                }else{
                                    if ($value->empresa_id != null) {
                                        $nombreempresa = trim($value->empresa->bussinesname);
                                    }
                                    
                                }
                                
                                if(strlen($nombrepaciente)>30){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(60,3,($nombrepaciente),0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(60,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(60,7,($nombrepaciente),1,0,'L');    
                                }
                                $venta= Movimiento::find($value->movimiento_id);
                                $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                                $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                                if ($venta->conveniofarmacia_id != null) {
                                    $nombreempresa = $venta->conveniofarmacia->nombre;
                                }
                                $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                                if($v->servicio_id>0){
                                    if(strlen($v->servicio->nombre)>35){
                                        $x=$pdf::GetX();
                                        $y=$pdf::GetY();                    
                                        $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                        $pdf::SetXY($x,$y);
                                        $pdf::Cell(70,7,"",1,0,'C');
                                    }else{
                                        $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                    }
                                }else{
                                    $pdf::Cell(70,7,$v->descripcion.'- MEDICINA',1,0,'L');    
                                }
                                $pdf::Cell(18,7,'',1,0,'C');
                                $pdf::Cell(18,7,number_format($v->movimiento->total,2,'.',''),1,0,'R');
                                $pdf::Cell(20,7,utf8_decode('-'),1,0,'C');
                                if ($venta->doctor_id != null) {
                                    $pdf::Cell(20,7,substr($venta->doctor->nombres,0,1).'. '.$venta->doctor->apellidopaterno,1,0,'L');

                                }else{
                                   $pdf::Cell(20,7," - ",1,0,'L'); 
                                }
                                
                                $pdf::Ln();
                                //$pago=$pago + number_format($v->movimiento->total,2,'.','');
                                $pago=$pago + round($v->movimiento->total,2);
                            }
                            
                        }
                    }else{
                        //PARA PAGO DE CLIENTE, BUSCO TICKET
                        /*$rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        foreach ($rs as $k => $v){*/

                            $pdf::SetTextColor(0,0,0);
                            if($transferenciae>0 && $bandtransferenciae){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(205,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($transferenciae,2,'.',''),1,0,'R');
                                $bandtransferenciae=false;
                                $pdf::Ln(); 
                            }
                            if($egreso1>0 && $bandegreso){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(205,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                                $bandegreso=false;
                                $pdf::Ln(); 
                            }
                            if($pago==0){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                                $pdf::Ln();
                                if($caja_id==3){
                                    $pdf::SetFont('helvetica','B',7);
                                    $pdf::Cell(223,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                                    $apert = Movimiento::find($aperturas[$valor]);
                                    $pdf::Cell(18,7,number_format($apert->total,2,'.',''),1,0,'R');
                                    $pago = $pago + round($apert->total,2);
                                    $ingreso = $ingreso + round($apert->total,2);
                                    $pdf::Ln();
                                }
                            }
                            $pdf::SetFont('helvetica','',7);
                            $venta= Movimiento::find($value->movimiento_id);
                            //if($aperturas[$valor]==290049){dd($venta);}
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($venta->fecha)),1,0,'C');
                            if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                            }
                           

                            $v = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->first();
                            $ticket= Movimiento::find($v->movimiento_id);
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                            if($v->servicio_id>0){
                                if (isset($v->servicio)) {
                                    if(strlen($v->servicio->nombre)>35){
                                        $x=$pdf::GetX();
                                        $y=$pdf::GetY();                    
                                        $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                        $pdf::SetXY($x,$y);
                                        $pdf::Cell(70,7,"",1,0,'C');
                                    }else{
                                        $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                    }
                                } else {
                                    $pdf::Cell(70,7,"",1,0,'L');
                                }
                                
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                            $pdf::Cell(18,7,'',1,0,'C');
                            //$pdf::Cell(18,7,number_format($v->cantidad*$v->pagohospital,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,number_format($venta->total,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode('-'),1,0,'C');
                            // dd($v);
                            if(!is_null($v->persona) /*&& !is_null($v->persona->apellidopaterno)*/)
                                $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                            
                            $pdf::Ln();
                            //$pago=$pago + number_format($v->cantidad*$v->pagohospital,2,'.','');
                            //$pago=$pago + number_format($venta->total,2,'.','');
                            $pago=$pago + round($venta->total,2);
                        //}
                    }
                }elseif($value->conceptopago_id==3 && $value->tipotarjeta!=''){//PARA PAGO DE CLIENTE, BUSCO TICKET CON TARJETA
                    // dd($value);

                    if ($caja_id == 4) {
                        $rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        foreach ($rs as $k => $v){
                            $pdf::SetTextColor(0,0,0);
                        
                            // echo $pago.'-'. $efectivo. '/';
                        
                            if($pago>0 && $bandpago){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                                $bandpago=false;
                                $pdf::Ln(); 
                            }

                            if($tarjeta==0){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(281,7,utf8_decode("TARJETA"),1,0,'L');
                                $pdf::Ln();
                            }
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            
                            $nombrepaciente = '';
                            $nombreempresa = '-';
                            if ($value->persona_id !== NULL) {
                                //echo 'entro'.$value->id;break;
                                $nombrepaciente =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                            }else{
                                $nombrepaciente = trim($value->nombrepaciente);
                            }
                            if ($value->tipodocumento_id == 5) {
                                
                                
                            }else{
                                $nombreempresa = trim($value->empresa->bussinesname);
                            }
                            if(strlen($nombrepaciente)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($nombrepaciente),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($nombrepaciente),1,0,'L');    
                            }
                            $venta= Movimiento::find($value->movimiento_id);
                            $ticket= Movimiento::find($v->movimiento_id);
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            if ($venta->conveniofarmacia_id != null) {
                                $nombreempresa = $venta->conveniofarmacia->nombre;
                            }
                            $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                            if($v->servicio_id>0){
                                if(strlen($v->servicio->nombre)>35){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(70,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                            $pdf::Cell(18,7,'',1,0,'C');
                            $tarjeta=$tarjeta + round($v->movimiento->total,2);
                            $pdf::Cell(18,7,number_format($v->movimiento->total,2,'.',''),1,0,'R');
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::SetFont('helvetica','',6);
                            $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(20,7,"",1,0,'C');
                            if (!is_null($venta->doctor) && $venta->doctor_id != null) {
                                $pdf::Cell(20,7,substr($venta->doctor->nombres,0,1).'. '.$venta->doctor->apellidopaterno,1,0,'L');

                            }else{
                               $pdf::Cell(20,7," - ",1,0,'L'); 
                            }
                            $pdf::Ln();
                            $pdf::SetFont('helvetica','',7);
                  
                            //$pago=$pago + number_format($v->movimiento->total,2,'.','');
                            
                        }
                    }else{
                        /*$rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        foreach ($rs as $k => $v){*/
                            $pdf::SetTextColor(0,0,0);
                            if($pago>0 && $bandpago){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                                $bandpago=false;
                                $pdf::Ln(); 
                            }
                            if($tarjeta==0){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(281,7,utf8_decode("TARJETA"),1,0,'L');
                                $pdf::Ln();
                            }
                            if($value->situacion<>'A'){
                                $pdf::SetTextColor(0,0,0);
                            }else{
                                $pdf::SetTextColor(255,0,0);
                            }
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            $venta= Movimiento::find($value->movimiento_id);
                            //$tarjeta=$tarjeta + number_format($value->total,2,'.','');
                            $tarjeta=$tarjeta + round($value->total,2);
                            if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                            }
                            $v = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->first();
                            $ticket= Movimiento::find($v->movimiento_id);
                            if(!is_null($venta)){
                                $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                                $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            }
                            $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                            
                            if($v->servicio_id>0){
                                if (isset($v->servicio)) {
                                    if(strlen($v->servicio->nombre)>35){
                                        $x=$pdf::GetX();
                                        $y=$pdf::GetY();                    
                                        $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                        $pdf::SetXY($x,$y);
                                        $pdf::Cell(70,7,"",1,0,'C');
                                    }else{
                                        $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                    }
                                } else {
                                    $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                            $pdf::Cell(18,7,'',1,0,'C');
                            //$pdf::Cell(18,7,number_format($v->cantidad*$v->pagohospital,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,number_format($venta->total,2,'.',''),1,0,'R');
                            $x=$pdf::GetX();
                            $y=$pdf::GetY(); 
                            $pdf::SetFont('helvetica','',6);
                            $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(20,7,"",1,0,'C');
                            $pdf::SetFont('helvetica','',7);                  
                            $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                            $pdf::Ln();   
                            if($value->total!=$venta->total){
                                $pdf::SetFont('helvetica','',7);
                                $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                                if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(60,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                                }
                                if(!is_null($venta)){
                                    $pdf::Cell(8,7,'R',1,0,'C');
                                    $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                                }
                                $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                                $pdf::Cell(70,7,'INGRESO POR DIFERENCIA TARJETA',1,0,'L');    
                                $pdf::Cell(18,7,'',1,0,'C');
                                $pdf::Cell(18,7,number_format($value->total-$venta->total,2,'.',''),1,0,'R');
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(20,7,"",1,0,'C');
                                $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                                $pdf::Ln();   
                            }
                        //}

                    }
                    
                }elseif(in_array($value->conceptopago_id, $listConcepto) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA EGRESOS
                    if($value->situacion2<>'R'){
                        $pdf::SetTextColor(0,0,0);
                        if($egreso1>0 && $bandegreso){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(205,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                            $bandegreso=false;
                            $pdf::Ln(); 
                        }
                        if($transferenciae==0){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(281,7,utf8_decode("TRANSFERENCIA"),1,0,'L');
                            $pdf::Ln();
                        }
                        if($value->situacion<>'A'){
                            $pdf::SetTextColor(0,0,0);
                        }else{
                            $pdf::SetTextColor(255,0,0);
                        }
                        $list=explode(",",$value->listapago);
                        // dd($list);

                        $transferenciae = $transferenciae + round($value->total,2);
                        for($c=0;$c<count($list);$c++){
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            $detalle = Detallemovcaja::find($list[$c]);
                            $ticket = Movimiento::where("id","=",$detalle->movimiento_id)->first();
                            $venta = Movimiento::where("movimiento_id","=",$detalle->movimiento_id)->first();
                            if(strlen($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            
                            if($venta->tipodocumento_id==4){
                                $pdf::Cell(40,7,$venta->persona->bussinesname,1,0,'L');
                            }else{
                                $pdf::Cell(40,7,"",1,0,'L');
                            }
                            if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                                $descripcion=$value->conceptopago->nombre.' - RH: '.$detalle->recibo;
                            }else{
                                $descripcion=$value->conceptopago->nombre;
                            }
                            if(strlen($descripcion)>40){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();
                                $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(70,7,"",1,0,'L');
                            }else{
                                $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                            }
                            if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                                $pdf::Cell(18,7,number_format($detalle->pagodoctor,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                            }elseif($value->conceptopago_id==16){//TRANSFERENCIA SOCIO
                                $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                            }elseif($value->conceptopago_id==20){//BOLETEO TOTAL
                                $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                            }elseif($value->conceptopago_id==14){//TARJETA
                                $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                                if(in_array($value->id, [359022,359023])){
                                    $egreso = $egreso - $detalle->pagotarjeta;
                                }//TRANSFERENCIA POR CHEQUE MANUAL DIA 09/01/19, USUARIO NELLY MONTEZA
                            }
                            $pdf::Ln();
                        }
                    }
                }elseif(in_array($value->conceptopago_id, $listConcepto) && $value->conceptopago->tipo=='I'){//CONCEPTOS QUE TIENEN LISTA INGRESOS
                    $pdf::SetTextColor(0,0,0);
                    if($pago>0 && $bandpago){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                        $bandpago=false;
                        $pdf::Ln(); 
                    }
                    if($tarjeta>0 && $bandtarjeta){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                        $bandtarjeta=false;
                        $pdf::Ln(); 
                    }
                    if($transferenciai==0){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("TRANSFERENCIA"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $transferenciai = $transferenciai + round($value->total,2);
                    // dd($value);
                    $list=explode(",",$value->listapago);
                    for($c=0;$c<count($list);$c++){
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        $detalle = Detallemovcaja::find($list[$c]);
                        // dd($detalle);
                        // echo json_encode($detalle);
                        $venta = Movimiento::where("movimiento_id","=",$detalle->movimiento_id)->first();
                        $ticket = Movimiento::find($detalle->movimiento_id);
                        if(!is_null($venta)){
                            if(strlen($ticket->persona->movimiento.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                        }
                        if($venta->tipodocumento_id==4){
                            $pdf::Cell(40,7,$venta->persona->bussinesname,1,0,'L');
                        }else{
                            $pdf::Cell(40,7,"",1,0,'L');
                        }
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $descripcion=$value->conceptopago->nombre.' - RH: '.$detalle->recibo;
                        }else{
                            $descripcion=$value->conceptopago->nombre;
                        }
                        if(strlen($descripcion)>40){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(70,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                        }
                        if($value->conceptopago_id==21 ){//BOLETEO TOTAL
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }elseif($value->conceptopago_id==17){// TRANSFERENCIA SOCIO
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }elseif($value->conceptopago_id==15){//TARJETA
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                            if(in_array($value->id, [359022,359023])){
                                $efectivo = $efectivo - $detalle->pagotarjeta;
                                // echo $efectivo;
                                $ingreso = $ingreso - $detalle->pagotarjeta;
                            }//TRANSFERENCIA POR CHEQUE MANUAL DIA 09/01/19, USUARIO NELLY MONTEZA
                        }
                        $pdf::Ln();   
                    }

                    // exit();

                }elseif(in_array($value->conceptopago_id, $listConcepto2) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA2
                    /*$pdf::SetTextColor(0,0,0);
                    if($egreso==0){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(279,7,utf8_decode("EGRESO"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $list=explode(",",$value->listapago);//print_r($value->listapago."-");
                    for($c=0;$c<count($list);$c++){
                        $detalle = Detallemovcaja::find($list[$c]);
                        $venta = Movimiento::where("movimiento_id","=",$detalle->movimiento_id)->first();
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        if(strlen($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(10,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                        $pdf::Cell(40,7,"",1,0,'L');
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $descripcion=$value->conceptopago->nombre.' - RH: '.$detalle->recibo;
                        }else{
                            $descripcion=$value->conceptopago->nombre;
                        }
                        if(strlen($descripcion)>40){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(70,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                        }
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $pdf::Cell(18,7,number_format($detalle->pagodoctor*$detalle->cantidad,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }else{//SOCIO
                            $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }
                        $pdf::Ln();   
                    }*/
                }elseif(in_array($value->conceptopago_id, $listConcepto3) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA3
                    $pdf::SetTextColor(0,0,0);
                    if($egreso1==0){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("EGRESO"),1,0,'L');
                        $pdf::Ln();
                    }
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                    }   
                    $pdf::Cell(8,7,'RH',1,0,'C');
                    if($value->voucher==""){
                        $list=explode(",",$value->listapago);
                        $detalle = Detallemovcaja::find($list[0]);
                        if($value->conceptopago_id==25)
                            $pdf::Cell(12,7,$detalle->recibo2,1,0,'C');
                        else
                            $pdf::Cell(12,7,$detalle->recibo,1,0,'C');
                    }else{
                        $pdf::Cell(12,7,$value->voucher,1,0,'C');
                    }
                    $pdf::Cell(40,7,"",1,0,'L');
                    $descripcion=$value->conceptopago->nombre;
                    if(strlen($descripcion)>40){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(70,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                    }
                    $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                    $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Ln();
                    $egreso1 = $egreso1 + round($value->total,2);
                }elseif($value->conceptopago_id==23 || $value->conceptopago_id == 32){//COBRANZA 
                    // dd($value);
                    if ($caja_id == 4 && $value->conceptopago_id == 32) {//print_r($value->id.'@');
                        if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }
                        if($tarjeta>0 && $bandtarjeta){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                            $bandtarjeta=false;
                            $pdf::Ln(); 
                        }
                        $listventas = Movimiento::where('movimientodescarga_id','=',$value->id)->get();
                        if($value->id==616375){
                            // dd($listventas);
                        }
                        if($cobranza==0 && $value->conceptopago->tipo=="I"){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(281,7,utf8_decode("COBRANZA"),1,0,'L');
                            $pdf::Ln();
                        }
                        foreach ($listventas as $key6 => $value6) {
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value6->fecha)),1,0,'C');
                            $nombrepersona = '-';
                            if ($value6->persona_id !== NULL) {
                                //echo 'entro'.$value6->id;break;
                                $nombrepersona =$value6->persona->apellidopaterno.' '.$value6->persona->apellidomaterno.' '.$value6->persona->nombres;

                            }else{
                                $nombrepersona = trim($value6->nombrepaciente);
                            }
                            if(strlen($nombrepersona)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($nombrepersona),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($value6->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$value6->serie.'-'.$value6->numero,1,0,'C');
                            $nombreempresa = '';
                            if ($value6->conveniofarmacia_id != null) {
                                $nombreempresa = $value6->conveniofarmacia->nombre;
                            }
                            $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                            $pdf::Cell(70,7,'MEDICINA',1,0,'L');
                            $pdf::Cell(18,7,'',1,0,'C');
                            if($value->situacion!="R")
                                $pdf::Cell(18,7,number_format($value6->total,2,'.',''),1,0,'R');
                            else
                                $pdf::Cell(18,7,' - ',1,0,'R');
                            if($value->tipotarjeta!=""){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                                $pdf::SetXY($x,$y);
                            }
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            if ($value6->doctor_id != null) {
                                $pdf::Cell(20,7,substr($value6->doctor->nombres,0,1).'. '.$value6->doctor->apellidopaterno,1,0,'L');

                            }else{
                               $pdf::Cell(20,7," - ",1,0,'L'); 
                            }
                            if($value->situacion!="R"){
                                // echo 'CR:' .$value6->total;
                                $cobranza=$cobranza + round($value6->total,2);
                            }
                            $pdf::Ln();
                        }
                    }elseif($caja_id == 4 && $value->conceptopago_id == 23){
                        if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }
                        if($tarjeta>0 && $bandtarjeta){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                            $bandtarjeta=false;
                            $pdf::Ln(); 
                        }
                        $listventas = Movimiento::where('movimiento_id','=',$value->id)->get();
                        if($cobranza==0 && $value->conceptopago->tipo=="I"){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(281,7,utf8_decode("COBRANZA"),1,0,'L');
                            $pdf::Ln();
                        }
                        foreach ($listventas as $key6 => $value6) {
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value6->fecha)),1,0,'C');
                            $nombrepersona = '-';
                            if ($value6->persona_id !== NULL) {
                                //echo 'entro'.$value6->id;break;
                                $nombrepersona =$value6->persona->apellidopaterno.' '.$value6->persona->apellidomaterno.' '.$value6->persona->nombres;

                            }else{
                                $nombrepersona = trim($value6->nombrepaciente);
                            }
                            if(strlen($nombrepersona)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($nombrepersona),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$value6->serie.'-'.$value6->numero,1,0,'C');
                            $nombreempresa = '';
                            if ($value6->conveniofarmacia_id != null) {
                                $nombreempresa = $value6->conveniofarmacia->nombre;
                            }
                            $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                            $pdf::Cell(70,7,'MEDICINA',1,0,'L');
                            $pdf::Cell(18,7,'',1,0,'C');
                            $pdf::Cell(18,7,number_format($value6->total,2,'.',''),1,0,'R');
                            if($value->tipotarjeta!=""){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                                $pdf::SetXY($x,$y);
                            }
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            if ($value6->doctor_id != null) {
                                $pdf::Cell(20,7,substr($value6->doctor->nombres,0,1).'. '.$value6->doctor->apellidopaterno,1,0,'L');

                            }else{
                               $pdf::Cell(20,7," - ",1,0,'L'); 
                            }
                            // echo 'C:' .$value6->total;
                            $cobranza=$cobranza + round($value6->total,2);
                            $pdf::Ln();
                        }
                    }else{

                        $pdf::SetTextColor(0,0,0);
                        if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }
                        if($tarjeta>0 && $bandtarjeta){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                            $bandtarjeta=false;
                            $pdf::Ln(); 
                        }
                        if($ingresotarjeta>0 && $bandingresotarjeta){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($ingresotarjeta,2,'.',''),1,0,'R');
                            $bandingresotarjeta=false;
                            $pdf::Ln(); 
                        }
                        if($cobranza==0 && $value->conceptopago->tipo=="I"){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(281,7,utf8_decode("COBRANZA"),1,0,'L');
                            $pdf::Ln();
                        }
                        if($value->situacion<>'A'){
                            $pdf::SetTextColor(0,0,0);
                        }else{
                            $pdf::SetTextColor(255,0,0);
                        }
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        $nombrepersona = '-';
                        $venta = Movimiento::find($value->movimiento_id);
                        if ($value->persona_id !== NULL) {
                            //echo 'entro'.$value->id;break;
                            if($venta->tipodocumento_id==5){
                                $nombrepersona =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                            }else{
                                $nombrepersona = $value->persona->bussinesname;
                            }
                        }else{
                            $nombrepersona = trim($value->nombrepaciente);
                        }
                        if(strlen($nombrepersona)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($nombrepersona),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':"F"),1,0,'C');
                        $pdf::Cell(12,7,utf8_decode($venta->serie.'-'.$venta->numero),1,0,'C');
                        if($value->conceptopago_id==11){//PAGO A ENFERMERIA
                            $descripcion=$value->conceptopago->nombre.': '.$value->comentario.' - RH: '.$value->voucher;
                        }else{
                            $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                        }
                        if(strlen($descripcion)>70){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(110,3,($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(110,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(110,7,($descripcion),1,0,'L');
                        }
                        if($value->situacion<>'R' && $value->situacion2<>'R'){
                            if($value->conceptopago->tipo=="I"){
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'R');
                                $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            }else{
                                $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            }
                        }else{
                            $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                            $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                        }
                        $cobranza=$cobranza + round($value->total,2);
                        if($value->tipotarjeta!=""){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                            $pdf::SetXY($x,$y);
                        }
                        $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                        $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                        $pdf::Ln();
                    }
                }elseif($value->conceptopago_id==33){//PAGO DE FARMACIA
                    $pdf::SetTextColor(0,0,0);
                    if($transferenciae>0 && $bandtransferenciae){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(205,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($transferenciae,2,'.',''),1,0,'R');
                        $transferenciae=false;
                        $pdf::Ln(); 
                    }
                    if($tarjeta>0 && $bandtarjeta){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                        $bandtarjeta=false;
                        $pdf::Ln(); 
                    }
                    if($pago>0 && $bandpago){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                        $bandpago=false;
                        $pdf::Ln(); 
                    }

                    if($ingresotarjeta==0){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("INGRESOS POR TRANSFERENCIA"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    $nombrepersona = '-';
                    if ($value->persona_id != NULL && !is_null($value->persona)) {
                        //echo 'entro'.$value->id;break;
                        if ($value->persona->bussinesname != null) {
                            $nombrepersona = $value->persona->bussinesname;
                        }else{
                            $nombrepersona =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                        }
                        
                    }else{
                        $nombrepersona = trim($value->nombrepaciente);
                    }
                    if(strlen($nombrepersona)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($nombrepersona),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                    }
                    if($value->conceptopago_id==31){
                        $pdf::Cell(8,7,'T',1,0,'C');
                    }else{
                        $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C'); 
                        
                    }
                    $pdf::Cell(12,7,utf8_decode(trim($value->voucher)==''?$value->numero:$value->voucher),1,0,'C');
                
                    $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                    
                    if(strlen($descripcion)>70){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(110,3,SUBSTR($descripcion,0,150),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(110,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(110,7,($descripcion),1,0,'L');
                    }
                    if($value->situacion<>'R' && $value->situacion2<>'R'){
                        if($value->conceptopago->tipo=="I"){
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'R');
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $ingresotarjeta=$ingresotarjeta + round($value->total,2);
                        }else{
                            $egreso1=$egreso1 + round($value->total,2);
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                        }
                    }else{
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                    }
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Ln();
                }elseif($value->conceptopago_id!=1 && $value->conceptopago_id!=2 && $value->conceptopago_id!=23 && $value->conceptopago_id!=10 && $value->conceptopago_id!=150){
                    // dd($value);
                    // echo json_encode($value);

                    $pdf::SetTextColor(0,0,0);
                    if($transferenciae>0 && $bandtransferenciae){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(205,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($transferenciae,2,'.',''),1,0,'R');
                        $transferenciae=false;
                        $pdf::Ln(); 
                    }
                    if(($ingreso==0 || $pago==0) && $value->conceptopago->tipo=="I"){
                        if($egreso1>0 && $bandegreso){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(205,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                            $bandegreso=false;
                            $pdf::Ln(); 
                        }
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                        $pdf::Ln();
                        if($pago==0){
                            if($caja_id==3){
                                $pdf::SetFont('helvetica','B',7);
                                $pdf::Cell(223,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                                $apert = Movimiento::find($aperturas[$valor]);
                                $pdf::Cell(18,7,number_format($apert->total,2,'.',''),1,0,'R');
                                $pago = $pago + round($apert->total,2);
                                $ingreso = $ingreso + round($apert->total,2);
                                $pdf::Ln();
                            }
                        }
                    }elseif($egreso1==0 && $value->conceptopago->tipo=="E"){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("EGRESOS"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');

                    
                    $nombrepersona = '-';
                    if ($value->persona_id != NULL && !is_null($value->persona)) {
                        //echo 'entro'.$value->id;break;
                        if ($value->persona->bussinesname != null) {
                            $nombrepersona = $value->persona->bussinesname;
                        }else{
                            $nombrepersona =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                        }
                        
                    }else{
                        $nombrepersona = trim($value->nombrepaciente);
                    }
                    if(strlen($nombrepersona)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($nombrepersona),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                    }
                    if($value->conceptopago_id!=13){
                        if($value->conceptopago_id==31){
                            $pdf::Cell(8,7,'T',1,0,'C');
                        }else{
                            if ($caja_id == 4) {
                                if ($value->tipodocumento_id == 7) {
                                    $pdf::Cell(8,7,'BV',1,0,'C');
                                }elseif($value->tipodocumento_id == 6){
                                    $pdf::Cell(8,7,'FT',1,0,'C');
                                }else{
                                    $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C'); 
                                }
                            }else{
                               $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C'); 
                            }
                            
                        }
                        $pdf::Cell(12,7,utf8_decode(trim($value->voucher)==''?$value->numero:$value->voucher),1,0,'C');
                    }else{//PARA ANULACION POR NOTA CREDITO
                        $pdf::Cell(8,7,'NA',1,0,'C');
                        //print_r($value->id);
                        $mov = Movimiento::find($value->movimiento_id);
                        // dd($mov,$value);
                        if(!is_null($mov)){
                            $pdf::Cell(12,7,($mov->serie.'-'.$mov->numero),1,0,'C');
                        }
                    }
                    if(empty($array)){
                        $array = array();
                    }
                    
                    if($value->voucher=="4-23142" || (!empty($mov) &&($mov->serie.'-'.$mov->numero)=="4-23142")){
                        $array[] = $value;
                            
                        }
                    if(count($array)>1){
                        //dd($array);
                    }
                    if($value->conceptopago_id==11){//PAGO A ENFERMERIA
                        $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                    }else{
                        $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                    }
                    if(strlen($descripcion)>70){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(110,3,SUBSTR($descripcion,0,150),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(110,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(110,7,($descripcion),1,0,'L');
                    }
                    if($value->situacion<>'R' && $value->situacion2<>'R'){
                        if($value->conceptopago->tipo=="I"){
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'R');
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pago=$pago + round($value->total,2);
                        }else{
                            $egreso1=$egreso1 + round($value->total,2);
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            // if($value->voucher = '4-243210')
                            //     dd($value->id, $value->conceptopago_id, $value->persona_id,$value->conceptopago->tipo, $listConcepto, $listConcepto2, $listConcepto3,$value->fecha, $value->total,$value->deleted_at,$value->voucher);

                        }
                    }else{
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                    }
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Ln();
                }
                
                if($value->conceptopago_id<>2 && $value->situacion<>'A'){
                    if($value->conceptopago->tipo=="I"){
                        if($value->conceptopago_id<>10 && $value->conceptopago_id<>150){//GARANTIA
                            if($value->conceptopago_id<>15 && $value->conceptopago_id<>17 && $value->conceptopago_id<>19 && $value->conceptopago_id<>21){
                                if ($value->tipodocumento_id != 15 && $value->conceptopago_id != 147) {
                                    //echo $value->total."@";205851
                                    $ingreso = $ingreso + round($value->total,2);
                                }
                                    
                            }elseif(($value->conceptopago_id==15 || $value->conceptopago_id==17 || $value->conceptopago_id==19 || $value->conceptopago_id==21) && $value->situacion=='C'){
                                $ingreso = $ingreso + round($value->total,2);    
                            }
                        }else{
                            $garantia = $garantia + $value->total;
                        }
                        if($value->conceptopago_id<>10 && $value->conceptopago_id<>150){//GARANTIA
                            if($value->tipotarjeta=='VISA'){
                                    $visa = $visa + round($value->total,2);
                            }elseif($value->tipotarjeta==''){
                                if ($value->tipodocumento_id != 15 && $value->conceptopago_id != 147) {
                                    $efectivo = $efectivo + round($value->total,2);
                                    // echo 'I:'.$value->total.',';
                                    // echo '01: ID:   '.$value->id.', TOTAL: '.$value->total.' F: '. $value->fecha.' S:'.$value->serie.'-'.$value->numero.
                                    // ' EF: '. $efectivo.'| ';
                                  
                                }
                            }else{
                                $master = $master + round($value->total,2);
                            }
                        }
                    }else{
                        if($value->conceptopago_id<>14 && $value->conceptopago_id<>16 && $value->conceptopago_id<>18 && $value->conceptopago_id<>20){
                            if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                                // echo '02_E:'. $efectivo.'|' ;
                                // echo 'E: Numero:'.$value->id.':::'.$value->total.',';
                            
                                if($efectivo == 0){
                                    $negativo =  round($value->total,2);
                                }
                                // AQUI VA EL CAMBIO -- ERICK
                                // if($value->id != 641734){
                                    $ingreso  = $ingreso - round($value->total,2);
                                    $efectivo = $efectivo - round($value->total,2);                                    
                                // }
                                // }
                                // echo '02:' .json_encode($value->total) .' EF: '. $efectivo.'|' ;
                            }else{
                                $egreso  = $egreso + round($value->total,2);
                            }
                        }elseif(($value->conceptopago_id==14 || $value->conceptopago_id==16 || $value->conceptopago_id==18 || $value->conceptopago_id==20) && $value->situacion2=='C'){
                            $egreso  = $egreso + round($value->total,2);
                        }
                    }
                }
                $res=$value->responsable2;
                if ($caja_id == 4) {
                    /*if($tarjeta>0 && $bandtarjeta){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                        $bandtarjeta=false;
                        $pdf::Ln(); 
                    }*/
                }
            }
            if($ingresotarjeta>0 && $bandingresotarjeta){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($ingresotarjeta,2,'.',''),1,0,'R');
                $bandingresotarjeta=false;
                $pdf::Ln(); 
            }
            if($cobranza>0 && $bandcobranza){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($cobranza,2,'.',''),1,0,'R');

                // echo $cobranza. ' ** '. $efectivo.'//';
                          


                $bandpago=false;
                $pdf::Ln(); 
            }
            if($transferenciai>0 && $bandtransferenciai){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($transferenciai,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($pago==0){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                $pdf::Ln();
                if($caja_id==3){
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(223,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                    $apert = Movimiento::find($aperturas[$valor]);
                    $pdf::Cell(18,7,number_format($apert->total,2,'.',''),1,0,'R');
                    $pago = $pago + round($apert->total,2);
                    $ingreso = $ingreso + round($apert->total,2);
                    $pdf::Ln();
                }
            }
            if($pago>0 && $bandpago){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($tarjeta>0 && $bandtarjeta){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                $bandtarjeta=false;
                $pdf::Ln(); 
            }

            $resultado1       = Movimiento::join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                ->leftjoin('movimiento as m2','movimiento.movimiento_id','=','m2.id')
                                ->leftjoin('person as paciente', 'paciente.id', '=', 'm2.persona_id')
                                ->where('movimiento.serie', '=', $serie)
                                ->where('movimiento.tipomovimiento_id', '=', 4)
                                ->where('movimiento.tipodocumento_id', '<>', 15)
                                ->where(function ($query) use($aperturas,$cierres,$valor) {
                                    $query->where(function($q) use($aperturas,$cierres,$valor){
                                            $q->where('movimiento.id', '>', $aperturas[$valor])
                                            ->where('movimiento.id', '<', $cierres[$valor])
                                            ->whereNull('movimiento.cajaapertura_id');
                                    })
                                          ->orwhere(function ($query1) use($aperturas,$cierres,$valor){
                                            $query1->where('movimiento.cajaapertura_id','=',$aperturas[$valor]);
                                            });//normal
                                })
                                ->where('movimiento.situacion','like','U');
            $resultado1       = $resultado1->select('movimiento.*','m2.situacion as situacion2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'))->orderBy('movimiento.numero', 'asc');
            
            $lista1           = $resultado1->get();
            if(count($lista1)>0){
                //echo 'alert('.count($lista1).')';
                $anuladas=0;
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(281,7,utf8_decode("ANULADAS"),1,0,'L');
                $pdf::Ln();
                // dd($lista1);

                foreach($lista1 as $key1 => $value1){
                    // dd($value1->movimiento_id);

                    $pdf::SetFont('helvetica','',7.5);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value1->fecha)),1,0,'C');
                    if($value1->tipodocumento_id==5){//BOLETA}
                        // $nombre='ANULADO';
                        $nombre=$value1->paciente2;
                        //$value1->persona->apellidopaterno.' '.$value1->persona->apellidomaterno.' '.$value1->persona->nombres;
                    }else{
                        $nombre=$value1->paciente2;
                        if($value1->persona_id>0){
                            $empresa=$value1->persona->bussinesname;
                        }else{
                            $empresa='';
                        }
                    }
                    if(strlen($nombre)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($nombre),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($nombre),1,0,'L');    
                    }
                    $pdf::Cell(8,7,($value1->tipodocumento_id==5?'B':'F'),1,0,'C');
                    $pdf::Cell(12,7,$value1->serie.'-'.$value1->numero,1,0,'C');
                    if($caja_id==4){
                        $nombreempresa='-';
                        if ($value->tipodocumento_id != 5) {
                            if ($value->empresa != null) {
                                $nombreempresa = trim($value->empresa->bussinesname);
                            }
                        }
                        $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                    }else{
                        if($value1->tipodocumento_id==5){
                            if($value1->movimiento_id>0){
                            $ticket= Movimiento::find($value1->movimiento_id);
                            if($ticket->plan_id>0)
                                $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                            else
                                $pdf::Cell(40,7,"",1,0,'L');
                            }else{
                                $pdf::Cell(40,7,"",1,0,'L');
                            }
                        }else{
                            $pdf::Cell(40,7,substr($empresa,0,23),1,0,'L');
                        }
                    }

                    if($caja_id==4){
                        $pdf::Cell(70,7,"MEDICINA",1,0,'L');    
                    }else{
                        
                        if($value1->movimiento_id !== ''){
                           $detalles = Detallemovcaja::join('servicio','servicio.id','=','detallemovcaja.servicio_id')->where('detallemovcaja.movimiento_id','=',$value1->movimiento_id)->select('servicio.nombre')->get();

                           $detalles_serv_cadena = '';
                        
                           foreach($detalles as $key2 => $value2){
                                $detalles_serv_cadena.=$value2->nombre;
                                break;
                           }                            

                           if($detalles_serv_cadena !== ''){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(70,7,$detalles_serv_cadena,0,'L');
                                $pdf::SetXY($x,$y);
                           }else{
                               $detalles = Detallemovcaja::where('detallemovcaja.movimiento_id','=',$value1->movimiento_id)->select('descripcion')->get();

                               $detalles_serv_cadena = '';
                            
                               foreach($detalles as $key2 => $value2){
                                    $detalles_serv_cadena.=$value2->descripcion;
                                    break;
                               }

                                // $pdf::SetFont('helvetica','',6);
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(70,7,$detalles_serv_cadena,0,'L');
                                $pdf::SetXY($x,$y);   
                            
                            }
                           

                            // $pdf::SetFont('helvetica','',6);
                        
                           // $pdf::Cell(88,7,$detalles_serv_cadena,1,0,'L');    
                        }
                    }

                    // $pdf::SetFont('helvetica','',7.5);
                    $pdf::Cell(70,7,'',1,0,'C');
                    $pdf::Cell(18,7,'',1,0,'R');
                    $pdf::Cell(18,7,number_format(0,2,'.',''),1,0,'R');
                    $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                    $pdf::Cell(20,7,'-',1,0,'L');
                    //substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno
                    $pdf::Ln();
                    $anuladas=$anuladas + number_format(0,2,'.','');
                }
            }
            $resp01=Movimiento::find($aperturas[$valor]);
            $resp=Movimiento::find($cierres[$valor]);
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(120,7,('RESPONSABLE: '.$resp->responsable->nombres)."/ Hora Apertura: " .date("d/m/Y H:i:s",strtotime($resp01->created_at)). "/ Hora Cierre: ".date("d/m/Y H:i:s",strtotime($resp->created_at)),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(50,7,utf8_decode("RESUMEN DE CAJA"),1,0,'C');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("INGRESOS :"),1,0,'L');
            
            // echo ' TOTAL FINAL:'. $efectivo .' - '.$resp->responsable->nombres;
            // exit();
            // echo $cobranza . '-'. $ingreso;
            // $efectivo = $cobranza + $pago;
            if($efectivo==5786.30){
                $ingreso = $ingreso + 0.01;
                $efectivo = $efectivo + 0.01;
                //$egreso = round($egreso,2);
            }

            // if($negativo != 0){
            //     $efectivo = $efectivo+$negativo;
            // }
            // echo 'I:' . $ingreso. 'T:' .$transferenciai;
            // exit();
            // echo('I:'.$ingreso.', T:'. $transferenciai.' TOTAL:' .$ingreso+$transferenciai.' /');
            $pdf::Cell(20,7,number_format($ingreso + $transferenciai,2,'.',''),1,0,'R');
            $pdf::Ln();
            /*$pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("INGRESOS(T) :"),1,0,'L');
            $pdf::Cell(20,7,number_format($transferenciai,2,'.',''),1,0,'R');
            $pdf::Ln();*/
            // echo $efectivo;
            // dd($efectivo);

            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Efectivo :"),1,0,'L');
            $pdf::Cell(20,7,number_format($efectivo,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Master :"),1,0,'L');
            $pdf::Cell(20,7,number_format($master,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Visa :"),1,0,'L');
            $pdf::Cell(20,7,number_format($visa,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("EGRESOS :"),1,0,'L');
            $pdf::Cell(20,7,number_format($egreso,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("SALDO :"),1,0,'L');
            $pdf::Cell(20,7,number_format($ingreso - $egreso - $visa - $master
            /*$efectivo - $egreso*/,2,'.',''),1,0,'R');
            $pdf::Ln();

            // $ingreso - $egreso - $visa - $master

            // - $visa - $master
            //$pdf::Output('ListaCaja.pdf');
                
            }

        }
        
        // exit();  
        $pdf::Output('ListaCaja.pdf');
    }

     public function pdfDetalleCierreF02(Request $request){
        $caja                = Caja::find($request->input('caja_id'));
        $caja_id          = Libreria::getParam($request->input('caja_id'),'1');
        $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->orderBy('movimiento.id','DESC')->limit(1)->first();
        if(count($rst)==0){
            $conceptopago_id=2;
        }else{
            $conceptopago_id=$rst->conceptopago_id;
        }
        
        $rst              = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',1)->orderBy('id','DESC')->limit(1)->first();
        if(count($rst)>0){
            $movimiento_mayor = $rst->id;    
        }else{
            $movimiento_mayor = 0;
        }
        
        // $movimiento_mayor = '635738';
        // dd($movimiento_mayor);
        

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            ->where('movimiento.id', '>', $movimiento_mayor)
                            ->whereNull('movimiento.cajaapertura_id')
                            ->where(function($query){
                                $query
                                    ->whereNotIn('movimiento.conceptopago_id',[31])
                                    ->orWhere('m2.situacion','<>','R');
                            })
                            ->where('movimiento.situacion', '<>', 'A')
                            ->where('movimiento.situacion', '<>', 'R');
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','responsable.nombres as responsable2')->orderBy('conceptopago.tipo', 'asc')->orderBy('conceptopago.orden', 'asc')->orderBy('conceptopago.id', 'asc')->orderBy('movimiento.tipotarjeta', 'asc')->orderBy('movimiento.numero', 'asc');
        $listConcepto     = array();
        $listConcepto2     = array();
        $listConcepto3     = array();
        $listConcepto4     = array();
        $listConcepto[]   = 6;//TRANSF CAJA INGRESO
        $listConcepto[]   = 7;//TRANSF CAJA EGRESO
        $listConcepto2[]   = 8;//HONORARIOS MEDICOS
        $listConcepto[]   = 14;//TRANSF TARJETA EGRESO
        $listConcepto[]   = 15;//TRANSF TARJETA INGRESO
        $listConcepto[]   = 16;//TRANSF SOCIO EGRESO
        $listConcepto[]   = 17;//TRANSF SOCIO INGRESO
        $listConcepto3[]   = 24;//PAGO DE CONVENIO
        $listConcepto3[]   = 25;//PAGO DE SOCIO
        $listConcepto[]   = 20;//TRANSF BOLETEO EGRESO
        $listConcepto[]   = 21;//TRANSF BOLETEO INGRESO
        $listConcepto4[]   = 31;//TRANSF FARMACIA EGRESO
        $listConcepto4[]   = 32;//TRANSF FARMACiA INGRESO
        $lista            = $resultado->get();
        $listapendiente = array();
        if ($caja_id != 6) {
            $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','movimiento.id','=','m2.movimiento_id')
                            ->where('movimiento.ventafarmacia', '=', 'S')
                            ->where('movimiento.estadopago', '=', 'PP')
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            ->where('movimiento.situacion', '<>', 'A')
                            ->where('movimiento.situacion', '<>', 'U')->where('movimiento.situacion', '<>', 'R')
                            ->whereNull('movimiento.cajaapertura_id');
            $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2',DB::raw('CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) as paciente'),DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.id', 'desc');
            $listapendiente            = $resultado2->get();
        }
        if (isset($lista)) {            
            $pdf = new TCPDF();
            //$pdf::SetImaº
            $pdf::SetTitle('Detalle Cierre de '.$caja->nombre);
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(0,10,utf8_decode("Detalle de Cierre de ".$caja->nombre),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(15,7,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(60,7,utf8_decode("PERSONA"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(40,7,utf8_decode("EMPRESA"),1,0,'C');
            $pdf::Cell(70,7,utf8_decode("CONCEPTO"),1,0,'C');
            $pdf::Cell(18,7,utf8_decode("EGRESO"),1,0,'C');
            $pdf::Cell(18,7,utf8_decode("INGRESO"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("TARJETA"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("DOCTOR"),1,0,'C');
            $pdf::Ln();
            if($caja_id==1){//ADMISION 1
                $serie=3;
            }elseif($caja_id==2){//ADMISION 2
                $serie=7;
            }elseif($caja_id==3){//CONVENIOS
                $serie=8;
            }elseif($caja_id==5){//EMERGENCIA
                $serie=9;
            }elseif($caja_id==4){//FARMACIA
                $serie=4;
            }elseif($caja_id==8){//PROCEDIMIENTOS
                $serie=5;
            }
            $resultado1       = Movimiento::join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','movimiento.movimiento_id','=','m2.id')
                            ->leftjoin('person as paciente', 'paciente.id', '=', 'm2.persona_id')
                            ->where('movimiento.serie', '=', $serie)
                            ->where('movimiento.tipomovimiento_id', '=', 4)
                            ->where('movimiento.situacion', '=', 'P')
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            ->where('movimiento.situacion', '<>', 'U')
                            ->where('movimiento.situacion', '<>', 'A')
                            ->where('movimiento.situacion', '<>', 'R');
            $resultado1       = $resultado1->select('movimiento.*','m2.situacion as situacion2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'responsable.nombres as responsable2')->orderBy('movimiento.numero', 'asc');
            
            $lista1           = $resultado1->get();
            if ($caja_id == 4) {
                $pendiente = 0;

                foreach ($listapendiente as $key => $value) { 
                    if($pendiente==0 && $value->tipodocumento_id != 15){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(281,7,utf8_decode("PENDIENTE"),1,0,'L');
                        $pdf::Ln();
                    }

                    if ($value->tipodocumento_id != 15) {
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');

                        $nombrepaciente = '';
                        $nombreempresa = '-';
                        if ($value->persona_id !== NULL) {
                            //echo 'entro'.$value->id;break;
                            $nombrepaciente =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                        }else{
                            $nombrepaciente = trim($value->nombrepaciente);
                        }
                        if ($value->tipodocumento_id == 5) {
                            
                            
                        }else{
                            if ($value->empresa_id != null) {
                                $nombreempresa = trim($value->empresa->bussinesname);
                            }
                        }
                        if(strlen($nombrepaciente)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($nombrepaciente),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($nombrepaciente),1,0,'L');    
                        }
                        //$venta= Movimiento::find($value->id);
                        $pdf::Cell(8,7,($value->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(12,7,$value->serie.'-'.$value->numero,1,0,'C');

                        if ($value->conveniofarmacia_id != null) {
                            $nombreempresa = $value->conveniofarmacia->nombre;
                        }

                        $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                        if($value->servicio_id>0){
                            if(strlen($value->servicio->nombre)>35){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(70,3,$value->servicio->nombre,0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(70,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(70,7,$value->servicio->nombre,1,0,'L');    
                            }
                        }else{
                            $pdf::Cell(70,7,'MEDICINA',1,0,'L');    
                        }
                        $pdf::Cell(18,7,'',1,0,'C');
                        $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                        $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                        if ($value->doctor_id != null) {
                            $pdf::Cell(20,7,substr($value->doctor->nombres,0,1).'. '.$value->doctor->apellidopaterno,1,0,'L');

                        }else{
                           $pdf::Cell(20,7," - ",1,0,'L'); 
                        }
                        
                        $pdf::Ln();
                        $pendiente=$pendiente + number_format($value->total,2,'.','');
                    }

                }
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($pendiente,2,'.',''),1,0,'R');
                $pdf::Ln();
            }
            

            if(count($lista1)>0){
                $pendiente=0;
                foreach($lista1 as $key1 => $value1){
                    $rs = Detallemovcaja::where("movimiento_id",'=',$value1->movimiento_id)->get();
                    foreach ($rs as $k => $v){
                        if($pendiente==0){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(281,7,utf8_decode("PENDIENTE"),1,0,'L');
                            $pdf::Ln();
                        }
                        $pdf::SetFont('helvetica','',7.5);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value1->fecha)),1,0,'C');
                        if($value1->tipodocumento_id==5){//BOLETA
                            $nombre=$value1->persona->apellidopaterno.' '.$value1->persona->apellidomaterno.' '.$value1->persona->nombres;
                        }else{
                            $nombre=$value1->paciente2;
                            $empresa=$value1->persona->bussinesname." / ";
                        }
                        if(strlen($nombre)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($nombre),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($nombre),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($value1->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(12,7,$value1->serie.'-'.$value1->numero,1,0,'C');
                        $ticket= Movimiento::find($value1->movimiento_id);
                        if($value1->tipodocumento_id==5){//BOLETA
                            $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');    
                        }else{
                            $pdf::Cell(40,7,substr($empresa,0,23),1,0,'L');
                        }
                        if($v->servicio_id>0){
                            if(strlen($v->servicio->nombre)>35){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(70,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                            }
                        }else{
                            $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                        }
                        $pdf::Cell(18,7,'',1,0,'C');
                        $pdf::Cell(18,7,number_format($v->cantidad*$v->pagohospital,2,'.',''),1,0,'R');
                        $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                        $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                        $pdf::Ln();
                        $pendiente=$pendiente + number_format($v->cantidad*$v->pagohospital,2,'.','');
                    }
                }
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($pendiente,2,'.',''),1,0,'R');
                $pdf::Ln();
            }

            $ingreso=0;$egreso=0;$transferenciai=0;$transferenciae=0;$garantia=0;$efectivo=0;$visa=0;$master=0;$pago=0;$tarjeta=0;$cobranza=0;$egreso1=0;$transferenciai=0;$cobranza=0;$ingresotarjeta=0;
            $bandpago=true;$bandegreso=true;$bandtransferenciae=true;$bandtarjeta=true;$bandtransferenciai=true;$bandcobranza=true;$bandingresotarjeta=true;
            foreach ($lista as $key => $value){
                if($ingreso==0){
                    $responsable=$value->responsable2;
                }
                
                if($value->conceptopago_id==3 && $value->tipotarjeta==''){


                    if ($caja_id == 4) {
                        $rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        //echo $value->movimiento_id."|".$value->id."@";
                        foreach ($rs as $k => $v) {
                            $pdf::SetTextColor(0,0,0);
                            if($egreso1>0 && $bandegreso){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(205,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                                $bandegreso=false;
                                $pdf::Ln(); 
                            }
                            if($pago==0 && $value->tipodocumento_id!=15){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                                $pdf::Ln();
                            }
                            if ($value->tipodocumento_id !== 15) {
                                $pdf::SetFont('helvetica','',7);
                                $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');

                                $nombrepaciente = '';
                                $nombreempresa = '-';
                                if ($value->persona_id !== NULL) {
                                    //echo 'entro'.$value->id;break;
                                    $nombrepaciente =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                                }else{
                                    $nombrepaciente = trim($value->nombrepaciente);
                                }
                                if ($value->tipodocumento_id == 5) {
                                    
                                    
                                }else{
                                    if ($value->empresa_id != null) {
                                        // dd($value);
                                        
                                        $nombreempresa = trim($value->empresa->bussinesname);
                                    }
                                    
                                }
                                

                                if(strlen($nombrepaciente)>30){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(60,3,($nombrepaciente),0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(60,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(60,7,($nombrepaciente),1,0,'L');    
                                }
                                $venta= Movimiento::find($value->movimiento_id);
                                $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                                $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                                if ($venta->conveniofarmacia_id != null) {
                                    $nombreempresa = $venta->conveniofarmacia->nombre;
                                }
                                $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                                if($v->servicio_id>0){
                                    if(strlen($v->servicio->nombre)>35){
                                        $x=$pdf::GetX();
                                        $y=$pdf::GetY();                    
                                        $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                        $pdf::SetXY($x,$y);
                                        $pdf::Cell(70,7,"",1,0,'C');
                                    }else{
                                        $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                    }
                                }else{
                                    $pdf::Cell(70,7,$v->descripcion.'- MEDICINA',1,0,'L');    
                                }
                                $pdf::Cell(18,7,'',1,0,'C');
                                $pdf::Cell(18,7,number_format($v->movimiento->total,2,'.',''),1,0,'R');
                                $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                                if ($venta->doctor_id != null) {
                                    $pdf::Cell(20,7,substr($venta->doctor->nombres,0,1).'. '.$venta->doctor->apellidopaterno,1,0,'L');

                                }else{
                                   $pdf::Cell(20,7," - ",1,0,'L'); 
                                }
                                
                                $pdf::Ln();
                                $pago=$pago + number_format($v->movimiento->total,2,'.','');
                            }
                            
                        }
                    }else{
                        //PARA PAGO DE CLIENTE, BUSCO TICKET
                        $rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        foreach ($rs as $k => $v){
                            $pdf::SetTextColor(0,0,0);
                            if($transferenciae>0 && $bandtransferenciae){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(205,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($transferenciae,2,'.',''),1,0,'R');
                                $bandtransferenciae=false;
                                $pdf::Ln(); 
                            }
                            if($egreso1>0 && $bandegreso){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(205,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                                $bandegreso=false;
                                $pdf::Ln(); 
                            }
                            if($pago==0){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                                $pdf::Ln();
                                if($caja_id==3){
                                    $pdf::SetFont('helvetica','B',7);
                                    $pdf::Cell(223,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                                    $apert = Movimiento::find($movimiento_mayor);
                                    $pdf::Cell(18,7,number_format($apert->total,2,'.',''),1,0,'R');
                                    $pago = $pago + $apert->total;
                                    $ingreso = $ingreso + $apert->total;
                                    $pdf::Ln();
                                }
                            }
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                            }
                            $venta= Movimiento::find($value->movimiento_id);
                            $ticket= Movimiento::find($v->movimiento_id);
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                            if($v->servicio_id>0){
                                if(strlen($v->servicio->nombre)>35){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(70,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                            $pdf::Cell(18,7,'',1,0,'C');
                            $pdf::Cell(18,7,number_format($v->cantidad*$v->pagohospital,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode('-'),1,0,'C');
                            $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                            $pdf::Ln();
                            $pago=$pago + number_format($v->cantidad*$v->pagohospital,2,'.','');
                        }
                    }
                }elseif($value->conceptopago_id==3 && $value->tipotarjeta!=''){//PARA PAGO DE CLIENTE, BUSCO TICKET CON TARJETA
                    if ($caja_id == 4) {
                        $rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        foreach ($rs as $k => $v){
                            $pdf::SetTextColor(0,0,0);
                            if($pago>0 && $bandpago){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                                $bandpago=false;
                                $pdf::Ln(); 
                            }

                            if($tarjeta==0){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(281,7,utf8_decode("TARJETA"),1,0,'L');
                                $pdf::Ln();
                            }
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            
                            $nombrepaciente = '';
                            $nombreempresa = '-';
                            if ($value->persona_id !== NULL) {
                                //echo 'entro'.$value->id;break;
                                $nombrepaciente =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                            }else{
                                $nombrepaciente = trim($value->nombrepaciente);
                            }
                            if ($value->tipodocumento_id == 5) {
                                
                                
                            }else{
                                $nombreempresa = trim($value->empresa->bussinesname);
                            }
                            if(strlen($nombrepaciente)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($nombrepaciente),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($nombrepaciente),1,0,'L');    
                            }
                            $venta= Movimiento::find($value->movimiento_id);
                            $ticket= Movimiento::find($v->movimiento_id);
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            if ($venta->conveniofarmacia_id != null) {
                                $nombreempresa = $venta->conveniofarmacia->nombre;
                            }
                            $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                            if($v->servicio_id>0){
                                if(strlen($v->servicio->nombre)>35){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(70,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                            $pdf::Cell(18,7,'',1,0,'C');
                            $tarjeta=$tarjeta + $v->movimiento->total;
                            $pdf::Cell(18,7,number_format($v->movimiento->total,2,'.',''),1,0,'R');
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(20,7,"",1,0,'C');
                            if ($venta->doctor_id != null) {
                                $pdf::Cell(20,7,substr($venta->doctor->nombres,0,1).'. '.$venta->doctor->apellidopaterno,1,0,'L');

                            }else{
                               $pdf::Cell(20,7," - ",1,0,'L'); 
                            }
                            $pdf::Ln();
                            //$pago=$pago + number_format($v->movimiento->total,2,'.','');
                            
                        }
                    }else{
                        $rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        foreach ($rs as $k => $v){
                            $pdf::SetTextColor(0,0,0);
                            if($pago>0 && $bandpago){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                                $bandpago=false;
                                $pdf::Ln(); 
                            }
                            if($tarjeta==0){
                                $pdf::SetFont('helvetica','B',8);
                                $pdf::Cell(281,7,utf8_decode("TARJETA"),1,0,'L');
                                $pdf::Ln();
                            }
                            if($value->situacion<>'A'){
                                $pdf::SetTextColor(0,0,0);
                            }else{
                                $pdf::SetTextColor(255,0,0);
                            }
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            $tarjeta=$tarjeta + number_format($v->cantidad*$v->pagohospital,2,'.','');
                            if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                            }
                            $venta= Movimiento::find($value->movimiento_id);
                            $ticket= Movimiento::find($v->movimiento_id);
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                            if($v->servicio_id>0){
                                if(strlen($v->servicio->nombre)>35){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(70,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                            $pdf::Cell(18,7,'',1,0,'C');
                            $pdf::Cell(18,7,number_format($v->cantidad*$v->pagohospital,2,'.',''),1,0,'R');
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(20,7,"",1,0,'C');
                            $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                            $pdf::Ln();
                            //$pago=$pago + number_format($v->cantidad*$v->pagohospital,2,'.','');
                        }

                    }
                    
                }elseif(in_array($value->conceptopago_id, $listConcepto) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA EGRESOS
                    if($value->situacion2<>'R'){
                        $pdf::SetTextColor(0,0,0);
                        if($egreso1>0 && $bandegreso){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(205,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                            $bandegreso=false;
                            $pdf::Ln(); 
                        }
                        if($transferenciae==0){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(281,7,utf8_decode("TRANSFERENCIA"),1,0,'L');
                            $pdf::Ln();
                        }
                        if($value->situacion<>'A'){
                            $pdf::SetTextColor(0,0,0);
                        }else{
                            $pdf::SetTextColor(255,0,0);
                        }
                        $list=explode(",",$value->listapago);
                        // dd($list);
                        $transferenciae = $transferenciae + $value->total;
                        for($c=0;$c<count($list);$c++){
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            // dd($list[$c]);
                            $detalle = Detallemovcaja::find($list[$c]);
                            $ticket = Movimiento::where("id","=",$detalle->movimiento_id)->first();
                            $venta = Movimiento::where("movimiento_id","=",$detalle->movimiento_id)->first();
                            if(strlen($ticket->persona->movimiento.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            if($venta->tipodocumento_id==4){
                                $pdf::Cell(40,7,$venta->persona->bussinesname,1,0,'L');
                            }else{
                                $pdf::Cell(40,7,"",1,0,'L');
                            }
                            if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                                $descripcion=$value->conceptopago->nombre.' - RH: '.$detalle->recibo;
                            }else{
                                $descripcion=$value->conceptopago->nombre;
                            }
                            if(strlen($descripcion)>40){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();
                                $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(70,7,"",1,0,'L');
                            }else{
                                $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                            }
                            if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                                $pdf::Cell(18,7,number_format($detalle->pagodoctor,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                            }elseif($value->conceptopago_id==16){//TRANSFERENCIA SOCIO
                                $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                            }elseif($value->conceptopago_id==20){//BOLETEO TOTAL
                                $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                            }elseif($value->conceptopago_id==14){//TARJETA
                                $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                                $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                                if(in_array($value->id, [359022,359023])){
                                    $egreso = $egreso - $detalle->pagotarjeta;
                                }//TRANSFERENCIA POR CHEQUE MANUAL DIA 09/01/19, USUARIO NELLY MONTEZA
                            }
                            $pdf::Ln();
                        }
                    }
                }elseif(in_array($value->conceptopago_id, $listConcepto) && $value->conceptopago->tipo=='I'){//CONCEPTOS QUE TIENEN LISTA INGRESOS
                    $pdf::SetTextColor(0,0,0);
                    if($pago>0 && $bandpago){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                        $bandpago=false;
                        $pdf::Ln(); 
                    }
                    if($tarjeta>0 && $bandtarjeta){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                        $bandtarjeta=false;
                        $pdf::Ln(); 
                    }
                    if($transferenciai==0){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(281,7,utf8_decode("TRANSFERENCIA"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $transferenciai = $transferenciai + $value->total;
                    $list=explode(",",$value->listapago);
                    for($c=0;$c<count($list);$c++){
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        $detalle = Detallemovcaja::find($list[$c]);
                        $venta = Movimiento::where("movimiento_id","=",$detalle->movimiento_id)->first();
                        if(strlen($venta->persona->movimiento.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                        $pdf::Cell(40,7,"",1,0,'L');
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $descripcion=$value->conceptopago->nombre.' - RH: '.$detalle->recibo;
                        }else{
                            $descripcion=$value->conceptopago->nombre;
                        }
                        if(strlen($descripcion)>40){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(70,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                        }
                        if($value->conceptopago_id==21 ){//BOLETEO TOTAL
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }elseif($value->conceptopago_id==17){// TRANSFERENCIA SOCIO
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }elseif($value->conceptopago_id==15){//TARJETA
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                            if(in_array($value->id, [359022,359023])){
                                $ingreso = $ingreso - $detalle->pagotarjeta;
                            }//TRANSFERENCIA POR CHEQUE MANUAL DIA 09/01/19, USUARIO NELLY MONTEZA
                        }
                        $pdf::Ln();   
                    }
                }elseif(in_array($value->conceptopago_id, $listConcepto2) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA2
                    /*$pdf::SetTextColor(0,0,0);
                    if($egreso==0){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(279,7,utf8_decode("EGRESO"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $list=explode(",",$value->listapago);//print_r($value->listapago."-");
                    for($c=0;$c<count($list);$c++){
                        $detalle = Detallemovcaja::find($list[$c]);
                        $venta = Movimiento::where("movimiento_id","=",$detalle->movimiento_id)->first();
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        if(strlen($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(10,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                        $pdf::Cell(40,7,"",1,0,'L');
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $descripcion=$value->conceptopago->nombre.' - RH: '.$detalle->recibo;
                        }else{
                            $descripcion=$value->conceptopago->nombre;
                        }
                        if(strlen($descripcion)>40){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(70,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                        }
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $pdf::Cell(18,7,number_format($detalle->pagodoctor,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }else{//SOCIO
                            $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }
                        $pdf::Ln();   
                    }*/
                }elseif(in_array($value->conceptopago_id, $listConcepto3) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA3
                    $pdf::SetTextColor(0,0,0);
                    if($egreso1==0){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(281,7,utf8_decode("EGRESO"),1,0,'L');
                        $pdf::Ln();
                    }
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                    }   
                    $pdf::Cell(8,7,'RH',1,0,'C');
                    $list=explode(",",$value->listapago);
                    $detalle = Detallemovcaja::find($list[0]);
                    if($value->conceptopago_id==25)//pago de socio
                        $pdf::Cell(12,7,$detalle->recibo2,1,0,'C');
                    else
                        $pdf::Cell(12,7,$detalle->recibo,1,0,'C');
                    $pdf::Cell(40,7,"",1,0,'L');
                    $descripcion=$value->conceptopago->nombre;
                    if(strlen($descripcion)>40){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(70,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                    }
                    $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                    $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Ln();
                    $egreso1 = $egreso1 + $value->total;
                }elseif($value->conceptopago_id==23 || $value->conceptopago_id == 32){//COBRANZA
                    if ($caja_id == 4 && $value->conceptopago_id == 32) {
                        if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }
                        if($tarjeta>0 && $bandtarjeta){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                            $bandtarjeta=false;
                            $pdf::Ln(); 
                        }
                        $listventas = Movimiento::where('movimientodescarga_id','=',$value->id)->get();

                        if($cobranza==0 && $value->conceptopago->tipo=="I"){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(281,7,utf8_decode("COBRANZA"),1,0,'L');
                            $pdf::Ln();
                        }
                        foreach ($listventas as $key6 => $value6) {
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value6->fecha)),1,0,'C');
                            $nombrepersona = '-';
                            if ($value6->persona_id !== NULL) {
                                //echo 'entro'.$value6->id;break;
                                $nombrepersona =$value6->persona->apellidopaterno.' '.$value6->persona->apellidomaterno.' '.$value6->persona->nombres;

                            }else{
                                $nombrepersona = trim($value6->nombrepaciente);
                            }
                            if(strlen($nombrepersona)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($nombrepersona),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($value6->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$value6->serie.'-'.$value6->numero,1,0,'C');
                            $nombreempresa = '';
                            if ($value6->conveniofarmacia_id != null) {
                                $nombreempresa = $value6->conveniofarmacia->nombre;
                            }
                            $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                            $pdf::Cell(70,7,'MEDICINA',1,0,'L');
                            $pdf::Cell(18,7,'',1,0,'C');
                            $pdf::Cell(18,7,number_format($value6->total,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                            if ($value6->doctor_id != null) {
                                $pdf::Cell(20,7,substr($value6->doctor->nombres,0,1).'. '.$value6->doctor->apellidopaterno,1,0,'L');

                            }else{
                               $pdf::Cell(20,7," - ",1,0,'L'); 
                            }
                            if($value->situacion=='C')//CONFIRMADO FARMACIA
                                $cobranza=$cobranza + $value6->total;
                            $pdf::Ln();
                        }
                    }elseif($caja_id == 4 && $value->conceptopago_id == 23){
                        if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }
                        if($tarjeta>0 && $bandtarjeta){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                            $bandtarjeta=false;
                            $pdf::Ln(); 
                        }
                        $listventas = Movimiento::where('movimiento_id','=',$value->id)->get();
                        if($cobranza==0 && $value->conceptopago->tipo=="I"){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(281,7,utf8_decode("COBRANZA"),1,0,'L');
                            $pdf::Ln();
                        }
                        foreach ($listventas as $key6 => $value6) {
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value6->fecha)),1,0,'C');
                            $nombrepersona = '-';
                            if ($value6->persona_id !== NULL) {
                                //echo 'entro'.$value6->id;break;
                                $nombrepersona =$value6->persona->apellidopaterno.' '.$value6->persona->apellidomaterno.' '.$value6->persona->nombres;

                            }else{
                                $nombrepersona = trim($value6->nombrepaciente);
                            }
                            if(strlen($nombrepersona)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($nombrepersona),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($value6->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$value6->serie.'-'.$value6->numero,1,0,'C');
                            $nombreempresa = '';
                            if ($value6->conveniofarmacia_id != null) {
                                $nombreempresa = $value6->conveniofarmacia->nombre;
                            }
                            $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                            $pdf::Cell(70,7,'MEDICINA',1,0,'L');
                            $pdf::Cell(18,7,'',1,0,'C');
                            $pdf::Cell(18,7,number_format($value6->total,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                            if ($value6->doctor_id != null) {
                                $pdf::Cell(20,7,substr($value6->doctor->nombres,0,1).'. '.$value6->doctor->apellidopaterno,1,0,'L');

                            }else{
                               $pdf::Cell(20,7," - ",1,0,'L'); 
                            }
                            $cobranza=$cobranza + $value6->total;
                            $pdf::Ln();
                        }
                    }else{

                        $pdf::SetTextColor(0,0,0);
                        if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }
                        if($tarjeta>0 && $bandtarjeta){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                            $bandtarjeta=false;
                            $pdf::Ln(); 
                        }
                        if($ingresotarjeta>0 && $bandingresotarjeta){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($ingresotarjeta,2,'.',''),1,0,'R');
                            $bandingresotarjeta=false;
                            $pdf::Ln(); 
                        }
                        if($cobranza==0 && $value->conceptopago->tipo=="I"){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(281,7,utf8_decode("COBRANZA"),1,0,'L');
                            $pdf::Ln();
                        }
                        if($value->situacion<>'A'){
                            $pdf::SetTextColor(0,0,0);
                        }else{
                            $pdf::SetTextColor(255,0,0);
                        }
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        $nombrepersona = '-';
                        if ($value->persona_id !== NULL) {
                            //echo 'entro'.$value->id;break;
                            $nombrepersona =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                        }else{
                            $nombrepersona = trim($value->nombrepaciente);
                        }
                        if(strlen($nombrepersona)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($nombrepersona),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                        }
                        $venta = Movimiento::find($value->movimiento_id);
                        $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':"F"),1,0,'C');
                        $pdf::Cell(12,7,utf8_decode($venta->serie.'-'.$venta->numero),1,0,'C');
                        if($value->conceptopago_id==11){//PAGO A ENFERMERIA
                            $descripcion=$value->conceptopago->nombre.': '.$value->comentario.' - RH: '.$value->voucher;
                        }else{
                            $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                        }
                        if(strlen($descripcion)>70){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(110,3,($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(110,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(110,7,($descripcion),1,0,'L');
                        }
                        if($value->situacion<>'R' && $value->situacion2<>'R'){
                            if($value->conceptopago->tipo=="I"){
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'R');
                                $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            }else{
                                $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            }
                        }else{
                            $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                            $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                        }
                        $cobranza=$cobranza + $value->total;
                        $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                        $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                        $pdf::Ln();
                    }
                }elseif($value->conceptopago_id==33){//PAGO DE FARMACIA
                    $pdf::SetTextColor(0,0,0);
                    if($transferenciae>0 && $bandtransferenciae){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(205,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($transferenciae,2,'.',''),1,0,'R');
                        $transferenciae=false;
                        $pdf::Ln(); 
                    }
                    if($tarjeta>0 && $bandtarjeta){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                        $bandtarjeta=false;
                        $pdf::Ln(); 
                    }
                    if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }

                    if($ingresotarjeta==0){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(281,7,utf8_decode("INGRESOS POR TRANSFERENCIA"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    $nombrepersona = '-';
                    if ($value->persona_id != NULL && !is_null($value->persona)) {
                        //echo 'entro'.$value->id;break;
                        if ($value->persona->bussinesname != null) {
                            $nombrepersona = $value->persona->bussinesname;
                        }else{
                            $nombrepersona =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                        }
                        
                    }else{
                        $nombrepersona = trim($value->nombrepaciente);
                    }
                    if(strlen($nombrepersona)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($nombrepersona),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                    }
                    if($value->conceptopago_id==31){
                        $pdf::Cell(8,7,'T',1,0,'C');
                    }else{
                        $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C'); 
                        
                    }
                    $pdf::Cell(12,7,utf8_decode(trim($value->voucher)==''?$value->numero:$value->voucher),1,0,'C');
                
                    $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                    
                    if(strlen($descripcion)>70){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(110,3,SUBSTR($descripcion,0,150),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(110,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(110,7,($descripcion),1,0,'L');
                    }
                    if($value->situacion<>'R' && $value->situacion2<>'R'){
                        if($value->conceptopago->tipo=="I"){
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'R');
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $ingresotarjeta=$ingresotarjeta + $value->total;
                        }else{
                            $egreso1=$egreso1 + $value->total;
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                        }
                    }else{
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                    }
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Ln();
                }elseif($value->conceptopago_id!=1 && $value->conceptopago_id!=2 && $value->conceptopago_id!=23 && $value->conceptopago_id!=10 && $value->conceptopago_id!=150){
                    $pdf::SetTextColor(0,0,0);
                    if($transferenciae>0 && $bandtransferenciae){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(205,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($transferenciae,2,'.',''),1,0,'R');
                        $transferenciae=false;
                        $pdf::Ln(); 
                    }
                    if(($ingreso==0 || $pago==0) && $value->conceptopago->tipo=="I"){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                        $pdf::Ln();
                        if($pago==0){
                            if($caja_id==3){
                                $pdf::SetFont('helvetica','B',7);
                                $pdf::Cell(223,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                                $apert = Movimiento::find($movimiento_mayor);
                                $pdf::Cell(18,7,number_format($apert->total,2,'.',''),1,0,'R');
                                $pago = $pago + $apert->total;
                                $ingreso = $ingreso + $apert->total;
                                $pdf::Ln();
                            }
                        }
                    }elseif($egreso1==0){
                        //$pdf::SetFont('helvetica','B',8.5);
                        //$pdf::Cell(281,7,utf8_decode("EGRESOS"),1,0,'L');
                        //$pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    $nombrepersona = '-';
                    if ($value->persona_id != NULL && !is_null($value->persona)) {
                        //echo 'entro'.$value->id;break;
                        if ($value->persona->bussinesname != null) {
                            $nombrepersona = $value->persona->bussinesname;
                        }else{
                            $nombrepersona =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                        }
                        
                    }else{
                        $nombrepersona = trim($value->nombrepaciente);
                    }
                    if(strlen($nombrepersona)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($nombrepersona),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                    }
                    if($value->conceptopago_id!=13){
                        if($value->conceptopago_id==31){
                            $pdf::Cell(8,7,'T',1,0,'C');
                        }else{
                            if ($caja_id == 4) {
                                if ($value->tipodocumento_id == 7) {
                                    $pdf::Cell(8,7,'BV',1,0,'C');
                                }elseif($value->tipodocumento_id == 6){
                                    $pdf::Cell(8,7,'FT',1,0,'C');
                                }else{
                                    $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C'); 
                                }
                            }else{
                               $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C'); 
                            }
                            
                        }
                        $pdf::Cell(12,7,utf8_decode(trim($value->voucher)==''?$value->numero:$value->voucher),1,0,'C');
                    }else{//PARA ANULACION POR NOTA CREDITO
                        $pdf::Cell(8,7,'NA',1,0,'C');
                        $mov = Movimiento::find($value->movimiento_id);
                        $pdf::Cell(12,7,($mov->serie.'-'.$mov->numero),1,0,'C');
                    }

                    if($value->conceptopago_id==11){//PAGO A ENFERMERIA
                        $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                    }else{
                        $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                    }
                    if(strlen($descripcion)>70){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(110,3,SUBSTR($descripcion,0,150),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(110,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(110,7,($descripcion),1,0,'L');
                    }
                    if($value->situacion<>'R' && $value->situacion2<>'R'){
                        if($value->conceptopago->tipo=="I"){
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'R');
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pago=$pago + $value->total;
                        }else{
                            $egreso1=$egreso1 + $value->total;
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                        }
                    }else{
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                    }
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Ln();
                }
                
                if($value->conceptopago_id<>2 && $value->situacion<>'A'){
                    if($value->conceptopago->tipo=="I"){
                        if($value->conceptopago_id<>10 && $value->conceptopago_id<>150){//GARANTIA
                            if($value->conceptopago_id<>15 && $value->conceptopago_id<>17 && $value->conceptopago_id<>19 && $value->conceptopago_id<>21){
                                if ($value->tipodocumento_id != 15) {
                                    //echo $value->total."@";
                                    $ingreso = $ingreso + $value->total;
                                }
                                    
                            }elseif(($value->conceptopago_id==15 || $value->conceptopago_id==17 || $value->conceptopago_id==19 || $value->conceptopago_id==21) && $value->situacion=='C'){
                                $ingreso = $ingreso + $value->total;    
                            }
                        }else{
                            $garantia = $garantia + $value->total;
                        }
                        if($value->conceptopago_id<>10  && $value->conceptopago_id<>150){//GARANTIA
                            if($value->tipotarjeta=='VISA'){
                                    $visa = $visa + $value->total;
                            }elseif($value->tipotarjeta==''){
                                if ($value->tipodocumento_id != 15) {
                                    $efectivo = $efectivo + $value->total;
                                }
                            }else{
                                $master = $master + $value->total;
                            }
                        }
                    }else{
                        if($value->conceptopago_id<>14 && $value->conceptopago_id<>16 && $value->conceptopago_id<>18 && $value->conceptopago_id<>20){
                            if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                                $ingreso  = $ingreso - $value->total;
                                $efectivo = $efectivo - $value->total;
                            }else{
                                $egreso  = $egreso + $value->total;
                            }
                        }elseif(($value->conceptopago_id==14 || $value->conceptopago_id==16 || $value->conceptopago_id==18 || $value->conceptopago_id==20) && $value->situacion2=='C'){
                            $egreso  = $egreso + $value->total;
                        }
                    }
                }
                $res=$value->responsable2;
                if ($caja_id == 4) {
                    /*if($tarjeta>0 && $bandtarjeta){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                        $bandtarjeta=false;
                        $pdf::Ln(); 
                    }*/
                }
            }
            if($ingresotarjeta>0 && $bandingresotarjeta){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($ingresotarjeta,2,'.',''),1,0,'R');
                $bandingresotarjeta=false;
                $pdf::Ln(); 
            }
            if($cobranza>0 && $bandcobranza){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($cobranza,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($transferenciai>0 && $bandtransferenciai){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($transferenciai,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($pago==0){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                $pdf::Ln();
                if($caja_id==3){
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(223,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                    $apert = Movimiento::find($movimiento_mayor);
                    $pdf::Cell(18,7,number_format($apert->total,2,'.',''),1,0,'R');
                    $pago = $pago + $apert->total;
                    $ingreso = $ingreso + $apert->total;
                    $pdf::Ln();
                }
            }
            if($pago>0 && $bandpago){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($tarjeta>0 && $bandtarjeta){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                $bandtarjeta=false;
                $pdf::Ln(); 
            }

            $resultado1       = Movimiento::join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                ->leftjoin('movimiento as m2','movimiento.movimiento_id','=','m2.id')
                                ->leftjoin('person as paciente', 'paciente.id', '=', 'm2.persona_id')
                                ->where('movimiento.serie', '=', $serie)
                                ->where('movimiento.tipomovimiento_id', '=', 4)
                                ->where('movimiento.tipodocumento_id', '<>', 15)
                                ->where('movimiento.id', '>', $movimiento_mayor)
                                ->where('movimiento.situacion','like','U');
            $resultado1       = $resultado1->select('movimiento.*','m2.situacion as situacion2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'))->orderBy('movimiento.numero', 'asc');
            
            $lista1           = $resultado1->get();
            if(count($lista1)>0){
                //echo 'alert('.count($lista1).')';
                $anuladas=0;
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(281,7,utf8_decode("ANULADAS"),1,0,'L');
                $pdf::Ln();
                foreach($lista1 as $key1 => $value1){
                    $pdf::SetFont('helvetica','',7.5);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value1->fecha)),1,0,'C');
                    if($value1->tipodocumento_id==5){//BOLETA}
                        $nombre=$value1->paciente2;
                        //$value1->persona->apellidopaterno.' '.$value1->persona->apellidomaterno.' '.$value1->persona->nombres;
                    }else{
                        $nombre=$value1->paciente2;
                        $empresa=$value1->persona->bussinesname;
                    }
                    if(strlen($nombre)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($nombre),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($nombre),1,0,'L');    
                    }
                    $pdf::Cell(8,7,($value1->tipodocumento_id==5?'B':'F'),1,0,'C');
                    $pdf::Cell(12,7,$value1->serie.'-'.$value1->numero,1,0,'C');
                    if($caja_id==4){
                        $nombreempresa='-';
                        if ($value->tipodocumento_id != 5) {
                            if ($value->empresa != null) {
                                $nombreempresa = trim($value->empresa->bussinesname);
                            }
                        }
                        $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                    }else{
                        if($value1->tipodocumento_id==5){
                            $ticket= Movimiento::find($value1->movimiento_id);
                            if($ticket->plan_id>0)
                                $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                            else
                                $pdf::Cell(40,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(40,7,substr($empresa,0,23),1,0,'L');
                        }
                    }
                    if($caja_id==4){
                        $pdf::Cell(70,7,"MEDICINA",1,0,'L');    
                    }else{
                        // echo json_encode($value1);
                        if($value1->movimiento_id !== ''){
                           $detalles = Detallemovcaja::join('servicio','servicio.id','=','detallemovcaja.servicio_id')->where('detallemovcaja.movimiento_id','=',$value1->movimiento_id)->select('servicio.nombre')->get();

                           $detalles_serv_cadena = '';
                        
                           foreach($detalles as $key2 => $value2){
                                $detalles_serv_cadena.=$value2->nombre;
                                break;
                           }

                           if($detalles_serv_cadena !== ''){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(70,7,$detalles_serv_cadena,0,'L');
                                $pdf::SetXY($x,$y);
                           }else{
                               $detalles = Detallemovcaja::where('detallemovcaja.movimiento_id','=',$value1->movimiento_id)->select('descripcion')->get();

                               $detalles_serv_cadena = '';
                            
                               foreach($detalles as $key2 => $value2){
                                    $detalles_serv_cadena.=$value2->descripcion;
                                    break;
                               }

                                // $pdf::SetFont('helvetica','',6);
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(70,7,$detalles_serv_cadena,0,'L');
                                $pdf::SetXY($x,$y);   
                            
                            }
                            // $pdf::SetFont('helvetica','',6);
                                  
                           // $pdf::Cell(88,7,$detalles_serv_cadena,1,0,'L');    
                        }
                        // $pdf::Cell(70,7,"SERVICIOS",1,0,'L');    
                    }
                    $pdf::Cell(70,7,'',1,0,'C');
                   
                    $pdf::Cell(18,7,'',1,0,'C');
                    $pdf::Cell(18,7,number_format(0,2,'.',''),1,0,'R');
                    $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                    $pdf::Cell(20,7,'-',1,0,'L');
                    //substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno
                    $pdf::Ln();
                    $anuladas=$anuladas + number_format(0,2,'.','');
                    
                }

                // exit();
            }
            $pdf::Ln();
            if (!isset($responsable)) {
                $responsable="CAJA VACIA";
            }
            $resp=Movimiento::where('caja_id','=',$caja_id)->where('conceptopago_id','=','2')->select('movimiento.*')->orderBy('id','desc')->limit(1)->first();
            $pdf::Cell(120,7,('RESPONSABLE: '.$responsable." / HORA DE IMPRESION: ".date("d/m/Y H:i:s")),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(50,7,utf8_decode("RESUMEN DE CAJA"),1,0,'C');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("INGRESOS :"),1,0,'L');
            $pdf::Cell(20,7,number_format($ingreso,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Efectivo :"),1,0,'L');
            $pdf::Cell(20,7,number_format($efectivo,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Master :"),1,0,'L');
            $pdf::Cell(20,7,number_format($master,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Visa :"),1,0,'L');
            $pdf::Cell(20,7,number_format($visa,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("EGRESOS :"),1,0,'L');
            $pdf::Cell(20,7,number_format($egreso,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("SALDO :"),1,0,'L');
            $pdf::Cell(20,7,number_format($ingreso - $egreso - $visa - $master,2,'.',''),1,0,'R');
            $pdf::Ln();

            if ($caja_id == 4) {
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(50,7,utf8_decode("VENTAS O SALIDAS:"),0,0,'L');
                $pdf::Ln();
                $pdf::Cell(28,7,"FECHA",1,0,'C');
                $pdf::Cell(65,7,"PERSONA",1,0,'C');
                $pdf::Cell(22,7,"TIPO DOC.",1,0,'C');
                $pdf::Cell(18,7,"NRO DOC.",1,0,'C');
                $pdf::Cell(80,7,"PRODUCTO",1,0,'C');
                $pdf::Cell(15,7,"CANT.",1,0,'C');
                $pdf::Cell(25,7,"TARIFA",1,0,'C');
                $pdf::Cell(20,7,"IMPORTE",1,0,'C');
                $pdf::Ln();

                $resultado1       = Movimiento::join('detallemovimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')
                            ->leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('producto','producto.id','=','detallemovimiento.producto_id')
                            ->where('movimiento.serie', '=', $serie)
                            ->whereIn('movimiento.tipodocumento_id', [4,5,15,9,11])
                            ->where('movimiento.id', '>=', $movimiento_mayor);
                $resultado1       = $resultado1->select('movimiento.created_at','producto.nombre as producto','detallemovimiento.cantidad','detallemovimiento.precio','detallemovimiento.subtotal','movimiento.serie','movimiento.numero','movimiento.tipodocumento_id','movimiento.tipoventa','movimiento.conveniofarmacia_id',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'))->orderBy('movimiento.numero', 'asc');
                $lista = $resultado1->get();
                foreach ($lista as $key => $value) {
                    if($value->situacion=='A'){//NOTA DE CREDITO
                        $pdf::SetTextColor(215,57,37);
                    }elseif($value->situacion=='U'){//ANULADA
                        $pdf::SetTextColor(48,215,37);
                    }else{
                        $pdf::SetTextColor(0,0,0);
                    }

                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(28,7,date("d/m/Y H:i:s",strtotime($value->created_at)),1,0,'C');
                    $pdf::Cell(65,7,substr($value->paciente2,0,40),1,0,'L');
                    $pdf::Cell(22,7,($value->tipodocumento_id==5?'BOLETA':($value->tipodocumento_id==4?'FACTURA':($value->tipodocumento_id==15?'GUIA INTERNA':($value->tipodocumento_id==11?'NC COMPRA':'SALIDA INT.')))),1,0,'C');
                    $pdf::Cell(18,7,$value->serie.'-'.$value->numero,1,0,'L');
                    $pdf::Cell(80,7,$value->producto,1,0,'L');
                    $pdf::Cell(15,7,$value->cantidad,1,0,'C');
                    if($value->conveniofarmacia_id!=NULL && $value->conveniofarmacia_id>0){
                        $convenio = conveniofarmacia::find($value->conveniofarmacia_id)->nombre;
                    }else{
                        $convenio="PARTICULAR";
                    }
                    $pdf::Cell(25,7,$convenio,1,0,'C');
                    $pdf::Cell(20,7,$value->subtotal,1,0,'C');
                    $pdf::Ln();

                }

                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(50,7,utf8_decode("COMPRAS O INGRESOS:"),0,0,'L');
                $pdf::Ln();
                $pdf::Cell(28,7,"FECHA",1,0,'C');
                $pdf::Cell(65,7,"PROVEEDOR",1,0,'C');
                $pdf::Cell(22,7,"TIPO DOC.",1,0,'C');
                $pdf::Cell(18,7,"NRO DOC.",1,0,'C');
                $pdf::Cell(80,7,"PRODUCTO",1,0,'C');
                $pdf::Cell(15,7,"CANT.",1,0,'C');
                $pdf::Cell(20,7,"IMPORTE",1,0,'C');
                $pdf::Ln();

                $resultado1       = Movimiento::join('detallemovimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')
                            ->leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('producto','producto.id','=','detallemovimiento.producto_id')
                            ->whereIn('movimiento.tipodocumento_id', [6,7,10,12,13,8])
                            ->where('movimiento.id', '>=', $movimiento_mayor);
                $resultado1       = $resultado1->select('movimiento.created_at','producto.nombre as producto','detallemovimiento.cantidad','detallemovimiento.precio','detallemovimiento.subtotal','movimiento.serie','movimiento.numero','movimiento.tipodocumento_id','movimiento.tipoventa','movimiento.conveniofarmacia_id',DB::raw('concat(paciente.bussinesname) as paciente2'))->orderBy('movimiento.numero', 'asc');
                $lista = $resultado1->get();
                foreach ($lista as $key => $value) {
                    if($value->situacion=='A'){//NOTA DE CREDITO
                        $pdf::SetTextColor(215,57,37);
                    }elseif($value->situacion=='U'){//ANULADA
                        $pdf::SetTextColor(48,215,37);
                    }else{
                        $pdf::SetTextColor(0,0,0);
                    }
                    $pdf::SetFont('helvetica','',8);
                    $pdf::Cell(28,7,date("d/m/Y H:i:s",strtotime($value->created_at)),1,0,'C');
                    $pdf::Cell(65,7,substr($value->paciente2,0,40),1,0,'L');
                    $pdf::Cell(22,7,($value->tipodocumento_id==6?'FACTURA':($value->tipodocumento_id==7?'BOLETA':($value->tipodocumento_id==10?'GUIA INTERNA':($value->tipodocumento_id==12?'TICKET':($value->tipodocumento_id==13?'NC VENTA':'INGRESO INT.'))))),1,0,'C');
                    $pdf::Cell(18,7,$value->serie.'-'.$value->numero,1,0,'L');
                    $pdf::Cell(80,7,$value->producto,1,0,'L');
                    $pdf::Cell(15,7,$value->cantidad,1,0,'C');
                    $pdf::Cell(20,7,$value->subtotal,1,0,'C');
                    $pdf::Ln();

                }

            }
            /*$pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("GARANTIA :"),1,0,'L');
            $pdf::Cell(20,7,number_format($garantia,2,'.',''),1,0,'R');*/
            $pdf::Ln();
            $pdf::Output('ListaCaja.pdf');
        }
    }


    public function apertura(Request $request)
    {
        $entidad             = 'Caja';
        $formData            = array('caja.aperturar');
        $listar              = $request->input('listar');
        $caja                = Caja::find($request->input('caja_id'));
        $numero              = Movimiento::NumeroSigue(2,2);//movimiento caja y documento ingreso
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Aperturar '.$caja->nombre;
        return view($this->folderview.'.apertura')->with(compact('caja', 'formData', 'entidad', 'boton', 'listar', 'numero'));
    }
    
    public function aperturar(Request $request)
    {
        $reglas     = array(
                'caja_id'                  => 'required',
                );
        $mensajes = array(
            'caja.required'         => 'Debe seleccionar una caja',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $movimiento        = new Movimiento();
            $movimiento->fecha = date("Y-m-d H:i:s");
            $movimiento->numero= $request->input('numero');
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$user->person_id;
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            // if($request->input('caja_id')==3){
            //     $ultimo = Movimiento::where('conceptopago_id','=',2)
            //               ->where('caja_id','=',3)  
            //               ->orderBy('id','desc')->limit(1)->first();
            //     if(count($ultimo)>0){
            //         $movimiento->total=$ultimo->total;
            //     }else{
            //         $movimiento->total=0;    
            //     }
            // }else{
                $movimiento->total=0;     
            //}
            $movimiento->tipomovimiento_id=2;
            $movimiento->tipodocumento_id=2;
            $movimiento->conceptopago_id=1;
            $movimiento->comentario=$request->input('comentario');
            $movimiento->caja_id=$request->input('caja_id');
            $movimiento->situacion='N';
            $movimiento->save();
            $caja = Caja::find($request->input('caja_id'));
            $caja->estado = "A";
            $caja->save();
        });
        return is_null($error) ? "OK" : $error;
    }
    
    public function generarConcepto(Request $request)
    {
        $tipodoc = $request->input("tipodocumento_id");
        if($tipodoc==2){
            $rst = Conceptopago::where('tipo','like','I')->where('id','<>',1)->where('id','<>',6)->where('id','<>',15)->where('id','<>',17)->where('id','<>',19)->where('id','<>',21)->where('id','<>',23)->where('id','<>',31)->where('id','<>',3)->where('id','<>',32)->where('admision','like','S')->orderBy('nombre','ASC')->get();
        }else{
            $rst = Conceptopago::where('tipo','like','E')->where('id','<>',2)->where('id','<>',13)->where('id','<>',24)->where('id','<>',25)->where('id','<>',26)->where('admision','like','S')->orderBy('nombre','ASC')->get();
        }
        $cbo="";
        foreach ($rst as $key => $value) {
            $cbo = $cbo."<option value='".$value->id."'>".$value->nombre."</option>";
        }
         
        return $cbo;
    }
        
    public function generarNumero(Request $request)
    {
        $tipodoc = $request->input("tipodocumento_id");
        $numero  = Movimiento::NumeroSigue(2,$tipodoc);
        return $numero;
    }
    
    public function personautocompletar($searching)
    {
        $resultado        = Person::where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($searching).'%')->orWhere('bussinesname', 'LIKE', '%'.strtoupper($searching).'%')->whereNull('deleted_at')->orderBy('apellidopaterno', 'ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = '';
            if ($value->bussinesname != null) {
                $name = $value->bussinesname;
            }else{
                $name = $value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres;
            }
            $data[] = array(
                            'label' => trim($name),
                            'id'    => $value->id,
                            'value' => trim($name),
                        );
        }
        return json_encode($data);
    }

    public function cierre(Request $request)
    {
        $entidad             = 'Caja';
        $formData            = array('caja.cerrar');
        $listar              = $request->input('listar');
        $caja                = Caja::find($request->input('caja_id'));
        $saldo                = Caja::find($request->input('saldo'));
        $numero              = Movimiento::NumeroSigue(2,3);//movimiento caja y documento egreso
        $rst              = Movimiento::where('tipomovimiento_id','=',2)
                            ->where('caja_id','=',$caja->id)->where('conceptopago_id','=',1)
                            ->orderBy('id','DESC')->limit(1)->first();
        if(count($rst)>0){
            $movimiento_mayor = $rst->id;    
        }else{
            $movimiento_mayor = 0;
        }
        
        $resultado        = Movimiento::
                                leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','movimiento.id','=','m2.movimiento_id')
                            ->where('movimiento.caja_id', '=', $caja->id)
                            ->whereNull('movimiento.cajaapertura_id')
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            ->whereNotIn('movimiento.id', [359022,359023])//TRANSFERENCIA POR CHEQUE MANUAL DIA 09/01/19, USUARIO NELLY MONTEZA
                            ->where(function($query){
                                $query
                                    ->whereNotIn('movimiento.conceptopago_id',[31])
                                    ->orWhere('m2.situacion','<>','R');
                            });
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2',
                                DB::raw('CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) as paciente'),
                                DB::raw('responsable.nombres as responsable'))
                                ->orderBy('movimiento.id', 'desc');
        $lista            = $resultado->get();
        //error_log("CIERRE DE CAJA ID: ".$caja->id);
        //error_log("MOV MAYOR: ".$movimiento_mayor);
        //error_log("SQL LISTADO MOVIMIENTOS : \n".json_encode($resultado->toSql()));
        //error_log("MOVIMIENTOS : \n".json_encode($lista));
        
        $ingreso=0;$egreso=0;$garantia=0;$efectivo=0;$master=0;$visa=0;$pendiente=0;
        foreach($lista as $k=>$v){
            if($v->conceptopago_id<>2 && $v->situacion<>'A'){
                if($v->conceptopago->tipo=="I"){
                    if($v->conceptopago_id<>10  && $v->conceptopago_id<>150){//Garantias
                        if($v->conceptopago_id<>15 && $v->conceptopago_id<>17 && $v->conceptopago_id<>19 && $v->conceptopago_id<>21 && $v->conceptopago_id<>32){
                            $ingreso = $ingreso + $v->total;    
                        }elseif(($v->conceptopago_id==15 || $v->conceptopago_id==17 || $v->conceptopago_id==19 || $v->conceptopago_id==21 || $v->conceptopago_id==32) && $v->situacion=='C'){
                            $ingreso = $ingreso + $v->total;    
                        }else{
                            $pendiente = $pendiente + $v->pendiente;
                        }
                        if($v->tipotarjeta=='VISA'){
                            $visa = $visa + $v->total;
                        }elseif($v->tipotarjeta==''){
                            $efectivo = $efectivo + $v->total;
                        }else{
                            $master = $master + $v->total;
                        }
                    }else{
                        $garantia = $garantia + $v->total;
                    }
                }else{
                    if($v->conceptopago_id<>14 && $v->conceptopago_id<>16 && $v->conceptopago_id<>18 && $v->conceptopago_id<>20 && $v->conceptopago_id<>31){
                        $egreso  = $egreso + $v->total;
                    }elseif(($v->conceptopago_id==14 || $v->conceptopago_id==16 || $v->conceptopago_id==18 || $v->conceptopago_id==20 || $v->conceptopago_id==31) && $v->situacion2=='C'){
                        $egreso  = $egreso + $v->total;
                    }else{
                        $pendiente = $pendiente + $v->pendiente;
                    }
                }
            }
        }
        if($pendiente>0){
            $dat[0]=array("respuesta"=>"ERROR","msg"=>"Transferencia pendiente");
            return json_encode($dat);
        }
        $total               = number_format($ingreso - $egreso - $visa - $master,2,'.',''); 
        //$total = $saldo;
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Cerrar '.$caja->nombre;
        return view($this->folderview.'.cierre')->with(compact('caja', 'formData', 'entidad', 'boton', 'listar', 'numero', 'total'));
    }
    
    public function cerrar(Request $request)
    {
        $reglas     = array(
                'caja_id'                  => 'required',
                );
        $mensajes = array(
            'caja.required'         => 'Debe seleccionar una caja',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $movimiento        = new Movimiento();
            $movimiento->fecha = date("Y-m-d H:i:s");
            $movimiento->numero= $request->input('numero');
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$user->person_id;
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->tipomovimiento_id=2;
            $movimiento->tipodocumento_id=2;
            $movimiento->conceptopago_id=2;
            $movimiento->comentario=$request->input('comentario');
            $movimiento->caja_id=$request->input('caja_id');
            $movimiento->situacion='N';
            $movimiento->save();
            error_log("CAJA CERRADA CON EL ID: ".json_encode($movimiento));
            $caja = Caja::find($request->input('caja_id'));
            $caja->estado = "C";
            $caja->save();
        });
        return is_null($error) ? "OK" : $error;
    }
    
    function validarCajaTransferencia(Request $request){
        $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$request->input('caja_id'))->orderBy('movimiento.id','DESC')->limit(1)->first();
        if(count($rst)==0){
            $conceptopago_id=2;
        }else{
            $conceptopago_id=$rst->conceptopago_id;
        }
        if($conceptopago_id==2){
            return "Error";
        }else{
            return "OK";
        }
    }

    public function rechazar($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Caja = Movimiento::find($id);
            $Caja->situacion="R";//Rechazado
            if ($Caja->caja_id == 4) {
                $listventas = Movimiento::where('movimientodescarga_id','=',$Caja->id)->get();
                foreach ($listventas as $key => $value) {
                    $value->movimientodescarga_id = null;
                    $value->formapago = 'P';
                    $value->save();
                }
            }else{
                $arr=explode(",",$Caja->listapago);
                for($c=0;$c<count($arr);$c++){
                    $Detalle = Detallemovcaja::find($arr[$c]);
                    if($Caja->conceptopago_id==6){//CAJA
                        $Detalle->situacion='N';//normal;
                    }elseif($Caja->conceptopago_id==17){//SOCIO
                        $Detalle->situacionsocio=null;//null
                        $Detalle->situaciontarjeta=null;//null
                        $Detalle->medicosocio_id=null;//null
                    }elseif($Caja->conceptopago_id==15 || $Caja->conceptopago_id==21){//TARJETA Y BOLETEO TOTAL
                        $Detalle->situaciontarjeta=null;//null
                    }
                    $Detalle->save();
                }
            }
            

            $Caja->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function reject($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Caja::find($id);
        $entidad  = 'Caja';
        $formData = array('route' => array('caja.rechazar', $id), 'method' => 'Reject', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Rechazar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function aceptar($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Caja = Movimiento::find($id);
            $Caja->situacion="C";//Aceptado
            $arr=explode(",",$Caja->listapago);
            for($c=0;$c<count($arr);$c++){
                $Detalle = Detallemovcaja::find($arr[$c]);
                if($Caja->conceptopago_id==6){//CAJA
                    $Detalle->situacion='C';//confirmado;
                }elseif($Caja->conceptopago_id==17){//SOCIO
                    $Detalle->situacion='C';//confirmado;
                }elseif($Caja->conceptopago_id==15 || $Caja->conceptopago_id==21){//TARJETA Y BOLETEO TOTAL
                    $Detalle->situacion='C';//confirmado;
                }
                $Detalle->save();
            }
            $Caja->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function acept($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Caja::find($id);
        $entidad  = 'Caja';
        $formData = array('route' => array('caja.aceptar', $id), 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Aceptar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function descarga(Request $request)
    {
        $entidad          = 'Venta';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $request->session()->forget('carritoventa');
        $movimiento_id = Libreria::getParam($request->input('movimiento_id'));
        return view($this->folderview.'.adminDescarga')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta','movimiento_id'));
    }

    public function listardescarga(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Venta';
        $fechainicio             = Libreria::getParam($request->input('fechainicio'));
        $fechafin             = Libreria::getParam($request->input('fechafin'));
        $numero             = Libreria::getParam($request->input('numero'));
        $movimiento_id = Libreria::getParam($request->input('movimiento_id'));
        $resultado        = Venta::where('tipomovimiento_id', '=', '4')
                            ->where('ventafarmacia','=','S')
                            ->where('movimientodescarga_id','=',$movimiento_id)
                            ->where(function($query) use ($numero){   
                                if (!is_null($numero) && $numero !== '') {
                                   
                                    $query->where('numero', '=', $numero);
                                }
                            })->where(function($query) use ($fechainicio,$fechafin){   
                                if (!is_null($fechainicio) && $fechainicio !== '') {
                                    $begindate   = Date::createFromFormat('d/m/Y', $fechainicio)->format('Y-m-d');
                                    $query->where('fecha', '>=', $begindate);
                                }
                                if (!is_null($fechafin) && $fechafin !== '') {
                                    $enddate   = Date::createFromFormat('d/m/Y', $fechafin)->format('Y-m-d');
                                    $query->where('fecha', '<=', $enddate);
                                }
                            })->orderBy('id','DESC');
        $lista            = $resultado->get();
        //dd($lista,$resultado->toSql(),array($numero,$movimiento_id,$fechainicio,$fechafin));
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Proveedor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Boleta', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_ver  = 'Ver Venta';
        $ruta             = $this->rutas;
        $list = array();
        if ($request->session()->get('carritoventa') !== null) {
            $list = $request->session()->get('carritoventa');
            
        }
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.listDescarga')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'titulo_ver', 'ruta','list'));
        }
        return view($this->folderview.'.listDescarga')->with(compact('lista', 'entidad'));
    }

    public function descargaadmision(Request $request)
    {
        $entidad          = 'Venta';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $request->session()->forget('carritoventa');
        $movimiento_id = Libreria::getParam($request->input('movimiento_id'));
        return view($this->folderview.'.adminDescargaadmision')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta','movimiento_id'));
    }

    public function listardescargaadmision(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Venta';
        $fechainicio             = Libreria::getParam($request->input('fechainicio'));
        $fechafin             = Libreria::getParam($request->input('fechafin'));
        $numero             = Libreria::getParam($request->input('numero'));//quite momentaneamente ->where('formapago','=','P')
        $resultado        = Venta::where('tipomovimiento_id', '=', '4')
                            ->whereNotIn('tipodocumento_id',['15'])
                            ->where('ventafarmacia','=','S')
                            ->where('formapago','=','P')
                            ->where('situacion','<>','U')
                            ->where('situacion','<>','A')
                            ->where(function($query) use ($numero){   
                                if (!is_null($numero) && $numero !== '') {
                                   
                                    $query->where('numero', '=', $numero);
                                }
                            })->where(function($query) use ($fechainicio,$fechafin){   
                                if (!is_null($fechainicio) && $fechainicio !== '') {
                                    $begindate   = Date::createFromFormat('d/m/Y', $fechainicio)->format('Y-m-d');
                                    $query->where('fecha', '>=', $begindate);
                                }
                                if (!is_null($fechafin) && $fechafin !== '') {
                                    $enddate   = Date::createFromFormat('d/m/Y', $fechafin)->format('Y-m-d');
                                    $query->where('fecha', '<=', $enddate);
                                }
                            })->orderBy('id','DESC');
        $lista            = $resultado->get();
        //dd($lista);
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Proveedor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Boleta', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_ver  = 'Ver Venta';
        $ruta             = $this->rutas;
        $list = array();
        if ($request->session()->get('carritoventa') !== null) {
            $list = $request->session()->get('carritoventa');
            
        }
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.listDescargaadmision')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'titulo_ver', 'ruta','list'));
        }
        return view($this->folderview.'.listDescargaadmision')->with(compact('lista', 'entidad'));
    }

    public function agregardescarga(Request $request)
    {
        $lista = array();
        $monto = 0;
        if ($request->session()->get('carritoventa') !== null) {
            $lista = $request->session()->get('carritoventa');
            $venta_id = Libreria::getParam($request->input('venta_id'));
            $venta = Movimiento::find($venta_id);
            $estaPresente   = false;
            $indicepresente = '';
            for ($i=0; $i < count($lista); $i++) { 
                if ($lista[$i]['venta_id'] == $venta_id) {
                    $estaPresente   = true;
                    $indicepresente = $i;
                }
            }
            if ($estaPresente === true) {
                $lista[$indicepresente]  = array('venta_id' => $venta_id, 'monto' => $venta->total);
            }else{
                $lista[]  = array('venta_id' => $venta_id, 'monto' => $venta->total);
            }
            $request->session()->put('carritoventa', $lista);
        }else{
            $venta_id = Libreria::getParam($request->input('venta_id'));
            $venta = Movimiento::find($venta_id);
            $lista[]  = array('venta_id' => $venta_id, 'monto' => $venta->total);
            $request->session()->put('carritoventa', $lista);
            //echo count($lista);

        }

        for ($i=0; $i < count($lista); $i++) { 
            $monto = $monto + $lista[$i]['monto'];
        }

        return json_encode(array($monto,$lista));
    }

    public function quitardescarga(Request $request)
    {

        $monto = 0;
        if ($request->session()->get('carritoventa') !== null) {
            $venta_id       = $request->input('venta_id');
            $cantidad = count($request->session()->get('carritoventa'));
            $lista2   = $request->session()->get('carritoventa');
            $lista    = array();
            for ($i=0; $i < $cantidad; $i++) {
                if ($lista2[$i]['venta_id'] != $venta_id) {
                    $lista[] = $lista2[$i];
                }else{
                    $venta_id = $lista2[$i]['venta_id'];
                }
            }
            $request->session()->put('carritoventa', $lista);
            for ($i=0; $i < count($lista); $i++) { 
                $monto = $monto + $lista[$i]['monto'];
            }
        }


        return json_encode(array($monto,$lista));
    }

    public function guardardescarga(Request $request)
    {
        $movimiento_id  = $request->input('movimiento_id');
        $existe = Libreria::verificarExistencia($movimiento_id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($request,$movimiento_id){
            $Caja = Movimiento::find($movimiento_id);
            $Caja->situacion="C";//Aceptado
            /*$arr=explode(",",$Caja->listapago);
            for($c=0;$c<count($arr);$c++){
                $Detalle = Detallemovcaja::find($arr[$c]);
                if($Caja->conceptopago_id==6){//CAJA
                    $Detalle->situacion='C';//confirmado;
                }elseif($Caja->conceptopago_id==17){//SOCIO
                    $Detalle->situacionsocio='C';//confirmado;
                }elseif($Caja->conceptopago_id==15 || $Caja->conceptopago_id==21){//TARJETA Y BOLETEO TOTAL
                    $Detalle->situaciontarjeta='C';//confirmado;
                }
                $Detalle->save();
            }*/
            $Caja->save();
            /*if ($request->session()->get('carritoventa') !== null) {
                $lista = $request->session()->get('carritoventa');
                for ($i=0; $i < count($lista) ; $i++) { 
                    $venta = Movimiento::find($lista[$i]['venta_id']);
                    $venta->formapago = 'C';
                    $venta->save();
                }
            }*/
        });
        return is_null($error) ? "OK" : $error;
    }
    
	public function pdfRecibo(Request $request){
        $lista = Movimiento::where('id','=',$request->input('id'))->first();
                 // dd($lista->persona_id);   
        $pdf = new TCPDF();
        $pdf::SetTitle('Recibo de '.($lista->conceptopago->tipo=="I"?"Ingreso":"Egreso"));
        $pdf::AddPage();
        if($lista->conceptopago_id==10 || $lista->conceptopago_id == 150){//GARANTIAS
            $pdf::SetFont('helvetica','B',10);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 35, 10);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 105, 0, 35, 10);
            $pdf::Cell(50,10,utf8_decode("Recibo de ".($lista->conceptopago->tipo=="I"?"Ingreso":"Egreso")." Nro. ".$lista->numero),0,0,'C');
            $pdf::Cell(50,7,utf8_decode(""),0,0,'L');
            $pdf::Cell(50,10,utf8_decode("Recibo de ".($lista->conceptopago->tipo=="I"?"Ingreso":"Egreso")." Nro. ".$lista->numero),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(18,7,utf8_decode("FECHA :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(32,7,utf8_decode(date('d/m/Y',strtotime($lista->fecha))),0,0,'L');
            $pdf::Cell(50,7,utf8_decode(""),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(18,7,utf8_decode("FECHA :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(40,7,utf8_decode(date('d/m/Y',strtotime($lista->fecha))),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("CONCEPTO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,utf8_decode($lista->conceptopago->nombre),0,0,'L');
            $pdf::Cell(50,7,utf8_decode(""),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("CONCEPTO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,utf8_decode($lista->conceptopago->nombre),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("PACIENTE :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,($lista->persona->apellidopaterno." ".$lista->persona->apellidomaterno." ".$lista->persona->nombres),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("PACIENTE :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,($lista->persona->apellidopaterno." ".$lista->persona->apellidomaterno." ".$lista->persona->nombres),0,0,'L');
            $pdf::Ln();
            if($lista->doctor_id>0){
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,7,utf8_decode("DOCTOR :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(80,7,($lista->doctor->apellidopaterno." ".$lista->doctor->apellidomaterno." ".$lista->doctor->nombres),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,7,utf8_decode("DOCTOR :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(30,7,($lista->doctor->apellidopaterno." ".$lista->doctor->apellidomaterno." ".$lista->doctor->nombres),0,0,'L');
                $pdf::Ln();
            }
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("TOTAL :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,number_format($lista->total,2,'.',''),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("TOTAL :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,number_format($lista->total,2,'.',''),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(25,7,utf8_decode("COMENTARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(75,7,utf8_decode($lista->comentario),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(25,7,utf8_decode("COMENTARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,utf8_decode($lista->comentario),0,0,'L');
            $pdf::Ln();

            if($lista->conceptopago_id == 150){
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(28,7,utf8_decode("RESPONSABLE: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                
                // $pdf::Cell(70,7,"",1,0,'C');
                
                $x=$pdf::GetX();
                $y=$pdf::GetY();                    
                $pdf::Multicell(70,3,utf8_decode($lista->responsableGarantia),0,'L');
                $pdf::SetXY($x,$y);    
                $pdf::Cell(72,7,"",0,0,'C');
                // $pdf::Cell(72,7,utf8_decode($lista->responsableGarantia),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(28,7,utf8_decode("RESPONSABLE: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);

                $x=$pdf::GetX();
                $y=$pdf::GetY();                    
                $pdf::Multicell(70,3,utf8_decode($lista->responsableGarantia),0,'L');
                $pdf::SetXY($x,$y);
                // $pdf::Cell(80,3,utf8_decode($lista->responsableGarantia),0,0,'L');
                $pdf::Cell(80,7,"",0,0,'C');
                $pdf::Ln();                  
            }
            
            if(strlen($lista->responsableGarantia)>75){
                $pdf::Ln();                     
            }
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,($lista->responsable->nombres),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,($lista->responsable->nombres),0,0,'L');
            
        }elseif($lista->conceptopago_id==8 || $lista->conceptopago_id==45){//HONORARIOS MEDICOS
            $pdf::SetFont('helvetica','B',10);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 35, 10);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 105, 0, 35, 10);
            $pdf::Cell(50,10,utf8_decode("Recibo Medico Nro. ".$lista->numero),0,0,'C');
            $pdf::Cell(50,7,utf8_decode(""),0,0,'L');
            $pdf::Cell(50,10,utf8_decode("Recibo Medico Nro. ".$lista->numero),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("FECHA :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,utf8_decode($lista->fecha),0,0,'L');
            $pdf::Cell(50,7,utf8_decode(""),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("FECHA :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(40,7,utf8_decode($lista->fecha),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("DOCTOR :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,($lista->persona->apellidopaterno." ".$lista->persona->apellidomaterno." ".$lista->persona->nombres),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("DOCTOR :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,($lista->persona->apellidopaterno." ".$lista->persona->apellidomaterno." ".$lista->persona->nombres),0,0,'L');
            $pdf::Ln();
            $list=explode(",",$lista->listapago);
            for($c=0;$c<count($list);$c++){
                $detalle = Detallemovcaja::find($list[$c]);
                $movimiento = Movimiento::where("movimiento_id","=",$detalle->movimiento->id)->first();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,7,utf8_decode("PACIENTE :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(80,7,($movimiento->persona->apellidopaterno." ".$movimiento->persona->apellidomaterno." ".$movimiento->persona->nombres),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,7,utf8_decode("PACIENTE :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(30,7,($movimiento->persona->apellidopaterno." ".$movimiento->persona->apellidomaterno." ".$movimiento->persona->nombres),0,0,'L');
                $pdf::Ln();                
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,7,utf8_decode("SERVICIO :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                if($detalle->servicio_id>0){
                    $pdf::Cell(80,7,$detalle->servicio->nombre,0,0,'L');
                }else{
                    $pdf::Cell(80,7,$detalle->descripcion,0,0,'L');
                }
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,7,utf8_decode("SERVICIO :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                if($detalle->servicio_id>0){
                    $pdf::Cell(30,7,$detalle->servicio->nombre,0,0,'L');
                }else{
                    $pdf::Cell(30,7,$detalle->descripcion,0,0,'L');
                }
                $pdf::Ln();                
            }  
           
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("TOTAL :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,number_format($lista->total,2,'.',''),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("TOTAL :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,number_format($lista->total,2,'.',''),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,($lista->responsable->nombres),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,($lista->responsable->nombres),0,0,'L');
            
        }else{
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 20);
            $pdf::Cell(0,10,utf8_decode("Recibo de ".($lista->conceptopago->tipo=="I"?"Ingreso":"Egreso")." Nro. ".$lista->numero),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(18,7,utf8_decode("FECHA :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,utf8_decode($lista->fecha),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(25,7,utf8_decode("CONCEPTO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(40,7,utf8_decode($lista->conceptopago->nombre),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Ln();
            $pdf::Cell(18,7,utf8_decode("PERSONA :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            // dd($lista->persona);
            if (!is_null($lista->persona->apellidopaterno)) {            
                $pdf::Cell(80,7,($lista->persona->apellidopaterno." ".$lista->persona->apellidomaterno." ".$lista->persona->nombres),0,0,'L');
            }else{
                $pdf::Cell(80,7,($lista->persona->bussinesname),0,0,'L');
            }
            $pdf::Ln();
            if($lista->conceptopago->id=="6" || $lista->conceptopago->id=="7" || $lista->conceptopago->id=="8"){
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(23,7,utf8_decode("DOC. VENTA"),1,0,'C');
                $pdf::Cell(75,7,utf8_decode("PACIENTE"),1,0,'C');
                $pdf::Cell(75,7,utf8_decode("SERVICIO"),1,0,'C');
                $pdf::Cell(20,7,utf8_decode("TOTAL"),1,0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',7);
                $list=explode(",",$lista->listapago);
                for($c=0;$c<count($list);$c++){
                    $detalle = Detallemovcaja::find($list[$c]);
                    $movimiento = Movimiento::where("movimiento_id","=",$detalle->movimiento->id)->first();
                    if($movimiento->tipodocumento_id=="4") $abr="F";else $abr="B";
                    $pdf::Cell(23,7,utf8_decode($abr.$movimiento->serie."-".$movimiento->numero),1,0,'C');
                    $pdf::Cell(75,7,($movimiento->persona->apellidopaterno." ".$movimiento->persona->apellidomaterno." ".$movimiento->persona->nombres),1,0,'C');
                    $pdf::Cell(75,7,utf8_decode($detalle->servicio->nombre),1,0,'C');
                    $pdf::Cell(20,7,number_format($detalle->pagodoctor,2,'.',''),1,0,'C');
                    $pdf::Ln();                
                }    
            }
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(25,7,utf8_decode("TOTAL :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(40,7,number_format($lista->total,2,'.',''),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(25,7,utf8_decode("COMENTARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            // dd($lista->comentario);
            $pdf::Cell(0,7,$lista->comentario,0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(25,7,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,($lista->responsable->apellidopaterno." ".$lista->responsable->apellidomaterno." ".$lista->responsable->nombres),0,0,'L');
            $pdf::Ln();
        }
        $pdf::Output('ReciboCaja.pdf');
        
    }

    public function pdfHonorario(Request $request){
        $caja_id          = Libreria::getParam($request->input('caja_id'),'1');
        $caja                = Caja::find($request->input('caja_id'));
        $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('situacion','<>','A')->orderBy('movimiento.id','DESC')->limit(1)->first();
        if(count($rst)==0){
            $conceptopago_id=2;
        }else{
            $conceptopago_id=$rst->conceptopago_id;
        }
        
        $rst              = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',1)->orderBy('id','DESC')->limit(1)->first();
        if(count($rst)>0){
            $movimiento_mayor = $rst->id;    
        }else{
            $movimiento_mayor = 0;
        }
        
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            ->where('movimiento.situacion','<>','A')
                            ->whereNull('movimiento.cajaapertura_id')
                            ->whereIn('movimiento.conceptopago_id', [8]);
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2')->orderBy('paciente.apellidopaterno', 'asc');
        $lista            = $resultado->get();

        $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            ->where('movimiento.conceptopago_id', '=', 10)
                            ->where('movimiento.situacion', '<>', 'A');
        $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2')->orderBy('paciente.apellidopaterno', 'asc');
        $lista2            = $resultado2->get();

        $pdf = new TCPDF();
        $pdf::SetTitle('Honorarios y Garantias del '.($rst->fecha));
        if (count($lista) > 0 || count($lista2) > 0) {            
            $pdf::AddPage();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Honorarios Medicos del ".date("d/m/Y",strtotime($rst->fecha))." - ".$caja->nombre),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(20,6,utf8_decode("DOC. VENTA"),1,0,'C');
            $pdf::Cell(75,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(75,6,utf8_decode("SERVICIO"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("SUBTOTAL"),1,0,'C');
            $pdf::Ln();
            $doctor="";$total=0;$totalgeneral=0;
            foreach ($lista as $key => $value){
                if($doctor!=($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)){
                    $pdf::SetFont('helvetica','B',8);
                    if($doctor!=""){
                        $pdf::Cell(170,6,("TOTAL :"),1,0,'R');
                        $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
                        $totalgeneral = $totalgeneral + $total;
                        $total=0;
                        $pdf::Ln();
                    }
                    $doctor=($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres);
                    $pdf::Cell(190,6,($doctor),1,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',7);
                $list=explode(",",$value->listapago);
               
                for($c=0;$c<count($list);$c++){
                    $detalle = Detallemovcaja::find($list[$c]);
                    // dd($detalle);

                    $movimiento = Movimiento::where("movimiento_id","=",$detalle->movimiento->id)->first();
                    if($movimiento->tipodocumento_id=="4") $abr="F";else $abr="B";
                    $pdf::Cell(20,6,utf8_decode($abr.$movimiento->serie."-".$movimiento->numero),1,0,'C');
                    $pdf::Cell(75,6,($movimiento->persona->apellidopaterno." ".$movimiento->persona->apellidomaterno." ".$movimiento->persona->nombres),1,0,'L');
                    if($detalle->servicio_id>0){
                        $pdf::Cell(75,6,utf8_decode($detalle->servicio->nombre),1,0,'L');
                    }else{
                        $pdf::Cell(75,6,utf8_decode($detalle->descripcion),1,0,'L');
                    }
                    $pdf::Cell(20,6,number_format($detalle->pagodoctor*$detalle->cantidad,2,'.',''),1,0,'C');
                    $total=$total + $detalle->pagodoctor*$detalle->cantidad;
                    $pdf::Ln();                
                }    
            }
            $totalgeneral = $totalgeneral + $total;
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(170,6,("TOTAL :"),1,0,'R');
            $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(170,6,("TOTAL HONORARIOS :"),1,0,'R');
            $pdf::Cell(20,6,number_format($totalgeneral,2,'.',''),1,0,'C');
            $pdf::Ln();

            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                                        ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                        ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                                        ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                                        ->where('movimiento.caja_id', '=', $caja_id)
                                        ->where('movimiento.id', '>=', $movimiento_mayor)
                                        ->where('movimiento.situacion','<>','A')
                                        ->whereNull('movimiento.cajaapertura_id')
                                        ->whereIn('movimiento.conceptopago_id', [45]);
            $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2')->orderBy('paciente.apellidopaterno', 'asc');
            $lista            = $resultado->get();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(0,10,utf8_decode("Honorarios Medicos - Tarjeta del ".date("d/m/Y",strtotime($rst->fecha))." - ".$caja->nombre),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(20,6,utf8_decode("DOC. VENTA"),1,0,'C');
            $pdf::Cell(75,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(75,6,utf8_decode("SERVICIO"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("SUBTOTAL"),1,0,'C');
            $pdf::Ln();
            $doctor="";$total=0;$totalgeneral=0;
            foreach ($lista as $key => $value){
                if($doctor!=($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)){
                    $pdf::SetFont('helvetica','B',8);
                    if($doctor!=""){
                        $pdf::Cell(170,6,("TOTAL :"),1,0,'R');
                        $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
                        $totalgeneral = $totalgeneral + $total;
                        $total=0;
                        $pdf::Ln();
                    }
                    $doctor=($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres);
                    $pdf::Cell(190,6,($doctor),1,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',7);
                $list=explode(",",$value->listapago);
                for($c=0;$c<count($list);$c++){
                    $detalle = Detallemovcaja::find($list[$c]);
                    $movimiento = Movimiento::where("movimiento_id","=",$detalle->movimiento->id)->first();
                    if($movimiento->tipodocumento_id=="4") $abr="F";else $abr="B";
                    $pdf::Cell(20,6,utf8_decode($abr.$movimiento->serie."-".$movimiento->numero),1,0,'C');
                    $pdf::Cell(75,6,($movimiento->persona->apellidopaterno." ".$movimiento->persona->apellidomaterno." ".$movimiento->persona->nombres),1,0,'L');
                    if($detalle->servicio_id>0){
                        $pdf::Cell(75,6,utf8_decode($detalle->servicio->nombre),1,0,'L');
                    }else{
                        $pdf::Cell(75,6,utf8_decode($detalle->descripcion),1,0,'L');
                    }
                    $pdf::Cell(20,6,number_format($detalle->pagodoctor*$detalle->cantidad,2,'.',''),1,0,'C');
                    $total=$total + $detalle->pagodoctor*$detalle->cantidad;
                    $pdf::Ln();                
                }    
            }
            $totalgeneral = $totalgeneral + $total;
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(170,6,("TOTAL :"),1,0,'R');
            $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(170,6,("TOTAL HONORARIOS :"),1,0,'R');
            $pdf::Cell(20,6,number_format($totalgeneral,2,'.',''),1,0,'C');
            $pdf::Ln();


            $pdf::Ln();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(0,10,utf8_decode("Garantias de Medicos del ".date("d/m/Y",strtotime($rst->fecha))." - ".$caja->nombre),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(85,6,utf8_decode("MEDICO"),1,0,'C');
            $pdf::Cell(85,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("SUBTOTAL"),1,0,'C');
            $pdf::Ln();
            $total=0;
            $pdf::SetFont('helvetica','',8);
            foreach ($lista2 as $key => $value){
                if(isset($value->doctor)){
                    $pdf::Cell(85,6,utf8_decode($value->doctor->apellidopaterno." ".$value->doctor->apellidomaterno." ".$value->doctor->nombres),1,0,'L');
                } else {
                    $pdf::Cell(85,6,"ERROR",1,0,'L');
                }
                $pdf::Cell(85,6,($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres),1,0,'L');
                $pdf::Cell(20,6,number_format($value->total,2,'.',''),1,0,'C');
                $pdf::Ln();
                $total=$total + $value->total;                
            }
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(170,6,("TOTAL GARANTIAS :"),1,0,'R');
            $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,6,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $user = Auth::user();
            $pdf::Cell(80,6,($user->person->nombres),0,0,'L');
            $pdf::Ln();
        }
        $pdf::Output('ReporteHonorario.pdf');
    }

    public function pdfHonorarioF(Request $request){
        $caja_id          = Libreria::getParam($request->input('caja_id'),'1');
        $fecha          = $request->input('fecha');
        $caja                = Caja::find($request->input('caja_id'));
        
        ////////////////////////////////////////////////////////////////////

        $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('situacion','<>','A')->where('fecha','=',$fecha)->orderBy('movimiento.id','ASC')->limit(1)->first();
        if(count($rst)==0){
            $conceptopago_id=2;
        }else{
            $conceptopago_id=$rst->conceptopago_id;
        }
        
        $rst              = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',1)->where('fecha','=',$fecha)->orderBy('id','ASC')->limit(1)->first();
        if(count($rst)>0){
            $movimiento_mayor = $rst->id;  
            $responsable = $rst->responsable->nombres;
        }else{
            $movimiento_mayor = 0;
        }
        
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            ->where('movimiento.fecha', '=', $fecha)
                            ->where('movimiento.situacion','<>','A')
                            ->where('movimiento.conceptopago_id', '=', 8);
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2')->orderBy('paciente.apellidopaterno', 'asc');
        $lista            = $resultado->get();
        // dd($lista);
        
        $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            ->where('movimiento.fecha', '=', $fecha)
                            ->where('movimiento.conceptopago_id', '=', 10)
                            ->where('movimiento.situacion', '<>', 'A');
        $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2')->orderBy('paciente.apellidopaterno', 'asc');
        $lista2            = $resultado2->get();
        //dd($lista2);
        $pdf = new TCPDF();
        $pdf::SetTitle('Honorarios y Garantias del '.($rst->fecha));
        if (count($lista) > 0 || count($lista2) > 0) {            
            $pdf::AddPage();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Honorarios Medicos del ".date("d/m/Y",strtotime($rst->fecha))." - ".$caja->nombre),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(20,6,utf8_decode("DOC. VENTA"),1,0,'C');
            $pdf::Cell(75,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(75,6,utf8_decode("SERVICIO"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("SUBTOTAL"),1,0,'C');
            $pdf::Ln();
            $doctor="";$total=0;$totalgeneral=0;
            // dd($lista);
            foreach ($lista as $key => $value){
                if($doctor!=($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)){
                    $pdf::SetFont('helvetica','B',8);
                    if($doctor!=""){
                        $pdf::Cell(170,6,("TOTAL :"),1,0,'R');
                        $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
                        $totalgeneral = $totalgeneral + $total;
                        $total=0;
                        $pdf::Ln();
                    }
                    $doctor=($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres);
                    $pdf::Cell(190,6,($doctor),1,0,'L');
                    $pdf::Ln();
                }

            
                $pdf::SetFont('helvetica','',7);
                $list=explode(",",$value->listapago);
                for($c=0;$c<count($list);$c++){
                    $detalle = Detallemovcaja::find($list[$c]);
                    if(!is_null($detalle)){
                        $movimiento = Movimiento::where("movimiento_id","=",$detalle->movimiento->id)->first();
                        if($movimiento->tipodocumento_id=="4") $abr="F";else $abr="B";
                        $pdf::Cell(20,6,utf8_decode($abr.$movimiento->serie."-".$movimiento->numero),1,0,'C');
                        $pdf::Cell(75,6,($movimiento->persona->apellidopaterno." ".$movimiento->persona->apellidomaterno." ".$movimiento->persona->nombres),1,0,'L');
                        if($detalle->servicio_id>0){
                            $pdf::Cell(75,6,utf8_decode($detalle->servicio->nombre),1,0,'L');
                        }else{
                            $pdf::Cell(75,6,utf8_decode($detalle->descripcion),1,0,'L');
                        }
                        $pdf::Cell(20,6,number_format($detalle->pagodoctor*$detalle->cantidad,2,'.',''),1,0,'C');
                        // if($detalle->pagodoctor == 115 And $detalle->persona_id != 91){
                        //     dd($detalle);
                        // }
                        $total=$total + $detalle->pagodoctor*$detalle->cantidad;
                        $pdf::Ln();                

                    }

                    // if ($movimiento->persona->apellidopaterno =='ORDEMAR') {
                    //     dd($detalle);
                    // }
                }    
            }
            $totalgeneral = $totalgeneral + $total;
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(170,6,("TOTAL :"),1,0,'R');
            $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(170,6,("TOTAL HONORARIOS :"),1,0,'R');
            $pdf::Cell(20,6,number_format($totalgeneral,2,'.',''),1,0,'C');
            $pdf::Ln();

            $pdf::Ln();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(0,10,utf8_decode("Garantias de Medicos del ".date("d/m/Y",strtotime($rst->fecha))." - ".$caja->nombre),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(85,6,utf8_decode("MEDICO"),1,0,'C');
            $pdf::Cell(85,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("SUBTOTAL"),1,0,'C');
            $pdf::Ln();
            $total=0;
            $pdf::SetFont('helvetica','',8);
            foreach ($lista2 as $key => $value){
                $pdf::Cell(85,6,utf8_decode($value->doctor->apellidopaterno." ".$value->doctor->apellidomaterno." ".$value->doctor->nombres),1,0,'L');
                $pdf::Cell(85,6,($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres),1,0,'L');
                $pdf::Cell(20,6,number_format($value->total,2,'.',''),1,0,'C');
                $pdf::Ln();
                $total=$total + $value->total;                
            }
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(170,6,("TOTAL GARANTIAS :"),1,0,'R');
            $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,6,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $user = Auth::user();
            $pdf::Cell(80,6,($responsable),0,0,'L');
            $pdf::Ln();
        }

        unset($rst);
        unset($resultado);unset($resultado2);
        unset($lista);unset($lista2);

        ////////////////////////////////////////////////////////////////////

        $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('situacion','<>','A')->where('fecha','=',$fecha)->orderBy('movimiento.id','DESC')->limit(1)->first();
        if(count($rst)==0){
            $conceptopago_id=2;
        }else{
            $conceptopago_id=$rst->conceptopago_id;
        }
        
        $rst              = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',1)->where('fecha','=',$fecha)->orderBy('id','DESC')->limit(1)->first();
        if(count($rst)>0){
            $movimiento_mayor = $rst->id;  
            $responsable = $rst->responsable->nombres;
        }else{
            $movimiento_mayor = 0;
        }
        
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            ->where('movimiento.situacion','<>','A')
                            ->where('movimiento.conceptopago_id', '=', 8);
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2')->orderBy('paciente.apellidopaterno', 'asc');
        $lista            = $resultado->get();
        //dd($lista);
        $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            ->where('movimiento.conceptopago_id', '=', 10)
                            ->where('movimiento.situacion', '<>', 'A');
        $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2')->orderBy('paciente.apellidopaterno', 'asc');
        $lista2            = $resultado2->get();

        if (count($lista) > 0 || count($lista2) > 0) {            
            $pdf::AddPage();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Honorarios Medicos del ".date("d/m/Y",strtotime($rst->fecha))." - ".$caja->nombre),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(20,6,utf8_decode("DOC. VENTA"),1,0,'C');
            $pdf::Cell(75,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(75,6,utf8_decode("SERVICIO"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("SUBTOTAL"),1,0,'C');
            $pdf::Ln();
            $doctor="";$total=0;$totalgeneral=0;
            foreach ($lista as $key => $value){
                if($doctor!=($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)){
                    $pdf::SetFont('helvetica','B',8);
                    if($doctor!=""){
                        $pdf::Cell(170,6,("TOTAL :"),1,0,'R');
                        $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
                        $totalgeneral = $totalgeneral + $total;
                        $total=0;
                        $pdf::Ln();
                    }
                    $doctor=($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres);
                    $pdf::Cell(190,6,($doctor),1,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',7);
                $list=explode(",",$value->listapago);
                for($c=0;$c<count($list);$c++){
                    $detalle = Detallemovcaja::find($list[$c]);
                    $movimiento = Movimiento::where("movimiento_id","=",$detalle->movimiento->id)->first();
                    if($movimiento->tipodocumento_id=="4") $abr="F";else $abr="B";
                    $pdf::Cell(20,6,utf8_decode($abr.$movimiento->serie."-".$movimiento->numero),1,0,'C');
                    $pdf::Cell(75,6,($movimiento->persona->apellidopaterno." ".$movimiento->persona->apellidomaterno." ".$movimiento->persona->nombres),1,0,'L');
                    if($detalle->servicio_id>0){
                        $pdf::Cell(75,6,utf8_decode($detalle->servicio->nombre),1,0,'L');
                    }else{
                        $pdf::Cell(75,6,utf8_decode($detalle->descripcion),1,0,'L');
                    }
                    $pdf::Cell(20,6,number_format($detalle->pagodoctor*$detalle->cantidad,2,'.',''),1,0,'C');
                    $total=$total + $detalle->pagodoctor*$detalle->cantidad;
                    $pdf::Ln();                
                }    
            }
            $totalgeneral = $totalgeneral + $total;
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(170,6,("TOTAL :"),1,0,'R');
            $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(170,6,("TOTAL HONORARIOS :"),1,0,'R');
            $pdf::Cell(20,6,number_format($totalgeneral,2,'.',''),1,0,'C');
            $pdf::Ln();

            //EDUARDO: AGREGO PAGO DE TARJETAS AL REPORTE 

            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                                        ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                        ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                                        ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                                        ->where('movimiento.caja_id', '=', $caja_id)
                                        ->where('movimiento.id', '>=', $movimiento_mayor)
                                        ->where('movimiento.situacion','<>','A')
                                        ->whereNull('movimiento.cajaapertura_id')
                                        ->whereIn('movimiento.conceptopago_id', [45]);
            $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2')->orderBy('paciente.apellidopaterno', 'asc');
            //dd($movimiento_mayor);
            $lista            = $resultado->get();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(0,10,utf8_decode("Honorarios Medicos - Tarjeta del ".date("d/m/Y",strtotime($rst->fecha))." - ".$caja->nombre),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(20,6,utf8_decode("DOC. VENTA"),1,0,'C');
            $pdf::Cell(75,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(75,6,utf8_decode("SERVICIO"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("SUBTOTAL"),1,0,'C');
            $pdf::Ln();
            $doctor="";$total=0;$totalgeneral=0;
            foreach ($lista as $key => $value){
                if($doctor!=($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)){
                    $pdf::SetFont('helvetica','B',8);
                    if($doctor!=""){
                        $pdf::Cell(170,6,("TOTAL :"),1,0,'R');
                        $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
                        $totalgeneral = $totalgeneral + $total;
                        $total=0;
                        $pdf::Ln();
                    }
                    $doctor=($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres);
                    $pdf::Cell(190,6,($doctor),1,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',7);
                $list=explode(",",$value->listapago);
                for($c=0;$c<count($list);$c++){
                    $detalle = Detallemovcaja::find($list[$c]);
                    $movimiento = Movimiento::where("movimiento_id","=",$detalle->movimiento->id)->first();
                    // $ticket = Movimiento::find($detalle->movimiento->id)
                    if($movimiento->tipodocumento_id=="4") $abr="F";else $abr="B";
                    $pdf::Cell(20,6,utf8_decode($abr.$movimiento->serie."-".$movimiento->numero),1,0,'C');
                    $pdf::Cell(75,6,($movimiento->persona->apellidopaterno." ".$movimiento->persona->apellidomaterno." ".$movimiento->persona->nombres),1,0,'L');
               
                    $pdf::SetFont('helvetica','',6.5);
                    if($detalle->servicio_id>0){
                        $pdf::Cell(75,6,utf8_decode($detalle->servicio->nombre.' - '.$detalle->movimiento->tarjeta.' '.$detalle->movimiento->voucher),1,0,'L');
                    }else{
                        $pdf::Cell(75,6,utf8_decode($detalle->descripcion.' - '.$detalle->movimiento->tarjeta.' '.$detalle->movimiento->voucher),1,0,'L');
                    }

                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(20,6,number_format($detalle->pagodoctor*$detalle->cantidad,2,'.',''),1,0,'C');
                    $total=$total + $detalle->pagodoctor*$detalle->cantidad;
                    $pdf::Ln();                
                }    
            }
            $totalgeneral = $totalgeneral + $total;
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(170,6,("TOTAL :"),1,0,'R');
            $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(170,6,("TOTAL HONORARIOS :"),1,0,'R');
            $pdf::Cell(20,6,number_format($totalgeneral,2,'.',''),1,0,'C');
            $pdf::Ln();


            $pdf::Ln();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(0,10,utf8_decode("Garantias de Medicos del ".date("d/m/Y",strtotime($rst->fecha))." - ".$caja->nombre),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(85,6,utf8_decode("MEDICO"),1,0,'C');
            $pdf::Cell(85,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("SUBTOTAL"),1,0,'C');
            $pdf::Ln();
            $total=0;
            $pdf::SetFont('helvetica','',8);
            foreach ($lista2 as $key => $value){
                $pdf::Cell(85,6,utf8_decode($value->doctor->apellidopaterno." ".$value->doctor->apellidomaterno." ".$value->doctor->nombres),1,0,'L');
                $pdf::Cell(85,6,($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres),1,0,'L');
                $pdf::Cell(20,6,number_format($value->total,2,'.',''),1,0,'C');
                $pdf::Ln();
                $total=$total + $value->total;                
            }
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(170,6,("TOTAL GARANTIAS :"),1,0,'R');
            $pdf::Cell(20,6,number_format($total,2,'.',''),1,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,6,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $user = Auth::user();
            $pdf::Cell(80,6,($responsable),0,0,'L');
            $pdf::Ln();
        }
        $pdf::Output('ReporteHonorarioF.pdf');
    }

    public function venta(Request $request)
    {//PAGO PARTICULAR
        $user = Auth::user();
        $resultado  = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                        ->join('person as medico','medico.id','=','dmc.persona_id')
                        ->join('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                        ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                        ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$request->input('busqueda').'%')
                        ->where('movimiento.tipomovimiento_id','=',1)
                        ->whereNull('movimiento.tarjeta')
                        ->where('dmc.situacion','LIKE','N')
                        ->where('movimiento.plan_id','=',6)
                        ->where('dmc.pagodoctor','>',0);
        if($request->input('miusuario')=="S"){
            $resultado = $resultado->where("movimiento.responsable_id","=",$user->person_id);
        }
        $resultado = $resultado->orderBy('mref.fecha', 'ASC')
                        ->select('mref.*','dmc.servicio_id','dmc.id as iddetalle','s.nombre as servicio','dmc.cantidad','dmc.descripcion as servicio2','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'));
        $list      = $resultado->get();
        $registro="<table class='table table-hover'>
                    <thead>
                        <tr>
                            <th  width='80px'  class='text-center'>Nro</th>
                            <th class='text-center'>Paciente</th>
                            <th class='text-center'>Servicio</th>
                            <th class='text-center'>Pago</th>
                        </tr>
                    </thead>
                    <tbody>";
        foreach ($list as $key => $value) {
            if($value->servicio_id>0){
                $servicio=$value->servicio;
            }else{
                $servicio=$value->servicio2;
            }
            $numero=($value->tipodocumento_id==4?'F':'B').$value->serie.'-'.$value->numero;
            $registro.="<tr onclick=\"agregarDoc($value->iddetalle,'".trim($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname)."','$servicio','$numero','".number_format($value->pagodoctor*$value->cantidad,2,'.','')."')\">";
            $registro.="<td  width='80px' >".$numero."</td>";
            $registro.="<td>".trim($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname)."</td>";
            $registro.="<td>".$servicio."</td>";
            $registro.="<td>".number_format($value->pagodoctor*$value->cantidad,2,'.','')."</td>";
            $registro.="</tr>";
        }
        $registro.="</tbody></table>";
        echo $registro;
    }

    public function ventasocio(Request $request)
    {
        $user = Auth::user();
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->join('person as paciente2','paciente2.id','=','movimiento.persona_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$request->input('busqueda').'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.medicosocio_id')
                            ->Where(function($query){
                                $query->whereNull('dmc.situacionboleteo')
                                      ->orWhere(function($q){
                                            $q->whereNull('dmc.situaciontarjeta');
                                      });
                            });
        if($request->input('miusuario')=="S"){
            $resultado = $resultado->where("movimiento.responsable_id","=",$user->person_id);
        }
        $resultado = $resultado->orderBy('mref.fecha', 'ASC')
                            ->select('mref.*','dmc.id as iddetalle','s.nombre as servicio','dmc.descripcion as servicio2','dmc.pagodoctor','dmc.cantidad','dmc.servicio_id',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente2.apellidopaterno,\' \',paciente2.apellidomaterno,\' \',paciente2.nombres) as nombrepaciente2'));
        $list      = $resultado->get();
        $registro="<table class='table table-hover'>
                    <thead>
                        <tr>
                            <th width='80px' class='text-center'>Nro</th>
                            <th class='text-center'>Paciente</th>
                            <th class='text-center'>Servicio</th>
                            <th class='text-center'>Pago</th>
                        </tr>
                    </thead>
                    <tbody>";
        foreach ($list as $key => $value) {
            if($value->servicio_id>0){
                $servicio=$value->servicio;
            }else{
                $servicio=$value->servicio2;
            }
            $numero=($value->tipodocumento_id==4?'F':'B').$value->serie.'-'.$value->numero;
            $registro.="<tr onclick=\"agregarDocSocio($value->iddetalle,'".trim($value->nombrepaciente2)."','$servicio','$numero','".number_format($value->pagodoctor*$value->cantidad,2,'.','')."')\">";
            $registro.="<td width='80px'>".$numero."</td>";
            $registro.="<td>".trim($value->nombrepaciente2)."</td>";
            $registro.="<td>".$servicio."</td>";
            $registro.="<td>".number_format($value->pagohospital*$value->cantidad,2,'.','')."</td>";
            $registro.="</tr>";
        }
        $registro.="</tbody></table>";
        echo $registro;
    }

    public function ventatarjeta(Request $request)
    {
        $user = Auth::user();
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->join('person as paciente2','paciente2.id','=','movimiento.persona_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$request->input('busqueda').'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNotNull('movimiento.tarjeta')
                            ->whereNull('dmc.medicosocio_id')
                            ->whereNull('dmc.situaciontarjeta');
        if($request->input('miusuario')=="S"){
            $resultado = $resultado->where("movimiento.responsable_id","=",$user->person_id);
        }
        $resultado = $resultado->orderBy('mref.fecha', 'ASC')
                            ->select('mref.*','dmc.id as iddetalle','s.nombre as servicio','dmc.descripcion as servicio2','dmc.pagodoctor','dmc.cantidad','dmc.servicio_id',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente2.apellidopaterno,\' \',paciente2.apellidomaterno,\' \',paciente2.nombres) as nombrepaciente2'));
        $list      = $resultado->get();
        $registro="<table class='table table-hover'>
                    <thead>
                        <tr>
                            <th width='80px' class='text-center'>Nro</th>
                            <th class='text-center'>Paciente</th>
                            <th class='text-center'>Servicio</th>
                            <th class='text-center'>Pago</th>
                        </tr>
                    </thead>
                    <tbody>";
        foreach ($list as $key => $value) {
            if($value->servicio_id>0){
                $servicio=$value->servicio;
            }else{
                $servicio=$value->servicio2;
            }
            $numero=($value->tipodocumento_id==4?'F':'B').$value->serie.'-'.$value->numero;
            $registro.="<tr onclick=\"agregarDocSocio($value->iddetalle,'".trim($value->nombrepaciente2)."','$servicio','$numero','".number_format($value->pagodoctor*$value->cantidad,2,'.','')."')\">";
            $registro.="<td width='80px'>".$numero."</td>";
            $registro.="<td>".trim($value->nombrepaciente2)."</td>";
            $registro.="<td>".$servicio."</td>";
            $registro.="<td>".number_format($value->pagohospital*$value->cantidad,2,'.','')."</td>";
            $registro.="</tr>";
        }
        $registro.="</tbody></table>";
        echo $registro;
    }

    public function ventaboleteo(Request $request)
    {
        $user = Auth::user();
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->join('person as paciente2','paciente2.id','=','movimiento.persona_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$request->input('busqueda').'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            //->where('movimiento.id','=',405414)
                            ->where('dmc.pagodoctor','=',0)
                            ->where('dmc.situacion','LIKE','N')
                            ->whereNotIn('mref.situacion',['B'])
                            ;
                            //->where('movimiento.plan_id','=',6);
        if($request->input('miusuario')=="S"){
            $resultado = $resultado->where("movimiento.responsable_id","=",$user->person_id);
        }
        $resultado = $resultado->orderBy('mref.fecha', 'ASC')
                            ->select('mref.*','dmc.id as iddetalle','s.nombre as servicio','dmc.descripcion as servicio2','dmc.pagodoctor','dmc.cantidad','dmc.servicio_id',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente2.apellidopaterno,\' \',paciente2.apellidomaterno,\' \',paciente2.nombres) as nombrepaciente2'));
                            //dd($resultado->toSql());
        $list      = $resultado->get();
        $registro="<table class='table table-hover'>
                    <thead>
                        <tr>
                            <th width='80px' class='text-center'>Nro</th>
                            <th class='text-center'>Paciente</th>
                            <th class='text-center'>Servicio</th>
                            <th class='text-center'>Pago</th>
                        </tr>
                    </thead>
                    <tbody>";
        foreach ($list as $key => $value) {
            if($value->servicio_id>0){
                $servicio=$value->servicio;
            }else{
                $servicio=$value->servicio2;
            }
            $numero=($value->tipodocumento_id==4?'F':'B').$value->serie.'-'.$value->numero;
            $registro.="<tr onclick=\"agregarDocSocio($value->iddetalle,'".trim($value->nombrepaciente2)."','".$servicio."','$numero','".number_format($value->pagodoctor*$value->cantidad,2,'.','')."')\">";
            $registro.="<td width='80px'>".$numero."</td>";
            $registro.="<td>".trim($value->nombrepaciente2)."</td>";
            $registro.="<td>".$servicio."</td>";
            $registro.="<td>".number_format($value->pagohospital*$value->cantidad,2,'.','')."</td>";
            $registro.="</tr>";
        }
        $registro.="</tbody></table>";
        echo $registro;
    }


    public function ventapago(Request $request)
    {
        $concepto_tipo = $request->input('concepto');
        $anio = '2019'; //date('Y');
        if($concepto_tipo == 35){
            if(strpos($request->input('busqueda'),'HOYOS ARRASCUE JHONY') == true){
                $user = Auth::user();

             
                $registro="<table class='table table-hover' id='tabla' style='max-heigth:150px;'>
                            <thead>
                                <tr>
                                    <th  width='80px'  class='text-center'>Nro</th>
                                    <th class='text-center'>Paciente</th>
                                    <th class='text-center'>Servicio</th>
                                    <th class='text-center'>Pago</th>
                                    <th class='text-center'>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>";

                $resultado2        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.movimiento_id')
                    ->join('person as medico','medico.id','=','dmc.persona_id')
                    ->join('historia as his','his.person_id','=','movimiento.persona_id')
                    // ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                    ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$request->input('busqueda').'%')
                    ->whereNotNull('medico.especialidad_id')
                    ->where('movimiento.tipomovimiento_id','=',4)
                    ->where('movimiento.situacion','<>','U')
                    ->where('dmc.pagodoctor','>',0)
                    ->where('his.tipopaciente','!=','Convenio')
                    // ->whereNull('dmc.situacion_ecografia')
                    ->where(function($query){
                        $query
                            ->whereNull('dmc.situacion_ecografia')
                            ->orWhere('dmc.situacion_ecografia','<>','P');
                    })
                    ->whereNull('dmc.pago_ecografia')
                    ->where('dmc.tiposervicio_id','=',4)
                    ->whereIn('dmc.situacion',['N','C'])
                    ->whereNotIn('dmc.situacionentrega',['E'])
                    // ->whereMonth('movimiento.fecha','>=','09')
                    ->whereYear('movimiento.fecha','>=',$anio);
                    
                    // ->where('movimiento.id','=','545459');
                if($request->input('miusuario')=="S"){
                    $resultado2 = $resultado2->where("movimiento.responsable_id","=",$user->person_id);
                }

                $resultado2 = $resultado2->orderBy('movimiento.fecha','DESC')
                                    ->select('movimiento.*','dmc.descripcion as servicio2','dmc.id as iddetalle','dmc.situacion','dmc.servicio_id','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'));
                $list2      = $resultado2->get();

                 // dd($list2);

                foreach ($list2 as $key => $value) {
                    if($value->servicio_id>0){
                        $servicio = Servicio::find($value->servicio_id)->nombre;
                    }else{
                        $servicio=$value->servicio2;
                    }
                    $registro.="<tr onclick=\"agregarDoc($value->iddetalle,'".trim($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname)."','$servicio','".($value->tipodocumento_id==4?"F":"B")."$value->serie-$value->numero','".number_format($value->pagodoctor*$value->cantidad,2,'.','')."')\">";
                    $registro.="<td  width='80px' >".($value->tipodocumento_id==1?"":($value->tipodocumento_id==4?"F":"B")).(is_null($value->serie)?'':'-').$value->numero."</td>";
                    $registro.="<td width='200px'>".trim($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname)."</td>";
                    $registro.="<td>".$servicio."</td>";
                    $registro.="<td>".number_format($value->pagodoctor*$value->cantidad,2,'.','')."</td>";
                    $registro.="<td>".date('d/m/Y',strotime($value->fecha))."</td>";
                    $registro.="</tr>";
                }
            

                $registro.="</tbody></table>";
                
                echo $registro;
            }else{
                $user = Auth::user();

                $resultado        = Movimiento::leftjoin('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                    ->leftjoin('person as medico','medico.id','=','dmc.persona_id')
                    ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like', $request->input('busqueda').'%')
                    ->where('movimiento.tipomovimiento_id','=','1')
                    ->where('movimiento.situacion','<>','U')
                    ->where('dmc.tiposervicio_id','=','4')
                    ->whereNotNull('medico.especialidad_id')
                    ->whereIn('dmc.situacion',['N','C'])
                    ->where(function($query){
                        $query
                            ->whereNull('dmc.situacion_ecografia')
                            ->orWhere('dmc.situacion_ecografia','<>','P');
                    })
                    ->whereNull('dmc.pago_ecografia') 
                    ->whereNotIn('dmc.situacionentrega',['E'])
                    // ->whereMonth('movimiento.fecha','>=','09')
                    ->whereYear('movimiento.fecha','>=',$anio);               
                    // ->where('movimiento.id','=','635168');
                if($request->input('miusuario')=="S"){
                    $resultado = $resultado->where("movimiento.responsable_id","=",$user->person_id);
                }

                $resultado = $resultado->orderBy('movimiento.fecha', 'DESC')->orderBy('movimiento.fecha', 'ASC')
                                    ->select('movimiento.*','dmc.descripcion as servicio2','dmc.id as iddetalle','dmc.situacion','dmc.servicio_id','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'));
                $list      = $resultado->get();

                $registro="<table class='table table-hover' id='tabla' style='max-heigth:150px;'>
                            <thead>
                                <tr>
                                    <th  width='80px'  class='text-center'>Nro</th>
                                    <th class='text-center'>Paciente</th>
                                    <th class='text-center'>Servicio</th>
                                    <th class='text-center'>Fecha</th>
                                    
                                </tr>
                            </thead>
                            <tbody>";

                // dd($list);
                // exit;
                foreach ($list as $key => $value) {
                    if($value->servicio_id>0){
                     $servicio = Servicio::find($value->servicio_id)->nombre;
                        // $servicio=$value->servicio->nombre;
                    }else{
                        $servicio=$value->servicio2;
                    }
                    $registro.="<tr onclick=\"agregarDoc($value->iddetalle,'".trim($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname)."','$servicio','".($value->tipodocumento_id==4?"F":"B")."$value->serie-$value->numero','".number_format($value->pagodoctor*$value->cantidad,2,'.','')."')\">";
                    $registro.="<td  width='80px' >".($value->tipodocumento_id==1?"":($value->tipodocumento_id==4?"F":"B")).(is_null($value->serie)?'':'-').$value->numero."</td>";
                    $registro.="<td width='200px'>".trim($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname)."</td>";
                    $registro.="<td>".$servicio."</td>";
                    $registro.="<td>".date('d/m/Y',strtotime($value->fecha))."</td>";
                    
                    // $registro.="<td>".number_format($value->pagodoctor*$value->cantidad,2,'.','')."</td>";
                    $registro.="</tr>";
                }
            

                // $resultado2        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.movimiento_id')
                //     ->join('person as medico','medico.id','=','dmc.persona_id')
                //     ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                //     ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$request->input('busqueda').'%')
                //     ->where('movimiento.tipomovimiento_id','=',4)
                //     ->where('movimiento.situacion','<>','U')
                //     // ->where('dmc.pagodoctor','>',0)
                //     ->where('s.tiposervicio_id','=',4)
                //     ->whereIn('dmc.situacion',['N','C'])
                //     // ->whereNull('dmc.situacion_ecografia')
                //     ->where('dmc.situacion_ecografia','<>','P')
                //     ->whereNull('dmc.pago_ecografia')
                //     ->whereNotIn('dmc.situacionentrega',['E'])
                //     ->whereMonth('movimiento.fecha','>=','09')
                //     ->whereYear('movimiento.fecha','>=',$anio);
                    
                //     // ->where('movimiento.id','=','545459');
                // if($request->input('miusuario')=="S"){
                //     $resultado2 = $resultado2->where("movimiento.responsable_id","=",$user->person_id);
                // }

                // $resultado2 = $resultado2->orderBy('movimiento.fecha', 'ASC')
                //                     ->select('movimiento.*','dmc.descripcion as servicio2','dmc.id as iddetalle','dmc.situacion','dmc.servicio_id','s.nombre as servicio','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'));
                // $list2      = $resultado2->get();

                //  // dd($list2);

                // foreach ($list2 as $key => $value) {
                //     if($value->servicio_id>0){
                //         $servicio=$value->servicio;
                //     }else{
                //         $servicio=$value->servicio2;
                //     }
                //     $registro.="<tr onclick=\"agregarDoc($value->iddetalle,'".trim($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname)."','$servicio','".($value->tipodocumento_id==4?"F":"B")."$value->serie-$value->numero','".number_format($value->pagodoctor*$value->cantidad,2,'.','')."')\">";
                //     $registro.="<td  width='80px' >".($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero."</td>";
                //     $registro.="<td>".trim($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname)."</td>";
                //     $registro.="<td>".$servicio."</td>";
                //     // $registro.="<td>".number_format($value->pagodoctor*$value->cantidad,2,'.','')."</td>";
                //     $registro.="</tr>";
                // }
            

                $registro.="</tbody></table>";
                
                echo $registro;
           
            }
        }else{
            $user = Auth::user();
            $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$request->input('busqueda').'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->where('dmc.pagodoctor','>',0)
                            ->whereNotNull('medico.especialidad_id')
                            ->whereIn('dmc.situacion',['N','C'])
                            ->whereNotIn('dmc.situacionentrega',['E']);
                            // ->where('movimiento.id','=','501873');
            if($request->input('miusuario')=="S"){
                $resultado = $resultado->where("movimiento.responsable_id","=",$user->person_id);
            }

            $resultado = $resultado->orderBy('mref.fecha', 'ASC')
                                ->select('mref.*','dmc.descripcion as servicio2','dmc.id as iddetalle','dmc.situacion','dmc.servicio_id','s.nombre as servicio','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'));
            $list      = $resultado->get();
            $registro="<table class='table table-hover' style='max-heigth:150px;'>
                        <thead>
                            <tr>
                                <th  width='80px'  class='text-center'>Nro</th>
                                <th class='text-center'>Paciente</th>
                                <th class='text-center'>Servicio</th>
                                <th class='text-center'>Pago</th>
                            </tr>
                        </thead>
                        <tbody>";

            // echo json_encode($list);
            // exit;
            foreach ($list as $key => $value) {
                if($value->servicio_id>0){
                    $servicio=$value->servicio;
                }else{
                    $servicio=$value->servicio2;
                }
                $registro.="<tr onclick=\"agregarDoc($value->iddetalle,'".trim($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname)."','$servicio','".($value->tipodocumento_id==4?"F":"B")."$value->serie-$value->numero','".number_format($value->pagodoctor*$value->cantidad,2,'.','')."')\">";
                $registro.="<td  width='80px' >".($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero."</td>";
                $registro.="<td>".trim($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname)."</td>";
                $registro.="<td>".$servicio."</td>";
                $registro.="<td>".number_format($value->pagodoctor*$value->cantidad,2,'.','')."</td>";
                $registro.="</tr>";
            }
            $registro.="</tbody></table>";
            echo $registro;
        }
    }


    /////////////////////////////////////////////////////////////////////////////

    public function control(Request $request){
        $entidad          = 'Caja';
        $title            = 'Garantia Control Remoto';
        $ruta             = $this->rutas;
        return view($this->folderview.'.control')->with(compact('entidad', 'title', 'ruta'));
    }

    public function buscarcontrol(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'buscarcontrol';
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->whereIn('movimiento.conceptopago_id',[29,30]);
                            //->where('movimiento.situacion','<>','U')
                            //->where('m2.tipomovimiento_id','=',1);
        if($request->input('fecha')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fecha').' 00:00:00');
        }
        if($request->input('numero')!=""){
            $resultado = $resultado->where('movimiento.numero','LIKE','%'.$request->input('numero').'%');
        }    
        if($request->input('paciente')!=""){
            $resultado = $resultado->where(DB::raw('concat(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres)'),'LIKE','%'.strtoupper($request->input('paciente')).'%');
        }   

        $resultado        = $resultado->select('movimiento.*','movimiento.numero as numero2','paciente.apellidopaterno','paciente.apellidomaterno','paciente.nombres')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $user = Auth::user();
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
            return view($this->folderview.'.list3')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'ruta', 'user'));
        }
        return view($this->folderview.'.list3')->with(compact('lista', 'entidad'));
    }
}