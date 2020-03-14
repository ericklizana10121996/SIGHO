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
            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td>{{ $value->paciente2 }}</td>
            <td align="center">{{ "F".$value->serie.'-'.$value->numero }}</td>
            <td align="right">{{ number_format(($value->preciodoctor)*$value->cantidad,2,'.','') }}</td>
            <td align="right">{{ number_format(($value->precio)*$value->cantidad,2,'.','') }}</td>
            <td>{{ $value->plan }}</td>
            <td>{{ $value->medico }}</td>
            <td>{{ round($value->cantidad,0) }}</td>
            <td>{{ $value->servicio2 }}</td>
            <td align="center">{{ $value->responsable }}</td>
            <td>{{ $value->historia }}</td>
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
</div>
@endif