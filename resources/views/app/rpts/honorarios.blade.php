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
								{!! Form::label('fecha', 'Fecha Inicial:') !!}
								{!! Form::date('fecha', date('Y-m-d',strtotime("now",strtotime("-1 week"))), array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
							</div>
                            

							<div class="form-group" id="cajas"></div>

							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Reporte', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'imprimirDetalleF()')) !!}
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
	function cajas(){
		$.ajax({
			type:'GET',
			url:"rpts/cajas",
			data:'',
			success: function(a) {
				$('#cajas').html(a);
			}
		});
	}

	cajas();

	function imprimirDetalleF(){
		var fi = $('#fecha').val();
        window.open('caja/pdfHonorarioF?caja_id='+$('#Medico').val()+'&fecha='+fi,"_blank");
    }


</script>