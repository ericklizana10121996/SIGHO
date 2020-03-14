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
use App\Area;
use App\Caja;
use App\Conceptopago;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Detallemovcaja;
use App\Librerias\EnLetras;
use Illuminate\Support\Facades\Auth;
use Excel;

class ReporteegresotesoreriaController extends Controller
{
    protected $folderview      = 'app.reporteegresotesoreria';
    protected $tituloAdmin     = 'Reporte de Egreso Tesoreria';
    protected $tituloRegistrar = 'Registrar Venta';
    protected $tituloModificar = 'Modificar Egreso';
    protected $tituloCobrar = 'Cobrar Venta';
    protected $tituloAnular  = 'Anular Venta';
    protected $rutas           = array('create' => 'reporteegresotesoreria.create', 
            'edit'   => 'reporteegresotesoreria.edit', 
            'anular' => 'movimientocaja.anular',
            'search' => 'reporteegresotesoreria.buscar',
            'index'  => 'reporteegresotesoreria.index',
            'detalle' => 'movimientocaja.detalle',
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
        $entidad          = 'Reporteegresotesoreria';
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago', 'conceptopago.id', '=', 'movimiento.conceptopago_id')
                            ->join('area','area.id','=','movimiento.area_id')
                            ->where('movimiento.situacion','<>','A')
                            ->where('conceptopago.id','<>','1')
                            ->where('conceptopago.id','<>','2')
                            ->where('conceptopago.tipo','like','E')
                            ->where('area.nombre','like','%'.strtoupper($request->input('area')).'%')
                            ->where('conceptopago.nombre','like','%'.strtoupper($request->input('concepto')).'%')
                            ->where('movimiento.caja_id','=',$request->input('caja_id'));
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
        }
        if($request->input('formapago')!=""){
            $resultado = $resultado->where('movimiento.numeroficha','like',$request->input('formapago'));
        }
        if($request->input('persona')!=""){
            $resultado = $resultado->where(function($q) use($request){
                        $q->where(DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('persona').'%')
                          ->orWhere('paciente.bussinesname','like','%'.$request->input('persona').'%');
                          });
        }

