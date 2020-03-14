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
							<select class="form-group" id="paciente">
								<option value="0">TODOS</option>
								<option value="1">PARTICULAR</option>
								<option value="2">CONVENIO</option>
							</select>
							<select class="form-group" id="filtro">
								<option value="2">ESPECIALIDAD</option>
								<option value="1">TIPO DE SERVICIO</option>
								<option value="3">SERVICIO</option>
							</select>
							<div class="form-group" id="servicios">
								<input type="text" id="servicio" placeholder="Buscar...">
								
							</div>
							<div id="busqueda">

							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'Genera()')) !!}
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
	var seleccion = 0;
	$('#filtro').change(function(){
		seleccion = 0;
		$('#busqueda').html("");
		$('#servicio').val("");
	});

	$('#servicio').keyup(function(){
		var indicio = $('#servicio').val();
		lin = "servicio";
		if ($('#filtro').val() == 2) {lin = "tiposervicio";}
		if ($('#filtro').val() == 3) {lin = "bservicio";}
		if (indicio.length >= 3) {
			$.ajax({
				type:'GET',
				url:"rpts/"+lin+"/"+indicio,
				success: function(a) {
					$('#busqueda').html(a);
				}
			});
		} else {
			$('#busqueda').html("");
		}
	});

	function selecciona(id){
		seleccion = id;
		Genera();
	}

	function Genera(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		var filtro = $('#filtro').val();
		var paciente = $('#paciente').val();
		if (ff != "") {
			var link = 'reporte.php?rep=31&fi='+fi+'&ff='+ff+'&filtro='+filtro+'&servicio='+seleccion+'&paciente='+paciente;
			window.open(link,'_blank');
		}
	}
	
	$(document).ready(function () {
		//buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		
	});
</script>