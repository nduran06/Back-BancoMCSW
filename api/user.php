<?php

include('../classes/Usuario.php');
include('../classes/Cuenta.php');
include('../persistence/existedClientsDB.php');
include('../persistence/userDB.php');
include('../persistence/cuentaDB.php');
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

if($page == 'clients') {

    // Ver si el usuario tiene una cuenta abierta
    // /user.php/clients/valid
    if ($action == 'valid') {
        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');
                    $dbExistedClients = new ExistedClientsDB($db, $dbConn);
                    $document = $_POST['documento'];
                    $response = $dbExistedClients->getExistedUser($document);
                    echo json_encode($response, JSON_PRETTY_PRINT);
                } catch (exception $e) {
                    header("HTTP/1.1 400 BAD REQUEST");
                    echo json_encode("Cuenta no encontrada", JSON_PRETTY_PRINT);
                }
                break;

            default://metodo NO soportado
                echo 'METODO NO SOPORTADO';
                break;
        }
    }

    // Crear usuario
    // /user.php/clients/add
    elseif ($action == 'add') {
        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');

                    $dbUser = new UserDB($db, $dbConn);
                    $dbCuenta = new CuentaDB($db, $dbConn);
                    $dbExisted = new ExistedClientsDB($db, $dbConn);

                    $responseExisted = $dbExisted->getExistedUser($_POST['documento']);

                    if($responseExisted != false) {

                        $usuario = new Usuario($_POST['documento'], $responseExisted['nombre'], $_POST['usuario'],
                            $_POST['passwd'], $_POST['tipo']);

                        $response = $dbUser->createUser($usuario);

                        $cuentaOnline = new Account($responseExisted['num_cuenta'], $responseExisted['saldo'],
                            $responseExisted['tipo'], 'activa', $response['id'], 1);


                        $responseCuenta = $dbCuenta->createAccount($cuentaOnline);

                        echo json_encode($response, JSON_PRETTY_PRINT);
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

            default://metodo NO soportado
                echo 'METODO NO SOPORTADO';
                break;
        }
    }

    elseif ($action == 'getBalance') {
        switch ($method) {
            case 'POST':
                try {

                    $dbCuenta = new CuentaDB($db, $dbConn);

                    $responseBalance= $dbCuenta->getAccountBalance($_POST['num_cuenta']);

                    if($responseBalance !== false) {
                        header('HTTP/1.1 200 OK');

                        echo json_encode($responseBalance, JSON_PRETTY_PRINT);
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

            default://metodo NO soportado
                echo 'METODO NO SOPORTADO';
                break;
        }
    }
    
    else {
        header("HTTP/1.1 404 BAD REQUEST");
    }

}

