<?php
//error_log("ROUTES ");
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
//Clear Cache facade value:
Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared</h1>';
});
//Reoptimized class loader:
Route::get('/optimize', function() {
    $exitCode = Artisan::call('optimize');
    return '<h1>Reoptimized class loader</h1>';
});
//Route cache:
Route::get('/route-cache', function() {
    $exitCode = Artisan::call('route:cache');
    return '<h1>Routes cached</h1>';
});
//Clear Route cache:
Route::get('/route-clear', function() {
    $exitCode = Artisan::call('route:clear');
    return '<h1>Route cache cleared</h1>';
});
//Clear View cache:
Route::get('/view-clear', function() {
    $exitCode = Artisan::call('view:clear');
    return '<h1>View cache cleared</h1>';
});
//Clear Config cache:
Route::get('/config-cache', function() {
    $exitCode = Artisan::call('config:cache');
    return '<h1>Clear Config cleared</h1>';
});

Route::post('/producto/vistamedico', 'ProductoController@vistamedico');
Route::post('/producto/cie10', 'ProductoController@cie10');
    

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', ['as' =>'auth/login', 'uses' => 'Auth\AuthController@postLogin']);
Route::get('auth/logout', ['as' => 'auth/logout', 'uses' => 'Auth\AuthController@getLogout']);
 
// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', ['as' => 'auth/register', 'uses' => 'Auth\AuthController@postRegister']);

Route::get('/', function(){
    return redirect('/dashboard');
});

Route::get('/vistamedico', function(){
    return View::make('app.producto.vistamedico');
});

