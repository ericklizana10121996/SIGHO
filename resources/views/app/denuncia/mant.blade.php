<?php
if(is_null($denuncia)){
    $paciente="";
    $doc="";
}else{
    $paciente=$denuncia->historia->persona->apellidopaterno.' '.$denuncia->historia->persona->apellidomaterno.' '.$denuncia->historia->persona->nombres;
    if(!is_null($denuncia->docgarantia)){
        $doc=$denuncia->docgarantia->numero.' / '.$denuncia->docgarantia->total;
    }else{
        $doc="";
    }
}
?>
<style>
.tr_hover{
	color:red;
}
.form-group{
    margin-bottom: 8px !important;
}
</style>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($denuncia, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    <div class="row">
        <div class="form-group">
            {!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
    		<div class="col-lg-9 col-md-9 col-sm-9">
                {!! Form::hidden('historia_id', null, array('id' => 'historia_id')) !!}
    			{!! Form::text('paciente', $paciente, array('class' => 'form-control input-xs', 'id' => 'paciente')) !!}
    		</div>
        </div>
        <div class="form-group">
            {!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-3 col-md-3 col-sm-3">
                {!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
            </div>
            {!! Form::label('seguro', 'Seguro:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-4 col-md-4 col-sm-4">
                {!! Form::text('seguro', null, array('class' => 'form-control input-xs', 'id' => 'seguro', 'onkeypress' => '')) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('placa', 'Placa:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-2 col-md-2 col-sm-2">
                {!! Form::text('placa', null, array('class' => 'form-control input-xs', 'id' => 'placa', 'onkeypress' => '')) !!}
            </div>
            {!! Form::label('garantia', 'Garantia:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-2 col-md-2 col-sm-2">
                {!! Form::hidden('garantia', null, array('id' => 'garantia')) !!}
                {!! Form::text('doc', $doc, array('class' => 'form-control input-xs', 'id' => 'doc')) !!}
            </div>
            {!! Form::label('denuncia', 'Denuncia:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-1 col-md-1 col-sm-1">
                {!! Form::select('denuncia', array("N"=>"No","S"=>"Si"), null, array('class' => 'form-control input-xs', 'id' => 'denuncia', 'style'=>'width:50px')) !!}
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-8 col-md-8 col-sm-8 text-right">
                {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-xs', 'id' => 'btnGuardar', 'onclick' => 'guardarDenuncia(\''.$entidad.'\', this)')) !!}
                {!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-xs', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
            </div>
        </div>
    </div>
{!! Form::close() !!}
<script type="text/javascript">
var valorbusqueda="";
$(document).ready(function() {
	configurarAnchoModal('650');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="garantia"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    
   	var personas = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit:10,
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
                        dni:movie.dni,
                        fallecido:movie.fallecido,
                        convenio:movie.convenio,
                        placa:movie.placa,
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
        if(datum.fallecido=='S'){
            alert('No puede elegir paciente fallecido');
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val('');
        }else{
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(datum.id);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="placa"]').val(datum.placa);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="seguro"]').val(datum.convenio);
            buscarGarantia(datum.person_id);
        }
    });

    var garantia = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit:10,
        remote: {
            url: 'denuncia/denunciaautocompletar/%QUERY',
            filter: function (garantia) {
                return $.map(garantia, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                    };
                });
            }
        }
    });
    garantia.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doc"]').typeahead(null,{
        displayKey: 'value',
        source: garantia.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="garantia"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doc"]').val(datum.value);
    });
    
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').focus();

}); 

function guardarDenuncia (entidad, idboton) {
    var band=true;
    var msg="";
    /*if($("#garantia").val()==""){
        band = false;
        msg += " *No se ingreso una garantia \n";    
    }*/
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

function buscarGarantia(id){
    $.ajax({
        type: "POST",
        url: "denuncia/buscarGarantia",
        data: "id="+id+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            eval(a);
            if(vmsg=="SI"){
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="garantia"]').val(vidmovimiento);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="doc"]').val(vnumero);         
            }
        }
    });
}



</script>