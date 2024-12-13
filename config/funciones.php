<?php
//funciones.php

/******************************************************
* Valida las entradas de los inputs contra
* Cross-site scripting (XSS)
******************************************************/
function test_input($data) {
    if (!is_null($data)){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
    }
    return $data;
}


/********************************************************
*                 GET USER ROLES
* Devuelve un array con los roles asignados a un usuario
********************************************************/
function get_menu_opciones($username){
    require_once 'Database_mariadb.php';
    $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
    $db_maria   = $data_maria->getConnection();

    $sql  = "SELECT DISTINCT a.menu_id FROM tmenu_roles a JOIN tusuarios_roles b ON a.menu_role = b.usrr_role WHERE b.usrr_name = :username";
    $stmt =  $db_maria->prepare($sql,array(PDO::ATTR_CURSOR=>PDO::CURSOR_SCROLL));
    $stmt->bindparam(':username',$username,PDO::PARAM_STR);
    $stmt->execute();
    $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
    $resp = array_column($resp,'menu_id');
    $data_maria = $db_maria = null;
    unset($username,$data_maria,$db_maria,$sql,$stmt);
    return ($resp);
}

    /***********************************************************************
    *                        BADGE HTML (BOOTSTRAP)
    ***********************************************************************/
    function badge_html($pstatus_ped){
        $estatus = trim($pstatus_ped);
        if ($pstatus_ped === 'ASIGNADO'){
            $badge = 'bg-info text-dark';
        }elseif ($pstatus_ped === 'TOTAL' || $pstatus_ped === 'PARCIAL'){
            $badge = 'bg-warning text-dark';
        }elseif ($pstatus_ped === 'RETENIDO'){
            $badge = 'bg-danger';
        }elseif ($pstatus_ped === 'PENDIENTE'){
            $badge = 'bg-warning text-dark';
        }elseif ($pstatus_ped === 'ANULADO'){
            $badge = 'bg-danger';
        }elseif ($pstatus_ped === 'APROBADO'){
            $badge = 'bg-primary';
        }elseif ($pstatus_ped === 'BACKLOG'){
            $badge = 'bg-secondary';
        }elseif ($pstatus_ped === 'PICKING'){
            $badge = 'bg-secondary';
        }elseif ($pstatus_ped === 'PACKING'){
            $badge = 'bg-secondary';            
        }elseif (empty($estatus)){
            $badge   = 'bg-info';
            $estatus = 'NUEVO';
        }else{
            $estatus = 'Estatus no reconocido';
            $badge = 'bg-danger';
        }
        $out   = '<span style="font-size: 80%;" class="badge '.$badge.'">'.$estatus.'</span>';
        $badge = $estatus = $pstatus_ped = null;
        unset($badge, $estatus, $pstatus_ped);
        return $out;
    }

?>