<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Egresado
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
                                <th>Código</th>
                                <th>Apellido Paterno</th>
                                <th>Apellido Materno</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>CV</th>
                                <th>Editar</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>117003K</td>
                                <td>Arambulo</td>
                                <td>Diaz</td>
                                <td>Yhann Carlos</td>
                                <td>Aprobado</td>
                                <td><a href="cv/117003K.pdf">Ir</a></td>
                                <td><button type="button" data-toggle="modal" data-target="#myModal" class="btn btn-xs btn-warning">Editar</button></td>
                                <td><button type="button" data-toggle="modal" data-target="#myModal3" class="btn btn-xs btn-danger">Eliminar</button></td>
                            </tr>
                            <tr>
                                <td>117004G</td>
                                <td>Balarezo</td>
                                <td>Machuca</td>
                                <td>Karen Alejandra</td>
                                <td>Pendiente</td>
                                <td><a href="cv/117004G.pdf">Ir</a></td>
                                <td><button type="button" data-toggle="modal" data-target="#myModal" class="btn btn-xs btn-warning">Editar</button></td>
                                <td><button type="button" data-toggle="modal" data-target="#myModal3" class="btn btn-xs btn-danger">Eliminar</button></td>
                            </tr>
                            <tr>
                                <td>107510G</td>
                                <td>Cabrejos </td>
                                <td>Esquerre</td>
                                <td>Jennifer Melissa</td>
                                <td>Pendiente</td>
                                <td><a href="cv/107510G.pdf">Ir</a></td>
                                <td><button type="button" data-toggle="modal" data-target="#myModal" class="btn btn-xs btn-warning">Editar</button></td>
                                <td><button type="button" data-toggle="modal" data-target="#myModal3" class="btn btn-xs btn-danger">Eliminar</button></td>
                            </tr>
                            <tr>
                                <td>100010I</td>
                                <td>Castillo</td>
                                <td>Salazar</td>
                                <td>Carlos Jesús</td>
                                <td>Pendiente</td>
                                <td><a href="cv/100010I.pdf">Ir</a></td>
                                <td><button type="button" data-toggle="modal" data-target="#myModal" class="btn btn-xs btn-warning">Editar</button></td>
                                <td><button type="button" data-toggle="modal" data-target="#myModal3" class="btn btn-xs btn-danger">Eliminar</button></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Código</th>
                                <th>Apellido Paterno</th>
                                <th>Apellido Materno</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>CV</th>
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
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Modificar registro</h4>
            </div>
            <div class="modal-body">
                <!-- form start -->
                <form class="form-horizontal">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Código</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Email</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Password</label>
                            <div class="col-sm-10">
                                <input type="password" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Apellido Paterno</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Apellido Materno</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Nombres</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">CV</label>
                            <div class="col-sm-10">
                                <input type="file" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-2 control-label">Aprobado</label>
                            <div class="col-sm-10">
                                <input type="checkbox">
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
<!-- Modal 2 -->
<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Registrar</h4>
            </div>
            <div class="modal-body">
                <!-- form start -->
                <form class="form-horizontal">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Código</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Email</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Password</label>
                            <div class="col-sm-10">
                                <input type="password" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Apellido Paterno</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Apellido Materno</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Nombres</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">CV</label>
                            <div class="col-sm-10">
                                <input type="file" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-2 control-label">Aprobado</label>
                            <div class="col-sm-10">
                                <input type="checkbox">
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