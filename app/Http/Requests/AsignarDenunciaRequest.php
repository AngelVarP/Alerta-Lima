<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AsignarDenunciaRequest extends FormRequest
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
            'funcionario_id' => 'required|exists:usuarios,id',
            'motivo' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'funcionario_id.required' => 'Debes seleccionar un funcionario.',
            'funcionario_id.exists' => 'El funcionario seleccionado no es válido.',
            'motivo.max' => 'El motivo no puede exceder 500 caracteres.',
        ];
    }
}
