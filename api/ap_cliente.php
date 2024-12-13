<?php
    require_once 'user-auth-api.php';
    require_once '../clases/clase_cliente.php';
    require_once '../config/Database_sqlsrv.php';
    require_once '../config/funciones.php';

    header("Access-Control-Allow-Origin: *");
    // header('Content-Type: application/json; charset=charset=iso-8859-1'); 
    header('Content-Type: application/json; charset=UTF-8'); 
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");
    $metodo = $_SERVER["REQUEST_METHOD"];
    if ($metodo=='GET'){
        if (isset($_GET["accion"]) && $_GET["accion"]=='deuda_detalle'){
            $idcliente  = test_input($_GET["idcliente"]);
            $out        = carga_facturas_pendientes($idcliente);
            http_response_code(200);
            unset($idcliente,$_GET["idcliente"],$_GET["accion"]);
            echo json_encode($out);
        }elseif (isset($_GET["accion"]) && $_GET["accion"]=='meses_anteriores'){
            $out=[];
            $idcliente  = test_input($_GET["idcliente"]);
            $out = carga_meses_anteriores($idcliente);
            http_response_code(200);
            unset($idcliente,$_GET["idcliente"],$_GET["accion"]);
            echo json_encode($out);
        }elseif (isset($_GET["accion"]) && $_GET["accion"]=='factura_uno'){
            $out=[];
            $idcliente  = test_input($_GET["idcliente"]);
            $out = carga_factura_uno($idcliente);
            http_response_code(200);
            unset($idcliente,$_GET["idcliente"],$_GET["accion"]);
            echo json_encode($out);
        }elseif (isset($_GET["accion"]) && $_GET["accion"]=='cobros_sin_procesar'){
            $out=[];
            $idcliente  = test_input($_GET["idcliente"]);
            $out = carga_cobros_noprocesados($idcliente);
            http_response_code(200);
            unset($idcliente,$_GET["idcliente"],$_GET["accion"]);
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

    /*************************************************************************************
    *                           CARGA FACTURAS PENDIENTES
    * Carga las facturas pendientes de un cliente y genera un html lista para mostrarlas
    **************************************************************************************/
    function carga_facturas_pendientes($idcliente){
        // Buscamos el Pedido
        $data_sqlsrv         = new Db_sqlsrv(); // Nueva conexión a SQL Server
        $db_sqlsrv           = $data_sqlsrv->getConnection();
        $ocliente            = new Cliente($db_sqlsrv);
        $ocliente->idcliente = $idcliente;
        $ocliente->carga_cliente_deuda(); 
        $html_deuda = '';
        if (!$ocliente->error) {
            $html_deuda = html_facturas_list($ocliente->deuda_detalle,$ocliente->deuda_total);   
        }

        if (!$ocliente->error){
            $out = array();
            $out["response"]    = 'success';
            $out["html_deuda"]  = $html_deuda;
        }else{
            $out["response"]    = 'fail';
            $out["html_deuda"]  = '<div class="alert alert-danger" role="alert">';
            $out["html_deuda"] .= 'Error Nro.:'.$ocliente->error_nro.'<br>';
            $out["html_deuda"] .= 'Msj:'.$ocliente->error_msj.'<br>';
            $out["html_deuda"] .= 'File:'.$ocliente->error_file.'<br>';
            $out["html_deuda"] .= 'Line:'.$ocliente->error_line.'<br>';
            $out["html_deuda"] .= 'Notifique a Soporte Técnico';
            $out["html_deuda"] .= '</div>';
        }
        $ocliente = null;
        unset($ocliente, $html_deuda);
        $data_sqlsrv = $db_sqlsrv = null;
        unset($data_sqlsrv, $db_sqlsrv);
        return ($out);
    }


    /**************************************************************
    *                 CARGA MESES ANTERIORES
    * Carga lo facturado de los últimos 6 meses y lo pagado 
    * de cada mes.
    **************************************************************/
    function carga_meses_anteriores($idcliente){
        // Buscamos el Pedido
        $data_sqlsrv         = new Db_sqlsrv(); // Nueva conexión a SQL Server
        $db_sqlsrv           = $data_sqlsrv->getConnection();
        $ocliente            = new Cliente($db_sqlsrv);
        $ocliente->idcliente = $idcliente;
        $ocliente->carga_meses_anteriores(); 
        $html_meses = '';
        if (!$ocliente->error) {
            $html_meses = html_facturas_meses($ocliente->meses_anteriores);
        }

        if (!$ocliente->error){
            $out = array();
            $out["response"]    = 'success';
            $out["html_meses"]  = $html_meses;
            $out["fact_avg"]    = $ocliente->promedio_fac;
        }else{
            $out["response"]    = 'fail';
            $out["html_meses"]  = '<div class="alert alert-danger" role="alert">';
            $out["html_meses"] .= 'Error Nro.:'.$ocliente->error_nro.'<br>';
            $out["html_meses"] .= 'Msj:'.$ocliente->error_msj.'<br>';
            $out["html_meses"] .= 'File:'.$ocliente->error_file.'<br>';
            $out["html_meses"] .= 'Line:'.$ocliente->error_line.'<br>';
            $out["html_meses"] .= 'Notifique a Soporte Técnico';
            $out["html_meses"] .= '</div>';
        }
        $ocliente = null;
        $data_sqlsrv = $db_sqlsrv = null;
        unset($data_sqlsrv, $db_sqlsrv, $ocliente, $html_meses);
        return ($out);
    }
    

    /*************************************************************************
    *                        HTML FACTURAS LIST
    * Esta función genera el html necesario para generar la lista de productos
    * de una tarea de picking
    *************************************************************************/
    function html_facturas_list($detalle_deuda,$total_deuda){
        $html  = '';
        if ($total_deuda > 0):
            $html .= '<table class="table table-striped table-hover table-bordered table-sm">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th style="text-align:center;" scope="col">Documento</th>';
            $html .= '<th style="text-align:center;" scope="col">Fecha</th>';
            $html .= '<th style="text-align:center;" scope="col">Recibido</th>';
            $html .= '<th style="text-align:center;" scope="col">Días</th>';
            $html .= '<th scope="col">Monto</th>';
            $html .= '<th scope="col">Pagado</th>';
            $html .= '<th scope="col">Saldo</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            for ($i=0; $i<count($detalle_deuda); $i++):
                $html .= '<tr>';
                $html .= '<td scope="row">'.$detalle_deuda[$i]["factura"].'</td>';
                $html .= '<td style="text-align:center;">'.$detalle_deuda[$i]["fecha_fac"].'</td>';
                if ($detalle_deuda[$i]["recibido_fac"] == '01/01/1900'){
                    $html .= '<td></td>';
                }else{
                    $html .= '<td style="text-align:center;">'.$detalle_deuda[$i]["recibido_fac"].'</td>';
                }
                $html .= '<td style="text-align:center;">'.number_format($detalle_deuda[$i]["dias"],0).'</td>';
                $html .= '<td style="text-align:right;">'.number_format($detalle_deuda[$i]["monto_fac"],2).'</td>';
                $html .= '<td style="text-align:right;">'.number_format($detalle_deuda[$i]["pagado_fac"],2).'</td>';
                $html .= '<td style="text-align:right;">'.number_format($detalle_deuda[$i]["saldo_fac"],2).'</td>';
                $html .= '</tr>';
            endfor;
            // $html .= '</tbody>';
            $html .= '<tr>';
            $html .= '<td colspan="4">Total</td>';
            $html .= '<td style="text-align:right;">'.number_format($total_deuda,2).'</td>';
            $html .= '</table>';
        else:
            $html .= '<div class="alert alert-success" role="alert">';
            $html .= 'El cliente No Posee documentos pendientes por Cancelar';
            $html .= '</div>';
        endif;        
        $detalle_deuda = null;    
        unset($detalle_deuda, $i,$total_deuda);
        return $html;
    }

    /*************************************************************************
    *                        HTML FACTURAS MESES
    * Llamado por la función carga_meses_anteriores().
    *************************************************************************/
    function html_facturas_meses($meses_deuda){
        $html  = '';
        if (count($meses_deuda) > 0):
            $html .= '<table class="table table-striped table-hover table-bordered table-sm">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th style="text-align:center;" scope="col">Año</th>';
            $html .= '<th style="text-align:center;" scope="col">Mes</th>';
            $html .= '<th style="text-align:center;" scope="col">Facturado</th>';
            $html .= '<th style="text-align:center;" scope="col">Pagado</th>';
            $html .= '<th style="text-align:center;" scope="col">Saldo</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            for ($i=0; $i<count($meses_deuda); $i++):
                $html .= '<tr>';
                $html .= '<td scope="row">'.$meses_deuda[$i]["ano"].'</td>';
                $html .= '<td>'.$meses_deuda[$i]["mes"].'</td>';
                $html .= '<td style="text-align:right;">'.number_format($meses_deuda[$i]["facturado"],2).'</td>';
                $html .= '<td style="text-align:right;">'.number_format($meses_deuda[$i]["pagado"],2).'</td>';
                $html .= '<td style="text-align:right;">'.number_format($meses_deuda[$i]["saldo"],2).'</td>';
                $html .= '</tr>';
            endfor;
            $html .= '<tr>';
            $html .= '<td colspan="4" style="text-align: right;">Total</td>';
            $html .= '<td style="text-align:right;">'.number_format(array_sum(array_column($meses_deuda,'saldo')),2).'</td>';
            $html .= '</table>';
        else:
            $html .= '<div class="alert alert-success" role="alert">';
            $html .= 'El cliente No Posee facturas registradas';
            $html .= '</div>';
        endif;            
        unset($meses_deuda,$i,$total);
        return $html;
    }

    /*************************************************************************************
    *                           CARGA FACTURAS PENDIENTES
    * Retorna la fecha de la primera factura para determinar la fecha de creación del
    * cliente.
    **************************************************************************************/
    function carga_factura_uno($idcliente){
        // Buscamos el Pedido
        $data_sqlsrv         = new Db_sqlsrv(); // Nueva conexión a SQL Server
        $db_sqlsrv           = $data_sqlsrv->getConnection();

        $sql = "select CONVERT(varchar(10), min(a.fecha_fac),103) as factura_uno FROM Tbfacturacion3 a WHERE a.status_fac = 1 and cliente_fac = :idcliente";

        $stmt = $db_sqlsrv->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idcliente',$idcliente, PDO::PARAM_STR);
        $stmt->bindColumn(1, $factura_uno);
        $stmt->execute();
        $stmt->fetch(PDO::FETCH_BOUND);
        $out = [];
        $out["response"]    = 'success';
        $out["factura_uno"] = $factura_uno;
        $data_sqlsrv = $db_sqlsrv = null;        
        $stmt = null;
        unset($data_sqlsrv, $db_sqlsrv, $sql, $stmt, $factura_uno);
        return ($out);
    }

    /*************************************************************************************
    *                           CARGA COBROS NO PROCESADOS
    * Trae el monto total de los recibos que tiene el cliente pero que aún no han sido
    * procesados o confirmados por el Departamento de Cobranza.
    **************************************************************************************/
    function carga_cobros_noprocesados($idcliente){
        $data_sqlsrv = new Db_sqlsrv(); // Nueva conexión a SQL Server
        $db_sqlsrv   = $data_sqlsrv->getConnection();

        $sql = "SELECT ISNULL(SUM(a.pago_cob),0) FROM TbCobranza2 a WHERE EXISTS (SELECT z.numero_cob FROM TbCobranza1 z WHERE z.cliente_cob = :idcliente AND z.actualiza_cob = 0 AND z.anulado_cob = 0 AND a.numero_cob  = z.numero_cob)";

        $stmt = $db_sqlsrv->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idcliente',$idcliente, PDO::PARAM_STR);
        $stmt->bindColumn(1, $monto);
        $stmt->execute();
        $stmt->fetch(PDO::FETCH_BOUND);
        $out = [];
        $out["response"] = 'success';
        $out["monto"]    = ROUND($monto,2);
        $data_sqlsrv     = $db_sqlsrv = $stmt = null;
        unset($data_sqlsrv, $db_sqlsrv, $sql, $stmt, $monto);
        return ($out);
    }
?>