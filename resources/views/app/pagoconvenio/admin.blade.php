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
								{!! Form::label('nombrep', 'Paciente:') !!}
								{!! Form::text('nombrep', '', array('class' => 'form-control input-xs', 'id' => 'nombrep')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('situacion', 'Situacion:') !!}
								{!! Form::select('situacion', $cboSituacion,'P', array('class' => 'form-control input-xs', 'id' => 'situacion', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('solo', 'Solo:') !!}
								{!! Form::select('solo', $cboSolo,'C', array('class' => 'form-control input-xs', 'id' => 'solo', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-usd"></i> Pagar', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnPagar', 'onclick' => 'modal2 (\''.URL::route($ruta["pagar"], array('listar'=>'SI')).'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-print"></i> Imprimir', array('class' => 'btn btn-info btn-xs', 'id' => 'btnImprimir', 'onclick' => 'imprimirReporte();')) !!}   
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-info btn-xs', 'id' => 'btnExcel', 'onclick' => 'excel();')) !!}
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

		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombrep"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
	});
	function imprimirReporte(){
        window.open("pagoconvenio/pdfReporte?doctor="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombrep"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechafinal"]').val()+"&fechainicial="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechainicial"]').val()+"&situacion="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="situacion"]').val()+"&solo="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="solo"]').val(),"_blank");
    }
    var list = new Array();
    function modal2 (controlador, titulo) {
    	if(list.length>0){
			var idContenedor = "divModal" + contadorModal;
			var divmodal     = "<div id=\"" + idContenedor + "\"></div>";
			var box          = bootbox.dialog({
				message: divmodal,
				className: 'modal' +  contadorModal,
				title: titulo,
				closeButton: false
			});
			box.prop('id', 'modal'+contadorModal);
			modales[contadorModal] = box;
			contadorModal          = contadorModal + 1;
			setTimeout(function(){
				cargarRuta(controlador+'/'+this.list, idContenedor);
			},400);
		}else{
			alert('Debe seleccionar un pago');
		}
	}
	function agregarDetalle(check,id){
		if(check){
			list.push(id);
			$("#td"+id).css('background-color','rgba(238, 0, 0, 0.27)');
		}else{
			for(c=0;list.length>c;c++){
				if(list[c]==id){
					list.splice(c,1);
					$("#td"+id).css('background-color','');
				}
			}
		}
	}
	function validarCheck(){
		for(c=0;list.length>c;c++){
			$("#td"+list[c]).css('background-color','rgba(238, 0, 0, 0.27)');
			$('#chk'+list[c]).attr('checked', true);
		}
	}
	function excel(entidad){
	    window.open("pagoconvenio/excel?doctor="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombrep"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechafinal"]').val()+"&fechainicial="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="fechainicial"]').val()+"&situacion="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="situacion"]').val()+"&solo="+$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="solo"]').val(),"_blank");
	}
</script>