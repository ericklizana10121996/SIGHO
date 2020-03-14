
<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $title }}
		{{-- <small>Descripción</small> --}}
	</h1>
	{{--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Tables</a></li>
		<li class="active">Data tables</li>
	</ol>
	--}}
</section>

<!-- Main content -->
<section class="content">
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header">
					<div class="row">
						<div class="col-xs-12">
							{!! Form::open(['route' => 'producto.buscarproducto', 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
							<div class="form-group">
								{!! Form::label('nombre', 'Nombre:') !!}
								{!! Form::text('nombre', '', array('class' => 'form-control input-xs', 'id' => 'nombre','onkeyup' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('principioactivo', 'Principio Activo:') !!}
								{!! Form::text('principioactivo', '', array('class' => 'form-control input-xs', 'id' => 'principioactivo','onkeyup' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('categoria_id2', 'Clasificacion:') !!}
								{!! Form::select('categoria_id2', $cboCategoria, null, array('class' => 'form-control input-xs', 'id' => 'categoria_id2','onchange' =>'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('especialidadfarmacia_id2', 'Especialidad:') !!}
								{!! Form::select('especialidadfarmacia_id2', $cboEspecialidad, null, array('class' => 'form-control input-xs', 'id' => 'especialidadfarmacia_id2','onchange' =>'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('presentacion_id2', 'Presentacion:') !!}
								{!! Form::select('presentacion_id2', $cboPresentacion, null, array('class' => 'form-control input-xs', 'id' => 'presentacion_id2','onchange' =>'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('origen_id2', 'Origen:') !!}
								{!! Form::select('origen_id2', $cboOrigen, null, array('class' => 'form-control input-xs', 'id' => 'origen_id2','onchange' =>'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 10, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Nuevo', array('class' => 'btn btn-info btn-xs', 'id' => 'btnNuevo', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
							{!! Form::close() !!}
						</div>
					</div>
				</div>
				<!-- /.box-header -->
				<div class="box-body" id="listado{{ $entidad }}">
				</div>
				<!-- /.box-body -->
			</div>
			<!-- /.box -->
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</section>
<!-- /.content -->	
<script>
	$(document).ready(function () {
		buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
	});
</script>