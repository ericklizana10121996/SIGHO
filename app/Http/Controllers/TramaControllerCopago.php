<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Convenio;
use App\Movimiento;
use App\Detallemovcaja;
use App\Person;
use App\Cie;
use App\Tiposervicio;
use App\Servicio;
use App\Plan;
use App\Tarifario;
use App\TarifarioSusalud;
use App\Detalleplan;
use App\Lotetrama;
use App\Detallelote;
use App\Librerias\Libreria;
use App\Librerias\EnLetras;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\Settings;

class TramaControllerCopago extends Controller
{

    protected $folderview      = 'app.trama';
    protected $tituloAdmin     = 'Generación de Trama con Copago';
    protected $tituloRegistrar = 'Registrar trama';
    protected $tituloModificar = 'Modificar trama';
    protected $tituloEliminar  = 'Eliminar trama';
    protected $rutas           = array('raiz'=>'tramagcop',
            'nueva' => 'tramacop.nueva', 
            'edit'   => 'tramacop.edit', 
            'delete' => 'tramacop.eliminar',
            'search' => 'tramacop.buscar',
            'generar' => 'tramacop.generar',
            'index'  => 'tramacop.index',
            'buscar' => 'tramacop.listarD',
            'listarD' => 'tramacop.listarD'
        );

    public function __construct(){
        $this->middleware('auth');
    }

