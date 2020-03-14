<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ env('APP_NAME', 'Laravel') }} | Login</title>
    <link rel="icon" href="{{ asset('dist/img/user2-160x160.jpg') }}" sizes="16x16 32x32 48x48 64x64" type="image/vnd.microsoft.icon">
    {{-- Tell the browser to be responsive to screen width --}}
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    {{-- Bootstrap 3.3.6 --}}
    {!! Html::style('bootstrap/css/bootstrap.min.css') !!}
    {{-- Font Awesome --}}
    {!! Html::style('dist/css/font-awesome.min.css') !!}
    {{-- Ionicons --}}
    {!! Html::style('dist/css/ionicons.min.css') !!}
    {{-- Theme style --}}
    {!! Html::style('dist/css/AdminLTE.min.css') !!}
    {{-- iCheck --}}
    {!! Html::style('plugins/iCheck/square/blue.css') !!}

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    {!! Html::script('dist/js/html5shiv.min.js') !!}
    {!! Html::script('dist/js/respond.min.js') !!}
    <![endif]-->
</head>
<body class="hold-transition login-page">
    @yield('content')
    <!-- jQuery 2.2.3 -->
    {!! Html::script('plugins/jQuery/jquery-2.2.3.min.js') !!}
    <!-- Bootstrap 3.3.6 -->
    {!! Html::script('bootstrap/js/bootstrap.min.js') !!}
    <!-- iCheck -->
    {!! Html::script('plugins/iCheck/icheck.min.js') !!}
    <script>
        $(function () {
            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
    </script>
</body>
</html>
