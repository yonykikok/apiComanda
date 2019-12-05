<?php

namespace Controllers;

use Models\PedidoComida;
use Models\PedidoBebida;
use Models\PedidoPostre;
use Models\PedidoTrago;
use Models\RegistroOperacion;
use Models\RegistroLogeo;
use Models\PedidoMozo;
use Models\Encuesta;
use Models\Mesa;
use Models\Mozo;
use Models\Bebida;
use Models\Trago;
use Models\Comida;
use Models\Postre;
use Models\Cliente;
use Controllers\ClientesController;
use Helpers\JWTAuth;
use Helpers\AppConfig as Config;
use Helpers\FilesHelper as Files;
use Helpers\ImagesHelper as Images;

use Controllers\MozosController;
use Illuminate\Database\Capsule\Manager as Capsule;

class SociosController
{

  public static function PedidosEsperandoCierre($request, $response, $args)
  {
    $mesas = Mozo::where('estado', 'cliente pagando')->get();
    if ($mesas && count($mesas) > 0) {
      return $response->withJson(json_encode($mesas, 200));
    }
    return $response->withJson("Sin cierres pendientes", 200);
  }
  public static function obtenerPedidosPorUsuario($request, $response, $args)
  {
    //
    $nombre = $args["nombre"];
    $clientes = Cliente::where("nombre", $nombre)->get();
    return $response->withJson(json_encode($clientes), 200);
  }
  public static function VerPedidos($request, $response, $args)
  {
    $pedidos = PedidoMozo::all();
    foreach ($pedidos as $indice => $pedido) {
      echo "Pedido '" . $pedido->orden . "' de la mesa " . $pedido->mesa . ' ' . $pedido->estado . '<br>';
    }
  }
  public static function VerPedidosEnPreparacion($request, $response, $args)
  {
    $pedidos = PedidoMozo::where('estado', "en preparacion")->get();
    foreach ($pedidos as $indice => $pedido) {
      echo "Pedido '" . $pedido->orden . "' de la mesa " . $pedido->mesa . ' ' . $pedido->estado . '<br>';
    }
  }
  public static function VerPedidosTerminados($request, $response, $args)
  {
    $pedidos = PedidoMozo::where('estado', 'en camino')->get();
    foreach ($pedidos as $indice => $pedido) {
      echo "Pedido '" . $pedido->orden . "' de la mesa " . $pedido->mesa . ' ' . $pedido->estado . '<br>';
    }
  }
  public static function VerPedidoPorOrden($request, $response, $args)
  {
    $datos = $request->getParsedBody();
    if (isset($datos['orden'])) {
      $pedido = PedidoMozo::where('orden', $datos['orden'])->get()->first();

      if (is_null($pedido)) {
        echo 'No se encontro el pedido: ' . $datos['orden'];
      } else {
        echo "<h3>Pedido '" . $pedido->orden . "' de la mesa '" . $pedido->mesa . "' " . $pedido->estado . '</h3><br>';
        ClientesController::verEstado($request, $response, $args);
      }
    } else {
      echo 'ingrese la orden a buscar';
    }
  }
  public static function LiberarMesasCerradas($request, $response, $args)
  {
    $mesas = Mesa::get();
    if (count($mesas) > 0) {
      foreach ($mesas as $indice => $mesa) {
        if ($mesa->estado == 'cerrada' || $mesa->estado == 'cancelado') {
          $mesa->estado = 'libre';
          $mesa->save();
        }
      }
      return $response->withJson('Mesas liberadas', 200);
    } else {
      return $response->withJson('Todas las mesas estan libres', 200);
    }
  }
  public static function CerrarMesa($request, $response, $args)
  {
    $data = $request->getParsedBody();
    if (isset($data['mesa']) && isset($data['mesa'])) {
      $pedido = PedidoMozo::where('mesa', $data['mesa'])->where('orden', $data['orden'])->first();
      $mesa = Mesa::where('mesa', $data['mesa'])->first();
      if (!is_null($pedido) && count($pedido) > 0 && !is_null($mesa) && count($mesa) > 0) {
        if ($mesa->estado == 'cliente pagando' && $pedido->estado == 'cliente pagando') {
          $mesa->estado = 'cerrada';
          $pedido->estado = 'cerrado';
          $pedido->save();
          $mesa->save();
          return $response->withJson("Mesa Cerrada", 200);
        } else {
          return $response->withJson("El cliente aun no esta pagando", 200);
        }
      } else {
        return $response->withJson("El pedido o la mesa no se encontro.", 200);
      }
    } else {

      return $response->withJson("Mesa no encontrada", 200);
    }
  }
  public static function MesaMasUsada($request, $response, $args)
  {
    $lista = Mesa::get();
    $mesaMasUsada = self::BuscarMenorOMayor('mas', $lista, 'usos');
    $mesas = Mesa::where('usos', $mesaMasUsada->usos)->get();
    if (count($mesas) > 1) {
      return $response->withJson(json_encode($mesas), 200);
    } else if (count($mesas) == 1) {
      return $response->withJson("La mesa mas usada es: " . $mesaMasUsada->mesa . " con " . $mesaMasUsada->usos . " usos", 200);
    } else {
      return $response->withJson("Aun no se registran usos", 200);
    }
  }
  public static function MesaMenosUsada($request, $response, $args)
  {
    $lista = Mesa::get();
    $mesaMenos = self::BuscarMenorOMayor('menos', $lista, 'usos');
    $mesas = Mesa::where('usos', $mesaMenos->usos)->get();
    if (count($mesas) > 1) {
      return $response->withJson(json_encode($mesas), 200);
    } else if (count($mesas) == 1) {
      return $response->withJson("La mesa menos usada es: " . $mesaMenos->mesa . " con " . $mesaMenos->usos . " usos", 200);
    } else {
      return $response->withJson("Aun no se registran usos", 200);
    }
  }


