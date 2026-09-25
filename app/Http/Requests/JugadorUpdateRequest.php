<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class JugadorUpdateRequest extends FormRequest
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
            "nombres" => "required|string",
            "apes" => "required|string",
            "ci" => "required|unique:jugadors,ci," . $this->jugador->id,
            "correo" => "nullable|email",
            "dir" => "nullable|string",
            "fono" => "nullable|string",
            "foto" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096",
        ];
    }

    public function messages()
    {
        return [
            "nombres.required" => "Debes completar este campo",
            "apes.required" => "Debes completar este campo",
            "ci.required" => "Debes completar este campo",
            "ci.unique" => "Este Nro. de C.I. ya esta registrado",
            "foto.required" => "Debes completar este campo",
            "foto.mimes" => "La imagen debe ser jpeg,jpg,png,gif,svg",
            "foto.max" => "La imagen no puede pesar mas de 4MB",
        ];
    }
}
