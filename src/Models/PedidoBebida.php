<?php
namespace Models;

use Helpers\AppConfig as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
class PedidoBebida extends Model
{
  protected $table = "pedidosbebida";
  public $timestamps = false;

  public static function LastInsertId()
  {
    $pedidoComida =  PedidoBebida::select("id")->orderBy("id", "desc")->first();
    return is_null($pedidoComida) ? 0 : $pedidoComida->id;
  } 
  public static function MostrarPedido($orden){
    $retorno='';
    $pedidosBebida=PedidoBebida::where("orden",$orden)->get();
    foreach ($pedidosBebida as $indice => $pedido) {
      $bebida=Bebida::where('id',$pedido->idBebida)->first();
      if(!is_null($bebida))
      {
        $retorno.=$pedido->cantidad.' '.$bebida->nombre.'<br>';
      } 
    }
    return $retorno;
  }
  public static function CalcularCostoDelPedido($orden,$bool)
  {
    $sumaAPagar=0;
    $pedidos= PedidoBebida::where('orden',$orden)->get();

    if($bool)
    {
      if(count($pedidos)>0)
      {
        foreach ($pedidos as $indice => $pedido) 
        {
          $bebida=Bebida::where('id',$pedido->idBebida)->first();
          $precioDelPedido=$bebida->precio*$pedido->cantidad;
          $sumaAPagar=$sumaAPagar+$precioDelPedido;  
          echo $pedido->cantidad.' '.$bebida->nombre.' --- $'.$precioDelPedido.'<br>';          
        }
      }
    }
    else
    {
      if(count($pedidos)>0)
      {
        foreach ($pedidos as $indice => $pedido) //sin imprimir
        {
          $bebida=Bebida::where('id',$pedido->idBebida)->first();
          if(!is_null($bebida))
          {
            $precioDelPedido=$bebida->precio*$pedido->cantidad;
            $sumaAPagar=$sumaAPagar+$precioDelPedido;  
          }
        }
      }
    }
    return $sumaAPagar;
  }  
  public static function CambiarEstado($orden,$estado,$estadoNuevo)
  {
    $pedidos=self::where('orden',$orden)->get();
    if(!is_null($pedidos) && count($pedidos)>0)
    {
      foreach ($pedidos as $indice => $pedido) {
        if($pedido->estado==$estado)
        {
          $pedido->estado=$estadoNuevo;
          $pedido->save();
        }
      }
    }
  }

  public static function CancelarPedido($orden)
  {
    $pedidos=self::where('orden',$orden)->get();
    if(count($pedidos)>=1)
    {
      foreach ($pedidos as $key => $pedido) {
        $pedido->estado='cancelado';
        $pedido->save();
      }
    }
  }

  public static function BorrarTodos()
  {
    $pedidos=self::where('id','>','0')->get();
    if(!is_null($pedidos) && count($pedidos)>=1)
    {
      foreach ($pedidos as $key => $pedido) {
        $pedido->delete();
      }
    }
  }
}