    public function buscar(Request $request){
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'tramacop';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = trama::where('descripcion', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('descripcion', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
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

    public function index(){
        $entidad          = 'tramacop';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    public function nueva(){
        $resultado        = Lotetrama::orderBy('created_at','desc')->first();
        $resultado        = $resultado->get();
        $direc = "tramagcop";
        foreach ($resultado as $key => $value){
            $lote = $value->numero;
            $lote++;
        }
        return view($this->folderview.'.nueva')->with(compact('lote','direc'));
    }

    public function listarD(Request $request){
        $plan           = $request->input('plan');
        $fechainicial   = $request->input('fechainicial');
        $fechafinal     = $request->input('fechafinal');
        $miusuario     = $request->input('miusuario');
        $user = Auth::user();
        //$lista     = $request->input('lista');
        $stotal = 0;
        $resultado        = Movimiento::join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('cie as cie','cie.id','=','movimiento.cie_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as empresa','empresa.id','=','movimiento.empresa_id')
                            ->where('plan.nombre','like','%'.$plan.'%')
                            //->where('plan.nombre','NOT LIKE','%LA POSITIVA S.A EPS%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->where('movimiento.situacion','<>','U')
                            ->where('movimiento.situacion','<>','A')
                            ->whereNotNull('movimiento.cie_id')
                            // ->whereIn('movimiento.serie',[2])
                            // ->whereIn('movimiento.numero',[11512,11513,11514,11515,11516,11517,11518])
                            // ->whereIn('movimiento.numero',[9350,6105])
                            ->where('movimiento.fecha','>=',$fechainicial)
                            ->where('movimiento.fecha','<=',$fechafinal)
                            ;
        if($miusuario=="S"){
            $resultado->where('movimiento.responsable_id','=',$user->person_id)
                            ;
        }
                            
                            //LILIANA 31227
                            //MAYRA 57059
                            //JEANCARLOS 57058
                            //$user->person_id
                            //->whereIn('movimiento.numero',$lista);
        $resultado        = $resultado->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.id as movid','cie.codigo as cie10','movimiento.subtotal','movimiento.igv','movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),DB::raw('TIME(movimiento.created_at) as hora2'),'historia.tipopaciente','movimiento.copago',DB::raw('CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) as pacienten'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','paciente.dni',DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'empresa.bussinesname','empresa.direccion','empresa.ruc','movimiento.comentario','movimiento.fechaingreso','movimiento.fechaalta','cie.descripcion as cie','movimiento.soat');
        $lista2            = $resultado->get();
        //dd($resultado->toSql(),$plan,$fechainicial,$fechafinal);
        $errores = 0;
        $reporte_error = '';
        $response = '<table class="table table-hover"><thead><tr><th>Documento</th><th>Paciente</th><th>Siniestro</th><th>CIE10</th><th>Total</th><th><input type="checkbox" checked="" onclick="desmarcarTodos($(this).is(\':checked\'));"></th></tr></thead><tbody>';
        $response1 = '';
        $response2 = '';

        $lugar = 0;
        foreach ($lista2 as $key => $value){
            $cie10 = $value->cie10;
            $siniestro = $value->comentario;
            if($siniestro == '' || !isset($cie10)){
                $errores++;

                $link = 'http://localhost/juanpablo/facturacion/'.$value->movid.'/edit?listar=SI';

                $response2 = $response2.'<tr><td>'.$value->serie.'-'.$value->numero.'</td><td>'.$value->pacienten.'</td><td>'.$value->comentario.'</td><td>'.$cie10.'</td><td>'.$value->total.'</td><td> <button onclick="modal (\''.$link.'\', \'Modificar\', this);" class="btn btn-xs btn-warning" type="button"><div class="glyphicon glyphicon-pencil"></div> Editar</button> </td></tr>';
            } else {
                $response1 = $response1.'<tr><td>'.$value->serie.'-'.$value->numero.'</td><td>'.$value->pacienten.'</td><td>'.$value->comentario.'</td><td>'.$cie10.'</td><td>'.$value->total.'</td><td><input class="lista" type="checkbox" id="'.$value->movid.'" checked> </td></tr>';
            }
            $lugar++;
            $stotal += $value->total;
        }

        //ERRORES
        if ($errores != 0) {
            $reporte_error = $reporte_error.'<h5 style="color:red;position:absolute;right:120px;top:90px;">Se encontraron '.$errores.' facturas con datos faltantes, corregir los siguientes resultados:</h5>';
            $response = $response.''.$response2.''.$reporte_error;
        } else {
            $response = $response.''.$response1.'</tbody></table><div><h5 id="factotal" style="color:red;position:absolute;right:20px;top:0px;">'.$lugar.' FACTURAS</h5>
                <h4 id="sumatotal" style="color:red;position:absolute;right:20px;top:20px;">TOTAL: S/'.$stotal.'</h4></div>';
        }

        //NO HAY FACTURAS
        if ($lugar == 0) {
             $response = $response.'<tr><td colspan="4">No se encontraron facturas entre las fechas indicadas.</td></tr>';
        }

        return $response;
    }

    public function guardarLote($lista, $lote){
        $lotetrama       = new Lotetrama();
        $lotetrama->numero = $lote;
        $lotetrama->save();

        $nlote = Lotetrama::where('numero','=',$lote)->get();
        foreach ($nlote as $key => $value) {
            $lote_id = $value->lotetrama_id;
        }

        foreach ($lista as $key => $value) {
            $doc       = new Detallelote();
            $doc->movimiento_id = $value;
            $doc->lotetrama_id = $lote_id;
            $doc->save();
        }
    }

    public function edit(Request $request,$id){
        $existe = Libreria::verificarExistencia($id, 'trama');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $trama = trama::find($id);
        $entidad  = 'trama';
        $formData = array('trama.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('trama', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function destroy($id){
        $existe = Libreria::verificarExistencia($id, 'trama');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $trama = trama::find($id);
            $trama->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego){
        $existe = Libreria::verificarExistencia($id, 'trama');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = trama::find($id);
        $entidad  = 'trama';
        $mensaje = '¿Desea eliminar el trama "'.$modelo->nombre.'" ? <br><br>';
        $formData = array('route' => array('trama.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar','mensaje'));
    }

    public function generar(Request $request){
        $plan      = $request->input('plan');
        $lista     = $request->input('lista');
        $lote      = $request->input('lote');
        //$lote      = 131;
        $periodo = date("Ym");
        $lote = str_pad($lote, 7, "0", STR_PAD_LEFT);

        $ruc = "20480082673";
        $ipress = "00010972";
        if (strrpos($plan, "POSITIVA") !== FALSE ) {
            if (strrpos($plan, "EPS") !== FALSE ) {
                $v_iafa = 20029;
            } else {
                $v_iafa = 40005;
            }
        } else if(strrpos($plan, "RIMAC") !== FALSE ) {
            if (strrpos($plan, "EPS") !== FALSE ) {
                $v_iafa = 20001;
            } else {
                $v_iafa = 40007;
            }
        } else if(strrpos($plan, "PACIFICO") !== FALSE ) {
            if (strrpos($plan, "ENTIDAD PRESTADORA DE SALUD") !== FALSE ) {
                $v_iafa = 20002;
            } else {
                $v_iafa = 40004;
            }
          
        } else if (strrpos($plan, "BAN") !== FALSE ){
            $v_iafa = 30004;
        }

        $fecha = date("Ymd");
        $this->generarFac($lista,$periodo,$lote,$plan,$fecha,$ruc,$ipress,$v_iafa);
        $this->generarAte($lista,$periodo,$lote,$plan,$fecha,$ruc,$ipress,$v_iafa);
        $this->generarSer($lista,$periodo,$lote,$plan,$fecha,$ruc,$ipress,$v_iafa);
        $this->generarFar($lista,$periodo,$lote,$plan,$fecha,$ruc,$ipress,$v_iafa);
        $this->generarDen($lista,$periodo,$lote,$plan,$fecha,$ruc,$ipress,$v_iafa);
        $this->guardarLote($lista,$lote);
    }

    function generarFac($lista,$periodo,$lote,$plan,$fecha,$ruc,$ipress,$v_iafa){
        $myfile = fopen("trama/dfac_".$ruc."_".$ipress."_".$v_iafa."_".$lote."_".$periodo."_".$fecha.".txt", "w") or die("Unable to open file!");

        $resultado        = Movimiento::join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('cie as cie','cie.id','=','movimiento.cie_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as empresa','empresa.id','=','movimiento.empresa_id')
                            ->where('plan.nombre','like','%'.$plan.'%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->where('movimiento.situacion','<>','U')
                            ->where('movimiento.situacion','<>','A')
                            ->whereNotNull('movimiento.cie_id')
                            ->whereIn('movimiento.id',$lista);
        $resultado        = $resultado->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.subtotal','movimiento.igv','movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'movimiento.copago','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','empresa.bussinesname','empresa.direccion','empresa.ruc','movimiento.comentario','movimiento.fechaingreso','movimiento.fechaalta', DB::raw("(SELECT ROUND((movimiento.montoinicial * SUM(detallemovcaja.cantidad * (CASE WHEN movimiento.igv > 0 THEN detallemovcaja.precio / 1.18 ELSE detallemovcaja.precio END))) / (100 - movimiento.montoinicial), 2) FROM detallemovcaja WHERE detallemovcaja.descripcion NOT LIKE '%CONSULTA%' AND detallemovcaja.descripcion NOT LIKE '%CONS %' AND detallemovcaja.movimiento_id = movimiento.id) AS copagovariable"));
        $lista            = $resultado->get();
        $contador = 0;

        foreach ($lista as $key => $value){
            $contador != 0 ? $txt = "\r\n" : $txt = '';
            $v_fecha = date('Ymd',strtotime($value->fecha));
            $v_hora = date("His");
            $v_numero = "F".str_pad($value->serie, 3, "0", STR_PAD_LEFT)."".str_pad($value->numero,8, "0", STR_PAD_LEFT);
            if($v_iafa != 20001 && $v_iafa != 40007){
                $v_producto = "99999";
            } else {
                $v_producto = "S    ";  
            }
            //floor($value->cantidad)
            $v_cantidad = str_pad(1, 5, " ", STR_PAD_RIGHT);
            $v_iafa == 40005 ? $v_mecanismo = "06" :  $v_mecanismo = "01";
            $v_iafa == 40005 ? $v_fecha2 = $v_fecha : $v_fecha2 = "        ";
            $v_subtipo = "999";

            $v_prepactado = str_pad(number_format($value->subtotal,2,'.',''), 12, " ", STR_PAD_LEFT);
            //str_pad(number_format((($value->precio)*$value->cantidad)/1.18,2,'.',''), 12, " ", STR_PAD_LEFT);
            $v_gastoexonerados = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);

            //SE COMENTO POR INGRESO DE LOS COPAGOS FIJOS Y VARIABLES 2019-03-18
            /*if ($v_iafa == 30004) {
                $v_copagofijo_afecto = str_pad(number_format($value->copago,2,'.',''), 12, " ", STR_PAD_LEFT);
            } else {
                $v_copagofijo_afecto = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            }*/
            if ($v_iafa == 30004 || $v_iafa == 20001 || TRUE) {
                $v_copagofijo_afecto = str_pad(number_format($value->copago,2,'.',''), 12, " ", STR_PAD_LEFT);
            } else {
                $v_copagofijo_afecto = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            }

            if($v_iafa == 20001 || TRUE){
                $v_copagofijo_exonerado = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_copagovariable_afecto = str_pad(number_format($value->copagovariable,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_copagovariable_exonerado = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            }else{
                $v_copagofijo_exonerado = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_copagovariable_afecto = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_copagovariable_exonerado = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            }

            if($value->igv == 0){
                $v_copagofijo_exonerado = $v_copagofijo_afecto;
                $v_copagofijo_afecto = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_copagovariable_exonerado = $v_copagovariable_afecto;
                $v_copagovariable_afecto = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_prepactado = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            }

            
            //$v_montoneto = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            $v_igv = str_pad(number_format($value->igv,2,'.',''), 12, " ", STR_PAD_LEFT);
            $v_total = str_pad(number_format($value->total,2,'.',''), 12, " ", STR_PAD_LEFT);
            //$v_sinigv = str_pad(number_format((($v_total-$v_igv)),2,'.',''), 12, " ", STR_PAD_LEFT);
            $v_sinigv = str_pad(number_format((($value->subtotal + $value->copago + $value->copagovariable)),2,'.',''), 12, " ", STR_PAD_LEFT);
            if($value->igv == 0){
                $v_gastoexonerados = $v_sinigv;
                $v_sinigv = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            }
            $v_tipo = "N";
            $v_nota = str_pad(" ", 41, " ", STR_PAD_LEFT);
            if($v_iafa == 20001 || TRUE || TRUE){
                //$v_sinigv = $v_sinigv + $v_copagofijo_afecto + $v_copagofijo_exonerado + $v_copagovariable_afecto + $v_copagovariable_exonerado;
                //$v_sinigv = str_pad(number_format($v_sinigv,2,'.',''), 12, " ", STR_PAD_LEFT);
            }
            $txt = $txt."".$fecha."".$v_hora."".$lote."".$v_iafa."".$ruc."".$ipress."01".$v_numero."".$v_fecha."".$v_producto."".$v_cantidad."".$v_mecanismo."".$v_subtipo."".$v_sinigv."".$v_fecha2."1".$v_gastoexonerados."".$v_copagofijo_afecto."".$v_copagofijo_exonerado."".$v_copagovariable_afecto."".$v_copagovariable_exonerado."".$v_prepactado."".$v_igv."".$v_total."".$v_tipo."".$v_nota."N";
            $contador++;
            fwrite($myfile, $txt);
        }
        
        fclose($myfile);
    }

    function generarAte($lista,$periodo,$lote,$plan,$fecha,$ruc,$ipress,$v_iafa){
        $myfile = fopen("trama/date_".$ruc."_".$ipress."_".$v_iafa."_".$lote."_".$periodo."_".$fecha.".txt", "wt") or die("Unable to open file!");
        $resultado        = Movimiento::join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('cie as cie','cie.id','=','movimiento.cie_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as empresa','empresa.id','=','movimiento.empresa_id')
                            ->where('plan.nombre','like','%'.$plan.'%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->whereNotNull('movimiento.cie_id')
                            ->where('movimiento.situacion','<>','U')
                            ->where('movimiento.situacion','<>','A')
                            ->whereIn('movimiento.id',$lista);
        $resultado        = $resultado->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.id as movid','cie.codigo as cie10','movimiento.subtotal','movimiento.igv','movimiento.total',DB::raw('plan.nombre as plan'),'historia.carnet','movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),DB::raw('TIME(movimiento.created_at) as hora2'),'historia.tipopaciente','movimiento.copago','movimiento.montoinicial',DB::raw('historia.numero as historia'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','paciente.dni',DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'empresa.bussinesname','empresa.direccion','empresa.ruc','movimiento.comentario','movimiento.fechaingreso','movimiento.fechaalta','cie.descripcion as cie','movimiento.soat', DB::raw("(SELECT ROUND((movimiento.montoinicial * SUM(detallemovcaja.cantidad * (CASE WHEN movimiento.igv > 0 THEN detallemovcaja.precio / 1.18 ELSE detallemovcaja.precio END))) / (100 - movimiento.montoinicial), 2) FROM detallemovcaja WHERE detallemovcaja.descripcion NOT LIKE '%CONSULTA%' AND detallemovcaja.descripcion NOT LIKE '%CONS %' AND detallemovcaja.movimiento_id = movimiento.id) AS copagovariable"));
        $lista            = $resultado->get();
        $contador = 0;
        foreach ($lista as $key => $value){
        //if ($value->comentario != '' && $value->cie10 != '') {
            $id = $value->movid;
            
            //BUSCAR RESULTADOS DE LABORATORIO
            $blaboratorio = Detallemovcaja::leftjoin('servicio','detallemovcaja.servicio_id','=','servicio.id')->where('movimiento_id','=',$id);
            $blaboratorio = $blaboratorio->select('detallemovcaja.id','detallemovcaja.descripcion','servicio.tiposervicio_id','detallemovcaja.precio','detallemovcaja.cantidad','detallemovcaja.tarifariosusalud_id');
            $laboratorio = $blaboratorio->get();
            $total_lab   = 0;
            $total_farm  = 0;
            $total_hon   = 0;
            $total_hot   = 0;
            $total_img   = 0;
            $total_odont = 0;
            foreach ($laboratorio as $key => $valor){
                if($valor->servicio_id>"0"){
                    if(!is_null($valor->servicio) && $valor->servicio->tipopago=="Convenio"){
                        $nombre=trim($valor->descripcion);    
                    }else{
                        if(!is_null($valor->servicio) && $valor->servicio_id>"0"){
                            $nombre=$valor->servicio->nombre;
                        }else{
                            $nombre=trim($valor->descripcion);
                        }
                    }
                }else{
                    $nombre=trim($valor->descripcion);
                }
                $lpre = number_format(($valor->precio)/1.18,2,'.','');
                $lcan = floor($valor->cantidad);
                $ltotal = number_format(($lcan*$lpre),2,'.','');
                //dd($nombre);
                $tarif =TarifarioSusalud::where('codigoSusalud','=',$valor->tarifariosusalud_id)->select('codigoSusalud','nombreServicio','idRubro')->first();

                if($v_iafa == 20001 || TRUE){
                    if(!is_null($tarif)){
                        if(strpos($tarif->nombreServicio,'CONSULTA') === false && strpos($tarif->nombreServicio,'CONS ') === false) {
                            $ltotal = (100*$ltotal) / (100-$value->montoinicial);
                            $ltotal = round($ltotal,2);
                            $ltotal = number_format(($ltotal),2,'.','');
                            //echo "1";
                        }else{
                            $ltotal = $ltotal + $value->copago;
                            //dd($ltotal,$value);
                            //echo "2";
                        }
                        //$ltotal = number_format($ltotal,2,'.','');
                    }
                }

                if(!is_null($tarif)){
                    switch ($tarif->idRubro) {
                       case '1':
                            $total_hon+=$ltotal;
                            break;
                       case '3':
                            $total_hot+=$ltotal;
                            break;
                       case '4':
                            $total_lab+=$ltotal;
                            break;
                       case '5':
                            $total_img+=$ltotal;
                            break;
                       case '6':
                            $total_farm+=$ltotal;
                            break;
                       default:
                            $total_odont += $ltotal;
                            break;
                    }
                }else{
                    // $valor->tiposervicio_id == 2 ? $total_lab += $ltotal : $total_lab += 0;
                    // strpos($valor->descripcion, 'FARMACIA') !== false ? $total_farm += $ltotal : $total_farm += 0;

                }
                // $valor->tiposervicio_id == 2 ? $total_lab += $ltotal : $total_lab += 0;
                // strpos($valor->descripcion, 'FARMACIA') !== false ? $total_farm += $ltotal : $total_farm += 0;
            }
            //dd($total_lab,$total_farm);
            //////////////////////////////////

            $contador != 0 ? $txt = "\n" : $txt = '';
            $v_fecha = date('Ymd',strtotime($value->fechaingreso));
            $v_hora = date("His");
            $v_hora2 = date("His",strtotime($value->hora2));
            $v_numero = "F".str_pad($value->serie, 3, "0", STR_PAD_LEFT)."".str_pad($value->numero,8, "0", STR_PAD_LEFT);
            $v_codigo = str_pad(3, 10, " ", STR_PAD_RIGHT);
            $value->carnet != "" ? $carnet = $value->carnet : $carnet = "000000000";
            $v_cod_paciente = str_pad($carnet, 20, " ", STR_PAD_LEFT);
            $value->dni != '' ? $vdni = $value->dni : $vdni = 00000000;
            $v_dni = str_pad($vdni, 15, " ", STR_PAD_LEFT);
            $v_historia = str_pad($value->historia, 8, " ", STR_PAD_LEFT);
            $v_doc_autorizacion = str_pad($value->comentario, 20, " ", STR_PAD_RIGHT);
            $v_iafa == 40005 ? $v_doc_autorizacion2 = str_pad("0699", 22, " ", STR_PAD_RIGHT) : $v_doc_autorizacion2 = str_pad("99  ", 22, " ", STR_PAD_RIGHT);
            $v_cie10 = str_pad($value->cie10, 15, " ", STR_PAD_RIGHT);
            //floor($value->cantidad)
            $v_cantidad = str_pad(1, 5, " ", STR_PAD_RIGHT);
            //$v_total = str_pad(number_format($value->total,2,'.',''), 12, " ", STR_PAD_LEFT);
            //CAMBIO 2019-03-18
            if($v_iafa == 20001 || TRUE){
                $v_total = str_pad(number_format($value->total + $value->copago + $value->copagovariable,2,'.',''), 12, " ", STR_PAD_LEFT);
            }else{
                $v_total = str_pad(number_format($value->total,2,'.',''), 12, " ", STR_PAD_LEFT);
            }
            $v_igv = str_pad(number_format($value->igv,2,'.',''), 12, " ", STR_PAD_LEFT);
            $v_totalotro = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);

            if ($v_igv == 0) {
                $v_lab = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_farm = str_pad(number_format($v_total,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_sinigv = str_pad(number_format($v_total,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_honorarios = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            } else {
                $v_lab = str_pad(number_format($total_lab,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_farm = str_pad(number_format($total_farm,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_sinigv = str_pad(number_format((($v_total-$v_igv)),2,'.',''), 12, " ", STR_PAD_LEFT);
                //echo json_encode(array($v_total,$v_igv,$v_sinigv,$v_farm,$v_lab));exit();
                $v_honorarios = str_pad(number_format((($v_sinigv-$v_farm-$v_lab)),2,'.',''), 12, " ", STR_PAD_LEFT);
            }

            //NUEVO PROCEDIMIENTO 2019-03-18
            if($v_iafa == 20001 || TRUE){
                if ($v_igv == 0) {
                    $v_copagofijo_afecto = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                    $v_copagofijo_exonerado = str_pad(number_format($value->copago,2,'.',''), 12, " ", STR_PAD_LEFT);
                    $v_copagovariable_afecto = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                    $v_copagovariable_exonerado = str_pad(number_format($value->copagovariable,2,'.',''), 12, " ", STR_PAD_LEFT);
                }else{
                    $v_copagofijo_afecto = str_pad(number_format($value->copago,2,'.',''), 12, " ", STR_PAD_LEFT);
                    $v_copagofijo_exonerado = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                    $v_copagovariable_afecto = str_pad(number_format($value->copagovariable,2,'.',''), 12, " ", STR_PAD_LEFT);
                    $v_copagovariable_exonerado = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                }
            }else{
                $v_copagofijo_afecto = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_copagofijo_exonerado = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_copagovariable_afecto = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_copagovariable_exonerado = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            }

            if($v_igv == 0){ 
                $v_lab = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_farm = str_pad(number_format($v_total,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_sinigv = str_pad(number_format($v_total,2,'.',''), 12, " ", STR_PAD_LEFT);
                
                $v_odon = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_hot = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_img = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);

                $v_honorarios = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);

                $v_gastoexonerados = str_pad(number_format($v_honorarios + $v_lab + $v_farm,2,'.',''), 12, " ", STR_PAD_LEFT);
            }else{
                $v_lab = str_pad(number_format($total_lab,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_farm = str_pad(number_format($total_farm,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_sinigv = str_pad(number_format((($v_total-$v_igv)),2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_odon = str_pad(number_format($total_odont,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_hot = str_pad(number_format($total_hot,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_img = str_pad(number_format($total_img,2,'.',''), 12, " ", STR_PAD_LEFT);

                $v_honorarios = str_pad(number_format($total_hon,2,'.',''), 12, " ", STR_PAD_LEFT);
                
                $v_gastoexonerados = $v_totalotro;
            }

            $txt = $txt."".$ruc."".$ipress."01".$v_numero."".$v_cantidad."3         1".$v_cod_paciente."1".$v_dni."".$v_historia."01".$v_doc_autorizacion."".$v_doc_autorizacion2."4100 ".$v_cie10."".$v_fecha."".$v_hora2."00                      ".$ruc."                                    ".$v_honorarios."".$v_odon."".$v_hot."".$v_lab."".$v_img."".$v_farm."".$v_totalotro."".$v_gastoexonerados."".$v_totalotro."".$v_copagofijo_afecto."".$v_copagofijo_exonerado."".$v_copagovariable_afecto."".$v_copagovariable_exonerado."".$v_sinigv;
            $contador++;
            fwrite($myfile, $txt);
        //} else { return "N"; }
        }
        fclose($myfile); 
    }

    function generarSer($lista,$periodo,$lote,$plan,$fecha,$ruc,$ipress,$v_iafa){
        $myfile = fopen("trama/dser_".$ruc."_".$ipress."_".$v_iafa."_".$lote."_".$periodo."_".$fecha.".txt", "w") or die("Unable to open file!");
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('especialidad as es','es.id','=','medico.especialidad_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('tarifario as ta','ta.id','=','s.tarifario_id')
                            ->leftjoin('cie as cie','cie.id','=','movimiento.cie_id')
                            ->leftjoin('tiposervicio as ts','ts.id','=','s.tiposervicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as empresa','empresa.id','=','movimiento.empresa_id')
                            ->where('plan.nombre','like','%'.$plan.'%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->whereNotNull('movimiento.cie_id')
                            ->where('movimiento.situacion','<>','U')
                            ->where('dmc.descripcion','NOT LIKE','FARMACIA%')
                            ->where('movimiento.situacion','<>','A')
                            ->whereIn('movimiento.id',$lista);
        $resultado        = $resultado->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.subtotal','movimiento.igv','movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'historia.tipopaciente', 'movimiento.copago','movimiento.montoinicial', DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.tarifariosusalud_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','s.tarifario_id','ta.codigo','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.dni',DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'empresa.bussinesname','empresa.direccion','empresa.ruc','movimiento.comentario','movimiento.fechaingreso','movimiento.fechaalta','ts.nombre as tiposervicio','cie.descripcion as cie','cie.codigo as codigocie','movimiento.soat','es.nombre as especialidad',DB::raw("( ROUND((movimiento.montoinicial * (dmc.cantidad * (CASE WHEN movimiento.igv > 0 THEN dmc.precio / 1.18 ELSE dmc.precio END))) / (100 - movimiento.montoinicial), 2)) AS copagovariable"));
        $lista            = $resultado->get();
        $contador = 0; $num = '';
        //TENEMOS COPAGO FIJO APLICADO A CONSULTAS Y COPAGO VARIABLE APLICADO A NO CONSULTAS
        foreach ($lista as $key => $value){
            
            $contador != 0 ? $txt = "\r\n" : $txt = '';

            $v_numero = "F".str_pad($value->serie, 3, "0", STR_PAD_LEFT)."".str_pad($value->numero,8, "0", STR_PAD_LEFT);
            $v_cantidad = str_pad(floor($value->cantidad), 5, " ", STR_PAD_RIGHT);
            if($num != $v_numero){ $num = $v_numero; $correlativo = 1; } else { $correlativo++; }
            $v_correlativo = str_pad($correlativo, 4, "0", STR_PAD_LEFT);
            
            // if ($value->tiposervicio == 'LABORATORIO'){
            //     // $v_codigo = str_pad("330118", 10, " ", STR_PAD_RIGHT);
            //     if($value->codigo == '390101'){
            //          $tarif = Tarifario::where('nombre','LIKE','%'.$value->servicio2.'%')->first();
            //          if(is_null($tarif)){
            //                // $v_codigo = str_pad($value->codigo, 10, " ", STR_PAD_RIGHT);  
            //             $v_codigo = str_pad("******", 10, " ", STR_PAD_RIGHT);  
            //               // dd($value->servicio2);
            //          }else{
            //             $v_codigo = str_pad($tarif->codigo, 10, " ", STR_PAD_RIGHT);
            //          }   
            //     }else{
            //         $v_codigo = str_pad($value->codigo, 10, " ", STR_PAD_RIGHT);//ES EL VERDADERO CODIGO DEL SERVICIO
            //     }
            // } else {
            //     if(trim($value->codigocie)=='O82.9'){
            //         $v_codigo = str_pad($value->codigo, 10, " ", STR_PAD_RIGHT);
            //     }else{
            //         // if ($value->tiposervicio == 'CONSULTAS') {
            //              $tarif = Tarifario::where('nombre','LIKE','%'.$value->servicio2.'%')->first();
            //              if(is_null($tarif)){
            //                    // $v_codigo = str_pad($value->codigo, 10, " ", STR_PAD_RIGHT);  
            //                 $v_codigo = str_pad("******", 10, " ", STR_PAD_RIGHT);  
            //                 // dd($value->servicio2);
            //              }else{
            //                 $v_codigo = str_pad($tarif->codigo, 10, " ", STR_PAD_RIGHT);

            //              }   
            //                  // dd($tarif->codigo);          
            //                  // $v_codigo = $tarif->codigo;
            //              // $v_codigo = str_pad('390101', 10, " ", STR_PAD_RIGHT);  

            //         // }else{
                                                
            //         // }

                   
            //         // $v_codigo = is_null($value->codigo)?str_pad("121204", 10, " ", STR_PAD_RIGHT):str_pad($value->codigo, 10, " ", STR_PAD_RIGHT);
            //     }
            //     //$v_codigo = str_pad($value->codigo, 10, " ", STR_PAD_RIGHT);//ES EL VERDADERO CODIGO DEL SERVICIO
            // }

            $v_codigo = STR_PAD($value->tarifariosusalud_id,10, " ", STR_PAD_RIGHT);
            $descri = $value->servicio2;    
            if (strlen($descri) > 65) {
                $desc = substr($descri,0,64);
            } else {
                $desc = $descri;
            }
            $desc = preg_replace("(Ñ+)","N",$desc);
            $desc = preg_replace("(Á+)","A",$desc);         
            $desc = preg_replace("(É+)","E",$desc);
            $desc = preg_replace("(Í+)","I",$desc);
            $desc = preg_replace("(Ó+)","O",$desc);
            $desc = preg_replace("(Ú+)","U",$desc);
            $desc = preg_replace("(À+)","A",$desc);
            $desc = preg_replace("(È+)","E",$desc);
            $desc = preg_replace("(Ì+)","I",$desc);
            $desc = preg_replace("(Ò+)","O",$desc);
            $desc = preg_replace("(Ù+)","U",$desc);
            $desc = preg_replace("(`+)"," ",$desc);
            $desc = preg_replace("(´+)"," ",$desc);
            $v_descripcion = str_pad($desc, 70, " ", STR_PAD_RIGHT);
            $v_fecha = date('Ymd',strtotime($value->fechaingreso));
            $v_colegiatura = str_pad("", 6, " ", STR_PAD_RIGHT);
            
            $cant = floor($value->cantidad);
            //$sinig = number_format(($value->precio)/1.18,2,'.','');
            //$v_sinigv = str_pad(number_format(($value->precio)/1.18,2,'.',''), 12, " ", STR_PAD_LEFT);

            //CAMBIO 2019-03-18
            //$v_total = str_pad(number_format(($cant*$sinig),2,'.',''), 12, " ", STR_PAD_LEFT);

            if($v_iafa == 20001 || TRUE){
                if(strpos($v_descripcion,'CONSULTA') === false && strpos($v_descripcion,'CONS ') === false) {
                    $sinig = number_format((($value->precio*100)/(100-$value->montoinicial))/1.18,2,'.','');
                    $v_copagovariable = str_pad(number_format($value->copagovariable,2,'.',''), 12, " ", STR_PAD_LEFT);
                    $v_copagofijo = str_pad(number_format(0.0,2,'.',''), 12, " ", STR_PAD_LEFT);
                }else{
                    $sinig = number_format((($value->precio)/1.18) + $value->copago,2,'.','');
                    $v_copagovariable = str_pad(number_format(0.0,2,'.',''), 12, " ", STR_PAD_LEFT);
                    $v_copagofijo = str_pad(number_format($value->copago,2,'.',''), 12, " ", STR_PAD_LEFT);
                }
                $v_sinigv = str_pad(number_format($sinig,2,'.',''), 12, " ", STR_PAD_LEFT);
            }else{
                $sinig = number_format(($value->precio)/1.18,2,'.','');
                $v_sinigv = str_pad(number_format(($value->precio)/1.18,2,'.',''), 12, " ", STR_PAD_LEFT);
            }
            
            $v_total = str_pad(number_format(($cant*$sinig),2,'.',''), 12, " ", STR_PAD_LEFT);
            //$v_copagovariable = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            $v_montoneto = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            $v_cie10 = str_pad($value->codigocie, 5, " ", STR_PAD_RIGHT);

            $txt = $txt."".$ruc."".$ipress."01".$v_numero."1    ".$v_correlativo."03".$v_codigo."".$v_descripcion."".$v_fecha."00".$v_colegiatura."                ".$v_cantidad."".$v_sinigv."".$v_copagovariable."".$v_copagofijo."".$v_total."".$v_montoneto."".$v_cie10."A02";
            $contador++;
            fwrite($myfile, $txt);
        }
        
        fclose($myfile);
    }

    function generarFar($lista,$periodo,$lote,$plan,$fecha,$ruc,$ipress,$v_iafa){
        $myfile = fopen("trama/dfar_".$ruc."_".$ipress."_".$v_iafa."_".$lote."_".$periodo."_".$fecha.".txt", "w") or die("Unable to open file!");
        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('especialidad as es','es.id','=','medico.especialidad_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('tarifario as ta','ta.id','=','s.tarifario_id')
                            ->leftjoin('cie as cie','cie.id','=','movimiento.cie_id')
                            ->leftjoin('tiposervicio as ts','ts.id','=','s.tiposervicio_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as empresa','empresa.id','=','movimiento.empresa_id')
                            ->where('plan.nombre','like','%'.$plan.'%')
                            ->where('movimiento.tipomovimiento_id','=',9)
                            ->whereNotNull('movimiento.cie_id')
                            ->where('movimiento.situacion','<>','U')
                            ->where('dmc.descripcion','LIKE','FARMACIA%')
                            ->where('movimiento.situacion','<>','A')
                            ->whereIn('movimiento.id',$lista);
        $resultado        = $resultado->orderBy('movimiento.serie', 'ASC')->orderBy('movimiento.numero', 'ASC')
                            ->select('movimiento.subtotal','movimiento.igv','movimiento.total',DB::raw('plan.nombre as plan'),'movimiento.tipomovimiento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha'),'historia.tipopaciente','movimiento.copago','movimiento.montoinicial',DB::raw('historia.numero as historia'),'dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.situacion as situacion2','historia.numero as historia','dmc.recibo','dmc.id as iddetalle','s.nombre as servicio','s.tarifario_id','ta.codigo','dmc.precioconvenio','dmc.precio','dmc.pagohospital','s.precio as precio2','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.dni',DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'empresa.bussinesname','empresa.direccion','empresa.ruc','movimiento.comentario','movimiento.fechaingreso','movimiento.fechaalta','ts.nombre as tiposervicio','cie.descripcion as cie','cie.codigo as codigocie','movimiento.soat','es.nombre as especialidad');
        $lista            = $resultado->get();
        $contador = 0; $num = '';

        foreach ($lista as $key => $value){
            
            $contador != 0 ? $txt = "\r\n" : $txt = '';

            $v_numero = "F".str_pad($value->serie, 3, "0", STR_PAD_LEFT)."".str_pad($value->numero,8, "0", STR_PAD_LEFT);
            $v_cantidad = str_pad(floor($value->cantidad), 5, " ", STR_PAD_RIGHT);
            if($num != $v_numero){ $num = $v_numero; $correlativo = 1; } else { $correlativo++; }
            $v_correlativo = str_pad($correlativo, 5, " ", STR_PAD_LEFT);
            
            if ($value->tiposervicio == 'LABORATORIO'){
                $v_codigo = str_pad("330118", 10, " ", STR_PAD_RIGHT);
            } else {
                $v_codigo = str_pad("121204", 10, " ", STR_PAD_RIGHT);
            }
            
            $v_descripcion = str_pad($value->servicio2, 70, " ", STR_PAD_RIGHT);
            $v_fecha = date('Ymd',strtotime($value->fechaingreso));
            $v_cantidadfar = str_pad(number_format($value->cantidad,2,'.',''), 7, " ", STR_PAD_LEFT);
            $v_colegiatura = str_pad("", 6, " ", STR_PAD_RIGHT);
            $v_total = str_pad(number_format($value->precio,2,'.',''), 12, " ", STR_PAD_LEFT);
            if($v_iafa == 20001 || TRUE){
                //$v_precioanterior = $value->precio;
                $value->precio=number_format($value->precio*100/(100-$value->montoinicial),2,'.','');
                if ($value->igv == 0) {
                    $v_copagovariable = $value->montoinicial * $value->precio / 100;
                }else{
                    $v_copagovariable = $value->montoinicial * $value->precio / 100 / 1.18;
                }
                $v_copagovariable = str_pad(number_format($v_copagovariable,2,'.',''), 12, " ", STR_PAD_LEFT);
            }else{
                $v_copagovariable = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            }
            $v_copagofijo = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            $v_montoneto = str_pad(number_format(0,2,'.',''), 12, " ", STR_PAD_LEFT);
            $v_cie10 = str_pad($value->codigocie, 5, " ", STR_PAD_RIGHT);

            if ($value->igv == 0) {
                //$v_sinigv = $v_total;
                $v_sinigv = str_pad(number_format(($value->precio),2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_igv = "D";
            } else {
                $v_sinigv = str_pad(number_format(($value->precio)/1.18,2,'.',''), 12, " ", STR_PAD_LEFT);
                $v_igv = "A";
            }

            if($v_iafa == 20001 || TRUE){
                //$v_sinigv = $v_sinigv + $v_copagovariable;
                //$v_sinigv = str_pad($v_sinigv, 12, " ", STR_PAD_LEFT);
            }

            $txt = $txt."".$ruc."".$ipress."01".$v_numero."1".$v_correlativo."   OXXXXXXXXXXXXXXXO     ".$v_fecha."".$v_cantidadfar."".$v_sinigv."".$v_copagovariable."".$v_sinigv."".$v_montoneto."".$v_cie10.$v_igv."XXXXXXXXXXXX";
            $contador++;
            fwrite($myfile, $txt);
        }
        
        fclose($myfile);
    }

    function generarDen($lista,$periodo,$lote,$plan,$fecha,$ruc,$ipress,$v_iafa){
        $myfile = fopen("trama/dden_".$ruc."_".$ipress."_".$v_iafa."_".$lote."_".$periodo."_".$fecha.".txt", "w") or die("Unable to open file!");
    }

}
