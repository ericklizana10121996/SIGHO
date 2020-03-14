<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Person;
use App\Rolpersona;
use App\Departamento;
use App\Provincia;
use App\Distrito;
use App\Workertype;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;

class EmployeeController extends Controller
{
    protected $folderview      = 'app.employee';
    protected $tituloAdmin     = 'Empleados';
    protected $tituloRegistrar = 'Registrar empleado';
    protected $tituloModificar = 'Modificar empleado';
    protected $tituloEliminar  = 'Eliminar empleado';
    protected $rutas           = array('create' => 'employee.create', 
            'edit'   => 'employee.edit', 
            'delete' => 'employee.eliminar',
            'search' => 'employee.buscar',
            'index'  => 'employee.index',
            'validar'  => 'employee.validarexistente',
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
        $entidad          = 'Employee';
        $name             = Libreria::getParam($request->input('name'));
        $resultado        = Rolpersona::join('person','rolpersona.person_id','=','person.id')
                            ->where('rol_id','=','5')->where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($name).'%')->whereNull('person.deleted_at')->orderBy('apellidopaterno', 'ASC')->select('rolpersona.*');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Apellido y nombres ', 'numero' => '1');
        $cabecera[]       = array('valor' => 'DNI', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Direccion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Telefono', 'numero' => '1');
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
        $entidad          = 'Employee';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    public function validarexistente(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Employee';
        $employee = null;
        $formData = array('employee.guardarexistente');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mantExistente')->with(compact('employee', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function validardni(Request $request)
    {
        $dni = Libreria::getParam($request->input('dni'));
        $person = Person::where('dni','=',$dni)->first();
        $id = 0;
        $name = 'No es paciente';
        if ($person !== null) {
            $id = $person->id;
            $name = $person->nombres.' '.$person->apellidopaterno.' '.$person->apellidomaterno;
        }
        return $id.'-'.$name;
    }

    public function guardarexistente(Request $request)
    {
        $reglas     = array(
                'person_id' => 'required|integer|exists:person,id,deleted_at,NULL',
                );
        $mensajes = array(
            'person_id.required'         => 'Debe ingresar un cliente',
            );


        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }

        $error = DB::transaction(function() use($request){
            $rolpersona = new Rolpersona();
            $rolpersona->rol_id = 5;
            $rolpersona->person_id = Libreria::getParam($request->input('person_id'));
            $rolpersona->save();
        });
        return is_null($error) ? "OK" : $error;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar          = Libreria::getParam($request->input('listar'), 'NO');
        $entidad         = 'Employee';
        $departamento    = Departamento::where('nombre', '=', 'LAMBAYEQUE')->first();
        $provincia       = Provincia::where('nombre', '=', 'CHICLAYO')->where('departamento_id', '=', $departamento->id)->first();
        $distrito        = Distrito::where('nombre', '=', 'CHICLAYO')->where('provincia_id', '=', $provincia->id)->first();
        $cboDepartamento = [''=>'Seleccione'] + Departamento::orderBy('nombre', 'ASC')->lists('nombre', 'id')->all();
        $cboProvincia    = [''=>'Seleccione'] + Provincia::where('departamento_id', '=', $departamento->id)->orderBy('nombre', 'ASC')->lists('nombre', 'id')->all();
        $cboDistrito     = [''=>'Seleccione'] + Distrito::where('provincia_id', '=', $provincia->id)->orderBy('nombre', 'ASC')->lists('nombre', 'id')->all();
        $cboWorkertype   = [''=>'Seleccione'] + Workertype::lists('name', 'id')->all();
        $birthdate       = null;
        $employee        = null;
        $formData        = array('employee.store');
        $formData        = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton           = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('employee', 'formData', 'entidad', 'boton', 'listar', 'departamento', 'provincia', 'distrito', 'cboDepartamento', 'cboProvincia', 'cboDistrito', 'birthdate', 'cboWorkertype'));
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
        $mensajes = array(
            'birthdate.required'         => 'Ingrese fecha de nacimiento',
            'direccion.required'           => 'Debe ingresar la dirección del personal',
            'dni.required'               => 'Debe ingresar el DNI del personal',
            'nombres.required'         => 'Debe ingresar nombre del personal',
            'apellidopaterno.required'          => 'Debe ingresar apellido paterno del personal',
            'apellidomaterno.required'          => 'Debe ingresar apellido materno del personal',
            'dni.exists'                 => 'N° de DNI pertenece a personal ya registrado',
            'workertype_id.required'     => 'Debe seleccionar el tipo de trabajador'
            );
        $reglas = array(
                'birthdate'       => 'required|date_format:d/m/Y',
                'apellidopaterno'        => 'required|max:100',
                'apellidomaterno'        => 'required|max:100',
                'nombres'       => 'required|max:100',
                'dni'             => 'required|unique:person,dni,NULL,id,deleted_at,NULL|regex:/^[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]$/',
                'ruc'             => 'unique:person,dni,NULL,id,deleted_at,NULL|regex:/^[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]$/',
                'direccion'         => 'required|max:120',
                'workertype_id'   => 'required|integer|exists:workertype,id,deleted_at,NULL'
                );

        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            Date::setLocale('es');
            $employee                = new Person();
            $employee->apellidopaterno     = strtoupper($request->input('apellidopaterno'));
            $employee->apellidomaterno     = strtoupper($request->input('apellidomaterno'));
            $employee->nombres      = $request->input('nombres');
            $employee->workertype_id = $request->input('workertype_id');
            $employee->direccion       = strtoupper($request->input('direccion'));
            $employee->dni           = $request->input('dni');
            if ($request->input('birthdate') != null) {
                $employee->fechanacimiento     = Date::createFromFormat('d/m/Y', $request->input('birthdate'))->format('Y-m-d');
            }
            
            $employee->email         = Libreria::getParam($request->input('email'));
            $employee->ruc           = Libreria::getParam($request->input('ruc'));
            $employee->telefono   = Libreria::getParam($request->input('telefono'));
            $employee->save();

            $rolpersona = new Rolpersona();
            $rolpersona->rol_id = 5;
            $rolpersona->person_id = $employee->id;
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
        $listar          = Libreria::getParam($request->input('listar'), 'NO');
        $employee        = Person::find($id);
        $entidad         = 'Employee';
        $cboWorkertype   = array('' => 'Seleccione') + Workertype::lists('name', 'id')->all();
        
        $formData        = array('employee.update', $id);
        $formData        = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton           = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('employee', 'formData', 'entidad', 'boton', 'departamento', 'provincia', 'distrito', 'cboDepartamento', 'cboProvincia', 'cboDistrito', 'listar', 'cboWorkertype'));
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
        $request->merge(array_map('trim', $request->all()));
        $mensajes = array(
            'birthdate.required'         => 'Ingrese fecha de nacimiento',
            'direccion.required'           => 'Debe ingresar la dirección del personal',
            'dni.required'               => 'Debe ingresar el DNI del personal',
            'nombres.required'         => 'Debe ingresar nombre del personal',
            'apellidopaterno.required'          => 'Debe ingresar apellido paterno del personal',
            'apellidomaterno.required'          => 'Debe ingresar apellido materno del personal',
            'workertype_id.required'     => 'Debe seleccionar el tipo de trabajador'
            );
        $reglas = array(
                'birthdate'       => 'required|date_format:d/m/Y',
                'apellidopaterno'        => 'required|max:100',
                'apellidomaterno'        => 'required|max:100',
                'nombres'       => 'required|max:100',
                'dni'             => 'required',
                'direccion'         => 'required|max:120',
                'workertype_id'   => 'required|integer|exists:workertype,id,deleted_at,NULL'
                );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }

        $person = Person::where('dni','=',$request->input('dni'))->where('id','<>',$id)->first();
        if ($person !== null) {
            $error = array(
                'dni' => array(
                    'El dni ya esta siendo usado por otra persona'
                    ));
            return json_encode($error);
        }
        $error = DB::transaction(function() use($request, $id){
            $employee                = Person::find($id);
            $employee->apellidopaterno     = strtoupper($request->input('apellidopaterno'));
            $employee->apellidomaterno     = strtoupper($request->input('apellidomaterno'));
            $employee->nombres      = $request->input('nombres');
            $employee->workertype_id = $request->input('workertype_id');
            $employee->direccion       = strtoupper($request->input('direccion'));
            $employee->dni           = $request->input('dni');
            if ($request->input('birthdate') != null) {
                $employee->fechanacimiento     = Date::createFromFormat('d/m/Y', $request->input('birthdate'))->format('Y-m-d');
            }
            
            $employee->email         = Libreria::getParam($request->input('email'));
            $employee->ruc           = Libreria::getParam($request->input('ruc'));
            $employee->telefono   = Libreria::getParam($request->input('telefono'));
            $employee->save();
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
            $employee = Person::find($id);
            $employee->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Función para confirmar la eliminación de un registrlo
     * @param  integer $id          id del registro a intentar eliminar
     * @param  string $listarLuego consultar si luego de eliminar se listará
     * @return html              se retorna html, con la ventana de confirmar eliminar
     */
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
        $entidad  = 'Employee';
        $formData = array('route' => array('employee.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function trabajadorautocompletar($searching)
    {
        $resultado        = Rolpersona::join('person','rolpersona.person_id','=','person.id')
                            ->where('rol_id','=','5')
                            ->where(DB::raw('CONCAT(person.apellidopaterno," ",person.apellidomaterno," ",person.nombres)'), 'LIKE', '%'.strtoupper($searching).'%')->orderBy('person.apellidopaterno', 'ASC')->whereNull('person.deleted_at')
                            ->select('person.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            if($value->apellidomaterno==""){
                $data[] = array(
                            'label' => trim($value->apellidopaterno." ".$value->nombres),
                            'id'    => $value->id,
                            'value' => trim($value->apellidopaterno." ".$value->nombres),
                        );
            }else{
                $data[] = array(
                            'label' => trim($value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres),
                            'id'    => $value->id,
                            'value' => trim($value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres),
                        );
            }
        }
        return json_encode($data);
    }

    public function mixtoautocompletar($searching)
    {
        $resultado = Rolpersona::join('person','rolpersona.person_id','=','person.id')
                            ->whereIn('rol_id',['5','1'])->where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($searching).'%')->whereNull('person.deleted_at')->orderBy('apellidopaterno', 'ASC')->select('person.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            if($value->apellidomaterno==""){
                $data[] = array(
                            'label' => trim($value->apellidopaterno." ".$value->nombres),
                            'id'    => $value->id,
                            'value' => trim($value->apellidopaterno." ".$value->nombres),
                        );
            }else{
                $data[] = array(
                            'label' => trim($value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres),
                            'id'    => $value->id,
                            'value' => trim($value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres),
                        );
            }
        }
        return json_encode($data);
    }
}
