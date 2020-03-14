<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($modelo, $formData) !!}
{!! Form::hidden('idS', $idsSelec, array('id' => 'idsSelec')) !!}
<?php $numerosArray = explode(',',$numerosSelec); $idsArray = explode(",",$idsSelec);?>
Se registrará el pago al medico con respecto a las siguientes facturas:
@foreach($numerosArray as $key => $num)
<b>{{$num}}<a href="#!" style="color: red;" onclick="removerFactura('{{$idsArray[$key]}}');$(this).parent().remove();">x</a> , </b>
@endforeach
<div class="form-group">
	<div class="col-sm-12">
    	{!! Form::label('medico', 'Medico:', array('class' => 'col-lg-4 col-md-6 col-sm-6 control-label')) !!}
	    <div class="col-lg-8 col-md-6 col-sm-6">
	        {!! Form::text('medico', null, array('class' => 'form-control input-xs', 'id' => 'medico')) !!}
	        {!! Form::hidden('medico_id', 0, array('id' => 'medico_id')) !!}
	    </div>
    </div>
	<div class="col-sm-12">
		<label for="totalAcumulado" class="col-lg-4 col-md-6 col-sm-6 control-label">Total Seleccionado:</label>
		<div class="col-lg-8 col-md-6 col-sm-6">
			<input class="form-control input-xs" id="totalAcumulado" name="total" type="text" value="" readonly="">
		</div>
	</div>
	<div class="col-sm-12">
		<label for="fechaSel" class="col-lg-4 col-md-6 col-sm-6 control-label">Fecha:</label>
		<div class="col-lg-8 col-md-6 col-sm-6">
			<input class="form-control input-xs" id="fechaSel" onchange="consultarPendientes();" name="fecha" type="date" value="{{date("Y-m-d")}}">
		</div>
	</div>
	<div class="col-sm-12">
		<label for="numero" class="col-lg-4 col-md-6 col-sm-6 control-label">Recibo Numero:</label>
		<div class="col-lg-8 col-md-6 col-sm-6">
			<input class="form-control input-xs" id="numero" name="numero" type="text" value="">
		</div>
	</div>
	<div class="col-sm-12">
		<label for="voucherSel" class="col-lg-4 col-md-6 col-sm-6 control-label">Voucher Numero:</label>
		<div class="col-lg-8 col-md-6 col-sm-6">
			<!--input class="form-control input-xs" id="voucherSel" name="voucher" type="text" value="" readonly=""-->
			<select class="form-control input-xs" id="voucherSel" name="voucher" onchange="seleccionarVoucher();">
				
			</select>
		</div>
	</div>
	<div class="col-sm-12">
		<label for="glosaSel" class="col-lg-4 col-md-6 col-sm-6 control-label">Glosa:</label>
		<div class="col-lg-8 col-md-6 col-sm-6">
			<textarea class="form-control input-xs" id="glosaSel" name="glosa" readonly=""></textarea>
		</div>
	</div>
	<div class="col-sm-12">
		<label for="totalSel" class="col-lg-4 col-md-6 col-sm-6 control-label">Total Pagado:</label>
		<div class="col-lg-8 col-md-6 col-sm-6">
			<input class="form-control input-xs" id="totalSel" name="totalp" type="text" value="" readonly="">
		</div>
	</div>
	<div class="col-sm-12">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this,null,\'buscar(\\\'Reportepagofacturacion\\\');\')')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal((contadorModal - 1));setTimeout(function(){ revisarSeleccionados();}, 1000);')) !!}
		</div>
	</div>
</div>
{!! Form::close() !!}
<script type="text/javascript">
	//guardar();
	var lastSelection = [];
	$(document).ready(function() {
		init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
		configurarAnchoModal('550');
		lastSelection = '{{$idsSelec}}';
		lastSelection = lastSelection.split(',');
		var totalAcumulado = 0;
		$.each(totalesSelec,function(key,val){
			totalAcumulado = Number(totalAcumulado) + Number(val);
		});
		$("#totalAcumulado").val(parseFloat(totalAcumulado).toFixed(2));
	});

	function removerFactura(id){
		var index = lastSelection.indexOf(id);
		if (index > -1) {
		  lastSelection.splice(index, 1);
		}
		$("#idsSelec").val(lastSelection.join(","));
		quitarSeleccion(id);
		revisarSeleccionados();
	}

	var medico = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        limit: 10,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'medico/medicoautocompletar/%QUERY',
            filter: function (planes) {
                return $.map(planes, function (movie) {
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
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="medico"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="medico_id"]').val(datum.id);
        consultarPendientes();

    });

    function consultarPendientes(){
    	var idmedico = $("#medico_id").val();
    	var fecha = $("#fechaSel").val();
    	if(Number(idmedico)>0 && fecha.toString().length>0){
	    	$.ajax({
	    		type:"GET",
	    		url: 'cuentasmedico/buscarajax',
	    		data: "idmedico="+idmedico+"&fecha="+fecha,
	    		success: function(a){
	    			a = JSON.parse(a);
	    			var html = "";
	    			$(a).each(function(key,val){
	    				html = html + '<option value="'+val.id+'" data_total="'+val.total+'" data_glosa="'+encodeURI(val.comentario)+'">'+val.formapago + ' ' + val.voucher +'</option>';
	    			});
	    			$("#voucherSel").html(html);
	    			seleccionarVoucher();
	    		}
	    	});
    	}
    }

    function seleccionarVoucher(){
    	var optSel = $("#voucherSel").find("option:selected");
    	$("#glosaSel").val(decodeURI($(optSel).attr("data_glosa")));
    	$("#totalSel").val($(optSel).attr("data_total"));
    }

</script>