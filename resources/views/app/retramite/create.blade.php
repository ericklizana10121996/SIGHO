<style>
.tr_hover{
  color:red;
}
.form-group{
    margin-bottom: 8px !important;
}
.form-group{
    margin-bottom: 8px !important;
}
.bg-col{
    /*background-color: #FA7E60;*/
}
textarea{
    resize: none;
}
.css_rounded {
  /*border-radius: 25px;*/
  /*border: 2px solid #FA7E60;*/
  /*background: #73AD21;*/
  padding: 8px;
/*  width: 200px;
  height: 150px;*/
}

</style>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($retramite, $formData) !!} 
  {!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('datos', null, array('id' => 'datos')) !!}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group css_rounded" id="ap_retramite">
                {!! Form::label('observacion_c', 'Obs. Compañia:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-4 col-md-4 col-sm-4" style="margin-top: 5px;">
                   {!! Form::textarea('observacion_c_text', null, array('class' => 'form-control input-xs', 'id' => 'observacion_c_text', 'rows'=>'2')) !!}        
                </div>

                {!! Form::label('observacion_p', 'Descargo Personal:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-4 col-md-4 col-sm-4" style="margin-top: 5px;">
                   {!! Form::textarea('observacion_p_text', null, array('class' => 'form-control input-xs', 'id' => 'observacion_p_text', 'rows'=> '2')) !!}        
                </div>

                
                {!! Form::label('fecha_desc', 'Fecha Obs.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3" style="margin-top: 15px;">
                   {!! Form::date('fecha_desc_text', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha-desc_text')) !!}        
                </div>

                {!! Form::label('numeroCarta', 'Num. Carta:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2" style="margin-top: 15px;">
                   {!! Form::text('numeroCarta_text', null, array('class' => 'form-control input-xs', 'id' => 'numeroCarta_text')) !!}        
                </div>

                {!! Form::label('facturaAsoc', 'Fact. Asociada:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2" style="margin-top: 15px;">
                   {!! Form::hidden('facturaAsoc_id', null, array('id' => 'facturaAsoc_id')) !!}      
                   {!! Form::text('facturaAsoc_text', null, array('class' => 'form-control input-xs', 'id' => 'facturaAsoc_text')) !!}        
                </div>
            </div>
        </div>
     </div>

     <div class="form-group">
        <div class="col-lg-12 col-md-12 col-sm-12 text-right">
            {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this);')) !!}
            {!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        </div>
    </div>
{!! Form::close() !!}
<script type="text/javascript">

$(document).ready(function() {
  // -----------------------  REALIZADO POR ERICK ---------------------------------
   var facturas = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit:10,
        remote: {
            url: 'facturas/facturasautocompletar/%QUERY',
            filter: function (facturas) {
                return $.map(facturas, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                    };
                });
            }
        }
    });
    facturas.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="facturaAsoc_text"]').typeahead(null,{
        displayKey: 'value',
        source: facturas.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="facturaAsoc_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="facturaAsoc_text"]').val(datum.value);
    });
    
});

</script>