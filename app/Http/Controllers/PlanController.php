<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Plan;
use App\Detalleplan;
use App\Tiposervicio;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    protected $folderview      = 'app.plan';
    protected $tituloAdmin     = 'Planes';
    protected $tituloRegistrar = 'Registrar plan';
    protected $tituloModificar = 'Modificar plan';
    protected $tituloEliminar  = 'Eliminar plan';
    protected $rutas           = array('create' => 'plan.create', 
            'edit'   => 'plan.edit', 
            'delete' => 'plan.eliminar',
            'search' => 'plan.buscar',
            'index'  => 'plan.index',
        );

    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
        $entidad          = 'Plan';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = plan::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('nombre', 'ASC')->where('tipopago','like',$request->input('tipopago'));
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Aseguradora', 'numero' => '1');
        $cabecera[]       = array('valor' => 'RUC', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Contratante', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Direccion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Deducible', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Coaseguro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Consulta', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Factor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '2');
        
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entidad          = 'Plan';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoPago     = array("Particular" => "Particular", "Convenio" => "Convenio");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoPago'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Plan';
        $plan = null;
        $formData = array('plan.store');
        $cboTipoPago     = array("Particular" => "Particular", "Convenio" => "Convenio");
        $cboTipo     = array("Aseguradora" => "Aseguradora", "Institucion" => "Institucion");
        $detalle = null;
        $cboTipoServicio = array();
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('plan', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPago', 'cboTipo', 'detalle', 'cboTipoServicio'));
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
        $reglas     = array('nombre' => 'required|max:100',
                            'aseguradora' => 'required|max:100',
                            'ruc' => 'required',
                            'razonsocial' => 'required',
                            'direccion' => 'required');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'aseguradora.required'         => 'Debe ingresar una aseguradora',
            'ruc.required'         => 'Debe ingresar un RUC',
            'razonsocial.required'         => 'Debe ingresar un contratante',
            'direccion.required'         => 'Debe ingresar una direccion',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $plan       = new plan();
            $plan->nombre = strtoupper($request->input('nombre'));
            $plan->aseguradora = strtoupper($request->input('aseguradora'));
            $plan->ruc = $request->input('ruc');
            $plan->razonsocial = strtoupper($request->input('razonsocial'));
            $plan->direccion = strtoupper($request->input('direccion'));
            $plan->coaseguro = $request->input('coaseguro');
            $plan->deducible = $request->input('deducible');
            $plan->consulta = $request->input('consulta');
            $plan->factor = $request->input('factor');
            $plan->tipopago = $request->input('tipopago');
            $plan->tipo = $request->input('tipo');
            $plan->descuentogenerico = $request->input('descuentogenerico');
            $plan->descuentomarca = $request->input('descuentomarca');
            $plan->save();
            if($request->input('tipo')=="Institucion"){
                $list=explode(",",$request->input('listTipoServicio'));
                for($i=0;$i<count($list);$i++){
                    $detalle = new Detalleplan;
                    $detalle->plan_id = $plan->id;
                    $detalle->tiposervicio_id = $list[$i];
                    $detalle->descuento = $request->input('txtPrecio'.$list[$i]);
                    $detalle->save();
                }
            }
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
        $existe = Libreria::verificarExistencia($id, 'plan');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $plan = plan::find($id);
        $detalle = Detalleplan::where('plan_id','=',$id)->get();
        $entidad  = 'Plan';
        $formData = array('plan.update', $id);
        $cboTipoPago     = array("Particular" => "Particular", "Convenio" => "Convenio");
        $cboTipo     = array("Aseguradora" => "Aseguradora", "Institucion" => "Institucion");
        $cboTipoServicio = array();
        $tiposervicio = Tiposervicio::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($tiposervicio as $key => $value) {
            $cboTipoServicio = $cboTipoServicio + array($value->id => $value->nombre);
        }
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('plan', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPago', 'cboTipo', 'detalle', 'cboTipoServicio'));
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
        $existe = Libreria::verificarExistencia($id, 'plan');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('nombre' => 'required|max:100',
                            'aseguradora' => 'required|max:100',
                            'ruc' => 'required',
                            'razonsocial' => 'required',
                            'direccion' => 'required');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'aseguradora.required'         => 'Debe ingresar una aseguradora',
            'ruc.required'         => 'Debe ingresar un RUC',
            'razonsocial.required'         => 'Debe ingresar un contratante',
            'direccion.required'         => 'Debe ingresar una direccion',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $plan       = plan::find($id);
            $plan->nombre = strtoupper($request->input('nombre'));
            $plan->aseguradora = strtoupper($request->input('aseguradora'));
            $plan->ruc = $request->input('ruc');
            $plan->razonsocial = strtoupper($request->input('razonsocial'));
            $plan->direccion = strtoupper($request->input('direccion'));
            $plan->coaseguro = $request->input('coaseguro');
            $plan->deducible = $request->input('deducible');
            $plan->consulta = $request->input('consulta');
            $plan->factor = $request->input('factor');
            $plan->tipopago = $request->input('tipopago');
            $plan->tipo = $request->input('tipo');
            $plan->descuentogenerico = $request->input('descuentogenerico');
            $plan->descuentomarca = $request->input('descuentomarca');
            $plan->save();
            if($request->input('tipo')=="Institucion"){
                $list=explode(",",$request->input('listTipoServicio'));
                $detalle = Detalleplan::where('plan_id','=',$id)->get();
                foreach ($detalle as $key => $value) {
                    $value->delete();
                }
                for($i=0;$i<count($list);$i++){
                    $detalle = new Detalleplan;
                    $detalle->plan_id = $plan->id;
                    $detalle->tiposervicio_id = $list[$i];
                    $detalle->descuento = $request->input('txtPrecio'.$list[$i]);
                    $detalle->save();
                }
            }
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
        $existe = Libreria::verificarExistencia($id, 'plan');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $plan = plan::find($id);
            $plan->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'plan');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = plan::find($id);
        $entidad  = 'Plan';
        $formData = array('route' => array('plan.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function planautocompletar($searching)
    {
        $entidad    = 'Plan';        
        $resultado = Plan::where('nombre', 'LIKE', '%'.strtoupper($searching).'%');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array(
                            'label' => $value->nombre,
                            'id'    => $value->id,
                            'value' => $value->nombre,
                            'coa'=> $value->coaseguro,
                            'deducible' => $value->deducible,
                            'ruc' => $value->ruc,
                            'direccion' => $value->direccion,
                            'razonsocial' => $value->razonsocial,
                            'tipo' => $value->tipo,
                        );
        }
        return json_encode($data);
    }
    
    public function buscarfactor(Request $request){
        $list = Plan::find($request->input('plan_id'));
        return $list->factor;
    }

}
