@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class="table-responsive">
<table id="example1" class="table table-bordered table-striped table-condensed table-hover">
	<thead>
		<tr>
			@foreach($cabecera as $key => $value)
				<th @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		<?php
		$contador = $inicio + 1;
		?>
		@foreach ($lista as $key => $value)
			@if($value->situacion == 'N')
				<tr>
			@elseif($value->situacion == 'U')
				<tr style="background:rgba(215,57,37,0.50)">
			@elseif($value->situacion == 'A')
				<tr style="background:rgba(48,215,37,0.50)">
			@endif
				<?php
					$estadopago = '';
					

					$abreviatura="G"; 
					
	                $nombrepaciente = '';
				?>

				<td>{{ $contador }}</td>
				<td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
				<td class="marcador" {{ 'id='.$contador }}>{{ utf8_encode($value->numero) }}</td>
				
				@if ( $value->persona_id !== null)
					<td>{{ $value->apellidopaterno.' '.$value->apellidomaterno.' '.$value->nombres }}</td>
				@else
					<td>{{ $nombrepaciente }}</td>
				@endif
				
				<td>{{ $value->total }}</td>
				<td>{{ $value->situacion }}</td>
				<td>{{ $value->comentario }}</td>
				
				@if($value->situacion == 'N')
					
					<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Impirmr', array('onclick' => 'window.open(\'caja/pdfRecibo?id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
				@endif
				
			</tr>
			<?php
			$contador = $contador + 1;
			?>
		@endforeach
	</tbody>
	<tfoot>
		<tr>
			@foreach($cabecera as $key => $value)
				<th @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</tfoot>
</table>
</div>
{!! $paginacion or '' !!}
@endif

<style type="text/css">
	.visitado{
		background: #00c0ef;
	}
</style>