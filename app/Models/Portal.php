<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Portal extends Model
{
    protected $connection = 'sybase';

public static function GetLastProcedureByVehicle(string $serial)
{
    // Escapar comillas simples para evitar inyección básica
    $serialSafe = str_replace("'", "''", $serial);

    $sql = "SELECT TOP 1 * FROM dbo.TRAMITE_VEHICULO
            INNER JOIN dbo.TRAMITE
            ON dbo.TRAMITE_VEHICULO.NRO_TRAMITE = dbo.TRAMITE.NRO_TRAMITE
            WHERE dbo.TRAMITE_VEHICULO.SERIAL_CARROCERIA = '{$serialSafe}'
            AND dbo.TRAMITE.FECHA_PROCESADO > '01-01-2009'
            AND dbo.TRAMITE.ID_ESTATUS IN ('9', '19', '15')
            ORDER BY dbo.TRAMITE.FECHA_PROCESADO DESC";

    return DB::connection('sybase')->select($sql);
}

}
