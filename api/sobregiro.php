<?php

include('../classes/sobregiro.php');
include('../persistence/cuentaDB.php');
include('../persistence/sobregiroDB.php');
include('../database/config.php');
include('../database/utils.php');

$db = dbInfo();
$dbConn =  connect($db);

$dbSobregiro = new SobregiroDB($db, $dbConn);
$dbCuenta = new CuentaDB($db, $dbConn);

$link = $_SERVER['PHP_SELF'];
$link_array = explode('/',$link);
$page = $link_array[count($link_array)-2];
$action = end($link_array);


$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/JSON');

// consulta si a cuenta esta creada

if($page == 'clients') {
    if ($action == 'consult') {
        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');

                    $account = $_POST['num_cuenta'];
                    $response =  $dbSobregiro -> getSobregirosByAccount($account);

                    echo json_encode($response->fetchAll(), JSON_PRETTY_PRINT);

                } catch (exception $e) {
                    header("HTTP/1.1 400 BAD REQUEST");
                    echo json_encode("Cuenta no encontrada", JSON_PRETTY_PRINT);
                }
                break;

            default://metodo NO soportado
                echo 'METODO NO SOPORTADO';
                break;
        }
    } // Crear sobregiro

    elseif ($action == 'add') {
        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');

                    $numCuenta = $_POST['num_cuenta'];
                    $saldo = $_POST['saldo'];

                    $date = new DateTime();

                    $cuentaValid = $dbCuenta->getAccount($numCuenta);

                    if($cuentaValid){
                        $sobregiro = new Sobregiro($numCuenta, 'en proceso', '0', $saldo, $date->format('Y-m-d H:i:s'));

                        $response = $dbSobregiro->createSobregiro($sobregiro);
                        echo json_encode($response, JSON_PRETTY_PRINT);
                    }

                    else {
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
    } else {
        header("HTTP/1.1 404 BAD REQUEST");
    }
}

elseif($page == 'overdraft') {

    // /user.php/overdraft/consult
    if ($action == 'consult') {

        // consulta si hay sobregiros pendientes por definir (administrador)
        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');
                    $dbExistedClients = new SobregiroDB($db, $dbConn);
                    $state=$_POST['state'];
                    $response = $dbExistedClients->getSobregiroByState($state);
                    echo json_encode($response->fetchAll(), JSON_PRETTY_PRINT);
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

    elseif ($action == 'update') {

        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');

                    $sobregiroId = $_POST['id'];
                    $sobregiroState = $_POST['estado'];

                    $response = $dbSobregiro -> updateSobregiroState($sobregiroId, $sobregiroState);
                    echo json_encode($response, JSON_PRETTY_PRINT);
                }

                catch (exception $e) {
                    header("HTTP/1.1 400 BAD REQUEST");
                    echo json_encode("Cuenta no encontrada", JSON_PRETTY_PRINT);
                }
                break;

            default://metodo NO soportado
                echo 'METODO NO SOPORTADO';
                break;
        }
    }

    // Defino un sobregiro si está pendientes (Administrador)
    elseif ($action == 'add') {
        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');


                    $dbExisted = new SobregiroDB($db, $dbConn);
                    $dbSobre = new SobregiroDB($db, $dbConn);;

                    $newAccount = $_POST['newAccount'];
                    $stateSobregiro = $_POST['stateSobregiro'];
                    $percent = $_POST['percent'];

                    $responseExisted = $dbExisted->getSobregiroByAccount($newAccount); //valido si hay solicitud de la cuenta

                    if($responseExisted != false) {

                        $state='Pendiente';
                        $responseExistedPend = $dbSobre->getSobregiroByAccount_State($newAccount, $state); //valido si el estado de esa solicitud está pendiente

                        if($responseExistedPend != false) {

                            $sobregiro = new Sobregiro($newAccount, $stateSobregiro, $percent);

                            $response = $dbSobre->updateSobregiro($sobregiro);

                            echo json_encode($response, JSON_PRETTY_PRINT);
                        } else echo 'El estado del sobregiro ya fue definido';
                    }
                    else{
                        header("HTTP/1.1 400 BAD REQUEST");
                        echo json_encode("La cuenta no esta creada", JSON_PRETTY_PRINT);
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

        //hago la solicitud del sobrecupo (Usuario)
    }elseif ($action == 'request') {
        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');


                    $dbExisted = new CuentaDB($db, $dbConn);
                    $dbSobre = new SobregiroDB($db, $dbConn);;

                    $newAccount = $_POST['account'];
                    $stateSobregiro = 'Pendiente';
                    $percent = null;

                    $responseExisted = $dbExisted->getAccount($newAccount);   //valido si la cuentaesta creada en la app

                    if($responseExisted != false) {


                        $responseExistedSob = $dbSobre->getSobregiroByAccount($newAccount); //valido si ya se izo la solicitud del sobrecupo

                        if($responseExistedSob != true) {

                            $sobregiro = new Sobregiro($newAccount, $stateSobregiro, $percent);

                            $response = $dbSobre->createSobregiro($sobregiro);   //reo la solicitud del sobrecupo

                            echo json_encode($response, JSON_PRETTY_PRINT);
                        } else echo 'Esta cuenta ya tiene un sobregiro activo';
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
