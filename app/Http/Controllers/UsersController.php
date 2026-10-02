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
            'fpktipodocumento' => 'required|integer|max:1',
            'correo' => 'required|string|email|max:50',
            'nombre_empresa' => 'nullable|string|max:50',
            'digito_rif' => 'nullable|string|max:15',
            'telef_celular' => 'required|string|max:12'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $cedula = str_replace(' ', '', $request->numero_documento);
            $user = User::getUser($cedula);

            // 1er caso: el usuario ya existe
            if ($user) {
                $emailBd = strtoupper(trim($user->correo));
                $emailRequest = strtoupper(trim($request->correo));

                if ($emailBd !== $emailRequest) {
                    return response()->json([
                        'estatus' => 409,
                        'message' => 'Hemos detectado que ya se encuentra registrado, pero con un correo diferente. ¿Desea actualizar su correo electrónico actual (' . $emailBd . ') por el nuevo (' . $emailRequest . ')?',
                        'data' => $user
                    ], 409);
                }

                // Si los correos son IGUALES
                return response()->json([
                    'estatus' => 200,
                    'message' => 'Su usuario se encuentra registrado y validado ante el INTT.',
                    'data' => $user
                ], 200);
            }

            // 2do caso: el usuario no existe (Creación de nuevo usuario)
            $emailExists = User::where('correo', $request->correo)->exists();
            if ($emailExists) {
                return response()->json([
                    'estatus' => 400,
                    'message' => 'No puede registrarse con el correo de otro usuario.'
                ], 400);
            }

            $newUser = User::create([
                'numero_documento' => $cedula,
                'primer_nombre' => strtoupper(trim($request->primer_nombre)),
                'segundo_nombre' => strtoupper(trim($request->segundo_nombre)),
                'primer_apellido' => strtoupper(trim($request->primer_apellido)),
                'segundo_apellido' => strtoupper(trim($request->segundo_apellido)),
                'fpktipodocumento' => (int) $request->fpktipodocumento,
                'correo' => strtoupper(trim($request->correo)),
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

    public function get(int $numDocumento)
    {
        if (!is_numeric($numDocumento)) {
            return response()->json([
                'error' => 'Error de validación',
                'message' => 'El numero de documento debe ser un número válido.'
            ], 400);
        }

        try {
            $cedula = str_replace(' ', '', $numDocumento);
            //$personaByCedula = User::where('numero_documento', $cedula)->first();
            $personaByCedula = User::getUser($cedula);

            if (!$personaByCedula) {
                return response()->json([
                    'estatus' => 404,
                    'message' => 'Usuario no encontrado.'
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'data' => $personaByCedula
            ], 200);

        } catch(Exception $e) {
            return response()->json([
                'estatus' => 500,
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
                'message' => 'El número de documento debe ser un número válido.'
            ], 400);
        }

        try {
            $cedulaLimpia = str_replace(' ', '', (string)$cedula);
            $usuario = User::getUser($cedulaLimpia);

            if (!$usuario) {
                return response()->json([
                    'error' => 'Usuario no encontrado en el sistema del INTT.'
                ], 404);
            }

            $correo_viejo = strtoupper(trim($usuario->correo));
            $correo_nuevo = strtoupper(trim($request->correo));

            if ($correo_viejo !== $correo_nuevo) {
                $emailTaken = User::where('correo', $correo_nuevo)
                    ->where('numero_documento', '!=', $cedulaLimpia)
                    ->exists();

                if ($emailTaken) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'El correo electrónico ya está registrado por otro usuario.'
                    ], 400);
                }

                User::updateEmail($cedulaLimpia, $correo_nuevo);

                $usuarioActualizado = User::getUser($cedulaLimpia);

                return response()->json([
                    'status' => 200,
                    'message' => 'Se ha actualizado su correo electrónico exitosamente.',
                    'data' => $usuarioActualizado
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
                'message' => 'Ocurrió un error al procesar la actualización del correo.'
            ], 500);
        }
    }
}
