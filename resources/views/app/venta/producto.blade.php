<!-- Content Header (Page header) -->
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
							{!! Form::open(['route' => 'venta.listarproducto', 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
							{!! Form::hidden('tipoventa', $tipoventa, array('id' => 'tipoventa')) !!}
							{!! Form::hidden('descuentokayros', $descuentokayros, array('id' => 'descuentokayros')) !!}
							{!! Form::hidden('copago', $copago, array('id' => 'copago')) !!}
							<div class="form-group">
								{!! Form::label('nombre', 'Nombre:') !!}
								{!! Form::text('nombre', '', array('class' => 'form-control input-xs', 'id' => 'nombre','onkeyup' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('tipo', 'Tipo:') !!}
								{!! Form::select('tipo', $cboTipo, null, array('class' => 'form-control input-xs', 'id' => 'tipo','onchange' =>'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 6, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							
							
						</div>
					</div>
				</div>
				<!-- /.box-header -->
				<div class="box-body" id="listado{{ $entidad }}">
				</div>
				{!! Form::button('Cerrar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancel', 'onclick' => 'cerrarModal();')) !!}
				{!! Form::close() !!}
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
		configurarAnchoModal('1400');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').focus();
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
			if (key == '27') {
				cerrarModal();
			}
		});
	});

	function addpurchasecart(elemento){
	var cantidad = $('#txtQuantity' + elemento).val();
	var price = $('#txtPrice' + elemento).val();
	var preciokayros = $('#txtPricekayros' + elemento).val();
	var product_id = $('#product_id' + elemento).val();
	var stock = $('#stock' + elemento).val();
	var tipoventa = $(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="tipoventa"]').val();
	var descuentokayros = $(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="descuentokayros"]').val();
	var copago = $(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="copago"]').val();

	var _token =$('input[name=_token]').val();
	if(cantidad.trim() == '' ){
		bootbox.alert("Ingrese Cantidad");
            setTimeout(function () {
                $('#txtQuantity' + elemento).focus();
            },2000) 
	}else if(cantidad.trim() == 0){
		bootbox.alert("la cantidad debe ser mayor a 0");
            setTimeout(function () {
                $('#txtQuantity' + elemento).focus();
            },2000) 
	}else if(price.trim() == '' ){
		bootbox.alert("Ingrese Precio");
            setTimeout(function () {
                $('#txtPrecio' + elemento).focus();
            },2000) 
	}else if(price.trim() == 0){
		bootbox.alert("el precio debe ser mayor a 0");
            setTimeout(function () {
                $('#txtPrecio' + elemento).focus();
            },2000) 
	}else if(parseFloat(cantidad.trim()) > parseFloat(stock)){
		bootbox.alert("No puede vender una cantidad mayor al stock actual");
            setTimeout(function () {
                $('#txtQuantity' + elemento).focus();
            },2000) 
	}else{
		$.post('{{ URL::route("venta.agregarcarritoventa")}}', {cantidad: cantidad,precio: price, producto_id: product_id, tipoventa: tipoventa, descuentokayros: descuentokayros, copago: copago, preciokayros: preciokayros,_token: _token} , function(data){
			$('#divDetail').html(data);
			calculatetotal();
			bootbox.alert("Producto Agregado");
            setTimeout(function () {
                $(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').focus();
            },2000) 
			//var totalpedido = $('#totalpedido').val();
			//$('#total').val(totalpedido);
		});
	}
	}
</script>