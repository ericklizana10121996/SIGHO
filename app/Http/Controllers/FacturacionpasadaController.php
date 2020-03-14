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

class FacturacionpasadaController extends Controller
{
    protected $folderview      = 'app.facturacionpasada';
    protected $tituloAdmin     = 'Facturacion Pasada';
    protected $tituloRegistrar = 'Registrar Factura Pasada';
    protected $tituloModificar = 'Modificar Siniestro';
    protected $tituloEliminar  = 'Eliminar Factura Pasada';
    protected $rutas           = array('create' => 'facturacionpasada.create', 
            'edit'   => 'facturacionpasada.edit', 
            'delete' => 'facturacionpasada.eliminar',
            'search' => 'facturacionpasada.buscar',
            'index'  => 'facturacionpasada.index',
            'pdfListar'  => 'facturacionpasada.pdfListar',
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
        $entidad          = 'Facturacionpasada';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fecha            = Libreria::getParam($request->input('fechainicial'));
        $fecha2            = Libreria::getParam($request->input('fechafinal'));
        $user = Auth::user();
        if($request->input('usuario')=="Todos"){
            $responsable_id=0;
        }else{
            $responsable_id=$user->person_id;
        }
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where(DB::raw('concat(movimiento.serie,\'-\',movimiento.numero)'),'LIKE','%'.$numero.'%')->where('movimiento.tipodocumento_id','=','17')
                            ->where('movimiento.manual','like','S');
        if($fecha!=""){
            $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fecha.'');
        }
        if($fecha2!=""){
            $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fecha2.'');
        }
        if($responsable_id>0){
            $resultado = $resultado->where('movimiento.responsable_id', '=', $responsable_id);   
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable',DB::raw('plan.razonsocial as empresa'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Empresa', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
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
        $entidad          = 'Facturacionpasada';
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
        $entidad             = 'Facturacionpasada';
        $facturacion = null;
        $cboConvenio = array();
        $convenios = Convenio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($convenios as $key => $value) {
            $cboConvenio = $cboConvenio + array($value->id => $value->nombre);
        }
        $cboTipoServicio = array(""=>"--Todos--");
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $formData            = array('facturacionpasada.store');
        $cboSerie     = array("002" => "002", "008" => "008", "007" => "007");
        $user = Auth::user();
        if($user->id==41){
            $numeroventa = Movimiento::NumeroSigue(9,17,8);    
        }else{
            $numeroventa = Movimiento::NumeroSigue(9,17,2);
        }
        
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('facturacion', 'formData', 'entidad', 'boton', 'listar', 'cboConvenio', 'cboSerie', 'numeroventa', 'cboTipoServicio', 'user'));
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
                'numeroventa'          => 'required',
                'paciente'          => 'required',
                'total'         => 'required',
                'plan'          => 'required',
                );
        $mensajes = array(
            'fecha.required'         => 'Debe seleccionar una fecha',
            'numeroventa.required'         => 'La factura debe tener un numero',
            'paciente.required'         => 'Debe seleccionar un paciente',
            'plan.required'         => 'Debe seleccionar un plan',
            'total.required'         => 'Debe agregar detalle a la factura',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       
        
        $user = Auth::user();
        $dat=array();
        $serie=($request->input('serieventa') + 0);
        $numeroventa = Movimiento::NumeroSigue(9,17,$serie);
        $numero="F".str_pad($request->input('serieventa'),3,'0',STR_PAD_LEFT).'-'.$numeroventa;
        $error = DB::transaction(function() use($request,$user,$numeroventa,$numero,&$dat){
            $venta        = new Movimiento();
            $venta->fecha = $request->input('fecha');
            $venta->fechaingreso = $request->input('fechaingreso');
            $venta->fechaalta = $request->input('fechasalida');
            $venta->numero= $request->input('numeroventa');
            $venta->serie = $request->input('serieventa');
            $venta->responsable_id=$user->person_id;
            $venta->cie_id=$request->input('cie_id');
            $venta->comentario=$request->input('siniestro');
            $venta->soat = $request->input('soat');
            $venta->plan_id = $request->input('plan_id');
            $venta->persona_id = $request->input('person_id');
            $paciente = Person::find($request->input('person_id'));
            $person=Person::where('ruc','LIKE',$request->input('ruc'))->limit(1)->first();
            if(count($person)==0){
                $person = new Person();
                $person->bussinesname = $request->input('plan');
                $person->ruc = $request->input('ruc');
                $person->direccion = $request->input('direccion');
                $person->save();
                $venta->empresa_id=$person->id;
            }else{
                $venta->empresa_id=$person->id;
            }
            if($request->input('igv')=="N"){
                $venta->subtotal=number_format($request->input('total'),2,'.','');
                $venta->igv=number_format(0,2,'.','');
                $venta->total=$request->input('total');     
            }else{
                $venta->subtotal=number_format($request->input('total'),2,'.','');
                $venta->igv=number_format($request->input('total')*0.18,2,'.','');
                $venta->total=number_format($venta->subtotal + $venta->igv,2,'.','');                    
            }
            $venta->tipomovimiento_id=9;
            $venta->tipodocumento_id=17;
            $venta->situacion='P';//Pendiente 
            $venta->ventafarmacia='N';
            $venta->manual='S';
            $venta->copago=$request->input('copago');
            $venta->montoinicial=$request->input('coaseguro');
            $venta->save();

            $pagohospital=0;
            $arr=explode(",",$request->input('listServicio'));
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detallemovcaja();
                $Detalle->movimiento_id=$venta->id;
                if($request->input('txtIdTipoServicio'.$arr[$c])!="0"){
                    //$Detalle->servicio_id=null;
                    $Detalle->servicio_id=$request->input('txtIdServicio'.$arr[$c]);
                    $Detalle->descripcion=trim($request->input('txtServicio'.$arr[$c]));
                }else{
                    $Detalle->servicio_id=null;
                    $Detalle->descripcion=trim($request->input('txtServicio'.$arr[$c]));
                }
                $Detalle->persona_id=$request->input('txtIdMedico'.$arr[$c]);
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                if($request->input('igv')=="N"){
                    $Detalle->precio=round($request->input('txtPrecio'.$arr[$c]),2);
                }else{
                    $Detalle->precio=round($request->input('txtPrecio'.$arr[$c])*1.18,2);
                }
                $Detalle->pagodoctor=$request->input('txtPrecioMedico'.$arr[$c]);
                $Detalle->pagohospital=0;
                $Detalle->descuento=$request->input('txtDias'.$arr[$c]);
                $Detalle->save();
            }
            
            $dat[0]=array("respuesta"=>"OK","id"=>$venta->id);
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
        $movimiento = Movimiento::find($id);
        $entidad             = 'Facturacionpasada';
        $formData            = array('facturacionpasada.update', $id);
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
        /*$existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }*/
        //dd($id);
        $error = DB::transaction(function() use($id){
            DB::delete("DELETE FROM detallemovcaja WHERE movimiento_id = ?",[$id]);
            DB::delete("DELETE FROM movimiento WHERE id = ?",[$id]);
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        /*$existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }*/
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Movimiento::find($id);
        $entidad  = 'Facturacionpasada';
        $formData = array('route' => array('facturacionpasada.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function buscarservicio(Request $request)
    {
        $descripcion = $request->input("descripcion");
        $idtiposervicio = trim($request->input("idtiposervicio"));
        $tipopago = 'Convenio';
        $resultado = Servicio::leftjoin('tarifario','tarifario.id','=','servicio.tarifario_id');
        $resultado = $resultado->where(DB::raw('trim(concat(tarifario.codigo,\' \',servicio.nombre,\' \',tarifario.nombre))'),'LIKE','%'.$descripcion.'%')->where('servicio.plan_id','=',$request->input('plan_id'));
        if(trim($idtiposervicio)!=""){
            $resultado = $resultado->where('tiposervicio_id','=',$idtiposervicio);
        }
        $resultado    = $resultado->where('tipopago','LIKE',''.strtoupper($tipopago).'')->select('servicio.*','tarifario.nombre as tarifario','tarifario.codigo')->get();
        if(count($resultado)>0){
            $c=0;
            foreach ($resultado as $key => $value){
                $data[$c] = array(
                            'servicio' => ($value->tipopago=='Convenio')?$value->tarifario:$value->nombre,
                            'codigo' => ($value->tipopago=='Convenio')?$value->codigo:'-',
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
                                    'codigo' => ($value->tipopago=='Convenio')?$value->codigo:'-',
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
                                    'codigo' => ($value->tipopago=='Convenio')?$value->codigo:'-',
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
        if($request->input('plan_id')>0){
            $plan = Plan::find($request->input('plan_id'));
            if($resultado->tiposervicio_id==1){//CONSULTA
                $data[0] = array(
                    'servicio' => ($resultado->tipopago=='Convenio')?$resultado->tarifario->nombre:$resultado->nombre,
                    'codigo' => $resultado->tarifario->codigo,
                    'tiposervicio' => $resultado->tiposervicio->nombre,
                    'precio' => $plan->consulta,
                    'id' => $resultado->id,
                    'idservicio' => "20".rand(0,1000).$resultado->id,
                    'preciohospital' => $plan->consulta,
                    'preciomedico' => 0,
                    'modo' => $resultado->modo,
                    'idtiposervicio' => $resultado->tiposervicio_id,
                );
            }else{
                $data[0] = array(
                    'servicio' => ($resultado->tipopago=='Convenio')?$resultado->tarifario->nombre:$resultado->nombre,
                    'tiposervicio' => $resultado->tiposervicio->nombre,
                    'codigo' => $resultado->tarifario->codigo,
                    'precio' => round($resultado->precio/1.18,2),
                    'id' => $resultado->id,
                    'idservicio' => "20".rand(0,1000).$resultado->id,
                    'preciohospital' => round($resultado->precio/1.18,2),
                    'preciomedico' => 0,
                    'modo' => $resultado->modo,
                    'idtiposervicio' => $resultado->tiposervicio_id,
                );
            }
        }
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
                $pdf::Cell(60,7,utf8_encode(str_pad($value->serie,4,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),'RBL',0,'C');
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

    public function generarNumero(Request $request){
        $serie = $request->input('serie') + 0;
        $numeroventa = Movimiento::NumeroSigue(9,17,$serie);
        echo $numeroventa;
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

    public function cieautocompletar($searching)
    {
        $resultado        = Cie::where(DB::raw('CONCAT(codigo," ",descripcion)'), 'LIKE', '%'.strtoupper(str_replace("_","",$searching)).'%')->orderBy('codigo', 'ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array(
                            'label' => trim($value->codigo.' '.$value->descripcion),
                            'id'    => $value->id,
                            'value' => trim($value->codigo.' '.$value->descripcion),
                        );
        }
        return json_encode($data);
    }

}
