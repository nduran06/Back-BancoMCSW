<?php

    class UserDB {

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

        public function getUserByUsername($usuario){

            $sql = "SELECT * FROM usuario WHERE usuario=:usuario";

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindValue(':usuario', $usuario);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function getIdByDoc($doc){

            $sql = "SELECT id FROM usuario WHERE documento=:doc";

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindValue(':doc', $doc);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function loginUser ($usuario) {

            $sql = "SELECT * FROM usuario WHERE usuario=:usuario";

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindValue(':usuario', $usuario);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result;
        }

        public function getUserIdByUsername($usuario) {
            $sql = "SELECT id FROM usuario WHERE usuario=:usuario";

            $statement = $this->dbConn->prepare($sql);

            $statement->bindValue(':usuario', $usuario);

            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        }

        public function createUser($user){

            $doc = $user->getDoc();
            $usuario = $user->getUser();
            $nombre = $user->getName();
            $passwd = $user->getPass();
            $tipo = $user->getType();

            $sql = "INSERT INTO usuario
                  (documento, usuario, passwd, nombre, tipo)
                  VALUES
                  (:doc, :usuario, :passwd, :nombre, :tipo)";
            $statement = $this->dbConn->prepare($sql);

            $statement->bindValue(':doc', $doc);
            $statement->bindValue(':usuario', $usuario);
            $statement->bindValue(':passwd', $passwd);
            $statement->bindValue(':nombre', $nombre);
            $statement->bindValue(':tipo', $tipo);

            $statement->execute();

            $id=$this->getIdByDoc($doc)["id"];

            return ["id" => $id, "tipo" => $tipo];
        }


    }