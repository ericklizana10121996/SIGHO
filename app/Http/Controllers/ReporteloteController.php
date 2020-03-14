<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Producto;
use App\Origen;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Excel;


/**
 * PagodoctorController
 * 
 * @package 
 * @author DaYeR
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class ReporteloteController extends Controller
{
    protected $folderview      = 'app.reportelote';
    protected $tituloAdmin     = 'Reporte de Lote';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Modificar Referido Tomografia';
    protected $tituloEliminar  = 'Eliminar Concepto de Pago';
    protected $rutas           = array('create' => 'reportelote.create', 
            'edit'   => 'reportelote.edit', 
            'delete' => 'reportelote.eliminar',
            'search' => 'reportelote.buscar',
            'index'  => 'reportelote.index',
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Reportelote';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Producto::join('lote','lote.producto_id','=','producto.id')
                        ->join('presentacion','presentacion.id','=','producto.presentacion_id')
                        ->join('origen','origen.id','=','producto.origen_id')
                        ->where('producto.nombre', 'LIKE', '%'.strtoupper($nombre).'%')->where(function ($query) use($request){
                        if ($request->input('tipo') !== null && $request->input('tipo') !== '') {
                            $query->where('tipo', '=', $request->input('tipo'));
                        }
                        if ($request->input('categoria_id2') !== null && $request->input('categoria_id2') !== '') {
                            $query->where('categoria_id', '=', $request->input('categoria_id2'));
                        }
                        if ($request->input('especialidadfarmacia_id2') !== null && $request->input('especialidadfarmacia_id2') !== '') {
                            $query->where('especialidadfarmacia_id', '=', $request->input('especialidadfarmacia_id2'));
                        }
                        if ($request->input('presentacion_id2') !== null && $request->input('presentacion_id2') !== '') {
                            $query->where('presentacion_id', '=', $request->input('presentacion_id2'));
                        }
                        if ($request->input('origen_id2') !== null && $request->input('origen_id2') !== '') {
                            $query->where('origen_id', '=', $request->input('origen_id2'));
                        }
                        if($request->input('modo')!=""){
                            if($request->input('modo')=="V"){
                                $query->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'<=',6)->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'>',3);
                            }
                            if($request->input('modo')=="A"){
                                $query->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'<=',3)->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'>',1);
                            }
                            if($request->input('modo')=="R"){
                                $query->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'<=',1);
                            }
                        }
                        if($request->input('fechainicio')!=''){
                            $query->where('lote.fechavencimiento','>=',$request->input('fechainicio'));
                        }
                        if($request->input('fechafinal')!=''){
                            $query->where('lote.fechavencimiento','<=',$request->input('fechafinal'));
                        }
                    })->where('lote.queda','>',0)->select('producto.nombre',DB::raw('origen.nombre as origen2'),DB::raw('presentacion.nombre as presentacion2'),DB::raw('lote.nombre as lote'),'lote.fechavencimiento','lote.queda',DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento) as meses'))->orderBy('producto.nombre', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => 'Producto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Presentacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Origen', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Lote', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Venc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cant.', 'numero' => '1');
        
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

    public function index()
    {
        $entidad          = 'Reportelote';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboOrigen = array('' => 'Todos') + Origen::lists('nombre', 'id')->all(); 
        $cboModo = array('' => 'Todos', 'V' => 'Verde', 'A' => 'Amarillo', 'R' => 'Rojo');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboOrigen', 'cboModo'));
    }

    public function show($id)
    {
        //
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Producto::join('lote','lote.producto_id','=','producto.id')
                        ->join('presentacion','presentacion.id','=','producto.presentacion_id')
                        ->join('origen','origen.id','=','producto.origen_id')
                        ->where('producto.nombre', 'LIKE', '%'.strtoupper($nombre).'%')->where(function ($query) use($request){
                        if ($request->input('tipo') !== null && $request->input('tipo') !== '') {
                            $query->where('tipo', '=', $request->input('tipo'));
                        }
                        if ($request->input('categoria_id2') !== null && $request->input('categoria_id2') !== '') {
                            $query->where('categoria_id', '=', $request->input('categoria_id2'));
                        }
                        if ($request->input('especialidadfarmacia_id2') !== null && $request->input('especialidadfarmacia_id2') !== '') {
                            $query->where('especialidadfarmacia_id', '=', $request->input('especialidadfarmacia_id2'));
                        }
                        if ($request->input('presentacion_id2') !== null && $request->input('presentacion_id2') !== '') {
                            $query->where('presentacion_id', '=', $request->input('presentacion_id2'));
                        }
                        if ($request->input('origen_id2') !== null && $request->input('origen_id2') !== '') {
                            $query->where('origen_id', '=', $request->input('origen_id2'));
                        }
                        if($request->input('modo')!=""){
                            if($request->input('modo')=="V"){
                                $query->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'<=',6)->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'>',3);
                            }
                            if($request->input('modo')=="A"){
                                $query->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'<=',3)->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'>',1);
                            }
                            if($request->input('modo')=="R"){
                                $query->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'<=',1);
                            }
                        }
                        if($request->input('fechainicio')!=''){
                            $query->where('lote.fechavencimiento','>=',$request->input('fechainicio'));
                        }
                        if($request->input('fechafinal')!=''){
                            $query->where('lote.fechavencimiento','<=',$request->input('fechafinal'));
                        }
                    })->where('lote.queda','>',0)->select('producto.nombre',DB::raw('origen.nombre as origen2'),DB::raw('presentacion.nombre as presentacion2'),DB::raw('lote.nombre as lote'),'lote.fechavencimiento','lote.queda',DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento) as meses'))->orderBy('producto.nombre', 'ASC');
        $lista            = $resultado->get();

        Excel::create('ExcelFechaVenc', function($excel) use($lista,$request) {
 
            $excel->sheet('FechaVenc', function($sheet) use($lista,$request) {
                $cabecera[] = "Producto";
                $cabecera[] = "Presentacion";
                $cabecera[] = "Origen";
                $cabecera[] = "Lote";
                $cabecera[] = "Fecha Venc.";
                $cabecera[] = "Cantidad";
                $sheet->row(1,$cabecera);
                $c=2;
                $color = ''; $verdes = 0; $amarillos = 0; $rojos = 0;
                foreach ($lista as $key => $value){
                    if($value->meses<=6 && $value->meses>3){
                        $verdes = $verdes + 1;
                        $color = '00FF00';
                    }elseif($value->meses<=3 && $value->meses>1){
                        $color = 'F4A900';
                        $amarillos = $amarillos + 1;
                    }elseif($value->meses<=1){
                        $color = 'FF0000';
                        $rojos = $rojos + 1;
                    }else{
                        $color = '000000';
                    }
                    $sheet->cells("A".$c.":F".$c, function($cells) use($color) {                        
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'color' => array('rgb' => $color),
                            'fill' =>  array('color'=> array('rgb' => $color)),
                            ));
                    });
                    $detalle = array();
                    $detalle[] = $value->nombre;
                    $detalle[] = $value->presentacion2;
                    $detalle[] = $value->origen2;
                    $detalle[] = $value->lote;
                    $detalle[] = date('d/m/Y',strtotime($value->fechavencimiento));
                    $detalle[] = $value->queda;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }

            });
        })->export('xls');
    }

    public function pdf(Request $request){
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Producto::join('lote','lote.producto_id','=','producto.id')
                        ->join('presentacion','presentacion.id','=','producto.presentacion_id')
                        ->join('origen','origen.id','=','producto.origen_id')
                        ->where('producto.nombre', 'LIKE', '%'.strtoupper($nombre).'%')->where(function ($query) use($request){
                        if ($request->input('tipo') !== null && $request->input('tipo') !== '') {
                            $query->where('tipo', '=', $request->input('tipo'));
                        }
                        if ($request->input('categoria_id2') !== null && $request->input('categoria_id2') !== '') {
                            $query->where('categoria_id', '=', $request->input('categoria_id2'));
                        }
                        if ($request->input('especialidadfarmacia_id2') !== null && $request->input('especialidadfarmacia_id2') !== '') {
                            $query->where('especialidadfarmacia_id', '=', $request->input('especialidadfarmacia_id2'));
                        }
                        if ($request->input('presentacion_id2') !== null && $request->input('presentacion_id2') !== '') {
                            $query->where('presentacion_id', '=', $request->input('presentacion_id2'));
                        }
                        if ($request->input('origen_id2') !== null && $request->input('origen_id2') !== '') {
                            $query->where('origen_id', '=', $request->input('origen_id2'));
                        }
                        if($request->input('modo')!=""){
                            if($request->input('modo')=="V"){
                                $query->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'<=',6)->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'>',3);
                            }
                            if($request->input('modo')=="A"){
                                $query->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'<=',3)->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'>',1);
                            }
                            if($request->input('modo')=="R"){
                                $query->where(DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento)'),'<=',1);
                            }
                        }
                        if($request->input('fechainicio')!=''){
                            $query->where('lote.fechavencimiento','>=',$request->input('fechainicio'));
                        }
                        if($request->input('fechafinal')!=''){
                            $query->where('lote.fechavencimiento','<=',$request->input('fechafinal'));
                        }
                    })->where('lote.queda','>',0)->select('producto.nombre',DB::raw('origen.nombre as origen2'),DB::raw('presentacion.nombre as presentacion2'),DB::raw('lote.nombre as lote'),'lote.fechavencimiento','lote.queda',DB::raw('TIMESTAMPDIFF(MONTH, curdate(), lote.fechavencimiento) as meses'))->orderBy('producto.nombre', 'ASC');
        $lista            = $resultado->get();
        $pdf = new TCPDF();
        $pdf::SetTitle('Reporte de Fechas Vencimiento');
        if (count($lista) > 0) {            
            $pdf::AddPage('P');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 0, 0, 70, 14);
            $pdf::Cell(0,10,utf8_decode("Reporte de Fechas de Vencimiento"),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(80,6,utf8_decode("PRODUCTO"),1,0,'C');
            $pdf::Cell(40,6,utf8_decode("PRESENTACION"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("ORIGEN"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("LOTE"),1,0,'C');
            $pdf::Cell(18,6,utf8_decode("FECHA VENC."),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("CANT."),1,0,'C');
            $pdf::Ln();
            $verdes = 0; $amarillos = 0; $rojos = 0;
            foreach ($lista as $key => $value){
                if($value->meses<=6 && $value->meses>3){
                    $verdes = $verdes + 1;
                    $pdf::SetTextColor(0,240,0);
                    $pdf::SetFont('helvetica','B',6.5);
                }elseif($value->meses<=3 && $value->meses>1){
                    $pdf::SetTextColor(244,169,0);
                    $pdf::SetFont('helvetica','B',6.5);
                    $amarillos = $amarillos + 1;
                }elseif($value->meses<=1){
                    $pdf::SetTextColor(240,0,0);
                    $pdf::SetFont('helvetica','B',6.5);
                    $rojos = $rojos + 1;
                }else{
                    $pdf::SetTextColor(0,0,0);
                    $pdf::SetFont('helvetica','',6.5);
                }
                $pdf::Cell(80,6,($value->nombre),1,0,'L');
                $pdf::Cell(40,6,($value->presentacion2),1,0,'C');
                $pdf::Cell(15,6,($value->origen2),1,0,'C');
                $pdf::Cell(15,6,($value->lote),1,0,'C');
                $pdf::Cell(18,6,date("d/m/Y",strtotime($value->fechavencimiento)),1,0,'C');
                $pdf::Cell(15,6,($value->queda),1,0,'C');
                $pdf::Ln();         
            }
            $pdf::SetFont('helvetica','B',8);
            $pdf::Ln();         
            $pdf::Cell(80,6,'',0,0,'C');
            $pdf::Cell(40,6,"RESUMEN",1,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(80,6,'',0,0,'C');
            $pdf::SetTextColor(0,240,0);
            $pdf::Cell(20,6,("VERDES :"),1,0,'R');
            $pdf::Cell(20,6,($verdes),1,0,'R');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::SetTextColor(244,169,0);
            $pdf::Cell(80,6,'',0,0,'C');
            $pdf::Cell(20,6,("AMARILLOS :"),1,0,'R');
            $pdf::Cell(20,6,($amarillos),1,0,'R');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::SetTextColor(240,0,0);
            $pdf::Cell(80,6,'',0,0,'C');
            $pdf::Cell(20,6,("ROJOS :"),1,0,'R');
            $pdf::Cell(20,6,($rojos),1,0,'R');
            $pdf::Ln();
            
        }
        $pdf::Output('ReporteFechaVenc.pdf');
    }

}
