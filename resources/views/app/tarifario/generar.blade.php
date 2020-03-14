<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($tarifario, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('id', $tarifario->id, array('id' => 'id')) !!}
	<div class="form-group">
		{!! Form::label('codigo', 'Codigo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::text('codigo', null, array('class' => 'form-control input-xs', 'id' => 'codigo', 'placeholder' => 'Ingrese codigo', 'readonly' => 'true')) !!}
		</div>
		{!! Form::label('unidad', 'Unid. :', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::text('unidad', null, array('class' => 'form-control input-xs', 'id' => 'unidad', 'readonly' => 'true')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('nombre', 'Nombre:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre', 'placeholder' => 'Ingrese nombre', 'readonly' => 'true')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('tiposervicio', 'Tipo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        <div class="col-lg-9 col-md-9 col-sm-9">
            {!! Form::select('tiposervicio', $cboTipoServicio, $tiposervicio_id, array('class' => 'form-control input-xs', 'id' => 'tiposervicio')) !!}
        </div>
	</div>
	<div class="form-group">
		<table class="table table-bordered table-striped table-condensed table-hover">
			<thead>
				<tr>
					<th class="text-center">Plan</th>
					<th class="text-center">Factor</th>
					<th class="text-center">Precio</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach($plan as $k=>$v){
					echo "<tr>";
					echo "<td>".$v->nombre."</td>";
					echo "<td><input type='text' id='txtFactor".$v->id."' name='txtFactor".$v->id."' value='".$v->factor."' onblur='calcularPrecio(".$v->id.",this.value)' onkeyup='calcularPrecio(".$v->id.",this.value)' data='numero' size='5' class='form-control input-xs'/></td>";
					echo "<td><input type='text' id='txtPrecio".$v->id."' name='txtPrecio".$v->id."' value='0' size='8' data='numero' class='form-control input-xs' readonly='' /></tr>";
					echo "</tr>";
				}
				?>
			</tbody>
		</table>
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
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="unidad"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
}); 

function calcularPrecio(id,valor){
	if(valor!="" && valor!="0"){
		precio =Math.round(parseFloat(valor)*parseFloat($("#unidad").val())*1.18*100)/100;
		$("#txtPrecio"+id).val(precio);
	}
}
</script>