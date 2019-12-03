<?php
namespace Models;

use Helpers\AppConfig as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use Models\PedidoBebida;
class Bebida extends Model
{
  protected $table = "menubebidas";
  public $timestamps = false;

  public static function LastInsertId()
  {
    $bebida =  Bebida::select("id")->orderBy("id", "desc")->first();
    return is_null($bebida) ? 0 : $bebida->id;
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
 public static function BebidaVendidaMasMas($id,$cantidadVendida)
 {
   $bebida=self::where('id',$id)->first();
     if(!is_null($bebida))
   {
     $bebida->cantidadVendida=$bebida->cantidadVendida+$cantidadVendida;
     $bebida->save();
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
          $bebida=new PedidoBebida;
          $bebida->cantidad=$cantidadPedida;
          $bebida->idBebida=$pedidos[$i];
          $bebida->orden=$numeroDeOrden;  
          $bebida->estado='pendiente';
          self::BebidaVendidaMasMas($bebida->idBebida,$bebida->cantidad);
          $bebida->save();
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
          $bebida=new Pedidobebida;
          $bebida->cantidad=$listado['cantidad'];
          $bebida->idBebida=$listado['id'];
          $bebida->orden=$numeroDeOrden;  
          $bebida->estado='pendiente';
          self::BebidaVendidaMasMas($bebida->idBebida,$bebida->cantidad);
          $bebida->save();
        }
        else
        {
          echo '<br>Codigo de menu erroneo :'.$listado['id'];
        }
    }
  }
  else
    {
      $bebida=new PedidoBebida;
      $bebida->cantidad=0;
      $bebida->idBebida=1000;
      $bebida->orden=$numeroDeOrden;  
      $bebida->estado='listo para servir';
      self::BebidaVendidaMasMas($bebida->idBebida,$bebida->cantidad);
      $bebida->save();
    }
  }
  public static function DescontarVendidas($orden)
  {
    $pedido=PedidoBebida::where('orden',$orden)->first();
    if(!is_null($pedido))
    {
      $menu=self::where('id',$pedido->idBebida)->first();
      if(!is_null($menu))
      {
        $menu->cantidadVendida=$menu->cantidadVendida-$pedido->cantidad;
      $menu->save();
      }else
      {
        echo 'no se encontro la bebida<br>';
      }
    }
    else
      {
        echo 'no se encontro el pedido de bebidas<br>';
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
