<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Tymon\JWTAuth\Facades\JWTAuth;

class InvalidateSystemToken extends Command
{
    protected $signature = 'jwt:invalidate {token}';

    protected $description = 'Invalida un token JWT específico';

    public function handle()
    {
        $token = $this->argument('token');

        try {
            // Esto extrae el token y lo invalida
            JWTAuth::setToken($token)->invalidate();

            $this->info("¡El token ha sido invalidado exitosamente!");
            $this->info("Cualquier sistema que intente usarlo recibirá un error de autenticación.");

        } catch (\Exception $e) {
            $this->error("No se pudo invalidar el token: " . $e->getMessage());
            $this->error("Nota: Si el token ya expiró o su firma es incorrecta, no es necesario invalidarlo.");
        }
    }
}
