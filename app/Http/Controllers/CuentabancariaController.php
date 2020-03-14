<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Cuentabanco;
use App\Kardex;
use App\Tipodocumento;
use App\Movimiento;
use App\Detallemovimiento;
use App\Person;
use App\Servicio;
use App\Conceptopago;
use App\Caja;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Detallemovcaja;
use App\Librerias\EnLetras;
use Illuminate\Support\Facades\Auth;
use Excel;

class CuentabancariaController extends Controller
{
    protected $folderview      = 'app.cuentabancaria';
    protected $tituloAdmin     = 'Cuenta Bancaria';
    protected $tituloRegistrar = 'Registrar en Cuenta Bancaria';
    protected $tituloModificar = 'Modificar en Cuenta Bancaria';
    protected $tituloCobrar = 'Cobrar Cuenta';
    protected $tituloEliminar  = 'Eliminar Registro';
    protected $rutas           = array('create' => 'cuentabancaria.create', 
            'edit'   => 'cuentabancaria.edit', 
            'delete' => 'cuentabancaria.eliminar',
            'search' => 'cuentabancaria.buscar',
            'index'  => 'cuentabancaria.index',
            'pdfListar'  => 'cuentabancaria.pdfListar',
            'cobrar' => 'cuentabancaria.cobrar',
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Cuentabancaria';
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->where('conceptopago.nombre','like','%'.$request->input('concepto').'%')
                            ->where('movimiento.tipomovimiento_id','=',12)
                            ->where('movimiento.Cuentabanco_id','=',$request->input('cuenta'));
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
        }        
        if($request->input('numero')!=""){
            $resultado = $resultado->where(DB::raw('concat(movimiento.voucher,\' \',movimiento.dni)'),'LIKE','%'.$request->input('numero').'%');
        }    
        if($request->input('persona')!=""){
            $resultado = $resultado->where(DB::raw('case when paciente.bussinesname is null then concat(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.bussinesname end'),'LIKE','%'.strtoupper($request->input('persona')).'%');
        }    
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }
        if($request->input('formapago')!=""){
            $resultado = $resultado->where('movimiento.numeroficha','like',$request->input('formapago'));
        }
        if($request->input('tipodocumento_id')!=""){
            if($request->input('tipodocumento_id')=="0"){
                $resultado = $resultado->whereIn('movimiento.conceptopago_id',[102,103,104,105]);   
            }else{
                $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento_id'));   
            }
        }

        $resultado        = $resultado->select('movimiento.*')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.voucher', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Ope', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Concepto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Forma Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Ingreso', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Egreso', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doc', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '4');
        
        $user = Auth::user();
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_cobrar    = $this->tituloCobrar;
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'titulo_cobrar', 'ruta', 'user'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    public function index()
    {
        $entidad          = 'Cuentabancaria';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        $cboCuenta = array();
        $rs = Cuentabanco::orderBy('descripcion','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboCuenta = $cboCuenta + array($value->id => $value->descripcion.' - '.$value->banco->descripcion);
        }
        $cboSituacion = array(''=> 'Todos','P'=>'Pendiente','C'=>'Cobrado');
        $cboTipoDocumento = array('' => 'Todos','21' => 'Egreso', '20' => 'Ingreso', '0' => 'Dep. - Abono');
        $cboFormaPago = array(''=> 'Todos','Transferencia' => 'Transferencia', 'Cheque' => 'Cheque', 'Deposito Cta' => 'Deposito Cta', 'Giro' => 'Giro', 'Tarjeta Debito' => 'Tarjeta Debito', 'Tarjeta Credito' => 'Tarjeta Credito', 'Letra Cambio' => 'Letra Cambio', 'Otros Medios Pago' => 'Otros Medios Pago', 'Orden Pago' => 'Orden Pago');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user','cboCuenta','cboSituacion','cboFormaPago', 'cboTipoDocumento'));
    }

    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Cuentabancaria';
        $cuentabancaria = null;
        $formData            = array('cuentabancaria.store');
        $cboCuenta = array();
        $rs = Cuentabanco::orderBy('descripcion','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboCuenta = $cboCuenta + array($value->id => $value->descripcion.' - '.$value->banco->descripcion);
        }
        $cboConcepto = array();
        $rs = Conceptopago::where('tipo','like','E')->where('id','<>',2)->where('id','<>',13)->where('id','<>',24)->where('id','<>',25)->where('id','<>',26)->where('id','<>',20)->where('id','<>',8)->where('id','<>',18)->where('id','<>',16)->where('id','<>',14)->where('id','<>',31)->where('tesoreria','like','S')->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboConcepto = $cboConcepto + array($value->id => $value->nombre);
        }
        $cboFormaPago = array('Transferencia' => 'Transferencia', 'Cheque' => 'Cheque', 'Deposito Cta' => 'Deposito Cta', 'Giro' => 'Giro', 'Tarjeta Debito' => 'Tarjeta Debito', 'Tarjeta Credito' => 'Tarjeta Credito', 'Letra Cambio' => 'Letra Cambio', 'Otros Medios Pago' => 'Otros Medios Pago', 'Orden Pago' => 'Orden Pago');
        $cboTipoDocumento = array('21' => 'Egreso', '20' => 'Ingreso');
        $cboTipo = array('RH' => 'RH', 'FT' => 'FT', 'BV' => 'BV', 'TK' => 'TK', 'VR' => 'VR', 'RC' => 'RC', 'NA' => 'NA', 'ND' => 'ND');
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('cuentabancaria', 'formData', 'entidad', 'boton', 'listar', 'cboCuenta', 'cboFormaPago', 'cboTipoDocumento', 'cboTipo', 'cboConcepto'));
    }

    public function store(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                'persona'                  => 'required',
                'total'          => 'required',
                'voucher'          => 'required',
                );
        $mensajes = array(
            'persona.required'         => 'Debe seleccionar una persona',
            'total.required'         => 'Debe ingresar total',
            'voucher.required'         => 'Debe indicar un voucher',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       
        
        $user = Auth::user();
        $error = DB::transaction(function() use($request,$user){
            $movimiento       = new Movimiento();
            $movimiento->fecha = $request->input('fecha');
            $person_id = $request->input('person_id');
            $movimiento->persona_id = $person_id;
            $numero              = Movimiento::NumeroSigueTesoreria(12,$request->input('tipodocumento_id'),0);//movimiento caja y documento ingreso
            $movimiento->numero= $numero;
            $movimiento->responsable_id=$user->person_id;
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->cuentabanco_id=$request->input('cuentabanco_id');     
            $movimiento->tipomovimiento_id=12;
            $movimiento->tipodocumento_id=$request->input('tipodocumento_id');
            $movimiento->conceptopago_id=$request->input('conceptopago_id');;
            $movimiento->comentario=$request->input('comentario');
            $movimiento->caja_id=$request->input('caja_id');
            if($request->input('formapago')=='Cheque'){
                $movimiento->situacion='P';
            }else{
                $movimiento->situacion='C';
            }
            $movimiento->listapago=$request->input('lista');//Lista de pagos para transferencia y pago tambien
            $movimiento->voucher=$request->input('rh');
            $movimiento->formapago=$request->input('tipo');
            $movimiento->dni=$request->input('voucher');
            $movimiento->numeroficha=$request->input('formapago');
            $movimiento->nombrepaciente=$request->input('entregado');

            $movimiento->save();
            //$idref=$movimiento->movimiento_id;
            $idref = $movimiento->id;

            $lista="";
            $arr=explode(",",$request->input('lista'));
            if($request->input('lista')!=""){
                for($c=0;$c<count($arr);$c++){
                    $Detalle = Movimiento::find($arr[$c]);
                    if(($Detalle->totalpagado+$request->input('txtPago').$arr[$c])==$Detalle->total){
                        $Detalle->estadopago='P';//pagado;
                    }
                    $Detalle->movimientodescarga_id=$idref;
                    $Detalle->totalpagado=$Detalle->totalpagado + $request->input('txtPago'.$arr[$c]);
                    $Detalle->save();
                    $lista.=$request->input('txtPago'.$arr[$c])."@";
                }
            }
            $movimiento->mensajesunat=substr($lista,0,strlen($lista)-1);
            $movimiento->save();
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
        $cuentabancaria = Movimiento::find($id);
        $entidad             = 'Cuentabancaria';
        $formData            = array('cuentabancaria.update', $id);
        $cboCuenta = array();
        $rs = Cuentabanco::orderBy('descripcion','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboCuenta = $cboCuenta + array($value->id => $value->descripcion.' - '.$value->banco->descripcion);
        }
        $cboConcepto = array();
        $rs = Conceptopago::where('tipo','like','E')->where('id','<>',2)->where('id','<>',13)->where('id','<>',24)->where('id','<>',25)->where('id','<>',26)->where('id','<>',20)->where('id','<>',8)->where('id','<>',18)->where('id','<>',16)->where('id','<>',14)->where('id','<>',31)->where('tesoreria','like','S')->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboConcepto = $cboConcepto + array($value->id => $value->nombre);
        }
        $cboFormaPago = array('Transferencia' => 'Transferencia', 'Cheque' => 'Cheque', 'Deposito Cta' => 'Deposito Cta', 'Giro' => 'Giro', 'Tarjeta Debito' => 'Tarjeta Debito', 'Tarjeta Credito' => 'Tarjeta Credito', 'Letra Cambio' => 'Letra Cambio', 'Otros Medios Pago' => 'Otros Medios Pago', 'Orden Pago' => 'Orden Pago');
        $cboTipoDocumento = array('20' => 'Ingreso', '21' => 'Egreso');
        $cboTipo = array('RH' => 'RH', 'FT' => 'FT', 'BV' => 'BV', 'TK' => 'TK', 'VR' => 'VR', 'RC' => 'RC', 'NA' => 'NA', 'ND' => 'ND');
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('cuentabancaria', 'formData', 'entidad', 'boton', 'listar', 'cboCuenta', 'cboFormaPago', 'cboTipoDocumento', 'cboTipo', 'cboConcepto'));
    }

    public function update(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id,$user,$request){
            $movimiento = Movimiento::find($id);
            $movimiento->fecha = $request->input('fecha');
            $person_id = $request->input('person_id');
            $movimiento->persona_id = $person_id;
            $movimiento->responsable_id=$user->person_id;
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->cuentabanco_id=$request->input('cuentabanco_id');     
            $movimiento->tipomovimiento_id=12;
            $movimiento->tipodocumento_id=$request->input('tipodocumento_id');
            $movimiento->conceptopago_id=$request->input('conceptopago_id');;
            $movimiento->comentario=$request->input('comentario');
            $movimiento->caja_id=$request->input('caja_id');
            //$movimiento->situacion='P';
            $movimiento->voucher=$request->input('rh');
            $movimiento->formapago=$request->input('tipo');
            $movimiento->dni=$request->input('voucher');
            $movimiento->numeroficha=$request->input('formapago');
            $movimiento->nombrepaciente=$request->input('entregado');
            $movimiento->save();
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
            $movimiento = Movimiento::find($id);
            $movimiento->delete();
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
        $entidad  = 'Cuentabancaria';
        $mensaje = '¿Desea eliminar el movimiento "'.$modelo->dni.'" ? <br><br>';
        $formData = array('route' => array('cuentabancaria.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar','mensaje'));
    }
    
    public function validarDNI(Request $request)
    {
        $dni = $request->input("dni");
        $entidad    = 'Person';
        $mdlPerson = new Person();
        $resultado = Person::where('dni','LIKE',$dni);
        $value     = $resultado->first();
        if(count($value)>0){
            $objVenta = new Venta();
            $list2       = Venta::where('person_id','=',$value->id)->first();
            if(count($list2)>0){//SI TIENE Venta
                $data[] = array(
                            'apellidopaterno' => $value->apellidopaterno,
                            'apellidomaterno' => $value->apellidomaterno,
                            'nombres' => $value->nombres,
                            'telefono' => $value->telefono,
                            'direccion' => $value->direccion,
                            'id'    => $value->id,
                            'msg' => 'N',
                        );
            }else{//NO TIENE Venta PERO SI ESTA REGISTRADO LA PERSONA COMO PROVEEDOR O PERSONAL
                $data[] = array(
                            'apellidopaterno' => $value->apellidopaterno,
                            'apellidomaterno' => $value->apellidomaterno,
                            'nombres' => $value->nombres,
                            'telefono' => $value->telefono,
                            'direccion' => $value->direccion,
                            'id'    => $value->id,
                            'msg' => 'S',
                            'modo'=> 'Registrado',
                        );                
            }
        }else{
            $data[] = array('msg'=>'S','modo'=>'Nada');
        }
        return json_encode($data);
    }

    public function pdfComprobante(Request $request){
        $entidad          = 'Ventaadmision';
        $id            = Libreria::getParam($request->input('id'),'');
        //$rst              = Movimiento::find($idref);
        //$id = $rst->movimiento_id;
        $resultado        = Movimiento::join('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->where('movimiento.id', '=', $id);
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
                $pdf::Cell(60,7,("RUC N° 20480082673"),'RTL',0,'C');
                $pdf::Ln();
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Cell(60,7,utf8_encode($value->tipodocumento_id=='4'?"FACTURA":"BOLETA"),'RL',0,'C');
                $pdf::Ln();
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Cell(60,7,("ELECTRÓNICA"),'RL',0,'C');
                $pdf::Ln();
                $pdf::Cell(130,7,"",0,0,'C');
                if($value->tipodocumento_id=="4"){
                    $abreviatura="F";
                    $dni=$value->persona->ruc;
                    $subtotal=number_format($value->total/1.18,2,'.','');
                    $igv=number_format($value->total - $subtotal,2,'.','');
                }else{
                    $abreviatura="B";
                    $subtotal=number_format($value->subtotal,2,'.','');
                    $igv=number_format($value->igv,2,'.','');
                    if(strlen($value->persona->dni)<>8){
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
                $pdf::Cell(37,6,("Nombre / Razón Social: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(110,6,(trim($value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,utf8_encode($abreviatura=="F"?"RUC":"DNI".": "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,utf8_encode($dni),0,0,'L');
                $pdf::Ln();
                if($value->tipodocumento_id=="4" && $value->id!=86410 && $value->id!=86407){
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(37,6,utf8_encode("Paciente: "),0,0,'L');
                    $pdf::SetFont('helvetica','',9);
                    $ticket = Movimiento::find($value->movimiento_id);
                    $pdf::Cell(110,6,(trim($ticket->persona->apellidopaterno." ".$ticket->persona->apellidomaterno." ".$ticket->persona->nombres)),0,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,("Dirección: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(110,6,(trim($value->persona->direccion)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,("Fecha de emisión: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,date("d/m/Y",strtotime($value->fecha)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,utf8_encode("Moneda: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(110,6,(trim('PEN - Sol')),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,("Condicion: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $value2=Movimiento::find($value->movimiento_id);
                if($value2->tarjeta!=""){
                    $pdf::Cell(37,6,trim($value2->tarjeta." - ".$value2->tipotarjeta),0,0,'L');
                }elseif($value2->situacion=='B'){
                    $pdf::Cell(37,6,trim('PENDIENTE'),0,0,'L');
                }else{
                    $pdf::Cell(37,6,trim('CONTADO'),0,0,'L');
                }

                $pdf::Ln();
                if($value->id!=86410 && $value->id!=86407){
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(37,6,utf8_encode("Convenio: "),0,0,'L');
                    $pdf::SetFont('helvetica','',9);
                    $pdf::Cell(110,6,(trim($value2->plan->nombre)),0,0,'L');
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(30,6,("Historia: "),0,0,'L');
                    $mov=Movimiento::find($value->movimiento_id);
                    $historia = Historia::where('person_id','=',$mov->persona_id)->first();
                    if(count($historia)>0){
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Cell(30,6,($historia->numero),0,0,'L');
                    }
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(10,7,("Item"),1,0,'C');
                $pdf::Cell(13,7,("Código"),1,0,'C');
                $pdf::Cell(68,7,("Descripción"),1,0,'C');
                $pdf::Cell(10,7,("Und."),1,0,'C');
                $pdf::Cell(15,7,("Cantidad"),1,0,'C');
                $pdf::Cell(20,7,("V. Unitario"),1,0,'C');
                $pdf::Cell(20,7,("P. Unitario"),1,0,'C');
                $pdf::Cell(20,7,("Descuento"),1,0,'C');
                $pdf::Cell(20,7,("Sub Total"),1,0,'C');
                $pdf::Ln();
                $resultado        = Detallemovcaja::leftjoin('servicio', 'servicio.id', '=', 'detallemovcaja.servicio_id')
                            ->where('detallemovcaja.movimiento_id', '=', $value->movimiento_id)
                            ->select('detallemovcaja.*');
                $lista2            = $resultado->get();
                $c=0;
                foreach($lista2 as $key2 => $v){$c=$c+1;
                    $pdf::SetFont('helvetica','',7.5);
                    $pdf::Cell(10,7,$c,1,0,'C');
                    if($v->servicio_id>0){
                        if($v->servicio->tipopago=="Convenio"){
                            $codigo=$v->servicio->tarifario->codigo;
                            $nombre=$v->servicio->tarifario->nombre;    
                        }else{
                            $codigo="-";
                            if($v->servicio_id>0){
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
                $valor=$letras->ValorEnLetras($value->total, "SOLES" );//letras
                
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
                $pdf::Cell(0,5,('Autorizado a ser emisor electrónico mediante R.I. SUNAT Nº 0340050004781'),0,0,'L');
                $pdf::Ln();
                $pdf::Cell(0,5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(160,5,('Representación Impresa de la Factura Electrónica, consulte en https://sfe.bizlinks.com.pe'),0,0,'L');
                $pdf::Cell(0,5,$value->created_at,0,0,'R');
                $pdf::Ln();
                $pdf::Output('Comprobante.pdf');
            }
        }
    }
    
   	public function pdfListar(Request $request){
        $entidad          = 'Cuentabancaria';
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->where('conceptopago.nombre','like','%'.$request->input('concepto').'%')
                            ->where('movimiento.tipomovimiento_id','=',12)
                            ->where('movimiento.Cuentabanco_id','=',$request->input('cuenta_id'));
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
        }        
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }
        if($request->input('formapago')!=""){
            $resultado = $resultado->where('movimiento.numeroficha','like',$request->input('formapago'));
        }
        if($request->input('tipodocumento_id')!=""){
            if($request->input('tipodocumento_id')=="0"){
                $resultado = $resultado->whereIn('movimiento.conceptopago_id',[102,103,104,105]);   
            }else{
                $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento_id'));   
            }
        }
        if($request->input('persona')!=""){
            $resultado = $resultado->where(DB::raw('case when paciente.bussinesname is null then concat(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.bussinesname end'),'LIKE','%'.strtoupper($request->input('persona')).'%');
        } 

        $resultado        = $resultado->select('movimiento.*')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.voucher', 'ASC')->orderBy('movimiento.numeroficha');
        $lista            = $resultado->get();
        if (count($lista) > 0) {            
            $pdf = new TCPDF();
            $pdf::SetTitle('Cuenta Bancaria');
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',11);
            $fechainicial=date("d/m/Y",strtotime($request->input('fechainicial')));
            $fechafinal=date("d/m/Y",strtotime($request->input('fechafinal')));
            $pdf::Cell(0,10,utf8_decode("REPORTE DE CUENTA BANCARIA DEL ".$fechainicial." AL ".$fechafinal),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            //TIPO|NRO|PERSONA|CONCEPTO|IMPORTE|CONDICION(EGRESADO Y COBRADO SOLO EN CHEQUES)|FECHA|FORMA DE PAGO|
            $pdf::Cell(20,6,utf8_decode("FORMA PAGO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("NRO OPE"),1,0,'C');
            $pdf::Cell(10,6,utf8_decode("TIPO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(70,6,utf8_decode("PERSONA"),1,0,'C');
            $pdf::Cell(70,6,utf8_decode("CONCEPTO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("IMPORTE"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("CONDICION"),1,0,'C');
            $pdf::Cell(22,6,utf8_decode("FECHA COBRO"),1,0,'C');
            //$pdf::Cell(22,6,utf8_decode("FORMA PAGO"),1,0,'C');
            $pdf::Ln();
            $formapago='';$total=0;$totalg=0;
            foreach ($lista as $key => $value){
                if($formapago!=$value->fecha){
                    if($formapago!=""){
                        $pdf::SetFont('helvetica','B',7);
                        $pdf::Cell(200,5,utf8_decode('TOTAL'),1,0,'R');
                        $pdf::Cell(15,5,number_format($total,2,'.',''),1,0,'C');
                        $pdf::Ln();    
                    }
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(257,5,date("d/m/Y",strtotime($value->fecha)),1,0,'L');
                    $pdf::Ln();
                    $totalg=$totalg+$total;
                    $total=0;
                    $formapago=$value->fecha;
                }
                $pdf::SetFont('helvetica','',7);
                $pdf::Cell(20,5,$value->numeroficha,1,0,'L');
                $pdf::Cell(15,5,utf8_decode($value->dni),1,0,'L');
                $pdf::Cell(10,5,utf8_decode($value->formapago),1,0,'L');
                $pdf::Cell(15,5,utf8_decode($value->voucher),1,0,'C');
                $persona = ($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname);
                if(strlen($persona)>25){
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();
                    $pdf::Multicell(70,2,$persona,0,'L');
                    $pdf::SetXY($x,$y);
                    $pdf::Cell(70,5,'',1,0,'L');
                }else{
                    $pdf::Cell(70,5,$persona,1,0,'L');
                }
                $pdf::Cell(70,5,($value->conceptopago->nombre),1,0,'L');
                $pdf::Cell(15,5,number_format($value->total,2,'.',''),1,0,'C');
                $pdf::Cell(20,5,utf8_decode($value->situacion=='P'?'PENDIENTE':'COBRADO'),1,0,'C');
                if($value->fechaentrega!="")
                    $pdf::Cell(22,5,date("d/m/Y",strtotime($value->fechaentrega)),1,0,'L');
                else
                    $pdf::Cell(22,5,'',1,0,'L');
                $total = $total + number_format($value->total,2,'.','');
                //$pdf::Cell(22,5,$value->numeroficha,1,0,'L');
                $pdf::Ln();
            }
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(200,5,utf8_decode('TOTAL'),1,0,'R');
            $pdf::Cell(15,5,number_format($total,2,'.',''),1,0,'C');
            $pdf::Ln(); 
            $totalg=$totalg+$total;
            $pdf::Ln(); 
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(200,5,utf8_decode('TOTAL GENERAL'),1,0,'R');
            $pdf::Cell(15,5,number_format($totalg,2,'.',''),1,0,'C');
            $pdf::Ln(); 

            $pdf::Output('ListaVenta.pdf');
        }
    }

    public function pdfListarResumen(Request $request){
        $entidad          = 'Cuentabancaria';
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','movimiento.conceptopago_id','=','conceptopago.id')
                            ->where('movimiento.tipomovimiento_id','=',12)
                            ->where('movimiento.Cuentabanco_id','=',$request->input('cuenta_id'))
                            ->where('movimiento.situacion','like','C');
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where(DB::raw('case when trim(movimiento.numeroficha) like \'Cheque\' and movimiento.tipodocumento_id=21 then movimiento.fechaentrega else movimiento.fecha end'),'>=',$request->input('fechainicial'));
            //$resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where(DB::raw('case when trim(movimiento.numeroficha) like \'Cheque\' and movimiento.tipodocumento_id=21 then movimiento.fechaentrega else movimiento.fecha end'),'<=',$request->input('fechafinal'));
            //$resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
        }        
        if($request->input('persona')!=""){
            $resultado = $resultado->where(DB::raw('case when paciente.bussinesname is null then concat(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.bussinesname end'),'LIKE','%'.strtoupper($request->input('persona')).'%');
        } 

        $resultado        = $resultado->select('movimiento.*','conceptopago.tipo as tipo2')->orderBy('conceptopago.tipo', 'desc')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numeroficha');
        $lista            = $resultado->get();
        if (count($lista) > 0) {            
            $pdf = new TCPDF();
            $pdf::SetTitle('Cuenta Bancaria');
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',11);
            $fechainicial=date("d/m/Y",strtotime($request->input('fechainicial')));
            $fechafinal=date("d/m/Y",strtotime($request->input('fechafinal')));
            $pdf::Cell(0,10,utf8_decode("REPORTE DE CUENTA BANCARIA DEL ".$fechainicial." AL ".$fechafinal),0,0,'C');
            $pdf::Ln();
            //TIPO|NRO|PERSONA|CONCEPTO|IMPORTE|CONDICION(EGRESADO Y COBRADO SOLO EN CHEQUES)|FECHA|FORMA DE PAGO|
            $fecha='';$total=0;$totalg=0;$ingresos=0;$egresos=0;$saldo=158514.28;
            if($request->input('fechainicial')!="2018-06-01"){
                $resul=Movimiento::join('conceptopago','movimiento.conceptopago_id','=','conceptopago.id')
                            ->where('movimiento.tipomovimiento_id','=',12)
                            ->where('movimiento.Cuentabanco_id','=',$request->input('cuenta_id'))
                            ->where('movimiento.situacion','like','C')
                            ->where(DB::raw('case when trim(movimiento.numeroficha) like \'Cheque\' and movimiento.tipodocumento_id=21 then movimiento.fechaentrega else movimiento.fecha end'),'<',$request->input('fechainicial'))
                            ->where(DB::raw('case when trim(movimiento.numeroficha) like \'Cheque\' and movimiento.tipodocumento_id=21 then movimiento.fechaentrega else movimiento.fecha end'),'>=','2018-06-01')
                            ->select(DB::raw('sum(case when conceptopago.tipo like \'I\' then movimiento.total else movimiento.total*(-1) end) as saldo'))
                            ->first();
                            //print_r($resul->saldo);
                            if($request->input('cuenta_id')==1) $saldo=$saldo+$resul->saldo;
                            //$saldo=39094.87;
            }
            foreach ($lista as $key => $value){
                if($fecha!=$value->fecha){
                    if($fecha!=""){
                        $pdf::SetFont('helvetica','B',7);
                        $pdf::Cell(185,5,utf8_decode('TOTAL'),1,0,'R');
                        $pdf::Cell(15,5,number_format($total,2,'.',''),1,0,'C');
                        $pdf::Ln();    
                    }
                    if($ingresos==0 && $value->tipo2=="I"){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(275,5,"INGRESOS",0,0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(20,6,utf8_decode("FORMA PAGO"),1,0,'C');
                        $pdf::Cell(15,6,utf8_decode("NRO OPE"),1,0,'C');
                        $pdf::Cell(10,6,utf8_decode("TIPO"),1,0,'C');
                        //$pdf::Cell(15,6,utf8_decode("NRO"),1,0,'C');
                        $pdf::Cell(70,6,utf8_decode("PERSONA"),1,0,'C');
                        $pdf::Cell(70,6,utf8_decode("CONCEPTO"),1,0,'C');
                        $pdf::Cell(15,6,utf8_decode("IMPORTE"),1,0,'C');
                        $pdf::Cell(75,6,utf8_decode("DETALLE"),1,0,'C');
                        $pdf::Ln();
                    }
                    if($egresos==0 && $value->tipo2=="E"){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(185,5,utf8_decode('TOTAL INGRESOS'),0,0,'R');
                        $pdf::Cell(15,5,number_format($ingresos,2,'.',''),0,0,'C');
                        $pdf::Ln(); 
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(275,5,"EGRESOS",0,0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(20,6,utf8_decode("FORMA PAGO"),1,0,'C');
                        $pdf::Cell(15,6,utf8_decode("NRO OPE"),1,0,'C');
                        $pdf::Cell(10,6,utf8_decode("TIPO"),1,0,'C');
                        //$pdf::Cell(15,6,utf8_decode("NRO"),1,0,'C');
                        $pdf::Cell(70,6,utf8_decode("PERSONA"),1,0,'C');
                        $pdf::Cell(70,6,utf8_decode("CONCEPTO"),1,0,'C');
                        $pdf::Cell(15,6,utf8_decode("IMPORTE"),1,0,'C');
                        $pdf::Cell(75,6,utf8_decode("DETALLE"),1,0,'C');
                        $pdf::Ln();
                    }
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(275,5,date("d/m/Y",strtotime($value->fecha)),1,0,'L');
                    $pdf::Ln();
                    $totalg=$totalg+$total;
                    $total=0;
                    $fecha=$value->fecha;
                }
                $pdf::SetFont('helvetica','',7);
                $pdf::Cell(20,5,($value->numeroficha),1,0,'L');
                $pdf::Cell(15,5,utf8_decode($value->dni),1,0,'L');
                $pdf::Cell(10,5,utf8_decode($value->formapago),1,0,'L');
                //$pdf::Cell(15,5,utf8_decode($value->voucher),1,0,'C');
                $persona = ($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname);
                if(strlen($persona)>25){
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();
                    $pdf::Multicell(70,2,$persona,0,'L');
                    $pdf::SetXY($x,$y);
                    $pdf::Cell(70,5,'',1,0,'L');
                }else{
                    $pdf::Cell(70,5,$persona,1,0,'L');
                }
                $pdf::Cell(70,5,($value->conceptopago->nombre),1,0,'L');
                $pdf::Cell(15,5,number_format($value->total,2,'.',''),1,0,'C');
                $pdf::Cell(75,5,($value->comentario),1,0,'L');
                $total = $total + number_format($value->total,2,'.','');
                if($value->conceptopago->tipo=="I"){
                    $ingresos=$ingresos + $value->total;
                }else{
                    $egresos=$egresos + $value->total;
                }
                //$pdf::Cell(22,5,$value->numeroficha,1,0,'L');
                $pdf::Ln();
            }
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(185,5,utf8_decode('TOTAL'),1,0,'R');
            $pdf::Cell(15,5,number_format($total,2,'.',''),1,0,'C');
            $pdf::Ln(); 
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(200,5,utf8_decode('TOTAL EGRESOS'),0,0,'R');
            $pdf::Cell(15,5,number_format($egresos,2,'.',''),0,0,'C');
            $pdf::Ln();

            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(50,7,utf8_decode("RESUMEN DE CUENTA"),1,0,'C');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("SALDO INICIAL :"),1,0,'L');
            $pdf::Cell(20,7,number_format($saldo,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("INGRESOS :"),1,0,'L');
            $pdf::Cell(20,7,number_format($ingresos,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("EGRESOS :"),1,0,'L');
            $pdf::Cell(20,7,number_format($egresos,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("SALDO :"),1,0,'L');
            $pdf::Cell(20,7,number_format($ingresos + $saldo - $egresos,2,'.',''),1,0,'R');
            $pdf::Ln();

            $pdf::Output('ListaVenta.pdf');
        }
    }

    public function pdfRecibo(Request $request){
        $lista = Movimiento::where('id','=',$request->input('id'))->first();
                    
        $pdf = new TCPDF();
        $pdf::SetTitle('Recibo de Egreso');
        $pdf::AddPage();

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
        if(trim($lista->listapago)==""){
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("DOCUMENTO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            if($lista->numeroficha=='Cheque'){
                $pdf::Cell(100,7,'CH '.utf8_decode($lista->dni.'          '.$lista->formapago.' '.$lista->voucher) ,0,0,'L');
            }else{
                $pdf::Cell(100,7,utf8_decode($lista->formapago.' '.$lista->voucher),0,0,'L');
            }
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
        $pdf::Cell(30,7,utf8_decode("ENTREGADO A :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(110,7,($lista->nombrepaciente),0,0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(30,7,"CONTABILIDAD",0,0,'C');
        $pdf::Cell(20,7,"",0,0,'L');
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(30,7,utf8_decode(""),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(30,7,utf8_decode(''),0,0,'L');
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
        if(trim($lista->listapago)==""){
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("DOCUMENTO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            if($lista->numeroficha=='Cheque'){
                $pdf::Cell(100,7,'CH '.utf8_decode($lista->dni.'          '.$lista->formapago.' '.$lista->voucher) ,0,0,'L');
            }else{
                $pdf::Cell(100,7,utf8_decode($lista->formapago.' '.$lista->voucher),0,0,'L');
            }
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
        $pdf::Cell(30,7,utf8_decode("ENTREGADO A :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(110,7,($lista->nombrepaciente),0,0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(30,7,"TESORERIA",0,0,'C');
        $pdf::Cell(20,7,"",0,0,'L');
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(30,7,utf8_decode(" "),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(30,7,utf8_decode(''),0,0,'L');
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
        if(trim($lista->listapago)==""){
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("DOCUMENTO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            if($lista->numeroficha=='Cheque'){
                $pdf::Cell(100,7,'CH '.utf8_decode($lista->dni.'          '.$lista->formapago.' '.$lista->voucher) ,0,0,'L');
            }else{
                $pdf::Cell(100,7,utf8_decode($lista->formapago.' '.$lista->voucher),0,0,'L');
            }
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
        $pdf::Cell(30,7,utf8_decode("ENTREGADO A :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(110,7,($lista->nombrepaciente),0,0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(30,7,"USUARIO",0,0,'C');
        $pdf::Cell(20,7,"",0,0,'L');
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(30,7,utf8_decode(""),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(30,7,utf8_decode(''),0,0,'L');
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(30,7,"",0,0,'L');
        $pdf::Cell(40,7,utf8_decode("RECIBI CONFORME"),'T',0,'C');            
        $pdf::SetFont('helvetica','',9);
            
        $pdf::Output('ReciboCaja.pdf');
        
    }

    public function cobrar($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'SI');
        $cuentabancaria = Movimiento::find($id);
        $entidad             = 'Cuentabancaria';
        $formData            = array('cuentabancaria.pagar', $id);
        $formData            = array('route' => $formData, 'method' => 'PAGAR', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Cobrar';
        return view($this->folderview.'.cobrar')->with(compact('cuentabancaria', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function pagar(Request $request)
    {
        $id = $request->input('id');
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id,$user,$request){
            $movimiento = Movimiento::find($id);
            $movimiento->fechaentrega = $request->input('fecha');
            $movimiento->situacion='C';
            $movimiento->usuarioentrega_id=$user->person_id;
            $movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function generarConcepto(Request $request)
    {
        $tipodoc = $request->input("tipodocumento_id");
        if($tipodoc==20){
            $rst = Conceptopago::where('tipo','like','I')->where('id','<>',1)->where('id','<>',6)->where('id','<>',15)->where('id','<>',17)->where('id','<>',19)->where('id','<>',21)->where('id','<>',23)->where('id','<>',31)->where('id','<>',3)->where('id','<>',32)->where('id','<>',10)->where('id','<>',33)->where('tesoreria','like','S')->orderBy('nombre','ASC')->get();
        }else{
            $rst = Conceptopago::where('tipo','like','E')->where('id','<>',2)->where('id','<>',13)->where('id','<>',24)->where('id','<>',25)->where('id','<>',26)->where('id','<>',20)->where('id','<>',8)->where('id','<>',18)->where('id','<>',16)->where('id','<>',14)->where('id','<>',31)->where('tesoreria','like','S')->orderBy('nombre','ASC')->get();
        }
        $cbo="";
        foreach ($rst as $key => $value) {
            $cbo = $cbo."<option value='".$value->id."'>".$value->nombre."</option>";
        }
         
        return $cbo;
    }    

    public function cuentasporpagar(Request $request)
    {
        $user = Auth::user();
        $proveedor = $request->input('busqueda');
        $resultado        = Movimiento::leftjoin('person as proveedor', 'proveedor.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('tipodocumento as td','td.id','=','movimiento.tipodocumento_id')
                            ->whereiN('movimiento.tipomovimiento_id', [3,11,13])
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
            $registro.="<tr onclick=\"agregarDoc($value->id,'".trim($numero)."','".date("d/m/Y",strtotime($value->fecha))."',".number_format($value->total-$value->totalpagado,2,'.','').")\">";
            $registro.="<td align='center' >".date("d/m/Y",strtotime($value->fecha))."</td>";
            $registro.="<td align='center'>".trim($numero)."</td>";
            $registro.="<td align='right'>".number_format($value->total-$value->totalpagado,2,'.','')."</td>";
            $registro.="</tr>";
        }
        $registro.="</tbody></table>";
        echo $registro;
    }

}
