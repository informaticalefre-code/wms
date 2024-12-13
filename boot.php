<?php
error_reporting (0);

//CONEXION A SQLSERVER
$serverName = "server02";
$connectionInfo = array(
    "Database" => "lefre_dv",
    "UID" => "gprueba",
    "PWD" => "Lefre.2023",
    "TrustServerCertificate" => "true"
);

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
//CONEXION A SQLSERVER


$app_name = $_POST["app"];
$sender   = $_POST["sender"];
$message  = $_POST["message"];
$telefatc="0414-4852667";

$fhoy = date("Y-m-d");
$dinicio = date('01-m-Y', strtotime($fhoy)); 



//acentos
//Reemplazamos la A y a
$message = str_replace(
        array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
        array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
        $message
        );
    
        //Reemplazamos la E y e
        $message = str_replace(
        array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
        array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
        $message );
    
        //Reemplazamos la I y i
        $message = str_replace(
        array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
        array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
        $message );
    
        //Reemplazamos la O y o
        $message = str_replace(
        array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
        array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
        $message );
    
        //Reemplazamos la U y u
        $message = str_replace(
        array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
        array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
        $message );
    
        //Reemplazamos la N, n, C y c
        $message = str_replace(
        array('Ñ', 'ñ', 'Ç', 'ç'),
        array('N', 'n', 'C', 'c'),
        $message
        );
    
    //acentos
    
    $message=strtolower($message);
    $consulta = explode (" ", $message);
    
    $comando=$consulta[0];
    $codigo=$consulta[1];
    //$casa=$consulta[2];
    //$recibo=$consulta[3];
    
    switch ($comando) {        
        case "mispedidos":
        case "suspedidos":
        case "menu":
            $cvalido=1;
        break;    
        default:
            $cvalido=0;            
    }
    
    if ($cvalido==1){
        switch ($comando) {
            case "menu":
                $mensaj="¡*Hola*! Soy *CondorApp*, tu asistente virtual 🦅. Por esta via podré atenderte de forma inmediata con información sobre:
                    
    ✔️ *El Tráfico de los pedidos* del mes por Cliente y por Vendedor.
         
    Para consultar solo debes enviar *la palabra clave* seguida de un espacio, tu número de zona o el codigo de tu cliente dependiendo la consulta que quieras efectuar
         
        *Palabras claves:*
    ✔️ *mispedidos* => Pedidos del Vendedor
    ✔️ *suspedidos* => Pedidos del Cliente                   
         
         *Por ejemplo:*
    mispedidos 123
    suspedidos 12233                                    
                                    
    📞   Soy un Asistente Virtual 🦅, si deseas mayor información puedes escribir a nuestro centro de atencion pulsando aqui ➡️ https://api.whatsapp.com/send?phone=+584144852667 o comunicarte al: *".$telefatc."*";                                        
            break; 
            case "suspedidos":  // PEDIDOS DE LOS CLIENTES
                $cod_cli=$codigo;

                $sql = "SELECT DISTINCT PA.numero_ped AS N_PEDIDO, SUM(PB.TOTALFULL_PED) AS MONTO_PEDIDO, CONVERT(varchar, PA.fecha_ped, 103) AS FECHA_PED, SUM(CASE WHEN PB.despachado_ped = 0 then '0' else PB.TOTALFULL_PED  end) AS MONTO_TOTAL, PA.status_ped AS ESTATUS_PED
                FROM TBPEDIDOS1 AS PA
                INNER JOIN TBPEDIDOS2 PB ON PA.numero_ped=PB.numero_ped
                where MONTH(fecha_ped)= MONTH(GETDATE()) AND  YEAR(fecha_ped)= YEAR(GETDATE()) and cliente_ped=$cod_cli
                GROUP BY FECHA_PED,PA.numero_ped,PA.status_ped ORDER BY FECHA_PED";
                $stmt = sqlsrv_query($conn, $sql);

                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }

                $mensaj="🦅 Bienvenido al *Servicio de Autogestion CondorApp*.
                 
    Le mostramos los pedidos tomados desde el *".$dinicio."* al dia de hoy.
                    
    ";
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    switch ($row['ESTATUS_PED']) {
                        case "TOTAL": $est="FACTURADO";break;
                        case "PARCIAL": $est="FACTURADO PARCIAL";break;
                        case "PENDIENTE": $est="EN PROCESO";break;
                        case "BACKLOG": $est="EN PROCESO";break;
                        case "RETENIDO": $est="RETENIDO";break;
                        case "PACKING": $est="EN PROCESO";break;
                        case "ANULADO": $est="ANULADO";break;
                        case "PICKING": $est="EN PROCESO";break;
                    }
                    $dato=$dato."*Fecha Pedido:* ".$row['FECHA_PED']." *Pedido:* ".$row['N_PEDIDO'] ." - *Monto Pedido:* $". number_format($row['MONTO_PEDIDO'],2, ',', '.')." - *Monto Facturado:* $". number_format($row['MONTO_TOTAL'],2, ',', '.')." - *Estatus:* ". $est."
    
    ";
                }
                sqlsrv_free_stmt($stmt);
                $mensaj=$mensaj.$dato. "            
    📞 Soy un Asistente Virtual 🦅, si deseas mayor información puedes escribir a nuestro centro de atencion pulsando aqui ➡️ https://api.whatsapp.com/send?phone=+584144852667 o comunicarte al: *".$telefatc."*
        
    📤  Envie menu para recibir más información sobre las opciones de  su canal de autogestión.
                            
                                   ";

            break; 
            case "mispedidos":  // PEDIDOS DE LOS VENDEDORES
                $vend=$codigo;

                $sql2 = "SELECT DISTINCT PA.cliente_ped AS CLIENTE,CONVERT(varchar, PA.fecha_ped, 103) AS FECHA_PED,PA.numero_ped AS N_PEDIDO,SUM(PB.TOTALFULL_PED) AS MONTO_PEDIDO,SUM(CASE WHEN PB.despachado_ped = 0 then '0' else PB.TOTALFULL_PED end) AS MONTO_TOTAL,PA.status_ped AS ESTATUS_PED
                FROM TBPEDIDOS1 AS PA
                INNER JOIN TBPEDIDOS2 PB ON PA.numero_ped=PB.numero_ped
                  where MONTH(fecha_ped)= MONTH(GETDATE()) AND  YEAR(fecha_ped)= YEAR(GETDATE()) and vendedor_ped='$vend' 
                  GROUP BY FECHA_PED,PA.cliente_ped,PA.numero_ped,PA.status_ped   ORDER BY FECHA_PED";
                $stmt2 = sqlsrv_query($conn, $sql2);
                
                if ($stmt2 === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
                $mensaj="🦅 Bienvenido al *Servicio de Autogestion CondorApp*.
                 
    Le mostramos los pedidos tomados desde el *".$dinicio."* al dia de hoy.
                    
    ";
                
                while ($row2 = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
                    $ped=$row2['N_PEDIDO'];
                    $ped="%".$ped."%";
                
                    $sql3="SELECT DISTINCT numero_fac AS N_FACT
                    FROM Tbfacturacion3
                    where MONTH(fecha_fac)= MONTH(GETDATE()) AND  YEAR(fecha_fac)= YEAR(GETDATE()) and vendedor_fac=$vend and pedido_fac LIKE '$ped'";
                    $stmt3 = sqlsrv_query($conn, $sql3);
                    if ($stmt3 === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }
                    $n_factu="";
                    while ($row3 = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)) {
                        $n_factu=$n_factu."-".$row3['N_FACT'];
                    }

                    switch ($row2['ESTATUS_PED']) {
                        case "TOTAL": $est="FACTURADO";break;
                        case "PARCIAL": $est="FACTURADO PARCIAL";break;
                        case "PENDIENTE": $est="EN PROCESO";break;
                        case "BACKLOG": $est="EN PROCESO";break;
                        case "RETENIDO": $est="RETENIDO";break;
                        case "PACKING": $est="EN PROCESO";break;
                        case "ANULADO": $est="ANULADO";break;
                        case "PICKING": $est="EN PROCESO";break;
                    }
                    $dato=$dato."*Fecha Pedido:* ".$row2['FECHA_PED']." - *Cliente:* ".$row2['CLIENTE']." - *Pedido:* ".$row2['N_PEDIDO'] ." - *Monto Pedido:* $". number_format($row2['MONTO_PEDIDO'],2, ',', '.')." - *N Nota:* ".$n_factu." - *Monto Facturado:* $". number_format($row2['MONTO_TOTAL'],2, ',', '.')." - *Estatus:* ". $est."
    
    ";                    
                }
                
                sqlsrv_free_stmt($stmt2);
                $mensaj=$mensaj.$dato. "            
    📞 Soy un Asistente Virtual 🦅, si deseas mayor información puedes escribir a nuestro centro de atencion pulsando aqui ➡️ https://api.whatsapp.com/send?phone=+584144852667 o comunicarte al: *".$telefatc."*
        
    📤  Envie menu para recibir más información sobre las opciones de  su canal de autogestión.
                            
                                   ";
            break;
        } // END SWITCH
    }else{
        $mensaj="🦅 Disculpe el comando enviado no esta asociado a ninguna acción, valide e intente nuevamente.

                    
        📞 Soy un Asistente Virtual 🦅, si deseas mayor información puedes escribir a nuestro centro de atencion pulsando aqui ➡️ https://api.whatsapp.com/send?phone=+584144852667 o comunicarte al: *".$telefatc."*

        📤  Envie menu para recibir más información sobre las opciones de  su canal de autogestión.
                    
                           ";
   }    // END IF
$reply = array("reply" => $mensaj);
echo json_encode($reply);

?>