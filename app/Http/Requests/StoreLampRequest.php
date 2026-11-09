<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLampRequest extends FormRequest
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
            'lamp_code' => ['required', 'string', 'max:50', 'unique:lamps,lamp_code'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'controller_device_id' => ['required', 'string', 'max:100', 'unique:lamps,controller_device_id'],
            'operation_mode' => ['nullable', 'string', 'in:ADAPTIVE,MANUAL'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
