<style>
.tr_hover{
	color:red;
}
.form-group{
    margin-bottom: 8px !important;
}
</style>
<?php
	$id = null;
/*
    if(isset($cirugia)){
      $id  = $cirugia->id;
      $fecha  =  $cirugia->fecha;
      $nombre =  $cirugia->nombre_cirugia;
      $id_paciente     =  $cirugia->paciente_id;
      $dni_paciente    =  $cirugia->paciente->dni;
      $nombre_paciente =  $cirugia->paciente->apellidopaterno.' '. $cirugia->paciente->apellidomaterno.' '. $cirugia->paciente->nombres;
      $historia_id = $cirugia->historia->id;

      $historia_numero = $cirugia->historia->numero;     
      $plan_tipo = $cirugia->plan->tipopago;
      $plan_id = $cirugia->plan_id;
      $plan_nombre = $cirugia->plan->nombre; 
      $total = $cirugia->pago_total;
      $id_medico_cabecera = $cirugia->medicoTratante_id;
      $nombre_medico_cabecera = (is_null($id_medico_cabecera) == true || $id_medico_cabecera == '0')?null:$cirugia->medico->apellidopaterno.' '. $cirugia->medico->apellidomaterno.' '. $cirugia->medico->nombres;
      $fecha_cirugia = (is_null($cirugia->fechaRealizacion)==true || 
            $cirugia->fechaRealizacion == '0000-00-00')?date('Y-m-d'):$cirugia->fechaRealizacion;
      $hora_cirugia = date('H:i',strtotime($cirugia->horaRealizacion));
    }else{
      $id = '';
      $fecha  = date('Y-m-d');
      $nombre = null;
      $id_paciente = null;
      $dni_paciente = null;
      $nombre_paciente = null;
      $historia_id  = null;
      $historia_numero  = null;
      $plan_tipo = null;
      $plan_id = null;
      $plan_nombre = null;
      $detalles = null;
      $total = null;
      $id_medico_cabecera = null;
      $nombre_medico_cabecera = null;
      $fecha_cirugia = date('Y-m-d');
      $hora_cirugia = date('H:i');
    }*/
?>


