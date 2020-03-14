<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($empresa, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('empresa_id', '0', array('id' => 'empresa_id')) !!}
	<div class="form-group">
		{!! Form::label('ruc', 'RUC:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('ruc', null, array('class' => 'form-control input-xs', 'id' => 'ruc', 'placeholder' => 'Ingrese ruc')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('nombre', 'Razon social:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre', 'placeholder' => 'Ingrese razon social')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('direccion', 'Direccion:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('direccion', null, array('class' => 'form-control input-xs', 'id' => 'direccion', 'placeholder' => 'Ingrese direccion')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('telefono', 'Telefono:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('telefono', null, array('class' => 'form-control input-xs', 'id' => 'telefono', 'placeholder' => 'Ingrese telefono')) !!}
		</div>
	</div>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardarEmpresa(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('500');
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="ruc"]').focus();
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').on( 'keydown', function () {
        var e = window.event; 
        var keyc = e.keyCode || e.which;

        if(keyc == 13 ){
            Verificarruc();
        }

    });
}); 

function Verificarruc() {
	var ruc = $(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="ruc"]').val();
	var _token =$('input[name=_token]').val();
	$.post('{{ URL::route("venta.verificarruc")}}', {ruc: ruc,_token: _token} , function(data){
		dat = data.split('-');
		if (dat[0] == 'SI') {
			$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="empresa_id"]').val(dat[1]);
			$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="nombre"]').val(dat[2]);
			$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="direccion"]').val(dat[3]);
			$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="telefono"]').val(dat[4]);
		}
		
	});
}
</script>