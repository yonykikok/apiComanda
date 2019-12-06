<?php

namespace Controllers;

use Models\RegistroLogeo;
use Helpers\JWTAuth;
use Middleware\TokenValidatorMiddleware;
use Models\User;

class AuthController
{
  public static function getInfoByToken($request, $response, $args)
  {
    $info = TokenValidatorMiddleware::GetTokenData($request);
    if (!$info) {
      return $response->withJson("Token invalido");
    } else {
      return $response->withJson($info);
    }
  }
  public static function LogIn($request, $response, $args)
  {
    $data = json_decode($request->getBody());
 
    if (!isset($data->nombre) || !isset($data->clave))
      return $response->withJson("ingrese nombre/clave", 400);

    $user = User::FindByUsername($data->nombre);

    if (!is_null($user)) {
      if (!password_verify($data->clave, $user->clave)) {
        return $response->withJson("invalid nombre/clave");
      }
    } else {
      return $response->withJson("invalid nombre/clave");
    }

    $obj = [
      "id" => $user->id,
      "nombre" => $user->nombre,
      "role" => $user->role
    ];

    $registro = new RegistroLogeo();
    $registro->fecha = date("d-m-Y");
    $registro->hora = date("h-i-sa");
    $registro->idUsuario = $user->id;
    $registro->save();

    $infoDelLogin = JWTAuth::CreateToken($obj);
    $infoDelLogin .= ";" . $user->role; //le paso el role al final del token
    return $response->withJson(json_encode($infoDelLogin), 200);
  }

  public static function Register($request, $response, $args)
  {
    $data = json_decode($request->getBody());

    if (!isset($data->nombre) || !isset($data->clave))
      return $response->withJson("ingrese nombre/clave", 400);

    $currentUser = User::where('nombre', $data->nombre)->first();
    if (!$currentUser) {
      $user = new User;
      $user->nombre = $data->nombre;
      $user->clave = password_hash($data->clave, PASSWORD_DEFAULT);
      $user->role = $data->role;
      $user->imagen = $data->imagen;

      $user->save();
      return self::LogIn($request, $response, $args);
    }
    return $response->withJson("usuario existente", 200);
  }

  public static function ChangePassword()
  { }
}
