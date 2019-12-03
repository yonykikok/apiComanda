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
    // $this->get('/',CocinerosController::class.':PedidosPendientes');
    // $this->post('/CocinarPedido',CocinerosController::class.':CocinarPedido');
    // $this->post('/TerminarPedido',CocinerosController::class.':TerminarPedido');
    // $this->post('/PrepararPostre',CocinerosController::class.':PrepararPostre');
    // $this->post('/TerminarPedidoPostre',CocinerosController::class.':TerminarPedidoPostre');

    $this->get('/',CocinerosController::class.':Pedidos');
    $this->get('/pendientes',CocinerosController::class.':PedidosPendientes');
    $this->get('/enPreparacion',CocinerosController::class.':PedidosEnPreparacion');
    $this->post('/PrepararPedido/{orden}',CocinerosController::class.':PrepararPedido');
    $this->post('/TerminarPedido/{orden}',CocinerosController::class.':TerminarPedido');
});/*->add(AuthMiddleware::class.':IsLoggedIn')
->add(RoleMiddleware::class . ':esCocinero')
->add(RegistroMiddleware::class . ':guardarOperacion')*/
$app->group('/Cocinero/Postres',function(){
    $this->get('/',CocinerosController::class.':PedidosPostres');
     $this->get('/pendientes',CocinerosController::class.':PedidosPendientesPostres');
     $this->get('/enPreparacion',CocinerosController::class.':PedidosEnPreparacionPostres');
     $this->post('/PrepararPedido/{orden}',CocinerosController::class.':PrepararPedidoPostres');
     $this->post('/TerminarPedido/{orden}',CocinerosController::class.':TerminarPedidoPostres');
 });
};
