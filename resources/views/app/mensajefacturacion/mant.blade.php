<?php
if($mensaje!=null){
    $historia=$mensaje->historia->numero;
    $person_id=$mensaje->person_id;
    $person=$mensaje->historia->persona->apellidopaterno.' '.$mensaje->historia->persona->apellidomaterno.' '.$mensaje->historia->persona->nombres;
    $convenio_id=$mensaje->historia->convenio_id;
}else{
    $historia=null;
    $person_id=null;
    $person=null;
    $convenio_id=0;
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($mensaje, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="form-group">
		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
        {!! Form::hidden('person_id', $person_id, array('id' => 'person_id')) !!}
			{!! Form::text('paciente', $person, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Paciente')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('numero', 'Historia:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
            {!! Form::hidden('historia_id', null, array('id' => 'historia_id')) !!}
			{!! Form::text('numero', $historia, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
		</div>
        {!! Form::label('tipopaciente', 'Tipo Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::select('tipopaciente', $cboTipoPaciente, null, array('class' => 'form-control input-xs', 'id' => 'tipopaciente' , 'readonly' => 'true')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('convenio_id', 'Convenio:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::select('convenio_id', $cboPlan, $convenio_id, array('class' => 'form-control input-xs', 'id' => 'convenio_id')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('mensaje', 'Mensaje:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::textarea('mensaje', null, array('class' => 'form-control input-xs', 'id' => 'mensaje', 'placeholder' => 'Ingrese mensaje')) !!}
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
	configurarAnchoModal('400');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
	var personas = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		limit: 10,
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'historia/personautocompletar/%QUERY',
			filter: function (personas) {
				return $.map(personas, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
                        historia: movie.numero,
                        person_id:movie.person_id,
                        tipopaciente:movie.tipopaciente,
                        telefono:movie.telefono,
                        convenio_id:movie.convenio_id,
					};
				});
			}
		}
	});
	personas.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').typeahead(null,{
		displayKey: 'value',
		limit: 10,
		source: personas.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero"]').val(datum.historia);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val(datum.tipopaciente);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="telefono"]').val(datum.telefono);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="convenio_id"]').val(datum.convenio_id);
	});
}); 
</script>