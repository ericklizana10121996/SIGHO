<style>
	.no-resize{
		resize: none;
	}
</style>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($compra, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('detalle', 'false', array( 'id' => 'detalle')) !!}
	{!! Form::hidden('listProducto', 'false', array( 'id' => 'listProducto')) !!}
	<div class="col-lg-4 col-md-4 col-sm-4">
		<div class="form-group" style="height: 12px;">
			{!! Form::label('tipodocumento_id', 'Documento:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::select('tipodocumento_id', $cboDocumento, null, array('style' => 'background-color: rgb(25,241,227);' ,'class' => 'form-control input-xs', 'id' => 'tipodocumento_id', 'onchange' => 'generarNumero(this.value);')) !!}
			</div>
		</div>
		<div class="form-group" style="height: 12px;">
			{!! Form::label('credito', 'Credito:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::select('credito', $cboCredito, null, array('style' => 'background-color: rgb(25,241,227);' ,'class' => 'form-control input-xs', 'id' => 'credito', 'onchange' => 'cambiar();')) !!}
			</div>
		</div>
		<div class="form-group" style="height: 12px;">
		
		{!! Form::label('numerodias', 'Nro Dias:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-2 col-md-2 col-sm-2">
				{!! Form::text('numerodias', null, array('style' => 'background-color: rgb(252,215,147);','class' => 'form-control input-xs', 'id' => 'numerodias')) !!}
				
			</div>

		</div>
		<div class="form-group" style="height: 12px;">
			{!! Form::label('afecto', 'Afecto:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::select('afecto', $cboAfecto, null, array('class' => 'form-control input-xs', 'id' => 'afecto')) !!}
			</div>
		</div>
		
		<div class="form-group" style="height: 12px;">
			{!! Form::label('numerodocumento', 'Nro Doc:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-3 col-md-3 col-sm-3">
				{!! Form::text('serie', null, array('class' => 'form-control input-xs', 'id' => 'serie', 'placeholder' => 'serie')) !!}
			</div>
			<div class="col-lg-4 col-md-4 col-sm-4">
				{!! Form::text('numerodocumento', null, array('class' => 'form-control input-xs', 'id' => 'numerodocumento', 'placeholder' => 'numerodocumento')) !!}
			</div>

		</div>
		<div class="form-group" style="height: 12px;">
			{!! Form::label('ruc2', 'RUC:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('ruc2', null, array('style' => 'background-color: rgb(252,215,147);','class' => 'form-control input-xs', 'id' => 'ruc2')) !!}
				
			</div>
		</div>
		<div class="form-group" style="height: 12px;">
			{!! Form::label('nombrepersona', 'Proveedor:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('person_id', null, array('id' => 'person_id')) !!}
			<div class="col-lg-6 col-md-6 col-sm-6">
				{!! Form::text('nombrepersona', null, array('style' => 'background-color: rgb(252,215,147);','class' => 'form-control input-xs', 'id' => 'nombrepersona', 'placeholder' => 'Seleccione persona','disabled' => true)) !!}
				
			</div>
			<div class="col-lg-0 col-md-0 col-sm-0">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('proveedor.crearsimple', array('listar'=>'SI','modo'=>'popup')).'\', \'Nuevo Proveedor\', this);', 'title' => 'Nuevo Proveedor')) !!}
    		</div>
		</div>
		<div class="form-group" id="divDescuentokayros" style="height: 12px;">
			{!! Form::label('fecha', 'Fecha de Doc.:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-3 col-md-3 col-sm-3">
				<div class='input-group input-group-xs' id='divfecha'>
					{!! Form::text('fecha', date('d/m/Y'), array('class' => 'form-control input-xs', 'id' => 'fecha', 'placeholder' => 'Ingrese fecha')) !!}
					
				</div>
			</div>
		</div>
		<div class="form-group" id="divDescuentokayros" style="height: 12px;">
			{!! Form::label('fecha2', 'Fecha Venc.:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-3 col-md-3 col-sm-3">
				<div class='input-group input-group-xs' id='divfecha2'>
					{!! Form::text('fecha2', null, array('class' => 'form-control input-xs', 'id' => 'fecha2', 'placeholder' => 'Ingrese fecha')) !!}
					
				</div>
			</div>
		</div>
		<div class="form-group" style="height: 12px;">
			{!! Form::label('cajafarmacialabel', 'Caja farmacia:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::select('cajafamarcia', $cboCajafarmacia, null, array('style' => 'background-color: rgb(25,241,227);' ,'class' => 'form-control input-xs', 'id' => 'cajafarmacia', 'onchange' => 'cambiar2();')) !!}
			</div>
		</div>
		
		<div class="form-group" style="height: 12px;">
		
		{!! Form::label('nombredoctor', 'Medico:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('doctor_id', null, array('id' => 'doctor_id')) !!}
			<div class="col-lg-7 col-md-7 col-sm-7">
				{!! Form::text('nombredoctor', null, array('style' => 'background-color: rgb(252,215,147);','class' => 'form-control input-xs', 'id' => 'nombredoctor', 'placeholder' => 'Seleccione persona' , 'readonly' =>'')) !!}
				
			</div>
		</div>
		<div class="form-group" style="height: 12px;">
			{!! Form::label('total', 'Total:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			{!! Form::hidden('doctor_id', null, array('id' => 'doctor_id')) !!}
			<div class="col-lg-3 col-md-3 col-sm-3">
				{!! Form::text('total', null, array('style' => 'background-color: rgb(252,215,147);','class' => 'form-control input-xs', 'id' => 'total' )) !!}
			</div>
			{!! Form::label('igv', 'Igv:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
			<div class="col-lg-3 col-md-3 col-sm-3">
				{!! Form::text('igv', '', array('style' => 'background-color: rgb(252,215,147);','class' => 'form-control input-xs', 'id' => 'igv' )) !!}
			</div>
		</div>
	</div>
	<div class="col-lg-8 col-md-8 col-sm-8">
		<div class="form-group" style="height: 12px;">
			{!! Form::label('nombreproducto', 'Producto:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-5 col-md-5 col-sm-5">
				{!! Form::text('nombreproducto', null, array('class' => 'form-control input-xs', 'id' => 'nombreproducto', 'placeholder' => 'Ingrese nombre','onkeypress' => '')) !!}
			</div>
			<div class="col-lg-0 col-md-0 col-sm-0">
                {!! Form::button('<i class="glyphicon glyphicon-plus"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('producto.create', array('listar'=>'SI','modo'=>'popup')).'\', \'Nuevo Proveedor\', this);', 'title' => 'Nuevo Proveedor')) !!}
    		</div>
			{!! Form::hidden('producto_id', null, array( 'id' => 'producto_id')) !!}
			{!! Form::hidden('preciokayros2', null, array( 'id' => 'preciokayros2')) !!}

			{!! Form::hidden('precioventa', null, array('id' => 'precioventa')) !!}
			{!! Form::hidden('stock', null, array('id' => 'stock')) !!}
		</div>

		<div class="form-group" id="divProductos" style="overflow:auto; height:180px; padding-right:10px; border:1px outset">
			
		</div>

		<div class="form-group">
			<table>
			<tr>
				<td><b>P.Kayros</b></td>
				<td>&nbsp</td>
				<td>{!! Form::text('preciokayros', null, array('class' => 'form-control input-xs', 'id' => 'preciokayros', 'size' => '3')) !!}</td>
				<td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td>
				<td><b>P. Compra</b></td>
				<td>&nbsp</td>
				<td>{!! Form::text('preciocompra', null, array('class' => 'form-control input-xs', 'id' => 'preciocompra','size' => '3')) !!}</td>
				<td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td>
				<td><b>P.Venta</b></td>
				<td>&nbsp</td>
				<td>{!! Form::text('precioventap', null, array('class' => 'form-control input-xs', 'id' => 'precioventap', 'size' => '3')) !!}</td>
				<td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td>
				<td><b>Cantidad</b></td>
				<td>&nbsp</td>
				<td>{!! Form::text('cantidad', null, array('class' => 'form-control input-xs', 'id' => 'cantidad', 'size' => '3')) !!}</td>
				<td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td>
				<td><b>F.Vencimiento</b></td>
				<td>&nbsp</td>
				<td>{!! Form::text('fechavencimiento', null, array('class' => 'form-control input-xs', 'id' => 'fechavencimiento', 'size' => '6')) !!}</td>
				<td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td>
				<td><b>Lote</b></td>
				<td>&nbsp</td>
				<td>{!! Form::text('lote', null, array('class' => 'form-control input-xs', 'id' => 'lote', 'size' => '6')) !!}</td>
			</tr>
				
			</table>
			
		</div>

		<div class="form-group">
			<div class="col-lg-12 col-md-12 col-sm-12 text-right">
				{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardarVenta(\''.$entidad.'\', this)')) !!}
				{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
			</div>
		</div>
		
	</div>
	<div class="form-group" style="display: none;">
		<div class="col-lg-12 col-md-12 col-sm-12" >
			{!! Form::label('codigo', 'Comprobar Productos:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
			<div class="col-lg-5 col-md-5 col-sm-5">
				{!! Form::text('codigo', null, array('class' => 'form-control input-xs', 'id' => 'codigo', 'placeholder' => 'Ingrese codigo')) !!}
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12">
			<div id="divDetail" class="table-responsive" style="overflow:auto; height:220px; padding-right:10px; border:1px outset">
		        <table style="width: 100%;" class="table-condensed table-striped" id="tbDetalle">
		            <thead>
		                <tr>
		                	<th bgcolor="#E0ECF8" class='text-center'>N°</th>
		                    <th bgcolor="#E0ECF8" class='text-center'>Producto</th>
		                    <th bgcolor="#E0ECF8" class='text-left'>Concentración</th>
		                    <th bgcolor="#E0ECF8" class='text-left'>Forma</th>
		                    <th bgcolor="#E0ECF8" class='text-left'>Cond. Almac.</th>
		                    <th bgcolor="#E0ECF8" class='text-left'>Reg. Sanit.</th>
		                    
		                    <th bgcolor="#E0ECF8" class='text-center'>Fecha Venc.</th>
		                    <th bgcolor="#E0ECF8" class='text-center'>Lote</th>
		                    <th bgcolor="#E0ECF8" class='text-center'>Cantidad</th>
		                    <th bgcolor="#E0ECF8" class="text-center">Precio</th>
		                    <th bgcolor="#E0ECF8" class="text-center">Subtotal</th>
		                    <th bgcolor="#E0ECF8" class='text-center'>Quitar</th>                            
		                </tr>
		            </thead>
		           
		        </table>
		    </div>
		</div>
	 </div>
    <br>
	
	
{!! Form::close() !!}
<style type="text/css">
tr.resaltar {
    background-color: #A9F5F2;
    cursor: pointer;
}
</style>
<script type="text/javascript">
var valorbusqueda="";
var indice = -1;
var anterior = -1;
$(document).ready(function() {
	configurarAnchoModal('1300');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');

	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="igv"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="precioventap"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 2 });
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="preciocompra"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3, digits: 3 });
		
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fecha"]').inputmask("dd/mm/yyyy");
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fecha"]').datetimepicker({
			pickTime: false,
			language: 'es'
		});
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fecha2"]').inputmask("dd/mm/yyyy");
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fecha2"]').datetimepicker({
			pickTime: false,
			language: 'es'
		});

	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fechavencimiento"]').inputmask("dd/mm/yyyy");
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="fechavencimiento"]').datetimepicker({
			pickTime: false,
			language: 'es'
		});

	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="codigo"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				comprobarproducto ();
			}
		});

	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="conveniofarmacia"]').focus(function(){
			abrirconvenios();
		});

	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="afecto"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});

	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="numerodias"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});

	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="serie"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="numerodocumento"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="preciokayros"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="preciocompra"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="precioventap"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="cantidad"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="fechavencimiento"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();
			}
		});
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="lote"]').keydown( function(e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if(key == 13) {
				/*e.preventDefault();
				var inputs = $(this).closest('form').find(':input:visible:not([disabled]):not([readonly])');
				inputs.eq( inputs.index(this)+ 1 ).focus();*/
				addpurchasecart();
				$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="nombreproducto"]').val('');
				$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="cantidad"]').val('');
				$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="preciocompra"]').val('');
				$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="precioventap"]').val('');
				$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="preciokayros"]').val('');
				$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="fechavencimiento"]').val('');
				$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="lote"]').val('');
				indice = -1;
				$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="nombreproducto"]').focus();
			}
		});


	var personas = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			limit:10,
			remote: {
				url: 'person/providersautocompleting/%QUERY',
				filter: function (personas) {
					return $.map(personas, function (movie) {
						return {
							value: movie.value,
							id: movie.id,
							ruc: movie.ruc
						};
					});
				}
			}
		});
		personas.initialize();
		$('#nombrepersona').typeahead(null,{
			displayKey: 'value',
			limit:10,
			source: personas.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#person_id').val(datum.id);
			$('#ruc2').val(datum.ruc);
			$('#cajafarmacia').focus();
		});

	var personas2 = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			limit:10,
			remote: {
				url: 'person/providersautocompleting/%QUERY',
				filter: function (personas) {
					return $.map(personas, function (movie) {
						return {
							value: movie.value,
							id: movie.id,
							ruc: movie.ruc
						};
					});
				}
			}
		});
		personas2.initialize();
		$('#ruc2').typeahead(null,{
			displayKey: 'ruc',
			limit:10,
			source: personas2.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#person_id').val(datum.id);
			$('#nombrepersona').val(datum.value);
			$('#cajafarmacia').focus();
		});

	var doctores = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'person/doctorautocompleting/%QUERY',
				filter: function (doctores) {
					return $.map(doctores, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		doctores.initialize();
		$('#nombredoctor').typeahead(null,{
			displayKey: 'value',
			source: doctores.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#doctor_id').val(datum.id);
		});


	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombreproducto"]').on( 'keydown', function () {
        var e = window.event; 
        var keyc = e.keyCode || e.which;
        //console.log(this.value);
        //console.log(valorbusqueda);
        if(this.value.length>2 && keyc == 13 && valorbusqueda!=this.value){
            buscarProducto(this.value);
            valorbusqueda=this.value;
            this.focus();
            return false;
        }
        if(keyc == 38 || keyc == 40 || keyc == 13 || keyc == 27) {
            var tabladiv='tablaProducto';
			var child = document.getElementById(tabladiv).rows;
			//var indice = -1;
			var i=0;
            /*$('#tablaProducto tr').each(function(index, elemento) {
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
    		});*/		 
			// return
			//if(keyc == 13) { // enter
			if(keyc == 27) { // esc  				
			     if(indice != -1){
					var seleccionado = '';			 
					if(child[indice].id) {
					   seleccionado = child[indice].id;
					} else {
					   seleccionado = child[indice].id;
					}		 		
					seleccionarProducto(seleccionado);
				}
			} else {
				// abajo
				if(keyc == 40) {
					if(indice == (child.length - 1) ) {
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

				if (indice != -1) {
					var element = '#'+child[indice].id;
					$(element).addClass("resaltar");
					if (anterior  != -1) {
						element = '#'+anterior;
						$(element).removeClass("resaltar");
					}
					anterior = child[indice].id;
				}
			}
        }
    });


	//cambiotipoventa();
	$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="nombreproducto"]').focus();

}); 

