<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CarreraJugadorUpdateRequest extends FormRequest
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
            "campeonato_inscripcion_id" => "required",
            "campeonato_id" => "required",
            "carrera_id" => "required",
            "jugador_id" => "required",
            "posicion" => "required",
            "nro" => "required",
        ];
    }
    public function attributes()
    {
        return [
            "campeonato_inscripcion_id" => "campeonato",
            "campeonato_id" => "campeonato",
            "carrera_id" => "carrera",
            "jugador_id" => "Jugador",
            "posicion" => "Posición",
            "nro" => "Nro. de Casaca",
        ];
    }
}
