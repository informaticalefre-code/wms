<?php
    require_once 'user-auth-api.php';
    require_once '../config/Database_mariadb.php';
    require_once '../config/funciones.php';
    require_once '../clases/clase_inventario.php';
    

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json; charset=UTF-8'); 
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");

    $metodo = $_SERVER["REQUEST_METHOD"];
    if ($metodo=='POST'){
        if (isset($_POST["accion"]) && $_POST["accion"]=="carga-conteos"){
            $search_text   = test_input($_POST["search-text"]);
            $search_option = test_input($_POST["search-options"]);
            $bloque        = intval(filter_var(test_input($_POST["bloque"]), FILTER_SANITIZE_NUMBER_INT));
            $filtros       = filtros_conteos();
            $sql_obj       = set_conteo_sqlwhere($search_text, $search_option, $filtros);
            $lista         = query_conteos($sql_obj, $bloque);
            $out["html"]   = html_conteos_lista($lista);
            http_response_code(200);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);            
            return;
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=="save-conteo"){
            $out = update_conteo();
            // $idproducto   = test_input($_POST["info-idproducto"]);
            // $idinventario = test_input($_POST["info-idinventario"]);
            // $conteo       = test_input($_POST["info-conteo"]);
            // $tpoconteo    = test_input($_POST["info-tpoconteo"]);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);            
            return;
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
        if (isset($_GET["accion"]) && $_GET["accion"]==='producto-conteo'){
            $out = query_producto_conteo(test_input($_GET["id"]),test_input($_GET["idproducto"]));
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
    *                      SET CONTEO SQL WHERE
    * Determina las condiciones del where y order by del SELECT-SQL 
    * de las consultas de conteos de inventario.
    ***********************************************************************/
    function set_conteo_sqlwhere($search_text, $search_options, $filtros){
        if ($search_options == 'todos'):
            $sql_where = null;
            $sql_order = 'ORDER BY a.invd_ubicacion, a.invd_idproducto';
            $sql_value = null;
        elseif ($search_options == 'codigo'):
            $sql_where = ' AND a.invd_idproducto like :search_text';
            $sql_order = 'order by a.invd_idproducto';
            $sql_value = $search_text .'%';
        elseif ($search_options == 'nombre'):
            $sql_over  = 'a.nombre_pro';
            $sql_where = ' AND a.invd_nombre like :search_text';
            $sql_order = 'order by a.invd_nombre';
            $sql_value = strtoupper($search_text) .'%';
        elseif ($search_options == 'ubicacion'):
            $sql_where = ' AND a.invd_ubicacion like :search_text';
            $sql_order = 'ORDER BY a.invd_ubicacion ASC, a.invd_idproducto';
            $sql_value = strtoupper($search_text) .'%';
        elseif ($search_options == 'barra'):
            $sql_where = ' AND b.codigobarra_pro = :search_text';
            $sql_order = 'ORDER by b.codigobarra_pro';
            $sql_value = $search_text;
        elseif ($search_options == 'ref'):
            $sql_where = ' AND b.referencia_pro like :search_text';
            $sql_order = 'ORDER BY b.referencia_pro';
            $sql_value = strtoupper($search_text).'%';
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

    /***********************************************************************
    *                         QUERY CONTEOS
    ***********************************************************************/
    function query_conteos($sql_obj, $pbloque){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $block      = ($pbloque - 1) * 20;

        // Armamos el SQL
        $sql = "SELECT a.invd_id, a.invd_idproducto, a.invd_nombre, a.invd_unidad, a.invd_ubicacion, a.invd_tpoconteo, a.invd_conteo, a.invd_fecha, b.codigobarra_pro, b.referencia_pro, NVL(c.desmarca,'*sin marca*') as desmarca
                  FROM vinventarios_conteo a
             LEFT JOIN TbProductos b ON b.codigo_pro = a.invd_idproducto
             LEFT JOIN TbMarcas c ON b.marca_pro = c.codmarca
                 WHERE EXISTS (SELECT z.inve_id FROM vinventarios z WHERE z.inve_estatus = 1 AND z.inve_id = a.invd_id) AND  a.invd_username = :username";
        $sql .= " ".$sql_obj->sql_where;
        $sql .= " ".$sql_obj->sql_order;
        $sql .= " LIMIT :bloque,20";

        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        if ($sql_obj->option !== 'todos'):
            $stmt -> bindparam(':search_text', $sql_obj->sql_value, PDO::PARAM_STR);    
        endif;
        $stmt -> bindparam(':username', $_SESSION["username"], PDO::PARAM_STR);
        $stmt -> bindparam(':bloque', $block, PDO::PARAM_INT);

        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);

        $data_maria = $db_maria = $sql = $stmt = $sql_obj = $block = null;
        $pbloque = null;
        unset($data_maria, $db_maria, $sql, $stmt, $sql_obj, $block, $pbloque);
        return $resp;
    }

    /***********************************************************************
    *                      HTML CONTEOS LISTA
    ***********************************************************************/
    function html_conteos_lista($apacking){
        $out = '';
        if (count($apacking)> 0){
            foreach($apacking as $fila):
                $obj = new stdClass();
                $obj->invd_id         = $fila["invd_id"];
                $obj->invd_idproducto = $fila["invd_idproducto"];
                $obj->invd_nombre     = $fila["invd_nombre"];
                $obj->invd_unidad     = $fila["invd_unidad"];
                $obj->invd_ubicacion  = $fila["invd_ubicacion"];
                $obj->invd_tpoconteo  = $fila["invd_tpoconteo"];
                $obj->invd_conteo     = $fila["invd_conteo"];
                $obj->invd_fecha      = $fila["invd_fecha"];
                $obj->codigobarra_pro = $fila["codigobarra_pro"];
                $obj->referencia_pro  = $fila["referencia_pro"];
                $obj->desmarca        = $fila["desmarca"];
                $out .= html_conteos_linea($obj);
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
    *                  HTML PRODUCTOS LINEA
    ***********************************************************************/
    function html_conteos_linea($odetalle){
        $out  = '';
        $out .= '<tr id="'.$odetalle->invd_idproducto.'">';
        $out .= '<td>'.$odetalle->invd_idproducto.'</td>';
        $out .= '<td style="text-align:left;">'.ucwords(strtolower($odetalle->invd_nombre)).'</td>';
        $out .= empty($odetalle->invd_unidad) ? '<td>&nbsp</td>' : '<td>'.$odetalle->invd_unidad.'</td>';
        $out .= empty($odetalle->invd_ubicacion) ? '<td>&nbsp</td>' : '<td>'.$odetalle->invd_ubicacion.'</td>';
        $out .= is_null($odetalle->invd_conteo) ? '<td>&nbsp</td>' : '<td>'.$odetalle->invd_conteo.'</td>';
        //$out .= empty($odetalle->referencia_pro) ? '<td>&nbsp</td>' : '<td>'.$odetalle->referencia_pro.'</td>';
        $out .= empty($odetalle->desmarca) ? '<td>&nbsp</td>' : '<td>'.ucwords(strtolower($odetalle->desmarca)).'</td>';
        $out .= '<td class="col_actions">';
        $out .= '<button type="button" name="update" class="btn btn-warning" id="edit-'.$odetalle->invd_idproducto.'" onclick="edit_conteos('.$odetalle->invd_id.',\''.$odetalle->invd_idproducto.'\');"><i class="icon-edit-12"></i></button>';
        $out .= '<input type="hidden" name="tpoconteo" id="tpoconteo-'.$odetalle->invd_idproducto.'" disabled autocomplete="off" value="'.$odetalle->invd_tpoconteo.'">';
        $out .= '</td>';
        $out .= '</tr>';
        $odetalle = null;
        unset($odetalle);
        return $out;
    }


    /***********************************************************************
    *                       UPDATE CONTEO
    ***********************************************************************/    
    function update_conteo(){
        $data_maria  = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria    = $data_maria->getConnection();
        $oinventario = new Inventario($db_maria);
        $tpoconteo   = test_input($_POST["info-tpoconteo"]);
        $oinventario->invd_id         = test_input($_POST["info-idinventario"]);
        $oinventario->invd_idproducto = test_input($_POST["info-idproducto"]);
        $oinventario->invd_conteo     = test_input($_POST["info-conteo"]);
        $oinventario->invd_conteo     = $oinventario->invd_conteo == "" ? null : (int) $oinventario->invd_conteo;

        $oinventario->update_conteo($tpoconteo);

        $out=[];
        if (!$oinventario->error){
            $out["response"] = "success";
            $out["texto"]    = "datos guardados con exito.";
            $out["invd_id"]  = $oinventario->invd_id;
        }else{
            $out["error_nro"]  = $oinventario->error_nro;
            $out["error_msj"]  = $oinventario->error_msj;
            $out["error_file"] = $oinventario->error_file;
            $out["error_line"] = $oinventario->error_line;
            $out["error_tpo"]  = 'error';
        }
        $data_maria = $db_maria = $oinventario = null;
        unset($data_maria, $db_maria, $oinventario);
        return $out;
    }

    
    /***********************************************************************
    *                   QUERY PRODUCTO CONTEO
    ***********************************************************************/
    function query_producto_conteo($idinventario, $idproducto){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();

        $sql = "SELECT invd_conteo 
                FROM vinventarios_conteo
                WHERE invd_id = :id AND invd_idproducto = :idproducto AND invd_username = :username";

        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt -> bindparam(':id', $idinventario, PDO::PARAM_INT);
        $stmt -> bindparam(':idproducto', $idproducto, PDO::PARAM_STR);
        $stmt -> bindparam(':username', $_SESSION["username"], PDO::PARAM_STR);

        $stmt->execute();
        $resp = $stmt->fetch(PDO::FETCH_ASSOC);

        $data_maria = $db_maria = $sql = $stmt = null;
        $idinventario = $idproducto = null;
        unset($data_maria, $db_maria, $sql, $stmt);
        unset($idinventario, $idproducto);
        return $resp;
    }
?>