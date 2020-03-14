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
								{!! Form::label('fechainicial', 'Fecha Inicial:') !!}
								{!! Form::date('fechainicial', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>
							<!--div class="form-group">
								{!! Form::label('fechainicial2', 'Fecha Inicial (Cobranza):') !!}
								{!! Form::date('fechainicial2', "", array('class' => 'form-control input-xs', 'id' => 'fechainicial2')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal2', 'Fecha Final (Cobranza):') !!}
								{!! Form::date('fechafinal2', "", array('class' => 'form-control input-xs', 'id' => 'fechafinal2')) !!}
							</div-->
							<div class="form-group">
								{!! Form::label('empresa', 'Empresa:') !!}
								{!! Form::text('empresa', '', array('class' => 'form-control input-xs', 'id' => 'empresa')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('paciente', 'Paciente:') !!}
								{!! Form::text('paciente', '', array('class' => 'form-control input-xs', 'id' => 'paciente')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('numero', 'Nro:') !!}
								{!! Form::text('numero', '', array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('situacion', 'Situacion:')!!}
								{!! Form::select('situacion', $cboSituacion, 'P',array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('tiposervicio', 'Servicio:')!!}
								<select id="tiposervicio"  name="tiposervicio" class="form-control input-xs">
									 @foreach($cboServicio as $key => $value)
									 	<option value="{{$value->id}}"> {{$value->nombre }}</option>
									 @endforeach									
								</select>
							</div>


							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 10, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-usd"></i> Pagar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnPagar', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excel();')) !!}

							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel Por Servicio', array('class' => 'btn btn-primary btn-xs', 'id' => 'btnExcel02','onclick' => 'excel02();')) !!}

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
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="paciente"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="empresa"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="numero"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});

	});
	function excel(entidad){
	    window.open("cobranza/excel?"+$(IDFORMBUSQUEDA + '{{ $entidad }}').serialize(),"_blank");
	}

	function excel02(entidad){
	    window.open("cobranza/excel02?"+$(IDFORMBUSQUEDA + '{{ $entidad }}').serialize(),"_blank");
	}
</script>