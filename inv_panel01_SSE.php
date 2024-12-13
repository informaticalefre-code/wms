<?php
    header("Content-Type:text/event-stream");
    header("Cache-Control:no-control");

    require_once 'api/user-auth-api.php';
    require_once 'config/Database_mariadb.php';

    $productos  = get_datos_productos($_GET["id"]);
    $contadores = get_datos_contadores($_GET["id"]);
    $out = [];
    $out["productos"] = $productos;
    $out["contadores"] = $contadores;
    
    $total_pedidos = json_encode($out);
    echo "data:".$total_pedidos." \n\n";
    flush();


    function get_datos_productos($idinventario){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();

        $sql = "SELECT COUNT(*) AS sku_total, 
        SUM(CASE WHEN length(trim(invd_ubicacion))=0 THEN 1 END) AS sku_sin_ubicacion,
        SUM(CASE WHEN length(trim(invd_ubicacion))>0 THEN 1 END) AS sku_con_ubicacion,
        SUM(CASE WHEN invd_diferencia IS NOT NULL OR invd_conteo3 IS NOT NULL THEN 1 END) AS contados_total,
        NVL(SUM(CASE WHEN length(trim(invd_ubicacion))=0 AND (invd_diferencia IS NOT NULL OR invd_conteo3 IS NOT NULL) THEN 1 END),0) AS contados_sin_ubicacion,
        SUM(CASE WHEN length(trim(invd_ubicacion))>0 AND (invd_diferencia IS NOT NULL OR invd_conteo3 IS NOT NULL) THEN 1 END) AS contados_con_ubicacion
        FROM tinventarios_detalle WHERE invd_id = :idinventario";

        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt -> bindparam(':idinventario', $idinventario, PDO::PARAM_INT);

        $stmt->execute();
        $resp = $stmt->fetch(PDO::FETCH_ASSOC);

        $data_maria = $db_maria = $sql = $stmt = $idinventario = null;
        unset($data_maria, $db_maria, $sql, $stmt, $idinventario);
        return $resp;
    }

    
    function get_datos_contadores($idinventario){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();

        $sql = "SELECT invd_username as username, COUNT(*) AS sku, SUM(CASE WHEN invd_conteo IS NOT NULL THEN 1 END) AS contados
                FROM vinventarios_conteo
                WHERE invd_id = :idinventario
                AND invd_username IS NOT NULL
                GROUP BY invd_username";

        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt -> bindparam(':idinventario', $idinventario, PDO::PARAM_INT);

        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);

        $data_maria = $db_maria = $sql = $stmt = $idinventario = null;
        unset($data_maria, $db_maria, $sql, $stmt, $idinventario);
        return $resp;
    }

?>