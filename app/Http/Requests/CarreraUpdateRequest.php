<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CarreraUpdateRequest extends FormRequest
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
            "nombre" => "required|string|unique:carreras,nombre," . $this->carrera->id,
            "descripcion" => "nullable|string",
        ];
    }

    public function messages(): array
    {
        return [
            "nombre.required" => "Debes completar este campo",
            "nombre.string" => "Debes ingresar un texto valido",
            "nombre.unique" => "Este nombre ya fue registrado",
            "descripcion.string" => "Debes ingresar un texto valido"
        ];
    }
}
