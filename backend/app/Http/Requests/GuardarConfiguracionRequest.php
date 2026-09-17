<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarConfiguracionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_negocio' => ['required', 'string', 'max:100'],
            // Formato internacional sin "+": solo dígitos.
            'whatsapp' => ['required', 'regex:/^\d+$/', 'max:20'],
            'hora_apertura' => ['required', 'date_format:H:i'],
            'hora_cierre' => ['required', 'date_format:H:i', 'after:hora_apertura'],
            'precio_hora' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'whatsapp.regex' => 'El WhatsApp debe tener solo dígitos, en formato internacional sin "+".',
            'hora_apertura.date_format' => 'La hora de apertura debe tener el formato HH:mm.',
            'hora_cierre.date_format' => 'La hora de cierre debe tener el formato HH:mm.',
            'hora_cierre.after' => 'La hora de cierre debe ser posterior a la de apertura.',
            'precio_hora.numeric' => 'El precio por hora debe ser un número.',
            'precio_hora.min' => 'El precio por hora no puede ser negativo.',
        ];
    }
}
