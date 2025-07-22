<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Enums\UserTypeEnum;

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
        $userId = $this->route('id'); // Get user ID for updates
        $isUpdate = !empty($userId); // Check if this is an update operation

        return [
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-Z\s]+$/', // Only letters and spaces
            ],
            'email' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'email:rfc', // Strict email validation
                'max:255',
                $isUpdate ? "unique:users,email,{$userId}" : 'unique:users,email',
            ],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:8',
                'max:255',
            ],
            'user_name' => [
                'nullable',
                'string',
                'max:255',
                $isUpdate ? "unique:users,user_name,{$userId}" : 'unique:users,user_name',
                'regex:/^[a-zA-Z0-9_]+$/', // Only letters, numbers, and underscores
            ],
            'type' => [
                'nullable',
                'string',
                'in:' . implode(',', array_column(UserTypeEnum::cases(), 'value')),
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
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'is_deleted' => [
                'nullable',
                'boolean',
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
        $userId = $this->route('id'); // Get user ID for updates
        $isUpdate = !empty($userId); // Check if this is an update operation
        $data = [];

        // Handle required fields for create, optional for update
        if ($this->has('email')) {
            $data['email'] = strtolower(trim($this->email));
        }

        if ($this->has('name')) {
            $data['name'] = trim($this->name);
        }

        // Handle optional fields
        if ($this->has('user_name')) {
            $data['user_name'] = !empty($this->user_name) ? strtolower(trim($this->user_name)) : null;
        }

        if ($this->has('type') && !empty($this->type)) {
            $data['type'] = strtolower(trim($this->type));
        } elseif (!$isUpdate) {
            // Only set default type for create operations
            $data['type'] = UserTypeEnum::PARTICIPANT->value;
        }

        if ($this->has('theme') && !empty($this->theme)) {
            $data['theme'] = strtolower(trim($this->theme));
        } elseif (!$isUpdate) {
            // Only set default theme for create operations
            $data['theme'] = 'light';
        }

        if ($this->has('web_engine')) {
            $data['web_engine'] = !empty($this->web_engine) ? trim($this->web_engine) : null;
        }

        if ($this->has('image')) {
            $data['image'] = !empty($this->image) ? trim($this->image) : null;
        }

        if ($this->has('is_active')) {
            $data['is_active'] = (bool) $this->is_active;
        }

        if ($this->has('is_deleted')) {
            $data['is_deleted'] = (bool) $this->is_deleted;
        }

        if (!empty($data)) {
            $this->merge($data);
        }
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
