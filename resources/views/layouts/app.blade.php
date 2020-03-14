<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name', 'SIGHO') }}</title>
    <link rel="icon" href="{{ asset('dist/img/user2-160x160.jpg') }}" sizes="16x16 32x32 48x48 64x64" type="image/vnd.microsoft.icon">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    {!! Html::style('bootstrap/css/bootstrap.min.css') !!}
    <!-- Font Awesome -->
    {!! Html::style('dist/css/font-awesome.min.css') !!}
    <!-- Ionicons -->
    {!! Html::style('dist/css/ionicons.min.css') !!}
    <!-- DataTables -->
    {!! Html::style('plugins/datatables/dataTables.bootstrap.css') !!}
    <!-- Theme style -->
    {!! Html::style('dist/css/AdminLTE.min.css') !!}
    {!! Html::style('css/custom.css') !!}
    <!-- AdminLTE Skins. Choose a skin from the css/skins
    folder instead of downloading all of them to reduce the load. -->
    {!! Html::style('dist/css/skins/_all-skins.min.css') !!}
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]-->
    {!! Html::script('dist/js/html5shiv.min.js') !!}
    {!! Html::script('dist/js/respond.min.js') !!}
    <!--[endif]-->
    {{-- bootstrap-datetimepicker: para calendarios --}}
    {!! HTML::style('dist/css/bootstrap-datetimepicker.min.css', array('media' => 'screen')) !!}

    {{-- typeahead.js-bootstrap: para autocompletar --}}
    {!! HTML::style('dist/css/typeahead.js-bootstrap.css', array('media' => 'screen')) !!}

