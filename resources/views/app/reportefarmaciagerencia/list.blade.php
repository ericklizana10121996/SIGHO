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
            <td>{{ $value->persona2 }}</td>
           	<td align="center">{{ $value->formapago }}</td>
            <td align="center">{{ $value->voucher }}</td>
            <td align="left">{{ $value->area->nombre }}</td>
            <td align="left">{{ $value->conceptopago->nombre }}</td>
            <td align="right">{{ $value->numeroficha }}</td>
            <td align="right">{{ $value->total }}</td>
            <td align="left">{{ $value->comentario }}</td>
            <td>{!! Form::button('<div class="glyphicon glyphicon-edit"></div> ', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
@endif