<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($modelo, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('listServicio', null, array('id' => 'listServicio')) !!}   
    {!! Form::hidden('movimiento_id', $modelo->id , array('id' => 'idMovimiento')) !!}   
   
     <div class="box">
        <div class="box-header">
            <h2 class="box-title col-lg-4 col-md-4 col-sm-4">Detalle <button type="button" class="btn btn-xs btn-info" title="Agregar Detalle" onclick="seleccionarServicioOtro();"><i class="fa fa-plus"></i></button></h2>
            {!! Form::hidden('text_movimientos', null, array('id' => 'text_movimientos')) !!}

        </div>
        <div class="box-body">
            <table class="table table-condensed table-border" id="tbDetalle">
                <thead>
                    <th class="text-center">Cant.</th>
                    <th class="text-center" colspan="2">Medico</th>
                    {{-- <th class="text-center">Rubro</th> --}}
                    <th class="text-center">Descripción</th>
                    <th class="text-center">Monto</th>
                    <th class="text-center">Subtotal</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <th class="text-right" colspan="5">Totales</th>
                    <th class="text-right">Total</th>
                    <th>{!! Form::text('total', '0.00', array('class' => 'form-control input-xs', 'id' => 'total', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
                    <th class="text-right">Queda</th>
                    <th>{!! Form::text('total_queda', $modelo->total, array('class' => 'form-control input-xs', 'id' => 'total_queda', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>

                </tfoot>
            </table>
        </div>
     </div>

     <div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listServicio\').val(carro);$(\'#movimiento_id\').val(carroDoc);guardarPago(\''.$entidad.'\', this);')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
var valorbusqueda="";
$(document).ready(function() {
	configurarAnchoModal('1200');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="totalboleta"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total_queda"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').inputmask("99999999999");
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="serieventa"]').inputmask("999");
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numeroventa"]').inputmask("99999999");
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="deducible"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="coa"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
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
                        fallecido:movie.fallecido,
                        plan_id:movie.plan_id,
                        plan:movie.plan,
                        tipo:movie.tipo
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
        if(datum.fallecido=='S'){
            alert('No puede elegir paciente fallecido');
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val('');
        }else{
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(datum.id);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').val(datum.historia);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').val(datum.dni);
            if(datum.tipopaciente=="Hospital"){
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val("Particular");
            }else{
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val(datum.tipopaciente);
            }    
            
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);

            // $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val(datum.coa);
            // $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="deducible"]').val(datum.deducible);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val(datum.plan_id);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipoplan"]').val(datum.tipo);
            console.log(datum);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').val(datum.plan);
            
            {{-- $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').focus(); --}}

            //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').typeahead('setQuery',datum.plan);
        }
    });
    
    var personas2 = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'ticket/personrucautocompletar/%QUERY',
			filter: function (personas2) {
				return $.map(personas2, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
                        ruc: movie.ruc,
                        razonsocial:movie.razonsocial,
                        direccion:movie.direccion,
                        label: movie.label,
					};
				});
			}
		}
	});
	personas2.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').typeahead(null,{
		displayKey: 'label',
		source: personas2.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val(datum.ruc);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="razon"]').val(datum.razonsocial);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="direccion"]').val(datum.direccion);
	});

    var personas4 = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'ticket/personrazonautocompletar/%QUERY',
            filter: function (personas4) {
                return $.map(personas4, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                        ruc: movie.ruc,
                        razonsocial:movie.razonsocial,
                        direccion:movie.direccion,
                        label: movie.label,
                    };
                });
            }
        }
    });
    personas4.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="razon"]').typeahead(null,{
        displayKey: 'label',
        source: personas4.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val(datum.ruc);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="razon"]').val(datum.razonsocial);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="direccion"]').val(datum.direccion);
    });
    
    var personas3 = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'medico/medicoautocompletar/%QUERY',
            filter: function (personas) {
                return $.map(personas, function (movie) {
                    return {
                        value: movie.value,
                        person_id:movie.id,
                    };
                });
            }
        }
    });
    personas3.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="medico_cabecera"]').typeahead(null,{
        displayKey: 'value',
        source: personas3.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="medico_cabecera_id"]').val(datum.person_id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="medico_cabecera"]').val(datum.value);
    });

    var personal = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit:10,
        remote: {
            url: 'employee/trabajadorautocompletar/%QUERY',
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
    personal.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="personal"]').typeahead(null,{
        displayKey: 'value',
        source: personal.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="personal_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="personal"]').val(datum.value);
    });
    

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
					};
				});
			}
		}
	});
	planes.initialize();
	// $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').typeahead(null,{
	// 	displayKey: 'value',
	// 	source: planes.ttAdapter()
	// }).on('typeahead:selected', function (object, datum) {
	// 	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').val(datum.value);
 //        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val(datum.coa);
 //        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="deducible"]').val(datum.deducible);
 //        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val(datum.id);
	// });
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="cirugia"]').focus();

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descripcion"]').on( 'keydown', function () {
        var e = window.event; 
        var keyc = e.keyCode || e.which;
        if(this.value.length>1 && keyc == 13){
            buscarServicio(this.value);
            valorbusqueda=this.value;
            this.focus();
            return false;
        }
        if(keyc == 38 || keyc == 40 || keyc == 13) {
            var tabladiv='tablaServicio';
			var child = document.getElementById(tabladiv).rows;
			var indice = -1;
			var i=0;
            $('#tablaServicio tr').each(function(index, elemento) {
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
					seleccionarServicio(seleccionado);
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

    seleccionarServicio({!! $modelo->id !!});
}); 

function guardarHistoria (entidad, idboton) {
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
			if (dat[0]!==undefined && (dat[0].respuesta=== 'OK')) {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').val(dat[0].historia);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val(dat[0].tipopaciente);
                alert('Historia Generada');
                window.open("historia/pdfhistoria?id="+dat[0].id,"_blank");
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

function guardarPago (entidad, idboton) {
    var band=true;
    var msg="";
  
    // if($("#person_id").val()==""){
    //     band = false;
    //     msg += " *No se selecciono un paciente \n";    
    // }


    // if($("#hora_cirugia").val()==""){
    //     band = false;
    //     msg += " *No se selecciono hora de Cirugia \n";    
    // }
    
    // if($("#plan_id").val()==""){
    //     band = false;
    //     msg += " *No se selecciono un plan \n";    
    // }
    // if($("#tipopaciente").val()!="Convenio"){
    //     for(c=0; c < carro.length; c++){
    //         if($("#txtIdTipoServicio"+carro[c]).val()!="1" && $("#txtIdTipoServicio"+carro[c]).val()!="6" && $("#txtIdTipoServicio"+carro[c]).val()!="7" && $("#txtIdTipoServicio"+carro[c]).val()!="8" && $("#txtIdTipoServicio"+carro[c]).val()!="12"){
    //             if($("#referido_id").val()=="0"){
    //                 band = false;
    //                 msg += " *Debe indicar referido \n";
    //             }
    //         }
    //     }  
    // }

    var c_s='';
    for(c=0; c < carro.length; c++){
        if(carro[c] != ''){
            var aux_s = carro[c]+',';
            if(c_s == ''){
                c_s = aux_s;
            }else{
                c_s = c_s+aux_s;
            }
        }


        // if($("#txtDescuento"+carro[c]).val()==""){
        //     band = false;
        //     msg += " *Descuento no puede ser vacio \n";            
        // }
        if($("#txtIdMedico"+carro[c]).val()==0){
            band = false;
            msg += " *Debe seleccionar medico \n";                        
        }
        // var hospital = parseFloat($("#txtPrecioHospital"+carro[c]).val());
        var doctor = parseFloat($("#txtPrecioMedico"+carro[c]).val());
        var precio = parseFloat($("#txtPrecio"+carro[c]).val());
        var cantidad = parseFloat($("#txtCantidad"+carro[c]).val());
        if((cantidad*precio) < 0){
            band = false;
            msg += " *El Monto no puede ser negativo \n";        
        }


        // if($("#cboDescuento").val()=="P"){
        //     precio = Math.round(100*(precio*(100-desc)/100))/100;
        // }else{
        //     precio = precio - desc;
        // }
        // if((hospital + doctor) != precio){
        //     band = false;
        //     msg += " *Suma de pago hospital + doctor no coincide con el precio \n";
        // }      
    } 

    if(carro == '' || carro == null){   
        band = false;
        msg += " *No se han indicado detalles de la cirugia \n";        
    }else{
        $('#text_movimientos').val(c_s)
    }

    // if(parseFloat($("#total").val())>700){
    //     if($("#dni").val().trim().length!=8){
    //         band = false;
    //         msg += " *El paciente debe tener DNI correcto \n";
    //     }
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
                    buscar('{{ $entidad }}');
                     // buscarCompaginado('', 'Accion realizada correctamente', entidad, 'OK');
                    // if(dat[0].pagohospital!="0"){
                    //     window.open('/juanpablo/ticket/pdfComprobante?ticket_id='+dat[0].ticket_id,'_blank')
                    // }else{
                    //     window.open('/juanpablo/ticket/pdfPrefactura?ticket_id='+dat[0].ticket_id,'_blank')
                    // }
                    // if(dat[0].notacredito_id!="0"){
                    //     window.open('/juanpablo/notacredito/pdfComprobante?id='+dat[0].notacredito_id,'_blank');
                    // }
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

function validarFormaPago(forma){
    if(forma=="Tarjeta"){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} div[id="divTarjeta"]').css("display","");
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} div[id="divTarjeta"]').css("display","none");
    }
}

var valorinicial="";
function buscarServicio(valor){
    //if(valorinicial!=valor){valorinicial=valor;
        $.ajax({
            type: "POST",
            url: "ticket/buscarservicio",
            data: "idtiposervicio="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tiposervicio"]').val()+"&descripcion="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descripcion"]').val()+"&tipopaciente="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val()+"&plan_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                datos=JSON.parse(a);
                $("#divBusqueda").html("<table class='table table-bordered table-condensed table-hover' border='1' id='tablaServicio'><thead><tr><th class='text-center'>TIPO</th><th class='text-center'>SERVICIO</th><th class='text-center'>P. UNIT.</tr></thead></table>");
                var pag=parseInt($("#pag").val());
                var d=0;
                for(c=0; c < datos.length; c++){
                    var a="<tr id='"+datos[c].idservicio+"' onclick=\"seleccionarServicio('"+datos[c].idservicio+"')\"><td align='center'>"+datos[c].tiposervicio+"</td><td>"+datos[c].servicio+"</td><td align='right'>"+datos[c].precio+"</td></tr>";
                    $("#tablaServicio").append(a);           
                }
                $('#tablaServicio').DataTable({
                    "scrollY":        "250px",
                    "scrollCollapse": true,
                    "paging":         false
                });
                $('#tablaServicio_filter').css('display','none');
                $("#tablaServicio_info").css("display","none");
    	    }
        });
    //}
}

