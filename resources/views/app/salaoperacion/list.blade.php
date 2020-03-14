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
        $color="";
        $titulo="";
        if($value->situacion=='P'){//PENDIENTE
            $color='background:rgba(255,235,59,0.76)';
            $titulo="Pendiente";
        }elseif($value->situacion=='C'){
            $color='background:rgba(10,215,37,0.50)';
            $titulo="Aceptado";
        }elseif($value->situacion=='A'){
            $color='background:rgba(215,57,37,0.50)';
            $titulo='Anulado'; 
        }
        ?> 
		<tr style="{{ $color }}" title="{{ $titulo }}">
			<td>{{ $contador }}</td>
            <td>{!! Form::button('<div class="glyphicon glyphicon-list"></div> ', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \'Detalle\', this);', 'class' => 'btn btn-xs btn-info', 'titulo' => 'Detalle')) !!}</td>
            <td>{{ $value->fecha }}</td>
            <td>{{ $value->doctor }}</td>
            <td>{{ $value->especialidad }}</td>
            <td>{{ $value->tipopaciente }}</td>
            <td>{{ ($value->historia_id?$value->paciente2:$value->paciente) }}</td>
            <td>{{ ($value->historia_id?$value->historia->numero: "-") }}</td>
            <td>{{ $value->sala }}</td>
            <td>{{ $value->horainicio }}</td>
            <td>{{ $value->horafin }}</td>
            <td>{{ $value->operacion }}</td>
            <td>{{ $value->responsable }}</td>
            <td>{{ $value->usuario3}}</td>
            @if($value->usuario2_id>0)
                <td>{{ $value->usuario2->nombres}}</td>
            @else
                <td> - </td>
            @endif
            @if($value->situacion=='P')
                <td align="center">{!! Form::button('<div class="glyphicon glyphicon-check"></div> Confirmar', array('onclick' => 'modal (\''.URL::route($ruta["acept"], array($value->id, 'SI')).'\', \'Aceptar\', this);', 'class' => 'btn btn-xs btn-success')) !!}</td>
            @else
                <td> - </td>
            @endif
            @if($user->usertype_id==7 || $user->usertype_id==1)
                @if($value->situacion=='P')
                    <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> ', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning', 'titulo' => 'Editar')) !!}</td>
                    <td align="center">{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Suspender', array('onclick' => 'modal (\''.URL::route($ruta["reject"], array($value->id, 'SI')).'\', \'Rechazar\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
                    <td style="display: none">{!! Form::button('<div class="glyphicon glyphicon-remove"></div> ', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger', 'title' => 'Eliminar')) !!}</td>
                @else
                    <td> - </td>
                    <td> - </td>
                @endif
            @else
                <td> - </td>
                <td> - </td>
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