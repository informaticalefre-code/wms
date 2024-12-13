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
$opedido     = new Pedido($db_sqlsrv);
$opedido->numero_ped = test_input($_POST["idpedido"]);
$html = stripcslashes($_POST["html"]);
$opedido->carga_pedidos_master();
if ($opedido->error){
    http_response_code(404);
    unset($data_sqlsrv, $db_sqlsrv);
    echo $opedido->error_msj;
}else{
    // Ahora vamos con el reporte
    $filePDF       = uniqid(rand()).'.pdf';
    $format_header = getheader($opedido);
    $format_footer = getfooter();
    $format_html   = '<div class="pedido-body">'.$html.'</div>';
    $format_css    = getcss();
    $count_titulos = substr_count($html,'bulto-titulo');
    $count_items   = substr_count($html,'bulto-prod-linea');
    if (($count_titulos + $count_items) < 15) {
        $size = array(215,139); // Media carta
    }else{
        $size = array(215,279); // Tamaño carta
    }
    
    $mpdf = new \Mpdf\Mpdf(['mode'           => 'c', 
                        'format'         => $size,
                        'orientation'    => 'P',
                        'margin_top'     => 35,
                        'margin_bottom'  => 4,
                        'margin_left'    => 10,
                        'margin_right'   => 10,
                        'margin_header'  => 2,
                        'margin_footer'  => 4,
                        'default_font_size' => 8,
                        'default_font' => 'San Serif'
                    ]); // Media Carta
    
    $mpdf->charset_in = 'iso-8859-4';
    //$mpdf->charset_in = 'iso-8859-6'; // No funciona
    $mpdf->SetHTMLHeader($format_header);
    //$mpdf->SetHTMLHeader('<div>('.$opedido->vendedor_ped.') '.$opedido->nombre_ven.'</div>');
    $mpdf->SetFooter($format_footer);
    $mpdf->WriteHTML($format_css,\Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($format_html, \Mpdf\HTMLParserMode::HTML_BODY);

    $mpdf->Output('../tmp/'.$filePDF,\Mpdf\Output\Destination::FILE);
    
    $mpdf = null;
    unset($mpdf);
    $data_sqlsrv = $db_sqlsrv = $opedido = $html = $format_html = $format_header = $format_footer = $count_titulos = $count_items = $size = null;
    unset($data_sqlsrv, $db_sqlsrv, $opedido, $html, $format_html, $format_header, $format_footer, $count_titulos, $count_items, $size);
    echo $filePDF;
}

function getheader($opedido){
    $plantilla = '<div id="pedido-header">
        <table style="width:100%;">
            <tr>
                <td style="width:80%;">
                    <img id="logo-lefre" src="../img/svg/lefre-logo03.svg">
                    <p>Fecha '.$opedido->fecha_ped.'</p>
                </td>
                <td style="text-align: center;">
                    <barcode code="'.$opedido->numero_ped.'" type="C128A" class="barcode" size="1"/>
                    <div style="font-size:12pt;"><strong>'.$opedido->numero_ped.'</strong></div>
                </td>
            </tr>
        </table>
        <table style="width:100%;">
            <tr>
                <td>Cliente: ('.$opedido->cliente_ped.') '.$opedido->nombre_cli.'</td>
            </tr>
            <tr>
                <td>Vendedor: ('.$opedido->vendedor_ped.') '.$opedido->nombre_ven.'</td>
            </tr>
        </table>                    
    </div>
    <hr style="margin:0.5% 0%;padding:0%;">';
    return $plantilla;
};


// function gethtml($opedido){
//     $plantilla = '<div class="pedido-body">
//                     <div class="pedido-body-main">
//                         <table>
//                             <tr>
//                                 <td>Cliente:</td>
//                                 <td>('.$opedido->cliente_ped.') '.$opedido->nombre_cli.'</td>
//                             </tr>
//                             <tr>
//                                 <td>Vendedor:</td>
//                                 <td>('.$opedido->vendedor_ped.') '.$opedido->nombre_ven.'</td>
//                             </tr>
//                             <tr>
//                                 <td>Observacion:</td>
//                                 <td>'.$opedido->observacion_ped.'</td>
//                             </tr>
//                         </table>
//                     </div>
//                     <br>
//                     <div class="pedido-body-detail">
//                         <table class="productos-lista">
//                             <tr>
//                             <th scope="col">codigo</th>
//                             <th scope="col">descripción</th>
//                             <th scope="col">ubicacion</th>
//                             <th scope="col">und</th>
//                             <th scope="col">cantidad</th>
//                             </tr>';

//                         for ($i=0;$i<count($opedido->pedido_detalle);$i++):
//                             $plantilla .= '<tr>';
//                             $plantilla .= '<td scope="row">'.$opedido->pedido_detalle[$i]["producto_ped"].'</th>';
//                             $plantilla .= '<td style="text-align:left;">'.$opedido->pedido_detalle[$i]["descripcion_ped"].'</th>';
//                             $plantilla .= '<td>'.$opedido->pedido_detalle[$i]["ubicacion_pro"].'</th>';
//                             $plantilla .= '<td>'.$opedido->pedido_detalle[$i]["unidad_pro"].'</th>';
//                             $plantilla .= '<td>'.number_format($opedido->pedido_detalle[$i]["cantidad_ped"],0).'</th>';
//                         endfor;
//                         $plantilla .='</table>
//                     </div>
//                 </div>';
//     return $plantilla;
//     };
    
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
        
        .productos-bultos {
            width: 100%;
            margin-bottom: 0.5rem;
            color: #212529;
            vertical-align: top;
            border-color: #0065cb;
            /*border-color: #dee2e6;*/
            font-size:10pt;
            border-style:solid;
        }

        h6{
            font-size:11pt;
            margin: 0px;
        }';
        return $css;
    };

    function getfooter(){
        $out = '<table width="100%">
                    <tr>
                    <td width="33%">'.$_SESSION["username"].' '.date('d-m-Y H:i').'</td>
                    <td width="33%" align="center">{PAGENO}/{nbpg}</td>
                    <td width="33%" style="text-align: right;">Productos por Bultos</td>
                    </tr>
                </table>';
        return $out;
    }
?>