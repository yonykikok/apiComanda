<?php

namespace Controllers;

use Models\PedidoComida;
use Models\PedidoBebida;
use Models\PedidoPostre;
use Models\PedidoTrago;
use Models\Cliente;
use Models\Encuesta;
use Models\PedidoMozo;
use Models\Mesa;

use Controllers\MozosController;

class ClientesController //implements IController
{
  public static function obtenerPedidosPorUsuario($request, $response, $args)
  { 
    var_dump($args["nombre"]);
    return json_encode(Cliente::all());
  }
  public static function GetAll($request, $response, $args)
  {
    return json_encode(Cliente::all());
  }

  public static function GetOne($request, $response, $args)
  {
    $id = $request->getAttributes()["id"];
    $cliente = Cliente::find($id);
    if($cliente)
    {
      $responseObj = ["message" => "cliente encontrado", "cliente" => $cliente];
      return $response->withJson(json_encode($responseObj), 200);
    }
    else
    {
      $responseObj = ["message" => "cliente no encontrada"];
      return $response->withJson(json_encode($responseObj), 401);
    }
  }
  public static function VerPedido($request, $response, $args)
  {
    $informacion=$request->getParsedBody();
    MozosController::mostrarTodosLosPedidos($informacion['orden']);
  }
  public static function verEstado($request, $response, $args)
  {
    $informacion=$request->getParsedBody();
    $numeroDeOrden=$informacion['orden'];

    $estadoComida=PedidoComida::where('orden',$informacion['orden'])->get('estado')->first();
    $estadoBebida=PedidoBebida::where('orden',$informacion['orden'])->get('estado')->first();
    $estadoPostre=PedidoPostre::where('orden',$informacion['orden'])->get('estado')->first();
    $estadoTrago=PedidoTrago::where('orden',$informacion['orden'])->get('estado')->first();

    $comida=PedidoComida::MostrarPedido($numeroDeOrden);
    $bebida=PedidoBebida::MostrarPedido($numeroDeOrden);
    $postre=PedidoPostre::MostrarPedido($numeroDeOrden);
    $trago=PedidoTrago::MostrarPedido($numeroDeOrden);
    $listo='listo para servir';
    if($estadoComida['estado']==$listo&&$estadoBebida['estado']==$listo&&
    $estadoPostre['estado']==$listo&&$estadoTrago['estado']==$listo)
    {
      echo 'Pedido listo, esta en camino';
    }
    else
    {
      echo'<h4>Cocina ('.$estadoComida['estado'].')</h4>';
      echo $comida;
      echo'<h4>Barra De Choperas ('.$estadoBebida['estado'].')</h4>';
      echo $bebida;
      echo'<h4>Candy Bar ('.$estadoPostre['estado'].')</h4>';
      echo $postre;
      echo'<h4>Tragos y Vinos ('.$estadoTrago['estado'].')</h4>';
      echo $trago;
    }
  }
  public static function calcularDemora($request, $response, $args){
    $informacion=$request->getParsedBody();
    if(isset($informacion['orden'])&&isset($informacion['mesa']))
    {
      $pedidoMozo=PedidoMozo::where('orden',$informacion['orden'])->where('mesa',$informacion['mesa'])->first();
      $mesaPedida=PedidoMozo::where('mesa',$informacion['mesa'])->first();
      if(!is_null($pedidoMozo) && !is_null($mesaPedida))
      {        
        $numeroDeOrden=$informacion['orden'];
        $demora=0;
        $estadoComida=PedidoComida::where('orden',$informacion['orden'])->get('estado')->first();
        $estadoPostre=PedidoPostre::where('orden',$informacion['orden'])->get('estado')->first();
        $estadoTrago=PedidoTrago::where('orden',$informacion['orden'])->get('estado')->first();
        $estadoBebida=PedidoBebida::where('orden',$informacion['orden'])->get('estado')->first();
        //agrego un tiempo que simule la demora de la preparacion 
        if($estadoComida['estado']=='entregado'&&$estadoPostre['estado']=='entregado'&&
        $estadoTrago['estado']=='entregado'&&$estadoBebida['estado']=='entregado')
        {
          echo "<h3>Entregado</h3>";
        }
        else
        {
          //var_dump($informacion['orden']);
          $demoraComida=self::calcularDemoraPorPedido($estadoComida['estado'],20,10,1);
          $demoraPostre=self::calcularDemoraPorPedido($estadoPostre['estado'],10,5,1);
          $demoraTrago=self::calcularDemoraPorPedido($estadoTrago['estado'],8,2,1);
          $demoraBebida=self::calcularDemoraPorPedido($estadoBebida['estado'],5,2,1);
          $demora=$demoraComida+$demoraBebida+$demoraPostre+$demoraTrago;
          
          echo '<h3>Demora estimada: '.$demora.' minutos</h3>';
        }
      }
      else
      {
        echo 'No se encontro pedido que coincida con mesa y orden';
      }
    }
    else
    {
      echo "ingrese orden y mesa";
    }
  }
  public static function calcularDemoraPorPedido($estado,$demoraMayor,$demoraIntermedia,$demoraMinima)
  {
    $demora=0;
    if($estado=='pendiente')
    {
      return $demoraMayor;
    }
    else  if($estado=='en preparacion')
    {
      return $demoraIntermedia;
    }
    else
    {
      return $demoraMinima;
    }
  }
  public static function Create($request, $response, $args)
  {
    //si viaja en form es parsed body y es array
    //si viaja como raw json: json_decode($request->getBody()); y es objeto
    $data = $request->getParsedBody(); 

    $cliente = new Cliente;
    $cliente->id = Cliente::LastInsertId()+1;
    $cliente->nombre = $data["nombre"];
    $cliente->apellido = $data["apellido"];
    $cliente->dni = $data["dni"];
    $cliente->image = Cliente::SaveImage($request, $cliente->id);
    $cliente->save();

    $responseObj = ["message" => "cliente creado", "cliente" => $cliente];
    return $response->withJson(json_encode($responseObj), 200);
  }

