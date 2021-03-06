@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class='table-responsive'>
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
            <td>{{ $value->paciente }}</td>
            @if($value->servicio_id>0)
            	<td>{{ $value->servicio }}</td>
            @else
            	<td>{{ $value->servicio2 }}</td>
            @endif
            <td>{{ $value->responsable2 }}</td>
            <td>{{ number_format($value->pagotarjeta,2,'.','') }}</td>
            <td align="center">{{ $value->situaciontarjeta=='E'?'Pagado':'Pendiente' }}</td>
            @if(!is_null($value->situaciontarjeta) && $value->situaciontarjeta=='E')
            	<td align="center">{{ date('d/m/Y',strtotime($value->fechaentrega)) }}</td>
            	<td align="center">{{ $value->usuarioentrega }}</td>
            @else
            	<td align="center"> - </td>
            	<td align="center"> - </td>
            @endif
            <td>{{ $value->recibo }}</td>
            @if(!is_null($value->situaciontarjeta) && $value->situaciontarjeta!='E')
				<td align="center">{!! Form::button('<div class="glyphicon glyphicon-usd"></div> Pagar', array('onclick' => 'modal (\''.URL::route($ruta["pagar"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-success')) !!}</td>
				<td align="center">{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Elimninar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
            @else
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