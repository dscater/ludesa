<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TipoCertificadoUpdateRequest extends FormRequest
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
            "nombre" => "required|string|unique:tipo_certificados,nombre," . $this->tipo_certificado->id,
            "precio" => "required|numeric|decimal:0,2|min:0",
            "descripcion" => "nullable|string",
        ];
    }

    public function messages(): array
    {
        return [
            "nombre.required" => "Debes completar este campo",
            "nombre.string" => "Debes ingresar un texto valido",
            "nombre.unique" => "Este nombre ya fue registrado",
            "precio.requried" => "Debes completar este campo",
            "precio.numeric" => "Debes ingresar un valor númerico",
            "precio.decimal" => "Debes ingresar un valor con hasta 2 decimales",
            "precio.min" => "Debes ingresar como minimo :min",
            "descripcion.string" => "Debes ingresar un texto valido"
        ];
    }
}
