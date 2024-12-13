<?php
    require_once 'user-auth-api.php';
    require_once '../config/Database_mariadb.php';
    require_once '../config/funciones.php';
    require_once '../clases/clase_packing_pista.php';

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: text/html; charset=UTF-8");
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");
   
    $metodo = $_SERVER["REQUEST_METHOD"];
    if ($metodo=='GET'){
        if (isset($_GET["pista"])){
            $out["html"] = carga_packing_pista(test_input($_GET["pista"]));
            unset($_GET["pista"]);
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
    }

    /*************************************************************************
    *                 CARGA TAREA DE PACKING
    *************************************************************************/
    function carga_packing_pista($pista){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $opista     = new Packing_Pista($db_maria);
        $opista->pista = $pista;
        $opista->carga_packing_pista();
        $out = $opista->html_packing_pista();
        $opista = $pista = null;
        unset($opista, $pista);
        $db_maria = $data_maria = null;
        unset($db_maria, $data_maria);
        return $out;
    }
?>