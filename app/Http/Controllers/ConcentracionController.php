<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Librerias\Libreria;
use Validator;
use App\Concentracion;
use Illuminate\Support\Facades\DB;

class ConcentracionController extends Controller
{
    protected $folderview      = 'app.concentracion';
    protected $tituloAdmin     = 'Concentración';
    protected $tituloRegistrar = 'Registrar Concentración';
    protected $tituloModificar = 'Modificar Concentración';
    protected $tituloEliminar  = 'Eliminar Concentración';
    protected $rutas           = array('create' => 'concentracion.create', 
            'edit'   => 'concentracion.edit', 
            'delete' => 'concentracion.eliminar',
            'search' => 'concentracion.buscar',
            'index'  => 'concentracion.index',
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

    public function index()
    {
        //
    }

    public function crearsimple(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Concentracion';
        $concentracion = null;
        $formData = array('concentracion.guardarsimple');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mantSimple')->with(compact('concentracion', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function guardarsimple(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array('nombre' => 'required|max:255');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $dat=array();
        $error = DB::transaction(function() use($request,&$dat){
            $concentracion       = new Concentracion();
            $concentracion->nombre = strtoupper($request->input('nombre'));
            $concentracion->save();
            $dat[0]=array("respuesta"=>"OK","id"=>$concentracion->id,"nombre"=>$concentracion->nombre); 
        });
        return is_null($error) ? json_encode($dat) : $error;
    }

 
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