var carro = new Array();
var carroDoc = new Array();
var copia = new Array();

function seleccionarServicio(idcirugia){
    var band=true;
    // if(carro != ''){    
    //     for(c=0; c < carro.length; c++){
    //         if(carro[c]==idcirugia){
    //             band=false;
    //         }      
    //     }
    // }
    if(band){
        $.ajax({
            type: "POST",
            url:  "garantia/seleccionardetalles",
            data: "idmov="+idcirugia+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                // datos=JSON.parse(a);
                // console.log('datos',a);
                for( var c= 0; c < a.length; c++){
                    // console.log(a[c].id);
                   var idservicio = a[c].id;
                    $("#tbDetalle").append("<tr id='tr"+idservicio+"' class='simple_pagar' ><td><input type='hidden' id='txtIdTipoServicio"+idservicio+"' name='txtIdTipoServicio"+idservicio+"' value='0' /><input type='text' data='numero' class='form-control input-xs' id='txtCantidad"+idservicio+"' name='txtCantidad"+idservicio+"' style='width: 40px;' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem2('"+idservicio+"')\" /></td>"+
				        "<td><input type='checkbox' id='chkCopiar"+idservicio+"' onclick=\"checkMedico(this.checked,'"+idservicio+"')\" /></td>"+
				        "<td><input type='text' class='form-control input-xs' id='txtMedico"+idservicio+"' name='txtMedico"+idservicio+"' /><input type='hidden' id='txtIdMedico"+idservicio+"' name='txtIdMedico"+idservicio+"' value='0' /></td>"+
				        "<td><textarea class='form-control input-xs' id='txtServicio"+idservicio+"' name='txtServicio"+idservicio+"' /></td>"+
				        "<td><input type='hidden' id='txtPrecio2"+idservicio+"' name='txtPrecio2"+idservicio+"' value='' min='0' /><input type='text' size='5' class='form-control input-xs' style='width: 60px;' data='numero' id='txtPrecio"+idservicio+"' name='txtPrecio"+idservicio+"' value='' min='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem2('"+idservicio+"')}\" onblur=\"calcularTotalItem2('"+idservicio+"')\" /></td>"+
				        "<td><input type='text' style='width: 60px;' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+idservicio+"' id='txtTotal"+idservicio+"' value=0' /></td>"+
				        "<td><input type='hidden' name='txtPagado"+idservicio+"' id='txtPagado"+idservicio+"' value='N' ><a href='#' onclick=\"quitarServicio('"+idservicio+"')\" data-role='button' id='quitar"+idservicio+"'><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i><a href='#' id='pagar"+idservicio+"' onclick=\"pagarServicio('"+idservicio+"')\" ><i class='fa fa-strikethrough' title='Pagar' style='margin-left:10px;color:green;' width='20px' height='20px'></i><a href='#' id='imprimir"+idservicio+"' onclick=\"imprimirServicio('"+idservicio+"')\"><i class='fa fa-file-text' title='Imprimir' style='margin-left:10px;color:teal;' width='20px' height='20px'></i></td></tr>");

                    carro.push(idservicio);
                    $("#txtCantidad"+a[c].id).val(a[c].cantidad);
                    $("#txtIdMedico"+a[c].id).val(a[c].doctor_id);
                    $("#txtMedico"+a[c].id).val(a[c].doctor);
                    $("#txtServicio"+a[c].id).val(a[c].descripcion);
                    $("#txtPrecio"+a[c].id).val(a[c].monto);
                    $('#txtTotal'+a[c].id).val(a[c].subTotal);
                    $('#txtPagado'+a[c].id).val(a[c].situacion);
                    
                    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
   
                    var id = idservicio;
                    calcularTotal();
                    if(a[c].situacion === 'S'){
                    	$('#tr'+id).css('background-color','lightgreen');		
						// pagarServicio(id);
						$('#txtPagado'+a[c].id).val('S');
                   	 	$("#txtPrecio"+a[c].id).attr('disabled','true');
                   	 	$("#txtCantidad"+a[c].id).attr('disabled','true');
                  	 	$("#txtMedico"+a[c].id).attr('disabled','true');
                  	 	$("#quitar"+a[c].id).remove();
                  	 	$("#pagar"+a[c].id).remove();
                    }else{
                  	 	$("#imprimir"+a[c].id).remove();
                    }

                    eval("var planes"+id+" = new Bloodhound({"+
                        "datumTokenizer: function (d) {"+
                            "return Bloodhound.tokenizers.whitespace(d.value);"+
                        "},"+
                        "limit: 10,"+
                        "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
                        "remote: {"+
                            "url: 'medico/medicoautocompletar/%QUERY',"+
                            "filter: function (planes"+id+") {"+
                                "return $.map(planes"+id+", function (movie) {"+
                                    "return {"+
                                        "value: movie.value,"+
                                        "id: movie.id,"+
                                    "};"+
                                "});"+
                            "}"+
                        "}"+
                    "});"+
                    "planes"+id+".initialize();"+
                    "$('#txtMedico"+id+"').typeahead(null,{"+
                        "displayKey: 'value',"+
                        "source: planes"+id+".ttAdapter()"+
                    "}).on('typeahead:selected', function (object, datum) {"+
                        "$('#txtMedico"+id+"').val(datum.value);"+
                        "$('#txtIdMedico"+id+"').val(datum.id);"+
                        "copiarMedico('"+id+"');"+
                    "});");
                    // $("#txtMedico"+idservicio).focus();
                    {{-- if({{$user->usertype_id)}} !='4'){ --}}
                        // $("#txtServicio"+a[c].id).attr('disabled','true');
                        // $("#txtPrecio"+a[c].id).attr('disabled','true');                        
                    // }
                }
            }
        });
    }else{
        $('#txtMedico'+idcirugia).focus();
    }
}

