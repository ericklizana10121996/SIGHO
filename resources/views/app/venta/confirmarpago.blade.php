<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($modelo, $formData) !!}
{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
<?php 
if(!is_null($modelo)){
    $fecha=$modelo->fecha;
    if($modelo->tipodocumento_id==5){
    	if($modelo->persona_id>0){
        	$paciente=$modelo->person->apellidopaterno.' '.$modelo->person->apellidomaterno.' '.$modelo->person->nombres;
    	}else{
       		$paciente=$modelo->nombrepaciente;
       	}
        $numero='B'.$modelo->serie.'-'.$modelo->numero;
    }else{
        $paciente=$modelo->empresa->bussinesname;
        $numero='F'.$modelo->serie.'-'.$modelo->numero;
    }
    $total=number_format($modelo->total,2,'.','');
    $id=$modelo->id;
}
?>
{!! Form::model($modelo, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('id', $id, array('id' => 'id')) !!}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-4 col-md-4 col-sm-4">
        			{!! Form::date('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'fecha', 'readonly' => 'true')) !!}
        		</div>
                {!! Form::label('numero', 'Nro.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
                </div>
        	</div>
            <div class="form-group">
        		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-7 col-md-7 col-sm-7">

        			{!! Form::text('paciente', $paciente, array('class' => 'form-control input-xs', 'id' => 'paciente', 'readonly' => 'true')) !!}
        		</div>
        	</div>
            <div class="form-group">
            {!! Form::label('formapago', 'Forma Pago:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::select('formapago', $cboFormaPago, null, array('class' => 'form-control input-xs datocaja caja', 'id' => 'formapago', 'onchange'=>'validarFormaPago(this.value);')) !!}
                </div>
            </div>
            <div class="form-group datocaja" id="divTarjeta" style="display: none;">
                {!! Form::label('tipotarjeta', 'Tarjeta:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('tipotarjeta', $cboTipoTarjeta, null, array('class' => 'form-control input-xs', 'id' => 'tipotarjeta')) !!}
                </div>
                {!! Form::label('tipotarjeta2', 'Tipo Tarjeta:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('tipotarjeta2', $cboTipoTarjeta2, null, array('class' => 'form-control input-xs', 'id' => 'tipotarjeta2')) !!}
                </div>
                {!! Form::label('nroref', 'Nro. Op.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('nroref', null, array('class' => 'form-control input-xs', 'id' => 'nroref')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('total', 'Total:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('total', $total, array('class' => 'form-control input-xs', 'id' => 'total', 'readonly' => 'true')) !!}
                </div>
            </div>
        	<div class="form-group">
        		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
        			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
        			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        		</div>
        	</div>
        </div>
     </div>
{!! Form::close() !!}
<script type="text/javascript">
	$(document).ready(function() {
		init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
		configurarAnchoModal('600');
	}); 

function validarFormaPago(forma){
    if(forma=="Tarjeta"){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} div[id="divTarjeta"]').css("display","");
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} div[id="divTarjeta"]').css("display","none");
    }
}
</script>