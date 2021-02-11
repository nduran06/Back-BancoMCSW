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


if($page == 'overdraft') {
    // Ver si la cuenta esta creada
    // /user.php/overdraft/consult
    if ($action == 'consult') {
// consulta si la cuenta esta creada
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


                    $dbExisted = new ExistedClientsDB($db, $dbConn);
                    $dbSobre = new SobregiroDB($db, $dbConn);;
                    
                    $newAccount = $_POST['newAccount'];
                    $stateSobregiro = $_POST['stateSobregiro'];
                    $percent = $_POST['percent'];
                    
                    $responseExisted = $dbExisted->getExistedAccount($newAccount);

                    if($responseExisted != false) {
                        

                        $responseExistedSob = $dbSobre->getSobregiroByAccount($newAccount);

                        if($responseExistedSob != true) {

                              $sobregiro = new Sobregiro($newAccount, $stateSobregiro, $percent);

                              $response = $dbSobre->createSobregiro($sobregiro);

                              echo json_encode($response, JSON_PRETTY_PRINT);
                        }else  echo json_encode("Cuenta con sobrecupo activo", JSON_PRETTY_PRINT);
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

