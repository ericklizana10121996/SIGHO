<?php
 //$cboSituacionPaciente = array('Seleccione Estado' => 'Seleccione Estado','Normal' => 'Normal','Emergencia' => 'Emergencia','Fallecido' => 'Fallecido','Herido' => 'Herido');

if(is_null($historia)){
	$numero=$num;
	//$situacionpaciente = null;
	$convenio=null;
	$departamento=null;
	$cboProv = array("Seleccione Provincia");
	$cboDist = array("Seleccione Distrito");	
}else{
	//dd($historia);
	//$situacionpaciente = $historia->estado_llegada;
	$numero=$historia->numero;
	$convenio=$historia->convenio_id;
	$departamento=$historia->departamento;
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($historia, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('modo', $modo, array('id' => 'modo')) !!}
    <div class="form-group">
		{!! Form::label('numero', 'Historia:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
		</div>
		{!! Form::label('tipopaciente', 'Tipo Pac.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::select('tipopaciente', $cboTipoPaciente, null, array('class' => 'form-control input-xs', 'id' => 'tipopaciente','onchange' =>'mostrarConvenio(this.value)')) !!}
		</div>
	</div>
    <div class="form-group" data="divConvenio" style="display: none;">
		{!! Form::label('convenio', 'Convenio:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::select('convenio', $cboConvenio, $convenio, array('class' => 'form-control input-xs', 'id' => 'convenio')) !!}
		</div>
	</div>
    <div class="form-group" data="divConvenio2" style="display: none;">
		{!! Form::label('empresa', 'Empresa:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('empresa', null, array('class' => 'form-control input-xs', 'id' => 'empresa')) !!}
		</div>
	</div>
    <div class="form-group" data="divConvenio" style="display: none;">
		{!! Form::label('carnet', 'Cod. Asegurado:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::text('carnet', null, array('class' => 'form-control input-xs', 'id' => 'carnet')) !!}
		</div>
		{!! Form::label('plan_susalud', 'NºPlan', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::text('plan_susalud', null, array('class' => 'form-control input-xs', 'id' => 'plan_susalud')) !!}
		</div>
		{!! Form::label('soat', 'SOAT:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::text('soat', null, array('class' => 'form-control input-xs', 'id' => 'soat')) !!}
		</div>
	</div>
    <div class="form-group" data="divConvenio" style="display: none;">
		{!! Form::label('poliza', 'Placa:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('poliza', null, array('class' => 'form-control input-xs', 'id' => 'poliza')) !!}
		</div>
	</div>
    <div class="form-group" data="divConvenio" style="display: none;">
		{!! Form::label('titular', 'Titular:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('titular', null, array('class' => 'form-control input-xs', 'id' => 'titular')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('dni', 'DNI:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('dni', null, array('class' => 'form-control input-xs', 'id' => 'dni', 'placeholder' => 'Ingrese dni', 'onblur'=>'validarDNI(this.value)')) !!}
		</div>
        {!! Form::label('modo', 'Modo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::select('modo', $cboModo, null, array('class' => 'form-control input-xs', 'id' => 'modo')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('apellidopaterno', 'Ap. Paterno:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('apellidopaterno', null, array('class' => 'form-control input-xs', 'id' => 'apellidopaterno', 'placeholder' => 'Ingrese Apellido Paterno')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('apellidomaterno', 'Ap. Materno:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('apellidomaterno', null, array('class' => 'form-control input-xs', 'id' => 'apellidomaterno', 'placeholder' => 'Ingrese Apellido Materno')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('nombres', 'Nombres:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('nombres', null, array('class' => 'form-control input-xs', 'id' => 'nombres', 'placeholder' => 'Ingrese nombres')) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('ocupacion', 'Ocupación:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('ocupacion', null, array('class' => 'form-control input-xs', 'id' => 'ocupacion', 'placeholder' => 'Ingrese ocupacion')) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('telefono', 'Telef. 1:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('telefono', null, array('class' => 'form-control input-xs', 'id' => 'telefono', 'placeholder' => 'Ingrese telefono')) !!}
		</div>
        {!! Form::label('telefono2', 'Telef. 2:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('telefono2', null, array('class' => 'form-control input-xs', 'id' => 'telefono2', 'placeholder' => 'Ingrese telefono')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('departamento', 'Departamento:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::select('departamento', $cboDepa, $departamento, array('class' => 'form-control input-xs', 'id' => 'departamento')) !!}
		
		</div>
		{!! Form::label('provincia', 'Provincia:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::select('provincia', $cboProv, null, array('class' => 'form-control input-xs', 'id' => 'provincia')) !!}
		</div>
	</div>
    <div class="form-group">
    	{!! Form::label('distrito', 'Distrito:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::select('distrito', $cboDist, null, array('class' => 'form-control input-xs', 'id' => 'distrito')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('direccion', 'Direccion:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('direccion', null, array('class' => 'form-control input-xs', 'id' => 'direccion', 'placeholder' => 'Ingrese direccion')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('email', 'Email:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::email('email', null, array('class' => 'form-control input-xs', 'id' => 'email', 'placeholder' => 'Ingrese email')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('fechanacimiento', 'Fecha Nac.:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::date('fechanacimiento', null, array('class' => 'form-control input-xs', 'id' => 'fechanacimiento')) !!}
		</div>
		{!! Form::label('sexo', 'Sexo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::select('sexo', $cboSexo, null, array('class' => 'form-control input-xs', 'id' => 'sexo')) !!}
		</div>

	</div>
    <div class="form-group">
		{!! Form::label('direccion', 'Estado Civil:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::select('estadocivil', $cboEstadoCivil, null, array('class' => 'form-control input-xs', 'id' => 'estadocivil')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('familiar', 'Familiar Resp.:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('familiar', null, array('class' => 'form-control input-xs', 'id' => 'familiar', 'placeholder' => 'Ingrese familiar responsable')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('enviadopor', 'Enviado por:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('enviadopor', null, array('class' => 'form-control input-xs', 'id' => 'enviadopor')) !!}
		</div>
	</div>
	{{-- <div class="form-group">
		{!! Form::label('situacionpaciente', 'Ingresa en Estado:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::select('situacionpaciente', $cboSituacionPaciente, $situacionpaciente, array('class' => 'form-control input-xs', 'id' => 'situacionpaciente')) !!}
		</div>
	</div> --}}
    

	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
            @if($modo=="popup")
                {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardarHistoria(\''.$entidad.'\', this)')) !!}
			@else
                {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardarHistoria(\''.$entidad.'\', this)')) !!}
            @endif
            {!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('600');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').inputmask("99999999");
}); 

$('#departamento').change(function(){
	var depa = $('#departamento').val();
	// alert(depa);
	$.ajax({
        type: "GET",
        url: "historia/buscaProv/"+depa,
        success: function(a) {
            $('#provincia').html(a);
        }
    });
});

$('#provincia').change(function(){
	var prov = $('#provincia').val();
	$.ajax({
        type: "GET",
        url: "historia/buscaDist/"+prov,
        success: function(a) {
            $('#distrito').html(a);
        }
    });
});

function mostrarConvenio(idtipopaciente){
    if(idtipopaciente=="Convenio"){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} div[data="divConvenio"]').css("display","");
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} div[data="divConvenio"]').css("display","none");
    }
}

function validarDNI(dni){
    dni=dni.replace("_","");
    if(dni.length>0){
        if(dni.length==8){
            $.ajax({
                type: "POST",
                url: "historia/validarDNI",
                data: "dni="+dni+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
                success: function(a) {
                    data = JSON.parse(a);
                    if(data[0].msg=="S" && data[0].modo=="Registrado"){
                        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="apellidopaterno"]').val(data[0].apellidopaterno);
                        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="apellidomaterno"]').val(data[0].apellidomaterno);
                        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombres"]').val(data[0].nombres);
                        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="telefono"]').val(data[0].telefono);
                        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="direccion"]').val(data[0].direccion);
                    }else if(data[0].msg=="N"){
                    	alert("El DNI ingresado ya tiene historia");
                    }
                }
            });
        }else{
            alert("Ingresar DNI correcto");
        }
    }
}
function guardarHistoria (entidad, idboton) {
	if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val()=='Convenio' && $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="carnet"]').val()==""){
		alert('Ingresar codigo de asegurado');
		return false;
	}
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
                alert('Historia Generada');
                if(dat[0].id!==undefined){
                	window.open("historia/pdfhistoria?id="+dat[0].id,"_blank");
                }
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}
<?php 
	if($user->usertype_id==1){ 
		echo '$(IDFORMMANTENIMIENTO +\''.$entidad.' :input[id="numero"]\').removeAttr("readonly");';
	}
	if(!is_null($historia)){
		echo "mostrarConvenio('".$historia->tipopaciente."');";
	}
?>
</script>