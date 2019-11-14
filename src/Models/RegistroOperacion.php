<?php
namespace Models;

use Helpers\AppConfig as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
class RegistroOperacion extends Model
{
  protected $table = "registrooperaciones";
  public $timestamps = false;

  public static function BorrarTodos()
  {
    $registros=self::where('id','>','0')->get();
    if(!is_null($registros) && count($registros)>=1)
    {
      foreach ($registros as $key => $registro) {
        $registro->delete();
      }
    }
  }
 
}
