<?php
    /*************************************************************************************
    * Clase Picking
    *
    * Metodos:
    * carga_picking_pista($pista)
    /**************************************************************************************/
    declare(strict_types=1);
    // namespace Picking_Pista;

class Picking_Pista {
    // Conexión
    private $conn;

    // Para manejo de errores o validaciones
    public  $error; // Indica si se detectó un error o advertencia en algún método.
    public  $error_nro;     // Nro de Error. Si es una excepción de usuario se coloca 45000
    public  $error_msj;     // Mensaje de Error.
    public  $error_file;    // Archivo del error.
    public  $error_line;    // Linea del error.
    public  $error_tpo;     // Tipo de error (2 valores): "warning" o "error"

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

    /**********************************************************
    *                 CARGA PICKING PISTA
    * Lee de la base de datos todas las tareas de picking
    * que se encuentren pendiente por verificacion.
    * y las carga en el atributo "pista_picking" como un array    
    ***********************************************************/
    function carga_picking_pista(){
        $sql = 'SELECT a.pick_pista, tabla1.picc_bin, a.pick_idpedido, a.pick_idpicking, a.pick_idempresa, a.pick_fecierre, a.pick_prioridad, tabla1.fec_crea, tabla1.orden
        FROM vpicking a,
        (SELECT rownum(), ROW_NUMBER() OVER ( ORDER BY z.fec_crea ) AS orden, z.picc_idpicking,  z.picc_bin, z.fec_crea 
          FROM vpicking_bins z) tabla1
        WHERE a.pick_status = 3 AND a.pick_pista = :pista
        AND a.pick_idpicking = tabla1.picc_idpicking
        ORDER BY tabla1.orden';
        $stmt = $this->conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':pista',$this->pista, PDO::PARAM_INT);
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        // if ($stmt->rowcount() > 0):
        $this->pista_picking = $resp;
        // endif;
        unset($sql,$stmt,$resp);
    }


    /******************************************************
    *                 tareas_picking_card
    *******************************************************/
    function html_picking_pista(){
        $out = '';
        $bin = '';
        $link = '';
        if (!empty($this->pista_picking)){
            for ($i=0; $i<count($this->pista_picking); $i++):
                $link   = "picking_verifica.php?id_picking=".$this->pista_picking[$i]["pick_idpicking"];
                $button = '<button class="btn btn-primary position-relative" style="height:45px;" onclick="window.location.href=\''.$link.'\'">'.$this->pista_picking[$i]["pick_idpedido"];
                if (($this->pista_picking[$i]["pick_prioridad"]) == 1){
                    $button .= '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">Urgente</span>';
                }
                $button .= '</button>';
                if ($bin == $this->pista_picking[$i]["picc_bin"]){
                    $out .= $button;
                }else{
                    if (!empty($bin)){
                        $out .= '</section></div>';
                    }
                    $bin = $this->pista_picking[$i]["picc_bin"];
                    $out .= '<div class="palet-card" id="'.$bin.'">';
                    $out .= '<section class="palet-card-header text-white">'.$bin.'</section>';
                    $out .= '<section class="palet-card-body">';
                    $out .= $button;
                }
            endfor;
            $out .= '</section></div>';
        }else{
            $out .= '<div style="text-align:center;">no hay tareas de picking para esta zona</div>';
        }            
        unset($bin,$link);
        return $out;
    }


}
?>