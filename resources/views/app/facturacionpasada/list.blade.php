@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
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
			<tr>
				<td>{{ $contador }}</td>
	            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
	            <td>{{ $value->serie.'-'.$value->numero }}</td>
	            <td>{{ $value->paciente }}</td>
	            <td>{{ $value->empresa }}</td>
	            <td align="center">{{ number_format($value->total,2,'.','') }}</td>
	            @if($value->situacion=='P' || $value->situacion=='B')
	            <td>PENDIENTE</td>
	            @elseif($value->situacion=='C')
	            <td>COBRADO</td>
	            @elseif($value->situacion=='A')
	            <td>NOTA CREDITO</td>
	            @endif
	  			<td>{{ $value->responsable }}</td>
	            <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'facturacionpasada/pdfComprobante?id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title'=>'Comprobante')) !!}</td>
				<td align="center">{!! Form::button('<div class="glyphicon glyphicon-remove"></div>', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger', 'title'=> 'Eliminar')) !!}</td>
			</tr>
			<?php
			$contador = $contador + 1;
			?>
			@endforeach
		</tbody>
	</table>
</div>
@endif