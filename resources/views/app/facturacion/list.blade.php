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
				@if($value->situacion == 'U' || $value->situacionbz == 'E' || $value->situacionsunat == 'E')
					<tr style="background-color:#FA7D62;">
						<td>{{ $contador }}</td>
			            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
			            <td>{{ date('d/m/Y',strtotime($value->fechaingreso)) }}</td>
			            <td>{{ $value->serie.'-'.$value->numero }}</td>
			            <td>{{ $value->paciente }}</td>
			            <td>{{ $value->empresa }}</td>
			            <td>{{ empty($value->tipo_poliza)===true?'No Especificado':$value->tipo_poliza }}</td>
			            <td>{{ $value->comentario }}</td>
			            <td>{{ $value->cie10 }}</td>
			            <td>{{ $value->uci }}</td>
			            <td>{{ $value->cartagarantia }}</td>
			            <td align="center">{{ number_format($value->total,2,'.','') }}</td>
			            @if($value->situacion=='P' || $value->situacion=='B')
			            <td>PENDIENTE</td>
			            @elseif($value->situacion=='C')
			            <td>COBRADO</td>
			            @elseif($value->situacion=='A')
			            <td>NOTA CREDITO</td>
			            @elseif($value->situacion=='U')
			            <td>ANULADA</td>
			            @endif
		                <td>{{ $value->retramite }} </td>
			  	    	<td>{{ $value->responsable }}</td>
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
			            <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'facturacion/pdfComprobante?id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title'=>'Comprobante')) !!}</td>
			            <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'facturacion/pdfLiquidacion?id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title'=>'Liquidacion')) !!}</td>
			             <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
			            @if($value->situacion=='A')
			            <td align="center">{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Nuevo', array('class' => 'btn btn-success btn-xs', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI','idmov'=>$value->id)).'\', \''.$titulo_registrar.'\', this);')) !!}</td>
			            @else
						<td align="center"> - </td>
			            @endif
			            @if($value->fecha==date("Y-m-d") && ($user->usertype_id==8 || $user->usertype_id==1) && $value->situacion!='U')
			            	<td align="center">{!! Form::button('<div class="glyphicon glyphicon-trash"></div> Anular', array('onclick' => 'modal (\''.URL::route($ruta["anular"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_anular.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
		            	@else
							<td align="center"> - </td>
						@endif
					</tr>
				@else
   					<tr style="background-color:#fff;">
						<td>{{ $contador }}</td>
			            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
			            <td>{{ date('d/m/Y',strtotime($value->fechaingreso)) }}</td>
			            <td>{{ $value->serie.'-'.$value->numero }}</td>
			            <td>{{ $value->paciente }}</td>
			            <td>{{ $value->empresa }}</td>
			            <td>{{ empty($value->tipo_poliza)===true?'No Especificado':$value->tipo_poliza }}</td>
			            <td>{{ $value->comentario }}</td>
			            <td>{{ $value->cie10 }}</td>
			            <td>{{ $value->uci }}</td>
			            <td>{{ $value->cartagarantia }}</td>
			            <td align="center">{{ number_format($value->total,2,'.','') }}</td>
			            @if($value->situacion=='P' || $value->situacion=='B')
			            <td>PENDIENTE</td>
			            @elseif($value->situacion=='C')
			            <td>COBRADO</td>
			            @elseif($value->situacion=='A')
			            <td>NOTA CREDITO</td>
			            @elseif($value->situacion=='U')
			            <td>ANULADA</td>
			            @endif
		                <td>{{ $value->retramite }} </td>
			  			<td>{{ $value->responsable }}</td>
			  			@if($value->situacionbz=='L')
							<td align="center">LEIDO</td>
						@elseif($value->situacionbz=='E')
							<td align="center">ERROR</td>
			            @else
			            	<td align="center">PENDIENTE</td>
			            @endif
			            @if($value->situacionsunat=='L')
			            	<td align="center">PENDIENTE RESPUESTA</td>
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
			            <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'facturacion/pdfComprobante?id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title'=>'Comprobante')) !!}</td>
			            <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'window.open(\'facturacion/pdfLiquidacion?id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info', 'title'=>'Liquidacion')) !!}</td>
			             <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
			            @if($value->situacion=='A')
			            <td align="center">{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Nuevo', array('class' => 'btn btn-success btn-xs', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI','idmov'=>$value->id)).'\', \''.$titulo_registrar.'\', this);')) !!}</td>
			            @else
						<td align="center"> - </td>
			            @endif
			            @if($value->fecha==date("Y-m-d") && ($user->usertype_id==8 || $user->usertype_id==1) && $value->situacion!='U')
			            	<td align="center">{!! Form::button('<div class="glyphicon glyphicon-trash"></div> Anular', array('onclick' => 'modal (\''.URL::route($ruta["anular"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_anular.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
		            	@else
							<td align="center"> - </td>
						@endif
					</tr>
				@endif
			<?php
				$contador = $contador + 1;
			?>
			@endforeach
		</tbody>
	</table>
</div>
<div style="position: absolute; right: 20px; top: 80px; color: red; font-weight: bold;">Total Facturado: {{ $totalfac }} </div>
@endif