<?php
if(is_null($salaoperacion)){
    $doctor_id=null;
    $doctor=null;
    $especialidad=null;
    $historia=null;
    $historia_id=null;
    $paciente=null;
    $person_id=null;
    $inicio=date('G:i');
    $fin=date('G:i');
    $tipopaciente=null;
    $tipohabitacion=null;
    $sala_id=null;
    $fecha=date('Y-m-d');
    $paquete=null;
    $tiempo=null;
    $arcoenc = "N";
}else{
    $doctor_id=$salaoperacion->medico_id;
    $arcoenc = $salaoperacion->arcoenc;
    $doctor=$salaoperacion->medico->apellidopaterno.' '.$salaoperacion->medico->apellidomaterno.' '.$salaoperacion->medico->nombres;
    $especialidad=$salaoperacion->medico->especialidad->nombre;
    if($salaoperacion->historia_id>0){
        $historia_id=$salaoperacion->historia_id;
        $historia=$salaoperacion->historia->numero;
        $paciente=$salaoperacion->historia->persona->apellidopaterno.' '.$salaoperacion->historia->persona->apellidomaterno.' '.$salaoperacion->historia->persona->nombres;
        $person_id=$salaoperacion->historia->person_id;
        $tipopaciente=$salaoperacion->historia->tipopaciente;
    }else{
        $historia=null;
        $historia_id=null;
        $paciente=null;
        $person_id=null;
        $tipopaciente=null;
    }
    $tiempo=$salaoperacion->tiempo;
    $inicio=$salaoperacion->horainicio;
    $fin=$salaoperacion->horafin;
    $tipohabitacion=$salaoperacion->tipohabitacion_id;
    $sala_id=$salaoperacion->sala_id;
    $fecha=$salaoperacion->fecha;
    $paquete=$salaoperacion->paquete;
}
if($user->usertype_id==7 || $user->usertype_id==1){
    $fecha2="1990-01-01";
}else{
    $fecha2=$fecha;
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($salaoperacion, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
        <div class="form-group">
    		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-4 col-md-4 col-sm-4">
    			{!! Form::date('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'fecha', 'min' => $fecha2)) !!}
    		</div>
            {!! Form::label('sala', 'Sala:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
    		<div class="col-lg-3 col-md-3 col-sm-3">
    			{!! Form::select('sala', $cboSala, $sala_id, array('class' => 'form-control input-xs', 'id' => 'sala')) !!}
    		</div>
    	</div>
        <div class="form-group">
    		{!! Form::label('doctor', 'Doctor:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-7 col-md-7 col-sm-7">
                {!! Form::hidden('doctor_id', $doctor_id, array('id' => 'doctor_id')) !!}
    			{!! Form::text('doctor', $doctor, array('class' => 'form-control input-xs', 'id' => 'doctor')) !!}
            </div>
    	</div>
        <div class="form-group">
    		{!! Form::label('especialidad', 'Especialidad:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-7 col-md-7 col-sm-7">
    			{!! Form::text('especialidad', $especialidad, array('class' => 'form-control input-xs', 'id' => 'especialidad', 'readonly','true')) !!}
    		</div>
    	</div>
        <div class="form-group">
    		{!! Form::label('anestesiologo', 'Anestesiologo:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-7 col-md-7 col-sm-7">
    			{!! Form::text('anestesiologo', null, array('class' => 'form-control input-xs', 'id' => 'anestesiologo')) !!}
    		</div>
    	</div>
        <div class="form-group">
    		{!! Form::label('instrumentista', 'Instrumentista:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-7 col-md-7 col-sm-7">
    			{!! Form::text('instrumentista', null, array('class' => 'form-control input-xs', 'id' => 'instrumentista')) !!}
    		</div>
    	</div>
        <div class="form-group">
    		{!! Form::label('ayudante1', 'Ayudante 1:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-7 col-md-7 col-sm-7">
    			{!! Form::text('ayudante1', null, array('class' => 'form-control input-xs', 'id' => 'ayudante1')) !!}
    		</div>
    	</div>
        <div class="form-group">
    		{!! Form::label('ayudante2', 'Ayudante 2:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-7 col-md-7 col-sm-7">
    			{!! Form::text('ayudante2', null, array('class' => 'form-control input-xs', 'id' => 'ayudante2')) !!}
    		</div>
    	</div>
        <div class="form-group">
            {!! Form::label('responsable', 'Responsable:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
            <div class="col-lg-7 col-md-7 col-sm-7">
                {!! Form::text('responsable', null, array('class' => 'form-control input-xs', 'id' => 'resposanble')) !!}
            </div>
        </div>
        <div class="form-group">
    		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-7 col-md-7 col-sm-7">
            {!! Form::hidden('person_id', $person_id, array('id' => 'person_id')) !!}
    			{!! Form::text('paciente', $paciente, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Paciente')) !!}
    		</div>
            <div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::button('<i class="fa fa-file fa-lg"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('historia.create', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Historia\', this);', 'title' => 'Nueva Historia')) !!}
    		</div>
    	</div>
        <div class="form-group">
    		{!! Form::label('numero', 'Historia:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-3 col-md-3 col-sm-3">
                {!! Form::hidden('historia_id', $historia_id, array('id' => 'historia_id')) !!}
    			{!! Form::text('numero', $historia, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
    		</div>
            {!! Form::label('tipopaciente', 'Tipo Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-3 col-md-3 col-sm-3">
    			{!! Form::select('tipopaciente', $cboTipoPaciente, $tipopaciente, array('class' => 'form-control input-xs', 'id' => 'tipopaciente', 'readonly' => 'true')) !!}
    		</div>
    	</div>
    	<div class="form-group">
    		{!! Form::label('horainicio', 'Inicio:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-3 col-md-3 col-sm-3">
    			{!! Form::time('horainicio', $inicio, array('class' => 'form-control input-xs', 'id' => 'horainicio')) !!}
    		</div>
    		{!! Form::label('tiempo', 'Fin:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-3 col-md-3 col-sm-3">
    			{!! Form::time('horafin', $fin, array('class' => 'form-control input-xs', 'id' => 'horafin')) !!}
    		</div>

            {!! Form::hidden('tiempo', $tiempo, array('class' => 'form-control input-xs', 'id' => 'tiempo')) !!}

    	</div>
        <div class="form-group">
            {!! Form::label('paquete', 'Paquete:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
            <div class="col-lg-3 col-md-3 col-sm-3">
                {!! Form::select('paquete', $cboPaquete, $paquete, array('class' => 'form-control input-xs', 'id' => 'paquete')) !!}
            </div>
            {!! Form::label('tipohabitacion', 'Tipo Hab.:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
            <div class="col-lg-3 col-md-3 col-sm-3">
                {!! Form::select('tipohabitacion', $cboTipoHabitacion, $tipohabitacion, array('class' => 'form-control input-xs', 'id' => 'tipohabitacion')) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('arcoenc', 'Arco en C:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
            <div class="col-lg-8 col-md-8 col-sm-8">
                <input type="checkbox" name="arcoenc" value="S" @if($arcoenc=="S") checked="" @endif>
            </div>
        </div>
        <div class="form-group">
    		{!! Form::label('operacion', 'Operacion:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-8 col-md-8 col-sm-8">
    			{!! Form::textarea('operacion', null, array('class' => 'form-control input-xs', 'id' => 'operacion', 'cols' => '4' , 'rows' => '2')) !!}
    		</div>
    	</div>
    	<div class="form-group">
    		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
    			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar2(\''.$entidad.'\', this)')) !!}
    			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
    		</div>
    	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {

    // if({!! $inicio !!} && {!! $fin !!}){
    //     $('#horainicio').attr('disabled',true);
    //     $('#horafin').attr('disabled',true);
    // }else{
    //     $('#horainicio').attr('disabled',false);
    //     $('#horafin').attr('disabled',false);    
    // }

	configurarAnchoModal('600');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
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

    var doctor = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        limit: 10,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'medico/medicoautocompletar/%QUERY',
            filter: function (doctores) {
                return $.map(doctores, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                        especialidad: movie.especialidad,
                    };
                });
            }
        }
    });
    doctor.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor"]').typeahead(null,{
        displayKey: 'value',
        source: doctor.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="especialidad"]').val(datum.especialidad);
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

function guardar2 (entidad, idboton) {
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
            var dat = JSON.parse(respuesta);
            if(dat[0]!==undefined){
                resp=dat[0].respuesta;    
            }else{
                resp='VALIDACION';
            }
            if (resp === 'OK') {
                cerrarModal();
                buscarCompaginado('', 'Accion realizada correctamente', entidad, 'OK');
            }else if(resp === 'ERROR') {
                alert(dat[0].msg);
            }else {
                mostrarErrores(respuesta, idformulario, entidad);
            }
        }
    });
}

</script>