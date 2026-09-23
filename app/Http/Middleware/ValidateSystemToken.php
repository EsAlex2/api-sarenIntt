<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Exception;

class ValidateSystemToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            dd($payload);

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'El token ha expirado'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'El token es inválido'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token no proporcionado en la cabecera'], 401);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error de autenticación del sistema'], 401);
        }

        return $next($request);
    }
}