  public static function Update($request, $response, $args)
  {
    /* 
    //ID POR PARAMETRO EN /update/{id} para sacar
    //el id de ahi y poder hacer update de foto
      if(!isset($body["id"]))
    {
      return $response->withJson("debe especificar id", 400);
    }
    */

    $body = $request->getParsedBody();

    $cliente = Cliente::find($args["id"]);
    if(!$cliente)
    {
      return $response->withJson("cliente inexistente", 200);
    }
    $cliente->nombre = $body["nombre"];
    $cliente->apellido = $body["apellido"];
    $cliente->dni = $body["dni"];
    $cliente->image = Cliente::SaveImage($request, $cliente->id);
    $cliente->save();

    return $response->withJson("cliente actualizado", 200);
  }

  public static function Delete($request, $response, $args)
  {
    $body = $request->getParsedBody();
    if(!isset($body["id"]))
    {
      return $response->withJson("debe especificar id", 400);
    }
    $cliente = Cliente::find($body["id"]);
    if(!$cliente)
    {
      return $response->withJson("cliente inexistente", 200);
    }
    $cliente->delete();
    return $response->withJson("cliente eliminado");
  }
 

  public static function GuardarEncuesta($request, $response, $args)
  {
    $datosDeLaEncuesta=$request->getParsedBody();
     
    if(self::EsUnaEncuestaValida($datosDeLaEncuesta))
    {
      if(self::SonDatosValidos($datosDeLaEncuesta))
      {
         $pedidoMozo=PedidoMozo::where('mesa',$datosDeLaEncuesta['mesa'])->where('orden',$datosDeLaEncuesta['orden'])->first();
         $encuesta=new Encuesta();
         $encuesta->idMozo=$pedidoMozo->id;
         $encuesta->mesa=$pedidoMozo->mesa;
         $encuesta->orden=$pedidoMozo->orden;
         $encuesta->puntosmesa=$datosDeLaEncuesta['puntosMesa'];
         $encuesta->puntosmozo=$datosDeLaEncuesta['puntosMozo'];
         $encuesta->puntoscocinero=$datosDeLaEncuesta['puntosCocinero'];
         $encuesta->puntosrestaurante= $datosDeLaEncuesta['puntosRestaurante'];
         $encuesta->experiencia=$datosDeLaEncuesta['experiencia'];
         $encuesta->puntuacionTotal=$encuesta->puntosmesa+$encuesta->puntosmozo+$encuesta->puntoscocinero+$encuesta->puntosrestaurante;
         if(is_null(Encuesta::VerificarExistencia($encuesta)))
         {
           $encuesta->save();
           echo 'encuesta enviada.';
         }
         else
         {
           echo 'ya se registro su encuesta, gracias por venir.';
         }
      }
    }
  }
  
