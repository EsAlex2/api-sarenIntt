<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSaren;
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
        if (in_array($tipo_documento, ['V', 'E', 'P'])) {
            $personaTipo = 'natural';
        } else {
            $personaTipo = 'empresa'; // J o G
        }

        // 3. Buscar en la tabla principal (user_sarens - SAREN)
        $sarenUser = UserSaren::where('tipo_documento', $tipo_documento)
                              ->where('numero_documento', $numero_documento)
                              ->first();

        // Si el usuario no existe en la principal (SAREN)
        if (!$sarenUser) {
            return response()->json([
                'existe_en_saren' => false,
                'persona_tipo' => $personaTipo,
                'mensaje' => 'El usuario no está registrado en el sistema principal (SAREN).'
            ], 404);
        }

        // 4. Buscar en la tabla secundaria (users - INTT)
        $inttUser = User::where('tipo_documento', $tipo_documento)
                        ->where('numero_documento', $numero_documento)
                        ->first();

        // Si el usuario no existe en la secundaria (INTT) -> Registrar automáticamente con los datos de SAREN
        if (!$inttUser) {
            $inttUser = User::create([
                'primer_nombre' => $sarenUser->primer_nombre,
                'segundo_nombre' => $sarenUser->segundo_nombre,
                'primer_apellido' => $sarenUser->primer_apellido,
                'segundo_apellido' => $sarenUser->segundo_apellido,
                'tipo_documento' => $sarenUser->tipo_documento,
                'numero_documento' => $sarenUser->numero_documento,
                'correo' => $sarenUser->correo_saren, // Mapear correo_saren de la principal al correo de la secundaria
                'fecha_nacimiento' => $sarenUser->fecha_nacimiento,
                'nombre_empresa' => $sarenUser->nombre_empresa,
                'digito_rif' => $sarenUser->digito_rif,
                'telef_celular' => $sarenUser->telef_celular,
            ]);

            // Generar token JWT para el nuevo registro
            $token = $this->jwtService->generateToken([
                'sub' => $inttUser->id,
                'email' => $inttUser->correo,
            ]);

            return response()->json([
                'existe_en_saren' => true,
                'existe_en_intt' => true,
                'mensaje' => 'El usuario ha sido registrado automáticamente al INTT con los datos de SAREN.',
                'user' => $inttUser,
                'token' => $token
            ], 201);
        }

        // 5. Si existe en ambas tablas, comparar los correos electrónicos
        $correoSaren = strtolower(trim($sarenUser->correo_saren));
        $correoIntt = strtolower(trim($inttUser->correo));

        if ($correoSaren === $correoIntt) {
            // Generar token JWT
            $token = $this->jwtService->generateToken([
                'sub' => $inttUser->id,
                'email' => $inttUser->correo,
            ]);

            return response()->json([
                'existe_en_saren' => true,
                'existe_en_intt' => true,
                'mensaje' => 'Validación exitosa. Acceso concedido.',
                'user' => $inttUser,
                'token' => $token
            ], 200);
        }

        // Si los correos no coinciden, retornar un error y mostrar los correos enmascarados
        $maskedSaren = $this->maskEmail($sarenUser->correo_saren);
        $maskedIntt = $this->maskEmail($inttUser->correo);

        return response()->json([
            'existe_en_saren' => true,
            'existe_en_intt' => true,
            'correos_diferentes' => true,
            'correo_saren_enmascarado' => $maskedSaren,
            'correo_intt_enmascarado' => $maskedIntt,
            'mensaje' => 'El correo electrónico registrado en INTT no coincide con el de SAREN. Por favor, actualice su correo electrónico.'
        ], 200);
    }

    /**
     * Actualiza el correo electrónico del usuario únicamente en la tabla secundaria (users / INTT).
     *
     * POST /users/update-email
     */
    public function updateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'required|string|in:V,E,P,J,G',
            'numero_documento' => 'required|string',
            'correo' => 'required|email|unique:users,correo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Error de validación de datos.',
                'detalles' => $validator->errors()
            ], 422);
        }

        $tipo_documento = strtoupper($request->input('tipo_documento'));
        $numero_documento = $request->input('numero_documento');
        $nuevo_correo = strtolower(trim($request->input('correo')));

        // Buscar el usuario en la tabla secundaria (users - INTT)
        $inttUser = User::where('tipo_documento', $tipo_documento)
                        ->where('numero_documento', $numero_documento)
                        ->first();

        if (!$inttUser) {
            return response()->json([
                'error' => 'Usuario no encontrado en el sistema secundario (INTT).'
            ], 404);
        }

        // Actualizar ÚNICAMENTE el correo electrónico
        $inttUser->correo = $nuevo_correo;
        $inttUser->save();

        // Generar un nuevo token JWT para el usuario con su correo recién actualizado
        $token = $this->jwtService->generateToken([
            'sub' => $inttUser->id,
            'email' => $inttUser->correo,
        ]);

        return response()->json([
            'mensaje' => 'Correo electrónico actualizado exitosamente en el sistema secundario (INTT).',
            'user' => $inttUser,
            'token' => $token
        ], 200);
    }

    /**
     * Almacenar un usuario recién creado en la base de datos de INTT.
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

        $user = User::create($validatedData);

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

    /**
     * Enmascara un correo electrónico para seguridad (ej: ju********@domain.com).
     */
    private function maskEmail($email)
    {
        $emailParts = explode('@', $email);
        $local = $emailParts[0];
        $domain = $emailParts[1] ?? '';
        $maskedLocal = substr($local, 0, 2) . str_repeat('*', max(0, strlen($local) - 2));
        return $maskedLocal . '@' . $domain;
    }
}
