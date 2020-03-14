@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<table id="example1" class="table table-bordered table-striped table-condensed table-hover">

	<thead>
		<tr>
			@foreach($cabecera as $key => $value)
				<th @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		<?php
		$contador = $inicio + 1;
		?>
		@foreach ($lista as $key => $value)
		<tr>
			<?php
				$estado = '';
				$fechafin = '';
				if ($value->estado == 'A') {
					$estado = 'Abierta';
				}elseif ($value->estado == 'C') {
					$estado = 'Cerrada';
				}

				if ($value->fechafin !== null) {
					$fechafin = date('d/m/Y H:i:s',strtotime($value->fechainicio));
				}

			?>
			<td>{{ $contador }}</td>
			<td>{{ date('d/m/Y H:i:s',strtotime($value->fechainicio)) }}</td>
			<td>{{ $fechafin }}</td>
			<td>{{ $value->montocierre }}</td>
			<td>{{ $estado }}</td>
			
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
{!! $paginacion or '' !!}
@endif