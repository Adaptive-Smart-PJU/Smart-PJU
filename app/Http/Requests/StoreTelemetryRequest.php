<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTelemetryRequest extends FormRequest
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
            'controller_device_id' => ['required_without:lamp_id', 'nullable', 'string', 'exists:lamps,controller_device_id'],
            'lamp_id' => ['required_without:controller_device_id', 'nullable', 'integer', 'exists:lamps,id'],
            'gateway_id' => ['nullable', 'string', 'max:100'],
            'lux_value' => ['required', 'numeric', 'min:0'],
            'activity_detected' => ['required', 'boolean'],
            'voltage_volt' => ['required', 'numeric', 'min:0'],
            'current_ampere' => ['required', 'numeric', 'min:0'],
            'power_watt' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
