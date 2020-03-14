@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class="table-responsive">
	<table id="tablaLista" class="table table-bordered table-striped table-condensed table-hover">

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
			<tr {{ ($value->delete_at!=null?'style=background-color:coral;':($value->situacion=='N'?'style=background-color:lightgreen;':'style=background-color:yellow;')) }}>
				<td style='font-size:12px'>{{ $contador }}</td>
	            <td style='font-size:12px' align="center">{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
	            <td style='font-size:12px' align="center">{{ date('d/m/Y',strtotime($value->fechaRealizacion)) }}</td>
	            <td style='font-size:12px' align="right">{{ $value->numero_ceros }}</td>
	            <td style='font-size:12px' align="center">{{ $value->medico }}</td>
	            <td style='font-size:12px' align="center">{{ $value->paciente }}</td>
	           	<td style='font-size:12px' align="center">{{ $value->historia }}</td>
	           	<td style='font-size:12px' align="center">{{ $value->nombre_cirugia }}</td>

	            <td style='font-size:12px' align="center">{{ ($value->situacion == 'N'?'Pendiente':'Pagado') }}</td>

	            <td style='font-size:12px' align="center">{{ $value->plan }}</td>
	
	            <td style='font-size:12px' align="right">{{ number_format($value->pago_total,2,'.','') }}</td>
	  			<td style='font-size:12px' align="center">{{ $value->nombre_responsable }}</td>
	  			<td style='font-size:12px' align="center">{{ is_null($value->nombre_responsable_actualiza)?'-':$value->nombre_responsable_actualiza }}</td>
	  			<td>

					@if($value->situacion =='N')
						{!! Form::button('<div class="glyphicon glyphicon-pencil"></div>', array( 'title' => 'Editar', 'onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}
					@endif


 					@if($value->situacion !='P' && ($user->usertype_id!=4 || $user->usertype_id==1))
						{!! Form::button('<div class="glyphicon glyphicon-usd"></div>', array('onclick' => 'modal (\''.URL::route($ruta["show"], array($value->id, 'SI')).'\', \''.$titulo_ver.'\', this);', 'class' => 'btn btn-xs btn-success', 'title' => 'Pagar')) !!}
					@endif

					@if($user->usertype_id==1)
						{!! Form::button('<div class="glyphicon glyphicon-remove"></div>', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger', 'title' => 'Eliminar')) !!}

					@endif


					{{-- {!! Form::button('<div class="glyphicon glyphicon-ok"></div>', array('onclick' => 'modal (\''.URL::route($ruta["confirmar"], array($value->id,'SI')).'\', \''.$titulo_confirmar.'\', this);', 'class' => 'btn btn-xs btn-success', 'title' => 'Confirmar')) !!}
					 --}}
					{{-- {!! Form::button('<div class="glyphicon glyphicon-usd"></div>', array('onclick' => 'modal (\''.URL::route($ruta["pagar"], array('id'=>$value->id,'listarLuego'=> 'SI')).'\', \''.$titulo_pago.'\', this);', 'class' => 'btn btn-xs btn-success', 'title' => 'Confirmar')) !!}
 --}}
	  			</td>
	  		
  			</tr>
			<?php $contador = $contador + 1; ?>
			@endforeach
		</tbody>
	</table>
</div>
@endif