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
		<?php
			if($value->fallecido=="S"){
				$color="background-color: rgba(29, 119, 162, 0.52);";
				$title="Fallecido el ".$value->fechafallecido;
			}else{
				$color="";
				$title="";
			}
		?>
		<tr style="{{ $color }}" title="{{ $title }}">
			<td>{{ $contador }}</td>
			<td>{{ $value->numero }}</td>
            <td>{{ $value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres }}</td>
            <td>{{ $value->persona->dni }}</td>
            <td align="center">{{ $value->tipopaciente }}</td>
            <td align="center">@if($value->tipopaciente == 'Convenio'){{ $value->convenio }}@else - @endif</td>
            <td>{{ $value->persona->telefono }}</td>
            <?/*td align="center">{{ $value->persona->fechanacimiento }}</td*/ ?>
            <td>{{ $value->persona->direccion }}</td>
            @if($value->fallecido=="S")
				<td> - </td>
				<td>{!! Form::button('<i class="glyphicon glyphicon-search"></i>', array('class' => 'btn btn-success btn-xs', 'id' => 'btnSeguimiento', 'title' => 'Seguimiento', 'onclick' => 'seguimiento(\''.$entidad.'\','.$value->id.')')) !!}</td>
	            <td>{!! Form::button('<i class="glyphicon glyphicon-print"></i>', array('class' => 'btn btn-info btn-xs', 'id' => 'btnImprimir', 'title' => 'Imprimir', 'onclick' => 'imprimirHistoria(\''.$entidad.'\','.$value->id.')')) !!}</td>
				<td> - </td>
				<td> - </td>
            @else
            	<td>{!! Form::button('<i class="glyphicon glyphicon-screenshot"></i>', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnFallecido', 'title' => 'Fallecido', 'onclick' => 'modal (\''.URL::route($ruta["fallecido"], array($value->id, 'SI')).'\', \'Fallecido\', this);')) !!}</td>
            	<td>{!! Form::button('<i class="glyphicon glyphicon-search"></i>', array('class' => 'btn btn-success btn-xs', 'id' => 'btnSeguimiento', 'title' => 'Seguimiento', 'onclick' => 'seguimiento(\''.$entidad.'\','.$value->id.')')) !!}</td>
	            <td>{!! Form::button('<i class="glyphicon glyphicon-print"></i>', array('class' => 'btn btn-info btn-xs', 'id' => 'btnImprimir', 'title' => 'Imprimir', 'onclick' => 'imprimirHistoria(\''.$entidad.'\','.$value->id.')')) !!}</td>
				<td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div>', array( 'title' => 'Editar', 'onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
				@if($user->usertype_id==1)
					<td>{!! Form::button('<div class="glyphicon glyphicon-trash"></div>', array( 'title' => 'Eliminar', 'onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
				@else
					<td> - </td>
				@endif
			@endif
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
@endif