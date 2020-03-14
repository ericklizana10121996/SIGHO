<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($employee, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="form-group">
		{!! Form::label('dni', 'DNI:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-5 col-md-5 col-sm-5">
			{!! Form::text('dni', null, array('class' => 'form-control input-xs', 'id' => 'dni', 'placeholder' => 'Ingrese dni')) !!}
		</div>
		<div class="col-lg-1 col-md-1 col-sm-1 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> Validar', array('class' => 'btn btn-primary btn-sm', 'id' => 'btnGuardar', 'onclick' => 'validar();')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::hidden('person_id', null, array( 'id' => 'person_id')) !!}
		{!! Form::label('name', 'Nombre:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('name', null, array('class' => 'form-control input-xs', 'id' => 'name', 'placeholder' => 'Persona', 'readonly' =>'')) !!}
		</div>
	</div>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('500');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').focus();
}); 

function validar() {
	var _token =$('input[name=_token]').val();
	var dni = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').val();
	$.post('{{ URL::route("employee.validardni")}}', {dni: dni,_token: _token} , function(data){
		//$('#divDetail').html(data);
		//calculatetotal();
		var datos = data.split('-');
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datos[0]);
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="name"]').val(datos[1]);
	});
}
</script>