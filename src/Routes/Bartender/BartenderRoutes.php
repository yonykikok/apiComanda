<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Slim\App;
use Controllers\AuthController;
use Controllers\BartenderController;
use Middleware\RoleMiddleware;
use Middleware\RegistroMiddleware;
use Middleware\AuthMiddleware;

return function(App $app){
$app->group('/Bartender',function(){
    $this->get('/',BartenderController::class.':PedidosPendientes');
    $this->post('/PrepararPedido',BartenderController::class.':PrepararPedido');
    $this->post('/TerminarPedido',BartenderController::class.':TerminarPedido');
})/*->add(AuthMiddleware::class.':IsLoggedIn')
->add(RoleMiddleware::class . ':esBartender')
->add(RegistroMiddleware::class . ':guardarOperacion')*/;
};
