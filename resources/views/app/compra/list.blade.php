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
				$estadopago = '';
				if ($value->estadopago == 'PP') {
					$estadopago = 'Pendiente';
				}elseif ($value->estadopago == 'P') {
					$estadopago = 'Pagado';
				}

			?>
			<td>{{ $contador }}</td>
			<td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
			<td>{{ $value->person->bussinesname }}</td>
			<td>{{ $value->serie.'-'.$value->numero }}</td>
			<td>{{ $value->tipodoc }}</td>
			<td>{{ $estadopago }}</td>
			<td>{{ $value->total }}</td>
			<!--<td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td> -->
			<td>{!! Form::button('<div class="glyphicon glyphicon-eye-open"></div> Ver', array('onclick' => 'modal (\''.URL::route($ruta["show"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_ver.'\', this);', 'class' => 'btn btn-xs btn-info')) !!}</td>
			<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Comprobante', array('onclick' => 'window.open(\'compra/pdfComprobante?movimiento_id='.$value->id.'&guia=NO\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
			@if($user->usertype_id==11 && date("d/m/Y",strtotime($value->fecha))==date("d/m/Y"))
				<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
			@else
				@if($user->usertype_id==1 || $user->usertype_id==8)
					<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>	
				@else
					<td align="center"> - </td>
				@endif
			@endif
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