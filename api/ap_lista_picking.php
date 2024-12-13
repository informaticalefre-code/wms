<?php
    require_once 'user-auth-api.php';
    require_once '../config/Database_mariadb.php';;
    include_once '../clases/clase_picking.php';
    require_once '../config/funciones.php';

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: text/html; charset=UTF-8");
    header("Access-Control-Max-Age: 300"); // 5 Minutos
    
    $data      = json_decode(file_get_contents("php://input"));
    $filtro    = "";
    if ($data->accion=="LISTA CULMINADOS"){
        $filtro .= "WHERE pick_status = 3";
    }


    $database = new Db_mariadb();  // Nueva conexión a SQL Server
    $db = $database->getConnection();
    $opicking = new Picking($db);
    $stmt = $opicking->lista_tareas_picking($filtro);
    $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
    if ($stmt->rowcount() > 0):
        $out = '';
        foreach($resp as $fila):
            $opicking->pick_idpicking = $fila["pick_idpicking"];
            $opicking->pick_fecha     = $fila["pick_fecha"];
            $opicking->pick_idpedido  = $fila["pick_idpedido"];
            $opicking->fecha_ped      = $fila["fecha_ped"];
            $opicking->cliente_ped    = $fila["cliente_ped"];
            $opicking->nombre_ven     = $fila["nombre_ven"];
            $opicking->nombre_cli     = $fila["nombre_cli"];
            $opicking->fecha_ped      = $fila["fecha_ped"];
            $out .= $opicking->html_picking_card();
        endforeach;
        http_response_code(200);
    else:
        $out  = '<div class="alert alert-danger" role="alert">';
        $out .= 'No hay tareas de Picking'.'<br>';
        $out .= '</div>';
        http_response_code(404);
    endif;    
    $filtro = $fila = $data = null;
    $database = $db = null;
    $opicking = $stmt = $resp = null;
    unset($database, $db);
    unset($opicking, $stmt, $resp, $filtro, $fila, $data);
    echo $out;
?>

