<?php
    class Db_mariadb {
        // private $servidor = "server01";
        private $servidor = "localhost";
        //private $servidor = "DESKTOP-GV0FI44";
        private $database = "wms";
        private $usuario  = "root";
        private $clave    = "";
        public  $conn ; // Variable del controlador
        // private $options  = [
        //   PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
        //   PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
        //   PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
        //   PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", // Para que se vean acentos y eñes.
        //   PDO::ATTR_STRINGIFY_FETCHES  => false  // Evita la conversión de numeros a caracteres en los queries.
        // ];
  
        public function getConnection(){
          $this->conn = null;
          try{
              $this->conn = new PDO("mysql:host=" . $this->servidor. ";dbname=" . $this->database, $this->usuario, $this->clave);
              $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
              //$this->conn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,"SET NAMES utf8");
              $this->conn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,"SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");
              $this->conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES,false);
              $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::ERRMODE_EXCEPTION);
              $this->conn->setAttribute(PDO::CASE_LOWER, true);
              $this->conn->exec('set names utf8');
              $servidor = $database = $usuario = $clave = null;
              unset($servidor, $database, $usuario, $clave);
          }catch(PDOException $exception){
              echo "Error en conexión a Mariadb Server: " . $exception->getMessage();
          }
          return $this->conn;
      }
    }
?>
