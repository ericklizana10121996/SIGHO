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
			<td>{!! Form::hidden('txtPrincipio'.$contador, $value->id, array('id' => 'txtPrincipio'.$contador)) !!}{{ $contador }}</td>
			<td>{{ $value->nombre }}</td>
			
			<td>{!! Form::hidden('txtNombre'.$contador, $value->nombre, array('id' => 'txtNombre'.$contador)) !!}
							{!! Form::button('<i class="fa fa-plus"></i> Agregar', array('onclick' => 'agregarprincipio(\''.$contador.'\')', 'class' => 'btn btn-xs btn-danger')) !!}</td>
							
							<td>{!! Form::button('<i class="btn btn-xs btn-danger"></i> Quitar', array('onclick' => 'quitar(\''.$value->id.'\')', 'class' => 'btn btn-xs btn-warning')) !!}</td>
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