<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Librerias\Libreria;
use Illuminate\Support\Facades\DB;
use App\Producto;
use App\lote;
use App\Conveniofarmacia;
use App\Movimiento;
use App\Detallemovimiento;
use App\Origen;
use App\Principioactivo;

use Excel;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;


class ReporteFarmaciaGerenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $folderview      = 'app.reportefarmaciagerencia';
    protected $tituloAdmin     = 'Reporte Gerencial de Farmacia';
    protected $tituloRegistrar = 'Registrar Egreso de Pago';
    protected $tituloModificar = 'Modificar Egreso de Pago';
    protected $tituloEliminar  = 'Eliminar Egreso de Pago';
    protected $rutas           = array('create' => 'reportegreso.create', 
            'edit'   => 'reporteconsulta.edit', 
            'delete' => 'reporteconsulta.eliminar',
            'search' => 'reportegreso.buscar',
            'index'  => 'reportegreso.index',
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
        $entidad          = 'ReporteFarmaciaGerencia';
        $doctor           = Libreria::getParam($request->input('doctor'));
        // $tipopaciente     = Libreria::getParam($request->input('tipopaciente'));
        // $plan           = Libreria::getParam($request->input('plan'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));

        if(is_null($doctor)){
            $doctor = '';
        }

        $lista = DB::select('CALL sp_reporte_sistemas(?,?,?)',array($doctor, $fechainicial, $fechafinal));      
        // dd($lista);

        $resultado = Movimiento::join('conceptopago as concep','concep.id','=','movimiento.conceptopago_id')
                ->join('person as doc_ref','doc_ref.id','=','movimiento.persona_id')
                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                // ->leftjoin('detallemovcaja as det','det.movimiento_id','=', 'movimiento.id')
                // ->leftjoin('especialidad as esp','doc_ref.especialidad_id','=','esp.id')
                ->whereNotNull('doc_ref.especialidad_id')
                ->whereIn('conceptopago_id',[8,34,35])
                ->orderBy('movimiento.fecha', 'ASC')->orderBy('doctor_resp','ASC')
                ->where(DB::raw("CONCAT(doc_ref.apellidopaterno,' ', doc_ref.apellidomaterno,' ',doc_ref.nombres)"), 'LIKE',$doctor.'%')
                ->whereBetween('movimiento.fecha',[$fechainicial, $fechafinal])
                ->select('movimiento.id as id_mov','movimiento.serie as serie_mov','movimiento.numero as num_mov','movimiento.fecha as fecha_mov',DB::raw("CONCAT(doc_ref.apellidopaterno,' ', doc_ref.apellidomaterno,' ', doc_ref.nombres) as doctor_resp"),'movimiento.total as total_venta', 'concep.nombre as conceptopago', DB::raw("CONCAT(responsable.apellidopaterno,' ', responsable.apellidomaterno,' ',responsable.nombres) as responsable_op"/*,'det.*'*/),'movimiento.comentario'/*,'det.*'*/);


        // $lista  =  $resultado->get();
    
        $cabecera         = array();

        $cabecera[]       = array('valor' => 'ID Mov.', 'numero' => '1');                
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Numero', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Concepto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
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

            // dd($paginacion);

            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = DB::select('CALL sp_reporte_sistemas_limit(?,?,?,?,?)',array($doctor, $fechainicial, $fechafinal, $pagina,$filas));

            //$resultado->paginate($filas);
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
        $entidad          = 'ReporteFarmaciaGerencia';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        // $cboTipoPaciente          = array("" => "Todos", "P" => "Particular", "C" => "Convenio");
        $cboTipoBusqueda   = array("1"=> "Más Vendidos" ,"2"=>"Menos Vendidos");
        $cboOrigen = Origen::all();
        $cboConvenios = Conveniofarmacia::orderBy('nombre','ASC')->get();

        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoBusqueda','cboOrigen','cboConvenios'));
    }

    public function obtenerProductos($fi,$ff,$tipo,$top,$origen){
        $arr = array();
        
        if($tipo== ""){
              $prod =   Movimiento::leftJoin('detallemovimiento as det','det.movimiento_id','=','movimiento.id')
                ->leftJoin('producto as pr','pr.id','=','det.producto_id')
                ->whereNull('movimiento.deleted_at')
                ->whereNull('det.deleted_at')
                ->whereNotNull('det.producto_id')
                // ->whereNotNull('movimiento.conveniofarmacia_id')
                ->whereNotIn('movimiento.situacion',['A','U'])
                // ->where('movimiento.tipodocumento_id','=',15)
                ->where('movimiento.tipomovimiento_id','=',4)
                ->whereBetween('movimiento.fecha',[$fi,$ff]);

                if($origen != ''){
                    $prod = $prod->where('pr.origen_id','=',$origen);
                }
        
                $prod = $prod->select('det.producto_id')
                        ->distinct()
                        ->orderBy('det.producto_id','ASC');
                // ->get();   

        }elseif($tipo == '1'){
            $prod =Movimiento::leftJoin('detallemovimiento as det','det.movimiento_id','=','movimiento.id')
                ->leftJoin('producto as pr','pr.id','=','det.producto_id')
                ->whereNull('movimiento.deleted_at')
                ->whereNull('det.deleted_at')
                ->whereNotNull('det.producto_id')
                ->whereNotIn('movimiento.situacion',['A','U'])
                // ->where('movimiento.tipodocumento_id','=',15)
                ->where('movimiento.tipomovimiento_id','=',4)
                ->whereBetween('movimiento.fecha',[$fi,$ff]);

                if($origen != ''){
                    $prod = $prod->where('pr.origen_id','=',$origen);
                }
        
                $prod = $prod->select(DB::raw('SUM(det.cantidad) as cantidad'),'det.producto_id')
                        ->groupBy('det.producto_id')
                        ->orderByRaw('SUM(det.cantidad) DESC');
                // ->get();

        }else{
            $prod = Movimiento::leftJoin('detallemovimiento as det','det.movimiento_id','=','movimiento.id')
                ->leftJoin('producto as pr','pr.id','=','det.producto_id')
                ->whereNull('movimiento.deleted_at')
                ->whereNull('det.deleted_at')
                ->whereNotNull('det.producto_id')
                ->whereNotIn('movimiento.situacion',['A','U'])
                // ->where('movimiento.tipodocumento_id','=',15)
                ->where('movimiento.tipomovimiento_id','=',4)
                ->whereBetween('movimiento.fecha',[$fi,$ff]);

                if($origen != ''){
                    $prod = $prod->where('pr.origen_id','=',$origen);
                }
        
                $prod = $prod->select(DB::raw('SUM(det.cantidad) as cantidad'),'det.producto_id')
                ->groupBy('det.producto_id')
                ->orderByRaw('SUM(det.cantidad) ASC');
                // ->get();
        }

        if($top != ''){
            $prod =$prod->take($top)->get();
        }else{
            $prod =$prod->get();
        }

        // dd($prod);

        foreach ($prod as $key => $value) {
             $arr[] = $value->producto_id;
        }

    /*   
      $prod02 = Movimiento::leftJoin('detallemovimiento as det','movimiento.id','=','det.movimiento_id')
          ->whereNotIn('movimiento.situacion',['A','U'])
          // ->whereNull('movimiento.conveniofarmacia_id')
          ->whereNotNull('movimiento.serie')
          ->whereNull('det.deleted_at')
          ->whereNull('movimiento.deleted_at')
          // ->where('movimiento.tipodocumento_id','=',5)
          ->where('movimiento.tipomovimiento_id','=',4)
          ->where('movimiento.serie','=','4')
          // ->where('movimiento.caja_id','=','4')
          ->whereBetween('movimiento.fecha',[$fi,$ff])
          ->select('det.producto_id')
          ->distinct()
          ->orderBy('det.producto_id','ASC')->get();
      
       // dd($prod,$prod02);
    */
     
        
     
    /*  foreach ($prod02 as $key =>  $value) {
         if (!in_array($value->producto_id, $arr)) {
              $arr[] = $value->producto_id;
         }
      }*/
      
         // dd($arr);
      return $arr;
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        
        $fechainicial     = Libreria::getParam($request->input('fi'));
        $fechafinal       = Libreria::getParam($request->input('ff'));
        $tipo       = Libreria::getParam($request->input('tipo'));
        $top       = Libreria::getParam($request->input('top'));
        $origen       = Libreria::getParam($request->input('origen'));
        
        // $prod = $this->obtenerProductos($fechainicial,$fechafinal,$tipo,2186,$origen);
        // echo json_encode($prod);exit();

        if($tipo == 1){
            $resultado = Movimiento::leftjoin('detallemovimiento as det','det.movimiento_id','=','movimiento.id')
                    ->select('det.producto_id as id',DB::raw("SUM(det.subTotal) as total"))
                    ->whereBetween('movimiento.fecha', [$fechainicial,$fechafinal])
                    ->groupBy('det.producto_id')
                    ->orderByRaw("SUM(det.subTotal) DESC")
                    ->get();        

        }else{
            $resultado = Movimiento::leftjoin('detallemovimiento as det','det.movimiento_id','=','movimiento.id')
                    ->select('det.producto_id as id',DB::raw("SUM(det.subTotal) as total"))
                    ->whereBetween('movimiento.fecha', [$fechainicial,$fechafinal])
                    ->groupBy('det.producto_id')
                    ->orderByRaw("SUM(det.subTotal) ASC")
                    ->get();        

        }

        // dd($resultado);
        // $resultado = Producto::leftJoin('lote','lote.producto_id','=','producto.id')
        // ->leftJoin('productoprincipio as rel_princ','rel_princ.producto_id','=','producto.id')
        // ->leftJoin('principioactivo as princ','princ.id','=','rel_princ.principioactivo_id')
        // ->leftJoin('especialidadfarmacia as especialidad','especialidad.id','=','producto.especialidadfarmacia_id')
        // ->leftJoin('origen','origen.id','=','producto.origen_id')
        // ->leftJoin('presentacion as pre','pre.id','=','producto.presentacion_id')
        // ->leftJoin('laboratorio','laboratorio.id','=','producto.laboratorio_id')
        // ->whereNull('producto.deleted_at')
        // ->whereIn('producto.id',$prod)
        // ->select('producto.id','producto.nombre', 'producto.preciocompra','producto.preciokayros','origen.nombre as origen','pre.nombre as presentacion','especialidad.nombre as especialidad','princ.nombre as principioActivo','lote.id as lote','laboratorio.nombre as laboratorio')
        // ->groupBy('lote.producto_id');


        // if($tipo == ''){
        //    $resultado = $resultado->orderBy('producto.id');
        // }

        // if ($top != '') {
        //     $resultado = $resultado->take(20);
        // }

        // $resultado = $resultado->get();

        // dd($resultado);

        // dd($resultado->toSql());
        // ->take(5)
        // ->get();

        Excel::create('Excel Gerencia Dr. Licham', function($excel) use($resultado,$fechainicial, $fechafinal) {
            
            $excel->sheet('Farmacia', function($sheet) use($resultado,$fechainicial, $fechafinal) {
                $default_border = array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb'=>'000000')
                );

                $style_header = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 12,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );


                $style_header02 = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 12,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );


                $style_header03 = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 12,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,        
                       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );

                $style_content = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'E4EAF9'),
                    ),
                    'font' => array(
                        'size' => 10,
                        'color' => array('rgb' => '000000'),
                    )
                );

                $sheet->setCellValue("A1", "DATOS DEL PRODUCTO");
                $sheet->setCellValue("I1", "TARIFAS");
                // $sheet->setCellValue("M1", "DATOS DEL PRODUCTO");
                $sheet->mergeCells("A1:H1");
                $sheet->getStyle("A1:H1")->applyFromArray($style_header);
                
                $sheet->setCellValue("A2", "PRODUCTO");
                $sheet->setCellValue("B2", "ORIGEN");
                $sheet->setCellValue("C2", "PRESENTACION");
                $sheet->setCellValue("D2", "ESPECIALIDAD");
                $sheet->setCellValue("E2", "PRINCIPIO ACTIVO");
                $sheet->setCellValue("F2", "LOTE");
                $sheet->setCellValue("G2", "LABORATORIO");
                $sheet->setCellValue("H2", "P. COMPRA");
                
                $sheet->mergeCells("A2:A3");
                $sheet->mergeCells("B2:B3");
                $sheet->mergeCells("C2:C3");
                $sheet->mergeCells("D2:D3");
                $sheet->mergeCells("E2:E3");
                $sheet->mergeCells("F2:F3");
                $sheet->mergeCells("G2:G3");
                $sheet->mergeCells("H2:H3");

                $sheet->getStyle("A2")->applyFromArray($style_header);
                $sheet->getStyle("B2")->applyFromArray($style_header);
                $sheet->getStyle("C2")->applyFromArray($style_header);
                $sheet->getStyle("D2")->applyFromArray($style_header);
                $sheet->getStyle("E2")->applyFromArray($style_header);
                $sheet->getStyle("F2")->applyFromArray($style_header);
                $sheet->getStyle("G2")->applyFromArray($style_header);
                $sheet->getStyle("H2")->applyFromArray($style_header);
            
                $sheet->getStyle("A2")->applyFromArray($style_header02);
                $sheet->getStyle("B2")->applyFromArray($style_header02);
                $sheet->getStyle("C2")->applyFromArray($style_header02);
                $sheet->getStyle("D2")->applyFromArray($style_header02);
                $sheet->getStyle("E2")->applyFromArray($style_header02);
                $sheet->getStyle("F2")->applyFromArray($style_header02);
                $sheet->getStyle("G2")->applyFromArray($style_header02);
                $sheet->getStyle("H2")->applyFromArray($style_header02);
              
              
                $cont = 9;                                         
                $convenios = Conveniofarmacia::whereNotIn('id',[1,7,13,14,16,17,20,21,15])->get();
                foreach ($convenios as $key => $value) {
                    $sheet->setCellValue($this->convertirLetraExcel($cont)."2", $value->nombre.' ('.$value->kayros .' %)');  
                    $sheet->mergeCells($this->convertirLetraExcel($cont)."2:".$this->convertirLetraExcel($cont+2)."2");
               
                    $sheet->getStyle($this->convertirLetraExcel($cont)."2:".$this->convertirLetraExcel($cont+2)."2")->applyFromArray($style_header);

                    $sheet->setCellValue($this->convertirLetraExcel($cont)."3",'CANT.');
                    $sheet->setCellValue($this->convertirLetraExcel($cont+1)."3",'P.V.');
                    $sheet->setCellValue($this->convertirLetraExcel($cont+2)."3",'TOTAL(S/)');

                    $sheet->getStyle($this->convertirLetraExcel($cont)."3")->applyFromArray($style_header);
                    $sheet->getStyle($this->convertirLetraExcel($cont+1)."3")->applyFromArray($style_header);
                    $sheet->getStyle($this->convertirLetraExcel($cont+2)."3")->applyFromArray($style_header);
                   $cont+=3;
                }
                
                $sheet->mergeCells("I1:".$this->convertirLetraExcel($cont-1)."1");
                $sheet->getStyle("I1:".$this->convertirLetraExcel($cont-1)."1")->applyFromArray($style_header);
            
                $sheet->setCellValue($this->convertirLetraExcel($cont)."1",'TARIFA PARTICULAR');
                $sheet->setCellValue($this->convertirLetraExcel($cont+3)."1",'TOTAL (S/)');
                $sheet->setCellValue($this->convertirLetraExcel($cont+6)."1",'CANT (UND)');
                $sheet->setCellValue($this->convertirLetraExcel($cont+7)."1",'GANANCIA (S/)');
                  
                $sheet->mergeCells($this->convertirLetraExcel($cont)."1:".$this->convertirLetraExcel($cont+2)."1");
                $sheet->mergeCells($this->convertirLetraExcel($cont)."2:".$this->convertirLetraExcel($cont+2)."2");
                $sheet->mergeCells($this->convertirLetraExcel($cont)."1:".$this->convertirLetraExcel($cont)."2");
                $sheet->getStyle($this->convertirLetraExcel($cont)."1")->applyFromArray($style_header);
            
                $sheet->mergeCells($this->convertirLetraExcel($cont+3)."1:".$this->convertirLetraExcel($cont+5)."1");
                $sheet->mergeCells($this->convertirLetraExcel($cont+3)."2:".$this->convertirLetraExcel($cont+5)."2");
                $sheet->mergeCells($this->convertirLetraExcel($cont+3)."1:".$this->convertirLetraExcel($cont+5)."2");
                $sheet->getStyle($this->convertirLetraExcel($cont+3)."1")->applyFromArray($style_header);
                
                $sheet->mergeCells($this->convertirLetraExcel($cont+6)."1:".$this->convertirLetraExcel($cont+6)."3");
                $sheet->mergeCells($this->convertirLetraExcel($cont+7)."1:".$this->convertirLetraExcel($cont+7)."3");
          
                $sheet->getStyle($this->convertirLetraExcel($cont+6)."1")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont+7)."1")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont+6)."1")->applyFromArray($style_header03);
                $sheet->getStyle($this->convertirLetraExcel($cont+7)."1")->applyFromArray($style_header03);
              
                // $sheet->setCellValue($this->convertirLetraExcel($cont+7)."1",'GANANCIA (S/)');
                // $sheet->getStyle($this->convertirLetraExcel($cont)."1:".$this->convertirLetraExcel($cont+2)."1")->applyFromArray($style_header);
                // $sheet->getStyle($this->convertirLetraExcel($cont)."2:".$this->convertirLetraExcel($cont+2)."2")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont)."1:".$this->convertirLetraExcel($cont)."2")->applyFromArray($style_header03);
           
                $sheet->getStyle($this->convertirLetraExcel($cont+3)."1:".$this->convertirLetraExcel($cont+3)."2")->applyFromArray($style_header03);
                                                        
                $sheet->setCellValue($this->convertirLetraExcel($cont)."3",'CANT.');
                $sheet->setCellValue($this->convertirLetraExcel($cont+1)."3",'PREC.');
                $sheet->setCellValue($this->convertirLetraExcel($cont+2)."3",'TOTAL');

                $sheet->setCellValue($this->convertirLetraExcel($cont+3)."3",'Conv.');
                $sheet->setCellValue($this->convertirLetraExcel($cont+4)."3",'Part.');
                $sheet->setCellValue($this->convertirLetraExcel($cont+5)."3",'Conv.+Part.');
                
                
                $sheet->getStyle($this->convertirLetraExcel($cont)."3")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont+1)."3")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont+2)."3")->applyFromArray($style_header);
        
                $sheet->getStyle($this->convertirLetraExcel($cont+3)."3")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont+4)."3")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont+5)."3")->applyFromArray($style_header);

                $cont_col = 4;

                foreach($resultado as $key => $value){
                   $producto  = DB::select('CALL sp_obtener_producto(?)',array($value->id));      
               
                   $nombre = "";
                   $origen = "";
                   $presentacion = "";
                   $especialidad = "";
                   $lote = "";
                   $principioActivo = "";
                   $laboratorio = "";
                   $preciocompra = 0;
                   $preciokayros = 0;
                   if(count($producto)>0){
                        $producto = $producto[0]; 
                        $nombre = $producto->nombre;
                        $origen = $producto->origen;
                        $presentacion = $producto->presentacion;
                        $especialidad = $producto->especialidad;
                        $lote = $producto->lote;
                        $principioActivo = $producto->principioActivo;
                        $laboratorio  = $producto->laboratorio;
                        $preciocompra  = $producto->preciocompra;
                        $preciokayros  = $producto->preciokayros;
                   }
                    //Producto::find($value->id);
                   // dd($producto->id);

                   $sheet->setCellValue("A".$cont_col, $nombre);
                   $sheet->getStyle("A".$cont_col)->applyFromArray($style_content);
                
                   $sheet->setCellValue("B".$cont_col, $origen);
                   $sheet->getStyle("B".$cont_col)->applyFromArray($style_content);
                  
                   $sheet->setCellValue("C".$cont_col, $presentacion);
                   $sheet->getStyle("C".$cont_col)->applyFromArray($style_content);
                
                   $sheet->setCellValue("D".$cont_col, $especialidad);
                   $sheet->getStyle("D".$cont_col)->applyFromArray($style_content);
                
                   $sheet->setCellValue("E".$cont_col, $principioActivo);
                   $sheet->getStyle("E".$cont_col)->applyFromArray($style_content); 

                   $sheet->setCellValue("F".$cont_col,$lote);
                   $sheet->getStyle("F".$cont_col)->applyFromArray($style_content); 
   
                   $sheet->setCellValue("G".$cont_col, $laboratorio);
                   $sheet->getStyle("G".$cont_col)->applyFromArray($style_content); 

                   $sheet->setCellValue("H".$cont_col, $preciocompra);
                   $sheet->getStyle("H".$cont_col)->applyFromArray($style_content); 
                    
                   $cont_let = 9; 
                   $acum_subTotal = 0;
                   $acum_subTotal_p = 0;
                   $acum_producto = 0;
                   
                   // $value->id=1125;
                   $movimientos = Movimiento::leftJoin('detallemovimiento as det','movimiento.id','=','det.movimiento_id')
                      ->whereNotIn('movimiento.situacion',['A','U'])
                      ->whereNotNull('movimiento.conveniofarmacia_id')  
                      // ->where('movimiento.conveniofarmacia_id','=',$value02->id)
                      ->whereNotNull('movimiento.serie')
                      ->whereNull('det.deleted_at')
                      ->whereNull('movimiento.deleted_at')
                      ->whereIn('movimiento.tipodocumento_id',[15,5])
                      ->where('movimiento.tipomovimiento_id','=',4)
                      ->where('det.producto_id','=',$value->id)
                      ->whereBetween('movimiento.fecha',[$fechainicial,$fechafinal])
                      ->whereNotIn('movimiento.conveniofarmacia_id',[1,7,13,14,16,17,20,21,15])
                      ->select(DB::raw('SUM(det.cantidad) as cantidad'), DB::raw('SUM(det.subTotal) as subTotal'),'movimiento.conveniofarmacia_id')
                      ->groupBy('movimiento.conveniofarmacia_id')
                      ->get();
                    
                     $sheet->getStyle("I".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("J".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("K".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("L".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("M".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("N".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("O".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("P".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("Q".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("R".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("S".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("T".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("U".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("V".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("W".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("X".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("Y".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("Z".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AA".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AB".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AC".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AD".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AE".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AF".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AG".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AH".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AI".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AJ".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AK".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AL".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AM".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AN".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AO".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AP".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AQ".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AR".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AS".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AT".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AU".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AV".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AW".$cont_col)->applyFromArray($style_content); 
                     $sheet->getStyle("AX".$cont_col)->applyFromArray($style_content); 


                      $cantidad = 0;
                      $subTotal = 0.0;
                      foreach ($movimientos as $key00 => $value00) {
                        if($value00->conveniofarmacia_id != 0){
                          // if(!is_null($movimientos)) {
                             $value02 = Conveniofarmacia::where('id','=',$value00->conveniofarmacia_id)->first();
                              // if(!is_null($value02)){
                             $cantidad = $value00->cantidad;//sum('det.cantidad');
                             $subTotal = $value00->cantidad*($preciokayros-($preciokayros*$value02->kayros/100));//sum('det.subtotal');
                              // }else{
                              //   dd($value02,$value00->conveniofarmacia_id);
                              // }                                  
                              $acum_subTotal+=$subTotal;
                              $acum_producto+=$cantidad;
                                switch ($value00->conveniofarmacia_id) {
                                    case '2':
                                          $sheet->setCellValue("I".$cont_col, $cantidad); 
                                          // $sheet->getStyle("I".$cont_col)->applyFromArray($style_content); 
                           
                                          $sheet->setCellValue("J".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("J".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("K".$cont_col, $subTotal);  
                                          // $sheet->getStyle("K".$cont_col)->applyFromArray($style_content); 
                                         break;
                                    case '3':
                                          $sheet->setCellValue("L".$cont_col, $cantidad); 
                                          // $sheet->getStyle("L".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("M".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("M".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("N".$cont_col, $subTotal);  
                                          // $sheet->getStyle("N".$cont_col)->applyFromArray($style_content); 
                                         break;
                                    case '4':
                                          $sheet->setCellValue("O".$cont_col, $cantidad); 
                                          // $sheet->getStyle("O".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("P".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("P".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("Q".$cont_col, $subTotal);  
                                          // $sheet->getStyle("Q".$cont_col)->applyFromArray($style_content); 
                                         break;
                                    case '5':
                                          $sheet->setCellValue("R".$cont_col, $cantidad); 
                                          // $sheet->getStyle("R".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("S".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("S".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("T".$cont_col, $subTotal);  
                                          // $sheet->getStyle("T".$cont_col)->applyFromArray($style_content); 
                                         break;
                                    case '6':
                                          $sheet->setCellValue("U".$cont_col, $cantidad); 
                                          // $sheet->getStyle("U".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("V".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("V".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("W".$cont_col, $subTotal);  
                                          // $sheet->getStyle("W".$cont_col)->applyFromArray($style_content); 
                                         break;
                                    case '8':
                                          $sheet->setCellValue("X".$cont_col, $cantidad); 
                                          // $sheet->getStyle("X".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("Y".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("Y".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("Z".$cont_col, $subTotal);  
                                          // $sheet->getStyle("Z".$cont_col)->applyFromArray($style_content); 
                                         break;
                                     case '9':
                                          $sheet->setCellValue("AA".$cont_col, $cantidad); 
                                          // $sheet->getStyle("AA".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("AB".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("AB".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("AC".$cont_col, $subTotal);  
                                          // $sheet->getStyle("AC".$cont_col)->applyFromArray($style_content); 
                                         break;
                                     case '10':
                                          $sheet->setCellValue("AD".$cont_col, $cantidad); 
                                          // $sheet->getStyle("AD".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("AE".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("AE".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("AF".$cont_col, $subTotal);  
                                          // $sheet->getStyle("AF".$cont_col)->applyFromArray($style_content); 
                                         break;
                                     case '11':
                                          $sheet->setCellValue("AG".$cont_col, $cantidad); 
                                          // $sheet->getStyle("AG".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("AH".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("AH".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("AI".$cont_col, $subTotal);  
                                          // $sheet->getStyle("AI".$cont_col)->applyFromArray($style_content); 
                                         break;
                                     case '12':
                                          $sheet->setCellValue("AJ".$cont_col, $cantidad); 
                                          // $sheet->getStyle("AJ".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("AK".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("AK".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("AL".$cont_col, $subTotal);  
                                          // $sheet->getStyle("AL".$cont_col)->applyFromArray($style_content); 
                                         break;
                                      case '18':
                                          $sheet->setCellValue("AM".$cont_col, $cantidad); 
                                          // $sheet->getStyle("AM".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("AN".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("AN".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("AO".$cont_col, $subTotal);  
                                          // $sheet->getStyle("AO".$cont_col)->applyFromArray($style_content); 
                                         break;
                                      case '18':
                                          $sheet->setCellValue("AP".$cont_col, $cantidad); 
                                          // $sheet->getStyle("AM".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("AQ".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("AN".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("AR".$cont_col, $subTotal);  
                                          // $sheet->getStyle("AO".$cont_col)->applyFromArray($style_content); 
                                         break;
                                      case '22':
                                          $sheet->setCellValue("AS".$cont_col, $cantidad); 
                                          // $sheet->getStyle("AP".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("AT".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("AQ".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("AU".$cont_col, $subTotal);  
                                          // $sheet->getStyle("AR".$cont_col)->applyFromArray($style_content); 
                                         break;
                                      case '23':
                                          $sheet->setCellValue("AV".$cont_col, $cantidad); 
                                          // $sheet->getStyle("AS".$cont_col)->applyFromArray($style_content); 
                               
                                          $sheet->setCellValue("AW".$cont_col, ($preciokayros-($preciokayros*$value02->kayros/100)));  
                                          // $sheet->getStyle("AT".$cont_col)->applyFromArray($style_content); 
                                          
                                          $sheet->setCellValue("AX".$cont_col, $subTotal);  
                                          // $sheet->getStyle("AU".$cont_col)->applyFromArray($style_content); 
                                         break;
                                } 
                        }
                          // }
                      }
                      // $movimientos = Movimiento::leftJoin('detallemovimiento as det','movimiento.id','=','det.movimiento_id')
                      // ->whereNotIn('movimiento.situacion',['A','U'])
                      // ->whereNotNull('movimiento.conveniofarmacia_id')  
                      // ->where('movimiento.conveniofarmacia_id','=',$value02->id)
                      // ->whereNotNull('movimiento.serie')
                      // ->whereNull('det.deleted_at')
                      // ->whereNull('movimiento.deleted_at')
                      // ->whereIn('movimiento.tipodocumento_id',[15,5])
                      // ->where('movimiento.tipomovimiento_id','=',4)
                      // ->where('det.producto_id','=',$producto->id)
                      // ->whereBetween('movimiento.fecha',[$fechainicial,$fechafinal])
                      // ->select(DB::raw('SUM(det.cantidad) as cantidad'), DB::raw('SUM(det.subTotal) as subTotal'))
                      // ->groupBy('det.producto_id')
                      // ->first();
                   
                   $cont_let = 51;
                   $movimientos_particular =Movimiento::leftJoin('detallemovimiento as det','movimiento.id','=','det.movimiento_id')
                      ->whereNotIn('movimiento.situacion',['A','U'])
                      ->whereNull('movimiento.conveniofarmacia_id')
                      ->whereNotNull('movimiento.serie')
                      ->whereNull('det.deleted_at')
                      ->whereNull('movimiento.deleted_at')
                      ->whereIn('movimiento.tipodocumento_id',[4,5])
                      ->where('movimiento.tipomovimiento_id','=',4)
                      ->where('movimiento.serie','=','4')
                      // ->where('movimiento.caja_id','=','4')
                      ->where('det.producto_id','=',$value->id)
                      ->whereBetween('movimiento.fecha',[$fechainicial,$fechafinal])
                      ->select(DB::raw('SUM(det.cantidad) as cantidad'), DB::raw('SUM(det.subTotal) as subTotal'))
                      ->groupBy('det.producto_id')
                      ->first();

                      $cantidad_p = 0;
                      $subTotal_p = 0.0;

                      if(!is_null($movimientos_particular)) {
                          $cantidad_p = $movimientos_particular->cantidad;//sum('det.cantidad');
                          $subTotal_p = $movimientos_particular->subTotal;
                          $acum_subTotal_p+=$subTotal_p;
                         
                          $acum_producto+=$cantidad_p;
                      }

                       // dd($cont_let, $movimientos_particular);
                         
                      // dd($this->convertirLetraExcel($cont_let), $cont_col);

                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col, $cantidad_p); 
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++; 
           
                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col, ($cantidad_p>0?($subTotal_p/$cantidad_p):$subTotal_p));  
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++; 
                      
                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col, $subTotal_p);  
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++; 

                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col, $acum_subTotal);  
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++;
                       
                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col, $acum_subTotal_p);  
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++;
                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col, ($acum_subTotal_p+$acum_subTotal) );  
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++;
                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col, $acum_producto);  
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++;
                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col, (($acum_subTotal_p+$acum_subTotal)-($acum_producto*$preciocompra)));  
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                                              

                   $cont_col++;    
                }
                $sheet->setCellValue("A".$cont_col,"REPORTE DE FARMACIA DEL: ".date('d/m/Y',strtotime($fechainicial)). " AL ".date('d/m/Y',strtotime($fechafinal)) );

            });
        })->export('xls');
   
        // dd($productos);
    }

    public function excel03(Request $request){
        setlocale(LC_TIME, 'spanish');
        
        $fechainicial     = Libreria::getParam($request->input('fi'));
        $fechafinal       = Libreria::getParam($request->input('ff'));
        $tipo       = Libreria::getParam($request->input('tipo'));
        $top       = Libreria::getParam($request->input('top'));
        $origen       = Libreria::getParam($request->input('origen'));
        
        // $prod = $this->obtenerProductos($fechainicial,$fechafinal,$tipo,2146,$origen);
        // echo json_encode($prod);exit();

        // $resultado = Producto::leftJoin('lote','lote.producto_id','=','producto.id')
        // ->leftJoin('productoprincipio as rel_princ','rel_princ.producto_id','=','producto.id')
        // ->leftJoin('principioactivo as princ','princ.id','=','rel_princ.principioactivo_id')
        // ->leftJoin('especialidadfarmacia as especialidad','especialidad.id','=','producto.especialidadfarmacia_id')
        // ->leftJoin('origen','origen.id','=','producto.origen_id')
        // ->leftJoin('detallemovimiento as det','det.producto_id','=','producto.id')
        // ->leftJoin('movimiento as mov','mov.id','=','det.movimiento_id')
        // ->leftJoin('presentacion as pre','pre.id','=','producto.presentacion_id')
        // ->leftJoin('laboratorio','laboratorio.id','=','producto.laboratorio_id')
        // ->whereNull('producto.deleted_at')
        // ->whereNotIn('mov.situacion',['A','U'])
        // // ->whereIn('producto.id',$prod)
        // ->whereNull('det.deleted_at')
        // ->whereNull('mov.deleted_at')
        // ->whereNotNull('mov.serie')
        // ->where('producto.preciocompra','>=','0.10')
        // ->where('producto.origen_id','=',$origen)
        // ->whereBetween('mov.fecha',[$fechainicial,$fechafinal])
        // ->select('producto.id','producto.nombre', 'producto.preciocompra','producto.preciokayros','producto.precioventa','origen.nombre as origen','pre.nombre as presentacion','especialidad.nombre as especialidad','princ.nombre as principioActivo','lote.id as lote','laboratorio.nombre as laboratorio')
        // ->groupBy('lote.producto_id'/*,'producto.nombre','producto.preciocompra','producto.preciokayros','producto.precioventa','origen.nombre','pre.nombre','especialidad.nombre','princ.nombre','lote.id','laboratorio.nombre'*/);

         // $movimientos = Movimiento::leftJoin('detallemovimiento as det','movimiento.id','=','det.movimiento_id')
         //              ->whereNotIn('movimiento.situacion',['A','U'])
         //              ->whereNotNull('movimiento.conveniofarmacia_id')  
         //              // ->where('movimiento.conveniofarmacia_id','=',$value02->id)
         //              ->whereNotNull('movimiento.serie')
         //              ->whereNull('det.deleted_at')
         //              ->whereNull('movimiento.deleted_at')
         //              ->whereIn('movimiento.tipodocumento_id',[15,5])
         //              ->where('movimiento.tipomovimiento_id','=',4)
         //              ->where('det.producto_id','=',$value->id)
         //              ->whereBetween('movimiento.fecha',[$fechainicial,$fechafinal])
         //              ->whereNotIn('movimiento.conveniofarmacia_id',[1,7,13,14,16,17,20,21,15])
         //              ->select(DB::raw('SUM(det.cantidad) as cantidad'), DB::raw('SUM(det.subTotal) as subTotal'),'movimiento.conveniofarmacia_id')
         //              ->groupBy('movimiento.conveniofarmacia_id')
         //              ->get();
                    
        // ->orderBy('producto.preciocompra','DESC')
        
        if ($tipo == '1') {
            # code...
            $resultado = DB::select('CALL sp_reporte_doctorT_desc(?,?,?)',array($fechainicial,$fechafinal,$origen));      
            // $resultado = $resultado->orderByRaw('SUM(det.cantidad) DESC');
        }else{
            $resultado = DB::select('CALL sp_reporte_doctorT_asc(?,?,?)',array($fechainicial,$fechafinal,$origen));            
        }
        // ->get();

        // if($tipo == ''){
        //    $resultado = $resultado->orderBy('producto.id');
        // }
        // dd($resultado->toSql());
        // if($top != ''){
        //     $resultado =$resultado->take(20)->get();
        // }else{
        //     $resultado =$resultado->take(100)->get();
        // }

        Excel::create('ExcelGerencia', function($excel) use($resultado,$fechainicial, $fechafinal) {
            
            $excel->sheet('Farmacia', function($sheet) use($resultado,$fechainicial, $fechafinal) {
                $default_border = array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb'=>'000000')
                );

                $style_header = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 10,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );


                $style_header02 = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 10,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );


                $style_header03 = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 10,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,        
                       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );

                $style_content = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'E4EAF9'),
                    ),
                    'font' => array(
                        'size' => 8,
                        'color' => array('rgb' => '000000'),
                    )
                );

                $style_content_util = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'E3F739'),
                    ),
                    'font' => array(
                        'size' => 8,
                        'color' => array('rgb' => '000000'),
                    )
                );


                $sheet->setCellValue("A1", "DATOS DEL PRODUCTO");
                $sheet->setCellValue("I1", "TARIFAS");
                // $sheet->setCellValue("M1", "DATOS DEL PRODUCTO");
                $sheet->mergeCells("A1:H1");
                $sheet->getStyle("A1:H1")->applyFromArray($style_header);
                
                $sheet->setCellValue("A2", "PRODUCTO");
                $sheet->setCellValue("B2", "ORIGEN");
                $sheet->setCellValue("C2", "PRESENTACION");
                $sheet->setCellValue("D2", "ESPECIALIDAD");
                $sheet->setCellValue("E2", "PRINCIPIO ACTIVO");
                $sheet->setCellValue("F2", "LOTE");
                $sheet->setCellValue("G2", "LABORATORIO");
                $sheet->setCellValue("H2", "P. COMPRA (UNITARIO)");
                
                $sheet->mergeCells("A2:A3");
                $sheet->mergeCells("B2:B3");
                $sheet->mergeCells("C2:C3");
                $sheet->mergeCells("D2:D3");
                $sheet->mergeCells("E2:E3");
                $sheet->mergeCells("F2:F3");
                $sheet->mergeCells("G2:G3");
                $sheet->mergeCells("H2:H3");

                $sheet->getStyle("A2")->applyFromArray($style_header);
                $sheet->getStyle("B2")->applyFromArray($style_header);
                $sheet->getStyle("C2")->applyFromArray($style_header);
                $sheet->getStyle("D2")->applyFromArray($style_header);
                $sheet->getStyle("E2")->applyFromArray($style_header);
                $sheet->getStyle("F2")->applyFromArray($style_header);
                $sheet->getStyle("G2")->applyFromArray($style_header);
                $sheet->getStyle("H2")->applyFromArray($style_header);
            
                $sheet->getStyle("A2")->applyFromArray($style_header02);
                $sheet->getStyle("B2")->applyFromArray($style_header02);
                $sheet->getStyle("C2")->applyFromArray($style_header02);
                $sheet->getStyle("D2")->applyFromArray($style_header02);
                $sheet->getStyle("E2")->applyFromArray($style_header02);
                $sheet->getStyle("F2")->applyFromArray($style_header02);
                $sheet->getStyle("G2")->applyFromArray($style_header02);
                $sheet->getStyle("H2")->applyFromArray($style_header02);
              
              
                $cont = 9;                                         
                // $convenios = Conveniofarmacia::all(); //whereNotIn('id',[1,7,13,14,16,17,20,21,15])->get();
                $convenios = Conveniofarmacia::whereNotIn('id',[1,7,11,13,14,16,17,20,21,15])->get();
                foreach ($convenios as $key => $value) {
                    if($value->id == 19){
                        $sheet->setCellValue($this->convertirLetraExcel($cont)."2", $value->nombre.' (25%,10%)'); 
                    }else{
                        if($value->id == 10){
                            $sheet->setCellValue($this->convertirLetraExcel($cont)."2", $value->nombre.' (25%,30%)'); 
                        }else{
                            $sheet->setCellValue($this->convertirLetraExcel($cont)."2", $value->nombre.' ('.$value->kayros .' %)');  
                        }
                    }
                    $sheet->mergeCells($this->convertirLetraExcel($cont)."2:".$this->convertirLetraExcel($cont+2)."2");
               
                    $sheet->getStyle($this->convertirLetraExcel($cont)."2:".$this->convertirLetraExcel($cont+2)."2")->applyFromArray($style_header);
                    $sheet->setCellValue($this->convertirLetraExcel($cont)."3",'CANT.');
                    $sheet->setCellValue($this->convertirLetraExcel($cont+1)."3",'VENTA');
                    // $sheet->setCellValue($this->convertirLetraExcel($cont+1)."3",'P.V.');
                    $sheet->setCellValue($this->convertirLetraExcel($cont+2)."3",'UTILIDAD');

                    $sheet->getStyle($this->convertirLetraExcel($cont)."3")->applyFromArray($style_header);
                    $sheet->getStyle($this->convertirLetraExcel($cont+1)."3")->applyFromArray($style_header);
                    $sheet->getStyle($this->convertirLetraExcel($cont+2)."3")->applyFromArray($style_header);
                   $cont+=3;
                }
                
                $sheet->mergeCells("I1:".$this->convertirLetraExcel($cont-1)."1");
                $sheet->getStyle("I1:".$this->convertirLetraExcel($cont-1)."1")->applyFromArray($style_header);
            
                $sheet->setCellValue($this->convertirLetraExcel($cont)."1",'TARIFA PARTICULAR');
                $sheet->setCellValue($this->convertirLetraExcel($cont)."3",'CANT.');
                $sheet->setCellValue($this->convertirLetraExcel($cont+1)."3",'VENTA');  
                $sheet->setCellValue($this->convertirLetraExcel($cont+2)."3",'UTILIDAD');
                // $sheet->setCellValue($this->convertirLetraExcel($cont+7)."1",'GANANCIA (S/)');
                  
                $sheet->mergeCells($this->convertirLetraExcel($cont)."1:".$this->convertirLetraExcel($cont+2)."1");
                $sheet->mergeCells($this->convertirLetraExcel($cont)."1:".$this->convertirLetraExcel($cont)."2");
                // $sheet->mergeCells($this->convertirLetraExcel($cont)."3:".$this->convertirLetraExcel($cont)."2");

                $sheet->getStyle($this->convertirLetraExcel($cont)."1")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont)."1")->applyFromArray($style_header03);
                
                $sheet->getStyle($this->convertirLetraExcel($cont)."3")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont+1)."3")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont+2)."3")->applyFromArray($style_header);
   
                $cont_col = 4;

                foreach($resultado as $key => $value){
                   $sheet->setCellValue("A".$cont_col, $value->nombre);
                   $sheet->getStyle("A".$cont_col)->applyFromArray($style_content);
                
                   $sheet->setCellValue("B".$cont_col, $value->origen);
                   $sheet->getStyle("B".$cont_col)->applyFromArray($style_content);
                  
                   $sheet->setCellValue("C".$cont_col, $value->presentacion);
                   $sheet->getStyle("C".$cont_col)->applyFromArray($style_content);
                
                   $sheet->setCellValue("D".$cont_col, $value->especialidad);
                   $sheet->getStyle("D".$cont_col)->applyFromArray($style_content);
                
                   $sheet->setCellValue("E".$cont_col, $value->principioActivo);
                   $sheet->getStyle("E".$cont_col)->applyFromArray($style_content); 

                   $sheet->setCellValue("F".$cont_col, $value->lote);
                   $sheet->getStyle("F".$cont_col)->applyFromArray($style_content); 
   
                   $sheet->setCellValue("G".$cont_col, $value->laboratorio);
                   $sheet->getStyle("G".$cont_col)->applyFromArray($style_content); 

                   $sheet->setCellValue("H".$cont_col, $value->preciocompra);
                   $sheet->getStyle("H".$cont_col)->applyFromArray($style_content); 
                    
                   $cont_let = 9; 
                   
                     // $sheet->getStyle("I".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("J".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("K".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("L".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("M".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("N".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("O".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("P".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("Q".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("R".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("S".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("T".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("U".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("V".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("W".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("X".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("Y".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("Z".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AA".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AB".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AC".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AD".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AE".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AF".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AG".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AH".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AI".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AJ".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AK".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AL".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AM".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AN".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AO".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AP".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AQ".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AR".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AS".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AT".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AU".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AV".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AW".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AX".$cont_col)->applyFromArray($style_content); 


                       // $convenios = Conveniofarmacia::all();
                       // $convenios = Conveniofarmacia::whereNotIn('id',[1,7,13,14,16,17,20,21,15])->get();
                       $convenios = Conveniofarmacia::whereNotIn('id',[1,7,11,13,14,16,17,20,21,15])->get();
            
                       foreach ($convenios as $keyC => $valueC) {
                           if($valueC->id != 19 && $valueC->id != 10){
                               $venta = $value->preciokayros-($value->preciokayros*$valueC->kayros/100);//sum('det.subtotal');
                               $utilidad = $venta-$value->preciocompra;
                           }else{
                                if($valueC->id == 19){
                                    if($value->origen == 'M'){
                                       $venta = $value->precioventa-(25*$value->precioventa/100);//sum('det.subtotal');
                                       $utilidad = $venta-$value->preciocompra;
                                    }else{
                                        if ($value->origen == 'G') {
                                           $venta = $value->precioventa-(10*$value->precioventa/100);//sum('det.subtotal');
                                           $utilidad = $venta-$value->preciocompra;
                                        }else{
                                           $venta = $value->precioventa-(0*$value->precioventa/100);//sum('det.subtotal');
                                           $utilidad = $venta-$value->preciocompra;
                                            
                                        }
                                    }
                                }else{
                                    if($value->origen == 'M'){
                                       $venta = $value->preciokayros-(25*$value->preciokayros/100);//sum('det.subtotal');
                                       $utilidad = $venta-$value->preciocompra;
                                    }else{
                                        if ($value->origen == 'G') {
                                           $venta = $value->preciokayros-(30*$value->preciokayros/100);//sum('det.subtotal');
                                           $utilidad = $venta-$value->preciocompra;
                                        }else{
                                           $venta = $value->precioventa-(0*$value->precioventa/100);//sum('det.subtotal');
                                           $utilidad = $venta-$value->preciocompra;
                                            
                                        }
                                    }
                                }
                           }

                           $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format(1,2,'.',''));
                           $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                           $cont_let++;

                           $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format($venta,2,'.',''));
                           $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                           $cont_let++;
                            
                           $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format($utilidad,2,'.',''));
                           $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content_util); 
                           $cont_let++;
                       }        

                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format(1,2,'.',''));
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++;
                        
                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format($value->precioventa,2,'.',''));
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++;

                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format($value->precioventa-$value->preciocompra,2,'.',''));
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content_util); 
                   
                   $cont_col++;    
                }

               $sheet->setCellValue("A".$cont_col,"REPORTE DE FARMACIA " .($tipo == 1?"(MÁS VENDIDOS)":"(MENOS VENDIDOS)"). " DEL: ".date('d/m/Y',strtotime($fechainicial)). " AL ".date('d/m/Y',strtotime($fechafinal)) );
               $sheet->getStyle("A".$cont_col)->applyFromArray($style_header);

                // Movimiento::whereIsNotNull('conveniofarmacia_id')


            });
        })->export('xls');
   
        // dd($productos);
    }

    public function excel04(Request $request){
        setlocale(LC_TIME, 'spanish');
        
        // $fechainicial     = Libreria::getParam($request->input('fi'));
        // $fechafinal       = Libreria::getParam($request->input('ff'));
        // $tipo       = Libreria::getParam($request->input('tipo'));
        // $top       = Libreria::getParam($request->input('top'));
        $origen       = Libreria::getParam($request->input('origen'));
        
        // $prod = $this->obtenerProductos($fechainicial,$fechafinal,$tipo,2146,$origen);
        // echo json_encode($prod);exit();

        // $resultado = Producto::leftJoin('lote','lote.producto_id','=','producto.id')
        // ->leftJoin('productoprincipio as rel_princ','rel_princ.producto_id','=','producto.id')
        // ->leftJoin('principioactivo as princ','princ.id','=','rel_princ.principioactivo_id')
        // ->leftJoin('especialidadfarmacia as especialidad','especialidad.id','=','producto.especialidadfarmacia_id')
        // ->leftJoin('origen','origen.id','=','producto.origen_id')
        // ->leftJoin('detallemovimiento as det','det.producto_id','=','producto.id')
        // ->leftJoin('movimiento as mov','mov.id','=','det.movimiento_id')
        // ->leftJoin('presentacion as pre','pre.id','=','producto.presentacion_id')
        // ->leftJoin('laboratorio','laboratorio.id','=','producto.laboratorio_id')
        // ->whereNull('producto.deleted_at')
        // ->whereNotIn('mov.situacion',['A','U'])
        // // ->whereIn('producto.id',$prod)
        // ->whereNull('det.deleted_at')
        // ->whereNull('mov.deleted_at')
        // ->whereNotNull('mov.serie')
        // ->where('producto.preciocompra','>=','0.10')
        // ->where('producto.origen_id','=',$origen)
        // ->whereBetween('mov.fecha',[$fechainicial,$fechafinal])
        // ->select('producto.id','producto.nombre', 'producto.preciocompra','producto.preciokayros','producto.precioventa','origen.nombre as origen','pre.nombre as presentacion','especialidad.nombre as especialidad','princ.nombre as principioActivo','lote.id as lote','laboratorio.nombre as laboratorio')
        // ->groupBy('lote.producto_id'/*,'producto.nombre','producto.preciocompra','producto.preciokayros','producto.precioventa','origen.nombre','pre.nombre','especialidad.nombre','princ.nombre','lote.id','laboratorio.nombre'*/);

         // $movimientos = Movimiento::leftJoin('detallemovimiento as det','movimiento.id','=','det.movimiento_id')
         //              ->whereNotIn('movimiento.situacion',['A','U'])
         //              ->whereNotNull('movimiento.conveniofarmacia_id')  
         //              // ->where('movimiento.conveniofarmacia_id','=',$value02->id)
         //              ->whereNotNull('movimiento.serie')
         //              ->whereNull('det.deleted_at')
         //              ->whereNull('movimiento.deleted_at')
         //              ->whereIn('movimiento.tipodocumento_id',[15,5])
         //              ->where('movimiento.tipomovimiento_id','=',4)
         //              ->where('det.producto_id','=',$value->id)
         //              ->whereBetween('movimiento.fecha',[$fechainicial,$fechafinal])
         //              ->whereNotIn('movimiento.conveniofarmacia_id',[1,7,13,14,16,17,20,21,15])
         //              ->select(DB::raw('SUM(det.cantidad) as cantidad'), DB::raw('SUM(det.subTotal) as subTotal'),'movimiento.conveniofarmacia_id')
         //              ->groupBy('movimiento.conveniofarmacia_id')
         //              ->get();
                    
        // ->orderBy('producto.preciocompra','DESC')
        
        // if ($tipo == '1') {
            # code...
        $resultado = DB::select('CALL sp_principio_activo_gerencia(?)',array($origen));      
            // $resultado = $resultado->orderByRaw('SUM(det.cantidad) DESC');
        // }else{
        //     $resultado = DB::select('CALL sp_reporte_doctorT_asc(?,?,?)',array($fechainicial,$fechafinal,$origen));            
        // }
        // ->get();

        // if($tipo == ''){
        //    $resultado = $resultado->orderBy('producto.id');
        // }
        // dd($resultado->toSql());
        // if($top != ''){
        //     $resultado =$resultado->take(20)->get();
        // }else{
        //     $resultado =$resultado->take(100)->get();
        // }

        Excel::create('Excel Gerencia Por Principio Activo', function($excel) use($resultado) {
            
            $excel->sheet('Farmacia', function($sheet) use($resultado) {
                $default_border = array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb'=>'000000')
                );

                $style_header = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 10,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );


                $style_header02 = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 10,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );


                $style_header03 = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 10,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,        
                       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );

                $style_content = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'E4EAF9'),
                    ),
                    'font' => array(
                        'size' => 8,
                        'color' => array('rgb' => '000000'),
                    )
                );

                $style_content_util = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'E3F739'),
                    ),
                    'font' => array(
                        'size' => 8,
                        'color' => array('rgb' => '000000'),
                    )
                );


                $sheet->setCellValue("A1", "DATOS DEL PRODUCTO");
                $sheet->setCellValue("I1", "TARIFAS");
                // $sheet->setCellValue("M1", "DATOS DEL PRODUCTO");
                $sheet->mergeCells("A1:H1");
                $sheet->getStyle("A1:H1")->applyFromArray($style_header);
                
                $sheet->setCellValue("A2", "PRODUCTO");
                $sheet->setCellValue("B2", "ORIGEN");
                $sheet->setCellValue("C2", "PRESENTACION");
                $sheet->setCellValue("D2", "ESPECIALIDAD");
                $sheet->setCellValue("E2", "PRINCIPIO ACTIVO");
                $sheet->setCellValue("F2", "LOTE");
                $sheet->setCellValue("G2", "LABORATORIO");
                $sheet->setCellValue("H2", "P. COMPRA (UNITARIO)");
                
                $sheet->mergeCells("A2:A3");
                $sheet->mergeCells("B2:B3");
                $sheet->mergeCells("C2:C3");
                $sheet->mergeCells("D2:D3");
                $sheet->mergeCells("E2:E3");
                $sheet->mergeCells("F2:F3");
                $sheet->mergeCells("G2:G3");
                $sheet->mergeCells("H2:H3");

                $sheet->getStyle("A2")->applyFromArray($style_header);
                $sheet->getStyle("B2")->applyFromArray($style_header);
                $sheet->getStyle("C2")->applyFromArray($style_header);
                $sheet->getStyle("D2")->applyFromArray($style_header);
                $sheet->getStyle("E2")->applyFromArray($style_header);
                $sheet->getStyle("F2")->applyFromArray($style_header);
                $sheet->getStyle("G2")->applyFromArray($style_header);
                $sheet->getStyle("H2")->applyFromArray($style_header);
            
                $sheet->getStyle("A2")->applyFromArray($style_header02);
                $sheet->getStyle("B2")->applyFromArray($style_header02);
                $sheet->getStyle("C2")->applyFromArray($style_header02);
                $sheet->getStyle("D2")->applyFromArray($style_header02);
                $sheet->getStyle("E2")->applyFromArray($style_header02);
                $sheet->getStyle("F2")->applyFromArray($style_header02);
                $sheet->getStyle("G2")->applyFromArray($style_header02);
                $sheet->getStyle("H2")->applyFromArray($style_header02);
              
              
                $cont = 9;                                         
                // $convenios = Conveniofarmacia::all(); //whereNotIn('id',[1,7,13,14,16,17,20,21,15])->get();
                $convenios = Conveniofarmacia::whereNotIn('id',[1,7,11,13,14,16,17,20,21,15])->get();
                // dd($convenios);
                foreach ($convenios as $key => $value) {
                    if($value->id == 19 || $value->id == 6 || $value->id == 10 || $value->id == 2 || $value->id == 3){
                        if($value->id == 19){
                            $sheet->setCellValue($this->convertirLetraExcel($cont)."2", $value->nombre.' (25%,10%)'); 
                        }else{
                            if($value->id == 10){
                                $sheet->setCellValue($this->convertirLetraExcel($cont)."2", $value->nombre.' (25%,30%)'); 
                            }else{
                                if($value->id == 2){
                                   $sheet->setCellValue($this->convertirLetraExcel($cont)."2", 'MAPFRE, FEBAN, POSITIVA, INTERSEGURO, BNP PARIBAS CARDIF S.A., PROTECTA, CHUBB SEGUROS, CRECER SEGUROS ('.$value->kayros .' %)'); 
                                }else{
                                    if($value->id == 3 || $value->id == 6 ){
                                        $sheet->setCellValue($this->convertirLetraExcel($cont)."2",($value->id==3?'PACIFICO PPS, PACIFICO EPS':$value->nombre).' ('.$value->kayros .' %)'); 
                                    }
                                }
                            }
                        }
                        $sheet->mergeCells($this->convertirLetraExcel($cont)."2:".$this->convertirLetraExcel($cont+2)."2");
                   
                        $sheet->getStyle($this->convertirLetraExcel($cont)."2:".$this->convertirLetraExcel($cont+2)."2")->applyFromArray($style_header);
                        $sheet->setCellValue($this->convertirLetraExcel($cont)."3",'CANT.');
                        $sheet->setCellValue($this->convertirLetraExcel($cont+1)."3",'VENTA');
                        // $sheet->setCellValue($this->convertirLetraExcel($cont+1)."3",'P.V.');
                        $sheet->setCellValue($this->convertirLetraExcel($cont+2)."3",'UTILIDAD');

                        $sheet->getStyle($this->convertirLetraExcel($cont)."3")->applyFromArray($style_header);
                        $sheet->getStyle($this->convertirLetraExcel($cont+1)."3")->applyFromArray($style_header);
                        $sheet->getStyle($this->convertirLetraExcel($cont+2)."3")->applyFromArray($style_header);
                       $cont+=3;
                    }
                }
                
                $sheet->mergeCells("I1:".$this->convertirLetraExcel($cont-1)."1");
                $sheet->getStyle("I1:".$this->convertirLetraExcel($cont-1)."1")->applyFromArray($style_header);
            
                $sheet->setCellValue($this->convertirLetraExcel($cont)."1",'TARIFA PARTICULAR');
                $sheet->setCellValue($this->convertirLetraExcel($cont)."3",'CANT.');
                $sheet->setCellValue($this->convertirLetraExcel($cont+1)."3",'VENTA');  
                $sheet->setCellValue($this->convertirLetraExcel($cont+2)."3",'UTILIDAD');
                // $sheet->setCellValue($this->convertirLetraExcel($cont+7)."1",'GANANCIA (S/)');
                  
                $sheet->mergeCells($this->convertirLetraExcel($cont)."1:".$this->convertirLetraExcel($cont+2)."1");
                $sheet->mergeCells($this->convertirLetraExcel($cont)."1:".$this->convertirLetraExcel($cont)."2");
                // $sheet->mergeCells($this->convertirLetraExcel($cont)."3:".$this->convertirLetraExcel($cont)."2");

                $sheet->getStyle($this->convertirLetraExcel($cont)."1")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont)."1")->applyFromArray($style_header03);
                
                $sheet->getStyle($this->convertirLetraExcel($cont)."3")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont+1)."3")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont+2)."3")->applyFromArray($style_header);
   
                $cont_col = 4;

                foreach($resultado as $key => $value){
                   $sheet->setCellValue("A".$cont_col, $value->nombre);
                   $sheet->getStyle("A".$cont_col)->applyFromArray($style_content);
                
                   $sheet->setCellValue("B".$cont_col, $value->origen);
                   $sheet->getStyle("B".$cont_col)->applyFromArray($style_content);
                  
                   $sheet->setCellValue("C".$cont_col, $value->presentacion);
                   $sheet->getStyle("C".$cont_col)->applyFromArray($style_content);
                
                   $sheet->setCellValue("D".$cont_col, $value->especialidad);
                   $sheet->getStyle("D".$cont_col)->applyFromArray($style_content);
                
                   $sheet->setCellValue("E".$cont_col, $value->principioActivo);
                   $sheet->getStyle("E".$cont_col)->applyFromArray($style_content); 

                   $sheet->setCellValue("F".$cont_col, $value->lote);
                   $sheet->getStyle("F".$cont_col)->applyFromArray($style_content); 
   
                   $sheet->setCellValue("G".$cont_col, $value->laboratorio);
                   $sheet->getStyle("G".$cont_col)->applyFromArray($style_content); 

                   $sheet->setCellValue("H".$cont_col, $value->preciocompra);
                   $sheet->getStyle("H".$cont_col)->applyFromArray($style_content); 
                    
                   $cont_let = 9; 
                   
                     // $sheet->getStyle("I".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("J".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("K".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("L".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("M".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("N".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("O".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("P".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("Q".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("R".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("S".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("T".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("U".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("V".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("W".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("X".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("Y".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("Z".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AA".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AB".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AC".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AD".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AE".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AF".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AG".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AH".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AI".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AJ".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AK".$cont_col)->applyFromArray($style_content); 
                     // $sheet->getStyle("AL".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AM".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AN".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AO".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AP".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AQ".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AR".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AS".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AT".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AU".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AV".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AW".$cont_col)->applyFromArray($style_content); 
                         // $sheet->getStyle("AX".$cont_col)->applyFromArray($style_content); 


                       // $convenios = Conveniofarmacia::all();
                       // $convenios = Conveniofarmacia::whereNotIn('id',[1,7,13,14,16,17,20,21,15])->get();
                       $convenios = Conveniofarmacia::whereNotIn('id',[1,7,11,13,14,16,17,20,21,15])->get();
            
                       foreach ($convenios as $keyC => $valueC) {
                           if($valueC->id == 19 || $valueC->id == 6 || $valueC->id == 10 || $valueC->id == 2 || $valueC->id == 3){
                       
                               if($valueC->id != 19 && $valueC->id != 10){
                                   $venta = $value->preciokayros-($value->preciokayros*$valueC->kayros/100);//sum('det.subtotal');
                                   $utilidad = $venta-$value->preciocompra;
                               }else{
                                    if($valueC->id == 19){
                                        if($value->origen == 'M'){
                                           $venta = $value->precioventa-(25*$value->precioventa/100);//sum('det.subtotal');
                                           $utilidad = $venta-$value->preciocompra;
                                        }else{
                                            if ($value->origen == 'G') {
                                               $venta = $value->precioventa-(10*$value->precioventa/100);//sum('det.subtotal');
                                               $utilidad = $venta-$value->preciocompra;
                                            }else{
                                               $venta = $value->precioventa-(0*$value->precioventa/100);//sum('det.subtotal');
                                               $utilidad = $venta-$value->preciocompra;
                                                
                                            }
                                        }
                                    }else{
                                        if($value->origen == 'M'){
                                           $venta = $value->preciokayros-(25*$value->preciokayros/100);//sum('det.subtotal');
                                           $utilidad = $venta-$value->preciocompra;
                                        }else{
                                            if ($value->origen == 'G') {
                                               $venta = $value->preciokayros-(30*$value->preciokayros/100);//sum('det.subtotal');
                                               $utilidad = $venta-$value->preciocompra;
                                            }else{
                                               $venta = $value->precioventa-(0*$value->precioventa/100);//sum('det.subtotal');
                                               $utilidad = $venta-$value->preciocompra;
                                                
                                            }
                                        }
                                    }
                               }

                               $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format(1,2,'.',''));
                               $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                               $cont_let++;

                               $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format($venta,2,'.',''));
                               $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                               $cont_let++;
                                
                               $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format($utilidad,2,'.',''));
                               $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content_util); 
                               $cont_let++;
                            }
                       }        

                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format(1,2,'.',''));
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++;
                        
                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format($value->precioventa,2,'.',''));
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++;

                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format($value->precioventa-$value->preciocompra,2,'.',''));
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content_util); 
                   
                   $cont_col++;    
                }

               // $sheet->setCellValue("A".$cont_col,"REPORTE DE FARMACIA " .($tipo == 1?"(MÁS VENDIDOS)":"(MENOS VENDIDOS)"). " DEL: ".date('d/m/Y',strtotime($fechainicial)). " AL ".date('d/m/Y',strtotime($fechafinal)) );
               // $sheet->getStyle("A".$cont_col)->applyFromArray($style_header);

                // Movimiento::whereIsNotNull('conveniofarmacia_id')


            });
        })->export('xls');
   
        // dd($productos);
    }

    
    public function excel05(Request $request){
        setlocale(LC_TIME, 'spanish');
        
     
        $principios = PrincipioActivo::orderBy('nombre','ASC')->get();

        Excel::create('Excel Gerencia Analisis Principio Activo', function($excel) use($principios) {
            
            $excel->sheet('Farmacia', function($sheet) use($principios) {
                $default_border = array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb'=>'000000')
                );

                $style_header = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 10,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );


                $style_header02 = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 10,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );


                $style_header03 = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 10,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,        
                       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );
                // E4EAF9
                $style_content = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'FFFFFF'),
                    ),
                    'font' => array(
                        'size' => 8,
                        'color' => array('rgb' => '000000'),
                    )
                );


                $style_content_principio = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'FFFFFF'),
                    ),
                    'font' => array(
                        'size' => 8,
                        'color' => array('rgb' => '000000'),
                    ),
                    'alignment' => array(
                       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,        
                       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );

                $style_content_util = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'E3F739'),
                    ),
                    'font' => array(
                        'size' => 8,
                        'color' => array('rgb' => '000000'),
                    )
                );


                $sheet->setCellValue("A1", "DATOS DEL PRODUCTO");
                $sheet->setCellValue("I1", "TARIFAS");
                // $sheet->setCellValue("M1", "DATOS DEL PRODUCTO");
                $sheet->mergeCells("A1:H1");
                $sheet->getStyle("A1:H1")->applyFromArray($style_header);
                
                $sheet->setCellValue("A2", "PRINCIPIO ACTIVO");
                $sheet->setCellValue("B2", "PRODUCTO");
                $sheet->setCellValue("C2", "ORIGEN");
                $sheet->setCellValue("D2", "PRESENTACION");
                $sheet->setCellValue("E2", "ESPECIALIDAD");
                $sheet->setCellValue("F2", "LOTE");
                $sheet->setCellValue("G2", "LABORATORIO");
                $sheet->setCellValue("H2", "P. COMPRA (UNITARIO)");
                
                $sheet->mergeCells("A2:A3");
                $sheet->mergeCells("B2:B3");
                $sheet->mergeCells("C2:C3");
                $sheet->mergeCells("D2:D3");
                $sheet->mergeCells("E2:E3");
                $sheet->mergeCells("F2:F3");
                $sheet->mergeCells("G2:G3");
                $sheet->mergeCells("H2:H3");

                $sheet->getStyle("A2")->applyFromArray($style_header);
                $sheet->getStyle("B2")->applyFromArray($style_header);
                $sheet->getStyle("C2")->applyFromArray($style_header);
                $sheet->getStyle("D2")->applyFromArray($style_header);
                $sheet->getStyle("E2")->applyFromArray($style_header);
                $sheet->getStyle("F2")->applyFromArray($style_header);
                $sheet->getStyle("G2")->applyFromArray($style_header);
                $sheet->getStyle("H2")->applyFromArray($style_header);
            
                $sheet->getStyle("A2")->applyFromArray($style_header02);
                $sheet->getStyle("B2")->applyFromArray($style_header02);
                $sheet->getStyle("C2")->applyFromArray($style_header02);
                $sheet->getStyle("D2")->applyFromArray($style_header02);
                $sheet->getStyle("E2")->applyFromArray($style_header02);
                $sheet->getStyle("F2")->applyFromArray($style_header02);
                $sheet->getStyle("G2")->applyFromArray($style_header02);
                $sheet->getStyle("H2")->applyFromArray($style_header02);
              
              

                $cont = 9;                                         
                // $convenios = Conveniofarmacia::all(); //whereNotIn('id',[1,7,13,14,16,17,20,21,15])->get();
                $convenios = Conveniofarmacia::whereNotIn('id',[1,7,11,13,14,16,17,20,21,15])->get();
                foreach ($convenios as $key => $value) {
                    if($value->id == 19 || $value->id == 6 || $value->id == 10 || $value->id == 2 || $value->id == 3){
                        if($value->id == 19){
                                $sheet->setCellValue($this->convertirLetraExcel($cont)."2", $value->nombre.' (25%,10%)'); 
                         }else{
                            if($value->id == 10){
                                $sheet->setCellValue($this->convertirLetraExcel($cont)."2", $value->nombre.' (25%,30%)'); 
                            }else{
                                if($value->id == 2){
                                   $sheet->setCellValue($this->convertirLetraExcel($cont)."2", 'MAPFRE, FEBAN, POSITIVA, INTERSEGURO, BNP PARIBAS CARDIF S.A., PROTECTA, CHUBB SEGUROS, CRECER SEGUROS ('.$value->kayros .' %)'); 
                                }else{
                                    if($value->id == 3 || $value->id == 6 ){
                                        $sheet->setCellValue($this->convertirLetraExcel($cont)."2",($value->id==3?'PACIFICO PPS, PACIFICO EPS':$value->nombre).' ('.$value->kayros .' %)'); 
                                    }
                                }
                            }
                        }
                    
                        $sheet->mergeCells($this->convertirLetraExcel($cont)."2:".$this->convertirLetraExcel($cont+2)."2");
                   
                        $sheet->getStyle($this->convertirLetraExcel($cont)."2:".$this->convertirLetraExcel($cont+2)."2")->applyFromArray($style_header);
                        $sheet->setCellValue($this->convertirLetraExcel($cont)."3",'CANT.');
                        $sheet->setCellValue($this->convertirLetraExcel($cont+1)."3",'VENTA');
                        // $sheet->setCellValue($this->convertirLetraExcel($cont+1)."3",'P.V.');
                        $sheet->setCellValue($this->convertirLetraExcel($cont+2)."3",'UTILIDAD');

                        $sheet->getStyle($this->convertirLetraExcel($cont)."3")->applyFromArray($style_header);
                        $sheet->getStyle($this->convertirLetraExcel($cont+1)."3")->applyFromArray($style_header);
                        $sheet->getStyle($this->convertirLetraExcel($cont+2)."3")->applyFromArray($style_header);
                        $cont+=3;
                    }
                }
                
                $sheet->mergeCells("I1:".$this->convertirLetraExcel($cont-1)."1");
                $sheet->getStyle("I1:".$this->convertirLetraExcel($cont-1)."1")->applyFromArray($style_header);
            
                $sheet->setCellValue($this->convertirLetraExcel($cont)."1",'TARIFA PARTICULAR');
                $sheet->setCellValue($this->convertirLetraExcel($cont)."3",'CANT.');
                $sheet->setCellValue($this->convertirLetraExcel($cont+1)."3",'VENTA');  
                $sheet->setCellValue($this->convertirLetraExcel($cont+2)."3",'UTILIDAD');
                // $sheet->setCellValue($this->convertirLetraExcel($cont+7)."1",'GANANCIA (S/)');
                  
                $sheet->mergeCells($this->convertirLetraExcel($cont)."1:".$this->convertirLetraExcel($cont+2)."1");
                $sheet->mergeCells($this->convertirLetraExcel($cont)."1:".$this->convertirLetraExcel($cont)."2");
                // $sheet->mergeCells($this->convertirLetraExcel($cont)."3:".$this->convertirLetraExcel($cont)."2");

                $sheet->getStyle($this->convertirLetraExcel($cont)."1")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont)."1")->applyFromArray($style_header03);
                
                $sheet->getStyle($this->convertirLetraExcel($cont)."3")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont+1)."3")->applyFromArray($style_header);
                $sheet->getStyle($this->convertirLetraExcel($cont+2)."3")->applyFromArray($style_header);
   
                $cont_col = 4;
                $inicio = 4;
                // $band = false;
                foreach ($principios as $key02 => $value02) {
                   $band = false;
                  
                   // $sheet->getStyle("A".$cont_col)->applyFromArray($style_content);
                   // dd($value02->id); 
                   $resultado = DB::select('CALL sp_reporte_analisis(?)',array($value02->id));    
                  // dd($resultado);
                   // $cont_celdas = $cont_col;
                   foreach($resultado as $key => $value){
                       // $cont_celdas++;
                       $band = true;
                       
                       $sheet->setCellValue("B".$cont_col, $value->nombre);
                       $sheet->getStyle("B".$cont_col)->applyFromArray($style_content);
                    
                       $sheet->setCellValue("C".$cont_col, $value->origen);
                       $sheet->getStyle("C".$cont_col)->applyFromArray($style_content);
                      
                       $sheet->setCellValue("D".$cont_col, $value->presentacion);
                       $sheet->getStyle("D".$cont_col)->applyFromArray($style_content);
                    
                       $sheet->setCellValue("E".$cont_col, $value->especialidad);
                       $sheet->getStyle("E".$cont_col)->applyFromArray($style_content);
                    
                       $sheet->setCellValue("F".$cont_col, $value->lote);
                       $sheet->getStyle("F".$cont_col)->applyFromArray($style_content); 

                       $sheet->setCellValue("G".$cont_col, $value->laboratorio);
                       $sheet->getStyle("G".$cont_col)->applyFromArray($style_content); 
       
                       $sheet->setCellValue("H".$cont_col, $value->preciocompra);
                       $sheet->getStyle("H".$cont_col)->applyFromArray($style_content); 

                       // $sheet->setCellValue("I".$cont_col, $value->preciocompra);
                       // $sheet->getStyle("I".$cont_col)->applyFromArray($style_content); 
                    
                       $cont_let = 9; 
                       $convenios = Conveniofarmacia::whereNotIn('id',[1,7,11,13,14,16,17,20,21,15])->get();
                
                       foreach ($convenios as $keyC => $valueC) {
                            if($valueC->id == 19 || $valueC->id == 6 || $valueC->id == 10 || $valueC->id == 2 || $valueC->id == 3){
            
                               if($valueC->id != 19 && $valueC->id != 10){
                                   $venta = $value->preciokayros-($value->preciokayros*$valueC->kayros/100);//sum('det.subtotal');
                                   $utilidad = $venta-$value->preciocompra;
                               }else{
                                    if($valueC->id == 19){
                                        if($value->origen == 'M'){
                                           $venta = $value->precioventa-(25*$value->precioventa/100);//sum('det.subtotal');
                                           $utilidad = $venta-$value->preciocompra;
                                        }else{
                                            if ($value->origen == 'G') {
                                               $venta = $value->precioventa-(10*$value->precioventa/100);//sum('det.subtotal');
                                               $utilidad = $venta-$value->preciocompra;
                                            }else{
                                               $venta = $value->precioventa-(0*$value->precioventa/100);//sum('det.subtotal');
                                               $utilidad = $venta-$value->preciocompra;
                                                
                                            }
                                        }
                                    }else{
                                        if($value->origen == 'M'){
                                           $venta = $value->preciokayros-(25*$value->preciokayros/100);//sum('det.subtotal');
                                           $utilidad = $venta-$value->preciocompra;
                                        }else{
                                            if ($value->origen == 'G') {
                                               $venta = $value->preciokayros-(30*$value->preciokayros/100);//sum('det.subtotal');
                                               $utilidad = $venta-$value->preciocompra;
                                            }else{
                                               $venta = $value->precioventa-(0*$value->precioventa/100);//sum('det.subtotal');
                                               $utilidad = $venta-$value->preciocompra;
                                                
                                            }
                                        }
                                    }
                               }

                               $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format(1,2,'.',''));
                               $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                               $cont_let++;

                               $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format($venta,2,'.',''));
                               $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                               $cont_let++;
                                
                               $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format($utilidad,2,'.',''));
                               $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content_util); 
                               $cont_let++;
                            }
                       }        

                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format(1,2,'.',''));
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++;
                        
                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format($value->precioventa,2,'.',''));
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content); 
                       $cont_let++;

                       $sheet->setCellValue($this->convertirLetraExcel($cont_let).$cont_col,number_format($value->precioventa-$value->preciocompra,2,'.',''));
                       $sheet->getStyle($this->convertirLetraExcel($cont_let).$cont_col)->applyFromArray($style_content_util); 
                    
                       $cont_col++;
                   }                
                

                    if($band == true){
                        // if($inicio != 4){
                        //     dd($inicio, $cont_col);
                        // }
                        // dd($inicio, $cont_col);

                        // if ($inicio < $cont_col) {
                        //        $sheet->getStyle("A".$inicio.":A".$cont_col)->applyFromArray($style_content_principio);
                        // }
                            $sheet->mergeCells("A".$inicio.":A".($cont_col-1));
                            $sheet->setCellValue("A".$inicio, $value02->nombre);
                            $sheet->getStyle("A".$inicio)->applyFromArray($style_content_principio);
                         

                            // $sheet->mergeCells("A".$inicio.":A".$cont_col);
                            $inicio = $cont_col ;          
                            $band  = false;                            
                    }          


                    // $band = false;
                }                       

               // $sheet->setCellValue("A".$cont_col,"REPORTE DE FARMACIA " .($tipo == 1?"(MÁS VENDIDOS)":"(MENOS VENDIDOS)"). " DEL: ".date('d/m/Y',strtotime($fechainicial)). " AL ".date('d/m/Y',strtotime($fechafinal)) );
               // $sheet->getStyle("A".$cont_col)->applyFromArray($style_header);

                // Movimiento::whereIsNotNull('conveniofarmacia_id')


            });
        })->export('xls');
   
        // dd($productos);
    }



    public function excel02(Request $request){
        $fechainicial     = Libreria::getParam($request->input('fi'));
        $fechafinal       = Libreria::getParam($request->input('ff'));
        $tipo           = Libreria::getParam($request->input('tipo'));
        $top            = Libreria::getParam($request->input('top'));
        $origen         = Libreria::getParam($request->input('origen'));
        $convenio       = Libreria::getParam($request->input('convenio'));
        $tipo       = Libreria::getParam($request->input('tipo'));
       
        $top = (is_null($top)?100:20);

        if($tipo == '' || $tipo == 1){
            $resultado = DB::select('CALL sp_reporte_gerencia(?,?,?,?,?)',array($fechainicial,$fechafinal,$origen,$convenio,$top));      
        }else{
            $resultado = DB::select('CALL sp_reportegerencia_02(?,?,?,?,?)',array($fechainicial,$fechafinal,$origen,$convenio,$top));      
        }
  
        // dd($resultado);

        setlocale(LC_TIME, 'spanish');
        
        Excel::create('Excel General', function($excel) use($resultado,$fechainicial, $fechafinal, $convenio) {
            
            $excel->sheet('Farmacia', function($sheet) use($resultado,$fechainicial, $fechafinal,$convenio) {
                $default_border = array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb'=>'000000')
                );

                $style_header = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 12,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );


                $style_header02 = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 12,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );


                $style_header03 = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'1A75A0'),
                    ),
                    'font' => array(
                        'bold' => true,
                        'size' => 12,
                        'color' => array('rgb' => 'FFFFFF'),
                    ),
                    'alignment' => array(
                       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,        
                       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );

                $style_content = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb'=>'E4EAF9'),
                    ),
                    'font' => array(
                        'size' => 10,
                        'color' => array('rgb' => '000000'),
                    )
                );

                $convenio = Conveniofarmacia::where('id','=',$convenio)->first();
                // $sheet->setCellValue("H1",strtoupper($convenio->nombre));
                
                $sheet->setCellValue("A1", "DATOS DEL PRODUCTO");
                $sheet->setCellValue("H1", "PLAN: ".strtoupper($convenio->nombre));
                // $sheet->setCellValue("M1", "DATOS DEL PRODUCTO");
                $sheet->mergeCells("A1:G1");
                $sheet->getStyle("A1:G1")->applyFromArray($style_header);
                
                $sheet->setCellValue("A2", "PRODUCTO");
                $sheet->setCellValue("B2", "ORIGEN");
                $sheet->setCellValue("C2", "PRESENTACION");
                $sheet->setCellValue("D2", "ESPECIALIDAD");
                $sheet->setCellValue("E2", "PRINCIPIO ACTIVO");
                $sheet->setCellValue("F2", "LABORATORIO");
                $sheet->setCellValue("G2", "P. COMPRA");
                
                $sheet->mergeCells("A2:A3");
                $sheet->mergeCells("B2:B3");
                $sheet->mergeCells("C2:C3");
                $sheet->mergeCells("D2:D3");
                $sheet->mergeCells("E2:E3");
                $sheet->mergeCells("F2:F3");
                $sheet->mergeCells("G2:G3");
                // $sheet->mergeCells("H2:H3");

                $sheet->getStyle("A2")->applyFromArray($style_header);
                $sheet->getStyle("B2")->applyFromArray($style_header);
                $sheet->getStyle("C2")->applyFromArray($style_header);
                $sheet->getStyle("D2")->applyFromArray($style_header);
                $sheet->getStyle("E2")->applyFromArray($style_header);
                $sheet->getStyle("F2")->applyFromArray($style_header);
                $sheet->getStyle("G2")->applyFromArray($style_header);
                // $sheet->getStyle("H2")->applyFromArray($style_header);
            
                $sheet->getStyle("A2")->applyFromArray($style_header02);
                $sheet->getStyle("B2")->applyFromArray($style_header02);
                $sheet->getStyle("C2")->applyFromArray($style_header02);
                $sheet->getStyle("D2")->applyFromArray($style_header02);
                $sheet->getStyle("E2")->applyFromArray($style_header02);
                $sheet->getStyle("F2")->applyFromArray($style_header02);
                $sheet->getStyle("G2")->applyFromArray($style_header02);
                // $sheet->getStyle("H2")->applyFromArray($style_header02);
              
              
                $cont = 9;   
            

           
                //whereNotIn('id',[1,7,13,14,16,17,20,21,15])->get();
            
                $sheet->mergeCells("H1:K1");
                // dd($this->convertirLetraExcel($cont));
                $sheet->getStyle("H1:K1")->applyFromArray($style_header);
            
                $sheet->setCellValue('H2','GUIAS');
                $sheet->setCellValue('J2','BOLETAS/FACTURAS');
                // $sheet->setCellValue('L2','FACTURAS');
               
                $sheet->mergeCells('H2:I2');
                $sheet->mergeCells('J2:K2');
                // $sheet->mergeCells('L2:M2');
                
                // dd(($this->convertirLetraExcel($cont+6)));
                $sheet->getStyle("H2:I2")->applyFromArray($style_header);
                $sheet->getStyle("J2:K2")->applyFromArray($style_header);
                // $sheet->getStyle("L2:M2")->applyFromArray($style_header);
               
                $sheet->setCellValue("H3",'CANT (UNID)');
                $sheet->setCellValue("I3",'TOTAL (S/)');
             
                $sheet->setCellValue("J3",'CANT (UNID)');
                $sheet->setCellValue("K3",'TOTAL (S/)');
               
                // $sheet->setCellValue("L3",'PREC (S/)');
                // $sheet->setCellValue("M3",'CANT (UND)');
                
                $sheet->getStyle("H3")->applyFromArray($style_header);
                $sheet->getStyle("I3")->applyFromArray($style_header);
                $sheet->getStyle("J3")->applyFromArray($style_header);
                $sheet->getStyle("K3")->applyFromArray($style_header);
                // $sheet->getStyle("L3")->applyFromArray($style_header);
                // $sheet->getStyle("M3")->applyFromArray($style_header);

               
                $cont_col = 4;
                // dd($resultado);

                foreach($resultado as $key => $value){
                    // dd($value);
                   $sheet->setCellValue("A".$cont_col, $value->nombre);
                   $sheet->getStyle("A".$cont_col)->applyFromArray($style_content);
                
                   $sheet->setCellValue("B".$cont_col, $value->origen);
                   $sheet->getStyle("B".$cont_col)->applyFromArray($style_content);
                  
                   $sheet->setCellValue("C".$cont_col, $value->presentacion);
                   $sheet->getStyle("C".$cont_col)->applyFromArray($style_content);
                
                   $sheet->setCellValue("D".$cont_col, $value->especialidad);
                   $sheet->getStyle("D".$cont_col)->applyFromArray($style_content);
                
                   $sheet->setCellValue("E".$cont_col, $value->principioActivo);
                   $sheet->getStyle("E".$cont_col)->applyFromArray($style_content); 

                   $sheet->setCellValue("F".$cont_col, $value->laboratorio);
                   $sheet->getStyle("F".$cont_col)->applyFromArray($style_content); 
   
                   $sheet->setCellValue("G".$cont_col, $value->preciocompra);
                   $sheet->getStyle("G".$cont_col)->applyFromArray($style_content); 

                   if($value->tipodocumento_id == 15){
                       $sheet->setCellValue("H".$cont_col, $value->total);
                       $sheet->setCellValue("I".$cont_col, $value->subTotal);
                   }

                   if($convenio->id == 19 || $convenio->id = 14){
                      $particular =Movimiento::leftJoin('detallemovimiento as det','movimiento.id','=','det.movimiento_id')
                          ->whereNotIn('movimiento.situacion',['A','U'])
                          ->where('movimiento.conveniofarmacia_id','=',$convenio->id)
                          ->whereNotNull('movimiento.serie')
                          ->whereNull('det.deleted_at')
                          ->whereNull('movimiento.deleted_at')
                          ->whereIn('movimiento.tipodocumento_id',[4,5])
                          ->where('movimiento.tipomovimiento_id','=',4)
                          ->where('movimiento.serie','=','4')
                          // ->where('movimiento.caja_id','=','4')
                          ->where('det.producto_id','=',$value->id)
                          ->whereBetween('movimiento.fecha',[$fechainicial,$fechafinal])
                          ->select(DB::raw('SUM(det.cantidad) as cantidad'), DB::raw('SUM(det.subTotal) as subTotal'))
                          ->groupBy('det.producto_id')
                          ->first();
                   
                   }else{
                         $particular =Movimiento::leftJoin('detallemovimiento as det','movimiento.id','=','det.movimiento_id')
                          ->whereNotIn('movimiento.situacion',['A','U'])
                          ->whereNull('movimiento.conveniofarmacia_id')
                          ->whereNotNull('movimiento.serie')
                          ->whereNull('det.deleted_at')
                          ->whereNull('movimiento.deleted_at')
                          ->whereIn('movimiento.tipodocumento_id',[4,5])
                          ->where('movimiento.tipomovimiento_id','=',4)
                          ->where('movimiento.serie','=','4')
                          // ->where('movimiento.caja_id','=','4')
                          ->where('det.producto_id','=',$value->id)
                          ->whereBetween('movimiento.fecha',[$fechainicial,$fechafinal])
                          ->select(DB::raw('SUM(det.cantidad) as cantidad'), DB::raw('SUM(det.subTotal) as subTotal'))
                          ->groupBy('det.producto_id')
                          ->first();
                      // dd($particular,$value->id);
                   }
                  

                    if(!is_null($particular)){
                        $sheet->setCellValue("J".$cont_col, $particular->cantidad);
                        $sheet->setCellValue("K".$cont_col, $particular->subTotal);

                    }
                   

                
                   $sheet->getStyle("H".$cont_col)->applyFromArray($style_content); 
                   $sheet->getStyle("I".$cont_col)->applyFromArray($style_content); 
                   $sheet->getStyle("J".$cont_col)->applyFromArray($style_content); 
                   $sheet->getStyle("K".$cont_col)->applyFromArray($style_content); 
                                        


                   $cont_col++;    
                }

                // exit();
             });
        })->export('xls');
   
        // dd($productos);
    }

    public function convertirLetraExcel($cant){
        $valor = '';
        switch($cant){
            case '9': $valor = 'I';break;
            case '10': $valor = 'J';break;
            case '11': $valor = 'K';break;
            case '12': $valor = 'L';break;
            case '13': $valor = 'M';break;
            case '14': $valor = 'N';break;
            case '15': $valor = 'O';break;
            case '16': $valor = 'P';break;
            case '17': $valor = 'Q';break;
            case '18': $valor = 'R';break;
            case '19': $valor = 'S';break;
            case '20': $valor = 'T';break;
            case '21': $valor = 'U';break;
            case '22': $valor = 'V';break;
            case '23': $valor = 'W';break;
            case '24': $valor = 'X';break;
            case '25': $valor = 'Y';break;
            case '26': $valor = 'Z';break;
            case '27': $valor = 'AA';break;
            case '28': $valor = 'AB';break;
            case '29': $valor = 'AC';break;
            case '30': $valor = 'AD';break;
            case '31': $valor = 'AE';break;
            case '32': $valor = 'AF';break;
            case '33': $valor = 'AG';break;
            case '34': $valor = 'AH';break;
            case '35': $valor = 'AI';break;
            case '36': $valor = 'AJ';break;
            case '37': $valor = 'AK';break;
            case '38': $valor = 'AL';break;
            case '39': $valor = 'AM';break;
            case '40': $valor = 'AN';break;
            case '41': $valor = 'AO';break;
            case '42': $valor = 'AP';break;
            case '43': $valor = 'AQ';break;
            case '44': $valor = 'AR';break;
            case '45': $valor = 'AS';break;
            case '46': $valor = 'AT';break;
            case '47': $valor = 'AU';break;
            case '48': $valor = 'AV';break;
            case '49': $valor = 'AW';break;
            case '50': $valor = 'AX';break;
            case '51': $valor = 'AY';break;
            case '52': $valor = 'AZ';break;
            
            case '53': $valor = 'BA';break;
            case '54': $valor = 'BB';break;
            case '55': $valor = 'BC';break;
            case '56': $valor = 'BD';break;
            case '57': $valor = 'BE';break;
            case '58': $valor = 'BF';break;
            case '59': $valor = 'BG';break;
            case '60': $valor = 'BH';break;
            case '61': $valor = 'BI';break;
            case '62': $valor = 'BJ';break;
            case '63': $valor = 'BK';break;
            case '64': $valor = 'BL';break;
            case '65': $valor = 'BM';break;
            case '66': $valor = 'BN';break;
            case '67': $valor = 'BO';break;
            case '68': $valor = 'BP';break;
            case '69': $valor = 'BQ';break;
            case '70': $valor = 'BR';break;
            case '71': $valor = 'BS';break;
            case '72': $valor = 'BT';break;
            case '73': $valor = 'BU';break;
            case '74': $valor = 'BV';break;
            case '75': $valor = 'BW';break;
            case '76': $valor = 'BX';break;
            case '77': $valor = 'BY';break;
            case '78': $valor = 'BZ';break;

            case '79': $valor = 'CA';break;
            case '80': $valor = 'CB';break;
            case '81': $valor = 'CC';break;
            case '82': $valor = 'CD';break;
            case '83': $valor = 'CE';break;
            case '84': $valor = 'CF';break;
            case '85': $valor = 'CG';break;
            case '86': $valor = 'CH';break;
            case '87': $valor = 'CI';break;
            case '88': $valor = 'CJ';break;
            case '89': $valor = 'CK';break;
            case '90': $valor = 'CL';break;
            case '91': $valor = 'CM';break;
            case '92': $valor = 'CN';break;
            case '93': $valor = 'CO';break;
            case '94': $valor = 'CP';break;
            case '95': $valor = 'CQ';break;
            case '96': $valor = 'CR';break;
            case '97': $valor = 'CS';break;
            case '98': $valor = 'CT';break;
            case '99': $valor = 'CU';break;
            case '100': $valor = 'CV';break;
            case '101': $valor = 'CW';break;
            case '102': $valor = 'CX';break;
            case '103': $valor = 'CY';break;
            case '104': $valor = 'CZ';break;

                  
        }

        return $valor;
    }
}
