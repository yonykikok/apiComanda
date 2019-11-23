<?php

namespace Controllers;

use Models\Mozo;
use Models\Mesa;
use Models\Comida;
use Models\Cliente;
use Models\Bebida;
use Models\Postre;
use Models\Trago;
use Models\PedidoMozo;
use Models\PedidoTrago;
use Models\PedidoBebida;
use Models\PedidoComida;
use Models\PedidoPostre;
use Helpers\JWTAuth;
use Helpers\AppConfig as Config;
use Helpers\FilesHelper as Files;
use Helpers\ImagesHelper as Images;
use Middleware\TokenValidatorMiddleware;
use Illuminate\Database\Capsule\Manager as Capsule;

class MozosController //implements IController
{
  public static function GetAll($request, $response, $args)
  {
    return json_encode(Mozo::all());
  }
  public static function TraerComidas($request, $response, $args)
  {
    return json_encode(Comida::all());
  }
  public static function GetOne($request, $response, $args)
  {
    $id = $request->getAttributes()["id"];
    $mozo = Mozo::find($id);
    if ($mozo) {
      $responseObj = ["message" => "mozo encontrado", "mozo" => $mozo];
      return $response->withJson(json_encode($responseObj), 200);
    } else {
      $responseObj = ["message" => "mozo no encontrada"];
      return $response->withJson(json_encode($responseObj), 401);
    }
  }

  public static function Create($request, $response, $args)
  {
    //si viaja en form es parsed body y es array
    //si viaja como raw json: json_decode($request->getBody()); y es objeto
    $data = $request->getParsedBody();

    $mozo = new Mozo;
    $mozo->id = Mozo::LastInsertId() + 1;
    $mozo->nombre = $data["nombre"];
    $mozo->apellido = $data["apellido"];
    $mozo->dni = $data["dni"];
    $mozo->image = Mozo::SaveImage($request, $mozo->id);
    $mozo->save();

    $responseObj = ["message" => "mozo creado", "mozo" => $mozo];
    return $response->withJson(json_encode($responseObj), 200);
  }

