<?php
    /*************************************************************************************
    * Clase Cliente
    * Propiedades:
    * Deuda_Detalle: Es un array con las facturas pendientes del cliente...
    *                 array:(factura, fecha_fac, monto_fac, pagado_fac, saldo_fac)
    * Deuda_total: Total Adeudado.
    * meses_anteriores: array con lo facturado y lo pagado en los ultimos 6 meses 
                        (año, mes, facturado, pagado).
    * Promedio_fac: Indica el promedio facturado en los últimos 6 meses. Es calculado
    *               al ejecutar el método 
    * Metodos:
    * carga_cliente_deuda(): Carga las facturas pendientes del cliente en las propiedades
                             "Deuda_Detalle" y "Deuda_total"
    * carga_meses_anteriores(): Carga la propiedad "meses_anteriores" y "promedio_fac".
    /**************************************************************************************/
    declare(strict_types=1);
    // namespace Packing;

class Cliente {
    // Conexión
    public  $conn;
    
    // Para manejo de errores o validaciones
    public $error ; // Indica si se detectó un error o advertencia en algún método.
    public $error_nro;      // Nro de Error. Si es una excepción de usuario se coloca 45000
    public $error_msj;      // Mensaje de Error.
    public $error_file;     // Archivo del error.
    public $error_line;     // Linea del error.
    public $error_tpo;      // Tipo de error (2 valores): "warning" o "error"

    public $idcliente;
    public $deuda_detalle;    // Array con las facturas que tiene pendiente el cliente por cancelar
    public $meses_anteriores; // Array con lo facturado y pagado en los últimos 6 meses.
    public $promedio_fac;     // propiedad que indica el promedio facturado en los últimos 6 meses.
    public $deuda_total;      // propiedad que muestra el monto adeudado por el cliente.

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
    *                 CARGA CLIENTE DEUDA
    **************************************************************/
    function carga_cliente_deuda(){
        $this->error = false;
        $sql  = "SELECT a.documento_cxc as factura, CONVERT(varchar(10), b.fecha_fac,103) as fecha_fac, CONVERT(varchar(10), ISNULL(c.recibido_fac,''),103) as recibido_fac,DATEDIFF(day, ISNULL(c.recibido_fac,b.fecha_fac), current_timestamp) as dias,ROUND(a.monto_fac,2) as monto_fac, Round(a.pagado_fac,2) as pagado_fac, ROUND(a.saldo_fac,2) as saldo_fac
                   FROM v_cuentas_x_cobrar a
                   JOIN Tbfacturacion3 b ON b.numero_fac = a.documento_cxc
              LEFT JOIN TbRecibeDoc2 c ON c.factura_fac = a.documento_cxc
                  WHERE a.codigo_cxc = :idcliente
                    AND a.saldo_fac >= 0.01
                  ORDER BY a.documento_cxc";

        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idcliente',$this->idcliente, PDO::PARAM_STR);
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        if ($stmt->rowcount() > 0):
            $this->deuda_detalle = $resp;
            $this->deuda_total = array_sum(array_column($this->deuda_detalle,'saldo_fac'));
        else:
            $this->deuda_detalle = $resp;
            $this->deuda_total = 0;
        endif;
        unset($sql,$stmt,$resp);
    }


    /**************************************************************
    *                 CARGA MESES ANTERIORES
    * Carga lo facturado de los últimos 6 meses y lo pagado 
    * de cada mes. Adicionalmente crea un propiedad promedio_fac
    * que saca el promedio de lo facturado.
    **************************************************************/
    function carga_meses_anteriores(){
        $this->error = false;
        $sql  = "select year(a.fecha_fac) as ano, month(a.fecha_fac) as mes, SUM(b.monto_fac) as facturado, SUM(b.pagado_fac) as pagado, SUM(b.saldo_fac) as saldo
        FROM Tbfacturacion3 a
        JOIN v_facturas_pagos b ON b.documento_cxc = a.numero_fac 
        WHERE a.status_fac = 1 and cliente_fac = :idcliente
        and a.fecha_fac >= Dateadd(Month, Datediff(Month, 0, DATEADD(m, -6,
        current_timestamp)), 0)
        GROUP BY year(a.fecha_fac), month(a.fecha_fac)
        ORDER BY year(a.fecha_fac), month(a.fecha_fac)";

        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':idcliente',$this->idcliente, PDO::PARAM_STR);
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        if ($stmt->rowcount() > 0):
            $this->meses_anteriores = $resp;
            $this->promedio_fac     = round(array_sum(array_column($resp,'facturado'))/ count($resp),2);
        else:
            $this->meses_anteriores = $resp;
            $this->promedio_fac     = null;
        endif;
        unset($sql,$stmt,$resp);
    }
}
?>