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
			<tr id="td{{ $value->id }}">
				<td>{{ $contador }}</td>
	            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
	            <td>{{ date('d/m/Y',strtotime($value->fechaingreso)) }}</td>
	            <td>{{ $value->serie.'-'.$value->numero }}</td>
	            <td>{{ $value->paciente }}</td>
	            <td>{{ $value->empresa }}</td>
	            <td>{{ $value->cie10 }}</td>
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
	  			<td>{{ $value->responsable }}</td>
	  			<td style='font-size:12px' align='center'><input type="checkbox" id="chk{{$value->id}}" onclick="marcar(this.checked,{{ $value->id }})"/></td>
			</tr>
			<?php
			$contador = $contador + 1;
			$dat.=$value->id.",";
			?>
			@endforeach
		</tbody>
	</table>
</div>
<div style="position: absolute; right: 20px; top: 80px; color: red; font-weight: bold;">Total Facturado: {{ $totalfac }} </div>
<script>
validarCheck();
<?php 
echo "cargarTodos('".substr($dat,0,strlen($dat)-1)."');";
?>
</script>
@endif