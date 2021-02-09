<?php
include('../database/config.php');
include('../database/utils.php');

class TransDB {

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

    public function getAllTrans(){

        $sql = $this->dbConn->prepare("SELECT * FROM transaccion");
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);

        return $sql;
    }

    public function getTrans($id){

        $sql = "SELECT * FROM transaccion WHERE id=:id";

        $stmt = $this->dbConn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

    public function getAllUserTrans($numeroCuenta){

        $sql = $this->dbConn->prepare("SELECT * FROM transaccion");
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);

        return $sql;
    }

    public function createTrans($trans){

        $origen = $trans->getDoc();
        $destino = $trans->getUsuario();
        $banco_origen = $trans->getName();
        $banco_destino = $trans->getPass();
        $saldo = $trans->getType();
        $estado = $trans->getType();
        $fecha = $trans->getType();


        $sql = "INSERT INTO transaccion
                  (origen, destino, banco_origen, banco_destino, saldo, estado, fecha)
                  VALUES
                  (:origen, :destino, :banco_origen, :banco_destino, :saldo, :estado, :fecha)";

        $statement = $this->dbConn->prepare($sql);

        $statement->bindValue(':origen', $origen);
        $statement->bindValue(':destino', $destino);
        $statement->bindValue(':banco_origen', $banco_origen);
        $statement->bindValue(':banco_destino', $banco_destino);
        $statement->bindValue(':saldo', $saldo);
        $statement->bindValue(':estado', $estado);
        $statement->bindValue(':fecha', $fecha);

        $statement->execute();

        return 0;
    }

}