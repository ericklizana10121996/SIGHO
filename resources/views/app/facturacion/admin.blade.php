<!-- Content Header (Page header) -->
<?php
	if($user->id==41 || $user->id == 49)
	    $serie='008';
	else
	    $serie='002';

	$serie = 'Todos';
?>

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
								{!! Form::date('fechainicial', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
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
								{!! Form::label('serie', 'Serie:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
	        			  		<div class="col-lg-2 col-md-2 col-sm-2" style="margin-top: -2px;">
			        				{!! Form::select('serie', $cboSerie, $serie, array('class' => 'form-control input-xs', 'id' => 'serie')) !!}
			        			</div>
			        		</div>
							<div class="form-group">
								{!! Form::label('numero', 'Nro:') !!}
								{!! Form::text('numero', '', array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
							</div>

						  	<div class="form-group">
				                {!! Form::label('retramite', 'Retramite:') !!}
			                    {!! Form::hidden('retramite', 'N', array('id' => 'retramite')) !!}
			                    <input type="checkbox" id="chkRetramite" name="chkRetramite" onclick="if(this.checked){$('#retramite').val('S');}else{$('#retramite').val('N');}" />
				            </div> 

							<div class="form-group">
								{!! Form::label('usuario', 'Mi Usuario:') !!}
								{!! Form::hidden('usuario', '', array('id' => 'usuario')) !!}
								<input type="checkbox" checked="" name="check" id="check" onclick="if(this.checked){$('#usuario').val('');}else{$('#usuario').val('Todos');}">
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 50, 30, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel', 'onclick' => 'excel(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Nuevo', array('class' => 'btn btn-info btn-xs', 'id' => 'btnNuevo', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
							@if($user->usertype_id==8 || $user->usertype_id==1 || $user->usertype_id==20 || $user->usertype_id==4)
							{!! Form::button('<i class="glyphicon glyphicon-cog"></i> Procesar', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnProcesar', 'onclick' => 'procesar(\''.$entidad.'\')')) !!}
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

		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="serie"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});

		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="chkRetramite"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});

	});
    function imprimir(entidad){
        window.open("cita/pdflistar?fecha="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fecha"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="doctor"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="paciente"]').val(),"_blank");
    }
    function excel(entidad){
        window.open("facturacion/excel?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&numero="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="numero"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="paciente"]').val()+"&retramite="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="retramite"]').val()+'&usuario='+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="usuario"]').val(),"_blank");
    }

    function procesar(entidad){
		var btn = $(IDFORMBUSQUEDA + '{!! $entidad !!} :input[id="btnProcesar"]');
		btn.button('loading');
		
        $.ajax({
	        type: "POST",
	        url: "facturacion/procesar",
	        data: "fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&numero="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="numero"]').val()+"&retramite="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="retramite"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),
	        success: function(a) {
	        	btn.button('reset');
	        	alert("Procesado correctamente");
	            buscar(entidad);
	        }
	    });
	}
</script>