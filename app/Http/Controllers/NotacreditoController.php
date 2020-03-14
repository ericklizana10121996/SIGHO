<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Caja;
use App\Tipodocumento;
use App\Movimiento;
use App\Detallemovimiento;
use App\Person;
use App\Detallemovcaja;
use App\Servicio;
use App\Producto;
use App\Kardex;
use App\Lote;
use App\Librerias\Libreria;
use App\Librerias\EnLetras;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;
use Excel;

/**
* 
*/
class MYPDF extends TCPDF
{
    
    function Header()
    {
        # code...
    }

    function Footer()
    {
        # code...
    }
}

class NotacreditoController extends Controller
{
    protected $folderview      = 'app.notacredito';
    protected $tituloAdmin     = 'Nota de Credito';
    protected $tituloRegistrar = 'Registrar Nota de Credito';
    protected $tituloModificar = 'Modificar Nota de Credito';
    protected $tituloEliminar  = 'Eliminar Nota de Credito';
    protected $tituloAnular    = 'Anular Nota de Credito';
    protected $rutas           = array('create' => 'notacredito.create', 
            'edit'   => 'notacredito.edit', 
            'anular' => 'notacredito.anular',
            'search' => 'notacredito.buscar',
            'index'  => 'notacredito.index',
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Notacredito';
  
        $user = Auth::user();
        if($request->input('usuario')=="Todos"){
            $responsable_id=0;
        }else{
            $responsable_id=$user->person_id;
        }

        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',6);
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
        }        
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }        
        if($request->input('numero')!=""){
            $resultado = $resultado->where(DB::raw('CONCAT(case when m2.tipodocumento_id=5 then "B" else "F" end,movimiento.serie,"-",movimiento.numero)'),'LIKE','%'.$request->input('numero').'%');
        }        
        if($request->input('numeroref')!=""){
            $resultado = $resultado->where(DB::raw('CONCAT(case when m2.tipodocumento_id=5 then "B" else "F" end,m2.serie,"-",m2.numero)'),'LIKE','%'.$request->input('numeroref').'%');
        }
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }
        if($responsable_id>0){
            $resultado = $resultado->where('movimiento.responsable_id', '=', $responsable_id);   
        }

        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2',DB::raw('CONCAT(case when m2.tipodocumento_id=4 or m2.tipodocumento_id=17 then "F" else "B" end,m2.serie,"-",m2.numero) as numeroref'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.bussinesname','m2.tipodocumento_id as tipodocumento_id2')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Ref', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado BZ', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado Sunat', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Msg. Sunat', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '2');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $ruta             = $this->rutas;
        $titulo_anular    = $this->tituloAnular;
        // $user = Auth::user();
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'user', 'titulo_anular'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    public function index()
    {
        $entidad          = 'Notacredito';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        $cboSituacion = array(''=>'Todos...','U'=>'Anulado');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user', 'cboSituacion'));
    }

    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Notacredito';
        $notacredito = null;
        $numero              = Movimiento::NumeroSigue(6,13,2,'N');
        $user = Auth::user();
        $idcaja=0;
        $rs = Caja::where('nombre','<>','FARMACIA')->orderBy('nombre','ASC')->get();
        $idcaja=0;
        foreach ($rs as $key => $value) {
            if($request->ip()==$value->ip){
                $idcaja=$value->id;
            }
        }
        if($idcaja==0){//ADMISION 1
            $idcaja=1;
        }
        $cboCaja = array();
        $rs = Caja::where('nombre','<>','FARMACIA')->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboCaja = $cboCaja + array($value->id => $value->nombre);
        }
        $cboComentario = array('01@Anulacion de la operacion'=>'Anulación de la operación',
                                '02@Anulacion por error en el RUC'=>'Anulación por error en el RUC',
                                '03@Correccion por error en la descripcion'=>'Corrección por error en la descripción',
                                '04@Descuento global'=>'Descuento global',
                                '05@Descuento por item'=>'Descuento por ítem',
                                '06@Devolucion total'=>'Devolución total',
                                '07@Devolucion por ítem'=>'Devolución por ítem',
                                '08@Bonificacion'=>'Bonificación',
                                '09@Disminucion en el valor'=>'Disminución en el valor',
                                '10@Otros conceptos'=>'Otros conceptos');
        $formData            = array('notacredito.store');
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('notacredito', 'formData', 'entidad', 'boton', 'listar','numero','cboCaja','idcaja', 'cboComentario', 'user'));
    }

    public function store(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                'numeroref'                  => 'required',
                'paciente'          => 'required',
                'numero'          => 'required',
                );
        $mensajes = array(
            'numeroref.required'         => 'Debe seleccionar un doctor',
            'paciente.required'         => 'Debe seleccionar un paciente',
            'numero.required'         => 'Debe seleccionar una historia',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       
        
        $user = Auth::user();
        $dat= array();
        $numero='';
        if($request->input('pagar')=='S'){
            $rst  = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$request->input('caja_id'))->orderBy('movimiento.id','DESC')->limit(1)->first();
            if(count($rst)==0){
                $conceptopago_id=2;
            }else{
                $conceptopago_id=$rst->conceptopago_id;
            }
            if($conceptopago_id==2){
                $dat[0]=array("respuesta"=>"ERROR","msg"=>"Caja cerrada");
                return json_encode($dat);
            }
        }
        $error = DB::transaction(function() use($request,$user,&$dat,&$numero){
            //VENTA
            $Movimientoref = Movimiento::find($request->input('movimiento_id'));
            $Movimientoref->situacion='A';
            $Movimientoref->save();
            
            //NOTA CREDITO
            $Movimiento       = new Movimiento();
            $Movimiento->fecha = $request->input('fecha');
            $Movimiento->serie = $request->input('serie');
            if($Movimientoref->tipodocumento_id=='5'){
                $dat = Movimiento::join('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                        ->where('movimiento.tipomovimiento_id','=',6)->where('movimiento.manual','like','N')->whereIn('m2.tipodocumento_id',['5'])->select(DB::raw("max((CASE WHEN movimiento.numero IS NULL THEN 0 ELSE movimiento.numero END)*1) AS maximo"))->first();
            }else{
                $dat = Movimiento::join('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                        ->where('movimiento.tipomovimiento_id','=',6)->where('movimiento.manual','like','N')->whereIn('m2.tipodocumento_id',['4','17'])->select(DB::raw("max((CASE WHEN movimiento.numero IS NULL THEN 0 ELSE movimiento.numero END)*1) AS maximo"))->first();
            }
            $numero = $dat->maximo + 1;
            $Movimiento->numero = $numero;
            $Movimiento->persona_id = $request->input('person_id');
            if($Movimientoref->igv>0){
                $subtotal = number_format($request->input('total')/1.18,2,'.','');
                $igv = number_format($request->input('total') - $subtotal,2,'.','');
            }else{
                $subtotal = number_format($request->input('total'),2,'.','');
                $igv=0;
            }
            $Movimiento->total = $request->input('total');
            $Movimiento->subtotal = $subtotal;
            $Movimiento->igv = $igv;
            $Movimiento->responsable_id=$user->person_id;
            $Movimiento->movimiento_id = $request->input('movimiento_id');
            $Movimiento->situacion='N';//Normal
            $Movimiento->tipomovimiento_id = 6;
            $Movimiento->tipodocumento_id = 13;
            $comentario = explode("@",$request->input('comentario'));
            if($comentario[0] =='10'){
                $Movimiento->comentario = $comentario[1].':'.$request->input('motivo');
            }else{
                $Movimiento->comentario = $comentario[1];
            }
                
            $Movimiento->manual='N';
            $Movimiento->save();
            $arr=explode(",",$request->input('listServicio'));
            for($c=0;$c<count($arr);$c++){
                $Detalleref = Detallemovcaja::find($arr[$c]);
                $Detalle = new Detallemovcaja();
                $Detalle->movimiento_id=$Movimiento->id;
                $Detalle->persona_id=$Detalleref->persona_id;
                $Detalle->cantidad=$Detalleref->cantidad;
                $Detalle->precio=$Detalleref->precio;
                $Detalle->servicio_id=$Detalleref->servicio_id;
                $Detalle->pagohospital=$Detalleref->pagohospital;
                $Detalle->descripcion=$Detalleref->descripcion;
                $Detalle->descuento=0;
                $Detalle->save();
            }
            //CAJA
            if($request->input('pagar')=='S'){
                $movimiento        = new Movimiento();
                $movimiento->fecha = date("Y-m-d");
                $movimiento->numero= Movimiento::NumeroSigue(2,2);
                $movimiento->responsable_id=$user->person_id;
                $movimiento->persona_id=$request->input('person_id');
                $movimiento->subtotal=0;
                $movimiento->igv=0;
                $movimiento->total=$request->input('total',0); 
                $movimiento->tipomovimiento_id=2;
                $movimiento->tipodocumento_id=2;
                $movimiento->conceptopago_id=13;//DEVVOLUCION
                $movimiento->comentario='Anulacion de: '.$request->input('numeroref');
                $movimiento->caja_id=$request->input('caja_id');
                $movimiento->totalpagado=$request->input('total',0);
                $movimiento->situacion='N';
                $movimiento->movimiento_id=$Movimiento->id;
                $movimiento->save();
            }
            /*
            //Array Insert facturacion
            if($Movimientoref->tipodocumento_id==5){//BOLETA
                $codigo="03";
                $abreviatura="BC";
            }else{
                $codigo="01";
                $abreviatura="FC";
            }            
            $person = Person::find($Movimiento->persona_id);
            $columna1=6;
            $columna2="20480082673";//RUC HOSPITAL
            $columna3="HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA";//Razon social Hospital                
            $columna4="07";
            $columna5=$abreviatura.substr($request->input('serie'),1,2).'-'.$request->input('numero');
            $numero=$columna5;
            $columna6=date('Y-m-d');
            $columna7="sistemas@hospitaljuanpablo.pe";
            if($Movimientoref->tipodocumento_id==5){//BOLETA
                $columna8=1;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                if(strlen($person->dni)<>8){
                    $columna9='00000000';
                }else{
                    $columna9=$person->dni;
                }
            }else{
                $columna8=6;//Tipo Doc. Persona->Paciente DNI // DNI=1  RUC=6  Ninguno=0
                $columna9=$person->ruc;
            }
            $columna10=trim($person->bussinesname." ".$person->apellidopaterno." ".$person->apellidomaterno." ".$person->nombres);//Razon social
            if(trim($person->direccion!="")){
                $columna101=trim($person->direccion);
            }else{
                $columna101="-";
            }
            $columna11="-";    
            $columna12="PEN";
            if($igv>0){
                $columna13=$Movimiento->subtotal;
                $columna14='0.00';
                $columna15='0.00';
            }else{
                $columna13='0.00';
                $columna14=$Movimiento->subtotal;
                $columna15='0.00';
            }
            $columna16="";
            $columna17=$Movimiento->igv;
            $columna18='0.00';
            $columna19='0.00';
            $columna20=$Movimiento->total;
            $columna21=1000;
            $letras = new EnLetras();
            $columna22=$letras->ValorEnLetras($columna20, "SOLES" );//letras
            $columna23=$codigo;
            $columna24=($Movimientoref->tipodocumento_id==5?"B":"F").str_pad($Movimientoref->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($Movimientoref->numero,8,'0',STR_PAD_LEFT);
            $columna25=$Movimiento->comentario;
            $columna26=$comentario[0];
            $columna27='9671';
            if($Movimientoref->tipodocumento_id==17){
                $paciente=$Movimientoref->persona->apellidopaterno.' '.$Movimientoref->persona->apellidomaterno.' '.$Movimientoref->persona->nombres;
                $Historia = Historia::where('person_id','=',$movimiento->persona_id)->first();
            }else{
                $mov=Movimiento::find($Movimientoref->movimiento_id);
                $paciente=$mov->persona->apellidopaterno.' '.$mov->persona->apellidomaterno.' '.$mov->persona->nombres;
                $Historia = Historia::where('person_id','=',$mov->persona_id)->first();
            }
            $columna28=substr(trim($paciente),0,100);
            $columna29='9671';
            $columna30='HISTORIA CLINICA: '.$Historia->numero;
            
            DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER (
                tipoDocumentoEmisor,
                numeroDocumentoEmisor,
                razonSocialEmisor,
                tipoDocumento,
                serieNumero,
                fechaEmision,
                correoEmisor,
                tipoDocumentoAdquiriente,
                numeroDocumentoAdquiriente,
                razonSocialAdquiriente,
                correoAdquiriente,
                tipoMoneda,
                totalValorVentaNetoOpGravadas,
                totalValorVentaNetoOpNoGravada,
                totalValorVentaNetoOpExonerada,                
                totalIgv,
                totalVenta,
                codigoLeyenda_1,
                textoLeyenda_1,
                tipoDocumentoReferenciaPrincip,
                numeroDocumentoReferenciaPrinc,
                motivoDocumento,
                codigoSerieNumeroAfectado,
                serieNumeroAfectado, 
                codigoAuxiliar100_1,
                textoAuxiliar100_1,
                codigoAuxiliar100_2,
                textoAuxiliar100_2
                ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                [$columna1, $columna2, $columna3, $columna4, $columna5, $columna6, $columna7, $columna8, $columna9, $columna10, $columna11, $columna12, $columna13, $columna14, $columna15, $columna17, $columna20, $columna21, $columna22, $columna23, $columna24, $columna25, $columna26, $columna24, $columna27, $columna28, $columna29, $columna30]);

            if($abreviatura=="BC"){
                DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                    tipoDocumentoEmisor,
                    numeroDocumentoEmisor,
                    serieNumero,
                    tipoDocumento,
                    clave,
                    valor) 
                    values (?, ?, ?, ?, ?, ?)',
                    [$columna1, $columna2, $columna5, $columna4, 'lugarDestino', $columna101]);
            }else{
                DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEHEADER_ADD(
                    tipoDocumentoEmisor,
                    numeroDocumentoEmisor,
                    serieNumero,
                    tipoDocumento,
                    clave,
                    valor) 
                    values (?, ?, ?, ?, ?, ?)',
                    [$columna1, $columna2, $columna5, $columna4, 'direccionAdquiriente', $columna101]);
            }
            //---
            
            //Array Insert Detalle Facturacion
            $arr=explode(",",$request->input('listServicio'));
            for($c=0;$c<count($arr);$c++){
                $columnad1=$c+1;
                $detalle = Detallemovcaja::find($arr[$c]);
                if($detalle->servicio_id>0){
                    $servicio = Servicio::find($detalle->servicio_id);
                    if($servicio->tipopago=="Convenio"){
                        $columnad2=$servicio->tarifario->codigo;
                        $columnad3=$servicio->tarifario->nombre;    
                    }else{
                        $columnad2="-";
                        if($detalle->servicio_id>0){
                            $columnad3=$servicio->nombre;
                        }else{
                            $columnad3=trim($detalle->descripcion);
                        }
                    }
                }else{
                    $columnad2="-";
                    $columnad3=trim($detalle->descripcion);
                }
                if($Movimientoref->tipodocumento_id==17){
                    $precio=$detalle->precio;
                }else{
                    $precio=$detalle->pagohospital;
                }
                $columnad4=round($detalle->cantidad,2);
                $columnad5="ZZ";
                if($igv>0)
                    $columnad6=round($precio/1.18,2);
                else
                    $columnad6=round($precio,2);
                $columnad7=$precio;
                $columnad8="01";
                $columnad9=round($columnad4*$columnad6,2);
                if($igv>0){
                    $columnad10="10";
                    $columnad11=round($columnad9*0.18,2);
                }else{
                    $columnad10="30";
                    $columnad11='0.00';
                }
                $columnad12='0.00';
                $columnad13='0.00';
                DB::connection('sqlsrv')->insert('insert into SPE_EINVOICEDETAIL(
                tipoDocumentoEmisor,
                numeroDocumentoEmisor,
                tipoDocumento,
                serieNumero,
                numeroOrdenItem,
                codigoProducto,
                descripcion,
                cantidad,
                unidadMedida,
                importeUnitarioSinImpuesto,
                importeUnitarioConImpuesto,
                codigoImporteUnitarioConImpues,
                importeTotalSinImpuesto,
                codigoRazonExoneracion,
                importeIgv
                )
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$columna1, $columna2, $columna4, $columna5, $columnad1, $columnad2, $columnad3, $columnad4, $columnad5, $columnad6, $columnad7, $columnad8, $columnad9, $columnad10, $columnad11]);
            }
            DB::connection('sqlsrv')->update('update SPE_EINVOICEHEADER set bl_estadoRegistro = ? where serieNumero  = ?',
                ['A',$columna5]);
                */
            $dat[0]=array("respuesta"=>"OK","id"=>$Movimiento->id);
        });
        /*if (!is_null($error)) {
            DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEHEADER where serieNumero="'.$numero.'"');
            DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEDETAIL where serieNumero="'.$numero.'"');
            DB::connection('sqlsrv')->delete('delete from SPE_EINVOICEHEADER_ADD where serieNumero="'.$numero.'"');
        }*/
        return is_null($error) ? json_encode($dat) : $error;
    }

    public function show($id)
    {
        //
    }

    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'Venta');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $Venta = Venta::find($id);
        $entidad             = 'Venta';
        $formData            = array('Venta.update', $id);
        $cboTipoPaciente     = array("Particular" => "Particular", "Convenio" => "Convenio", "Hospital" => "Hospital");
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('Venta', 'formData', 'entidad', 'boton', 'listar', 'cboTipoPaciente'));
    }

    public function update(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'Venta');
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
    public function anulacion($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id,$user){
            $Venta = Movimiento::find($id);
            $Venta->situacion = 'U';
            $Venta->usuarioentrega_id=$user->person_id;
            $Venta->save();

            $Caja = Movimiento::where("movimiento_id","=",$Venta->id)->first();
            if(!is_null($Caja)){
                $Caja->situacion = 'A';
                $Caja->save();
            }
            $Caja = Movimiento::find($Venta->movimiento_id);
            if(!is_null($Caja)){
                $Caja->situacion = 'N';
                $Caja->save();
                if($Caja->ventafarmacia == 'S'){

                    $detalles = Detallemovimiento::where('movimiento_id','=',$Venta->id)->get();
                    $lista = array();
                    foreach ($detalles as $key => $value) {
                        $lista[]  = array('detalleid' => $value->id,'cantidad' => $value->cantidad, 'precio' => $value->precio, 'productonombre' => $value->producto->nombre,'producto_id' => $value->producto_id);
                    }


                    for ($i=0; $i < count($lista); $i++) {
                        $cantidad  = str_replace(',', '',$lista[$i]['cantidad']);
                        $precio    = str_replace(',', '',$lista[$i]['precio']);
                        $subtotal  = round(($cantidad*$precio), 2);

                        $producto = Producto::find($lista[$i]['producto_id']);
                        if ($producto->afecto == 'NO') {
                            $ind = 1;
                            
                        }else{
                            
                        }

                        $consultakardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('movimiento.id', '=',$Caja->id)->where('producto_id', '=', $lista[$i]['producto_id'])->orderBy('kardex.id', 'DESC')->select('kardex.*')->limit(1)->get();
                        foreach ($consultakardex as $key => $value) {
                            if($value->lote_id>0){
                                $lote = Lote::find($value->lote_id);
                                $lote->queda = $lote->queda - $cantidad;
                                $lote->save();
                            }
                            $ultimokardex = Kardex::join('detallemovimiento', 'kardex.detallemovimiento_id', '=', 'detallemovimiento.id')->join('movimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')->where('producto_id', '=', $lista[$i]['producto_id'])->where('movimiento.almacen_id', '=',1)->orderBy('kardex.id', 'DESC')->first();

                            $stockanterior = 0;
                            $stockactual = 0;
                            // ingresamos nuevo kardex
                            if ($ultimokardex === NULL) {
                                
                                
                            }else{
                                $stockanterior = $ultimokardex->stockactual;
                                $stockactual = $ultimokardex->stockactual-$cantidad;
                                $kardex = new Kardex();
                                $kardex->tipo = 'S';
                                $kardex->fecha = date('Y-m-d');
                                $kardex->stockanterior = $stockanterior;
                                $kardex->stockactual = $stockactual;
                                $kardex->cantidad = $cantidad;
                                $kardex->precioventa = $precio;
                                //$kardex->almacen_id = 1;
                                $kardex->detallemovimiento_id = $value->detallemovimiento_id;
                                $kardex->lote_id = $lote->id;
                                $kardex->save();    

                            }
                        }
                        
                    } 

                }
            }
            //throw new \Exception("CORRECTO");
        });
        return is_null($error) ? "OK" : $error;
    }

    public function anular($id, $listarLuego)
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
        $entidad  = 'Notacredito';
        $formData = array('route' => array('notacredito.anulacion', $id), 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
   	public function pdfListar(Request $request){
        $entidad          = 'Venta';
        $paciente         = Libreria::getParam($request->input('paciente'),'');
        $doctor           = Libreria::getParam($request->input('doctor'),'');
        $fecha            = Libreria::getParam($request->input('fecha'));
        $resultado        = Venta::leftjoin('person as paciente', 'paciente.id', '=', 'Venta.paciente_id')
                            ->join('person as doctor', 'doctor.id', '=', 'Venta.doctor_id')
                            ->join('especialidad','especialidad.id','=','doctor.especialidad_id')
                            ->leftjoin('historia','historia.id','=','Venta.historia_id')
                            ->where('Venta.paciente', 'LIKE', '%'.strtoupper($paciente).'%')
                            ->where(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'), 'LIKE', '%'.strtoupper($doctor).'%');
        if($fecha!=""){
            $resultado = $resultado->where('Venta.fecha', '=', ''.$fecha.'');
        }
        $resultado        = $resultado->select('Venta.*','historia.tipopaciente as tipopaciente2','especialidad.nombre as especialidad','historia.numero as historia2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres) as doctor'))->orderBy('Venta.fecha', 'ASC')->orderBy(DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres)'),'asc')->orderBy('Venta.horainicio','ASC');
        $lista            = $resultado->get();
        if (count($lista) > 0) {            
            $pdf = new TCPDF();
            $pdf::SetTitle('Lista de Pacientes');
            $pdf::AddPage();
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(0,10,utf8_decode("LISTA DE VentaS"),0,0,'C');
            $pdf::Ln();
            $iddoctorant=0;
            foreach ($lista as $key => $value){
                if($iddoctorant!=$value->doctor_id){
                    if($iddoctorant>0){
                        $pdf::Ln();
                    }

                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(17,9,utf8_decode("FECHA:"),0,0,'L');
                    $pdf::SetFont('helvetica','',10);
                    $pdf::Cell(20,9,utf8_decode($value->fecha),0,0,'L');
                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(20,9,utf8_decode("DOCTOR:"),0,0,'L');
                    $pdf::SetFont('helvetica','',10);
                    $pdf::Cell(55,9,($value->doctor),0,0,'L');
                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(30,9,utf8_decode("ESPECIALIDAD:"),0,0,'L');
                    $pdf::SetFont('helvetica','',10);
                    $pdf::Cell(0,9,utf8_decode($value->especialidad),0,0,'L');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(60,6,utf8_decode("PACIENTE"),1,0,'C');
                    $pdf::Cell(20,6,utf8_decode("TIPO PAC."),1,0,'C');
                    $pdf::Cell(23,6,utf8_decode("TELEF."),1,0,'C');
                    $pdf::Cell(18,6,utf8_decode("HISTORIA"),1,0,'C');
                    $pdf::Cell(13,6,utf8_decode("INICIO"),1,0,'C');
                    $pdf::Cell(13,6,utf8_decode("FIN"),1,0,'C');
                    $pdf::Cell(50,6,utf8_decode("CONCEPTO"),1,0,'C');
                    $pdf::Ln();
                    $iddoctorant=$value->doctor_id;
                }
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(60,5,utf8_decode($value->paciente),1,0,'L');
                $pdf::Cell(20,5,utf8_decode($value->tipopaciente),1,0,'C');
                $pdf::Cell(23,5,utf8_decode($value->telefono),1,0,'C');
                $pdf::Cell(18,5,utf8_decode($value->historia),1,0,'C');
                $pdf::Cell(13,5,utf8_decode(substr($value->horainicio,0,5)),1,0,'C');
                $pdf::Cell(13,5,utf8_decode(substr($value->horafin,0,5)),1,0,'C');
                $pdf::Cell(50,5,utf8_decode($value->comentario),1,0,'L');
                $pdf::Ln();
            }
            $pdf::Output('ListaVenta.pdf');
        }
    }

    public function seleccionarventa(Request $request){
        $resultado        = Movimiento::where('id', '=', $request->input('id'))
                            ->where('ventafarmacia','=','N')
                            ->whereIn('situacion',['A','U']);
        $list      = $resultado->get();
        if(count($list)>0){
            echo "<h2>NO SE PUEDE GENERAR NOTA DE CREDITO A DOCUMENTO ANULADO</h2>";
        }else{
            $resultado = Detallemovcaja::join('movimiento as m','m.id','=','detallemovcaja.movimiento_id')
                         ->join('movimiento as m2','m2.movimiento_id','=','m.id')
                         ->where('m2.id','=',$request->input('id'))
                         ->select('detallemovcaja.*');
            $lista            = $resultado->get();
            $list="";
            if(count($lista)==0){//PARA CONVENIOS
                $resultado = Detallemovcaja::join('movimiento as m','m.id','=','detallemovcaja.movimiento_id')
                         ->where('m.id','=',$request->input('id'))
                         ->select('detallemovcaja.*','m.total');
                $lista            = $resultado->get();

                foreach ($lista as $key => $value) {
                    $list.=$value->id.",";
                    if(!is_null($value->servicio) && $value->servicio_id>0){
                        /*if($value->descripcion!="")
                            $servicio=$value->servicio->nombre;
                        else*/
                            $servicio=$value->descripcion;
                    }else{
                        $servicio=$value->descripcion;
                    }
                    echo "<tr id='tr".$value->id."'>";
                    echo "<td align='center'>".$value->cantidad."</td>";
                    echo "<td>".$servicio."</td>";
                    echo "<td align='center'>".number_format($value->precio,2,'.','')."</td>";
                    echo "<td align='center'><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' id='txtTotal".$value->id."' style='width: 70px;' value='".number_format(($value->cantidad * $value->precio),2,'.','')."'/></td>";
                    echo "<td><a href='#' onclick=\"quitarServicio('".$value->id."')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td>";
                    echo "</tr>";
                }
            }else{
                foreach ($lista as $key => $value) {
                    $list.=$value->id.",";
                    if($value->servicio_id>0){
                        $servicio=$value->servicio->nombre;
                    }else{
                        $servicio=$value->descripcion;
                    }
                    echo "<tr id='tr".$value->id."'>";
                    echo "<td align='center'>".$value->cantidad."</td>";
                    echo "<td>".$servicio."</td>";
                    echo "<td align='center'>".number_format($value->pagohospital,2,'.','')."</td>";
                    echo "<td align='center'><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' id='txtTotal".$value->id."' style='width: 70px;' value='".number_format(($value->cantidad * $value->pagohospital),2,'.','')."'/></td>";
                    echo "<td><a href='#' onclick=\"quitarServicio('".$value->id."')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td>";
                    echo "</tr>";
                }
            }
            echo "<input type='hidden' value='".substr($list,0,strlen($list)-1)."' id='listServicio' name='listServicio'/>";
        }
    }
    
    public function pdfComprobante(Request $request){
        $entidad          = 'Notacredito';
        $id               = Libreria::getParam($request->input('id'),'');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->where('movimiento.id', '=', $id);
        $resultado        = $resultado->select('movimiento.*','tipodocumento.nombre as tipodocumento');
        $lista            = $resultado->get();
        if (count($lista) > 0) {     
            foreach($lista as $key => $value){
                $pdf = new TCPDF();
                $pdf::SetTitle('Comprobante');
                $pdf::AddPage();
                $pdf::SetFont('helvetica','B',12);
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Image("http://localhost/juanpablo/dist/img/logo.jpg", 15, 5, 115, 30);
                $pdf::Cell(60,7,("RUC N° 20480082673"),'RTL',0,'C');
                $pdf::Ln();
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Cell(60,7,utf8_encode("NOTA DE CREDITO"),'RL',0,'C');
                $pdf::Ln();
                $pdf::Cell(130,7,"",0,0,'C');
                $pdf::Cell(60,7,("ELECTRÓNICA"),'RL',0,'C');
                $pdf::Ln();
                $pdf::Cell(130,7,"",0,0,'C');
                $subtotal=number_format($value->subtotal,2,'.','');
                $igv=number_format($value->igv,2,'.','');
                $movimiento = Movimiento::find($value->movimiento_id);
                if($movimiento->tipodocumento_id==4 || $movimiento->tipodocumento_id==17){//FACTURA
                    $dni=$value->persona->ruc;
                    $abreviatura="FC";
                }else{    
                    $abreviatura="BC";
                    if(is_null($value->persona) || strlen($value->persona->dni)<>8){
                        $dni='00000000';
                    }else{
                        $dni=$value->persona->dni;
                    }
                }
                $pdf::Cell(60,7,utf8_encode($abreviatura.str_pad($value->serie,2,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),'RBL',0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(0,7,utf8_decode("HOSPITAL PRIVADO JUAN PABLO II SOCIEDAD ANONIMA CERRADA"),0,0,'L');
                $pdf::Ln();
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,("Nombre / Razón Social: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                if($value->persona_id>0){
                    $pdf::Cell(110,6,(trim($value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                }else{
                    $pdf::Cell(110,6,(trim($value->nombrepaciente)),0,0,'L');
                }
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,utf8_encode($movimiento->tipodocumento_id==4?"RUC":($movimiento->tipodocumento_id==17?'RUC':"DNI")).": ",0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,utf8_encode($dni),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,("Dirección: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                if($value->persona_id>0){
                    $pdf::Cell(110,6,(trim($value->persona->direccion)),0,0,'L');
                }else{
                    $pdf::Cell(110,6,(trim('-')),0,0,'L');
                }
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,("Fecha de emisión: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,date("d/m/Y",strtotime($value->fecha)),0,0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(37,6,utf8_encode("Moneda: "),0,0,'L');
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(110,6,(trim('PEN - Sol')),0,0,'L');
                if($abreviatura=="FC"){
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(30,6,("Factura: "),0,0,'L');
                }else{
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(30,6,("Boleta: "),0,0,'L');
                }
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,($abreviatura=="FC"?"F":"B").str_pad($movimiento->serie,2,'0',STR_PAD_LEFT).'-'.str_pad($movimiento->numero,8,'0',STR_PAD_LEFT),0,0,'L');
                $pdf::Ln();
                if($movimiento->tipodocumento_id==4){
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(37,6,utf8_encode("Paciente: "),0,0,'L');
                    //$mov=Movimiento::find($movimiento->movimiento_id);
                    $mov=Movimiento::find($movimiento->id);
                    $pdf::SetFont('helvetica','',9);
                    $pdf::Cell(110,6,$mov->persona->apellidopaterno.' '.$mov->persona->apellidomaterno.' '.$mov->persona->nombres,0,0,'L');
                }else{
                    $pdf::Cell(147,6,(""),0,0,'L');
                }
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(30,6,("Fecha Ref: "),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(37,6,date("d/m/Y",strtotime($movimiento->fecha)),0,0,'L');
                $pdf::Ln();
                if($movimiento->tipodocumento_id==17){
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(37,6,("Paciente: "),0,0,'L');
                    $pdf::SetFont('helvetica','',9);
                    $pdf::Cell(110,6,(trim($movimiento->persona->apellidopaterno." ".$movimiento->persona->apellidomaterno." ".$movimiento->persona->nombres)),0,0,'L');
                    $pdf::SetFont('helvetica','B',9);
                    $pdf::Cell(30,6,("Historia: "),0,0,'L');
                    $pdf::SetFont('helvetica','',8);
                    $historia = Historia::where('person_id','=',$movimiento->persona_id)->first();
                    if(!is_null($historia))
                        $pdf::Cell(37,6,$historia->numero,0,0,'L');

                    $pdf::Ln();
                }    
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(10,7,("Item"),1,0,'C');
                $pdf::Cell(13,7,("Código"),1,0,'C');
                $pdf::Cell(108,7,("Descripción"),1,0,'C');
                $pdf::Cell(10,7,("Und."),1,0,'C');
                $pdf::Cell(15,7,("Cantidad"),1,0,'C');
                $pdf::Cell(20,7,("V. Unitario"),1,0,'C');
                //$pdf::Cell(20,7,("P. Unitario"),1,0,'C');
                //$pdf::Cell(20,7,("Descuento"),1,0,'C');
                $pdf::Cell(20,7,("Sub Total"),1,0,'C');
                $pdf::Ln();
                $resultado        = Detallemovcaja::leftjoin('servicio', 'servicio.id', '=', 'detallemovcaja.servicio_id')
                            ->where('detallemovcaja.movimiento_id', '=', $id)
                            ->select('detallemovcaja.*');
                $lista2            = $resultado->get();
                $c=0;
                foreach($lista2 as $key2 => $v){$c=$c+1;
                    $pdf::SetFont('helvetica','',7.5);
                    $pdf::Cell(10,7,$c,1,0,'C');
                    if($v->servicio_id>"0" && $v->descripcion==""){
                        if($v->servicio->tipopago=="Convenio"){
                            $codigo=$v->servicio->tarifario->codigo;
                            $nombre=$v->servicio->tarifario->nombre;    
                        }else{
                            $codigo="-";
                            if($v->servicio_id>"0"){
                                $nombre=$v->servicio->nombre;
                            }else{
                                $nombre=trim($v->descripcion);
                            }
                        }
                    }else{
                        $codigo="-";
                        $nombre=trim($v->descripcion);
                    }
                    if($movimiento->tipodocumento_id==17){
                        $precio=$v->precio;
                    }else{
                        $precio=$v->pagohospital;
                    }
                    $nombre.=" - ".substr($v->persona->nombres,0,1)." ".$v->persona->apellidopaterno;
                    $pdf::Cell(13,7,$codigo,1,0,'C');
                    $pdf::Cell(108,7,utf8_encode($nombre),1,0,'L');
                    $pdf::Cell(10,7,("ZZ."),1,0,'C');
                    $pdf::Cell(15,7,number_format($v->cantidad,2,'.',''),1,0,'R');
                    if($igv>0){
                        $pdf::Cell(20,7,number_format($precio/1.18,2,'.',''),1,0,'R');
                        $pdf::Cell(20,7,number_format($precio*$v->cantidad/1.18,2,'.',''),1,0,'R');
                    }else{
                        $pdf::Cell(20,7,number_format($precio,2,'.',''),1,0,'R');
                        $pdf::Cell(20,7,number_format($precio*$v->cantidad,2,'.',''),1,0,'R');
                    }
                    //$pdf::Cell(20,7,number_format($precio,2,'.',''),1,0,'R');
                    //$pdf::Cell(20,7,("0.00"),1,0,'R');
                    //$pdf::Cell(20,7,number_format($precio*$v->cantidad,2,'.',''),1,0,'R');
                    $pdf::Ln();                    
                }

                $resultado = Detallemovimiento::where('movimiento_id','=',$value->id);
                $lista2            = $resultado->get();
                //dd($movimiento->copago);
                $c=0;
                foreach($lista2 as $key2 => $v){$c=$c+1;
                    $pdf::SetFont('helvetica','',7.5);
                    $pdf::Cell(10,7,$c,1,0,'C');
                    $dscto = 0;
                    $subtotal2 = 0;
                    if ($movimiento->conveniofarmacia_id !== null) {
                        $valaux = round(($v->precio*$v->cantidad), 2);
                        $precioaux = $v->precio - ($v->precio*($movimiento->descuentokayros/100));
                        $dscto = round(($precioaux*$v->cantidad),2);
                        $subtotal2 = round(($dscto*($movimiento->copago/100)),2);
                    }else{
                        $subtotal2 = round(($v->precio*$v->cantidad), 2);
                    }
                    $pdf::Cell(13,7,"-",1,0,'C');
                    $pdf::Cell(108,7,$v->producto->nombre,1,0,'L');
                    $pdf::Cell(10,7,("NIU."),1,0,'C');
                    $pdf::Cell(15,7,number_format($v->cantidad,2,'.',''),1,0,'R');
                    $pdf::Cell(20,7,number_format($subtotal2 / $v->cantidad,2,'.',''),1,0,'R');
                    //$pdf::Cell(20,4,number_format($dscto,2,'.',''),0,0,'C');
                    $pdf::Cell(20,7,number_format($subtotal2,2,'.',''),1,0,'R');
                    $pdf::Ln('4');               
                }
                $letras = new EnLetras();
                $pdf::SetFont('helvetica','B',8);
                $valor=$letras->ValorEnLetras($value->total, "SOLES" );//letras
                
                $pdf::Cell(116,5,utf8_decode($valor),0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(40,5,utf8_decode('Op. Gravada'),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                if($igv>0)
                    $pdf::Cell(20,5,$subtotal,0,0,'R');
                else
                    $pdf::Cell(20,5,'0.00',0,0,'R');
                $pdf::Ln();
                $pdf::Cell(116,5,'',0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(40,5,utf8_decode('I.G.V'),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                $pdf::Cell(20,5,$igv,0,0,'R');
                $pdf::Ln();
                $pdf::Cell(116,5,'',0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(40,5,utf8_decode('Op. Inafecta'),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                if($igv>0)
                    $pdf::Cell(20,5,'0.00',0,0,'R');
                else
                    $pdf::Cell(20,5,$subtotal,0,0,'R');
                $pdf::Ln();
                $pdf::Cell(116,5,'',0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(40,5,utf8_decode('Op. Exonerada'),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                $pdf::Cell(20,5,'0.00',0,0,'R');
                $pdf::Ln();
                $pdf::Cell(116,5,'',0,0,'L');
                $pdf::Cell(40,5,'',0,0,'L');
                $pdf::Cell(20,5,'',0,0,'C');
                $pdf::Cell(20,5,'----------------------',0,0,'R');
                $pdf::Ln();
                $pdf::Cell(116,5,'',0,0,'L');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(40,5,utf8_decode('Importe Total'),0,0,'L');
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(20,5,utf8_decode('PEN'),0,0,'C');
                $pdf::Cell(20,5,number_format($value->total,2,'.',''),0,0,'R');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(195,5,'Motivo o Sustento:'.$value->comentario,'LRT',0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(195,5,$value->comentario,'LRB',0,'L');
                $pdf::Ln();
                $pdf::Ln('3');
                $pdf::SetFont('helvetica','B',8);
                $pdf::Cell(195,5,'Observaciones de SUNAT:','LRT',0,'L');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(195,5,'','LRB',0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(0,5,('Autorizado a ser emisor electrónico mediante R.I. SUNAT Nº 0340050004781'),0,0,'L');
                $pdf::Ln();
                $pdf::Cell(0,5,'Usuario: '.$value->responsable->nombres,0,0,'R');
                $pdf::Ln();
                $pdf::SetFont('helvetica','',8);
                $pdf::Cell(160,5,('Representación Impresa de la Factura Electrónica, consulte en https://sfe.bizlinks.com.pe'),0,0,'L');
                $pdf::Cell(0,5,$value->created_at,0,0,'R');
                $pdf::Ln();
                $pdf::Output('Comprobante.pdf');
            }
        }
    }

    public function pdfComprobante2(Request $request){
        $entidad          = 'Notacredito';
        $id               = Libreria::getParam($request->input('id'),'');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                            ->where('movimiento.id', '=', $id);
        $resultado        = $resultado->select('movimiento.*','tipodocumento.nombre as tipodocumento');
        $lista            = $resultado->get();
        if (count($lista) > 0) {     
            foreach($lista as $key => $value){
                if ($request->ip()=='192.168.1.20') {//FARMACIA
                    $pdf = new TCPDF();
                    //$pdf = new TCPDF("P",'mm',array(354,350),true,'UTF-8',false);
                    $pdf::SetTitle('Comprobante');
                    $pdf::AddPage('');
                    $pdf::SetFont('helvetica','B',12);
                    $pdf::Ln();
                    $pdf::Cell(180,6,"",0,0,'C');
                    $subtotal=number_format($value->total/1.18,2,'.','');
                    $igv=number_format($value->total - $subtotal,2,'.','');
                    $movimiento = Movimiento::find($value->movimiento_id);
                    $numeroref=str_pad($movimiento->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($movimiento->numero,8,'0',STR_PAD_LEFT);
                    $fecha=$movimiento->fecha;
                    if($movimiento->tipodocumento_id==4){//FACTURA
                        $dni=$value->persona->ruc;
                        $persona=$value->persona->bussinesname;
                        $abreviatura="FC";
                        $abreviatura1="F";
                    }else{    
                        $abreviatura="BC";
                        $abreviatura1="B";
                        if(is_null($value->persona_id)){
                            $persona=$value->nombrepaciente;
                            $dni='00000000';
                        }else{    
                            $persona=$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                            if(is_null($value->persona->dni) || strlen($value->persona->dni)<>8){
                                $dni='00000000';
                            }else{
                                $dni=$value->persona->dni;
                            }
                        }
                    }
                    $pdf::setXY(120,60);
                    $pdf::Cell(60,4,utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',12);
                    //$pdf::Cell(35,4,"RAZON SOCIAL: ",0,0,'L');
                    //$pdf::Cell(180,4,(trim($value->persona->bussinesname." ".$value->persona->apellidopaterno." ".$value->persona->apellidomaterno." ".$value->persona->nombres)),0,0,'L');
                    $pdf::Ln();
                    //$pdf::Cell(35,4,utf8_encode($movimiento->tipodocumento_id==4?"RUC":"DNI".": "),0,0,'L');
                    //$pdf::Cell(180,4,$dni,0,0,'L');
                    $pdf::setX(120);
                    $pdf::Cell(35,4,$persona,0,0,'L');
                    $pdf::Ln();
                    //$pdf::Cell(35,4,"DIRECCION: ",0,0,'L');
                    //$pdf::Cell(180,4,(trim($value->persona->direccion)),0,0,'L');
                    $pdf::setX(120);
                    $pdf::Cell(35,4,$numeroref,0,0,'L');
                    $pdf::Ln();
                    //$pdf::Cell(35,4,"FECHA: ",0,0,'L');
                    //$pdf::Cell(180,4,date("d/m/Y",strtotime($value->fecha)),0,0,'L');
                    $pdf::setX(120);
                    $pdf::Cell(180,4,date("d/m/Y",strtotime($fecha)),0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(0,4,$value->responsable->nombres,0,0,'R');
                    $pdf::Ln();
                    $pdf::setX(0);
                    $pdf::Cell(5,7,"",0,0,'C');
                    $pdf::Cell(15,7,("Cant."),0,0,'C');
                    $pdf::Cell(120,7,("Descripción"),0,0,'C');
                    $pdf::Cell(20,7,("P. Unitario"),0,0,'C');
                    $pdf::Cell(20,7,("Dscto"),0,0,'C');
                    $pdf::Cell(20,7,("Sub Total"),0,0,'C');
                    $pdf::Ln();
                    $resultado = Detallemovimiento::where('movimiento_id','=',$value->id);
                    $lista2            = $resultado->get();
                    $c=0;
                    foreach($lista2 as $key2 => $v){$c=$c+1;

                        $dscto = 0;
                        $subtotal2 = 0;
                        if ($movimiento->conveniofarmacia_id !== null) {
                            $valaux = round(($v->precio*$v->cantidad), 2);
                            $precioaux = $v->precio - ($v->precio*($movimiento->descuentokayros/100));
                            $dscto = round(($precioaux*$v->cantidad),2);
                            $subtotal2 = round(($dscto*($movimiento->copago/100)),2);
                        }else{
                            $subtotal2 = round(($v->precio*$v->cantidad), 2);
                        }
                        $pdf::setX(0);
                        $pdf::SetFont('helvetica','B',11);
                        $pdf::Cell(5,4,"",0,0,'C');
                        $pdf::Cell(15,4,number_format($v->cantidad,2,'.',''),0,0,'C');
                        $pdf::Cell(120,4,$v->producto->nombre,0,0,'L');
                        $pdf::Cell(20,4,number_format($v->precio,2,'.',''),0,0,'C');
                        $pdf::Cell(20,4,number_format($dscto,2,'.',''),0,0,'C');
                        $pdf::Cell(20,4,number_format($subtotal2,2,'.',''),0,0,'C');
                        $pdf::Ln('4');               
                    }
                    $pdf::Ln();
                    $letras = new EnLetras();
                    $valor=$letras->ValorEnLetras($value->total, "SOLES" );//letras
                    $pdf::Cell(15,7,"",0,0,'C');
                    $pdf::Cell(125,5,utf8_decode($valor),0,0,'L');
                    $pdf::SetFont('helvetica','B',12);
                    $pdf::Cell(20,7,"SUBTOTAL: ",0,0,'L');
                    $pdf::Cell(30,5,"S/. ".number_format($subtotal,2,'.',''),0,0,'R');
                    $pdf::Ln();
                    $pdf::Cell(140,5,'',0,0,'L');
                    $pdf::Cell(20,7,"IGV: ",0,0,'L');
                    $pdf::Cell(30,5,"S/. ".$igv,0,0,'R');
                    $pdf::Ln();
                    $pdf::Cell(140,5,'',0,0,'L');
                    $pdf::Cell(20,7,"TOTAL: ",0,0,'L');
                    $pdf::Cell(30,5,"S/. ".number_format($value->total,2,'.',''),0,0,'R');
                    $pdf::Ln();

                    $pdf::Output('Notacredito.pdf');
                }else{
                    $pdf = new TCPDF();
                    $pdf::SetTitle('Comprobante');
                    $pdf::AddPage('L');
                    $pdf::SetFont('helvetica','B',11);
                    $pdf::Ln();
                    $pdf::Cell(180,6,"",0,0,'C');
                    $subtotal=number_format($value->total/1.18,2,'.','');
                    $igv=number_format($value->total - $subtotal,2,'.','');
                    $movimiento = Movimiento::find($value->movimiento_id);
                    $numeroref=str_pad($movimiento->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($movimiento->numero,8,'0',STR_PAD_LEFT);
                    $fecha=$movimiento->fecha;
                    if($movimiento->tipodocumento_id==4){//FACTURA
                        $dni=$value->persona->ruc;
                        $persona=$value->persona->bussinesname;
                        $direccion=$value->persona->direccion;
                        $abreviatura="FC";
                        $abreviatura1="F";
                    }else{    
                        $abreviatura="BC";
                        $abreviatura1="B";
                        if(is_null($value->persona_id)){
                            $persona=$value->nombrepaciente;
                            $dni='00000000';
                            $direccion='-';
                        }else{ 
                            $persona=$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                            $direccion=$value->persona->direccion;
                            if(strlen($value->persona->dni)<>8){
                                $dni='00000000';
                            }else{
                                $dni=$value->persona->dni;
                            }
                        }
                    }
                    $pdf::Cell(60,4,utf8_encode(str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)),0,0,'C');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','B',11);
                    $pdf::Cell(35,4,"RAZON SOCIAL: ",0,0,'L');
                    $pdf::Cell(180,4,(trim($persona)),0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(35,4,utf8_encode($movimiento->tipodocumento_id==4?"RUC":"DNI".": "),0,0,'L');
                    $pdf::Cell(180,4,$dni,0,0,'L');
                    $pdf::Cell(35,4,$persona,0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(35,4,"DIRECCION: ",0,0,'L');
                    $pdf::Cell(180,4,(trim($direccion)),0,0,'L');
                    $pdf::Cell(35,4,$numeroref,0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(35,4,"FECHA: ",0,0,'L');
                    $pdf::Cell(180,4,date("d/m/Y",strtotime($value->fecha)),0,0,'L');
                    $pdf::Cell(180,4,date("d/m/Y",strtotime($fecha)),0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(0,4,$value->responsable->nombres,0,0,'R');
                    $pdf::Ln();
                    $pdf::Cell(5,7,"",0,0,'C');
                    $pdf::Cell(15,7,("Cant."),0,0,'C');
                    $pdf::Cell(180,7,("Descripción"),0,0,'C');
                    $pdf::Cell(30,7,("P. Unitario"),0,0,'C');
                    $pdf::Cell(30,7,("Sub Total"),0,0,'C');
                    $pdf::Ln();
                    $resultado        = Detallemovcaja::leftjoin('servicio', 'servicio.id', '=', 'detallemovcaja.servicio_id')
                                ->where('detallemovcaja.movimiento_id', '=', $id)
                                ->select('detallemovcaja.*');
                    $lista2            = $resultado->get();
                    $c=0;
                    foreach($lista2 as $key2 => $v){$c=$c+1;
                        $pdf::Cell(5,4,"",0,0,'C');
                        if($v->servicio_id>"0"){
                            if($v->servicio->tipopago=="Convenio"){
                                $codigo=$v->servicio->tarifario->codigo;
                                $nombre=$v->servicio->tarifario->nombre;    
                            }else{
                                $codigo="-";
                                if($v->servicio_id>"0"){
                                    $nombre=$v->servicio->nombre;
                                }else{
                                    $nombre=trim($v->descripcion);
                                }
                            }
                        }else{
                            $codigo="-";
                            $nombre=trim($v->descripcion);
                        }
                        $pdf::Cell(15,4,number_format($v->cantidad,2,'.',''),0,0,'C');
                        $nombre.=" - ".substr($v->persona->nombres,0,1)." ".$v->persona->apellidopaterno;
                        if(strlen($nombre)<80){
                            $pdf::Cell(180,4,utf8_encode($nombre),0,0,'L');
                        }else{
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(180,2,utf8_encode($nombre),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(180,4,"",0,0,'L');
                        }
                        $pdf::Cell(30,4,number_format($v->pagohospital,2,'.',''),0,0,'R');
                        $pdf::Cell(30,4,number_format($v->pagohospital*$v->cantidad,2,'.',''),0,0,'R');
                        $pdf::Ln('4');                    
                    }
                    //PARA MOSTRAR DATOS DE FARMACIA
                    $resultado = Detallemovimiento::where('movimiento_id','=',$value->id);
                    $lista2            = $resultado->get();
                    $c=0;
                    foreach($lista2 as $key2 => $v){$c=$c+1;

                        $dscto = 0;
                        $subtotal2 = 0;
                        if ($movimiento->conveniofarmacia_id !== null) {
                            $valaux = round(($v->precio*$v->cantidad), 2);
                            $precioaux = $v->precio - ($v->precio*($movimiento->descuentokayros/100));
                            $dscto = round(($precioaux*$v->cantidad),2);
                            $subtotal2 = round(($dscto*($movimiento->copago/100)),2);
                        }else{
                            $subtotal2 = round(($v->precio*$v->cantidad), 2);
                        }
                       // $pdf::setX(0);
                        $pdf::SetFont('helvetica','B',11);
                        $pdf::Cell(5,4,"",0,0,'C');
                        $pdf::Cell(15,4,number_format($v->cantidad,2,'.',''),0,0,'C');
                        $pdf::Cell(150,4,$v->producto->nombre,0,0,'L');
                        $pdf::Cell(30,4,number_format($v->precio,2,'.',''),0,0,'C');
                        $pdf::Cell(30,4,number_format($dscto,2,'.',''),0,0,'C');
                        $pdf::Cell(30,4,number_format($subtotal2,2,'.',''),0,0,'C');
                        $pdf::Ln('4');               
                    }

                    $pdf::Ln();
                    $letras = new EnLetras();
                    $valor=$letras->ValorEnLetras($value->total, "SOLES" );//letras
                    $pdf::Cell(15,7,"",0,0,'C');
                    $pdf::Cell(195,5,utf8_decode($valor),0,0,'L');
                    $pdf::SetFont('helvetica','B',11);
                    $pdf::Cell(20,7,"SUBTOTAL: ",0,0,'L');
                    $pdf::Cell(30,5,"S/. ".number_format($subtotal,2,'.',''),0,0,'R');
                    $pdf::Ln();
                    $pdf::Cell(210,5,'',0,0,'L');
                    $pdf::Cell(20,7,"IGV: ",0,0,'L');
                    $pdf::Cell(30,5,"S/. ".$igv,0,0,'R');
                    $pdf::Ln();
                    $pdf::Cell(210,5,'',0,0,'L');
                    $pdf::Cell(20,7,"TOTAL: ",0,0,'L');
                    $pdf::Cell(30,5,"S/. ".number_format($value->total,2,'.',''),0,0,'R');
                    $pdf::Ln();

                    $pdf::Output('Comprobante.pdf');
                }
                
            }
        }
    }

    public function procesar(Request $request)
    {
        $error = DB::transaction(function() use($request){
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',6);
            if($request->input('fechainicial')!=""){
                $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
            }
            if($request->input('fechafinal')!=""){
                $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
            }        
            if($request->input('numero')!=""){
                $resultado = $resultado->where('movimiento.numero','LIKE','%'.$request->input('numero').'%');
            }        

            $resultado        = $resultado->select('movimiento.*','m2.tipodocumento_id as tipodocumento_id2')->orderBy('movimiento.fecha', 'ASC');
            $lista            = $resultado->get();
            foreach ($lista as $key => $value) {
                $numero=($value->tipodocumento_id2==5?"BC":"FC").str_pad($value->serie,2,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT);

                $dias_trascurridos = date_diff(date_create($value->fecha),date_create())->days;
                if(substr($numero, 0, 1) == "B" && $dias_trascurridos <= 7){
                    //dd($numero,$dias_trascurridos);
                    $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICE_RESPONSE')->where('serieNumero','like',$numero)->where("bl_estadoRegistro","=","L")->count("*");
                    //dd($rs);
                    if($rs>0){
                        DB::connection('sqlsrvtst21')->delete("delete from SPE_EINVOICE_RESPONSE where serieNumero in (?)",[$numero]); 
                        DB::connection('sqlsrvtst21')->update("update SPE_EINVOICEHEADER set bl_estadoRegistro='A',bl_reintento=0 where serieNumero in (?)",[$numero]); 
                    }
                }

                //if($value->situacionsunat!="E"){
                    // dd($numero);
                    $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICEHEADER')->where('serieNumero','like','%'.$numero.'%')->first();
                    // dd($rs);
                    if(count($rs)>0){
                        $value->situacionbz=$rs->bl_estadoRegistro;
                        if($rs->bl_estadoRegistro==='E'){
                            $value->situacionsunat='E';    
                        }
                        // else{          
                        //     $value->situacionsunat = $rs->bl_estadoRegistro;             
                        // }
                    }
                    $rs=DB::connection('sqlsrvtst21')->table('SPE_EINVOICE_RESPONSE')->where('serieNumero','like',$numero)->first();
                    if(count($rs)>0){
                        $value->situacionsunat=$rs->bl_estadoRegistro;
                        $value->mensajesunat=$rs->bl_mensajeSunat;
                    }
                    $value->save();

                //}
            }
        });
        return is_null($error) ? "OK" : $error;
    }

    public function excel(Request $request)
    {
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',6);
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial').' 00:00:00');
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal').' 23:59:59');
        }              
        if($request->input('numero')!=""){
            $resultado = $resultado->where('movimiento.numero','LIKE','%'.$request->input('numero').'%');
        }        
        if($request->input('numeroref')!=""){
            $resultado = $resultado->where(DB::raw('CONCAT(case when m2.tipodocumento_id=4 then "F" else "B" end,m2.serie,"-",m2.numero)'),'LIKE','%'.$request->input('numeroref').'%');
        }
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }else{
            $resultado = $resultado->whereNotIn('movimiento.situacion',['U','A']);       
        }
        
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2',DB::raw('CONCAT(case when m2.tipodocumento_id=4 or m2.tipodocumento_id=17 then "F" else "B" end,m2.serie,"-",m2.numero) as numeroref'),'m2.fecha as fecha_doc', DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.bussinesname','m2.tipodocumento_id as tipodocumento_id2')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.numero', 'ASC');
        $resultado        = $resultado->get();

        // dd($resultado);

        Excel::create('NotasCredito', function($excel) use($resultado,$request) {
 
            $excel->sheet('NotasCredito', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Nº";
                $cabecera[] = "Fecha";
                $cabecera[] = "Paciente";
                $cabecera[] = "Tipo Doc.";
                $cabecera[] = "Serie";
                $cabecera[] = "Numero";
                $cabecera[] = "Fecha Ref";
                $cabecera[] = "Nro Ref";
                // $cabecera[] = "Paciente";
                $cabecera[] = "Total";
                $cabecera[] = "Estado";
                $cabecera[] = "Estado BZ";
                $cabecera[] = "Estado SUNAT";
                $cabecera[] = "Msg SUNAT";
                $cabecera[] = "Motivo Anulación";
                $cabecera[] = "Usuario";
                
                $array[] = $cabecera;
                $c=3;$d=3;$band=true;$nro=1;

                foreach ($resultado as $key => $value){
                    // dd($value);
                    if($value->situacion!="U"){//NO ANULADAS
                        $detalle = array();
                        $detalle[] = $nro;
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        // $detalle[] = $value->paciente2;
                        if($value->tipodocumento_id2==5){
                            $detalle[] = $value->paciente2;
                        } else{
                            $detalle[] = $value->bussinesname;
                        }
                        $detalle[] = $value->tipodocumento_id==5?'BOLETA':'FACTURA';
                        $detalle[] = str_pad($value->serie,4,'0',STR_PAD_LEFT);
                        $detalle[] = str_pad($value->numero,4,'0',STR_PAD_LEFT);
                        $detalle[] = date('d/m/Y', strtotime($value->fecha_doc));
                        $detalle[] = $value->numeroref;                        
                    
                        $detalle[] = $value->total;
                        $detalle[] = $value->situacion;
                        if($value->situacionbz=='L'){
                            $detalle[] = "LEIDO";
                        }elseif($value->situacionbz=='E'){
                            $detalle[] = "ERROR";
                        }else{
                            $detalle[] = "PENDIENTE";
                        }
                        if($value->situacionsunat=='L'){
                            $detalle[] = "PENDIENTE RESPUESTA";
                        }elseif($value->situacionsunat=='E'){
                            $detalle[] = "ERROR";
                        }elseif($value->situacionsunat=='R'){
                            $detalle[] = "RECHAZADO";
                        }elseif($value->situacionsunat=='P'){
                            $detalle[] = "ACEPTADO";
                        }else{
                            $detalle[] = "PENDIENTE";
                        }
                        
                        $detalle[] = $value->mensajesunat;
                        $detalle[] = $value->comentario;
                        $detalle[] = $value->responsable->nombres;

                        $c=$c+1;
                        $array[] = $detalle;
                    }
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }
}
