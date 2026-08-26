<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "nombre" => "required|min:2",
            "paterno" => "required|min:2",
            "materno" => "nullable",
            "dir" => "nullable",
            "ci" => "required|unique:users,ci," . $this->user->id,
            "ci_exp" => "required",
            "correo" => "nullable|email|unique:users,correo," . $this->user->id,
            "fono" => "required|min:2",
            "acceso" => "required",
            "tipo" => "required",
            "foto" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
            "sucursal_id" => "required",
        ];
    }

    /**
     * Mensages validacion
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            "nombre.required" => "Este campo es obligatorio",
            "nombre.min" => "Debes ingresar al menos :min caracteres",
            "paterno.required" => "Este campo es obligatorio",
            "paterno.min" => "Debes ingresar al menos :min caracteres",
            "fono.required" => "Este campo es obligatorio",
            "fono.min" => "Debes ingresar al menos :min caracteres",
            "password.required" => "Este campo es obligatorio",
            "password.min" => "Debes ingresar al menos :min caracteres",
            "acceso.required" => "Este campo es obligatorio",
            "tipo.required" => "Este campo es obligatorio",
            "sucursal_id.required" => "Este campo es obligatorio",
        ];
    }
}
