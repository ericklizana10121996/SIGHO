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
								{!! Form::date('fechainicial', date('Y-m-d',strtotime("now",strtotime("-30 day"))), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>

							<div class="form-group">
								<label for="Tipo">Tipo:</label>
								<select class="form-control input-xs" id="Tipo">
									<option value="1">Más Vendidos</option>
									<option value="0">Menos Vendidos</option>
								</select>
							</div>
							<div class="form-group">
								<label for="origen">Origen:</label>
								<select class="form-control input-xs" id="origen">
									<option value="1">M</option>
									<option value="2">G</option>
									<option value="3">I</option>
									<option value="4">S</option>
									<option value="5">SO</option>
								</select>
							</div>
							<div class="form-group">
								<label for="filtro">Filtro:</label>
								<select class="form-control input-xs" id="filtro">
									<option value="0">Todos</option>
									<option value="1">Top</option>
									<option value="2">Por Especialidad</option>
									<option value="3">Por Principio Activo</option>
								</select>
							</div>
							<div class="form-group">
								<label for="orden">Ordenar por:</label>
								<select class="form-control input-xs" id="orden">
									<option value="0">Ventas</option>
									<option value="1">Total Soles</option>
								</select>
							</div>
							<div class="form-group" id="meds">
								<input class="form-control input-xs" type="text" id="indicio" placeholder="Valor">
								<div class="form-group" id="rmeds" style="width: 100%"></div>
							</div>

							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'Genera()')) !!}
							{!! Form::button('Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel', 'onclick' => 'Genera2()')) !!}
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
	$('#meds').hide();
	$('#filtro').change(function(){
		$('#rmeds').html('');
		$('#indicio').val('');
		medicinasel = 0;
		if ($('#filtro').val() != 0) {
			$('#meds').show();
		}  else {
			$('#meds').hide();
		}
	});

	$('#indicio').keyup(function(){
		var indicio = $('#indicio').val();
		lin = "nmedicinas";
		if ($('#filtro').val() == 2) {lin = "nespecialidad";}
		else if ($('#filtro').val() == 3) {lin = "nprincipio";}
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
		var filtr = $('#filtro').val();
		var origen = $('#origen').val();
		var orden = $('#orden').val();
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		var filtrs;
		switch(filtr){
			case '0':
				filtrs = 'filtro='+filtr+'&limite=0&palabra=todos&orden='+orden+'&origen='+origen;
				break;
			case '1':
				filtrs = 'filtro='+filtr+'&limite='+$('#indicio').val()+'&palabra=todos&orden='+orden+'&origen='+origen;
				break;
			case '2':
				filtrs = 'filtro='+filtr+'&limite=0&palabra='+medicinasel+'&orden='+orden+'&origen='+origen;
				break;
			case '3':
				filtrs = 'filtro='+filtr+'&limite=0&palabra='+medicinasel+'&orden='+orden+'&origen='+origen;
				break;
		}
		link = 'reporte.php?rep=22&fi='+fi+'&ff='+ff+'&tipo='+$('#Tipo').val()+'&'+filtrs;
		
		window.open(link,'_blank');
	}

	function Genera2(){
		var link = '';
		var filtr = $('#filtro').val();
		var origen = $('#origen').val();
		var orden = $('#orden').val();
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		var filtrs;
		switch(filtr){
			case '0':
				filtrs = 'filtro='+filtr+'&limite=0&palabra=todos&orden='+orden+'&origen='+origen;
				break;
			case '1':
				filtrs = 'filtro='+filtr+'&limite='+$('#indicio').val()+'&palabra=todos&orden='+orden+'&origen='+origen;
				break;
			case '2':
				filtrs = 'filtro='+filtr+'&limite=0&palabra='+medicinasel+'&orden='+orden+'&origen='+origen;
				break;
			case '3':
				filtrs = 'filtro='+filtr+'&limite=0&palabra='+medicinasel+'&orden='+orden+'&origen='+origen;
				break;
		}
		link = 'reporte.php?rep=222&fi='+fi+'&ff='+ff+'&tipo='+$('#Tipo').val()+'&'+filtrs;
		
		window.open(link,'_blank');
	}
	
</script>