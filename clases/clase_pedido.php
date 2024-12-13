<?php
    declare(strict_types=1);
    // namespace Pedido;

/*******************************************************************************************
 * Clase Pedido
 *  Funciones Publicas
 *  lista_aprobados(): retorna Json con todos los pedidos PENDIENTES Y ASIGNADOS.
 *  genera_pedidos_card(): retorna Html para tarjeta de Pedidos con datos del Pedido Cargado.
 *  carga_datos(): Carga datos en el objeto Pedidos dado un código de Pedido.
 *  carga_pedidos_master(): carga datos de un pedido
 *  carga_pedidos_detalle(): carga los productos o el detalle de un pedido
 *
 *  Funciones Privadas
 ********************************************************************************************/
class Pedido {
    // Conexión
    public $conn;

    // Para manejo de errores o validaciones
    public $error;      // Indica si se detectó un error o advertencia en algún método.
    public $error_nro;  // Nro de Error. Si es una excepción de usuario se coloca 45000
    public $error_msj;  // Mensaje de Error.
    public $error_file; // Archivo del error.
    public $error_line; // Linea del error.
    public $error_tpo;  // Tipo de error (2 valores): "warning" o "error"

    public $numero_ped;
    public $fecha_ped;
    public $cliente_ped;
    public $nombre_cli;
    public $direccion_cli;
    public $vendedor_ped;
    public $nombre_ven;
    public $observacion_ped;
    public $status_ped = null;
    public $pedido_detalle; // Array con los productos del pedido.
    
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

    /**************************************************************
    *               HTML PEDIDOS CARD 
    **************************************************************/
    function html_pedidos_card(){
        $out  = '';
        $out .= '<div class="pedido-tarjeta" id="'.$this->numero_ped.'">';
        $out .= '<section class="pedido-header text-white">';
        $out .= '<p class="mb-0">'.$this->numero_ped.'</p>';
        $out .= '<button id="pedido_seleccionar" onclick="pedido_picktarea(\''.$this->numero_ped.'\')" type="button" class="btn"><i class="icon-plus-circle"></i></button>';
        $out .= '</section>';
        $out .= '<section class="pedido-body">';
        $out .= '<div class="pedido-body-line1">';
        $out .= '<button type="button" onclick="pedido_productlist(\''.$this->numero_ped.'\')" class="btn btn-sm btn-outline-primary">Productos</button>';
        $out .= '<p class="mb-0">'.$this->fecha_ped.'</p>';
        $out .= '</div>';
        $out .= '<p id="card_cliente" class="mb-0">'.ucwords(strtolower($this->nombre_cli)).'</p>';
        $out .= '<p class="mb-0">'.ucwords(strtolower($this->nombre_ven)).'</p>';
        $out .= '</section>';
        $out .= '</div>';
        return $out;
    }

    /**************************************************************
    *                   CARGA DATOS     
    **************************************************************/
    function carga_datos(){
        $this->carga_pedidos_master(); 
        if (!$this->error) {
            $this->carga_pedidos_detalle();
        }
    }

