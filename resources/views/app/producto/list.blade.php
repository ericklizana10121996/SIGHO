<?php
use App\Productoprincipio;
use App\Principioactivo;
use App\Origen;
use App\Anaquel;
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
					//echo json_encode($listado);
					//exit();
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
				$l2 = Origen::orderBy('nombre','asc')->get();
				$selec="<select id='cboOrigen".($value->id)."' onchange='cambiarOrigen(this.value,".$value->id.");'><option value='0'></option>";
				foreach ($l2 as $k=>$v){
					if($value->origen_id !== null&& $v->id==$value->origen_id){
						$sel="selected=''";
					}else{
						$sel="";
					}
					$selec.="<option value='".$v->id."' $sel>".$v->nombre."</option>";
				}
				$selec.="</select>";
				$l2 = Anaquel::orderBy('descripcion','asc')->get();
				$selec2="<select id='cboAnaquel".($value->id)."' onchange='cambiarAnaquel(this.value,".$value->id.");'><option value='0'></option>";
				foreach ($l2 as $k=>$v){
					if($value->anaquel_id !== null&& $v->id==$value->anaquel_id){
						$sel="selected=''";
					}else{
						$sel="";
					}
					$selec2.="<option value='".$v->id."' $sel>".$v->descripcion."</option>";
				}
				$selec2.="</select>";
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
			<td><?php echo $selec; ?></td>
			<td><?php echo $selec2; ?></td>
			<td>{{ $value->precioventa }}</td>
			<td>{{ $value->preciocompra }}</td>
			<td>{{ $value->preciokayros }}</td>
			<td>{{ $value->afecto }}</td>
			<td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
			<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
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