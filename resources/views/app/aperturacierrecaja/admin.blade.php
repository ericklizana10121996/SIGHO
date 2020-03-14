<?php
use App\Aperturacierrecaja;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$ind = 0; // 0=Abierto , 1=Cerrada 
$aperturacierrecaja = Aperturacierrecaja::where('estado','=','A')->first();

if ($aperturacierrecaja !== null) {
		$ind =1;
}

$url = URL::to("aperturacierrecaja");
?>
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
							@if($ind == 0)
							{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Abrir caja', array('class' => 'btn btn-info btn-xs', 'id' => 'btnNuevo', 'onclick' => 'abrircaja();')) !!}
							@else
								@if($ind == 1)
									{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Cerrar caja', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnNuevo', 'onclick' => 'cerrarcaja();')) !!}
								@endif
							@endif
							
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
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechainicio"]').inputmask("dd/mm/yyyy");
		$('#divfechainicio').datetimepicker({
			pickTime: false,
			language: 'es'
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechafin"]').inputmask("dd/mm/yyyy");
		$('#divfechafin').datetimepicker({
			pickTime: false,
			language: 'es'
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});

	});

	function abrircaja () {

	var mensaje = '¿Está seguro de abrir caja?';
	
	bootbox.confirm({
		message : mensaje,
		buttons: {
			'cancel': {
				label: 'Cancelar',
				className: 'btn btn-default btn-sm'
			},
			'confirm':{
				label: 'Abrir',
				className: 'btn btn-success btn-sm'
			}
		}, 
		callback: function(result) {
			if (result) {
				var _token =$('input[name=_token]').val();
				$.post('{{ URL::route("aperturacierrecaja.abrir")}}', {_token: _token} , function(data){
						if (data === 'OK') {
							bootbox.alert("Caja abierta Correctamente");
				            setTimeout(function () {
				                $('#fechainicio').focus();
				            },2000) 
							cargarRutaMenu('{{ $url }}', 'container', '16');
							
						}
						
					});
			};
		}            
	}).find("div.modal-content").addClass("bootboxConfirmWidth");
	setTimeout(function () {
		if (contadorModal !== 0) {
			$('.modal' + (contadorModal-1)).css('pointer-events','auto');
			$('body').addClass('modal-open');
		}
	},2000);
}

function cerrarcaja () {

	var mensaje = '¿Está seguro de cerrar caja?';
	
	bootbox.confirm({
		message : mensaje,
		buttons: {
			'cancel': {
				label: 'Cancelar',
				className: 'btn btn-default btn-sm'
			},
			'confirm':{
				label: 'Cerrar',
				className: 'btn btn-danger btn-sm'
			}
		}, 
		callback: function(result) {
			if (result) {
				var _token =$('input[name=_token]').val();
				$.post('{{ URL::route("aperturacierrecaja.cerrar")}}', {_token: _token} , function(data){
						if (data === 'OK') {
							bootbox.alert("Caja cerrada Correctamente");
				            setTimeout(function () {
				                $('#fechainicio').focus();
				            },2000) 
							cargarRutaMenu('{{ $url }}', 'container', '16');
							
						}
						
					});
			};
		}            
	}).find("div.modal-content").addClass("bootboxConfirmWidth");
	setTimeout(function () {
		if (contadorModal !== 0) {
			$('.modal' + (contadorModal-1)).css('pointer-events','auto');
			$('body').addClass('modal-open');
		}
	},2000);
}
</script>