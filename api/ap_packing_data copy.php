<?php
    require_once 'user-auth-api.php';
    require_once '../clases/clase_pedido.php';
    require_once '../clases/clase_packing.php';
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
        if (isset($_GET["idpacking"])){
            $id_packing = test_input($_GET["idpacking"]);
            $accion     = test_input($_GET["accion"]);
            if ($accion == 'packing-master'){
                $out = carga_tarea_packing($id_packing);
            }elseif ($accion == 'packing-detalle'){
                $out = carga_tarea_detalle($id_packing);
            }
            unset($id_packing,$_GET["idpacking"],$_GET["accion"]);
        }elseif (isset($_GET["idpedido"])){
            $idpedido = test_input($_GET["idpedido"]);
            $accion   = test_input($_GET["accion"]);
            if ($accion == 'packing-dif'){         
                $out = carga_tarea_dif($idpedido);       
            }
            unset($id_packing,$_GET["idpedido"],$_GET["accion"]);
        }
        http_response_code(200);
        echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
    }elseif ($metodo == 'POST'){
        $out = update_packing_data();
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
    *                      CARGA TAREA DE PACKING
    * Carga en memoria una tarea de packing.
    *************************************************************************/
    function carga_tarea_packing($pid_packing){
        $html_detalle = "";
        $html_bins    = "";
        $bins         = [];
        $data_maria   = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria     = $data_maria->getConnection();
        $opacking     = new Packing($db_maria);
        $opacking->pack_idpacking = $pid_packing;
        $opacking->carga_packing_master();

        $out = array();
        $out["idpacking"]   = $opacking->pack_idpacking;
        $out["fecha"]       = $opacking->pack_fecha;
        $out["idpedido"]    = $opacking->pack_idpedido;
        $out["idpicking"]   = $opacking->pack_idpicking;
        $out["idvendedor"]  = $opacking->vendedor_ped;
        $out["vendedor"]    = ucwords(strtolower($opacking->nombre_ven));
        $out["cliente"]     = ucwords(strtolower($opacking->nombre_cli));
        $out["pista"]       = $opacking->pack_pista;
        $out["estatus"]     = $opacking->pack_status;
        $out["prioridad"]   = $opacking->pack_prioridad;
        $out["observacion"] = $opacking->pack_observacion;
        $out["embalador"]   = $opacking->pack_embalador;

        $data_maria = $db_maria = null;
        $opacking = null;
        unset($data_maria, $db_maria); // destruimos objeto
        unset($opacking);
        return $out;
    }


    /*************************************************************************
    *                      CARGA DETALLE DE PACKING
    * Carga en memoria una tarea de packing.
    *************************************************************************/
    function carga_tarea_detalle($pid_packing){
        $data_maria   = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria     = $data_maria->getConnection();
        $opacking     = new Packing($db_maria);
        $opacking->pack_idempresa = 1;
        $opacking->pack_idpacking = $pid_packing;
        $opacking->carga_packing_detalle();
        $out = array();
        $out["html_detalle"] = genera_html_detalle($opacking->packing_detalle,'DETALLE');
        $data_maria = $db_maria = null;
        $opacking = null;
        unset($data_maria, $db_maria); // destruimos objeto
        unset($opacking);
        return $out;
    }    


    /***********************************************************************
    *                      GENERA HTML DETALLE
    ***********************************************************************/
    function genera_html_detalle($packing_detalle,$modo){
        $html = '';
        $contador = 0;
        if (count($packing_detalle)> 0){
            foreach($packing_detalle as $fila):
                $contador++; 
                $html .= '<div id="detalle-'.strval($contador).'" class="detalle-row mb-1" style="display:flex; gap:5px; justify-content:center;">';
                $html .= '<div class="packing-prod-status">';
                if ($modo == 'DETALLE'){
                    $html .= '<span id="semaforo" class="semaforo semaforo-blanco"></span>';
                }else{
                    $html .= '<span id="semaforo" class="'.packing_semaforo($fila["cantidad_ped"], $fila["pacd_requerido"]).'"></span>';
                }
                $html .= '</div>';                
                $html .= '<div>';
                $html .= '<input id="input-idproducto" class="form-control p-1" type="text" name="detalle-idproducto[]" autocomplete="off" placeholder="Id. Producto" value="'.$fila["pacd_idproducto"].'" readonly required>';
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
                $html .= '<input id="input-requerido" onchange="change_detalle('.strval($contador).');" class="form-control p-1 detalle-number" type="text" name="detalle-requerido[]" autocomplete="off" placeholder="Requerido" value="'.$fila["pacd_requerido"].'" required>';
                $html .= '</div>';
                $html .= '<div>';
                $html .= '<input id="input-cantidad" onchange="change_detalle('.strval($contador).');" class="form-control p-1 detalle-number" type="text" name="detalle-cantidad[]" autocomplete="off" value="'.$fila["pacd_cantidad"].'" disabled>';
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
        $packing_detalle = $contador = null;
        unset($packing_detalle, $contador);
        return ($html);
    }


    /*************************************************************************
    *                           CARGA TAREA DIF
    * Carga en memoria una tarea de packing.
    *************************************************************************/
    function carga_tarea_dif($idpedido){
        $data_maria   = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria     = $data_maria->getConnection();

        $sql = "SELECT a.pacd_idproducto, CAST(a.cantidad_ped AS INT) as cantidad_ped, a.pacd_requerido, a.total, a.tipo, b.nombre_pro, c.pacd_cantidad
                FROM vpedidos_packing_dif a 
                INNER JOIN tbproductos b ON a.pacd_idproducto = b.codigo_pro
                LEFT JOIN vpacking_detalle c ON c.pacd_idpacking = a.pack_idpacking AND c.pacd_idproducto = a.pacd_idproducto
                WHERE a.pack_idpedido = :idpedido ORDER BY a.pacd_idproducto"; 
    
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
    *                        PACKING SEMAFORO
    * En las pantallas trabajamos con semáforos que indican el status de 
    * packing de un producto determinado.
    * El valor retornado son clases CSS que colocan el color al semaforo.
    *************************************************************************/
    function packing_semaforo($pedido,$requerido){
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
    *                     UPDATE PACKING DATA
    *************************************************************************/
    function update_packing_data(){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $opacking   = new Packing($db_maria);
        $opacking->pack_idempresa   = 1;
        $opacking->pack_idpacking   = test_input($_POST["input-idpacking"]);
        $opacking->pack_fecha       = test_input($_POST["input-fecha"]);
        $opacking->pack_idpedido    = test_input($_POST["input-idpedido"]); 
        $opacking->pack_idpicking   = test_input($_POST["input-idpicking"]);
        $opacking->pack_embalador   = test_input($_POST["input-embalador"]);
        $opacking->pack_status      = test_input($_POST["input-status"]);
        $opacking->pack_prioridad   = test_input($_POST["input-prioridad"]);
        $opacking->pack_pista       = test_input($_POST["input-pista"]);
        $opacking->pack_observacion = test_input($_POST["input-observacion"]);
        $opacking->user_crea        = $_SESSION['username'];
        $opacking->user_mod         = $_SESSION['username'];

        // Arrays de acciones
        $ainsertados  = array_keys($_POST["accion"],"INSERTADO");
        $aeliminados  = array_keys($_POST["accion"],"ELIMINADO");
        $amodificados = array_keys($_POST["accion"],"MODIFICADO");

        $opacking->conn->beginTransaction();

        $opacking->update_packing_master();

        // Vamos con los posibles productos que se agregaron al pedido.
        if (count($ainsertados)> 0){
            $opedido = new Pedido($db_maria);
            $opedido->numero_ped = $opacking->pack_idpedido;
            $opedido->carga_pedidos_detalle();
            $i= 0;
            // $opacking->conn->beginTransaction();
            foreach($ainsertados as $fila):
                $index = array_search($_POST["detalle-idproducto"][$fila],array_column($opedido->pedido_detalle,'producto_ped'));
                $opacking->packing_detalle[$i]['pacd_idempresa']  = $opacking->pack_idempresa;
                $opacking->packing_detalle[$i]['pacd_idproducto'] = $opedido->pedido_detalle[$index]["producto_ped"];
                $opacking->packing_detalle[$i]['pacd_unidad']     = $opedido->pedido_detalle[$index]["unidad_pro"];
                $opacking->packing_detalle[$i]['pacd_idalmacen']  = 1;
                $opacking->packing_detalle[$i]['pacd_ubicacion']  = $opedido->pedido_detalle[$index]["ubicacion_pro"];
                $opacking->packing_detalle[$i]['pacd_requerido']  = $opedido->pedido_detalle[$index]["cantidad_ped"];
                $i++;
            endforeach;

            if (count($opacking->packing_detalle)>0){
                $opacking->insert_packing_detalle();
            }
            $opedido = $index = $i = $fila = null;
            unset ($opedido, $index, $i, $fila);
        }

        // Vamos con los posibles productos elimiandos al pedido.
        if (!$opacking->error && count($aeliminados)> 0){
            foreach($aeliminados as $fila):
                $idproducto = test_input($_POST["detalle-idproducto"][$fila]);
                $opacking->delete_packing_detalle($idproducto);
            endforeach;
            $idproducto = $fila = null;
            unset ($idproducto, $fila);
        }

        // Vamos con los posibles productos modificados al pedido.
        if (!$opacking->error && count($amodificados)> 0){
            foreach($amodificados as $fila):
                $idproducto = test_input($_POST["detalle-idproducto"][$fila]);
                $adatos = [];
                $adatos["idpacking"]  = $opacking->pack_idpacking;
                $adatos["idproducto"] = test_input($_POST["detalle-idproducto"][$fila]);
                $adatos["requerido"]  = empty($_POST["detalle-requerido"]) ? null : test_input($_POST["detalle-requerido"][$fila]);
                $adatos["cantidad"]   = empty(intval($_POST["detalle-cantidad"][$fila])) ? null :  filter_var(test_input($_POST["detalle-cantidad"][$fila]),FILTER_SANITIZE_NUMBER_INT); 
                $adatos["user_mod"]   = $_SESSION['username'];
                $opacking->update_packing_detalle($adatos);
            endforeach;
            $adatos = null;    
            unset ($adatos);
        }

        if (!$opacking->error){
            $opacking->conn->commit();
            $out["response"]       = "success";
            $out["texto"]          = "datos guardados con exito.";
            $out["pack_idpacking"] = $opacking->pack_idpacking;
        }else{
            $opacking->conn->rollback();
            $out["response"] = "fail";
            $out["error_nro"]  = $opacking->error_nro;
            $out["error_msj"]  = $opacking->error_msj;
            $out["error_file"] = $opacking->error_file;
            $out["error_line"] = $opacking->error_line;
            $out["error_tpo"]  = 'error';            
        }

        $data_maria = $db_maria = $opacking = null;
        $ainsertados = $aeliminados = $amodificados = null;
        unset($data_maria, $db_maria, $opacking);
        unset($ainsertados, $aeliminados, $amodificados);

        $_POST["input-idpacking"]    = null;
        $_POST["input-fecha"]        = null;
        $_POST["input-idpedido"]     = null;
        $_POST["input-idpicking"]    = null;
        $_POST["input-embalador"]    = null;
        $_POST["input-status"]       = null;
        $_POST["input-prioridad"]    = null;
        $_POST["input-pista"]        = null;
        $_POST["input-observacion"]  = null;
        $_POST["detalle-idproducto"] = null;
        $_POST["detalle-idproducto"] = null;
        $_POST["detalle-requerido"]  = null;
        $_POST["detalle-cantidad"]   = null;
        unset($_POST["input-idpacking"], $_POST["input-fecha"], $_POST["input-idpedido"], $_POST["input-idpicking"], $_POST["input-embalador"]);
        unset($_POST["input-status"], $_POST["input-prioridad"], $_POST["input-pista"], $_POST["input-observacion"]);
        unset($_POST["detalle-idproducto"], $_POST["detalle-idproducto"]);
        unset($_POST["detalle-requerido"], $_POST["detalle-cantidad"]);
     
        return $out;
    }        
?>