<?php 
if(is_null($cuentabancaria)){
    $persona="";
    $person_id=0;
    $total=0;
    $tipo=3;
    $voucher="";
    $rh="";
    $forma="";
    $fecha=date('Y-m-d');
}else{
    $persona=$cuentabancaria->persona->bussinesname.' '.$cuentabancaria->persona->apellidopaterno.' '.$cuentabancaria->persona->apellidomaterno.' '.$cuentabancaria->persona->nombres;
    $person_id=$cuentabancaria->persona_id;
    $forma=$cuentabancaria->numeroficha;
    $total=$cuentabancaria->total;
    $tipo=$cuentabancaria->formapago;
    $voucher=$cuentabancaria->dni;
    $rh=$cuentabancaria->voucher;
    $fecha=$cuentabancaria->fecha;
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($cuentabancaria, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('lista', '', array('id' => 'lista')) !!}
	<div class="form-group">
		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::date('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
		</div>
        {!! Form::label('tipodocumento_id', 'Tipo:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        <div class="col-lg-2 col-md-2 col-sm-2">
            {!! Form::select('tipodocumento_id', $cboTipoDocumento, null, array('class' => 'form-control input-xs', 'id' => 'tipodocumento_id','onchange' => 'generarConcepto(this.value,0);')) !!}
        </div>
        {!! Form::label('formapago', 'Forma Pago:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        <div class="col-lg-2 col-md-2 col-sm-2">
            {!! Form::select('formapago', $cboFormaPago, $forma, array('class' => 'form-control input-xs', 'id' => 'formapago')) !!}
        </div>
	</div>
    <div class="form-group" id="divPersona">
		{!! Form::label('persona', 'Persona:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
        {!! Form::hidden('person_id', $person_id, array('id' => 'person_id')) !!}
			{!! Form::text('persona', $persona, array('class' => 'form-control input-xs', 'id' => 'persona', 'placeholder' => 'Ingrese Persona')) !!}
		</div>
    </div>
    <div class="form-group" id="divDocs">
        <div class="col-lg-12 col-md-12 col-sm-12" id="divDoc" style='overflow-y:auto;max-height:400px;'>
        </div>
        <hr />
        <div class="col-lg-12 col-md-12 col-sm-12" id="divDetalleDoc">
            <table id="tbDetalleDoc" class="table table-bordered table-striped table-condensed table-hover">
                <thead>
                    <tr>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Nro</th>
                        <th class="text-center">Total</th>
                        <th class="text-center"></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('concepto', 'Concepto Pago:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        <div class="col-lg-9 col-md-9 col-sm-9">
            {!! Form::select('conceptopago_id', $cboConcepto, null, array('class' => 'form-control input-xs', 'id' => 'conceptopago_id')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('cuentabanco_id', 'Cuenta Banco:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        <div class="col-lg-3 col-md-3 col-sm-3">
            {!! Form::select('cuentabanco_id', $cboCuenta, null, array('class' => 'form-control input-xs', 'id' => 'cuentabanco_id')) !!}
        </div>
        {!! Form::label('voucher', 'Nro Ope.:', array('class' => 'col-lg-1 col-md-1 col-sm-2 control-label')) !!}
        <div class="col-lg-2 col-md-2 col-sm-2">
            {!! Form::text('voucher', $voucher, array('class' => 'form-control input-xs', 'id' => 'voucher')) !!}
        </div>
        {!! Form::label('total', 'Total:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        <div class="col-lg-2 col-md-2 col-sm-2">
            {!! Form::text('total', $total, array('class' => 'form-control input-xs', 'id' => 'total')) !!}
        </div>
	</div>
    <div class="form-group">
		{!! Form::label('comentario', 'Comentario:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::textarea('comentario', null, array('class' => 'form-control input-xs', 'id' => 'comentario', 'cols' => 10 , 'rows','5')) !!}
		</div>
        {!! Form::label('rh', 'Doc.:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label rh')) !!}
        <div class="col-lg-2 col-md-2 col-sm-2">
            {!! Form::select('tipo', $cboTipo, $tipo, array('class' => 'form-control input-xs', 'id' => 'tipo')) !!}
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2">
            {!! Form::text('rh', $rh, array('class' => 'form-control input-xs', 'id' => 'rh')) !!}
        </div>
	</div>
	 <div class="form-group">
        {!! Form::label('entregado', 'Entregado a:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        <div class="col-lg-6 col-md-6 col-sm-6">
            {!! Form::text('entregado', null, array('class' => 'form-control input-xs', 'id' => 'entregado')) !!}
        </div>
    </div>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'validarTransferencia(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('700');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
	var personas = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
        limit: 10,
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'cajatesoreria/personautocompletar/%QUERY',
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

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').keyup(function (e) {
        var key = window.event ? e.keyCode : e.which;
        if (key == '13') {
            buscarDoc();
        }
    });   

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').focus();
}); 

function validarTransferencia(entidad,valor){
    $("#lista").val(carro);
    if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').val()==7){
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

function buscarDoc(){
    $.ajax({
        type: "POST",
        url: "cuentabancaria/cuentasporpagar",
        data: "busqueda="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="persona"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $("#divDoc").html(a);
        }
    }); 
}

var carro = new Array();
function agregarDoc(id,numero,fecha,pago){
    var band = true;
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            band = false;
            break;
        }
    }    
    if(band){
        var a="<tr id='tr"+id+"'><td>"+fecha+"</td><td>"+numero+"</td><td id='tdPago"+id+"' align='right'><input type='text'  class='form-control input-xs' id='txtPago"+id+"' name='txtPago"+id+"' value='"+pago+"' onblur='calcularTotalDoc()' onkeyup='calcularTotalDoc()' style='width:80px;' data='numero' max='"+pago+"'/></td><td><button type='button' title='Quitar' class='btn btn-danger btn-xs' onclick=\"quitarDoc('"+id+"')\"><i class='fa fa-minus'></i></button></td></tr>";
        var total = parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val());
        total = total + parseFloat(pago);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val(total);
        carro.push(id);
        $("#tbDetalleDoc").append(a);
        $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    }else{
        alert("Ya agregado");
    }
}

function quitarDoc(id){
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
    var total = parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val());
    total = total - parseFloat($("#txtPago"+id).val());
    $("#doc").val('');
    $("#tr"+id).remove();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val(total);
}

function calcularTotalDoc(){
    var tot=0;
    for(c=0; c < carro.length; c++){
        tot = tot + parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="txtPago'+carro[c]+'"]').val());
    }
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="total"]').val(tot);
}

function generarConcepto(valor,id){
    $.ajax({
        type: "POST",
        url: "cuentabancaria/generarConcepto",
        data: "tipodocumento_id="+valor+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="conceptopago_id"]').html(a);
            //generarNumero(valor);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="conceptopago_id"]').val(id);
            transferencia($("#concepto").val());
        }
    });
}

<?php 
if(!is_null($cuentabancaria)){
    echo "generarConcepto(".$cuentabancaria->tipodocumento_id.",".$cuentabancaria->conceptopago_id.");";
}
?>
</script>