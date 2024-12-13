<?php
    // clases.php    
    // Aseguramos que cada parametro de cada función sea del mismo 
    // tipo definido. 
    declare(strict_types=1); 
    use Symfony\Component\Mailer\Mailer; 
    use Symfony\Component\Mailer\Transport\SendmailTransport; 
    use Symfony\Component\Mime\Email;
    
class Usuario {
    // Conexión
    private $conn;

    public $user_id;
    // Para manejo de errores o validaciones
    public $error = false;      // Indica si se detectó un error o advertencia en algún método.
    public $error_nro;  // Nro de Error. Si es una excepción de usuario se coloca 45000
    public $error_msj;  // Mensaje de Error.
    public $error_file; // Archivo del error.
    public $error_line; // Linea del error.
    public $error_tpo;  // Tipo de error (2 valores): "warning" o "error

    public $user_uuid;
    public $user_name;
    public $user_nombre;
    public $user_apellido;
    public $user_email;
    public $user_role;
    public $user_password;
    public $user_idempresa;
    public $user_token;
    public $user_tokenexp;
    public $user_activo;
    public $user_mod;

    
    /*************************************************************
    * CONSTRUCTOR
    **************************************************************/
    public function __construct($db){
        if (isset($db)){
            $this->conn = $db;
        }
    }

    /****************************************************
    *                 PASSWORD HASH
    * Se coloca en una función para tenerla en un solo
    * sitio y facilitar su mantenimiento o modificación.
    ****************************************************/
    private function password_hash($password){
        return (password_hash($password, PASSWORD_ARGON2I,['memory_cost' => 2 ** 12,'time_cost' => 10,'threads' => 20]));
    }

    /****************************************************
    *                 NEXT CLAVE PRIMARIA
    * El ID de usuario es unico sin importar la empresa
    ****************************************************/
    private function next_idusuario(){
        $out = null;
        if (is_null($this->user_id)){
            $sql  =  "SELECT IFNULL(MAX(user_id),0)+1 as next_id FROM tusuarios" ;
            $stmt =  $this->conn->prepare($sql);
            $stmt -> bindcolumn('next_id',$out,PDO::PARAM_INT);
            $stmt -> execute();
            $res  =  $stmt->fetch(PDO::FETCH_BOUND);
            $this->user_id = $out;
            unset($sql, $stmt, $res);
        }            
        return $out;
    }

    /****************************************************
    *            VERIFICA PASSWORD
    * Busca el username en la base de datos. Toma el
    * password y lo compara con el introducido
    * en el formulario.
    * Parametros:
    * 2. Username
    * 4. Password a verificar
    * Retorna: Json;
    ******************************************************/
    function verifica_password($password) {
        $out = false;
        $out = password_verify($password,$this->user_password);
        return ($out);
    }



    /****************************************************
    *            INSERT USUARIO MASTER
    ******************************************************/
    function insert_usuario_master(){
        $this->error = false ;
        $fecha_now = new DateTime('now',new DateTimeZone('America/Caracas'));
        $fecha_now->add(new DateInterval("PT24H"));
        $this->user_password  = $this->password_hash($this->user_password); /* Aplicamos hash al password */
        $this->user_token     = md5(rand().time()); // Token de validación de usuario.
        $this->user_tokenexp  = $fecha_now->format('Y/m/d H:i:s');
        $this->user_id        = $this->next_idusuario();
        
        $data = array(
            'user_id'        => $this->user_id,
            'user_name'      => $this->user_name,
            'user_nombre'    => $this->user_nombre,
            'user_apellido'  => $this->user_apellido,
            'user_email'     => $this->user_email,
            'user_password'  => $this->user_password,
            'user_token'     => $this->user_token,
            'user_tokenexp'  => $this->user_tokenexp,
            'user_activo'    => 0, // Inactivo por defecto.
            'user_tipo'      => 2, // Externo o usuario Web por defecto.
            'user_perfil'    => 'WEB',
            'user_admin'     => 0,
            'user_idempresa' => $this->user_idempresa,
            'user_crea'      => $_SESSION['username']
        );

        $sql = "INSERT INTO vusuarios (
            user_id, user_uuid, user_name, user_nombre, user_apellido,
            user_password, user_token, user_tokenexp,
            user_email,user_activo, user_tipo, 
            user_perfil, user_admin, user_idempresa, user_crea) VALUES (
            :user_id, UUID(), :user_name, :user_nombre, :user_apellido,
            :user_password, :user_token, :user_tokenexp,
            :user_email,:user_activo, :user_tipo,
            :user_perfil, :user_admin, :user_idempresa, :user_crea)";

        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($data);
            if ($stmt->rowcount() <> 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se pudo crear usuario';
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
        $data = $sql = $stmt = $fecha_now = null;
        unset($data,$sql,$stmt,$fecha_now);
    }

  

