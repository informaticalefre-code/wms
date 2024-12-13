<?php
    class Db_sqlsrv {
        //private $servidor = "localhost";
        /************** CASA ********************/
        // private $servidor = "DESKTOP-GV0FI44";
        // private $database = "LEFRE_DV";
        // private $usuario  = "sa";
        // private $clave    = "Xenx1234";

        // ************* LEFRE PRUEBAS ****************/
        // private $servidor = "server03";
        // private $database = "LEFRE_DV";
        // private $usuario  = "consulta";
        // private $clave    = "l1234*";
        private $servidor = "server02";
        private $database = "LEFRE_DV";
        private $usuario  = "picking";
        private $clave    = "p1ck1ng";

        public function getConnection(){
            $this->conn = null;
            try{
                $this->conn = new PDO("sqlsrv:server=" . $this->servidor . ";database=" . $this->database, $this->usuario, $this->clave);
                $this->conn->ATTR_ATTR_EMULATE_PREPARES = false;
                // $this->conn->ATTR_ERRMODE            = $this->conn::ERRMODE_EXCEPTION;
                $this->conn->MYSQL_ATTR_INIT_COMMAND    = "SET NAMES utf8";
                $this->conn->ATTR_STRINGIFY_FETCHES     = false;
                $this->conn->ATTR_DEFAULT_FETCH_MODE    = $this->conn::FETCH_ASSOC;
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // $this->conn->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
                unset($servidor, $database, $usuario, $clave);
            }catch(PDOException $exception){
                // $_SESSION["error_nro"] = 50000;
                // $_SESSION["error_msj"] = "Error en conexión." . $exception->getMessage();
                // $url = "http://".$_SERVER['SERVER_NAME']."/wms/error.php";
                // echo "url:".$url."\n";
                echo "Error en conexión a SQL Server: " . $exception->getMessage();
                // header("Location: ".$url);
                // header("location: ../error.php",true, 302);
                // header("location: ../error.php",true, 301);
                // exit();
            }
            return $this->conn;
        }
    }
?>