Route::post('/seguimiento/alerta', 'SeguimientoController@alerta')->name('seguimiento.alerta');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', function(){
        return View::make('layouts.app');
    });

    Route::post('categoriaopcionmenu/buscar', 'CategoriaopcionmenuController@buscar')->name('categoriaopcionmenu.buscar');
    Route::get('categoriaopcionmenu/eliminar/{id}/{listarluego}', 'CategoriaopcionmenuController@eliminar')->name('categoriaopcionmenu.eliminar');
    Route::resource('categoriaopcionmenu', 'CategoriaopcionmenuController', array('except' => array('show')));

    Route::post('opcionmenu/buscar', 'OpcionmenuController@buscar')->name('opcionmenu.buscar');
    Route::get('opcionmenu/eliminar/{id}/{listarluego}', 'OpcionmenuController@eliminar')->name('opcionmenu.eliminar');
    Route::resource('opcionmenu', 'OpcionmenuController', array('except' => array('show')));

    Route::post('tipousuario/buscar', 'TipousuarioController@buscar')->name('tipousuario.buscar');
    Route::get('tipousuario/obtenerpermisos/{listar}/{id}', 'TipousuarioController@obtenerpermisos')->name('tipousuario.obtenerpermisos');
    Route::post('tipousuario/guardarpermisos/{id}', 'TipousuarioController@guardarpermisos')->name('tipousuario.guardarpermisos');
    Route::get('tipousuario/eliminar/{id}/{listarluego}', 'TipousuarioController@eliminar')->name('tipousuario.eliminar');
    Route::resource('tipousuario', 'TipousuarioController', array('except' => array('show')));

    Route::post('workertype/buscar', 'WorkertypeController@buscar')->name('workertype.buscar');
    Route::get('workertype/eliminar/{id}/{listarluego}', 'WorkertypeController@eliminar')->name('workertype.eliminar');
    Route::resource('workertype', 'WorkertypeController', array('except' => array('show')));

    Route::post('employee/buscar', 'EmployeeController@buscar')->name('employee.buscar');
    Route::get('employee/eliminar/{id}/{listarluego}', 'EmployeeController@eliminar')->name('employee.eliminar');
    Route::get('employee/validarexistente', 'EmployeeController@validarexistente')->name('employee.validarexistente');
    Route::post('employee/validardni', 'EmployeeController@validardni')->name('employee.validardni');
    Route::post('employee/guardarexistente', 'EmployeeController@guardarexistente')->name('employee.guardarexistente');
    Route::resource('employee', 'EmployeeController', array('except' => array('show')));
    Route::get('employee/trabajadorautocompletar/{searching}', 'EmployeeController@trabajadorautocompletar')->name('employee.trabajadorautocompletar');

    // -- RUTAS ERICK
    Route::get('facturas/facturasautocompletar/{searching}', 'FacturacionController@facturasautocompletar')->name('facturacion.facturasautocompletar');
    
    // ------------------------------------------------------------------ 
    Route::get('employee/mixtoautocompletar/{searching}', 'EmployeeController@mixtoautocompletar')->name('employee.mixtoautocompletar');


    Route::post('usuario/buscar', 'UsuarioController@buscar')->name('usuario.buscar');
    Route::get('usuario/eliminar/{id}/{listarluego}', 'UsuarioController@eliminar')->name('usuario.eliminar');
    Route::resource('usuario', 'UsuarioController', array('except' => array('show')));

    /* DISTRIBUIDORA */
    Route::post('distribuidora/buscar', 'DistribuidoraController@buscar')->name('distribuidora.buscar');
    Route::get('distribuidora/eliminar/{id}/{listarluego}', 'DistribuidoraController@eliminar')->name('distribuidora.eliminar');
    Route::resource('distribuidora', 'DistribuidoraController', array('except' => array('show')));

    /* LABORATORIO */
    Route::post('laboratorio/buscar', 'LaboratorioController@buscar')->name('laboratorio.buscar');
    Route::get('laboratorio/eliminar/{id}/{listarluego}', 'LaboratorioController@eliminar')->name('laboratorio.eliminar');
    Route::get('laboratorio/crearsimple', 'LaboratorioController@crearsimple')->name('laboratorio.crearsimple');
    Route::post('laboratorio/guardarsimple', 'LaboratorioController@guardarsimple')->name('laboratorio.guardarsimple');
    Route::get('laboratorio/autocompletarlaboratorio/{searching}', 'LaboratorioController@autocompletarlaboratorio')->name('laboratorio.autocompletarlaboratorio');
    Route::resource('laboratorio', 'LaboratorioController', array('except' => array('show')));

    /* CATEGORIA */
    Route::post('categoria/buscar', 'CategoriaController@buscar')->name('categoria.buscar');
    Route::get('categoria/eliminar/{id}/{listarluego}', 'CategoriaController@eliminar')->name('categoria.eliminar');
    Route::get('categoria/crearsimple', 'CategoriaController@crearsimple')->name('categoria.crearsimple');
    Route::post('categoria/guardarsimple', 'CategoriaController@guardarsimple')->name('categoria.guardarsimple');
    Route::get('categoria/autocompletarcategoria/{searching}', 'CategoriaController@autocompletarcategoria')->name('categoria.autocompletarcategoria');
    Route::resource('categoria', 'CategoriaController', array('except' => array('show')));

    // ------------- ERICK -------------------
    // REPORTES PARA  EL AREA DE SISTEMAS
    // REPORTE DE EGRESOS

    Route::post('reportegreso/buscar', 'ReporteSistemasEgresoController@buscar')->name('reportegreso.buscar');
    Route::resource('reportegreso', 'ReporteSistemasEgresoController', array('except' => array('show')));
    Route::get('reportegreso/excel', 'ReporteSistemasEgresoController@excel')->name('reportegreso.excel');
    Route::get('reportegreso/excel02', 'ReporteSistemasEgresoController@excel02')->name('reportegreso.excel02');
    Route::get('reportegreso/excel03', 'ReporteSistemasEgresoController@excel03')->name('reportegreso.excel03');


    /* *********************************************/
    // REPORTE FARMACIA - GERENCIA 
    // --------------------------------------------*/

    Route::resource('reportefarmacia','ReporteFarmaciaGerenciaController', array('except' => array('show')));
    Route::post('reportefarmacia/buscar', 'ReporteFarmaciaGerenciaController@buscar')->name('reportefarmaciagerencia.buscar');
    Route::get('reportefarmacia/excel', 'ReporteFarmaciaGerenciaController@excel')->name('reportefarmaciagerencia.excel');
    Route::get('reportefarmacia/excel02', 'ReporteFarmaciaGerenciaController@excel02')->name('reportefarmaciagerencia.excel02');
    Route::get('reportefarmacia/excel03', 'ReporteFarmaciaGerenciaController@excel03')->name('reportefarmaciagerencia.excel03');
    Route::get('reportefarmacia/excel04', 'ReporteFarmaciaGerenciaController@excel04')->name('reportefarmaciagerencia.excel04');
    Route::get('reportefarmacia/excel05', 'ReporteFarmaciaGerenciaController@excel05')->name('reportefarmaciagerencia.excel05');

    /* PAGO CIRUGIA */
    Route::post('pagoscirugia/buscar', 'PagosCirugiaController@buscar')->name('pagoscirugia.buscar');
  
    Route::get('pagoscirugia/confirmar/{id}/{listarluego}', 'PagosCirugiaController@confirmar')->name('pagoscirugia.confirmar');
    Route::post('pagoscirugia/confirmarcajacirugia', 'PagosCirugiaController@confirmarCaja')->name('pagoscirugia.confirmarCaja');
   
    Route::get('pagoscirugia/pagar/{id}/{listarluego}', 'PagosCirugiaController@pagar')->name('pagoscirugia.pagar');
    Route::post('pagoscirugia/confirmarpago/{id}', 'PagosCirugiaController@confirmarPago')->name('pagoscirugia.confirmarPago');
   

    Route::get('pagoscirugia/eliminar/{id}/{listarluego}', 'PagosCirugiaController@eliminar')->name('pagoscirugia.eliminar');
    Route::resource('pagoscirugia', 'PagosCirugiaController');
   
    // Route::post('pagoscirugia/cargado', 'PagosCirugiaController@cargado')->name('pagoscirugia.cargado');
    Route::post('pagoscirugia/seleccionardetalles', 'PagosCirugiaController@seleccionardetalles')->name('pagoscirugia.seleccionardetalles');
    
    Route::get('pagoscirugiapendiente/excel', 'PagosCirugiaController@excel')->name('pagoscirugia.excel');


    
    // MODULO DE LABORATORIO - BRENDA

    Route::post('modlaboratorio/buscar', 'ModLaboratorioController@buscar')->name('modLaboratorio.buscar');
    
    Route::get('modlaboratorio/excel2', 'ModLaboratorioController@excel2')->name('modLaboratorio.excel2');

    Route::resource('modlaboratorio', 'ModLaboratorioController', array('except' => array('show')));

    Route::get('modlaboratorio/eliminar/{id}/{listarluego}', 'ModLaboratorioController@eliminar')->name('modLaboratorio.eliminar');
    Route::post('modlaboratorio/buscarexamen', 'ModLaboratorioController@buscarexamen')->name('modLaboratorio.buscarexamen');
    Route::post('modlaboratorio/seleccionarexamen', 'ModLaboratorioController@seleccionarexamen')->name('modLaboratorio.seleccionarexamen');
    Route::post('modlaboratorio/agregarDetalle', 'ModLaboratorioController@agregarDetalle')->name('modLaboratorio.agregarDetalle');
    

    //----------------------------------


    /* UNIDAD */
    Route::post('unidad/buscar', 'UnidadController@buscar')->name('unidad.buscar');
    Route::get('unidad/eliminar/{id}/{listarluego}', 'UnidadController@eliminar')->name('unidad.eliminar');
    Route::resource('unidad', 'UnidadController', array('except' => array('show')));

    /* TRAMA */
    Route::get('tramag/generar', 'TramaController@generar')->name('trama.generar');
    Route::get('tramag/nueva', 'TramaController@nueva')->name('trama.nueva');
    Route::get('tramag/buscar', 'TramaController@listarD')->name('trama.buscar');
    Route::get('tramag/listarD', 'TramaController@listarD')->name('trama.listarD');
    Route::resource('tramag', 'TramaController', array('except' => array('show')));

    Route::get('tramagcop/generar', 'TramaControllerCopago@generar')->name('tramacop.generar');
    Route::get('tramagcop/nueva', 'TramaControllerCopago@nueva')->name('tramacop.nueva');
    Route::get('tramagcop/buscar', 'TramaControllerCopago@listarD')->name('tramacop.buscar');
    Route::get('tramagcop/listarD', 'TramaControllerCopago@listarD')->name('tramacop.listarD');
    Route::resource('tramagcop', 'TramaControllerCopago', array('except' => array('show')));

    /* PRINCIPIO ACTIVO */
    Route::post('principioactivo/buscar', 'PrincipioactivoController@buscar')->name('principioactivo.buscar');
    Route::get('principioactivo/eliminar/{id}/{listarluego}', 'PrincipioactivoController@eliminar')->name('principioactivo.eliminar');
    Route::get('principioactivo/indexsimple', 'PrincipioactivoController@indexsimple')->name('principioactivo.indexsimple');
    Route::post('principioactivo/buscarsimple', 'PrincipioactivoController@buscarsimple')->name('principioactivo.buscarsimple');
    Route::get('principioactivo/crearsimple', 'PrincipioactivoController@crearsimple')->name('principioactivo.crearsimple');
    Route::post('principioactivo/guardarsimple', 'PrincipioactivoController@guardarsimple')->name('principioactivo.guardarsimple');
    Route::post('principioactivo/agregarprincipio', 'PrincipioactivoController@agregarprincipio')->name('principioactivo.agregarprincipio');
    Route::post('principioactivo/quitarprincipio', 'PrincipioactivoController@quitarprincipio')->name('principioactivo.quitarprincipio');
    Route::resource('principioactivo', 'PrincipioactivoController', array('except' => array('show')));


    /* ORIGEN */
        Route::post('origen/buscar', 'OrigenController@buscar')->name('origen.buscar');
        Route::get('origen/eliminar/{id}/{listarluego}', 'OrigenController@eliminar')->name('origen.eliminar');
        Route::get('origen/crearsimple', 'OrigenController@crearsimple')->name('origen.crearsimple');
        Route::post('origen/guardarsimple', 'OrigenController@guardarsimple')->name('origen.guardarsimple');
        Route::get('origen/autocompletarorigen/{searching}', 'OrigenController@autocompletarorigen')->name('origen.autocompletarorigen');
        Route::resource('origen', 'OrigenController', array('except' => array('show')));

    /* ANAQUEL */
    Route::post('anaquel/buscar', 'AnaquelController@buscar')->name('anaquel.buscar');
    Route::get('anaquel/eliminar/{id}/{listarluego}', 'AnaquelController@eliminar')->name('anaquel.eliminar');
    Route::get('anaquel/crearsimple', 'AnaquelController@crearsimple')->name('anaquel.crearsimple');
    Route::post('anaquel/guardarsimple', 'AnaquelController@guardarsimple')->name('anaquel.guardarsimple');
    Route::get('anaquel/autocompletaranaquel/{searching}', 'AnaquelController@autocompletaranaquel')->name('anaquel.autocompletaranaquel');
    Route::resource('anaquel', 'AnaquelController', array('except' => array('show')));



    /* ESPECIALIDAD FARMACIA */
        Route::post('especialidadfarmacia/buscar', 'EspecialidadfarmaciaController@buscar')->name('especialidadfarmacia.buscar');
        Route::get('especialidadfarmacia/eliminar/{id}/{listarluego}', 'EspecialidadfarmaciaController@eliminar')->name('especialidadfarmacia.eliminar');
        Route::get('especialidadfarmacia/crearsimple', 'EspecialidadfarmaciaController@crearsimple')->name('especialidadfarmacia.crearsimple');
        Route::post('especialidadfarmacia/guardarsimple', 'EspecialidadfarmaciaController@guardarsimple')->name('especialidadfarmacia.guardarsimple');
        Route::get('especialidadfarmacia/autocompletarespecialidadfarmacia/{searching}', 'EspecialidadfarmaciaController@autocompletarespecialidadfarmacia')->name('especialidadfarmacia.autocompletarespecialidadfarmacia');
        Route::resource('especialidadfarmacia', 'EspecialidadfarmaciaController', array('except' => array('show')));

    /* PRESENTACION */
        Route::post('presentacion/buscar', 'PresentacionController@buscar')->name('presentacion.buscar');
        Route::get('presentacion/eliminar/{id}/{listarluego}', 'PresentacionController@eliminar')->name('presentacion.eliminar');
        Route::get('presentacion/crearsimple', 'PresentacionController@crearsimple')->name('presentacion.crearsimple');
        Route::post('presentacion/guardarsimple', 'PresentacionController@guardarsimple')->name('presentacion.guardarsimple');
        Route::get('presentacion/autocompletarpresentacion/{searching}', 'PresentacionController@autocompletarpresentacion')->name('presentacion.autocompletarpresentacion');
        Route::resource('presentacion', 'PresentacionController', array('except' => array('show')));

    /* PRODUCTO */
    Route::post('producto/buscar', 'ProductoController@buscar')->name('producto.buscar');
    Route::get('producto/eliminar/{id}/{listarluego}', 'ProductoController@eliminar')->name('producto.eliminar');
    Route::get('producto/indexbuscarproducto', 'ProductoController@indexbuscarproducto')->name('producto.indexbuscarproducto');
    Route::post('producto/buscarproducto', 'ProductoController@buscarproducto')->name('producto.buscarproducto');
    Route::post('producto/cambiarOrigen', 'ProductoController@cambiarOrigen')->name('producto.cambiarOrigen');
    Route::post('producto/cambiarAnaquel', 'ProductoController@cambiarAnaquel')->name('producto.cambiarAnaquel');
    Route::resource('producto', 'ProductoController', array('except' => array('show')));
    
    
    Route::get('producto/excel', 'ProductoController@excel')->name('producto.excel');

    /* FORMA FARMACEUTICA */
    Route::resource('forma', 'FormaController', array('except' => array('show')));
    Route::post('forma/buscar', 'FormaController@buscar')->name('forma.buscar');
    Route::get('forma/eliminar/{id}/{listarluego}', 'FormaController@eliminar')->name('forma.eliminar');
     
    Route::get('forma/crearsimple', 'FormaController@crearsimple')->name('forma.crearsimple');
    Route::post('forma/guardarsimple', 'FormaController@guardarsimple')->name('forma.guardarsimple');
    Route::get('forma/autocompletarforma/{searching}', 'FormaController@autocompletarforma')->name('forma.autocompletarforma');
     

      /* CONCENTRACION */ 
    Route::get('concentracion/autocompletarconcentracion/{searching}', 'ProductoController@autocompletarconcentracion')->name('concentracion.autocompletarconcentracion');
      

    /* CONDICION DE ALMACENAMIENTO */

    Route::resource('condicionAlmacenamiento', 'CondicionAlmacenamientoController', array('except' => array('show')));
    Route::resource('condicionalmacenamiento', 'CondicionAlmacenamientoController', array('except' => array('show')));
 
    Route::post('condicionAlmacenamiento/buscar', 'CondicionAlmacenamientoController@buscar')->name('condicionAlmacenamiento.buscar');
    Route::post('condicionalmacenamiento/buscar', 'CondicionAlmacenamientoController@buscar')->name('condicionAlmacenamiento.buscar');
    
    Route::get('condicionAlmacenamiento/eliminar/{id}/{listarluego}', 'CondicionAlmacenamientoController@eliminar')->name('condicionAlmacenamiento.eliminar');


    Route::get('condicionAlmacenamiento/crearsimple', 'CondicionAlmacenamientoController@crearsimple')->name('condicionAlmacenamiento.crearsimple');
    Route::post('condicionalmacenamiento/guardarsimple', 'CondicionAlmacenamientoController@guardarsimple')->name('condicionAlmacenamiento.guardarsimple');
    Route::get('condicionAlmacenamiento/autocompletarcondicion/{searching}', 'CondicionAlmacenamientoController@autocompletarcondicion')->name('condicion.autocompletarcondicion');
    
    // Route::delete('condicionalmacenamiento/{id}', 'CondicionAlmacenamientoController@destroy')->name('condicionAlmacenamiento.destroy');
      


    /* ESPECIALIDAD */
    Route::post('especialidad/buscar', 'EspecialidadController@buscar')->name('especialidad.buscar');
    Route::get('especialidad/eliminar/{id}/{listarluego}', 'EspecialidadController@eliminar')->name('especialidad.eliminar');
    Route::resource('especialidad', 'EspecialidadController', array('except' => array('show')));

     /* COMPRA */
     Route::get('compra/create2', 'CompraController@create2')->name('compra.create2');
    Route::post('compra/store2', 'CompraController@store2')->name('compra.store2');
    Route::post('compra/buscar', 'CompraController@buscar')->name('compra.buscar');
    Route::get('compra/eliminar/{id}/{listarluego}', 'CompraController@eliminar')->name('compra.eliminar');
    Route::get('compra/buscarproducto', array('as' => 'compra.buscarproducto', 'uses' => 'CompraController@buscarproducto'));
    Route::post('compra/listarproducto', array('as' => 'compra.listarproducto', 'uses' => 'CompraController@listarproducto'));
    Route::post('compra/agregarcarritocompra', array('as' => 'compra.agregarcarritocompra', 'uses' => 'CompraController@agregarcarritocompra'));
    Route::post('compra/quitarcarritocompra', array('as' => 'compra.quitarcarritocompra', 'uses' => 'CompraController@quitarcarritocompra'));
    Route::post('compra/calculartotal', array('as' => 'compra.calculartotal', 'uses' => 'CompraController@calculartotal'));
    Route::post('compra/generarcreditos', array('as' => 'compra.generarcreditos', 'uses' => 'CompraController@generarcreditos'));
    Route::post('compra/comprobarproducto', array('as' => 'compra.comprobarproducto', 'uses' => 'CompraController@comprobarproducto'));
    Route::get('compra/pdfComprobante', 'CompraController@pdfComprobante')->name('compra.pdfComprobante');
    Route::resource('compra', 'CompraController');

    /* VENTA */
    Route::get('venta/create2', 'VentaController@create2')->name('venta.create2');
    Route::post('venta/store2', 'VentaController@store2')->name('venta.store2');
    Route::get('venta/clienteautocompletar/{nombre}', 'VentaController@clienteautocompletar')->name('venta.clienteautocompletar');
    Route::get('venta/empresaautocompletar/{nombre}', 'VentaController@empresaautocompletar')->name('venta.empresaautocompletar');

    Route::post('venta/buscar', 'VentaController@buscar')->name('venta.buscar');
    Route::get('venta/excel', 'VentaController@excel')->name('venta.excel');
    Route::get('venta/excel2', 'VentaController@excel2')->name('venta.excel2');
    Route::post('venta/buscar2', 'VentaController@buscar2')->name('venta.buscar2');
    Route::get('venta/eliminar/{id}/{listarluego}', 'VentaController@eliminar')->name('venta.eliminar');
    Route::get('venta/buscarproducto', array('as' => 'venta.buscarproducto', 'uses' => 'VentaController@buscarproducto'));
    Route::get('venta/buscarconvenio', array('as' => 'venta.buscarconvenio', 'uses' => 'VentaController@buscarconvenio'));
    Route::post('venta/listarproducto', array('as' => 'venta.listarproducto', 'uses' => 'VentaController@listarproducto'));
    Route::post('venta/listarconvenio', array('as' => 'venta.listarconvenio', 'uses' => 'VentaController@listarconvenio'));
    Route::post('venta/agregarcarritoventa', array('as' => 'venta.agregarcarritoventa', 'uses' => 'VentaController@agregarcarritoventa'));
    Route::post('venta/quitarcarritoventa', array('as' => 'venta.quitarcarritoventa', 'uses' => 'VentaController@quitarcarritoventa'));
    Route::post('venta/quitarcarritonotacredito', array('as' => 'venta.quitarcarritonotacredito', 'uses' => 'VentaController@quitarcarritonotacredito'));
    Route::post('venta/calculartotal', array('as' => 'venta.calculartotal', 'uses' => 'VentaController@calculartotal'));
    Route::post('venta/generarcreditos', array('as' => 'venta.generarcreditos', 'uses' => 'VentaController@generarcreditos'));
    Route::post('venta/comprobarproducto', array('as' => 'venta.comprobarproducto', 'uses' => 'VentaController@comprobarproducto'));
    Route::post('venta/generarNumero', array('as' => 'venta.generarNumero', 'uses' => 'VentaController@generarNumero'));
    Route::post('venta/agregarconvenio', array('as' => 'venta.agregarconvenio', 'uses' => 'VentaController@agregarconvenio'));
    Route::get('venta/busquedacliente', array('as' => 'venta.busquedacliente', 'uses' => 'VentaController@busquedacliente'));
    Route::post('venta/listarclientes', array('as' => 'venta.listarclientes', 'uses' => 'VentaController@listarclientes'));
    Route::post('venta/clienteid', array('as' => 'venta.clienteid', 'uses' => 'VentaController@clienteid'));
    Route::post('venta/buscandoproducto', array('as' => 'venta.buscandoproducto', 'uses' => 'VentaController@buscandoproducto'));
    Route::post('venta/buscandoproducto2', array('as' => 'venta.buscandoproducto2', 'uses' => 'VentaController@buscandoproducto2'));
    Route::post('venta/consultaproducto', array('as' => 'venta.consultaproducto', 'uses' => 'VentaController@consultaproducto'));
    Route::post('venta/buscandoclientes', array('as' => 'venta.buscandoclientes', 'uses' => 'VentaController@buscandoclientes'));
    Route::get('venta/pdfComprobante', 'VentaController@pdfComprobante')->name('venta.pdfComprobante');
    Route::get('venta/pdfComprobante2', 'VentaController@pdfComprobante2')->name('venta.pdfComprobante2');
    Route::get('venta/pdfComprobante4', 'VentaController@pdfComprobante4')->name('venta.pdfComprobante4');
    Route::post('venta/buscandoconvenios', array('as' => 'venta.buscandoconvenios', 'uses' => 'VentaController@buscandoconvenios'));
    Route::get('venta/busquedaempresa', array('as' => 'venta.busquedaempresa', 'uses' => 'VentaController@busquedaempresa'));
    Route::post('venta/buscandoempresas', array('as' => 'venta.buscandoempresas', 'uses' => 'VentaController@buscandoempresas'));
    Route::post('venta/agregarempresa', array('as' => 'venta.agregarempresa', 'uses' => 'VentaController@agregarempresa'));
    Route::get('venta/indexempresa', array('as' => 'venta.indexempresa', 'uses' => 'VentaController@indexempresa'));
    Route::post('venta/guardarempresa', array('as' => 'venta.guardarempresa', 'uses' => 'VentaController@guardarempresa'));
    Route::post('venta/verificarruc', array('as' => 'venta.verificarruc', 'uses' => 'VentaController@verificarruc'));
    Route::get('venta/createpedido', array('as' => 'venta.createpedido', 'uses' => 'VentaController@createpedido'));
    Route::post('venta/storepedido', array('as' => 'venta.storepedido', 'uses' => 'VentaController@storepedido'));
    Route::get('venta/buscarpedido', array('as' => 'venta.buscarpedido', 'uses' => 'VentaController@buscarpedido'));
    Route::post('venta/listarpedido', array('as' => 'venta.listarpedido', 'uses' => 'VentaController@listarpedido'));
    Route::get('venta/createnotacredito/{id}/{listarluego}', 'VentaController@createnotacredito')->name('venta.createnotacredito');
    Route::post('venta/savenotacredito', 'VentaController@savenotacredito')->name('venta.savenotacredito');
    Route::post('venta/listarcarritonota', array('as' => 'venta.listarcarritonota', 'uses' => 'VentaController@listarcarritonota'));
    Route::get('venta/pagar/{id}/{listarluego}', 'VentaController@pagar')->name('venta.pagar');
    Route::post('venta/guardarpago/{id}', array('as' => 'venta.guardarpago', 'uses' => 'VentaController@guardarpago'));
    Route::get('venta/anulacion/{id}/{listarluego}', 'VentaController@anulacion')->name('venta.anulacion');
    Route::post('venta/anular/{id}', 'VentaController@anular')->name('venta.anular');
    Route::get('venta/creatependientepasado', array('as' => 'venta.creatependientepasado', 'uses' => 'VentaController@creatependientepasado'));
    Route::post('venta/storependientepasado', array('as' => 'venta.storependientepasado', 'uses' => 'VentaController@storependientepasado'));
    Route::resource('venta', 'VentaController');
    Route::get('venta2', 'VentaController@index2')->name('venta.index2');
    Route::post('venta/procesar', 'VentaController@procesar')->name('venta.procesar');


    /* MOVIMIENTO ALMACEN */
    Route::post('movimientoalmacen/buscar', 'MovimientoalmacenController@buscar')->name('movimientoalmacen.buscar');
    Route::get('movimientoalmacen/eliminar/{id}/{listarluego}', 'MovimientoalmacenController@eliminar')->name('movimientoalmacen.eliminar');
    Route::get('movimientoalmacen/buscarproducto', array('as' => 'movimientoalmacen.buscarproducto', 'uses' => 'MovimientoalmacenController@buscarproducto'));
    Route::post('movimientoalmacen/listarproducto', array('as' => 'movimientoalmacen.listarproducto', 'uses' => 'MovimientoalmacenController@listarproducto'));
    Route::post('movimientoalmacen/agregarcarritomovimientoalmacen', array('as' => 'movimientoalmacen.agregarcarritomovimientoalmacen', 'uses' => 'MovimientoalmacenController@agregarcarritomovimientoalmacen'));
    Route::post('movimientoalmacen/quitarcarritomovimientoalmacen', array('as' => 'movimientoalmacen.quitarcarritomovimientoalmacen', 'uses' => 'MovimientoalmacenController@quitarcarritomovimientoalmacen'));
    Route::post('movimientoalmacen/calculartotal', array('as' => 'movimientoalmacen.calculartotal', 'uses' => 'MovimientoalmacenController@calculartotal'));
    Route::post('movimientoalmacen/generarcreditos', array('as' => 'movimientoalmacen.generarcreditos', 'uses' => 'MovimientoalmacenController@generarcreditos'));
    Route::resource('movimientoalmacen', 'MovimientoalmacenController');
    Route::get('movimientoalmacen/corregir/{listarluego}', 'MovimientoalmacenController@corregir')->name('movimientoalmacen.corregir');
    Route::post('movimientoalmacen/cuadrarstock', 'MovimientoalmacenController@cuadrarstock')->name('movimientoalmacen.cuadrarstock');
    Route::get('movimientoalmacen/pdfComprobante/{id}', 'MovimientoalmacenController@pdfComprobante')->name('movimientoalmacen.pdfComprobante');
    Route::post('movimientoalmacen/generarNumero', array('as' => 'movimientoalmacen.generarNumero', 'uses' => 'MovimientoalmacenController@generarNumero'));


    /* MEDICO */
    Route::post('medico/buscar', 'MedicoController@buscar')->name('medico.buscar');
    Route::get('medico/eliminar/{id}/{listarluego}', 'MedicoController@eliminar')->name('medico.eliminar');
    Route::resource('medico', 'MedicoController', array('except' => array('show')));
    Route::get('medico/lista', 'MedicoController@lista')->name('medico.lista');
    Route::post('medico/seleccionarMedico', 'MedicoController@seleccionarMedico')->name('medico.seleccionarMedico');
    Route::post('medico/horario', 'MedicoController@horario')->name('medico.horario');
    Route::get('medico/medicoautocompletar/{searching}', 'MedicoController@medicoautocompletar')->name('medico.medicoautocompletar');
    Route::get('medico/excel', 'MedicoController@excel')->name('medico.excel');
    Route::get('facturacion/susaludautocompletar/{searching}', 'FacturacionController@susaludautocompletar')->name('facturacion.susaludautocompletar');
    
    Route::get('facturacion/arreglarfacturas','FacturacionController@arreglarfacturas')->name('facturacion.arreglarfacturas');
    /* GUARDIA */
    Route::post('guardia/buscar', 'GuardiaController@buscar')->name('guardia.buscar');
    Route::get('guardia/eliminar/{id}/{listarluego}', 'GuardiaController@eliminar')->name('guardia.eliminar');
    Route::resource('guardia', 'GuardiaController', array('except' => array('show')));

    /* HORARIO */
    Route::post('horario/buscar', 'HorarioController@buscar')->name('horario.buscar');
    Route::get('horario/eliminar/{id}/{listarluego}', 'HorarioController@eliminar')->name('horario.eliminar');
    Route::resource('horario', 'HorarioController', array('except' => array('show')));

    /* CONVENIO */
    Route::post('convenio/buscar', 'ConvenioController@buscar')->name('convenio.buscar');
    Route::get('convenio/eliminar/{id}/{listarluego}', 'ConvenioController@eliminar')->name('convenio.eliminar');
    Route::resource('convenio', 'ConvenioController', array('except' => array('show')));
    Route::get('convenio/convenioautocompletar/{searching}', 'ConvenioController@medicoautocompletar')->name('convenio.convenioautocompletar');

    /* PROVEEDOR */
    Route::post('proveedor/buscar', 'ProveedorController@buscar')->name('proveedor.buscar');
    Route::get('proveedor/eliminar/{id}/{listarluego}', 'ProveedorController@eliminar')->name('proveedor.eliminar');
    Route::get('proveedor/crearsimple', 'ProveedorController@crearsimple')->name('proveedor.crearsimple');
    Route::post('proveedor/guardarsimple', 'ProveedorController@guardarsimple')->name('proveedor.guardarsimple');
    Route::resource('proveedor', 'ProveedorController', array('except' => array('show')));

    /*PERSON*/
    Route::post('person/search', 'PersonController@search')->name('person.search');
    Route::get('person/employeesautocompleting/{searching}', 'PersonController@employeesautocompleting')->name('person.employeesautocompleting');
    Route::get('person/providersautocompleting/{searching}', 'PersonController@providersautocompleting')->name('person.providersautocompleting');
    Route::get('person/customersautocompleting/{searching}', 'PersonController@customersautocompleting')->name('person.customersautocompleting');
    Route::get('person/doctorautocompleting/{searching}', 'PersonController@doctorautocompleting')->name('person.doctorautocompleting');

    /* HISTORIA */
    Route::post('historia/buscar', 'HistoriaController@buscar')->name('historia.buscar');
    Route::get('historia/buscaProv/{departamento}', 'HistoriaController@buscaProv')->name('historia.buscaProv');
    Route::get('historia/buscaDist/{provncia}', 'HistoriaController@buscaDist')->name('historia.buscaDist');
    Route::get('historia/eliminar/{id}/{listarluego}', 'HistoriaController@eliminar')->name('historia.eliminar');
    Route::get('historia/fallecido/{id}/{listarluego}', 'HistoriaController@fallecido')->name('historia.fallecido');
    Route::post('historia/guardarfallecido', 'HistoriaController@guardarfallecido')->name('historia.guardarfallecido');
    Route::resource('historia', 'HistoriaController', array('except' => array('show')));
    Route::post('historia/validarDNI', 'HistoriaController@validarDNI')->name('historia.validarDNI');
    Route::get('historia/personautocompletar/{searching}', 'HistoriaController@personautocompletar')->name('historia.personautocompletar');
    Route::get('historia/historiaautocompletar/{searching}', 'HistoriaController@historiaautocompletar')->name('historia.historiaautocompletar');
    Route::get('historia/pdfseguimiento', 'HistoriaController@pdfSeguimiento')->name('historia.pdfSeguimiento');
    Route::get('historia/pdfhistoria', 'HistoriaController@pdfHistoria')->name('historia.pdfHistoria');

    /* ACCIDENTES DE TRÁNSITO */
    Route::post('atransito/buscar', 'aTransitoController@buscar')->name('atransito.buscar');
    
    Route::get('atransito/eliminar/{id}/{listarluego}', 'aTransitoController@eliminar')->name('atransito.eliminar');
    Route::resource('atransito', 'aTransitoController', array('except' => array('show')));
    Route::post('atransito/validarDNI', 'aTransitoController@validarDNI')->name('atransito.validarDNI');
    Route::get('atransito/personautocompletar/{searching}', 'aTransitoController@personautocompletar')->name('atransito.personautocompletar');
    Route::get('atransito/pdfseguimiento', 'aTransitoController@pdfSeguimiento')->name('atransito.pdfSeguimiento');
    Route::get('atransito/excel', 'aTransitoController@excel')->name('atransito.excel');
    Route::get('atransito/pdfDocumento/{id}', 'aTransitoController@pdfDocumento')->name('atransito.pdfDocumento');

    /* CITA */
    Route::post('cita/buscar', 'CitaController@buscar')->name('cita.buscar');
    Route::get('cita/buscarboleta', 'CitaController@buscarboleta')->name('cita.buscarboleta');
    Route::get('cita/buscarcita', 'CitaController@buscarcita')->name('cita.buscarcita');
    Route::get('cita/marcador', 'CitaController@marcador')->name('cita.marcador');
    Route::get('cita/pdflistar', 'CitaController@pdfListar')->name('cita.pdfListar');
    Route::get('cita/eliminar/{id}/{listarluego}', 'CitaController@eliminar')->name('cita.eliminar');
    Route::resource('cita', 'CitaController', array('except' => array('show')));
    Route::post('cita/cargarCitaMedico', 'CitaController@cargarCitaMedico')->name('cita.cargarCitaMedico');
    Route::get('cita/anular/{id}/{listarluego}', 'CitaController@anular')->name('cita.anular');
    Route::post('cita/anula/{id}', 'CitaController@anula')->name('cita.anula');
    Route::post('cita/destroy02/{id}', 'CitaController@destroy02')->name('cita.destroy02');
    
    Route::get('cita/excel', 'CitaController@excel')->name('cita.excel');

    /* TIPOSERVICIO */
    Route::post('tiposervicio/buscar', 'TiposervicioController@buscar')->name('tiposervicio.buscar');
    Route::get('tiposervicio/eliminar/{id}/{listarluego}', 'TiposervicioController@eliminar')->name('tiposervicio.eliminar');
    Route::resource('tiposervicio', 'TiposervicioController', array('except' => array('show')));

    /* SERVICIO */
    Route::post('servicio/buscar', 'ServicioController@buscar')->name('servicio.buscar');
    Route::get('servicio/eliminar/{id}/{listarluego}', 'ServicioController@eliminar')->name('servicio.eliminar');
    Route::resource('servicio', 'ServicioController', array('except' => array('show')));
    Route::get('servicio/excel', 'ServicioController@excel')->name('servicio.excel');

    /* PLAN */
    Route::post('plan/buscar', 'PlanController@buscar')->name('plan.buscar');
    Route::get('plan/eliminar/{id}/{listarluego}', 'PlanController@eliminar')->name('plan.eliminar');
    Route::resource('plan', 'PlanController', array('except' => array('show')));
    Route::get('plan/planautocompletar/{searching}', 'PlanController@planautocompletar')->name('plan.planautocompletar');
    Route::post('plan/buscarfactor', 'PlanController@buscarfactor')->name('plan.buscarfactor');

    /* TICKET */
    Route::post('ticket/buscar', 'TicketController@buscar')->name('ticket.buscar');
    Route::get('ticket/eliminar/{id}/{listarluego}', 'TicketController@eliminar')->name('ticket.eliminar');
    Route::resource('ticket', 'TicketController', array('except' => array('show')));
    Route::post('ticket/buscarservicio', 'TicketController@buscarservicio')->name('ticket.buscarsetvicio');
    Route::post('ticket/seleccionarservicio', 'TicketController@seleccionarservicio')->name('ticket.seleccionarsetvicio');
    Route::post('ticket/generarNumero', 'TicketController@generarNumero')->name('ticket.generarNumero');
    Route::get('ticket/personrucautocompletar/{searching}', 'TicketController@personrucautocompletar')->name('ticket.personrucautocompletar');
    Route::get('ticket/personrazonautocompletar/{searching}', 'TicketController@personrazonautocompletar')->name('ticket.personrazonautocompletar');
    Route::get('ticket/pdfComprobante', 'TicketController@pdfComprobante')->name('ticket.pdfComprobante');
    Route::get('ticket/pdfComprobante2', 'TicketController@pdfComprobante2')->name('ticket.pdfComprobante2');
    Route::get('ticket/pdfComprobante3', 'TicketController@pdfComprobante3')->name('ticket.pdfComprobante3');
    Route::get('ticket/pdfPrefactura', 'TicketController@pdfPrefactura')->name('ticket.pdfPrefactura');
    Route::get('ticket/anular/{id}/{listarluego}', 'TicketController@anular')->name('ticket.anular');
    Route::get('ticket/editarresponsable/{id}/{listarluego}', 'TicketController@editarresponsable')->name('ticket.editarresponsable');
    Route::post('ticket/anulacion', 'TicketController@anulacion')->name('ticket.anulacion');
    Route::post('ticket/guardarresponsable', 'TicketController@guardarresponsable')->name('ticket.guardarresponsable');
    Route::post('ticket/agregardetalle', 'TicketController@agregardetalle')->name('ticket.agregardetalle');
    Route::post('ticket/agregarhojacosto', 'TicketController@agregarhojacosto')->name('ticket.agregarhojacosto');

    /* HOJA COSTO */
    Route::post('hojacosto/buscar', 'HojacostoController@buscar')->name('hojacosto.buscar');
    Route::get('hojacosto/eliminar/{id}/{listarluego}', 'HojacostoController@eliminar')->name('hojacosto.eliminar');
    Route::resource('hojacosto', 'HojacostoController', array('except' => array('show')));
    Route::post('hojacosto/buscarservicio', 'HojacostoController@buscarservicio')->name('hojacosto.buscarsetvicio');
    Route::post('hojacosto/seleccionarservicio', 'HojacostoController@seleccionarservicio')->name('hojacosto.seleccionarsetvicio');
    Route::get('hojacosto/hospitalizadoautocompletar/{searching}', 'HojacostoController@hospitalizadoautocompletar')->name('hojacosto.hospitalizadoautocompletar');
    Route::post('hojacosto/agregardetalle', 'HojacostoController@agregardetalle')->name('hojacosto.agregardetalle');
    Route::get('hojacosto/pdfHojacosto', 'HojacostoController@pdfHojacosto')->name('hojacosto.pdfHojacosto');


    /* CUENTAS POR PAGAR */
    Route::post('cuentasporpagar/buscar', 'CuentasporpagarController@buscar')->name('cuentasporpagar.buscar');
    Route::get('cuentasporpagar/eliminar/{id}/{listarluego}', 'CuentasporpagarController@eliminar')->name('cuentasporpagar.eliminar');
    Route::resource('cuentasporpagar', 'CuentasporpagarController', array('except' => array('show')));
    Route::get('cuentasporpagar/personautocompletar/{searching}', 'CuentasporpagarController@personautocompletar')->name('cuentasporpagar.personautocompletar');
    Route::get('cuentasporpagar/excel', 'CuentasporpagarController@excel')->name('cuentasporpagar.excel');
    Route::get('cuentasporpagar/pdfComprobante', 'CuentasporpagarController@pdfComprobante')->name('cuentasporpagar.pdfComprobante');
    Route::get('cuentasporpagar/vencimiento', 'CuentasporpagarController@vencimiento')->name('cuentasporpagar.vencimiento');
    /*Route::post('analisis/buscarpagos', 'AnalisisController@buscarpagos')->name('analisis.buscarpagos');
    
    /* REPORTE PAGO CONVENIO*/
    Route::resource('reporteconveniosistemas', 'ReporteconveniosistemasController', array('except' => array('show')));
    Route::post('reporteconveniosistemas/buscar', 'ReporteconveniosistemasController@buscar')->name('reporteconveniosistemas.buscar');
    Route::get('reporteconveniosistemas/excel', 'ReporteconveniosistemasController@excel')->name('reporteconveniosistemas.excel');

    /* CUENTAS MEDICO */
    Route::post('cuentasmedico/buscar', 'CuentasmedicoController@buscar')->name('cuentasmedico.buscar');
    Route::get('cuentasmedico/buscarajax', 'CuentasmedicoController@buscarajax')->name('cuentasmedico.buscarajax');
    Route::get('cuentasmedico/eliminar/{id}/{listarluego}', 'CuentasmedicoController@eliminar')->name('cuentasmedico.eliminar');
    Route::resource('cuentasmedico', 'CuentasmedicoController', array('except' => array('show')));
    Route::get('cuentasmedico/personautocompletar/{searching}', 'CuentasmedicoController@personautocompletar')->name('cuentasmedico.personautocompletar');
    Route::get('cuentasmedico/excel', 'CuentasmedicoController@excel')->name('cuentasmedico.excel');
    Route::get('cuentasmedico/retencion/{id}/{listarluego}', 'CuentasmedicoController@retencion')->name('cuentasmedico.retencion');
    Route::post('cuentasmedico/confirmarretencion/{id}', 'CuentasmedicoController@confirmarretencion')->name('cuentasmedico.confirmarretencion');
    /* ANALISIS */
    Route::post('analisis/buscar', 'AnalisisController@buscar')->name('analisis.buscar');
    Route::get('analisis/eliminar/{id}/{listarluego}', 'AnalisisController@eliminar')->name('analisis.eliminar');
    Route::resource('analisis', 'AnalisisController', array('except' => array('show')));
    Route::post('analisis/buscarexamen', 'AnalisisController@buscarexamen')->name('analisis.buscarexamen');
    Route::post('analisis/seleccionarexamen', 'AnalisisController@seleccionarexamen')->name('analisis.seleccionarexamen');
    Route::post('analisis/buscarpagos', 'AnalisisController@buscarpagos')->name('analisis.buscarpagos');
    Route::get('analisis/pdfAnalisis', 'AnalisisController@pdfAnalisis')->name('analisis.pdfAnalisis');
    Route::post('analisis/agregarDetalle', 'AnalisisController@agregarDetalle')->name('analisis.agregarDetalle');

    /* FACTURACION */
    Route::post('facturacion/buscar', 'FacturacionController@buscar')->name('facturacion.buscar');
    Route::get('facturacion/excel', 'FacturacionController@excel')->name('facturacion.excel');
    Route::get('facturacion/eliminar/{id}/{listarluego}', 'FacturacionController@eliminar')->name('facturacion.eliminar');
    Route::get('facturacion/anular/{id}/{listarluego}', 'FacturacionController@anular')->name('facturacion.anular');
    Route::post('facturacion/anulacion/{id}', 'FacturacionController@anulacion')->name('facturacion.anulacion');
    Route::resource('facturacion', 'FacturacionController', array('except' => array('show')));
    Route::post('facturacion/buscarservicio', 'FacturacionController@buscarservicio')->name('facturacion.buscarsetvicio');
    Route::post('facturacion/buscarserviciosusalud', 'FacturacionController@buscarserviciosusalud')->name('facturacion.buscarserviciosusalud');
    
    Route::post('facturacion/seleccionarservicio', 'FacturacionController@seleccionarservicio')->name('facturacion.seleccionarsetvicio');
    Route::get('facturacion/cieautocompletar/{searching}', 'FacturacionController@cieautocompletar')->name('facturacion.cieautocompletar');
   Route::post('facturacion/generarNumero', 'FacturacionController@generarNumero')->name('facturacion.generarNumero');
    Route::post('facturacion/agregarDetallePrefactura', 'FacturacionController@agregarDetallePrefactura')->name('facturacion.agregarDetallePrefactura');
    Route::get('facturacion/pdfComprobante', 'FacturacionController@pdfComprobante')->name('facturacion.pdfComprobante');
    Route::get('facturacion/pdfComprobante2', 'FacturacionController@pdfComprobante2')->name('facturacion.pdfComprobante2');
    Route::post('facturacion/procesar', 'FacturacionController@procesar')->name('facturacion.procesar');
    Route::get('facturacion/pdfLiquidacion', 'FacturacionController@pdfLiquidacion')->name('facturacion.pdfLiquidacion');
    Route::get('facturacion/cartaGarantia', 'FacturacionController@cartaGarantia')->name('facturacion.cartaGarantia');

    // Route::get('facturacion/arreglarte', 'FacturacionController@arreglarte')->name('facturacion.cartaGarantia');