  public static function FacturaMasAlta($request, $response, $args)
  {
    $pedidoConMayorFacturacion = PedidoMozo::where('facturacion', PedidoMozo::max('facturacion'))->get();
    if ($pedidoConMayorFacturacion) {
      if (count($pedidoConMayorFacturacion) > 1) {
        return $response->withJson(json_encode($pedidoConMayorFacturacion), 200);
      } else {
        return $response->withJson(json_encode($pedidoConMayorFacturacion[0]), 200);
      }
    } else {
      return $response->withJson("Sin Facturaciones", 200);
    }
  }
  public static function FacturaMasBaja($request, $response, $args)
  {
    $pedidoConMayorFacturacion = PedidoMozo::where('facturacion', PedidoMozo::min('facturacion'))->get();

    if ($pedidoConMayorFacturacion) {
      if (count($pedidoConMayorFacturacion) > 1) {
        return $response->withJson(json_encode($pedidoConMayorFacturacion), 200);
      } else {
        return $response->withJson(json_encode($pedidoConMayorFacturacion[0]), 200);
      }
    } else {
      return $response->withJson("Sin Facturaciones", 200);
    }
  }

  public static function BuscarMenorOMayor($stringMasOMenos, $lista, $campoAComparar)
  {
    $retorno = null;
    $contador = 0;
    foreach ($lista as $indice => $objeto) {
      if ($contador == 0) {
        $retorno = $objeto;
        $contador++;
      }
      switch ($stringMasOMenos) {
        case 'menos':
          if ($retorno->$campoAComparar > $objeto->$campoAComparar) {
            $retorno = $objeto;
          }
          break;
        case 'mas':
          if ($retorno->$campoAComparar < $objeto->$campoAComparar) {
            $retorno = $objeto;
          }
          break;
      }
    }
    return $retorno;
  }