var valorinicial="";
function buscarProducto(valor){
    if(valorinicial!=valor){valorinicial=valor;
        $.ajax({
            type: "POST",
            url: "venta/buscandoproducto",
            data: "nombre="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombreproducto"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                datos=JSON.parse(a);
                $("#divProductos").html("<table class='table-condensed table-hover' border='1' id='tablaProducto'><thead><tr><th class='text-center' style='width:220px;'><span style='display: block; font-size:.9em'>P. Activo</span></th><th class='text-center' style='width:220px;'><span style='display: block; font-size:.9em'>Nombre</span></th><th class='text-center' style='width:70px;'><span style='display: block; font-size:.9em'>Presentacion</span></th><th class='text-center' style='width:20px;'><span style='display: block; font-size:.9em'>Stock</span></th><th class='text-center' style='width:20px;'><span style='display: block; font-size:.9em'>P.Kayros</span></th><th class='text-center' style='width:20px;'><span style='display: block; font-size:.9em'>P.Venta</span></th></tr></thead></table>");
                var pag=parseInt($("#pag").val());
                var d=0;
                for(c=0; c < datos.length; c++){
                    var a="<tr id='"+datos[c].idproducto+"' onclick=\"seleccionarProducto('"+datos[c].idproducto+"')\"><td align='center'><span style='display: block; font-size:.7em'>"+datos[c].principio+"</span></td><td id='txtProducto"+datos[c].idproducto+"'><span style='display: block; font-size:.7em'>"+datos[c].nombre+"</span></td><td align='right'><span style='display: block; font-size:.7em'>"+datos[c].presentacion+"</span></td><td align='right'><span style='display: block; font-size:.7em'>"+datos[c].stock+"</span></td><td align='right'><span style='display: block; font-size:.7em'>"+datos[c].preciokayros+"</span></td><td align='right'><span style='display: block; font-size:.7em'>"+datos[c].precioventa+"</span><input type='hidden' id='camposAux"+datos[c].idproducto+"' value='"+datos[c].idConcentracion+"@"+datos[c].concentracion+"@"+datos[c].idForma+"@"+datos[c].formaFarmaceutica+"@"+datos[c].idCondicion+"@"+datos[c].condicionAlmacenamiento+"@"+datos[c].regSanitario+"'/></td></tr>";
                    $("#tablaProducto").append(a);           
                }
                $('#tablaProducto').DataTable({
                    "scrollY":        "250px",
                    "scrollCollapse": true,
                    "paging":         false,
                    "ordering"        :false
                });
                $('#tablaProducto_filter').css('display','none');
                $("#tablaProducto_info").css("display","none");
    	    }
        });
    }
}

