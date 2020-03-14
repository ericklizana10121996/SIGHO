@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class="table-responsive">
<table id="tablaLista" class="table table-bordered table-striped table-condensed table-hover">
	<thead>
		<tr>
			@foreach($cabecera as $key => $value)
				<th  style='font-size:12px' @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		<?php
		$contador = $inicio + 1;
		?>
		@foreach ($lista as $key => $value)
		@if($value->situacion == 'N')
		<tr>
		@elseif($value->situacion == 'U')
		<tr style="background:rgba(215,57,37,0.50)">
		@elseif($value->situacion == 'A')
		<tr style="background:rgba(48,215,37,0.50)">
		@endif
			<?php
				$estadopago = '';
				/*if ($value->estadopago == 'PP') {
					$estadopago = 'Pendiente';
				}elseif ($value->estadopago == 'P') {
					$estadopago = 'Pagado';
				}*/
				if ($value->formapago == 'P') {
					$estadopago = 'Pendiente';
				}else {
					$estadopago = 'Pagado';
				}

				if($value->tipodocumento_id=="4"){
                    $abreviatura="F";
                }elseif($value->tipodocumento_id=="5"){
                    $abreviatura="B";    
                }else{
                	$abreviatura="G"; 
                }
                $nombrepaciente = '';
                if ($value->persona_id !== NULL) {
                	//echo 'entro'.$value->id;break;
                    $nombrepaciente = trim($value->person->bussinesname." ".$value->person->apellidopaterno." ".$value->person->apellidomaterno." ".$value->person->nombres);

                }else{
                	$nombrepaciente = trim($value->nombrepaciente);
                }

                

                /*if ($value->tipodocumento_id == 5) {   
                    
                }else{
                    $nombrepaciente = trim($value->empresa->nombre);
                }*/

			?>
			<td style='font-size:12px'>{{ $contador }}</td>
			<td style='font-size:12px'>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
			<td class="marcador" style='font-size:12px' {{ 'id='.$contador }}>{{ utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)) }}</td>
			<td style='font-size:12px'>{{ $nombrepaciente }}</td>
			<td style='font-size:12px'>{{ $estadopago }}</td>
			<td style='font-size:12px'>{{ $value->total }}</td>
	  		<td align='center'><input type="checkbox" class="mchk" id="chk{{$value->id}}" {{ ($value->tipo=='S'?'checked':'') }} onclick="cargado(this.checked,{{ $value->id }},1)"/></td>
	  		<td><textarea id='txt{{$value->id}}' onblur='guardarObservacion(this.value,{{ $value->id }},1);' cols='20' rows='1'>{{$value->listapago}}</textarea></td>
			<td>{!! Form::button('<div class="glyphicon glyphicon-eye-open"></div> Ver', array('onclick' => 'modal (\''.URL::route($ruta["show"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_ver.'\', this);', 'class' => 'btn btn-xs btn-info')) !!}</td>
			@if($value->situacion == 'N')
				@if($value->conveniofarmacia_id !== null)
					@if($value->copago == 0)
						<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Guia', array('onclick' => 'window.open(\'venta/pdfComprobante?venta_id='.$value->id.'&guia=SI\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
					@else
						<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Comprobante', array('onclick' => 'window.open(\'venta/pdfComprobante?venta_id='.$value->id.'&guia=NO\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
						<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Ticket', array('onclick' => 'window.open(\'venta/pdfComprobante2?venta_id='.$value->id.'&guia=NO\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
						<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Guia', array('onclick' => 'window.open(\'venta/pdfComprobante?venta_id='.$value->id.'&guia=SI\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
						<td align="center">{!! Form::button('<div class="glyphicon glyphicon-file"></div> Excel', array('onclick' => 'window.open(\'ventaadmision/excelFarmacia1?venta_id='.$value->id.'&guia=SI\',\'_blank\')', 'class' => 'btn btn-xs btn-success')) !!}</td>
					@endif
				@else
					<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Comprobante', array('onclick' => 'window.open(\'venta/pdfComprobante?venta_id='.$value->id.'&guia=NO\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
					<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Ticket', array('onclick' => 'window.open(\'venta/pdfComprobante2?venta_id='.$value->id.'&guia=NO\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
				@endif
			@endif
			
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
	<tfoot>
		<tr>
			@foreach($cabecera as $key => $value)
				<th @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</tfoot>
</table>
</div>
<div style="position: absolute; right: 300px; top: 80px; color: red; font-weight: bold;"><input type="checkbox" onclick="marcar()"/></div>
<div style="position: absolute; right: 20px; top: 80px; color: red; font-weight: bold;">Total Facturado: {{ $totalfac }} </div>
{!! $paginacion or '' !!}
@endif

<style type="text/css">
	.visitado{
		background: #00c0ef;
	}
</style>