  public static function MejorFacturacion($request, $response, $args)
  {
    $pedidos = PedidoMozo::get();
    $mesas = Mesa::get();
    $mesaDeMayorFacturacion = self::ObtenerFacturacionMayorOMenor($mesas, $pedidos, 'mayor');
    return $response->withJson(json_encode($mesaDeMayorFacturacion), 200);
  }
  public static function PeorFacturacion($request, $response, $args)
  {
    $pedidos = PedidoMozo::get();
    $mesas = Mesa::get();
    $mesaDeMenorFacturacion = self::ObtenerFacturacionMayorOMenor($mesas, $pedidos, 'menor');
    return $response->withJson(json_encode($mesaDeMenorFacturacion), 200);
  }
  public static function ObtenerFacturacionMayorOMenor($mesas, $pedidos, $stringMayorOMenor)
  {
    $mesaQueMasFacturo = null;
    $bandera = false;
    if ($stringMayorOMenor == 'mayor') {
      $facturacionAntes = 0;
    } else {
      $facturacionAntes = 9999999;
    }
    foreach ($mesas as $indice => $mesa) {
      $facturacionTotal = 0;
      foreach ($pedidos as $key => $pedido) {
        if ($pedido->mesa == $mesa->mesa) {
          $facturacionTotal = $facturacionTotal + $pedido->facturacion;
          $bandera = true;
        }
      }
      switch ($stringMayorOMenor) {
        case 'mayor':
          if ($facturacionTotal > $facturacionAntes && $bandera == true) {
            $facturacionAntes = $facturacionTotal;
            $mesaQueMasFacturo = $mesa;
            $facturacionMasAlta = $facturacionTotal;
            $mesaQueMasFacturo["total"] = $facturacionTotal;

          }
          $bandera = false;
          break;
        case 'menor':
          if ($facturacionTotal < $facturacionAntes && $bandera == true) {
            $facturacionAntes = $facturacionTotal;
            $mesaQueMasFacturo = $mesa;
            $facturacionMasAlta = $facturacionTotal;
            $mesaQueMasFacturo["total"] = $facturacionTotal;
          }
          $bandera = false;
          break;
      }
    }
    return $mesaQueMasFacturo;
  }
  public static function MejoresComentarios($request, $response, $args)
  {
    $encuestas = Encuesta::where('puntuacionTotal', '>=', 32)->get();
    if ($encuestas) {
      return $response->withJson(json_encode($encuestas), 200);
    } else {
      return $response->withJson("Sin comentarios", 200);
    }
  }
  public static function PeoresComentarios($request, $response, $args)
  {
    $encuestas = Encuesta::where('puntuacionTotal', '<', 32)->get();
    if ($encuestas) {
      return $response->withJson(json_encode($encuestas), 200);
    } else {
      return $response->withJson("Sin comentarios", 200);
    }
  }
  public static function PedidosMasVendidos($request, $response, $args)
  {
    $pedidos = PedidoMozo::get();
    $bebidas = Bebida::get();
    $tragos = Trago::get();
    $postres = Postre::get();
    $comidas = Comida::get();

    $bebidaMasVendida = self::BuscarMenorOMayor('mas', $bebidas, 'cantidadVendida');
    $tragoMasVendida = self::BuscarMenorOMayor('mas', $tragos, 'cantidadVendida');
    $postreMasVendida = self::BuscarMenorOMayor('mas', $postres, 'cantidadVendida');
    $comidaMasVendida = self::BuscarMenorOMayor('mas', $comidas, 'cantidadVendida');

    $bebidas = Bebida::where('cantidadVendida', '=', $bebidaMasVendida->cantidadVendida)->get();
    $tragos = Trago::where('cantidadVendida', '=', $tragoMasVendida->cantidadVendida)->get();
    $postres = Postre::where('cantidadVendida', '=', $postreMasVendida->cantidadVendida)->get();
    $comidas = Comida::where('cantidadVendida', '=', $comidaMasVendida->cantidadVendida)->get();

    $retorno = [];
    $retorno['bebidas'] = $bebidas;
    $retorno['tragos'] = $tragos;
    $retorno['comidas'] = $comidas;
    $retorno['postres'] = $postres;
    return $response->withJson(json_encode($retorno), 200);
    // echo '<h3> Bebidas</h3>';
    // self::MostrarNombreYCantidad($bebidas, 'nombre', 'cantidadVendida');
    // echo '<h3> Tragos</h3>';
    // self::MostrarNombreYCantidad($tragos, 'nombre', 'cantidadVendida');
    // echo '<h3> Comidas</h3>';
    // self::MostrarNombreYCantidad($comidas, 'nombre', 'cantidadVendida');
    // echo '<h3> Postres</h3>';
    // self::MostrarNombreYCantidad($postres, 'nombre', 'cantidadVendida');
  }
  public static function MostrarNombreYCantidad($lista, $campoNombre, $campoCantidad)
  {
    if (count($lista) >= 1) {
      foreach ($lista as $key => $objeto) {
        echo $objeto->$campoNombre . ' con: ' . $objeto->$campoCantidad . '<br>';
      }
    }
  }
  public static function PedidosMenosVendidos($request, $response, $args)
  {
    $pedidos = PedidoMozo::get();
    $bebidas = Bebida::get();
    $tragos = Trago::get();
    $postres = Postre::get();
    $comidas = Comida::get();

    $bebidaMenosVendida = self::BuscarMenorOMayor('menos', $bebidas, 'cantidadVendida');
    $tragoMenosVendida = self::BuscarMenorOMayor('menos', $tragos, 'cantidadVendida');
    $postreMenosVendida = self::BuscarMenorOMayor('menos', $postres, 'cantidadVendida');
    $comidaMenosVendida = self::BuscarMenorOMayor('menos', $comidas, 'cantidadVendida');

    $bebidas = Bebida::where('cantidadVendida', '=', $bebidaMenosVendida->cantidadVendida)->get();
    $tragos = Trago::where('cantidadVendida', '=', $tragoMenosVendida->cantidadVendida)->get();
    $postres = Postre::where('cantidadVendida', '=', $postreMenosVendida->cantidadVendida)->get();
    $comidas = Comida::where('cantidadVendida', '=', $comidaMenosVendida->cantidadVendida)->get();

    $retorno = [];
    $retorno['bebidas'] = $bebidas;
    $retorno['tragos'] = $tragos;
    $retorno['comidas'] = $comidas;
    $retorno['postres'] = $postres;
    return $response->withJson(json_encode($retorno), 200);
  }

