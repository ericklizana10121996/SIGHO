<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ 'Ventas Pendientes' }}
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
							{!! Form::open(['route' => 'caja.listardescarga', 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
							{!! Form::hidden('movimiento_id', $movimiento_id, array('id' => 'movimiento_id')) !!}
							<div class="form-group">
								{!! Form::label('numero', 'Nro Documento:') !!}
								{!! Form::text('numero', '', array('class' => 'form-control input-xs', 'id' => 'numero', 'onkeyup' =>'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('fechainicio', 'Fecha Inicio :', array()) !!}
									<div class='input-group input-group-xs' id='divfechainicio'>
										{!! Form::text('fechainicio', null, array('class' => 'form-control input-xs', 'id' => 'fechainicio', 'placeholder' => 'Ingrese fecha inicio')) !!}
										<span class="input-group-btn">
											<button class="btn btn-default calendar">
												<i class="glyphicon glyphicon-calendar"></i>
											</button>
										</span>
									</div>
							</div>
							<div class="form-group">
								{!! Form::label('fechafin', 'Fecha Fin :', array()) !!}
									<div class='input-group input-group-xs' id='divfechafin'>
										{!! Form::text('fechafin', null, array('class' => 'form-control input-xs', 'id' => 'fechafin', 'placeholder' => 'Ingrese fecha fin')) !!}
										<span class="input-group-btn">
											<button class="btn btn-default calendar">
												<i class="glyphicon glyphicon-calendar"></i>
											</button>
										</span>
									</div>
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 10, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-floppy-saved"></i> Guardar', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'guardardescarga();')) !!}
							
						</div>
					</div>
				</div>
				<!-- /.box-header -->
				<div class="box-body" id="listado{{ $entidad }}">
				</div>
				{!! Form::button('Cerrar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancel', 'onclick' => 'cerrarModal();')) !!}
				{!! Form::close() !!}
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
		configurarAnchoModal('1200');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="begindate"]').inputmask("dd/mm/yyyy");

		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="numero"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$('#divfechainicio').datetimepicker({
			pickTime: false,
			language: 'es'
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="enddate"]').inputmask("dd/mm/yyyy");
		$('#divfechafin').datetimepicker({
			pickTime: false,
			language: 'es'
		});
		
	});

	function changestate (elemento) {
		var id = elemento.id;
		var venta_id = elemento.value;
		//alert(value);
	if (elemento.checked) {
		//$('#'+id).val('SI');
		var _token =$('input[name=_token]').val();
		$.post('{{ URL::route("caja.agregardescarga")}}', {venta_id: venta_id,_token: _token} , function(data){
			//$('#divDetail').html(data);

		});
	} else{
		//$('#'+id).val('NO');
		var _token =$('input[name=_token]').val();
		$.post('{{ URL::route("caja.quitardescarga")}}', {venta_id: venta_id,_token: _token} , function(data){
			//$('#divDetail').html(data);

		});
	};


}
</script>