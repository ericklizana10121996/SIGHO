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
								{!! Form::label('paciente', 'Paciente:') !!}
								{!! Form::text('paciente', '', array('class' => 'form-control input-xs', 'id' => 'paciente')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('doctor', 'Doctor:') !!}
								{!! Form::text('doctor', '', array('class' => 'form-control input-xs', 'id' => 'doctor')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('fechainicial', 'Fecha Inicial:') !!}
								{!! Form::date('fechainicial', date('Y-m-d',strtotime("now",strtotime("-1 month"))), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel', 'onclick' => 'excel(\''.$entidad.'\')')) !!}
							<?php /*{!! Form::button('<i class="glyphicon glyphicon-print"></i> Imprimir', array('class' => 'btn btn-success btn-xs', 'id' => 'btnPdf','onclick' => 'pdf();')) !!}
							 */ ?>
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
	function excel(entidad){
	    window.open("reportepagoconvenio/excel?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="doctor"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
</script>