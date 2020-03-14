<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($venta, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('total', '0', array( 'id' => 'total')) !!}
	<div class="col-lg-4 col-md-4 col-sm-4">
		<div class="form-group" style="height: 12px;">
			{!! Form::label('tipoventa', 'Tipo Venta:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::select('tipoventa', $cboTipoventa, null, array('style' => 'background-color: rgb(25,241,227);' ,'class' => 'form-control input-xs', 'id' => 'tipoventa', 'onchange' => 'cambiotipoventa();')) !!}
			</div>
		</div>
		
		<div class="form-group" style="height: 12px;">
			{!! Form::label('nombrepersona', 'Cliente:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('person_id', null, array('id' => 'person_id')) !!}
			{!! Form::hidden('empresa_id', null, array('id' => 'empresa_id')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('nombrepersona', null, array('style' => 'background-color: rgb(252,215,147);' ,'class' => 'form-control input-xs', 'id' => 'nombrepersona', 'placeholder' => 'Seleccione Cliente')) !!}
				
			</div>
		</div>
		<div class="form-group" style="display: none">
    		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
    		<div class="col-lg-7 col-md-7 col-sm-7">
            {!-- Form::hidden('person_id', null, array('id' => 'person_id')) --!}
    			{!! Form::text('paciente', null, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Paciente')) !!}
    		</div>
    	</div>
		<div class="form-group" id="divConvenio" style="height: 12px;">
			{!! Form::label('conveniofarmacia', 'Convenio:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('conveniofarmacia_id', null, array('id' => 'conveniofarmacia_id')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('conveniofarmacia', null, array('class' => 'form-control input-xs', 'id' => 'conveniofarmacia', 'placeholder' => 'Seleccione Convenio')) !!}
				
			</div>
		</div>
		<div class="form-group" id="divDescuentokayros" style="height: 12px;">
			{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
			<div class="col-lg-3 col-md-3 col-sm-3">
				<div class='input-group input-group-xs' id='divfecha'>
					{!! Form::text('fecha', date('d/m/Y'), array('class' => 'form-control input-xs', 'id' => 'fecha', 'placeholder' => 'Ingrese fecha')) !!}
					
				</div>
			</div>
			
			{!! Form::label('descuentokayros', 'Dcto. Kayros:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-2 col-md-2 col-sm-2">
				{!! Form::text('descuentokayros', null, array('class' => 'form-control input-xs', 'id' => 'descuentokayros')) !!}
				
			</div>	
		</div>
		<div class="form-group" style="height: 12px;">
		<div class="col-lg-7 col-md-7 col-sm-7">
				
				
			</div>
		{!! Form::label('copago', 'Copago:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
			<div class="col-lg-2 col-md-2 col-sm-2">
				{!! Form::text('copago', null, array('class' => 'form-control input-xs', 'id' => 'copago')) !!}
				
			</div>

		</div>
	</div>
	<div class="col-lg-8 col-md-8 col-sm-8">
		<div class="form-group" style="height: 12px;">
			{!! Form::label('nombreproducto', 'Producto:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
			<div class="col-lg-4 col-md-4 col-sm-4">
				{!! Form::text('nombreproducto', null, array('class' => 'form-control input-xs', 'id' => 'nombreproducto', 'placeholder' => 'Ingrese nombre','onkeypress' => '')) !!}
			</div>
			{!! Form::label('cantidad', 'Cantidad:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
			<div class="col-lg-2 col-md-2 col-sm-2">
				{!! Form::text('cantidad', null, array('class' => 'form-control input-xs', 'id' => 'cantidad')) !!}
			</div>
			{!! Form::hidden('producto_id', null, array( 'id' => 'producto_id')) !!}
			{!! Form::hidden('preciokayros', null, array( 'id' => 'preciokayros')) !!}

			{!! Form::hidden('precioventa', null, array('id' => 'precioventa')) !!}
			{!! Form::hidden('stock', null, array('id' => 'stock')) !!}
		</div>
		<div class="form-group" id="divProductos" style="overflow:auto; height:180px; padding-right:10px; border:1px outset">
			
		</div>

		<div class="form-group">
			<div class="col-lg-12 col-md-12 col-sm-12 text-right">
				<!--<div align="center" class="col-lg-3 ">
		       {-- Form::button('<i class="glyphicon glyphicon-plus"></i> Agregar', array('class' => 'btn btn-info btn-xs', 'id' => 'btnAgregar', 'onclick' => 'ventanaproductos();')) --}   
		    	
		    	</div>-->
				{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardarVenta(\''.$entidad.'\', this)')) !!}
				{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
			</div>
		</div>
		
	</div>
	<div class="form-group" style="display: none;">
		<div class="col-lg-12 col-md-12 col-sm-12" >
			{!! Form::label('codigo', 'Comprobar Productos:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-5 col-md-5 col-sm-5">
				{!! Form::text('codigo', null, array('class' => 'form-control input-xs', 'id' => 'codigo', 'placeholder' => 'Ingrese codigo')) !!}
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12">
			<div id="divDetail" class="table-responsive" style="overflow:auto; height:220px; padding-right:10px; border:1px outset">
		        <table style="width: 100%;" class="table-condensed table-striped">
		            <thead>
		                <tr>
		                    <th bgcolor="#E0ECF8" class='text-center'>Producto</th>
		                    <th bgcolor="#E0ECF8" class='text-center'>Cantidad</th>
		                    <th bgcolor="#E0ECF8" class="text-center">Precio</th>
		                    <th bgcolor="#E0ECF8" class="text-center">Subtotal</th>
		                    <th bgcolor="#E0ECF8" class='text-center'>Quitar</th>                            
		                </tr>
		            </thead>
		           
		        </table>
		    </div>
		</div>
	 </div>
    <br>
	
	
{!! Form::close() !!}
<style type="text/css">
tr.resaltar {
    background-color: #A9F5F2;
    cursor: pointer;
}
</style>
<script type="text/javascript">
var valorbusqueda="";
var indice = -1;
var anterior = -1;
$(document).ready(function() {
	configurarAnchoModal('1300');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');

		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });

		
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fecha"]').inputmask("dd/mm/yyyy");
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fecha"]').datetimepicker({
			pickTime: false,
			language: 'es'
		});

	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="codigo"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				comprobarproducto ();
			}
		});

	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="nombrepersona"]').focus(function(){
			var documento = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="documento"]').val();
			if (documento == 4) {
				modal('{{URL::route('venta.busquedaempresa')}}', '');
			}else{
				modal('{{URL::route('venta.busquedacliente')}}', '');
			}
			
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="conveniofarmacia"]').focus(function(){
			abrirconvenios();
		});

	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="descuentokayros"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="copago"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="preciokayros"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="cantidad"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				/*e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();*/
				addpurchasecart();
				$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="nombreproducto"]').val('');
				$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="cantidad"]').val('');
				indice = -1;
				$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="nombreproducto"]').focus();
			}
		});


	var personas = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
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
        
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);
	});

	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombreproducto"]').on( 'keydown', function () {
        var e = window.event; 
        var keyc = e.keyCode || e.which;
        console.log(this.value);
        console.log(valorbusqueda);
        if(this.value.length>2 && keyc == 13 && valorbusqueda!=this.value){
            buscarProducto(this.value);
            valorbusqueda=this.value;
            this.focus();
            return false;
        }
        if(keyc == 38 || keyc == 40 || keyc == 13) {
            var tabladiv='tablaProducto';
			var child = document.getElementById(tabladiv).rows;
			//var indice = -1;
			var i=0;
            /*$('#tablaProducto tr').each(function(index, elemento) {
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
    		});*/		 
			// return
			if(keyc == 13) {        				
			     if(indice != -1){
					var seleccionado = '';			 
					if(child[indice].id) {
					   seleccionado = child[indice].id;
					} else {
					   seleccionado = child[indice].id;
					}		 		
					seleccionarProducto(seleccionado);
				}
			} else {
				// abajo
				if(keyc == 40) {
					if(indice == (child.length - 1) ) {
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

				if (indice != -1) {
					var element = '#'+child[indice].id;
					$(element).addClass("resaltar");
					if (anterior  != -1) {
						element = '#'+anterior;
						$(element).removeClass("resaltar");
					}
					anterior = child[indice].id;
				}
			}
        }
    });


	cambiotipoventa();

}); 

