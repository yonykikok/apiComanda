<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Slim\App;
use Controllers\AuthController;
use Controllers\MozosController;
use Middleware\RoleMiddleware;
use Middleware\AuthMiddleware;
use Middleware\RegistroMiddleware;

return function (App $app) {
    $app->group('/Mozo', function () {
        $this->get('/',MozosController::class.':obtenerPedidosListos');
        $this->post('/TomarPedido', MozosController::class . ':TomarPedido');
        $this->post('/ServirPedido', MozosController::class . ':ServirPedido');
        $this->post('/CobrarPedido', MozosController::class . ':CobrarPedido');
        $this->post('/TomarFotografia', MozosController::class . ':TomarFotografia');
        $this->post('/CancelarPedido', MozosController::class . ':CancelarPedido');
        $this->post('/CambiarPedidoComida', MozosController::class . ':CambiarPedidoComida');
    })->add(RegistroMiddleware::class . ':guardarOperacion');/*->add(AuthMiddleware::class.':IsLoggedIn')
->add(RoleMiddleware::class . ':esMozo')
->add(RegistroMiddleware::class . ':guardarOperacion');*/

    $app->get('/MostrarMenu', MozosController::class . ':MostrarMenu');
};
