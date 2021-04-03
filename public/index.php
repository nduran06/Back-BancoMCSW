<?php
require __DIR__ . '/../bootstrap.php';

include('../Controllers/CustomerController.php');
include_once('../api/LoginController.php');
include_once('../api/UserController.php');
include_once('../api/OverdraftController.php');
include_once('../api/TransController.php');
include_once('../client/OKTAToken.php');

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
            $userRole = $userLogin->login($_POST['usuario'], $_POST['passwd']);

            if ($userRole) {
                $oktaAuth = new OKTAToken();
                $token = $oktaAuth->getValidToken();

                $ans = [
                    'role' => $userRole,
                    'token' => $token
                ];

                echo json_encode($ans, JSON_PRETTY_PRINT);
            }

            else {
                header("HTTP/1.1 401 Unauthorized");
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
     * /MiBanco/validator/user
     *
     * Petición para verificar si un usuario que quiere registrarse en la app, en realidad tiene una cuenta bancaria existente en MiBanco
     * Parámetros HTTP: documento -> Número del documento de identidad del posible nuevo usuario
     *
     */
    $klein->respond('POST', '/validator/user', function ($request, $response) {

        if(authenticate()) {
            try {
                $userController = new UserController();
                $existedUser = $userController->validUser($_POST['documento']);

                if ($existedUser) {

                    echo json_encode($existedUser, JSON_PRETTY_PRINT);
                } else {
                    header("HTTP/1.1 400 Bad Request");
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
     * /MiBanco/user/getAccount
     *
     * Petición para obtener el número de cuenta bancaria de un usuario (cliente)
     * Parámetros HTTP: usuario -> Nombre de usuario del cual se desea saber su número de cuenta
     *
     */
    $klein->respond('POST', '/user/client/getAccount', function ($request, $response) {

        if(authenticate()) {
            try {
                $userController = new UserController();
                $existedUser = $userController->getUserAccount($_POST['usuario']);

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
                $userController = new UserController();

                $tipoUsuario = $_POST['tipo'];

                $newUserResp = null;

                if ($tipoUsuario === "cliente") {
                    $newUserResp = $userController->createClientUser($_POST['documento'], $tipoUsuario, $_POST['usuario'], $_POST['passwd']);

                } elseif ($tipoUsuario === "admin" or $tipoUsuario === "auditor") {
                    $newUserResp = $userController->createHightUser($_POST['documento'], $tipoUsuario, $_POST['usuario'], $_POST['passwd'], $_POST['nombre']);
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
     * Parámetros HTTP: num_cuenta -> Número de la cuenta bancaria
     *
     */
    $klein->respond('POST', '/user/getBalance', function ($request, $response) {

        if(authenticate()) {
            try {
                $userController = new UserController();

                $newUserResp = $userController->getBalance($_POST['num_cuenta']);

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
                $userController = new UserController();

                $newUserResp = $userController->modifyBalance($_POST['num_cuenta'], $_POST['saldo']);

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
                $overdraftController = new OverdraftController();

                $userOverdrafts = $overdraftController->getUserOverdrafts($_POST['num_cuenta']);

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
     * Petición para crear un nuevo sobregiros
     * Parámetros HTTP: num_cuenta -> Número de la cuenta bancaria del usuario
     *                  saldo -> Saldo solicitado paa el sobregiro
     *
     */
    $klein->respond('POST', '/user/overdraft/new', function ($request, $response) {

        if(authenticate()) {
            try {
                $overdraftController = new OverdraftController();

                $userOverdrafts = $overdraftController->createUserOverdraft($_POST['num_cuenta'], $_POST['saldo']);

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
     *
     */
    $klein->respond('POST', '/overdraft/getAll', function ($request, $response) {

        if(authenticate()) {
            try {
                $overdraftController = new OverdraftController();

                $userOverdrafts = $overdraftController->getAllOverdrafts($_POST['usuario']);

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
     *
     */
    $klein->respond('POST', '/overdraft/update', function ($request, $response) {
        if(authenticate()) {
            try {
                $overdraftController = new OverdraftController();

                $updatedOverdraft = $overdraftController->updateOverdraft($_POST['id'], $_POST['estado'], $_POST['porcentaje'], $_POST['usuario']);

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
                $transtController = new TransController();

                $createdTrans = $transtController->createTransaction($_POST['origen'], $_POST['destino'], $_POST['banco_destino'], $_POST['saldo']);

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
                $transtController = new TransController();

                $myTrans = $transtController->getAllUserOper($_POST['usuario']);

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

                $transtController = new TransController();

                $myTrans = $transtController->getAllUserTrans($_POST['usuario']);

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
     *
     */
    $klein->respond('POST', '/operations/getAll', function ($request, $response) {

        if(authenticate()) {
            try{
                $transtController = new TransController();

                $allTrans = $transtController->getAllTrans($_POST['usuario']);

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
