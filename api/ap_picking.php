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
        if (isset($_POST["accion"]) && $_POST["accion"]=="picking-insert"){
            inserta_picking();
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=="picking-close"){
            $out = cierra_tarea_picking();
            http_response_code(200);
            echo json_encode($out);
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=="picking-consolida"){
            $out = consolida_tarea_picking();
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
    }elseif ($metodo=='GET'){
        if (isset($_GET["idpicking"])){
            require_once '../clases/clase_foto.php';
            $id_picking = test_input($_GET["idpicking"]);
            $accion     = test_input($_GET["accion"]);
            if ($accion=='picking-tarea' || $accion=='picking-verifica'){
                $out = carga_picking_tarea($id_picking,$accion);
            }elseif ($accion=='picking-bins'){
                $out = carga_picking_bins($id_picking);
            }
            // $out = utf8_encode($html);
            // $out = json_encode(["html_lista"=>$html],JSON_UNESCAPED_SLASHES); // NO FUNCIONA
            // $out = json_encode(["html_lista"=>$html],JSON_UNESCAPED_UNICODE); // NO FUNCIONA
            // $out = json_encode(["html_lista"=>$html]); // NO FUNCIONA
            // $out = json_encode(["html_lista"=>$html],JSON_HEX_QUOT); // NO FUNCIONA
            // $out = json_encode(["html_lista"=>$html],JSON_UNESCAPED_UNICODE); // NO FUNCIONA
            // $out = json_encode(["html_lista"=>$html],JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            // $out = json_encode(["html_lista"=>$html],JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
            // $out = json_encode($out,JSON_INVALID_UTF8_SUBSTITUTE);
            // $out = json_encode(["html_lista"=>$html],JSON_INVALID_UTF8_SUBSTITUTE);
            // echo json_encode($out); // Si tiene "ñ" o "Ñ" no va a funcionar
            http_response_code(200);
            unset($id_picking,$_GET["idpicking"]);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        }elseif (isset($_GET["idproducto"])){
            require_once '../clases/clase_producto.php';
            $idproducto = test_input($_GET["idproducto"]);
            $anclados   = get_anclados($idproducto);
            $pendientes = get_pendientes($idproducto);
            $disp = json_encode(["anclados"=>$anclados+$pendientes]);
            unset($idproducto, $anclados, $pendientes);
            http_response_code(200);
            echo $disp;
        }
    }elseif ($metodo=='PATCH'){
        $idpicking  = test_input($_GET["idpicking"]);
        $data       = json_decode(file_get_contents("php://input"));
        $accion     = test_input($data->accion);
        if ($accion == 'picking-producto'){
            $idproducto = test_input($data->idproducto);
            $requerido  = test_input($data->requerido);
            $cantidad   = test_input($data->cantidad);
            $ubicacion  = test_input($data->ubicacion);
            /* El campo picd_cantidad acepta valores nulos. Significa que el preparador no ha hecho nada con ese producto.
            En el sistema un campo puede tener un valor nulo o un entero a partir del cero.
            El problema viene desde el HTML cuando trae una cadena vacía... cuando está
            vacía la cambiamos a nulo*/
            if (!is_numeric($cantidad)){
                $cantidad = null;
            }
            $out = actualiza_picking_producto($idpicking,$idproducto,$requerido,$cantidad,$ubicacion);
            http_response_code(200);
            echo json_encode($out);
        }elseif ($accion == 'picking-cantverif'){
            $idproducto = test_input($data->idproducto);
            $anclado    = test_input($data->anclado);
            $cantverif  = test_input($data->cantverif);
            /* El campo picd_cantidad acepta valores nulos. Significa que el preparador no ha hecho nada con ese producto.
            En el sistema un campo puede tener un valor nulo o un entero a partir del cero.
            El problema viene desde el HTML cuando trae una cadena vacía... cuando está
            vacía la cambiamos a nulo*/
            if (!is_numeric($cantverif)){
                $cantverif = null;
            }
            $out = actualiza_picking_verifica($idpicking, $idproducto, $anclado, $cantverif);
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
    *                         INSERTA PICKING
    * Inserta o graba una nueva tarea de picking en base de datos. Esto 
    * ocurre cuando se asigna un pedido a un preparador.
    *************************************************************************/
    function inserta_picking(){
        // Buscamos el Pedido
        $data_sqlsrv = new Db_sqlsrv();  // Nueva conexión a SQL Server
        $db_sqlsrv   = $data_sqlsrv->getConnection();

        $preparador = isset($_POST["preparador"]) ? test_input($_POST["preparador"]) : $_SESSION['username'];
        $prioridad  = isset($_POST["prioridad"]) ? test_input($_POST["prioridad"]) : 0;

        $opedido = new Pedido($db_sqlsrv);
        $opedido->numero_ped = test_input($_POST["idpedido"]);
        $opedido->carga_datos();

        // Asignamos datos al objeto de Picking
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $opicking   = new Picking($db_maria);

        //comenzamos la transacción. Si al grabar en TbPedidos
        $opedido->conn->beginTransaction();
        $opicking->conn->beginTransaction();
        if(!$opedido->error){
            $opicking->pick_idempresa   = 1; 
            $opicking->pick_idpedido    = $opedido->numero_ped;
            $opicking->pick_prioridad   = $prioridad;
            $opicking->pick_preparador  = $preparador;
            $opicking->user_crea        = $_SESSION['username'];
            // Copiamos los productos del Pedido a la estructura de Picking.
            $opicking->picking_detalle = array();
            for ($i=0; $i<count($opedido->pedido_detalle); $i++):
                $opicking->picking_detalle[$i]['picd_idempresa']  = $opicking->pick_idempresa;
                $opicking->picking_detalle[$i]['picd_idproducto'] = $opedido->pedido_detalle[$i]["producto_ped"];
                $opicking->picking_detalle[$i]['picd_unidad']     = $opedido->pedido_detalle[$i]["unidad_pro"];
                $opicking->picking_detalle[$i]['picd_idalmacen']  = 1;
                $opicking->picking_detalle[$i]['picd_ubicacion']  = $opedido->pedido_detalle[$i]["ubicacion_pro"];
                $opicking->picking_detalle[$i]['picd_requerido']  = $opedido->pedido_detalle[$i]["cantidad_ped"];
            endfor;

            $opedido->update_pedidos_status('PICKING'); // Pasamos a picking

            if (!$opedido->error){
                $opicking->insertar_datos();
            }
        }

        $out = [];
        if (!$opedido->error){
            if (!$opicking->error){
                $opedido->conn->commit();
                $opicking->conn->commit();
                $out["response"]  = "success";
                $out["texto"]     = "datos guardados con exito.";
                $out["idpicking"] = $opicking->pick_idpicking;
                http_response_code(201);
            }else{
                $opedido->conn->rollback();
                $opicking->conn->rollback();
                $out["response"]  = "fail";
                $out["error_nro"] = $opicking->error_nro;
                $out["error_msj"] = $opicking->error_msj;
                $out["error_tpo"] = $opicking->error_tpo;
                http_response_code(400);
            }
        }else{
            $opedido->conn->rollback();
            $opicking->conn->rollback();
            $out["response"]  = "fail";
            $out["error_nro"] = $opedido->error_nro;
            $out["error_msj"] = $opedido->error_msj;
            $out["error_tpo"] = $opedido->error_tpo;
            http_response_code(400);
        }
        $data_sqlsrv = $db_sqlsrv = null;
        $data_maria = $db_maria = null;
        $opedido = $opicking = null; // Liberamos memoria
        $preparador = null;
        unset($data_sqlsrv, $db_sqlsrv, $data_maria, $db_maria, $opedido, $opicking, $preparador); // destruimos objeto
        echo json_encode($out); 
    }
    
    
    /*************************************************************************
    *                      CARGA TAREA DE PICKING
    * Carga en memoria una tarea de picking.
    *************************************************************************/
    function carga_picking_tarea($pid_picking,$accion){
        $html_lista = "";
        $data_maria                 = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                   = $data_maria->getConnection();
        $opicking                   = new Picking($db_maria);
        $opicking->pick_idpicking   = $pid_picking;
        $opicking->carga_picking_master(); 
        if (!$opicking->error) {
            $opicking->picking_detalle = carga_picking_tarea_detalle($db_maria,$opicking->pick_idpicking); //Esta función esta en este archivo. Ver Nota más abajo.
            if ($accion == 'picking-tarea'){
                $html_lista  = html_picking_list($opicking->picking_detalle);
            }elseif ($accion == 'picking-verifica'){
                // $opicking->carga_picking_bins(); 
                // $bins        =  array_column($opicking->picking_bins, 'picc_bin');
                $html_lista  = html_verifica_list($opicking->picking_detalle);
            }else{
                $html_lista  = '<div class="alert alert-danger" role="alert">';
                $html_lista .= 'No se determinó el tipo de tarea de picking'.'<br>';
                $html_lista .= '</div>';
            }
            $picking_productos = array();
            for ($i=0; $i<count($opicking->picking_detalle); $i++):
                $picking_productos[$i] = array(
                    "id_producto"=>$opicking->picking_detalle[$i]["picd_idproducto"],
                    "codigobarra"=>$opicking->picking_detalle[$i]["codigobarra_pro"],
                    "ubicacion"  =>$opicking->picking_detalle[$i]["ubicacion_pro"],
                    "existencia" =>(int)$opicking->picking_detalle[$i]["deposito1_pro"],
                    "requerido"  =>$opicking->picking_detalle[$i]["picd_requerido"],
                    "cantidad"   =>$opicking->picking_detalle[$i]["picd_cantidad"],
                    "unidad"     =>$opicking->picking_detalle[$i]["picd_unidad"],
                    "cantverif"  =>$opicking->picking_detalle[$i]["picd_cantverif"],
                    "referencia" =>$opicking->picking_detalle[$i]["referencia_pro"]
                );
            endfor;
        }
        $out = array();
        $out["idpicking"]    = $opicking->pick_idpicking;
        $out["fecha"]        = date_format(date_create($opicking->pick_fecha),'d/m/Y h:i');
        $out["idpedido"]     = $opicking->pick_idpedido;
        $out["idvendedor"]   = $opicking->vendedor_ped;
        $out["vendedor"]     = ucwords(strtolower($opicking->nombre_ven));
        $out["cliente"]      = ucwords(strtolower($opicking->nombre_cli));
        $out["pista"]        = $opicking->pick_pista;
        $out["observacion"]  = $opicking->pick_observacion;
        $out["preparador"]   = $opicking->pick_preparador;
        $out["picking_productos"] = $picking_productos; 
        $out["paletas"]      = "no tiene ubicacion en pistas";
        $out["html_lista"]   = $html_lista;
        $data_maria = $db_maria = null;
        $opicking = $html_lista = $picking_productos = $i = null;
        unset($data_maria, $db_maria); // destruimos objeto
        unset($opicking, $html_lista, $picking_productos, $i);
        return $out;
    }

    /*************************************************************************
    *                      CARGA PICKING BINS
    * Carga las paletas o las cestas en las que se encuentra consolidad
    * el pedido.
    *************************************************************************/
    function carga_picking_bins($pid_picking){
        $bins       = [];
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $opicking   = new Picking($db_maria);
        $opicking->pick_idpicking   = $pid_picking;
        $opicking->carga_picking_master(); 
        $opicking->carga_picking_bins(); 
        $bins =  array_column($opicking->picking_bins, 'picc_bin');
        $out["paletas"] = json_encode($bins);
        $data_maria = $db_maria = null;
        $opicking = $bins = null;
        unset($data_maria, $db_maria); // destruimos objeto
        unset($opicking, $bins);
        return $out;
    }    


    /*************************************************************************
    *                 CARGA PICKING TAREA DETALLE
    * No se usa la función de la clase ya que esa función carga la ubicación
    * de las selecciones ya guardadas. Esta es una tarea de picking en
    * ejecución por lo tanto debemos traer la ubicación y la existencia
    * al momento que se carga la tarea en pantalla.
    *************************************************************************/
    function carga_picking_tarea_detalle($dbconexion, $id_picking){
        $sql = "SELECT a.picd_idempresa, a.picd_idpicking, a.picd_idproducto, b.unidad_pro as picd_unidad,
        a.picd_idalmacen, a.picd_ubicacion, a.picd_requerido, a.picd_cantidad, a.picd_cantverif,
        b.nombre_pro, b.existencia_pro, b.deposito1_pro, b.deposito2_pro, b.ubicacion_pro, b.codigobarra_pro, b.bultooriginal_pro, referencia_pro
        FROM vpicking_detalle a
        JOIN tbproductos b ON b.codigo_pro = a.picd_idproducto
        WHERE a.picd_idempresa = 1 AND a.picd_idpicking =:idpicking ORDER BY b.ubicacion_pro";
    
        $stmt = $dbconexion->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idpicking',$id_picking, PDO::PARAM_INT);
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        $dbconexion = $id_picking = null;
        $sql = $stmt = null;
        unset($sql, $stmt);
        unset($dbconexion, $id_picking); // destruimos objeto
        return $resp;
    }


    /*************************************************************************
    *                 HTML PICKING LIST
    * Esta función genera el html necesario para generar la lista de productos
    * de una tarea de picking
    *************************************************************************/
    function html_picking_list($picking_detalle){
        $html  = '';
        $ofoto = new foto();
        for ($i=0; $i<count($picking_detalle); $i++):
            $foto  = $ofoto->genera_html_foto($picking_detalle[$i]["picd_idproducto"].'-1-lefre-','SM');
            $html .= '<div class="picking-prod" id="picking-'.$picking_detalle[$i]["picd_idproducto"].'" onclick="pick_producto(\''.$picking_detalle[$i]["picd_idproducto"].'\')">';
            $html .= '<div class="picking-prod-status">';
            $html .= '<span id="semaforo" class="'.picking_semaforo($picking_detalle[$i]["picd_requerido"],$picking_detalle[$i]["picd_cantidad"]).'"></span>';
            $html .= '</div>';
            $html .= '<div class="picking-prod-foto" id="foto-'.$picking_detalle[$i]["picd_idproducto"].'">';
            $html .= $foto;
            $html .= '</div>';
            $html .= '<div class="picking-prod-detalle">';
            $html .= '<div class="picking-detalle-header">';
            $html .= '<span style="font-size:100%;" class="badge bg-success">'.$picking_detalle[$i]["picd_idproducto"].'</span>';
            $html .= '</div>';
            $html .= '<p class="text-wrap mb-0" id="picking-nombre-pro-'.$picking_detalle[$i]["picd_idproducto"].'">'.ucwords(strtolower($picking_detalle[$i]["nombre_pro"])).'</p>';
            $html .= '<div class="picking-prod-detalle-ubica">';
            $html .= '<div>';
            $html .= '<span><strong>Ubicación:&nbsp</strong></span><span>'.$picking_detalle[$i]["ubicacion_pro"].'</span>';
            $html .= '</div>';
            $html .= '<div style="text-align:right;">';
            $html .= '<span>Bulto Orig.:&nbsp</span><span id="picking-bulto">'.$picking_detalle[$i]["bultooriginal_pro"].'</span>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="picking-prod-requerido">';
            $html .= '<label for="anclado">Requerido</label>';
            $html .= '<input class="text-wrap" type="text" name="picd_requerido[]" id="picd_requerido" value="'.$picking_detalle[$i]["picd_requerido"].'">';
            $html .= '<input type="hidden" name="picd_cantidad[]" id="picd_cantidad" value="'.$picking_detalle[$i]["picd_cantidad"].'">';
            $html .= '<input type="hidden" name="picking-ref" id="picking-ref" value="'.$picking_detalle[$i]["referencia_pro"].'">';
            $html .= '<input type="hidden" class="codigobarra_clase" name="codigobarra_pro[]" id="codigobarra_pro" value="'.$picking_detalle[$i]["codigobarra_pro"].'">';
            $html .= '</div>';
            $html .= '</div>';
        endfor;
        $ofoto = $picking_detalle = $i = null;
        unset($ofoto, $picking_detalle, $i);
        return $html;
    }

    /*************************************************************************
    *                            HTML VERIFICA LIST
    * Esta función genera el html necesario para generar la lista de productos
    * para verificar una tarea de picking
    *************************************************************************/
    function html_verifica_list($picking_detalle){
        $html  = '';
        $ofoto = new foto();
        for ($i=0; $i<count($picking_detalle); $i++):
            $foto  = $ofoto->genera_html_foto($picking_detalle[$i]["picd_idproducto"].'-1-lefre-','SM');
            $html .= '<div class="picking-prod" id="picking-'.$picking_detalle[$i]["picd_idproducto"].'" onclick="pick_producto(\''.$picking_detalle[$i]["picd_idproducto"].'\')">';
            $html .= '<div class="picking-prod-status">';
            $html .= '<span id="semaforo" class="'.picking_semaforo($picking_detalle[$i]["picd_cantidad"],$picking_detalle[$i]["picd_cantverif"]).'"></span>';
            $html .= '</div>';
            $html .= '<div class="picking-prod-foto" id="foto-'.$picking_detalle[$i]["picd_idproducto"].'">';
            $html .= $foto;
            $html .= '</div>';
            $html .= '<div class="picking-prod-detalle">';
            $html .= '<div class="picking-detalle-header">';
            $html .= '<span style="font-size:100%;" class="badge bg-success">'.$picking_detalle[$i]["picd_idproducto"].'</span>';
            $html .= '<div style="text-align:right;">';
            $html .= '<span>Requerido:&nbsp</span><span id="picd_requerido">'.$picking_detalle[$i]["picd_requerido"].'</span>';
            $html .= '</div>';
            // $html .= '<div class="d-flex justify-content-end">';
            // $html .= '<label for="picd_requerido">Requerido:&nbsp</label>';
            // $html .= '<input type="text" class="col-3 border-0 disabled" name="picd_requerido[]" id="picd_requerido" value="'.$picking_detalle[$i]["picd_requerido"].'" readonly>';
            // $html .= '</div>';
            $html .= '</div>';
            $html .= '<p class="text-wrap mb-0" id="picking-nombre-pro-'.$picking_detalle[$i]["picd_idproducto"].'">'.ucwords(strtolower($picking_detalle[$i]["nombre_pro"])).'</p>';
            $html .= '<div class="picking-prod-detalle-ubica d-flex justify-content-between">';
            $html .= '<div>';
            $html .= '<label for="picd_unidad">Unidad:</label>';
            $html .= '<input id="picd_unidad" type="text" name="picd_unidad" class="col-3 border-0 disabled" readonly disabled autocomplete="off" value="'.$picking_detalle[$i]["picd_unidad"].'">';
            $html .= '</div>';
            $html .= '<div style="text-align:right;">';
            $html .= '<span>Bulto Orig.:&nbsp</span><span id="picking-bulto">'.$picking_detalle[$i]["bultooriginal_pro"].'</span>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="picking-prod-requerido">';
            $html .= '<label for="picd_cantidad">Anclado</label>';
            $html .= '<input class="text-wrap" type="text" name="picd_cantidad[]" id="picd_cantidad" value="'.$picking_detalle[$i]["picd_cantidad"].'">';
            $html .= '<input type="hidden" name="picd_cantiverif[]" id="picd_cantverif" value="'.$picking_detalle[$i]["picd_cantverif"].'">';
            $html .= '<input type="hidden" name="picking-ref" id="picking-ref" value="'.$picking_detalle[$i]["referencia_pro"].'">';
            $html .= '<input type="hidden" class="codigobarra_clase" name="codigobarra_pro[]" id="codigobarra_pro" value="'.$picking_detalle[$i]["codigobarra_pro"].'">';
            $html .= '</div>';
            $html .= '</div>';
        endfor;
        $ofoto = $picking_detalle = $i = null;
        unset($ofoto, $picking_detalle, $i);
        return $html;
    }

    /*************************************************************************
    *                          GET ANCLADOS
    * Trae los productos que actualmente se encuentre anclados a tareas
    * de picking y packing. Esto se llama para mostrar a los preparadores la 
    * disponibilidad de un producto cuando están haciendo sus tareas de 
    * picking
    *************************************************************************/
    function get_anclados($idproducto) {
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $anclados   = null;

        $sql = "SELECT IFNULL(SUM(tabla1.anclado),0) AS anclado
        FROM (SELECT IFNULL(SUM(a.picd_cantidad),0) AS anclado 
                FROM tpicking_detalle AS a
            WHERE a.picd_idproducto = :idpproducto
            AND exists(SELECT z.pick_idpicking
                            FROM tpicking z where z.pick_status in (1,2,3) 
                            AND z.pick_idempresa = a.picd_idempresa 
                            AND z.pick_idpicking = a.picd_idpicking) 
        GROUP BY a.picd_idproducto 
        UNION
        SELECT IFNULL(SUM(b.pacd_requerido),0)
        FROM tpacking_detalle b
        WHERE b.pacd_idproducto = :idpproducto2
        AND EXISTS (SELECT w.pack_idpacking
        FROM tpacking w 
                where w.pack_status in (1,2) 
        AND w.pack_idempresa = b.pacd_idempresa 
            AND w.pack_idpacking = b.pacd_idpacking)
        ) tabla1";

        $stmt = $db_maria->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idpproducto',$idproducto, PDO::PARAM_STR);
        $stmt->bindparam(':idpproducto2',$idproducto, PDO::PARAM_STR);
        $stmt->bindColumn('anclado', $anclados);
        $stmt->execute();
        if ($stmt->rowcount() == 1){
            $stmt->fetch(PDO::FETCH_BOUND);
        }else{
            $anclados = 0;
        }
        $data_maria = $db_maria = null;
        $sql = $stmt = null;
        unset($data_maria, $db_maria, $sql, $stmt);
        return ($anclados);
    }


    /**************************************************************************
    *                         GET PENDIENTES
    * Busca el producto en aquellos pedidos que se encuentren con el estatus
    * de "Pendiente" (esperando por facturar). Este datos es super necesario
    * para calcular la disponibilidad de un producto. Para calcular la 
    * disponibilidad de un producto es necesario usarlo junto ocn GET_ANCLADOS
    ***************************************************************************/
    function get_pendientes($idproducto){
        $data_sqlsrv = new Db_sqlsrv();  // Nueva conexión a SQL Server
        $db_sqlsrv   = $data_sqlsrv->getConnection();
        $pendientes  = null;
        $sql = "SELECT ISNULL(SUM(a.cantidad_ped),0) AS pendientes
        FROM tbpedidos2 a
        WHERE a.producto_ped = :idproducto
        AND EXISTS (SELECT z.numero_ped 
                      FROM tbpedidos1 z
                     WHERE z.status_ped = 'PENDIENTE'
                       AND z.numero_ped = a.numero_ped)";

        $stmt = $db_sqlsrv->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idproducto',$idproducto, PDO::PARAM_STR);
        $stmt->bindColumn('pendientes', $pendientes);

        $stmt->execute();
        if ($stmt->rowcount() == 1){
            $stmt->fetch(PDO::FETCH_BOUND);
        }else{
            $pendientes = 0;
        }
        $data_sqlsrv = $db_sqlsrv = null;
        $sql = $stmt = null;
        unset($data_sqlsrv, $db_sqlsrv, $sql, $stmt);
        return ($pendientes);
    }


    /*************************************************************************
    *                    ACTUALIZA PICKING PRODUCTO
    * Actualiza la cantidad anclada de un producto en la Tarea de Picking
    * especificada.
    *************************************************************************/
    function actualiza_picking_producto($idpicking,$idproducto,$requerido,$cantidad,$ubicacion){
        $data_maria               = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                 = $data_maria->getConnection();
        $opicking                 = new Picking($db_maria);
        $opicking->pick_idpicking = $idpicking;
        $opicking->user_mod       = $_SESSION['username'];
        $opicking->update_picking_producto($idproducto, $cantidad,$ubicacion);

        $out=[];
        if (!$opicking->error){
            $out["response"]  = "success";
            $out["texto"]     = "datos guardados con exito.";
            $out["idpicking"] = $opicking->pick_idpicking;
        }else{
            $out["response"] = "fail";
            if ($opicking->error_nro == '45000'){
                $out["error_nro"]  = $opicking->error_nro;
                $out["error_msj"]  = 'No se actualizó la cantidad en la Tarea de Picking '.$opicking->pick_idpicking.' para el producto '.$idproducto;
                $out["error_tpo"]  = 'error';
            }elseif ($opicking->error_nro == '23000'){
                $out["error_nro"]  = $opicking->error_nro;
                $out["error_msj"]  = 'Error en la cantidad ingresada';
                $out["error_tpo"]  = 'error';
            }else{
                $out["error_nro"]  = $opicking->error_nro;
                $out["error_msj"]  = $opicking->error_msj;
                $out["error_file"] = $opicking->error_file;
                $out["error_line"] = $opicking->error_line;
                $out["error_tpo"]  = 'error';
            };
        }

        // Si se actualizaron los datos exitosamente entonces tomo la clase correspondiente
        // de css del semaforo.
        if ($out["response"] == "success"){
            $out["semaforo"] = picking_semaforo($requerido,$cantidad);
        }
        $data_maria = $db_maria = null;
        $opicking = null;
        unset($data_maria, $db_maria, $opicking);
        return $out;
    }

    /*************************************************************************
    *                 ACTUALIZA PICKING VERIFICA
    * Actualiza la cantidad verificada de un producto en la Tarea de Picking
    * cargada en la clase
    *************************************************************************/
    function actualiza_picking_verifica($idpicking,$idproducto,$anclado,$cantverif){
        $data_maria                = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                  = $data_maria->getConnection();
        $opicking                  = new Picking($db_maria);
        $opicking->pick_idempresa  = 1;
        $opicking->pick_idpicking  = $idpicking;
        $opicking->user_mod        = $_SESSION['username'];

        $opicking->conn->beginTransaction();
        $opicking->update_picking_verifica($idproducto, $cantverif);

        $out=[];
        if (!$opicking->error){
            $opicking->conn->commit();
            $out["response"]       = "success";
            $out["texto"]          = "datos guardados con exito.";
            $out["idpicking"] = $opicking->pick_idpicking;
            $out["semaforo"] = picking_semaforo($anclado,$cantverif);
        }else{
            $opicking->conn->rollback();
            $out["response"] = "fail";
            if ($opicking->error_nro == '45000'){
                $out["error_nro"]  = $opicking->error_nro;
                $out["error_msj"]  = $opicking->error_msj; //'No se actualizó la cantidad en la Tarea de Picking '.$opicking->pick_idpicking.' para el producto '.$idproducto;
                $out["error_file"] = $opicking->error_file;
                $out["error_line"] = $opicking->error_line;
                $out["error_tpo"]  = 'error';
            }elseif ($opicking->error_nro == '23000'){
                $out["error_nro"]  = $opicking->error_nro;
                $out["error_msj"]  = 'Error en la cantidad ingresada';
                $out["error_tpo"]  = 'error';
            }else{
                $out["error_nro"]  = $opicking->error_nro;
                $out["error_msj"]  = $opicking->error_msj;
                $out["error_file"] = $opicking->error_file;
                $out["error_line"] = $opicking->error_line;
                $out["error_tpo"]  = 'error';
            };
        }
        
        $data_maria = $db_maria = null;
        $opicking = null;
        unset($data_maria, $db_maria, $opicking);
        return $out;
    }

    /*************************************************************************
    *                 CONSOLIDA TAREA DE PICKING
    * Esta acción se genera cuando el preparador termina de consolidar todos
    * los productos para que estos sean luegos verificados.
    *************************************************************************/
    function consolida_tarea_picking(){
        $data_maria                 = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                   = $data_maria->getConnection();
        $opicking                   = new Picking($db_maria);
        $opicking->pick_idpicking   = test_input($_POST["idpicking"]);
        $opicking->carga_picking_master();
        $opicking->pick_observacion = test_input($_POST["observacion"]);
        $opicking->pick_pista       = test_input($_POST["pista"]);
        $opicking->user_mod         = $_SESSION['username'];
        $opicking->picking_bins     = array();

        for ($i=0; $i<count($_POST["picc_bin"]); $i++):
            if (!empty($_POST["picc_bin"][$i])){
                $opicking->picking_bins[] = array(
                    'picc_idempresa' => 1,
                    'picc_idpicking' => $opicking->pick_idpicking,
                    'picc_bin'       => test_input($_POST["picc_bin"][$i]),
                    'user_crea'      => $_SESSION["username"]
                );
            }
        endfor;

        if ($opicking->pick_pista < 1 || $opicking->pick_pista > 7){
            $opicking->error      = true;
            $opicking->error_nro  = 25001;
            $opicking->error_msj  = 'Pista no existe para ubicar productos consolidados.';
            $opicking->error_file = 'ap_picking.php';
            $opicking->error_line = '';
        }

        if (!$opicking->error){
            if (count($opicking->picking_bins)> 0){
                $opicking->consolida_tarea_picking();
            }else{
                $opicking->error      = true;
                $opicking->error_nro  = 25000;
                $opicking->error_msj  = 'No se ha indicado ninguna paleta o contenedor';
                $opicking->error_file = 'ap_picking.php';
                $opicking->error_line = '';
            }
        }
        
        $out=[];
        if (!$opicking->error){
            $out["response"]       = "success";
            $out["texto"]          = "datos guardados con exito.";
            $out["pick_idpicking"] = $opicking->pick_idpicking;
        }else{
            $out["response"]   = "fail";
            $out["error_nro"]  = $opicking->error_nro;
            $out["error_msj"]  = $opicking->error_msj;
            $out["error_file"] = $opicking->error_file;
            $out["error_line"] = $opicking->error_line;
            $out["error_tpo"]  = 'error';
        }

        $_POST["idpicking"] = $_POST["observacion"] = $_POST["pista"] = $_POST["picc_bin"] = null;
        $data_maria = $db_maria = $opicking = null; // destruimos objeto
        unset($_POST["idpicking"], $_POST["observacion"], $_POST["pista"], $_POST["picc_bin"]);
        unset($data_maria, $db_maria, $opicking); // destruimos objeto
        return $out;
    }


    /*************************************************************************
    *                      CIERRA TAREA DE PICKING
    * Se cierra la tarea de picking y se cambia el status del Pedido.
    * A partir de este momento no se debe permitir modificaciones a la tarea
    * de picking, esto está validado a nivel de base de datos.
    *************************************************************************/
    function cierra_tarea_picking(){
        /*Cargamos los datos de la tarea de Picking Ya que necesitamos
        el ID del Pedido. No se trae como parámetro para hacer el programa
        más robusto a prueba de errores */
        $data_maria                 = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                   = $data_maria->getConnection();
        $opicking                   = new Picking($db_maria);
        $opicking->pick_idpicking   = test_input($_POST["pick_idpicking"]);
        $opicking->carga_picking_master();
        $opicking->carga_picking_detalle();
        $opicking->pick_observacion = test_input($_POST["pick_observacion"]);
        $opicking->pick_userverif   = $_SESSION['username'];
        $opicking->user_mod         = $_SESSION['username'];

        // Buscamos el Pedido
        $data_sqlsrv = new Db_sqlsrv(); // Nueva conexión a SQL Server
        $db_sqlsrv   = $data_sqlsrv->getConnection();
        $opedido     = new Pedido($db_sqlsrv);
        $opedido->numero_ped = $opicking->pick_idpedido;

        // Creamos objeto Packing
        require_once '../clases/clase_packing.php';
        $opacking = new Packing($db_maria);
        $opacking->pack_idempresa   = $opicking->pick_idempresa;
        $opacking->pack_idpicking   = $opicking->pick_idpicking ;
        $opacking->pack_idpedido    = $opicking->pick_idpedido;
        $opacking->pack_prioridad   = $opicking->pick_prioridad;
        $opacking->user_crea        = $_SESSION['username'];

        $opacking->packing_detalle  = array();
        // Insertamos lo requerido para embalar. Viene del Picking
        $j = 0;
        for ($i=0; $i<count($opicking->picking_detalle); $i++):
            if ($opicking->picking_detalle[$i]["picd_cantverif"] > 0){
                $opacking->packing_detalle[$j]['pacd_idproducto']   = $opicking->picking_detalle[$i]["picd_idproducto"];
                $opacking->packing_detalle[$j]['pacd_unidad']       = $opicking->picking_detalle[$i]["picd_unidad"];
                $opacking->packing_detalle[$j]['pacd_requerido']    = $opicking->picking_detalle[$i]["picd_cantverif"];
                $j++;
            }
        endfor;

        //comenzamos la transacción. Si al grabar en TbPedidos
        $db_maria->beginTransaction();
        $db_sqlsrv->beginTransaction();
        $opedido->update_pedidos_status('PACKING'); // Pasamos a packing

        if (!$opedido->error){
            $opicking->cierra_tarea_picking();
            if (!$opicking->error){
                $opacking->insert_packing_master();
                if (!$opacking->error){
                    $opacking->insert_packing_detalle();
                }
                
                if (!$opacking->error){
                    $db_sqlsrv->commit();
                    $db_maria->commit();
                    $out["response"]       = "success";
                    $out["texto"]          = "datos guardados con exito.";
                    $out["pick_idpicking"] = $opicking->pick_idpicking;
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
                $out["response"] = "fail";
                $out["error_nro"]  = $opicking->error_nro;
                $out["error_msj"]  = $opicking->error_msj;
                $out["error_file"] = $opicking->error_file;
                $out["error_line"] = $opicking->error_line;
                $out["error_tpo"]  = 'error';
            }
        }else{
            $db_sqlsrv->rollback();
            $out["response"] = "fail";
            $out["error_nro"]  = $opedido->error_nro;
            $out["error_msj"]  = $opedido->error_msj;
            $out["error_file"] = $opedido->error_file;
            $out["error_line"] = $opedido->error_line;
            $out["error_tpo"]  = 'error';
        }
        $_POST["idpicking"] = $_POST["observacion"] = $_POST["pista"] = $_POST["picc_bin"] = null;
        $data_sqlsrv = $db_sqlsrv = $data_maria = $db_maria = null; // destruimos objeto
        $opedido = $opicking = $opacking = null; // Liberamos memoria
        unset($data_sqlsrv, $db_sqlsrv, $data_maria, $db_maria, $opedido, $opicking, $opacking); // destruimos objeto
        unset($_POST["idpicking"], $_POST["observacion"], $_POST["pista"], $_POST["picc_bin"]);
        return $out;
    }

    /*************************************************************************
    *                        PICKING SEMAFORO
    * En las pantallas trabajamos con semáforos que indican el status de 
    * picking de un producto determinado.
    * El valor retornado son clases CSS que colocan el color al semaforo.
    *************************************************************************/
    function picking_semaforo($requerido,$cantidad){
        $clases = 'semaforo ';
        if (is_null($cantidad)){
            $clases .= "semaforo-blanco";
        }elseif ($requerido == $cantidad){
            $clases .= "semaforo-verde";
        }elseif ($cantidad == 0){
            $clases .= "semaforo-rojo";
        }elseif ($requerido <> $cantidad){
            $clases .= "semaforo-amarillo";
        }            
        $requerido = $cantidad = null;
        unset($requerido, $cantidad);
        return $clases;
    }
?>