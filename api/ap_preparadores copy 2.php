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
        if (isset($_GET["accion"]) && $_GET["accion"]==='assign-stats'){
            $preparador = test_input($_GET["preparador"]);
            $out        = carga_preparadores_stats($preparador);
            http_response_code(200);
            unset($preparador,$_GET["preparador"],$_GET["accion"]);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        }elseif (isset($_GET["accion"]) && $_GET["accion"]==='preparadores'){
            $out = lista_preparadores();

            if ($out["response"]== "success"){
                $out["html_preparadores"] = html_preparadores($out["preparadores"]);
                unset($out["preparadores"]);
                http_response_code(200);
            }else{
                http_response_code(404);
            }
            unset($_GET["accion"]);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        }elseif (isset($_GET["accion"]) && $_GET["accion"]==='embaladores'){
            $out = lista_embaladores();
            if ($out["response"]== "success"){
                $out["html_embaladores"] = html_embaladores($out["embaladores"]);
                unset($out["embaladores"]);
                http_response_code(200);
        }elseif (isset($_GET["accion"]) && $_GET["accion"]==='verificador'){
            $out = lista_verificador();
            if ($out["response"]== "success"){
                $out["html_verificador"] = html_verificador($out["verificador"]);
                unset($out["verificador"]);
                http_response_code(200);
            }else{
                http_response_code(404);
            }
            unset($_GET["accion"]);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        }elseif (isset($_GET["accion"]) && $_GET["accion"]=='usuarios-almacen'){
            $out = usuarios_almacen();
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
        echo json_encode($out);
    }

    
    /*************************************************************************
    *                      CARGA PREPARADORES STATS
    * Si el parametro $preparador es igual a "todos", trae todas la 
    * información estadística de todos los preparadores.
    *************************************************************************/
    function carga_preparadores_stats($preparador){
        $error      = false;
        $error_nro  = null;
        $error_msj  = null;
        $resp       = [];
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $sql = "SELECT * FROM vstats_preparador2 a ";
        if ($preparador !== 'todos'){
            $sql .= 'where a.user_name = :preparador ';
        }

        $sql .= "ORDER BY a.porcentaje, a.cant_sku";

        $stmt = $db_maria->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        if ($preparador !== 'todos'){
            $stmt->bindparam(':preparador',$preparador, PDO::PARAM_STR);
        }            

        try {
            $stmt->execute();
        }catch (PDOException $e) {
            $error      = true;
            $error_nro  = $e->getCode();
            $error_msj  = $e->getMessage();
        }catch (Exception $e) {
            $error      = true;
            $error_nro  = $e->getCode();
            $error_msj  = $e->getMessage();
        }

        if (!$error){
            $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        }
        
        $out=[];
        if (!$error){
            $out["response"]   = "success";
            $out["texto"]      = "datos guardados con exito.";
            $out["html_prepa"] = html_preparador_card($resp);
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
    *                         HTML PREPARADOR CARD
    * Genera el HTML para las tarjetas de los preparadores.
    *************************************************************************/
    function html_preparador_card($resp){
        $html  = '';
        for ($i=0; $i<count($resp); $i++):
            $html .= '<div id="'.$resp[$i]['user_name'].'" class="preparador-card">';
            $html .= '<section class="preparador-header text-white">';
            $html .= '<p class="mb-0">'.$resp[$i]['user_name'].'</p>';
            $html .= '</section>';
            $html .= '<section class="preparador-body" style="width:100%; padding: 2% 5%;">';
            $html .= '<div class="progress">';
            $html .= '<div class="progress-bar" role="progressbar" style="width:'.$resp[$i]['porcentaje'].'%;" aria-valuenow="'.$resp[$i]['porcentaje'].'" aria-valuemin="0" aria-valuemax="100">'.$resp[$i]['porcentaje'].'%</div>';
            $html .= '</div>';            
            $html .= '<div>';
            $html .= '<p class="mb-0">Tareas='.$resp[$i]['tareas'].'</p>';
            // $html .= '<p class="mb-0">Avance='.$resp[$i]['porcentaje'].'</p>';
            $html .= '</div>';
            $html .= '<div>';
            $html .= '<p class="mb-0">SKU='.$resp[$i]['cant_sku'].'</p>';
            $html .= '<p class="mb-0">Productos='.$resp[$i]['cant_productos'].'</p>';
            $html .= '</div>';
            $html .= '</section>';
            $html .= '</div>';
        endfor;
        $i = $resp = null;
        unset($i, $resp);
        return $html;
    }


    /*************************************************************************
    *                            lista_preparadores
    * Genera un array con los usuarios que tengan el rol de "preparador"
    *************************************************************************/
    function lista_preparadores(){
        $error      = false;
        $error_nro  = null;
        $error_msj  = null;
        $resp       = [];
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $sql = "SELECT a.user_name, a.user_nombre, a.user_apellido FROM vusuarios a WHERE a.user_activo = 1 AND EXISTS (SELECT z.usrr_role FROM tusuarios_roles z WHERE z.usrr_role = 'PREPARADOR' AND z.usrr_name = a.user_name) ORDER BY 1";

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
            $out["response"]     = "success";
            $out["preparadores"] = $resp;
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
    *                            lista_embaladores
    * Genera un array con los usuarios que tengan el rol de "Embalador"
    *************************************************************************/
    function lista_embaladores(){
        $error      = false;
        $error_nro  = null;
        $error_msj  = null;
        $resp       = [];
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $sql = "SELECT a.user_name, a.user_nombre, a.user_apellido FROM vusuarios a WHERE a.user_activo = 1 AND EXISTS (SELECT z.usrr_role FROM tusuarios_roles z WHERE z.usrr_role = 'EMBALADOR' AND z.usrr_name = a.user_name) ORDER BY 1";

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
            $out["response"]  = "success";
            $out["embaladores"] = $resp;
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
    *                            lista_verificador
    * Genera un array con los usuarios que tengan el rol de "Verificador"
    *************************************************************************/
    function lista_verificador(){
        $error      = false;
        $error_nro  = null;
        $error_msj  = null;
        $resp       = [];
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $sql = "SELECT a.user_name, a.user_nombre, a.user_apellido FROM vusuarios a WHERE a.user_activo = 1 AND EXISTS (SELECT z.usrr_role FROM tusuarios_roles z WHERE z.usrr_role IN ('VERIFICADOR','PREPARADOR') AND z.usrr_name = a.user_name) ORDER BY 1";

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
            $out["response"]  = "success";
            $out["verificador"] = $resp;
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
    *                         HTML PREPARADORES
    *************************************************************************/
    function html_preparadores($resp){
        $html  = '';
        $i = 0;
        if (!empty($resp) > 0) {
            for ($i=0; $i<count($resp); $i++):
                $html .= '<option value="'.$resp[$i]['user_name'].'">'.$resp[$i]['user_name'].'</option>';
            endfor;
        }
        $i = $resp = null;
        unset($i, $resp);
        return $html;
    }

    /*************************************************************************
    *                         HTML EMBALADORES
    *************************************************************************/
    function html_embaladores($resp){
        $html  = '';
        $i = 0;
        if (!empty($resp) > 0) {
            for ($i=0; $i<count($resp); $i++):
                $html .= '<option value="'.$resp[$i]['user_name'].'">'.$resp[$i]['user_name'].'</option>';
            endfor;
        }
        $i = $resp = null;
        unset($i, $resp);
        return $html;
    }

    /*************************************************************************
    *                         HTML VERIFICADOR
    *************************************************************************/
    function html_verificador($resp){
        $html  = '';
        $i = 0;
        if (!empty($resp) > 0) {
            for ($i=0; $i<count($resp); $i++):
                $html .= '<option value="'.$resp[$i]['user_name'].'">'.$resp[$i]['user_name'].'</option>';
            endfor;
        }
        $i = $resp = null;
        unset($i, $resp);
        return $html;
    }

    /***********************************************************************
    *                    USUARIOS ALMACEN
    ***********************************************************************/
    function usuarios_almacen(){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();

        // Armamos el SQL
        $sql  = "SELECT user_name FROM vusuarios_almacen ORDER BY 1";
        
        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->execute();
        $resp = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = array_column($resp,"user_name");

        $db_sqlsrv = $data_sqlsrv = $sql = $stmt = $resp = null;
        unset($db_sqlsrv, $data_sqlsrv, $sql, $stmt, $resp);
        return $out;
    }    

?>