function cambiar() {
	var credito = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="credito"]').val();
	if (credito == 'S') {
		$('#numerodias').focus();
		$("#numerodias").prop('readonly', false);
		/*$('#divcuota').show();
		$('#divnumerocuota').show();
		$('#divdias').show();
		$("#inicial").prop('readonly', false);*/
	}else{
		$("#numerodias").prop('readonly', true);
		$('#serie').focus();
	}
	
}

function cambiar2() {
	var cajafarmacia = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="cajafarmacia"]').val();
	if (cajafarmacia == 'S') {
		$('#nombredoctor').focus();
		$("#nombredoctor").prop('readonly', false);
	}else{
		$("#nombredoctor").prop('readonly', true);
		$('#nombreproducto').focus();
	}
	
}

function seleccionarProducto(idproducto){
	//alert(idproducto);
	var _token =$('input[name=_token]').val();
	$.post('{{ URL::route("venta.consultaproducto")}}', {idproducto: idproducto,_token: _token} , function(data){
		//$('#divDetail').html(data);
		//calculatetotal();
		var datos = data.split('@');
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="producto_id"]').val(datos[0]);
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="preciokayros"]').val(datos[1]);
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="precioventap"]').val(datos[2]);
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="precioventap"]').attr("data_valor",datos[2]);
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="stock"]').val(datos[3]);
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="preciocompra"]').val(datos[4]);
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="preciocompra"]').attr("data_valor",datos[4]);
	});
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="cantidad"]').focus();

}

