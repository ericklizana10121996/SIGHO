<?php
if($accidente!=null){
    $hora=$accidente->hora;
    $person_id=$accidente->persona_id;
    if ($person_id != 0) {
        $paciente=$accidente->person->nombres;
    } else {
        $paciente='';
    }
    $fecha = $accidente->fecha;
}else{
    $person_id=null;
    $paciente=null;
    $hora=date("H:i");
    $fecha=date("Y-m-d");
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($accidente, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::date('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'fecha', 'onchange' => 'cargaraccidentes($(\'#doctor_id\').val())')) !!}
        		</div>
                {!! Form::label('tipoa', 'Tipo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::select('tipoa', $cboTipoAccidente, null, array('class' => 'form-control input-xs', 'id' => 'tipoa')) !!}
                </div>
        	</div>
            <div class="form-group">
        		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-8 col-md-8 col-sm-8">
                {!! Form::hidden('person_id', $person_id, array('id' => 'person_id')) !!}
        			{!! Form::text('paciente', $paciente, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Paciente')) !!}
        		</div>
        	</div>
            <div class="form-group">
        		
                {!! Form::label('referido', 'Referido:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                <div class="col-lg-8 col-md-8 col-sm-8">
                    {!! Form::text('referido', null, array('class' => 'form-control input-xs', 'id' => 'referido', 'placeholder' => 'Referido Por')) !!}
                </div>
        	</div>

            <div class="form-group">
                {!! Form::label('chofer', 'Chofer:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                <div class="col-lg-5 col-md-5 col-sm-5">
                    {!! Form::text('chofer', null, array('class' => 'form-control input-xs', 'id' => 'chofer', 'placeholder' => 'Ingrese Chofer')) !!}
                </div>

                {!! Form::label('edadc', 'Edad:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('edadc', null, array('class' => 'form-control input-xs', 'id' => 'edadc', 'placeholder' => 'Edad Chofer')) !!}
                </div>

            </div>
            <div class="form-group">
                {!! Form::label('dnic', 'DNI Chofer:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('dnic', null, array('class' => 'form-control input-xs', 'id' => 'dnic')) !!}
                </div>
                {!! Form::label('telefonoc', 'Telefono:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('telefonoc', null, array('class' => 'form-control input-xs', 'id' => 'telefonoc', 'placeholder' => 'Telefono Chofer')) !!}
                </div>
            </div>

        	<div class="form-group">
                <div class="col-lg-1 col-md-1 col-sm-1"></div>
        		{!! Form::label('lugar', 'Lugar:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('lugar', null, array('class' => 'form-control input-xs', 'id' => 'lugar', 'placeholder' => 'Lugar del accidente')) !!}
                </div>
                {!! Form::label('hora', 'Hora:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::time('hora', $hora, array('class' => 'form-control input-xs', 'id' => 'hora')) !!}
        		</div>
        	</div>

            <div class="form-group">
                <div class="col-lg-1 col-md-1 col-sm-1"></div>
                {!! Form::label('codigollamada', 'Código de Llamada:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('codigollamada', null, array('class' => 'form-control input-xs', 'id' => 'codigollamada')) !!}
                </div>
                {!! Form::label('placa', 'Placa:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('placa', null, array('class' => 'form-control input-xs', 'id' => 'placa')) !!}
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-1 col-md-1 col-sm-1"></div>
                {!! Form::label('autoriza', 'Autoriza:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-8 col-md-8 col-sm-8">
                    {!! Form::text('autoriza', null, array('class' => 'form-control input-xs', 'id' => 'autoriza', 'placeholder' => 'Persona quien atendió la llamada - CIA')) !!}
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-1 col-md-1 col-sm-1"></div>
                {!! Form::label('convenio', 'Seguro:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-8 col-md-8 col-sm-8">
                    {!! Form::select('convenio', $cboConvenio, array('class' => 'form-control input-xs', 'id' => 'convenio')) !!}
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-1 col-md-1 col-sm-1"></div>
                {!! Form::label('soatn', 'SOAT Nº:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('soatn', null, array('class' => 'form-control input-xs', 'id' => 'soatn')) !!}
                </div>
                {!! Form::label('comisaria', 'Comisaría:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('comisaria', null, array('class' => 'form-control input-xs', 'id' => 'comisaria')) !!}
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

    <?php
        if($accidente!=null){
            echo "$('#tipoa').val('".$accidente->tipoa."');$('#convenio').val(".$accidente->convenio_id.");";
        }
    ?>

	configurarAnchoModal('600');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
	var personas = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'historia/personautocompletar/%QUERY',
			filter: function (personas) {
				return $.map(personas, function (movie) {
					return {
						value: movie.value2,
						id: movie.id,
                        historia: movie.numero,
                        person_id:movie.person_id,
					};
				});
			}
		}
	});
	personas.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').typeahead(null,{
		displayKey: 'value',
		source: personas.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero"]').val(datum.historia);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);
	});

}); 

</script>