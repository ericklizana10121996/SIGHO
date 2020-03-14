@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="login-logo">
        <b>SIGHO</b>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <b><p class="login-box-msg">Ingresa para iniciar tu sesión</p></b>

        <form action="{{ url('/auth/login') }}" method="post">
            {{ csrf_field() }}
            @if (count($errors) > 0)
            <div class="form-group has-error has-feedback">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li class="text-red">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="form-group{{ $errors->has('login') ? ' has-error' : '' }} has-feedback">
                <input id="login" name="login" type="text" class="form-control" placeholder="Usuario" value="{{ old('login') }}" required autofocus>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" placeholder="Password" id="password" name="password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-8">
                    <div class="checkbox icheck">
                        <label>
                            <input type="checkbox" name="remember"> Recordarme
                        </label>
                    </div>
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">Ingresar</button>
                </div>
                <!-- /.col -->
            </div>
        </form>
        <a href="{{ url('/password/reset') }}">Recuperar mi contraseña</a><br>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->
@endsection