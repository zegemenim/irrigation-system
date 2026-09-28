<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SyncRelayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $configuredToken = config('relay.device_token');

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
            'sensors' => ['required', 'array', 'min:1'],
            'sensors.*.sensor_number' => ['required', 'integer', 'min:1', 'max:10'],
            'sensors.*.motion_detected' => ['required', 'boolean'],
        ];
    }
}
