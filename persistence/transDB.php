<?php

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

            $sql = $this->dbConn->prepare("SELECT * FROM transaccion where origen =:origen");

            $sql->bindValue(':origen', $numeroCuenta);

            $sql->execute();
            $sql->setFetchMode(PDO::FETCH_ASSOC);

            return $sql;
        }

        // retorna true o false
        public function getValidAccount($numCuenta){

            $sql = "SELECT * FROM cuenta WHERE numero=:numCuenta";

            $statement = $this->dbConn->prepare($sql);

            $statement->bindValue(':numCuenta', $numCuenta);

            $statement->execute();
            $result =  $statement->fetch(PDO::FETCH_ASSOC);

            return $result;
        }

        // retorna true o false
        public function getValidAccountOtherBack($numCuenta, $nombreBanco){

            $sql = "SELECT * FROM cuentas_exis_otros_bancos WHERE num_cuenta = :numCuenta AND banco = :nombreBanco";

            $statement = $this->dbConn->prepare($sql);

            $statement->bindValue(':numCuenta', $numCuenta);
            $statement->bindValue(':nombreBanco', $nombreBanco);

            $statement->execute();
            $result =  $statement->fetch(PDO::FETCH_ASSOC);

            return $result;
        }

        private function string_curr_to_num($curr){
            $stringSaldoCuenta = str_replace(str_split('$,'), '', $curr);

            return floatval($stringSaldoCuenta);
        }

        public function createTrans($trans){

            $origen = $trans->getOrigen();
            $destino = $trans->getDestino();
            $banco_origen = $trans->getBancoOrigen();
            $banco_destino = $trans->getBancoDestino();
            $saldo = $trans->getSaldo();

            $fecha = $trans->getFecha();

            $validoOri = $this->getValidAccount($origen);
            $validoDes = $banco_origen === $banco_destino ?
                $this->getValidAccount($destino) : $this->getValidAccountOtherBack($destino, $banco_destino);

            echo($validoOri['saldo']);
            echo($validoDes['saldo']);

            $nuevoSaldoMenos = $validoOri['saldo'] - $saldo;
            $nuevoSaldoMas = $validoDes['saldo'] + $saldo;

            echo($nuevoSaldoMenos);
            echo($nuevoSaldoMas);
            if($validoOri['saldo'] > 0 and $validoOri['saldo'] >= $saldo) {
                $sqlOri = "UPDATE cuenta SET saldo=saldo-:nuevoSaldoMenos WHERE numero=:cuentaOrigen";
                $statementOri = $this->dbConn->prepare($sqlOri);
                $statementOri->bindValue(':nuevoSaldoMenos', $nuevoSaldoMenos);
                $statementOri->bindValue(':cuentaOrigen', $origen);
                $statementOri->execute();

                if( $banco_origen === $banco_destino) {
                    $sqlDes = "UPDATE cuenta SET saldo=:nuevoSaldoMas WHERE numero=:cuentaDestino";
                    $statementDes = $this->dbConn->prepare($sqlDes);
                    $statementDes->bindValue(':nuevoSaldoMas', $nuevoSaldoMas);
                    $statementDes->bindValue(':cuentaDestino', $destino);
                    $statementDes->execute();
                }

            }

            else {
                $validoOri->false;
            }

            $estado = ($validoOri and $validoDes and $validoOri >= $saldo
                and $banco_origen !== $banco_destino) ? 'exitosa' : 'rechazada';

            $trans->setEstado($estado);

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

            return $trans->toArray();
        }

    }