  public static function TomarPedido($request, $response, $args)
  {
    $ordenCompleta = $request->getParsedBody(); //obtengo la orden completa del cliente

    $numeroDeOrden = self::generarCodigoAlfaNumerico(5); //genero un codigo alfanumerico como numero de orden
    $mesa = Mesa::BuscarMesaDisponible($ordenCompleta['mesa']['ubicacion'], $ordenCompleta['mesa']['asientos']);

    if (!is_null($mesa)) {
      foreach ($ordenCompleta as $key => $value) {
        switch ($key) {
          case 'comidas':
            Comida::ArmarPedido($value, $numeroDeOrden);
            break;
          case 'bebidas':
            Bebida::ArmarPedido($value, $numeroDeOrden);
            break;
          case 'postres':
            Postre::ArmarPedido($value, $numeroDeOrden);
            break;
          case 'tragos':
            Trago::ArmarPedido($value, $numeroDeOrden);
            break;
          case 'token':
            $datosMozo = JWTAuth::GetPayload($value['token']);  //obtengo los datos del mozo que toma el pedido
            break;
          default:
            break;
        }
      }
      //self::mostrarTodosLosPedidos($numeroDeOrden);
      $pedidoMozo = new PedidoMozo();
      //$pedidoMozo->idMozo = $datosMozo->id;
      $pedidoMozo->orden = $numeroDeOrden;
      $pedidoMozo->mesa = $mesa->mesa;
      $pedidoMozo->estado = 'en preparacion';
      $pedidoMozo->facturacion = self::CalcularTotalAPagarPorElPedido($numeroDeOrden, false);
      $pedidoMozo->save();

      $cliente = new Cliente();
      $cliente->nombre = $ordenCompleta['cliente']['nombre'];
      $cliente->orden = $numeroDeOrden;
      $cliente->mesa = $mesa->mesa;
      $cliente->save();
      Mesa::cantidadDeUsosMasMas($mesa['mesa']);
      Mesa::cambiarEstadoMesa($mesa['mesa'], 'esperando pedido');
    } else {
      echo "<br>Sin mesas disponible lo sentimos<br>";
    }
    $responseObj = ["message" => "PedidoCreado", "PedidoCompleto: " => "LALALALA"];
    return $response->withJson($responseObj, 200);
  }
  public static function CancelarPedido($request, $response, $args)
  {
    $informacion = $request->getParsedBody();
    if (isset($informacion["mesa"]) && isset($informacion["orden"])) {
      $pedido = PedidoMozo::where('mesa', $informacion['mesa'])->where('orden', $informacion['orden'])->first();
      $mesa = Mesa::where('mesa', $informacion["mesa"])->first();

      if (!is_null($pedido) && !is_null($mesa)) {
        if ($mesa->estado == 'esperando pedido') {
          $pedido->estado = 'cancelado';
          $mesa->estado = 'cancelado';

          PedidoComida::CancelarPedido($pedido->orden);
          PedidoPostre::CancelarPedido($pedido->orden);
          PedidoBebida::CancelarPedido($pedido->orden);
          PedidoTrago::CancelarPedido($pedido->orden);

          Comida::DescontarVendidas($pedido->orden);
          Trago::DescontarVendidas($pedido->orden);
          Bebida::DescontarVendidas($pedido->orden);
          Postre::DescontarVendidas($pedido->orden);
          $pedido->save();
          $mesa->save();
          echo 'pedido Cancelado';
        } else {
          echo 'solo se puede cancelar el pedido si aun no fue entregado';
        }
      } else {
        echo 'no se encontro el pedido que coincida con mesa y orden';
      }
    } else {
      echo "debe indicar mesa y orden a cancelar.";
    }
  }
  public static function ActualizarEstadoPedido($orden)
  {
    $estadoListo = 'listo para servir';
    $pedidoComida = PedidoComida::where('orden', $orden)->get('estado')->first();
    $pedidoBebida = PedidoBebida::where('orden', $orden)->get('estado')->first();
    $pedidoTrago = PedidoTrago::where('orden', $orden)->get('estado')->first();
    $pedidoPostre = PedidoPostre::where('orden', $orden)->get('estado')->first();

    if (
      $pedidoComida['estado'] == $estadoListo && $pedidoBebida['estado'] == $estadoListo &&
      $pedidoTrago['estado'] == $estadoListo && $pedidoPostre['estado'] == $estadoListo
    ) {
      $ordenDelMozo = Mozo::where('orden', $orden)->first();
      $ordenDelMozo->estado = 'en camino';
      $ordenDelMozo->save();
      echo 'Pedido En Camino';
    }
  }
  public static function CobrarPedido($request, $response, $args)
  {
    $data = $request->getParsedBody();
    if (isset($data['mesa']) && isset($data['orden'])) //si ingreso mesa y orden
    {
      $pedido = PedidoMozo::where('orden', $data['orden'])->where('mesa', $data['mesa'])->first(); //buscamos el pedido
      if (!is_null($pedido) && count($pedido) > 0) {
        $clienteACobrar = Cliente::where('mesa', $data['mesa'])->first(); //buscamos al cliente de esa mesa
        $mesa = Mesa::where('mesa', $data['mesa'])->first(); //traemos la mesa para cambiar el estado
        if (!is_null($clienteACobrar) && count($clienteACobrar) > 0 && !is_null($mesa) && count($mesa) > 0) {
          self::CalcularTotalAPagarPorElPedido($data['orden'], true);
          if ($mesa->estado == 'comiendo' && $pedido->estado != 'cancelado') {
            Mesa::cambiarEstadoMesa($clienteACobrar->mesa, 'cliente pagando');
          } else {
            if ($mesa->estado != 'cliente pagando') {
              return $response->withJson("La mesa aun no recibio su pedido.<br>", 401);
            } else {
              return $response->withJson("Ya se le cobro a esa mesa.<br>", 401);
            }
          }
        } else {
          return $response->withJson("No se encontro al cliente de esa mesa<br>", 401);
        }
      } else {
        return $response->withJson("No se encontro el pedido<br>", 401);
      }
    } else {
      return $response->withJson("Ingrese la mesa y orden a la cual cobrar<br>", 401);
    }
  }
  public static function CalcularTotalAPagarPorElPedido($orden, $bool)
  {
    $totalComidas = PedidoComida::CalcularCostoDelPedido($orden, $bool);
    $totalBebidas = PedidoBebida::CalcularCostoDelPedido($orden, $bool);
    $totalPostres = PedidoPostre::CalcularCostoDelPedido($orden, $bool);
    $totalTragos = PedidoTrago::CalcularCostoDelPedido($orden, $bool);
    $totalAPagar = $totalComidas + $totalBebidas + $totalPostres + $totalTragos;
    $propinaDelMoso = $totalAPagar * 0.10;
   /* if ($bool) {
      echo 'Costo servicio del mozo(10%) --- $' . $propinaDelMoso . '<br>';
      echo '<br>TOTAL: $' . $totalAPagar . '<br>';
    }*/
    return $totalAPagar;
  }
  public static function ServirPedido($request, $response, $args)
  {
    $data = $request->getParsedBody();
    if (isset($data['orden'])) {
      $pedido = PedidoMozo::where('orden', $data['orden'])->get()->first();
      if (!is_null($pedido)) {
        if ($pedido->estado == 'en camino') {
          $pedido->estado = 'entregado';
          $pedido->save();
          Mesa::cambiarEstadoMesa($pedido->mesa, 'comiendo');
          PedidoComida::CambiarEstado($pedido->orden, 'listo para servir', 'entregado');
          PedidoBebida::CambiarEstado($pedido->orden, 'listo para servir', 'entregado');
          PedidoPostre::CambiarEstado($pedido->orden, 'listo para servir', 'entregado');
          PedidoTrago::CambiarEstado($pedido->orden, 'listo para servir', 'entregado');
          echo ($pedido);
        } else {
          if ($pedido->estado == 'entregado') {
            echo 'el pedido ya fue entregado.';
          } else {
            echo 'aun no se puede entregar ese pedido, hay platos que no se terminaron.';
          }
        }
      }
    }
  }
  // var_dump($arrayNombre);
  /* $archivos=$request->getUploadedFiles();
          foreach ($archivos as $key => $archivo) {
            var_dump($archivo->getClientFilename());
          }
          die();*/
  public static function TomarFotografia($request, $response, $args)
  {
    $data = $request->getParsedBody();
    if (isset($data['mesa']) && isset($data['orden'])) {
      $pedidoMozo = Mozo::where('orden', $data['orden'])->where('mesa', $data['mesa'])->first();
      if (!is_null($pedidoMozo) && count($pedidoMozo) > 0) {
        $mesa = Mesa::where('mesa', $data['mesa'])->first();
        if (!is_null($mesa) && count($mesa) > 0) {
          $cliente = Cliente::where('mesa', $mesa['mesa'])->where('orden', $data['orden'])->first();
          if (!is_null($cliente) && count($cliente) > 0) {
            if ($mesa->estado != 'cerrada' && $mesa->estado != 'libre') {
              $arrayNombre = array("nombre" => $cliente->nombre . '-', "mesa" => $mesa['mesa'] . '-', "orden" => $cliente->orden);
              $pathFinal = self::cargarFoto($arrayNombre);
              echo ($pedidoMozo);

              $pedidoMozo->foto = $pathFinal;
              $pedidoMozo->save();
              echo '<br>Foto De Mesa Actualizada';
            } else {
              echo 'la mesa esta vacia';
            }
          }
        } else {
          echo 'no se encontro la mesa';
        }
      } else {
        echo 'no se encontro el pedido del mozo que coincida con la mesa: ' . $data['mesa'] . ' y la orden: ' . $data['orden'];
      }
    } else {
      echo "indique a que mesa tomarle una fotografia y el codigo de orden para guardarlo";
    }
    die();
  }

