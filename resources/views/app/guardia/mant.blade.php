<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($guardia, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('person_id', $person_id, array('id' => 'person_id')) !!}
	<?php  
	$date = null;
	if ($guardia !== NULL) {
		$date = date('d/m/Y',strtotime($guardia->fecha));
	}
	?>
	<div class="form-group">
		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			<div class='input-group input-group-xs' id='divfecha'>
				{!! Form::text('fecha', $date, array('class' => 'form-control input-xs', 'id' => 'fecha', 'placeholder' => 'Ingrese fecha')) !!}
				<span class="input-group-btn">
					<button class="btn btn-default calendar">
						<i class="glyphicon glyphicon-calendar"></i>
					</button>
				</span>
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
	configurarAnchoModal('350');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fecha"]').inputmask("dd/mm/yyyy");
		$('#divfecha').datetimepicker({
			pickTime: false,
			language: 'es'
		});
}); 
</script>