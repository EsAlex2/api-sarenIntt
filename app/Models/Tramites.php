<?php

namespace App\Models;

use Carbon\Carbon;
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
    public static function tramiteByCedula(string $cedula, string $tipoTramite = '23', string $estatus = '2')
{
    $primeraConsulta = DB::table('intranet.citas_asignadas_vh as cav')
        ->select([
            DB::raw("'Vehiculo' as control"),
            'p.numero_documento as cedula_identidad',
            'p.primer_nombre as nombre',
            'p.primer_apellido as apellido',
            'cav.fecha_cita as fecha_cita_vh',
            'cav.spkcitavh as cita_vh',
            'tt.tipo_tramite',
            'ctv.monto',
            'sp.descripcion as estatus',
            DB::raw("REPLACE(cav.numero || '' || cav.spkcitavh, '.', '') as n_planilla")
        ])
        ->join('administracion.personas as p', 'p.spkpersonas', '=', 'cav.fpkpersonas')
        ->join('intranet.cita_tra_vh as ctv', 'cav.spkcitavh', '=', 'ctv.fpkcitavh')
        ->join('administracion.tipo_tramite as tt', function ($join) use ($tipoTramite) {
            $join->on('ctv.fpktipotra', '=', 'tt.spktipotramite')
                ->where('ctv.fpktipotra', '=', $tipoTramite);
        })
        ->join('administracion.status_pago as sp', 'sp.spkstatus_pago', '=', 'ctv.status')
        ->where('p.numero_documento', $cedula)
        ->where('cav.fpkoficina', 128)
        ->where('cav.fpkperfil', 56);

    $segundaConsulta = DB::table('intranet.citas_asignadas_vh_ver as cavv')
        ->select([
            DB::raw("'Vehiculo' as control"),
            'p2.numero_documento as cedula_identidad',
            'p2.primer_nombre as nombre',
            'p2.primer_apellido as apellido',
            'cavv.fecha_cita as fecha_cita_vh',
            'cavv.spkcitavh as cita_vh',
            'tt.tipo_tramite',
            'ctvv.monto',
            'sp.descripcion as estatus',
            DB::raw("cavv.numero || '' || cavv.spkcitavh as n_planilla")
        ])
        ->join('administracion.personas as p2', 'p2.spkpersonas', '=', 'cavv.fpkpersonas')
        ->join('intranet.cita_tra_vh_ver as ctvv', 'cavv.spkcitavh', '=', 'ctvv.fpkcitavh')
        ->join('administracion.tipo_tramite as tt', function ($join) use ($tipoTramite) {
            $join->on('ctvv.fpktipotra', '=', 'tt.spktipotramite')
                ->where('ctvv.fpktipotra', '=', $tipoTramite);
        })
        ->join('administracion.status_pago as sp', 'sp.spkstatus_pago', '=', 'ctvv.status')
        ->where('p2.numero_documento', $cedula)
        ->where('cavv.fpkoficina', 128)
        ->where('cavv.fpkperfil', 56);

    return $primeraConsulta
        ->union($segundaConsulta)
        ->orderBy('control')
        ->orderBy('fecha_cita_vh', 'desc')
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

    public static function createProcedure(array $data)
    {
        try {
            DB::beginTransaction();

            $asignacion = DB::table('intranet.citas_asignadas_vh')->insertGetId([
                'fecha_cita' => now(),
                'fpkoficina' => 128,
                'placa' => $data['placa'],
                'serial' => $data['serial'],
                'fecha_registro' => now(),
                'ip_registro' => $data['ip_registro'],
                'fpkpersonas' => $data['fpkpersonas'],
                'fpkperfil' => 56,
                'ano' => $data['ano'],
                'fpkestatus' => 2,
                'numero' =>  Carbon::now()->format('ymd') . substr((string) microtime(true), -4),
                'fpktipooficina' => 1,
                'causa' => 1,
            ], 'spkcitavh');

            $tramite = DB::table('intranet.cita_tra_vh')->insert([
                'fpkcitavh' => $asignacion,
                'fpktipotra' => 23,
                'monto' => $data['monto'],
                'status' => 1
            ]);

            DB::commit();
            return $tramite;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Error al crear el trámite: " . $e->getMessage());
        }

    }
}