</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('app.main-header')
        @include('app.main-sidebar')
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper" id="container">
            content
        </div>
        <!-- /.content-wrapper -->
        @include('app.footer')
        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Create the tabs -->
            <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
                <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
                <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
                <!-- Home tab content -->
                <div class="tab-pane" id="control-sidebar-home-tab">
                    <h3 class="control-sidebar-heading">Recent Activity</h3>
                    <ul class="control-sidebar-menu">
                        <li>
                            <a href="javascript:void(0)">
                                <i class="menu-icon fa fa-birthday-cake bg-red"></i>
                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>
                                    <p>Will be 23 on April 24th</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="menu-icon fa fa-user bg-yellow"></i>
                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Frodo Updated His Profile</h4>
                                    <p>New phone +1(800)555-1234</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="menu-icon fa fa-envelope-o bg-light-blue"></i>
                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Nora Joined Mailing List</h4>
                                    <p>nora@example.com</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="menu-icon fa fa-file-code-o bg-green"></i>
                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Cron Job 254 Executed</h4>
                                    <p>Execution time 5 seconds</p>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <!-- /.control-sidebar-menu -->
                    <h3 class="control-sidebar-heading">Tasks Progress</h3>
                    <ul class="control-sidebar-menu">
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="control-sidebar-subheading">
                                    Custom Template Design
                                    <span class="label label-danger pull-right">70%</span>
                                </h4>
                                <div class="progress progress-xxs">
                                    <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="control-sidebar-subheading">
                                    Update Resume
                                    <span class="label label-success pull-right">95%</span>
                                </h4>
                                <div class="progress progress-xxs">
                                    <div class="progress-bar progress-bar-success" style="width: 95%"></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="control-sidebar-subheading">
                                    Laravel Integration
                                    <span class="label label-warning pull-right">50%</span>
                                </h4>
                                <div class="progress progress-xxs">
                                    <div class="progress-bar progress-bar-warning" style="width: 50%"></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="control-sidebar-subheading">
                                    Back End Framework
                                    <span class="label label-primary pull-right">68%</span>
                                </h4>
                                <div class="progress progress-xxs">
                                    <div class="progress-bar progress-bar-primary" style="width: 68%"></div>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <!-- /.control-sidebar-menu -->
                </div>
                <!-- /.tab-pane -->
                <!-- Stats tab content -->
                <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
                <!-- /.tab-pane -->
                <!-- Settings tab content -->
                <div class="tab-pane" id="control-sidebar-settings-tab">
                    <form method="post">
                        <h3 class="control-sidebar-heading">General Settings</h3>
                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Report panel usage
                                <input type="checkbox" class="pull-right" checked>
                            </label>
                            <p>
                                Some information about this general settings option
                            </p>
                        </div>
                        <!-- /.form-group -->
                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Allow mail redirect
                                <input type="checkbox" class="pull-right" checked>
                            </label>
                            <p>
                                Other sets of options are available
                            </p>
                        </div>
                        <!-- /.form-group -->
                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Expose author name in posts
                                <input type="checkbox" class="pull-right" checked>
                            </label>
                            <p>
                                Allow the user to show his name in blog posts
                            </p>
                        </div>
                        <!-- /.form-group -->
                        <h3 class="control-sidebar-heading">Chat Settings</h3>
                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Show me as online
                                <input type="checkbox" class="pull-right" checked>
                            </label>
                        </div>
                        <!-- /.form-group -->
                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Turn off notifications
                                <input type="checkbox" class="pull-right">
                            </label>
                        </div>
                        <!-- /.form-group -->

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Delete chat history
                                <a href="javascript:void(0)" class="text-red pull-right"><i class="fa fa-trash-o"></i></a>
                            </label>
                        </div>
                        <!-- /.form-group -->
                    </form>
                </div>
                <!-- /.tab-pane -->
            </div>
        </aside>
        <!-- /.control-sidebar -->
        <!-- Add the sidebar's background. This div must be placed immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>
    </div>
    <!-- ./wrapper -->
    <!-- jQuery 2.2.3 -->
    {!! Html::script('plugins/jQuery/jquery-2.2.3.min.js') !!}
    <!-- Bootstrap 3.3.6 -->
    {!! Html::script('bootstrap/js/bootstrap.min.js') !!}
    {!! Html::script('plugins/datatables/jquery.dataTables.min.js') !!}
    {!! Html::script('plugins/datatables/dataTables.bootstrap.min.js') !!}
    <!-- Slimscroll -->
    {!! Html::script('plugins/slimScroll/jquery.slimscroll.min.js') !!}
    <!-- FastClick -->
    {!! Html::script('plugins/fastclick/fastclick.js') !!}
    <!-- AdminLTE App -->
    {!! Html::script('dist/js/app.min.js') !!}
    <!-- AdminLTE for demo purposes -->
    {!! Html::script('dist/js/demo.js') !!}
    <!-- bootbox code -->
    {!! Html::script('dist/js/bootbox.min.js') !!}
    {{-- Funciones propias --}}
    {!! Html::script('dist/js/funciones.js') !!}
    {{-- jquery.inputmask: para mascaras en cajas de texto --}}
    {!! Html::script('plugins/input-mask/jquery.inputmask.js') !!}
    {!! Html::script('plugins/input-mask/jquery.inputmask.extensions.js') !!}
    {!! Html::script('plugins/input-mask/jquery.inputmask.date.extensions.js') !!}
    {!! Html::script('plugins/input-mask/jquery.inputmask.numeric.extensions.js') !!}
    {!! Html::script('plugins/input-mask/jquery.inputmask.phone.extensions.js') !!}
    {!! Html::script('plugins/input-mask/jquery.inputmask.regex.extensions.js') !!}
    {{-- bootstrap-datetimepicker: para calendarios --}}
    {!! HTML::script('dist/js/moment-with-locales.min.js') !!}
    {!! HTML::script('dist/js/bootstrap-datetimepicker.min.js') !!}
    {{-- typeahead.js-bootstrap: para autocompletar --}}
    {!! HTML::script('dist/js/typeahead.bundle.min.js') !!}
    {!! HTML::script('dist/js/bloodhound.min.js') !!}
    
</body>
</html>