    /****************************************************
    *                 VALIDA USUARIO MASTER
    * Validamos que todos los campos que sabemos que no
    * permiten nulos tengan valores y otras validaciones
    ****************************************************/
    function valida_cuenta_usuario(){
        
        if (is_null($this->user_name) or empty($this->user_name)){
            $this->error = true;
            $this->error_nro  = "25101";
            $this->error_msj  = "Cuenta de usuario en blanco";
        }

        /* La cuenta de usuario no puede tener espacios en blanco */    
        if (strstr($this->user_name," ")){
            $this->error = true;
            $this->error_nro  = "25101";
            $this->error_msj = "Cuenta de usuario no pude tener espacios";
        }
        
        if (is_null($this->user_nombre) or empty($this->user_nombre)){
            $this->error = true;
            $this->error_nro  = "25101";            
            $this->error_msj = "Nombre en blanco";
        }

        if (is_null($this->user_apellido) or empty($this->user_apellido)){
            $this->error = true;
            $this->error_nro  = "25101";            
            $this->error_msj = "Apellido en blanco";
        }
        
        if (is_null($this->user_email) or empty($this->user_email)){
            $this->error = true;
            $this->error_nro  = "25101";
            $this->error_msj  = "Email en blanco";
        }
        
        $this->valida_clave_usuario();

        if ($this->error){
            $out["error_nro"]  = "20100";
            $out["error_file"] = "<sin especificar>";
            $out["error_line"] = "<sin especificar>";
            $out["error_tpo"]  = "error";
        }
    }


    /****************************************************
    *               VALIDA CLAVE USUARIO
    * Valida que la clave cumpla con las condiciones
    * requeridas en este sistema.
    *****************************************************/
    function valida_clave_usuario(){
        if (is_null($this->user_password) or empty($this->user_password)){
            $this->error = true;
            $this->error_msj  = "Password en blanco";
        }

        if (strlen($this->user_password)<5){
            $this->error = true;
            $this->error_msj  = "Password debe ser mínimo de 5 caracteres";
        }

        if (strlen($this->user_password)>15){
            $this->error = true;
            $this->error_msj  = "Password debe ser máximo de 15 caracteres";
        }

        if (strstr($this->user_password," ")){
            $this->error = true;
            $this->error_msj = "Password no pude tener espacios";
        }
    }    

