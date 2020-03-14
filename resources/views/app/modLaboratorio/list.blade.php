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
				<td align="center">{{ $contador }}</td>
	            <td align="center">{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
	            <td align="center">{{ date('H:i:s',strtotime($value->created_at)) }}</td>
	            <td align="center">{{ number_format($value->total) }}</td>
	            
	            <td align="center">{{ $value->responsable->nombres }}</td>

	            <td align="center">
	            	{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}
	            	
					{!! Form::button('<div class="glyphicon glyphicon-file"></div>Excel', array('onclick' => 'window.open(\'modlaboratorio/excel2?id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title'=>'Reporte')) !!}
	          
            	
                @if($user->usertype_id==1)
	            	{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}
	            @endif
	            </td>
	           
			</tr>
			<?php
			$contador = $contador + 1;
			?>
			@endforeach
		</tbody>
	</table>
</div>
@endif