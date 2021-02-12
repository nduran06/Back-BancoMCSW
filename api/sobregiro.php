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