  /**
   * Carga una foto y la renombre con los campos del array que le pasemos como argumento
   * @param array $arrayNombre es el array que contendra los datos para el nombre de la imagen
   */
  public static function cargarFoto($arraNombre)
  {
    $pathImagen = $_FILES['image']['tmp_name'];

    if (isset($_FILES['logo'])) //opcional por si cargamos un logo
    {
      $pathLogo = $_FILES['logo']['tmp_name'];
    } else {
      $pathLogo = "./public/img/fotosPng/utn.png"; //logo por default

    }
    $nameImg = $_FILES['image']['name'];

    $nameImg = self::changeImgName($nameImg, $arraNombre);
    $pathNewImg = "./public/img/mesas/" . $nameImg;


    if (file_exists($pathNewImg)) {

      $auxNewName = self::AddDateTimeImg($nameImg);
      $pathBackUpImg = "./public/img/mesasBackUp/" . $auxNewName;

      if (rename($pathNewImg, $pathBackUpImg)) //mueve la image vieja a otra carpeta (backup)
      {
        self::CrearImgConMarca($pathImagen, $pathLogo, $pathNewImg);
      } else {
        echo "No se pudo crear la imagen";
      }
    } else {
      self::CrearImgConMarca($pathImagen, $pathLogo, $pathNewImg);
    }
    return $pathNewImg;
  }

