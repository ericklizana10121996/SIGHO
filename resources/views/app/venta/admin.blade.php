<?php 
$url = URL::route($ruta["create2"], array('listar'=>'SI'));
?> 
<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $title }}
	</h1>
</section>
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
								{!! Form::label('numero', 'Nro Doc.:') !!}
								{!! Form::text('numero', '', array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('paciente', 'Paciente:') !!}
								{!! Form::text('paciente', '', array('class' => 'form-control input-xs', 'id' => 'paciente')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('fechainicio', 'Fecha Inicio :', array()) !!}
									<div class='input-group input-group-xs' id='divfechainicio'>
										{!! Form::text('fechainicio', date('d/m/Y'), array('class' => 'form-control input-xs', 'id' => 'fechainicio', 'placeholder' => 'Ingrese fecha inicio')) !!}
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
								{!! Form::label('tipodocumento', 'Tipo Doc.:') !!}
								{!! Form::select('tipodocumento', $cboTipoDoc,'', array('class' => 'form-control input-xs', 'id' => 'tipodocumento', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('situacion', 'Situacion:') !!}
								{!! Form::select('situacion', $cboSituacion,'', array('class' => 'form-control input-xs', 'id' => 'situacion', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 10, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							<?php if($user->usertype_id==1){
							?>
							{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Nuevo', array('class' => 'btn btn-info btn-xs', 'id' => 'btnNuevo', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
							<?php }?>
							<?php if($user->usertype_id==11 || $user->usertype_id==1){
							?> 
							<button type="button" class="btn btn-danger btn-xs" onclick="modal('{{URL::route($ruta["create2"], array('listar'=>'SI'))}}','{{$titulo_registrar}}',this)"><i class="glyphicon glyphicon-plus"></i> Nuevo 2</button>
							{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Nuevo Pendiente Pasado', array('class' => 'btn btn-info btn-xs', 'id' => 'btnNuevo', 'onclick' => 'modal (\''.URL::route('venta.creatependientepasado', array('listar'=>'SI')).'\', \'Registrar Venta Pendiente\', this);')) !!}
							<?php 
								}
							?>
							@if($user->usertype_id==8 || $user->usertype_id==1)
							{!! Form::button('<i class="glyphicon glyphicon-cog"></i> Procesar', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnProcesar', 'onclick' => 'procesar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-cog"></i> Pendientes', array('class' => 'btn btn-default btn-xs', 'id' => 'btnPendientes', 'onclick' => 'excel(\''.$entidad.'\',\'P\')')) !!}
							@endif
							{!! Form::button('<i class="glyphicon glyphicon-cog"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel', 'onclick' => "excel2('".$entidad."',$('#situacion').val())")) !!}
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
		init(IDFORMBUSQUEDA+'{{ $entidad }}', '', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="begindate"]').inputmask("dd/mm/yyyy");

		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="numero"]').keyup(function (e) {
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
		$('#divfechainicio').datetimepicker({
			pickTime: false,
			language: 'es'
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="enddate"]').inputmask("dd/mm/yyyy");
		$('#divfechafin').datetimepicker({
			pickTime: false,
			language: 'es'
		});
		<?php if($user->usertype_id==11){
		?> 
			modal ('{{ $url }}', 'Registrar venta');
		<?php 
			}
		?> 
	});
	function procesar(entidad){
		var btn = $(IDFORMBUSQUEDA + '{!! $entidad !!} :input[id="btnProcesar"]');
		btn.button('loading');
	    $.ajax({
	        type: "POST",
	        url: "venta/procesar",
	        data: "fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicio"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafin"]').val()+"&tipodocumento="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipodocumento"]').val()+"&numero="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="numero"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),
	        success: function(a) {
	        	btn.button('reset');
	        	alert("Procesado correctamente");
	            buscar(entidad);
	        }
	    });
	}

	function excel(entidad,situacion){
	    window.open("venta/excel?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicio"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafin"]').val()+"&tipodocumento="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipodocumento"]').val()+"&numero="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="numero"]').val()+"&situacion="+situacion+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}

	function excel2(entidad,situacion){
	    window.open("venta/excel2?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicio"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafin"]').val()+"&tipodocumento="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipodocumento"]').val()+"&numero="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="numero"]').val()+"&situacion="+situacion+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
</script>