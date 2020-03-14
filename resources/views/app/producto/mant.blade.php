<?php
use App\Productoprincipio;
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($producto, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="col-lg-6 col-md-6 col-sm-6">
		<div class="form-group">
			{!! Form::label('nombre', 'Nombre:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre', 'placeholder' => 'Ingrese nombre comercial')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('codigobarra', 'Cod. Barras:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('codigobarra', null, array('class' => 'form-control input-xs', 'id' => 'codigobarra', 'placeholder' => 'Ingrese codigo de barra')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('preciocompra', 'Precio compra:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('preciocompra', null, array('class' => 'form-control input-xs', 'id' => 'preciocompra', 'placeholder' => 'Ingrese precio de compra')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('precioventa', 'Precio venta:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('precioventa', null, array('class' => 'form-control input-xs', 'id' => 'precioventa', 'placeholder' => 'Ingrese precio de venta')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('preciokayros', 'Precio Kayros:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('preciokayros', null, array('class' => 'form-control input-xs', 'id' => 'preciokayros', 'placeholder' => 'Ingrese precio de venta')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('stockseguridad', 'Stock Segur:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('stockseguridad', null, array('class' => 'form-control input-xs', 'id' => 'stockseguridad', 'placeholder' => 'Ingrese Stock de Seguridad')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('afecto', 'Afecto:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
			<div class="col-lg-3 col-md-3 col-sm-3">
				{!! Form::select('afecto', $cboAfecto, null, array('class' => 'form-control input-xs', 'id' => 'afecto')) !!}
			</div>

			{!! Form::label('codigo_producto', 'Código Producto:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
			<div class="col-lg-3 col-md-3 col-sm-3">
				{!! Form::text('codigo_producto', null, array('class' => 'form-control input-xs', 'id' => 'codigo_producto')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('registro_sanitario', 'Registro Sanitario:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
			<div class="col-lg-3 col-md-3 col-sm-3">
				{!! Form::text('registro_sanitario', null, array('class' => 'form-control input-xs', 'id' => 'registro_sanitario')) !!}
			</div>

			{!! Form::label('precioxcaja', 'Precio por Caja:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
			<div class="col-lg-3 col-md-3 col-sm-3">
				{!! Form::text('precioxcaja', null, array('class' => 'form-control input-xs', 'id' => 'precioxcaja')) !!}
			</div>
		</div>
		
	</div>
	<div class="col-lg-6 col-md-6 col-sm-6">
		<?php 
			$nombrecategoria = null; $nombrelaboratorio = null; $nombrepresentacion = null; $nombreespecialidadfarmacia = null;
			$nombreproveedor = null; $nombreorigen = null; $principio = null;$anaquel = null;$nombreforma = null; $nombreconcentracion = null; $condicionAlmacenamiento = null;

			if ($producto !== null) {
				
				if ($producto->categoria_id !== null) {
					if ($producto->categoria !== null) {
						$nombrecategoria = $producto->categoria->nombre;
					}	
				}
				if ($producto->laboratorio_id !== null) {
					if ($producto->laboratorio !== null) {
						$nombrelaboratorio = $producto->laboratorio->nombre;
					}
				}
				if ($producto->presentacion_id !== null) {
					if ($producto->presentacion !== null) {
						$nombrepresentacion = $producto->presentacion->nombre;
					}
				}
				if ($producto->concentracion !== null) {
					$nombreconcentracion = $producto->concentracion;
				}
				if ($producto->formaFarmac_id !== null) {
					if ($producto->formaFarmaceutica !== null) {
						$nombreforma = $producto->formaFarmaceutica->nombre;
					}
				}	
				if ($producto->condicionAlmac_id !== null) {
					if ($producto->condicionAlmacenamiento !== null) {
						$condicionAlmacenamiento = $producto->condicionAlmacenamiento->nombre;
					}
				}
				if ($producto->especialidadfarmacia_id !== null) {
					if ($producto->especialidadfarmacia !== null) {
						$nombreespecialidadfarmacia = $producto->especialidadfarmacia->nombre;
					}
				}
				if ($producto->origen_id !== null) {
					if ($producto->origen !== null) {
						$nombreorigen = $producto->origen->nombre;
					}	
				}
				if ($producto->proveedor_id !== null) {
					if($producto->proveedor !== null){
						$nombreproveedor = $producto->proveedor->bussinesname;
					}
					
				}
				if($producto->anaquel_id>0){
					$anaquel = $producto->anaquel->descripcion;
				}
				$listado = Productoprincipio::where('producto_id','=',$producto->id)->get();
				$i = 0;
				
				foreach ($listado as $key2 => $value2) {
					if ($i == 0) {
						if ($value2->principioactivo !== null ) {
							$principio = $principio.$value2->principioactivo->nombre;
						}
	                    
	                }else{
	                    if ($value2->principioactivo !== null ) {
							$principio = $principio.'+'.$value2->principioactivo->nombre;
						}
	                }
	                $i++;
				}
			}
			
		?>
		<div class="form-group">
			{!! Form::label('nombrecategoria', 'Clasificacion:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('categoria_id', null, array('id' => 'categoria_id')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('nombrecategoria', $nombrecategoria, array('class' => 'form-control input-xs', 'id' => 'nombrecategoria', 'placeholder' => 'Seleccione clasificacion')) !!}
			</div>
			<div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('categoria.crearsimple', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Clasificacion\', this);', 'title' => 'Nueva Clasificacion')) !!}
    		</div>
		</div>
		<div class="form-group">
			{!! Form::label('nombrelaboratorio', 'Laboratorio:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('laboratorio_id', null, array('id' => 'laboratorio_id')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('nombrelaboratorio', $nombrelaboratorio, array('class' => 'form-control input-xs', 'id' => 'nombrelaboratorio', 'placeholder' => 'Seleccione laboratorio')) !!}
			</div>
			<div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('laboratorio.crearsimple', array('listar'=>'SI','modo'=>'popup')).'\', \'Nuevo Laboratorio\', this);', 'title' => 'Nuevo Laboratorio')) !!}
    		</div>
		</div>
		<div class="form-group">
			{!! Form::label('nombrepresentacion', 'Presentacion:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('presentacion_id', null, array('id' => 'presentacion_id')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('nombrepresentacion', $nombrepresentacion, array('class' => 'form-control input-xs', 'id' => 'nombrepresentacion', 'placeholder' => 'Seleccione presentacion')) !!}
			</div>
			<div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('presentacion.crearsimple', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Presentacion\', this);', 'title' => 'Nueva Presentacion')) !!}
    		</div>
		</div>
		<div class="form-group">
			{!! Form::label('nombreforma', 'Forma Farmacéutica:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('forma_id', null, array('id' => 'forma_id')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('nombreforma', $nombreforma, array('class' => 'form-control input-xs', 'id' => 'nombreforma', 'placeholder' => 'Seleccione Forma Farmacéutica')) !!}
			</div>
			<div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('forma.crearsimple', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Forma Farmacéutica\', this);', 'title' => 'Nueva Forma Farmacéutica')) !!}
    		</div>
		</div>

		<div class="form-group">
			{!! Form::label('nombreconcentracion', 'Concentración:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('concentracion_id', null, array('id' => 'concentracion_id')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('nombreconcentracion', $nombreconcentracion, array('class' => 'form-control input-xs', 'id' => 'nombreconcentracion', 'placeholder' => 'Ingrese Concentración')) !!}
			</div>
			{{-- <div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('concentracion.crearsimple', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Concentración\', this);', 'title' => 'Nueva Concentración')) !!}
    		</div> --}}
		</div>

		<div class="form-group">
			{!! Form::label('condicionAlmacenamiento', 'Condición de Almacenamiento:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('condicionAlmacenamiento_id', null, array('id' => 'condicionAlmacenamiento_id')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('nombrecondicionAlmacenamiento', $condicionAlmacenamiento, array('class' => 'form-control input-xs', 'id' => 'nombrecondicionAlmacenamiento', 'placeholder' => 'Seleccione Condición de Almacenamiento')) !!}
			</div>
			<div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('condicionAlmacenamiento.crearsimple', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Condición de Almacenamiento\', this);', 'title' => 'Nueva Condición de Almacenamiento')) !!}
    		</div>
		</div>


		<div class="form-group">
			{!! Form::label('nombreespecialidadfarmacia', 'Especialidad:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('especialidadfarmacia_id', null, array('id' => 'especialidadfarmacia_id')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('nombreespecialidadfarmacia', $nombreespecialidadfarmacia, array('class' => 'form-control input-xs', 'id' => 'nombreespecialidadfarmacia', 'placeholder' => 'Seleccione especialidad')) !!}
			</div>
			<div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('especialidadfarmacia.crearsimple', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Especialidad\', this);', 'title' => 'Nueva Especialidad')) !!}
    		</div>
		</div>
		<div class="form-group">
			{!! Form::label('nombreproveedor', 'Proveedor:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('proveedor_id', null, array('id' => 'proveedor_id')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('nombreproveedor', $nombreproveedor, array('class' => 'form-control input-xs', 'id' => 'nombreproveedor', 'placeholder' => 'Seleccione proveedor')) !!}
			</div>
			<div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('proveedor.crearsimple', array('listar'=>'SI','modo'=>'popup')).'\', \'Nuevo Proveedor\', this);', 'title' => 'Nuevo Proveedor')) !!}
    		</div>
		</div>
		<div class="form-group">
			{!! Form::label('nombreorigen', 'Origen:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('origen_id', null, array('id' => 'origen_id')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('nombreorigen', $nombreorigen, array('class' => 'form-control input-xs', 'id' => 'nombreorigen', 'placeholder' => 'Seleccione origen')) !!}
			</div>
			<div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('origen.crearsimple', array('listar'=>'SI','modo'=>'popup')).'\', \'Nuevo Origen\', this);', 'title' => 'Nuevo Origen')) !!}
    		</div>
		</div>
		<div class="form-group">
			{!! Form::label('principioactivo', 'Principio Activo:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('principioactivo', $principio, array('class' => 'form-control input-xs', 'id' => 'principioactivo', 'placeholder' => 'Ingrese Principio Activo', 'readonly' => '')) !!}
			</div>
			<div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('principioactivo.indexsimple', array('listar'=>'SI','modo'=>'popup')).'\', \'\', this);', 'title' => 'Agregar Principio Activo')) !!}
    		</div>
		</div>
		<div class="form-group">
			{!! Form::label('anaquel', 'Anaquel:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('anaquel_id', null, array('id' => 'anaquel_id')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('anaquel', $anaquel, array('class' => 'form-control input-xs', 'id' => 'anaquel', 'placeholder' => 'Ingrese Anaquel')) !!}
			</div>
			<div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('anaquel.crearsimple', array('listar'=>'SI','modo'=>'popup')).'\', \'\', this);', 'title' => 'Agregar Anaquel')) !!}
    		</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('1000');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
	$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="nombre"]').focus();
	$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="preciocompra"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
	$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="precioventa"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
	$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="preciokayros"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
	$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="stockseguridad"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });

	$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="codigobarra"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="preciocompra"]').focus();
			}
		});

	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="nombre"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="codigobarra"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="preciocompra"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="preciokayros"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="precioventa"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="stockseguridad"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="afecto"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});

	var personas = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'person/providersautocompleting/%QUERY',
				filter: function (personas) {
					return $.map(personas, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		personas.initialize();
		$('#nombreproveedor').typeahead(null,{
			displayKey: 'value',
			source: personas.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#proveedor_id').val(datum.id);
			$('#nombreorigen').focus();
		});


		var origenes = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'origen/autocompletarorigen/%QUERY',
				filter: function (origenes) {
					return $.map(origenes, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});

		origenes.initialize();
		$('#nombreorigen').typeahead(null,{
			displayKey: 'value',
			source: origenes.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#origen_id').val(datum.id);
			var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
			inputs.eq( inputs.index(this)+ 1 ).focus();
		});

		var anaqueles = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'anaquel/autocompletaranaquel/%QUERY',
				filter: function (anaqueles) {
					return $.map(anaqueles, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		anaqueles.initialize();
		$('#anaquel').typeahead(null,{
			displayKey: 'value',
			source: anaqueles.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#anaquel_id').val(datum.id);
			var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
			inputs.eq( inputs.index(this)+ 1 ).focus();
		});


		var especialidades = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'especialidadfarmacia/autocompletarespecialidadfarmacia/%QUERY',
				filter: function (especialidades) {
					return $.map(especialidades, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		especialidades.initialize();
		$('#nombreespecialidadfarmacia').typeahead(null,{
			displayKey: 'value',
			source: especialidades.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#especialidadfarmacia_id').val(datum.id);
			$('#nombreproveedor').focus();
		});



		var presentaciones = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'presentacion/autocompletarpresentacion/%QUERY',
				filter: function (presentaciones) {
					return $.map(presentaciones, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		presentaciones.initialize();
		$('#nombrepresentacion').typeahead(null,{
			displayKey: 'value',
			source: presentaciones.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#presentacion_id').val(datum.id);
			$('#nombreforma').focus();
		});


		var laboratorios = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'laboratorio/autocompletarlaboratorio/%QUERY',
				filter: function (laboratorios) {
					return $.map(laboratorios, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		laboratorios.initialize();
		$('#nombrelaboratorio').typeahead(null,{
			displayKey: 'value',
			source: laboratorios.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#laboratorio_id').val(datum.id);
			$('#nombrepresentacion').focus();
		});


		//--

		var categorias = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'categoria/autocompletarcategoria/%QUERY',
				filter: function (categorias) {
					return $.map(categorias, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		categorias.initialize();
		$('#nombrecategoria').typeahead(null,{
			displayKey: 'value',
			source: categorias.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#categoria_id').val(datum.id);
			$('#nombrelaboratorio').focus();
		});

		// --

		var formas = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'forma/autocompletarforma/%QUERY',
				filter: function (formas) {
					return $.map(formas, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		formas.initialize();
		$('#nombreforma').typeahead(null,{
			displayKey: 'value',
			source: formas.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#forma_id').val(datum.id);
			$('#nombreconcentracion').focus();
		});


		var concentracion = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'concentracion/autocompletarconcentracion/%QUERY',
				filter: function (concentracion) {
					return $.map(concentracion, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});

		concentracion.initialize();
		$('#nombreconcentracion').typeahead(null,{
			displayKey: 'value',
			source: concentracion.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#concentracion_id').val(datum.id);
			$('#nombrecondicionAlmacenamiento').focus();
		});



		var condicionAlmacenamiento = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'condicionAlmacenamiento/autocompletarcondicion/%QUERY',
				filter: function (condicionAlmacenamiento) {
					return $.map(condicionAlmacenamiento, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		condicionAlmacenamiento.initialize();
		$('#nombrecondicionAlmacenamiento').typeahead(null,{
			displayKey: 'value',
			source: condicionAlmacenamiento.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#condicionAlmacenamiento_id').val(datum.id);
			$('#nombreespecialidadfarmacia').focus();
		});

}); 

function guardarProveedor (entidad, idboton) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
	var btn = $(idboton);
	btn.button('loading');
	data.done(function(msg) {
		respuesta = msg;
	}).fail(function(xhr, textStatus, errorThrown) {
		respuesta = 'ERROR';
	}).always(function() {
		btn.button('reset');
		if(respuesta === 'ERROR'){
		}else{
		  //alert(respuesta);
            var dat = JSON.parse(respuesta);
            resp=dat[0].respuesta;
			if (resp === 'OK') {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="proveedor_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombreproveedor"]').val(dat[0].nombre);
                /*$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(dat[0].paciente);*/
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

function guardarOrigen (entidad, idboton) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
	var btn = $(idboton);
	btn.button('loading');
	data.done(function(msg) {
		respuesta = msg;
	}).fail(function(xhr, textStatus, errorThrown) {
		respuesta = 'ERROR';
	}).always(function() {
		btn.button('reset');
		if(respuesta === 'ERROR'){
		}else{
		  //alert(respuesta);
            var dat = JSON.parse(respuesta);
            resp=dat[0].respuesta;
			if (resp === 'OK') {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="origen_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombreorigen"]').val(dat[0].nombre);
                /*$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(dat[0].paciente);*/
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

function guardarEspecialidadfarmacia (entidad, idboton) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
	var btn = $(idboton);
	btn.button('loading');
	data.done(function(msg) {
		respuesta = msg;
	}).fail(function(xhr, textStatus, errorThrown) {
		respuesta = 'ERROR';
	}).always(function() {
		btn.button('reset');
		if(respuesta === 'ERROR'){
		}else{
		  //alert(respuesta);
            var dat = JSON.parse(respuesta);
            resp=dat[0].respuesta;
			if (resp === 'OK') {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="especialidadfarmacia_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombreespecialidadfarmacia"]').val(dat[0].nombre);
                /*$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(dat[0].paciente);*/
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

function guardarPresentacion (entidad, idboton) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
	var btn = $(idboton);
	btn.button('loading');
	data.done(function(msg) {
		respuesta = msg;
	}).fail(function(xhr, textStatus, errorThrown) {
		respuesta = 'ERROR';
	}).always(function() {
		btn.button('reset');
		if(respuesta === 'ERROR'){
		}else{
		  //alert(respuesta);
            var dat = JSON.parse(respuesta);
            resp=dat[0].respuesta;
			if (resp === 'OK') {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="presentacion_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombrepresentacion"]').val(dat[0].nombre);
                /*$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(dat[0].paciente);*/
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

function guardarLaboratorio (entidad, idboton) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
	var btn = $(idboton);
	btn.button('loading');
	data.done(function(msg) {
		respuesta = msg;
	}).fail(function(xhr, textStatus, errorThrown) {
		respuesta = 'ERROR';
	}).always(function() {
		btn.button('reset');
		if(respuesta === 'ERROR'){
		}else{
		  //alert(respuesta);
            var dat = JSON.parse(respuesta);
            resp=dat[0].respuesta;
			if (resp === 'OK') {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="laboratorio_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombrelaboratorio"]').val(dat[0].nombre);
                /*$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(dat[0].paciente);*/
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

function guardarCategoria (entidad, idboton) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
	var btn = $(idboton);
	btn.button('loading');
	data.done(function(msg) {
		respuesta = msg;
	}).fail(function(xhr, textStatus, errorThrown) {
		respuesta = 'ERROR';
	}).always(function() {
		btn.button('reset');
		if(respuesta === 'ERROR'){
		}else{
		  //alert(respuesta);
            var dat = JSON.parse(respuesta);
            resp=dat[0].respuesta;
			if (resp === 'OK') {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="categoria_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombrecategoria"]').val(dat[0].nombre);
                /*$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(dat[0].paciente);*/
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}


function guardarForma (entidad, idboton) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
	var btn = $(idboton);
	btn.button('loading');
	data.done(function(msg) {
		respuesta = msg;
	}).fail(function(xhr, textStatus, errorThrown) {
		respuesta = 'ERROR';
	}).always(function() {
		btn.button('reset');
		if(respuesta === 'ERROR'){
		}else{
		  //alert(respuesta);
            var dat = JSON.parse(respuesta);
            resp=dat[0].respuesta;
			if (resp === 'OK') {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="forma_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombreforma"]').val(dat[0].nombre);
                /*$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(dat[0].paciente);*/
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

function guardarConcentracion (entidad, idboton) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
	var btn = $(idboton);
	btn.button('loading');
	data.done(function(msg) {
		respuesta = msg;
	}).fail(function(xhr, textStatus, errorThrown) {
		respuesta = 'ERROR';
	}).always(function() {
		btn.button('reset');
		if(respuesta === 'ERROR'){
		}else{
		  //alert(respuesta);
            var dat = JSON.parse(respuesta);
            resp=dat[0].respuesta;
			if (resp === 'OK') {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="concentracion_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombreconcentracion"]').val(dat[0].nombre);
                /*$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(dat[0].paciente);*/
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}


function guardarCondicion (entidad, idboton) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
	var btn = $(idboton);
	btn.button('loading');
	data.done(function(msg) {
		respuesta = msg;
	}).fail(function(xhr, textStatus, errorThrown) {
		respuesta = 'ERROR';
	}).always(function() {
		btn.button('reset');
		if(respuesta === 'ERROR'){
		}else{
		  //alert(respuesta);
            var dat = JSON.parse(respuesta);
            resp=dat[0].respuesta;
			if (resp === 'OK') {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="condicionAlmacenamiento_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombrecondicionAlmacenamiento"]').val(dat[0].nombre);
                /*$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(dat[0].paciente);*/
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}


function agregarprincipio(elemento) {
	var principioactivo_id = $('#txtPrincipio' + elemento).val();
	var _token =$('input[name=_token]').val();
	$.post('{{ URL::route("principioactivo.agregarprincipio")}}', {principioactivo_id: principioactivo_id,_token: _token} , function(data){
			$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="principioactivo"]').val(data);
			bootbox.alert("Principio Agregado");
            setTimeout(function () {
                $('#nombre' + elemento).focus();
            },2000) 
			//var totalpedido = $('#totalpedido').val();
			//$('#total').val(totalpedido);
		});
}

function quitar (valor) {
	var _token =$('input[name=_token]').val();
	$.post('{{ URL::route("principioactivo.quitarprincipio")}}', {valor: valor,_token: _token} , function(data){
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="principioactivo"]').val(data);
		//calculatetotal();
		//generarSaldototal ();
		// var totalpedido = $('#totalpedido').val();
		// $('#total').val(totalpedido);
	});
}
</script>