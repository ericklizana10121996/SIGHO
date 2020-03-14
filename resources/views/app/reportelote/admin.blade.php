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
								{!! Form::label('nombre', 'Nombre:') !!}
								{!! Form::text('nombre', '', array('class' => 'form-control input-xs', 'id' => 'nombre')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('origen_id2', 'Origen:') !!}
								{!! Form::select('origen_id2', $cboOrigen ,'', array('class' => 'form-control input-xs', 'id' => 'origen_id2')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('modo', 'Modo:') !!}
								{!! Form::select('modo', $cboModo ,'', array('class' => 'form-control input-xs', 'id' => 'modo')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('fechainicial', 'Fecha Inicial:') !!}
								{!! Form::date('fechainicial', null, array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d',strtotime("+1 month")), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Pdf', array('class' => 'btn btn-warning btn-xs', 'id' => 'btnPdf','onclick' => 'pdf();')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excel();')) !!}
							<div class="form-group">
								{!! Form::label('leyenda', 'Verdes <= 6 meses',array('style' => 'color:green')) !!} |
								{!! Form::label('leyenda', 'Amarillo <= 3 meses',array('style' => 'color:#e6d508')) !!} |
								{!! Form::label('leyenda', 'Rojo <= 1 meses',array('style' => 'color:red')) !!}
							</div>
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
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="doctor"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="paciente"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
	});
	function pdf(entidad){
	    window.open("reportelote/pdf?"+$(IDFORMBUSQUEDA + '{{ $entidad }}').serialize(),"_blank");
	}
	function excel(entidad){
	    window.open("reportelote/excel?"+$(IDFORMBUSQUEDA + '{{ $entidad }}').serialize(),"_blank");
	}
</script>