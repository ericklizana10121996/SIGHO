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
								{!! Form::date('fechainicial', date('Y-m-d',strtotime('-365 day')), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('origen', 'Origen:') !!}
								<select name="origen" id="origen" class="form-control input-xs">
									{{-- <option value="">Todos</option> --}}
									@foreach($cboOrigen as $key => $value)
										<option value="{{$value->id}}">{{$value->nombre}}</option>
									@endforeach	
								</select>
							</div>
                     		
                     	{{-- 	<div class="form-group">
								{!! Form::label('convenio', 'Convenio:') !!}
								<select name="convenio" id="convenio" class="form-control input-xs">
									@foreach($cboConvenios as $key => $value)
										<option value="{{$value->id}}">{{$value->nombre}}</option>
									@endforeach	
								</select>
							</div> --}}
                     		
                     		<div class="form-group">
								{!! Form::label('tipobusqueda', 'Búsqueda:')!!}

								{!! Form::select('tipobusqueda', $cboTipoBusqueda,null, array('class' => 'form-control input-xs')) !!}
							</div>
							
							<div class="form-group">
								{!! Form::label('top', 'Top 20 /Top 50:') !!}
								{!! Form::hidden('top', '20', array('id' => 'top')) !!}
								<input type="checkbox" checked="" style="margin-top: 1px;" name="check" id="check" onclick="if(this.checked){$('#top').val('20');}else{$('#top').val('');}">
							</div>

							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel General', array('class' => 'btn btn-warning btn-xs', 'id' => 'btnExcel','onclick' => 'excel();')) !!}

							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel Dr. Tello', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnExcel03','onclick' => 'excel03();')) !!}

							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel Por Principio Dr. Tello', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel04','onclick' => 'excel04();')) !!}

							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel Analisis por Principio Activo', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnExcel05','onclick' => 'excel05();')) !!}


							{{-- {!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel Por Convenio', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnExcel','onclick' => 'excel02();')) !!} --}}
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
		//buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="persona"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				//buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="concepto"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				//buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="area"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				//buscar('{{ $entidad }}');
			}
		});
	});
    function excel(entidad){
	    window.open("reportefarmacia/excel?fi="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&ff="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&tipo="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipobusqueda"]').val()+"&top="+$('#top').val()+"&origen="+$("#origen").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}

    function excel02(entidad){
	    window.open("reportefarmacia/excel02?fi="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&ff="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&tipo="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipobusqueda"]').val()+"&top="+$('#top').val()+"&origen="+$("#origen").val()+"&convenio="+$("#convenio").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}

   function excel03(entidad){
	    window.open("reportefarmacia/excel03?fi="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&ff="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&tipo="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipobusqueda"]').val()+"&top="+$('#top').val()+"&origen="+$("#origen").val()+"&convenio="+$("#convenio").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}

    function excel04(entidad){
	    window.open("reportefarmacia/excel04?fi="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&ff="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&tipo="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipobusqueda"]').val()+"&top="+$('#top').val()+"&origen="+$("#origen").val()+"&convenio="+$("#convenio").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}

	function excel05(entidad){
	    window.open("reportefarmacia/excel05?fi="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&ff="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&tipo="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipobusqueda"]').val()+"&top="+$('#top').val()+"&origen="+$("#origen").val()+"&convenio="+$("#convenio").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
/*	function excel2(entidad){
	    window.open("reporteegresotesoreria/excel2?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&caja_id="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="caja_id"]').val()+"&persona="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="persona"]').val()+"&area="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="area"]').val()+"&concepto="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="concepto"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function excelbonos(entidad){
	    window.open("reporteegresotesoreria/excelbonos?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&caja_id="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="caja_id"]').val()+"&persona="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="persona"]').val()+"&area="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="area"]').val()+"&concepto="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="concepto"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}*/
</script>