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

            if (empty($tramites)) {
                return $this->errorValidations('No se encontraron trámites pagados registrados para la cédula ingresada.', 404);
            }

            return $this->successResponse($tramites, 200, 'Trámites encontrados.');

        } catch (QueryException $e) {
            return $this->error('Error en la base de datos al realizar la consulta' . $e, 500);
        }
    }
}
