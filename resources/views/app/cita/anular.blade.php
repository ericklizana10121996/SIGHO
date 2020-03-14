<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($modelo, $formData) !!}
{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
{!! $mensaje or '<blockquote><p class="text-danger">¿Esta seguro de eliminar el registro?</p></blockquote>' !!}
<div class="form-group">
	<div class="col-lg-12 col-md-12 col-sm-12 text-right">
		<div class="form-group">
			{!! Form::label('motivo', 'Motivo:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		    <div class="col-lg-9 col-md-9 col-sm-9">
		        {!! Form::textarea('motivo', null, array('class' => 'form-control input-xs datocaja caja', 'id' => 'motivo','rows' => '2', 'required')) !!}
		    </div>
		</div>
	</div>

	<div class="col-lg-12 col-md-12 col-sm-12 text-right">
		{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
		{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal((contadorModal - 1));')) !!}
	</div>
</div>
{!! Form::close() !!}
<script type="text/javascript">
	$(document).ready(function() {
		init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
		configurarAnchoModal('350');
	}); 
</script>