<?php
    require_once 'user-auth-api.php';
    require_once '../clases/clase_pedido.php';
    require_once '../clases/clase_picking.php';
    require_once '../config/Database_mariadb.php';
    require_once '../config/Database_sqlsrv.php';
    require_once '../config/funciones.php';

    header("Access-Control-Allow-Origin: *");
    // header('Content-Type: application/json; charset=charset=iso-8859-1'); 
    header('Content-Type: application/json; charset=UTF-8'); 
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");
    $metodo = $_SERVER["REQUEST_METHOD"];
    if ($metodo=='POST'){
        if (isset($_POST["accion"]) && $_POST["accion"]=="barra-search"){
            $search_text   = test_input($_POST["search-text"]);
            $search_option = test_input($_POST["search-options"]);
            $bloque        = intval(filter_var(test_input($_POST["bloque"]), FILTER_SANITIZE_NUMBER_INT));
            $filtros       = query_filtros();
            $sql_obj       = set_producto_sqlwhere($search_text, $search_option, $filtros);
            $lista         = query_packingtasks($sql_obj, $bloque);
            $out["html_lista"] = html_packing_lista($lista);
            http_response_code(200);
            unset($tpo_lista, $lista);
            unset($search_text, $search_option, $bloque, $filtros, $sql_obj);
            unset($_POST["accion"], $_POST["search-text"], $_POST["search-options"], $_POST["bloque"]);
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
        http_response_code(400);
        echo json_encode($out);
    }

    /***********************************************************************
    *                         QUERY PRODUCTOS
    ***********************************************************************/
    function query_packingtasks($sql_obj, $pbloque){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $block      = ($pbloque - 1) * 20;

        // Armamos el SQL
        $sql  = "SELECT a.pack_idpacking, a.pack_idpedido, a.pack_fecha, a.pack_embalador, a.pack_status, a.pack_prioridad FROM vpacking a";
        $sql .= " ".$sql_obj->sql_where;
        $sql .= " ".$sql_obj->sql_order;
        $sql .= " LIMIT :bloque,20";

        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        if ($sql_obj->option !== 'todos'):
            $stmt -> bindparam(':search_text', $sql_obj->sql_value, PDO::PARAM_STR);    
        endif;
        $stmt -> bindparam(':bloque', $block, PDO::PARAM_INT);

        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);

        $data_maria = $db_maria = $sql = $stmt = $sql_obj = $block = null;
        $search_text = $search_options = $pbloque = null;
        unset($data_maria, $db_maria, $sql, $stmt, $sql_obj, $block);
        unset($search_text, $search_options, $pbloque);
        return $resp;
    }

    /***********************************************************************
    *                      SET PRODUCTO SQL WHERE
    * Determina las condiciones del where y order by del SELECT-SQL 
    * de las consultas de pedidos.
    ***********************************************************************/
    function set_producto_sqlwhere($search_text, $search_options, $filtros){
        if ($search_options == 'todos'):
            $sql_where = null;
            $sql_order = 'ORDER BY a.pack_fecha DESC';
            $sql_value = null;
        elseif ($search_options == 'tarea'):
            $sql_where = 'WHERE a.pack_idpacking = :search_text';
            $sql_order = 'ORDER BY a.pack_idpacking';
            $sql_value = $search_text;
        elseif ($search_options == 'embalador'):
            $sql_where = 'WHERE a.pack_embalador like :search_text';
            $sql_order = 'ORDER BY a.pack_embalador,a.pack_fecha DESC';
            $sql_value = strtoupper($search_text) .'%';
        elseif ($search_options == 'pedido'):
            $sql_where = 'WHERE a.pack_idpedido like :search_text';
            $sql_order = 'ORDER BY a.pack_idpedido';
            $sql_value = '%'.strtoupper($search_text);
        endif;

        /* Los filtros no deben aplicar en caso que se busque por
           Nro. de Pedido o por Nro. de Tarea */
        if ($search_options !== 'tarea' && $search_options !== 'pedido'){
            if (!empty($sql_where) && !empty($filtros)){
                $sql_where .= ' AND '.$filtros;
            }elseif (empty($sql_where) && !empty($filtros)){
                $sql_where = "WHERE ".$filtros;
            }
        }            

        $sql_struct = new stdClass();
        $sql_struct->option    = $search_options;
        $sql_struct->sql_where = $sql_where;
        $sql_struct->sql_order = $sql_order;
        $sql_struct->sql_value = $sql_value;

        $sql_where = $sql_order = $sql_value = null;
        $search_text = $search_options = $filtros = null;
        unset($sql_where, $sql_order, $sql_value);
        unset($search_text, $search_options, $filtros);
        return ($sql_struct);
    }


    /***********************************************************************
    *                      PACKING LISTA
    ***********************************************************************/
    function html_packing_lista($apacking){
        $out = '';
        if (count($apacking)> 0){
            foreach($apacking as $fila):
                $obj = new stdClass();
                $obj->pack_idpacking = $fila["pack_idpacking"];
                $obj->pack_idpedido  = $fila["pack_idpedido"];
                $obj->pack_fecha     = $fila["pack_fecha"];
                $obj->pack_embalador = $fila["pack_embalador"];
                $obj->pack_status    = $fila["pack_status"];
                $obj->pack_prioridad = $fila["pack_prioridad"];
                $out .= html_packing_linea($obj);
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
    function html_packing_linea($opacking){
        $out  = '';
        $out .= '<tr id="'.$opacking->pack_idpacking.'" class="picking_linea">';
        $out .= '<td>'.$opacking->pack_idpacking.'</td>';
        $out .= '<td>'.$opacking->pack_fecha.'</td>';
        $out .= '<td>'.$opacking->pack_idpedido.'</td>';
        if ($opacking->pack_prioridad == 1){
            $out .= '<td><span class="badge rounded-pill bg-danger">Urgente</span></td>';
        }else{
            $out .= '<td>&nbsp</td>';
        }
        if (is_null($opacking->pack_embalador)){
            $out .= '<td>&nbsp</td>';
        }else{
            $out .= '<td>'.strtolower($opacking->pack_embalador).'</td>';            
            
        }
        $out .= '<td>'.badge_pack_status($opacking->pack_status).'</td>';
        $out .= '<td class="col_actions">';
        $out .= '<a href="'.$_SERVER['HTTP_ORIGIN'].'/wms/packing_form.php?idpacking='.$opacking->pack_idpacking.'"';
        $out .= '<button type="button" name="update" class="btn btn-warning" id="edit-'.$opacking->pack_idpacking.'"><i class="icon-edit-12"></i></button>';
        $out .= '</a>';
        // $out .= '<a href="'.$_SERVER['HTTP_ORIGIN'].'/wms/packing.php?idpacking='.$opacking->pack_idpacking.'"';
        // $out .= '<button type="button" name="edit" class="btn btn-warning"><i class="icon-pack-1"></i></button>';
        // $out .= '</a>';
        $out .= '<button type="button" name="packing" id="packing-'.$opacking->pack_idpacking.'" class="btn btn-secondary btn-xs" onclick="packing_bultos(\''.$opacking->pack_idpedido.'\')"><i class="icon-boxes-1"></i></button>';
        $out .= '</td>';
        $out .= '</tr>';
        $opacking = null;
        unset($opacking);
        return $out;
    }

    /***********************************************************************
    *                    BADGE PICK STATUS
    ***********************************************************************/
    function badge_pack_status($pack_status){
        if ($pack_status == 0){
            $out = '<span style="font-size: 80%;" class="badge bg-danger">Anulado</span>';
        }elseif ($pack_status == 1){
            $out = '<span style="font-size: 80%;" class="badge bg-secondary">En Proceso</span>';
        }elseif ($pack_status == 2){
            $out = '<span style="font-size: 80%;" class="badge bg-warning text-dark">Pausado</span>';
        }elseif ($pack_status == 3){
            $out = '<span style="font-size: 80%;" class="badge bg-info text-dark">Consolidado</span>';
        }elseif ($pack_status == 5){
            $out = '<span style="font-size: 80%;" class="badge bg-success">Culminado</span>';
        }
        $pack_status = null;
        unset($pack_status);
        return ($out);
    }

    /***********************************************************************
    *                    QUERY FILTROS
    ***********************************************************************/
    function query_filtros(){
        $filtro = $filtro2 = "";
        $afiltro1 = [];
        if (!isset($_POST["status-todos"])){
            if (isset($_POST["status-anulado"])){
                array_push($afiltro1,'0');
            }
            if (isset($_POST["status-proceso"])){
                array_push($afiltro1,'1');
            }
            if (isset($_POST["status-consolidado"])){
                array_push($afiltro1,'3');
            }
            if (isset($_POST["status-culminado"])){
                array_push($afiltro1,'5');
            }
        }

        if (!isset($_POST["prioridad-todos"])){
            if (isset($_POST["prioridad-normal"])){
                $filtro2 = "a.pack_prioridad = 0";
            }
            if (isset($_POST["prioridad-urgente"])){
                $filtro2 = "a.pack_prioridad = 1";
            }
        }

        if (!empty($afiltro1)){
            $filtro = "a.pack_status IN (".implode(', ', $afiltro1).")";
        }
        
        if (!empty($filtro2)){
            if (!empty($filtro)){
                $filtro .= " AND ";
            }
            $filtro .= $filtro2;
        }

        $afiltro1 = $filtro2 = null;
        unset($afiltro1, $filtro2);
        return ($filtro);
    }

?>