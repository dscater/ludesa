<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PartidoUpdateRequest extends FormRequest
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
            "ci_local_id" => "required",
            "ci_visitante_id" => "required",
            "fecha" => "required|date",
            "hora" => [
                "required",
                "date_format:H:i,H:i:s",
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $fecha = $this->input('fecha');
            $hora = $this->input('hora');

            if (!$fecha || !$hora) {
                return;
            }

            $fechaHora = Carbon::parse("$fecha $hora");

            if ($fechaHora->isPast()) {
                $validator->errors()->add(
                    'fecha',
                    'La fecha y hora no pueden ser anteriores a la fecha y hora actual.'
                );
            }
        });
    }

    public function attributes()
    {
        return [
            "campeonato_id" => "Campeonato",
            "ci_local_id" => "Equipo Local",
            "ci_visitante_id" => "Equipo Visitante",
            "fecha" => "Fecha",
            "hora" => "Hora",
        ];
    }
}
