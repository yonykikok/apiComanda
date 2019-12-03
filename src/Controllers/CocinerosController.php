<?php

namespace Controllers;

use Models\Comida;
use Models\Postre;
use Models\PedidoComida;
use Models\PedidoPostre;
use Controllers\MozosController;

class CocinerosController //implements IController
{

  public static function obtenerImagenPorId($request, $response, $args)
  {
    $id = $args["id"];

    var_dump(PedidoPostre::where('id', $id)->first());
  }

  public static function PedidosEnPreparacion($request, $response, $args)
  {
    $pedidosPendientes = PedidoComida::where('estado', 'en preparacion')->where('idComida','!=',1000)->get();
    if (count($pedidosPendientes) > 0) {
      return json_encode($pedidosPendientes);
    } else {
      return $response->withJson("sin pedidos", 200);
    }
  }

  public static function Pedidos($request, $response, $args)
  {
    $pedidosPendientes = PedidoComida::where('idComida','!=',1000)->get();
    foreach ($pedidosPendientes as $key => $value) {
      if ($value->idComida != 1000) {
        $value['imagenPedido'] = Comida::where('id', $value->idComida)->first()->imagen;
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
    $pedidosPendientes = PedidoComida::where('estado', 'pendiente')->where('idComida','!=',1000)->get();
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
      $pedido = PedidoComida::where('estado', 'pendiente')->get()->first(); //obtengo el pedido que le sigue por orden
      $retorno = self::CambiarEstado($pedido, 'pendiente', 'en preparacion', $orden);
    } else {
      if (isset($orden)) // si ingresa una orden la busca y le da prioridad a esa orden
      {
        $pedido = PedidoComida::where('estado', 'pendiente')->where('orden', $orden)->get()->first();
        $retorno = self::CambiarEstado($pedido, 'pendiente', 'en preparacion', $orden);
      } else {
        $pedido = PedidoComida::where('estado', 'pendiente')->get()->first(); //obtengo el pedido que le sigue por orden
        $retorno = self::CambiarEstado($pedido, 'pendiente', 'en preparacion', '');
      }
    }
    return $response->withJson($retorno, 200);
  }

  public static function CambiarEstado($pedido, $estadoActual, $estadoSiguiente, $ordenABuscar)
  {
    if ($pedido) {
      $pedidosACambiarEstado = PedidoComida::where('orden', $pedido->orden)->get(); //obtengo todos los pedidos con la misma orden
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
      $pedido = PedidoComida::where('estado', 'en preparacion')->get()->first(); //obtengo el pedido que le sigue por orden
      $retorno = self::CambiarEstado($pedido, 'en preparacion', 'listo para servir', $orden);
    } else {
      if (isset($orden)) // si ingresa una orden la busca y le da prioridad a esa orden
      {
        $pedido = PedidoComida::where('estado', 'en preparacion')->where('orden', $orden)->get()->first(); //obtengo el pedido que le sigue por orden
        $retorno = self::CambiarEstado($pedido, 'en preparacion', 'listo para servir', $orden);
      } else {
        $pedido = PedidoComida::where('estado', 'en preparacion')->get()->first(); //obtengo el pedido que le sigue por orden
        $retorno = self::CambiarEstado($pedido, 'en preparacion', 'listo para servir', '');
      }
    }
    if (!is_null($pedido)) {
      MozosController::ActualizarEstadoPedido($pedido->orden);
    }
    return $response->withJson($retorno, 200);
  }



  //POSTRESSSSSSS
  public static function PedidosEnPreparacionPostres($request, $response, $args)
  {
    $pedidosPendientes = PedidoPostre::where('estado', 'en preparacion')->where('idPostre','!=',1000)->get();
    if (count($pedidosPendientes) > 0) {
      return json_encode($pedidosPendientes);
    } else {
      return $response->withJson("sin pedidos", 200);
    }
  }

  public static function PedidosPostres($request, $response, $args)
  {
    $pedidosPendientes = PedidoPostre::where('idPostre','!=',1000)->get();
    foreach ($pedidosPendientes as $key => $value) {
      if ($value->idPostre != 1000) {
        $value['imagenPedido'] = Postre::where('id', $value->idPostre)->first()->imagen;
      }
    }
    if (count($pedidosPendientes) > 0) {
      return json_encode($pedidosPendientes);
    } else {
      return $response->withJson("sin pedidos", 200);
    }
  }
  public static function PedidosPendientesPostres($request, $response, $args)
  {
    $pedidosPendientes = PedidoPostre::where('estado', 'pendiente')->where('idPostre','!=',1000)->get();
    if (count($pedidosPendientes) > 0) {
      return json_encode($pedidosPendientes);
    } else {
      return $response->withJson("sin pedidos", 200);
    }
  }

  public static function PrepararPedidoPostres($request, $response, $args)
  {
    $orden = $args["orden"];
    $retorno = "";
    if (is_null($orden)) {
      $pedido = PedidoPostre::where('estado', 'pendiente')->get()->first(); //obtengo el pedido que le sigue por orden
      $retorno = self::CambiarEstadoPostres($pedido, 'pendiente', 'en preparacion', $orden);
    } else {
      if (isset($orden)) // si ingresa una orden la busca y le da prioridad a esa orden
      {
        $pedido = PedidoPostre::where('estado', 'pendiente')->where('orden', $orden)->get()->first();
        $retorno = self::CambiarEstadoPostres($pedido, 'pendiente', 'en preparacion', $orden);
      } else {
        $pedido = PedidoPostre::where('estado', 'pendiente')->get()->first(); //obtengo el pedido que le sigue por orden
        $retorno = self::CambiarEstadoPostres($pedido, 'pendiente', 'en preparacion', '');
      }
    }
    return $response->withJson($retorno, 200);
  }

  public static function CambiarEstadoPostres($pedido, $estadoActual, $estadoSiguiente, $ordenABuscar)
  {
    if ($pedido) {
      $pedidosACambiarEstado = PedidoPostre::where('orden', $pedido->orden)->get(); //obtengo todos los pedidos con la misma orden
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


  public static function TerminarPedidoPostres($request, $response, $args)
  {
    $orden = $args["orden"];
    $retorno = "";
    if (is_null($orden)) {
      $pedido = PedidoPostre::where('estado', 'en preparacion')->get()->first(); //obtengo el pedido que le sigue por orden
      $retorno = self::CambiarEstadoPostres($pedido, 'en preparacion', 'listo para servir', $orden);
    } else {
      if (isset($orden)) // si ingresa una orden la busca y le da prioridad a esa orden
      {
        $pedido = PedidoPostre::where('estado', 'en preparacion')->where('orden', $orden)->get()->first(); //obtengo el pedido que le sigue por orden
        $retorno = self::CambiarEstadoPostres($pedido, 'en preparacion', 'listo para servir', $orden);
      } else {
        $pedido = PedidoPostre::where('estado', 'en preparacion')->get()->first(); //obtengo el pedido que le sigue por orden
        $retorno = self::CambiarEstadoPostres($pedido, 'en preparacion', 'listo para servir', '');
      }
    }
    if (!is_null($pedido)) {
      MozosController::ActualizarEstadoPedido($pedido->orden);
    }
    return $response->withJson($retorno, 200);
  }






























  // public static function PedidosPendientes($request,$response,$args){
  //   echo '<h4>Comidas</h4>';
  //     $pedidosPendientesCocina=PedidoComida::where('estado','pendiente')->get();
  //   if(count($pedidosPendientesCocina)>0)
  //   {
  //     foreach ($pedidosPendientesCocina as $indice => $pedido) {
  //       $comida=Comida::where('id',$pedido->idComida)->get()->first();       
  //       echo $comida->nombre.' >>> Orden: '.$pedido['orden'].'<br>';
  //       }
  //   }
  //   else{
  //     echo 'Sin comidas pendientes';
  //   }
  //   echo '<h4>Postres</h4>';
  //   $pedidosPendientesCocina=PedidoPostre::where('estado','pendiente')->get();
  //   if(count($pedidosPendientesCocina)>0)
  //   {
  //     foreach ($pedidosPendientesCocina as $indice => $pedido) {
  //       $postre=Postre::where('id',$pedido->idPostre)->get()->first();       
  //       echo $postre->nombre.' >>> Orden: '.$pedido['orden'].'<br>';
  //       }
  //   }
  //   else{
  //     echo 'Sin postres pendientes';
  //   }
  // }


  // public static function CocinarPedido($request,$response,$args)
  // {
  //   $data=$request->getParsedBody();
  //   if(is_null($data))
  //   {
  //     $pedido=PedidoComida::where('estado','pendiente')->get()->first();//obtengo el pedido que le sigue por orden
  //     self::CambiarEstado($pedido,'pendiente','en preparacion',$data['orden']);
  //   }
  //   else
  //   {
  //     if(isset($data['orden']))// si ingresa una orden la busca y le da prioridad a esa orden
  //     {
  //       $pedido=PedidoComida::where('estado','pendiente')->where('orden',$data['orden'])->get()->first();//obtengo el pedido que le sigue por orden
  //       self::CambiarEstado($pedido,'pendiente','en preparacion',$data['orden']);
  //     }
  //     else{
  //       $pedido=PedidoComida::where('estado','pendiente')->get()->first();//obtengo el pedido que le sigue por orden
  //       self::CambiarEstado($pedido,'pendiente','en preparacion','');
  //     }
  //   }
  //   ///echo 'Cocinado';
  // }
  // public static function CambiarEstado($pedido,$estadoActual,$estadoSiguiente,$ordenABuscar)
  // {
  //   if($pedido)
  //   {
  //     $pedidosACambiarEstado= PedidoComida::where('orden',$pedido->orden)->get();//obtengo todos los pedidos con la misma orden
  //     foreach ($pedidosACambiarEstado as $indice => $pedido) 
  //     {
  //       $pedido->estado=$estadoSiguiente;//cambiamos su estado
  //       $pedido->save();//guardamos los cambios
  //       echo($pedido);
  //     }
  //   }
  //   else
  //   {
  //     if($ordenABuscar)
  //     {
  //       echo 'No hay pedidos con orden: '.$ordenABuscar;
  //     }
  //     else
  //     {
  //       echo 'No hay pedidos '.$estadoActual;
  //     }
  //   }
  // }
  // public static function PrepararPostre($request,$response,$args)
  // {
  //   $data=$request->getParsedBody();
  //   if(is_null($data))
  //   {
  //     $pedido=PedidoPostre::where('estado','pendiente')->get()->first();//obtengo el pedido que le sigue por orden
  //     self::CambiarEstadoPostre($pedido,'pendiente','en preparacion',$data['orden']);
  //   }
  //   else
  //   {
  //     if(isset($data['orden']))// si ingresa una orden la busca y le da prioridad a esa orden
  //     {
  //       $pedido=PedidoPostre::where('estado','pendiente')->where('orden',$data['orden'])->get()->first();//obtengo el pedido que le sigue por orden
  //       self::CambiarEstadoPostre($pedido,'pendiente','en preparacion',$data['orden']);
  //     }
  //     else{
  //       $pedido=PedidoPostre::where('estado','pendiente')->get()->first();//obtengo el pedido que le sigue por orden
  //       self::CambiarEstadoPostre($pedido,'pendiente','en preparacion','');
  //     }
  //   }
  // }

  // public static function CambiarEstadoPostre($pedido,$estadoActual,$estadoSiguiente,$ordenABuscar)
  // {
  //   if($pedido)
  //   {
  //     $pedidosACambiarEstado= PedidoPostre::where('orden',$pedido->orden)->get();//obtengo todos los pedidos con la misma orden
  //     foreach ($pedidosACambiarEstado as $indice => $pedido) 
  //     {
  //       $pedido->estado=$estadoSiguiente;//cambiamos su estado
  //       $pedido->save();//guardamos los cambios
  //       echo($pedido);
  //     }
  //   }
  //   else
  //   {
  //     if($ordenABuscar)
  //     {
  //       echo 'No hay pedidos con orden: '.$ordenABuscar;
  //     }
  //     else
  //     {
  //       echo 'No hay pedidos '.$estadoActual;
  //     }
  //   }
  // }

  // public static function TerminarPedido($request,$response,$args)
  // {
  //   $data=$request->getParsedBody();
  //   if(is_null($data))
  //   {
  //     $pedido=PedidoComida::where('estado','en preparacion')->get()->first();//obtengo el pedido que le sigue por orden
  //     self::CambiarEstado($pedido,'en preparacion','listo para servir',$data['orden']);
  //   }
  //   else
  //   {
  //     if(isset($data['orden']))// si ingresa una orden la busca y le da prioridad a esa orden
  //     {
  //       $pedido=PedidoComida::where('estado','en preparacion')->where('orden',$data['orden'])->get()->first();//obtengo el pedido que le sigue por orden
  //       self::CambiarEstado($pedido,'en preparacion','listo para servir',$data['orden']);
  //     }
  //     else{
  //       $pedido=PedidoComida::where('estado','en preparacion')->get()->first();//obtengo el pedido que le sigue por orden
  //       self::CambiarEstado($pedido,'en preparacion','listo para servir','');
  //     }
  //   }
  // }

  // public static function TerminarPedidoPostre($request,$response,$args)
  // {
  //   $data=$request->getParsedBody();
  //   if(is_null($data))
  //   {
  //     $pedido=PedidoPostre::where('estado','en preparacion')->get()->first();//obtengo el pedido que le sigue por orden
  //     self::CambiarEstadoPostre($pedido,'en preparacion','listo para servir',$data['orden']);
  //   }
  //   else
  //   {
  //     if(isset($data['orden']))// si ingresa una orden la busca y le da prioridad a esa orden
  //     {
  //       $pedido=PedidoPostre::where('estado','en preparacion')->where('orden',$data['orden'])->get()->first();//obtengo el pedido que le sigue por orden
  //       self::CambiarEstadoPostre($pedido,'en preparacion','listo para servir',$data['orden']);
  //     }
  //     else{
  //       $pedido=PedidoPostre::where('estado','en preparacion')->get()->first();//obtengo el pedido que le sigue por orden
  //       self::CambiarEstadoPostre($pedido,'en preparacion','listo para servir','');
  //     }
  //   }
  //   if(!is_null($pedido))
  //   {
  //     MozosController::ActualizarEstadoPedido($pedido->orden);    
  //   }
  // }


}
