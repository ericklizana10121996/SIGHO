<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Rolpersona;
use App\Person;
use App\Horario;
use App\Especialidad;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Excel;


class MedicoController extends Controller
{
    protected $folderview      = 'app.medico';
    protected $tituloAdmin     = 'Medicos';
    protected $tituloRegistrar = 'Registrar medico';
    protected $tituloModificar = 'Modificar medico';
    protected $tituloEliminar  = 'Eliminar medico';
    protected $rutas           = array('create' => 'medico.create', 
            'edit'   => 'medico.edit', 
            'delete' => 'medico.eliminar',
            'search' => 'medico.buscar',
            'index'  => 'medico.index',
            'lista'  => 'medico.lista',
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
        $entidad          = 'Medico';
        $nombre           = Libreria::getParam($request->input('nombre'));
        $socio            = Libreria::getParam($request->input('socio'));
        $resultado        = Person::join('especialidad','especialidad.id','=','person.especialidad_id')
                            ->where('workertype_id','=','1');
        if(strlen($socio)>0){
            $resultado        = $resultado->where("person.socio",$socio);
        }
        $resultado        = $resultado->where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('apellidopaterno', 'ASC');
        $especialidad     = $request->input('especialidad','');
        $especialidad_id     = $request->input('especialidad_id','0');
        $modo             = $request->input('modo','');
        if($especialidad!=""){
            $resultado = $resultado->where('especialidad.nombre','LIKE','%'.$especialidad.'%');
        }
        if($especialidad_id!="0"){
            $resultado = $resultado->where('especialidad.id','=',$especialidad_id);
        }
        $lista            = $resultado->select('person.*')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Medico', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Especialidad', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'DNI', 'numero' => '1');
        $cabecera[]       = array('valor' => 'RNE', 'numero' => '1');
        $cabecera[]       = array('valor' => 'CMP', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Telefono', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '4');
        
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'modo'));
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
        $entidad          = 'Medico';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboEspecialidad = array('0' => 'Todos');
        $list = Especialidad::orderBy('nombre','asc')->get();
        foreach ($list as $key => $value) {
            $cboEspecialidad = $cboEspecialidad + array($value->id => $value->nombre);
        }
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboEspecialidad'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Medico';
        $medico = null;
        //$cboEspecialidad  = Especialidad::lists('nombre', 'id')->all();
        $cboEspecialidad = array();
        $especialidades = Especialidad::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($especialidades as $key => $value) {
            $cboEspecialidad = $cboEspecialidad + array($value->id => $value->nombre);
        }
        $cboTipomedico = array('E' => 'Especialista', 'G' => 'General');
        $formData = array('medico.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('medico', 'formData', 'entidad', 'boton', 'listar','cboEspecialidad','cboTipomedico'));
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
            'nombres'                  => 'required|max:100',
            'apellidopaterno'                  => 'required|max:100',
            'apellidomaterno'                 => 'required|max:100',
            //'dni'                 => 'required'
            );
        $mensajes = array(
            'nombres.required'         => 'Debe ingresar nombres',
            'apellidopaterno.required'         => 'Debe ingresar un apellido paterno',
            'apellidomaterno.required'         => 'Debe ingresar apellido materno',
            //'dni.required'         => 'Debe ingresar un dni'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $medico       = new Person();
            $medico->nombres = strtoupper($request->input('nombres'));
            $medico->apellidopaterno = strtoupper($request->input('apellidopaterno'));
            $medico->apellidomaterno = strtoupper($request->input('apellidomaterno'));
            $medico->dni = strtoupper($request->input('dni'));
            $medico->ruc = Libreria::obtenerParametro($request->input('ruc'));
            $medico->rne = Libreria::obtenerParametro($request->input('rne'));
            $medico->cmp = Libreria::obtenerParametro($request->input('cmp'));
            $medico->direccion = Libreria::obtenerParametro($request->input('direccion'));
            $medico->telefono = Libreria::obtenerParametro($request->input('telefono'));
            $medico->email = Libreria::obtenerParametro($request->input('email'));
            $medico->especialidad_id = Libreria::obtenerParametro($request->input('especialidad_id'));
            $medico->tipomedico = Libreria::obtenerParametro($request->input('tipomedico'));
            $medico->workertype_id = 1;
            $medico->save();

            $rolpersona = new Rolpersona();
            $rolpersona->rol_id = 1;
            $rolpersona->person_id = $medico->id;
            $rolpersona->save();
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
        $existe = Libreria::verificarExistencia($id, 'person');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $medico = Person::find($id);
        $entidad  = 'Medico';
        //$cboEspecialidad  = Especialidad::lists('nombre', 'id')->all();
        $cboEspecialidad = array();
        $especialidades = Especialidad::where(DB::raw('1'),'=','1')->orderBy('nombre','ASC')->get();
        foreach ($especialidades as $key => $value) {
            $cboEspecialidad = $cboEspecialidad + array($value->id => $value->nombre);
        }
        $cboTipomedico = array('E' => 'Especialista', 'G' => 'General');
        $formData = array('medico.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('medico', 'formData', 'entidad', 'boton', 'listar','cboEspecialidad','cboTipomedico'));
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
        $existe = Libreria::verificarExistencia($id, 'person');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
            'nombres'                  => 'required|max:100',
            'apellidopaterno'                  => 'required|max:100',
            'apellidomaterno'                 => 'required|max:100',
            //'dni'                 => 'required'
            );
        $mensajes = array(
            'nombres.required'         => 'Debe ingresar nombres',
            'apellidopaterno.required'         => 'Debe ingresar un apellido paterno',
            'apellidomaterno.required'         => 'Debe ingresar apellido materno',
            //'dni.required'         => 'Debe ingresar un dni'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $medico       = Person::find($id);
            $medico->nombres = strtoupper($request->input('nombres'));
            $medico->apellidopaterno = strtoupper($request->input('apellidopaterno'));
            $medico->apellidomaterno = strtoupper($request->input('apellidomaterno'));
            $medico->dni = strtoupper($request->input('dni'));
            $medico->ruc = Libreria::obtenerParametro($request->input('ruc'));
            $medico->rne = Libreria::obtenerParametro($request->input('rne'));
            $medico->cmp = Libreria::obtenerParametro($request->input('cmp'));
            $medico->direccion = Libreria::obtenerParametro($request->input('direccion'));
            $medico->telefono = Libreria::obtenerParametro($request->input('telefono'));
            $medico->email = Libreria::obtenerParametro($request->input('email'));
            $medico->especialidad_id = Libreria::obtenerParametro($request->input('especialidad_id'));
            $medico->tipomedico = Libreria::obtenerParametro($request->input('tipomedico'));
            //$medico->workertype_id = 1;
            $medico->save();
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
        $existe = Libreria::verificarExistencia($id, 'person');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $medico = Person::find($id);
            $medico->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'person');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Person::find($id);
        $entidad  = 'Medico';
        $formData = array('route' => array('medico.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function lista()
    {
        $entidad          = 'Medico';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.lista')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }
    
    public function seleccionarMedico(Request $request)
    {
        $id = $request->input("idmedico");
        $entidad    = 'Medico';
        $value = Person::find($id);
        $dato = Horario::where('person_id','=',$value->id)->where('desde','>=',date("Y-m-d"))->first();
        if(count($dato)>0){
            $fecha='Desde '.$dato->desde.' al '.$dato->hasta;
            $observacion=$dato->observaciones;
            $horario=$dato->horarios;
        }else{
            $fecha="";
            $observacion="";
            $horario="";
        }
        $data[] = array(
                    'medico' => $value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres,
                    'especialidad' => $value->especialidad->nombre,
                    'id'    => $value->id,
                    'fecha' => $fecha,
                    'observacion' => $observacion,
                    'horario' => $horario,
                );
        return json_encode($data);
    }

    public function medicoautocompletar($searching)
    {
        $resultado        = Person::join('especialidad','especialidad.id','=','person.especialidad_id')
                            ->where('workertype_id','=','1')->where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres, " (",especialidad.nombre,")")'), 'LIKE', '%'.strtoupper($searching).'%')->orderBy('socio', 'DESC')->orderBy('apellidopaterno', 'ASC')
                            ->select('person.*','especialidad.nombre as especialidad');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $dato = Horario::where('person_id','=',$value->id)->where('desde','<=',date("Y-m-d"))->where('hasta','>=',date("Y-m-d"))->first();
            if(count($dato)>0){
                $fecha='Desde '.$dato->desde.' al '.$dato->hasta;
                $observacion=$dato->observaciones;
                $horario=str_replace("\\r\\n","<br />",json_encode($dato->horarios));
            }else{
                $fecha="";
                $observacion="";
                $horario="";
            }
            if($value->apellidomaterno==""){
                $data[] = array(
                            'label' => trim($value->apellidopaterno." ".$value->nombres),
                            'id'    => $value->id,
                            'value' => trim($value->apellidopaterno." ".$value->nombres),
                            'especialidad' => $value->especialidad,
                            'fecha' => $fecha,
                            'observacion' => $observacion,
                            'horario' => $horario,
                        );
            }else{
                $data[] = array(
                            'label' => trim($value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres),
                            'id'    => $value->id,
                            'value' => trim($value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres),
                            'especialidad' => $value->especialidad,
                            'fecha' => $fecha,
                            'observacion' => $observacion,
                            'horario' => $horario,
                        );
            }
        }
        return json_encode($data);
    }

    public function horario(Request $request)
    {
        
        $dato = Horario::where('person_id','=',$request->input('idmedico'))->where('desde','<=',date("Y-m-d"))->where('hasta','>=',date("Y-m-d"))->first();
        if(count($dato)>0){
            $fecha='Desde '.$dato->desde.' al '.$dato->hasta;
            $observacion=$dato->observaciones;
            $horario=str_replace("\\r\\n","<br />",json_encode($dato->horarios));
        }else{
            $fecha="";
            $observacion="";
            $horario="";
        }
        $data[] = array(
                    'fecha' => $fecha,
                    'observacion' => $observacion,
                    'horario' => $horario,
                );
        return json_encode($data);
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Person::join('especialidad','especialidad.id','=','person.especialidad_id')
                            ->where('workertype_id','=','1')->orderBy('apellidopaterno', 'ASC');
        
        $resultado        = $resultado->select('person.*')->orderBy('person.apellidopaterno', 'ASC')->get();

        Excel::create('ExcelMedico', function($excel) use($resultado,$request) {
 
            $excel->sheet('Medico', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Medico";
                $cabecera[] = "Especialidad";
                $cabecera[] = "Tipo";
                $cabecera[] = "DNI";
                $cabecera[] = "RNE";
                $cabecera[] = "CMP";
                $cabecera[] = "Telefono";
                $array[] = $cabecera;
                $c=1;$d=3;
                foreach ($resultado as $key => $value){
                    $detalle = array();
                    $detalle[] = $value->apellidopaterno.' '.$value->apellidomaterno.' '.$value->nombres;
                    $detalle[] = $value->especialidad->nombre;
                    $detalle[] = ($value->tipomedico == 'E'?'Especialista':'General');
                    $detalle[] = $value->dni;
                    $detalle[] = $value->rne;
                    $detalle[] = $value->cmp;
                    $detalle[] = $value->telefono;
                    $array[] = $detalle;
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }

}
