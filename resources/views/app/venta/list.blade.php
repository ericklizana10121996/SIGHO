@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class="table-responsive">
<table id="example1" class="table table-bordered table-striped table-condensed table-hover">
	<thead>
		<tr>
			@foreach($cabecera as $key => $value)
				<th @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
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
			<?php
				$estadopago = '';
				/*if ($value->estadopago == 'PP') {
					$estadopago = 'Pendiente';
				}elseif ($value->estadopago == 'P') {
					$estadopago = 'Pagado';
				}*/
				if ($value->formapago == 'P') {
					$estadopago = 'Pendiente';
				}else {
					$estadopago = 'Pagado';
				}

				if($value->tipodocumento_id=="4"){
                    $abreviatura="F";
                }elseif($value->tipodocumento_id=="5"){
                    $abreviatura="B";    
                }else{
                	$abreviatura="G"; 
                }
                $nombrepaciente = '';
                if ($value->persona_id !== NULL) {
                	//echo 'entro'.$value->id;break;
                    $nombrepaciente = trim($value->person->bussinesname." ".$value->person->apellidopaterno." ".$value->person->apellidomaterno." ".$value->person->nombres);

                }else{
                	$nombrepaciente = trim($value->nombrepaciente);
                }

                

                /*if ($value->tipodocumento_id == 5) {   
                    
                }else{
                    $nombrepaciente = trim($value->empresa->nombre);
                }*/

			?>
			<td>{{ $contador }}</td>
			<td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
			<td class="marcador" {{ 'id='.$contador }}>{{ utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)) }}</td>
			<td>{{ $nombrepaciente }}</td>
			<td>{{ $estadopago }}</td>
			<td>{{ $value->total }}</td>
			
			<td>{!! Form::button('<div class="glyphicon glyphicon-eye-open"></div> Ver', array('onclick' => 'modal (\''.URL::route($ruta["show"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_ver.'\', this);', 'class' => 'btn btn-xs btn-info')) !!}</td>
			@if($value->situacion == 'N')
				@if($value->conveniofarmacia_id !== null)
					@if($value->copago == 0)
						<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Guia', array('onclick' => 'window.open(\'venta/pdfComprobante?venta_id='.$value->id.'&guia=SI\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
						@if($value->descuentokayros==0)
							<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Ticket', array('onclick' => 'window.open(\'venta/pdfComprobante2?venta_id='.$value->id.'&guia=NO\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
						@endif
					@else
						<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Comprobante', array('onclick' => 'window.open(\'venta/pdfComprobante?venta_id='.$value->id.'&guia=NO\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
						<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Ticket', array('onclick' => 'window.open(\'venta/pdfComprobante2?venta_id='.$value->id.'&guia=NO\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
						<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Guia', array('onclick' => 'window.open(\'venta/pdfComprobante?venta_id='.$value->id.'&guia=SI\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
					@endif
				@else
					<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Comprobante', array('onclick' => 'window.open(\'venta/pdfComprobante?venta_id='.$value->id.'&guia=NO\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
					<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Ticket', array('onclick' => 'window.open(\'venta/pdfComprobante2?venta_id='.$value->id.'&guia=NO\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
				@endif
			

			<!--<td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td> -->
				@if($user->usertype_id== 11 || $user->usertype_id== 1)
					@if($value->tipodocumento_id != 15)
						<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Nota Credito', array('onclick' => 'modal (\''.URL::route($ruta["notacredito"], array($value->id, 'SI')).'\', \'Nota de Credito\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
					@endif
					@if($value->formapago == 'P')
						<td>{!! Form::button('<div class="glyphicon glyphicon-usd"></div> Pagar', array('onclick' => 'modal (\''.URL::route('venta.pagar', array($value->id, 'SI')).'\', \'Pagar Comprobante\', this);', 'class' => 'btn btn-xs btn-success')) !!}</td>
					@endif
					@if($user->usertype_id==11 && date("d/m/Y",strtotime($value->fecha))==date("d/m/Y"))
						<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Anular', array('onclick' => 'modal (\''.URL::route('venta.anulacion', array($value->id, 'SI')).'\', \'Anular Comprobante\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
					@else
						@if($user->usertype_id==1 || $user->usertype_id==8 || $value->tipodocumento_id == 15)
							<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Anular', array('onclick' => 'modal (\''.URL::route('venta.anulacion', array($value->id, 'SI')).'\', \'Anular Comprobante\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
						@else
							<td align="center"> - </td>       
						@endif
					@endif
				@endif
			@endif
			
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
	<tfoot>
		<tr>
			@foreach($cabecera as $key => $value)
				<th @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</tfoot>
</table>
</div>
{!! $paginacion or '' !!}
@endif

<style type="text/css">
	.visitado{
		background: #00c0ef;
	}
</style>