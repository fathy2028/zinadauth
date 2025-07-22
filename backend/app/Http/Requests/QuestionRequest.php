<?php

namespace App\Http\Requests;

use App\Enums\QuestionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class QuestionRequest extends FormRequest
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
        $questionId = $this->route('id'); // Get question ID for updates
        $isUpdate = !empty($questionId); // Check if this is an update operation

        $rules = [
            'question_text' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'min:10',
                'max:1000',
            ],
            'question_text_ar' => [
                'nullable',
                'string',
                'min:10',
                'max:1000',
            ],
            'type' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'in:' . implode(',', array_column(QuestionTypeEnum::cases(), 'value')),
            ],
            'points' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'duration' => [
                'nullable',
                'integer',
                'min:5',
                'max:300', // 5 minutes max
            ],
            'choices' => [
                'nullable',
                'array',
                'min:2',
                'max:6',
            ],
            'choices.*' => [
                'required_with:choices',
                'string',
                'max:500',
            ],
            'choices_ar' => [
                'nullable',
                'array',
                'min:2',
                'max:6',
            ],
            'choices_ar.*' => [
                'required_with:choices_ar',
                'string',
                'max:500',
            ],
            'answer' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'text_answer' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];

        // Add conditional validation based on question type
        $type = $this->input('type');
        if ($type) {
            $questionType = QuestionTypeEnum::tryFrom($type);
            
            if ($questionType) {
                switch ($questionType) {
                    case QuestionTypeEnum::SINGLE_CHOICE:
                    case QuestionTypeEnum::MULTIPLE_CHOICE:
                        $rules['choices'][] = 'required';
                        $rules['answer'][] = 'required';
                        break;
                        
                    case QuestionTypeEnum::TEXT:
                    case QuestionTypeEnum::CODE:
                        $rules['text_answer'][] = 'required';
                        break;
                }
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'question_text.required' => 'Question text is required.',
            'question_text.min' => 'Question text must be at least 10 characters.',
            'question_text.max' => 'Question text cannot exceed 1000 characters.',
            'question_text_ar.min' => 'Arabic question text must be at least 10 characters.',
            'question_text_ar.max' => 'Arabic question text cannot exceed 1000 characters.',
            'type.required' => 'Question type is required.',
            'type.in' => 'Invalid question type. Must be one of: ' . implode(', ', array_column(QuestionTypeEnum::cases(), 'value')),
            'points.integer' => 'Points must be a number.',
            'points.min' => 'Points must be at least 1.',
            'points.max' => 'Points cannot exceed 100.',
            'duration.integer' => 'Duration must be a number.',
            'duration.min' => 'Duration must be at least 5 seconds.',
            'duration.max' => 'Duration cannot exceed 300 seconds (5 minutes).',
            'choices.required' => 'Choices are required for choice-based questions.',
            'choices.array' => 'Choices must be an array.',
            'choices.min' => 'At least 2 choices are required.',
            'choices.max' => 'Maximum 6 choices are allowed.',
            'choices.*.required_with' => 'All choice options are required.',
            'choices.*.string' => 'Each choice must be a string.',
            'choices.*.max' => 'Each choice cannot exceed 500 characters.',
            'choices_ar.array' => 'Arabic choices must be an array.',
            'choices_ar.min' => 'At least 2 Arabic choices are required.',
            'choices_ar.max' => 'Maximum 6 Arabic choices are allowed.',
            'choices_ar.*.required_with' => 'All Arabic choice options are required.',
            'choices_ar.*.string' => 'Each Arabic choice must be a string.',
            'choices_ar.*.max' => 'Each Arabic choice cannot exceed 500 characters.',
            'answer.required' => 'Answer is required for choice-based questions.',
            'answer.integer' => 'Answer must be a number.',
            'answer.min' => 'Answer index must be 0 or greater.',
            'text_answer.required' => 'Text answer is required for text-based questions.',
            'text_answer.string' => 'Text answer must be a string.',
            'text_answer.max' => 'Text answer cannot exceed 1000 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $questionId = $this->route('id');
        $isUpdate = !empty($questionId);
        $data = [];

        // Clean and prepare question text
        if ($this->has('question_text')) {
            $data['question_text'] = trim($this->question_text);
        }

        if ($this->has('question_text_ar')) {
            $data['question_text_ar'] = !empty($this->question_text_ar) ? trim($this->question_text_ar) : null;
        }

        // Clean and prepare type
        if ($this->has('type')) {
            $data['type'] = strtolower(trim($this->type));
        }

        // Set default values for create operations
        if (!$isUpdate) {
            $data['points'] = $this->points ?? 10;
            $data['duration'] = $this->duration ?? 30;
        } else {
            if ($this->has('points')) {
                $data['points'] = $this->points;
            }
            if ($this->has('duration')) {
                $data['duration'] = $this->duration;
            }
        }

        // Clean choices arrays
        if ($this->has('choices') && is_array($this->choices)) {
            $data['choices'] = array_map('trim', array_filter($this->choices));
        }

        if ($this->has('choices_ar') && is_array($this->choices_ar)) {
            $data['choices_ar'] = array_map('trim', array_filter($this->choices_ar));
        }

        // Clean text answer
        if ($this->has('text_answer')) {
            $data['text_answer'] = !empty($this->text_answer) ? trim($this->text_answer) : null;
        }

        // Handle answer field
        if ($this->has('answer')) {
            $data['answer'] = is_numeric($this->answer) ? (int) $this->answer : null;
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateQuestionTypeSpecificRules($validator);
        });
    }

    /**
     * Validate question type specific rules.
     */
    protected function validateQuestionTypeSpecificRules(Validator $validator): void
    {
        $type = $this->input('type');
        $choices = $this->input('choices', []);
        $answer = $this->input('answer');

        if (!$type) {
            return;
        }

        $questionType = QuestionTypeEnum::tryFrom($type);
        
        if (!$questionType) {
            return;
        }

        switch ($questionType) {
            case QuestionTypeEnum::SINGLE_CHOICE:
                if (!empty($choices) && $answer !== null) {
                    if ($answer >= count($choices)) {
                        $validator->errors()->add('answer', 'Answer index must be within the choices range.');
                    }
                }
                break;

            case QuestionTypeEnum::MULTIPLE_CHOICE:
                // For multiple choice, answer can be an array or single value
                if (!empty($choices) && $answer !== null) {
                    $answerArray = is_array($answer) ? $answer : [$answer];
                    foreach ($answerArray as $ans) {
                        if ($ans >= count($choices)) {
                            $validator->errors()->add('answer', 'All answer indices must be within the choices range.');
                            break;
                        }
                    }
                }
                break;
        }

        // Validate that Arabic choices count matches English choices if both are provided
        $choicesAr = $this->input('choices_ar', []);
        if (!empty($choices) && !empty($choicesAr)) {
            if (count($choices) !== count($choicesAr)) {
                $validator->errors()->add('choices_ar', 'Arabic choices count must match English choices count.');
            }
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
