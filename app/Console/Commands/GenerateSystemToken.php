<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Facades\JWTAuth;

class GenerateSystemToken extends Command
{

    protected $signature = 'jwt:generate-system-token {email}';

    protected $description = 'Generamos un token con JWT para el SAREN con una expiracion indefinida';

    public function handle()
    {
        $email = $this->argument('email');

        $exp = now()->addYears(100)->timestamp;

        try {
            $payload = JWTFactory::customClaims([
                'sub'   => $email,
                'email' => $email,
                'exp'   => $exp,   // Fecha de expiración
                'm2m'   => true
            ])->make();

            $token = JWTAuth::encode($payload)->get();

            $this->info("Token M2M generado exitosamente para: {$email}");
            $this->info("Válido indefinifamente");
            $this->line("--------------------------------------------------");
            $this->line($token);
            $this->line("--------------------------------------------------");

        } catch (\Exception $e) {
            $this->error("Ocurrió un error al generar el token: " . $e->getMessage());
        }
    }
}
