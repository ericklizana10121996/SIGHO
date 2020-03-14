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
			<td>{{ $contador }}</td>
			<td>{{ $value->id }}</td>
			<td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
			<td>{{ $value->tipodocumento->nombre }}</td>
			<td>{{ $value->numero }}</td>
			<td>{{ isset($value->motivo)?$value->motivo->descripcion:'-' }}</td>
			<td>{{ $value->total }}</td>
			<!--<td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td> -->
			<td>{!! Form::button('<div class="glyphicon glyphicon-eye-open"></div> Ver', array('onclick' => 'modal (\''.URL::route($ruta["show"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_ver.'\', this);', 'class' => 'btn btn-xs btn-info')) !!}</td>
			<td>{!! Form::button('<div class="glyphicon glyphicon-print"></div> Imprimir', array('onclick' => 'window.open(\'movimientoalmacen/pdfComprobante/'.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
			<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
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