  public static function SonDatosValidos($datosDeLaEncuesta)
  {
    $retorno=false;
    $mesa=Mesa::where('mesa',$datosDeLaEncuesta['mesa'])->first();
    if($mesa->estado!='esperando pedido' && $mesa->estado!='comiendo')
    {
        $orden=$datosDeLaEncuesta['orden'];
        
        $pedidoMozo=PedidoMozo::where('mesa',$mesa['mesa'])->where('orden',$orden)->first();
      if(!is_null($pedidoMozo))
      {
        $puntosMozo=$datosDeLaEncuesta['puntosMozo'];
        $puntosCocinero=$datosDeLaEncuesta['puntosCocinero'];
        $puntosMesa=$datosDeLaEncuesta['puntosMesa'];
        $puntosRestaurante=$datosDeLaEncuesta['puntosRestaurante'];
        $experiencia=$datosDeLaEncuesta['experiencia'];
        if(($puntosCocinero<0||$puntosCocinero>10)||($puntosMesa<0||$puntosMesa>10)||//si la puntuacion esta mal tira error
        ($puntosRestaurante<0||$puntosRestaurante>10)||($puntosMozo<0||$puntosMozo>10))
        {
          echo "la puntuacion minima es 0 y la maxima 10, verifique su critica";
        }
        else//si esta todo ok con la puntuacion entra!
        {
          if(strlen($experiencia)<=66 && strlen($experiencia)>=0)
          {
            $retorno=true;
          }
          else
          {
            echo 'el texto debe contener un maximo de 66 caracteres';
          }
        }
      }
      else{
        echo 'no se encontro ningun pedido que coincida con ese codigo y esa mesa';
      }
    }
    else
    {
      echo 'la encuesta se pondra disponible cuando terminen de comer.';
    }
    return $retorno;
  }
  public static function EsUnaEncuestaValida($datosDeLaEncuesta)
  {
    $retorno=false;
    if(isset($datosDeLaEncuesta['mesa']))
    {
      if(isset($datosDeLaEncuesta['orden']))
      {
        if(isset($datosDeLaEncuesta['puntosMozo']))
        {
          if(isset($datosDeLaEncuesta['puntosCocinero']))
          {
            if(isset($datosDeLaEncuesta['puntosMesa']))
            {
              if(isset($datosDeLaEncuesta['puntosRestaurante']))
              {
                if(isset($datosDeLaEncuesta['experiencia']))
                {
                  $retorno = true;
                }
                else
                {
                  echo 'no ingreso la experiencia';
                }
              }
              else
              {
                echo 'no ingreso los puntos del restaurante (puntosRestaurante)';
              }
            }
            else
            {
              echo 'no ingreso los puntos de la mesa (puntosMesa)';
            }
          }
          else
          {
            echo 'no ingreso los puntos del cocinero (puntosCocinero)';
          }
        }
        else
        {
          echo 'no ingreso los puntos del mozo (puntosMozo)';
        }
      }
      else
      {
        echo 'no ingreso el codigo de orden recibido';
      }
    }
    else
    {
      echo 'no ingreso la mesa';
    }
    return $retorno;
  }
}
