<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Validator;
use App\Conceptopago;
use App\Movimiento;
use App\Detallemovcaja;
use App\Tiposervicio;
use App\Person;

use App\Librerias\Libreria;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Excel;

class ReporteconveniosistemasController extends Controller
{
    protected $folderview      = 'app.reporteconveniosistemas';
    protected $tituloAdmin     = 'Reporte de Convenio - Sistemas';
    protected $tituloRegistrar = 'Registrar Egreso de Pago';
    protected $tituloModificar = 'Modificar Egreso de Pago';
    protected $tituloEliminar  = 'Eliminar Egreso de Pago';
    protected $rutas           = array('create' => 'Reporteconveniosistemas.create', 
            'edit'   => 'reporteconsulta.edit', 
            'delete' => 'reporteconsulta.eliminar',
            'search' => 'reporteconveniosistemas.buscar',
            'index'  => 'Reporteconveniosistemas.index',
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
        $entidad          = 'Reporteconveniosistemas';
        $doctor           = Libreria::getParam($request->input('doctor'));
        // $tipopaciente     = Libreria::getParam($request->input('tipopaciente'));
        // $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));

        if(is_null($doctor)){
            $doctor = '';
        }

        // $lista = DB::select('CALL sp_reporte_sistemas(?,?,?)',array($doctor, $fechainicial, $fechafinal));      
        // dd($lista);

        $resultado = Movimiento::leftjoin('detallemovcaja as det','det.movimiento_id','=','movimiento.id')
                        ->leftJoin('servicio as s','s.id','=','det.servicio_id')
                        ->leftJoin('tiposervicio as tip','')
                        ->whereBetween('movimiento.fecha',[$fechainicial,$fechafinal])
                        ->whereNull('s.tipopago','=','Convenio')
                        ->whereNotNull('movimiento.plan_id')
                        ->whereNotNull('movimiento.serie')
                        ->whereNotNull('det.servicio_id')
                        ->whereIn('movimiento.situacion',['C','T'])
                        ->select('det.*');

        // dd($resultado);

        // Movimiento::join('conceptopago as concep','concep.id','=','movimiento.conceptopago_id')
        //         ->join('person as doc_ref','doc_ref.id','=','movimiento.persona_id')
        //         ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
        //         // ->leftjoin('detallemovcaja as det','det.movimiento_id','=', 'movimiento.id')
        //         // ->leftjoin('especialidad as esp','doc_ref.especialidad_id','=','esp.id')
        //         ->whereNotNull('doc_ref.especialidad_id')
        //         ->whereIn('conceptopago_id',[8,34,35])
        //         ->orderBy('movimiento.fecha', 'ASC')->orderBy('doctor_resp','ASC')
        //         ->where(DB::raw("CONCAT(doc_ref.apellidopaterno,' ', doc_ref.apellidomaterno,' ',doc_ref.nombres)"), 'LIKE',$doctor.'%')
        //         ->whereBetween('movimiento.fecha',[$fechainicial, $fechafinal])
        //         ->select('movimiento.id as id_mov','movimiento.serie as serie_mov','movimiento.numero as num_mov','movimiento.fecha as fecha_mov',DB::raw("CONCAT(doc_ref.apellidopaterno,' ', doc_ref.apellidomaterno,' ', doc_ref.nombres) as doctor_resp"),'movimiento.total as total_venta', 'concep.nombre as conceptopago', DB::raw("CONCAT(responsable.apellidopaterno,' ', responsable.apellidomaterno,' ',responsable.nombres) as responsable_op"/*,'det.*'*/),'movimiento.comentario'/*,'det.*'*/);


        $lista  =  $resultado->get();
    
        $cabecera         = array();

        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Documento', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
      
        // $cabecera[]       = array('valor' => 'Cant.', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Referido', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Forma Pago', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        // $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        
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
            //$resultado->paginate($filas);
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
        $entidad          = 'ReporteEgresos';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoPaciente          = array("" => "Todos", "P" => "Particular", "C" => "Convenio");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoPaciente'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'ReporteEgresos';
        $conceptopago = null;
        $formData            = array('reportetomografia.store');
        $cboTipo          = array("Ingreso" => "Ingreso", "Egreso" => "Egreso");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('reportetomografia', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
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
                'nombre'                  => 'required|max:200',
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $Conceptopago       = new Conceptopago();
            $Conceptopago->nombre = strtoupper($request->input('nombre'));
            $Conceptopago->tipo = $request->input('tipo');
            $Conceptopago->save();
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
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $conceptopago = Conceptopago::find($id);
        $entidad             = 'conceptopago';
        $formData            = array('Conceptopago.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('conceptopago', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'nombre'                  => 'required|max:200',
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request, $id){
            $categoria                        = Categoria::find($id);
            $categoria->nombre = strtoupper($request->input('nombre'));
            $Conceptopago->tipo = $request->input('tipo');
            $categoria->save();
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
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Conceptopago = Conceptopago::find($id);
            $Conceptopago->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Conceptopago::find($id);
        $entidad  = 'conceptopago';
        $formData = array('route' => array('conceptopago.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $fechainicial     = Libreria::getParam($request->input('fi'));
        $fechafinal       = Libreria::getParam($request->input('ff'));
        
        Excel::create('ReporteConvenios', function($excel) use($request,$fechainicial,$fechafinal) {
            $excel->sheet('ReporteConvenios', function($sheet) use($request,$fechainicial,$fechafinal) {
 
                $array = array();
                $cabecera = array();
                $cabecera2 = array();
               

                $c=3;$d=3;$band=true;
                $cabecera[] = "";
                $sheet->mergeCells("B2:C2"); 
                $sheet->mergeCells("D2:E2"); 
                $sheet->mergeCells("F2:G2"); 
                $sheet->mergeCells("H2:I2"); 
                $sheet->mergeCells("J2:K2"); 
                $sheet->mergeCells("L2:M2"); 
                $sheet->mergeCells("N2:O2"); 
                $sheet->mergeCells("P2:Q2"); 
                $sheet->mergeCells("R2:S2"); 
                $sheet->mergeCells("T2:U2"); 
                $sheet->mergeCells("V2:W2"); 
                $sheet->mergeCells("x2:Y2"); 
                $sheet->mergeCells("Z2:AA2"); 
                
                $sheet->getStyle("A1:AA1")
                        ->getFill()
                        ->getStartColor()
                        ->setRGB('6592F2');

                $sheet->getStyle("A2:AA2")
                        ->getFill()
                        ->getStartColor()
                        ->setRGB('6592F2');

                for ($i=0; $i < 12; $i++) { 
                    $cabecera[] = $this->aLetras(($i+1));
                    $cabecera[] = '';
                }
                $array[] = $cabecera;


                $cabecera2[] = 'Tipo Servicio';
                for ($i=0; $i <12; $i++) { 
                    $cabecera2[] = 'Cantidad';
                    $cabecera2[] = 'Total';
                }

                $cabecera2[] = 'Cantidad';
                $cabecera2[] = 'Total';
                
                $array[] = $cabecera2;
                
                $tipoServicios = TipoServicio::all();  
                $cont_filas = 4;
                $d = 4;
                foreach ($tipoServicios as $key02 => $value02) {
                    $cont_filas++;
                    // $detalle[] = $value02->nombre;
                    $sheet->cells("A".$d,function($cells) use ($value02){
                       $cells->setValue($value02->nombre);
                    });
                    // $d++;
                    $cont_general = 0;
                    $acum_general = 0;
                    
                    /*
                        // $detalle = array();
                                    
                        // for ($i=0; $i <12; $i++) { 
                        //     if(($i+1)<10){
                        //         $mes = '0'.($i+1);
                        //     }else{
                        //         $mes = ($i+1);
                        //     }
                    
                        // ->leftJoin('tiposervicio as tip','tip.id','=','s.tiposervicio_id')   
                    
                    */
                    $resultado = Movimiento::leftjoin('detallemovcaja as det','det.movimiento_id','=','movimiento.id')
                        ->leftJoin('servicio as s','s.id','=','det.servicio_id')
                        ->whereNotNull('movimiento.plan_id')
                        ->whereNotNull('movimiento.serie')
                        ->whereNotNull('det.servicio_id')
                        ->where('movimiento.tipomovimiento_id','=','9')      
                        ->whereIn('movimiento.situacion',['C','T'])
                        ->where('s.tipopago','=','Convenio')
                        ->where('det.tiposervicio_id','=',$value02->id)
                        ->whereBetween('movimiento.fecha',[$fechainicial,$fechafinal])
                        ->select(DB::raw(" (CASE WHEN movimiento.igv > 0 THEN (CASE WHEN (det.descripcion LIKE 'CONS %' OR s.nombre LIKE 'CONS %') THEN SUM(det.cantidad/1.18*det.precio+movimiento.copago) ELSE  SUM(det.cantidad*det.precio*100/(100-movimiento.montoinicial)/1.18) END) ELSE SUM(det.cantidad*det.precio) END) as monto"), 
                            DB::raw("SUM(det.cantidad) as cantidad"),
                            DB::raw("MONTH(movimiento.fecha) as mes"))
                        ->groupBy(DB::raw("MONTH(movimiento.fecha)"))
                        ->get();

                        foreach($resultado as $key => $value){
                            switch ((int)$value->mes) {
                                case '1':
                                    $sheet->cells("B".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->cantidad,2,'.',''));
                                    });

                                    $sheet->cells("C".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->monto,2,'.',''));
                                    });
                                    break;
                                case '2':
                                    $sheet->cells("D".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->cantidad,2,'.',''));
                                    });
                                   
                                    $sheet->cells("E".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->monto,2,'.',''));
                                    });
                                    break;
                                case '3':
                                    $sheet->cells("F".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->cantidad,2,'.',''));
                                    });
                                    
                                    $sheet->cells("G".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->monto,2,'.',''));
                                    });
                                    break;
                                case '4':
                                    $sheet->cells("H".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->cantidad,2,'.',''));
                                    });

                                    $sheet->cells("I".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->monto,2,'.',''));
                                    });
                                    break;
                                case '5':
                                    $sheet->cells("J".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->cantidad,2,'.',''));
                                    });
                                    
                                    $sheet->cells("K".$d,function($cells) use ($value){
                                        $cells->setValue(number_format($value->monto,2,'.',''));
                                    });
                                    break;
                                case '6':
                                    $sheet->cells("L".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->cantidad,2,'.',''));
                                    });

                                    $sheet->cells("M".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->monto,2,'.',''));
                                    });
                                    break;
                                case '7':
                                    $sheet->cells("N".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->cantidad,2,'.',''));
                                    });

                                    $sheet->cells("O".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->monto,2,'.',''));
                                    });
                                    break;
                                case '8':
                                    $sheet->cells("P".$d,function($cells) use ($value){
                                        $cells->setValue(number_format($value->cantidad,2,'.',''));
                                    });

                                    $sheet->cells("Q".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->monto,2,'.',''));
                                    });
                                    break;
                                case '9':
                                    $sheet->cells("R".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->cantidad,2,'.',''));
                                    });
                                    
                                    $sheet->cells("S".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->monto,2,'.',''));
                                    });
                                    break;
                                case '10':
                                    $sheet->cells("T".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->cantidad,2,'.',''));
                                    });
                                    
                                    $sheet->cells("U".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->monto,2,'.',''));
                                    });
                                    break;
                                case '11':
                                    $sheet->cells("V".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->cantidad,2,'.',''));
                                    });
                                    
                                    $sheet->cells("W".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->monto,2,'.',''));
                                    });
                                    break;
                                case '12':
                                    $sheet->cells("X".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->cantidad,2,'.',''));
                                    });

                                    $sheet->cells("Y".$d,function($cells) use ($value){
                                       $cells->setValue(number_format($value->monto,2,'.',''));
                                    });
                                    break;
                             }
                        }
                    
                    // $cont_general = 0;
                    // $acum_general =0;                        
                   
                    $sheet->cells("Z".$d,function($cells) use ($d){
                        $cells->setValue('=SUM(B'.$d.',D'.$d.',F'.$d.',H'.$d.',J'.$d.',L'.$d.',N'.$d.',P'.$d.',R'.$d.',T'.$d.',V'.$d.',X'.$d.')');
                    });

                    $sheet->cells("AA".$d,function($cells) use ($d){
                        $cells->setValue('=SUM(C'.$d.',E'.$d.',G'.$d.',I'.$d.',K'.$d.',M'.$d.',O'.$d.',Q'.$d.',S'.$d.',U'.$d.',W'.$d.',Y'.$d.')');
                    });

                    $d=$d+1;
                    
                    /*
                        // $d=4;
                        
                            //

                            // // dd($value02->id, $mes, $fechainicial, $fechafinal, $resultado);

                            // foreach ($resultado as $key => $value) {
                            //     // dd(date('m',strtotime($value->fecha)), ($i+1));
                            //     if( (int)date('m',strtotime($value->fecha)) === ($i+1) ){
                            //         if ($value02->id == $value->tiposervicio_id) {
                            //             $cont++;
                            //             $cont_total++;
                            //             if($value->igv>0){
                            //                 $acum+= number_format($value->precio*$value->cantidad/1.18,2,'.','');
                            //             }else{
                            //                 $acum+= number_format($value->precio*$value->cantidad,2,'.','');
                            //             }
                            //             $acum_total+=$acum;     
                            //             $acumY+=$acum;
                            //             $contY++;
                            //         }
                            //     }

                               // }
                           
                            // if($acum>0){
                            //     $detalle[] = number_format($acum,2,'.','');   
                            // }else{
                            //     $detalle[] = number_format('0',2,'.','');        
                            // }

                        // }
                        // $detalle[] = $cont_total;
                        // $detalle[] = number_format($acum_total,2,'.','');

                        // $array[] = $detalle;
                        // $contadorLetras++;
                    */
                }

                // dd($cont_filas);

                $sheet->cells("A".$cont_filas,function($cells) use ($cont_filas){
                   $cells->setValue("TOTAL");
                });

                $sheet->cells("B".$cont_filas,function($cells) use ($cont_filas){
                   $cells->setValue("=SUM(B4:B".($cont_filas-1).")");
                });
                $sheet->cells("C".$cont_filas,function($cells) use ($cont_filas){
                   $cells->setValue("=SUM(C4:C".($cont_filas-1).")");
                });
                $sheet->cells("D".$cont_filas,function($cells) use ($cont_filas){
                   $cells->setValue("=SUM(D4:D".($cont_filas-1).")");
                });
                $sheet->cells("E".$cont_filas,function($cells) use ($cont_filas){
                   $cells->setValue("=SUM(E4:E".($cont_filas-1).")");
                });
                $sheet->cells("F".$cont_filas,function($cells) use ($cont_filas){
                   $cells->setValue("=SUM(F4:F".($cont_filas-1).")");
                });
                $sheet->cells("G".$cont_filas,function($cells) use ($cont_filas){
                   $cells->setValue("=SUM(G4:G".($cont_filas-1).")");
                });
                $sheet->cells("H".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(H4:H".($cont_filas-1).")");
                });
                $sheet->cells("I".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(I4:I".($cont_filas-1).")");
                });
                $sheet->cells("J".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(J4:J".($cont_filas-1).")");
                });
                $sheet->cells("K".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(K4:K".($cont_filas-1).")");
                });
                $sheet->cells("L".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(L4:L".($cont_filas-1).")");
                });
                $sheet->cells("M".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(M4:M".($cont_filas-1).")");
                });
                $sheet->cells("N".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(N4:N".($cont_filas-1).")");
                });
                $sheet->cells("O".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(O4:O".($cont_filas-1).")");
                });
                $sheet->cells("P".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(P4:P".($cont_filas-1).")");
                });
                $sheet->cells("Q".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(Q4:Q".($cont_filas-1).")");
                });
                $sheet->cells("R".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(R4:R".($cont_filas-1).")");
                });
                $sheet->cells("S".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(S4:S".($cont_filas-1).")");
                });
                $sheet->cells("T".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(T4:T".($cont_filas-1).")");
                });
                $sheet->cells("U".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(U4:U".($cont_filas-1).")");
                });
                $sheet->cells("V".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(V4:V".($cont_filas-1).")");
                });
                $sheet->cells("W".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(W4:W".($cont_filas-1).")");
                });
                $sheet->cells("X".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(X4:X".($cont_filas-1).")");
                });
                $sheet->cells("Y".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(Y4:Y".($cont_filas-1).")");
                });
                $sheet->cells("Z".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(Z4:Z".($cont_filas-1).")");
                });
                $sheet->cells("AA".$cont_filas,function($cells) use ($cont_filas){
                    $cells->setValue("=SUM(AA4:AA".($cont_filas-1).")");
                });

            /*
                // $detalle = array();
                // $detalle[] = 'TOTAL';
                // $detalle[] =  $sheet->setCellValue('B'.$contadorLetras, "=SUMA(B4:B".($contadorLetras-1).")");// $contmesY01;
                // $detalle[] =  $sheet->setCellValue('C'.$contadorLetras, "=SUMA(C4:B".($contadorLetras-1).")");
                // $detalle[] = number_format($acumesY01,2,'.',' ');

                // $detalle[] = $contmesY02;
                // $detalle[] = number_format($acumesY02,2,'.',' ');
                
                // $detalle[] = $contmesY03;
                // $detalle[] = number_format($acumesY03,2,'.',' ');
                
                // $detalle[] = $contmesY04;
                // $detalle[] = number_format($acumesY04,2,'.',' ');
                
                // $detalle[] = $contmesY05;
                // $detalle[] = number_format($acumesY05,2,'.',' ');
                
                // $detalle[] = $contmesY06;
                // $detalle[] = number_format($acumesY06,2,'.',' ');
                
                // $detalle[] = $contmesY07;
                // $detalle[] = number_format($acumesY07,2,'.',' ');
                
                // $detalle[] = $contmesY08;
                // $detalle[] = number_format($acumesY08,2,'.',' ');
                
                // $detalle[] = $contmesY09;
                // $detalle[] = number_format($acumesY09,2,'.',' ');
                
                // $detalle[] = $contmesY010;
                // $detalle[] = number_format($acumesY010,2,'.',' ');
                
                // $detalle[] = $contmesY011;
                // $detalle[] = number_format($acumesY011,2,'.',' ');
                
                // $detalle[] = $contmesY012;
                // $detalle[] = number_format($acumesY012,2,'.',' ');
                
                // $detalle[] = $contY;
                // $detalle[] = number_format($acumY,2,'.',' ');
                
                // $array[] = $detalle;
                // // foreach ($resultado as $key => $value){
                // //     $detalle = array();
                // //     $detalle[] = $value->id_mov;
                // //     $detalle[] = date('d/m/Y',strtotime($value->fecha_mov));
                // //     $detalle[] = is_null($value->serie_mov)?$value->num_mov:$value->serie_mov.'-'.$value->num_mov;
                // //     $detalle[] = $value->doctor_resp;
                // //     $detalle[] = $value->conceptopago;
                // //     $detalle[] = $value->comentario;
                // //     $detalle[] = $value->total_venta;
                // //     $detalle[] = $value->responsable_op;

                // //     // $detalle[] = $this->mes_letras(date('m',strtotime($value->fecha)));
                  
                // //     // $this->obtener_semana($value->fecha);
                // //     // // exit();
                // //     // $detalle[] = $value->mes;//$this->obtener_semana($value->fecha); 
                // //     // $detalle[] = date('d/m/Y',strtotime($value->fecha));
                // //     // $detalle[] = $value->tipopaciente;
                // //     // $detalle[] = $value->historia;
                // //     // $detalle[] = $value->paciente2;
                // //     // $detalle[] = $value->plan2;
                // //     // if ($value->fechaentrega != "0000-00-00") {
                // //     //     $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                // //     // } else {
                // //     //     $detalle[] = "";
                // //     // }
                // //     // $detalle[] = $value->recibo;
                // //     // $detalle[] = $value->medico;
                // //     // $detalle[] = number_format($value->cantidad,0,'.','');
                // //     // if($value->servicio_id>0){
                // //     //     $detalle[] = $value->servicio;$nombre = $value->servicio;
                // //     // }
                // //     // else{
                // //     //     $detalle[] = $value->servicio2;$nombre = $value->servicio2;
                // //     // }
                // //     // $detalle[] = (trim($value->especialidad)==="PROVEEDOR" || trim($value->especialidad)==="MEDICINA GENERAL" || trim($value->especialidad)==="HOSPITAL")?"":$value->especialidad;
                // //     // $detalle[] = $value->tiposervicio;
                // //     // $detalle[] = number_format($value->pagodoctor*$value->cantidad,2,'.','');
                // //     // $detalle[] = number_format($value->pagohospital*$value->cantidad,2,'.','');
                // //     // $detalle[] = number_format(($value->pagohospital + $value->pagodoctor)*$value->cantidad,2,'.','');
                // //     // if(strpos($nombre,'CONSULTA') === false && $value->tiposervicio_id!=9) {
                // //     //     $detalle[] = "";
                // //     // }else{
                // //     //     $detalle[] = "10.00";
                // //     // }
                // //     // if($value->referido_id>0)
                // //     //     $detalle[] = $value->referido;
                // //     // else
                // //     //     $detalle[] = "NO REFERIDO";
                // //     // if($value->total>0)
                // //     //     $detalle[] = ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero;
                // //     // else
                // //     //     $detalle[] = 'PREF. '.$value->numero2;
                // //     // $detalle[] = $value->situacion=='C'?($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta.'-'.$value->voucher):'CONTADO'):'-';
                // //     // $detalle[] = ($value->situacion=='C'?'Pagado':'Pendiente');
                // //     // $detalle[] = $value->soat;
                // //     // $detalle[] = $value->responsable;
                // //     // $detalle[] = $value->condicionpaciente;
                // //     // if (is_null($value->created_at) || $value->created_at == '') { 
                // //     //     $detalle[] = "-";      
                // //     // }else{
                // //     //     $detalle[] = date('H:i:s', strtotime($value->created_at));
                // //     // }
                // //     $array[] = $detalle;                    
                // // }
            */
                $sheet->fromArray($array);
            });

            $excel->sheet('Reporte Detallado', function($sheet) use($request, $fechainicial, $fechafinal){
                $array = array();
                $cabecera = array();

                $cabecera[] = "Tipo de Servicio";
                $cabecera[] = "Documento";
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Servicio";
                $cabecera[] = "Doctor";
                $cabecera[] = "Cantidad";
                $cabecera[] = "Precio";
                $cabecera[] = "Total";
                $cabecera[] = "Usuario";
                
                $array[] = $cabecera;

                $tipos = TipoServicio::all();
                
                foreach ($tipos as $key02 => $value02) {
                    $resultado = Movimiento::leftJoin('person as paciente','paciente.id','=','movimiento.persona_id')
                        ->leftjoin('detallemovcaja as det','det.movimiento_id','=','movimiento.id')
                        ->leftJoin('person as medico','medico.id','=','det.persona_id')
                        ->leftJoin('servicio as s','s.id','=','det.servicio_id')
                        ->whereNotNull('movimiento.plan_id')
                        ->whereNotNull('movimiento.serie')
                        // ->whereNotNull('det.servicio_id')
                        ->whereIn('movimiento.situacion',['C','T'])
                        ->where('s.tipopago','=','Convenio')
                        ->where('movimiento.tipomovimiento_id','=','9')
                        ->where('det.tiposervicio_id','=',$value02->id)
                        ->whereBetween('movimiento.fecha',[$fechainicial,$fechafinal])
                        ->select(DB::raw("CONCAT(paciente.apellidopaterno,' ',paciente.apellidomaterno,' ', paciente.nombres) as paciente"),DB::raw("CONCAT(medico.apellidopaterno,' ',medico.apellidomaterno,' ', medico.nombres) as doctor"),'s.nombre as servicio','movimiento.igv','movimiento.fecha','movimiento.responsable_id','det.cantidad','det.precio','movimiento.serie','movimiento.numero','movimiento.copago','movimiento.montoinicial','det.descripcion')
                        ->orderBy(DB::raw("MONTH(movimiento.fecha)"),'ASC')
                        ->get();
                    
                    // dd($resultado);
                    $ultimo = '';
                    foreach ($resultado as $key => $value) {
                        if($ultimo == ''){
                            $ultimo = (int)date('m',strtotime($value->fecha));
                            $detalle = array();
                            $detalle[] = $this->aLetras($ultimo);
                            $array[] = $detalle;
                        }else{
                            if($ultimo != (int)date('m',strtotime($value->fecha))){
                                $ultimo = (int)date('m',strtotime($value->fecha));
                                $detalle = array();
                                $detalle[] = $this->aLetras($ultimo);
                                $array[] = $detalle;       
                            }
                        } 
                        $detalle = array();
                        $detalle[] = $value02->nombre;
                        $detalle[] = 'F'.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT);
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        $detalle[] = $value->paciente;
                        if (is_null($value->servicio)) {
                           $nombre = $value->descripcion;
                        }else{
                           $nombre = $value->servicio;
                        }
                        $detalle[] = $nombre;
                        $detalle[] = $value->doctor;
                        $detalle[] = $value->cantidad;
                        
                        if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $value02->id!="1") {
                            $value->precio=number_format($value->precio*100/(100-$value->montoinicial),2,'.','');
                        }

                        if($value->igv>0){
                            if(strpos($nombre,'CONSULTA') === false && strpos($nombre,'CONS ') === false && $value02->id!="1") {
                                // $pr=number_format($value->precio/1.18,2,'.','');
                                // $pc=number_format($value->precio*$value->cantidad/1.18,2,'.','');
                  
                                $detalle[] = number_format($value->precio/1.18,2,'.',' ');                        
                                $detalle[] = number_format(($value->precio*$value->cantidad)/1.18,2,'.',' ');
                  
                                // $total=$total+$pc;
                                // $total1=$total1+$pc;
                                //dd($v);
                            }else{
                                 $detalle[]  = number_format($value->copago+round($value->precio/1.18,2),2,'.',' ');
                                 $detalle[] = number_format($value->copago+round($value->precio*$value->cantidad/1.18,2),2,'.',' ');
                                // $total=$total+$cop1;
                                //+number_format($v->precio*$v->cantidad/1.18,2,'.','')
                            }
                        }else{
                        
                            $detalle[] = number_format($value->precio,2,'.',' ');                        
                            $detalle[] = number_format(($value->precio*$value->cantidad),2,'.',' ');
              
                            // $pdf::Cell(20,7,number_format($value->precio,2,'.',''),1,0,'R');
                            // $pdf::Cell(20,7,number_format($value->precio*$value->cantidad,2,'.',''),1,0,'R');
                            // $total=$total+number_format($value->precio*$value->cantidad,2,'.','');
                            // $total1=$total1+number_format($value->precio*$value->cantidad,2,'.','');
                        }
                     

                        // if ($value->igv>0) {
                        //     $detalle[] = number_format($value->precio/1.18,2,'.',' ');                        
                        //     $detalle[] = number_format(($value->precio*$value->cantidad)/1.18,2,'.',' ');
                        // }else{
                        //     $detalle[] = number_format($value->precio,2,'.',' ');                        
                        //     $detalle[] = number_format($value->precio*$value->cantidad,2,'.',' ');
                        // }
                        $resp = Person::find($value->responsable_id);
                        $detalle[] = $resp->nombres; 
                        $array[] = $detalle;
                    }
                }

                $sheet->fromArray($array);
             
            });
        })->export('xls');
    }

    public function aLetras($mes){
        
        switch ($mes) {
            case '1':
                $mes = 'Enero';
                break;
            case '2':
                $mes = 'Febrero';
                break;
            case '3':
                $mes = 'Marzo';
                break;
            case '4':
                $mes = 'Abril';
                break;
            case '5':
                $mes = 'Mayo';
                break;
            case '6':
                $mes = 'Junio';
                break;
            case '7':
                $mes = 'Julio';
                break;
            case '8':
                $mes = 'Agosto';
                break;
            case '9':
                $mes = 'Setiembre';
                break;
            case '10':
                $mes = 'Octubre';
                break;
            case '11':
                $mes = 'Noviembre';
                break;
            case '12':
                $mes = 'Diciembre';
                break;
        }

        return $mes;
    }
}
