<?php


include($_SERVER['DOCUMENT_ROOT'].'/persistence/userDB.php');



/*$db = dbInfo();
$dbConn =  connect($db);*/


// Crear un nuevo usuario
/*if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $input = $_POST;

    $usuario = $input['usuario'];
    $passwd = $input['passwd'];
    $tipo = $input['tipo'];

    $sql = "INSERT INTO usuario
          (usuario, passwd, tipo)
          VALUES
          (:usuario, :passwd, :tipo)";
    $statement = $dbConn->prepare($sql);

    $statement->bindValue(':usuario', $usuario);
    $statement->bindValue(':passwd', $passwd);
    $statement->bindValue(':tipo', $tipo);

    $statement->execute();


    header("HTTP/1.1 200 OK");
    echo json_encode($input);
    exit();

}*/

function pp(){
      $db = new UserDB;
      $response = $db->getUsers();
      echo json_encode($response, JSON_PRETTY_PRINT);

 }

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET'://consulta

        break;
    case 'POST'://inserta

        try {
            header('Content-Type: application/JSON');
            $db = new UserDB;
            $response = $db->createUser($_POST);
            echo json_encode($response, JSON_PRETTY_PRINT);
        }
        catch (exception $e){
            header("HTTP/1.1 400 BAD REQUEST");
            echo json_encode("datos inválidos", JSON_PRETTY_PRINT);


        }
        break;
    case 'PUT'://actualiza
        echo 'PUT';
        break;
    case 'DELETE'://elimina
        echo 'DELETE';
        break;
    default://metodo NO soportado
        echo 'METODO NO SOPORTADO';
        break;
}
