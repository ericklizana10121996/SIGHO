<!-- Content Header (Page header) -->
<?php
	if($user->id==41 || $user->id == 49)
	    $serie='008';
	else
	    $serie='002';

	$serie = "";
?>

<section class="content-header">
	<h1>
		{{ $title }}
		{{-- <small>Descripción</small> --}}
	</h1>
	{{--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Tables</a></li>
		<li class="active">Data tables</li>
	</ol>
	--}}
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
							<div class="form-group">
								{!! Form::label('serie', 'Serie:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
	        			  		<div class="col-lg-2 col-md-2 col-sm-2" style="margin-top: -2px;">
			        				{!! Form::select('serie', $cboSerie, $serie, array('class' => 'form-control input-xs', 'id' => 'serie')) !!}
			        			</div>
			        		</div>
							
							<div class="form-group">
								{!! Form::label('plan', 'Plan:') !!}
								{!! Form::text('plan', '', array('class' => 'form-control input-xs', 'id' => 'plan', 'size' => '30')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('usuario', 'Mi Usuario:') !!}
								{!! Form::hidden('usuario', '', array('id' => 'usuario')) !!}
								<input type="checkbox" checked="" name="check" id="check" onclick="if(this.checked){$('#usuario').val('');}else{$('#usuario').val('Todos');}">
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 20, 40, 30, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-save-file"></i> Word', array('class' => 'btn btn-info btn-xs', 'id' => 'btnWord', 'onclick' => 'word(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Cartas', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnNuevo1', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
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
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="plan"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});

	});
	var planes = new Bloodhound({
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
	planes.initialize();
	$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[id="plan"]').typeahead(null,{
		displayKey: 'value',
		source: planes.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[id="plan"]').val(datum.value);
	});
    function word(entidad){
        window.open("cartasgarantia/word?fechainicial="+$('input[name="fechainicial"]').val()+"&fechafinal="+$('input[name="fechafinal"]').val()+"&plan="+$('input[name="plan"]').val()+"&id="+list,"_blank");
    }
    var list = new Array();
    function marcar(check,id){
		if(check){
			var band = false;
			for(x=0;list.length>x;x++){
				if(list[x]==id){
					band=true;
				}
			}
			if(!band){
				list.push(id);
				$("#td"+id).css('background-color','rgba(238, 0, 0, 0.27)');
			}
		}else{
			for(c=0;list.length>c;c++){
				if(list[c]==id){
					list.splice(c,1);
					$("#td"+id).css('background-color','');
				}
			}
		}
		console.log(list);
	}
	function validarCheck(){
		for(c=0;list.length>c;c++){
			$("#td"+list[c]).css('background-color','rgba(238, 0, 0, 0.27)');
			$('#chk'+list[c]).attr('checked', true);
		}
	}
	var todos=new Array();
	function cargarTodos(v){
		var tod = v.split(',');
		for(c=0;tod.length>c;c++){
			todos.push(parseInt(tod[c]));
		}
	}
	function marcarTodos(check){
		if(check){
			for(c=0;todos.length>c;c++){
				var band = false;
				for(x=0;list.length>x;x++){
					if(list[x]==todos[c]){
						band=true;
					}
				}
				if(!band){
					list.push(todos[c]);
					$("#td"+todos[c]).css('background-color','rgba(238, 0, 0, 0.27)');
					$('#chk'+todos[c]).attr('checked', true);
				}
			}
		}else{
			for(c=0;todos.length>c;c++){
				for(x=0;list.length>x;x++){
					if(list[x]==todos[c]){
						list.splice(x,1);
						$("#td"+todos[c]).css('background-color','');
						$('#chk'+todos[c]).removeAttr('checked');
					}
				}
			}
		}
		console.log(list);
	}
</script>