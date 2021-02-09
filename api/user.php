<?php

include('../classes/Usuario.php');
include('../persistence/userDB.php');
include('../persistence/existedClientsDB.php');


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
                    $db = new ExistedClientsDB;
                    $document = $_POST['documento'];
                    $response = $db->getExistedUser($document);
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
                    $db = new UserDB;
                    $usuario = new Usuario($_POST['documento'], $_POST['nombre'], $_POST['usuario'],
                        $_POST['passwd'], $_POST['tipo']);
                    $response = $db->createUser($usuario);
                    echo json_encode($response, JSON_PRETTY_PRINT);
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

    // Obtener usuarios
    // /user.php/clients/getALl
    elseif ($action == 'getAll'){

        switch ($method) {

            case 'POST':
                $usuario = $_POST['usuario'];
                $tipo = $_POST['tipo'];

                if($tipo == 'admin') {

                    if (isset($_GET['id'])) {
                        try {
                            header('HTTP/1.1 200 OK');
                            $db = new UserDB;
                            $response = $db->getUser($_GET['id']);
                            echo json_encode($response, JSON_PRETTY_PRINT);
                        } catch (exception $e) {
                            header("HTTP/1.1 400 BAD REQUEST");
                            echo json_encode("datos inválidos", JSON_PRETTY_PRINT);
                        } finally {
                            exit();
                        }
                    } else {
                        try {
                            header('HTTP/1.1 200 OK');
                            $db = new UserDB;
                            $response = $db->getUsers();

                            echo json_encode($response->fetchAll(), JSON_PRETTY_PRINT);
                            exit();

                        } catch (exception $e) {
                            header("HTTP/1.1 400 BAD REQUEST");
                            echo json_encode("datos inválidos", JSON_PRETTY_PRINT);
                        }
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

