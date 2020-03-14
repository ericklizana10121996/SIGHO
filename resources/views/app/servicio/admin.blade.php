<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $title }}
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
                    			{!! Form::label('tipopago', 'Tipo Pago:') !!}
                   				{!! Form::select('tipopago', $cboTipoPago, null, array('class' => 'form-control input-xs', 'id' => 'tipopago', 'onchange' => 'mostrarPlan();buscar(\''.$entidad.'\')')) !!}
                    		</div>
							<div class="form-group">
								{!! Form::label('nombre', 'Nombre:') !!}
								{!! Form::text('nombre', '', array('class' => 'form-control input-xs', 'id' => 'nombre')) !!}
							</div>
							<div class="form-group" style="display: none" id="divPlan">
								{!! Form::label('plan', 'Plan:') !!}
								{!! Form::text('plan', '', array('class' => 'form-control input-xs', 'id' => 'plan')) !!}
							</div>
                            <div class="form-group">
                    			{!! Form::label('tiposervicio', 'Tipo:') !!}
                   				{!! Form::select('tiposervicio', $cboTipoServicio, null, array('class' => 'form-control input-xs', 'id' => 'tiposervicio' , 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
                    		</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							@if($user->usertype_id==1 || $user->usertype_id==4)
								{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Nuevo', array('class' => 'btn btn-info btn-xs', 'id' => 'btnNuevo', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
								{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-warning btn-xs', 'id' => 'btnExcel', 'onclick' => 'excel(\''.$entidad.'\')')) !!}
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
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="plan"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
	});
	function mostrarPlan(){
		if($(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="tipopago"]').val()=="Convenio"){
			$("#divPlan").css("display","");
		}else{
			$("#divPlan").css("display","none");
		}
	}
	function excel(entidad){
	    window.open("servicio/excel?tipopago="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipopago"]').val()+"&nombre="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="nombre"]').val()+"&tiposervicio="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tiposervicio"]').val()+"&plan="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="plan"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
</script>