var valorinicial="";
function buscarProducto(valor){
    if(valorinicial!=valor){valorinicial=valor;
        $.ajax({
            type: "POST",
            url: "venta/buscandoproducto",
            data: "nombre="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombreproducto"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                datos=JSON.parse(a);
                //$("#divProductos").html("<table class='table table-bordered table-condensed table-hover' border='1' id='tablaProducto'><thead><tr><th class='text-center'>P. Activo</th><th class='text-center'>Nombre</th><th class='text-center'>Presentacion</th><th class='text-center'>Stock</th><th class='text-center'>P.Kayros</th><th class='text-center'>P.Venta</th></tr></thead></table>");
                $("#divProductos").html("<table class='table-condensed table-hover' border='1' id='tablaProducto'><thead><tr><th class='text-center' style='width:220px;'><span style='display: block; font-size:.9em'>P. Activo</span></th><th class='text-center' style='width:220px;'><span style='display: block; font-size:.9em'>Nombre</span></th><th class='text-center' style='width:70px;'><span style='display: block; font-size:.9em'>Presentacion</span></th><th class='text-center' style='width:20px;'><span style='display: block; font-size:.9em'>Stock</span></th><th class='text-center' style='width:20px;'><span style='display: block; font-size:.9em'>P.Kayros</span></th><th class='text-center' style='width:20px;'><span style='display: block; font-size:.9em'>P.Venta</span></th></tr></thead></table>");
                var pag=parseInt($("#pag").val());
                var d=0;
                for(c=0; c < datos.length; c++){
                    var a="<tr id='"+datos[c].idproducto+"' onclick=\"seleccionarProducto('"+datos[c].idproducto+"')\"><td align='center'><span style='display: block; font-size:.7em'>"+datos[c].principio+"</span></td><td><span style='display: block; font-size:.7em'>"+datos[c].nombre+"</span></td><td align='right'><span style='display: block; font-size:.7em'>"+datos[c].presentacion+"</span></td><td align='right'><span style='display: block; font-size:.7em'>"+datos[c].stock+"</span></td><td align='right'><span style='display: block; font-size:.7em'>"+datos[c].preciokayros+"</span></td><td align='right'><span style='display: block; font-size:.7em'>"+datos[c].precioventa+"</span></td></tr>";
                    $("#tablaProducto").append(a);           
                }
                $('#tablaProducto').DataTable({
                    "scrollY":        "250px",
                    "scrollCollapse": true,
                    "paging":         false,
                    "ordering"        :false
                });
                $('#tablaProducto_filter').css('display','none');
                $("#tablaProducto_info").css("display","none");
    	    }
        });
    }
}

