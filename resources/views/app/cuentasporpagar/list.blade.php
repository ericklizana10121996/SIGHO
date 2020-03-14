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
				<?php
				$estadopago = '';
				if ($value->estadopago == 'PP') {
					$estadopago = 'Pendiente';
				}elseif ($value->estadopago == 'P') {
					$estadopago = 'Pagado';
				}

				?>
	            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
	            @if($value->fechaingreso!="")
	            	<td>{{ date('d/m/Y',strtotime($value->fechaingreso)) }}</td>
	            @else
	            	<td>{{ '-' }}</td>
	            @endif
	            @if($value->tipomovimiento_id==3)
	            	<td>{{ $value->tipodocumento2.' '.$value->serie.'-'.$value->numero }}</td>
	            @else
	            	<td>{{ $value->formapago.' '.$value->voucher }}</td>
	            @endif
	            <td>{{ $value->proveedor2 }}</td>
	            <td align='right'>{{ number_format($value->total,2,'.','') }}</td>
	            <td align='right'>{{ number_format($value->totalpagado,2,'.','') }}</td>
	            <td>{{ $value->comentario }}</td>
	            <td align="center">{{ $estadopago }}</td>
	            <td align="center">{{ $value->responsable }}</td>
	            @if($value->tipomovimiento_id==3)
	            	<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'compra/pdfComprobante?movimiento_id='.$value->id.'&guia=NO\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
	            	<td align="center"> - </td>
	            @else
	            	<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'cuentasporpagar/pdfComprobante?id='.$value->id.'&\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
	            	@if($value->estadopago=='PP')
	            		<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div>', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
	            	@else
	            		<td align="center"> - </td>	
	            	@endif
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