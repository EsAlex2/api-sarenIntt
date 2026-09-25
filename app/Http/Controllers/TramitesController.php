<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProcedure;
use App\Models\Portal;
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

    public function createProcedure(Request $request){


        //Verificar ultimo tramite registrado para el vehiculo (debe ser mayor para 2009 para el el registro de traspaso)
        $lastProcedure = Portal::GetLastProcedureByVehicle($request->input('vehiculo.serial'));
        return $this->successResponse($lastProcedure, 200, 'Último trámite registrado para el vehículo.');
    }
}
