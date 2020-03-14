@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class="table-responsive">
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
			$tipomedico = '';
			if ($value->tipomedico == 'E') {
				$tipomedico = 'Especialista';
			}elseif ($value->tipomedico == 'G') {
				$tipomedico = 'General';
			}
			?>
			<td>{{ $contador }}</td>
			<td>{{ $value->apellidopaterno.' '.$value->apellidomaterno.' '.$value->nombres }}</td>
			<td>{{ $value->especialidad->nombre }}</td>
			<td>{{ $tipomedico }}</td>
			<td>{{ $value->dni }}</td>
			<td>{{ $value->rne }}</td>
			<td>{{ $value->cmp }}</td>
			<td>{{ $value->telefono }}</td>
            @if($modo=="popup")
            <td>{!! Form::button('<div class="glyphicon glyphicon-check"></div>', array('onclick' => 'seleccionarMedico(\''.$value->id.'\');', 'class' => 'btn btn-xs btn-danger')) !!}</td>
            @else
			<td>{!! Form::button('<div class="glyphicon glyphicon-list"></div> Horarios', array('onclick' => 'modal (\''.URL::route('horario.index', array('person_id' => $value->id, 'listar'=>'SI')).'\', \'Horarios '.$value->apellidopaterno.' '.$value->apellidomaterno.' '.$value->nombres.'\', this);', 'class' => 'btn btn-xs btn-default')) !!}</td>
			<td>{!! Form::button('<div class="glyphicon glyphicon-list"></div> Guardias', array('onclick' => 'modal (\''.URL::route('guardia.index', array('person_id' => $value->id, 'listar'=>'SI')).'\', \'Guardias '.$value->apellidopaterno.' '.$value->apellidomaterno.' '.$value->nombres.'\', this);', 'class' => 'btn btn-xs btn-default')) !!}</td>
			<td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
			<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
            @endif
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
</div>
@endif