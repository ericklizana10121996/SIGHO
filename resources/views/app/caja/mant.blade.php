<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($caja, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('lista', '', array('id' => 'lista')) !!}
    {!! Form::hidden('caja_id', $caja2->id, array('id' => 'caja_id')) !!}
    <input type="hidden" name="carritoJSON" id="carritoJSON">
	<div class="form-group">
		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
		</div>
		{!! Form::label('numero', 'Nro:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('tipodocumento', 'Tipo:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::select('tipodocumento', $cboTipoDoc, null, array('class' => 'form-control input-xs', 'id' => 'tipodocumento', 'onchange' => 'generarConcepto(this.value);')) !!}
		</div>
		{!! Form::label('concepto', 'Concepto:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::select('concepto', $cboConcepto, null, array('class' => 'form-control input-xs', 'id' => 'concepto', 'onchange' => 'transferencia(this.value);')) !!}
		</div>
	</div>
	<div class="form-group" >
		{!! Form::label('caja', 'Caja a enviar:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label divCaja')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3 divCaja">
			{!! Form::select('caja', $cboCaja, 3, array('class' => 'form-control input-xs', 'id' => 'caja')) !!}
		</div>
        {!! Form::label('usuario', 'Mi Usuario:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label divUsuario')) !!}
        <div class="col-lg-1 col-md-1 col-sm-1">
            <input type="hidden" id="miusuario" name="miusuario" value="S" />
            <input type="checkbox" checked="" class="divUsuario" onclick="if(this.checked){$('#miusuario').val('S');}else{$('#miusuario').val('N');}" />    
        </div>
	</div>
    <div class="form-group" id="divPersona">
		{!! Form::label('persona', 'Persona:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
        {!! Form::hidden('person_id', null, array('id' => 'person_id')) !!}
			{!! Form::text('persona', null, array('class' => 'form-control input-xs', 'id' => 'persona', 'placeholder' => 'Ingrese Persona')) !!}
		</div>
    </div>
    <div class="form-group" id="divSocio" style="display: none">
        {!! Form::label('socio', 'Doc. Socio:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        <div class="col-lg-9 col-md-9 col-sm-9">
        {!! Form::hidden('socio_id', null, array('id' => 'socio_id')) !!}
            {!! Form::text('socio', null, array('class' => 'form-control input-xs', 'id' => 'socio', 'placeholder' => 'Ingrese Socio')) !!}
        </div>
    </div>
    <div class="form-group" id="divDoctor" style="display: none">
        {!! Form::label('doctor', 'Doctor:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        <div class="col-lg-9 col-md-9 col-sm-9">
        {!! Form::hidden('doctor_id', null, array('id' => 'doctor_id')) !!}
            {!! Form::text('doctor', null, array('class' => 'form-control input-xs', 'id' => 'doctor', 'placeholder' => 'Ingrese Doctor')) !!}
        </div>
    </div>

    <div class="form-group" id="divResponsable" style="display: none">
        {!! Form::label('responsable', 'Responsable:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        <div class="col-lg-9 col-md-9 col-sm-9">
            {!! Form::text('responsable', null, array('class' => 'form-control input-xs', 'id' => 'responsable', 'placeholder' => 'Ingrese Responsable')) !!}
        </div>
    </div>
	<div class="form-group" id="divDocs" style="display: none;">
		{!! Form::label('doc', 'Doctor.:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label', 'style' => 'display:none')) !!}
        <div class="col-lg-9 col-md-9 col-sm-9" style="display: none;">
            {!! Form::text('busqueda', null, array('class' => 'form-control input-xs', 'id' => 'busqueda')) !!}
        </div>
		<div class="col-lg-12 col-md-12 col-sm-12" id="divDoc" style='overflow-y:auto;max-height:400px;'>
		</div>
        <div class="col-lg-12 col-md-12 col-sm-12" id="divDoc2" style="display: none;overflow-y:auto;max-height:400px;">
		</div>
        <hr />
        <div class="col-lg-12 col-md-12 col-sm-12" id="divDetalleDoc">
            <table id="tbDetalleDoc" class="table table-bordered table-striped table-condensed table-hover">
                <thead id="tblEncabezado">
                    <tr>
                        <th class="text-center">Nro.</th>
                        <th class="text-center">Paciente</th>
                        <th class="text-center">Servicio</th>
                        <th class="text-right">Pago</th>
                        <th class="text-center">R.H.</th>
                        <th class="text-center"></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
	</div>
    <div class="form-group">
		{!! Form::label('total', 'Total:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::text('total', 0, array('class' => 'form-control input-xs', 'id' => 'total')) !!}
		</div>
        {!! Form::label('rh', 'Doc.:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label rh')) !!}
        <div class="col-lg-2 col-md-2 col-sm-2 rh">
            {!! Form::select('tipo', $cboTipo, 3, array('class' => 'form-control input-xs', 'id' => 'tipo')) !!}
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 rh">
            {!! Form::text('rh', null, array('class' => 'form-control input-xs', 'id' => 'rh')) !!}
        </div>
	</div>
    <div class="form-group">
		{!! Form::label('comentario', 'Comentario:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::textarea('comentario', null, array('class' => 'form-control input-xs', 'id' => 'comentario', 'cols' => 10 , 'rows','5')) !!}
		</div>
	</div>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'validarTransferencia(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
    <?php 
    $url = URL::route('caja.descargaadmision', array('listar'=>'SI'));
    ?> 
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('750');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    
    var personas = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
        limit: 10,
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'caja/personautocompletar/%QUERY',
			filter: function (personas) {
				return $.map(personas, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
					};
				});
			}
		}
	});
	personas.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').typeahead(null,{
		displayKey: 'value',
		source: personas.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="busqueda"]').val(datum.value);
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
    }); 

    var socio = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        limit: 10,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'medico/medicoautocompletar/%QUERY',
            filter: function (socios) {
                return $.map(socios, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                    };
                });
            }
        }
    });
    socio.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="socio"]').typeahead(null,{
        displayKey: 'value',
        source: socio.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="socio"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="socio_id"]').val(datum.id);
    });  
    
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').focus();
}); 

