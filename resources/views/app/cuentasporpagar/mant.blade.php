<style>
.tr_hover{
	color:red;
}
.form-group{
    margin-bottom: 8px !important;
}
</style>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($movimiento, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('lista', null, array('id' => 'lista')) !!}
    {!! Form::hidden('listaPago', null, array('id' => 'listaPago')) !!}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
                </div>
                {!! Form::label('numero', 'Doc.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2 rh">
                    {!! Form::select('tipo', $cboTipo, 3, array('class' => 'form-control input-xs', 'id' => 'tipo')) !!}
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('numero', null, array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('proveedor', 'Proveedor:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-8 col-md-8 col-sm-8">
                {!! Form::hidden('person_id', null, array('id' => 'person_id')) !!}
                {!! Form::text('proveedor', null, array('class' => 'form-control input-xs', 'id' => 'proveedor', 'placeholder' => 'Ingrese Proveedor')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('glosa', 'Glosa:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-5 col-md-5 col-sm-5">
                    {!! Form::textarea('glosa', null, array('class' => 'form-control input-xs', 'id' => 'glosa', 'rows' => '4')) !!}
                </div>
                {!! Form::label('fechavencimiento', 'Fecha Venc.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::date('fechavencimiento', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechavencimiento')) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="box">
        <div class="box-header">
            <h2 class="box-title col-lg-4 col-md-4 col-sm-4">Detalle <button type="button" class="btn btn-xs btn-info" title="Agregar Detalle" onclick="agregarDetalle();"><i class="fa fa-plus"></i></button></h2>
        </div>
        <div class="box-body" style="max-height: 400px;overflow: auto;">
            <table class="table table-condensed table-border" id="tbDetalle">
                <thead>
                    <th class="text-center">Cant.</th>
                    <th class="text-center">Descripcion</th>
                    <th class="text-center">P. Vent.</th>
                    <th class="text-center">Subtotal</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <th class="text-right">Igv</th>
                    <th class="text-right"><input type='text' id='txtIgv' name="txtIgv" size='5' class="form-control input-xs" style="width:60px;" value="0" /></th>
                    <th class="text-right">Total</th>
                    <th class="text-right"><input type='text' id='txtTotal' name="txtTotal" size='5' class="form-control input-xs" style="width:60px;" /></th>
                </tfoot>
            </table>
        </div>
     </div>
     <div class="form-group">
        <div class="col-lg-12 col-md-12 col-sm-12 text-right">
            {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#lista\').val(carro);$(\'#listaPago\').val(carroPago);guardarCuenta(\''.$entidad.'\', this);')) !!}
            {!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        </div>
    </div>
{!! Form::close() !!}
<script type="text/javascript">
var valorbusqueda="";
$(document).ready(function() {
	configurarAnchoModal('600');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="txtIgv"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="txtTotal"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    
   	var personas = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit:10,
        remote: {
            url: 'cuentasporpagar/personautocompletar/%QUERY',
            filter: function (personas) {
                return $.map(personas, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                        person_id:movie.person_id,
                    };
                });
            }
        }
    });
    personas.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="proveedor"]').typeahead(null,{
        displayKey: 'value',
        source: personas.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="proveedor"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.id);
    });
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fecha"]').focus();
}); 

function guardarCuenta (entidad, idboton) {
    var band=true;
    var msg="";
    if($("#person_id").val()==""){
        band = false;
        msg += " *No se selecciono un proveedor \n";    
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

var valorinicial="";

var carro = new Array();
var carroPago = new Array();
function agregarDetalle(){
    var id = Math.floor((Math.random() * 10000) + 1);
    $("#tbDetalle").append("<tr id='tr"+id+"'><td><input type='text' id='txtCantidad"+id+"' name='txtCantidad"+id+"' value='1' class='form-control input-xs' style='width: 60px;' data='numero' onkeyup='calcularSubtotal("+id+")' onblur='calcularSubtotal("+id+")' /></td>"+
        "<td><textarea class='form-control input-xs' id='txtDescripcion"+id+"'  name='txtDescripcion"+id+"'></textarea></td>"+
        "<td><input type='text' id='txtPrecio"+id+"' name='txtPrecio"+id+"' value='1' size='5' class='form-control input-xs' style='width: 60px;' data='numero' onkeyup='calcularSubtotal("+id+")' onblur='calcularSubtotal("+id+")'/></td>"+
        "<td><input type='text' id='txtSubtotal"+id+"' name='txtSubtotal"+id+"' value='' size='5' class='form-control input-xs' style='width: 60px;' readonly='' data='numero' /></td>"+
        "<td><a href='#' onclick=\"quitarDetalle('"+id+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
    carro.push(id);
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $("#txtCantidad"+id).focus();  
}

function quitarDetalle(id){
    $("#tr"+id).remove();
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
    calcularTotal();
}

function calcularSubtotal(id){
    var pre=parseFloat($("#txtPrecio"+id).val());
    var cant=parseFloat($("#txtCantidad"+id).val());
    var tot=Math.round(pre*cant*100)/100;
    $("#txtSubtotal"+id).val(tot);
    calcularTotal();
}

function calcularTotal(){
    var total2=0;
    for(c=0; c < carro.length; c++){
        var tot=parseFloat($("#txtSubtotal"+carro[c]).val());
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#txtTotal").val(total2);
}

</script>