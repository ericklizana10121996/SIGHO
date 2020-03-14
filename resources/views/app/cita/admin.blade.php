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
								{!! Form::label('fechaI', 'Fecha Inicial:') !!}
								{!! Form::date('fechaI', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechaI')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('fechaF', 'Fecha Fin:') !!}
								{!! Form::date('fechaF', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechaF')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('paciente', 'Paciente:') !!}
								{!! Form::text('paciente', '', array('class' => 'form-control input-xs', 'id' => 'paciente')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('doctor', 'Doctor:') !!}
								{!! Form::text('doctor', '', array('class' => 'form-control input-xs', 'id' => 'doctor')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Nuevo', array('class' => 'btn btn-info btn-xs', 'id' => 'btnNuevo', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
                            {!! Form::button('<i class="glyphicon glyphicon-print"></i> Imprimir', array('class' => 'btn btn-info btn-xs', 'id' => 'btnImprimir', 'onclick' => 'imprimir(\''.$entidad.'\')')) !!}
                            @if($user->usertype_id==8 || $user->usertype_id==1)
                            	{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excel();')) !!}
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
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="paciente"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="doctor"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechaI"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechaF"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
	});
    function imprimir(entidad){
        window.open("cita/pdflistar?fechaI="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechaI"]').val()+"&fechaF="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechaF"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="doctor"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="paciente"]').val(),"_blank");
    }
    function excel(entidad){
	    window.open("cita/excel?fechaI="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechaI"]').val()+"&fechaF="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechaF"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="doctor"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="paciente"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function guardarCheckado(campo,valor,idcita,elem){
		$(elem).prop("disabled",true);
		$.ajax({
			type:"GET",
			url:"cita/marcador",
			data:"campo="+campo+"&valor="+valor+"&idcita="+idcita,
			success: function(a){
				$(elem).prop("disabled",false);
				if(a=="OK"){

				}else{
					alert("NO SE PUDO GUARDAR");
					$(elem).prop("checked",!valor);
				}
			}
		});
	}
</script>