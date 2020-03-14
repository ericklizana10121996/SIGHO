<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($detalle, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('id', $id, array('id' => 'id')) !!}
	<div class="form-group">
		<table class="table table-bordered table-striped table-condensed table-hover">
			<thead>
				<tr>
					<th class="text-center">NRO</th>
					<th class="text-center">DOCTOR</th>
					<th class="text-center">PACIENTE</th>
					<th class="text-center">SERVICIO</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$c=0;
				foreach ($detalle1 as $key => $value){$c=$c+1;
					echo "<tr>";
					echo "<td>".$c."</td>";
					echo "<td>".$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres."</td>";
					echo "<td>".$value->movimiento->persona->apellidopaterno.' '.$value->movimiento->persona->apellidomaterno.' '.$value->movimiento->persona->nombres."</td>";
					if($value->servicio_id>0){
						echo "<td>".$value->servicio->nombre."</td>";
					}else{
						echo "<td>".$value->descripcion."</td>";
					}
					echo "</tr>";
				}
				?>
			</tbody>
		</table>
	</div>
	<div class="form-group">
		{!! Form::label('monto', 'Monto:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('monto', 0, array('class' => 'form-control input-xs', 'id' => 'monto')) !!}
		</div>
		{!! Form::label('recibo', 'Recibo:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('recibo', '', array('class' => 'form-control input-xs', 'id' => 'recibo')) !!}
		</div>

	</div>
	<div class="form-group">
		{!! Form::label('retencion', 'Retencion:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			<input type="checkbox" onchange="calcularRetencion(this.checked)" id="chkre" class="control-label" />8%
			{!! Form::text('retencion', 0, array('class' => 'form-control input-xs', 'id' => 'retencion', 'readonly' => 'true')) !!}
		</div>
		{!! Form::label('pago', 'Pago:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('pago', 0, array('class' => 'form-control input-xs', 'id' => 'pago', 'readonly' => 'true')) !!}
		</div>
	</div>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardarPago(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('600');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
}); 

$("#monto").change(function(){
	var ret=0;
	if($("#chkre").is(":checked")){
		ret = Math.round((parseFloat($("#monto").val())*0.08)*100)/100;	
	}
	var pago = Math.round((parseFloat($("#monto").val())-ret)*100)/100;
	$("#pago").val(pago);
});

function calcularRetencion(check){
	if(check){
		var ret = Math.round((parseFloat($("#monto").val())*0.08)*100)/100;
		var pago = Math.round((parseFloat($("#monto").val())-ret)*100)/100;
		$("#retencion").val(ret);
		$("#pago").val(pago);
	}else{
		var pago = $("#monto").val();
		$("#retencion").val(0);
		$("#pago").val(pago);
	}
}
function guardarPago (entidad, idboton, entidad2) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
	var listar       = 'NO';
	if ($(idformulario + ' :input[id = "listar"]').length) {
		var listar = $(idformulario + ' :input[id = "listar"]').val()
	};
	var btn = $(idboton);
	btn.button('loading');
	data.done(function(msg) {
		respuesta = msg;
	}).fail(function(xhr, textStatus, errorThrown) {
		respuesta = 'ERROR';
	}).always(function() {
		btn.button('reset');
		if(respuesta === 'ERROR'){
		}else{
			if (respuesta === 'OK') {
				list = new Array();
				cerrarModal();
				if (listar === 'SI') {
					if(typeof entidad2 != 'undefined' && entidad2 !== ''){
						entidad = entidad2;
					}
					buscarCompaginado('', 'Accion realizada correctamente', entidad, 'OK');
				}        
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

</script>