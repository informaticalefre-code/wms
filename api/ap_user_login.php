<?php
    require '../clases/clase_usuario.php';
    require '../config/Database_mariadb.php';
    require '../config/funciones.php';

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json'); 
    // header("multipart/form-data; boundary=something; charset=UTF-8");
    // header("multipart/form-data");

    $username = strtolower(trim(test_input($_POST["username"])));
    $password = test_input($_POST["password"]);
    $database = new Db_mariadb();  // Nueva conexión a SQL Server
    $db       = $database->getConnection();
    $ousuario = new Usuario($db);
    $ousuario->carga_usuario($username);
    if (!$ousuario->error){/*Llave 1*/ 
        if($ousuario->verifica_password($password)){/*Llave 2*/
            session_unset();
            session_start();
            $_SESSION['username']  = $ousuario->user_name;
            $_SESSION['user_uuid'] = $ousuario->user_uuid;
            $_SESSION['user_menu'] = $menu_opciones = get_menu_opciones($ousuario->user_name); 
            $_SESSION['user_role'] = $ousuario->user_role;
            $ousuario = null;
            unset($username,$password,$database,$db,$ousuario);
            http_response_code(200);
            echo json_encode(array("message" => "Autorizado"));
        
        }else{
            $out = array('error_nro'=>40501,'error_msj'=>'Contraseña Invalida');
            $ousuario = null;
            unset($username,$password,$database,$db,$ousuario);
            http_response_code(403);
            echo json_encode($out);
        }
        
    }else {
        $out = array(
            'error_nro'=>$ousuario->error_nro,
            'error_msj'=>$ousuario->error_msj);
        $ousuario = null;
        unset($username,$password,$database,$db,$ousuario);
        http_response_code(20302);
        echo json_encode($out);
    }
?>

