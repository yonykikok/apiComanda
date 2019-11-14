<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Slim\App;
use Controllers\AuthController;
use Controllers\CerveceroController;
use Middleware\RoleMiddleware;
use Middleware\AuthMiddleware;
use Middleware\RegistroMiddleware;
return function(App $app){
$app->group('/Cervecero',function(){
    $this->get('/',CerveceroController::class.':PedidosPendientes');
    $this->post('/PrepararPedido',CerveceroController::class.':PrepararPedido');
    $this->post('/TerminarPedido',CerveceroController::class.':TerminarPedido');
})->add(AuthMiddleware::class.':IsLoggedIn')
->add(RoleMiddleware::class . ':esCervecero')
->add(RegistroMiddleware::class . ':guardarOperacion');
};
