<?php
namespace Models;

use Helpers\AppConfig as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
class Encuesta extends Model
{
  protected $table = "encuestas";
  public $timestamps = false;
  
  public static function VerificarExistencia($encuesta)
  {
    $encuesta=self::where('orden',$encuesta->orden)->where('mesa',$encuesta->mesa)->first();
    return $encuesta;
  }
  public static function MostrarEncuesta($encuesta)
  {
    if(!is_null($encuesta))
    {
      echo 'Id del mozo: '.$encuesta->idMozo.'<br>';
      echo 'Mesa: '.$encuesta->idMozo.'<br>';
      echo 'Orden: '.$encuesta->orden.'<br>';
      echo 'Puntuacion del mesa: '.$encuesta->puntosmesa.'<br>';
      echo 'Puntuacion del mozo: '.$encuesta->puntosmozo.'<br>';
      echo 'Puntuacion del cocinero: '.$encuesta->puntoscocinero.'<br>';
      echo 'Puntuacion del restaurante: '.$encuesta->puntosrestaurante.'<br>';
      if($encuesta->puntuacionTotal>=32)
      { 
        echo '<p style="color:green; font-size:120%">Experiencia del cliente: '.$encuesta->experiencia.'</p>';
      }
      else if($encuesta->puntuacionTotal>25&&$encuesta->puntuacionTotal<32)
      {
        echo '<p style="color:orange; font-size:120%">Experiencia del cliente: '.$encuesta->experiencia.'</p>';
      }
      else
      {
        echo '<p style="color:red; font-size:120%">Experiencia del cliente: '.$encuesta->experiencia.'</p>';
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