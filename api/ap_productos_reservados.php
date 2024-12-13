<?php
    require_once 'user-auth-api.php';
    require_once '../config/Database_sqlsrv.php';
    require_once '../config/funciones.php';

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json; charset=UTF-8');
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");

    $metodo = $_SERVER["REQUEST_METHOD"];
    if ($metodo=='POST'){
        if (isset($_POST["accion"]) && $_POST["accion"]=="barra-search"){
            $search_text   = test_input($_POST["search-text"]);
            $search_option = test_input($_POST["search-options"]);
            $bloque        = intval(filter_var(test_input($_POST["bloque"]), FILTER_SANITIZE_NUMBER_INT));
            $lista         = query_reservados($search_text, $search_option, $bloque);
            $out["html"]   = html_reservados_lista($lista);
            unset($tpo_lista, $lista);
            unset($search_text, $search_option, $bloque);
            unset($_POST["accion"], $_POST["search-text"], $_POST["search-options"], $_POST["bloque"]);
            http_response_code(200);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        }
    }elseif ($metodo=='GET'){
        if (isset($_GET["accion"]) && $_GET["accion"]=="pedidos-reservados"){
            $idproducto  = test_input($_GET["idproducto"]); 
            $lista       = query_pedidos_reservados($idproducto);
            $out["html"] = html_pedidos_reservados($lista);
            http_response_code(200);
            unset($idproducto, $lista);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
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
    function query_reservados($search_text, $search_options, $pbloque){
        $data_sqlsrv = new Db_sqlsrv();  // Nueva conexión a SQL Server
        $db_sqlsrv   = $data_sqlsrv->getConnection();
        $sql_obj     = set_reservado_sqlwhere($search_text, $search_options);
        
        // Armamos el SQL
        $sql  = "SELECT tabla1.producto_ped, tabla1.nombre_pro, tabla1.unidad_pro, tabla1.existencia_pro, tabla1.ubicacion_pro, tabla1.codigobarra_pro, tabla1.referencia_pro, tabla1.desmarca, tabla1.cant_reservado, tabla1.disponible, RowNum FROM (";
        $sql .= "SELECT a.producto_ped, b.nombre_pro, b.unidad_pro, b.ubicacion_pro, b.codigobarra_pro, b.referencia_pro, ISNULL(c.desmarca,'*sin marca*') as desmarca, CAST(b.existencia_pro as int) as existencia_pro,  CAST(a.cant_reservado as int) as cant_reservado, CAST((b.existencia_pro - a.cant_reservado) as int) as disponible,
        ROW_NUMBER() OVER (ORDER BY ".$sql_obj->sql_over.") AS RowNum
        FROM v_productos_reservados a
        INNER JOIN TbProductos b ON b.codigo_pro = a.producto_ped
        LEFT JOIN TbMarcas c ON b.marca_pro = c.codmarca
        WHERE b.inactivo_pro = 0";
        $sql .= $sql_obj->sql_where;
        $sql .=") AS tabla1 WHERE tabla1.RowNum BETWEEN ((:bloque-1)*20)+1 AND 20*(:bloque2)";
        $sql .= " ".$sql_obj->sql_order;
        $stmt = $db_sqlsrv->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        if ($sql_obj->option !== 'todos'):
            $stmt -> bindparam(':search_text', $sql_obj->sql_value, PDO::PARAM_STR);    
        endif;
        $stmt -> bindparam(':bloque', $pbloque, PDO::PARAM_INT);
        $stmt -> bindparam(':bloque2', $pbloque, PDO::PARAM_INT);
        
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        $sql_obj = $db_sqlsrv = $data_sqlsrv = null;
        unset($db_sqlsrv, $data_sqlsrv, $sql, $stmt, $sql_obj, $pbloque);
        unset($search_text, $search_options, $pbloque);
        return $resp;
    }    
    
    /***********************************************************************
    *                      SET PRODUCTO SQL WHERE
    * Determina las condiciones del where y order by del SELECT-SQL 
    * de las consultas de pedidos.
    ***********************************************************************/
    function set_reservado_sqlwhere($search_text, $search_options){
        if ($search_options == 'todos'):
            $sql_over  = 'a.producto_ped';
            $sql_where = null;
            $sql_order = null;
            $sql_value = null;
        elseif ($search_options == 'codigo'):
            $sql_over  = 'a.producto_ped';
            $sql_where = ' AND a.producto_ped like :search_text';
            $sql_order = 'order by tabla1.producto_ped';
            $sql_value = $search_text .'%';
        elseif ($search_options == 'nombre'):
            $sql_over  = 'b.nombre_pro';
            $sql_where = ' AND b.nombre_pro like :search_text';
            $sql_order = 'order by tabla1.nombre_pro';
            $sql_value = strtoupper($search_text) .'%';
        elseif ($search_options == 'ubicacion'):
            $sql_over  = 'b.ubicacion_pro';
            $sql_where = ' AND b.ubicacion_pro like :search_text';
            $sql_order = 'order by tabla1.ubicacion_pro ASC';
            $sql_value = strtoupper($search_text) .'%';
        elseif ($search_options == 'barra'):
            $sql_over  = 'b.codigobarra_pro';
            $sql_where = ' AND b.codigobarra_pro = :search_text';
            $sql_order = 'order by tabla1.codigobarra_pro';
            $sql_value = $search_text;
        elseif ($search_options == 'ref'):
            $sql_over  = 'b.referencia_pro';
            $sql_where = ' AND b.referencia_pro like :search_text';
            $sql_order = 'order by tabla1.referencia_pro';
            $sql_value = strtoupper($search_text).'%';
        endif;
        $sql_struct = new stdClass();
        $sql_struct->option    = $search_options;
        $sql_struct->sql_over  = $sql_over;
        $sql_struct->sql_where = $sql_where;
        $sql_struct->sql_order = $sql_order;
        $sql_struct->sql_value = $sql_value;
        unset($sql_over, $sql_where, $sql_order, $sql_value);
        return ($sql_struct);
    }


    /***********************************************************************
    *                      RESERVADOS LISTA
    ***********************************************************************/
    function html_reservados_lista($aproductos){
        $out = '';
        // $out = '<table id="reservados-tabla" class="table table-hover table-striped" cellspacing="0">';
        // $out .= '<tr class="tabla-header"><th>Código</th><th>Descripción</th><th>Existencia</th><th>Reservado</th><th>Disponible</th><th>Acción</th></tr>';
        if (count($aproductos)> 0){
            foreach($aproductos as $fila):
                $out .= '<tr id="'.$fila["producto_ped"].'">';
                $out .= '<td>'.$fila["producto_ped"].'</td>';
                $out .= '<td>'.ucwords(strtolower($fila["nombre_pro"])).'</td>';
                $out .= '<td>'.$fila["existencia_pro"].'</td>';
                $out .= '<td>'.$fila["cant_reservado"].'</td>';
                $out .= '<td>'.$fila["disponible"].'</td>';
                $out .= '<td>';
                $out .= '<button type="button" name="check_reservas" id="reserva-'.$fila["producto_ped"].'" class="btn btn-secondary btn-sm btn-xs" onclick="ver_reservados_pedidos(\''.$fila["producto_ped"].'\')"><i class="icon-eye"></i></button>';
                $out .= '</td>';
            endforeach;
            $out .="</table>";
            http_response_code(200);
        }else{
            $out  = '<div class="alert alert-danger" role="alert">';
            $out .= 'No se encontraron coincidencias';
            $out .= '</div>';
            http_response_code(404);
        }
        unset($aproductos);
        return ($out);
    }

    /******************************************************
    * Lista los pedidos que tienen reservado un producto 
    * dado. En otras palabras, se manda un código de producto
    * y se mostrará aquellos pedidos que lo tienen en reserva
    *******************************************************/
    function query_pedidos_reservados($pid_producto){
        require_once '../config/Database_mariadb.php';
        $data_sqlsrv = new Db_sqlsrv();  // Nueva conexión a SQL Server
        $db_sqlsrv   = $data_sqlsrv->getConnection();

        $sql="SELECT a.numero_ped, b.creacion_ped, b.cliente_ped, c.nombre_cli, b.vendedor_ped, d.nombre_ven, b.status_ped, CAST(a.cantidad_ped as int) as requerido, CAST(a.aprobado_ped as int) as cantidad
        FROM TbPedidos2 a
        INNER JOIN TbPedidos1 b ON a.numero_ped = b.numero_ped
        INNER JOIN TbClientes c ON b.cliente_ped = c.codigo_cli
        INNER JOIN TbVendedores d ON b.vendedor_ped = d.codigo_ven
        WHERE b.status_ped IN ('', 'ASIGNADO', 'PENDIENTE', 'RETENIDO')
        AND a.producto_ped = :id_producto
        ORDER BY b.creacion_ped";

        $stmt = $db_sqlsrv->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt -> bindparam(':id_producto', $pid_producto, PDO::PARAM_STR);    
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);

        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();

        $sql="SELECT a.pick_idpedido as numero_ped, c.creacion_ped, c.cliente_ped, d.nombre_cli, c.vendedor_ped, e.nombre_ven, c.status_ped, b.picd_requerido as requerido, 
            CASE 
            WHEN a.pick_status = 1 THEN IFNULL(b.picd_cantidad,0)
            WHEN (a.pick_status = 3 AND b.picd_cantverif IS NOT NULL) THEN b.picd_cantverif
            WHEN (a.pick_status = 3 AND b.picd_cantverif IS NULL) THEN b.picd_cantidad            
            END cantidad
            FROM vpicking a
            JOIN vpicking_detalle b ON b.picd_idproducto = :id_producto AND a.pick_idempresa = b.picd_idempresa AND a.pick_idpicking = b.picd_idpicking
            JOIN tbpedidos1 c ON c.numero_ped = a.pick_idpedido
            JOIN tbclientes d ON d.codigo_cli = c.cliente_ped
            JOIN tbvendedores e ON e.codigo_ven = c.vendedor_ped
            WHERE a.pick_status = 1 OR a.pick_status = 3
            UNION 
            SELECT a.pack_idpedido as numero_ped, c.creacion_ped, c.cliente_ped, d.nombre_cli, c.vendedor_ped, e.nombre_ven, c.status_ped, b.pacd_requerido AS requerido, IFNULL(b.pacd_requerido,0) AS cantidad
            FROM vpacking a
            JOIN vpacking_detalle b ON a.pack_idempresa = b.pacd_idempresa AND a.pack_idpacking = b.pacd_idpacking
            JOIN tbpedidos1 c ON c.numero_ped = a.pack_idpedido
            JOIN tbclientes d ON d.codigo_cli = c.cliente_ped
            JOIN tbvendedores e ON e.codigo_ven = c.vendedor_ped
            WHERE b.pacd_idproducto = :id_producto2
            AND a.pack_status = 1";

        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt -> bindparam(':id_producto', $pid_producto, PDO::PARAM_STR);    
        $stmt -> bindparam(':id_producto2', $pid_producto, PDO::PARAM_STR);
        $stmt->execute();
        $resp2 = $stmt->fetchall(PDO::FETCH_ASSOC);
       
        $sql_obj = $db_sqlsrv = $data_sqlsrv = null;
        unset($db_sqlsrv, $data_sqlsrv, $sql, $stmt, $sql_obj, $pbloque);
        unset($search_text, $search_options, $pbloque);
        return array_merge($resp,$resp2);
    }

    /***********************************************************************
    *                      RESERVADOS LISTA
    ***********************************************************************/
    function html_pedidos_reservados($apedidos){
        $out = '';
        if (count($apedidos)> 0){
            foreach($apedidos as $fila):
                $out .= '<div class="ficha-reservado container-fluid">';
                $out .= '<div class="ficha-body border p-2">';
                $out .= '<div class="ficha-body-header">';
                $out .= '<div><span class="badge bg-primary">'.$fila["numero_ped"].'</span></div>';
                $out .= '<div><span class="badge text-dark">'.$fila["creacion_ped"].'</span></div>';
                $out .= '<div>'.badge_html($fila["status_ped"]).'</div>';
                $out .= '</div>';
                $out .= '<div class="ficha-body-detail">('.$fila["vendedor_ped"].') '.ucwords(strtolower($fila["nombre_ven"])).'</div>';
                $out .= '<div class="ficha-body-detail">('.$fila["cliente_ped"].') '.ucwords(strtolower($fila["nombre_cli"])).'</div>';
                $out .= '</div>';
                $out .= '<div class="ficha-cantidad border"><strong>'.$fila["requerido"].'</strong></div>';
                $out .= '<div class="ficha-cantidad border"><strong>'.$fila["cantidad"].'</strong></div>';
                $out .= '</div>';
            endforeach;
            http_response_code(200);
        }else{
            $out  = '<div class="alert alert-danger" role="alert">';
            $out .= 'No se encontraron coincidencias';
            $out .= '</div>';
            http_response_code(404);
        }
        unset($apedidos);
        return ($out);
    }
?>

