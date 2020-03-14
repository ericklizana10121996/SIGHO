<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Accidente;
use App\Rolpersona;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;
use App\Convenio;
USE Excel;

class aTransitoController extends Controller
{

    protected $folderview      = 'app.atransito';
    protected $tituloAdmin     = 'Accidente de Tránsito';
    protected $tituloRegistrar = 'Registrar Accidente de Tránsito';
    protected $tituloModificar = 'Modificar Accidente de Tránsito';
    protected $tituloEliminar  = 'Eliminar Accidente de Tránsito';
    protected $rutas           = array('create' => 'atransito.create', 
            'edit'   => 'atransito.edit', 
            'delete' => 'atransito.eliminar',
            'search' => 'atransito.buscar',
            'index'  => 'atransito.index',
            'pdfDocumento'  => 'atransito.pdfDocumento'
        );

     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function autocompletaranaquel($searching)
    {
        $entidad   = 'atransito';
        $resultado = Anaquel::where('descripcion', 'LIKE', '%'.strtoupper($searching).'%')->orderBy('descripcion', 'ASC');
        $lista     = $resultado->get();
        $data      = array();
        foreach ($lista as $key => $value) {
            $data[] = array(
                            'label' => $value->descripcion,
                            'id'    => $value->id,
                            'value' => $value->descripcion,
                        );
            
        }
        return json_encode($data);
    }

