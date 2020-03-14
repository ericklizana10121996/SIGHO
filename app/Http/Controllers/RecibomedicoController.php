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
use App\Caja;
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

class RecibomedicoController extends Controller
{
    protected $folderview      = 'app.recibomedico';
    protected $tituloAdmin     = 'Recibo Blanco';
    protected $tituloRegistrar = 'Registrar Recibo Blanco';
    protected $tituloModificar = 'Modificar Recibo Blanco';
    protected $tituloEliminar  = 'Eliminar Recibo Blanco';
    protected $rutas           = array('create' => 'recibomedico.create', 
            'edit'   => 'recibomedico.edit', 
            'delete' => 'recibomedico.eliminar',
            'search' => 'recibomedico.buscar',
            'index'  => 'recibomedico.index',
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
        $entidad          = 'Reciboblanco';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fecha            = Libreria::getParam($request->input('fecha'));
        $user = Auth::user();
        if($request->input('usuario')=="Todos"){
            $responsable_id=0;
        }else{
            $responsable_id=$user->person_id;
        }
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where('movimiento.numero','LIKE','%'.$numero.'%')->where('movimiento.tipodocumento_id','=','18');
        if($fecha!=""){
            $resultado = $resultado->where('movimiento.fecha', '=', ''.$fecha.'');
        }
        if($responsable_id>0){
            $resultado = $resultado->where('movimiento.responsable_id', '=', $responsable_id);   
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('case when movimiento.persona_id>1 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente'),DB::raw('concat(responsable.apellidopaterno,\' \',responsable.apellidomaterno,\' \',responsable.nombres) as responsable'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Concepto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
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
        $entidad          = 'Reciboblanco';
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
        $entidad             = 'Reciboblanco';
        $reciboblanco = null;
        $formData            = array('recibomedico.store');
        $numero = Movimiento::NumeroSigue(10,18);
        $cboTipoPaciente     = array("Convenio" => "Convenio", "Particular" => "Particular", "Hospital" => "Hospital");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('reciboblanco', 'formData', 'entidad', 'boton', 'listar', 'numero', 'cboTipoPaciente'));
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
                );
        $mensajes = array(
            'fecha.required'         => 'Debe seleccionar una fecha',
            'numero.required'         => 'El recibo debe tener un numero',
            'paciente.required'         => 'Debe seleccionar una persona',
            'total.required'         => 'Debe agregar total al recibo',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       
        
        $user = Auth::user();
        $dat=array();
        $error = DB::transaction(function() use($request,$user,&$dat){
            $Ticket       = new Movimiento();
            $Ticket->fecha = $request->input('fecha');
            $Ticket->numero = $request->input('numero');
            $Ticket->subtotal = 0;
            $Ticket->igv = 0;
            $Ticket->total = $request->input('total');
            $Ticket->tipomovimiento_id=10;
            $Ticket->tipodocumento_id=18;
            $Ticket->persona_id = $request->input('person_id');
            $Ticket->doctor_id = $request->input('doctor_id');
            // $Ticket->responsableGarantia = $request->input('responsable');
            $Ticket->situacion='N';
            $Ticket->comentario = $request->input('concepto');
            $Ticket->nombrepaciente = $request->input('paciente');
            $Ticket->responsable_id=$user->person_id;            
            $Ticket->save();
            
            $dat[0]=array("respuesta"=>"OK","id"=>$Ticket->id);
        });
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
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $reciboblanco = Movimiento::find($id);
        $numero              = str_pad($reciboblanco->numero,8,'0',STR_PAD_LEFT);
        $entidad             = 'Reciboblanco';
        $formData            = array('recibomedico.update', $id);
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio");
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('reciboblanco', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente', 'numero'));
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
                );
        $mensajes = array(
            'fecha.required'         => 'Debe seleccionar una fecha',
            'numero.required'         => 'El recibo debe tener un numero',
            'paciente.required'         => 'Debe seleccionar un paciente',
            'total.required'         => 'Debe agregar total al recibo',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $dat = array();
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $id, $user, &$dat){
            $Ticket = Movimiento::find($id);
            $Ticket->fecha = $request->input('fecha');
            $Ticket->numero = $request->input('numero');
            $Ticket->subtotal = 0;
            $Ticket->igv = 0;
            $Ticket->total = $request->input('total');
            $Ticket->tipomovimiento_id=10;
            $Ticket->tipodocumento_id=18;
            $Ticket->persona_id = $request->input('person_id');
            $Ticket->doctor_id = $request->input('doctor_id');
            $Ticket->situacion='N';
            $Ticket->comentario = $request->input('concepto');
            $Ticket->nombrepaciente = $request->input('paciente');
            $Ticket->responsable_id=$user->person_id;            
            $Ticket->save();
            
            $dat[0]=array("respuesta"=>"OK","id"=>$Ticket->id);
        });
        return is_null($error) ? json_encode($dat) : $error;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->input("id");
        $comentarioa = $request->input("comentarioa");
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id, $comentarioa){
            $Ticket = Movimiento::find($id);
            $Ticket->motivo_anul = $comentarioa;
            $Ticket->save();
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
        $entidad  = 'Reciboblanco';
        $formData = array('route' => array('recibomedico.destroy'), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar2')->with(compact('id', 'modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function pdfRecibo(Request $request){
        $lista = Movimiento::where('id','=',$request->input('id'))->first();
                    
        $pdf = new TCPDF();
        $pdf::SetTitle('Recibo');
        $pdf::AddPage();
        $pdf::SetFont('helvetica','B',10);
        $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 35, 10);
        $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 105, 0, 35, 10);
        $pdf::Cell(50,10,utf8_decode("Recibo Nro. ".$lista->numero),0,0,'C');
        $pdf::Cell(50,7,utf8_decode(""),0,0,'L');
        $pdf::Cell(50,10,utf8_decode("Recibo Nro. ".$lista->numero),0,0,'C');
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
        $pdf::Cell(20,7,utf8_decode("PERSONA :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        if(!is_null($lista->persona_id) && $lista->persona_id>1){
            $pdf::Cell(80,7,utf8_decode($lista->persona->apellidopaterno." ".$lista->persona->apellidomaterno." ".$lista->persona->nombres),0,0,'L');
        }else{
            $pdf::Cell(80,7,utf8_decode($lista->nombrepaciente),0,0,'L');
        }
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(20,7,utf8_decode("PERSONA :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        if(!is_null($lista->persona_id) && $lista->persona_id>1){
            $pdf::Cell(30,7,utf8_decode($lista->persona->apellidopaterno." ".$lista->persona->apellidomaterno." ".$lista->persona->nombres),0,0,'L');
        }else{
            $pdf::Cell(30,7,utf8_decode($lista->nombrepaciente),0,0,'L');
        }
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(20,7,utf8_decode("CONCEPTO :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(30,7,utf8_decode($lista->comentario),0,0,'L');
        $pdf::Cell(50,7,utf8_decode(""),0,0,'L');
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(20,7,utf8_decode("CONCEPTO :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(30,7,utf8_decode($lista->comentario),0,0,'L');
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
        $pdf::Cell(20,7,utf8_decode("USUARIO :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(80,7,($lista->responsable->nombres),0,0,'L');
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(20,7,utf8_decode("USUARIO :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(80,7,($lista->responsable->nombres),0,0,'L');
        $pdf::Output('ReciboBlanco.pdf');
    }
    
    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $fecha       = Libreria::getParam($request->input('fecha'));
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where('movimiento.tipodocumento_id','=','18');
        if($fecha!=""){
            $resultado = $resultado->where('movimiento.fecha', '=', ''.$fecha.'');
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('case when movimiento.persona_id>1 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente'),DB::raw('concat(responsable.apellidopaterno,\' \',responsable.apellidomaterno,\' \',responsable.nombres) as responsable'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        $lista            = $resultado->get();

        Excel::create('ExcelReciboBlanco', function($excel) use($lista,$request) {
 
            $excel->sheet('ReciboBlanco', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Nro";
                $cabecera[] = "Persona";
                $cabecera[] = "Doctor";
                $cabecera[] = "Concepto";
                $cabecera[] = "Total";
                $cabecera[] = "Usuario";
                $array[] = $cabecera;
                $c=2;$d=3;$band=true;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->numero;
                    $detalle[] = $value->paciente;
                    $detalle[] = $value->doctor->apellidopaterno.' '.$value->doctor->apellidomaterno.' '.$value->doctor->nombres;
                    $detalle[] = $value->comentario;
                    $detalle[] = number_format($value->total,2,'.','');
                    $detalle[] = $value->responsable;
                    $array[] = $detalle;
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }
}
