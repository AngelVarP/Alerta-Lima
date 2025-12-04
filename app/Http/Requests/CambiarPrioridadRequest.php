<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CambiarPrioridadRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'prioridad_id' => 'required|exists:prioridades_denuncia,id',
            'motivo' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'prioridad_id.required' => 'Debes seleccionar una prioridad.',
            'prioridad_id.exists' => 'La prioridad seleccionada no es válida.',
            'motivo.max' => 'El motivo no puede exceder 500 caracteres.',
        ];
    }
}
