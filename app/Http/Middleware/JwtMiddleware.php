<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\JwtService;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    protected JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Maneja una solicitud entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return response()->json([
                'error' => 'Token no proporcionado o formato inválido. Use Bearer <token>'
            ], 401);
        }

        $token = substr($authorizationHeader, 7);
        $payload = $this->jwtService->decodeToken($token);

        if (!$payload || !isset($payload['sub'])) {
            return response()->json([
                'error' => 'Token inválido o expirado.'
            ], 401);
        }

        $user = User::find($payload['sub']);

        if (!$user) {
            return response()->json([
                'error' => 'Usuario asociado al token no encontrado.'
            ], 401);
        }

        // Vincular el usuario a la solicitud para que $request->user() resuelva a este usuario.
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
