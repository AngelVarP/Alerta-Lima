<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoDenunciaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el Policy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'estado_id' => 'required|exists:estados_denuncia,id',
            'motivo' => 'nullable|string|max:500',
            'comentario_interno' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'estado_id.required' => 'Debes seleccionar un estado.',
            'estado_id.exists' => 'El estado seleccionado no es válido.',
            'motivo.max' => 'El motivo no puede exceder 500 caracteres.',
            'comentario_interno.max' => 'El comentario no puede exceder 1000 caracteres.',
        ];
    }
}
