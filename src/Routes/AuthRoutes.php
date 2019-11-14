<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Slim\App;
use Controllers\AuthController;
return function(App $app)
{
  $app->group('/auth', function()
  {
    $this->post('/login', AuthController::class . ':LogIn');
    $this->get('/getInfoByToken', AuthController::class . ':getInfoByToken');
    $this->post('/register', AuthController::class . ':Register');
  });

  
};
