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
use App\Cie;
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
USE Excel;

class RetramiteController extends Controller
{
    protected $folderview      = 'app.retramite';
    protected $tituloAdmin     = 'Retramite';
    protected $tituloRegistrar = 'Registrar Retramite por Devolución';
    protected $tituloModificar = 'Modificar Siniestro';
    protected $tituloEliminar  = 'Eliminar Factura';
    protected $rutas           = array('create' => 'retramite.create', 
            'edit'   => 'retramite.edit', 
            'delete' => 'retramite.eliminar',
            'search' => 'retramite.buscar',
            'index'  => 'retramite.index',
            'pdfListar'  => 'retramite.pdfListar',
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
        $entidad          = 'Retramite';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fecha            = Libreria::getParam($request->input('fechainicial'));
        $fecha2            = Libreria::getParam($request->input('fechafinal'));
        $user = Auth::user();
        /*$resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where('movimiento.situacion','like','%'.$request->input('situacion').'%')
                            ->where('plan.razonsocial', 'LIKE', '%'.strtoupper($request->input('empresa')).'%')
                            ->where(DB::raw('concat(movimiento.serie,\'-\',movimiento.numero)'),'LIKE','%'.$numero.'%')->where('movimiento.tipodocumento_id','=','17')
                            ->whereNotIn('movimiento.situacion',['U','A']);*/
        /*
        if($request->input('situacion') != ""){
            $resultado->where('movimiento.situacion','like','%'.$request->input('situacion').'%');
        }else{
            $resultado->whereIn('movimiento.situacion',["P","C"]);
        }
        if($fecha!=""){
            $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fecha.'');
        }
        if($fecha2!=""){
            $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fecha2.'');
        }
        if($fecha3!=""){
            $resultado = $resultado->where('movimiento.fechaentrega', '>=', ''.$fecha3.'');
        }
        if($fecha4!=""){
            $resultado = $resultado->where('movimiento.fechaentrega', '<=', ''.$fecha4.'');
        }
        *//*
        if($fecha!=""){
            if($request->input('situacion')=='P')
                $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fecha.'');
            else
                $resultado = $resultado->where('movimiento.fechaentrega', '>=', ''.$fecha.'');
        }
        if($fecha2!=""){
            if($request->input('situacion')=='P')
                $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fecha2.'');
            else
                $resultado = $resultado->where('movimiento.fechaentrega', '<=', ''.$fecha2.'');
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable',DB::raw('plan.razonsocial as empresa'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');*/

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where('movimiento.situacion','like','%'.$request->input('situacion').'%')
                            ->where('plan.razonsocial', 'LIKE', '%'.strtoupper($request->input('empresa')).'%')
                            ->where(DB::raw('concat(movimiento.serie,\'-\',movimiento.numero)'),'LIKE','%'.$numero.'%')->where('movimiento.tipodocumento_id','=','17')
                            ->whereNotIn('movimiento.situacion',['U','A']);
        if($fecha!=""){
            if($request->input('situacion')=='P')
                $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fecha.'');
            else
                $resultado = $resultado->where('movimiento.fechaentrega', '>=', ''.$fecha.'');
        }
        if($fecha2!=""){
            if($request->input('situacion')=='P')
                $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fecha2.'');
            else
                $resultado = $resultado->where('movimiento.fechaentrega', '<=', ''.$fecha2.'');
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable',DB::raw('plan.razonsocial as empresa'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');

        $total = $resultado->sum('movimiento.total');/**$value->total - $value->detraccion - $value->retencion*/
        //$detraccion = $resultado->sum('movimiento.detraccion');
        //$retencion = $resultado->sum('movimiento.retencion');
        //$total = $total - $detraccion - $retencion;
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Empresa', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Ope.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Detraccion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Retencion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'conf','total'));
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
        $entidad          = 'Retramite';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        $cboSituacion = array(''=>'Todos','P'=>'Pendiente','C'=>'Cobrado');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user','cboSituacion'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Retramite';
        $retramite = null;
        $titulo_registrar = $this->tituloRegistrar;
        $formData            = array('retramite.store');
        $user = Auth::user();
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.create')->with(compact('retramite', 'formData', 'entidad', 'boton', 'listar'));
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
        // $reglas     = array(
        //         'fechapago'                  => 'required',
        //         'total'         => 'required',
        //         );
        // $mensajes = array(
        //     'fechapago.required'         => 'Debe seleccionar una fecha',
        //     'total.required'         => 'Debe agregar detalle a pagar',
        //     );

        $reglas     = array(
            'observacion_c_text' => 'required',
            'observacion_p_text' => 'required',
            'fecha_desc_text'    => 'required',
            'numeroCarta_text'   => 'required',
            'facturaAsoc_text'   => 'required',
        );  
    
        $mensajes = array(
            'observacion_c_text.required' => 'Indique Observacion de Compañia',
            'observacion_p_text.required' => 'Indique Descargo de Personal',
            'fecha_desc_text.required'    => 'Indique Fecha de Observacion',
            'numeroCarta_text.required'   => 'Indique Nro de Carta',
            'facturaAsoc_text.required'   => 'Indique Factura Asociada',
        );


        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       
        
        $user = Auth::user();
        // $dat=array();
        $error = DB::transaction(function() use($request,$user){
            // $arr=explode(",",$request->input('listar'));
            // for($c=0;$c<count($arr);$c++){
            //     $Movimiento = Movimiento::find($arr[$c]);
            //     $Movimiento->fechaentrega=$request->input('fechapago');
            //     $Movimiento->voucher=$request->input('voucher');
            //     $Movimiento->detraccion=$request->input('txtDetraccion'.$arr[$c]);
            //     $Movimiento->retencion=$request->input('txtRetencion'.$arr[$c]);
            //     $Movimiento->situacion='C';
            //     $Movimiento->usuarioentrega_id=$user->id;
            //     $Movimiento->save();
            // }
               // --------------------- RETRAMITES [CAMPOS] ---------------------------

             if( !is_null($request->input('observacion_c_text')) && !is_null($request->input('observacion_p_text'))
                && !is_null($request->input('fecha_desc_text')) && !is_null($request->input('numeroCarta_text'))
                    && !is_null($request->input('facturaAsoc_text'))
             ){
                $idFactRef = $request->input('facturaAsoc_id');
                $mov = Movimiento::find($idFactRef);
                // dd($mov);
                if(!is_null($mov)){
                    if(is_null($mov->facturaAsociada_id) ) {
                        $mov->observacionCompania = $request->input('observacion_c_text');
                        $mov->descargoPersonal    = $request->input('observacion_p_text');
                        $mov->fechaObservacion    = $request->input('fecha_desc_text');
                        $mov->numCarta            = $request->input('numeroCarta_text');
                       
                        $mov->facturaAsociada_id = $mov->id;
                    }else{
                        $mov->observacionCompania = $mov->observacionCompania .'/'.$request->input('observacion_c_text');
                        $mov->descargoPersonal    = $mov->descargoPersonal    .'/'.$request->input('observacion_p_text').' con Fecha:'.date('d/m/Y'.strtotime($request->input('fecha_desc_text')));
                        $mov->fechaObservacion    = $mov->fechaObservacion;
                        $mov->numCarta            = $mov->numCarta            .'/'.$request->input('numeroCarta_text');
                        $mov->facturaAsociada_id  = $mov->facturaAsociada_id;
                    }

                    $mov->retramite = 'S';  
                    $mov->update();
                    
                }

            }    
            
            // $dat[0]=array("respuesta"=>"OK");
        });
        // dd($error);
        return is_null($error)? "OK" : $error;
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
        $movimiento = Movimiento::find($id);
        $entidad             = 'Facturacion';
        $formData            = array('facturacion.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.siniestro')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar'));
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
        $error = DB::transaction(function() use($request, $id){
            $movimiento        = Movimiento::find($id);
            $movimiento->comentario = $request->input('siniestro');
            $movimiento->save();
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
    
    public function buscardocumento(Request $request)
    {
        $descripcion = $request->input("numero");
        $idplan = trim($request->input("plan_id"));
        $resultado = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                    ->where(DB::raw('concat(movimiento.serie,\'-\',movimiento.numero)'),'LIKE','%'.$descripcion.'%')
                    ->where('movimiento.plan_id','=',$request->input('plan_id'))
                    ->where('movimiento.situacion','like','P');
        $resultado    = $resultado->select('movimiento.*')->get();
        if(count($resultado)>0){
            $c=0;
            foreach ($resultado as $key => $value){
                $data[$c] = array(
                            'id' => $value->id,
                            'numero' => $value->serie.'-'.$value->numero,
                            'fecha' => date("d/m/Y",strtotime($value->fecha)),
                            'paciente' => $value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres,
                            'total' => $value->total,
                        );
                        $c++;                
            }            
        }else{
            $data = array();
        }
        return json_encode($data);
    }

    public function seleccionardocumento(Request $request)
    {
        $resultado = Movimiento::find($request->input('id'));
        $data[0] = array(
            'id' => $resultado->id,
            'fecha' => date("d/m/Y",strtotime($resultado->fecha)),
            'numero' => $resultado->serie.'-'.$resultado->numero,
            'total' => $resultado->total,
            'paciente' => $resultado->persona->apellidopaterno.' '.$resultado->persona->apellidomaterno.' '.$resultado->persona->nombres,
        );
            
        return json_encode($data);
    }
    
   	public function pdfComprobante(Request $request){
        $entidad          = 'Facturacion';
        $id               = Libreria::getParam($request->input('id'),'');
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
                $pdf::Cell(60,7,utf8_encode("RUC N° 20480082673"),'RTL',0,'C');
                $pdf::Ln();
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Cell(60,7,utf8_encode("FACTURA"),'RL',0,'C');
                $pdf::Ln();
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Cell(60,7,utf8_encode("ELECTRÓNICA"),'RL',0,'C');
                $pdf::Ln();
                $pdf::Cell(130,7,"",0,0,'C');
                $abreviatura="F";
                $dni=$value->empresa->ruc;
                $subtotal=number_format($value->subtotal,2,'.','');
                $igv=number_format($value->total - $subtotal,2,'.','');
                $pdf::Cell(60,7,utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),'RBL',0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(0,7,utf8_decode("HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA"),0,0,'L');
                $pdf::Ln();
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,utf8_encode("Paciente: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                //$ticket = Movimiento::find($value->movimiento_id);
                $pdf::Cell(110,6,(trim($value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,utf8_encode("DNI: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,(trim($value->persona->dni)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,utf8_encode("Nombre / Razón Social: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(110,6,(trim($value->empresa->bussinesname)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,utf8_encode($abreviatura=="F"?"RUC :":"DNI".": "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,utf8_encode($dni),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,utf8_encode("Dirección: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(110,6,(trim($value->empresa->direccion)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,utf8_encode("Fecha de emisión: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,utf8_encode($value->fecha),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,utf8_encode("Moneda: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(70,6,(trim('PEN - Sol')),0,0,'L');
                $pdf::Cell(40,6,(trim('PENDIENTE')),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $historia = Historia::where('person_id','=',$value->persona_id)->first();
                $pdf::Cell(30,6,utf8_encode("Historia: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,utf8_encode($historia->numero),0,0,'L');
                $pdf::Ln();
                
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(10,7,("Item"),1,0,'C');
                $pdf::Cell(13,7,utf8_encode("Código"),1,0,'C');
                $pdf::Cell(107,7,utf8_encode("Descripción"),1,0,'C');
                $pdf::Cell(10,7,("Und."),1,0,'C');
                $pdf::Cell(15,7,("Cantidad"),1,0,'C');
                $pdf::Cell(20,7,("V. Unitario"),1,0,'C');
                //$pdf::Cell(20,7,("P. Unitario"),1,0,'C');
                //$pdf::Cell(20,7,("Descuento"),1,0,'C');
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
                            $nombre=trim($v->descripcion);    
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
                    $pdf::Cell(13,7,$codigo,1,0,'C');
                    if(strlen($nombre)<60){
                        $pdf::Cell(107,7,($nombre),1,0,'L');
                    }else{
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(107,3.5,($nombre),1,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(107,7,"",1,0,'L');
                    }
                    $pdf::Cell(10,7,("ZZ."),1,0,'C');
                    $pdf::Cell(15,7,number_format($v->cantidad,2,'.',''),1,0,'R');
                    if($value->igv>0){
                        $pdf::Cell(20,7,number_format($v->precio/1.18,2,'.',''),1,0,'R');
                        $pdf::Cell(20,7,number_format($v->precio*$v->cantidad/1.18,2,'.',''),1,0,'R');
                    }else{
                        $pdf::Cell(20,7,number_format($v->precio,2,'.',''),1,0,'R');
                        $pdf::Cell(20,7,number_format($v->precio*$v->cantidad,2,'.',''),1,0,'R');
                    }
                    //$pdf::Cell(20,7,number_format($v->precio,2,'.',''),1,0,'R');
                    //$pdf::Cell(20,7,("0.00"),1,0,'R');
                    //$pdf::Cell(20,7,number_format($v->precio*$v->cantidad,2,'.',''),1,0,'R');
                    $pdf::Ln();                    
                }
                $pdf::Cell(70,7,"",0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(20,7,"COPAGO:",0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,7,number_format($value->copago,2,'.',''),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(20,7,"COASEGURO:",0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,7,number_format($value->montoinicial,2,'.','').'%',0,0,'L');
                $pdf::Ln();                    
                $letras = new EnLetras();
                $pdf::SetFont('helvetica','B',8);
                $valor=$letras->ValorEnLetras($value->total, "SOLES" );//letras
                
                $pdf::Cell(116,5,utf8_decode($valor),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(40,5,utf8_decode('Op. Gravada'),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                if($igv>0)
                    $pdf::Cell(20,5,$subtotal,0,0,'R');
                else
                    $pdf::Cell(20,5,'0.00',0,0,'R');
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
                if($igv>0)
                    $pdf::Cell(20,5,'0.00',0,0,'R');
                else
                    $pdf::Cell(20,5,$subtotal,0,0,'R');
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
                $pdf::Cell(0,5,utf8_encode('Representación Impresa de la Factura Electrónica, consulte en https://sfe.bizlinks.com.pe'),0,0,'L');
                $pdf::Ln();
                $pdf::Output('Comprobante.pdf');
            }
        }
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

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fecha            = Libreria::getParam($request->input('fechainicial'));
        $fecha2            = Libreria::getParam($request->input('fechafinal'));
        $user = Auth::user();
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where('movimiento.situacion','like','%'.$request->input('situacion').'%')
                            ->where('plan.razonsocial', 'LIKE', '%'.strtoupper($request->input('empresa')).'%')
                            ->where(DB::raw('concat(movimiento.serie,\'-\',movimiento.numero)'),'LIKE','%'.$numero.'%')->where('movimiento.tipodocumento_id','=','17')
                            ->whereNotIn('movimiento.situacion',['U','A']);
        if($fecha!=""){
            if($request->input('situacion')=='P')
                $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fecha.'');
            else
                $resultado = $resultado->where('movimiento.fechaentrega', '>=', ''.$fecha.'');
        }
        if($fecha2!=""){
            if($request->input('situacion')=='P')
                $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fecha2.'');
            else
                $resultado = $resultado->where('movimiento.fechaentrega', '<=', ''.$fecha2.'');
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable',DB::raw('plan.razonsocial as empresa'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        $resultado            = $resultado->get();

        Excel::create('ExcelReporteRetramite', function($excel) use($resultado,$request) {
 
            $excel->sheet('Retramite', function($sheet) use($resultado,$request) {
 
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Nro";
                $cabecera[] = "Empresa";
                $cabecera[] = "Paciente";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Nro. Ope.";
                $cabecera[] = "Total";
                $cabecera[] = "Detraccion";
                $cabecera[] = "Retencion";
                $cabecera[] = "Pago";
                $cabecera[] = "Situacion";
                $cabecera[] = "Usuario";
                $c=2;$d=3;$band=true;
                $sheet->row(1,$cabecera);
                $total = 0;

                foreach ($resultado as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->serie.'-'.$value->numero;
                    $detalle[] = $value->empresa;
                    $detalle[] = $value->paciente;
                    if($value->fechaentrega!="")
                        $detalle[] = date("d/m/Y",strtotime($value->fechaentrega));
                    else
                        $detalle[] = '';
                    $detalle[] = $value->voucher;
                    $detalle[] = number_format($value->total,2,'.','');
                    $detalle[] = number_format($value->detraccion,2,'.','');
                    $detalle[] = number_format($value->retencion,2,'.','');
                    $detalle[] = number_format($value->total - $value->detraccion - $value->retencion,2,'.','');
                    if($value->situacion=='P')
                        $detalle[] = 'PENDIENTE';
                    else
                        $detalle[] = "COBRADO";
                    $detalle[] = $value->responsable;
                    $total = $total + $value->total;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
                $detalle = array("","","","","","",$total);
                $sheet->row($c,$detalle);
            });
        })->export('xls');
    }
}
