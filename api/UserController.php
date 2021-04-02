<?php

include_once('../classes/Usuario.php');
include_once('../classes/Cuenta.php');
include_once('../persistence/existedClientsDB.php');
include_once('../persistence/userDB.php');
include_once('../persistence/cuentaDB.php');
include_once('../database/config.php');
include_once('../database/utils.php');

class UserController {

    private $db;
    private $dbConn;
    private $dbUser;
    private $dbCuenta;
    private $dbExistedClients;

    public function __construct() {

        $this->db = dbInfo();
        $this->dbConn =  connect($this->db);

        $this->dbUser = new UserDB($this->db, $this->dbConn);
        $this->dbCuenta = new CuentaDB($this->db, $this->dbConn);
        $this->dbExistedClients = new ExistedClientsDB($this->db, $this->dbConn);

    }

    /**
     * Función que verifica si un usuario cliente tiene una cuenta bancaria
     *
     * @param $document Número de documento del usuario
     * @return Retorna al usuario si existe; en caso contrario (o si se produce una excepción) retorna false
     */
    public function validUser($document) {

        try {
            return $this->dbExistedClients->getExistedUser($document);;
        }

        catch (exception $e) {
            return false;
        }

    }

    /**
     * Función que entrega el número de cuenta bancaria de un usuario cliente
     *
     * @param $usuario Nombre de usuario del usuario cliente del cual se desea saber su número de cuenta
     * @return Retorna el número de cuenta del usuario cliente (si existe); en caso contrario retorna null; y si se produce una excepción retorna false
     */
    public function getUserAccount ($usuario) {

        try {
            $userId = $this->dbUser->getUserIdByUsername($usuario)["id"];
            $response = $this->dbCuenta->getAccountByUserId($userId);

            return $response["numero"];
        }

        catch (exception $e) {
            return false;
        }
    }

    /**
     * Funcióm para la creación de la cuenta en la aplicación de un usuario cliente (necesita tener una cuenta bancaria creada)
     *
     * @param $documento Número de documento de identidad del usuario
     * @param $tipoUsuario Tipo de usuario
     * @param $nombreUsuario Nombre de usuario
     * @param $passwd Contraseña para la cuenta
     * @return Si el usuario existe retorna una lista con su id y el tipo de usuario; de lo contrario (o si se produce una excepción) retorna false
     */
    public function createClientUser ($documento, $tipoUsuario, $nombreUsuario, $passwd) {

        try {
            /* Usuario cliente con cuenta bancaria */
            $responseExisted = $this->dbExistedClients->getExistedUser($documento);

            if ($responseExisted) {

                $usuario = new Usuario($documento, $responseExisted['nombre'], $nombreUsuario, $passwd, $tipoUsuario);

                $response = $this->dbUser->createUser($usuario);

                $cuentaOnline = new Account($responseExisted['num_cuenta'], $responseExisted['saldo'],
                    $responseExisted['tipo'], 'activa', $response['id'], 1);

                /* Se crea la cuenta de usuario de la aplicación */
                $responseCuenta = $this->dbCuenta->createAccount($cuentaOnline);

                return $response;
            }
            else {
                return false;
            }
        }

        catch (exception $e) {
            echo json_encode($e, JSON_PRETTY_PRINT);
            return false;
        }

    }

    /**
     * Función para la creación de un usuario de alto rango (admin, auditor)
     *
     * @param $documento Número de identificación para el reconocimiento del usuario
     * @param $tipoUsuario Tipo de usuario
     * @param $nombreUsuario Nombre de usuario
     * @param $passwd Contraseña para la cuenta
     * @param $nombre Nombre de la persona
     * @return Si el usuario existe retorna una lista con su id y el tipo de usuario; de lo contrario (o si se produce una excepción) retorna false
     */
    public function createHightUser ($documento, $tipoUsuario, $nombreUsuario, $passwd, $nombre) {

        try {
            $usuario = new Usuario($documento, $nombre, $nombreUsuario, $passwd, $tipoUsuario);

            return $this->dbUser->createUser($usuario);;
        }

        catch (exception $e) {
            return false;
        }

    }

    /**
     * Función para obtener la cantidad de dinero que contiene una cuenta
     *
     * @param $numCuenta Número de la cuenta bancaria
     * @return Si la cuenta existe retorna el saldo; de lo contrario (o si se produce una excepción) retorna false
     */
    public function getBalance($numCuenta){
        try {

            $responseBalance= $this->dbCuenta->getAccountBalance($numCuenta);

            if($responseBalance) {
                return $responseBalance["saldo"];
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
     * Función para modificar la cantidad de dinero que contiene una cuenta
     *
     * @param $numCuenta Número de la cuenta bancaria
     * @param $nuevoSaldo Nuevo saldo de la cuenta
     * @return Si el cambio de saldo en una cuenta existente fue exitoso retorna true; de lo contrario (o si se produce una excepción) retorna false
     */
    public function modifyBalance($numCuenta, $nuevoSaldo){
        try {

            return $this->dbCuenta->updateBalance($numCuenta, $nuevoSaldo);
        }

        catch (exception $e) {
            return false;
        }
    }

}
