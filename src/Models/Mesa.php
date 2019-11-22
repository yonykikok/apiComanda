<?php
namespace Models;

use Helpers\AppConfig as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
class Mesa extends Model
{
  protected $table = "mesas";
  public $timestamps = false;

  public static function LastInsertId()
  {
    $mesa =  Mesa::select("id")->orderBy("id", "desc")->first();
    return is_null($mesa) ? 0 : $mesa->id;
  }
  public static function BuscarMesaDisponible($ubicacion,$cantPersonas){

    $mesa=Mesa::where('asientos',$cantPersonas)->where('ubicacion',$ubicacion)->where('estado','libre')->first();
    
    if(is_null($mesa))//si no hay mesas disponible
    {
      echo 'No hay mesas '.$ubicacion.' de '.$cantPersonas.' asientos disponible<br>';
      $mesa=Mesa::where('asientos','>',$cantPersonas)->where('ubicacion',$ubicacion)->where('estado','libre')->first();
    /*  if(is_null($mesa))
      {
        echo "Lo siento, no hay mesas disponible para la cantidad de personas que pide";
      }
      else{
        if($mesa->asientos>6)
        {
          echo 'pero se armara una mesa especial de '.$cantPersonas.' personas en "'.$mesa->mesa.'"';
        }
        else
        {
          echo 'pero se encontro mesa disponible para '.$mesa->asientos.' personas en "'.$mesa->mesa.'"';
        }      
      }*/
    }
      return $mesa;
  }
  public static function cambiarEstadoMesa($mesa,$estado)
  {    
    $mesa = Mesa::where('mesa',$mesa)->first();
    if(!$mesa)
    {
      echo 'no se encontro la mesa-> cambiarEstadoMesa';
      return false;
    }
    $mesa->estado = $estado;
    $mesa->save();
    return true;
  }
  public static function cantidadDeUsosMasMas($mesa)
  {    
    $miMesa = Mesa::where('mesa',$mesa)->first();
    if(!is_null($miMesa))
    {
      $miMesa->usos=$miMesa->usos+1;
      $miMesa->save();
    }    
  }
  public static function LimpiarMesas()
  {
    $mesas=self::where('id','>','0')->get();
    if(!is_null($mesas) && count($mesas)>=1)
    {
      foreach ($mesas as $key => $mesa) {
        $mesa->estado='libre';
        $mesa->usos=0;
        $mesa->save();
      }
    }
  }
}
