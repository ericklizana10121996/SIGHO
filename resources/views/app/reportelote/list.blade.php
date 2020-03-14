@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class="table-responsive">
<table id="example1" class="table table-bordered table-striped table-condensed table-hover">
	<thead>
		<tr>
			@foreach($cabecera as $key => $value)
				<th style='font-size:12px' class="text-center" @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		<?php
		$contador = $inicio + 1;
		?>
		@foreach ($lista as $key => $value)
        @if($value->meses<=6 && $value->meses>3)
            <tr style='background-color:#008000a6'>
        @elseif($value->meses<=3 && $value->meses>1)
            <tr style='background-color:yellow'>
        @elseif($value->meses<=1)
            <tr style='background-color:#ff00006b'>
        @else
            <tr>
        @endif
            <td>{{ $value->nombre }}</td>
            <td>{{ ($value->presentacion2) }}</td>
            <td>{{ ($value->origen2) }}</td>
            <td>{{ $value->lote }}</td>
            <td>{{ date('d/m/Y',strtotime($value->fechavencimiento)) }}</td>
            <td>{{ $value->queda }}</td>
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
</div>
@endif
