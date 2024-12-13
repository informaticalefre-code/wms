<?php
    /*************************************************************************************
    * Clase Inventario
    *
    /**************************************************************************************/
    declare(strict_types=1);

class Inventario {
    // Conexión
    public $conn;

    // Para manejo de errores o validaciones
    public $error ;     // Indica si se detectó un error o advertencia en algún método.
    public $error_nro;  // Nro de Error. Si es una excepción de usuario se coloca 45000
    public $error_msj;  // Mensaje de Error.
    public $error_file; // Archivo del error.
    public $error_line; // Linea del error.
    public $error_tpo;  // Tipo de error (2 valores): "warning" o "error"

    public $inve_id;
    public $inve_observacion;
    public $user_crea;
    public $contadores; // Arreglo con los nombre de los contadores.
    public $invd_conteo;
    public $invd_id;
    public $invd_idproducto;
   

    /******************************************************************************
    *  Constructor
    *  Parametros:
    *  1. Conexión a base de datos:
    *********************************************************************************/   
    // Db connection
    public function __construct($db){
        if (isset($db)){
            $this->conn = $db;
        }
    }

    /****************************************************
    *              NEXT CLAVE PRIMARIA
    * El ID de usuario es unico sin importar la empresa
    ****************************************************/
    function next_idinventario(){
        $out  = null;
        $sql  = "SELECT fnext_idinventario()" ;
        $stmt = $this->conn->prepare($sql);
        $stmt -> bindcolumn(1,$out,PDO::PARAM_INT);
        $stmt -> execute();
        $stmt->fetch(PDO::FETCH_BOUND);
        unset($sql,$stmt);
        return ($out);
    }

    /****************************************************
    *                 INSERT DATOS
    ****************************************************/
    function insertar_datos(){
        $this->error = false ;

        $this->insert_inventario_master();
        if (!$this->error){
            $this->insert_inventario_contadores();
        }
        if (!$this->error){
            $this->insert_inventario_detalle();
        }
    }

    /****************************************************
    *            INSERT INVENTARIO MASTER
    ******************************************************/
    private function insert_inventario_master(){
        // Vamos con la tabla Inventario
        $this->inve_id = $this->next_idinventario();

        $sql = "INSERT INTO vinventarios (
            inve_id, inve_observacion, user_crea) VALUES (
            :inve_id, :inve_observacion, :user_crea)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':inve_id',$this->inve_id, PDO::PARAM_INT);
        $stmt->bindParam(':inve_observacion',$this->inve_observacion, PDO::PARAM_STR);
        $stmt->bindParam(':user_crea',$this->user_crea, PDO::PARAM_STR);

        try {
            $stmt->execute();
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
        
        unset($data,$sql,$stmt);
    }


    /****************************************************
    *            INSERT INVENTARIO DETALLE
    ****************************************************/
    function insert_inventario_detalle() {
        // Ahora vamos con Picking_Detalle
        $sql = "INSERT INTO vinventarios_detalle (invd_id, invd_idproducto, invd_nombre, invd_unidad, invd_existencia, invd_ubicacion, user_crea)
                SELECT :invd_id, codigo_pro, nombre_pro, unidad_pro, existencia_pro, ubicacion_pro, :user_crea 
                FROM vproductos WHERE inactivo_pro = 0 ORDER BY codigo_pro";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':invd_id',$this->inve_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_crea',$this->user_crea, PDO::PARAM_STR);

        try {
            $stmt->execute();
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
        unset($sql,$stmt,$data);
    }

    
    /****************************************************
    *            INSERT INVENTARIO CONTADORES
    ****************************************************/
    function insert_inventario_contadores(){
        // Ahora vamos con Picking_Detalle
        $sql = "INSERT INTO tinventarios_personas (invp_id, invp_username)
            VALUES (:invp_id, :invp_username)";

        for ($i=0; $i<count($this->contadores); $i++):
            $stmt = $this->conn->prepare($sql);
            $data = array(
                'invp_id'       => $this->inve_id,
                'invp_username' => $this->contadores[$i],
            );  

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute($data);
            }catch (PDOException $e) {
                $this->error_registro($e);
            }catch (Exception $e) {
                $this->error_registro($e);
            }
        endfor;
        unset($sql,$stmt,$data);
    }
    

    /**************************************************************
    *                   UPDATE CONTEO
    * Actualiza la cantidad cuando se termina de hacer un conte
    * de productos en la toma de inventario
    **************************************************************/
    function update_conteo($tpoconteo){
        $this->error = false;
        $sql = $stmt = null;
        if ($tpoconteo == 'conteo1'){
            $sql = 'UPDATE vinventarios_detalle SET invd_conteo1 = :conteo, user_mod = :username1 WHERE invd_id = :id AND invd_idproducto = :idproducto AND invd_username1 = :username';
        }elseif ($tpoconteo == 'conteo2'){
            $sql = 'UPDATE vinventarios_detalle SET invd_conteo2 = :conteo, user_mod = :username1 WHERE invd_id = :id AND invd_idproducto = :idproducto AND invd_username2 = :username';
        }elseif ($tpoconteo == 'conteo3'){
            $sql = 'UPDATE vinventarios_detalle SET invd_conteo3 = :conteo, user_mod = :username1 WHERE invd_id = :id AND invd_idproducto = :idproducto AND invd_username3 = :username';
        }else{
            $this->error     = true;
            $this->error_nro = '45000';
            $this->error_msj = 'No se puedo determinar el tipo de conteo para actualizar';
            $this->error_tpo = 'error';
        }

        if (!$this->error){
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':conteo',$this->invd_conteo, PDO::PARAM_INT);
            $stmt->bindParam(':username1',$_SESSION['username'], PDO::PARAM_STR);
            $stmt->bindParam(':id',$this->invd_id, PDO::PARAM_INT);
            $stmt->bindParam(':idproducto',$this->invd_idproducto, PDO::PARAM_STR);
            $stmt->bindParam(':username',$_SESSION['username'], PDO::PARAM_STR);

            try {
                $stmt->execute();
                if ($stmt->rowcount() <> 1){
                    $this->error     = true;
                    $this->error_nro = '45000';
                    $this->error_msj = 'No se pudo actualizar el conteo para el producto '.$this->invd_idproducto;
                    $this->error_tpo = 'error';
                }
            }catch (PDOException $e) {
                $this->error_registro($e);
            }catch (Exception $e) {
                $this->error_registro($e);
            }
        }
        unset($sql, $stmt);
    }


    /**************************************************************
    *                    UPDATE DETALLE
    * Actualiza datos de la tabla de "Inventario_Detalle"
    * No todos. Solo los que podría modificar el usuario. Los datos
    * de esta tabla son generados automáticamente al crear un 
    * nuevo proceso de inventario y debe ser una copia de la tabla
    * productos al momento de iniciarse el proceso.
    **************************************************************/
    function update_detalle($odatos){
        $this->error = false;

        $datos = (array) $odatos;
        $sql = "UPDATE vinventarios_detalle 
        SET invd_conteo1 = :frm_conteo1, invd_username1 = :frm_username1,
        invd_conteo2 = :frm_conteo2, invd_username2 = :frm_username2,
        invd_conteo3 = :frm_conteo3, invd_username3 = :frm_username3,
        user_mod = '".$_SESSION["username"]."' WHERE invd_id = :frm_idinventario
        AND invd_idproducto = :frm_idproducto";

        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($datos);
            if ($stmt->rowcount() <> 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se pudo actualizar los datos para el producto '.$odatos->idproducto;
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