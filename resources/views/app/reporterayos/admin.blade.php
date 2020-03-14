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
							{!! Form::hidden('tiposervicio_id', '', array('id' => 'tiposervicio_id')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
							<div class="form-group">
								{!! Form::label('tipopaciente', 'Tipo Paciente:') !!}
								{!! Form::select('tipopaciente', $cboTipoPaciente,null, array('class' => 'form-control input-xs', 'id' => 'tipopaciente')) !!}
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
								{!! Form::label('fechainicial', 'Fecha Inicial:') !!}
								{!! Form::date('fechainicial', date('Y-m-d',strtotime("now",strtotime("-1 month"))), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>
							<div class="form-group">
								<input type="checkbox" onclick="checkBusqueda('Laboratorio',checked,2);">
								{!! Form::label('laboratorio', 'Laboratorio') !!}
							</div>
							<div class="form-group">
								<input id="ecografias" type="checkbox" onclick="checkBusqueda('Ecografias',checked,4);">
								{!! Form::label('ecografias', 'Ecografias') !!}
							</div>
							<div class="form-group">
								<input type="checkbox" onclick="checkBusqueda('Radiografias',checked,5);">
								{!! Form::label('radiografias', 'Radiografias') !!}
							</div>
							<div class="form-group">
								<input type="checkbox" onclick="checkBusqueda('Tomografias',checked,16);">
								{!! Form::label('tomografias', 'Tomografias') !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-usd"></i> Pagar', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnPagar', 'onclick' => 'modal2 (\''.URL::route($ruta["pagar"], array('listar'=>'SI')).'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excel();')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel Marcados', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel2','onclick' => 'excels();')) !!}
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
	var list1=new Array();
	function checkBusqueda(servicio,check,servicio_id){
		if(check){
			list1.push(servicio_id);
		}else{
			for(c=0; c < list1.length; c++){
		        if(list1[c] == servicio_id) {
		            list1.splice(c,1);
		        }
		    }
		}
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="tiposervicio_id"]').val(list1);
	}
	function excel(entidad){
	    window.open("reporterayos/excel?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&tiposervicio_id="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tiposervicio_id"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="doctor"]').val()+"&tipopaciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipopaciente"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
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

	function excels(entidad){
		if($("#ecografias").prop("checked")){
			excel3(entidad);
		} else {
			excel2(entidad);
		}
	}

	function excel2(entidad){
	    window.open("reporterayos/excel2?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&tiposervicio_id="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tiposervicio_id"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="doctor"]').val()+"&tipopaciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipopaciente"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}

	function excel3(entidad){
	    window.open("reporterayos/excel3?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&tiposervicio_id="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tiposervicio_id"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="doctor"]').val()+"&tipopaciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipopaciente"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
</script>