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
		$title = '';
		$color = '';
		?>
		@foreach ($lista as $key => $value)
			<?php
				if($value->situacion=='A'){
					$title='Anulado por '.$value->anulacion->nombres;
					$color='color:red';
				}else{
					$title='';
					$color='';
				}
			?>
		<tr title='{{ $title }}' style='{{ $color }}'>
			<td style='font-size:12px'>{{ $contador }}</td>
            <td style='font-size:12px'>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
            <td style='font-size:12px'>{{ $value->doctor }}</td>
            <td style='font-size:12px'>{{ $value->especialidad }}</td>
            <td style='font-size:12px'>{{ $value->tipopaciente }}</td>
            <td style='font-size:12px'>{{ $value->paciente }}</td>
            <td style='font-size:12px'>{{ $value->telefono }}</td>
            <td style='font-size:12px'>{{ $value->historia }}</td>
            <td style='font-size:12px'>{{ substr($value->horainicio,0,5) }}</td>
            <td style='font-size:12px'>{{ substr($value->horafin,0,5) }}</td>
            <td style='font-size:12px'>{{ $value->comentario }}</td>
            <td style='font-size:12px'>{{ $value->usuario->nombres }}</td>
            <td style='font-size:12px'>{{ ($value->usuario2_id>0?($value->usuario2->nombres." ".date("d/m/Y H:i:s",strtotime($value->updated_at))):'-') }}</td>
            <td style='font-size:12px'>
            	@if(true)
            	<input type="checkbox" onchange="guardarCheckado('atendio',$(this).is(':checked'),{{$value->id}},this);" <?php if($value->atendio>0){?>checked=""<?php }?>>
            	@else
            	@if($value->atendio>0) SI
            	@else NO
            	@endif
            	@endif
        	</td>
            <td style='font-size:12px'>
            	@if(true)
            	<input type="checkbox" onchange="guardarCheckado('ficha',$(this).is(':checked'),{{$value->id}},this);" <?php if($value->ficha>0){?>checked=""<?php }?>>
            	@else
            	@if($value->ficha>0) SI
            	@else NO
            	@endif
            	@endif
            </td>
            <td style='font-size:12px'>
            	@if(true)
            	<input type="checkbox" onchange="guardarCheckado('soat',$(this).is(':checked'),{{$value->id}},this);" <?php if($value->soat>0){?>checked=""<?php }?>>
            	@else
            	@if($value->soat>0) SI
            	@else NO
            	@endif
            	@endif
            </td>
            <td style='font-size:12px'>
            	@if(true)
            	<input type="checkbox" onchange="guardarCheckado('sctr',$(this).is(':checked'),{{$value->id}},this);" <?php if($value->sctr>0){?>checked=""<?php }?>>
            	@else
            	@if($value->sctr>0) SI
            	@else NO
            	@endif
            	@endif
            </td>
            <td>
            	@if($value->movimiento_id > 0)
            	{{$value->movimiento->numero}}
            	@else
            	-
            	@endif
            </td>
            @if($value->situacion=='P')
	  			<td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div>', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning', 'title' => 'Editar')) !!}</td>
	  			<td>{!! Form::button('<div class="glyphicon glyphicon-minus"></div>', array('onclick' => 'modal (\''.URL::route($ruta["anular"], array($value->id, 'SI')).'\', \''.$titulo_anular.'\', this);', 'class' => 'btn btn-xs btn-info', 'title' => 'Anular')) !!}</td>
	  			@if($user->usertype_id==1 || $user->usertype_id==7)
					<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div>', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger', 'title' => 'Eliminar')) !!}</td>
				@else
					<td> - </td>
					<td> - </td>
				@endif
			@else
				<td> - </td>
				<td> - </td>
				<td> - </td>
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