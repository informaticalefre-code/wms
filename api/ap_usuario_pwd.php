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

    if ($metodo=='PATCH'){
        $data       = json_decode(file_get_contents("php://input"));
        $accion     = test_input($data->accion);
        if ($accion == 'clave-cambiar'){
            $password = test_input($data->password);
            $token    = test_input($data->token);
            $out = cambia_password($password, $token);
            unset($accion,$password,$token);
            http_response_code(200);
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
    *                  CAMBIA PASSWORD
    **************************************************************/
    function cambia_password($password, $token){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $ousuario   = new Usuario($db_maria);
        if (!empty($token)){
            if (!$ousuario->valida_token($token)){
                $ousuario->error      = true ;
                $ousuario->error_nro  = '25301';
                $ousuario->error_msj  = 'Enlace para cambiar contraseña no es valido o ha expirado';
                $ousuario->error_file = '';
                $ousuario->error_line = '';
            }
        }else{
            session_start();
            if (!$ousuario->valida_uuid($_SESSION['user_uuid'])){
                $ousuario->error      = true ;
                $ousuario->error_nro  = '25301';
                $ousuario->error_msj  = 'Enlace para cambiar contraseña no es valido o ha expirado';
                $ousuario->error_file = '';
                $ousuario->error_line = '';
            }
        }

        $db_maria->beginTransaction();
        $ousuario->cambia_clave_usuario($password);

        if (!$ousuario->error){
            $db_maria->commit();
            session_unset(); // Quito todos los valores asociados a la sesión.
            if (session_status() == PHP_SESSION_ACTIVE){
                session_destroy();
            }
            $out["response"] = "success";
            $out["texto"]    = "Contraseña cambiada con éxito";
        }else{
            $db_maria->rollback();
            $out["response"]   = "fail";
            $out["error_nro"]  = $ousuario->error_nro;
            $out["error_msj"]  = $ousuario->error_msj;
            $out["error_file"] = $ousuario->error_file;
            $out["error_line"] = $ousuario->error_line;
            $out["error_tpo"]  = 'error';
        }
        return $out;
    }

?>