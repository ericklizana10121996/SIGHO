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
								{!! Form::label('fechainicial', 'Fecha Inicial:') !!}
								{!! Form::date('fechainicial', null, array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('horainicial', 'Hora Inicial:') !!}
								{!! Form::time('horainicial', '00:00', array('class' => 'form-control input-xs', 'id' => 'horainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('horafinal', 'Hora Final:') !!}
								{!! Form::time('horafinal', '23:59', array('class' => 'form-control input-xs', 'id' => 'horafinal')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('nombre', 'Doctor:') !!}
								{!! Form::text('nombre', '', array('class' => 'form-control input-xs', 'id' => 'nombre')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('nombrepaciente', 'Paciente:') !!}
								{!! Form::text('nombrepaciente', '', array('class' => 'form-control input-xs', 'id' => 'nombrep')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('recibo', 'Nro:') !!}
								{!! Form::text('recibo', '', array('class' => 'form-control input-xs', 'id' => 'recibo')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('situacion', 'Situacion:') !!}
								{!! Form::select('situacion', $cboSituacion,'N', array('class' => 'form-control input-xs', 'id' => 'situacion', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-print"></i> Imprimir', array('class' => 'btn btn-info btn-xs', 'id' => 'btnImprimir', 'onclick' => 'imprimirReporte();')) !!}
							
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excel();')) !!}
							
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
		//buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="recibo"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});

		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombrep"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
	});
	function imprimirReporte(){
        window.open("pagoparticular/pdfReporte?doctor="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombrep"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechafinal"]').val()+"&fechainicial="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechainicial"]').val()+"&horafinal="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="horafinal"]').val()+"&horainicial="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="horainicial"]').val()+"&situacion="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="situacion"]').val(),"_blank");
    }
    function excel(entidad){
	    window.open("pagoparticular/excel?doctor="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombrep"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechafinal"]').val()+"&fechainicial="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechainicial"]').val()+"&horafinal="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="horafinal"]').val()+"&horainicial="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="horainicial"]').val()+"&situacion="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="situacion"]').val(),"_blank");
	}
	function actualizarVista(respuesta,idtr) {
		if(respuesta === 'OK'){
			console.log("PAGO CORRECTO");
			$("#tr_"+idtr).remove();
			console.log($("#tr_"+idtr));
		}else{
			console.log("ERROR AL PAGAR");
		}
	}
</script>