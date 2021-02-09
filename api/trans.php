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
header('Content-Type: application/JSON');

if($page == 'transaction') {

    // Crear transacción
    // /trans.php/transaction/new
    if ($action == 'new') {
        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');
                    $db = new TransDB;

                    $date = new DateTime();

                    $trans = new Transaccion($_POST['origen'], $_POST['destino'], $_POST['banco_origen'],
                        $_POST['banco_destino'], $_POST['saldo'], $_POST['estado'], $date->format('Ymd\Thms'));

                    $response = $db->createTrans($trans);

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

    // Obtener usuarios
    // /user.php/clients/getALl
    elseif ($action == 'getAll'){

        $db = new TransDB;

        switch ($method) {

            case 'POST':
                $usuario = $_POST['usuario'];

                $tipo = $db->getUserByUsername($usuario);

                if($tipo == 'auditor') {

                    try {
                        header('HTTP/1.1 200 OK');
                        $response = $db->getUsers();

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

    else {
        header("HTTP/1.1 404 BAD REQUEST");
    }

}

