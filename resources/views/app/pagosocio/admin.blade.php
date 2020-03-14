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
							{!! Form::open(['route' => $ruta["search"], 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
                            <div class="form-group">
								{!! Form::label('fechainicial', 'Fecha Inicial:') !!}
								{!! Form::date('fechainicial', null, array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('nombre', 'Doctor:') !!}
								{!! Form::text('nombre', '', array('class' => 'form-control input-xs', 'id' => 'nombre')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('situacion', 'Situacion:') !!}
								{!! Form::select('situacion', $cboSituacion,'P', array('class' => 'form-control input-xs', 'id' => 'situacion', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-print"></i> Imprimir', array('class' => 'btn btn-info btn-xs', 'id' => 'btnImprimir', 'onclick' => 'imprimirReporte();')) !!}   
							{!! Form::button('<i class="glyphicon glyphicon-print"></i> Excel', array('class' => 'btn btn-info btn-xs', 'id' => 'btnImprimir', 'onclick' => 'excel();')) !!}   
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
	function imprimirReporte(){
        window.open("pagosocio/pdfReporte?doctor="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechafinal"]').val()+"&fechainicial="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechainicial"]').val()+"&situacion="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="situacion"]').val(),"_blank");
    }
    function excel(){
        window.open("pagosocio/excel?doctor="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechafinal"]').val()+"&fechainicial="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechainicial"]').val()+"&situacion="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="situacion"]').val(),"_blank");
    }
</script>