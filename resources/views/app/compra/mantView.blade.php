<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($compra, $formData) !!}
<?php 
use Jenssegers\Date\Date;
$montoinicial=0;
if ($compra->montoinicial !== null) {
	$montoinicial=$compra->montoinicial;
}
$fecha = Date::createFromFormat('Y-m-d', $compra->fecha)->format('d/m/Y');
echo $compra->cajafamarcia;
?>	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="col-lg-6 col-md-6 col-sm-6">
		<div class="form-group">
			{!! Form::label('documento', 'Documento:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::select('documento', $cboDocumento, $compra->tipodocumento_id, array('class' => 'form-control input-xs', 'id' => 'documento')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('numerodocumento', 'Nro Doc:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-3 col-md-3 col-sm-3">
				{!! Form::text('serie', null, array('class' => 'form-control input-xs', 'id' => 'serie', 'placeholder' => 'serie')) !!}
			</div>
			<div class="col-lg-4 col-md-4 col-sm-4">
				{!! Form::text('numerodocumento', $compra->numero, array('class' => 'form-control input-xs', 'id' => 'numerodocumento', 'placeholder' => 'Ingrese numerodocumento')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('nombrepersona', 'Proveedor:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			{!! Form::hidden('person_id', null, array('id' => 'person_id')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('nombrepersona', $compra->person->bussinesname, array('class' => 'form-control input-xs', 'id' => 'nombrepersona', 'placeholder' => 'Seleccione persona')) !!}
				
			</div>
		</div>
		<div id="divnumerodias" class="form-group">
			{!! Form::label('numerodias', 'Nro Dias:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('numerodias', null, array('class' => 'form-control input-xs', 'id' => 'numerodias', 'placeholder' => 'Ingrese numero dias')) !!}
			</div>
		</div>
		<div style="display: none" class="form-group" id="divcuota">
			{!! Form::label('fechacuota', 'Fecha 1° pago:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				<div class='input-group input-group-xs' id='divfechacuota'>
					{!! Form::text('fechacuota', null, array('class' => 'form-control input-xs', 'id' => 'fechacuota', 'placeholder' => 'Ingrese fecha 1° cuota')) !!}
					<span class="input-group-btn">
						<button class="btn btn-default calendar">
							<i class="glyphicon glyphicon-calendar"></i>
						</button>
					</span>
				</div>
			</div>
		</div>
		<div style="display: none" class="form-group" id="divnumerocuota">
			{!! Form::label('numerocuotas', 'Nro cuotas:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('numerocuotas', null, array('class' => 'form-control input-xs', 'id' => 'numerocuotas', 'placeholder' => 'Ingrese numerocuotas')) !!}
			</div>
		</div>

		<div class="form-group" id = "divFicha">
			{!! Form::label('numeroficha', 'Ficha Atencion:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('numeroficha', null, array('class' => 'form-control input-xs', 'id' => 'numeroficha', 'placeholder' => 'Ingrese numeroficha')) !!}
			</div>
		</div>
	</div>
	<div class="col-lg-6 col-md-6 col-sm-6">
		<div class="form-group">
			{!! Form::label('credito', 'credito:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::select('credito', $cboCredito, null, array('class' => 'form-control input-xs', 'id' => 'credito', 'onchange' => 'cambiar();')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				<div class='input-group input-group-xs' id='divfecha'>
					{!! Form::text('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'fecha', 'placeholder' => 'Ingrese fecha')) !!}
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
				{!! Form::text('total', null, array('class' => 'form-control input-xs', 'id' => 'total', 'placeholder' => 'Ingrese total', 'readonly' => '')) !!}
			</div>
		</div>
		<div style="display: none" class="form-group" id="divinicial">
			{!! Form::label('inicial', 'Inicial:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('inicial', $montoinicial, array('class' => 'form-control input-xs', 'id' => 'inicial', 'placeholder' => 'Ingrese inicial', 'readonly'=>'')) !!}
			</div>
		</div>
		<div style="display: none" class="form-group" id="divsaldo">
			{!! Form::label('saldo', 'saldo:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('saldo', '0', array('class' => 'form-control input-xs', 'id' => 'saldo', 'placeholder' => 'Ingrese saldo', 'readonly' => '')) !!}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('cajafamarcia', 'Caja famarcia:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::select('cajafamarcia', $cboCajafarmacia, $compra->cajaprueba, array('class' => 'form-control input-xs', 'id' => 'cajafamarcia', 'onchange' => 'cambiar2();')) !!}
			</div>
		</div>
		<div class="form-group" id = "divDoctor">
			{!! Form::label('nombredoctor', 'Doctor:', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
			{!! Form::hidden('doctor_id', null, array('id' => 'doctor_id')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('nombredoctor', $doctor, array('class' => 'form-control input-xs', 'id' => 'nombredoctor', 'placeholder' => 'Seleccione persona')) !!}
				
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
			
			
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('880');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="inicial"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="saldo"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numerocuotas"]').inputmask('Regex', { regex: "[0-9]+" });
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="diasentrecuotas"]').inputmask('Regex', { regex: "[0-9]+" });
		$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="inicial"]').keyup(function(){
			generarSaldototal ();
		});
		$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="numerocuotas"]').blur(function(){
			generarCreditos ();
		});
		$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="diasentrecuotas"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				generarCreditos ();
			}
		});
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fecha"]').inputmask("dd/mm/yyyy");
		$('#divfecha').datetimepicker({
			pickTime: false,
			language: 'es'
		});
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fechacuota"]').inputmask("dd/mm/yyyy");
		$('#divfechacuota').datetimepicker({
			pickTime: false,
			language: 'es'
		});
	cambiar();
	cambiar2();

	var personas = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'person/providersautocompleting/%QUERY',
				filter: function (personas) {
					return $.map(personas, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		personas.initialize();
		$('#nombrepersona').typeahead(null,{
			displayKey: 'value',
			source: personas.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#person_id').val(datum.id);
		});

	var doctores = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'person/doctorautocompleting/%QUERY',
				filter: function (doctores) {
					return $.map(doctores, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		doctores.initialize();
		$('#nombredoctor').typeahead(null,{
			displayKey: 'value',
			source: doctores.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#doctor_id').val(datum.id);
		});
}); 

function cambiar() {
	var credito = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="credito"]').val();
	if (credito == 'S') {
		$('#divnumerodias').show();
		/*$('#divcuota').show();
		$('#divnumerocuota').show();
		$('#divdias').show();
		$("#inicial").prop('readonly', false);*/
	}else{
		$('#divcuota').hide();
		$('#divnumerocuota').hide();
		$('#divdias').hide();
		$("#inicial").prop('readonly', true);
		$("#inicial").val('0.00');
	}
	generarSaldototal ();
}

function cambiar2() {
	var cajafamarcia = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="cajafamarcia"]').val();
	if (cajafamarcia == 'S') {
		$('#divFicha').show();
		$('#divDoctor').show();
	}else{
		$('#divFicha').hide();
		$('#divDoctor').hide();
	}
	
}

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

function quitar (valor) {
	var _token =$('input[name=_token]').val();
	$.post('{{ URL::route("compra.quitarcarritocompra")}}', {valor: valor,_token: _token} , function(data){
		$('#divDetail').html(data);
		calculatetotal();
		generarSaldototal ();
		// var totalpedido = $('#totalpedido').val();
		// $('#total').val(totalpedido);
	});
}

function calculatetotal () {
	var _token =$('input[name=_token]').val();
	var valor =0;
	$.post('{{ URL::route("compra.calculartotal")}}', {valor: valor,_token: _token} , function(data){
		valor = retornarFloat(data);
		$("#total").val(valor);
		generarSaldototal();
		// var totalpedido = $('#totalpedido').val();
		// $('#total').val(totalpedido);
	});
}

function generarCreditos () {
	var nrocuotas   = getValorFormapago('numerocuotas');
	var saldototal  = getValorFormapago('saldo');
	var primerpago  = getValorFormapago('fechacuota');
	var diasentrecuotas   = getValorFormapago('diasentrecuotas');
	var _token =$('input[name=_token]').val();
	if(primerpago === ''){
		alert('Ingrese fecha de la primera cuota');
		$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="fechacuota"]').focus();
	}else if(nrocuotas == ''){
		alert('Ingrese numero de cuotas');
		$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="numerocuotas"]').focus();
	}else if(diasentrecuotas == ''){
		alert('Ingrese dias entre cuotas');
		$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="diasentrecuotas"]').focus();
	}else if(saldototal == ''){
		alert('Ingrese saldo');
	}else{
		$('#divCreditos').html(imgCargando());
		$.post('{{ URL::route("compra.generarcreditos")}}', {nrocuotas: nrocuotas, saldototal: saldototal, primerpago: primerpago, diasentrecuotas: diasentrecuotas,_token: _token} , function(data){
			$('#divCreditos').html(data);
		});
	}
}
</script>