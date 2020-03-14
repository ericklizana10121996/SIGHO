@if($conceptopago_id==1)
	{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Apertura', array('class' => 'btn btn-info btn-xs', 'disabled' => 'true', 'id' => 'btnApertura', 'onclick' => 'modalCaja (\''.URL::route($ruta["apertura"], array('listar'=>'SI')).'\', \''.$titulo_apertura.'\', this);')) !!}
    {!! Form::button('<i class="glyphicon glyphicon-usd"></i> Nuevo', array('class' => 'btn btn-success btn-xs', 'id' => 'btnCerrar', 'onclick' => 'modalCaja (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
    {!! Form::button('<i class="glyphicon glyphicon-remove-circle"></i> Cierre', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnCerrar', 'onclick' => 'modalCaja (\''.URL::route($ruta["cierre"], array('listar'=>'SI')).'\', \''.$titulo_cierre.'\', this);')) !!}
@elseif($conceptopago_id==2)
    {!! Form::button('<i class="glyphicon glyphicon-plus"></i> Apertura', array('class' => 'btn btn-info btn-xs', 'id' => 'btnApertura', 'onclick' => 'modalCaja (\''.URL::route($ruta["apertura"], array('listar'=>'SI')).'\', \''.$titulo_apertura.'\', this);')) !!}
    {!! Form::button('<i class="glyphicon glyphicon-usd"></i> Nuevo', array('class' => 'btn btn-success btn-xs', 'disabled' => 'true', 'id' => 'btnCerrar', 'onclick' => 'modalCaja (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
    {!! Form::button('<i class="glyphicon glyphicon-remove-circle"></i> Cierre', array('class' => 'btn btn-danger btn-xs' , 'disabled' => 'true', 'id' => 'btnCerrar', 'onclick' => 'modalCaja (\''.URL::route($ruta["cierre"], array('listar'=>'SI')).'\', \''.$titulo_cierre.'\', this);')) !!}
@else
    {!! Form::button('<i class="glyphicon glyphicon-plus"></i> Apertura', array('class' => 'btn btn-info btn-xs', 'disabled' => 'true', 'id' => 'btnApertura', 'onclick' => 'modalCaja (\''.URL::route($ruta["apertura"], array('listar'=>'SI')).'\', \''.$titulo_apertura.'\', this);')) !!}
    {!! Form::button('<i class="glyphicon glyphicon-usd"></i> Nuevo', array('class' => 'btn btn-success btn-xs', 'id' => 'btnCerrar', 'onclick' => 'modalCaja (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
    {!! Form::button('<i class="glyphicon glyphicon-remove-circle"></i> Cierre', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnCerrar', 'onclick' => 'modalCaja (\''.URL::route($ruta["cierre"], array('listar'=>'SI')).'\', \''.$titulo_cierre.'\', this);')) !!}
@endif

<?php 
$saldo = number_format($ingreso - $egreso - $visa - $master,2,'.','');
?>
{!! Form::hidden('saldo', $saldo, array('id' => 'saldo')) !!}   
<hr />
@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
<div class="table-responsive">
<table id="example1" class="table table-bordered table-striped table-condensed table-hover">

	<thead>
		<tr>
			@foreach($cabecera as $key => $value)
				<th class="text-center" @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		<?php
		$contador = $inicio + 1;
		?>
		@foreach ($lista as $key => $value)
        <?php
        $color="";
        $color2="";
        $titulo="";

        if($value->conceptopago_id==15 || $value->conceptopago_id==14 || $value->conceptopago_id==16 || $value->conceptopago_id==17 || $value->conceptopago_id==18 || $value->conceptopago_id==19 || $value->conceptopago_id==20 || $value->conceptopago_id==21){
            if($value->conceptopago_id==14 || $value->conceptopago_id==16 || $value->conceptopago_id==18 || $value->conceptopago_id==20){//TRANSFERENCIA EGRESO TARJETA, CONVENIO, SOCIO, BOLETEO QUE ENVIA
                if($value->situacion2=='P' && $value->situacion!='A'){//PENDIENTE
                    $color='background:rgba(255,235,59,0.76)';
                    $titulo="Pendiente";
                }elseif($value->situacion2=='R' && $value->situacion!='A'){
                    $color='background:rgba(215,57,37,0.50)';
                    $titulo="Rechazado";
                }elseif($value->situacion2=='C' && $value->situacion!='A'){
                    $color='background:rgba(10,215,37,0.50)';
                    $titulo="Aceptado";
                }elseif($value->situacion2=='A' || $value->situacion=='A'){
                    $color='background:rgba(215,57,37,0.50)';
                    $titulo='Anulado'; 
                }
                //echo "hola".$value->situacion2;
            }else{
                if($value->situacion=='P'){
                    $color='background:rgba(255,235,59,0.76)';
                    $titulo="Pendiente";
                }elseif($value->situacion=='R'){
                    $color='background:rgba(215,57,37,0.50)';
                    $titulo="Rechazado";
                }elseif($value->situacion=="C"){
                    $color='background:rgba(10,215,37,0.50)';
                    $titulo="Aceptado";
                }elseif($value->situacion=='A'){
                    $color='background:rgba(215,57,37,0.50)';
                    $titulo='Anulado'; 
                } 
            }
        }else{

            $color=($value->situacion=='A')?'background:rgba(215,57,37,0.50)':'';
            $titulo=($value->situacion=='A')?'Anulado':'';            
        }
        if($value->conceptopago->tipo=='I'){
            $color2='color:green;font-weight: bold;';
        }else{
            $color2='color:red;font-weight: bold;';
        }
        $nombrepaciente = '';
        if($value->caja_id == 4){
            if ($value->persona_id !== NULL) {
                    //echo 'entro'.$value->paciente;break;
                $nombrepaciente = $value->paciente;

            }else{
                $nombrepaciente = trim($value->nombrepaciente);
            }
                            /*if ($value->tipodocumento_id == 5) {
                                
                                
                            }else{
                                $nombrepaciente = trim($value->empresa->nombre);
                            }*/
        }
        ?>
		<tr style="{{ $color }}" title="{{ $titulo }}">
            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td>{{ $value->numero }}</td>
            <td>{{ $value->conceptopago->nombre }}</td>
            @if ($value->caja_id == 4)
                <td>{{ $nombrepaciente}}</td>
            @else
                 <td>{{ $value->paciente}}</td>
            @endif
           
            @if(!is_null($value->situacion) && $value->situacion<>'R' && !is_null($value->situacion2) && $value->situacion2<>'R')
                @if($value->conceptopago_id>0 && !is_null($value->conceptopago_id) && $value->conceptopago->tipo=="I")
                    <td align="center" style='{{ $color2 }}'>{{ number_format($value->total,2,'.','') }}</td>
                    <td align="center">0.00</td>
                @else
                    <td align="center">0.00</td>
                    <td align="center" style='{{ $color2 }}'>{{ number_format($value->total,2,'.','') }}</td>
                @endif
            @else
                @if($value->conceptopago->tipo=="I")
                    <td align="center" style='{{ $color2 }}'>{{ number_format($value->total,2,'.','') }}</td>
                    <td align="center">0.00</td>
                @else
                    <td align="center">0.00</td>
                    <td align="center" style='{{ $color2 }}'>{{ number_format($value->total,2,'.','') }}</td>
                @endif
            @endif 
            @if($value->tipotarjeta!="")
                <td align="center">{{ $value->tipotarjeta.' - '.$value->tarjeta.' - '.$value->voucher}}</td>
            @else
                <td align="center"> - </td>
            @endif 
            <td>{{ $value->comentario }}</td>
            <td>{{ $value->responsable }}</td>
            <?php //echo $value->conceptopago_id; ?>
            @if($value->conceptopago_id<>2 && $value->conceptopago_id<>1 && $value->situacion<>'A' && !is_null($value->situacion2)  && $value->situacion2<>'R')
                <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'imprimirRecibo ('.$value->id.');', 'class' => 'btn btn-xs btn-warning', 'title' => 'Imprimir')) !!}</td>
            @elseif($value->conceptopago_id<>2 && $value->conceptopago_id<>1 && $value->situacion<>'A')
                <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'imprimirRecibo ('.$value->id.');', 'class' => 'btn btn-xs btn-warning', 'title' => 'Imprimir')) !!}</td>
            @else
                <td align="center"> - </td>
            @endif
            <?php //echo $value->conceptopago_id.''; ?>
            @if($conceptopago_id<>2 && $value->situacion<>'A' && $value->conceptopago_id<>15 && $value->conceptopago_id<>17 && $value->conceptopago_id<>19 && $value->conceptopago_id<>21 && $value->conceptopago_id<>32)
                @if($value->conceptopago_id<>1 && $value->conceptopago_id<>2 && $value->conceptopago_id<>14 && $value->conceptopago_id<>16 && $value->conceptopago_id<>18 && $value->conceptopago_id<>20)
                    @if($user->usertype_id==8 || $user->usertype_id==1)
                        <td align="center">{!! Form::button('<div class="glyphicon glyphicon-remove"></div>', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_anular.'\', this);', 'class' => 'btn btn-xs btn-danger', 'title' => 'Anular')) !!}</td>
                    @else
                        <td align="center"> - </td>
                    @endif
                @elseif(($value->conceptopago_id==14 || $value->conceptopago_id==16 || $value->conceptopago_id==18 || $value->conceptopago_id==20) && !is_null($value->situacion2)  && $value->situacion2=='P')
                    @if($user->usertype_id==8 || $user->usertype_id==1)
                        <td align="center">{!! Form::button('<div class="glyphicon glyphicon-remove"></div>', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_anular.'\', this);', 'class' => 'btn btn-xs btn-danger', 'title' => 'Anular')) !!}</td>
                    @else
                        <td align="center"> - </td>
                    @endif
                @else
                    <td align="center"> - </td>
                @endif
                
                <td align="center"> - </td>
            @elseif(($value->conceptopago_id==15 || $value->conceptopago_id==17 || $value->conceptopago_id==19 || $value->conceptopago_id==21|| $value->conceptopago_id==32) && $value->situacion=='P')
                <?php //echo 'entro'; ?>
                <td align="center">{!! Form::button('<div class="glyphicon glyphicon-check"></div> Aceptar y Descargar', array('onclick' => 'modal (\''.URL::route($ruta["descarga"], array('movimiento_id'=>$value->id, 'SI')).'\', \'Aceptar\', this);', 'class' => 'btn btn-xs btn-success')) !!}{!! Form::button('<div class="glyphicon glyphicon-check"></div> Aceptar', array('onclick' => 'modal (\''.URL::route($ruta["acept"], array($value->id, 'SI')).'\', \'Aceptar\', this);', 'class' => 'btn btn-xs btn-success')) !!}</td>
                <td align="center">{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Rechazar', array('onclick' => 'modal (\''.URL::route($ruta["reject"], array($value->id, 'SI')).'\', \'Rechazar\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
            @else
                <td align="center"> - </td>
                <td align="center"> - </td>
            @endif
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
{!! $paginacion or '' !!}
<table class="table-bordered table-striped table-condensed" align="center">
    <thead>
        <tr>
            <th class="text-center" colspan="2">Resumen de Caja</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>Ingresos :</th>
            <th class="text-right">{{ number_format($ingreso,2,'.','') }}</th>
        </tr>
        <tr>
            <td>Efectivo :</td>
            <td align="right">{{ number_format($efectivo,2,'.','') }}</td>
        </tr>
        <tr>
            <td>VISA :</td>
            <td align="right">{{ number_format($visa,2,'.','') }}</td>
        </tr>
        <tr>
            <td>MASTER :</td>
            <td align="right">{{ number_format($master,2,'.','') }}</td>
        </tr>

        <tr>
            <th>Egresos :</th>
            <th class="text-right">{{ number_format($egreso,2,'.','') }}</th>
        </tr>
        <tr>
            <th>Saldo :</th>
            <th class="text-right">{{ number_format($ingreso - $egreso - $visa - $master,2,'.','') }}</th>
        </tr>
        <tr>
            <th>Garantia :</th>
            <th class="text-right">{{ number_format($garantia,2,'.','') }}</th>
        </tr>
    </tbody>
</table>
</div>
@endif