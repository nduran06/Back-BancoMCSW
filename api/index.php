<?php

include($_SERVER['DOCUMENT_ROOT'].'/database/config.php');
include($_SERVER['DOCUMENT_ROOT'].'/database/utils.php');
$db = [
    'host' => 'ec2-54-225-190-241.compute-1.amazonaws.com',
    'username' => 'vfjfgyrqwnprtl',
    'password' => '028192792e4e5a57e671a4e97de8291d4f12cd211a684d70a16f93e339f06570',
    'db' => 'd29grojbmdsft4'
];

$dbConn =  connect($db);
/*
  listar todos los posts o solo uno
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if (isset($_GET['id']))
    {
        //Mostrar un post
        $sql = $dbConn->prepare("SELECT * FROM transaccion");

        $sql->execute();
        header("HTTP/1.1 200 OK");
        echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
        exit();
    }
    else {
        //Mostrar lista de post
        $sql = $dbConn->prepare("SELECT * FROM transaccion");
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode( $sql->fetchAll()  );
        exit();
    }
}