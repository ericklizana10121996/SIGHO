<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Convenio;
use App\Cita;
use App\Person;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;
use Excel;

class CitaController extends Controller
{
    protected $folderview      = 'app.cita';
    protected $tituloAdmin     = 'Cita';
    protected $tituloRegistrar = 'Registrar Cita';
    protected $tituloModificar = 'Modificar Cita';
    protected $tituloEliminar  = 'Eliminar Cita';
    protected $tituloAnular    = 'Anular Cita';
    protected $rutas           = array('create' => 'cita.create', 
            'edit'   => 'cita.edit', 
            'delete' => 'cita.eliminar',
            'anular' => 'cita.anular',
            'search' => 'cita.buscar',
            'marcador' => 'cita.marcador',
            'buscarboleta' => 'cita.buscarboleta',
            'buscarcita' => 'cita.buscarcita',
            'index'  => 'cita.index',
            'pdfListar'  => 'cita.pdfListar',
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
        $entidad          = 'Cita';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $doctor           = Libreria::getParam($request->input('doctor'),'');
        $fechaI           = Libreria::getParam($request->input('fechaI'));
        $fechaF           = Libreria::getParam($request->input('fechaF'));
        
        $resultado        = Cita::leftjoin('person as paciente', 'paciente.id', '=', 'cita.paciente_id')
                            ->join('person as doctor', 'doctor.id', '=', 'cita.doctor_id')
                            ->join('especialidad','especialidad.id','=','doctor.especialidad_id')
                            ->leftjoin('historia','historia.id','=','cita.historia_id')
                            ->where('cita.paciente', 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'), 'LIKE', '%'.strtoupper($doctor).'%');
        if($fechaI!="" && $fechaF == ""){
            $resultado = $resultado->where('cita.fecha', '>=', ''.$fechaI.'');
        }elseif($fechaI != "" && $fechaF != ""){
            $resultado = $resultado->whereBetween('cita.fecha', [$fechaI,$fechaF]);
            
        }
        $resultado        = $resultado->select('cita.*','historia.tipopaciente as tipopaciente2','especialidad.nombre as especialidad','historia.numero as historia1',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente1'),DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres) as doctor'))->orderBy('cita.fecha', 'ASC')->orderBy('cita.horainicio','ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Especialidad', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Telef.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Hora Inicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Hora Fin', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Concepto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Modifica', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Atendio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Ficha llena', 'numero' => '1');
        $cabecera[]       = array('valor' => 'SOAT', 'numero' => '1');
        $cabecera[]       = array('valor' => 'SCTR', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Ticket', 'numero' => '1');
        
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_anular    = $this->tituloAnular;
        $ruta             = $this->rutas;
        $user             = Auth::user();
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'titulo_anular', 'user'));
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
        $entidad          = 'Cita';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Cita';
        $cita = null;
        $cboConvenio = array();
        $convenios = Convenio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($convenios as $key => $value) {
            $cboConvenio = $cboConvenio + array($value->id => $value->nombre);
        }
        $formData            = array('cita.store');
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('cita', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente', 'cboConvenio'));
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
                );
        $mensajes = array(
            'doctor.required'         => 'Debe seleccionar un doctor',
            'especialidad.required'         => 'Debe seleccionar una especialidad',
            'paciente.required'         => 'Debe ingresar un paciente',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       

        $user = Auth::user();
        $error = DB::transaction(function() use($request,$user){
            $Cita       = new Cita();
            $Cita->fecha = $request->input('fecha');
            $person_id = $request->input('person_id');
            if($person_id==""){
                $person_id = null;
            }else{
                if(trim($request->input('telefono'))!=''){
                    $person = Person::find($person_id);
                    $person->telefono = $request->input('telefono');
                    $person->save();
                }
            }
            $historia_id = $request->input('historia_id');
            if($historia_id==""){
                $historia_id = null;
            }
            $Cita->paciente_id = $person_id;
            $Cita->historia_id = $historia_id;
            $Cita->doctor_id = $request->input('doctor_id');
            $Cita->situacion='P';//Pendiente
            $Cita->horainicio = $request->input('horainicio');
            $Cita->horafin = $request->input('horafin');
            $Cita->comentario = $request->input('comentario');
            $Cita->telefono = $request->input('telefono');
            $Cita->paciente = $request->input('paciente');
            $Cita->historia = $request->input('numero');
            $Cita->tipopaciente = $request->input('tipopaciente');
            $Cita->movimiento_id = $request->input('movimiento_id');
            $Cita->usuario_id = $user->person_id;
            $Cita->save();
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
        $existe = Libreria::verificarExistencia($id, 'Cita');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $cita = Cita::find($id);
        $entidad             = 'Cita';
        $formData            = array('cita.update', $id);
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('cita', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente'));
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
        $existe = Libreria::verificarExistencia($id, 'cita');
        if ($existe !== true) {
            return $existe;
        }
         $reglas     = array(
                'doctor'                  => 'required',
                'especialidad'          => 'required',
                'paciente'          => 'required',
                );
        $mensajes = array(
            'doctor.required'         => 'Debe seleccionar un doctor',
            'especialidad.required'         => 'Debe seleccionar una especialidad',
            'paciente.required'         => 'Debe ingresar un paciente',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $id, $user){
            $Cita = Cita::find($id);
            $Cita->fecha = $request->input('fecha');
            $person_id = $request->input('person_id');
            if($person_id==""){
                $person_id = null;
            }
            $historia_id = $request->input('historia_id');
            if($historia_id==""){
                $historia_id = null;
            }
            $Cita->paciente_id = $person_id;
            $Cita->historia_id = $historia_id;
            $Cita->doctor_id = $request->input('doctor_id');
            $Cita->situacion='P';//Pendiente
            $Cita->horainicio = $request->input('horainicio');
            $Cita->horafin = $request->input('horafin');
            $Cita->comentario = $request->input('comentario');
            $Cita->telefono = $request->input('telefono');
            $Cita->paciente = $request->input('paciente');
            $Cita->historia = $request->input('numero');
            $Cita->tipopaciente = $request->input('tipopaciente');
            $Cita->movimiento_id = $request->input('movimiento_id');
            $Cita->usuario2_id = $user->person_id;
            $Cita->save();
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
        $existe = Libreria::verificarExistencia($id, 'cita');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Cita = Cita::find($id);
            $Cita->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function destroy02($id, Request $request)
    {
        $reglas     = array(
            'motivo'                  => 'required'
        );

        $mensajes = array(
            'motivo.required'         => 'Debe ingresar un motivo'
        );

        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }

        // dd('dhejdleñ');


        $existe = Libreria::verificarExistencia($id, 'cita');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id,$request){
            $Cita = Cita::find($id);
            $Cita->motivoAnulacion = $request->get('motivo');
            $Cita->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'cita');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Cita::find($id);
        $entidad  = 'Cita';
        $formData = array('route' => array('cita.destroy02', $id), 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';

        return view($this->folderview.'.anular')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function anula($id, Request $request)
    {
        $reglas     = array(
            'motivo'                  => 'required'
        );

        $mensajes = array(
            'motivo.required'         => 'Debe ingresar un motivo'
        );

        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }

        $existe = Libreria::verificarExistencia($id, 'cita');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id, $user,$request){
            $Cita = Cita::find($id);
            $Cita->situacion='A';
            $Cita->anulacion_id=$user->person_id;
            $Cita->motivoAnulacion = $request->get('motivo');

            $Cita->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function anular($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'cita');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Cita::find($id);
        $entidad  = 'Cita';
        $formData = array('route' => array('cita.anula', $id), 'method' => 'Anular', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view($this->folderview.'.anular')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function validarDNI(Request $request)
    {
        $dni = $request->input("dni");
        $entidad    = 'Person';
        $mdlPerson = new Person();
        $resultado = Person::where('dni','LIKE',$dni);
        $value     = $resultado->first();
        if(count($value)>0){
            $objCita = new Cita();
            $list2       = Cita::where('person_id','=',$value->id)->first();
            if(count($list2)>0){//SI TIENE Cita
                $data[] = array(
                            'apellidopaterno' => $value->apellidopaterno,
                            'apellidomaterno' => $value->apellidomaterno,
                            'nombres' => $value->nombres,
                            'telefono' => $value->telefono,
                            'direccion' => $value->direccion,
                            'id'    => $value->id,
                            'msg' => 'N',
                        );
            }else{//NO TIENE Cita PERO SI ESTA REGISTRADO LA PERSONA COMO PROVEEDOR O PERSONAL
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

    public function marcador(Request $request)
    {
        $user = Auth::user();
        $error = DB::transaction(function() use($request,$user){
            $idcita = $request->input('idcita');
            $valor = $request->input('valor');
            if($valor=="true"){
                $valor = "1";
            }else{
                $valor = "0";
            }
            $campo = $request->input('campo');
            $Cita       = Cita::find($idcita);
            if($campo == "atendio"){
                $Cita->atendio = $valor;
                $Cita->usuario_atendio = $user->id;
            }elseif($campo == "ficha"){
                $Cita->ficha = $valor;
                $Cita->usuario_ficha = $user->id;
            }elseif($campo == "soat"){
                $Cita->soat = $valor;
                $Cita->usuario_soat = $user->id;   
            }elseif($campo == "sctr"){
                $Cita->sctr = $valor;
                $Cita->usuario_sctr = $user->id;         
            }
            $Cita->save();
        });
        return is_null($error) ? "OK" : $error;
    }

   	public function pdfListar(Request $request){
        $entidad          = 'Cita';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $doctor           = Libreria::getParam($request->input('doctor'),'');
        $fechaI           = Libreria::getParam($request->input('fechaI'));
        $fechaF           = Libreria::getParam($request->input('fechaF'));
        
        $resultado        = Cita::leftjoin('person as paciente', 'paciente.id', '=', 'cita.paciente_id')
                            ->join('person as doctor', 'doctor.id', '=', 'cita.doctor_id')
                            ->join('person as usuario', 'usuario.id', '=', 'cita.usuario_id')
                            ->join('especialidad','especialidad.id','=','doctor.especialidad_id')
                            ->leftjoin('historia','historia.id','=','cita.historia_id')
                            ->where('cita.paciente', 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where('cita.situacion', '<>', 'A')
                            ->where(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'), 'LIKE', '%'.strtoupper($doctor).'%');
        if($fechaI!="" && $fechaF == ""){
            $resultado = $resultado->where('cita.fecha', '>=', ''.$fecha.'');
        }else{
            if ($fechaI != "" && $fechaF != "") {
                  $resultado = $resultado->whereBetween('cita.fecha', [$fechaI,$fechaF]);
            }
        }
        $resultado        = $resultado->select('cita.*','historia.tipopaciente as tipopaciente2','especialidad.nombre as especialidad','usuario.nombres as usuario2','historia.numero as historia2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres) as doctor'))->orderBy('cita.fecha', 'ASC')->orderBy(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'),'asc')->orderBy('cita.horainicio','ASC');
        $lista            = $resultado->get();
        if (count($lista) > 0) {            
            $pdf = new TCPDF();
            $pdf::SetTitle('Lista de Pacientes');
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(0,10,utf8_decode("LISTA DE CITAS"),0,0,'C');
            $pdf::Ln();
            $iddoctorant=0;
            foreach ($lista as $key => $value){
                if($iddoctorant!=$value->doctor_id){
                    if($iddoctorant>0){
                        $pdf::Ln();
                    }

                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(15,9,utf8_decode("FECHA:"),0,0,'L');
                    $pdf::SetFont('helvetica','',10);
                    $pdf::Cell(20,9,utf8_decode($value->fecha),0,0,'L');
                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(18,9,utf8_decode("DOCTOR:"),0,0,'L');
                    $pdf::SetFont('helvetica','',10);
                    $pdf::Cell(90,9,($value->doctor),0,0,'L');
                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(30,9,utf8_decode("ESPECIALIDAD:"),0,0,'L');
                    $pdf::SetFont('helvetica','',10);
                    $pdf::Cell(0,9,utf8_decode($value->especialidad),0,0,'L');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(8,6,utf8_decode("Nro"),1,0,'C');
                    $pdf::Cell(70,6,utf8_decode("PACIENTE"),1,0,'C');
                    $pdf::Cell(18,6,utf8_decode("TIPO PAC."),1,0,'C');
                    $pdf::Cell(23,6,utf8_decode("TELEF."),1,0,'C');
                    $pdf::Cell(18,6,utf8_decode("HISTORIA"),1,0,'C');
                    $pdf::Cell(13,6,utf8_decode("INICIO"),1,0,'C');
                    $pdf::Cell(13,6,utf8_decode("FIN"),1,0,'C');
                    $pdf::Cell(49,6,utf8_decode("CONCEPTO"),1,0,'C');
                    $pdf::Cell(30,6,utf8_decode("FECHA CREAC."),1,0,'C');
                    $pdf::Cell(25,6,utf8_decode("USUARIO"),1,0,'C');
                    $pdf::Cell(8,6,utf8_decode("ATE"),1,0,'C');
                    $pdf::Cell(8,6,utf8_decode("FIC"),1,0,'C');
                    $pdf::Ln();
                    $iddoctorant=$value->doctor_id;
                    $c=1;
                }
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(8,5,($c),1,0,'L');
                $pdf::Cell(70,5,($value->paciente),1,0,'L');
                $pdf::Cell(18,5,utf8_decode($value->tipopaciente),1,0,'C');
                $pdf::Cell(23,5,utf8_decode($value->telefono),1,0,'C');
                $pdf::Cell(18,5,utf8_decode($value->historia),1,0,'C');
                $pdf::Cell(13,5,utf8_decode(substr($value->horainicio,0,5)),1,0,'C');
                $pdf::Cell(13,5,utf8_decode(substr($value->horafin,0,5)),1,0,'C');
                if(strlen($value->comentario)>28){
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();
                    $pdf::Multicell(49,2,utf8_decode($value->comentario),1,'L');
                    $pdf::SetXY($x+49,$y);
                }else{
                    $pdf::Cell(49,5,utf8_decode($value->comentario),1,0,'L');
                }
                $pdf::Cell(30,5,utf8_decode(date("d/m/Y H:i:s",strtotime($value->created_at))),1,0,'C');
                $pdf::Cell(25,5,substr(utf8_decode($value->usuario2),0,10),1,0,'C');
                if($value->atendio>0){
                    $pdf::Cell(8,5,utf8_decode("SI"),1,0,'C');
                }else{
                    $pdf::Cell(8,5,utf8_decode("NO"),1,0,'C');
                }
                if($value->ficha>0){
                    $pdf::Cell(8,5,utf8_decode("SI"),1,0,'C');
                }else{
                    $pdf::Cell(8,5,utf8_decode("NO"),1,0,'C');
                }
                $pdf::Ln();
                $c=$c+1;
            }
            $pdf::Output('ListaCita.pdf');
        }
    }

    public function cargarCitaMedico(Request $request){
        $resultado        = Cita::leftjoin('person as paciente', 'paciente.id', '=', 'cita.paciente_id')
                            ->join('person as doctor', 'doctor.id', '=', 'cita.doctor_id')
                            ->join('especialidad','especialidad.id','=','doctor.especialidad_id')
                            ->leftjoin('historia','historia.id','=','cita.historia_id')
                            ->where('cita.doctor_id', '=', $request->input('idmedico'))
                            ->where('cita.situacion','<>','A');
        if($request->input('fecha')!=""){
            $resultado = $resultado->where('cita.fecha', '=', ''.$request->input('fecha').'');
        }
        $resultado        = $resultado->select('cita.*','historia.tipopaciente as tipopaciente2','especialidad.nombre as especialidad','historia.numero as historia1',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente1'),DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres) as doctor'))->orderBy('cita.fecha', 'ASC')->orderBy('cita.horainicio','ASC');
        $lista            = $resultado->get();
        if(count($lista) == 0){
            $registro="";
        }else{
            $registro="<table class='table table-bordered table-striped table-condensed table-hover'>
                        <thead>
                            <tr>
                                <th class='text-center' style='font-size:12px'>Nro.</th>
                                <th class='text-center' style='font-size:12px'>Paciente</th>
                                <th class='text-center' style='font-size:12px'>Tipo Pac.</th>
                                <th class='text-center' style='font-size:12px'>Inicio</th>
                                <th class='text-center' style='font-size:12px'>Fin</th>
                            </tr>
                        </thead>
                        <tbody>";
            $c=0;
            foreach ($lista as $key => $value){$c=$c+1;
                $registro.="<tr>";
                $registro.="<td style='font-size:12px'>".$c."</td>";
                $registro.="<td style='font-size:12px'>".$value->paciente."</td>";
                $registro.="<td style='font-size:12px'>".$value->tipopaciente."</td>";
                $registro.="<td style='font-size:12px'>".substr($value->horainicio,0,5)."</td>";
                $registro.="<td style='font-size:12px'>".substr($value->horafin,0,5)."</td>";
                $registro.="</tr>";
            }
            $registro.="</tbody></table>";
        }
        echo $registro;
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $fechaI       = Libreria::getParam($request->input('fechaI'));
        $fechaF       = Libreria::getParam($request->input('fechaF'));
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $doctor           = Libreria::getParam($request->input('doctor'),'');

        $resultado        = Cita::leftjoin('person as paciente', 'paciente.id', '=', 'cita.paciente_id')
                            ->join('person as doctor', 'doctor.id', '=', 'cita.doctor_id')
                            ->join('especialidad','especialidad.id','=','doctor.especialidad_id')
                            ->leftjoin('historia','historia.id','=','cita.historia_id')
                            ->where('cita.paciente', 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'), 'LIKE', '%'.strtoupper($doctor).'%');
        // if($fecha!=""){
        //     $resultado = $resultado->where('cita.fecha', '=', ''.$fecha.'');
        // }
        if($fechaI!="" && $fechaF == ""){
            $resultado = $resultado->where('cita.fecha', '>=', ''.$fechaI.'');
        }elseif($fechaI != "" && $fechaF != ""){
            $resultado = $resultado->whereBetween('cita.fecha', [$fechaI,$fechaF]);
            
        }
        
        $resultado        = $resultado->select('cita.*','historia.tipopaciente as tipopaciente2','especialidad.nombre as especialidad','historia.numero as historia1',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente1'),DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres) as doctor'))->orderBy('cita.fecha', 'ASC')->orderBy('cita.horainicio','ASC');
        $lista            = $resultado->get();


        Excel::create('ExcelCita', function($excel) use($lista,$request) {
 
            $excel->sheet('Cita', function($sheet) use($lista,$request) {
 
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Doctor";
                $cabecera[] = "Especialidad";
                $cabecera[] = "Tipo Paciente";
                $cabecera[] = "Paciente";
                $cabecera[] = "Telefono";
                $cabecera[] = "Historia";
                $cabecera[] = "Hora Inicio";
                $cabecera[] = "Hora Fin";
                $cabecera[] = "Concepto";
                //$cabecera[] = "Usuario";
                $cabecera[] = "Se atendio";
                $cabecera[] = "Lleno ficha";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;$doctor="";$idmedico=0;$nombre="";

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date("d/m/Y",strtotime($value->fecha));
                    $detalle[] = $value->doctor;
                    $detalle[] = $value->especialidad;
                    $detalle[] = $value->tipopaciente;
                    $detalle[] = $value->paciente;
                    $detalle[] = $value->telefono;
                    $detalle[] = $value->historia;
                    $detalle[] = substr($value->horainicio,0,5);
                    $detalle[] = substr($value->horafin,0,5);
                    $detalle[] = $value->comentario;
                    //$detalle[] = $value->usuario->nombres;
                    if($value->atendio>0){
                        $detalle[] = "SI";
                    }else{
                        $detalle[] = "NO";
                    }
                    if($value->ficha>0){
                        $detalle[] = "SI";
                    }else{
                        $detalle[] = "NO";
                    }
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
            });
        })->export('xls');
    }

    public function buscarboleta(Request $request)
    {
        $user = Auth::user();
        $numero = $request->input('numero');
        $select = DB::select("SELECT mv.id, mv.persona_id, CONCAT_WS(' ',ps.apellidopaterno,ps.apellidomaterno,ps.nombres) AS paciente, CONCAT_WS('-', mv.serie,mv.numero) AS boleta FROM movimiento mv INNER JOIN person ps ON ps.id = mv.persona_id WHERE tipomovimiento_id = 4 AND tipodocumento_id IN (4,5) AND CONCAT_WS('-', mv.serie,mv.numero) = ? LIMIT 1",array($numero));
        if(count($select)>0){
            $select = $select[0];
        }else{
            $select = array("boleta"=>"","id"=>"","persona_id"=>"");
        }
        /*$error = DB::transaction(function() use($request,$user){
            $idcita = $request->input('idcita');
            $valor = $request->input('valor');
            if($valor=="true"){
                $valor = "1";
            }else{
                $valor = "0";
            }
            $campo = $request->input('campo');
            $Cita       = Cita::find($idcita);
            if($campo == "atendio"){
                $Cita->atendio = $valor;
                $Cita->usuario_atendio = $user->id;
            }elseif($campo == "ficha"){
                $Cita->ficha = $valor;
                $Cita->usuario_ficha = $user->id;
            }
            $Cita->save();
        });
        return is_null($error) ? "OK" : $error;*/
        return json_encode($select);
    }

    public function buscarcita(Request $request)
    {
        $user = Auth::user();
        $idpersona = $request->input('idpersona');
        $fecha = $request->input('fecha');
        $select = DB::select("SELECT ct.id AS numero, ct.id FROM cita ct INNER JOIN historia hs ON hs.id = ct.historia_id WHERE (ct.paciente_id = ? OR hs.person_id = ?) AND ct.situacion <> 'A' AND ct.fecha = ? LIMIT 1",array($idpersona,$idpersona,$fecha));
        if(count($select)>0){
            $select = $select[0];
        }else{
            $select = array("numero"=>"","id"=>"0");
        }
        /*$error = DB::transaction(function() use($request,$user){
            $idcita = $request->input('idcita');
            $valor = $request->input('valor');
            if($valor=="true"){
                $valor = "1";
            }else{
                $valor = "0";
            }
            $campo = $request->input('campo');
            $Cita       = Cita::find($idcita);
            if($campo == "atendio"){
                $Cita->atendio = $valor;
                $Cita->usuario_atendio = $user->id;
            }elseif($campo == "ficha"){
                $Cita->ficha = $valor;
                $Cita->usuario_ficha = $user->id;
            }
            $Cita->save();
        });
        return is_null($error) ? "OK" : $error;*/
        return json_encode($select);
    }

}
