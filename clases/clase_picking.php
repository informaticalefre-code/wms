<?php
    /*************************************************************************************
    * Clase Picking
    *
    * Metodos:
    * lista_tareas_picking(filtro)
    * html_picking_card()
    * next_idpicking()
    * insertar_datos()
    * insert_picking_master()
    * insert_picking_detalle()
    * insert_picking_bins()
    * carga_datos()
    * carga_picking_master()
    * carga_picking_bins()
    * carga_picking_detalle()
    * update_picking_producto($idproducto, $cantidad,$ubicacion)
    * update_picking_verifica($idproducto, $cantverif)
    * update_picking_master();
    * update_picking_detalle($adatos);
    * elimina_detalle_producto($idproducto)
    * elimina_picking_bins()
    * consolida_tarea_picking(){
    * cierra_tarea_picking()
    /**************************************************************************************/
    declare(strict_types=1);
    // namespace Picking;

class Picking {
    // Conexión
    public $conn;

    // Para manejo de errores o validaciones
    public $error ;     // Indica si se detectó un error o advertencia en algún método.
    public $error_nro;  // Nro de Error. Si es una excepción de usuario se coloca 45000
    public $error_msj;  // Mensaje de Error.
    public $error_file; // Archivo del error.
    public $error_line; // Linea del error.
    public $error_tpo;  // Tipo de error (2 valores): "warning" o "error"

    public $pick_idpicking;
    public $pick_idempresa;
    public $pick_fecha;
    public $pick_idpedido;
    public $pick_preparador;
    public $pick_status;
    public $pick_prioridad  ;
    public $pick_pista;
    public $pick_observacion;
    public $pick_userverif;
    public $pick_fecverif;
    public $fecha_ped;
    public $cliente_ped;
    public $vendedor_ped;
    public $status_ped;
    public $nombre_cli;
    public $nombre_ven;
    public $user_crea;
    public $user_mod;
    
    public $picking_detalle; // Array con los productos del pedido al momento que se crea la tarea.
    public $picking_bins;     // Array con las distintas cestas o paletas en las que se consolida el pedido.
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

