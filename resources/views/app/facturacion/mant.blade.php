<?php
if($user->id==41 || $user->id == 49)
    $serie='008';
else
    $serie='002';
?>

<style>
.tr_hover{
	color:red;
}
.form-group{
    margin-bottom: 8px !important;
}
.bg-col{
    background-color: #FA7E60;
}
textarea{
    resize: none;
}
.css_rounded {
  border-radius: 25px;
  border: 2px solid #FA7E60;
  /*background: #73AD21;*/
  padding: 15px;
/*  width: 200px;
  height: 150px;*/
}
.ocultar{
    display: none;
}
</style>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($facturacion, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('listServicio', null, array('id' => 'listServicio')) !!}
    {!! Form::hidden('listServicioSusalud', null, array('id' => 'listServicioSusalud')) !!}
    
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
        		{!! Form::label('fechaingreso', 'Fecha Ingreso:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-2m-3">
        			{!! Form::date('fechaingreso', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechaingreso', 'onblur' => 'copiarFecha(this.value)')) !!}
        		</div>
                {!! Form::label('fechasalida', 'Fecha Alta:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::date('fechasalida', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechasalida')) !!}
                </div>
        	</div>

            <div class="form-group">
               {!! Form::label('poliza', 'Póliza:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-5 col-md-5 col-sm-5">
                    {!! Form::select('poliza', $cboPoliza, null, array('class' => 'form-control input-xs', 'id' => 'poliza', 'onkeypress' => '', 'required' => true)) !!}
                </div>
            </div> 
      
           {{--  <div class="form-group">
                <div class="col-lg-2 col-md-2 col-sm-2">
                </div>
                {!! Form::label('soat', 'Soat:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::hidden('soat', 'N', array('id' => 'soat')) !!}
                    <input type="checkbox" onclick="Soat(this.checked)" />
                </div>
            </div>
 --}}            <div class="form-group">
        		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-6 col-md-6 col-sm-6">
                {!! Form::hidden('person_id', null, array('id' => 'person_id')) !!}
                {!! Form::hidden('dni', null, array('id' => 'dni')) !!}
        		{!! Form::text('paciente', null, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Paciente')) !!}
        		</div>
                {!! Form::label('numero', 'Historia:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::hidden('historia_id', null, array('id' => 'historia_id')) !!}
                    {!! Form::text('numero_historia', null, array('class' => 'form-control input-xs', 'id' => 'numero_historia')) !!}
                </div>
        	</div>
            <div class="form-group">
                {!! Form::label('plan', 'Plan:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-6 col-md-6 col-sm-6">
                    {!! Form::hidden('plan_id', null, array('id' => 'plan_id')) !!}
        			{!! Form::text('plan', null, array('class' => 'form-control input-xs', 'id' => 'plan')) !!}
        		</div>
                {!! Form::label('uci', 'UCI:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-1 col-md-1 col-sm-1" style="margin-top: 5px;">
                    {!! Form::hidden('uci', 'N', array('id' => 'uci')) !!}
                    <input type="checkbox" onclick="Uci(this.checked)" />
                </div>
            </div>

            <div class="form-group bg-col">
                {!! Form::label('retramite', 'Retramite:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label bg-col')) !!}
                <div class="col-lg-1 col-md-1 col-sm-1 bg-col" style="margin-top: 5px;">
                    {!! Form::hidden('retramite', 'N', array('id' => 'retramite')) !!}
                    <input type="checkbox" class="bg-col" onclick="Retramite(this.checked)" />
                </div>
            </div> 

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

            <div class="form-group" style="display: none;">
        		{!! Form::label('deducible', 'Deducible:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('deducible', null, array('class' => 'form-control input-xs', 'id' => 'deducible')) !!}
        		</div>
                {!! Form::label('coa', 'Coaseguro:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('coa', null, array('class' => 'form-control input-xs', 'id' => 'coa')) !!}
        		</div>
        	</div>
            <div class="form-group">
                {!! Form::label('ruc', 'RUC:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('ruc', null, array('class' => 'form-control input-xs', 'id' => 'ruc')) !!}
                </div>
                {!! Form::label('direccion', 'Direccion:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-6 col-md-6 col-sm-6">
                    {!! Form::text('direccion', null, array('class' => 'form-control input-xs', 'id' => 'direccion')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('cie', 'CIE10:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-6 col-md-6 col-sm-6">
                    {!! Form::text('cie', null, array('class' => 'form-control input-xs', 'id' => 'cie')) !!}
                    {!! Form::hidden('cie_id', 0, array('id' => 'cie_id')) !!}
                </div>
                {!! Form::label('siniestro', 'Siniestro:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('siniestro', null, array('class' => 'form-control input-xs', 'id' => 'siniestro')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('igv', 'Igv:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::hidden('igv', 'S', array('id' => 'igv')) !!}
                    <input type="checkbox" onclick="Igv(this.checked)" checked="" />
                </div>
                {!! Form::label('siniestro', 'Copago:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('copago', 0, array('class' => 'form-control input-xs', 'id' => 'copago', 'onkeyup' => 'calcularCopago(this.value);')) !!}
                </div>
                {!! Form::label('coaseguro', 'Coaseguro:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('coaseguro', 0, array('class' => 'form-control input-xs', 'id' => 'coaseguro', 'onkeyup' => 'calcularCoaseguro(this.value);')) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('cartagarantia', 'Carta de Garantia:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('cartagarantia', '', array('class' => 'form-control input-xs', 'id' => 'cartagarantia')) !!}
                </div>
                {!! Form::label('cobertura', 'Codigo de Beneficio:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('cobertura', '', array('class' => 'form-control input-xs', 'id' => 'cobertura')) !!}
                </div>
            </div>

            <div class="form-group" style="display: none;">
                {!! Form::label('descuento', 'Descuento por Días de Hospitalización:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::hidden('descuento', 'S', array('id' => 'descuento')) !!}
                    <input type="checkbox" onclick="Descuento(this.checked)" checked="" />
                </div>

                {!! Form::label('cantDias', 'Cant. Días:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label','id' => 'cantDias_label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::number('cantDias', 0, array('class' => 'form-control input-xs text-right', 'id' => 'cantDias', 'onkeyup' => 'calcularHospitalizacion(this.value);', 'min'=>'0')) !!}
                </div>
                {!! Form::label('monto', 'Monto:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label', 'id' => 'monto_label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('monto', 0.0, array('class' => 'form-control input-xs', 'id' => 'monto', 'onkeyup' => 'calcularHospitalizacion(this.value);')) !!}
                </div>
           
            </div>
            
        	<div class="form-group">
        		<div class="col-lg-6 col-md-6 col-sm-6 text-right">
        			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listServicio\').val(carro);$(\'#movimiento_id\').val(carroDoc);guardarPago(\''.$entidad.'\', this);')) !!}
        			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        		</div>
        	</div>
         </div>
         <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group datocaja">
                {!! Form::label('serieventa', 'Nro.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::select('serieventa', $cboSerie, $serie, array('class' => 'form-control input-xs', 'id' => 'serieventa', 'onchange' => 'generarNumero()')) !!}
        		</div>
                <div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('numeroventa', $numeroventa, array('class' => 'form-control input-xs', 'id' => 'numeroventa')) !!}
        		</div>
                {!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
                </div>
        	</div>
            <div class="form-group">
                {!! Form::label('tiposervicio', 'Tipo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::select('tiposervicio', $cboTipoServicio, null, array('class' => 'form-control input-xs', 'id' => 'tiposervicio')) !!}
        		</div>
                {!! Form::label('descripcion', 'Servicio:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-5 col-md-5 col-sm-5">
        			{!! Form::text('descripcion', null, array('class' => 'form-control input-xs', 'id' => 'descripcion', 'onkeypress' => '')) !!}
        		</div>
            </div>
            <div class="form-group col-lg-12 col-md-12 col-sm-12" id="divBusqueda">
            </div>

            <small><stromg>Tarifario Susalud</stromg></small>
            <div class="form-group col-lg-12 col-md-12 col-sm-12" id="divBusqueda02" style="margin-top:10px;">
            </div>
         </div>     
     </div>
     <div class="box">
        <div class="box-header">
            <h2 class="box-title col-lg-4 col-md-4 col-sm-4">Detalle <button type="button" class="btn btn-xs btn-info" title="Agregar Detalle" onclick="seleccionarServicioOtro();"><i class="fa fa-plus"></i></button>
            </h2>
        </div>
        <div class="box-body" style="max-height: 400px;overflow: auto;">
            <table class="table table-condensed table-border" id="tbDetalle">
                <thead>
                    <th class="text-center">Cant.</th>
                    <th class="text-center" colspan="2">Medico</th>
                    <th class="text-center">Rubro</th>
                    <th class="text-center">Codigo</th>
                    <th class="text-center">Descripcion</th>
                    <th class="text-center">Precio</th>
                    <th class="text-center">Dias</th>
                    <th class="text-center" colspan="2">Pago Medico</th>
                    <th class="text-center">Subtotal</th>
                </thead>
                <tbody>

                    @if(!empty($detalles) && count($detalles)>0 && false)
                    @foreach($detalles as $detalle)
                    <tr id="tr202822494"><td><input type="hidden" id="txtIdUnspsc202822494" name="txtIdUnspsc202822494" value="85121600"><input type="hidden" id="txtIdTipoServicio202822494" name="txtIdTipoServicio202822494" value="1"><input type="hidden" id="txtIdServicio202822494" name="txtIdServicio202822494" value="2494"><input type="text" data="numero" style="width: 40px; text-align: right;" class="form-control input-xs" id="txtCantidad202822494" name="txtCantidad202822494" value="1" size="3" onkeydown="if(event.keyCode==13){calcularTotal()}" onblur="calcularTotalItem('202822494')"></td><td><input type="checkbox" id="chkCopiar202822494" onclick="checkMedico(this.checked,'202822494')"></td><td><span class="twitter-typeahead" style="position: relative; display: inline-block; direction: ltr;"><input type="text" class="form-control input-xs tt-hint" disabled="" autocomplete="off" spellcheck="false" style="position: absolute; top: 0px; left: 0px; border-color: transparent; box-shadow: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgb(255, 255, 255);"><input type="text" class="form-control input-xs tt-input" id="txtMedico202822494" name="txtMedico202822494" autocomplete="off" spellcheck="false" dir="auto" style="position: relative; vertical-align: top; background-color: transparent;"><pre aria-hidden="true" style="position: absolute; visibility: hidden; white-space: pre; font-family: &quot;Source Sans Pro&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-size: 11px; font-style: normal; font-variant: normal; font-weight: 400; word-spacing: 0px; letter-spacing: 0px; text-indent: 0px; text-rendering: auto; text-transform: none;"></pre><span class="tt-dropdown-menu" style="position: absolute; top: 100%; left: 0px; z-index: 100; display: none; right: auto;"><div class="tt-dataset-13"></div></span></span><input type="hidden" id="txtIdMedico202822494" name="txtIdMedico202822494" value="0"></td><td align="left">CONSULTAS</td><td>390101</td><td><textarea style="resize: none;" class="form-control input-xs txtareaa" id="txtServicio202822494" name="txtServicio202822494">CONSULTA</textarea></td><td><input type="hidden" id="txtPrecio2202822494" name="txtPrecio2202822494" value="50.00"><input type="text" size="5" class="form-control input-xs" data="numero" id="txtPrecio202822494" style="width: 60px; text-align: right;" name="txtPrecio202822494" value="50.00" onkeydown="if(event.keyCode==13){calcularTotalItem('202822494')}" onblur="calcularTotalItem('202822494')"></td><td><input type="text" size="5" class="form-control input-xs" data="numero" id="txtDias202822494" style="width: 60px; text-align: right;" name="txtDias202822494" value="0" onkeydown="if(event.keyCode==13){calcularTotalItem('202822494')}" onblur="calcularTotalItem('202822494')"></td><td><input type="text" size="5" class="form-control input-xs" data="numero" style="width: 60px; text-align: right;" id="txtPorcentajeMedico202822494" name="txtPorcentajeMedico202822494" value="0" onkeyup="calcularPorcentajeMedico('202822494')"></td><td><input type="text" size="5" class="form-control input-xs" data="numero" style="width: 60px; text-align: right;" id="txtPrecioMedico202822494" name="txtPrecioMedico202822494" value="0" onblur="calcularTotalItem('202822494');$('#descripcion').focus();"></td><td><input type="text" readonly="" data="numero" class="form-control input-xs" size="5" name="txtTotal202822494" style="width: 60px; text-align: right;" id="txtTotal202822494" value="50.00"></td><td><a href="#" onclick="quitarServicio('202822494')"><i class="fa fa-minus-circle" title="Quitar" width="20px" height="20px"></i></a></td></tr>
                    @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <th class="text-right" id="label_acumulado">Acumulado</th>
                    <th>{!! Form::text('acumulado', null, array('class' => 'form-control input-xs', 'id' => 'sub_total', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>

                    <th class="text-right" id="label_descuento">Descuento</th>
                    <th>{!! Form::text('descuento_total','0.00', array('class' => 'form-control input-xs', 'id' => 'descuento_total', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>

                    <th class="text-right" id="label_subTotal">Sub Total</th>
                    <th>{!! Form::text('sub_total', null, array('class' => 'form-control input-xs', 'id' => 'acumulado_total', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>

                    <th class="text-right" id="label_igv">Igv</th>
                    <th>{!! Form::text('igv_02', null, array('class' => 'form-control input-xs', 'id' => 'igv_02', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>

                    <th class="text-right" id="label_total">Total</th>
                    <th>{!! Form::text('total', null, array('class' => 'form-control input-xs', 'id' => 'total', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
                </tfoot>
            </table>
        </div>
     </div>
{!! Form::close() !!}
<script type="text/javascript">

$('#ap_retramite').addClass('ocultar');

var valorbusqueda="";
var valorbusqueda="";
$(document).ready(function() {
    $('#cobertura').css('margin-top','15px');
    $('#cartagarantia').css('margin-top','15px');

	configurarAnchoModal('1300');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="descuento_total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });

    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="totalboleta"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="monto"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').inputmask("99999999999");
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numeroventa"]').inputmask("99999999");
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="deducible"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="coa"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });


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
    





// ------------------------------------------------------------------------------


	var personas = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit:10,
		remote: {
			url: 'historia/personautocompletar/%QUERY',
			filter: function (personas) {
				return $.map(personas, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
                        historia: movie.numero,
                        person_id:movie.person_id,
                        tipopaciente:movie.tipopaciente,
                        dni:movie.dni,
                        plan_id:movie.plan_id,
                        plan:movie.plan,
                        coa:movie.coa,
                        deducible:movie.deducible,
                        ruc:movie.ruc,
                        direccion:movie.direccion,
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
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').val(datum.historia);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').val(datum.dni);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);
        if(datum.plan_id>0){
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').val(datum.plan);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val(datum.coa);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="deducible"]').val(datum.deducible);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val(datum.plan_id);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val(datum.ruc);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="direccion"]').val(datum.direccion);
        }
        agregarDetallePrefactura(datum.person_id);
	});

    var personas2 = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit:10,
        remote: {
            url: 'historia/historiaautocompletar/%QUERY',
            filter: function (personas) {
                return $.map(personas, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                        historia: movie.numero,
                        person_id:movie.person_id,
                        tipopaciente:movie.tipopaciente,
                        dni:movie.dni,
                        plan_id:movie.plan_id,
                        plan:movie.plan,
                        coa:movie.coa,
                        deducible:movie.deducible,
                        ruc:movie.ruc,
                        direccion:movie.direccion,
                    };
                });
            }
        }
    });
    personas2.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').typeahead(null,{
        displayKey: 'historia',
        source: personas2.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').val(datum.historia);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').val(datum.dni);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val(datum.value);
        if(datum.plan_id>0){
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').val(datum.plan);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val(datum.coa);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="deducible"]').val(datum.deducible);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val(datum.plan_id);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val(datum.ruc);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="direccion"]').val(datum.direccion);
        }
        agregarDetallePrefactura(datum.person_id);
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
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val(datum.coa);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="deducible"]').val(datum.deducible);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val(datum.ruc);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="direccion"]').val(datum.direccion);

	});

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

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').focus();

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descripcion"]').on( 'keydown', function () {
        var e = window.event; 
        var keyc = e.keyCode || e.which;
        if(this.value.length>1 && keyc == 13 && this.value!=valorbusqueda){
            buscarServicio(this.value);
            valorbusqueda=this.value;
            this.focus();
            return false;
        }
        if(keyc == 38 || keyc == 40 || keyc == 13) {
            var tabladiv='tablaServicio';
			var child = document.getElementById(tabladiv).rows;
			var indice = -1;
			var i=0;
            $('#tablaServicio tr').each(function(index, elemento) {
                if($(elemento).hasClass("tr_hover")) {
    			    $(elemento).removeClass("par");
    				$(elemento).removeClass("impar");								
    				indice = i;
                }
                if(i % 2==0){
    			    $(elemento).removeClass("tr_hover");
    			    $(elemento).addClass("impar");
                }else{
    				$(elemento).removeClass("tr_hover");								
    				$(elemento).addClass('par');
    			}
    			i++;
    		});		 
			// return
			if(keyc == 13) {        				
			     if(indice != -1){
					var seleccionado = '';			 
					if(child[indice].id) {
					   seleccionado = child[indice].id;
					} else {
					   seleccionado = child[indice].id;
					}		 		
					seleccionarServicio(seleccionado);
				}
			} else {
				// abajo
				if(keyc == 40) {
					if(indice == (child.length - 1)) {
					   indice = 1;
					} else {
					   if(indice==-1) indice=0;
	                   indice=indice+1;
					} 
				// arriba
				} else if(keyc == 38) {
					indice = indice - 1;
					if(indice==0) indice=-1;
					if(indice < 0) {
						indice = (child.length - 1);
					}
				}	 
				child[indice].className = child[indice].className+' tr_hover';
			}
        }
    });
}); 

function guardarPago (entidad, idboton) {
    var band=true;
    var msg="";
    var total2=0;var cant=0;var pv=0;var total=0;
    for(c=0; c < carro.length; c++){
        cant=parseFloat($("#txtCantidad"+carro[c]).val());
        pv=parseFloat($("#txtPrecio"+carro[c]).val());
        total=Math.round((pv*cant) * 100) / 100;
        $("#txtTotal"+carro[c]).val(total);   
        total2=Math.round((total2+total) * 100) / 100;        
    }
    $("#total").val(total2);
    if($(".txtareaa").val()==""){
        band = false;
        msg += " *Se debe agregar una descripcion \n";    
    }

    if($("#person_id").val()==""){
        band = false;
        msg += " *No se selecciono un paciente \n";    
    }
    if($("#plan_id").val()==""){
        band = false;
        msg += " *No se selecciono un plan \n";    
    }
    for(c=0; c < carro.length; c++){
        if($("#txtIdMedico"+carro[c]).val()==0){
            band = false;
            msg += " *Debe seleccionar medico \n";
            $("#txtIdMedico"+carro[c]).focus();
        }
    }
    if(band){
    	var idformulario = IDFORMMANTENIMIENTO + entidad;
    	var data         = submitForm(idformulario);
    	var respuesta    = '';
    	var btn = $(idboton);
    	btn.button('loading');
    	data.done(function(msg) {
    		respuesta = msg;
    	}).fail(function(xhr, textStatus, errorThrown) {
    		respuesta = 'ERROR';
    	}).always(function() {
    		btn.button('reset');
    		if(respuesta === 'ERROR'){
    		}else{
    		  //alert(respuesta);
                var dat = JSON.parse(respuesta);
                if(dat[0]!==undefined){
                    resp=dat[0].respuesta;    
                }else{
                    resp='VALIDACION';
                }
                
    			if (resp === 'OK') {
    				cerrarModal();
                    buscarCompaginado('', 'Accion realizada correctamente', entidad, 'OK');
                    window.open('/juanpablo/facturacion/pdfComprobante?id='+dat[0].id,'_blank')
    			} else if(resp === 'ERROR') {
    				alert(dat[0].msg);
    			} else {
    				mostrarErrores(respuesta, idformulario, entidad);
    			}
    		}
    	});
    }else{
        alert("Corregir los sgtes errores: \n"+msg);
    }
}

var valorinicial="";
function calcularHospitalizacion(valor){
    $('#descuento_total').val($('#cantDias').val() * $('#monto').val());
    calcularTotal();
}

function buscarServicio(valor){
    $.ajax({
        type: "POST",
        url: "facturacion/buscarservicio",
        data: "idtiposervicio="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tiposervicio"]').val()+"&descripcion="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descripcion"]').val()+"&plan_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            $("#divBusqueda").html("<table class='table table-bordered table-condensed table-hover' border='1' id='tablaServicio'><thead><tr><th class='text-center'>TIPO</th><th class='text-center'>CODIGO</th><th class='text-center'>SERVICIO</th><th class='text-center'>P. UNIT.</tr></thead></table>");
            var pag=parseInt($("#pag").val());
            var d=0;
            for(c=0; c < datos.length; c++){
                var a="<tr id='"+datos[c].idservicio+"' onclick=\"seleccionarServicio('"+datos[c].idservicio+"')\"><td align='center' style='font-size:12px'>"+datos[c].tiposervicio+"</td><td style='font-size:12px'>"+datos[c].codigo+"</td><td style='font-size:12px'>"+datos[c].servicio+"</td><td align='right' style='font-size:12px'>"+datos[c].precio+"</td></tr>";
                $("#tablaServicio").append(a);           
            }
            $('#tablaServicio').DataTable({
                "scrollY":        "150px",
                "scrollCollapse": true,
                "paging":         false,
                "columnDefs": [
                    { "width": "80%", "targets": 2 }
                  ]
            });
            $('#tablaServicio_filter').css('display','none');
            $("#tablaServicio_info").css("display","none");
	    }
    });

    $.ajax({
        type: "POST",
        url: "facturacion/buscarserviciosusalud",
        data: "idtiposervicio="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tiposervicio"]').val()+"&descripcion="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descripcion"]').val()+"&plan_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            $("#divBusqueda02").html("<table class='table table-bordered table-condensed table-hover' border='1' id='tablaServicio02'><thead><tr><th class='text-center'>TIPO</th><th class='text-center'>CODIGO</th><th class='text-center'>SERVICIO</th></tr></thead></table>");
            var pag=parseInt($("#pag").val());
            var c=0;
            for(c=0; c < datos.length; c++){
                var a="<tr id='"+datos[c].idservicio+"' onclick=\"seleccionarServicioSusalud('"+datos[c].id+"')\"><td align='center' style='font-size:12px'><input type='hidden' name='tiposerviciosusalud"+datos[c].id+"' id='tiposerviciosusalud"+datos[c].id+"' value='"+datos[c].tiposervicio +"'>"+datos[c].tiposervicio+"</td><td style='font-size:12px'>"+datos[c].codigoSusalud+"</td><td style='font-size:12px'><input type='hidden' id='codigoserviciosusalud"+datos[c].id +"' value='"+datos[c].codigoSusalud +"'>"+datos[c].nombreServicio+"</td></tr>";
                $("#tablaServicio02").append(a);           
            }
            $('#tablaServicio02').DataTable({
                "scrollY":        "150px",
                "scrollCollapse": true,
                "paging":         false,
                "columnDefs": [
                    { "width": "80%", "targets": 2 }
                  ]
            });
            // $('#tablaServicio02_filter').css('display','none');
            $("#tablaServicio02_info").css("display","none");
        }
    });
       
}

function copiarFecha(fecha){
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fechasalida"]').val(fecha);
}

var carro = new Array();
var carroSusalud = new Array();

var carroDoc = new Array();
var copia = new Array();

var id_seleccionado_susalud = '';
var tiposervicio_seleccionado_susalud = '';
var codigoservicio_seleccionado_susalud = '';
var ultimo_agregado = '';
var descripcionservicio_seleccionado_susalud = '';
function seleccionarServicio(idservicio){
    var band=true;
    /*for(c=0; c < carro.length; c++){
        if(carro[c]==idservicio){
            band=false;
        }      
    }*/
    // ultimo_agregado  = idservicio;
 
    if(band){
        $.ajax({
            type: "POST",
            url: "facturacion/seleccionarservicio",
            data: "idservicio="+idservicio+"&soat="+$("#soat").val()+"&plan_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                datos=JSON.parse(a);
                var c=0;
                if(datos[c].codigo == '390101'){
                    $("#tbDetalle").append("<tr id='tr"+datos[c].idservicio+"'><td><input type='hidden' id='txtIdUnspsc"+datos[c].idservicio+"' name='txtIdUnspsc"+datos[c].idservicio+"' value='"+datos[c].idunspsc+"' /><input type='hidden' id='txtIdTipoServicio"+datos[c].idservicio+"' name='txtIdTipoServicio"+datos[c].idservicio+"' value='"+datos[c].idtiposervicio+"' /><input type='hidden' id='txtIdServicio"+datos[c].idservicio+"' name='txtIdServicio"+datos[c].idservicio+"' value='"+datos[c].id+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+datos[c].idservicio+"' name='txtCantidad"+datos[c].idservicio+"' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
                        "<td><input type='checkbox' id='chkCopiar"+datos[c].idservicio+"' onclick=\"checkMedico(this.checked,'"+datos[c].idservicio+"')\" /></td>"+
                        "<td><input type='text' class='form-control input-xs' id='txtMedico"+datos[c].idservicio+"' name='txtMedico"+datos[c].idservicio+"' /><input type='hidden' id='txtIdMedico"+datos[c].idservicio+"' name='txtIdMedico"+datos[c].idservicio+"' value='0' /></td>"+
                        "<td align='left' id='tipoSusalud"+datos[c].idservicio+"'><input type='hidden' id='txtIdSusalud"+datos[c].idservicio+"' name='txtIdSusalud"+datos[c].idservicio+"' value='0' style='width:60px;'><small>"+datos[c].tiposervicio+"</small></td><td id='codigoSusalud"+datos[c].idservicio+"'><input type='text' style='width:60px;' class='form-control input-xs' required='' name='txtSusalud"+datos[c].idservicio+"' id='txtSusalud"+datos[c].idservicio+"' value=''><input type='hidden' id='txtIdS"+datos[c].idservicio+"' name='txtIdS"+datos[c].idservicio+"' value='' /></td><td style='width:150px;'><textarea style='resize: none;width:150px;' class='form-control input-xs txtareaa' id='txtServicio"+datos[c].idservicio+"' name='txtServicio"+datos[c].idservicio+"'>"+datos[c].servicio+"</textarea></td>"+
                        "<td><input type='hidden' id='txtPrecio2"+datos[c].idservicio+"' name='txtPrecio2"+datos[c].idservicio+"' value='"+datos[c].precio+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+datos[c].idservicio+"' style='width: 60px;' name='txtPrecio"+datos[c].idservicio+"' value='"+datos[c].precio+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+"')}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
                        "<td><input type='text' size='5' class='form-control input-xs' data='numero' id='txtDias"+datos[c].idservicio+"' style='width: 60px;' name='txtDias"+datos[c].idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+"')}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" style='width:50%' /></td>"+
                        "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPorcentajeMedico"+datos[c].idservicio+"' name='txtPorcentajeMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onkeyup=\"calcularPorcentajeMedico('"+datos[c].idservicio+"')\" /></td>"+
                        "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPrecioMedico"+datos[c].idservicio+"' name='txtPrecioMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onblur=\"calcularTotalItem('"+datos[c].idservicio+"');$('#descripcion').focus();\" /></td>"+
                        "<td><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[c].idservicio+"' style='width: 60px;' id='txtTotal"+datos[c].idservicio+"' value='"+datos[c].precio+"' /></td>"+
                        "<td><a href='#' onclick=\"quitarServicio('"+datos[c].idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                }else{
                    $("#tbDetalle").append("<tr id='tr"+datos[c].idservicio+"'><td><input type='hidden' id='txtIdUnspsc"+datos[c].idservicio+"' name='txtIdUnspsc"+datos[c].idservicio+"' value='"+datos[c].idunspsc+"' /><input type='hidden' id='txtIdTipoServicio"+datos[c].idservicio+"' name='txtIdTipoServicio"+datos[c].idservicio+"' value='"+datos[c].idtiposervicio+"' /><input type='hidden' id='txtIdServicio"+datos[c].idservicio+"' name='txtIdServicio"+datos[c].idservicio+"' value='"+datos[c].id+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+datos[c].idservicio+"' name='txtCantidad"+datos[c].idservicio+"' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
                        "<td><input type='checkbox' id='chkCopiar"+datos[c].idservicio+"' onclick=\"checkMedico(this.checked,'"+datos[c].idservicio+"')\" /></td>"+
                        "<td><input type='text' class='form-control input-xs' id='txtMedico"+datos[c].idservicio+"' name='txtMedico"+datos[c].idservicio+"' /><input type='hidden' id='txtIdMedico"+datos[c].idservicio+"' name='txtIdMedico"+datos[c].idservicio+"' value='0' /></td>"+
                        "<td align='left' id='tipoSusalud"+datos[c].idservicio+"'><input type='hidden' id='txtIdSusalud"+datos[c].idservicio+"' name='txtIdSusalud"+datos[c].idservicio+"' value='0' style='width:60px;'><small>"+datos[c].tiposervicio+"</small></td><td id='codigoSusalud"+datos[c].idservicio+"'><input type='text' style='width:60px;' class='form-control input-xs' required='' name='txtSusalud"+datos[c].idservicio+"' id='txtSusalud"+datos[c].idservicio+"' value='"+datos[c].codigo+"'><input type='hidden' id='txtIdS"+datos[c].idservicio+"' name='txtIdS"+datos[c].idservicio+"' value='"+datos[c].codigo +"' /></td><td style='width:150px;'><textarea style='resize: none;width:150px;' class='form-control input-xs txtareaa' id='txtServicio"+datos[c].idservicio+"' name='txtServicio"+datos[c].idservicio+"'>"+datos[c].servicio+"</textarea></td>"+
                        "<td><input type='hidden' id='txtPrecio2"+datos[c].idservicio+"' name='txtPrecio2"+datos[c].idservicio+"' value='"+datos[c].precio+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+datos[c].idservicio+"' style='width: 60px;' name='txtPrecio"+datos[c].idservicio+"' value='"+datos[c].precio+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+"')}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
                        "<td><input type='text' size='5' class='form-control input-xs' data='numero' id='txtDias"+datos[c].idservicio+"' style='width: 60px;' name='txtDias"+datos[c].idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+"')}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" style='width:50%' /></td>"+
                        "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPorcentajeMedico"+datos[c].idservicio+"' name='txtPorcentajeMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onkeyup=\"calcularPorcentajeMedico('"+datos[c].idservicio+"')\" /></td>"+
                        "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPrecioMedico"+datos[c].idservicio+"' name='txtPrecioMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onblur=\"calcularTotalItem('"+datos[c].idservicio+"');$('#descripcion').focus();\" /></td>"+
                        "<td><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[c].idservicio+"' style='width: 60px;' id='txtTotal"+datos[c].idservicio+"' value='"+datos[c].precio+"' /></td>"+
                        "<td><a href='#' onclick=\"quitarServicio('"+datos[c].idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                }
                carro.push(datos[c].idservicio);
                $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
                eval("var planes"+datos[c].idservicio+" = new Bloodhound({"+
            		"datumTokenizer: function (d) {"+
            			"return Bloodhound.tokenizers.whitespace(d.value);"+
            		"},"+
                    "limit: 10,"+
            		"queryTokenizer: Bloodhound.tokenizers.whitespace,"+
            		"remote: {"+
            			"url: 'medico/medicoautocompletar/%QUERY',"+
            			"filter: function (planes"+datos[c].idservicio+") {"+
                            "return $.map(planes"+datos[c].idservicio+", function (movie) {"+
            					"return {"+
            						"value: movie.value,"+
            						"id: movie.id,"+
            					"};"+
            				"});"+
            			"}"+
            		"}"+
            	"});"+
            	"planes"+datos[c].idservicio+".initialize();"+
            	"$('#txtMedico"+datos[c].idservicio+"').typeahead(null,{"+
            		"displayKey: 'value',"+
            		"source: planes"+datos[c].idservicio+".ttAdapter()"+
            	"}).on('typeahead:selected', function (object, datum) {"+
            		"$('#txtMedico"+datos[c].idservicio+"').val(datum.value);"+
                    "$('#txtIdMedico"+datos[c].idservicio+"').val(datum.id);"+
                    "copiarMedico("+datos[c].idservicio+");"+
            	"});");


                eval("var codigo_s"+datos[c].idservicio+" = new Bloodhound({"+
                    "datumTokenizer: function (d) {"+
                        "return Bloodhound.tokenizers.whitespace(d.value);"+
                    "},"+
                    "limit: 10,"+
                    "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
                    "remote: {"+
                        "url: 'facturacion/susaludautocompletar/%QUERY',"+
                        "filter: function (codigo_s"+datos[c].idservicio+") {"+
                            "return $.map(codigo_s"+datos[c].idservicio+", function (movie) {"+
                                "return {"+
                                    "value: movie.codigoSusalud,"+
                                    "id: movie.codigoSusalud,"+
                                "};"+
                            "});"+
                        "}"+
                    "}"+
                "});"+
                "codigo_s"+datos[c].idservicio+".initialize();"+
                "$('#txtSusalud"+datos[c].idservicio+"').typeahead(null,{"+
                    "displayKey: 'value',"+
                    "source: codigo_s"+datos[c].idservicio+".ttAdapter()"+
                "}).on('typeahead:selected', function (object, data) {"+
                    "$('#txtSusalud"+datos[c].idservicio+"').val(data.value);"+
                    "$('#txtIdS"+datos[c].idservicio+"').val(data.id);"+
                "});");

                


                // alert(tiposervicio_seleccionado_susalud);
                
                $("#txtMedico"+datos[c].idservicio).focus(); 
                if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="editarprecio"]').val()=='S'){
                    editarPrecio(true);
                }             
            }
        });
        
        // alert(ultimo_agregado);

        // $("#tipoSusalud"+datos[c].idservicio).text(tiposervicio_seleccionado_susalud);
        // $("#codigoSusalud"+datos[c].idservicio).text(codigoservicio_seleccionado_susalud);
        // // alert(id_seleccionado_susalud);
        // $("#txtIdSusalud"+datos[c].idservicio).val(id_seleccionado_susalud);
        // id_seleccionado_susalud = '';
        // tiposervicio_seleccionado_susalud= '';
        // codigoservicio_seleccionado_susalud = '';
    }else{
        $('#txtMedico'+idservicio).focus();
    }
}

function seleccionarServicioSusalud(idservicio){
    var band=true;

    // id_seleccionado_susalud = idservicio;
    // // alert(id_seleccionado_susalud);
    // tiposervicio_seleccionado_susalud = $('#tiposerviciosusalud'+id_seleccionado_susalud).val();
    // codigoservicio_seleccionado_susalud =  $('#codigoserviciosusalud'+id_seleccionado_susalud).val();
    // descripcionservicio_seleccionado_susalud =  $('#descripcionserviciosusalud'+id_seleccionado_susalud).val();
    

    // if(band && id_seleccionado_susalud !== '' && tiposervicio_seleccionado_susalud !== '' && codigoservicio_seleccionado_susalud !==''){
    //    $("#tbDetalle").append("<tr id='tr"+idservicio+"'><td><input type='hidden' id='txtIdUnspsc"+idservicio+"' name='txtIdUnspsc"+idservicio+"' value='0' /><input type='hidden' id='txtIdTipoServicio"+idservicio+"' name='txtIdTipoServicio"+idservicio+"' value='"+datos[c].idtiposervicio+"' /><input type='hidden' id='txtIdServicio"+idservicio+"' name='txtIdServicio"+idservicio+"' value='"+datos[c].id+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+idservicio+"' name='txtCantidad"+idservicio+"' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem('"+idservicio+"')\" /></td>"+
    //             "<td><input type='checkbox' id='chkCopiar"+idservicio+"' onclick=\"checkMedico(this.checked,'"+idservicio+"')\" /></td>"+
    //             "<td><input type='text' class='form-control input-xs' id='txtMedico"+idservicio+"' name='txtMedico"+idservicio+"' /><input type='hidden' id='txtIdMedico"+idservicio+"' name='txtIdMedico"+idservicio+"' value='0' /></td>"+
    //             "<td align='left' id='tipoSusalud"+idservicio+"'><input type='hidden' id='txtIdSusalud"+idservicio+"' name='txtIdSusalud"+idservicio+"' value='"+id_seleccionado_susalud+"'>"+tiposervicio_seleccionado_susalud+"</td><td id='codigoSusalud"+idservicio+"'>"+codigoservicio_seleccionado_susalud+"</td><td><textarea style='resize: none;' class='form-control input-xs txtareaa' id='txtServicio"+idservicio+"' name='txtServicio"+idservicio+"'>"+descripcionservicio_seleccionado_susalud+"</textarea></td>"+
    //             "<td><input type='hidden' id='txtPrecio2"+idservicio+"' name='txtPrecio2"+idservicio+"' value='0.00' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+idservicio+"' style='width: 60px;' name='txtPrecio"+idservicio+"' value='0.00' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+idservicio+"')}\" onblur=\"calcularTotalItem('"+idservicio+"')\" /></td>"+
    //             "<td><input type='text' size='5' class='form-control input-xs' data='numero' id='txtDias"+idservicio+"' style='width: 60px;' name='txtDias"+idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+idservicio+"')}\" onblur=\"calcularTotalItem('"+idservicio+"')\" style='width:50%' /></td>"+
    //             "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPorcentajeMedico"+idservicio+"' name='txtPorcentajeMedico"+idservicio+"' value='0.00' onkeyup=\"calcularPorcentajeMedico('"+idservicio+"')\" /></td>"+
    //             "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPrecioMedico"+idservicio+"' name='txtPrecioMedico"+idservicio+"' value='0.00' onblur=\"calcularTotalItem('"+idservicio+"');$('#descripcion').focus();\" /></td>"+
    //             "<td><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+idservicio+"' style='width: 60px;' id='txtTotal"+idservicio+"' value='0.00' /></td>"+
    //             "<td><a href='#' onclick=\"quitarServicio('"+idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
    //         carroSusalud.push(idservicio);
         

    //          $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    //             eval("var planes"+idservicio+" = new Bloodhound({"+
    //                 "datumTokenizer: function (d) {"+
    //                     "return Bloodhound.tokenizers.whitespace(d.value);"+
    //                 "},"+
    //                 "limit: 10,"+
    //                 "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
    //                 "remote: {"+
    //                     "url: 'medico/medicoautocompletar/%QUERY',"+
    //                     "filter: function (planes"+idservicio+") {"+
    //                         "return $.map(planes"+idservicio+", function (movie) {"+
    //                             "return {"+
    //                                 "value: movie.value,"+
    //                                 "id: movie.id,"+
    //                             "};"+
    //                         "});"+
    //                     "}"+
    //                 "}"+
    //             "});"+
    //             "planes"+idservicio+".initialize();"+
    //             "$('#txtMedico"+idservicio+"').typeahead(null,{"+
    //                 "displayKey: 'value',"+
    //                 "source: planes"+idservicio+".ttAdapter()"+
    //             "}).on('typeahead:selected', function (object, datum) {"+
    //                 "$('#txtMedico"+idservicio+"').val(datum.value);"+
    //                 "$('#txtIdMedico"+idservicio+"').val(datum.id);"+
    //                 "copiarMedico("+idservicio+");"+
    //             "});");
    //             // alert(tiposervicio_seleccionado_susalud);
                
    //             $("#txtMedico"+idservicio).focus(); 
    //             if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="editarprecio"]').val()=='S'){
    //                 editarPrecio(true);
    //             }  
    //     // $.ajax({
    //     //     type: "POST",
    //     //     url: "facturacion/seleccionarserviciosusalud",
    //     //     data: "idservicio="+idservicio+"&soat="+$("#soat").val()+"&plan_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
    //     //     success: function(a) {
    //     //         datos=JSON.parse(a);
    //     //         var c=0;
             

    //     //         $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    //     //         eval("var planes"+datos[c].idservicio+" = new Bloodhound({"+
    //     //             "datumTokenizer: function (d) {"+
    //     //                 "return Bloodhound.tokenizers.whitespace(d.value);"+
    //     //             "},"+
    //     //             "limit: 10,"+
    //     //             "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
    //     //             "remote: {"+
    //     //                 "url: 'medico/medicoautocompletar/%QUERY',"+
    //     //                 "filter: function (planes"+datos[c].idservicio+") {"+
    //     //                     "return $.map(planes"+datos[c].idservicio+", function (movie) {"+
    //     //                         "return {"+
    //     //                             "value: movie.value,"+
    //     //                             "id: movie.id,"+
    //     //                         "};"+
    //     //                     "});"+
    //     //                 "}"+
    //     //             "}"+
    //     //         "});"+
    //     //         "planes"+datos[c].idservicio+".initialize();"+
    //     //         "$('#txtMedico"+datos[c].idservicio+"').typeahead(null,{"+
    //     //             "displayKey: 'value',"+
    //     //             "source: planes"+datos[c].idservicio+".ttAdapter()"+
    //     //         "}).on('typeahead:selected', function (object, datum) {"+
    //     //             "$('#txtMedico"+datos[c].idservicio+"').val(datum.value);"+
    //     //             "$('#txtIdMedico"+datos[c].idservicio+"').val(datum.id);"+
    //     //             "copiarMedico("+datos[c].idservicio+");"+
    //     //         "});");
    //     //         // alert(tiposervicio_seleccionado_susalud);
                
    //     //         $("#txtMedico"+datos[c].idservicio).focus(); 
    //     //         if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="editarprecio"]').val()=='S'){
    //     //             editarPrecio(true);
    //     //         }             
    //     //     }
    //     // });
        
    //     // // alert(ultimo_agregado);

    //     // $("#tipoSusalud"+datos[c].idservicio).text(tiposervicio_seleccionado_susalud);
    //     // $("#codigoSusalud"+datos[c].idservicio).text(codigoservicio_seleccionado_susalud);
    //     // // alert(id_seleccionado_susalud);
    //     // $("#txtIdSusalud"+datos[c].idservicio).val(id_seleccionado_susalud);
    //     // {{-- $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="txtcodigosusalud+'+datos[c].idservicio+'"]').val(id_seleccionado_susalud); --}}

    //     // id_seleccionado_susalud = '';
    //     // tiposervicio_seleccionado_susalud= '';
    //     // codigoservicio_seleccionado_susalud = '';
    // }else{
    //     $('#txtMedico'+idservicio).focus();
    // }




    // alert(id_seleccionado_susalud+'. '+ tiposervicio_seleccionado_susalud+ '. '+ codigoservicio_seleccionado_susalud);

    /*for(c=0; c < carro.length; c++){
        if(carro[c]==idservicio){
            band=false;
        }      
    }*/
    // if(band){
    //     $.ajax({
    //         type: "POST",
    //         url: "facturacion/seleccionarserviciosusalud",
    //         data: "idservicio="+idservicio+"&soat="+$("#soat").val()+"&plan_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
    //         success: function(a) {
    //             datos=JSON.parse(a);
    //             var c=0;
    //             $("#tbDetalle").append("<tr id='tr"+datos[c].idservicio+"'><td><input type='hidden' id='txtIdUnspsc"+datos[c].idservicio+"' name='txtIdUnspsc"+datos[c].idservicio+"' value='"+datos[c].idunspsc+"' /><input type='hidden' id='txtIdTipoServicio"+datos[c].idservicio+"' name='txtIdTipoServicio"+datos[c].idservicio+"' value='"+datos[c].idtiposervicio+"' /><input type='hidden' id='txtIdServicio"+datos[c].idservicio+"' name='txtIdServicio"+datos[c].idservicio+"' value='"+datos[c].id+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+datos[c].idservicio+"' name='txtCantidad"+datos[c].idservicio+"' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
    //                 "<td><input type='checkbox' id='chkCopiar"+datos[c].idservicio+"' onclick=\"checkMedico(this.checked,'"+datos[c].idservicio+"')\" /></td>"+
    //                 "<td><input type='text' class='form-control input-xs' id='txtMedico"+datos[c].idservicio+"' name='txtMedico"+datos[c].idservicio+"' /><input type='hidden' id='txtIdMedico"+datos[c].idservicio+"' name='txtIdMedico"+datos[c].idservicio+"' value='0' /></td>"+
    //                 "<td align='left'>"+datos[c].tiposervicio+"</td><td>"+datos[c].codigo+"</td><td><textarea style='resize: none;' class='form-control input-xs txtareaa' id='txtServicio"+datos[c].idservicio+"' name='txtServicio"+datos[c].idservicio+"'>"+datos[c].servicio+"</textarea></td>"+
    //                 "<td><input type='hidden' id='txtPrecio2"+datos[c].idservicio+"' name='txtPrecio2"+datos[c].idservicio+"' value='"+datos[c].precio+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+datos[c].idservicio+"' style='width: 60px;' name='txtPrecio"+datos[c].idservicio+"' value='"+datos[c].precio+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+"')}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
    //                 "<td><input type='text' size='5' class='form-control input-xs' data='numero' id='txtDias"+datos[c].idservicio+"' style='width: 60px;' name='txtDias"+datos[c].idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+"')}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" style='width:50%' /></td>"+
    //                 "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPorcentajeMedico"+datos[c].idservicio+"' name='txtPorcentajeMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onkeyup=\"calcularPorcentajeMedico('"+datos[c].idservicio+"')\" /></td>"+
    //                 "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPrecioMedico"+datos[c].idservicio+"' name='txtPrecioMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onblur=\"calcularTotalItem('"+datos[c].idservicio+"');$('#descripcion').focus();\" /></td>"+
    //                 "<td><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[c].idservicio+"' style='width: 60px;' id='txtTotal"+datos[c].idservicio+"' value='"+datos[c].precio+"' /></td>"+
    //                 "<td><a href='#' onclick=\"quitarServicio('"+datos[c].idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
    //             carro.push(datos[c].idservicio);
    //             $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    //             eval("var planes"+datos[c].idservicio+" = new Bloodhound({"+
    //                 "datumTokenizer: function (d) {"+
    //                     "return Bloodhound.tokenizers.whitespace(d.value);"+
    //                 "},"+
    //                 "limit: 10,"+
    //                 "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
    //                 "remote: {"+
    //                     "url: 'medico/medicoautocompletar/%QUERY',"+
    //                     "filter: function (planes"+datos[c].idservicio+") {"+
    //                         "return $.map(planes"+datos[c].idservicio+", function (movie) {"+
    //                             "return {"+
    //                                 "value: movie.value,"+
    //                                 "id: movie.id,"+
    //                             "};"+
    //                         "});"+
    //                     "}"+
    //                 "}"+
    //             "});"+
    //             "planes"+datos[c].idservicio+".initialize();"+
    //             "$('#txtMedico"+datos[c].idservicio+"').typeahead(null,{"+
    //                 "displayKey: 'value',"+
    //                 "source: planes"+datos[c].idservicio+".ttAdapter()"+
    //             "}).on('typeahead:selected', function (object, datum) {"+
    //                 "$('#txtMedico"+datos[c].idservicio+"').val(datum.value);"+
    //                 "$('#txtIdMedico"+datos[c].idservicio+"').val(datum.id);"+
    //                 "copiarMedico("+datos[c].idservicio+");"+
    //             "});");
    //             $("#txtMedico"+datos[c].idservicio).focus();  
    //             if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="editarprecio"]').val()=='S'){
    //                 editarPrecio(true);
    //             }             
    //         }
    //     });
    // }else{
    //     $('#txtMedico'+idservicio).focus();
    // }
}


function seleccionarServicioOtro(){
    var idservicio = "10"+Math.round(Math.random()*10000);
    $("#tbDetalle").append("<tr id='tr"+idservicio+"'><td><input type='hidden' id='txtIdUnspsc"+idservicio+"' name='txtIdUnspsc"+idservicio+"' value='0' /><input type='hidden' id='txtIdTipoServicio"+idservicio+"' name='txtIdTipoServicio"+idservicio+"' value='0' /><input type='text' data='numero' class='form-control input-xs' id='txtCantidad"+idservicio+"' name='txtCantidad"+idservicio+"' style='width: 40px;' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem2('"+idservicio+"')\" /></td>"+
        "<td><input type='checkbox' id='chkCopiar"+idservicio+"' onclick=\"checkMedico(this.checked,'"+idservicio+"')\" /></td>"+
        "<td><input type='text' class='form-control input-xs' id='txtMedico"+idservicio+"' name='txtMedico"+idservicio+"' /><input type='hidden' id='txtIdMedico"+idservicio+"' name='txtIdMedico"+idservicio+"' value='0' /></td>"+
        "<td align='left'>OTROS<select class='form-control input-xs' id='cboUnspsc"+idservicio+"' name='cboUnspsc"+idservicio+"'><option value='85101500'>Centros de salud</option><option value='85121500'>Servicios de prestadores de cuidado primario</option><option value='85121600'>Servicios medicos de doctores especialistas</option><option value='85121800'>Laboratorios medicos</option><option value='85121900'>Farmaceuticos</option><option value='85122100'>Servicios de rehabilitacion</option></select></td><td align='right'> - </td><td><textarea style='resize: none;' class='form-control input-xs txtareaa' id='txtServicio"+idservicio+"' name='txtServicio"+idservicio+"' /></td>"+
        "<td><input type='hidden' id='txtPrecio2"+idservicio+"' name='txtPrecio2"+idservicio+"' value='0' /><input type='text' size='5' class='form-control input-xs' style='width: 60px;' data='numero' id='txtPrecio"+idservicio+"' name='txtPrecio"+idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem2('"+idservicio+"')}\" onblur=\"calcularTotalItem2('"+idservicio+"')\" /></td>"+
        "<td><input type='text' size='5' style='width: 60px;' class='form-control input-xs' data='numero' id='txtDias"+idservicio+"' name='txtDias"+idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem2('"+idservicio+"')}\" onblur=\"calcularTotalItem2('"+idservicio+"')\" style='width:50%' /></td>"+
        "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPorcentajeMedico"+idservicio+"' name='txtPorcentajeMedico"+idservicio+"' value='' onkeyup=\"calcularPorcentajeMedico('"+idservicio+"')\" /></td>"+
        "<td><input type='text' size='5' class='form-control input-xs' data='numero'  id='txtPrecioMedico"+idservicio+"' name='txtPrecioMedico"+idservicio+"' value='0' style='width: 60px;' onblur=\"calcularTotalItem2('"+idservicio+"');$('#descripcion').focus();\" /></td>"+
        "<td><input type='text' style='width: 60px;' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+idservicio+"' id='txtTotal"+idservicio+"' value=0' /></td>"+
        "<td><a href='#' onclick=\"quitarServicio('"+idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
    carro.push(idservicio);
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    eval("var planes"+idservicio+" = new Bloodhound({"+
		"datumTokenizer: function (d) {"+
			"return Bloodhound.tokenizers.whitespace(d.value);"+
		"},"+
        "limit: 10,"+
		"queryTokenizer: Bloodhound.tokenizers.whitespace,"+
		"remote: {"+
			"url: 'medico/medicoautocompletar/%QUERY',"+
			"filter: function (planes"+idservicio+") {"+
                "return $.map(planes"+idservicio+", function (movie) {"+
					"return {"+
						"value: movie.value,"+
						"id: movie.id,"+
					"};"+
				"});"+
			"}"+
		"}"+
	"});"+
	"planes"+idservicio+".initialize();"+
	"$('#txtMedico"+idservicio+"').typeahead(null,{"+
		"displayKey: 'value',"+
		"source: planes"+idservicio+".ttAdapter()"+
	"}).on('typeahead:selected', function (object, datum) {"+
		"$('#txtMedico"+idservicio+"').val(datum.value);"+
        "$('#txtIdMedico"+idservicio+"').val(datum.id);"+
        "copiarMedico('"+idservicio+"');"+
	"});");
    $("#txtMedico"+idservicio).focus();             
}

function calcularTotal(){
    var total2=0;
    var total_f = 0;
    var igv = 0;
    for(c=0; c < carro.length; c++){
        var tot=parseFloat($("#txtTotal"+carro[c]).val());
        total2=Math.round((total2+tot) * 100) / 100;  
        // igv = Math.round(total2 *0.18*100)/100; 
        // total_f = Math.round((total2+igv)*100)/100;
    }
    var desc = parseFloat(Math.round($('#descuento_total').val()*100)/100);
    $("#sub_total").val(total2);
    total2 = Math.round((total2 - desc)*100)/100;
    $("#acumulado_total").val(total2);
    igv = Math.round(total2*0.18*100)/100;
    $("#igv_02").val(igv);
    total_f = Math.round((total2+igv)*100)/100;
    $("#total").val(total_f);
}

function calcularTotalItem(id){
    var cant=parseFloat($("#txtCantidad"+id).val());
    var pv=parseFloat($("#txtPrecio"+id).val());
    var total=Math.round((pv*cant) * 100) / 100;

    $("#txtTotal"+id).val(total);   
    calcularTotal();
}

function calcularPorcentajeMedico(id){
    var e = window.event; 
    var keyc = e.keyCode || e.which;
    if(keyc==13){
        var pago = Math.round((parseFloat($("#txtCantidad"+id).val())*parseFloat($("#txtPorcentajeMedico"+id).val())*parseFloat($("#txtPrecio"+id).val())/100)*100)/100;
        $("#txtPrecioMedico"+id).val(pago);
    }
}

function calcularTotalItem2(id){
    var cant=parseFloat($("#txtCantidad"+id).val());
    var pv=parseFloat($("#txtPrecio"+id).val());
    var total=Math.round((pv*cant) * 100) / 100;
    $("#txtTotal"+id).val(total);   
    calcularTotal();
}

function quitarServicio(id){
    $("#tr"+id).remove();
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
    calcularTotal();
}

function generarNumero(){
    $.ajax({
        type: "POST",
        url: "facturacion/generarNumero",
        data: "&serie="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="serieventa"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="numeroventa"]').val(a);
        }
    });
}

function Soat(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="soat"]').val('S');
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="soat"]').val('N');
    }
}

function Uci(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="uci"]').val('S');
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="uci"]').val('N');     
    }
}

function Retramite(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="retramite"]').val('S');
        $('#ap_retramite').removeClass('ocultar');
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="retramite"]').val('N');     
        $('#ap_retramite').addClass('ocultar');
        // $('#ap_retramite').addClass("css_rounded");
           
    }
}

function Igv(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="igv"]').val('S');
        $('#igv_02').css('display','initial');
        $('#label_igv').css('display','initial');
       
        $('#total').css('display','initial');
        $('#label_total').css('display','initial');

        $('#label_subTotal').text('Sub Total');
        
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="igv"]').val('N');
        $('#label_igv').css('display','none');
        $('#igv_02').css('display','none');   

        $('#total').css('display','none');
        $('#label_total').css('display','none');

        $('#label_subTotal').text('Total');
        
    }
}

function Descuento(check){
    // alert(check);
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descuento"]').val('S');
        $('#cantDias_label').css('display','initial');
        $('#cantDias').css('display','initial');
    
        $('#monto_label').css('display','initial');
        $('#monto').css('display','initial');
                
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descuento"]').val('N');
        $('#cantDias_label').css('display','none');
        $('#cantDias').css('display','none');
       
        $('#monto_label').css('display','none');
        $('#monto').css('display','none');
        
    }

    // $('#cantDias').val(0);
    // $('#monto').val(0);
  
}

function checkMedico(check,idservicio){
    if(check){
        copia.push(idservicio);
    }else{
        for(c=0; c < copia.length; c++){
            if(copia[c]==idservicio){
                copia.splice(c,1);
            }
        }
        $("#txtIdMedico"+idservicio).val(0);
        $("#txtMedico"+idservicio).val("");
        $("#txtMedico"+idservicio).focus();
    }
}

function copiarMedico(idservicio){
    if($("#chkCopiar"+idservicio).is(":checked")){
        for(c=0; c < copia.length; c++){
            $("#txtIdMedico"+copia[c]).val($("#txtIdMedico"+idservicio).val());
            $("#txtMedico"+copia[c]).val($("#txtMedico"+idservicio).val());
        }
    }
}

function copiarSusalud(idservicio){
    if($("#chkCopiar"+idservicio).is(":checked")){
        for(c=0; c < copia.length; c++){
            $("#txtSusalud"+copia[c]).val($("#txtSusalud"+idservicio).val());
            $("#txtIdS"+copia[c]).val($("#txtIdS"+idservicio).val());
        }
    }
}

function calcularCoaseguro(value){
    var e = window.event; 
    var keyc = e.keyCode || e.which;
    if(keyc==13){
        if($("#coaseguro").val()!="0" && $("#coaseguro").val()!=""){
            for(x=0; x < carro.length; x++){
                var descr = $("#txtServicio"+carro[x]).val();
                console.log(descr.search('CONSULTA'));
                console.log(descr.search('eduardo'));
                if(descr.search('CONSULTA')=="-1" && descr.search('CONS')=="-1" && $("#txtIdServicio"+carro[x]).val()!="1"){
                    var precio = Math.round(parseFloat($("#txtPrecio"+carro[x]).val())*(100 - parseFloat($("#coaseguro").val())))/100;
                    $("#txtPrecio"+carro[x]).val(precio);
                    calcularTotalItem(carro[x]);
                }
            }
        }
    }
}

function calcularCopago(value){
    var e = window.event; 
    var keyc = e.keyCode || e.which;
    if(keyc==13){
        if($("#copago").val()!="0" && $("#copago").val()!=""){
            for(x=0; x < carro.length; x++){
                var descr = $("#txtServicio"+carro[x]).val();
                console.log(descr.search('CONSULTA'));
                if((descr.search('CONSULTA')!="-1" || descr.search('CONS')!="-1" || $("#txtIdServicio"+carro[x]).val()=="1") && descr.search('INCLUYE')=="-1"){
                    var precio = Math.round((parseFloat($("#txtPrecio"+carro[x]).val()) - parseFloat($("#copago").val()))*100)/100;
                    $("#txtPrecio"+carro[x]).val(precio);
                    calcularTotalItem(carro[x]);   
                }
            }
        }
    }
}

function agregarDetallePrefactura(idpersona){
    $.ajax({
        type: "POST",
        url: "facturacion/agregarDetallePrefactura",
        data: "&persona_id="+idpersona+"&soat="+$("#soat").val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            for(c=0; c < datos.length; c++){
                // $("#tbDetalle").append("<tr id='tr"+datos[c].idservicio+"'><td><input type='hidden' id='txtIdUnspsc"+datos[c].idservicio+"' name='txtIdUnspsc"+datos[c].idservicio+"' value='"+datos[c].idunspsc+"' /><input type='hidden' id='txtIdDetalle"+datos[c].idservicio+"' name='txtIdDetalle"+datos[c].idservicio+"' value='"+datos[c].iddetalle+"' /><input type='hidden' id='txtIdTipoServicio"+datos[c].idservicio+"' name='txtIdTipoServicio"+datos[c].idservicio+"' value='"+datos[c].idtiposervicio+"' /><input type='hidden' id='txtIdServicio"+datos[c].idservicio+"' name='txtIdServicio"+datos[c].idservicio+"' value='"+datos[c].id+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+datos[c].idservicio+"' name='txtCantidad"+datos[c].idservicio+"' value='"+datos[c].cantidad+"' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
                //     "<td><input type='checkbox' id='chkCopiar"+datos[c].idservicio+"' onclick=\"checkMedico(this.checked,'"+datos[c].idservicio+"')\" /></td>"+
                //     "<td><input type='text' class='form-control input-xs' id='txtMedico"+datos[c].idservicio+"' name='txtMedico"+datos[c].idservicio+"' value='"+datos[c].medico+"' /><input type='hidden' id='txtIdMedico"+datos[c].idservicio+"' name='txtIdMedico"+datos[c].idservicio+"' value='"+datos[c].medico_id+"' /></td>"+
                //     "<td align='left'>"+datos[c].tiposervicio+"</td><td>"+datos[c].codigo+"</td><td><textarea style='resize: none;' class='form-control input-xs txtareaa' id='txtServicio"+datos[c].idservicio+"' name='txtServicio"+datos[c].idservicio+"'>"+datos[c].servicio+"</textarea></td>"+
                //     "<td><input type='hidden' id='txtPrecio2"+datos[c].idservicio+"' name='txtPrecio2"+datos[c].idservicio+"' value='"+datos[c].precio+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+datos[c].idservicio+"' style='width: 60px;' name='txtPrecio"+datos[c].idservicio+"' value='"+datos[c].precio+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+"')}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
                //     "<td><input type='text' size='5' class='form-control input-xs' data='numero' id='txtDias"+datos[c].idservicio+"' style='width: 60px;' name='txtDias"+datos[c].idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+")}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" style='width:50%' /></td>"+
                //     "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPorcentajeMedico"+datos[c].idservicio+"' name='txtPorcentajeMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onkeyup=\"calcularPorcentajeMedico('"+datos[c].idservicio+"')\" /></td>"+
                //     "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPrecioMedico"+datos[c].idservicio+"' name='txtPrecioMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onblur=\"calcularTotalItem('"+datos[c].idservicio+"');$('#descripcion').focus();\" /></td>"+
                //     "<td><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[c].idservicio+"' style='width: 60px;' id='txtTotal"+datos[c].idservicio+"' value='"+datos[c].precio+"' /></td>"+
                //     "<td><a href='#' onclick=\"quitarServicio('"+datos[c].idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                // carro.push(datos[c].idservicio);
                // $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
                // eval("var planes"+datos[c].idservicio+" = new Bloodhound({"+
                //     "datumTokenizer: function (d) {"+
                //         "return Bloodhound.tokenizers.whitespace(d.value);"+
                //     "},"+
                //     "limit: 10,"+
                //     "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
                //     "remote: {"+
                //         "url: 'medico/medicoautocompletar/%QUERY',"+
                //         "filter: function (planes"+datos[c].idservicio+") {"+
                //             "return $.map(planes"+datos[c].idservicio+", function (movie) {"+
                //                 "return {"+
                //                     "value: movie.value,"+
                //                     "id: movie.id,"+
                //                 "};"+
                //             "});"+
                //         "}"+
                //     "}"+
                // "});"+
                // "planes"+datos[c].idservicio+".initialize();"+
                // "$('#txtMedico"+datos[c].idservicio+"').typeahead(null,{"+
                //     "displayKey: 'value',"+
                //     "source: planes"+datos[c].idservicio+".ttAdapter()"+
                // "}).on('typeahead:selected', function (object, datum) {"+
                //     "$('#txtMedico"+datos[c].idservicio+"').val(datum.value);"+
                //     "$('#txtIdMedico"+datos[c].idservicio+"').val(datum.id);"+
                //     "copiarMedico("+datos[c].idservicio+");"+
                // "});");
                                // var c=0;

                $("#tbDetalle").append("<tr id='tr"+datos[c].idservicio+"'><td><input type='hidden' id='txtIdUnspsc"+datos[c].idservicio+"' name='txtIdUnspsc"+datos[c].idservicio+"' value='"+datos[c].idunspsc+"' /><input type='hidden' id='txtIdDetalle"+datos[c].idservicio+"' name='txtIdDetalle"+datos[c].idservicio+"' value='"+datos[c].iddetalle+"' /><input type='hidden' id='txtIdTipoServicio"+datos[c].idservicio+"' name='txtIdTipoServicio"+datos[c].idservicio+"' value='"+datos[c].idtiposervicio+"' /><input type='hidden' id='txtIdServicio"+datos[c].idservicio+"' name='txtIdServicio"+datos[c].idservicio+"' value='"+datos[c].id+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+datos[c].idservicio+"' name='txtCantidad"+datos[c].idservicio+"' value='"+datos[c].cantidad+"' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
                    "<td><input type='checkbox' id='chkCopiar"+datos[c].idservicio+"' onclick=\"checkMedico(this.checked,'"+datos[c].idservicio+"')\" /></td>"+
                    "<td><input type='text' class='form-control input-xs' id='txtMedico"+datos[c].idservicio+"' name='txtMedico"+datos[c].idservicio+"' value='"+datos[c].medico+"' /><input type='hidden' id='txtIdMedico"+datos[c].idservicio+"' name='txtIdMedico"+datos[c].idservicio+"' value='"+ datos[c].medico_id+"' /></td>"+
                    "<td align='left' style='width:60px;' id='tipoSusalud"+datos[c].idservicio+"'><input type='hidden' id='txtIdSusalud"+datos[c].idservicio+"' name='txtIdSusalud"+datos[c].idservicio+"' value='0'><small>"+datos[c].tiposervicio+"</small></td><td id='codigoSusalud"+datos[c].idservicio+"'><input type='text' style='width:60px;' class='form-control input-xs' required='' name='txtSusalud"+datos[c].idservicio+"' id='txtSusalud"+datos[c].idservicio+"' value='"+datos[c].codigo+"'><input type='hidden' id='txtIdS"+datos[c].idservicio+"' name='txtIdS"+datos[c].idservicio+"' value='"+datos[c].codigo +"' /></td><td style='width:150px;'><textarea style='resize: none; width:150px;' class='form-control input-xs txtareaa' id='txtServicio"+datos[c].idservicio+"' name='txtServicio"+datos[c].idservicio+"'>"+datos[c].servicio+"</textarea></td>"+
                    "<td><input type='hidden' id='txtPrecio2"+datos[c].idservicio+"' name='txtPrecio2"+datos[c].idservicio+"' value='"+datos[c].precio+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+datos[c].idservicio+"' style='width: 60px;' name='txtPrecio"+datos[c].idservicio+"' value='"+datos[c].precio+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+"')}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
                    "<td><input type='text' size='5' class='form-control input-xs' data='numero' id='txtDias"+datos[c].idservicio+"' style='width: 60px;' name='txtDias"+datos[c].idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+"')}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" style='width:50%' /></td>"+
                    "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPorcentajeMedico"+datos[c].idservicio+"' name='txtPorcentajeMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onkeyup=\"calcularPorcentajeMedico('"+datos[c].idservicio+"')\" /></td>"+
                    "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPrecioMedico"+datos[c].idservicio+"' name='txtPrecioMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onblur=\"calcularTotalItem('"+datos[c].idservicio+"');$('#descripcion').focus();\" /></td>"+
                    "<td><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[c].idservicio+"' style='width: 60px;' id='txtTotal"+datos[c].idservicio+"' value='"+datos[c].precio+"' /></td>"+
                    "<td><a href='#' onclick=\"quitarServicio('"+datos[c].idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                carro.push(datos[c].idservicio);
                $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
                eval("var planes"+datos[c].idservicio+" = new Bloodhound({"+
                    "datumTokenizer: function (d) {"+
                        "return Bloodhound.tokenizers.whitespace(d.value);"+
                    "},"+
                    "limit: 10,"+
                    "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
                    "remote: {"+
                        "url: 'medico/medicoautocompletar/%QUERY',"+
                        "filter: function (planes"+datos[c].idservicio+") {"+
                            "return $.map(planes"+datos[c].idservicio+", function (movie) {"+
                                "return {"+
                                    "value: movie.value,"+
                                    "id: movie.id,"+
                                "};"+
                            "});"+
                        "}"+
                    "}"+
                "});"+
                "planes"+datos[c].idservicio+".initialize();"+
                "$('#txtMedico"+datos[c].idservicio+"').typeahead(null,{"+
                    "displayKey: 'value',"+
                    "source: planes"+datos[c].idservicio+".ttAdapter()"+
                "}).on('typeahead:selected', function (object, datum) {"+
                    "$('#txtMedico"+datos[c].idservicio+"').val(datum.value);"+
                    "$('#txtIdMedico"+datos[c].idservicio+"').val(datum.id);"+
                    "copiarMedico("+datos[c].idservicio+");"+
                "});");


                eval("var codigo_s"+datos[c].idservicio+" = new Bloodhound({"+
                    "datumTokenizer: function (d) {"+
                        "return Bloodhound.tokenizers.whitespace(d.value);"+
                    "},"+
                    "limit: 10,"+
                    "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
                    "remote: {"+
                        "url: 'facturacion/susaludautocompletar/%QUERY',"+
                        "filter: function (codigo_s"+datos[c].idservicio+") {"+
                            "return $.map(codigo_s"+datos[c].idservicio+", function (movie) {"+
                                "return {"+
                                    "value: movie.codigoSusalud,"+
                                    "id: movie.codigoSusalud,"+
                                "};"+
                            "});"+
                        "}"+
                    "}"+
                "});"+
                "codigo_s"+datos[c].idservicio+".initialize();"+
                "$('#txtSusalud"+datos[c].idservicio+"').typeahead(null,{"+
                    "displayKey: 'value',"+
                    "source: codigo_s"+datos[c].idservicio+".ttAdapter()"+
                "}).on('typeahead:selected', function (object, data) {"+
                    "$('#txtSusalud"+datos[c].idservicio+"').val(data.value);"+
                    "$('#txtIdS"+datos[c].idservicio+"').val(data.id);"+
                "});");

                

                $("#txtMedico"+datos[c].idservicio).focus(); 
            } 
            calcularTotal();
        }
    });
}

function agregarDetallesFactura(a){
    datos=JSON.parse(a);
    //console.log(datos);
    // $(datos).each(function(c,val){
        //console.log(datos[key]);
        for(var c=0; c < datos.length; c++){
        //  datos=JSON.parse(a);
        // for(c=0; c < datos.length; c++){
        // console.log(datos[1]);
        // $("#tbDetalle").append("<tr id='tr"+datos[c].idservicio+"'><td><input type='hidden' id='txtIdUnspsc"+datos[c].idservicio+"' name='txtIdUnspsc"+datos[c].idservicio+"' value='"+datos[c].idunspsc+"' /><input type='hidden' id='txtIdTipoServicio"+datos[c].idservicio+"' name='txtIdTipoServicio"+datos[c].idservicio+"' value='"+datos[c].idtiposervicio+"' /><input type='hidden' id='txtIdServicio"+datos[c].idservicio+"' name='txtIdServicio"+datos[c].idservicio+"' value='"+datos[c].id+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+datos[c].idservicio+"' name='txtCantidad"+datos[c].idservicio+"' value='"+datos[c].cantidad+"' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
        //     "<td><input type='checkbox' id='chkCopiar"+datos[c].idservicio+"' onclick=\"checkMedico(this.checked,'"+datos[c].idservicio+"')\" /></td>"+
        //     "<td><input type='text' class='form-control input-xs' id='txtMedico"+datos[c].idservicio+"' name='txtMedico"+datos[c].idservicio+"' value='"+datos[c].medico+"' /><input type='hidden' id='txtIdMedico"+datos[c].idservicio+"' name='txtIdMedico"+datos[c].idservicio+"' value='"+datos[c].medico_id+"' /></td>"+
        //     "<td align='left'>"+datos[c].tiposervicio+"</td><td>"+datos[c].codigo+"</td><td><textarea style='resize: none;' class='form-control input-xs txtareaa' id='txtServicio"+datos[c].idservicio+"' name='txtServicio"+datos[c].idservicio+"'>"+datos[c].servicio+"</textarea></td>"+
        //     "<td><input type='hidden' id='txtPrecio2"+datos[c].idservicio+"' name='txtPrecio2"+datos[c].idservicio+"' value='"+datos[c].precio+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+datos[c].idservicio+"' style='width: 60px;' name='txtPrecio"+datos[c].idservicio+"' value='"+datos[c].precio+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+"')}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
        //     "<td><input type='text' size='5' class='form-control input-xs' data='numero' id='txtDias"+datos[c].idservicio+"' style='width: 60px;' name='txtDias"+datos[c].idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+")}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" style='width:50%' /></td>"+
        //     "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPorcentajeMedico"+datos[c].idservicio+"' name='txtPorcentajeMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onkeyup=\"calcularPorcentajeMedico('"+datos[c].idservicio+"')\" /></td>"+
        //     "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPrecioMedico"+datos[c].idservicio+"' name='txtPrecioMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onblur=\"calcularTotalItem('"+datos[c].idservicio+"');$('#descripcion').focus();\" /></td>"+
        //     "<td><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[c].idservicio+"' style='width: 60px;' id='txtTotal"+datos[c].idservicio+"' value='"+datos[c].precio+"' /></td>"+
        //     "<td><a href='#' onclick=\"quitarServicio('"+datos[c].idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
        // carro.push(datos[c].idservicio);
        // $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
        // eval("var planes"+datos[c].idservicio+" = new Bloodhound({"+
        //     "datumTokenizer: function (d) {"+
        //         "return Bloodhound.tokenizers.whitespace(d.value);"+
        //     "},"+
        //     "limit: 10,"+
        //     "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
        //     "remote: {"+
        //         "url: 'medico/medicoautocompletar/%QUERY',"+
        //         "filter: function (planes"+datos[c].idservicio+") {"+
        //             "return $.map(planes"+datos[c].idservicio+", function (movie) {"+
        //                 "return {"+
        //                     "value: movie.value,"+
        //                     "id: movie.id,"+
        //                 "};"+
        //             "});"+
        //         "}"+
        //     "}"+
        // "});"+
        // "planes"+datos[c].idservicio+".initialize();"+
        // "$('#txtMedico"+datos[c].idservicio+"').typeahead(null,{"+
        //     "displayKey: 'value',"+
        //     "source: planes"+datos[c].idservicio+".ttAdapter()"+
        // "}).on('typeahead:selected', function (object, datum) {"+
        //     "$('#txtMedico"+datos[c].idservicio+"').val(datum.value);"+
        //     "$('#txtIdMedico"+datos[c].idservicio+"').val(datum.id);"+
        //     "copiarMedico("+datos[c].idservicio+");"+
        // "});");
                        // var c=0;
                $("#tbDetalle").append("<tr id='tr"+datos[c].idservicio+"'><td><input type='hidden' id='txtIdUnspsc"+datos[c].idservicio+"' name='txtIdUnspsc"+datos[c].idservicio+"' value='"+datos[c].idunspsc+"' /><input type='hidden' id='txtIdTipoServicio"+datos[c].idservicio+"' name='txtIdTipoServicio"+datos[c].idservicio+"' value='"+datos[c].idtiposervicio+"' /><input type='hidden' id='txtIdServicio"+datos[c].idservicio+"' name='txtIdServicio"+datos[c].idservicio+"' value='"+datos[c].id+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+datos[c].idservicio+"' name='txtCantidad"+datos[c].idservicio+"' value='"+datos[c].cantidad+"' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
                    "<td><input type='checkbox' id='chkCopiar"+datos[c].idservicio+"' onclick=\"checkMedico(this.checked,'"+datos[c].idservicio+"')\" /></td>"+
                    "<td><input type='text' class='form-control input-xs' id='txtMedico"+datos[c].idservicio+"' name='txtMedico"+datos[c].idservicio+"' value='"+datos[c].medico+"'/><input type='hidden' id='txtIdMedico"+datos[c].idservicio+"' name='txtIdMedico"+datos[c].idservicio+"' value='"+datos[c].medico_id+"' /></td>"+
                    "<td align='left' id='tipoSusalud"+datos[c].idservicio+"'><input type='hidden' id='txtIdSusalud"+datos[c].idservicio+"' name='txtIdSusalud"+datos[c].idservicio+"' value='"+datos[c].tiposervicio_id+"'>"+datos[c].tiposervicio+"</td><td id='codigoSusalud"+datos[c].idservicio+"'><input type='text' class='form-control input-xs' required='' name='txtSusalud"+datos[c].idservicio+"' id='txtSusalud"+datos[c].idservicio+"' value='"+datos[c].codigo+"'><input type='hidden' id='txtIdS"+datos[c].idservicio+"' name='txtIdS"+datos[c].idservicio+"' value='"+datos[c].codigo +"' /></td><td style='width:150px;'><textarea style='resize: none;width:150px;' class='form-control input-xs txtareaa' id='txtServicio"+datos[c].idservicio+"' name='txtServicio"+datos[c].idservicio+"'>"+datos[c].servicio+"</textarea></td>"+
                    "<td><input type='hidden' id='txtPrecio2"+datos[c].idservicio+"' name='txtPrecio2"+datos[c].idservicio+"' value='"+datos[c].precio+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+datos[c].idservicio+"' style='width: 60px;' name='txtPrecio"+datos[c].idservicio+"' value='"+datos[c].precio+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+"')}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" /></td>"+
                    "<td><input type='text' size='5' class='form-control input-xs' data='numero' id='txtDias"+datos[c].idservicio+"' style='width: 60px;' name='txtDias"+datos[c].idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem('"+datos[c].idservicio+"')}\" onblur=\"calcularTotalItem('"+datos[c].idservicio+"')\" style='width:50%' /></td>"+
                    "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPorcentajeMedico"+datos[c].idservicio+"' name='txtPorcentajeMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onkeyup=\"calcularPorcentajeMedico('"+datos[c].idservicio+"')\" /></td>"+
                    "<td><input type='text' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPrecioMedico"+datos[c].idservicio+"' name='txtPrecioMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onblur=\"calcularTotalItem('"+datos[c].idservicio+"');$('#descripcion').focus();\" /></td>"+
                    "<td><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[c].idservicio+"' style='width: 60px;' id='txtTotal"+datos[c].idservicio+"' value='"+datos[c].precio+"' /></td>"+
                    "<td><a href='#' onclick=\"quitarServicio('"+datos[c].idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                carro.push(datos[c].idservicio);
                $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
                eval("var planes"+datos[c].idservicio+" = new Bloodhound({"+
                    "datumTokenizer: function (d) {"+
                        "return Bloodhound.tokenizers.whitespace(d.value);"+
                    "},"+
                    "limit: 10,"+
                    "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
                    "remote: {"+
                        "url: 'medico/medicoautocompletar/%QUERY',"+
                        "filter: function (planes"+datos[c].idservicio+") {"+
                            "return $.map(planes"+datos[c].idservicio+", function (movie) {"+
                                "return {"+
                                    "value: movie.value,"+
                                    "id: movie.id,"+
                                "};"+
                            "});"+
                        "}"+
                    "}"+
                "});"+
                "planes"+datos[c].idservicio+".initialize();"+
                "$('#txtMedico"+datos[c].idservicio+"').typeahead(null,{"+
                    "displayKey: 'value',"+
                    "source: planes"+datos[c].idservicio+".ttAdapter()"+
                "}).on('typeahead:selected', function (object, datum) {"+
                    "$('#txtMedico"+datos[c].idservicio+"').val(datum.value);"+
                    "$('#txtIdMedico"+datos[c].idservicio+"').val(datum.id);"+
                    "copiarMedico("+datos[c].idservicio+");"+
                "});");


                eval("var codigo_s"+datos[c].idservicio+" = new Bloodhound({"+
                    "datumTokenizer: function (d) {"+
                        "return Bloodhound.tokenizers.whitespace(d.value);"+
                    "},"+
                    "limit: 10,"+
                    "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
                    "remote: {"+
                        "url: 'facturacion/susaludautocompletar/%QUERY',"+
                        "filter: function (codigo_s"+datos[c].idservicio+") {"+
                            "return $.map(codigo_s"+datos[c].idservicio+", function (movie) {"+
                                "return {"+
                                    "value: movie.codigoSusalud,"+
                                    "id: movie.codigoSusalud,"+
                                "};"+
                            "});"+
                        "}"+
                    "}"+
                "});"+
                "codigo_s"+datos[c].idservicio+".initialize();"+
                "$('#txtSusalud"+datos[c].idservicio+"').typeahead(null,{"+
                    "displayKey: 'value',"+
                    "source: codigo_s"+datos[c].idservicio+".ttAdapter()"+
                "}).on('typeahead:selected', function (object, data) {"+
                    "$('#txtSusalud"+datos[c].idservicio+"').val(data.value);"+
                    "$('#txtIdS"+datos[c].idservicio+"').val(data.id);"+
                "});");

                

            $("#txtMedico"+datos[c].idservicio).focus(); 
            calcularTotalItem(datos[c].idservicio);
        }    
    // });
    calcularTotal();
}

@if(!empty($detalles) && count($detalles)>0)
    //console.log(JSON.parse('<?php echo json_encode($detalles);?>'));
    agregarDetallesFactura('<?php echo json_encode($detalles);?>');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val('<?php echo $movref->historia_id;?>');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').val('<?php echo $movref->historia;?>');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').val('<?php echo $movref->paciente;?>');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').val('<?php echo $movref->dni;?>');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val('<?php echo $movref->persona_id;?>');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan"]').val('<?php echo $movref->plan;?>');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val('<?php echo $movref->coa;?>');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="deducible"]').val('<?php echo $movref->deducible;?>');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val('<?php echo $movref->plan_id;?>');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val('<?php echo $movref->ruc;?>');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="direccion"]').val('<?php echo $movref->direccion;?>');
@endif
</script>