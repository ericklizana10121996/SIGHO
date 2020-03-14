<style>
.tr_hover{
	color:red;
}
.form-group{
    margin-bottom: 8px !important;
}
</style>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($ticket, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('listServicio', null, array('id' => 'listServicio')) !!}
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
        		</div>
                {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
        		</div>
        	</div>
            <div class="form-group">
        		{!! Form::label('paciente', 'Paciente:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-7 col-md-7 col-sm-7">
                {!! Form::hidden('person_id', null, array('id' => 'person_id')) !!}
                {!! Form::hidden('dni', null, array('id' => 'dni')) !!}
        		{!! Form::text('paciente', null, array('class' => 'form-control input-xs', 'id' => 'paciente', 'placeholder' => 'Ingrese Paciente')) !!}
        		</div>
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::button('<i class="fa fa-file fa-lg"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('historia.create', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Historia\', this);', 'title' => 'Nueva Historia')) !!}
        		</div>
        	</div>
            <div class="form-group">
        		{!! Form::label('numero', 'Historia:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::hidden('historia_id', null, array('id' => 'historia_id')) !!}
        			{!! Form::text('numero_historia', null, array('class' => 'form-control input-xs', 'id' => 'numero_historia', 'readonly' => 'true')) !!}
        		</div>
                {!! Form::label('tipopaciente', 'Tipo Paciente:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::select('tipopaciente', $cboTipoPaciente, null, array('class' => 'form-control input-xs', 'id' => 'tipopaciente')) !!}
        		</div>
        	</div>
            <div class="form-group">
                {!! Form::label('plan', 'Plan:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-8 col-md-8 col-sm-8">
                    {!! Form::hidden('plan_id', null, array('id' => 'plan_id')) !!}
        			{!! Form::text('plan', null, array('class' => 'form-control input-xs', 'id' => 'plan')) !!}
        		</div>
                {!! Form::label('soat', 'Soat:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::hidden('soat', 'N', array('id' => 'soat')) !!}
                    <input type="checkbox" onclick="Soat(this.checked)" />
                </div>
            </div>
            <div class="form-group">
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
                {!! Form::label('plan', 'Generar:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::hidden('comprobante', 'S', array('id' => 'comprobante')) !!}
                    <input type="checkbox" onchange="mostrarDatoCaja(0,this.checked)" checked id="boleta" class="col-lg-2 col-md-2 col-sm-2 control-label" />
                    {!! Form::label('boleta', 'Comprobante', array('class' => 'col-lg-10 col-md-10 col-sm-10 control-label')) !!}
                    {!! Form::hidden('pagar', 'S', array('id' => 'pagar')) !!}    
        			<input type="checkbox" onchange="mostrarDatoCaja(this.checked,0)" checked id="pago" class="col-lg-2 col-md-2 col-sm-2 control-label datocaja" />
                    {!! Form::label('pago', 'Pago', array('class' => 'col-lg-10 col-md-10 col-sm-10 control-label datocaja')) !!}
        		</div>
                {!! Form::label('formapago', 'Forma Pago:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label datocaja caja')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::select('formapago', $cboFormaPago, null, array('class' => 'form-control input-xs datocaja caja', 'id' => 'formapago', 'onchange'=>'validarFormaPago(this.value);')) !!}
        		</div>
                {!! Form::label('caja_id', 'Caja:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label datocaja caja')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::select('caja_id', $cboCaja, $idcaja, array('class' => 'form-control input-xs datocaja caja', 'id' => 'caja_id', 'readonly' => 'true')) !!}
        		</div>
            </div>
            <div class="form-group datocaja" id="divTarjeta" style="display: none;">
                {!! Form::label('tipotarjeta', 'Tarjeta:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::select('tipotarjeta', $cboTipoTarjeta, null, array('class' => 'form-control input-xs', 'id' => 'tipotarjeta')) !!}
        		</div>
                {!! Form::label('tipotarjeta2', 'Tipo Tarjeta:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::select('tipotarjeta2', $cboTipoTarjeta2, null, array('class' => 'form-control input-xs', 'id' => 'tipotarjeta2')) !!}
        		</div>
                {!! Form::label('nroref', 'Nro. Op.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('nroref', null, array('class' => 'form-control input-xs', 'id' => 'nroref')) !!}
                </div>
        	</div>

        	<div class="form-group">
                {!! Form::label('plan', 'Boletear Todo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label', 'style'=>'display:none')) !!}
        		<div class="col-lg-1 col-md-1 col-sm-1" style="display: none;">
                    {!! Form::hidden('boletear', 'N', array('id' => 'boletear')) !!}
        			<input type="checkbox" onclick="boletearTodoCaja(this.checked)" />
        		</div>
                {!! Form::label('plan', 'Editar Reparticion:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::hidden('editarprecio', 'N', array('id' => 'editarprecio')) !!}
                    <input type="checkbox" onclick="editarPrecio(this.checked)" />
                </div>
                {!! Form::label('personal', 'Descuento Personal:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label descuento', 'style' => 'display:none' )) !!}
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::hidden('descuentopersonal', 'N', array('id' => 'descuentopersonal')) !!}
                    <input type="checkbox" class="descuento" style="display: none;" onclick="editarDescuentoPersonal(this.checked)" />
                </div>
        		<div class="col-lg-6 col-md-6 col-sm-6 text-right">
        			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listServicio\').val(carro);$(\'#movimiento_id\').val(carroDoc);guardarPago(\''.$entidad.'\', this);')) !!}
        			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        		</div>
        	</div>
            <div class="form-group descuentopersonal" style="display: none">
                {!! Form::label('personal', 'Personal:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-7 col-md-7 col-sm-7">
                {!! Form::hidden('personal_id', null, array('id' => 'personal_id')) !!}
                {!! Form::text('personal', null, array('class' => 'form-control input-xs', 'id' => 'personal', 'placeholder' => 'Ingrese Personal')) !!}
                </div>
            </div>
         </div>
         <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group datocaja">
                {!! Form::label('tipodocumento', 'Tipo Doc.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::select('tipodocumento', $cboTipoDocumento, null, array('class' => 'form-control input-xs', 'id' => 'tidodocumento', 'onchange' => 'generarNumero()')) !!}
        		</div>
                {!! Form::label('numeroventa', 'Nro.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::text('serieventa', $serie, array('class' => 'form-control input-xs datocaja', 'id' => 'serieventa')) !!}
        		</div>
                <div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('numeroventa', $numeroventa, array('class' => 'form-control input-xs', 'id' => 'numeroventa')) !!}
        		</div>
        	</div>
            <div class="form-group datocaja datofactura" style="display: none;">
                {!! Form::label('ruc', 'RUC:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::text('ruc', null, array('class' => 'form-control input-xs', 'id' => 'ruc')) !!}
        		</div>
                {!! Form::label('razon', 'Razon:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-6 col-md-6 col-sm-6">
        			{!! Form::text('razon', null, array('class' => 'form-control input-xs datocaja', 'id' => 'razon')) !!}
        		</div>
            </div>
            <div class="form-group datocaja datofactura" style="display: none;">
                {!! Form::label('direccion', 'Direccion:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-10 col-md-10 col-sm-10">
        			{!! Form::text('direccion', null, array('class' => 'form-control input-xs', 'id' => 'direccion')) !!}
        		</div>
        	</div>
            <div class="form-group">
                {!! Form::label('referido', 'Referido:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-10 col-md-10 col-sm-10">
                    {!! Form::hidden('referido_id', 0, array('id' => 'referido_id')) !!}
                    {!! Form::text('referido', null, array('class' => 'form-control input-xs', 'id' => 'referido')) !!}
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
         </div>     
     </div>
     <div class="box">
        <div class="box-header">
            <h2 class="box-title col-lg-4 col-md-4 col-sm-4">Detalle <button type="button" class="btn btn-xs btn-info" title="Agregar Detalle" onclick="seleccionarServicioOtro();"><i class="fa fa-plus"></i></button></h2>
            <div class="text-right col-lg-8 col-md-8 col-sm-8">
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::hidden('movimientoref', 'N', array('id' => 'movimientoref')) !!}
                    <input type="checkbox" onclick="movimientoRef(this.checked)" />
                </div>
                {!! Form::label('movimiento', 'Doc. Ref.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::hidden('movimiento_id', 0, array('id' => 'movimiento_id')) !!}
                    {!! Form::text('movimiento', null, array('class' => 'form-control input-xs', 'id' => 'movimiento', 'style' => 'display:none')) !!}
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <table class="table table-condensed table-border" id="tbDoc" style="display: none;">
                        <thead>
                            <tr>
                                <th class="text-center">Doc.</th>
                                <th class="text-center">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-center">Total</th>
                                <th class="text-center" id='totalDoc'>0</th>
                            </tr>                            
                        </tfoot>
                    </table>    
                </div>
            </div>
        </div>
        <div class="box-body">
            <table class="table table-condensed table-border" id="tbDetalle">
                <thead>
                    <th class="text-center">Cant.</th>
                    <th class="text-center" colspan="2">Medico</th>
                    <th class="text-center">Rubro</th>
                    <th class="text-center">Descripcion</th>
                    <th class="text-center">Precio</th>
                    <th class="text-center">
                        <select id='cboDescuento' name='cboDescuento' class="input-xs" style='width: 60px;'>
                            <option value='P'>%</option>
                            <option value='M'>Monto</option>
                        </select><br />&nbsp;Desc.
                    </th>
                    <th class="text-center">Hospital</th>
                    <th class="text-center">Medico</th>
                    <th class="text-center">Subtotal</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <th class="text-right" colspan="7">Comprobante</th>
                    <th>{!! Form::text('totalboleta', null, array('class' => 'form-control input-xs', 'id' => 'totalboleta', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
                    <th class="text-right">Pago</th>
                    <th>{!! Form::text('total', null, array('class' => 'form-control input-xs', 'id' => 'total', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
                </tfoot>
            </table>
        </div>
     </div>
{!! Form::close() !!}
<script type="text/javascript">
var valorbusqueda="";
$(document).ready(function() {
	configurarAnchoModal('1300');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="totalboleta"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').inputmask("99999999999");
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="serieventa"]').inputmask("999");
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numeroventa"]').inputmask("99999999");
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="deducible"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="coa"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
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
        if(datum.tipopaciente=="Hospital"){
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val("Particular");
        }else{
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val(datum.tipopaciente);
        }    
        
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);
	});
    
    var personas2 = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'ticket/personrucautocompletar/%QUERY',
			filter: function (personas2) {
				return $.map(personas2, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
                        ruc: movie.ruc,
                        razonsocial:movie.razonsocial,
                        direccion:movie.direccion,
                        label: movie.label,
					};
				});
			}
		}
	});
	personas2.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').typeahead(null,{
		displayKey: 'label',
		source: personas2.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val(datum.ruc);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="razon"]').val(datum.razonsocial);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="direccion"]').val(datum.direccion);
	});

    var personas4 = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'ticket/personrazonautocompletar/%QUERY',
            filter: function (personas4) {
                return $.map(personas4, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                        ruc: movie.ruc,
                        razonsocial:movie.razonsocial,
                        direccion:movie.direccion,
                        label: movie.label,
                    };
                });
            }
        }
    });
    personas4.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="razon"]').typeahead(null,{
        displayKey: 'label',
        source: personas4.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val(datum.ruc);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="razon"]').val(datum.razonsocial);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="direccion"]').val(datum.direccion);
    });
    
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

    var personal = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit:10,
        remote: {
            url: 'employee/trabajadorautocompletar/%QUERY',
            filter: function (personas) {
                return $.map(personas, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                    };
                });
            }
        }
    });
    personal.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="personal"]').typeahead(null,{
        displayKey: 'value',
        source: personal.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="personal_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="personal"]').val(datum.value);
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
	});
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="paciente"]').focus();

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descripcion"]').on( 'keydown', function () {
        var e = window.event; 
        var keyc = e.keyCode || e.which;
        if(this.value.length>1 && keyc == 13){
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

function guardarHistoria (entidad, idboton) {
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
			if (dat[0]!==undefined && (dat[0].respuesta=== 'OK')) {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').val(dat[0].historia);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val(dat[0].tipopaciente);
                alert('Historia Generada');
                window.open("historia/pdfhistoria?id="+dat[0].id,"_blank");
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

function guardarPago (entidad, idboton) {
    var band=true;
    var msg="";
    if($("#person_id").val()==""){
        band = false;
        msg += " *No se selecciono un paciente \n";    
    }
    if($("#plan_id").val()==""){
        band = false;
        msg += " *No se selecciono un plan \n";    
    }
    if($("#tipopaciente").val()!="Convenio"){
        for(c=0; c < carro.length; c++){
            if($("#txtIdTipoServicio"+carro[c]).val()!="1" && $("#txtIdTipoServicio"+carro[c]).val()!="6" && $("#txtIdTipoServicio"+carro[c]).val()!="7" && $("#txtIdTipoServicio"+carro[c]).val()!="8" && $("#txtIdTipoServicio"+carro[c]).val()!="12"){
                if($("#referido_id").val()=="0"){
                    band = false;
                    msg += " *Debe indicar referido \n";
                }
            }
        }  
    }
    for(c=0; c < carro.length; c++){
        if($("#txtDescuento"+carro[c]).val()==""){
            band = false;
            msg += " *Descuento no puede ser vacio \n";            
        }
        if($("#txtIdMedico"+carro[c]).val()==0){
            band = false;
            msg += " *Debe seleccionar medico \n";                        
        }
        var hospital = parseFloat($("#txtPrecioHospital"+carro[c]).val());
        var doctor = parseFloat($("#txtPrecioMedico"+carro[c]).val());
        var precio = parseFloat($("#txtPrecio"+carro[c]).val());
        var desc = parseFloat($("#txtDescuento"+carro[c]).val());
        if($("#cboDescuento").val()=="P"){
            precio = Math.round(100*(precio*(100-desc)/100))/100;
        }else{
            precio = precio - desc;
        }
        if((hospital + doctor) != precio){
            band = false;
            msg += " *Suma de pago hospital + doctor no coincide con el precio \n";
        }      
    } 
    if(parseFloat($("#total").val())>700){
        if($("#dni").val().trim().length!=8){
            band = false;
            msg += " *El paciente debe tener DNI correcto \n";
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
                    if(dat[0].pagohospital!="0"){
                        window.open('/juanpablo/ticket/pdfComprobante?ticket_id='+dat[0].ticket_id,'_blank')
                    }else{
                        window.open('/juanpablo/ticket/pdfPrefactura?ticket_id='+dat[0].ticket_id,'_blank')
                    }
                    if(dat[0].notacredito_id!="0"){
                        window.open('/juanpablo/notacredito/pdfComprobante?id='+dat[0].notacredito_id,'_blank');
                    }
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

function validarFormaPago(forma){
    if(forma=="Tarjeta"){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} div[id="divTarjeta"]').css("display","");
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} div[id="divTarjeta"]').css("display","none");
    }
}

var valorinicial="";
function buscarServicio(valor){
    //if(valorinicial!=valor){valorinicial=valor;
        $.ajax({
            type: "POST",
            url: "ticket/buscarservicio",
            data: "idtiposervicio="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tiposervicio"]').val()+"&descripcion="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descripcion"]').val()+"&tipopaciente="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val()+"&plan_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                datos=JSON.parse(a);
                $("#divBusqueda").html("<table class='table table-bordered table-condensed table-hover' border='1' id='tablaServicio'><thead><tr><th class='text-center'>TIPO</th><th class='text-center'>SERVICIO</th><th class='text-center'>P. UNIT.</tr></thead></table>");
                var pag=parseInt($("#pag").val());
                var d=0;
                for(c=0; c < datos.length; c++){
                    var a="<tr id='"+datos[c].idservicio+"' onclick=\"seleccionarServicio('"+datos[c].idservicio+"')\"><td align='center'>"+datos[c].tiposervicio+"</td><td>"+datos[c].servicio+"</td><td align='right'>"+datos[c].precio+"</td></tr>";
                    $("#tablaServicio").append(a);           
                }
                $('#tablaServicio').DataTable({
                    "scrollY":        "250px",
                    "scrollCollapse": true,
                    "paging":         false
                });
                $('#tablaServicio_filter').css('display','none');
                $("#tablaServicio_info").css("display","none");
    	    }
        });
    //}
}

var carro = new Array();
var carroDoc = new Array();
var copia = new Array();
function seleccionarServicio(idservicio){
    var band=true;
    for(c=0; c < carro.length; c++){
        if(carro[c]==idservicio){
            band=false;
        }      
    }
    if(band){
        $.ajax({
            type: "POST",
            url: "ticket/seleccionarservicio",
            data: "idservicio="+idservicio+"&tipopaciente="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val()+"&formapago="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="formapago"]').val()+"&tarjeta="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipotarjeta2"]').val()+"&plan_id="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                datos=JSON.parse(a);
                var c=0;
                $("#tbDetalle").append("<tr id='tr"+datos[c].idservicio+"'><td><input type='hidden' id='txtIdTipoServicio"+datos[c].idservicio+"' name='txtIdTipoServicio"+datos[c].idservicio+"' value='"+datos[c].idtiposervicio+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+datos[c].idservicio+"' name='txtCantidad"+datos[c].idservicio+"' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem("+datos[c].idservicio+")\" /></td>"+
                    "<td><input type='checkbox' id='chkCopiar"+datos[c].idservicio+"' onclick='checkMedico(this.checked,"+datos[c].idservicio+")' /></td>"+
                    "<td><input type='text' class='form-control input-xs' id='txtMedico"+datos[c].idservicio+"' name='txtMedico"+datos[c].idservicio+"' /><input type='hidden' id='txtIdMedico"+datos[c].idservicio+"' name='txtIdMedico"+datos[c].idservicio+"' value='0' /></td>"+
                    "<td align='left'>"+datos[c].tiposervicio+"</td><td>"+datos[c].servicio+"</td>"+
                    "<td><input type='hidden' id='txtPrecio2"+datos[c].idservicio+"' name='txtPrecio2"+datos[c].idservicio+"' value='"+datos[c].precio+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+datos[c].idservicio+"' style='width: 60px;' name='txtPrecio"+datos[c].idservicio+"' value='"+datos[c].precio+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+datos[c].idservicio+")}\" onblur=\"calcularTotalItem("+datos[c].idservicio+")\" /></td>"+
                    "<td><input type='text' size='5' class='form-control input-xs' data='numero' id='txtDescuento"+datos[c].idservicio+"' style='width: 60px;' name='txtDescuento"+datos[c].idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+datos[c].idservicio+")}\" onblur=\"calcularTotalItem("+datos[c].idservicio+")\" style='width:50%' /></td>"+
                    "<td><input type='hidden' id='txtPrecioHospital2"+datos[c].idservicio+"' name='txtPrecioHospital2"+datos[c].idservicio+"' value='"+datos[c].preciohospital+"' /><input type='text' readonly='' size='5' class='form-control input-xs' style='width: 60px;' data='numero'  id='txtPrecioHospital"+datos[c].idservicio+"' name='txtPrecioHospital"+datos[c].idservicio+"' value='"+datos[c].preciohospital+"' onblur=\"calcularTotalItem("+datos[c].idservicio+")\" /></td>"+
                    "<td><input type='hidden' id='txtPrecioMedico2"+datos[c].idservicio+"' name='txtPrecioMedico2"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' /><input type='text' readonly='' size='5' class='form-control input-xs' data='numero' style='width: 60px;' id='txtPrecioMedico"+datos[c].idservicio+"' name='txtPrecioMedico"+datos[c].idservicio+"' value='"+datos[c].preciomedico+"' onblur=\"calcularTotalItem("+datos[c].idservicio+")\" /></td>"+
                    "<td><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[c].idservicio+"' style='width: 60px;' id='txtTotal"+datos[c].idservicio+"' value='"+datos[c].precio+"' /></td>"+
                    "<td><a href='#' onclick=\"quitarServicio('"+datos[c].idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                carro.push(idservicio);
                $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
                calcularCoaseguro();
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
                $("#txtMedico"+datos[c].idservicio).focus();  
                if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="editarprecio"]').val()=='S'){
                    editarPrecio(true);
                }             
            }
        });
    }else{
        $('#txtMedico'+idservicio).focus();
    }
}

function seleccionarServicioOtro(){
    var idservicio = "0"+Math.round(Math.random()*100);
    $("#tbDetalle").append("<tr id='tr"+idservicio+"'><td><input type='hidden' id='txtIdTipoServicio"+idservicio+"' name='txtIdTipoServicio"+idservicio+"' value='0' /><input type='text' data='numero' class='form-control input-xs' id='txtCantidad"+idservicio+"' name='txtCantidad"+idservicio+"' style='width: 40px;' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem2('"+idservicio+"')\" /></td>"+
        "<td><input type='checkbox' id='chkCopiar"+idservicio+"' onclick=\"checkMedico(this.checked,'"+idservicio+"')\" /></td>"+
        "<td><input type='text' class='form-control input-xs' id='txtMedico"+idservicio+"' name='txtMedico"+idservicio+"' /><input type='hidden' id='txtIdMedico"+idservicio+"' name='txtIdMedico"+idservicio+"' value='0' /></td>"+
        "<td align='left'>OTROS</td><td><textarea class='form-control input-xs' id='txtServicio"+idservicio+"' name='txtServicio"+idservicio+"' /></td>"+
        "<td><input type='hidden' id='txtPrecio2"+idservicio+"' name='txtPrecio2"+idservicio+"' value='0' /><input type='text' size='5' class='form-control input-xs' style='width: 60px;' data='numero' id='txtPrecio"+idservicio+"' name='txtPrecio"+idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem2('"+idservicio+"')}\" onblur=\"calcularTotalItem2('"+idservicio+"')\" /></td>"+
        "<td><input type='text' size='5' style='width: 60px;' class='form-control input-xs' data='numero' id='txtDescuento"+idservicio+"' name='txtDescuento"+idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem2('"+idservicio+"')}\" onblur=\"calcularTotalItem2('"+idservicio+"')\" style='width:50%' /></td>"+
        "<td><input type='hidden' id='txtPrecioHospital2"+idservicio+"' name='txtPrecioHospital2"+idservicio+"' value='0' /><input type='text' size='5' style='width: 60px;' class='form-control input-xs' data='numero'  id='txtPrecioHospital"+idservicio+"' name='txtPrecioHospital"+idservicio+"' value='0' onblur=\"calcularTotalItem2("+idservicio+")\" /></td>"+
        "<td><input type='hidden' id='txtPrecioMedico2"+idservicio+"' name='txtPrecioMedico2"+idservicio+"' value='0' /><input type='text' size='5' class='form-control input-xs' data='numero'  id='txtPrecioMedico"+idservicio+"' name='txtPrecioMedico"+idservicio+"' value='0' style='width: 60px;' /></td>"+
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
    for(c=0; c < carro.length; c++){
        var tot=parseFloat($("#txtTotal"+carro[c]).val());
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#total").val(total2);
    var total2=0;
    for(c=0; c < carro.length; c++){
        var tot=Math.round(100*(parseFloat($("#txtPrecioHospital"+carro[c]).val())*parseFloat($("#txtCantidad"+carro[c]).val())))/100;
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#totalboleta").val(total2);
}

function calcularCoaseguro(){
    if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val()!="" && parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val())>0){
        var ded=parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val());
        if(ded==100){
            ded=0;
        }
    }else{
        var ded=100;
    }
    for(c=0; c < carro.length; c++){
        if($("#txtIdTipoServicio"+carro[c]).val()!="1" && $("#txtIdTipoServicio"+carro[c]).val()!="0"){//Para todo lo q no es consulta
            var cant=parseFloat($("#txtCantidad"+carro[c]).val());
            var pv=parseFloat($("#txtPrecio2"+carro[c]).val());
            var tot=parseFloat($("#txtTotal"+carro[c]).val());
            var precio = Math.round((pv*ded/100)*100)/100;
            var hospital = Math.round((parseFloat($("#txtPrecioHospital2"+carro[c]).val())*ded/100)*100)/100;
            var medico = Math.round((parseFloat($("#txtPrecioMedico2"+carro[c]).val())*ded/100)*100)/100;
            $("#txtPrecio"+carro[c]).val(precio);  
            var desc=parseFloat($("#txtDescuento"+carro[c]).val());
            if($("#cboDescuento").val()=="P"){
                pv = Math.round(100*(pv * (100 - desc)/100))/100;
                hospital = Math.round(100*(hospital * (100 - desc)/100))/100;
                medico = Math.round(100*(medico * (100 - desc)/100))/100;
            }else{
                pv = pv - desc;
                medico = medico - desc;
            }
            var total=Math.round((pv*cant*ded/100) * 100) / 100;
            $("#txtTotal"+carro[c]).val(total);  
            $("#txtPrecioHospital"+carro[c]).val(hospital);
            $("#txtPrecioMedico"+carro[c]).val(medico);
        }else{
            if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()!="6" && $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val()=="Convenio"){
                var cant=parseFloat($("#txtCantidad"+carro[c]).val());
                var pv=parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="deducible"]').val());
                var tot=parseFloat($("#txtTotal"+carro[c]).val());
                var total=Math.round((pv*cant) * 100) / 100;
                $("#txtTotal"+carro[c]).val(total);  
                $("#txtPrecio"+carro[c]).val(pv);  
                $("#txtPrecioHospital"+carro[c]).val(pv);
                var medico = parseFloat($("#txtPrecioMedico2"+carro[c]).val());
                $("#txtPrecioMedico"+carro[c]).val(medico);
            }
        }
    }
    calcularTotal();
}

