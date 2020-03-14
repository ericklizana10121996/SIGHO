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
			<td>{{ $value->medico }}</td>
			<td>{{ $value->paciente2 }}</td>
			<td>{{ date('d/m/Y',strtotime($value->fechaingreso)) }}</td>
            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td align="center">
                @if($situacion == "H")
                {{ $value->numero }}
                @else
                <a target="_blank" href="facturacion/pdfLiquidacion?id={{$value->mov_id}}">{{ "F".$value->serie.'-'.$value->numero }}</a>
                @endif
            </td>
            <td align="right" ondblclick="actualizarPagoCero(this,{{$value->dmc_id}});">
            	{{ number_format(($value->pagodoctor),2,'.','') }}
            </td>
            <td>{{ date('d/m/Y',strtotime($value->fechaentrega)) }}</td>
            <td>{{ $value->voucher }}</td>
            <td>{{ $value->plan }}</td>
            <td>{{ round($value->cantidad,0) }}</td>
            <td>{{ $value->servicio2 }}</td>
            <td align="center">{{ $value->responsable }}</td>
            @if($situacion == "H")
            <td align="center">-</td>
            <td align="center">-</td>
            <td align="center">-</td>
            <td align="center">-</td>
            <td align="center">-</td>
            <td align="center">-</td>
            @else
            <td align="center">@if(!empty($value->fechapagodoctor) && strlen($value->fechapagodoctor)>0){{ date('d/m/Y',strtotime($value->fechapagodoctor)) }} @else - @endif</td>
            <td align="center">@if(!empty($value->recibopagodoctor) && strlen($value->recibopagodoctor)>0){{ $value->recibopagodoctor }} @else - @endif</td>
            <td align="center">
            	@if(empty($value->fechapagodoctor) || strlen($value->fechapagodoctor)==0 || empty($value->recibopagodoctor) || strlen($value->recibopagodoctor)==0)
            	<input type="checkbox" class="chkSeleccionados" data_id="{{$value->dmc_id}}" data_numero="{{ "F".$value->serie.'-'.$value->numero }}" data_total="{{$value->pagodoctor}}" onclick="verificarSeleccion(this);" disabled="">
            	@endif
        	</td>
        	<td align="center">@if($value->reportados>0) SI({{$value->reportados}}) @else NO @endif</td>
        	<td align="center">
        		@if((empty($value->fechapagodoctor) || strlen($value->fechapagodoctor)==0 || empty($value->recibopagodoctor) || strlen($value->recibopagodoctor)==0) && $value->reportados==0)
        		{!! Form::button('<div class="glyphicon glyphicon-remove"></div>', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->dmc_id, 'SI')).'\', \'Eliminar Pago Medico\', this);', 'class' => 'btn btn-xs btn-danger')) !!}
            	@endif
        	</td>
            <td align="center">
            @if((empty($value->fechapagodoctor) || strlen($value->fechapagodoctor)==0 || empty($value->recibopagodoctor) || strlen($value->recibopagodoctor)==0) && $value->reportados==0)
                <textarea class="form-control" style="min-width: 200px;" id='txt{{$value->dmc_id}}' iddet="{{$value->dmc_id}}" ondblclick="activarTextarea(this);" onblur='guardarComentario(this,{{ $value->dmc_id }});' readonly="">{{$value->comentario}}</textarea>
            @else
                {{$value->comentario}}
            @endif
            </td>
            @endif
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
</div>
<script>
revisarSeleccionados();
</script>
@endif