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
            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td>{{ $value->paciente2 }}</td>
            @if($value->total>0)
                  <td align="center">{{ ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero }}</td>
            @else
                  <td align="center">{{ 'PREF. '.$value->numero2 }}</td>
            @endif
            <td align="right">{{ number_format($value->pagohospital*$value->cantidad,2,'.','') }}</td>
            @if($value->tiposervicio_id==2)
                <td align="right">{{ number_format($value->pagohospital*$value->cantidad*0.12,2,'.','')}}</td>
            @elseif($value->tiposervicio_id==4)
                <td align="right">{{ number_format($value->pagohospital*$value->cantidad*0.1,2,'.','')}}</td>
            @elseif($value->tiposervicio_id==5)
                <td align="right">{{ number_format($value->pagohospital*$value->cantidad*0.1,2,'.','')}}</td>
            @else
                <td align="right"> - </td>
            @endif
            <td>{{ $value->medico }}</td>
            @if($value->servicio_id>0)
                <td>{{ $value->servicio }}</td>
            @else
                <td>{{ $value->servicio2 }}</td>
            @endif
            @if($value->referido_id>0)
            	<td>{{ $value->referido }}</td>
            @else
				<td>NO REFERIDO</td>
            @endif
            <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
</div>
@endif