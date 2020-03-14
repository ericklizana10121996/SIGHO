<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Convenio;
use App\Hojacosto;
use App\Movimiento;
use App\Detallehojacosto;
use App\Detallemovcaja;
use App\Person;
use App\Caja;
use App\Tiposervicio;
use App\Servicio;
use App\Plan;
use App\Detalleplan;
use App\Hospitalizacion;
use App\Librerias\Libreria;
use App\Librerias\EnLetras;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;

class HojacostoController extends Controller
{
    protected $folderview      = 'app.hojacosto';
    protected $tituloAdmin     = 'Hoja de Costo';
    protected $tituloRegistrar = 'Registrar Hoja de Costo';
    protected $tituloModificar = 'Modificar Hoja de Costo';
    protected $tituloEliminar  = 'Eliminar Hoja de Costo';
    protected $rutas           = array('create' => 'hojacosto.create', 
            'edit'   => 'hojacosto.edit', 
            'delete' => 'hojacosto.eliminar',
            'search' => 'hojacosto.buscar',
            'index'  => 'hojacosto.index',
            'pdfListar'  => 'hojacosto.pdfListar',
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
        $entidad          = 'Hojacosto';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $fechainicio      = Libreria::getParam($request->input('fechainicio'));
        $fechafin         = Libreria::getParam($request->input('fechafin'));
        $user = Auth::user();

        $resultado        = Hojacosto::join('hospitalizacion','hospitalizacion.id','=','hojacosto.hospitalizacion_id')
                            ->join('historia', 'historia.id', '=', 'hospitalizacion.historia_id')
                            ->join('person as paciente', 'paciente.id', '=', 'historia.person_id')
                            ->leftjoin('person as responsable','responsable.id','=','hojacosto.usuario_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where('hojacosto.situacion','=',$request->input('solo'));
        if($request->input('solo')=='A'){
            $resultado= $resultado->where('hospitalizacion.fecha','>=',''.$fechainicio.'')
                            ->where('hospitalizacion.fecha','<=',''.$fechafin.'');
        }
        $resultado        = $resultado->select('hojacosto.*',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable','hospitalizacion.fecha',DB::raw('historia.numero as historia'))->orderBy('hospitalizacion.fecha', 'DESC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'conf'));
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
        $entidad          = 'Hojacosto';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboSolo          = array('P' => 'Pendiente','F' => 'Finalizado');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboSolo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Hojacosto';
        $hojacosto = null;
        $cboTipoServicio = array(""=>"--Todos--");
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $formData            = array('hojacosto.store');
        $cboTipoPaciente     = array("Convenio" => "Convenio", "Particular" => "Particular", "Hospital" => "Hospital");
        $cbosituacionpaciente= array(''=> 'Seleccione','Hospitalizacion' => 'Hospitalizacion', 'UCI' => 'UCI',  'UCIN' => 'UCIN');
        $cboresponsablepago  = array(''=> 'Seleccione','D' => 'Doctor', 'P' => 'Paciente');

        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('hojacosto', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente', 'cboConvenio', 'cboTipoServicio','cbosituacionpaciente','cboresponsablepago'));
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
                'paciente'          => 'required',
                );
        $mensajes = array(
            'paciente.required'         => 'Debe seleccionar un paciente',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       
        
        $user = Auth::user();
        $dat=array();
        
        $error = DB::transaction(function() use($request,$user,&$dat,&$numeronc){
            $Hoja       = new Hojacosto();
            $Hospitalizacion = Hospitalizacion::find($request->input('hospitalizacion_id'));
            $Hoja->hospitalizacion_id = $request->input('hospitalizacion_id');
            $Hoja->situacion = 'P';
            $Hoja->tipopaciente = $request->input('tipopaciente');
            $Hoja->movimientoinicial_id = $Hospitalizacion->movimientoinicial_id;
            $Hoja->usuario_id=$user->person_id;
      
            $Hoja->situacion_paciente = $request->input('situacionpaciente');
            $Hoja->responsable_pago = $request->input('responsablepago');
            $Hoja->doctor_id = ($request->input('responsablepago') == 'D'?$request->input('doctor_responsable_id'):null);
                     
            $Hoja->save();

            $arr=explode(",",$request->input('listServicio'));
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detallehojacosto();
                $Detalle->hojacosto_id=$Hoja->id;
                if($request->input('txtIdTipoServicio'.$arr[$c])!="0"){
                    $Detalle->servicio_id=$request->input('txtIdServicio'.$arr[$c]);
                    $Detalle->descripcion="";
                }else{
                    $Detalle->servicio_id=null;
                    $Detalle->descripcion=trim($request->input('txtServicio'.$arr[$c]));
                }
                $Detalle->persona_id=$request->input('txtIdMedico'.$arr[$c]);
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                $Detalle->precio=$request->input('txtPrecio'.$arr[$c]);
                $Detalle->save();
            }

            $dat[0]=array("respuesta"=>"OK");
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
        $existe = Libreria::verificarExistencia($id, 'hojacosto');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $cboTipoServicio = array(""=>"--Todos--");
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $hojacosto = Hojacosto::find($id);
        $entidad             = 'Hojacosto';
        $formData            = array('hojacosto.update', $id);
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => 'Hospital');
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        $cbosituacionpaciente= array(''=> 'Seleccione','Hospitalizacion' => 'Hospitalizacion', 'UCI' => 'UCI',  'UCIN' => 'UCIN');
        $cboresponsablepago  = array(''=> 'Seleccione','D' => 'Doctor', 'P' => 'Paciente');

        return view($this->folderview.'.mant')->with(compact('hojacosto', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente', 'cboTipoServicio','cbosituacionpaciente','cboresponsablepago'));
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
        // dd($request->input('situacionpaciente'), $request->input('responsablepago'), $request->input('doctor_responsable_id'));

        $existe = Libreria::verificarExistencia($id, 'hojacosto');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'paciente'                  => 'required',
                );
        $mensajes = array(
            'paciente.required'         => 'Debe ingresar un paciente',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $dat= array();

        $Hoja = Hojacosto::find($id);
        $Hoja->situacion_paciente = $request->input('situacionpaciente');
        $Hoja->responsable_pago = $request->input('responsablepago');
        $Hoja->doctor_id = ($request->input('responsablepago') =='D'?$request->input('doctor_responsable_id'):null);
        $Hoja->tipopaciente = $request->input('tipopaciente');
              
        $Hoja->save();
        

        $error = DB::transaction(function() use($request, $id, &$dat){
            Detallehojacosto::where('hojacosto_id','=',$id)->delete();
            $arr=explode(",",$request->input('listServicio'));
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detallehojacosto();
                $Detalle->hojacosto_id=$id;
                if($request->input('txtIdTipoServicio'.$arr[$c])!="0"){
                    $Detalle->servicio_id=$request->input('txtIdServicio'.$arr[$c]);
                    $Detalle->descripcion="";
                }else{
                    $Detalle->servicio_id=null;
                    $Detalle->descripcion=trim($request->input('txtServicio'.$arr[$c]));
                }
                $Detalle->persona_id=$request->input('txtIdMedico'.$arr[$c]);
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                $Detalle->precio=$request->input('txtPrecio'.$arr[$c]);
                $Detalle->save();
            }

            $dat[0]=array("respuesta"=>"OK");
            
        });
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
        $existe = Libreria::verificarExistencia($id, 'hojacosto');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Hojacosto = Hojacosto::find($id);
            $Hojacosto->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'hojacosto');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Movimiento::find($id);
        $entidad  = 'Hojacosto';
        $formData = array('route' => array('hojacosto.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function buscarservicio(Request $request)
    {
        $descripcion = $request->input("descripcion");
        $idtiposervicio = trim($request->input("idtiposervicio"));
        $tipopago = $request->input('tipopaciente');
        if ($tipopago == 'Hospital') {
            $tipopago = 'Particular';
        }

        $resultado = Servicio::leftjoin('tarifario','tarifario.id','=','servicio.tarifario_id');
        if($tipopago=='Convenio'){
            $resultado = $resultado->where(DB::raw('trim(concat(servicio.nombre,\' \',tarifario.nombre))'),'LIKE','%'.$descripcion.'%')->where('servicio.plan_id','=',$request->input('plan_id'));
        }else{
            $resultado = $resultado->where(DB::raw('trim(servicio.nombre)'),'LIKE','%'.$descripcion.'%');
        }
        if(trim($idtiposervicio)!=""){
            $resultado = $resultado->where('tiposervicio_id','=',$idtiposervicio);
        }
        $resultado    = $resultado->where('tipopago','LIKE',''.strtoupper($tipopago).'')->select('servicio.*','tarifario.nombre as tarifario')->get();

        // dd($resultado);

        if(count($resultado)>0){
            $c=0;
            foreach ($resultado as $key => $value){
                $data[$c] = array(
                            'servicio' => ($value->tipopago=='Convenio')?$value->tarifario:$value->nombre,
                            'tiposervicio' => $value->tiposervicio->nombre,
                            'precio' => $value->precio,
                            'idservicio' => $value->id,
                        );
                        $c++;                
            }            
            if($tipopago=='Convenio' && ($idtiposervicio=='' || $idtiposervicio==1)){//buscar consultas con precio de convenio
                $resultado = Servicio::where(DB::raw('trim(servicio.nombre)'),'LIKE','%'.$descripcion.'%')
                            ->where('tipopago','LIKE','Particular')
                            ->where('tiposervicio_id','=','1')->get();
                if(count($resultado)>0){
                    foreach ($resultado as $key => $value){
                        //COSTO DE CONSULTA
                        $plan = Plan::find($request->input('plan_id'));
                        $data[$c] = array(
                                    'servicio' => ($value->tipopago=='Convenio')?$value->tarifario:$value->nombre,
                                    'tiposervicio' => $value->tiposervicio->nombre,
                                    'precio' => $plan->consulta,
                                    'idservicio' => $value->id,
                                );
                                $c++;                
                    }            
                }
            }
        }else{
            if($tipopago=='Convenio' && ($idtiposervicio=='' || $idtiposervicio==1)){//buscar consultas con precio de convenio
                $resultado = Servicio::where(DB::raw('trim(servicio.nombre)'),'LIKE','%'.$descripcion.'%')
                            ->where('tipopago','LIKE','Particular')
                            ->where('tiposervicio_id','=','1')->get();
                if(count($resultado)>0){
                    $c=0;
                    foreach ($resultado as $key => $value){
                        //COSTO DE CONSULTA
                        $plan = Plan::find($request->input('plan_id'));
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
                    $precio = $resultado->precio;
                }
                /*if($request->input('formapago')=='Tarjeta'){
                    if($request->input('tarjeta')=="CREDITO"){
                        $precio=number_format($precio*1.04,2,'.','');
                    }else{
                        $precio=number_format($precio*1.03,2,'.','');
                    }
                    $pagohospital=$precio - $pagomedico;
                }*/
                $data[0] = array(
                        'servicio' => ($resultado->tipopago=='Convenio')?$resultado->tarifario->nombre:$resultado->nombre,
                        'tiposervicio' => $resultado->tiposervicio->nombre,
                        'precio' => $precio,
                        'idservicio' => $resultado->id,
                        'preciohospital' => $pagohospital,
                        'preciomedico' => $pagomedico,
                        'modo' => $resultado->modo,
                        'idtiposervicio' => $resultado->tiposervicio_id,
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
                );
        }
        return json_encode($data);
    }
    
   	public function pdfHojacosto(Request $request){
        $entidad          = 'Ticket';
        $id               = Libreria::getParam($request->input('id'),'');
        $resultado        = Hojacosto::join('hospitalizacion','hospitalizacion.id','=','hojacosto.hospitalizacion_id')
                            ->join('historia', 'historia.id', '=', 'hospitalizacion.historia_id')
                            ->join('person as paciente', 'paciente.id', '=', 'historia.person_id')
                            ->leftjoin('person as responsable','responsable.id','=','hojacosto.usuario_id')
                            ->where('hojacosto.id','=',$id);
        $resultado        = $resultado->select('hojacosto.*',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable','hospitalizacion.fecha',DB::raw('historia.numero as historia'),'hospitalizacion.fechaalta',DB::raw('paciente.id as paciente_id'),'hospitalizacion.hora',DB::raw('hospitalizacion.situacion as situacion2'))->orderBy('hospitalizacion.fecha', 'ASC');
        $lista            = $resultado->get();


        if (count($lista) > 0) {     
            foreach($lista as $key => $value){
                $paciente_id = $value->paciente_id;
                // dd($paciente_id);
                $pdf = new TCPDF();
                $pdf::SetTitle('Hoja de Costo');
                $pdf::AddPage();
                $pdf::SetFont('helvetica','B',12);
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 15, 5, 115, 20);
                $pdf::Ln();
                $pdf::Ln();
                $pdf::Cell(0,7,"HOJA DE COSTO",0,0,'C');
                $pdf::Ln();
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(25,6,utf8_encode("Paciente: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(100,6,(trim($value->paciente)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,6,utf8_encode("Historia: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(0,6,(trim($value->historia)),0,0,'L');
                $pdf::Ln();
                
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(25,6,utf8_encode("Situación: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(100,6,(strtoupper(is_null($value->situacionpaciente)==true?'Hospitalizacion':$value->situacionpaciente)),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,6,utf8_encode("Pagado Por: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);

                $a_cargo = ($value->responsable_pago=='D'?'DR. '. $value->doctor_responsable->apellidopaterno.' '.$value->doctor_responsable->apellidomaterno.' '. $value->doctor_responsable->nombres:'PACIENTE');
                
                // dd(strlen($a_cargo));
                if (strlen($a_cargo)>25) {
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();                    
                    $pdf::Multicell(0,3,$a_cargo,0,'L');
                    $pdf::SetXY($x,$y);                            
                    $pdf::Cell(0,6,"",0,0,'C');
                }else{
                    $pdf::Cell(0,6,$a_cargo,0,0,'L');
                }

                $pdf::Ln();

                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(25,6,utf8_encode("T. Paciente: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(100,6,(strtoupper(is_null($value->tipopaciente)==true?'-':$value->tipopaciente)),0,0,'L');
                $pdf::Ln();

                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(25,6,utf8_encode("Fecha Ingreso: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);

                $fecha = $value->fecha;
                $fecha_hora = $value->fecha.' '. $value->hora;

                $pdf::Cell(100,6,date("d/m/Y",strtotime($value->fecha))." ".substr($value->hora,0,5),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(20,6,utf8_encode("Fecha Alta: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                if($value->situacion2=='A'){
                    $pdf::Cell(0,6,date("d/m/Y",strtotime($value->fechaalta)),0,0,'L');
                }
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(8,8.2,("Item"),1,0,'C');
                $pdf::Cell(50,8.2,utf8_encode("Tipo Serv."),1,0,'C');
                $pdf::Cell(80,8.2,utf8_encode("Servicio"),1,0,'C');
                $pdf::Cell(15,8,'Doc.',1,0,'C');
                $pdf::Cell(40,4,utf8_encode("Deuda"),1,0,'C');
                // $pdf::Cell(40,3.5,utf8_encode("Pagado"),1,0,'C');
                $pdf::Ln();
                $pdf::Cell(153,8,'',0,0,'C');
                $pdf::Cell(10,3.5,("Cant."),1,0,'C');
                $pdf::Cell(15,3.5,("Precio"),1,0,'C');
                $pdf::Cell(15,3.5,("Sub Tot."),1,0,'C');
                // $pdf::Cell(10,3.5,("Cant."),1,0,'C');
                // $pdf::Cell(15,3.5,("Precio"),1,0,'C');
                // $pdf::Cell(15,3.5,("Sub Tot."),1,0,'C');
                $pdf::Ln();
                $resultado        = Detallehojacosto::leftjoin('servicio', 'servicio.id', '=', 'detallehojacosto.servicio_id')
                            ->where('detallehojacosto.hojacosto_id', '=', $id)
                            ->select('detallehojacosto.*');
                $lista2            = $resultado->get();
                // dd($lista2);

                $c=0;$deuda=0;
                foreach($lista2 as $key2 => $v){$c=$c+1;
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(8,6,$c,1,0,'C');
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
                    if (isset($v->servicio->tiposervicio->nombre)) {
                        $pdf::Cell(50,6,utf8_encode($v->servicio->tiposervicio->nombre),1,0,'L');
                    } else {
                        $pdf::Cell(50,6,'VARIOS',1,0,'L');
                    }
                    $nombre.=" - ".substr($v->persona->nombres,0,1)." ".$v->persona->apellidopaterno;
                    if(strlen($nombre)<50){
                        $pdf::Cell(80,6,($nombre),1,0,'L');
                    }else{
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(80,3,($nombre),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(80,6,"",1,0,'L');
                    }
                    $pdf::Cell(15,6,'Hoja Costo',1,0,'R');
                    $pdf::Cell(10,6,number_format($v->cantidad,2,'.',''),1,0,'R');
                    $pdf::Cell(15,6,number_format($v->precio,2,'.',''),1,0,'R');
                    $pdf::Cell(15,6,number_format($v->precio*$v->cantidad,2,'.',''),1,0,'R');
                    // $pdf::Cell(10,6,'-',1,0,'R');
                    // $pdf::Cell(15,6,'-',1,0,'R');
                    // $pdf::Cell(15,6,'-',1,0,'R');
                    $pdf::Ln();
                    $deuda = $deuda + number_format($v->precio*$v->cantidad,2,'.','');         
                }
                
                $resultado1        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->where('mref.situacion','<>','A')
                            ->where('movimiento.persona_id','=',$value->paciente_id)
                            ->where('movimiento.id','>=',$value->movimientoinicial_id);
                if($value->movimientofinal_id>0){
                    $resultado1 = $resultado1->where('movimiento.id','<=',$value->movimientofinal_id);
                }
                $resultado1        = $resultado1->orderBy('mref.fecha', 'ASC')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                                    ->select('mref.*','movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.cantidad','dmc.precio','dmc.id as iddetalle','s.nombre as servicio','dmc.pagohospital','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'));
                $lista2            = $resultado1->get();
                $pago=0;
               /* if (count($lista2) > 0) {     
                    foreach($lista2 as $key2 => $v){$c=$c+1;
                        $pdf::SetFont('helvetica','',7.5);
                        $pdf::Cell(8,6,$c,1,0,'C');
                        if($v->servicio_id>"0"){
                            $servicio = Servicio::find($v->servicio_id);
                            if($servicio->tipopago=="Convenio"){
                                $codigo=$servicio->tarifario->codigo;
                                $nombre=$servicio->tarifario->nombre;    
                            }else{
                                $codigo="-";
                                if($v->servicio_id>"0"){
                                    $nombre=$servicio->nombre;
                                }else{
                                    $nombre=trim($servicio->descripcion);
                                }
                            }
                            $tiposervicio=$servicio->tiposervicio->nombre;
                        }else{
                            $codigo="-";
                            $nombre=trim($v->servicio2);
                            $tiposervicio='-';
                        }
                        $pdf::Cell(25,6,utf8_encode($tiposervicio),1,0,'L');
                        //$nombre.=" - ".substr($v->persona->nombres,0,1)." ".$v->persona->apellidopaterno;
                        if(strlen($nombre)<43){
                            $pdf::Cell(70,6,utf8_encode($nombre),1,0,'L');
                        }else{
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(70,3,utf8_encode($nombre),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(70,6,"",1,0,'L');
                        }
                        $venta = Movimiento::where("movimiento_id","=",$v->movimiento_id)->first();
                        $pdf::Cell(15,6,(($venta->tipodocumento_id==5?'B':'F').$venta->serie.'-'.$venta->numero),1,0,'L');
                        if($venta->situacion=='P'){
                            $pdf::Cell(10,6,number_format($v->cantidad,2,'.',''),1,0,'R');
                            $pdf::Cell(15,6,number_format($v->precio,2,'.',''),1,0,'R');
                            $pdf::Cell(15,6,number_format($v->precio*$v->cantidad,2,'.',''),1,0,'R');
                            $pdf::Cell(10,6,'-',1,0,'R');
                            $pdf::Cell(15,6,'-',1,0,'R');
                            $pdf::Cell(15,6,'-',1,0,'R');

                            $pdf::Ln();  
                            $deuda = $deuda + number_format($v->precio*$v->cantidad,2,'.','');
                        }else{
                            $pdf::Cell(10,6,'-',1,0,'R');
                            $pdf::Cell(15,6,'-',1,0,'R');
                            $pdf::Cell(15,6,'-',1,0,'R');
                            $pdf::Cell(10,6,number_format($v->cantidad,2,'.',''),1,0,'R');
                            $pdf::Cell(15,6,number_format($v->precio,2,'.',''),1,0,'R');
                            $pdf::Cell(15,6,number_format($v->precio*$v->cantidad,2,'.',''),1,0,'R');
                            $pdf::Ln();  
                            $pago = $pago + number_format($v->precio*$v->cantidad,2,'.','');
                        }
                    }
                }
                */
                $pdf::SetFont('helvetica','B',10);
                $pdf::Cell(155,5,utf8_decode('TOTAL:'),0,0,'R');
                $pdf::Cell(38,5,number_format($deuda,2,'.',''),0,0,'R');
                // $pdf::Cell(40,5,number_format($pago,2,'.',''),0,0,'R');
                $pdf::Ln();
                $pdf::Ln();

                // dd($fecha_hora);
                /*->where('estadopago','=','PP')->where('serie','=','4')*/
                // dd($fecha_hora);
                // dd($paciente_id);

                $resultado_22= Movimiento::where('persona_id','=',$paciente_id)->where('tipomovimiento_id','=','4')->where('created_at','>=',$fecha.' 00:00:00')->whereNull('deleted_at')->whereIn('situacion',['N','P'])->whereNotIn('tipodocumento_id',[15])/*->where('formapago','=','P')*/->orderBy('fecha','DESC')->select('serie','numero','total','tipodocumento_id as tipo_doc','situacion','movimiento_id','formapago','tipomovimiento_id')->get();




                // dd($resultado_22);

                // dd($paciente_id);
                
                $x_pendiente  = $pdf::GetX();
                $y_pendiente  = $pdf::GetY();
                
                $pdf::Cell(0,7,"PENDIENTES",0,0,'L');
                $pdf::Ln();
           
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(8,6,("Item"),1,0,'C');
                $pdf::Cell(18,6,'Doc.',1,0,'C');
                $pdf::Cell(45,6,'Servicio',1,0,'C');
                $pdf::Cell(15,6,("Sub Tot."),1,0,'C');
                $pdf::Ln();
                
                $c_22 =1;
                $total_farmacia = 0;
                $pdf::SetFont('helvetica','',7.5);
                // dd($resultado_22);
                if(count($resultado_22) > 0 ){
                    foreach ($resultado_22 as $key_22 => $value_22) {  
                        if($value_22->serie == '4'){
                            if ($value_22->situacion == 'N' && $value_22->formapago == 'P') {
                                 $pdf::Cell(8,6 ,$c_22,1,0,'C');
                                 $pdf::Cell(18,6,(($value_22->tipo_doc =='5'?'B':'F').$value_22->serie.'-'.$value_22->numero),1,0,'L');
                                 $pdf::Cell(45,6,'MEDICAMENTOS',1,0,'C');
                                 $pdf::Cell(15,6,number_format($value_22->total,2,'.',''),1,0,'R');
                                 $pdf::Ln();
                                 $c_22++;
                                 $total_farmacia+=$value_22->total;
                            }
                        }else{

                            // if($value_22->serie == '9'){
                            //     dd($value_22);
                            // }

                            if($value_22->situacion == 'P' && $value->serie != '4'){
                                if (!is_null($value_22->movimiento_id) && is_null($value_22->formapago) ) {
                                    $situacion2 = Movimiento::find($value_22->movimiento_id)->situacion;
                                    if($situacion2 == 'B'){
                                        $det = Detallemovcaja::join('servicio as s','s.id','=','detallemovcaja.servicio_id')->where('detallemovcaja.movimiento_id','=',$value_22->movimiento_id)->select('s.nombre as servicio')->get();    
                                        $cad_det = '';
                                        $band_det = 0;
                                            // dd($det);
                                        if (count($det) == 0) {
                                            $band_det = 1;
                                        }else{
                                            foreach ($det as $key_88 => $value_88) {
                                                $cad_det.=$value_88->servicio.',';
                                            }
                                        
                                        }

                                        if($band_det == 1){
                                            $det = Detallemovcaja::whereNull('servicio_id')->where('movimiento_id','=',$value_22->movimiento_id)->select('descripcion')->get();

                                            foreach ($det as $key_88 => $value_88) {
                                                $cad_det.=$value_88->descripcion.',';
                                            }
                                        
                                        }

                                        // if($value_22->movimiento_id == '602226')
                                        //     dd(strlen($cad_det));
                                        
                                        if(strlen($cad_det)>23 And strlen($cad_det) <45){
                                            $pdf::Cell(8,9 ,$c_22,1,0,'C');
                                            $pdf::Cell(18,9,(($value_22->tipo_doc =='5'?'B':'F').$value_22->serie.'-'.$value_22->numero),1,0,'L');
                                            $x_2=$pdf::GetX();
                                            $y_2=$pdf::GetY();
                                            $pdf::Multicell(45,9,substr($cad_det,0,-1),0,'C');
                                            $pdf::SetXY($x_2,$y_2);
                                            $pdf::Cell(45,9,"",1,0,'L');
                                              // $pdf::Multicell(80,3,($nombre),0,'L');
                          
                                            // $pdf::Cell(45,9, substr($cad_det,0,-1),1,0,'C');
                                           $pdf::Cell(15,9,number_format($value_22->total,2,'.',''),1,0,'R');                                        
                                         }elseif (strlen($cad_det)>=45 And strlen($cad_det) < 70) {
                                            $pdf::Cell(8,12,$c_22,1,0,'C');
                                            $pdf::Cell(18,12,(($value_22->tipo_doc =='5'?'B':'F').$value_22->serie.'-'.$value_22->numero),1,0,'L');
                                            $x_2=$pdf::GetX();
                                            $y_2=$pdf::GetY();
                                            $pdf::Multicell(45,12,substr($cad_det,0,-1),0,'C');
                                            $pdf::SetXY($x_2,$y_2);
                                            $pdf::Cell(45,12,"",1,0,'L');
                                              // $pdf::Multicell(80,3,($nombre),0,'L');
                          
                                            // $pdf::Cell(45,9, substr($cad_det,0,-1),1,0,'C');
                                            $pdf::Cell(15,12,number_format($value_22->total,2,'.',''),1,0,'R');                                        
                                        }elseif (strlen($cad_det)>=70 And strlen($cad_det) <= 120) {
                                            $pdf::Cell(8,17,$c_22,1,0,'C');
                                            $pdf::Cell(18,17,(($value_22->tipo_doc =='5'?'B':'F').$value_22->serie.'-'.$value_22->numero),1,0,'L');
                                            $x_2=$pdf::GetX();
                                            $y_2=$pdf::GetY();
                                            $pdf::Multicell(45,17,substr($cad_det,0,-1),0,'C');
                                            $pdf::SetXY($x_2,$y_2);
                                            $pdf::Cell(45,17,"",1,0,'L');
                                              // $pdf::Multicell(80,3,($nombre),0,'L');
                          
                                            // $pdf::Cell(45,9, substr($cad_det,0,-1),1,0,'C');
                                            $pdf::Cell(15,17,number_format($value_22->total,2,'.',''),1,0,'R');                                        
                                        }elseif(strlen($cad_det) <=23){
                                            $pdf::Cell(8,6 ,$c_22,1,0,'C');
                                            $pdf::Cell(18,6,(($value_22->tipo_doc =='5'?'B':'F').$value_22->serie.'-'.$value_22->numero),1,0,'L');
                                            $pdf::Cell(45,6, substr($cad_det,0,-1),1,0,'C');
                                            $pdf::Cell(15,6,number_format($value_22->total,2,'.',''),1,0,'R');                                        
                                        }elseif(strlen($cad_det) > 120){
                                            $cad_det = substr($cad_det, 0,120);
                                            $pdf::Cell(8,20,$c_22,1,0,'C');
                                            $pdf::Cell(18,20,(($value_22->tipo_doc =='5'?'B':'F').$value_22->serie.'-'.$value_22->numero),1,0,'L');
                                            $x_2=$pdf::GetX();
                                            $y_2=$pdf::GetY();
                                            $pdf::Multicell(45,20,substr($cad_det,0,-1),0,'C');
                                            $pdf::SetXY($x_2,$y_2);
                                            $pdf::Cell(45,20,"",1,0,'L'); 
                                            $pdf::Cell(15,20,number_format($value_22->total,2,'.',''),1,0,'R');    
                                        }
                                        $pdf::Ln();
                                        $c_22++;
                                        $total_farmacia+=$value_22->total;                                
                                    }
                                }else{
                                    // $pdf::Cell(45,8,'-',1,0,'C');
                                    
                                }
                            }
                        }
                    }                    
                }

                if($c_22 == 1){
                    $pdf::Cell(8,6 ,$c_22,1,0,'C');
                    $pdf::Cell(18,6 ,'-',1,0,'C');
                    $pdf::Cell(45,6 ,'-',1,0,'C');
                    $pdf::Cell(15,6,number_format($total_farmacia,2,'.',''),1,0,'R');
                    $pdf::Ln();                        
                }   
                // $y_dd = $pdf::GetY();
                // dd($y_dd);
                // if ($y_dd <=100) {
                //     $pdf::SetY($pdf::GetY()+8);
                // }else{
                    // $pdf::SetY($y_dd+6);
                // }

                $pdf::SetFont('helvetica','B',10);
                $pdf::Cell(52,5,utf8_decode('TOTAL:'),0,0,'R');
                $pdf::Cell(35,5,number_format($total_farmacia,2,'.',''),0,0,'R');
                // $pdf::Ln();
                $pdf::Ln();


              
                // $alt = $pdf::GetY();
                // $acum = 8;
                // // dd($alt);
                // //alt = 113.340916
                // if ($alt < 135) {
                //     $pdf::setXY(115,80); 
                // }else{
                //     $pdf::SetXY(115,86);
                // }
                // dd($paciente_id);
                       
                $resultado_331 = Movimiento::join('detallemovcaja as det_m','det_m.movimiento_id','=','movimiento.movimiento_id')
                    ->where('det_m.descripcion','LIKE','%GARANTIA%')->where('movimiento.persona_id','=',$paciente_id)
                    ->where('movimiento.created_at','>=',$fecha.'00:00:00')
                    ->whereNotNull('movimiento.serie')
                    ->whereNull('movimiento.deleted_at')
                    ->where('movimiento.tipodocumento_id','=','5')
                    ->whereIn('movimiento.situacion',['N'])
                    ->orderBy('movimiento.fecha','DESC')
                    ->select('movimiento.id','movimiento.serie','movimiento.numero','movimiento.total','movimiento.tipodocumento_id as tipo_doc')
                    ->groupBy('movimiento.id');

                    // dd($resultado_331);

                $resultado_33 = Movimiento::leftJoin('movimiento as mov2','mov2.id','=','movimiento.movimiento_id')
                ->join('detallemovcaja as det_m','det_m.movimiento_id','=','mov2.id')
                ->where('det_m.descripcion','LIKE','%GARANTIA%')
                ->where('movimiento.created_at','>=',$fecha.'00:00:00')
                ->whereNull('movimiento.deleted_at')
                ->whereNotNull('movimiento.serie')
                ->whereIn('movimiento.situacion',['N'])
                ->where('mov2.persona_id','=',$paciente_id)
                ->where('movimiento.tipodocumento_id','=','4')
                ->orderBy('movimiento.fecha','DESC')
                ->select('movimiento.id','movimiento.serie','movimiento.numero','movimiento.total','movimiento.tipodocumento_id as tipo_doc')
                ->groupBy('movimiento.id')
                ->unionAll($resultado_331);
                // ->get();
                
                $resultado_33 = $resultado_33->get();
                // dd($resultado_33,$fecha);

                $y_actual = $pdf::GetY();
                $x_actual = $pdf::GetX();
                $pdf::SetFont('helvetica','B',9);
                // dd($y_pendiente, $y_actual);
                if(!($y_actual < $y_pendiente)){
                    $pdf::SetXY(115,$y_pendiente);
                    $pdf::Cell(80,7,"GARANTIA",0,0,'L');
                    $pdf::Ln();
                        
                    $pdf::SetX(115);
                    $pdf::Cell(8,6,("Item"),1,0,'C');
                    $pdf::Cell(18,6,'Doc.',1,0,'C');
                    $pdf::Cell(15,6,("Sub Tot."),1,0,'C');
                    $pdf::Ln();
                    
                    $c_33 =1;
                    $total_garantias = 0;
                    $aum = 0;
                    $pdf::SetFont('helvetica','',7.5);
                    // $y = $pdf::GetY();

                    // if($alt < 135){
                    //     $pdf::SetXY(115,95);
                    // }else{
                    //     $pdf::SetXY(115,101);
                    // }
                    // $y_pendiente02 = $y_pendiente;
                    $pdf::SetX(115);
                    if(count($resultado_33) > 0 ){           
                        foreach ($resultado_33 as $key_33 => $value_33) {
                            // $aum+=19;
                            
                            $pdf::Cell(8,6,$c_33,1,0,'C');
                            $pdf::Cell(18,6 ,(($value_33->tipo_doc =='5'?'B':'F').$value_33->serie.'-'.$value_33->numero),1,0,'L');
                            $pdf::Cell(15,6,number_format($value_33->total,2,'.',''),1,0,'R');
                            
                            $pdf::Ln();
                            $c_33++;
                            $total_garantias+=$value_33->total;
                            $pdf::SetX(115);  
                            // $pdf::SetXY(115,$y_pendiente+$aum);  
                            
                         }

                        // exit();
                    }else{
                        $pdf::Cell(8,6 ,$c_33,1,0,'C');
                        $pdf::Cell(18,6 ,'-',1,0,'C');
                        $pdf::Cell(15,6,number_format($total_garantias,2,'.',''),1,0,'R');
                        $pdf::Ln();
                        // $aum+=8;
                    }

                    // $pdf::setX(115);
                    // $pdf::setY($pdf::GetY()+10);
                    $pdf::SetX(115);
                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(15,5,utf8_decode('TOTAL:'),0,0,'R');
                    $pdf::Cell(27,5,number_format($total_garantias,2,'.',''),0,0,'R');
                    $pdf::Ln();
                    $pdf::Ln();

                    $y = $pdf::GetY();
                    if($y>150){
                        $pdf::SetY($y+85);
                    }else{
                        $pdf::SetY($y+115);
                    }
                    
                    $pdf::SetFont('helvetica','B',12);
                    $pdf::Cell(100,5,utf8_decode('POR REGULARIZAR:'),0,0,'R');
                    $pdf::Cell(0,5,number_format($deuda+$total_farmacia-$total_garantias,2,'.',''),0,0,'C');  
                    $pdf::Ln();

                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(0,5,'Usuario: '.$value->usuario->nombres,0,0,'L');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',8);
               
                    $pdf::Cell(25,8,utf8_encode('Fecha de Impresión: '.date('d/m/Y H:i:s')),0,0,'L');
                    $pdf::Ln();

                }else{

                    $pdf::Cell(80,7,"GARANTIA",0,0,'L');
                    $pdf::Ln();           
                    // $pdf::SetX(115);
                    $pdf::Cell(8,6,("Item"),1,0,'C');
                    $pdf::Cell(18,6,'Doc.',1,0,'C');
                    $pdf::Cell(15,6,("Sub Tot."),1,0,'C');
                    $pdf::Ln();
                    
                    $c_33 =1;
                    $total_garantias = 0;
                    // $aum = 8;
                    $pdf::SetFont('helvetica','',7.5);
                    $pdf::SetX(115);
                    if(count($resultado_33) > 0 ){           
                        foreach ($resultado_33 as $key_33 => $value_33) {
                            $pdf::Cell(8,6,$c_33,1,0,'C');
                            $pdf::Cell(18,6 ,(($value_33->tipo_doc =='5'?'B':'F').$value_33->serie.'-'.$value_33->numero),1,0,'L');
                            $pdf::Cell(15,6,number_format($value_33->total,2,'.',''),1,0,'R');
                            
                            $pdf::Ln();
                            $pdf::SetX(115);
                            $c_33++;
                            $total_garantias+=$value_33->total;
                            // $pdf::SetXY(115,$y_pendiente+$aum);
                        
                            // $aum+=8;
                        }
                    }else{
                        $pdf::Cell(8,6 ,$c_33,1,0,'C');
                        $pdf::Cell(18,6 ,'-',1,0,'C');
                        $pdf::Cell(15,6,number_format($total_garantias,2,'.',''),1,0,'R');
                        $pdf::Ln();
                        // $aum+=8;
                    }

                    // $pdf::setX(115);
                     // $y_dd = $pdf::GetY();
                    // dd($y_dd);
                    // if ($y_dd <=100) {
                    //     $pdf::SetY($pdf::GetY()+8);
                    // }else{
                    // $pdf::SetY($y_dd+15);

                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(15,5,utf8_decode('TOTAL:'),0,0,'R');
                    $pdf::Cell(27,5,number_format($total_garantias,2,'.',''),0,0,'R');
                    $pdf::Ln();

                    // $y = $pdf::GetY();
                    // $pdf::SetY($y_actual);
                    $pdf::SetFont('helvetica','B',12);
                    $pdf::Cell(100,5,utf8_decode('POR REGULARIZAR:'),0,0,'R');
                    $pdf::Cell(0,5,number_format($deuda+$total_farmacia-$total_garantias,2,'.',''),0,0,'C');  
                    $pdf::Ln();

                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(0,5,'Usuario: '.$value->usuario->nombres,0,0,'L');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',8);
               
                    $pdf::Cell(25,8,utf8_encode('Fecha de Impresión: '.date('d/m/Y H:i:s')),0,0,'L');
                    $pdf::Ln();
                }

               $pdf::Output('HojaCosto.pdf');
            }
        }
    }

    public function hospitalizadoautocompletar($searching)
    {
        $resultado        = Hospitalizacion::join('habitacion as h','h.id','=','hospitalizacion.habitacion_id')
                            ->join('historia','historia.id','=','hospitalizacion.historia_id')
                            ->join('person as paciente','paciente.id','=','historia.person_id')
                            ->where('hospitalizacion.situacion','=','H')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($searching).'%')
                            ->select('hospitalizacion.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array(
                            'label' => trim($value->historia->persona->apellidopaterno.' '.$value->historia->persona->apellidomaterno.' '.$value->historia->persona->nombres),
                            'id'    => $value->id,
                            'historia' => $value->historia->numero,
                            'tipopaciente' => $value->historia->tipopaciente,
                            'value' => trim($value->historia->persona->apellidopaterno.' '.$value->historia->persona->apellidomaterno.' '.$value->historia->persona->nombres),
                        );
        }
        return json_encode($data);
    }

    public function agregardetalle(Request $request){
        $resultado        = Detallehojacosto::where('detallehojacosto.hojacosto_id', '=', $request->input('id'))
                            /*->select('*')*/;
        $lista            = $resultado->get();
        //echo json_encode($lista);exit();
        $data = array();
        foreach($lista as $k => $v){
            $servicio = 
            $data[] = array("idservicio"=> $v->servicio_id,
                            "servicio" => ($v->servicio_id>0?$v->servicio->nombre:$v->descripcion),
                            "precio" => $v->precio,
                            "cantidad" => $v->cantidad,
                            "servicio2" => $v->descripcion,
                            "tiposervicio" => ($v->servicio_id>0?$v->servicio->tiposervicio->nombre:'VARIOS'),
                            "idtiposervicio" => ($v->servicio_id>0?$v->servicio->tiposervicio_id:0),
                            "idmedico" => $v->persona_id,
                            "medico" => $v->persona->apellidopaterno.' '.$v->persona->apellidomaterno.' '.$v->persona->nombres);
        }
        return json_encode($data);
    }

}
