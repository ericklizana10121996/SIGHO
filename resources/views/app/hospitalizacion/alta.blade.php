<?php
if(!is_null($hospitalizacion)){
    $fecha=$hospitalizacion->fecha;
    $numero=$hospitalizacion->historia->numero;
    $paciente=$hospitalizacion->historia->persona->apellidopaterno.' '.$hospitalizacion->historia->persona->apellidomaterno.' '.$hospitalizacion->historia->persona->nombres;
    $historia_id=$hospitalizacion->historia_id;
    if($hospitalizacion->medico_id>0){
        $medico=$hospitalizacion->medico->apellidopaterno.' '.$hospitalizacion->medico->apellidomaterno.' '.$hospitalizacion->medico->nombres;
        $medico_id=$hospitalizacion->medico_id;
    }else{
        $medico=null;
        $medico_id=null;        
    }
}else{
    $fecha=date("Y-m-d");
    $numero=null;
    $paciente=null;
    $historia_id=null;
    $medico=null;
    $medico_id=null;
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($hospitalizacion, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('id', $hospitalizacion->id, array('id' => 'id')) !!}
         <div class="form-group">
    		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
    		<div class="col-lg-3 col-md-3 col-sm-3">
    			{!! Form::date('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'fecha', 'readonly' => 'true')) !!}
    		</div>
            {!! Form::label('habitacion', 'Habitacion:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-2 col-md-2 col-sm-2">
                {!! Form::hidden('habitacion_id', $habitacion->id, array('id' => 'habitacion_id')) !!}                    
                {!! Form::text('habitacion', $habitacion->nombre, array('class' => 'form-control input-xs', 'id' => 'habitacion', 'readonly' => 'true')) !!}
            </div>
    	</div>
        <div class="form-group">
    		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
    		<div class="col-lg-7 col-md-7 col-sm-7">
                {!! Form::hidden('historia_id', $historia_id, array('id' => 'historia_id')) !!}
    			{!! Form::text('paciente', $paciente, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Paciente', 'readonly' => 'true')) !!}
    		</div>
            {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
            <div class="col-lg-2 col-md-2 col-sm-2">
                {!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
            </div>

    	</div>
        <div class="form-group">
            {!! Form::label('medico', 'Medico:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-6 col-md-6 col-sm-6">
                {!! Form::hidden('medico_id', $medico_id, array('id' => 'medico_id')) !!}
                {!! Form::text('medico', $medico, array('class' => 'form-control input-xs', 'id' => 'medico', 'readonly' => 'true')) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('tipoalta', 'Motivo de alta:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-3 col-md-3 col-sm-3">
                {!! Form::select('tipoalta', $cboTipoalta, "0", array('onchange' => 'cambiaralta($(this).val());','class' => 'form-control input-xs', 'id' => 'tipoalta')) !!}
            </div>
            {!! Form::label('fechaalta', 'Fecha Alta:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-3 col-md-3 col-sm-3">
                {!! Form::date('fechaalta', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechaalta')) !!}
            </div>
        </div>
        <div class="form-group" id="divTrasferido" hidden="">
            {!! Form::label('trasferido', 'Trasferido a:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-8 col-md-8 col-sm-8">
                {!! Form::select('trasferido', array('HOSPITALIZACION'=>'HOSPITALIZACION','UCI'=>'UCI','UCIN'=>'UCIN'), "", array('class' => 'form-control input-xs', 'id' => 'trasferido')) !!}
            </div>
        </div>
        <div class="form-group" id="divReferido" hidden="">
            {!! Form::label('referido', 'Referido a:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-8 col-md-8 col-sm-8">
                {!! Form::text('referido', "", array('class' => 'form-control input-xs', 'id' => 'referido')) !!}
            </div>
        </div>
        <div class="form-group" id="divOtro" hidden="">
            {!! Form::label('detalle', 'Detalle adicional:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
            <div class="col-lg-8 col-md-8 col-sm-8">
                {!! Form::text('detalle', "", array('class' => 'form-control input-xs', 'id' => 'detalle')) !!}
            </div>
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
	configurarAnchoModal('650');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
	
});

function cambiaralta(tipalt){
    $("#divReferido").hide();
    $("#divTrasferido").hide();
    $("#divOtro").hide();
    if(tipalt == 3){
        $("#divReferido").show();
    }else if(tipalt == 4){
        $("#divTrasferido").show();
    }else if(tipalt == 7){
        $("#divOtro").show();
    }
}

</script>