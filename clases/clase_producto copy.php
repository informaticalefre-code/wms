<?php
    declare(strict_types=1);
    // namespace Pedido;

/*******************************************************************************************
 *  Clase Productos
 ********************************************************************************************/
class Producto {
    // Conexión
    private $conn;

    // Para manejo de errores o validaciones
    public $error ; // Indica si se detectó un error o advertencia en algún método.
    public $error_nro;      // Nro de Error. Si es una excepción de usuario se coloca 45000
    public $error_msj;      // Mensaje de Error.
    public $error_file;     // Archivo del error.
    public $error_line;     // Linea del error.
    public $error_tpo;      // Tipo de error (2 valores): "warning" o "error"

    public $codigo_pro;
    public $nombre_pro;     
    public $unidad_pro;
    public $codigobarra_pro;
    public $existencia_pro;
    public $EmpaqueOriginal_Pro;
    public $BultoOriginal_Pro;
    public $ubicacion_pro;
    public $referencia_pro;
    public $inactivo_pro;

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

    public function __destruct(){
        if (isset($this->conn)){
            $this->conn = null;
        }
    }

    /**************************************************************
    *                   CARGA DATOS     
    **************************************************************/
    function carga_datos($codigo_pro){
        $this->codigo_pro = $codigo_pro;
        $this->carga_productos_master(); 
    }

    /**************************************************************
    *                   CARGA PRODUCTOS MASTER
    **************************************************************/
    function carga_productos_master(){
        $this->error = false;
        $sql = "select codigo_pro, nombre_pro, unidad_pro, referencia_pro, existencia_pro, inactivo_pro, ubicacion_pro, codigobarra_pro, BultoOriginal_Pro, EmpaqueOriginal_Pro
                from TbProductos
                WHERE codigo_pro = :codigo_pro";
        
        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':codigo_pro',$this->codigo_pro, PDO::PARAM_INT);
        if ($stmt->execute()):
            $resp = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($stmt->rowcount() > 0):
                $this->codigo_pro          = $resp["codigo_pro"];
                $this->nombre_pro          = $resp["nombre_pro"];
                $this->unidad_pro          = $resp["unidad_pro"];
                $this->referencia_pro      = $resp["referencia_pro"];
                $this->existencia_pro      = $resp["existencia_pro"];
                $this->inactivo_pro        = $resp["inactivo_pro"];
                $this->ubicacion_pro       = $resp["ubicacion_pro"];
                $this->codigobarra_pro     = $resp["codigobarra_pro"];
                $this->BultoOriginal_Pro   = $resp["BultoOriginal_Pro"];
                $this->EmpaqueOriginal_Pro = $resp["EmpaqueOriginal_Pro"];
            else:
                $this->error     = true;
                $this->error_msj = "El Producto ".$this->codigo_pro." no existe";
                $this->error_tpo = "error";
            endif;            
        else:
            $error = $stmt->errorInfo();
            $this->error     = true;
            $this->error_nro = $error[1];
            $this->error_msj = $error[2];
            $this->error_tpo = 'error';
        endif;
        unset($sql,$stmt,$resp);
    }


    /**************************************************************
    *                   UPDATE DATOS PRODUCTOS
    * Ejecuta un update pero previamente hace las validaciones
    **************************************************************/
    function update_datos_producto(){
        $this->error = false;
        if (isset($this->codigo_pro) && !empty($this->codigobarra_pro)){
            if (!$this->valida_codigobarra($this->codigo_pro, $this->codigobarra_pro)){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'Código de barra '.$this->codigobarra_pro.' ya existe para otro producto';
                $this->error_tpo = 'error';
            }
        }
        if (!$this->error){
            $this->update_producto();
        }
    }

    /**************************************************************
    *                   UPDATE PRODUCTO
    * Hace update sin validación alguna. Para trabajar con
    * validaciones usar Update_Datos_Productos()
    **************************************************************/
    private function update_producto(){
        $this->error = false;

        // Ahora vamos con Picking_Detalle
        $sql = "UPDATE tbproductos SET ubicacion_pro =:ubicacion, codigobarra_pro = :codigobarra WHERE codigo_pro = :idproducto";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idproducto',$this->codigo_pro,PDO::PARAM_STR);
        $stmt->bindParam(':codigobarra',$this->codigobarra_pro,PDO::PARAM_STR);
        $stmt->bindParam(':ubicacion',$this->ubicacion_pro,PDO::PARAM_STR);
        
        try {
            $stmt->execute();
            if ($stmt->rowcount() !== 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se pudo actualizar el producto '.$this->codigo_pro;
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error      = true;
            $this->error_nro  = $e->getCode();
            $this->error_msj  = $e->getMessage();
            $this->error_file = $e->getfile();
            $this->error_line = $e->getLine();
            $this->error_tpo = 'error';
        }catch (Exception $e) {
            $this->error      = true;
            $this->error_nro  = $e->getCode();
            $this->error_msj  = $e->getMessage();
            $this->error_file = $e->getfile();
            $this->error_line = $e->getLine();
            $this->error_tpo = 'error';
        }
        unset($sql, $stmt, $data);
    }

    /**************************************************************
    *                   VALIDA CODIGOBARRA
    * Se han detectado productos con codigos de barra iguales.
    * No podemos validarlo por base de datos aún. Se hará por el 
    * sistema WMS Lefre.
    * Parametro: 
    * 1. Id Producto
    * 2. Codigobarra
    *
    * Return:
    * 1. true  -> Existe otro producto con el mismo codigo de barra
    * 2. false -> No existe otro producto con el mismo codigo de
                  barra.
    **************************************************************/
    function valida_codigobarra($idproducto, $codigobarra){
        
        $valida    = true;
        $registros = 0;
        $sql = "SELECT count(codigo_pro) FROM tbproductos WHERE codigobarra_pro = :codigobarra AND codigo_pro <> :idproducto";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idproducto',$idproducto,PDO::PARAM_STR);
        $stmt->bindParam(':codigobarra',$codigobarra,PDO::PARAM_STR);
        $stmt->bindColumn(1,$registros);

        try {
            $stmt->execute();
            $stmt->fetch(PDO::FETCH_OBJ);
        }catch (PDOException $e) {
            $this->error      = true;
            $this->error_nro  = $e->getCode();
            $this->error_msj  = $e->getMessage();
            $this->error_file = $e->getfile();
            $this->error_line = $e->getLine();
            $this->error_tpo = 'error';
        }catch (Exception $e) {
            $this->error      = true;
            $this->error_nro  = $e->getCode();
            $this->error_msj  = $e->getMessage();
            $this->error_file = $e->getfile();
            $this->error_line = $e->getLine();
            $this->error_tpo = 'error';
        }

        if ($registros > 0){
            $valida = false;
        }
        $registros = null;
        unset($sql, $stmt, $registros);
        return ($valida);
    }    
}
?>
