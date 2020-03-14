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
		<tr id='td{{ $value->id }}'>
			<?php
			if($value->situacionentrega!="E" && $value->situacionentrega!="A"){
			?>		
				<td><input type="checkbox" id="chk{{$value->id}}" onclick="agregarDetalle(this.checked,{{ $value->id }})"/></td>
			<?php
			}else{
			?>
				<td> - </td>
			<?php
			}
			?>
			<td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td>{{ $value->medico }}</td>
            <td>{{ $value->paciente }}</td>
            <td align="center">{{ $value->condicionpaciente==''?'-':$value->condicionpaciente}}</td>
            
            @if ($value->convenio != '') 
            	<td align="center">{{ $value->convenio }} </td>
        	@else
            	<td align="center">{{ "-" }} </td>
            @endif
            @if($value->servicio_id>0)
            	<td>{{ $value->servicio }}</td>
            @else
            	<td>{{ $value->servicio2 }}</td>
            @endif
            <td>{{ $value->responsable2 }}</td>
            <td align="center">
            	@if($value->situacionentrega=='E') Pagado
            	@elseif($value->situacionentrega=='A') Eliminado
            	@else Pendiente
            	@endif
        	</td>
            @if(!is_null($value->situacionentrega) && $value->situacionentrega=='E')
            	<td align="center">{{ date('d/m/Y',strtotime($value->fechaentrega)) }}</td>
            	<td align="center">{{ $value->usuarioentrega }}</td>
            	<td>{{ $value->recibo }}</td>
				<td>{{ (empty(explode('|',$value->feccita)[0]))?"-":explode('|',$value->feccita)[0] }}</td>
				<td>{{ (empty(explode('|',$value->feccita)[0]))?"-":((explode('|',$value->feccita)[1]==1)?"SI":"NO") }}</td>
				<td>{{ (empty(explode('|',$value->feccita)[0]))?"-":((explode('|',$value->feccita)[2]==1)?"SI":"NO") }}</td>
				<td>{{$value->idticket}}</td>
            @else
            	<td align="center"> - </td>
            	<td align="center"> - </td>
            	<td align="center"> - </td>
				<td>{{ (empty(explode('|',$value->feccita)[0]))?"-":explode('|',$value->feccita)[0] }}</td>
				<td>{{ (empty(explode('|',$value->feccita)[0]))?"-":((explode('|',$value->feccita)[1]==1)?"SI":"NO") }}</td>
				<td>{{ (empty(explode('|',$value->feccita)[0]))?"-":((explode('|',$value->feccita)[2]==1)?"SI":"NO") }}</td>

				<td>{{ (empty(explode('|',$value->feccita)[0]))?"-":((explode('|',$value->feccita)[3]==1)?"SI":"NO") }}</td>
				<td>{{ (empty(explode('|',$value->feccita)[0]))?"-":((explode('|',$value->feccita)[4]==1)?"SI":"NO") }}</td>
				

				<td>{{$value->idticket}}</td>
            	<td align="center">
            	@if($value->situacionentrega!='A')
            	{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Elimninar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}
            	</td>
            	@else -
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
<script>
validarCheck();
</script>