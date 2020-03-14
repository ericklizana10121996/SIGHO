<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($movimiento, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="form-group">
		{!! Form::label('referido', 'Referido:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
            @if ($movimiento->doctor_id != null)
                {!! Form::hidden('referido_id', $movimiento->doctor_id, array('id' => 'referido_id')) !!}
                {!! Form::text('referido', $movimiento->doctor->apellidopaterno.' '.$movimiento->doctor->apellidomaterno.' '.$movimiento->doctor->nombres, array('class' => 'form-control input-xs', 'id' => 'referido', 'placeholder' => 'Ingrese nombre')) !!}
            @else
                {!! Form::hidden('referido_id', 0, array('id' => 'referido_id')) !!}
                {!! Form::text('referido', null, array('class' => 'form-control input-xs', 'id' => 'referido', 'placeholder' => 'Ingrese nombre')) !!}
            @endif

		</div>
	</div>
    <div class="form-group">
        {!! Form::label('comentario', 'Comentario:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        <div class="col-lg-9 col-md-9 col-sm-9">
            {!! Form::textarea('comentario', $movimiento->mensajesunat, array('class' => 'form-control input-xs', 'id' => 'comentario', 'placeholder' => 'Ingrese comentario', 'cols' => '6', 'rows' => '3' )) !!}

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
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
        var personas3 = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'medico/medicoautocompletar/%QUERY',
            filter: function (personas) {
                return $.map(personas, function (movie) {
                    return {
                        value: movie.value,
                        person_id:movie.id,
                    };
                });
            }
        }
    });
    personas3.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="referido"]').typeahead(null,{
        displayKey: 'value',
        source: personas3.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="referido"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="referido_id"]').val(datum.person_id);
    });

}); 

</script>