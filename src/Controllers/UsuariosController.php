<?php

namespace Controllers;

use Models\RegistroOperacion;
use Models\RegistroLogeo;
use Controllers\ClientesController;
use Helpers\JWTAuth;
use Helpers\AppConfig as Config;
use Helpers\FilesHelper as Files;
use Helpers\ImagesHelper as Images;

use Controllers\MozosController;
use Illuminate\Database\Capsule\Manager as Capsule;

class UsuariosController
{
  public static function VerUsuarios($request, $response, $args)
  {
    $usuarios = User::all();
    foreach ($pedidos as $indice => $pedido) {
      echo "Pedido '" . $pedido->orden . "' de la mesa " . $pedido->mesa . ' ' . $pedido->estado . '<br>';
    }
  }
  public static function obtenerPedidosPorUsuario($request, $response, $args)
  { 
    var_dump($args["nombre"]);
  }
}
