<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOperationParameterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isPengelola() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'profile_name' => ['required', 'string', 'max:255'],
            'lux_threshold' => ['required', 'numeric', 'min:1', 'max:1000'],
            'hysteresis_lux' => ['required', 'numeric', 'min:1', 'max:500'],
            'dim_intensity_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'bright_intensity_percent' => ['required', 'integer', 'min:1', 'max:100', 'gte:dim_intensity_percent'],
            'energy_saving_start_time' => ['required', 'string', 'max:10'],
            'energy_saving_end_time' => ['required', 'string', 'max:10'],
        ];
    }
}
