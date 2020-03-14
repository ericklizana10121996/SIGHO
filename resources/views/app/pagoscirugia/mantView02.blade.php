<div id="divMensajeError{!! $entidad !!}"></div>
{{-- {{dd($cirugia->historia_id)}} --}}
{!! Form::model($cirugia, $formData) !!}	
	{{-- {!! Form::hidden('listar', $listar, array('id' => 'listar')) !!} --}}
	<div class="row">
		<div class="col-lg-6 col-md-6 col-sm-6">
			<div class="form-group">
	    		{!! Form::label('fecha_00', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
	    		<div class="col-lg-4 col-md-4 col-sm-4">
	    			{!! Form::date('fecha_00', date('Y-m-d',strtotime($cirugia->fecha)), array('class' => 'form-control input-xs', 'id' => 'fecha_00', 'readonly' => 'true')) !!}
	    		</div>
	            {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
	    		<div class="col-lg-3 col-md-3 col-sm-3">
	    			{!! Form::text('numero', 'C'.str_pad($cirugia->numero, 9, "0", STR_PAD_LEFT), array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
	    		</div>
	    	</div>

		    <div class="form-group">
	            {!! Form::label('cirugia', 'Cirugia:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
	            <div class="col-lg-8 col-md-8 col-sm-8">
	            {!! Form::text('cirugia', $cirugia->nombre_cirugia, array('class' => 'form-control input-xs', 'id' => 'cirugia', 'placeholder' => 'Ingrese Cirugia', 'readonly' => 'true')) !!}
	            </div>
	        </div>


	        <div class="form-group">
	    		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
	    		<div class="col-lg-8 col-md-8 col-sm-8">
	            {!! Form::hidden('person_id', $cirugia->paciente_id, array('id' => 'person_id')) !!}
	            {!! Form::hidden('dni', $cirugia->paciente->dni, array('id' => 'dni')) !!}
	    		{!! Form::text('paciente', $cirugia->paciente->apellidopaterno.' '.$cirugia->paciente->apellidopaterno.' '.$cirugia->paciente->nombres, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Paciente','readOnly' => 'true')) !!}
	    		</div>
	    	</div>

			<div class="form-group">
	    		{!! Form::label('numero', 'Historia:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
	    		<div class="col-lg-3 col-md-3 col-sm-3">
	                {!! Form::hidden('historia_id', $cirugia->historia->id, array('id' => 'historia_id')) !!}
	    			{!! Form::text('numero_historia', $cirugia->historia->numero, array('class' => 'form-control input-xs', 'id' => 'numero_historia', 'readonly' => 'true')) !!}
	    		</div>
	           
	            {!! Form::label('tipopaciente', 'T. Pac.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
	    		<div class="col-lg-3 col-md-3 col-sm-3">
	    			{!! Form::select('tipopaciente', $cboTipoPaciente, 'Convenio', array('class' => 'form-control input-xs', 'id' => 'tipopaciente' ,'disabled' => true)) !!}
	    		</div>
	    	</div>

	        <div class="form-group">
	            {!! Form::label('plan', 'Plan:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
	    		<div class="col-lg-8 col-md-8 col-sm-8">
	                {!! Form::hidden('tipoplan', null, array('id' => 'tipoplan')) !!}
	                {!! Form::hidden('plan_id', $cirugia->plan_id, array('id' => 'plan_id')) !!}
	                {!! Form::text('plan', $cirugia->plan->nombre, array('class' => 'form-control input-xs', 'id' => 'plan','readonly'=>'true')) !!}
	    		</div>
	        </div>
		</div>
		<div class="col-lg-6 col-md-6 col-sm-6">
			{{-- <div class="col-lg-12 col-md-12 col-sm-12"> --}}
				<div id="divDetail" class="table-responsive" style="overflow:auto; height:180px; padding-right:10px; border:1px outset">
			        <table style="width: 100%;" class="table-condensed table-striped">
			            <thead>
			                <tr>
			                    <th bgcolor="#E0ECF8" class='text-center'>Cant</th>
			                    <th bgcolor="#E0ECF8" class='text-center'>Médico</th>
			                    <th bgcolor="#E0ECF8" class="text-center">Descripción</th>
			                    <th bgcolor="#E0ECF8" class="text-right">Monto</th>
			                    <th bgcolor="#E0ECF8" class="text-right">SubTotal</th>
			                    <th bgcolor="#E0ECF8" class="text-right">Op.</th>
			                    
			                                               
			                </tr>
			            </thead>
			            <tbody>
			            @foreach($detalles as $key => $value)
						<tr>
							<td class="text-center">{!! $value->cantidad !!}</td>
							<td class="text-center">{!! $value->doctor->apellidopaterno.' '.$value->doctor->apellidomaterno.' '.$value->doctor->nombres !!}</td>
							<td class="text-center">{!! $value->descripcion !!}</td>
							<td class="text-right">{!! $value->monto !!}</td>
							<td class="text-right">{!! $value->sub_total !!}</td>
							<td class="text-center">
								{!! Form::button('<div class="glyphicon glyphicon-usd"></div>', array('onclick' => 'modal (\''.URL::route($ruta["pagar"], array('id'=>$value->id,'listarLuego'=> 'SI')).'\', \''.'Pagar'.'\', this);', 'class' => 'btn btn-xs btn-success', 'title' => 'Confirmar')) !!}							
							</td>
							
						</tr>
						@endforeach
			            </tbody>
			           
			        </table>
			    </div>
			{{-- </div> --}}
		</div>

		{{-- <div class="form-group"> --}}
		<div class="col-lg-12 col-md-12 col-sm-12 text-right mt-3">		
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
		{{-- </div> --}}
	<div>
	
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


</script>