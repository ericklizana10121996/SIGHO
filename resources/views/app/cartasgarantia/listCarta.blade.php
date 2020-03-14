@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else

{!! $paginacion or '' !!}
<div class="table-responsive">

	<table id="example1" class="table table-bordered table-striped table-condensed table-hover">

		<thead>
			<tr>
				@foreach($cabecera as $key => $value)
					<th class="text-center" @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!} @if($value['valor']=='Marcar') <input type='checkbox' onclick='marcarTodos(this.checked);' title='Marcar Todos' /> @endif</th>
				@endforeach
			</tr>
		</thead>
		<tbody>
			<?php
			$contador = $inicio + 1;
			$dat="";
			?>
			@foreach ($lista as $key => $value)
			@if($value->tipoventa == 'A')
			<tr style="background:rgba(215,0,0,0.50)">
			@else
			<tr>
			@endif
	            <td>{{ $value->numerodias }}</td>
	            <td>{{ $value->empresa }}</td>
	            <td align="center">{{ $value->documentos }}</td>
	            @if($value->fechacarta!="")
	            	<td align="center">{{ date('d/m/Y',strtotime($value->fechacarta)) }}</td>
	            @else
	            	<td align="center">{{ '' }}</td>
	            @endif
	            <td>{{ $value->responsable }}</td>
	            <td align="center">{{ number_format($value->total,2,'.','') }}</td>
	            <td align="center">{!! Form::button('<div class="glyphicon glyphicon-save-file"></div>', array('onclick' => 'window.open(\'cartasgarantia/wordPlan?plan_id='.$value->plan_id.'&numero='.$value->numerodias.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title' => 'Word')) !!}</td>
	            @if($value->tipoventa!="A")
	            	<td align="center">{!! Form::button('<div class="glyphicon glyphicon-remove"></div>', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->plan_id,$value->numerodias, 'SI')).'\', \'Anular\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
	            @else
	            	<td align="center">{{ '-' }}</td>
	            @endif
			</tr>
			<?php
			$contador = $contador + 1;
			$dat.=$value->id.",";
			?>
			@endforeach
		</tbody>
	</table>
</div>
@endif