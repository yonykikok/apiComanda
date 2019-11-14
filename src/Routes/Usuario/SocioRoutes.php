<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Slim\App;
use Controllers\SociosController;
use Middleware\RoleMiddleware;
use Middleware\AuthMiddleware;
use Middleware\RegistroMiddleware;
return function(App $app)
{
  $app->group('/usuarios', function()
  {
    $this->get('/', SociosController::class . ':VerPedidos');
    $this->get('/TraerTodos', SociosController::class . ':VerPedidosEnPreparacion');
    $this->post('/PorOrden', SociosController::class . ':VerPedidoPorOrden');    
  });//->add(AuthMiddleware::class.':IsLoggedIn')
    //-/>add(RoleMiddleware::class.':IsAdmin');
    // ->add(RegistroMiddleware::class . ':guardarOperacion'); 

};
