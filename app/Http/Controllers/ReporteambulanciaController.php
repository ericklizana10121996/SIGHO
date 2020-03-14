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

class ReporteambulanciaController extends Controller
{
    protected $folderview      = 'app.reporteambulancia';
    protected $tituloAdmin     = 'Reporte de Ambulancia';
    protected $tituloRegistrar = 'Registrar Venta';
    protected $tituloModificar = 'Modificar Egreso';
    protected $tituloCobrar = 'Cobrar Venta';
    protected $tituloAnular  = 'Anular Venta';
    protected $rutas           = array('create' => 'reporteambulancia.create', 
            'edit'   => 'reporteegresotesoreria.edit', 
            'anular' => 'movimientocaja.anular',
            'search' => 'reporteambulancia.buscar',
            'index'  => 'reporteambulancia.index',
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
        $entidad          = 'Reporteambulancia';
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('movimiento as mref',function($join){
                                $join->on('mref.movimiento_id', '=', 'movimiento.id')
                                     ->whereNotIn('mref.situacion',['U']);
                            })
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('persona').'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->where('movimiento.situacion','<>','U')
                            ->whereNotIn('dmc.situacionentrega',['A'])
                            //->where('movimiento.plan_id','=','6')
                            ->where('dmc.persona_id','=','292')
                            /*->where(function($query){
                                $query->where('s.nombre','not like','%OXIGENO%')
                                      ->orWhere('dmc.descripcion','not like','%OXIGENO%');
                            })
                            ->where(function($query){
                                $query->where('s.nombre','like','%AMBULANCIA%')
                                      ->orWhere('dmc.descripcion','like','%AMBULANCIA%');
                            })*/
                            ->where(function($query){
                                $query->whereNull('dmc.situaciontarjeta')
                                      ->orWhere(function($q){
                                        $q->whereNotIn('dmc.situaciontarjeta',['A']);
                                      });
                            });
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

        $resultado        = $resultado->select('movimiento.numero','movimiento.fecha','dmc.precio as total',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('concat(case when mref.tipodocumento_id=5 then \'B\' else \'F\' end,mref.serie,\'-\',mref.numero) as numero2'),'dmc.descripcion as servicio2','s.nombre as servicio','dmc.servicio_id')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        
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
        $entidad          = 'Reporteambulancia';
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
        $entidad             = 'Reporteambulancia';
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


    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('movimiento as mref',function($join){
                                $join->on('mref.movimiento_id', '=', 'movimiento.id')
                                     ->whereNotIn('mref.situacion',['U']);
                            })
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('persona').'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->where('movimiento.situacion','<>','U')
                            ->whereNotIn('dmc.situacionentrega',['A'])
                            ->where('movimiento.plan_id','=','6')
                            ->where('dmc.persona_id','=','292')
                            /*->where(function($query){
                                $query->where('s.nombre','not like','%OXIGENO%')
                                      ->orWhere('dmc.descripcion','not like','%OXIGENO%');
                            })
                            ->where(function($query){
                                $query->where('s.nombre','like','%AMBULANCIA%')
                                      ->orWhere('dmc.descripcion','like','%AMBULANCIA%');
                            })*/
                            ->where(function($query){
                                $query->whereNull('dmc.situaciontarjeta')
                                      ->orWhere(function($q){
                                        $q->whereNotIn('dmc.situaciontarjeta',['A']);
                                      });
                            });
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

        $resultado        = $resultado->select('movimiento.numero','movimiento.fecha','dmc.precio as total',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('concat(case when mref.tipodocumento_id=5 then \'B\' else \'F\' end,mref.serie,\'-\',mref.numero) as numero2'),'dmc.descripcion as servicio2','s.nombre as servicio','dmc.servicio_id')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('movimiento as mref',function($join){
                                $join->on('mref.movimiento_id', '=', 'movimiento.id')
                                     ->whereNotIn('mref.situacion',['U']);
                            })
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$request->input('persona').'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->where('movimiento.situacion','<>','U')
                            ->whereNotIn('dmc.situacionentrega',['A'])
                            ->where('movimiento.plan_id','<>','6')
                            ->where('dmc.persona_id','=','292')
                            /*->where(function($query){
                                $query->where('s.nombre','not like','%OXIGENO%')
                                      ->orWhere('dmc.descripcion','not like','%OXIGENO%');
                            })
                            ->where(function($query){
                                $query->where('s.nombre','like','%AMBULANCIA%')
                                      ->orWhere('dmc.descripcion','like','%AMBULANCIA%');
                            })*/
                            ->where(function($query){
                                $query->whereNull('dmc.situaciontarjeta')
                                      ->orWhere(function($q){
                                        $q->whereNotIn('dmc.situaciontarjeta',['A']);
                                      });
                            });
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

        $resultado        = $resultado->select('movimiento.numero','movimiento.fecha','dmc.precio as total',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('concat(case when mref.tipodocumento_id=5 then \'B\' else \'F\' end,mref.serie,\'-\',mref.numero) as numero2'),'dmc.descripcion as servicio2','s.nombre as servicio','dmc.servicio_id')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista1           = $resultado->get();

        Excel::create('ExcelAmbulancia', function($excel) use($lista,$request,$lista1) {
 
            $excel->sheet('Ambulancia', function($sheet) use($lista,$request,$lista1) {
                $caja = Caja::find($request->input('caja_id'));
                $celdas      = 'A1:H1';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $title[] = "Reporte de Ambulancia Particular del ".date("d/m/Y",strtotime($request->input('fechainicial')))." al ".date("d/m/Y",strtotime($request->input('fechafinal')));
                $sheet->row(1,$title);
                $cabecera[] = "Fecha" ;               
                $cabecera[] = "Paciente";
                $cabecera[] = "Nro";
                $cabecera[] = "Total" ;  
                $cabecera[] = "Alerta Vital" ;  
                $cabecera[] = "Juan Pablo" ;  
                $cabecera[] = "Tipo Paciente" ;  
                $cabecera[] = "Servicio" ;  
                $sheet->cells("A3:H3", function($cells) {
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
                        $detalle = array();
                        $detalle[] = date("d/m/Y",strtotime($value->fecha));
                        $detalle[] = $value->paciente2;                    
                        $detalle[] = $value->numero2;
                        $detalle[] = $value->total;
                        $detalle[] = number_format($value->total*0.8,2,'.','');
                        $detalle[] = number_format($value->total*0.2,2,'.','');
                        $detalle[] = 'PARTICULAR';
                        if($value->servicio_id>0)
                            $detalle[] = $value->servicio;
                        else
                            $detalle[] = $value->servicio2;
                        $sheet->row($c,$detalle);
                        $sheet->cells("A".$c.":H".$c, function($cells) {
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
                    $totalg=$totalg+$total;
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "TOTAL";
                    $detalle[] = number_format($totalg,2,'.','');
                    $detalle[] = number_format($totalg*0.8,2,'.','');
                    $sheet->row($c,$detalle);
                    $sheet->cells('C'.$c.':E'.$c, function($cells) {
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

                $c=$c+4;
                $celdas      = 'A'.$c.':H'.$c;
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $title=array();
                $title[] = "Reporte de Ambulancia Convenio del ".date("d/m/Y",strtotime($request->input('fechainicial')))." al ".date("d/m/Y",strtotime($request->input('fechafinal')));
                $sheet->row($c,$title);
                $c=$c+2;
                $cabecera=array();
                $cabecera[] = "Fecha" ;               
                $cabecera[] = "Paciente";
                $cabecera[] = "Nro";
                $cabecera[] = "Total" ;  
                $cabecera[] = "Alerta Vital" ;  
                $cabecera[] = "Juan Pablo" ;  
                $cabecera[] = "Tipo Paciente" ;  
                $cabecera[] = "Servicio" ;  
                $sheet->cells("A".$c.":H".$c, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setValignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });             
                $sheet->row($c,$cabecera);
                $c=$c+1;
                if(count($lista1)>0){
                    $band=true;$area="";$total=0;$totalg=0;$d2=0;
                    foreach ($lista1 as $key => $value){
                        $detalle = array();
                        $detalle[] = date("d/m/Y",strtotime($value->fecha));
                        $detalle[] = $value->paciente2;                    
                        $detalle[] = 'PREF '.$value->numero;
                        $detalle[] = $value->total;
                        $detalle[] = '';
                        $detalle[] = '';
                        $detalle[] = 'CONVENIO';
                        if($value->servicio_id>0)
                            $detalle[] = $value->servicio;
                        else
                            $detalle[] = $value->servicio2;
                        $sheet->row($c,$detalle);
                        $sheet->cells("A".$c.":H".$c, function($cells) {
                            $cells->setBorder('thin','thin','thin','thin');
                            $cells->setFont(array(
                                'family'     => 'Calibri',
                                'size'       => '10',
                                ));
                        });
                        $total = $total + $value->total;
                        $c++;
                    }
                    $totalg=$totalg+$total;
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = "TOTAL";
                    $detalle[] = '';
                    $detalle[] = '';
                    $sheet->row($c,$detalle);
                    $sheet->cells('C'.$c.':E'.$c, function($cells) {
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
}
