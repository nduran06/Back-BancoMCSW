<?php

    class SobregiroDB {

        private $db;
        private $dbConn;

        public function __construct($db, $dbConn) {

            try{
                $this->db = $db;
                $this->dbConn =  $dbConn;
            }catch (exception $e) {
                http_response_code(500);
                exit;
            }
        }

        public function getSobregiros(){

            $sql = $this->dbConn->prepare("SELECT * FROM sobregiro");
            $sql->execute();
            $sql->setFetchMode(PDO::FETCH_ASSOC);

            return $sql;
        }

        public function getSobregiroByAccount($cuentaID){

            $sql = "SELECT * FROM sobregiro WHERE cuenta_id=:id";

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindValue(':id', $cuentaID);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result;
        }
      
         public function getSobregiroById($id){

            $sql = "SELECT * FROM sobregiro WHERE id=:id";

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result;
        }

        public function getUserByState($state){

            $sql = "SELECT * FROM sobregiro WHERE estado=:state";

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindValue(':state', $state);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result;
        }

   
        public function createSobregiro($cuentaID){

          
            $cuentaExistID = $cuentaID->getCuentaExistID();
            $state = $cuentaID->getState();
            $percent = $cuentaID->getPercent();
                        
            $sql = "INSERT INTO sobregiro
                  (cuenta_id, estado, porcentaje)
                  VALUES
                  (:cuenta, :estado, :porcentaje)";
            $statement = $this->dbConn->prepare($sql);

            $statement->bindValue(':cuenta', $cuentaExistID);
            $statement->bindValue(':estado', $state);
            $statement->bindValue(':porcentaje', $percent);

            $statement->execute();

            return $percent;
        }

    }
