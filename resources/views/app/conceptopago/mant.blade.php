<?php
if(!is_null($conceptopago)){
	$admision=$conceptopago->admision;
	$tesoreria=$conceptopago->tesoreria;
}else{
	$admision='N';
	$tesoreria='N';
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($conceptopago, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="form-group">
		{!! Form::label('nombre', 'Nombre:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre', 'placeholder' => 'Ingrese nombre')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('cuenta', 'Cuenta:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('cuenta', null, array('class' => 'form-control input-xs', 'id' => 'cuenta', 'placeholder' => 'Ingrese cuenta')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('tipo', 'Tipo:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::select('tipo', $cboTipo, null, array('class' => 'form-control input-xs', 'id' => 'tipo')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('admision', 'Admision:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        <div class="col-lg-1 col-md-1 col-sm-1">
            {!! Form::hidden('admision', $admision, array('id' => 'admision')) !!}
            <input type="checkbox" onclick="admision2(this.checked)"  <?php if($admision=='S') echo "checked=''";?> />
        </div>
        {!! Form::label('tesoreria', 'Tesoreria:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        <div class="col-lg-1 col-md-1 col-sm-1">
            {!! Form::hidden('tesoreria', $tesoreria, array('id' => 'tesoreria')) !!}
            <input type="checkbox" onclick="tesoreria2(this.checked)" <?php if($tesoreria=='S') echo "checked=''";?> />
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
	configurarAnchoModal('450');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
}); 

function admision2(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="admision"]').val('S');
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="admision"]').val('N');
    }
}

function tesoreria2(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tesoreria"]').val('S');
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tesoreria"]').val('N');
    }
}
</script>