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
								{!! Form::label('fechainicial', 'Fecha Inicial:') !!}
								{!! Form::date('fechainicial', date('Y-m-d',strtotime("now",strtotime("-365 day"))), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>

							<div class="form-group">
								<label for="Tipo">Tipo:</label>
								<select class="form-control" id="Tipo">
									<option value="0">Todos</option>
									<option value="1">Pagado</option>
									<option value="2">Pendiente</option>
									<option value="3">Por Producto</option>
									<option value="5">Por Proveedor</option>
									<option value="4">Anulados</option>
								</select>
							</div>

							<div class="form-group" id="meds">
								<input class="form-control" type="text" id="indicio" placeholder="Buscar...">
								<div class="form-group" id="rmeds" style="width: 100%"></div>
							</div>

							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'Genera()')) !!}
							{!! Form::close() !!}

							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar2', 'onclick' => 'Genera2()')) !!}
							{!! Form::close() !!}

							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel Detallado', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar3', 'onclick' => 'Genera3()')) !!}
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

	$('#indicio').keyup(function(){
		var indicio = $('#indicio').val();
		var link = 'nmedicinas';
		if ($('#Tipo').val() == 5) { link='nproveedor'; }
		if (indicio.length >= 3) {
			$.ajax({
				type:'GET',
				url:"rpts/"+link+"/"+indicio,
				data:'',
				success: function(a) {
					$('#rmeds').html(a);
				}
			});
		} else {
			$('#rmeds').html('');
		}
	});
	function selecciona(id){
		medicinasel = id;
		Genera();
	}
	
	$('#meds').hide();

	$('#Tipo').change(function(){
		$('#rmeds').html('');
		$('#indicio').val('');
		if ($('#Tipo').val() == 3 || $('#Tipo').val() == 5) {
			$('#meds').show();
		} else {
			$('#meds').hide();
		}	
	});

	function Genera(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		if (ff != "") {
			var med = '', tipo ='';
			if ($('#Medico').val() != null) {
				med = '&med='+$('#Medico').val();
			}
			if ($('#Tipo').val() != null) {
				tipo = '&tipo='+$('#Tipo').val();
			}
			if ($('#Tipo').val() == 3 || $('#Tipo').val() == 5) {
				tipo = '&tipo='+$('#Tipo').val()+'&med='+medicinasel;
			}
			var link = 'reporte.php?rep=13&fi='+fi+'&ff='+ff+''+med+''+tipo;
			window.open(link,'_blank');
		}
	}

	function Genera2(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		if (ff != "") {
			var med = '', tipo ='';
			if ($('#Medico').val() != null) {
				med = '&med='+$('#Medico').val();
			}
			if ($('#Tipo').val() != null) {
				tipo = '&tipo='+$('#Tipo').val();
			}
			if ($('#Tipo').val() == 3) {
				tipo = '&tipo=3&med='+medicinasel;
			}
			var link = 'reporte.php?rep=131&fi='+fi+'&ff='+ff+''+med+''+tipo;
			window.open(link,'_blank');
		}
	}
	
	function Genera3(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		if (ff != "") {
			var med = '', tipo ='';
			if ($('#Medico').val() != null) {
				med = '&med='+$('#Medico').val();
			}
			if ($('#Tipo').val() != null) {
				tipo = '&tipo='+$('#Tipo').val();
			}
			if ($('#Tipo').val() == 3) {
				tipo = '&tipo=3&med='+medicinasel;
			}
			var link = 'reporte.php?rep=132&fi='+fi+'&ff='+ff+''+med+''+tipo;
			window.open(link,'_blank');
		}
	}
	
</script>