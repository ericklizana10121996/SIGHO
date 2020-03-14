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
            <td>{{ $value->tipopago }}</td>
            <td>{{ $value->plan_id>0?$value->plan->nombre:" - " }}</td>
			<td>{{ ($value->tipopago=='Convenio')?($value->tarifario->codigo." ".$value->tarifario->nombre):$value->nombre }}</td>
            <td>{{ $value->tiposervicio->nombre }}</td>
            <td>{{ $value->precio }}</td>
            <td>{{ $value->modo }}</td>
            <td align="right">{{ $value->pagodoctor }}</td>
            <td align="right">{{ $value->pagohospital }}</td>
            @if($user->usertype_id==1 || $user->usertype_id==4)
				<td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
				<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
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
</div>
@endif