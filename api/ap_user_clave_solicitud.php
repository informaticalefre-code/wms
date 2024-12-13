<?php
    require '../clases/clase_usuario.php';
    require '../config/Database_mariadb.php';
    require '../config/funciones.php';

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json'); 

    $user_email = test_input($_POST["user_email"]);
    $database   = new Db_mariadb();  // Nueva conexión a SQL Server
    $db         = $database->getConnection();
    $ousuario   = new Usuario($db);
    $out = $ousuario->enlace_recuperar_clave($user_email);
    if (!$out){
        $lcerror = $ousuario->error_msj;
        echo $lcerror."\n";
    }else{
        $lcerror = 'Correo enviado con exito';
        echo "correo enviado con exito\n";
    }
?>
