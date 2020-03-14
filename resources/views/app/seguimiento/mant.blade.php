<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($seguimiento, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    <div class="form-group">
		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-7 col-md-7 col-sm-7">
        {!! Form::hidden('person_id', null, array('id' => 'person_id')) !!}
			{!! Form::text('paciente', null, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Paciente')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('numero', 'Historia:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
            {!! Form::hidden('historia_id', null, array('id' => 'historia_id')) !!}
			{!! Form::text('numero', null, array('class' => 'form-control input-xs', 'id' => 'numero', 'placeholder' => 'Ingrese Historia')) !!}
		</div>
        {!! Form::label('tipopaciente', 'Tipo Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::select('tipopaciente', $cboTipoPaciente, null, array('class' => 'form-control input-xs', 'id' => 'tipopaciente', 'disabled' => 'true')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('areaenvio', 'Area Solicitante:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-6 col-md-6 col-sm-6">
			{!! Form::select('areaenvio', $cboArea, null, array('class' => 'form-control input-xs', 'id' => 'areaenvio')) !!}
		</div>
	</div>
    <div class="form-group" style="display:none;">
		{!! Form::label('areadestino', 'Area Destino:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-6 col-md-6 col-sm-6">
			{!! Form::select('areadestino', $cboArea, null, array('class' => 'form-control input-xs', 'id' => 'areadestino')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('comentario', 'Comentario:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-7 col-md-7 col-sm-7">
			{!! Form::textarea('comentario', null, array('class' => 'form-control input-xs', 'id' => 'comentario')) !!}
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
	configurarAnchoModal('600');
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

	var personas2 = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit:10,
        remote: {
            url: 'historia/historiaautocompletar/%QUERY',
            filter: function (personas) {
                return $.map(personas, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                        historia: movie.numero,
                        person_id:movie.person_id,
                        tipopaciente:movie.tipopaciente,
                        plan_id:movie.plan_id,
                        plan:movie.plan,
                        coa:movie.coa,
                        deducible:movie.deducible,
                        ruc:movie.ruc,
                        direccion:movie.direccion,
                    };
                });
            }
        }
    });
    personas2.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero"]').typeahead(null,{
        displayKey: 'historia',
        source: personas2.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero"]').val(datum.historia);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
        // $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').val(datum.dni);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
    });    
    

}); 

</script>