function ventanaproductos() {
	var tipoventa = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipoventa"]').val();
	var descuentokayros = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descuentokayros"]').val();
	var copago = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="copago"]').val();
	modal('{{URL::route('venta.buscarproducto')}}'+'?tipoventa='+tipoventa+'&descuentokayros='+descuentokayros+'&copago='+copago, '');
}


function abrirconvenios() {
	modal('{{URL::route('venta.buscarconvenio')}}', '');
}

$('#ruc2').on('change',function(){
   var person_id = $('#person_id').val();
   // alert(person_id);
	
	if(person_id === ''){
		$('#nombrepersona').val('');
	}else{
		if($('#ruc2').val() === ''){
			$('#nombrepersona').val('');
		}
    }
});

function generarNumero(valor){
    $.ajax({
        type: "POST",
        url: "venta/generarNumero",
        data: "tipodocumento_id="+valor+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="numerodocumento"]').val(a);
        }
    });
    /*if (valor == 4) {
		modal('{{URL::route('venta.busquedaempresa')}}', '');
	}else{
		modal('{{URL::route('venta.busquedacliente')}}', '');
	} */   
}


function setValorFormapago (id, valor) {
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="' + id + '"]').val(valor);
}

function getValorFormapago (id) {
	var valor = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="' + id + '"]').val();
	return valor;
}

