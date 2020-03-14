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
            <?php
                  if($value->tipodocumento_id=="20"){
                        $color2='color:green;font-weight: bold;';
                  }else{
                        $color2='color:red;font-weight: bold;';
                  }
            ?>      
		<tr>
		<td>{{ $contador }}</td>
            <td>{{ date('d/m/Y',strtotime($value->fecha)) }}</td>
            <td align="right">{{ $value->dni }}</td>
            <td>{{ $value->persona->bussinesname.' '.$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres }}</td>
            <td align="left">{{ $value->conceptopago->nombre }}</td>
            <td align="left">{{ $value->numeroficha }}</td>
            @if($value->tipodocumento_id=="20")
              <td align="center" style='{{ $color2 }}'>{{ number_format($value->total,2,'.','') }}</td>
              <td align="center">0.00</td>
            @else
              <td align="center">0.00</td>
              <td align="center" style='{{ $color2 }}'>{{ number_format($value->total,2,'.','') }}</td>
            @endif
            <td align="left">{{ $value->comentario }}</td>
            <td align="left">{{ $value->formapago.' '.$value->voucher }}</td>
            <td align="left">{{ $value->situacion=='P'?'Pendiente':'Cobrado' }}</td>
            @if($value->situacion=='P')
              <td align="center">{!! Form::button('<div class="glyphicon glyphicon-check"></div>', array('onclick' => 'modal (\''.URL::route($ruta["cobrar"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_cobrar.'\', this);', 'class' => 'btn btn-xs btn-success', 'title' => 'Cobrar')) !!}</td>
            @else
              <td align='center'> - </td>
            @endif
            <td align="center">{!! Form::button('<div class="glyphicon glyphicon-print"></div>', array('onclick' => 'imprimirRecibo ('.$value->id.');', 'class' => 'btn btn-xs btn-info', 'title' => 'Imprimir')) !!}</td>
            @if($value->situacion=='P')
              <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
              <td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
            @else
              <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
              <td align='center'> - </td>
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