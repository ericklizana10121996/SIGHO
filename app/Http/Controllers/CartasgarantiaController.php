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
use Word;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\Settings;
use Excel;

class CartasgarantiaController extends Controller
{
    protected $folderview      = 'app.cartasgarantia';
    protected $tituloAdmin     = 'Cartas de Envio';
    protected $tituloRegistrar = 'Lista de Cartas';
    protected $tituloModificar = 'Modificar Siniestro';
    protected $tituloEliminar  = 'Eliminar Factura';
    protected $rutas           = array('create' => 'cartasgarantia.create', 
            'edit'   => 'facturacion.edit', 
            'delete' => 'cartasgarantia.eliminar',
            'search' => 'cartasgarantia.buscar',
            'search2' => 'cartasgarantia.buscarcarta',
            'index'  => 'cartasgarantia.index',
            'word'  => 'cartasgarantia.word'
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'CartasGarantia';
        $plan             = Libreria::getParam($request->input('plan'),'');
        $fecha            = Libreria::getParam($request->input('fechainicial'));
        $fecha2           = Libreria::getParam($request->input('fechafinal'));
        $serie            = Libreria::getParam($request->input('serie'));
        $user = Auth::user();
        if($request->input('usuario')=="Todos"){
            $responsable_id=0;
        }else{
            $responsable_id=$user->person_id;
        }
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('cie','movimiento.cie_id','=','cie.id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->where('plan.razonsocial','like','%'.$plan.'%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->where('movimiento.manual','like','N');

        if($serie != ""){            
            $resultado = $resultado->where('movimiento.serie','=',$serie);
        }

        $resultado = $resultado->whereNotIn('movimiento.situacion',['U','A']);
        if($fecha!=""){
            $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fecha.'');
        }
        if($fecha2!=""){
            $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fecha2.'');
        }
        if($responsable_id>0){
            $resultado = $resultado->where('movimiento.responsable_id', '=', $responsable_id);   
        }
        $resultado        = $resultado->select('movimiento.*','cie.codigo as cie10',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable',DB::raw('plan.razonsocial as empresa'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Atencion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Empresa', 'numero' => '1');
        $cabecera[]       = array('valor' => 'CIE', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Marcar', 'numero' => '1');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $ruta             = $this->rutas;
        $totalfac = 0;

        foreach ($lista as $key => $value3) {
            $totalfac+=$value3->total;
        }

        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'totalfac', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'conf'));
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
        $entidad          = 'CartasGarantia';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboSerie         = array(""=>"Todos", "2" => "002", "8" => "008");
    
        $user = Auth::user();
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user','cboSerie'));
    }

    public function show($id)
    {
        //
    }

     public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'CartasGarantia2';
        $boton    = 'Registrar'; 
        $ruta     = $this->rutas;
        return view($this->folderview.'.mant')->with(compact('entidad', 'boton', 'listar','ruta'));
    }

    public function destroy($id)
    {
        $error = DB::transaction(function() use($id){
            $plan = explode("@", $id);
            $listventas = Movimiento::where('plan_id','=',$plan[0])
                            ->where('numerodias','=',$plan[1])
                            ->get();
            foreach ($listventas as $key => $value) {
                $value->tipoventa = 'A';
                $value->save();
            }
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($plan_id, $numero, $listarLuego)
    {
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = null;
        $entidad  = 'CartasGarantia2';
        $formData = array('route' => array('cartasgarantia.destroy', $plan_id.'@'.$numero), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function buscarcarta(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'CartasGarantia2';
        $plan           = Libreria::getParam($request->input('plan2'),'');
        $resultado        = Movimiento::leftjoin('person as responsable','responsable.id','=','movimiento.usuariocarta_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->where('plan.razonsocial','like','%'.$plan.'%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->where('movimiento.manual','like','N')
                            ->whereNotNull('movimiento.numerodias')
                            ->whereNotIn('movimiento.situacion',['U','A'])
                            ->groupBy('movimiento.plan_id')
                            ->groupBy('plan.razonsocial')
                            ->groupBy('movimiento.fechacarta')
                            ->groupBy('movimiento.tipoventa')
                            ->groupBy('movimiento.numerodias')
                            ->groupBy('responsable.nombres');
        $resultado        = $resultado->select(DB::raw('plan.razonsocial as empresa'),DB::raw('sum(movimiento.total) as total'),DB::raw('count(*) as documentos'),'movimiento.numerodias','movimiento.plan_id','movimiento.fechacarta','movimiento.tipoventa','responsable.nombres')->orderBy('plan.razonsocial', 'asc')->orderBy('movimiento.numerodias','desc');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Empresa', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Documentos', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        
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
            return view($this->folderview.'.listCarta')->with(compact('lista', 'totalfac', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta'));
        }
        return view($this->folderview.'.listCarta')->with(compact('lista', 'entidad','conf'));
    }

    public function word(Request $request){
        date_default_timezone_set('America/Lima');
        setlocale(LC_TIME, 'es_ES'); 
        $plan           = Libreria::getParam($request->input('plan'),'');
        $fecha            = Libreria::getParam($request->input('fechainicial'));
        $fecha2            = Libreria::getParam($request->input('fechafinal'));
        $id = explode(",",$request->input('id'));
        $user = Auth::user();
        $resultado  = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                        ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                        ->leftjoin('cie','movimiento.cie_id','=','cie.id')
                        ->join('plan','plan.id','=','movimiento.plan_id')
                        ->where('plan.razonsocial','like','%'.$plan.'%')
                        ->where('movimiento.manual','like','N')
                        ->where('movimiento.tipomovimiento_id','=',9)
                        ->whereIn('movimiento.id',$id);
        if($fecha!=""){
            $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fecha.'');
        }
        if($fecha2!=""){
            $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fecha2.'');
        }
        $resultado        = $resultado->select('movimiento.*','cie.codigo as cie10',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable','plan.id as id_plan','plan.razonsocial as empresa','plan.direccion as direccion2')->orderBy('plan.razonsocial','ASC')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');

        $lista            = $resultado->get();
        //dd($lista);
        $plan_id=0;$total=0;
        $phpWord = new Word();
        $fancyTableStyleName = 'Fancy Table';
        $fancyTableStyle = array('borderSize' => 6, 'borderColor' => '006699', 'cellMargin' => 20, 'alignment' => 'center', 'cellSpacing' => 10);
        $fancyTableFirstRowStyle = array('borderBottomSize' => 18, 'borderBottomColor' => '0000FF');
        $fancyTableCellStyle = array('valign' => 'center', 'alignment' => 'center');
        $fancyTableCellBtlrStyle = array('valign' => 'center', 'alignment' => 'center');
        $fancyTableFontStyle = array('bold' => true);
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
        //$section = $phpWord->addSection();
        
        foreach ($lista as $key => $value) {
            if($plan_id!=$value->plan_id){
                if($plan_id!=0){
                    $table->addRow(200);
                    $table->addCell(7000, $fancyTableCellStyle)->addText('');
                    $table->addCell(1000, $fancyTableCellStyle)->addText('TOTAL');
                    $table->addCell(1000, $fancyTableCellStyle)->addText(number_format($total,2,'.',''),null,array('alignment' => 'right'));
                    $total=0;
                }
                // Adding an empty Section to the document...
                $section = $phpWord->addSection();
                $plan_id = $value->plan_id;

                $section->addTextBreak(1);
                // Adding Text element to the Section having font styled by default...
                // Universalización 
                $section->addText('"AÑO DE LA UNIVERSALIZACIÓN DE LA SALUD"',
                    array('name' => 'Calibri', 'size' => 12, 'bold' => true),
                    array('alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0));
                $section->addTextBreak('',
                    array('name' => 'Calibri', 'size' => 10),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                if(date("m")=="01") $mes="ENERO";
                if(date("m")=="02") $mes="FEBRERO";
                if(date("m")=="03") $mes="MARZO";
                if(date("m")=="04") $mes="ABRIL";
                if(date("m")=="05") $mes="MAYO";
                if(date("m")=="06") $mes="JUNIO";
                if(date("m")=="07") $mes="JULIO";
                if(date("m")=="08") $mes="AGOSTO";
                if(date("m")=="09") $mes="SETIEMBRE";
                if(date("m")=="10") $mes="OCTUBRE";
                if(date("m")=="11") $mes="NOVIEMBRE";
                if(date("m")=="12") $mes="DICIEMBRE";
                $section->addText('CHICLAYO, '.date('d').' DE '.$mes.' DEL '.date('Y'),
                    array('name' => 'Calibri', 'size' => 10),
                    array('alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0));
                //$section->addTextBreak(1);
                $section->addTextBreak('',
                    array('name' => 'Calibri', 'size' => 10),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );

                $numero=Movimiento::where('tipomovimiento_id','=',9)
                            ->where('plan_id','=',$value->plan_id)
                            ->select(DB::raw('max(case when numerodias is null then 0 else numerodias end) as maximo'))
                            ->first()->maximo + 1;
                $section->addText('Nº '.str_pad($numero,3,'0',STR_PAD_LEFT).'/HJPII/'.date('Y'),
                    array('name' => 'Calibri', 'size' => 11, 'bold' => true),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $section->addText('SEÑORES:',
                    array('name' => 'Calibri', 'size' => 11),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $section->addText($value->empresa,
                    array('name' => 'Calibri', 'size' => 11, 'bold' => true),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                if ($value->id_plan==2) {
                //if ($value->empresa == "BNP PARIBAS CARDIF S.A.") {
                    $section->addText("AV. CANABAL Y MOREYRA 380 PISO 11 S.I. LIMA",
                        array('name' => 'Calibri', 'size' => 11, 'bold' => true),
                        array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                    );
                } else {
                    $section->addText(utf8_decode($value->direccion2),
                        array('name' => 'Calibri', 'size' => 11, 'bold' => true),
                        array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                    );
                }
                $section->addText('Asunto:              ENVIO DE FACTURAS MEDICAS',
                    array('name' => 'Calibri', 'size' => 11, 'bold' => true),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $section->addText('De nuestra Consideración: ',
                    array('name' => 'Calibri', 'size' => 11),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                //echo "CONTROL 2";
                //exit();
                $section->addText('Expresando nuestro saludo cordial la presente tiene como finalidad remitirles la documentación y facturas de las atenciones médicas de los siguientes pacientes:',
                    array('name' => 'Calibri', 'size' => 11),
                    array('alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                //echo "CONTROL 2";
                //exit();
                $section->addTextBreak('',
                    array('name' => 'Calibri', 'size' => 10),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $fancyTableStyleName = 'Fancy Table';
                $fancyTableStyle = array('borderSize' => 6, 'borderColor' => '006699', 'cellMargin' => 20, 'alignment' => 'center', 'cellSpacing' => 10);
                $fancyTableFirstRowStyle = array('borderBottomSize' => 18, 'borderBottomColor' => '0000FF');
                $fancyTableCellStyle = array('valign' => 'center', 'alignment' => 'center');
                $fancyTableCellBtlrStyle = array('valign' => 'center', 'alignment' => 'center');
                $fancyTableFontStyle = array('bold' => true);
                $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                $table = $section->addTable($fancyTableStyleName);
                $table->addRow(200);
                $table->addCell(6000, $fancyTableCellStyle)->addText('APELLIDOS Y NOMBRES', $fancyTableFontStyle);
                $table->addCell(2000, $fancyTableCellStyle)->addText('FACTURA', $fancyTableFontStyle);
                $table->addCell(1000, $fancyTableCellStyle)->addText('TOTAL', $fancyTableFontStyle, array('alignment' => 'center'));
                $value->numerodias=$numero;
                $value->fechacarta=date("Y-m-d");
                $value->tipoventa='N';
                $value->usuariocarta_id=$user->person_id;
            }else{
                $value->numerodias=$numero;
                $value->fechacarta=date("Y-m-d");
                $value->tipoventa='N';
                $value->usuariocarta_id=$user->person_id;
            }
            $table->addRow(200);
            $table->addCell(6000, $fancyTableCellStyle)->addText($value->paciente);
            $table->addCell(2000, $fancyTableCellStyle)->addText('F'.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT));
            $table->addCell(1000, $fancyTableCellStyle)->addText(number_format($value->total,2,'.',''),null,array('alignment' => 'right'));
            $total=$total + number_format($value->total,2,'.','');
            $value->save();
        }
        //$table = $section->addTable($fancyTableStyleName);
        if (!isset($table)){
            $section = $phpWord->addSection();
            $table = $section->addTable($fancyTableStyleName);
        }
        $table->addRow(200);
        $table->addCell(7000, $fancyTableCellStyle)->addText('');
        $table->addCell(1000, $fancyTableCellStyle)->addText('TOTAL');
        $table->addCell(1000, $fancyTableCellStyle)->addText(number_format($total,2,'.',''),null,array('alignment' => 'right'));
        
        $section->addTextBreak('',
            array('name' => 'Calibri', 'size' => 10),
            array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
        );
        $section->addImage('dist/img/firma_convenios.jpg',array('width' => 210, 'height' => 150));

        // Saving the document as OOXML file...
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $file = 'CartaEnvio.docx';
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        $objWriter->save("php://output");
    }

    public function wordPlan(Request $request){
        date_default_timezone_set('America/Lima');
        setlocale(LC_TIME, 'es_ES'); 
        $plan_id           = Libreria::getParam($request->input('plan_id'),'');
        $numero        = Libreria::getParam($request->input('numero'));
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('cie','movimiento.cie_id','=','cie.id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->where('plan.id','=',$plan_id)
                            ->where('movimiento.manual','like','N')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->where('movimiento.numerodias','=',$numero);
        $resultado        = $resultado->select('movimiento.*','cie.codigo as cie10',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable',DB::raw('plan.razonsocial as empresa'),DB::raw('plan.direccion as direccion2'))->orderBy('plan.razonsocial','ASC')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');

        $lista            = $resultado->get();
        // dd($lista);
        $plan_id=0;$total=0;
        $j=1;
        $phpWord = new Word();
        foreach ($lista as $key => $value) {
            if($plan_id!=$value->plan_id){
                if($plan_id!=0){
                    $table->addRow(200);
                    $table->addCell(7000, $fancyTableCellStyle)->addText('');
                    $table->addCell(1000, $fancyTableCellStyle)->addText('TOTAL');
                    $table->addCell(1000, $fancyTableCellStyle)->addText(number_format($total,2,'.',''),null,array('alignment' => 'right'));
                    $total=0;
                }
                // Adding an empty Section to the document...
                $section = $phpWord->addSection();
                $plan_id = $value->plan_id;

                $section->addTextBreak(1);
                // Adding Text element to the Section having font styled by default...
                if (date('Y',strtotime($value->fechacarta)) == '2019') {
                    $titulo_anio = '"AÑO DEL DIALOGO Y RECONCILIACION NACIONAL"';
                }else{
                    $titulo_anio = '"AÑO DE LA UNIVERSALIZACIÓN DE LA SALUD"';
                }
                $section->addText($titulo_anio,
                    array('name' => 'Calibri', 'size' => 12, 'bold' => true),
                    array('alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0));
                $section->addTextBreak('',
                    array('name' => 'Calibri', 'size' => 10),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                if($value->fechacarta!="")
                    $fecha=strtotime($value->fechacarta);
                else
                    $fecha=strtotime('now');

                // dd($value, date('d/m/Y',$fecha));

                if(date("m",$fecha)=="01") $mes="ENERO";
                if(date("m",$fecha)=="02") $mes="FEBRERO";
                if(date("m",$fecha)=="03") $mes="MARZO";
                if(date("m",$fecha)=="04") $mes="ABRIL";
                if(date("m",$fecha)=="05") $mes="MAYO";
                if(date("m",$fecha)=="06") $mes="JUNIO";
                if(date("m",$fecha)=="07") $mes="JULIO";
                if(date("m",$fecha)=="08") $mes="AGOSTO";
                if(date("m",$fecha)=="09") $mes="SETIEMBRE";
                if(date("m",$fecha)=="10") $mes="OCTUBRE";
                if(date("m",$fecha)=="11") $mes="NOVIEMBRE";
                if(date("m",$fecha)=="12") $mes="DICIEMBRE";
                $section->addText('CHICLAYO, '.date('d',$fecha).' DE '.$mes.' DEL '.date('Y',$fecha),
                    array('name' => 'Calibri', 'size' => 10),
                    array('alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0));
                //$section->addTextBreak(1);
                $section->addTextBreak('',
                    array('name' => 'Calibri', 'size' => 10),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $section->addText('Nº '.str_pad($numero,3,'0',STR_PAD_LEFT).'/HJPII/'.date('Y'),
                    array('name' => 'Calibri', 'size' => 11, 'bold' => true),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $section->addText('SEÑORES:',
                    array('name' => 'Calibri', 'size' => 11),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $section->addText($value->empresa,
                    array('name' => 'Calibri', 'size' => 11, 'bold' => true),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $section->addText($value->direccion2,
                    array('name' => 'Calibri', 'size' => 11, 'bold' => true),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $section->addText('Asunto:              ENVIO DE FACTURAS MEDICAS',
                    array('name' => 'Calibri', 'size' => 11, 'bold' => true),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $section->addText('De nuestra Consideración: ',
                    array('name' => 'Calibri', 'size' => 11),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $section->addText('Expresando nuestro saludo cordial la presente tiene como finalidad remitirles la documentación y facturas de las atenciones médicas de los siguientes pacientes:',
                    array('name' => 'Calibri', 'size' => 11),
                    array('alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $section->addTextBreak('',
                    array('name' => 'Calibri', 'size' => 10),
                    array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
                );
                $fancyTableStyleName = 'Fancy Table';
                $fancyTableStyle = array('borderSize' => 6, 'borderColor' => '006699', 'cellMargin' => 8, 'alignment' => 'center', 'cellSpacing' => 10);
                $fancyTableFirstRowStyle = array('borderBottomSize' => 18, 'borderBottomColor' => '0000FF');
                $fancyTableCellStyle = array('valign' => 'center', 'alignment' => 'center');
                $fancyTableCellBtlrStyle = array('valign' => 'center', 'alignment' => 'center');
                $fancyTableFontStyle = array('bold' => true);
                $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                $table = $section->addTable($fancyTableStyleName);
                $table->addRow(200);
                $table->addCell(1000, $fancyTableCellStyle)->addText('NRO', $fancyTableFontStyle,array('alignment' => 'center'));
                $table->addCell(7000, $fancyTableCellStyle)->addText('APELLIDOS Y NOMBRES', $fancyTableFontStyle,array('alignment' => 'center'));
                $table->addCell(2000, $fancyTableCellStyle)->addText('FACTURA', $fancyTableFontStyle,array('alignment' => 'center'));
                $table->addCell(1000, $fancyTableCellStyle)->addText('TOTAL', $fancyTableFontStyle, array('alignment' => 'center'));
                //$value->numerodias=$numero;
            }else{
                //$value->numerodias=$numero;
            }
            $table->addRow(200);
            $table->addCell(1000, $fancyTableCellStyle)->addText($j,null, array('alignment' => 'center'));   
            $table->addCell(7000, $fancyTableCellStyle)->addText($value->paciente);
            $table->addCell(2000, $fancyTableCellStyle)->addText('F'.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT),null,array('alignment' => 'center'));
            $table->addCell(1000, $fancyTableCellStyle)->addText(number_format($value->total,2,'.',''),null,array('alignment' => 'center'));
            $total=$total + number_format($value->total,2,'.','');
            $j++;
        }
        $table->addRow(200);
        $table->addCell(1000, $fancyTableCellStyle)->addText('');
        $table->addCell(7000, $fancyTableCellStyle)->addText('');
        $table->addCell(1000, $fancyTableCellStyle)->addText('TOTAL');
        $table->addCell(1000, $fancyTableCellStyle)->addText(number_format($total,2,'.',''),null,array('alignment' => 'right'));
        
        $section->addTextBreak('',
            array('name' => 'Calibri', 'size' => 10),
            array('spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0)
        );
        $section->addImage('dist/img/firma_convenios.jpg',array('width' => 210, 'height' => 150));

        // Saving the document as OOXML file...
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $file = 'CartaEnvio.docx';
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        $objWriter->save("php://output");
    }
}
