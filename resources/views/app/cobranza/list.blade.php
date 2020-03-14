@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
<h3 class="text-warning">SUMA TOTAL: {{$total}}</h3>
{!! $paginacion or '' !!}
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
				@if($value->situacion == 'U' || $value->situacionbz == 'E' || $value->situacionsunat == 'E')
					<tr style="background-color:#FA7D62;">
						<td>{{ $contador }}</td>
			            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
			            <td>{{ $value->serie.'-'.$value->numero }}</td>
			            <td>{{ $value->empresa }}</td>
			            <td>{{ empty($value->tipo_poliza)===true?'No Especificado':$value->tipo_poliza }}</td>
			            <td>{{ $value->paciente }}</td>
			            <td align="center">{{ number_format($value->total,2,'.','') }}</td>
			            @if($value->fechaentrega!="")
			            	<td>{{ date("d/m/Y",strtotime($value->fechaentrega)) }}</td>
			            @else
			            	<td></td>
			            @endif
			  			<td>{{ $value->voucher }}</td>
			  			
			  			<td align="center">{{ $value->detraccion }}</td>
			  			<td align="center">{{ $value->retencion }}</td>
			            @if($value->situacion=='P' || $value->situacion=='B')
			            <td>PENDIENTE</td>
			            @elseif($value->situacion=='C')
			            <td>COBRADO</td>
			            @endif
			  			<td>{{ $value->responsable }}</td>
			            <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'facturacion/pdfComprobante?id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title'=>'Comprobante')) !!}</td>
					</tr>
				@else
					<tr style="background-color:#FFF;">
						<td>{{ $contador }}</td>
			            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
			            <td>{{ $value->serie.'-'.$value->numero }}</td>
			            <td>{{ $value->empresa }}</td>
			            <td>{{ empty($value->tipo_poliza)===true?'No Especificado':$value->tipo_poliza }}</td>
			            <td>{{ $value->paciente }}</td>
			            <td align="center">{{ number_format($value->total,2,'.','') }}</td>
			            @if($value->fechaentrega!="")
			            	<td>{{ date("d/m/Y",strtotime($value->fechaentrega)) }}</td>
			            @else
			            	<td></td>
			            @endif
			  			<td>{{ $value->voucher }}</td>
			  			
			  			<td align="center">{{ $value->detraccion }}</td>
			  			<td align="center">{{ $value->retencion }}</td>
			            @if($value->situacion=='P' || $value->situacion=='B')
			            <td>PENDIENTE</td>
			            @elseif($value->situacion=='C')
			            <td>COBRADO</td>
			            @endif
			  			<td>{{ $value->responsable }}</td>
			            <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'facturacion/pdfComprobante?id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title'=>'Comprobante')) !!}</td>
					</tr>
				@endif
			<?php
			$contador = $contador + 1;
			?>
			@endforeach
		</tbody>
	</table>
</div>
@endif