var carro = new Array();
function agregarDoc(id,paciente,servicio,numero,pago){
    var band = true;
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            band = false;
            break;
        }
    }    
    if(band){
        var concepto = $('#concepto').val();
        if(concepto != 35){
            var a="<tr id='tr"+id+"'><td>"+numero+"</td><td>"+paciente+"</td><td>"+servicio+"</td><td id='tdPago"+id+"'>"+pago+"</td><td><input type='text' id='txtRecibo"+id+"' name='txtRecibo"+id+"' size='8' class='form-control input-xs' /></td><td><button type='button' title='Quitar' class='btn btn-danger btn-xs' onclick=\"quitarDoc('"+id+"')\"><i class='fa fa-minus'></i></button></td></tr>";
            var total = parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val());
            total = total + parseFloat(pago);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val(total);
            carro.push(id);
            $("#tbDetalleDoc").append(a);
            if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').val()=="7"){  
                $("#doc").val('');
                $("#doc").focus();
            }else{
                $("#doc2").val('');
                $("#doc2").focus();            
            }    
        }else{
            // <td><input type='text' id='txtRecibo"+id+"' name='txtRecibo"+id+"' size='8' class='form-control input-xs' /></td>
            
            if(pago > 0){
                var a="<tr id='tr"+id+"'><td>"+numero+"</td><td>"+paciente+"</td><td>"+servicio+"</td><td id='tdPago"+id+"'>"+pago+"</td><td><button type='button' title='Quitar' class='btn btn-danger btn-xs' onclick=\"quitarDoc('"+id+"')\"><i class='fa fa-minus'></i></button></td></tr>";

                var total = parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val());
                total = total + parseFloat(pago);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val(total);
                carro.push(id);
                $("#tbDetalleDoc").append(a);
                if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').val()=="7"){  
                    $("#doc").val('');
                    $("#doc").focus();
                }else{
                    $("#doc2").val('');
                    $("#doc2").focus();            
                } 
            }else{
                var a="<tr id='tr"+id+"'><td>"+numero+"</td><td>"+paciente+"</td><td>"+servicio+"</td><td><input type='number' class='text-right' id='txtPrecio"+id+"' name='txtPrecio"+id+"' min='0' step='0.1' class='form-control input-xs' onchange=\"agregarMonto('"+id + "')\" /></td><td><button type='button' title='Quitar' class='btn btn-danger btn-xs' onclick=\"quitarDoc02('"+id+"')\"><i class='fa fa-minus'></i></button></td></tr>";

                var total = parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val());
                total = total + parseFloat(pago);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val(total);
                carro.push(id);
                $("#tbDetalleDoc").append(a);
                if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').val()=="7"){  
                    $("#doc").val('');
                    $("#doc").focus();
                }else{
                    $("#doc2").val('');
                    $("#doc2").focus();            
                } 
            }
               
        }   
    }else{
        alert("Ya agregado");
    }
}

