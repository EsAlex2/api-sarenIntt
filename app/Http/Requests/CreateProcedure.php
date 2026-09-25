<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateProcedure extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'solicitante' => 'required|array',
            // 'solicitante.identificacion' => 'required|string|max:1',
            // 'solicitante.cedula' => 'required|integer|max:10',
            // 'solicitante.digito_rif' => 'required_unless:solicitante.identificacion,V,E|integer|max:1',
            // 'solicitante.nombres' => 'required|string|max:255',
            // 'solicitante.apellidos' => 'required|string|max:255',
            // 'solicitante.email' => 'required|email|max:255',
            // 'vendedor' => 'required|array',
            // 'vendedor.identificacion' => 'required|string|max:1',
            // 'vendedor.cedula' => 'required|integer|max:10',
            // 'vendedor.digito_rif' => 'required_unless:vendedor.identificacion,V,E|integer|max:1',
            // 'vendedor.nombres' => 'required|string|max:255',
            // 'vendedor.apellidos' => 'required|string|max:255',
            // 'comprador' => 'required|array',
            // 'comprador.identificacion' => 'required|string|max:1',
            // 'comprador.cedula' => 'required|integer|max:10',
            // 'comprador.digito_rif' => 'required_unless:comprador.identificacion,V,E|integer|max:1',
            // 'comprador.nombres' => 'required|string|max:255',
            // 'comprador.apellidos' => 'required|string|max:255',
            // 'comprador.email' => 'required|email|max:255',
            // 'vehiculo' => 'required|array',
            // 'vehiculo.placa' => 'required|string|max:8',
            'vehiculo.serial' => 'required|integer|max:17',
            // 'vehiculo.marca' => 'required|string|max:255',
            // 'vehiculo.modelo' => 'required|string|max:255',
            // 'vehiculo.anio' => 'required|integer|max:4',
            // 'vehiculo.color' => 'required|string|max:255',
            // 'vehiculo.tipo' => 'required|string|max:255',
        ];
    }
}
