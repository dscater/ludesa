<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampeonatoStoreRequest extends FormRequest
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
        $campeonatoId = $this->route('campeonato')?->id;

        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('campeonatos', 'nombre')
                    ->where(function ($query) {
                        return $query
                            ->where('periodo', $this->periodo)
                            ->where('gestion', $this->gestion)
                            ->where('tipo', $this->tipo);
                    })
                    ->ignore($campeonatoId),
            ],

            'periodo' => [
                'required',
                'string',
            ],

            'gestion' => [
                'required',
                'integer',
            ],

            'tipo' => [
                'required',
                'string',
            ],

            'descripcion' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del campeonato es obligatorio.',
            'nombre.string' => 'El nombre del campeonato debe ser un texto.',
            'nombre.max' => 'El nombre del campeonato no puede superar los 255 caracteres.',
            'nombre.unique' => 'Ya existe un campeonato con el mismo nombre, periodo, gestión y tipo.',

            'periodo.required' => 'Debe seleccionar el periodo del campeonato.',
            'periodo.string' => 'El periodo seleccionado no es válido.',

            'gestion.required' => 'Debe seleccionar la gestión del campeonato.',
            'gestion.integer' => 'La gestión debe ser un año válido.',

            'tipo.required' => 'Debe seleccionar el tipo de campeonato.',
            'tipo.string' => 'El tipo de campeonato no es válido.',

            'descripcion.string' => 'La descripción debe ser un texto.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre del campeonato',
            'periodo' => 'periodo',
            'gestion' => 'gestión',
            'tipo' => 'tipo de campeonato',
            'descripcion' => 'descripción',
        ];
    }
}
