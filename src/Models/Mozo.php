<?php
namespace Models;

use Helpers\AppConfig as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
class Mozo extends Model
{
  protected $table = "pedidosmozo";
  public $timestamps = false;

  public static function LastInsertId()
  {
    $mozo =  Mozo::select("id")->orderBy("id", "desc")->first();
    return is_null($mozo) ? 0 : $mozo->id;
  }


  
}
