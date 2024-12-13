<?php
    require '../user-auth-frontend.php';
    require_once '../config/Database_mariadb.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Lefre WMS Beta 1</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, minimum-scale=1">
    <link rel="icon" type="image/jpg" sizes="16x16" href="../img/favicon/favicon-16x16.jpg">
    <link rel="icon" type="image/jpg" sizes="32x32" href="../img/favicon/favicon-32x32.jpg">
    <link rel="icon" type="image/jpg" sizes="96x96" href="../img/favicon/favicon-96x96.jpg">
    <!-- Normalize       ----------------------------------------------->
    <!-- <link rel="stylesheet" type="text/css" href="css/normalize.min.css"> -->
    <!-- Boostrap CSS    ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
    <!-- Iconos Iconmoon      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="../pluggins/icomoon-v1.0/style.css">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="../css/header_barra.css">
    <link rel="stylesheet" type="text/css" href="../css/wms.css">
    <!-- Bootstrap 5.0  ------------------------------------>
    <script type="text/javascript" src="../js/bootstrap.bundle.min.js"></script>
    <meta http-equiv="Cache-Control" content="no-store">
    <style>
        .tabla-container{
            width: 100%;
            max-width: 400px;
        }

        table td:nth-child(1){
            width: 100px;
        }

        table td:nth-child(2),td:nth-child(3){
            width: 100px;
        }


        .datos-line td:nth-child(2),td:nth-child(3){
            text-align: right;
        }
    </style>
</head>
<body>
<?php
    require '../config/barra.html';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div><h3 class='titulos_categorias'>Estadísticas Almacén</h3></div>
        <div id="consulta" >
            <div class="tabla-container">
                <div style="background-color:gainsboro;font-weight:600;">Preparadores</div>
                <table id="preparadores-tabla" class="table table-striped resumen-tareas">
                    <thead>
                        <tr>
                            <th>Persona</th>
                            <th style="text-align:right;">Tareas</th>
                            <th style="text-align:right;">SKU</th>
                        </tr>
                    </thead>
                    <tbody id="preparadores-body">
                    </tbody>
                </table>
            </div>
            <div class="tabla-container">
                <div style="background-color:gainsboro;font-weight:600;">Embaladores</div>
                <table id="embaladores-tabla" class="table table-striped resumen-tareas">
                    <thead>
                        <tr>
                            <th>Persona</th>
                            <th style="text-align:right;">Tareas</th>
                            <th style="text-align:right;">SKU</th>
                        </tr>
                    </thead>
                    <tbody id="embaladores-body">
                    </tbody>
                </table>
            </div>
            <div class="tabla-container">
                <div style="background-color:gainsboro;font-weight:600;">Chequeadores</div>
                <table id="chequeadores-tabla" class="table table-striped resumen-tareas">
                    <thead>
                        <tr>
                            <th>Persona</th>
                            <th style="text-align:right;">Tareas</th>
                            <th style="text-align:right;">SKU</th>
                        </tr>
                    </thead>
                    <tbody id="chequeadores-body">
                    </tbody>
                </table>
            </div>
        </div>
    </main>
<?php
    estadisticas('preparadores');
    require '../config/footer.html';
?>

<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/rep_torrectrl.php");
    }

    document.getElementById('preparadores-body').innerHTML = "<?php echo estadisticas("preparadores");?>";
    document.getElementById('embaladores-body').innerHTML = "<?php echo estadisticas("embaladores");?>";
    document.getElementById('chequeadores-body').innerHTML = "<?php echo estadisticas("chequeadores");?>";
    
    carga_estadisticas_almacen();

    /**************************************************************************
    * carga_estadisticas_almacen
    ***************************************************************************/
    function carga_estadisticas_almacen(){
        spinner.removeAttribute('hidden');
        document.getElementById('preparadores-body').innerHTML = "<?php echo estadisticas("preparadores");?>";
        document.getElementById('embaladores-body').innerHTML = "<?php echo estadisticas("embaladores");?>";
        document.getElementById('chequeadores-body').innerHTML = "<?php echo estadisticas("chequeadores");?>";
        spinner.setAttribute('hidden', '');
    };