  public static function changeImgName($nameImg, $arrayNewName)
  {
    $arrayNameImg = explode('.', $nameImg); //separo el nombre de la imagen por el '.' y creo un array
    $arrayNameImg[0] = ""; //borramos el nombre de la imagen
    foreach ($arrayNewName as $auxName) {
      $arrayNameImg[0] .= $auxName; //en la parte izquierda del punto ponemos el nombre
    }
    $nameImg = $arrayNameImg[0] . "." . $arrayNameImg[1];
    return $nameImg;
  }

  public static function AddDateTimeImg($nameImg)
  {
    $arrayNameImg = explode('.', $nameImg); //creo un array y separo

    $auxNewName = $arrayNameImg[0] . date("-d-m-Y") . date("-h-i-sa") . "." . $arrayNameImg[1]; //creo el nuevo nombre de la imagen agregando fecha y hora      
    return $auxNewName;
  }

  public static function CrearImgConMarca($path, $pathLogo, $pathNewImg)
  {

    $marca = imagecreatefrompng($pathLogo); //creamos el sello
    $img = imagecreatefromjpeg($path); //creamos la imagen        
    $right = 10;
    $bottom = 10;
    $jx = imagesx($marca);
    $jy = imagesY($marca);

    imagecopy($img, $marca, imagesx($img) - $jx - $right, imagesy($img) - $jy - $bottom, 0, 0, imagesx($marca), imagesy($marca));


    move_uploaded_file($path, $pathNewImg);
    imagepng($img, $pathNewImg); //guarda la imagen que creamos con el sello de agua en el pathNewImg
  }
  public static function mostrarTodosLosPedidos($numeroDeOrden)
  {
    $PedidoAMostrar = PedidoComida::MostrarPedido($numeroDeOrden);
    echo '<h4>Cocina</h4>';
    echo $PedidoAMostrar;
    echo '<h4>Barra De Choperas</h4>';
    $PedidoAMostrar = PedidoBebida::MostrarPedido($numeroDeOrden);
    echo $PedidoAMostrar;
    echo '<h4>Candy Bar</h4>';
    $PedidoAMostrar = PedidoPostre::MostrarPedido($numeroDeOrden);
    echo $PedidoAMostrar;
    echo '<h4>Tragos y Vinos</h4>';
    $PedidoAMostrar = PedidoTrago::MostrarPedido($numeroDeOrden);
    echo $PedidoAMostrar;
  }
  public static function generarCodigoAlfaNumerico($longitud)
  {
    $key = '';
    $pattern = '0123456789abcdefghijklmnopqrstuvwxyz';
    $max = strlen($pattern) - 1;
    for ($i = 0; $i < $longitud; $i++) $key .= $pattern{
      mt_rand(0, $max)};
    return $key;
  }
  public static function generarCodigoSoloLetras($longitud)
  {
    $key = '';
    $pattern = 'abcdefghijklmnopqrstuvwxyz';
    $max = strlen($pattern) - 1;
    for ($i = 0; $i < $longitud; $i++) $key .= $pattern{
      mt_rand(0, $max)};
    return $key;
  }

  public static function MostrarMenu($request, $response, $args)
  {

    $comidas = Comida::all();
    $bebidas = Bebida::all();
    $tragos = Trago::all();
    $postres = Postre::all();
    $array = array(
      "comidas" => $comidas,
      "bebidas" => $bebidas,
      "tragos" => $tragos,
      "postres" => $postres
    );
    // echo "<h3>----------Comidas---------</h3>";
    // foreach ($comidas as $key => $value) {
    //   echo 'Cod: '.$value['id'].'<br>';
    //   echo 'Nombre: '.$value['nombre'].'<br>';
    //   echo 'Precio: '.$value['precio'].'<br>';
    //   echo "---------------------------------<br>";
    // }

    // echo "<h3>----------Bebidas---------</h3>";
    // foreach ($bebidas as $key => $value) {
    //   echo 'Cod: '.$value['id'].'<br>';
    //   echo 'Nombre: '.$value['nombre'].'<br>';
    //   echo 'Precio: '.$value['precio'].'<br>';
    //   echo "---------------------------------<br>";
    // }

    // echo "<h3>----------Tragos---------</h3>";
    // foreach ($tragos as $key => $value) {
    //   echo 'Cod: '.$value['id'].'<br>';
    //   echo 'Nombre: '.$value['nombre'].'<br>';
    //   echo 'Precio: '.$value['precio'].'<br>';
    //   echo "---------------------------------<br>";
    // }

    // echo "<h3>----------Postres---------</h3>";
    // foreach ($postres as $key => $value) {
    //   echo 'Cod: '.$value['id'].'<br>';
    //   echo 'Nombre: '.$value['nombre'].'<br>';
    //   echo 'Precio: '.$value['precio'].'<br>';
    //   echo "---------------------------------<br>";
    // }
    return json_encode($array);
    //return $response->withJson(json_encode($array), 200);
  }
  public static function Update($request, $response, $args)
  {
    $body = $request->getParsedBody();

    $mozo = Mozo::find($args["id"]);
    if (!$mozo) {
      return $response->withJson("mozo inexistente", 200);
    }
    $mozo->nombre = $body["nombre"];
    $mozo->apellido = $body["apellido"];
    $mozo->dni = $body["dni"];
    $mozo->image = Mozo::SaveImage($request, $mozo->id);
    $mozo->save();

    return $response->withJson("mozo actualizado", 200);
  }