function calcularTotalItem(id){
    var cant=parseFloat($("#txtCantidad"+id).val());
    var pv=parseFloat($("#txtPrecio"+id).val());
    var pv2=parseFloat($("#txtPrecio2"+id).val());
    var desc=parseFloat($("#txtDescuento"+id).val());
    var hosp=parseFloat($("#txtPrecioHospital"+id).val());
    if($("#cboDescuento").val()=="P"){
        pv = Math.round(100*(pv * (100 - desc)/100))/100;
    }else{
        pv = pv - desc;
    }
    if($("#txtIdTipoServicio"+id).val()!="1" && $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()!="6"){
        if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val()!=""){
            var ded=parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val());
            if(ded==100){
               ded=0;
            }else if(ded==0){
                ded=100;
            }
        }else{
            var ded=100;
        }
    }else{
        var ded = 100;
    }
    if(ded>0 && ded<100){
        pv = pv2;
    }
    pv=Math.round((pv*ded/100) * 100) / 100;
    var total=Math.round((pv*cant) * 100) / 100;
    var med = Math.round((parseFloat($("#txtPrecioMedico2"+id).val())*ded/100)*100)/100;

    if($("#txtIdTipoServicio"+id).val()!="1"){
        $("#txtTotal"+id).val(total);   
        if(med==0){
            var hos=pv - med;
            $("#txtPrecioHospital"+id).val(hos);    
        }
        $("#txtPrecioMedico"+id).val(med);
    }else if($("#txtIdTipoServicio"+id).val()=="1" && $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()=="6"){
        $("#txtTotal"+id).val(total);
        med = pv - hosp;
        $("#txtPrecioMedico"+id).val(med);
    }
    calcularTotal();
}

