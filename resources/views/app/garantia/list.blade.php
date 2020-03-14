@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class='table-responsive'>
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
            <td>{{ $value->medico }}</td>
            @if(trim($value->paciente)=="")
            	<td>{{ $value->paciente2 }}</td>
            @else
            	<td>{{ $value->paciente }}</td>
            @endif
            <td>{{ $value->servicio2 }}</td>
            <td>{{ $value->recibo }}</td>
            <td>{{ number_format($value->pagodoctor*$value->cantidad,2,'.','') }}</td>
            <td>{{ $value->responsable }}</td>
            <td align="center">{{ $value->situacionentrega=='E'?'Pagado':'Pendiente' }}</td>
            @if(!is_null($value->situacionentrega) && $value->situacionentrega=='E')
            	<td align="center">{{ date('d/m/Y',strtotime($value->fechaentrega)) }}</td>
            	<td align="center">{{ $value->usuarioentrega }}</td>
            @else
            	<td align="center"> - </td>
            	<td align="center"> - </td>
            @endif
            @if(!is_null($value->situacionentrega) && $value->situacionentrega!='E')
            	@if($band || $user->usertype_id==1 || $user->usertype_id==7)
            		<td align="center">{!! Form::button('<div class="glyphicon glyphicon-usd"></div> Pagar', array('onclick' => 'modal (\''.URL::route($ruta["regularizar"], array($value->id, 'listar'=>'SI')).'\', \'Pagar\', this);', 'class' => 'btn btn-xs btn-success')) !!}</td>
                        <td align="center">{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'listar'=>'SI')).'\', \'Eliminar\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
                        <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'imprimirRecibo ('.$value->id.');', 'class' => 'btn btn-xs btn-warning', 'title' => 'Imprimir')) !!}</td>

                        <td align="center">{!! Form::button('<div class="glyphicon glyphicon-new-window"></div>', array('onclick' => 'modal (\''.URL::route($ruta["confirmarmedicos"], array($value->id, 'listar'=>'SI')).'\', \'Gestionar Pagos a Médicos\', this);', 'class' => 'btn btn-xs btn-success')) !!}</td>

            	@else
            		<td align="center"> - </td>
                        <td align="center"> - </td>
                        <td align="center"> - </td>                        
                        <td align="center">{!! Form::button('<div class="glyphicon glyphicon-new-window"></div>', array('onclick' => 'modal (\''.URL::route($ruta["confirmarmedicos"], array($value->id, 'listar'=>'SI')).'\', \'Gestionar Pagos a Médicos\', this);', 'class' => 'btn btn-xs btn-success')) !!}</td>

            	@endif
            @else
            	<td align="center"> - </td>
                  <td align="center"> - </td>
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