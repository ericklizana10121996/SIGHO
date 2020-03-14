<?php
use App\Menuoptioncategory;
use App\Menuoption;
use App\Permission;
use App\User;
use App\Person;
$user                  = Auth::user();
session(['usertype_id' => $user->usertype_id]);
$tipousuario_id        = session('usertype_id');
$menu                  = generarMenu($tipousuario_id);
$person                = Person::find($user->person_id);
?>

<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
       <!-- <div class="user-panel">
            <div class="pull-left image">
                <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ $person->firstname.' '.$person->lastname }}</p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>-->
        <!-- search form -->
        <!--<form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
                    <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                    </button>
                </span>
            </div>
        </form>-->
        <!-- /.search form -->
        <!-- sidebar menu: : style can be found in sidebar.less -->
        {!! $menu !!}
    </section>
    <!-- /.sidebar -->
</aside>
<?php
function generarMenu($idtipousuario)
{
    $menu = array();
    #Paso 1°: Buscar las categorias principales
    $categoriaopcionmenu = new Menuoptioncategory();
    $opcionmenu          = new Menuoption();
    $permiso             = new Permission();
    $catPrincipales      = $categoriaopcionmenu->whereNull('menuoptioncategory_id')->orderBy('order', 'ASC')->get();
    $cadenaMenu          = '<ul class="sidebar-menu">';
    foreach ($catPrincipales as $key => $catPrincipal) {
        #Buscamos a las categorias hijo
        $hijos = buscarHijos($catPrincipal->id, $idtipousuario);
        $usar = false;
        $aux = array();
        $opciones = $opcionmenu->where('menuoptioncategory_id', '=', $catPrincipal->id)->orderBy('order', 'ASC')->get();
        if ($opciones->count()) {               
            foreach ($opciones as $key => $opcion) {
                $permisos = $permiso->where('menuoption_id', '=', $opcion->id)->where('usertype_id', '=', $idtipousuario)->first();
                if ($permisos) {
                    $usar  = true;
                    $aux2  = $opcionmenu->find($permisos->menuoption_id);
                    $aux[] = array(
                        'nombre' => $aux2->name,
                        'link'   => $aux2->link,
                        'icono'  => $aux2->icon
                        );
                }
            }           
        }
        if ($hijos != '' || $usar === true ) {
            $cadenaMenu .= '<li class="treeview">';
            $cadenaMenu .= '<a href="#"><i class="'.$catPrincipal->icon.'"></i> <span>'.$catPrincipal->name.'</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>';
            $cadenaMenu .= '<ul class="treeview-menu">';
            for ($i=0; $i < count($aux); $i++) { 
                if (strtoupper($aux[$i]['nombre']) === 'SEPARADOR') {
                    //$cadenaMenu .= '<li class="divider"></li>';
                }else{
                    $cadenaMenu .= '<li><a onclick="cargarRuta(\''.URL::to($aux[$i]['link']).'\', \'container\');"><i class="'.$aux[$i]['icono'].'"></i> '.$aux[$i]['nombre'].'</a></li>';
                }
            }
            if (count($aux) > 0 && $hijos != '' ) {
                $cadenaMenu .= '<li class="divider"></li>';
            }
            if ($hijos != '') {
                $cadenaMenu .= $hijos;
            }
            $cadenaMenu .= '</ul>';
            $cadenaMenu .= '</li>';
        }
    }
    $cadenaMenu.='<li class="treeview">
            <a href="#"><i class="fa fa-lock"></i> <span>Aseguradoras</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
            <ul class="treeview-menu">
                <li> 
                    <a onclick="window.open(\'http://srvjavapp.lapositiva.com.pe:7780/webclinic/\', \'_blank\');">  <i class="glyphicon glyphicon-expand"></i> La Positiva</a>
                </li>
                <li>
                    <a onclick="window.open(\'http://www.feban.net/jdextre/clinicas2009/index.php\', \'_blank\');">  <i class="glyphicon glyphicon-expand"></i> Feban</a>
                </li>
                <li>
                    <a onclick="window.open(\'https://www.pacificoseguros.com/loginPacifico/login.aspx\', \'_blank\');">  <i class="glyphicon glyphicon-expand"></i> Pacifico Seguros</a>
                </li>
                <li>
                    <a onclick="window.open(\'http://www.rel.rimac.com.pe/AAAWWeb/logueo_aplicaciones.jsp\', \'_blank\');">  <i class="glyphicon glyphicon-expand"></i> Rimac</a>
                </li>
                <li>
                    <a onclick="window.open(\'https://portaladminusuarios.reniec.gob.pe/validacionweb/index.html\', \'_blank\');">  <i class="glyphicon glyphicon-expand"></i> Consulta DNI</a>
                </li>
            </ul>
        </li>';
    $cadenaMenu .= '</ul>';
    return $cadenaMenu;
}

function buscarHijos($categoriaopcionmenu_id, $tipousuario_id)
{
    $menu = array();
    $categoriaopcionmenu = new Menuoptioncategory();
    $opcionmenu          = new Menuoption();
    $permiso             = new Permission();

    $catHijos = $categoriaopcionmenu->where('menuoptioncategory_id', '=', $categoriaopcionmenu_id)->orderBy('order', 'ASC')->get();
    $cadenaMenu = '';
    foreach ($catHijos as $key => $catHijo) {
        $usar = false;
        $aux = array();
        $hijos = buscarHijos($catHijo->id, $tipousuario_id);
        $opciones = $opcionmenu->where('menuoptioncategory_id', '=', $catHijo->id)->orderBy('order', 'ASC')->get();
        if ($opciones->count()) {

            foreach ($opciones as $key => $opcion) {
                $permisos = $permiso->where('menuoption_id', '=', $opcion->id)->where('usertype_id', '=', $tipousuario_id)->first();
                if ($permisos) {
                    $usar = true;
                    $aux2 = $opcionmenu->find($permisos->menuoption_id);
                    $aux[] = array(
                        'nombre' => $aux2->name,
                        'link'   => $aux2->link,
                        'icono'  => $aux2->icon
                        );
                }
            }

        }
        if ($hijos != '' || $usar === true ) {

            $cadenaMenu .= '<li>';
            $cadenaMenu .= '<a href="#"><i class="'.$catHijo->icon.'"></i> '.$catHijo->name.'<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>';
            $cadenaMenu .= '<ul class="treeview-menu">';
            for ($i=0; $i < count($aux); $i++) { 
                if (strtoupper($aux[$i]['nombre']) === 'SEPARADOR') {
                    //$cadenaMenu .= '<li class="divider"></li>';
                } else {
                    $cadenaMenu .= '<li><a onclick="cargarRuta(\''.URL::to($aux[$i]['link']).'\', \'container\');"><i class="'.$aux[$i]['icono'].'" ></i> '.$aux[$i]['nombre'].'</a></li>';
                }
            }
            if (count($aux) > 0 && $hijos != '' ) {
                //$cadenaMenu .= '<li class="divider"></li>';
            }
            if ($hijos != '') {
                $cadenaMenu .= $hijos;
            }
            $cadenaMenu .= '</ul>';
            $cadenaMenu .= '</li>';
        }
    }
    return $cadenaMenu;
}
?>