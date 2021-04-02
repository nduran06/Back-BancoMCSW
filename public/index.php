<?php
require __DIR__ . '/../bootstrap.php';

include('../Controllers/CustomerController.php');
include_once('../api/LoginController.php');
include_once('../api/UserController.php');
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

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER["REQUEST_METHOD"];
$uriParts = explode( '/', $uri );

// define all valid endpoints - this will act as a simple router
$routes = [
    'customers' => [
        'method' => 'GET',
        'expression' => '/^\/customers\/?$/',
        'controller_method' => 'index'
    ],
    'customers.create' => [
        'method' => 'POST',
        'expression' => '/^\/customers\/?$/',
        'controller_method' => 'store'
    ],
    'customers.charge' => [
        'method' => 'POST',
        'expression' => '/^\/customers\/(\d+)\/charges\/?$/',
        'controller_method' => 'charge'
    ]
];

$klein = new Klein();

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
        }

        catch (Exception $e){
            header("HTTP/1.1 404 Bad Request");
            echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
        }

        exit();
    });

    /**
     * /MiBanco/validator/user
     *
     * Petición para verificar si un usuario que quiere registrarse en la app, en realidad tiene una cuenta bancaria existente en MiBanco
     * Parámetros HTTP: documento -> Número del documento de identidad del posible nuevo usuario
     *
     */
    $klein->respond('POST', '/validator/user', function ($request, $response) {

        try {
            $userController = new UserController();
            $existedUser = $userController -> validUser($_POST['documento']);

            if($existedUser) {

                echo json_encode($existedUser, JSON_PRETTY_PRINT);
            }

            else {
                header("HTTP/1.1 400 Bad Request");
                echo json_encode(null, JSON_PRETTY_PRINT);
            }
        }

        catch (Exception $e){
            header("HTTP/1.1 404 Bad Request");
            echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
        }

        exit();
    });

    /**
     * /MiBanco/user/getAccount
     *
     * Petición para obtener el número de cuenta bancaria de un usuario (cliente)
     * Parámetros HTTP: usuario -> Nombre de usuario del cual se desea saber su número de cuenta
     *
     */
    $klein->respond('POST', '/user/client/getAccount', function ($request, $response) {

        try {
            $userController = new UserController();
            $existedUser = $userController->getUserAccount($_POST['usuario']);

            if ($existedUser) {

                echo json_encode($existedUser, JSON_PRETTY_PRINT);
            }

            else {
                header("HTTP/1.1 404 Bad Request");
                echo json_encode(null, JSON_PRETTY_PRINT);
            }
        }

        catch (Exception $e){
            header("HTTP/1.1 404 Bad Request");
            echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
        }

        exit();
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

        try {
            $userController = new UserController();

            $tipoUsuario = $_POST['tipo'];

            $newUserResp = null;

            if($tipoUsuario === "cliente") {
                $newUserResp = $userController->createClientUser($_POST['documento'], $tipoUsuario, $_POST['usuario'], $_POST['passwd']);

            }

            elseif($tipoUsuario === "admin" or $tipoUsuario === "auditor") {
                $newUserResp = $userController->createHightUser($_POST['documento'], $tipoUsuario, $_POST['usuario'], $_POST['passwd'], $_POST['nombre']);
            }

            if($newUserResp) {
                echo json_encode($newUserResp, JSON_PRETTY_PRINT);

            }

            else {
                header("HTTP/1.1 404 Bad Request");
                echo json_encode(null, JSON_PRETTY_PRINT);
            }

        }

        catch (Exception $e){
            header("HTTP/1.1 404 Bad Request");
            echo json_encode("Datos incorrectos", JSON_PRETTY_PRINT);
        }

        exit();
    });

    /**
     *  /MiBanco/user/getBalance
     *
     * Petición para obtener el dinero que posee un usuario cliente en su cuenta
     * Parámetros HTTP: num_cuenta -> Número de la cuenta bancaria
     *
     */
    $klein->respond('POST', '/user/getBalance', function ($request, $response) {

        try {
            $userController = new UserController();

            $newUserResp = $userController->getBalance($_POST['num_cuenta']);

            if($newUserResp) {
                echo json_encode($newUserResp, JSON_PRETTY_PRINT);
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


        exit();
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

        try {
            $userController = new UserController();

            $newUserResp = $userController->modifyBalance($_POST['num_cuenta'], $_POST['saldo']);

            if($newUserResp) {
                echo json_encode($newUserResp, JSON_PRETTY_PRINT);
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


        exit();

    });

});



$klein->dispatch();

/*Route::get('ID/{id}',function($id) {
    echo 'ID: '.$id;
});

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

// all of our endpoints start with /person
// everything else results in a 404 Not Found
if ($uri[1] !== 'person') {
    header("HTTP/1.1 404 Not Found");
    exit();
}


$routeFound = null;
foreach ($routes as $route) {
    if ($route['method'] == $requestMethod &&
        preg_match($route['expression'], $uri))
    {
        $routeFound = $route;
        break;
    }
}

if (! $routeFound) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$methodName = $route['controller_method'];

// authenticate the request:
if (! authenticate($methodName)) {
    header("HTTP/1.1 401 Unauthorized");
    exit('Unauthorized');
}

$controller = new CustomerController();
$controller->$methodName($uriParts);*/




// END OF FRONT CONTROLLER
// OAuth authentication functions follow

function authenticate($methodName)
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

    // validate the token
    if ($methodName == 'charge') {
        return authenticateRemotely($token);
    } else {
        return authenticateLocally($token);
    }
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
