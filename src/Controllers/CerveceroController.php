<?php

namespace Controllers;

use Models\PedidoBebida;

class CerveceroController //implements IController
{
  public static function PedidosEnPreparacion($request, $response, $args)
  {
    $pedidosPendientes = PedidoBebida::where('estado', 'en preparacion')->get();
    if (count($pedidosPendientes) > 0) {
      return json_encode($pedidosPendientes);
    } else {
      return $response->withJson("sin pedidos", 200);
    }
  }

  public static function Pedidos($request, $response, $args)
  {
    $pedidosPendientes = PedidoBebida::all();
    foreach ($pedidosPendientes as $key => $value) {
      if ($value->idTrago != 1000) {
        $value['imagenPedido'] = Trago::where('id', $value->idTrago)->first()->imagen;
      }
    }
    if (count($pedidosPendientes) > 0) {
      return json_encode($pedidosPendientes);
    } else {
      return $response->withJson("sin pedidos", 200);
    }
  }
  public static function PedidosPendientes($request, $response, $args)
  {
    $pedidosPendientes = PedidoBebida::where('estado', 'pendiente')->get();
    if (count($pedidosPendientes) > 0) {
      return json_encode($pedidosPendientes);
    } else {
      return $response->withJson("sin pedidos", 200);
    }
  }

  public static function PrepararPedido($request, $response, $args)
  {
    $orden = $args["orden"];
    $retorno = "";
    if (is_null($orden)) {
      $pedido = PedidoBebida::where('estado', 'pendiente')->get()->first(); //obtengo el pedido que le sigue por orden
      $retorno = self::CambiarEstado($pedido, 'pendiente', 'en preparacion', $orden);
    } else {
      if (isset($orden)) // si ingresa una orden la busca y le da prioridad a esa orden
      {
        $pedido = PedidoBebida::where('estado', 'pendiente')->where('orden', $orden)->get()->first();
        $retorno = self::CambiarEstado($pedido, 'pendiente', 'en preparacion', $orden);
      } else {
        $pedido = PedidoBebida::where('estado', 'pendiente')->get()->first(); //obtengo el pedido que le sigue por orden
        $retorno = self::CambiarEstado($pedido, 'pendiente', 'en preparacion', '');
      }
    }
    return $response->withJson($retorno, 200);
  }

  public static function CambiarEstado($pedido, $estadoActual, $estadoSiguiente, $ordenABuscar)
  {
    if ($pedido) {
      $pedidosACambiarEstado = PedidoBebida::where('orden', $pedido->orden)->get(); //obtengo todos los pedidos con la misma orden
      foreach ($pedidosACambiarEstado as $indice => $pedido) {
        $pedido->estado = $estadoSiguiente; //cambiamos su estado
        $pedido->save(); //guardamos los cambios
      }
      return "todo ok";
    } else {
      if ($ordenABuscar) {
        return 'No hay pedidos con orden: ' . $ordenABuscar;
      } else {
        return 'No hay pedidos ' . $estadoActual;
      }
    }
  }

  public static function TerminarPedido($request, $response, $args)
  {
    $orden = $args["orden"];
    $retorno = "";
    if (is_null($orden)) {
      $pedido = PedidoBebida::where('estado', 'en preparacion')->get()->first(); //obtengo el pedido que le sigue por orden
      $retorno = self::CambiarEstado($pedido, 'en preparacion', 'listo para servir', $orden);
    } else {
      if (isset($orden)) // si ingresa una orden la busca y le da prioridad a esa orden
      {
        $pedido = PedidoBebida::where('estado', 'en preparacion')->where('orden', $orden)->get()->first(); //obtengo el pedido que le sigue por orden
        $retorno = self::CambiarEstado($pedido, 'en preparacion', 'listo para servir', $orden);
      } else {
        $pedido = PedidoBebida::where('estado', 'en preparacion')->get()->first(); //obtengo el pedido que le sigue por orden
        $retorno = self::CambiarEstado($pedido, 'en preparacion', 'listo para servir', '');
      }
    }
    if (!is_null($pedido)) {
      MozosController::ActualizarEstadoPedido($pedido->orden);
    }
    return $response->withJson($retorno, 200);
  }

  
}
