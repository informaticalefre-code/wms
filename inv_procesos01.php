<?php
    /*************************************************************************************
     *  Asigna productos cada 2 racks a los usuarios asignados al proceso de inventario
     *************************************************************************************/
    require_once 'config/Database_mariadb.php';
    $data_maria   = new Db_mariadb(); // Nueva conexión a Mariadb
    $db_maria     = $data_maria->getConnection();
    $idinventario = 5;
    $grupos_rack  = 2; //Cada cuantos racks se asignará a cada usuario para contar


    /* Primero tomamos las personas que harán el conteo */
    $sql  = "SELECT invp_username FROM tinventarios_personas WHERE invp_id = :id";
    $stmt = $db_maria->prepare($sql);
    $stmt -> bindparam(':id', $idinventario);
    $stmt->execute();
    $personas = $stmt->fetchall(PDO::FETCH_ASSOC);
    $personas1 = array_column($personas,"invp_username");
    var_dump($personas1);
    echo "<br>";
    $personas2 = orden_conteo2($personas1);
    $personas_total = count($personas2);
    echo "total personas:".$personas_total."<br>";

    /* Ahora tomamos los racks */
    $sql  = "SELECT rack, '' as usuario1, '' as usuario2 FROM vproductos_rack ORDER BY rack";
    $stmt = $db_maria->prepare($sql);
    $stmt->execute();
    $racks = $stmt->fetchall(PDO::FETCH_ASSOC);

    var_dump($personas1);
    echo "<br>";
    var_dump($personas2);
    echo "<br>";
    print_r($personas1[0]);
    print_r($personas1[1]);

    /* Asignamos cada 2 racks a 1 usuario */
    $i=0;
    $error = false;
    $db_maria->beginTransaction();
    echo "total array personas1 = ".count($personas1)."<br>";
    echo "total array racks = ".count($racks)."<br>";
    while($i<count($racks)):
        for($j=0; $j<count($personas1); $j++):
            for ($k=1; $k<=$grupos_rack; $k++):
                echo "j=".$j."<br>";
                print_r("persona1:".$personas1[$j]."<br>");
                print_r("persona2:".$personas2[$j]."<br>");
                print_r("rack:".$racks[$i]["rack"]."<br>");

                $sql = "UPDATE tinventarios_detalle a
                SET a.invd_username1 = :usuario1, a.invd_username2 = :usuario2, a.user_mod = 'jcfreytes'
                WHERE a.invd_id = :id
                AND SUBSTRING_INDEX(a.invd_ubicacion, '-', 2) = :rack";
                $stmt = $db_maria->prepare($sql);
                $stmt->bindparam(':id', $idinventario, PDO::PARAM_INT);
                $stmt->bindparam(':rack', $racks[$i]["rack"], PDO::PARAM_STR);
                $stmt->bindparam(':usuario1', $personas1[$j], PDO::PARAM_STR);
                $stmt->bindparam(':usuario2', $personas2[$j], PDO::PARAM_STR);
                
                try {
                    $stmt->execute();
                }catch (PDOException $e) {
                    $error = true;
                }catch (Exception $e) {
                    $error = true;
                }
                if ($error){
                    die;
                }   
                $racks[$i]["usuario1"] = $personas1[$j];
                $racks[$i]["usuario2"] = $personas2[$j];
                echo "i=".$i."<br>";
                $i++;
            endfor;
        endfor;
    endwhile;
    
    if (!$error){
        $db_maria->commit();
    }else {
        $db_maria->rollback();
    }




    function orden_conteo2($array1){
        $iguales = false;
        $array2 = $array1;
        shuffle($array2);

        //ahora comparamos si todos son diferentes
        for($i=0;$i<count($array1);$i++):
            if ($array1[$i] == $array2[$i]){
                $iguales = true;
                break;
            }   
        endfor;
        if ($iguales){
             $array2 = orden_conteo2($array1);
        }
        return ($array2);
    }

?>