<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->get('language', 'en');
        $includeAnswers = $request->get('include_answers', false);
        
        return [
            'id' => $this->id,
            'question_text' => $this->getQuestionText($language),
            'question_text_en' => $this->question_text,
            'question_text_ar' => $this->question_text_ar,
            'choices' => $this->getChoices($language),
            'choices_en' => $this->choices,
            'choices_ar' => $this->choices_ar,
            'type' => $this->type->value,
            'type_label' => $this->getTypeLabel(),
            'points' => $this->points,
            'duration' => $this->duration,
            'difficulty' => $this->getDifficulty(),
            'is_multiple_choice' => $this->isMultipleChoice(),
            'is_single_choice' => $this->isSingleChoice(),
            'is_text_based' => $this->isTextBased(),
            
            // Include answers only if explicitly requested (for admin/creator view)
            'answer' => $this->when($includeAnswers && !$this->isTextBased(), $this->getCorrectAnswer()),
            'text_answer' => $this->when($includeAnswers && $this->isTextBased(), $this->getCorrectTextAnswer()),
            
            // Creator information
            'creator' => $this->when($this->relationLoaded('creator'), function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            
            // Assignment information if loaded
            'assignments' => $this->when($this->relationLoaded('assignments'), function () {
                return $this->assignments->map(function ($assignment) {
                    return [
                        'id' => $assignment->id,
                        'title' => $assignment->title,
                        'question_order' => $assignment->pivot->question_order ?? null,
                    ];
                });
            }),
            
            // Metadata
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'created_by' => $this->created_by,
            
            // Additional computed fields
            'has_arabic_translation' => !empty($this->question_text_ar),
            'choices_count' => $this->isTextBased() ? 0 : count($this->choices ?? []),
            'estimated_reading_time' => $this->getEstimatedReadingTime(),
        ];
    }

    /**
     * Get the type label for display.
     */
    protected function getTypeLabel(): string
    {
        return match ($this->type) {
            \App\Enums\QuestionTypeEnum::SINGLE_CHOICE => 'Single Choice',
            \App\Enums\QuestionTypeEnum::MULTIPLE_CHOICE => 'Multiple Choice',
            \App\Enums\QuestionTypeEnum::TEXT => 'Text Answer',
            \App\Enums\QuestionTypeEnum::CODE => 'Code Answer',
            default => 'Unknown',
        };
    }

    /**
     * Get estimated reading time in seconds.
     */
    protected function getEstimatedReadingTime(): int
    {
        $text = $this->question_text;
        $wordCount = str_word_count(strip_tags($text));
        
        // Average reading speed: 200 words per minute
        $readingTimeMinutes = $wordCount / 200;
        $readingTimeSeconds = $readingTimeMinutes * 60;
        
        // Add time for choices if applicable
        if (!$this->isTextBased() && !empty($this->choices)) {
            $choicesText = implode(' ', $this->choices);
            $choicesWordCount = str_word_count(strip_tags($choicesText));
            $choicesReadingTime = ($choicesWordCount / 200) * 60;
            $readingTimeSeconds += $choicesReadingTime;
        }
        
        // Minimum 5 seconds, maximum based on duration
        return max(5, min((int) ceil($readingTimeSeconds), $this->duration - 5));
    }

    /**
     * Create a collection of question resources for student view (without answers).
     */
    public static function collectionForStudents($resource)
    {
        return static::collection($resource)->additional([
            'meta' => [
                'include_answers' => false,
                'view_type' => 'student',
            ]
        ]);
    }

    /**
     * Create a collection of question resources for admin/creator view (with answers).
     */
    public static function collectionForAdmins($resource)
    {
        return static::collection($resource)->additional([
            'meta' => [
                'include_answers' => true,
                'view_type' => 'admin',
            ]
        ]);
    }

    /**
     * Create a minimal question resource for listing.
     */
    public function toMinimalArray(): array
    {
        return [
            'id' => $this->id,
            'question_text' => $this->question_text,
            'type' => $this->type->value,
            'type_label' => $this->getTypeLabel(),
            'points' => $this->points,
            'duration' => $this->duration,
            'difficulty' => $this->getDifficulty(),
            'created_at' => $this->created_at?->toISOString(),
            'creator_name' => $this->creator?->name,
        ];
    }
}
