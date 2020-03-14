<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $title }}
		{{-- <small>Descripción</small> --}}
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
							{!! Form::open(['method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
						    
							<div class="form-group">
								{!! Form::label('fechainicial', 'Fecha Inicial:') !!}
								{!! Form::date('fechainicial', date('Y-m-d',strtotime("now",strtotime("-7 day"))), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>

							<div class="form-group" id="meds"></div>
							<div class="form-group" id="salas"></div>

							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Reporte', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'Genera()')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar2', 'onclick' => 'Genera2()')) !!}
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
	function medicos(){
		$.ajax({
			type:'GET',
			url:"rpts/medicos",
			data:'',
			success: function(a) {
				$('#meds').html(a);
			}
		});
	}

	function salas(){
		$.ajax({
			type:'GET',
			url:"rpts/salas",
			data:'',
			success: function(a) {
				$('#salas').html(a);
			}
		});
	}

	medicos();
	salas();

	function Genera(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		if (ff != "") {
			var med = '', sala ='';
			if ($('#Medico').val() != null) {
				med = '&med='+$('#Medico').val();
			}
			if ($('#Sala').val() != null) {
				sala = '&sala='+$('#Sala').val();
			}

			var link = 'reporte.php?rep=2&fi='+fi+'&ff='+ff+''+med+''+sala;
			window.open(link,'_blank');
		}
	}
	
	$(document).ready(function () {
		//buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		
	});

	function Genera2(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		if (ff != "") {
			var med = '', sala ='';
			if ($('#Medico').val() != null) {
				med = '&med='+$('#Medico').val();
			}
			if ($('#Sala').val() != null) {
				sala = '&sala='+$('#Sala').val();
			}
			var link = 'reporte.php?rep=221&fi='+fi+'&ff='+ff+''+med+''+sala;
			window.open(link,'_blank');
		}
	}
</script>