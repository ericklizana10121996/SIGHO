<?php
if(is_null($reciboblanco)){
    $person_id=1;
    $persona=null;
    $fecha=date('Y-m-d');
    $historia_id=null;
    $historia=null;
    $tipopaciente=null;
    $doctor_id=null;
    $doctor=null;
    $concepto=null;
    $total=0;
}else{
    $person_id=$reciboblanco->persona_id;
    $persona=$reciboblanco->persona->apellidopaterno.' '.$reciboblanco->persona->apellidomaterno.' '.$reciboblanco->persona->nombres;
    $fecha=$reciboblanco->fecha;
    $historia_id=null;
    $historia=null;
    $tipopaciente=null;
    $doctor_id=$reciboblanco->doctor_id;
    $doctor=$reciboblanco->doctor->apellidopaterno.' '.$reciboblanco->doctor->apellidomaterno.' '.$reciboblanco->doctor->nombres;
    $concepto=$reciboblanco->comentario;
    $total=$reciboblanco->total;
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($reciboblanco, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
        <div class="form-group">
    		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
    		<div class="col-lg-4 col-md-4 col-sm-4">
    			{!! Form::date('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
    		</div>
            {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
    		<div class="col-lg-3 col-md-3 col-sm-3">
    			{!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
    		</div>
    	</div>
        <div class="form-group">
    		{!! Form::label('paciente', 'Persona:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
    		<div class="col-lg-8 col-md-8 col-sm-8">
            {!! Form::hidden('person_id', $person_id, array('id' => 'person_id')) !!}
    			{!! Form::text('paciente', $persona, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Persona')) !!}
    		</div>
            <div class="col-lg-1 col-md-1 col-sm-1" style="display: none;">
                {!! Form::button('<i class="fa fa-file fa-lg"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('historia.create', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Historia\', this);', 'title' => 'Nueva Historia')) !!}
    		</div>
    	</div>
        <div class="form-group" style="display: none;">
    		{!! Form::label('numero', 'Historia:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
    		<div class="col-lg-3 col-md-3 col-sm-3">
                {!! Form::hidden('historia_id', $historia_id, array('id' => 'historia_id')) !!}
    			{!! Form::text('numero_historia', $historia, array('class' => 'form-control input-xs', 'id' => 'numero_historia', 'readonly' => 'true')) !!}
    		</div>
            {!! Form::label('tipopaciente', 'Tipo Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    		<div class="col-lg-3 col-md-3 col-sm-3">
    			{!! Form::select('tipopaciente', $cboTipoPaciente, $tipopaciente, array('class' => 'form-control input-xs', 'id' => 'tipopaciente')) !!}
    		</div>
    	</div>
        <div class="form-group">
            {!! Form::label('doctor', 'Doctor:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-10 col-md-10 col-sm-10">
            {!! Form::hidden('doctor_id', $doctor_id, array('id' => 'doctor_id')) !!}
                {!! Form::text('doctor', $doctor, array('class' => 'form-control input-xs', 'id' => 'doctor')) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('concepto', 'Concepto:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-10 col-md-10 col-sm-10">
                {!! Form::text('concepto', $concepto, array('class' => 'form-control input-xs', 'id' => 'concepto')) !!}
            </div>
        </div>
        <div class="form-group">   
            <div class="col-lg-6 col-md-6 col-sm-6">
                {!! Form::label('total', 'Total:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
                <div class="col-lg-6 col-md-6 col-sm-6">
                    {!! Form::text('total', $total, array('class' => 'form-control input-xs', 'id' => 'total')) !!}
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 text-right">
                {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardarPago(\''.$entidad.'\', this);')) !!}
                {!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
            </div>
         </div>       
{!! Form::close() !!}
<script type="text/javascript">
var valorbusqueda="";
$(document).ready(function() {
	configurarAnchoModal('550');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
	var personas = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit:10,
		remote: {
			url: 'caja/personautocompletar/%QUERY',
			filter: function (personas) {
				return $.map(personas, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
                        //historia: movie.numero,
                        person_id:movie.id,
                        //tipopaciente:movie.tipopaciente,
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
		//$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(datum.id);
       // $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').val(datum.historia);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
        /*if(datum.tipopaciente=="Hospital"){
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val("Particular");
        }else{
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val(datum.tipopaciente);
        } */   
        
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);
	});
    
    var personas3 = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'medico/medicoautocompletar/%QUERY',
            filter: function (personas) {
                return $.map(personas, function (movie) {
                    return {
                        value: movie.value,
                        person_id:movie.id,
                    };
                });
            }
        }
    });
    personas3.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor"]').typeahead(null,{
        displayKey: 'value',
        source: personas3.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doctor_id"]').val(datum.person_id);
    });
}); 

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
		  //alert(respuesta);
            var dat = JSON.parse(respuesta);
			if (dat[0]!==undefined && (dat[0].respuesta=== 'OK')) {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').val(dat[0].historia);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val(dat[0].tipopaciente);
                alert('Historia Generada');
                window.open("historia/pdfhistoria?id="+dat[0].id,"_blank");
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

function guardarPago (entidad, idboton) {
    var band=true;
    var msg="";
    /*if($("#person_id").val()==""){
        band = false;
        msg += " *No se selecciono una persona \n";    
    }*/ 
    if($("#doctor_id").val()==""){
        band = false;
        msg += " *No se selecciono un doctor \n";    
    }     
    if(band){
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
    		  //alert(respuesta);
                var dat = JSON.parse(respuesta);
                if(dat[0]!==undefined){
                    resp=dat[0].respuesta;    
                }else{
                    resp='VALIDACION';
                }
                
    			if (resp === 'OK') {
    				cerrarModal();
                    buscarCompaginado('', 'Accion realizada correctamente', entidad, 'OK');
                    window.open('/juanpablo/reciboblanco/pdfRecibo?id='+dat[0].id,'_blank')
    			} else if(resp === 'ERROR') {
    				alert(dat[0].msg);
    			} else {
    				mostrarErrores(respuesta, idformulario, entidad);
    			}
    		}
    	});
    }else{
        alert("Corregir los sgtes errores: \n"+msg);
    }
}

</script>