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
								<select class="form-control input-xs" id="Tipo">
									<option value="0">Todos</option>
									<option value="1">Inicial 2018</option>
								</select>
							</div>

							<div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>

							<div class="form-group" id="origend">
								<label for="origen">Origen:</label>
								<select class="form-control input-xs" id="origen">
									<option value="0">Todos</option>
									<option value="1">M</option>
									<option value="6">G</option>
									<option value="4">I</option>
									<option value="8">S</option>
									<option value="9">SO</option>
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
		if ($('#Tipo').val() != 0) {
			//$('#meds').show();
			$('#fechafinal').hide();
			$('#btnBuscar2').hide();
		}  else {
			$('#fechafinal').show();
			$('#btnBuscar2').show();
			//$('#meds').hide();
		}
	});

	function Genera(){
		var link = '';
		var ff = $('#fechafinal').val();
		var presentacion = $('#presentacion').val();
		var origen = $('#origen').val();
		if ($('#Tipo').val() == 0) {
			link = 'reporte.php?rep=25&fi=&ff='+ff+'&med=0&lab=0&origen='+origen+'&presentacion='+presentacion;
		} else {
			link = 'reporte.php?rep=27&fi=&ff=&med=0&lab=0&origen='+origen+'&presentacion='+presentacion;
		}
		
		window.open(link,'_blank');
	}

	function Genera2(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		var presentacion = $('#presentacion').val();
		var origen = $('#origen').val();
		if (ff != "") {
			var link = 'reporte.php?rep=251&fi=&ff='+ff+'&med=0&lab=0&origen='+origen+'&presentacion='+presentacion;
			window.open(link,'_blank');
		}
	}
	
</script>