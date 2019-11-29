<?php

namespace Controllers;

use Models\Mozo;
use Models\Mesa;
use Models\Comida;
use Models\Cliente;
use Models\Bebida;
use Models\Postre;
use Models\Trago;
use Models\PedidoMozo;
use Models\PedidoTrago;
use Models\PedidoBebida;
use Models\PedidoComida;
use Models\PedidoPostre;
use Helpers\JWTAuth;
use Helpers\AppConfig as Config;
use Helpers\FilesHelper as Files;
use Helpers\ImagesHelper as Images;
use Middleware\TokenValidatorMiddleware;
use Illuminate\Database\Capsule\Manager as Capsule;

class BartenderController //implements IController
{
  public static function PedidosEnPreparacion($request,$response,$args){
    $pedidosPendientes=PedidoTrago::where('estado','en preparacion')->get();
   if(count($pedidosPendientes)>0)
   {
    return json_encode($pedidosPendientes);
   }
   else{
    return $response->withJson("sin pedidos", 200);
   }
  }
   
  public static function Pedidos($request,$response,$args){
    $pedidosPendientes=PedidoTrago::all();
   if(count($pedidosPendientes)>0)
   {
    return json_encode($pedidosPendientes);
   }
   else{
    return $response->withJson("sin pedidos", 200);
   }
  }
  public static function PedidosPendientes($request,$response,$args){
    $pedidosPendientes=PedidoTrago::where('estado','pendiente')->get();
   if(count($pedidosPendientes)>0)
   {
    return json_encode($pedidosPendientes);
   }
   else{
    return $response->withJson("sin pedidos", 200);
   }
  }
 
  public static function PrepararPedido($request,$response,$args)
  {
    $orden= $args["orden"];
    if(is_null($orden))
    {
      $pedido=PedidoTrago::where('estado','pendiente')->get()->first();//obtengo el pedido que le sigue por orden
      self::CambiarEstado($pedido,'pendiente','en preparacion',$orden);
    }
    else
    {
      if(isset($orden))// si ingresa una orden la busca y le da prioridad a esa orden
      {
        $pedido=PedidoTrago::where('estado','pendiente')->where('orden',$orden)->get()->first();//obtengo el pedido que le sigue por orden
        self::CambiarEstado($pedido,'pendiente','en preparacion',$orden);
      }
      else{
        $pedido=PedidoTrago::where('estado','pendiente')->get()->first();//obtengo el pedido que le sigue por orden
        self::CambiarEstado($pedido,'pendiente','en preparacion','');
      }
    }
  }

  public static function CambiarEstado($pedido,$estadoActual,$estadoSiguiente,$ordenABuscar)
  {
    if($pedido)
    {
      $pedidosACambiarEstado= PedidoTrago::where('orden',$pedido->orden)->get();//obtengo todos los pedidos con la misma orden
      foreach ($pedidosACambiarEstado as $indice => $pedido) 
      {
        $pedido->estado=$estadoSiguiente;//cambiamos su estado
        $pedido->save();//guardamos los cambios
        echo($pedido);
      }
    }
    else
    {
      if($ordenABuscar)
      {
        echo 'No hay pedidos con orden: '.$ordenABuscar;
      }
      else
      {
        echo 'No hay pedidos '.$estadoActual;
      }
    }
  }

  public static function TerminarPedido($request,$response,$args)
  {
    $data=$request->getParsedBody();
    if(is_null($data))
    {
      $pedido=PedidoTrago::where('estado','en preparacion')->get()->first();//obtengo el pedido que le sigue por orden
      self::CambiarEstado($pedido,'en preparacion','listo para servir',$data['orden']);
    }
    else
    {
      if(isset($data['orden']))// si ingresa una orden la busca y le da prioridad a esa orden
      {
        $pedido=PedidoTrago::where('estado','en preparacion')->where('orden',$data['orden'])->get()->first();//obtengo el pedido que le sigue por orden
        self::CambiarEstado($pedido,'en preparacion','listo para servir',$data['orden']);
      }
      else{
        $pedido=PedidoTrago::where('estado','en preparacion')->get()->first();//obtengo el pedido que le sigue por orden
        self::CambiarEstado($pedido,'en preparacion','listo para servir','');
      }
    }
    if(!is_null($pedido))
    {
      MozosController::ActualizarEstadoPedido($pedido->orden);    
    }
  }

  
}
