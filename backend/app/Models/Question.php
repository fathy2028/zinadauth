<?php

namespace App\Models;

use App\Enums\QuestionTypeEnum;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question_text',
        'question_text_ar',
        'choices',
        'choices_ar',
        'type',
        'created_by',
        'points',
        'duration',
        'answer',
        'text_answer',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'created_by' => 'string',
        'choices' => 'array',
        'choices_ar' => 'array',
        'type' => QuestionTypeEnum::class,
        'points' => 'integer',
        'duration' => 'integer',
        'answer' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'answer',
        'text_answer',
    ];

    /**
     * Get the user who created this question.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the assignments that include this question.
     */
    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Assignment::class, 'assignment_questions')
                    ->withPivot('question_order')
                    ->withTimestamps();
    }

    /**
     * Scope to filter questions by type.
     */
    public function scopeOfType($query, $type)
    {
        $typeValue = $type instanceof QuestionTypeEnum ? $type->value : $type;
        return $query->where('type', $typeValue);
    }

    /**
     * Scope to filter questions by creator.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope to filter questions with minimum points.
     */
    public function scopeWithMinPoints($query, $points)
    {
        return $query->where('points', '>=', $points);
    }

    /**
     * Scope to filter questions with maximum duration.
     */
    public function scopeWithMaxDuration($query, $duration)
    {
        return $query->where('duration', '<=', $duration);
    }

    /**
     * Get the question text based on language preference.
     */
    public function getQuestionText($language = 'en'): string
    {
        return $language === 'ar' ? $this->question_text_ar : $this->question_text;
    }

    /**
     * Get the choices based on language preference.
     */
    public function getChoices($language = 'en'): array
    {
        return $language === 'ar' ? ($this->choices_ar ?? []) : ($this->choices ?? []);
    }

    /**
     * Check if the question is multiple choice.
     */
    public function isMultipleChoice(): bool
    {
        return $this->type === QuestionTypeEnum::MULTIPLE_CHOICE;
    }

    /**
     * Check if the question is single choice.
     */
    public function isSingleChoice(): bool
    {
        return $this->type === QuestionTypeEnum::SINGLE_CHOICE;
    }

    /**
     * Check if the question is text-based.
     */
    public function isTextBased(): bool
    {
        return in_array($this->type, [QuestionTypeEnum::TEXT, QuestionTypeEnum::CODE]);
    }

    /**
     * Get the correct answer for choice-based questions.
     */
    public function getCorrectAnswer()
    {
        return $this->isTextBased() ? null : $this->answer;
    }

    /**
     * Get the correct text answer for text-based questions.
     */
    public function getCorrectTextAnswer(): ?string
    {
        return $this->isTextBased() ? $this->text_answer : null;
    }

    /**
     * Validate if a given answer is correct.
     */
    public function isCorrectAnswer($answer): bool
    {
        if ($this->isTextBased()) {
            return trim(strtolower($answer)) === trim(strtolower($this->text_answer));
        }

        if ($this->isMultipleChoice()) {
            // For multiple choice, answer should be an array
            if (!is_array($answer)) {
                return false;
            }
            sort($answer);
            $correctAnswers = is_array($this->answer) ? $this->answer : [$this->answer];
            sort($correctAnswers);
            return $answer === $correctAnswers;
        }

        // Single choice - answer is stored as array but should have one element
        $correctAnswer = is_array($this->answer) ? $this->answer[0] : $this->answer;
        return (int) $answer === (int) $correctAnswer;
    }

    /**
     * Get question difficulty based on points and duration.
     */
    public function getDifficulty(): string
    {
        $score = ($this->points / 10) + (10 - $this->duration);
        
        if ($score <= 5) {
            return 'easy';
        } elseif ($score <= 10) {
            return 'medium';
        } else {
            return 'hard';
        }
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($question) {
            if (auth()->check()) {
                $question->created_by = auth()->id();
            }
        });
    }
}
