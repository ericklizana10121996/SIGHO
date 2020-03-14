<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Convenio;
use App\Caja;
use App\Area;
use App\Person;
use App\Venta;
use App\Movimiento;
use App\Tipodocumento;
use App\Conceptopago;
use App\Detallemovcaja;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;
use Excel;

class CajatesoreriaController extends Controller
{
    protected $folderview      = 'app.cajatesoreria';
    protected $tituloAdmin     = 'Caja Tesoreria';
    protected $tituloRegistrar = 'Registrar Movimiento de Caja Tesoreria';
    protected $tituloModificar = 'Modificar Caja';
    protected $tituloEliminar  = 'Eliminar Caja';
    protected $rutas           = array('create' => 'cajatesoreria.create', 
            'edit'   => 'caja.edit', 
            'delete' => 'cajatesoreria.eliminar',
            'search' => 'cajatesoreria.buscar',
            'index'  => 'cajatesoreria.index',
            'pdfListar'  => 'cajatesoreria.pdfListar',
            'apertura' => 'cajatesoreria.apertura',
            'cierre' => 'cajatesoreria.cierre',
            'acept' => 'cajatesoreria.acept',
            'reject' => 'cajatesoreria.reject',
            'imprimir' => 'cajatesoreria.imprimir',
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
        $entidad          = 'Cajatesoreria';
        $caja_id          = Libreria::getParam($request->input('caja_id'),'6');
        $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->orderBy('movimiento.id','DESC')->limit(1)->first();
        if(count($rst)==0){
            $conceptopago_id=2;
        }else{
            $conceptopago_id=$rst->conceptopago_id;
        }
        $titulo_registrar = $this->tituloRegistrar;
        $titulo_apertura  = 'Apertura';
        $titulo_cierre    = 'Cierre'; 
        
        $rst              = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->whereNull('deleted_at')->where('conceptopago_id','=',1)->orderBy('id','DESC')->limit(1)->first();
        
        // $movimiento_mayor = 563597;

        if(count($rst)>0){
            $movimiento_mayor = $rst->id;    
        } else{
            $movimiento_mayor = 0;
        }
        
        // dd($movimiento_mayor);

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','movimiento.id','=','m2.movimiento_id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            // ->where('movimiento.id','<',563865)
                            ->where(function($sql) use($movimiento_mayor) {
                                $sql->where('movimiento.id', '>=', $movimiento_mayor)
                                    ->whereNull('movimiento.cajaapertura_id')
                                    ->orWhere('movimiento.cajaapertura_id','=',$movimiento_mayor);
                                })
                            ;
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2',DB::raw('CASE WHEN paciente.bussinesname is null then CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.bussinesname end as paciente'),DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.id', 'desc');
        $lista            = $resultado->get();
    
        // dd($lista);
    
