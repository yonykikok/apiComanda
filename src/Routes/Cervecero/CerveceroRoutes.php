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

    $this->get('/',CerveceroController::class.':Pedidos');
    $this->get('/pendientes',CerveceroController::class.':PedidosPendientes');
    $this->get('/enPreparacion',CerveceroController::class.':PedidosEnPreparacion');
    $this->post('/PrepararPedido/{orden}',CerveceroController::class.':PrepararPedido');
    $this->post('/TerminarPedido/{orden}',CerveceroController::class.':TerminarPedido');
});/*->add(AuthMiddleware::class.':IsLoggedIn')
->add(RoleMiddleware::class . ':esCervecero')
->add(RegistroMiddleware::class . ':guardarOperacion')*/
};