</script>

<?php 
    function estadisticas($tipo){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $desde = strtotime($_GET['desde']. ' 00:00:00');
        $hasta = strtotime($_GET['hasta']. ' 23:59:59');
        $desde = date('Y-m-d H:i:s',$desde);
        $hasta = date('Y-m-d H:i:s',$hasta);
        if ($tipo === 'preparadores'){
            $sql = "SELECT a.pick_preparador as persona, COUNT(a.pick_idpicking) as tareas, tabla1.SKU
                      FROM tpicking a,
                        (SELECT b.pick_preparador, COUNT(a.picd_idproducto) AS SKU
                           FROM tpicking_detalle a
                          INNER JOIN tpicking b ON a.picd_idempresa = b.pick_idempresa AND a.picd_idpicking = b.pick_idpicking AND b.pick_fecierre BETWEEN :desde AND :hasta
                          GROUP BY b.pick_preparador) tabla1
                    WHERE pick_fecierre BETWEEN :desde2 AND :hasta2
                    AND pick_status IN (3,5)
                    AND a.pick_preparador = tabla1.pick_preparador
                    GROUP BY a.pick_preparador";

        } elseif ($tipo === 'embaladores') {
            $sql = "SELECT a.pack_embalador as persona, COUNT(a.pack_idpacking) as tareas, tabla1.SKU
                      FROM tpacking a,
                        (SELECT b.pack_embalador, COUNT(a.pacd_idproducto) AS SKU
	                       FROM tpacking_detalle a
	                      INNER JOIN tpacking b ON a.pacd_idempresa = b.pack_idempresa AND a.pacd_idpacking = b.pack_idpacking AND b.pack_fecierre BETWEEN :desde AND :hasta
	                      GROUP BY b.pack_embalador) tabla1
                     WHERE pack_fecierre BETWEEN :desde2 AND :hasta2
                       AND pack_status = 5
                       AND a.pack_embalador = tabla1.pack_embalador
                     GROUP BY a.pack_embalador";
        } elseif ($tipo === 'chequeadores') {
            $sql = "SELECT a.user_crea as persona, COUNT(a.pack_idpacking) as tareas, tabla1.SKU
                      FROM tpacking a,
                        (SELECT b.user_crea, COUNT(a.pacd_idproducto) AS SKU
	                       FROM tpacking_detalle a
	                      INNER JOIN tpacking b ON a.pacd_idempresa = b.pack_idempresa AND a.pacd_idpacking = b.pack_idpacking AND b.pack_fecierre BETWEEN :desde AND :hasta
	                      GROUP BY b.user_crea) tabla1
                     WHERE pack_fecierre BETWEEN :desde2 AND :hasta2
                       AND pack_status = 5
                       AND a.user_crea = tabla1.user_crea
                     GROUP BY a.user_crea";
        }
        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt -> bindparam(':desde', $desde, PDO::PARAM_INT);
        $stmt -> bindparam(':hasta', $hasta, PDO::PARAM_STR);
        $stmt -> bindparam(':desde2', $desde, PDO::PARAM_INT);
        $stmt -> bindparam(':hasta2', $hasta, PDO::PARAM_STR);
        $stmt -> execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        $html = '';
        $sum_task = 0;
        $sum_sku  = 0;
        foreach($resp as $fila):
            $html .= "<tr class='datos-line'>";
            $html .= '<td>'.$fila["persona"].'</td>';
            $html .= "<td>".$fila["tareas"]."</td>";
            $html .= "<td>".$fila["SKU"]."</td>";
            $sum_task += $fila["tareas"];
            $sum_sku  += $fila["SKU"];
            $html .= '</tr>';
        endforeach;
        $html .= "<tr class='datos-line'>";
        $html .= "<td style='text-align:right;'>total</td>";
        $html .= "<td>".$sum_task."</td>";
        $html .= "<td>".$sum_sku."</td>";
        $html .= '</tr>';
        return $html;
    }
?>