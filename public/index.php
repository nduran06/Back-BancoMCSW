<?php

//lineas agregadas -> 53 y 192
//lineas modificadas -> 55 , 199 y 202

require __DIR__ . '/../bootstrap.php';

include_once('../api/LoginController.php');
include_once('../api/UserController.php');
include_once('../api/OverdraftController.php');
include_once('../api/TransController.php');
include_once('../client/OKTAToken.php');
include_once('../auxiliar/cript.php');

use \Firebase\JWT\JWT;
use \Firebase\JWT\JWK;
use \Klein\Klein;

// send some CORS headers so the API can be called from anywhere
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


/* Manejo de rutas */

$klein = new Klein();

/**
 * /MiBanco
 */
$klein->with('/MiBanco', function () use ($klein) {


    /**
     * /MiBanco/login
     *
     * Peticón para loggearse en la aplicación
     * Parámetros HTTP: usuario -> Nombre de usuario
     *                  passwd -> Contraseña del usuario que quiere loggearse
     *
     */
    $klein->respond('POST', '/login', function ($request, $response) {

        try {
            $userLogin = new LoginController();

            $_POST = json_decode(array_keys($_POST)[0], true);
            $user = sanitizeParameter($_POST, 'usuario');
            $pass = sanitizeParameter($_POST, 'passwd');

            $userRole = $userLogin->login($user, $pass);

            if ($userRole) {
                $userController = new UserController();
                $numCuenta = $userController->getUserAccount($user);

                $oktaAuth = new OKTAToken();
                $token = $oktaAuth->getValidToken();

                $ans = [
                    'role' => $userRole,
                    'token' => $token,
                    'cuenta' => $numCuenta
                ];

                echo json_encode($ans, JSON_PRETTY_PRINT);
            }

            else {
                header("HTTP/1.1 404 Unauthorized");
                echo json_encode(null, JSON_PRETTY_PRINT);
            }
        } catch (Exception $e) {
            header("HTTP/1.1 404 Bad Request");
            echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
        }

        finally {
            exit();
        }
    });

    /**
     * /MiBanco/signup
     *
     * Petición para registrar un nuevo usuario en la app (tiene que tener una cuenta bancaria (cuenta_existente) en MiBanco)
     * Parámetros HTTP: documento -> Número del documento de identidad del posible nuevo usuario
     *                               usuario -> Nombre de usuario
     *                               passwd -> Contraseña del usuario que quiere crear una cuenta
     *
     */
    $klein->respond('POST', '/signup', function ($request, $response) {

            try {
                $_POST = json_decode(array_keys($_POST)[0], true);
                $documento = sanitizeParameter($_POST, 'documento');
                $nombreUsuario = sanitizeParameter($_POST, 'usuario');
                $passwd = sanitizeParameter($_POST, 'passwd');

                $userController = new UserController();

                $existedUser = $userController->validUser($documento);

                if ($existedUser) {

                    $existedUser = $userController->createClientUser($documento, 'cliente', $nombreUsuario, $passwd);

                    echo json_encode($existedUser, JSON_PRETTY_PRINT);
                } else {
                    header("HTTP/1.1 400 Bad Request");
                    echo json_encode(true, JSON_PRETTY_PRINT);
                }
            } catch (Exception $e) {
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
            }

            finally {
                exit();
            }
    });

    /**
     * /MiBanco/user/getAccount
     *
     * Petición para obtener el número de cuenta bancaria de un usuario (cliente)
     * Parámetros HTTP: usuario -> Nombre de usuario del cual se desea saber su número de cuenta
     *
     */
    $klein->respond('POST', '/user/client/getAccount', function ($request, $response) {

        if(authenticate()) {
            try {

                $_POST = json_decode(array_keys($_POST)[0], true);
                $usuario = sanitizeParameter($_POST, 'usuario');

                $userController = new UserController();
                $existedUser = $userController->getUserAccount($usuario);

                if ($existedUser) {

                    echo json_encode($existedUser, JSON_PRETTY_PRINT);
                } else {
                    header("HTTP/1.1 404 Bad Request");
                    echo json_encode(null, JSON_PRETTY_PRINT);
                }
            } catch (Exception $e) {
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
            }

            finally {
                exit();
            }
        }

        else {
            header("HTTP/1.1 401 Unauthorized");
            exit('Unauthorized');
        }
    });

    /**
     * /MiBanco/user/new
     *
     * Petición para crear un nuevo usuario en la aplicación
     * Parámetros HTTP: tipo -> Tipo de usuario
     *                  documento -> Número de documento del nuevo usuario
     *                  nombre -> Nombre de la persona (si es un cliente no es necesario)
     *                  usuario -> Nombre de usuario de la aplicación
     *                  passwd -> Contraseña de loggeo en la aplicación
     *
     */
    $klein->respond('POST', '/user/new', function ($request, $response) {

        if(authenticate()) {
            try {
                $_POST = json_decode(array_keys($_POST)[0], true);
                $tipoUsuario = sanitizeParameter($_POST, 'tipo');
                $docUsuario = sanitizeParameter($_POST, 'documento');
                $nombreUsuario = sanitizeParameter($_POST, 'nombre');
                $userUsuario = sanitizeParameter($_POST, 'usuario');
                $passUsuario = sanitizeParameter($_POST, 'passwd');

                $userController = new UserController();

                $newUserResp = null;

                if ($tipoUsuario === "cliente") {
                    $newUserResp = $userController->createClientUser($docUsuario, $tipoUsuario, $userUsuario, $passUsuario);

                } elseif ($tipoUsuario === "admin" or $tipoUsuario === "auditor") {
                    $newUserResp = $userController->createHightUser($docUsuario, $tipoUsuario, $userUsuario, $passUsuario, $nombreUsuario);
                }

                if ($newUserResp) {
                    echo json_encode($newUserResp, JSON_PRETTY_PRINT);

                } else {
                    header("HTTP/1.1 404 Bad Request");
                    echo json_encode(null, JSON_PRETTY_PRINT);
                }

            } catch (Exception $e) {
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
            }

            finally {
                exit();
            }
        }

        else {
            header("HTTP/1.1 401 Unauthorized");
            exit('Unauthorized');
        }
    });

    /**
     *  /MiBanco/user/getBalance
     *
     * Petición para obtener el dinero que posee un usuario cliente en su cuenta
     * Parámetros HTTP: usuario -> Nombre de usuario del cliente
     *
     */
    $klein->respond('POST', '/user/getBalance', function ($request, $response) {

        if(authenticate()) {
            try {
                $_POST = json_decode(array_keys($_POST)[0], true);
                $numCuenta = sanitizeParameter($_POST, 'num_cuenta');

                $userController = new UserController();

                $balance = $userController->getBalance($numCuenta);

                if ($balance) {
                    echo json_encode($balance, JSON_PRETTY_PRINT);
                } else {
                    header("HTTP/1.1 404 Bad Request");
                    echo json_encode(null, JSON_PRETTY_PRINT);
                }



            } catch (Exception $e) {
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
            }

            finally {
                exit();
            }
        }

        else {
            header("HTTP/1.1 401 Unauthorized");
            exit('Unauthorized');
        }
    });

    /**
     * /MiBanco/user/modifyBalance
     *
     * Petición para modificar el saldo de una cuenta
     * Parámetros HTTP: num_cuenta -> Número de la cuenta bancaria
     *                  saldo -> Nuevo saldo de la cuenta
     *
     */
    $klein->respond('POST', '/user/modifyBalance', function ($request, $response) {
        if(authenticate()) {
            try {
                $_POST = json_decode(array_keys($_POST)[0], true);
                $num_cuenta = sanitizeParameter($_POST, 'num_cuenta');
                $saldo = sanitizeParameter($_POST, 'saldo');

                $userController = new UserController();

                $newUserResp = $userController->modifyBalance($num_cuenta, $saldo);

                if ($newUserResp) {
                    echo json_encode($newUserResp, JSON_PRETTY_PRINT);
                }
                else {
                    header("HTTP/1.1 404 Bad Request");
                    echo json_encode(null, JSON_PRETTY_PRINT);
                }

            }
            catch (Exception $e) {
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
            }
            finally {
                exit();
            }
        }

        else {
            header("HTTP/1.1 401 Unauthorized");
            exit('Unauthorized');
        }

    });

    /**
     * /MiBanco/user/overdraft/consult
     *
     * Petición para obtener los sobregiros de un usuario cliente
     * Parámetros HTTP: num_cuenta -> Número de la cuenta bancaria del usuario
     *
     */
    $klein->respond('POST', '/user/overdraft/consult', function ($request, $response) {

        if(authenticate()) {
            try {

                $_POST = json_decode(array_keys($_POST)[0], true);
                $num_cuenta = sanitizeParameter($_POST, 'num_cuenta');

                $overdraftController = new OverdraftController();

                $userOverdrafts = $overdraftController->getUserOverdrafts($num_cuenta);

                if ($userOverdrafts) {
                    echo json_encode($userOverdrafts, JSON_PRETTY_PRINT);
                }
                else {
                    header("HTTP/1.1 404 Bad Request");
                    echo json_encode(null, JSON_PRETTY_PRINT);
                }
            }

            catch (Exception $e) {
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
            }
            finally {
                exit();
            }
        }

        else {
            header("HTTP/1.1 401 Unauthorized");
            exit('Unauthorized');
        }

    });

    /**
     * /MiBanco/user/overdraft/new
     *
     * Petición para crear un nuevo sobregiro
     * Parámetros HTTP: num_cuenta -> Número de la cuenta bancaria del usuario
     *                  saldo -> Saldo solicitado paa el sobregiro
     *
     */
    $klein->respond('POST', '/user/overdraft/new', function ($request, $response) {

        if(authenticate()) {
            try {
                $_POST = json_decode(array_keys($_POST)[0], true);
                $num_cuenta = sanitizeParameter($_POST, 'num_cuenta');
                $saldo = sanitizeParameter($_POST, 'saldo');

                $overdraftController = new OverdraftController();

                $userOverdrafts = $overdraftController->createUserOverdraft($num_cuenta, $saldo);

                if ($userOverdrafts) {
                    echo json_encode($userOverdrafts, JSON_PRETTY_PRINT);
                } else {
                    header("HTTP/1.1 404 Bad Request");
                    echo json_encode(null, JSON_PRETTY_PRINT);
                }
            }

            catch (Exception $e) {
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
            }
            finally {
                exit();
            }
        }

        else {
            header("HTTP/1.1 401 Unauthorized");
            exit('Unauthorized');
        }

    });

    /**
     * /MiBanco/overdraft/getAll
     *
     * Petición para obtener todos los sobregiros que han sido creados
     * Parámetros HTTP: usuario -> Nombre de usuario de quien realiza la petición
     *
     */
    $klein->respond('POST', '/overdraft/getAll', function ($request, $response) {

        if(authenticate()) {
            try {
                $_POST = json_decode(array_keys($_POST)[0], true);
                $usuario = sanitizeParameter($_POST, 'usuario');

                $overdraftController = new OverdraftController();

                $userOverdrafts = $overdraftController->getAllOverdrafts($usuario);

                if ($userOverdrafts) {
                    echo json_encode($userOverdrafts, JSON_PRETTY_PRINT);
                } else {
                    header("HTTP/1.1 404 Bad Request");
                    echo json_encode(null, JSON_PRETTY_PRINT);
                }
            }

            catch (Exception $e) {
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
            }

            finally {
                exit();
            }
        }

        else {
            header("HTTP/1.1 401 Unauthorized");
            exit('Unauthorized');
        }

    });

    /**
     * /MiBanco/overdraft/update
     *
     * Petición para actualizar el estado y porcentaje (si se aprueba) de un sobregiro
     * Parámetros HTTP: id -> Id del sobregiro
     *                  estado -> Nuevo estado del sobregiro
     *                  porcentaje -> Porcentaje de aprobación (sólo es necesario si se aprueba el sobregiro)
     *                  usuario -> Nombre de usuario de quien realiza la petición
     *
     */
    $klein->respond('POST', '/overdraft/update', function ($request, $response) {
        if(authenticate()) {
            try {
                $_POST = json_decode(array_keys($_POST)[0], true);
                $id = sanitizeParameter($_POST, 'id');
                $estado = sanitizeParameter($_POST, 'estado');
                $porcentaje = sanitizeParameter($_POST, 'porcentaje');
                $usuario = sanitizeParameter($_POST, 'usuario');

                $overdraftController = new OverdraftController();

                $updatedOverdraft = $overdraftController->updateOverdraft($id, $estado, $porcentaje, $usuario);

                if($updatedOverdraft){
                    echo json_encode($updatedOverdraft, JSON_PRETTY_PRINT);
                }

                else{
                    header("HTTP/1.1 404 Bad Request");
                    echo json_encode(null, JSON_PRETTY_PRINT);
                }
            }

            catch (Exception $e){
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
            }
            finally {
                exit();
            }
        }

        else {
            header("HTTP/1.1 401 Unauthorized");
            exit('Unauthorized');
        }

    });

    /**
     * /MiBanco/user/transaction/new
     *
     * Petición para crear una nueva transacción
     * Parámetros HTTP: origen -> Cuenta bancaria que realiza la transacción
     *                  destino -> Cuenta bancaria que recibe la transacción
     *                  banco_destino -> Nombre del banco al que pertenece la cuenta bancaria de destino
     *                  saldo -> Cantidad de dinero que desea transferirse
     *
     */
    $klein->respond('POST', '/user/transaction/new', function ($request, $response) {

        if(authenticate()) {
            try {
                $_POST = json_decode(array_keys($_POST)[0], true);
                $origen = sanitizeParameter($_POST, 'origen');
                $destino = sanitizeParameter($_POST, 'destino');
                $banco_destino = sanitizeParameter($_POST, 'banco_destino');
                $saldo = sanitizeParameter($_POST, 'saldo');

                $transtController = new TransController();

                $createdTrans = $transtController->createTransaction($origen, $destino, $banco_destino, $saldo);

                if($createdTrans) {
                    echo json_encode($createdTrans, JSON_PRETTY_PRINT);
                }

                else{
                    header("HTTP/1.1 404 Bad Request");
                    echo json_encode(null, JSON_PRETTY_PRINT);
                }

            }

            catch (Exception $e){
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
            }
            finally {
                exit();
            }
        }

        else {
            header("HTTP/1.1 401 Unauthorized");
            exit('Unauthorized');
        }

    });

    /**
     * /MiBanco/user/operations/getAll
     *
     * Petición para obtener todas las transacciones que ha hecho un usuario (exitosas y rechazadas) y las transacciones que ha recibido
     * Parámetros HTTP: usuario -> Nombre de usuario
     *
     */
    $klein->respond('POST', '/user/operations/getAll', function ($request, $response) {

        if(authenticate()) {
            try {
                $_POST = json_decode(array_keys($_POST)[0], true);
                $usuario = sanitizeParameter($_POST, 'usuario');

                $transtController = new TransController();

                $myTrans = $transtController->getAllUserOper($usuario);

                if ($myTrans) {
                    echo json_encode($myTrans, JSON_PRETTY_PRINT);
                } else {
                    header("HTTP/1.1 404 Bad Request");
                    echo json_encode(null, JSON_PRETTY_PRINT);
                }

            }

            catch (Exception $e) {
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
            }

            finally {
                exit();
            }
        }

        else {
            header("HTTP/1.1 401 Unauthorized");
            exit('Unauthorized');
        }

    });

    /**
     * /MiBanco/user/operations/myTransactions
     *
     * Petición para obtener todas las transacciones que ha hecho un usuario (exitosas y rechazadas)
     * Parámetros HTTP: usuario -> Nombre de usuario
     *
     */
    $klein->respond('POST', '/user/operations/myTransactions', function ($request, $response) {

        if(authenticate()) {

            try {
                $_POST = json_decode(array_keys($_POST)[0], true);
                $usuario = sanitizeParameter($_POST, 'usuario');

                $transtController = new TransController();

                $myTrans = $transtController->getAllUserTrans($usuario);

                if($myTrans) {
                    echo json_encode($myTrans, JSON_PRETTY_PRINT);
                }

                else{
                    header("HTTP/1.1 404 Bad Request");
                    echo json_encode(null, JSON_PRETTY_PRINT);
                }

            }

            catch (Exception $e){
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
            }

            finally {
                exit();
            }
        }

        else {
            header("HTTP/1.1 401 Unauthorized");
            exit('Unauthorized');
        }

    });

    /**
     * /MiBanco/operations/getAll
     *
     * Petición para obtener todas las transacciones en la base de datos
     * Parámetros HTTP: usuario -> Nombre de usuario de quien realiza la petición

     */
    $klein->respond('POST', '/operations/getAll', function ($request, $response) {

        if(authenticate()) {
            try{

                $_POST = json_decode(array_keys($_POST)[0], true);
                $usuario = sanitizeParameter($_POST, 'usuario');

                $transtController = new TransController();

                $allTrans = $transtController->getAllTrans($usuario);

                if($allTrans) {
                    echo json_encode($allTrans, JSON_PRETTY_PRINT);
                }

                else{
                    header("HTTP/1.1 404 Bad Request");
                    echo json_encode(null, JSON_PRETTY_PRINT);
                }

            }

            catch (Exception $e){
                header("HTTP/1.1 404 Bad Request");
                echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);

            }

            finally {
                exit();
            }
        }

        else {
            header("HTTP/1.1 401 Unauthorized");
            exit('Unauthorized');
        }

    });
});

