<?php 
$url = URL::route($ruta["create"], array('listar'=>'SI'));
?> 
<style>
.tr_hover{
	color:red;
}
</style>
<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $title }}
	</h1>
</section>
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
								{!! Form::label('numero', 'Nro Doc.:') !!}
								{!! Form::text('numero', '', array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('paciente', 'Paciente:') !!}
								{!! Form::text('paciente', '', array('class' => 'form-control input-xs', 'id' => 'paciente')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('fechainicio', 'Fecha Inicio :', array()) !!}
									<div class='input-group input-group-xs' id='divfechainicio'>
										{!! Form::text('fechainicio', date('d/m/Y'), array('class' => 'form-control input-xs', 'id' => 'fechainicio', 'placeholder' => 'Ingrese fecha inicio')) !!}
										<span class="input-group-btn">
											<button class="btn btn-default calendar">
												<i class="glyphicon glyphicon-calendar"></i>
											</button>
										</span>
									</div>
							</div>
							<div class="form-group">
								{!! Form::label('fechafin', 'Fecha Fin :', array()) !!}
									<div class='input-group input-group-xs' id='divfechafin'>
										{!! Form::text('fechafin', null, array('class' => 'form-control input-xs', 'id' => 'fechafin', 'placeholder' => 'Ingrese fecha fin')) !!}
										<span class="input-group-btn">
											<button class="btn btn-default calendar">
												<i class="glyphicon glyphicon-calendar"></i>
											</button>
										</span>
									</div>
							</div>
							<div class="form-group">
								{!! Form::label('tipodocumento', 'Tipo Doc.:') !!}
								{!! Form::select('tipodocumento', $cboTipoDoc,'', array('class' => 'form-control input-xs', 'id' => 'tipodocumento', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('situacion', 'Situacion:') !!}
								{!! Form::select('situacion', $cboSituacion,'', array('class' => 'form-control input-xs', 'id' => 'situacion', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 10, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}

							{!! Form::button('<i class="glyphicon glyphicon-save"></i> Guardar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar','disabled'=> 'true', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel', 'onclick' => 'excelFarmacia(\''.$entidad.'\')')) !!}
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
		init(IDFORMBUSQUEDA+'{{ $entidad }}', '', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="begindate"]').inputmask("dd/mm/yyyy");

		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="numero"]').keyup(function (e) {
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
		$('#divfechainicio').datetimepicker({
			pickTime: false,
			language: 'es'
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="enddate"]').inputmask("dd/mm/yyyy");
		$('#divfechafin').datetimepicker({
			pickTime: false,
			language: 'es'
		});
	});
	function excelFarmacia(entidad){
		var fechainicio = $(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicio"]').val();
		var inicio = fechainicio.split('/');
	    window.open("ventaadmision/excelFarmacia?fechainicio="+inicio[2]+"-"+inicio[1]+"-"+inicio[0]+"&fechafin="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafin"]').val()+"&tipodocumento="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipodocumento"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&numero="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="numero"]').val()+"&situacion="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="situacion"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function cargado(check,idmov,tipo){
		$.ajax({
	        type: "POST",
	        url: "prefactura/cargado",
	        data: "id="+idmov+"&check="+check+"&tipo="+tipo+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),
	        success: function(a) {
	            if(a!='OK'){
	            	alert('Error guardando descargo');
	            }
	        }
    	});
	}
	function guardarObservacion(value,idmov,tipo){
		$.ajax({
	        type: "POST",
	        url: "prefactura/observacion",
	        data: "id="+idmov+"&value="+value+"&tipo="+tipo+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),
	        success: function(a) {
	            if(a!='OK'){
	            	alert('Error guardando observacion');
	            }
	        }
    	});
	}

	function marcar(){
		lista = $('.mchk');
		// alert(lista.length);
		for (var i = 0; i < lista.length; i++) {
			lista[i].click();
			
		}
	}
</script>