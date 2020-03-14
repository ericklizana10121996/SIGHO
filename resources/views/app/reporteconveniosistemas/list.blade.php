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
            <td>{{ $value->id_mov }}</td>
            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td>{{ is_null($value->serie)?$value->numero:$value->serie.'-'.$value->numero }}</td>
            <td>{{ $value->doctor_resp }}</td>
            <td>{{ $value->conceptopago }}</td>
            <td>{{ $value->comentario }}</td>
            <td>{{ $value->total_venta }}</td>
            <td>{{ $value->responsable_op }}</td>
        </tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
</div>
@endif