        $cabecera         = array();
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Numero', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Concepto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Ingreso', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Egreso', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Forma Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Entregado a', 'numero' => '1');
        $cabecera[]       = array('valor' => 'DNI', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_anular    = "Anular";
        $ruta             = $this->rutas;
        $ingreso=0;$egreso=0;$garantia=0;$efectivo=0;$master=0;$visa=0;
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            foreach($lista as $k=>$v){
                if($v->conceptopago_id<>2 && $v->situacion<>'A'){
                    if($v->conceptopago->tipo=="I"){
                        if($v->conceptopago_id<>10 && $v->conceptopago_id<>150){//Garantias
                            if($v->conceptopago_id<>15 && $v->conceptopago_id<>17 && $v->conceptopago_id<>19 && $v->conceptopago_id<>21){
                                $ingreso = $ingreso + $v->total;    
                            }elseif(($v->conceptopago_id==15 || $v->conceptopago_id==17 || $v->conceptopago_id==19 || $v->conceptopago_id==21) && $v->situacion=='C'){
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
                    }else{
                        if($v->conceptopago_id<>14 && $v->conceptopago_id<>16 && $v->conceptopago_id<>18 && $v->conceptopago_id<>20){
                            $egreso  = $egreso + $v->total;
                        }elseif(($v->conceptopago_id==14 || $v->conceptopago_id==16 || $v->conceptopago_id==18 || $v->conceptopago_id==20) && $v->situacion2=='C'){
                            $egreso  = $egreso + $v->total;
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'conceptopago_id', 'titulo_registrar', 'titulo_apertura', 'titulo_cierre', 'ingreso', 'egreso', 'titulo_anular', 'garantia', 'efectivo', 'visa', 'master' ));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad', 'conceptopago_id', 'titulo_registrar', 'titulo_apertura', 'titulo_cierre', 'ruta', 'ingreso', 'egreso','visa', 'master'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $entidad          = 'Cajatesoreria';
        $title            = $this->tituloAdmin;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'ruta', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Cajatesoreria';
        $caja = null;
        $cboTipoDoc = array();
        $rs = Tipodocumento::where(DB::raw('1'),'=','1')->where('tipomovimiento_id','=',2)->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboTipoDoc = $cboTipoDoc + array($value->id => $value->nombre);
        }
        $cboConcepto = array();
        $rs = Conceptopago::where('tipo','like','E')->where('id','<>',2)->where('id','<>',13)->where('id','<>',24)->where('id','<>',25)->where('id','<>',26)->where('id','<>',20)->where('id','<>',8)->where('id','<>',18)->where('id','<>',16)->where('id','<>',14)->where('id','<>',31)->where('tesoreria','like','S')->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboConcepto = $cboConcepto + array($value->id => $value->nombre);
        }
        $formData            = array('cajatesoreria.store');
        $caja2                = Caja::find($request->input('caja_id'));
        $cboCaja = array();
        $rs = Caja::where(DB::raw('1'),'=','1')->where('id','<>',6)->where('id','<>',$request->input('caja_id'))->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboCaja = $cboCaja + array($value->id => $value->nombre);
        }
        $cboArea = array();
        $rs = Area::orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboArea = $cboArea + array($value->id => $value->nombre);
        }
        
        $cboTipo = array('RH' => 'RH', 'FT' => 'FT', 'BV' => 'BV', 'TK' => 'TK', 'VR' => 'VR', 'RC' => 'RC', 'NA' => 'NA', 'ND' => 'ND');
        $cboFormaPago = array('Contado' => 'Contado', 'Deposito' => 'Deposito');
        $numero              = Movimiento::NumeroSigueTesoreria(2,3,6);//movimiento caja y documento ingreso
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');

        $boton               = 'Registrar '.$caja2->nombre; 
        return view($this->folderview.'.mant')->with(compact('caja', 'formData', 'entidad', 'boton', 'listar', 'cboTipoDoc', 'caja2', 'numero', 'cboConcepto', 'cboCaja', 'user', 'cboTipo', 'cboFormaPago', 'cboArea'));
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
        $error = DB::transaction(function() use($request,$user){
            $movimiento        = new Movimiento();
            $movimiento->fecha = $request->input('fecha');
            $movimiento->numero= $request->input('numero');
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$request->input('person_id');    
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
            $movimiento->voucher=$request->input('rh');
            $movimiento->formapago=$request->input('tipo');
            $movimiento->nombrepaciente=$request->input('entregado');
            $movimiento->dni=$request->input('dni');
            $movimiento->numeroficha=$request->input('formapago');
            $movimiento->area_id=$request->input('area_id');
            
            if($request->input('concepto') == 27){
                $movimiento->fechaMovilidad=$request->input('movilidadFecha');
            }

            $movimiento->save();
            $idref=$movimiento->id;
            
            $lista="";    
            $arr=explode(",",$request->input('lista'));
            if($request->input('concepto')==9 || $request->input('concepto')==47 || $request->input('concepto')==72){//PAGO A PROVEEDOR Y DETRACCION
                if($request->input('lista')!=""){
                    for($c=0;$c<count($arr);$c++){
                        $Detalle = Movimiento::find($arr[$c]);
                        if(($Detalle->totalpagado+$request->input('txtPago'.$arr[$c]))==$Detalle->total){
                            $Detalle->estadopago='P';//pagado;
                        }
                        $Detalle->movimientodescarga_id=$idref;
                        $Detalle->totalpagado=$Detalle->totalpagado + $request->input('txtPago'.$arr[$c]);
                        $Detalle->save();
                        if($request->input('concepto')==72){
                            DB::update("UPDATE detallemovcaja SET fechapagodoctor = ?, recibopagodoctor = ?, idhonorario = ? WHERE id IN (SELECT iddetallemovcaja FROM reportepagomedico WHERE idmovimiento = ?)",[$request->input('fecha'),$request->input('tipo')." ".$request->input('rh'),$arr[$c],$arr[$c]]);
                        }
                        $lista.=$request->input('txtPago'.$arr[$c])."@";
                    }
                }
            }

            $movimiento->mensajesunat=substr($lista,0,strlen($lista)-1);
            $movimiento->save();

            if($request->input('concepto')==90 || $request->input('concepto')==146){//Transferencia de Tesoreria
                $caja = Caja::find($request->input('caja_id'));
                $movimiento        = new Movimiento();
                $movimiento->fecha = date("Y-m-d");
                $numero              = Movimiento::NumeroSigue(2,2);
                $movimiento->numero= $numero;
                $movimiento->responsable_id=$user->person_id;
                $movimiento->persona_id=$request->input('person_id');
                $movimiento->subtotal=0;
                $movimiento->igv=0;
                $movimiento->total=str_replace(",","",$request->input('total'));
                $movimiento->tipomovimiento_id=2;
                $movimiento->tipodocumento_id=2;//Ingreso
                if($request->input('concepto')==90){
                    $movimiento->conceptopago_id=100;
                }elseif($request->input('concepto')==146){
                    $movimiento->conceptopago_id=147;
                }
                $movimiento->comentario="Envio de caja ".$caja->nombre." por ".$request->input('comentario');
                $movimiento->caja_id=$request->input('caja');
                $movimiento->situacion='P';//PENDIENTE
                $movimiento->movimiento_id=$idref;
                $movimiento->listapago=$request->input('lista');//Lista de pagos para transferencia y pago tambien
                $movimiento->save();  

            }
            
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
                    if($Caja->conceptopago_id==9){//PAGO A PROVEEDOR
                        $Detalle = Movimiento::find($arr[$c]);
                        $Detalle->estadopago='PP';//pendiente;
                        $Detalle->movimientodescarga_id=null;
                        $Detalle->save();
                    }
                }                
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
        $entidad  = 'Cajatesoreria';
        $formData = array('route' => array('cajatesoreria.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
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
        
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->leftjoin('area','area.id','=','movimiento.area_id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            ->where(function($sql) use($movimiento_mayor) {
                                $sql->where('movimiento.id', '>=', $movimiento_mayor)
                                    ->whereNull('movimiento.cajaapertura_id')
                                    ->orWhere('movimiento.cajaapertura_id','=',$movimiento_mayor);
                                })
                            ->where('movimiento.situacion', '<>', 'A');
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','responsable.nombres as responsable2','area.nombre as area2')->orderBy('conceptopago.tipo', 'asc')->orderBy('movimiento.numeroficha','asc')->orderBy('conceptopago.id', 'asc')->orderBy('movimiento.tipotarjeta', 'asc')->orderBy('movimiento.numero', 'asc');
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
        
        if (count($lista) > 0) {            
            $pdf = new TCPDF();
            //$pdf::SetImaº
            $pdf::SetTitle('Detalle Cierre de '.$caja->nombre);
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',12);
            $apert = Movimiento::find($movimiento_mayor);
            $pdf::Cell(0,10,utf8_decode("Detalle de Cierre TESORERIA del ".date("d/m/Y",strtotime($apert->fecha))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8.5);
            $pdf::Cell(15,7,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(70,7,utf8_decode("PERSONA"),1,0,'C');
            $pdf::Cell(28,7,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(40,7,utf8_decode("CONCEPTO"),1,0,'C');
            $pdf::Cell(60,7,utf8_decode("GLOSA"),1,0,'C');
            $pdf::Cell(13,7,utf8_decode("ORDEN"),1,0,'C');
            $pdf::Cell(16,7,utf8_decode("EGRESO"),1,0,'C');
            $pdf::Cell(16,7,utf8_decode("INGRESO"),1,0,'C');
            $pdf::Cell(23,7,utf8_decode("AREA"),1,0,'C');
            $pdf::Ln();

            $ingreso=0;$egreso=0;$transferenciai=0;$transferenciae=0;$garantia=0;$efectivo=0;$visa=0;$master=0;$pago=0;$tarjeta=0;$cobranza=0;$egreso1=0;$transferenciai=0;$cobranza=0;
            $bandpago=true;$bandegreso=true;$bandtransferenciae=true;$bandtarjeta=true;$bandtransferenciai=true;$bandcobranza=true;$egresod=0;$egresoc=0;$bandegresoc=true;$bandegresod=true;
            foreach ($lista as $key => $value){
                if($ingreso==0){
                    $responsable=$value->responsable2;
                }
                if(in_array($value->conceptopago_id, $listConcepto) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA EGRESOS
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
                    $transferenciae = $transferenciae + $value->total;
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
                        }
                        $pdf::Ln();
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
                    $transferenciai = $transferenciai + $value->total;
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    $list=explode(",",$value->listapago);
                    for($c=0;$c<count($list);$c++){
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
                        }elseif($value->conceptopago_id==17){// TRANSFERENCIA SOCIO
                            $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                        }elseif($value->conceptopago_id==15){//TARJETA
                            $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                        }
                        
                    }
                    $pdf::Ln();
                }elseif($value->conceptopago_id!=1 && $value->conceptopago_id!=2 && $value->conceptopago_id!=23 && $value->conceptopago_id!=10 && $value->conceptopago_id!=150){
                    $pdf::SetTextColor(0,0,0);
                    if($ingreso==0 && $value->conceptopago->tipo=="I"){
                        if($egresoc>0 && $bandegresoc){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(226,7,'TOTAL',1,0,'R');
                            $pdf::Cell(16,7,number_format($egresoc,2,'.',''),1,0,'R');
                            $bandegresoc=false;
                            $pdf::Ln(); 
                        }
                        if($egresod>0 && $bandegresod){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(226,7,'TOTAL',1,0,'R');
                            $pdf::Cell(16,7,number_format($egresod,2,'.',''),1,0,'R');
                            $bandegresod=false;
                            $pdf::Ln(); 
                        }
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',7);
                        $pdf::Cell(242,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                        $apert = Movimiento::find($movimiento_mayor);
                        $pdf::Cell(16,7,number_format($apert->total,2,'.',''),1,0,'R');
                        $pdf::Ln();
                        $pago = $pago + $apert->total;
                        $ingreso = $ingreso + $apert->total;
                    }elseif($egresoc==0 && $value->conceptopago->tipo=="E" && $value->numeroficha=='Contado'){
                        if($egresod>0 && $bandegresod){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(226,7,'TOTAL',1,0,'R');
                            $pdf::Cell(16,7,number_format($egresod,2,'.',''),1,0,'R');
                            $bandegresod=false;
                            $pdf::Ln(); 
                        }
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("EGRESOS - CONTADO"),1,0,'L');
                        $pdf::Ln();
                    }elseif($egresod==0 && $value->conceptopago->tipo=="E" && $value->numeroficha=='Deposito'){
                        if($egresoc>0 && $bandegresoc){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(226,7,'TOTAL',1,0,'R');
                            $pdf::Cell(16,7,number_format($egresoc,2,'.',''),1,0,'R');
                            $bandegresoc=false;
                            $pdf::Ln(); 
                        }
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("EGRESOS - DEPOSITO"),1,0,'L');
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
                    if ($value->persona_id !== NULL  && !is_null($value->persona)) {
                        //echo 'entro'.$value->id;break;
                        if ($value->persona->bussinesname != null || $value->persona->bussinesname!="") {
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
                        $pdf::Multicell(70,3,($nombrepersona),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(70,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(70,7,($nombrepersona),1,0,'L');    
                    }
                    if($value->conceptopago_id!=13){
                        if($value->conceptopago_id==31){
                            $pdf::Cell(8,7,'T',1,0,'C');
                        }else{
                            $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C');
                        }
                        if($value->formapago!='VR'){
                            $pdf::Cell(20,7,utf8_decode(trim($value->voucher)==''?$value->numero:$value->voucher),1,0,'C');
                        }else{
                            $pdf::Cell(20,7,trim($value->numero),1,0,'C');
                        }
                    }else{
                        $pdf::Cell(8,7,'NA',1,0,'C');
                        $mov = Movimiento::find($value->movimiento_id);
                        $pdf::Cell(20,7,($mov->serie.'-'.$mov->numero),1,0,'C');
                    }
                    $descripcion=$value->conceptopago->nombre;
                    if(strlen($descripcion)>25){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(40,3,($descripcion),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(40,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(40,7,($descripcion),1,0,'L');
                    }
                    $descripcion=$value->comentario;
                    if(strlen($descripcion)>35){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(60,3,($descripcion),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(60,7,($descripcion),1,0,'L');
                    }
                    $pdf::Cell(13,7,$value->numero,1,0,'R');
                    if($value->situacion<>'R' && $value->situacion2<>'R'){
                        if($value->conceptopago->tipo=="I"){
                            $pdf::Cell(16,7,utf8_decode(""),1,0,'R');
                            $pdf::Cell(16,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pago=$pago + $value->total;
                        }else{
                            $egreso1=$egreso1 + $value->total;
                            $pdf::Cell(16,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pdf::Cell(16,7,utf8_decode(""),1,0,'C');
                            if($value->numeroficha=='Deposito'){
                                $egresod=$egresod+$value->total;
                            }else{
                                $egresoc=$egresoc+$value->total;
                            }
                        }
                    }else{
                        $pdf::Cell(16,7,utf8_decode(" - "),1,0,'C');
                        $pdf::Cell(16,7,utf8_decode(" - "),1,0,'C');
                    }
                    $descripcion=$value->area_id>0?$value->area2:'CAJA';
                    if(strlen($descripcion)>12){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(23,3,($descripcion),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(23,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(23,7,($descripcion),1,0,'L');
                    }
                    $pdf::Ln();
                }
                
                if($value->conceptopago_id<>2 && $value->situacion<>'A'){
                    if($value->conceptopago->tipo=="I"){
                        if($value->conceptopago_id<>10 && $value->conceptopago_id <> 150){//GARANTIA
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
                        if($value->conceptopago_id<>10 && $value->conceptopago_id!=150){//GARANTIA
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
                $res=$value->responsable->nombres;
            }
            if($cobranza>0 && $bandcobranza){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(16,7,number_format($cobranza,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($transferenciai>0 && $bandtransferenciai){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(16,7,number_format($transferenciai,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($pago>0 && $bandpago){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(242,7,'TOTAL',1,0,'R');
                $pdf::Cell(16,7,number_format($pago,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($tarjeta>0 && $bandtarjeta){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(16,7,number_format($tarjeta,2,'.',''),1,0,'R');
                $bandtarjeta=false;
                $pdf::Ln(); 
            }
            $pdf::Ln();
            $pdf::Cell(120,7,('RESPONSABLE: '.$responsable),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
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

        $caja_id          = Libreria::getParam($request->input('caja_id'),'1');
        
        
        $rst        = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',1)->where('movimiento.fecha', '>=', $f_inicial)->where('movimiento.fecha', '<=', $f_final)->orderBy('id','ASC')->get();
        if(count($rst)>0){
            foreach ($rst as $key => $rvalue) {
                array_push($aperturas,$rvalue->id);
                $rvalue       = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',2)->where('movimiento.fecha', '>=', $f_inicial)->where('movimiento.fecha', '<=', $f_final)
                ->where('movimiento.id', '>=', $rvalue->id)
                ->orderBy('id','ASC')->first();
                if(!is_null($rvalue)){
                    array_push($cierres,$rvalue->id);
                }else{
                    array_push($cierres,0);
                }

            }
            
        }else{
            $movimiento_mayor = 0;
        }
        
        $vmax = sizeof($aperturas);
        $pdf = new TCPDF();
        $pdf::SetTitle('Detalle Cierre General');
        //dd($aperturas,$cierres);
        for ($valor=0; $valor < $vmax -1; $valor++) {
            //dd($aperturas,$cierres);
            //echo $aperturas[$valor].' - '.$cierres[$valor].' ';
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                                ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                                ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                                ->leftjoin('area','area.id','=','movimiento.area_id')
                                ->where('movimiento.caja_id', '=', $caja_id)
                                ->where('movimiento.situacion', '<>', 'A')
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
                                ->orWhere('movimiento.cajaapertura_id','=',$aperturas[$valor])
                                ->whereNull('movimiento.cajaapertura_id');
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','responsable.nombres as responsable2','area.nombre as area2')->orderBy('conceptopago.tipo', 'asc')->orderBy('movimiento.numeroficha','asc')->orderBy('conceptopago.id', 'asc')->orderBy('movimiento.tipotarjeta', 'asc')->orderBy('movimiento.numero', 'asc');
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
        //dd($lista);
        if (count($lista) > 0) {            
            $pdf = new TCPDF();
            //$pdf::SetImaº
            $pdf::SetTitle('Detalle Cierre de '.$caja->nombre);
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',12);
            $apert = Movimiento::find($aperturas[$valor]);
            //dd($apert);
            $pdf::Cell(0,10,utf8_decode("Detalle de Cierre ".$caja->nombre." del ".date("d/m/Y",strtotime($apert->fecha))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8.5);
            $pdf::Cell(15,7,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(70,7,utf8_decode("PERSONA"),1,0,'C');
            $pdf::Cell(28,7,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(40,7,utf8_decode("CONCEPTO"),1,0,'C');
            $pdf::Cell(60,7,utf8_decode("GLOSA"),1,0,'C');
            $pdf::Cell(13,7,utf8_decode("ORDEN"),1,0,'C');
            $pdf::Cell(16,7,utf8_decode("EGRESO"),1,0,'C');
            $pdf::Cell(16,7,utf8_decode("INGRESO"),1,0,'C');
            $pdf::Cell(23,7,utf8_decode("AREA"),1,0,'C');
            $pdf::Ln();

            $ingreso=0;$egreso=0;$transferenciai=0;$transferenciae=0;$garantia=0;$efectivo=0;$visa=0;$master=0;$pago=0;$tarjeta=0;$cobranza=0;$egreso1=0;$transferenciai=0;$cobranza=0;
            $bandpago=true;$bandegreso=true;$bandtransferenciae=true;$bandtarjeta=true;$bandtransferenciai=true;$bandcobranza=true;$egresod=0;$egresoc=0;$bandegresoc=true;$bandegresod=true;

            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(242,7,utf8_decode("SALDO INICIAL"),1,0,'L');
            $apert = Movimiento::find($aperturas[$valor]);
            $pdf::Cell(16,7,number_format($apert->total,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pago = $pago + $apert->total;
            $ingreso = $ingreso + $apert->total;
            $ingresoCero = $apert->total;
            $responsable=$apert->responsable2;
            $mostrosaldoinicial = false;
            foreach ($lista as $key => $value){
                if($ingreso==0){
                    $responsable=$value->responsable2;
                }
                if(in_array($value->conceptopago_id, $listConcepto) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA EGRESOS
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
                    $transferenciae = $transferenciae + $value->total;
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
                        }
                        $pdf::Ln();
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
                    $transferenciai = $transferenciai + $value->total;
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    $list=explode(",",$value->listapago);
                    for($c=0;$c<count($list);$c++){
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
                        }elseif($value->conceptopago_id==17){// TRANSFERENCIA SOCIO
                            $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                        }elseif($value->conceptopago_id==15){//TARJETA
                            $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                        }
                        
                    }
                    $pdf::Ln();
                }elseif($value->conceptopago_id!=1 && $value->conceptopago_id!=2 && $value->conceptopago_id!=23 && $value->conceptopago_id!=10 && $value->conceptopago_id!=150){
                    $pdf::SetTextColor(0,0,0);
                    if($ingreso==$ingresoCero && $value->conceptopago->tipo=="I"){
                        if($egresoc>0 && $bandegresoc){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(226,7,'TOTAL',1,0,'R');
                            $pdf::Cell(16,7,number_format($egresoc,2,'.',''),1,0,'R');
                            $bandegresoc=false;
                            $pdf::Ln(); 
                        }
                        if($egresod>0 && $bandegresod){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(226,7,'TOTAL',1,0,'R');
                            $pdf::Cell(16,7,number_format($egresod,2,'.',''),1,0,'R');
                            $bandegresod=false;
                            $pdf::Ln(); 
                        }
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                        $pdf::Ln();
                        //$pdf::SetFont('helvetica','B',7);
                        //$pdf::Cell(242,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                        //$apert = Movimiento::find($aperturas[$valor]);
                        //$pdf::Cell(16,7,number_format($apert->total,2,'.',''),1,0,'R');
                        //$pdf::Ln();
                        //$pago = $pago + $apert->total;
                        //$ingreso = $ingreso + $apert->total;
                        $mostrosaldoinicial = true;
                    }elseif($egresoc==0 && $value->conceptopago->tipo=="E" && $value->numeroficha=='Contado'){
                        if($egresod>0 && $bandegresod){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(226,7,'TOTAL',1,0,'R');
                            $pdf::Cell(16,7,number_format($egresod,2,'.',''),1,0,'R');
                            $bandegresod=false;
                            $pdf::Ln(); 
                        }
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("EGRESOS - CONTADO"),1,0,'L');
                        $pdf::Ln();
                    }elseif($egresod==0 && $value->conceptopago->tipo=="E" && $value->numeroficha=='Deposito'){
                        if($egresoc>0 && $bandegresoc){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(226,7,'TOTAL',1,0,'R');
                            $pdf::Cell(16,7,number_format($egresoc,2,'.',''),1,0,'R');
                            $bandegresoc=false;
                            $pdf::Ln(); 
                        }
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("EGRESOS - DEPOSITO"),1,0,'L');
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
                    if ($value->persona_id !== NULL  && !is_null($value->persona)) {
                        //echo 'entro'.$value->id;break;
                        if ($value->persona->bussinesname != null || $value->persona->bussinesname!="") {
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
                        $pdf::Multicell(70,3,($nombrepersona),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(70,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(70,7,($nombrepersona),1,0,'L');    
                    }
                    if($value->conceptopago_id!=13){
                        if($value->conceptopago_id==31){
                            $pdf::Cell(8,7,'T',1,0,'C');
                        }else{
                            $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C');
                        }
                        if($value->formapago!='VR'){
                            $pdf::Cell(20,7,utf8_decode(trim($value->voucher)==''?$value->numero:$value->voucher),1,0,'C');
                        }else{
                            $pdf::Cell(20,7,trim($value->numero),1,0,'C');
                        }
                    }else{
                        $pdf::Cell(8,7,'NA',1,0,'C');
                        $mov = Movimiento::find($value->movimiento_id);
                        $pdf::Cell(20,7,($mov->serie.'-'.$mov->numero),1,0,'C');
                    }
                    $descripcion=$value->conceptopago->nombre;
                    if(strlen($descripcion)>25){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(40,3,($descripcion),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(40,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(40,7,($descripcion),1,0,'L');
                    }
                    $descripcion=$value->comentario;
                    if(strlen($descripcion)>35){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(60,3,($descripcion),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(60,7,($descripcion),1,0,'L');
                    }
                    $pdf::Cell(13,7,$value->numero,1,0,'R');
                    if($value->situacion<>'R' && $value->situacion2<>'R'){
                        if($value->conceptopago->tipo=="I"){
                            $pdf::Cell(16,7,utf8_decode(""),1,0,'R');
                            $pdf::Cell(16,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pago=$pago + $value->total;
                        }else{
                            $egreso1=$egreso1 + $value->total;
                            $pdf::Cell(16,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pdf::Cell(16,7,utf8_decode(""),1,0,'C');
                            if($value->numeroficha=='Deposito'){
                                $egresod=$egresod+$value->total;
                            }else{
                                $egresoc=$egresoc+$value->total;
                            }
                        }
                    }else{
                        $pdf::Cell(16,7,utf8_decode(" - "),1,0,'C');
                        $pdf::Cell(16,7,utf8_decode(" - "),1,0,'C');
                    }
                    $descripcion=$value->area_id>0?$value->area2:'CAJA';
                    if(strlen($descripcion)>12){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(23,3,($descripcion),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(23,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(23,7,($descripcion),1,0,'L');
                    }
                    $pdf::Ln();
                }
                
                if($value->conceptopago_id<>2 && $value->situacion<>'A'){
                    if($value->conceptopago->tipo=="I"){
                        if($value->conceptopago_id<>10 && $value->conceptopago_id!=150){//GARANTIA
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
                        if($value->conceptopago_id<>10 && $value->conceptopago_id!=150){//GARANTIA
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
                $res=$value->responsable->nombres;
            }
            if($cobranza>0 && $bandcobranza){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(16,7,number_format($cobranza,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($transferenciai>0 && $bandtransferenciai){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(16,7,number_format($transferenciai,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($pago>0 && $bandpago){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(242,7,'TOTAL',1,0,'R');
                $pdf::Cell(16,7,number_format($pago,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($tarjeta>0 && $bandtarjeta){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(16,7,number_format($tarjeta,2,'.',''),1,0,'R');
                $bandtarjeta=false;
                $pdf::Ln(); 
            }
            $pdf::Ln();
            $pdf::Cell(120,7,('RESPONSABLE: '.$responsable),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
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
            /*$pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("GARANTIA :"),1,0,'L');
            $pdf::Cell(20,7,number_format($garantia,2,'.',''),1,0,'R');*/
            $pdf::Ln();
            
        }
    }
    $pdf::Output('ListaCaja.pdf');
    }

    public function apertura(Request $request)
    {
        $entidad             = 'Cajatesoreria';
        $formData            = array('cajatesoreria.aperturar');
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
            $movimiento->fecha = $request->input('fecha');
            $movimiento->numero= $request->input('numero');
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$user->person_id;
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            if($request->input('caja_id')==6){
                $ultimo = Movimiento::where('conceptopago_id','=',2)
                          ->where('caja_id','=',6)  
                          ->orderBy('id','desc')->limit(1)->first();
                if(count($ultimo)>0){
                    $movimiento->total=$ultimo->total;
                }else{
                    $movimiento->total=0;    
                }
            }else{
                $movimiento->total=0;     
            }
            $movimiento->tipomovimiento_id=2;
            $movimiento->tipodocumento_id=2;
            $movimiento->conceptopago_id=1;
            $movimiento->comentario=$request->input('comentario');
            $movimiento->caja_id=$request->input('caja_id');
            $movimiento->situacion='N';
            $movimiento->save();

            $resultado1        = Movimiento::join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->where('movimiento.tipomovimiento_id','=',2)
                            ->where('movimiento.conceptopago_id','=','2')
                            ->where('movimiento.caja_id','<>',6)
                            ->where('movimiento.caja_id','<>',7);
            $resultado1 = $resultado1->where('movimiento.fecha','>=',date("Y-m-d",strtotime("-1 day",strtotime($request->input('fecha')))));
            $resultado1 = $resultado1->where('movimiento.fecha','<=',date("Y-m-d",strtotime("-1 day",strtotime($request->input('fecha')))));

            $resultado1        = $resultado1->select('movimiento.*','responsable.nombres as responsable2')->orderBy('movimiento.fecha', 'ASC')->get();
            $c=2;$d=3;$total=0;$band=true;
            foreach ($resultado1 as $key1 => $value1){
                $apertura= Movimiento::where('conceptopago_id','=',1)
                        ->where('id','<',$value1->id)
                        ->where('caja_id','=',$value1->caja_id)
                        ->orderBy('id','desc')
                        ->first();

                $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->where('movimiento.caja_id', '=', $value1->caja_id)
                            ->where(function ($query) use($value1,$apertura) {
                                $query->where(function($q) use($value1,$apertura){
                                        $q->where('movimiento.id', '>', $apertura->id)
                                        ->where('movimiento.id', '<', $value1->id)
                                        ->whereNull('movimiento.cajaapertura_id');
                                })
                                      ->orwhere(function ($query1) use($value1,$apertura){
                                        $query1->where('movimiento.cajaapertura_id','=',$apertura->id);
                                        });//normal
                            })
                            ->where('movimiento.situacion', '<>', 'A')->where('movimiento.situacion', '<>', 'R')
                            ->where(function($query){
                                $query
                                    ->whereNotIn('movimiento.conceptopago_id',[31])
                                    ->orWhere('m2.situacion','<>','R');
                            });
                $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','responsable.nombres as responsable2')->orderBy('conceptopago.tipo', 'asc')->orderBy('conceptopago.orden', 'asc')->orderBy('conceptopago.id', 'asc')->orderBy('movimiento.tipotarjeta', 'asc')->orderBy('movimiento.numero', 'asc');
                $lista = $resultado->get();
                $ingreso=0;$egreso=0;$visa=0;$master=0;$garantia=0;$efectivo=0;
                foreach ($lista as $key => $value){
                    if($value->conceptopago_id<>2 && $value->situacion<>'A'){
                        if($value->conceptopago->tipo=="I"){
                            if($value->conceptopago_id<>10 && $value->conceptopago_id!=150){//GARANTIA
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
                            if($value->conceptopago_id<>10 && $value->conceptopago_id!=150){//GARANTIA
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
                }   
                $ingreso = $ingreso - $visa - $master;
                $movimiento        = new Movimiento();
                $movimiento->fecha = $apertura->fecha;
                $movimiento->numero= Movimiento::NumeroSigue(2,2);
                $movimiento->responsable_id=$user->person_id;
                $movimiento->persona_id=$value1->responsable_id;
                $movimiento->subtotal=0;
                $movimiento->igv=0;
                $movimiento->total=number_format($ingreso - $egreso,2,'.','');     
                $movimiento->tipomovimiento_id=2;
                $movimiento->tipodocumento_id=2;
                $movimiento->conceptopago_id=60;
                isset($value)?$movimiento->comentario='Cierre de Caja '.$value->caja->nombre:$movimiento->comentario='Cierre de Caja OBSERVAR';
                //$movimiento->comentario='Cierre de Caja '.$value->caja->nombre;
                $movimiento->caja_id=6;
                $movimiento->movimiento_id=$value1->id;
                $movimiento->situacion='N';
                $movimiento->save();
                
            }
 
        });
        return is_null($error) ? "OK" : $error;
    }
    
    public function recalcular(Request $request){
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
        $resultado1        = Movimiento::join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                        ->where('movimiento.tipomovimiento_id','=',2)
                        ->where('movimiento.conceptopago_id','=','60')
                        ->where('movimiento.caja_id','=',6)
                        ->where('movimiento.id','>',$movimiento_mayor);

        $resultado1        = $resultado1->select('movimiento.*','responsable.nombres as responsable2')->orderBy('movimiento.fecha', 'ASC')->get();
        $c=2;$d=3;$total=0;$band=true;
        foreach ($resultado1 as $key1 => $value1){
            $caja=Movimiento::find($value1->movimiento_id);
            $apertura= Movimiento::where('conceptopago_id','=',1)
                    ->where('id','<',$value1->movimiento_id)
                    ->where('caja_id','=',$caja->caja_id)
                    ->orderBy('id','desc')
                    ->first();

            /*$resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                        ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                        ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                        ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                        ->where('movimiento.caja_id', '=', $caja->caja_id)
                        ->where(function ($query) use($value1,$apertura) {
                            $query->where(function($q) use($value1,$apertura){
                                    $q->where('movimiento.id', '>', $apertura->id)
                                    ->where('movimiento.id', '<', $value1->movimiento_id)
                                    ->whereNull('movimiento.cajaapertura_id');
                            })
                                  ->orwhere(function ($query1) use($value1,$apertura){
                                    $query1->where('movimiento.cajaapertura_id','=',$apertura->id);
                                    });//normal
                        })
                        ->where('movimiento.situacion', '<>', 'A')->where('movimiento.situacion', '<>', 'R');*/
          
            $resultado  = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                        ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                        ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                        ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                        ->where('movimiento.caja_id', '=', $caja->caja_id)
                        ->where(function ($query) use($value1,$apertura) {
                            $query->where(function($q) use($value1,$apertura){
                                $q->where('movimiento.id', '>', $apertura->id)
                                    ->where('movimiento.id', '<', $value1->movimiento_id)
                                    ->whereNull('movimiento.cajaapertura_id');
                            })
                            ->orwhere(function ($query1) use($value1,$apertura){
                                $query1->where('movimiento.cajaapertura_id','=',$apertura->id);
                            });//normal
                        })
                        //->where('movimiento.voucher', '04-23390')
                        ->where(function($query){
                            $query
                                ->whereNotIn('movimiento.conceptopago_id',[31])
                                ->orWhere('m2.situacion','<>','R');
                        })
                        ->where('movimiento.situacion', '<>', 'A')->where('movimiento.situacion', '<>', 'R');
            $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','responsable.nombres as responsable2')->orderBy('conceptopago.tipo', 'asc')->orderBy('conceptopago.orden', 'asc')->orderBy('conceptopago.id', 'asc')->orderBy('movimiento.tipotarjeta', 'asc')->orderBy('movimiento.numero', 'asc');
            
            $lista = $resultado->get();
            $ingreso=0;$egreso=0;$visa=0;$master=0;$garantia=0;$efectivo=0;
            
            // dd($lista);

            foreach ($lista as $key => $value){
                if($value->conceptopago_id<>2 && $value->situacion<>'A'){
                    if($value->conceptopago->tipo=="I"){
                        if($value->conceptopago_id<>10 && $value->conceptopago_id!=150){//GARANTIA
                            if($value->conceptopago_id<>15 && $value->conceptopago_id<>17 && $value->conceptopago_id<>19 && $value->conceptopago_id<>21){
                                if ($value->tipodocumento_id != 15) {
                                    //echo $value->total."@";205851
                                    $ingreso = $ingreso + round($value->total,2);
                                }
                                    
                            }elseif(($value->conceptopago_id==15 || $value->conceptopago_id==17 || $value->conceptopago_id==19 || $value->conceptopago_id==21) && $value->situacion=='C'){
                                $ingreso = $ingreso + round($value->total,2);    
                            }
                        }else{
                            $garantia = $garantia + $value->total;
                        }
                        if($value->conceptopago_id<>10 && $value->conceptopago_id!=150){//GARANTIA
                            if($value->tipotarjeta=='VISA'){
                                    $visa = $visa + round($value->total,2);
                            }elseif($value->tipotarjeta==''){
                                if ($value->tipodocumento_id != 15) {
                                    $efectivo = $efectivo + round($value->total,2);
                                }
                            }else{
                                $master = $master + round($value->total,2);
                            }
                        }
                    }else{
                        if($value->conceptopago_id<>14 && $value->conceptopago_id<>16 && $value->conceptopago_id<>18 && $value->conceptopago_id<>20){
                            if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                                $ingreso  = $ingreso - round($value->total,2);
                                $efectivo = $efectivo - round($value->total,2);
                            }else{
                                $egreso  = $egreso + round($value->total,2);
                            }
                        }elseif(($value->conceptopago_id==14 || $value->conceptopago_id==16 || $value->conceptopago_id==18 || $value->conceptopago_id==20) && $value->situacion2=='C'){
                            $egreso  = $egreso + round($value->total,2);
                        }
                    }
                }
            }   
            $ingreso = $ingreso - $visa - $master;            
            $value1->total=number_format($ingreso - $egreso,2,'.','');     
            $value1->save();
            
        }
    }

    public function generarConcepto(Request $request)
    {
        $tipodoc = $request->input("tipodocumento_id");
        if($tipodoc==2){
            $rst = Conceptopago::where('tipo','like','I')->where('id','<>',1)->where('id','<>',6)->where('id','<>',15)->where('id','<>',17)->where('id','<>',19)->where('id','<>',21)->where('id','<>',23)->where('id','<>',31)->where('id','<>',3)->where('id','<>',32)->where('id','<>',10)->where('id','<>',150)->where('id','<>',33)->where('tesoreria','like','S')->orderBy('nombre','ASC')->get();
        }else{
            $rst = Conceptopago::where('tipo','like','E')->where('id','<>',2)->where('id','<>',13)->where('id','<>',24)->where('id','<>',25)->where('id','<>',26)->where('id','<>',20)->where('id','<>',8)->where('id','<>',18)->where('id','<>',16)->where('id','<>',14)->where('id','<>',31)->where('tesoreria','like','S')->orderBy('nombre','ASC')->get();
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
        $resultado        = Person::where(DB::raw('CONCAT(case when ruc is null then dni else ruc end," ",apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($searching).'%')->orWhere(DB::raw('concat(ruc," ",bussinesname)'), 'LIKE', '%'.strtoupper($searching).'%')->whereNull('deleted_at')->orderBy('apellidopaterno', 'ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = '';
            if ($value->bussinesname != null) {
                $name = $value->ruc.' '.$value->bussinesname;
            }else{
                $name = ($value->ruc==''?$value->dni:$value->ruc).' '.$value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres;
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
        $entidad             = 'Cajatesoreria';
        $formData            = array('cajatesoreria.cerrar');
        $listar              = $request->input('listar');
        $caja                = Caja::find($request->input('caja_id'));
        $saldo                = Caja::find($request->input('saldo'));
        $numero              = Movimiento::NumeroSigue(2,3);//movimiento caja y documento egreso
        $rst              = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja->id)->where('conceptopago_id','=',1)->whereNull('deleted_at')->orderBy('id','DESC')->limit(1)->first();
        if(count($rst)>0){
            $movimiento_mayor = $rst->id;    
        }else{
            $movimiento_mayor = 0;
        }
        
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','movimiento.id','=','m2.movimiento_id')
                            ->where('movimiento.caja_id', '=', $caja->id)
                            // ->where('movimiento.id','<',563865)
                            ->where(function($sql) use($movimiento_mayor) {
                                $sql->where('movimiento.id', '>=', $movimiento_mayor)
                                    ->whereNull('movimiento.cajaapertura_id')
                                    ->orWhere('movimiento.cajaapertura_id','=',$movimiento_mayor);
                                })
                            ;
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2',DB::raw('CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) as paciente'),DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.id', 'desc');
        $lista            = $resultado->get();
        
        $ingreso=0;$egreso=0;$garantia=0;$efectivo=0;$master=0;$visa=0;
            foreach($lista as $k=>$v){
                if($v->conceptopago_id<>2 && $v->situacion<>'A'){
                    if($v->conceptopago->tipo=="I"){
                        if($v->conceptopago_id<>10 && $v->conceptopago_id<>150){//Garantias
                            if($v->conceptopago_id<>15 && $v->conceptopago_id<>17 && $v->conceptopago_id<>19 && $v->conceptopago_id<>21){
                                $ingreso = $ingreso + $v->total;    
                            }elseif(($v->conceptopago_id==15 || $v->conceptopago_id==17 || $v->conceptopago_id==19 || $v->conceptopago_id==21) && $v->situacion=='C'){
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
                    }else{
                        if($v->conceptopago_id<>14 && $v->conceptopago_id<>16 && $v->conceptopago_id<>18 && $v->conceptopago_id<>20){
                            $egreso  = $egreso + $v->total;
                        }elseif(($v->conceptopago_id==14 || $v->conceptopago_id==16 || $v->conceptopago_id==18 || $v->conceptopago_id==20) && $v->situacion2=='C'){
                            $egreso  = $egreso + $v->total;
                        }
                    }
                }
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
            $arr=explode(",",$Caja->listapago);
            for($c=0;$c<count($arr);$c++){
                $Detalle = Detallemovcaja::find($arr[$c]);
                if($Caja->conceptopago_id==6){//CAJA
                    $Detalle->situacion='N';//normal;
                }elseif($Caja->conceptopago_id==17){//SOCIO
                    $Detalle->situacionsocio=null;//null
                }elseif($Caja->conceptopago_id==15 || $Caja->conceptopago_id==21){//TARJETA Y BOLETEO TOTAL
                    $Detalle->situaciontarjeta=null;//null
                }
                $Detalle->save();
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
    
    public function cuentasporpagar(Request $request)
    {
        $user = Auth::user();
        $proveedor = $request->input('busqueda');
        $resultado        = Movimiento::leftjoin('person as proveedor', 'proveedor.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('tipodocumento as td','td.id','=','movimiento.tipodocumento_id')
                            ->whereiN('movimiento.tipomovimiento_id', [3,11])
                            ->where('movimiento.estadopago','like','PP')
                            ->where(DB::raw('case when proveedor.bussinesname is null then CONCAT(case when proveedor.ruc is null then proveedor.dni else proveedor.ruc end," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end'),'like','%'.$proveedor.'%');
        
        $resultado        = $resultado->select('movimiento.*',DB::raw('case when proveedor.bussinesname is null then CONCAT(proveedor.dni," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end as proveedor2'),DB::raw('responsable.nombres as responsable'),'td.abreviatura as tipodocumento2')->orderBy('movimiento.fecha', 'desc');
        $lista            = $resultado->get();
        $registro="<table class='table table-hover' style='max-heigth:150px;'>
                    <thead>
                        <tr>
                            <th class='text-center'>Fecha</th>
                            <th class='text-center'>Nro</th>
                            <th class='text-center'>Total</th>
                        </tr>
                    </thead>
                    <tbody>";
        foreach ($lista as $key => $value) {
            if($value->tipomovimiento_id==3){
                $numero=$value->tipodocumento2.' '.$value->serie.'-'.$value->numero;
            }else{
                $numero=$value->formapago.' '.$value->voucher;
            }
            if($value->tipodocumento_id==11 && $value->tipomovimiento_id==3){
                $signo = -1;
            }else{
                $signo = 1;
            }
            $registro.="<tr onclick=\"agregarDoc($value->id,'".trim($numero)."','".date("d/m/Y",strtotime($value->fecha))."',".number_format($signo * ($value->total-$value->totalpagado),2,'.','').")\">";
            $registro.="<td align='center' >".date("d/m/Y",strtotime($value->fecha))."</td>";
            $registro.="<td align='center'>".trim($numero)."</td>";
            $registro.="<td align='right'>".number_format($value->total-$value->totalpagado,2,'.','')."</td>";
            $registro.="</tr>";
        }
        $registro.="</tbody></table>";
        echo $registro;
    }

    public function cuentasmedico(Request $request)
    {
        $user = Auth::user();
        $proveedor = $request->input('busqueda');
        $resultado        = Movimiento::leftjoin('person as proveedor', 'proveedor.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('tipodocumento as td','td.id','=','movimiento.tipodocumento_id')
                            ->whereiN('movimiento.tipomovimiento_id', [13])
                            ->where('movimiento.estadopago','like','PP')
                            ->where(DB::raw('case when proveedor.bussinesname is null then CONCAT(case when proveedor.ruc is null then proveedor.dni else proveedor.ruc end," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(case when proveedor.ruc is null then proveedor.dni else proveedor.ruc end," ",proveedor.bussinesname) end'),'like','%'.$proveedor.'%');
        
        $resultado        = $resultado->select('movimiento.*',DB::raw('case when proveedor.bussinesname is null then CONCAT(proveedor.dni," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end as proveedor2'),DB::raw('responsable.nombres as responsable'),'td.abreviatura as tipodocumento2')->orderBy('movimiento.fecha', 'desc');
        $lista            = $resultado->get();
        $registro="<table class='table table-hover' style='max-heigth:150px;'>
                    <thead>
                        <tr>
                            <th class='text-center'>Fecha</th>
                            <th class='text-center'>Nro</th>
                            <th class='text-center'>Total</th>
                        </tr>
                    </thead>
                    <tbody>";
        foreach ($lista as $key => $value) {
            if($value->tipomovimiento_id==3){
                $numero=$value->tipodocumento2.' '.$value->serie.'-'.$value->numero;
            }else{
                $numero=$value->formapago.' '.$value->voucher;
            }
            // dd($value->comentario);
            // /date('Y',strtotime($value->fecha)).
            $anio = substr(trim($numero),6);

            $registro.="<tr onclick=\"agregarDoc($value->id,'".trim($numero)."','".date("d/m/Y",strtotime($value->fecha))."',".number_format($value->total-$value->totalpagado,2,'.','').",'".substr(json_encode($value->comentario),1,strlen(json_encode($value->comentario))-6).$anio."');\">";
            $registro.="<td align='center' >".date("d/m/Y",strtotime($value->fecha))."</td>";
            $registro.="<td align='center'>".trim($numero)."</td>";
            $registro.="<td align='right'>".number_format($value->total-$value->totalpagado,2,'.','')."</td>";
            $registro.="</tr>";
        }
        $registro.="</tbody></table>";
        echo $registro;
    }

	public function pdfRecibo(Request $request){
        $lista = Movimiento::where('id','=',$request->input('id'))->first();
                    
        $pdf = new TCPDF();
        $pdf::SetTitle('Recibo de '.($lista->conceptopago->tipo=="I"?"Ingreso":"Egreso"));
        $pdf::AddPage();
        if($lista->conceptopago_id==10){//GARANTIAS
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
            $pdf::Cell(32,7,utf8_decode($lista->fecha),0,0,'L');
            $pdf::Cell(50,7,utf8_decode(""),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(18,7,utf8_decode("FECHA :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(40,7,utf8_decode($lista->fecha),0,0,'L');
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
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("DOCTOR :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,utf8_decode($lista->doctor->apellidopaterno." ".$lista->doctor->apellidomaterno." ".$lista->doctor->nombres),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("DOCTOR :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,utf8_decode($lista->doctor->apellidopaterno." ".$lista->doctor->apellidomaterno." ".$lista->doctor->nombres),0,0,'L');
            $pdf::Ln();
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
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,($lista->responsable->nombres),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,($lista->responsable->nombres),0,0,'L');
            
        }elseif($lista->conceptopago_id==8){//HONORARIOS MEDICOS
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
                    $pdf::Cell(80,7,utf8_decode($detalle->servicio->nombre),0,0,'L');
                }else{
                    $pdf::Cell(80,7,utf8_decode($detalle->descripcion),0,0,'L');
                }
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,7,utf8_decode("SERVICIO :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                if($detalle->servicio_id>0){
                    $pdf::Cell(30,7,utf8_decode($detalle->servicio->nombre),0,0,'L');
                }else{
                    $pdf::Cell(30,7,utf8_decode($detalle->descripcion),0,0,'L');
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
            
        }elseif($lista->conceptopago_id==9 || $lista->conceptopago->tipo=="E" || $lista->Conceptopago->tipo=='I'){//PAGO A PROVEEDOR
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 5, 70, 20);
            $pdf::Cell(0,6,utf8_decode("ORDEN DE PAGO NRO $lista->numero"),0,0,'C');
            $pdf::Ln();
            $pdf::Ln();
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("RAZON SOCIAL :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            if($lista->persona->bussinesname!="")
                $pdf::Cell(100,7,($lista->persona->bussinesname),0,0,'L');
            else
                $pdf::Cell(100,7,($lista->persona->apellidopaterno.' '.$lista->persona->apellidomaterno.' '.$lista->persona->nombres),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(15,7,utf8_decode("FECHA :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,date("d/m/Y",strtotime($lista->fecha)),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("CONCEPTO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(100,7,$lista->conceptopago->nombre,0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(15,7,utf8_decode("RUC :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,$lista->persona->ruc,0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Ln();
            if($lista->conceptopago_id!=9){
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,7,utf8_decode("DOCUMENTO :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(100,7,utf8_decode($lista->formapago.' '.$lista->voucher),0,0,'L');
            }else{
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,7,utf8_decode("DOCUMENTOS :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                if(trim($lista->listapago)!=""){
                    $arr=explode(",",$lista->listapago);
                    $list="";
                    $arr2=explode("@", $lista->mensajesunat);
                    for($c=0;$c<count($arr);$c++){
                        $mov=Movimiento::find($arr[$c]);
                        if($mov->tipomovimiento_id==3){
                            $numero=($mov->tipodocumento_id==5?'BV':'FT').' '.$mov->serie.'-'.$mov->numero;
                        }else{
                            $numero=$mov->formapago.' '.$mov->voucher;
                        }
                        $list.=$numero."(S/ ".number_format($arr2[$c],2,'.','')."), ";
                    }
                    if(strlen($list)>100){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::MultiCell(120,7,substr($list,0,strlen($list)-2),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(120,7,"",0,0,'L');
                    }else{
                        $pdf::Cell(120,7,substr($list,0,strlen($list)-2),0,0,'L');
                    }
                }else{
                    $pdf::Cell(100,7,utf8_decode($lista->formapago.' '.$lista->voucher),0,0,'L');
                }
            }
            $pdf::Ln();
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("COMENTARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            if(strlen($lista->comentario)>37){
                $x=$pdf::GetX();
                $y=$pdf::GetY();
                $pdf::MultiCell(110,7,$lista->comentario,0,'L');
                $pdf::SetXY($x,$y);
                $pdf::Cell(110,7,"",0,0,'L');
            }else{
                $pdf::Cell(110,7,($lista->comentario),0,0,'L');
            }
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(15,7,utf8_decode("TOTAL :"),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,number_format($lista->total,2,'.',''),0,0,'L');
            $pdf::Ln();
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(50,7,"",0,0,'L');
            $pdf::Cell(30,7,utf8_decode(($lista->conceptopago->tipo=="I"?"ENTREGADO POR :":"ENTREGADO A :")),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(110,7,($lista->nombrepaciente),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,"CONTABILIDAD",0,0,'C');
            $pdf::Cell(20,7,"",0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("DNI :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,utf8_decode($lista->dni),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,"",0,0,'L');
            $pdf::Cell(40,7,utf8_decode("RECIBI CONFORME"),'T',0,'C');            
            $pdf::SetFont('helvetica','',9);
            $pdf::Ln();
            $pdf::Ln();
            $pdf::Cell(0,7,"---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------",0,0,'C');
            $pdf::Ln();
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 100, 70, 20);
            $pdf::Cell(0,6,utf8_decode("ORDEN DE PAGO NRO $lista->numero"),0,0,'C');
            $pdf::Ln();
            $pdf::Ln();
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("RAZON SOCIAL :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            if($lista->persona->bussinesname!="")
                $pdf::Cell(100,7,($lista->persona->bussinesname),0,0,'L');
            else
                $pdf::Cell(100,7,($lista->persona->apellidopaterno.' '.$lista->persona->apellidomaterno.' '.$lista->persona->nombres),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(15,7,utf8_decode("FECHA :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,date("d/m/Y",strtotime($lista->fecha)),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("CONCEPTO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(100,7,$lista->conceptopago->nombre,0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(15,7,utf8_decode("RUC :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,$lista->persona->ruc,0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Ln();
            if($lista->conceptopago_id!=9){
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,7,utf8_decode("DOCUMENTO :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(100,7,utf8_decode($lista->formapago.' '.$lista->voucher),0,0,'L');
            }else{
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,7,utf8_decode("DOCUMENTOS :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                if(trim($lista->listapago)!=""){
                    $arr=explode(",",$lista->listapago);
                    $list="";
                    $arr2=explode("@", $lista->mensajesunat);
                    for($c=0;$c<count($arr);$c++){
                        $mov=Movimiento::find($arr[$c]);
                        if($mov->tipomovimiento_id==3){
                            $numero=($mov->tipodocumento_id==5?'BV':'FT').' '.$mov->serie.'-'.$mov->numero;
                        }else{
                            $numero=$mov->formapago.' '.$mov->voucher;
                        }
                        $list.=$numero."(S/ ".number_format($arr2[$c],2,'.','')."), ";
                    }
                    if(strlen($list)>100){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::MultiCell(120,7,substr($list,0,strlen($list)-2),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(120,7,"",0,0,'L');
                    }else{
                        $pdf::Cell(120,7,substr($list,0,strlen($list)-2),0,0,'L');
                    }
                }else{
                    $pdf::Cell(100,7,utf8_decode($lista->formapago.' '.$lista->voucher),0,0,'L');
                }
            }
            $pdf::Ln();
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("COMENTARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            if(strlen($lista->comentario)>37){
                $x=$pdf::GetX();
                $y=$pdf::GetY();
                $pdf::MultiCell(110,7,$lista->comentario,0,'L');
                $pdf::SetXY($x,$y);
                $pdf::Cell(110,7,"",0,0,'L');
            }else{
                $pdf::Cell(110,7,($lista->comentario),0,0,'L');
            }
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(15,7,utf8_decode("TOTAL :"),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,number_format($lista->total,2,'.',''),0,0,'L');
            $pdf::Ln();
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(50,7,"",0,0,'L');
            $pdf::Cell(30,7,utf8_decode(($lista->conceptopago->tipo=="I"?"ENTREGADO POR :":"ENTREGADO A :")),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(110,7,($lista->nombrepaciente),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,"TESORERIA",0,0,'C');
            $pdf::Cell(20,7,"",0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("DNI :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,utf8_decode($lista->dni),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,"",0,0,'L');
            $pdf::Cell(40,7,utf8_decode("RECIBI CONFORME"),'T',0,'C');            
            $pdf::SetFont('helvetica','',9);
            $pdf::Ln();
            $pdf::Ln();
            $pdf::Cell(0,7,"---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------",0,0,'C');
            $pdf::Ln();
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 195, 70, 20);
            $pdf::Cell(0,6,utf8_decode("ORDEN DE PAGO NRO $lista->numero"),0,0,'C');
            $pdf::Ln();
            $pdf::Ln();
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("RAZON SOCIAL :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            if($lista->persona->bussinesname!="")
                $pdf::Cell(100,7,($lista->persona->bussinesname),0,0,'L');
            else
                $pdf::Cell(100,7,($lista->persona->apellidopaterno.' '.$lista->persona->apellidomaterno.' '.$lista->persona->nombres),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(15,7,utf8_decode("FECHA :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,date("d/m/Y",strtotime($lista->fecha)),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("CONCEPTO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(100,7,$lista->conceptopago->nombre,0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(15,7,utf8_decode("RUC :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,$lista->persona->ruc,0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Ln();
            if($lista->conceptopago_id!=9){
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,7,utf8_decode("DOCUMENTO :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(100,7,utf8_decode($lista->formapago.' '.$lista->voucher),0,0,'L');
            }else{
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,7,utf8_decode("DOCUMENTOS :"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                if(trim($lista->listapago)!=""){
                    $arr=explode(",",$lista->listapago);
                    $list="";
                    $arr2=explode("@", $lista->mensajesunat);
                    for($c=0;$c<count($arr);$c++){
                        $mov=Movimiento::find($arr[$c]);
                        if($mov->tipomovimiento_id==3){
                            $numero=($mov->tipodocumento_id==5?'BV':'FT').' '.$mov->serie.'-'.$mov->numero;
                        }else{
                            $numero=$mov->formapago.' '.$mov->voucher;
                        }
                        $list.=$numero."(S/ ".number_format($arr2[$c],2,'.','')."), ";
                    }
                    if(strlen($list)>100){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::MultiCell(120,7,substr($list,0,strlen($list)-2),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(120,7,"",0,0,'L');
                    }else{
                        $pdf::Cell(120,7,substr($list,0,strlen($list)-2),0,0,'L');
                    }
                }else{
                    $pdf::Cell(100,7,utf8_decode($lista->formapago.' '.$lista->voucher),0,0,'L');
                }
            }
            $pdf::Ln();
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("COMENTARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            if(strlen($lista->comentario)>37){
                $x=$pdf::GetX();
                $y=$pdf::GetY();
                $pdf::MultiCell(110,7,$lista->comentario,0,'L');
                $pdf::SetXY($x,$y);
                $pdf::Cell(110,7,"",0,0,'L');
            }else{
                $pdf::Cell(110,7,($lista->comentario),0,0,'L');
            }
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(15,7,utf8_decode("TOTAL :"),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,number_format($lista->total,2,'.',''),0,0,'L');
            $pdf::Ln();
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(50,7,"",0,0,'L');
            $pdf::Cell(30,7,utf8_decode(($lista->conceptopago->tipo=="I"?"ENTREGADO POR :":"ENTREGADO A :")),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(110,7,($lista->nombrepaciente),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,"USUARIO",0,0,'C');
            $pdf::Cell(20,7,"",0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("DNI :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(30,7,utf8_decode($lista->dni),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,"",0,0,'L');
            $pdf::Cell(40,7,utf8_decode("RECIBI CONFORME"),'T',0,'C');            
            $pdf::SetFont('helvetica','',9);
            
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
            $pdf::Cell(80,7,utf8_decode($lista->persona->apellidopaterno." ".$lista->persona->apellidomaterno." ".$lista->persona->nombres),0,0,'L');
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
            $pdf::Cell(0,7,utf8_decode($lista->comentario),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(25,7,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,($lista->responsable->apellidopaterno." ".$lista->responsable->apellidomaterno." ".$lista->responsable->nombres),0,0,'L');
            $pdf::Ln();
        }
        $pdf::Output('ReciboCaja.pdf');
        
    }

    public function pdfMovilidad(Request $request){
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
            $responsable = $rst->responsable->nombres;//.' '.$rst->responsable->apellidopaterno. ' '. $rst->responsable->apellidomaterno; 
        }else{
            $movimiento_mayor = 0;
            $responsable = '';
        }
        
        // dd($movimiento_mayor);

        $pdf = new TCPDF();
        $pdf::SetTitle('Movilidad');
        $pdf::AddPage('L');
        // $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 5, 5, 60, 20);
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(30,5,utf8_decode("HOSPITAL PRIVADO JUAN PABLO II SAC. "),0,0,'L');
        $pdf::Ln();
        $pdf::Cell(30,5,utf8_decode("AV. GRAU #1461"),0,0,'L');
        $pdf::Ln();
        $pdf::Cell(30,2,utf8_decode("RUC: 20480082673"),0,0,'L');
        
        $pdf::SetFont('helvetica','B',12);
        $pdf::Cell(0,6,utf8_decode("PLANILLA DE GASTOS DE MOVILIDAD"),0,0,'C');
        $pdf::SetFont('helvetica','B',8);
        $pdf::Cell(0,6,utf8_decode("TESOR ").date('Y').' - _______',0,0,'R');
        
        $pdf::Ln();
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(310,5,utf8_decode("DS- 159-2017-EF Art. 37 Inc. a1) Literal V del LIR"),0,0,'C');
        $pdf::Ln();
        // $pdf::Ln();


        // $pdf::Ln();
        // $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(60,7,utf8_encode("FECHA EMISIÓN : "). date("d/m/Y"),1,0,'L');
        $pdf::Ln();
        $pdf::Cell(60,7,utf8_encode("ÁREA                   : TESORERIA"),1,0,'L');
        $pdf::Ln();
        $pdf::Cell(60,7,utf8_encode("RESPONSABLE  : ". $responsable),1,0,'L');
        $pdf::Ln();
        
        
   
        $total=0;
        $contador = 1;
        $distintos = Movimiento::join('person','person.id','=','movimiento.persona_id')
                ->where('movimiento.caja_id','=',6)
                ->where('movimiento.id','>',$movimiento_mayor)
                ->WHERE('movimiento.conceptopago_id','=',27)
                ->WHERE('movimiento.situacion','NOT LIKE','A')
                ->select('movimiento.persona_id',DB::raw('CASE WHEN person.bussinesname is null then CONCAT(person.apellidopaterno," ",person.apellidomaterno," ",person.nombres) else person.bussinesname end as paciente'),'movimiento.fechaMovilidad as fecha')
                ->groupBy('movimiento.fecha','movimiento.persona_id','paciente')
                ->orderBy('movimiento.fecha','ASC')
                ->orderBy('paciente', 'ASC')->get();

        // dd($distintos);
       foreach ($distintos as $key02 => $value02) {
            $pdf::SetFont('helvetica','B',8.5);
            $pdf::Cell(20,7,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(70,7,utf8_decode("TRABAJADOR "). str_pad($contador, 2, "0", STR_PAD_LEFT),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("DNI"),1,0,'C');
            $pdf::Cell(70,7,utf8_decode("MOTIVO"),1,0,'C');
            $pdf::Cell(40,7,utf8_decode("DESTINO"),1,0,'C');
            $pdf::Cell(20,7,utf8_encode("ORDEN"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("IMPORTE"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("FIRMA"),1,0,'C');
            $pdf::Ln();
            $list = Movimiento::where('movimiento.caja_id','=',6)
                    ->where('movimiento.id','>',$movimiento_mayor)
                    ->WHERE('movimiento.conceptopago_id','=',27)
                    ->WHERE('movimiento.situacion','NOT LIKE','A')
                    ->where('movimiento.persona_id','=',$value02->persona_id)
                    ->select('movimiento.*')
                    ->get();

            foreach ($list as $key => $value) {
                $pdf::SetFont('helvetica','',8.5);
                $pdf::Cell(20,7,utf8_decode(date('d/m/Y',strtotime($value->fecha))),1,0,'C');
                $pdf::Cell(70,7,($value02->paciente),1,0,'L');
                $pdf::Cell(20,7,utf8_decode($value->persona->dni),1,0,'C');
                // $pdf::Cell(70,7,utf8_decode($value->comentario),1,0,'L');
                if(strlen($value->comentario) >= 40){
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();                    
                    $pdf::Multicell(70,7,utf8_decode(substr($value->comentario,0,70)),0,'L');
                    $pdf::SetXY($x,$y);
                    $pdf::Cell(70,7,"",1,0,'C');
                }else{
                    $pdf::Cell(70,7,utf8_decode($value->comentario),1,0,'L');
                }
                $pdf::Cell(40,7,'CHICLAYO',1,0,'C');
                $pdf::Cell(20,7,utf8_decode($value->numero),1,0,'C');
                $pdf::Cell(20,7,number_format($value->total,2,'.',''),1,0,'R');
                // $pdf::Cell(20,7,$value->numero,1,0,'R');
                $pdf::Cell(20,7,' ',1,0,'R');
                $total = $total + number_format($value->total,2,'.','');
                $pdf::Ln();
            }

            $pdf::Cell(210,7,utf8_encode('TOTAL POR DÍA S/ '),1,0,'R');
            $pdf::Cell(20,7,number_format($total,2,'.',''),1,0,'R');
            $pdf::Ln();
            $total = 0;
            $contador++;
            
       }

       if($contador == 1){
            $pdf::SetFont('helvetica','B',8.5);
            $pdf::Cell(20,7,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(70,7,utf8_decode("TRABAJADOR "). str_pad($contador, 2, "0", STR_PAD_LEFT),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("DNI"),1,0,'C');
            $pdf::Cell(70,7,utf8_decode("MOTIVO"),1,0,'C');
            $pdf::Cell(40,7,utf8_decode("DESTINO"),1,0,'C');
            $pdf::Cell(20,7,utf8_encode("ORDEN"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("IMPORTE"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("FIRMA"),1,0,'C');
            $pdf::Ln();
            $pdf::Cell(280,7,utf8_decode("NO TIENE GASTOS DE MOVILIDAD"),1,0,'C');
            // $pdf::Cell(70,7,utf8_decode("TRABAJADOR "). str_pad($contador, 2, "0", STR_PAD_LEFT),1,0,'C');
            // $pdf::Cell(20,7,utf8_decode("DNI"),1,0,'C');
            // $pdf::Cell(70,7,utf8_decode("MOTIVO"),1,0,'C');
            // $pdf::Cell(40,7,utf8_decode("DESTINO"),1,0,'C');
            // $pdf::Cell(20,7,utf8_encode("ÁREA"),1,0,'C');
            // $pdf::Cell(20,7,utf8_decode("IMPORTE"),1,0,'C');
            // $pdf::Cell(20,7,utf8_decode("FIRMA"),1,0,'C');
            $pdf::Ln();
       }
        
      $pdf::Output('Movilidad.pdf');
        
    }
 
    public function pdfMovilidadF(Request $request){
        $caja                = Caja::find($request->input('caja_id'));
        $f_inicial           = $request->input('fi');
        $f_final             = $request->input('ff');

        $aperturas = array();
        $cierres = array();

        $caja_id      = Libreria::getParam($request->input('caja_id'),'1');
        
        $rst02        = Movimiento::where('tipomovimiento_id','=',2)
                    ->where('caja_id','=',$caja_id)
                    ->where('conceptopago_id','=',1)
                    ->where('movimiento.fecha', '>=', $f_inicial)->where('movimiento.fecha', '<=', $f_final)->orderBy('id','ASC')->get();
        if(count($rst02)>0){
            // dd($rst02);

            foreach ($rst02 as $key => $rvalue) {
                array_push($aperturas,$rvalue->id);
                $rvalue       = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',2)->where('movimiento.fecha', '>=', $f_inicial)->where('movimiento.fecha', '<=', $f_final)
                ->where('movimiento.id', '>=', $rvalue->id)
                ->orderBy('id','ASC')->first();
                if(!is_null($rvalue)){
                    array_push($cierres,$rvalue->id);
                }else{
                    array_push($cierres,0);
                }
                // echo json_encode($rvalue);     
            }

            // exit();
        }else{
            $movimiento_mayor = 0;
        }

        $vmax = sizeof($aperturas);

        // $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->orderBy('movimiento.id','DESC')->limit(1)->first();
        // if(count($rst)==0){
        //     $conceptopago_id=2;
        // }else{
        //     $conceptopago_id=$rst->conceptopago_id;
        // }
        
        $rst              = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',1)
                            ->where('fecha','=',$f_inicial)->orderBy('id','ASC')->limit(1)->first();
        if(count($rst)>0){
            // $movimiento_mayor = $rst->id;   
            $responsable = $rst->responsable->nombres;
            $fechaE = $rst->fecha;//.' '.$rst->responsable->apellidopaterno. ' '. $rst->responsable->apellidomaterno; 
        }else{
            // $movimiento_mayor = 0;
            $responsable = '';
            $fechaE = '';
        }
        


        $pdf = new TCPDF();
        $pdf::SetTitle('Movilidad');
        $pdf::AddPage('L');
        // $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 5, 5, 60, 20);
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(30,5,utf8_decode("HOSPITAL PRIVADO JUAN PABLO II SAC. "),0,0,'L');
        $pdf::Ln();
        $pdf::Cell(30,5,utf8_decode("AV. GRAU #1461"),0,0,'L');
        $pdf::Ln();
        $pdf::Cell(30,2,utf8_decode("RUC: 20480082673"),0,0,'L');
        
        $pdf::SetFont('helvetica','B',12);
        $pdf::Cell(0,6,utf8_decode("PLANILLA DE GASTOS DE MOVILIDAD"),0,0,'C');
        $pdf::SetFont('helvetica','B',8);
        $pdf::Cell(0,6,utf8_decode("TESOR ").date('Y').' - _______',0,0,'R');
        
        $pdf::Ln();
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(310,5,utf8_decode("DS- 159-2017-EF Art. 37 Inc. a1) Literal V del LIR"),0,0,'C');
        $pdf::Ln();
        // $pdf::Ln();


        // $pdf::Ln();
        // $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(60,7,utf8_encode("FECHA EMISIÓN : "). date("d/m/Y",strtotime($fechaE)),1,0,'L');
        $pdf::Ln();
        $pdf::Cell(60,7,utf8_encode("ÁREA                   : TESORERIA"),1,0,'L');
        $pdf::Ln();
        $pdf::Cell(60,7,utf8_encode("RESPONSABLE  : ". $responsable),1,0,'L');
        $pdf::Ln();
          

        $total=0;
        $contador = 1;

        for ($valor=0; $valor < $vmax -1; $valor++) {

            $distintos = Movimiento::join('person','person.id','=','movimiento.persona_id')
                ->where('movimiento.caja_id','=',$caja_id)
                ->WHERE('movimiento.conceptopago_id','=',27)
                ->WHERE('movimiento.situacion','NOT LIKE','A')
                ->where(function ($query) use($aperturas,$cierres,$valor) {
                    $query
                    ->where(function($q) use($aperturas,$cierres,$valor){
                        $q
                        ->where('movimiento.id', '>', $aperturas[$valor])
                        ->where('movimiento.id', '<', $cierres[$valor])
                        ->whereNull('movimiento.cajaapertura_id');
                    })
                    ->orwhere(function ($query1) use($aperturas,$cierres,$valor){
                        $query1->where('movimiento.cajaapertura_id','=',$aperturas[$valor]);
                    });//normal
                })->select('movimiento.persona_id',DB::raw('CASE WHEN person.bussinesname is null then CONCAT(person.apellidopaterno," ",person.apellidomaterno," ",person.nombres) else person.bussinesname end as paciente'),'movimiento.fechaMovilidad')
                    ->groupBy('movimiento.fechaMovilidad','movimiento.persona_id','paciente')
                    ->orderBy('movimiento.fechaMovilidad','ASC')
                    ->orderBy('paciente', 'ASC')->get();  


            foreach ($distintos as $key02 => $value02) {
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(20,7,utf8_decode("FECHA"),1,0,'C');
                $pdf::Cell(70,7,utf8_decode("TRABAJADOR "). str_pad($contador, 2, "0", STR_PAD_LEFT),1,0,'C');
                $pdf::Cell(20,7,utf8_decode("DNI"),1,0,'C');
                $pdf::Cell(70,7,utf8_decode("MOTIVO"),1,0,'C');
                $pdf::Cell(40,7,utf8_decode("DESTINO"),1,0,'C');
                // $pdf::Cell(20,7,utf8_encode("ÁREA"),1,0,'C');
                $pdf::Cell(20,7,utf8_decode("ORDEN"),1,0,'C');
                $pdf::Cell(20,7,utf8_decode("IMPORTE"),1,0,'C');
 
                $pdf::Cell(20,7,utf8_decode("FIRMA"),1,0,'C');
                $pdf::Ln();

                $list = Movimiento::where('movimiento.caja_id','=',$caja_id)
                        ->WHERE('movimiento.conceptopago_id','=',27)
                        ->WHERE('movimiento.situacion','NOT LIKE','A')
                        ->where(function ($query) use($aperturas,$cierres,$valor) {
                            $query
                            ->where(function($q) use($aperturas,$cierres,$valor){
                                $q
                                ->where('movimiento.id', '>', $aperturas[$valor])
                                ->where('movimiento.id', '<', $cierres[$valor])
                                ->whereNull('movimiento.cajaapertura_id');
                            })
                            ->orwhere(function ($query1) use($aperturas,$cierres,$valor){
                                $query1->where('movimiento.cajaapertura_id','=',$aperturas[$valor]);
                            });//normal
                        })
                        ->where('movimiento.persona_id','=',$value02->persona_id)
                        ->where('movimiento.fechaMovilidad','=',$value02->fechaMovilidad)
                        ->select('movimiento.*')
                        ->get();

                        // dd($list);
                foreach ($list as $key => $value) {
                    $pdf::SetFont('helvetica','',8.5);
                    if($value->fechaMovilidad == ''){
                        $pdf::Cell(20,7,utf8_decode(date('d/m/Y',strtotime($value->fecha))),1,0,'C');
                    }else{
                        $pdf::Cell(20,7,utf8_decode(date('d/m/Y',strtotime($value->fechaMovilidad))),1,0,'C');
                    }
                    
                    $pdf::Cell(70,7,($value02->paciente),1,0,'L');
                    if(!is_null($value->persona)){
                        $pdf::Cell(20,7,utf8_decode($value->persona->dni),1,0,'C');
                    }else{
                        $pdf::Cell(20,7,'-',1,0,'C');
                        
                    }
                    if(strlen($value->comentario) >= 40){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(70,7,substr($value->comentario,0,70),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(70,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(70,7,$value->comentario,1,0,'L');
                    }

                    $pdf::Cell(40,7,'CHICLAYO',1,0,'C');
                    $pdf::Cell(20,7,utf8_decode($value->numero),1,0,'C');
                    $pdf::Cell(20,7,number_format($value->total,2,'.',''),1,0,'R');
                    // $pdf::Cell(20,7,$value->numero,1,0,'R');
                    $pdf::Cell(20,7,' ',1,0,'R');
                    $total = $total + number_format($value->total,2,'.','');
                    $pdf::Ln();
                }

                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(240,7,utf8_encode('TOTAL POR DÍA S/ '),1,0,'R');
                $pdf::Cell(20,7,number_format($total,2,'.',''),1,0,'R');
                $pdf::Ln();
                $total = 0;
                $contador++;
             }
        }


       if($contador == 1){
            $pdf::SetFont('helvetica','B',8.5);
            $pdf::Cell(20,7,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(70,7,utf8_decode("TRABAJADOR "). str_pad($contador, 2, "0", STR_PAD_LEFT),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("DNI"),1,0,'C');
            $pdf::Cell(70,7,utf8_decode("MOTIVO"),1,0,'C');
            $pdf::Cell(40,7,utf8_decode("DESTINO"),1,0,'C');
            $pdf::Cell(20,7,utf8_encode("ORDEN"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("IMPORTE"),1,0,'C');
            $pdf::Cell(20,7,utf8_decode("FIRMA"),1,0,'C');
            $pdf::Ln();
            $pdf::Cell(280,7,utf8_decode("NO TIENE GASTOS DE MOVILIDAD"),1,0,'C');
            // $pdf::Cell(70,7,utf8_decode("TRABAJADOR "). str_pad($contador, 2, "0", STR_PAD_LEFT),1,0,'C');
            // $pdf::Cell(20,7,utf8_decode("DNI"),1,0,'C');
            // $pdf::Cell(70,7,utf8_decode("MOTIVO"),1,0,'C');
            // $pdf::Cell(40,7,utf8_decode("DESTINO"),1,0,'C');
            // $pdf::Cell(20,7,utf8_encode("ÁREA"),1,0,'C');
            // $pdf::Cell(20,7,utf8_decode("IMPORTE"),1,0,'C');
            // $pdf::Cell(20,7,utf8_decode("FIRMA"),1,0,'C');
            $pdf::Ln();
        }
        
        // $rst        = Movimiento::where('tipomovimiento_id','=',2)
        //             ->where('caja_id','=',$caja_id)
        //             ->where('conceptopago_id','=',1)
        //             ->where('movimiento.fecha', '>=', $f_inicial)->where('movimiento.fecha', '<=', $f_final)->orderBy('id','ASC')->get();
        // if(count($rst)>0){
        //     foreach ($rst as $key => $rvalue) {
        //         array_push($aperturas,$rvalue->id);
        //         $rvalue       = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',2)->where('movimiento.fecha', '>=', $f_inicial)->where('movimiento.fecha', '<=', $f_final)
        //         ->where('movimiento.id', '>=', $rvalue->id)
        //         ->orderBy('id','ASC')->first();
        //         if(!is_null($rvalue)){
        //             array_push($cierres,$rvalue->id);
        //         }else{
        //             array_push($cierres,0);
        //         }

        //     }
            
        // }else{
        //     $movimiento_mayor = 0;
        // }
        
        // $vmax = sizeof($aperturas);
        // $pdf = new TCPDF();
        // $pdf::SetTitle('Movilidad');

        // for ($valor=0; $valor < $vmax -1; $valor++) {
        //     //echo $aperturas[$valor].' - '.$cierres[$valor].' ';
        //     //dd($aperturas,$cierres);
        //     $list = Movimiento::join('person','person.id','=','movimiento.persona_id')
        //         ->where('caja_id','=',6)
        //         //->where('movimiento.id','>',$aperturas[$valor])
        //         //->where('movimiento.id', '<', $cierres[$valor])
        //         ->WHERE('movimiento.conceptopago_id','=',27)
        //         ->WHERE('movimiento.situacion','NOT LIKE','A')
        //         ->where(function ($query) use($aperturas,$cierres,$valor) {
        //             $query
        //             ->where(function($q) use($aperturas,$cierres,$valor){
        //                 $q
        //                 ->where('movimiento.id', '>', $aperturas[$valor])
        //                 ->where('movimiento.id', '<', $cierres[$valor])
        //                 ->whereNull('movimiento.cajaapertura_id');
        //             })
        //             ->orwhere(function ($query1) use($aperturas,$cierres,$valor){
        //                 $query1->where('movimiento.cajaapertura_id','=',$aperturas[$valor]);
        //             });//normal
        //         })
        //         ->select('movimiento.*',DB::raw('CASE WHEN person.bussinesname is null then CONCAT(person.apellidopaterno," ",person.apellidomaterno," ",person.nombres) else person.bussinesname end as paciente'))
        //         ->get();
                    
        //     $pdf::AddPage('L');
        //     $pdf::SetFont('helvetica','B',12);
        //     $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 5, 70, 20);
        //     $pdf::Cell(0,6,utf8_decode("PLANILLA DE GASTOS DE MOVILIDAD"),0,0,'C');
        //     $pdf::Ln();
        //     $pdf::Ln();
        //     $pdf::Ln();
        //     $pdf::SetFont('helvetica','B',9);
        //     $pdf::Cell(30,7,utf8_decode("FECHA DE PAGO :"),0,0,'L');
        //     $pdf::SetFont('helvetica','',9);
        //     $aper=Movimiento::find($aperturas[$valor]);
        //     $pdf::Cell(30,7,date("d/m/Y",strtotime($aper->fecha)),0,0,'L');
        //     $pdf::SetFont('helvetica','B',9);
        //     $pdf::Cell(15,7,utf8_decode("AREA :"),0,0,'L');
        //     $pdf::SetFont('helvetica','',9);
        //     $pdf::Cell(50,7,'TESORERIA',0,0,'L');
        //     $pdf::Cell(100,7,utf8_decode("DS- 159-2017-EF Art. 37 Inc. a1) Literal V del LII"),0,0,'C');
        //     $pdf::Ln();
        //     $pdf::SetFont('helvetica','B',9);
        //     $pdf::Cell(70,7,utf8_decode("NOMBRE TRABAJADOR"),1,0,'C');
        //     $pdf::Cell(120,7,utf8_decode("MOTIVO"),1,0,'C');
        //     $pdf::Cell(20,7,utf8_decode("DESTINO"),1,0,'C');
        //     $pdf::Cell(20,7,utf8_decode("IMPORTE"),1,0,'C');
        //     $pdf::Cell(20,7,utf8_decode("ORDEN"),1,0,'C');
        //     $pdf::Ln();
        //     $total=0;
        //     foreach ($list as $key => $value) {
        //         $pdf::SetFont('helvetica','',8);
        //         $pdf::Cell(70,7,($value->paciente),1,0,'L');
        //         $pdf::Cell(120,7,utf8_decode($value->comentario),1,0,'L');
        //         $pdf::Cell(20,7,'CHICLAYO',1,0,'L');
        //         $pdf::Cell(20,7,number_format($value->total,2,'.',''),1,0,'R');
        //         $pdf::Cell(20,7,$value->numero,1,0,'R');
        //         $total = $total + number_format($value->total,2,'.','');
        //         $pdf::Ln();
        //     }
        //     $pdf::Cell(210,7,('TOTAL'),1,0,'R');
        //     $pdf::Cell(20,7,number_format($total,2,'.',''),1,0,'R');
        //     $pdf::Ln();
        // }

        $pdf::Output('Movilidad.pdf');
        
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
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
        
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->leftjoin('area','area.id','=','movimiento.area_id')
                            ->where('movimiento.caja_id', '=', $caja_id)
                            ->where('movimiento.id', '>', $movimiento_mayor)
                            ->whereNull('movimiento.cajaapertura_id')
                            ->where('movimiento.situacion', '<>', 'A');
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','responsable.nombres as responsable2','area.nombre as area2',DB::raw('CASE WHEN paciente.bussinesname is null then CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.bussinesname end as paciente'),'conceptopago.tipo')->orderBy('conceptopago.tipo', 'asc')->orderBy('movimiento.numeroficha','asc')->orderBy('conceptopago.id', 'asc')->orderBy('movimiento.comentario', 'asc')->orderBy('movimiento.numero', 'asc');
        $lista            = $resultado->get();

        Excel::create('Excel', function($excel) use($lista,$request,$movimiento_mayor) {
 
            $excel->sheet('Tesoreria', function($sheet) use($lista,$request,$movimiento_mayor) {
                $celdas      = 'A1:F1';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $celdas      = 'C2:E2';
                $sheet->mergeCells($celdas);
                $celdas      = 'C2:F2';
                $sheet->cells($celdas, function($cells) {
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                
                $apertura = Movimiento::find($movimiento_mayor);
                $title = array();
                $title[]="Caja Tesoreria ".date("d/m/Y",strtotime($apertura->fecha));
                $sheet->row(1,$title);
                $title = array();
                $title[]="";
                $title[]="";
                $title[]="Saldo del dia anterior";
                $title[]="";
                $title[]="";
                $title[]=$apertura->total;
                $sheet->row(2,$title);
                $cabecera[] = "";
                $cabecera[] = "Caja";
                $cabecera[] = "Ingreso";
                $cabecera[] = "Egreso";
                $sheet->row(3,$cabecera);
                $sheet->mergeCells('D3:F3');
                $sheet->cells('B3:F3', function($cells) {
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $c=4;$d=4;$band=true;$concepto="";$egresos=0;$caja2="";$x=4;$ingresos=0;$oingresos=0;
                foreach ($lista as $key => $value){
                    if($value->conceptopago_id!=$concepto && $value->tipo=='E'){
                        if($value->conceptopago_id!=60 && $value->numeroficha!="Deposito"){
                            $sheet->mergeCells('D'.$c.':E'.$c);
                            $sheet->cells("D".$c.":F".$c, function($cells) {
                                $cells->setAlignment('center');
                                $cells->setBorder('','thin','','thin');
                                $cells->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '10',
                                    'bold'       =>  true
                                ));
                            });
                            $detalle = array();
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = $value->conceptopago->nombre;
                            $sheet->row($c,$detalle);
                            $c=$c+1;
                        }elseif($value->numeroficha=='Deposito' && $band){
                            $sheet->mergeCells('D'.$c.':E'.$c);
                            $sheet->cells("D".$c.":F".$c, function($cells) {
                                $cells->setAlignment('center');
                                $cells->setBorder('','thin','','thin');
                                $cells->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '10',
                                    'bold'       =>  true
                                ));
                            });
                            $detalle = array();
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = 'DEPOSITO';
                            $sheet->row($c,$detalle);
                            $c=$c+1;$band=false;
                        }
                        $concepto=$value->conceptopago_id;
                    }
                    if($value->conceptopago_id==60 || $value->tipo=='I'){
                        if($value->conceptopago_id==60){
                            $caja=Movimiento::find($value->movimiento_id);
                            $sheet->cells("B".$d,function($cells) use ($caja){
                                $cells->setValue($caja->persona->nombres);
                            });
                            $sheet->cells("A".$d,function($cells) use ($caja){
                                $cells->setValue($caja->caja->nombre);
                            });
                            $sheet->cells("C".$d,function($cells) use ($value){
                                $cells->setValue(number_format($value->total,2,'.',''));
                            });
                            $ingresos = $ingresos + $value->total;
                            if($caja2!=$caja->caja->nombre){
                                if($caja2!=""){
                                    $sheet->mergeCells('A'.$x.':A'.($d-1));
                                    $sheet->cells('A'.$x.':A'.($d-1), function($cells) {
                                        $cells->setBorder('thin','thin','thin','thin');
                                        $cells->setFont(array(
                                            'family'     => 'Calibri',
                                            'size'       => '10',
                                            'bold'       =>  true
                                            ));
                                    });
                                }
                                $caja2=$caja->caja->nombre;
                                $x=$d;
                            }
                            $sheet->cells("A".$d.":C".$d, function($cells) {
                                $cells->setBorder('','thin','','thin');
                                $cells->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '10',
                                ));
                            });
                            $d=$d+1;
                        }else{
                            if($oingresos==0){
                                $sheet->mergeCells('A'.$x.':A'.($d-1));
                                $sheet->cells('A'.$x.':A'.($d-1), function($cells) {
                                    $cells->setBorder('thin','thin','thin','thin');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        'bold'       =>  true
                                        ));
                                });
                                $sheet->mergeCells('B'.$d.':C'.$d);
                                $sheet->cells("B".$d,function($cells) use ($caja){
                                    $cells->setValue('OTROS INGRESOS');
                                });
                                $sheet->cells('B'.$d.':C'.$d, function($cells) {
                                    $cells->setBorder('thin','thin','thin','thin');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        'bold'       =>  true
                                        ));
                                });
                                $d=$d+1;
                            }
                            $sheet->cells("B".$d,function($cells) use ($value){
                                $cells->setValue($value->paciente);
                            });
                            $sheet->cells("C".$d,function($cells) use ($value){
                                $cells->setValue(number_format($value->total,2,'.',''));
                            });
                            $sheet->cells('B'.$d.':C'.$d, function($cells) {
                                $cells->setBorder('','thin','','thin');
                                $cells->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '10',
                                    ));
                            });
                            $d=$d+1;
                            $oingresos = $oingresos + $value->total;
                            $ingresos = $ingresos + $value->total;
                        }
                    }else{
                        $detalle = array();
                        $detalle[] = "";
                        $detalle[] = "";
                        $detalle[] = "";
                        $detalle[] = $value->paciente;
                        $detalle[] = $value->comentario;
                        $detalle[] = number_format($value->total,2,'.','');
                        $egresos = $egresos + $value->total;
                        $sheet->row($c,$detalle);
                        $sheet->cells("D".$c.":F".$c, function($cells) {
                            $cells->setBorder('','thin','','thin');
                            $cells->setFont(array(
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ));
                        });
                        $c=$c+1;
                    }
                }
                if($oingresos==0){
                    $sheet->mergeCells('A'.$x.':A'.($d-1));
                    $sheet->cells('A'.$x.':A'.($d-1), function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });
                }
                if($d>$c) $c=$d;
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "Total";
                $detalle[] = number_format($ingresos,2,'.','');
                $detalle[] = "Total";
                $detalle[] = "";
                $detalle[] = number_format($egresos,2,'.','');
                $sheet->row($c,$detalle);
                $sheet->mergeCells('D'.$c.':E'.$c);
                $sheet->cells("D".$c.":F".$c, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                    ));
                });
                $sheet->cells("B".$c.":C".$c, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                    ));
                });
                $c=$c+1;
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "Total General";
                $detalle[] = "";
                $detalle[] = number_format($ingresos - $egresos +$apertura->total,2,'.','');
                $sheet->row($c,$detalle);
                $sheet->mergeCells('D'.$c.':E'.$c);
                $sheet->cells("D".$c.":F".$c, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                    ));
                });
            });
        })->export('xls');
    }

    public function excelF(Request $request){
        setlocale(LC_TIME, 'spanish');
        $caja_id             = 6;
        $f_inicial           = $request->input('fi');
        $f_final             = $request->input('ff');
         $aperturas = array();
        $cierres = array();
        $rst        = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',1)->where('movimiento.fecha', '>=', $f_inicial)->where('movimiento.fecha', '<=', $f_final)->orderBy('id','ASC')->get();
        if(count($rst)>0){
            foreach ($rst as $key => $rvalue) {
                array_push($aperturas,$rvalue->id);
                $rvalue       = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',2)->where('movimiento.fecha', '>=', $f_inicial)->where('movimiento.fecha', '<=', $f_final)
                ->where('movimiento.id', '>=', $rvalue->id)
                ->orderBy('id','ASC')->first();
                if(!is_null($rvalue)){
                    array_push($cierres,$rvalue->id);
                }else{
                    array_push($cierres,0);
                }

            }
            
        }else{
            $movimiento_mayor = 0;
        }
        
        $vmax = sizeof($aperturas);
        
        for ($valor=0; $valor < $vmax; $valor++) {
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                                ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                                ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                                ->leftjoin('area','area.id','=','movimiento.area_id')
                                ->where('movimiento.caja_id', '=', 6)
                                ->where('movimiento.situacion', '<>', 'A')
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
                                ->orWhere('movimiento.cajaapertura_id','=',$aperturas[$valor])
                                ->whereNull('movimiento.cajaapertura_id');
            $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','responsable.nombres as responsable2','area.nombre as area2',DB::raw('CASE WHEN paciente.bussinesname is null then CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.bussinesname end as paciente'),'conceptopago.tipo')->orderBy('conceptopago.tipo', 'asc')->orderBy('movimiento.numeroficha','asc')->orderBy('conceptopago.id', 'asc')->orderBy('movimiento.comentario', 'asc')->orderBy('movimiento.numero', 'asc');
            $lista            = $resultado->get();
            $movimiento_mayor=$aperturas[$valor];
            Excel::create('Excel', function($excel) use($lista,$request,$movimiento_mayor) {
     
                $excel->sheet('Tesoreria', function($sheet) use($lista,$request,$movimiento_mayor) {
                    $celdas      = 'A1:F1';
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });
                    $celdas      = 'C2:E2';
                    $sheet->mergeCells($celdas);
                    $celdas      = 'C2:F2';
                    $sheet->cells($celdas, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });
                    
                    $apertura = Movimiento::find($movimiento_mayor);
                    $title = array();
                    $title[]="Caja Tesoreria ".date("d/m/Y",strtotime($apertura->fecha));
                    $sheet->row(1,$title);
                    $title = array();
                    $title[]="";
                    $title[]="";
                    $title[]="Saldo del dia anterior";
                    $title[]="";
                    $title[]="";
                    $title[]=$apertura->total;
                    $sheet->row(2,$title);
                    $cabecera[] = "";
                    $cabecera[] = "Caja";
                    $cabecera[] = "Ingreso";
                    $cabecera[] = "Egreso";
                    $sheet->row(3,$cabecera);
                    $sheet->mergeCells('D3:F3');
                    $sheet->cells('B3:F3', function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });
                    $c=4;$d=4;$band=true;$concepto="";$egresos=0;$caja2="";$x=4;$ingresos=0;$oingresos=0;
                    foreach ($lista as $key => $value){
                        if($value->conceptopago_id!=$concepto && $value->tipo=='E'){
                            if($value->conceptopago_id!=60 && $value->numeroficha!="Deposito"){
                                $sheet->mergeCells('D'.$c.':E'.$c);
                                $sheet->cells("D".$c.":F".$c, function($cells) {
                                    $cells->setAlignment('center');
                                    $cells->setBorder('','thin','','thin');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        'bold'       =>  true
                                    ));
                                });
                                $detalle = array();
                                $detalle[] = "";
                                $detalle[] = "";
                                $detalle[] = "";
                                $detalle[] = $value->conceptopago->nombre;
                                $sheet->row($c,$detalle);
                                $c=$c+1;
                            }elseif($value->numeroficha=='Deposito' && $band){
                                $sheet->mergeCells('D'.$c.':E'.$c);
                                $sheet->cells("D".$c.":F".$c, function($cells) {
                                    $cells->setAlignment('center');
                                    $cells->setBorder('','thin','','thin');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        'bold'       =>  true
                                    ));
                                });
                                $detalle = array();
                                $detalle[] = "";
                                $detalle[] = "";
                                $detalle[] = "";
                                $detalle[] = 'DEPOSITO';
                                $sheet->row($c,$detalle);
                                $c=$c+1;$band=false;
                            }
                            $concepto=$value->conceptopago_id;
                        }
                        if($value->conceptopago_id==60 || $value->tipo=='I'){
                            if($value->conceptopago_id==60){
                                $caja=Movimiento::find($value->movimiento_id);
                                $sheet->cells("B".$d,function($cells) use ($caja){
                                    $cells->setValue($caja->persona->nombres);
                                });
                                $sheet->cells("A".$d,function($cells) use ($caja){
                                    $cells->setValue($caja->caja->nombre);
                                });
                                $sheet->cells("C".$d,function($cells) use ($value){
                                    $cells->setValue(number_format($value->total,2,'.',''));
                                });
                                $ingresos = $ingresos + $value->total;
                                if($caja2!=$caja->caja->nombre){
                                    if($caja2!=""){
                                        $sheet->mergeCells('A'.$x.':A'.($d-1));
                                        $sheet->cells('A'.$x.':A'.($d-1), function($cells) {
                                            $cells->setBorder('thin','thin','thin','thin');
                                            $cells->setFont(array(
                                                'family'     => 'Calibri',
                                                'size'       => '10',
                                                'bold'       =>  true
                                                ));
                                        });
                                    }
                                    $caja2=$caja->caja->nombre;
                                    $x=$d;
                                }
                                $sheet->cells("A".$d.":C".$d, function($cells) {
                                    $cells->setBorder('','thin','','thin');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                    ));
                                });
                                $d=$d+1;
                            }else{
                                if($oingresos==0){
                                    $sheet->mergeCells('A'.$x.':A'.($d-1));
                                    $sheet->cells('A'.$x.':A'.($d-1), function($cells) {
                                        $cells->setBorder('thin','thin','thin','thin');
                                        $cells->setFont(array(
                                            'family'     => 'Calibri',
                                            'size'       => '10',
                                            'bold'       =>  true
                                            ));
                                    });
                                    $sheet->mergeCells('B'.$d.':C'.$d);
                                    $sheet->cells("B".$d,function($cells) use ($caja){
                                        $cells->setValue('OTROS INGRESOS');
                                    });
                                    $sheet->cells('B'.$d.':C'.$d, function($cells) {
                                        $cells->setBorder('thin','thin','thin','thin');
                                        $cells->setFont(array(
                                            'family'     => 'Calibri',
                                            'size'       => '10',
                                            'bold'       =>  true
                                            ));
                                    });
                                    $d=$d+1;
                                }
                                $sheet->cells("B".$d,function($cells) use ($value){
                                    $cells->setValue($value->paciente.' - '.$value->conceptopago->nombre);
                                });
                                $sheet->cells("C".$d,function($cells) use ($value){
                                    $cells->setValue(number_format($value->total,2,'.',''));
                                });
                                $sheet->cells('B'.$d.':C'.$d, function($cells) {
                                    $cells->setBorder('','thin','','thin');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        ));
                                });
                                $d=$d+1;
                                $oingresos = $oingresos + $value->total;
                                $ingresos = $ingresos + $value->total;
                            }
                        }else{
                            $detalle = array();
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = $value->paciente;
                            $detalle[] = $value->comentario;
                            $detalle[] = number_format($value->total,2,'.','');
                            $egresos = $egresos + $value->total;
                            $sheet->row($c,$detalle);
                            $sheet->cells("D".$c.":F".$c, function($cells) {
                                $cells->setBorder('','thin','','thin');
                                $cells->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '10',
                                ));
                            });
                            $c=$c+1;
                        }
                    }
                    if($oingresos==0){
                        $sheet->mergeCells('A'.$x.':A'.($d-1));
                        $sheet->cells('A'.$x.':A'.($d-1), function($cells) {
                            $cells->setBorder('thin','thin','thin','thin');
                            $cells->setFont(array(
                                'family'     => 'Calibri',
                                'size'       => '10',
                                'bold'       =>  true
                                ));
                        });
                    }
                    if($d>$c) $c=$d;
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "Total";
                    $detalle[] = number_format($ingresos,2,'.','');
                    $detalle[] = "Total";
                    $detalle[] = "";
                    $detalle[] = number_format($egresos,2,'.','');
                    $sheet->row($c,$detalle);
                    $sheet->mergeCells('D'.$c.':E'.$c);
                    $sheet->cells("D".$c.":F".$c, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cells("B".$c.":C".$c, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                        ));
                    });
                    $c=$c+1;
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "Total General";
                    $detalle[] = "";
                    $detalle[] = number_format($ingresos - $egresos +$apertura->total,2,'.','');
                    $sheet->row($c,$detalle);
                    $sheet->mergeCells('D'.$c.':E'.$c);
                    $sheet->cells("D".$c.":F".$c, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                        ));
                    });
                });
            })->export('xls');
        }
    }

    public function egresosExcel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $f_inicial           = $request->input('fi');
        $f_final             = $request->input('ff');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->leftjoin('area','area.id','=','movimiento.area_id')
                            ->where('movimiento.situacion', '<>', 'A')
                            ->whereNotIn('movimiento.conceptopago_id', [2,8])
                            ->whereNotIn('movimiento.caja_id', [6,7])
                            ->where('conceptopago.tipo', '=', 'E')
                            ->whereNotNull('movimiento.caja_id')
                            ->where('movimiento.fecha', '>=', $f_inicial)
                            ->where('movimiento.fecha', '<=', $f_final);
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','responsable.nombres as responsable2','area.nombre as area2',DB::raw('CASE WHEN paciente.bussinesname is null then CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.bussinesname end as paciente'),'conceptopago.tipo')->orderBy('movimiento.fecha','asc')->orderBy('movimiento.caja_id', 'asc')->orderBy('movimiento.numero', 'asc');
        $lista            = $resultado->get();

        Excel::create('Egresos', function($excel) use($lista,$request) {
 
            $excel->sheet('EgresosCajas', function($sheet) use($lista,$request) {
                $c=1;$d=4;$band=true;$fecha="";$egresos=0;$caja=0;$x=4;$total=0;$oingresos=0;
                foreach ($lista as $key => $value){
                    if($value->fecha!=$fecha){
                        if($value->caja_id!=$caja){
                            if($caja==1 && $value->caja_id==2){

                            }else{
                                if($caja!=0){
                                    $sheet->mergeCells('A'.$c.':F'.$c);
                                    $sheet->cells("A".$c.":G".$c, function($cells) {
                                        $cells->setAlignment('center');
                                        $cells->setBorder('thin','thin','thin','thin');
                                        $cells->setFont(array(
                                            'family'     => 'Calibri',
                                            'size'       => '10',
                                            'bold'       =>  true,
                                        ));
                                    });
                                    $detalle = array();
                                    $detalle[] = "Total ".date("d/m/Y",strtotime($fecha));
                                    $detalle[] = "";
                                    $detalle[] = "";
                                    $detalle[] = "";
                                    $detalle[] = "";
                                    $detalle[] = "";
                                    $detalle[] = number_format($egresos,2,'.','');
                                    $sheet->row($c,$detalle);
                                    $egresos=0;
                                    $c=$c+2;
                                }

                                if($fecha!=""){
                                    $sheet->mergeCells('E'.$c.':F'.$c);
                                    $sheet->cells("E".$c.":G".$c, function($cells) {
                                        $cells->setAlignment('center');
                                        $cells->setBorder('thin','thin','thin','thin');
                                        $cells->setFont(array(
                                            'family'     => 'Calibri',
                                            'size'       => '10',
                                            'bold'       =>  true,
                                        ));
                                    });
                                    $detalle = array();
                                    $detalle[] = "";
                                    $detalle[] = "";
                                    $detalle[] = "";
                                    $detalle[] = "";
                                    $detalle[] = "Total ".date("d/m/Y",strtotime($fecha));
                                    $detalle[] = "";
                                    $detalle[] = number_format($total,2,'.','');
                                    $sheet->row($c,$detalle);
                                    $total=0;
                                    $c=$c+2;
                                }
                                
                                $sheet->mergeCells('A'.$c.':G'.$c);
                                $sheet->cells("A".$c.":G".$c, function($cells) {
                                    $cells->setAlignment('center');
                                    $cells->setBorder('thin','thin','thin','thin');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        'bold'       =>  true
                                    ));
                                });
                                $detalle = array();
                                if($value->caja_id==1 || $value->caja_id==2){
                                    $detalle[] = "ADMISION";
                                }else{
                                    $Caja = Caja::find($value->caja_id);
                                    $detalle[] = $Caja->nombre;
                                }
                                $sheet->row($c,$detalle);
                                $c=$c+1;
                                $caja=$value->caja_id;
                                $detalle = array();
                                $detalle[] = "FECHA";
                                $detalle[] = "AREA";
                                $detalle[] = "TIPO DOC";
                                $detalle[] = "PERSONA";
                                $detalle[] = "DETALLE";
                                $detalle[] = "USUARIO";
                                $detalle[] = "TOTAL";
                                $sheet->row($c,$detalle);
                                $sheet->cells("A".$c.":G".$c, function($cells) {
                                    $cells->setBorder('thin','thin','thin','thin');
                                    $cells->setAlignment('center');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        'bold'       => true,
                                    ));
                                });
                                $c=$c+1;
                            }
                        }
                        $fecha=$value->fecha;
                    }else{
                        if($value->caja_id!=$caja){
                            if($caja==1 && $value->caja_id==2){

                            }else{
                                if($caja!=0){
                                    $sheet->mergeCells('A'.$c.':F'.$c);
                                    $sheet->cells("A".$c.":G".$c, function($cells) {
                                        $cells->setAlignment('center');
                                        $cells->setBorder('thin','thin','thin','thin');
                                        $cells->setFont(array(
                                            'family'     => 'Calibri',
                                            'size'       => '10',
                                            'bold'       =>  true
                                        ));
                                    });
                                    $detalle = array();
                                    $detalle[] = "Total ".date("d/m/Y",strtotime($fecha));
                                    $detalle[] = "";
                                    $detalle[] = "";
                                    $detalle[] = "";
                                    $detalle[] = "";
                                    $detalle[] = "";
                                    $detalle[] = number_format($egresos,2,'.','');
                                    $sheet->row($c,$detalle);
                                    $egresos=0;
                                    $c=$c+2;
                                }
                                $sheet->mergeCells('A'.$c.':G'.$c);
                                $sheet->cells("A".$c.":G".$c, function($cells) {
                                    $cells->setAlignment('center');
                                    $cells->setBorder('thin','thin','thin','thin');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        'bold'       =>  true
                                    ));
                                });
                                $detalle = array();
                                if($value->caja_id==1 || $value->caja_id==2){
                                    $detalle[] = "ADMISION";
                                }else{
                                    $Caja = Caja::find($value->caja_id);
                                    $detalle[] = $Caja->nombre;
                                }
                                $sheet->row($c,$detalle);
                                $c=$c+1;
                                $caja=$value->caja_id;
                                $detalle = array();
                                $detalle[] = "FECHA";
                                $detalle[] = "AREA";
                                $detalle[] = "TIPO DOC";
                                $detalle[] = "PERSONA";
                                $detalle[] = "DETALLE";
                                $detalle[] = "USUARIO";
                                $detalle[] = "TOTAL";
                                $sheet->row($c,$detalle);
                                $sheet->cells("A".$c.":G".$c, function($cells) {
                                    $cells->setBorder('thin','thin','thin','thin');
                                    $cells->setAlignment('center');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        'bold'       => true,
                                    ));
                                });
                                $c=$c+1;
                            }
                        }
                    }
                    $detalle = array();
                    $detalle[] = date("d/m/Y",strtotime($value->fecha));
                    $detalle[] = $value->area2;
                    $detalle[] = $value->formapago;
                    $detalle[] = $value->paciente;
                    $detalle[] = $value->comentario;
                    $detalle[] = $value->responsable2;
                    $detalle[] = number_format($value->total,2,'.','');
                    $egresos = $egresos + $value->total;
                    $total = $total + $value->total;
                    $sheet->row($c,$detalle);
                    $sheet->cells("A".$c.":G".$c, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                        ));
                    });
                    $c=$c+1;
                }
                $sheet->mergeCells('A'.$c.':F'.$c);
                $sheet->cells("A".$c.":G".$c, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                    ));
                });
                $detalle = array();
                $detalle[] = "Total ".date("d/m/Y",strtotime($fecha));
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = number_format($egresos,2,'.','');
                $sheet->row($c,$detalle);
                $egresos=0;
                $c=$c+2;

                $sheet->mergeCells('E'.$c.':F'.$c);
                $sheet->cells("E".$c.":G".$c, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true,
                    ));
                });
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "Total ".date("d/m/Y",strtotime($fecha));
                $detalle[] = "";
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
                $total=0;
                $c=$c+2;
            });
        })->export('xls');
    }

}