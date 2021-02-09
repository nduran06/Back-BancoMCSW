<?php
    include('../database/config.php');
    include('../database/utils.php');

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

            $sql = $this->dbConn->prepare("SELECT * FROM usuario");
            $sql->execute();
            $sql->setFetchMode(PDO::FETCH_ASSOC);

            return $sql;
        }

        public function getUser($id){

            $sql = "SELECT * FROM usuario WHERE id=:id";

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result;
        }

        public function createUser($user){

            $doc = $user->getDoc();
            $usuario = $user->getUsuario();
            $nombre = $user->getName();
            $passwd = $user->getPass();
            $tipo = $user->getType();

            $sql = "INSERT INTO usuario
                  (documento, usuario, passwd, nombre, tipo)
                  VALUES
                  (:doc, :usuario, :passwd, :nombre, :tipo)";
            $statement = $this->dbConn->prepare($sql);

            $statement->bindValue(':documento', $doc);
            $statement->bindValue(':usuario', $usuario);
            $statement->bindValue(':passwd', $passwd);
            $statement->bindValue(':nombre', $nombre);
            $statement->bindValue(':tipo', $tipo);

            $statement->execute();

            return $usuario;
        }

    }