function seleccionarProducto(idproducto){
	//alert(idproducto);
	var _token =$('input[name=_token]').val();
	$.post('{{ URL::route("venta.consultaproducto")}}', {idproducto: idproducto,_token: _token} , function(data){
		//$('#divDetail').html(data);
		//calculatetotal();
		var datos = data.split('-');
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="producto_id"]').val(datos[0]);
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="preciokayros"]').val(datos[1]);
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="precioventa"]').val(datos[2]);
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="stock"]').val(datos[3]);
	});
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="cantidad"]').focus();

}

function ventanaproductos() {
	var tipoventa = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipoventa"]').val();
	var descuentokayros = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descuentokayros"]').val();
	var copago = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="copago"]').val();
	modal('{{URL::route('venta.buscarproducto')}}'+'?tipoventa='+tipoventa+'&descuentokayros='+descuentokayros+'&copago='+copago, '');
}


function abrirconvenios() {
	modal('{{URL::route('venta.buscarconvenio')}}', '');
}


function generarNumero(valor){
    $.ajax({
        type: "POST",
        url: "venta/generarNumero",
        data: "tipodocumento_id="+valor+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="numerodocumento"]').val(a);
        }
    });
    if (valor == 4) {
		modal('{{URL::route('venta.busquedaempresa')}}', '');
	}else{
		modal('{{URL::route('venta.busquedacliente')}}', '');
	}    
}


function setValorFormapago (id, valor) {
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="' + id + '"]').val(valor);
}

function getValorFormapago (id) {
	var valor = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="' + id + '"]').val();
	return valor;
}

function cambiotipoventa() {
	var tipoventa = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipoventa"]').val();
	if (tipoventa == 'C') {
		//$('#divConvenio').show();
		//$('#divDescuentokayros').show();
		//$('#divCopago').show();
		modal('{{URL::route('venta.busquedacliente')}}', '');

	}else if (tipoventa == 'N') {
		$('#divConvenio').hide();
		//$('#divDescuentokayros').hide();
		//$('#divCopago').hide();
	}
}

function generarSaldototal () {
	var total = retornarFloat(getValorFormapago('total'));
	var inicial = retornarFloat(getValorFormapago('inicial'));
	var saldototal = (total - inicial).toFixed(2);
	if (saldototal < 0.00) {
		setValorFormapago('inicial', total);
		setValorFormapago('saldo', '0.00');
	}else{
		setValorFormapago('saldo', saldototal);
	}
}