    /******************************************************
    *                 lista tareas de picking
    * Lista de tareas de picking
    *******************************************************/
    function lista_tareas_picking($filtro){
        $sql="SELECT a.pick_idpicking, date_format(a.pick_fecha,'%d/%m/%Y %H:%i') as pick_fecha, a.pick_idpedido, a.pick_prioridad,
        date_format(b.fecha_ped,'%d/%m/%Y') as fecha_ped, b.cliente_ped, b.vendedor_ped, b.status_ped,
        c.descripcion_cli as nombre_cli, d.nombre_ven
        FROM vpicking a
        INNER JOIN tbpedidos1 b ON a.pick_idpedido = b.numero_ped
        INNER JOIN TbClientes c ON b.cliente_ped = c.codigo_cli
        INNER JOIN TbVendedores d ON b.vendedor_ped = d.codigo_ven";
        
        if (isset($filtro)){
            $sql .= " ".$filtro." ORDER BY a.pick_fecha";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        unset($sql);
        return $stmt;
    }

    /******************************************************
    *                 tareas_picking_card
    *******************************************************/
    function html_picking_card(){
        $out  = '';
        $out .= '<div class="picking-card" id="'.$this->pick_idpicking.'">';
        $out .= '<section class="picking-card-header text-white">';
        $out .= '<p class="mb-0">'.$this->pick_idpicking.'</p>';
        $out .= '<button id="picking_seleccionar" onclick="picking_start('.$this->pick_idpicking.')" type="button" class="btn"><i class="icon-plus-circle"></i></button>';
        $out .= '</section>';
        $out .= '<section class="picking-card-body">';
        $out .= '<div class="d-flex flex-row justify-content-between">';
        $out .= '<span style="font-size:100%;" class="badge bg-primary">'.$this->pick_idpedido.'</span>';
        if ($this->pick_prioridad == 1){
            $out .= '<span style="font-size:100%;" class="badge rounded-pill bg-danger">Urgente</span>';
        }
        $out .= '<p class="mb-0">'.$this->fecha_ped.'</p>';
        $out .= '</div>';
        $out .= '<p class="mb-0">'.strtolower($this->nombre_cli).'</p>';
        $out .= '<p class="mb-0">'.strtolower($this->nombre_ven).'</p>';
        $out .= '</section>';
        $out .= '</div>';
        return $out;
    }


    /****************************************************
    *              NEXT CLAVE PRIMARIA
    * El ID de usuario es unico sin importar la empresa
    ****************************************************/
    function next_idpicking(){
        $out = null;
        $sql  =  "SELECT fnext_idpicking()" ;
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

        if (!$this->error){
            $this->insert_picking_master();
            if (!$this->error){
                $this->insert_picking_detalle();
            }
        }

    }

    /****************************************************
    *            INSERT PICKING MASTER
    ******************************************************/
    private function insert_picking_master(){
        // Vamos con la tabla Picking
        $this->pick_idpicking = $this->next_idpicking();
        $data = array(
            'pick_idempresa'  => $this->pick_idempresa,
            'pick_idpicking'  => $this->pick_idpicking,
            'pick_idpedido'   => $this->pick_idpedido,
            'pick_preparador' => $this->pick_preparador,
            'pick_prioridad'  => $this->pick_prioridad,
            'user_crea'       => $this->user_crea
        );

        $sql = "INSERT INTO vpicking (
            pick_idempresa, pick_idpicking, pick_idpedido,
            pick_preparador, pick_prioridad, user_crea) VALUES (
            :pick_idempresa, :pick_idpicking, :pick_idpedido,
            :pick_preparador, :pick_prioridad, :user_crea)";

        // $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        // $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($data);
            if ($stmt->rowcount() != 1){
                $this->error     = true;
                $this->error_nro = '46000';
                $this->error_msj = 'No se insertaron datos en tpicking';
                $this->error_tpo = 'error';
            }
            }catch (PDOException $e) {
                $this->error_registro($e);
            }catch (Exception $e) {
                $this->error_registro($e);
            }
        unset($data,$sql,$stmt);
    }


    /****************************************************
    *            INSERT PICKING DETALLE
    ****************************************************/
    function insert_picking_detalle() {
        // Ahora vamos con Picking_Detalle
        $sql = "INSERT INTO vpicking_detalle (
        picd_idempresa, picd_idpicking, picd_idproducto, picd_unidad, picd_idalmacen,
        picd_ubicacion, picd_requerido, user_crea)
        VALUES (:picd_idempresa, :picd_idpicking, :picd_idproducto, :picd_unidad, :picd_idalmacen,
        :picd_ubicacion, :picd_requerido, :user_crea)";

        for ($i=0; $i<count($this->picking_detalle); $i++):
            $stmt = $this->conn->prepare($sql);
            $data = array(
                'picd_idempresa'    => $this->pick_idempresa,
                'picd_idpicking'    => $this->pick_idpicking,
                'picd_idproducto'   => $this->picking_detalle[$i]["picd_idproducto"],
                'picd_unidad'       => $this->picking_detalle[$i]["picd_unidad"],
                'picd_idalmacen'    => $this->picking_detalle[$i]["picd_idalmacen"],
                'picd_ubicacion'    => $this->picking_detalle[$i]["picd_ubicacion"],
                'picd_requerido'    => $this->picking_detalle[$i]["picd_requerido"],
                'user_crea'         => $this->user_crea
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

    /****************************************************
    *            INSERT PICKING BINS
    * Eliminamos primero cualquier dato en la tabla 
    * relacionado con la tarea de picking y luego 
    * lo insertamos
    ****************************************************/
    private function insert_picking_bins() {
        $this->elimina_picking_bins();

        $sql = "INSERT INTO vpicking_bins (
        picc_idempresa, picc_idpicking, picc_bin, user_crea)
        VALUES (:picc_idempresa, :picc_idpicking, :picc_bin, :user_crea)";

        for ($i=0; $i<count($this->picking_bins); $i++):
            $stmt = $this->conn->prepare($sql);
            $data = array(
            'picc_idempresa' => $this->picking_bins[$i]["picc_idempresa"],
            'picc_idpicking' => $this->picking_bins[$i]["picc_idpicking"],
            'picc_bin'       => $this->picking_bins[$i]["picc_bin"],
            'user_crea'      => $this->picking_bins[$i]["user_crea"]
            );  

            try {
                $stmt->execute($data);
            } catch (PDOException $e) {
                $this->error_registro($e);
            } catch (Exception $e) {
                $this->error_registro($e);
            }
        endfor;
    unset($sql,$stmt,$data);
    }    


    /**************************************************************
    *                   CARGA DATOS     
    **************************************************************/
    function carga_datos(){
        $this->carga_picking_master(); 
        if (!$this->error) {
            $this->carga_picking_bins();
        }
        if (!$this->error) {
            $this->carga_picking_detalle();
        }
    }


    /**************************************************************
    *                   CARGA PEDIDOS MASTER
    **************************************************************/
    function carga_picking_master(){
        $sql = "select a.pick_idempresa, a.pick_idpicking, a.pick_fecha, a.pick_idpedido, a.pick_preparador, a.pick_userverif,
        a.pick_status, a.pick_prioridad, a.pick_pista, a.pick_observacion, a.pick_userverif, a.pick_fecverif,
        b.fecha_ped, b.cliente_ped, b.vendedor_ped, b.status_ped, c.descripcion_cli as nombre_cli, d.nombre_ven
        FROM vpicking a
        INNER JOIN tbpedidos1 b ON a.pick_idpedido = b.numero_ped
        INNER JOIN TbClientes c ON b.cliente_ped = c.codigo_cli
        INNER JOIN TbVendedores d ON b.vendedor_ped = d.codigo_ven
        WHERE a.pick_idempresa = 1
        AND a.pick_idpicking = :idpicking";

        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idpicking',$this->pick_idpicking, PDO::PARAM_INT);
        $stmt->execute();
        $resp = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowcount() > 0):
            $this->pick_idempresa   = $resp["pick_idempresa"];
            $this->pick_fecha       = $resp["pick_fecha"];
            $this->pick_idpedido    = $resp["pick_idpedido"];
            $this->pick_preparador  = $resp["pick_preparador"];
            $this->pick_userverif   = $resp["pick_userverif"];
            $this->pick_status      = $resp["pick_status"];
            $this->pick_prioridad   = $resp["pick_prioridad"];
            $this->pick_pista       = $resp["pick_pista"];
            $this->pick_observacion = $resp["pick_observacion"];
            $this->pick_userverif   = $resp["pick_userverif"];
            $this->pick_fecverif    = $resp["pick_fecverif"];
            $this->fecha_ped        = $resp["fecha_ped"];
            $this->cliente_ped      = $resp["cliente_ped"];
            $this->vendedor_ped     = $resp["vendedor_ped"];
            $this->status_ped       = $resp["status_ped"];
            $this->nombre_cli       = $resp["nombre_cli"];
            $this->nombre_ven       = $resp["nombre_ven"];
        else:
            $this->error     = true;
            $this->error_msj = "La tarea de Picking Nro. ".$this->pick_idpicking." no existe";
            $this->error_tpo = "error";
        endif;
        unset($sql,$stmt,$resp);
    }

    /**************************************************************
    *                 CARGA PICKING BINS
    **************************************************************/
    function carga_picking_bins(){
        $sql  = "Select a.picc_idempresa, a.picc_idpicking, a.picc_bin FROM vpicking_bins a WHERE a.picc_idpicking = :idpicking ORDER BY a.picc_bin";
    
        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idpicking',$this->pick_idpicking, PDO::PARAM_INT);
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        $this->picking_bins = $resp;
        unset($sql,$stmt,$resp);
    }

    /**************************************************************
    *                 CARGA PICKING DETALLE
    **************************************************************/
    function carga_picking_detalle(){
        $sql  = "Select a.picd_idempresa, a.picd_idpicking, a.picd_idproducto, a.picd_unidad, a.picd_idalmacen,
        a.picd_ubicacion, a.picd_requerido, a.picd_cantidad, a.picd_cantverif, b.nombre_pro
        FROM vpicking_detalle a
        LEFT JOIN tbproductos b ON b.codigo_pro = a.picd_idproducto
        WHERE a.picd_idempresa = :idempresa AND a.picd_idpicking = :idpicking
        ORDER BY a.picd_idproducto";        
    
        $stmt = $this->conn->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idempresa', $this->pick_idempresa, PDO::PARAM_INT);
        $stmt->bindparam(':idpicking', $this->pick_idpicking, PDO::PARAM_INT);
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        if ($stmt->rowcount() > 0):
            $this->picking_detalle = $resp;
        endif;
        unset($sql,$stmt,$resp);
    }

    /**************************************************************
    *                 UPDATE PICKING PRODUCTO
    * Ocurre cuando se indica la cantidad que se va anclar a una
    * tarea de picking.
    **************************************************************/
    function update_picking_producto($idproducto, $cantidad,$ubicacion){
        $this->error = false;

        // Ahora vamos con Picking_Detalle
        $sql = "Update vpicking_detalle 
                SET picd_cantidad = :cantidad, picd_ubicacion=:ubicacion, user_mod = :user_mod
                WHERE picd_idpicking = :idpicking AND picd_idproducto = :idproducto";

        $this->conn->beginTransaction();
        $stmt = $this->conn->prepare($sql);
        if (is_null($cantidad)){
            $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_NULL);
        }else{
            $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        }
        $stmt->bindParam(':ubicacion',$ubicacion,PDO::PARAM_STR);
        $stmt->bindParam(':user_mod',$this->user_mod,PDO::PARAM_STR);
        $stmt->bindParam(':idpicking',$this->pick_idpicking);
        $stmt->bindParam(':idproducto',$idproducto,PDO::PARAM_STR);

        try {
            $stmt->execute();
            if ($stmt->rowcount() == 1){
                $this->conn->commit();
            }else{
                $this->error     = true;
                $this->error_nro = '46000';
                $this->error_msj = 'No se actualizó la cantidad en la Tarea de Picking '.$this->pick_idpicking.' para el producto '.$idproducto;
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }

        if ($this->error){
            $this->conn->rollback();
        }
        unset($sql, $stmt, $data, $idpicking, $idproducto, $cantidad);
    }


    /**************************************************************
    *                 UPDATE PICKING VERIFICA
    * Ocurre cuando se indica la cantidad verificada en una tarea
    * de picking.    
    **************************************************************/
    function update_picking_verifica($idproducto, $cantverif){
        $this->error = false;

        // Ahora vamos con Picking_Detalle
        $sql = "Update vpicking_detalle
                SET picd_cantverif = :cantverif, user_mod = :user_mod
                WHERE picd_idempresa = :idempresa AND picd_idpicking = :idpicking AND picd_idproducto = :idproducto";

        $stmt = $this->conn->prepare($sql);
        if (is_null($cantverif)){
            $stmt->bindParam(':cantverif', $cantverif, PDO::PARAM_NULL);
        }else{
            $stmt->bindParam(':cantverif', $cantverif, PDO::PARAM_INT);
        }
        $stmt->bindParam(':user_mod',$this->user_mod,PDO::PARAM_STR);
        $stmt->bindParam(':idempresa',$this->pick_idempresa);
        $stmt->bindParam(':idpicking',$this->pick_idpicking);
        $stmt->bindParam(':idproducto',$idproducto,PDO::PARAM_STR);

        try {
            $stmt->execute();
            if ($stmt->rowcount() == 1){
            }else{
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se actualizó la cantidad en la Tarea de Picking '.$this->pick_idpicking.' para el producto '.$idproducto;
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }

        unset($sql, $stmt, $data, $idpicking, $idproducto, $cantverif);
    }

    /*****************************************************************
    *                 UPDATE PICKING MASTER
    * Permite modificar datos de la tabla maestra de Tareas de Picking
    ******************************************************************/
    function update_picking_master(){
        $this->error = false;

        $adatos = array(
            'idpicking'   => $this->pick_idpicking,
            'fecha'       => $this->pick_fecha,
            'preparador'  => $this->pick_preparador,
            'status'      => $this->pick_status,
            'prioridad'   => $this->pick_prioridad,
            'pista'       => $this->pick_pista,
            'observacion' => $this->pick_observacion,
            'fecverif'    => $this->pick_fecverif,
            'userverif'   => $this->pick_userverif,
            'user_mod'    => $this->user_mod
        );

        // Ahora vamos con Picking_Detalle
        $sql = "UPDATE vpicking
                SET pick_fecha = :fecha, pick_preparador = :preparador, pick_status = :status, 
                pick_prioridad = :prioridad,  pick_pista = :pista, pick_observacion = :observacion,
                pick_fecverif = :fecverif, pick_userverif = :userverif, user_mod = :user_mod
                WHERE pick_idpicking = :idpicking";

        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($adatos);
            if ($stmt->rowcount() !== 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se actualizó la Tarea de Picking '.$adatos["idpicking"];
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
    *                 UPDATE PICKING DETALLE
    * Permite modificar datos de los productos requeridos en una 
    * tarea de picking
    **************************************************************/
    function update_picking_detalle($adatos){
        $this->error = false;

        // Ahora vamos con Picking_Detalle
        $sql = "UPDATE vpicking_detalle
                SET picd_requerido = :requerido, picd_cantidad = :cantidad, picd_cantverif = :cantverif, user_mod = :user_mod
                WHERE picd_idpicking = :idpicking AND picd_idproducto = :idproducto";

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


    /**************************************************************
    *               ELIMINA DETALLE PRODUCTO
    * Elimina 1 producto de la tarea de picking
    **************************************************************/
    function elimina_detalle_producto($idproducto){
        $this->error = false;
        if (empty($this->pick_idempresa) || empty($this->pick_idpicking) || empty($idproducto)){
            $this->error      = true;
            $this->error_nro  = 45200;
            $this->error_msj  = 'No hay tarea de picking o código de producto para proceder con eliminación';
            $this->error_file = '';
            $this->error_line = '';
            $this->error_tpo = 'error';
            return;
        }

        // Ahora vamos con Picking_Detalle
        $sql = "DELETE FROM vpicking_detalle WHERE picd_idpicking = :idpicking AND picd_idproducto = :idproducto";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idpicking',$this->pick_idpicking);
        $stmt->bindParam(':idproducto',$idproducto,PDO::PARAM_STR);

        try {
            $stmt->execute();
            if ($stmt->rowcount() != 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se eliminó el producto '.$idproducto.' de la Tarea de Picking '.$this->pick_idpicking;
                $this->error_tpo = 'error';
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }
    }

    /**************************************************************
    *               ELIMINA PICKING BINS
    * Elimina cualquier PALET o Cesta donde se ubican físicamente
    * los pedidos consolidados.
    **************************************************************/
    private function elimina_picking_bins(){
        $this->error = false;
        if (empty($this->pick_idempresa) || empty($this->pick_idpicking)){
            $this->error      = true;
            $this->error_nro  = 45201;
            $this->error_msj  = 'No hay tarea de picking para proceder con eliminación de Cestas o Palets';
            $this->error_file = '';
            $this->error_line = '';
            $this->error_tpo = 'error';
            return;
        }

        // Ahora vamos con Picking_Detalle
        $sql = "DELETE FROM vpicking_bins WHERE picc_idpicking = :idpicking";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idpicking',$this->pick_idpicking,PDO::PARAM_INT);

        try {
            $stmt->execute();
            /* Aquí en este caso no se hace ninguna validación porque entonces nos vemos obligados
            a hacer un procedimiento función que verifique si hay datos o no */
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }


    }
    
    /**************************************************************
    *               CONSOLIDA TAREA DE PICKING
    * Camia el Estatus de la tarea a 3=Consolidado y asigna la el 
    * nro de pista. Actualiza el campo pick_cantidad de la tabla
    * picking_detalle.
    **************************************************************/
    function consolida_tarea_picking(){
        $this->error = false;

        $data = array(
            'status'       => 3,
            'pista'        => $this->pick_pista,
            'observacion'  => $this->pick_observacion,
            'user_mod'     => $this->user_mod,
            'idempresa'    => $this->pick_idempresa,
            'idpicking'    => $this->pick_idpicking
        );

        // Ahora vamos con Picking_Detalle
        $sql = "UPDATE vpicking SET pick_status = :status, pick_pista= :pista, pick_observacion = :observacion, user_mod = :user_mod
                WHERE pick_idempresa = :idempresa AND pick_idpicking = :idpicking";
    
        $this->conn->beginTransaction();
        $stmt = $this->conn->prepare($sql);

        // Vamos con la actualización de la Picking.
        try {
            $stmt->execute($data);
            if ($stmt->rowcount() <> 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se actualizó  la Tarea de Picking '.$this->pick_idpicking;
                $this->error_tpo = 'error';                
            }
        }catch (PDOException $e) {
            $this->error_registro($e);
        }catch (Exception $e) {
            $this->error_registro($e);
        }

        // Si no hay error, inserto el container donde se consolidó el pedido luego del picking.
        if (!$this->error){
            $this->insert_picking_bins();
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
            $out["pick_idpicking"] = $this->pick_idpicking;
        }else{
            $out["response"] = "fail";
            $out["error_nro"]  = $this->error_nro;
            $out["error_msj"]  = $this->error_msj;
            $out["error_file"] = $this->error_file;
            $out["error_line"] = $this->error_line;
            $out["error_tpo"]  = 'error';
        }
        unset($sql, $stmt, $data);
        return ($out);
    }


    /**************************************************************
    *               CIERRA TAREA DE PICKING
    **************************************************************/
    function cierra_tarea_picking(){
        $this->error = false;

        $data = array(
            'status'       => 5,
            'userverif'    => $this->pick_userverif,
            'observacion'  => $this->pick_observacion,
            'user_mod'     => $this->user_mod,
            'idempresa'    => $this->pick_idempresa,
            'idpicking'    => $this->pick_idpicking
        );

        // Ahora vamos con Picking_Detalle
        $sql = "UPDATE vpicking SET pick_status = :status, pick_userverif= :userverif, pick_observacion = :observacion, user_mod = :user_mod
                WHERE pick_idempresa = :idempresa AND pick_idpicking = :idpicking";
    
        $stmt = $this->conn->prepare($sql);
        
        // Vamos con la actualización de la Picking.
        try {
            $stmt->execute($data);
            if ($stmt->rowcount() <> 1){
                $this->error     = true;
                $this->error_nro = '45000';
                $this->error_msj = 'No se actualizó  la Tarea de Picking '.$this->pick_idpicking;
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