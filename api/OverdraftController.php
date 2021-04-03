<?php

include_once('../classes/sobregiro.php');
include_once('../persistence/cuentaDB.php');
include_once('../persistence/sobregiroDB.php');
include_once('../persistence/userDB.php');
include_once('../database/config.php');
include_once('../database/utils.php');

class OverdraftController {

    private $db;
    private $dbConn;
    private $dbUser;
    private $dbCuenta;
    private $dbSobregiro;

    public function __construct() {

        $this->db = dbInfo();
        $this->dbConn =  connect($this->db);

        $this->dbUser = new UserDB($this->db, $this->dbConn);
        $this->dbCuenta = new CuentaDB($this->db, $this->dbConn);
        $this->dbSobregiro = new SobregiroDB($this->db, $this->dbConn);
    }

    /**
     * Función para encontrar los sobregiros creados por un usuario
     *
     * @param $account Número de cuenta bancaria del usuario
     * @return Retorna la lista de sobregiros creadas por un usuario cliente; si se produce una excepción retorna false
     *
     */
    public function getUserOverdrafts($account){

        try {
            $response =  $this->dbSobregiro -> getSobregirosByAccount($account);
            return $response->fetchAll();
        }

        catch (exception $e) {
            return false;
        }
    }

    /**
     * Función para crear un nuevo sobregiro
     *
     * @param $numCuenta Número de la cuenta bancaria sobre la que se creará el sobregiro
     * @param $saldo Saldo pedido en el sobregiro
     * @return Retorna la información del sobregiro creado satisfactoriamente por un usuario cliente; de lo contrario (o si se produce una excepción) retorna false
     *
     */
    public function createUserOverdraft($numCuenta, $saldo){

        try {
            // Verifica si el número de cuenta bancaria existe
            $cuentaValid = $this->dbCuenta->getAccount($numCuenta);

            if($cuentaValid) {
                $date = new DateTime();

                $sobregiro = new Sobregiro($numCuenta, 'en proceso', '0', $saldo, $date->format('Y-m-d H:i:s'));

                return $this->dbSobregiro->createSobregiro($sobregiro);;
            }

            else {
                return false;
            }
        }
        catch (exception $e) {
            return false;

        }
    }

    /**
     * Función para obtener todos los sobregiros que han sido creados
     *
     * @return Retorna una lista con los sobregiros; si se produce una excepción retorna false
     *
     */
    public function getAllOverdrafts($usuario){

        try {
            $rolesValidos = array("auditor", "admin");

            $user = $this->dbUser->getUserByUsername($usuario);

            if(in_array(trim($user["tipo"]), $rolesValidos)) {
                return $this->dbSobregiro->getSobregiros()->fetchAll();
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
     * Función para actualizar el estado y el porcentaje de aprobación (opcional) de un sobregiro
     *
     * @param $idSobregiro Id del sobregiro
     * @param $estadoSobregiro Estado del sobregiro
     * @param $porcentaje Porcentaje de aprobación
     * @return Retorna true si la actualización del estado se realizó correctamente; de lo contrario (o si se produce una excepción) retorna false
     */
    public function updateOverdraft($idSobregiro, $estadoSobregiro, $porcentaje, $usuario){

        try {

            $rolesValidos = array("auditor", "admin");

            $user = $this->dbUser->getUserByUsername($usuario);

            if(in_array(trim($user["tipo"]), $rolesValidos)) {

                $estadosValidos = array("en proceso", "aprobado", "rechazado");

                $realPerc = 100;

                if(in_array($estadoSobregiro, $estadosValidos)){

                    if($estadoSobregiro === "aprobado"){
                        $realPerc = $porcentaje;
                    }

                    if($realPerc > 0 and $realPerc <= 100) {

                        if($estadoSobregiro === "en proceso"){
                            $realPerc = 0;
                        }

                        return $this->dbSobregiro -> updateSobregiroState($idSobregiro, $estadoSobregiro, $realPerc);
                    }
                }

                else {
                    return false;
                }
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