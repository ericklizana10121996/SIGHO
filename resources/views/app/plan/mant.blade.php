<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($plan, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('listTipoServicio', null, array('id' => 'listTipoServicio')) !!}
	<div class="form-group">
		{!! Form::label('nombre', 'Nombre:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre', 'placeholder' => 'Ingrese nombre')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('aseguradora', 'Aseguradora:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('aseguradora', null, array('class' => 'form-control input-xs', 'id' => 'aseguradora', 'placeholder' => 'Ingrese aseguradora')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('ruc', 'RUC:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('ruc', null, array('class' => 'form-control input-xs', 'id' => 'ruc', 'placeholder' => 'Ingrese RUC')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('razonsocial', 'Contratante:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('razonsocial', null, array('class' => 'form-control input-xs', 'id' => 'razonsocial', 'placeholder' => 'Ingrese razon social')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('direccion', 'Direccion:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('direccion', null, array('class' => 'form-control input-xs', 'id' => 'direccion', 'placeholder' => 'Ingrese direccion')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('deducible', 'Deducible:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('deducible', null, array('class' => 'form-control input-xs', 'id' => 'deducible', 'placeholder' => '')) !!}
		</div>
		{!! Form::label('coaseguro', 'Coaseguro:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('coaseguro', null, array('class' => 'form-control input-xs', 'id' => 'coaseguro', 'placeholder' => '')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('consulta', 'Consulta:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('consulta', null, array('class' => 'form-control input-xs', 'id' => 'consulta', 'placeholder' => '')) !!}
		</div>
        {!! Form::label('tipopago', 'Tipo Pago:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    	<div class="col-lg-3 col-md-3 col-sm-3">
            {!! Form::select('tipopago', $cboTipoPago, null, array('class' => 'form-control input-xs', 'id' => 'tipopago')) !!}
        </div>
	</div>
    <div class="form-group">
        {!! Form::label('tipo', 'Tipo:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
    	<div class="col-lg-3 col-md-3 col-sm-3">
            {!! Form::select('tipo', $cboTipo, null, array('class' => 'form-control input-xs', 'id' => 'tipo', 'onchange' => 'validarTipo(this.value)')) !!}
        </div>
		{!! Form::label('factor', 'Factor:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label factor')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('factor', null, array('class' => 'form-control input-xs factor', 'id' => 'factor', 'placeholder' => '')) !!}
		</div>
    </div>
    <div class="form-group">
        {!! Form::label('descuentogenerico', '% Generico:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        <div class="col-lg-3 col-md-3 col-sm-3">
            {!! Form::text('descuentogenerico', null, array('class' => 'form-control input-xs', 'id' => 'descuentogenerico')) !!}
        </div>
        {!! Form::label('descuentomarca', '% Marca:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        <div class="col-lg-3 col-md-3 col-sm-3">
            {!! Form::text('descuentomarca', null, array('class' => 'form-control input-xs', 'id' => 'descuentomarca')) !!}
        </div>
    </div>
    <div class="form-group tiposervicio">
		{!! Form::label('tiposervicio', 'Tipo Servicio:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-5 col-md-5 col-sm-5">
			{!! Form::select('tiposervicio', $cboTipoServicio ,null, array('class' => 'form-control input-xs', 'id' => 'tiposervicio')) !!}
		</div>
        <div class="col-lg-3 col-md-3 col-sm-3">
            {!! Form::button('<i class="fa fa-plus fa-lg"></i> Agregar', array('class' => 'btn btn-info btn-xs', 'id' => 'btnAgregar', 'onclick' => 'agregar()')) !!}
        </div>
	</div>
    <div id="divDetalle">
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
            if($detalle!=null){
                foreach ($detalle as $key => $value) {
                    if($value->tiposervicio_id>0){
                        echo "<tr id='tr".$value->tiposervicio_id."'>";
                        echo "<td>".$value->tiposervicio->nombre."</td>";
                        echo "<td><input type='text' id='txtPrecio".$value->tiposervicio_id."' name='txtPrecio".$value->tiposervicio_id."' class='form-control input-xs' size='5' value='".$value->descuento."' data='numero'/></td>";
                        echo "<td><a href='#' onclick=\"quitar('".$value->tiposervicio_id."')\"><i class='fa fa-minus-circle' title='Quitar' width='60px' height='60px'></i></td></tr>";
                    }
                }
            }
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
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').inputmask("99999999999");
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="consulta"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="deducible"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="coaseguro"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="factor"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="descuentogenerico"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="descuentomarca"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
}); 

function validarTipo(tipo){
    if(tipo=="Aseguradora"){
        $(".factor").css("display","");
        $(".tiposervicio").css("display","none");
        $("#divDetalle").css("display","none");
    }else{
        $(".tiposervicio").css("display","");
        $(".factor").css("display","none");
        $("#divDetalle").css("display","");
    }
}

validarTipo($(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="tipo"]').val());

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
                    "<td align='center'><input type='text' id='txtPrecio"+id+"' name='txtPrecio"+id+"' style='width:60px' class='form-control input-xs' size='5' value='0' data='numero'/></td>"+
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
if($detalle!=null){
    foreach ($detalle as $key => $value) {
        echo "carro.push(".$value->tiposervicio_id.");";
    }
    echo "$(':input[data=\"numero\"]').inputmask('decimal', { radixPoint: \".\", autoGroup: true, groupSeparator: \"\", groupSize: 3, digits: 2 });"; 
}
?>
</script>