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

							<div class="form-group">
								<label for="Tipo">Tipo:</label>
								<select class="form-control" id="Tipo">
									<option value="0">Todos</option>
									{{-- <option value="1">Pagado</option>
									<option value="2">Pendiente</option>
									<option value="4">Anulados</option> --}}
									<option value="3">Por Paciente</option>
								</select>
							</div>

							<div class="form-group" id="meds">
								<input class="form-control" type="text" id="indicio">
								<div class="form-group" id="rmeds" style="width: 100%"></div>
							</div>

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
	var medicinasel = 0;

	$('#indicio').keyup(function(){
		var indicio = $('#indicio').val();
		var linkead = "nombres";
		var nindicio = indicio;
		var letra;
		var mindi = "";
		var indle = indicio.length;
		var divi = false;

		
		for (var i = 0; i < indle; i++) {
			if (!divi) {
				letra = indicio.charAt(i);
				if (letra == " ") {
					mindi = indicio.substr(i+1, indle);
					nindicio = indicio.substr(0, i);
					divi = true;
				}
			}
		}
		nindicio = nindicio+"2"+mindi;

		if (indicio.length >= 3) {
			$.ajax({
				type:'GET',
				url:"rpts/nombres/"+nindicio,
				data:'',
				success: function(a) {
					$('#rmeds').html(a);
				}
			});
		}
	});

	$('#meds').hide();
	$('#pacs').hide();

	$('#Tipo').change(function(){
		$('#rmeds').html('');
		$('#indicio').val('');
		medicinasel = 0;
		if ($('#Tipo').val() == 3) {
			$('#pacs').hide();
			$('#meds').show();
		} else if ($('#Tipo').val() == 5) {
			$('#meds').hide();
			$('#pacs').show();
		} else {
			$('#pacs').hide();
			$('#meds').hide();
		}
	});

	$( "#nMedico" ).change(function() {
		cambia = 1;
	});

	$( "#Medico" ).change(function() {
		cambia = 0;
	});

	function selecciona(id){
		medicinasel = id;
		Genera();
	}
	

	function Genera(){
		var fi = $('#fechainicial').val();
		var ff = $('#fechafinal').val();
		var nomb = $('#Nombre').val();
		var dni = $('#Medico').val();
		var apepat = $('#nMedico').val();
		var apemat = $('#mMedico').val();
		if (ff != "") {
			var med = '', tipo ='';
			if ($('#Medico').val() != '') {
				med = '&med='+$('#Medico').val();
			}
			if ($('#Tipo').val() != null) {
				tipo = '&tipo='+$('#Tipo').val();
			}
			if ($('#Tipo').val() == 3) {
				tipo = '&tipo=3&med='+medicinasel;
			}
			if ($('#Tipo').val() == 5) {
				var vari = nomb;
				if (apepat == '') { vari = dni; }
				tipo = '&tipo=5&med='+vari;
			}
			var link = 'reporte.php?rep=23&fi='+fi+'&ff='+ff+''+med+''+tipo;
			window.open(link,'_blank');
		}
	}
	
</script>