function calcularTotalItem2(id){
    var cant=parseFloat($("#txtCantidad"+id).val());
    var pv=parseFloat($("#txtPrecio"+id).val());
    var desc=parseFloat($("#txtDescuento"+id).val());
    var hosp=parseFloat($("#txtPrecioHospital"+id).val());
    if($("#cboDescuento").val()=="P"){
        pv = Math.round(100*(pv * (100 - desc)/100))/100;
    }else{
        pv = pv - desc;
    }
    /*if($("#txtIdTipoServicio"+id).val()!="1" && $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()!="6"){
        if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val()!=""){
            var ded=parseFloat($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="coa"]').val());
            if(ded==100){
               ded=0;
            }else if(ded==0){
                ded=100;
            }
        }else{
            var ded=100;
        }
    }else{*/
        var ded = 100;
    //}
    var total=Math.round((pv*cant*ded/100) * 100) / 100;
    var med = pv - hosp;
    if($("#txtIdTipoServicio"+id).val()!="1"){
        $("#txtTotal"+id).val(total);   
        $("#txtPrecioMedico"+id).val(med);
    }else if($("#txtIdTipoServicio"+id).val()=="1" && $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="plan_id"]').val()=="6"){
        $("#txtTotal"+id).val(total);
        $("#txtPrecioMedico"+id).val(med);
    }
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