function agregarMonto(id){

  var valor = $('#txtPrecio'+id).val();
  if(valor > 0 && valor != ''){
        var total = parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val());

        total = total + parseFloat(valor);

        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val(total);
        $('#txtPrecio'+id).attr('disabled',true);   
  }

}

function quitarDoc(id){
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
    var total = parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val());
    total = total - parseFloat($("#tdPago"+id).html());
    $("#doc").val('');
    $("#tr"+id).remove();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val(total);
}


function quitarDoc02(id){
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
    var total = parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val());
    total = total - parseFloat($("#txtPrecio"+id).val());
    $("#doc").val('');
    $("#tr"+id).remove();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val(total);

}

function validarFormaPago(forma){
    if(forma=="Tarjeta"){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} div[id="divTarjeta"]').css("display","");
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} div[id="divTarjeta"]').css("display","none");
    }
}

function generarConcepto(valor){
    $.ajax({
        type: "POST",
        url: "caja/generarConcepto",
        data: "tipodocumento_id="+valor+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').html(a);
            generarNumero(valor);
            transferencia($("#concepto").val());
        }
    });
}

function generarNumero(valor){
    $.ajax({
        type: "POST",
        url: "caja/generarNumero",
        data: "tipodocumento_id="+valor+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="numero"]').val(a);
        }
    });    
}

