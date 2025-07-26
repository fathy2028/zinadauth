<?php

namespace App\Http\Requests;

use App\Enums\QuestionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class QuestionBulkCreateRequest extends FormRequest
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
            'questions' => 'required|array|min:1|max:50',
            'questions.*.question_text' => 'required|string|min:10|max:1000',
            'questions.*.question_text_ar' => 'nullable|string|min:10|max:1000',
            'questions.*.type' => 'required|string|in:' . implode(',', array_column(QuestionTypeEnum::cases(), 'value')),
            'questions.*.choices' => 'nullable|array|min:2|max:6',
            'questions.*.choices.*' => 'required_with:questions.*.choices|string|max:500',
            'questions.*.choices_ar' => 'nullable|array|min:2|max:6',
            'questions.*.choices_ar.*' => 'required_with:questions.*.choices_ar|string|max:500',
            'questions.*.answer' => 'nullable|array',
            'questions.*.text_answer' => 'nullable|string|max:1000',
            'questions.*.points' => 'nullable|integer|min:1|max:100',
            'questions.*.duration' => 'nullable|integer|min:5|max:300',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'questions.required' => 'Questions array is required.',
            'questions.min' => 'At least one question is required.',
            'questions.max' => 'Cannot create more than 50 questions at once.',
            'questions.*.question_text.required' => 'Question text is required for each question.',
            'questions.*.question_text.min' => 'Question text must be at least 10 characters.',
            'questions.*.question_text.max' => 'Question text cannot exceed 1000 characters.',
            'questions.*.type.required' => 'Question type is required for each question.',
            'questions.*.type.in' => 'Invalid question type.',
            'questions.*.choices.min' => 'At least 2 choices are required for choice-based questions.',
            'questions.*.choices.max' => 'Maximum 6 choices allowed per question.',
            'questions.*.choices.*.max' => 'Each choice cannot exceed 500 characters.',
            'questions.*.points.min' => 'Points must be at least 1.',
            'questions.*.points.max' => 'Points cannot exceed 100.',
            'questions.*.duration.min' => 'Duration must be at least 5 seconds.',
            'questions.*.duration.max' => 'Duration cannot exceed 300 seconds.',
        ];
    }
}
