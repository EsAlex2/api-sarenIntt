<?php

namespace App\Services;

class JwtService
{
    /**
     * La clave secreta utilizada para firmar los tokens.
     *
     * @var string
     */
    private string $secret;

    public function __construct()
    {
        // Recuperar la clave secreta del entorno, por defecto usa una clave de respaldo si no está configurada.
        $this->secret = env('JWT_SECRET', 'saren_intt_default_secret_key_123456_secure_key');
    }

    /**
     * Genera un nuevo JSON Web Token (JWT).
     *
     * @param array $payload Payload personalizado para incluir en el token.
     * @param int $expiry Tiempo de expiración en segundos (por defecto 1 hora).
     * @return string El JWT generado.
     */
    public function generateToken(array $payload, int $expiry = 3600): string
    {
        $header = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]);

        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry;
        $payloadJson = json_encode($payload);

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payloadJson);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Decodifica y valida un JSON Web Token (JWT).
     *
     * @param string $token El token JWT.
     * @return array|null El payload decodificado si es válido, o null si no lo es.
     */
    public function decodeToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($headerB64, $payloadB64, $signatureB64) = $parts;

        // Verificar la firma
        $signature = $this->base64UrlDecode($signatureB64);
        $expectedSignature = hash_hmac('sha256', $headerB64 . "." . $payloadB64, $this->secret, true);

        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($payloadB64), true);

        if (!$payload) {
            return null;
        }

        // Verificar la expiración del token
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null; // El token ha expirado
        }

        return $payload;
    }

    /**
     * Función de ayuda para codificar datos al formato Base64Url.
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Función de ayuda para decodificar datos desde el formato Base64Url.
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
