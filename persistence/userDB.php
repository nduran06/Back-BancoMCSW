<?php
    include($_SERVER['DOCUMENT_ROOT'].'/database/config.php');
    include($_SERVER['DOCUMENT_ROOT'].'/database/utils.php');

    class UserDB {

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

        public function getUsers(){
           header('Content-Type: application/JSON');

            $stmt = $this->dbConn->prepare("SELECT * FROM usuario ");

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result;
        }

        public function createUser($input){

            $usuario = $input['usuario'];
            $passwd = $input['passwd'];
            $tipo = $input['tipo'];

            $sql = "INSERT INTO usuario
                  (usuario, passwd, tipo)
                  VALUES
                  (:usuario, :passwd, :tipo)";
            $statement = $this->dbConn->prepare($sql);

            $statement->bindValue(':usuario', $usuario);
            $statement->bindValue(':passwd', $passwd);
            $statement->bindValue(':tipo', $tipo);

            $statement->execute();

            return $input;
        }

    }