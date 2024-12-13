<?php
    require 'user-auth-frontend.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, minimum-scale=1,minimum-scale=1"> 
    <meta charset="utf-8">
    <meta http-equiv="Cache-Control" conten="max-age=21600">
    <link rel="icon" type="image/jpg" sizes="16x16" href="img/favicon/favicon-16x16.jpg">
    <link rel="icon" type="image/jpg" sizes="32x32" href="img/favicon/favicon-32x32.jpg">
    <link rel="icon" type="image/jpg" sizes="96x96" href="img/favicon/favicon-96x96.jpg">
    <title>Lefre WMS Alfa 1</title>
    <!-- Iconos Iconmoon      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="pluggins/icomoon-v1.0/style.css">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/index.css">
</head>
<body>
    <main id="main_screen">
        <h1>Gestión de Almacén (v1.0)<i class="icon-home3"></i></h1>
        <!-- Contenedor -->
        <ul id="accordion" class="accordion">
            <?php 
                if(!isset($_SESSION["user_menu"])){
                    header("Location: user-login.php");
                    exit(); 
                }
                if (in_array(1,$_SESSION['user_menu'])){
                    echo <<<EOL
                    <li>
                        <div class="link"><i class="icon-order-2"></i>Pedidos<i class="icon-circle-down"></i></div>
                        <ul class="submenu">
                            <li><a href="pedidos_lista.php">Lista de Pedidos</a></li>
                        </ul>
                    </li>
                    EOL;
                }
                if (in_array(2,$_SESSION['user_menu'])){
                    echo <<<EOL
                    <li class="default open">
                        <div class="link"><i class="icon-trolley-2"></i>Picking<i class="icon-circle-down"></i></div>
                        <ul class="submenu">
                            <!-- <li><a href="pedidos_listpick.php">Selección de Tareas</a></li> -->
                            <li><a href="picking_tareas.php">Tareas Asignadas</a></li>
                            <li><a href="picking_pistas.php">Verificación Picking</a></li>
                        </ul>
                    </li>
                    EOL;
                }
                if (in_array(3,$_SESSION['user_menu'])){
                    echo <<<EOL
                    <li>
                        <div class="link"><i class="icon-pack-1"></i>Packing<i class="icon-circle-down"></i></div>
                        <ul class="submenu">
                        <li><a href="packing_pistas.php">Selección de Tareas</a></li>
                        </ul>
                    </li>
                    EOL;
                }
                if (in_array(4,$_SESSION['user_menu'])){
                    echo <<<EOL
                    <li>
                        <div class="link"><i class="icon-transport-14"></i>Transporte<i class="icon-circle-down"></i></div>
                        <ul class="submenu">
                            <li><a href="#">Creación de Guías</a></li>
                            <li><a href="#">Entrega de Facturas</a></li>
                            <li><a href="#">Carga en Camión</a></li>
                        </ul>
                    </li>
                    EOL;
                }
                // Comentar el IF ////////////////////////////////////////
                // if ($_SESSION['username'] == 'padronc' || 
                //        $_SESSION['username'] == 'lmarquez' ||
                //        $_SESSION['username'] == 'garciniega' ||
                //        $_SESSION['username'] == 'buyona' ||
                //        $_SESSION['username'] == 'granadosf' ||
                //        $_SESSION['username'] == 'reyesf' ){
                    if (in_array(5,$_SESSION['user_menu'])){
                        echo <<<EOL
                        <li>
                            <div class="link"><i class="icon-storehouse-1"></i>Almacén<i class="icon-circle-down"></i></div>
                            <ul class="submenu">
                                <li><a href="prod_almacen.php">Ubicación de Productos</a></li>
                                <li><a href="productos_reservados.php">Productos Reservados</a></li>
                                <li><a href="rep_almacen.php">Consultas y Reportes</a></li>
                                <!-- <li><a href="#">Consultar Producto</a></li> -->
                            </ul>
                        </li>
                        EOL;
                    }
                // }
                if ( $_SESSION['username'] == 'lmarquez'  ){
                    echo <<<EOL
                    <li>
                            <div class="link"><i class="icon-storehouse-1"></i>Listado de Packing<i class="icon-circle-down"></i></div>
                            <ul class="submenu">
                                <li><a href="packing_lista.php">Listado de Packing</a></li>
                            </ul>
                    </li>
                    EOL;
                }
                if (in_array(6,$_SESSION['user_menu'])){
                    echo <<<EOL
                    <li>
                        <div class="link"><i class="icon-equalizer2"></i>Torre de Control<i class="icon-circle-down"></i></div>
                        <ul class="submenu">
                            <li><a href="pedidos_assign.php">Asignación de Tareas</a></li>
                            <li><a href="picking_master.php">Tareas de Picking</a></li>
                            <li><a href="packing_master.php">Tareas de Packing</a></li>
                            <li><a href="packing_estaciones.php">Estaciones de Embalaje</a></li>
                            <li><a href="rep_torrectrl.php">Consultas y Reportes</a></li>
                            <li><a href="user_lista.php">Usuarios</a></li>
                            <li><a href="user-registro.php">Registrar Usuario</a></li>
                        </ul>
                    </li>
                    EOL;
                }
                if ($_SESSION['username'] == 'padronc' || 
                       $_SESSION['username'] == 'lmarquez' ||
                       $_SESSION['username'] == 'buyona' ||
                       $_SESSION['username'] == 'garciniega' ||
                       $_SESSION['username'] == 'granadosf' ||
                       $_SESSION['username'] == 'reyesf' ){               
                    if (in_array(7,$_SESSION['user_menu'])){
                        echo <<<EOL
                        <li>
                            <div class="link"><i class="icon-order-2"></i>Inventarios<i class="icon-circle-down"></i></div>
                            <ul class="submenu">
                                <li><a href="inv_procesos.php">Procesos de Inventario</a></li>
                            </ul>
                        </li>
                        EOL;
                    }
                } 
            ?>                
            <li>
                <div class="link"><i class="icon-user-8"></i>Usuario<i class="icon-circle-down"></i></div>
                <ul class="submenu">
                    <li><a href="#"><?php echo $_SESSION['username']?></a></li>
                    <!-- <li><a href="inv_conteos.php">Conteo Físico de Inventario</a></li> -->
                    <li><a href="user-clave-cambio.php">Cambiar Contraseña</a></li>
                    <li><a href="user-logout.php">Salir</a></li>
                </ul>
            </li>
        </ul>
    </main>
    <script src='pluggins/jquery/jquery-3.6.0.min.js'></script>
    <script id="rendered-js">
        $(function () {
        var Accordion = function (el, multiple) {
            this.el = el || {};
            this.multiple = multiple || false;

            // Variables privadas
            let links = this.el.find('.link');
            // Evento
            links.on('click', { el: this.el, multiple: this.multiple }, this.dropdown);
        };

        Accordion.prototype.dropdown = function (e) {
            let $el = e.data.el;
            $this = $(this),
            $next = $this.next();

            $next.slideToggle();
            $this.parent().toggleClass('open');

            if (!e.data.multiple) {
            $el.find('.submenu').not($next).slideUp().parent().removeClass('open');
            };
        };

        var accordion = new Accordion($('#accordion'), false);
        });
    </script>
</body>
</html>