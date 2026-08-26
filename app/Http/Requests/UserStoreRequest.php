<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
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
            "ci" => "required|unique:users,ci",
            "ci_exp" => "required",
            "correo" => "nullable|email|unique:users,correo",
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
            "ci.required" => "Este campo es obligatorio",
            "ci.unique" => "Este C.I. ya fue registrado",
            "ci_exp.required" => "Este campo es obligatorio",
            "fono.required" => "Este campo es obligatorio",
            "fono.min" => "Debes ingresar al menos :min caracteres",
            "acceso.required" => "Este campo es obligatorio",
            "tipo.required" => "Este campo es obligatorio",
            "sucursal_id.required" => "Este campo es obligatorio",
        ];
    }
}
