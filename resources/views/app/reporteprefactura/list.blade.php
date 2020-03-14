@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class="table-responsive">
	<table id="example1" class="table table-bordered table-striped table-condensed table-hover">

		<thead>
			<tr>
				@foreach($cabecera as $key => $value)
					<th style='font-size:12px' class="text-center" @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
				@endforeach
			</tr>
		</thead>
		<tbody>
			<?php
			$contador = $inicio + 1;
			?>
			@foreach ($lista as $key => $value)
			<tr>
				<td style='font-size:12px'>{{ $contador }}</td>
	            <td style='font-size:12px'>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
	            @if($value->copago>0)
	            	<td style='font-size:12px' align="center"><a href='javascript:void(0)' onclick="window.open('venta/pdfComprobante?venta_id={{ $value->id }}&guia=SI','_blank')">{{ ($value->tipodocumento_id==4?"F":($value->tipodocumento_id==15?"G":"B")).$value->numero }}</a></td>
	            @else
	            	<td style='font-size:12px'>{{ $value->numero }}</td>
	            @endif
	            <td style='font-size:12px'>{{ $value->paciente2 }}</td>
	            <td style='font-size:12px'>{{ $value->plan2 }}</td>
	            <td style='font-size:12px'>{{ number_format($value->cantidad,0,'.','') }}</td>
	            @if($value->servicio_id>0)
	            	<td style='font-size:12px'>{{ $value->servicio }}</td>
	            @else
	            	<td style='font-size:12px'>{{ $value->servicio2 }}</td>
	            @endif
	            <!--td style='font-size:12px' align="right">{{ number_format($value->total,2,'.','') }}</td-->
	            <td style='font-size:12px'>{{ $value->doctor }}</td>
	  			<td style='font-size:12px'>{{ $value->responsable }}</td>
	  			<td style='font-size:12px'>{{ $value->numero2 }}</td>
	  			@if($value->fecha2!="")
	  				<td style='font-size:12px'>{{ date('d/m/Y',strtotime($value->fecha2)) }}</td>	  	
	  			@else
	  				<td style='font-size:12px'>{{ '-' }}</td>
	  			@endif
	  			@if($value->fechaentrega2!="")
	  				<td style='font-size:12px'>{{ date('d/m/Y',strtotime($value->fechaentrega2)) }}</td>
	  			@else
	  				<td style='font-size:12px'>{{ '-' }}</td>
	  			@endif
	  			<td style='font-size:12px'>{{ $value->voucher2 }}</td>
			</tr>
			<?php
			$contador = $contador + 1;
			?>
			@endforeach
		</tbody>
	</table>
</div>
@endif