<?php 
$url = URL::route($ruta["create2"], array('listar'=>'SI'));
?> 
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
							{!! Form::open(['route' => $ruta["search"], 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
							<div class="form-group">
								{!! Form::label('numero', 'Nro:') !!}
								{!! Form::text('numero', '', array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('nombre', 'Proveedor:') !!}
								{!! Form::text('nombre', '', array('class' => 'form-control input-xs', 'id' => 'nombre')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('fechainicio', 'Fecha Inicio :', array()) !!}
									<div class='input-group input-group-xs' id='divfechainicio'>
										{!! Form::text('fechainicio', date('d/m/Y',strtotime('now',strtotime('-5 day'))), array('class' => 'form-control input-xs', 'id' => 'fechainicio', 'placeholder' => 'Ingrese fecha inicio')) !!}
										<span class="input-group-btn">
											<button class="btn btn-default calendar">
												<i class="glyphicon glyphicon-calendar"></i>
											</button>
										</span>
									</div>
							</div>
							<div class="form-group">
								{!! Form::label('fechafin', 'Fecha Fin :', array()) !!}
									<div class='input-group input-group-xs' id='divfechafin'>
										{!! Form::text('fechafin', null, array('class' => 'form-control input-xs', 'id' => 'fechafin', 'placeholder' => 'Ingrese fecha fin')) !!}
										<span class="input-group-btn">
											<button class="btn btn-default calendar">
												<i class="glyphicon glyphicon-calendar"></i>
											</button>
										</span>
									</div>
							</div>
							<div class="form-group">
								{!! Form::label('tipodocumento', 'Tipo Doc.:') !!}
								{!! Form::select('tipodocumento', $cboTipoDoc,'', array('class' => 'form-control input-xs', 'id' => 'tipodocumento', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 10, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							<?php if($user->usertype_id==1){
							?>
							{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Nuevo', array('class' => 'btn btn-info btn-xs', 'id' => 'btnNuevo', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
							<?php } ?>
							<button type="button" class="btn btn-danger btn-xs" onclick="modal('{{URL::route($ruta["create2"], array('listar'=>'SI'))}}','{{$titulo_registrar}}',this)"><i class="glyphicon glyphicon-plus"></i> Nuevo 2</button>
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
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechainicio"]').inputmask("dd/mm/yyyy");
		$('#divfechainicio').datetimepicker({
			pickTime: false,
			language: 'es'
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechafin"]').inputmask("dd/mm/yyyy");
		$('#divfechafin').datetimepicker({
			pickTime: false,
			language: 'es'
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="numero"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});

		modal ('{{ $url }}', 'Registrar compra');
	});
</script>