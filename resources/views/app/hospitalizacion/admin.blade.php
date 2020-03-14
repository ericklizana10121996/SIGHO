<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $title }}
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
                            <table class="table table-bordered table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th class="text-center" colspan="5">
                                            <i class="fa fa-bed" style="color: green;"></i> &nbsp;Disponible |
                                            <i class="fa fa-bed" style="color: red;"></i> &nbsp;Ocupada
                                        </th>
                                    </tr>    
                                </thead>
                                <tbody>
                                <?php
                                $idpisoant=0;$c=0;
                                foreach ($lista as $key => $value){$c=$c+1;
                                    if($idpisoant<>$value->piso_id){
                                        if($idpisoant>0){
                                            echo "</tr>";
                                        }
                                        echo "<tr><th class='text-center' colspan='5'>".$value->piso->nombre."</th></tr><tr>";
                                        $idpisoant=$value->piso_id;
                                        $c=1;
                                    }                    
                                    if($value->situacion=="D") $color="green";else $color="red";
                                    if($value->situacion=="D"){
                                    	echo "<td align='center' onclick=\"modal('". URL::route($ruta["create"])."?listar=SI&habitacion_id=".$value->id."', '$titulo_registrar', this);\"><i class='fa fa-bed' style='color:$color'></i>&nbsp;".$value->nombre."</td>";            
                                    }else{
                                    	echo "<td align='center' onclick=\"modal('". URL::route($ruta["edit"],array($value->hospitalizacion_id,'listar' => 'SI'))."', 'Modificar Hospitalizacion', this);\"><i class='fa fa-bed' style='color:$color'></i>&nbsp;".$value->nombre."</td>";            
                                    }
                                    if($c%5==0 && $c>0){
                                    	echo "</tr><tr>";
                                    }
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr />
					<div class="row">
						<div class="col-xs-12">
							{!! Form::open(['route' => $ruta["search"], 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
							<div class="form-group">
								{!! Form::label('solo', 'Solo:') !!}
								{!! Form::select('solo', $cboSolo, 'H', array('class' => 'form-control input-xs', 'id' => 'solo', 'onchange' => 'validarSolo(this.value);')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('paciente', 'Paciente:') !!}
								{!! Form::text('paciente', '', array('class' => 'form-control input-xs', 'id' => 'paciente')) !!}
							</div>
							<div class="form-group fecha"  style="display: none;">
								{!! Form::label('tipopaciente', 'Tipo Pac.:') !!}
								{!! Form::select('tipopaciente', $cboTipoPaciente,null, array('class' => 'form-control input-xs', 'id' => 'tipopaciente', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group fecha"  style="display: none;">
								{!! Form::label('fechaingresoinicio', 'Fecha Ingreso Inicio:') !!}
								{!! Form::date('fechaingresoinicio', date('Y-m').'-01', array('class' => 'form-control input-xs', 'id' => 'fechaingresoinicio')) !!}
							</div>
      	                    <div class="form-group fecha" style="display: none;">
								{!! Form::label('fechaingresofin', 'Fecha Ingreso Fin:') !!}
								{!! Form::date('fechaingresofin', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechaingresofin')) !!}
							</div>
      	                    <div class="form-group fecha"  style="display: none;">
								{!! Form::label('fechainicio', 'Fecha Alta Inicio:') !!}
								{!! Form::date('fechainicio', null, array('class' => 'form-control input-xs', 'id' => 'fechainicio')) !!}
							</div>
      	                    <div class="form-group fecha" style="display: none;">
								{!! Form::label('fechafin', 'Fecha Alta Fin:') !!}
								{!! Form::date('fechafin', null, array('class' => 'form-control input-xs', 'id' => 'fechafin')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-print fecha"></i> Imprimir', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'imprimir()')) !!}

							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel','onclick' => 'excel();')) !!}

							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel Actualizado (20/08/2019)', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnExcel02','onclick' => 'excel02();')) !!}
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
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="paciente"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
	});

    function validarSolo(tipo){
    	if(tipo=='H'){
    		$('.fecha').css('display','none');
    	}else{
    		$('.fecha').css('display','');
    	}
    }
    function excel(entidad){
	    window.open("hospitalizacion/excel?fechainicio="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicio"]').val()+"&fechafin="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafin"]').val()+"&fechaingresoinicio="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechaingresoinicio"]').val()+"&fechaingresofin="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechaingresofin"]').val()+"&tipopaciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipopaciente"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&solo="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="solo"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
    function excel02(entidad){
	    window.open("hospitalizacion/excel02?fechainicio="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechainicio"]').val()+"&fechafin="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechafin"]').val()+"&fechaingresoinicio="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechaingresoinicio"]').val()+"&fechaingresofin="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="fechaingresofin"]').val()+"&tipopaciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="tipopaciente"]').val()+"&paciente="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="paciente"]').val()+"&solo="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="solo"]').val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function imprimir(){
    	var tipopaciente = 'Todos';
    	var alta = $('#solo').val();
    	var fi = $('#fechaingresoinicio').val();
    	var ff = $('#fechaingresofin').val();
    	if ($('#tipopaciente').val() == 'Convenio' || $('#tipopaciente').val() == 'Particular') {
    		tipopaciente = $('#tipopaciente').val();
    	}
		window.open("hospitalizacion/pdfHospitalizados/"+tipopaciente+"/"+alta+"/"+fi+"/"+ff,"_blank");
    }

    function cargado2(check,idmov,tipo){
		$.ajax({
	        type: "POST",
	        url: "hospitalizacion/cargado",
	        data: "id="+idmov+"&check="+check+"&tipo="+tipo+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),
	        success: function(a) {
	            if(a!='OK'){
	            	alert('Error guardando descargo');
	            }
	        }
    	});
	}
</script>