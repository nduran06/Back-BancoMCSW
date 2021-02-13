<?php

include('../classes/Usuario.php');
include('../persistence/userDB.php');
include('../persistence/existedClientsDB.php');
include('../database/config.php');
include('../database/utils.php');

$db = dbInfo();
$dbConn =  connect($db);

// /login.php

$method = $_SERVER['REQUEST_METHOD'];
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Disposition, Content-Type, Content-Length, Accept-Encoding");
header("Content-type:application/json");

switch ($method) {
    case 'POST':
        try {
            header('HTTP/1.1 200 OK');
            $db = new UserDB($db, $dbConn);

            $usuario = $_POST['usuario'];
            $passwd = $_POST['passwd'];

            $response = $db->loginUser ($usuario, $passwd);

            echo json_encode($response['tipo'], JSON_PRETTY_PRINT);

        }
        catch (exception $e) {
            header("HTTP/1.1 400 BAD REQUEST");
            echo json_encode("datos inválidos", JSON_PRETTY_PRINT);
        }

        finally {
            exit();
        }

    default:
        header("HTTP/1.1 400 BAD REQUEST");
        echo 'METODO NO SOPORTADO';
        break;
}


