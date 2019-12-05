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
  $app->group('/Socio/Pedidos', function()
  {
    $this->get('/', SociosController::class . ':VerPedidos');
    $this->get('/EnPreparacion', SociosController::class . ':VerPedidosEnPreparacion');
    $this->get('/Terminados', SociosController::class . ':VerPedidosTerminados');
    $this->get('/EsperandoCierre', SociosController::class . ':PedidosEsperandoCierre');
    $this->post('/PorOrden', SociosController::class . ':VerPedidoPorOrden');
    $this->post('/CerrarMesa', SociosController::class . ':CerrarMesa');
    $this->post('/LiberarMesas', SociosController::class . ':LiberarMesasCerradas');
    
  })->add(RegistroMiddleware::class . ':guardarOperacion');/*->add(AuthMiddleware::class.':IsLoggedIn')
    ->add(RoleMiddleware::class.':IsAdmin')
    ->add(RegistroMiddleware::class . ':guardarOperacion')*/

  $app->group('/Socio/Administracion/Mesas', function()
  {
    $this->get('/MasUsada', SociosController::class . ':MesaMasUsada');
    $this->get('/MenosUsada', SociosController::class . ':MesaMenosUsada');   
    $this->get('/MejorFacturacion', SociosController::class . ':MejorFacturacion');   
    $this->get('/PeorFacturacion', SociosController::class . ':PeorFacturacion');   
    $this->get('/FacturaMasAlta', SociosController::class . ':FacturaMasAlta');   
    $this->get('/FacturaMasBaja', SociosController::class . ':FacturaMasBaja');   
    $this->get('/FacturaMasAltaEntreFechas', SociosController::class . ':FacturaMasAltaEntreFechas');   
    $this->get('/MejoresComentarios', SociosController::class . ':MejoresComentarios');   
    $this->get('/PeoresComentarios', SociosController::class . ':PeoresComentarios');   
  })->add(RegistroMiddleware::class . ':guardarOperacion');/*->add(AuthMiddleware::class.':IsLoggedIn')  
    ->add(RoleMiddleware::class.':IsAdmin')
    ->add(RegistroMiddleware::class . ':guardarOperacion')*/

  $app->group('/Socio/Administracion/Pedidos', function()
  {
    $this->get('/MasVendido', SociosController::class . ':PedidosMasVendidos');
    $this->get('/MenosVendido', SociosController::class . ':PedidosMenosVendidos');   
    $this->get('/Atrasados', SociosController::class . ':PedidosAtrasados');   //no se hizo
    $this->get('/Cancelados', SociosController::class . ':PedidosCancelados');   
  })->add(RegistroMiddleware::class . ':guardarOperacion');/*->add(AuthMiddleware::class.':IsLoggedIn')
  ->add(RoleMiddleware::class.':IsAdmin')
  ->add(RegistroMiddleware::class . ':guardarOperacion')*/

    $app->group('/Socio/Administracion/Especial', function()
  {
    $this->get('/LimpiarBaseDeDatos', SociosController::class . ':LimpiarTodo');
  })->add(RegistroMiddleware::class . ':guardarOperacion');/*->add(AuthMiddleware::class.':IsLoggedIn')
    ->add(RoleMiddleware::class.':IsAdmin')
    ->add(RegistroMiddleware::class . ':guardarOperacion')*/

};
