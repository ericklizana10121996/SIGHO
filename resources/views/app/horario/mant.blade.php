<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($horario, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('person_id', $person_id, array('id' => 'person_id')) !!}
	<?php
	$desde=null;$hasta=null;
	if ($horario != null) {
		$desde = date('d/m/Y',strtotime($horario->desde));
		$hasta = date('d/m/Y',strtotime($horario->hasta));
	}
	?>
	<div class="form-group">
		{!! Form::label('desde', 'Desde:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			<div class='input-group input-group-xs' id='divdesde'>
				{!! Form::text('desde', $desde, array('class' => 'form-control input-xs', 'id' => 'desde', 'placeholder' => 'Ingrese desde')) !!}
				<span class="input-group-btn">
					<button class="btn btn-default calendar">
						<i class="glyphicon glyphicon-calendar"></i>
					</button>
				</span>
			</div>
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('hasta', 'Hasta:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			<div class='input-group input-group-xs' id='divhasta'>
				{!! Form::text('hasta', $hasta, array('class' => 'form-control input-xs', 'id' => 'hasta', 'placeholder' => 'Ingrese hasta')) !!}
				<span class="input-group-btn">
					<button class="btn btn-default calendar">
						<i class="glyphicon glyphicon-calendar"></i>
					</button>
				</span>
			</div>
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('observaciones', 'Observaciones:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::textarea('observaciones', null, array('style' => 'resize: none;', 'rows' => '3','class' => 'form-control input-xs', 'id' => 'observaciones', 'placeholder' => 'Ingrese observaciones')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('horarios', 'Horarios:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::textarea('horarios', null, array('style' => 'resize: none;', 'rows' => '3','class' => 'form-control input-xs', 'id' => 'horarios', 'placeholder' => 'Ingrese horarios')) !!}
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
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="desde"]').inputmask("dd/mm/yyyy");
		$('#divdesde').datetimepicker({
			pickTime: false,
			language: 'es'
		});
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="hasta"]').inputmask("dd/mm/yyyy");
		$('#divhasta').datetimepicker({
			pickTime: false,
			language: 'es'
		});
}); 
</script>