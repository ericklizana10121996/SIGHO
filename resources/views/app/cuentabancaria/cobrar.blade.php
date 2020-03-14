<?php
if(!is_null($cuentabancaria)){
    $fecha=$cuentabancaria->fecha;
    $total=number_format($cuentabancaria->total,2,'.','');
    $id=$cuentabancaria->id;
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($cuentabancaria, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('id', $id, array('id' => 'id')) !!}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-4 col-md-4 col-sm-4">
        			{!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
        		</div>
                {!! Form::label('total', 'Total:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
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
	configurarAnchoModal('500');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
	
}); 
</script>