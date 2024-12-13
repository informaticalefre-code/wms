<?php
    /*************************************************************************************
    * Clase Packing
    *
    * Metodos:
    * next_idpacking()
    * insertar_datos()
    * insert_packing_master()
    * insert_packing_detalle()
    /**************************************************************************************/
    declare(strict_types=1);
    // namespace Packing;

class Packing {
    // Conexión
    public  $conn;
    
    // Bultos
    public $bultos       = 0 ; // Indica la cantidad de bultos asociados a la tarea
    public $bulto_open   = 0 ; // Indica el nro de bulto abierto para insertar productos a embalar 

    // Para manejo de errores o validaciones
    public $error ; // Indica si se detectó un error o advertencia en algún método.
    public $error_nro;      // Nro de Error. Si es una excepción de usuario se coloca 45000
    public $error_msj;      // Mensaje de Error.
    public $error_file;     // Archivo del error.
    public $error_line;     // Linea del error.
    public $error_tpo;      // Tipo de error (2 valores): "warning" o "error"

    public $pack_idempresa;
    public $pack_idpacking;
    public $pack_fecha;
    public $pack_idpedido;
    public $pack_idpicking;
    public $pack_embalador;
    public $pack_status;
    public $pack_prioridad;
    public $pack_pista;
    public $pack_observacion;
    public $user_crea;
    public $user_mod;
    public $packing_detalle;   // Array con los productos de la tarea de packing-
    public $packing_bultos;    // Array con la descripción de cada bulto de la tarea de packing.
    public $packing_productos; // Array con la relación de productos que hay en cada bulto.

    /* Estos atributos están aquí pero debe crearse otra clase para los bultos */    
    public $pack_idbulto;
    public $pack_peso;
    public $pack_unidadpeso;

    /* Estos atributos son del Pedido pero los puse aqui por hacerlo rápido (EVALUAR)*/
    public $fecha_ped;
    public $cliente_ped;
    public $vendedor_ped;
    public $status_ped;
    public $nombre_cli;
    public $nombre_ven;

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
    function next_idpacking(){
        $out = null;
        $sql  =  "SELECT fnext_idpacking()" ;
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
        $this->conn->beginTransaction();   

        if (!$this->error){
            $this->insert_packing_master();
            if (!$this->error){
                $this->insert_packing_detalle();
            }
        }

        if ($this->error){
            $this->conn->rollback();
        }else{
            $this->conn->commit();
        }

        $out=[];
        if (!$this->error){
            $out["response"]       = "success";
            $out["texto"]          = "datos guardados con exito.";
            $out["pack_idpacking"] = $this->pack_idpedido;
        }else{
            $out["response"]   = "fail";
            $out["error_nro"]  = $this->error_nro;
            $out["error_msj"]  = $this->error_msj;        
            $out["error_file"] = $this->error_file;
            $out["error_line"] = $this->error_line;
            $out["error_tpo"]  = $this->error_tpo;
        }
        return ($out);
    }


