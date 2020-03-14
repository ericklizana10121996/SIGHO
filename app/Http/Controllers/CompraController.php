<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Compra;
use App\Producto;
use App\Distribuidora;
use App\Tipodocumento;
use App\Detallemovimiento;
use App\Kardex;
use App\Movimiento;
use App\Detallemovcaja;
use App\Lote;
use App\Stock;
use App\Cuenta;
use App\Person;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;

ini_set('memory_limit', '512M'); //Raise to 512 MB
ini_set('max_execution_time', '60000'); //Raise to 512 MB 

class CompraController extends Controller
{
    protected $folderview      = 'app.compra';
    protected $tituloAdmin     = 'Compras';
    protected $tituloRegistrar = 'Registrar Compra';
    protected $tituloModificar = 'Modificar Compra';
    protected $tituloVer       = 'Ver Compra';
    protected $tituloEliminar  = 'Eliminar Compra';
    protected $rutas           = array('create' => 'compra.create', 
            'create2'  => 'compra.create2',
            'edit'   => 'compra.edit',
            'show'   => 'compra.show', 
            'delete' => 'compra.eliminar',
            'search' => 'compra.buscar',
            'index'  => 'compra.index',
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entidad          = 'Compra';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        $cboTipoDoc = array('0'=>'Todos...');
        $rs = Tipodocumento::where('tipomovimiento_id','=','3')->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboTipoDoc = $cboTipoDoc + array($value->id => $value->nombre);
        }

        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta','cboTipoDoc','user'));
    }

    public function buscarproducto()
    {
        $entidad          = 'Producto';
        $title            = 'Agregar Productos';
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipo          = array("" => "Todos","P" => "Producto", "I" => "Insumo", "O" => "Otros");  
        return view($this->folderview.'.producto')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipo'));
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
        $entidad          = 'Compra';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $fechainicio             = Libreria::getParam($request->input('fechainicio'));
        $fechafin             = Libreria::getParam($request->input('fechafin'));
        $tipodoc             = Libreria::getParam($request->input('tipodocumento'));
        $resultado        = Compra::join('person','movimiento.persona_id','=','person.id')
                                ->leftJoin('tipodocumento','movimiento.tipodocumento_id','=','tipodocumento.id')
                                ->where('movimiento.tipomovimiento_id', '=', '3')->where(function($query) use ($nombre){
                    if (!is_null($nombre) && $nombre !== '') {
                        
                        $query->where('person.bussinesname', 'LIKE', '%'.strtoupper($nombre).'%');
                    }

        })->where(function($query) use ($fechainicio,$fechafin){   
                                if (!is_null($fechainicio) && $fechainicio !== '') {
                                    $begindate   = Date::createFromFormat('d/m/Y', $fechainicio)->format('Y-m-d');
                                    $query->where('movimiento.fecha', '>=', $begindate);
                                }
                                if (!is_null($fechafin) && $fechafin !== '') {
                                    $enddate   = Date::createFromFormat('d/m/Y', $fechafin)->format('Y-m-d');
                                    $query->where('movimiento.fecha', '<=', $enddate);
                                }
                            });
        if($tipodoc>0){
            $resultado->where("movimiento.tipodocumento_id",$tipodoc);
        }
        $resultado->where(DB::raw('concat(movimiento.serie,\'-\',movimiento.numero)'),'like','%'.$request->input('numero').'%')->orderBy('movimiento.fecha','DESC')->select('movimiento.*','tipodocumento.nombre as tipodoc');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Proveedor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Doc', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_ver  = $this->tituloVer;
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'titulo_ver', 'ruta', 'user'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    public function listarproducto(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Producto';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Producto::where('nombre', 'LIKE', '%'.strtoupper($nombre).'%')->where(function ($query) use($request){
                        if ($request->input('tipo') !== null && $request->input('tipo') !== '') {
                            $query->where('tipo', '=', $request->input('tipo'));
                        }
                    })->orderBy('nombre', 'ASC');
        $lista            = $resultado->get();
        //$cboDistribuidora        = Distribuidora::lists('nombre', 'id')->all();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Principio Activo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Clasificacion', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Forma', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Presentacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Stock', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Precio Kayros', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Precio Compra', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Precio Venta', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cantidad', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Venc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Lote', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Distribuidora', 'numero' => '1');
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
            return view($this->folderview.'.listproducto')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta','cboDistribuidora'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    public function agregarcarritocompra(Request $request)
    {
        $lista = array();
        if($request->input('detalle')=='false'){
            $request->session()->put('carritocompra', $lista);
        }
        $cadena = '';
        if ($request->session()->get('carritocompra') !== null) {
            $lista          = $request->session()->get('carritocompra');
            $cantidad       = Libreria::getParam($request->input('cantidad'));
            $producto_id       = Libreria::getParam($request->input('producto_id'));
            $precio       = Libreria::getParam($request->input('precio'));
            $precio = str_replace( ',', '', $precio);
            $preciokayros       = Libreria::getParam($request->input('preciokayros'));
            $precioventa       = Libreria::getParam($request->input('precioventa'));
            $precioventa = str_replace( ',', '', $precioventa);
            $fechavencimiento       = Libreria::getParam($request->input('fechavencimiento'));
            $lote       = Libreria::getParam($request->input('lote'));
            $distribuidora_id       = Libreria::getParam($request->input('distribuidora_id'));
            $producto   = Producto::find($producto_id);
            $estaPresente   = false;
            $indicepresente = '';
            for ($i=0; $i < count($lista); $i++) { 
                if ($lista[$i]['producto_id'] == $producto_id) {
                    $estaPresente   = true;
                    $indicepresente = $i;
                }
            }
            if ($estaPresente === true) {
                $lista[$indicepresente]  = array('cantidad' => $cantidad, 'precio' => $precio, 'productonombre' => $producto->nombre,'producto_id' => $producto_id,'fechavencimiento' => $fechavencimiento,'lote' => $lote,'distribuidora_id' => $distribuidora_id, 'codigobarra' => $producto->codigobarra, 'preciokayros' => $preciokayros, 'precioventa' => $precioventa);
            }else{
                $lista[]  = array('cantidad' => $cantidad, 'precio' => $precio, 'productonombre' => $producto->nombre,'producto_id' => $producto_id,'fechavencimiento' => $fechavencimiento,'lote' => $lote,'distribuidora_id' => $distribuidora_id, 'codigobarra' => $producto->codigobarra, 'preciokayros' => $preciokayros, 'precioventa' => $precioventa);
            }
            
            $cadena   .= '<table style="width: 100%;" border="1">';
            $cadena   .= '<thead>
                                <tr>
                                    <th bgcolor="#E0ECF8" class="text-center">Producto</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Cantidad</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Precio</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Subtotal</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Quitar</th>                            
                                </tr>
                            </thead>';
            
            $total = 0;
            
            for ($i=0; $i < count($lista); $i++) {
                $subtotal = round(($lista[$i]['cantidad']*$lista[$i]['precio']), 2);
                $total    += $subtotal;
                $cadena   .= '<tr><td class="text-center" style="width:750px;">'.$lista[$i]['productonombre'].'</td>';
                $cadena   .= '<td class="text-center" style="width:100px;">'.$lista[$i]['cantidad'].'</td>';
                $cadena   .= '<td class="text-center" style="width:100px;">'.$lista[$i]['precio'].'</td>';
                $cadena   .= '<td class="text-center" style="width:90px;">'.$subtotal.'</td>';
                $cadena   .= '<td class="text-center"><a class="btn btn-xs btn-danger" onclick="quitar(\''.$i.'\');">Quitar</a></td></tr>';
            }
            $cadena  .= '<tr><th colspan="3" style="text-align: right;">TOTAL</th><td class="text-center">'.$total.'<input type ="hidden" id="totalcompra" readonly=""  name="totalcompra" value="'.$total.'"></td></tr></tr>';
            $cadena .= '</table>';
            $request->session()->put('carritocompra', $lista);

        }else{
            $cantidad       = Libreria::getParam($request->input('cantidad'));
            $producto_id       = Libreria::getParam($request->input('producto_id'));
            $precio       = Libreria::getParam($request->input('precio'));
            $preciokayros       = Libreria::getParam($request->input('preciokayros'));
            $precioventa       = Libreria::getParam($request->input('precioventa'));
            $producto   = Producto::find($producto_id);
            $fechavencimiento       = Libreria::getParam($request->input('fechavencimiento'));
            $lote       = Libreria::getParam($request->input('lote'));
            $distribuidora_id       = Libreria::getParam($request->input('distribuidora_id'));
            $subtotal       = round(($cantidad*$precio), 2);
            $cadena   .= '<table style="width: 100%;" border="1">';
            $cadena   .= '<thead>
                                <tr>
                                    <th bgcolor="#E0ECF8" class="text-center">Producto</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Cantidad</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Precio</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Subtotal</th>
                                    <th bgcolor="#E0ECF8" class="text-center">Quitar</th>                            
                                </tr>
                            </thead>';
            $cadena         .= '<tr><td class="text-center" style="width:550px;">'.$producto->nombre.'</td>';
            $cadena         .= '<td class="text-center" style="width:100px;">'.$cantidad.'</td>';
            $cadena         .= '<td class="text-center" style="width:100px;">'.$precio.'</td>';
            $cadena         .= '<td class="text-center" style="width:90px;">'.$subtotal.'</td>';
            $cadena         .= '<td class="text-center"><a class="btn btn-xs btn-danger" onclick="quitar(\'0\');">Quitar</a></td><tr>';
            $cadena         .= '<tr><th colspan="3" style="text-align: right;">TOTAL</th><td class="text-center">'.$subtotal.'<input type ="hidden" id="totalcompra" readonly=""  name="totalcompra" value="'.$subtotal.'"></td></tr></tr>';
            $cadena         .= '</table>';
            $lista[]  = array('cantidad' => $cantidad, 'precio' => $precio, 'productonombre' => $producto->nombre,'producto_id' => $producto_id,'fechavencimiento' => $fechavencimiento,'lote' => $lote,'distribuidora_id' => $distribuidora_id, 'codigobarra' => $producto->codigobarra, 'preciokayros' => $preciokayros, 'precioventa' => $precioventa);
            $request->session()->put('carritocompra', $lista);
        }
        return $cadena; 


    }

    public function quitarcarritocompra(Request $request)
    {
        $id       = $request->input('valor');
        $cantidad = count($request->session()->get('carritocompra'));
        $lista2   = $request->session()->get('carritocompra');
        $lista    = array();
        $producto_id = '';
        for ($i=0; $i < $cantidad; $i++) {
            if ($i != $id) {
                $lista[] = $lista2[$i];
            }else{
                $producto_id = $lista2[$i]['producto_id'];
            }
        }
        $cadena   = '<table style="width: 100%;" border="1">';
        $cadena   .= '<thead>
                            <tr>
                                <th bgcolor="#E0ECF8" class="text-center">Producto</th>
                                <th bgcolor="#E0ECF8" class="text-center">Cantidad</th>
                                <th bgcolor="#E0ECF8" class="text-center">Precio</th>
                                <th bgcolor="#E0ECF8" class="text-center">Subtotal</th>
                                <th bgcolor="#E0ECF8" class="text-center">Quitar</th>                            
                            </tr>
                        </thead>';
            
            $total = 0;
            
            for ($i=0; $i < count($lista); $i++) {
                $subtotal = round(($lista[$i]['cantidad']*$lista[$i]['precio']), 2);
                $total    += $subtotal;
                $cadena   .= '<tr><td class="text-center" style="width:750px;">'.$lista[$i]['productonombre'].'</td>';
                $cadena   .= '<td class="text-center" style="width:100px;">'.$lista[$i]['cantidad'].'</td>';
                $cadena   .= '<td class="text-center" style="width:100px;">'.$lista[$i]['precio'].'</td>';
                $cadena   .= '<td class="text-center" style="width:90px;">'.$subtotal.'</td>';
                $cadena   .= '<td class="text-center"><a class="btn btn-xs btn-danger" onclick="quitar(\''.$i.'\');">Quitar</a></td></tr>';
            }
            $cadena  .= '<tr><th colspan="3" style="text-align: right;">TOTAL</th><td class="text-center">'.$total.'<input type ="hidden" id="totalcompra" readonly=""  name="totalcompra" value="'.$total.'"></td></tr></tr>';
            $cadena .= '</table>';
        $request->session()->put('carritocompra', $lista);
        return  $cadena;
    }

    public function comprobarproducto(Request $request)
    {

        $lista   = $request->session()->get('carritocompra');
        $valor = Libreria::obtenerParametro($request->input('valor'));
        $resp = "NO";
        for ($i=0; $i < count($lista); $i++) {
            //echo $lista[$i]['codigobarra'].'-'.$valor;
            if ($valor == $lista[$i]['codigobarra']) {
                $resp = "SI";
                break;
            }
        }

        return $resp;

    }

    public function calculartotal(Request $request)
    {

        
        $lista   = $request->session()->get('carritocompra');
        
        $total  = 0;
        for ($i=0; $i < count($lista); $i++) {
                $subtotal = round(($lista[$i]['cantidad']*$lista[$i]['precio']), 2);
                $total    += $subtotal;
        }
       
        return  $total;
    }

    public function generarcreditos(Request $request)
    {

        
        $lista          = array();
        $cadena         = '';
        $saldototal     = Libreria::obtenerParametro($request->input('saldototal'));
        $saldototal     = str_replace(',', '', $saldototal);
        $nrocuotas      = Libreria::obtenerParametro($request->input('nrocuotas')); 
        $nrocuotas      = str_replace(',', '', $nrocuotas);
        $fecha          = Libreria::obtenerParametro($request->input('primerpago'));
        $diasentrecuotas      = Libreria::obtenerParametro($request->input('diasentrecuotas'));
        
       
        $detallecredito = 'Cuota';
        $cadena         .= '<table class="table-bordered table-hover" style="width: 100%">';
        $cadena         .= '<thead><tr><th bgcolor="#E0ECF8" class="text-center">Detalle</th><th bgcolor="#E0ECF8" class="text-center">Fecha</th><th bgcolor="#E0ECF8" class="text-center">Monto</th></tr></thead><tbody>';
        $nrocredito     = 0;
        $calendarios    = 0;
        
        if ($nrocredito === 0) {
            $nrocredito = 1;
        }
        $total    = 0;
        $cuota    = round(($saldototal / $nrocuotas), 2);
        $detalle2 = '';
        for ($i=0; $i < $nrocuotas; $i++) {
            //$detalle2      = ($contrato->tipo === 'N') ? 'L/. '.$nrocredito.' de '.$nrocuotas : 'C/. '.$nrocredito.' de '.$nrocuotas ;
            $detalle2 = 'C/. '.$nrocredito.' de '.$nrocuotas ;
            
            $fecha1        = Date::createFromFormat('d/m/Y', $fecha)->format('Y-m-d'); 
            $nuevafecha = strtotime ( '+'.$diasentrecuotas.' day' , strtotime ( $fecha1 ) ) ;
            $nuevafecha = date( 'd/m/Y' , $nuevafecha );
            $fecha    = $nuevafecha;
            $lista[]       = array('fecha' => $nuevafecha, 'monto' => $cuota, 'nrocredito' => $nrocredito, 'nombre' => $detallecredito.' N° '.$nrocredito, 'detallecredito' => $detalle2);
            $nrocredito++;
        }
        $letra = 1;
        
        for ($i=0; $i < $nrocuotas; $i++) {
            $cadena .= '<tr><td class="text-center">'.$detallecredito.' N° '.$letra.'</td>';
            $cadena .= '<td class="text-center"><input name="fechaspago[]" id="calendario'.$calendarios.'" type="text" size="8" value="'.$lista[$i]['fecha'].'"></td>';
            $cadena .= '<td class="text-center"><input name="montocuota[]" id="montocuota'.$calendarios.'" type="text" size="8" value="'.$lista[$i]['monto'].'"></td>';
            $cadena .= '</tr>';
            $calendarios ++;
            $letra++;
        }
        $cadena .= '</tbody></table>';
        $cadena .= '<script>$(document).ready(function() {';
        for ($i=0; $i < $calendarios; $i++) {
            $cadena .= '$(IDFORMMANTENIMIENTO + \'Compra\' + \' :input[id="montocuota'.$i.'"]\').inputmask("decimal", { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });';
            $cadena .= '$(IDFORMMANTENIMIENTO + \'Compra\' + \' :input[id="calendario'.$i.'"]\').inputmask("dd/mm/yyyy");';
            $cadena .= '$(IDFORMMANTENIMIENTO + \'Compra\' + \' :input[id="calendario'.$i.'"]\').datetimepicker({ pickTime: false, language: "es"});';
        }
        $cadena .= '});</script>';
        $request->session()->put('carritocredito', $lista);
        return $cadena;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Compra';
        $compra = null;
        //$cboDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $cboDocumento = array();
        $listdocument = Tipodocumento::where('tipomovimiento_id','=','3')->get();
        foreach ($listdocument as $key => $value) {
            $cboDocumento = $cboDocumento + array( $value->id => $value->nombre);
        }
        $cboCredito        = array('S' => 'SI', "N" => 'NO');
        $cboCajafarmacia        = array("N" => 'NO', 'S' => 'SI');
        $cboAfecto        = array('S' => 'SI', "N" => 'NO');
        $formData = array('compra.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        $request->session()->forget('carritocompra');
        $lista = array();
        $request->session()->put('carritocompra', $lista);
        return view($this->folderview.'.mant')->with(compact('compra', 'formData', 'entidad', 'boton', 'listar','cboDocumento','cboCredito','cboCajafarmacia','cboAfecto'));
    }

    public function create2(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Compra';
        $compra = null;
        //$cboDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $cboDocumento = array();
        $listdocument = Tipodocumento::where('tipomovimiento_id','=','3')->get();
        foreach ($listdocument as $key => $value) {
            $cboDocumento = $cboDocumento + array( $value->id => $value->nombre);
        }
        $cboCredito        = array('S' => 'SI', "N" => 'NO');
        $cboCajafarmacia        = array("N" => 'NO', 'S' => 'SI');
        $cboAfecto        = array('S' => 'SI', "N" => 'NO');
        $formData = array('compra.store2');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        $request->session()->forget('carritocompra');
        $lista = array();
        $request->session()->put('carritocompra', $lista);
        return view($this->folderview.'.mant2')->with(compact('compra', 'formData', 'entidad', 'boton', 'listar','cboDocumento','cboCredito','cboCajafarmacia','cboAfecto'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request);

        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                'person_id' => 'required|integer|exists:person,id,deleted_at,NULL',
                'numerodocumento'                  => 'required',
                'fecha'                 => 'required'
                );
        $mensajes = array(
            'person_id.required'         => 'Debe ingresar un proveedor',
            'numerodocumento.required'         => 'Debe ingresar un numero de documento',
            'fecha.required'         => 'Debe ingresar fecha'
            );
        

        if (is_null($request->session()->get('carritocompra')) || count($request->session()->get('carritocompra')) === 0) {
            $error = array(
                'total' => array(
                    'Debe agregar al menos un producto'
                    ));
            return json_encode($error);
        }

        if ($request->input('credito') !== NULL && $request->input('credito') !== '' ) {
            if ( ($request->input('numerodias') === '' || $request->input('numerodias') === null ) && $request->input('credito') === 'S') {
                $error = array(
                    'numerodias' => array(
                        'Debe ingresar numerodias de factura'
                        )
                    );
                return json_encode($error);
            }
        }

        

        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }

        $dat=array();

        $error = DB::transaction(function() use($request,&$dat){
            $lista = $request->session()->get('carritocompra');
            $total = str_replace(',', '', $request->input('total'));
            $igv = str_replace(',', '', $request->input('igv'));
            $compra                 = new Compra();
            $compra->tipodocumento_id          = $request->input('tipodocumento_id');
            $compra->tipomovimiento_id          = 3;
            $compra->persona_id = $request->input('person_id');
            $compra->serie = $request->input('serie');
            $compra->numero = $request->input('numerodocumento');
            $compra->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
            $compra->total = $total;
            $compra->igv = $igv;
            $compra->subtotal = $total - $igv;
            $compra->credito = $request->input('credito');
            $compra->inicial = 'N';
            $compra->estadopago = 'P';
            if ($request->input('credito') == 'S') {
                $compra->numerodias = Libreria::obtenerParametro($request->input('numerodias'));
                $compra->estadopago = 'PP';
            }
            
            $user = Auth::user();
            $compra->responsable_id = $user->person_id;
            $compra->doctor_id = Libreria::obtenerParametro($request->input('doctor_id'));
            $compra->numeroficha = Libreria::obtenerParametro($request->input('numeroficha'));
            $compra->cajaprueba = $request->input('cajafamarcia');
            $compra->almacen_id = 1;
            $compra->fechaingreso  = Date::createFromFormat('d/m/Y', $request->input('fecha2'))->format('Y-m-d');
            $compra->save();

            $tipodoc_id = $request->input('tipodocumento_id');

            $movimiento_id = $compra->id;
            for ($i=0; $i < count($lista); $i++) {
                $cantidad  = str_replace(',', '',$lista[$i]['cantidad']);
                $precio    = str_replace(',', '',$lista[$i]['precio']);
                $subtotal  = round(($cantidad*$precio), 2);
                if($tipodoc_id==11){
                    $cantidad = floatval($cantidad) * -1;
                }
                $detalleCompra = new Detallemovimiento();
                $detalleCompra->cantidad = $cantidad;
                $detalleCompra->precio = $precio;
                $detalleCompra->subtotal = $subtotal;
                $detalleCompra->movimiento_id = $movimiento_id;
                $detalleCompra->producto_id = $lista[$i]['producto_id'];
                $detalleCompra->save();
                $producto = Producto::find($lista[$i]['producto_id']);
                $producto->preciocompra = str_replace(',', '',$lista[$i]['precio']);
                $producto->precioventa = str_replace(',', '',$lista[$i]['precioventa']);
                $producto->preciokayros = str_replace(',', '',$lista[$i]['preciokayros']);
                $producto->save();
                $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $lista[$i]['producto_id'])->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                //$ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->where('promarlab_id', '=', $lista[$i]['promarlab_id'])->where('kardex.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();

                // Creamos el lote para el producto
                $lote = new Lote();
                $lote->nombre  = $lista[$i]['lote'];
                $lote->fechavencimiento  = Date::createFromFormat('d/m/Y', $lista[$i]['fechavencimiento'])->format('Y-m-d');
                $lote->cantidad = $cantidad;
                $lote->queda = $cantidad;
                $lote->producto_id = $lista[$i]['producto_id'];
                $lote->almacen_id = 1;
                $lote->save();

                $stockanterior = 0;
                $stockactual = 0;
                // ingresamos nuevo kardex
                if ($ultimokardex === NULL) {
                    $stockactual = $cantidad;
                    $kardex = new Kardex();
                    $kardex->tipo = 'I';
                    $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                    $kardex->stockanterior = $stockanterior;
                    $kardex->stockactual = $stockactual;
                    $kardex->cantidad = $cantidad;
                    $kardex->preciocompra = $precio;
                    //$kardex->almacen_id = 1;
                    $kardex->detallemovimiento_id = $detalleCompra->id;
                    $kardex->lote_id = $lote->id;
                    $kardex->save();
                    
                }else{
                    $stockanterior = $ultimokardex->stockactual;
                    $stockactual = $ultimokardex->stockactual+$cantidad;
                    $kardex = new Kardex();
                    $kardex->tipo = 'I';
                    $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                    $kardex->stockanterior = $stockanterior;
                    $kardex->stockactual = $stockactual;
                    $kardex->cantidad = $cantidad;
                    $kardex->preciocompra = $precio;
                    //$kardex->almacen_id = 1;
                    $kardex->detallemovimiento_id = $detalleCompra->id;
                    $kardex->lote_id = $lote->id;
                    $kardex->save();    

                }

            }

            # REGISTRO DE CREDITOS
            
            if ($request->input('credito') == 'S') {
                /*$fechaspago = ($request->input('fechaspago') !== NULL) ? str_replace(',', '', $request->input('fechaspago')) : array();
                $montocuota = ($request->input('montocuota') !== NULL) ? str_replace(',', '', $request->input('montocuota')) : array();
                if (count($fechaspago) > 0) {
                    $creditos      = $request->session()->get('carritocredito');
                    for ($j=0; $j < count($fechaspago); $j++) { 
                        $cuenta                   = new Cuenta();
                        $cuenta->fecha            = Date::createFromFormat('d/m/Y', $fechaspago[$j])->format('Y-m-d');
                        $cuenta->nombre           = $creditos[$j]['detallecredito'];
                        $cuenta->monto            = $montocuota[$j];
                        $cuenta->saldo            = $montocuota[$j];
                        $cuenta->tipo             = 'XP';
                        $cuenta->condicion        = 'NP';
                        $cuenta->movimiento_id      = $movimiento_id;
                        $cuenta->save();
                    }
                }
                if (($request->input('inicial') != '0') && ($request->input('inicial') != '')) {
                    
                    $total = str_replace(',', '', $request->input('inicial'));
                    $movimiento                 = new Movimiento();
                    $movimiento->tipodocumento_id          = $request->input('tipodocumento_id');
                    $movimiento->tipomovimiento_id          = 2;
                    $movimiento->persona_id = $request->input('persona_id');
                    $movimiento->numero = $request->input('numerodocumento');
                    $movimiento->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                    $movimiento->total = $total;
                    
                    $user = Auth::user();
                    $movimiento->responsable_id = $user->id;
                    $movimiento->conceptopago_id = 9;
                    if ($request->input('cajafamarcia')  == 'S') {
                        $movimiento->caja_id = 4;
                    }else{

                    }
                    $movimiento->movimiento_id = $compra->id;
                    $movimiento->save();


                    $movimientocaja = new Detallemovcaja();
                    $movimientocaja->persona_id = $request->input('persona_id');
                    $movimientocaja->movimiento_id = $movimiento->id;
                    $movimientocaja->descripcion = 'PAGO A PROVEEDOR';
                    $movimientocaja->save();
                }*/
            }else{

                if ($request->input('cajafamarcia')  == 'S') {
                    $total = str_replace(',', '', $request->input('total'));
                    $movimiento                 = new Movimiento();
                    $movimiento->tipodocumento_id          = $request->input('tipodocumento_id');
                    $movimiento->tipomovimiento_id          = 2;
                    $movimiento->persona_id = $request->input('person_id');
                    $movimiento->serie = $request->input('serie');
                    $movimiento->numero = $request->input('numerodocumento');
                    $movimiento->voucher = $request->input('serie') .'-'.$request->input('numerodocumento');
                    $movimiento->fecha  = date("Y-m-d");
                    $movimiento->total = $total;
                    
                    $user = Auth::user();
                    $movimiento->responsable_id = $user->person_id;
                    $movimiento->conceptopago_id = 9;
                    
                    $movimiento->caja_id = 4;
                    
                    $movimiento->movimiento_id = $compra->id;
                    $movimiento->comentario = 'Compra de Medicamentos con dinero de caja de farmacia';
                    $movimiento->save();


                    $movimientocaja = new Detallemovcaja();
                    $movimientocaja->persona_id = $request->input('person_id');
                    $movimientocaja->movimiento_id = $movimiento->id;
                    $movimientocaja->descripcion = 'PAGO A PROVEEDOR';
                    $movimientocaja->save();
                }
            }

            $dat[0]=array("respuesta"=>"OK","compra_id"=>$compra->id, "ind" => 0, "second_id" => 0);
        });
        return is_null($error) ? json_encode($dat) : $error;

    }

    public function store2(Request $request)
    {
        // dd($request);

        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                'person_id' => 'required|integer|exists:person,id,deleted_at,NULL',
                'numerodocumento'                  => 'required',
                'fecha'                 => 'required'
                );
        $mensajes = array(
            'person_id.required'         => 'Debe ingresar un proveedor',
            'numerodocumento.required'         => 'Debe ingresar un numero de documento',
            'fecha.required'         => 'Debe ingresar fecha'
            );
        
        if ($request->input('credito') !== NULL && $request->input('credito') !== '' ) {
            if ( ($request->input('numerodias') === '' || $request->input('numerodias') === null ) && $request->input('credito') === 'S') {
                $error = array(
                    'numerodias' => array(
                        'Debe ingresar numerodias de factura'
                        )
                    );
                return json_encode($error);
            }
        }

        

        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }

        $dat=array();

        $error = DB::transaction(function() use($request,&$dat){
            $total = str_replace(',', '', $request->input('total'));
            $igv = str_replace(',', '', $request->input('igv'));
            $compra                 = new Compra();
            $compra->tipodocumento_id          = $request->input('tipodocumento_id');
            $compra->tipomovimiento_id          = 3;
            $compra->persona_id = $request->input('person_id');
            $compra->serie = $request->input('serie');
            $compra->numero = $request->input('numerodocumento');
            $compra->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
            $compra->total = $total;
            $compra->igv = $igv;
            $compra->subtotal = $total - $igv;
            $compra->credito = $request->input('credito');
            $compra->inicial = 'N';
            $compra->estadopago = 'P';
            if ($request->input('credito') == 'S') {
                $compra->numerodias = Libreria::obtenerParametro($request->input('numerodias'));
                $compra->estadopago = 'PP';
            }
            
            $user = Auth::user();
            $compra->responsable_id = $user->person_id;
            $compra->doctor_id = Libreria::obtenerParametro($request->input('doctor_id'));
            $compra->numeroficha = Libreria::obtenerParametro($request->input('numeroficha'));
            $compra->cajaprueba = $request->input('cajafamarcia');
            $compra->almacen_id = 1;
            $compra->fechaingreso  = Date::createFromFormat('d/m/Y', $request->input('fecha2'))->format('Y-m-d');
            $compra->save();

            $tipodoc_id = $request->input('tipodocumento_id');

            $movimiento_id = $compra->id;
            $arr=explode(",",$request->input('listProducto'));
            for($c=0;$c<count($arr);$c++){
                $cantidad  = str_replace(',', '',$request->input('txtCantidad'.$arr[$c]));
                $precio    = str_replace(',', '',$request->input('txtPrecio'.$arr[$c]));
                $subtotal  = round(($cantidad*$precio), 2);
                if($tipodoc_id==11){
                    $cantidad = floatval($cantidad) * -1;
                }
                $detalleCompra = new Detallemovimiento();
                $detalleCompra->cantidad = $cantidad;
                $detalleCompra->precio = $precio;
                $detalleCompra->subtotal = $subtotal;
                $detalleCompra->movimiento_id = $movimiento_id;
                $detalleCompra->producto_id = $request->input('txtIdProducto'.$arr[$c]);
                $detalleCompra->fechavencimiento = Date::createFromFormat('d/m/Y', $request->input('txtFechaVencimiento'.$arr[$c]))->format('Y-m-d');
                $detalleCompra->registroSanitario = $request->input('txtRegSanitario'.$arr[$c]);
                
                // txtRegSanitario1545
                $detalleCompra->lote = $request->input('txtLote'.$arr[$c]);
                $detalleCompra->save();

                //Stock
                $stock = Stock::where('producto_id','=',$request->input('txtIdProducto'.$arr[$c]))->where('almacen_id','=',1)->first();
                if(is_null($stock)){
                    $stock = new Stock();
                    $stock->producto_id = $request->input('txtIdProducto'.$arr[$c]);
                    $stock->cantidad = $cantidad;
                    $stock->almacen_id = 1;
                    $stock->save();
                }else{
                    $stock->cantidad = $cantidad + $stock->cantidad;
                    $stock->save();
                }
                //

                $producto = Producto::find($request->input('txtIdProducto'.$arr[$c]));
                $producto->preciocompra = str_replace(',', '',$request->input('txtPrecio'.$arr[$c]));
                $producto->precioventa = str_replace(',', '',$request->input('txtPrecioVenta'.$arr[$c]));
                $producto->preciokayros = str_replace(',', '',$request->input('txtPrecioKayros'.$arr[$c]));
                $producto->condicionAlmac_id = $request->input('hdCondicionAlm'.$arr[$c]);
                $producto->concentracion = $request->input('txtConcentracion'.$arr[$c]);
                $producto->formaFarmac_id = $request->input('hdForma'.$arr[$c]);
                $producto->save();
                $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $request->input('txtIdProducto'.$arr[$c]))->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();
                //$ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->where('promarlab_id', '=', $lista[$i]['promarlab_id'])->where('kardex.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();

                // Creamos el lote para el producto

                
                $lote = new Lote();
                $lote->nombre  = $request->input('txtLote'.$arr[$c]);
                $lote->fechavencimiento  = Date::createFromFormat('d/m/Y', $request->input('txtFechaVencimiento'.$arr[$c]))->format('Y-m-d');
                $lote->cantidad = $cantidad;
                $lote->queda = $cantidad;
                $lote->producto_id = $request->input('txtIdProducto'.$arr[$c]);
                $lote->almacen_id = 1;
                $lote->save();

                $stockanterior = 0;
                $stockactual = 0;
                // ingresamos nuevo kardex
                if ($ultimokardex === NULL) {
                    $stockactual = $cantidad;
                    $kardex = new Kardex();
                    if($tipodoc_id!=11){
                        $kardex->tipo = 'I';
                    }else{
                        $kardex->tipo = 'E';
                    }
                    $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                    $kardex->stockanterior = $stockanterior;
                    $kardex->stockactual = $stockactual;
                    if($tipodoc_id==11){
                        $cantidad = $cantidad*(-1);
                    }
                    $kardex->cantidad = $cantidad;
                    $kardex->preciocompra = $precio;
                    //$kardex->almacen_id = 1;
                    $kardex->detallemovimiento_id = $detalleCompra->id;
                    $kardex->lote_id = $lote->id;
                    $kardex->save();
                    
                }else{
                    $stockanterior = $ultimokardex->stockactual;
                    $stockactual = $ultimokardex->stockactual+$cantidad;
                    $kardex = new Kardex();
                    if($tipodoc_id!=11){
                        $kardex->tipo = 'I';
                    }else{
                        $kardex->tipo = 'E';
                    }
                    $kardex->fecha = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                    $kardex->stockanterior = $stockanterior;
                    $kardex->stockactual = $stockactual;
                    if($tipodoc_id==11){
                       $cantidad = $cantidad*(-1);
                    }
                    $kardex->cantidad = $cantidad;
                    $kardex->preciocompra = $precio;
                    //$kardex->almacen_id = 1;
                    $kardex->detallemovimiento_id = $detalleCompra->id;
                    $kardex->lote_id = $lote->id;
                    $kardex->save();    

                }

            }

            # REGISTRO DE CREDITOS
            
            if ($request->input('credito') == 'S') {
                /*$fechaspago = ($request->input('fechaspago') !== NULL) ? str_replace(',', '', $request->input('fechaspago')) : array();
                $montocuota = ($request->input('montocuota') !== NULL) ? str_replace(',', '', $request->input('montocuota')) : array();
                if (count($fechaspago) > 0) {
                    $creditos      = $request->session()->get('carritocredito');
                    for ($j=0; $j < count($fechaspago); $j++) { 
                        $cuenta                   = new Cuenta();
                        $cuenta->fecha            = Date::createFromFormat('d/m/Y', $fechaspago[$j])->format('Y-m-d');
                        $cuenta->nombre           = $creditos[$j]['detallecredito'];
                        $cuenta->monto            = $montocuota[$j];
                        $cuenta->saldo            = $montocuota[$j];
                        $cuenta->tipo             = 'XP';
                        $cuenta->condicion        = 'NP';
                        $cuenta->movimiento_id      = $movimiento_id;
                        $cuenta->save();
                    }
                }
                if (($request->input('inicial') != '0') && ($request->input('inicial') != '')) {
                    
                    $total = str_replace(',', '', $request->input('inicial'));
                    $movimiento                 = new Movimiento();
                    $movimiento->tipodocumento_id          = $request->input('tipodocumento_id');
                    $movimiento->tipomovimiento_id          = 2;
                    $movimiento->persona_id = $request->input('persona_id');
                    $movimiento->numero = $request->input('numerodocumento');
                    $movimiento->fecha  = Date::createFromFormat('d/m/Y', $request->input('fecha'))->format('Y-m-d');
                    $movimiento->total = $total;
                    
                    $user = Auth::user();
                    $movimiento->responsable_id = $user->id;
                    $movimiento->conceptopago_id = 9;
                    if ($request->input('cajafamarcia')  == 'S') {
                        $movimiento->caja_id = 4;
                    }else{

                    }
                    $movimiento->movimiento_id = $compra->id;
                    $movimiento->save();


                    $movimientocaja = new Detallemovcaja();
                    $movimientocaja->persona_id = $request->input('persona_id');
                    $movimientocaja->movimiento_id = $movimiento->id;
                    $movimientocaja->descripcion = 'PAGO A PROVEEDOR';
                    $movimientocaja->save();
                }*/
            }else{

                if ($request->input('cajafamarcia')  == 'S') {
                    $total = str_replace(',', '', $request->input('total'));
                    $movimiento                 = new Movimiento();
                    $movimiento->tipodocumento_id          = $request->input('tipodocumento_id');
                    $movimiento->tipomovimiento_id          = 2;
                    $movimiento->persona_id = $request->input('person_id');
                    $movimiento->serie = $request->input('serie');
                    $movimiento->numero = $request->input('numerodocumento');
                    $movimiento->voucher = $request->input('serie') .'-'.$request->input('numerodocumento');
                    $movimiento->fecha  = date("Y-m-d");
                    $movimiento->total = $total;
                    
                    $user = Auth::user();
                    $movimiento->responsable_id = $user->person_id;
                    $movimiento->conceptopago_id = 9;
                    
                    $movimiento->caja_id = 4;
                    
                    $movimiento->movimiento_id = $compra->id;
                    $movimiento->comentario = 'Compra de Medicamentos con dinero de caja de farmacia';
                    $movimiento->save();


                    $movimientocaja = new Detallemovcaja();
                    $movimientocaja->persona_id = $request->input('person_id');
                    $movimientocaja->movimiento_id = $movimiento->id;
                    $movimientocaja->descripcion = 'PAGO A PROVEEDOR';
                    $movimientocaja->save();
                }
            }

            $dat[0]=array("respuesta"=>"OK","compra_id"=>$compra->id, "ind" => 0, "second_id" => 0);
        });
        return is_null($error) ? json_encode($dat) : $error;

    }

    public function pdfComprobante(Request $request){
        $entidad          = 'Venta';
        $id               = Libreria::getParam($request->input('movimiento_id'),'');
        $guia = $request->input('guia');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->where('movimiento.id', '=', $id);
        $resultado        = $resultado->select('movimiento.*','tipodocumento.nombre as tipodocumento');
        $resultado = Movimiento::where('id','=',$id);
        $lista            = $resultado->get();
        //print_r(count($lista));
        if (count($lista) > 0) {     
            foreach($lista as $key => $value){
                    $pdf = new TCPDF();
                    $tipodocumento = '';
                    if($value->tipodocumento_id==6){
                        $tipodocumento="FACTURA";
                    }elseif($value->tipodocumento_id==7){
                        $tipodocumento="BOLETA";    
                    }elseif($value->tipodocumento_id==11){
                        $tipodocumento="NOTA DE CREDITO";    
                    }else{
                        $tipodocumento="GUIA"; 
                    }
                    $pdf::SetTitle('Factura de Compra');
                    $pdf::AddPage("L");
                    // $pdf::SetFont('helvetica','BI',14);
                    // $pdf::Cell(265,7,'ANEXO N° 05 ',0,0,'C');
                    // $pdf::Ln();
                    // $pdf::Ln();
                    
                    $pdf::SetFont('helvetica','B',12);
                    $pdf::Cell(70,7,'RAZON SOCIAL        : '.$value->persona->bussinesname,0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(70,7,'Nro RUC        : '.$value->persona->ruc,0,0,'L');
                    $pdf::setX(100);
                    $pdf::Cell(150,7,'FECHA DE INGRESO.   : '.date("d/m/Y  H:i:s",strtotime($value->created_at)),0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(70,7,'DOCUM         : '.utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'L');
                    $pdf::setX(100);
                    $pdf::Cell(150,4,'USUARIO            : '.$value->responsable->nombres,0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(70,7,'TIPO DOC     : '.$tipodocumento,0,0,'L');
                    $pdf::setX(100);
                    $pdf::Cell(150,7,'NRO DIAS           : '.$value->numerodias,0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(70,7,'FECHA DE DOC : '.date("d/m/Y",strtotime($value->fecha)),0,0,'L');
                    $pdf::setX(100);
                    $pdf::Cell(150,7,'FECHA DE VENC.  : '.date("d/m/Y",strtotime($value->fechaingreso)),0,0,'L');
                    $pdf::Ln(10);

                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(50,7,'PRODUCTO',1,0,'L');
                    $pdf::Cell(35,7,'CONCENTRACIÓN',1,0,'C');
                    $pdf::Cell(35,7,'F. FARMACÉUTICA',1,0,'C');
                    $pdf::Cell(12,7,'CANT',1,0,'C');
                    $pdf::Cell(20,7,'PRESENT.',1,0,'L');
                    $pdf::Cell(15,7,'P.UNIT',1,0,'C');
                    $pdf::Cell(15,7,'TOTAL',1,0,'C');
                    $pdf::Cell(15,7,'P.V',1,0,'C');
                    $pdf::Cell(15,7,'P.K',1,0,'C');
                    $pdf::Cell(18,7,'FEC. VEN.',1,0,'C');
                    $pdf::Cell(14,7,'LOTE',1,0,'C');
                    $pdf::Cell(20,7,'REG. SANIT.',1,0,'C');
                    $pdf::Cell(22   ,7,'C. ALMACEN.',1,0,'C');
                    
                    $pdf::Ln();
                    $detalles = Detallemovimiento::where('movimiento_id','=',$value->id)->get();
                    $sumaunitario=0;
                    $sumatotal = 0;
                    $sumapv=0;
                    foreach ($detalles as $key2 => $value2) {
                        // dd($value2);
                        $nombrepresentacion = '';
                        if (is_null($value2->producto->presentacion)) {
                            $auxservice = DB::connection('mysql')->table('presentacion')->where('id','=',$value2->producto->presentacion_id)->whereNotNull('deleted_at')->first();
                            if (!is_null($auxservice)) {
                                $nombrepresentacion = $auxservice->nombre;
                            }
                            
                        }else{
                            $nombrepresentacion = $value2->producto->presentacion->nombre;
                        }
                      
                        $nombreconcentracion  = '';
                        // if ($value2->producto->concentracion_id !== null) {
                        if ($value2->producto->concentracion !== null && $value2->producto->concentracion != '0' ) {
                            $nombreconcentracion = $value2->producto->concentracion;
                        }
                        // }
                        $nombreforma  = '';
                        if ($value2->producto->formaFarmac_id !== null) {
                            if ($value2->producto->formaFarmaceutica !== null) {
                                $nombreforma = $value2->producto->formaFarmaceutica->nombre;
                            }
                        }   
                        $condicionAlmacenamiento = '';
                        if ($value2->producto->condicionAlmac_id !== null) {
                            if ($value2->producto->condicionAlmacenamiento !== null) {
                                $condicionAlmacenamiento = $value2->producto->condicionAlmacenamiento->nombre;
                            }
                        }
                        $pdf::SetFont('helvetica','',7);
                        // $pdf::Cell(50,7,$value2->producto->nombre,1,0,'L');
                        if(strlen($value2->producto->nombre) > 25){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(50,3,$value2->producto->nombre,0,'L');
                            $pdf::SetXY($x,$y);                            
                            $pdf::Cell(50,7,"",1,0,'C');
                    
                        }else{
                            $pdf::Cell(50,7,$value2->producto->nombre,1,0,'L');
                        }
                                                

                        if(strlen($nombreconcentracion) > 25){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(35,3,$nombreconcentracion,0,'L');
                            $pdf::SetXY($x,$y);                            
                            $pdf::Cell(35,7,"",1,0,'C');
                    
                        }else{
                            $pdf::Cell(35,7,$nombreconcentracion,1,0,'L');
                        }
                        
                        if(strlen($nombreforma) > 25){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(35,3,$nombreforma,0,'L');
                            $pdf::SetXY($x,$y);                            
                            $pdf::Cell(35,7,"",1,0,'C');
                    
                        }else{
                            $pdf::Cell(35,7,$nombreforma,1,0,'L');
                        }
                      

                        // $pdf::Cell(35,7,$nombreforma,1,0,'L');
                        $pdf::Cell(12,7,number_format($value2->cantidad,0,'.',''),1,0,'C');
                       
                        if(strlen($nombrepresentacion) > 10){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(20,3,$nombrepresentacion,0,'L');
                            $pdf::SetXY($x,$y);                            
                            $pdf::Cell(20,7,"",1,0,'C');
                    
                        }else{
                            $pdf::Cell(20,7,$nombrepresentacion,1,0,'L');
                        }
                      

                        // $pdf::Cell(20,7,$nombrepresentacion,1,0,'L');
                        $pdf::Cell(15,7,$value2->precio,1,0,'C');
                        $pdf::Cell(15,7,$value2->subtotal,1,0,'C');
                        $pdf::Cell(15,7,$value2->producto->precioventa,1,0,'C');
                        $pdf::Cell(15,7,$value2->producto->preciokayros,1,0,'C');
                        $pdf::Cell(18,7,date("d/m/Y",strtotime($value2->fechavencimiento)),1,0,'C');
                        $pdf::Cell(14,7,$value2->lote,1,0,'C');
                        $pdf::Cell(20,7,$value2->registroSanitario,1,0,'C');
                        

                        if(strlen($condicionAlmacenamiento) > 18){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(22,3,$condicionAlmacenamiento,0,'L');
                            $pdf::SetXY($x,$y);                            
                            $pdf::Cell(22,7,"",1,0,'C');
                    
                        }else{
                            $pdf::Cell(22,7,$condicionAlmacenamiento,1,0,'L');
                        }
                      
                        // $pdf::Cell(22,7,$condicionAlmacenamiento,1,0,'C');
                        
                        $pdf::Ln();
                        $sumaunitario = $sumaunitario+$value2->precio;
                        $sumatotal = $sumatotal+$value2->subtotal;
                        $sumapv = $sumapv+$value2->producto->precioventa;
                    }

                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',12);
                    $pdf::setX(122);
                    $pdf::Cell(15,7,"TOTAL: "/*$sumaunitario*/,0,0,'C');
                    //$pdf::Cell(20,7,$value->total,0,0,'C');
                    $pdf::Cell(20,7,'S/ '.number_format($sumatotal,2,'.',''),0,0,'C');
                    $pdf::Cell(15,7,""/*$sumapv*/,0,0,'C');
                    $pdf::Ln();
                    $pdf::setX(115);
                    $pdf::Cell(15,7,'__________',0,0,'C');
                    $pdf::Cell(20,7,'__________',0,0,'C');
                    $pdf::Cell(15,7,'__________',0,0,'C');
                    $pdf::Ln(2);
                    $pdf::setX(115);
                    $pdf::Cell(15,7,'__________',0,0,'C');
                    $pdf::Cell(20,7,'__________',0,0,'C');
                    $pdf::Cell(15,7,'__________',0,0,'C');

                    
                    /*$pdf::Cell(60,7,'Convenio: '.$value->conveniofarmacia->nombre,0,0,'R');
                    $pdf::Ln();
                    $pdf::Ln();
                    if ($value->persona_id !== NULL) {
                        $pdf::Cell(110,6,(trim("Paciente: ".$value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                    }else{
                        $pdf::Cell(110,6,(trim("Paciente: ".$value->nombrepaciente)),0,0,'L');
                    }
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',12);
                    $pdf::Cell(15,7,("Cant"),1,0,'C');
                    $pdf::Cell(80,7,"Producto",1,0,'C');
                    $pdf::Cell(22,7,("Prec. Unit."),1,0,'C');
                    $pdf::Cell(20,7,("Dscto"),1,0,'C');
                    $pdf::Cell(20,7,("Copago"),1,0,'C');
                    $pdf::Cell(20,7,("Total"),1,0,'C');
                    $pdf::Cell(20,7,("Sin IGV"),1,0,'C');
                    $pdf::SetFont('helvetica','',13);
                    $pdf::Ln();
                    $resultado = Detallemovimiento::where('movimiento_id','=',$id);
                    $lista2            = $resultado->get();
                    $totalpago=0;
                    $totaldescuento=0;
                    $totaligv=0;
                    foreach($lista2 as $key2 => $v){
                        $pdf::Cell(15,12,number_format($v->cantidad,2,'.',''),1,0,'C');
                        if(strlen($v->producto->nombre)<35){
                            $pdf::Cell(80,12,$v->producto->nombre,1,0,'L');
                        }else{
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(80,2,$v->producto->nombre,0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(80,12,"",1,0,'L');
                        }
                        //$pdf::Cell(80,12,$v->producto->nombre,0,0,'L');
                        $pdf::Cell(22,12,number_format($v->precio,2,'.',''),1,0,'C');
                        $valaux = round(($v->precio*$v->cantidad), 2);
                        $precioaux = $v->precio - ($v->precio*($value->descuentokayros/100));
                        $dscto = round(($precioaux*$v->cantidad),2);
                        $totalpago = $totalpago+$dscto;
                        $pdf::Cell(20,12,number_format($dscto,2,'.',''),1,0,'C');
                        $pdf::Cell(20,12,number_format($value->copago,2,'.',''),1,0,'C');
                        $subtotal = round(($dscto*($value->copago/100)),2);
                        $subigv = round(($subtotal/1.18),2);
                        $totaldescuento = $totaldescuento+$subtotal;
                        $totaligv = $totaligv+$subigv;
                        $pdf::Cell(20,12,number_format($subtotal,2,'.',''),1,0,'C');
                        $pdf::Cell(20,12,number_format($subigv,2,'.',''),1,0,'C');
                        $pdf::Ln();
                    }
                    $pdf::SetFont('helvetica','B',13);
                    $pdf::Cell(115,7,'',0,0,'C');
                    $pdf::Cell(20,7,number_format($totalpago,2,'.',''),0,0,'C');
                    $pdf::Cell(20,7,'',0,0,'C');
                    $pdf::Cell(20,7,number_format($totaldescuento,2,'.',''),0,0,'C');
                    $pdf::Cell(20,7,number_format($totaligv,2,'.',''),0,0,'C');
                    $pdf::Ln();*/
                    $pdf::Output('FacturaCompra.pdf');
                    
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $compra = Compra::find($id);
        $entidad             = 'Compra';
        $cboDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $cboCredito        = array("N" => 'NO', 'S' => 'SI');
        $cboCajafarmacia        = array("N" => 'NO', 'S' => 'SI');     
        $formData            = array('compra.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        //$cuenta = Cuenta::where('movimiento_id','=',$compra->id)->orderBy('id','ASC')->first();
        //$fechapago =  Date::createFromFormat('Y-m-d', $cuenta->fecha)->format('d/m/Y');
        $detalles = Detallemovimiento::where('movimiento_id','=',$compra->id)->get();
        $cuentas = Cuenta::where('movimiento_id','=',$compra->id)->orderBy('id','ASC')->get();
        $doctor = '';
        if ($compra->doctor_id !== null) {
            $person = Person::find($compra->doctor_id);
            $doctor = $person->nombres.' '.$person->apellidopaterno.' '.$person->apellidomaterno;
        }
        //$numerocuotas = count($cuentas);
        return view($this->folderview.'.mantView')->with(compact('compra', 'formData', 'entidad', 'boton', 'listar','cboDocumento','cboCredito','cboCajafarmacia','fechapago','detalles','cuentas','doctor','numerocuotas'));
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
    public function eliminar($id,$listarLuego)
    {
        //
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Movimiento::find($id);
        $entidad  = 'Compra';
        $formData = array('route' => array('compra.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function destroy($id)
    {
        $error = DB::transaction(function() use($id){
            $movimiento = Movimiento::find($id);

            $detalles = Detallemovimiento::where('movimiento_id','=',$movimiento->id)->get();
            foreach ($detalles as $key => $value) {
                $consultakardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('movimiento.id', '=',$movimiento->id)->where('producto_id', '=', $value->producto_id)->select('kardex.*')->get();

                foreach ($consultakardex as $key2 => $value2) {
                    $lote = Lote::find($value2->lote_id);
                    if($movimiento->tipodocumento_id==11){//NOTA DE CREDITO
                        $lote->queda = $lote->queda - $value2->cantidad;
                    }else{
                        $lote->queda = $lote->queda - $value2->cantidad;
                    }
                    $lote->save();

                    //Stock
                    $stock = Stock::where('producto_id','=',$value->producto_id)->where('almacen_id','=',1)->first();
                    if(is_null($stock)){
                        $stock = new Stock();
                        $stock->producto_id = $value->producto_id;
                        if($movimiento->tipodocumento_id==11){//NOTA DE CREDITO
                            $stock->cantidad = $value2->cantidad*(-1);
                        }else{
                            $stock->cantidad = $value2->cantidad*(-1);
                        }
                        $stock->almacen_id = 1;
                        $stock->save();
                    }else{  
                        if($movimiento->tipodocumento_id==11){//NOTA DE CREDITO
                            $stock->cantidad = $stock->cantidad - $value2->cantidad;
                        }else{
                            $stock->cantidad = $stock->cantidad - $value2->cantidad;
                        }
                        $stock->save();
                    }
                    //

                    $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $value->producto_id)->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();

                    $stockanterior = 0;
                    $stockactual = 0;
                    // ingresamos nuevo kardex
                    if ($ultimokardex === NULL) {
                        
                        
                    }else{
                        $stockanterior = $ultimokardex->stockactual;
                        if($movimiento->tipodocumento_id==11){//NOTA DE CREDITO
                            $stockactual = $ultimokardex->stockactual-$value2->cantidad;
                        }else{
                            $stockactual = $ultimokardex->stockactual-$value2->cantidad;
                        }
                        $kardex = new Kardex();
                        if($movimiento->tipodocumento_id==11){//NOTA DE CREDITO
                            $kardex->tipo = 'I';
                        }else{
                            $kardex->tipo = 'S';
                        }
                        $kardex->fecha = date('Y-m-d');
                        $kardex->stockanterior = $stockanterior;
                        $kardex->stockactual = $stockactual;
                        if($movimiento->tipodocumento_id==11){//NOTA DE CREDITO
                            $kardex->cantidad = (-1)*$value2->cantidad;
                        }else{
                            $kardex->cantidad = $value2->cantidad;
                        }
                        $kardex->preciocompra = $value->precio;
                        //$kardex->almacen_id = 1;
                        $kardex->detallemovimiento_id = $value->id;
                        $kardex->lote_id = $lote->id;
                        $kardex->save();    

                    }
                }
            }

            $movimiento->delete();
            
        });
        return is_null($error) ? "OK" : $error;
    }
}