function mostrarDatoCaja(check,check2){
    if(check==0){
        check = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="pago"]').is(":checked");
    }
    if(check2==0){
        check2 = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="boleta"]').is(":checked");
    }
    if(check2){//CON BOLETA
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="comprobante"]').val('S');
        $(".datocaja").css("display","");
        if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="tipodocumento"]').val()=="Factura"){
            $(".datofactura").css("display","");
        }else{
            $(".datofactura").css("display","none");
        }
        if(check){
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="pagar"]').val('S');
            $(".caja").css("display","");
            $(".descuento").css("display","none");
            $(".descuentopersonal").css('display','none');
            $("#descuentopersonal").val('N');
        }else{
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="pagar"]').val('N');
            $(".caja").css("display","none");
            $(".descuento").css("display","");
        }
        validarFormaPago($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="formapago"]').val());
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="comprobante"]').val('N');
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="pagar"]').val('N');
        $(".datocaja").css("display","none");
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="pago"]').attr("checked",true);
        validarFormaPago($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="formapago"]').val());
    }
}

function boletearTodoCaja(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="boletear"]').val('S');
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="boletear"]').val('N');
    }
}

function editarPrecio(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="editarprecio"]').val('S');
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="editarprecio"]').val('N');
    }
    for(c=0; c < carro.length; c++){
        if(check) {
            //$("#txtPrecio"+carro[c]).removeAttr("readonly");
            $("#txtPrecioHospital"+carro[c]).removeAttr("readonly");
            $("#txtPrecioMedico"+carro[c]).removeAttr("readonly");
        }else{
            //$("#txtPrecio"+carro[c]).attr("readonly","true");
            $("#txtPrecioHospital"+carro[c]).attr("readonly","true");
            $("#txtPrecioMedico"+carro[c]).attr("readonly","true");
        }
    }
}

