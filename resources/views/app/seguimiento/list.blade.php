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
			<td>{{ date("d/m/Y H:i:s",strtotime($value->fechaenvio)) }}</td>
            <td>{{ $value->areaenvio->nombre }}</td>
            <td>{{ $value->historia->numero }}</td>
            <td>{{ $value->historia->persona->apellidopaterno.' '.$value->historia->persona->apellidomaterno.' '.$value->historia->persona->nombres }}</td>
            <td>{{ $value->comentario }}</td>
            <td>{{ $value->personaenvio2 }}</td>
            @if($user->usertype_id==1 || $user->usertype_id==16)
	            @if($value->situacion=='E')
					<td>{!! Form::button('<div class="glyphicon glyphicon-check"></div> Aceptar', array('onclick' => 'modal (\''.URL::route($ruta["aceptar"], array($value->id, 'listar'=>'SI')).'\', \'Aceptar\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
					<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Rechazar', array('onclick' => 'modal (\''.URL::route($ruta["rechazar"], array($value->id, 'SI')).'\', \'Rechazar\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
				@else
					@if($value->situacion=='A')
						<td>{!! Form::button('<div class="glyphicon glyphicon-check"></div> Retornar', array('onclick' => 'modal (\''.URL::route($ruta["retornar"], array($value->id, 'listar'=>'SI')).'\', \'Retornar\', this);', 'class' => 'btn btn-xs btn-success')) !!}</td>
					@else
						<td> - </td>
					@endif
				@endif
			@else
				<td> - </td>
				<td> - </td>
			@endif
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
@endif