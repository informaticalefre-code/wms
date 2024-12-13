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
    if ($metodo=='GET'){
        if (isset($_GET["idpicking"])){
            $id_picking = test_input($_GET["idpicking"]);
            $accion     = test_input($_GET["accion"]);
            if ($accion == 'picking-master'){
                $out = carga_tarea_picking($id_picking);
            }elseif ($accion == 'picking-detalle'){
                $out = carga_tarea_detalle($id_picking);
            }
            unset($id_picking,$_GET["idpicking"],$_GET["accion"]);
        }elseif (isset($_GET["idpedido"])){
            $idpedido = test_input($_GET["idpedido"]);
            $accion   = test_input($_GET["accion"]);
            if ($accion == 'picking-dif'){         
                $out = carga_tarea_dif($idpedido);       
            }
            unset($id_picking,$_GET["idpedido"],$_GET["accion"]);
        }
        http_response_code(200);
        echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
    }elseif ($metodo == 'POST'){
        $out = update_picking_data();
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
    *                      CARGA TAREA DE PICKING
    * Carga en memoria una tarea de picking.
    *************************************************************************/
    function carga_tarea_picking($pid_picking){
        $data_maria   = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria     = $data_maria->getConnection();
        $opicking     = new Picking($db_maria);
        $opicking->pick_idpicking = $pid_picking;
        $opicking->carga_picking_master(); 

        $out = array();
        $out["idpicking"]   = $opicking->pick_idpicking;
        $out["fecha"]       = $opicking->pick_fecha;
        $out["idpedido"]    = $opicking->pick_idpedido;
        $out["idvendedor"]  = $opicking->vendedor_ped;
        $out["vendedor"]    = ucwords(strtolower($opicking->nombre_ven));
        $out["cliente"]     = ucwords(strtolower($opicking->nombre_cli));
        $out["pista"]       = $opicking->pick_pista;
        $out["estatus"]     = $opicking->pick_status;
        $out["prioridad"]   = $opicking->pick_prioridad;
        $out["observacion"] = $opicking->pick_observacion;
        $out["preparador"]  = $opicking->pick_preparador;
        $out["fecverif"]    = $opicking->pick_fecverif;
        $out["userverif"]   = $opicking->pick_userverif;

        $data_maria = $db_maria = null;
        $opicking = null;
        unset($data_maria, $db_maria); // destruimos objeto
        unset($opicking);
        return $out;
    }


    /*************************************************************************
    *                      CARGA DETALLE DE PICKING
    * Carga en memoria una tarea de picking.
    *************************************************************************/
    function carga_tarea_detalle($pid_picking){
        $data_maria   = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria     = $data_maria->getConnection();
        $opicking     = new Picking($db_maria);
        $opicking->pick_idempresa = 1;
        $opicking->pick_idpicking = $pid_picking;
        $opicking->carga_picking_detalle();
        $out = array();
        $out["html_detalle"] = genera_html_detalle($opicking->picking_detalle,'DETALLE');
        $data_maria = $db_maria = null;
        $opicking = null;
        unset($data_maria, $db_maria); // destruimos objeto
        unset($opicking);
        return $out;
    }    


    /***********************************************************************
    *                      GENERA HTML DETALLE
    ***********************************************************************/
    function genera_html_detalle($picking_detalle,$modo){
        $html = '';
        $contador = 0;
        if (count($picking_detalle)> 0){
            foreach($picking_detalle as $fila):
                $contador++; 
                $html .= '<div id="detalle-'.strval($contador).'" class="detalle-row mb-1" style="display:flex; gap:5px; justify-content:center;">';
                $html .= '<div class="picking-prod-status">';
                if ($modo == 'DETALLE'){
                    $html .= '<span id="semaforo" class="semaforo semaforo-blanco"></span>';
                }else{
                    $html .= '<span id="semaforo" class="'.picking_semaforo($fila["cantidad_ped"], $fila["picd_requerido"]).'"></span>';
                }
                $html .= '</div>';                
                $html .= '<div>';
                $html .= '<input id="input-idproducto" class="form-control p-1" type="text" name="detalle-idproducto[]" autocomplete="off" placeholder="Id. Producto" value="'.$fila["picd_idproducto"].'" readonly required>';
                $html .= '</div>';
                $html .= '<div>';
                $html .= '<p>'.ucwords(strtolower($fila["nombre_pro"])).'</p>';
                $html .= '</div>';
                $html .= '<div>';

                if (isset($fila["cantidad_ped"])){
                    $html .= '<input id="input-cantidadped" class="form-control p-1 detalle-number" type="text" name="detalle-cantidadped[]" autocomplete="off" placeholder="Pedido" value="'.$fila["cantidad_ped"].'" disabled>';
                }else{
                    $html .= '<input id="input-cantidadped" class="form-control p-1 detalle-number" type="text" name="detalle-cantidadped[]" autocomplete="off" placeholder="Pedido" disabled>';
                }                    
                $html .= '</div>';
                $html .= '<div>';
                $html .= '<input id="input-requerido" onchange="change_detalle('.strval($contador).');" class="form-control p-1 detalle-number" type="text" name="detalle-requerido[]" autocomplete="off" placeholder="Requerido" value="'.$fila["picd_requerido"].'" required>';
                $html .= '</div>';
                $html .= '<div>';
                $html .= '<input id="input-cantidad" onchange="change_detalle('.strval($contador).');" class="form-control p-1 detalle-number" type="text" name="detalle-cantidad[]" autocomplete="off" placeholder="Anclado" value='.$fila["picd_cantidad"].'>';
                $html .= '</div>';
                $html .= '<div>';
                $html .= '<input d="input-cantverif" onchange="change_detalle('.strval($contador).');" class="form-control p-1 detalle-number" type="text" name="detalle-cantverif[]" autocomplete="off" placeholder="Verificado" value="'.$fila["picd_cantverif"].'">';
                $html .= '</div>';
                $html .= '<button type="button" onclick="borra_detalle('.strval($contador).');" name="del_detalle" id="del_places" style="border:none;background-color:white;width:40px;"><i style="color:red;font-size:1.2rem;" class="icon-minus-circle"></i></button>';
                if ($modo == 'DETALLE'){
                    $html .= '<input id="detalle-accion" class="form-control" type="hidden" name="accion[]" value="SINCAMBIOS">';
                }else{
                    $html .= '<input id="detalle-accion" class="form-control" type="hidden" name="accion[]"  value="'.$fila["tipo"].'">';
                }
                $html .= '</div>';
            endforeach;
        }
        $picking_detalle = $contador = null;
        unset($picking_detalle, $contador);
        return ($html);
    }


    /*************************************************************************
    *                      CARGA TAREA DIF
    * Carga en memoria una tarea de picking.
    *************************************************************************/
    function carga_tarea_dif($idpedido){
        $data_maria   = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria     = $data_maria->getConnection();

        $sql = "SELECT a.picd_idproducto, CAST(a.cantidad_ped AS INT) as cantidad_ped, a.picd_requerido, a.total, a.tipo, b.nombre_pro, c.picd_cantidad, c.picd_cantverif
                FROM vpedidos_picking_dif a 
                INNER JOIN tbproductos b ON a.picd_idproducto = b.codigo_pro
                LEFT JOIN vpicking_detalle c ON c.picd_idpicking = a.pick_idpicking AND c.picd_idproducto = a.picd_idproducto
                WHERE a.pick_idpedido = :idpedido ORDER BY a.picd_idproducto"; 
    
        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idpedido', $idpedido, PDO::PARAM_INT);
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        $out = array();
        $out["html_detalle"] = genera_html_detalle($resp,'DIFERENCIAS');

        $data_maria = $db_maria = $stmt = $sql = $resp = null;
        $idpedido = null;
        unset($data_maria, $db_maria); // destruimos objeto
        unset($sql,$stmt,$resp);
        unset($idpedido);
        return $out;
    }


    /*************************************************************************
    *                        PICKING SEMAFORO
    * En las pantallas trabajamos con semáforos que indican el status de 
    * picking de un producto determinado.
    * El valor retornado son clases CSS que colocan el color al semaforo.
    *************************************************************************/
    function picking_semaforo($pedido,$requerido){
        $clases = 'semaforo ';
        if ($requerido == $pedido){
            $clases .= "semaforo-verde";
        }elseif (empty($pedido)){
            $clases .= "semaforo-rojo";
        }elseif (empty($requerido)){
            $clases .= "semaforo-blanco";
        }elseif ($requerido !== $pedido){
            $clases .= "semaforo-amarillo";
        }
        $requerido = $pedido = null;
        unset($requerido, $pedido);
        return $clases;
    }


    /*************************************************************************
    *                     UPDATE PICKING DATA
    *************************************************************************/
    function update_picking_data(){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $opicking   = new Picking($db_maria);
        $opicking->pick_idempresa   = 1;
        $opicking->pick_idpicking   = test_input($_POST["input-idpicking"]);
        $opicking->pick_fecha       = test_input($_POST["input-fecha"]);
        $opicking->pick_idpedido    = test_input($_POST["input-idpedido"]); 
        $opicking->pick_preparador  = test_input($_POST["input-preparador"]);
        $opicking->pick_status      = test_input($_POST["input-status"]);
        $opicking->pick_prioridad   = test_input($_POST["input-prioridad"]);
        $opicking->pick_pista       = test_input($_POST["input-pista"]);
        $opicking->pick_observacion = test_input($_POST["input-observacion"]);
        $opicking->pick_fecverif    = empty($_POST["input-fecverif"]) ? null :test_input($_POST["input-fecverif"]);
        $opicking->pick_userverif   = test_input($_POST["input-userverif"]);
        $opicking->user_crea        = $_SESSION['username'];
        $opicking->user_mod         = $_SESSION['username'];

        // Arrays de acciones
        $ainsertados  = array_keys($_POST["accion"],"INSERTADO");
        $aeliminados  = array_keys($_POST["accion"],"ELIMINADO");
        $amodificados = array_keys($_POST["accion"],"MODIFICADO");

        $opicking->conn->beginTransaction();

        $opicking->update_picking_master();

        // Vamos con los posibles productos que se agregaron al pedido.
        if (count($ainsertados)> 0){
            $opedido = new Pedido($db_maria);
            $opedido->numero_ped = $opicking->pick_idpedido;
            $opedido->carga_pedidos_detalle();
            $i= 0;
            // $opicking->conn->beginTransaction();
            foreach($ainsertados as $fila):
                $index = array_search($_POST["detalle-idproducto"][$fila],array_column($opedido->pedido_detalle,'producto_ped'));
                $opicking->picking_detalle[$i]['picd_idempresa']  = $opicking->pick_idempresa;
                $opicking->picking_detalle[$i]['picd_idproducto'] = $opedido->pedido_detalle[$index]["producto_ped"];
                $opicking->picking_detalle[$i]['picd_unidad']     = $opedido->pedido_detalle[$index]["unidad_pro"];
                $opicking->picking_detalle[$i]['picd_idalmacen']  = 1;
                $opicking->picking_detalle[$i]['picd_ubicacion']  = $opedido->pedido_detalle[$index]["ubicacion_pro"];
                $opicking->picking_detalle[$i]['picd_requerido']  = $opedido->pedido_detalle[$index]["cantidad_ped"];
                $i++;
            endforeach;

            if (count($opicking->picking_detalle)>0){
                $opicking->insert_picking_detalle();
            }
            $opedido = $index = $i = $fila = null;
            unset ($opedido, $index, $i, $fila);
        }

        // Vamos con los posibles productos elimiandos al pedido.
        if (!$opicking->error && count($aeliminados)> 0){
            foreach($aeliminados as $fila):
                $idproducto = test_input($_POST["detalle-idproducto"][$fila]);
                $opicking->elimina_detalle_producto($idproducto);
            endforeach;
            $idproducto = $fila = null;
            unset ($idproducto, $fila);
        }

        // Vamos con los posibles productos modificados al pedido.
        if (!$opicking->error && count($amodificados)> 0){
            foreach($amodificados as $fila):
                $idproducto = test_input($_POST["detalle-idproducto"][$fila]);
                $adatos = [];
                $adatos["idpicking"]  = $opicking->pick_idpicking;
                $adatos["idproducto"] = test_input($_POST["detalle-idproducto"][$fila]);
                $adatos["requerido"]  = empty($_POST["detalle-requerido"]) ? null : test_input($_POST["detalle-requerido"][$fila]);
                $adatos["cantidad"]   =  empty(intval($_POST["detalle-cantidad"][$fila])) ? null :  filter_var(test_input($_POST["detalle-cantidad"][$fila]),FILTER_SANITIZE_NUMBER_INT); 
                $adatos["cantverif"]  =  empty(intval($_POST["detalle-cantverif"][$fila])) ? null :  filter_var(test_input($_POST["detalle-cantverif"][$fila]),FILTER_SANITIZE_NUMBER_INT); 
                $adatos["user_mod"]   = $_SESSION['username'];
                $opicking->update_picking_detalle($adatos);
            endforeach;
            $adatos = null;    
            unset ($adatos);
        }

        if (!$opicking->error){
            $opicking->conn->commit();
            $out["response"]       = "success";
            $out["texto"]          = "datos guardados con exito.";
            $out["pick_idpicking"] = $opicking->pick_idpicking;
        }else{
            $opicking->conn->rollback();
            $out["response"] = "fail";
            $out["error_nro"]  = $opicking->error_nro;
            $out["error_msj"]  = $opicking->error_msj;
            $out["error_file"] = $opicking->error_file;
            $out["error_line"] = $opicking->error_line;
            $out["error_tpo"]  = 'error';            
        }

        $data_maria = $db_maria = $opicking = null;
        $ainsertados = $aeliminados = $amodificados = null;
        unset($data_maria, $db_maria, $opicking);
        unset($ainsertados, $aeliminados, $amodificados);

        $_POST["input-idpicking"]    = null;
        $_POST["input-fecha"]        = null;
        $_POST["input-idpedido"]     = null;
        $_POST["input-preparador"]   = null;
        $_POST["input-status"]      = null;
        $_POST["input-prioridad"]    = null;
        $_POST["input-pista"]        = null;
        $_POST["input-observacion"]  = null;
        $_POST["input-fecverif"]     = null;
        $_POST["input-userverif"]    = null;
        $_POST["detalle-idproducto"] = null;
        $_POST["detalle-idproducto"] = null;
        $_POST["detalle-requerido"]  = null;
        $_POST["detalle-cantidad"]   = null;
        $_POST["detalle-cantverif"]  = null;
        unset($_POST["input-idpicking"], $_POST["input-fecha"], $_POST["input-idpedido"], $_POST["input-preparador"]);
        unset($_POST["input-status"], $_POST["input-prioridad"], $_POST["input-pista"], $_POST["input-observacion"]);
        unset($_POST["input-fecverif"], $_POST["input-userverif"], $_POST["detalle-idproducto"], $_POST["detalle-idproducto"]);
        unset($_POST["detalle-requerido"], $_POST["detalle-cantidad"], $_POST["detalle-cantverif"]);
     
        return $out;
    }
?>