    /****************************************************
    *            INSERT PACKING MASTER
    ******************************************************/
    function insert_packing_master(){
        $this->error = false;
        // Vamos con la tabla Packing
        $this->pack_idpacking = $this->next_idpacking();
        $data = array(
            'pack_idempresa'  => $this->pack_idempresa,
            'pack_idpacking'  => $this->pack_idpacking,
            'pack_idpicking'  => $this->pack_idpicking,
            'pack_idpedido'   => $this->pack_idpedido,
            'pack_prioridad'  => $this->pack_prioridad,
            'user_crea'       => $this->user_crea
        );

        $sql = "INSERT INTO vpacking (
            pack_idempresa, pack_idpacking, pack_idpicking, pack_idpedido, pack_prioridad, user_crea) VALUES (
            :pack_idempresa, :pack_idpacking, :pack_idpicking, :pack_idpedido, :pack_prioridad, :user_crea)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($data);
            if ($stmt->rowcount() != 1){
                $this->error     = true;
                $this->error_nro = '46000';
                $this->error_msj = 'No se insertaron datos en tpacking';
                $this->error_tpo = 'error';
            }
        } catch (PDOException $e) {
            $this->error_registro($e);
        } catch (Exception $e) {
            $this->error_registro($e);
        }
        unset($data,$sql,$stmt);
    }


    /****************************************************
    *            INSERT PACKING DETALLE
    ****************************************************/
    function insert_packing_detalle() {
        $this->error = false;
        // Ahora vamos con Packing_Detalle
        $sql = "INSERT INTO vpacking_detalle (
        pacd_idempresa, pacd_idpacking, pacd_idproducto, pacd_unidad, pacd_requerido, user_crea)
        VALUES (:pacd_idempresa, :pacd_idpacking, :pacd_idproducto, :pacd_unidad, 
        :pacd_requerido, :user_crea)";
        
        for ($i=0; $i<count($this->packing_detalle); $i++):
            $data = array(
                'pacd_idempresa'    => $this->pack_idempresa,
                'pacd_idpacking'    => $this->pack_idpacking,
                'pacd_idproducto'   => $this->packing_detalle[$i]["pacd_idproducto"],
                'pacd_unidad'       => $this->packing_detalle[$i]["pacd_unidad"],
                'pacd_requerido'    => $this->packing_detalle[$i]["pacd_requerido"],
                'user_crea'         => $this->user_crea
            );  

            $stmt = $this->conn->prepare($sql);
            try {
                $stmt->execute($data);
            } catch (PDOException $e) {
                $this->error_registro($e);
            } catch (Exception $e) {
                $this->error_registro($e);
            }
        endfor;
        $sql = $stmt = $data = null;
        unset($sql,$stmt,$data);
    }

    /****************************************************
    *            INSERT PACKING BULTO
    ****************************************************/
    function insert_packing_bulto($idbulto, $status=0, $peso=0, $unidad='Kg'){
        $this->error = false;
        $sql = "INSERT INTO vpacking_bultos (
            pack_idempresa, pack_idpacking, pack_idbulto, pack_status, pack_peso, pack_unidadpeso, user_crea)
            VALUES (:idempresa, :idpacking, :idbulto, :status, :peso, :unidad, :user_crea)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idempresa',$this->pack_idempresa);
        $stmt->bindParam(':idpacking',$this->pack_idpacking);
        $stmt->bindParam(':idbulto',$idbulto);
        $stmt->bindParam(':status',$status, PDO::PARAM_INT);
        $stmt->bindParam(':peso',$peso);
        $stmt->bindParam(':unidad',$unidad);
        $stmt->bindParam(':user_crea',$this->user_crea, PDO::PARAM_STR);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            $this->error_registro($e);
        } catch (Exception $e) {
            $this->error_registro($e);
        }
        unset($data,$sql,$stmt);
    }


    /****************************************************
    *            INSERT PACKING PRODUCTOS
    ****************************************************/
    function insert_packing_productos($idpacking, $idbulto, $idproducto, $cantidad, $username){
        $this->error = false;
        $sql = "INSERT INTO vpacking_productos (
            pacp_idempresa, pacp_idpacking, pacp_idbulto, pacp_idproducto, pacp_cantidad, user_crea)
            VALUES (1, :idpacking, :idbulto, :idproducto, :cantidad, :user_crea)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idpacking',$idpacking, PDO::PARAM_INT);
        $stmt->bindParam(':idbulto',$idbulto, PDO::PARAM_INT);
        $stmt->bindParam(':idproducto',$idproducto, PDO::PARAM_STR);
        $stmt->bindParam(':cantidad',$cantidad, PDO::PARAM_INT);
        $stmt->bindParam(':user_crea',$username, PDO::PARAM_STR);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            $this->error_registro($e);
        } catch (Exception $e) {
            $this->error_registro($e);
        }

        if ($this->error_nro == 23000){
            $this->error_msj = 'Elimine primero los productos de la caja y vuelva a insertarlos';
        }
        unset($sql,$stmt,$data);
        $idpacking = $idbulto = $idproducto = $cantidad = $username = null;
        unset($ipacking, $idbulto, $idproducto, $cantidad, $username);
    }

    /**************************************************************
    *                 UPDATE PACKING START
    * Es cuando el embalador selecciona una tarea de Packing. 
    * A partir de ese momento esa tarea comienza.
    **************************************************************/
    function update_packing_start(){
        $this->error = false;

        // Ahora vamos con Packing_Detalle
        $sql = "update vpacking
                SET pack_fecinicio = CURRENT_TIMESTAMP, pack_embalador = :embalador, user_mod = :user_mod
                WHERE pack_idempresa = :idempresa AND pack_idpacking = :idpacking";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idempresa',$this->pack_idempresa);
        $stmt->bindParam(':idpacking',$this->pack_idpacking);
        $stmt->bindParam(':embalador',$this->pack_embalador, PDO::PARAM_STR);
        $stmt->bindParam(':user_mod',$this->user_mod, PDO::PARAM_STR);

        try {
            $stmt->execute();
            if ($stmt->rowcount() <> 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se pudo iniciar la Tarea de Packing '.$this->pack_idpacking;
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
        $sql = $stmt = null;
        unset($sql, $stmt);
    }

    /**************************************************************
    *                 UPDATE CLOSE BULTO
    * Aquí se cierra el bulto... Cuando un bulto está cerrado
    * se permite imprimir su etiqueta.
    **************************************************************/
    function update_closebulto(){
        $this->error = false;

        // Ahora vamos con Packing_Detalle
        $sql = "UPDATE vpacking_bultos
                   SET pack_peso = :peso, pack_unidadpeso = :unidad, pack_status = :status, user_mod = :user_mod
                 WHERE pack_idempresa = :idempresa AND pack_idpacking = :idpacking AND pack_idbulto = :idbulto";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idempresa',$this->pack_idempresa,PDO::PARAM_INT);
        $stmt->bindParam(':idpacking',$this->pack_idpacking, PDO::PARAM_INT);
        $stmt->bindParam(':idbulto',$this->pack_idbulto, PDO::PARAM_INT);
        $stmt->bindParam(':peso',$this->pack_peso);
        $stmt->bindParam(':unidad',$this->pack_unidadpeso, PDO::PARAM_STR);
        $stmt->bindParam(':status',$this->pack_status, PDO::PARAM_INT);
        $stmt->bindParam(':user_mod',$this->user_mod, PDO::PARAM_STR);

        try {
            $stmt->execute();
            if ($stmt->rowcount() <> 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se pudo cerrar la Tarea de Packing '.$this->pack_idpacking;
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
    *                 OPEN BULTO
    * Se abre un bulto para poder agregar o eliminar productos 
    * de él.
    **************************************************************/
    function open_bulto($idbulto){
        $this->error = false;

        $sql = "UPDATE vpacking_bultos
                   SET pack_status = 0, user_mod = :user_mod
                 WHERE pack_idempresa = :idempresa AND pack_idpacking = :idpacking AND pack_idbulto = :idbulto";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idempresa',$this->pack_idempresa,PDO::PARAM_INT);
        $stmt->bindParam(':idpacking',$this->pack_idpacking, PDO::PARAM_INT);
        $stmt->bindParam(':idbulto',$idbulto, PDO::PARAM_INT);
        $stmt->bindParam(':user_mod',$this->user_mod, PDO::PARAM_STR);

        try {
            $stmt->execute();
            if ($stmt->rowcount() <> 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se pudo abrir el bulto '.$idbulto;
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
    *                 UPDATE PACKING MASTER
    * Permite modificar datos de la tabla maestra de Tareas de Packing
    ******************************************************************/
    function update_packing_master(){
        $this->error = false;

        $adatos = array(
            'idpacking'   => $this->pack_idpacking,
            'fecha'       => $this->pack_fecha,
            'embalador'   => $this->pack_embalador,
            'status'      => $this->pack_status,
            'prioridad'   => $this->pack_prioridad,
            'pista'       => $this->pack_pista,
            'observacion' => $this->pack_observacion,
            'user_mod'    => $this->user_mod
        );

        // Ahora vamos con Packing_Detalle
        $sql = "UPDATE vpacking
                SET pack_fecha = :fecha, pack_embalador = :embalador, pack_status = :status, 
                pack_prioridad = :prioridad,  pack_pista = :pista, 
                pack_observacion = :observacion, user_mod = :user_mod
                WHERE pack_idpacking = :idpacking";

        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($adatos);
            if ($stmt->rowcount() !== 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se actualizó la Tarea de Packing '.$adatos["idpacking"];
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
        unset($sql, $stmt, $adatos);
    }

    /**************************************************************
    *                 UPDATE PACKING DETALLE
    * Permite modificar datos de los productos requeridos en una 
    * tarea de packing
    **************************************************************/
    function update_packing_detalle($adatos){
        $this->error = false;

        // Ahora vamos con Picking_Detalle
        $sql = "UPDATE vpacking_detalle
                SET pacd_requerido = :requerido, pacd_cantidad = :cantidad, user_mod = :user_mod
                WHERE pacd_idpacking = :idpacking AND pacd_idproducto = :idproducto";

        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($adatos);
            if ($stmt->rowcount() !== 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se actualizó el registro para el producto '.$adatos["idproducto"].' en la Tarea de Picking '.$adatos["idpicking"];
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
        unset($sql, $stmt, $adatos);
    }
    

    /************************************************************
    *              UPDATE PACKING PRODUCTO
    * Ocurre cuando se indica la cantidad que se va anclar a una
    * tarea de packing.
    *************************************************************/
    function update_packing_producto($idproducto, $cantidad){
        $this->error = false;

        $sql = "UPDATE vpacking_detalle SET pacd_cantidad = IFNULL(pacd_cantidad,0) + :cantidad, user_mod = :user_mod
        WHERE pacd_idempresa = :idempresa AND pacd_idpacking = :idpacking AND pacd_idproducto=:idproducto";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':cantidad',$cantidad, PDO::PARAM_INT);
        $stmt->bindParam(':user_mod',$this->user_mod, PDO::PARAM_STR);
        $stmt->bindParam(':idempresa',$this->pack_idempresa);
        $stmt->bindParam(':idpacking',$this->pack_idpacking);
        $stmt->bindParam(':idproducto',$idproducto, PDO::PARAM_STR);

        try {
            $stmt->execute();
            if ($stmt->rowcount() !== 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se pudo iniciar la Tarea de Packing '.$this->pack_idpacking;
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }    
    }

    /**************************************************************
    *                   CARGA PEDIDOS MASTER
    **************************************************************/
    function carga_packing_master(){
        $sql = "select a.pack_idempresa, a.pack_idpacking, a.pack_fecha, a.pack_idpedido, a.pack_idpicking, a.pack_embalador,
                a.pack_status, a.pack_prioridad, a.pack_pista, a.pack_observacion, a.user_crea,
                b.fecha_ped, b.cliente_ped, b.vendedor_ped, b.status_ped,
                c.descripcion_cli as nombre_cli, d.nombre_ven
                FROM vpacking a
                INNER JOIN tbpedidos1 b ON b.numero_ped = a.pack_idpedido
                INNER JOIN TbClientes c ON c.codigo_cli = b.cliente_ped
                INNER JOIN TbVendedores d ON d.codigo_ven = b.vendedor_ped
                WHERE a.pack_idempresa = 1
                AND a.pack_idpacking = :idpacking";

        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idpacking',$this->pack_idpacking, PDO::PARAM_INT);
        $stmt->execute();
        $resp = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowcount() > 0):
            $this->pack_idempresa = $resp["pack_idempresa"];
            $this->pack_fecha     = $resp["pack_fecha"];
            $this->pack_idpedido  = $resp["pack_idpedido"];
            $this->pack_idpicking = $resp["pack_idpicking"];
            $this->pack_embalador = $resp["pack_embalador"];
            $this->pack_status    = $resp["pack_status"];
            $this->pack_prioridad = $resp["pack_prioridad"];
            $this->user_crea      = $resp["user_crea"];
            $this->pack_pista     = $resp["pack_pista"];
            $this->pack_observacion = $resp["pack_observacion"];
            $this->fecha_ped      = $resp["fecha_ped"];
            $this->cliente_ped    = $resp["cliente_ped"];
            $this->vendedor_ped   = $resp["vendedor_ped"];
            $this->status_ped     = $resp["status_ped"];
            $this->nombre_cli     = $resp["nombre_cli"];
            $this->nombre_ven     = $resp["nombre_ven"];
        else:
            $this->error     = true;
            $this->error_msj = "La tarea de Packing Nro. ".$this->pack_idpacking." no existe";
            $this->error_tpo = "error";
        endif;
        unset($sql,$stmt,$resp);
    }


    /**************************************************************
    *                 CARGA PACKING DETALLE
    **************************************************************/
    function carga_packing_detalle(){
        $sql  = "Select a.pacd_idempresa, a.pacd_idpacking, a.pacd_idproducto, a.pacd_requerido, a.pacd_cantidad, 
        b.nombre_pro, b.codigobarra_pro, b.unidad_pro, b.referencia_pro, b.peso_pro, b.bultooriginal_pro, b.empaqueoriginal_pro
        FROM vpacking_detalle a
        JOIN tbproductos b ON b.codigo_pro = a.pacd_idproducto
        WHERE a.pacd_idempresa = :idempresa AND a.pacd_idpacking = :idpacking
        order by a.pacd_idproducto";

        try {
            $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->bindparam(':idempresa',$this->pack_idempresa, PDO::PARAM_INT);
            $stmt->bindparam(':idpacking',$this->pack_idpacking, PDO::PARAM_INT);
            $stmt->execute();
            $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }

        if (!$this->error && $stmt->rowcount() > 0){
            $this->packing_detalle = $resp;
        }else{
            $this->error     = true;
            $this->error_nro = '45000';
            $this->error_msj = 'Error consultado detalle de tarea de Packing Nro.'.$this->pack_idpacking;
            $this->error_tpo = 'error';
        }

        unset($sql,$stmt,$resp);
    }

    /**************************************************************
    *                 CARGA PACKING BULTOS
    **************************************************************/
    function carga_packing_bultos($bulto){
        $this->bultos = 0;
        $this->bulto_open = 0;
        $sql = "Select a.pack_idbulto, a.pack_peso, a.pack_unidadpeso, a.pack_status 
        FROM vpacking_bultos a WHERE a.pack_idempresa = :idempresa AND a.pack_idpacking = :idpacking";

        if ($bulto == '<TODOS>'){
            $sql .= ' ORDER BY pack_idbulto ASC';
        }else{    
            $sql .= " and a.pack_idbulto = :bulto ORDER BY pack_idbulto DESC";
        }

        $stmt = $this->conn->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idempresa', $this->pack_idempresa, PDO::PARAM_INT);
        $stmt->bindparam(':idpacking', $this->pack_idpacking, PDO::PARAM_INT);
        if (($bulto !== '<TODOS>')){
            $stmt->bindparam(':bulto',$bulto, PDO::PARAM_INT);
        }

        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        if ($stmt->rowcount() > 0){
            $this->packing_bultos = $resp;
        }else{
            $this->packing_bultos = [];
        }

        // Buscamos si está abierto o no algún bulto.
        if ($bulto == '<TODOS>'){
            $this->bultos = count($this->packing_bultos); // Indica la cantidad de bultos asociados a la tarea
            for ($i=0; $i<$this->bultos; $i++):
                if ($this->packing_bultos[$i]["pack_status"] == 0){
                    $this->bulto_open = $this->packing_bultos[$i]["pack_idbulto"];
                    break; // Salimos del loop... Ya encontramos lo que queríamos
                }
            endfor;
        }
        unset($sql,$stmt,$resp,$bulto);
    }


    /**************************************************************
    *                 CARGA PACKING PRODUCTOS
    **************************************************************/
    function carga_packing_productos($bulto){
        $sql = "Select a.pacp_idbulto, a.pacp_idproducto, a.pacp_cantidad, b.nombre_pro
        FROM vpacking_productos a
        JOIN tbproductos b ON b.codigo_pro = a.pacp_idproducto 
       WHERE a.pacp_idempresa = :idempresa AND a.pacp_idpacking = :idpacking and a.pacp_idbulto = :bulto
       ORDER BY a.pacp_idbulto, a.pacp_idproducto";

        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idempresa',$this->pack_idempresa, PDO::PARAM_INT);
        $stmt->bindparam(':idpacking',$this->pack_idpacking, PDO::PARAM_INT);
        $stmt->bindparam(':bulto',$bulto, PDO::PARAM_INT);

        $this->packing_productos = [];

        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        if ($stmt->rowcount() > 0):
            $this->packing_productos = $resp;
        endif;
        unset($sql,$stmt,$resp,$bulto);
    }

    /**************************************************************
    *               DELETE PACKING DETALLE
    * Elimina 1 producto de la tarea de packing
    **************************************************************/
    function delete_packing_detalle($idproducto){
        $this->error = false;
        if (empty($this->pack_idempresa) || empty($this->pack_idpacking) || empty($idproducto)){
            $this->error      = true;
            $this->error_nro  = 45200;
            $this->error_msj  = 'No hay tarea de packing o código de producto para proceder con eliminación';
            $this->error_file = '';
            $this->error_line = '';
            $this->error_tpo = 'error';
            return;
        }

        // Ahora vamos con Packing_Detalle
        $sql = "DELETE FROM vpacking_detalle WHERE pacd_idpacking = :idpacking AND pacd_idproducto = :idproducto";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idpacking',$this->pack_idpacking);
        $stmt->bindParam(':idproducto',$idproducto,PDO::PARAM_STR);

        try {
            $stmt->execute();
            if ($stmt->rowcount() != 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se eliminó el producto '.$idproducto.' de la Tarea de Packing '.$this->pack_idpacking;
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
    }
    
    
    /**************************************************************
    *                 DELETE PACKING BOXPRODUCTO
    **************************************************************/
    function delete_packing_boxproducto($idbulto, $idproducto){
        $sql = "DELETE FROM vpacking_productos 
        WHERE pacp_idempresa = :idempresa AND pacp_idpacking = :idpacking
        AND pacp_idbulto = :idbulto AND pacp_idproducto = :idproducto";

        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idempresa',$this->pack_idempresa, PDO::PARAM_INT);
        $stmt->bindparam(':idpacking',$this->pack_idpacking, PDO::PARAM_INT);
        $stmt->bindparam(':idbulto',$idbulto, PDO::PARAM_INT);
        $stmt->bindparam(':idproducto',$idproducto, PDO::PARAM_STR);
        try {
            $stmt->execute();
            if ($stmt->rowcount() <> 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se pudo Eliminar el producto '.$idproducto. ' del bulto '.$idbulto;
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
    }

    /**************************************************************
    *                 DELETE PACKING BOX
    **************************************************************/
    function delete_packing_box($idbulto){
        $sql = "DELETE FROM vpacking_bultos
        WHERE pack_idempresa = :idempresa AND pack_idpacking = :idpacking
        AND pack_idbulto = :idbulto";

        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idempresa',$this->pack_idempresa, PDO::PARAM_INT);
        $stmt->bindparam(':idpacking',$this->pack_idpacking, PDO::PARAM_INT);
        $stmt->bindparam(':idbulto',$idbulto, PDO::PARAM_INT);

        try {
            $stmt->execute();
            if ($stmt->rowcount() !== 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se pudo Eliminar el bulto '.$idbulto;
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);        
        }

        if ($this->error_nro == 23000){
            $this->error_msj = 'Elimine primero los productos de la caja y vuelva a intentarlo';
        }
        $sql = $stmt = $data = null;
        unset($sql,$stmt,$data);
    }    


    /************************************************************************
    *                      CIERRA TAREA DE PACKING
    * Para acelerar el proceso de packing, cada vez que el usuario cierra un
    * bulto automáticamente abre otro. Pero si hay bultos abiertos no deja
    * cerrar la tarea, pero esta regla aplica solo a bultos abiertos que
    * tengan productos asociados a él.    
    *************************************************************************/
    function cierra_tarea_packing(){
        $this->error = false;

        // Borramos bultos vacíos
        $data = array(
            'status'      => 5,
            'observacion' => $this->pack_observacion,
            'user_mod'    => $this->user_mod,
            'idempresa'   => $this->pack_idempresa,
            'idpacking'   => $this->pack_idpacking
        );

        // Ahora vamos con Packing_Detalle
        $sql = "UPDATE vpacking SET pack_status = :status, pack_observacion = :observacion, user_mod = :user_mod
                WHERE pack_idempresa = :idempresa AND pack_idpacking = :idpacking";
    
        $stmt = $this->conn->prepare($sql);
        
        // Vamos con la actualización del Packing.
        try {
            $stmt->execute($data);
            if ($stmt->rowcount() <> 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se actualizó  la Tarea de Packing '.$this->pack_idpacking;
                $this->error_tpo = 'error';                
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
        unset($sql, $stmt, $data);
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