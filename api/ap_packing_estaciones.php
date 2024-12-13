<?php
    require_once 'user-auth-api.php';
    require_once '../config/Database_mariadb.php';
    require_once '../config/funciones.php';

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json; charset=UTF-8'); 
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");    

    $metodo = $_SERVER["REQUEST_METHOD"];
    if ($metodo=='GET'){
        if (isset($_GET["id"]) && $_GET["id"]== 'todas'){
            $estaciones        = get_packing_estaciones();
            $aembaladores      = lista_embaladores();
            $html_embaladores  = html_embaladores($aembaladores);
            $out["html_lista"] = html_estaciones($estaciones,$html_embaladores);
            $estaciones = null;
            unset($estaciones, $_GET["accion"], $_GET["id"]);
            http_response_code(200);
            echo json_encode($out);
        }elseif (isset($_GET["accion"]) && $_GET["accion"]== 'packing-printer'){
            /* Devuelve la impresora instalada en la mesa a la que fue asignado
               un embalador */
            $username = $_SESSION['username'];
            $printer  = label_printer($username);
            unset($username, $_GET["accion"]);
            http_response_code(200);
            echo json_encode($printer);            
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
    }elseif ($metodo == 'POST'){
        $out = update_estaciones_data();
        http_response_code(200);
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




    /*************************************************************************
    *                          GET PACKING ESTACION
    *************************************************************************/
    function get_packing_estaciones(){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $sql  = "select pace_idestacion, pace_printer, pace_username FROM tpacking_estaciones ORDER BY pace_idestacion";
        $stmt = $db_maria->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        $data_maria = $db_maria = $sql = $stmt = null;
        unset($data_maria, $db_maria, $sql, $stmt);
        return ($resp);
    }



    /******************************************************
    *                 HTML ESTACIONES
    *******************************************************/
    function html_estaciones($aestaciones,$html_embaladores){
        $out = '';
        if (!empty($aestaciones)){
            for ($i=0; $i<count($aestaciones); $i++):
                $out .= '<div class="estacion-card" id="estacion-'.$aestaciones[$i]["pace_idestacion"].'">';
                $out .= '<section class="estacion-card-header text-white">Mesa&nbsp'.$aestaciones[$i]["pace_idestacion"].'</section>';
                $out .= '<input id="input-estacion" class="form-control" type="hidden" name="input-estacion[]" value="'.$aestaciones[$i]["pace_idestacion"].'">';
                $out .= '<input id="input-accion" class="form-control" type="hidden" name="accion[]" value="SINCAMBIOS">';
                $out .= '<section class="estacion-card-body">';
                $out .= '<div class="row">';
                $out .= '<label for="input-printer" class="form-label mb-0">Impresora</label>';
                $out .= '<input type="text" class="form-control" id="input-printer" name="input-printer[]" onchange="change_record('.$aestaciones[$i]["pace_idestacion"].');" placeholder="Impresora" value="'.$aestaciones[$i]["pace_printer"].'">';
                $out .= '</div>';
                $out .= '<div class="row">';
                $out .= '<label for="input-embalador" class="form-label mb-0">Embalador</label>';
                $out .= '<select class="form-select" id="input-status" name="input-embalador[]" onchange="change_record('.$aestaciones[$i]["pace_idestacion"].');" aria-label="Usuario embalador">';
                $out .= '<option value="'.$aestaciones[$i]["pace_username"].'" selected>'.$aestaciones[$i]["pace_username"].'</option>';
                $out .= $html_embaladores;
                $out .= '</select>';
                $out .= '</div>';
                $out .= '</section>';
                $out .= '</div>';
            endfor;
            $out .= '</section></div>';
        }else{
            $out .= '<div style="text-align:center;">no hay estaciones de embalaje</div>';
        }            
        unset($bin,$link);
        return $out;
    }

    /*************************************************************************
    *                            lista_embaladores
    * Genera un array con los usuarios que tengan el rol de "Embalador"
    *************************************************************************/
    function lista_embaladores(){
        $resp       = [];
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $sql = "SELECT a.user_name, a.user_nombre, a.user_apellido FROM vusuarios a WHERE a.user_activo = 1 AND EXISTS (SELECT z.usrr_role FROM tusuarios_roles z WHERE z.usrr_role = 'EMBALADOR' AND z.usrr_name = a.user_name) ORDER BY 1";

        $stmt = $db_maria->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        $data_maria = $db_maria = $sql = $stmt = null;
        unset($data_maria, $db_maria, $sql, $stmt);
        return ($resp);
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
    *                     UPDATE ESTACIONES DATA
    *************************************************************************/
    function update_estaciones_data(){
        $error      = false;
        $aerror     = [];
        $adatos = [];
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();

        // Arrays de acciones
        $amodificados = array_keys($_POST["accion"],"MODIFICADO");

        $db_maria->beginTransaction();

        // Vamos con los posibles productos modificados al pedido.
        if (count($amodificados)> 0){
            foreach($amodificados as $fila):
                $adatos["idestacion"] = test_input($_POST["input-estacion"][$fila]);
                $adatos["printer"]    = test_input($_POST["input-printer"][$fila]);
                $adatos["username"]   = test_input($_POST["input-embalador"][$fila]);
                $adatos["user_mod"]   = $_SESSION['username'];

                $sql = "UPDATE tpacking_estaciones
                        SET pace_printer = :printer, pace_username = :username, user_mod = :user_mod
                        WHERE pace_idestacion = :idestacion";
            
                $stmt = $db_maria->prepare($sql);
            
                if (!$error){
                    try {
                        $stmt->execute($adatos);
                        if ($stmt->rowcount() !== 1){
                            $error = true;
                            $aerror["error_nro"] = '45000';
                            $aerror["error_msj"] = 'No se actualizó el registro para el producto '.$adatos["idproducto"].' en la Tarea de Picking '.$adatos["idpicking"];
                            $aerror["error_tpo"] = 'error';
                        }
                    }catch (PDOException $e) {
                        $error = true;
                        $aerror["error_nro"]  = $e->getCode();
                        $aerror["error_msj"]  = $e->getMessage();
                        $aerror["error_file"] = $e->getfile();
                        $aerror["error_line"] = $e->getLine();
                        $aerror["error_tpo"]  = 'error';
                    }catch (Exception $e) {
                        $error = true;
                        $aerror["error_nro"]  = $e->getCode();
                        $aerror["error_msj"]  = $e->getMessage();
                        $aerror["error_file"] = $e->getfile();
                        $aerror["error_line"] = $e->getLine();
                        $aerror["error_tpo"]  = 'error';
                    }
                }
            endforeach;
            $sql = $stmt = null;
            unset($sql, $stmt);
        }

        if (!$error){
            $db_maria->commit();
            $out["response"] = "success";
            $out["texto"]    = "datos guardados con exito.";
        }else{
            $db_maria->rollback();
            $out["response"]   = "fail";
            $out["error_nro"]  = $aerror["error_nro"];
            $out["error_msj"]  = $aerror["error_msj"];
            $out["error_file"] = $aerror["error_file"];
            $out["error_line"] = $aerror["error_line"];
            $out["error_tpo"]  = 'error';            
        }

        $adatos = null;    
        $data_maria = $db_maria = $amodificados = null;
        $_POST["input-estacion"] = $_POST["input-printer"] = $_POST["input-username"] = null;
        unset ($adatos);
        unset($data_maria, $db_maria,$amodificados);
        unset($_POST["input-estacion"], $_POST["input-printer"], $_POST["input-username"]);
        return $out;
    }


    /*************************************************************************
    *                           LABEL PRINTER
    * Devuelve la impresora asignada 
    *************************************************************************/
    function label_printer($embalador){
        $out = [];
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $sql = "SELECT pace_printer FROM tpacking_estaciones WHERE pace_username = :username";

        $stmt = $db_maria->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':username', $embalador, PDO::PARAM_STR);
        $stmt->bindColumn(1, $printer);
        $stmt->execute();
        $stmt->fetch(PDO::FETCH_BOUND);

        if ($stmt->rowcount() == 1){
            $out["response"] = "success";
            $out["printer"]  = $printer;
        }else{
            $out["response"]   = "fail";
            $out["error_nro"]  = '27000';
            $out["error_msj"]  = 'El embalador no está asociado a ninguna estación o mesa de embalaje.';
            $out["error_file"] = null;
            $out["error_line"] = null;
            $out["error_tpo"]  = 'error';           
        }
        
        $data_maria = $db_maria = $sql = $stmt = $printer = null;
        unset($data_maria, $db_maria, $sql, $stmt, $printer);
        return ($out);
    }
?>