<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Examen;
use App\Detalleexamen;
use App\Tipoexamen;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;

class ExamenController extends Controller
{
    protected $folderview      = 'app.examen';
    protected $tituloAdmin     = 'Examen';
    protected $tituloRegistrar = 'Registrar Examen';
    protected $tituloModificar = 'Modificar Examen';
    protected $tituloEliminar  = 'Eliminar Examen';
    protected $rutas           = array('create' => 'examen.create', 
            'edit'   => 'examen.edit', 
            'delete' => 'examen.eliminar',
            'search' => 'examen.buscar',
            'index'  => 'examen.index',
            'pdfListar'  => 'examen.pdfListar',
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
        $entidad          = 'Examen';
        $user = Auth::user();

        $resultado        = Examen::join('tipoexamen','tipoexamen.id','=','examen.tipoexamen_id')
                            ->where('examen.nombre', 'LIKE', '%'.strtoupper($request->input('nombre')).'%');
        if($request->input('tipoexamen_id')!="0"){
            $resultado= $resultado->where('examen.tipoexamen_id','=',$request->input('tipoexamen_id'));
        }
        $resultado        = $resultado->select('examen.*','tipoexamen.nombre as tipoexamen2')->orderBy('tipoexamen.nombre', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Examen', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Examen', 'numero' => '1');
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
        $entidad          = 'Examen';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoexamen          = array('0' => 'Todos');
        $list = Tipoexamen::orderBy('nombre','asc')->get();
        foreach ($list as $key => $value) {
            $cboTipoexamen = $cboTipoexamen + array($value->id => $value->nombre);
        }
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoexamen'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Examen';
        $examen              = null;
        $formData            = array('examen.store');
        $cboTipoexamen          = array();
        $list = Tipoexamen::orderBy('nombre','asc')->get();
        foreach ($list as $key => $value) {
            $cboTipoexamen = $cboTipoexamen + array($value->id => $value->nombre);
        }
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('examen', 'formData', 'entidad', 'boton', 'listar', 'cboTipoexamen'));
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
                'nombre'          => 'required',
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $dat=array();
        $list = Examen::where('nombre','like',$request->input('nombre'))->get();
        $dat[0]=array("respuesta"=>"Erro","msg"=>"Ya creado");
        if(count($list)>0){
            return json_encode($dat);
        }
        
        $user = Auth::user();
        
        $error = DB::transaction(function() use($request,$user,&$dat){
            $Examen       = new Examen();
            $Examen->nombre = $request->input('nombre');
            $Examen->tipoexamen_id = $request->input('tipoexamen_id');
            $Examen->save();

            $arr=explode(",",$request->input('listServicio'));
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detalleexamen();
                $Detalle->examen_id=$Examen->id;
                $Detalle->unidad=$request->input('txtUnidad'.$arr[$c]);
                $Detalle->descripcion=trim($request->input('txtDescripcion'.$arr[$c]));
                $Detalle->referencia=$request->input('txtReferencia'.$arr[$c]);
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
        $existe = Libreria::verificarExistencia($id, 'examen');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $cboTipoexamen          = array();
        $list = Tipoexamen::orderBy('nombre','asc')->get();
        foreach ($list as $key => $value) {
            $cboTipoexamen = $cboTipoexamen + array($value->id => $value->nombre);
        }
        $examen = Examen::find($id);
        $entidad             = 'Examen';
        $formData            = array('examen.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('examen', 'formData', 'entidad', 'boton', 'listar', 'cboTipoexamen'));
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
        $existe = Libreria::verificarExistencia($id, 'examen');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'nombre'                  => 'required',
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $dat= array();
        $error = DB::transaction(function() use($request, $id, &$dat){
            Detalleexamen::where('examen_id','=',$id)->delete();
            $Examen = Examen::find($id);
            $Examen->nombre = $request->input('nombre');
            $Examen->tipoexamen_id = $request->input('tipoexamen_id');
            $Examen->save();

            $arr=explode(",",$request->input('listServicio'));
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detalleexamen();
                $Detalle->examen_id=$Examen->id;
                $Detalle->unidad=$request->input('txtUnidad'.$arr[$c]);
                $Detalle->descripcion=trim($request->input('txtDescripcion'.$arr[$c]));
                $Detalle->referencia=$request->input('txtReferencia'.$arr[$c]);
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
        $existe = Libreria::verificarExistencia($id, 'examen');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $examen = Examen::find($id);
            $examen->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'examen');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Examen::find($id);
        $entidad  = 'Examen';
        $formData = array('route' => array('examen.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    

    public function agregardetalle(Request $request){
        $resultado        = Detalleexamen::where('detalleexamen.examen_id', '=', $request->input('id'))
                            ->select('detalleexamen.*');
        $lista            = $resultado->get();
        $data = array();
        foreach($lista as $k => $v){
            $data[] = array("referencia" => $v->referencia,
                            "unidad" => $v->unidad,
                            "descripcion" => $v->descripcion,
                            "id" => $v->id,
                            );
        }
        return json_encode($data);
    }

}
