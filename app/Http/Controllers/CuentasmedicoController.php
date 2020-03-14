<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Convenio;
use App\Caja;
use App\Person;
use App\Venta;
use App\Movimiento;
use App\Tipodocumento;
use App\Conceptopago;
use App\Detallemovcaja;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;
use Excel;

class CuentasmedicoController extends Controller
{
    protected $folderview      = 'app.cuentasmedico';
    protected $tituloAdmin     = 'Honorarios Medicos Convenio';
    protected $tituloRegistrar = 'Registrar Honorario Medico';
    protected $tituloRetencion = 'Retencion Honorario Medico';
    protected $tituloEliminar  = 'Eliminar Honorario Medico';
    protected $rutas           = array('create' => 'cuentasmedico.create', 
            'edit'   => 'cuentasmedico.edit', 
            'delete' => 'cuentasmedico.eliminar',
            'retencion' => 'cuentasmedico.retencion',
            'search' => 'cuentasmedico.buscar',
            'buscarajax' => 'cuentasmedico.buscarajax',
            'index'  => 'cuentasmedico.index',            
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
        $entidad          = 'Cuentasmedico';
        $titulo_registrar = $this->tituloRegistrar;
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $proveedor       = Libreria::getParam($request->input('proveedor'));
        $numero       = Libreria::getParam($request->input('numero'));
        $estadopago       = Libreria::getParam($request->input('estadopago'));
        $user = Auth::user();
        
        $resultado        = Movimiento::leftjoin('person as proveedor', 'proveedor.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('tipodocumento as td','td.id','=','movimiento.tipodocumento_id')
                            ->whereiN('movimiento.tipomovimiento_id', [13])
                            ->where('movimiento.voucher','like','%'.$numero.'%')
                            ->where(DB::raw('case when proveedor.bussinesname is null then CONCAT(proveedor.dni," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end'),'like','%'.$proveedor.'%');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fechainicial.'');
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fechafinal.'');
        }
        if($estadopago!=""){
            $resultado = $resultado->where('movimiento.estadopago', 'like', ''.$estadopago.'');
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('case when proveedor.bussinesname is null then CONCAT(proveedor.dni," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end as proveedor2'),DB::raw('responsable.nombres as responsable'),'td.abreviatura as tipodocumento2')->orderBy('movimiento.id', 'desc');
        $lista            = $resultado->get();
        
        $cabecera         = array();
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Numero', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Proveedor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pagado', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Glosa', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_retencion = $this->tituloRetencion;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_anular    = "Anular";
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_retencion', 'titulo_eliminar', 'ruta', 'titulo_anular','user'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad', 'ruta'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $entidad          = 'Cuentasmedico';
        $title            = $this->tituloAdmin;
        $ruta             = $this->rutas;
        $titulo_registrar = $this->tituloRegistrar;
        $cboEstadoPago = array(''=>'Todos', 'P'=>'Pagado', 'PP'=>'Pendiente');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'ruta', 'titulo_registrar', 'cboEstadoPago'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Cuentasmedico';
        $formData            = array('cuentasmedico.store');
        $movimiento          = null;
        $cboTipo = array('RH' => 'RH', 'FT' => 'FT', 'BV' => 'BV', 'TK' => 'TK', 'VR' => 'VR', 'RC' => 'RC', 'NA' => 'NA', 'ND' => 'ND');
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');

        $boton               = 'Registrar '; 
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar','cboTipo'));
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
                'total'          => 'required',
                );
        $mensajes = array(
            'total.required'         => 'Debe tener un monto',
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $dat[0]=array("respuesta"=>"OK");
        $error = DB::transaction(function() use($request,$user){
            $movimiento        = new Movimiento();
            $movimiento->fecha = $request->input('fecha');
            $movimiento->voucher= $request->input('numero');
            $movimiento->formapago=$request->input('tipo');
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$request->input('person_id');    
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->tipomovimiento_id=13;
            $movimiento->tipodocumento_id=22;
            $movimiento->comentario=$request->input('glosa');
            $movimiento->situacion='N';
            $movimiento->estadopago='PP';
            $movimiento->fechaingreso  = $request->input('fechavencimiento');
            $movimiento->save();
            
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Caja = Movimiento::find($id);
            $Caja->delete();
            DB::delete("DELETE FROM reportepagomedico WHERE idmovimiento = ?",[$id]);
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Movimiento::find($id);
        $entidad  = 'Cuentasmedico';
        $formData = array('route' => array('cuentasmedico.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function confirmarretencion($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Caja = Movimiento::find($id);
            $Caja->retencion=number_format($Caja->total*0.08,2,'.','');
            $Caja->totalpagado=$Caja->totalpagado + $Caja->retencion;
            if($Caja->total==$Caja->totalpagado){
                $Caja->estadopago='P';
            }
            $Caja->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function retencion($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Movimiento::find($id);
        $entidad  = 'Cuentasmedico';
        $formData = array('route' => array('cuentasmedico.confirmarretencion', $id), 'method' => 'PUTH', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Retencion';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function personautocompletar($searching)
    {
        $resultado        = Person::where(DB::raw('CONCAT(dni," ",apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($searching).'%')->orWhere(DB::raw('concat(ruc," ",bussinesname)'), 'LIKE', '%'.strtoupper($searching).'%')->whereNull('deleted_at')->orderBy('apellidopaterno', 'ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = '';
            if ($value->bussinesname != null) {
                $name = $value->ruc.' '.$value->bussinesname;
            }else{
                $name = $value->dni.' '.$value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres;
            }
            $data[] = array(
                            'label' => trim($name),
                            'id'    => $value->id,
                            'value' => trim($name),
                        );
        }
        return json_encode($data);
    }

    public function excel(Request $request){
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $proveedor       = Libreria::getParam($request->input('proveedor'));
        $numero       = Libreria::getParam($request->input('numero'));
        $estadopago       = Libreria::getParam($request->input('estadopago'));
        
        $resultado        = Movimiento::leftjoin('person as proveedor', 'proveedor.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('tipodocumento as td','td.id','=','movimiento.tipodocumento_id')
                            ->whereiN('movimiento.tipomovimiento_id', [13])
                            ->where('movimiento.voucher','like','%'.$numero.'%')
                            ->where(DB::raw('case when proveedor.bussinesname is null then CONCAT(proveedor.dni," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end'),'like','%'.$proveedor.'%');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha', '>=', ''.$fechainicial.'');
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha', '<=', ''.$fechafinal.'');
        }
        if($estadopago!=""){
            $resultado = $resultado->where('movimiento.estadopago', 'like', ''.$estadopago.'');
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('case when proveedor.bussinesname is null then CONCAT(proveedor.dni," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end as proveedor2'),DB::raw('responsable.nombres as responsable'),'td.abreviatura as tipodocumento2')->orderBy('movimiento.id', 'desc');

         $lista            = $resultado->get();

        Excel::create('ExcelCuentasMedico', function($excel) use($lista,$request) {
 
            $excel->sheet('Excel', function($sheet) use($lista,$request) {
                $caja = Caja::find($request->input('caja_id'));
                $celdas      = 'A1:I1';
                $sheet->mergeCells($celdas);
                $sheet->cells($celdas, function($cells) {
                    $cells->setAlignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });
                $title[] = "Honorarios Medico Convenio del ".date("d/m/Y",strtotime($request->input('fechainicial')))." al ".date("d/m/Y",strtotime($request->input('fechafinal')));
                $sheet->row(1,$title);
                $cabecera[] = "Fecha" ;               
                $cabecera[] = "Fecha Venc." ; 
                $cabecera[] = "Numero";
                $cabecera[] = "Proveedor";
                $cabecera[] = "Total" ;  
                $cabecera[] = "Situacion" ;  
                $cabecera[] = "Fecha Pago" ; 
                $cabecera[] = "Glosa" ;  
                $cabecera[] = "Usuario" ;  
                $sheet->cells("A3:I3", function($cells) {
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                    $cells->setValignment('center');
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '10',
                        'bold'       =>  true
                        ));
                });             
                $sheet->row(3,$cabecera);
                if(count($lista)>0){
                    $c=4;$d1=4;$band=true;$area="";$total=0;$totalg=0;$d2=0;
                    foreach ($lista as $key => $value){
                        $detalle = array();
                        $detalle[] = date("d/m/Y",strtotime($value->fecha));
                        if($value->fechaingreso!="")
                            $detalle[] = date("d/m/Y",strtotime($value->fechaingreso));
                        else
                            $detalle[] = "";
                        if($value->tipomovimiento_id==3)
                            $detalle[] = $value->tipodocumento2.' '.$value->serie.'-'.$value->numero;
                        else
                            $detalle[] = $value->formapago.' '.$value->voucher;
                        $detalle[] = $value->proveedor2;
                        $detalle[] = $value->total;
                        if ($value->estadopago == 'PP') {
                            $estadopago = 'Pendiente';
                        }elseif ($value->estadopago == 'P') {
                            $estadopago = 'Pagado';
                        }
                        $detalle[] = $estadopago;
                        if($value->estadopago=='P' && $value->tipomovimiento_id==3){
                            $detalle[] = date("d/m/Y",strtotime($value->fecha));
                        }elseif($value->estadopago=='P'){
                            $pago = Movimiento::find($value->movimientodescarga_id);
                            $detalle[] = date("d/m/Y",strtotime($pago->fecha));
                        }else{
                            $detalle[] = '';    
                        }
                        $detalle[] = $value->comentario;
                        $detalle[] = $value->responsable;
                        $sheet->row($c,$detalle);
                        $sheet->cells("A".$c.":H".$c, function($cells) {
                            $cells->setBorder('thin','thin','thin','thin');
                            $cells->setFont(array(
                                'family'     => 'Calibri',
                                'size'       => '10',
                                ));
                        });
                        $total = $total + $value->total;
                        $c++;
                    }
                    $detalle = array();
                    $detalle[] = "";
                    $detalle[] = "TOTAL GENERAL";
                    $detalle[] = "";
                    $detalle[] = "";
                    $detalle[] = number_format($total,2,'.','');
                    $sheet->row($c,$detalle);
                    $celdas      = 'A'.$c.':D'.$c;
                    $sheet->mergeCells($celdas);
                    $sheet->cells('A'.$c.':E'.$c, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setValignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '10',
                            'bold'       =>  true
                            ));
                    });                    
                }
            });
        })->export('xls');
    }

    public function buscarajax(Request $request)
    {
        $entidad          = 'Cuentasmedico';
        $idmedico     = Libreria::getParam($request->input('idmedico'));
        $fecha       = Libreria::getParam($request->input('fecha'));
        $estadopago = "P";
        $numero = "";
        $proveedor = "";
        $resultado        = Movimiento::leftjoin('person as proveedor', 'proveedor.id', '=', 'movimiento.persona_id')
                            ->leftjoin('movimiento as pago','movimiento.movimientodescarga_id', '=','pago.id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('tipodocumento as td','td.id','=','movimiento.tipodocumento_id')
                            ->whereIn('movimiento.tipomovimiento_id', [13,12])
                            //->where('movimiento.voucher','like','%'.$numero.'%')
                            //->where(DB::raw('case when proveedor.bussinesname is null then CONCAT(proveedor.dni," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end'),'like','%'.$proveedor.'%')
                            ;
        if($idmedico!=""){
            $resultado = $resultado->where('movimiento.persona_id', '=', ''.$idmedico.'');
        }
        if($fecha!=""){
            $resultado = $resultado
                ->where(function($q) use ($fecha){
                    $q->where("movimiento.fecha","=",''.$fecha)
                        ->orWhere('pago.fecha', '=', ''.$fecha.'');
                });
        }
        if($estadopago!=""){
            $resultado = $resultado
                ->where(function($qq) use ($estadopago){
                    $qq->where('movimiento.estadopago', 'like', ''.$estadopago.'')
                        ->orWhereNull("movimiento.estadopago");
                });
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('case when proveedor.bussinesname is null then CONCAT(proveedor.dni," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end as proveedor2'),DB::raw('responsable.nombres as responsable'),'td.abreviatura as tipodocumento2')->orderBy('movimiento.id', 'desc');
        $lista            = $resultado->get();
        
        return json_encode($lista);
    }

}