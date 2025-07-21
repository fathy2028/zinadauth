<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class RegisterRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-Z\s]+$/', // Only letters and spaces
            ],
            'email' => [
                'required',
                'string',
                'email:rfc', // Strict email validation
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
            'user_name' => [
                'nullable',
                'string',
                'max:255',
                'unique:users,user_name',
                'regex:/^[a-zA-Z0-9_]+$/', // Only letters, numbers, and underscores
            ],
            'type' => [
                'nullable',
                'string',
                'in:admin,participant,facilitator',
            ],
            'theme' => [
                'nullable',
                'string',
                'in:light,dark',
            ],
            'image' => [
                'nullable',
                'string',
                'max:65535', // TEXT field limit
            ],
            'web_engine' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The name may only contain letters and spaces.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.min' => 'Password must be at least 8 characters long.',
            'user_name.regex' => 'The username may only contain letters, numbers, and underscores.',
            'user_name.unique' => 'This username is already taken.',
            'type.in' => 'The user type must be admin, participant, or facilitator.',
            'theme.in' => 'The theme must be light or dark.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [
            'email' => strtolower(trim($this->email)),
            'name' => trim($this->name),
        ];

        // Handle optional fields
        if ($this->has('user_name') && !empty($this->user_name)) {
            $data['user_name'] = strtolower(trim($this->user_name));
        }

        if ($this->has('type') && !empty($this->type)) {
            $data['type'] = strtolower(trim($this->type));
        } else {
            $data['type'] = 'participant'; // Default type
        }

        if ($this->has('theme') && !empty($this->theme)) {
            $data['theme'] = strtolower(trim($this->theme));
        } else {
            $data['theme'] = 'light'; // Default theme
        }

        if ($this->has('web_engine') && !empty($this->web_engine)) {
            $data['web_engine'] = trim($this->web_engine);
        }

        if ($this->has('image') && !empty($this->image)) {
            $data['image'] = trim($this->image);
        }

        $this->merge($data);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
