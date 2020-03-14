<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Convenio;
use App\Movimiento;
use App\Detallemovcaja;
use App\Detallehojacosto;
use App\Person;
use App\Numeracion;
use App\Caja;
use App\Tiposervicio;
use App\Servicio;
use App\Plan;
use App\Detalleplan;
use App\Cita;
use App\Librerias\Libreria;
use App\Librerias\EnLetras;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    protected $folderview      = 'app.ticket';
    protected $tituloAdmin     = 'Ticket';
    protected $tituloRegistrar = 'Registrar Ticket';
    protected $tituloModificar = 'Modificar Ticket';
    protected $tituloEliminar  = 'Eliminar Ticket';
    protected $rutas           = array('create' => 'ticket.create', 
            'edit'   => 'ticket.edit',
            'delete' => 'ticket.eliminar',
            'anular' => 'ticket.anular',
            'editarresponsable'   => 'ticket.editarresponsable',
            'search' => 'ticket.buscar',
            'index'  => 'ticket.index',
            'pdfListar'  => 'ticket.pdfListar',
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
        $entidad          = 'Ticket';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fecha            = Libreria::getParam($request->input('fecha'));
        $user = Auth::user();
        if($request->input('usuario')=="Todos"){
            $responsable_id=0;
        }else{
            $responsable_id=$user->person_id;
        }
        $resultado        = Movimiento::
                            where('movimiento.numero','LIKE','%'.$numero.'%')->where('movimiento.tipodocumento_id','=','1');
        if($paciente!=""){
            $resultado = $resultado->where('movimiento.nombrepaciente', 'LIKE', '%'.strtoupper($paciente).'%');
        }
        if($fecha!=""){
            $resultado = $resultado->where('movimiento.fecha', '=', ''.$fecha.'');
        }
        if($responsable_id>0){
            $resultado = $resultado->where('movimiento.responsable_id', '=', $responsable_id);   
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('nombrepaciente as paciente'),'movimiento.nombreresponsable as responsable')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        $lista            = $resultado->get();

        // dd($lista);

        // foreach ($lista as $key2 => $value2) {
        //     // dd($value2);

        //     $resultado        = Detallemovcaja::leftjoin('servicio', 'servicio.id', '=', 'detallemovcaja.servicio_id')
        //                     ->where('detallemovcaja.movimiento_id', '=', $value2->id)
        //                     ->select('detallemovcaja.movimiento_id','servicio.nombre');
        //     $lista2            = $resultado->get();

        //     // dd($lista2);
        // }


        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Detalles de Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '4');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $ruta             = $this->rutas;
        //$conf = DB::connection('sqlsrv')->table('BL_CONFIGURATION')->get();
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'conf', 'user'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad','conf'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entidad          = 'Ticket';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $facturacion              = Libreria::getParam($request->input('facturacion'), 'NO');
        $entidad             = 'Ticket';
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
        if($facturacion=="SI"){
            $cboTipoPaciente     = array("Convenio" => "Convenio");
        }else{
            $cboTipoPaciente     = array("Convenio" => "Convenio", "Particular" => "Particular", "Hospital" => "Hospital");
        }
        $cboFormaPago     = array("Efectivo" => "Efectivo", "Tarjeta" => "Tarjeta");
        $cboTipoTarjeta    = array("VISA" => "VISA", "MASTER" => "MASTER");
        $cboTipoTarjeta2    = array("CREDITO" => "CREDITO", "DEBITO" => "DEBITO");        
        $cboCaja = array();
        $rs = Caja::where('nombre','<>','FARMACIA')->where('nombre','<>','TESORERIA')->where('nombre','<>','TESORERIA - FARMACIA')->orderBy('nombre','ASC')->get();
        $idcaja=0;//ECHO $request->ip();
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
        $cboCondicionPaciente = array("Consulta externa" => "Consulta externa", "Emergencia" => "Emergencia", "Hospitalizado" => "Hospitalizado", "UCI" => "UCI");

        $numeracion = Numeracion::where('tipomovimiento_id','=',1)->where('tipodocumento_id','=',1)->first();
        if(is_null($numeracion)){
            $numero = Movimiento::NumeroSigue(1);
            $numeracion = new Numeracion();
            $numeracion->serie=0;
            $numeracion->numero=$numero-1;
            $numeracion->tipomovimiento_id=1;
            $numeracion->tipodocumento_id=1;
            $numeracion->save();
        }else{
            $numero = $numeracion->numero + 1;
        }
        //$numero = Movimiento::NumeroSigue(1);

        $user = Auth::user();
        //|| $user->usertype_id == 5 || $user->usertype_id == 6
        if($serie=='8'){
            $cboTipoDocumento     = array("Boleta" => "Boleta");
        }else{
            $cboTipoDocumento     = array("Boleta" => "Boleta", "Factura" => "Factura");
        }

        //$numeroventa = Movimiento::NumeroSigue(4,5,$serie,'N');
        $numeracion = Numeracion::where('tipomovimiento_id','=',4)->where('tipodocumento_id','=',5)->where('serie','=',$serie)->first();
        if(is_null($numeracion)){
            $numeroventa = Movimiento::NumeroSigue(4,5,$serie,'N');
            $numeracion = new Numeracion();
            $numeracion->serie=$serie;
            $numeracion->numero=$numeroventa-1;
            $numeracion->tipomovimiento_id=4;
            $numeracion->tipodocumento_id=5;
            $numeracion->save();
        }else{
            $numeroventa = str_pad($numeracion->numero + 1,8,'0',STR_PAD_LEFT);
        }

        $serie='00'.$serie;
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('ticket', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente', 'cboConvenio', 'cboTipoDocumento', 'cboFormaPago', 'cboTipoTarjeta', 'cboTipoServicio', 'cboTipoTarjeta2', 'numero', 'cboCaja', 'numeroventa','serie','idcaja','facturacion','cboCondicionPaciente'));
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
                'fecha'                  => 'required',
                'numero'          => 'required',
                'paciente'          => 'required',
                'numero'          => 'required',
                'total'         => 'required',
                'plan'          => 'required',
                );
        $mensajes = array(
            'fecha.required'         => 'Debe seleccionar una fecha',
            'numero.required'         => 'El ticket debe tener un numero',
            'paciente.required'         => 'Debe seleccionar un paciente',
            'plan.required'         => 'Debe seleccionar un plan',
            'total.required'         => 'Debe agregar detalle al ticket',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       
        
        $validar = Movimiento::where('serie','=',$request->input('serieventa'))->where('manual','like','N')->where('tipodocumento_id','=',$request->input('tipodocumento')=="Boleta"?'5':'4')->where('numero','=',$request->input('numeroventa'))->first();
        if ($validar != null) {
            $dat[0]=array("respuesta"=>"ERROR","msg"=>"Nro de Comprobante ya registrado");
                return json_encode($dat);
        }

        $user = Auth::user();
        $dat=array();
        if($request->input('pagar')=='S'){
            $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$request->input('caja_id'))->orderBy('movimiento.id','DESC')->limit(1)->first();
            if(count($rst)==0){
                $conceptopago_id=2;
            }else{
                $conceptopago_id=$rst->conceptopago_id;
            }
            if($conceptopago_id==2){
                $dat[0]=array("respuesta"=>"ERROR","msg"=>"Caja cerrada");
                return json_encode($dat);
            }
        }
        $numero=($request->input('tipodocumento')=="Boleta"?"B":"F").str_pad($request->input('serieventa'),3,'0',STR_PAD_LEFT).'-'.$request->input('numeroventa');
        $numeronc="";//numero nota credito

        //BORRO BOLETA O FACUTRA REGISTRADA
        /* DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEHEADER where serieNumero="'.$numero.'"');
        DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEDETAIL where serieNumero="'.$numero.'"');
        DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEHEADER_ADD where serieNumero="'.$numero.'"');*/

        $error = DB::transaction(function() use($request,$user,&$dat,&$numeronc){
            $numeracion = Numeracion::where('tipomovimiento_id','=',1)->where('tipodocumento_id','=',1)->first();
            $numeracion->numero = $numeracion->numero + 1;
            $numeracion->save();

            $Ticket       = new Movimiento();
            $Ticket->fecha = $request->input('fecha');
            $Ticket->numero = $numeracion->numero;
            //$Ticket->numero = Movimiento::NumeroSigue(1);
            $Ticket->subtotal = $request->input('coa');//COASEGURO
            $Ticket->igv = $request->input('deducible');//DEDUCIBLE
            $Ticket->total = $request->input('total');
            $Ticket->tipomovimiento_id=1;//TICKET
            $Ticket->tipodocumento_id=1;//TICKET
            $Ticket->persona_id = $request->input('person_id');
            $Ticket->nombrepaciente = $request->input('paciente');
            $Ticket->plan_id = $request->input('plan_id');
            $Ticket->soat = $request->input('soat');
            $Ticket->sctr = $request->input('sctr');
            if($request->input('formapago')=="Tarjeta"){
                $Ticket->tarjeta=$request->input('tipotarjeta');//VISA/MASTER
                $Ticket->tipotarjeta=$request->input('tipotarjeta2');//DEBITO/CREDITO
                $Ticket->voucher=$request->input('nroref');
            }
            if($request->input('pagar')=="S"){
                $Ticket->situacion='C';//Pendiente => P / Cobrado => C / Boleteado => B
            }elseif($request->input('comprobante')=="S"){
                $Ticket->situacion='B';//Pendiente => P / Cobrado => C
                if($request->input('descuentopersonal')=="S"){
                    $Ticket->descuentoplanilla='S';
                    $Ticket->personal_id=$request->input('personal_id');
                }
            }else{
                $Ticket->situacion='P';//Pendiente => P / Cobrado => C
                if($request->input('descuentopersonal')=="S"){
                    $Ticket->descuentoplanilla='S';
                    $Ticket->personal_id=$request->input('personal_id');
                }
            }
            $Ticket->comentario = $request->input('formapago')."@".$request->input('tipodocumento');
            $Ticket->responsable_id=$user->person_id;
            $Ticket->nombreresponsable = $user->person->nombres;
            if($request->input('referido_id')>0){
                $Ticket->doctor_id=$request->input('referido_id');
            }
            $Ticket->condicionpaciente = $request->input('condicion');
            $Ticket->save();
            if(intval($request->input('idcita'))>0){
                $Cita = Cita::find($request->input('idcita'));
                $Cita->movimiento_id = $Ticket->id;
                $Cita->save();
            }
            
            $pagohospital=0;
            $arr=explode(",",$request->input('listServicio'));
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detallemovcaja();
                $Detalle->movimiento_id=$Ticket->id;
                if($request->input('txtIdTipoServicio'.$arr[$c])!="0"){
                    $Detalle->servicio_id=$request->input('txtIdServicio'.$arr[$c]);
                    $Detalle->descripcion="";
                    $servicio = Servicio::find($Detalle->servicio_id);
                    $Detalle->precioconvenio=$servicio->precio;
                    $Detalle->tiposervicio_id=$request->input('txtIdTipoServicio'.$arr[$c]);
                    $Detalle->idunspsc=$request->input('txtIdUnspsc'.$arr[$c]);
                }else{
                    $Detalle->servicio_id=null;
                    $Detalle->descripcion=trim($request->input('txtServicio'.$arr[$c]));
                    $Detalle->tiposervicio_id=$request->input('cboTipoServicio'.$arr[$c]);
                    $idtiposer = $request->input('cboTipoServicio'.$arr[$c]);
                    if($idtiposer>0){
                        $tiposer = Tiposervicio::find($idtiposer);
                        $idunspsc = $tiposer->idunspsc;
                    }else{
                        $idunspsc = $request->input('cboUnspsc'.$arr[$c]);
                    }
                    $Detalle->idunspsc=$idunspsc;
                }
                $Detalle->persona_id=$request->input('txtIdMedico'.$arr[$c]);
                $Detalle->nombremedico=$request->input('txtMedico'.$arr[$c]);
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                if(floatval($request->input('txtPrecio'.$arr[$c])) < 0.0){
                    throw new \Exception("EL PRECIO ES MENOR O IGUAL A CERO");
                }
                if(floatval($request->input('txtPrecioMedico'.$arr[$c]))<0.0){
                    throw new \Exception("EL PAGO AL MEDICO ES MENOR O IGUAL A CERO");
                }
                if(floatval($request->input('txtPrecioHospital'.$arr[$c]))<0.0){
                    throw new \Exception("EL PAGO AL HOSPITAL ES MENOR O IGUAL A CERO");
                }
                $Detalle->precio=$request->input('txtPrecio'.$arr[$c]);
                $Detalle->pagodoctor=$request->input('txtPrecioMedico'.$arr[$c]);
                $Detalle->pagohospital=$request->input('txtPrecioHospital'.$arr[$c]);
                $Detalle->tipodescuento=$request->input('cboDescuento');
                $Detalle->descuento=$request->input('txtDescuento'.$arr[$c]);
                $Detalle->save();
                $pagohospital=$pagohospital + $request->input('txtPrecioHospital'.$arr[$c]) * $request->input('txtCantidad'.$arr[$c]);
            }
            
            if($request->input('boletear')=="S"){//Boleteo todo
                $pagohospital=$request->input('total');
            }

            $notacredito_id=0;
            if($request->input('comprobante')=="S" && $pagohospital>0){//Puse con pago hospital por generar F.E.            
                //Genero Documento de Venta
                if($request->input('tipodocumento')=="Boleta"){
                    $tipodocumento_id=5;
                    $codigo="03";
                    $abreviatura="B";
                }else{
                    $tipodocumento_id=4;
                    $codigo="01";
                    $abreviatura="F";
                }
                $venta        = new Movimiento();
                $venta->fecha = $request->input('fecha');
                if($request->input('manual')=='N'){
                    $numeracion = Numeracion::where('tipomovimiento_id','=',4)->where('tipodocumento_id','=',$tipodocumento_id)->where('serie','=', ($request->input('serieventa') + 0))->first();
                    $numeracion->numero = $numeracion->numero + 1;
                    $numeracion->save();
                    $venta->numero=$numeracion->numero;
                    //$venta->numero= Movimiento::NumeroSigue(4,$tipodocumento_id,$request->input('serieventa'),'N');
                }else{
                    $venta->numero= $request->input('numeroventa');
                }
                $venta->serie = $request->input('serieventa');
                $venta->responsable_id=$user->person_id;
                if($request->input('tipodocumento')=="Boleta"){
                    $venta->persona_id=$request->input('person_id');
                }else{
                    $person=Person::where('ruc','LIKE',$request->input('ruc'))->limit(1)->first();
                    if(count($person)==0){
                        $person = new Person();
                        $person->bussinesname = $request->input('razon');
                        $person->ruc = $request->input('ruc');
                        $person->direccion = $request->input('direccion');
                        $person->save();
                        $venta->persona_id=$person->id;
                    }else{
                        $venta->persona_id=$person->id;
                    }
                }
                if($request->input('tipodocumento')=="Boleta"){
                    $venta->subtotal=number_format($pagohospital/1.18,2,'.','');
                    $venta->igv=number_format($pagohospital - $venta->subtotal,2,'.','');
                    $venta->total=$pagohospital;     
                }else{
                    $venta->subtotal=number_format($pagohospital/1.18,2,'.','');
                    $venta->igv=number_format($pagohospital - $venta->subtotal,2,'.','');
                    $venta->total=number_format($pagohospital,2,'.','');                     
                }
                $venta->tipomovimiento_id=4;
                $venta->tipodocumento_id=$tipodocumento_id;
                $venta->comentario='';
                $venta->manual=$request->input('manual');
                if($request->input('pagar')=="S"){//Pagado
                    $venta->situacion='N';        
                }else{
                    $venta->situacion='P';//Pendiente 
                    if($request->input('descuentopersonal')=="S"){
                        $venta->descuentoplanilla='S';
                        $venta->personal_id=$request->input('personal_id');
                    }   
                }
                $venta->movimiento_id=$Ticket->id;
                $venta->ventafarmacia='N';
                $venta->save();
                //
                
                if($request->input('pagar')=="S"){
                    //guardo movimiento en caja
                    $movimiento        = new Movimiento();
                    $movimiento->fecha = date("Y-m-d");
                    $movimiento->numero= Movimiento::NumeroSigue(2,2);
                    $movimiento->responsable_id=$user->person_id;
                    $movimiento->persona_id=$request->input('person_id');
                    $movimiento->subtotal=0;
                    $movimiento->igv=0;
                    $movimiento->total=$request->input('total',0); 
                    $movimiento->tipomovimiento_id=2;
                    $movimiento->tipodocumento_id=2;
                    $movimiento->conceptopago_id=3;//PAGO DE CLIENTE
                    $movimiento->comentario='Pago de : '.substr($request->input('tipodocumento'),0,1).' '.$venta->serie.'-'.$venta->numero;
                    $movimiento->caja_id=$request->input('caja_id');
                    if($request->input('formapago')=="Tarjeta"){
                        $movimiento->tipotarjeta=$request->input('tipotarjeta');
                        $movimiento->tarjeta=$request->input('tipotarjeta2');
                        $movimiento->voucher=$request->input('nroref');
                        $movimiento->totalpagado=0;
                    }else{
                        $movimiento->totalpagado=$request->input('total',0);
                    }
                    $movimiento->situacion='N';
                    $movimiento->movimiento_id=$venta->id;
                    $movimiento->save();
                    $idref=$movimiento->id;
                    //
                }
                /*
                //Array Insert facturacion
                $person = Person::find($venta->persona_id);
                $persona = Person::find($request->input('person_id'));
                $columna1=6;
                $columna2="20480082673";//RUC HOSPITAL
                $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                $columna4=$codigo;
                $columna5=$abreviatura.str_pad($venta->serie,3,'0',STR_PAD_LEFT).'-'.$venta->numero;
                $columna6=date('Y-m-d');
                $columna7="sistemas@hospitaljuanpablo.pe";
                if($codigo=="03"){//BOLETA
                    if(strlen($person->dni)<>8 || ($request->input('total')+0)<700){
                        $columna8=0;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        $columna9='-';
                        $columna10="CLIENTES VARIOS";//Razon social
                    }else{
                        $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        $columna9=$person->dni;
                        $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                    }
                }else{
                    $columna8=6;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                    $columna9=$person->ruc;
                    $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                }
                if(trim($person->direccion)==''){
                    $columna101=trim('-');
                }else{
                    $columna101=trim($person->direccion);
                }
                //if(trim($person->email)!="" && trim($person->email)!="."){
                //    $columna11=$person->email;
                //}else{
                    $columna11="-";    
                //}
                $columna12="PEN";
                $columna13=$venta->subtotal;
                $columna14='0.00';
                $columna15='0.00';
                $columna16="";
                $columna17=$venta->igv;
                $columna18='0.00';
                $columna19='0.00';
                $columna20=$venta->total;
                $columna21=1000;
                $letras = new EnLetras();
                $columna22=$letras->ValorEnLetras($columna20, "SOLES" );//letras
                $columna23='9670';
                $columna24=substr("CONVENIO: ".$request->input('plan'),0,100);
                $columna25='9199';
                $columna26=substr(trim($persona->apellidopaterno." ".$persona->apellidomaterno." ".$persona->nombres),0,100);
                $columna27='9671';                
                $columna28='HISTORIA CLINICA: '.$request->input('numero_historia');
                $columna29='9672';
                $columna30='DNI: '.$request->input('dni');
                DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER (
                    tipoDocumentoEmisor,
                    numeroDocumentoEmisor,
                    razonSocialEmisor,
                    tipoDocumento,
                    serieNumero,
                    fechaEmision,
                    correoEmisor,
                    tipoDocumentoAdquiriente,
                    numeroDocumentoAdquiriente,
                    razonSocialAdquiriente,
                    correoAdquiriente,
                    tipoMoneda,
                    totalValorVentaNetoOpGravadas,
                    totalValorVentaNetoOpNoGravada,
                    totalValorVentaNetoOpExonerada,
                    totalIgv,
                    totalVenta,
                    codigoLeyenda_1,
                    textoLeyenda_1,
                    codigoAuxiliar100_1,
                    textoAuxiliar100_1,
                    codigoAuxiliar100_2,
                    textoAuxiliar100_2,
                    codigoAuxiliar100_3,
                    textoAuxiliar100_3,
                    codigoAuxiliar100_4,
                    textoAuxiliar100_4
                    ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?, ? ,?, ? ,?)', 
                    [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22, $columna23, $columna24, $columna25, $columna26, $columna27, $columna28, $columna29, $columna30]);
                if($abreviatura=="F"){
                    DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        serieNumero,
                        tipoDocumento,
                        clave,
                        valor) 
                        values (?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);
                }else{
                    DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        serieNumero,
                        tipoDocumento,
                        clave,
                        valor) 
                        values (?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna5, $columna4, 'lugarDestino', $columna101]);
                }
                //---
                
                //Array Insert Detalle Facturacion
                for($c=0;$c<count($arr);$c++){
                    $columnad1=$c+1;
                    $servicio = Servicio::find($arr[$c]);
                    if(!is_null($servicio) && $servicio->tipopago=="Convenio"){
                        $columnad2=$servicio->tarifario->codigo;
                        $columnad3=$servicio->tarifario->nombre;    
                    }else{
                        $columnad2="-";
                        if($request->input('txtIdTipoServicio'.$arr[$c])!="0"){
                            $columnad3=$servicio->nombre;
                        }else{
                            $columnad3=trim($request->input('txtServicio'.$arr[$c]));
                        }
                    }
                    $columnad4=$request->input('txtCantidad'.$arr[$c]);
                    $columnad5="ZZ";
                    $columnad6=round($request->input('txtPrecioHospital'.$arr[$c])/1.18,2);
                    $columnad7=$request->input('txtPrecioHospital'.$arr[$c]);
                    $columnad8="01";
                    $columnad9=round($columnad4*$columnad6,2);
                    $columnad10="10";
                    $columnad11=round($columnad9*0.18,2);
                    $columnad12='0.00';
                    $columnad13='0.00';
                    DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
                    tipoDocumentoEmisor,
                    numeroDocumentoEmisor,
                    tipoDocumento,
                    serieNumero,
                    numeroOrdenItem,
                    codigoProducto,
                    descripcion,
                    cantidad,
                    unidadMedida,
                    importeUnitarioSinImpuesto,
                    importeUnitarioConImpuesto,
                    codigoImporteUnitarioConImpues,
                    importeTotalSinImpuesto,
                    codigoRazonExoneracion,
                    importeIgv
                    )
                    values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);
                }
                DB::connection('sqlsrv')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?',
                    ['A',$columna5]);
                    */
                //--
                
                ///////NOTA DE CREDITO DE GARANTIA
                $notacredito_id=0;
                if($request->input('movimientoref')=='S' && $request->input('movimiento_id')!='0'){
                    $arrNota=explode(",",$request->input('movimiento_id'));
                    for($cd=0;$cd<count($arrNota);$cd++){
                        $ventaref  = Movimiento::find($arrNota[$cd]);
                        $Movimiento       = new Movimiento();
                        $Movimiento->fecha = $request->input('fecha');
                        $Movimiento->serie = 2;
                        $Movimiento->numero = Movimiento::NumeroSigue(6,13,2,'N');
                        $Movimiento->persona_id = $request->input('person_id');
                        $subtotal = number_format($ventaref->total/1.18,2,'.','');
                        $igv = number_format($ventaref->total - $subtotal,2,'.','');
                        $Movimiento->total = $ventaref->total;
                        $Movimiento->subtotal = $subtotal;
                        $Movimiento->igv = $igv;
                        $Movimiento->responsable_id=$user->person_id;
                        $Movimiento->movimiento_id = $arrNota[$cd];
                        $Movimiento->situacion='N';//Normal
                        $Movimiento->tipomovimiento_id = 6;
                        $Movimiento->tipodocumento_id = 13;
                        $Movimiento->manual='N';
                        $Movimiento->comentario = 'Anulacion de la Operacion';
                        $Movimiento->save();
                        $notacredito_id=$Movimiento->id;

                        $resultado = Detallemovcaja::join('movimiento as m','m.id','=','detallemovcaja.movimiento_id')
                                    ->join('movimiento as m2','m2.movimiento_id','=','m.id')
                                    ->where('m2.id','=',$arrNota[$cd])
                                    ->select('detallemovcaja.*');
                        $lista            = $resultado->get();
                        foreach ($lista as $key => $value) {
                            $Detalle = new Detallemovcaja();
                            $Detalle->movimiento_id=$Movimiento->id;
                            $Detalle->persona_id=$value->persona_id;
                            $Detalle->cantidad=$value->cantidad;
                            $Detalle->precio=$value->precio;
                            $Detalle->servicio_id=$value->servicio_id;
                            $Detalle->pagohospital=$value->pagohospital;
                            $Detalle->descripcion=$value->descripcion;
                            $Detalle->descuento=0;
                            $Detalle->save();
                        }
                        //VENTA
                        $ventaref->situacion='A';
                        $ventaref->save();
                        //CAJA
                        $movimiento        = new Movimiento();
                        $movimiento->fecha = date("Y-m-d");
                        $movimiento->numero= Movimiento::NumeroSigue(2,2);
                        $movimiento->responsable_id=$user->person_id;
                        $movimiento->persona_id=$request->input('person_id');
                        $movimiento->subtotal=0;
                        $movimiento->igv=0;
                        $movimiento->total=$ventaref->total; 
                        $movimiento->tipomovimiento_id=2;
                        $movimiento->tipodocumento_id=2;
                        $movimiento->conceptopago_id=13;//DEVVOLUCION
                        $movimiento->comentario='Anulacion de Documento de Venta: '.($ventaref->tipodocumento_id==5?'B':'F').$ventaref->serie.'-'.$ventaref->numero;
                        $movimiento->caja_id=$request->input('caja_id');
                        $movimiento->totalpagado=$ventaref->total;
                        $movimiento->situacion='N';
                        $movimiento->movimiento_id=$Movimiento->id;
                        $movimiento->save();
                        
                        //Array Insert facturacion
                        if($ventaref->tipodocumento_id==5){//BOLETA
                            $codigo="03";
                            $abreviatura="BC";
                        }else{
                            $codigo="01";
                            $abreviatura="FC";
                        }
                        $person = Person::find($Movimiento->persona_id);
                        
                        /*$columna1=6;
                        $columna2="20480082673";//RUC HOSPITAL
                        $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                        $columna4="07";
                        $columna5=$abreviatura.str_pad($Movimiento->serie,2,'0',STR_PAD_LEFT).'-'.$Movimiento->numero;
                        $numeronc=$columna5;//PARA ANULAR POR ERROR
                        $columna6=date('Y-m-d');
                        $columna7="sistemas@hospitaljuanpablo.pe";
                        if($ventaref->tipodocumento_id==5){//BOLETA
                            if(strlen($person->dni)<>8 || $Movimiento->total<700){
                                $columna8=0;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                                $columna9='-';
                                $columna10="CLIENTES VARIOS";//Razon social
                            }else{
                                $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                                $columna9=$person->dni;
                                $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                            }
                        }else{
                            $columna8=6;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                            $columna9=$person->ruc;
                            $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                        }
                        if(trim($person->direccion)!=""){
                            $columna101=trim($person->direccion);
                        }else{
                            $columna101="-";
                        }
                        $columna11="-";    
                        $columna12="PEN";
                        $columna13=$Movimiento->subtotal;
                        $columna14='0.00';
                        $columna15='0.00';
                        $columna16="";
                        $columna17=$Movimiento->igv;
                        $columna18='0.00';
                        $columna19='0.00';
                        $columna20=$Movimiento->total;
                        $columna21=1000;
                        $letras = new EnLetras();
                        $columna22=$letras->ValorEnLetras($columna20, "SOLES" );//letras
                        $columna23=$codigo;
                        $columna24=($ventaref->tipodocumento_id==4?"F":"B").str_pad($ventaref->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($ventaref->numero,8,'0',STR_PAD_LEFT);
                        $columna25=$Movimiento->comentario;
                        $columna26='01';
                        DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER (
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            razonSocialEmisor,
                            tipoDocumento,
                            serieNumero,
                            fechaEmision,
                            correoEmisor,
                            tipoDocumentoAdquiriente,
                            numeroDocumentoAdquiriente,
                            razonSocialAdquiriente,
                            correoAdquiriente,
                            tipoMoneda,
                            totalValorVentaNetoOpGravadas,
                            totalValorVentaNetoOpNoGravada,
                            totalValorVentaNetoOpExonerada,                
                            totalIgv,
                            totalVenta,
                            codigoLeyenda_1,
                            textoLeyenda_1,
                            tipoDocumentoReferenciaPrincip,
                            numeroDocumentoReferenciaPrinc,
                            motivoDocumento,
                            codigoSerieNumeroAfectado,
                            serieNumeroAfectado 
                            ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                            [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22, $columna23, $columna24, $columna25, $columna26, $columna24]);

                        if($abreviatura=="BC"){
                            DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                                tipoDocumentoEmisor,
                                numeroDocumentoEmisor,
                                serieNumero,
                                tipoDocumento,
                                clave,
                                valor) 
                                values (?, ?, ?, ?, ?, ?)',
                                [$columna1, $columna2, $columna5, $columna4, 'lugarDestino', $columna101]);
                        }else{
                            DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                                tipoDocumentoEmisor,
                                numeroDocumentoEmisor,
                                serieNumero,
                                tipoDocumento,
                                clave,
                                valor) 
                                values (?, ?, ?, ?, ?, ?)',
                                [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);
                        }
                        //---
                        
                        //Array Insert Detalle Facturacion
                        $resultado = Detallemovcaja::join('movimiento as m','m.id','=','detallemovcaja.movimiento_id')
                                    ->join('movimiento as m2','m2.movimiento_id','=','m.id')
                                    ->where('m2.id','=',$arrNota[$cd])
                                    ->select('detallemovcaja.*');
                        $lista            = $resultado->get();
                        $c=0;
                        foreach ($lista as $key => $value) {
                            $c=$c+1;
                            $columnad1=$c;
                            if($value->servicio_id>0){
                                $servicio = Servicio::find($value->servicio_id);
                                if($servicio->tipopago=="Convenio"){
                                    $columnad2=$servicio->tarifario->codigo;
                                    $columnad3=$servicio->tarifario->nombre;    
                                }else{
                                    $columnad2="-";
                                    if($vlaue->servicio_id!="0"){
                                        $columnad3=$servicio->nombre;
                                    }else{
                                        $columnad3=trim($value->descripcion);
                                    }
                                }
                            }else{
                                $columnad2="-";
                                $columnad3=trim($value->descripcion);
                            }
                            $columnad4=round($value->cantidad,2);
                            $columnad5="ZZ";
                            $columnad6=round($value->pagohospital/1.18,2);
                            $columnad7=$value->pagohospital;
                            $columnad8="01";
                            $columnad9=round($columnad4*$columnad6,2);
                            $columnad10="10";
                            $columnad11=round($columnad9*0.18,2);
                            $columnad12='0.00';
                            $columnad13='0.00';
                            DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
                            tipoDocumentoEmisor,
                            numeroDocumentoEmisor,
                            tipoDocumento,
                            serieNumero,
                            numeroOrdenItem,
                            codigoProducto,
                            descripcion,
                            cantidad,
                            unidadMedida,
                            importeUnitarioSinImpuesto,
                            importeUnitarioConImpuesto,
                            codigoImporteUnitarioConImpues,
                            importeTotalSinImpuesto,
                            codigoRazonExoneracion,
                            importeIgv
                            )
                            values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                            [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);
                        }
                        DB::connection('sqlsrv')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?',
                            ['A',$columna5]);
                        */
                    }
                }
                
                ///////
            }
            $dat[0]=array("respuesta"=>"OK","ticket_id"=>$Ticket->id,"pagohospital"=>$pagohospital,"notacredito_id"=>$notacredito_id);
        });
        /*if (!is_null($error)) {
            DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEHEADER where serieNumero="'.$numero.'"');
            DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEDETAIL where serieNumero="'.$numero.'"');
            DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEHEADER_ADD where serieNumero="'.$numero.'"');
            DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEHEADER where serieNumero="'.$numeronc.'"');
            DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEDETAIL where serieNumero="'.$numeronc.'"');
            DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEHEADER_ADD where serieNumero="'.$numeronc.'"');
        }*/
        return is_null($error) ? json_encode($dat) : $error;
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
        $existe = Libreria::verificarExistencia($id, 'Movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $facturacion              = Libreria::getParam($request->input('facturacion'), 'NO');
        $ticket = Movimiento::find($id);
        $entidad             = 'Ticket';
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
        $cboCondicionPaciente = array("Consulta externa" => "Consulta externa", "Emergencia" => "Emergencia", "Hospitalizado" => "Hospitalizado", "UCI" => "UCI");
        $numero = $ticket->numero;
        $user = Auth::user();

        $numeroventa = Movimiento::NumeroSigue(4,5,$serie,'N');
        $serie='00'.$serie;

        $formData            = array('ticket.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('ticket', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente', 'cboConvenio', 'cboTipoDocumento', 'cboFormaPago', 'cboTipoTarjeta', 'cboTipoServicio', 'cboTipoTarjeta2', 'numero', 'cboCaja', 'numeroventa','serie','idcaja','facturacion','cboCondicionPaciente'));
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
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'fecha'                  => 'required',
                'numero'          => 'required',
                'paciente'          => 'required',
                'numero'          => 'required',
                'total'         => 'required',
                'plan'          => 'required',
                );
        $mensajes = array(
            'fecha.required'         => 'Debe seleccionar una fecha',
            'numero.required'         => 'El ticket debe tener un numero',
            'paciente.required'         => 'Debe seleccionar un paciente',
            'plan.required'         => 'Debe seleccionar un plan',
            'total.required'         => 'Debe agregar detalle al ticket',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
       
        $error = DB::transaction(function() use($request, $id){
            $Ticket                        = Movimiento::find($id);
            $Ticket->persona_id = $request->input('person_id');
            $Ticket->nombrepaciente = $request->input('paciente');
            $Ticket->fecha = $request->input('fecha');
            $Ticket->plan_id = $request->input('plan_id');
            $Ticket->soat = $request->input('soat');
            $Ticket->sctr = $request->input('sctr');
            $Ticket->save();
            $arr2 = array();
            $arr=explode(",",$request->input('listServicio'));
            Detallemovcaja::where('movimiento_id','=',$id)->whereNotIn('situacionentrega',['A'])->delete();
            foreach ($arr as $ids) {
                if(strlen($ids)>0){
                    $arr2[] = $ids;
                }
            }
            $arr = $arr2;
            //dd($arr);
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detallemovcaja();
                $Detalle->movimiento_id=$Ticket->id;
                //dd($Ticket);
                if($request->input('txtIdTipoServicio'.$arr[$c])!="0"){
                    $Detalle->servicio_id=$request->input('txtIdServicio'.$arr[$c]);
                    $Detalle->descripcion="";
                    $servicio = Servicio::find($request->input('txtIdServicio'.$arr[$c]));
                    $Detalle->precioconvenio=$servicio->precio;
                    $Detalle->tiposervicio_id=$request->input('cboTipoServicio'.$arr[$c]);
                }else{
                    $Detalle->servicio_id=null;
                    $Detalle->descripcion=trim($request->input('txtServicio'.$arr[$c]));
                    $Detalle->tiposervicio_id=$request->input('cboTipoServicio'.$arr[$c]);
                }
                $Detalle->persona_id=$request->input('txtIdMedico'.$arr[$c]);
                $Detalle->nombremedico=$request->input('txtMedico'.$arr[$c]);
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                $Detalle->precio=$request->input('txtPrecio'.$arr[$c]);
                $Detalle->pagodoctor=$request->input('txtPrecioMedico'.$arr[$c]);
                $Detalle->pagohospital=$request->input('txtPrecioHospital'.$arr[$c]);
                $Detalle->tipodescuento=$request->input('cboDescuento');
                $Detalle->descuento=$request->input('txtDescuento'.$arr[$c]);
                $Detalle->save();
            }
        });
        
        $dat[0]=array("respuesta"=>"OK","ticket_id"=>$id,"pagohospital"=>0,"notacredito_id"=>0);
        return is_null($error) ? json_encode($dat) : $error;
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

    public function anulacion(Request $request)
    {
        $id = $request->input("id");
        $comentarioa = $request->input("comentarioa");
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id, $comentarioa){
            $Venta = Movimiento::find($id);
            $Venta->motivo_anul = $comentarioa;
            $Venta->situacion = 'U';
            $Venta->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function anular($id, $listarLuego)
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
        $formData = array('route' => array('ticket.anulacion'), 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view('app.confirmar2')->with(compact('id' ,'modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function editarresponsable($id, $listarLuego)
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
        $formData = array('route' => array('ticket.guardarresponsable'), 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Guardar';
        return view($this->folderview.'.editarresponsable')->with(compact('id' ,'modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function guardarresponsable(Request $request)
    {
        $id = $request->input("id");
        $comentarioa = $request->input("comentarioa");
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id, $comentarioa){
            $Venta = Movimiento::find($id);
            $Venta->listapago = $comentarioa;
            //dd($Venta);
            $Venta->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function buscarservicio(Request $request)
    {
        $descripcion = $request->input("descripcion");
        $idtiposervicio = trim($request->input("idtiposervicio"));
        $tipopago = $request->input('tipopaciente');
        $resultado = Servicio::leftjoin('tarifario','tarifario.id','=','servicio.tarifario_id');
        if($tipopago=='Convenio'){
            $resultado = $resultado->where(DB::raw('trim(concat(servicio.nombre,\' \',tarifario.codigo,\' \',tarifario.nombre))'),'LIKE','%'.$descripcion.'%')->where('servicio.plan_id','=',$request->input('plan_id'));
        }else{
            $resultado = $resultado->where(DB::raw('trim(servicio.nombre)'),'LIKE','%'.$descripcion.'%');
        }
        if(trim($idtiposervicio)!=""){
            $resultado = $resultado->where('tiposervicio_id','=',$idtiposervicio);
        }
        $resultado    = $resultado->where('tipopago','LIKE',''.strtoupper($tipopago).'')->select('servicio.*','tarifario.nombre as tarifario')->get();
        if(count($resultado)>0){
            $c=0;
            foreach ($resultado as $key => $value){
                if($value->tiposervicio_id==1 && $tipopago=='Particular' && $request->input('plan_id')!="6"){//PARA CONSULTAS DE CONVENIO
                    $plan = Plan::find($request->input('plan_id'));
                    $data[$c] = array(
                            'servicio' => ($value->tipopago=='Convenio')?$value->tarifario:$value->nombre,
                            'tiposervicio' => $value->tiposervicio->nombre,
                            'precio' => $plan->consulta,
                            'idservicio' => $value->id,
                        );
                }else{
                    $data[$c] = array(
                            'servicio' => ($value->tipopago=='Convenio')?$value->tarifario:$value->nombre,
                            'tiposervicio' => $value->tiposervicio->nombre,
                            'precio' => $value->precio,
                            'idservicio' => $value->id,
                        );
                }
                $c++;                
            }         
        }else{
            if(($idtiposervicio=='' || $idtiposervicio==1)){//buscar consultas con precio de convenio
                $resultado = Servicio::where(DB::raw('trim(servicio.nombre)'),'LIKE','%'.$descripcion.'%')
                            ->where('tipopago','LIKE','Particular')
                            ->where('tiposervicio_id','=','1')->get();
                if(count($resultado)>0){
                    $c=0;
                    $plan = Plan::find($request->input('plan_id'));
                    foreach ($resultado as $key => $value){
                        //COSTO DE CONSULTA
                        $data[$c] = array(
                                    'servicio' => ($value->tipopago=='Convenio')?$value->tarifario:$value->nombre,
                                    'tiposervicio' => $value->tiposervicio->nombre,
                                    'precio' => $plan->consulta,
                                    'idservicio' => $value->id,
                                );
                                $c++;                
                    }            
                }else{
                    $data = array();    
                }
            }else{
                $data = array();
            }
        }
        return json_encode($data);
    }

    public function seleccionarservicio(Request $request)
    {
        $resultado = Servicio::find($request->input('idservicio'));
        if($resultado->modo=="Monto"){
            $pagohospital=$resultado->pagohospital;
            $pagomedico=$resultado->pagodoctor;
        }else{
            $pagohospital=number_format($resultado->pagohospital*$resultado->precio/100,2,'.','');
            $pagomedico=number_format($resultado->pagodoctor*$resultado->precio/100,2,'.','');
        }
        if($request->input('plan_id')>0 && $request->input('tipopaciente')=="Convenio"){
            $plan = Plan::find($request->input('plan_id'));
            if($resultado->tiposervicio_id==1){//CONSULTA
                $data[0] = array(
                    'servicio' => ($resultado->tipopago=='Convenio')?$resultado->tarifario->nombre:$resultado->nombre,
                    'tiposervicio' => $resultado->tiposervicio->nombre,
                    'precio' => $plan->consulta,
                    'idservicio' => $resultado->id,
                    'preciohospital' => $plan->consulta,
                    'preciomedico' => 0,
                    'modo' => $resultado->modo,
                    'idtiposervicio' => $resultado->tiposervicio_id,
                    'idunspsc' => $resultado->tiposervicio->idunspsc,
                );
            }else{
                $data[0] = array(
                    'servicio' => ($resultado->tipopago=='Convenio')?$resultado->tarifario->nombre:$resultado->nombre,
                    'tiposervicio' => $resultado->tiposervicio->nombre,
                    'precio' => $resultado->precio,
                    'idservicio' => $resultado->id,
                    'preciohospital' => $resultado->precio,
                    'preciomedico' => 0,
                    'modo' => $resultado->modo,
                    'idtiposervicio' => $resultado->tiposervicio_id,
                    'idunspsc' => $resultado->tiposervicio->idunspsc,
                );
            }
        }elseif($request->input('plan_id')>0 && $request->input('tipopaciente')=="Particular") {
            $plan = Plan::find($request->input('plan_id'));
            if($plan->tipo=='Institucion'){//DESCUENTO PARA LOS CONVENIOS INSTITUCIONALES
                $rs = Detalleplan::where('plan_id','=',$request->input('plan_id'))
                                    ->where('tiposervicio_id','=',$resultado->tiposervicio_id)->get();
                if(count($rs)>0){
                    foreach ($rs as $key => $value) {
                        $precio = number_format($resultado->precio*(100-$value->descuento)/100,2,'.','');
                        $pagohospital = $precio;
                        $pagomedico = 0;   
                    }
                }else{
                    if($resultado->tiposervicio_id==1 && $request->input('plan_id')!="6"){//PARA CONSULTAS DE CONVENIO
                        $precio = $plan->consulta;
                        $pagohospital = $precio;
                        $pagomedico = 0;
                    }else{
                        $precio = $resultado->precio;
                    }
                }
                $data[0] = array(
                        'servicio' => ($resultado->tipopago=='Convenio')?$resultado->tarifario->nombre:$resultado->nombre,
                        'tiposervicio' => $resultado->tiposervicio->nombre,
                        'precio' => $precio,
                        'idservicio' => $resultado->id,
                        'preciohospital' => $pagohospital,
                        'preciomedico' => $pagomedico,
                        'modo' => $resultado->modo,
                        'idtiposervicio' => $resultado->tiposervicio_id,
                        'idunspsc' => $resultado->tiposervicio->idunspsc,
                    );
            }else{
                /*if($request->input('formapago')=='Tarjeta'){
                    if($request->input('tarjeta')=="CREDITO"){
                        $precio=number_format($resultado->precio*1.04,2,'.','');
                    }else{
                        $precio=number_format($resultado->precio*1.03,2,'.','');
                    }
                    $pagohospital=$precio - $pagomedico;
                }else{*/
                    $precio=$resultado->precio;
                //}
                $data[0] = array(
                        'servicio' => ($resultado->tipopago=='Convenio')?$resultado->tarifario->nombre:$resultado->nombre,
                        'tiposervicio' => $resultado->tiposervicio->nombre,
                        'precio' => $precio,
                        'idservicio' => $resultado->id,
                        'preciohospital' => $pagohospital,
                        'preciomedico' => $pagomedico,
                        'modo' => $resultado->modo,
                        'idtiposervicio' => $resultado->tiposervicio_id,
                        'idunspsc' => $resultado->tiposervicio->idunspsc,
                    );
            }
        }else{
            /*if($request->input('formapago')=='Tarjeta'){
                if($request->input('tarjeta')=="CREDITO"){
                    $precio=number_format($resultado->precio*1.04,2,'.','');
                }else{
                    $precio=number_format($resultado->precio*1.03,2,'.','');
                }
                $pagohospital=$precio - $pagomedico;
            }else{*/
                $precio=$resultado->precio;
            //}
            $data[0] = array(
                    'servicio' => ($resultado->tipopago=='Convenio')?$resultado->tarifario->nombre:$resultado->nombre,
                    'tiposervicio' => $resultado->tiposervicio->nombre,
                    'precio' => $precio,
                    'idservicio' => $resultado->id,
                    'preciohospital' => $pagohospital,
                    'preciomedico' => $pagomedico,
                    'modo' => $resultado->modo,
                    'idtiposervicio' => $resultado->tiposervicio_id,
                    'idunspsc' => $resultado->tiposervicio->idunspsc,
                );
        }
        return json_encode($data);
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
                $pdf::AddPage('');
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
                $pdf::Cell(37,6,utf8_encode("Nombre / Razón Social: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(110,6,(trim($value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,utf8_encode($abreviatura=="F"?"RUC :":"DNI".": "),0,0,'L');
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
                $pdf::Cell(37,6,utf8_encode("Dirección: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(110,6,(trim($value->persona->direccion)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,utf8_encode("Fecha de emisión: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,date("d/m/Y",strtotime($value->fecha)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,utf8_encode("Moneda: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(110,6,(trim('PEN - Sol')),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,("Condicion: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $value2=Movimiento::find($id);
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
                    $historia = Historia::where('person_id','=',$value2->persona_id)->first();
                    // dd($value2->persona_id);
                    if(!is_null($historia)){
                        $pdf::SetFont('helvetica','',8);
                        $pdf::Cell(30,6,($historia->numero),0,0,'L');

                    } 
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(10,7,("Item"),1,0,'C');
                $pdf::Cell(13,7,utf8_encode("Código"),1,0,'C');
                $pdf::Cell(68,7,utf8_encode("Descripción"),1,0,'C');
                $pdf::Cell(10,7,("Und."),1,0,'C');
                $pdf::Cell(15,7,("Cantidad"),1,0,'C');
                $pdf::Cell(20,7,("V. Unitario"),1,0,'C');
                $pdf::Cell(20,7,("P. Unitario"),1,0,'C');
                $pdf::Cell(20,7,("Descuento"),1,0,'C');
                $pdf::Cell(20,7,("Total"),1,0,'C');
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
                $pdf::Cell(0,5,utf8_encode('Autorizado a ser emisor electrónico mediante R.I. SUNAT Nº 0340050004781'),0,0,'L');
                $pdf::Ln();
                $pdf::Cell(0,5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(160,5,utf8_encode('Representación Impresa de la Factura Electrónica, consulte en https://sfe.bizlinks.com.pe'),0,0,'L');
                $pdf::Cell(0,5,$value->created_at,0,0,'R');
                $pdf::Ln();
                $pdf::Output('Comprobante.pdf');
            }
        }
    }
    
    public function pdfComprobante2(Request $request){
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
                /*if($value->serie==9){//EMERGENCIA
                    $pdf::AddPage();
                }else{*/
                    $pdf::AddPage('L');
                //}
                $pdf::SetFont('helvetica','B',13);
                $pdf::Ln();
                $pdf::Cell(180,6,"",0,0,'C');
                if($value->tipodocumento_id=="4"){//Factura
                    $abreviatura="F";
                    $dni=$value->persona->ruc;
                    $subtotal=number_format($value->total/1.18,2,'.','');
                    $igv=number_format($value->total - $subtotal,2,'.','');
                    $pdf::Cell(60,4,utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',11);
                    $pdf::Cell(35,4,"RAZON SOCIAL: ",0,0,'L');
                    $pdf::Cell(180,4,(trim($value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                    $pdf::Cell(25,4,"RUC: ",0,0,'L');
                    $pdf::Cell(30,4,$dni,0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(35,4,"DIRECCION: ",0,0,'L');
                    $pdf::Cell(180,4,(trim($value->persona->direccion)),0,0,'L');
                    $pdf::Cell(25,4,"FECHA: ",0,0,'L');
                    $pdf::Cell(30,4,date("d/m/Y",strtotime($value->fecha)),0,0,'L');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',11);
                    $pdf::Cell(35,6,utf8_encode("PACIENTE: "),0,0,'L');
                    $ticket = Movimiento::find($value->movimiento_id);
                    $pdf::Cell(180,6,(trim($ticket->persona->apellidopaterno." ".$ticket->persona->apellidomaterno." ".$ticket->persona->nombres)),0,0,'L');
                    $pdf::Cell(25,4,"DNI: ",0,0,'L');
                    $pdf::Cell(30,4,utf8_encode($ticket->persona->dni),0,0,'L');
                    $pdf::Ln();
                    $value2=Movimiento::find($id);
                    $historia = Historia::where('person_id','=',$value2->persona_id)->first();
                    $pdf::Cell(35,4,"CONVENIO: ",0,0,'L');
                    $pdf::Cell(120,4,trim($value2->plan->nombre),0,0,'L');
                    $pdf::Cell(60,4,utf8_encode($value->situacion=='P'?'PENDIENTE':'CONTADO'),0,0,'C');
                    $pdf::Cell(25,4,"HISTORIA: ",0,0,'L');
                    $pdf::Cell(30,4,utf8_encode($historia->numero),0,0,'L');
                    $pdf::Ln();
                    if($value2->tarjeta!="")
                        $pdf::Cell(50,6,trim($value2->tarjeta." - ".$value2->tipotarjeta),0,0,'L');
                    $pdf::Cell(0,4,$value->responsable->nombres,0,0,'R');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',11);
                    $pdf::Cell(5,7,"",0,0,'C');
                    $pdf::Cell(15,7,("Cant."),0,0,'C');
                    $pdf::Cell(180,7,utf8_encode("Descripción"),0,0,'C');
                    $pdf::Cell(30,7,("P. Unitario"),0,0,'C');
                    $pdf::Cell(30,7,("Sub Total"),0,0,'C');
                    $pdf::Ln();
                    $resultado        = Detallemovcaja::leftjoin('servicio', 'servicio.id', '=', 'detallemovcaja.servicio_id')
                                ->where('detallemovcaja.movimiento_id', '=', $id)
                                ->select('detallemovcaja.*');
                    $lista2            = $resultado->get();
                    $c=0;
                    foreach($lista2 as $key2 => $v){$c=$c+1;
                        $pdf::SetFont('helvetica','B',11);
                        $pdf::Cell(5,4,"",0,0,'C');
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
                        $pdf::Cell(15,4,number_format($v->cantidad,2,'.',''),0,0,'C');
                        $nombre.=" - ".substr($v->persona->nombres,0,1)." ".$v->persona->apellidopaterno;
                        if(strlen($nombre)<80){
                            $pdf::Cell(180,4,utf8_encode($nombre),0,0,'L');
                        }else{
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(180,2,utf8_encode($nombre),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(180,4,"",0,0,'L');
                        }
                        $pdf::Cell(30,4,number_format($v->pagohospital,2,'.',''),0,0,'R');
                        $pdf::Cell(30,4,number_format($v->pagohospital*$v->cantidad,2,'.',''),0,0,'R');
                        $pdf::Ln('4');                    
                    }
                    $pdf::Ln();
                    $letras = new EnLetras();
                    $pdf::SetFont('helvetica','B',11);
                    $valor=$letras->ValorEnLetras($value->total, " SOLES" );//letras
                    $pdf::Cell(15,7,"",0,0,'C');
                    $pdf::Cell(195,5,utf8_decode($valor),0,0,'L');
                    $pdf::SetFont('helvetica','B',11);
                    $pdf::Cell(20,7,"SUBTOTAL: ",0,0,'L');
                    $pdf::Cell(30,5,"S/. ".number_format($subtotal,2,'.',''),0,0,'R');
                    $pdf::Ln();
                    $pdf::Cell(210,5,'',0,0,'L');
                    $pdf::Cell(20,7,"IGV: ",0,0,'L');
                    $pdf::Cell(30,5,"S/. ".$igv,0,0,'R');
                    $pdf::Ln();
                    $pdf::Cell(210,5,'',0,0,'L');
                    $pdf::Cell(20,7,"TOTAL: ",0,0,'L');
                    $pdf::Cell(30,5,"S/. ".number_format($value->total,2,'.',''),0,0,'R');
                    $pdf::Ln();
                }else{
                    $abreviatura="B";
                    $subtotal='0.00';
                    $igv='0.00';
                    if(strlen($value->persona->dni)<>8){
                        $dni='-';
                    }else{
                        $dni=$value->persona->dni;
                    }
                    $pdf::Cell(60,4,utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',11);
                    $pdf::Cell(40,4,"",0,0,'C');
                    $pdf::Cell(180,4,(trim($value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                    $pdf::Cell(37,4,date("d/m/Y",strtotime($value->fecha)),0,0,'L');
                    $pdf::Ln();
                    if($value->tipodocumento_id=="4"){
                        $pdf::SetFont('helvetica','B',11);
                        $pdf::Cell(37,6,utf8_encode("Paciente: "),0,0,'L');
                        $pdf::SetFont('helvetica','',11);
                        $ticket = Movimiento::find($value->movimiento_id);
                        $pdf::Cell(110,6,(trim($ticket->persona->apellidopaterno." ".$ticket->persona->apellidomaterno." ".$ticket->persona->nombres)),0,0,'L');
                        $pdf::Ln();
                    }
                    $pdf::SetFont('helvetica','B',11);
                    $pdf::Cell(40,4,"",0,0,'C');
                    $pdf::Cell(40,4,utf8_encode($dni),0,0,'L');
                    $value2=Movimiento::find($id);
                    $pdf::Cell(50,4,(trim($value2->plan->nombre)),0,0,'L');
                    if($value2->tarjeta!="")
                        $pdf::Cell(50,6,trim($value2->tarjeta." - ".$value2->tipotarjeta),0,0,'L');
                    $pdf::Cell(30,4,utf8_encode($value->situacion=='P'?'PENDIENTE':'CONTADO'),0,0,'C');
                    $pdf::Cell(0,4,$value->responsable->nombres,0,0,'R');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',11);
                    $pdf::Cell(5,7,"",0,0,'C');
                    $pdf::Cell(15,7,("Cant."),0,0,'C');
                    $pdf::Cell(180,7,utf8_encode("Descripción"),0,0,'C');
                    $pdf::Cell(30,7,("P. Unitario"),0,0,'C');
                    $pdf::Cell(30,7,("Sub Total"),0,0,'C');
                    $pdf::Ln();
                    $resultado        = Detallemovcaja::leftjoin('servicio', 'servicio.id', '=', 'detallemovcaja.servicio_id')
                                ->where('detallemovcaja.movimiento_id', '=', $id)
                                ->select('detallemovcaja.*');
                    $lista2            = $resultado->get();
                    $c=0;
                    foreach($lista2 as $key2 => $v){$c=$c+1;
                        $pdf::SetFont('helvetica','B',11);
                        $pdf::Cell(5,4,"",0,0,'C');
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
                        $pdf::Cell(15,4,number_format($v->cantidad,2,'.',''),0,0,'C');
                        $nombre.=" - ".substr($v->persona->nombres,0,1)." ".$v->persona->apellidopaterno;
                        if(strlen($nombre)<80){
                            $pdf::Cell(180,4,utf8_encode($nombre),0,0,'L');
                        }else{
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(180,2,substr(utf8_encode($nombre),0,80),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(180,4,"",0,0,'L');
                        }
                        $pdf::Cell(30,4,number_format($v->pagohospital,2,'.',''),0,0,'R');
                        $pdf::Cell(30,4,number_format($v->pagohospital*$v->cantidad,2,'.',''),0,0,'R');
                        $pdf::Ln('4');                    
                    }
                    $pdf::Ln();
                    $letras = new EnLetras();
                    $pdf::SetFont('helvetica','B',11);
                    $valor=$letras->ValorEnLetras($value->total, " SOLES" );//letras
                    $pdf::Cell(15,7,"",0,0,'C');
                    $pdf::Cell(215,5,utf8_decode($valor),0,0,'L');
                    $pdf::SetFont('helvetica','B',11);
                    $pdf::Cell(30,5,"S/. ".number_format($value->total,2,'.',''),0,0,'R');
                    $pdf::Ln();
                }
                $pdf::Output('Comprobante.pdf');
            }
        }
    }

    public function pdfComprobante3(Request $request){
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
                $pdf::AddPage('');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(60,4,utf8_decode("HOSPITAL PRIVADO JUAN PABLO II SAC"),0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(60,4,utf8_decode("RUC: 20480082673"),0,0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(60,4,utf8_decode("Tel.: 226070 - 226108"),0,0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(60,4,utf8_decode("Dir.: Av. Grau 1461 - Chiclayo"),0,0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(60,4,utf8_encode($value->tipodocumento_id=='4'?"FACTURA":"BOLETA").utf8_encode(" ELECTRÓNICA"),0,0,'C');
                $pdf::Ln();
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
                $pdf::Cell(60,4,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                $pdf::Ln();
                $pdf::Cell(60,4,"====================================",0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(13,4,utf8_encode("Cliente: "),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::MultiCell(47,6,(trim($value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(13,4,utf8_encode($abreviatura=="F"?"RUC :":"DNI".": "),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(47,4,utf8_encode($dni),0,0,'L');
                $pdf::Ln();
                if($value->tipodocumento_id=="4" && $value->id!=86410 && $value->id!=86407 && $value->id!=144210 && $value->id!=144227){
                    $pdf::SetFont('helvetica','B',8);
                    $pdf::Cell(13,4,utf8_encode("Paciente: "),0,0,'L');
                    $pdf::SetFont('helvetica','B',8);
                    $ticket = Movimiento::find($value->movimiento_id);
                    $pdf::MultiCell(47,6,(trim($ticket->persona->apellidopaterno." ".$ticket->persona->apellidomaterno." ".$ticket->persona->nombres)),0,'L');
                }
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(14,4,utf8_encode("Dirección: "),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                if(strlen((trim($value->persona->direccion)))>20){
                    $pdf::MultiCell(37,6,(trim($value->persona->direccion)),0,'L');
                }else{
                    $pdf::Cell(37,4,(trim($value->persona->direccion)),0,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(13,4,utf8_encode("Fecha: "),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(37,4,date("d/m/Y",strtotime($value->fecha)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(13,4,("Cond.: "),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $value2=Movimiento::find($id);
                if($value2->tarjeta!=""){
                    $pdf::Cell(37,4,trim($value2->tarjeta." - ".$value2->tipotarjeta),0,0,'L');
                }elseif($value2->situacion=='B'){
                    $pdf::Cell(37,4,trim('PENDIENTE'),0,0,'L');
                }else{
                    $pdf::Cell(37,4,trim('CONTADO'),0,0,'L');
                }

                $pdf::Ln();
                if($value->id!=86410 && $value->id!=86407 && $value->id!=144210 && $value->id!=144227){
                    $pdf::SetFont('helvetica','B',8);
                    $pdf::Cell(14,4,utf8_encode("Convenio: "),0,0,'L');
                    $pdf::SetFont('helvetica','B',8);
                    if(strlen(trim($value2->plan->nombre))>20){
                        $pdf::MultiCell(37,6,(trim($value2->plan->nombre)),0,'L');
                    }else{
                        $pdf::Cell(37,4,(trim($value2->plan->nombre)),0,0,'L');
                        $pdf::Ln();
                    }
                    $pdf::SetFont('helvetica','B',8);
                    $pdf::Cell(13,4,("Historia: "),0,0,'L');
                    $historia = Historia::where('person_id','=',$value2->persona_id)->first();
                    $pdf::SetFont('helvetica','B',8);
                    if(!is_null($historia)){
                        $pdf::Cell(37,4,($historia->numero),0,0,'L');
                    }
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(60,4,"====================================",0,0,'L');
                $pdf::Ln();
                $pdf::Cell(20,4,utf8_encode("Descripción"),0,0,'C');
                $pdf::Cell(10,4,("Cant"),0,0,'C');
                $pdf::Cell(15,4,("P. Unit"),0,0,'C');
                $pdf::Cell(15,4,("Total"),0,0,'C');
                $pdf::Ln();
                $resultado        = Detallemovcaja::leftjoin('servicio', 'servicio.id', '=', 'detallemovcaja.servicio_id')
                            ->where('detallemovcaja.movimiento_id', '=', $id)
                            ->select('detallemovcaja.*');
                $lista2            = $resultado->get();
                $c=0;
                foreach($lista2 as $key2 => $v){$c=$c+1;
                    $pdf::SetFont('helvetica','B',8);
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
                    if(strlen($nombre)<50){
                        $pdf::Cell(60,4,utf8_encode($nombre),0,0,'L');
                        $pdf::Ln();
                    }else{
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(60,6,utf8_encode($nombre),0,'L');
                    }
                    $pdf::Cell(20,4,"",0,0,'R');
                    $pdf::Cell(10,4,number_format($v->cantidad,2,'.',''),0,0,'R');
                    $pdf::Cell(15,4,number_format($v->pagohospital,2,'.',''),0,0,'R');
                    $pdf::Cell(15,4,number_format($v->pagohospital*$v->cantidad,2,'.',''),0,0,'R');
                    $pdf::Ln();                    
                }
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(60,4,"====================================",0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(30,4,utf8_decode('Op. Gravada'),0,0,'L');
                $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(20,4,$subtotal,0,0,'R');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(30,4,utf8_decode('I.G.V'),0,0,'L');
                $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(20,4,$igv,0,0,'R');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(30,4,utf8_decode('Op. Inafecta'),0,0,'L');
                $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(20,4,'0.00',0,0,'R');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(30,4,utf8_decode('Op. Exonerada'),0,0,'L');
                $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(20,4,'0.00',0,0,'R');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(30,4,utf8_decode('Total'),0,0,'L');
                $pdf::Cell(10,4,utf8_decode('S/'),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,4,number_format($value->total,2,'.',''),0,0,'R');
                $pdf::Ln();
                $letras = new EnLetras();
                $pdf::SetFont('helvetica','B',8);
                $valor="SON: ".$letras->ValorEnLetras($value->total, "SOLES" );//letras
                if(strlen($valor)>40){
                    $pdf::MultiCell(60,6,utf8_decode($valor),0,'L');
                }else{
                    $pdf::Cell(60,4,utf8_decode($valor),0,0,'L');
                    $pdf::Ln();
                }
                $pdf::Cell(60,5,'Usuario: '.utf8_decode($value->responsable->nombres),0,0,'L');
                $pdf::Ln();
                $pdf::Cell(60,5,$value->created_at,0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(60,4,"====================================",0,0,'L');
                $pdf::Ln();
                $objTicket = Movimiento::find($id);
                if(strlen($objTicket->listapago)>0){
                    // dd($objTicket->listapago);
                    $pdf::MultiCell(60,4,strtoupper($objTicket->listapago),0,'L');
                    $pdf::Cell(60,4,"====================================",0,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','B',8);
                $pdf::MultiCell(60,6,utf8_encode('Autorizado a ser emisor electrónico mediante R.I. SUNAT Nº 0340050004781'),0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::MultiCell(60,6,utf8_encode('Representación Impresa de la Factura Electrónica, consulte en https://www.hospitaljuanpablo.pe'),0,'L');
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
                            ->leftjoin('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->where('movimiento.id', '=', $id);
        $resultado        = $resultado->select('movimiento.*','tipodocumento.nombre as tipodocumento','plan.aseguradora','plan.nombre as plan','historia.numero as historia','responsable.nombres as responsable2');
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
                $pdf::Cell(110,6,date("d/m/Y",strtotime($value->fecha)),0,0,'L');
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
                    if($value->sctr=="S"){
                        $pdf::Cell(110,6,(trim("SCTR")),0,0,'L');
                    }else{
                        $pdf::Cell(110,6,(trim($value->plan)),0,0,'L');
                    }
                }
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,6,utf8_encode("Usuario: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,utf8_encode($value->responsable2),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(22,6,utf8_encode("Aseguradora: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(110,6,(trim($value->aseguradora)),0,0,'L');
                $pdf::Ln();
                $val      = Detallemovcaja::leftjoin('servicio', 'servicio.id', '=', 'detallemovcaja.servicio_id')
                            ->where('detallemovcaja.movimiento_id', '=', $id)
                            ->select('detallemovcaja.*')->first();

                // dd($val);
                
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
                $pdf::Cell(20,7,utf8_encode("P. Unit"),1,0,'C');
                $pdf::Cell(20,7,utf8_encode("Total"),1,0,'C');
                $pdf::Cell(20,7,utf8_encode("Empresa"),1,0,'C');
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
                    if($v->servicio_id>0 && $v->servicio->tiposervicio_id==1){
                        $plan = Plan::find($value->plan_id);
                        $pdf::Cell(20,7,($plan->consulta),1,0,'C');
                        $precio=$plan->consulta;
                    }else{
                        if($v->servicio_id>0)
                            $precio = $v->servicio->precio;
                        else
                            $precio = $v->precio;
                        $pdf::Cell(20,7,($precio),1,0,'C');
                    }
                    $pdf::Cell(20,7,number_format(0,2,'.',''),1,0,'C');
                    $pdf::Cell(20,7,number_format($precio*$v->cantidad,2,'.',''),1,0,'C');
                    $pdf::Ln();                    
                }
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(27,6,utf8_encode("Hora de Emision: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,utf8_encode(date_format(date_create($value->created_at),'d/m/Y h:i:s A')),0,0,'L');
                $pdf::Ln();
                $pdf::Output('Prefactura.pdf');
            }
        }
    }

    public function generarNumero(Request $request){
        if($request->input('tipodocumento')=="Boleta"){
            $tipodocumento_id=5;
        }else{
            $tipodocumento_id=4;
        }
        $serie = $request->input('serie') + 0;
        
        $numeracion = Numeracion::where('tipomovimiento_id','=',4)->where('tipodocumento_id','=',$tipodocumento_id)->where('serie','=',$serie)->first();
        if(is_null($numeracion)){
            $numeroventa = Movimiento::NumeroSigue(4,$tipodocumento_id,$serie,'N');
            $numeracion = new Numeracion();
            $numeracion->serie=$serie;
            $numeracion->numero=$numeroventa-1;
            $numeracion->tipomovimiento_id=4;
            $numeracion->tipodocumento_id=$tipodocumento_id;
            $numeracion->save();
        }else{
            $numeroventa = str_pad($numeracion->numero + 1,8,'0',STR_PAD_LEFT);
        }
        //$numeroventa = Movimiento::NumeroSigue(4,$tipodocumento_id,$serie,'N');
        echo $numeroventa;
    }
    
    public function personrucautocompletar($searching)
    {
        $resultado        = Person::where(DB::raw('CONCAT(ruc," ",bussinesname)'), 'LIKE', ''.strtoupper(str_replace("_","",$searching)).'%')->orderBy('ruc', 'ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array(
                            'label' => trim($value->ruc.' '.$value->bussinesname),
                            'id'    => $value->id,
                            'value' => trim($value->bussinesname),
                            'ruc'   => $value->ruc,
                            'razonsocial' => $value->bussinesname,
                            'direccion' => $value->direccion,
                        );
        }
        return json_encode($data);
    }

    public function personrazonautocompletar($searching)
    {
        $resultado        = Person::where('bussinesname', 'LIKE', '%'.strtoupper(str_replace("_","",$searching)).'%')->orderBy('ruc', 'ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array(
                            'label' => trim($value->ruc.' '.$value->bussinesname),
                            'id'    => $value->id,
                            'value' => trim($value->bussinesname),
                            'ruc'   => $value->ruc,
                            'razonsocial' => $value->bussinesname,
                            'direccion' => $value->direccion,
                        );
        }
        return json_encode($data);
    }

    public function agregardetalle(Request $request){
        $resultado        = Detallemovcaja::leftjoin('servicio', 'servicio.id', '=', 'detallemovcaja.servicio_id')
                            ->where('detallemovcaja.movimiento_id', '=', $request->input('id'))
                            ->select('detallemovcaja.*');
        $lista            = $resultado->get();
        $data = array();
        foreach($lista as $k => $v){
            $data[] = array("idservicio"=> $v->servicio_id,
                            "servicio" => ($v->servicio_id>0?$v->servicio->nombre:''),
                            "precio" => $v->precio,
                            "cantidad" => $v->cantidad,
                            "servicio2" => $v->descripcion,
                            "tiposervicio" => ($v->servicio_id>0?$v->servicio->tiposervicio->nombre:'VARIOS'),
                            "idtiposervicio" => ($v->servicio_id>0?$v->servicio->tiposervicio_id:0),
                            "situacionentrega" => $v->situacionentrega,
                            "idmedico" => $v->persona_id,
                            "iddetalle" => $v->id,
                            "medico" => $v->persona->apellidopaterno.' '.$v->persona->apellidomaterno.' '.$v->persona->nombres);
        }
        return json_encode($data);
    }

    public function agregarhojacosto(Request $request){
        $resultado        = Detallehojacosto::leftjoin('servicio', 'servicio.id', '=', 'detallehojacosto.servicio_id')
                            ->join('hojacosto','detallehojacosto.hojacosto_id','=','hojacosto.id')
                            ->join('hospitalizacion','hospitalizacion.id','=','hojacosto.hospitalizacion_id')
                            ->where('hospitalizacion.historia_id', '=', $request->input('id'))
                            // ->where('hojacosto.situacion','like','P')
                            ->select('detallehojacosto.*');
        $lista            = $resultado->get();
        $data = array();
        foreach($lista as $k => $v){
            $data[] = array("idservicio"=> $v->servicio_id,
                            "servicio" => ($v->servicio_id>0?$v->servicio->nombre:$v->descripcion),
                            "precio" => $v->precio,
                            "cantidad" => $v->cantidad,
                            "total" => $v->precio*$v->cantidad,
                            "servicio2" => $v->descripcion,
                            "tiposervicio" => ($v->servicio_id>0?$v->servicio->tiposervicio->nombre:'VARIOS'),
                            "idtiposervicio" => ($v->servicio_id>0?$v->servicio->tiposervicio_id:0),
                            "idmedico" => $v->persona_id,
                            "medico" => $v->persona->apellidopaterno.' '.$v->persona->apellidomaterno.' '.$v->persona->nombres);
        }
        return json_encode($data);
    }
}
