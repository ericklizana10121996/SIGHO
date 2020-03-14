<?php
    if (isset ($cie10->codigo)){
        $ciecod = $cie10->codigo;
    } else {
        $ciecod = '';
    }
?>

<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($movimiento, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    <div class="form-group">
        {!! Form::label('numero', 'Nro. Doc.:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
        <div class="col-lg-6 col-md-6 col-sm-6">
            {!! Form::text('numero', $movimiento->serie.'-'.$movimiento->numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'placeholder' => 'Ingrese', 'readonly' => 'true')) !!}

        </div>
    </div>
	<div class="form-group">
		{!! Form::label('siniestro', 'Siniestro:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('siniestro', $movimiento->comentario, array('class' => 'form-control input-xs', 'id' => 'siniestro', 'placeholder' => 'Ingrese')) !!}
		</div>
	</div>

    <div class="form-group">
        {!! Form::label('cie', 'CIE10:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
        <div class="col-lg-8 col-md-8 col-sm-8">
            {!! Form::text('cie', $ciecod, array('class' => 'form-control input-xs', 'id' => 'cie')) !!}
            {!! Form::hidden('cie_id', 0, array('id' => 'cie_id')) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('cartagarantia', 'Carta de Garantia:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
        <div class="col-lg-8 col-md-8 col-sm-8">
            {!! Form::text('cartagarantia', $movimiento->cartagarantia, array('class' => 'form-control input-xs', 'id' => 'cartagarantia', 'placeholder' => 'Numero de carta')) !!}
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

 var cie = new Bloodhound({
    datumTokenizer: function (d) {
        return Bloodhound.tokenizers.whitespace(d.value);
    },
    limit: 10,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
        url: 'facturacion/cieautocompletar/%QUERY',
        filter: function (planes) {
            return $.map(planes, function (movie) {
                return {
                    value: movie.value,
                    id: movie.id,
                };
            });
        }
    }
});
cie.initialize();
$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="cie"]').typeahead(null,{
    displayKey: 'value',
    source: cie.ttAdapter()
}).on('typeahead:selected', function (object, datum) {
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="cie"]').val(datum.value);
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="cie_id"]').val(datum.id);

});

$(document).ready(function() {
	configurarAnchoModal('350');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
}); 

</script>