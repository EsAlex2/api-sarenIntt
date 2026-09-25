<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProcedure;
use App\Models\Persona;
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
        if (empty($lastProcedure)) {
            return $this->errorValidations('No se encontraron trámites registrados para el vehículo ingresado con una fecha posterior a 2009. Se recomienda realizar un trámite duplicado.', 404);
        }

        //Busqueda de persona en la put
        $persona = Persona::findByCedula($request->input('comprador.cedula'));
        if (empty($persona)) {
            return $this->errorValidations('No se encontró la persona en la base de datos. Debe crear su usuario antes de realizar el trámite de traspaso.', 404);
        }

        $data = [
            'placa' => $request->input('vehiculo.placa'),
            'serial' => $request->input('vehiculo.serial'),
            'ano' => $request->input('vehiculo.anio'),
            'ip_registro' => $request->input('solicitante.ip'),
            'fpkpersonas' => $persona->spkpersonas,
            'monto' => 1000, // Monto fijo para el trámite de traspaso
        ];

        //En caso de encontrarlo crear el tramite de traspaso
        $tramite = Tramites::createProcedure($data);
        return $this->successResponse($tramite, 200, 'Trámite de traspaso creado exitosamente.');
    }
}
