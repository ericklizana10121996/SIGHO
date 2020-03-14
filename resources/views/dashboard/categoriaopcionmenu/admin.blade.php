<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Empresas
        &nbsp;&nbsp;&nbsp;<button data-toggle="modal" data-target="#myModal2" type="button" class="btn btn-sm btn-success">Nuevo</button>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Razón Social</th>
                                <th>Departamento</th>
                                <th>Descripción</th>
                                <th>Web</th>
                                <th>Estado</th>
                                <th>Editar</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Empresa A</td>
                                <td>Cajamarca</td>
                                <td>Empresa dedicada a la exportación de verduras</td>
                                <td><a href="www.empresaa.com">Ir</a></td>
                                <td>Aprobado</td>
                                <td><button type="button" data-toggle="modal" data-target="#myModal" class="btn btn-xs btn-warning">Editar</button></td>
                                <td><button type="button" data-toggle="modal" data-target="#myModal3" class="btn btn-xs btn-danger">Eliminar</button></td>
                            </tr>
                            <tr>
                                <td>Empresa B</td>
                                <td>lambayeque</td>
                                <td>Estudio contable</td>
                                <td><a href="www.empresab.com">Ir</a></td>
                                <td>Pendiente</td>
                                <td><button type="button" class="btn btn-xs btn-warning">Editar</button></td>
                                <td><button type="button" class="btn btn-xs btn-danger">Eliminar</button></td>
                            </tr>
                            <tr>
                                <td>Empresa C</td>
                                <td>Cajamarca</td>
                                <td>Empresa de Transporte Turística a Cumbemayo</td>
                                <td><a href="www.empresac.com">Ir</a></td>
                                <td>Aprobado</td>
                                <td><button type="button" class="btn btn-xs btn-warning">Editar</button></td>
                                <td><button type="button" class="btn btn-xs btn-danger">Eliminar</button></td>
                            </tr>
                            <tr>
                                <td>Empresa E</td>
                                <td>lambayeque</td>
                                <td>Agroexportadora</td>
                                <td><a href="www.empresad.com">Ir</a></td>
                                <td>Aprobado</td>
                                <td><button type="button" class="btn btn-xs btn-warning">Editar</button></td>
                                <td><button type="button" class="btn btn-xs btn-danger">Eliminar</button></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Razón Social</th>
                                <th>Departamento</th>
                                <th>Descripción</th>
                                <th>Web</th>
                                <th>Estado</th>
                                <th>Editar</th>
                                <th>Eliminar</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</section>
<script>
    $(function () {
        $("#example1").DataTable();
        $('#example2').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false
        });
    });
</script>
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Modificar Empresa</h4>
            </div>
            <div class="modal-body">
                <!-- form start -->
                <form class="form-horizontal">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Email</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3" placeholder="Email">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-2 control-label">Password</label>

                            <div class="col-sm-10">
                                <input type="password" class="form-control" id="inputPassword3" placeholder="Password">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary">Modificar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal 2 -->
<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Registrar Registro</h4>
            </div>
            <div class="modal-body">
                <!-- form start -->
                <form class="form-horizontal">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Email</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3" placeholder="Email">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-2 control-label">Password</label>
                            <div class="col-sm-10">
                                <input type="password" class="form-control" id="inputPassword3" placeholder="Password">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary">Registrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal 3 -->
<div class="modal fade" id="myModal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Eliminar registro</h4>
            </div>
            <div class="modal-body">
                <!-- form start -->
                <form class="form-horizontal">
                    ¿Esta seguro de eliminar el registro?
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary">Eliminar</button>
            </div>
        </div>
    </div>
</div>