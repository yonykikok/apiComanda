<?php
namespace Models;

use Helpers\AppConfig as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
class Trago extends Model
{
  protected $table = "menutragos";
  public $timestamps = false;

  public static function LastInsertId()
  {
    $trago =  Trago::select("id")->orderBy("id", "desc")->first();
    return is_null($trago) ? 0 : $trago->id;
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
  public static function TragoVendidaMasMas($id,$cantidadVendida)
  {
    $trago=self::where('id',$id)->first();
      if(!is_null($trago))
    {
      $trago->cantidadVendida=$trago->cantidadVendida+$cantidadVendida;
      $trago->save();
    }
  }
  public static function ArmarPedido($listado,$numeroDeOrden)
  {
    $cantidades=array();
    $pedidos=array();
    if(!is_null($listado) && count($listado)>0)
    { /*
      if( strpos($listado['id'],',')!=false && strpos($listado['cantidad'],',')!=false)
      {
        $cantidades=explode(',',$listado['cantidad']);
        $pedidos=explode(',',$listado['id']);
        for($i=0;$i<count($pedidos);$i++)
        {
          if(self::VerificarExistencia($pedidos[$i]))
          {
              $cantidadPedida=$cantidades[$i];
              $trago=new PedidoTrago;
              $trago->cantidad=$cantidadPedida;
              $trago->idTrago=$pedidos[$i];
              $trago->orden=$numeroDeOrden;  
            $trago->estado='pendiente';
            self::TragoVendidaMasMas($trago->idTrago,$trago->cantidad);
            $trago->save();
          }
          else{
            echo '<br>Codigo de menu erroneo :'.$pedidos[$i];
          }
        }

      }
      else
      {
        if(self::VerificarExistencia($listado['id']))
        {
          $trago=new Pedidotrago;
          $trago->cantidad=$listado['cantidad'];
          $trago->idTrago=$listado['id'];
          $trago->orden=$numeroDeOrden;  
          $trago->estado='pendiente';
            self::TragoVendidaMasMas($trago->idTrago,$trago->cantidad);
            $trago->save();
        }
        else
        {
          echo '<br>Codigo de menu erroneo :'.$listado['id'];
        }
      }*/
    }
    else
      {
        $trago=new PedidoTrago;
        $trago->cantidad=0;
        $trago->idTrago=1000;
        $trago->orden=$numeroDeOrden;  
        $trago->estado='pendiente';
            self::TragoVendidaMasMas($trago->idTrago,$trago->cantidad);
            $trago->save();
      }
  }
  public static function DescontarVendidas($orden)
  {
    $pedido=PedidoTrago::where('orden',$orden)->first();
    if(!is_null($pedido))
    {
      $menu=self::where('id',$pedido->idTrago)->first();
      if(!is_null($menu))
      {
        $menu->cantidadVendida=$menu->cantidadVendida-$pedido->cantidad;
        $menu->save();
      }else
      {
        echo 'no se encontro el trago<br>';
      }
    }
    else
      {
        echo 'no se encontro el pedido en tragos<br>';
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