$klein->dispatch();


function sanitizeParameter($post, $param){

    return filter_var($post[$param], FILTER_SANITIZE_STRING);

}

// OAuth authentication functions follow
function authenticate()
{
    // extract the token from the headers
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return false;
    }

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    preg_match('/Bearer\s(\S+)/', $authHeader, $matches);

    if (!isset($matches[1])) {
        return false;
    }

    $token = $matches[1];

    return authenticateRemotely($token);

}

function authenticateRemotely($token)
{
    $metadataUrl = getenv('OKTA_ISSUER') . '/.well-known/oauth-authorization-server';
    $metadata = http($metadataUrl);
    $introspectionUrl = $metadata['introspection_endpoint'];

    $params = [
        'token' => $token,
        'client_id' => getenv('OKTA_SERVICE_APP_ID'),
        'client_secret' => getenv('OKTA_SERVICE_APP_SECRET')
    ];

    $result = http($introspectionUrl, $params);

    if (!$result['active']) {
        return false;
    }

    return true;
}

function authenticateLocally($token)
{
    $tokenParts = explode('.', $token);
    $decodedToken['header'] = json_decode(base64UrlDecode($tokenParts[0]), true);
    $decodedToken['payload'] = json_decode(base64UrlDecode($tokenParts[1]), true);
    $decodedToken['signatureProvided'] = base64UrlDecode($tokenParts[2]);

    // Get the JSON Web Keys from the server that signed the token
    // (ideally they should be cached to avoid
    // calls to Okta on each API request)...
    $metadataUrl = getenv('OKTA_ISSUER') . '/.well-known/oauth-authorization-server';
    $metadata = http($metadataUrl);
    $jwksUri = $metadata['jwks_uri'];
    $keys = http($jwksUri);

    // Find the public key matching the kid from the input token
    $publicKey = false;
    foreach ($keys['keys'] as $key) {
        if ($key['kid'] == $decodedToken['header']['kid']) {
            $publicKey = JWK::parseKey($key);
            break;
        }
    }
    if (!$publicKey) {
        echo "Couldn't find public key\n";
        return false;
    }

    // Check the signing algorithm
    if ($decodedToken['header']['alg'] != 'RS256') {
        echo "Bad algorithm\n";
        return false;
    }

    $result = JWT::decode($token, $publicKey, array('RS256'));

    if (!$result) {
        echo "Error decoding JWT\n";
        return false;
    }

    // Basic JWT validation passed, now check the claims

    // Verify the Issuer matches Okta's issuer
    if ($decodedToken['payload']['iss'] != getenv('OKTA_ISSUER')) {
        echo "Issuer did not match\n";
        return false;
    }

    // Verify the audience matches the expected audience for this API
    if ($decodedToken['payload']['aud'] != getenv('OKTA_AUDIENCE')) {
        echo "Audience did not match\n";
        return false;
    }

    // Verify this token was issued to the expected client_id
    if ($decodedToken['payload']['cid'] != getenv('OKTA_CLIENT_ID')) {
        echo "Client ID did not match\n";
        return false;
    }

    return true;
}

function http($url, $params = null)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($params) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }
    return json_decode(curl_exec($ch), true);
}

function base64UrlDecode($input)
{
    $remainder = strlen($input) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $input .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($input, '-_', '+/'));
}

function encodeLength($length)
{
    if ($length <= 0x7F) {
        return chr($length);
    }
    $temp = ltrim(pack('N', $length), chr(0));
    return pack('Ca*', 0x80 | strlen($temp), $temp);
}

function base64UrlEncode($text)
{
    return str_replace(
        ['+', '/', '='],
        ['-', '_', ''],
        base64_encode($text)
    );
}
