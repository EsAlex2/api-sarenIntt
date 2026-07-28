<?php

namespace App\Http\Controllers;

use App\Models\Tramites;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class TramitesController extends Controller
{
    public function traspasoByCedula(Request $request, string $cedula)
    {

        try {
            $tramites = Tramites::tramiteByCedula($cedula);

            if ($tramites->isEmpty()) {
                return response()->json([
                    'estatus' => 404,
                    'mensaje' => 'No se encontraron trámites pagados registrados para la cédula ingresada.'
                ], 404);
            }

            return response()->json([
                'estatus' => 200,
                'data' => $tramites
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Error de base de datos al realizar la consulta.',
                'detalles' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error inesperado.',
                'detalles' => $e->getMessage()
            ], 500);
        }
    }
}
