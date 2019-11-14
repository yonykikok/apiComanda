<?php
namespace Models;

use Helpers\AppConfig as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
class PedidoTrago extends Model
{
  protected $table = "pedidostrago";
  public $timestamps = false;

  public static function LastInsertId()
  {
    $pedidoTrago =  PedidoTrago::select("id")->orderBy("id", "desc")->first();
    return is_null($pedidoTrago) ? 0 : $pedidoTrago->id;
  } 
  public static function MostrarPedido($orden){
    $retorno='';
    $pedidosTrago=PedidoTrago::where("orden",$orden)->get();
    foreach ($pedidosTrago as $indice => $pedido) {
      $trago=Trago::where('id',$pedido->idTrago)->first();
      $retorno.=$pedido->cantidad.' '.$trago->nombre.'<br>';
    } 
    return $retorno;
  }
  public static function CalcularCostoDelPedido($orden,$bool)
  {
    $sumaAPagar=0;
    $pedidos= PedidoTrago::where('orden',$orden)->get();
    if($bool)
    {
      if(count($pedidos)>0)
      {
        foreach ($pedidos as $indice => $pedido) 
        {
          $trago=Trago::where('id',$pedido->idTrago)->first();
          $precioDelPedido=$trago->precio*$pedido->cantidad;
          $sumaAPagar=$sumaAPagar+$precioDelPedido;  
          echo $pedido->cantidad.' '.$trago->nombre.' --- $'.$precioDelPedido.'<br>';          
        }
      }
    }else
    {
      if(count($pedidos)>0)
      {
        foreach ($pedidos as $indice => $pedido) 
        {
          $trago=Trago::where('id',$pedido->idTrago)->first();
          $precioDelPedido=$trago->precio*$pedido->cantidad;
          $sumaAPagar=$sumaAPagar+$precioDelPedido;  
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
