<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Plan;
use App\Kardex;
use App\Tipodocumento;
use App\Movimiento;
use App\Detallemovimiento;
use App\Person;
use App\Servicio;
use App\Caja;
use App\Producto;
use App\Venta;
use App\Banco;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Detallemovcaja;
use App\Librerias\EnLetras;
use Illuminate\Support\Facades\Auth;
use Excel;

class VentaadmisionController extends Controller
{
    protected $folderview      = 'app.ventaadmision';
    protected $tituloAdmin     = 'Venta';
    protected $tituloRegistrar = 'Registrar Venta';
    protected $tituloModificar = 'Modificar Venta';
    protected $tituloCobrar = 'Cobrar Venta';
    protected $tituloAnular  = 'Anular Venta';
    protected $rutas           = array('create' => 'ventaadmision.create', 
            'edit'   => 'ventaadmision.edit', 
            'anular' => 'ventaadmision.anular',
            'search' => 'ventaadmision.buscar',
            'index'  => 'ventaadmision.index',
            'pdfListar'  => 'ventaadmision.pdfListar',
            'procesar'  => 'ventaadmision.procesar',
            'cobrar' => 'ventaadmision.cobrar',
            'pagar' => 'ventaadmision.pagar',
            'excel' => 'ventaadmision.excelConcar',
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
        $entidad          = 'Ventaadmision';
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','N')
                            //->where('movimiento.situacion','<>','U')
                            ->where('m2.tipomovimiento_id','=',1);
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
        }        
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }        
        if($request->input('numero')!=""){
            $resultado = $resultado->where(DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero)'),'LIKE','%'.$request->input('numero').'%');
        }    
        if($request->input('paciente')!=""){
            $resultado = $resultado->where(DB::raw('case when movimiento.tipodocumento_id=5 then concat(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.bussinesname end'),'LIKE','%'.strtoupper($request->input('paciente')).'%');
        }    
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }

        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado BZ', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado Sunat', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Msg. Sunat', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $user = Auth::user();
        $titulo_modificar = $this->tituloModificar;
        $titulo_anular  = $this->tituloAnular;
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_anular', 'titulo_cobrar', 'ruta', 'user'));
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
        $entidad          = 'Ventaadmision';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        $cboTipoDoc = array(''=>'Todos...');
        $rs = Tipodocumento::where('tipomovimiento_id','=','4')->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboTipoDoc = $cboTipoDoc + array($value->id => $value->nombre);
        }
        $cboSituacion = array(''=>'Todos...','U'=>'Anulado');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta','cboTipoDoc', 'user','cboSituacion'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Ventaadmision';
        $Venta = null;
        $cboConvenio = array();
        $convenios = Convenio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($convenios as $key => $value) {
            $cboConvenio = $cboConvenio + array($value->id => $value->nombre);
        }
        $formData            = array('Venta.store');
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar';
        return view($this->folderview.'.mant')->with(compact('Venta', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente', 'cboConvenio'));
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
                'doctor'                  => 'required',
                'especialidad'          => 'required',
                'paciente'          => 'required',
                'numero'          => 'required',
                );
        $mensajes = array(
            'doctor.required'         => 'Debe seleccionar un doctor',
            'especialidad.required'         => 'Debe seleccionar una especialidad',
            'paciente.required'         => 'Debe seleccionar un paciente',
            'numero.required'         => 'Debe seleccionar una historia',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       
        
        $error = DB::transaction(function() use($request){
            $Venta       = new Venta();
            $Venta->fecha = $request->input('fecha');
            $person_id = $request->input('person_id');
            if($person_id==""){
                $person_id = null;
            }
            $historia_id = $request->input('historia_id');
            if($historia_id==""){
                $historia_id = null;
            }
            $Venta->paciente_id = $person_id;
            $Venta->historia_id = $historia_id;
            $Venta->doctor_id = $request->input('doctor_id');
            $Venta->situacion='P';//Pendiente
            $Venta->horainicio = $request->input('horainicio');
            $Venta->horafin = $request->input('horafin');
            $Venta->comentario = $request->input('comentario');
            $Venta->telefono = $request->input('telefono');
            $Venta->paciente = $request->input('paciente');
            $Venta->historia = $request->input('numero');
            $Venta->tipopaciente = $request->input('tipopaciente');
            $Venta->save();
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
        $existe = Libreria::verificarExistencia($id, 'Venta');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $Venta = Venta::find($id);
        $entidad             = 'Venta';
        $formData            = array('Venta.update', $id);
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('Venta', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function anulacion(Request $request)
    {
        $id=$request->input('id');
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id,$user,$request){
            $Venta = Movimiento::find($id);
            $Venta->situacion = 'U';
            $Venta->usuarioentrega_id=$user->person_id;
            $Venta->comentario = $request->input('motivo');
            $Venta->save();

            $Caja = Movimiento::where('movimiento_id','=',$id)->first();
            if(!is_null($Caja)){
                $Caja->situacion = 'A';
                $Caja->save();
            }

            $Ticket = Movimiento::where('id','=',$Venta->movimiento_id)->first();
            if(!is_null($Ticket)){
                $Ticket->situacion = 'U';//Anulada
                $Ticket->save();
            }
            $rs=Detallemovcaja::where("movimiento_id",'=',$Venta->movimiento_id)->get();
            foreach ($rs as $key => $value) {
                $caja = Movimiento::where('listapago','like','%'.$value->id.'%')->whereIn('conceptopago_id',[8,10])->first();
                if(!is_null($caja)){
                    $caja->situacion = 'A';//Anulada
                    $caja->save();
                }
            }
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
        $entidad  = 'Ventaadmision';
        $formData = array('route' => array('ventaadmision.anulacion', $id), 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view($this->folderview.'.anular')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function pagar(Request $request)
    {
        $existe = Libreria::verificarExistencia($request->input('id'), 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
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
    
        $error = DB::transaction(function() use($request, $user){
            $Venta = Movimiento::find($request->input('id'));
            $Venta->situacion ='N';
            $Venta->save();

            $movimiento        = new Movimiento();
            $movimiento->fecha = date("Y-m-d");
            $movimiento->numero= Movimiento::NumeroSigue(2,2);
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$Venta->persona_id;
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=$request->input('total',0); 
            $movimiento->tipomovimiento_id=2;
            $movimiento->tipodocumento_id=2;
            $movimiento->conceptopago_id=23;//COBRANZA
            $movimiento->comentario='Pago de Documento de Venta Credito: '.($Venta->tipodocumento_id==5?'Boleta':'Factura').' '.$Venta->serie.'-'.$Venta->numero;
            $movimiento->caja_id=$request->input('caja_id');
            if($request->input('formapago')=="Tarjeta"){
                $movimiento->tipotarjeta=$request->input('tipotarjeta');
                $movimiento->tarjeta=$request->input('tipotarjeta2');
                $movimiento->voucher=$request->input('nroref');
                $movimiento->totalpagado=0;
            }else{
                if($request->input('formapago')=="Deposito"){
                    $movimiento->idTipoBanco = $request->input('tipoBanco');
                    $movimiento->voucher=$request->input('nro_op');
                    $movimiento->totalpagado= 0;
                }else{
                    $movimiento->totalpagado=$request->input('total',0);
                }
            }
            $movimiento->situacion='N';
            $movimiento->movimiento_id=$Venta->id;
            $movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function cobrar($id, $listarLuego,Request $request)
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
        $entidad  = 'Ventaadmision';
        $cboCaja          = array();
        $resultado        = Caja::where('id','<>',6)->where('id','<>',4)->orderBy('nombre','ASC')->get();
        $caja=0;
        foreach ($resultado as $key => $value) {
            $cboCaja = $cboCaja + array($value->id => $value->nombre);
            if($request->ip()==$value->ip){
                $caja=$value->id;
                $serie=$value->serie;
            }
        }
        $cboFormaPago     = array("Efectivo" => "Efectivo", "Tarjeta" => "Tarjeta", "Deposito" => "Depósito en Cuenta");
        $cboTipoTarjeta    = array("VISA" => "VISA", "MASTER" => "MASTER");
        $cboTipoTarjeta2    = array("CREDITO" => "CREDITO", "DEBITO" => "DEBITO");  
        $cboBanco = Banco::all(); 
        // foreach ($bancos as $key => $value) {
        //     $cboBanco[] = array($value->id => $value->descripcion);
        // }

        $user = Auth::user();
        if($caja==0){//ADMISION 1
            $serie=3;
            $idcaja=1;
        }
        $formData = array('route' => array('ventaadmision.pagar', $id), 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Cobrar';
        return view($this->folderview.'.cobrar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar', 'cboCaja' , 'caja', 'cboFormaPago', 'cboTipoTarjeta2', 'cboTipoTarjeta','cboBanco'));
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
                    if(strlen($value->persona->dni)<>8 && strlen($value->persona->dni)<>9){
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
                    if(isset($v->servicio)){
                        if($v->servicio_id>0){
                            if($v->servicio->tipopago=="Convenio"){

                                //// REVISAR TARIA}FARIO ENDOCRINOLOGIA
                                if (isset($v->servicio->tarifario)) {
                                    $codigo=$v->servicio->tarifario->codigo;
                                    $nombre=$v->servicio->tarifario->nombre;
                                }
                                    
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
        $entidad          = 'Venta';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $doctor           = Libreria::getParam($request->input('doctor'),'');
        $fecha            = Libreria::getParam($request->input('fecha'));
        $resultado        = Venta::leftjoin('person as paciente', 'paciente.id', '=', 'Venta.paciente_id')
                            ->join('person as doctor', 'doctor.id', '=', 'Venta.doctor_id')
                            ->join('especialidad','especialidad.id','=','doctor.especialidad_id')
                            ->leftjoin('historia','historia.id','=','Venta.historia_id')
                            ->where('Venta.paciente', 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'), 'LIKE', '%'.strtoupper($doctor).'%');
        if($fecha!=""){
            $resultado = $resultado->where('Venta.fecha', '=', ''.$fecha.'');
        }
        $resultado        = $resultado->select('Venta.*','historia.tipopaciente as tipopaciente2','especialidad.nombre as especialidad','historia.numero as historia2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres) as doctor'))->orderBy('Venta.fecha', 'ASC')->orderBy(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'),'asc')->orderBy('Venta.horainicio','ASC');
        $lista            = $resultado->get();
        if (count($lista) > 0) {            
            $pdf = new TCPDF();
            $pdf::SetTitle('Lista de Pacientes');
            $pdf::AddPage();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(0,10,utf8_decode("LISTA DE VentaS"),0,0,'C');
            $pdf::Ln();
            $iddoctorant=0;
            foreach ($lista as $key => $value){
                if($iddoctorant!=$value->doctor_id){
                    if($iddoctorant>0){
                        $pdf::Ln();
                    }

                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(17,9,utf8_decode("FECHA:"),0,0,'L');
                    $pdf::SetFont('helvetica','',10);
                    $pdf::Cell(20,9,utf8_decode($value->fecha),0,0,'L');
                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(20,9,utf8_decode("DOCTOR:"),0,0,'L');
                    $pdf::SetFont('helvetica','',10);
                    $pdf::Cell(55,9,($value->doctor),0,0,'L');
                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(30,9,utf8_decode("ESPECIALIDAD:"),0,0,'L');
                    $pdf::SetFont('helvetica','',10);
                    $pdf::Cell(0,9,utf8_decode($value->especialidad),0,0,'L');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(60,6,utf8_decode("PACIENTE"),1,0,'C');
                    $pdf::Cell(20,6,utf8_decode("TIPO PAC."),1,0,'C');
                    $pdf::Cell(23,6,utf8_decode("TELEF."),1,0,'C');
                    $pdf::Cell(18,6,utf8_decode("HISTORIA"),1,0,'C');
                    $pdf::Cell(13,6,utf8_decode("INICIO"),1,0,'C');
                    $pdf::Cell(13,6,utf8_decode("FIN"),1,0,'C');
                    $pdf::Cell(50,6,utf8_decode("CONCEPTO"),1,0,'C');
                    $pdf::Ln();
                    $iddoctorant=$value->doctor_id;
                }
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(60,5,utf8_decode($value->paciente),1,0,'L');
                $pdf::Cell(20,5,utf8_decode($value->tipopaciente),1,0,'C');
                $pdf::Cell(23,5,utf8_decode($value->telefono),1,0,'C');
                $pdf::Cell(18,5,utf8_decode($value->historia),1,0,'C');
                $pdf::Cell(13,5,utf8_decode(substr($value->horainicio,0,5)),1,0,'C');
                $pdf::Cell(13,5,utf8_decode(substr($value->horafin,0,5)),1,0,'C');
                $pdf::Cell(50,5,utf8_decode($value->comentario),1,0,'L');
                $pdf::Ln();
            }
            $pdf::Output('ListaVenta.pdf');
        }
    }

    public function procesar(Request $request)
    {
        try{
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','N')
                            ->where('m2.tipomovimiento_id','=',1);
            if($request->input('fechainicial')!=""){
                $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
            }
            if($request->input('fechafinal')!=""){
                $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
            }        
            if($request->input('tipodocumento')!=""){
                $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
            }        
            if($request->input('numero')!=""){
                $resultado = $resultado->where('movimiento.numero','LIKE','%'.$request->input('numero').'%');
            }        

            $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2')->orderBy('movimiento.fecha', 'ASC');
            $lista            = $resultado->get();
            foreach ($lista as $key => $value) {
                $numero=($value->tipodocumento_id==4?"F":"B").str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT);
                //dd($numero);
                $dias_trascurridos = date_diff(date_create($value->fecha),date_create())->days;
                if(substr($numero, 0, 1) == "B" && $dias_trascurridos <= 7){
                    $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICE_RESPONSE')->where('serieNumero','like',$numero)->where("bl_estadoRegistro","=","L")->count("*");
                    //dd($rs);
                    if($rs>0){
                        DB::connection('sqlsrvtst21')->delete("delete from SPE_EINVOICE_RESPONSE where serieNumero in (?)",[$numero]); 
                        DB::connection('sqlsrvtst21')->update("update SPE_EINVOICEHEADER set bl_estadoRegistro='A',bl_reintento=0 where serieNumero in (?)",[$numero]); 
                    }
                }
                
                //if($value->situacionsunat!="E"){
                    $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICEHEADER')->where('serieNumero','like',$numero)->first();
                  
                    if(count($rs)>0){
                        $value->situacionbz=$rs->bl_estadoRegistro;
                        if($rs->bl_estadoRegistro==='E'){
                            $value->situacionsunat='E';    
                        }
                        // else{
                        //    $value->situacionsunat = $rs->bl_estadoRegistro;             
                        // }
                    }
                    $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICE_RESPONSE')->where('serieNumero','like',$numero)->first();
                    if(count($rs)>0){
                        $value->situacionsunat=$rs->bl_estadoRegistro;
                        $value->mensajesunat=$rs->bl_mensajeSunat;
                    }
                    $value->save();
                      // dd($rs);

                //}
            }
            $error = "OK";
        }catch(\Exception $e){
            $error = $e->getMessage();
        }
        return $error;
    }

    public function procesaroriginal(Request $request)
    {
        $error = DB::transaction(function() use($request){
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','N')
                            ->where('m2.tipomovimiento_id','=',1);
            if($request->input('fechainicial')!=""){
                $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
            }
            if($request->input('fechafinal')!=""){
                $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
            }        
            if($request->input('tipodocumento')!=""){
                $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
            }        
            if($request->input('numero')!=""){
                $resultado = $resultado->where('movimiento.numero','LIKE','%'.$request->input('numero').'%');
            }        

            $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2')->orderBy('movimiento.fecha', 'ASC');
            $lista            = $resultado->get();
            foreach ($lista as $key => $value) {
                $numero=($value->tipodocumento_id==4?"F":"B").str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT);
                //dd($numero);
                $dias_trascurridos = date_diff(date_create($value->fecha),date_create())->days;
                if(substr($numero, 0, 1) == "B" && $dias_trascurridos <= 7){
                    $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICE_RESPONSE')->where('serieNumero','like',$numero)->where("bl_estadoRegistro","=","L")->count("*");
                    //dd($rs);
                    if($rs>0){
                        DB::connection('sqlsrvtst21')->delete("delete from SPE_EINVOICE_RESPONSE where serieNumero in (?)",[$numero]); 
                        DB::connection('sqlsrvtst21')->update("update SPE_EINVOICEHEADER set bl_estadoRegistro='A',bl_reintento=0 where serieNumero in (?)",[$numero]); 
                    }
                }
                
                //if($value->situacionsunat!="E"){
                    $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICEHEADER')->where('serieNumero','like',$numero)->first();
                    if(count($rs)>0){
                        $value->situacionbz=$rs->bl_estadoRegistro;
                        if($rs->bl_estadoRegistro=='E'){
                            $value->situacionsunat='E';    
                        }
                    }
                    $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICE_RESPONSE')->where('serieNumero','like',$numero)->first();
                    if(count($rs)>0){
                        $value->situacionsunat=$rs->bl_estadoRegistro;
                        $value->mensajesunat=$rs->bl_mensajeSunat;
                    }
                    $value->save();
                //}
            }
        });
        return is_null($error) ? "OK" : $error;
    }

    public function resumen(Request $request){
        $error = DB::transaction(function() use($request){
                $fechainicial=$request->input('fechainicial');
                $fechafinal=$request->input('fechafinal');
                while(strtotime($fechainicial)<=strtotime($fechafinal)){
                    //CABECERA
                    $columna1=6;
                    $columna2="20480082673";//RUC HOSPITAL
                    $columna3="RC-".str_replace('-', '', $fechainicial).'-0001';
                    $columna4=$fechainicial;
                    $columna5=$fechainicial;
                    $columna6="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                    $columna7="sistemas@hospitaljuanpablo.pe";
                    $columna8=1;
                    $columna9='N';
                    $columna10='RC';
                    DB::connection('sqlsrv')->insert('insert into SPE_SUMMARYHEADER (
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        resumenId,
                        fechaEmisionComprobante,
                        fechaGeneracionResumen,
                        razonSocialEmisor,
                        correoEmisor,
                        inHabilitado,
                        bl_estadoRegistro,
                        resumenTipo
                        ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                        [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10]);

                    //DETALLES POR SERIE
                    $rs=Movimiento::where('movimiento.tipomovimiento_id','=',4)
                                    ->where('movimiento.ventafarmacia','=','N')
                                    ->where('movimiento.fecha','>=',$fechainicial)
                                    ->where('movimiento.fecha','<=',$fechainicial)
                                    ->orderBy('movimiento.tipodocumento_id','desc')
                                    ->orderBy('movimiento.serie','asc')
                                    ->orderBy('movimiento.numero','asc')->get();
                    $c=0;$serie='';$tipodocumento_id=0;$subtotal=0;$igv=0;$total=0;
                    foreach ($rs as $key => $value) {
                        if($serie!=$value->serie || $tipodocumento_id!=$value->tipodocumento_id){
                            if($serie!=''){
                                $c=$c+1;
                                $columna4=$c;
                                $columna5=$codigo;
                                $columna6='PEN';
                                $columna7=$abreviatura.str_pad($serie,3,'0',STR_PAD_LEFT);
                                $columna8=$inicio;
                                $columna9=$fin;
                                $columna10=$subtotal;
                                $columna11=0;
                                $columna12=0;
                                $columna13=0;
                                $columna14=0;
                                $columna15=$total;
                                $columna16=0;
                                $columna17=$igv;
                                $columna18=0;

                                DB::connection('sqlsrv')->insert('insert into SPE_SUMMARYDETAIL (
                                tipoDocumentoEmisor,
                                numeroDocumentoEmisor,
                                resumenId,
                                numeroFila,
                                tipoDocumento,
                                tipoMoneda,
                                serieGrupoDocumento,
                                numeroCorrelativoInicio,
                                numeroCorrelativoFin,
                                totalValorVentaOpGravadaConIgv,
                                totalValorVentaOpExoneradasIgv,
                                totalValorVentaOpInafectasIgv,
                                totalValorVentaOpGratuitas,
                                totalOtrosCargos,
                                totalVenta,
                                totalIsc,
                                totalIgv,
                                totalOtrosTributos
                                ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                                [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna16, $columna17, $columna18]);

                                $subtotal=0;
                                $igv=0;
                                $total=0;
                            }
                            $serie=$value->serie;
                            $tipodocumento_id=$value->tipodocumento_id;
                            if($value->tipodocumento_id==5){//BOLETA
                                $codigo='03';
                                $abreviatura='B';
                            }else{
                                $codigo='01';
                                $abreviatura='F';
                            }
                            $inicio=str_pad($value->numero,8,'0',STR_PAD_LEFT);
                        }
                        $fin=str_pad($value->numero,8,'0',STR_PAD_LEFT);
                        $subtotal=$subtotal + $value->subtotal;
                        $total=$total + $value->total;
                        $igv=$igv + $value->igv;
                        /*$columna4=$c;
                        if($value->tipodocumento_id==5){//BOLETA
                            $codigo='03';
                            $abreviatura='B';
                            if(strlen($value->persona->dni)<>8 || $value->total<700){
                                $columna8=0;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                                $columna9='-';
                            }else{
                                $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                                $columna9=$value->persona->dni;
                            }
                        }else{
                            $codigo='01';
                            $abreviatura='F';
                            $columna8=6;
                            $columna9=$value->persona->ruc;
                        }
                        $columna5=$codigo
                        $columna6='PEN';
                        $columna7=utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT));


                        DB::connection('sqlsrv')->insert('insert into SPE_SUMMARYITEM” (
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        resumenId,
                        numeroFila,
                        tipoDocumento,
                        tipoMoneda,
                        numeroCorrelativo,
                        tipoDocumentoAdquiriente,
                        numeroDocumentoAdquiriente,
                        numeroCorrBoletaModificada,
                        tipoDocumentoModificado,
                        estadoItem,
                        totalValorVentaOpGravadaConIgv,
                        totalValorVentaOpExoneradasIgv,
                        totalValorVentaOpInafectasIgv,
                        totalValorVentaOpGratuitas,
                        totalOtrosCargos,
                        totalVenta,
                        totalIsc,
                        totalIgv,
                        totalOtrosTributos
                        ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                        [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna16, $columna17, $columna18, $columna19, $columna20, $columna21]);
                        */
                    }

                    $c=$c+1;
                    $columna4=$c;
                    $columna5=$codigo;
                    $columna6='PEN';
                    $columna7=$abreviatura.str_pad($serie,3,'0',STR_PAD_LEFT);
                    $columna8=$inicio;
                    $columna9=$fin;
                    $columna10=$subtotal;
                    $columna11=0;
                    $columna12=0;
                    $columna13=0;
                    $columna14=0;
                    $columna15=$total;
                    $columna16=0;
                    $columna17=$igv;
                    $columna18=0;

                    DB::connection('sqlsrv')->insert('insert into SPE_SUMMARYDETAIL (
                    tipoDocumentoEmisor,
                    numeroDocumentoEmisor,
                    resumenId,
                    numeroFila,
                    tipoDocumento,
                    tipoMoneda,
                    serieGrupoDocumento,
                    numeroCorrelativoInicio,
                    numeroCorrelativoFin,
                    totalValorVentaOpGravadaConIgv,
                    totalValorVentaOpExoneradasIgv,
                    totalValorVentaOpInafectasIgv,
                    totalValorVentaOpGratuitas,
                    totalOtrosCargos,
                    totalVenta,
                    totalIsc,
                    totalIgv,
                    totalOtrosTributos
                    ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna16, $columna17, $columna18]);

                    /*
                        PARA FARMACIA
                    */

                    //DETALLES POR SERIE

                    $rs=Movimiento::where('movimiento.tipomovimiento_id','=',4)
                                    ->where('movimiento.ventafarmacia','=','S')
                                    ->where('movimiento.tipodocumento_id','<>',15)
                                    ->where('movimiento.fecha','>=',$fechainicial)
                                    ->where('movimiento.fecha','<=',$fechainicial)
                                    ->orderBy('movimiento.tipodocumento_id','desc')
                                    ->orderBy('movimiento.serie','asc')
                                    ->orderBy('movimiento.numero','asc')->get();

                    $serie='';$tipodocumento_id=0;$subtotal=0;$igv=0;$total=0;$totalinafecta=0;$inicio='';

                    foreach ($rs as $key => $value) {
                        $ind = 0;
                        $listdetalles = Detallemovimiento::where('movimiento_id','=',$value->id)->get();
                        foreach ($listdetalles as $key3 => $value3) {
                            if ($value3->producto->afecto == 'NO') {
                                $ind = 1;
                            }
                        }
                        if($serie!=$value->serie || $tipodocumento_id!=$value->tipodocumento_id){
                            if($serie!=''){
                                $c=$c+1;
                                $columna4=$c;
                                $columna5=$codigo;
                                $columna6='PEN';
                                $columna7=$abreviatura.str_pad($serie,3,'0',STR_PAD_LEFT);
                                $columna8=$inicio;
                                $columna9=$fin;
                                $columna10=$subtotal;
                                $columna11=0;
                                $columna12=$totalinafecta;
                                $columna13=0;
                                $columna14=0;
                                $columna15=$total;
                                $columna16=0;
                                $columna17=$igv;
                                $columna18=0;
                                
                                


                                DB::connection('sqlsrv')->insert('insert into SPE_SUMMARYDETAIL (
                                tipoDocumentoEmisor,
                                numeroDocumentoEmisor,
                                resumenId,
                                numeroFila,
                                tipoDocumento,
                                tipoMoneda,
                                serieGrupoDocumento,
                                numeroCorrelativoInicio,
                                numeroCorrelativoFin,
                                totalValorVentaOpGravadaConIgv,
                                totalValorVentaOpExoneradasIgv,
                                totalValorVentaOpInafectasIgv,
                                totalValorVentaOpGratuitas,
                                totalOtrosCargos,
                                totalVenta,
                                totalIsc,
                                totalIgv,
                                totalOtrosTributos
                                ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 

                                [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna16, $columna17, $columna18]);

                                $subtotal=0;
                                $igv=0;
                                $total=0;
                                $totalinafecta = 0;

                            }

                            $serie=$value->serie;
                            $tipodocumento_id=$value->tipodocumento_id;
                            if($value->tipodocumento_id==5){//BOLETA
                                $codigo='03';
                                $abreviatura='B';
                            }else{
                                $codigo='01';
                                $abreviatura='F';
                            }
                            $inicio=str_pad($value->numero,8,'0',STR_PAD_LEFT);
                        }

                        $fin=str_pad($value->numero,8,'0',STR_PAD_LEFT);
                        if ($ind == 0) {
                            $subtotal=$subtotal + $value->subtotal;
                            $total=$total + $value->total;
                            $igv=$igv + $value->igv;
                        }else{
                            $totalinafecta=$totalinafecta + $value->total;
                            $total=$total + $value->total;
                        }
                        

                    }


                    $c=$c+1;
                    $columna4=$c;
                    $columna5=$codigo;
                    $columna6='PEN';
                    $columna7=$abreviatura.str_pad($serie,3,'0',STR_PAD_LEFT);
                    $columna8=$inicio;
                    $columna9=$fin;
                    $columna10=$subtotal;
                    $columna11=0;
                    $columna12=$totalinafecta;
                    $columna13=0;
                    $columna14=0;
                    $columna15=$total;
                    $columna16=0;
                    $columna17=$igv;
                    $columna18=0;


                    DB::connection('sqlsrv')->insert('insert into SPE_SUMMARYDETAIL (
                    tipoDocumentoEmisor,
                    numeroDocumentoEmisor,
                    resumenId,
                    numeroFila,
                    tipoDocumento,
                    tipoMoneda,
                    serieGrupoDocumento,
                    numeroCorrelativoInicio,
                    numeroCorrelativoFin,
                    totalValorVentaOpGravadaConIgv,
                    totalValorVentaOpExoneradasIgv,
                    totalValorVentaOpInafectasIgv,
                    totalValorVentaOpGratuitas,
                    totalOtrosCargos,
                    totalVenta,
                    totalIsc,
                    totalIgv,
                    totalOtrosTributos
                    ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 

                    [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna16, $columna17, $columna18]);

                    DB::connection('sqlsrv')->update('update SPE_SUMMARYHEADER set bl_estadoRegistro = ? where resumenId  = ?',
                            ['A',$columna3]);

                    $fechainicial=date("Y-m-d",strtotime('+1 day',strtotime($fechainicial)));
                }
            });
        return is_null($error) ? "OK" : $error;

    }

    public function resumen1(Request $request){
        $error = DB::transaction(function() use($request){
            /*$lista = Movimiento::where('tipodocumento_id','=',5)
                        ->where('tipomovimiento_id','=',4)
                        ->whereIn('fecha',['2018-01-10','2018-01-12','2018-01-17'])
                        ->where('serie','=','4')
                        ->select('*')
                        ->orderBy('fecha','asc')
                        ->orderBy('serie','asc')
                        ->orderBy('numero','asc')
                        ->get();
            foreach ($lista as $key => $value) {
                if($value->serie!="4"){
                    $venta        = new Movimiento();
                    $venta->fecha = $value->fecha;
                    $numeroventa = Movimiento::NumeroSigue(4,5,3,'N');
                    $venta->numero=$numeroventa;
                    $venta->serie = '3';
                    $venta->responsable_id=$value->responsable_id;
                    $venta->persona_id=$value->persona_id;
                    $venta->subtotal=$value->subtotal;
                    $venta->igv=$value->igv;
                    $venta->total=$value->total;     
                    $venta->tipomovimiento_id=4;
                    $venta->tipodocumento_id=5;
                    $venta->comentario='';
                    $venta->manual='N';
                    $venta->situacion=$value->situacion;        
                    $venta->descuentoplanilla=$value->descuentoplanilla;
                    $venta->personal_id=$value->personal_id;
                    $venta->movimiento_id=$value->movimiento_id;
                    $venta->ventafarmacia='N';
                    $venta->save();
                    
                    $caja=Movimiento::where('movimiento_id','=',$value->id)
                            ->where('tipomovimiento_id','=',2)
                            ->first();
                    if(count($caja)>0){
                        $caja->movimiento_id=$venta->id;
                        $caja->save();
                    }
                    $value->situacion='U';
                    $venta->save();
                }else{
                    $venta  = new Movimiento();
                    $venta->serie = 4;
                    $venta->tipodocumento_id = 5;
                    $venta->persona_id = $value->persona_id;
                    $venta->nombrepaciente = $value->nombrepaciente;
                    $venta->empresa_id = $value->empresa_id;
                    $venta->tipomovimiento_id = 4;
                    $venta->almacen_id = 1;
                    
                    $numeroventa = Movimiento::NumeroSigue(4,5,4,'N');
                    $venta->numero=$numeroventa;
                    $venta->fecha  = $value->fecha;
                    $venta->subtotal=$value->total;
                    $venta->igv=$value->igv;
                    $venta->total = $value->total;
                    $venta->credito = $value->credito;
                    $venta->tipoventa = $value->tipoventa;
                    $venta->formapago = $value->formapago;
                    $venta->tarjeta=$value->tipotarjeta;//VISA/MASTER
                    $venta->tipotarjeta=$value->tipotarjeta2;//DEBITO/CREDITO
                    $venta->conveniofarmacia_id = $value->conveniofarmacia_id;
                    $venta->descuentokayros = $value->descuentokayros;
                    $venta->copago = $value->copago;
                           
                    $venta->inicial = 'N';
                    $venta->estadopago = $value->estadopago;
                    $venta->ventafarmacia = 'S';
                    $venta->manual='N';
                    $venta->descuentoplanilla = $value->descuentoplanilla;
                    $venta->responsable_id = $value->responsable_id;
                    $venta->doctor_id = $value->doctor_id;
                    $venta->save();
                    
                    $resultado1 = Detallemovimiento::where('detallemovimiento.movimiento_id','=',$value->id)
                                ->select('Detallemovimiento.*');
                    $lista1      = $resultado1->get();
                    foreach ($lista1 as $k => $v) {
                        $detalleVenta = new Detallemovimiento();
                        $detalleVenta->cantidad = $v->cantidad;
                        $detalleVenta->precio = $v->precio;
                        $detalleVenta->subtotal = $v->subtotal;
                        $detalleVenta->movimiento_id = $venta->id;
                        $detalleVenta->producto_id = $v->producto_id;
                        $detalleVenta->save();

                        //$kardex = Kardex::where('detallemovimiento_id','=',$v->id)
                        //            ->select('*')
                        //            ->get();
                        //$kardex = Kardex::where('producto_id','=',$v->producto_id)
                        //            ->where('fecha','=',$value->fecha)
                        //           ->where('cantidad',$v->cantidad)
                        //            ->where('tipo','like','S')
                        //            ->select('*')
                        //            ->get();
                        //foreach ($kardex as $key1 => $value1) {
                        //    $value1->detallemovimiento_id=$detalleVenta->id;
                        //    $value1->save();
                        //}
                    }
                    //$caja=Movimiento::where('movimiento_id','=',$value->id)
                    //        ->where('tipomovimiento_id','=',2)
                    //        ->first();
                    if($value->persona_id>0)
                        $caja=Movimiento::where('caja_id','=',4)
                            ->where('tipomovimiento_id','=',2)
                            ->where('tipomovimiento_id','=',5)
                            ->where('fecha','=',$value->fecha)
                            ->where('total','=',$value->total)
                            ->where('persona_id','=',$value->persona_id)
                            ->first();                            
                    else
                        $caja=Movimiento::where('caja_id','=',4)
                            ->where('tipomovimiento_id','=',2)
                            ->where('tipomovimiento_id','=',5)
                            ->where('fecha','=',$value->fecha)
                            ->where('total','=',$value->total)
                            ->where('nombrepaciente','like',$value->nombrepaciente)
                            ->first();                            
                    if(count($caja)>0){
                        $caja->movimiento_id=$venta->id;
                        $caja->numero=$venta->numero;
                        $caja->save();
                    }
                    $value->situacion='U';
                    $value->save();
                }
            }*/
            /*$lista = Movimiento::where('tipodocumento_id','=',13)
                        ->where('tipomovimiento_id','=',6)
                        ->whereIn('fecha',['2018-01-10','2018-01-12','2018-01-17'])
                        ->where('fecha','>=','2018-01-01')
                        ->where('fecha','<=','2018-01-31')
                        ->whereIn('situacionsunat',['L','R'])
                        ->select('*')
                        ->orderBy('fecha','asc')
                        ->orderBy('serie','asc')
                        ->orderBy('numero','asc')
                        ->get();
            foreach ($lista as $key => $value) {
                $Movimiento = new Movimiento();
                $Movimiento->fecha = date("Y-m-d");
                $Movimiento->serie = 2;
                $numero = Movimiento::NumeroSigue(6,13,2,'N');
                $Movimiento->numero = $numero;
                $Movimiento->persona_id = $value->persona_id;
                $Movimiento->total = $value->total;
                $Movimiento->subtotal = $value->subtotal;
                $Movimiento->igv = $value->igv;
                $Movimiento->responsable_id=$value->responsable_id;
                $Movimiento->movimiento_id = $value->movimiento_id;
                $Movimiento->situacion='N';//Normal
                $Movimiento->tipomovimiento_id = 6;
                $Movimiento->tipodocumento_id = 13;
                $Movimiento->comentario = $value->comentario;
                $Movimiento->manual='N';
                $Movimiento->save();
                $resultado1 = Detallemovcaja::where('movimiento_id','=',$value->id)
                                ->select('*');
                    $lista1      = $resultado1->get();
                foreach ($lista1 as $k => $v) {
                    $Detalle = new Detallemovcaja();
                    $Detalle->movimiento_id=$Movimiento->id;
                    $Detalle->persona_id=$v->persona_id;
                    $Detalle->cantidad=$v->cantidad;
                    $Detalle->precio=$v->precio;
                    $Detalle->servicio_id=$v->servicio_id;
                    $Detalle->pagohospital=$v->pagohospital;
                    $Detalle->descripcion=$v->descripcion;
                    $Detalle->descuento=0;
                    $Detalle->save();
                }
                $caja=Movimiento::where('tipomovimiento_id','=',2)
                    ->where('tipomovimiento_id','=',5)
                    ->where('fecha','=',$value->fecha)
                    ->where('total','=',$value->total)
                    ->where('movimiento_id','=',$value->id)
                    ->first();                            
                if(count($caja)>0){
                    $caja->movimiento_id=$Movimiento->id;
                    $caja->save();
                }
                $value->situacion='U';
                $value->save();
            }*/
            for($c=617;$c<=865;$c++){
                $nota = Movimiento::join('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                        ->where('movimiento.numero','=',$c)
                        ->where('movimiento.serie','=',2)
                        ->where('movimiento.tipodocumento_id','=',13)
                        ->whereIn('m2.tipodocumento_id',[4,17])
                        ->whereIn('m2.tipomovimiento_id',[4,9])
                        ->select('movimiento.fecha')
                        ->first();
                if(!is_null($nota)){
                    $fecha=$nota->fecha;
                }else{
                    $venta  = new Movimiento();
                    $venta->serie = 2;
                    $venta->tipodocumento_id = 13;
                    $venta->persona_id = 57371;
                    $venta->tipomovimiento_id = 6;
                    
                    $numeroventa = $c;
                    $venta->numero=$numeroventa;
                    $venta->fecha  = $fecha;
                    $venta->subtotal=0;
                    $venta->igv=0;
                    $venta->total = 0;
                    $venta->comentario = 'Anulacion de la operacion';
                    $venta->situacion = 'N';
                    $venta->movimiento_id = 148032;
                    $venta->formapago='MI';
                           
                    $venta->manual='N';
                    $venta->responsable_id = 1;
                    $venta->save();
                    
                    $Detalle = new Detallemovcaja();
                    $Detalle->movimiento_id=$venta->id;
                    $Detalle->persona_id=294;
                    $Detalle->cantidad=1;
                    $Detalle->precio=0;
                    $Detalle->servicio_id=12244;
                    $Detalle->pagohospital=0;
                    $Detalle->descuento=0;
                    $Detalle->save();
                }
            }

        });
        return is_null($error) ? "OK" : $error;
    }

    public function ventaautocompletar($searching)
    {
        $resultado        = Movimiento::where(DB::raw('CONCAT(case when tipodocumento_id=4 or tipodocumento_id=17 then "F" else "B" end,serie,"-",numero)'), 'LIKE', '%'.trim($searching).'%')
                            ->where('ventafarmacia','=','N')
                            ->whereNotIn('situacion',['A','U'])
                            ->orderBy('serie', 'ASC')
                            ->orderBy('numero', 'ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            if($value->tipodocumento_id=="4"){
                $paciente=$value->persona->bussinesname;
                $paciente2="";
            }else{
                if($value->tipodocumento_id=="17"){
                    $paciente=$value->empresa->bussinesname;
                    $paciente2=$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                }else{
                    $paciente=$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                    $paciente2=$paciente;
                }
            }

            $data[] = array(
                            'label' => ($value->tipodocumento_id=='4'?'F':($value->tipodocumento_id=='17')?'F':'B').trim($value->serie.'-'.$value->numero),
                            'id'    => $value->id,
                            'value' => ($value->tipodocumento_id=='4'?'F':($value->tipodocumento_id=='17')?'F':'B').trim($value->serie.'-'.$value->numero),
                            'paciente'   => $paciente,
                            'paciente2' => $paciente2,
                            'person_id' => $value->tipodocumento_id=='17'?$value->empresa_id:$value->persona_id,
                            'value2' => ($value->tipodocumento_id=='4'?'F':($value->tipodocumento_id=='17')?'F':'B').trim($value->serie.'-'.$value->numero).' | '.$value->total,
                            'total' => $value->total,
                        );
        }
        return json_encode($data);
    }

    public function excelConcar(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','N')
                            ->where('m2.tipomovimiento_id','=',1);
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
        }        

        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','m2.tarjeta as tarjeta2',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'))->orderBy('movimiento.fecha', 'ASC')->get();

        Excel::create('ExcelVentaConcar', function($excel) use($resultado,$request) {
 
            $excel->sheet('Ventas', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Sub Diario";
                $cabecera[] = "Nro. Comprobante";
                $cabecera[] = "Fecha Comprobante";
                $cabecera[] = "Codigo Moneda";
                $cabecera[] = "Glosa Principal";
                $cabecera[] = "Tipo de Cambio";
                $cabecera[] = "Tipo de Conversion";
                $cabecera[] = "Flag de Conversion";
                $cabecera[] = "Fecha de Tipo de Cambio";
                $cabecera[] = "Cuenta Contable";
                $cabecera[] = "Codigo Anexo";
                $cabecera[] = "Codig Centro Costo";
                $cabecera[] = "Debe / Haber";
                $cabecera[] = "Importe Original";
                $cabecera[] = "Importe Dolares";
                $cabecera[] = "Importe Soles";
                $cabecera[] = "Tipo Documento";
                $cabecera[] = "Nro Documento";
                $cabecera[] = "Fecha Documento";
                $cabecera[] = "Fecha Vencimiento";
                $cabecera[] = "Codig Area";
                $cabecera[] = "Glosa Detalle";
                $cabecera[] = "Codigo Anexo Auxiliar";
                $cabecera[] = "Medio Pago";
                $cabecera[] = "Tipo de Documento de Referencia";
                $cabecera[] = "Nro Documento de Referencia";
                $cabecera[] = "Fecha Documento de Referencia";
                $cabecera[] = "Nro Maq. Registradora";
                $cabecera[] = "Base Imponible Doc. Referencia";
                $cabecera[] = "IGV Documento Provision";
                $cabecera[] = "Tipo de Referencia en Estado";
                $cabecera[] = "Nro de Serie de Caja Registradora";
                $cabecera[] = "Fecha de Operacion";
                $cabecera[] = "Tipo de Tasa";
                $cabecera[] = "Tasa de Detraccion/Percepcion";
                $cabecera[] = "Importe Base Detraccion/Percepcion Dolares";
                $cabecera[] = "Importe Base Detraccion/Percepcion Soles";
                $cabecera[] = "Tipo de Cambio para F";
                $cabecera[] = "Importe de IGV sin derecho a Credito Fiscal";
                $array[] = $cabecera;
                $c=1;$d=3;
                foreach ($resultado as $key => $value){
                    $detalle = Detallemovcaja::where('movimiento_id','=',$value->movimiento_id)->first();
                    if($detalle->servicio_id>0){
                        $glosa=$detalle->servicio->tiposervicio->nombre;
                    }else{
                        $glosa='VARIOS';
                    }
                    $detalle = array();
                    $detalle[] = "05";
                    $detalle[] = date('m',strtotime($value->fecha)).str_pad($c,4,'0',STR_PAD_LEFT);
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "MN";
                    $detalle[] = "VENTAS ".strtoupper(strftime("%B",strtotime($value->fecha)))." DEL ".date("Y",strtotime($value->fecha));
                    $detalle[] = "0";
                    $detalle[] = "M";
                    $detalle[] = "S";
                    $detalle[] = "";
                    $detalle[] = "401111";
                    $person = Person::find($value->persona_id);
                    if ($person !== null) {
                        if(strlen($person->dni)<>8 || $value->total<700){
                            $detalle[] = "0000";
                        }else{
                            if($value->tipodocumento_id==5)//boleta
                                $detalle[] = $person->dni;
                            else
                                $detalle[] = $person->ruc;
                        }
                    }else{
                        $detalle[] = "0000";
                    }
                    
                    $detalle[] = "";
                    $detalle[] = "H";
                    $detalle[] = $value->igv;
                    $detalle[] = "=O".($d)."/F".($d);
                    $detalle[] = "";
                    $detalle[] = $value->tipodocumento_id==5?'BV':'FT';
                    $detalle[] = $value->serie.'-'.$value->numero;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = $glosa;
                    $person = Person::find($value->persona_id);
                    if ($person !== null) {
                        if(strlen($person->dni)<>8 || $value->total<700){
                            $detalle[] = "0000";
                        }else{
                            if($value->tipodocumento_id==5)//boleta
                                $detalle[] = $person->dni;
                            else
                                $detalle[] = $person->ruc;
                        }
                    }else{
                        $detalle[] = "0000";
                    }
                    $array[] = $detalle;
                    $d=$d+1;

                    //SUBTOTAL
                    $detalle = array();
                    $detalle[] = "05";
                    $detalle[] = date('m',strtotime($value->fecha)).str_pad($c,4,'0',STR_PAD_LEFT);
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "MN";
                    $detalle[] = "VENTAS ".strtoupper(strftime("%B",strtotime($value->fecha)))." DEL ".date("Y",strtotime($value->fecha));
                    $detalle[] = "0";
                    $detalle[] = "M";
                    $detalle[] = "S";
                    $detalle[] = "";
                    $detalle[] = "70321";
                    $person = Person::find($value->persona_id);
                    $person = Person::find($value->persona_id);
                    if ($person != null) {
                        if(strlen($person->dni)<>8 || $value->total<700){
                            $detalle[] = "0000";
                        }else{
                            if($value->tipodocumento_id==5)//boleta
                                $detalle[] = $person->dni;
                            else
                                $detalle[] = $person->ruc;
                        }
                    }else{
                        $detalle[] = "0000";
                    }
                    $detalle[] = "";
                    $detalle[] = "H";
                    $detalle[] = $value->subtotal;
                    $detalle[] = "=O".($d)."/F".($d);
                    $detalle[] = "";
                    $detalle[] = '';
                    $detalle[] = $value->serie.'-'.$value->numero;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = $glosa;
                    $person = Person::find($value->persona_id);
                    if ($person != null) {
                        if(strlen($person->dni)<>8 || $value->total<700){
                            $detalle[] = "0000";
                        }else{
                            if($value->tipodocumento_id==5)//boleta
                                $detalle[] = $person->dni;
                            else
                                $detalle[] = $person->ruc;
                        }
                    }else{
                        $detalle[] = "0000";
                    }
                    $array[] = $detalle;
                    $d=$d+1;

                    //TOTAL
                    $detalle = array();
                    $detalle[] = "05";
                    $detalle[] = date('m',strtotime($value->fecha)).str_pad($c,4,'0',STR_PAD_LEFT);
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "MN";
                    $detalle[] = "VENTAS ".strtoupper(strftime("%B",strtotime($value->fecha)))." DEL ".date("Y",strtotime($value->fecha));
                    $detalle[] = "0";
                    $detalle[] = "M";
                    $detalle[] = "S";
                    $detalle[] = "";
                    if($value->situacion=='P'){
                        $detalle[] = "121204";//VENTA CREDITO
                    }else{
                        if($value->tarjeta!=""){
                            $detalle[] = "121205";//VENTA TARJETA
                        }else{
                            $detalle[] = "121203";//VENTA CONTADO
                        }
                    }
                    $person = Person::find($value->persona_id);
                    if ($person != null) {
                        if(strlen($person->dni)<>8 || $value->total<700){
                            $detalle[] = "0000";
                        }else{
                            if($value->tipodocumento_id==5)//boleta
                                $detalle[] = $person->dni;
                            else
                                $detalle[] = $person->ruc;
                        }
                    }else{
                        $detalle[] = "0000";
                    }
                    
                    $detalle[] = "";
                    $detalle[] = "D";
                    $detalle[] = $value->total;
                    $detalle[] = "=O".($d)."/F".($d);
                    $detalle[] = "";
                    $detalle[] = $value->tipodocumento_id==5?'BV':'FT';
                    $detalle[] = $value->serie.'-'.$value->numero;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "";
                    $detalle[] = $glosa;
                    $person = Person::find($value->persona_id);
                    if ($person != null) {
                        if(strlen($person->dni)<>8 || $value->total<700){
                            $detalle[] = "0000";
                        }else{
                            if($value->tipodocumento_id==5)//boleta
                                $detalle[] = $person->dni;
                            else
                                $detalle[] = $person->ruc;
                        }
                    }else{
                        $detalle[] = "0000";
                    }
                    
                    $array[] = $detalle;
                    $c=$c+1;
                    $d=$d+1;
                }

                $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','S')
                            ->where('m2.tipomovimiento_id','=',2);
                if($request->input('fechainicial')!=""){
                    $resultado2 = $resultado2->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
                }
                if($request->input('fechafinal')!=""){
                    $resultado2 = $resultado2->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
                }        

                $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2','m2.tarjeta as tarjeta2',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'))->orderBy('movimiento.fecha', 'ASC')->get();

                foreach ($resultado2 as $key => $value){
                    $detalle = Detallemovcaja::where('movimiento_id','=',$value->movimiento_id)->first();
                    if($detalle->servicio_id>0){
                        $glosa=$detalle->servicio->tiposervicio->nombre;
                    }else{
                        $glosa='VARIOS';
                    }
                    $detalle = array();
                    $detalle[] = "05";
                    $detalle[] = date('m',strtotime($value->fecha)).str_pad($c,4,'0',STR_PAD_LEFT);
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "MN";
                    $detalle[] = "VENTAS ".strtoupper(strftime("%B",strtotime($value->fecha)))." DEL ".date("Y",strtotime($value->fecha));
                    $detalle[] = "0";
                    $detalle[] = "M";
                    $detalle[] = "S";
                    $detalle[] = "";
                    $detalle[] = "401111";
                    $person = Person::find($value->persona_id);
                    if ($person !== null) {
                        if(strlen($person->dni)<>8 || $value->total<700){
                            $detalle[] = "0000";
                        }else{
                            if($value->tipodocumento_id==5)//boleta
                                $detalle[] = $person->dni;
                            else
                                $detalle[] = $person->ruc;
                        }
                    }else{
                        $detalle[] = "0000";
                    }
                    
                    $detalle[] = "";
                    $detalle[] = "H";
                    $detalle[] = $value->igv;
                    $detalle[] = "=O".($d)."/F".($d);
                    $detalle[] = "";
                    $detalle[] = $value->tipodocumento_id==5?'BV':'FT';
                    $detalle[] = $value->serie.'-'.$value->numero;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = $glosa;
                    $person = Person::find($value->persona_id);
                    if ($person !== null) {
                        if(strlen($person->dni)<>8 || $value->total<700){
                            $detalle[] = "0000";
                        }else{
                            if($value->tipodocumento_id==5)//boleta
                                $detalle[] = $person->dni;
                            else
                                $detalle[] = $person->ruc;
                        }
                    }else{
                        $detalle[] = "0000";
                    }
                    $array[] = $detalle;
                    $d=$d+1;

                    //SUBTOTAL
                    $detalle = array();
                    $detalle[] = "05";
                    $detalle[] = date('m',strtotime($value->fecha)).str_pad($c,4,'0',STR_PAD_LEFT);
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "MN";
                    $detalle[] = "VENTAS ".strtoupper(strftime("%B",strtotime($value->fecha)))." DEL ".date("Y",strtotime($value->fecha));
                    $detalle[] = "0";
                    $detalle[] = "M";
                    $detalle[] = "S";
                    $detalle[] = "";
                    $detalle[] = "70321";
                    $person = Person::find($value->persona_id);
                    $person = Person::find($value->persona_id);
                    if ($person != null) {
                        if(strlen($person->dni)<>8 || $value->total<700){
                            $detalle[] = "0000";
                        }else{
                            if($value->tipodocumento_id==5)//boleta
                                $detalle[] = $person->dni;
                            else
                                $detalle[] = $person->ruc;
                        }
                    }else{
                        $detalle[] = "0000";
                    }
                    $detalle[] = "";
                    $detalle[] = "H";
                    $detalle[] = $value->subtotal;
                    $detalle[] = "=O".($d)."/F".($d);
                    $detalle[] = "";
                    $detalle[] = '';
                    $detalle[] = $value->serie.'-'.$value->numero;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = $glosa;
                    $person = Person::find($value->persona_id);
                    if ($person != null) {
                        if(strlen($person->dni)<>8 || $value->total<700){
                            $detalle[] = "0000";
                        }else{
                            if($value->tipodocumento_id==5)//boleta
                                $detalle[] = $person->dni;
                            else
                                $detalle[] = $person->ruc;
                        }
                    }else{
                        $detalle[] = "0000";
                    }
                    $array[] = $detalle;
                    $d=$d+1;

                    //TOTAL
                    $detalle = array();
                    $detalle[] = "05";
                    $detalle[] = date('m',strtotime($value->fecha)).str_pad($c,4,'0',STR_PAD_LEFT);
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "MN";
                    $detalle[] = "VENTAS ".strtoupper(strftime("%B",strtotime($value->fecha)))." DEL ".date("Y",strtotime($value->fecha));
                    $detalle[] = "0";
                    $detalle[] = "M";
                    $detalle[] = "S";
                    $detalle[] = "";
                    if($value->situacion=='P'){
                        $detalle[] = "121204";//VENTA CREDITO
                    }else{
                        if($value->tarjeta!=""){
                            $detalle[] = "121205";//VENTA TARJETA
                        }else{
                            $detalle[] = "121203";//VENTA CONTADO
                        }
                    }
                    $person = Person::find($value->persona_id);
                    if ($person != null) {
                        if(strlen($person->dni)<>8 || $value->total<700){
                            $detalle[] = "0000";
                        }else{
                            if($value->tipodocumento_id==5)//boleta
                                $detalle[] = $person->dni;
                            else
                                $detalle[] = $person->ruc;
                        }
                    }else{
                        $detalle[] = "0000";
                    }
                    
                    $detalle[] = "";
                    $detalle[] = "D";
                    $detalle[] = $value->total;
                    $detalle[] = "=O".($d)."/F".($d);
                    $detalle[] = "";
                    $detalle[] = $value->tipodocumento_id==5?'BV':'FT';
                    $detalle[] = $value->serie.'-'.$value->numero;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = "";
                    $detalle[] = $glosa;
                    $person = Person::find($value->persona_id);
                    if ($person != null) {
                        if(strlen($person->dni)<>8 || $value->total<700){
                            $detalle[] = "0000";
                        }else{
                            if($value->tipodocumento_id==5)//boleta
                                $detalle[] = $person->dni;
                            else
                                $detalle[] = $person->ruc;
                        }
                    }else{
                        $detalle[] = "0000";
                    }
                    
                    $array[] = $detalle;
                    $c=$c+1;
                    $d=$d+1;
                }

                $sheet->fromArray($array);
 
            });
        })->export('xls');
    }

    public function excelSunatConvenio(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->where('movimiento.tipodocumento_id','=',14);
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
        }        

        $resultado        = $resultado->select('movimiento.*',DB::raw('CONCAT("F",movimiento.serie,"-",movimiento.numero) as numero2'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->get();

        Excel::create('ExcelVentaSunatCovenio', function($excel) use($resultado,$request) {
 
            $excel->sheet('VentasConvenio', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "";
                $cabecera[] = "Codigo";
                $cabecera[] = "Fecha Emision";
                $cabecera[] = "Tipo de Comprob";
                $cabecera[] = "Serie";
                $cabecera[] = "Numero";
                $cabecera[] = "Ticket Sin IGv";
                $cabecera[] = "Tipo de Documento";
                $cabecera[] = "Numero de Dcto Ident";
                $cabecera[] = "Apellidos,Nombres,RAzon Social";
                $cabecera[] = "Total valor venta operac gravadas";
                $cabecera[] = "Total valor venta operac exoneradas";
                $cabecera[] = "Total valor venta operac. inafectas";
                $cabecera[] = "ISC";
                $cabecera[] = "IGV";
                $cabecera[] = "OTROS TRIBUTOS";
                $cabecera[] = "IMPORTE TOTAL";
                $cabecera[] = "TIPO DCTO RELACIONADO";
                $cabecera[] = "SERIE DCTO RELACIONADO";
                $cabecera[] = "N° DCTO RELACIONADO ";
                $cabecera[] = "FORMULA";
                $array[] = $cabecera;
                $c=3;$d=3;$band=true;

                foreach ($resultado as $key => $value){
                    if($value->situacion!="U"){//NO ANULADAS
                        $detalle = array();
                        $detalle[] = "";
                        $detalle[] = "7";
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        $detalle[] = $value->tipodocumento_id==5?'03':'01';
                        $detalle[] = str_pad($value->serie,4,'0',STR_PAD_LEFT);
                        $detalle[] = str_pad($value->numero,4,'0',STR_PAD_LEFT);
                        $detalle[] = "";
                        $person = Person::find($value->persona_id);
                        if($value->tipodocumento_id==5){//boleta
                            if(strlen($person->dni)<>8 || $value->total<700){
                                $detalle[] = "0";
                                $detalle[] = "0";
                                $detalle[] = "CLIENTES VARIOS";
                            }else{
                                $detalle[] = "1";
                                $detalle[] = $person->dni;
                                $detalle[] = $person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres;
                            }
                        }else{
                            $detalle[] = "6";
                            $detalle[] = $person->ruc;
                            $detalle[] = $person->bussinesname;
                            
                        }
                        $detalle[] = number_format($value->subtotal,2,'.','');
                        $detalle[] = "0.00";
                        $detalle[] = "0.00";
                        $detalle[] = "0.00";
                        $detalle[] = number_format($value->igv,2,'.','');
                        $detalle[] = "0.00";
                        $detalle[] = number_format($value->total,2,'.','');
                        $detalle[] = "";
                        $detalle[] = '';
                        $detalle[] = "";
                        $detalle[] = '=CONCATENATE(B'.$c.',"|",C'.$c.',"|",D'.$c.',"|",E'.$c.',"|",F'.$c.',"|",G'.$c.',"|",H'.$c.',"|",I'.$c.',"|",J'.$c.',"|",ROUND(K'.$c.',2),"|",ROUND(L'.$c.',2),"|",ROUND(M'.$c.',2),"|",ROUND(N'.$c.',2),"|",ROUND(O'.$c.',2),"|",ROUND(P'.$c.',2),"|",ROUND(Q'.$c.',2),"|",R'.$c.',"|",S'.$c.',"|",T'.$c.',"|")';
                        $c=$c+1;
                        $array[] = $detalle;
                    }
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function excelVentaConvenio(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('person as empresa','empresa.id','=','movimiento.empresa_id')
                            ->where('movimiento.tipodocumento_id','=',17);
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
        }        

        $resultado        = $resultado->select('movimiento.*',DB::raw('CONCAT("F",movimiento.serie,"-",movimiento.numero) as numero2'),'empresa.bussinesname as empresa2','empresa.ruc as ruc2')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->get();

        Excel::create('ExcelVentaCovenio', function($excel) use($resultado,$request) {
 
            $excel->sheet('VentasConvenio', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Tipo";
                $cabecera[] = "Numero";
                $cabecera[] = "Cliente";
                $cabecera[] = "RUC";
                $cabecera[] = "Situacion";
                $cabecera[] = "CTA";
                $cabecera[] = "CTA";
                $cabecera[] = "CTA";
                $cabecera[] = "Subtotal";
                $cabecera[] = "IGV";
                $cabecera[] = "Total";
                $cabecera[] = "Glosa";
                $cabecera[] = "SERVICIO";
                $cabecera[] = "USUARIO";
                $cabecera[] = "ESTADO";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;
                $subtotal=0;
                $igv=0;
                $total=0;

                foreach ($resultado as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                    $detalle[] = 'FT';
                    $detalle[] = 'F'.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.$value->numero;
                    $detalle[] = $value->empresa2;
                    $detalle[] = $value->ruc2;
                    $detalle[] = "CREDITO";
                    $rs=Detallemovcaja::where('movimiento_id','=',$value->id)
                            ->where(function($q){
                                $q->where('descripcion','like','%FARMACIA%')
                                  ->orWhere('descripcion','like','MEDICINA%')
                                  ->orWhere('descripcion','like','%MEDICAMENTO%');

                            })->get();
                    $farmacia=0;
                    if(count($rs)>0){
                        foreach ($rs as $k => $v) {
                            $farmacia=$farmacia + round($v->cantidad*$v->precio/1.18,2);
                        }
                        if(round($value->total/1.18,2)>$farmacia){
                            $value->subtotal = $value->subtotal - $farmacia;
                            $servicio='SERVICIOS';
                            $cuenta='70321';
                        }else{
                            $farmacia=0;
                            $servicio='FARMACIA';
                            $cuenta='70121';
                        }
                    }else{
                        $servicio='SERVICIOS';
                        $cuenta='70321';
                    }
                    $detalle[] = $cuenta;
                    $detalle[] = "121206";
                    $detalle[] = "401111";
                    if($value->situacionsunat!='E' && $value->situacion!="U"){
                        $detalle[] = number_format($value->subtotal,2,'.','');
                        $detalle[] = number_format($value->igv,2,'.','');
                        $detalle[] = number_format($value->total,2,'.','');
                        $subtotal=$subtotal+number_format($value->subtotal,2,'.','');
                        $igv=$igv+number_format($value->igv,2,'.','');
                        $total=$total+number_format($value->total,2,'.','');
                    }else{
                        $detalle[] = 0;
                        $detalle[] = 0;
                        $detalle[] = 0;
                    }
                    $detalle[] = "VENTAS CONVENIOS ".strtoupper(strftime("%B",strtotime($value->fecha)))." DEL ".date("Y",strtotime($value->fecha));
                    $detalle[] = $servicio;
                    $detalle[] = $value->responsable->nombres;
                    /*if($value->situacion=='A')
                        $sunat = 'NOTA DE CREDITO';
                    else*/if($value->situacion=='U')
                        $sunat = 'ANULADO';
                    elseif($value->situacionsunat=='L')
                        $sunat = 'PENDIENTE RESPUESTA';
                    elseif($value->situacionsunat=='R')
                        $sunat = 'RECHAZADO';
                    elseif($value->situacionsunat=='E')
                        $sunat = 'ERROR';
                    elseif($value->situacionsunat=='P')
                        $sunat = 'ACEPTADO';
                    else
                        $sunat = 'PENDIENTE';
                    $detalle[] = $sunat;
                    $sheet->row($c,$detalle);
                    $c=$c+1;

                    if($farmacia>0){
                        $detalle = array();
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        $detalle[] = $value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                        $detalle[] = 'FT';
                        $detalle[] = 'F'.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.$value->numero;
                        $detalle[] = $value->empresa2;
                        $detalle[] = $value->ruc2;
                        $detalle[] = "CREDITO";
                        $detalle[] = '70121';
                        $detalle[] = '';
                        $detalle[] = '';
                        if($value->situacionsunat!='E' && $value->situacion!="U"){
                            $detalle[] = number_format($farmacia,2,'.','');
                            $subtotal=$subtotal+number_format($farmacia,2,'.','');
                            $detalle[] = '';
                            $detalle[] = '';
                        }else{
                            $detalle[] = 0;
                            $detalle[] = '';
                            $detalle[] = '';
                        }
                        $detalle[] = "VENTAS CONVENIOS ".strtoupper(strftime("%B",strtotime($value->fecha)))." DEL ".date("Y",strtotime($value->fecha));
                        $detalle[] = 'FARMACIA';
                        $sheet->row($c,$detalle);
                        $c=$c+1;
                    }
                }
                $cabecera = array();
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = number_format($subtotal,2,'.','');
                $cabecera[] = number_format($igv,2,'.','');
                $cabecera[] = number_format($total,2,'.','');
                $sheet->row($c,$cabecera);
            });

            $excel->sheet('Cobranza', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Tipo";
                $cabecera[] = "Numero";
                $cabecera[] = "Paciente";
                $cabecera[] = "Cliente";
                $cabecera[] = "RUC";
                $cabecera[] = "Subtotal";
                $cabecera[] = "IGV";
                $cabecera[] = "Total";
                $cabecera[] = "Situacion";
                $cabecera[] = "Retencion";
                $cabecera[] = "Detraccion";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Mov.";
                $cabecera[] = "USUARIO";
                $cabecera[] = "ESTADO";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;
                $subtotal=0;$retencion=0;
                $igv=0;$detraccion=0;
                $total=0;

                foreach ($resultado as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = 'FT';
                    $detalle[] = 'F'.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.$value->numero;
                    $detalle[] = $value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                    $detalle[] = $value->ruc2;
                    $detalle[] = $value->empresa2;
                    if($value->situacionsunat!='E'){
                        $detalle[] = number_format($value->subtotal,2,'.','');
                        $detalle[] = number_format($value->igv,2,'.','');
                        $detalle[] = number_format($value->total,2,'.','');
                        $subtotal=$subtotal+number_format($value->subtotal,2,'.','');
                        $igv=$igv+number_format($value->igv,2,'.','');
                        $total=$total+number_format($value->total,2,'.','');
                        if($value->situacion=='C'){
                            $detraccion=$detraccion+number_format($value->detraccion,2,'.','');
                            $retencion=$retencion+number_format($value->retencion,2,'.','');
                        }
                    }else{
                        $detalle[] = 0;
                        $detalle[] = 0;
                        $detalle[] = 0;
                    }
                    if($value->situacion=='C'){
                        $detalle[] = "CANCELADO";
                    }elseif($value->situacion=='A'){
                        $detalle[] = "NOTA CREDITO";
                    }else{
                        $detalle[] = "CREDITO";
                    }
                    if($value->situacion=='C'){
                        $detalle[] = number_format($value->retencion,2,'.','');
                        $detalle[] = number_format($value->detraccion,2,'.','');
                    }else{
                        $detalle[] = 0;
                        $detalle[] = 0;
                    }
                    $detalle[] = date("d/m/Y",strtotime($value->fechaentrega));
                    $detalle[] = $value->voucher;
                    $detalle[] = $value->responsable->nombres;
                    if($value->situacionsunat=='L')
                        $sunat = 'PENDIENTE RESPUESTA';
                    elseif($value->situacionsunat=='R')
                        $sunat = 'RECHAZADO';
                    elseif($value->situacionsunat=='E')
                        $sunat = 'ERROR';
                    elseif($value->situacionsunat=='P')
                        $sunat = 'ACEPTADO';
                    else
                        $sunat = 'PENDIENTE';
                    $detalle[] = $sunat;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
                $cabecera = array();
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = number_format($subtotal,2,'.','');
                $cabecera[] = number_format($igv,2,'.','');
                $cabecera[] = number_format($total,2,'.','');
                $cabecera[] = "";
                $cabecera[] = number_format($retencion,2,'.','');
                $cabecera[] = number_format($detraccion,2,'.','');
                $sheet->row($c,$cabecera);
            });
        })->export('xls');
    }

    public function excelSunat(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','N')
                            ->where('m2.tipomovimiento_id','=',1);
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
        }        

        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','m2.tarjeta as tarjeta2',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->get();

        Excel::create('ExcelVentaSunat', function($excel) use($resultado,$request) {
 
            $excel->sheet('Ventas', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "";
                $cabecera[] = "Codigo";
                $cabecera[] = "Fecha Emision";
                $cabecera[] = "Tipo de Comprob";
                $cabecera[] = "Serie";
                $cabecera[] = "Numero";
                $cabecera[] = "Ticket Sin IGv";
                $cabecera[] = "Tipo de Documento";
                $cabecera[] = "Numero de Dcto Ident";
                $cabecera[] = "Apellidos,Nombres,RAzon Social";
                $cabecera[] = "Total valor venta operac gravadas";
                $cabecera[] = "Total valor venta operac exoneradas";
                $cabecera[] = "Total valor venta operac. inafectas";
                $cabecera[] = "ISC";
                $cabecera[] = "IGV";
                $cabecera[] = "OTROS TRIBUTOS";
                $cabecera[] = "IMPORTE TOTAL";
                $cabecera[] = "TIPO DCTO RELACIONADO";
                $cabecera[] = "SERIE DCTO RELACIONADO";
                $cabecera[] = "N° DCTO RELACIONADO ";
                $cabecera[] = "FORMULA";
                $array[] = $cabecera;
                $c=3;$d=3;$band=true;

                //NOTA DE CREDITO
                $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',6);
                            //->where('movimiento.situacion','<>','U')
                            //->where('movimiento.situacion','<>','A');
                if($request->input('fechainicial')!=""){
                    $resultado2 = $resultado2->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
                }
                if($request->input('fechafinal')!=""){
                    $resultado2 = $resultado2->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
                }        

                $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2','m2.tarjeta as tarjeta2','m2.tipodocumento_id as tipodocumento_id2','m2.serie as serie2','m2.numero as numero2')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->get();


                foreach ($resultado2 as $key1 => $value1){
                    //if($value1->situacion!="U"){//NO ANULADAS
                        $detalle = array();
                        $detalle[] = "";
                        $detalle[] = "7";
                        $detalle[] = date('d/m/Y',strtotime($value1->fecha));
                        $detalle[] = '07';
                        $detalle[] = str_pad($value1->serie,4,'0',STR_PAD_LEFT);
                        $detalle[] = str_pad($value1->numero,4,'0',STR_PAD_LEFT);
                        $detalle[] = "";
                        $person = Person::find($value1->persona_id);
                        if($value1->tipodocumento_id2==5){
                            if ($person != null) {
                                if(strlen($person->dni)<>8 || $value1->total<700){
                                    $detalle[] = "0";
                                    $detalle[] = "0";
                                    $detalle[] = "CLIENTES VARIOS";
                                }else{
                                    $detalle[] = "1";
                                    $detalle[] = $person->dni;
                                    $detalle[] = $person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres;
                                }
                            }else{
                                $detalle[] = "0";
                                $detalle[] = "0";
                                $detalle[] = "CLIENTES VARIOS";
                            }
                        }else{
                            $detalle[] = "6";
                            $detalle[] = $person->ruc;
                            $detalle[] = $person->bussinesname;
                            
                        }
                        $detalle[] = number_format($value1->subtotal,2,'.','');
                        $detalle[] = "0.00";
                        $detalle[] = "0.00";
                        $detalle[] = "0.00";
                        $detalle[] = number_format($value1->igv,2,'.','');
                        $detalle[] = "0.00";
                        $detalle[] = number_format($value1->total,2,'.','');
                        if($value1->tipodocumento_id2==5){
                            $detalle[] = "01";
                        }else{
                            $detalle[] = "03";
                        }
                        $detalle[] = str_pad($value1->serie2,4,'0',STR_PAD_LEFT);
                        $detalle[] = str_pad($value1->numero2,4,'0',STR_PAD_LEFT);
                        $detalle[] = '=CONCATENATE(B'.$c.',"|",C'.$c.',"|",D'.$c.',"|",E'.$c.',"|",F'.$c.',"|",G'.$c.',"|",H'.$c.',"|",I'.$c.',"|",J'.$c.',"|",ROUND(K'.$c.',2),"|",ROUND(L'.$c.',2),"|",ROUND(M'.$c.',2),"|",ROUND(N'.$c.',2),"|",ROUND(O'.$c.',2),"|",ROUND(P'.$c.',2),"|",ROUND(Q'.$c.',2),"|",R'.$c.',"|",S'.$c.',"|",T'.$c.',"|")';
                        $c=$c+1;
                        $array[] = $detalle;
                    //}
                }

                foreach ($resultado as $key => $value){
                    if (($value->serie==7 || $value->serie==9) && $band) {
                        $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.tipodocumento_id','<>',15)
                            ->where('movimiento.ventafarmacia','=','S');
                            //->where('m2.tipomovimiento_id','=',2);
                        if($request->input('fechainicial')!=""){
                            $resultado2 = $resultado2->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
                        }
                        if($request->input('fechafinal')!=""){
                            $resultado2 = $resultado2->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
                        }        

                        $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2','m2.tarjeta as tarjeta2',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->get();


                        foreach ($resultado2 as $key1 => $value1){
                            //if($value1->situacion!="U"){//NO ANULADAS
                                $detalle = array();
                                $detalle[] = "";
                                $detalle[] = "7";
                                $detalle[] = date('d/m/Y',strtotime($value1->fecha));
                                $detalle[] = $value1->tipodocumento_id==5?'03':'01';
                                $detalle[] = str_pad($value1->serie,4,'0',STR_PAD_LEFT);
                                $detalle[] = str_pad($value1->numero,4,'0',STR_PAD_LEFT);
                                $detalle[] = "";
                                $person = Person::find($value1->persona_id);
                                if($value1->tipodocumento_id==5){//boleta
                                    if ($person != null) {
                                        if(strlen($person->dni)<>8 || $value1->total<700){
                                            $detalle[] = "0";
                                            $detalle[] = "0";
                                            $detalle[] = "CLIENTES VARIOS";
                                        }else{
                                            $detalle[] = "1";
                                            $detalle[] = $person->dni;
                                            $detalle[] = $person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres;
                                        }
                                    }else{
                                        $detalle[] = "0";
                                        $detalle[] = "0";
                                        $detalle[] = "CLIENTES VARIOS";
                                    }
                                    
                                }else{
                                    $detalle[] = "6";
                                    $detalle[] = $value1->empresa->ruc;
                                    $detalle[] = $value1->empresa->bussinesname;
                                    
                                }
                                $detalle[] = number_format($value1->subtotal,2,'.','');
                                $detalle[] = "0.00";
                                $detalle[] = "0.00";
                                $detalle[] = "0.00";
                                $detalle[] = number_format($value1->igv,2,'.','');
                                $detalle[] = "0.00";
                                $detalle[] = number_format($value1->total,2,'.','');
                                $detalle[] = "";
                                $detalle[] = '';
                                $detalle[] = "";
                                $detalle[] = '=CONCATENATE(B'.$c.',"|",C'.$c.',"|",D'.$c.',"|",E'.$c.',"|",F'.$c.',"|",G'.$c.',"|",H'.$c.',"|",I'.$c.',"|",J'.$c.',"|",ROUND(K'.$c.',2),"|",ROUND(L'.$c.',2),"|",ROUND(M'.$c.',2),"|",ROUND(N'.$c.',2),"|",ROUND(O'.$c.',2),"|",ROUND(P'.$c.',2),"|",ROUND(Q'.$c.',2),"|",R'.$c.',"|",S'.$c.',"|",T'.$c.',"|")';
                                $c=$c+1;
                                $array[] = $detalle;
                            //}
                        }
                        $band=false;
                    }
                    //if($value->situacion!="U"){//NO ANULADAS
                        $detalle = array();
                        $detalle[] = "";
                        $detalle[] = "7";
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        $detalle[] = $value->tipodocumento_id==5?'03':'01';
                        $detalle[] = str_pad($value->serie,4,'0',STR_PAD_LEFT);
                        $detalle[] = str_pad($value->numero,4,'0',STR_PAD_LEFT);
                        $detalle[] = "";
                        $person = Person::find($value->persona_id);
                        if($value->tipodocumento_id==5){//boleta
                            if(strlen($person->dni)<>8 || $value->total<700){
                                $detalle[] = "0";
                                $detalle[] = "0";
                                $detalle[] = "CLIENTES VARIOS";
                            }else{
                                $detalle[] = "1";
                                $detalle[] = $person->dni;
                                $detalle[] = $person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres;
                            }
                        }else{
                            $detalle[] = "6";
                            $detalle[] = $person->ruc;
                            $detalle[] = $person->bussinesname;
                            
                        }
                        $detalle[] = number_format($value->subtotal,2,'.','');
                        $detalle[] = "0.00";
                        $detalle[] = "0.00";
                        $detalle[] = "0.00";
                        $detalle[] = number_format($value->igv,2,'.','');
                        $detalle[] = "0.00";
                        $detalle[] = number_format($value->total,2,'.','');
                        $detalle[] = "";
                        $detalle[] = '';
                        $detalle[] = "";
                        $detalle[] = '=CONCATENATE(B'.$c.',"|",C'.$c.',"|",D'.$c.',"|",E'.$c.',"|",F'.$c.',"|",G'.$c.',"|",H'.$c.',"|",I'.$c.',"|",J'.$c.',"|",ROUND(K'.$c.',2),"|",ROUND(L'.$c.',2),"|",ROUND(M'.$c.',2),"|",ROUND(N'.$c.',2),"|",ROUND(O'.$c.',2),"|",ROUND(P'.$c.',2),"|",ROUND(Q'.$c.',2),"|",R'.$c.',"|",S'.$c.',"|",T'.$c.',"|")';
                        $c=$c+1;
                        $array[] = $detalle;
                    //}
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function excelVenta(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','N')
                            ->where('m2.tipomovimiento_id','=',1)
                            ->whereNull('m2.deleted_at');
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
        }        

        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','m2.tarjeta as tarjeta2',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'), 'responsable.nombres as responsable2_nombres' ,'responsable.apellidopaterno as responsable2_apPaterno','responsable.apellidomaterno as responsable2_apMaterno')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->orderBy('movimiento.fecha', 'ASC')->get();

        Excel::create('ExcelVentaFarmacia', function($excel) use($resultado,$request) {
            
            $excel->sheet('VentasFarmacia', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
              //  $cabecera[] = "Responsable";
                $cabecera[] = "Doc.";
                $cabecera[] = "Nro.";
                $cabecera[] = "Cliente";
                $cabecera[] = "RUC";
                $cabecera[] = "Condicion";
                $cabecera[] = "CTA";
                $cabecera[] = "CTA";
                $cabecera[] = "CTA";
                $cabecera[] = "Subtotal";
                $cabecera[] = "Igv";
                $cabecera[] = "Total";
                $cabecera[] = "Glosa 1";
                $cabecera[] = "Glosa 2";
                $cabecera[] = "Sunat";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$subtotal=0;$igv=0;$total=0;$band=true;
                foreach ($resultado as $key => $value){
                    //FARMACIA
                    if (($value->serie==7 || $value->serie==9) && $band) {
                        $band=false;
                        $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','S')
                            ->where('movimiento.tipodocumento_id','<>',15);
                        if($request->input('fechainicial')!=""){
                            $resultado2 = $resultado2->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
                        }
                        if($request->input('fechafinal')!=""){
                            $resultado2 = $resultado2->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
                        }        

                        $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2','m2.tarjeta as tarjeta2',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'),DB::raw('CONCAT(responsable.apellidopaterno," ",responsable.apellidomaterno, " ", responsable.nombres) as responsable2'))->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->get();


                        foreach ($resultado2 as $key1 => $value1){
                            $glosa='VARIOS';

                            $detalle = array();
                            $detalle[] = date('d/m/Y',strtotime($value1->fecha));
                            $person = Person::find($value1->persona_id);
                            if ($person !== null) {
                                $detalle[] = $person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres;
                            }else{
                                $detalle[] = $value1->nombrepaciente;
                            }
                            
                            //$resp = Person::where('id','=',$value1->responsable_id)->first();
                            //echo json_encode($resp);
                            //if($resp !== null){
                              //$detalle[] =  $resp->apellidopaterno." ".$resp->apellidomaterno." ".$resp->nombres;
                           
                            //}else{
                            //$detalle[] = $value1->responsable2 == ""? "": $value1->responsable2;
                           // }
                         
                            $detalle[] = $value1->tipodocumento_id==5?'BV':'FT';
                            if($value1->manual=='S')
                                $detalle[] = $value1->serie.'-'.$value1->numero;
                            else
                                $detalle[] = ($value1->tipodocumento_id==5?'B':'F').str_pad($value1->serie,3,'0',STR_PAD_LEFT).'-'.$value1->numero;
                            if($value1->tipodocumento_id==4){//Factura
                                if(!is_null($value1->empresa_id) && $value1->empresa_id>0){
                                    $detalle[] = $value1->empresa->bussinesname;
                                    $detalle[] = $value1->empresa->ruc;
                                }else{
                                    $detalle[] = "";
                                    $detalle[] = "";
                                }
                            }else{
                                $detalle[] = "";
                                $detalle[] = "0000";
                            }
                            if($value1->tarjeta2!="" && $value1->estadopago!="PP"){
                                $detalle[] = "TARJETA";
                                $detalle[] = "121205";
                                $detalle[] = "70121";
                                $detalle[] = "401111";
                            }elseif($value1->estadopago=="PP"){//PENDIENTE
                                $detalle[] = "CREDITO";
                                $detalle[] = "121204";
                                $detalle[] = "70121";
                                $detalle[] = "401111";
                            }else{
                                $detalle[] = "CONTADO";
                                $detalle[] = "121203";
                                $detalle[] = "70121";
                                $detalle[] = "401111";
                            }
                            if($value1->situacion=="U"){
                                $detalle[] = number_format(0,2,'.','');
                                $detalle[] = number_format(0,2,'.','');
                                $detalle[] = number_format(0,2,'.','');
                            }else{
                                $detalle[] = number_format($value1->subtotal,2,'.','');
                                $detalle[] = number_format($value1->igv,2,'.','');
                                $detalle[] = number_format($value1->total,2,'.','');
                                $subtotal = $subtotal + number_format($value1->subtotal,2,'.','');
                                $igv = $igv + number_format($value1->igv,2,'.','');
                                $total = $total + number_format($value1->total,2,'.','');
                            }
                            $detalle[] = "VENTAS ".strtoupper(strftime("%B",strtotime($value1->fecha)))." DEL ".date("Y",strtotime($value1->fecha));
                            $detalle[] = $glosa;
                            if($value1->situacionsunat=='L')
                                // if(substr($value1->numero2, 0,1) == 'B'){
                                    $sunat = 'ACEPTADO';
                                // }else{
                                //     $sunat = 'PENDIENTE RESPUESTA';
                                // }
                            elseif($value1->situacionsunat=='R')
                                $sunat = 'RECHAZADO';
                            elseif($value1->situacionsunat=='E')
                                $sunat = 'ERROR';
                            elseif($value1->situacionsunat=='P')
                                $sunat = 'ACEPTADO';
                            else
                                $sunat = 'PENDIENTE';
                            $detalle[] = $sunat;
                            $sheet->row($c,$detalle);
                            $c=$c+1;
                        }
                    }
                    //exit();
                    $detalle = Detallemovcaja::where('movimiento_id','=',$value->movimiento_id)->first();
                    if(!empty($detalle)){
                    // dd($value->movimiento_id);
                        if(!is_null($detalle->servicio) && $detalle->servicio_id>0){
                            $glosa=$detalle->servicio->tiposervicio->nombre;
                        }else{
                            $glosa='VARIOS';
                        }
                    }else{
                        
                    }
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $ticket = Movimiento::find($value->movimiento_id);

                    $person = Person::find($ticket->persona_id);
                    $detalle[] = $person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres;
                    $detalle[] = $value->tipodocumento_id==5?'BV':'FT';
                    if($value->manual=='S')
                        $detalle[] = $value->serie.'-'.$value->numero;
                    else
                        $detalle[] = ($value->tipodocumento_id==5?'B':'F').str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.$value->numero;
                    // if ($value->serie == '4' && $value->numero =='251861') {
                    //     dd($value);
                    // }
                    if($value->tipodocumento_id==4){//Factura
                        $detalle[] = $value->persona->bussinesname;
                        $detalle[] = $value->persona->ruc;
                    }else{
                        $detalle[] = "";
                        $detalle[] = "0000";
                    }
                    if($ticket->tarjeta!=""){
                        $detalle[] = "TARJETA";
                        $detalle[] = "121205";
                        $detalle[] = "70321";
                        $detalle[] = "401111";
                    }elseif($ticket->situacion=="B"){//PENDIENTE
                        $detalle[] = "CREDITO";
                        $detalle[] = "121204";
                        $detalle[] = "70321";
                        $detalle[] = "401111";
                    }else{
                        $detalle[] = "CONTADO";
                        $detalle[] = "121203";
                        $detalle[] = "70321";
                        $detalle[] = "401111";
                    }
                    if($value->situacion=="U"){
                        $detalle[] = number_format(0,2,'.','');
                        $detalle[] = number_format(0,2,'.','');
                        $detalle[] = number_format(0,2,'.','');
                    }else{
                        $detalle[] = number_format($value->subtotal,2,'.','');
                        $detalle[] = number_format($value->igv,2,'.','');
                        $detalle[] = number_format($value->total,2,'.','');
                        $subtotal = $subtotal + number_format($value->subtotal,2,'.','');
                        $igv = $igv + number_format($value->igv,2,'.','');
                        $total = $total + number_format($value->total,2,'.','');
                    }
                    $detalle[] = "VENTAS ".strtoupper(strftime("%B",strtotime($value->fecha)))." DEL ".date("Y",strtotime($value->fecha));
                    $detalle[] = $glosa;
                    if($value->situacionsunat=='L')
                        // if(substr($value->numero2, 0,1) == 'B'){
                            $sunat = 'ACEPTADO';
                        // }else{
                        //     $sunat = 'PENDIENTE RESPUESTA';
                        // }
                    elseif($value->situacionsunat=='E')
                        $sunat = 'ERROR';
                    elseif($value->situacionsunat=='R')
                        $sunat = 'RECHAZADO';
                    elseif($value->situacionsunat=='P')
                        $sunat = 'ACEPTADO';
                    else
                        $sunat = 'PENDIENTE';
                    $detalle[] = $sunat;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
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
                $detalle[] = number_format($subtotal,2,'.','');
                $detalle[] = number_format($igv,2,'.','');
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
 
            });

            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->join('movimiento as m3','m3.movimiento_id','=','movimiento.id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            // ->where('movimiento.serie','=','4')
                            ->where('movimiento.ventafarmacia','=','N')
                            ->where('m2.tipomovimiento_id','=',1)
                            ->where('m3.tipomovimiento_id','=',2)
                            ->whereNull('m2.deleted_at')
                            ->whereNull('m3.deleted_at');
                            // ->whereIn('movimiento.id',['668978']);
            if($request->input('fechainicial')!=""){
                $resultado = $resultado->where('m3.fecha','>=',$request->input('fechainicial').' 00:00:00');
            }
            if($request->input('fechafinal')!=""){
                $resultado = $resultado->where('m3.fecha','<=',$request->input('fechafinal').' 23:59:59');
            }        

            $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','m2.tarjeta as tarjeta2',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'),'m3.fecha as fechacaja','m3.tipotarjeta as tipotarjeta3','responsable.nombres as responsable2')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->orderBy('movimiento.fecha', 'ASC')->get();

            // echo json_encode($resultado);
            // exit();
            // dd($resultado);
            $excel->sheet('Cobranza', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Doc.";
                $cabecera[] = "Nro.";
                $cabecera[] = "Condicion";
                $cabecera[] = "CTA";
                $cabecera[] = "CTA";
                $cabecera[] = "Cliente";
                $cabecera[] = "RUC";
                $cabecera[] = "Subtotal";
                $cabecera[] = "Igv";
                $cabecera[] = "Total";
                $cabecera[] = "Glosa 2";
                $cabecera[] = "Sunat";
                $sheet->row(1,$cabecera);

                $c=2;$d=3;$subtotal=0;$igv=0;$total=0;$band=true;
                
                // dd($resultado);
                
                foreach ($resultado as $key => $value){
                    //FARMACIA

                    if (($value->serie==7 || $value->serie==9) && $band) {
                        $band=false;
                        $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->leftjoin('movimiento as m3', function($join)
                            {
                                $join->on('m3.movimiento_id','=','movimiento.id')
                                     ->where('m3.tipomovimiento_id', '=', 2)
                                     ->whereNull('m3.deleted_at');
                            })
                            ->leftjoin('movimiento as m4', function($join)
                            {
                                $join->on('m4.id','=','movimiento.movimientodescarga_id')
                                     ->where('m4.tipomovimiento_id', '=', 2)
                                     ->whereNull('m4.deleted_at');
                            })
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','S')
                            // ->whereNotIn('m2.situacion',['A'])
                            // ->whereNull('m2.deleted_at')
                            // ->whereNotIn('m3.situacion',['A'])
                            // ->whereNull('m3.deleted_at')
                            // ->where('movimiento.serie','=','4')
                            // ->where('movimiento.numero','=','250096')
                            ->whereIn('movimiento.formapago',['T','C'])
                            ->where('movimiento.tipodocumento_id','<>',15);
       
                        if($request->input('fechainicial')!=""){
                            $resultado2 = $resultado2->where(function($query) use ($request){
                                        $query->where('m3.fecha','>=',$request->input('fechainicial'))
                                              ->orWhere('m4.fecha','>=',$request->input('fechainicial'));
                                        });
                        }
                        if($request->input('fechafinal')!=""){
                            $resultado2 = $resultado2->where(function($query) use ($request){
                                        $query->where('m3.fecha','<=',$request->input('fechafinal'))
                                              ->orWhere('m4.fecha','<=',$request->input('fechafinal'));
                                        });
                        }        

                        $resultado2        = $resultado2->select('movimiento.*','m3.situacion as situacion2','m2.tarjeta as tarjeta2','m3.fecha as fechacaja3','m4.fecha as fechacaja4',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'),'m3.tarjeta as tarjeta3')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->get();

                        // dd($resultado2);

                        foreach ($resultado2 as $key1 => $value1){
                            // if($value1->serie == 9 && $value->numero == '23411'){
                            //     dd($value1);
                            // }

                            if($value1->situacion<>'U'){
                                $glosa='VARIOS';
                                //die();
                                $detalle = array();
                                if($value1->estadopago=='PP'){
                                    //print_r($value1->movimientodescarga_id."-");
                                    //$caja = Movimiento::where('id','=',$value1->movimientodescarga_id)->where('tipomovimiento_id','=','2')->first();
                                    
                                    if (!isset($value1->fechacaja4)) {
                                        $detalle[] = date('d/m/Y',strtotime($value1->fechacaja3));
                                    } else {
                                        $detalle[] = date('d/m/Y',strtotime($value1->fechacaja4));
                                    }
                                }else{
                                    //$caja = Movimiento::where('movimiento_id','=',$value1->id)->where('tipomovimiento_id','=','2')->first();
                                    $detalle[] = date('d/m/Y',strtotime($value1->fechacaja3));
                                }
                                $person = Person::find($value1->persona_id);
                                if ($person !== null) {
                                    $detalle[] = $person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres;
                                }else{
                                    $detalle[] = $value1->nombrepaciente;
                                }
                                $detalle[] = $value1->tipodocumento_id==5?'BV':'FT';
                                if($value1->manual=='S')
                                    $detalle[] = $value1->serie.'-'.$value1->numero;
                                else
                                    $detalle[] = ($value1->tipodocumento_id==5?'B':'F').str_pad($value1->serie,3,'0',STR_PAD_LEFT).'-'.$value1->numero;
                                if($value1->tarjeta2!="" && $value1->formapago=='T'){
                                    $detalle[] = "TARJETA";
                                    $detalle[] = "121205";
                                    $detalle[] = "103112";
                                }elseif($value1->estadopago=="PP" && $value1->formapago=='C'){//PENDIENTE
                                    if($value1->tarjeta3!=""){
                                        $detalle[] = "COBRANZA";
                                        $detalle[] = "121204";
                                        $detalle[] = "103112";
                                    }else{
                                        $detalle[] = "COBRANZA";
                                        $detalle[] = "121204";
                                        $detalle[] = "101101";
                                    }
                                }else{
                                    $detalle[] = "CONTADO";
                                    $detalle[] = "121203";
                                    $detalle[] = "101101";
                                }
                                if($value1->tipodocumento_id==4){//Factura
                                    if($value1->empresa_id>0){
                                        $detalle[] = $value1->empresa->bussinesname;
                                        $detalle[] = $value1->empresa->ruc;
                                    }else{
                                        $detalle[] = "";
                                        $detalle[] = "";
                                    }
                                }else{
                                    $detalle[] = "";
                                    $detalle[] = "0000";
                                }
                                $detalle[] = number_format($value1->subtotal,2,'.','');
                                $detalle[] = number_format($value1->igv,2,'.','');
                                $detalle[] = number_format($value1->total,2,'.','');
                                $subtotal = $subtotal + number_format($value1->subtotal,2,'.','');
                                $igv = $igv + number_format($value1->igv,2,'.','');
                                $total = $total + number_format($value1->total,2,'.','');
                                $detalle[] = $glosa;
                                if($value1->situacionsunat=='L')
                                    // if(substr($value1->numero2, 0,1) == 'B'){
                                        $sunat = 'ACEPTADO';
                                    // }else{
                                    //     $sunat = 'PENDIENTE RESPUESTA';
                                    // }
                                elseif($value1->situacionsunat=='E')
                                    $sunat = 'ERROR';
                                elseif($value1->situacionsunat=='R')
                                    $sunat = 'RECHAZADO';
                                elseif($value1->situacionsunat=='P')
                                    $sunat = 'ACEPTADO';
                                else
                                    $sunat = 'PENDIENTE';
                                $detalle[] = $sunat;
                                $sheet->row($c,$detalle);
                                $c=$c+1;
                            }
                        }
                    }

                    // if($value->serie == 4)
                    //     dd($value);

                    if($value->situacion=='N' || $value->situacion=='A'){//SOLO PAGADOS
                        // dd($value);
                        $detalle = Detallemovcaja::where('movimiento_id','=',$value->movimiento_id)->first();
                        
                        if(!is_null($detalle)){
                            if(!is_null($detalle->servicio) && $detalle->servicio_id>0){
                                $glosa=$detalle->servicio->tiposervicio->nombre;
                            }else{
                                // dd($detalle);
                                $glosa='VARIOS';
                            }                            
                        }else{
                            $glosa='VARIOS';       
                        }
                        $detalle = array();
                        $detalle[] = date('d/m/Y',strtotime($value->fechacaja));
                        
                        // if($value->id == '616280'){
                        //     dd($value);
                        // }

                        $ticket = Movimiento::find($value->movimiento_id);

                        $person = Person::find($ticket->persona_id);
                        //$caja = Movimiento::where('movimiento_id','=',$value->id)->first();
                        $detalle[] = $person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres;
                        $detalle[] = $value->tipodocumento_id==5?'BV':'FT';
                        if($value->manual=='S'){
                            $detalle[] = $value->serie.'-'.$value->numero;
                        }else{
                            $detalle[] = ($value->tipodocumento_id==5?'B':'F').str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.$value->numero;
                        }
                        if($ticket->tarjeta!=""){
                            $detalle[] = "TARJETA";
                            $detalle[] = "121205";
                            $detalle[] = "103112";
                        }elseif($ticket->situacion=="B" && $value->tipotarjeta3==""){//PENDIENTE Y CONTADO
                            $detalle[] = "COBRANZA";
                            $detalle[] = "121204";
                            if($value->serie==8){
                                $detalle[] = "101101";    
                            }else{
                                $detalle[] = "101101";
                            }
                        }elseif($ticket->situacion=="B" && $value->tipotarjeta3!=""){//PENDIENTE Y TARJETA
                            $detalle[] = "COBRANZA";
                            $detalle[] = "121204";
                            $detalle[] = "103112";
                        }else{
                            $detalle[] = "CONTADO";
                            $detalle[] = "121203";
                            if($value->serie==8){
                                $detalle[] = "101101";    
                            }else{
                                $detalle[] = "101101";
                            }
                        }
                        if($value->tipodocumento_id==4){//Factura
                            $detalle[] = $value->persona->bussinesname;
                            $detalle[] = $value->persona->ruc;
                        }else{
                            $detalle[] = "";
                            $detalle[] = "0000";
                        }
                        if($value->situacion=="U"){
                            $detalle[] = number_format(0,2,'.','');
                            $detalle[] = number_format(0,2,'.','');
                            $detalle[] = number_format(0,2,'.','');
                        }else{
                            $detalle[] = number_format($value->subtotal,2,'.','');
                            $detalle[] = number_format($value->igv,2,'.','');
                            $detalle[] = number_format($value->total,2,'.','');
                            $subtotal = $subtotal + number_format($value->subtotal,2,'.','');
                            $igv = $igv + number_format($value->igv,2,'.','');
                            $total = $total + number_format($value->total,2,'.','');
                        }
                        // if($value->total == '31.4'){
                        //     dd($value);
                        // }
                        $detalle[] = $glosa;
                        if($value->situacionsunat=='L')
                            // if(substr($value->numero2, 0,1) == 'B'){
                                $sunat = 'ACEPTADO';
                            // }else{
                            //     $sunat = 'PENDIENTE RESPUESTA';
                            // }
                        elseif($value->situacionsunat=='E')
                            $sunat = 'ERROR';
                        elseif($value->situacionsunat=='R')
                            $sunat = 'RECHAZADO';
                        elseif($value->situacionsunat=='P')
                            $sunat = 'ACEPTADO';
                        else
                            $sunat = 'PENDIENTE';
                        $detalle[] = $sunat;
                        $sheet->row($c,$detalle);
                        $c=$c+1;
                    }
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
                $detalle[] = number_format($subtotal,2,'.','');
                $detalle[] = number_format($igv,2,'.','');
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);

            });

            $excel->sheet('Tarjeta', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Doc.";
                $cabecera[] = "Nro.";
                $cabecera[] = "Importe";
                $cabecera[] = "Operacion";
                $cabecera[] = "Nro. Operacion";
                $cabecera[] = "Tipo Tarjeta";
                $cabecera[] = "Usuario";
                $cabecera[] = "Sunat";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$total=0;$band=true;
                foreach ($resultado as $key => $value){
                    //FARMACIA
                    if (($value->serie==7 || $value->serie==9) && $band) {
                        $band=false;
                        $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','S')
                            ->where('movimiento.estadopago','<>','PP')
                            ->where('movimiento.tipodocumento_id','<>',15)
                            ->where('m2.tipomovimiento_id','=',2);
                        if($request->input('fechainicial')!=""){
                            $resultado2 = $resultado2->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
                        }
                        if($request->input('fechafinal')!=""){
                            $resultado2 = $resultado2->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
                        }        

                        $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2','m2.tipotarjeta as tarjeta2','m2.voucher as voucher2',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'),'responsable.nombres as responsable2')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->get();

                        foreach ($resultado2 as $key1 => $value1){
                            if($value1->situacion<>'U' && $value1->tarjeta2!=""){
                                $detalle = Detallemovcaja::where('movimiento_id','=',$value1->movimiento_id)->first();
                                if(!is_null($detalle) &&  !is_null($detalle->servicio_id) && $detalle->servicio_id>0){
                                    $glosa=$detalle->servicio->tiposervicio->nombre;
                                }else{
                                    $glosa='VARIOS';
                                }

                                $detalle = array();
                                $detalle[] = date('d/m/Y',strtotime($value1->fecha));
                                $person = Person::find($value1->persona_id);
                                if ($person !== null) {
                                    $detalle[] = $person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres;
                                }else{
                                    $detalle[] = $value1->nombrepaciente;
                                }
                                $detalle[] = $value1->tipodocumento_id==5?'BV':'FT';
                                if($value1->manual=='S')
                                    $detalle[] = $value1->serie.'-'.$value1->numero;
                                else
                                    $detalle[] = ($value1->tipodocumento_id==5?'B':'F').str_pad($value1->serie,3,'0',STR_PAD_LEFT).'-'.$value1->numero;
                                
                                if($value1->situacion=="U"){
                                    $detalle[] = number_format(0,2,'.','');
                                    $detalle[] = "ANULADA";
                                }else{
                                    $detalle[] = number_format($value1->total,2,'.','');
                                    $total = $total + number_format($value1->total,2,'.','');
                                    $detalle[] = "MEDICAMENTO";
                                }
                                $detalle[] = $value1->voucher2;
                                $detalle[] = $value1->tarjeta2;
                                $detalle[] = $value1->responsable2;
                                if($value1->situacionsunat=='L')
                                    // if(substr($value1->numero2, 0,1) == 'B'){
                                        $sunat = 'ACEPTADO';
                                    // }else{
                                    //     $sunat = 'PENDIENTE RESPUESTA';
                                    // }
                                elseif($value1->situacionsunat=='R')
                                    $sunat = 'RECHAZADO';
                                elseif($value1->situacionsunat=='E')
                                    $sunat = 'ERROR';
                                elseif($value1->situacionsunat=='P')
                                    $sunat = 'ACEPTADO';
                                else
                                    $sunat = 'PENDIENTE';
                                $detalle[] = $sunat;
                                $sheet->row($c,$detalle);
                                $c=$c+1;
                            }
                        }
                    }
                    if($value->situacion=='N' || $value->situacion=='A'){//SOLO PAGADOS
                        $res = Detallemovcaja::where('movimiento_id','=',$value->movimiento_id)->get();
                        $caja = Movimiento::where('movimiento_id','=',$value->id)->first();
                        if(!is_null($caja) && $caja->tipotarjeta!=""){
                            foreach($res as $k1 => $det){
                                $detalle = array();
                                $detalle[] = date('d/m/Y',strtotime($value->fecha));
                                $ticket = Movimiento::find($value->movimiento_id);
                                $person = Person::find($ticket->persona_id);
                                $detalle[] = $person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres;
                                $detalle[] = $value->tipodocumento_id==5?'BV':'FT';
                                if($value->manual=='S')
                                    $detalle[] = $value->serie.'-'.$value->numero;
                                else
                                    $detalle[] = ($value->tipodocumento_id==5?'B':'F').str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.$value->numero;
                                if($value->situacion=="U"){
                                    $detalle[] = number_format(0,2,'.','');
                                    $detalle[] = "ANULADA";
                                }else{
                                    $detalle[] = number_format($det->pagohospital*$det->cantidad,2,'.','');
                                    $total = $total + number_format($det->pagohospital*$det->cantidad,2,'.','');
                                    if(!is_null($det->servicio) && $det->servicio_id>0){
                                        $detalle[] = $det->servicio->nombre;
                                    }else{
                                        $detalle[] = $det->descripcion;
                                    }
                                }
                                $detalle[] = $caja->voucher;
                                $detalle[] = $caja->tipotarjeta;
                                $detalle[] = $value->responsable2;
                                if($value->situacionsunat=='L')
                                    // if(substr($value->numero2, 0,1) == 'B'){
                                        $sunat = 'ACEPTADO';
                                    // }else{
                                    //     $sunat = 'PENDIENTE RESPUESTA';
                                    // }
                                elseif($value->situacionsunat=='E')
                                    $sunat = 'ERROR';
                                elseif($value->situacionsunat=='R')
                                    $sunat = 'RECHAZADO';
                                elseif($value->situacionsunat=='P')
                                    $sunat = 'ACEPTADO';
                                else
                                    $sunat = 'PENDIENTE';
                                $detalle[] = $sunat;
                                $sheet->row($c,$detalle);
                                $c=$c+1;
                            }
                        }
                    }
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
 
            });
            
            $excel->sheet('Egresos', function($sheet) use($request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Tipo Doc.";
                $cabecera[] = "Serie";
                $cabecera[] = "Nro.";
                $cabecera[] = "Proveedor";
                $cabecera[] = "Ruc";
                $cabecera[] = "Rubro";
                $cabecera[] = "Importe";
                $cabecera[] = "Glosa";
                $cabecera[] = "Usuario";
                $cabecera[] = "CTA";
                $cabecera[] = "CTA";
                $sheet->row(1,$cabecera);

                $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('movimiento as mv2','movimiento.id',"=",'mv2.movimiento_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->where('movimiento.tipomovimiento_id','=',2)
                            ->where('movimiento.conceptopago_id','<>','2')
                            ->where('movimiento.conceptopago_id','<>','8')
                            ->where('movimiento.situacion','<>','R')
                            ->where('movimiento.situacion','<>','P')
                            ->where('movimiento.situacion','<>','A')
                            ->whereNotIn('movimiento.caja_id',[6,7])
                            ->whereNotNull('movimiento.caja_id')
                            ->where('movimiento.situacion','<>','U')
                            // ->whereIn('movimiento.id',[659702,659703,659698])
                            ->where(function($query){
                                $query
                                    ->whereNotIn('movimiento.conceptopago_id',[31])
                                    ->orWhere('mv2.situacion','<>','R');
                            })
                            ;
                if($request->input('fechainicial')!=""){
                    $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
                }
                if($request->input('fechafinal')!=""){
                    $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
                }        

                $resultado        = $resultado->select('movimiento.*','responsable.nombres as responsable2')->orderBy('movimiento.fecha', 'ASC')->get();

                // dd($resultado);

                $c=2;$d=3;$total=0;$band=true;$band2=true;
                $ultimo_id = '';
                foreach ($resultado as $key => $value){
                    if($value->conceptopago_id==20 || $value->conceptopago_id==16){
                        $mov = Movimiento::where('movimiento_id','=',$value->id)->first();
                        if(!is_null($mov)){
                            if($mov->situacion=='R'){
                                $band2=false;
                            }else{
                                $band2=true;
                            }
                        }else{
                            $band2=false;
                        }
                    }else{
                        $band2=true;
                    }

                    if($value->conceptopago->tipo=='E' && $band2 && $ultimo_id <> $value->id){
                        // dd($value);

                        $detalle = array();
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        //$ticket = Movimiento::find($value->movimiento_id);
                        if(($value->conceptopago_id==24 || $value->conceptopago_id==25) && $value->listapago){
                            if($value->formapago!=""){
                                $detalle[] = $value->formapago;
                                $num = explode('-',$value->voucher);
                                if(count($num)>1){
                                    $detalle[] = $num[0];
                                    $detalle[] = $num[1];
                                }else{
                                    $detalle[] = "";
                                    $detalle[] = $value->voucher;
                                }
                            }else{
                                $detalle[] = 'RH';
                                $list=explode(",",$value->listapago);
                                for($x=0;$x<count($list);$x++){
                                    $detalle1 = Detallemovcaja::find($list[0]);
                                    if($detalle1->recibo!=""){
                                        $num = explode('-',$detalle1->recibo);
                                    }
                                }
                                if(count($num)>1){
                                    $detalle[] = $num[0];
                                    $detalle[] = $num[1];
                                }else{
                                    $detalle[] = "";
                                    $detalle[] = $detalle1->recibo;
                                }
                            }
                        }else{
                            if($value->caja_id==4){
                                if($value->formapago!=""){
                                    $detalle[] = $value->formapago;    
                                }elseif($value->tipodocumento_id==7){
                                    $detalle[] = 'BV';    
                                }elseif($value->tipodocumento_id==6){
                                    $detalle[] = 'FT';    
                                }else{
                                    $detalle[] = '';    
                                }
                            }else{
                                $detalle[] = $value->formapago;
                            }
                            if($value->voucher!="")
                                $num = explode('-',$value->voucher);
                            else
                                $num = explode('-','1-'.$value->numero);
                            if(count($num)>1){
                                $detalle[] = $num[0];
                                $detalle[] = $num[1];
                            }else{
                                $detalle[] = "";
                                $detalle[] = $num[0];
                            }
                        }

                        if(!is_null($value->persona)){
                            if($value->persona->bussinesname!=null){
                                $detalle[] = $value->persona->bussinesname;
                            }else{
                                $detalle[] = $value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres;
                            }
                            $detalle[] = ($value->persona->ruc==""?"00000":$value->persona->ruc);
                        }else{
                            $detalle[] = "";
                            $detalle[] = "00000";
                        }
                        if(strtoupper($value->conceptopago->nombre)=="TRANSFERENCIA FARMACIA" && strtoupper($value->comentario)=="TRANSFERENCIA A FARMACIA JESSENIA" && floatval($value->total) == 16 && $value->id != 293223){
                            // dd($value);
                        }
                        $detalle[] = $value->conceptopago->nombre;
                        $detalle[] = number_format($value->total,2,'.','');
                        $detalle[] = $value->comentario;
                        $detalle[] = $value->responsable2;
                        $total = $total + number_format($value->total,2,'.','');
                        if($value->caja_id==3)
                            $detalle[] = "101101";
                        else
                            $detalle[] = "101101";
                        $detalle[] = $value->conceptopago->cuenta;
                        $ultimo_id=$value->id;
                        $sheet->row($c,$detalle);
                        $c=$c+1;
                    }
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = number_format($total,2,'.','');
                // exit();
                $sheet->row($c,$detalle);
            });
            
            $excel->sheet('EgresosTesoreria', function($sheet) use($request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Tipo Doc.";
                $cabecera[] = "Serie";
                $cabecera[] = "Nro.";
                $cabecera[] = "Proveedor";
                $cabecera[] = "Ruc";
                $cabecera[] = "Rubro";
                $cabecera[] = "Importe";
                $cabecera[] = "Glosa";
                $cabecera[] = "Usuario";
                $cabecera[] = "CTA";
                $cabecera[] = "CTA";
                $sheet->row(1,$cabecera);

                $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->where('movimiento.tipomovimiento_id','=',2)
                            ->where('movimiento.conceptopago_id','<>','2')
                            ->where('movimiento.conceptopago_id','<>','8')
                            ->where('movimiento.caja_id','=','6')
                            ->where('movimiento.situacion','<>','R')
                            ->where('movimiento.situacion','<>','P')
                            ->where('movimiento.situacion','<>','A')
                            ->whereNotNull('movimiento.caja_id');
                if($request->input('fechainicial')!=""){
                    $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
                }
                if($request->input('fechafinal')!=""){
                    $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
                }        

                $resultado        = $resultado->select('movimiento.*','responsable.nombres as responsable2')->orderBy('movimiento.fecha', 'ASC')->get();
                $c=2;$d=3;$total=0;$band=true;
                foreach ($resultado as $key => $value){
                    if($value->conceptopago->tipo=='E'){
                        $detalle = array();
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        //$ticket = Movimiento::find($value->movimiento_id);
                       if(($value->conceptopago_id==24 || $value->conceptopago_id==25) && $value->listapago){
                            $detalle[] = 'RH';
                            if($value->voucher==""){
                                $list=explode(",",$value->listapago);
                                for($x=0;$x<count($list);$x++){
                                    $detalle1 = Detallemovcaja::find($list[0]);
                                    if($detalle1->recibo!=""){
                                        $num = explode('-',$detalle1->recibo);
                                    }
                                }
                            }else{
                                $num = explode('-',$value->voucher);
                            }
                            if(count($num)>1){
                                $detalle[] = $num[0];
                                $detalle[] = $num[1];
                            }else{
                                $detalle[] = "";
                                $detalle[] = $detalle1->recibo;
                            }
                        }else{
                            if($value->caja_id==4){
                                if($value->tipodocumento_id==7){
                                    $detalle[] = 'BV';    
                                }elseif($value->tipodocumento_id==6){
                                    $detalle[] = 'FT';    
                                }else{
                                    $detalle[] = $value->formapago;    
                                }
                            }else{
                                $detalle[] = $value->formapago;
                            }
                            if($value->voucher!="")
                                $num = explode('-',$value->voucher);
                            else
                                $num = explode('-','1-'.$value->numero);
                            if(count($num)>1){
                                $detalle[] = $num[0];
                                $detalle[] = $num[1];
                            }else{
                                $detalle[] = "";
                                $detalle[] = $num[0];
                            }
                        }
                        if(!is_null($value->persona)){
                            if($value->persona->bussinesname!=null){
                                $detalle[] = $value->persona->bussinesname;
                            }else{
                                $detalle[] = $value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres;
                            }
                            $detalle[] = ($value->persona->ruc==""?"00000":$value->persona->ruc);
                        }else{
                            $detalle[] = "";
                            $detalle[] = "00000";
                        }
                        $detalle[] = $value->conceptopago->nombre;
                        $detalle[] = number_format($value->total,2,'.','');
                        $detalle[] = $value->comentario;
                        $detalle[] = $value->responsable2;
                        $total = $total + number_format($value->total,2,'.','');
                        $detalle[] = "101103";
                        $detalle[] = $value->conceptopago->cuenta;
                        $sheet->row($c,$detalle);
                        $c=$c+1;
                    }
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
 
            });
            
            $excel->sheet('NotaCredito', function($sheet) use($request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Tipo";
                $cabecera[] = "Numero";
                $cabecera[] = "Usuario";
                $cabecera[] = "RUC";
                $cabecera[] = "Razon Social";
                $cabecera[] = "Descripcion";
                $cabecera[] = "Subtotal";
                $cabecera[] = "CTA";
                $cabecera[] = "Igv";
                $cabecera[] = "CTA";
                $cabecera[] = "Total";
                $cabecera[] = "CTA";
                $cabecera[] = "Fecha Ref";
                $cabecera[] = "Doc.";
                $cabecera[] = "Nro. Ref";
                $cabecera[] = "Sub Total Ref";
                $cabecera[] = "Igv Ref";
                $cabecera[] = "SUNAT";
                $sheet->row(1,$cabecera);

                $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.Situacion','<>','A')
                            ->where('movimiento.tipomovimiento_id','=',6);
                if($request->input('fechainicial')!=""){
                    $resultado2 = $resultado2->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
                }
                if($request->input('fechafinal')!=""){
                    $resultado2 = $resultado2->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
                }        

                $resultado2        = $resultado2->select('movimiento.*','m2.fecha as fecha2','m2.situacion as situacion2','m2.tarjeta as tarjeta2','m2.tipodocumento_id as tipodocumento_id2','m2.serie as serie2','m2.numero as numero2','m2.movimiento_id as movimiento_id2','m2.subtotal as subtotal2','m2.igv as igv2','m2.estadopago as estadopago2','responsable.nombres as responsable2','m2.nombrepaciente as nombrepaciente2','m2.manual as manual2')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->get();

                $c=2;$d=3;$subtotal=0;$igv=0;$total=0;$band=true;
                foreach ($resultado2 as $key => $value){
                    $detalle = Detallemovcaja::where('movimiento_id','=',$value->movimiento_id)->first();
                    if(!is_null($detalle) && $detalle->servicio_id>0 && !is_null($detalle->servicio)){
                        $glosa=$detalle->servicio->tiposervicio->nombre;
                    }else{
                        $glosa='SERVICIOS';
                    }
                    $rs=Detallemovcaja::where('movimiento_id','=',$value->id)
                            ->where(function($q){
                                $q->where('descripcion','like','%FARMACIA%')
                                  ->orWhere('descripcion','like','MEDICINA%')
                                  ->orWhere('descripcion','like','%MEDICAMENTO%');

                            })->get();
                    $farmacia=0;
                    if(count($rs)>0){
                        foreach ($rs as $k => $v) {
                            $farmacia=$farmacia + round($v->cantidad*$v->precio/1.18,2);
                        }
                        if(round($value->total/1.18,2)>$farmacia){
                            $value->subtotal = $value->subtotal - $farmacia;
                            $servicio='SERVICIOS';
                            $cuenta='70321';
                        }else{
                            $farmacia=0;
                            $servicio='FARMACIA';
                            $cuenta='70121';
                        }
                    }else{
                        if($value->serie2==4){
                            $servicio='FARMACIA';
                            $glosa='FARMACIA';
                            $cuenta='70121';
                        }else{
                            $servicio='SERVICIOS';
                            $cuenta='70321';
                        }
                    }

                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = 'NA';
                    if($value->manual=='S')
                        $detalle[] = $value->serie.'-'.$value->numero;
                    else
                        $detalle[] = ($value->tipodocumento_id2==5?'BC':'FC').str_pad($value->serie,2,'0',STR_PAD_LEFT).'-'.$value->numero;
                    $detalle[] = $value->responsable2;
                    if(!is_null($value->persona)){
                        if($value->tipodocumento_id2=='5'){
                            $detalle[] = "0000";
                            $detalle[] = $value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres;
                        }else{
                            $detalle[] = $value->persona->ruc;
                            $detalle[] = $value->persona->bussinesname;
                        }
                    }else{
                        if($value->nombrepaciente2!=""){
                            $detalle[] = "0000";
                            $detalle[] = $value->nombrepaciente2;
                        }else{
                            $detalle[] = "0000";
                            $detalle[] = "";
                        }
                    }
                    $detalle[] = $glosa;
                    if($value->situacion!="U"){
                        $detalle[] = number_format($value->subtotal,2,'.','');
                        $subtotal = $subtotal + number_format($value->subtotal,2,'.','');
                    }else{
                        $detalle[] = number_format(0,2,'.','');
                        $subtotal = $subtotal + number_format(0,2,'.','');
                    }
                    if($value->serie2=="4"){
                        $detalle[] = "70121";
                    }else{
                        if($value->igv>0 && $servicio!='FARMACIA')
                            $detalle[] = "70321";
                        else
                            $detalle[] = "70121";
                    }
                    //dd($servicio);
                    if($value->situacion!="U"){
                        $detalle[] = number_format($value->igv,2,'.','');
                        $igv = $igv + number_format($value->igv,2,'.','');
                    }else{
                        $detalle[] = number_format(0,2,'.','');
                        $igv = $igv + number_format(0,2,'.','');
                    }
                    $detalle[] = "401111";
                    if($value->situacion!="U"){
                        $detalle[] = number_format($value->total,2,'.','');
                        $total = $total + number_format($value->total,2,'.','');
                    }else{
                        $detalle[] = number_format(0,2,'.','');
                        $total = $total + number_format(0,2,'.','');
                    }
                    $caja = Movimiento::where("movimiento_id",'=',$value->movimiento_id)->where('tipomovimiento_id','=',2)->first();
                    $caja1 = Movimiento::where("movimiento_id",'=',$value->id)->where('tipomovimiento_id','=',2)->first();
                    $ticket=Movimiento::where('id','=',$value->movimiento_id2)->first();
                    if($value->tipodocumento_id2==17){
                        $detalle[] = "121206";
                    }else{
                        if((!is_null($ticket) && $ticket->situacion=="B") || $value->estadopago2=="PP"){
                            $detalle[] = "121204";
                        }elseif(!is_null($caja) && $caja->tipotarjeta!=""){
                            if(is_null($caja1)){
                                $detalle[] = "121205";//SIN MOVIMIETO DE CAJA
                            }else{
                                $detalle[] = "101101";//MOVIMIENTO DE CAJA DE LA NOTA DE CREDITO
                            }
                        }else{
                            if(!is_null($caja1) && $caja1->caja_id!="3"){//DIF DE CONVENIO
                                $detalle[] = "101101";
                            }else{
                                $detalle[] = "101101";
                            }
                        }
                    }
                    $detalle[] = date('d/m/Y',strtotime($value->fecha2));
                    $detalle[] = $value->tipodocumento_id2=="5"?"BV":"FT";
                    if($value->manual2=='S')
                        $detalle[] = $value->serie2.'-'.$value->numero2;
                    else
                        $detalle[] = ($value->tipodocumento_id2==5?'B':'F').str_pad($value->serie2,3,'0',STR_PAD_LEFT).'-'.$value->numero2;
                    $detalle[] = number_format($value->subtotal+$farmacia,2,'.','');
                    $detalle[] = number_format($value->igv,2,'.','');
                    if($value->situacionsunat=='L')
                        // if(substr($value->numero2, 0,1) == 'B'){
                            $sunat = 'ACEPTADO';
                        // }else{
                        //     $sunat = 'PENDIENTE RESPUESTA';
                        // }
                    elseif($value->situacionsunat=='E')
                        $sunat = 'ERROR';
                    elseif($value->situacionsunat=='R')
                        $sunat = 'RECHAZADO';
                    elseif($value->situacionsunat=='P')
                        $sunat = 'ACEPTADO';
                    else
                        $sunat = 'PENDIENTE';
                    $detalle[] = $sunat;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                    if($farmacia>0){
                        $detalle = array();
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        $detalle[] = 'NA';
                        if($value->manual=='S')
                            $detalle[] = $value->serie.'-'.$value->numero;
                        else
                            $detalle[] = ($value->tipodocumento_id2==5?'BC':'FC').str_pad($value->serie,2,'0',STR_PAD_LEFT).'-'.$value->numero;
                        $detalle[] = $value->responsable2;
                        if(!is_null($value->persona)){
                            if($value->tipodocumento_id2=='5'){
                                $detalle[] = "0000";
                                $detalle[] = $value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres;
                            }else{
                                $detalle[] = $value->persona->ruc;
                                $detalle[] = $value->persona->bussinesname;
                            }
                        }else{
                            if($value->nombrepaciente2!=""){
                                $detalle[] = "0000";
                                $detalle[] = $value->nombrepaciente2;
                            }else{
                                $detalle[] = "0000";
                                $detalle[] = "";
                            }
                        }
                        $detalle[] = 'FARMACIA';
                        if($value->situacion!="U"){
                            $detalle[] = number_format($farmacia,2,'.','');
                            $subtotal = $subtotal + number_format($farmacia,2,'.','');
                        }else{
                            $detalle[] = number_format(0,2,'.','');
                            $subtotal = $subtotal + number_format(0,2,'.','');
                        }
                        $detalle[] = "70121";
                        $detalle[] = '';
                        $detalle[] = "";
                        $detalle[] = "";
                        $detalle[] = "";
                        $detalle[] = '';
                        $detalle[] = "";
                        $detalle[] = "";
                        $detalle[] = "";
                        $detalle[] = "";
                        if($value->situacionsunat=='L')
                            if(substr($value->numero2, 0,1) == 'B'){
                                $sunat = 'ACEPTADO';
                            }else{
                                $sunat = 'PENDIENTE RESPUESTA';
                            }
                        elseif($value->situacionsunat=='E')
                            $sunat = 'ERROR';
                        elseif($value->situacionsunat=='R')
                            $sunat = 'RECHAZADO';
                        elseif($value->situacionsunat=='P')
                            $sunat = 'ACEPTADO';
                        else
                            $sunat = 'PENDIENTE';
                        $detalle[] = $sunat;
                        $sheet->row($c,$detalle);
                        $c=$c+1;
                    }

                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = number_format($subtotal,2,'.','');
                $detalle[] = "";
                $detalle[] = number_format($igv,2,'.','');
                $detalle[] = "";
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
 
            });

            $excel->sheet('SaldoCaja', function($sheet) use($request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Caja";
                $cabecera[] = "Ingresos";
                $cabecera[] = "Egresos";
                $cabecera[] = "Saldo";
                $cabecera[] = "Responsable";
                $cabecera[] = "Ingr. BV-FT";
                $cabecera[] = "Cobranza Efectivo";
                $cabecera[] = "Cobranza Tarjeta";
                $cabecera[] = "Ingr. x Trasnf.";
                $cabecera[] = "Trasnf.";
                $cabecera[] = "Garant.-Sobr.-Otros Ing";
                $sheet->row(1,$cabecera);

                $resultado        = Movimiento::join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->where('movimiento.tipomovimiento_id','=',2)
                            ->where('movimiento.conceptopago_id','=','2')
                            ->whereNotIn('movimiento.caja_id',[6,7]);
                if($request->input('fechainicial')!=""){
                    $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
                }
                if($request->input('fechafinal')!=""){
                    $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
                }        

                $resultado        = $resultado->select('movimiento.*','responsable.nombres as responsable2')->orderBy('movimiento.fecha', 'ASC')->get();
                $c=2;$d=3;$total=0;$band=true;

                $listConcepto     = array();
                $listConcepto[]   = 6;//TRANSF CAJA INGRESO
                $listConcepto[]   = 15;//TRANSF TARJETA INGRESO
                $listConcepto[]   = 17;//TRANSF SOCIO INGRESO
                $listConcepto[]   = 21;//TRANSF BOLETEO INGRESO
                foreach ($resultado as $key => $value1){
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
                                //->where('movimiento.id', '>', $aperturas[$valor])
                                //->where('movimiento.id', '<', $cierres[$valor])
                                /*->where('movimiento.situacion', '<>', 'A')*/->where('movimiento.situacion', '<>', 'R')
                                ->where(function($query){
                                    $query
                                        ->whereNotIn('movimiento.conceptopago_id',[31])
                                        ->orWhere('m2.situacion','<>','R');
                                });
                    $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','responsable.nombres as responsable2')->orderBy('conceptopago.tipo', 'asc')->orderBy('conceptopago.orden', 'asc')->orderBy('conceptopago.id', 'asc')->orderBy('movimiento.tipotarjeta', 'asc')->orderBy('movimiento.numero', 'asc');
                    $lista = $resultado->get();
                    $ingreso=0;$egreso=0;$visa=0;$master=0;$garantia=0;$efectivo=0;$transferenciai=0;$cliente=0;$cobrae=0;$cobrat=0;$itransferencia=0;
                    foreach ($lista as $key => $value){
                        if($value->conceptopago_id==33  && $value->situacion<>"A"){
                            $transferenciai = $transferenciai + $value->total;
                        }
                        if($value->conceptopago_id==3  && $value->tipotarjeta=='' && $value->situacion<>"A"){
                            if ($value->tipodocumento_id != 15) {
                                $Venta = Movimiento::find($value->movimiento_id);
                                $cliente = $cliente + $Venta->total;
                            }
                        }
                        if(($value->conceptopago_id==23 || $value->conceptopago_id==32) && $value->situacion<>"A"){
                            if($value->tipotarjeta==''){
                                $cobrae = $cobrae + $value->total;
                            }else{
                                $cobrat = $cobrat + $value->total;
                            }
                        }
                        if(in_array($value->conceptopago_id, $listConcepto) && $value->situacion<>"A"){
                            $itransferencia = $itransferencia + $value->total;
                        }
                        if($value->conceptopago_id<>2 /*&& $value->situacion<>'A'*/){
                            if($value->conceptopago->tipo=="I" && $value->situacion<>"A"){  
                              
                                if($value->conceptopago_id<>10 && $value->conceptopago_id<>150){//GARANTIA
                                    if($value->conceptopago_id<>15 && $value->conceptopago_id<>17 && $value->conceptopago_id<>19 && $value->conceptopago_id<>21){
                                        if ($value->tipodocumento_id != 15) {
                                            //echo $value->total."@";
                                            $ingreso = $ingreso + $value->total;
                                        }
                                            
                                    }elseif(($value->conceptopago_id==15 || $value->conceptopago_id==17 || $value->conceptopago_id==19 || $value->conceptopago_id==21) && $value->situacion=="C"){
                                        $ingreso = $ingreso + $value->total;    
                                    }
                                }else{
                                    $garantia = $garantia + $value->total;
                                }

                                if($value->conceptopago_id<>10 && $value->conceptopago_id<>150){//GARANTIA
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
                                        if($value->conceptopago_id==8){  //HONORARIOS MEDICOS
                                            if($value->id == 675344 || $value->situacion <> "A"){
                                                $ingreso  = $ingreso - $value->total;
                                                $efectivo = $efectivo - $value->total;
                                            }
                                        }else{
                                            if($value->situacion <> "A"){
                                                $egreso  = $egreso + $value->total;
                                            }
                                        }
                                }elseif(($value->conceptopago_id==14 || $value->conceptopago_id==16 || $value->conceptopago_id==18 || $value->conceptopago_id==20) && $value->situacion2=='C' && $value->situacion <> "A"){
                                    $egreso  = $egreso + $value->total;
                                }
                            }
                        }
                    }   
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value1->fecha));
                    
                    if(isset($value)){
                        $detalle[] = $value->caja->nombre;
                        if(isset($value->caja_id)){
                            if($value->caja_id==3) $ingreso = $ingreso + $apertura->total;                        
                        } else {
                            $detalle[] = "ERROR CAJA_ID";
                        }
                    } else {
                        $detalle[] = "ERROR CAJA_CIERRE";
                    }
                    $ingreso = $ingreso - $visa - $master;
                    $detalle[] = number_format($ingreso,2,'.','');
                    $detalle[] = number_format($egreso,2,'.','');
                    $detalle[] = number_format($ingreso - $egreso,2,'.','');
                    $detalle[] = $value1->responsable->nombres;
                    $detalle[] = number_format($cliente,2,'.','');
                    $detalle[] = number_format($cobrae,2,'.','');
                    $detalle[] = number_format($cobrat,2,'.','');
                    $detalle[] = number_format($transferenciai,2,'.','');
                    $detalle[] = number_format($itransferencia,2,'.','');
                    $detalle[] = number_format($ingreso - $cliente - $cobrae - $transferenciai - $itransferencia,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                    
                }
 
            });
            
        })->export('xls');
    }

    public function excelVentaBizlink(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','N')
                            ->where('m2.tipomovimiento_id','=',1)
                            ->whereNotIn('movimiento.situacionsunat',['P']);
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
        }        

        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','m2.tarjeta as tarjeta2',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'),'responsable.nombres as responsable2')->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->orderBy('movimiento.fecha', 'ASC')->get();

        Excel::create('ExcelVentaBizlink', function($excel) use($resultado,$request) {
            
            $excel->sheet('VentasBizlink', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Doc.";
                $cabecera[] = "Nro.";
                $cabecera[] = "Cliente";
                $cabecera[] = "RUC";
                $cabecera[] = "Subtotal";
                $cabecera[] = "Igv";
                $cabecera[] = "Total";
                $cabecera[] = "Sunat";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$subtotal=0;$igv=0;$total=0;$band=true;
                foreach ($resultado as $key => $value){
                    //FARMACIA
                    if (($value->serie==7 || $value->serie==9) && $band) {
                        $band=false;
                        $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','S')
                            ->where('movimiento.tipodocumento_id','<>',15)
                            ->whereNotIn('movimiento.situacionsunat',['P']);
                        if($request->input('fechainicial')!=""){
                            $resultado2 = $resultado2->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
                        }
                        if($request->input('fechafinal')!=""){
                            $resultado2 = $resultado2->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
                        }        

                        $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2','m2.tarjeta as tarjeta2',DB::raw('CONCAT(case when movimiento.tipodocumento_id=4 then "F" else "B" end,movimiento.serie,"-",movimiento.numero) as numero2'))->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')->get();

                        foreach ($resultado2 as $key1 => $value1){
                            $detalle = array();
                            $detalle[] = date('d/m/Y',strtotime($value1->fecha));
                            $person = Person::find($value1->persona_id);
                            if ($person !== null) {
                                $detalle[] = $person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres;
                            }else{
                                $detalle[] = $value1->nombrepaciente;
                            }
                            $detalle[] = $value1->tipodocumento_id==5?'BV':'FT';
                            if($value1->manual=='S')
                                $detalle[] = $value1->serie.'-'.$value1->numero;
                            else
                                $detalle[] = ($value1->tipodocumento_id==5?'B':'F').str_pad($value1->serie,3,'0',STR_PAD_LEFT).'-'.$value1->numero;
                            if($value1->tipodocumento_id==4){//Factura
                                if(!is_null($value1->empresa_id) && $value1->empresa_id>0){
                                    $detalle[] = $value1->empresa->bussinesname;
                                    $detalle[] = $value1->empresa->ruc;
                                }else{
                                    $detalle[] = "";
                                    $detalle[] = "";
                                }
                            }else{
                                $detalle[] = "";
                                $detalle[] = "0000";
                            }
                            if($value1->situacion=="U"){
                                $detalle[] = number_format(0,2,'.','');
                                $detalle[] = number_format(0,2,'.','');
                                $detalle[] = number_format(0,2,'.','');
                            }else{
                                $detalle[] = number_format($value1->subtotal,2,'.','');
                                $detalle[] = number_format($value1->igv,2,'.','');
                                $detalle[] = number_format($value1->total,2,'.','');
                                $subtotal = $subtotal + number_format($value1->subtotal,2,'.','');
                                $igv = $igv + number_format($value1->igv,2,'.','');
                                $total = $total + number_format($value1->total,2,'.','');
                            }
                            if($value1->situacionsunat=='L')
                                $sunat = 'PENDIENTE RESPUESTA';
                            elseif($value1->situacionsunat=='R')
                                $sunat = 'RECHAZADO';
                            elseif($value1->situacionsunat=='E')
                                $sunat = 'ERROR';
                            elseif($value1->situacionsunat=='P')
                                $sunat = 'ACEPTADO';
                            else
                                $sunat = 'PENDIENTE';
                            $detalle[] = $sunat;
                            $sheet->row($c,$detalle);
                            $c=$c+1;
                        }
                    }
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $ticket = Movimiento::find($value->movimiento_id);
                    $person = Person::find($ticket->persona_id);
                    $detalle[] = $person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres;
                    $detalle[] = $value->tipodocumento_id==5?'BV':'FT';
                    if($value->manual=='S')
                        $detalle[] = $value->serie.'-'.$value->numero;
                    else
                        $detalle[] = ($value->tipodocumento_id==5?'B':'F').str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.$value->numero;
                    if($value->tipodocumento_id==4){//Factura
                        $detalle[] = $value->persona->bussinesname;
                        $detalle[] = $value->persona->ruc;
                    }else{
                        $detalle[] = "";
                        $detalle[] = "0000";
                    }
                    if($value->situacion=="U"){
                        $detalle[] = number_format(0,2,'.','');
                        $detalle[] = number_format(0,2,'.','');
                        $detalle[] = number_format(0,2,'.','');
                    }else{
                        $detalle[] = number_format($value->subtotal,2,'.','');
                        $detalle[] = number_format($value->igv,2,'.','');
                        $detalle[] = number_format($value->total,2,'.','');
                        $subtotal = $subtotal + number_format($value->subtotal,2,'.','');
                        $igv = $igv + number_format($value->igv,2,'.','');
                        $total = $total + number_format($value->total,2,'.','');
                    }
                    if($value->situacionsunat=='L')
                        $sunat = 'PENDIENTE RESPUESTA';
                    elseif($value->situacionsunat=='E')
                        $sunat = 'ERROR';
                    elseif($value->situacionsunat=='R')
                        $sunat = 'RECHAZADO';
                    elseif($value->situacionsunat=='P')
                        $sunat = 'ACEPTADO';
                    else
                        $sunat = 'PENDIENTE';
                    $detalle[] = $sunat;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = number_format($subtotal,2,'.','');
                $detalle[] = number_format($igv,2,'.','');
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
 
            });

            
        })->export('xls');
    }

    public function declarar(Request $request){
        
       /*$resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.tipodocumento_id','=',5)
                            ->where('movimiento.ventafarmacia','=','N')
                            ->where('movimiento.fecha','>=','2018-01-06')
                            ->where('movimiento.fecha','<=','2018-01-12')
                            ->where('movimiento.serie','=',9)
                            ->where('m2.tipomovimiento_id','=',1)
                            ->where('movimiento.situacion','<>','U')
                            ->select('movimiento.*','m2.persona_id as paciente2_id','m2.plan_id as plan2_id')
                            ->orderBy('movimiento.id','asc')
                            ->get(); 
        $c=198;
        foreach ($resultado as $key => $value) {$c=$c+1;
            $value->numero=$c;
            $value->save();
        }*/
        //die();
        //ADMISION
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            //->where('movimiento.tipodocumento_id','=',4)
                            ->where('movimiento.ventafarmacia','=','N')
                            // ->where('movimiento.serie,','=','8')
                            // ->whereIn('movimiento.numero',['14866','14996'])
                            ->where('movimiento.fecha','>=',$request->input('fechainicial'))
                            ->where('movimiento.fecha','<=',$request->input('fechafinal'))
                            ->where('m2.tipomovimiento_id','=',1)
                            ->where('movimiento.manual','like','N')
                            //->where('movimiento.serie','=',7)->where('movimiento.numero','=',910)
                            ->select('movimiento.*','m2.persona_id as paciente2_id','m2.plan_id as plan2_id')
                            ->orderBy('movimiento.id','asc')
                            //->limit(1)
                            ->get();

        // dd($resultado); 
        foreach ($resultado as $key => $value) {
            $numero=($value->tipodocumento_id==4?"F":"B").str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT);
            error_log("CONSULTANDO COMPROBANTE: ".$numero);
            $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICEHEADER')->where('serieNumero','like',$numero)->first();
            //$rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICEHEADER')->where('serieNumero','like',$numero)->first();
            if(count($rs)==0){
                if($value->tipodocumento_id==5){
                    $codigo="03";
                    $abreviatura="B";
                }else{
                    $codigo="01";
                    $abreviatura="F";
                }
                //Array Insert facturacion
                $person = Person::find($value->persona_id);
                $persona = Person::find($value->paciente2_id);
                $plan = Plan::find($value->plan2_id);
                $historia = Historia::where('person_id','=',$value->paciente2_id)->first();
                $columna1=6;
                $columna2="20480082673";//RUC HOSPITAL
                $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                $columna4=$codigo;
                $value->numero=str_pad($value->numero,8,'0',STR_PAD_LEFT);
                $columna5=$abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.$value->numero;
                $columna6=$value->fecha;
                $columna7="sistemas@hospitaljuanpablo.pe";
                if($codigo=="03"){//BOLETA
                    if(strlen($person->dni)<>8 || ($value->total)<700){
                        //$columna8=0;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        //$columna9='-';
                        $columna8=1;
                        $columna9='99999999';
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
                $columna13=number_format($value->subtotal,2,'.','');
                $columna14='0.00';
                $columna15='0.00';
                $columna16="";
                $columna17=number_format($value->igv,2,'.','');
                $columna18='0.00';
                $columna19='0.00';
                $columna20=number_format($value->total,2,'.','');
                $columna21=1000;
                $letras = new EnLetras();
                $columna22=trim($letras->ValorEnLetras($columna20, "SOLES" ));//letras
                $columna23='9670';
                $columna24=substr("CONVENIO: ".$plan->nombre,0,100);
                $columna25='9199';
                $columna26=substr(trim($persona->apellidopaterno." ".$persona->apellidomaterno." ".$persona->nombres),0,100);
                $columna27='9671';                
                $columna28='HISTORIA CLINICA: '.(is_null($historia)?'':$historia->numero).' - CONDICION: '.($value->situacion=='B'?'PENDIENTE':'PAGADO');
                $columna29='9672';
                $columna30='DNI: '.$persona->dni;
                $codigoventa = "0101";
                $horaemision = date("H:i:s");
                $codigoUbicacion = "0000";
                /***********
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
                    [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22, $columna23, $columna24, $columna25, $columna26, $columna27, $columna28, $columna29, $columna30]);*/

                DB::connection('sqlsrvtst21')->insert("INSERT INTO SPE_EINVOICEHEADER(
                    correoEmisor,
                    correoAdquiriente,
                    numeroDocumentoEmisor,
                    tipoDocumentoEmisor,
                    tipoDocumento,
                    razonSocialEmisor,
                    nombreComercialEmisor,
                    serieNumero,
                    fechaEmision,
                    ubigeoEmisor,
                    direccionEmisor,
                    urbanizacion,
                    provinciaEmisor,
                    departamentoEmisor,
                    distritoEmisor,
                    paisEmisor,
                    numeroDocumentoAdquiriente,
                    tipoDocumentoAdquiriente,
                    razonSocialAdquiriente,
                    tipoMoneda,
                    totalValorVentaNetoOpGravadas,
                    totalValorVentaNetoOpNoGravada,
                    totalValorVentaNetoOpExonerada,
                    totalIgv,
                    totalImpuestos,
                    totalVenta,
                    codigoLeyenda_1,
                    textoLeyenda_1,
                    codigoAuxiliar40_1,
                    textoAuxiliar40_1,
                    tipoOperacion,
                    horaEmision,
                    codigoLocalAnexoEmisor,
                    codigoAuxiliar100_1,
                    textoAuxiliar100_1,
                    codigoAuxiliar100_2,
                    textoAuxiliar100_2,
                    codigoAuxiliar100_3,
                    textoAuxiliar100_3,
                    codigoAuxiliar100_4,
                    textoAuxiliar100_4) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    [$columna7, '-', $columna2, $columna1, $columna4, $columna3, '-',$columna5, $columna6,"140106","Avenida Grau 1461",'-','LAMBAYEQUE','CHICLAYO','La Victoria','PE', $columna9, $columna8, $columna10,$columna12,$columna13, $columna14, $columna15, $columna17, $columna17, $columna20, $columna21, $columna22,'9011','18%',$codigoventa,$horaemision,$codigoUbicacion, $columna23, $columna24, $columna25, $columna26, $columna27, $columna28, $columna29, $columna30]);
                if($abreviatura=="F"){
                    /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        serieNumero,
                        tipoDocumento,
                        clave,
                        valor) 
                        values (?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);*/

                    DB::connection('sqlsrvtst21')->insert('insert into SPE_EINVOICEHEADER_ADD(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        serieNumero,
                        tipoDocumento,
                        clave,
                        valor) 
                        values (?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);
                }else{
                    /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        serieNumero,
                        tipoDocumento,
                        clave,
                        valor) 
                        values (?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna5, $columna4, 'lugarDestino', $columna101]);*/
                }
                //---
                
                //Array Insert Detalle Facturacion
                $resultado1 = Detallemovcaja::join('movimiento as m','m.id','=','detallemovcaja.movimiento_id')
                             ->where('m.id','=',$value->movimiento_id)
                             ->select('detallemovcaja.*');
                $lista      = $resultado1->get();
                $c=0;
                foreach ($lista as $key1 => $value1) {
                    $columnad1=$c+1;
                    $servicio = Servicio::find($value1->servicio_id);
                    if(!is_null($servicio) && $servicio->tipopago=="Convenio"){
                        $columnad2=$servicio->tarifario->codigo;
                        $columnad3=$servicio->tarifario->nombre;    
                    }else{
                        $columnad2="-";
                        if($value1->servicio_id>0){
                            $columnad3=$servicio->nombre;
                        }else{
                            $columnad3=trim($value1->descripcion);
                        }
                    }
                    $columnad4=$value1->cantidad;
                    $columnad5="ZZ";
                    $columnad6=number_format($value1->pagohospital/1.18,2,".","");
                    $columnad7=number_format($value1->pagohospital,2,".","");
                    $columnad8="01";
                    $columnad9=number_format($columnad4*$columnad6,2,".","");
                    $columnad10="10";
                    $columnad11=number_format($columnad9*0.18,2,".","");
                    $columnad12='0.00';
                    $columnad13='0.00';

                    $columnad14='8142';
                    if(empty($value1->idunspsc) || strlen($value1->idunspsc)==0 || $value1->idunspsc<=0){
                        if(empty($servicio)){
                            $columnad15 = "85101500";
                        }else{
                            $columnad15 = $servicio->tiposervicio->idunspsc;
                        }
                    }else{
                        $columnad15=substr($value1->idunspsc, 0, 8);
                    }
                    //$columnad15=substr($value1->idunspsc, 0, 8);
                    /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
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
                    importeIgv,
                    codigoAuxiliar100_1,
                    textoAuxiliar100_1
                    )
                    values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11, $columnad14, $columnad15]);*/

                    DB::connection('sqlsrvtst21')->insert('insert into SPE_EINVOICEDETAIL(
                    numeroDocumentoEmisor,
                    tipoDocumentoEmisor,
                    tipoDocumento,
                    serieNumero,
                    numeroOrdenItem,
                    codigoProducto,
                    codigoProductoSunat,
                    descripcion,
                    cantidad,
                    unidadMedida,
                    importeUnitarioSinImpuesto,
                    importeUnitarioConImpuesto,
                    codigoImporteUnitarioConImpues,
                    importeTotalSinImpuesto,
                    codigoRazonExoneracion,
                    importeIgv,
                    montoBaseIgv,
                    tasaIgv,
                    importeTotalImpuestos
                    )
                    values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [$columna2, $columna1, $columna4, $columna5, $columnad1, $columnad2, $columnad15, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11, $columnad9,18, $columnad11]);

                    ///UBL 2.0 SIN CODSUNAT
                    /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
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
                    [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);*/

                    $c=$c+1;
                }
                DB::connection('sqlsrvtst21')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?',['A',$columna5]);  
            }
        } 
        //return "OK";exit();
        //FARMACIA
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.tipodocumento_id','<>',15)
                            ->where('movimiento.ventafarmacia','=','S')
                            ->where('movimiento.fecha','>=',$request->input('fechainicial'))
                            ->where('movimiento.fecha','<=',$request->input('fechafinal'))
                            ->where('movimiento.manual','like','N')
                            ->select('movimiento.*')
                            ->orderBy('movimiento.id','asc')
                            ->get();     
        foreach ($resultado as $key => $value) {
            $numero=($value->tipodocumento_id==4?"F":"B").str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT);
            $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICEHEADER')->where('serieNumero','like',$numero)->first();
            //$rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICEHEADER')->where('serieNumero','like',$numero)->first();
            if(count($rs)==0){
                if($value->tipodocumento_id==5){
                    $codigo="03";
                    $abreviatura="B";
                }else{
                    $codigo="01";
                    $abreviatura="F";
                }
                //Array Insert facturacion
                $person = Person::find($value->persona_id);
                $persona = Person::find($value->paciente2_id);
                if($value->conveniofarmacia_id>0){
                    $plan = Plan::find($value->conveniofarmacia_id);
                }
                if($value->persona_id>0){
                    $historia = Historia::where('person_id','=',$value->persona_id)->first();
                }
                $columna1=6;
                $columna2="20480082673";//RUC HOSPITAL
                $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                $columna4=$codigo;
                $columna5=$abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT);
                $columna6=$value->fecha;
                $columna7="sistemas@hospitaljuanpablo.pe";
                if($codigo=="03"){//BOLETA
                    if(is_null($person)){
                        //$columna8=0;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        //$columna9='-';
                        $columna8=1;
                        $columna9='99999999';
                        $columna10="CLIENTES VARIOS";//Razon social
                    }elseif(strlen($person->dni)<>8 || ($value->total)<700){
                        //$columna8=0;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        //$columna9='-';
                        $columna8=1;
                        $columna9='99999999';
                        $columna10="CLIENTES VARIOS";//Razon social
                    }else{
                        $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        $columna9=$person->dni;
                        $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                    }
                }else{
                    $columna8=6;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0}
                    if($columna5=="F004-00010804"){
                        //dd($value);
                    }
                    $columna9=$value->empresa->ruc;
                    $columna10=trim($value->empresa->bussinesname);//Razon social
                }
                if(is_null($person) || trim($person->direccion)==''){
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
                if($value->igv>0){
                    $columna13=number_format($value->subtotal,2,'.','');
                    $columna14='0.00';
                    $columna15='0.00';
                }else{
                    $columna13='0.00';
                    $columna14=number_format($value->subtotal,2,'.','');
                    $columna15='0.00';
                }
                $columna16="";
                $columna17=number_format($value->igv,2,'.','');
                $columna18='0.00';
                $columna19='0.00';
                $columna20=number_format($value->total,2,'.','');
                $columna21=1000;
                $letras = new EnLetras();
                $columna22=trim($letras->ValorEnLetras($columna20, "SOLES" ));//letras
                $columna23='9670';
                if($value->conveniofarmacia_id>0 && !is_null($plan)){
                    $columna24=substr("CONVENIO: ".$plan->nombre,0,100);
                }else{
                    $columna24=substr("CONVENIO: PARTICULAR",0,100);
                }
                $columna25='9199';
                if($value->persona_id>0){
                    $columna26=substr(trim($person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres),0,100);
                }else{
                    $columna26=$value->nombrepaciente;
                }
                $columna27='9671'; 
                if($value->persona_id>0){
                    $columna28='HISTORIA CLINICA: '.(!is_null($historia)?$historia->numero:'SN').' - CONDICION: '.($value->estadopago=='PP'?'PENDIENTE':'PAGADO');
                }else{
                    $columna28='HISTORIA CLINICA: SN - CONDICION: '.($value->estadopago=='PP'?'PENDIENTE':'PAGADO');
                }
                $columna29='9672';
                if($value->persona_id>0){
                    $columna30='DNI: '.$person->dni;
                }else{
                    $columna30='DNI: -';
                }
                $codigoventa = "0101";
                $horaemision = date("H:i:s");
                $codigoUbicacion = "0000";
                /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER (
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
                    [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22, $columna23, $columna24, $columna25, $columna26, $columna27, $columna28, $columna29, $columna30]);*/

                DB::connection('sqlsrvtst21')->insert("INSERT INTO SPE_EINVOICEHEADER(
                    correoEmisor,
                    correoAdquiriente,
                    numeroDocumentoEmisor,
                    tipoDocumentoEmisor,
                    tipoDocumento,
                    razonSocialEmisor,
                    nombreComercialEmisor,
                    serieNumero,
                    fechaEmision,
                    ubigeoEmisor,
                    direccionEmisor,
                    urbanizacion,
                    provinciaEmisor,
                    departamentoEmisor,
                    distritoEmisor,
                    paisEmisor,
                    numeroDocumentoAdquiriente,
                    tipoDocumentoAdquiriente,
                    razonSocialAdquiriente,
                    tipoMoneda,
                    totalValorVentaNetoOpGravadas,
                    totalValorVentaNetoOpNoGravada,
                    totalValorVentaNetoOpExonerada,
                    totalIgv,
                    totalImpuestos,
                    totalVenta,
                    codigoLeyenda_1,
                    textoLeyenda_1,
                    codigoAuxiliar40_1,
                    textoAuxiliar40_1,
                    tipoOperacion,
                    horaEmision,
                    codigoLocalAnexoEmisor,
                    codigoAuxiliar100_1,
                    textoAuxiliar100_1,
                    codigoAuxiliar100_2,
                    textoAuxiliar100_2,
                    codigoAuxiliar100_3,
                    textoAuxiliar100_3,
                    codigoAuxiliar100_4,
                    textoAuxiliar100_4) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    [$columna7, '-', $columna2, $columna1, $columna4, $columna3, '-',$columna5, $columna6,"140106","Avenida Grau 1461",'-','LAMBAYEQUE','CHICLAYO','La Victoria','PE', $columna9, $columna8, $columna10,$columna12,$columna13, $columna14, $columna15, $columna17, $columna17, $columna20, $columna21, $columna22,'9011','18%',$codigoventa,$horaemision,$codigoUbicacion, $columna23, $columna24, $columna25, $columna26, $columna27, $columna28, $columna29, $columna30]);

                if($abreviatura=="F"){
                    /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        serieNumero,
                        tipoDocumento,
                        clave,
                        valor) 
                        values (?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);*/

                    DB::connection('sqlsrvtst21')->insert('insert into SPE_EINVOICEHEADER_ADD(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        serieNumero,
                        tipoDocumento,
                        clave,
                        valor) 
                        values (?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);
                }else{
                    /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        serieNumero,
                        tipoDocumento,
                        clave,
                        valor) 
                        values (?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna5, $columna4, 'lugarDestino', $columna101]);*/
                }
                //---
                
                //Array Insert Detalle Facturacion
                $resultado1 = Detallemovimiento::where('detallemovimiento.movimiento_id','=',$value->id)
                             ->select('Detallemovimiento.*');
                $lista      = $resultado1->get();
                $c=0;
                foreach ($lista as $key1 => $value1) {
                    $columnad1=$c+1;
                    $columnad2=$value1->producto_id;
                    $columnad3=$value1->producto->nombre;    
                    $columnad4=$value1->cantidad;
                    //PRECIO UNITARIO
                    if ($value->conveniofarmacia_id !== null) {
                        $valaux = round(($value1->precio*$value1->cantidad), 2);
                        $precioaux = $value1->precio - ($value1->precio*($value->descuentokayros/100));
                        $dscto = round(($precioaux*$value1->cantidad),2);
                        $subtotal1 = round(($dscto*($value->copago/100)),2);
                        $value1->precio=round($subtotal1/$value1->cantidad,2);
                    }
                    //
                    $columnad5="NIU";
                    if($value->igv>0){
                        $columnad6=number_format($value1->precio/1.18,2,".","");
                    }else{
                        $columnad6=number_format($value1->precio,2,".","");
                    }
                    $columnad7=$value1->precio;
                    $columnad8="01";
                    $columnad9=number_format($columnad4*$columnad6,2,".","");
                    $tasaigv = "18";
                    $montobaseigv = $columnad9;
                    if($value->igv>0){
                        $columnad10="10";
                        $columnad11=number_format($columnad9*0.18,2,".","");
                    }else{
                        $columnad10="30";
                        $columnad11='0.00';
                        $tasaigv = "0.00";
                        $montobaseigv = "0.00";
                    }
                    $columnad12='0.00';
                    $columnad13='0.00';

                    $columnad14='8142';
                    $columnad15=substr($value1->idunspsc, 0, 8);
                    /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
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
                    importeIgv,
                    codigoAuxiliar100_1,
                    textoAuxiliar100_1
                    )
                    values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11, $columnad14, $columnad15]);*/

                    DB::connection('sqlsrvtst21')->insert('insert into SPE_EINVOICEDETAIL(
                    numeroDocumentoEmisor,
                    tipoDocumentoEmisor,
                    tipoDocumento,
                    serieNumero,
                    numeroOrdenItem,
                    codigoProducto,
                    codigoProductoSunat,
                    descripcion,
                    cantidad,
                    unidadMedida,
                    importeUnitarioSinImpuesto,
                    importeUnitarioConImpuesto,
                    codigoImporteUnitarioConImpues,
                    importeTotalSinImpuesto,
                    codigoRazonExoneracion,
                    importeIgv,
                    montoBaseIgv,
                    tasaIgv,
                    importeTotalImpuestos
                    )
                    values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [$columna2, $columna1, $columna4, $columna5, $columnad1, $columnad2, $columnad15, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11, $montobaseigv,$tasaigv, $columnad11]);

                    /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
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
                    [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);*/
                    $c=$c+1;
                }
                error_log("update SPE_EINVOICEHEADER set bl_estadoRegistro = 'A' where serieNumero  = ".$columna5);
                DB::connection('sqlsrvtst21')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?', ['A',$columna5]);  
            }

        } 
        
        //NOTA DE CREDITO
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',6)
                            ->where('movimiento.fecha','>=',$request->input('fechainicial'))
                            ->where('movimiento.fecha','<=',$request->input('fechafinal'))
                            //->where('m2.fecha','>=','2018-01-01')
                            //->where('movimiento.situacion','=','N')
                            ->where('movimiento.manual','like','N')
                            ->select('movimiento.*','m2.serie as serie2','m2.numero as numero2','m2.tipodocumento_id as tipodocumento2_id','m2.ventafarmacia as ventafarmacia2','m2.nombrepaciente as nombrepaciente2','m2.persona_id as persona2_id','m2.movimiento_id as movimiento_id2','m2.conveniofarmacia_id as conveniofarmacia_id2','m2.manual as manual2')
                            ->orderBy('movimiento.id','asc')
                            ->get();
        //dd($resultado);     
        foreach ($resultado as $key => $value) {
            $numero=($value->tipodocumento2_id==5?"BC":"FC").str_pad($value->serie,2,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT);
            $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICEHEADER')->where('serieNumero','like',$numero)->first();
            //$rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICEHEADER')->where('serieNumero','like',$numero)->first();
            if(count($rs)==0){
                if($value->tipodocumento2_id==5){
                    $codigo="03";
                    $abreviatura="BC";
                }else{
                    $codigo="01";
                    $abreviatura="FC";
                }
                //Array Insert facturacion
                $person = Person::find($value->persona_id);
                if($value->conveniofarmacia_id>0){
                    $plan = Plan::find($value->conveniofarmacia_id);
                }
                if($value->persona_id>0){
                    $historia = Historia::where('person_id','=',$value->persona_id)->first();
                }
                $columna1=6;
                $columna2="20480082673";//RUC HOSPITAL
                $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
                $columna4="07";
                $columna5=$abreviatura.str_pad($value->serie,2,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT);
                $columna6=$value->fecha;
                $columna7="sistemas@hospitaljuanpablo.pe";
                if($codigo=="03"){//BOLETA
                    if(is_null($person)){
                        //$columna8=0;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        //$columna9='-';
                        $columna8=1;
                        $columna9='99999999';
                        $columna10="CLIENTES VARIOS";//Razon social
                    }elseif(strlen($person->dni)<>8 || ($value->total)<700){
                        //$columna8=0;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        //$columna9='-';
                        $columna8=1;
                        $columna9='99999999';
                        $columna10="CLIENTES VARIOS";//Razon social
                    }else{
                        $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                        $columna9=$person->dni;
                        $columna10=trim($person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
                    }
                }else{
                    $columna8=6;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                    $columna9=$person->ruc;
                    $columna10=trim($person->bussinesname);//Razon social
                }
                if(is_null($person) || trim($person->direccion)==''){
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
/*
                $columna13=number_format($value->subtotal,2,'.','');
                $columna14='0.00';
                $columna15='0.00';
*/
                if($value->igv>0){
                    $columna13=number_format($value->subtotal,2,'.','');
                    $columna14='0.00';
                    $columna15='0.00';
                }else{
                    $columna13='0.00';
                    $columna14=number_format($value->subtotal,2,'.','');
                    $columna15='0.00';
                }
                $columna16="";
                $columna17=number_format($value->igv,2,'.','');
                $columna18='0.00';
                $columna19='0.00';
                $columna20=number_format($value->total,2,'.','');
                $columna21=1000;
                $letras = new EnLetras();
                $columna22=trim($letras->ValorEnLetras($columna20, "SOLES" ));//letras
                $columna23=$codigo;
                if($value->manual2=='N'){
                    $columna24=($value->tipodocumento2_id==5?"B":"F").str_pad($value->serie2,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero2,8,'0',STR_PAD_LEFT);
                }else{
                    $columna24=str_pad($value->serie2,4,'0',STR_PAD_LEFT).'-'.str_pad($value->numero2,8,'0',STR_PAD_LEFT);
                }
                $columna25=$value->comentario;
                $columna26='01';
                $columna27='9199';
                if($value->ventafarmacia2=='S'){
                    if($value->persona2_id>0){
                        $person2 = Person::find($value->persona2_id);
                        $paciente=$person2->apellidopaterno.' '.$person2->apellidomaterno.' '.$person2->nombres;
                        $dni=$person2->dni;
                        $Historia = Historia::where('person_id','=',$value->persona2_id)->first();
                        $historia = !is_null($Historia)?$Historia->numero:'SN';
                        if($value->conveniofarmacia_id2>0){
                            $plan = Plan::find($value->conveniofarmacia_id2);
                            $convenio = $plan->nombre;
                        }else{
                            $convenio='PARTICULAR';
                        }
                    }else{
                        $paciente=$value->nombrepaciente2;
                        $historia='SN';
                        $dni='-';
                        $convenio='PARTICULAR';
                    }
                }else{
                    if($value->tipodocumento2_id==17){
                        $mov = Movimiento::find($value->movimiento_id);    
                        $paciente = $mov->persona->apellidopaterno.' '.$mov->persona->apellidomaterno.' '.$mov->persona->nombres;
                        $dni=$mov->persona->dni;
                        $Historia = Historia::where('person_id','=',$mov->persona_id)->first();
                        $historia = !is_null($Historia)?$Historia->numero:'SN';
                        $convenio = $mov->plan->nombre;
                    }else{
                        $mov = Movimiento::find($value->movimiento_id2);
                        $paciente = $mov->persona->apellidopaterno.' '.$mov->persona->apellidomaterno.' '.$mov->persona->nombres;
                        $dni=$mov->persona->dni;
                        $Historia = Historia::where('person_id','=',$mov->persona_id)->first();
                        $historia = !is_null($Historia)?$Historia->numero:'SN';
                        $convenio = $mov->plan->nombre;
                    }
                }
                $columna28=substr(trim($paciente),0,100);
                $columna29='9671';
                $columna30='HISTORIA CLINICA: '.$historia;
                $columna31='9672';
                $columna32='DNI: '.$dni;
                $columna33='9670';
                $columna34=substr("CONVENIO: ".$convenio,0,100);
                $horaemision = date("H:i:s");
                $codigoUbicacion = "0000";
                    
                /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER (
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
                    serieNumeroAfectado, 
                    codigoAuxiliar100_1,
                    textoAuxiliar100_1,
                    codigoAuxiliar100_2,
                    textoAuxiliar100_2,
                    codigoAuxiliar100_3,
                    textoAuxiliar100_3,
                    codigoAuxiliar100_4,
                    textoAuxiliar100_4
                    ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22, $columna23, $columna24, $columna25, $columna26, $columna24, $columna27, $columna28, $columna29, $columna30, $columna31, $columna32, $columna33, $columna34]);*/

                DB::connection('sqlsrvtst21')->insert("INSERT INTO SPE_EINVOICEHEADER(
                    correoEmisor,
                    correoAdquiriente,
                    numeroDocumentoEmisor,
                    tipoDocumentoEmisor,
                    tipoDocumento,
                    razonSocialEmisor,
                    nombreComercialEmisor,
                    serieNumero,
                    fechaEmision,
                    ubigeoEmisor,
                    direccionEmisor,
                    urbanizacion,
                    provinciaEmisor,
                    departamentoEmisor,
                    distritoEmisor,
                    paisEmisor,
                    numeroDocumentoAdquiriente,
                    tipoDocumentoAdquiriente,
                    razonSocialAdquiriente,
                    tipoMoneda,
                    totalValorVentaNetoOpGravadas,
                    totalValorVentaNetoOpNoGravada,
                    totalValorVentaNetoOpExonerada,
                    totalIgv,
                    totalImpuestos,
                    totalVenta,
                    codigoLeyenda_1,
                    textoLeyenda_1,
                    codigoAuxiliar40_1,
                    textoAuxiliar40_1,
                    horaEmision,
                    codigoLocalAnexoEmisor,
                    tipoDocumentoReferenciaPrincip,
                    numeroDocumentoReferenciaPrinc,
                    motivoDocumento,
                    codigoSerieNumeroAfectado,
                    codigoAuxiliar100_1,
                    textoAuxiliar100_1,
                    codigoAuxiliar100_2,
                    textoAuxiliar100_2,
                    codigoAuxiliar100_3,
                    textoAuxiliar100_3,
                    codigoAuxiliar100_4,
                    textoAuxiliar100_4) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    [$columna7, '-', $columna2, $columna1, $columna4, $columna3, '-',$columna5, $columna6,"140106","Avenida Grau 1461",'-','LAMBAYEQUE','CHICLAYO','La Victoria','PE', $columna9, $columna8, $columna10,$columna12,$columna13, $columna14, $columna15, $columna17, $columna17, $columna20, $columna21, $columna22,'9011','18%',$horaemision,$codigoUbicacion, $columna23, $columna24, $columna25, $columna26, $columna27, $columna28, $columna29, $columna30, $columna31, $columna32, $columna33, $columna34]);

                if($abreviatura=="BC"){
                    /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        serieNumero,
                        tipoDocumento,
                        clave,
                        valor) 
                        values (?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna5, $columna4, 'lugarDestino', $columna101]);*/
                }else{
                    /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                        tipoDocumentoEmisor,
                        numeroDocumentoEmisor,
                        serieNumero,
                        tipoDocumento,
                        clave,
                        valor) 
                        values (?, ?, ?, ?, ?, ?)',
                        [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);*/

                    DB::connection('sqlsrvtst21')->insert('insert into SPE_EINVOICEHEADER_ADD(
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
                if($value->ventafarmacia2=='S'){
                    error_log("DETALLE FARMACIA");
                    //Array Insert Detalle Facturacion
                    $resultado1 = Detallemovimiento::where('detallemovimiento.movimiento_id','=',$value->id)
                                 ->select('Detallemovimiento.*');
                    $lista      = $resultado1->get();
                    $c=0;
                    foreach ($lista as $key1 => $value1) {
                        $columnad1=$c+1;
                        $columnad2=$value1->producto_id;
                        $columnad3=$value1->producto->nombre;    
                        $columnad4=$value1->cantidad;
                        $columnad5="NIU";
                        //$columnad6=number_format($value1->precio/1.18,2,".","");
                        if($value->igv>0){
                            $columnad6=number_format($value1->precio/1.18,2,".","");
                        }else{
                            $columnad6=number_format($value1->precio,2,".","");
                        }
                        $columnad7=$value1->precio;
                        $columnad8="01";
                        $columnad9=number_format($columnad4*$columnad6,2,".","");
                        //$columnad10="10";
                        //$columnad11=number_format($columnad9*0.18,2,".","");
                        $columnad12='0.00';
                        $columnad13='0.00';

                        $tasaigv = "18";
                        $montobaseigv = $columnad9;
                        if($value->igv>0){
                            $columnad10="10";
                            $columnad11=number_format($columnad9*0.18,2,".","");
                        }else{
                            $columnad10="30";
                            $columnad11='0.00';
                            $tasaigv = "0.00";
                            $montobaseigv = "0.00";
                        }
                        
                        /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
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
                        [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);*/

                        DB::connection('sqlsrvtst21')->insert('insert into SPE_EINVOICEDETAIL(
                        numeroDocumentoEmisor,
                        tipoDocumentoEmisor,
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
                        importeIgv,
                        montoBaseIgv,
                        tasaIgv,
                        importeTotalImpuestos
                        )
                        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                        [$columna2, $columna1, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11, $montobaseigv,$tasaigv, $columnad11]);

                        $c=$c+1;
                    }
                }else{
                    error_log("DETALLE NO FARMACIA");
                    $resultado1 = Detallemovcaja::join('movimiento as m','m.id','=','detallemovcaja.movimiento_id')
                                 ->where('m.id','=',$value->id)
                                 ->select('detallemovcaja.*');
                    $lista      = $resultado1->get();
                    $c=0;
                    foreach ($lista as $key1 => $value1) {
                        $columnad1=$c+1;
                        $servicio = Servicio::find($value1->servicio_id);
                        if(!is_null($servicio) && $servicio->tipopago=="Convenio"){
                            $columnad2=$servicio->tarifario->codigo;
                            $columnad3=$servicio->tarifario->nombre;    
                        }else{
                            $columnad2="-";
                            if($value1->servicio_id>0 && !is_null($servicio)){
                                $columnad3=$servicio->nombre;
                            }else{
                                $columnad3=trim($value1->descripcion);
                            }
                        }
                        $columnad4=$value1->cantidad;
                        $columnad5="ZZ";
                        if($value->tipodocumento2_id==17){
                            $columnad7=$value1->precio;
                            if($value->igv>0){
                                $columnad6=number_format($value1->precio/1.18,2,".","");
                            }else{
                                $columnad6=number_format($value1->precio,2,".","");
                            }
                        }else{
                            $columnad7=$value1->pagohospital;
                            if($value->igv>0){
                                $columnad6=number_format($value1->pagohospital/1.18,2,".","");
                            }else{
                                $columnad6=number_format($value1->pagohospital,2,".","");
                            }
                        }
                        $columnad8="01";
                        $columnad9=number_format($columnad4*$columnad6,2,".","");
                        $columnad10="10";
                        $columnad11=number_format($columnad9*0.18,2,".","");
                        $columnad12='0.00';
                        $columnad13='0.00';
                        
                        $tasaigv = "18";
                        $montobaseigv = $columnad9;
                        if($value->igv>0){
                            $columnad10="10";
                            $columnad11=number_format($columnad9*0.18,2,".","");
                        }else{
                            $columnad10="30";
                            $columnad11='0.00';
                            $tasaigv = "0.00";
                            $montobaseigv = "0.00";
                        }
                        /*DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
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
                        [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);*/

                        DB::connection('sqlsrvtst21')->insert('insert into SPE_EINVOICEDETAIL(
                        numeroDocumentoEmisor,
                        tipoDocumentoEmisor,
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
                        importeIgv,
                        montoBaseIgv,
                        tasaIgv,
                        importeTotalImpuestos
                        )
                        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                        [$columna2, $columna1, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11, $montobaseigv,$tasaigv, $columnad11]);

                        $c=$c+1;
                    }
                }
                DB::connection('sqlsrvtst21')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?', ['A',$columna5]); 
            }
        } 

        return "OK";  
    }

    public function excelFarmacia(Request $request){
        setlocale(LC_TIME, 'spanish');
        $fechainicio             = Libreria::getParam($request->input('fechainicio'));
        $fechafin             = Libreria::getParam($request->input('fechafin'));
        $numero             = Libreria::getParam($request->input('numero'));
        $paciente             = Libreria::getParam($request->input('paciente'));
        $resultado        = Venta::leftjoin('person','person.id','=','movimiento.persona_id')
                                ->where('tipomovimiento_id', '=', '4')
                                ->where('ventafarmacia','=','S')//where('serie','=','4')->
                                ->where(function($query) use ($numero){   
                                if (!is_null($numero) && $numero !== '') {
                                   
                                    $query->where('numero', '=', $numero);
                                }
                            })->where(function($query) use ($fechainicio,$fechafin){   
                                if (!is_null($fechainicio) && $fechainicio !== '') {
                                    $query->where('fecha', '>=', $fechainicio);
                                }
                                if (!is_null($fechafin) && $fechafin !== '') {
                                    $query->where('fecha', '<=', $fechafin);
                                }
                            });
        if($paciente!=""){
            $resultado = $resultado->where(DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }
        $resultado = $resultado->orderBy('movimiento.id','DESC')->select('movimiento.*');
        $lista            = $resultado->get();

        Excel::create('ExcelFarmacia', function($excel) use($lista,$request) {
 
            $excel->sheet('Farmacia', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Numero";
                $cabecera[] = "Paciente";
                $cabecera[] = "Total";
                $cabecera[] = "Descargado";
                $cabecera[] = "Observacion";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $nombrepaciente = '';
                    if ($value->persona_id !== NULL) {
                        $nombrepaciente = trim($value->person->bussinesname." ".$value->person->apellidopaterno." ".$value->person->apellidomaterno." ".$value->person->nombres);

                    }else{
                        $nombrepaciente = trim($value->nombrepaciente);
                    }
                    if($value->tipodocumento_id=="4"){
                        $abreviatura="F";
                    }elseif($value->tipodocumento_id=="5"){
                        $abreviatura="B";    
                    }else{
                        $abreviatura="G"; 
                    }
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT));
                    $detalle[] = $nombrepaciente;
                    $detalle[] = $value->total;
                    $detalle[] = $value->tipo;
                    $detalle[] = $value->listapago;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
            });
        })->export('xls');
    }

    public function excelFarmacia1(Request $request){
        setlocale(LC_TIME, 'spanish');
        $id               = Libreria::getParam($request->input('venta_id'),'');
        $guia = $request->input('guia');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->where('movimiento.id', '=', $id);
        $resultado        = $resultado->select('movimiento.*','tipodocumento.nombre as tipodocumento');
        $lista            = $resultado->get();
        //print_r(count($lista));
        if (count($lista) > 0) {     
            foreach($lista as $key => $value){
                if ( ($value->tipodocumento_id == 5) || ($value->tipodocumento_id == 4)) {
                    if ($guia == 'SI') {
                        Excel::create('ExcelGuia', function($excel) use($value,$request,$id) {
                 
                            $excel->sheet('Guia', function($sheet) use($value,$request,$id) {
                                $cabecera[] = "GUIA INTERNA DE SALIDA DE MEDICAMENTOS";
                                $sheet->row(1,$cabecera);
                                $sheet->mergeCells('A1:G1');
                                $detalle = array();
                                $abreviatura="B";
                                $detalle[]=utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT));
                                $detalle[]="";
                                $detalle[]='Usuario: '.$value->responsable->nombres;
                                $detalle[]="";
                                $detalle[]='Convenio: '.$value->conveniofarmacia->nombre;
                                $detalle[]="";
                                $detalle[]="";
                                $sheet->row(2,$detalle);
                                $sheet->mergeCells('A2:B2');
                                $sheet->mergeCells('C2:D2');
                                $sheet->mergeCells('E2:G2');
                                $detalle = array();
                                if ($value->persona_id !== NULL) {
                                    $detalle[]=(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres));
                                    $detalle[]="";
                                    $detalle[]="";
                                    $detalle[]=($value->fecha)." ".(substr($value->created_at, 11));
                                    $detalle[]="";
                                }else{
                                    $detalle[]=(trim("Paciente: ".$value->nombrepaciente));
                                    $detalle[]="";
                                    $detalle[]="";
                                    $detalle[]=($value->fecha)." ".(substr($value->created_at, 11));
                                    $detalle[]="";
                                }
                                $sheet->row(3,$detalle);
                                $sheet->mergeCells('A3:C3');
                                $sheet->mergeCells('D3:E3');
                                $detalle = array();
                                $detalle[]="Cantidad";
                                $detalle[]="Producto";
                                $detalle[]="Prec. Unit.";
                                $detalle[]="Dscto";
                                $detalle[]="Copago";
                                $detalle[]="Total";
                                $detalle[]="Sin IGV";
                                $sheet->row(4,$detalle);
                                $detalle = array();
                                $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                                $lista2            = $resultado->get();
                                $totalpago=0;
                                $totaldescuento=0;
                                $totaligv=0;$c=5;
                                foreach($lista2 as $key2 => $v){
                                    $valaux = round(($v->precio*$v->cantidad), 2);
                                    $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                                    $dscto = round(($precioaux*$v->cantidad),2);
                                    $totalpago = $totalpago+$dscto;
                                    $subtotal = round(($dscto*($value->copago/100)),2);
                                    $subigv = round(($subtotal/1.18),2);
                                    $totaldescuento = $totaldescuento+$subtotal;
                                    $totaligv = $totaligv+$subigv;
                                    $detalle = array();
                                    $detalle[]=number_format($v->cantidad,2,'.','');
                                    $detalle[]=utf8_encode($v->producto->nombre);
                                    $detalle[]=number_format($v->precio,2,'.','');
                                    $detalle[]=number_format($dscto,2,'.','');
                                    $detalle[]=number_format($value->copago,2,'.','');
                                    $detalle[]=number_format($subtotal,2,'.','');
                                    $detalle[]=number_format($subigv,2,'.','');
                                    $sheet->row($c,$detalle);
                                    $c=$c+1;
                                }
                                $detalle = array();
                                $detalle[]="";
                                $detalle[]="";
                                $detalle[]="";
                                $detalle[]=number_format($totalpago,2,'.','');
                                $detalle[]="";
                                $detalle[]=number_format($totaldescuento,2,'.','');
                                $detalle[]=number_format($totaligv,2,'.','');
                                $sheet->row($c,$detalle);
                                
                            });
                        })->export('xls');
                    }
                }elseif ($value->tipodocumento_id == 15) {
                    Excel::create('ExcelGuia', function($excel) use($value,$request,$id) {
                 
                        $excel->sheet('Guia', function($sheet) use($value,$request,$id) {
                            $cabecera[] = "GUIA INTERNA";
                            $sheet->row(1,$cabecera);
                            $sheet->mergeCells('A1:G1');
                            $detalle = array();
                            $abreviatura="G";
                            $detalle[]=utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT));
                            $detalle[]="";
                            $detalle[]='Usuario: '.$value->responsable->nombres;
                            $detalle[]="";
                            $detalle[]='Convenio: '.$value->conveniofarmacia->nombre;
                            $detalle[]="";
                            $detalle[]="";
                            $sheet->row(2,$detalle);
                            $sheet->mergeCells('A2:B2');
                            $sheet->mergeCells('C2:D2');
                            $sheet->mergeCells('E2:G2');
                            $detalle = array();
                            if ($value->persona_id !== NULL) {
                                $detalle[]=(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres));
                                $detalle[]="";
                                $detalle[]="";
                                $detalle[]=($value->fecha)." ".(substr($value->created_at, 11));
                                $detalle[]="";
                            }else{
                                $detalle[]=(trim("Paciente: ".$value->nombrepaciente));
                                $detalle[]="";
                                $detalle[]="";
                                $detalle[]=($value->fecha)." ".(substr($value->created_at, 11));
                                $detalle[]="";
                            }
                            $sheet->row(3,$detalle);
                            $sheet->mergeCells('A3:C3');
                            $sheet->mergeCells('D3:E3');
                            $detalle = array();
                            $detalle[]="Cantidad";
                            $detalle[]="Producto";
                            $detalle[]="Prec. Unit.";
                            $detalle[]="Dscto";
                            $detalle[]="Copago";
                            $detalle[]="Total";
                            $detalle[]="Sin IGV";
                            $sheet->row(4,$detalle);
                            $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                            $lista2            = $resultado->get();
                            $totalpago=0;
                            $totaldescuento=0;
                            $totaligv=0;$c=5;
                            foreach($lista2 as $key2 => $v){
                                $valaux = round(($v->precio*$v->cantidad), 2);
                                $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                                $dscto = round(($precioaux*$v->cantidad),2);
                                $totalpago = $totalpago+$dscto;
                                $subtotal = round(($dscto*($value->copago/100)),2);
                                $subigv = round(($subtotal/1.18),2);
                                $totaldescuento = $totaldescuento+$subtotal;
                                $totaligv = $totaligv+$subigv;
                                $detalle = array();
                                $detalle[]=number_format($v->cantidad,2,'.','');
                                $detalle[]=utf8_encode($v->producto->nombre);
                                $detalle[]=number_format($v->precio,2,'.','');
                                $detalle[]=number_format($dscto,2,'.','');
                                $detalle[]=number_format($value->copago,2,'.','');
                                $detalle[]=number_format($subtotal,2,'.','');
                                $detalle[]=number_format($subigv,2,'.','');
                                $sheet->row($c,$detalle);
                                $c=$c+1;
                            }
                            $detalle = array();
                            $detalle[]="";
                            $detalle[]="";
                            $detalle[]="";
                            $detalle[]=number_format($totalpago,2,'.','');
                            $detalle[]="";
                            $detalle[]=number_format($totaldescuento,2,'.','');
                            $detalle[]=number_format($totaligv,2,'.','');
                            $sheet->row($c,$detalle);
                        });
                     })->export('xls');
                }
                    
            }
        }
    }

    public function excelKardex(Request $request){
        setlocale(LC_TIME, 'spanish');
        $fechainicio    = Libreria::getParam($request->input('fechainicial'));
        $fechafin       = Libreria::getParam($request->input('fechafinal'));
        $resultado      = Venta::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                            ->join('producto','producto.id','=','detallemovimiento.producto_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->join('tipomovimiento','movimiento.tipomovimiento_id','=','tipomovimiento.id')
                            ->leftjoin('motivo','motivo.id','=','movimiento.motivo_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where(function($query) use ($fechainicio,$fechafin){   
                                if (!is_null($fechainicio) && $fechainicio !== '') {
                                    $query->where('movimiento.fecha', '>=', $fechainicio);
                                }
                                if (!is_null($fechafin) && $fechafin !== '') {
                                    $query->where('movimiento.fecha', '<=', $fechafin);
                                }
                            })
                            ->whereNotIn('movimiento.situacion',['A']);
        if($request->input('producto_id')!=""){
            $resultado = $resultado->where('detallemovimiento.producto_id', '=', $request->input('producto_id'));
        }
        $resultado = $resultado->orderBy('movimiento.fecha','asc')->orderBy('movimiento.id','asc')->select('movimiento.*',DB::raw('producto.nombre as producto'),DB::raw('CONCAT(case when m2.tipodocumento_id=4 or m2.tipodocumento_id=17 then "F" else "B" end,m2.serie,"-",m2.numero) as numeroref'),'tipodocumento.codigo','tipodocumento.stock','detallemovimiento.cantidad','detallemovimiento.precio','detallemovimiento.subtotal as subtotal2','movimiento.created_at','movimiento.motivo_id',DB::raw('motivo.codigo as motivo'),DB::raw('(select nombres from person where id=movimiento.responsable_id) as usuario'));
        $lista            = $resultado->get();

        Excel::create('ExcelKardex', function($excel) use($lista,$request) {
 
            $excel->sheet('Kardex', function($sheet) use($lista,$request) {
                $producto = Producto::find($request->producto_id);
                $celdas      = 'A12:E12';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $celdas      = 'F12:F13';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $celdas      = 'G12:I12';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $celdas      = 'J12:L12';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $celdas      = 'M12:O12';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });

                $sheet->cells('A2:A9', function($cells) {
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '12',
                        'bold'       =>  true
                        ));
                });
                $sheet->mergeCells("A2:O2");
                $sheet->mergeCells("A3:O3");
                $sheet->mergeCells("A4:O4");
                $sheet->mergeCells("A5:O5");
                $sheet->mergeCells("A6:O6");
                $sheet->mergeCells("A7:O7");
                $sheet->mergeCells("A8:O8");
                $sheet->mergeCells("A9:O9");
                $cabecera = array();
                $cabecera[] = 'FORMATO 13.1: "REGISTRO DE INVENTARIO PERMANENTE VALORIZADO - DETALLE DEL INVENTARIO VALORIZADO"';
                $sheet->row(2,$cabecera);

                $cabecera = array();
                $cabecera[] = 'PERIODO:';
                $sheet->row(3,$cabecera);

                $cabecera = array();
                $cabecera[] = 'RUC:20480082673';
                $sheet->row(4,$cabecera);


                $cabecera = array();
                $cabecera[] = 'APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL: HOSPITAL PRIVADO JUAN PABLO II';
                $sheet->row(5,$cabecera);

                $cabecera = array();
                $cabecera[] = 'CÓDIGO DE LA EXISTENCIA: '.$producto->nombre;
                $sheet->row(6,$cabecera);

                $cabecera = array();
                $cabecera[] = 'TIPO (TABLA 5): 01';
                $sheet->row(7,$cabecera);

                $cabecera = array();
                $cabecera[] = 'CODIGO DE LA UNIDAD DE MEDIDA (TABLA 5): 07';
                $sheet->row(8,$cabecera);

                $cabecera = array();
                $cabecera[] = 'MÉTODO DE VALUACIÓN: PEPS (PRIMERAS ENTRADAS PRIMERAS SALIDAS)';
                $sheet->row(9,$cabecera);

                $cabecera = array();
                $cabecera[] = "DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DCTO INTERNO O SIMILAR";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "TIPO DE OPERACIÓN (TABLA 12)";
                $cabecera[] = "ENTRADAS";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "SALIDAS";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "SALDO FINAL";
                $cabecera[] = "";
                $sheet->row(12,$cabecera);

                formatoCelda($sheet,'A13',true);
                formatoCelda($sheet,'B13',true);
                formatoCelda($sheet,'C13',true);
                formatoCelda($sheet,'D13',true);
                formatoCelda($sheet,'E13',true);
                formatoCelda($sheet,'F13',true);
                formatoCelda($sheet,'G13',true);
                formatoCelda($sheet,'H13',true);
                formatoCelda($sheet,'I13',true);
                formatoCelda($sheet,'J13',true);
                formatoCelda($sheet,'K13',true);
                formatoCelda($sheet,'L13',true);
                formatoCelda($sheet,'M13',true);
                formatoCelda($sheet,'N13',true);
                formatoCelda($sheet,'O13',true);
                formatoCelda($sheet,'P13',true);
                formatoCelda($sheet,'Q13',true);
                $cabecera = array();
                $cabecera[] = "FECHA";
                $cabecera[] = "TIPO (TABLA 10)";
                $cabecera[] = "PROVEEDOR CLIENTE";
                $cabecera[] = "SERIE";
                $cabecera[] = "NÚMERO";
                $cabecera[] = "";
                $cabecera[] = "CANTIDAD";
                $cabecera[] = "COSTO UNITARIO";
                $cabecera[] = "COSTO TOTAL";
                $cabecera[] = "CANTIDAD";
                $cabecera[] = "VALOR UNITARIO";
                $cabecera[] = "VALOR TOTAL";
                $cabecera[] = "CANTIDAD";
                $cabecera[] = "COSTO UNITARIO";
                $cabecera[] = "COSTO TOTAL";
                $cabecera[] = "HORA REGISTRO";
                $cabecera[] = "USUARIO";
                $sheet->row(13,$cabecera);
                $c=14;$d=3;$band=true;
                /*$venta = Venta::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->where('movimiento.tipomovimiento_id','=','4')
                                ->where('movimiento.fecha','<',$request->input('fechainicial'))
                                ->whereNotIn('movimiento.situacion',['I'])
                                ->where('detallemovimiento.producto_id','=',$request->input('producto_id'))
                                ->select(DB::raw('sum(detallemovimiento.cantidad) as venta'))
                                ->first()->venta;

                $compra = Venta::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->where('tipomovimiento_id','=','3')
                                ->where('detallemovimiento.producto_id','=',$request->input('producto_id'))
                                ->where('movimiento.fecha','<',$request->input('fechainicial'))
                                ->select(DB::raw('sum(detallemovimiento.cantidad) as compra'))
                                ->first()->compra;

                $almacen = Venta::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->where('tipomovimiento_id','=','5')
                                ->where('detallemovimiento.producto_id','=',$request->input('producto_id'))
                                ->where('movimiento.fecha','>=','2018-01-02')
                                ->where('movimiento.fecha','<',$request->input('fechainicial'))
                                ->select(DB::raw('sum(case when movimiento.tipodocumento_id = 8 then detallemovimiento.cantidad else detallemovimiento.cantidad*(-1) end) as almacen'))
                                ->first()->almacen;*/

                $precio = Venta::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->where('tipomovimiento_id','=','3')
                                ->where('detallemovimiento.producto_id','=',$request->input('producto_id'))
                                ->where('movimiento.fecha','<',$request->input('fechainicial'))
                                ->select(DB::raw('detallemovimiento.precio as compra'))
                                ->orderBy('movimiento.fecha','desc')
                                ->first();
                if(!is_null($precio)){
                    $precio = $precio->compra;
                }else{
                    $precio = 0;
                }

                $final = array();

                $inicial = Kardex::join('lote','lote.id','=','kardex.lote_id')
                                ->where('producto_id','=',$request->input('producto_id'))
                                //->where('kardex.fecha','<=',date('Y',strtotime($request->input('fechainicial'))).'-12-31')
                                ->where('kardex.fecha','<',$request->input('fechainicial'))
                                ->select('kardex.stockactual')
                                ->orderBy('kardex.fecha','desc')
                                ->orderBy('kardex.id','desc')
                                ->first();
                if(!is_null($inicial)){
                    $inicio = $inicial->stockactual;
                }else{
                    $inicio = 0;
                }
                //$final[]=array("precio"=>$precio,"cantidad"=>($almacen+$compra-$venta));
                $final[]=array("precio"=>$precio,"cantidad"=>($inicio));
                formatoCelda($sheet,'A'.$c,false);
                formatoCelda($sheet,'B'.$c,false);
                formatoCelda($sheet,'C'.$c,false);
                formatoCelda($sheet,'D'.$c,false);
                formatoCelda($sheet,'E'.$c,false);
                formatoCelda($sheet,'F'.$c,false);
                formatoCelda($sheet,'G'.$c,false);
                formatoCelda($sheet,'H'.$c,false);
                formatoCelda($sheet,'I'.$c,false);
                formatoCelda($sheet,'J'.$c,false);
                formatoCelda($sheet,'K'.$c,false);
                formatoCelda($sheet,'L'.$c,false);
                formatoCelda($sheet,'M'.$c,false);
                formatoCelda($sheet,'N'.$c,false);
                formatoCelda($sheet,'O'.$c,false);
                formatoCelda($sheet,'P'.$c,false);
                formatoCelda($sheet,'Q'.$c,false);
                $detalle = array();
                $detalle[] = date('d/m/Y',strtotime($request->input('fechainicial')));
                $detalle[] = "";
                $detalle[] = "SALDO INICIAL";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "16";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";                        
                $detalle[] = $final[0]["cantidad"];
                $detalle[] = $final[0]["precio"];
                $detalle[] = round($final[0]["cantidad"]*$final[0]["precio"],2);
                $invinicial = round($final[0]["cantidad"]*$final[0]["precio"],2);
                $sheet->row($c,$detalle);
                $c=$c+1;
                $compras=0;$invfinal=0;$cantcompras=0;$ventas=0;$cantventas=0;
                foreach ($lista as $key => $value){
                    formatoCelda($sheet,'A'.$c,false);
                    formatoCelda($sheet,'B'.$c,false);
                    formatoCelda($sheet,'C'.$c,false);
                    formatoCelda($sheet,'D'.$c,false);
                    formatoCelda($sheet,'E'.$c,false);
                    formatoCelda($sheet,'F'.$c,false);
                    formatoCelda($sheet,'G'.$c,false);
                    formatoCelda($sheet,'H'.$c,false);
                    formatoCelda($sheet,'I'.$c,false);
                    formatoCelda($sheet,'J'.$c,false);
                    formatoCelda($sheet,'K'.$c,false);
                    formatoCelda($sheet,'L'.$c,false);
                    formatoCelda($sheet,'M'.$c,false);
                    formatoCelda($sheet,'N'.$c,false);
                    formatoCelda($sheet,'O'.$c,false);
                    formatoCelda($sheet,'P'.$c,false);
                    formatoCelda($sheet,'Q'.$c,false);
                    $detalle = array();
                    $nombrepaciente = '';
                    if ($value->persona_id !== NULL) {
                        $nombrepaciente = trim($value->person->bussinesname." ".$value->person->apellidopaterno." ".$value->person->apellidomaterno." ".$value->person->nombres);
                    }else{
                        $nombrepaciente = trim($value->nombrepaciente);
                    }
                    if($value->tipomovimiento_id=="5"){
                        $nombrepaciente = "ALMACEN";
                    }
                    if($value->tipodocumento_id=="4"){
                        $abreviatura="F";
                    }elseif($value->tipodocumento_id=="5"){
                        $abreviatura="B";    
                    }else{
                        $abreviatura="G"; 
                    }
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->codigo;
                    $detalle[] = $nombrepaciente;
                    if($value->tipomovimiento_id==4){//VENTA
                        $detalle[] = utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT));
                        $detalle[] = str_pad($value->numero,8,'0',STR_PAD_LEFT);
                        $detalle[] = "01";
                        $ventas = $ventas + abs($final[0]["precio"]*$value->cantidad);
                        $cantventas = $cantventas + abs($value->cantidad);
                    }elseif($value->tipomovimiento_id==6){//NOTA DE CREDITO
                        $detalle[] = utf8_encode(substr($value->numeroref,0,1).'C'.str_pad($value->serie,2,'0',STR_PAD_LEFT));
                        $detalle[] = str_pad($value->numero,8,'0',STR_PAD_LEFT);
                        $detalle[] = "05";
                        $ventas = $ventas - abs($final[0]["precio"]*$value->cantidad);
                        $cantventas = $cantventas - abs($value->cantidad);
                    }elseif($value->tipomovimiento_id==3){//COMPRA
                        $detalle[] = utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT));
                        $detalle[] = str_pad($value->numero,8,'0',STR_PAD_LEFT);
                        if($value->tipoDocumento_id==11){//NOTA DE CREDITO
                            $detalle[] = "06";
                            $compras = $compras - abs($value->subtotal2);
                            $cantcompras = $cantcompras - abs($value->cantidad);
                        }else{
                            $detalle[] = "02";
                            $compras = $compras + abs($value->subtotal2);
                            $cantcompras = $cantcompras + abs($value->cantidad);
                        }
                    }elseif($value->tipomovimiento_id==5){//ALMACEN
                        $detalle[] = utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT));
                        $detalle[] = str_pad($value->numero,8,'0',STR_PAD_LEFT);
                        if($value->motivo_id>0){
                            $detalle[] = $value->motivo;
                        }else{
                            $detalle[] = "99";
                        }
                        if($value->stock=="S"){
                            $compras = $compras + abs($value->subtotal2);
                            $cantcompras = $cantcompras + abs($value->cantidad);
                        }else{
                            $compras = $compras - abs($value->subtotal2);
                            $cantcompras = $cantcompras - abs($value->cantidad);
                        }                        
                    }

                    if($value->stock=="S"){
                        $detalle[] = abs($value->cantidad);
                        $detalle[] = $value->precio;
                        $detalle[] = abs($value->subtotal2);
                        $detalle[] = "";   
                        $detalle[] = "";   
                        $detalle[] = "";
                        $band=false;$indice=0;
                        for($x=0;$x<count($final);$x++){
                            if($final[$x]["precio"]==$value->precio){
                                $band=true;
                                $indice=$x;
                                $x=999999;
                            }
                        }
                        if($band){
                            $final[$indice]["cantidad"]=$final[$indice]["cantidad"]+$value->cantidad;
                        }else{
                            $final[]=array("precio"=>$value->precio,"cantidad"=>$value->cantidad);
                        }
                    }else{
                        $detalle[] = "";
                        $detalle[] = "";   
                        $detalle[] = "";
                        $detalle[] = abs($value->cantidad);
                        //$detalle[] = $value->precio;
                        //$detalle[] = abs($value->subtotal2);
                        $detalle[] = $final[0]["precio"];
                        $detalle[] = abs($final[0]["precio"]*$value->cantidad);
                        $lista2=array();
                        $cantidad=$value->cantidad;
                        for($x=0;$x<count($final);$x++){
                            if(($final[$x]["cantidad"]+0)>=$cantidad){
                                $final[$x]["cantidad"]=$final[$x]["cantidad"] - $cantidad;
                                $lista2[]=$final[$x];
                                $cantidad=0;
                            }else{
                                $cantidad = $cantidad - $final[$x]["cantidad"]; 
                            }
                        }
                        $final=$lista2;
                    }
                    for($x=0;$x<count($final);$x++){
                        if($x==0){
                            $detalle[] = $final[$x]["cantidad"];
                            $detalle[] = $final[$x]["precio"];
                            $detalle[] = round($final[$x]["cantidad"]*$final[$x]["precio"],2);
                            $detalle[] = date("H:i:s",strtotime($value->created_at));
                            $detalle[] = $value->usuario;
                            $sheet->row($c,$detalle);
                            $invfinal = round($final[$x]["cantidad"]*$final[$x]["precio"],2);
                            $c=$c+1;
                        }else{
                            formatoCelda($sheet,'A'.$c,false);
                            formatoCelda($sheet,'B'.$c,false);
                            formatoCelda($sheet,'C'.$c,false);
                            formatoCelda($sheet,'D'.$c,false);
                            formatoCelda($sheet,'E'.$c,false);
                            formatoCelda($sheet,'F'.$c,false);
                            formatoCelda($sheet,'G'.$c,false);
                            formatoCelda($sheet,'H'.$c,false);
                            formatoCelda($sheet,'I'.$c,false);
                            formatoCelda($sheet,'J'.$c,false);
                            formatoCelda($sheet,'K'.$c,false);
                            formatoCelda($sheet,'L'.$c,false);
                            formatoCelda($sheet,'M'.$c,false);
                            formatoCelda($sheet,'N'.$c,false);
                            formatoCelda($sheet,'O'.$c,false);
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
                            $detalle[] = "";                        
                            $detalle[] = $final[$x]["cantidad"];
                            $detalle[] = $final[$x]["precio"];
                            $detalle[] = round($final[$x]["cantidad"]*$final[$x]["precio"],2);
                            $sheet->row($c,$detalle);
                            $c=$c+1;
                            $invfinal = $invfinal + round($final[$x]["cantidad"]*$final[$x]["precio"],2);
                        }
                    }
                }
                formatoCelda($sheet,'G'.$c,false);
                formatoCelda($sheet,'I'.$c,false);
                formatoCelda($sheet,'J'.$c,false);
                formatoCelda($sheet,'L'.$c,false);
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = $cantcompras;
                $detalle[] = "";
                $detalle[] = round($compras,2);
                $detalle[] = $cantventas;
                $detalle[] = "";
                $detalle[] = round($ventas,2);                        
                $sheet->row($c,$detalle);
                
                $c=$c+5;
                formatoCelda($sheet,'K'.$c,false);
                $sheet->mergeCells("I$c:J$c");
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "INV. INICIAL";
                $detalle[] = "";
                $detalle[] = round($invinicial,2);
                $sheet->row($c,$detalle);
                $c=$c+1;
                
                formatoCelda($sheet,'K'.$c,false);
                $sheet->mergeCells("I$c:J$c");
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "(+)COMPRAS";
                $detalle[] = "";
                $detalle[] = round($compras,2);
                $sheet->row($c,$detalle);
                $c=$c+1;

                formatoCelda($sheet,'K'.$c,false);
                $sheet->mergeCells("I$c:J$c");
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "(-)INV. FINAL";
                $detalle[] = "";
                $detalle[] = '-'.round($invfinal,2);
                $sheet->row($c,$detalle);
                $c=$c+1;

                formatoCelda($sheet,'K'.$c,false);
                $sheet->mergeCells("I$c:J$c");
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "(=)COSTO DE VENTAS";
                $detalle[] = "";
                $detalle[] = round($invinicial + $compras - $invfinal,2);
                $sheet->row($c,$detalle);
                $c=$c+1;

            });
        })->export('xls');
    }

    public function excelKardexTodos(Request $request){
        setlocale(LC_TIME, 'spanish');

        Excel::create('ExcelKardex', function($excel) use($request) {
 
            $excel->sheet('Kardex', function($sheet) use($request) {
                $producto = Producto::whereNull('deleted_at')->limit(200000)->get();
                $compras=0;$invfinal=0;$cantcompras=0;$ventas=0;$cantventas=0;$invinicial=0;$invfinal1=0;
                foreach ($producto as $key1 => $value1) {
                    $producto_id = $value1->id;
                
                    $fechainicio    = Libreria::getParam($request->input('fechainicial'));
                    $fechafin       = Libreria::getParam($request->input('fechafinal'));
                    $resultado      = Venta::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                        ->join('producto','producto.id','=','detallemovimiento.producto_id')
                                        ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                                        ->join('tipomovimiento','movimiento.tipomovimiento_id','=','tipomovimiento.id')
                                        ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                                        ->leftjoin('motivo','motivo.id','=','movimiento.motivo_id')
                                        ->where(function($query) use ($fechainicio,$fechafin){   
                                            if (!is_null($fechainicio) && $fechainicio !== '') {
                                                $query->where('movimiento.fecha', '>=', $fechainicio);
                                            }
                                            if (!is_null($fechafin) && $fechafin !== '') {
                                                $query->where('movimiento.fecha', '<=', $fechafin);
                                            }
                                        })
                                        ->whereNotIn('movimiento.situacion',['A']);
                    $resultado = $resultado->where('detallemovimiento.producto_id', '=', $producto_id);
                    $resultado = $resultado->orderBy('movimiento.fecha','asc')->orderBy('movimiento.id','asc')->select('movimiento.*',DB::raw('producto.nombre as producto'),DB::raw('CONCAT(case when m2.tipodocumento_id=4 or m2.tipodocumento_id=17 then "F" else "B" end,m2.serie,"-",m2.numero) as numeroref'),'tipodocumento.codigo','tipodocumento.stock','detallemovimiento.cantidad','detallemovimiento.precio','detallemovimiento.subtotal as subtotal2','movimiento.created_at',DB::raw('(select nombres from person where id=movimiento.responsable_id) as usuario'),'movimiento.motivo_id',DB::raw('motivo.codigo as motivo'));
                    $lista            = $resultado->get();
                    /*$producto = Producto::find($request->producto_id);
                    $celdas      = 'A12:E12';
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setAlignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });
                    $celdas      = 'F12:F13';
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setAlignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });
                    $celdas      = 'G12:I12';
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setAlignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });
                    $celdas      = 'J12:L12';
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setAlignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });
                    $celdas      = 'M12:O12';
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setAlignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });

                    $sheet->cells('A2:A9', function($cells) {
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                            ));
                    });
                    $sheet->mergeCells("A2:O2");
                    $sheet->mergeCells("A3:O3");
                    $sheet->mergeCells("A4:O4");
                    $sheet->mergeCells("A5:O5");
                    $sheet->mergeCells("A6:O6");
                    $sheet->mergeCells("A7:O7");
                    $sheet->mergeCells("A8:O8");
                    $sheet->mergeCells("A9:O9");
                    $cabecera = array();
                    $cabecera[] = 'FORMATO 13.1: "REGISTRO DE INVENTARIO PERMANENTE VALORIZADO - DETALLE DEL INVENTARIO VALORIZADO"';
                    $sheet->row(2,$cabecera);

                    $cabecera = array();
                    $cabecera[] = 'PERIODO:';
                    $sheet->row(3,$cabecera);

                    $cabecera = array();
                    $cabecera[] = 'RUC:20480082673';
                    $sheet->row(4,$cabecera);


                    $cabecera = array();
                    $cabecera[] = 'APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL: HOSPITAL PRIVADO JUAN PABLO II';
                    $sheet->row(5,$cabecera);

                    $cabecera = array();
                    $cabecera[] = 'CÓDIGO DE LA EXISTENCIA: '.$producto->nombre;
                    $sheet->row(6,$cabecera);

                    $cabecera = array();
                    $cabecera[] = 'TIPO (TABLA 5): 01';
                    $sheet->row(7,$cabecera);

                    $cabecera = array();
                    $cabecera[] = 'CODIGO DE LA UNIDAD DE MEDIDA (TABLA 5): 07';
                    $sheet->row(8,$cabecera);

                    $cabecera = array();
                    $cabecera[] = 'MÉTODO DE VALUACIÓN: PEPS (PRIMERAS ENTRADAS PRIMERAS SALIDAS)';
                    $sheet->row(9,$cabecera);

                    $cabecera = array();
                    $cabecera[] = "DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DCTO INTERNO O SIMILAR";
                    $cabecera[] = "";
                    $cabecera[] = "";
                    $cabecera[] = "";
                    $cabecera[] = "";
                    $cabecera[] = "TIPO DE OPERACIÓN (TABLA 12)";
                    $cabecera[] = "ENTRADAS";
                    $cabecera[] = "";
                    $cabecera[] = "";
                    $cabecera[] = "SALIDAS";
                    $cabecera[] = "";
                    $cabecera[] = "";
                    $cabecera[] = "SALDO FINAL";
                    $cabecera[] = "";
                    $sheet->row(12,$cabecera);
                    
                    formatoCelda($sheet,'A13',true);
                    formatoCelda($sheet,'B13',true);
                    formatoCelda($sheet,'C13',true);
                    formatoCelda($sheet,'D13',true);
                    formatoCelda($sheet,'E13',true);
                    formatoCelda($sheet,'F13',true);
                    formatoCelda($sheet,'G13',true);
                    formatoCelda($sheet,'H13',true);
                    formatoCelda($sheet,'I13',true);
                    formatoCelda($sheet,'J13',true);
                    formatoCelda($sheet,'K13',true);
                    formatoCelda($sheet,'L13',true);
                    formatoCelda($sheet,'M13',true);
                    formatoCelda($sheet,'N13',true);
                    formatoCelda($sheet,'O13',true);
                    formatoCelda($sheet,'P13',true);
                    formatoCelda($sheet,'Q13',true);
                    $cabecera = array();
                    $cabecera[] = "FECHA";
                    $cabecera[] = "TIPO (TABLA 10)";
                    $cabecera[] = "PROVEEDOR CLIENTE";
                    $cabecera[] = "SERIE";
                    $cabecera[] = "NÚMERO";
                    $cabecera[] = "";
                    $cabecera[] = "CANTIDAD";
                    $cabecera[] = "COSTO UNITARIO";
                    $cabecera[] = "COSTO TOTAL";
                    $cabecera[] = "CANTIDAD";
                    $cabecera[] = "VALOR UNITARIO";
                    $cabecera[] = "VALOR TOTAL";
                    $cabecera[] = "CANTIDAD";
                    $cabecera[] = "COSTO UNITARIO";
                    $cabecera[] = "COSTO TOTAL";
                    $cabecera[] = "HORA REGISTRO";
                    $cabecera[] = "USUARIO";
                    $sheet->row(13,$cabecera);*/
                    $c=14;$d=3;$band=true;
                    /*$venta = Venta::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                    ->where('movimiento.tipomovimiento_id','=','4')
                                    ->where('movimiento.fecha','<',$request->input('fechainicial'))
                                    ->whereNotIn('movimiento.situacion',['I'])
                                    ->where('detallemovimiento.producto_id','=',$request->input('producto_id'))
                                    ->select(DB::raw('sum(detallemovimiento.cantidad) as venta'))
                                    ->first()->venta;

                    $compra = Venta::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                    ->where('tipomovimiento_id','=','3')
                                    ->where('detallemovimiento.producto_id','=',$request->input('producto_id'))
                                    ->where('movimiento.fecha','<',$request->input('fechainicial'))
                                    ->select(DB::raw('sum(detallemovimiento.cantidad) as compra'))
                                    ->first()->compra;

                    $almacen = Venta::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                    ->where('tipomovimiento_id','=','5')
                                    ->where('detallemovimiento.producto_id','=',$request->input('producto_id'))
                                    ->where('movimiento.fecha','>=','2018-01-02')
                                    ->where('movimiento.fecha','<',$request->input('fechainicial'))
                                    ->select(DB::raw('sum(case when movimiento.tipodocumento_id = 8 then detallemovimiento.cantidad else detallemovimiento.cantidad*(-1) end) as almacen'))
                                    ->first()->almacen;*/

                    $precio = Venta::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                    ->where('tipomovimiento_id','=','3')
                                    ->where('detallemovimiento.producto_id','=',$producto_id)
                                    ->where('movimiento.fecha','<',$request->input('fechainicial'))
                                    ->select(DB::raw('detallemovimiento.precio as compra'))
                                    ->orderBy('movimiento.fecha','desc')
                                    ->first();
                    if(!is_null($precio)){
                        $precio = $precio->compra;
                    }else{
                        $precio = 0;
                    }

                    $final = array();

                    $inicial = Kardex::join('lote','lote.id','=','kardex.lote_id')
                                    ->where('producto_id','=',$producto_id)
                                    //->where('kardex.fecha','<=',date('Y',strtotime($request->input('fechainicial'))).'-12-31')
                                    ->where('kardex.fecha','<',$request->input('fechainicial'))
                                    ->select('kardex.stockactual')
                                    ->orderBy('kardex.fecha','desc')
                                    ->orderBy('kardex.id','desc')
                                    ->first();
                    if(!is_null($inicial)){
                        $inicio = $inicial->stockactual;
                    }else{
                        $inicio = 0;
                    }
                    //$final[]=array("precio"=>$precio,"cantidad"=>($almacen+$compra-$venta));
                    $final[]=array("precio"=>$precio,"cantidad"=>($inicio));
                    /*formatoCelda($sheet,'A'.$c,false);
                    formatoCelda($sheet,'B'.$c,false);
                    formatoCelda($sheet,'C'.$c,false);
                    formatoCelda($sheet,'D'.$c,false);
                    formatoCelda($sheet,'E'.$c,false);
                    formatoCelda($sheet,'F'.$c,false);
                    formatoCelda($sheet,'G'.$c,false);
                    formatoCelda($sheet,'H'.$c,false);
                    formatoCelda($sheet,'I'.$c,false);
                    formatoCelda($sheet,'J'.$c,false);
                    formatoCelda($sheet,'K'.$c,false);
                    formatoCelda($sheet,'L'.$c,false);
                    formatoCelda($sheet,'M'.$c,false);
                    formatoCelda($sheet,'N'.$c,false);
                    formatoCelda($sheet,'O'.$c,false);
                    formatoCelda($sheet,'P'.$c,false);
                    formatoCelda($sheet,'Q'.$c,false);
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($request->input('fechainicial')));
                    $detalle[] = "";
                    $detalle[] = "SALDO INICIAL";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "16";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";                        
                    $detalle[] = $final[0]["cantidad"];
                    $detalle[] = $final[0]["precio"];
                    $detalle[] = round($final[0]["cantidad"]*$final[0]["precio"],2);
                    $sheet->row($c,$detalle);
                    $c=$c+1;*/
                    $invinicial = $invinicial + round($final[0]["cantidad"]*$final[0]["precio"],2);
                    foreach ($lista as $key => $value){
                        /*formatoCelda($sheet,'A'.$c,false);
                        formatoCelda($sheet,'B'.$c,false);
                        formatoCelda($sheet,'C'.$c,false);
                        formatoCelda($sheet,'D'.$c,false);
                        formatoCelda($sheet,'E'.$c,false);
                        formatoCelda($sheet,'F'.$c,false);
                        formatoCelda($sheet,'G'.$c,false);
                        formatoCelda($sheet,'H'.$c,false);
                        formatoCelda($sheet,'I'.$c,false);
                        formatoCelda($sheet,'J'.$c,false);
                        formatoCelda($sheet,'K'.$c,false);
                        formatoCelda($sheet,'L'.$c,false);
                        formatoCelda($sheet,'M'.$c,false);
                        formatoCelda($sheet,'N'.$c,false);
                        formatoCelda($sheet,'O'.$c,false);
                        formatoCelda($sheet,'P'.$c,false);
                        formatoCelda($sheet,'Q'.$c,false);*/
                        $detalle = array();
                        $nombrepaciente = '';
                        if ($value->persona_id !== NULL) {
                            $nombrepaciente = trim($value->person->bussinesname." ".$value->person->apellidopaterno." ".$value->person->apellidomaterno." ".$value->person->nombres);
                        }else{
                            $nombrepaciente = trim($value->nombrepaciente);
                        }
                        if($value->tipomovimiento_id=="5"){
                            $nombrepaciente = "ALMACEN";
                        }
                        if($value->tipodocumento_id=="4"){
                            $abreviatura="F";
                        }elseif($value->tipodocumento_id=="5"){
                            $abreviatura="B";    
                        }else{
                            $abreviatura="G"; 
                        }
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        $detalle[] = $value->codigo;
                        $detalle[] = $nombrepaciente;
                        if($value->tipomovimiento_id==4){//VENTA
                            $detalle[] = utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT));
                            $detalle[] = str_pad($value->numero,8,'0',STR_PAD_LEFT);
                            $detalle[] = "01";
                            $ventas = $ventas + abs($final[0]["precio"]*$value->cantidad);
                            $cantventas = $cantventas + abs($value->cantidad);
                        }elseif($value->tipomovimiento_id==6){//NOTA DE CREDITO
                            $detalle[] = utf8_encode(substr($value->numeroref,0,1).'C'.str_pad($value->serie,2,'0',STR_PAD_LEFT));
                            $detalle[] = str_pad($value->numero,8,'0',STR_PAD_LEFT);
                            $detalle[] = "05";
                            $ventas = $ventas - abs($final[0]["precio"]*$value->cantidad);
                            $cantventas = $cantventas - abs($value->cantidad);
                        }elseif($value->tipomovimiento_id==3){//COMPRA
                            $detalle[] = utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT));
                            $detalle[] = str_pad($value->numero,8,'0',STR_PAD_LEFT);
                            if($value->tipoDocumento_id==11){//NOTA DE CREDITO
                                $detalle[] = "06";
                                $compras = $compras - abs($value->subtotal2);
                                $cantcompras = $cantcompras - abs($value->cantidad);
                            }else{
                                $detalle[] = "02";
                                $compras = $compras + abs($value->subtotal2);
                                $cantcompras = $cantcompras + abs($value->cantidad);
                            }
                        }elseif($value->tipomovimiento_id==5){//ALMACEN
                            $detalle[] = utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT));
                            $detalle[] = str_pad($value->numero,8,'0',STR_PAD_LEFT);
                            if($value->motivo_id>0){
                                $detalle[] = $value->motivo;
                            }else{
                                $detalle[] = "99";
                            }
                            if($value->stock=="S"){
                                $compras = $compras + abs($value->subtotal2);
                                $cantcompras = $cantcompras + abs($value->cantidad);
                            }else{
                                $compras = $compras - abs($value->subtotal2);
                                $cantcompras = $cantcompras - abs($value->cantidad);
                            }                        
                        }

                        if($value->stock=="S"){
                            $detalle[] = abs($value->cantidad);
                            $detalle[] = $value->precio;
                            $detalle[] = abs($value->subtotal2);
                            $detalle[] = "";   
                            $detalle[] = "";   
                            $detalle[] = "";
                            $band=false;$indice=0;
                            for($x=0;$x<count($final);$x++){
                                if($final[$x]["precio"]==$value->precio){
                                    $band=true;
                                    $indice=$x;
                                    $x=999999;
                                }
                            }
                            if($band){
                                $final[$indice]["cantidad"]=$final[$indice]["cantidad"]+$value->cantidad;
                            }else{
                                $final[]=array("precio"=>$value->precio,"cantidad"=>$value->cantidad);
                            }
                        }else{
                            $detalle[] = "";
                            $detalle[] = "";   
                            $detalle[] = "";
                            $detalle[] = abs($value->cantidad);
                            //$detalle[] = $value->precio;
                            //$detalle[] = abs($value->subtotal2);
                            $detalle[] = $final[0]["precio"];
                            $detalle[] = abs($final[0]["precio"]*$value->cantidad);
                            $lista2=array();
                            $cantidad=$value->cantidad;
                            for($x=0;$x<count($final);$x++){
                                if(($final[$x]["cantidad"]+0)>=$cantidad){
                                    $final[$x]["cantidad"]=$final[$x]["cantidad"] - $cantidad;
                                    $lista2[]=$final[$x];
                                    $cantidad=0;
                                }else{
                                    $cantidad = $cantidad - $final[$x]["cantidad"]; 
                                }
                            }
                            $final=$lista2;
                        }
                        for($x=0;$x<count($final);$x++){
                            if($x==0){
                                $detalle[] = $final[$x]["cantidad"];
                                $detalle[] = $final[$x]["precio"];
                                $detalle[] = round($final[$x]["cantidad"]*$final[$x]["precio"],2);
                                $detalle[] = date("H:i:s",strtotime($value->created_at));
                                $detalle[] = $value->usuario;
                                //$sheet->row($c,$detalle);
                                $invfinal1 = round($final[$x]["cantidad"]*$final[$x]["precio"],2);
                                //$c=$c+1;
                            }else{
                                /*formatoCelda($sheet,'A'.$c,false);
                                formatoCelda($sheet,'B'.$c,false);
                                formatoCelda($sheet,'C'.$c,false);
                                formatoCelda($sheet,'D'.$c,false);
                                formatoCelda($sheet,'E'.$c,false);
                                formatoCelda($sheet,'F'.$c,false);
                                formatoCelda($sheet,'G'.$c,false);
                                formatoCelda($sheet,'H'.$c,false);
                                formatoCelda($sheet,'I'.$c,false);
                                formatoCelda($sheet,'J'.$c,false);
                                formatoCelda($sheet,'K'.$c,false);
                                formatoCelda($sheet,'L'.$c,false);
                                formatoCelda($sheet,'M'.$c,false);
                                formatoCelda($sheet,'N'.$c,false);
                                formatoCelda($sheet,'O'.$c,false);*/
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
                                $detalle[] = "";                        
                                $detalle[] = $final[$x]["cantidad"];
                                $detalle[] = $final[$x]["precio"];
                                $detalle[] = round($final[$x]["cantidad"]*$final[$x]["precio"],2);
                                //$sheet->row($c,$detalle);
                                //$c=$c+1;
                                $invfinal1 = $invfinal1 + round($final[$x]["cantidad"]*$final[$x]["precio"],2);
                            }
                        }
                    }
                    $invfinal = $invfinal + $invfinal1;
                }
                $c=0;
                /*formatoCelda($sheet,'G'.$c,false);
                formatoCelda($sheet,'I'.$c,false);
                formatoCelda($sheet,'J'.$c,false);
                formatoCelda($sheet,'L'.$c,false);
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = $cantcompras;
                $detalle[] = "";
                $detalle[] = round($compras,2);
                $detalle[] = $cantventas;
                $detalle[] = "";
                $detalle[] = round($ventas,2);                        
                $sheet->row($c,$detalle);*/
                
                $c=$c+5;
                formatoCelda($sheet,'K'.$c,false);
                $sheet->mergeCells("I$c:J$c");
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "INV. INICIAL";
                $detalle[] = "";
                $detalle[] = round($invinicial,2);
                $sheet->row($c,$detalle);
                $c=$c+1;
                
                formatoCelda($sheet,'K'.$c,false);
                $sheet->mergeCells("I$c:J$c");
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "(+)COMPRAS";
                $detalle[] = "";
                $detalle[] = round($compras,2);
                $sheet->row($c,$detalle);
                $c=$c+1;

                formatoCelda($sheet,'K'.$c,false);
                $sheet->mergeCells("I$c:J$c");
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "(-)INV. FINAL";
                $detalle[] = "";
                $detalle[] = '-'.round($invfinal,2);
                $sheet->row($c,$detalle);
                $c=$c+1;

                formatoCelda($sheet,'K'.$c,false);
                $sheet->mergeCells("I$c:J$c");
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "(=)COSTO DE VENTAS";
                $detalle[] = "";
                $detalle[] = round($invinicial + $compras - $invfinal,2);
                $sheet->row($c,$detalle);
                $c=$c+1;

            });
        })->export('xls');
    }
}

function formatoCelda($sheet,$celda,$negrita){
    $sheet->cells($celda, function($cells) use($negrita) {
        $cells->setBorder('thin','thin','thin','thin');
        $cells->setAlignment('center');
        if($negrita){
            $cells->setFont(array(
                'family'     => 'Calibri',
                'size'       => '10',
                'bold'       =>  true
                ));
        }else{
            $cells->setFont(array(
                'family'     => 'Calibri',
                'size'       => '10',
                ));
        }
    });
}