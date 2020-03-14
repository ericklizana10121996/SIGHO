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
            <td align="right">{{ $value->numero2 }}</td>
            @if($value->tipodocumento_id==5)
            	<td>{{ $value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres }}</td>
            @else
            	<td>{{ $value->persona->bussinesname }}</td>
            @endif
            <td align="right">{{ $value->total }}</td>
            @if($value->situacion=='N')
			<td align="center">OK</td>
            @elseif($value->situacion=='A')
			<td align="center">Nota Credito</td>
            @elseif($value->situacion=='P')
            	<td align="center">PENDIENTE</td>
            @elseif($value->situacion=='U')
                  <td align="center">Anulada</td>
            @endif
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
            <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> ', array('onclick' => 'window.open(\'ventaadmision/pdfComprobante?id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
            @if($value->situacion=='P')
			<td>{!! Form::button('<div class="glyphicon glyphicon-usd"></div> ', array('onclick' => 'modal (\''.URL::route($ruta["cobrar"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_cobrar.'\', this);', 'class' => 'btn btn-xs btn-success')) !!}</td>
            @else
            	<td align="center"> - </td>
            @endif
            @if($value->situacion=='P' || $value->situacion=='N')
                  @if($user->usertype_id==8 || $user->usertype_id==1)
                        <td >{!! Form::button('<div class="glyphicon glyphicon-trash"></div> Anular', array('onclick' => 'modal (\''.URL::route($ruta["anular"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_anular.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
                  @else
                        @if($user->usertype_id==7 && date('d/m/Y',strtotime($value->fecha))==date("d/m/Y"))
                              <td >{!! Form::button('<div class="glyphicon glyphicon-trash"></div> Anular', array('onclick' => 'modal (\''.URL::route($ruta["anular"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_anular.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
                        @else
                              <td align="center"> - </td>       
                        @endif
                  @endif
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
@endif