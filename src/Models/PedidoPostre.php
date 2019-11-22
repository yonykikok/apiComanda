<?php

namespace Models;

use Helpers\AppConfig as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use Models\postre;

class PedidoPostre extends Model
{
  protected $table = "pedidospostre";
  public $timestamps = false;

  public static function LastInsertId()
  {
    $pedidoPostre =  PedidoPostre::select("id")->orderBy("id", "desc")->first();
    return is_null($pedidoPostre) ? 0 : $pedidoPostre->id;
  }
  public static function MostrarPedido($orden)
  {
    $retorno = '';
    $pedidosPostre = Pedidopostre::where("orden", $orden)->get();
    foreach ($pedidosPostre as $indice => $pedido) {
      $postre = Postre::where('id', $pedido->idPostre)->first();
      if (!is_null($postre)) {
        $retorno .= $pedido->cantidad . ' ' . $postre->nombre . '<br>';
      }
    }
    return $retorno;
  }
  public static function CalcularCostoDelPedido($orden, $bool)
  {
    $sumaAPagar = 0;
    $pedidos = PedidoPostre::where('orden', $orden)->get();
    if ($bool) {
      if (count($pedidos) > 0) {
        foreach ($pedidos as $indice => $pedido) {
          $postre = Postre::where('id', $pedido->idPostre)->first();
          $precioDelPedido = $postre->precio * $pedido->cantidad;
          $sumaAPagar = $sumaAPagar + $precioDelPedido;
          echo $pedido->cantidad . ' ' . $postre->nombre . ' --- $' . $precioDelPedido . '<br>';
        }
      }
    } else {
      if (count($pedidos) > 0) //sin imprimir
      {
        foreach ($pedidos as $indice => $pedido) {
          $postre = Postre::where('id', $pedido->idPostre)->first();
          if (!is_null($postre)) {
            $precioDelPedido = $postre->precio * $pedido->cantidad;
            $sumaAPagar = $sumaAPagar + $precioDelPedido;
          }
        }
      }
    }
    return $sumaAPagar;
  }
  public static function CambiarEstado($orden, $estado, $estadoNuevo)
  {
    $pedidos = self::where('orden', $orden)->get();
    if (!is_null($pedidos) && count($pedidos) > 0) {
      foreach ($pedidos as $indice => $pedido) {
        if ($pedido->estado == $estado) {
          $pedido->estado = $estadoNuevo;
          $pedido->save();
        }
      }
    }
  }
  public static function CancelarPedido($orden)
  {
    $pedidos = self::where('orden', $orden)->get();
    if (count($pedidos) >= 1) {
      foreach ($pedidos as $key => $pedido) {
        $pedido->estado = 'cancelado';
        $pedido->save();
      }
    }
  }
  public static function BorrarTodos()
  {
    $pedidos = self::where('id', '>', '0')->get();
    if (!is_null($pedidos) && count($pedidos) >= 1) {
      foreach ($pedidos as $key => $pedido) {
        $pedido->delete();
      }
    }
  }
}
