<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Aperturacierrecaja;
use App\Detallemovcaja;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AperturacierrecajaController extends Controller
{
    protected $folderview      = 'app.aperturacierrecaja';
    protected $tituloAdmin     = 'Caja Farmacia';
    protected $tituloRegistrar = 'Registrar caja';
    protected $tituloModificar = 'Modificar caja';
    protected $tituloEliminar  = 'Eliminar caja';
    protected $rutas           = array('create' => 'aperturacierrecaja.create', 
            'edit'   => 'aperturacierrecaja.edit', 
            'delete' => 'aperturacierrecaja.eliminar',
            'search' => 'aperturacierrecaja.buscar',
            'index'  => 'aperturacierrecaja.index',
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
        $entidad          = 'Aperturacierrecaja';
        $fechainicio             = Libreria::getParam($request->input('fechainicio'));
        $fechafin             = Libreria::getParam($request->input('fechafin'));
        $resultado        = Aperturacierrecaja::where(function($query) use ($fechainicio,$fechafin){   
                                if (!is_null($fechainicio) && $fechainicio !== '') {
                                    $begindate   = Date::createFromFormat('d/m/Y', $fechainicio)->format('Y-m-d');
                                    $query->where('fechainicio', '>=', $fechainicio);
                                }
                                if (!is_null($fechafin) && $fechafin !== '') {
                                    $enddate   = Date::createFromFormat('d/m/Y', $fechafin)->format('Y-m-d');
                                    $query->where('fechainicio', '<=', $fechafin);
                                }
                            })->orderBy('fechainicio', 'DESC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha apertura', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha cierre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Monto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado', 'numero' => '1');
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
        $entidad          = 'Aperturacierrecaja';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    public function abrir(Request $request)
    {
        $error = DB::transaction(function() use($request){
            $user = Auth::user();
            $aperturacierrecaja = new Aperturacierrecaja();
            $aperturacierrecaja->fechainicio = date('Y-m-d H:i:s');
            $aperturacierrecaja->montoapertura = 0;
            $aperturacierrecaja->estado = 'A';
            $aperturacierrecaja->person_id = $user->person_id;
            $aperturacierrecaja->save();

        });
        return is_null($error) ? "OK" : $error;
        
    }

    public function cerrar(Request $request)
    {
        $error = DB::transaction(function() use($request){
            $amount= 0;
            $user = Auth::user();
            $aperturacierrecaja = Aperturacierrecaja::where('estado','=','A')->first();
            $list = Detallemovcaja::where('aperturacierrecaja_id','=',$aperturacierrecaja->id)->get();
            foreach ($list as $key => $value) {
                $amount = $amount+$value->moviemiento->total;
            }
            $aperturacierrecaja->fechafin = date('Y-m-d H:i:s');
            $aperturacierrecaja->montocierre = $amount;
            $aperturacierrecaja->estado = 'C';
            $aperturacierrecaja->save();

        });
        return is_null($error) ? "OK" : $error;
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
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
