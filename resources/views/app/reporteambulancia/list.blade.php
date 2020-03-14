@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
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
            @if($value->total>0)
            	<td>{{ $value->numero2 }}</td>
            @else
            	<td>{{ 'PREF '.$value->numero }}</td>
            @endif
            <td>{{ $value->paciente2 }}</td>
            <td align="right">{{ $value->total }}</td>
            @if($value->servicio_id>0)
            	<td align="left">{{ $value->servicio }}</td>
            @else
            	<td align="left">{{ $value->servicio2 }}</td>
            @endif
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
@endif