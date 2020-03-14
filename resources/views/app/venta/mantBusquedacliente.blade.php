<style type="text/css">
    * {
    font-size: 12px;
    font-family: 'Helvetica', Arial, Sans-Serif;
    box-sizing: border-box;
}

table, th, td {
    border-collapse:collapse;
    border: solid 1px #ccc;
    padding: 10px 20px;
    text-align: center;
}

th {
    background: #0f4871;
    color: #fff;
}

tr:nth-child(2n) {
    background: #f1f1f1;
}
td:hover {
    color: #fff;
    background: #64A8FB;
}
td:focus {
    background: #64A8FB;
}

.editing {
    border: 2px dotted #c9c9c9;
}

#edit { 
    display: none;
}
  </style>

  <title> by lanebuckingham</title>

  
    
<script type="text/javascript">//<![CDATA[

var indicador = 0;
var id = '';
var currCell = null;
var firstid = '';
var editing = false;

init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    
/*  $(window).load(function(){

// User can cancel edit by pressing escape
$('#edit').keydown(function (e) {
    if (editing && e.which == 27) { 
        editing = false;
        $('#edit').hide();
        currCell.toggleClass("editing");
        currCell.focus();
    }
});
});//]]> */

$(document).ready(function() {
    configurarAnchoModal('600');
    //currCell = $('td').first();
    //currCell = $('#1');
    //var editing = false;
    //currCell.focus();
    //$('#1').focus();
    //alert(currCell.attr("id"));

    // User clicks on a cell
    $('td').click(function() {
        currCell = $(this);
        //alert ('holi');
        //$(this).attr('contenteditable','true');
        //alert($(this).attr("id"));
        id = $(this).attr("id");
        //indicador = 1;
        //edit();
    });

    // User navigates table using keyboard
    $('table').keydown(function (e) {
        var c = "";
        if (e.which == 39) {
            // Right Arrow
            if (indicador == 0) {
                c = currCell.next();
            }
            
        } else if (e.which == 37) { 
            // Left Arrow
            if (indicador == 0) {
                c = currCell.prev();
            }
            
        } else if (e.which == 38) { 
            // Up Arrow
            if (indicador == 0) {
                c = currCell.closest('tr').prev().find('td:eq(' + 
              currCell.index() + ')');
            }
            
        } else if (e.which == 40) { 
            // Down Arrow
            if (indicador == 0) {
                c = currCell.closest('tr').next().find('td:eq(' + 
              currCell.index() + ')');
            }
            
        } else if (!editing && (e.which == 13 || e.which == 32)) { 
            // Enter or Spacebar - edit cell
            e.preventDefault();
            if (indicador == 0) {
                seleccionarCliente(seleccionado);
                //alert(currCell.html());
                //alert(id);
            }else{
                indicador = 0;
                currCell.removeAttr("contenteditable");
            }
            //e.preventDefault();
            //edit();
        } else if (!editing && (e.which == 9 && !e.shiftKey)) { 
            // Tab
            if (indicador == 0) {
                e.preventDefault();
                c = currCell.next();
            }
            
        } else if (!editing && (e.which == 9 && e.shiftKey)) { 
            // Shift + Tab
            if (indicador == 0) {
                e.preventDefault();
                c = currCell.prev();
            }
            
        }else if (e.which == 27) {
            //currCell.attr('contenteditable','true');  
            id = currCell.attr("id");
            currCell.html('');
            setcursor(id);
            indicador = 1;
            var idtxt = '#txt'+id;
            $(idtxt).focus();
        } 
        
        // If we didn't hit a boundary, update the current cell
        if (c.length > 0) {
            currCell = c;
            currCell.focus();
        }
    });

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="name"]').on( 'keydown', function () {
        var e = window.event; 
        var keyc = e.keyCode || e.which;

        if(this.value.length>2 && keyc == 13 ){
            buscarCliente(this.value);
        }

        if (keyc == 40) {
            //alert('entro');
            //var cel = '#'+firstid;
            var id ='#'+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="firstid"]').val();
            currCell = $(id);
            //var editing = false;
            currCell.focus();
            $('#1').focus();
            $(id).focus();
        }else if (keyc == 27) {
            seleccionarParticular(this.value);
        }

    });
});

