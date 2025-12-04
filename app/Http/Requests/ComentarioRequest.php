<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComentarioRequest extends FormRequest
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
            'contenido' => 'required|string|min:5|max:1000',
            'es_interno' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'contenido.required' => 'El contenido del comentario es obligatorio.',
            'contenido.min' => 'El comentario debe tener al menos 5 caracteres.',
            'contenido.max' => 'El comentario no puede exceder 1000 caracteres.',
        ];
    }
}
