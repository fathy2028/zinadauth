<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\User;
use App\Enums\QuestionTypeEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Question::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(QuestionTypeEnum::cases());
        
        return [
            'question_text' => $this->faker->sentence() . '?',
            'question_text_ar' => 'سؤال تجريبي باللغة العربية؟',
            'type' => $type,
            'choices' => $this->getChoicesForType($type),
            'choices_ar' => $this->getArabicChoicesForType($type),
            'answer' => $this->getAnswerForType($type),
            'text_answer' => $this->getTextAnswerForType($type),
            'points' => $this->faker->numberBetween(5, 50),
            'duration' => $this->faker->numberBetween(30, 300),
            'created_by' => User::factory()->create(['type' => UserTypeEnum::FACILITATOR->value])->id,
        ];
    }

    /**
     * Create a single choice question.
     */
    public function singleChoice(): static
    {
        return $this->state(function (array $attributes) {
            $choices = ['Option A', 'Option B', 'Option C', 'Option D'];
            $correctAnswer = $this->faker->numberBetween(0, 3);
            
            return [
                'type' => QuestionTypeEnum::SINGLE_CHOICE,
                'choices' => $choices,
                'choices_ar' => ['خيار أ', 'خيار ب', 'خيار ج', 'خيار د'],
                'answer' => [$correctAnswer],
                'text_answer' => null,
            ];
        });
    }

    /**
     * Create a multiple choice question.
     */
    public function multipleChoice(): static
    {
        return $this->state(function (array $attributes) {
            $choices = ['Option A', 'Option B', 'Option C', 'Option D', 'Option E'];
            $correctAnswers = $this->faker->randomElements([0, 1, 2, 3, 4], $this->faker->numberBetween(2, 3));
            
            return [
                'type' => QuestionTypeEnum::MULTIPLE_CHOICE,
                'choices' => $choices,
                'choices_ar' => ['خيار أ', 'خيار ب', 'خيار ج', 'خيار د', 'خيار هـ'],
                'answer' => array_values($correctAnswers),
                'text_answer' => null,
            ];
        });
    }

    /**
     * Create a text question.
     */
    public function text(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => QuestionTypeEnum::TEXT,
                'choices' => null,
                'choices_ar' => null,
                'answer' => null,
                'text_answer' => $this->faker->paragraph(),
            ];
        });
    }

    /**
     * Create a code question.
     */
    public function code(): static
    {
        return $this->state(function (array $attributes) {
            $codeExamples = [
                'function reverseString($str) { return strrev($str); }',
                'function factorial($n) { return $n <= 1 ? 1 : $n * factorial($n - 1); }',
                'function isPrime($num) { for($i = 2; $i <= sqrt($num); $i++) { if($num % $i == 0) return false; } return true; }',
                'function fibonacci($n) { return $n <= 1 ? $n : fibonacci($n-1) + fibonacci($n-2); }',
            ];
            
            return [
                'type' => QuestionTypeEnum::CODE,
                'choices' => null,
                'choices_ar' => null,
                'answer' => null,
                'text_answer' => $this->faker->randomElement($codeExamples),
            ];
        });
    }

    /**
     * Create a question with specific points.
     */
    public function withPoints(int $points): static
    {
        return $this->state(function (array $attributes) use ($points) {
            return [
                'points' => $points,
            ];
        });
    }

    /**
     * Create a question with specific duration.
     */
    public function withDuration(int $duration): static
    {
        return $this->state(function (array $attributes) use ($duration) {
            return [
                'duration' => $duration,
            ];
        });
    }

    /**
     * Create a question created by a specific user.
     */
    public function createdBy(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'created_by' => $user->id,
            ];
        });
    }

    /**
     * Create a question with specific difficulty (easy, medium, hard).
     */
    public function difficulty(string $difficulty): static
    {
        return $this->state(function (array $attributes) use ($difficulty) {
            switch ($difficulty) {
                case 'easy':
                    return [
                        'points' => $this->faker->numberBetween(5, 15),
                        'duration' => $this->faker->numberBetween(180, 300),
                    ];
                case 'medium':
                    return [
                        'points' => $this->faker->numberBetween(16, 35),
                        'duration' => $this->faker->numberBetween(90, 179),
                    ];
                case 'hard':
                    return [
                        'points' => $this->faker->numberBetween(36, 50),
                        'duration' => $this->faker->numberBetween(30, 89),
                    ];
                default:
                    return [];
            }
        });
    }

    /**
     * Create a question with multilingual support.
     */
    public function multilingual(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'question_text' => 'What is the capital of France?',
                'question_text_ar' => 'ما هي عاصمة فرنسا؟',
                'choices' => ['London', 'Berlin', 'Paris', 'Madrid'],
                'choices_ar' => ['لندن', 'برلين', 'باريس', 'مدريد'],
            ];
        });
    }

    /**
     * Get choices based on question type.
     */
    private function getChoicesForType(QuestionTypeEnum $type): ?array
    {
        if (in_array($type, [QuestionTypeEnum::SINGLE_CHOICE, QuestionTypeEnum::MULTIPLE_CHOICE])) {
            return [
                $this->faker->sentence(3),
                $this->faker->sentence(3),
                $this->faker->sentence(3),
                $this->faker->sentence(3),
            ];
        }
        
        return null;
    }

    /**
     * Get Arabic choices based on question type.
     */
    private function getArabicChoicesForType(QuestionTypeEnum $type): ?array
    {
        if (in_array($type, [QuestionTypeEnum::SINGLE_CHOICE, QuestionTypeEnum::MULTIPLE_CHOICE])) {
            return [
                'خيار أول',
                'خيار ثاني',
                'خيار ثالث',
                'خيار رابع',
            ];
        }
        
        return null;
    }

    /**
     * Get answer based on question type.
     */
    private function getAnswerForType(QuestionTypeEnum $type): ?array
    {
        switch ($type) {
            case QuestionTypeEnum::SINGLE_CHOICE:
                return [$this->faker->numberBetween(0, 3)];
            case QuestionTypeEnum::MULTIPLE_CHOICE:
                return $this->faker->randomElements([0, 1, 2, 3], $this->faker->numberBetween(2, 3));
            default:
                return null;
        }
    }

    /**
     * Get text answer based on question type.
     */
    private function getTextAnswerForType(QuestionTypeEnum $type): ?string
    {
        if (in_array($type, [QuestionTypeEnum::TEXT, QuestionTypeEnum::CODE])) {
            if ($type === QuestionTypeEnum::CODE) {
                return 'function example() { return "Hello World"; }';
            }
            return $this->faker->paragraph();
        }
        
        return null;
    }
}
