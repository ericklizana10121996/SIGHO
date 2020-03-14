<style>
.tr_hover{
	color:red;
}
.form-group{
    margin-bottom: 8px !important;
}
</style>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($examen, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('listServicio', null, array('id' => 'listServicio')) !!}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
        		{!! Form::label('tipoexamen_id', 'Tipo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-4 col-md-4 col-sm-4">
                    {!! Form::select('tipoexamen_id', $cboTipoexamen, null, array('class' => 'form-control input-xs', 'id' => 'tipoexamen_id')) !!}
        		</div>
        		{!! Form::label('nombre', 'Nombre:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-4 col-md-4 col-sm-4">
        			{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre')) !!}
        		</div>
        	</div>
        </div>  
        <div class="col-lg-6 col-md-6 col-sm-6 text-right">
            <div class="form-group">
                {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listServicio\').val(carro);guardar1(\''.$entidad.'\', this);')) !!}
                {!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
            </div>
        </div>              
     </div>
     <div class="box">
        <div class="box-header">
            <h2 class="box-title col-lg-4 col-md-4 col-sm-4">Detalle <button type="button" class="btn btn-xs btn-info" title="Agregar Detalle" onclick="agregar();"><i class="fa fa-plus"></i></button></h2>
        </div>
        <div class="box-body" style="max-height: 500px;overflow: auto;">
            <table class="table table-condensed table-border" id="tbDetalle">
                <thead>
                    <th class="text-center">Descripcion</th>
                    <th class="text-center">Referencia</th>
                    <th class="text-center">Unidades</th>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
     </div>
{!! Form::close() !!}
<script type="text/javascript">
var valorbusqueda="";
$(document).ready(function() {
	configurarAnchoModal('600');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombre"]').focus();
}); 

function guardar1 (entidad, idboton) {
    var band=true;
    var msg="";
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
var carroDoc = new Array();
var copia = new Array();

function agregar(){
    var idservicio = "00"+Math.round(Math.random()*10000);
    $("#tbDetalle").append("<tr id='tr"+idservicio+"'><td><textarea class='form-control input-xs' id='txtDescripcion"+idservicio+"' name='txtDescripcion"+idservicio+"' /></td>"+
        "<td><textarea class='form-control input-xs' id='txtReferencia"+idservicio+"' name='txtReferencia"+idservicio+"' /></td>"+
        "<td><input type='text' class='form-control input-xs' size='5' name='txtUnidad"+idservicio+"' id='txtUnidad"+idservicio+"' value='' /></td>"+
        "<td><a href='#' onclick=\"quitar('"+idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
    carro.push(idservicio);
    $("#txtDescripcion"+idservicio).focus();             
}

function quitar(id){
    $("#tr"+id).remove();
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
}

function agregarDetalle(id){
     $.ajax({
        type: "POST",
        url: "examen/agregardetalle",
        data: "id="+id+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            for(d=0;d < datos.length; d++){
                datos[d].idservicio="01"+Math.round(Math.random()*100)+datos[d].id;

                $("#tbDetalle").append("<tr id='tr"+datos[d].idservicio+"'><td><textarea class='form-control input-xs' id='txtDescripcion"+datos[d].idservicio+"' name='txtDescripcion"+datos[d].idservicio+"'>"+datos[d].descripcion+"</textarea></td>"+
                    "<td><textarea class='form-control input-xs' id='txtReferencia"+datos[d].idservicio+"' name='txtReferencia"+datos[d].idservicio+"'>"+datos[d].referencia+"</textarea></td>"+
                    "<td><input type='text' class='form-control input-xs' size='5' name='txtUnidad"+datos[d].idservicio+"' id='txtUnidad"+datos[d].idservicio+"' value='"+datos[d].unidad+"' /></td>"+
                    "<td><a href='#' onclick=\"quitar('"+datos[d].idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td>"+
                    "<td><a href='#' onclick=\"agregar2('"+datos[d].idservicio+"')\"><i class='fa fa-plus-circle' title='Agregar' width='20px' height='20px'></i></td></tr>");
                carro.push(datos[d].idservicio);
                $("#txtDescripcion"+datos[d].idservicio).focus();   
            } 
        }
    });
}

function agregar2(id){
    var idservicio = "00"+Math.round(Math.random()*10000);
    $("#tbDetalle #tr"+id).after("<tr id='tr"+idservicio+"'><td><textarea class='form-control input-xs' id='txtDescripcion"+idservicio+"' name='txtDescripcion"+idservicio+"' /></td>"+
        "<td><textarea class='form-control input-xs' id='txtReferencia"+idservicio+"' name='txtReferencia"+idservicio+"' /></td>"+
        "<td><input type='text' class='form-control input-xs' size='5' name='txtUnidad"+idservicio+"' id='txtUnidad"+idservicio+"' value='' /></td>"+
        "<td><a href='#' onclick=\"quitar('"+idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
    $("#txtDescripcion"+idservicio).focus();             
    var copia = new Array();
    console.log(carro);
    for(c=0; c < carro.length; c++){
        copia.push(carro[c]);
        if(carro[c] == id) {
            copia.push(idservicio);
        }
    }
    carro=copia;
    console.log(carro);
}
<?php
if(!is_null($examen)){
    echo "agregarDetalle(".$examen->id.");";
}
?>
</script>