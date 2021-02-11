<?php

include('../classes/sobregiro.php');
include('../persistence/existedClientsDB.php');
include('../persistence/sobregiroDB.php');
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

// consulta si a cuenta esta creada
    if ($action == 'consult') {
        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');
                    $dbExistedClients = new ExistedClientsDB($db, $dbConn);
                    $newAccount = $_POST['newAccount'];
                    $response = $dbExistedClients->getExistedAccount($newAccount);
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

    // Crear sobregiro
    
    elseif ($action == 'add') {
        switch ($method) {
            case 'POST':
                try {
                    header('HTTP/1.1 200 OK');

                    $dbUser = new UserDB($db, $dbConn);;
                    $dbExisted = new ExistedClientsDB($db, $dbConn);

                    $responseExisted = $dbExisted->getExistedAccount($_POST['$newAccount']);
                    
                    if($responseExisted != false) {
                      
                        $responseExistedSobregiro = $dbExisted-> getSobregiroByAccount($_POST['$newAccount']);
                      
                        if($responseExistedSobregiro != true) {
                          
                              $sobregiro = new Sobregiro($_POST['$newAccount'], $_POST['stateSobregiro'], $_POST['percent']);
                              $response = $dbUser->createSobregiro($sobregiro);

                              echo json_encode($response, JSON_PRETTY_PRINT);
                        }
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


