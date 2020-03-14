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
		<tr id='td{{ $value->iddetalle }}'>
            <?php
            if($value->descargado!="E"){
            ?>      
                <td><input type="checkbox" id="chk{{$value->iddetalle}}" onclick="agregarDetalle(this.checked,{{ $value->iddetalle }})"/></td>
            <?php
            }else{
            ?>
                <td title='Fecha Pago:{{ date('d/m/Y',strtotime($value->fechadescargo)) }} , Factura:{{ $value->recibo }}' style='color:red'> P </td>
            <?php
            }
            ?>
            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td>{{ $value->paciente2 }}</td>
            @if($value->total>0)
                <td align="center">{{ ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero }}</td>
                <td align="right">{{ number_format($value->pagodoctor*$value->cantidad,2,'.','') }}</td>
                <?php //number_format($value->pagohospital*$value->cantidad,2,'.','') ?> 
                <td align="right">{{ number_format($value->precioconvenio*$value->cantidad,2,'.','') }}</td>
            @else
                <td align="center">{{ 'PREF. '.$value->numero2 }}</td>
                <td align="right">{{ '0.00' }}</td>
                <td align="right">{{ number_format($value->precioconvenio*$value->cantidad,2,'.','') }}</td>
            @endif
            <td>{{ $value->plan }}</td>
            <td>{{ empty($value->tipo_poliza) === true?'No Especificado':$value->tipo_poliza }}</td>
            <td>{{ $value->medico }}</td>
            <td>{{ round($value->cantidad,0) }}</td>
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
            @if($value->total>0)
                <td align="center">{{ $value->situacion=='P'?'-':($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta):'CONTADO') }}</td>
                <td align="center">{{ ($value->situacion=='P'?'Pendiente':'Pagado') }}</td>
            @else
                <td align="center">{{ 'CREDITO' }}</td>
                <td align="center">{{ 'Pendiente' }}</td>
            @endif
            <td align="center">{{ $value->responsable }}</td>
            <td>{{ $value->historia }}</td>
            @if($user->usertype_id== 1)
                  @if($value->marcado== 1)
                        <td style='font-size:12px' align="center"><input type="checkbox" checked onclick="desmarcar({{ $value->iddetalle }})"></td>
                  @else
                        <td style='font-size:12px' align="center"><input type="checkbox" onclick="marcar({{ $value->iddetalle }})"></td>
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
function marcar(id){
    $.ajax({
          type:'GET',
          url:"reporteconsulta/marca",
          data:{'id':id},
          success: function(a) {
                console.log('Listo');
          }
    });
}

function desmarcar(id){
    $.ajax({
          type:'GET',
          url:"reporteconsulta/desmarca",
          data:{'id':id},
          success: function(a) {
                console.log('Listo');
          }
    });
}
</script>