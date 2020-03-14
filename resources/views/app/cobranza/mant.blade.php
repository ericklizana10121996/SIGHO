<style>
.tr_hover{
	color:red;
}
.form-group{
    margin-bottom: 8px !important;
}
</style>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($cobranza, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('datos', null, array('id' => 'datos')) !!}
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {!! Form::label('plan', 'Plan:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-10 col-md-10 col-sm-10">
                    {!! Form::hidden('plan_id', null, array('id' => 'plan_id')) !!}
        			{!! Form::text('plan', null, array('class' => 'form-control input-xs', 'id' => 'plan')) !!}
        		</div>
            </div>
            <div class="form-group">
                {!! Form::label('fechapago', 'Fecha Pago:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-4 col-md-4 col-sm-4">
                    {!! Form::date('fechapago', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechapago')) !!}
                </div>
                {!! Form::label('voucher', 'Nro. Ope.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-4 col-md-4 col-sm-4">
                    {!! Form::text('voucher', null, array('class' => 'form-control input-xs', 'id' => 'voucher')) !!}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('numero', null, array('class' => 'form-control input-xs', 'id' => 'numero', 'onkeypress' => '')) !!}
                </div>
            </div>
            <div class="form-group col-lg-12 col-md-12 col-sm-12" id="divBusqueda">
            </div>
        </div>
    </div>
    <div class="box">
        <div class="box-header">
            <h2 class="box-title col-lg-4 col-md-4 col-sm-4">Detalle</h2>
        </div>
        <div class="box-body">
            <table class="table table-condensed table-border" id="tbDetalle">
                <thead>
                    <th class="text-center">Fecha</th>
                    <th class="text-center">Nro</th>
                    <th class="text-center">Paciente</th>
                    <th class="text-center">Total</th>
                    <th class="text-center"><input type="checkbox" onchange="marcarTodo($(this).is(':checked'),'chkRetencion');"></th>
                    <th class="text-center">Retencion</th>
                    <th class="text-center"><input type="checkbox" onchange="marcarTodo($(this).is(':checked'),'chkDetraccion');"></th>
                    <th class="text-center">Detraccion</th>
                    <th class="text-center">Subtotal</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <th class="text-right" colspan="6">Total</th>
                    <th class="text-right">{!! Form::text('totalRetencion', null, array('class' => 'form-control input-xs', 'id' => 'totalRetencion', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
                    <th class="text-right">{!! Form::text('totalDetraccion', null, array('class' => 'form-control input-xs', 'id' => 'totalDetraccion', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
                    <th class="text-right">{!! Form::text('total', null, array('class' => 'form-control input-xs', 'id' => 'total', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
                </tfoot>
            </table>
        </div>
     </div>
     <div class="form-group">
        <div class="col-lg-12 col-md-12 col-sm-12 text-right">
            {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listar\').val(carro);guardarPago(\''.$entidad.'\', this);')) !!}
            {!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        </div>
    </div>
{!! Form::close() !!}
<script type="text/javascript">
var valorbusqueda="";
$(document).ready(function() {
	configurarAnchoModal('1000');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    
   	var planes = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
        limit: 10,
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'plan/planautocompletar/%QUERY',
			filter: function (planes) {
				return $.map(planes, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
                        coa: movie.coa,
                        deducible:movie.deducible,
                        ruc:movie.ruc,
                        direccion:movie.direccion,
					};
				});
			}
		}
	});
	planes.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').typeahead(null,{
		displayKey: 'value',
		source: planes.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val(datum.id);
        buscarDocumento();
	});

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').focus();

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero"]').on( 'keydown', function () {
        var e = window.event; 
        var keyc = e.keyCode || e.which;
        if(this.value.length>0 && keyc == 13 && this.value!=valorbusqueda){
            buscarDocumento(this.value);
            valorbusqueda=this.value;
            this.focus();
            return false;
        }
        if(keyc == 38 || keyc == 40 || keyc == 13) {
            var tabladiv='tablaDocumento';
			var child = document.getElementById(tabladiv).rows;
			var indice = -1;
			var i=0;
            $('#tablaDocumento tr').each(function(index, elemento) {
                if($(elemento).hasClass("tr_hover")) {
    			    $(elemento).removeClass("par");
    				$(elemento).removeClass("impar");								
    				indice = i;
                }
                if(i % 2==0){
    			    $(elemento).removeClass("tr_hover");
    			    $(elemento).addClass("impar");
                }else{
    				$(elemento).removeClass("tr_hover");								
    				$(elemento).addClass('par');
    			}
    			i++;
    		});		 
			// return
			if(keyc == 13) {        				
			     if(indice != -1){
					var seleccionado = '';			 
					if(child[indice].id) {
					   seleccionado = child[indice].id;
					} else {
					   seleccionado = child[indice].id;
					}		 		
					seleccionarDocumento(seleccionado);
				}
			} else {
				// abajo
				if(keyc == 40) {
					if(indice == (child.length - 1)) {
					   indice = 1;
					} else {
					   if(indice==-1) indice=0;
	                   indice=indice+1;
					} 
				// arriba
				} else if(keyc == 38) {
					indice = indice - 1;
					if(indice==0) indice=-1;
					if(indice < 0) {
						indice = (child.length - 1);
					}
				}	 
				child[indice].className = child[indice].className+' tr_hover';
			}
        }
    });
}); 

function guardarPago (entidad, idboton) {
    var band=true;
    var msg="";
    if($("#voucher").val()==""){
        band = false;
        msg += " *Debe ingresar mov ref. \n";    
    }
    if($("#plan_id").val()==""){
        band = false;
        msg += " *No se selecciono un plan \n";    
    }
    var datosCobranza = new Array();
    for(c=0; c < carro.length; c++){
        datosCobranza.push($('#txtDetraccion'+carro[c]).val()+'@'+$('#txtRetencion'+carro[c]).val());
    }
    $('#datos').val(datosCobranza);
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
function buscarDocumento(valor){
    $.ajax({
        type: "POST",
        url: "cobranza/buscardocumento",
        data: "numero="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero"]').val()+"&plan_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            $("#divBusqueda").html("<table class='table table-bordered table-condensed table-hover' border='1' id='tablaDocumento'><thead><tr><th class='text-center'>FECHA</th><th class='text-center'>NRO</th><th class='text-center'>PACIENTE</th><th class='text-center'>TOTAL</tr></thead></table>");
            var pag=parseInt($("#pag").val());
            var d=0;
            for(c=0; c < datos.length; c++){
                var a="<tr id='"+datos[c].id+"' onclick=\"seleccionarDocumento('"+datos[c].id+"')\"><td align='center' style='font-size:12px'>"+datos[c].fecha+"</td><td style='font-size:12px'>"+datos[c].numero+"</td><td style='font-size:12px'>"+datos[c].paciente+"</td><td align='right' style='font-size:12px'>"+datos[c].total+"</td></tr>";
                $("#tablaDocumento").append(a);           
            }
            $('#tablaDocumento').DataTable({
                "scrollY":        "250px",
                "scrollCollapse": true,
                "paging":         false,
                "columnDefs": [
                    { "width": "80%", "targets": 2 }
                  ]
            });
            $('#tablaDocumento_filter').css('display','none');
            $("#tablaDocumento_info").css("display","none");
	    }
    });
}

var carro = new Array();
var carroDoc = new Array();
function seleccionarDocumento(id){
    var band=true;
    for(c=0; c < carro.length; c++){
        if(carro[c]==id){
            band=false;
        }      
    }
    if(band){
        $.ajax({
            type: "POST",
            url: "cobranza/seleccionardocumento",
            data: "id="+id+"&plan_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                datos=JSON.parse(a);
                var c=0;
                $("#tbDetalle").append("<tr id='tr"+datos[c].id+"'><td><input type='hidden' id='txtId"+datos[c].id+"' name='txtId"+datos[c].id+"' value='"+datos[c].id+"' />"+datos[c].fecha+"</td>"+
                    "<td>"+datos[c].numero+"</td>"+
                    "<td>"+datos[c].paciente+"</td>"+
                    "<td><input type='text' id='txtPrecio"+datos[c].id+"' name='txtPrecio"+datos[c].id+"' value='"+datos[c].total+"' size='5' class='form-control input-xs' data='numero' style='width: 60px;' readonly='' /></td>"+
                    "<td><input class='chkRetencion' style='margin-left:6px;' id='chkRetencion"+datos[c].id+"' type='checkbox' onchange='calcularRetencion($(this).is(\":checked\"),"+datos[c].id+")' /></td>"+
                    "<td><input type='text' size='5' class='form-control input-xs' data='numero' id='txtRetencion"+datos[c].id+"' style='width: 60px;' name='txtRetencion"+datos[c].id+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].id+"')}\" onblur=\"calcularTotalItem('"+datos[c].id+"')\" /></td>"+
                    "<td><input class='chkDetraccion' style='margin-left:30px;' type='checkbox' id='chkDetraccion"+datos[c].id+"' onchange='calcularDetraccion($(this).is(\":checked\"),"+datos[c].id+")' /></td>"+
                    "<td><input type='text' size='5' class='form-control input-xs' data='numero' id='txtDetraccion"+datos[c].id+"' style='width: 60px;' name='txtDetraccion"+datos[c].id+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].id+")}\" onblur=\"calcularTotalItem('"+datos[c].id+"')\" style='width:50%' /></td>"+
                    "<td><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[c].id+"' style='width: 60px;' id='txtTotal"+datos[c].id+"' value='"+datos[c].total+"' /></td>"+
                    "<td><a href='#' onclick=\"quitarDocumento('"+datos[c].id+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                carro.push(datos[c].id);
                $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
                //$("#txtMedico"+datos[c].idservicio).focus();  
            }
        });
    }else{
        alert('Documento ya agregado');
    }
}

function marcarTodo(valor,clase){
    $("."+clase).each(function(key,val){
        if($(val).is(":checked") != valor){
            $(val).prop("checked",valor);
            $(val).trigger("change");
        }
    });
}

function calcularTotal(){
    var total2=0;
    for(c=0; c < carro.length; c++){
        var tot=parseFloat($("#txtTotal"+carro[c]).val());
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#total").val(total2);
}

var total_det = 0;
var total_ret = 0; 

function calcularDetraccion(valor,id){
    var det = 0;
    if(valor){
        det = Math.round((parseFloat($("#txtPrecio"+id).val())*0.12)*100)/100;

        total_det+=det;

        // var total_det =det;
        // if(total_det =='' || total_det == '0'){
        //     $('#totalDetraccion').val(det);                
        // }else{
        //     $('#totalDetraccion').val(parseFloat(det) + det);                   
        // }
    }else{
       det = Math.round((parseFloat($("#txtPrecio"+id).val())*0.12)*100)/100;
       // $("#txtPrecio"+id).val("0");
       total_det-=det;
       det = 0 ;
    }

    $('#totalDetraccion').val(total_det);
    $("#txtDetraccion"+id).val(det);
    calcularTotalItem(id);
    return;
}
 
function calcularRetencion(valor,id){
    var ret = 0;
    if(valor){
        ret = Math.round((parseFloat($("#txtPrecio"+id).val())*0.03)*100)/100;
        total_ret += ret;
        // var total_ret =ret;
   
        // if(total_ret =='' || total_ret == '0'){
        //     $('#totalRetencion').val(ret);
        // }else{
        //     $('#totalRetencion').val(parseFloat(ret) + ret);   
        // }
    }else{
        ret = Math.round((parseFloat($("#txtPrecio"+id).val())*0.03)*100)/100;
        // $("#txtPrecio"+id).val("0");
        total_ret -=  ret;
        ret = 0;
    }
    $("#txtRetencion"+id).val(ret);
    $('#totalRetencion').val(total_ret);    
   
    calcularTotalItem(id);
}

function calcularTotalItem(id){
    var ret=parseFloat($("#txtRetencion"+id).val());
    var pv=parseFloat($("#txtPrecio"+id).val());
    var det=parseFloat($("#txtDetraccion"+id).val());
    var total=Math.round((pv - det - ret) * 100) / 100;

    $("#txtTotal"+id).val(total);   
    calcularTotal();
}


function quitarDocumento(id){
    $("#tr"+id).remove();
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
    calcularTotal();
}

</script>