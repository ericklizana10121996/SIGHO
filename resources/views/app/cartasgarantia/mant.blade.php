<section class="content">
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header">
					<div class="row">
						<div class="col-xs-12">
							{!! Form::open(['route' => $ruta["search2"], 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
							<div class="form-group">
								{!! Form::label('plan2', 'Plan:') !!}
								{!! Form::text('plan2', '', array('class' => 'form-control input-xs', 'id' => 'plan2', 'size' => '30')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 20, 40, 30, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-xs', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
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
		configurarAnchoModal('750');
		buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="plan2"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});

	});
	var planes2 = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
        limit: 10,
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'plan/planautocompletar/%QUERY',
			filter: function (planes) {
				return $.map(planes, function (movie) {
					return {
						value: movie.razonsocial,
						id: movie.id,
                        coa: movie.coa,
                        deducible:movie.deducible,
                        tipo:movie.tipo,
					};
				});
			}
		}
	});
	planes2.initialize();
	$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[id="plan2"]').typeahead(null,{
		displayKey: 'value',
		source: planes.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[id="plan2"]').val(datum.value);
	});
    function word(entidad){
        window.open("cartasgarantia/word?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&plan="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="plan"]').val()+"&id="+list,"_blank");
    }
</script>