<?php
    require_once 'user-auth-api.php';
    require_once '../config/Database_sqlsrv.php';
    require_once '../config/Database_mariadb.php';
    require_once '../config/funciones.php';
    require_once '../clases/clase_foto.php';
    require_once '../clases/clase_producto.php';

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
            if ($tpo_lista == 'productos-lista'){
                http_response_code(200);
                $filtros = query_filtros();
                $sql_obj = set_producto_sqlwhere($search_text, $search_option, $filtros);
                $lista = query_productos($sql_obj, $bloque);
                $out["html_lista"] = html_productos_lista($lista);
            }else{
                http_response_code(412);
                $out["response"]  = "fail";
                $out["error_nro"] = '45021';
                $out["error_msj"] = 'Solicitud de recursos no valido. No se puede interpretar solicitud';
            }
            unset($tpo_lista, $lista);
            unset($search_text, $search_option, $bloque);
            unset($_POST["accion"], $_POST["search-text"], $_POST["search-options"], $_POST["bloque"]);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=="producto-ubicacion"){
            $out = actualiza_producto_ubicacion();
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
        if (isset($_GET["idproducto"]) && !isset($_GET["accion"])){
            $idproducto = test_input($_GET["idproducto"]);
            $out        = carga_producto($idproducto);
            http_response_code(200);
            unset($id_packing,$_GET["idpacking"]);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        }elseif (isset($_GET["idproducto"]) && isset($_GET["accion"]) && $_GET["accion"]=='html-ubicaciones'){
            $idproducto = test_input($_GET["idproducto"]);
            $resp       = lista_ubicaciones_alternas($idproducto);
            $out["response"]   = "success";
            $out["cant_ubica"] = count($resp);
            $out["html"]       = genera_html_ubicaciones($resp);
            http_response_code(200);
            echo json_encode($out); 
        }elseif (isset($_GET["idproducto"]) && isset($_GET["accion"]) && $_GET["accion"]=='ubicaciones-alternas'){
            $idproducto = test_input($_GET["idproducto"]);
            $out        = lista_ubicaciones_alternas($idproducto);
            http_response_code(200);
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
    *                         QUERY PRODUCTOS
    ***********************************************************************/
    function query_productos($sql_obj, $pbloque){
        $data_sqlsrv = new Db_sqlsrv();  // Nueva conexión a SQL Server
        $db_sqlsrv   = $data_sqlsrv->getConnection();
        
        // Armamos el SQL
        $sql  = "SELECT tabla1.codigo_pro, tabla1.nombre_pro, tabla1.unidad_pro, tabla1.existencia_pro, tabla1.ubicacion_pro, tabla1.codigobarra_pro, tabla1.referencia_pro, tabla1.desmarca, RowNum FROM (";
        $sql .= "SELECT a.codigo_pro, a.nombre_pro, a.unidad_pro, a.existencia_pro, a.ubicacion_pro, a.codigobarra_pro, a.referencia_pro, ISNULL(b.desmarca,'*sin marca*') as desmarca,
        ROW_NUMBER() OVER (ORDER BY ".$sql_obj->sql_over.") AS RowNum
        FROM TbProductos a 
        LEFT JOIN TbMarcas b ON a.marca_pro = b.codmarca
        WHERE a.inactivo_pro = 0 AND a.codigo_pro NOT LIKE 'ZZ%' 
        AND a.codigo_pro NOT LIKE '999%' AND a.codigo_pro NOT LIKE 'ADM%' 
        AND a.codigo_pro NOT IN ('9700-0000')";
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
        return $resp;
    }


    /***********************************************************************
    *                      PRODUCTOS LISTA
    ***********************************************************************/
    function html_productos_lista($aproductos){
        $out = '';
        if (count($aproductos)> 0){
            foreach($aproductos as $fila):
                $obj = new stdClass();
                $obj->codigo_pro      = $fila["codigo_pro"];
                $obj->nombre_pro      = $fila["nombre_pro"];
                $obj->unidad_pro      = $fila["unidad_pro"];
                $obj->existencia_pro  = $fila["existencia_pro"];
                $obj->ubicacion_pro   = $fila["ubicacion_pro"];
                $obj->codigobarra_pro = $fila["codigobarra_pro"];
                $obj->referencia_pro  = $fila["referencia_pro"];
                $obj->desmarca        = $fila["desmarca"];
                $out .= html_productos_linea($obj);
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
        unset($aproductos);
        return ($out);
    }


    /***********************************************************************
    *                      SET PRODUCTO SQL WHERE
    * Determina las condiciones del where y order by del SELECT-SQL 
    * de las consultas de productos.
    ***********************************************************************/
    function set_producto_sqlwhere($search_text, $search_options, $filtros){
        if ($search_options == 'todos'):
            $sql_over  = 'a.codigo_pro';
            $sql_where = null;
            $sql_order = null;
            $sql_value = null;
        elseif ($search_options == 'codigo'):
            $sql_over  = 'a.codigo_pro';
            $sql_where = ' AND a.codigo_pro like :search_text';
            $sql_order = 'order by tabla1.codigo_pro';
            $sql_value = $search_text .'%';
        elseif ($search_options == 'nombre'):
            $sql_over  = 'a.nombre_pro';
            $sql_where = ' AND a.nombre_pro like :search_text';
            $sql_order = 'order by tabla1.nombre_pro';
            $sql_value = strtoupper($search_text) .'%';
        elseif ($search_options == 'ubicacion'):
            $sql_over  = 'a.ubicacion_pro';
            $sql_where = ' AND a.ubicacion_pro like :search_text';
            $sql_order = 'order by tabla1.ubicacion_pro ASC';
            $sql_value = strtoupper($search_text) .'%';
        elseif ($search_options == 'barra'):
            $sql_over  = 'a.codigobarra_pro';
            $sql_where = ' AND a.codigobarra_pro = :search_text';
            $sql_order = 'order by tabla1.codigobarra_pro';
            $sql_value = $search_text;
        elseif ($search_options == 'ref'):
            $sql_over  = 'a.referencia_pro';
            $sql_where = ' AND a.referencia_pro like :search_text';
            $sql_order = 'order by tabla1.referencia_pro';
            $sql_value = strtoupper($search_text).'%';
        endif;

        if (!empty($filtros)){
            $sql_where .= ' AND '.$filtros;
        }

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
    *                  HTML PRODUCTOS LINEA
    ***********************************************************************/
    function html_productos_linea($oproducto){
        $out  = '';
        $out .= '<tr id="'.$oproducto->codigo_pro.'">';
        $out .= '<td>'.$oproducto->codigo_pro.'</td>';
        $out .= '<td style="text-align:left;">'.ucwords(strtolower($oproducto->nombre_pro)).'</td>';
        $out .= empty($oproducto->unidad_pro) ? '<td>&nbsp</td>' : '<td>'.$oproducto->unidad_pro.'</td>';
        $out .= '<td style="text-align:right;">'.number_format($oproducto->existencia_pro,2).'</td>';
        $out .= empty($oproducto->ubicacion_pro) ? '<td>&nbsp</td>' : '<td>'.$oproducto->ubicacion_pro.'</td>';
        $out .= empty($oproducto->referencia_pro) ? '<td>&nbsp</td>' : '<td>'.$oproducto->referencia_pro.'</td>';
        $out .= empty($oproducto->desmarca) ? '<td>&nbsp</td>' : '<td>'.ucwords(strtolower($oproducto->desmarca)).'</td>';
        $out .= '<td class="col_actions">';
        $out .= '<button type="button" name="update" class="btn btn-warning" id="edit-'.$oproducto->codigo_pro.'" onclick="edit_productos(\''.$oproducto->codigo_pro.'\');"><i class="icon-edit-12"></i></button>';
        $out .= '</td>';
        $out .= '</tr>';
        $oproducto = null;
        unset($oproducto);
        return $out;
    }


    /***********************************************************************
    *                      CARGA PRODUCTO
    ***********************************************************************/
    function carga_producto($idproducto){
        $data_sqlsrv = new Db_sqlsrv();  // Nueva conexión a SQL Server
        $db_sqlsrv   = $data_sqlsrv->getConnection();
        $oproducto   = new Producto($db_sqlsrv);
        $oproducto->carga_datos($idproducto);

        if (!$oproducto->error){
            $out = array();
            $out["response"]    = 'success';
            $out["unidad"]      = $oproducto->unidad_pro;
            $out["codigobarra"] = $oproducto->codigobarra_pro;
            $out["existencia"]  = $oproducto->existencia_pro;
            $out["empaque"]     = $oproducto->EmpaqueOriginal_Pro;
            $out["bulto"]       = $oproducto->BultoOriginal_Pro;
            $out["ubicacion"]   = $oproducto->ubicacion_pro;
            $out["referencia"]  = $oproducto->referencia_pro;
        }else{
            $out["response"]   = 'fail';
            $out["error_nro"]  = $oproducto->error_nro;
            $out["error_msj"]  = $oproducto->error_msj;
            $out["error_tpo"]  = 'error';
        }
        $oproducto = null;
        unset($data_sqlsrv, $db_sqlsrv, $oproducto);
        return ($out);
    };


    /***********************************************************************
    *                  LISTA DE UBICACIONES ALTERNAS
    ***********************************************************************/
    function lista_ubicaciones_alternas($idproducto){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $sql = "SELECT a.prou_almacen, a.prou_ubicacion, a.prou_cantidad FROM vproductos_ubica a WHERE a.prou_idproducto = :idproducto";
        $stmt = $db_maria->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idproducto',$idproducto, PDO::PARAM_STR);
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        $data_maria = $db_maria = $sql = $stmt = null;
        unset($data_maria, $db_maria, $sql, $stmt);
        return ($resp);
    };

    
    /***********************************************************************
    *                      GENERA HTML UBICACIONES
    ***********************************************************************/
    function genera_html_ubicaciones($aubica){
        $out = '';
        $contador = 0;
        if (count($aubica)> 0){
            foreach($aubica as $fila):
                $contador++; 
                $out .= '<div id="ubicacion-'.strval($contador).'" class="p-0 mb-1" style="display:flex; gap:10px; justify-content:center;">';
                $out .= '<div style="width:clamp(100px, 35%, 160px);">';
                $out .= '<select class="form-select" aria-label="Número de almacén" name="almacen2[]" readonly>';
                // $out .= '<option selected>'.$fila["prou_almacen"].'</option>';
                if ($fila["prou_almacen"] == '01'){
                    $out .= '<option value="01">01</option>';    
                }elseif ($fila["prou_almacen"] == '02'){
                    $out .= '<option value="02">02</option>';
                }                
                $out .= '</select>';
                $out .= '</div>';
                $out .= '<div style="width:clamp(100px, 40%,160px);">';
                $out .= '<input id="info-ubicacion2" class="form-control" type="text" name="ubicacion2[]" autocomplete="off" placeholder="Ubicación" value="'.$fila["prou_ubicacion"].'" readonly>';                
                $out .= '</div>';
                $out .= '<div style="width:clamp(100px, 25%,120px);">';
                $out .= '<input id="info-cantidad2" class="form-control" type="text" name="cantidad2[]" autocomplete="off" style="text-align:right;" placeholder="Cantidad" value="'.$fila["prou_cantidad"].'" onchange="change_detalle_ubicaciones('.strval($contador).');">';
                $out .= '</div>';        
                $out .= '<button type="button" onclick="del_places('.strval($contador).');" name="add_places" id="add_places" style="border:none;background-color:white;width:40px;"><i style="color:red;font-size:1.2rem;" class="icon-minus-circle"></i></button>';
                // $out .= '<input id="info-activado2" class="form-control" type="hidden" name="activado2[]" value="0">';
                $out .= '<input id="info-accion2" class="form-control" type="hidden" name="accion2[]" value="SINCAMBIOS">';
                $out .= '</div>';
            endforeach;
            http_response_code(200);
        }
        $aubica = $contador = null;
        unset($aubica, $contador);
        return ($out);
    }


    /*************************************************************************
    *                     ACTUALIZA PRODUCTO UBICACION
    * Es para actualizar la ubicacion de los productos.
    *************************************************************************/
    function actualiza_producto_ubicacion(){
        $data_sqlsrv = new Db_sqlsrv();  // Nueva conexión a SQL Server
        $db_sqlsrv   = $data_sqlsrv->getConnection();
        $data_maria  = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria    = $data_maria->getConnection();

        $oproducto = new Producto($db_sqlsrv);
        $oproducto->codigo_pro      = test_input($_POST["idproducto"]);
        $oproducto->codigobarra_pro = test_input($_POST["codigobarra"]);
        $oproducto->ubicacion_pro   = strtoupper(test_input($_POST["ubicacion"]));

        // La tabla de Productos está en SQL Server y la de las ubicaciones se encuentra en MariaDB. 
        // Comenzamos las transacciones para ambos tipos de base de datos.
        $db_maria->beginTransaction();
        $db_sqlsrv->beginTransaction();

        $ubicaciones = [];
        $j = 0;
        if (isset($_POST["ubicacion2"])){
            for ($i=0; $i<count($_POST["ubicacion2"]); $i++):
                if ($_POST["accion2"][$i] == "INSERTADO" || $_POST["accion2"][$i] == 'MODIFICADO' || $_POST["accion2"][$i]==='ELIMINADO'){
                    $ubicaciones[$j]["almacen"]   = test_input($_POST["almacen2"][$i]);
                    $ubicaciones[$j]["ubicacion"] = strtoupper(test_input($_POST["ubicacion2"][$i]));
                    $ubicaciones[$j]["cantidad"]  = intval(filter_var(test_input($_POST["cantidad2"][$i]), FILTER_SANITIZE_NUMBER_INT));
                    $ubicaciones[$j]["accion"]    = test_input($_POST["accion2"][$i]);
                    if (empty($ubicaciones[$j]["almacen"]) || empty($ubicaciones[$j]["ubicacion"]) || empty($ubicaciones[$j]["cantidad"])){
                        $oproducto->error = true;
                        $oproducto->error_nro = 45400;
                        $oproducto->error_msj = 'No pueden haber campos en blanco o vacíos';
                    }
                    $j++;
                }
            endfor;
        }
        
        if (empty($oproducto->codigo_pro)){
            $oproducto->error = true;
            $out["error_nro"]  = 45300;
            $out["error_msj"]  = 'código de producto en blanco o vacío';
            $out["error_file"] = '';
            $out["error_line"] = '';
        }

        if (!$oproducto->error){
            $oproducto->update_datos_producto();
            if (!$oproducto->error){
                $aerror = update_ubicaciones($oproducto->codigo_pro, $ubicaciones, $db_maria);
                if ($aerror["response"]=='fail'){
                    $oproducto->error = true;
                    $oproducto->error_nro = $aerror["error_nro"];
                    $oproducto->error_msj = $aerror["error_msj"];
                }
                $aerror = null;
                unset($aerror);
            }
        }

        $out=[];
        if (!$oproducto->error){
            $db_sqlsrv->commit();
            $db_maria->commit();
            $out["response"]   = "success";
            $out["texto"]      = "datos guardados con exito.";
            $out["idproducto"] = $oproducto->codigo_pro;
            $out["ubicacion"]  = $oproducto->ubicacion_pro;
        }else{
            $db_sqlsrv->rollback();
            $db_maria->rollback();
            $out["response"]   = "fail";
            $out["error_nro"]  = $oproducto->error_nro;
            $out["error_msj"]  = $oproducto->error_msj;
            $out["error_tpo"]  = 'error';
        }
        // Liberando memoria.....
        $oproducto = null;
        $data_sqlsrv = $db_sqlsrv = $data_maria = $db_maria = $data_maria = null;
        $ubicaciones = null;
        unset($data_sqlsrv, $db_sqlsrv, $data_maria, $db_maria, $data_maria);
        unset($ubicaciones, $i, $j);
        return $out;
    }


    /**************************************************************
    *                   UPDATE UBICACIONES
    **************************************************************/
    function update_ubicaciones($idproducto, $puestos, $dbconexion){
        $error  = false;
        $aerror = [];
        $data   = [];
        for ($i=0; $i<count($puestos); $i++):
            if (!$error){
                if ($puestos[$i]["accion"]=="INSERTADO"){
                    $sql = "INSERT INTO vproductos_ubica (prou_idproducto, prou_almacen, prou_ubicacion, prou_cantidad, user_crea) 
                    VALUES (:prou_idproducto, :prou_almacen, :prou_ubicacion, :prou_cantidad, :user_crea)";
                    $data = array(
                        'prou_idproducto' => $idproducto,
                        'prou_almacen'    => $puestos[$i]["almacen"],
                        'prou_ubicacion'  => $puestos[$i]["ubicacion"],
                        'prou_cantidad'   => $puestos[$i]["cantidad"],
                        'user_crea'       => $_SESSION["username"]
                    );
                }elseif ($puestos[$i]["accion"]=="MODIFICADO"){
                    $sql = "UPDATE vproductos_ubica SET prou_cantidad = :prou_cantidad, user_mod = :user_mod
                    WHERE prou_idproducto = :prou_idproducto AND prou_almacen = :prou_almacen AND prou_ubicacion = :prou_ubicacion";
                    $data = array(
                        'prou_idproducto' => $idproducto,
                        'prou_almacen'    => $puestos[$i]["almacen"],
                        'prou_ubicacion'  => $puestos[$i]["ubicacion"],
                        'prou_cantidad'   => $puestos[$i]["cantidad"],
                        'user_mod'        => $_SESSION["username"]
                    );
                }elseif ($puestos[$i]["accion"]=="ELIMINADO"){
                    $sql = "DELETE FROM vproductos_ubica WHERE prou_idproducto = :prou_idproducto AND prou_almacen = :prou_almacen AND prou_ubicacion = :prou_ubicacion";
                    $data = array(
                        'prou_idproducto' => $idproducto,
                        'prou_almacen'    => $puestos[$i]["almacen"],
                        'prou_ubicacion'  => $puestos[$i]["ubicacion"]
                    );
                }

                $stmt = $dbconexion->prepare($sql);
                try {
                    $stmt->execute($data);
                } catch (PDOException $e) {
                        $error                = true;
                        $aerror["response"]   = "fail";
                        $aerror["error_nro"]  = $e->getCode();
                        $aerror["error_msj"]  = $e->getMessage();
                        $aerror["error_file"] = $e->getfile();
                        $aerror["error_line"] = $e->getLine();
                        $aerror["error_tpo"]  = 'error';
                        break;
                } catch (Exception $e) {
                        $error                = true;
                        $aerror["response"]   = "fail";
                        $aerror["error_nro"]  = $e->getCode();
                        $aerror["error_msj"]  = $e->getMessage();
                        $aerror["error_file"] = $e->getfile();
                        $aerror["error_line"] = $e->getLine();
                        $aerror["error_tpo"]  = 'error';
                        break;
                }
            }
        endfor;

        if (!$error){
            $aerror["response"] = "success";
        }

        $i = $error = $data = null;
        $sql = $stmt = null ;
        unset($i, $error, $data);
        unset($sql, $stmt);
        return ($aerror);
    }


    /***********************************************************************
    *                    QUERY FILTROS
    ***********************************************************************/
    function query_filtros(){
        $filtro = "";
        if (!isset($_POST["existencia-todos"])){
            if (isset($_POST["existencia-con"])){
                $filtro = 'a.existencia_pro > 0';
            }elseif (isset($_POST["existencia-sin"])){
                $filtro = 'a.existencia_pro = 0';
            }
        }
        return ($filtro);
    }    
?>