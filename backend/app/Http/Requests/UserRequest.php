<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('id') ?? $this->route('user');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'user_type' => 'required|string|in:admin,facilitator,participant',
        ];

        // Password rules for creation or when password is provided for update
        if (!$isUpdate || $this->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['password_confirmation'] = 'required|string|min:8';
        }

        // Optional fields
        $rules['phone'] = 'nullable|string|max:20';
        $rules['bio'] = 'nullable|string|max:1000';
        $rules['avatar'] = 'nullable|string|max:255';
        $rules['language_preference'] = 'nullable|string|in:en,ar';
        $rules['timezone'] = 'nullable|string|max:50';
        $rules['is_active'] = 'nullable|boolean';

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name cannot exceed 255 characters.',
            
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'email.max' => 'Email cannot exceed 255 characters.',
            
            'user_type.required' => 'User type is required.',
            'user_type.in' => 'User type must be admin, facilitator, or participant.',
            
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            
            'password_confirmation.required' => 'Password confirmation is required.',
            'password_confirmation.min' => 'Password confirmation must be at least 8 characters long.',
            
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'bio.max' => 'Bio cannot exceed 1000 characters.',
            'avatar.max' => 'Avatar URL cannot exceed 255 characters.',
            'language_preference.in' => 'Language preference must be en or ar.',
            'timezone.max' => 'Timezone cannot exceed 50 characters.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_type' => 'user type',
            'language_preference' => 'language preference',
            'is_active' => 'active status',
        ];
    }
}
