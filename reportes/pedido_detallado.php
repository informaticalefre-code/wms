<?php
require_once '../user-auth.php';
require_once '../clases/clase_pedido.php';
require_once '../config/Database_sqlsrv.php';
require_once '../config/funciones.php';
require_once '../pluggins/mPDF/vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: text/html; charset=UTF-8");
// header('Content-Type: application/json'); 

// Tomamos los datos
$data_sqlsrv = new Db_sqlsrv();  // Nueva conexión a SQL Server
$db_sqlsrv   = $data_sqlsrv->getConnection();
$opedido = new Pedido($db_sqlsrv);
$opedido->numero_ped = test_input($_POST["idpedido"]);
$opedido->carga_datos();
if ($opedido->error){
    http_response_code(404);
    unset($data_sqlsrv, $db_sqlsrv);
    echo $opedido->error_msj;
}else{
    // Ahora vamos con el reporte
    array_multisort(array_column($opedido->pedido_detalle, "ubicacion_pro"), SORT_ASC, $opedido->pedido_detalle );
    $filePDF       = uniqid(rand()).'.pdf';
    $format_header = getheader($opedido);
    $format_footer = getfooter();
    $format_html   = gethtml($opedido);
    $format_css    = getcss();

    if (count($opedido->pedido_detalle) < 13) {
        $size = array(215,139); // Media carta
    }else{
        $size = array(215,279); // Tamaño carta
    }
    
    $mpdf = new \Mpdf\Mpdf(['mode'           => 'c', 
                        'format'         => $size,
                        'orientation'    => 'P',
                        'margin_top'     => 22,
                        'margin_bottom'  => 4,
                        'margin_left'    => 10,
                        'margin_right'   => 10,
                        'margin_header'  => 2,
                        'margin_footer'  => 4,
                        'default_font_size' => 8,
                        'default_font' => 'San Serif'
                    ]); // Media Carta

    $mpdf->SetHTMLHeader($format_header);
    $mpdf->SetFooter($format_footer);
    $mpdf->WriteHTML($format_css,\Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($format_html, \Mpdf\HTMLParserMode::HTML_BODY);

    $mpdf->Output('../tmp/'.$filePDF,\Mpdf\Output\Destination::FILE);
    
    $opedido = null;
    echo $filePDF;
}

function getheader($opedido){
    $plantilla = '<body>
    <main>
                <div id="pedido-header">
                    <table style="width:100%;">
                        <tr>
                            <td style="width:70%;">
                                <img id="logo-lefre" src="../img/svg/lefre-logo03.svg" style="width:150px;">
                                <p>Fecha '.$opedido->fecha_ped.'</p>
                            </td>
                            <td style="text-align: center;">
                                <barcode code="'.$opedido->numero_ped.'" type="C128A" class="barcode" size="1"/>
                                <div style="font-size:14pt;"><strong>'.$opedido->numero_ped.'</strong></div>
                            </td>
                        </tr>
                    </table>
                </div>
                <hr style="margin:0.5% 0%;padding:0%;">';
    return $plantilla;
    };


function gethtml($opedido){
    $plantilla = '<div class="pedido-body">
                    <div class="pedido-body-main">
                        <table>
                            <tr>
                                <td>Cliente:</td>
                                <td>('.$opedido->cliente_ped.') '.$opedido->nombre_cli.'</td>
                            </tr>
                            <tr>
                                <td>Dirección:</td>
                                <td>'.ucwords(strtolower($opedido->direccion_cli)).'</td>
                            </tr>
                            <tr>
                                <td>Vendedor:</td>
                                <td>('.$opedido->vendedor_ped.') '.$opedido->nombre_ven.'</td>
                            </tr>
                            <tr>
                                <td>Observacion:</td>
                                <td>'.$opedido->observacion_ped.'</td>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <div class="pedido-body-detail">
                        <table class="productos-lista">
                            <tr>
                            <th scope="col">codigo</th>
                            <th scope="col">descripción</th>
                            <th scope="col">ubicacion</th>
                            <th scope="col">und</th>
                            <th scope="col">cantidad</th>
                            </tr>';

                        for ($i=0;$i<count($opedido->pedido_detalle);$i++):
                            $plantilla .= '<tr>';
                            $plantilla .= '<td scope="row">'.$opedido->pedido_detalle[$i]["producto_ped"].'</th>';
                            $plantilla .= '<td style="text-align:left;">'.$opedido->pedido_detalle[$i]["descripcion_ped"].'</th>';
                            $plantilla .= '<td>'.$opedido->pedido_detalle[$i]["ubicacion_pro"].'</th>';
                            $plantilla .= '<td>'.$opedido->pedido_detalle[$i]["unidad_pro"].'</th>';
                            $plantilla .= '<td>'.number_format($opedido->pedido_detalle[$i]["cantidad_ped"],0).'</th>';
                        endfor;
                        $plantilla .='</table>
                    </div>
                </div>';
    return $plantilla;
    };
    
    
    
    function getcss(){
        $css = '* {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            font-family: "Courier New";
            text-rendering: optimizelegibility;	
            scroll-behavior: smooth;
        }
    
        #logo-lefre {
            width: 25%;
        }
        
        .productos-lista {
            border: 1px solid #ccc;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
            width: 100%;
            table-layout: fixed;
        }
    
        .productos-lista tr {
            /* background-color: #f8f8f8; */
            border: 1px solid #ddd;
            padding: .35em;
        }
    
        .productos-lista tr:nth-child(even){
            background-color: #f2f2f2;
        }
    
        .productos-lista th {
            padding: .5rem; 
            text-align: center;
            font-size: .85em;
            letter-spacing: .1em;
            text-transform: uppercase;
        }
    
        .productos-lista td {
            /* padding: .625em; */
            padding: .5rem; 
            text-align: center;
        }';
        return $css;
    };

    function getfooter(){
        $out = '<table width="100%">
                    <tr>
                    <td width="33%">'.$_SESSION["username"].' '.date('d-m-Y H:i').'</td>
                    <td width="33%" align="center">{PAGENO}/{nbpg}</td>
                    <td width="33%" style="text-align: right;">Tarea de Picking</td>
                    </tr>
                </table>';
        return $out;
    }
?>