function pagarServicio(id){
	// alert(id);
	if($('#tr'+id).hasClass('simple_pagar')){
		$('#tr'+id).removeClass('simple_pagar');
		if($('#txtIdMedico'+id).val()>0 && $('#txtPrecio'+id).val() > 0 ){
			$('#txtPagado'+id).val('S');
			$('#tr'+id).css('background-color','lightgreen');		

			window.open("garantia/pdfRecibo?id="+id+"&pagado=S", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=500,left=500,width=800,height=1200");			
		}
	}else{
		$('#tr'+id).css('background-color','#fff');
		$('#tr'+id).addClass('simple_pagar');
		$('#txtPagado'+id).val('N');
	}

	// alert($('#txtPagado'+id).val());
}

function imprimirServicio(idservicio){
	window.open("garantia/pdfRecibo?id="+idservicio, "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=500,left=500,width=800,height=1200");
}

function seleccionarServicioOtro(){
    var idservicio = "0"+Math.round(Math.random()*100);
    $("#tbDetalle").append("<tr id='tr"+idservicio+"' class='simple_pagar' ><td><input type='hidden' id='txtIdTipoServicio"+idservicio+"' name='txtIdTipoServicio"+idservicio+"' value='0' /><input type='text' data='numero' class='form-control input-xs' id='txtCantidad"+idservicio+"' name='txtCantidad"+idservicio+"' style='width: 40px;' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem2('"+idservicio+"')\" /></td>"+
        "<td><input type='checkbox' id='chkCopiar"+idservicio+"' onclick=\"checkMedico(this.checked,'"+idservicio+"')\" /></td>"+
        "<td><input type='text' class='form-control input-xs' id='txtMedico"+idservicio+"' name='txtMedico"+idservicio+"' /><input type='hidden' id='txtIdMedico"+idservicio+"' name='txtIdMedico"+idservicio+"' value='0' /></td>"+
        "<td><textarea class='form-control input-xs' id='txtServicio"+idservicio+"' name='txtServicio"+idservicio+"' /></td>"+
        "<td><input type='hidden' id='txtPrecio2"+idservicio+"' name='txtPrecio2"+idservicio+"' value='' min='0' /><input type='text' size='5' class='form-control input-xs' style='width: 60px;' data='numero' id='txtPrecio"+idservicio+"' name='txtPrecio"+idservicio+"' value='' min='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem2('"+idservicio+"')}\" onblur=\"calcularTotalItem2('"+idservicio+"')\" /></td>"+
        "<td><input type='text' style='width: 60px;' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+idservicio+"' id='txtTotal"+idservicio+"' value=0' /></td>"+
        "<td><input type='hidden' name='txtPagado"+idservicio+"' id='txtPagado"+idservicio+"' value='N' ><a href='#' onclick=\"quitarServicio('"+idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i><a href='#' onclick=\"pagarServicio('"+idservicio+"')\" ><i class='fa fa-strikethrough' title='Pagar' style='margin-left:10px;color:green;' width='20px' height='20px'></i><a href='#' onclick=\"imprimirServicio('"+idservicio+"')\"><i class='fa fa-file-text' title='Imprimir' style='margin-left:10px;color:teal;' width='20px' height='20px' id='pagar"+idservicio+"'></i></td></tr>");
    
    carro.push(idservicio);
    $('#pagar'+idservicio).remove();
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    eval("var planes"+idservicio+" = new Bloodhound({"+
		"datumTokenizer: function (d) {"+
			"return Bloodhound.tokenizers.whitespace(d.value);"+
		"},"+
        "limit: 10,"+
		"queryTokenizer: Bloodhound.tokenizers.whitespace,"+
		"remote: {"+
			"url: 'medico/medicoautocompletar/%QUERY',"+
			"filter: function (planes"+idservicio+") {"+
                "return $.map(planes"+idservicio+", function (movie) {"+
					"return {"+
						"value: movie.value,"+
						"id: movie.id,"+
					"};"+
				"});"+
			"}"+
		"}"+
	"});"+
	"planes"+idservicio+".initialize();"+
	"$('#txtMedico"+idservicio+"').typeahead(null,{"+
		"displayKey: 'value',"+
		"source: planes"+idservicio+".ttAdapter()"+
	"}).on('typeahead:selected', function (object, datum) {"+
		"$('#txtMedico"+idservicio+"').val(datum.value);"+
        "$('#txtIdMedico"+idservicio+"').val(datum.id);"+
        "copiarMedico('"+idservicio+"');"+
	"});");
    $("#txtMedico"+idservicio).focus();             
}

var sobrante = $('#total_queda').val();

function calcularTotal(){
    var total2=0;
    for(c=0; c < carro.length; c++){
        var tot=parseFloat($("#txtTotal"+carro[c]).val());
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#total").val(total2);
    var total2=0;
    for(c=0; c < carro.length; c++){
        var tot=Math.round(100*(parseFloat($("#txtPrecioHospital"+carro[c]).val())*parseFloat($("#txtCantidad"+carro[c]).val())))/100;
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#totalboleta").val(total2);
    // sobrante = ;
    $('#total_queda').val(sobrante - $('#total').val());
}

function calcularCoaseguro(){
    if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val()!="" && parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val())>0){
        var ded=parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val());
        if(ded==100){
            ded=0;
        }
    }else{
        var ded=100;
    }
    for(c=0; c < carro.length; c++){
        if($("#txtIdTipoServicio"+carro[c]).val()!="1" && $("#txtIdTipoServicio"+carro[c]).val()!="0"){//Para todo lo q no es consulta
            var cant=parseFloat($("#txtCantidad"+carro[c]).val());
            var pv=parseFloat($("#txtPrecio2"+carro[c]).val());
            var tot=parseFloat($("#txtTotal"+carro[c]).val());
            var precio = Math.round((pv*ded/100)*100)/100;
            var hospital = Math.round((parseFloat($("#txtPrecioHospital2"+carro[c]).val())*ded/100)*100)/100;
            var medico = Math.round((parseFloat($("#txtPrecioMedico2"+carro[c]).val())*ded/100)*100)/100;
            $("#txtPrecio"+carro[c]).val(precio);  
            var desc=parseFloat($("#txtDescuento"+carro[c]).val());
            if($("#cboDescuento").val()=="P"){
                pv = Math.round(100*(pv * (100 - desc)/100))/100;
                hospital = Math.round(100*(hospital * (100 - desc)/100))/100;
                medico = Math.round(100*(medico * (100 - desc)/100))/100;
            }else{
                pv = pv - desc;
                medico = medico - desc;
            }
            var total=Math.round((pv*cant*ded/100) * 100) / 100;
            $("#txtTotal"+carro[c]).val(total);  
            $("#txtPrecioHospital"+carro[c]).val(hospital);
            $("#txtPrecioMedico"+carro[c]).val(medico);
        }else{
            if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()!="6" && $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val()=="Convenio"){
                var cant=parseFloat($("#txtCantidad"+carro[c]).val());
                var pv=parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="deducible"]').val());
                var tot=parseFloat($("#txtTotal"+carro[c]).val());
                var total=Math.round((pv*cant) * 100) / 100;
                $("#txtTotal"+carro[c]).val(total);  
                $("#txtPrecio"+carro[c]).val(pv);  
                $("#txtPrecioHospital"+carro[c]).val(pv);
                var medico = parseFloat($("#txtPrecioMedico2"+carro[c]).val());
                $("#txtPrecioMedico"+carro[c]).val(medico);
            }
        }
    }
    calcularTotal();
}

