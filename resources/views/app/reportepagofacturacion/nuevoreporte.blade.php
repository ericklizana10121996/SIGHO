<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($modelo, $formData) !!}
{!! Form::hidden('idS', $idsSelec, array('id' => 'idsSelec')) !!}
<?php $numerosArray = explode(',',$numerosSelec); $idsArray = explode(",",$idsSelec);?>
Se generará un pago pendiente al medico con respecto a las siguientes facturas:
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
		<label for="mesSel" class="col-lg-4 col-md-6 col-sm-6 control-label">Mes:</label>
		<div class="col-lg-8 col-md-6 col-sm-6">
			<input class="form-control input-xs" id="mesSel" onchange="consultarPendientes();" name="mes" type="month" value="{{$mes}}">
		</div>
	</div>
	<div class="col-sm-12">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this,null,\'if(respuesta.substring(0, 2)=="OK"){ finalizarNuevoReporte(respuesta.substring(3,(respuesta.length)));}\')')) !!}
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
    });

</script>