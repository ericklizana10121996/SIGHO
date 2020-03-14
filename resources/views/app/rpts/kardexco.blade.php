<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $title }}
		{{-- <small>Descripción</small> --}}
	</h1>
</section>

<style type="text/css">
	.resul{
		border: solid gray 1px;
	}
	.resul:hover{
		background: rgb(102,175,233);
	}
</style>

<!-- Main content -->
<section class="content">
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header">
					<div class="row">
						<div class="col-xs-12">
							{!! Form::open(['method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}

							<div class="form-group">
								{!! Form::label('fechainicial', 'Fecha Inicial:') !!}
								{!! Form::date('fechainicial', date('Y-m-d',strtotime("now",strtotime("-365 day"))), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>

							<div class="form-group" id="meds">
								<input class="form-control" type="text" id="indicio" placeholder="Producto">
								<div class="form-group" id="rmeds" style="width: 100%"></div>
							</div>

							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'Generar2()')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Generar TODOS', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnBuscar2', 'onclick' => 'Generar3()')) !!}
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
	var medicinasel = 0;

	$('#indicio').keyup(function(){
		var indicio = $('#indicio').val();
		if (indicio.length >= 3) {
			$.ajax({
				type:'GET',
				url:"rpts/nmedicinas/"+indicio,
				data:'',
				success: function(a) {
					$('#rmeds').html(a);
				}
			});
		} else {
			$('#rmeds').html('');
		}
	});
	function selecciona(id){
		medicinasel = id;
		Generar2();
	}

	function Genera(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		//var mini = $('#medicinas').val();
		if (ff != "") {
			var link = 'reporte.php?rep=38&fi='+fi+'&ff='+ff+'&p_id='+medicinasel;
			window.open(link,'_blank');
		}
	}

	function Generar2(){
		window.open("ventaadmision/excelKardex?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&producto_id="+medicinasel+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}

	function Generar3(){
		window.open("ventaadmision/excelKardexTodos?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&producto_id="+medicinasel+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	
</script>