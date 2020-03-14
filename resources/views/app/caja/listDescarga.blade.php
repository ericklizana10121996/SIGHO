@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
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
		<tr>
			<?php
				$estadopago = '';
				$check = false;
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
                }else{
                    $abreviatura="B";    
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

                for ($i=0; $i < count($list) ; $i++) { 
                	if ($list[$i]['venta_id'] == $value->id ) {
                		$check = true;
                	}
                }

			?>
			<td>{{ $contador }}</td>
			<td>{{ $value->fecha }}</td>
			<td>{{ utf8_encode($abreviatura.str_pad($value->serie,3,'0',STR_PAD_LEFT).'-'.str_pad($value->numero,8,'0',STR_PAD_LEFT)) }}</td>
			<td>{{ $nombrepaciente }}</td>
			<td>{{ $estadopago }}</td>
			<td>{{ $value->total }}</td>
			<td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div> Comprobante', array('onclick' => 'window.open(\'venta/pdfComprobante?venta_id='.$value->id.'\',\'_blank\')', 'class' => 'btn btn-xs btn-info')) !!}</td>
			<td>{!! Form::button('<div class="glyphicon glyphicon-eye-open"></div> Ver', array('onclick' => 'modal (\''.URL::route('venta.show', array($value->id, 'listar'=>'SI')).'\', \''.$titulo_ver.'\', this);', 'class' => 'btn btn-xs btn-info')) !!}</td>
			<!--<td>{!! Form::checkbox('pay'.$value->id, $value->id,$check, array('id' => 'pay'.$value->id,'class' => 'pull-right', 'onchange' => 'changestate(this);')) !!}</td>-->
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
{!! $paginacion or '' !!}
@endif