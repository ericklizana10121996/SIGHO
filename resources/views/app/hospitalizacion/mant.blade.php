<?php
//dd($hospitalizacion->historia);
if(!is_null($hospitalizacion)){
   
    $fecha=$hospitalizacion->fecha;
    $hora=$hospitalizacion->hora;
    $numero=$hospitalizacion->historia->numero;
    $paciente=$hospitalizacion->historia->persona->apellidopaterno.' '.$hospitalizacion->historia->persona->apellidomaterno.' '.$hospitalizacion->historia->persona->nombres;
    $historia_id=$hospitalizacion->historia_id;
    $paquete=$hospitalizacion->paquete;
    if($hospitalizacion->medico_id>0){
        $medico=$hospitalizacion->medico->apellidopaterno.' '.$hospitalizacion->medico->apellidomaterno.' '.$hospitalizacion->medico->nombres;
        $medico_id=$hospitalizacion->medico_id;
    }else{
        $medico=null;
        $medico_id=null;        
    }
    $cboSituacion = array('Seleccione' => 'Seleccione','Convenio'=>'Convenio', 'Particular' => 'Particular','Hospital' => 'Hospital');

    if(!is_null($tipo_paciente)){
        $situacion = $tipo_paciente;
    }else{
        $situacion = $hospitalizacion->historia->tipopaciente;
    }
}else{
    $fecha=date("Y-m-d");
    $hora=date("H:i");
    $numero=null;
    $paciente=null;
    $historia_id=null;
    $paquete=null;
    $medico=null;
    $medico_id=null;
    $cboSituacion = array('Seleccione' => 'Seleccione','Convenio'=>'Convenio', 'Particular' => 'Particular', 'Hospital' => 'Hospital');
    $situacion = null;
}

?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($hospitalizacion, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
         <div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::date('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
        		</div>
        		{!! Form::label('hora', 'Hora:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::time('hora', $hora, array('class' => 'form-control input-xs', 'id' => 'hora')) !!}
        		</div>
                {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
        		</div>
        	</div>
            <div class="form-group">
        		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-7 col-md-7 col-sm-7">
                    {!! Form::hidden('historia_id', $historia_id, array('id' => 'historia_id')) !!}
        			{!! Form::text('paciente', $paciente, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Paciente')) !!}
        		</div>
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::button('<i class="fa fa-file fa-lg"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('historia.create', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Historia\', this);', 'title' => 'Nueva Historia')) !!}
        		</div>
        	</div>

            <div class="form-group">
                {!! Form::label('situacion', 'Situación Actual:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                  {!! Form::select('situacion', $cboSituacion , $situacion, array('class' => 'form-control input-xs', 'id' => 'situacion_id','disabled' => 'true')) !!}
                </div>
            </div>  

            <div class="form-group">
          		{!! Form::label('habitacion', 'Habitacion:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
                    @if(is_null($hospitalizacion))
                        {!! Form::hidden('habitacion_id', $habitacion->id, array('id' => 'habitacion_id')) !!}                    
        			    {!! Form::text('habitacion', $habitacion->nombre, array('class' => 'form-control input-xs', 'id' => 'habitacion', 'readonly' => 'true')) !!}
                    @else
                        {!! Form::select('habitacion_id', $cboHabitacion , $habitacion->id, array('class' => 'form-control input-xs', 'id' => 'habitacion_id')) !!}
                    @endif
           		</div>
                {!! Form::label('modo', 'Modo Ingreso:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-6 col-md-6 col-sm-6">
                    {!! Form::select('modo', $cboModo , null, array('class' => 'form-control input-xs', 'id' => 'modo')) !!}
                </div>
            </div>
            <div class="form-group">
          		{!! Form::label('paquete', 'Paquete:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">                    
        			{!! Form::select('paquete', $cboPaquete , $paquete, array('class' => 'form-control input-xs', 'id' => 'paquete')) !!}
           		</div>
                {!! Form::label('medico', 'Medico:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-6 col-md-6 col-sm-6">
                    {!! Form::hidden('medico_id', $medico_id, array('id' => 'medico_id')) !!}
        			{!! Form::text('medico', $medico, array('class' => 'form-control input-xs', 'id' => 'medico')) !!}
                </div>
            </div>
{{--             <div class="form-group">
                {!! Form::label('convenio', 'Convenio:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-10 col-md-10 col-sm-10">
                    {!! Form::hidden('convenio_id', null, array('id' => 'convenio_id')) !!}
                    {!! Form::text('convenio', null, array('class' => 'form-control input-xs', 'id' => 'convenio', 'placeholder' => 'Ingrese Convenio')) !!}
                </div>
            </div> --}}
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
	configurarAnchoModal('650');
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
                        plan_id:movie.plan_id
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
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="situacion_id"]').val(datum.tipopaciente);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero"]').val(datum.historia);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
	});
	var medico = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'medico/medicoautocompletar/%QUERY',
			filter: function (medicos) {
				return $.map(medicos, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
					};
				});
			}
		}
	});
	medico.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="medico"]').typeahead(null,{
		displayKey: 'value',
		source: medico.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="medico_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="medico"]').val(datum.value);
	});

    var convenio = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'convenio/convenioautocompletar/%QUERY',
            filter: function (convenios) {
                return $.map(convenios, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                    };
                });
            }
        }
    });
    convenio.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="convenio"]').typeahead(null,{
        displayKey: 'value',
        source: convenio.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="convenio_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="convenio"]').val(datum.value);
    });

    var sit =  $('#situacion_id').val();
    if(sit != 'Seleccione'){
        $('#situacion_id').attr('disabled',false);
    }else{
        $('#situacion_id').attr('disabled',true);    
    }
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

function seleccionarConvenio(idconvenio){
    $.ajax({
        type: "POST",
        url: "convenio/seleccionarConvenio",
        data: "idconvenio="+idmedico+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            data = JSON.parse(a);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="convenio"]').val(data[0].convenio);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="convenio_id"]').val(data[0].id);
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