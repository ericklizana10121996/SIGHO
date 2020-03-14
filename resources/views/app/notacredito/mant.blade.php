<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($notacredito, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {!! Form::label('numeroref', 'Nro Ref:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::hidden('movimiento_id', null, array('id' => 'movimiento_id')) !!}
        			{!! Form::text('numeroref', null, array('class' => 'form-control input-xs', 'id' => 'numeroref')) !!}
        		</div>
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
        		</div>
                {!! Form::label('serie', 'Serie:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-1 col-md-1 col-sm-1">
        			{!! Form::text('serie', '002', array('class' => 'form-control input-xs', 'id' => 'serie')) !!}
        		</div>
                <div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
        		</div>
        	</div>
            <div class="form-group">
        		{!! Form::label('paciente', 'Persona:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-5 col-md-5 col-sm-5">
                {!! Form::hidden('person_id', null, array('id' => 'person_id')) !!}
    			{!! Form::text('paciente', null, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Paciente', 'readonly' => 'true')) !!}
        		</div>
                <?php
                if($user->usertype_id==4 || $user->usertype_id==20){
                ?>
                {!! Form::hidden('pagar', 'N', array('id' => 'pagar')) !!}    
                <?php
                }else{
                ?>
                {!! Form::hidden('pagar', 'S', array('id' => 'pagar')) !!}    
                <input type="checkbox" onchange="mostrarCaja(this.checked)" checked id="pago" class="col-lg-1 col-md-1 col-sm-1 control-label datocaja" />
                {!! Form::label('pago', 'Genera Egreso', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                {!! Form::label('caja_id', 'Caja:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label caja')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('caja_id', $cboCaja, $idcaja, array('class' => 'form-control input-xs caja', 'id' => 'caja_id', 'readonly' => 'true')) !!}
                </div>
                <?php
                }
                ?>
        	</div>
            <div class="form-group">
                {!! Form::label('paciente2', 'Paciente:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-5 col-md-5 col-sm-5">
                    {!! Form::text('paciente2', null, array('class' => 'form-control input-xs', 'id' => 'paciente2', 'readonly' => 'true')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('comentario', 'Caja:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-7 col-md-7 col-sm-7">
                    {!! Form::select('comentario', $cboComentario, null, array('class' => 'form-control input-xs', 'id' => 'comentario')) !!}
                </div>
            </div>
       
            <div class="form-group">
                {!! Form::label('motivo', 'Motivo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label','id' => 'label_motivo')) !!}
               <div class="col-lg-7 col-md-7 col-sm-7">
                {!! Form::textarea('motivo', null, array('class' => 'form-control input-xs', 'id' => 'motivo', 'placeholder' => 'Ingrese motivo', 'rows' => '2')) !!}
                
                </div>
         
                <div class="col-lg-3 col-md-3 col-sm-3 text-right">
        			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-xs', 'id' => 'btnGuardar', 'onclick' => 'guardarNota(\''.$entidad.'\', this)')) !!}
        			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-xs', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        		</div>
        	</div>
         </div>
         <div class="col-lg-12 col-md-12 col-sm-12">
            <table class="table table-condensed table-border" id="tbDetalle">
                <thead>
                    <th class="text-center">Cant.</th>
                    <th class="text-center">Descripcion</th>
                    <th class="text-center">Precio</th>
                    <th class="text-center">Subtotal</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <th class="text-right" colspan="3">Total</th>
                    <td align="center">{!! Form::text('total', null, array('class' => 'form-control input-xs numero', 'id' => 'total', 'size' => 5, 'readonly' => 'true', 'style' => 'width: 70px;')) !!}</td>
                </tfoot>
            </table>
         </div>
     </div>
</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
    $('#motivo').css('display','none');
    $('#motivo').css('resize','none');
    $('#label_motivo').css('display','none');
   
	configurarAnchoModal('760');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="serie"]').inputmask("999");
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero"]').inputmask("99999999");
	var doc = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
        limit:10,
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'ventaadmision/ventaautocompletar/%QUERY',
			filter: function (docs) {
				return $.map(docs, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
                        paciente: movie.paciente,
                        person_id:movie.person_id,
                        paciente2:movie.paciente2,
					};
				});
			}
		}
	});
	doc.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numeroref"]').typeahead(null,{
		displayKey: 'value',
		source: doc.ttAdapter(),
        limit:10,
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numeroref"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.paciente);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente2"]').val(datum.paciente2);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);
        agregarDetalle(datum.id);
	});
}); 

function agregarDetalle(id){
     $.ajax({
        type: "POST",
        url: "notacredito/seleccionarventa",
        data: "id="+id+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $("#tbDetalle tbody").html('');
            $("#tbDetalle").append(a);
            $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
            calcularTotal();
        }
    });
}

function quitarServicio(id){
    $("#tr"+id).remove();
    var carro = $("#listServicio").val().split(",");
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
    $("#listServicio").val(carro);
    calcularTotal();
}

function calcularTotal(){
    var total2=0;
    var carro = $("#listServicio").val().split(",");
    for(c=0; c < carro.length; c++){
        var tot=parseFloat($("#txtTotal"+carro[c]).val());
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#total").val(total2); 
}

function guardarNota (entidad, idboton) {
    var carro = $("#listServicio").val().split(",");
    if(carro.length==0){
        alert("Nota de credito debe tener un detalle");
    }else{
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
                    window.open('/juanpablo/notacredito/pdfComprobante?id='+dat[0].id,'_blank');
    			} else if(resp === 'ERROR') {
    				alert(dat[0].msg);
    			} else {
    				mostrarErrores(respuesta, idformulario, entidad);
    			}
    		}
    	});
    }
}

function mostrarCaja(check){
    if(check){
        $(".caja").css("display","");
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="pagar"]').val('S');
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="pagar"]').val('N');
        $(".caja").css("display","none");
    }
}

$('#comentario').on('change',function(){
   // alert("dee");
   var val =  $('#comentario').val();
    if(val == "10@Otros conceptos"){
        $('#motivo').css('display','');
        $('#label_motivo').css('display','');
    }else{
        $('#motivo').css('display','none');    
        $('#label_motivo').css('display','none');
    }
});

</script>