    /****************************************************
    *            ENLACE RECUPERAR CLAVE
    * Verifica el correo enviado como parametro y si 
    * existe manda un correo con un enlace para cambiar
    * la contraseña.
    ******************************************************/
    function enlace_recuperar_clave($email){
        $this->error = false;
        $sql = "SELECT user_id, user_name, user_email, user_idempresa
                    FROM vusuarios 
                    WHERE user_activo = 1
                    AND user_email = :user_email";
        $stmt = $this->conn->prepare($sql);
        $stmt -> bindparam(':user_email', $email, PDO::PARAM_STR);
        $stmt -> bindColumn('user_id', $this->user_id);
        $stmt -> bindColumn('user_name', $this->user_name);
        $stmt -> bindColumn('user_email', $this->user_email);
        $stmt -> bindColumn('user_idempresa', $this->user_idempresa);
        $stmt -> execute();
        try {
            if ($stmt->rowcount() == 1){
                $stmt->fetch(PDO::FETCH_BOUND);
                // Aquí creamos el Token y la fecha de expiración de dicho Token
                $fecha_now = new DateTime('now',new DateTimeZone('America/Caracas'));
                $fecha_now->add(new DateInterval("PT24H"));
                $this->user_token     = md5(rand().time()); // Token de validación de usuario.
                $this->user_tokenexp  = $fecha_now->format('Y/m/d H:i:s');
                /* Ahora colcamos el token con su fecha de expiración en la base de datos */
                $sql = "UPDATE vusuarios SET user_token=:user_token, user_tokenexp=:user_tokenexp WHERE user_id=:user_id AND user_idempresa=:id_empresa";
                $stmt = $this->conn->prepare($sql);
                $stmt -> bindparam(':user_token', $this->user_token, PDO::PARAM_STR);
                $stmt -> bindparam(':user_tokenexp', $this->user_tokenexp, PDO::PARAM_STR);
                $stmt -> bindparam(':user_id', $this->user_id, PDO::PARAM_INT);
                $stmt -> bindparam(':id_empresa', $this->user_idempresa, PDO::PARAM_INT);
                $stmt -> execute();
                if ($stmt->rowcount() !== 1){
                    $this->error = true;
                    $this->error_nro = 40502;
                    $this->error_msj = 'Imposible generar token para enviar correo de enlace de recuperación de contraseña';
                } 
            }else{
                $this->error = true;
                $this->error_nro = 40600;
                $this->error_msj = 'El correo no se encuentra asignado a ningún usuario activo';
            }
        } catch (PDOException $e) {
            $this->error_registro($e);
        } catch (Exception $e) {
            $this->error_registro($e);
        }
        unset($sql,$stmt);
    }

    /**************************************************************
    *                         VALIDA_TOKEN
    * Valida el token de registro de usuario o el token
    * enviado para el cambio de contraseña
    *
    * Todos los token tienen una fecha de expiración.
    * Para validar el token la validación tiene que hacerse
    * antes de la fecha de expiración y que la cuenta esté 
    * desactivada (user_activo = 0).
    *
    * Primero se consulta y luego se hace el update. Esto porque 
    * necesitamos el valor user_idempresa para hacer de ella
    * nuevamente una variable GLOBAL. (MUY IMPORTANTE).
    *
    * Parametros:
    * 1. ptoken: El token enviado al correo.
    ***************************************************************/
    function valida_token($ptoken) {
        $out = false;
        $fecha_now = new DateTime('now',new DateTimeZone('America/Caracas'));
        $fecha = $fecha_now->format('Y/m/d H:i:s');
        $sql = 'select user_id, user_name, user_uuid from vusuarios where user_token = :user_token and user_tokenexp >= :fecha';

        $stmt = $this->conn->prepare($sql);
        $stmt -> bindparam(':user_token',$ptoken, PDO::PARAM_STR);
        $stmt -> bindparam(':fecha',$fecha, PDO::PARAM_STR);
        $stmt -> bindColumn('user_id', $this->user_id);
        $stmt -> bindColumn('user_name', $this->user_name);
        $stmt -> bindColumn('user_uuid', $this->user_uuid);
        $stmt -> execute();
        if ($stmt->rowcount() == 1){
            $stmt->fetch(PDO::FETCH_BOUND);
            $out = true;
        }                
        unset($fecha_now,$fecha,$sql,$stmt);
        return $out;
    }


    /****************************************************************************************
    *                         VALIDA_UUID
    * Todos los tienen un Identificador Unico Universal (Universally Unique Identifier),
    * conocido también como "UUID". Cada vez que se inicia sesión en el Sistema se crea
    * una variable de session llamada "USER_UUID".
    ***************************************************************/
    function valida_uuid($uuid) {
        $out = false;
        $sql = 'SELECT user_id, user_name FROM vusuarios WHERE user_uuid = :user_uuid';

        $stmt = $this->conn->prepare($sql);
        $stmt -> bindparam(':user_uuid',$uuid, PDO::PARAM_STR);
        $stmt -> bindColumn('user_id', $this->user_id);
        $stmt -> bindColumn('user_name', $this->user_name);
        $stmt -> execute();
        if ($stmt->rowcount() == 1){
            $stmt->fetch(PDO::FETCH_BOUND);
            $out = true;
        }                
        $sql = $stmt = $uuid = null;
        unset($sql, $stmt, $uuid);
        return $out;
    }

