<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Conceptopago;
use App\Movimiento;
use App\Detallemovcaja;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Excel;

class ReporteconsultaController extends Controller
{
    protected $folderview      = 'app.reporteconsulta';
    protected $tituloAdmin     = 'Consulta de Pagos';
    protected $tituloRegistrar = 'Registrar Concepto de Pago';
    protected $tituloModificar = 'Modificar Concepto de Pago';
    protected $tituloEliminar  = 'Eliminar Concepto de Pago';
    protected $rutas           = array('create' => 'reporteconsulta.create', 
            'edit'   => 'reporteconsulta.edit', 
            'delete' => 'reporteconsulta.eliminar',
            'search' => 'reporteconsulta.buscar',
            'index'  => 'reporteconsulta.index',
            'marca'  => 'reporteconsulta.marca',
            'desmarca'  => 'reporteconsulta.desmarca',
            'uci' => 'reporteconsulta.uci'
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function uci(Request $request)
    {
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $detalles   =   Detallemovcaja::leftjoin('movimiento as m','detallemovcaja.movimiento_id','=','m.id')->where('m.situacion','<>','U')->where('m.situacion','<>','A')->whereIn('servicio_id',[43,133]);

        if($fechainicial!=""){
            $detalles = $detalles->where('m.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $detalles = $detalles->where('m.fecha','<=',$fechafinal);
        }

        $detalles   =   $detalles->select('detallemovcaja.movimiento_id');
        $detalles   =   $detalles->get();

        Excel::create('ExcelReporteConsulta', function($excel) use($detalles,$request) {
 
            $excel->sheet('ConsultaPagos', function($sheet) use($detalles,$request) {

                $array = array();
                $cabecera = array();
                $cabecera[] = "Tipo Paciente";
                $cabecera[] = "Paciente";
                //$cabecera[] = "Plan";
                $cabecera[] = "Fecha";
                $cabecera[] = "Doctor";
                $cabecera[] = "Cant.";
                $cabecera[] = "Servicio";
                $cabecera[] = "Pago Doct.";
                $cabecera[] = "Pago Hosp.";
                $cabecera[] = "Referido";
                $cabecera[] = "Nro. Doc.";
                $cabecera[] = "Forma Pago";
                $cabecera[] = "Situacion";
                $cabecera[] = "Emergencia";
                $cabecera[] = "Usuario";
                $array[] = $cabecera;
                $c=3;$d=3;$band=true;

                foreach ($detalles as $key => $value){
                    $detallitos   =   Detallemovcaja::leftjoin('movimiento as m','detallemovcaja.movimiento_id','=','m.id')
                    ->leftjoin('movimiento as m2','m2.movimiento_id','=','m.id')
                    ->leftjoin('person as paciente','paciente.id','=','m.persona_id')
                    ->leftjoin('person as medico','medico.id','=','detallemovcaja.persona_id')
                    ->leftjoin('servicio','servicio.id','=','detallemovcaja.servicio_id')
                    ->where('m.id','=',$value->movimiento_id);
                    $detallitos   =   $detallitos->select('m2.*','detallemovcaja.*',DB::raw('CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) as paciente'),DB::raw('CONCAT(medico.apellidopaterno," ",medico.apellidomaterno," ",medico.nombres) as medico'),'servicio.nombre as servicio','detallemovcaja.descripcion as servicio2');
                    $detallitos   =   $detallitos->get();

                    foreach ($detallitos as $key => $value2){
                        $detalle = array();
                        $detalle[] = $value2->tipopaciente;
                        $detalle[] = $value2->paciente;
                        //$detalle[] = $value2->plan;
                        $detalle[] = date('d/m/Y',strtotime($value2->fecha));
                        $detalle[] = $value2->medico;
                        $detalle[] = number_format($value2->cantidad,0,'.','');
                        if($value2->servicio_id>0)
                            $detalle[] = $value2->servicio;
                        else
                            $detalle[] = $value2->servicio2;
                        $detalle[] = number_format($value2->pagodoctor*$value2->cantidad,2,'.','');
                        $detalle[] = number_format($value2->pagohospital*$value2->cantidad,2,'.','');
                        if($value2->referido_id>0)
                            $detalle[] = $value2->referido;
                        else
                            $detalle[] = "NO REFERIDO";
                        if($value2->total>0)
                            $detalle[] = ($value2->tipodocumento_id==4?"F":"B").$value2->serie.'-'.$value2->numero;
                        else
                            $detalle[] = 'PREF. '.$value2->numero2;
                        $detalle[] = $value2->situacion=='C'?($value2->tarjeta!=''?($value2->tarjeta.' / '.$value2->tipotarjeta.'-'.$value2->voucher):'CONTADO'):'-';
                        $detalle[] = ($value2->situacion=='C'?'Pagado':'Pendiente');
                        $detalle[] = $value2->soat;
                        $detalle[] = $value2->responsable;
                        $array[] = $detalle;                    
                    }
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function marca(Request $request)
    {
        $id     =   $request->input('id');
        $movimiento         = Detallemovcaja::find($id);
        $movimiento->marcado = 1;
        $movimiento->save();
    }

    public function desmarca(Request $request)
    {
        $id     =   $request->input('id');
        $movimiento         = Detallemovcaja::find($id);
        $movimiento->marcado = 0;
        $movimiento->save();
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'reporteconsulta';
        $paciente         = Libreria::getParam($request->input('paciente'));
        $servicio         = Libreria::getParam($request->input('servicio'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));

        $pago        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as responsable','responsable.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->where('movimiento.situacion','<>','U')
                            ->where('dmc.situacionentrega','like','E')
                            ->whereNull('historia.deleted_at');
        if($paciente!=""){
            $pago = $pago->where('movimiento.nombrepaciente','like','%'.$paciente.'%');
                            
        }
        if($doctor!=""){
            $pago = $pago->where('dmc.nombremedico','like','%'.$doctor.'%');
                            
        }
        if($fechainicial!=""){
            $pago = $pago->where('dmc.fechaentrega','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $pago = $pago->where('dmc.fechaentrega','<=',$fechafinal);
        }
        if($situacion!=""){
            $pago = $pago->where('mref.situacion','LIKE',$situacion);
        }
        if($servicio!=""){
            $pago = $pago->where(DB::raw('concat(case when dmc.servicio_id>0 then s.nombre else \' \'end,\' \',dmc.descripcion)'),'LIKE','%'.$servicio.'%');   
        }
        $pago        = $pago->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total','plan.nombre as plan2','mref.tipodocumento_id','mref.serie','mref.numero',DB::raw('dmc.fechaentrega as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id',DB::raw('case when movimiento.plan_id=6 then \'PAGO ATENCION PARTICULAR\' else \'PAGO ATENCION CONVENIO\' end  as servicio2'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','mref.situacion','historia.numero as historia','dmc.recibo','dmc.id as iddetalle',DB::raw('case when movimiento.plan_id=6 then \'PAGO ATENCION PARTICULAR\' else \'PAGO ATENCION CONVENIO\' end as servicio'),DB::raw('0 as pagohospital'),'dmc.cantidad','dmc.pagodoctor',DB::raw('dmc.nombremedico as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('movimiento.nombrepaciente as paciente2'),DB::raw('movimiento.nombreresponsable as responsable'),DB::raw('movimiento.numero as numero2'),'mref.ventafarmacia','mref.estadopago','mref.formapago','movimiento.nombrepaciente','movimiento.copago','dmc.id as dmc_id','dmc.marcado','movimiento.id','mref.id as venta_id');

        $socio        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.medicosocio_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->whereNull('historia.deleted_at')
                            ->where('movimiento.situacion','<>','U')
                            ->where('dmc.situacionsocio','like','E');
        if($fechainicial!=""){
            $socio = $socio->where('dmc.fechaentrega','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $socio = $socio->where('dmc.fechaentrega','<=',$fechafinal);
        }
        if($situacion!=""){
            $socio = $socio->where('mref.situacion','LIKE',$situacion);
        }
        if($servicio!=""){
            $socio = $socio->where(DB::raw('concat(case when dmc.servicio_id>0 then s.nombre else \' \'end,\' \',dmc.descripcion)'),'LIKE','%'.$servicio.'%');   
        }
        $socio        = $socio->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total','plan.nombre as plan2','mref.tipodocumento_id','mref.serie','mref.numero',DB::raw('dmc.fechaentrega as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id',DB::raw('\'PAGO SOCIO\' as servicio2'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','mref.situacion','historia.numero as historia','dmc.recibo','dmc.id as iddetalle',DB::raw('\'PAGO SOCIO\' as servicio'),DB::raw('0 as pagohospital'),'dmc.cantidad',DB::raw('dmc.pagosocio as pagodoctor'),DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'mref.ventafarmacia','mref.estadopago','mref.formapago','movimiento.nombrepaciente','movimiento.copago','dmc.id as dmc_id','dmc.marcado','movimiento.id','mref.id as venta_id');

        $tarjeta        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.medicosocio_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->whereNull('historia.deleted_at')
                            ->where('movimiento.situacion','<>','U')
                            ->where('dmc.situaciontarjeta','like','E');
        if($fechainicial!=""){
            $tarjeta = $tarjeta->where('dmc.fechaentrega','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $tarjeta = $tarjeta->where('dmc.fechaentrega','<=',$fechafinal);
        }
        if($situacion!=""){
            $tarjeta = $tarjeta->where('mref.situacion','LIKE',$situacion);
        }
        if($servicio!=""){
            $tarjeta = $tarjeta->where(DB::raw('concat(case when dmc.servicio_id>0 then s.nombre else \' \'end,\' \',dmc.descripcion)'),'LIKE','%'.$servicio.'%');   
        }
        $tarjeta        = $tarjeta->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total','plan.nombre as plan2','mref.tipodocumento_id','mref.serie','mref.numero',DB::raw('dmc.fechaentrega as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id',DB::raw('\'PAGO BOLETEO TOTAL\' as servicio2'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','mref.situacion','historia.numero as historia','dmc.recibo','dmc.id as iddetalle',DB::raw('\'PAGO BOLETEO TOTAL\' as servicio'),DB::raw('0 as pagohospital'),'dmc.cantidad',DB::raw('dmc.pagotarjeta as pagodoctor'),DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'mref.ventafarmacia','mref.estadopago','mref.formapago','movimiento.nombrepaciente','movimiento.copago','dmc.id as dmc_id','dmc.marcado','movimiento.id','mref.id as venta_id');


        $first            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('detallemovimiento as dm','dm.movimiento_id','=','movimiento.id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->join('producto as pr','pr.id','=','dm.producto_id')
                            ->leftjoin('conveniofarmacia','conveniofarmacia.id','=','movimiento.conveniofarmacia_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->whereIn('movimiento.situacion',['N'])
                            ->where('movimiento.tipomovimiento_id', '=', '4')
                            ->where('movimiento.ventafarmacia', '=', 'S')
                            ->where(DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end'),'like','%'.$paciente.'%')
                            ->whereNull('historia.deleted_at')
                            ->whereIn('movimiento.tipodocumento_id',['4','5','15']);
        if($fechainicial!=""){
            $first = $first->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first = $first->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $first = $first->where('movimiento.situacion','LIKE',$situacion);
        }
                            //->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
        $first            = $first->select(DB::raw('dm.precio*dm.cantidad as total'),DB::raw('cast(conveniofarmacia.nombre  as char(100)) as plan2'),'movimiento.tipodocumento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dm.producto_id','pr.nombre as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia',DB::raw('\'-\' as recibo'),'dm.id as iddetalle','pr.nombre as servicio',DB::raw('dm.precio pagohospital'),'dm.cantidad',DB::raw('0 as pagodoctor'),DB::raw('\'-\' as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.ventafarmacia','movimiento.estadopago','movimiento.formapago','movimiento.nombrepaciente','movimiento.copago','dm.id as dmc_id','dmc.marcado','movimiento.id','movimiento.id as venta_id')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');

        $first1            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('conveniofarmacia','conveniofarmacia.id','=','movimiento.conveniofarmacia_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->whereIn('movimiento.situacion',['N'])
                            ->where('movimiento.tipomovimiento_id', '=', '4')
                            ->where('movimiento.ventafarmacia', '=', 'S')
                            ->where(DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end'),'like','%'.$paciente.'%')
                            ->whereNull('historia.deleted_at')
                            ->whereIn('movimiento.tipodocumento_id',['4','5','15']);
        if($fechainicial!=""){
            $first1 = $first1->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first1 = $first1->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $first1 = $first1->where('movimiento.situacion','LIKE',$situacion);
        }
                            //->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
        $first1            = $first1->select('movimiento.total',DB::raw('cast(conveniofarmacia.nombre as char(100)) as plan2'),'movimiento.tipodocumento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('0 as producto_id'),DB::raw('\'MEDICAMENTOS\' as servicio2'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia',DB::raw('\'-\' as recibo'),DB::raw('0 as iddetalle'),DB::raw('\'MEDICAMENTOS\' as servicio'),DB::raw('movimiento.total pagohospital'),DB::raw('1 as cantidad'),DB::raw('0 as pagodoctor'),DB::raw('\'-\' as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.ventafarmacia','movimiento.estadopago','movimiento.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id as dmc_id',DB::raw('"0" as marcado'),'movimiento.id','movimiento.id as venta_id')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');

        

        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->leftjoin('movimiento as mref',function($join){
                                $join->on('mref.movimiento_id', '=', 'movimiento.id')
                                     ->whereNotIn('mref.situacion',['U']);
                            })
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->whereNull('historia.deleted_at')
                            ->where('movimiento.situacion','<>','U')
                            ->whereNotIn('dmc.situacionentrega',['A'])
                            ->where(function($query){
                                $query->whereNull('dmc.situaciontarjeta')
                                      ->orWhere(function($q){
                                        $q->whereNotIn('dmc.situaciontarjeta',['A']);
                                      });
                            });
        if($paciente!=""){
            $resultado = $resultado->where('movimiento.nombrepaciente','like','%'.$paciente.'%');
                            
        }
        if($doctor!=""){
            $resultado = $resultado->where('dmc.nombremedico','like','%'.$doctor.'%');
                            
        }
        if($fechainicial!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $resultado = $resultado->where('mref.situacion','LIKE',$situacion);
        }
        if($servicio!=""){
            $resultado = $resultado->where(DB::raw('concat(case when dmc.servicio_id>0 then s.nombre else \' \'end,\' \',dmc.descripcion)'),'LIKE','%'.$servicio.'%');   
        }
        $resultado        = $resultado->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total','plan.nombre as plan2','mref.tipodocumento_id','mref.serie','mref.numero',DB::raw('case when mref.id>0 then (case when mref.fecha=movimiento.fecha then mref.fecha else movimiento.fecha end) else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','mref.situacion','historia.numero as historia','dmc.recibo','dmc.id as iddetalle',DB::raw('case when s.tarifario_id>0 then (select concat(codigo,\' \',nombre) from tarifario where id=s.tarifario_id) else s.nombre end as servicio'),'dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('dmc.nombremedico as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('movimiento.nombrepaciente as paciente2'),DB::raw('movimiento.nombreresponsable as responsable'),DB::raw('movimiento.numero as numero2'),'mref.ventafarmacia','mref.estadopago','mref.formapago','movimiento.nombrepaciente','movimiento.copago','dmc.id as dmc_id','dmc.marcado','movimiento.id','mref.id as venta_id');
        //dd($resultado->get());
        if($request->input('farmacia')=="D"){
            $querySql = $resultado->unionAll($first)->toSql();
            $binding  = $resultado->getBindings();
            $resultado = DB::table(DB::raw("($querySql) as a order by fecha desc"))->addBinding($binding);
        }elseif($request->input('farmacia')=="C"){
            $querySql = $resultado->unionAll($first1)->toSql();
            $binding  = $resultado->getBindings();
            $resultado = DB::table(DB::raw("($querySql) as a order by fecha desc"))->addBinding($binding);
        }else{
            //dd($resultado->get());
            //->unionAll($socio)->unionAll($tarjeta)
            $querySql = $resultado->unionAll($pago)->toSql();
            $binding  = $resultado->getBindings();
            $resultado = DB::table(DB::raw("($querySql) as a order by fecha desc"))->addBinding($binding);
        }
        $lista            = $resultado->get();//dd($lista);
        $cabecera         = array();
        $cabecera[]       = array('valor' => 'Tipo Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Paciente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Plan', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Doctor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cant.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Servicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago Hosp.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Referido', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Forma Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        
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
            $user = Auth::user();
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'user'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    public function index()
    {
        $entidad          = 'reporteconsulta';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        $cboSituacion     = array("" => "Todos", "N" => "Pagado", "P" => "Pendiente");
        $cboFarmacia      = array("D" => "Detallado", "C" => "Consolidado", "N" => "No");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboSituacion', 'user','cboFarmacia'));
    }

    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Pagodoctor';
        $conceptopago = null;
        $formData            = array('conceptopago.store');
        $cboTipo          = array("Ingreso" => "Ingreso", "Egreso" => "Egreso");
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('conceptopago', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
    }

    public function store(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                'nombre'                  => 'required|max:200',
                );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $Conceptopago       = new Conceptopago();
            $Conceptopago->nombre = strtoupper($request->input('nombre'));
            $Conceptopago->tipo = $request->input('tipo');
            $Conceptopago->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function show($id)
    {
        //
    }

    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $conceptopago = Conceptopago::find($id);
        $entidad             = 'conceptopago';
        $formData            = array('Conceptopago.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('conceptopago', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function update(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'nombre'                  => 'required|max:200',
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
            $Conceptopago->tipo = $request->input('tipo');
            $categoria->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function destroy($id)
    {
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Conceptopago = Conceptopago::find($id);
            $Conceptopago->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'conceptopago');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Conceptopago::find($id);
        $entidad  = 'conceptopago';
        $formData = array('route' => array('conceptopago.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $servicio         = Libreria::getParam($request->input('servicio'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));
        $uci        = Libreria::getParam($request->input('uci'));
        $pago        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
            ->join('person as medico','medico.id','=','dmc.persona_id')
            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
            ->join('especialidad as esp','esp.id','=','medico.especialidad_id')
            ->join('person as responsable','responsable.id','=','dmc.usuarioentrega_id')
            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
            ->leftjoin('tiposervicio as tps','tps.id','=','s.tiposervicio_id')
            ->join('plan','plan.id','=','movimiento.plan_id')
            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
            ->where('movimiento.tipomovimiento_id','=',1)
            ->whereNull('dmc.deleted_at')
            ->where('movimiento.situacion','<>','U')
            ->where('dmc.situacionentrega','like','E')
            ->where(DB::raw('s.nombre'),'not like','%GARANTIA%')
            ->where(DB::raw('s.nombre'),'not like','%PAGO DE ATENCION PARTICULAR%')
            ->where(DB::raw('plan.nombre'),'not like','%PLAN PARTICULAR%');
                            
        if($fechainicial!=""){
            $pago = $pago->where('dmc.fechaentrega','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $pago = $pago->where('dmc.fechaentrega','<=',$fechafinal);
        }
        if($situacion!=""){
            $pago = $pago->where('mref.situacion','LIKE',$situacion);
        }
        if($servicio!=""){
            $pago = $pago->where(DB::raw('concat(case when dmc.servicio_id>0 then s.nombre else \' \'end,\' \',dmc.descripcion)'),'LIKE','%'.$servicio.'%');   
        }
        $pago        = $pago->orderBy('mes','asc')->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
            ->select('mref.total','esp.nombre as especialidad','plan.nombre as plan2','mref.tipodocumento_id','mref.serie','mref.numero','movimiento.soat',DB::raw('dmc.fechaentrega as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id',DB::raw('case when movimiento.plan_id=6 then \'PAGO ATENCION PARTICULAR\' else \'PAGO ATENCION CONVENIO\' end  as servicio2'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','mref.situacion','historia.numero as historia','dmc.recibo','dmc.fechaentrega','dmc.id as iddetalle',DB::raw('case when movimiento.plan_id=6 then \'PAGO ATENCION PARTICULAR\' else \'PAGO ATENCION CONVENIO\' end as servicio'),DB::raw('0 as pagohospital'),'dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'mref.ventafarmacia','mref.estadopago','mref.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','mref.id as venta_id','s.tiposervicio_id','movimiento.condicionpaciente','tps.nombre as tiposervicio','movimiento.created_at', DB::raw('WEEK(dmc.fechaentrega, 5) - WEEK(DATE_SUB(dmc.fechaentrega, INTERVAL DAYOFMONTH(dmc.fechaentrega) - 1 DAY), 5) + 1 as mes'));

        $first            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
            ->join('detallemovimiento as dm','dm.movimiento_id','=','movimiento.id')
            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
            ->join('producto as pr','pr.id','=','dm.producto_id')
            ->leftjoin('conveniofarmacia','conveniofarmacia.id','=','movimiento.conveniofarmacia_id')
            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
            ->whereIn('movimiento.situacion',['N','A'])
            ->where('movimiento.tipomovimiento_id', '=', '4')
            ->where('movimiento.ventafarmacia', '=', 'S')
            ->where(DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end'),'like','%'.$paciente.'%')
            ->whereIn('movimiento.tipodocumento_id',['4','5','15']);
            
        if($fechainicial!=""){
            $first = $first->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first = $first->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $first = $first->where('movimiento.situacion','LIKE',$situacion);
        }
      
        //->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
      
        $first            = $first->select(DB::raw('dm.precio*dm.cantidad as total'),DB::raw('cast(conveniofarmacia.nombre  as char(100)) as plan2'),'movimiento.tipodocumento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dm.producto_id','pr.nombre as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia',DB::raw('\'-\' as recibo'),'dm.id as iddetalle','pr.nombre as servicio',DB::raw('dm.precio pagohospital'),'dm.cantidad',DB::raw('0 as pagodoctor'),DB::raw('\'-\' as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.ventafarmacia','movimiento.estadopago','movimiento.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','movimiento.id as venta_id','0 AS tiposervicio_id','movimiento.condicionpaciente', DB::raw('WEEK(movimiento.fecha, 5) - WEEK(DATE_SUB(movimiento.fecha, INTERVAL DAYOFMONTH(movimiento.fecha) - 1 DAY), 5) + 1 as mes'))->orderBy('mes','asc')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');

        $first1            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('conveniofarmacia','conveniofarmacia.id','=','movimiento.conveniofarmacia_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->whereIn('movimiento.situacion',['N','A'])
                            ->where('movimiento.tipomovimiento_id', '=', '4')
                            ->where('movimiento.ventafarmacia', '=', 'S')
                            ->where(DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end'),'like','%'.$paciente.'%')
                            ->whereIn('movimiento.tipodocumento_id',['4','5','15']);
        if($fechainicial!=""){
            $first1 = $first1->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first1 = $first1->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $first1 = $first1->where('movimiento.situacion','LIKE',$situacion);
        }
      
        //->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
        $first1            = $first1->select('movimiento.total',DB::raw('cast(conveniofarmacia.nombre as char(100)) as plan2'),'movimiento.tipodocumento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('\'0\' as producto_id'),DB::raw('\'MEDICAMENTOS\' as servicio2'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia',DB::raw('\'-\' as recibo'),DB::raw('\'0\' as iddetalle'),DB::raw('\'MEDICAMENTOS\' as servicio'),DB::raw('movimiento.total pagohospital'),DB::raw('1 as cantidad'),DB::raw('\'0\' as pagodoctor'),DB::raw('\'-\' as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.ventafarmacia','movimiento.estadopago','movimiento.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','movimiento.id as venta_id',DB::raw('\'0\' AS tiposervicio_id'),'movimiento.condicionpaciente','movimiento.created_at', DB::raw('WEEK(movimiento.fecha, 5) - WEEK(DATE_SUB(movimiento.fecha, INTERVAL DAYOFMONTH(movimiento.fecha) - 1 DAY), 5) + 1 as mes'))->orderBy('mes','asc')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');



        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
            ->join('person as medico','medico.id','=','dmc.persona_id')
            ->join('especialidad as esp','esp.id','=','medico.especialidad_id')
            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
            ->leftjoin('movimiento as mref',function($join){
                $join->on('mref.movimiento_id', '=', 'movimiento.id')
                     ->whereNotIn('mref.situacion',['U']);
            })
            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
            //->leftjoin('tiposervicio as tps','tps.id','=','s.tiposervicio_id')
            ->leftjoin('tiposervicio as tps',function($join){
                $join->on('tps.id','=','s.tiposervicio_id')
                    ->orOn('tps.id','=','dmc.tiposervicio_id');
            })
            ->join('plan','plan.id','=','movimiento.plan_id')
            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
            ->where('movimiento.tipomovimiento_id','=',1)
            ->whereNull('dmc.deleted_at')
            ->where('movimiento.situacion','<>','U')
            ->whereRaw("( mref.id IS NULL OR (mref.id IS NOT NULL AND mref.situacion <> 'A') )")
            ->whereNotIn('dmc.situacionentrega',['A'])
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
        if($situacion!=""){
            $resultado = $resultado->where('mref.situacion','LIKE',$situacion);
        }
        if($servicio!=""){
            $resultado = $resultado->where(DB::raw('concat(case when dmc.servicio_id>0 then s.nombre else \' \'end,\' \',dmc.descripcion)'),'LIKE','%'.$servicio.'%');   
        }
        $resultado        = $resultado->orderBy('mes','asc')->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
            ->select('mref.total','esp.nombre as especialidad','plan.nombre as plan2','mref.tipodocumento_id','mref.serie','mref.numero','movimiento.soat',DB::raw('case when mref.id>0 then (case when mref.fecha=movimiento.fecha then mref.fecha else movimiento.fecha end) else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia','dmc.recibo','dmc.fechaentrega','dmc.id as iddetalle',DB::raw('case when s.tarifario_id>0 then (select concat(codigo,\' \',nombre) from tarifario where id=s.tarifario_id) else s.nombre end as servicio'),'dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'mref.ventafarmacia','mref.estadopago','mref.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','mref.id as venta_id','s.tiposervicio_id','movimiento.condicionpaciente','movimiento.created_at','tps.nombre as tiposervicio',DB::raw('case when mref.id>0 then (case when mref.fecha=movimiento.fecha then WEEK(mref.fecha, 5) - WEEK(DATE_SUB(mref.fecha, INTERVAL DAYOFMONTH(mref.fecha) - 1 DAY), 5) + 1 else WEEK(movimiento.fecha, 5) - WEEK(DATE_SUB(movimiento.fecha, INTERVAL DAYOFMONTH(movimiento.fecha) - 1 DAY), 5) + 1 end) else WEEK(movimiento.fecha, 5) - WEEK(DATE_SUB(movimiento.fecha, INTERVAL DAYOFMONTH(movimiento.fecha) - 1 DAY), 5) + 1 end as mes'));

        if($request->input('farmacia')=="D"){
            //$querySql = $resultado->unionAll($first)->toSql();
            //$binding  = $resultado->getBindings();
            //$resultado = DB::table(DB::raw("($querySql) as a order by fecha desc"))->addBinding($binding);
            $resultado=$first;
        }elseif($request->input('farmacia')=="C"){
            //$querySql = $resultado->unionAll($first1)->toSql();
            //$binding  = $resultado->getBindings();
            //$resultado = DB::table(DB::raw("($querySql) as a order by fecha desc"))->addBinding($binding);
            $resultado=$first1;
        }else{
            //->unionAll($socio)->unionAll($tarjeta)
            $querySql = $resultado->unionAll($pago)->toSql();
            $binding  = $resultado->getBindings();
            $resultado = DB::table(DB::raw("($querySql) as a order by fecha asc"))->addBinding($binding);
        }
        $resultado = $resultado->get();

       // echo json_encode($resultado);
       // exit;
        
        Excel::create('ExcelReporteConsulta', function($excel) use($resultado,$request) {
 
            $excel->sheet('ConsultaPagos', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Mes";
                $cabecera[] = "Semana";
                $cabecera[] = "Fecha";
                $cabecera[] = "Tipo Paciente";
                $cabecera[] = "Historia";
                $cabecera[] = "Paciente";
                $cabecera[] = "Plan";
                $cabecera[] = "Fecha Entrega";
                $cabecera[] = "RRHH Doctor";
                $cabecera[] = "Doctor";
                $cabecera[] = "Cant.";
                $cabecera[] = "Servicio";
                $cabecera[] = "Especialidad";
                $cabecera[] = "Tipo Servicio";
                $cabecera[] = "Pago Doct.";
                $cabecera[] = "Pago Hosp.";
                $cabecera[] = "Total";
                $cabecera[] = "Plan 10";
                $cabecera[] = "Referido";
                $cabecera[] = "Nro. Doc.";
                $cabecera[] = "Forma Pago";
                $cabecera[] = "Situacion";
                $cabecera[] = "Emergencia";
                $cabecera[] = "Usuario";
                $cabecera[] = "Condicion";
                $cabecera[] = "Fecha de Registro";
                $array[] = $cabecera;
                $c=3;$d=3;$band=true;


                foreach ($resultado as $key => $value){
                    $detalle = array();
                    $detalle[] = $this->mes_letras(date('m',strtotime($value->fecha)));
                  
                    // $this->obtener_semana($value->fecha);
                    // exit();
                    $detalle[] = $value->mes;//$this->obtener_semana($value->fecha); 
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->tipopaciente;
                    $detalle[] = $value->historia;
                    $detalle[] = $value->paciente2;
                    $detalle[] = $value->plan2;
                    if ($value->fechaentrega != "0000-00-00") {
                        $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                    } else {
                        $detalle[] = "";
                    }
                    $detalle[] = $value->recibo;
                    $detalle[] = $value->medico;
                    $detalle[] = number_format($value->cantidad,0,'.','');
                    if($value->servicio_id>0){
                        $detalle[] = $value->servicio;$nombre = $value->servicio;
                    }
                    else{
                        $detalle[] = $value->servicio2;$nombre = $value->servicio2;
                    }
                    $detalle[] = (trim($value->especialidad)==="PROVEEDOR" || trim($value->especialidad)==="MEDICINA GENERAL" || trim($value->especialidad)==="HOSPITAL")?"":$value->especialidad;
                    $detalle[] = $value->tiposervicio;
                    $detalle[] = number_format($value->pagodoctor*$value->cantidad,2,'.','');
                    $detalle[] = number_format($value->pagohospital*$value->cantidad,2,'.','');
                    $detalle[] = number_format(($value->pagohospital + $value->pagodoctor)*$value->cantidad,2,'.','');
                    if(strpos($nombre,'CONSULTA') === false && $value->tiposervicio_id!=9) {
                        $detalle[] = "";
                    }else{
                        $detalle[] = "10.00";
                    }
                    if($value->referido_id>0)
                        $detalle[] = $value->referido;
                    else
                        $detalle[] = "NO REFERIDO";
                    if($value->total>0)
                        $detalle[] = ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero;
                    else
                        $detalle[] = 'PREF. '.$value->numero2;
                    $detalle[] = $value->situacion=='C'?($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta.'-'.$value->voucher):'CONTADO'):'-';
                    $detalle[] = ($value->situacion=='C'?'Pagado':'Pendiente');
                    $detalle[] = $value->soat;
                    $detalle[] = $value->responsable;
                    $detalle[] = $value->condicionpaciente;
                    if (is_null($value->created_at) || $value->created_at == '') { 
                        $detalle[] = "-";      
                    }else{
                        $detalle[] = date('H:i:s', strtotime($value->created_at));
                    }
                    $array[] = $detalle;                    
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function obtener_semana($fecha){
        $anio = date('Y',strtotime($fecha));
        $mes  = date('m',strtotime($fecha));
        $f_ini= $anio.'-'.$mes.'-01'; //date('Y-m-d',strtotime($anio.'-'.$mes.'-01'));

        $f_it = date('Y-m-d',strtotime($f_ini)); //date('N',strtotime($f_ini));
        $f_fin= date('Y-m-d',strtotime($fecha));
      
        $cont_sem = 0;
        $cont_ite = 0;
        while ($f_it < $f_fin) {
            if($cont_ite > 0){
                $f_it = date('Y-m-d',strtotime($f_it));
                $cont_ite++;
            }
           
            $f_it = date('N', strtotime($f_it. "+ 1 days"));
        
            if ($f_it == '7') {
                $cont_sem++;
            }
            $f_it = date('Y-m-d',strtotime($f_it));
            

        }

        echo $cont_sem;
           

    }
    public function mes_letras($mes){
        switch ($mes) {
            case '01':
                return 'Enero';
                break;
            case '02':
                return 'Febrero';
                break;
            case '03':
                return 'Marzo';
                break;
            case '04':
                return 'Abril';
                break;
            case '05':
                return 'Mayo';
                break;
            case '06':
                return 'Junio';
                break;
            case '07':
                return 'Julio';
                break;
            case '08':
                return 'Agosto';
                break;
            case '09':
                return 'Setiembre';
                break;
            case '10':
                return 'Octubre';
                break;
            case '11':
                return 'Noviembre';
                break;
            case '12':
                return 'Diciembre';
                break;
        }
    }

    public function excelMarcado(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $servicio         = Libreria::getParam($request->input('servicio'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));
        $first            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('detallemovimiento as dm','dm.movimiento_id','=','movimiento.id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->join('producto as pr','pr.id','=','dm.producto_id')
                            ->leftjoin('conveniofarmacia','conveniofarmacia.id','=','movimiento.conveniofarmacia_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->whereIn('movimiento.situacion',['N','A'])
                            ->where('movimiento.tipomovimiento_id', '=', '4')
                            ->where('movimiento.ventafarmacia', '=', 'S')
                            ->where(DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end'),'like','%'.$paciente.'%')
                            ->whereIn('movimiento.tipodocumento_id',['4','5','15']);
        if($fechainicial!=""){
            $first = $first->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first = $first->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $first = $first->where('movimiento.situacion','LIKE',$situacion);
        }
                            //->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
        $first            = $first->select(DB::raw('dm.precio*dm.cantidad as total'),DB::raw('cast(conveniofarmacia.nombre  as char(100)) as plan2'),'movimiento.tipodocumento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dm.producto_id','pr.nombre as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia',DB::raw('\'-\' as recibo'),'dm.id as iddetalle','pr.nombre as servicio',DB::raw('dm.precio pagohospital'),'dm.cantidad',DB::raw('0 as pagodoctor'),DB::raw('\'-\' as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.ventafarmacia','movimiento.estadopago','movimiento.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','movimiento.id as venta_id')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');

        $first1            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('conveniofarmacia','conveniofarmacia.id','=','movimiento.conveniofarmacia_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->whereIn('movimiento.situacion',['N','A'])
                            ->where('movimiento.tipomovimiento_id', '=', '4')
                            ->where('movimiento.ventafarmacia', '=', 'S')
                            ->where(DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end'),'like','%'.$paciente.'%')
                            ->whereIn('movimiento.tipodocumento_id',['4','5','15']);
        if($fechainicial!=""){
            $first1 = $first1->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first1 = $first1->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $first1 = $first1->where('movimiento.situacion','LIKE',$situacion);
        }
                            //->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
        $first1            = $first1->select('movimiento.total',DB::raw('cast(conveniofarmacia.nombre as char(100)) as plan2'),'movimiento.tipodocumento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('0 as producto_id'),DB::raw('\'MEDICAMENTOS\' as servicio2'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia',DB::raw('\'-\' as recibo'),DB::raw('0 as iddetalle'),DB::raw('\'MEDICAMENTOS\' as servicio'),DB::raw('movimiento.total pagohospital'),DB::raw('1 as cantidad'),DB::raw('0 as pagodoctor'),DB::raw('\'-\' as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.ventafarmacia','movimiento.estadopago','movimiento.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','movimiento.id as venta_id')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');


        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('movimiento as mref',function($join){
                                $join->on('mref.movimiento_id', '=', 'movimiento.id')
                                     ->whereNotIn('mref.situacion',['U']);
                            })
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->where('movimiento.situacion','<>','U')
                            ->where('dmc.marcado','=','1')
                            ->whereNotIn('dmc.situacionentrega',['A'])
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
        if($situacion!=""){
            $resultado = $resultado->where('mref.situacion','LIKE',$situacion);
        }
        if($servicio!=""){
            $resultado = $resultado->where(DB::raw('concat(case when dmc.servicio_id>0 then s.nombre else \' \'end,\' \',dmc.descripcion)'),'LIKE','%'.$servicio.'%');   
        }
        $resultado        = $resultado->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total','plan.nombre as plan2','mref.tipodocumento_id','mref.serie','mref.numero','movimiento.soat',DB::raw('case when mref.id>0 then (case when mref.fecha=movimiento.fecha then mref.fecha else movimiento.fecha end) else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia','dmc.recibo','dmc.id as iddetalle',DB::raw('case when s.tarifario_id>0 then (select concat(codigo,\' \',nombre) from tarifario where id=s.tarifario_id) else s.nombre end as servicio'),'dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'mref.ventafarmacia','mref.estadopago','mref.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','mref.id as venta_id');

        if($request->input('farmacia')=="D"){
            $querySql = $resultado->unionAll($first)->toSql();
            $binding  = $resultado->getBindings();
            $resultado = DB::table(DB::raw("($querySql) as a order by fecha desc"))->addBinding($binding);
        }elseif($request->input('farmacia')=="C"){
            $querySql = $resultado->unionAll($first1)->toSql();
            $binding  = $resultado->getBindings();
            $resultado = DB::table(DB::raw("($querySql) as a order by fecha desc"))->addBinding($binding);
        }else{
            
        }
        $resultado = $resultado->get();
        Excel::create('excelMarcado', function($excel) use($resultado,$request) {
 
            $excel->sheet('Marcado', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Tipo Paciente";
                $cabecera[] = "Paciente";
                $cabecera[] = "Plan";
                $cabecera[] = "Fecha";
                $cabecera[] = "Doctor";
                $cabecera[] = "Cant.";
                $cabecera[] = "Servicio";
                $cabecera[] = "Pago Doct.";
                $cabecera[] = "Pago Hosp.";
                $cabecera[] = "Referido";
                $cabecera[] = "Nro. Doc.";
                $cabecera[] = "Forma Pago";
                $cabecera[] = "Situacion";
                $cabecera[] = "Emergencia";
                $cabecera[] = "Usuario";
                $array[] = $cabecera;
                $c=3;$d=3;$band=true;


                foreach ($resultado as $key => $value){
                    $detalle = array();
                    $detalle[] = $value->tipopaciente;
                    $detalle[] = $value->paciente2;
                    $detalle[] = $value->plan2;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->medico;
                    $detalle[] = number_format($value->cantidad,0,'.','');
                    if($value->servicio_id>0)
                        $detalle[] = $value->servicio;
                    else
                        $detalle[] = $value->servicio2;
                    $detalle[] = number_format($value->pagodoctor*$value->cantidad,2,'.','');
                    $detalle[] = number_format($value->pagohospital*$value->cantidad,2,'.','');
                    if($value->referido_id>0)
                        $detalle[] = $value->referido;
                    else
                        $detalle[] = "NO REFERIDO";
                    if($value->total>0)
                        $detalle[] = ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero;
                    else
                        $detalle[] = 'PREF. '.$value->numero2;
                    $detalle[] = $value->situacion=='C'?($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta.'-'.$value->voucher):'CONTADO'):'-';
                    $detalle[] = ($value->situacion=='C'?'Pagado':'Pendiente');
                    $detalle[] = $value->soat;
                    $detalle[] = $value->responsable;
                    $array[] = $detalle;                    
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function excelCons(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $servicio         = Libreria::getParam($request->input('servicio'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));
        $uci        = Libreria::getParam($request->input('uci'));
        $pago        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->where('movimiento.situacion','<>','U')
                            ->where('dmc.situacionentrega','like','E');
        if($fechainicial!=""){
            $pago = $pago->where('dmc.fechaentrega','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $pago = $pago->where('dmc.fechaentrega','<=',$fechafinal);
        }
        if($situacion!=""){
            $pago = $pago->where('mref.situacion','LIKE',$situacion);
        }
        if($servicio!=""){
            $pago = $pago->where(DB::raw('concat(case when dmc.servicio_id>0 then s.nombre else \' \'end,\' \',dmc.descripcion)'),'LIKE','%'.$servicio.'%');   
        }
        $pago        = $pago->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total','plan.nombre as plan2','mref.tipodocumento_id','mref.serie','mref.numero','movimiento.soat',DB::raw('dmc.fechaentrega as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id',DB::raw('case when movimiento.plan_id=6 then \'PAGO ATENCION PARTICULAR\' else \'PAGO ATENCION CONVENIO\' end  as servicio2'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','mref.situacion','historia.numero as historia','dmc.recibo','dmc.fechaentrega','dmc.id as iddetalle',DB::raw('case when movimiento.plan_id=6 then \'PAGO ATENCION PARTICULAR\' else \'PAGO ATENCION CONVENIO\' end as servicio'),DB::raw('0 as pagohospital'),'dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'mref.ventafarmacia','mref.estadopago','mref.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','mref.id as venta_id');

        $first            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('detallemovimiento as dm','dm.movimiento_id','=','movimiento.id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->join('producto as pr','pr.id','=','dm.producto_id')
                            ->leftjoin('conveniofarmacia','conveniofarmacia.id','=','movimiento.conveniofarmacia_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->whereIn('movimiento.situacion',['N','A'])
                            ->where('movimiento.tipomovimiento_id', '=', '4')
                            ->where('movimiento.ventafarmacia', '=', 'S')
                            ->where(DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end'),'like','%'.$paciente.'%')
                            ->whereIn('movimiento.tipodocumento_id',['4','5','15']);
        if($fechainicial!=""){
            $first = $first->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first = $first->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $first = $first->where('movimiento.situacion','LIKE',$situacion);
        }
                            //->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
        $first            = $first->select(DB::raw('dm.precio*dm.cantidad as total'),DB::raw('cast(conveniofarmacia.nombre  as char(100)) as plan2'),'movimiento.tipodocumento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dm.producto_id','pr.nombre as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia',DB::raw('\'-\' as recibo'),'dmc.fechaentrega','dm.id as iddetalle','pr.nombre as servicio',DB::raw('dm.precio pagohospital'),'dm.cantidad',DB::raw('0 as pagodoctor'),DB::raw('\'-\' as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.ventafarmacia','movimiento.estadopago','movimiento.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','movimiento.id as venta_id')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');

        $first1            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('conveniofarmacia','conveniofarmacia.id','=','movimiento.conveniofarmacia_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->whereIn('movimiento.situacion',['N','A'])
                            ->where('movimiento.tipomovimiento_id', '=', '4')
                            ->where('movimiento.ventafarmacia', '=', 'S')
                            ->where(DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end'),'like','%'.$paciente.'%')
                            ->whereIn('movimiento.tipodocumento_id',['4','5','15']);
        if($fechainicial!=""){
            $first1 = $first1->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first1 = $first1->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $first1 = $first1->where('movimiento.situacion','LIKE',$situacion);
        }
                            //->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
        $first1            = $first1->select('movimiento.total',DB::raw('cast(conveniofarmacia.nombre as char(100)) as plan2'),'movimiento.tipodocumento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('0 as producto_id'),DB::raw('\'MEDICAMENTOS\' as servicio2'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia',DB::raw('\'-\' as recibo'),'dmc.fechaentrega',DB::raw('0 as iddetalle'),DB::raw('\'MEDICAMENTOS\' as servicio'),DB::raw('movimiento.total pagohospital'),DB::raw('1 as cantidad'),DB::raw('0 as pagodoctor'),DB::raw('\'-\' as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.ventafarmacia','movimiento.estadopago','movimiento.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','movimiento.id as venta_id')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');


        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('movimiento as mref',function($join){
                                $join->on('mref.movimiento_id', '=', 'movimiento.id')
                                     ->whereNotIn('mref.situacion',['U']);
                            })
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->where('movimiento.situacion','<>','U')
                            ->whereNotIn('dmc.situacionentrega',['A'])
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
        if($situacion!=""){
            $resultado = $resultado->where('mref.situacion','LIKE',$situacion);
        }
        if($servicio!=""){
            $resultado = $resultado->where(DB::raw('concat(case when dmc.servicio_id>0 then s.nombre else \' \'end,\' \',dmc.descripcion)'),'LIKE','%'.$servicio.'%');   
        }
        $resultado        = $resultado->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total','plan.nombre as plan2','mref.tipodocumento_id','mref.serie','mref.numero','movimiento.soat',DB::raw('case when mref.id>0 then (case when mref.fecha=movimiento.fecha then mref.fecha else movimiento.fecha end) else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia','dmc.recibo','dmc.fechaentrega','dmc.id as iddetalle',DB::raw('case when s.tarifario_id>0 then (select concat(codigo,\' \',nombre) from tarifario where id=s.tarifario_id) else s.nombre end as servicio'),'dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'mref.ventafarmacia','mref.estadopago','mref.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','mref.id as venta_id');

        if($request->input('farmacia')=="D"){
            $querySql = $resultado->unionAll($first)->toSql();
            $binding  = $resultado->getBindings();
            $resultado = DB::table(DB::raw("($querySql) as a order by fecha asc"))->addBinding($binding);
        }elseif($request->input('farmacia')=="C"){
            $querySql = $resultado->unionAll($first1)->toSql();
            $binding  = $resultado->getBindings();
            $resultado = DB::table(DB::raw("($querySql) as a order by fecha asc"))->addBinding($binding);
        }else{
            //->unionAll($socio)->unionAll($tarjeta)
            $querySql = $resultado->unionAll($pago)->toSql();
            $binding  = $resultado->getBindings();
            $resultado = DB::table(DB::raw("($querySql) as a order by fecha asc"))->addBinding($binding);
        }
        $resultado = $resultado->get();
        Excel::create('ExcelAtenConvenio', function($excel) use($resultado,$request,$doctor) {
 
            $excel->sheet('Aten. Convenio', function($sheet) use($resultado,$request,$doctor) {
 
                $array = array();
                $cabecera1 = array();
                // dd($resultado);
                $cabecera1[] = "ATENCIONES A PACIENTES DE CONVENIO - DR. ".$doctor." - MES DE ";
                $array[] = $cabecera1;
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Historia";
                $cabecera[] = "Paciente";
                $cabecera[] = "Plan";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Recibo";
                $cabecera[] = "Doctor";
                $cabecera[] = "Cant.";
                $cabecera[] = "Servicio";
                $cabecera[] = "Nro. Doc.";
                $array[] = $cabecera;
                $c=3;$d=3;$band=true;

                foreach ($resultado as $key => $value){
                    if ($value->plan2 != "TARIFA-PARTICULAR 1" && strpos($value->servicio, "PAGO") === FALSE && strpos($value->servicio2, "PAGO") === FALSE) {
                        $detalle = array();
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        $detalle[] = $value->historia;
                        $detalle[] = $value->paciente2;
                        $detalle[] = $value->plan2;
                        if ($value->fechaentrega != "0000-00-00") {
                            $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                        } else {
                            $detalle[] = "";
                        }
                        $detalle[] = $value->recibo;
                        $detalle[] = $value->medico;
                        $detalle[] = number_format($value->cantidad,0,'.','');
                        if($value->servicio_id>0)
                            $detalle[] = $value->servicio;
                        else
                            $detalle[] = $value->servicio2;
                        if($value->total>0)
                            $detalle[] = ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero;
                        else
                            $detalle[] = 'PREF. '.$value->numero2;
                        //$detalle[] = $value->situacion=='C'?($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta.'-'.$value->voucher):'CONTADO'):'-';
                        
                        $array[] = $detalle;
                    }
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }

    public function excelConsMedico(Request $request){
        setlocale(LC_TIME, 'spanish');
        $paciente         = Libreria::getParam($request->input('paciente'));
        $servicio         = Libreria::getParam($request->input('servicio'));
        $doctor           = Libreria::getParam($request->input('doctor'));
        $fechainicial     = Libreria::getParam($request->input('fechainicial'));
        $fechafinal       = Libreria::getParam($request->input('fechafinal'));
        $situacion        = Libreria::getParam($request->input('situacion'));
        $uci        = Libreria::getParam($request->input('uci'));
        $pago        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','dmc.usuarioentrega_id')
                            ->leftjoin('movimiento as mref','mref.movimiento_id','=','movimiento.id')
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->where('movimiento.situacion','<>','U')
                            ->where('dmc.situacionentrega','like','E');
        if($fechainicial!=""){
            $pago = $pago->where('dmc.fechaentrega','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $pago = $pago->where('dmc.fechaentrega','<=',$fechafinal);
        }
        if($situacion!=""){
            $pago = $pago->where('mref.situacion','LIKE',$situacion);
        }
        if($servicio!=""){
            $pago = $pago->where(DB::raw('concat(case when dmc.servicio_id>0 then s.nombre else \' \'end,\' \',dmc.descripcion)'),'LIKE','%'.$servicio.'%');   
        }
        $pago        = $pago->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total','plan.nombre as plan2','mref.tipodocumento_id','mref.serie','mref.numero','movimiento.soat',DB::raw('dmc.fechaentrega as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id',DB::raw('case when movimiento.plan_id=6 then \'PAGO ATENCION PARTICULAR\' else \'PAGO ATENCION CONVENIO\' end  as servicio2'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','mref.situacion','historia.numero as historia','dmc.recibo','dmc.fechaentrega','dmc.id as iddetalle',DB::raw('case when movimiento.plan_id=6 then \'PAGO ATENCION PARTICULAR\' else \'PAGO ATENCION CONVENIO\' end as servicio'),DB::raw('0 as pagohospital'),'dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'mref.ventafarmacia','mref.estadopago','mref.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','mref.id as venta_id');

        $first            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('detallemovimiento as dm','dm.movimiento_id','=','movimiento.id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->join('producto as pr','pr.id','=','dm.producto_id')
                            ->leftjoin('conveniofarmacia','conveniofarmacia.id','=','movimiento.conveniofarmacia_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->whereIn('movimiento.situacion',['N','A'])
                            ->where('movimiento.tipomovimiento_id', '=', '4')
                            ->where('movimiento.ventafarmacia', '=', 'S')
                            ->where(DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end'),'like','%'.$paciente.'%')
                            ->whereIn('movimiento.tipodocumento_id',['4','5','15']);
        if($fechainicial!=""){
            $first = $first->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first = $first->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $first = $first->where('movimiento.situacion','LIKE',$situacion);
        }
                            //->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
        $first            = $first->select(DB::raw('dm.precio*dm.cantidad as total'),DB::raw('cast(conveniofarmacia.nombre  as char(100)) as plan2'),'movimiento.tipodocumento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dm.producto_id','pr.nombre as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia',DB::raw('\'-\' as recibo'),'dmc.fechaentrega','dm.id as iddetalle','pr.nombre as servicio',DB::raw('dm.precio pagohospital'),'dm.cantidad',DB::raw('0 as pagodoctor'),DB::raw('\'-\' as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.ventafarmacia','movimiento.estadopago','movimiento.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','movimiento.id as venta_id')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');

        $first1            = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('conveniofarmacia','conveniofarmacia.id','=','movimiento.conveniofarmacia_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->whereIn('movimiento.situacion',['N','A'])
                            ->where('movimiento.tipomovimiento_id', '=', '4')
                            ->where('movimiento.ventafarmacia', '=', 'S')
                            ->where(DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end'),'like','%'.$paciente.'%')
                            ->whereIn('movimiento.tipodocumento_id',['4','5','15']);
        if($fechainicial!=""){
            $first1 = $first1->where('movimiento.fecha','>=',$fechainicial);
        }
        if($fechafinal!=""){
            $first1 = $first1->where('movimiento.fecha','<=',$fechafinal);
        }
        if($situacion!=""){
            $first1 = $first1->where('movimiento.situacion','LIKE',$situacion);
        }
                            //->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
        $first1            = $first1->select('movimiento.total',DB::raw('cast(conveniofarmacia.nombre as char(100)) as plan2'),'movimiento.tipodocumento_id','movimiento.serie','movimiento.numero',DB::raw('movimiento.fecha as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente',DB::raw('0 as producto_id'),DB::raw('\'MEDICAMENTOS\' as servicio2'),'movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia',DB::raw('\'-\' as recibo'),'dmc.fechaentrega',DB::raw('0 as iddetalle'),DB::raw('\'MEDICAMENTOS\' as servicio'),DB::raw('movimiento.total pagohospital'),DB::raw('1 as cantidad'),DB::raw('0 as pagodoctor'),DB::raw('\'-\' as medico'),DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('case when movimiento.persona_id>0 then concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) else movimiento.nombrepaciente end as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'movimiento.ventafarmacia','movimiento.estadopago','movimiento.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','movimiento.id as venta_id')->orderBy('movimiento.fecha', 'desc')->orderBy('movimiento.numero','ASC');


        $resultado        = Movimiento::join('detallemovcaja as dmc','dmc.movimiento_id','=','movimiento.id')
                            ->join('person as medico','medico.id','=','dmc.persona_id')
                            ->join('person as paciente','paciente.id','=','movimiento.persona_id')
                            ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                            ->leftjoin('movimiento as mref',function($join){
                                $join->on('mref.movimiento_id', '=', 'movimiento.id')
                                     ->whereNotIn('mref.situacion',['U']);
                            })
                            ->leftjoin('servicio as s','s.id','=','dmc.servicio_id')
                            ->join('plan','plan.id','=','movimiento.plan_id')
                            ->leftjoin('historia','historia.person_id','=','movimiento.persona_id')
                            ->leftjoin('person as referido','referido.id','=','movimiento.doctor_id')
                            ->where(DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres)'),'like','%'.$doctor.'%')
                            ->where(DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres)'),'like','%'.$paciente.'%')
                            ->where('movimiento.tipomovimiento_id','=',1)
                            ->whereNull('dmc.deleted_at')
                            ->where('movimiento.situacion','<>','U')
                            ->whereNotIn('dmc.situacionentrega',['A'])
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
        if($situacion!=""){
            $resultado = $resultado->where('mref.situacion','LIKE',$situacion);
        }
        if($servicio!=""){
            $resultado = $resultado->where(DB::raw('concat(case when dmc.servicio_id>0 then s.nombre else \' \'end,\' \',dmc.descripcion)'),'LIKE','%'.$servicio.'%');   
        }
        $resultado        = $resultado->orderBy(DB::raw('case when mref.id>0 then mref.fecha else movimiento.fecha end'), 'desc')->orderBy('mref.serie', 'ASC')->orderBy('mref.numero', 'ASC')
                            ->select('mref.total','plan.nombre as plan2','mref.tipodocumento_id','mref.serie','mref.numero','movimiento.soat',DB::raw('case when mref.id>0 then (case when mref.fecha=movimiento.fecha then mref.fecha else movimiento.fecha end) else movimiento.fecha end as fecha'),'movimiento.doctor_id as referido_id','historia.tipopaciente','dmc.servicio_id','dmc.descripcion as servicio2','movimiento.tarjeta','movimiento.tipotarjeta','movimiento.voucher','movimiento.situacion','historia.numero as historia','dmc.recibo','dmc.fechaentrega','dmc.id as iddetalle',DB::raw('case when s.tarifario_id>0 then (select concat(codigo,\' \',nombre) from tarifario where id=s.tarifario_id) else s.nombre end as servicio'),'dmc.pagohospital','dmc.cantidad','dmc.pagodoctor',DB::raw('concat(medico.apellidopaterno,\' \',medico.apellidomaterno,\' \',medico.nombres) as medico'),'medico.id as id_medico',DB::raw('concat(referido.apellidopaterno,\' \',referido.apellidomaterno,\' \',referido.nombres) as referido'),DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'),DB::raw('responsable.nombres as responsable'),DB::raw('movimiento.numero as numero2'),'mref.ventafarmacia','mref.estadopago','mref.formapago','movimiento.nombrepaciente','movimiento.copago','movimiento.id','mref.id as venta_id');

        if($request->input('farmacia')=="D"){
            $querySql = $resultado->unionAll($first)->toSql();
            $binding  = $resultado->getBindings();
            $resultado = DB::table(DB::raw("($querySql) as a order by fecha asc"))->addBinding($binding);
        }elseif($request->input('farmacia')=="C"){
            $querySql = $resultado->unionAll($first1)->toSql();
            $binding  = $resultado->getBindings();
            $resultado = DB::table(DB::raw("($querySql) as a order by fecha asc"))->addBinding($binding);
        }else{
            //->unionAll($socio)->unionAll($tarjeta)
            $querySql = $resultado->unionAll($pago)->toSql();
            $binding  = $resultado->getBindings();
            $resultado = DB::table(DB::raw("($querySql) as a order by fecha asc"))->addBinding($binding);
        }
        $resultado = $resultado->get();
        
        $resultado2 = array();
        foreach ($resultado as $key => $value){
            if ($value->plan2 != "TARIFA-PARTICULAR 1" && strpos($value->servicio, "PAGO") === FALSE && strpos($value->servicio2, "PAGO") === FALSE) {

            }
        }
        Excel::create('ExcelAtenConvenio', function($excel) use($resultado,$request) {
 
            $excel->sheet('Aten. Convenio', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera1 = array();
                $cabecera1[] = "ATENCIONES A PACIENTES DE CONVENIO - DR. ".$resultado[0]->medico." - MES DE ";
                $array[] = $cabecera1;
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Historia";
                $cabecera[] = "Paciente";
                $cabecera[] = "Plan";
                $cabecera[] = "Fecha Pago";
                $cabecera[] = "Recibo";
                $cabecera[] = "Doctor";
                $cabecera[] = "Cant.";
                $cabecera[] = "Servicio";
                $cabecera[] = "Nro. Doc.";
                $array[] = $cabecera;
                $c=3;$d=3;$band=true;

                foreach ($resultado as $key => $value){
                    if ($value->plan2 != "TARIFA-PARTICULAR 1" && strpos($value->servicio, "PAGO") === FALSE && strpos($value->servicio2, "PAGO") === FALSE) {
                        $detalle = array();
                        $detalle[] = date('d/m/Y',strtotime($value->fecha));
                        $detalle[] = $value->historia;
                        $detalle[] = $value->paciente2;
                        $detalle[] = $value->plan2;
                        if ($value->fechaentrega != "0000-00-00") {
                            $detalle[] = date('d/m/Y',strtotime($value->fechaentrega));
                        } else {
                            $detalle[] = "";
                        }
                        $detalle[] = $value->recibo;
                        $detalle[] = $value->medico;
                        $detalle[] = number_format($value->cantidad,0,'.','');
                        if($value->servicio_id>0)
                            $detalle[] = $value->servicio;
                        else
                            $detalle[] = $value->servicio2;
                        if($value->total>0)
                            $detalle[] = ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero;
                        else
                            $detalle[] = 'PREF. '.$value->numero2;
                        //$detalle[] = $value->situacion=='C'?($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta.'-'.$value->voucher):'CONTADO'):'-';
                        
                        $array[] = $detalle;
                    }
                }

                $sheet->fromArray($array);
            });
        })->export('xls');
    }
}
