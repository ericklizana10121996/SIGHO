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
			<td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td>{{ $value->medico }}</td>
            <td>{{ $value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres }}</td>
            @if($value->servicio_id>0)
            	<td>{{ $value->servicio }}</td>
            @else
            	<td>{{ $value->servicio2 }}</td>
            @endif
            <td>{{ number_format($value->pagodoctor,2,'.','') }}</td>
            <td align="center">{{ $value->situacion=='P'?'Pagado':'Pendiente' }}</td>
            <td>{{ $value->recibo }}</td>
            @if($value->situacion=='P')
			<td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
            @else
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