    /****************************************************************************************
    *                        VALIDA_USUARIO
    * Cada vez que se crea un usuario este está inactivo por defecto. Al correo del
    * usuario se envía un enlace con un token (que tiene un tiempo de vida limitado).
    * Cuando el usuario hace clic al enlace, el sistema valida ese token y si
    * el token es valido, entonces cambia el status del usuario a "valido" y lo
    * envía a la pagina "index.php". Esto lo hace la pagina user_dashboard.php
    ***************************************************************/
    function valida_usuario($token) {
        if ($this->valida_token($token)){
            $fecha_now = new DateTime('now',new DateTimeZone('America/Caracas'));
            $fecha = $fecha_now->format('Y/m/d H:i:s');
            $sql = "UPDATE vusuarios SET user_activo = 1,
                                         user_tokenexp = :fin_token,
                                         user_mod = :user_mod
                     WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);      
            $stmt -> bindparam(':fin_token', $fecha);
            $stmt -> bindparam(':user_mod', $this->user_name, PDO::PARAM_STR);
            $stmt -> bindparam(':user_id', $this->user_id, PDO::PARAM_INT);
            try {
                $stmt->execute();
                if ($stmt->rowcount() <> 1){
                    $this->error     = true;
                    $this->error_nro = '45000';
                    $this->error_msj = 'No se pudo validar usuario por su token';
                    $this->error_tpo = 'error';
                }
            }catch (PDOException $e) {
                $this->error_registro($e);
            }catch (Exception $e) {
                $this->error_registro($e);
            }
            $sql = $stmt = null;
            $fecha_now = $fecha = $sql = $stmt = $ppassword = null;
            unset($sql, $stmt);
            unset($fecha_now,$fecha,$sql,$stmt,$ppassword);
        }else{
            $this->error      = true;
            $this->error_nro  = '25301';
            $this->error_msj  = 'Enlace para cambiar contraseña no es valido o ha expirado';
            $this->error_file = '';
            $this->error_line = '';
        }
    }


    /****************************************************
    *            CAMBIA CLAVE DE USUARIO
    * Se envía una clave, se valida y si cumple con los 
    * requisitos se procede a actualizar la base de datos
    ******************************************************/
    function cambia_clave_usuario($ppassword){
        $this->error = false;
        $this->user_password = $ppassword;
        $this->valida_clave_usuario() ;
        /* Si no hay error procedemos a cambiarlo en la base de datos */
        if (!$this->error){
            $this->user_password = $this->password_hash($this->user_password); /* Aplicamos hash al password */
            $fecha_now = new DateTime('now',new DateTimeZone('America/Caracas'));
            $fecha = $fecha_now->format('Y/m/d H:i:s');
            $sql = "UPDATE vusuarios SET user_password = :user_password,
                                         user_tokenexp = :fin_token,
                                         user_mod = :user_mod,
                                         fec_mod = :fecha_mod
                     WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);      
            $stmt -> bindparam(':user_password', $this->user_password, PDO::PARAM_INT);
            $stmt -> bindparam(':fin_token', $fecha);
            $stmt -> bindparam(':user_mod', $this->user_name, PDO::PARAM_STR);
            $stmt -> bindparam(':fecha_mod', $fecha);
            $stmt -> bindparam(':user_id', $this->user_id, PDO::PARAM_INT);

            try {
                $stmt->execute();
                if ($stmt->rowcount() <> 1){
                    $this->error     = true;
                    $this->error_nro = '45000';
                    $this->error_msj = 'No se pudo cambiar la contraseña';
                    $this->error_tpo = 'error';
                }
            }catch (PDOException $e) {
                $this->error_registro($e);
            }catch (Exception $e) {
                $this->error_registro($e);
            }
            $sql = $stmt = null;
            $fecha_now = $fecha = $sql = $stmt = $ppassword = null;
            unset($sql, $stmt);
            unset($fecha_now,$fecha,$sql,$stmt,$ppassword);
        }
        if ($this->error){
            $this->user_password = null;
        }
    }

    /**************************************************************
    *                 CARGA DATOS
    **************************************************************/
    function carga_usuario($username){
        $sql  = "SELECT a.user_name, a.user_nombre, a.user_apellido, a.user_email, a.user_activo,a.user_password, b.usrr_role
                   FROM vusuarios a 
              LEFT JOIN tusuarios_roles b ON a.user_name = b.usrr_name
              WHERE a.user_name = :username";

        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':username',$username, PDO::PARAM_STR);
        if ($stmt->execute()):
            $resp = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($stmt->rowcount() == 1):
                $this->user_name     = $username;
                $this->user_nombre   = $resp["user_nombre"];
                $this->user_apellido = $resp["user_apellido"];
                $this->user_email    = $resp["user_email"];
                $this->user_activo   = $resp["user_activo"];
                $this->user_password = $resp["user_password"];
                $this->user_role     = $resp["usrr_role"];
            else:
                $this->error     = true;
                $this->error_msj = "El usuario ".$username." no existe o tiene más de 1 rol.";
                $this->error_tpo = "error";
                $this->error_nro = 20302;
            endif;            
        else:
            $error = $stmt->errorInfo();
            $this->error     = true;
            $this->error_nro = $error[1];
            $this->error_msj = $error[2];
            $this->error_tpo = 'error';
        endif;
    }


    /*****************************************************************
    *                    UPDATE USUARIO
    ******************************************************************/
    function update_usuario(){
        $adatos = array(
            'user_name'     => $this->user_name,
            'user_nombre'   => $this->user_nombre,
            'user_apellido' => $this->user_apellido,
            'user_email'    => $this->user_email,
            'user_activo'   => $this->user_activo,
            'user_mod'      => $this->user_mod
        );

        $sql = "UPDATE vusuarios
                SET user_nombre = :user_nombre, user_apellido = :user_apellido, user_email = :user_email,
                user_activo = :user_activo, user_mod = :user_mod
                WHERE user_name = :user_name";

        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($adatos);
            if ($stmt->rowcount() !== 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se actualizó el usuario '.$this->user_name;
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
        $sql = $stmt = $adatos = null;
        unset($sql, $stmt, $adatos);
    }


    /*****************************************************************
    *                      INSERT ROLE
    ******************************************************************/
    function insert_role(){
        $sql = "INSERT INTO tusuarios_roles (usrr_name, usrr_role, user_crea) VALUES ( :usrr_name, :usrr_role, :user_crea)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindparam(':usrr_name',$this->user_name, PDO::PARAM_STR);        
        $stmt->bindparam(':usrr_role',$this->user_role, PDO::PARAM_STR);
        $stmt->bindparam(':user_crea',$_SESSION["username"], PDO::PARAM_STR);

        try {
            $stmt->execute();
            if ($stmt->rowcount() <> 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se guardo el rol para el usuario '.$this->user_name;
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
        unset($sql, $stmt);
    }

    /*****************************************************************
    *                      UPDATE USUARIO
    ******************************************************************/
    function update_role(){
        $sql = "UPDATE tusuarios_roles
                SET usrr_role = :usrr_role, user_mod = :user_mod
                WHERE usrr_name = :usrr_name";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindparam(':usrr_role',$this->user_role, PDO::PARAM_STR);
        $stmt->bindparam(':user_mod',$this->user_mod, PDO::PARAM_STR);
        $stmt->bindparam(':usrr_name',$this->user_name, PDO::PARAM_STR);

        try {
            $stmt->execute();
            if ($stmt->rowcount() > 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se actualizó el rol para el usuario '.$this->user_name;
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
        unset($sql, $stmt);
    }

    /**************************************************************
    *               ERROR REGISTRO
    **************************************************************/
    private function error_registro($oerror){
        $this->error      = true;
        $this->error_nro  = $oerror->getCode();
        $this->error_msj  = $oerror->getMessage();
        $this->error_file = $oerror->getfile();
        $this->error_line = $oerror->getLine();
        $this->error_tpo = 'error';
        unset($oerror);
    }
}
?>