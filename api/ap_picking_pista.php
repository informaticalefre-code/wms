<?php
    require_once 'user-auth-api.php';
    require_once '../config/Database_mariadb.php';
    require_once '../config/funciones.php';
    require_once '../clases/clase_picking_pista.php';

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: text/html; charset=UTF-8");
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");
   
    $metodo = $_SERVER["REQUEST_METHOD"];
    if ($metodo=='GET'){
        if (isset($_GET["idvendedor"])){
            $id_vendedor = test_input($_GET["idvendedor"]);
            $out = get_vendedor_pista($id_vendedor);
            unset($id_vendedor,$_GET["idvendedor"]);
            http_response_code(200);
            echo $out;
        }elseif (isset($_GET["pista"])){
            $out["html"] = carga_picking_pista(test_input($_GET["pista"]));
            unset($_GET["pista"]);
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
    }


    /*************************************************************************
    *                          GET PISTA
    * Devuelve el nro. de pista en la playa donde deben colocarse todos los 
    * pedidos correspondientes al vendedor indicado.
    *************************************************************************/
    function get_vendedor_pista($id_vendedor) {
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $pista      = 0;
        $sql  = "select picp_pista FROM vpicking_pistas WHERE picp_idvendedor = :id_vendedor";
        $stmt = $db_maria->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':id_vendedor',$id_vendedor, PDO::PARAM_STR);
        $stmt->bindColumn(1, $pista);

        $stmt->execute();
        if ($stmt->rowcount() == 1){
            $stmt->fetch(PDO::FETCH_BOUND);
        }else{
            $pista = 0;
        }
        $data_maria = $db_maria = null;
        $sql = $stmt = $id_vendedor = null;
        unset($data_maria,$db_maria,$sql,$stmt);
        return ($pista);
    }


    /*************************************************************************
    *                 CARGA PICKING PISTA
    *************************************************************************/
    function carga_picking_pista($pista){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $opista     = new Picking_Pista($db_maria);
        $opista->pista = $pista;
        $opista->carga_picking_pista();
        $out = $opista->html_picking_pista();

        $data_maria = $db_maria = null;
        $pista = $opista = null;
        unset($data_maria, $db_maria, $pista, $opista);
        return $out;
    }
?>