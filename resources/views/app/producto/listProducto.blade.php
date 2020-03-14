<?php
use App\Productoprincipio;
use App\Principioactivo;
?>
@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class="card-box table-responsive">
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
			<?php
				$ind = 0;$principio = '';
				if ($principioactivo !== null && $principioactivo !== '') {
				 	$listado = Productoprincipio::where('producto_id','=',$value->id)->get();
					$i = 0;
					$principio = '';
					foreach ($listado as $key2 => $value2) {
						if ($i == 0) {
	                       if ($value2->principioactivo !== null) {
								$principio = $principio.$value2->principioactivo->nombre;
							}
	                    }else{
	                        if ($value2->principioactivo !== null) {
	                    		$principio = $principio.'+'.$value2->principioactivo->nombre;
	                    	}
	                    }
	                    
	                    if ($value2->principioactivo !== null) {
	                    	$like = array();
	                    	$like = Principioactivo::where('nombre','LIKE', '%'.strtoupper($principioactivo).'%')->where('id','=',$value2->principioactivo->id)->get();
		                    if (count($like) > 0) {
		                    	$ind = 1;
		                    }
	                    }
	                    
	                    $i++;
					}
				}else{
					$listado = Productoprincipio::where('producto_id','=',$value->id)->get();
					$i = 0;
					$principio = '';
					foreach ($listado as $key2 => $value2) {
						if ($i == 0) {
							if ($value2->principioactivo !== null) {
								$principio = $principio.$value2->principioactivo->nombre;
							}
	                        
	                    }else{
	                    	if ($value2->principioactivo !== null) {
	                    		$principio = $principio.'+'.$value2->principioactivo->nombre;
	                    	}
	                        
	                    }
	                    $i++;
					}
					$ind =1;
				} 

				$laboratorio = '-'; $categoria = '-'; $presentacion = '-'; $especialidadfarmacia = '-'; $proveedor = '-'; $origen = '-';
				if ($value->categoria_id !== null) {
					if ($value->categoria !== null) {
						$categoria = $value->categoria->nombre;
					}				
				}
				if ($value->laboratorio_id !== null) {
					if ($value->laboratorio !== null) {
						$laboratorio = $value->laboratorio->nombre;
					}	
				}
				if ($value->presentacion_id !== null) {
					if ($value->presentacion !== null) {
						$presentacion = $value->presentacion->nombre;
					}	
				}
				if ($value->especialidadfarmacia_id !== null) {
					if ($value->especialidadfarmacia !== null) {
						$especialidadfarmacia = $value->especialidadfarmacia->nombre;
					}
				}
				if ($value->origen_id !== null) {

					if ( $value->origen !== null ) {
						$origen = $value->origen->nombre;
					}
				}
				if ($value->proveedor_id !== null) {
					if ($value->proveedor !== null) {
						$proveedor = $value->proveedor->bussinesname;
					}
				
				}
			?>
		@if($ind == 1)	
		<tr>
			<td>{{ $contador }}</td>
			<td>{{ $value->nombre }}</td>
			<td>{{ $principio }}</td>
			<td>{{ $categoria }}</td>
			<td>{{ $laboratorio }}</td>
			<td>{{ $presentacion }}</td>
			<td>{{ $especialidadfarmacia }}</td>
			<td>{{ $proveedor }}</td>
			<td>{{ $origen }}</td>
			<td>{{ $value->precioventa }}</td>
			<td>{{ $value->afecto }}</td>
		</tr>
		@endif
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