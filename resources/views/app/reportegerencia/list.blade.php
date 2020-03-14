<?php 
use App\Movimiento;
?>
@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class="table-responsive">
<table id="tablaLista" class="table table-bordered table-striped table-condensed table-hover">
	<thead>
		<tr>
			@foreach($cabecera as $key => $value)
				<th  style='font-size:12px' class="text-center" @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		<?php
		$contador = $inicio + 1;
		?>
		@foreach ($lista as $key => $value)
		<tr>
            <td style='font-size:12px'>{{ $value->tipopaciente }}</td>
            <td style='font-size:12px'>{{ $value->paciente2 }}</td>
            <td style='font-size:12px'>{{ $value->plan2 }}</td>
		<td style='font-size:12px'>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td style='font-size:12px'>{{ $value->medico }}</td>
            <td style='font-size:12px' align="right">{{ number_format($value->cantidad,0,'.','') }}</td>
            @if($value->servicio_id>0)
                <td style='font-size:12px'>{{ $value->servicio }}</td>
            @else
                <td style='font-size:12px'>{{ $value->servicio2 }}</td>
            @endif
            <td style='font-size:12px' align="right">{{ number_format($value->pagodoctor*$value->cantidad,2,'.','') }}</td>
            <td style='font-size:12px' align="right">{{ number_format($value->pagohospital*$value->cantidad,2,'.','') }}</td>
            @if($value->referido_id>0)
            	<td style='font-size:12px'>{{ $value->referido }}</td>
            @else
			<td style='font-size:12px'>NO REFERIDO</td>
            @endif
            @if($value->total>0)
                  @if($value->copago>0)
            	     <td style='font-size:12px' align="center"><a href='javascript:void(0)' onclick="window.open('venta/pdfComprobante?venta_id={{ $value->id }}&guia=SI','_blank')">{{ ($value->tipodocumento_id==4?"F":($value->tipodocumento_id==15?"G":"B")).$value->serie.'-'.$value->numero }}</a></td>
                  @else
                        <td style='font-size:12px' align="center">{{ ($value->tipodocumento_id==4?"F":($value->tipodocumento_id==15?"G":"B")).$value->serie.'-'.$value->numero }}</td>
                  @endif
            @else
            	<td style='font-size:12px' align="center">{{ 'PREF. '.$value->numero2 }}</td>
            @endif
            @if($value->ventafarmacia=='S')
                  <td style='font-size:12px' align="center">{{ $value->estadopago=='P'?($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta.'-'.$value->voucher):'CONTADO'):'-' }}</td>
                  <?php 
                  if($value->situacion=='A'){
                        $nota = Movimiento::where('movimiento_id','=',$value->venta_id)->where('tipodocumento_id','=',13)->first();
                  }
                  ?>
                  <td style='font-size:12px;<?php echo ($value->situacion=='A')?'color:green':((($value->estadopago=='P'?'Pagado':($value->formapago=='C'?'Pagado':'Pendiente')))=='Pendiente'?'color:red':''); ?>' align="center">{{ ($value->estadopago=='P'?'Pagado':($value->formapago=='C'?'Pagado':($value->situacion=='A'?('NC '.$nota->serie.' - '.$nota->numero):'Pendiente'))) }}</td>
            @else
                  <td style='font-size:12px' align="center">{{ $value->situacion=='N'?($value->tarjeta!=''?($value->tarjeta.' / '.$value->tipotarjeta.'-'.$value->voucher):'CONTADO'):'-' }}</td>
                  <?php 
                  if($value->situacion=='A'){
                        $nota = Movimiento::where('movimiento_id','=',$value->venta_id)->where('tipodocumento_id','=',13)->first();
                  }
                  ?>
                  <td style='font-size:12px;<?php echo $value->situacion=='N'?'':($value->situacion=='A'?'color:green':'color:red'); ?>' align="center">{{ ($value->situacion=='N'?'Pagado':($value->situacion=='A'?('NC '.$nota->serie.' - '.$nota->numero):'Pendiente')) }}</td>
            @endif
            <td style='font-size:12px' align="center">{{ $value->responsable }}</td>
            @if($user->usertype_id== 1)
                  @if($value->marcado== 1)
                        <td style='font-size:12px' align="center"><input type="checkbox" checked onclick="desmarcar({{ $value->dmc_id }})"></td>
                  @else
                        <td style='font-size:12px' align="center"><input type="checkbox" onclick="marcar({{ $value->dmc_id }})"></td>
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

<script type="text/javascript">
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