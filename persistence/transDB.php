<?php

include('cuentaDB.php');
include('externalEntDB.php');


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

        public function getAllUserSuccessTrans($numeroCuenta){

            $sql = $this->dbConn->prepare("SELECT * FROM transaccion 
                                            where origen=:origen or (destino =:origen and estado=:validEstado)");

            $sql->bindValue(':origen', $numeroCuenta);
            $sql->bindValue(':validEstado', "exitosa");

            $sql->execute();
            $sql->setFetchMode(PDO::FETCH_ASSOC);

            return $sql;
        }

        public function getAllUserTrans($numeroCuenta){

            $sql = $this->dbConn->prepare("SELECT * FROM transaccion where origen=:origen");

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

            $saldoValidoOri = $this->string_curr_to_num($validoOri['saldo']);
            $saldoValidoDes = $this->string_curr_to_num($validoDes['saldo']);
            $numSaldo = $this->string_curr_to_num($saldo);

            $nuevoSaldoMenos = $saldoValidoOri - $numSaldo;
            $nuevoSaldoMas = $saldoValidoDes + $numSaldo;


            if($origen !== $destino and $validoOri and $validoDes and $saldoValidoOri > 0 and $saldoValidoOri >= $numSaldo) {

                $dbCuenta = new CuentaDB($this->db, $this->dbConn);

                $oriAns = $dbCuenta->updateBalance($origen, $nuevoSaldoMenos);
                $trans->setEstado($oriAns ? 'exitosa' : 'rechazada');

                if ($banco_origen === $banco_destino) {

                    $desAns = $dbCuenta->updateBalance($destino, $nuevoSaldoMas);
                    $trans->setEstado($desAns ? 'exitosa' : 'rechazada');

                } else {

                    $dbCuentaExterna = new ExternalEntDB($this->db, $this->dbConn);

                    $desAns = $dbCuentaExterna->updateBalance($destino, $nuevoSaldoMas, $banco_destino);
                    $trans->setEstado($desAns ? 'exitosa' : 'rechazada');
                }

            }

            else {
                $trans->setEstado('rechazada');
            }

            $estado = $trans->getEstado();

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