function generarNumero(){
    $.ajax({
        type: "POST",
        url: "ticket/generarNumero",
        data: "tipodocumento="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="tipodocumento"]').val()+"&serie="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="serieventa"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="numeroventa"]').val(a);
            if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="tipodocumento"]').val()=="Factura"){
                $(".datofactura").css("display","");
            }else{
                $(".datofactura").css("display","none");
            }
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

function editarDescuentoPersonal(check){
    if(check){
        $(".descuentopersonal").css('display','');
        $("#descuentopersonal").val('S');
    }else{
        $(".descuentopersonal").css('display','none');
        $("#descuentopersonal").val('N');
    }
}

function movimientoRef(check){
    if(check){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimientoref"]').val('S');
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento"]').css("display","");
        $('#tbDoc').css("display","");
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento_id"]').val(0);
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimientoref"]').val('N');
        $('#tbDoc').css("display","none");
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento"]').css("display","none");
    }
}

    var numeroref = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit:10,
        remote: {
            url: 'ventaadmision/ventaautocompletar/%QUERY',
            filter: function (docs) {
                return $.map(docs, function (movie) {
                    return {
                        value: movie.value2,
                        id: movie.id,
                        paciente: movie.paciente,
                        person_id:movie.person_id,
                        num:movie.value,
                        total:movie.total,
                    };
                });
            }
        }
    });
    numeroref.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento"]').typeahead(null,{
        displayKey: 'value',
        source: numeroref.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento_id"]').val(datum.id);
        //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento"]').val(datum.value);
        $("#tbDoc").append("<tr id='trDoc"+datum.id+"'><td align='left'>"+datum.num+"</td><td id='tdTotalDoc"+datum.id+"' align='center'>"+datum.total+"<td><td><a href='#' onclick=\"quitarDoc('"+datum.id+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
        carroDoc.push(datum.id);
        calcularTotalDoc();
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento"]').val('');
    });

function quitarDoc(id){
    $("#trDoc"+id).remove();
    for(c=0; c < carroDoc.length; c++){
        if(carroDoc[c] == id) {
            carroDoc.splice(c,1);
        }
    }
    calcularTotalDoc();
}

function calcularTotalDoc(){
    var total2=0;
    for(c=0; c < carroDoc.length; c++){
        var tot=parseFloat($("#tdTotalDoc"+carroDoc[c]).html());
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#totalDoc").html(total2);
}

</script>