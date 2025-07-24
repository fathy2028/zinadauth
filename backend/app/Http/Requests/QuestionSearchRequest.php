<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionSearchRequest extends FormRequest
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
        return [
            'q' => 'nullable|string|min:2|max:255',
            'language' => 'nullable|string|in:en,ar',
            'type' => 'nullable|string|in:single_choice,multiple_choice,true_false,fill_blank,essay',
            'created_by' => 'nullable|string|exists:users,id',
            'points_min' => 'nullable|integer|min:1',
            'points_max' => 'nullable|integer|max:100',
            'duration_min' => 'nullable|integer|min:5',
            'duration_max' => 'nullable|integer|max:300',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'q.min' => 'Search term must be at least 2 characters.',
            'q.max' => 'Search term cannot exceed 255 characters.',
            'language.in' => 'Language must be either en or ar.',
            'type.in' => 'Invalid question type.',
            'created_by.exists' => 'The specified user does not exist.',
            'points_min.min' => 'Minimum points must be at least 1.',
            'points_max.max' => 'Maximum points cannot exceed 100.',
            'duration_min.min' => 'Minimum duration must be at least 5 seconds.',
            'duration_max.max' => 'Maximum duration cannot exceed 300 seconds.',
            'page.min' => 'Page number must be at least 1.',
            'per_page.min' => 'Items per page must be at least 1.',
            'per_page.max' => 'Items per page cannot exceed 100.',
        ];
    }
}
