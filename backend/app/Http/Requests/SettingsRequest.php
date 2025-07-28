<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client' => ['required', 'string', 'max:255'],
            'logo' => ['required', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
            'primary_color' => ['required', 'hex_color'],
            'secondary_color' => ['required', 'hex_color'],
            'dark_primary_color' => ['required', 'hex_color'],
            'dark_secondary_color' => ['required', 'hex_color'],
            'lang' => ['required', 'string', 'size:2', 'regex:/^[a-z]{2}$/'],
            'domain_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/'],
            'duration' => ['required', 'numeric', 'min:0', 'max:1000'],
        ];
    }
}
