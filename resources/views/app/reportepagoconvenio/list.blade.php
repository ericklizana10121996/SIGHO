<?php
use App\Movimiento;
?>
@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class="table-responsive">
<table id="example1" class="table table-bordered table-striped table-condensed table-hover">
	<thead>
		<tr>
			@foreach($cabecera as $key => $value)
				<th style='font-size:12px' class="text-center" @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		<?php
		$contador = $inicio + 1;
		?>
		@foreach ($lista as $key => $value)
		<tr>
            <td style='font-size:12px'>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td style='font-size:12px' title='{{ $value->paciente2 }}'>{{ substr($value->paciente2,0,15).'...' }}</td>
            @if($value->total>0)
                  <td style='font-size:12px' align="center">{{ ($value->tipodocumento_id==4?"F":"B").$value->serie.'-'.$value->numero }}</td>
            @else
                  <td style='font-size:12px' align="center">{{ 'PREF. '.$value->numero2 }}</td>
            @endif
            @if($value->pagodoctor>0)
                  <td style='font-size:12px' align="right">{{ number_format(($value->pagodoctor)*$value->cantidad,2,'.','') }}</td>
            @else
                  <?php
                  $mov = Movimiento::where('listapago','like','%'.$value->iddetalle.'%')->where('situacion','<>','A')->first();
                  $d=explode(",",$mov->listapago);
                  ?>
                  <td style='font-size:12px' align="right">{{ number_format($mov->total/count($d),2,'.','') }}</td>
            @endif
            <td style='font-size:12px' title='{{ $value->plan }}'>{{ substr($value->plan,0,16).'...' }}</td>
            <td style='font-size:12px' title='{{ $value->medico }}'>{{ substr($value->medico,0,15).'...' }}</td>
            @if($value->servicio_id>0)
                <td style='font-size:12px' title='{{ $value->servicio }}'>{{ substr($value->servicio,0,15).'...' }}</td>
            @else
                <td style='font-size:12px' title='{{ $value->servicio2 }}'>{{ substr($value->servicio2,0,15).'...' }}</td>
            @endif
            <td style='font-size:12px' align="center">{{ $value->responsable }}</td>
            <td style='font-size:12px' align="center">{{ date("d/m/Y",strtotime($value->fechaentrega)) }}</td>
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
</div>
@endif