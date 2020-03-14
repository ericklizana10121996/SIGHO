<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Movimiento;
use App\Analisis;
use App\Detalleanalisis;
use App\Person;
use App\Tarifario;
use App\Examen;
use App\Tipoexamen;
use App\Plan;
use App\Detalleexamen;
use App\Detallemovcaja;
use App\Librerias\Libreria;
use App\Librerias\EnLetras;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;
USE Excel;

class AnalisisController extends Controller
{
    protected $folderview      = 'app.analisis';
    protected $tituloAdmin     = 'Analisis';
    protected $tituloRegistrar = 'Registrar Analisis';
    protected $tituloModificar = 'Modificar Analisis';
    protected $tituloEliminar  = 'Eliminar Analisis';
    protected $rutas           = array('create' => 'analisis.create', 
            'edit'   => 'analisis.edit', 
            'delete' => 'analisis.eliminar',
            'search' => 'analisis.buscar',
            'index'  => 'analisis.index',
            'pdfListar'  => 'analisis.pdfListar',
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
        $entidad          = 'Analisis';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fecha            = Libreria::getParam($request->input('fechainicial'));
        $fecha2           = Libreria::getParam($request->input('fechafinal'));
        $user = Auth::user();
        $resultado        = Analisis::leftjoin('historia', 'historia.id', '=', 'analisis.historia_id')
                            ->leftjoin('person as paciente', 'paciente.id', '=', 'historia.person_id')
                            ->leftjoin('person as responsable','responsable.id','=','analisis.usuario_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where('historia.numero','LIKE','%'.$numero.'%');
        if($fecha!=""){
            $resultado = $resultado->where('analisis.fecha', '>=', ''.$fecha.'');
        }
        if($fecha2!=""){
            $resultado = $resultado->where('analisis.fecha', '<=', ''.$fecha2.'');
        }
        $resultado        = $resultado->select('analisis.*',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable')->orderBy('analisis.fecha', 'ASC')->orderBy('analisis.hora','ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Hora', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Historia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'conf', 'user'));
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
        $entidad          = 'Analisis';
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
        $entidad             = 'Analisis';
        $analisis = null;
        $formData            = array('analisis.store');
        $user = Auth::user();
        $cboTipoPaciente     = array("Convenio" => "Convenio", "Particular" => "Particular", "Hospital" => "Hospital");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('analisis', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente'));
    }

    public function store(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                'fecha'                  => 'required',
                'historia_id'         => 'required',
                );
        $mensajes = array(
            'fecha.required'         => 'Debe seleccionar una fecha',
            'historia_id.required'         => 'Debe seleccionar un paciente',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       
        
        $user = Auth::user();
        $dat=array();
        $error = DB::transaction(function() use($request,$user,&$dat){
            $analisis = new Analisis();
            $analisis->fecha = $request->input('fecha');
            $analisis->hora = date('H:i:s');
            $analisis->historia_id = $request->input('historia_id');
            $analisis->edad = $request->input('edad');
            $analisis->direccion = $request->input('direccion');
            $analisis->usuario_id = $user->person_id;
            $analisis->save();


            if($request->input('lista') != ""){
                $arr=explode(",",$request->input('lista'));
                for($c=0;$c<count($arr);$c++){
                    $detalle = new Detalleanalisis();
                    $detalle->detalleexamen_id=$arr[$c];
                    $detalle->analisis_id=$analisis->id;
                    $detalle->referencia=$request->input('txtReferencia'.$arr[$c]);
                    $detalle->unidad=$request->input('txtUnidad'.$arr[$c]);
                    $detalle->descripcion=$request->input('txtDescripcion'.$arr[$c]);
                    $detalle->resultado=$request->input('txtResultado'.$arr[$c]);
                    $detalle->save();
                }

            }
            
            if($request->input('listaPago')!=""){
                $arr=explode(",",$request->input('listaPago'));
                for($c=0;$c<count($arr);$c++){
                    $detalle = Detallemovcaja::find($arr[$c]);
                    $detalle->analisis_id = $analisis->id;
                    $detalle->situacionanalisis = 'E';
                    $detalle->save();
                }
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
        $existe = Libreria::verificarExistencia($id, 'analisis');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $analisis = Analisis::find($id);
        $entidad             = 'Analisis';
        $user = Auth::user();
        $cboTipoPaciente     = array("Convenio" => "Convenio", "Particular" => "Particular", "Hospital" => "Hospital");
        $formData            = array('analisis.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('analisis', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente'));
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
        $existe = Libreria::verificarExistencia($id, 'analisis');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $dat=array();

        $error = DB::transaction(function() use($request, $id, $user, &$dat){
            $analisis        = Analisis::find($id);
            $analisis->fecha = $request->input('fecha');
            $analisis->hora = date('H:i:s');
            $analisis->historia_id = $request->input('historia_id');
            $analisis->edad = $request->input('edad');
            $analisis->direccion = $request->input('direccion');
            $analisis->usuario_id = $user->person_id;
            $analisis->save();

            $resultado = Detalleanalisis::where('analisis_id','=',$id)->orderBy('id','asc')->get();
            foreach ($resultado as $key => $value) {
                $value->delete();
            }

            $arr=explode(",",$request->input('lista'));
            for($c=0;$c<count($arr);$c++){
                $detalle = new Detalleanalisis();
                $detalle->detalleexamen_id=$arr[$c];
                $detalle->analisis_id=$analisis->id;
                $detalle->referencia=$request->input('txtReferencia'.$arr[$c]);
                $detalle->unidad=$request->input('txtUnidad'.$arr[$c]);
                $detalle->descripcion=$request->input('txtDescripcion'.$arr[$c]);
                $detalle->resultado=$request->input('txtResultado'.$arr[$c]);
                $detalle->save();
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
        $existe = Libreria::verificarExistencia($id, 'analisis');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $analisis = Analisis::find($id);
            $analisis->delete();

            $lista = Detallemovcaja::where('analisis_id','=',$id)->get();
            foreach ($lista as $key => $value) {
                $value->analisis_id = null;
                $value->situacionanalisis = null;
                $value->save();
            }
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'analisis');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Movimiento::find($id);
        $entidad  = 'Analisis';
        $formData = array('route' => array('analisis.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar', 'id'));
    }
    
    public function buscarexamen(Request $request)
    {
        $descripcion = $request->input("examen");
        $resultado = Examen::where('nombre','LIKE','%'.$descripcion.'%');
        $resultado    = $resultado->get();
        // dd($resultado);
        if(count($resultado)>0){
            $c=0;
            foreach ($resultado as $key => $value){
                $data[$c] = array(
                            'id' => $value->id,
                            'examen' => $value->nombre,
                            'tipoexamen' => $value->tipoexamen->nombre,
                        );
                        $c++;                
            }            
        }else{
            $data = array();
        }
        return json_encode($data);
    }

    public function seleccionarexamen(Request $request)
    {
        $examen = Examen::find($request->input('id'));
        $resultado = Detalleexamen::where('examen_id','=',$request->input('id'))->orderBy('id','asc')->get();
        $dato = array();$c=0;
        foreach ($resultado as $key => $value) {
            $data[$c] = array(
                'id' => $value->id,
                'examen_id' => $value->examen_id,
                'referencia' => $value->referencia,
                'descripcion' => $value->descripcion,
                'unidad' => $value->unidad,
                'tipoexamen' => $examen->tipoexamen->nombre,
                'examen' => $examen->nombre,
            );      
            $c=$c+1;      
        }
        return json_encode($data);
    }

    public function agregarDetalle(Request $request)
    {
        $resultado = Detalleanalisis::where('detalleanalisis.analisis_id','=',$request->input('id'))
                        ->join('detalleexamen as de','de.id','=','detalleanalisis.detalleexamen_id')
                        ->join('examen as ex','ex.id','=','de.examen_id')
                        ->join('tipoexamen as te','te.id','=','ex.tipoexamen_id')
                        ->orderBy('detalleanalisis.id','asc')
                        ->select('detalleanalisis.*','de.examen_id','ex.nombre as examen2','te.nombre as tipoexamen2')
                        ->get();
        $dato = array();$c=0;
        foreach ($resultado as $key => $value) {
            $data[$c] = array(
                'id' => $value->detalleexamen_id,
                'examen_id' => $value->examen_id,
                'referencia' => $value->referencia,
                'descripcion' => $value->descripcion,
                'unidad' => $value->unidad,
                'resultado' => $value->resultado,
                'tipoexamen' => $value->tipoexamen2,
                'examen' => $value->examen2,
            );      
            $c=$c+1;      
        }
        return json_encode($data);
    }

    public function buscarpagos(Request $request)
    {
        $persona_id = $request->input("persona_id");
        $resultado = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                        ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                        ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                        ->where('movimiento.tipodocumento_id','=',1)
                        ->where(function($sql){
                            $sql->whereNull('dmc.situacionanalisis')
                                ->orWhere('dmc.situacionanalisis','<>','E');
                        })
                        ->whereNull('dmc.deleted_at')
                        ->where(DB::raw('case when dmc.servicio_id>0 then s.tiposervicio_id else dmc.tiposervicio_id end'),'=','2')
                        ->whereNotIn('movimiento.situacion',['A','U'])
                        ->where('movimiento.persona_id','=',$persona_id);
        $resultado    = $resultado->select('dmc.id','movimiento.fecha','movimiento.numero','movimiento.total','m2.tipodocumento_id','m2.serie as serie2','m2.numero as numero2','movimiento.numero','dmc.servicio_id','dmc.descripcion','s.nombre as servicio2','s.tarifario_id','dmc.cantidad','dmc.precio')->orderBy('movimiento.fecha','desc')->get();
        if(count($resultado)>0){
            $c=0;
            foreach ($resultado as $key => $value){
                if($value->servicio_id>0){
                    $descripcion = $value->servicio2;
                }else{
                    if($value->tarifario_id>0){
                        $tarifario=Tarifario::find($value->tarifario_id);
                        $descripcion = $tarifario->codigo.' - '.$tarifario->nombre;
                    }else{
                        $descripcion = $value->descripcion;
                    }
                }
                if($value->total==0){
                    $numero="PREF. ".$value->numero;
                }else{
                    $numero=($value->tipodocumento_id==5?'B':'F').$value->serie2.'-'.$value->numero2;
                }
                $data[$c] = array(
                            'id' => $value->id,
                            'fecha' => date('d/m/Y',strtotime($value->fecha)),
                            'descripcion' => $descripcion,
                            'numero' => $numero,
                            'cantidad' => number_format($value->cantidad,0,'.',''),
                            'precio' => $value->precio,
                        );
                        $c++;                
            }            
        }else{
            $data = array();
        }
        return json_encode($data);
    }
    
   	public function pdfAnalisis(Request $request){
        $entidad          = 'Facturacion';
        $id               = Libreria::getParam($request->input('id'),'');
        $resultado        = Analisis::join('historia','historia.id','=','analisis.historia_id')
                            ->join('person as paciente', 'paciente.id', '=', 'historia.person_id')
                            ->leftjoin('person as responsable','responsable.id','=','analisis.usuario_id')
                            ->where('analisis.id', '=', $id);
        $resultado        = $resultado->select('analisis.*',DB::raw('concat(responsable.apellidopaterno,\' \',responsable.apellidomaterno,\' \',responsable.nombres) as responsable2'));
        $lista            = $resultado->get();
        if (count($lista) > 0) {     
            foreach($lista as $key => $value){
                $pdf = new TCPDF();
                $pdf::SetTitle('Analisis');
                $pdf::AddPage();
                $pdf::SetFont('helvetica','B',12);
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Ln();
                $pdf::Ln();
                $pdf::Ln();
                $pdf::Ln();
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(20,6,utf8_encode("Paciente: "),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(120,6,(trim($value->historia->persona->apellidopaterno." ".$value->historia->persona->apellidomaterno." ".$value->historia->persona->nombres)),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(30,6,utf8_encode("Tipo Paciente: "),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,(trim($value->historia->tipopaciente)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(20,6,("Dirección: "),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(120,6,(trim($value->direccion)),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(30,6,utf8_encode("Hora:"),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(37,6,utf8_encode($value->hora),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(20,6,utf8_encode("Historia: "),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,(trim($value->historia->numero)),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(10,6,utf8_encode("Edad: "),0,0,'L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(80,6,(trim($value->edad)),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(30,6,("Fecha de impresión: "),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(37,6,date('d/m/Y'),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(140,6,'',0,0,'L');
                $pdf::Cell(30,6,("Fecha de Análisis: "),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(37,6,date('d/m/Y',strtotime($value->fecha)),0,0,'L');
                $pdf::Ln();
                
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(10,6,(""),0,0,'C');
                $pdf::setFillColor(180,180,180); 
                $pdf::Cell(70,6,utf8_encode("EXAMEN"),0,0,'L',1);
                $pdf::Cell(37,6,utf8_encode("RESULTADO"),0,0,'L',1);
                $pdf::Cell(50,6,utf8_encode("REFERENCIA"),0,0,'L',1);
                $pdf::Cell(20,6,utf8_encode("UNIDADES"),0,0,'L',1);
                $pdf::Ln();
                $resultado        = Detalleanalisis::leftjoin('detalleexamen as de', 'de.id', '=', 'detalleanalisis.detalleexamen_id')
                            ->join('examen','examen.id','=','de.examen_id')
                            ->join('tipoexamen','tipoexamen.id','=','examen.tipoexamen_id')
                            ->where('detalleanalisis.analisis_id', '=', $id)
                            ->select('detalleanalisis.*','examen.nombre as examen2','tipoexamen.nombre as tipoexamen2')
                            ->orderBy('detalleanalisis.id','asc');
                $lista2            = $resultado->get();
                $c=0;$examen="";$tipoexamen="";
                foreach($lista2 as $key2 => $v){$c=$c+1;
                    if($pdf::GetY()>220){
                        $pdf::SetFont('helvetica','B',9);
                        $fechaAnalisis = new \DateTime($value->fecha);
                        $hoy = new \DateTime("2019-02-02");
                        if($fechaAnalisis >= $hoy){
                            $pdf::Image("http://localhost/juanpablo/dist/img/firma-analisis2.png", 61, 225, 20,35);
                            $pdf::SetY('260');
                            $pdf::Cell(30,4,"",0,0,'C');
                            $pdf::Cell(60,4,"DR. JORGE LUIS GARCIA CARASSAS",0,0,'C');
                            $pdf::Cell(40,4,"",0,0,'C');
                            $pdf::Cell(60,4,"PROCESADO POR:",0,0,'C');
                            $pdf::Ln();                    
                            $pdf::Cell(30,4,"",0,0,'C');
                            $pdf::Cell(60,4,"PATOLOGIA CLINICA",0,0,'C');
                            $pdf::Cell(40,4,"",0,0,'C');
                            $pdf::Cell(60,4,$value->responsable2,0,0,'C');
                            $pdf::Ln();                    
                            $pdf::Cell(30,4,"",0,0,'C');
                            $pdf::Cell(60,4,"CMP 036387",0,0,'C');
                            $pdf::Ln();
                            $pdf::Cell(30,4,"",0,0,'C');
                            $pdf::Cell(60,4,"RNE 024417",0,0,'C');
                            $pdf::Ln();
                        }else{
                            $pdf::Image("http://localhost/juanpablo/dist/img/firma-analisis.jpg", 50, 230, 40, 20);
                            $pdf::SetY('255');
                            $pdf::Cell(30,4,"",0,0,'C');
                            $pdf::Cell(60,4,"ANGELA YOVERA PUICAN ",0,0,'C');
                            $pdf::Cell(40,4,"",0,0,'C');
                            $pdf::Cell(60,4,"PROCESADO POR:",0,0,'C');
                            $pdf::Ln();                    
                            $pdf::Cell(30,4,"",0,0,'C');
                            $pdf::Cell(60,4,"PATOLOGIA CLINICA",0,0,'C');
                            $pdf::Cell(40,4,"",0,0,'C');
                            $pdf::Cell(60,4,$value->responsable2,0,0,'C');
                            $pdf::Ln();                    
                            $pdf::Cell(30,4,"",0,0,'C');
                            $pdf::Cell(60,4,"CMP 54524",0,0,'C');
                            $pdf::Ln();
                        }
                        $pdf::AddPage();
                        $pdf::SetFont('helvetica','B',12);
                        $pdf::Cell(130,7,"",0,0,'C');
                        $pdf::Ln();
                        $pdf::Ln();
                        $pdf::Ln();
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','',9);
                        $pdf::Cell(20,6,utf8_encode("Paciente: "),0,0,'L');
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(120,6,(trim($value->historia->persona->apellidopaterno." ".$value->historia->persona->apellidomaterno." ".$value->historia->persona->nombres)),0,0,'L');
                        $pdf::SetFont('helvetica','',9);
                        $pdf::Cell(30,6,utf8_encode("Tipo Paciente: "),0,0,'L');
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(37,6,(trim($value->historia->tipopaciente)),0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','',9);
                        $pdf::Cell(20,6,("Dirección: "),0,0,'L');
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(120,6,(trim($value->direccion)),0,0,'L');
                        $pdf::SetFont('helvetica','',9);
                        $pdf::Cell(30,6,utf8_encode("Hora:"),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(37,6,utf8_encode($value->hora),0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','',9);
                        $pdf::Cell(20,6,utf8_encode("Historia: "),0,0,'L');
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(30,6,(trim($value->historia->numero)),0,0,'L');
                        $pdf::SetFont('helvetica','',9);
                        $pdf::Cell(10,6,utf8_encode("Edad: "),0,0,'L');
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(80,6,(trim($value->edad)),0,0,'L');
                        $pdf::SetFont('helvetica','',9);
                        $pdf::Cell(30,6,("Fecha de impresión: "),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(37,6,date('d/m/Y'),0,0,'L');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','',9);
                        $pdf::Cell(140,6,'',0,0,'L');
                        $pdf::Cell(30,6,("Fecha de Análisis: "),0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(37,6,date('d/m/Y',strtotime($value->fecha)),0,0,'L');
                        $pdf::Ln();
                        
                        $pdf::SetFont('helvetica','B',9);
                        $pdf::Cell(10,6,(""),0,0,'C');
                        $pdf::Cell(70,6,utf8_encode("EXAMEN"),0,0,'L',1);
                        $pdf::Cell(37,6,utf8_encode("RESULTADO"),0,0,'L',1);
                        $pdf::Cell(50,6,utf8_encode("REFERENCIA"),0,0,'L',1);
                        $pdf::Cell(20,6,utf8_encode("UNIDADES"),0,0,'L',1);
                        $pdf::Ln();
                        
                    }
                    if($tipoexamen!=$v->tipoexamen2){
                        $pdf::Cell(10,4,'',0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(177,4,$v->tipoexamen2,1,0,'L');    
                        $pdf::Ln();
                        $tipoexamen=$v->tipoexamen2;
                    }
                    
                    if($examen!=$v->examen2){
                        $pdf::Ln();
                        $pdf::Cell(10,4,'',0,0,'L');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(177,4,$v->examen2,0,0,'L');    
                        $pdf::Ln();
                        $examen=$v->examen2;
                    }
                    //$h=NbLines(25,$v->referencia);
                    $lines = explode("\n",trim($v->referencia));
                    $h=4*count($lines);
                    $h1 = $pdf::getNumLines($v->resultado,27)*4;
                    if($h<$h1){
                        $h=$h1;
                    }
                      
                    $pdf::SetFont('helvetica','',7.5);
                    $pdf::Cell(10,$h,'',0,0,'L');
                    $nombre=trim($v->descripcion);//$pdf::getNumLines($nombre,70)
                    if($pdf::getNumLines($nombre,70)==1){
                        $pdf::Cell(70,4,($nombre),0,0,'L');
                    }else{
                        if(strlen($nombre)<60){
                            $pdf::Cell(70,$h,($nombre),0,0,'L');
                        }else{
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(80,$h,($nombre),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(70,$h,"",0,0,'L');
                        }
                    }


                    $lines2 = explode("\n",trim($v->resultado));
                    $h2=4*count($lines2);
                    $h3 = $pdf::getNumLines($v->resultado,27)*4;
                    if($h2<$h3){
                        $h2=$h3;
                    }
                    if(strlen($v->resultado)>15){
                        $y=$pdf::GetY();
                        $x=$pdf::GetX();
                        $pdf::Multicell(27,$h2,$v->resultado,0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(37,$h2,'',0,0,'L');
                    }else{
                        $pdf::Cell(37,$h2,$v->resultado,0,0,'L');
                    }
                    if(strlen($v->referencia)>30){
                        $y=$pdf::GetY();
                        $x=$pdf::GetX();
                        $pdf::Multicell(50,$h,$v->referencia,0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(50,$h,'',0,0,'L');
                    }else{
                        $pdf::Cell(50,$h,$v->referencia,0,0,'L');
                    }
                    $pdf::Cell(20,$h,$v->unidad,0,0,'L');
                    $pdf::Ln();                    
                }
                $pdf::SetFont('helvetica','B',9);
                $fechaAnalisis = new \DateTime($value->fecha);
                $hoy = new \DateTime("2019-02-02");
                if($fechaAnalisis >= $hoy){
                    $pdf::Image("http://localhost/juanpablo/dist/img/firma-analisis2.png", 61, 225, 20,35);
                    $pdf::SetY('260');
                    $pdf::Cell(30,4,"",0,0,'C');
                    $pdf::Cell(60,4,"DR. JORGE LUIS GARCIA CARASSAS",0,0,'C');
                    $pdf::Cell(40,4,"",0,0,'C');
                    $pdf::Cell(60,4,"PROCESADO POR:",0,0,'C');
                    $pdf::Ln();                    
                    $pdf::Cell(30,4,"",0,0,'C');
                    $pdf::Cell(60,4,"PATOLOGIA CLINICA",0,0,'C');
                    $pdf::Cell(40,4,"",0,0,'C');
                    $pdf::Cell(60,4,$value->responsable2,0,0,'C');
                    $pdf::Ln();                    
                    $pdf::Cell(30,4,"",0,0,'C');
                    $pdf::Cell(60,4,"CMP 036387",0,0,'C');
                    $pdf::Ln();
                    $pdf::Cell(30,4,"",0,0,'C');
                    $pdf::Cell(60,4,"RNE 024417",0,0,'C');
                    $pdf::Ln();
                }else{
                    $pdf::Image("http://localhost/juanpablo/dist/img/firma-analisis.jpg", 50, 230, 40, 20);
                    $pdf::SetY('255');
                    $pdf::Cell(30,4,"",0,0,'C');
                    $pdf::Cell(60,4,"ANGELA YOVERA PUICAN ",0,0,'C');
                    $pdf::Cell(40,4,"",0,0,'C');
                    $pdf::Cell(60,4,"PROCESADO POR:",0,0,'C');
                    $pdf::Ln();                    
                    $pdf::Cell(30,4,"",0,0,'C');
                    $pdf::Cell(60,4,"PATOLOGIA CLINICA",0,0,'C');
                    $pdf::Cell(40,4,"",0,0,'C');
                    $pdf::Cell(60,4,$value->responsable2,0,0,'C');
                    $pdf::Ln();                    
                    $pdf::Cell(30,4,"",0,0,'C');
                    $pdf::Cell(60,4,"CMP 54524",0,0,'C');
                    $pdf::Ln();
                }
                $pdf::Output('Analisis.pdf');
            }
        }
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

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $numero           = Libreria::getParam($request->input('numero'),'');
        $fecha            = Libreria::getParam($request->input('fechainicial'));
        $fecha2            = Libreria::getParam($request->input('fechafinal'));
        $user = Auth::user();
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where('movimiento.situacion','like','%'.$request->input('situacion').'%')
                            ->where('plan.razonsocial', 'LIKE', '%'.strtoupper($request->input('empresa')).'%')
                            ->where(DB::raw('concat(movimiento.serie,\'-\',movimiento.numero)'),'LIKE','%'.$numero.'%')->where('movimiento.tipodocumento_id','=','17')
                            ->whereNotIn('movimiento.situacion',['U','A']);
        if($fecha!=""){
            if($request->input('situacion')=='P')
                $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fecha.'');
            else
                $resultado = $resultado->where('movimiento.fechaentrega', '>=', ''.$fecha.'');
        }
        if($fecha2!=""){
            if($request->input('situacion')=='P')
                $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fecha2.'');
            else
                $resultado = $resultado->where('movimiento.fechaentrega', '<=', ''.$fecha2.'');
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable',DB::raw('plan.razonsocial as empresa'))->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero','ASC');
        $resultado            = $resultado->get();

        Excel::create('ExcelReporteCobranza', function($excel) use($resultado,$request) {
 
            $excel->sheet('Cobranza', function($sheet) use($resultado,$request) {
 
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Nro";
                $cabecera[] = "Empresa";
                $cabecera[] = "Paciente";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Nro. Ope.";
                $cabecera[] = "Total";
                $cabecera[] = "Detraccion";
                $cabecera[] = "Retencion";
                $cabecera[] = "Pago";
                $cabecera[] = "Situacion";
                $cabecera[] = "Usuario";
                $c=2;$d=3;$band=true;
                $sheet->row(1,$cabecera);

                foreach ($resultado as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->serie.'-'.$value->numero;
                    $detalle[] = $value->empresa;
                    $detalle[] = $value->paciente;
                    if($value->fechaentrega!="")
                        $detalle[] = date("d/m/Y",strtotime($value->fechaentrega));
                    else
                        $detalle[] = '';
                    $detalle[] = $value->voucher;
                    $detalle[] = number_format($value->total,2,'.','');
                    $detalle[] = number_format($value->detraccion,2,'.','');
                    $detalle[] = number_format($value->retencion,2,'.','');
                    $detalle[] = number_format($value->total - $value->detraccion - $value->retencion,2,'.','');
                    if($value->situacion=='P')
                        $detalle[] = 'PENDIENTE';
                    else
                        $detalle[] = "COBRADO";
                    $detalle[] = $value->responsable;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }

            });
        })->export('xls');
    }
}
