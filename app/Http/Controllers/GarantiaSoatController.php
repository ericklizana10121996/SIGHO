<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Conceptopago;
use App\Movimiento;
use App\Person;
use App\Caja;
use App\Detallemovcaja;
use App\DetalleCirugiaParticular;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
class GarantiaSoatController extends Controller
{
    protected $folderview      = 'app.garantiasoat';
    protected $tituloAdmin     = 'Garantias SOAT';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Pago Particular';
    protected $tituloEliminar  = 'Eliminar el Pago Particular';
    protected $rutas           = array('create' => 'garantiasoat.create', 
            'pagar'   => 'pagoparticular.pago', 
            'regularizar' => 'garantiasoat.regularizar',
            'delete' => 'garantia.eliminar',
            'search' => 'garantiasoat.buscar',
            'index'  => 'garantiasoat.index',
            'pdfRecibo' => 'garantia.pdfRecibo',
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
    public function update(Request $request, $id)
    {
       $error = DB::transaction(function() use($request,$id){
           $detalles = DetalleCirugiaParticular::where('movimiento_id','=',$id)->whereNull('deleted_at')->get();
           
           // $user = Auth::user();
           // $pago->responsableActualiza_id =  $user->person_id;
           // $person = Person::find($pago->responsable_id);
           // $pago->nombre_responsable_actualiza = $person->nombres;
        
           // $detalles = DetalleMovimientoCirugia::where('cirugia_id','=',$id)->get();
           // $cont_detalles_pagados = 0;
           // $cont_detalles_general = 0;

           foreach ($detalles as $value) {
                $d = DetalleCirugiaParticular::find($value->id);
                if(is_null($d->fechaPago) && $d->situacion == 'N'){
                    // $d->usuario_actualiza_id = $user->person_id;  
                    // $d->usuario_actualiza = $person->nombres;    
                    // $d->update();
                    $d->delete();
                    // $cont_detalles_pagados++;
                }
                // $cont_detalles_general++;
           }
           // // dd($cont_detalles_pagados, $cont_detalles_general);
           // if ($cont_detalles_pagados === $cont_detalles_general) {
           //     $pago->pago_total = $request->input('total');           
           // }
           // $pago->update();


           if($request->input('listServicio') !== ''){ 
               $lista_ax = explode(',', $request->input('listServicio'));
               // dd($lista_ax);
                
               foreach ($lista_ax as $key => $value) {
                 $det =  DetalleCirugiaParticular::find($value);

                 if(is_null($det)){
                     $det = new DetalleCirugiaParticular;
                 }

                 // dd($det->fechaPago);

                 $user = Auth::user();
                 if(is_null($det->fechaPago) || $det->fechaPago == ''){
                     // $det = new DetalleCirugiaParticular;
                     $det->movimiento_id = $id;
                     $det->doctor_id  = $request->input('txtIdMedico'.$value);
                     
                     $det->usuario_registro = $user->person_id;
                     $det->descripcion  = $request->input('txtServicio'.$value);
                     $det->cantidad  = $request->input('txtCantidad'.$value);
                     $det->monto  = $request->input('txtPrecio'.$value);   
                     $det->subTotal  = $request->input('txtTotal'.$value);   
                     $det->situacion = $request->input('txtPagado'.$value);

                     if ($request->input('txtPagado'.$value) === 'S') {
                        $det->usuario_pago = $user->person_id; 
                        $det->fechaPago = date('Y-m-d H:i:s'); 
                     }

                     $det->save();

                     // $det->movimiento_id = $id;
                     // $det->doctor_id  = $request->input('txtIdMedico'.$value);
                     // // $det->usuario_registro_id = $user->person_id;
                     // $det->usuario_registro    = $person->person_id;
                     
                     // $det->usuario_actualiza_id = $user->person_id;      
                     // $det->usuario_actualiza    = $person->nombres;

                     // $det->descripcion  = $request->input('txtServicio'.$value);
                     // $det->cantidad  = $request->input('txtCantidad'.$value);
                     // $det->monto  = $request->input('txtPrecio'.$value);   
                     // $det->sub_total  = $request->input('txtTotal'.$value);   
                     // $det->save();                   
                 }else{
                     if ($request->input('txtPagado'.$value) === 'N') {
                         $det->situacion = 'N';
                         $det->usuario_pago = null; 
                         $det->fechaPago = null;    
                     }
                     $det->descripcion  = $request->input('txtServicio'.$value);
                     $det->usuario_registro = $user->person_id;
                     $det->update();
                 }
               }
            }else{
                $dat[0]=array("respuesta"=>"ERROR","msg"=>"La Cirugia no contiene Detalles");
                return json_encode($dat);
       
            }


       });
        
       $dat[0]=array("respuesta"=>"OK");

       return is_null($error) ? json_encode($dat) : $error;
    }

    public function pdfRecibo(Request $request){
        // dd($request);
        if(!is_null($request->get('pagado')) ){
           $user = Auth::user();
           $d =  DetalleCirugiaParticular::find($request->input('id'));
           $d->situacion = 'S';
           $d->fechaPago = date('Y-m-d H:i:s');
           $d->usuario_pago = $user->person_id; 
           $d->update();
        }

        $det = DetalleCirugiaParticular::find($request->input('id'));
        $lista = Movimiento::where('id','=',$det->movimiento_id)->first();
                 // dd($lista->persona_id);   
        $pdf = new TCPDF();
        $pdf::SetTitle('Garantia Medica');
        $pdf::AddPage();
        if($lista->conceptopago_id==10 && !is_null($det->fechaPago)){//GARANTIAS
            $pdf::SetFont('helvetica','B',10);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 35, 10);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 105, 0, 35, 10);
        
            // $pdf::Cell(50,7,utf8_decode(""),0,0,'L');
            $pdf::Cell(50,10,utf8_encode("GARANTÍA MÉDICA"),0,0,'C');
            $pdf::Cell(50,7,utf8_decode(""),0,0,'L');
            $pdf::Cell(50,10,utf8_encode("GARANTÍA MÉDICA"),0,0,'C');
          
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
                $pdf::Cell(30,7,utf8_decode("DOCTOR PRINC:"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(70,7,($lista->doctor->apellidopaterno." ".$lista->doctor->apellidomaterno." ".$lista->doctor->nombres),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,7,utf8_decode("DOCTOR PRINC:"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(30,7,($lista->doctor->apellidopaterno." ".$lista->doctor->apellidomaterno." ".$lista->doctor->nombres),0,0,'L');
                $pdf::Ln();

                $doc = $det->doctor->apellidopaterno.' '. $det->doctor->apellidomaterno. ' '. $det->doctor->nombres;
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,7,utf8_decode("DOCTOR PART:"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(70,7,($doc),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,7,utf8_decode("DOCTOR PART:"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(30,7,($doc),0,0,'L');
                $pdf::Ln();
            
            }
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("TOTAL :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,number_format($det->subTotal,2,'.',''),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("TOTAL :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,number_format($det->subTotal,2,'.',''),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(25,7,utf8_decode("COMENTARIO:"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(75,7,utf8_decode($det->descripcion),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(25,7,utf8_decode("COMENTARIO:"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,utf8_decode($det->descripcion),0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $registra = $det->responsableRegistra->apellidopaterno.' '. $det->responsableRegistra->apellidomaterno.' '. $det->responsableRegistra->nombres;
            $paga = $det->responsablePaga->apellidopaterno.' '. $det->responsablePaga->apellidomaterno.' '. $det->responsablePaga->nombres;
            
            $pdf::Cell(20,7,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,($paga),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,7,utf8_decode("USUARIO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,($paga),0,0,'L');
  
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("FECHA DE PAGO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(70,7,(date('d/m/Y h:i a',strtotime($det->fechaPago))),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(30,7,utf8_decode("FECHA DE PAGO :"),0,0,'L');
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(80,7,(date('d/m/Y h:i a',strtotime($det->fechaPago))),0,0,'L');
            
        }else{

        }

        $pdf::Output('ReciboCaja.pdf');
        
    }

    public function confirmarmedicos($id, $listarLuego, Request $request){
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Movimiento::find($id);
        $entidad  = 'Garantia';

        $cant = DetalleCirugiaParticular::where('movimiento_id','=',$id)->whereNull('deleted_at')->count();
        if ($cant > 0) {
            $formData            = array('garantia.update', $id);
            $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off'); 
            $boton               = 'Actualizar'; 
    
        }else{
            $formData            = array('garantia.store');
            $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
            $boton               = 'Registrar'; 
        
        }
      
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
        $user = Auth::user();
        // $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        return view($this->folderview.'.confirmarmedicos')->with(compact('formData', 'entidad', 'boton', 'listar','idcaja','modelo'));
    }

    public function seleccionardetalles(Request $request){
      $id = $request->get('idmov');  

      // dd($id);  
      $detalles = DetalleCirugiaParticular::join('person as doctor','doctor.id','=','detalle_cirugiaparticular.doctor_id')->where('movimiento_id','=',$id)
          ->select(DB::raw("CONCAT(doctor.apellidopaterno,' ', doctor.apellidomaterno,' ', doctor.nombres) as doctor"),'detalle_cirugiaparticular.id','detalle_cirugiaparticular.doctor_id','detalle_cirugiaparticular.descripcion','detalle_cirugiaparticular.cantidad','detalle_cirugiaparticular.monto','detalle_cirugiaparticular.subTotal','detalle_cirugiaparticular.situacion',DB::raw("DATE_FORMAT(detalle_cirugiaparticular.fechaPago,'%Y-%m-%d') as fechaPago"))
          ->get();

      return $detalles;
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'GarantiaSoat';
        $nombre           = Libreria::getParam($request->input('nombre'));
        $paciente           = Libreria::getParam($request->input('paciente'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                ->join('person as medico','medico.id','=','movimiento.doctor_id')
                ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','movimiento.usuarioentrega_id')
                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                ->where('movimiento.situacion', 'not like', 'A')
                ->whereIn('movimiento.conceptopago_id',['150'])
                ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%');

        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $resultado = $resultado->where('movimiento.situacion','LIKE',$situacion);
        }
        $resultado        = $resultado->orderBy('movimiento.fecha', 'ASC')
                            ->select('movimiento.fecha','movimiento.id','movimiento.comentario as servicio2','movimiento.numero as recibo','movimiento.situacion as situacionentrega','movimiento.comentario as servicio','movimiento.fechaentrega','movimiento.id as servicio_id',DB::raw('1 as cantidad'),'movimiento.total as pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'paciente.bussinesname as paciente2',DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id',DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        $lista            = $resultado->get();
        //dd($lista);
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Recibo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Fecha Pago', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Usuario Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '2');
        
        $titulo_regularizar = 'Regularizar';
        $ruta             = $this->rutas;
        $user = Auth::user();
        $rs        = Caja::orderBy('nombre','ASC')->get();
        $band=false;
        foreach ($rs as $key => $value) {
            if($request->ip()==$value->ip && $value->id==3){
                $band=true;
            }
        }

        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, "Garantia");
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_regularizar', 'ruta', 'band', 'user'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad', 'band'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entidad          = 'GarantiaSoat';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboSituacion          = array("" => "Todos", "E" => "Pagado", "N" => "Pendiente");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboSituacion'));
    }

    public function store(Request $request){
        $id = $request->input('movimiento_id');
        $lista_servicios = $request->input('listServicio');
        // dd($lista_servicios);

        $error = DB::transaction(function() use($request, $id, $lista_servicios){ 
            if ($lista_servicios != '') {
                $lista = explode(',', $lista_servicios);
                foreach ($lista as $key => $value) {
                    // dd($request->input('txtPagado'.$value));
                     $det = new DetalleCirugiaParticular;
                     $det->movimiento_id = $id;
                     $det->doctor_id  = $request->input('txtIdMedico'.$value);
                     $user = Auth::user();
                     
                     $det->usuario_registro = $user->person_id;
                     $det->descripcion  = $request->input('txtServicio'.$value);
                     $det->cantidad  = $request->input('txtCantidad'.$value);
                     $det->monto  = $request->input('txtPrecio'.$value);   
                     $det->subTotal  = $request->input('txtTotal'.$value);   
                     $det->situacion = $request->input('txtPagado'.$value);

                     if ( $request->input('txtPagado'.$value) === 'S') {
                        $det->usuario_pago = $user->person_id; 
                        $det->fechaPago = date('Y-m-d H:i:s'); 
                     }

                     $det->save();
                }
                 
            }else{
                $dat[0]=array("respuesta"=>"ERROR","msg"=>"La Cirugia no contiene Detalles");
                return json_encode($dat);
            }
        });
        $dat[0]=array("respuesta"=>"OK");
        return is_null($error) ? json_encode($dat) : $error;
        // return 'Ok';
        // dd($id);
        // dd($request);
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

    public function regulariza($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id,$user){
            $Venta = Movimiento::find($id);
            $Venta->situacion='E';
            $Venta->fechaentrega = date("Y-m-d");
            $Venta->usuarioentrega_id = $user->person_id;
            $Venta->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function regularizar($id, $listarLuego)
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
        $entidad  = 'GarantiaSoat';
        $formData = array('route' => array('garantiasoat.regulariza', $id), 'method' => 'Regulariza', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Regularizar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }


    public function destroy(Request $request)
    {
        $id = $request->input("id");
        $comentarioa = $request->input("comentarioa");

        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id, $user, $comentarioa){
            $movimiento = Movimiento::find($id);
            $movimiento->fechaentrega = date("Y-m-d");
            $movimiento->usuarioentrega_id = $user->person_id;
            $movimiento->situacion = 'A';
            $movimiento->motivo_anul = $comentarioa;
            $movimiento->save();
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
        $entidad  = 'Garantia';
        $formData = array('route' => array('garantiasoat.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar2')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar','id'));
    }

    public function pdfReporte(Request $request){
        $nombre           = Libreria::getParam($request->input('doctor'));
        $paciente           = Libreria::getParam($request->input('paciente'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                ->join('person as medico','medico.id','=','movimiento.doctor_id')
                ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','movimiento.usuarioentrega_id')
                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                ->whereIn('movimiento.conceptopago_id',['150'])
                ->where('movimiento.situacion','!=','A')
                ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%');

        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
     
        $resultado        = $resultado->orderBy('movimiento.fecha', 'ASC')
                            ->select('movimiento.fecha','movimiento.id','movimiento.comentario as servicio2','movimiento.numero as recibo','movimiento.situacion as situacionentrega','movimiento.comentario as servicio','movimiento.fechaentrega','movimiento.id as servicio_id',DB::raw('1 as cantidad'),'movimiento.total as pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'paciente.bussinesname as paciente2',DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id',DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        $lista = $resultado->get();
        $pdf = new TCPDF();
        $pdf::SetTitle('Reporte de Dinero en Garantias de SOAT al '.($fechafinal));
        if (count($lista) > 0) {            
            $pdf::AddPage();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Reporte de Garantias por SOAT  al ".date("d/m/Y",strtotime($fechafinal))),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',7.5);
            $pdf::Cell(15,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(55,6,utf8_decode("PACIENTE"),1,0,'C');
            $pdf::Cell(55,6,utf8_decode("CONCEPTO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("RECIBO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("TOTAL"),1,0,'C');
            $pdf::Cell(25,6,utf8_decode("USUARIO"),1,0,'C');
            $pdf::Ln();
            $doctor="";$total=0;$totalgeneral=0;$idmedico=0;$array=array();
            foreach ($lista as $key => $value){
                if($doctor!=($value->medico)){
                    $pdf::SetFont('helvetica','B',7);
                    if($doctor!=""){
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(125,6,("EFECTIVO :"),1,0,'R');
                        $pdf::Cell(15,6,'',1,0,'L');
                        $pdf::Cell(15,6,number_format($total,2,'.',''),1,0,'C');
                        $totalgeneral = $totalgeneral + $total;
                        $total=0;
                        $pdf::Ln();
                    }
                    $doctor=($value->medico);
                    $idmedico=$value->medico_id;
                    $array[]=$idmedico;
                    $pdf::SetFont('helvetica','B',8);
                    $pdf::Cell(180,6,($doctor),1,0,'L');
                    $pdf::Ln();
                }
                $pdf::SetFont('helvetica','',7);
                $pdf::Cell(15,6,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                if($value->paciente!=""){
                    $pdf::Cell(55,6,($value->paciente),1,0,'L');
                }else{
                    $pdf::Cell(55,6,($value->paciente2),1,0,'L');
                }
                $pdf::Cell(55,6,($value->servicio2),1,0,'L');
                $pdf::Cell(15,6,$value->recibo,1,0,'L');
                $pdf::Cell(15,6,number_format($value->pagodoctor*$value->cantidad,2,'.',''),1,0,'C');
                $pdf::Cell(25,6,($value->responsable),1,0,'L');
                $total=$total + $value->pagodoctor*$value->cantidad;
                $pdf::Ln();                
            }
            
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(125,6,("EFECTIVO :"),1,0,'R');
            $pdf::Cell(15,6,(""),1,0,'R');
            $pdf::Cell(15,6,number_format($total,2,'.',''),1,0,'C');
            $totalgeneral = $totalgeneral + $total;
            $total=0;
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(140,6,("TOTAL GENERAL:"),1,0,'R');
            $pdf::Cell(15,6,number_format($totalgeneral,2,'.',''),1,0,'C');
            $pdf::Ln();
        }
        $pdf::Output('ReporteGarantia.pdf');
    }

    public function ExcelReporte(Request $request){
        $nombre           = Libreria::getParam($request->input('doctor'));
        $paciente         = Libreria::getParam($request->input('paciente'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        // $situacion        = Libreria::getParam($request->input('situacion'));

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                ->join('person as medico','medico.id','=','movimiento.doctor_id')
                ->leftjoin('person as usuarioentrega','usuarioentrega.id','=','movimiento.usuarioentrega_id')
                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                ->whereIn('movimiento.conceptopago_id',['150'])
                ->where('movimiento.situacion','!=','A')
                ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$nombre.'%')
                ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%');

        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        // if($situacion!=""){
        //     $resultado = $resultado->where('movimiento.situacion','LIKE',$situacion);
        // }
        $resultado        = $resultado->orderBy('movimiento.fecha', 'ASC')
                            ->select('movimiento.fecha','movimiento.id','movimiento.comentario as servicio2','movimiento.numero as recibo','movimiento.situacion as situacionentrega','movimiento.comentario as servicio','movimiento.fechaentrega','movimiento.id as servicio_id',DB::raw('1 as cantidad'),'movimiento.total as pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'paciente.bussinesname as paciente2',DB::raw('concat(usuarioentrega.apellidopaterno,\' \',usuarioentrega.apellidomaterno,\' \',usuarioentrega.nombres) as usuarioentrega'),'movimiento.tipomovimiento_id',DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        $lista = $resultado->get();
        
        Excel::create('ExcelSOAT', function($excel) use($lista,$request) {
 
            $excel->sheet('Garantias SOAT', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Concepto";
                $cabecera[] = "Nro Doc.";
                $cabecera[] = "Total";
                $cabecera[] = "Usuario";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;$total=0;$doctor="";$totalg=0;$idmedico=0;$array=array();
                foreach ($lista as $key => $value){
                    if($doctor!=$value->medico){
                        if($doctor!=""){
                            $detalle = array();
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "";
                            $detalle[] = "TOTAL";
                            $detalle[] = number_format($total,2,'.','');
                            $sheet->row($c,$detalle);
                            $totalg=$totalg+$total;
                            $c=$c+1;        
                        }
                        $detalle = array();
                        $detalle[] = $value->medico;
                        $sheet->row($c,$detalle);
                        $doctor=$value->medico;
                        $c=$c+1;    
                        $total=0;
                    }
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    if($value->paciente!=""){
                        $detalle[] = $value->paciente;
                    }else{
                        $detalle[] = $value->paciente2;
                    }
                    $detalle[] = $value->servicio2;
                    $detalle[] = $value->recibo;
                    $detalle[] = number_format($value->pagodoctor*$value->cantidad,2,'.','');
                    $detalle[] = $value->responsable;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                    $total=$total + $value->pagodoctor*$value->cantidad;
                }
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "TOTAL";
                $detalle[] = number_format($total,2,'.','');
                $sheet->row($c,$detalle);
                $c=$c+1;        
                $totalg=$totalg+$total;
                $total=0;
                $detalle = array();
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "";
                $detalle[] = "TOTAL GENERAL";
                $detalle[] = number_format($totalg,2,'.','');
                $sheet->row($c,$detalle);
                $c=$c+1;        
            });
        })->export('xls');
    }
}

