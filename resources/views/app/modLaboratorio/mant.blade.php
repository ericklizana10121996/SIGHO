<?php
if(is_null($reporteLab)){
    $fecha=date('Y-m-d');
    /*
        $paciente='';
        $person_id=null;
        $dni=null;
        $historia=null;
        $historia_id=null;
    */
}else{
    $fecha=$reporteLab->fecha;
    /*
        $paciente=$reporteLab->historia->persona->apellidopaterno.' '.$reporteLab->historia->persona->apellidomaterno.' '.$reporteLab->historia->persona->nombres;
        $person_id=$reporteLab->historia->persona_id;
        $dni=$reporteLab->historia->persona->dni;
        $historia_id=$reporteLab->historia_id;
        $historia=$reporteLab->historia->numero;
    */
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
{!! Form::model($reporteLab, $formData) !!}   
    {!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('lista', null, array('id' => 'lista')) !!}
    {!! Form::hidden('listaPago', null, array('id' => 'listaPago')) !!}
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="form-group">
                {!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                <div class="col-lg-8 col-md-8 col-sm-8">
                    {!! Form::date('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
                </div>
              {{--   {!! Form::label('tipopaciente', 'Tipo Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::select('tipopaciente', $cboTipoPaciente, null, array('class' => 'form-control input-xs', 'id' => 'tipopaciente', 'disabled' => 'true')) !!}
                </div> --}}
            </div>
       
            <div class="form-group col-lg-12 col-md-12 col-sm-12" id="divPagos">
            </div>
        </div>
        <div class="col-lg-8 col-md-8 col-sm-8">
            <div class="form-group">
                {!! Form::label('examen', 'Examen:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('examen', null, array('class' => 'form-control input-xs', 'id' => 'examen', 'onkeypress' => '')) !!}
                </div>
            </div>
            <div class="form-group col-lg-12 col-md-12 col-sm-12" id="divBusqueda">
            </div>
        </div>
    </div>
    <div class="box">
        <div class="box-header">
            <h2 class="box-title col-lg-4 col-md-4 col-sm-4">Detalle
            {{--     <button type="button" class="btn btn-xs btn-info" title="Agregar Detalle" onclick="seleccionarServicioOtro();"><i class="fa fa-plus"></i></button> --}}
{{--                 {!! Form::button(array('class' => 'btn btn-info btn-xs', 'title' => 'Agregar Detalle', 'id' => 'btnNuevo02', 'onclick' => 'modal (\''.URL::route($ruta["create02"], array('listar'=>'SI')).'\', \''.$titulo_registrar02.'\', this);','<i class="fa fa-plus"></i>')) !!} --}}

                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'id' => 'btnNuevo02', 'onclick' => 'modal (\''.URL::route($ruta["create02"], array('listar'=>'SI')).'\', \''.$titulo_registrar02.'\', this);')) !!}
            </h2>
            </h2>
        </div>
        <div class="box-body" style="max-height: 400px;overflow: auto;">
            <table class="table table-condensed table-border" id="tbDetalle">
                <thead>
                    <th class="text-center">Tipo Pago</th>
                    <th class="text-center">Examen</th>
                    <th class="text-center">Cantidad</th>
                  {{--   <th class="text-center">Resultado</th>
                    <th class="text-center">Referencia</th>
                    <th class="text-center">Unidades</th> --}}
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
     </div>
     <div class="form-group">
        <div class="col-lg-12 col-md-12 col-sm-12 text-right">
            {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#lista\').val(carro);$(\'#listaPago\').val(carroPago);guardarAnalisis(\''.$entidad.'\', this);')) !!}
            {!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        </div>
    </div>
{!! Form::close() !!}
<script type="text/javascript">
var valorbusqueda="";
$(document).ready(function() {
    configurarAnchoModal('800');
    init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    
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
                        direccion:movie.direccion2,
                        edad:movie.edad,
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
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').val(datum.historia);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);
        if(datum.tipopaciente=="Hospital"){
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val("Particular");
        }else{
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val(datum.tipopaciente);
        }   
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="direccion"]').val(datum.direccion);
        if(datum.edad=="0"){
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="edad"]').val(datum.edad);
        }else{
            var fecha1 = moment(datum.edad);
            var fecha2 = moment(datum.fecha);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="edad"]').val(fecha2.diff(fecha1, 'years'));
        }
        agregarDetallePago(datum.person_id);
    });
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fecha"]').focus();

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="examen"]').on( 'keydown', function () {
        var e = window.event; 
        var keyc = e.keyCode || e.which;
        if(this.value.length>0 && keyc == 13 && this.value!=valorbusqueda){
            buscarExamen(this.value);
            valorbusqueda=this.value;
            this.focus();
            return false;
        }
        if(keyc == 38 || keyc == 40 || keyc == 13) {
            var tabladiv='tablaExamen';
            var child = document.getElementById(tabladiv).rows;
            var indice = -1;
            var i=0;
            $('#tablaExamen tr').each(function(index, elemento) {
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
                    seleccionarExamen(seleccionado);
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

function guardarAnalisis (entidad, idboton) {
    var band=true;
    var msg="";
    // if($("#historia_id").val()==""){
    //     band = false;
    //     msg += " *No se selecciono un paciente \n";    
    // }
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
function buscarExamen(valor){
    $.ajax({
        type: "POST",
        url: "modlaboratorio/buscarexamen",
        data: "examen="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="examen"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            $("#divBusqueda").html("<table class='table table-bordered table-condensed table-hover' border='1' id='tablaExamen'><thead><tr><th class='text-center'>TIPO PAGO</th><th class='text-center'>EXAMEN</th></tr></thead></table>");
            var pag=parseInt($("#pag").val());
            var d=0;
            for(c=0; c < datos.length; c++){
                var a="<tr id='"+datos[c].id+"' onclick=\"seleccionarExamen('"+datos[c].id+"')\"><td align='center' style='font-size:12px'>"+datos[c].tipopago+"</td><td style='font-size:12px'>"+datos[c].servicio+"</td></tr>";
                $("#tablaExamen").append(a);           
            }
            $('#tablaExamen').DataTable({
                "scrollY":        "250px",
                "scrollCollapse": true,
                "paging":         false,
                "columnDefs": [
                    { "width": "80%", "targets": 1 }
                  ]
            });
            $('#tablaExamen_filter').css('display','none');
            $("#tablaExamen_info").css("display","none");
        }
    });
}

var carro = new Array();
var carroExamen = new Array();
var carroPago = new Array();

function marcar(id){
    $.ajax({
          type:'GET',
          url:"reporteconsulta/marca",
          data:{'id':id},
          success: function(a) {
                console.log('Listo');
          }
    });
}

function desmarcar(id){
    $.ajax({
          type:'GET',
          url:"reporteconsulta/desmarca",
          data:{'id':id},
          success: function(a) {
                console.log('Listo');
          }
    });
}

function seleccionarExamen(id){
    // alert(id);
    var band=true;
    for(var x=0; x < carroExamen.length; x++){
        if(carroExamen[x]==id){
            band=false;
        }      
    }

    console.log(carroExamen);

    if(band){
        carroExamen.push(id);
        $.ajax({
            type: "POST",
            url: "modlaboratorio/seleccionarexamen",
            data: "id="+id+"&plan_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                datos=JSON.parse(a);
                var c=0;
                // for(c==0;c<datos.length;c++){
                    $("#tbDetalle").append("<tr id='tr"+datos[c].id+"'><td>"+datos[c].tipopago+"</td><td><input type='hidden' id='txtId"+datos[c].id+"' name='txtId"+datos[c].id+"' value='"+datos[c].id+"' />"+datos[c].servicio+"</td>"+
                        "<td><input type='number' min='1' class='form-control input-xs text-right' id='txtCantidad"+datos[c].id+"' name='txtCantidad"+datos[c].id+"'></td>"+
                        // "<td><textarea id='txtResultado"+datos[c].id+"' name='txtResultado"+datos[c].id+"' class='form-control input-xs' data='datos' ></textarea>"+
                        // "<td><textarea class='form-control input-xs' id='txtReferencia"+datos[c].id+"'  name='txtReferencia"+datos[c].id+"' readonly=''>"+datos[c].referencia+"</textarea></td>"+
                        // "<td><textarea class='form-control input-xs' id='txtUnidad"+datos[c].id+"'  name='txtUnidad"+datos[c].id+"' readonly=''>"+datos[c].unidad+"</textarea></td>"+
                        "<td><a href='#' onclick=\"quitarExamen('"+datos[c].id+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                    carro.push(datos[c].id);
                // }
                //$(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
                $(':input[data="datos"]').keydown( function(e) {
                    var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                    if(key == 40) {
                        e.preventDefault();
                        var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
                        inputs.eq( inputs.index(this)+ 1 ).focus();
                    }
                    if(key == 38) {
                        e.preventDefault();
                        var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
                        inputs.eq( inputs.index(this)-1 ).focus();
                    }
                });
            }
        });
    }else{
        alert('Examen ya agregado');
    }
}

function quitarExamen(id){
    $("#tr"+id).remove();
    for(var c=0; c < carroExamen.length; c++){
        if(carro[c] == id) {
            carroExamen.splice(c,1);
            c--;
        }
    }
    // console.log(carroExamen);
}

function agregarDetallePago(idpersona){
    $.ajax({
        type: "POST",
        url: "analisis/buscarpagos",
        data: "persona_id="+idpersona+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            $("#divPagos").html("<table class='table table-bordered table-condensed table-hover' border='1' id='tablaPago'><thead><tr><th>-</th><th class='text-center'>FECHA</th><th class='text-center'>NRO</th><th class='text-center'>CANT.</th><th class='text-center'>DESCRIPCION</th><th class='text-center'>PRECIO</th></tr></thead></table>");
            var pag=parseInt($("#pag").val());
            carroPago = new Array();
            var d=0;
            for(c=0; c < datos.length; c++){
                var a="<tr><td><input type='checkbox' id='chk"+datos[c].id+"' onclick=\"agregarPago(this.checked,'"+datos[c].id+"')\" /></td><td align='center' style='font-size:12px'>"+datos[c].fecha+"</td><td style='font-size:12px'>"+datos[c].numero+"</td><td style='font-size:12px'>"+datos[c].cantidad+"</td><td style='font-size:12px'>"+datos[c].descripcion+"</td><td style='font-size:12px'>"+datos[c].precio+"</td></tr>";
                $("#tablaPago").append(a);           
            }
            $('#tablaPago').DataTable({
                "scrollY":        "250px",
                "scrollCollapse": true,
                "paging":         false,
                "columnDefs": [
                    { "width": "10%", "targets": 1 }
                  ]
            });
            $('#tablaPago_filter').css('display','none');
            $("#tablaPago_info").css("display","none");
        }
    });
}

function agregarPago(check,id){
    if(check){
        marcar(id);
        carroPago.push(id);
    }else{
        desmarcar(id);
        for(c=0; c < carroPago.length; c++){
            if(carroPago[c] == id) {
                carroPago.splice(c,1);
            }
        }
    }
}

function agregarDetalle(id){
    $.ajax({
        type: "POST",
        url: "modlaboratorio/agregarDetalle",
        data: "id="+id+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            var c=0;
            for(c==0;c<datos.length;c++){
                // $("#tbDetalle").append("<tr id='tr"+datos[c].id+"'><td>"+datos[c].tipoexamen+"</td><td><input type='hidden' id='txtId"+datos[c].id+"' name='txtId"+datos[c].id+"' value='"+datos[c].id+"' />"+datos[c].examen+"</td>"+
                //     "<td><textarea class='form-control input-xs' id='txtDescripcion"+datos[c].id+"'  name='txtDescripcion"+datos[c].id+"' readonly=''>"+datos[c].descripcion+"</textarea></td>"+
                //     "<td><textarea id='txtResultado"+datos[c].id+"' name='txtResultado"+datos[c].id+"'  class='form-control input-xs' data='datos'>"+datos[c].resultado+"</textarea>"+
                //     "<td><textarea class='form-control input-xs' id='txtReferencia"+datos[c].id+"'  name='txtReferencia"+datos[c].id+"' readonly=''>"+datos[c].referencia+"</textarea></td>"+
                //     "<td><textarea class='form-control input-xs' id='txtUnidad"+datos[c].id+"'  name='txtUnidad"+datos[c].id+"' readonly=''>"+datos[c].unidad+"</textarea></td>"+
                //     "<td><a href='#' onclick=\"quitarExamen('"+datos[c].id+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                // carro.push(datos[c].id);

                 $("#tbDetalle").append("<tr id='tr"+datos[c].id+"'><td>"+datos[c].tipopago+"</td><td><input type='hidden' id='txtId"+datos[c].id+"' name='txtId"+datos[c].id+"' value='"+datos[c].id+"' />"+datos[c].servicio+"</td>"+
                        "<td><input type='number' min='1' class='form-control input-xs text-right' id='txtCantidad"+datos[c].id+"' name='txtCantidad"+datos[c].id+"' value='"+ datos[c].cantidad+"'></td>"+
                        // "<td><textarea id='txtResultado"+datos[c].id+"' name='txtResultado"+datos[c].id+"' class='form-control input-xs' data='datos' ></textarea>"+
                        // "<td><textarea class='form-control input-xs' id='txtReferencia"+datos[c].id+"'  name='txtReferencia"+datos[c].id+"' readonly=''>"+datos[c].referencia+"</textarea></td>"+
                        // "<td><textarea class='form-control input-xs' id='txtUnidad"+datos[c].id+"'  name='txtUnidad"+datos[c].id+"' readonly=''>"+datos[c].unidad+"</textarea></td>"+
                        "<td><a href='#' onclick=\"quitarExamen('"+datos[c].id+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                carro.push(datos[c].id);
            }
            //$(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
            $(':input[data="datos"]').keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                console.log(key);
                if(key == 27) {
                    e.preventDefault();
                    var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
                    inputs.eq( inputs.index(this)+ 1 ).focus();
                }
            });
        }
    }); 
}

<?php
if(!is_null($reporteLab)){
    echo "agregarDetalle(".$reporteLab->id.");";
}
?>

</script>