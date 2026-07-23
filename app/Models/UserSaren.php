<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSaren extends Model
{
    /**
     * Tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'user_sarens';

    /**
     * Atributos asignables de forma masiva (Mass Assignable).
     *
     * @var array
     */
    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'tipo_documento',
        'numero_documento',
        'correo_saren',
        'fecha_nacimiento',
        'nombre_empresa',
        'digito_rif',
        'telef_celular'
    ];

    /**
     * Conversión de tipos de atributos (Casting).
     *
     * @var array
     */
    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];
}
