<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'administracion.personas';
    protected $primaryKey = 'spkpersonas';
    public $timestamps = false;

    public static function findByCedula(string $cedula)
    {
        return self::where('numero_documento', $cedula)->first();
    }
}
