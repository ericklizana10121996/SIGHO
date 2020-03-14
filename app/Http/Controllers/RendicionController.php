<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Tipodocumento;
use App\Movimiento;
use App\Detallemovimiento;
use App\Person;
use App\Servicio;
use App\Caja;
use App\Area;
use App\Conceptopago;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Detallemovcaja;
use App\Librerias\EnLetras;
use Illuminate\Support\Facades\Auth;
use Excel;

class RendicionController extends Controller
{
    protected $folderview      = 'app.rendicion';
    protected $tituloAdmin     = 'Rendiciones';
    protected $tituloRegistrar = 'Registrar Rendicion';
    protected $tituloModificar = 'Realizar Rendicion';
    protected $tituloCobrar = 'Cobrar Venta';
    protected $tituloAnular  = 'Anular Venta';
    protected $rutas           = array('create' => 'rendicion.create', 
            'edit'   => 'rendicion.edit', 
            'anular' => 'movimientocaja.anular',
            'search' => 'rendicion.buscar',
            'index'  => 'rendicion.index',
            'detalle' => 'rendicion.detalle',
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
        $entidad          = 'Rendicion';
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago', 'conceptopago.id', '=', 'movimiento.conceptopago_id')
                            ->where('movimiento.situacion','<>','A')
                            ->where('conceptopago.id','<>','1')
                            ->where('conceptopago.id','<>','2')
                            ->where('conceptopago.id','=','39')
                            ->where('conceptopago.nombre','like','%'.strtoupper($request->input('concepto')).'%')
                            ->where('movimiento.caja_id','=',6);
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
        }
        if($request->input('nombre')!=""){
            $resultado = $resultado->where(function($q) use($request){
                        $q->where(DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('nombre').'%')
                          ->orWhere('paciente.bussinesname','like','%'.$request->input('nombre').'%');
                          });
        }
        if($request->input('situacion')!=""){
            if($request->input('situacion')!="C"){
                $resultado = $resultado->whereNull('movimiento.estadopago');
            }else{
                $resultado = $resultado->where('movimiento.estadopago','like','C');
            }
        }

