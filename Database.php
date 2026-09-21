<?php 
    class Database{
        private $host = "localhost";
        private $db_name = "luis_db";
        private $username = "root";
        private $password = "";
        private $conn;

        public function getConnection() {
            $this->conn = null;

            try {
                $this->conn = new PDO(
                    "mysql:host" . $this->host . "; db_name" . $this-> db_name,
                    $this->username, 
                    $this->password 
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO:: ERRMODE_EXCEPTION);
            } catch (PDOException $exception) {
                echo "error de conexion: " . $exception->getMessage();
            }
        }
    }
?>