// Show edit box
function edit() {
    editing = true;
    currCell.toggleClass("editing");
    $('#edit').show();
    $('#edit #text').val(currCell.html());
    $('#edit #text').select();
}

// User saves edits
$('#edit form').submit(function(e) {
    editing = false;
    e.preventDefault();
    // Ajax to update value in database
    $.get('#', '', function() {
        $('#edit').hide();
        currCell.toggleClass("editing");
        currCell.html($('#edit #text').val());
        currCell.focus();
    });
});

function setcursor(id) {
    var celda = document.getElementById(id);
    var codigo = document.createElement("INPUT");
    codigo.setAttribute("type","text");
    codigo.setAttribute("size","8");
    codigo.setAttribute("maxlength","20");
    codigo.setAttribute("onkeydown","funcionEnter(event);");
    codigo.setAttribute("id","txt" + id);
    //alert('entro');
     
    celda.appendChild(codigo);
    
}
$(IDFORMMANTENIMIENTO+'{!! $entidad !!}' + ' :input[id="name"]').focus();
</script>

  
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($venta, $formData) !!}  
    {!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('firstid', null, array('id' => 'firstid')) !!}
    <div class="form-group">
        {!! Form::label('name', 'Cliente:', array('class' => 'col-lg-4 col-md-4 col-sm-4 control-label')) !!}
        <div class="col-lg-4 col-md-4 col-sm-4">
            {!! Form::text('name', null, array('class' => 'form-control input-xs', 'id' => 'name', 'placeholder' => 'Ingrese nombre','onkeypress' => '')) !!}
        </div>
        <div class="col-lg-1 col-md-1 col-sm-1">
            {!! Form::button('<i class="fa fa-file fa-lg"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('historia.create', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Historia\', this);', 'title' => 'Nueva Historia')) !!}
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div id="divBusqueda" class="table-responsive" style="overflow:auto; height:350px; padding-right:10px; border:1px outset">

           <!-- <table>
                <thead>
                    <tr>
                        <th style='width:320px;'>Cliente</th>
                        <th style='width:160px;'>Contratante</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td id="1" tabindex="1">1</td>
                        <td id="2" tabindex="2">2</td>
                    </tr>
                    <tr>
                        <td id="3" tabindex="3">3</td>
                        <td id="4" tabindex="4">4</td>
                    </tr>
                    <tr>
                        <td id="5" tabindex="5">5</td>
                        <td id="6" tabindex="6">6</td> 
                    </tr>
                    <tr>
                        <td id="7" tabindex="7">7</td>
                        <td id="8" tabindex="8">8</td>
                    </tr>
                    <tr>
                        <td id="9" tabindex="9">9</td>
                        <td id="10" tabindex="10">10</td>     
                    </tr>
                    <tr>
                        <td id="11" tabindex="11">11</td>
                        <td id="12" tabindex="12">12</td>
                        
                    </tr>
                    <tr>
                        <td id="13" tabindex="13">13</td>
                        <td id="14" tabindex="14">14</td>
                    </tr>
                    <tr>
                        <td id="15" tabindex="15">15</td>
                        <td id="16" tabindex="16">16</td>
                    </tr>
                </tbody>
            </table>-->
                
            </div>
        </div>
    </div>
    <br>
    
    <div class="form-group">
        <div class="col-lg-12 col-md-12 col-sm-12 text-right">

            {!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        </div>
    </div>
{!! Form::close() !!}
  
  <script>


  function funcionEnter(evento) 
{ 
    //para IE 
    if (window.event) 
    { 
        if (window.event.keyCode==13) 
        { 
            var idtxt = '#txt'+id;
            var value = $(idtxt).val();
            currCell.html(value);
            currCell.focus();
            //indicador = 0;
        } 
    } 
    else 
    { 
        //Firefox y otros navegadores 
        if (evento) 
        { 
            if(evento.which==13) 
            { 
                var idtxt = '#txt'+id;
                var value = $(idtxt).val();
                currCell.html(value); 
                currCell.focus();
                //indicador = 0;
            } 
        } 
    } 
}

var valorinicial="";
function buscarCliente(valor){
    var indice2 = -1;
    if(valorinicial!=valor){valorinicial=valor;
        $.ajax({
            type: "POST",
            url: "venta/buscandoclientes",
            data: "nombre="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="name"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            beforeSend: function(){
                 $('#divBusqueda').html(imgCargando());
            },
            success: function(a) {
                datos=JSON.parse(a);
                $("#divBusqueda").html("<table id='tablaClientes'><thead><tr><th style='width:350px;'>Cliente</th><th>Contratante</th></thead></table>");
                var pag=parseInt($("#pag").val());
                var d=0;
                for(c=0; c < datos.length; c++){
                    if (d==0) {
                        //firstid = datos[c].person_id;
                        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="firstid"]').val(datos[c].person_id);
                    }
                    aux1 = d+1;
                    aux2 = d+2;
                    var a="<tr><td class='text-left' id='"+datos[c].person_id+"' tabindex='"+aux1+"'>"+datos[c].value+"</td><td id='"+datos[c].person_id+"' tabindex='"+aux2+"'>"+datos[c].convenio+"</td></tr>";
                    $("#tablaClientes").append(a); 
                    d = d+2;          
                }
                propiedadestabla();
                /*$('#tablaClientes').DataTable({
                    "scrollY":        "250px",
                    "scrollCollapse": true,
                    "paging":         false,
                    "ordering"        :false
                });
                $('#tablaClientes_filter').css('display','none');
                $("#tablaClientes_info").css("display","none");*/
            }
        });
    }
}

