<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
     * @param string $estatus
     */
    public static function tramiteByCedula(string $cedula, string $tipoTramite = '46', string $estatus = '2')
    {
        return self::select(
                'p.numero_documento as cedula_identidad',
                'p.primer_nombre as nombre',
                'p.primer_apellido as apellido',
                'intranet.citas_asignadas_vh.fecha_cita as fecha_cita',
                'intranet.citas_asignadas_vh.ano as año_vehiculo',
                'sp.descripcion as estatus',
                'intranet.citas_asignadas_vh.placa as placa_vh',
                'intranet.citas_asignadas_vh.spkcitavh as cita_asig',
                'tt.tipo_tramite as nombre_tramite'
            )
            ->join('administracion.personas as p', 'p.spkpersonas', '=', 'intranet.citas_asignadas_vh.fpkpersonas')
            ->join('intranet.cita_tra_vh as ctv', 'ctv.fpkcitavh', '=', 'intranet.citas_asignadas_vh.spkcitavh')
            ->join('administracion.tipo_tramite as tt', 'tt.spktipotramite', '=', 'ctv.fpktipotra')
            ->join('administracion.status_pago as sp', 'sp.spkstatus_pago', '=', 'intranet.citas_asignadas_vh.fpkestatus')
            ->where('p.numero_documento', $cedula)
            ->where('tt.spktipotramite', $tipoTramite)
            ->where('intranet.citas_asignadas_vh.fpkestatus', $estatus)
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
