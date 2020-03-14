<?php
$doctor=$detalle->persona->apellidopaterno.' '.$detalle->persona->apellidomaterno.' '.$detalle->persona->nombres;
$paciente=$detalle->movimiento->persona->apellidopaterno.' '.$detalle->movimiento->persona->apellidomaterno.' '.$detalle->movimiento->persona->nombres;
$recibo=$detalle->recibo;	
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($detalle, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('id', $detalle->id, array('id' => 'id')) !!}
	<div class="form-group">
		{!! Form::label('doctor', 'Doctor:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('doctor', $doctor, array('class' => 'form-control input-xs', 'id' => 'doctor', 'readonly' => 'true')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('paciente', $paciente, array('class' => 'form-control input-xs', 'id' => 'paciente', 'readonly' => 'true')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('recibo', 'Recibo:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('recibo', $recibo, array('class' => 'form-control input-xs', 'id' => 'recibo')) !!}
		</div>
	</div>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this, \'\',\''."actualizarVista(respuesta,".$detalle->id.");".'\')')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('450');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
});

</script>