<?php
namespace Models;

use Helpers\AppConfig as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
class Cliente extends Model
{
  protected $table = "clientes";
  public $timestamps = false;

  public static function LastInsertId()
  {
    $cliente =  Cliente::select("id")->orderBy("id", "desc")->first();
    return is_null($cliente) ? 0 : $cliente->id;
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