    /**
     * Mostrar el resultado de búsquedas
     * 
     * @return Response 
     */
    public function buscar(Request $request)
    {
        $fecha            = Libreria::getParam($request->input('fecha'));
        $fecfin            = Libreria::getParam($request->input('fecfin'));
        $paciente         = Libreria::getParam($request->input('paciente'));
        $placa            = Libreria::getParam($request->input('placa'));

        $filas            = $request->input('filas');
        $pagina           = $request->input('page');

        $entidad          = 'atransito';
        //$nombre           = Libreria::getParam($request->input('nombre'));
        $resultado        = Accidente::leftjoin('person as paciente','paciente.id','=','accidente.persona_id')->leftjoin('historia as h','h.person_id','=','paciente.id');

        if($fecha!=""){
            $resultado = $resultado->where('accidente.fecha','>=',$fecha);
        }
        if($fecfin!=""){
            $resultado = $resultado->where('accidente.fecha','<=',$fecfin);
        }
        if($paciente!=""){
            $resultado = $resultado->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%');
        }
        if($placa!=""){
            $resultado = $resultado->where('accidente.placa','=',$placa);
        }
        $resultado        = $resultado->orderBy('fecha', 'ASC')->orderBy('hora', 'ASC');

        $lista            = $resultado->select('accidente.*','h.numero as historia');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Referido', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Seguro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comisaría', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Chofer', 'numero' => '1');
        $cabecera[]       = array('valor' => 'DNI Chofer', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Placa', 'numero' => '1');
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta'));
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
        $entidad          = 'atransito';
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
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Accidente';
        $accidente = null;
        $cboTipoAccidente = Array('Choque'=>'Choque','Despiste'=>'Despiste','Atropello'=>'Atropello','Caida del Pasajero' => 'Caída del Pasajero');
        //$cboConvenio = Array(24=>'LA POSITIVA SEGUROS Y REASEGUROS',18=>'PROTECTA S.A. COMPAÑIA DE SEGUROS',3=>'MAPFRE',17=>'PACIFICO SEGUROS',23=>'RIMAC SEGUROS Y REASEGUROS',25=>'BNP PARIBAS CARDIF',21=>'INTERSEGURO SOAT');
        $convenios = Convenio::get();
        $cboConvenio = array();
        foreach ($convenios as $convenio) {
            $cboConvenio[$convenio->id] = $convenio->nombre;
        }
        $formData = array('atransito.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('accidente', 'cboTipoAccidente', 'cboConvenio', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $reglas     = array('paciente' => 'required|max:50');
        $mensajes = array(
            'paciente.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }

        $user = Auth::user();

        if ($request->input('person_id') != 0) {
            $error = DB::transaction(function() use($request, $user){
                $atransito       = new Accidente();
                $atransito->fecha = strtoupper($request->input('fecha'));
                $atransito->persona_id = strtoupper($request->input('person_id'));
                $atransito->tipoa = strtoupper($request->input('tipoa'));
                $atransito->referido = strtoupper($request->input('referido'));
                $atransito->chofer = strtoupper($request->input('chofer'));
                $atransito->edadc = strtoupper($request->input('edadc'));
                $atransito->dnic = strtoupper($request->input('dnic'));
                $atransito->telefonoc = strtoupper($request->input('telefonoc'));
                $atransito->lugar = strtoupper($request->input('lugar'));
                $atransito->hora = strtoupper($request->input('hora'));
                $atransito->placa = strtoupper($request->input('placa'));
                $atransito->comisaria = strtoupper($request->input('comisaria'));
                $atransito->codigollamada = strtoupper($request->input('codigollamada'));
                $atransito->convenio_id = strtoupper($request->input('convenio'));
                $atransito->soatn = strtoupper($request->input('soatn'));
                $atransito->autoriza = strtoupper($request->input('autoriza'));
                $atransito->usuario_id = $user->person_id;
                $atransito->save();
            });
        } else {
            $error = 'NO SE HA DEFINIDO PACIENTE';
        }
        
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
    public function edit(Request $request,$id)
    {
        $existe = Libreria::verificarExistencia($id, 'accidente');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $accidente = Accidente::find($id);
        $entidad  = 'Accidente';
        $cboTipoAccidente = Array('CHOQUE'=>'Choque','DESPISTE'=>'Despiste','ATROPELLO'=>'Atropello');
        //$cboConvenio = Array(24=>'LA POSITIVA SEGUROS Y REASEGUROS',18=>'PROTECTA S.A. COMPAÑIA DE SEGUROS',3=>'MAPFRE',17=>'PACIFICO SEGUROS',23=>'RIMAC SEGUROS Y REASEGUROS',25=>'BNP PARIBAS CARDIF',21=>'INTERSEGURO SOAT');
        $convenios = Convenio::get();
        $cboConvenio = array();
        foreach ($convenios as $convenio) {
            $cboConvenio[$convenio->id] = $convenio->nombre;
        }
        $formData = array('atransito.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('cboConvenio', 'accidente', 'cboTipoAccidente', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'accidente');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('paciente' => 'required|max:50');
        $mensajes = array(
            'paciente.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 

        $user = Auth::user();

        $error = DB::transaction(function() use($request, $id, $user){
            $atransito       = Accidente::find($id);
            $atransito->fecha = strtoupper($request->input('fecha'));
            $atransito->persona_id = strtoupper($request->input('person_id'));
            $atransito->tipoa = strtoupper($request->input('tipoa'));
            $atransito->referido = strtoupper($request->input('referido'));
            $atransito->chofer = strtoupper($request->input('chofer'));
            $atransito->edadc = strtoupper($request->input('edadc'));
            $atransito->dnic = strtoupper($request->input('dnic'));
            $atransito->telefonoc = strtoupper($request->input('telefonoc'));
            $atransito->lugar = strtoupper($request->input('lugar'));
            $atransito->hora = strtoupper($request->input('hora'));
            $atransito->placa = strtoupper($request->input('placa'));
            $atransito->comisaria = strtoupper($request->input('comisaria'));
            $atransito->codigollamada = strtoupper($request->input('codigollamada'));
            $atransito->convenio_id = strtoupper($request->input('convenio'));
            $atransito->soatn = strtoupper($request->input('soatn'));
            $atransito->autoriza = strtoupper($request->input('autoriza'));
            $atransito->usuario_id = $user->person_id;
            $atransito->save();
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
        $existe = Libreria::verificarExistencia($id, 'accidente');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $anaquel = Accidente::find($id);
            $anaquel->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'accidente');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Accidente::find($id);
        $entidad  = 'atransito';
        $mensaje = '¿Desea eliminar el accidente de '.$modelo->person->nombres.'? <br><br>';
        $formData = array('route' => array('atransito.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar','mensaje'));
    }

    public function pdfDocumento($id){
        $entidad          = 'atransito';
        
        $resultado        = Accidente::leftjoin('person as paciente', 'paciente.id', '=', 'accidente.persona_id')->leftjoin('historia','person_id','=','paciente.id')
                            ->where('accidente.id', '=', $id);
        $lista            = $resultado->select('accidente.*','paciente.*','historia.estadocivil','historia.ocupacion');
        $lista            = $resultado->get();
        if (count($lista) > 0) {            
            $pdf = new TCPDF();
            $pdf::SetTitle('Documento Accidente');
            $pdf::AddPage('P');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(135,10,utf8_decode("REPORTE DE SOAT"),0,0,'C');
            $iddoctorant=0;
            foreach ($lista as $key => $value){
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(15,10,utf8_decode("FECHA:"),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(10,10,$value->fecha,0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(72,5,('NOMBRES Y APELLIDOS DEL ACCIDENTADO: '),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(80,5,$value->apellidopaterno.' '.$value->apellidomaterno.' '.$value->nombres,0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(10,5,'DNI: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(60,5,utf8_decode($value->dni),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(25,5,'ESTADO CIVIL: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(40,5,utf8_decode($value->estadocivil),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(25,5,'OCUPACION: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(35,5,strtoupper(utf8_decode($value->ocupacion)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,5,'TELEFONO: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(50,5,$value->telefono,0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(40,5,'FECHA DE NACIMIENTO: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(25,5,$value->fechanacimiento,0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(15,5,'EDAD: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);

                list($Y,$m,$d) = explode("-",$value->fechanacimiento);
                $edadp = date("md") < $m.$d ? date("Y")-$Y-1 : date("Y")-$Y ;

                $pdf::Cell(45,5,$edadp,0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(25,5,'DOMICILIO: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(80,5,strtoupper(utf8_decode($value->direccion)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(27,5,'REFERIDO POR: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(80,5,strtoupper(utf8_decode($value->referido)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(38,5,'HORA DEL ACCIDENTE: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(32,5,$value->hora,0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(39,5,'LUGAR DEL ACCIDENTE: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(41,5,strtoupper(utf8_decode($value->lugar)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(15,5,'SOAT Nº: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(55,5,$value->soatn,0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(12,5,'PLACA: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(53,5,$value->placa,0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(15,5,'MOTIVO: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(45,5,strtoupper($value->tipoa),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(62,5,'NOMBRES Y APELLIDOS DEL CHOFER: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(80,5,strtoupper(utf8_decode($value->chofer)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(10,5,'DNI: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(60,5,utf8_decode($value->dnic),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(11,5,'EDAD: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(54,5,utf8_decode($value->edadc),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(25,5,'TELEFONO: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(35,5,$value->telefonoc,0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(47,5,'COMISARIA QUE INTERVIENE: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(60,5,$value->comisaria,0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(35,5,'CODIGO DE LLAMADA: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(35,5,$value->codigollamada,0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,5,'REPORTADO POR: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(35,5,strtoupper(utf8_decode($value->usuario->nombres)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,5,'AUTORIZADO POR: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(40,5,strtoupper(utf8_decode($value->autoriza)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(40,5,'COMPAÑIA DE SEGURO: ',0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(35,5,utf8_decode($value->convenio->nombre),0,0,'L');
            }
            $pdf::Output('pdfDocumento.pdf');
        }
    }

    public function excel(Request $request){
        $fecha           = Libreria::getParam($request->input('fecha'));
        $fecfin            = Libreria::getParam($request->input('fecfin'));
        $paciente     = Libreria::getParam($request->input('paciente'));
        $placa       = Libreria::getParam($request->input('placa'));

        $resultado        = Accidente::leftjoin('person as paciente','paciente.id','=','accidente.persona_id')
                            ->leftjoin('convenio','convenio.id','=','accidente.convenio_id')
                            ->leftjoin('person as usuario','usuario.id','=','accidente.usuario_id')
                            ->leftjoin('historia as h','paciente.id','=','h.person_id');
        if($fecha!=""){
            $resultado = $resultado->where('accidente.fecha','>=',$fecha);
        }
        if($fecfin!=""){
            $resultado = $resultado->where('accidente.fecha','<=',$fecfin);
        }
        if($paciente!=""){
            $resultado = $resultado->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%');
        }
        if($placa!=""){
            $resultado = $resultado->where('accidente.placa','=',$placa);
        }
        $resultado        = $resultado->orderBy('accidente.id', 'ASC')
                            ->select('accidente.fecha','accidente.tipoa','h.numero as historia',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'accidente.referido','accidente.hora','accidente.lugar','convenio.nombre as convenio','accidente.comisaria','accidente.chofer','accidente.dnic','accidente.edadc','accidente.telefonoc','accidente.placa','accidente.codigollamada','accidente.autoriza','usuario.nombres as usuario');

        $resultado            = $resultado->get();
        
        Excel::create('ExcelAccidentes', function($excel) use($resultado,$request) {
 
            $excel->sheet('Accidentes de Tránsito', function($sheet) use($resultado,$request) {
 
                $cabecera = array();
                $cabecera[] = "Nro";
                $cabecera[] = "Fecha";
                $cabecera[] = "Tipo";
                $cabecera[] = "Historia";
                $cabecera[] = "Paciente";
                $cabecera[] = "Referido";
                $cabecera[] = "Convenio";
                $cabecera[] = "Hora";
                $cabecera[] = "Lugar";
                $cabecera[] = "Comisaría";
                $cabecera[] = "Chofer";
                $cabecera[] = "DNI Chofer";
                $cabecera[] = "Edad Chofer";
                $cabecera[] = "Teléfono Chofer";
                $cabecera[] = "Placa";
                $cabecera[] = "Cod. Llamada";
                $cabecera[] = "Autoriza";
                $cabecera[] = "Usuario";
                $c=2;$d=3;$band=true;$numero=1;
                $sheet->row(1,$cabecera);

                foreach ($resultado as $key => $value){
                    $detalle = array();
                    $detalle[] = $numero;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->tipoa;
                    $detalle[] = $value->historia;
                    $detalle[] = $value->paciente;
                    $detalle[] = $value->referido;
                    $detalle[] = $value->convenio;
                    $detalle[] = $value->hora;
                    $detalle[] = $value->lugar;
                    $detalle[] = $value->comisaria;
                    $detalle[] = $value->chofer;
                    $detalle[] = $value->dnic;
                    $detalle[] = $value->edadc;
                    $detalle[] = $value->telefonoc;
                    $detalle[] = $value->placa;
                    $detalle[] = $value->codigollamada;
                    $detalle[] = $value->autoriza;
                    $detalle[] = $value->usuario;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }

            });
        })->export('xls');
    }
}