/*****************************************/
    /* CARTAS DE GARANTIA*/
    Route::post('cartasgarantia/buscar', 'CartasgarantiaController@buscar')->name('cartasgarantia.buscar');
    Route::post('cartasgarantia/buscarcarta', 'CartasgarantiaController@buscarcarta')->name('cartasgarantia.buscarcarta');
    Route::get('cartasgarantia/word', 'CartasgarantiaController@word')->name('cartasgarantia.word');
    Route::get('cartasgarantia/wordPlan', 'CartasgarantiaController@wordPlan')->name('cartasgarantia.wordPlan');
    Route::resource('cartasgarantia', 'CartasgarantiaController', array('except' => array('show')));
    Route::get('cartasgarantia/eliminar/{plan_id}/{numero}/{listarluego}', 'CartasgarantiaController@eliminar')->name('cartasgarantia.eliminar');


    /* FACTURACION PASADA*/
    Route::post('facturacionpasada/buscar', 'FacturacionpasadaController@buscar')->name('facturacionpasada.buscar');
    Route::get('facturacionpasada/eliminar/{id}/{listarluego}', 'FacturacionpasadaController@eliminar')->name('facturacionpasada.eliminar');
    Route::resource('facturacionpasada', 'FacturacionpasadaController', array('except' => array('show')));
    Route::post('facturacionpasada/buscarservicio', 'FacturacionpasadaController@buscarservicio')->name('facturacionpasada.buscarsetvicio');
    Route::post('facturacionpasada/seleccionarservicio', 'FacturacionpasadaController@seleccionarservicio')->name('facturacionpasada.seleccionarsetvicio');
    Route::get('facturacionpasada/cieautocompletar/{searching}', 'FacturacionpasadaController@cieautocompletar')->name('facturacionpasada.cieautocompletar');
   Route::post('facturacionpasada/generarNumero', 'FacturacionpasadaController@generarNumero')->name('facturacionpasada.generarNumero');
    Route::post('facturacionpasada/agregardetalle', 'FacturacionpasadaController@agregardetalle')->name('facturacionpasada.agregardetalle');
    Route::get('facturacionpasada/pdfComprobante', 'FacturacionpasadaController@pdfComprobante')->name('facturacionpasada.pdfComprobante');

    /* COBRANZA */
    Route::post('cobranza/buscar', 'CobranzaController@buscar')->name('cobranza.buscar');
    Route::get('cobranza/eliminar/{id}/{listarluego}', 'CobranzaController@eliminar')->name('cobranza.eliminar');
    Route::resource('cobranza', 'CobranzaController', array('except' => array('show')));
    Route::post('cobranza/buscardocumento', 'CobranzaController@buscardocumento')->name('cobranza.buscardocumento');
    Route::post('cobranza/seleccionardocumento', 'CobranzaController@seleccionardocumento')->name('cobranza.seleccionardocumento');
    Route::get('cobranza/excel', 'CobranzaController@excel')->name('cobranza.excel');
    Route::get('cobranza/excel02', 'CobranzaController@excel02')->name('cobranza.excel02');
    
    /*Route::post('facturacion/generarNumero', 'FacturacionController@generarNumero')->name('facturacion.generarNumero');
    Route::post('facturacion/agregardetalle', 'FacturacionController@agregardetalle')->name('facturacion.agregardetalle');
    Route::get('facturacion/pdfComprobante', 'FacturacionController@pdfComprobante')->name('facturacion.pdfComprobante');*/

    /* RETRAMITE */
    Route::post('retramite/buscar', 'RetramiteController@buscar')->name('retramite.buscar');
    Route::get('retramite/eliminar/{id}/{listarluego}', 'RetramiteController@eliminar')->name('retramite.eliminar');
    Route::resource('retramite', 'RetramiteController', array('except' => array('show')));
    Route::post('retramite/buscardocumento', 'RetramiteController@buscardocumento')->name('retramite.buscardocumento');
    Route::post('retramite/seleccionardocumento', 'RetramiteController@seleccionardocumento')->name('retramite.seleccionardocumento');
    Route::get('retramite/excel', 'RetramiteController@excel')->name('retramite.excel');

    /* PREFACTURA */
    Route::post('prefactura/buscar', 'PrefacturaController@buscar')->name('prefactura.buscar');
    Route::get('prefactura/eliminar/{id}/{listarluego}', 'PrefacturaController@eliminar')->name('prefactura.eliminar');
    Route::resource('prefactura', 'PrefacturaController', array('except' => array('show')));
    Route::post('prefactura/cargado', 'PrefacturaController@cargado')->name('prefactura.cargado');
    Route::post('prefactura/anulado', 'PrefacturaController@anulado')->name('prefactura.anulado');
    Route::post('prefactura/observacion', 'PrefacturaController@observacion')->name('prefactura.observacion');
    Route::get('prefactura/excel', 'PrefacturaController@excel')->name('prefactura.excel');
    Route::get('prefactura/excelDiario', 'PrefacturaController@excelDiario')->name('prefactura.excelDiario');
    Route::get('prefactura/excelUsuario', 'PrefacturaController@excelUsuario')->name('prefactura.excelUsuario');

    /* REPORTE PREFACTURA */
    Route::post('reporteprefactura/buscar', 'ReporteprefacturaController@buscar')->name('reporteprefactura.buscar');
    Route::resource('reporteprefactura', 'ReporteprefacturaController', array('except' => array('show')));
    Route::get('reporteprefactura/excel', 'ReporteprefacturaController@excel')->name('reporteprefactura.excel');
    
    /* REPORTE GERENCIA */
    Route::resource('reportegerencia', 'ReportegerenciaController', array('except' => array('show')));
    Route::get('reportegerencia/excelConsultaExterna', 'ReportegerenciaController@excelConsultaExterna')->name('reportegerencia.excelConsultaExterna');
    Route::get('reportegerencia/excelHospitalizacion', 'ReportegerenciaController@excelHospitalizacion')->name('reportegerencia.excelHospitalizacion');
    Route::get('reportegerencia/excelUCI', 'ReportegerenciaController@excelUCI')->name('reportegerencia.excelUCI');
    Route::get('reportegerencia/excelEmergencia', 'ReportegerenciaController@excelEmergencia')->name('reportegerencia.excelEmergencia');
    Route::get('reportegerencia/excelSOP', 'ReportegerenciaController@excelSOP')->name('reportegerencia.excelSOP');

    /* AREA */
    Route::post('area/buscar', 'AreaController@buscar')->name('area.buscar');
    Route::get('area/eliminar/{id}/{listarluego}', 'AreaController@eliminar')->name('area.eliminar');
    Route::resource('area', 'AreaController', array('except' => array('show')));

    /* SEGUIMIENTO */
    Route::post('seguimiento/buscar', 'SeguimientoController@buscar')->name('seguimiento.buscar');
    Route::get('seguimiento/rechazar/{id}/{listarluego}', 'SeguimientoController@rechazar')->name('seguimiento.rechazar');
    Route::get('seguimiento/aceptar/{id}/{listarluego}', 'SeguimientoController@aceptar')->name('seguimiento.aceptar');
    Route::post('seguimiento/respuestaaceptar/{id}', 'SeguimientoController@respuestaaceptar')->name('seguimiento.respuestaaceptar');
    Route::post('seguimiento/respuestarechazar/{id}', 'SeguimientoController@respuestarechazar')->name('seguimiento.respuestarechazar');
    Route::get('seguimiento/retornar/{id}/{listarluego}', 'SeguimientoController@retornar')->name('seguimiento.retornar');
    Route::post('seguimiento/respuestaretornar/{id}', 'SeguimientoController@respuestaretornar')->name('seguimiento.respuestaretornar');
    Route::post('seguimiento/solicitar', 'SeguimientoController@solicitar')->name('seguimiento.solicitar');
    Route::resource('seguimiento', 'SeguimientoController', array('except' => array('show')));

    /* CONCEPTOPAGO */
    Route::post('conceptopago/buscar', 'ConceptopagoController@buscar')->name('conceptopago.buscar');
    Route::get('conceptopago/eliminar/{id}/{listarluego}', 'ConceptopagoController@eliminar')->name('conceptopago.eliminar');
    Route::resource('conceptopago', 'ConceptopagoController', array('except' => array('show')));

    /* CAJA */
    Route::post('caja/buscar', 'CajaController@buscar')->name('caja.buscar');
    Route::post('caja/buscarcontrol', 'CajaController@buscarControl')->name('caja.buscarcontrol');
    Route::get('caja/eliminar/{id}/{listarluego}', 'CajaController@eliminar')->name('caja.eliminar');
    Route::resource('caja', 'CajaController', array('except' => array('show')));
    Route::resource('control', 'CajaController@control', array('except' => array('show')));
    Route::get('caja/apertura', 'CajaController@apertura')->name('caja.apertura');
    Route::post('caja/aperturar', 'CajaController@aperturar')->name('caja.aperturar');
    Route::get('caja/cierre', 'CajaController@cierre')->name('caja.cierre');
    Route::post('caja/cerrar', 'CajaController@cerrar')->name('caja.cerrar');
    Route::post('caja/generarConcepto', 'CajaController@generarConcepto')->name('caja.generarconcepto');
    Route::post('caja/generarNumero', 'CajaController@generarNumero')->name('caja.generarnumero');
    Route::post('caja/validarCajaTransferencia', 'CajaController@validarCajaTransferencia')->name('caja.validarCajaTransferencia');
    Route::get('caja/personautocompletar/{searching}', 'CajaController@personautocompletar')->name('caja.personautocompletar');
    Route::get('caja/acept/{id}/{listarluego}', 'CajaController@acept')->name('caja.acept');
    Route::post('caja/aceptar/{id}', 'CajaController@aceptar')->name('caja.aceptar');
    Route::get('caja/reject/{id}/{listarluego}', 'CajaController@reject')->name('caja.reject');
    Route::post('caja/rechazar/{id}', 'CajaController@rechazar')->name('caja.rechazar');
    Route::get('caja/pdfCierre', 'CajaController@pdfCierre')->name('caja.pdfCierre');
    Route::get('caja/pdfHonorario', 'CajaController@pdfHonorario')->name('caja.pdfHonorario');
    Route::get('caja/pdfHonorarioF', 'CajaController@pdfHonorarioF')->name('caja.pdfHonorarioF');
    Route::get('caja/pdfDetalleCierre', 'CajaController@pdfDetalleCierre')->name('caja.pdfDetalleCierre');
    Route::get('caja/pdfDetalleCierreF', 'CajaController@pdfDetalleCierreF')->name('caja.pdfDetalleCierreF');
    Route::get('caja/pdfDetalleCierreF02', 'CajaController@pdfDetalleCierreF02')->name('caja.pdfDetalleCierreF02');
    
    Route::get('caja/pdfRecibo', 'CajaController@pdfRecibo')->name('caja.pdfRecibo');
    Route::post('caja/venta', 'CajaController@venta')->name('caja.venta');
    Route::post('caja/ventapago', 'CajaController@ventapago')->name('caja.ventapago');
    Route::post('caja/ventasocio', 'CajaController@ventasocio')->name('caja.ventasocio');
    Route::post('caja/ventatarjeta', 'CajaController@ventatarjeta')->name('caja.ventatarjeta');
    Route::post('caja/ventaboleteo', 'CajaController@ventaboleteo')->name('caja.ventaboleteo');
    Route::get('caja/descarga', 'CajaController@descarga')->name('caja.descarga');
    Route::post('caja/listardescarga', 'CajaController@listardescarga')->name('caja.listardescarga');
    Route::post('caja/guardardescarga', 'CajaController@guardardescarga')->name('caja.guardardescarga');
    Route::post('caja/agregardescarga', 'CajaController@agregardescarga')->name('caja.agregardescarga');
    Route::post('caja/quitardescarga', 'CajaController@quitardescarga')->name('caja.quitardescarga');
    Route::get('caja/descargaadmision', 'CajaController@descargaadmision')->name('caja.descargaadmision');
    Route::post('caja/listardescargaadmision', 'CajaController@listardescargaadmision')->name('caja.listardescargaadmision');
    
    /* CAJA TESORERIA*/
    Route::post('cajatesoreria/buscar', 'CajatesoreriaController@buscar')->name('cajatesoreria.buscar');
    Route::get('cajatesoreria/eliminar/{id}/{listarluego}', 'CajatesoreriaController@eliminar')->name('cajatesoreria.eliminar');
    Route::resource('cajatesoreria', 'CajatesoreriaController', array('except' => array('show')));
    Route::get('cajatesoreria/apertura', 'CajatesoreriaController@apertura')->name('cajatesoreria.apertura');
    Route::post('cajatesoreria/aperturar', 'CajatesoreriaController@aperturar')->name('cajatesoreria.aperturar');
    Route::get('cajatesoreria/cierre', 'CajatesoreriaController@cierre')->name('cajatesoreria.cierre');
    Route::post('cajatesoreria/cerrar', 'CajatesoreriaController@cerrar')->name('cajatesoreria.cerrar');
    Route::post('cajatesoreria/generarConcepto', 'CajatesoreriaController@generarConcepto')->name('cajatesoreria.generarconcepto');
    Route::post('cajatesoreria/generarNumero', 'CajatesoreriaController@generarNumero')->name('cajatesoreria.generarnumero');
    Route::post('cajatesoreria/validarCajaTransferencia', 'CajatesoreriaController@validarCajaTransferencia')->name('cajatesoreria.validarCajaTransferencia');
    Route::get('cajatesoreria/personautocompletar/{searching}', 'CajatesoreriaController@personautocompletar')->name('cajatesoreria.personautocompletar');
    Route::get('cajatesoreria/acept/{id}/{listarluego}', 'CajatesoreriaController@acept')->name('cajatesoreria.acept');
    Route::post('cajatesoreria/aceptar/{id}', 'CajatesoreriaController@aceptar')->name('cajatesoreria.aceptar');
    Route::get('cajatesoreria/reject/{id}/{listarluego}', 'CajatesoreriaController@reject')->name('cajatesoreria.reject');
    Route::post('cajatesoreria/rechazar/{id}', 'CajatesoreriaController@rechazar')->name('cajatesoreria.rechazar');
    Route::get('cajatesoreria/pdfDetalleCierre', 'CajatesoreriaController@pdfDetalleCierre')->name('cajatesoreria.pdfDetalleCierre');
    Route::get('cajatesoreria/pdfDetalleCierreF', 'CajatesoreriaController@pdfDetalleCierreF')->name('cajatesoreria.pdfDetalleCierreF');
    Route::get('cajatesoreria/pdfRecibo', 'CajatesoreriaController@pdfRecibo')->name('cajatesoreria.pdfRecibo');
    Route::post('cajatesoreria/cuentasporpagar', 'CajatesoreriaController@cuentasporpagar')->name('cajatesoreria.cuentasporpagar');
    Route::post('cajatesoreria/cuentasmedico', 'CajatesoreriaController@cuentasmedico')->name('cajatesoreria.cuentasmedico');
    Route::get('cajatesoreria/pdfMovilidad', 'CajatesoreriaController@pdfMovilidad')->name('cajatesoreria.pdfMovilidad');
    Route::get('cajatesoreria/pdfMovilidadF', 'CajatesoreriaController@pdfMovilidadF')->name('cajatesoreria.pdfMovilidadF');
    Route::get('cajatesoreria/excel', 'CajatesoreriaController@excel')->name('cajatesoreria.excel');
    Route::get('cajatesoreria/excelF', 'CajatesoreriaController@excelF')->name('cajatesoreria.excelF');
    Route::get('cajatesoreria/recalcular', 'CajatesoreriaController@recalcular')->name('cajatesoreria.recalcular');
    Route::get('cajatesoreria/egresosExcel', 'CajatesoreriaController@egresosExcel')->name('cajatesoreria.egresosExcel');

    /* CAJA FARMACIA*/
    Route::post('cajafarmacia/buscar', 'CajafarmaciaController@buscar')->name('cajafarmacia.buscar');
    Route::get('cajafarmacia/eliminar/{id}/{listarluego}', 'CajafarmaciaController@eliminar')->name('cajafarmacia.eliminar');
    Route::resource('cajafarmacia', 'CajafarmaciaController', array('except' => array('show')));
    Route::get('cajafarmacia/apertura', 'CajafarmaciaController@apertura')->name('cajafarmacia.apertura');
    Route::post('cajafarmacia/aperturar', 'CajafarmaciaController@aperturar')->name('cajafarmacia.aperturar');
    Route::get('cajafarmacia/cierre', 'CajafarmaciaController@cierre')->name('cajafarmacia.cierre');
    Route::post('cajafarmacia/cerrar', 'CajafarmaciaController@cerrar')->name('cajafarmacia.cerrar');
    Route::post('cajafarmacia/generarConcepto', 'CajafarmaciaController@generarConcepto')->name('cajafarmacia.generarconcepto');
    Route::post('cajafarmacia/generarNumero', 'CajafarmaciaController@generarNumero')->name('cajafarmacia.generarnumero');
    Route::post('cajafarmacia/validarCajaTransferencia', 'CajafarmaciaController@validarCajaTransferencia')->name('cajafarmacia.validarCajaTransferencia');
    Route::get('cajafarmacia/personautocompletar/{searching}', 'CajafarmaciaController@personautocompletar')->name('cajafarmacia.personautocompletar');
    Route::get('cajafarmacia/acept/{id}/{listarluego}', 'CajafarmaciaController@acept')->name('cajafarmacia.acept');
    Route::post('cajafarmacia/aceptar/{id}', 'CajafarmaciaController@aceptar')->name('cajafarmacia.aceptar');
    Route::get('cajafarmacia/reject/{id}/{listarluego}', 'CajafarmaciaController@reject')->name('cajafarmacia.reject');
    Route::post('cajafarmacia/rechazar/{id}', 'CajafarmaciaController@rechazar')->name('cajafarmacia.rechazar');
    Route::get('cajafarmacia/pdfDetalleCierre', 'CajafarmaciaController@pdfDetalleCierre')->name('cajafarmacia.pdfDetalleCierre');
    Route::get('cajafarmacia/pdfDetalleCierreF', 'CajafarmaciaController@pdfDetalleCierreF')->name('cajafarmacia.pdfDetalleCierreF');
    Route::get('cajafarmacia/pdfRecibo', 'CajafarmaciaController@pdfRecibo')->name('cajafarmacia.pdfRecibo');
    Route::post('cajafarmacia/cuentasporpagar', 'CajafarmaciaController@cuentasporpagar')->name('cajafarmacia.cuentasporpagar');
    
    /* VENTAADMISION */
    Route::post('ventaadmision/buscar', 'VentaadmisionController@buscar')->name('ventaadmision.buscar');
    Route::get('ventaadmision/pdflistar', 'VentaadmisionController@pdfListar')->name('ventaadmision.pdfListar');
    Route::get('ventaadmision/eliminar/{id}/{listarluego}', 'VentaadmisionController@eliminar')->name('ventaadmision.eliminar');
    Route::resource('ventaadmision', 'VentaadmisionController', array('except' => array('show')));
    Route::get('ventaadmision/pdfComprobante', 'VentaadmisionController@pdfComprobante')->name('ventaadmision.pdfComprobante');
    Route::post('ventaadmision/procesar', 'VentaadmisionController@procesar')->name('ventaadmision.procesar');
    Route::get('ventaadmision/ventaautocompletar/{searching}', 'VentaadmisionController@ventaautocompletar')->name('ventaadmision.ventaautocompletar');
    Route::get('ventaadmision/cobrar/{id}/{listarluego}', 'VentaadmisionController@cobrar')->name('ventaadmision.cobrar');
    Route::post('ventaadmision/pagar', 'VentaadmisionController@pagar')->name('ventaadmision.pagar');
    Route::get('ventaadmision/anular/{id}/{listarluego}', 'VentaadmisionController@anular')->name('ventaadmision.anular');
    Route::post('ventaadmision/anulacion/{id}', 'VentaadmisionController@anulacion')->name('ventaadmision.anulacion');
    Route::post('ventaadmision/resumen', 'VentaadmisionController@resumen')->name('ventaadmision.resumen');
    Route::post('ventaadmision/resumen1', 'VentaadmisionController@resumen1')->name('ventaadmision.resumen1');
    Route::get('ventaadmision/excelConcar', 'VentaadmisionController@excelConcar')->name('ventaadmision.excelConcar');
    Route::get('ventaadmision/excelVenta', 'VentaadmisionController@excelVenta')->name('ventaadmision.excelVenta');
    Route::get('ventaadmision/excelVentaConvenio', 'VentaadmisionController@excelVentaConvenio')->name('ventaadmision.excelVentaConvenio');
    Route::get('ventaadmision/excelSunat', 'VentaadmisionController@excelSunat')->name('ventaadmision.excelSunat');
    Route::get('ventaadmision/excelSunatConvenio', 'VentaadmisionController@excelSunatConvenio')->name('ventaadmision.excelSunatConvenio');
    Route::post('ventaadmision/declarar', 'VentaadmisionController@declarar')->name('ventaadmision.declarar');
    Route::get('ventaadmision/excelVentaBizlink', 'VentaadmisionController@excelVentaBizlink')->name('ventaadmision.excelVentaBizlink');
    Route::get('ventaadmision/excelFarmacia', 'VentaadmisionController@excelFarmacia')->name('ventaadmision.excelFarmacia');
    Route::get('ventaadmision/excelFarmacia1', 'VentaadmisionController@excelFarmacia1')->name('ventaadmision.excelFarmacia1');
    Route::get('ventaadmision/excelKardex', 'VentaadmisionController@excelKardex')->name('ventaadmision.excelKardex');
    Route::get('ventaadmision/excelKardexTodos', 'VentaadmisionController@excelKardexTodos')->name('ventaadmision.excelKardexTodos');
 
    /* MOVIMIENTO CAJA*/
    Route::post('movimientocaja/buscar', 'MovimientocajaController@buscar')->name('movimientocaja.buscar');
    Route::get('movimientocaja/pdflistar', 'MovimientocajaController@pdfListar')->name('movimientocaja.pdfListar');
    Route::get('movimientocaja/detalle/{id}/{listarluego}', 'MovimientocajaController@detalle')->name('movimientocaja.detalle');
    Route::resource('movimientocaja', 'MovimientocajaController', array('except' => array('show')));
    Route::get('movimientocaja/excel', 'MovimientocajaController@excel')->name('movimientocaja.excel');

    /* REPORTE ORDEN PAGO*/
    Route::post('reporteordenpago/buscar', 'ReporteordenpagoController@buscar')->name('reporteordenpago.buscar');
    Route::get('reporteordenpago/pdflistar', 'ReporteordenpagoController@pdfListar')->name('reporteordenpago.pdfListar');
    Route::resource('reporteordenpago', 'ReporteordenpagoController', array('except' => array('show')));

    /* REPORTE EGRESO TESORERIA*/
    Route::post('reporteegresotesoreria/buscar', 'ReporteegresotesoreriaController@buscar')->name('reporteegresotesoreria.buscar');
    Route::get('reporteegresotesoreria/excel', 'ReporteegresotesoreriaController@excel')->name('reporteegresotesoreria.excel');
    Route::get('reporteegresotesoreria/excel2', 'ReporteegresotesoreriaController@excel2')->name('reporteegresotesoreria.excel2');
    Route::get('reporteegresotesoreria/excelbonos', 'ReporteegresotesoreriaController@excelbonos')->name('reporteegresotesoreria.excelbonos');
    Route::resource('reporteegresotesoreria', 'ReporteegresotesoreriaController', array('except' => array('show')));

    /* REPORTE AMBULANCIA*/
    Route::post('reporteambulancia/buscar', 'ReporteambulanciaController@buscar')->name('reporteambulancia.buscar');
    Route::get('reporteambulancia/excel', 'ReporteambulanciaController@excel')->name('reporteambulancia.excel');
    Route::resource('reporteambulancia', 'ReporteambulanciaController', array('except' => array('show')));


    /* REPORTE INGRESO TESORERIA*/
    Route::post('reporteingresotesoreria/buscar', 'ReporteingresotesoreriaController@buscar')->name('reporteingresotesoreria.buscar');
    Route::get('reporteingresotesoreria/excel', 'ReporteingresotesoreriaController@excel')->name('reporteingresotesoreria.excel');
    Route::resource('reporteingresotesoreria', 'ReporteingresotesoreriaController', array('except' => array('show')));

    /* RENDICION*/
    Route::post('rendicion/buscar', 'RendicionController@buscar')->name('rendicion.buscar');
    Route::get('rendicion/pdf', 'RendicionController@pdf')->name('rendicion.pdf');
    Route::get('rendicion/excel', 'RendicionController@excel')->name('rendicion.excel');
    Route::resource('rendicion', 'RendicionController', array('except' => array('show')));
    Route::get('rendicion/detalle/{id}/{listarluego}', 'RendicionController@detalle')->name('rendicion.detalle');
    
    /* REPORTE VENTA*/
    Route::resource('reporteventa', 'ReporteventaController', array('except' => array('show')));
    Route::get('reporteventa/excel', 'ReporteventaController@excel')->name('reporteventa.excel');


    /* SALA */
    Route::post('sala/buscar', 'SalaController@buscar')->name('sala.buscar');
    Route::get('sala/eliminar/{id}/{listarluego}', 'SalaController@eliminar')->name('sala.eliminar');
    Route::resource('sala', 'SalaController', array('except' => array('show')));

    /* TIPO EXAMEN */
    Route::post('tipoexamen/buscar', 'TipoexamenController@buscar')->name('tipoexamen.buscar');
    Route::get('tipoexamen/eliminar/{id}/{listarluego}', 'TipoexamenController@eliminar')->name('tipoexamen.eliminar');
    Route::resource('tipoexamen', 'TipoexamenController', array('except' => array('show')));

    /* EXAMEN */
    Route::post('examen/buscar', 'ExamenController@buscar')->name('examen.buscar');
    Route::get('examen/eliminar/{id}/{listarluego}', 'ExamenController@eliminar')->name('examen.eliminar');
    Route::resource('examen', 'ExamenController', array('except' => array('show')));
    Route::post('examen/agregardetalle', 'ExamenController@agregardetalle')->name('examen.agregardetalle');

    /* TIPOHABITACION */
    Route::post('tipohabitacion/buscar', 'TipohabitacionController@buscar')->name('tipohabitacion.buscar');
    Route::get('tipohabitacion/eliminar/{id}/{listarluego}', 'TipohabitacionController@eliminar')->name('tipohabitacion.eliminar');
    Route::resource('tipohabitacion', 'TipohabitacionController', array('except' => array('show')));

    /* SALAOPERACION */
    Route::post('salaoperacion/buscar', 'SalaoperacionController@buscar')->name('salaoperacion.buscar');
    Route::get('salaoperacion/pdflistar', 'SalaoperacionController@pdfListar')->name('salaoperacion.pdfListar');
    Route::get('salaoperacion/eliminar/{id}/{listarluego}', 'SalaoperacionController@eliminar')->name('salaoperacion.eliminar');
    Route::resource('salaoperacion', 'SalaoperacionController', array('except' => array('show')));
    Route::get('salaoperacion/acept/{id}/{listarluego}', 'SalaoperacionController@acept')->name('salaoperacion.acept');
    Route::post('salaoperacion/aceptar/{id}', 'SalaoperacionController@aceptar')->name('salaoperacion.aceptar');
    Route::get('salaoperacion/reject/{id}/{listarluego}', 'SalaoperacionController@reject')->name('salaoperacion.reject');
    Route::post('salaoperacion/rechazar/{id}', 'SalaoperacionController@rechazar')->name('salaoperacion.rechazar');

    /* TARIFARIO */
    Route::post('tarifario/buscar', 'TarifarioController@buscar')->name('tarifario.buscar');
    Route::get('tarifario/eliminar/{id}/{listarluego}', 'TarifarioController@eliminar')->name('tarifario.eliminar');
    Route::resource('tarifario', 'TarifarioController', array('except' => array('show')));
    Route::get('tarifario/tarifarioautocompletar/{searching}', 'TarifarioController@tarifarioautocompletar')->name('tarifario.personautocompletar');
    Route::get('tarifario/generar/{id}/{listarluego}', 'TarifarioController@generar')->name('tarifario.generar');
    Route::post('tarifario/guardar/{id}', 'TarifarioController@guardar')->name('tarifario.guardar');


    /* PAGO DOCTOR */
    Route::post('pagodoctor/buscar', 'PagodoctorController@buscar')->name('pagodoctor.buscar');
    Route::get('pagodoctor/eliminar/{id}/{listarluego}', 'PagodoctorController@eliminar')->name('pagodoctor.eliminar');
    Route::resource('pagodoctor', 'PagodoctorController', array('except' => array('show')));

    /* PAGO PARTICULAR */
    Route::post('pagoparticular/buscar', 'PagoparticularController@buscar')->name('pagoparticular.buscar');
    Route::get('pagoparticular/pago/{id}/{listarluego}', 'PagoparticularController@pago')->name('pagoparticular.pago');
    Route::post('pagoparticular/pagar', 'PagoparticularController@pagar')->name('pagoparticular.pagar');;
    Route::resource('pagoparticular', 'PagoparticularController', array('except' => array('show')));
    Route::get('pagoparticular/pdfReporte', 'PagoparticularController@pdfReporte')->name('pagoparticular.pdfReporte');
    Route::get('pagoparticular/regularizar/{id}/{listarluego}', 'PagoparticularController@regularizar')->name('pagoparticular.regularizar');
    Route::post('pagoparticular/regulariza/{id}', 'PagoparticularController@regulariza')->name('pagoparticular.regulariza');
    Route::get('pagoparticular/eliminar/{id}/{listarluego}', 'PagoparticularController@eliminar')->name('pagoparticular.eliminar');
    Route::get('pagoparticular/excel', 'PagoparticularController@excel')->name('pagoparticular.excel');

     /* GARANTIA */
    Route::post('garantia/buscar', 'GarantiaController@buscar')->name('garantia.buscar');
    Route::resource('garantia', 'GarantiaController', array('except' => array('show')));
    Route::get('garantia/pdfReporte', 'GarantiaController@pdfReporte')->name('garantia.pdfReporte');
    Route::get('garantia/ExcelReporte', 'GarantiaController@ExcelReporte')->name('garantia.ExcelReporte');
    Route::get('garantia/regularizar/{id}/{listarluego}', 'GarantiaController@regularizar')->name('garantia.regularizar');
    Route::post('garantia/regulariza/{id}', 'GarantiaController@regulariza')->name('garantia.regulariza');
    Route::get('garantia/eliminar/{id}/{listarluego}', 'GarantiaController@eliminar')->name('garantia.eliminar');
    Route::get('garantia/confirmarmedicos/{id}/{listarluego}', 'GarantiaController@confirmarmedicos')->name('garantia.confirmarmedicos');
    Route::post('garantia/seleccionardetalles', 'GarantiaController@seleccionardetalles')->name('garantia.seleccionardetalles');
    Route::get('garantia/pdfRecibo','GarantiaController@pdfRecibo')->name('garantia.pdfRecibo');

    /* GARANTIA SOAT*/
    Route::post('garantiasoat/buscar', 'GarantiaSoatController@buscar')->name('garantiasoat.buscar');
    Route::resource('garantiasoat', 'GarantiaSoatController', array('except' => array('show')));
    Route::get('garantiasoat/pdfReporte', 'GarantiaSoatController@pdfReporte')->name('garantiasoat.pdfReporte');
    Route::get('garantiasoat/ExcelReporte', 'GarantiaSoatController@ExcelReporte')->name('garantiasoat.ExcelReporte');
    Route::get('garantiasoat/regularizar/{id}/{listarluego}', 'GarantiaSoatController@regularizar')->name('garantiasoat.regularizar');
    Route::post('garantiasoat/regulariza/{id}', 'GarantiaSoatController@regulariza')->name('garantiasoat.regulariza');
    Route::get('garantiasoat/eliminar/{id}/{listarluego}', 'GarantiaSoatController@eliminar')->name('garantiasoat.eliminar');
    Route::get('garantiasoat/confirmarmedicos/{id}/{listarluego}', 'GarantiaSoatController@confirmarmedicos')->name('garantiasoat.confirmarmedicos');
    Route::post('garantiasoat/seleccionardetalles', 'GarantiaSoatController@seleccionardetalles')->name('garantiasoat.seleccionardetalles');
    Route::get('garantiasoat/pdfRecibo','GarantiaSoatController@pdfRecibo')->name('garantiasoat.pdfRecibo');


    /* PAGO SOCIO */
    Route::post('pagosocio/buscar', 'PagosocioController@buscar')->name('pagosocio.buscar');
    Route::get('pagosocio/pago/{id}/{listarluego}', 'PagosocioController@pago')->name('pagosocio.pago');
    Route::post('pagosocio/pagar', 'PagosocioController@pagar')->name('pagosocio.pagar');;
    Route::resource('pagosocio', 'PagosocioController', array('except' => array('show')));
    Route::get('pagosocio/pdfReporte', 'PagosocioController@pdfReporte')->name('pagosocio.pdfReporte');
    Route::get('pagosocio/excel', 'PagosocioController@excel')->name('pagosocio.excel');

    /* PAGO TARJETA Y BOLETEO TOTAL */
    Route::post('pagotarjeta/buscar', 'PagotarjetaController@buscar')->name('pagotarjeta.buscar');
    Route::get('pagotarjeta/pago/{id}/{listarluego}', 'PagotarjetaController@pago')->name('pagotarjeta.pago');
    Route::post('pagotarjeta/pagar', 'PagotarjetaController@pagar')->name('pagotarjeta.pagar');;
    Route::resource('pagotarjeta', 'PagotarjetaController', array('except' => array('show')));
    Route::get('pagotarjeta/pdfReporte', 'PagotarjetaController@pdfReporte')->name('pagotarjeta.pdfReporte');
    Route::get('pagotarjeta/excel', 'PagotarjetaController@excel')->name('pagotarjeta.excel');
    Route::get('pagotarjeta/eliminar/{id}/{listarluego}', 'PagotarjetaController@eliminar')->name('pagotarjeta.eliminar');


    /* PAGO CONVENIO */
    Route::post('pagoconvenio/buscar', 'PagoconvenioController@buscar')->name('pagoconvenio.buscar');
    Route::get('pagoconvenio/pago/{listarluego}/{list}', 'PagoconvenioController@pago')->name('pagoconvenio.pago');
    Route::post('pagoconvenio/pagar', 'PagoconvenioController@pagar')->name('pagoconvenio.pagar');;
    Route::resource('pagoconvenio', 'PagoconvenioController', array('except' => array('show')));
    Route::get('pagoconvenio/pdfReporte', 'PagoconvenioController@pdfReporte')->name('pagoconvenio.pdfReporte');
    Route::get('pagoconvenio/excel', 'PagoconvenioController@excel')->name('pagoconvenio.excel');
    Route::get('pagoconvenio/eliminar/{id}/{listarluego}', 'PagoconvenioController@eliminar')->name('pagoconvenio.eliminar');

    /* REPORTE CONSULTA */
    Route::post('reporteconsulta/buscar', 'ReporteconsultaController@buscar')->name('reporteconsulta.buscar');
    Route::resource('reporteconsulta', 'ReporteconsultaController', array('except' => array('show')));
    Route::get('reporteconsulta/uci', 'ReporteconsultaController@uci')->name('reporteconsulta.uci');
    Route::get('reporteconsulta/excel', 'ReporteconsultaController@excel')->name('reporteconsulta.excel');
    Route::get('reporteconsulta/excelCons', 'ReporteconsultaController@excelCons')->name('reporteconsulta.excelCons');
    Route::get('reporteconsulta/excelConsMedico', 'ReporteconsultaController@excelConsMedico')->name('reporteconsulta.excelConsMedico');
    Route::get('reporteconsulta/excelMarcado', 'ReporteconsultaController@excelMarcado')->name('reporteconsulta.excelMarcado');
    Route::get('reporteconsulta/marca', 'ReporteconsultaController@marca')->name('reporteconsulta.marca');
    Route::get('reporteconsulta/desmarca', 'ReporteconsultaController@desmarca')->name('reporteconsulta.desmarca');

    /* REPORTE RAYOS Y RADIOGRAFIAS */
    Route::post('reporterayos/buscar', 'ReporterayosController@buscar')->name('reporterayos.buscar');
    Route::resource('reporterayos', 'ReporterayosController', array('except' => array('show')));
    Route::get('reporterayos/excel', 'ReporterayosController@excel')->name('reporterayos.excel');
    Route::get('reporterayos/excel2', 'ReporterayosController@excel2')->name('reporterayos.excel2');
    Route::get('reporterayos/excel3', 'ReporterayosController@excel3')->name('reporterayos.excel3');
    Route::get('reporterayos/pago/{listarluego}/{list}', 'ReporterayosController@pago')->name('reporterayos.pago');
    Route::post('reporterayos/pagar', 'ReporterayosController@pagar')->name('reporterayos.pagar');;
    

     /* REPORTE REFERIDO */
    Route::post('reportereferido/buscar', 'ReportereferidoController@buscar')->name('reportereferido.buscar');
    Route::resource('reportereferido', 'ReportereferidoController', array('except' => array('show')));
    Route::get('reportereferido/pdf', 'ReportereferidoController@pdf')->name('reportereferido.pdf');
    Route::get('reportereferido/excel', 'ReportereferidoController@excel')->name('reportereferido.excel');
    Route::get('reportereferido/excelresumen', 'ReportereferidoController@excelresumen')->name('reportereferido.excelresumen');

    /* REPORTE FACTURACION */
    Route::post('reportefacturacion/buscar', 'ReportefacturacionController@buscar')->name('reportefacturacion.buscar');
    Route::resource('reportefacturacion', 'ReportefacturacionController', array('except' => array('show')));
    Route::get('reportefacturacion/excel', 'ReportefacturacionController@excel')->name('reportefacturacion.excel');
    Route::get('reportefacturacion/excelsincoaseguro', 'ReportefacturacionController@excelsincoaseguro')->name('reportefacturacion.excelsincoaseguro');

    /* REPORTE DE PAGO DE FACTURACION */
    Route::get('reportepagofacturacion/actualizarpagomedico', 'ReportepagofacturacionController@actualizarpagocero')->name('reportepagofacturacion.actualizarpagocero');
    Route::get('reportepagofacturacion/eliminar/{id}/{listarluego}', 'ReportepagofacturacionController@eliminar')->name('reportepagofacturacion.eliminar');
    Route::post('reportepagofacturacion/comentario', 'ReportepagofacturacionController@comentario')->name('reportepagofacturacion.comentario');

    Route::post('reportepagofacturacion/buscar', 'ReportepagofacturacionController@buscar')->name('reportepagofacturacion.buscar');
    Route::get('reportepagofacturacion/nuevopago', 'ReportepagofacturacionController@nuevopago')->name('reportepagofacturacion.nuevopago');
    Route::get('reportepagofacturacion/nuevoreporte', 'ReportepagofacturacionController@nuevoreporte')->name('reportepagofacturacion.nuevoreporte');
    Route::post('reportepagofacturacion/pagarmedico', 'ReportepagofacturacionController@pagarmedico')->name('reportepagofacturacion.pagarmedico');


    Route::post('reportepagofacturacion/generarreporte', 'ReportepagofacturacionController@generarreporte')->name('reportepagofacturacion.generarreporte');
    Route::get('reportepagofacturacion/generarreporteexcel', 'ReportepagofacturacionController@generarreporteexcel')->name('reportepagofacturacion.generarreporteexcel');
    Route::get('reportepagofacturacion/generarreportepdf', 'ReportepagofacturacionController@generarreportepdf')->name('reportepagofacturacion.generarreportepdf');
    Route::get('reportepagofacturacion/generarreporteconsolidadoexcel', 'ReportepagofacturacionController@generarreporteconsolidadoexcel')->name('reportepagofacturacion.generarreporteconsolidadoexcel');
    Route::resource('reportepagofacturacion', 'ReportepagofacturacionController', array('except' => array('show')));
    Route::get('reportepagofacturacion/excel', 'ReportepagofacturacionController@excel')->name('reportepagofacturacion.excel');
    Route::get('reportepagofacturacion/excelGeneral', 'ReportepagofacturacionController@excelGeneral')->name('reportepagofacturacion.excelGeneral');
    Route::get('reportepagofacturacion/excelDoctor', 'ReportepagofacturacionController@excelDoctor')->name('reportepagofacturacion.excelDoctor');

    /* REPORTE CONVENIO */
    Route::post('reporteconvenio/buscar', 'ReporteconvenioController@buscar')->name('reporteconvenio.buscar');
    Route::resource('reporteconvenio', 'ReporteconvenioController', array('except' => array('show')));
    Route::get('reporteconvenio/excel', 'ReporteconvenioController@excel')->name('reporteconvenio.excel');


    /* REPORTE PAGO MEDICO CONVENIO */
    Route::post('reportepagoconvenio/buscar', 'ReportepagoconvenioController@buscar')->name('reportepagoconvenio.buscar');
    Route::resource('reportepagoconvenio', 'ReportepagoconvenioController', array('except' => array('show')));
    Route::get('reportepagoconvenio/pdf', 'ReportepagoconvenioController@pdf')->name('reportepagoconvenio.pdf');
    Route::get('reportepagoconvenio/excel', 'ReportepagoconvenioController@excel')->name('reportepagoconvenio.excel');


    /* REPORTE TOMOGRAFIAS */
    Route::post('reportetomografia/buscar', 'ReportetomografiaController@buscar')->name('reportetomografia.buscar');
    Route::resource('reportetomografia', 'ReportetomografiaController', array('except' => array('show')));
    Route::get('reportetomagrafia/excel', 'ReportetomografiaController@excel')->name('reportetomagrafia.excel');
    Route::get('reportetomagrafia/excelReferido', 'ReportetomografiaController@excelReferido')->name('reportetomagrafia.excelReferido');
    Route::get('reportetomagrafia/pdf', 'ReportetomografiaController@pdf')->name('reportetomagrafia.pdf');
    Route::get('reportetomagrafia/pdf2', 'ReportetomografiaController@pdf2')->name('reportetomagrafia.pdf2');
    Route::get('reportetomagrafia/pdf3', 'ReportetomografiaController@pdf3')->name('reportetomagrafia.pdf3');
    Route::get('reportetomagrafia/pdfOncorad', 'ReportetomografiaController@pdfOncorad')->name('reportetomagrafia.pdfOncorad');
   
    /* REPORTE LOTE Y FECHA VENC.*/
    Route::post('reportelote/buscar', 'ReporteloteController@buscar')->name('reportelote.buscar');
    Route::resource('reportelote', 'ReporteloteController', array('except' => array('show')));
    Route::get('reportelote/excel', 'ReporteloteController@excel')->name('reportelote.excel');
    Route::get('reportelote/pdf', 'ReporteloteController@pdf')->name('reportelote.pdf');
    
      /* PISO */
    Route::post('piso/buscar', 'PisoController@buscar')->name('piso.buscar');
    Route::get('piso/eliminar/{id}/{listarluego}', 'PisoController@eliminar')->name('piso.eliminar');
    Route::resource('piso', 'PisoController', array('except' => array('show')));

    /* HABITACION */
    Route::post('habitacion/buscar', 'HabitacionController@buscar')->name('habitacion.buscar');
    Route::get('habitacion/eliminar/{id}/{listarluego}', 'HabitacionController@eliminar')->name('habitacion.eliminar');
    Route::resource('habitacion', 'HabitacionController', array('except' => array('show')));
  
    /* HOSPITALIZACION */
    Route::post('hospitalizacion/buscar', 'HospitalizacionController@buscar')->name('hospitalizacion.buscar');
    Route::get('hospitalizacion/eliminar/{id}/{listarluego}', 'HospitalizacionController@eliminar')->name('hospitalizacion.eliminar');
    Route::resource('hospitalizacion', 'HospitalizacionController', array('except' => array('show')));
    Route::get('hospitalizacion/alta/{id}/{listarluego}', 'HospitalizacionController@alta')->name('hospitalizacion.alta');
    Route::post('hospitalizacion/aceptalta', 'HospitalizacionController@aceptalta')->name('hospitalizacion.aceptalta');
    Route::get('hospitalizacion/pdfHospitalizados/{tipo}/{alta}/{fi}/{ff}', 'HospitalizacionController@pdfHospitalizados')->name('hospitalizacion.pdfHospitalizados');
    Route::get('hospitalizacion/excel', 'HospitalizacionController@excel')->name('hospitalizacion.excel');
    Route::get('hospitalizacion/excel02', 'HospitalizacionController@excel02')->name('hospitalizacion.excel02');
    Route::post('hospitalizacion/cargado', 'HospitalizacionController@cargado')->name('hospitalizacion.cargado');

    /* NOTACREDITO */
    Route::post('notacredito/buscar', 'NotacreditoController@buscar')->name('notacredito.buscar');
    Route::get('notacredito/anular/{id}/{listarluego}', 'NotacreditoController@anular')->name('notacredito.anular');
    Route::resource('notacredito', 'NotacreditoController', array('except' => array('show')));
    Route::post('notacredito/seleccionarventa', 'NotacreditoController@seleccionarventa')->name('notacredito.seleccionarventa');
    Route::get('notacredito/pdfComprobante', 'NotacreditoController@pdfComprobante')->name('notacredito.pdfComprobante');
    Route::get('notacredito/pdfComprobante2', 'NotacreditoController@pdfComprobante2')->name('notacredito.pdfComprobante2');
    Route::post('notacredito/procesar', 'NotacreditoController@procesar')->name('notacredito.procesar');
    Route::get('notacredito/anular/{id}/{listarluego}', 'NotacreditoController@anular')->name('notacredito.anular');
    Route::post('notacredito/anulacion/{id}', 'NotacreditoController@anulacion')->name('notacredito.anulacion');
    Route::get('notacredito/excel', 'NotacreditoController@excel')->name('notacredito.excel');

    /* DENUNCIA */
    Route::post('denuncia/buscar', 'DenunciaController@buscar')->name('denuncia.buscar');
    Route::get('denuncia/eliminar/{id}/{listarluego}', 'DenunciaController@eliminar')->name('denuncia.eliminar');
    Route::resource('denuncia', 'DenunciaController', array('except' => array('show')));
    Route::get('denuncia/excel', 'DenunciaController@excel')->name('denuncia.excel');
    Route::get('denuncia/denunciaautocompletar/{searching}', 'DenunciaController@denunciaautocompletar')->name('denuncia.denunciaautocompletar');
    Route::post('denuncia/buscarGarantia', 'DenunciaController@buscarGarantia')->name('denuncia.buscarGarantia');

    /* RECIBO EGRESO */
    Route::post('reciboblanco/buscar', 'ReciboblancoController@buscar')->name('reciboblanco.buscar');
    Route::get('reciboblanco/eliminar/{id}/{listarluego}', 'ReciboblancoController@eliminar')->name('reciboblanco.eliminar');
    Route::resource('reciboblanco', 'ReciboblancoController', array('except' => array('show')));
    Route::get('reciboblanco/pdfRecibo', 'ReciboblancoController@pdfRecibo')->name('reciboblanco.pdfRecibo');

    /* RECIBO INGRESO */
    Route::post('reciboblancoingreso/buscar', 'ReciboblancoingresoController@buscar')->name('reciboblancoingreso.buscar');
    Route::get('reciboblancoingreso/eliminar/{id}/{listarluego}', 'ReciboblancoingresoController@eliminar')->name('reciboblancoingreso.eliminar');
    Route::resource('reciboblancoingreso', 'ReciboblancoingresoController', array('except' => array('show')));
    Route::get('reciboblancoingreso/pdfRecibo', 'ReciboblancoingresoController@pdfRecibo')->name('reciboblancoingreso.pdfRecibo');

    /* RECIBO BLANCO */
    Route::post('recibomedico/buscar', 'RecibomedicoController@buscar')->name('recibomedico.buscar');
    Route::get('recibomedico/eliminar/{id}/{listarluego}', 'RecibomedicoController@eliminar')->name('recibomedico.eliminar');
    Route::resource('recibomedico', 'RecibomedicoController', array('except' => array('show')));
    Route::get('recibomedico/pdfRecibo', 'RecibomedicoController@pdfRecibo')->name('recibomedico.pdfRecibo');
    Route::get('recibomedico/excel', 'RecibomedicoController@excel')->name('recibomedico.excel');

    /* MENSAJE */
    Route::post('mensaje/buscar', 'MensajeController@buscar')->name('mensaje.buscar');
    Route::get('mensaje/eliminar/{id}/{listarluego}', 'MensajeController@eliminar')->name('mensaje.eliminar');
    Route::resource('mensaje', 'MensajeController', array('except' => array('show')));

    /* MENSAJE FACTURACION*/
    Route::post('mensajefacturacion/buscar', 'MensajefacturacionController@buscar')->name('mensajefacturacion.buscar');
    Route::get('mensajefacturacion/eliminar/{id}/{listarluego}', 'MensajefacturacionController@eliminar')->name('mensajefacturacion.eliminar');
    Route::resource('mensajefacturacion', 'MensajefacturacionController', array('except' => array('show')));

    /* BANCO */
    Route::post('banco/buscar', 'BancoController@buscar')->name('banco.buscar');
    Route::get('banco/eliminar/{id}/{listarluego}', 'BancoController@eliminar')->name('banco.eliminar');
    Route::resource('banco', 'BancoController', array('except' => array('show')));

    /* CUENTA BANCO */
    Route::post('cuentabanco/buscar', 'CuentabancoController@buscar')->name('cuentabanco.buscar');
    Route::get('cuentabanco/eliminar/{id}/{listarluego}', 'CuentabancoController@eliminar')->name('cuentabanco.eliminar');
    Route::resource('cuentabanco', 'CuentabancoController', array('except' => array('show')));


    /* CUENTA BANCARIA */
    Route::post('cuentabancaria/buscar', 'CuentabancariaController@buscar')->name('cuentabancaria.buscar');
    Route::get('cuentabancaria/eliminar/{id}/{listarluego}', 'CuentabancariaController@eliminar')->name('cuentabancaria.eliminar');
    Route::resource('cuentabancaria', 'CuentabancariaController', array('except' => array('show')));
    Route::get('cuentabancaria/pdfRecibo', 'CuentabancariaController@pdfRecibo')->name('cuentabancaria.pdfRecibo');
    Route::get('cuentabancaria/cobrar/{id}/{listarluego}', 'CuentabancariaController@cobrar')->name('cuentabancaria.cobrar');
    Route::post('cuentabancaria/pagar/{id}', 'CuentabancariaController@pagar')->name('cuentabancaria.pagar');
    Route::get('cuentabancaria/pdfListar', 'CuentabancariaController@pdfListar')->name('cuentabancaria.pdfListar');
    Route::get('cuentabancaria/pdfListarResumen', 'CuentabancariaController@pdfListarResumen')->name('cuentabancaria.pdfListarResumen');
    Route::post('cuentabancaria/generarConcepto', 'CuentabancariaController@generarConcepto')->name('cuentabancaria.generarconcepto');
    Route::post('cuentabancaria/cuentasporpagar', 'CuentabancariaController@cuentasporpagar')->name('cuentabancaria.cuentasporpagar');


    /* REPORTES */
    Route::get('rpts/medicos', 'repsController@medicos')->name('reps.medicos');
    Route::get('rpts/creditop', 'repsController@creditop')->name('reps.creditop');
    Route::get('rpts/salas', 'repsController@salas')->name('reps.salas');
    Route::get('rpts/nombres/{ap}', 'repsController@nombres')->name('reps.nombres');
    Route::get('rpts/pnombres/{ap}/{am}', 'repsController@pnombres')->name('reps.pnombres');
    Route::get('rpts/cajas', 'repsController@cajas')->name('reps.cajas');
    Route::get('rpts/convenios', 'repsController@convenios')->name('reps.convenios');
    Route::get('rpts/medicinas', 'repsController@medicinas')->name('reps.medicinas');
    Route::get('rpts/nmedicinas/{indicio}', 'repsController@nmedicinas')->name('reps.nmedicinas');
    Route::get('rpts/nproveedor/{indicio}', 'repsController@nproveedor')->name('reps.nproveedor');
    Route::get('rpts/nespecialidad/{indicio}', 'repsController@nespecialidad')->name('reps.nespecialidad');
    Route::get('rpts/nprincipio/{indicio}', 'repsController@nprincipio')->name('reps.nprincipio');
    Route::get('rpts/presentaciones', 'repsController@presentaciones')->name('reps.presentaciones');
    Route::get('rpts/tiposervicio/{indicio}', 'repsController@tiposervicio')->name('reps.tiposervicio');
    Route::get('rpts/servicio/{indicio}', 'repsController@servicio')->name('reps.servicio');
    Route::get('rpts/bservicio/{indicio}', 'repsController@bservicio')->name('reps.bservicio');

    Route::resource('rptMensaje', 'repsController', array('except' => array('show')));    

    Route::resource('rptAnuladas', 'repsController@anuladas', array('except' => array('show')));    
    Route::resource('creditop', 'repsController@creditop', array('except' => array('show')));    
    Route::resource('rptsSalaope', 'repsController@salaope', array('except' => array('show')));    
    Route::resource('rptFallecidos', 'repsController@fallecidos', array('except' => array('show')));
    Route::resource('rptConsultaPago', 'repsController@ConsultaPago', array('except' => array('show')));    
    Route::resource('rptPacM', 'repsController@pacM', array('except' => array('show')));
    Route::resource('rptHistoria', 'repsController@historia', array('except' => array('show')));    
    Route::resource('rptPacP', 'repsController@pacP', array('except' => array('show')));    
    Route::resource('rptHosp', 'repsController@hosp', array('except' => array('show')));    
    Route::resource('rptCaja', 'repsController@caja', array('except' => array('show')));
    Route::resource('rptHonorariosD', 'repsController@honorarios', array('except' => array('show')));    
    Route::resource('rptPagE', 'repsController@pagE', array('except' => array('show')));   
    Route::resource('rptVendm', 'repsController@Vendm', array('except' => array('show'))); 
    Route::resource('rptPagC', 'repsController@pagC', array('except' => array('show')));
    Route::resource('rptInventarioVal', 'repsController@InventarioVal', array('except' => array('show')));
    Route::resource('rptsmovAlma', 'repsController@movAlma', array('except' => array('show')));
    Route::resource('rptatenC', 'repsController@atenC', array('except' => array('show')));
    Route::resource('rptPagME', 'repsController@pagME', array('except' => array('show')));
    Route::resource('rptPagMC', 'repsController@pagMC', array('except' => array('show')));
    Route::resource('rptCompras', 'repsController@compras', array('except' => array('show')));
    Route::resource('rptVentas', 'repsController@ventas', array('except' => array('show')));
    Route::resource('rptGConv', 'repsController@gconv', array('except' => array('show')));
    Route::resource('rptCostos', 'repsController@costos', array('except' => array('show')));
    Route::resource('rptNotas', 'repsController@notas', array('except' => array('show')));
    Route::resource('rptKardex', 'repsController@kardex', array('except' => array('show')));
    Route::resource('rptKardexCo', 'repsController@kardexCo', array('except' => array('show')));
    Route::resource('rptStock', 'repsController@ProQuimica', array('except' => array('show')));
    Route::resource('rptPro', 'repsController@Proveedores', array('except' => array('show')));
    Route::resource('rptFpro', 'repsController@Productos', array('except' => array('show')));
    Route::resource('rptPedido', 'repsController@Pedido', array('except' => array('show')));
    Route::resource('rptFacCon', 'repsController@FacCon', array('except' => array('show')));
    Route::resource('rptRetramites', 'repsController@Retramites', array('except' => array('show')));
    Route::resource('rptMovimientos', 'repsController@movimientosConta', array('except' => array('show')));
    // Route::get('createRetramite','repsController@createRetramite');
    
    /* APERTURA CIERRE CAJA */
    Route::post('aperturacierrecaja/buscar', 'AperturacierrecajaController@buscar')->name('aperturacierrecaja.buscar');
    Route::get('aperturacierrecaja/eliminar/{id}/{listarluego}', 'AperturacierrecajaController@eliminar')->name('aperturacierrecaja.eliminar');
    Route::post('aperturacierrecaja/abrir', 'AperturacierrecajaController@abrir')->name('aperturacierrecaja.abrir');
    Route::post('aperturacierrecaja/cerrar', 'AperturacierrecajaController@cerrar')->name('aperturacierrecaja.cerrar');
    Route::resource('aperturacierrecaja', 'AperturacierrecajaController', array('except' => array('show')));
      /* MIGRAR EXCEL*/
    Route::get('importHistoria', 'ExcelController@importHistoria');
    Route::get('downloadExcel/{type}', 'ExcelController@downloadExcel');
    Route::post('importHistoriaExcel', 'ExcelController@importHistoriaExcel');
    Route::post('importTarifario', 'ExcelController@importTarifario');
    Route::post('importApellidoExcel', 'ExcelController@importApellidoExcel');
    Route::post('importCie', 'ExcelController@importCie');
    Route::post('importStock', 'ExcelController@importStock');
    
    Route::get('/empresa', function(){
        return View::make('dashboard.empresa.admin');
    });
    Route::get('/egresado', function(){
        return View::make('dashboard.egresado.admin');
    });
    Route::get('/publicacion', function(){
        return View::make('dashboard.publicacion.admin');
    });
});

Route::get('provincia/cboprovincia/{id?}', array('as' => 'provincia.cboprovincia', 'uses' => 'ProvinciaController@cboprovincia'));
Route::get('distrito/cbodistrito/{id?}', array('as' => 'distrito.cbodistrito', 'uses' => 'DistritoController@cbodistrito'));
