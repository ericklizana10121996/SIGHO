<?php
use App\Kardex;
?>
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
			
			<td>{!! Form::hidden('convenio_id'.$contador, $value->id, array('id' => 'convenio_id'.$contador)) !!}{{ $contador }}</td>
			<td>{{ $value->nombre }}</td>
			<td>{!! Form::text('txtKayros'.$contador, $value->kayros, array('class' => '', 'size' => '7', 'id' => 'txtKayros'.$contador, 'placeholder' => '', 'data-inputmask' => '\'alias\': \'decimal\', \'groupSeparator\': \',\', \'autoGroup\': false')) !!}
			</td>
			<td>{!! Form::text('txtCopago'.$contador, $value->copago, array('class' => '', 'size' => '7', 'id' => 'txtCopago'.$contador, 'placeholder' => '', 'data-inputmask' => '\'alias\': \'decimal\', \'groupSeparator\': \',\', \'autoGroup\': false')) !!}</td>

			<?php
			$cadena = '<script>$(document).ready(function() {';
			$cadena .= '$(\'#txtKayros'.$contador.'\').inputmask("decimal", { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });';
			$cadena .= '$(\'#txtCopago'.$contador.'\').inputmask("decimal", { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });';
			$cadena .= '$(\'#txtKayros'.$contador.'\').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
				if (key == \'13\') {
					agregarconvenio(\''.$contador.'\');
				}
			});';
			$cadena .= '$(\'#txtCopago'.$contador.'\').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
				if (key == \'13\') {
					agregarconvenio(\''.$contador.'\');
				}
			});';
			$cadena .= '});</script>';
			echo $cadena;
			?>
			
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
{!! $paginacion or '' !!}
@endif