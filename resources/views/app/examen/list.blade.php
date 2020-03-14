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
	            <td>{{ $value->tipoexamen2 }}</td>
	            <td>{{ $value->nombre }}</td>
                <td align="center">{!! Form::button('<div class="glyphicon glyphicon-pencil"></div>', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning', 'title' => 'Editar')) !!}</td>

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