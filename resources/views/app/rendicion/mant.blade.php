<?php
if(!is_null($Rendicion)){
    $fecha=$Rendicion->fecha;
    $paciente=trim($Rendicion->persona->apellidopaterno.' '.$Rendicion->persona->apellidomaterno.' '.$Rendicion->persona->nombres.' '.$Rendicion->persona->bussinesname);
    $numero=$Rendicion->numero;
    $persona_id=$Rendicion->persona_id;
    $tipo=$Rendicion->formapago;
    $concepto_id=$Rendicion->conceptopago_id;
    $total=number_format($Rendicion->total,2,'.','');
    $id=$Rendicion->id;
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($Rendicion, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('id', $id, array('id' => 'id')) !!}
    {!! Form::hidden('listaCarro', null, array('id' => 'listaCarro')) !!}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::date('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'fecha', 'readonly' => 'true')) !!}
        		</div>
                {!! Form::label('numero', 'Nro.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
                </div>
        	</div>
            <div class="form-group">
        		{!! Form::label('paciente', 'Persona:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                {!! Form::hidden('persona_id', $persona_id, array('id' => 'persona_id')) !!}
        		<div class="col-lg-6 col-md-6 col-sm-6">
        			{!! Form::text('persona', $paciente, array('class' => 'form-control input-xs', 'id' => 'persona', 'readonly' => 'true')) !!}
        		</div>
                {!! Form::label('total', 'Total:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('total', $total, array('class' => 'form-control input-xs', 'id' => 'total', 'readonly' => 'true')) !!}
                </div>
            </div>
            <hr />
            <h4 class="box-title">Detalle Documento</h5>
            <hr />
            <div class="form-group">
                {!! Form::label('fecha2', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::date('fecha2', date("Y-m-d"), array('class' => 'form-control input-xs', 'id' => 'fecha2')) !!}
                </div>
                {!! Form::label('numero2', 'Nro.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('tipo2', $cboTipo, 3, array('class' => 'form-control input-xs', 'id' => 'tipo2')) !!}
                </div>
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('numero2', null, array('class' => 'form-control input-xs', 'id' => 'numero2')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('persona2', 'Proveedor:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                {!! Form::hidden('persona_id2', 0, array('id' => 'persona_id2')) !!}
                <div class="col-lg-6 col-md-6 col-sm-6">
                    {!! Form::text('persona2', null, array('class' => 'form-control input-xs', 'id' => 'persona2')) !!}
                </div>
                {!! Form::label('total2', 'Total:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('total2', 0, array('class' => 'form-control input-xs', 'id' => 'total2')) !!}
                </div>
                <div class="col-lg-1 col-md-1 col-sm-1">
                    <button type="button" title="Agregar" onclick="agregarDocumento()" class="btn btn-xs btn-info"><i class='fa fa-plus'></i></button>
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('area_id', 'Area:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label rh')) !!}
                <div class="col-lg-4 col-md-4 col-sm-4">
                    {!! Form::select('area_id', $cboArea, null, array('class' => 'form-control input-xs', 'id' => 'area_id')) !!}
                </div>
            </div>
            <hr />
            <div class="form-group">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <table id="tbDetalle" class="table table-bordered table-striped table-condensed table-hover">
                        <thead>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">Nro</th>
                            <th class="text-center">Proveedor</th>
                            <th class="text-center">Area</th>
                            <th class="text-center">Monto</th>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th class="text-right" colspan="5">Total</th>
                                <th class="text-center"><input type="text" name="txtTotal2" id="txtTotal2" class="form-control input-xs" style="width: 100px" readonly="" value="0"></th>
                            </tr>
                            <tr>
                                <th class="text-right" colspan="5">Vuelto</th>
                                <th class="text-center"><input type="text" name="txtVuelto" id="txtVuelto" class="form-control input-xs" style="width: 100px" readonly="" value="0"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        	<div class="form-group">
        		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
                    {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listaCarro\').val(carro);guardar(\''.$entidad.'\', this)')) !!}
        			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        		</div>
        	</div>
        </div>
     </div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('650');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total2"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="txtVuelto"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="txtTotal2"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
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
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona2"]').typeahead(null,{
        displayKey: 'value',
        source: personas.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona2"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona_id2"]').val(datum.id);
    });  
}); 
var carro = Array();
function agregarDocumento(){
    if($("#persona2_id").val()=="" ||  $("#persona2_id").val()=="0"){
        alert("Debe seleccionar una persona");
        return false;
    }
    if($("#total2").val()=="" ||  $("#total2").val()=="0"){
        alert("Debe ingresar un total");
        return false;
    }
    var id = Math.round(Math.random()*10000);
    var a="<tr id='tr"+id+"'><td>"+$("#fecha2").val()+"</td><td>"+$("#tipo2").val()+"</td><td>"+$("#numero2").val()+"</td><td>"+$("#persona2").val()+"</td><td>"+$("#area_id option:selected").text()+"</td><td id='tdPago"+id+"' align='right'>"+$("#total2").val()+"</td><td><button type='button' title='Quitar' class='btn btn-danger btn-xs' onclick=\"quitarDocumento('"+id+"')\"><i class='fa fa-minus'></i></button></td></tr>";
    var total = parseFloat($('#txtTotal2').val());
    id = id + "@" + $("#fecha2").val() + "@" + $("#tipo2").val() + "@" + $("#numero2").val() + "@" + $("#persona_id2").val() + "@" + $("#area_id").val() + "@" + $("#total2").val();
    $("#tbDetalle").append(a);
    total = total + parseFloat($("#total2").val());
    $("#txtTotal2").val(total);
    carro.push(id);
    calcularVuelto();
    $('#total2').val('');
    $('#numero2').val('');
}

function quitarDocumento(id){
    var id2 = id.split("@");
    var tot = parseFloat($("#tdPago"+id2[0]).html());
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
    var total = parseFloat($('#txtTotal2').val());
    total = total - tot;
    $("#txtTotal2").val(total);
    $("#tr"+id2[0]).remove();
    calcularVuelto();
}

function calcularVuelto(){
    var total2 = parseFloat($('#txtTotal2').val());
    var total = parseFloat($('#total').val());
    var vuelto = Math.round((total - total2) * 100)/100;
    $("#txtVuelto").val(vuelto);
}

</script>