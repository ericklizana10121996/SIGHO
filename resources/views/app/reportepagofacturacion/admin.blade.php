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
								{!! Form::label('plan', 'Plan:') !!}
								{!! Form::text('plan', '', array('class' => 'form-control input-xs', 'id' => 'plan')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('doctor', 'Doctor:') !!}
								{!! Form::text('doctor', '', array('class' => 'form-control input-xs', 'id' => 'doctor')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('fechainicial', 'Fecha de Cobranza Inicial:') !!}
								{!! Form::date('fechainicial', date('Y-m-d',strtotime("now",strtotime("-1 month"))), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha de Cobranza Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('nroope', 'Nro Operacion:') !!}
								{!! Form::text('nroope', '', array('class' => 'form-control input-xs', 'id' => 'nroope')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('nrodoc', 'Nro Documento:') !!}
								{!! Form::text('nrodoc', '', array('class' => 'form-control input-xs', 'id' => 'nrodoc')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('situacion', 'Situacion:')!!}
								{!! Form::select('situacion', $cboSituacion, 'N',array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 10, 50, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excel();')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel Doctor', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel3','onclick' => 'excelDoctor();')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel General', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel2','onclick' => 'excelGeneral();')) !!}
							<button type="button" disabled="" id="btnPagoMedico" class="btn btn-warning btn-xs" onclick="nuevoPagoDoctor();"><i class="glyphicon glyphicon-plus"></i> Pago Doctor</button>
							<button type="button" disabled="" id="btnReporteMedico" class="btn btn-danger btn-xs" onclick="nuevoReporte();"><i class="glyphicon glyphicon-plus"></i> Generar Reporte Pago Doctor</button>
							<button type="button" id="" class="btn btn-info btn-xs" onclick="marcarTodos(true);">Marcar Todos</button>
							<button type="button" id="" class="btn btn-info btn-xs" onclick="marcarTodos(false);">Desmarcar Todos</button>
							{!! Form::close() !!}
						</div>
					</div>
					<br>
					<div class="row" style="padding-left: 10px;">
						<div class="col-xs-5" style="background-color: wheat; padding-top: 5px; padding-bottom: 5px; padding-left: 0px; padding-right: 0px;">
							<div class="col-xs-6">
								<input class="form-control input-xs" id="mesCon" name="mes" type="month" value="{{date("Y-m")}}">
							</div>
							<div class="col-xs-6">
								<button type="button" id="" class="btn btn-info btn-xs" onclick="excelConsolidado();">Generar Consolidado</button>
							</div>
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
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="plan"]').keyup(function (e) {
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
	    window.open("reportepagofacturacion/excel?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&plan="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="plan"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="doctor"]').val()+"&tipopaciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipopaciente"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function excelDoctor(entidad){
	    window.open("reportepagofacturacion/excelDoctor?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&plan="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="plan"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="doctor"]').val()+"&tipopaciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipopaciente"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function excelGeneral(entidad){
	    window.open("reportepagofacturacion/excelGeneral?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="doctor"]').val()+"&plan="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="plan"]').val()+"&tipopaciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipopaciente"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function excelConsolidado(){
	    window.open("reportepagofacturacion/generarreporteconsolidadoexcel?mes="+$("#mesCon").val(),"_blank");
	}

	var idsSelec = [];
	var numerosSelec = [];
	var totalesSelec = [];

	function verificarSeleccion(ipt){
		var idS = $(ipt).attr("data_id");
		var numeroS = $(ipt).attr("data_numero");
		var totalS = $(ipt).attr("data_total");
		if($(ipt).is(":checked")){
			agregarSeleccion(idS,numeroS,totalS);
		}else{
			quitarSeleccion(idS);
		}
	}

	function agregarSeleccion(idS,numeroS,totalS){
		idsSelec.push(idS);
		numerosSelec.push(numeroS);
		totalesSelec.push(totalS);
		habilitarBoton();
	}

	function quitarSeleccion(idS){
		var index = idsSelec.indexOf(idS);
		if (index > -1) {
		  idsSelec.splice(index, 1);
		  numerosSelec.splice(index, 1);
		  totalesSelec.splice(index, 1);
		  habilitarBoton();
		}
	}

	function revisarSeleccionados(){
		console.log(idsSelec);
		$(".chkSeleccionados").prop("disabled",true);
		$(".chkSeleccionados").each(function(key,val){
			var idS = $(val).attr("data_id");
			var index = idsSelec.indexOf(idS);
			if (index > -1) {
				$(val).prop("checked",true);
			}else{
				$(val).prop("checked",false);
			}
			$(val).prop("disabled",false);
		});
		habilitarBoton();
	}

	function habilitarBoton(){
		if(idsSelec.length>0){
			$("#btnPagoMedico").prop("disabled",false);
			$("#btnReporteMedico").prop("disabled",false);
		}else{
			$("#btnPagoMedico").prop("disabled",true);
			$("#btnReporteMedico").prop("disabled",true);
		}
	}

	function nuevoPagoDoctor(){
		var idS = idsSelec.join(",");
		var numeroS = numerosSelec.join(", ");
		modal ('{{URL::route($ruta["nuevopago"], array())}}?idS='+idS+'&numeroS='+numeroS, 'Registrar Pago al Medico', this);
	}

	function nuevoReporte(){
		var idS = idsSelec.join(",");
		var numeroS = numerosSelec.join(", ");
		modal ('{{URL::route($ruta["nuevoreporte"], array())}}?fecha='+$("#fechainicial").val()+'&idS='+idS+'&numeroS='+numeroS, 'Generar Reporte de Pago Pendiente al Medico', this);
	}

	function finalizarNuevoReporte(url){
		idsSelec = [];
		numerosSelec = [];
		totalesSelec = [];
		buscar('Reportepagofacturacion');
		cerrarModal();
		setTimeout(function(){ window.location = url; }, 1000);
	}

	function marcarTodos(valor){
		$(".chkSeleccionados").each(function(key,val){
			if($(val).is(":checked")!=valor){
				$(val).prop("checked",valor);
				verificarSeleccion(val);
			}
		});
	}

	function actualizarPagoCero(celda,id){
		var valoractual = $(celda).html().toString().trim();
		var iptPago = '<input type="number" id="iptPago_'+id+'" value="'+valoractual+'" dmc_id="'+id+'" step="0.01" min="0">';
		$(celda).html(iptPago);
		$("#iptPago_"+id).focus();
		$("#iptPago_"+id).select();
		$(celda).attr("ondblclick","");
		$("#iptPago_"+id).on("keypress",function(e){
			if(e.which==13){
				var valornuevo = $("#iptPago_"+id).val();
				$(celda).html(valornuevo);
				$(celda).attr("ondblclick","actualizarPagoCero(this,"+id+");");
				$.ajax({
					type:"GET",
					url:"reportepagofacturacion/actualizarpagomedico",
					data:"dmc_id="+id+"&valor="+valornuevo,
					success:function(a){
						if(a!="OK"){
							alert("NO SE PUDO ACTUALIZAR");
						}else{
							//console.log($(celda).parent(),$(celda).parent().find(".chkSeleccionados"));
							$(celda).parent().find(".chkSeleccionados").attr("data_total",valornuevo);
						}
					}
				});
				return false;
			}
		});
		//console.log(celda,id);
	}

	function activarTextarea(txtarea){
		$(txtarea).attr("ondblclick","");
		$(txtarea).prop("readonly",false);
		$(txtarea).focus();
		$(txtarea).select();
		$(txtarea).attr("onblur",'guardarComentario(this,'+$(txtarea).attr("iddet")+');');
	}

	function guardarComentario(txtarea,iddet){
		$.ajax({
	        type: "POST",
	        url: "reportepagofacturacion/comentario",
	        data: "id="+iddet+"&value="+$(txtarea).val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),
	        success: function(a) {
	            if(a!='OK'){
	            	alert('Error guardando comentario');
	            }else{
	            	$(txtarea).prop("readonly",true);
	            	$(txtarea).attr("onblur","");
	            	$(txtarea).attr("ondblclick","activarTextarea(this);");
	            }
	        }
    	});
	}

</script>