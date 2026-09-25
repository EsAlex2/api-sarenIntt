<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portal extends Model
{
    protected $connection = 'sybase';

    public static function GetLastProcedureByVehicle(string $serial)
    {
        return self::on('sybase')
            ->table('portal_inttt.TRAMITE_VEHICULO')
            ->join('portal_inttt.TRAMITE', 'portal_inttt.TRAMITE_VEHICULO.NRO_TRAMITE', '=', 'portal_inttt.TRAMITE.NRO_TRAMITE')
            ->where('TRAMITE_VEHICULO.SERIAL_CARROCERIA', $serial)
            ->whereNotNull('TRAMITE.FECHA_PROCESADO')
            ->whereIn('TRAMITE.ID_ESTATUS', ['9', '19'])
            ->orderByDesc('TRAMITE.FECHA_PROCESADO')
            ->first();
    }

}
