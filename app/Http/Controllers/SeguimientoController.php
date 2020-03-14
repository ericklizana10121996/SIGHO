<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Seguimiento;
use App\Area;
use App\Person;
use App\User;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SeguimientoController extends Controller
{
    protected $folderview      = 'app.seguimiento';
    protected $tituloAdmin     = 'Solicitud Historia';
    protected $tituloRegistrar = 'Registrar Solicitud';
    protected $tituloModificar = 'Modificar Solicitud';
    protected $tituloEliminar  = 'Eliminar Solicitud';
    protected $rutas           = array('create' => 'seguimiento.create', 
            'edit'   => 'seguimiento.edit', 
            'aceptar' => 'seguimiento.aceptar',
            'rechazar' => 'seguimiento.rechazar',
            'retornar' => 'seguimiento.retornar',
            'search' => 'seguimiento.buscar',
            'index'  => 'seguimiento.index',
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Seguimiento';
        $area           = Libreria::getParam($request->input('area'),'0');
        $historia         = Libreria::getParam($request->input('historia'));
        $paciente         = Libreria::getParam($request->input('nombre'));
        $situacion         = Libreria::getParam($request->input('situacion'));
        $resultado        = Seguimiento::join('historia', 'historia.id', '=', 'seguimiento.historia_id')
                            ->join('person', 'person.id', '=', 'historia.person_id')
                            ->join('person as responsable','responsable.id','=','seguimiento.personaenvio_id')
                            ->where(DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where('historia.numero','LIKE','%'.$historia.'%');
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('fechaenvio','>=',$request->input('fechainicial').' 00:00:00');
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('fechaenvio','<=',$request->input('fechafinal').' 23:59:59');
        }
        if($situacion!="")
            $resultado = $resultado->where('seguimiento.situacion','LIKE',$situacion);
        if($area!="0"){
            $resultado = $resultado->where('seguimiento.areaenvio_id', '=', $area)->orWhere('seguimiento.areadestino_id','=',$area);
        }
        $resultado        = $resultado->select('Seguimiento.*',DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as paciente2'),'responsable.nombres as personaenvio2')->orderBy('Seguimiento.fechaenvio', 'desc');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Solicitud', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Area Solicitante', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '2');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta','user'));
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
        $entidad          = 'Seguimiento';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboArea = array("0" => "--Todos--");
        $area = Area::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($area as $key => $value) {
            $cboArea = $cboArea + array($value->id => $value->nombre);
        }
        $cboSituacion = array('' => 'Todos','E' => 'Pendiente', 'A' => 'Aceptado');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboArea','cboSituacion'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $modo              = $request->input('modo','');
        $entidad             = 'Seguimiento';
        $seguimiento = null;
        $cboArea = array();
        $area = Area::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($area as $key => $value) {
            $cboArea = $cboArea + array($value->id => $value->nombre);
        }
        $formData            = array('seguimiento.store');
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $cboModo             = array("F" => "Fisico", "V" => "Virtual");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('seguimiento', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente', 'cboArea'));
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
        $modo     = $request->input('modo','');
        $reglas     = array(
                'paciente'                  => 'required',
                'numero'                  => 'required',
                );
        $mensajes = array(
            'paciente.required'         => 'Debe ingresar un paciente',
            'numero.required'         => 'Debe seleccionar un paciente',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        
        $user = Auth::user();
        $error = DB::transaction(function() use($request,$user){
            $Seguimiento       = new Seguimiento();
            $Seguimiento->historia_id = $request->input('historia_id');
            $Seguimiento->personaenvio_id = $user->person_id;
            $Seguimiento->areaenvio_id=$request->input('areaenvio');
            $Seguimiento->areadestino_id=$request->input('areadestino');
            $Seguimiento->fechaenvio=date("Y-m-d H:i:s");
            $Seguimiento->situacion="E";
            $Seguimiento->comentario=$request->input('comentario');
            $Seguimiento->save();            
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
        $existe = Libreria::verificarExistencia($id, 'Seguimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $modo              = $request->input('modo','');
        $Seguimiento = Seguimiento::join('person','person.id','=','Seguimiento.person_id')->where('Seguimiento.id','=',$id)->select('Seguimiento.*')->select('person.*','Seguimiento.*')->first();
        $entidad             = 'Seguimiento';
                $cboConvenio = array();
        $convenios = Convenio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($convenios as $key => $value) {
            $cboConvenio = $cboConvenio + array($value->id => $value->nombre);
        }
        $cboEstadoCivil = array("SOLTERO(A)"=>"SOLTERO(A)","CASADO(A)"=>"CASADO(A)","VIUDO(A)"=>"VIUDO(A)","DIVORCIADO(A)"=>"DIVORCIADO(A)","CONVIVIENTE"=>"CONVIVIENTE");
        $cboSexo = array("M"=>"M","F"=>"F");
        $cboModo             = array("F" => "Fisico", "V" => "Virtual");
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $formData            = array('Seguimiento.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('Seguimiento', 'formData', 'entidad', 'boton', 'listar', 'cboConvenio', 'cboTipoPaciente', 'cboEstadoCivil', 'modo', 'cboSexo', 'cboModo'));
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
        $existe = Libreria::verificarExistencia($id, 'Seguimiento');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'nombres'                  => 'required',
                'apellidopaterno'          => 'required',
                'apellidomaterno'          => 'required',
                'telefono'          => 'required',
                );
        $mensajes = array(
            'apellidopaterno.required'         => 'Debe ingresar un apellido paterno',
            'apellidomaterno.required'         => 'Debe ingresar un apellido materno',
            'nombres.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $dni = $request->input('dni');
        $mdlPerson = new Person();
        $resultado = Person::where('dni','LIKE',$dni);
        $value     = $resultado->first();
        if(count($value)>0 && strlen(trim($dni))>0){
            $objSeguimiento = new Seguimiento();
            $list2       = Seguimiento::where('person_id','=',$value->id)->where('id','<>',$id)->first();
            if(count($list2)>0){//SI TIENE Seguimiento
                return "Ya tiene otra Seguimiento";
            }else{//NO TIENE Seguimiento PERO SI ESTA REGISTRADO LA PERSONA COMO PROVEEDOR O PERSONAL
                $idpersona=$value->id;
            }
        }else{
            $idpersona=0;
        }    
        $error = DB::transaction(function() use($request, $id, $idpersona){
            $Seguimiento = Seguimiento::find($id);
            $Seguimiento->person_id = $idpersona;
            $Seguimiento->tipopaciente=$request->input('tipopaciente');
            $Seguimiento->fecha=date("Y-m-d");
            $Seguimiento->enviadopor=$request->input('enviadopor');
            $Seguimiento->familiar=$request->input('familiar');
            $Seguimiento->estadocivil=$request->input('estadocivil');
            $Seguimiento->modo=$request->input('modo');
            if($request->input('tipopaciente')=="Convenio"){
                $Seguimiento->convenio_id=$request->input('convenio');
                $Seguimiento->empresa=$request->input('empresa');
                $Seguimiento->carnet=$request->input('carnet');
                $Seguimiento->poliza=$request->input('poliza');
                $Seguimiento->soat=$request->input('soat');
                $Seguimiento->titular=$request->input('titular');
            }
            $Seguimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function respuestaaceptar($id)
    {
        $existe = Libreria::verificarExistencia($id, 'Seguimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id,$user){
            $Seguimiento = Seguimiento::find($id);
            $Seguimiento->situacion="A";
            $Seguimiento->fecharecepcion=date("Y-m-d H:i:s");
            $Seguimiento->personarecepcion_id=$user->person_id;
            $Seguimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function aceptar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'Seguimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Seguimiento::find($id);
        $entidad  = 'Seguimiento';
        $formData = array('route' => array('seguimiento.respuestaaceptar', $id), 'method' => 'Aceptar', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Aceptar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
     public function respuestarechazar($id)
    {
        $existe = Libreria::verificarExistencia($id, 'Seguimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id,$user){
            $Seguimiento = Seguimiento::find($id);
            $Seguimiento->situacion="R";
            $Seguimiento->fecharecepcion=date("Y-m-d H:i:s");
            $Seguimiento->personarecepcion_id=$user->person_id;
            $Seguimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function rechazar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'Seguimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Seguimiento::find($id);
        $entidad  = 'Seguimiento';
        $formData = array('route' => array('seguimiento.respuestarechazar', $id), 'method' => 'Rechazar', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Rechazar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function respuestaretornar($id)
    {
        $existe = Libreria::verificarExistencia($id, 'Seguimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id,$user){
            $Seguimiento = Seguimiento::find($id);
            $Seguimiento->situacion="T";
            $Seguimiento->fecharetorno=date("Y-m-d H:i:s");
            $Seguimiento->personaretorno_id=$user->person_id;
            $Seguimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function retornar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'Seguimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Seguimiento::find($id);
        $entidad  = 'Seguimiento';
        $formData = array('route' => array('seguimiento.respuestaretornar', $id), 'method' => 'Rechazar', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Retornar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function alerta(Request $request){
        $rs = Seguimiento::where('situacion','like','E')->where('fechaenvio','>=',date('Y-m-d').' 00:00:00')->where('fechaenvio','<=',date('Y-m-d').' 23:59:59')->get();
        $cantidad=count($rs);
        if(count($rs)>0){
            $msg="<label>Solicitudes Pendientes: $cantidad</label>";
            $alerta="Solicitudes Pendientes: $cantidad";
        }else{
            $msg="";
            $alerta="";
        }
        return "vcantidad='$cantidad';vdatos='$msg';valerta='$alerta';";
    }

    public function solicitar(Request $request)
    {
        $user = Auth::user();
        $error = DB::transaction(function() use($request,$user){
            $Seguimiento       = new Seguimiento();
            $Seguimiento->historia_id = $request->input('historia_id');
            $Seguimiento->personaenvio_id = $user->person_id;
            $Seguimiento->areaenvio_id=16;
            $Seguimiento->areadestino_id=16;
            $Seguimiento->fechaenvio=date("Y-m-d H:i:s");
            $Seguimiento->situacion="E";
            $Seguimiento->comentario='HISTORIA SOLICITADA POR ADMISION';
            $Seguimiento->save();            
        });
        return is_null($error) ? "OK" : $error;
    }
}
