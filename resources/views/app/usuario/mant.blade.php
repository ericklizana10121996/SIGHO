<?php 
$nombrepersona = NULL;
if (!is_null($usuario)) {
	$persona = $usuario->person;
	$nombrepersona = trim($persona->lastname.' '.$persona->apellidopaterno.' '.$persona->apellidomaterno.', '.trim($persona->firstname.' '.$persona->nombres));
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($usuario, $formData) !!}
{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
<div class="form-group">
	{!! Form::label('usertype_id', 'Tipo de usuario:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
	<div class="col-lg-8 col-md-8 col-sm-8">
		{!! Form::select('usertype_id', $cboTipousuario, null, array('class' => 'form-control input-xs', 'id' => 'usertype_id')) !!}
	</div>
</div>
<div class="form-group">
	{!! Form::label('nombrepersona', 'Persona:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
	{!! Form::hidden('person_id', null, array('id' => 'person_id')) !!}
	<div class="col-lg-8 col-md-8 col-sm-8">
		@if(!is_null($usuario))
		{!! Form::text('nombrepersona', $nombrepersona, array('class' => 'form-control input-xs', 'id' => 'nombrepersona', 'placeholder' => 'Seleccione persona')) !!}
		@else
		{!! Form::text('nombrepersona', $nombrepersona, array('class' => 'form-control input-xs', 'id' => 'nombrepersona', 'placeholder' => 'Seleccione persona')) !!}
		@endif
	</div>
</div>
<div class="form-group">
	{!! Form::label('login', 'Usuario:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
	<div class="col-lg-8 col-md-8 col-sm-8">
		{!! Form::text('login', null, array('class' => 'form-control input-xs', 'id' => 'login', 'placeholder' => 'Ingrese login')) !!}
	</div>
</div>
<div class="form-group">
	{!! Form::label('password', 'Contraseña:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
	<div class="col-lg-8 col-md-8 col-sm-8">
		{!! Form::password('password', array('class' => 'form-control input-xs', 'id' => 'password', 'placeholder' => 'Ingrese contraseña')) !!}
	</div>
</div>
<div class="form-group">
	<div class="col-lg-12 col-md-12 col-sm-12 text-right">
		{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
		&nbsp;
		{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
	</div>
</div>
{!! Form::close() !!}
<script type="text/javascript">
	$(document).ready(function() {
		init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="usertype_id"]').focus();
		configurarAnchoModal('400');
		var personas = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'person/employeesautocompleting/%QUERY',
				filter: function (personas) {
					return $.map(personas, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		personas.initialize();
		$('#nombrepersona').typeahead(null,{
			displayKey: 'value',
			source: personas.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#person_id').val(datum.id);
		});
	}); 
</script>