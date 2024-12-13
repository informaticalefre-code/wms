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
            max-width: 500px;
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
        <div><h3 class='titulos_categorias'>Pedidos Clasificados por Estatus</h3></div>
        <div id="consulta" >
            <div class="tabla-container">
                <div style="background-color:gainsboro;font-weight:600;">Reporte de Pedidos</div>
                <table id="pedidos-tabla" class="table table-striped resumen-tareas">
                    <thead>
                        <tr>
                            <th>Numero de pedido</th>
                            <th style="text-align:right;">fecha de pedido</th>
                            <th style="text-align:right;">estatus del pedido</th>
                        </tr>
                    </thead>
                    <tbody id="pedidos-body">
                    </tbody>
                </table>
            </div>
        </div>
    </main>
<?php
    pedidos('pedidos');
    require '../config/footer.html';
?>

<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/rep_torrectrl.php");
    }

    document.getElementById('pedidos-body').innerHTML = "<?php echo pedidos("pedidos");?>";
    
    carga_pedidos();

    /**************************************************************************
    * carga_pedidos
    ***************************************************************************/
    function carga_pedidos(){
        spinner.removeAttribute('hidden');
        document.getElementById('pedidos-body').innerHTML = "<?php echo pedidos("pedidos");?>";

        spinner.setAttribute('hidden', '');
    };
</script>

<?php 
    function pedidos(){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
       /* $desde = strtotime($_GET['desde']. ' 00:00:00');
        $hasta = strtotime($_GET['hasta']. ' 23:59:59');
        $desde = date('Y-m-d H:i:s',$desde);
        $hasta = date('Y-m-d H:i:s',$hasta);*/
        $sql = "SELECT 
            a.numero_ped as NUMERO, 
           a.fecha_ped AS FECHA, (
             CASE 
                 WHEN a.status_ped = '' 
                     THEN 'NUEVO' 
                     ELSE a.status_ped 
            END) AS ESTATUS 
        FROM tbpedidos1 a 
       /* WHERE a.fecha_ped BETWEEN :desde AND :hasta*/
            ORDER BY a.fecha_ped DESC";
        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
       /* $stmt -> bindparam(':desde', $desde, PDO::PARAM_INT);
        $stmt -> bindparam(':hasta', $hasta, PDO::PARAM_STR);*/
        $stmt -> execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        $html = '';
        foreach($resp as $fila):
            $html .= "<tr class='datos-line'>";
            $html .= '<td>'.$fila["NUMERO"].'</td>';
            $html .= "<td>".$fila["FECHA"]."</td>";
            $html .= "<td>".$fila["ESTATUS"]."</td>";
            $html .= '</tr>';
        endforeach;
        return $html;
    }
?>