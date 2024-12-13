<?php
    // Recuerdese que para recuperar clave nunca hay una sesión iniciada.
    // Si existe alguna hay error.
    if ($_SERVER["REQUEST_METHOD"] == 'POST'){
        if (isset($_POST["accion"]) && $_POST["accion"]!== "clave-recuperar"){
            require_once 'user-auth-api.php';
        }
    }else{
        require_once 'user-auth-api.php';
    };
    require_once '../config/Database_mariadb.php';
    require_once '../clases/clase_usuario.php';
    require_once '../config/funciones.php';
    
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json; charset=UTF-8');
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");
    $metodo = $_SERVER["REQUEST_METHOD"];
    if ($metodo=='POST'){
        if (isset($_POST["accion"]) && $_POST["accion"]=="user-registro"){
            $out = insert_new_user();
            http_response_code(200);
            echo json_encode($out);
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=="clave-recuperar"){            
            $out = enlace_recuperar_clave(test_input($_POST["user_email"]));
            http_response_code(200);
            echo json_encode($out);
        }elseif (isset($_POST["accion"]) && $_POST["accion"]=="barra-search"){
            $search_text   = test_input($_POST["search-text"]);
            $search_option = test_input($_POST["search-options"]);
            $bloque        = intval(filter_var(test_input($_POST["bloque"]), FILTER_SANITIZE_NUMBER_INT));
            $filtros       = query_filtros_usuarios();
            $sql_obj       = set_usuarios_sqlwhere($search_text, $search_option, $filtros);
            $lista         = query_usuarios_lista($sql_obj, $bloque);
            $out["html_lista"] = html_usuarios_lista($lista);
            $lista = $filtros = $sql_obj = null;
            unset($lista, $filtros, $sql_obj);
            unset($tpo_lista, $lista);
            unset($search_text, $search_option, $bloque);
            unset($_POST["lista"], $_POST["accion"], $_POST["search-text"], $_POST["search-options"], $_POST["bloque"]);
            echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
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
    }elseif ($metodo=='GET'){
        if (isset($_GET['user'])){
            $username = test_input($_GET["user"]);
            $out = carga_usuario($username);
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
    }elseif ($metodo=='PUT'){
        $out = update_usuario();
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


    /***********************************************************************
    *                       INSERT NEW USER
    ***********************************************************************/
    function insert_new_user(){
        require_once '../clases/clase_email.php';
        $data_maria               = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                 = $data_maria->getConnection();
        $ousuario                 = new Usuario($db_maria);
        $ousuario->user_name      = test_input($_POST["name"]);
        $ousuario->user_nombre    = test_input($_POST["nombre"]);
        $ousuario->user_apellido  = test_input($_POST["apellido"]);
        $ousuario->user_email     = filter_var(test_input($_POST["email"]),FILTER_SANITIZE_EMAIL);
        $ousuario->user_role      = test_input($_POST["role"]);
        $ousuario->user_password  = test_input($_POST["password"]);
        $ousuario->user_idempresa = 1;

        $ousuario->valida_cuenta_usuario();

        $db_maria->beginTransaction();

        if (!$ousuario->error){
            $ousuario->insert_usuario_master();
        }

        if (!$ousuario->error){
            $ousuario->insert_role();
        }

        if (!$ousuario->error){
            $mail = new Email();
            $mail->send_email('user-registro',$ousuario->user_email,$ousuario->user_token);
            if ($mail->error) {
                $ousuario->error = true;
                $ousuario->error_msj = $mail->error_msj;
            }
        }

        if (!$ousuario->error){
            $db_maria->commit();
            $out["response"] = "success";
            $out["texto"]    = "Usuario creado con exito.";
        }else{
            $db_maria->rollback();
            $out["response"]   = "fail";
            $out["error_nro"]  = $ousuario->error_nro;
            $out["error_msj"]  = $ousuario->error_msj;
            $out["error_file"] = $ousuario->error_file;
            $out["error_line"] = $ousuario->error_line;
            $out["error_tpo"]  = 'error';
        }
        return $out;
    }    


    /***********************************************************************
    *                      ENLACE_RECUPERAR_CLAVE
    ***********************************************************************/
    function enlace_recuperar_clave($email){
        require_once '../clases/clase_email.php';
        $data_maria               = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria                 = $data_maria->getConnection();
        $ousuario                 = new Usuario($db_maria);
        $ousuario->user_idempresa = 1;
        $ousuario->enlace_recuperar_clave($email);
        if (!$ousuario->error){
            $mail = new Email();
            $mail->send_email('user-clave',$ousuario->user_email,$ousuario->user_token);
            if ($mail->error) {
                $ousuario->error = true;
                $ousuario->error_msj = $mail->error_msj;
            }
            $mail = null;
            unset($mail);
        }

        if (!$ousuario->error){
            $out["response"] = "success";
            $out["texto"]    = "Correo de recuperación enviado con éxito.";
        }else{
            $out["response"]   = "fail";
            $out["error_nro"]  = $ousuario->error_nro;
            $out["error_msj"]  = $ousuario->error_msj;
            $out["error_file"] = $ousuario->error_file;
            $out["error_line"] = $ousuario->error_line;
            $out["error_tpo"]  = 'error';
        }
        $db_maria = $data_maria = $ousuario = null;
        unset($data_maria, $db_maria, $ousuario);
        return $out;
    }

    /***********************************************************************
    *                    QUERY FILTROS
    ***********************************************************************/
    function query_filtros_usuarios(){
        $filtro = "";
        $afiltro1 = [];
        if (!isset($_POST["rol-todos"])){
            if (isset($_POST["rol-aprobador"])){
                array_push($afiltro1,'\'APROBADOR\'');
            }
            if (isset($_POST["rol-pedidos"])){
                array_push($afiltro1,'\'PEDIDOS\'');
            }
            if (isset($_POST["rol-preparador"])){
                array_push($afiltro1,'\'PREPARADOR\'');
            }
            if (isset($_POST["rol-verificador"])){
                array_push($afiltro1,'\'VERIFICADOR\'');
            }            
            if (isset($_POST["rol-embalador"])){
                array_push($afiltro1,'\'EMBALADOR\'');
            }
            if (isset($_POST["rol-recepcion"])){
                array_push($afiltro1,'\'RECEPCION_CONTROL\'');
            }
            if (isset($_POST["rol-logistica"])){
                array_push($afiltro1,'\'LOGISTICA\'');
            }
            if (isset($_POST["rol-admin"])){
                array_push($afiltro1,'\'ADMIN\'');
            }
        }

        if (!empty($afiltro1)){
            $filtro = "b.usrr_role IN (".implode(', ', $afiltro1).")";
        }
        
        $afiltro1 = null;
        unset($afiltro1);
        return ($filtro);
    }

    /***********************************************************************
    *                      SET PRODUCTO SQL WHERE
    * Determina las condiciones del where y order by del SELECT-SQL 
    * de las consultas de pedidos.
    ***********************************************************************/
    function set_usuarios_sqlwhere($search_text, $search_options, $filtros){
        if ($search_options == 'todos'):
            $sql_over  = 'a.user_name';
            $sql_where = null;
            $sql_order = null;
            $sql_value = null;
        elseif ($search_options == 'username'):
            $sql_over  = 'a.user_name';
            $sql_where = 'WHERE a.user_name like :search_text';
            $sql_order = 'ORDER by a.user_name desc';
            $sql_value = $search_text . '%';
        elseif ($search_options == 'nombre'):
            $sql_over  = 'a.user_nombre';
            $sql_where = 'WHERE a.user_nombre like :search_text';
            $sql_order = 'ORDER by a.user_nombre';
            $sql_value = $search_text . '%';
        endif;
        if (!empty($sql_where) && !empty($filtros)){
            $sql_where .= ' AND '.$filtros;
        }elseif (empty($sql_where) && !empty($filtros)){
            $sql_where = "WHERE ".$filtros;
        }        
        $sql_struct = new stdClass();
        $sql_struct->option    = $search_options;
        $sql_struct->sql_over  = $sql_over;
        $sql_struct->sql_where = $sql_where;
        $sql_struct->sql_order = $sql_order;
        $sql_struct->sql_value = $sql_value;
        unset($search_text, $search_options, $filtros);
        unset($sql_over, $sql_where, $sql_order, $sql_value);
        return ($sql_struct);
    }


    /******************************************************
    *               QUERY USUARIOS LISTA
    *******************************************************/
    function query_usuarios_lista($sql_obj,$pbloque){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $block      = ($pbloque - 1) * 20;

        // Armamos el SQL
        $sql  = "SELECT a.user_name, a.user_nombre, a.user_email, a.user_activo, b.usrr_role
                   FROM vusuarios a LEFT JOIN tusuarios_roles b ON a.user_name = b.usrr_name";
        $sql .= " ".$sql_obj->sql_where;
        $sql .= " ".$sql_obj->sql_order;
        $sql .= " LIMIT :bloque,20";
        // echo "bloque:".$block."\n";
        // echo $sql ."\n";

        $stmt = $db_maria->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        if ($sql_obj->option !== 'todos'):
            $stmt -> bindparam(':search_text', $sql_obj->sql_value, PDO::PARAM_STR);    
        endif;
        $stmt -> bindparam(':bloque', $block, PDO::PARAM_INT);

        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        $sql_obj = $pbloque = $db_sqlsrv = $data_sqlsrv = $block = null;
        unset($db_sqlsrv, $data_sqlsrv, $sql, $stmt, $sql_obj, $pbloque, $block);
        unset($search_text, $search_options, $pbloque);
        return $resp;
    }

    /***********************************************************************
    *                      PRODUCTOS LISTA
    ***********************************************************************/
    function html_usuarios_lista($ausuarios){
        $out = '';
        if (count($ausuarios)> 0){
            foreach($ausuarios as $fila):
                $obj = new stdClass();
                $obj->user_name   = $fila["user_name"];
                $obj->user_nombre = $fila["user_nombre"];
                $obj->user_email  = $fila["user_email"];
                $obj->user_activo = $fila["user_activo"];
                $obj->usrr_role   = $fila["usrr_role"];
                $out .= html_usuarios_linea($obj);
            endforeach;
            $obj = null;
            unset ($obj);
            http_response_code(200);
        }else{
            $out  = '<div class="alert alert-danger" role="alert">';
            $out .= 'No se encontraron coincidencias';
            $out .= '</div>';
            http_response_code(404);
        }
        unset($ausuarios);
        return ($out);
    }

    /***********************************************************************
    *                  HTML PaCKING LINEA
    ***********************************************************************/
    function html_usuarios_linea($ousuarios){
        $out  = '';
        $out .= '<tr id="'.$ousuarios->user_name.'" class="usuario_linea">';
        $out .= '<td>'.$ousuarios->user_name.'</td>';
        $out .= '<td>'.$ousuarios->user_nombre.'</td>';
        $out .= '<td>'.$ousuarios->user_email.'</td>';
        if ($ousuarios->user_activo == 0){
            $out .= '<td><span class="badge rounded-pill bg-danger">No Activo</span></td>';
        }else{
            $out .= '<td>Activo</td>';
        }
        if (!empty($ousuarios->usrr_role)){
            $out .= '<td>'.$ousuarios->usrr_role.'</td>';
        }else{
            $out .= '<td>&nbsp</td>';
        }
        $out .= '<td class="col_actions">';

        $out .= '<a href="'.$_SERVER['HTTP_ORIGIN'].'/wms/user-form.php?user='.$ousuarios->user_name.'"';
        $out .= '<button type="button" name="update" class="btn btn-warning" id="edit-'.$ousuarios->user_name.'"><i class="icon-edit-12"></i></button>';
        $out .= '</a>';
        $out .= '</td>';
        $out .= '</tr>';
        $ousuarios = null;
        unset($ousuarios);
        return $out;
    }

    /***********************************************************************
    *                      CARGA USUARIO
    ***********************************************************************/
    function carga_usuario($username){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $ousuario   = new Usuario($db_maria);
        $ousuario->carga_usuario($username);

        if (!$ousuario->error){
            $out = array();
            $out["response"] = 'success';
            $out["nombre"]   = $ousuario->user_nombre;
            $out["apellido"] = $ousuario->user_apellido;
            $out["email"]    = $ousuario->user_email;
            $out["activo"]   = $ousuario->user_activo;
            $out["role"]     = $ousuario->user_role;
        }else{
            $out["response"]   = 'fail';
            $out["error_nro"]  = $ousuario->error_nro;
            $out["error_msj"]  = $ousuario->error_msj;
            $out["error_tpo"]  = 'error';
        }
        $ousuario = null;
        unset($data_sqlsrv, $db_sqlsrv, $ousuario);
        return ($out);
    };




    /***********************************************************************
    *                      UPDATE USUARIO
    ***********************************************************************/
    function update_usuario(){
        $data_maria = new Db_mariadb(); // Nueva conexión a Mariadb
        $db_maria   = $data_maria->getConnection();
        $data       = json_decode(file_get_contents("php://input"));

        $ousuario   = new Usuario($db_maria);
        $ousuario->user_name     = test_input($data->username);
        $ousuario->user_nombre   = test_input($data->nombre);
        $ousuario->user_apellido = test_input($data->apellido);
        $ousuario->user_email    = test_input($data->email);
        $ousuario->user_activo   = test_input($data->estatus);
        $ousuario->user_role     = test_input($data->role);
        $ousuario->user_mod      = $_SESSION['username'];
        $db_maria->beginTransaction();
        $ousuario->update_usuario();
        if (!$ousuario->error){
            $ousuario->update_role();
        }
        

        if (!$ousuario->error){
            $db_maria->commit();
            $out = array();
            $out["response"] = 'success';
            $out["username"] = $ousuario->user_name;
            $out["role"]     = $ousuario->user_role;
        }else{
            $db_maria->rollback();
            $out["response"]   = 'fail';
            $out["error_nro"]  = $ousuario->error_nro;
            $out["error_msj"]  = $ousuario->error_msj;
            $out["error_tpo"]  = 'error';
        }
        $data_maria = $db_maria = $data = $ousuario = null;
        unset($data_maria, $db_maria, $data, $ousuario);
        return ($out);
    };
?>