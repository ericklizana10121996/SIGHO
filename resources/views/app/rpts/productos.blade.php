<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $title }}
		{{-- <small>Descripción</small> --}}
	</h1>
</section>

<style type="text/css">
	.resul{
		border: solid gray 1px;
	}
	.resul:hover{
		background: rgb(102,175,233);
	}
</style>

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
								<label for="Tipo">Tipo:</label>
								<select class="form-control" id="Tipo">
									<option value="0">Todos</option>
									<option value="1">Especìfico</option>
									<option value="2">Por Especialidad</option>
									<option value="3">Por Principio Activo</option>
								</select>
							</div>

							<div class="form-group" id="origend">
								<label for="origen">Origen:</label>
								<select class="form-control" id="origen">
									<option value="0">Todos</option>
									<option value="1">M</option>
									<option value="6">G</option>
									<option value="4">I</option>
									<option value="8">S</option>
									<option value="9">SO</option>
									<option value="-1">NULO</option>
								</select>
							</div>

							<div class="form-group" id="presentaciond">
								
							</div>

							<div class="form-group" id="meds">
								<input class="form-control" type="text" id="indicio" placeholder="Búsqueda">
								<div class="form-group" id="rmeds" style="width: 100%"></div>
							</div>

							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'Genera()')) !!}
							{!! Form::close() !!}

							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar2', 'onclick' => 'Genera2()')) !!}
							{!! Form::close() !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel 2', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar3', 'onclick' => 'Genera3()')) !!}
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
	var medicinasel = 0;

	function cargaPresentacion() {
		$.ajax({
			type:'GET',
			url:"rpts/presentaciones",
			data:'',
			success: function(a) {
				$('#presentaciond').html(a);
			}
		});
	}
	cargaPresentacion();

	$('#meds').hide();
	$('#origend').show();
	$('#presentaciond').show();
	$('#Tipo').change(function(){
		$('#rmeds').html('');
		$('#indicio').val('');
		medicinasel = 0;
		cambia = 0;
		if ($('#Tipo').val() != 0) {
			$('#meds').show();
			$('#origend').hide();
			$('#presentaciond').hide();
		}  else {
			$('#origend').show();
			$('#presentaciond').show();
			$('#meds').hide();
		}
	});

	$('#indicio').keyup(function(){
		var indicio = $('#indicio').val();
		lin = "nmedicinas";
		if ($('#Tipo').val() == 2) {lin = "nespecialidad";}
		else if ($('#Tipo').val() == 3) {lin = "nprincipio";}
		if (indicio.length >= 3) {
			$.ajax({
				type:'GET',
				url:"rpts/"+lin+"/"+indicio,
				success: function(a) {
					$('#rmeds').html(a);
				}
			});
		}
	});

	function selecciona(id){
		medicinasel = id;
		Genera();
	}
	function Genera(){
		var link = '';
		var presentacion = $('#presentacion').val();
		var origen = $('#origen').val();
		if ($('#Tipo').val() == 0) {
			link = 'reporte.php?rep=21&fi=&ff=&med=0&tipo=0&origen='+origen+'&presentacion='+presentacion;
		} else {
			link = 'reporte.php?rep=21&fi=&ff=&origen='+origen+'&tipo='+$('#Tipo').val()+'&med='+medicinasel+'&presentacion='+presentacion;
		}
		
		window.open(link,'_blank');
	}

	function Genera2(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		var presentacion = $('#presentacion').val();
		var origen = $('#origen').val();
		if (ff != "") {
			var link = 'reporte.php?rep=211&fi=&ff=&origen='+origen+'&tipo='+$('#Tipo').val()+'&med='+medicinasel+'&presentacion='+presentacion;
			window.open(link,'_blank');
		}
	}
	
	function Genera3(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		var presentacion = $('#presentacion').val();
		var origen = $('#origen').val();
		if (ff != "") {
			var link = 'reporte.php?rep=212&fi=&ff=&origen='+origen+'&tipo='+$('#Tipo').val()+'&med='+medicinasel+'&presentacion='+presentacion;
			window.open(link,'_blank');
		}
	}
	
</script>