  public static function LimpiarTodo($request, $response, $args)
  {
    try {

      PedidoMozo::BorrarTodos();
      PedidoComida::BorrarTodos();
      PedidoTrago::BorrarTodos();
      PedidoBebida::BorrarTodos();
      PedidoPostre::BorrarTodos();
      Cliente::BorrarTodos();
      Encuesta::BorrarTodos();
      Mesa::LimpiarMesas();
      Bebida::LimpiarVendidos();
      Comida::LimpiarVendidos();
      Postre::LimpiarVendidos();
      Trago::LimpiarVendidos();
      RegistroOperacion::BorrarTodos();
      RegistroLogeo::BorrarTodos();
      echo 'TODO LIMPIO';
    } catch (Exception $e) {
      echo $e->message;
    }
  }
  public static function PedidosCancelados($request, $response, $args)
  {
    $pedidosCancelados = PedidoMozo::where('estado', 'cancelado')->get();
    if (!is_null($pedidosCancelados) && count($pedidosCancelados) > 0) {
      foreach ($pedidosCancelados as $key => $pedidoCancelado) {
        echo 'Orden ' . $pedidoCancelado->orden . ' cancelada<br>';
        echo PedidoComida::MostrarPedido($pedidoCancelado->orden);
        echo  PedidoBebida::MostrarPedido($pedidoCancelado->orden);
        echo  PedidoPostre::MostrarPedido($pedidoCancelado->orden);
        echo  PedidoTrago::MostrarPedido($pedidoCancelado->orden);
        echo '-------------------------------------------------<br>';
      }
    } else {
      echo 'no hay pedidos cancelados';
    }
  }
}
