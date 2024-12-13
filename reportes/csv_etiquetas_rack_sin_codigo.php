<?php
    require '../user-auth-frontend.php';
    require_once '../config/Database_sqlsrv.php';

    $data_sqlsrv = new Db_sqlsrv();  // Nueva conexión a SQL Server
    $db_sqlsrv   = $data_sqlsrv->getConnection();

    $sql  = "SELECT a.codigo_pro, a.nombre_pro, a.unidad_pro, a.ubicacion_pro, a.codigobarra_pro, a.existencia_pro
     FROM TbProductos a
     WHERE a.inactivo_pro = 0
     AND a.codigo_pro not like 'ZZ%'
     AND a.codigo_pro not like '999%'
     AND a.codigo_pro not like 'ADM%'
     AND a.codigo_pro NOT IN ('9700-0000')
     AND (a.ubicacion_pro = '' OR a.codigobarra_pro = '')
     ORDER BY a.codigo_pro";
    $stmt = $db_sqlsrv->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
    $stmt->execute();
    $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
    $fileCSV = uniqid(rand()).'.csv';
    $data_sqlsrv = null;
    $db_sqlsrv   = null;
    $sql = $stmt = null;
    unset($data_sqlsrv, $db_sqlsrv, $sql, $stmt);
    if (count($resp)>0){
        $fp = fopen('../tmp/'.$fileCSV, 'w+');
        foreach ($resp as $linea) {
            fputcsv($fp, array_values($linea),";");
        }
        rewind($fp);

        // Set headers to download file rather than displayed 
        header('Content-Type: text/csv'); 
        header('Content-Disposition: attachment; filename="' . $fileCSV . '";'); 
        header("Content-Transfer-Encoding: UTF-8");
         
        //output all remaining data on a file pointer 
        fpassthru($fp); 
        fclose($fp);
        unlink('../tmp/'.$fileCSV);
        $fp = $fileCSV = null;
        unset($fp,$fileCSV);
    }
?>