<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Slim\App;
use Controllers\AuthController;
use Controllers\ClientesController;
use Middleware\RoleMiddleware;

return function(App $app){
$app->group('/Cliente/Pedido',function(){
    $this->get('/{nombre}',ClientesController::class.':obtenerPedidosPorUsuario');
    $this->get('/',ClientesController::class.':VerPedido');
    $this->get('/verEstado',ClientesController::class.':verEstado');
    $this->get('/verDemora',ClientesController::class.':calcularDemora');
    $this->post('/responderEncuesta',ClientesController::class.':GuardarEncuesta');
});
};
