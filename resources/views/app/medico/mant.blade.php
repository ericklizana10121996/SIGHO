<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($medico, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="col-lg-6 col-md-6 col-sm-6">
		<div class="form-group">
			{!! Form::label('nombres', 'Nombres:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('nombres', null, array('class' => 'form-control input-xs', 'id' => 'nombres', 'placeholder' => 'Ingrese nombres')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('apellidopaterno', 'Apellido Paterno:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('apellidopaterno', null, array('class' => 'form-control input-xs', 'id' => 'apellidopaterno', 'placeholder' => 'Ingrese apellido paterno')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('apellidomaterno', 'Apellido Materno:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('apellidomaterno', null, array('class' => 'form-control input-xs', 'id' => 'apellidomaterno', 'placeholder' => 'Ingrese apellido materno')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('dni', 'DNI:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('dni', null, array('class' => 'form-control input-xs', 'id' => 'dni', 'placeholder' => 'Ingrese DNI')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('ruc', 'RUC:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('ruc', null, array('class' => 'form-control input-xs', 'id' => 'ruc', 'placeholder' => 'Ingrese RUC')) !!}
			</div>
		</div>
	</div>
	<div class="col-lg-6 col-md-6 col-sm-6">
	<div class="form-group">
			{!! Form::label('rne', 'RNE:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('rne', null, array('class' => 'form-control input-xs', 'id' => 'rne', 'placeholder' => 'Ingrese RNE')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('cmp', 'CMP:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('cmp', null, array('class' => 'form-control input-xs', 'id' => 'cmp', 'placeholder' => 'Ingrese CMP')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('direccion', 'Direccion:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('direccion', null, array('class' => 'form-control input-xs', 'id' => 'direccion', 'placeholder' => 'Ingrese direccion')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('telefono', 'Telefono:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('telefono', null, array('class' => 'form-control input-xs', 'id' => 'telefono', 'placeholder' => 'Ingrese telefono')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('email', 'Email:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('email', null, array('class' => 'form-control input-xs', 'id' => 'email', 'placeholder' => 'Ingrese email')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('especialidad_id', 'Especialidad:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::select('especialidad_id', $cboEspecialidad, null, array('class' => 'form-control input-xs', 'id' => 'especialidad_id')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('tipomedico', 'Tipo:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::select('tipomedico', $cboTipomedico, null, array('class' => 'form-control input-xs', 'id' => 'tipomedico')) !!}
			</div>
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
	configurarAnchoModal('720');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombres"]').focus();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').inputmask("99999999");
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').inputmask("99999999999");
}); 
</script>