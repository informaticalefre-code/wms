<?php
    require_once 'user-auth-api.php';
    require_once '../clases/clase_pedido.php';
    require_once '../config/Database_mariadb.php';
    require_once '../config/funciones.php';

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json; charset=UTF-8'); 
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");

    $metodo = $_SERVER["REQUEST_METHOD"];
    if ($metodo=='GET'){
        if (isset($_GET["accion"]) && $_GET["accion"]==='user-roles'){
            $out = lista_roles();
            if ($out["response"]== "success"){
                $out["html_roles"] = html_roles($out["rol_nombre"]);
                unset($out["rol_nombre"]);
                http_response_code(200);
            }else{
                http_response_code(404);
            }
            unset($_GET["accion"]);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
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
        echo json_encode($out);
    }

    
    /*************************************************************************
    *                    LISTA ROLES
    * Carga en un array todos los roles de usuarios
    *************************************************************************/
    function lista_roles(){
        $error      = false;
        $error_nro  = null;
        $error_msj  = null;
        $resp       = [];
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $sql = "SELECT rol_nombre FROM troles ORDER BY rol_id";

        $stmt = $db_maria->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

        try {
            $stmt->execute();
            $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            $error      = true;
            $error_nro  = $e->getCode();
            $error_msj  = $e->getMessage();
        }catch (Exception $e) {
            $error      = true;
            $error_nro  = $e->getCode();
            $error_msj  = $e->getMessage();
        }

        $out=[];
        if (!$error){
            $out["response"]   = "success";
            $out["rol_nombre"] = $resp;
        }else{
            $out["error_nro"] = $error_nro;
            $out["error_msj"] = $error_msj;
        }
        $data_maria = $db_maria = $sql = $stmt = $resp = null;
        $error = $error_nro = $error_msj  = null;
        unset($data_maria, $db_maria, $sql, $stmt, $resp);
        unset($error, $error_nro, $error_msj);
        return ($out);
    }



    /*************************************************************************
    *                         HTML ROLES
    *************************************************************************/
    function html_roles($resp){
        $html  = '';
        $i = 0;
        if (!empty($resp) > 0) {
            for ($i=0; $i<count($resp); $i++):
                $html .= '<option value="'.$resp[$i]['rol_nombre'].'">'.$resp[$i]['rol_nombre'].'</option>';
            endfor;
        }
        $i = $resp = null;
        unset($i, $resp);
        return $html;
    }

?>