function cambiotipoventa() {
	var tipoventa = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipoventa"]').val();
	if (tipoventa == 'C') {
		//$('#divConvenio').show();
		//$('#divDescuentokayros').show();
		//$('#divCopago').show();
		modal('{{URL::route('venta.busquedacliente')}}', '');

	}else if (tipoventa == 'N') {
		$('#divConvenio').hide();
		//$('#divDescuentokayros').hide();
		//$('#divCopago').hide();
	}
}

function generarSaldototal () {
	var total = retornarFloat(getValorFormapago('total'));
	var inicial = retornarFloat(getValorFormapago('inicial'));
	var saldototal = (total - inicial).toFixed(2);
	if (saldototal < 0.00) {
		setValorFormapago('inicial', total);
		setValorFormapago('saldo', '0.00');
	}else{
		setValorFormapago('saldo', saldototal);
	}
}

function retornarFloat (value) {
	var retorno = 0.00;
	value       = value.replace(',','');
	if(value.trim() === ''){
		retorno = 0.00; 
	}else{
		retorno = parseFloat(value)
	}
	return retorno;
}

function quitar (valor) {
	var _token =$('input[name=_token]').val();
	$.post('{{ URL::route("compra.quitarcarritocompra")}}', {valor: valor,_token: _token} , function(data){
		$('#divDetail').html(data);
		calculatetotal();
		//generarSaldototal ();
		// var totalpedido = $('#totalpedido').val();
		// $('#total').val(totalpedido);
	});
}

function calculatetotal () {
	var _token =$('input[name=_token]').val();
	var valor =0;
	$.post('{{ URL::route("compra.calculartotal")}}', {valor: valor,_token: _token} , function(data){
		valor = retornarFloat(data);
		$("#total").val(valor);
		//generarSaldototal();
		// var totalpedido = $('#totalpedido').val();
		// $('#total').val(totalpedido);
	});
}

function comprobarproducto () {
	var _token =$('input[name=_token]').val();
	var valor =$('input[name=codigo]').val();
	$.post('{{ URL::route("venta.comprobarproducto")}}', {valor: valor,_token: _token} , function(data){
		
		if (data.trim() == 'NO') {
			$('input[name=codigo]').val('');
			bootbox.alert("Este Producto no esta en lista de venta");
            setTimeout(function () {
                $('#codigo').focus();
            },2000) 
		}else{
			$('input[name=codigo]').val('');
			$('#codigo').focus();
		}
	});
}

function seleccionarCliente(id) {
	var _token =$('input[name=_token]').val();
	$.post('{{ URL::route("venta.clienteid")}}', {id: id,_token: _token} , function(data){
		var datos = data.split('-'); 
		$('#person_id').val(datos[0]);
		$('#nombrepersona').val(datos[1]);
		
		cerrarModal();
		var tipoventa = $('#tipoventa').val();
		if (tipoventa == 'N') {
			$('#nombreproducto').focus();
		}else{
			//$('#conveniofarmacia').focus();
			abrirconvenios();
		}
	});
	
}

function seleccionarParticular(value) {
	$('#nombrepersona').val(value);
	cerrarModal();
	$('#nombreproducto').focus();
}

function agregarconvenio(id){

	var kayros = $('#txtKayros').val();
	var copago = $('#txtCopago').val();
	var convenio_id = id;

	var _token =$('input[name=_token]').val();
	if(kayros.trim() == '' ){
		bootbox.alert("Ingrese precio kayros");
            setTimeout(function () {
                $('#txtKayros').focus();
            },2000) 
	}else if(copago.trim() == '' ){
		bootbox.alert("Ingrese copago");
            setTimeout(function () {
                $('#txtCopago').focus();
            },2000) 
	}else{
		$.post('{{ URL::route("venta.agregarconvenio")}}', {kayros: kayros,copago: copago, convenio_id: convenio_id,_token: _token} , function(data){
			dat = data.split('-');
			$('#copago').val(copago);
			$('#descuentokayros').val(kayros);
			$('#conveniofarmacia').val(dat[0]);
			$('#nombreconvenio').val(dat[0]);
			$('#conveniofarmacia_id').val(dat[1]);

			cerrarModal();
			$('#descuentokayros').focus();
			/*$('#divDetail').html(data);
			calculatetotal();
			bootbox.alert("Producto Agregado");
            setTimeout(function () {
                $('#txtPrecio' + elemento).focus();
            },2000) */
			
		});
	}
}

