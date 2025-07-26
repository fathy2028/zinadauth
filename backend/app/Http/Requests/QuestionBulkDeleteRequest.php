<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBulkDeleteRequest extends FormRequest
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
            'ids' => 'required|array|min:1|max:50',
            'ids.*' => 'required|string|exists:questions,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'Question IDs array is required.',
            'ids.min' => 'At least one question ID is required.',
            'ids.max' => 'Cannot delete more than 50 questions at once.',
            'ids.*.required' => 'Each question ID is required.',
            'ids.*.string' => 'Question ID must be a string.',
            'ids.*.exists' => 'One or more question IDs do not exist.',
        ];
    }
}
