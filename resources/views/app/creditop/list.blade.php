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
	            <td>{{ $value->numero }}</td>
	            <td>{{ $value->paciente }}</td>
	            <td align="center">{{ number_format($value->total,2,'.','') }}</td>
	            @if($value->situacion=='P' || $value->situacion=='B')
	            <td>PENDIENTE</td>
	            @elseif($value->situacion=='C')
	            <td>COBRADO</td>
	            @elseif($value->situacion=='U')
	            <td>ANULADO</td>
	            @endif
	  			<td>{{ $value->responsable }}</td>
	            @if($value->situacion=='C' || $value->situacion=='B' || $value->situacion=='U')
	            	@if($value->total>0)
	                	<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'ticket/pdfComprobante?ticket_id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title'=>'Comprobante A4')) !!}</td>
	                	<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'ticket/pdfComprobante3?ticket_id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title'=>'Comprobante Ticketera')) !!}</td>
	                @else
	                	<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'ticket/pdfPrefactura?ticket_id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title' => 'Prefactura')) !!}</td>
	                @endif
	            @else
	                <td align="center"> - </td>
	            @endif
	            @if($value->situacion=='P')
					<td align="center">{!! Form::button('<div class="glyphicon glyphicon-remove"></div>', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger', 'title'=> 'Eliminar')) !!}</td>
				@else
					<td align="center"> - </td>
				@endif
				@if($user->usertype_id==1 && $value->situacion!='U' && $value->total==0)
					<td align="center">{!! Form::button('<div class="glyphicon glyphicon-pencil"></div>', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning', 'title' => 'Editar')) !!}</td>
				@else
					<td align="center"> - </td>
				@endif
				@if(($user->usertype_id==1 || $user->usertype_id==7) && $value->total==0 && $value->situacion!='U')
					<td align="center">{!! Form::button('<div class="glyphicon glyphicon-minus"></div>', array('onclick' => 'modal (\''.URL::route($ruta["anular"], array($value->id, 'listar'=>'SI')).'\', \'Anular\', this);', 'class' => 'btn btn-xs btn-danger', 'title' => 'Anular')) !!}</td>
				@else
					<td align="center"> - </td>
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