<?php
    require_once 'user-auth-api.php';
    include_once '../config/Database_mariadb.php';
    include_once '../clases/clase_picking.php';

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: text/html; charset=UTF-8");
    header("Access-Control-Max-Age: 300"); // 5 Minutos

    $preparador = $_SESSION['username'];
    $database = new Db_mariadb();  // Nueva conexión a SQL Server
    $db  = $database->getConnection();
    $sql = "SELECT a.pick_idpicking, date_format(a.pick_fecha,'%d/%m/%Y %H:%i') as pick_fecha, a.pick_idpedido, a.pick_prioridad,
    date_format(b.fecha_ped,'%d/%m/%Y') as fecha_ped, b.cliente_ped, b.vendedor_ped, b.status_ped,
    c.descripcion_cli as nombre_cli, d.nombre_ven
    FROM vpicking a
    INNER JOIN tbpedidos1 b ON a.pick_idpedido = b.numero_ped
    INNER JOIN TbClientes c ON b.cliente_ped = c.codigo_cli
    INNER JOIN TbVendedores d ON b.vendedor_ped = d.codigo_ven
    WHERE a.pick_status IN (1,2) AND a.pick_preparador = :preparador
    ORDER BY a.pick_prioridad DESC, a.pick_fecha";

    $stmt = $db->prepare($sql);
    $stmt->bindparam(':preparador',$preparador, PDO::PARAM_STR);
    $stmt->execute();    
    $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
    if ($stmt->rowcount() > 0):
        $opicking = new Picking($db);
        $out = '';
        foreach($resp as $fila):
            $opicking->pick_idpicking = $fila["pick_idpicking"];
            $opicking->pick_fecha     = $fila["pick_fecha"];
            $opicking->pick_idpedido  = $fila["pick_idpedido"];
            $opicking->pick_prioridad = $fila["pick_prioridad"];
            $opicking->fecha_ped      = $fila["fecha_ped"];
            $opicking->cliente_ped    = $fila["cliente_ped"];
            $opicking->nombre_ven     = $fila["nombre_ven"];
            $opicking->nombre_cli     = $fila["nombre_cli"];
            $opicking->fecha_ped      = $fila["fecha_ped"];
            $out .= $opicking->html_picking_card();
        endforeach;

        $preparador = $database = $db = null ;
        $opicking = $sql = $stmt = $resp = null;
        unset($database, $db) ;
        unset($opicking, $stmt, $resp);
        http_response_code(200);
        echo $out;
        $out = null;
        unset($out);
    else:
        $out  = '<div class="alert alert-danger" role="alert">';
        $out .= 'No tienes tareas de Picking asignadas.'.'<br>';
        $out .= '</div>';
        http_response_code(404);
        echo $out;
        $out = null;
        unset($out);
    endif;


?>