function calcularTotalItem(id){
    var cant=parseFloat($("#txtCantidad"+id).val());
    var pv=parseFloat($("#txtPrecio"+id).val());
    var pv2=parseFloat($("#txtPrecio2"+id).val());
    var desc=parseFloat($("#txtDescuento"+id).val());
    var hosp=parseFloat($("#txtPrecioHospital"+id).val());
    if($("#cboDescuento").val()=="P"){
        pv = Math.round(100*(pv * (100 - desc)/100))/100;
    }else{
        pv = pv - desc;
    }
    if($("#txtIdTipoServicio"+id).val()!="1" && $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()!="6"){
        if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val()!=""){
            var ded=parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val());
            if(ded==100){
               ded=0;
            }else if(ded==0){
                ded=100;
            }
        }else{
            var ded=100;
        }
    }else{
        var ded = 100;
    }
    if(ded>0 && ded<100){
        pv = pv2;
    }
    pv=Math.round((pv*ded/100) * 100) / 100;
    var total=Math.round((pv*cant) * 100) / 100;
    var med = Math.round((parseFloat($("#txtPrecioMedico2"+id).val())*ded/100)*100)/100;

    if($("#txtIdTipoServicio"+id).val()!="1"){
        $("#txtTotal"+id).val(total);   
        if(med==0){
            var hos=pv - med;
            $("#txtPrecioHospital"+id).val(hos);    
        }
        $("#txtPrecioMedico"+id).val(med);
    }else if($("#txtIdTipoServicio"+id).val()=="1" && $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()=="6"){
        $("#txtTotal"+id).val(total);
        med = pv - hosp;
        $("#txtPrecioMedico"+id).val(med);
    }
    calcularTotal();
}

