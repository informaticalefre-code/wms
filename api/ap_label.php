<?php
    require_once 'user-auth-api.php';
    require_once '../config/Database_mariadb.php';
    require_once '../clases/clase_packing.php';
    require_once '../config/funciones.php';

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json; charset=UTF-8');
    $metodo = $_SERVER["REQUEST_METHOD"];
    if ($metodo=='POST'){
        $data       = json_decode(file_get_contents("php://input"));
        $accion     = test_input($data->accion);
        if (isset($data->accion) && $data->accion=='print-bulto-label'){
            $idpacking = test_input($data->idpacking);
            $idbulto   = test_input($data->idbulto);
            $out       = print_bulto_label($idpacking,$idbulto);
            echo json_encode($out);
        }elseif (isset($data->accion) && $data->accion=='print-all-label'){
            $idpacking = test_input($data->idpacking);
            $out       = print_all_label($idpacking);
            echo json_encode($out);
        }else{
            $out=[];
            $out["response"]   = "fail";
            $out["error_nro"]  = '45020';
            $out["error_msj"]  = 'Solicitud de recursos no valida. No se puede interpretar solicitud';
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


    /***************************************************************************
    *                           PRINT ALL LABEL
    * Imprime todas las etiquetas de los bultos cerrados de una tarea de packing
    ****************************************************************************/
    function print_all_label($idpacking){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $sql = "SELECT a.pack_idpacking, a.pack_idbulto, a.pack_peso, a.pack_unidadpeso, b.pack_idpedido, d.nombre_cli, d.direccion_cli, e.codigo_ven, e.nombre_ven 
        FROM vpacking_bultos a 
        JOIN vpacking b ON a.pack_idempresa = b.pack_idempresa AND b.pack_idpacking = a.pack_idpacking
        JOIN tbpedidos1 c ON c.numero_ped = b.pack_idpedido
        JOIN tbclientes d ON d.codigo_cli = c.cliente_ped
        JOIN tbvendedores e ON e.codigo_ven = c.vendedor_ped
        WHERE a.pack_idpacking = :idpacking AND a.pack_status = 1
        ORDER BY a.pack_idbulto";

        $stmt = $db_maria->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idpacking',$idpacking, PDO::PARAM_INT);

        $stmt->execute();
        $resp   = $stmt->fetchall(PDO::FETCH_ASSOC);
        $labels = '';
        $out    = [];
        for ($i=0; $i<count($resp);$i++){
            $labels .= print_label($resp[$i]);
        }

        if ($stmt->rowcount() > 1){
            $out["response"] = 'success';
            $out["etiqueta"] = $labels;
        }else{
            $out["response"]  = 'fail';
            $out["error_tpo"] = 'error';
            $out["error_msj"] = 'No se encontraron datos';
        }
        $data_maria = $db_maria = $resp = $labels = null;
        unset($data_maria, $db_maria, $sql, $stmt, $resp, $labels);
        return ($out);
    }


    /*************************************************************************
    *                           PRINT BULTO LABEL
    * Genera toda la información necesaria para la impresión del ticket
    *************************************************************************/
    function print_bulto_label($idpacking,$idbulto){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $sql = "SELECT a.pack_idpacking, a.pack_idbulto, a.pack_peso, a.pack_unidadpeso, b.pack_idpedido, d.nombre_cli, d.direccion_cli, e.codigo_ven, e.nombre_ven 
        FROM vpacking_bultos a 
        JOIN vpacking b ON a.pack_idempresa = b.pack_idempresa AND b.pack_idpacking = a.pack_idpacking
        JOIN tbpedidos1 c ON c.numero_ped = b.pack_idpedido
        JOIN tbclientes d ON d.codigo_cli = c.cliente_ped
        JOIN tbvendedores e ON e.codigo_ven = c.vendedor_ped
        WHERE a.pack_idempresa = :idempresa AND a.pack_idpacking = :idpacking AND a.pack_idbulto = :idbulto
        AND a.pack_status = 1";

        $stmt = $db_maria->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindvalue(':idempresa', 1, PDO::PARAM_INT);
        $stmt->bindparam(':idpacking', $idpacking, PDO::PARAM_INT);
        $stmt->bindparam(':idbulto', $idbulto, PDO::PARAM_INT);

        $stmt->execute();
        $resp = [];
        $out = [];
        if ($stmt->rowcount()== 1){
            $resp  = $stmt->fetch(PDO::FETCH_ASSOC);
            $out["response"] = 'success';
            $out["etiqueta"] = print_label($resp);
        }else{
            $out["response"]  = 'fail';
            $out["error_tpo"] = 'error';
            $out["error_msj"] = 'No se encontraron datos';
        }
        $data_maria = $db_maria = null;
        unset($data_maria, $db_maria, $sql, $stmt, $resp);
        return ($out);
    }

    
    /*******************************************************************
    *                     PRINT LABEL
    * Genera el ZPL necesario de la etiqueta
    ********************************************************************/
    function print_label($resp){
        // ^LL Label Height 2 pulgadas = 406 dot
        // ^LL Label Height 3 pulgadas = 609 dot
        // ^PW Label Width 4 pulgadas = 812 dot  
        // ^MT Media Type T=thermal transfer media D=direct thermal media
        // ^BY 2 Bar Code Field Default
        $label  = "^XA^LL406^PW812^MTT";
        $label .= "^FO50,25^GFA,3496,3496,38,Q0E,P03F8,P0FFE,O03IF8,O0JFE,N03KF8,N0LFE,M03MF8,M0NFE,L01OF8,L07OFE,K01JFC03JF8,K07JF001JFE,J01JFCI07JF,J07JFJ01JFC,I01JFC006007JF,I07JF003F801JFC,001JFC007FE007JF,007JF001IF801JFC,01JFC007IFE007JF,07JF001KF001JFC,1JFC007KFC007JF,7JF801MF001JFC,7IFE007MFC007IFE,3IF801OF001IF8,0FFE007OFC007FE,03F801JFC07JF003F8,K07JF001JFC,J01JFCI07JFM07F8M07NF07NF07LFCI07NF,J07JFJ01JFCL0FF8M07NF07NF07MF8007NF,I01JFE00E007JFL07F8M07NF07NF07MFE007NF,J0JF803F801JFL07F8M07NF07NF07NF007NF,J03FFE00FFE007FFCL07F8M07NF07NF07NF807NF,K0FF801IF801FFM07F8M07NF07NF07NFC07NF,00E003E007IFE007C006J07F8M07NF07NF07NFC07NF,03F8J01KF8J03F8I07F8M07NF07NF07NFE07NF,0FFEJ07KFCJ0FFEI07F8M07NF07NF07NFE07NF,3IF8001MF8003IF8007F8M07F8M07F8M07F8J07FE07F8,7IFE003MF800JFE007F8M07F8M07F8M07F8J03FF07F8,3JF800MF003JF8007F8M07F8M07F8M07F8J01FF07F8,0JFE003KFC00JFEI07F8M07F8M07F8M07F8J01FF07F8,03JF800FC07F001JFCI07F8M07FCM07F8M07F8J01FF07F8,00JFE003001C007JFJ07F8M07NF07F83KF07F8J01FF07F8LF,003JF8M01JFCJ07F8M07NF07F87KF07F8J01FF07F8LF,I0JFEM07JFK07F8M07NF07F8LF07F8J03FF07F8LF,I07JF8K01JFCK07F8M07NF07F8LF07F8J07FE07F8LF,I01JFCK07JFL07F8M07NF07F9LF07NFE07F8LF,J07JFJ01JFCL07F8M07NF07F9LF07NFE07F8LF,J01JFCI07JFM07F8M07NF07FBLF07NFC07F8LF,K07JF001JFCM07F8M07NF07NF07NFC07F8LF,03F801JFC07JF003F8I07F8M07FCM07FCM07NF807F8,0FFE007OFC00FFEI07F8M07F8M07F8M07NF007F8,3IF801OF003IF8007F8M07F8M07F8M07MFE007F8,7IFE007MFC007IFE007F8M07F8M07F8M07MF8007F8,3JF801MF801JFC007F8M07F8M07F8M07MF8007F8,0JFE007KFE007JFI07NF07NF07F8M07F8I07FC007NF,03JF801KF801JFCI07NF07NF07F8M07F8I07FE007NF,01JFE007IFE007JFJ07NF07NF07F8M07F8I03FE007NF,007JF001IF801JFCJ07NF07NF07F8M07F8I03FF007NF,001JFC00FFE007JFK07NF07NF07F8M07F8I01FF807NF,I07JF003F801JFCK07NF07NF07F8M07F8J0FF807NF,I01JFC00E007JFL07NF07NF07F8M07F8J0FFC07NF,J07JFJ01JFCL0OF07NF07F8M07F8J07FE07NF,J01JFCI07JFM07NF07NF07F8M07F8J07FE07NF,K07JF001JFC,03F801JFC07JF001F8,07FE007OFE007FE,1IF801OF801IF8,7IFE007MFE007IFE,7JF001MF801JFC,1JFC00LFE007JF,07JF003KF801JFC,01JFC00JFE007JF,007JF003IF801JFC,001JFC00FFE007JF,I07JF003F801JFC,I01JFC00E007JF,J07JFJ01JFC,J01JFCI03JF8,K07JFI0JFE,K01JFC03JF8,L07JFBJFE,L03OF8,M0NFE,M03MF8,N0LFE,N03KF8,O0JFE,O03IF8,P0FFE,P03FC,Q0F,^FS";
        $label .= "^FO450,25";
        $label .= "^CF0,50";
        $label .= "^FD".$resp["pack_idpedido"]."^FS";
        $label .= "^FO450,80";
        $label .= "^BY2";
        $label .= "^BCN, 40, N, N, N^FD".$resp["pack_idpedido"]."^FS";
        $label .= "^CI28";
        $label .= "^CF0,30";
        $label .= "^FO50,145^FD".$resp["nombre_cli"]."^FS";
        $label .= "^FO50,180^GB700,3,3^FS";
        $label .= "^CFA,20";
        $label .= "^FO50,200^FD".mb_substr($resp["direccion_cli"],0,60,"UTF-8")."^FS";
        $label .= "^FO50,230^FD".mb_substr($resp["direccion_cli"],60,NULL,"UTF-8")."^FS";
        $label .= "^CF0,25";
        $label .= "^FO50,265^FD".$resp["nombre_ven"]."^FS";
        $label .= "^CFA,22";
        $label .= "^FO50,355";
        $label .= "^FDPeso:^FS";
        $label .= "^FO110,337";
        $label .= "^CF0,45";
        $label .= "^FD".$resp["pack_peso"]." ".$resp["pack_unidadpeso"]."^FS";
        $label .= "^FO330,355";
        $label .= "^CFA,22";
        $label .= "^FDBulto:^FS";
        $label .= "^FO400,320";
        $label .= "^CF0,70";
        $label .= "^FD".$resp["pack_idbulto"]."^FS";
        $label .= "^FO570,310";
        $label .= "^BCN, 80, N, Y, N^FD".$resp["pack_idpacking"]."-".$resp["pack_idbulto"]."^FS";
        $label .= "^XZ";
        $resp = null;
        unset ($resp);
        return ($label);
    }



    
?>

