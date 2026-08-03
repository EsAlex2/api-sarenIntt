<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Tramites extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'intranet.citas_asignadas_vh';
    protected $primaryKey = 'spkcitavh';
    public $timestamps = false;

    protected $fillable = [
        'placa',
        'serial',
        'fecha_cita',
        'ano',
        'fpktipotra'
    ];

    /**
     * Consulta trámites pagados por número de cédula y tipo de trámite.
     *
     * @param string $cedula
     * @param string $tipoTramite
     * //@param string $estatus
     */

    //string $estatus = '2'
    public static function tramiteByCedula(string $cedula, string $tipoTramite = '46', string $estatus = '2')
    {
        return self::select(
            'p.numero_documento as cedula_identidad',
            'p.primer_nombre as nombre',
            'p.primer_apellido as apellido',
            'intranet.citas_asignadas_vh.fecha_registro as fecha_registro',
            'intranet.citas_asignadas_vh.ano as año_vehiculo',
            'sp.descripcion as estatus',
            'intranet.citas_asignadas_vh.placa as placa_vh',
            DB::raw("intranet.citas_asignadas_vh.numero || intranet.citas_asignadas_vh.spkcitavh as num_tramite"),
            'tt.tipo_tramite as nombre_tramite'
        )
            ->join('administracion.personas as p', 'p.spkpersonas', '=', 'intranet.citas_asignadas_vh.fpkpersonas')
            ->join('intranet.cita_tra_vh as ctv', 'ctv.fpkcitavh', '=', 'intranet.citas_asignadas_vh.spkcitavh')
            ->join('administracion.tipo_tramite as tt', 'tt.spktipotramite', '=', 'ctv.fpktipotra')
            ->join('administracion.status_pago as sp', 'sp.spkstatus_pago', '=', 'intranet.citas_asignadas_vh.fpkestatus')
            ->where('p.numero_documento', $cedula)
            ->where('sp.spkstatus_pago', $estatus)
            ->where('tt.spktipotramite', $tipoTramite)
            ->get();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