function agregarempresa(id){

		var ruc = $('#ruc').val();
		var direccion = $('#direccion').val();
		var telefono = $('#telefono').val();
		var empresa_id = id;

		var _token =$('input[name=_token]').val();
	/*if(kayros.trim() == '' ){
		bootbox.alert("Ingrese precio kayros");
            setTimeout(function () {
                $('#txtKayros').focus();
            },2000) 
	}else if(copago.trim() == '' ){
		bootbox.alert("Ingrese copago");
            setTimeout(function () {
                $('#txtCopago').focus();
            },2000) 
	}else{*/
		$.post('{{ URL::route("venta.agregarempresa")}}', {ruc: ruc,direccion: direccion,telefono: telefono, empresa_id: empresa_id,_token: _token} , function(data){
			dat = data.split('-');
			$('#nombrepersona').val(dat[0]);
			$('#empresa_id').val(dat[1]);

			cerrarModal();
			$('#nombreproducto').focus();
			/*$('#divDetail').html(data);
			calculatetotal();
			bootbox.alert("Producto Agregado");
            setTimeout(function () {
                $('#txtPrecio' + elemento).focus();
            },2000) */
			
		});
	//}
	}

var carro = new Array();
function addpurchasecart(elemento){
	var cantidad = $('#cantidad').val();
	var precio = $('#preciocompra').val();
	//var precio = $('#preciocompra').attr("data_valor");
	var precioventa = $('#precioventap').val();
	//var precioventa = $('#precioventap').attr("data_valor");
	var preciokayros = $('#preciokayros').val();
	var product_id = $('#producto_id').val();
	var fechavencimiento = $('#fechavencimiento').val();
	var lote = $('#lote').val();
	var stock = $('#stock').val();
	var tipoventa = $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="tipoventa"]').val();
	var descuentokayros = $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="descuentokayros"]').val();
	var copago = $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="copago"]').val();

	var _token =$('input[name=_token]').val();
	if(cantidad.trim() == '' ){
		bootbox.alert("Ingrese Cantidad");
            setTimeout(function () {
                $('#cantidad').focus();
            },2000) 
	}else if(cantidad.trim() == 0){
		bootbox.alert("la cantidad debe ser mayor a 0");
            setTimeout(function () {
                $('#cantidad').focus();
            },2000) 
	}else if(precio.trim() == '' ){
		bootbox.alert("Ingrese Precio");
            setTimeout(function () {
                $('#preciocompra').focus();
            },2000) 
	}/*else if(precio.trim() == 0){
		bootbox.alert("el precio debe ser mayor a 0");
            setTimeout(function () {
                $('#preciocompra').focus();
            },2000) 
	}*/else if(fechavencimiento.trim() == '' ){
		bootbox.alert("Ingrese Fecha Vencimiento");
            setTimeout(function () {
                $('#fechavencimiento').focus();
            },2000) 
	}else if(precio.trim() == '' ){
		bootbox.alert("Ingrese Nombre lote");
            setTimeout(function () {
                $('#lote').focus();
            },2000) 
	}else if(product_id=="" || product_id=="0"){
		bootbox.alert("Debe seleccionar un producto");
            setTimeout(function () {
                $('#nombreproducto').focus();
            },2000) 
	}else{
		var band=true;
	    for(c=0; c < carro.length; c++){
	        if(carro[c]==product_id){
	            band=false;
	        }      
	    }
	    if(band){
			var subtotal = Math.round((cantidad*precio)*100)/100;
			var auxiliar = $('#camposAux'+product_id).val();
			var str = auxiliar.split('@');
			// datos[c].idConcentracion+"@"+datos[c].concentracion+"@"+datos[c].idForma+"@"+datos[c].formaFarmaceutica+"@"+datos[c].idCondicion+"@"+datos[c].condicionAlmacenamiento


			$("#tbDetalle").append("<tr id='tr"+product_id+"'>"+
				"<td align='left'>"+(carro.length + 1 ) +"</td>"+
				"<td align='left'><input type='hidden' id='txtPrecioKayros"+product_id+"' name='txtPrecioKayros"+product_id+"' value='"+preciokayros+"' /><input type='hidden' id='txtPrecioVenta"+product_id+"' name='txtPrecioVenta"+product_id+"' value='"+precioventa+"' /><input type='hidden' id='txtTipoVenta"+product_id+"' name='txtTipoVenta"+product_id+"' value='"+tipoventa+"' />"+$('#txtProducto'+product_id).html()+"</td>"+

				"<td align='left'><input id='hdConcentracion"+product_id+"' name='hdConcentracion"+product_id+"' value='"+str[0]+"' type='hidden' /><input type='text' class='form-control input-xs' name='txtConcentracion"+product_id+"' id='txtConcentracion"+product_id+"' value='"+str[1]+"'/>"+
				"<td align='left'><input id='hdForma"+product_id+"' name='hdForma"+product_id+"' type='hidden' value='"+str[2]+"' /><input type='text' class='form-control input-xs' value='"+str[3]+"' id='txtForma"+product_id+"' name='txtForma"+product_id+"'/></td>"+
				"<td align='left'><input id='hdCondicionAlm"+product_id+"' value='"+str[4]+"' name='hdCondicionAlm"+product_id+"' type='hidden' /><input type='text' value='"+ str[5]+"' class='form-control input-xs' id='txtCondicionAlm"+product_id+"' name='txtCondicionAlm"+product_id+"'/></td>"+
				"<td align='center' style='background-color:#FDD9E8;'><input style='background-color:#FDD9E8;' type='text' size='5' class='form-control input-xs' id='txtRegSanitario"+product_id+"' name='txtRegSanitario"+product_id+"' value='"+str[6]+"'/></td>"+
				"<td align='center'><input type='text' size='5' class='form-control input-xs'  id='txtFechaVencimiento"+product_id+"' style='width: 70px;' name='txtFechaVencimiento"+product_id+"' value='"+fechavencimiento+"' readonly='' /></td>"+
				"<td align='center'><input type='text' size='5' class='form-control input-xs'  id='txtLote"+product_id+"' style='width: 60px;' name='txtLote"+product_id+"' value='"+lote+"' readonly='' /></td>"+
				"<td align='center'><input type='hidden' id='txtIdProducto"+product_id+"' name='txtIdProducto"+product_id+"' value='"+product_id+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+product_id+"' name='txtCantidad"+product_id+"' value='"+cantidad+"' size='3' onkeyup=\"if(event.keyCode==13){calcularTotalItem('"+product_id+"')}\" onblur=\"calcularTotalItem("+product_id+")\" /></td>"+
	            "<td align='center'><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+product_id+"' style='width: 60px;' name='txtPrecio"+product_id+"' value='"+precio+"' readonly='' onblur=\"calcularTotalItem("+product_id+")\" /></td>"+
	            "<td align='center'><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+product_id+"' style='width: 60px;' id='txtTotal"+product_id+"' value='"+subtotal+"' /></td>"+
	            "<td align='center'><a href='#' onclick=\"quitar2('"+product_id+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
	        carro.push(product_id);
	        $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 3 });


	        eval("var formas"+product_id+" = new Bloodhound({"+
	                "datumTokenizer: function (d) {"+
	                    "return Bloodhound.tokenizers.whitespace(d.value);"+
	                "},"+
	                "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
	                "remote: {"+
	                    "url: 'forma/autocompletarforma/%QUERY',"+
	                    "filter: function (formas"+product_id+") {"+
	                        "return $.map(formas"+product_id+", function (movie) {"+
	                            "return {"+
	                                "value: movie.value,"+
	                                "id: movie.id"+
	                            "};"+
	                        "});"+
	                    "}"+
	                "}"+
	            "});"+
	            "formas"+product_id+".initialize();"+
	            "$('#txtForma"+product_id+"').typeahead(null,{"+
	                "displayKey: 'value',"+
	                "source: formas"+product_id+".ttAdapter()"+
	            "}).on('typeahead:selected', function (object, datum) {"+
	                "$('#hdForma"+product_id+"').val(datum.id);"+
	            "});");


	        eval("var concentracion"+product_id+" = new Bloodhound({"+
	                "datumTokenizer: function (d) {"+
	                    "return Bloodhound.tokenizers.whitespace(d.value);"+
	                "},"+
	                "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
	                "remote: {"+
	                    "url: 'concentracion/autocompletarconcentracion/%QUERY',"+
	                    "filter: function (concentracion"+product_id+") {"+
	                        "return $.map(concentracion"+product_id+", function (movie) {"+
	                            "return {"+
	                                "value: movie.value,"+
	                                "id: movie.id"+
	                            "};"+
	                        "});"+
	                    "}"+
	                "}"+
	            "});"+
	            "concentracion"+product_id+".initialize();"+
	            "$('#txtConcentracion"+product_id+"').typeahead(null,{"+
	                "displayKey: 'value',"+
	                "source: concentracion"+product_id+".ttAdapter()"+
	            "}).on('typeahead:selected', function (object, datum) {"+
	                "$('#hdConcentracion"+product_id+"').val(datum.id);"+
	            "});");

	        eval("var condicionAlmacenamiento"+product_id+" = new Bloodhound({"+
	                "datumTokenizer: function (d) {"+
	                    "return Bloodhound.tokenizers.whitespace(d.value);"+
	                "},"+
	                "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
	                "remote: {"+
	                    "url: 'condicionAlmacenamiento/autocompletarcondicion/%QUERY',"+
	                    "filter: function (condicionAlmacenamiento"+product_id+") {"+
	                        "return $.map(condicionAlmacenamiento"+product_id+", function (movie) {"+
	                            "return {"+
	                                "value: movie.value,"+
	                                "id: movie.id"+
	                            "};"+
	                        "});"+
	                    "}"+
	                "}"+
	            "});"+
	            "condicionAlmacenamiento"+product_id+".initialize();"+
	            "$('#txtCondicionAlm"+product_id+"').typeahead(null,{"+
	                "displayKey: 'value',"+
	                "source: condicionAlmacenamiento"+product_id+".ttAdapter()"+
	            "}).on('typeahead:selected', function (object, datum) {"+
	                "$('#hdCondicionAlm"+product_id+"').val(datum.id);"+
	            "});");




	    }else{
	    	alert("Producto ya Agregado");
	    }
	    $('#detalle').val(true);
		calcularTotal();
	}
}

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
                //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(dat[0].id);
                //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').val(dat[0].historia);
                //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombrepersona"]').val(dat[0].paciente);
                $('#person_id').val(dat[0].person_id);
				$('#nombrepersona').val(dat[0].paciente);
				cerrarModal();
                var tipoventa = $('#tipoventa').val();
				if (tipoventa == 'N') {
					$('#nombreproducto').focus();
				}else{
					//$('#conveniofarmacia').focus();
					abrirconvenios();
				}
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

