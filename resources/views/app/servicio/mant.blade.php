<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($servicio, $formData) !!}	
<?php
if($servicio!=null){
    $plan_id=$servicio->plan_id;
    $tiposervicio_id=$servicio->tiposervicio_id;
    if($servicio->tipopago=='Convenio'){
    	$unidad=$servicio->tarifario->unidad;
    	$tarifario=$servicio->tarifario->codigo.' '.$servicio->tarifario->nombre;
    	$tarifario_id=$servicio->tarifario_id;
    }else{
	    $unidad=null;
	    $tarifario=null;
	    $tarifario_id=null;    	
    }

}else{
    $plan_id=null;
    $unidad=null;
    $tarifario=null;
    $tarifario_id=null;
    $tiposervicio_id=null;
}
?>
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    <div class="form-group">
		{!! Form::label('tipopago', 'Tipo Pago:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
        <div class="col-lg-4 col-md-4 col-sm-4">
            {!! Form::select('tipopago', $cboTipoPago, null, array('class' => 'form-control input-xs', 'id' => 'tipopago', 'onchange' => 'validarTipoPago(this.value,true)')) !!}
        </div>
	</div>
    <div class="form-group" id="divPlan" style="display:none">
		{!! Form::label('plan', 'Plan:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
        <div class="col-lg-8 col-md-8 col-sm-8">
            {!! Form::select('plan', $cboPlan, $plan_id, array('class' => 'form-control input-xs', 'id' => 'plan', 'onchange' => 'buscarFactor();calcularPrecio();')) !!}
        </div>
	</div>
    <div class="form-group">
		{!! Form::label('tiposervicio', 'Tipo:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
        <div class="col-lg-8 col-md-8 col-sm-8">
            {!! Form::select('tiposervicio', $cboTipoServicio, $tiposervicio_id, array('class' => 'form-control input-xs', 'id' => 'tiposervicio')) !!}
        </div>
	</div>
	<div class="form-group" id="divNombre"> 
		{!! Form::label('nombre', 'Nombre:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre', 'placeholder' => 'Ingrese nombre')) !!}
		</div>
	</div>
	<div class="form-group" id="divTarifario" style="display: none;">
		{!! Form::label('tarifario', 'Nombre:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
            {!! Form::hidden('tarifario_id', $tarifario_id, array('class' => 'form-control input-xs', 'id' => 'tarifario_id')) !!}
			{!! Form::text('tarifario', $tarifario, array('class' => 'form-control input-xs', 'id' => 'tarifario', 'placeholder' => 'Ingrese tarifario')) !!}
		</div>
	</div>
	<div class="form-group" id="divUnidad" style="display: none;">
		{!! Form::label('factor', 'Factor :', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('factor', null, array('class' => 'form-control input-xs', 'id' => 'factor', 'onkeyup' => 'calcularPrecio()', 'onblur' => 'calcularPrecio()')) !!}
		</div>
		{!! Form::label('unidad', 'Unid. :', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('unidad', $unidad, array('class' => 'form-control input-xs', 'id' => 'unidad', 'readonly' => 'true')) !!}
		</div>

	</div>
	<div class="form-group">
		{!! Form::label('precio', 'Precio :', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('precio', null, array('class' => 'form-control input-xs', 'id' => 'precio', 'placeholder' => 'Ingrese precio')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('modo', 'Modo:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
        <div class="col-lg-3 col-md-3 col-sm-3">
            {!! Form::select('modo', $cboModo, null, array('class' => 'form-control input-xs', 'id' => 'modo')) !!}
        </div>
	</div>    
	<div class="form-group">
		{!! Form::label('pagodoctor', 'Pago Medico :', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::text('pagodoctor', null, array('class' => 'form-control input-xs', 'id' => 'pagodoctor')) !!}
		</div>
		{!! Form::label('pagohospital', 'Hospital :', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::text('pagohospital', null, array('class' => 'form-control input-xs', 'id' => 'pagohospital')) !!}
		</div>
	</div>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('500');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="precio"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="factor"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="unidad"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="pagodoctor"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="pagohospital"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    	var personas = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'tarifario/tarifarioautocompletar/%QUERY',
			filter: function (personas) {
				return $.map(personas, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
                        unidad: movie.unidad,
					};
				});
			}
		}
	});
	personas.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tarifario"]').typeahead(null,{
		displayKey: 'value',
		source: personas.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tarifario_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tarifario"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="unidad"]').val(datum.unidad);
        calcularPrecio();
	});    
}); 

function validarTipoPago(tipopago,band){
    if(tipopago=="Convenio"){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} #divPlan').css("display","");
        $("#divTarifario").css("display","");
        $("#divUnidad").css("display","");
        $("#divNombre").css("display","none");
        //$("#precio").attr("readonly","true");
        if(band) buscarFactor();
    }else{
    	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} #divPlan').css("display","none");
        $("#divTarifario").css("display","none");
        $("#divNombre").css("display","");
        $("#divUnidad").css("display","none");
        $("#precio").removeAttr("readonly");
    }
}

function calcularPrecio(){
    if($("#factor")!=""){
        var factor = parseFloat($("#factor").val());
    }else{
        var factor = 0;
    }
    if($("#unidad").val()!=""){
        var unidad = parseFloat($("#unidad").val());
    }else{
        var unidad = 0;
    }
    var precio = Math.round((factor*unidad*1.18)*100)/100;
    $("#precio").val(precio);
}

function buscarFactor(){
    $.ajax({
        type: "POST",
        url: "plan/buscarfactor",
        data: "plan_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $("#factor").val(a);
	    }
    });
}
<?php
if($servicio!=null){
    echo "validarTipoPago('".$servicio->tipopago."',false)";
}
?>
</script>