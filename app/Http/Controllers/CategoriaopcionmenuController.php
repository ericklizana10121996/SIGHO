<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Menuoptioncategory;
use App\Rolpersona;
use App\Movimientoalmacen;
use App\Detallemovimiento;
use App\Producto;
use App\Lote;
use App\Kardex;
use App\Librerias\Libreria;
use App\Librerias\phpJson;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;
use Illuminate\Support\Facades\Auth;

ini_set('memory_limit', '512M'); //Raise to 512 MB
ini_set('max_execution_time', '60000'); //Raise to 512 MB 

class CategoriaopcionmenuController extends Controller
{
    protected $folderview      = 'app.categoriaopcionmenu';
    protected $tituloAdmin     = 'Categoría opción menú';
    protected $tituloRegistrar = 'Registrar categoría';
    protected $tituloModificar = 'Modificar categoría';
    protected $tituloEliminar  = 'Eliminar categoría';
    protected $rutas           = array('create' => 'categoriaopcionmenu.create', 
            'edit'   => 'categoriaopcionmenu.edit', 
            'delete' => 'categoriaopcionmenu.eliminar',
            'search' => 'categoriaopcionmenu.buscar',
            'index'  => 'categoriaopcionmenu.index',
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
        $entidad          = 'Categoriaopcionmenu';
        $name             = Libreria::getParam($request->input('name'));
        $resultado        = Menuoptioncategory::listar($name);
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Orden', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Categoria', 'numero' => '1');
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
        $entidad          = 'Categoriaopcionmenu';
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
        $entidad             = 'Categoriaopcionmenu';
        $cboCategoria        = [''=>'Seleccione una categoría'] + Menuoptioncategory::lists('name', 'id')->all();
        $categoriaopcionmenu = null;
        $formData            = array('categoriaopcionmenu.store');
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('categoriaopcionmenu', 'formData', 'entidad', 'boton', 'cboCategoria', 'listar'));
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
        $validacion = Validator::make($request->all(),
            array(
                'name'                  => 'required|max:60',
                'menuoptioncategory_id' => 'integer|integer|exists:menuoptioncategory,id,deleted_at,NULL',
                'order'                 => 'required|integer',
                'icon'                  => 'required'
                )
            );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $categoriaopcionmenu                        = new Menuoptioncategory();
            $categoriaopcionmenu->name                  = $request->input('name');
            $categoriaopcionmenu->order                 = $request->input('order');
            $categoriaopcionmenu->icon                  = $request->input('icon');
            $categoriaopcionmenu->menuoptioncategory_id = Libreria::obtenerParametro($request->input('menuoptioncategory_id'));
            $categoriaopcionmenu->save();
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
        $existe = Libreria::verificarExistencia($id, 'menuoptioncategory');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $categoriaopcionmenu = Menuoptioncategory::find($id);
        $entidad             = 'Categoriaopcionmenu';
        $cboCategoria        = [''=>'Seleccione una categoría'] + Menuoptioncategory::where('id', '<>', $id)->lists('name', 'id')->all();
        $formData            = array('categoriaopcionmenu.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('categoriaopcionmenu', 'formData', 'entidad', 'boton', 'cboCategoria', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'menuoptioncategory');
        if ($existe !== true) {
            return $existe;
        }
        $validacion = Validator::make($request->all(),
            array(
                'name'                  => 'required|max:60',
                'menuoptioncategory_id' => 'integer|exists:menuoptioncategory,id,deleted_at,NULL',
                'order'                 => 'required|integer',
                'icon'                  => 'required'
                )
            );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $categoriaopcionmenu                        = Menuoptioncategory::find($id);
            $categoriaopcionmenu->name                  = $request->input('name');
            $categoriaopcionmenu->order                 = $request->input('order');
            $categoriaopcionmenu->icon                  = $request->input('icon');
            $categoriaopcionmenu->menuoptioncategory_id = Libreria::obtenerParametro($request->input('menuoptioncategory_id')); 
            $categoriaopcionmenu->save();

            /*$movimientoalmacen                 = new Movimientoalmacen();
            $movimientoalmacen->tipodocumento_id          = $request->input('tipodocumento_id');
            $movimientoalmacen->tipomovimiento_id          = 5;
            $movimientoalmacen->almacen_id          = 1;
            //$movimientoalmacen->persona_id = $request->input('person_id');
            $movimientoalmacen->comentario   = 'stock de prueba';
            $movimientoalmacen->numero = '00005';
            $movimientoalmacen->fecha  = '2017-09-13';
            $movimientoalmacen->total = 0;
            
            
            $user = Auth::user();
            $movimientoalmacen->responsable_id = $user->id;
            $movimientoalmacen->save();

            $listproductos = Producto::where(DB::raw('1'),'=','1')->get();
            foreach ($listproductos as $key => $value) {
                $cantidad  = 100;
                $precio    = 5;
                $subtotal  = 500;
                $detalleVenta = new Detallemovimiento();
                $detalleVenta->cantidad = $cantidad;
                $detalleVenta->precio = $precio;
                $detalleVenta->subtotal = $subtotal;
                $detalleVenta->movimiento_id = $movimientoalmacen->id;
                $detalleVenta->producto_id = $value->id;
                $detalleVenta->save();
                
                $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $value->id)->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                //$ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->where('promarlab_id', '=', $lista[$i]['promarlab_id'])->where('kardex.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();

                // Creamos el lote para el producto
                $lote = new Lote();
                $lote->nombre  = 'prueba';
                $lote->fechavencimiento  = '2017-12-31';
                $lote->cantidad = 100;
                $lote->queda = 100;
                $lote->producto_id = $value->id;
                $lote->almacen_id = 1;
                $lote->save();
                
                $stockanterior = 0;
                $stockactual = 0;
                // ingresamos nuevo kardex
                if ($ultimokardex === NULL) {
                    $stockactual = 100;
                    $kardex = new Kardex();
                    $kardex->tipo = 'I';
                    $kardex->fecha = '2017-12-31';
                    $kardex->stockanterior = $stockanterior;
                    $kardex->stockactual = $stockactual;
                    $kardex->cantidad = $cantidad;
                    $kardex->preciocompra = 5;
                    //$kardex->almacen_id = 1;
                    $kardex->detallemovimiento_id = $detalleVenta->id;
                    $kardex->lote_id = $lote->id;
                    $kardex->save();
                    
                }else{
                    $stockanterior = $ultimokardex->stockactual;
                    $stockactual = $ultimokardex->stockactual+100;
                    $kardex = new Kardex();
                    $kardex->tipo = 'I';
                    $kardex->fecha = '2017-12-31';
                    $kardex->stockanterior = $stockanterior;
                    $kardex->stockactual = $stockactual;
                    $kardex->cantidad = $cantidad;
                    $kardex->preciocompra = $precio;
                    //$kardex->almacen_id = 1;
                    $kardex->detallemovimiento_id = $detalleVenta->id;
                    $kardex->lote_id = $lote->id;
                    $kardex->save();    

                }
            }*/

            /*$storage_path = storage_path();
            $url = $storage_path.'/archivo.txt';
            $file = fopen($url, "w");
            fwrite($file, "Esto es una nueva linea de texto" . PHP_EOL);
            fwrite($file, "Otra más" . PHP_EOL);
            fclose($file);*/

            /*$arr_clientes = array('nombre'=> 'Jose', 'edad'=> '20', 'genero'=> 'masculino','email'=> 'correodejose@dominio.com', 'localidad'=> 'Madrid', 'telefono'=> '91000000');
            //Creamos el JSON
            $phpjson = new phpJson();
            $json_string = $phpjson->encode($arr_clientes);
            $storage_path = storage_path();
            $url = $storage_path.'/clientes.json';
            //$file = 'clientes.json';
            file_put_contents($url, $json_string);

            
            //Leemos el JSON
            $datos_clientes = file_get_contents($url);
            //$json_clientes = json_decode($datos_clientes, true);
            $json_clientes = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $datos_clientes), true );

            foreach ($json_clientes as $cliente) {
                
                echo $cliente."<br>";
            }*/

            /*$resultado = Rolpersona::join('person','rolpersona.person_id','=','person.id')
                            ->where('rol_id','=','3')->whereNull('person.deleted_at')->orderBy('person.bussinesname', 'ASC')->select('rolpersona.*');
            $lista     = $resultado->get();
            $data      = array();
            foreach ($lista as $key => $value) {
                $data[] = array(
                                'label' => $value->person->nombres.' '.$value->person->apellidopaterno.' '.$value->person->apellidomaterno,
                                'id'    => $value->person->id,
                                'value' => $value->person->nombres.' '.$value->person->apellidopaterno.' '.$value->person->apellidomaterno,
                            );
                
            }
            $phpjson = new phpJson();
            $json_string = $phpjson->encode($data);
            $storage_path = storage_path();
            $url = $storage_path.'/clientes.json';
            file_put_contents($url, $json_string);*/
            /*
            $storage_path = storage_path();
            $url = $storage_path.'/clientes.json';
            $datos_clientes = file_get_contents($url);
            //$json_clientes = json_decode($datos_clientes, true);
            $json_clientes = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $datos_clientes), true );

            foreach ($json_clientes as $cliente) {
                
                echo var_dump($cliente)."<br>";
            }*/

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
        $existe = Libreria::verificarExistencia($id, 'menuoptioncategory');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $categoriaopcionmenu = Menuoptioncategory::find($id);
            $categoriaopcionmenu->delete();
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
        $existe = Libreria::verificarExistencia($id, 'menuoptioncategory');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Menuoptioncategory::find($id);
        $entidad  = 'Categoriaopcionmenu';
        $formData = array('route' => array('categoriaopcionmenu.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
