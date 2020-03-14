<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Conceptopago;
use App\Movimiento;
use App\Hospitalizacion;
use App\Detallemovcaja;
use App\Salaoperacion;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Excel;

class ReportegerenciaController extends Controller
{
    protected $folderview      = 'app.reportegerencia';
    protected $tituloAdmin     = 'Reporte Gerencia';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Modificar Concepto de Pago';
    protected $tituloEliminar  = 'Eliminar Concepto de Pago';
    protected $rutas           = array('create' => 'reportegerencia.create', 
            'edit'   => 'reportegerencia.edit', 
            'delete' => 'reportegerencia.eliminar',
            'index'  => 'reportegerencia.index'
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $entidad          = 'reportegerencia';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user'));
    }


    public function show($id)
    {
        //
    }

    public function excelConsultaExterna(Request $request){
        setlocale(LC_TIME, 'spanish');
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('movimiento as mref',function($join){
                                $join->on('mref.movimiento_id', '=', 'movimiento.id');
                            })
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->leftjoin('tiposervicio as tps',function($join){
                                $join->on('tps.id','=','s.tiposervicio_id')
                                    ->orOn('tps.id','=','dmc.tiposervicio_id');
                            })
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->whereRaw("( mref.id IS NULL OR (mref.id IS NOT NULL AND mref.situacion <> 'A') )")
                            ->whereNotIn('dmc.situacionentrega',['A'])
                            ->where('movimiento.condicionpaciente','like','Consulta Externa')
                            ->where(function($query){
                                $query->whereNull('dmc.situaciontarjeta')
                                      ->orWhere(function($q){
                                        $q->whereNotIn('dmc.situaciontarjeta',['A']);
                                      });
                            });
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        $resultado        = $resultado->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total','plan.nombre as plan2','mref.tipodocumento_id','mref.serie','mref.numero','movimiento.soat',DB::raw('case when mref.id>0 then (case when mref.fecha=movimiento.fecha then mref.fecha else movimiento.fecha end) else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia','dmc.recibo','dmc.fechaentrega','dmc.id as iddetalle',DB::raw('case when s.tarifario_id>0 then (select concat(codigo,\' \',nombre) from tarifario where id=s.tarifario_id) else s.nombre end as servicio'),'dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),'paciente.dni','paciente.fechanacimiento','paciente.sexo',DB::raw('movimiento.numero as numero2'),'mref.ventafarmacia','mref.estadopago','mref.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','mref.id as venta_id','s.tiposervicio_id','movimiento.condicionpaciente','tps.nombre as tiposervicio','movimiento.created_at',DB::raw('(case when medico.especialidad_id >0 then (select nombre from especialidad where id=medico.especialidad_id) else \'-\' end) as especialidad'),'medico.cmp','historia.fecha as fechahistoria','movimiento.situacion');

        
        $resultado = $resultado->get();
        Excel::create('excelConsultaExterna', function($excel) use($resultado,$request) {
 
            $excel->sheet('ConsultaExterna', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "MES";
                $cabecera[] = "FECHA CITA";
                $cabecera[] = "TURNO";
                $cabecera[] = "CONDICION";
                $cabecera[] = "HC";
                $cabecera[] = "DNI";
                $cabecera[] = "APELLIDOS Y NOMBRE (s)";
                $cabecera[] = "EDAD";
                $cabecera[] = "SEXO";
                $cabecera[] = "ESPECIALIDAD";
                $cabecera[] = "PROFESIONAL";
                $cabecera[] = "NRO COLEGIATURA";
                $cabecera[] = "TIPO PACIENTE";
                $cabecera[] = "NOMBRE CONVENIO";
                $cabecera[] = "CONDICION DEL PACIENTE EN EL ESTABLECIMIENTO";
                $cabecera[] = "CONDICION DEL PACIENTE EN LA ESPECIALIDAD";
                $cabecera[] = "CIE-X - 1";
                $cabecera[] = "Dx_1";
                $cabecera[] = "TIPO DE Dx_1";
                $cabecera[] = "CIE-X - 2";
                $cabecera[] = "Dx_2";
                $cabecera[] = "TIPO DE Dx_2";
                $cabecera[] = "FECHA DE PAGO";
                $cabecera[] = "MODO DE PAGO";
                $cabecera[] = "NRO COMPROBANTE";
                $cabecera[] = "IMPORTE";
                $cabecera[] = "USUARIO";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;


                foreach ($resultado as $key => $value){
                    $detalle = array();
                    if(date('m',strtotime($value->fecha))=="01"){
                        $mes = "ENERO";
                    }elseif(date('m',strtotime($value->fecha))=="02"){
                        $mes = "FEBRERO";
                    }elseif(date('m',strtotime($value->fecha))=="03"){
                        $mes = "MARZO";
                    }elseif(date('m',strtotime($value->fecha))=="04"){
                        $mes = "ABRIL";
                    }elseif(date('m',strtotime($value->fecha))=="05"){
                        $mes = "MAYO";
                    }elseif(date('m',strtotime($value->fecha))=="06"){
                        $mes = "JUNIO";
                    }elseif(date('m',strtotime($value->fecha))=="07"){
                        $mes = "JULIO";
                    }elseif(date('m',strtotime($value->fecha))=="08"){
                        $mes = "AGOSTO";
                    }elseif(date('m',strtotime($value->fecha))=="09"){
                        $mes = "SETIEMBRE";
                    }elseif(date('m',strtotime($value->fecha))=="10"){
                        $mes = "OCTUBRE";
                    }elseif(date('m',strtotime($value->fecha))=="11"){
                        $mes = "NOVIEMBRE";
                    }elseif(date('m',strtotime($value->fecha))=="12"){
                        $mes = "DICIEMBRE";
                    }
                    $hora1 = strtotime( "06:00" );
                    $hora2 = strtotime( "15:00" );
                    $hora3 = strtotime( "19:30" );
                    $hora = strtotime(date("H:i",strtotime($value->created_at)));
                    if($hora>=$hora1 && $hora<$hora2){
                        $turno = "MAÑANA";
                    }elseif($hora>$hora2 && $hora<$hora3){
                        $turno = "TARDE";
                    }else{
                        $turno = "NOCHE";
                    }
                    $detalle[] = $mes;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $turno;
                    if($value->situacion!="U"){
                        $detalle[] = "ATENDIDO";
                    }else{
                        $detalle[] = "DESERTADO";
                    }
                    $detalle[] = $value->historia;
                    $detalle[] = $value->dni;
                    $detalle[] = $value->paciente2;
                    if($value->fechanacimiento!=""){
                        $detalle[] = round((strtotime('now') - strtotime($value->fechanacimiento))/(60*60*24*365),0);
                    }else{
                        $detalle[] = '-';
                    }
                    $detalle[] = $value->sexo;
                    $detalle[] = $value->especialidad;
                    $detalle[] = $value->medico;
                    $detalle[] = $value->cmp;
                    $detalle[] = $value->tipopaciente;
                    $detalle[] = $value->plan2;
                    if($value->fechahistoria==$value->fecha){
                        $detalle[] = 'N';
                    }else{
                        $detalle[] = 'C';
                    }
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->situacion=='C'?($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta.'-'.$value->voucher):'CONTADO'):'-';
                    if($value->total>0)
                        $detalle[] = ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero;
                    else
                        $detalle[] = 'PREF. '.$value->numero2;
                    $detalle[] = $value->cantidad*$value->pagohospital;
                    $detalle[] = $value->responsable;
                    $sheet->row($c,$detalle);                  
                    $c=$c+1;
                }
            });
        })->export('xls');
    }

    public function excelHospitalizacion(Request $request){
        setlocale(LC_TIME, 'spanish');
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));

        $resultado        = Hospitalizacion::join('habitacion as h','h.id','=','hospitalizacion.habitacion_id')
                            ->join('piso','piso.id','=','h.piso_id')
                            ->join('historia','historia.id','=','hospitalizacion.historia_id')
                            ->leftjoin('convenio','convenio.id','=','historia.convenio_id')
                            ->join('person as paciente','paciente.id','=','historia.person_id')
                            ->join('person as medico','medico.id','=','hospitalizacion.medico_id')
                            ->join('especialidad','especialidad.id','=','medico.especialidad_id')
                            ->join('person as responsable','responsable.id','=','hospitalizacion.usuario_id')
                            ->leftjoin('tipoalta','tipoalta.idtipoalta','=','hospitalizacion.tipoalta_id')
                            ->whereNotIn('piso.id',[1,3,5]);
        if($fechainicial!=""){
            $resultado = $resultado->where('hospitalizacion.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('hospitalizacion.fecha','<=',$fechafinal);
        }
        $resultado        = $resultado->orderBy('hospitalizacion.fecha', 'ASC')
                            ->select('hospitalizacion.*','historia.numero as numerohistoria','paciente.dni',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.fechanacimiento','historia.tipopaciente','paciente.sexo','historia.convenio_id',DB::raw('tipoalta.nombre as tipoalta2'),DB::raw('piso.nombre as piso2'),DB::raw('h.nombre as habitacion2'),DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico2'),DB::raw('responsable.nombres as responsable'),DB::raw('especialidad.nombre as especialidad2'),DB::raw('convenio.nombre as convenio2'));
        
        $resultado = $resultado->get();
        Excel::create('excelHospitalizacion', function($excel) use($resultado,$request) {
 
            $excel->sheet('Hospitalizacion', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "MES";
                $cabecera[] = "FECHA DE INGRESO";
                $cabecera[] = "HORA DE INGRESO";
                $cabecera[] = "FECHA DE EGRESO";
                $cabecera[] = "HORA DE EGRESO";
                $cabecera[] = "ESTANCIA(DIAS)";
                $cabecera[] = "HC";
                $cabecera[] = "DNI";
                $cabecera[] = "APELLIDOS Y NOMBRES";
                $cabecera[] = "EDAD";
                $cabecera[] = "SEXO";
                $cabecera[] = "ESTACION";
                $cabecera[] = "CAMA";
                $cabecera[] = "ESPECIALIDAD";
                $cabecera[] = "PROFESIONAL";
                $cabecera[] = "TIPO DE CONVENIO";
                $cabecera[] = "NOMBRE DEL CONVENIO";
                $cabecera[] = "CIE-X EGRESO_1";
                $cabecera[] = "Dx. DE EGRESO_1";
                $cabecera[] = "TIPO DE Dx_1";
                $cabecera[] = "CIE-X EGRESO_2";
                $cabecera[] = "Dx. DE EGRESO_2";
                $cabecera[] = "TIPO DE Dx_2";
                $cabecera[] = "CONDICION DEL EGRESO";
                $cabecera[] = "DESTINO";
                $cabecera[] = "USUARIO";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;


                foreach ($resultado as $key => $value){
                    $detalle = array();
                    if(date('m',strtotime($value->fecha))=="01"){
                        $mes = "ENERO";
                    }elseif(date('m',strtotime($value->fecha))=="02"){
                        $mes = "FEBRERO";
                    }elseif(date('m',strtotime($value->fecha))=="03"){
                        $mes = "MARZO";
                    }elseif(date('m',strtotime($value->fecha))=="04"){
                        $mes = "ABRIL";
                    }elseif(date('m',strtotime($value->fecha))=="05"){
                        $mes = "MAYO";
                    }elseif(date('m',strtotime($value->fecha))=="06"){
                        $mes = "JUNIO";
                    }elseif(date('m',strtotime($value->fecha))=="07"){
                        $mes = "JULIO";
                    }elseif(date('m',strtotime($value->fecha))=="08"){
                        $mes = "AGOSTO";
                    }elseif(date('m',strtotime($value->fecha))=="09"){
                        $mes = "SETIEMBRE";
                    }elseif(date('m',strtotime($value->fecha))=="10"){
                        $mes = "OCTUBRE";
                    }elseif(date('m',strtotime($value->fecha))=="11"){
                        $mes = "NOVIEMBRE";
                    }elseif(date('m',strtotime($value->fecha))=="12"){
                        $mes = "DICIEMBRE";
                    }
                    $detalle[] = $mes;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->hora;
                    if($value->fechaalta!="" && !is_null($value->fechaalta) && $value->fechaalta!="0000-00-00"){
                        $detalle[] = date('d/m/Y',strtotime($value->fechaalta));
                        $detalle[] = '00:00';
                        $detalle[] = round((strtotime($value->fechaalta) - strtotime($value->fecha))/(60*60*24),0);
                    }else{
                        $detalle[] = "";
                        $detalle[] = "";
                        $detalle[] = "";
                    }
                    $detalle[] = $value->numerohistoria;
                    $detalle[] = $value->dni;
                    $detalle[] = $value->paciente2;
                    if($value->fechanacimiento!=""){
                        $detalle[] = round((strtotime('now') - strtotime($value->fechanacimiento))/(60*60*24*365),0);
                    }else{
                        $detalle[] = '-';
                    }
                    $detalle[] = $value->sexo;
                    $detalle[] = $value->piso2;
                    $detalle[] = $value->habitacion2;
                    $detalle[] = $value->especialidad2;
                    $detalle[] = $value->medico2;
                    $detalle[] = $value->tipopaciente;
                    $detalle[] = $value->convenio2;
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = $value->tipoalta2;
                    $detalle[] = $value->detalle;
                    $detalle[] = $value->responsable;
                    $sheet->row($c,$detalle);                  
                    $c=$c+1;
                }
            });
        })->export('xls');
    }

    public function excelUCI(Request $request){
        setlocale(LC_TIME, 'spanish');
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));

        $resultado        = Hospitalizacion::join('habitacion as h','h.id','=','hospitalizacion.habitacion_id')
                            ->join('piso','piso.id','=','h.piso_id')
                            ->join('historia','historia.id','=','hospitalizacion.historia_id')
                            ->leftjoin('convenio','convenio.id','=','historia.convenio_id')
                            ->join('person as paciente','paciente.id','=','historia.person_id')
                            ->join('person as medico','medico.id','=','hospitalizacion.medico_id')
                            ->join('especialidad','especialidad.id','=','medico.especialidad_id')
                            ->join('person as responsable','responsable.id','=','hospitalizacion.usuario_id')
                            ->leftjoin('tipoalta','tipoalta.idtipoalta','=','hospitalizacion.tipoalta_id')
                            ->whereIn('piso.id',[1,3]);
        if($fechainicial!=""){
            $resultado = $resultado->where('hospitalizacion.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('hospitalizacion.fecha','<=',$fechafinal);
        }
        $resultado        = $resultado->orderBy('hospitalizacion.fecha', 'ASC')
                            ->select('hospitalizacion.*','historia.numero as numerohistoria','paciente.dni',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.fechanacimiento','historia.tipopaciente','paciente.sexo','historia.convenio_id',DB::raw('tipoalta.nombre as tipoalta2'),DB::raw('piso.nombre as piso2'),DB::raw('h.nombre as habitacion2'),DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico2'),DB::raw('responsable.nombres as responsable'),DB::raw('especialidad.nombre as especialidad2'),DB::raw('convenio.nombre as convenio2'));
        
        $resultado = $resultado->get();
        Excel::create('excelUCI', function($excel) use($resultado,$request) {
 
            $excel->sheet('UCI', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "MES";
                $cabecera[] = "FECHA DE INGRESO";
                $cabecera[] = "HORA DE INGRESO";
                $cabecera[] = "FECHA DE EGRESO";
                $cabecera[] = "HORA DE EGRESO";
                $cabecera[] = "ESTANCIA(DIAS)";
                $cabecera[] = "HC";
                $cabecera[] = "DNI";
                $cabecera[] = "APELLIDOS Y NOMBRES";
                $cabecera[] = "EDAD";
                $cabecera[] = "SEXO";
                $cabecera[] = "ESTACION";
                $cabecera[] = "CAMA";
                $cabecera[] = "ESPECIALIDAD";
                $cabecera[] = "PROFESIONAL";
                $cabecera[] = "TIPO DE CONVENIO";
                $cabecera[] = "NOMBRE DEL CONVENIO";
                $cabecera[] = "CIE-X EGRESO_1";
                $cabecera[] = "Dx. DE EGRESO_1";
                $cabecera[] = "TIPO DE Dx_1";
                $cabecera[] = "CIE-X EGRESO_2";
                $cabecera[] = "Dx. DE EGRESO_2";
                $cabecera[] = "TIPO DE Dx_2";
                $cabecera[] = "CONDICION DEL EGRESO";
                $cabecera[] = "DESTINO";
                $cabecera[] = "USUARIO";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;


                foreach ($resultado as $key => $value){
                    $detalle = array();
                    if(date('m',strtotime($value->fecha))=="01"){
                        $mes = "ENERO";
                    }elseif(date('m',strtotime($value->fecha))=="02"){
                        $mes = "FEBRERO";
                    }elseif(date('m',strtotime($value->fecha))=="03"){
                        $mes = "MARZO";
                    }elseif(date('m',strtotime($value->fecha))=="04"){
                        $mes = "ABRIL";
                    }elseif(date('m',strtotime($value->fecha))=="05"){
                        $mes = "MAYO";
                    }elseif(date('m',strtotime($value->fecha))=="06"){
                        $mes = "JUNIO";
                    }elseif(date('m',strtotime($value->fecha))=="07"){
                        $mes = "JULIO";
                    }elseif(date('m',strtotime($value->fecha))=="08"){
                        $mes = "AGOSTO";
                    }elseif(date('m',strtotime($value->fecha))=="09"){
                        $mes = "SETIEMBRE";
                    }elseif(date('m',strtotime($value->fecha))=="10"){
                        $mes = "OCTUBRE";
                    }elseif(date('m',strtotime($value->fecha))=="11"){
                        $mes = "NOVIEMBRE";
                    }elseif(date('m',strtotime($value->fecha))=="12"){
                        $mes = "DICIEMBRE";
                    }
                    $detalle[] = $mes;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->hora;
                    if($value->fechaalta!="" && !is_null($value->fechaalta) && $value->fechaalta!="0000-00-00"){
                        $detalle[] = date('d/m/Y',strtotime($value->fechaalta));
                        $detalle[] = '00:00';
                        $detalle[] = round((strtotime($value->fechaalta) - strtotime($value->fecha))/(60*60*24),0);
                    }else{
                        $detalle[] = "";
                        $detalle[] = "";
                        $detalle[] = "";
                    }
                    $detalle[] = $value->numerohistoria;
                    $detalle[] = $value->dni;
                    $detalle[] = $value->paciente2;
                    if($value->fechanacimiento!=""){
                        $detalle[] = round((strtotime('now') - strtotime($value->fechanacimiento))/(60*60*24*365),0);
                    }else{
                        $detalle[] = '-';
                    }
                    $detalle[] = $value->sexo;
                    $detalle[] = $value->piso2;
                    $detalle[] = $value->habitacion2;
                    $detalle[] = $value->especialidad2;
                    $detalle[] = $value->medico2;
                    $detalle[] = $value->tipopaciente;
                    $detalle[] = $value->convenio2;
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = $value->tipoalta2;
                    $detalle[] = $value->detalle;
                    $detalle[] = $value->responsable;
                    $sheet->row($c,$detalle);                  
                    $c=$c+1;
                }
            });
        })->export('xls');
    }

    public function excelEmergencia(Request $request){
        setlocale(LC_TIME, 'spanish');
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));

        $resultado        = Hospitalizacion::join('habitacion as h','h.id','=','hospitalizacion.habitacion_id')
                            ->join('piso','piso.id','=','h.piso_id')
                            ->join('historia','historia.id','=','hospitalizacion.historia_id')
                            ->leftjoin('convenio','convenio.id','=','historia.convenio_id')
                            ->join('person as paciente','paciente.id','=','historia.person_id')
                            ->join('person as medico','medico.id','=','hospitalizacion.medico_id')
                            ->join('especialidad','especialidad.id','=','medico.especialidad_id')
                            ->join('person as responsable','responsable.id','=','hospitalizacion.usuario_id')
                            ->leftjoin('tipoalta','tipoalta.idtipoalta','=','hospitalizacion.tipoalta_id')
                            ->whereIn('piso.id',[5]);
        if($fechainicial!=""){
            $resultado = $resultado->where('hospitalizacion.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('hospitalizacion.fecha','<=',$fechafinal);
        }
        $resultado        = $resultado->orderBy('hospitalizacion.fecha', 'ASC')
                            ->select('hospitalizacion.*','historia.numero as numerohistoria','paciente.dni',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.fechanacimiento','historia.tipopaciente','paciente.sexo','historia.convenio_id',DB::raw('tipoalta.nombre as tipoalta2'),DB::raw('piso.nombre as piso2'),DB::raw('h.nombre as habitacion2'),DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico2'),DB::raw('responsable.nombres as responsable'),DB::raw('especialidad.nombre as especialidad2'),DB::raw('convenio.nombre as convenio2'));
        
        $resultado = $resultado->get();
        Excel::create('excelEmergencia', function($excel) use($resultado,$request) {
 
            $excel->sheet('Emergencia', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "MES";
                $cabecera[] = "FECHA DE INGRESO";
                $cabecera[] = "HORA DE INGRESO";
                $cabecera[] = "FECHA DE EGRESO";
                $cabecera[] = "HORA DE EGRESO";
                $cabecera[] = "ESTANCIA(HORAS)";
                $cabecera[] = "HC";
                $cabecera[] = "DNI";
                $cabecera[] = "APELLIDOS Y NOMBRES";
                $cabecera[] = "EDAD";
                $cabecera[] = "SEXO";
                $cabecera[] = "ESTACION";
                $cabecera[] = "CAMA";
                $cabecera[] = "ESPECIALIDAD";
                $cabecera[] = "PROFESIONAL";
                $cabecera[] = "TIPO DE CONVENIO";
                $cabecera[] = "NOMBRE DEL CONVENIO";
                $cabecera[] = "CIE-X EGRESO_1";
                $cabecera[] = "Dx. DE EGRESO_1";
                $cabecera[] = "TIPO DE Dx_1";
                $cabecera[] = "CIE-X EGRESO_2";
                $cabecera[] = "Dx. DE EGRESO_2";
                $cabecera[] = "TIPO DE Dx_2";
                $cabecera[] = "CONDICION DEL EGRESO";
                $cabecera[] = "DESTINO";
                $cabecera[] = "USUARIO";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;


                foreach ($resultado as $key => $value){
                    $detalle = array();
                    if(date('m',strtotime($value->fecha))=="01"){
                        $mes = "ENERO";
                    }elseif(date('m',strtotime($value->fecha))=="02"){
                        $mes = "FEBRERO";
                    }elseif(date('m',strtotime($value->fecha))=="03"){
                        $mes = "MARZO";
                    }elseif(date('m',strtotime($value->fecha))=="04"){
                        $mes = "ABRIL";
                    }elseif(date('m',strtotime($value->fecha))=="05"){
                        $mes = "MAYO";
                    }elseif(date('m',strtotime($value->fecha))=="06"){
                        $mes = "JUNIO";
                    }elseif(date('m',strtotime($value->fecha))=="07"){
                        $mes = "JULIO";
                    }elseif(date('m',strtotime($value->fecha))=="08"){
                        $mes = "AGOSTO";
                    }elseif(date('m',strtotime($value->fecha))=="09"){
                        $mes = "SETIEMBRE";
                    }elseif(date('m',strtotime($value->fecha))=="10"){
                        $mes = "OCTUBRE";
                    }elseif(date('m',strtotime($value->fecha))=="11"){
                        $mes = "NOVIEMBRE";
                    }elseif(date('m',strtotime($value->fecha))=="12"){
                        $mes = "DICIEMBRE";
                    }
                    $detalle[] = $mes;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->hora;
                    $detalle[] = date('d/m/Y',strtotime($value->fechaalta));
                    $detalle[] = '00:00';
                    //$detalle[] = round((strtotime($value->fechaalta) - strtotime($value->fecha." ".$value->hora))/(60*60),0);
                    $detalle[] = '-';
                    $detalle[] = $value->numerohistoria;
                    $detalle[] = $value->dni;
                    $detalle[] = $value->paciente2;
                    if($value->fechanacimiento!=""){
                        $detalle[] = round((strtotime('now') - strtotime($value->fechanacimiento))/(60*60*24*365),0);
                    }else{
                        $detalle[] = '-';
                    }
                    $detalle[] = $value->sexo;
                    $detalle[] = $value->piso2;
                    $detalle[] = $value->habitacion2;
                    $detalle[] = $value->especialidad2;
                    $detalle[] = $value->medico2;
                    $detalle[] = $value->tipopaciente;
                    $detalle[] = $value->convenio2;
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = $value->tipoalta2;
                    $detalle[] = $value->detalle;
                    $detalle[] = $value->responsable;
                    $sheet->row($c,$detalle);                  
                    $c=$c+1;
                }
            });
        })->export('xls');
    }

    public function excelSOP(Request $request){
        setlocale(LC_TIME, 'spanish');
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));

        $resultado        = Salaoperacion::leftjoin('historia','historia.id','=','salaoperacion.historia_id')
                            ->leftjoin('convenio','convenio.id','=','historia.convenio_id')
                            ->join('sala as sa', 'sa.id', '=', 'salaoperacion.sala_id')
                            ->join('person as doctor', 'doctor.id', '=', 'salaoperacion.medico_id')
                            ->leftjoin('person as usuario', 'usuario.id', '=', 'salaoperacion.usuario_id')
                            ->join('especialidad','especialidad.id','=','doctor.especialidad_id')
                            ->leftjoin('person as paciente', 'paciente.id', '=', 'historia.person_id');
        if($fechainicial!=""){
            $resultado = $resultado->where('salaoperacion.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('salaoperacion.fecha','<=',$fechafinal);
        }
        $resultado        = $resultado
                            ->select('Salaoperacion.*','sa.nombre as sala','usuario.nombres as usuario3','historia.tipopaciente','especialidad.nombre as especialidad','historia.numero as historia1','paciente.sexo',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),'paciente.dni','paciente.fechanacimiento',DB::raw('concat(doctor.apellidopaterno,\' \',doctor.apellidomaterno,\' \',doctor.nombres) as doctor'),DB::raw('convenio.nombre as convenio2'))->orderBy('Salaoperacion.fecha', 'ASC')->orderBy('Salaoperacion.horainicio','ASC');;
        
        $resultado = $resultado->get();
        Excel::create('excelSOP', function($excel) use($resultado,$request) {
 
            $excel->sheet('SOP', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "MES";
                $cabecera[] = "FECHA DE INGRESO";
                $cabecera[] = "HC";
                $cabecera[] = "DNI";
                $cabecera[] = "APELLIDOS Y NOMBRES";
                $cabecera[] = "TIPO PACIENTE";
                $cabecera[] = "NOMBRE DEL CONVENIO";
                $cabecera[] = "EDAD";
                $cabecera[] = "SEXO";
                $cabecera[] = "HORA INICIO CIRUGIA";
                $cabecera[] = "HORA TERMINO CIRUGIA";
                $cabecera[] = "TIEMPO Qx. (hh:mm)";
                $cabecera[] = "COMPLEJIDAD 1";
                $cabecera[] = "COD. CPT_1";
                $cabecera[] = "PROCEDIMIENTO REALIZADO_1";
                $cabecera[] = "COMPLEJIDAD 2";
                $cabecera[] = "COD. CPT_2";
                $cabecera[] = "PROCEDIMIENTO REALIZADO_2";
                $cabecera[] = "COMPLEJIDAD 3";
                $cabecera[] = "COD. CPT_3";
                $cabecera[] = "PROCEDIMIENTO REALIZADO_3";
                $cabecera[] = "ESPECIALIDAD";
                $cabecera[] = "CIRUJANO PRINCIPAL";
                $cabecera[] = "1º AYUDANTE";
                $cabecera[] = "2º AYUDANTE";
                $cabecera[] = "ANESTESIOLOGO";
                $cabecera[] = "INSTRUMENTISTA";
                $cabecera[] = "CIRCULANTE";
                $cabecera[] = "SALA";
                $cabecera[] = "CONDICION DE LA CIRUGIA";
                $cabecera[] = "MOTIVO DE SUSPENSIÓN";
                $cabecera[] = "DESTINO FINAL";
                $cabecera[] = "USUARIO";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;


                foreach ($resultado as $key => $value){
                    $detalle = array();
                    if(date('m',strtotime($value->fecha))=="01"){
                        $mes = "ENERO";
                    }elseif(date('m',strtotime($value->fecha))=="02"){
                        $mes = "FEBRERO";
                    }elseif(date('m',strtotime($value->fecha))=="03"){
                        $mes = "MARZO";
                    }elseif(date('m',strtotime($value->fecha))=="04"){
                        $mes = "ABRIL";
                    }elseif(date('m',strtotime($value->fecha))=="05"){
                        $mes = "MAYO";
                    }elseif(date('m',strtotime($value->fecha))=="06"){
                        $mes = "JUNIO";
                    }elseif(date('m',strtotime($value->fecha))=="07"){
                        $mes = "JULIO";
                    }elseif(date('m',strtotime($value->fecha))=="08"){
                        $mes = "AGOSTO";
                    }elseif(date('m',strtotime($value->fecha))=="09"){
                        $mes = "SETIEMBRE";
                    }elseif(date('m',strtotime($value->fecha))=="10"){
                        $mes = "OCTUBRE";
                    }elseif(date('m',strtotime($value->fecha))=="11"){
                        $mes = "NOVIEMBRE";
                    }elseif(date('m',strtotime($value->fecha))=="12"){
                        $mes = "DICIEMBRE";
                    }
                    $detalle[] = $mes;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->historia1;
                    $detalle[] = $value->dni;
                    $detalle[] = $value->paciente;
                    $detalle[] = $value->tipopaciente;
                    $detalle[] = $value->convenio2;
                    if($value->fechanacimiento!=""){
                        $detalle[] = round((strtotime('now') - strtotime($value->fechanacimiento))/(60*60*24*365),0);
                    }else{
                        $detalle[] = '-';
                    }
                    $detalle[] = $value->sexo;
                    $detalle[] = $value->horainicio;
                    $detalle[] = $value->horafin;
                    //$detalle[] = (strtotime($value->horafin) - strtotime($value->horainicio))/(60*60);
                    $detalle[] = '-';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = $value->operacion;
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = '';
                    $detalle[] = $value->especialidad;
                    $detalle[] = $value->doctor;
                    $detalle[] = $value->ayudante1;
                    $detalle[] = $value->ayudante2;
                    $detalle[] = $value->anestesiologo;
                    $detalle[] = $value->instrumentista;
                    $detalle[] = '';
                    $detalle[] = $value->sala;
                    $detalle[] = $value->situacion=='C'?'Confirmada':($value->situacion=='A'?'Anulada':'Pendiente');
                    $detalle[] = $value->comentario;
                    $detalle[] = '';
                    $detalle[] = $value->usuario3;
                    $sheet->row($c,$detalle);                  
                    $c=$c+1;
                }
            });
        })->export('xls');
    }
}
