<?php
if($cita!=null){
    $doctor_id=$cita->doctor_id;
    $doctor=$cita->doctor->apellidopaterno." ".$cita->doctor->apellidomaterno." ".$cita->doctor->nombres;
    $especialidad=$cita->doctor->especialidad->nombre;
    $historia=$cita->historia;
    $inicio=$cita->horainicio;
    $fin=$cita->horafin;
    $person_id=$cita->paciente_id;
    if(empty($person_id)){
        $person_id = $cita->histori2->person_id;
        //echo json_encode($cita->histori2);
    }
    $movimiento_id = $cita->movimiento_id;
    if(!empty($cita->movimiento)){
        $boleta = $cita->movimiento->serie."-".$cita->movimiento->numero;
    }else{
        $boleta = "";
    }
}else{
    $doctor_id=null;
    $doctor=null;
    $especialidad=null;
    $tarifario_id=null;
    $historia=null;
    $person_id=null;
    $inicio=date("H:i");
    $fin=date("H:i");
    $movimiento_id = "";
    $boleta = "";
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($cita, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-4 col-md-4 col-sm-4">
        			{!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha', 'onchange' => 'cargarCitas($(\'#doctor_id\').val())')) !!}
        		</div>
        	</div>
            <div class="form-group">
        		{!! Form::label('doctor', 'Doctor:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-8 col-md-8 col-sm-8">
                    {!! Form::hidden('doctor_id', $doctor_id, array('id' => 'doctor_id')) !!}
        			{!! Form::text('doctor', $doctor, array('class' => 'form-control input-xs', 'id' => 'doctor')) !!}
                </div>
                <div class="col-lg-1 col-md-1 col-sm-1" style="display: none;">
                    {!! Form::button('<i class="fa fa-search fa-lg"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('medico.lista', array('listar'=>'SI')).'\', \'Lista de Doctores\', this);', 'title' => 'Buscar Doctor')) !!}
        		</div>
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::button('<i class="fa fa-search fa-lg"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modalHorario (\''.URL::route('horario.index', array('listar'=>'SI')).'\', \'Horarios\', this);', 'title' => 'Horarios del Doctor')) !!}
                </div>
        	</div>
            <div class="form-group">
        		{!! Form::label('especialidad', 'Especialidad:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-8 col-md-8 col-sm-8">
        			{!! Form::text('especialidad', $especialidad, array('class' => 'form-control input-xs', 'id' => 'especialidad', 'readonly','true')) !!}
        		</div>
        	</div>
            <div class="form-group">
                {!! Form::label('boleta', 'Boleta:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                <div class="col-lg-7 col-md-7 col-sm-7">
                {!! Form::hidden('movimiento_id', $movimiento_id, array('id' => 'movimiento_id')) !!}
                    {!! Form::text('boleta', $boleta, array('class' => 'form-control input-xs', 'id' => 'boleta', 'placeholder' => 'Ingrese Boleta de pago')) !!}
                </div>
            </div>
            <div class="form-group">
        		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-7 col-md-7 col-sm-7">
                {!! Form::hidden('person_id', $person_id, array('id' => 'person_id')) !!}
        			{!! Form::text('paciente', null, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Paciente')) !!}
        		</div>
        	</div>
            <div class="form-group">
        		{!! Form::label('numero', 'Historia:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::hidden('historia_id', null, array('id' => 'historia_id')) !!}
        			{!! Form::text('numero', $historia, array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
        		</div>
                {!! Form::label('tipopaciente', 'Tipo Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::select('tipopaciente', $cboTipoPaciente, null, array('class' => 'form-control input-xs', 'id' => 'tipopaciente')) !!}
        		</div>
        	</div>
            <div class="form-group">
        		{!! Form::label('telefono', 'Telefono:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-8 col-md-8 col-sm-8">
        			{!! Form::text('telefono', null, array('class' => 'form-control input-xs', 'id' => 'telefono', 'placeholder' => 'Ingrese Telefono')) !!}
        		</div>
        	</div>
        	<div class="form-group">
        		{!! Form::label('horainicio', 'Inicio:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::time('horainicio', $inicio, array('class' => 'form-control input-xs', 'id' => 'horainicio')) !!}
        		</div>
        		{!! Form::label('horafin', 'Fin:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::time('horafin', $fin, array('class' => 'form-control input-xs', 'id' => 'horafin')) !!}
        		</div>
        	</div>
            <div class="form-group">
        		{!! Form::label('comentario', 'Concepto:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-8 col-md-8 col-sm-8">
        			{!! Form::textarea('comentario', null, array('class' => 'form-control input-xs', 'id' => 'comentario', 'cols' => '4' , 'rows' => '5')) !!}
        		</div>
        	</div>
        	<div class="form-group">
        		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
        			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
        			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        		</div>
        	</div>
         </div>
         <div class="col-lg-6 col-md-6 col-sm-6">
            <table class="table table-condensed">
                <tr>
                    <th style='font-size:12px'>Fecha</th>
                    <td id="tdFecha" style='font-size:12px'></td>
                </tr>
                <tr>
                    <th style='font-size:12px'>Observacion</th>
                    <td id="tdObservacion" style='font-size:12px'></td>
                </tr>
                <tr>
                    <th style='font-size:12px'>Horario</th>
                    <td id="tdHorario" style='font-size:12px'></td>
                </tr>
            </table>
            <div id="divCita"></div>
         </div>
     </div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('1000');
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
						value: movie.value2,
						id: movie.id,
                        historia: movie.numero,
                        person_id:movie.person_id,
                        tipopaciente:movie.tipopaciente,
                        telefono:movie.telefono,
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
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="telefono"]').val(datum.telefono);
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
                        fecha: movie.fecha,
                        horario: movie.horario,
                        observacion: movie.observacion,
                    };
                });
            }
        },
        cache:false,
    });
    doctor.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor"]').typeahead(null,{
        displayKey: 'value',
        source: doctor.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="especialidad"]').val(datum.especialidad);
        cargarHorario(datum.id);
        cargarCitas(datum.id);
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
            cargarCitas(idmedico);
            cerrarModal();
        }
    });
}

function cargarCitas(idmedico){
    $.ajax({
        type: "POST",
        url: "cita/cargarCitaMedico",
        data: "idmedico="+idmedico+"&fecha="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fecha"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $("#divCita").html(a);
        }
    });
}

function cargarHorario(idmedico){
    $.ajax({
        type: "POST",
        url: "medico/horario",
        data: "idmedico="+idmedico+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            console.log(a);
            datum = JSON.parse(a);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} td[id="tdFecha"]').html(datum[0].fecha);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} td[id="tdObservacion"]').html(datum[0].observacion);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} td[id="tdHorario"]').html(datum[0].horario);
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

function modalHorario (controlador, titulo) {
    if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor_id"]').val()!=""){
        controlador = controlador + '&person_id='+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor_id"]').val();
        var idContenedor = "divModal" + contadorModal;
        var divmodal     = "<div id=\"" + idContenedor + "\"></div>";
        var box          = bootbox.dialog({
            message: divmodal,
            className: 'modal' +  contadorModal,
            title: titulo,
            closeButton: false
        });
        box.prop('id', 'modal'+contadorModal);
        modales[contadorModal] = box;
        contadorModal          = contadorModal + 1;
        setTimeout(function(){
            cargarRuta(controlador, idContenedor);
        },400);
    }else{
        alert("Debe seleccionar un medico");
    }
}

$("#boleta").on('keypress',function(e){
    if(e.which==13){
        $.ajax({
            type:"GET",
            url: "cita/buscarboleta",
            data: "numero="+$("#boleta").val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                datum = JSON.parse(a);
                //console.log(datum,$("#paciente"));
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento_id"]').val(datum.persona_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="boleta"]').val(datum.boleta);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.paciente);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').focus();
                //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').trigger("keypress");
                //alert("");
                if(datum.id > 0){
                    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="boleta"]').attr("style","background-color: lightgreen;");
                }else{
                    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="boleta"]').attr("style","");
                }
            }
        });
    }
});

<?php
if($cita!=null){
    echo "cargarCitas(".$cita->doctor_id.");";
}
?>

<?php

if($cita!=null && $cita->movimiento_id>0 && !empty($cita->movimiento)){ ?>
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="boleta"]').attr("style","background-color: lightgreen;");
<?php } ?>

</script>