function transferencia(valor){
    if(valor==7 || valor==8 || valor==16 || valor==14 || valor==18 || valor==20 || valor==31 || valor==45 || valor == 35){ 
        if(valor==7){//TRANSFERENCIA
            $("#divDocs").css("display","");
            $("#divPersona").css("display","none");
            $(".divCaja").css("display","");
            $(".divUsuario").css("display","");
            $("#divDoc").css("display","");
            $("#divDoc2").css("display","none");
            $("#divSocio").css("display","none");
            $('#doctor').off('keyup');
            $("#doctor").keyup(function (e) {
    			var key = window.event ? e.keyCode : e.which;
    			if (key == '13') {
    				buscarTransferencia();
    			}
    		});
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').attr("readonly","true");
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="caja"]').val(3);
        }else if(valor==8 || valor==45 || valor == 35){//PAGO A DOCTOR
            $("#divDocs").css("display","");
            $("#divPersona").css("display","none");
            $(".divCaja").css("display","none");
            $(".divUsuario").css("display","");
            $("#divDoc").css("display","none");
            $("#divDoc2").css("display","");


            $("#divSocio").css("display","none");
            $('#doctor').off('keyup');
            $("#doctor").keyup(function (e) {
    			var key = window.event ? e.keyCode : e.which;
    			if (key == '13') {
    				buscarPago();
    			}
    		});
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').attr("readonly","true");
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="caja"]').val(3);
        }else if(valor==16){//TRANSFERENCIA SOCIO
            $("#divDocs").css("display","");
            $("#divPersona").css("display","none");
            $(".divCaja").css("display","");
            $(".divUsuario").css("display","");
            $("#divDoc").css("display","");
            $("#divSocio").css("display","");
            $("#divDoc2").css("display","none");
            $('#doctor').off('keyup');
            $("#doctor").keyup(function (e) {
                var key = window.event ? e.keyCode : e.which;
                if (key == '13') {
                    buscarTransferenciaSocio();
                }
            });
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').attr("readonly","true");
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="caja"]').val(3);
        }else if(valor==14){//TRANSFERENCIA TARJETA
            $("#divDocs").css("display","");
            $("#divPersona").css("display","none");
            $(".divCaja").css("display","");
            $(".divUsuario").css("display","");
            $("#divDoc").css("display","");
            $("#divSocio").css("display","none");
            $("#divDoc2").css("display","none");
            $('#doctor').off('keyup');
            $("#doctor").keyup(function (e) {
                var key = window.event ? e.keyCode : e.which;
                if (key == '13') {
                    buscarTransferenciaTarjeta();
                }
            });
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').attr("readonly","true");
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="caja"]').val(3);
        }else if(valor==18){//ATENCION POR CONVENIO
            $("#divDocs").css("display","none");
            $("#divPersona").css("display","");
            $(".divCaja").css("display","");
            $("#divDoc").css("display","none");
            $("#divSocio").css("display","none");
            $("#divDoc2").css("display","none");
            $('#doctor').off('keyup');
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').removeAttr("readonly");
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="caja"]').val(3);
        }else if(valor==20){//TRANSFERENCIA BOLETEO
            $("#divDocs").css("display","");
            $("#divPersona").css("display","none");
            $(".divCaja").css("display","");
            $(".divUsuario").css("display","");
            $("#divDoc").css("display","");
            $("#divSocio").css("display","none");
            $("#divDoc2").css("display","none");
            $('#doctor').off('keyup');
            $("#doctor").keyup(function (e) {
                var key = window.event ? e.keyCode : e.which;
                if (key == '13') {
                    buscarTransferenciaBoleteo();
                }
            });
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').attr("readonly","true");
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="caja"]').val(3);
        }else if(valor==31){//TRANSFERENCIA FARMACIA
            $("#divDocs").css("display","none");
            $("#divPersona").css("display","");
            $(".divCaja").css("display","");
            $(".divUsuario").css("display","none");
            $("#divDoc").css("display","none");
            $("#divSocio").css("display","none");
            $("#divDoc2").css("display","none");
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').removeAttr("readonly");
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="caja"]').val(4);
            modal ('{{ $url }}', '');
        }
    }else{
        $(".divCaja").css("display","none");
        $(".divUsuario").css("display","none");
        $("#divDocs").css("display","none");
        $("#divDoc2").css("display","none");
        $("#divDoc").css("display","none");
        $("#divSocio").css("display","none");
        $("#divPersona").css("display","");
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').removeAttr("readonly");
    }
    if(valor==10 || valor==150 ||  valor==7 || valor==8 || valor==16 || valor==14 || valor==20 || valor==45 || valor == 35){
        $("#divDoctor").css("display","");
    }else{
        $("#divDoctor").css("display","none");
    }

    if(valor == 150){
        $("#divResponsable").css("display","");     
    }else{
        $("#divResponsable").css("display","none");      
    }

    if(valor == 35){
        $('#tblEncabezado').empty();
        $('#tblEncabezado').append('<tr><th class="text-center">Nro.</th><th class="text-center">Paciente</th><th class="text-center">Servicio</th><th class="text-center">Pago</th></tr>');         
    }else{
        $('#tblEncabezado').empty();
        $('#tblEncabezado').append('<tr><th class="text-center">Nro.</th><th class="text-center">Paciente</th><th class="text-center">Servicio</th><th class="text-center">Pago</th><th class="text-center">R.H.</th><th class="text-center"></th></tr>');
    }

    //if(valor==11 || valor==28 || valor==9 || valor==30 || valor==5 || valor==22 || valor==27 || valor==35){//PAGO ENFERMERIA, PAGO A LIMPIEZA, PROVEEDOR, DEVOLUCION CONTROL Y OTROS EGRESOS, AGENTE POLICIAL, MOVILIDAD
    //if($("#tipodocumento").val()=="3"){
        $(".rh").css("display","");
    //}else{
    //   $(".rh").css("display","none");   
    //}
}

