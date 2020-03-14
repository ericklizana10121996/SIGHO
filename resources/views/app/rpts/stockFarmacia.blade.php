<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $title }}
		{{-- <small>Descripción</small> --}}
	</h1>
</section>

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
								<label for="mini">Productos con existencias menores a:</label>
								<input class="form-control input-xs" type="number" id="mini" value="100">
							</div>

							<div class="form-group" id="origend">
								<label for="origen">Origen:</label>
								<select class="form-control input-xs" id="origen">
									<option value="0">Todos</option>
									<option value="1">M</option>
									<option value="2">G</option>
									<option value="3">I</option>
									<option value="4">S</option>
									<option value="5">SO</option>
								</select>
							</div>

							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'Genera()')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnBuscar', 'onclick' => 'Genera1()')) !!}
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
	function Genera(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		var mini = $('#mini').val();
		var origen = $('#origen').val();
		if (ff != "") {
			var link = 'reporte.php?rep=16&fi=a&ff=a&min='+mini+'&origen='+origen;
			window.open(link,'_blank');
		}
	}
	function Genera1(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		var mini = $('#mini').val();
		var origen = $('#origen').val();
		if (ff != "") {
			var link = 'reporte.php?rep=161&fi=a&ff=a&min='+mini+'&origen='+origen;
			window.open(link,'_blank');
		}
	}
</script>