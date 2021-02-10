<?php

include('../classes/Cuenta.php');
include('../persistence/accountDB.php');
include('../database/config.php');
include('../database/utils.php');

$db = dbInfo();
$dbConn =  connect($db);

$link = $_SERVER['PHP_SELF'];
$link_array = explode('/',$link);
$page = $link_array[count($link_array)-2];
$action = end($link_array);


$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/JSON');

if($page == 'account') {

    // Crear cuenta
    // /trans.php/account/new
    if ($action == 'new') {
        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');

                    $dbTrans = new accountDB($db, $dbConn);

                    $date = new DateTime();

                    $account = new Account($_POST['accNumber'], $_POST['money'], $_POST['type'], $_POST['state'], $_POST['userID'], "MIBANCO");

                    
                }
                catch (exception $e) {
                    header("HTTP/1.1 400 BAD REQUEST");
                    echo json_encode($e, JSON_PRETTY_PRINT);
                }
                break;

            default://metodo NO soportado
                echo 'METODO NO SOPORTADO';
                break;
        }
    }
    
    else {
        header("HTTP/1.1 404 BAD REQUEST");
    }

}
