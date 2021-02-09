<?php

    include('../database/config.php');
    include('../database/utils.php');

    class ExistedClientsDB {

        private $db;
        private $dbConn;

        public function __construct() {

            try{
                $this->db = dbInfo();
                $this->dbConn =  connect($this->db);
            }catch (exception $e) {
                http_response_code(500);
                exit;
            }
        }

        public function getExistedUser($documento){

            $sql = "SELECT * FROM cuentas_existentes WHERE doc_usuario=:documento";

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindValue(':documento', $documento);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result;
        }

    }