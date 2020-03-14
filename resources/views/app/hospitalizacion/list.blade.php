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
			<td align="center">{{ $value->habitacion->nombre }}</td>
			<td align="left">{{ $value->paciente2 }}</td>
			<td align="center">{{ $value->numerohistoria }}</td>
			<td align="center">{{ $value->fecha }}</td>
			<td align="center">{{ substr($value->hora,0,5) }}</td>
			<td align="center">{{ $value->tipopaciente }}</td>
			@if($value->medico_id>0)
				<td align="left">{{ $value->medico->apellidopaterno.' '.$value->medico->apellidomaterno.' '.$value->medico->nombres }}</td>
			@else
				<td align="center"> - </td>
			@endif
			<td align="center">{{ $value->paquete }}</td>
			<td align="center">{{ $value->modo }}</td>
            <td align="center">
            @if($value->situacion=='H') 'HOSPITALIZADO'
            @else {{$value->tipoalta}} ({{$value->detalle}})
            @endif
            </td>
            @if($value->situacion=='H')
            	<td align="center"> - </td>
            	<td align="center"> - </td>
            	@if($user->usertype_id==7 || $user->usertype_id==1 || $user->usertype_id==12 || $user->person_id==56606)
            		<td align="center">{!! Form::button('<div class="glyphicon glyphicon-chevron-up"></div>', array('onclick' => 'modal (\''.URL::route($ruta["alta"], array($value->id, 'listar'=>'SI')).'\', \'Alta\', this);', 'class' => 'btn btn-xs btn-info', 'title' => 'Alta')) !!}</td>
            		<td align="center">{!! Form::button('<div class="glyphicon glyphicon-pencil"></div>', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning', 'title' => 'Editar')) !!}</td>
					<td align="center">{!! Form::button('<div class="glyphicon glyphicon-remove"></div>', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger', 'title' => 'Eliminar')) !!}</td>
            	@else
            		<td align="center"> - </td>
            		<td align="center"> - </td>
            		<td align="center"> - </td>
            	@endif
			@else
				<td align="center">{{ $value->fechaalta }}</td>
				<td align="center">{{ $value->usuarioalta->nombres }}</td>
				@if($value->tipopaciente=='Convenio')
					<td style='font-size:12px' align='center'><input type="checkbox" id="chk{{$value->id}}" {{ ($value->descargado=='S'?'checked':'') }} onclick="cargado2(this.checked,{{ $value->id }},0)"/></td>
				@else
					<td align="center"> - </td>
				@endif
				<td align="center"> - </td>
				<td align="center"> - </td>
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