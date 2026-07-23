<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;    
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected JwtService $jwtService;

    /**
     * Inyectar el JwtService personalizado.
     */
    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Verifica un usuario por número de documento.
     *
     * GET /users/verify/{documento}
     */
    public function verify(Request $request, $documento)
    {
        // 1. Limpiar y procesar la entrada del documento
        $cleanDoc = str_replace(['-', ' '], '', $documento);
        $tipo_documento = strtoupper(substr($cleanDoc, 0, 1));
        $numero_documento = substr($cleanDoc, 1);

        $validTypes = ['V', 'E', 'P', 'J', 'G'];

        // Si el formato no es estándar, verificar si lo enviaron a través de parámetros query
        if (!in_array($tipo_documento, $validTypes) || !ctype_digit($numero_documento)) {
            $tipo_documento = strtoupper($request->query('tipo_documento', ''));
            $numero_documento = $request->query('numero_documento', '');

            if (!in_array($tipo_documento, $validTypes) || !ctype_digit($numero_documento)) {
                return response()->json([
                    'error' => 'Formato de documento inválido. Debe comenzar con una letra válida (V, E, P, J, G) seguida del número (ej. V12345678), o enviarse como parámetros query tipo_documento y numero_documento.'
                ], 400);
            }
        }

        // 2. Identificar el tipo de persona (Natural vs. Empresa)
        // V: Venezolano, E: Extranjero, P: Pasaporte -> Persona Natural
        // J: Jurídico, G: Gubernamental -> Empresa
        if (in_array($tipo_documento, ['V', 'E', 'P'])) {
            $personaTipo = 'natural';
        } else {
            $personaTipo = 'empresa'; // J o G
        }

        // 3. Verificar si el usuario existe en la base de datos
        $user = User::where('tipo_documento', $tipo_documento)
                    ->where('numero_documento', $numero_documento)
                    ->first();

        // 4. Validar existencia y correo electrónico
        if ($user) {
            // Verificar si el parámetro correo fue provisto para validación
            if ($request->has('correo')) {
                $correoPeticion = strtolower(trim($request->query('correo')));
                $correoUsuario = strtolower(trim($user->correo));

                if ($correoPeticion === $correoUsuario) {
                    // Generar token JWT
                    $token = $this->jwtService->generateToken([
                        'sub' => $user->id,
                        'email' => $user->correo,
                    ]);

                    return response()->json([
                        'existe' => true,
                        'persona_tipo' => $personaTipo,
                        'mensaje' => 'Validación exitosa. Acceso concedido.',
                        'user' => $user,
                        'token' => $token
                    ], 200);
                } else {
                    return response()->json([
                        'existe' => true,
                        'persona_tipo' => $personaTipo,
                        'mensaje' => 'El correo electrónico no coincide con el registrado.'
                    ], 400);
                }
            }

            // Si no se pasó el parámetro de correo, enmascarar el correo y solicitar validación
            $emailParts = explode('@', $user->correo);
            $local = $emailParts[0];
            $domain = $emailParts[1] ?? '';
            $maskedLocal = substr($local, 0, 2) . str_repeat('*', max(0, strlen($local) - 2));
            $maskedEmail = $maskedLocal . '@' . $domain;

            return response()->json([
                'existe' => true,
                'persona_tipo' => $personaTipo,
                'correo_registrado_parcial' => $maskedEmail,
                'mensaje' => 'Usuario encontrado. Por favor, valide su correo electrónico ?correo=tu_correo@dominio.com para autenticarse.'
            ], 200);
        }

        // 5. Si el usuario no existe, notificar que debe registrarse (POST)
        return response()->json([
            'existe' => false,
            'persona_tipo' => $personaTipo,
            'mensaje' => 'El usuario no existe. Debe registrarse (POST).'
        ], 200);
    }

    /**
     * Almacenar un usuario recién creado en la base de datos.
     *
     * POST /users
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'primer_nombre' => 'required|string|max:255',
            'segundo_nombre' => 'nullable|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido' => 'nullable|string|max:255',
            'tipo_documento' => 'required|string|in:V,E,P,J,G',
            'numero_documento' => 'required|string|max:255|unique:users,numero_documento',
            'correo' => 'required|email|max:255|unique:users,correo',
            'fecha_nacimiento' => 'required|date',
            'nombre_empresa' => 'required|string|max:255',
            'digito_rif' => 'required|string|max:255|unique:users,digito_rif',
            'telef_celular' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Error de validación de datos.',
                'detalles' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        // Guardar el usuario en la base de datos
        $user = User::create($validatedData);

        // Generar token JWT para el usuario recién registrado
        $token = $this->jwtService->generateToken([
            'sub' => $user->id,
            'email' => $user->correo,
        ]);

        return response()->json([
            'mensaje' => 'Usuario registrado exitosamente.',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Obtener el perfil del usuario autenticado.
     *
     * GET /users/me
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ], 200);
    }
}
