<?php
    require_once 'user-auth-api.php';
    require_once '../clases/clase_inventario.php';
    require_once '../config/Database_mariadb.php';
    require_once '../config/funciones.php';

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json; charset=UTF-8'); 
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");
    $metodo = $_SERVER["REQUEST_METHOD"];
    if ($metodo=='POST'){
        if (isset($_POST["accion"]) && $_POST["accion"]=="lista-inventarios"){
            $bloque  = intval(filter_var(test_input($_POST["bloque"]), FILTER_SANITIZE_NUMBER_INT));
            $filtros = query_filtros();
            $lista   = query_inventarios($filtros, $bloque);
            $out["html_lista"] = html_lista_master($lista);
            http_response_code(200);
            $bloque = $filtros = $lista = null;
            unset($bloque, $filtros, $lista);
            unset($_POST["accion"], $_POST["search-text"], $_POST["search-options"], $_POST["bloque"]);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=="inventario-detalle"){
            $search_text   = test_input($_POST["search-text"]);
            $search_option = test_input($_POST["search-options"]);
            $idinventario  = test_input($_POST["idinventario"]);
            $bloque        = intval(filter_var(test_input($_POST["bloque"]), FILTER_SANITIZE_NUMBER_INT));
            $filtros       = query_filtros_detalle();
            $sql_obj       = set_detalle_sqlwhere($search_text, $search_option, $filtros);
            $lista         = query_detalle($idinventario, $sql_obj, $bloque);
            $out["html"]   = html_lista_detalle($lista);
            http_response_code(200);
            unset($bloque, $filtros, $lista);
            unset($_POST["accion"], $_POST["search-text"], $_POST["search-options"], $_POST["bloque"]);
            $bloque = $filtros = $lista = null;
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=="new-inventario"){
            $out = new_inventario();
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
    }elseif ($metodo=='GET'){
        if (isset($_GET["accion"]) && $_GET["accion"]==='contadores'){
            $out = inventario_contadores(test_input($_GET["id"]));
            http_response_code(200);
            echo json_encode($out);
        }elseif (isset($_GET["accion"]) && $_GET["accion"]==='producto-detalle'){
            $out = query_producto_detalle(test_input($_GET["id"]),test_input($_GET["idproducto"]));
            http_response_code(200);
            echo json_encode($out);
        }else{
            $out=[];
            $out["response"]   = 'fail';
            $out["error_nro"]  = '45020';
            $out["error_msj"]  = 'Solicitud de recursos no valido. No se puede interpretar solicitud';
            $out["error_file"] = 'API';
            $out["error_line"] = 'API';
            $out["error_tpo"]  = 'error';
            http_response_code(400);
            echo json_encode($out);
        }
    }elseif ($metodo=='PATCH'){
        $data = json_decode(file_get_contents("php://input"));
        if (isset($_GET["idinventario"]) && isset($_GET["idproducto"])){
            $out = update_inventario_detalle($data);
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

    /***********************************************************************
    *                         QUERY INVENTARIOS
    ***********************************************************************/
    function query_inventarios($filtros, $pbloque){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $block      = ($pbloque - 1) * 20;

        // Armamos el SQL
        $sql  = "SELECT inve_id, inve_estatus, inve_observacion, DATE_FORMAT(fec_crea, '%d/%m/%Y') as fec_crea, DATE_FORMAT(inve_fecierre, '%d/%m/%Y') as inve_fecierre FROM vinventarios";
        if (!empty($filtros)){
            $sql .= " ".$filtros;    
        }
        $sql .= " ORDER BY inve_id DESC";
        $sql .= " LIMIT :bloque,20";

        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt -> bindparam(':bloque', $block, PDO::PARAM_INT);

        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);

        $data_maria = $db_maria = $sql = $stmt = $block = null;
        $pbloque = null;
        unset($data_maria, $db_maria, $sql, $stmt, $block);
        unset($pbloque);
        return $resp;
    }


    /***********************************************************************
    *                         QUERY DETALLE
    ***********************************************************************/
    function query_detalle($idinventario, $sql_obj, $pbloque){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $block      = ($pbloque - 1) * 100;

        // Armamos el SQL
        $sql = "SELECT a.invd_id, a.invd_idproducto, a.invd_nombre, a.invd_unidad, a.invd_ubicacion, a.invd_existencia, a.invd_username1, a.invd_conteo1, a.invd_fecha1,
                a.invd_username2, a.invd_conteo2, a.invd_fecha2, a.invd_diferencia,  a.invd_username3, a.invd_conteo3, a.invd_username3, a.invd_fecha3, a.invd_definitivo
                FROM vinventarios_detalle a
                WHERE a.invd_id = :id";
        $sql .= " ".$sql_obj->sql_where;
        $sql .= " ".$sql_obj->sql_order;
        $sql .= " LIMIT :bloque,100";

        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        if (!empty($sql_obj->sql_value)):
            $stmt -> bindparam(':search_text', $sql_obj->sql_value, PDO::PARAM_STR);    
        endif;
        $stmt -> bindparam(':id', $idinventario, PDO::PARAM_INT);
        $stmt -> bindparam(':bloque', $block, PDO::PARAM_INT);

        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);

        $data_maria = $db_maria = $sql = $stmt = $block = null;
        $idinventario = $sql_obj = $pbloque = null;
        unset($data_maria, $db_maria, $sql, $stmt, $block);
        unset($idinventario, $sql_obj, $pbloque);
        return $resp;
    }

    /***********************************************************************
    *                   QUERY PRODUCTO DETALLE
    ***********************************************************************/
    function query_producto_detalle($idinventario, $idproducto){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();

        $sql = "SELECT a.invd_id, a.invd_idproducto, a.invd_nombre, a.invd_unidad, a.invd_existencia, a.invd_ubicacion, a.invd_username1, a.invd_conteo1, a.invd_fecha1,
                a.invd_username2, a.invd_conteo2, a.invd_fecha2, a.invd_diferencia,  a.invd_username3, a.invd_conteo3, a.invd_username3, a.invd_fecha3, a.invd_definitivo
                FROM vinventarios_detalle a
                WHERE a.invd_id = :id AND a.invd_idproducto = :idproducto";

        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt -> bindparam(':id', $idinventario, PDO::PARAM_INT);
        $stmt -> bindparam(':idproducto', $idproducto, PDO::PARAM_STR);

        $stmt->execute();
        $resp = $stmt->fetch(PDO::FETCH_ASSOC);

        $data_maria = $db_maria = $sql = $stmt = null;
        $idinventario = $idproducto = null;
        unset($data_maria, $db_maria, $sql, $stmt);
        unset($idinventario, $idproducto);
        return $resp;
    }

    /***********************************************************************
    *                    HTML LISTA MASTER
    ***********************************************************************/
    function html_lista_master($ainventario){
        $out = '';
        if (count($ainventario)> 0){
            foreach($ainventario as $fila):
                $out .= html_linea_master($fila);
            endforeach;
            $obj = null;
            unset ($obj);
            http_response_code(200);
        }else{
            $out  = '<div class="alert alert-danger" role="alert">';
            $out .= 'No se encontraron coincidencias';
            $out .= '</div>';
            http_response_code(404);
        }
        $apacking = null;
        unset($apacking);
        return ($out);
    }


    /***********************************************************************
    *                  HTML PaCKING LINEA
    ***********************************************************************/
    function html_linea_master($afila){
        $out  = '';
        $out .= '<tr id="'.$afila["inve_id"].'" class="picking_linea">';
        $out .= '<td>'.$afila["inve_id"].'</td>';
        $out .= '<td>'.$afila["fec_crea"].'</td>';
        $out .= empty($afila["inve_fecierre"]) ? '<td>&nbsp</td>' : '<td>'.$afila["inve_fecierre"].'</td>';
        $out .= '<td>';
        if ($afila["inve_estatus"] == 0){
            $out .= '<span style="font-size: 80%;" class="badge bg-danger">Anulado</span>';
        }elseif ($afila["inve_estatus"] == 1){
            $out .= '<span style="font-size: 80%;" class="badge bg-secondary">En Proceso</span>';
        }elseif ($afila["inve_estatus"] == 5){
            $out .= '<span style="font-size: 80%;" class="badge bg-success">Culminado</span>';
        }
        $out .= '</td>';
        $out .= empty($afila["inve_observacion"]) ? '<td>&nbsp</td>' : '<td>'.ucwords(strtolower($afila["inve_observacion"])).'</td>';
        $out .= '<td class="col_actions">';
        $out .= '<a href="'.$_SERVER['HTTP_ORIGIN'].'/wms/inv_detalle.php?id='.$afila["inve_id"].'"';
        $out .= '<button type="button" name="update" class="btn btn-info" id="detalle-'.$afila["inve_id"].'"><i class="icon-ver-detalle"></i></button>';
        $out .= '</a>';
        $out .= '<a href="'.$_SERVER['HTTP_ORIGIN'].'/wms/inv_panel01.php?id='.$afila["inve_id"].'"';        
        $out .= '<button type="button" name="panel" class="btn btn-secondary" id="panel-'.$afila["inve_id"].'" onclick="ver_panel('.$afila["inve_id"].');"><i class="icon-eye"></i></button>';
        $out .= '</a>';
        $out .= '<button type="button" name="update" class="btn btn-warning" id="edit-'.$afila["inve_id"].'" onclick="edit_proceso('.$afila["inve_id"].');"><i class="icon-edit-12"></i></button>';
        $out .= '</td>';
        $out .= '</tr>';
        $afila = null;
        unset($afila);
        return $out;
    }


    /***********************************************************************
    *                    HTML LISTA DETALLE
    ***********************************************************************/
    function html_lista_detalle($ainventario){
        $out = '';
        if (count($ainventario)> 0){
            foreach($ainventario as $fila):
                $out .= html_linea_detalle($fila);
            endforeach;
            $obj = null;
            unset ($obj);
            http_response_code(200);
        }else{
            $out  = '<div class="alert alert-danger" role="alert">';
            $out .= 'No se encontraron coincidencias';
            $out .= '</div>';
            http_response_code(404);
        }
        $apacking = null;
        unset($apacking);
        return ($out);
    }


    /***********************************************************************
    *                  HTML LINEA DETALLE
    ***********************************************************************/
    function html_linea_detalle($afila){
        $out  = '';
        $out .= '<tr id="'.$afila["invd_idproducto"].'">';
        $out .= '<td>'.$afila["invd_idproducto"].'</td>';
        $out .= '<td style="text-align:left;">'.ucwords(strtolower($afila["invd_nombre"])).'</td>';
        $out .= empty($afila["invd_ubicacion"]) ? '<td>&nbsp</td>' : '<td>'.$afila["invd_ubicacion"].'</td>';
        $out .= empty($afila["invd_existencia"] && $afila["invd_existencia"] !==0) ? '<td>&nbsp</td>' : '<td>'.$afila["invd_existencia"].'</td>';        
        $out .= empty($afila["invd_conteo1"]) && $afila["invd_conteo1"] !== 0 ? '<td id="table-conteo1">&nbsp</td>' : '<td id="table-conteo1">'.$afila["invd_conteo1"].'</td>';
        $out .= empty($afila["invd_conteo2"]) && $afila["invd_conteo2"] !== 0 ? '<td id="table-conteo2">&nbsp</td>' : '<td id="table-conteo2">'.$afila["invd_conteo2"].'</td>';
        if ($afila["invd_diferencia"] === 0){
            $out .= '<td id="table-resultado"><i style="color:green;" class="icon-checkmark"></td>';
        }elseif (!empty($afila["invd_diferencia"]) && $afila["invd_diferencia"] <> 0){
            $out .= '<td id="table-resultado"><i style="color:red;" class="icon-cross"></td>';
        }else{
            $out .= '<td id="table-resultado">&nbsp</td>';
        }
        $out .= empty($afila["invd_conteo3"]) && $afila["invd_conteo3"] !== 0 ? '<td id="table-conteo3">&nbsp</td>' : '<td id="table-conteo3">'.$afila["invd_conteo3"].'</td>';
        $out .= empty($afila["invd_username3"]) ? '<td id="table-username3">&nbsp</td>' : '<td id="table-username3">'.$afila["invd_username3"].'</td>';
        $out .= '<td class="col_actions">';
        $out .= '<button type="button" name="update" class="btn btn-warning" id="edit-'.$afila["invd_idproducto"].'" onclick="edit_detalle('.$afila["invd_id"].',\''.$afila["invd_idproducto"].'\');"><i class="icon-edit-12"></i></button>';
        $out .= '</td>';        
        $out .= '</tr>';
        $afila = null;
        unset($afila);
        return $out;
    }

    /***********************************************************************
    *                    QUERY FILTROS
    ***********************************************************************/
    function query_filtros(){
        $filtro = null;
        $afiltro1 = [];
        if (!isset($_POST["status-todos"])){
            if (isset($_POST["status-anulado"])){
                array_push($afiltro1,'0');
            }
            if (isset($_POST["status-proceso"])){
                array_push($afiltro1,'1');
            }
            if (isset($_POST["status-culminado"])){
                array_push($afiltro1,'5');
            }
        }

        if (!empty($afiltro1)){
            $filtro = "WHERE inve_estatus IN (".implode(', ', $afiltro1).")";
        }
        
        $afiltro1 = null;
        unset($afiltro1);
        return ($filtro);
    }

    /***********************************************************************
    *                    QUERY FILTROS
    ***********************************************************************/
    function query_filtros_detalle(){
        $filtro = $filtro1 = $filtro2 = '';
        if (!isset($_POST["detalle-todos"])){
            if (isset($_POST["detalle-pendientes"])){
                $filtro1 = ' (a.invd_conteo1 IS NULL OR a.invd_conteo2 IS NULL OR (a.invd_conteo1 <> a.invd_conteo2 AND a.invd_conteo3 IS NULL))';
            }
            if (isset($_POST["detalle-culminados"])){
                $filtro2 = ' (a.invd_conteo1 IS NOT NULL AND a.invd_conteo2 IS NOT NULL OR (a.invd_conteo1 <> a.invd_conteo2 AND a.invd_conteo3 IS NOT NULL))';
            }
        }

        if (!isset($_POST["difconteo-todos"])){
            if (isset($_POST["difconteo-diferencia"])){
                $filtro2 = ' a.invd_conteo1 <> a.invd_conteo2';
            }
            if (isset($_POST["difconteo-iguales"])){
                $filtro2 = ' a.invd_conteo1 = a.invd_conteo2';
            }
        }

        $filtro = $filtro1;
        if (!empty($filtro1) && !empty($filtro2)){
            $filtro .= ' AND ';
        }
        $filtro .= $filtro2;
        return ($filtro);
    }    

    /***********************************************************************
    *                    QUERY FILTROS
    ***********************************************************************/
    function filtros_conteos(){
        $filtro = "";
        if (!isset($_POST["conteo-todos"])){
            if (isset($_POST["conteo-pendientes"])){
                $filtro = ' a.invd_conteo IS NULL ';
            }elseif (isset($_POST["conteo-culminados"])){
                $filtro = ' a.invd_conteo IS NOT NULL';
            }
        }
        return ($filtro);
    }

    /***********************************************************************
    *                        NEW INVENTARIO
    ***********************************************************************/
    function new_inventario(){
        $data_maria              = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                = $data_maria->getConnection();
        $oinve                   = new Inventario($db_maria);
        $oinve->inve_observacion = test_input($_POST["input-observacion"]);
        $oinve->user_crea        = $_SESSION["username"];
        $oinve->contadores       = $_POST["contador"];
        for($i = 0;$i < count($oinve->contadores);$i++):
            $oinve->contadores[$i] = test_input($oinve->contadores[$i]);
            $oinve->contadores[$i] = empty($oinve->contadores[$i]) ? null : $oinve->contadores[$i];
        endfor;
        $db_maria->beginTransaction();
        $oinve->insertar_datos();

        /* No está funcionando, se está corriendo el programa inv_panel01.php */
        // if (!$oinve->error){
        //     $db_maria->commit();
        //     asigna_conteos($oinve->inve_id);
        // }else{
        //     $db_maria->rollback();
        // }

        $out=[];
        if (!$oinve->error){
            $db_maria->commit();
            $out["response"] = "success";
            $out["texto"]    = "datos guardados con exito.";
            $out["id"]       = $oinve->inve_id;
        }else{
            $db_maria->rollback();
            $out["error_nro"]  = $oinve->error_nro;
            $out["error_msj"]  = $oinve->error_msj;
            $out["error_file"] = $oinve->error_file;
            $out["error_line"] = $oinve->error_line;
            $out["error_tpo"]  = 'error';
        }
        $oinve = $i = null;
        $data_maria = $db_maria = null;
        unset($data_maria, $db_maria, $oinve, $i);
        return $out;
    }

    /***********************************************************************
    *                      SET CONTEO SQL WHERE
    * Determina las condiciones del where y order by del SELECT-SQL 
    * de las consultas de conteos de inventario.
    ***********************************************************************/
    function set_detalle_sqlwhere($search_text, $search_options, $filtros){
        $sql_where = $sql_value = $sql_order = '';
        if ($search_options == 'todos'):
            $sql_where = null;
            $sql_order = 'ORDER BY a.invd_idproducto DESC';
            $sql_value = null;
        elseif ($search_options == 'codigo'):
            if (!empty($search_text)){
                $sql_where = ' AND a.invd_idproducto like :search_text';
                $sql_value = $search_text .'%';
            }
            $sql_order = 'order by a.invd_idproducto';
        elseif ($search_options == 'nombre'):
            if (!empty($search_text)){
                $sql_where = ' AND a.invd_nombre like :search_text';
                $sql_value = strtoupper($search_text) .'%';
            }
            $sql_order = 'order by a.invd_nombre';
        elseif ($search_options == 'ubicacion'):
            if (!empty($search_text)){
                $sql_where = ' AND a.invd_ubicacion like :search_text';
                $sql_value = strtoupper($search_text) .'%';
            }
            $sql_order = 'ORDER BY a.invd_ubicacion ASC, a.invd_idproducto';
        endif;

        if (!empty($filtros)){
            $sql_where .= ' AND '.$filtros;
        }

        $sql_struct = new stdClass();
        $sql_struct->option    = $search_options;
        $sql_struct->sql_where = $sql_where;
        $sql_struct->sql_order = $sql_order;
        $sql_struct->sql_value = $sql_value;
        unset($sql_where, $sql_order, $sql_value);
        return ($sql_struct);
    }


    /*************************************************************************
    *                      INVENTARIO CONTADORES
    * Genera una lista con las personas asignadas a la toma física de 
    * inventario
    *************************************************************************/
    function inventario_contadores($idinventario){
        $resp       = [];
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $sql = "SELECT invp_username FROM tinventarios_personas a WHERE a.invp_id = :id ORDER BY 1";

        $stmt = $db_maria->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt -> bindparam(':id', $idinventario, PDO::PARAM_INT);

        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_NUM);

        $data_maria = $db_maria = $sql = $stmt = null;
        unset($data_maria, $db_maria, $sql, $stmt);
        return ($resp);
    }    

    /***************************************************************
    *                  UPDATE INVENTARIO DETALLE
    ****************************************************************/
    function update_inventario_detalle($datos){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $odetalle   = new Inventario($db_maria);
        $datos->frm_conteo1   = test_input($datos->frm_conteo1);
        $datos->frm_conteo2   = test_input($datos->frm_conteo2);
        $datos->frm_conteo3   = test_input($datos->frm_conteo3);
        $datos->frm_username3 = test_input($datos->frm_username3);

        $datos->frm_conteo1   = $datos->frm_conteo1 == "" ? null : (int) $datos->frm_conteo1;
        $datos->frm_conteo2   = $datos->frm_conteo2 == "" ? null : (int) $datos->frm_conteo2;
        $datos->frm_conteo3   = $datos->frm_conteo3 == "" ? null : (int) $datos->frm_conteo3;
        $datos->frm_username3 = empty($datos->frm_username3) ? null : $datos->frm_username3;
        
        $odetalle->update_detalle($datos);

        $out=[];
        if (!$odetalle->error){
            $out["response"]     = "success";
            $out["texto"]        = "datos guardados con exito.";
            $out["idinventario"] = $datos->frm_idinventario;
            $out["idproducto"]   = $datos->frm_idproducto;
        }else{
            $out["response"]   = "fail";
            $out["error_nro"]  = $odetalle->error_nro;
            $out["error_msj"]  = $odetalle->error_msj;
            $out["error_file"] = $odetalle->error_file;
            $out["error_line"] = $odetalle->error_line;
            $out["error_tpo"]  = 'error';
        };

        $odetalle = null;
        $data_maria = $db_maria = null;
        unset($data_maria, $db_maria, $opacking);
        return $out;
    }

    /***************************************************************
    *                  ASIGNA CONTEOS
    ****************************************************************/
    // function asigna_conteos($idinventario){
    //     $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
    //     $db_maria   = $data_maria->getConnection();

    //     $grupos_rack  = 2; //Cada cuantos racks se asignará a cada usuario para contar
    //     // echo "idinventario:".$idinventario."\n";
    //     /* Primero tomamos las personas que harán el conteo */
    //     $sql  = "SELECT invp_username FROM tinventarios_personas WHERE invp_id = :id";
    //     $stmt = $db_maria->prepare($sql);
    //     $stmt -> bindparam(':id', $idinventario);
    //     $stmt->execute();
    //     $personas = $stmt->fetchall(PDO::FETCH_ASSOC);
    //     $personas1 = array_column($personas,"invp_username");
    //     $personas2 = orden_conteo2($personas1);
    //     $personas_total = count($personas2);
    
    //     /* Ahora tomamos los racks */
    //     $sql  = "SELECT rack, '' as usuario1, '' as usuario2 FROM vproductos_rack ORDER BY rack";
    //     $stmt = $db_maria->prepare($sql);
    //     $stmt->execute();
    //     $racks = $stmt->fetchall(PDO::FETCH_ASSOC);

    //     // echo "total personas:".$personas_total."<br>";
    //     // var_dump($personas1);
    //     // echo "<br>";
    //     // var_dump($personas2);
    //     // echo "<br>";
    //     // print_r($personas1[0]);
    //     // print_r($personas1[1]);
    
    //     /* Asignamos cada 2 racks a 1 usuario */
    //     $i=0;
    //     $error = false;
    //     // echo "total array personas1 = ".count($personas1)."<br>";
    //     // echo "total array racks = ".count($racks)."<br>";
    //     while($i<count($racks)):
    //         for($j=0; $j<count($personas1); $j++):
    //             for ($k=1; $k<=$grupos_rack; $k++):
    //                 // echo "j=".$j."<br>";
    //                 // print_r("persona1:".$personas1[$j]."<br>");
    //                 // print_r("persona2:".$personas2[$j]."<br>");
    //                 // print_r("rack:".$racks[$i]["rack"]."<br>");
    
    //                 $sql = "UPDATE tinventarios_detalle a
    //                 SET a.invd_username1 = :usuario1, a.invd_username2 = :usuario2, a.user_mod = 'prueba'
    //                 WHERE a.invd_id = :id
    //                 AND SUBSTRING_INDEX(a.invd_ubicacion, '-', 2) = :rack";
    //                 $stmt = $db_maria->prepare($sql);
    //                 $stmt->bindparam(':id', $idinventario, PDO::PARAM_INT);
    //                 $stmt->bindparam(':rack', $racks[$i]["rack"], PDO::PARAM_STR);
    //                 $stmt->bindparam(':usuario1', $personas1[$j], PDO::PARAM_STR);
    //                 $stmt->bindparam(':usuario2', $personas2[$j], PDO::PARAM_STR);
                    
    //                 try {
    //                     $stmt->execute();
    //                 }catch (PDOException $e) {
    //                     $error = true;
    //                 }catch (Exception $e) {
    //                     $error = true;
    //                 }
    //                 if ($error){
    //                     die;
    //                 }   
    //                 $racks[$i]["usuario1"] = $personas1[$j];
    //                 $racks[$i]["usuario2"] = $personas2[$j];
    //                 // echo "i=".$i."<br>";
    //                 $i++;
    //             endfor;
    //         endfor;
    //     endwhile;
        
    //     $data_maria = $db_maria = $sql = $stmt = null;
    //     $grupos_rack = $personas = $personas1 = $personas2 = $personas_total = null;
    //     $racks = $i = $j = $k = $error = null;
    //     unset($data_maria, $db_maria, $sql, $stmt);
    //     unset($grupos_rack, $personas, $personas1, $personas2, $personas_total);
    //     unset($racks, $i, $j, $k, $error);
    // }    

    


    // function orden_conteo2($array1){
    //     $iguales = false;
    //     $array2 = $array1;
    //     shuffle($array2);

    //     //ahora comparamos si todos son diferentes
    //     for($i=0;$i<count($array1);$i++):
    //         if ($array1[$i] == $array2[$i]){
    //             $iguales = true;
    //             break;
    //         }   
    //     endfor;
    //     if ($iguales){
    //          $array2 = orden_conteo2($array1);
    //     }
    //     return ($array2);
    // }


?>