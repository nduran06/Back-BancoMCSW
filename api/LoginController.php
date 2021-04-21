<?php

include_once('../persistence/userDB.php');
include_once('../database/config.php');
include_once('../database/utils.php');
include_once('../auxiliar/cript.php');


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

            if($response){
                $validPass = validPass($passwd, trim($response['passwd']));
                
                if($validPass) {
                    return $response['tipo'];
                }

                else{
                    false;
                }
            }

            return false;
        }

        catch (exception $e) {

            return false;

        }
    }

}
