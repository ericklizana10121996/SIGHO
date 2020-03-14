<?php
use App\Detallemovcaja;
if(!is_null($Movimientocaja)){
    $fecha=$Movimientocaja->fecha;
    $paciente=trim($Movimientocaja->persona->apellidopaterno.' '.$Movimientocaja->persona->apellidomaterno.' '.$Movimientocaja->persona->nombres.' '.$Movimientocaja->persona->bussinesname);
    $numero=$Movimientocaja->voucher;
    $persona_id=$Movimientocaja->persona_id;
    $tipo=$Movimientocaja->formapago;
    $concepto_id=$Movimientocaja->conceptopago_id;
    $total=number_format($Movimientocaja->total,2,'.','');
    $id=$Movimientocaja->id;
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($Movimientocaja, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('id', $id, array('id' => 'id')) !!}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
        		<div class="col-lg-4 col-md-4 col-sm-4">
        			{!! Form::date('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'fecha', 'readonly' => 'true')) !!}
        		</div>
                {!! Form::label('numero', 'Nro.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
                </div>
        	</div>
            <div class="form-group">
        		{!! Form::label('paciente', 'Persona:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                {!! Form::hidden('persona_id', $persona_id, array('id' => 'persona_id')) !!}
        		<div class="col-lg-7 col-md-7 col-sm-7">
        			{!! Form::text('persona', $paciente, array('class' => 'form-control input-xs', 'id' => 'persona')) !!}
        		</div>
        	</div>
            <div class="form-group">
                {!! Form::label('concepto', 'Concepto:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                <div class="col-lg-4 col-md-4 col-sm-4">
                    {!! Form::select('concepto', $cboConcepto, $concepto_id, array('class' => 'form-control input-xs', 'id' => 'concepto')) !!}
                </div>
                {!! Form::label('tipo', 'Tipo:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                 <div class="col-lg-2 col-md-2 col-sm-2 rh">
                    {!! Form::select('tipo', $cboTipo, $tipo, array('class' => 'form-control input-xs', 'id' => 'tipo')) !!}
                </div>
            </div>
            <div class="form-group">
                <table class="table table-condensed">
                    <thead>
                        <tr>
                            <th class="text-center">Nro</th>
                            <th class="text-center">Paciente</th>
                            <th class="text-center">Servicio</th>
                            <th class="text-right">Total</th> 
                            <th class="text-center">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $list = explode(",",$Movimientocaja->listapago);
                        $d=0;
                        if($Movimientocaja->listapago!=""){
                            for($c=0;$c<count($list);$c++){
                                $d=$d+1;
                                $detalle = Detallemovcaja::where('id','=',$list[$c])->first();
                                echo "<tr>";
                                echo "<td>".$d."</td>";
                                echo "<td>".$detalle->movimiento->persona->apellidopaterno.' '.$detalle->movimiento->persona->apellidomaterno.' '.$detalle->movimiento->persona->nombres."</td>";
                                if($detalle->servicio_id>0){
                                    echo "<td>".$detalle->servicio->nombre."</td>";
                                }else{
                                    echo "<td>".$detalle->descripcion."</td>";
                                }
                                echo "<td class='text-right'>".number_format($detalle->pagohospital,2,'.',' ')."</td>";
                                echo "<td>".date("d/m/Y",strtotime($detalle->movimiento->fecha))."</td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="form-group">
                {!! Form::label('total', 'Total:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('total', $total, array('class' => 'form-control input-xs', 'id' => 'total', 'readonly' => 'true')) !!}
                </div>
            </div>
        	<div class="form-group">
        		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
        			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        		</div>
        	</div>
        </div>
     </div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('600');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
    var personas = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        limit: 10,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'caja/personautocompletar/%QUERY',
            filter: function (personas) {
                return $.map(personas, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                    };
                });
            }
        }
    });
    personas.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').typeahead(null,{
        displayKey: 'value',
        source: personas.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona_id"]').val(datum.id);
    });  
}); 


function validarFormaPago(forma){
    if(forma=="Tarjeta"){
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} div[id="divTarjeta"]').css("display","");
    }else{
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} div[id="divTarjeta"]').css("display","none");
    }
}
</script>