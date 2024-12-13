<?php
    require_once 'user-auth-api.php';
    require_once '../clases/clase_pedido.php';
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
            $tpo_lista     = test_input($_POST["lista"]);

            /* VALIDO FECHA EN CASO QUE BUSQUEMOS POR FECHA.*/
            /* ESTO ESTA FEO... SE HACE POR URGENCIA */
            /* ARREGLAR  --- IMPORTANTE ----- */
            if ($search_option == 'fecha' && !strtotime($search_text)){
                http_response_code(412);
                $out["html_lista"] = '<div class="alert alert-danger" role="alert">';
                $out["html_lista"] .= 'No se encontraron coincidencias';
                $out["html_lista"] .= '</div>';
                unset($tpo_lista, $lista);
                unset($search_text, $search_option, $bloque);
                unset($_POST["accion"], $_POST["search-text"], $_POST["search-options"], $_POST["bloque"]);
                echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
                return;
            }

            if ($tpo_lista == 'pedidos-lista'){
                http_response_code(200);
                $filtros = query_filtros();
                $sql_obj = set_pedido_sqlwhere($search_text, $search_option, $filtros);
                $lista = query_pedidos($sql_obj, $bloque);
                $out["html_lista"] = html_pedidos_lista($lista);
                $lista = $filtros = $sql_obj = null;
                unset($lista, $filtros, $sql_obj);
            }elseif ($tpo_lista == 'pedidos-aprobados'){
                http_response_code(200);
                $out = query_aprobados($search_text, $search_option, $bloque);
            }else{
                http_response_code(412);
                $out["response"]   = "fail";
                $out["error_nro"]  = '45021';
                $out["error_msj"]  = 'Solicitud de recursos no valido. No se puede interpretar solicitud';
            }
            unset($tpo_lista, $lista);
            unset($search_text, $search_option, $bloque);
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
    }elseif ($metodo=='GET'){
        if (isset($_GET["accion"]) && $_GET["accion"]=='pedido_productlist'){
            $idpedido  = test_input($_GET["idpedido"]);
            $out       = lista_pedido_productos($idpedido);
            http_response_code(200);
            unset($idcliente,$_GET["idcliente"],$_GET["accion"]);
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
    }elseif ($metodo=='PUT'){
        $data     = json_decode(file_get_contents("php://input"));
        $accion   = strtoupper(test_input($data->accion)); 
        $idpedido = test_input($data->id_pedido); 
        $status   = '';
        switch ($accion) {
            case 'APROBAR':
                $status = "APROBADO";
                break;
            case 'ANULAR':
                $status = "ANULADO";
                break;
            case 'RETENER':
                $status = "RETENIDO";
                break;
        }
        if (!empty($status)){
            $out = cambia_status_pedido($idpedido,$status);            
            http_response_code(200);
        }else{
            $out=[];
            $out["response"]   = "fail";
            $out["error_nro"]  = '45021';
            $out["error_msj"]  = 'Solicitud de recursos no valido. No se puede interpretar solicitud';
            $out["error_file"] = 'API';
            $out["error_line"] = 'API';
            $out["error_tpo"]  = 'error';
            http_response_code(400);
        }
        $data = $accion = $idpedido = $status = $metodo = null;
        unset ($data, $accion, $idpedido, $status, $metodo);
        echo json_encode($out);    
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


    /***********************************************************************
    *                         QUERY PEDIDOS
    ***********************************************************************/
    function query_pedidos($sql_obj, $bloque){
        $resp    = lista_pedidos($sql_obj, $bloque);
        $sql_obj = null;
        $search_text = $search_options = $bloque = null;
        $data_sqlsrv = $db_sqlsrv = null;
        unset($data_sqlsrv, $db_sqlsrv);
        unset($sql_obj, $search_text, $search_options, $bloque);
        return ($resp);
    }


    /***********************************************************************
    *                         QUERY APROBADOS
    ***********************************************************************/
    function query_aprobados($search_text, $search_options, $bloque){
        $data_sqlsrv = new Db_sqlsrv();  // Nueva conexión a SQL Server
        $db_sqlsrv   = $data_sqlsrv->getConnection();
        $opedido     = new Pedido($db_sqlsrv);
        $sql_obj     = set_pedido_sqlwhere($search_text, $search_options,null);
        if ($search_options == 'todos'){
            $sql_obj->sql_where .= "WHERE a.status_ped IN ('APROBADO')";
        }else{
            $sql_obj->sql_where .= " AND a.status_ped IN ('APROBADO')";
        }
        $sql_obj->sql_order = " ORDER BY tabla1.creacion_ped";
        //$resp = $opedido->lista_pedidos($sql_obj, $bloque);
        $resp = lista_pedidos($sql_obj, $bloque);
        $out = [];
        $out["html_lista"] = '';
        $out["count"] = count($resp);
        if (count($resp)> 0){
            foreach($resp as $fila):
                $opedido->numero_ped = $fila["numero_ped"];
                $opedido->fecha_ped  = $fila["fecha_ped"];
                $opedido->nombre_cli = $fila["nombre_cli"];
                $opedido->nombre_ven = $fila["nombre_ven"];
                $out["html_lista"] .= $opedido->html_pedidos_card();
            endforeach;
            http_response_code(200);
        }else{
            $out["html_lista"]  = '<div class="alert alert-danger" role="alert">';
            $out["html_lista"] .= 'No se encontraron coincidencias';
            $out["html_lista"] .= '</div>';
            http_response_code(404);
        }
        $opedido = $sql_obj = null;
        unset($search_text, $search_options, $bloque);
        $search_text = $search_options = $bloque = null;
        unset($opedido, $sql_obj, $resp);
        $data_sqlsrv = $db_sqlsrv = null;
        unset($data_sqlsrv, $db_sqlsrv);
        return ($out);
    }


    /***********************************************************************
    *                      PEDIDOS LISTA
    ***********************************************************************/
    function html_pedidos_lista($apedidos){
        $out = '';
        if (count($apedidos)> 0){
            foreach($apedidos as $fila):
                $obj = new stdClass();
                $obj->numero_ped  = $fila["numero_ped"];
                $obj->fecha_ped   = $fila["fecha_ped"];
                $obj->cliente_ped = $fila["cliente_ped"];
                $obj->nombre_cli  = $fila["nombre_cli"];
                $obj->nombre_ven  = $fila["nombre_ven"];
                $obj->status_ped  = $fila["status_ped"];
                $obj->total_ped   = $fila["total_ped"];
                $out .= html_pedidos_linea($obj);
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
        $apedidos = null;
        unset($apedidos);
        return ($out);
    }


    /***********************************************************************
    *                  HTML PEDIDOS LINEA
    ***********************************************************************/
    function html_pedidos_linea($opedido){
        $out  = '';
        $out .= '<tr id="'.$opedido->numero_ped.'">';
        $out .= '<td>'.$opedido->numero_ped.'</td>';
        $out .= '<td>'.$opedido->fecha_ped.'</td>';
        $out .= '<td>'.ucwords(strtolower($opedido->nombre_cli)).'</td>';
        $out .= '<td>'.ucwords(strtolower($opedido->nombre_ven)).'</td>';
        $out .= '<td style="text-align:right;">'.number_format($opedido->total_ped,2).'</td>';
        $out .= '<td class="col_status_ped">'.badge_html($opedido->status_ped).'</td>';
        $out .= '<td class="col_actions">';
        $out .= '<button type="button" name="print" id="print-'.$opedido->numero_ped.'" class="btn btn-secondary btn-xs" onclick="pedido_print(\''.$opedido->numero_ped.'\')"><i class="icon-printer-5"></i></button>';
        $out .= '<button type="button" name="update" class="btn btn-warning btn-xs" id="edit-'.$opedido->numero_ped.'" onclick="pedido_consulta(\''.$opedido->numero_ped.'\',\''.$opedido->cliente_ped.'\')"><i class="icon-edit-12"></i></button>';
        $out .= '<button type="button" name="bultos" id="bultos-'.$opedido->numero_ped.'" class="btn btn-secondary btn-xs" onclick="packing_bultos(\''.$opedido->numero_ped.'\')"><i class="icon-boxes-1"></i></button>';
        $out .= '</td>';
        $out .= '</tr>';
        $opedido = null;
        unset($opedido);
        return $out;
    }


    /***********************************************************************
    *                  HTML PEDIDOS LINEA
    ***********************************************************************/
    function html_aprobados_linea($objlinea){
        $out    = '';
        $out .= '<div id="'.$objlinea->numero_ped.'">';
        $out .= '<div>'.$objlinea->numero_ped.'</div>';
        $out .= '<div>'.$objlinea->fecha_ped.'</div>';
        $out .= '<div>'.ucwords(strtolower($objlinea->nombre_cli)).'</div>';
        $out .= '<div>'.ucwords(strtolower($objlinea->nombre_ven)).'</div>';
        $out .= '<div>'.$objlinea->total_ped.'</div>';
        $out .= '</div>';
        $objlinea = null;
        unset($objlinea);
        return $out;
    }


    /****************************************************************************
    *                         CAMBIA STATUS PEDIDO
    * Cambia el status de un pedido
    * Condiciones:
    *   1. No se puede mandar a cambiar a un status en el que ya está el pedido.
    *   2. No se puede cambiar el Status de un Pedido "Anulado".
    *   3. No se puede cambiar el Status de un Pedido "Facturado".
    *   4. No se puede pasar de Packing, Picking o Pendiente a estatus Aprobado.
    *****************************************************************************/
    function cambia_status_pedido($idpedido,$status){
        $data_sqlsrv         = new Db_sqlsrv(); // Nueva conexión a SQL Server
        $db_sqlsrv           = $data_sqlsrv->getConnection();
        $opedido             = new Pedido($db_sqlsrv);
        $opedido->numero_ped = $idpedido;
        $opedido->carga_pedidos_master();
        $out = [];

        /* No hay hay ninguna restricción por lo tanto seguimos*/
        if ($status == 'APROBADO'){
            /* La validación de que sea usuario aprobador se hace en el metodo VALIDA_STATUS() de la clase Pedido*/
            $opedido->aprobar_pedido();
        }else{
            $opedido->update_pedidos_status($status);
        }

        if (!$opedido->error){
            $out["response"]    = "success";
            $out["texto"]       = "datos guardados con exito.";
            $out["numero_ped"]  = $opedido->numero_ped;
            $out["html_status"] = badge_html($opedido->status_ped);
        }else{
            $out["response"]   = 'fail';
            $out["error_nro"]  = $opedido->error_nro;
            $out["error_msj"]  = $opedido->error_msj;
            $out["error_file"] = $opedido->error_file;
            $out["error_line"] = $opedido->error_line;
            $out["error_tpo"]  = $opedido->error_tpo;
        }
        $opedido = $idpedido = $status = null;
        unset($opedido, $idpedido, $status);
        $data_sqlsrv = $db_sqlsrv = null;
        unset($data_sqlsrv, $db_sqlsrv);
        return ($out);
    }


    /*************************************************************************************
    *                           LISTA PEDIDO PRODUCTOS
    * Crea una tabla HTML con los productos de un pedido
    **************************************************************************************/
    function lista_pedido_productos($idpedido){
        $data_sqlsrv         = new Db_sqlsrv(); // Nueva conexión a SQL Server
        $db_sqlsrv           = $data_sqlsrv->getConnection();
        $opedido             = new Pedido($db_sqlsrv);
        $opedido->numero_ped = $idpedido;
        $opedido ->carga_datos();
        $html_productos = '';
        if (!$opedido->error) {
            $html_productos = 'Observación:&nbsp<div>'.$opedido->observacion_ped.'</div>';
            $html_productos .= html_pedido_productlist($opedido->pedido_detalle);   
        }

        if (!$opedido->error){
            $out = array();
            $out["response"]       = 'success';
            $out["html_productos"] = $html_productos;
        }else{
            $out["response"] = 'fail';
            $out["html_productos"]  = '<div class="alert alert-danger" role="alert">';
            $out["html_productos"] .= 'Error Nro.:'.$opedido->error_nro.'<br>';
            $out["html_productos"] .= 'Msj:'.$opedido->error_msj.'<br>';
            $out["html_productos"] .= 'File:'.$opedido->error_file.'<br>';
            $out["html_productos"] .= 'Line:'.$opedido->error_line.'<br>';
            $out["html_productos"] .= 'Notifique a Soporte Técnico';
            $out["html_productos"] .= '</div>';
        }
        $opedido = $idpedido = $html_productos = null;
        unset($opedido, $idpedido, $html_productos);
        $data_sqlsrv = $db_sqlsrv = null;
        unset($data_sqlsrv, $db_sqlsrv);
        return ($out);
    }    


    /*************************************************************************
    *                        HTML PRODUCT LIST
    * Genera un tabla HTML con los productos de un pedido
    *************************************************************************/
    function html_pedido_productlist($arraylista){
        $html  = '';
        if (count($arraylista) > 0):
            $html .= '<table class="table table-striped table-hover table-bordered table-sm">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th scope="col">Codigo</th>';
            $html .= '<th scope="col">Producto</th>';
            $html .= '<th scope="col">Cantidad</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            for ($i=0; $i<count($arraylista); $i++):
                $html .= '<tr>';
                $html .= '<td scope="row">'.$arraylista[$i]["producto_ped"].'</td>';
                $html .= '<td>'.ucwords(strtolower($arraylista[$i]["descripcion_ped"])).'</td>';
                $html .= '<td style="text-align:right;">'.number_format($arraylista[$i]["cantidad_ped"],2).'</td>';
                $html .= '</tr>';
            endfor;
            $html .= '</tbody>';
            $html .= '</table>';
        else:
            $html .= '<div class="alert alert-success" role="alert">';
            $html .= 'El cliente No Posee documentos pendientes por Cancelar';
            $html .= '</div>';
        endif;            
        $arraylista = $i = null;
        unset($arraylista, $i);
        return $html;
    }


    /******************************************************
    *                 Lista Pedidos
    * Retorna los pedidos ordenados por fecha del más
    * reciente al más antiguo
    *******************************************************/
    function lista_pedidos($sql_obj,$pbloque){
        $data_sqlsrv = new Db_sqlsrv();  // Nueva conexión a SQL Server
        $db_sqlsrv   = $data_sqlsrv->getConnection();
        $sql  = "SELECT tabla1.numero_ped, tabla1.fecha_ped, tabla1.cliente_ped, tabla1.nombre_cli, tabla1.vendedor_ped, tabla1.nombre_ven, tabla1.status_ped, tabla1.total_ped, tabla1.creacion_ped, RowNum FROM (";
        $sql .= "SELECT a.numero_ped, CONVERT(varchar(10), a.fecha_ped,103) as fecha_ped, a.cliente_ped, a.creacion_ped, b.descripcion_cli as nombre_cli, a.vendedor_ped, c.nombre_ven, a.status_ped, a.total_ped,
        ROW_NUMBER() OVER (ORDER BY a.creacion_ped DESC) AS RowNum
        FROM TbPedidos1 a 
        INNER JOIN TbClientes b ON a.cliente_ped = b.codigo_cli
        INNER JOIN TbVendedores c ON a.vendedor_ped = c.codigo_ven";
        $sql .= " ".$sql_obj->sql_where;
        $sql .=") AS tabla1 WHERE tabla1.RowNum BETWEEN ((:bloque-1)*20)+1 AND 20*(:bloque2)";
        $sql .= " ".$sql_obj->sql_order;
        $stmt = $db_sqlsrv->prepare($sql);

        if ($sql_obj->option == 'fecha'):
            $stmt -> bindparam(':search_text', $sql_obj->sql_value);
        elseif ($sql_obj->option !== 'todos'):
            $stmt -> bindparam(':search_text', $sql_obj->sql_value, PDO::PARAM_STR);    
        endif;
        $stmt ->bindparam(':bloque', $pbloque, PDO::PARAM_INT);
        $stmt ->bindparam(':bloque2', $pbloque, PDO::PARAM_INT);
        $resp = [];
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        $data_sqlsrv = $db_sqlsrv = null;
        $sql = $stmt = $sql_obj = $pbloque = null;
        unset($data_sqlsrv, $db_sqlsrv);
        unset($sql, $stmt, $sql_obj, $pbloque);
        return $resp;
    }    

    /***********************************************************************
    *                    QUERY FILTROS
    ***********************************************************************/
    function query_filtros(){
        $filtro = "";
        $afiltro1 = [];
        if (!isset($_POST["status-todos"])){
            if (isset($_POST["status-nuevo"])){
                array_push($afiltro1,'\'\'');
            }
            if (isset($_POST["status-aprobado"])){
                array_push($afiltro1,'\'APROBADO\'');
            }
            if (isset($_POST["status-picking"])){
                array_push($afiltro1,'\'PICKING\'');
            }
            if (isset($_POST["status-packing"])){
                array_push($afiltro1,'\'PACKING\'');
            }
            if (isset($_POST["status-pendiente"])){
                array_push($afiltro1,'\'PENDIENTE\'');
            }
            if (isset($_POST["status-facturado"])){
                array_push($afiltro1,'\'TOTAL\'');
                array_push($afiltro1,'\'PARCIAL\'');
            }
            if (isset($_POST["status-retenido"])){
                array_push($afiltro1,'\'RETENIDO\'');
            }
            if (isset($_POST["status-anulado"])){
                array_push($afiltro1,'\'ANULADO\'');
            }                                                
        }

        if (!empty($afiltro1)){
            $filtro = "a.status_ped IN (".implode(', ', $afiltro1).")";
        }
        
        $afiltro1 = null;
        unset($afiltro1);
        return ($filtro);
    }

    /***********************************************************************
    *                      SET PRODUCTO SQL WHERE
    * Determina las condiciones del where y order by del SELECT-SQL 
    * de las consultas de pedidos.
    ***********************************************************************/
    function set_pedido_sqlwhere($search_text, $search_options, $filtros){
        if ($search_options == 'todos'):
            $sql_where = null;
            $sql_order = null;
            $sql_value = null;
        elseif ($search_options == 'pedido'):
            $sql_where = 'WHERE a.numero_ped like :search_text';
            $sql_order = 'ORDER BY tabla1.creacion_ped desc';
            $sql_value = '%'.$search_text;
        elseif ($search_options == 'cliente'):
            $sql_where = 'WHERE b.descripcion_cli like :search_text';
            $sql_order = 'ORDER BY tabla1.creacion_ped DESC';
            $sql_value = strtoupper($search_text) .'%';
        elseif ($search_options == 'vendedor'):
            $sql_where = 'WHERE c.nombre_ven like :search_text';
            $sql_order = 'ORDER BY tabla1.creacion_ped desc';
            $sql_value = strtoupper($search_text) .'%';
        elseif ($search_options == 'fecha'):
            $sql_where = 'WHERE a.fecha_ped >= :search_text';
            $sql_order = 'ORDER BY tabla1.creacion_ped desc';
            $sql_value = $search_text;
        endif;
        if (!empty($sql_where) && !empty($filtros)){
            $sql_where .= ' AND '.$filtros;
        }elseif (empty($sql_where) && !empty($filtros)){
            $sql_where = "WHERE ".$filtros;
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
?>