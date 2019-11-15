<?php

namespace Middleware;
use Helpers\JWTAuth;
class AuthMiddleware extends TokenValidatorMiddleware
{
  public static function IsLoggedIn($request, $response, $next)
  {
    var_dump($request."Middleware");
    if(parent::IsValidToken($request))
      return $next($request, $response);
    else
      return $response->withJson("not logged in", 401);
  }
}
