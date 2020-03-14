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

class CuentasporpagarController extends Controller
{
    protected $folderview      = 'app.cuentasporpagar';
    protected $tituloAdmin     = 'Cuentas por Pagar';
    protected $tituloRegistrar = 'Registrar Cuentas por Pagar';
    protected $tituloModificar = 'Modificar Caja';
    protected $tituloEliminar  = 'Eliminar Cuentas por Pagar';
    protected $rutas           = array('create' => 'cuentasporpagar.create', 
            'edit'   => 'cuentasporpagar.edit', 
            'delete' => 'cuentasporpagar.eliminar',
            'search' => 'cuentasporpagar.buscar',
            'index'  => 'cuentasporpagar.index',            
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
        $entidad          = 'Cuentasporpagar';
        $titulo_registrar = $this->tituloRegistrar;
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $proveedor       = Libreria::getParam($request->input('proveedor'));
        $numero       = Libreria::getParam($request->input('numero'));
        $estadopago       = Libreria::getParam($request->input('estadopago'));
        
        $resultado        = Movimiento::leftjoin('person as proveedor', 'proveedor.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('tipodocumento as td','td.id','=','movimiento.tipodocumento_id')
                            ->whereiN('movimiento.tipomovimiento_id', [3,11])
                            ->where(DB::raw('CASE WHEN movimiento.tipomovimiento_id=3 then CONCAT(movimiento.serie," ",movimiento.numero) else movimiento.voucher end'),'like','%'.$numero.'%')
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
        if($request->input('modo')!=""){
            if($request->input('modo')=="F"){
                $resultado = $resultado->where('movimiento.tipomovimiento_id', '=', 3);
            }else{
                $resultado = $resultado->where('movimiento.tipomovimiento_id', '<>', 3);
            }
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('case when proveedor.bussinesname is null then CONCAT(proveedor.dni," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end as proveedor2'),DB::raw('responsable.nombres as responsable'),'td.abreviatura as tipodocumento2')->orderBy('movimiento.id', 'desc');
        $lista            = $resultado->get();
        
        $cabecera         = array();
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Venc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Numero', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Proveedor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pagado', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Glosa', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'titulo_anular'));
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
        $entidad          = 'Cuentasporpagar';
        $title            = $this->tituloAdmin;
        $ruta             = $this->rutas;
        $titulo_registrar = $this->tituloRegistrar;
        $cboEstadoPago = array(''=>'Todos', 'P'=>'Pagado', 'PP'=>'Pendiente');
        $cboModo = array(''=>'Todos','F'=>'Farmacia','O'=>'Hospital');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'ruta', 'titulo_registrar', 'cboEstadoPago','cboModo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Cuentasporpagar';
        $formData            = array('cuentasporpagar.store');
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
                'txtTotal'          => 'required',
                );
        $mensajes = array(
            'txtTotal.required'         => 'Debe tener un monto',
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
            $movimiento->igv=str_replace(",","",$request->input('txtIgv'));
            $movimiento->total=str_replace(",","",$request->input('txtTotal')); 
            $movimiento->tipomovimiento_id=11;
            $movimiento->tipodocumento_id=19;
            $movimiento->comentario=$request->input('glosa');
            $movimiento->situacion='N';
            $movimiento->estadopago='PP';
            $movimiento->fechaingreso  = $request->input('fechavencimiento');
            $movimiento->save();
            
            $list = explode(',',$request->input('lista'));
            for($c=0;$c<count($list);$c++){
                $detalle = new Detallemovcaja();
                $detalle->movimiento_id = $movimiento->id;
                $detalle->cantidad=$request->input('txtCantidad'.$list[$c]);
                $detalle->descripcion=$request->input('txtDescripcion'.$list[$c]);
                $detalle->precio=$request->input('txtPrecio'.$list[$c]);
                //$detalle->recibo=$request->input('txtUnidad'.$list[$c]);
                $detalle->save();
            }
            
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
        $existe = Libreria::verificarExistencia($id, 'Caja');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $Caja = Caja::find($id);
        $entidad             = 'Caja';
        $formData            = array('Caja.update', $id);
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('Caja', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente'));
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
        $existe = Libreria::verificarExistencia($id, 'Caja');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'nombre'                  => 'required|max:100',
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request, $id){
            $categoria                        = Categoria::find($id);
            $categoria->nombre = strtoupper($request->input('nombre'));
            $categoria->save();
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
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Caja = Movimiento::find($id);
            $Caja->delete();
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
        $entidad  = 'Cuentasporpagar';
        $formData = array('route' => array('cuentasporpagar.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
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

	public function pdfComprobante(Request $request){
        $lista = Movimiento::where('id','=',$request->input('id'))->first();
                    
        $pdf = new TCPDF();
        $pdf::SetTitle('Otras Compras');
        $pdf::AddPage();
        $pdf::SetFont('helvetica','B',12);
        $pdf::Cell(0,10,'',0,0,'C');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(27,7,utf8_decode("FECHA :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(100,7,date("d/m/Y",strtotime($lista->fecha)),0,0,'L');
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(20,7,utf8_decode("NRO :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(40,7,utf8_decode($lista->formapago.' '.$lista->voucher),0,0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(27,7,utf8_decode("RAZON SOCIAL :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(100,7,utf8_decode($lista->persona->bussinesname),0,0,'L');
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(20,7,utf8_decode("RUC :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(40,7,utf8_decode($lista->persona->ruc),0,0,'L');
        $pdf::Ln();
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',8);
        $pdf::Cell(15,7,utf8_decode("CANT."),1,0,'C');
        $pdf::Cell(100,7,utf8_decode("DESCRIPCION"),1,0,'C');
        //$pdf::Cell(20,7,utf8_decode("UND"),1,0,'C');
        $pdf::Cell(20,7,utf8_decode("P. VENT."),1,0,'C');
        $pdf::Cell(20,7,utf8_decode("SUBTOTAL"),1,0,'C');
        $pdf::Ln();
        $pdf::SetFont('helvetica','',8);
        $detalle = Detallemovcaja::where('movimiento_id','=',$lista->id)->get();
        foreach ($detalle as $key => $value) {
            $pdf::Cell(15,7,number_format($value->cantidad,2,'.',''),1,0,'C');
            $pdf::Cell(100,7,($value->descripcion),1,0,'L');
            //$pdf::Cell(20,7,utf8_decode($value->recibo),1,0,'C');
            $pdf::Cell(20,7,number_format($value->precio,2,'.',''),1,0,'R');
            $pdf::Cell(20,7,number_format($value->precio*$value->cantidad,2,'.',''),1,0,'R');
            $pdf::Ln();                
        }    
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(115,7,utf8_decode(""),0,0,'L');
        $pdf::Cell(20,7,utf8_decode("IGV :"),0,0,'R');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(20,7,number_format($lista->igv,2,'.',''),0,0,'R');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(115,7,utf8_decode(""),0,0,'L');
        $pdf::Cell(20,7,utf8_decode("TOTAL :"),0,0,'R');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(20,7,number_format($lista->total,2,'.',''),0,0,'R');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(20,7,utf8_decode("GLOSA :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(0,7,utf8_decode($lista->comentario),0,0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(20,7,utf8_decode("USUARIO :"),0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(100,7,($lista->responsable->nombres).' - '.date("d/m/Y H:i:s",strtotime($lista->created_at)),0,0,'L');
        $pdf::Ln();
        $pdf::Output('OtrasCompras.pdf');
        
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
                            ->whereiN('movimiento.tipomovimiento_id', [3,11])
                            ->where(DB::raw('CASE WHEN movimiento.tipomovimiento_id=3 then CONCAT(movimiento.serie," ",movimiento.numero) else movimiento.voucher end'),'like','%'.$numero.'%')
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
        if($request->input('modo')!=""){
            if($request->input('modo')=="F"){
                $resultado = $resultado->where('movimiento.tipomovimiento_id', '=', 3);
            }else{
                $resultado = $resultado->where('movimiento.tipomovimiento_id', '<>', 3);
            }
        }
        $resultado        = $resultado->select('movimiento.*',DB::raw('case when proveedor.bussinesname is null then CONCAT(proveedor.dni," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end as proveedor2'),DB::raw('responsable.nombres as responsable'),'td.abreviatura as tipodocumento2')->orderBy('movimiento.id', 'desc');

         $lista            = $resultado->get();

        Excel::create('ExcelCuentasporPagar', function($excel) use($lista,$request) {
 
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
                $title[] = "Cuentas por pagar del ".date("d/m/Y",strtotime($request->input('fechainicial')))." al ".date("d/m/Y",strtotime($request->input('fechafinal')));
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

    public function vencimiento(Request $request){
        $entidad          = 'Cuentabancaria';
         $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $proveedor       = Libreria::getParam($request->input('proveedor'));
        $numero       = Libreria::getParam($request->input('numero'));
        $estadopago       = Libreria::getParam($request->input('estadopago'));
        
        $resultado        = Movimiento::leftjoin('person as proveedor', 'proveedor.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('tipodocumento as td','td.id','=','movimiento.tipodocumento_id')
                            ->whereiN('movimiento.tipomovimiento_id', [3,11])
                            ->where(DB::raw('CASE WHEN movimiento.tipomovimiento_id=3 then CONCAT(movimiento.serie," ",movimiento.numero) else movimiento.voucher end'),'like','%'.$numero.'%')
                            ->where(DB::raw('case when proveedor.bussinesname is null then CONCAT(proveedor.dni," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end'),'like','%'.$proveedor.'%');
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fechaingreso', '>=', ''.$fechainicial.'');
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fechaingreso', '<=', ''.$fechafinal.'');
        }
        $resultado = $resultado->where('movimiento.estadopago', 'like', 'PP');
        $resultado        = $resultado->select('movimiento.*',DB::raw('case when proveedor.bussinesname is null then CONCAT(proveedor.dni," ",proveedor.apellidopaterno," ",proveedor.apellidomaterno," ",proveedor.nombres) else concat(proveedor.ruc," ",proveedor.bussinesname) end as proveedor2'),DB::raw('responsable.nombres as responsable'),'td.abreviatura as tipodocumento2')->orderBy('movimiento.fechaingreso', 'asc');
       $lista            = $resultado->get();
        if (count($lista) > 0) {            
            $pdf = new TCPDF();
            $pdf::SetTitle('Vencimiento');
            $pdf::AddPage('P');
            $pdf::SetFont('helvetica','B',11);
            $fechainicial=date("d/m/Y",strtotime($request->input('fechainicial')));
            $fechafinal=date("d/m/Y",strtotime($request->input('fechafinal')));
            $pdf::Cell(0,10,utf8_decode("REPORTE DE VENCIMIENTO DEL ".$fechainicial." AL ".$fechafinal),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            $pdf::Cell(15,6,utf8_decode("FECHA"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("FECHA VENC."),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(100,6,utf8_decode("PERSONA"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("IMPORTE"),1,0,'C');
            //$pdf::Cell(22,6,utf8_decode("FORMA PAGO"),1,0,'C');
            $pdf::Ln();
            $formapago='';$total=0;$totalg=0;
            foreach ($lista as $key => $value){
                $pdf::SetFont('helvetica','',7);
                $pdf::Cell(15,5,date("d/m/Y",strtotime($value->fecha)),1,0,'L');
                $pdf::Cell(20,5,date("d/m/Y",strtotime($value->fechaingreso)),1,0,'C');
                if($value->tipomovimiento_id==3)
                    $pdf::Cell(20,5,$value->tipodocumento2.' '.$value->serie.'-'.$value->numero,1,0,'L');
                else
                    $pdf::Cell(20,5,$value->formapago.' '.$value->voucher,1,0,'L');
                $persona = ($value->proveedor2);
                if(strlen($persona)>25){
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();
                    $pdf::Multicell(100,2,$persona,0,'L');
                    $pdf::SetXY($x,$y);
                    $pdf::Cell(100,5,'',1,0,'L');
                }else{
                    $pdf::Cell(100,5,$persona,1,0,'L');
                }
                $pdf::Cell(15,5,number_format($value->total,2,'.',''),1,0,'C');
                $total = $total + number_format($value->total,2,'.','');
                //$pdf::Cell(22,5,$value->numeroficha,1,0,'L');
                $pdf::Ln();
            }
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(155,5,utf8_decode('TOTAL'),1,0,'R');
            $pdf::Cell(15,5,number_format($total,2,'.',''),1,0,'C');
            $pdf::Ln(); 
            $pdf::Output('ListaVenta.pdf');
        }
    }
}