function validarTransferencia(entidad,valor){
    $("#lista").val(carro);
    if($("#tipo").val()!="VR"){
        var rh = $("#rh").val().split("-");
        if($("#rh").val()=="" || rh.length<2){
            alert("Debe ingresar serie y numero correcto de documento");
            return false;
        }
    }
    if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').val()==7 || $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').val()==14 || $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').val()==16 || $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').val()==18 || $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').val()==20 || $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').val()==31){
        $.ajax({
            type: "POST",
            url: "caja/validarCajaTransferencia",
            data: "caja_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="caja"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                if(a=="OK"){
                    guardar(entidad,valor);            
                }else{
                    alert("Error, caja no aperturada");
                }
            }
        });        
    }else{
        guardar(entidad,valor);
    }
}

function buscarTransferencia(){
    $.ajax({
        type: "POST",
        url: "caja/venta",
        data: "busqueda="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="doctor"]').val()+"&miusuario="+$("#miusuario").val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $("#divDoc").html(a);
        }
    }); 
}

function buscarTransferenciaSocio(){
    $.ajax({
        type: "POST",
        url: "caja/ventasocio",
        data: "busqueda="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="doctor"]').val()+"&miusuario="+$("#miusuario").val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $("#divDoc").html(a);
        }
    }); 
}

function buscarTransferenciaTarjeta(){
    $.ajax({
        type: "POST",
        url: "caja/ventatarjeta",
        data: "busqueda="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="doctor"]').val()+"&miusuario="+$("#miusuario").val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $("#divDoc").html(a);
        }
    }); 
}

function buscarTransferenciaBoleteo(){
    $.ajax({
        type: "POST",
        url: "caja/ventaboleteo",
        data: "busqueda="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="doctor"]').val()+"&miusuario="+$("#miusuario").val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $("#divDoc").html(a);
        }
    }); 
}

function buscarPago(){
    $.ajax({
        type: "POST",
        url: "caja/ventapago",
        data: "busqueda="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="doctor"]').val()+"&concepto="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').val()+"&miusuario="+$("#miusuario").val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $("#divDoc2").html(a);
            $('#tabla').DataTable({
                "scrollY":        "250px",
                "scrollCollapse": true,
                "paging":         false,
                "columnDefs": [
                    { "width": "100%", "targets": 2 }
                  ]
            });
            // $('#tabla_filter').css('display','none');
            $("#tabla_info").css("display","none");
        }
    }); 
}

function agregarDocSocio(id,paciente,servicio,numero,pago){
    var band = true;
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            band = false;
            break;
        }
    }    
    if(band){
        var a="<tr id='tr"+id+"'><td>"+numero+"</td><td>"+paciente+"</td><td>"+servicio+"</td><td><input type='text' size='8' id='txtPago"+id+"' name='txtPago"+id+"' value='"+pago+"' class='form-control input-xs' onblur='calcularTotal();' /></td><td><input type='text' id='txtRecibo"+id+"' name='txtRecibo"+id+"' size='8' class='form-control input-xs' /></td><td><button type='button' title='Quitar' class='btn btn-danger btn-xs' onclick=\"quitarDocSocio('"+id+"')\"><i class='fa fa-minus'></i></button></td></tr>";
        carro.push(id);
        calcularTotal();
        $("#tbDetalleDoc").append(a);
        $('#txtPago'+id).inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    }else{
        alert("Ya agregado");
    }
}

function quitarDocSocio(id){
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
    $("#tr"+id).remove();
    calcularTotal();
}

function calcularTotal(){
    var total = 0;
    for(c=0; c < carro.length; c++){
        total = total + parseFloat($('#txtPago'+carro[c]).val());
    }
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val(total);
}
function confirmardescarga() {
    var total = $('#amount').val();
    $('#total').val(total);
    cerrarModal();
}

transferencia($("#concepto").val());

</script>