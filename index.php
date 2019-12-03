<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Helpers\AppConfig;

require __DIR__.'/vendor/autoload.php';

$config = ['settings' => ['displayErrorDetails' => true]];

$app = new \Slim\App($config);



$app->add(function ($req, $res, $next) {
    $res = $res->withHeader('Access-Control-Allow-Origin', '*');
    $res = $res->withHeader('Access-Control-Allow-Methods', $req->getHeaderLine('Access-Control-Request-Method'));
    $res = $res->withHeader('Access-Control-Allow-Headers', $req->getHeaderLine('Access-Control-Request-Headers'));

    return $next($req, $res);
});

$capsule = new Capsule;
$capsule->addConnection(AppConfig::$illuminateDb);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$authRoutes = require __DIR__.'/src/Routes/AuthRoutes.php';
$authRoutes($app);

$socioRoutes = require __DIR__.'/src/Routes/Socio/SocioRoutes.php';
$socioRoutes($app);

$mozoRoutes = require __DIR__.'/src/Routes/Mozo/MozoRoutes.php';
$mozoRoutes($app);

$clienteRoutes = require __DIR__.'/src/Routes/Cliente/ClienteRoutes.php';
$clienteRoutes($app);

$usuarioRoutes = require __DIR__.'/src/Routes/Usuario/SocioRoutes.php';
$usuarioRoutes($app);

$cocineroRoutes = require __DIR__.'/src/Routes/Cocinero/CocineroRoutes.php';
$cocineroRoutes($app);

$cerveceroRoutes = require __DIR__.'/src/Routes/Cervecero/CerveceroRoutes.php';
$cerveceroRoutes($app);

$bartenderRoutes = require __DIR__.'/src/Routes/Bartender/BartenderRoutes.php';
$bartenderRoutes($app);


$app->run();
