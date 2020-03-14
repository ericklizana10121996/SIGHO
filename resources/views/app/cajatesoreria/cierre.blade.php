<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($caja, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('caja_id', $caja->id, array('id' => 'caja_id')) !!}
	<div class="form-group">
		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha' , 'readonly' => 'true')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('numero', 'Nro:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('concepto', 'Concepto:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('concepto', 'Cierre de Caja', array('class' => 'form-control input-xs', 'id' => 'concepto', 'readonly' => 'true')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('total', 'Total:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('total', $total, array('class' => 'form-control input-xs', 'id' => 'total', 'readonly' => 'true')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('comentario', 'Comentario:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::textarea('comentario', null, array('class' => 'form-control input-xs', 'id' => 'comentario', 'cols' => 10 , 'rows','5')) !!}
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
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
}); 
</script>