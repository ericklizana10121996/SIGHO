<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($convenio, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('listTipoServicio', null, array('id' => 'listTipoServicio')) !!}
	<div class="form-group">
		{!! Form::label('nombre', 'Nombre:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-8 col-md-8 col-sm-8">
			{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre', 'placeholder' => 'Ingrese nombre')) !!}
		</div>
	</div>
    <div class="form-group">
        {!! Form::label('plan', 'Plan:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
        <div class="col-lg-8 col-md-8 col-sm-8">
            {!! Form::hidden('plan_id', null, array('id' => 'plan_id')) !!}
            {!! Form::text('plan', null, array('class' => 'form-control input-xs', 'id' => 'plan', 'placeholder' => 'Ingrese nombre')) !!}
        </div>
    </div>
    <div class="form-group" style="display: none">
		{!! Form::label('tiposervicio', 'Tipo Servicio:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
		<div class="col-lg-5 col-md-5 col-sm-5">
			{!! Form::select('tiposervicio', $cboTipoServicio ,null, array('class' => 'form-control input-xs', 'id' => 'tiposervicio')) !!}
		</div>
        <div class="col-lg-3 col-md-3 col-sm-3">
            {!! Form::button('<i class="fa fa-plus fa-lg"></i> Agregar', array('class' => 'btn btn-info btn-sm', 'id' => 'btnAgregar', 'onclick' => 'agregar()')) !!}
        </div>
	</div>
    <div class="" id="divDetalle" style="display: none">
        <table class="table table-bordered table-striped table-condensed" id="tbDetalle">
            <thead>
                <tr>
                    <th class="text-center">Tipo Servicio</th>
                    <th class="text-center">% Desc.</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php
            /*if($detalle!=null){
                foreach ($detalle as $key => $value) {
                    echo "<tr id='tr".$value->id."'>";
                    echo "<td>".$value->tiposervicio->nombre."</td>";
                    echo "<td><input type='text' id='txtPrecio".$value->id."' name='txtPrecio".$value->id."' class='form-control input-xs' size='5' value='".$value->descuento."' data='numero'/></td>";
                    echo "<td><a href='#' onclick=\"quitar('".$value->id."')\"><i class='fa fa-minus-circle' title='Quitar' width='60px' height='60px'></i></td></tr>";
                }
            }*/
            ?>
            </tbody>
        </table>
    </div>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listTipoServicio\').val(carro);guardar(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('500');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
}); 


    var planes = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        limit: 10,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'plan/planautocompletar/%QUERY',
            filter: function (planes) {
                return $.map(planes, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                        coa: movie.coa,
                        deducible:movie.deducible,
                        ruc:movie.ruc,
                        direccion:movie.direccion,
                    };
                });
            }
        }
    });
    planes.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').typeahead(null,{
        displayKey: 'value',
        source: planes.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val(datum.id);
    });

var carro = new Array();
function agregar(){
    var id=$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tiposervicio"]').val();
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            alert("Ya agregado");
            return true;
        }
    }
    var tiposervicio=$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tiposervicio"] option:selected').text();
    $("#tbDetalle").append("<tr id='tr"+id+"'><td>"+tiposervicio+"</td>"+
                    "<td><input type='text' id='txtPrecio"+id+"' name='txtPrecio"+id+"' class='form-control input-xs' size='5' value='0' data='numero'/></td>"+
                    "<td><a href='#' onclick=\"quitar('"+id+"')\"><i class='fa fa-minus-circle' title='Quitar' width='60px' height='60px'></i></td></tr>");
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    carro.push(id);
}

function quitar(id){
    $("#tr"+id).remove();
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
}
<?php
/*if($detalle!=null){
    foreach ($detalle as $key => $value) {
        echo "carro.push(".$value->id.");";
    }
    echo "$(':input[data=\"numero\"]').inputmask('decimal', { radixPoint: \".\", autoGroup: true, groupSeparator: \"\", groupSize: 3, digits: 2 });"; 
}*/
?>
</script>