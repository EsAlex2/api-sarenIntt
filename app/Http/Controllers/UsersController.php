<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class UsersController extends Controller
{
    public function create(Request $request)
    {
        // validaciones
        $validator = Validator::make($request->all(), [
            'numero_documento' => 'required|string',
            'primer_nombre' => 'required|string|max:20',
            'segundo_nombre' => 'nullable|string|max:20',
            'primer_apellido' => 'required|string|max:20',
            'segundo_apellido' => 'nullable|string|max:20',
            'fpktipodocumento' => 'required|string|max:1',
            'correo' => 'required|string|email|max:50',
            // 'fecha_nacimiento' => 'required|date_format:d/m/Y|before_or_equal:today',
            'nombre_empresa' => 'nullable|string|max:50',
            'digito_rif' => 'nullable|string|max:15',
            'telef_celular' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $cedula = str_replace(' ', '', $request->numero_documento);
            $user = User::where('numero_documento', $cedula)->first();

            // 1er caso: el usuario ya existe
            if ($user) {
                $emailBd = strtoupper(trim($user->correo));
                $emailRequest = strtoupper(trim($request->correo));

                if ($emailBd !== $emailRequest) {
                    return response()->json([
                        'status' => 409,
                        'message' => 'Hemos detectado que ya se encuentra registrado, pero con un correo diferente. ¿Desea actualizar su correo electrónico actual (' . $emailBd . ') por el nuevo (' . $emailRequest . ')?',
                        'data' => $user
                    ], 409);
                }

                // Si los correos son IGUALES
                return response()->json([
                    'status' => 200,
                    'message' => 'Su usuario se encuentra registrado y validado ante el INTT.',
                    'data' => $user
                ], 200);
            }

            // 2do caso: el usuario no existe (Creación de nuevo usuario)
            $emailExists = User::where('correo', $request->correo)->exists();
            if ($emailExists) {
                return response()->json([
                    'status' => 400,
                    'message' => 'No puede registrarse con el correo de otro usuario.'
                ], 400);
            }

            $newUser = User::create([
                'numero_documento' => $cedula,
                'primer_nombre' => strtoupper(trim($request->primer_nombre)),
                'segundo_nombre' => strtoupper(trim($request->segundo_nombre)),
                'primer_apellido' => strtoupper(trim($request->primer_apellido)),
                'segundo_apellido' => strtoupper(trim($request->segundo_apellido)),
                'fpktipodocumento' => strtoupper(trim($request->fpktipodocumento)),
                'correo' => strtoupper(trim($request->correo)),
                // 'fecha_nacimiento' => $request->fecha_nacimiento,
                'nombre_empresa' => strtoupper(trim($request->nombre_empresa)),
                'digito_rif' => strtoupper(trim($request->digito_rif)),
                'telef_celular' => str_replace(' ', '', $request->telef_celular),
            ]);

            return response()->json([
                'status' => 201,
                'message' => 'Usuario registrado exitosamente.',
                'data' => $newUser
            ], 201);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Ocurrió un error al procesar la solicitud.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUser()
    {
        try {
            $users = User::orderBy('numero_documento', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $users
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al obtener la lista de usuarios.' . $e
            ], 500);
        }
    }

    public function getUserByspkpersonas(int $spkpersonas)
    {
        if (!is_numeric($spkpersonas)) {
            return response()->json([
                'error' => 'Error de validación',
                'message' => 'El parámetro spkpersonas debe ser un número válido.'
            ], 400);
        }

        try {
            $id = str_replace(' ', '', $spkpersonas);
            $personaByspk = User::where('spkpersonas', $id)->first();

            if (!$personaByspk) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Usuario no encontrado.'
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'data' => $personaByspk
            ], 200);

        } catch(Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Ocurrió un error al obtener el usuario.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateEmail(Request $request, string $cedula)
    {
        $validator = Validator::make($request->all(), [
            'correo' => 'required|string|email|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Error de validación de datos.',
                'detalles' => $validator->errors()
            ], 422);
        }

        if (!is_numeric($cedula)) {
            return response()->json([
                'error' => 'Error de validación',
                'message' => 'El número de cédula debe ser un número válido.'
            ], 400);
        }

        try {
            $cedulaLimpia = str_replace(' ', '', (string)$cedula);

            $usuario = User::where('numero_documento', $cedulaLimpia)->first();

            if (!$usuario) {
                return response()->json([
                    'error' => 'Usuario no encontrado en el sistema secundario (INTT).'
                ], 404);
            }

            $correo_viejo = strtoupper(trim($usuario->correo));
            $correo_nuevo = strtoupper(trim($request->correo));

            if ($correo_viejo !== $correo_nuevo) {
                $emailTaken = User::where('correo', $correo_nuevo)
                    ->where('numero_documento', '!=', $usuario->numero_documento)
                    ->exists();

                if ($emailTaken) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'El correo electrónico ya está registrado por otro usuario.'
                    ], 400);
                }

                $usuario->correo = $correo_nuevo;
                $usuario->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Se ha actualizado su correo electrónico exitosamente.',
                    'data' => $usuario
                ], 200);
            }

            return response()->json([
                'status' => 200,
                'message' => 'El correo electrónico proporcionado es el mismo que ya tiene registrado.',
                'data' => $usuario
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Ocurrió un error al procesar la actualización del correo.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}
