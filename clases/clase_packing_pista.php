<?php
    /*************************************************************************************
    * Clase Packing
    *
    * Metodos:
    * carga_packing_pista($pista)
    /**************************************************************************************/
    declare(strict_types=1);
    // namespace clases\Packing_Pista;

class Packing_Pista {
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
    *                 CARGA PACKING PISTA
    * Lee de la base de datos todas las tareas de packing
    * que se encuentren en proceso
    ***********************************************************/
    function carga_packing_pista(){
        $sql = "SELECT b.pick_pista, tabla1.picc_bin, a.pack_idpedido, a.pack_idpacking, a.pack_embalador, a.pack_prioridad, b.fec_crea, tabla1.orden
                  FROM vpacking a
                  JOIN vpicking b ON b.pick_idempresa = a.pack_idempresa AND b.pick_idpicking = a.pack_idpicking,
                (SELECT rownum(), ROW_NUMBER() OVER ( ORDER BY z.fec_crea ) AS orden, z.picc_idpicking,  z.picc_bin, z.fec_crea
                   FROM vpicking_bins z) tabla1
                  WHERE a.pack_status = 1 AND b.pick_pista = :pista
                    AND a.pack_idpicking = tabla1.picc_idpicking
                  ORDER BY tabla1.orden";

        $stmt = $this->conn->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->bindparam(':pista',$this->pista, PDO::PARAM_INT);
        $stmt->execute();
        $resp = $stmt->fetchall(PDO::FETCH_ASSOC);
        $this->pista_packing = $resp;
        unset($sql,$stmt,$resp);
    }


    /******************************************************
    *                 tareas_packing_card
    *******************************************************/
    function html_packing_pista(){
        $out  = '';
        $bin  = '';
        if (!empty($this->pista_packing)){
            for ($i=0; $i<count($this->pista_packing); $i++):
                $button = '<button id="'.$this->pista_packing[$i]["pack_idpacking"].'" ';
                if ($this->pista_packing[$i]["pack_embalador"] == $_SESSION["username"]){
                    $button .= 'class="btn btn-success position-relative"';
                }elseif (empty($this->pista_packing[$i]["pack_embalador"])) {
                    $button .= 'class="btn btn-primary position-relative"';
                }else{
                    $button .= 'class="btn btn-danger position-relative"';
                }
                
                $button .= ' style="height:45px;" onclick="packing_start('.$this->pista_packing[$i]["pack_idpacking"].',\''.$this->pista_packing[$i]["pack_idpedido"].'\')">'.$this->pista_packing[$i]["pack_idpedido"];
                if (($this->pista_packing[$i]["pack_prioridad"]) == 1){
                    $button .= '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">Urgente</span>';
                }
                $button .= '</button>';

                if ($bin == $this->pista_packing[$i]["picc_bin"]){
                    $out .= $button;
                }else{
                    if (!empty($bin)){
                        $out .= '</section></div>';
                    }
                    $bin = $this->pista_packing[$i]["picc_bin"];
                    $out .= '<div class="palet-card" id="'.$bin.'">';
                    $out .= '<section class="palet-card-header text-white">'.$bin.'</section>';
                    $out .= '<section class="palet-card-body">';
                    $out .= $button;
                }
            endfor;
            $out .= '</section></div>';
        }else{
            $out .= '<div style="text-align:center;">no hay tareas de packing para esta zona</div>';
        }
        unset($bin);
        return $out;
    }
}
?>