function guardarProveedor (entidad, idboton) {
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
				//cerrarModal();
                $('#person_id').val(dat[0].id);
				$('#nombrepersona').val(dat[0].nombre);
				cerrarModal();
                
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

function guardarVenta (entidad, idboton, entidad2) {
	$("#listProducto").val(carro);
	if($(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="fecha2"]').val()==""){
		alert("Debe ingresar una fecha de vencimiento");
		$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="fecha2"]').focus();
		return false;
	}
	if($(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="igv"]').val()==""){
		alert("Debe ingresar igv");
		$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="igv"]').focus();
		return false;
	}else{
		var total = $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').val();
		var igv = $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="igv"]').val();
		if(parseFloat(total)<parseFloat(igv)){
			alert('Igv debe ser menor que total');
			return false;
		}
		var mensaje = '<h3 align = "center">Total = '+total+'</h3>';
		/*if (typeof mensajepersonalizado != 'undefined' && mensajepersonalizado !== '') {
			mensaje = mensajepersonalizado;
		}*/
		bootbox.confirm({
			message : mensaje,
			buttons: {
				'cancel': {
					label: 'Cancelar',
					className: 'btn btn-default btn-sm'
				},
				'confirm':{
					label: 'Aceptar',
					className: 'btn btn-success btn-sm'
				}
			}, 
			callback: function(result) {
				if (result) {
					var idformulario = IDFORMMANTENIMIENTO + entidad;
					var data         = submitForm(idformulario);
					var respuesta    = '';
					var listar       = 'NO';
					
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
							var dat = JSON.parse(respuesta);
				            if(dat[0]!==undefined){
				                resp=dat[0].respuesta;    
				            }else{
				                resp='VALIDACION';
				            }
				            
							if (resp === 'OK') {
								cerrarModal();
				                buscarCompaginado('', 'Accion realizada correctamente', entidad, 'OK');
				                /*if(dat[0].pagohospital!="0"){
				                    window.open('/juanpablo/ticket/pdfComprobante?ticket_id='+dat[0].ticket_id,'_blank')
				                }else{
				                    window.open('/juanpablo/ticket/pdfPrefactura?ticket_id='+dat[0].ticket_id,'_blank')
				                }*/
				                //alert('hola');
				                /*if (dat[0].ind == 1) {
				                	window.open('/juanpablo/venta/pdfComprobante?venta_id='+dat[0].venta_id,'_blank');
				                	window.open('/juanpablo/venta/pdfComprobante?venta_id='+dat[0].second_id,'_blank');
				                }else{
				                	window.open('/juanpablo/venta/pdfComprobante?venta_id='+dat[0].venta_id,'_blank');
				                }*/
				                
							} else if(resp === 'ERROR') {
								alert(dat[0].msg);
							} else {
								mostrarErrores(respuesta, idformulario, entidad);
							}
						}
					});
				};
			}            
		}).find("div.modal-content").addClass("bootboxConfirmWidth");
		setTimeout(function () {
			if (contadorModal !== 0) {
				$('.modal' + (contadorModal-1)).css('pointer-events','auto');
				$('body').addClass('modal-open');
			}
		},2000);

	}
	
}

function quitar2 (id) {
	$("#tr"+id).remove();
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
    calcularTotal();
}

function calcularTotal () {
	var total2=0;
    for(c=0; c < carro.length; c++){
        var tot=Math.round(parseFloat($("#txtTotal"+carro[c]).val())*100)/100;
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#total").val(total2);   
}

function calcularTotalItem(id){
	var cant=parseFloat($("#txtCantidad"+id).val());
    var pv=parseFloat($("#txtPrecio"+id).val());
    var subtotal = Math.round((cant*pv)*100)/100;
    $("#txtTotal"+id).val(subtotal);
    calcularTotal();
}

</script>