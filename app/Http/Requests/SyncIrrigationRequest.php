<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncIrrigationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $configuredToken = config('irrigation.device_token');

        if (! is_string($configuredToken) || $configuredToken === '') {
            return ! app()->isProduction();
        }

        $providedToken = $this->header('X-Device-Token');

        return is_string($providedToken) && hash_equals($configuredToken, $providedToken);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pressure_bar' => ['required', 'numeric', 'min:0', 'max:16'],
            'temperature_celsius' => ['sometimes', 'nullable', 'numeric', 'min:-40', 'max:85'],
            'humidity_percent' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'inverter_hz' => ['required', 'numeric', 'min:0', 'max:80'],
            'inverter_status' => ['required', 'string', Rule::in(['RUN', 'STOP', 'FAULT'])],
            'inverter_current' => ['required', 'numeric', 'min:0', 'max:200'],
            'error_code' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
