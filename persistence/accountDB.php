<?php

    class AccountDB {

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

        public function getAccounts(){

            $sql = $this->dbConn->prepare("SELECT * FROM cuenta");
            $sql->execute();
            $sql->setFetchMode(PDO::FETCH_ASSOC);

            return $result;
        }

        

        public function createAccount($account){
            
            $number = $account->getNumber();
            $balance = $account->getBalance();
            $type = $account->getType();
            $state = $account->getState();

            $sql = "INSERT INTO cuenta
                  (numero, saldo, tipo, estado)
                  VALUES
                  (:n, :b, :t, :s)";
            $statement = $this->dbConn->prepare($sql);

            $statement->bindValue(':n', $number);
            $statement->bindValue(':b', $balance);
            $statement->bindValue(':t', $type);
            $statement->bindValue(':s', $state);

            $statement->execute();
            
        }

    }
