<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($habitacion, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
        <div class="form-group">
    		{!! Form::label('nombre', 'Nombre:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-7 col-md-7 col-sm-7">
    			{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre')) !!}
    		</div>
    	</div>
        <div class="form-group">
            {!! Form::label('sexo', 'Sexo:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-3 col-md-3 col-sm-3">
    			{!! Form::select('sexo', $cboSexo, null, array('class' => 'form-control input-xs', 'id' => 'sexo')) !!}
    		</div>
    	</div>
    	<div class="form-group">
            {!! Form::label('tipohabitacion_idtipohabitacion_id', 'Tipo Hab.:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-3 col-md-3 col-sm-3">
    			{!! Form::select('tipohabitacion_id', $cboTipoHabitacion, null, array('class' => 'form-control input-xs', 'id' => 'tipohabitacion_id')) !!}
    		</div>
    	</div>
        <div class="form-group">
            {!! Form::label('piso_id', 'Piso:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-3 col-md-3 col-sm-3">
    			{!! Form::select('piso_id', $cboPiso, null, array('class' => 'form-control input-xs', 'id' => 'piso_id')) !!}
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
	var personas = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
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
					};
				});
			}
		}
	});
	personas.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').typeahead(null,{
		displayKey: 'value',
		source: personas.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero"]').val(datum.historia);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val(datum.tipopaciente);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);
	});
}); 

function seleccionarMedico(idmedico){
    $.ajax({
        type: "POST",
        url: "medico/seleccionarMedico",
        data: "idmedico="+idmedico+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            data = JSON.parse(a);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor"]').val(data[0].medico);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="especialidad"]').val(data[0].especialidad);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor_id"]').val(data[0].id);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} td[id="tdFecha"]').html(data[0].fecha);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} td[id="tdObservacion"]').html(data[0].observacion);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} td[id="tdHorario"]').html(data[0].horario);

            cerrarModal();
        }
    });
}

function guardarHistoria (entidad, idboton) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
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
		  alert(respuesta);
            var dat = JSON.parse(respuesta);
            resp=dat[0].respuesta;
			if (resp === 'OK') {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero"]').val(dat[0].historia);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(dat[0].paciente);
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}


</script>