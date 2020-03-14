<!-- Content Header (Page header) -->
<style>
.tr_hover{
	color:red;
}
</style>
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
								{!! Form::date('fechainicial', date('Y').'-'.date('m').'-01', array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('paciente', 'Paciente:') !!}
								{!! Form::text('paciente', '', array('class' => 'form-control input-xs', 'id' => 'paciente')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('medico', 'Medico:') !!}
								{!! Form::text('medico', '', array('class' => 'form-control input-xs', 'id' => 'medico')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('numero', 'Nro:') !!}
								{!! Form::text('numero', '', array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('cirugia', 'Cirugia:') !!}
								{!! Form::text('cirugia', '', array('class' => 'form-control input-xs', 'id' => 'cirugia')) !!}
							</div>
							
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 50, 30, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Nuevo', array('class' => 'btn btn-info btn-xs', 'id' => 'btnNuevo', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI' , 'facturacion'=>'NO') ).'\', \''.$titulo_registrar.'\', this);')) !!}

{{-- 							{!! Form::button('<i class="glyphicon glyphicon-save"></i> Guardar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnGuardar','onclick' => 'cargado();'/)) !!}
							 --}}
{{-- 							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excel();')) !!}
 --}}

 							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excelPendiente();')) !!}
{{-- 							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Reporte Diario', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excelDiario();')) !!} --}}
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
	var arreglo_cargar = [];
	var arreglo_anulado = [];

	$(document).ready(function () {
		buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechainicial"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechafinal"]').keyup(function (e) {
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
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="numero"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="cirugia"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});

		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="medico"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		// setInterval(buscar('{{ $entidad }}'),5000);
	});

	function excel(entidad){
	    window.open("prefactura/excel?"+$(IDFORMBUSQUEDA + '{{ $entidad }}').serialize(),"_blank");
	}
	function excelPendiente(entidad){
	   window.open("pagoscirugiapendiente/excel?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="medico"]').val()+"&numero="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="numero"]').val()+"&cirugia="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="cirugia"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");

	    // window.open("pagoscirugia/excelPendiente?"+$(IDFORMBUSQUEDA + '{{ $entidad }}').serialize(),"_blank");
	}
	function excelDiario(entidad){
	    window.open("prefactura/excelDiario?"+$(IDFORMBUSQUEDA + '{{ $entidad }}').serialize(),"_blank");
	}
</script>