    /**************************************************************
    *                   CARGA PEDIDOS MASTER
    **************************************************************/
    function carga_pedidos_master(){
        $sql = "select a.numero_ped, CONVERT(varchar(10), a.fecha_ped,103) as fecha_ped, a.cliente_ped, 
                b.descripcion_cli as nombre_cli, b.direccion_cli, a.vendedor_ped, c.nombre_ven, a.observacion_ped,a.status_ped
                from TbPedidos1 a 
                INNER JOIN TbClientes b ON a.cliente_ped = b.codigo_cli
                INNER JOIN TbVendedores c ON a.vendedor_ped = c.codigo_ven
                WHERE a.numero_ped = :numero_ped";

        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':numero_ped',$this->numero_ped, PDO::PARAM_STR);
        $stmt->execute();
        $resp = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowcount() == 1){
            $this->numero_ped      = $resp["numero_ped"];
            $this->fecha_ped       = $resp["fecha_ped"];
            $this->cliente_ped     = $resp["cliente_ped"];
            $this->nombre_cli      = $resp["nombre_cli"];
            $this->direccion_cli   = $resp["direccion_cli"];
            $this->vendedor_ped    = $resp["vendedor_ped"];
            $this->nombre_ven      = $resp["nombre_ven"];
            $this->observacion_ped = $resp["observacion_ped"];
            $this->status_ped      = $resp["status_ped"];
        }else{
            $this->error     = true;
            $this->error_msj = "El Pedido Nro. ".$this->numero_ped." no existe";
            $this->error_tpo = "error";
        }
        unset($sql,$stmt,$resp);
    }


    /**************************************************************
    *                 CARGA PEDIDOS DETALLE
    **************************************************************/
    function carga_pedidos_detalle(){
        $sql  = "select a.numero_ped, a.producto_ped, ROUND(a.cantidad_ped,0) as cantidad_ped, a.descripcion_ped,
                 b.unidad_pro, b.ubicacion_pro, b.existencia_pro
                 from tbpedidos2 a 
                 left join tbproductos b ON b.codigo_pro = a.producto_ped
                 where numero_ped = :numero_ped";
    
        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR=>PDO::CURSOR_SCROLL));
        $stmt->bindparam(':numero_ped',$this->numero_ped,PDO::PARAM_STR);
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        if ($stmt->rowcount() > 0):
            $this->pedido_detalle = $resp;
        endif;
        unset($sql,$stmt,$resp);
    }


    /****************************************************
    *                  APROBAR PEDIDO
    ******************************************************/
    function aprobar_pedido(){
        $this->error = false ;

        if (!isset($this->numero_ped) OR empty($this->numero_ped)){
            $this->error      = true ;
            $this->error_nro  = 45001;
            $this->error_msj  = 'Proceso Ilegal. Pedido no definido para operación de actualizacion';        
            $this->error_file = null;
            $this->error_line = null;
            $this->error_tpo  = 'error';
        }

        if (!$this->error && $this->valida_status("APROBADO")){
            $this->status_ped = "APROBADO";
            $data = array('status_ped'    => $this->status_ped, 
                          'aprobador_ped' => $_SESSION["username"],
                          'numero_ped'    => $this->numero_ped);
            $sql  = "UPDATE TbPedidos1 SET status_ped = :status_ped, aprobador_ped = :aprobador_ped, aprofecha_ped = CURRENT_TIMESTAMP WHERE numero_ped = :numero_ped";
            $stmt = $this->conn->prepare($sql);
            
            try {
                $stmt->execute($data);
                if ($stmt->rowcount() != 1){
                    $this->error     = true;
                    $this->error_nro = '46000';
                    $this->error_msj = 'No se actualizó datos en Pedidos';
                    $this->error_tpo = 'error';
                }
            } catch (PDOException $e) {
                $this->error_registro($e);
            } catch (Exception $e) {
                $this->error_registro($e);
            }
            unset($data,$sql,$stmt);
        }
    }    


    /****************************************************
    *            UPDATE PEDIDOS STATUS
    ******************************************************/
    function update_pedidos_status($pstatus){
        $this->error = false ;

        if (!isset($this->numero_ped) OR empty($this->numero_ped)){
            $this->error      = true ;
            $this->error_nro  = 45001;
            $this->error_msj  = 'Proceso Ilegal. Pedido no definido para operación de actualizacion';        
            $this->error_file = null;
            $this->error_line = null;
            $this->error_tpo  = 'error';
            return;
        }else{
            /* Si no se ha cargado datos es necesario cargar el Pedido para validar cualquier 
               cambio de estatus */
            if (!isset($this->status_ped)){
                $this->carga_pedidos_master();
            }
        }


        if (!$this->error && $this->valida_status($pstatus)){
            $this->status_ped = $pstatus;
            $data = array('status_ped'=>$this->status_ped, 'numero_ped'=>$this->numero_ped);
            $sql  = "UPDATE TbPedidos1 SET status_ped = :status_ped WHERE numero_ped = :numero_ped";
            $stmt = $this->conn->prepare($sql);
            
            try {
                $stmt->execute($data);
                if ($stmt->rowcount() != 1){
                    $this->error     = true;
                    $this->error_nro = '46000';
                    $this->error_msj = 'No se actualizó datos en Pedidos';
                    $this->error_tpo = 'error';
                }
            } catch (PDOException $e) {
                $this->error_registro($e);
            } catch (Exception $e) {
                $this->error_registro($e);
            }
            unset($data,$sql,$stmt);
        }

    }


    /******************************************************
    *                 VALIDA STATUS
    * Valida que se hagan correctamente los cambios de los
    * status de los pedidos a través del Sistema de Almacén
    * Se hace privado porque previamente se tiene que cargar
    * los datos del pedido a través del metodo carga_datos()
    * o carga_datos_master()
    *******************************************************/
    private function valida_status($pstatus){
        if (!isset($this->status_ped)){
            $this->error     = true ;
            $this->error_nro = 45010 ;
            $this->error_msj = 'No hay datos cargados de Pedidos para validar cambio de estatus';
            $this->error_tpo = 'warning';
        }

        if ($pstatus == $this->status_ped && $pstatus == 'APROBADO'){
            /* Esta validación solo para aprobado es porque cuando se aprueba se copia el pedido al sistema de Gestión
            de almacén, aunque no se puede colocar un pedido repetido en picking a nivel de base de datos igual 
            se hace la validación */
            $this->error     = true;
            $this->error_nro = 45011;
            $this->error_msj = 'Pedido ya se encuentra en el status indicado';
            $this->error_tpo = 'warning';
        }elseif ($pstatus == 'APROBADO' && $_SESSION["user_role"] !== 'Aprobador'){
            /* Si se quiere aprobar pero no es "aprobador" el usuario */
            $this->error     = true;
            $this->error_nro = 45003;
            $this->error_msj = 'No autorizado';
            $this->error_tpo = 'warning';
        }elseif ($this->status_ped == 'TOTAL' || $this->status_ped == 'PARCIAL' || $this->status_ped == 'ANULADO'){
            $this->error     = true ;
            $this->error_nro = 45012 ;
            $this->error_msj = 'No se puede cambiar o modificar Pedido ya Facturado o Anulado';
            $this->error_tpo = 'warning';
        }elseif (($pstatus == 'APROBADO') && ($this->status_ped == 'PICKING' || $this->status_ped == 'PACKING' || $this->status_ped == 'PENDIENTE')){
            $this->error     = true ;
            $this->error_nro = 45013 ;
            $this->error_msj = 'No se puede regresar el pedido a un estatus anterior';
            $this->error_tpo = 'warning';
        }
        return (!$this->error);
    }


    /****************************************************
    *                  CIERRA PEDIDO
    ******************************************************/
    function cierra_pedido($pstatus,$productos_packing){
        $this->update_pedidos_cantidades($productos_packing);
        if (!$this->error){
            $this->update_pedidos_status($pstatus);
        }
    }


    /*******************************************************
    *            UPDATE PEDIDOS CANTIDADES
    * Primero inicializamos todos los campos aprobado_ped
    * a su valor antes de ser aprobados en el Xenx o antes
    * que hayan sido modificados previos a un cierre de una
    * tarea de packing.
    ********************************************************/
    function update_pedidos_cantidades($productos_packing){
        /* A continuación procedemos a inicializar el campos aprobado_ped en el pedido */
        $sql = "UPDATE TbPedidos2 SET aprobado_ped = 0 WHERE numero_ped = :numero_ped";
        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR=>PDO::CURSOR_SCROLL));
        $stmt->bindparam(':numero_ped',$this->numero_ped,PDO::PARAM_STR);
        
        try {
            $stmt->execute();
        } catch (PDOException $e){
            $this->error_registro($e);
        } catch (Exception $e){
            $this->error_registro($e);
        }

        if (!$this->error){
            for ($i=0; $i<count($productos_packing); $i++):
                $data = array('aprobado_ped'=> is_null($productos_packing[$i]['pacd_cantidad']) ? 0 : $productos_packing[$i]['pacd_cantidad'], 
                              'numero_ped'=>$this->numero_ped, 
                              'producto_ped'=>$productos_packing[$i]['pacd_idproducto']);
                $sql  = "UPDATE TbPedidos2 SET aprobado_ped = :aprobado_ped WHERE numero_ped = :numero_ped AND producto_ped = :producto_ped";
                $stmt = $this->conn->prepare($sql);
                
                try {
                    $stmt->execute($data);
                    if ($stmt->rowcount() != 1){
                        $this->error     = true;
                        $this->error_nro = '46000';
                        $this->error_msj = 'No se actualizó cantidad aprobada en Pedidos';
                        $this->error_tpo = 'error';
                    }
                } catch (PDOException $e){
                    $this->error_registro($e);
                } catch (Exception $e){
                    $this->error_registro($e);
                }
            endfor;
        }

        unset($data,$sql,$stmt);
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
