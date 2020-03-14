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
            @if($value->empresa_id>0){
            	<td>{{ $value->empresa->bussinesname }}</td>
            @else
            	@if($value->persona_id>0 && $value->persona->bussinesname!="")
            		<td>{{ $value->persona->bussinesname }}</td>
            	@else
            		<td>{{ $value->persona2 }}</td>
            	@endif
            @endif
           	<td align="center">{{ $value->formapago }}</td>
            <td align="center">{{ $value->voucher }}</td>
            <td align="left">{{ $value->area->nombre }}</td>
            <td align="left">{{ $value->conceptopago->nombre }}</td>
            <td align="right">{{ $value->total }}</td>
            <td align="left">{{ $value->comentario }}</td>
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
@endif