        $resultado        = $resultado->select('movimiento.*',DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as persona2'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Concepto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_anular  = $this->tituloAnular;
        $titulo_cobrar    = $this->tituloCobrar;
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
        $entidad          = 'Rendicion';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        $cboSituacion = array('' => 'Todos', 'C' => 'Confirmado', 'P' => 'Pendiente');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user', 'cboSituacion'));
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
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $Rendicion = Movimiento::find($id);
        $entidad             = 'Rendicion';
        $formData            = array('rendicion.update', $id);
        $cboTipo = array('RH' => 'RH', 'FT' => 'FT', 'BV' => 'BV', 'TK' => 'TK', 'VR' => 'VR');
        $cboArea = array();
        $rs = Area::orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboArea = $cboArea + array($value->id => $value->nombre);
        }
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Guardar';
        return view($this->folderview.'.mant')->with(compact('Rendicion', 'formData', 'entidad', 'boton', 'listar', 'cboConcepto', 'cboTipo', 'cboArea'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request)
    {
        $existe = Libreria::verificarExistencia($request->input('id'), 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $Movimiento = Movimiento::find($request->input('id'));
            $Movimiento->estadopago='C';
            $Movimiento->save();
            $arr=explode(",",$request->input('listaCarro'));
            for($c=0;$c<count($arr);$c++){
                $arr2 = explode("@", $arr[$c]);
                $Detalle = new Detallemovcaja();
                $Detalle->fechaentrega = $arr2[1];
                $Detalle->persona_id = $arr2[4];
                $Detalle->precio = $arr2[6];
                $Detalle->recibo = $arr2[2];
                $Detalle->recibo2 = $arr2[3];
                $Detalle->area_id = $arr2[5];
                $Detalle->movimiento_id = $Movimiento->id;
                $Detalle->save();
            }
            if(($request->input('txtVuelto')+0)>0){
                $movimiento        = new Movimiento();
                $movimiento->fecha = date("Y-m-d");
                $movimiento->numero= Movimiento::NumeroSigueTesoreria(2,2,6);//movimiento caja y documento ingreso;
                $movimiento->responsable_id=$Movimiento->responsable_id;
                $movimiento->persona_id=$Movimiento->persona_id;    
                $movimiento->subtotal=0;
                $movimiento->igv=0;
                $movimiento->total=$request->input('txtVuelto'); 
                $movimiento->tipomovimiento_id=2;
                $movimiento->tipodocumento_id=2;
                $movimiento->conceptopago_id=88;
                $movimiento->comentario='INGRESO POR VUELTO';
                $movimiento->caja_id=6;
                $movimiento->situacion='N';
                $movimiento->listapago='';//Lista de pagos para transferencia y pago tambien
                $movimiento->voucher='';
                $movimiento->formapago='VR';
                $movimiento->nombrepaciente=$Movimiento->nombrepaciente;
                $movimiento->dni=$Movimiento->dni;
                $movimiento->numeroficha=$Movimiento->numeroficha;
                $movimiento->area_id=$Movimiento->area_id;
                $movimiento->movimiento_id=$Movimiento->id;
                $movimiento->save();
            }elseif(($request->input('txtVuelto')+0)<0){
                $movimiento        = new Movimiento();
                $movimiento->fecha = date("Y-m-d");
                $movimiento->numero= Movimiento::NumeroSigueTesoreria(2,3,6);//movimiento caja y documento ingreso;
                $movimiento->responsable_id=$Movimiento->responsable_id;
                $movimiento->persona_id=$Movimiento->persona_id;    
                $movimiento->subtotal=0;
                $movimiento->igv=0;
                $movimiento->total=$request->input('txtVuelto')*(-1); 
                $movimiento->tipomovimiento_id=2;
                $movimiento->tipodocumento_id=3;
                $movimiento->conceptopago_id=89;
                $movimiento->comentario='SALDO POR RENDICION';
                $movimiento->caja_id=6;
                $movimiento->situacion='N';
                $movimiento->listapago='';//Lista de pagos para transferencia y pago tambien
                $movimiento->voucher='';
                $movimiento->formapago='VR';
                $movimiento->nombrepaciente=$Movimiento->nombrepaciente;
                $movimiento->dni=$Movimiento->dni;
                $movimiento->numeroficha=$Movimiento->numeroficha;
                $movimiento->area_id=$Movimiento->area_id;
                $movimiento->movimiento_id=$Movimiento->id;
                $movimiento->save();
            }
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
        $cboFormaPago     = array("Efectivo" => "Efectivo", "Tarjeta" => "Tarjeta");
        $cboTipoTarjeta    = array("VISA" => "VISA", "MASTER" => "MASTER");
        $cboTipoTarjeta2    = array("CREDITO" => "CREDITO", "DEBITO" => "DEBITO");        
        $user = Auth::user();
        if($caja==0){//ADMISION 1
            $serie=3;
            $idcaja=1;
        }
        $formData = array('route' => array('ventaadmision.pagar', $id), 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Cobrar';
        return view($this->folderview.'.cobrar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar', 'cboCaja' , 'caja', 'cboFormaPago', 'cboTipoTarjeta2', 'cboTipoTarjeta'));
    }

    public function detalle($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $Rendicion = Movimiento::find($id);
        $entidad             = 'Rendicion';
        $formData            = array('rendicion.update', $id);
        $cboConcepto = array();
        $rs = Conceptopago::where(DB::raw('1'),'=','1')->where('tipo','LIKE','E')->where('id','<>','2')->where('id','<>',8)->where('id','<>',14)->where('id','<>',16)->where('id','<>',18)->where('id','<>',20)->where('id','<>',31)->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboConcepto = $cboConcepto + array($value->id => $value->nombre);
        }
        $cboTipo = array('RH' => 'RH', 'FT' => 'FT', 'BV' => 'BV', 'TK' => 'TK', 'VR' => 'VR');
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Detalle';
        return view($this->folderview.'.detalle')->with(compact('Rendicion', 'formData', 'entidad', 'boton', 'listar', 'cboConcepto', 'cboTipo'));
    }


    public function pdf(Request $request){
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago', 'conceptopago.id', '=', 'movimiento.conceptopago_id')
                            ->where('movimiento.situacion','<>','A')
                            ->where('conceptopago.id','<>','1')
                            ->where('conceptopago.id','<>','2')
                            ->where('conceptopago.id','=','39')
                            ->where('conceptopago.nombre','like','%'.strtoupper($request->input('concepto')).'%')
                            ->where('movimiento.caja_id','=',6)
                            ->where('movimiento.estadopago','like','C');
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
        }
        if($request->input('nombre')!=""){
            $resultado = $resultado->where(function($q) use($request){
                        $q->where(DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('nombre').'%')
                          ->orWhere('paciente.bussinesname','like','%'.$request->input('nombre').'%');
                          });
        }

        $resultado        = $resultado->select('movimiento.*',DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as persona2'),DB::raw('(select sum(precio) from detallemovcaja where movimiento_id=movimiento.id) as total2'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();   
        $pdf = new TCPDF();
        $pdf::SetTitle('Reporte de Rendicion');
        if (count($lista) > 0) {            
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Reporte de Rendicion del ".date("d/m/Y",strtotime($request->input('fechainicial')))." al ".date("d/m/Y",strtotime($request->input('fechafinal')))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(14,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(55,6,utf8_decode("PROVEEDOR"),1,0,'C');
            $pdf::Cell(10,6,utf8_decode("TIPO"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("TOTAL"),1,0,'C');
            $pdf::Cell(30,6,utf8_decode("AREA"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("ORDEN"),1,0,'C');
            $pdf::Cell(55,6,utf8_decode("PERSONA"),1,0,'C');
            $pdf::Cell(14,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("VUELTO"),1,0,'C');
            $pdf::Ln();
            $fecha="";$total=0;$totalgeneral=0;$idmedico=0;$c=0;$d=0;
            foreach ($lista as $key => $value){
                $pdf::SetFont('helvetica','',7);
                $detalle = Detallemovcaja::where('movimiento_id','=',$value->id)->get();
                foreach ($detalle as $key1 => $value1) {
                    $pdf::Cell(14,6,date("d/m/Y",strtotime($value1->fechaentrega)),1,0,'C');
                    $pdf::Cell(55,6,trim(substr($value1->persona->apellidopaterno.' '.$value1->persona->apellidomaterno.' '.$value1->persona->nombres.' '.$value1->persona->bussinesname,0,40)),1,0,'L');
                    $pdf::Cell(10,6,$value1->recibo,1,0,'L');
                    $pdf::Cell(20,6,$value1->recibo2,1,0,'L');
                    $pdf::Cell(15,6,number_format($value1->precio,2,'.',''),1,0,'R');
                    $pdf::Cell(30,6,substr($value->area->nombre,0,25),1,0,'L');
                    $pdf::Cell(15,6,$value->numero,1,0,'L');
                    if($value->persona_id>0 && $value->persona->bussinesname!=""){
                        $pdf::Cell(55,6,trim($value->persona->bussinesname ),1,0,'L');
                    }else{
                        $pdf::Cell(55,6,trim($value->persona2),1,0,'L');
                    }
                    $pdf::Cell(14,6,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    $pdf::Cell(15,6,number_format($value->total - $value->total2,2,'.',''),1,0,'R');
                    $pdf::Ln();    
                    $total=$total + $value1->precio;
                }
            }
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(100,6,("TOTAL :"),0,0,'R');
            $totalgeneral = $totalgeneral + $total;
            $pdf::Cell(15,6,number_format($totalgeneral,2,'.',''),0,0,'R');
            $pdf::Ln();
        }
        $pdf::Output('ReporteTomografia2.pdf');     
    }

    public function excel(Request $request){
       $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago', 'conceptopago.id', '=', 'movimiento.conceptopago_id')
                            ->where('movimiento.situacion','<>','A')
                            ->where('conceptopago.id','<>','1')
                            ->where('conceptopago.id','<>','2')
                            ->where('conceptopago.id','=','39')
                            ->where('conceptopago.nombre','like','%'.strtoupper($request->input('concepto')).'%')
                            ->where('movimiento.caja_id','=',6);
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
        }
        if($request->input('nombre')!=""){
            $resultado = $resultado->where(function($q) use($request){
                        $q->where(DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('nombre').'%')
                          ->orWhere('paciente.bussinesname','like','%'.$request->input('nombre').'%');
                          });
        }
        if($request->input('situacion')!=""){
            if($request->input('situacion')!="C"){
                $resultado = $resultado->whereNull('movimiento.estadopago');
            }else{
                $resultado = $resultado->where('movimiento.estadopago','like','C');
            }
        }

        $resultado        = $resultado->select('movimiento.*',DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as persona2'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();

        Excel::create('ExcelRendicionTesoreria', function($excel) use($lista,$request) {
 
            $excel->sheet('Rendiciones', function($sheet) use($lista,$request) {
                $celdas      = 'A1:J1';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $title[] = "Reporte de Rendicion del ".date("d/m/Y",strtotime($request->input('fechainicial')))." al ".date("d/m/Y",strtotime($request->input('fechafinal')));
                $sheet->row(1,$title);
                $cabecera=array();
                $cabecera[] = "Numero" ;               
                $cabecera[] = "Fecha";
                $cabecera[] = "Persona";
                $cabecera[] = "Tipo Doc.";
                $cabecera[] = "Nro";  
                $cabecera[] = "Concepto";               
                $cabecera[] = "Total";
                $cabecera[] = "Comentario";
                $cabecera[] = "Vuelto" ;
                $cabecera[] = "Reembolso" ;
                $sheet->row(3,$cabecera);
                $sheet->cells("A3:J3", function($cells) {
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setValignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $c=4;$d=3;
                $fecha="";$total=0;$totalgeneral=0;$idmedico=0;$w = 1;
                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = $w;
                    $detalle[] = date("d/m/Y",strtotime($value->fecha));
                    if($value->empresa_id>0){
                        $detalle[] = $value->empresa->bussinesname;
                    }else{
                        if($value->persona_id>0 && $value->persona->bussinesname!=""){
                            $detalle[] = $value->persona->bussinesname;
                        }else{
                            $detalle[] = $value->persona2;
                        }
                    }
                    if($value->caja_id==4){
                        if($value->formapago!=""){
                            $detalle[] = $value->formapago;
                        }elseif($value->tipodocumento_id==7){
                            $detalle[] = 'BV';
                        }elseif($value->tipodocumento_id==6){
                            $detalle[] = 'FT';
                        }
                    }else{
                        $detalle[] = $value->formapago;
                    }
                    $detalle[] = $value->numero;
                    $detalle[] = $value->conceptopago->nombre;
                    $detalle[] = $value->total;
                    $detalle[] = $value->comentario;
                    $sheet->row($c,$detalle);
                    $sheet->cells("A".$c.":J".$c, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setValignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            ));
                    });
                    $sheet->cells("G".$c, function($cells) {
                        $cells->setBorder('thin','','thin','');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'color' => array('rgb' => 'FF0000'),
                            'fill' =>  array('color'=> array('rgb' => 'FF0000')),
                            ));
                    });
                    $cVuelto = $c;
                    $c++;
                    $detalle = Detallemovcaja::where('movimiento_id','=',$value->id)->get();
                    $d2=0;$total2=0;$w2 = 1;
                    foreach ($detalle as $key2 => $value2) {
                        $detalle = array();
                        $detalle[] = $w.".".$w2;$w2++;
                        $detalle[] = date("d/m/Y",strtotime($value2->fechaentrega));
                        $detalle[] = $value2->persona->apellidopaterno.' '.$value2->persona->apellidomaterno.' '.$value2->persona->nombres.' '.$value2->persona->bussinesname;
                        $detalle[] = $value2->recibo;
                        $detalle[] = $value2->recibo2;
                        $detalle[] = $value2->area->nombre;
                        $detalle[] = number_format($value2->precio,2,'.','');
                        $total2 = $total2 + $value2->precio;
                        $sheet->row($c,$detalle);
                        $sheet->cells("A".$c.":J".$c, function($cells) {
                            $cells->setBorder('thin','thin','thin','thin');
                            $cells->setValignment('center');
                            $cells->setFont(array(
                                'family'     => 'Calibri',
                                'size'       => '10',
                                ));
                        });
                        $sheet->cells("G".$c, function($cells) {
                            $cells->setBorder('thin','','thin','');
                            $cells->setFont(array(
                                'family'     => 'Calibri',
                                'size'       => '10',
                                'color' => array('rgb' => 'FF0000'),
                                'fill' =>  array('color'=> array('rgb' => 'FF0000')),
                                ));
                        });
                        $c++;
                    }
                    if($value->total-$total2 > 0){
                        $sheet->setCellValue("I".$cVuelto,$value->total-$total2);
                    }elseif($value->total-$total2 < 0){
                        $sheet->setCellValue("J".$cVuelto,$total2-$value->total);
                    }
                    $w++;
                    $c++;
                }
            });
        })->export('xlsx');
    }

}
