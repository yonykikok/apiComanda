<?php
namespace Models;

use Helpers\AppConfig as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use Models\PedidoComida;
class Comida extends Model
{
  protected $table = "menucomidas";
  public $timestamps = false;

  public static function LastInsertId()
  {
    $comida =  Comida::select("id")->orderBy("id", "desc")->first();
    return is_null($comida) ? 0 : $comida->id;
  }
  public static function VerificarExistencia($id)
  {
    $retorno=false;
    $menu= self::where('id',$id)->first();
    if(!is_null($menu))
    {
      $retorno=true;
    }
    return $retorno;
  }
  public static function ComidaVendidaMasMas($id,$cantidadVendida)
  {
    $comida=self::where('id',$id)->first();
      if(!is_null($comida))
    {
      $comida->cantidadVendida=$comida->cantidadVendida+$cantidadVendida;
      $comida->save();
    }
  }
  public static function ArmarPedido($listado,$numeroDeOrden)
  {
    $cantidades=array();
    $pedidos=array();
    if(!is_null($listado) && count($listado)>0)
    {  
    
      if( strpos($listado['id'],',')!=false && strpos($listado['cantidad'],',')!=false)
      {
        $cantidades=explode(',',$listado['cantidad']);
        $pedidos=explode(',',$listado['id']);
        for($i=0;$i<count($pedidos);$i++)
        {
           
          if(self::VerificarExistencia($pedidos[$i]))
          {
            $cantidadPedida=$cantidades[$i];
            $comida=new PedidoComida;
            $comida->cantidad=$cantidadPedida;
            $comida->idComida=$pedidos[$i];
            $comida->orden=$numeroDeOrden;  
            $comida->estado='pendiente';
            self::ComidaVendidaMasMas($comida->idComida,$comida->cantidad);
            $comida->save();
          }
          else
          {
            echo '<br>Codigo de menu erroneo :'.$pedidos[$i];
          }
        }

      }
      else{
        if(self::VerificarExistencia($listado['id']))
        {
          $comida=new PedidoComida;
          $comida->cantidad=$listado['cantidad'];
          $comida->idComida=$listado['id'];
          $comida->orden=$numeroDeOrden;  
          $comida->estado='pendiente';
            self::ComidaVendidaMasMas($comida->idComida,$comida->cantidad);
            $comida->save();
        }
        else
        {
          echo '<br>Codigo de menu erroneo :'.$listado['id'];
        }
      }
    }
    else
      {
        // $comida=new PedidoComida;
        // $comida->cantidad=0;
        // $comida->idComida=1000;
        // $comida->orden=$numeroDeOrden;  
        // $comida->estado='pendiente';
        //     self::ComidaVendidaMasMas($comida->idComida,$comida->cantidad);
        //     $comida->save();
      }
  }
  public static function DescontarVendidas($orden)
  {
    $pedido=PedidoComida::where('orden',$orden)->first();
    if(!is_null($pedido))
    {
      $menu=self::where('id',$pedido->idComida)->first();
      if(!is_null($menu))
      {
        $menu->cantidadVendida=$menu->cantidadVendida-$pedido->cantidad;
        $menu->save();
      }else
      {
        echo 'no se encontro la comida<br>';
      }
    }
    else
      {
        echo 'no se encontro el pedido de comidas<br>';
      }
  }
  public static function LimpiarVendidos()
  {
    $menus=self::where('id','>','0')->get();
    if(!is_null($menus) && count($menus)>=1)
    {
      foreach ($menus as $key => $menu) {
        $menu->cantidadVendida=0;
        $menu->save();
      }
    }
  }
}
