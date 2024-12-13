<?php
    // Recuerdese que para recuperar clave nunca hay una sesión iniciada.
    // Si existe alguna hay error.
    require_once '../config/Database_mariadb.php';
    require_once '../clases/clase_usuario.php';
    require_once '../config/funciones.php';
    
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json; charset=UTF-8');
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");
    $metodo = $_SERVER["REQUEST_METHOD"];

    if ($metodo=='GET'){
        if (isset($_GET["token"])){
            $token = test_input($_GET["token"]);
            $out   = valida_usuario($token);
            unset($token);
            echo json_encode($out);
        }else{
            $out=[];
            $out["response"]   = "fail";
            $out["error_nro"]  = '45020';
            $out["error_msj"]  = 'Solicitud de recursos no valido. No se puede interpretar solicitud';
            $out["error_file"] = 'API';
            $out["error_line"] = 'API';
            $out["error_tpo"]  = 'error';
            http_response_code(400);
            echo json_encode($out);
        }
    }else{
        $out=[];
        $out["response"]   = "fail";
        $out["error_nro"]  = '45020';
        $out["error_msj"]  = 'Solicitud de recursos no valido. No se puede interpretar solicitud';
        $out["error_file"] = 'API';
        $out["error_line"] = 'API';
        $out["error_tpo"]  = 'error';
        http_response_code(400);
        echo json_encode($out);
    }

    /**************************************************************
    *                     VALIDA USUARIO
    **************************************************************/
    function valida_usuario($token){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $ousuario   = new Usuario($db_maria);
        if (empty($token)){
            $ousuario->error      = true ;
            $ousuario->error_nro  = '25302';
            $ousuario->error_msj  = 'Token no puede estar vacío';
            $ousuario->error_file = '';
            $ousuario->error_line = '';
        }

        if (!$ousuario->error){
            $db_maria->beginTransaction();
            $ousuario->valida_usuario($token);
        }

        if (!$ousuario->error){
            $db_maria->commit();
            session_unset();
            session_start();
            $_SESSION['username']  = $ousuario->user_name;
            $_SESSION['user_uuid'] = $ousuario->user_uuid ;
            $_SESSION['user_menu'] = get_menu_opciones($ousuario->user_name); 
            $out["response"] = "success";
            $out["texto"]    = "Usuario validado";
            http_response_code(200);
        }else{
            http_response_code(400);
            $db_maria->rollback();
            $out["response"]   = "fail";
            $out["error_nro"]  = $ousuario->error_nro;
            $out["error_msj"]  = $ousuario->error_msj;
            $out["error_file"] = $ousuario->error_file;
            $out["error_line"] = $ousuario->error_line;
            $out["error_tpo"]  = 'error';
        }
        $data_maria = $db_maria = $ousuario = null;
        unset($data_maria, $db_maria, $ousuario);

        return $out;
    }
?>