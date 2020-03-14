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
				$estadopago = 'Pendiente';
				/*if ($value->estadopago == 'PP') {
					$estadopago = 'Pendiente';
				}elseif ($value->estadopago == 'P') {
					$estadopago = 'Pagado';
				}*/

			?>
			<td>{{ $contador }}</td>
			<td>{{ $value->fecha }}</td>
			<td>{{ $value->numero }}</td>
			<td>{{ $estadopago }}</td>
			<td>{{ $value->total }}</td>
			<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Comprobante', array('onclick' => 'window.open(\'venta/pdfComprobante?venta_id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
			<!--<td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td> -->
			<td>{!! Form::button('<div class="glyphicon glyphicon-eye-open"></div> Ver', array('onclick' => 'modal (\''.URL::route($ruta["show"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_ver.'\', this);', 'class' => 'btn btn-xs btn-info')) !!}</td>
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