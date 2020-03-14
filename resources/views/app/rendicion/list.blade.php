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
			<td>{{ $contador }}</td>
            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            @if($value->empresa_id>0){
            	<td>{{ $value->empresa->bussinesname }}</td>
            @else
            	@if($value->persona_id>0 && $value->persona->bussinesname!="")
            		<td>{{ $value->persona->bussinesname }}</td>
            	@else
            		<td>{{ $value->persona2 }}</td>
            	@endif
            @endif
            @if($value->caja_id==4)
            	@if($value->formapago!="")
                        <td align="center">{{ $value->formapago }}</td>
                  @elseif($value->tipodocumento_id==7)
            		<td align="center">{{ 'BV' }}</td>
            	@elseif($value->tipodocumento_id==6)
            		<td align="center">{{ 'FT' }}</td>
            	@endif
            @else
            	<td align="center">{{ $value->formapago }}</td>
            @endif
            <td align="center">{{ $value->numero }}</td>
            <td align="left">{{ $value->conceptopago->nombre }}</td>
            <td align="right">{{ $value->total }}</td>
            <td align="left">{{ $value->comentario }}</td>
            @if($value->estadopago!='C')
                  <td>{!! Form::button('<div class="glyphicon glyphicon-edit"></div> ', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
                  <td align="center"> - </td>
            @else
                  <td align="center"> - </td>
                  <td>{!! Form::button('<div class="glyphicon glyphicon-list"></div> ', array('onclick' => 'modal (\''.URL::route($ruta["detalle"], array($value->id, 'listar'=>'SI')).'\', \'Detalle\', this);', 'class' => 'btn btn-xs btn-info')) !!}</td>
            @endif
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
@endif