        $resultado        = $resultado->select('movimiento.*',DB::raw('case when paciente.bussinesname is null then CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else paciente.bussinesname end  as persona2'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Area', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Concepto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Forma Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        
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
        $entidad          = 'Reporteegresotesoreria';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        $cboCaja = array('6'=>'Tesoreria','7'=>'Farmacia - Tesoreria');
        $cboFormapago = array('' => 'Todos', 'Contado' => 'Contado', 'Deposito' => 'Deposito');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user','cboCaja', 'cboFormapago'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Ventaadmision';
        $Venta = null;
        $cboConvenio = array();
        $convenios = Convenio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($convenios as $key => $value) {
            $cboConvenio = $cboConvenio + array($value->id => $value->nombre);
        }
        $formData            = array('Venta.store');
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('Venta', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente', 'cboConvenio'));
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
                'numero'          => 'required',
                );
        $mensajes = array(
            'doctor.required'         => 'Debe seleccionar un doctor',
            'especialidad.required'         => 'Debe seleccionar una especialidad',
            'paciente.required'         => 'Debe seleccionar un paciente',
            'numero.required'         => 'Debe seleccionar una historia',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       
        
        $error = DB::transaction(function() use($request){
            $Venta       = new Venta();
            $Venta->fecha = $request->input('fecha');
            $person_id = $request->input('person_id');
            if($person_id==""){
                $person_id = null;
            }
            $historia_id = $request->input('historia_id');
            if($historia_id==""){
                $historia_id = null;
            }
            $Venta->paciente_id = $person_id;
            $Venta->historia_id = $historia_id;
            $Venta->doctor_id = $request->input('doctor_id');
            $Venta->situacion='P';//Pendiente
            $Venta->horainicio = $request->input('horainicio');
            $Venta->horafin = $request->input('horafin');
            $Venta->comentario = $request->input('comentario');
            $Venta->telefono = $request->input('telefono');
            $Venta->paciente = $request->input('paciente');
            $Venta->historia = $request->input('numero');
            $Venta->tipopaciente = $request->input('tipopaciente');
            $Venta->save();
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
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $Movimientocaja = Movimiento::find($id);
        $cboConcepto = array();
        $rs = Conceptopago::where('tipo','like','E')->where('id','<>',2)->where('id','<>',13)->where('id','<>',24)->where('id','<>',25)->where('id','<>',26)->where('id','<>',20)->where('id','<>',8)->where('id','<>',18)->where('id','<>',16)->where('id','<>',14)->where('id','<>',31)->where('tesoreria','like','S')->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboConcepto = $cboConcepto + array($value->id => $value->nombre);
        }
        $cboArea = array();
        $rs = Area::orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboArea = $cboArea + array($value->id => $value->nombre);
        }
        $entidad             = 'Reporteegresotesoreria';
        $formData            = array('reporteegresotesoreria.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('Movimientocaja', 'formData', 'entidad', 'boton', 'listar', 'cboConcepto', 'cboArea'));
    }

    public function update(Request $request)
    {
        $existe = Libreria::verificarExistencia($request->input('id'), 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $Movimiento = Movimiento::find($request->input('id'));
            $Movimiento->conceptopago_id=$request->input('concepto');
            $Movimiento->area_id=$request->input('area');
            $Movimiento->comentario=$request->input('comentario');
            $Movimiento->persona_id=$request->input('persona_id');
            $Movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }


    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago', 'conceptopago.id', '=', 'movimiento.conceptopago_id')
                            ->join('area','area.id','=','movimiento.area_id')
                            ->where('movimiento.situacion','<>','A')
                            ->where('conceptopago.id','<>','1')
                            ->where('conceptopago.id','<>','2')
                            ->where('conceptopago.tipo','like','E')
                            ->where('area.nombre','like','%'.strtoupper($request->input('area')).'%')
                            ->where('conceptopago.nombre','like','%'.strtoupper($request->input('concepto')).'%')
                            ->where('movimiento.caja_id','=',$request->input('caja_id'));
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
        }
        if($request->input('persona')!=""){
            $resultado = $resultado->where(function($q) use($request){
                        $q->where(DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('persona').'%')
                          ->orWhere('paciente.bussinesname','like','%'.$request->input('persona').'%');
                          });
        }

        $resultado        = $resultado->select('movimiento.*',DB::raw('case when paciente.bussinesname is null then CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else paciente.bussinesname end as persona2'))->orderBy('area.nombre', 'ASC')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();

        Excel::create('ExcelEgresosTesoreria', function($excel) use($lista,$request) {
 
            $excel->sheet('Egresos', function($sheet) use($lista,$request) {
                $caja = Caja::find($request->input('caja_id'));
                $celdas      = 'A1:D1';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $title[] = "Egresos de ".$caja->nombre." del ".date("d/m/Y",strtotime($request->input('fechainicial')))." al ".date("d/m/Y",strtotime($request->input('fechafinal')));
                $sheet->row(1,$title);
                $cabecera[] = "Area" ;               
                $cabecera[] = "Proveedor";
                $cabecera[] = "Concepto";
                $cabecera[] = "Total" ;  
                $sheet->cells("A3:D3", function($cells) {
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setValignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });             
                $sheet->row(3,$cabecera);
                if(count($lista)>0){
                    $c=4;$d1=4;$band=true;$area="";$total=0;$totalg=0;$d2=0;
                    foreach ($lista as $key => $value){
                        if($area!=$value->area_id){
                            if($area!=""){
                                $celdas      = 'A'.$d1.':A'.$d2;
                                $sheet->mergeCells($celdas);
                                $sheet->cells($celdas, function($cells) {
                                    $cells->setAlignment('center');
                                    $cells->setBorder('thin','thin','thin','thin');
                                    $cells->setValignment('center');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        'bold'       =>  true
                                        ));
                                });
                                $detalle = array();
                                $detalle[] = "";
                                $detalle[] = "SubTotal";
                                $detalle[] = "";
                                $detalle[] = number_format($total,2,'.','');
                                $sheet->row($c,$detalle);
                                $celdas      = 'B'.$c.':C'.$c;
                                $sheet->mergeCells($celdas);
                                $sheet->cells('B'.$c.':D'.$c, function($cells) {
                                    $cells->setAlignment('center');
                                    $cells->setBorder('thin','thin','thin','thin');
                                    $cells->setValignment('center');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        'bold'       =>  true
                                        ));
                                });
                                $c=$c+2; 
                                $d1=$c;   
                                $totalg=$totalg+$total;
                                $total=0;
                            }
                            $detalle = array();
                            $detalle[] = $value->area->nombre;
                            $area=$value->area_id;
                        }else{
                            $detalle = array();
                            $detalle[] = "";

                        }
                        $detalle[] = $value->persona2;                    
                        $detalle[] = $value->conceptopago->nombre;
                        $detalle[] = $value->total;
                        $sheet->row($c,$detalle);
                        $sheet->cells("A".$c.":D".$c, function($cells) {
                            $cells->setBorder('thin','thin','thin','thin');
                            $cells->setFont(array(
                                'family'     => 'Calibri',
                                'size'       => '10',
                                ));
                        });
                        $total = $total + $value->total;
                        $c++;
                        $d2=$c;
                    }
                    $celdas      = 'A'.$d1.':A'.$d2;
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "SubTotal";
                    $detalle[] = number_format($total,2,'.','');
                    $sheet->row($c,$detalle);
                    $celdas      = 'B'.$c.':C'.$c;
                    $sheet->mergeCells($celdas);
                    $sheet->cells('B'.$c.':D'.$c, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setValignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });
                    $c=$c+2;    
                    $totalg=$totalg+$total;
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "TOTAL GENERAL";
                    $detalle[] = "";
                    $detalle[] = number_format($totalg,2,'.','');
                    $sheet->row($c,$detalle);
                    $celdas      = 'B'.$c.':C'.$c;
                    $sheet->mergeCells($celdas);
                    $sheet->cells('B'.$c.':D'.$c, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setValignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });                    
                }
            });
        })->export('xls');
    }

    public function excel2(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago', 'conceptopago.id', '=', 'movimiento.conceptopago_id')
                            ->join('area','area.id','=','movimiento.area_id')
                            ->where('movimiento.situacion','<>','A')
                            ->where('conceptopago.id','<>','1')
                            ->where('conceptopago.id','<>','2')
                            ->where('conceptopago.tipo','like','E')
                            ->where('area.nombre','like','%'.strtoupper($request->input('area')).'%')
                            ->where('conceptopago.nombre','like','%'.strtoupper($request->input('concepto')).'%')
                            ->where('movimiento.caja_id','=',$request->input('caja_id'));
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
        }
        if($request->input('persona')!=""){
            $resultado = $resultado->where(function($q) use($request){
                        $q->where(DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('persona').'%')
                          ->orWhere('paciente.bussinesname','like','%'.$request->input('persona').'%');
                          });
        }

        $resultado        = $resultado->select('movimiento.*',DB::raw('case when paciente.bussinesname is null then CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else paciente.bussinesname end as persona2'))
                ->orderBy('conceptopago.nombre', 'ASC')
                //->orderBy('movimiento.fecha', 'ASC')
                ->orderByRaw('case when paciente.bussinesname is null then CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else paciente.bussinesname end' . ' ASC')
                ->orderBy('area.nombre', 'ASC');
        $lista            = $resultado->get();

        Excel::create('ExcelEgresosTesoreria', function($excel) use($lista,$request) {
 
            $excel->sheet('Egresos', function($sheet) use($lista,$request) {
                $caja = Caja::find($request->input('caja_id'));
                $celdas      = 'A1:E1';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $title[] = "Egresos de ".$caja->nombre." del ".date("d/m/Y",strtotime($request->input('fechainicial')))." al ".date("d/m/Y",strtotime($request->input('fechafinal')));
                $sheet->row(1,$title);
                $cabecera[] = "Concepto" ;               
                $cabecera[] = "Proveedor";
                $cabecera[] = "Area";
                $cabecera[] = "Comentario";
                $cabecera[] = "Veces";
                $cabecera[] = "Total" ;  
                $sheet->cells("A3:F3", function($cells) {
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setValignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });             
                $sheet->row(3,$cabecera);
                if(count($lista)>0){
                    $c=4;$d1=4;$band=true;$area="";$total=0;$totalg=0;$d2=0;$personaTemp = "";$personaVeces = 0;$lastDetalle=array();
                    $estructura = array();
                    $agruparFecha = array(90,27);
                    $agruparFechaComentario = array();
                    $detalleComentario = array(27);
                    foreach ($lista as $key => $value){
                        if(empty($estructura[$value->conceptopago_id])){
                            $estructura[$value->conceptopago_id] = array(
                                $value->conceptopago->nombre,
                                array()
                            );
                        }
                        if(empty($estructura[$value->conceptopago_id][1][$value->persona_id])){
                            $estructura[$value->conceptopago_id][1][$value->persona_id] = array(
                                    $value->persona2,
                                    array()
                                );
                        }
                        if(empty($estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id])){
                            $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id] = array(
                                    $value->area->nombre,array(0,0,"",array())
                                );
                        }
                        $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][0] = $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][0] + 1;
                        $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][1] = $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][1] + $value->total;
                        $comen = "";
                        if(in_array($value->conceptopago_id, $agruparFechaComentario)){
                            $comen = $value->comentario." (".$value->fecha.")";
                            $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][2] = $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][2] . $comen . "; ";
                        }elseif(!in_array($value->conceptopago_id, $agruparFecha)){
                            $comen = $value->comentario;
                            if(!in_array(str_replace(' ', '', trim(strtoupper($comen))), $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][3])){
                                $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][3][] = str_replace(' ', '', trim(strtoupper($comen)));
                                $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][2] = $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][2] . $comen . ", ";
                            }
                        }elseif(in_array($value->conceptopago_id, $detalleComentario)){
                            $comen = $value->comentario;
                            $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][2] = "SEPARAR";
                            $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][3][] = array($comen,$value->fecha,$value->total);
                            $cabecera[4] = "Fecha";
                            $sheet->row(3,$cabecera);
                        }else{
                            $comen = $value->fecha;
                            //dd($comen);
                            $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][2] = $estructura[$value->conceptopago_id][1][$value->persona_id][1][$value->area_id][1][2] . $value->fecha . ", ";
                        }
                    }
                    $agruparConcepto = array(43,46,63,28,72,11,116,90,52,42,37);
                    $agruparArea = array();
                    foreach ($estructura as $key => $value){
                        if(in_array($key, $agruparConcepto)){
                            $totalConcepto = 0;
                            $vecesConcepto = 0;
                            $comentarioConcepto = "";
                            foreach ($value[1] as $key2 => $value2) {
                                foreach ($value2[1] as $key3 => $value3) {
                                    $vecesConcepto = $vecesConcepto + $value3[1][0];
                                    $totalConcepto = $totalConcepto + $value3[1][1];
                                    $comentarioConcepto = $comentarioConcepto . $value3[1][2];
                                }
                            }
                            if(!in_array($key, $agruparFecha)){
                                $comentarioConcepto = "";
                            }else{
                                //dd($key,$comentarioConcepto);
                            }
                            $estructura[$key][1] = array("1"=>array($value[0],array("1"=>array("VARIAS AREAS",array($vecesConcepto,$totalConcepto,$comentarioConcepto,array())))));
                        }elseif(in_array($key, $agruparArea)){
                            $totalesArea = array();
                            foreach ($value[1] as $key2 => $value2) {
                                foreach ($value2[1] as $key3 => $value3) {
                                    if(empty($totalesArea[$key3])){
                                        $totalesArea[$key3] = array($value3[0],0,0,"");
                                    }
                                    $totalesArea[$key3][1] = $totalesArea[$key3][1] + $value3[1][0];
                                    $totalesArea[$key3][2] = $totalesArea[$key3][2] + $value3[1][1];
                                    $totalesArea[$key3][3] = $totalesArea[$key3][3] . $value3[1][2];
                                }
                            }
                            $areasConcepto = array();
                            $estructura[$key][1] = array();
                            //dd($totalesArea);
                            foreach ($totalesArea as $key4 => $totalArea) {
                                if(empty($estructura[$key][1][$key4])){
                                    $estructura[$key][1][$key4] = array();
                                }
                                $estructura[$key][1][$key4] = array(
                                    $totalArea[0],
                                    array($key4=>array($totalArea[0],array($totalArea[1],$totalArea[2],$totalArea[3],array()))));
                                //$areasConcepto[$key4] = array($totalArea[0],array($totalArea[1],$totalArea[2],$totalArea[3],array()));
                                //$estructura[$key][1] = array($key4=>array("VARIOS",array($key4=>array($totalArea[0],array($totalArea[1],$totalArea[2],$totalArea[3],array())))));
                            }
                            //dd($estructura[$key]);
                        }
                    }
                    //dd($estructura);
                    /*foreach ($estructura as $key => $value){
                        if($area!=$key){
                            if($area!=""){
                                $celdas      = 'A'.$d1.':A'.$d2;
                                $sheet->mergeCells($celdas);
                                $sheet->cells($celdas, function($cells) {
                                    $cells->setAlignment('center');
                                    $cells->setBorder('thin','thin','thin','thin');
                                    $cells->setValignment('center');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        'bold'       =>  true
                                        ));
                                });
                                $detalle = array();
                                $detalle[] = "";
                                $detalle[] = "SubTotal";
                                $detalle[] = "";
                                $detalle[] = number_format($total,2,'.','');
                                $sheet->row($c,$detalle);
                                $celdas      = 'B'.$c.':D'.$c;
                                $sheet->mergeCells($celdas);
                                $sheet->cells('B'.$c.':E'.$c, function($cells) {
                                    $cells->setAlignment('center');
                                    $cells->setBorder('thin','thin','thin','thin');
                                    $cells->setValignment('center');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        'bold'       =>  true
                                        ));
                                });
                                $c=$c+2; 
                                $d1=$c;   
                                $totalg=$totalg+$total;
                                $total=0;
                            }
                            $detalle = array();
                            $detalle[] = $value->conceptopago->nombre;
                            $area=$value->conceptopago_id;
                            $personaTemp = "";
                            $personaVeces = 0;
                        }else{
                            $detalle = array();
                            $detalle[] = "";

                        }
                        if($key===0){
                            $lastDetalle = array($value->persona2,$value->area->nombre,$value->total);
                            $personaVeces = 1;
                            $personaTemp = $value->persona2."|".$value->area->nombre;
                        }else{
                            if($value->persona2."|".$value->area->nombre==$personaTemp){
                                $personaVeces = $personaVeces + 1;
                                $lastDetalle[2] = $lastDetalle[2] + $value->total;
                            }else{
                                $detalle[] = $lastDetalle[0];
                                $detalle[] = $lastDetalle[1];
                                $detalle[] = $personaVeces;
                                $detalle[] = $lastDetalle[2];
                                $sheet->row($c,$detalle);
                                $sheet->cells("A".$c.":E".$c, function($cells) {
                                    $cells->setBorder('thin','thin','thin','thin');
                                    $cells->setFont(array(
                                        'family'     => 'Calibri',
                                        'size'       => '10',
                                        ));
                                });
                                $total = $total + $lastDetalle[2];
                                $c++;
                                $d2=$c;

                                $lastDetalle = array($value->persona2,$value->area->nombre,$value->total);
                                $personaVeces = 1;
                                $personaTemp = $value->persona2."|".$value->area->nombre;
                            }
                        }
                        if($key===(count($lista) - 1)){
                            $detalle[] = $lastDetalle[0];
                            $detalle[] = $lastDetalle[1];
                            $detalle[] = $personaVeces;
                            $detalle[] = $lastDetalle[2];
                            $sheet->row($c,$detalle);
                            $sheet->cells("A".$c.":E".$c, function($cells) {
                                $cells->setBorder('thin','thin','thin','thin');
                                $cells->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '10',
                                    ));
                            });
                            $total = $total + $lastDetalle[2];
                            $c++;
                            $d2=$c;
                        }
                    }*/
                    $c=4;$d1=4;$band=true;$area="";$total=0;$totalg=0;$d2=0;
                    foreach ($estructura as $key => $value){
                        foreach ($value[1] as $key2 => $value2) {
                            foreach ($value2[1] as $key3 => $value3) {
                                if($value3[1][2] != "SEPARAR"){
                                    if($area!=$key){
                                        if($area!=""){
                                            $celdas      = 'A'.$d1.':A'.$d2;
                                            $sheet->mergeCells($celdas);
                                            $sheet->cells($celdas, function($cells) {
                                                $cells->setAlignment('center');
                                                $cells->setBorder('thin','thin','thin','thin');
                                                $cells->setValignment('center');
                                                $cells->setFont(array(
                                                    'family'     => 'Calibri',
                                                    'size'       => '10',
                                                    'bold'       =>  true
                                                    ));
                                            });
                                            $detalle = array();
                                            $detalle[] = "";
                                            $detalle[] = "SubTotal";
                                            $detalle[] = "";
                                            $detalle[] = "";
                                            $detalle[] = "";
                                            $detalle[] = number_format($total,2,'.','');
                                            $sheet->row($c,$detalle);
                                            $celdas      = 'B'.$c.':E'.$c;
                                            $sheet->mergeCells($celdas);
                                            $sheet->cells('B'.$c.':F'.$c, function($cells) {
                                                $cells->setAlignment('center');
                                                $cells->setBorder('thin','thin','thin','thin');
                                                $cells->setValignment('center');
                                                $cells->setFont(array(
                                                    'family'     => 'Calibri',
                                                    'size'       => '10',
                                                    'bold'       =>  true
                                                    ));
                                            });
                                            $c=$c+2; 
                                            $d1=$c;   
                                            $totalg=$totalg+$total;
                                            $total=0;
                                        }
                                        $detalle = array();
                                        $detalle[] = $key." ".$value[0];
                                        $area=$key;
                                    }else{
                                        $detalle = array();
                                        $detalle[] = "";

                                    }
                                    $detalle[] = $value2[0];
                                    $detalle[] = $value3[0];
                                    $detalle[] = $value3[1][2];
                                    $detalle[] = $value3[1][0];
                                    $detalle[] = $value3[1][1];
                                    $sheet->row($c,$detalle);
                                    $sheet->cells("A".$c.":F".$c, function($cells) {
                                        $cells->setBorder('thin','thin','thin','thin');
                                        $cells->setFont(array(
                                            'family'     => 'Calibri',
                                            'size'       => '10',
                                            ));
                                    });
                                    $total = $total + $value3[1][1];
                                    $c++;
                                    $d2=$c;
                                }else{
                                    foreach ($value3[1][3] as $key4 => $value4) {
                                        if($area!=$key){
                                            if($area!=""){
                                                $celdas      = 'A'.$d1.':A'.$d2;
                                                $sheet->mergeCells($celdas);
                                                $sheet->cells($celdas, function($cells) {
                                                    $cells->setAlignment('center');
                                                    $cells->setBorder('thin','thin','thin','thin');
                                                    $cells->setValignment('center');
                                                    $cells->setFont(array(
                                                        'family'     => 'Calibri',
                                                        'size'       => '10',
                                                        'bold'       =>  true
                                                        ));
                                                });
                                                $detalle = array();
                                                $detalle[] = "";
                                                $detalle[] = "SubTotal";
                                                $detalle[] = "";
                                                $detalle[] = "";
                                                $detalle[] = "";
                                                $detalle[] = number_format($total,2,'.','');
                                                $sheet->row($c,$detalle);
                                                $celdas      = 'B'.$c.':E'.$c;
                                                $sheet->mergeCells($celdas);
                                                $sheet->cells('B'.$c.':F'.$c, function($cells) {
                                                    $cells->setAlignment('center');
                                                    $cells->setBorder('thin','thin','thin','thin');
                                                    $cells->setValignment('center');
                                                    $cells->setFont(array(
                                                        'family'     => 'Calibri',
                                                        'size'       => '10',
                                                        'bold'       =>  true
                                                        ));
                                                });
                                                $c=$c+2; 
                                                $d1=$c;   
                                                $totalg=$totalg+$total;
                                                $total=0;
                                            }
                                            $detalle = array();
                                            $detalle[] = $key." ".$value[0];
                                            $area=$key;
                                        }else{
                                            $detalle = array();
                                            $detalle[] = "";

                                        }
                                        $detalle[] = $value2[0];
                                        $detalle[] = $value3[0];
                                        $detalle[] = $value4[0];
                                        $detalle[] = $value4[1];
                                        $detalle[] = $value4[2];
                                        $sheet->row($c,$detalle);
                                        $sheet->cells("A".$c.":F".$c, function($cells) {
                                            $cells->setBorder('thin','thin','thin','thin');
                                            $cells->setFont(array(
                                                'family'     => 'Calibri',
                                                'size'       => '10',
                                                ));
                                        });
                                        $total = $total + $value4[2];
                                        $c++;
                                        $d2=$c;
                                    }
                                }
                            }
                        }
                    }
                    $celdas      = 'A'.$d1.':A'.$d2;
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "SubTotal";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";
                    //dd($total);
                    $detalle[] = number_format($total,2,'.','');
                    $sheet->row($c,$detalle);
                    $celdas      = 'B'.$c.':E'.$c;
                    $sheet->mergeCells($celdas);
                    $sheet->cells('B'.$c.':F'.$c, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setValignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });
                    $c=$c+2;    
                    $totalg=$totalg+$total;
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "TOTAL GENERAL";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = number_format($totalg,2,'.','');
                    $sheet->row($c,$detalle);
                    $celdas      = 'B'.$c.':E'.$c;
                    $sheet->mergeCells($celdas);
                    $sheet->cells('B'.$c.':F'.$c, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setValignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });                    
                }
            });
        })->export('xls');
    }

    public function excelbonos(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago', 'conceptopago.id', '=', 'movimiento.conceptopago_id')
                            ->join('area','area.id','=','movimiento.area_id')
                            ->where('movimiento.situacion','<>','A')
                            ->where('conceptopago.id','<>','1')
                            ->where('conceptopago.id','<>','2')
                            ->where('conceptopago.tipo','like','E')
                            ->where('area.nombre','like','%'.strtoupper($request->input('area')).'%')
                            ->where('conceptopago.nombre','like','%ADELANTO DE BONO PRODUCTIVIDAD%')
                            ->where('movimiento.caja_id','=',$request->input('caja_id'));
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
        }
        if($request->input('persona')!=""){
            $resultado = $resultado->where(function($q) use($request){
                        $q->where(DB::raw('CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('persona').'%')
                          ->orWhere('paciente.bussinesname','like','%'.$request->input('persona').'%');
                          });
        }

        $resultado        = $resultado->select('movimiento.*',DB::raw("paciente.id as idtra"),DB::raw('MONTH(movimiento.fecha) as mes'),DB::raw('case when paciente.bussinesname is null then CONCAT(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else paciente.bussinesname end as persona2'))
                ->orderBy(DB::raw("case when paciente.bussinesname is null then CONCAT(paciente.apellidopaterno,' ',paciente.apellidomaterno,' ',paciente.nombres) else paciente.bussinesname end"))
                //->orderBy('paciente.apellidopaterno', 'ASC')
                //->orderBy('paciente.apellidomaterno', 'ASC')
                //->orderBy('paciente.nombres', 'ASC')
                ->orderBy('movimiento.fecha', 'ASC')
                ->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();
        //dd($resultado->toSql());
        Excel::create('ExcelEgresosTesoreria', function($excel) use($lista,$request) {
 
            $excel->sheet('Egresos', function($sheet) use($lista,$request) {
                $caja = Caja::find($request->input('caja_id'));
                $celdas      = 'A1:D1';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $meses = array(
                    "1"=>"ENERO",
                    "2"=>"FEBRERO",
                    "3"=>"MARZO",
                    "4"=>"ABRIL",
                    "5"=>"MAYO",
                    "6"=>"JUNIO",
                    "7"=>"JULIO",
                    "8"=>"AGOSTO",
                    "9"=>"SEPTIEMBRE",
                    "10"=>"OCTUBRE",
                    "11"=>"NOVIEMBRE",
                    "12"=>"DICIEMBRE",
                );
                $mesTotales = array(0,0,0,0,0,0,0,0,0,0,0,0);
                $title[] = "Egresos de ".$caja->nombre." del ".date("d/m/Y",strtotime($request->input('fechainicial')))." al ".date("d/m/Y",strtotime($request->input('fechafinal')));
                $sheet->row(1,$title);
                $cabecera[] = "N" ;               
                $cabecera[] = "PERSONAL";
                foreach ($meses as $key => $value) {
                    $cabecera[] =  $value;
                }
                $cabecera[] = "Total" ;  
                $sheet->cells("A3:O3", function($cells) {
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setValignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });             
                $sheet->row(3,$cabecera);
                $detalles = array();
                if(count($lista)>0){
                    $c=4;$d1=4;$band=true;$trabajador="";$total=0;$totalg=0;$d2=0;$i=0;
                    foreach ($lista as $key => $value){ //dd($value);
                        if($trabajador!=trim($value->persona2)){
                            if($trabajador!=""){
                                $detalle[14] = number_format($total,2,'.','');
                                $detalles[] = $detalle;
                                $sheet->row($c,$detalle);
                            }
                            $i++;
                            $detalle = array();
                            $detalle[] = $i;
                            $detalle[] = $value->persona2;
                            foreach ($meses as $mes) {
                                $detalle[] = "0";
                            }
                            $c=$c+1; 
                            $d1=$c;   
                            $totalg=$totalg+$total;
                            $total=0;

                            $trabajador=trim($value->persona2);
                        }
                        $mes = $value->mes;
                        $detalle[$mes+1] = $detalle[$mes+1] + $value->total;
                        $mesTotales[$mes-1] = $mesTotales[$mes-1] + $value->total;
                        $total = $total + $value->total;
                        //$c++;
                        $d2=$c;
                    }
                    $detalle[14] = number_format($total,2,'.','');
                    $detalles[] = $detalle;
                    $sheet->row($c,$detalle);
                    $c=$c+2; 
                    $d1=$c;   
                    $totalg=$totalg+$total;
                    $total=0;

                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "SubTotal";
                    foreach ($mesTotales as $mestotal) {
                        $detalle[] = number_format($mestotal,2,'.','');
                    }
                    $detalle[14] = number_format($totalg,2,'.','');
                    $detalles[] = $detalle;
                    $sheet->row($c,$detalle);

                }
            });
        })->export('xls');
    }

}