function calcularTotalItem2(id){
    var cant=parseFloat($("#txtCantidad"+id).val());
    var pv=parseFloat($("#txtPrecio"+id).val());
    // var desc=parseFloat($("#txtDescuento"+id).val());
    // var hosp=parseFloat($("#txtPrecioHospital"+id).val());
    // if($("#cboDescuento").val()=="P"){
    //     pv = Math.round(100*(pv * (100 - desc)/100))/100;
    // }else{
    //     pv = pv - desc;
    // }

    pv = Math.round(100*(cant*pv))/100;
    $("#txtTotal"+id).val(pv);   
    


    /*if($("#txtIdTipoServicio"+id).val()!="1" && $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()!="6"){
        if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val()!=""){
            var ded=parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val());
            if(ded==100){
               ded=0;
            }else if(ded==0){
                ded=100;
            }
        }else{
            var ded=100;
        }
    }else{*/
        // var ded = 100;
    //}
    // var total=Math.round((pv*cant*ded/100) * 100) / 100;
    // var med = pv - hosp;
    // if($("#txtIdTipoServicio"+id).val()!="1"){
    //     $("#txtTotal"+id).val(total);   
    //     $("#txtPrecioMedico"+id).val(med);
    // }else if($("#txtIdTipoServicio"+id).val()=="1" && $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()=="6"){
    //     $("#txtTotal"+id).val(total);
    //     $("#txtPrecioMedico"+id).val(med);
    // }
    calcularTotal();
}

