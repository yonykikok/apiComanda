<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Slim\App;
use Controllers\AuthController;
use Controllers\CocinerosController;
use Middleware\RoleMiddleware;
use Middleware\AuthMiddleware;
use Middleware\RegistroMiddleware;
return function(App $app){
$app->group('/Cocinero',function(){
    $this->get('/',CocinerosController::class.':PedidosPendientes');
    $this->post('/CocinarPedido',CocinerosController::class.':CocinarPedido');
    $this->post('/TerminarPedido',CocinerosController::class.':TerminarPedido');
    $this->post('/PrepararPostre',CocinerosController::class.':PrepararPostre');
    $this->post('/TerminarPedidoPostre',CocinerosController::class.':TerminarPedidoPostre');
})->add(AuthMiddleware::class.':IsLoggedIn')
->add(RoleMiddleware::class . ':esCocinero')
->add(RegistroMiddleware::class . ':guardarOperacion');
};