function retornarFloat (value) {
	var retorno = 0.00;
	value       = value.replace(',','');
	if(value.trim() === ''){
		retorno = 0.00; 
	}else{
		retorno = parseFloat(value)
	}
	return retorno;
}

function quitar (valor) {
	var _token =$('input[name=_token]').val();
	$.post('{{ URL::route("venta.quitarcarritoventa")}}', {valor: valor,_token: _token} , function(data){
		$('#divDetail').html(data);
		calculatetotal();
		//generarSaldototal ();
		// var totalpedido = $('#totalpedido').val();
		// $('#total').val(totalpedido);
	});
}

function calculatetotal () {
	var _token =$('input[name=_token]').val();
	var valor =0;
	$.post('{{ URL::route("venta.calculartotal")}}', {valor: valor,_token: _token} , function(data){
		valor = retornarFloat(data);
		$("#total").val(valor);
		//generarSaldototal();
		// var totalpedido = $('#totalpedido').val();
		// $('#total').val(totalpedido);
	});
}

function comprobarproducto () {
	var _token =$('input[name=_token]').val();
	var valor =$('input[name=codigo]').val();
	$.post('{{ URL::route("venta.comprobarproducto")}}', {valor: valor,_token: _token} , function(data){
		
		if (data.trim() == 'NO') {
			$('input[name=codigo]').val('');
			bootbox.alert("Este Producto no esta en lista de venta");
            setTimeout(function () {
                $('#codigo').focus();
            },2000) 
		}else{
			$('input[name=codigo]').val('');
			$('#codigo').focus();
		}
	});
}

function seleccionarCliente(id) {
	var _token =$('input[name=_token]').val();
	$.post('{{ URL::route("venta.clienteid")}}', {id: id,_token: _token} , function(data){
		var datos = data.split('-'); 
		$('#person_id').val(datos[0]);
		$('#nombrepersona').val(datos[1]);
		
		cerrarModal();
		var tipoventa = $('#tipoventa').val();
		if (tipoventa == 'N') {
			$('#nombreproducto').focus();
		}else{
			//$('#conveniofarmacia').focus();
			abrirconvenios();
		}
	});
	
}

function seleccionarParticular(value) {
	$('#nombrepersona').val(value);
	cerrarModal();
	$('#nombreproducto').focus();
}