function quitarServicio(id){
    $("#tr"+id).remove();
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
    calcularTotal();
}

function mostrarDatoCaja(check,check2){
    if(check==0){
        check = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="pago"]').is(":checked");
    }
    if(check2==0){
        check2 = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="boleta"]').is(":checked");
    }
    if(check2){//CON BOLETA
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="comprobante"]').val('S');
        $(".datocaja").css("display","");
        if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="tipodocumento"]').val()=="Factura"){
            $(".datofactura").css("display","");
        }else{
            $(".datofactura").css("display","none");
        }
        if(check){
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="pagar"]').val('S');
            $(".caja").css("display","");
            $(".descuento").css("display","none");
            $(".descuentopersonal").css('display','none');
            $("#descuentopersonal").val('N');
        }else{
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="pagar"]').val('N');
            $(".caja").css("display","none");
            $(".descuento").css("display","");
        }
        validarFormaPago($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="formapago"]').val());
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="comprobante"]').val('N');
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="pagar"]').val('N');
        $(".datocaja").css("display","none");
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="pago"]').attr("checked",true);
        validarFormaPago($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="formapago"]').val());
    }
}

function boletearTodoCaja(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="boletear"]').val('S');
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="boletear"]').val('N');
    }
}

function editarPrecio(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="editarprecio"]').val('S');
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="editarprecio"]').val('N');
    }
    for(c=0; c < carro.length; c++){
        if(check) {
            //$("#txtPrecio"+carro[c]).removeAttr("readonly");
            $("#txtPrecioHospital"+carro[c]).removeAttr("readonly");
            $("#txtPrecioMedico"+carro[c]).removeAttr("readonly");
        }else{
            //$("#txtPrecio"+carro[c]).attr("readonly","true");
            $("#txtPrecioHospital"+carro[c]).attr("readonly","true");
            $("#txtPrecioMedico"+carro[c]).attr("readonly","true");
        }
    }
}

