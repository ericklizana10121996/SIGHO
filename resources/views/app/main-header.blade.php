<?php
use App\User;
use App\Person;
use App\Usertype;
use Jenssegers\Date\Date;
Date::setLocale('es');
$user     = Auth::user();
$person   = Person::find($user->person_id);
$usertype = Usertype::find($user->usertype_id);
$date     = Date::instance($usertype->created_at)->format('l j F Y');
?>
<style>
.enlaces{
    float: left;
    background-image: none;
    padding: 15px 15px;
    cursor: pointer;    color: #000;
    font-family: fontAwesome;
}
</style>
<header class="main-header">
    <!-- Logo -->
    <a href="#" class="logo" onclick="window.open('{{ url('/dashboard')}}','_blank')">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>SIGHO</b></span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><b>SIGHO</b></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <a href="#" onclick="cargarRuta('{{ url('/cita') }}', 'container');" title="Citas" class="enlaces"><i class="fa fa-calendar"></i></a>
        <a href="#" onclick="cargarRuta('{{ url('/medico') }}', 'container');" title="Medicos" class="enlaces"><i class="fa fa-users"></i></a>
        <a href="#" onclick="cargarRuta('{{ url('/hospitalizacion') }}', 'container');" title="Hospitalizacion" class="enlaces"><i class="fa fa-ambulance"></i></a>
        <a href="#" onclick="cargarRuta('{{ url('/salaoperacion') }}', 'container');" title="Sala de Operacion" class="enlaces"><i class="fa fa-bed"></i></a>
        <div id='divAlerta' class='enlaces' style='color:red;font-weight: bold;'></div>
        
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="dist/img/logo2.jpg" class="user-image" alt="User Image">
                        <span class="hidden-xs">{{ $person->nombres.' '.$person->apellidopaterno.' '.$person->apeliidomaterno }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="dist/img/logo2.jpg" class="img-circle" alt="User Image">

                            <p>
                                {{ $person->nombres.' '.$person->apellidopaterno.' '.$person->apeliidomaterno }} - {{ $usertype->name }}
                                <small>Miembro desde {{ $date }}</small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat">Perfil</a>
                            </div>
                            <div class="pull-right">
                                <a href="{{ url('/auth/logout') }}" class="btn btn-default btn-flat">Cerrar Sesión</a>
                            </div>
                        </li>
                    </ul>
                    
                </li>
                <!-- Control Sidebar Toggle Button -->
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>

<script type="text/javascript">
    function alertaCierre(){
        if ( '{{ date("H:i") }}' >= "23:50" ){
            if ( {{ $usertype->id }} == 11 || {{ $usertype->id }} == 5) {
                alert("\n \n \n \n        ¡RECUERDA QUE DEBES CERRAR TU CAJA HASTA LAS 11:55 P.M.!\n \n \n \n");
            }
        }
    }

    //setInterval(function(){ alertaCierre(); }, 60000);
    var alerta="";
    function alertaArchivo(){
        $.ajax({
            type: "POST",
            url: "seguimiento/alerta",
            data: "_token="+$(' :input[name="_token"]').val(),
            success: function(a) {
                eval(a);
                if(vcantidad=='0'){
                    $("#divAlerta").html('');
                }else{
                    $("#divAlerta").html(vdatos);
                    if(alerta!=valerta){
                        alert(valerta);
                        alerta=valerta;
                    }
                }
            }
        });
    }
    @if($usertype->id==16)
        setInterval(function(){ alertaArchivo(); }, 4000);
    @endif
</script>