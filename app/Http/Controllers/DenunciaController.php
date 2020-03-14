<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Denuncia;
use App\Movimiento;
use App\Person;
use App\Caja;
use App\Librerias\Libreria;
use App\Librerias\EnLetras;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;
use Excel;


class DenunciaController extends Controller
{
    protected $folderview      = 'app.denuncia';
    protected $tituloAdmin     = 'Denuncia';
    protected $tituloRegistrar = 'Registrar Denuncia';
    protected $tituloModificar = 'Modificar Denuncia';
    protected $tituloEliminar  = 'Eliminar Denuncia';
    protected $rutas           = array('create' => 'denuncia.create', 
            'edit'   => 'denuncia.edit', 
            'delete' => 'denuncia.eliminar',
            'search' => 'denuncia.buscar',
            'index'  => 'denuncia.index',
            'pdfListar'  => 'denuncia.pdfListar',
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
        $entidad          = 'Denuncia';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $fechainicio      = Libreria::getParam($request->input('fechainicio'));
        $fechafin         = Libreria::getParam($request->input('fechafin'));
        $user = Auth::user();

        $resultado        = Denuncia::join('historia', 'historia.id', '=', 'denuncia.historia_id')
                            ->join('person as paciente', 'paciente.id', '=', 'historia.person_id')
                            ->leftjoin('person as responsable','responsable.id','=','denuncia.usuario_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where('denuncia.placa','like','%'.$request->input('placa').'%');
        if($request->input('fechainicio')!=''){
            $resultado= $resultado->where('denuncia.fecha','>=',''.$fechainicio.'');
        }
        if($request->input('fechafin')!=''){
            $resultado= $resultado->where('denuncia.fecha','<=',''.$fechafin.'');
        }
        $resultado        = $resultado->select('denuncia.*',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable')->orderBy('denuncia.fecha', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Seguro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Placa', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Garantia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Denuncia', 'numero' => '1');
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
        $entidad          = 'Denuncia';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Denuncia';
        $denuncia = null;
        $formData            = array('denuncia.store');
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('denuncia', 'formData', 'entidad', 'boton', 'listar'));
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
        
        $error = DB::transaction(function() use($request,$user,&$dat){
            $Denuncia      = new Denuncia();
            $Denuncia->fecha = $request->input('fecha');
            $Denuncia->historia_id = $request->input('historia_id');
            $Denuncia->seguro = $request->input('seguro');
            $Denuncia->garantia = $request->input('garantia');
            $Denuncia->placa = $request->input('placa');
            $Denuncia->denuncia = $request->input('denuncia');
            $Denuncia->usuario_id=$user->person_id;
            $Denuncia->save();

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
        $existe = Libreria::verificarExistencia($id, 'denuncia');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $denuncia = Denuncia::find($id);
        $entidad             = 'Denuncia';
        $formData            = array('denuncia.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('denuncia', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'denuncia');
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
        $error = DB::transaction(function() use($request, $id, &$dat){
            $Denuncia = Denuncia::find($id);
            $Denuncia->fecha = $request->input('fecha');
            $Denuncia->historia_id = $request->input('historia_id');
            $Denuncia->seguro = $request->input('seguro');
            $Denuncia->garantia = $request->input('garantia');
            $Denuncia->placa = $request->input('placa');
            $Denuncia->denuncia = $request->input('denuncia');
            $Denuncia->save();

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
        $existe = Libreria::verificarExistencia($id, 'denuncia');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Denuncia = Denuncia::find($id);
            $Denuncia->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'denuncia');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Denuncia::find($id);
        $entidad  = 'Denuncia';
        $formData = array('route' => array('denuncia.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function denunciaautocompletar($searching)
    {
        $entidad    = 'Denuncia';        
        $resultado = Movimiento::where('numero', 'LIKE', '%'.strtoupper($searching).'%')
                            ->where('movimiento.tipodocumento_id','=','18')
                            ->select('movimiento.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array(
                            'value' => $value->numero.' / '.$value->total,
                            'id'    => $value->id,
                        );
        }
        return json_encode($data);
    }

    public function buscarGarantia(Request $request)
    {
        $entidad    = 'Denuncia';        
        $resultado = Movimiento::where('persona_id', '=', $request->input('id'))
                            ->where('movimiento.tipodocumento_id','=','18')
                            ->select('movimiento.*')
                            ->orderBy('movimiento.fecha','desc');
        $list      = $resultado->first();
        if(!is_null($list)){
            echo "vmsg='SI';vidmovimiento='".$list->id."';vnumero='".$list->numero." / ".$list->total."'";
        }else{
            echo "vmsg='NO';";
        }
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $resultado        = Denuncia::join('historia', 'historia.id', '=', 'denuncia.historia_id')
                            ->join('person as paciente', 'paciente.id', '=', 'historia.person_id')
                            ->leftjoin('person as responsable','responsable.id','=','denuncia.usuario_id')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'), 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where('denuncia.placa','like','%'.$request->input('placa').'%');
        if($request->input('fechainicio')!=''){
            $resultado= $resultado->where('denuncia.fecha','>=',''.$fechainicio.'');
        }
        if($request->input('fechafin')!=''){
            $resultado= $resultado->where('denuncia.fecha','<=',''.$fechafin.'');
        }
        $resultado        = $resultado->select('denuncia.*',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente'),'responsable.nombres as responsable')->orderBy('denuncia.fecha', 'ASC');
        $lista            = $resultado->get();

        Excel::create('ExcelReporte', function($excel) use($lista,$request) {
 
            $excel->sheet('Reporte', function($sheet) use($lista,$request) {
                $cabecera[] = "Fecha";
                $cabecera[] = "Seguro";
                $cabecera[] = "Paciente";
                $cabecera[] = "Placa";
                $cabecera[] = "Garantia";
                $cabecera[] = "Denuncia";
                $cabecera[] = "Usuario";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;

                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->seguro;
                    $detalle[] = $value->paciente;
                    $detalle[] = $value->placa;
                    if(!is_null($value->docgarantia)){
                        $detalle[] = $value->docgarantia->numero.' / '.$value->docgarantia->total;
                    }else{
                        $detalle[] = "-";    
                    }
                    $detalle[] = $value->denuncia;
                    $detalle[] = $value->responsable;
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
            });
        })->export('xls');
    }


}
