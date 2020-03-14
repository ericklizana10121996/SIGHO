<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $title }}

	</h1>
</section>

<!-- Main content -->
<section class="content">
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header">
					<div class="row">
						<div class="col-xs-12">
							{!! Form::open(['route' => $ruta["search"], 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
							<div class="form-group">
								{!! Form::label('cuenta', 'Cuenta:') !!}
								{!! Form::select('cuenta', $cboCuenta,'', array('class' => 'form-control input-xs', 'id' => 'cuenta', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('fechainicial', 'Fecha Inicial:') !!}
								{!! Form::date('fechainicial', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('numero', 'Nro.:') !!}
								{!! Form::text('numero', '', array('class' => 'form-control input-xs', 'id' => 'numero', 'size' => 10)) !!}
							</div>
							<div class="form-group">
								{!! Form::label('persona', 'Persona:') !!}
								{!! Form::text('persona', '', array('class' => 'form-control input-xs', 'id' => 'persona', 'size' => 20)) !!}
							</div>
							<div class="form-group">
								{!! Form::label('concepto', 'Concepto:') !!}
								{!! Form::text('concepto', '', array('class' => 'form-control input-xs', 'id' => 'concepto', 'size' => 20)) !!}
							</div>
							<div class="form-group">
								{!! Form::label('situacion', 'Situacion:') !!}
								{!! Form::select('situacion', $cboSituacion,'', array('class' => 'form-control input-xs', 'id' => 'situacion', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('formapago', 'Forma Pago:') !!}
								{!! Form::select('formapago', $cboFormaPago,'', array('class' => 'form-control input-xs', 'id' => 'formapago', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('tipodocumento_id', 'Tipo:') !!}
								{!! Form::select('tipodocumento_id', $cboTipoDocumento,'', array('class' => 'form-control input-xs', 'id' => 'tipodocumento_id', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Nuevo', array('class' => 'btn btn-info btn-xs', 'id' => 'btnNuevo', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-cog"></i> Cerrar', array('class' => 'btn btn-warning btn-xs', 'id' => 'btnCerrar','onclick' => 'resumen();')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-print"></i> Imprimir', array('class' => 'btn btn-info btn-xs', 'id' => 'btnCerrar','onclick' => 'pdf();')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-print"></i> Resumen Mes', array('class' => 'btn btn-info btn-xs', 'id' => 'btnCerrar','onclick' => 'pdfResumen();')) !!}
							{!! Form::close() !!}
						</div>
					</div>
				</div>
				<!-- /.box-header -->
				<div class="box-body" id="listado{{ $entidad }}">
				</div>
				<!-- /.box-body -->
			</div>
			<!-- /.box -->
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</section>
<!-- /.content -->	
<script>
	$(document).ready(function () {
		buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="numero"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="persona"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="concepto"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
	});
	function procesar(entidad){
		var btn = $(IDFORMBUSQUEDA + '{!! $entidad !!} :input[id="btnProcesar"]');
		btn.button('loading');
	    $.ajax({
	        type: "POST",
	        url: "ventaadmision/procesar",
	        data: "fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&tipodocumento="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipodocumento"]').val()+"&numero="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="numero"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),
	        success: function(a) {
	        	btn.button('reset');
	        	alert("Procesado correctamente");
	            buscar(entidad);
	        }
	    });
	}
	function imprimirRecibo(id){
        window.open("cuentabancaria/pdfRecibo?id="+id,"_blank");
    }
    function pdf(){
        window.open("cuentabancaria/pdfListar?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&situacion="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="situacion"]').val()+"&formapago="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="formapago"]').val()+"&tipodocumento_id="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipodocumento_id"]').val()+"&cuenta_id="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="cuenta"]').val()+"&persona="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="persona"]').val()+"&concepto="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="concepto"]').val(),"_blank");
    }
	function pdfResumen(){
        window.open("cuentabancaria/pdfListarResumen?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&cuenta_id="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="cuenta"]').val()+"&persona="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="persona"]').val(),"_blank");
    }
</script>