function propiedadestabla() {
    // User clicks on a cell
    $('td').click(function() {
        currCell = $(this);
        //alert ('holi');
        //$(this).attr('contenteditable','true');
        //alert($(this).attr("id"));
        id = $(this).attr("id");
        //indicador = 1;
        //edit();
    });

    // User navigates table using keyboard
    $('table').keydown(function (e) {
        var c = "";
        if (e.which == 39) {
            // Right Arrow
            if (indicador == 0) {
                c = currCell.next();
            }
            
        } else if (e.which == 37) { 
            // Left Arrow
            if (indicador == 0) {
                c = currCell.prev();
            }
            
        } else if (e.which == 38) { 
            // Up Arrow
            if (indicador == 0) {
                c = currCell.closest('tr').prev().find('td:eq(' + 
              currCell.index() + ')');
            }
            
        } else if (e.which == 40) { 
            // Down Arrow
            if (indicador == 0) {
                c = currCell.closest('tr').next().find('td:eq(' + 
              currCell.index() + ')');
            }
            
        } else if (!editing && (e.which == 13 || e.which == 32)) { 
            // Enter or Spacebar - edit cell
            e.preventDefault();
            if (indicador == 0) {
                id = currCell.attr("id");
                seleccionarCliente(id);
                //alert(currCell.html());
                //alert(id);
            }else{
                indicador = 0;
                currCell.removeAttr("contenteditable");
            }
            //e.preventDefault();
            //edit();
        } else if (!editing && (e.which == 9 && !e.shiftKey)) { 
            // Tab
            if (indicador == 0) {
                e.preventDefault();
                c = currCell.next();
            }
            
        } else if (!editing && (e.which == 9 && e.shiftKey)) { 
            // Shift + Tab
            if (indicador == 0) {
                e.preventDefault();
                c = currCell.prev();
            }
            
        }else if (e.which == 27) {
            //currCell.attr('contenteditable','true');  
            id = currCell.attr("id");
            //alert(id);
            currCell.html('');
            setcursor(id);
            indicador = 1;
            var idtxt = '#txt'+id;
            $(idtxt).focus();
        } 
        
        // If we didn't hit a boundary, update the current cell
        if (c.length > 0) {
            currCell = c;
            currCell.focus();
        }
    });
}
</script>






