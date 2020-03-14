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
				<td>{{ $contador }}</td>
	            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
	            <td>{{ $value->hora }}</td>
	            <td>{{ $value->paciente }}</td>
	            <td>{{ $value->historia->numero }}</td>
	            <td align="center">{{ $value->responsable }}</td>
	            <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'analisis/pdfAnalisis?id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title'=>'Comprobante')) !!}</td>
	            <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
	            @if($user->usertype_id==1)
	            	<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
	            @else
	            	<td align='center'> - </td>
	            	<td align='center'> - </td>
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