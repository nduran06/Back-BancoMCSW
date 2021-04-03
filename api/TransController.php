<?php

include_once('../classes/Transaccion.php');
include_once('../persistence/userDB.php');
include_once('../persistence/CuentaDB.php');
include_once('../persistence/existedClientsDB.php');
include_once('../persistence/transDB.php');
include_once('../database/config.php');
include_once('../database/utils.php');

class TransController {

    private $db;
    private $dbConn;
    private $dbUser;
    private $dbCuenta;
    private $dbExistedClients;
    private $dbTrans;

    public function __construct() {

        $this->db = dbInfo();
        $this->dbConn =  connect($this->db);

        $this->dbUser = new UserDB($this->db, $this->dbConn);
        $this->dbCuenta = new CuentaDB($this->db, $this->dbConn);
        $this->dbExistedClients = new ExistedClientsDB($this->db, $this->dbConn);
        $this->dbTrans = new TransDB($this->db, $this->dbConn);

    }

    /**
     * Función ṕara crear una nueva transacción
     *
     * @param $cuentaOrigen Cuenta bancaria desde la que se realiza la transacción
     * @param $cuentaDestino Cuenta bancaria que recibe la transacción
     * @param $bancoDestino Nombre del banco al que pertenece la cuenta bancaria de destino
     * @param $saldo Cantidad de dinero que desea transferirse
     * @return Si se realiza la transacción independientemente de su estado retorna dicha transacción; si se produce un error retorna false
     *
     */
    public function createTransaction($cuentaOrigen, $cuentaDestino, $bancoDestino, $saldo) {

        try {
            $date = new DateTime();

            $trans = new Transaccion($cuentaOrigen, $cuentaDestino, "MIBANCO", $bancoDestino, $saldo, "en proceso", $date->format('Y-m-d H:i:s'));

            return $this->dbTrans->createTrans($trans);
        }

        catch (exception $e) {
            return false;
        }
    }

    /**
     * Función para obtener todas las transacciones que ha hecho un usuario (exitosas y rechazadas) y las transacciones que ha recibido
     *
     * @param $usuario Nombre de usuario
     * @return Retorna una lista de los movimientos del usuario (si existe); en caso contrario (o si se produce una excepción) retorna false
     *
     */
    public function getAllUserOper($usuario) {
        try {
            $userId = $this->dbUser->getUserIdByUsername($usuario)["id"];
            $numeroCuenta = trim($this->dbCuenta->getAccountByUserId($userId)["numero"]);

            if($numeroCuenta) {

                return $this->dbTrans->getAllUserSuccessTrans($numeroCuenta)->fetchAll();
            }

            else{
                return false;
            }
        }
        catch (exception $e) {
            return false;
        }
    }

    /**
     * Función para obtener todas las transacciones que ha realizado el usuario (exitosas y rechazadas)
     *
     * @param $usuario Nombre de usuario
     * @return false Retorna una lista de los movimientos del usuario (si existe); en caso contrario (o si se produce una excepción) retorna false
     */
    public function getAllUserTrans($usuario) {
        try {
            $userId = $this->dbUser->getUserIdByUsername($usuario)["id"];
            $numeroCuenta = trim($this->dbCuenta->getAccountByUserId($userId)["numero"]);

            if($numeroCuenta) {

                return $this->dbTrans->getAllUserTrans($numeroCuenta)->fetchAll();
            }

            else{
                return false;
            }

        }
        catch (exception $e) {
            return false;
        }
    }

    /**
     * Función para obtener todas las transacciones existentes en la base de datos
     *
     * @return Retorna una lista con las transacciones existente; Si se produce una excepción retorn false
     *
     */
    public function getAllTrans($usuario) {
        try {
            $rolesValidos = array("auditor", "admin");

            $user = $this->dbUser->getUserByUsername($usuario);

            if(in_array(trim($user["tipo"]), $rolesValidos)) {
                return $this->dbTrans->getAllTrans()->fetchAll();
            }

            else {
                return false;
            }

        }
        catch (exception $e) {
            return false;
        }
    }
}

