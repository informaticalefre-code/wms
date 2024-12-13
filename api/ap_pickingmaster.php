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
            $lista         = query_pickingtasks($sql_obj, $bloque);
            $out["html_lista"] = html_picking_lista($lista);
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
    function query_pickingtasks($sql_obj, $pbloque){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $block      = ($pbloque - 1) * 20;

        // Armamos el SQL
        $sql  = "SELECT a.pick_idpicking, a.pick_idpedido, a.pick_fecha, a.pick_preparador, a.pick_status, a.pick_prioridad FROM vpicking a";
        $sql .= " ".$sql_obj->sql_where;
        $sql .= " ".$sql_obj->sql_order;
        $sql .= " LIMIT :bloque,20";

        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        if ($sql_obj->option !== 'todos'):
            $stmt -> bindparam(':search_text', $sql_obj->sql_value, PDO::PARAM_STR);    
        endif;
        $stmt -> bindparam(':bloque', $block, PDO::PARAM_INT);
        // echo '$sql_obj->sql_value:'.$sql_obj->sql_value;
        // echo 'bloque:'.$block;
        // echo $sql;
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
            $sql_order = 'ORDER BY a.pick_fecha DESC';;
            $sql_value = null;
        elseif ($search_options == 'tarea'):
            $sql_where = 'WHERE a.pick_idpicking = :search_text';
            $sql_order = 'ORDER BY a.pick_idpicking';
            $sql_value = $search_text;
        elseif ($search_options == 'preparador'):
            $sql_where = 'WHERE a.pick_preparador LIKE :search_text';
            $sql_order = 'ORDER BY a.pick_preparador,a.pick_fecha DESC';
            $sql_value = strtoupper($search_text).'%';
        elseif ($search_options == 'pedido'):
            $sql_where = 'WHERE a.pick_idpedido LIKE :search_text';
            $sql_order = 'ORDER BY a.pick_idpedido';
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
    *                      PICKING LISTA
    ***********************************************************************/
    function html_picking_lista($apicking){
        $out = '';
        if (count($apicking)> 0){
            foreach($apicking as $fila):
                $obj = new stdClass();
                $obj->pick_idpicking  = $fila["pick_idpicking"];
                $obj->pick_idpedido   = $fila["pick_idpedido"];
                $obj->pick_fecha      = $fila["pick_fecha"];
                $obj->pick_preparador = $fila["pick_preparador"];
                $obj->pick_status     = $fila["pick_status"];
                $obj->pick_prioridad  = $fila["pick_prioridad"];
                $out .= html_picking_linea($obj);
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
        $apicking = null;
        unset($apicking);
        return ($out);
    }


    /***********************************************************************
    *                  HTML PICKING LINEA
    ***********************************************************************/
    function html_picking_linea($opicking){
        $out  = '';
        $out .= '<tr id="'.$opicking->pick_idpicking.'" class="picking_linea">';
        $out .= '<td>'.$opicking->pick_idpicking.'</td>';
        $out .= '<td>'.$opicking->pick_fecha.'</td>';
        $out .= '<td>'.$opicking->pick_idpedido.'</td>';
        if ($opicking->pick_prioridad == 1){
            $out .= '<td><span class="badge rounded-pill bg-danger">Urgente</span></td>';
        }else{
            $out .= '<td>&nbsp</td>';
        }
        $out .= '<td>'.strtolower($opicking->pick_preparador).'</td>';
        $out .= '<td>'.badge_pick_status($opicking->pick_status).'</td>';
        $out .= '<td>';
        //$out .= '<button type="button" name="update" class="btn btn-warning" id="edit-'.$opicking->pick_idpicking.'" onclick="edit_picking(\''.$opicking->pick_idpicking.'\');"><i class="icon-edit-12"></i></button>';
        $out .= '<a href="'.$_SERVER['HTTP_ORIGIN'].'/wms/picking_form.php?idpicking='.$opicking->pick_idpicking.'"';
        $out .= '<button type="button" name="update" class="btn btn-warning" id="edit-'.$opicking->pick_idpicking.'" onclick="edit_picking(\''.$opicking->pick_idpicking.'\');"><i class="icon-edit-12"></i></button>';
        $out .= '</a>';
        $out .= '</td>';
        $out .= '</tr>';
        $opicking = null;
        unset($opicking);
        return $out;
    }

    /***********************************************************************
    *                    BADGE PICK STATUS
    ***********************************************************************/
    function badge_pick_status($pick_status){
        if ($pick_status == 0){
            $out = '<span style="font-size: 80%;" class="badge bg-danger">Anulado</span>';
        }elseif ($pick_status == 1){
            $out = '<span style="font-size: 80%;" class="badge bg-secondary">En Proceso</span>';
        }elseif ($pick_status == 2){
            $out = '<span style="font-size: 80%;" class="badge bg-warning text-dark">Pausado</span>';
        }elseif ($pick_status == 3){
            $out = '<span style="font-size: 80%;" class="badge bg-info text-dark">Consolidado</span>';
        }elseif ($pick_status == 5){
            $out = '<span style="font-size: 80%;" class="badge bg-success">Culminado</span>';
        }
        $pick_status = null;
        unset($pick_status);
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
                $filtro2 = "a.pick_prioridad = 0";
            }
            if (isset($_POST["prioridad-urgente"])){
                $filtro2 = "a.pick_prioridad = 1";
            }
        }

        if (!empty($afiltro1)){
            $filtro = "a.pick_status IN (".implode(', ', $afiltro1).")";
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