<?php

namespace App\Http\Requests;

use App\Models\Configuracion;
use App\Models\Reserva;
use App\Support\GeneradorSlots;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class GuardarReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** En PATCH todo es opcional: se actualiza solo lo que llega. */
    private function editando(): bool
    {
        return $this->isMethod('PATCH') || $this->isMethod('PUT');
    }

    protected function prepareForValidation(): void
    {
        // Aceptamos "18:00:00" del cliente pero validamos y guardamos siempre "18:00".
        if (is_string($this->hora_inicio)) {
            $this->merge(['hora_inicio' => substr($this->hora_inicio, 0, 5)]);
        }
    }

    public function rules(): array
    {
        $base = $this->editando() ? 'sometimes' : 'required';

        return [
            'cancha_id' => [$base, 'integer', 'exists:canchas,id'],
            'fecha' => [$base, 'date_format:Y-m-d'],
            'hora_inicio' => [$base, 'regex:/^([01]\d|2[0-3]):00$/'],
            'estado' => [$base, 'in:'.implode(',', Reserva::ESTADOS)],
            'cliente_nombre' => [$base, 'string', 'max:100'],
            'cliente_telefono' => [$base, 'string', 'max:20'],
            'nota' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'cancha_id.exists' => 'La cancha seleccionada no existe.',
            'fecha.date_format' => 'La fecha debe tener el formato AAAA-MM-DD.',
            'hora_inicio.regex' => 'La hora debe ser una hora en punto, por ejemplo 18:00.',
            'estado.in' => 'El estado debe ser separado u ocupado.',
            'cliente_nombre.max' => 'El nombre no puede pasar de 100 caracteres.',
            'cliente_telefono.max' => 'El teléfono no puede pasar de 20 caracteres.',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return; // Sin datos básicos válidos estas reglas no tienen sentido.
            }

            [$fecha, $hora] = $this->fechaYHoraFinales();

            $this->validarHorario($validator, $hora);
            $this->validarNoPasada($validator, $fecha, $hora);
        }];
    }

    /** En PATCH los campos ausentes se toman de la reserva existente. */
    private function fechaYHoraFinales(): array
    {
        $reserva = $this->route('reserva');

        return [
            $this->input('fecha', $reserva?->fecha?->toDateString()),
            $this->input('hora_inicio', $reserva?->hora_inicio),
        ];
    }

    private function validarHorario(Validator $validator, ?string $hora): void
    {
        if ($hora === null) {
            return;
        }

        $config = Configuracion::obtener();
        $horas = GeneradorSlots::horas($config->hora_apertura, $config->hora_cierre);

        if (! in_array($hora, $horas, true)) {
            $validator->errors()->add('hora_inicio', sprintf(
                'La hora debe estar dentro del horario de atención (%s a %s).',
                $config->hora_apertura,
                $config->hora_cierre
            ));
        }
    }

    private function validarNoPasada(Validator $validator, ?string $fecha, ?string $hora): void
    {
        if ($fecha === null || $hora === null) {
            return;
        }

        $inicio = Carbon::createFromFormat('Y-m-d H:i', "$fecha $hora", config('app.timezone'));

        if ($inicio->isPast()) {
            $validator->errors()->add('fecha', 'No se puede reservar una fecha y hora que ya pasó.');
        }
    }
}
