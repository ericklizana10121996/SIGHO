<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Sala;
use App\Tipohabitacion;
use App\Salaoperacion;
use App\Person;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;

class SalaoperacionController extends Controller
{
    protected $folderview      = 'app.salaoperacion';
    protected $tituloAdmin     = 'Sala de Operacion';
    protected $tituloRegistrar = 'Registrar Sala de Operacion';
    protected $tituloModificar = 'Modificar Sala de Operacion';
    protected $tituloEliminar  = 'Eliminar Sala de Operacion';
    protected $rutas           = array('create' => 'salaoperacion.create', 
            'edit'   => 'salaoperacion.edit', 
            'delete' => 'salaoperacion.eliminar',
            'search' => 'salaoperacion.buscar',
            'index'  => 'salaoperacion.index',
            'pdfListar'  => 'salaoperacion.pdfListar',
            'acept' => 'salaoperacion.acept',
            'reject' => 'salaoperacion.reject',
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
        $entidad          = 'Salaoperacion';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $doctor           = Libreria::getParam($request->input('doctor'),'');
        $fecha            = Libreria::getParam($request->input('fecha'));
        $sala             = $request->input('sala');
        $resultado        = Salaoperacion::leftjoin('historia','historia.id','=','salaoperacion.historia_id')
                            ->join('sala as sa', 'sa.id', '=', 'salaoperacion.sala_id')
                            ->join('person as doctor', 'doctor.id', '=', 'salaoperacion.medico_id')
                            ->leftjoin('person as usuario', 'usuario.id', '=', 'salaoperacion.usuario_id')
                            ->join('especialidad','especialidad.id','=','doctor.especialidad_id')
                            ->leftjoin('person as paciente', 'paciente.id', '=', 'historia.person_id')
                            ->where(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'), 'LIKE', '%'.strtoupper($doctor).'%');
        if($paciente!=""){
            //$resultado = $resultado->where('Salaoperacion.paciente', 'LIKE', '%'.strtoupper($paciente).'%');
            $resultado = $resultado->where(function ($query) use ($paciente) {
                            $query->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                                ->orWhere('Salaoperacion.paciente', 'LIKE', '%'.strtoupper($paciente).'%');
                        });
        }
        if($doctor!=""){
            $resultado = $resultado->where(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'), 'LIKE', '%'.strtoupper($doctor).'%');
        }
        if($fecha!=""){
            $resultado = $resultado->where('Salaoperacion.fecha', '=', ''.$fecha.'');
        }
        if($sala!=0){
            $resultado = $resultado->where('Salaoperacion.sala_id', '=',$sala);
        }
        $resultado        = $resultado->select('Salaoperacion.*','sa.nombre as sala','usuario.nombres as usuario3','historia.tipopaciente','especialidad.nombre as especialidad','historia.numero as historia1',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres) as doctor'))->orderBy('Salaoperacion.fecha', 'ASC')->orderBy('Salaoperacion.horainicio','ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => '', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Especialidad', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Sala operacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Hora Inicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Hora Fin', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operacion a realizar', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Responsable', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Modifica', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '4');
        
        $user = Auth::user();

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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'user'));
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
        $entidad          = 'Salaoperacion';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboSala = array(0 => 'Todas');
        $sala = Sala::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($sala as $key => $value) {
            $cboSala = $cboSala + array($value->id => $value->nombre);
        }
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboSala'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Salaoperacion';
        $salaoperacion = null;
        $cboSala = array();
        $user = Auth::user();
        $sala = Sala::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($sala as $key => $value) {
            $cboSala = $cboSala + array($value->id => $value->nombre);
        }
        $cboTipoHabitacion = array('null'=>'No precisa');
        $rs = Tipohabitacion::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboTipoHabitacion = $cboTipoHabitacion + array($value->id => $value->nombre);
        }
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $cboHoras = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
        $cboPaquete = array('No'=>'No','Si'=>'Si');
        $formData            = array('salaoperacion.store');
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('salaoperacion', 'formData', 'entidad', 'boton', 'listar', 'cboTipoHabitacion', 'cboHoras', 'cboSala', 'cboTipoPaciente', 'cboPaquete', 'user'));
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
                );
        $mensajes = array(
            'doctor.required'         => 'Debe seleccionar un doctor',
            'especialidad.required'         => 'Debe seleccionar una especialidad'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }  

        // $list = [];  
        // $fecha_hora_inicio = $request->input('fecha').' '.$request->input('horainicio');
        
        // if($request->input('horafin') >= '00:00:00'){
        //    // dd($request->input('fecha'));
        //    $fecha = $request->input('fecha');
        //    $fechafin = date('Y-m-d',strtotime($fecha ."+ 1 day"));
        // }else{
        //     $fechafin = $request->input('fecha');
        // }
 


        $fecha_inicio = $request->input('fecha').' '.$request->input('horainicio').':00';
        $sala = $request->input('sala');
        $fecha_i = $request->input('fecha');
           
        if($request->input('horafin') > '23:59:59'){
           // dd($request->input('fecha'));
           $fecha_f = date('Y-m-d',strtotime($fecha_i ."+ 1 day"));
        }else{
            $fecha_f = $request->input('fecha');
        }

        $fecha_fin = $fecha_f.' '.$request->input('horafin').':00';
        
        // dd(array($fecha_i, $fecha_f, $fecha_inicio, $fecha_fin,$sala));
        // $list = [];
        $list = DB::select('CALL sp_validar_horario_sala_operac(?,?,?,?,?)', array($fecha_i, $fecha_f, $fecha_inicio, $fecha_fin,$sala));

        // Salaoperacion::whereBetween()
        // dd($list);
        

        // $cont_err = 0;

        // $list = [];

        // $list = Salaoperacion::where('sala_id','=', $request->input('sala'))
        // ->where('situacion','!=','A')
        // ->whereNull('deleted_at')
        // ->where('fecha','=',$request->input('fecha'))
        // ->where(DB::raw("CAST(CONCAT(fecha,' ', horainicio) as DATETIME)"),'<=' ,
        //     $fecha_hora_inicio)
        // ->where('horafin','>',$request->input('horainicio'))
        // ->where('horainicio','<=',$request->input('horainicio'))
        // ->where(function($q) use ($request, $fechafin){
        //     $q->where(function($qq) use ($request, $fechafin){
        //         $qq->where(DB::raw("CAST(CONCAT(DATE_ADD(fecha, INTERVAL 1 DAY), ' ', horafin) as DATETIME)"),'>', $fechafin.' '. $request->input('horafin'));
        //     })->orWhere(function($qq) use ($request, $fechafin){
        //         $qq->where(DB::raw("CAST(CONCAT(fecha,' ', horafin) as DATETIME)"), '>', $fechafin.' '. $request->input('horafin'));
        //     });
        // })
        // ->get();




        //                     ->where('deleted_at','=',NULL)   
        // $list = Salaoperacion::where('fecha','=',$request->input('fecha'))
        //                     ->where('sala_id','=',$request->input('sala'))
        //                     ->where('situacion','!=','A')
        //                     ->where('deleted_at','=',NULL)
        //                     //->where('horainicio','<',$request->input('horafin'))
        //                     //->where('horafin','>',$request->input('horainicio'))
        //                     ->where(function($q) use ($request){
        //                         $q->where(function($qq) use ($request){
        //                             $qq->where('horainicio','<',$request->input('horainicio'))
        //                                 ->where('horafin','>',$request->input('horainicio'));
        //                         })->orWhere(function($qq) use ($request){
        //                             $qq->where('horainicio','<',$request->input('horafin'))
        //                                 ->where('horafin','>',$request->input('horafin'));
        //                         })
        //                         // ->orwhere(function($qq) use ($request){
        //                         //      $qq->where('horainicio','>=',$request->input('horainicio'))->where('horafin','<=',$request->input('horafin'));
        //                         // })
        //                         ;

        //                         // ->orWhere(function($qq) use ($request){
        //                         //     $qq->where('horainicio','<=',$request->input('horainicio'))
        //                         //         ->where('horafin','<=',$request->input('horafin'));
        //                         // })
        //                     })
        //                     ->get();

        // dd($list);

        if(count($list)>0){
            $dat[0]=array("respuesta"=>"ERROR",'msg'=>'Hora no permitida');
            return json_encode($dat);
        }

        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $Salaoperacion       = new Salaoperacion();
            $Salaoperacion->fecha = $request->input('fecha');
            $person_id = $request->input('person_id');
            if($person_id==""){
                $person_id = null;
            }
            $historia_id = $request->input('historia_id');
            if($historia_id==""){
                $historia_id = null;
            }
            $Salaoperacion->historia_id = $historia_id;
            $Salaoperacion->paciente = $request->input('paciente');
            $Salaoperacion->medico_id = $request->input('doctor_id');
            $Salaoperacion->situacion='P';//Pendiente
            $Salaoperacion->horainicio = $request->input('horainicio');
            $Salaoperacion->horafin = $request->input('horafin');
            $Salaoperacion->tiempo = $request->input('tiempo');
            $Salaoperacion->operacion = $request->input('operacion');
            if($request->input('arcoenc')=="S"){
                $Salaoperacion->arcoenc = $request->input('arcoenc');
            }
            $Salaoperacion->instrumentista = $request->input('instrumentista');
            $Salaoperacion->anestesiologo = $request->input('anestesiologo');
            $Salaoperacion->ayudante1 = $request->input('ayudante1');
            $Salaoperacion->ayudante2 = $request->input('ayudante2');
            $Salaoperacion->responsable = $request->input('responsable');
            $Salaoperacion->sala_id = $request->input('sala');
            $Salaoperacion->paquete = $request->input('paquete');
            if($request->input('tipohabitacion')=="null"){
                $Salaoperacion->tipohabitacion_id = null;    
            }else{
                $Salaoperacion->tipohabitacion_id = $request->input('tipohabitacion');
            }
            $Salaoperacion->usuario_id = $user->person_id;
            $Salaoperacion->save();
        });
        $dat[0]=array('respuesta'=>'OK');
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
        $existe = Libreria::verificarExistencia($id, 'Salaoperacion');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $salaoperacion = Salaoperacion::find($id);
        $entidad             = 'Salaoperacion';
        $formData            = array('salaoperacion.update', $id);
        $cboSala = array();
        $user = Auth::user();
        $sala = Sala::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($sala as $key => $value) {
            $cboSala = $cboSala + array($value->id => $value->nombre);
        }
        $cboTipoHabitacion = array('null'=>'No precisa');
        $rs = Tipohabitacion::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboTipoHabitacion = $cboTipoHabitacion + array($value->id => $value->nombre);
        }
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $cboPaquete = array('No'=>'No','Si'=>'Si');
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('salaoperacion', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente', 'cboTipoHabitacion', 'cboSala', 'cboPaquete' , 'user'));
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
        // dd('probando..');
       
        $existe = Libreria::verificarExistencia($id, 'salaoperacion');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'doctor'                  => 'required',
                'especialidad'          => 'required',
                );
        $mensajes = array(
            'doctor.required'         => 'Debe seleccionar un doctor',
            'especialidad.required'         => 'Debe seleccionar una especialidad'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $id, $user){
            $Salaoperacion = Salaoperacion::find($id);
             $Salaoperacion->fecha = $request->input('fecha');
            $person_id = $request->input('person_id');
            if($person_id==""){
                $person_id = null;
            }
            $historia_id = $request->input('historia_id');
            if($historia_id==""){
                $historia_id = null;
            }
            $Salaoperacion->historia_id = $historia_id;
            $Salaoperacion->medico_id = $request->input('doctor_id');
            $Salaoperacion->paciente = $request->input('paciente');
        
            // $list = [];
            
            $fecha_inicio = $request->input('fecha').' '.$request->input('horainicio').':00';
            $fecha_fin = $request->input('fecha').' '.$request->input('horafin').':00';
        
            $fecha_i = $request->input('fecha');
            $sala = $request->input('sala');

            if($request->input('horafin') > '23:59:59'){
               // dd($request->input('fecha'));
               $fecha_f = date('Y-m-d',strtotime($fecha_i ."+ 1 day"));
            }else{
                $fecha_f = $request->input('fecha');
            }
               // dd($fechafin);
            // $list = [];
            $list = DB::select('CALL sp_validar_horario_sala_operac(?,?,?,?,?)', array($fecha_i, $fecha_f, $fecha_inicio, $fecha_fin, $sala));

            $cont_err = 0;

            // dd($list);

            foreach ($list as $key => $value) {
               if ($value->id != $id) {
                    $cont_err++;
               }
               // dd($value->id);
            }

            // dd($cont_err);

            // $list = Salaoperacion::where('sala_id','=', $request->input('sala'))
            //         ->where('situacion','!=','A')
            //         ->whereNull('deleted_at')
            //         ->where('id','!=',$id)
            //         ->where('fecha','=',$request->input('fecha'))
            //         // ->where(function($a) use ($fecha_hora_inicio, $fechafin){
            //         //     $a->where(function($aa) use ($fecha_hora_inicio){
            //         //         $aa->where(DB::raw("CAST(CONCAT(fecha,' ', horainicio) as DATETIME)"),'<=' ,$fecha_hora_inicio);
            //         //     })->orWhere(function($aa) use ($fecha_hora_inicio, $fechafin){
            //         //         $aa->where(DB::raw("CAST(CONCAT(fecha,' ', horainicio) as DATETIME)"),'>' ,$fecha_hora_inicio)->where('fecha','=',$fechafin);
            //         //     });
            //         // ->where('horafin','>',$request->input('horainicio'))
            //         // ->where('horainicio','<')
            //         ->where(function($a) use ($request){
            //             $a->where(function($aa) use ($request){
            //                 $aa->where('horainicio','<=',$request->input('horainicio'))->where('horafin','>=',$request->input('horainicio'))->where('horafin','<','horainicio');
            //             })->orWhere(function($aa) use($request){
            //                 $aa->where('horainicio','>=',$request->input('horainicio'))->where('horafin','<',$request->input('horainicio'))->where('horafin','>','horainicio');
            //             });
            //         })
            //         ->where(function($q) use ($request, $fechafin){
            //             $q->where(function($qq) use ($request, $fechafin){
            //                 $qq->where(DB::raw("CAST(CONCAT(DATE_ADD(fecha, INTERVAL 1 DAY), ' ', horafin) as DATETIME)"),'>', $fechafin.' '. $request->input('horafin'))->where(DB::raw("CAST(CONCAT(fecha, ' ', horainicio) as DATETIME)"),'<', $fechafin.' '. $request->input('horafin'));
            //             })->orWhere(function($qq) use ($request, $fechafin){
            //                 $qq->where(DB::raw("CAST(CONCAT(fecha,' ', horafin) as DATETIME)"), '>', $fechafin.' '. $request->input('horafin'))->where(DB::raw("CAST(CONCAT(fecha, ' ', horainicio) as DATETIME)"),'<', $fechafin.' '. $request->input('horafin'));
            //             });
            //         })
            //         ->get();




            // $list = Salaoperacion::where('fecha','=',$request->input('fecha'))
            //         ->where('sala_id','=',$request->input('sala'))
            //         ->where('situacion','!=','A')
            //         ->where('deleted_at','=',NULL)
            //         ->where('id','!=',$id)
            //         //->where('horainicio','<',$request->input('horafin'))
            //         //->where('horafin','>',$request->input('horainicio'))
                   
            //         ->where(function($q) use ($request){
            //             $q->where(function($qq) use ($request){
            //                 $qq->where('horainicio','<',$request->input('horainicio'))
            //                     ->where('horafin','>',$request->input('horainicio'));
            //             })->orWhere(function($qq) use ($request){
            //                 $qq->where('horainicio','<',$request->input('horafin'))
            //                     ->where('horafin','>',$request->input('horafin'));
            //             })->orwhere(function($qq) use ($request){
            //                 $qq->where('horainicio','>=',$request->input('horainicio'))->where('horafin','<=',$request->input('horafin'));
                         
            //             });

            //             // ->orWhere(function($qq) use ($request){
            //             //     $qq->where('horainicio','<=',$request->input('horainicio'))
            //             //         ->where('horafin','<=',$request->input('horafin'));
            //             // })
            //         })
            //         ->get();

            // dd($cont_err);

            if($cont_err>0){
                $dat[0]=array("respuesta"=>"ERROR",'msg'=>'Hora no permitida');
                return json_encode($dat);
            }else{
                $Salaoperacion->horainicio = $request->input('horainicio');
                $Salaoperacion->horafin = $request->input('horafin');
            }
     

            // $Salaoperacion->horainicio = $request->input('horainicio');
            // $Salaoperacion->horafin = $request->input('horafin');
            $Salaoperacion->tiempo = $request->input('tiempo');
            $Salaoperacion->operacion = $request->input('operacion');
            $Salaoperacion->instrumentista = $request->input('instrumentista');
            $Salaoperacion->anestesiologo = $request->input('anestesiologo');
            $Salaoperacion->ayudante1 = $request->input('ayudante1');
            $Salaoperacion->ayudante2 = $request->input('ayudante2');
            $Salaoperacion->responsable = $request->input('responsable');
            if($request->input('arcoenc')=="S"){
                $Salaoperacion->arcoenc = $request->input('arcoenc');
            }
            $Salaoperacion->sala_id = $request->input('sala');
            $Salaoperacion->paquete = $request->input('paquete');
            if($request->input('tipohabitacion')=="null"){
                $Salaoperacion->tipohabitacion_id = null;    
            }else{
                $Salaoperacion->tipohabitacion_id = $request->input('tipohabitacion');
            }
            $Salaoperacion->usuario2_id = $user->person_id;
            $Salaoperacion->save();
        });
        $dat[0]=array('respuesta'=>'OK');
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
        $id             = Libreria::getParam($request->input('id'),'');
        $comentarioa    = Libreria::getParam($request->input('comentarioa'),'');
        $existe = Libreria::verificarExistencia($id, 'salaoperacion');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id, $comentarioa){
            $Salaoperacion = Salaoperacion::find($id);
            $Salaoperacion->comentario = $comentarioa;
            $Salaoperacion->save();
            $Salaoperacion->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'salaoperacion');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Salaoperacion::find($id);
        $entidad  = 'Salaoperacion';
        $formData = array('route' => array('Salaoperacion.destroy'), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar2')->with(compact('id', 'modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function validarDNI(Request $request)
    {
        $dni = $request->input("dni");
        $entidad    = 'Person';
        $mdlPerson = new Person();
        $resultado = Person::where('dni','LIKE',$dni);
        $value     = $resultado->first();
        if(count($value)>0){
            $objSalaoperacion = new Salaoperacion();
            $list2       = Salaoperacion::where('person_id','=',$value->id)->first();
            if(count($list2)>0){//SI TIENE Salaoperacion
                $data[] = array(
                            'apellidopaterno' => $value->apellidopaterno,
                            'apellidomaterno' => $value->apellidomaterno,
                            'nombres' => $value->nombres,
                            'telefono' => $value->telefono,
                            'direccion' => $value->direccion,
                            'id'    => $value->id,
                            'msg' => 'N',
                        );
            }else{//NO TIENE Salaoperacion PERO SI ESTA REGISTRADO LA PERSONA COMO PROVEEDOR O PERSONAL
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
    
   	public function pdfListar(Request $request){
        $entidad          = 'Salaoperacion';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $doctor           = Libreria::getParam($request->input('doctor'),'');
        $fecha            = Libreria::getParam($request->input('fecha'));
        $resultado        = Salaoperacion::leftjoin('person as paciente', 'paciente.id', '=', 'Salaoperacion.paciente_id')
                            ->join('person as doctor', 'doctor.id', '=', 'Salaoperacion.doctor_id')
                            ->join('especialidad','especialidad.id','=','doctor.especialidad_id')
                            ->leftjoin('historia','historia.id','=','Salaoperacion.historia_id')
                            ->where('Salaoperacion.paciente', 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'), 'LIKE', '%'.strtoupper($doctor).'%');
        if($fecha!=""){
            $resultado = $resultado->where('Salaoperacion.fecha', '=', ''.$fecha.'');
        }
        $resultado        = $resultado->select('Salaoperacion.*','historia.tipopaciente as tipopaciente2','especialidad.nombre as especialidad','historia.numero as historia2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres) as doctor'))->orderBy('Salaoperacion.fecha', 'ASC')->orderBy(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'),'asc')->orderBy('Salaoperacion.horainicio','ASC');
        $lista            = $resultado->get();
        if (count($lista) > 0) {            
            $pdf = new TCPDF();
            $pdf::SetTitle('Lista de Pacientes');
            $pdf::AddPage();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(0,10,utf8_decode("LISTA DE SalaoperacionS"),0,0,'C');
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
                    $pdf::Cell(13,6,utf8_decode("TIEMPO"),1,0,'C');
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
                $pdf::Cell(13,5,utf8_decode($value->tiempo),1,0,'C');
                $pdf::Cell(50,5,utf8_decode($value->comentario),1,0,'L');
                $pdf::Ln();
            }
            $pdf::Output('ListaSalaoperacion.pdf');
        }
    }
    
    
    public function aceptar($id)
    {
        $existe = Libreria::verificarExistencia($id, 'salaoperacion');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Caja = Salaoperacion::find($id);
            $Caja->situacion="C";//Aceptado
            $Caja->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function acept($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'salaoperacion');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Salaoperacion::find($id);
        $entidad  = 'Salaoperacion';
        $formData = array('route' => array('salaoperacion.aceptar', $id), 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Confirmar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function rechazar(Request $request)
    {
        $id             = Libreria::getParam($request->input('id'),'');
        $comentarioa    = Libreria::getParam($request->input('comentarioa'),'');
        $existe = Libreria::verificarExistencia($id, 'salaoperacion');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id, $comentarioa){
            $Caja = Salaoperacion::find($id);
            $Caja->comentario = $comentarioa;
            $Caja->situacion="A";//Anulado
            $Caja->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function reject($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'salaoperacion');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Salaoperacion::find($id);
        $entidad  = 'Salaoperacion';
        $opciones = array("PRIMER MOTIVO");
        $formData = array('route' => array('salaoperacion.rechazar'), 'method' => 'Reject', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view('app.confirmar3')->with(compact('id', 'modelo', 'formData', 'entidad', 'boton', 'listar','opciones'));
    }
}
