<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($venta, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('movimiento_id', $venta->id, array('id' => 'movimiento_id')) !!}
	{!! Form::hidden('person_id', $venta->persona_id, array('id' => 'person_id')) !!}
	<div class="col-lg-6 col-md-6 col-sm-6">
		<div class="form-group">
			{!! Form::label('tipodocumento_id', 'Documento:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::select('tipodocumento_id', $cboDocumento, null, array('class' => 'form-control input-xs', 'id' => 'tipodocumento_id', 'readonly' => 'true')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('numerodocumento', 'Nro Doc:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::text('serie', '002', array('class' => 'form-control input-xs', 'id' => 'serie')) !!}
        	</div>
			<div class="col-lg-5 col-md-5 col-sm-5">
				{!! Form::text('numerodocumento', $numero, array('class' => 'form-control input-xs', 'id' => 'numerodocumento', 'placeholder' => 'Ingrese numerodocumento')) !!}
			</div>
		</div>

		<div class="form-group">
			{!! Form::label('comentario', 'Comentario:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::select('comentario', $cboComentario, null, array('class' => 'form-control input-xs', 'id' => 'comentario')) !!}
			</div>
		</div>


	</div>
	<div class="col-lg-6 col-md-6 col-sm-6">
		<div class="form-group">
			{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				<div class='input-group input-group-xs' id='divfecha'>
					{!! Form::text('fecha', date('d/m/Y'), array('class' => 'form-control input-xs', 'id' => 'fecha', 'placeholder' => 'Ingrese fecha')) !!}
					<span class="input-group-btn">
						<button class="btn btn-default calendar">
							<i class="glyphicon glyphicon-calendar"></i>
						</button>
					</span>
				</div>
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('total', 'Total:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('total', $venta->total, array('class' => 'form-control input-xs', 'id' => 'total', 'placeholder' => 'Ingrese total', 'readonly' => '')) !!}
			</div>
		</div>
		<div class="form-group" >
			<div class="col-lg-5 col-md-5 col-sm-5">
			</div>
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::hidden('pagar', 'S', array('id' => 'pagar')) !!}    
	            <input type="checkbox" onclick="if(this.checked){$('#pagar').val('S');}else{$('#pagar').val('N');}" checked id="pago" class="col-lg-2 col-md-2 col-sm-2 control-label datocaja" />
	            {!! Form::label('pago', 'Genera Egreso', array('class' => 'col-lg-10 col-md-10 col-sm-10 control-label')) !!}
            </div>
        </div>
                
	</div>
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12">
			<div id="divDetail" class="table-responsive" style="overflow:auto; height:180px; padding-right:10px; border:1px outset">
		        <table style="width: 100%;" class="table-condensed table-striped">
		            <thead>
		                <tr>
		                    <th bgcolor="#E0ECF8" class='text-center'>Producto</th>
		                    <th bgcolor="#E0ECF8" class='text-center'>Cantidad</th>
		                    <th bgcolor="#E0ECF8" class="text-center">Precio</th>
		                    <th bgcolor="#E0ECF8" class="text-center">Subtotal</th>                           
		                </tr>
		            </thead>
		            <tbody>
		            @foreach($detalles as $key => $value)
					<tr>
						<td class="text-center">{!! $value->producto->nombre !!}</td>
						<td class="text-center">{!! $value->cantidad !!}</td>
						<td class="text-center">{!! $value->precio !!}</td>
						<td class="text-center">{!! $value->subtotal !!}</td>
					</tr>
					@endforeach
		            </tbody>
		           
		        </table>
		    </div>
		</div>
	 </div>
    <br>
	
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardarVenta(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('880');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');

		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });

		
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fecha"]').inputmask("dd/mm/yyyy");
		$('#divfecha').datetimepicker({
			pickTime: false,
			language: 'es'
		});

	listar();

}); 



function setValorFormapago (id, valor) {
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="' + id + '"]').val(valor);
}

function getValorFormapago (id) {
	var valor = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="' + id + '"]').val();
	return valor;
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

function listar () {
	var _token =$('input[name=_token]').val();
	var valor = 1;
	$.post('{{ URL::route("venta.listarcarritonota")}}', {valor: valor,_token: _token} , function(data){
		$('#divDetail').html(data);
		//calculatetotal();
		//generarSaldototal ();
		// var totalpedido = $('#totalpedido').val();
		// $('#total').val(totalpedido);
	});
}

function quitar (valor) {
	var _token =$('input[name=_token]').val();
	$.post('{{ URL::route("venta.quitarcarritonotacredito")}}', {valor: valor,_token: _token} , function(data){
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

function guardarVenta (entidad, idboton, entidad2) {
	var total = $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="totalventa"]').val();
	var mensaje = '<h3 align = "center">Total = '+total+'</h3>';
	/*if (typeof mensajepersonalizado != 'undefined' && mensajepersonalizado !== '') {
		mensaje = mensajepersonalizado;
	}*/
	bootbox.confirm({
		message : mensaje,
		buttons: {
			'cancel': {
				label: 'Cancelar',
				className: 'btn btn-default btn-sm'
			},
			'confirm':{
				label: 'Aceptar',
				className: 'btn btn-success btn-sm'
			}
		}, 
		callback: function(result) {
			if (result) {
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
			                if (dat[0].ind == 1) {
			                	window.open('/juanpablo/venta/pdfComprobante?venta_id='+dat[0].venta_id+'&guia='+dat[0].guia,'_blank');
			                	window.open('/juanpablo/venta/pdfComprobante?venta_id='+dat[0].second_id+'&guia='+dat[0].guia,'_blank');
			                }else{
			                	window.open('/juanpablo/venta/pdfComprobante?venta_id='+dat[0].venta_id+'&guia='+dat[0].guia,'_blank');
			                }
			                
						} else if(resp === 'ERROR') {
							alert(dat[0].msg);
						} else {
							mostrarErrores(respuesta, idformulario, entidad);
						}
					}
				});
			};
		}            
	}).find("div.modal-content").addClass("bootboxConfirmWidth");
	setTimeout(function () {
		if (contadorModal !== 0) {
			$('.modal' + (contadorModal-1)).css('pointer-events','auto');
			$('body').addClass('modal-open');
		}
	},2000);


	
}


</script>