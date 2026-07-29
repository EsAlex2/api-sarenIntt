<?php

namespace App\Http\Controllers;

use App\Models\Tramites;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class TramitesController extends Controller
{
    public function traspasoByCedula(string $cedula)
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
                'estatus' => 500,
                'error' => 'Error en la base de datos al realizar la consulta'
            ], 500);
        }
    }
}
