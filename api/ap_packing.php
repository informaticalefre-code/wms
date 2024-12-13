<?php
    require_once 'user-auth-api.php';
    require_once '../clases/clase_pedido.php';
    require_once '../clases/clase_packing.php';
    require_once '../config/Database_mariadb.php';
    require_once '../config/Database_sqlsrv.php';
    require_once '../config/funciones.php';

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json; charset=UTF-8'); 
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");
    $metodo = $_SERVER["REQUEST_METHOD"];
    if ($metodo=='GET'){
        require_once '../clases/clase_foto.php';
        if (isset($_GET["accion"]) && $_GET["accion"]=='packing-tarea'){
            $id_packing = test_input($_GET["idpacking"]);
            $accion     = test_input($_GET["accion"]);
            $out        = carga_tarea_packing($id_packing);
            http_response_code(200);
            unset($id_packing,$_GET["idpacking"]);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        }elseif (isset($_GET["idpacking"]) && isset($_GET["box"])){
            /* Esto carga los datos de 1 solo bulto */
            $id_packing = test_input($_GET["idpacking"]);
            $box        = test_input($_GET["box"]);
            $out        = carga_bulto_packing($id_packing,$box);
            unset($id_packing,$box);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        }elseif (isset($_GET["accion"]) && $_GET["accion"]=='packing-bultos'){
            /* Esto carga los datos de todos los bultos a partir de un Nro. de Pedido.*/
            $idpedido = test_input($_GET["idpedido"]);
            $out      = carga_bultos_pedido($idpedido);
            unset($id_packing,$box);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        }
    }elseif ($metodo=='PATCH'){
        $idpacking = test_input($_GET["idpacking"]);
        $data      = json_decode(file_get_contents("php://input"));
        $accion    = test_input($data->accion);
        if ($accion == 'packing-start'){
            $out = actualiza_packing_start($idpacking);
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
        $idpacking = $data = $accion = null;
        unset($idpacking, $data, $accion);
    }elseif ($metodo=='POST'){
        /* Se inserta un nuevo bulto a la tarea de packing */
        if (isset($_POST["accion"]) && $_POST["accion"]=="add_box"){
            $idbulto   = test_input($_POST["idbulto"]);
            $idpacking = test_input($_POST["idpacking"]);
            $out = insert_packing_bulto($idpacking, $idbulto);
            unset($idbulto, $idpacking);
            http_response_code(200);
            echo json_encode($out);
        /* Se insertan productos al bulto*/
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=="packing-productos-bultos"){
            $out = insert_productos_bulto();
            http_response_code(200);
            echo json_encode($out);
        /* Al cerrar caja */    
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=='close-box') {
            $out = cierra_bulto();
            http_response_code(200);
            echo json_encode($out);
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=='open-box') {
            $out = abre_bulto();
            http_response_code(200);
            echo json_encode($out);            
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=="packing-close"){
            $out = cierra_tarea_packing();
            http_response_code(200);
            echo json_encode($out);
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=='auto-box') {
            $out = embala_automatic();
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
    }elseif ($metodo=='DELETE'){
        $data = json_decode(file_get_contents("php://input"));
        if (isset($data->accion) && $data->accion=='remove-box-producto'){
            $idpacking  = test_input($data->idpacking);
            $idbulto    = test_input($data->idbulto);
            $idproducto = test_input($data->idproducto);
            $out = delete_box_producto($idpacking, $idbulto, $idproducto);
            unset($data, $idpacking, $idbulto, $idproducto);
            http_response_code(200);
            echo json_encode($out);
        }elseif (isset($data->accion) && $data->accion=='remove-box'){
            $idpacking  = test_input($data->idpacking);
            $idbulto    = test_input($data->idbulto);
            $out = delete_box($idpacking,$idbulto);
            unset($data, $idpacking, $idbulto);
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
        http_response_code(400);
        echo json_encode($out);
    }



    /*************************************************************************
    *                 ACTUALIZA PACKING START
    * Es cuando el embalador selecciona una tarea de Picking. A partir de 
    * ese momento esa tarea comienza y será asignada únicamente a él.
    *************************************************************************/
    function actualiza_packing_start($idpacking){
        $data_maria                 = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                   = $data_maria->getConnection();
        $opacking                   = new Packing($db_maria);
        $opacking->pack_idempresa   = 1;
        $opacking->pack_idpacking   = $idpacking;
        $opacking->pack_embalador   = $_SESSION["username"];
        $opacking->user_mod         = $_SESSION["username"];
        $db_maria->beginTransaction();
        $opacking->update_packing_start();
        $out=[];
        if (!$opacking->error){
            $db_maria->commit();
            $out["response"]       = "success";
            $out["texto"]          = "datos guardados con exito.";
            $out["pack_idpacking"] = $opacking->pack_idpacking;
        }else{
            $db_maria->rollback();
            $out["response"] = "fail";
            if ($opacking->error_nro == '45000'){
                $out["error_nro"]  = $opacking->error_nro;
                $out["error_msj"]  = 'No se pudo iniciar la Tarea de Packing '.$opacking->pack_idpacking;
                $out["error_tpo"]  = 'error';
            }else{
                $out["error_nro"]  = $opacking->error_nro;
                $out["error_msj"]  = $opacking->error_msj;
                $out["error_file"] = $opacking->error_file;
                $out["error_line"] = $opacking->error_line;
                $out["error_tpo"]  = 'error';
            };
        }
        $opacking = null;
        $data_maria = $db_maria = null;
        unset($data_maria, $db_maria, $opacking);
        return $out;
    }


    /*************************************************************************
    *                           CIERRA BULTO
    * Cierra bulto de una tarea de packing
    *************************************************************************/
    function cierra_bulto(){
        $data_maria                = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                  = $data_maria->getConnection();
        $opacking                  = new Packing($db_maria);
        $opacking->pack_idempresa  = 1;
        $opacking->pack_idpacking  = test_input($_POST["idpacking"]);
        $opacking->pack_idbulto    = test_input($_POST["bulto-idbulto"]);
        $opacking->pack_peso       = isset($_POST["bulto-peso"]) ? test_input($_POST["bulto-peso"]) : null;
        $opacking->pack_unidadpeso = isset($_POST["bulto-unidad"]) ? test_input($_POST["bulto-unidad"]) : null;
        $opacking->pack_status     = 1; // Vamos a cerrarlo
        $opacking->user_mod        = $_SESSION["username"];
        
        $db_maria->beginTransaction();
        $opacking->update_closebulto();
        $out=[];
        if (!$opacking->error){
            $db_maria->commit();
            $out["response"]       = "success";
            $out["texto"]          = "datos guardados con exito.";
            $out["pack_idpacking"] = $opacking->pack_idpacking;
        }else{
            $db_maria->rollback();
            $out["error_nro"]  = $opacking->error_nro;
            $out["error_msj"]  = $opacking->error_msj;
            $out["error_file"] = $opacking->error_file;
            $out["error_line"] = $opacking->error_line;
            $out["error_tpo"]  = 'error';
        }
        $opacking = null;
        $data_maria = $db_maria = null;
        unset($data_maria, $db_maria, $opacking);
        return $out;
    }

    /*************************************************************************
    *                           ABRE BULTO
    * Permite abrir una caja para agregar o eliminar productos en él
    *************************************************************************/
    function abre_bulto(){
        $data_maria               = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                 = $data_maria->getConnection();
        $opacking                 = new Packing($db_maria);
        $opacking->pack_idempresa = 1;
        $opacking->pack_idpacking = test_input($_POST["idpacking"]);
        $opacking->user_mod       = $_SESSION["username"];
        $idbulto                  = test_input($_POST["idbulto"]);

        $db_maria->beginTransaction();
        $opacking->open_bulto($idbulto);

        $out=[];
        if (!$opacking->error){
            $db_maria->commit();
            $out["response"]       = "success";
            $out["texto"]          = "datos guardados con exito.";
            $out["pack_idpacking"] = $opacking->pack_idpacking;
        }else{
            $db_maria->rollback();
            $out["error_nro"]  = $opacking->error_nro;
            $out["error_msj"]  = $opacking->error_msj;
            $out["error_file"] = $opacking->error_file;
            $out["error_line"] = $opacking->error_line;
            $out["error_tpo"]  = 'error';
        }
        $opacking = null;
        $data_maria = $db_maria = null;
        unset($data_maria, $db_maria, $opacking);
        return $out;
    }


    /*************************************************************************
    *                 CARGA TAREA DE PACKING
    * Carga en memoria una tarea de packing.
    *************************************************************************/
    function carga_tarea_packing($id_packing){
        $html_lista = "";
        $data_maria                 = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                   = $data_maria->getConnection();
        $opacking                   = new Packing($db_maria);
        $opacking->pack_idpacking   = $id_packing;
        $opacking->carga_packing_master(); 
        if (!$opacking->error){
            $opacking->carga_packing_detalle(); 
        }
        if (!$opacking->error){
            $opacking->carga_packing_bultos('<TODOS>');
        }

        if (!$opacking->error) {
            $html_lista  = html_packing_list($opacking->packing_detalle);
            for ($i=0; $i<count($opacking->packing_detalle); $i++):
                $packing_productos[$i] = array(
                    "id_producto"=>$opacking->packing_detalle[$i]["pacd_idproducto"],
                    "codigobarra"=>$opacking->packing_detalle[$i]["codigobarra_pro"],
                    "requerido"  =>$opacking->packing_detalle[$i]["pacd_requerido"],
                    "cantidad"   =>$opacking->packing_detalle[$i]["pacd_cantidad"],
                    "unidad"     =>$opacking->packing_detalle[$i]["unidad_pro"],
                    "producto"   =>ucwords(strtolower($opacking->packing_detalle[$i]["nombre_pro"]))
                );
            endfor;

            $html_bultos = "";
            /* Ordenamos descendentemente */
            array_multisort(array_column($opacking->packing_bultos, "pack_idbulto"), SORT_DESC, $opacking->packing_bultos );
            for ($i=0; $i<count($opacking->packing_bultos); $i++):
                $html_bultos .= html_packing_bulto($opacking->packing_bultos[$i]["pack_idbulto"],$opacking->packing_bultos[$i]["pack_status"]);
            endfor;
        }
        if (!$opacking->error){
            $out = array();
            $out["response"]     = 'success';
            $out["idpacking"]    = $opacking->pack_idpacking;
            $out["idpicking"]    = $opacking->pack_idpicking;
            $out["fecha"]        = date_format(date_create($opacking->pack_fecha),'d/m/Y h:i');
            $out["idpedido"]     = $opacking->pack_idpedido;
            $out["idvendedor"]   = $opacking->vendedor_ped;
            $out["vendedor"]     = ucwords(strtolower($opacking->nombre_ven));
            $out["cliente"]      = ucwords(strtolower($opacking->nombre_cli));
            $out["embalador"]    = $opacking->pack_embalador;
            $out["bultos"]       = $opacking->bultos;     // Indica la cantidad de bultos asociados a la tarea
            $out["bulto_open"]   = $opacking->bulto_open; // Indica el nro. de bulto actualmente abierto. 0=ninguno;
            $out["packing_productos"] = $packing_productos; 
            $out["html_lista"]   = $html_lista;
            $out["html_bultos"]  = $html_bultos;
        }else{
            $out["response"]    = 'fail';
            $out["html_lista"]  = '<div class="alert alert-danger" role="alert">';
            $out["html_lista"] .= 'Error Nro.:'.$opacking->error_nro.'<br>';
            $out["html_lista"] .= 'Msj:'.$opacking->error_msj.'<br>';
            $out["html_lista"] .= 'File:'.$opacking->error_file.'<br>';
            $out["html_lista"] .= 'Line:'.$opacking->error_line.'<br>';
            $out["html_lista"] .= 'Notifique a Soporte Técnico';
            $out["html_lista"] .= '</div>';
        }
        $opacking = $packing_productos = $html_lista = $i = null;
        $db_maria = $data_maria = null;
        unset($data_maria,$db_maria,$opacking,$packing_productos,$html_lista,$i);
        return $out;
    }    

    /*************************************************************************
    *                 CARGA BULTO PACKING
    * Carga los datos de un bulto especifico con sus respectivos productos
    * embalados en él.
    *************************************************************************/
    function carga_bulto_packing($id_packing,$bulto){
        $html_lista = "";
        $data_maria                 = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                   = $data_maria->getConnection();
        $opacking                   = new Packing($db_maria);
        $opacking->pack_idempresa   = 1;
        $opacking->pack_idpacking   = $id_packing;
        $opacking->carga_packing_bultos($bulto);
        if (!$opacking->error){
            $opacking->carga_packing_productos($bulto);
        }

        if (!$opacking->error){
            $out = array();
            $out["response"]   = 'success';
            $out["idbulto"]    = $opacking->packing_bultos[0]["pack_idbulto"];
            $out["peso"]       = $opacking->packing_bultos[0]["pack_peso"];
            $out["unidadpeso"] = $opacking->packing_bultos[0]["pack_unidadpeso"];
            $out["status"]     = $opacking->packing_bultos[0]["pack_status"];
            $out["cant_sku"]   = count($opacking->packing_productos);
            if (count($opacking->packing_productos) > 0):
                $out["html_prod"] =  html_packing_productos($opacking->packing_productos,$out["status"]);
            else:
                $out["html_prod"]  = '<div class="alert alert-danger" role="alert">';
                $out["html_prod"] .= 'No hay productos en la caja';
                $out["html_prod"] .= '</div>';
            endif;
        }else{
            $out["response"]    = 'fail';
            $out["html_lista"]  = '<div class="alert alert-danger" role="alert">';
            $out["html_lista"] .= 'Error Nro.:'.$opacking->error_nro.'<br>';
            $out["html_lista"] .= 'Msj:'.$opacking->error_msj.'<br>';
            $out["html_lista"] .= 'File:'.$opacking->error_file.'<br>';
            $out["html_lista"] .= 'Line:'.$opacking->error_line.'<br>';
            $out["html_lista"] .= 'Notifique a Soporte Técnico';
            $out["html_lista"] .= '</div>';
        }
        $opacking = $packing_productos = $html_lista = $i = null;
        $db_maria = $data_maria = null;
        unset($data_maria,$db_maria,$opacking,$packing_productos,$html_lista,$i);
        return $out;
    }        

    /*************************************************************************
    *                        HTML PACKING PRODUCTOS
    * Genera el html necesario para mostrar los productos asociados a un
    * bulto.
    * Parametros: 
    * 1. $pack_prod: Array con los productos de la tabla
    * 2. $status: Estatus de la caja (0=Abierto, 1=Cerrado).
    *************************************************************************/
    function html_packing_productos($pack_prod, $status){
        $html  = '';
        $ofoto = new foto();
        for ($i=0; $i<count($pack_prod); $i++):
            $foto  = $ofoto->genera_html_foto($pack_prod[$i]["pacp_idproducto"].'-1-lefre-','TH');
            $html .= '<hr id="hr-'.$i.'">';
            $html .= '<div class="box-prod" id="box-prod-'.$i.'">';
            $html .= '<div class="box-prod-foto" id="boxfoto-'.$pack_prod[$i]["pacp_idproducto"].'">';
            $html .= $foto;
            $html .= '</div>';
            $html .= '<input id="box_idbulto" type="hidden" name="pacp_idproducto" class="box-input border-0" disabled autocomplete="off" value="'.$pack_prod[$i]["pacp_idbulto"].'">';
            $html .= '<input id="box_idproducto" type="text" name="pacp_idproducto" class="box-input col-2 border-0" style="text-align:center;" disabled autocomplete="off" value="'.$pack_prod[$i]["pacp_idproducto"].'">';
            // $html .= '<input id="box_descripcion_ped" type="text" name="descripcion_ped" class="box-input col-6 border-0" disabled autocomplete="off" value="'.ucwords(strtolower($pack_prod[$i]["descripcion_ped"])).'">';
            $html .= '<input id="box_descripcion_ped" type="text" name="descripcion_ped" class="box-input col-6 border-0" disabled autocomplete="off" value="error...">';
            $html .= '<input id="box_cantidad" type="text" name="pacp_cantidad" class="box-input col-2 border-0" style="text-align:right;" disabled autocomplete="off" value="'.$pack_prod[$i]["pacp_cantidad"].'">';
            /* Si la caja está cerrada no se pueden alterar los productos que hay en ella */
            if ($status == 0){
                $html .= '<button type="button" name="remove_prod" id="remove-'.$pack_prod[$i]["pacp_idproducto"].'" class="btn box-del-prod" onclick="box_del_row('.$pack_prod[$i]["pacp_idbulto"].','.$i.',\''.$pack_prod[$i]["pacp_idproducto"].'\')"><i class="icon-minus-circle"></i></button>';
            }else{
                $html .= '<button type="button" name="remove_prod" id="remove-'.$pack_prod[$i]["pacp_idproducto"].'" class="btn box-del-prod" disabled onclick="box_del_row('.$pack_prod[$i]["pacp_idbulto"].','.$i.',\''.$pack_prod[$i]["pacp_idproducto"].'\')"><i class="icon-minus-circle"></i></button>';
            }
            $html .= '</div>';
        endfor;
        $ofoto = $pack_prod = $i = $status = null;
        unset($ofoto, $pack_prod, $i, $status);
        return $html;
    }
    
    /*************************************************************************
    *                        HTML PICKING LIST
    * Esta función genera el html necesario para generar la lista de productos
    * de una tarea de picking
    *************************************************************************/
    function html_packing_list($packing_detalle){
        $html  = '';
        $ofoto = new foto();
        for ($i=0; $i<count($packing_detalle); $i++):
            $foto  = $ofoto->genera_html_foto($packing_detalle[$i]["pacd_idproducto"].'-1-lefre-','SM');
            $html .= '<div class="packing-prod" id="packing-'.$packing_detalle[$i]["pacd_idproducto"].'"';
            if ($packing_detalle[$i]["pacd_requerido"] == $packing_detalle[$i]["pacd_cantidad"]){
                $html .= ' style="display:none;"';
            }
            $html .= ' onclick="pack_producto(\''.$packing_detalle[$i]["pacd_idproducto"].'\')">';
            $html .= '<div class="packing-prod-status">';
            $html .= '<span id="semaforo" class="'.packing_semaforo($packing_detalle[$i]["pacd_requerido"],$packing_detalle[$i]["pacd_cantidad"]).'"></span>';
            $html .= '</div>';
            $html .= '<div class="packing-prod-foto" id="foto-'.$packing_detalle[$i]["pacd_idproducto"].'">';
            $html .= $foto;
            $html .= '</div>';
            $html .= '<div class="packing-prod-detalle" draggable="true">';
            $html .= '<div class="packing-detalle-header">';
            $html .= '<span style="font-size:100%;" class="badge bg-success">'.$packing_detalle[$i]["pacd_idproducto"].'</span>';
            $html .= '</div>';
            $html .= '<p class="text-wrap mb-0" id="packing-nombre-pro-'.$packing_detalle[$i]["pacd_idproducto"].'">'.ucwords(strtolower($packing_detalle[$i]["nombre_pro"])).'</p>';
            $html .= '<div class="packing-prod-detalle-info">';
            $html .= '<div>';
            $html .= '<span>Unidad:&nbsp</span><span id="packing-unidad">'.$packing_detalle[$i]["unidad_pro"].'</span>';
            $html .= '</div>';
            $html .= '<div style="text-align:right;">';
            $html .= '<span>Bulto Orig.:&nbsp</span><span id="packing-bultoorig">'.$packing_detalle[$i]["bultooriginal_pro"].'</span>';
            $html .= '</div>';
            $html .= '</div>';             
            $html .= '</div>';
            $html .= '<div class="packing-prod-requerido">';
            $html .= '<label for="anclado">Anclado</label>';
            $html .= '<input class="text-wrap" type="text" name="pacd_requerido[]" id="pacd_requerido" value="'.$packing_detalle[$i]["pacd_requerido"].'">';
            $html .= '<input type="hidden" name="pacd_cantidad[]" id="pacd_cantidad" value="'.$packing_detalle[$i]["pacd_cantidad"].'">';
            $html .= '<input type="hidden" class="codigobarra_clase" name="codigobarra_pro[]" id="codigobarra_pro" value="'.$packing_detalle[$i]["codigobarra_pro"].'">';
            $html .= '<input type="hidden" name="referencia_pro[]" id="referencia_pro" value="'.$packing_detalle[$i]["referencia_pro"].'">';
            $html .= '</div>';
            $html .= '</div>';
        endfor;
        $ofoto = $packing_detalle = $i = null;
        unset($ofoto, $packing_detalle, $i);
        return $html;
    }


    /*************************************************************************
    *                        HTML PACKING bulto
    * Genera el HTML necesario para agregar un bulto a DOM con todos sus
    * botones.
    *************************************************************************/
    function html_packing_bulto($idbulto,$status){
        $html  = '';
        $html .= '<div id="box-'.$idbulto.'" class="box-packing">';
        $html .= '<div id="box-btn-'.$idbulto.'" class="box-packing-buttons">';
        if ($status == 0){
            $html .= '<button class="btn btn-secondary" style="height:45px;font-size:1.5rem;" onclick="box_open('.$idbulto.')" data-bs-toggle="tooltip" title="Cerrar Caja"><i id="icon-lock" class="icon-unlocked"></i></button>';
        }else{
            $html .= '<button class="btn btn-secondary" style="height:45px;font-size:1.5rem;" onclick="box_open('.$idbulto.')" data-bs-toggle="tooltip" title="Abrir Caja"><i id="icon-lock" class="icon-lock"></i></button>';
        }
        $html .= '<button class="btn btn-secondary" style="height:45px;font-size:1.5rem;" onclick="print_box('.$idbulto.')" data-bs-toggle="tooltip" title="Imprime etiqueta"><i class="icon-noun-open-bill"></i></button>';
        $html .= '</div>';
        if ($status == 0){
            $html .= '<div id="box-id-'.$idbulto.'" class="box-packing-number box-open" ondrop="drop(event)" ondragover="allowDrop(event)" onclick="pack_box('.$idbulto.')">';
        }else{
            $html .= '<div id="box-id-'.$idbulto.'" class="box-packing-number box-close" ondrop="drop(event)" ondragover="allowDrop(event)" onclick="pack_box('.$idbulto.')">';
        }
        $html .= '<p ondrop="drop(event)" ondragover="allowDrop(event)">'.$idbulto.'</p>';
        //$html .= '<div style="background: rgba(255,255,255,0.5);padding:4px;color:rgb(109, 109, 109);font-size: 18px;font-weight:500;width:70px;text-align:center;">99999</div>';
        $html .= '<input type="hidden" id="box-cant" value="" readonly disabled autocomplete="off" ondrop="drop(event)" ondragover="allowDrop(event)">';
        $html .= '<input type="hidden" id="box-id" value="'.$idbulto.'" readonly disabled autocomplete="off" ondrop="drop(event)" ondragover="allowDrop(event)">';
        $html .= '</div></div>';
        $idbulto = $status = null;
        unset($idbulto, $status);
        return $html;
    }

    
    /*************************************************************************
    *                       INSERT PACKING BULTO
    * Insertamos un nuevo bulto a la tarea de packing
    *************************************************************************/
    function insert_packing_bulto($idpacking,$idbulto){
        $data_maria               = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                 = $data_maria->getConnection();
        $opacking                 = new Packing($db_maria);
        $opacking->pack_idempresa = 1;
        $opacking->pack_idpacking = $idpacking;
        $opacking->user_crea      = $_SESSION['username'];
        $opacking->insert_packing_bulto($idbulto,0,0);

        if (!$opacking->error){
            $out = array();
            $out["response"]   = 'success';
            $out["idpacking"]  = $opacking->pack_idpacking;
            $out["html"]       = html_packing_bulto($idbulto,0);
        }else{
            $out["response"]   = "fail";
            $out["error_nro"]  = $opacking->error_nro;
            $out["error_msj"]  = $opacking->error_msj;
            $out["error_file"] = $opacking->error_file;
            $out["error_line"] = $opacking->error_line;
            $out["error_tpo"]  = 'error';
        }
        $data_maria = $db_maria = $opacking = null;
        unset($data_maria,$db_maria,$opacking);
        return $out;
    }

    /*************************************************************************
    *                       INSERT PRODUCTOS BULTOS
    * Cargamos los datos de la tarea de Picking Ya que necesitamos
    * el ID del Pedido. 
    *************************************************************************/
    function insert_productos_bulto(){
        $data_maria                 = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                   = $data_maria->getConnection();
        $opacking                   = new Packing($db_maria);
        $bulto                      = test_input($_POST["idbulto"]);
        $idproducto                 = test_input($_POST["info-idproducto"]);
        $requerido                  = (int) test_input($_POST["requerido"]);
        $porembalar                 = (int) test_input($_POST["disponible"]);
        $cantidad                   = (int) test_input($_POST["cantidad"]);
        $opacking->pack_idempresa   = 1;
        $opacking->pack_idpacking   = test_input($_POST["idpacking"]);
        $opacking->user_mod         = $_SESSION['username'];

        $opacking->conn->beginTransaction();
        $opacking->update_packing_producto($idproducto,$cantidad);
        if (!$opacking->error){
            $opacking->insert_packing_productos($opacking->pack_idpacking, $bulto, $idproducto, $cantidad, $_SESSION['username']);
        }
        if (!$opacking->error){
            $opacking->conn->commit();
            $out["response"]  = "success";
            $out["texto"]     = "datos guardados con exito.";
            $out["idpacking"] = $opacking->pack_idpacking;
            $out["cantidad"]  = $cantidad;
            $out["semaforo"]  = packing_semaforo($requerido, $requerido-$porembalar + $cantidad);
            $out["embalaje"]  = $porembalar == $cantidad ? 'total':'parcial';
        }else{
            $opacking->conn->rollback();
            $out["response"]   = "fail";
            $out["error_nro"]  = $opacking->error_nro;
            $out["error_msj"]  = $opacking->error_msj;
            $out["error_file"] = $opacking->error_file;
            $out["error_line"] = $opacking->error_line;
            $out["error_tpo"]  = 'error';
        }
        $bulto = $idproducto = $cantidad = $requerido = $porembalar = null;
        $data_maria = $db_maria = $opacking = null;
        unset($data_maria, $db_maria, $opacking, $bulto, $idproducto, $requerido, $porembalar, $cantidad);
        return $out;
    }

    /*************************************************************************
    *                       DELETE BOX PRODUCTO
    * Quita o Borra de un bulto un producto determinado.
    *************************************************************************/
    function delete_box_producto($idpacking, $idbulto, $idproducto){
        $data_maria                 = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                   = $data_maria->getConnection();
        $opacking                   = new Packing($db_maria);
        $opacking->pack_idempresa   = 1;
        $opacking->pack_idpacking   = $idpacking;

        $db_maria->beginTransaction();
        $opacking->delete_packing_boxproducto($idbulto,$idproducto);
        if (!$opacking->error){
            $db_maria->commit();
            $out["response"]       = "success";
            $out["texto"]          = "datos guardados con exito.";
            $out["pack_idpacking"] = $opacking->pack_idpacking;
        }else{
            $db_maria->rollback();
            $out["response"]   = "fail";
            $out["error_nro"]  = $opacking->error_nro;
            $out["error_msj"]  = $opacking->error_msj;
            $out["error_file"] = $opacking->error_file;
            $out["error_line"] = $opacking->error_line;
            $out["error_tpo"]  = 'error';
        }
        $data_maria = $db_maria = $opacking = null;
        $idpacking = $idbulto = $idproducto = null;
        unset($data_maria, $db_maria, $opacking, $idpacking, $idbulto, $idproducto);
        return $out;
    }

    /*************************************************************************
    *                          DELETE BOX
    * Quita o Borra un bulto determinado.
    *************************************************************************/
    function delete_box($idpacking, $idbulto){
        $data_maria                 = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                   = $data_maria->getConnection();
        $opacking                   = new Packing($db_maria);
        $opacking->pack_idempresa   = 1;
        $opacking->pack_idpacking   = $idpacking;
        
        $db_maria->beginTransaction();
        $opacking->delete_packing_box($idbulto);
        if (!$opacking->error){
            $db_maria->commit();
            $out["response"]       = "success";
            $out["texto"]          = "datos guardados con exito.";
            $out["pack_idpacking"] = $opacking->pack_idpacking;
        }else{
            $db_maria->rollback();
            $out["response"]   = "fail";
            $out["error_nro"]  = $opacking->error_nro;
            $out["error_msj"]  = $opacking->error_msj;
            $out["error_file"] = $opacking->error_file;
            $out["error_line"] = $opacking->error_line;
            $out["error_tpo"]  = 'error';
        }
        $data_maria = $db_maria = $opacking = null;
        $idpacking = $idbulto = null;
        unset($data_maria,$db_maria, $opacking, $idpacking, $idbulto);
        return $out;
    }

    /*************************************************************************
    *                      CIERRA TAREA DE PACKING
    * Se cierra la tarea de packing y se cambia el status del Pedido.
    * A partir de este momento no se debe permitir modificaciones a la tarea
    * de packing, esto está validado a nivel de base de datos.
    *************************************************************************/
    function cierra_tarea_packing(){
        /*Cargamos los datos de la tarea de Packing Ya que necesitamos
          saber todos los productos que fueron empacados */
        $data_maria               = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                 = $data_maria->getConnection();
        $opacking                 = new Packing($db_maria);
        $opacking->pack_idempresa = 1;
        $opacking->pack_idpacking = test_input($_POST["pack_idpacking"]);
        $opacking->user_mod       = $_SESSION['username'];
        $opacking->carga_packing_master();
        $opacking->pack_observacion = test_input($_POST["pack_observacion"]);
        $opacking->carga_packing_detalle();
        
        // Buscamos el Pedido
        $data_sqlsrv = new Db_sqlsrv(); // Nueva conexión a SQL Server
        $db_sqlsrv   = $data_sqlsrv->getConnection();
        $opedido     = new Pedido($db_sqlsrv);
        $opedido->numero_ped = $opacking->pack_idpedido;

        //comenzamos las transacciones para ambos tipos de datos.
        $db_maria->beginTransaction();
        $db_sqlsrv->beginTransaction();
        // Para que un Pedido en el Xenx pueda facturarse este debe estar en el status de PENDIENTE

        $opedido->cierra_pedido('PENDIENTE',$opacking->packing_detalle);

        if (!$opedido->error){
            $opacking->cierra_tarea_packing();
        }

        if (!$opedido->error){
            if (!$opacking->error){
                $db_sqlsrv->commit();
                $db_maria->commit();
                $out["response"]       = "success";
                $out["texto"]          = "datos guardados con exito.";
                $out["pack_idpacking"] = $opacking->pack_idpacking;
            }else{
                $db_sqlsrv->rollback();
                $db_maria->rollback();
                $out["response"]   = "fail";
                $out["error_nro"]  = $opacking->error_nro;
                $out["error_msj"]  = $opacking->error_msj;
                $out["error_file"] = $opacking->error_file;
                $out["error_line"] = $opacking->error_line;
                $out["error_tpo"]  = 'error';
            }
        }else{
            $db_sqlsrv->rollback();
            $db_maria->rollback();
            $out["response"]   = "fail";
            $out["error_nro"]  = $opedido->error_nro;
            $out["error_msj"]  = $opedido->error_msj;
            $out["error_file"] = $opedido->error_file;
            $out["error_line"] = $opedido->error_line;
            $out["error_tpo"]  = 'error';
        }
        $opedido = $opacking = null; // Liberamos memoria
        $data_sqlsrv = $db_sqlsrv = null; // Liberamos memoria
        $data_maria  = $db_maria  = null; // Liberamos memoria
        $_POST["pack_idpacking"] = $_POST["pack_observacion"] = null;
        unset($data_sqlsrv, $db_sqlsrv, $data_maria, $db_maria, $opedido, $opacking, $_POST["pack_idpacking"],$_POST["pack_observacion"]); // destruimos objeto
        return $out;
    }


    /*************************************************************************
    *                        PACKING SEMAFORO
    * En las pantallas trabajamos con semáforos que indican el status de 
    * picking o del packing de un producto determinado.
    * El valor retornado son clases CSS que colocan el color al semaforo.
    *************************************************************************/
    function packing_semaforo($requerido,$cantidad){
        $clases = 'semaforo ';
        if (is_null($cantidad)){
            $clases .= "semaforo-blanco";
        }elseif ($requerido == $cantidad){
            $clases .= "semaforo-verde";
        }elseif ($cantidad == 0){
            $clases .= "semaforo-rojo";
        }elseif ($requerido > $cantidad){
            $clases .= "semaforo-amarillo";
        }
        $requerido = $cantidad = null;
        unset($requerido, $cantidad);
        return $clases;
    }

    /*************************************************************************
    *                        EMBALA AUTOMATIC
    * Crea bultos automáticamente para aquellos productos que por si solos se
    * embalan como 1 bulto o aquellos que superen o igualen la cantidad
    * de unidades requeridas para un bulto.
    *************************************************************************/    
    function embala_automatic(){
        $data_maria               = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                 = $data_maria->getConnection();
        $opacking                 = new Packing($db_maria);
        $idbulto                  = test_input($_POST["bulto"]);
        $idproducto               = null;
        $unidad                   = null;
        $peso                     = null;
        $cantidad                 = null;
        $requerido                = null;
        $bulto_orig               = null;
        $opacking->pack_idempresa = 1;
        $opacking->pack_idpacking = test_input($_POST["idpacking"]);
        $opacking->user_crea      = $_SESSION['username'];
        $opacking->user_mod       = $_SESSION['username'];
        $opacking->carga_packing_detalle();
        $opacking->conn->beginTransaction();
        for ($i=0; $i<count($opacking->packing_detalle); $i++):
            /* Hacemos un loop por los productos y buscamos solo los que no hayan sido empacados 
               por completo, es decir, aquellos que lo requerido sea mayor a la cantidad */
            $idproducto = $opacking->packing_detalle[$i]["pacd_idproducto"];
            $unidad     = $opacking->packing_detalle[$i]["unidad_pro"];
            $cantidad   = empty($opacking->packing_detalle[$i]["pacd_cantidad"]) ? 0 : $opacking->packing_detalle[$i]["pacd_cantidad"] ;
            $requerido  = $opacking->packing_detalle[$i]["pacd_requerido"];
            $peso       = $opacking->packing_detalle[$i]["peso_pro"];
            $bulto_orig = $opacking->packing_detalle[$i]["bultooriginal_pro"];
            if (($cantidad < $requerido) && (!$opacking->error)){
                // echo "la cantidad es menor que lo requerido \n";
                // echo "Codigo Producto:".$idproducto."\n";
                // echo "Cantidad:".$cantidad."\n";
                // echo "Requerido:".$requerido."\n";
                // echo "bulto_orig:".$bulto_orig."\n";
                /* Ahora preguntamos si es un cuñete */
                if ($unidad == 'CÑT'){
                    $cantidad  = empty($cantidad) ? 0 : $cantidad;
                    $requerido = empty($requerido) ? 0 : $requerido;
                    for ($j=$cantidad; $j<$requerido; $j++):
                        $idbulto++;
                        $opacking->insert_packing_bulto($idbulto, 1, $peso,'Kg');
                        if (!$opacking->error){
                            $opacking->insert_packing_productos($opacking->pack_idpacking, $idbulto, $idproducto, 1, $opacking->user_mod);
                        }

                        if ($opacking->error){
                            break;
                        }
                    endfor;
                    if (!$opacking->error){
                        $opacking->update_packing_producto($idproducto,$j);                    
                    }
                    $j = null;
                    unset($j);
                }elseif ((!empty($bulto_orig)) && ($requerido >= $bulto_orig)){
                    // echo "*** No es cuñete, pero coincide con el bulto original***\n";
                    $ciclos = intval(($requerido - $cantidad) / $bulto_orig);
                    // echo "ciclos:".$ciclos."\n";

                    for ($j=0;$j<$ciclos;$j++){
                        $idbulto++;
                        $opacking->insert_packing_bulto($idbulto, 1, $peso,'Kg');
                        if (!$opacking->error){
                            $opacking->insert_packing_productos($opacking->pack_idpacking, $idbulto, $idproducto, $bulto_orig, $opacking->user_mod);
                        }
                    }
                    if (!$opacking->error){
                        $opacking->update_packing_producto($idproducto,$bulto_orig * $ciclos);
                    }
                    $ciclo = $j = null;
                    unset($ciclo, $j);
                }
            }
            if ($opacking->error){
                break;
            }
        endfor;
        if (!$opacking->error){
            $opacking->conn->commit();
            $out["response"]  = "success";
            $out["texto"]     = "datos guardados con exito.";
            $out["idpacking"] = $opacking->pack_idpacking;
            $out["bulto"]     = $idbulto;
        }else{
            $opacking->conn->rollback();
            $out["response"]   = "fail";
            $out["error_nro"]  = $opacking->error_nro;
            $out["error_msj"]  = $opacking->error_msj;
            $out["error_file"] = $opacking->error_file;
            $out["error_line"] = $opacking->error_line;
            $out["error_tpo"]  = 'error';
        }
        $data_maria = $db_maria = $opacking = $idbulto = $bulto_orig = null;
        unset($data_maria, $db_maria, $opacking, $idbulto, $bulto_orig);
        return $out;
    }


    /*************************************************************************
    *                        CARGA BULTOS PEDIDO
    * Dado un número de pedido, devuelve todos los bultos que posee un pedido.
    * Obviamente este pedido tuvo que haber pasado por Packing.
    *************************************************************************/    
    function carga_bultos_pedido($idpedido){
        /* Buscamos en Packing el Nro. de Pedido.*/
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $opacking   = new Packing($db_maria);
        $html       = "";
        $nota       = "";
        $sql  = "SELECT a.pack_idpacking, a.pack_status FROM vpacking a WHERE a.pack_idpedido = :idpedido";

        $stmt = $db_maria->prepare($sql);
        $stmt->bindparam(':idpedido', $idpedido);
        $stmt->bindcolumn('pack_idpacking', $idpacking);
        $stmt->execute();
        $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowcount() == 1){
            $opacking->pack_idempresa = 1;
            $opacking->pack_idpacking = $idpacking;
            $opacking->carga_packing_bultos('<TODOS>');
            //$resp = $opacking->packing_bultos;
            array_walk($opacking->packing_bultos, function(&$a){
                unset($a['pack_status']);
            });

            $nota .= 'Bultos:'.count($opacking->packing_bultos);
            for ($i=0; $i<count($opacking->packing_bultos); $i++):
                $html .= '<h6 class="bulto-titulo">Bulto '.$opacking->packing_bultos[$i]["pack_idbulto"].' ('.$opacking->packing_bultos[$i]["pack_peso"].$opacking->packing_bultos[$i]["pack_unidadpeso"].')</h6>';
                if ($opacking->packing_bultos[$i]["pack_peso"] > 0) {
                    $nota .= ' P'.$opacking->packing_bultos[$i]["pack_idbulto"].' ('.$opacking->packing_bultos[$i]["pack_peso"].$opacking->packing_bultos[$i]["pack_unidadpeso"].')';
                }
                $opacking->carga_packing_productos($opacking->packing_bultos[$i]["pack_idbulto"]);
                $html .= '<table class="productos-bultos table table-striped table-hover table-bordered table-sm">';
                $html .= '<tbody>';
                for ($j=0; $j<count($opacking->packing_productos); $j++):
                    $html .= '<tr class="bulto-prod-linea">';
                    $html .= '<td scope="row" class="bulto-prod-idproducto">'.$opacking->packing_productos[$j]["pacp_idproducto"].'</td>';
                    $html .= '<td class="bulto-prod-nombre">'.ucwords(strtolower($opacking->packing_productos[$j]["nombre_pro"])).'</td>';
                    $html .= '<td class="bulto-prod-cantidad">'.$opacking->packing_productos[$j]["pacp_cantidad"].'</td>';
                    $html .= '</tr>';
                endfor;
                $html .= '</tbody></table>';
            endfor;
            $html .= '</tbody></table>';
            $resp["response"]    = "success";
            $resp["nota"]        = $nota;
            $resp["html_bultos"] = $html;
            $resp["idpacking"]   = $idpacking;
        }else{
            $resp["response"]  = "fail";
            $resp["error_msj"] = "Tarea de Packing no culminada o no existe";
        }
        $data_maria = $db_maria = $sql = $stmt = $opacking = null;
        unset($data_maria, $db_maria, $sql, $stmt, $opacking);
        return $resp;
    }
?>

