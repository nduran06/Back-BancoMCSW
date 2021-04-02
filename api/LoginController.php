<?php

include_once('../persistence/userDB.php');
include_once('../database/config.php');
include_once('../database/utils.php');


class LoginController {

    private $db;
    private $dbConn;
    private $dbUser;

    function __construct() {

        $this->db = dbInfo();
        $this->dbConn =  connect($this->db);
        $this->dbUser = new UserDB($this->db, $this->dbConn);

    }

    public function login($usuario, $passwd) {

        try {

            $response = $this->dbUser->loginUser($usuario, $passwd);

            return $response['tipo'];
        }

        catch (exception $e) {

            return false;

        }
    }

}
