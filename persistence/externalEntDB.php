<?php

class ExternalEntDB {

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

    public function updateBalance($account, $nuevoSaldo, $nombreBanco) {

        $sql = "UPDATE cuentas_exis_otros_bancos SET saldo=:nuevoSaldo WHERE num_cuenta=:cuenta and banco=:nombreBanco";

        $stmt = $this->dbConn->prepare($sql);
        $stmt->bindValue(':nuevoSaldo', $nuevoSaldo);
        $stmt->bindValue(':cuenta', $account);
        $stmt->bindValue(':nombreBanco', $nombreBanco);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false;
    }

}