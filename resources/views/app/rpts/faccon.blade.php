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

							<div class="form-group" id="compa">
								<select id="tipo">
									<option value="1">Sin Notas de Crédito</option>
									<option value="2">Con Notas de Crédito</option>
									<option value="3">Solo Anulados</option>
									<option value="0">Todos</option>
								</select>
							</div>

							{{-- {!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'Genera()')) !!}
							{!! Form::close() !!} --}}

							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'Genera()')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar2', 'onclick' => 'Genera2()')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-save-file"></i> Word', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar3', 'onclick' => 'word()')) !!}
							{!! Form::close() !!}

						</div>
						<div class="col-xs-12" id="resultados"></div>
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

	function cargaCompa(){
		$.ajax({
			type:'GET',
			url:"rpts/compas",
			data:'',
			success: function(a) {
				$('#compa').html(a);
			}
		});
	}

	//cargaCompa();

	function Genera(){
		$('#resultados').html('Buscando....');
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		var tipo = $('#tipo').val();
		if (ff != "") {
			$.ajax({
				type:'GET',
				url:'reporte.php?rep=261&fi='+fi+'&ff='+ff+'&tipo='+tipo,
				data:'',
				success: function(response) {
					$('#resultados').html(response);
				}
			});
		}
	}

	function Genera2(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		var tipo = $('#tipo').val();
		if (ff != "") {
			var link = 'reporte.php?rep=26&fi='+fi+'&ff='+ff+'&tipo='+tipo;
			window.open(link,'_blank');
		}
	}

	function word(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		var tipo = $('#tipo').val();
		if (ff != "") {
			var link = 'facturacion/cartaGarantia?fechainicial='+fi+'&fechafinal='+ff+'&tipo='+tipo;
			window.open(link,'_blank');
		}
	}
	
</script>