<?php

class CuentaDB {

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

    public function getAccount($cuenta) {
        $sql = "SELECT * FROM cuenta WHERE numero=:cuenta";

        $statement = $this->dbConn->prepare($sql);
        $statement->bindValue(':cuenta', $cuenta);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);

    }
    public function createAccount($cuenta) {

        $num_cuenta = $cuenta->getNumber();
        $tipo = $cuenta->getType();
        $saldo = $cuenta->getBalance();
        $estado = $cuenta->getState();
        $id_prop = $cuenta->getIdUsuario();
        $id_banco = $cuenta->getIdBanco();


        $sql = "INSERT INTO cuenta
                  (numero, tipo, saldo, estado, id_usuario, id_banco)
                  VALUES
                  (:numero, :tipo, :saldo, :estado, :id_usuario, :id_banco)";

        $statement = $this->dbConn->prepare($sql);

        $statement->bindValue(':numero', $num_cuenta);
        $statement->bindValue(':tipo', $tipo);
        $statement->bindValue(':saldo', $saldo);
        $statement->bindValue(':estado', $estado);
        $statement->bindValue(':id_usuario', $id_prop);
        $statement->bindValue(':id_banco', $id_banco);

        $statement->execute();

        return $sql->setFetchMode(PDO::FETCH_ASSOC);
    }
    
    
    

    public function getAccountBalance($cuenta) {

        $sql = "SELECT saldo FROM cuenta WHERE numero=:cuenta";

        $statement = $this->dbConn->prepare($sql);
        $statement->bindValue(':cuenta', $cuenta);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function updateBalance($account, $nuevoSaldo) {

        $sql = "UPDATE cuenta SET saldo=:nuevoSaldo WHERE numero=:cuenta";

        $stmt = $this->dbConn->prepare($sql);
        $stmt->bindValue(':nuevoSaldo', $nuevoSaldo);
        $stmt->bindValue(':cuenta', $account);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false;
    }

}