function generarNumero(){
    $.ajax({
        type: "POST",
        url: "ticket/generarNumero",
        data: "tipodocumento="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="tipodocumento"]').val()+"&serie="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="serieventa"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="numeroventa"]').val(a);
            if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="tipodocumento"]').val()=="Factura"){
                $(".datofactura").css("display","");
            }else{
                $(".datofactura").css("display","none");
            }
        }
    });
}

function Soat(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="soat"]').val('S');
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="soat"]').val('N');
    }
}

function checkMedico(check,idservicio){
    // if(check){
    //     copia.push(idservicio);
    // }else{
    //     for(c=0; c < copia.length; c++){
    //         if(copia[c]==idservicio){
    //             copia.splice(c,1);
    //         }
    //     }
    //     $("#txtIdMedico"+idservicio).val(0);
    //     $("#txtMedico"+idservicio).val("");
    //     $("#txtMedico"+idservicio).focus();
    // }
}

function copiarMedico(idservicio){
    // if($("#chkCopiar"+idservicio).is(":checked")){
    //     for(c=0; c < copia.length; c++){
    //         $("#txtIdMedico"+copia[c]).val($("#txtIdMedico"+idservicio).val());
    //         $("#txtMedico"+copia[c]).val($("#txtMedico"+idservicio).val());
    //     }
    // }
}

function editarDescuentoPersonal(check){
    if(check){
        $(".descuentopersonal").css('display','');
        $("#descuentopersonal").val('S');
    }else{
        $(".descuentopersonal").css('display','none');
        $("#descuentopersonal").val('N');
    }
}

function movimientoRef(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimientoref"]').val('S');
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento"]').css("display","");
        $('#tbDoc').css("display","");
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento_id"]').val(0);
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimientoref"]').val('N');
        $('#tbDoc').css("display","none");
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento"]').css("display","none");
    }
}

var numeroref = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit:10,
        remote: {
            url: 'ventaadmision/ventaautocompletar/%QUERY',
            filter: function (docs) {
                return $.map(docs, function (movie) {
                    return {
                        value: movie.value2,
                        id: movie.id,
                        paciente: movie.paciente,
                        person_id:movie.person_id,
                        num:movie.value,
                        total:movie.total,
                    };
                });
            }
        }
    });
    numeroref.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento"]').typeahead(null,{
        displayKey: 'value',
        source: numeroref.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento_id"]').val(datum.id);
        //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento"]').val(datum.value);
        $("#tbDoc").append("<tr id='trDoc"+datum.id+"'><td align='left'>"+datum.num+"</td><td id='tdTotalDoc"+datum.id+"' align='center'>"+datum.total+"<td><td><a href='#' onclick=\"quitarDoc('"+datum.id+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
        carroDoc.push(datum.id);
        calcularTotalDoc();
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento"]').val('');
    });

function quitarDoc(id){
    $("#trDoc"+id).remove();
    for(c=0; c < carroDoc.length; c++){
        if(carroDoc[c] == id) {
            carroDoc.splice(c,1);
        }
    }
    calcularTotalDoc();
}

function calcularTotalDoc(){
    var total2=0;
    for(c=0; c < carroDoc.length; c++){
        var tot=parseFloat($("#tdTotalDoc"+carroDoc[c]).html());
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#totalDoc").html(total2);
}

</script>