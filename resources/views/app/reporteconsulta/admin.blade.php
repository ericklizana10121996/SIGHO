<style>
.tr_hover{
	color:red;
}
</style>
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
							{!! Form::open(['route' => $ruta["search"], 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
						    <div class="form-group">
								{!! Form::label('paciente', 'Paciente:') !!}
								{!! Form::text('paciente', '', array('class' => 'form-control input-xs', 'id' => 'paciente')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('doctor', 'Doctor:') !!}
								{!! Form::text('doctor', '', array('class' => 'form-control input-xs', 'id' => 'doctor')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('servicio', 'Servicio:') !!}
								{!! Form::text('servicio', '', array('class' => 'form-control input-xs', 'id' => 'servicio')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('situacion', 'Situacion:') !!}
								{!! Form::select('situacion', $cboSituacion,'', array('class' => 'form-control input-xs', 'id' => 'situacion', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('fechainicial', 'Fecha Inicial:') !!}
								{!! Form::date('fechainicial', date('Y-m-d',strtotime("now",strtotime("-1 month"))), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafinal', 'Fecha Final:') !!}
								{!! Form::date('fechafinal', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('farmacia', 'Farmacia:') !!}
								{!! Form::select('farmacia', $cboFarmacia, 'N', array('class' => 'form-control input-xs', 'id' => 'farmacia')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 20, 50, 50, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							@if($user->usertype_id==1 || $user->usertype_id==7)
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excel();')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel Marcado', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excelMarcado();')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> UCI/UCIN', array('class' => 'btn btn-success btn-xs', 'id' => 'btnUci','onclick' => 'buscarUci();')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Aten. Convenio', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excelCons();')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Aten. Convenio x Medico', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excelConsMedico();')) !!}
							@endif
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
		//buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="doctor"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
			if(key == 38 || key == 40) {
	            var tabladiv='tablaLista';
				var child = document.getElementById(tabladiv).rows;
				var indice = -1;
				var i=0;
	            $('#tablaLista tr').each(function(index, elemento) {
	                if($(elemento).hasClass("tr_hover")) {
	    			    $(elemento).removeClass("par");
	    				$(elemento).removeClass("impar");								
	    				indice = i;
	                }
	                if(i % 2==0){
	    			    $(elemento).removeClass("tr_hover");
	    			    $(elemento).addClass("impar");
	                }else{
	    				$(elemento).removeClass("tr_hover");								
	    				$(elemento).addClass('par');
	    			}
	    			i++;
	    		});		 
				// abajo
				if(key == 40) {
					if(indice == (child.length - 1)) {
					   indice = 1;
					} else {
					   if(indice==-1) indice=0;
	                   indice=indice+1;
					} 
				// arriba
				} else if(key == 38) {
					indice = indice - 1;
					if(indice==0) indice=-1;
					if(indice < 0) {
						indice = (child.length - 1);
					}
				}	 
				child[indice].className = child[indice].className+' tr_hover';
        	}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="paciente"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
			if(key == 38 || key == 40) {
	            var tabladiv='tablaLista';
				var child = document.getElementById(tabladiv).rows;
				var indice = -1;
				var i=0;
	            $('#tablaLista tr').each(function(index, elemento) {
	                if($(elemento).hasClass("tr_hover")) {
	    			    $(elemento).removeClass("par");
	    				$(elemento).removeClass("impar");								
	    				indice = i;
	                }
	                if(i % 2==0){
	    			    $(elemento).removeClass("tr_hover");
	    			    $(elemento).addClass("impar");
	                }else{
	    				$(elemento).removeClass("tr_hover");								
	    				$(elemento).addClass('par');
	    			}
	    			i++;
	    		});		 
				// abajo
				if(key == 40) {
					if(indice == (child.length - 1)) {
					   indice = 1;
					} else {
					   if(indice==-1) indice=0;
	                   indice=indice+1;
					} 
				// arriba
				} else if(key == 38) {
					indice = indice - 1;
					if(indice==0) indice=-1;
					if(indice < 0) {
						indice = (child.length - 1);
					}
				}	 
				child[indice].className = child[indice].className+' tr_hover';
			
        	}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="servicio"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
			if(key == 38 || key == 40) {
	            var tabladiv='tablaLista';
				var child = document.getElementById(tabladiv).rows;
				var indice = -1;
				var i=0;
	            $('#tablaLista tr').each(function(index, elemento) {
	                if($(elemento).hasClass("tr_hover")) {
	    			    $(elemento).removeClass("par");
	    				$(elemento).removeClass("impar");								
	    				indice = i;
	                }
	                if(i % 2==0){
	    			    $(elemento).removeClass("tr_hover");
	    			    $(elemento).addClass("impar");
	                }else{
	    				$(elemento).removeClass("tr_hover");								
	    				$(elemento).addClass('par');
	    			}
	    			i++;
	    		});		 
				// abajo
				if(key == 40) {
					if(indice == (child.length - 1)) {
					   indice = 1;
					} else {
					   if(indice==-1) indice=0;
	                   indice=indice+1;
					} 
				// arriba
				} else if(key == 38) {
					indice = indice - 1;
					if(indice==0) indice=-1;
					if(indice < 0) {
						indice = (child.length - 1);
					}
				}	 
				child[indice].className = child[indice].className+' tr_hover';
			
        	}
		});
	});
	function buscarUci(){
		window.open("reporteconsulta/uci?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val(),"_blank");
	}
	function excel(entidad){
	    window.open("reporteconsulta/excel?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&situacion="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="situacion"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="doctor"]').val()+"&servicio="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="servicio"]').val()+"&farmacia="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="farmacia"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function excelCons(entidad){
	    window.open("reporteconsulta/excelCons?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&situacion="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="situacion"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="doctor"]').val()+"&servicio="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="servicio"]').val()+"&farmacia="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="farmacia"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function excelConsMedico(entidad){
	    window.open("reporteconsulta/excelConsMedico?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&situacion="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="situacion"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="doctor"]').val()+"&servicio="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="servicio"]').val()+"&farmacia="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="farmacia"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function excelMarcado(entidad){
	    window.open("reporteconsulta/excelMarcado?fechainicial="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicial"]').val()+"&fechafinal="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafinal"]').val()+"&situacion="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="situacion"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&doctor="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="doctor"]').val()+"&servicio="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="servicio"]').val()+"&farmacia="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="farmacia"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function farmacia(check){
		if(check){
			$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="farmacia"]').val('S');
		}else{
			$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="farmacia"]').val('N');
		}
	}
</script>