  public static function Delete($request, $response, $args)
  {
    $body = $request->getParsedBody();
    if (!isset($body["id"])) {
      return $response->withJson("debe especificar id", 400);
    }
    $mozo = Mozo::find($body["id"]);
    if (!$mozo) {
      return $response->withJson("mozo inexistente", 200);
    }
    $mozo->delete();
    return $response->withJson("mozo eliminado");
  }
  public static function SeguirConElPedido($ordenCompleta)
  {
    $retorno = false;
    if (isset($ordenCompleta['comidas'])) {
      if (isset($ordenCompleta['bebidas'])) {
        if (isset($ordenCompleta['postres'])) {
          if (isset($ordenCompleta['tragos'])) {
            if (isset($ordenCompleta['cliente'])) {
              if (isset($ordenCompleta['mesa'])) {
                $retorno = true;
              } else {
                echo 'Indique que tipo de mesa necesita(mesa)';
              }
            } else {
              echo 'indique el nombre del cliente (cliente)';
            }
          } else {
            echo 'indique los tragos que va a encargar(tragos)';
          }
        } else {
          echo 'indique los postres que va a encargar(postres)';
        }
      } else {
        echo 'indique las bebidas que va a encargar(bebidas)';
      }
    } else {
      echo 'indique las comidas que va a encargar(comidas)';
    }
    return $retorno;
  }

  public static function EsUnPedidoValido($ordenCompleta)
  {
    $retorno = false;
    if (
      isset($ordenCompleta['comidas']['id']) && isset($ordenCompleta['comidas']['cantidad']) &&
      count($ordenCompleta['comidas']['id']) > 0 && count($ordenCompleta['comidas']['cantidad']) > 0 &&
      $ordenCompleta['comidas']['cantidad'] > 0
    ) {
      $retorno = true;
    } else if (
      isset($ordenCompleta['bebidas']['id']) && isset($ordenCompleta['bebidas']['cantidad']) &&
      count($ordenCompleta['bebidas']['id']) > 0 && count($ordenCompleta['bebidas']['cantidad']) > 0 &&
      $ordenCompleta['bebidas']['cantidad'] > 0
    ) {
      $retorno = true;
    } else if (
      isset($ordenCompleta['tragos']['id']) && isset($ordenCompleta['tragos']['cantidad']) &&
      count($ordenCompleta['tragos']['id']) > 0 && count($ordenCompleta['tragos']['cantidad']) > 0 &&
      $ordenCompleta['tragos']['cantidad'] > 0
    ) {
      $retorno = true;
    } else if (
      isset($ordenCompleta['postres']['id']) && isset($ordenCompleta['postres']['cantidad']) &&
      count($ordenCompleta['postres']['id']) > 0 && count($ordenCompleta['postres']['cantidad']) > 0 &&
      $ordenCompleta['postres']['cantidad'] > 0
    ) {
      $retorno = true;
    }
  }

  public static function ObtenerPedidoDelMozoExistente($request, $response, $args)
  {
    $data = $request->getParsedBody();
    $retorno = null;
    if (!is_null($data['orden']) && !is_null($data['mesa'])) {
      $retorno = PedidoMozo::where('orden', $data['orden'])->where('mesa', $data['mesa'])->first();
      if (!is_null($retorno)) {
        return $retorno;
      } else {
        echo 'No se encontro ningun pedido con esa mesa y esa orden';
      }
    } else {
      echo 'Indique Mesa y Orden a Modificar <br>';
    }

    return $retorno;
  }
  public static function CambiarPedidoComida($request, $response, $args)
  {
    $pedidoMozo = self::ObtenerPedidoDelMozoExistente($request, $response, $args);
    if (!is_null($pedidoMozo)) {
      echo 'si';
    }
  }
}
