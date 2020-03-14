<?php
$entidad='Producto';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name', 'SIGHO') }}</title>
    <link rel="icon" href="{{ asset('dist/img/user2-160x160.jpg') }}" sizes="16x16 32x32 48x48 64x64" type="image/vnd.microsoft.icon">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    {!! Html::style('bootstrap/css/bootstrap.min.css') !!}
    <!-- Font Awesome -->
    {!! Html::style('dist/css/font-awesome.min.css') !!}
    <!-- Ionicons -->
    {!! Html::style('dist/css/ionicons.min.css') !!}
    <!-- DataTables -->
    {!! Html::style('plugins/datatables/dataTables.bootstrap.css') !!}
    <!-- Theme style -->
    {!! Html::style('dist/css/AdminLTE.min.css') !!}
    {!! Html::style('css/custom.css') !!}
    <!-- AdminLTE Skins. Choose a skin from the css/skins
    folder instead of downloading all of them to reduce the load. -->
    {!! Html::style('dist/css/skins/_all-skins.min.css') !!}
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]-->
    {!! Html::script('dist/js/html5shiv.min.js') !!}
    {!! Html::script('dist/js/respond.min.js') !!}
    <!--[endif]-->
    {{-- bootstrap-datetimepicker: para calendarios --}}
    {!! HTML::style('dist/css/bootstrap-datetimepicker.min.css', array('media' => 'screen')) !!}

    {{-- typeahead.js-bootstrap: para autocompletar --}}
    {!! HTML::style('dist/css/typeahead.js-bootstrap.css', array('media' => 'screen')) !!}

</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
		<!-- Content Header (Page header) -->
		<section class="content-header">
			<h1>
				VISTA MEDICO
				{{-- <small>Descripción</small> --}}
			</h1>
		</section>
		<ul class="nav nav-tabs">
		  <li class="active"><a data-toggle="tab" href="#Farmacia">Farmacia</a></li>
		  <li><a data-toggle="tab" href="#cie">CIE 10</a></li>
		</ul>
		<div class="tab-content">
  			<div id="Farmacia" class="tab-pane fade in active">
				<!-- Main content -->
				<section class="content">
					<div class="row">
						<div class="col-xs-12">
							<div class="box">
								<div class="box-header">
									<div class="row">
										<div class="col-xs-12">
											{!! Form::open(['method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
											{!! Form::hidden('page', 1, array('id' => 'page')) !!}
											{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
											<div class="form-group">
												{!! Form::label('nombre', 'Nombre:') !!}
												{!! Form::text('nombre', '', array('class' => 'form-control input-xs', 'id' => 'nombre')) !!}
											</div>
											{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar2(\''.$entidad.'\')')) !!}
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
			</div>
			<div id="cie" class="tab-pane fade">
				<!-- Main content -->
				<section class="content">
					<div class="row">
						<div class="col-xs-12">
							<div class="box">
								<div class="box-header">
									<div class="row">
										<div class="col-xs-12">
											{!! Form::open(['method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
											<div class="form-group">
												{!! Form::label('cie10', 'Cie10:') !!}
												{!! Form::text('cie10', '', array('class' => 'form-control input-xs', 'id' => 'cie10')) !!}
											</div>
											{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar2', 'onclick' => 'buscar3(\''.$entidad.'\')')) !!}
											{!! Form::close() !!}
										</div>
									</div>
								</div>
								<!-- /.box-header -->
								<div class="box-body" id="listado2{{ $entidad }}">
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
			</div>
			
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer" style="margin-left: 10px !important">
			<div class="pull-right hidden-xs">
				<b>Version</b> 2.3.8
			</div>
			<strong>Copyright © 2016 <a href="#">GARZATEC</a>.</strong> All rights
			reserved.
		</footer>
    </div>
    <!-- ./wrapper -->
    <!-- jQuery 2.2.3 -->
    {!! Html::script('plugins/jQuery/jquery-2.2.3.min.js') !!}
    <!-- Bootstrap 3.3.6 -->
    {!! Html::script('bootstrap/js/bootstrap.min.js') !!}
    {!! Html::script('plugins/datatables/jquery.dataTables.min.js') !!}
    {!! Html::script('plugins/datatables/dataTables.bootstrap.min.js') !!}
    <!-- Slimscroll -->
    {!! Html::script('plugins/slimScroll/jquery.slimscroll.min.js') !!}
    <!-- FastClick -->
    {!! Html::script('plugins/fastclick/fastclick.js') !!}
    <!-- AdminLTE App -->
    {!! Html::script('dist/js/app.min.js') !!}
    <!-- AdminLTE for demo purposes -->
    {!! Html::script('dist/js/demo.js') !!}
    <!-- bootbox code -->
    {!! Html::script('dist/js/bootbox.min.js') !!}
    {{-- Funciones propias --}}
    {!! Html::script('dist/js/funciones.js') !!}
    {{-- jquery.inputmask: para mascaras en cajas de texto --}}
    {!! Html::script('plugins/input-mask/jquery.inputmask.js') !!}
    {!! Html::script('plugins/input-mask/jquery.inputmask.extensions.js') !!}
    {!! Html::script('plugins/input-mask/jquery.inputmask.date.extensions.js') !!}
    {!! Html::script('plugins/input-mask/jquery.inputmask.numeric.extensions.js') !!}
    {!! Html::script('plugins/input-mask/jquery.inputmask.phone.extensions.js') !!}
    {!! Html::script('plugins/input-mask/jquery.inputmask.regex.extensions.js') !!}
    {{-- bootstrap-datetimepicker: para calendarios --}}
    {!! HTML::script('dist/js/moment-with-locales.min.js') !!}
    {!! HTML::script('dist/js/bootstrap-datetimepicker.min.js') !!}
    {{-- typeahead.js-bootstrap: para autocompletar --}}
    {!! HTML::script('dist/js/typeahead.bundle.min.js') !!}
    {!! HTML::script('dist/js/bloodhound.min.js') !!}
    
</body>
</html>
<script>
	$(document).ready(function () {
		//buscar('{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar2('{{ $entidad }}');
			}
		});
		$("#cie10").keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar3('{{ $entidad }}');
			}
		});
	});
	function buscar2(){
		$.ajax({
	        type: "POST",
	        url: "producto/vistamedico",
	        data: "producto="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').val()+"&_token=<?php echo csrf_token(); ?>",
	        success: function(a) {
	        	$("#listado{{ $entidad }}").html(a);
	        }
	    });
	}
	function buscar3(){
		$.ajax({
	        type: "POST",
	        url: "producto/cie10",
	        data: "cie="+$("#cie10").val()+"&_token=<?php echo csrf_token(); ?>",
	        success: function(a) {
	        	$("#listado2{{ $entidad }}").html(a);
	        }
	    });
	}
</script>