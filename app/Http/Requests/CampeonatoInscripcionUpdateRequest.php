<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CampeonatoInscripcionUpdateRequest extends FormRequest
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
            "campeonato_id" => "required",
            "carrera_id" => "required",
            "fecha" => "required|date",
            "hora" => "required",
        ];
    }

    public function messages(): array
    {
        return [
            "campeonato_id.required" => "Debes completar este campo",
            "carrera_id.required" => "Debes completar este campo",
            "fecha.required" => "Debes completar este campo",
            "hora.required" => "Debes completar este campo",
        ];
    }
}
