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
		<tr>
            <td style='font-size:12px'>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td style='font-size:12px'>{{ $value->paciente2 }}</td>
            @if($value->total>0)
                  <td align="center" style='font-size:12px'>{{ ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero }}</td>
            @else
                  <td align="center" style='font-size:12px'>{{ 'PREF. '.$value->numero2 }}</td>
            @endif
            @if($value->precio>0)
                <td align="right" style='font-size:12px'>{{ number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio)*$value->cantidad,2,'.','') }}</td>
            @else
                <td align="right" style='font-size:12px'>{{ number_format(($value->precioconvenio>0?$value->precioconvenio:$value->precio2)*$value->cantidad,2,'.','') }}</td>
            @endif
            <td style='font-size:12px'>{{ $value->plan }}</td>
            <td style='font-size:12px'>{{ $value->medico }}</td>
            <td style='font-size:12px'>{{ round($value->cantidad,0) }}</td>
            @if($value->servicio_id>0)
                <td style='font-size:12px'>{{ $value->servicio }}</td>
            @else
                <td style='font-size:12px'>{{ $value->servicio2 }}</td>
            @endif
            @if($value->referido_id>0)
            	<td style='font-size:12px'>{{ $value->referido }}</td>
            @else
				<td style='font-size:12px'>NO REFERIDO</td>
            @endif
            <td align="center" style='font-size:12px'>{{ $value->situacion=='P'?'-':($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta):'CONTADO') }}</td>
            <td align="center" style='font-size:12px'>{{ ($value->situacion=='P'?'Pendiente':'Pagado') }}</td>
            <td align="center" style='font-size:12px'>{{ $value->responsable }}</td>
            <td style='font-size:12px'>{{ $value->historia }}</td>
            <td style='font-size:12px'>{{ $value->mensajesunat }}</td>
            <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
</div>
@endif
