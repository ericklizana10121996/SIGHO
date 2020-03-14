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
								{!! Form::date('fechainicial', date('Y-m-d',strtotime("now",strtotime("-365 day"))), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>

							<div class="form-group">
								<label for="Tipo">Modo de Ingreso:</label>
								<select class="form-control" id="Tipo">
									<option value="0">Todos</option>
									<option value="1">Ambulatorio</option>
									<option value="2">Cirugia</option>
								</select>
							</div>

							<div class="form-group"><label for="Medico">Paciente DNI:</label><input type="number" class="form-control" id="Medico"></div>
							<div class="form-group"><label for="nMedico">Apellido Paterno:</label><input type="text" class="form-control" id="nMedico"></div>
							<div class="form-group"><label for="mMedico">Apellido Materno:</label><input type="text" class="form-control" id="mMedico"></div>

							<div class="form-group" id="Nombres"></div>

							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'Genera()')) !!}
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
	var cambia = 1;

	$( "#nMedico" ).change(function() {
		cambia = 1;
	});

	$( "#mMedico" ).change(function() {
		cambia = 1;
	});

	function BuscaNombre(){
		var apepat = $('#nMedico').val();
		var apemat = $('#mMedico').val();
		
		$.ajax({
			type:'GET',
			url:"rpts/nombres/"+apepat+"/"+apemat,
			data:'',
			success: function(a) {
				$('#Nombres').html(a);
			}
		});
		cambia = 0;

		if(apepat == '' || apemat == ''){
			cambia = 1;
		}
	}

	function Genera(){

		if(cambia == 1){
			BuscaNombre();
		} else {
			var fi = $('#fechainicial').val();
			var ff = $('#fechafinal').val();
			var dni = $('#Medico').val();
			var apepat = $('#nMedico').val();
			var apemat = $('#mMedico').val();
			var nomb = $('#Nombre').val();

			if ($('#Tipo').val() != null) {
				tipo = '&tipo='+$('#Tipo').val();
			}

			if (nomb != null) {
				med = '&med='+dni;
				nmed = '&nmed='+apepat;
				mmed = '&mmed='+apemat;
				nomb = '&nombres='+nomb;
				
				if(dni != '' ){
					var link = 'reporte.php?rep=4&fi='+fi+'&ff='+ff+''+med+''+nmed+''+mmed+''+tipo;
					window.open(link,'_blank');
				}else if(apepat != ''){
					if (apemat != ''){
						var link = 'reporte.php?rep=4&fi='+fi+'&ff='+ff+''+med+''+nmed+''+mmed+''+nomb+''+tipo;
						window.open(link,'_blank');
					}
				}
			}
		}
	}
	
</script>