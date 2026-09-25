<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $table = 'administracion.personas';
    protected $primaryKey = 'spkpersonas';
    public $timestamps = false;
    protected $fillable = [
        'numero_documento',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'fpktipodocumento',
        'correo',
        'nombre_empresa',
        'digito_rif',
        'telef_celular'
    ];


    public static function getUser(string $cedula)
    {
        return DB::table('administracion.personas as p')
            ->select([
                'p.numero_documento as cedula_identidad',
                'p.primer_nombre',
                'p.segundo_nombre',
                'p.primer_apellido',
                'p.segundo_apellido',
                'p.fecha_registro',
                'p.correo',
            ])
            ->where('p.numero_documento', $cedula)
            ->first();
    }

    public static function updateEmail(string $cedula, string $correo)
    {
        return DB::table('administracion.personas')
            ->where('numero_documento', $cedula)
            ->update([
                'correo' => $correo
            ]);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function userBycedula(string $cedula)
    {
        return static::where('numero_documento', $cedula)->first();
    }
}