function agregarconvenio(id){

	var kayros = $('#txtKayros').val();
	var copago = $('#txtCopago').val();
	var convenio_id = id;

	var _token =$('input[name=_token]').val();
	if(kayros.trim() == '' ){
		bootbox.alert("Ingrese precio kayros");
            setTimeout(function () {
                $('#txtKayros').focus();
            },2000) 
	}else if(copago.trim() == '' ){
		bootbox.alert("Ingrese copago");
            setTimeout(function () {
                $('#txtCopago').focus();
            },2000) 
	}else{
		$.post('{{ URL::route("venta.agregarconvenio")}}', {kayros: kayros,copago: copago, convenio_id: convenio_id,_token: _token} , function(data){
			dat = data.split('-');
			$('#copago').val(copago);
			$('#descuentokayros').val(kayros);
			$('#conveniofarmacia').val(dat[0]);
			$('#conveniofarmacia_id').val(dat[1]);

			cerrarModal();
			$('#descuentokayros').focus();
			/*$('#divDetail').html(data);
			calculatetotal();
			bootbox.alert("Producto Agregado");
            setTimeout(function () {
                $('#txtPrecio' + elemento).focus();
            },2000) */
			
		});
	}
	}

	function agregarempresa(id){

	var ruc = $('#ruc').val();
	var direccion = $('#direccion').val();
	var telefono = $('#telefono').val();
	var empresa_id = id;

	var _token =$('input[name=_token]').val();
	/*if(kayros.trim() == '' ){
		bootbox.alert("Ingrese precio kayros");
            setTimeout(function () {
                $('#txtKayros').focus();
            },2000) 
	}else if(copago.trim() == '' ){
		bootbox.alert("Ingrese copago");
            setTimeout(function () {
                $('#txtCopago').focus();
            },2000) 
	}else{*/
		$.post('{{ URL::route("venta.agregarempresa")}}', {ruc: ruc,direccion: direccion,telefono: telefono, empresa_id: empresa_id,_token: _token} , function(data){
			dat = data.split('-');
			$('#nombrepersona').val(dat[0]);
			$('#empresa_id').val(dat[1]);

			cerrarModal();
			$('#nombreproducto').focus();
			/*$('#divDetail').html(data);
			calculatetotal();
			bootbox.alert("Producto Agregado");
            setTimeout(function () {
                $('#txtPrecio' + elemento).focus();
            },2000) */
			
		});
	//}
	}

	function addpurchasecart(elemento){
		var cantidad = $('#cantidad').val();
		var price = $('#precioventa').val();
		var preciokayros = $('#preciokayros').val();
		var product_id = $('#producto_id').val();
		var stock = $('#stock').val();
		var tipoventa = $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="tipoventa"]').val();
		var descuentokayros = $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="descuentokayros"]').val();
		var copago = $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="copago"]').val();

		var _token =$('input[name=_token]').val();
		if(cantidad.trim() == '' ){
			bootbox.alert("Ingrese Cantidad");
	            setTimeout(function () {
	                $('#cantidad').focus();
	            },2000) 
		}else if(cantidad.trim() == 0){
			bootbox.alert("la cantidad debe ser mayor a 0");
	            setTimeout(function () {
	                $('#cantidad').focus();
	            },2000) 
		}else if(price.trim() == '' ){
			bootbox.alert("Ingrese Precio");
	            setTimeout(function () {
	                $('#precioventa').focus();
	            },2000) 
		}else if(price.trim() == 0){
			bootbox.alert("el precio debe ser mayor a 0");
	            setTimeout(function () {
	                $('#precioventa').focus();
	            },2000) 
		}/*else if(parseFloat(cantidad.trim()) > parseFloat(stock)){
			bootbox.alert("No puede vender una cantidad mayor al stock actual");
	            setTimeout(function () {
	                $('#cantidad').focus();
	            },2000) 
		}*/else{
			$.post('{{ URL::route("venta.agregarcarritoventa")}}', {cantidad: cantidad,precio: price, producto_id: product_id, tipoventa: tipoventa, descuentokayros: descuentokayros, copago: copago, preciokayros: preciokayros,_token: _token} , function(data){
				$('#divDetail').html(data);
				calculatetotal();
				/*bootbox.alert("Producto Agregado");
	            setTimeout(function () {
	                $(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').focus();
	            },2000) */
				//var totalpedido = $('#totalpedido').val();
				//$('#total').val(totalpedido);
			});
		}
	}

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
	                //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(dat[0].id);
	                //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').val(dat[0].historia);
	                //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
	                //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombrepersona"]').val(dat[0].paciente);
	                $('#person_id').val(dat[0].person_id);
					$('#nombrepersona').val(dat[0].paciente);
					cerrarModal();
	                var tipoventa = $('#tipoventa').val();
					if (tipoventa == 'N') {
						$('#nombreproducto').focus();
					}else{
						//$('#conveniofarmacia').focus();
						abrirconvenios();
					}
				} else {
					mostrarErrores(respuesta, idformulario, entidad);
				}
			}
		});
	}

	function guardarEmpresa (entidad, idboton) {
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
	                $('#empresa_id').val(dat[0].empresa_id);
					$('#nombrepersona').val(dat[0].nombre);
					cerrarModal();
	                var tipoventa = $('#tipoventa').val();
					if (tipoventa == 'N') {
						$('#nombreproducto').focus();
					}else{
						//$('#conveniofarmacia').focus();
						abrirconvenios();
					}
				} else {
					mostrarErrores(respuesta, idformulario, entidad);
				}
			}
		});
	}

function guardarVenta (entidad, idboton, entidad2) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
	var listar       = 'NO';
	
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
			var dat = JSON.parse(respuesta);
            if(dat[0]!==undefined){
                resp=dat[0].respuesta;    
            }else{
                resp='VALIDACION';
            }
            
			if (resp === 'OK') {
				cerrarModal();
                buscarCompaginado('', 'Accion realizada correctamente', entidad, 'OK');
                /*if(dat[0].pagohospital!="0"){
                    window.open('/juanpablo/ticket/pdfComprobante?ticket_id='+dat[0].ticket_id,'_blank')
                }else{
                    window.open('/juanpablo/ticket/pdfPrefactura?ticket_id='+dat[0].ticket_id,'_blank')
                }*/
                //alert('hola');
                //window.open('/juanpablo/venta/pdfComprobante?venta_id='+dat[0].venta_id,'_blank')
			} else if(resp === 'ERROR') {
				alert(dat[0].msg);
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}



</script>