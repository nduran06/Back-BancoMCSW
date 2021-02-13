<?php

include('../classes/Transaccion.php');
include('../persistence/transDB.php');
include('../persistence/userDB.php');
include('../persistence/existedClientsDB.php');
include('../database/config.php');
include('../database/utils.php');

$db = dbInfo();
$dbConn =  connect($db);

$link = $_SERVER['PHP_SELF'];
$link_array = explode('/',$link);
$page = $link_array[count($link_array)-2];
$action = end($link_array);


$method = $_SERVER['REQUEST_METHOD'];
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Disposition, Content-Type, Content-Length, Accept-Encoding");
header("Content-type:application/json");

if($page == 'transaction') {

    // Crear transacción
    // /trans.php/transaction/new
    if ($action == 'new') {
        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');

                    $dbTrans = new TransDB($db, $dbConn);

                    $date = new DateTime();

                    $trans = new Transaccion($_POST['origen'], $_POST['destino'], "MIBANCO",
                        $_POST['banco_destino'], $_POST['saldo'], "en proceso", $date->format('Y-m-d H:i:s'));

                    echo json_encode($trans->getBancoOrigen(), JSON_PRETTY_PRINT);

                    $response = $dbTrans->createTrans($trans);

                    echo json_encode($response, JSON_PRETTY_PRINT);
                }
                catch (exception $e) {
                    header("HTTP/1.1 400 BAD REQUEST");
                    echo json_encode("datos inválidos", JSON_PRETTY_PRINT);
                }
                break;

            default://metodo NO soportado
                echo 'METODO NO SOPORTADO';
                break;
        }
    }

    // Obtener todos los usuarios
    // /user.php/clients/getALl
    elseif ($action == 'getAll'){

        $dbUser = new UserDB($db, $dbConn);
        $dbTrans = new TransDB($db, $dbConn);

        switch ($method) {

            case 'POST':
                $usuario = $_POST['usuario'];

                $tipo = $dbUser->getUserByUsername($usuario)["tipo"];

                if(trim($tipo) === 'auditor') {

                    try {
                        header('HTTP/1.1 200 OK');
                        $response = $dbTrans->getAllTrans();

                        echo json_encode($response->fetchAll(), JSON_PRETTY_PRINT);
                        exit();

                    } catch (exception $e) {
                        header("HTTP/1.1 400 BAD REQUEST");
                        echo json_encode("datos inválidos", JSON_PRETTY_PRINT);
                    }
                }

                else{
                    header("HTTP/1.1 400 BAD REQUEST");
                }

                break;
        }
    }

    /* Muestra todas las transacciones que ha hecho (exitosas y rechazadas) y las transacciones que ha recibido*/
    elseif ($action == 'specific'){

        $dbUser = new UserDB($db, $dbConn);
        $dbTrans = new TransDB($db, $dbConn);

        switch ($method) {

            case 'POST':

                $numeroCuenta = $_POST['cuenta'];

                try {
                    if($numeroCuenta) {
                        header('HTTP/1.1 200 OK');
                        $response = $dbTrans->getAllUserSuccessTrans($numeroCuenta);

                        echo json_encode($response->fetchAll(), JSON_PRETTY_PRINT);
                        exit();
                    }

                    else{
                        header("HTTP/1.1 400 BAD REQUEST");
                        echo json_encode("datos inválidos", JSON_PRETTY_PRINT);
                    }

                } catch (exception $e) {
                    header("HTTP/1.1 400 BAD REQUEST");
                    echo json_encode("datos inválidos", JSON_PRETTY_PRINT);
                }

                break;
        }
    }

    /* Muestra todas las transacciones que ha hecho (exitosas y rechazadas)*/
    elseif ($action == 'actions'){

        $dbUser = new UserDB($db, $dbConn);
        $dbTrans = new TransDB($db, $dbConn);

        switch ($method) {

            case 'POST':

                $numeroCuenta = $_POST['cuenta'];

                try {
                    if($numeroCuenta) {
                        header('HTTP/1.1 200 OK');
                        $response = $dbTrans->getAllUserTrans($numeroCuenta);

                        echo json_encode($response->fetchAll(), JSON_PRETTY_PRINT);
                        exit();
                    }

                    else{
                        header("HTTP/1.1 400 BAD REQUEST");
                        echo json_encode("datos inválidos", JSON_PRETTY_PRINT);
                    }

                } catch (exception $e) {
                    header("HTTP/1.1 400 BAD REQUEST");
                    echo json_encode("datos inválidos", JSON_PRETTY_PRINT);
                }

                break;
        }
    }

    else {
        header("HTTP/1.1 404 BAD REQUEST");
    }

}

