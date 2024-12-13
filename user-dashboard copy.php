<?php
    /* Cuando un usuario valida su cuenta por primera vez se le manda un link a su correo
       que lo dirije a esta pantalla o programa.*/
    require 'user-auth.php';
    require_once 'header.php';
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if (isset($_GET["token"])) {
            require 'funciones.php';
            require 'db-mysql.php';
            require 'clases/clase_usuario.php';
            $user_token = test_input($_GET["token"]);
            $user_token = filter_var($_GET["token"], FILTER_SANITIZE_STRING);
            $usuario = new usuario(0); // Sin Id empresa. Se lee de la base de datos al buscar el Token.
            $resp = $usuario->valida_token($con_mysql,$user_token,'REGISTRO');
            if ($resp){
                $_SESSION['empresa_id'] = $usuario->user_idempresa;
                $_SESSION['username']   = $usuario->user_name;
                $_SESSION['user_id']    = $usuario->user_id;
                unset($user_token,$resp);
            }else{
                echo '<div class="alert alert-danger" role="alert">';
                echo 'Llamada ilegal al programa.'.'<br>';
                echo 'El enlace no es válido.'.'<br>';
                echo 'Posibles causas:'."<br>";
                echo 'El enlace tiene una validez de 24 horas.' . '<br>';
                echo 'El usuario ya se encuentra activo. Si este es el caso entra directamente a www.elferretero.com' . '<br>';
                echo 'Si tienes problemas con tu usuario envía un correo a ferretero@gmail.com' . '<br>';
                echo 'programa terminado.';
                echo '</div>';
                die;
            }
        }
    }
?>
	<!-- Personales CSS  ----------------------------------------------->
	<link rel="stylesheet" type="text/css" href="css/user-registro.css">
</head>
<body>
	<?php require 'barra_menu.php'?>
	<main id="main-content" style="display:flex;">
        <div id="form-container">
            <p class="h2">TABLERO USUARIO</p>
            <hr style="border: 0;width: 96%;background-color:#C8C8C8;height:2px;margin:0px 0px 8px 0px;">
            <p>Esta es un area segura</p>
        <div>            
    </main>
</body>
</html>
