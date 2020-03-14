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
		@if($value->situacion == 'N')
		<tr>
		@elseif($value->situacion == 'U')
		<tr style="background:rgba(215,57,37,0.50)">
		@elseif($value->situacion == 'A')
		<tr style="background:rgba(48,215,37,0.50)">
		@endif
			<td>{{ $contador }}</td>
            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td>{{ $value->tipodocumento->nombre }}</td>
            <td>{{ $value->serie.'-'.$value->numero }}</td>
            <td>{{ $value->numeroref }}</td>
            @if($value->tipodocumento_id2==5)
            	<td>{{ $value->paciente2 }}</td>
            @else
            	<td>{{ $value->bussinesname }}</td>
            @endif
            <td>{{ $value->total }}</td>
            @if($value->situacionbz=='L')
				<td align="center">LEIDO</td>
			@elseif($value->situacionbz=='E')
				<td align="center">ERROR</td>
            @else
            	<td align="center">PENDIENTE</td>
            @endif
            @if($value->situacionsunat=='L')
                <td align="center">ACEPTADO</td>
            @elseif($value->situacionsunat=='E')
				<td align="center">ERROR</td>
            @elseif($value->situacionsunat=='R')
				<td align="center">RECHAZADO</td>
            @elseif($value->situacionsunat=='P')
				<td align="center">ACEPTADO</td>
            @else
            	<td align="center">PENDIENTE</td>
            @endif
            <td align="center">{{ $value->mensajesunat }}</td>
            <td align="center"> {{ $value->responsable->nombres }}</td>
  			<td>{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'imprimir (\''.$value->id.'\');', 'class' => 'btn btn-xs btn-info')) !!}</td>
			 @if($value->situacion=='N')
                @if($user->usertype_id==8 || $user->usertype_id==1)
                    <td >{!! Form::button('<div class="glyphicon glyphicon-trash"></div> Anular', array('onclick' => 'modal (\''.URL::route($ruta["anular"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_anular.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
                @else
                	<td> - </td>
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